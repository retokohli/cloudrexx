<?php
/**
 * This listener ensures slug consistency on Page objects.
 * On Flushing, all entities are scanned and changed where needed.
 */
namespace \Cx\Model\Events;
use \Cx\Model\ContentManager\Page as Page;

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
            $parentNodeId = $entity->getNode()->getParent()->getId();
            if(!$sortedPages[$parentNodeId])
                $sortedPages[$parentNodeId] = array();

            $mySortedPages = &$sortedPages[$parentNodeId];
            $lang = $entity->getLang();

            if(!isset($mySortedPages['lang'])) {
                $mySortedPages['lang'] = array();
                if(!isset($mySortedPages['lang'][$lang]))
                    $mySortedPages['lang'][$lang] = array();
            }
            $mySortedPages = &$mySortedPages['lang'][$lang];

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
     */
    private function checkSlugsLocal(&$sortedPages) {
        foreach($sortedPages as $nodeId => $languages) {
            foreach($languages as $language => $entries) {
                $deletedAndFreeSlugs = &$entries['deletedAndFreeSlugs'];
                $pages = &$entries['pages'];
                
                $usedSlugs = array();
                foreach($pages as $page) {
                    while(in_array($usedSlugs, $page->getSlug())) {
                        $page->nextSlug();
                    }
                    $usedSlugs[] = $page->getSlug();
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
        $pageRepo = $em->getRepository('Cx\Model\ContentManager\Page');

        $nodeIds = array_keys($sortedPages);

        $repo = $em->getRepository('Cx\Model\ContentManager\Node');
        $nodes = $em->getByNode($nodeIds);

        foreach($nodes as $node) {
            $usedSlugs = array();
            $childs = $node->getChildren();
            foreach($childs as $child) {
                $pages = $repo->findByParent($node);
                foreach($pages as $page) {
                    $usedSlugs[] = $page->getSlug();
                }
            }
            
            $persistData = $sortedPages[$node->getId()];

            foreach($persistData as $lang => $data) {
                $freeSlugs = $data['deletedAndFreeSlugs'];
                foreach($data['pages'] as $page) {
                    $changed = false;
                    while(in_array($usedSlugs, $page->getSlug()) && !in_array($freeSlugs, $page->getSlug())) {
                        $page->nextSlug();
                        $changed = true;
                    }
                    $usedSlugs[] = $page->getSlug();

                    //tell doctrine to recompute the changes if any.
                    if($changed)
                        $uow->computeChangeSet($pageRepo->getClassMetadata(), $page);
                }
            }
        }
    }
    
    public function onFlush(OnFlushEventArgs $eventArgs) {
        $em = $eventArgs->getEntityManager();

        //array ( parent-node-id => array( lang => array( array( pages ), deletedAndFreeSlugs => array( slugs ) ) )
        $sortedPages = array();

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
            $this->addSortedPage($entity, $sortedPages);
        }

        $this->checkSlugsLocal($sortedPages);

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
}