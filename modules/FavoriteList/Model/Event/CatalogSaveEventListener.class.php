<?php

/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2016
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
 * This listener ensures slug consistency on Page objects.
 * On Flushing, all entities are scanned and changed where needed.
 * After persist, the XMLSitemap is rewritten
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Manuel Schenk <manuel.scenk@comvation.com>
 * @package     cloudrexx
 * @subpackage  module_favoritelist
 */

namespace Cx\Modules\FavoriteList\Model\Event;

/**
 * CatalogSaveEventListenerException
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Manuel Schenk <manuel.scenk@comvation.com>
 * @package     cloudrexx
 * @subpackage  module_favoritelist
 */
class CatalogSaveEventListenerException extends \Exception
{
}

/**
 * CatalogSaveEventListener
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Manuel Schenk <manuel.scenk@comvation.com>
 * @package     cloudrexx
 * @subpackage  module_favoritelist
 */
class CatalogSaveEventListener extends \Cx\Core\Event\Model\Entity\DefaultEventListener
{

    public function prePersist($eventArgs)
    {
        $entity = $eventArgs->getEntity();
        $entity->setDate(new \DateTime());
        $entity->setSessionId($this->getComponent('Session')->getSession()->sessionid);
    }
}
