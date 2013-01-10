<?php

namespace Cx\Update;

class UpdatePageEventListener extends \Cx\Model\Events\PageEventListener {
    /**
     * @param \Doctrine\ORM\Event\PreUpdateEventArgs $eventArgs 
     */
    public function preUpdate($eventArgs) {
        $entity = $eventArgs->getEntity();
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        if ($entity instanceof \Cx\Model\ContentManager\Page) {
            $updatedBy = $entity->getUpdatedBy();
            if (empty($updatedBy)) {
                $entity->setUpdatedBy(
                    $_SESSION['contrexx_update']['username']
                );
                
                $uow->recomputeSingleEntityChangeSet(
                    $em->getClassMetadata('Cx\Model\ContentManager\Page'),
                    $entity
                );
            }
        }
    }
    
    public function postPersist($eventArgs) {}
}
