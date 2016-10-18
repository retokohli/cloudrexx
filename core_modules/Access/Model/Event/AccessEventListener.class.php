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
 * Class AccessEventListener
 *
 * @copyright   CLOUDREXX CMS - Cloudrexx AG Thun
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     cloudrexx
 * @subpackage  coremodule_access
 * @version     1.0.0
 */

namespace Cx\Core_Modules\Access\Model\Event;

use Cx\Core\Event\Model\Entity\DefaultEventListener;
use Cx\Core\MediaSource\Model\Entity\MediaSourceManager;
use Cx\Core\MediaSource\Model\Entity\MediaSource;

/**
 * Class AccessEventListener
 *
 * @copyright   CLOUDREXX CMS - Cloudrexx AG Thun
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     cloudrexx
 * @subpackage  coremodule_access
 * @version     1.0.0
 */
class AccessEventListener extends DefaultEventListener
{

    /**
     * @param MediaSourceManager $mediaBrowserConfiguration
     */
    public function mediasourceLoad(
        MediaSourceManager $mediaBrowserConfiguration
    ) {
        global $_ARRAYLANG;
        $mediaType = new MediaSource(
            'access',
            $_ARRAYLANG['TXT_USER_ADMINISTRATION'],
            array(
                $this->cx->getWebsiteImagesAccessPath(),
                $this->cx->getWebsiteImagesAccessWebPath(),
            ),
            array(18)
        );
        $mediaBrowserConfiguration->addMediaType($mediaType);
    }

    /**
     * Access event listener for clearing esi cache
     *
     * @param array $eventArgs
     *
     * @return null
     */
    public function clearEsiCache($eventArgs)
    {
        if (empty($eventArgs) || $eventArgs[0] != 'Access') {
            return;
        }
        global $objInit;

        $accessBlocks = array(
            // jsonAdaptor => block name
            'showCurrentlyOnlineUsers'  => 'access_currently_online_members',
            'showLastActiveUsers'       => 'access_last_active_member_list',
            'showLatestRegisteredUsers' => 'access_latest_registered_member_list',
            'showBirthdayUsers'         => 'access_birthday_member_list',
        );
        $themeRepo   = new \Cx\Core\View\Model\Repository\ThemeRepository();
        $themesBlock = array();
        foreach ($themeRepo->findAll() as $theme) {
            $themeId = $theme->getId();
            $searchTemplateFiles = array_merge(
                array('index.html', 'home.html'),
                $objInit->getCustomContentTemplatesForTheme($theme)
            );
            foreach ($accessBlocks as $adaptor => $block) {
                $themesBlock[$themeId][$adaptor] = array();
                foreach ($searchTemplateFiles as $file) {
                    if ($theme->isBlockExistsInfile($file, $block)) {
                        $themesBlock[$themeId][$adaptor][] = $file;
                    }
                }
            }
        }
        foreach ($themesBlock as $themeId => $adaptorArray) {
            foreach ($adaptorArray as $adaptor => $file) {
                $this->cx->getComponent('Cache')->clearSsiCachePage(
                    'Access',
                    $adaptor,
                    array(
                        'template' => $themeId,
                        'file'     => $file,
                    )
                );
            }
        }
    }
}
