<?php

/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2015
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
 * "Cloudrexx" is a registered trademark of Cloudrexx AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * CategoryEventListener
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_mediadir
 */
namespace Cx\Modules\MediaDir\Model\Event;

/**
 * CategoryEventListener
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_mediadir
 */
class CategoryEventListener extends \Cx\Core\Event\Model\Entity\DefaultEventListener
{

    /**
     * Pre remove category listener
     *
     * @param array $eventArgs
     */
    public function preRemove($eventArgs) {
        $em      = $eventArgs->getEntityManager();
        $uow     = $em->getUnitOfWork();
        $entity  = $eventArgs->getEntity();

        $categoryRepo = $em->getRepository('Cx\Modules\MediaDir\Model\Entity\Category');

        $locales  = $entity->getLocale();
        foreach ($locales as $locale) {
            $em->remove($locale);
            $uow->computeChangeSet(
                $em->getClassMetadata('Cx\Modules\MediaDir\Model\Entity\CategoryLocale'),
                $locale
            );
        }

        // remove all child nodes
        foreach ($entity->getChildren() as $subCategory) {
            $em->remove($subCategory);
        }

        $categoryRepo->removeFromTree($entity);
    }
}
