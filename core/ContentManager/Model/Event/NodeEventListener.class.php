<?php

/**
 * This listener ensures tree consistency on Node objects.
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_contentmanager
 */

namespace Cx\Core\ContentManager\Model\Event;

use \Cx\Core\ContentManager\Model\Entity\Page as Page;
use Doctrine\Common\Util\Debug as DoctrineDebug;

/**
 * NodeEventListenerException
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_contentmanager
 */
class NodeEventListenerException extends \Exception {}

/**
 * NodeEventListener
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_contentmanager
 */
class NodeEventListener implements \Cx\Core\Event\Model\Entity\EventListener {
    
    public function preRemove($eventArgs) {
        $em      = $eventArgs->getEntityManager();
        $uow     = $em->getUnitOfWork();
        $entity  = $eventArgs->getEntity();
        $nodeRepo = $em->getRepository('Cx\Core\ContentManager\Model\Entity\Node');
        $pages = $entity->getPages(true);

        foreach ($pages as $page) {
            // NOTE: aliases will be removed when this hook is triggered again
            //       by the removal process of the page (see below)
            $em->remove($page);
            $uow->computeChangeSet(
                $em->getClassMetadata('Cx\Core\ContentManager\Model\Entity\Page'),
                $page
            );
        }

        // NOTE: removeFromTree() will manually remove $entity from the database using DQL.
        //       Additionally, it will detach/remove $entity from UnitOfWork,
        //       which will UnitOfWork cause to skip the final remove() operation on $entity
        //       to prevent causing an issue with the already removed $entity.
        
        // remove all child nodes
        foreach ($entity->getChildren() as $childNode) {
            $em->remove($childNode);
        }
        $nodeRepo->removeFromTree($entity);
    }
    
    public function onFlush($eventArgs) {}

    public function onEvent($eventName, array $eventArgs) {
        $this->$eventName(current($eventArgs));
    }
}
