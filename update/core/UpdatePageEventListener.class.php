<?php

namespace Cx\Update;

class UpdatePageEventListener extends \Cx\Core\ContentManager\Model\Event\PageEventListener {
    protected function setUpdatedByCurrentlyLoggedInUser($eventArgs) {
        $entity = $eventArgs->getEntity();
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        if ($entity instanceof \Cx\Core\ContentManager\Model\Entity\Page) {
            $updatedBy = $entity->getUpdatedBy();
            if (empty($updatedBy)) {
                $entity->setUpdatedBy(
                    $_SESSION['contrexx_update']['username']
                );
                
                $uow->recomputeSingleEntityChangeSet(
                    $em->getClassMetadata('Cx\Core\ContentManager\Model\Entity\Page'),
                    $entity
                );
            }
        }
    }

    protected function writeXmlSitemap($eventArgs) {}

    protected function checkValidPersistingOperation($pageRepo, $page) {}
}
