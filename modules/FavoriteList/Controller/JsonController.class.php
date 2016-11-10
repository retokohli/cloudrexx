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
 * Json
 * Json controller for FavoriteList
 *
 * @copyright   Cloudrexx AG
 * @author      Manuel Schenk <manuel.schenk@comvation.com>
 * @package     cloudrexx
 * @subpackage  module_favoritelist
 * @version     5.0.0
 */

namespace Cx\Modules\FavoriteList\Controller;

/**
 * Json
 * Json controller for FavoriteList
 *
 * @copyright   Cloudrexx AG
 * @author      Manuel Schenk <manuel.schenk@comvation.com>
 * @package     cloudrexx
 * @subpackage  module_favoritelist
 * @version     5.0.0
 */
class JsonController extends \Cx\Core\Core\Model\Entity\Controller implements \Cx\Core\Json\JsonAdapter
{

    /**
     * Returns the internal name used as identifier for this adapter
     * @return String Name of this adapter
     */
    public function getName()
    {
        return 'FavoriteList';
    }

    /**
     * Returns an array of method names accessable from a JSON request
     * @return array List of method names
     */
    public function getAccessableMethods()
    {
        return array(
            'getCatalog' => new \Cx\Core_Modules\Access\Model\Entity\Permission(null, null, false),
            'addFavorite',
            'removeFavorite',
        );
    }

    /**
     * Returns all messages as string
     * @return String HTML encoded error messages
     */
    public function getMessagesAsString()
    {
        return implode('<br />', $this->messages);
    }

    /**
     * Returns default permission as object
     * @return Object
     */
    public function getDefaultPermissions()
    {
        return null;
    }

    /**
     * get catalog by session
     *
     * @return json result
     */
    public function getCatalog()
    {
        global $_ARRAYLANG;

        $em = $this->cx->getDb()->getEntityManager();
        $catalogRepo = $em->getRepository($this->getNamespace() . '\Model\Entity\Catalog');
        $catalog = $catalogRepo->findOneBy(array('sessionId' => $this->getComponent('Session')->getSession()->sessionid));

        if (empty($catalog)) {
            $content = '<span>' . $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_MESSAGE_NO_LIST'] . '</span>';
        } else {
            $favorites = $catalog->getFavorites();
            if (empty($favorites)) {
                $content = '<span>' . $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_MESSAGE_NO_ENTRIES'] . '</span>';
            } else {
                $content = '<ul>';
                foreach ($favorites as $favorite) {
                    $content .= '<li>';
                    $content .= '<span>' . contrexx_raw2xhtml($favorite->getTitle()) . '</span>';
                    $content .= '<span class="functions">';
                    $removeLink = \Cx\Core\Routing\Url::fromModuleAndCmd($this->getName()) . '/?editid=' . urlencode('{0,' . $favorite->getId() . '}');
                    $content .= '<a class="edit" href="' . $removeLink . '"></a>';
                    $content .= '<a class="delete" href="javascript:void(0);" onclick="favoriteListRemoveFavorite(' . $favorite->getId() . ');"></a>';
                    $content .= '</span>';
                    $content .= '</li>';
                }
                $content .= '</ul>';
            }
        }

        return $content;
    }

    /**
     * adds Favorite to a Catalog
     *
     * @return json result
     */
    public function addFavorite($data = array())
    {
        var_dump('here: addFavorite');
    }

    /**
     * removes a Favorite from Catalog by id
     *
     * @return json result
     */
    public function removeFavorite($data = array())
    {
        var_dump('here: removeFavorite');
    }
}
