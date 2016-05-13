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
 * @copyright   Cloudrexx AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     cloudrexx
 */

namespace Cx\Core\ViewManager\Model\Event;

use Cx\Core\MediaSource\Model\Entity\MediaSourceManager;
use Cx\Core\MediaSource\Model\Entity\MediaSource;
use Cx\Core\Event\Model\Entity\DefaultEventListener;
use Cx\Core\ViewManager\Model\Entity\ViewManagerFileSystem;

/**
 * Class ViewManagerEventListener
 *
 * @copyright   Cloudrexx AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 */
class ViewManagerEventListener extends DefaultEventListener
{
    /**
     * @param MediaSourceManager $mediaSourceManager
     */
    public function mediasourceLoad(MediaSourceManager $mediaSourceManager)
    {
        global $_ARRAYLANG;
        $mediaType = new MediaSource(
            'themes', $_ARRAYLANG['TXT_THEME_THEMES'],
            array(
                $this->cx->getWebsiteThemesPath(),
                $this->cx->getWebsiteThemesWebPath(),
            ), array(), '',
            new ViewManagerFileSystem($this->cx->getWebsiteThemesPath())
        );
        $mediaSourceManager->addMediaType($mediaType);
    }

    /**
     * Reload the MaintenanceFile config option
     * Search for the offline.html in active templates and update the option
     */
    public function viewManagerThemeActive()
    {
        // reload Setting MaintenanceFiles
        $subTypeArray = array(
          \Cx\Core\View\Model\Entity\Theme::THEME_TYPE_WEB      => 'standard',
          \Cx\Core\View\Model\Entity\Theme::THEME_TYPE_MOBILE   => 'mobile',
          \Cx\Core\View\Model\Entity\Theme::THEME_TYPE_PRINT    => 'print',
          \Cx\Core\View\Model\Entity\Theme::THEME_TYPE_PDF      => 'pdf',
          \Cx\Core\View\Model\Entity\Theme::THEME_TYPE_APP      => 'app'
        );
        $themeRepository  = new \Cx\Core\View\Model\Repository\ThemeRepository();
        $languages        = \FWLanguage::getActiveFrontendLanguages();
        $maintenanceFiles = array();
        foreach ($subTypeArray as $subType => $maintenanceFileKey) {
            foreach ($languages as $language) {
                $theme = $themeRepository->getDefaultTheme($subType, $language['id']);
                if (!$theme) {
                    continue;
                }
                $filePath = $theme->getFullFilePath('/offline.html');
                if (!file_exists($filePath)) {
                    continue;
                }
                if (!isset($maintenanceFiles[$maintenanceFileKey])) {
                    $maintenanceFiles[$maintenanceFileKey] = array();
                }
                $fileRelativePath = \Cx\Core\Core\Controller\Cx::FOLDER_NAME_THEMES . '/' . $theme->getFoldername() . '/offline.html';
                $maintenanceFiles[$maintenanceFileKey][$language['id']] = $fileRelativePath;
            }
        }

        \Cx\Core\Setting\Controller\Setting::init('Config');
        \Cx\Core\Setting\Controller\Setting::set('maintenanceFiles', json_encode($maintenanceFiles));
        \Cx\Core\Setting\Controller\Setting::update('maintenanceFiles');
    }
}
