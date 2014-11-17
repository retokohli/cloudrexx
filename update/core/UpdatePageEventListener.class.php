<?php

/**
 * Contrexx
 *
 * @link      http://www.contrexx.com
 * @copyright Comvation AG 2007-2014
 * @version   Contrexx 4.0
 * 
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Contrexx" is a registered trademark of Comvation AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

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
