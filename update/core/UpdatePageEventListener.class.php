<?php

namespace Cx\Update;

class UpdatePageEventListener extends \Cx\Model\Events\PageEventListener {
    protected function setUpdatedByCurrentlyLoggedInUser($eventArgs) {
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

    protected function writeXmlSitemap($eventArgs) {}

    protected function checkValidPersistingOperation($pageRepo, $page) {}
}
