<?php

/**
 * This listener ensures slug consistency on Page objects.
 * On Flushing, all entities are scanned and changed where needed.
 * After persist, the XMLSitemap is rewritten
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  model_events
 */

namespace Cx\Model\Events;

use \Cx\Model\ContentManager\Page as Page;
use Doctrine\Common\Util\Debug as DoctrineDebug;

/**
 * PageEventListenerException
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  model_events
 */
class PageEventListenerException extends \Exception {}

/**
 * PageEventListener
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  model_events
 */
class PageEventListener {
    public function prePersist($eventArgs) {
        $this->setUpdatedByCurrentlyLoggedInUser($eventArgs);
    }

    public function preUpdate($eventArgs) {
        $this->setUpdatedByCurrentlyLoggedInUser($eventArgs);
    }

    protected function setUpdatedByCurrentlyLoggedInUser($eventArgs) {
        $entity = $eventArgs->getEntity();
        $em     = $eventArgs->getEntityManager();
        $uow    = $em->getUnitOfWork();

        if ($entity instanceof \Cx\Model\ContentManager\Page) {
            $entity->setUpdatedBy(
                \FWUser::getFWUserObject()->objUser->getUsername()
            );

            $uow->computeChangeSet(
                $em->getClassMetadata('Cx\Model\ContentManager\Page'),
                $entity
            );
        }
    }

    public function preRemove($eventArgs) {
        $em      = $eventArgs->getEntityManager();
        $uow     = $em->getUnitOfWork();
        $entity  = $eventArgs->getEntity();
        $aliases = array();

        if ($entity instanceof \Cx\Model\ContentManager\Node) {
            $pages = $entity->getPages(true);

            foreach ($pages as $page) {
                $aliases = array_merge($aliases, $page->getAliases());
                $em->remove($page);
                $uow->computeChangeSet(
                    $em->getClassMetadata('Cx\Model\ContentManager\Page'),
                    $page
                );
            }
        } else if ($entity instanceof \Cx\Model\ContentManager\Page) {
            $aliases = $entity->getAliases();
        }

        if (!empty($aliases)) {
            foreach ($aliases as $alias) {
                $node = $alias->getNode();
                $em->remove($node);
                $uow->computeChangeSet(
                    $em->getClassMetadata('Cx\Model\ContentManager\Node'),
                    $node
                );
            }
        }
    }

    public function postPersist($eventArgs) {
        $this->writeXmlSitemap($eventArgs);
    }

    public function postUpdate($eventArgs) {
        $this->writeXmlSitemap($eventArgs);
    }

    public function postRemove($eventArgs) {
        $this->writeXmlSitemap($eventArgs);
    }

    protected function writeXmlSitemap($eventArgs) {
        global $_CONFIG;

        $entity = $eventArgs->getEntity();
        if (($entity instanceof \Cx\Model\ContentManager\Page)
            && ($entity->getType() != 'alias')
            && ($_CONFIG['xmlSitemapStatus'] == 'on')
        ) {
            \Cx\Core\PageTree\XmlSitemapPageTree::write();
        }
    }

    public function onFlush($eventArgs) {
        $em = $eventArgs->getEntityManager();

        $uow = $em->getUnitOfWork();

        $pageRepo = $em->getRepository('Cx\Model\ContentManager\Page');

        foreach ($uow->getScheduledEntityUpdates() AS $entity) {
            $this->checkValidPersistingOperation($pageRepo, $entity);
        }
    }

    /**
     * Sanity test for Pages. Prevents user from persisting bogus Pages.
     * This is the case if
     *  - the Page has fallback content. In this case, the Page's content was overwritten with
     *    other data that is not meant to be persisted.
     *  - more than one page has module home without cmd
     * @throws PageEventListenerException
     */
    protected function checkValidPersistingOperation($pageRepo, $page) {
        if ($page instanceof Page) {
            if ($page->isVirtual()) {
                throw new PageEventListenerException('Tried to persist Page "'.$page->getTitle().'" with id "'.$page->getId().'". This Page is virtual and cannot be stored in the DB.');
            }
            if ($page->getModule() == 'home'
                    && $page->getCmd() == '') {
                $home = $pageRepo->findBy(array(
                    'module' => 'home',
                    'cmd' => '',
                    'lang' => $page->getLang(),
                ));
                reset($home);
                if (   count($home) > 1
                    || (   count($home) == 1
                        && current($home)->getId() != $page->getId())
                ) {
                    throw new PageEventListenerException('Tried to persist Page "'.$page->getTitle().'" with id "'.$page->getId().'". Only one page with module "home" and no cmd is allowed.');
                }
            }
        }
    }
}
