<?php
/**
 * This listener ensures slug consistency on Page objects.
 * On Flushing, all entities are scanned and changed where needed.
 */
namespace Cx\Model\Events;
use \Cx\Model\ContentManager\Page as Page;
use Doctrine\Common\Util\Debug as DoctrineDebug;

class PageEventListenerException extends \Exception {}

class PageEventListener {
    /**
     * Creates the nested arrays needed to store the page information and adds it to $sortedPages.
     *
     * @param $entity the Page
     * @param array $sortedPages target Array
     * @param $delete whether we're remembering a deleted page. defaults to false.
     */
    private function addSortedPage($entity, &$sortedPages, $delete = false) {
        if($entity instanceof Page) {
            $node = $entity->getNode();
            if(!$node) //un-linked page. ugly, but not our problem
                return;

            $node = $node->getParent(); //un-linked node. ugly, but not our problem
            if(!$node)
                return;

            $key = $node->getUniqueIdentifier();
            if(!isset($sortedPages[$key]))
                $sortedPages[$key] = array();

            $mySortedPages = &$sortedPages[$key];
            $lang = $entity->getLang();

            if(!isset($mySortedPages[$lang]))
                $mySortedPages[$lang] = array();

            $mySortedPages = &$mySortedPages[$lang];

            if(!isset($mySortedPages['pages'])) {
                $mySortedPages['pages'] = array();
                $mySortedPages['deletedAndFreeSlugs'] = array();
            }
            
            if(!$delete)
                $mySortedPages['pages'][] = $entity;
            else
                $mySortedPages['deletedAndFreeSlugs'][] = $entity->getSlug();
        }        
    }

    /**
     * Makes sure Slugs in sortedPages are unique level-widely.
     *
     * @param array $sortedPages
     * @param EntityManager $em
     */
    private function checkSlugsLocal(&$sortedPages, $em) {
        $uow = $em->getUnitOfWork();

        foreach($sortedPages as $identifier => $languages) {
            foreach($languages as $language => $entries) {
                $deletedAndFreeSlugs = &$entries['deletedAndFreeSlugs'];
                $pages = &$entries['pages'];            

                $usedSlugs = array();
                foreach($pages as $page) {
                    $changed = false;
                    while(in_array($page->getSlug(), $usedSlugs)) {
                        $page->nextSlug();
                        $changed = true;
                    }
                    $usedSlugs[] = $page->getSlug();

                    if($changed) {
                        $uow->recomputeSingleEntityChangeSet($em->getClassMetadata(get_class($page)), $page);
                    }
                }            
            }
        }
    }

    /**
     * Makes sure the unique slugs in sortedPages do not collide with db,
     * changes slugs to keep them unique level-widely.
     *
     * @param array $sortedPages
     * @param EntityManager $em
     */
    private function checkSlugsDb(&$sortedPages, $em) {
        $uow = $em->getUnitOfWork();

        $identifiers = array_keys($sortedPages);
        //sort out ids - nodes who do not have an id as identifier do
        //not need to get checked against the database, since they
        //are not persisted yet.
        $nodeIds = array();
        foreach($identifiers as $identifier) {
            if(substr($identifier,0,1) != 'i')
                $nodeIds[] = intval($identifier);
        }

        if(count($nodeIds) == 0)
            return;

        $repo = $em->getRepository('Cx\Model\ContentManager\Node');
        $pageRepo = $em->getRepository('Cx\Model\ContentManager\Page');

        $query = 'select n from Cx\Model\ContentManager\Node n where n.id in (' . join(',', $nodeIds) . ')';
        $nodes = $em->createQuery($query)->getResult();

        foreach($nodes as $node) {
            $usedSlugs = array();
            $childs = $node->getChildren();
            foreach($childs as $child) {
                $pages = $pageRepo->findBy(array('node' => $child->getId()));
                foreach($pages as $page) {
                    $lang = $page->getLang();
                    if(!isset($usedSlugs[$lang]))
                        $usedSlugs[$lang] = array();
                    $usedSlugs[$lang][$page->getSlug()] = $page->getId();
                }
            }
            
            $persistData = &$sortedPages[$node->getId()];

            foreach($persistData as $lang => $data) {
                $freeSlugs = $data['deletedAndFreeSlugs'];
                foreach($data['pages'] as $page) {
                    if(!isset($usedSlugs[$lang]))
                        $usedSlugs[$lang] = array();

                    $changed = false;

                    while(
                          isset($usedSlugs[$lang][$page->getSlug()]) && 
                          $usedSlugs[$lang][$page->getSlug()] != $page->getId() &&
                          !in_array($page->getSlug(), $freeSlugs)) {

                        $page->nextSlug();
                        $changed = true;
                    }

                    //remember this slug is taken
                    //the -1 makes sure pages without id (those not yet persisted) do not produce a
                    //match in the while loop above
                    $usedSlugs[$lang][$page->getSlug()] = -1;

                    $key = array_search($page->getSlug(), $freeSlugs);
                    if($key)
                        unset($freeSlugs[$key]);

                    //tell doctrine to recompute the changes if any.
                    if($changed) {
                        $uow->recomputeSingleEntityChangeSet($em->getClassMetadata(get_class($page)), $page);
                    }
                }
            }
        }
    }
    
    public function onFlush($eventArgs) {
        $em = $eventArgs->getEntityManager();

        //array ( parent-node-id => array( lang => array( array( pages ), deletedAndFreeSlugs => array( slugs ) ) )
        $sortedPages = array();

        $uow = $em->getUnitOfWork();

        /*
          deleted entities' slugs will be available, set them to null
         */
        foreach ($uow->getScheduledEntityDeletions() AS $entity) {
            $this->addSortedPage($entity, $sortedPages, true);
        }

        /*
          new and updated entities' slugs are remembered.
          nulls from above are overwritten - the pages switched the slug.
         */
        foreach ($uow->getScheduledEntityInsertions() AS $entity) {
            $this->addSortedPage($entity, $sortedPages);
        }

        foreach ($uow->getScheduledEntityUpdates() AS $entity) {
            $this->checkValidPersistingOperation($entity);
            $this->addSortedPage($entity, $sortedPages);
        }

        $this->checkSlugsLocal($sortedPages, $em);

        /*
          we know all slugs taken by the new / changed pages now. 
          check whether those slugs are already used by current pages.

          slugs that are already taken by pages existing in the db have priority.
          for those cases, the slug of the entity to persist is changed to receive
          an unique slug suffix.
         */
        $this->checkSlugsDb($sortedPages, $em);

        /**
         * All pages have got unique slots now. Fiine.
         */
    }

    /**
     * Sanity test for Pages. Prevents user from persisting bogus Pages.
     * This is the case if
     *  - the Page has fallback content. In this case, the Page's content was overwritten with
     *    other data that is not meant to be persisted.
     * @throws PageEventListenerException
     */
    protected function checkValidPersistingOperation($page) {
        // TODO: uh..?
        if($page->getType() != "\Cx\Model\ContentManager\Page") return;
        if($page->hasFallbackContent()) {
            throw new PageEventListenerException('Tried to persist Page "'.$page->getTitle().'" with id "'.$page->getId().'". This Page was retrieved by the routing and is filled with (bogus) fallback content. Please re-fetch the Page via it\'s id, then edit and persist it.');
        }
    }
}
