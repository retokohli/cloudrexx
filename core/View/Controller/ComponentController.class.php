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
 * This is the main Controller for View
 * @copyright   Cloudrexx AG
 * @author      Michael Ritter <michael.ritter@comvation.com>
 * @package     cloudrexx
 * @subpackage  core_view
 * @version     5.0.0
 */

namespace Cx\Core\View\Controller;

/**
 * This is the main Controller for View
 * @copyright   Cloudrexx AG
 * @author      Michael Ritter <michael.ritter@comvation.com>
 * @package     cloudrexx
 * @subpackage  core_view
 * @version     5.0.0
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {

    /**
     * Returns all Controller class names for this component (except this)
     *
     * Be sure to return all your controller classes if you add your own
     * @return array List of Controller class names (without namespace)
     */
    public function getControllerClasses() {
        return array();
    }

    /**
     * Returns the channel for the given request
     * @todo Replace Request and Page by Response once it's used in the system
     * @param \Cx\Core\Routing\Model\Entity\Request $request Cx request
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page Page for request
     * @return string Channel name (see Theme entity for constants)
     */
    public function getChannel($request, $page) {
        if (
            $request->hasParam('printview') &&
            $request->getParam('printview') == 1
        ) {
            return \Cx\Core\View\Model\Entity\Theme::THEME_TYPE_PRINT;
        }
        if (
            $request->hasParam('pdfview') &&
            $request->getParam('pdfview') == 1
        ) {
            return \Cx\Core\View\Model\Entity\Theme::THEME_TYPE_PDF;
        }
        if (
            $request->hasParam('appview') &&
            $request->getParam('appview') == 1
        ) {
            return \Cx\Core\View\Model\Entity\Theme::THEME_TYPE_APP;
        }
        if (
            (
                $request->hasParam('smallscreen') &&
                $request->getParam('smallscreen') >= 1
            ) ||
            (
                $request->hasCookie('smallscreen') &&
                $request->getCookie('smallscreen') >= 1
            )
        ) {
            return \Cx\Core\View\Model\Entity\Theme::THEME_TYPE_MOBILE;
        }
        if (
            $request->isMobilePhone() &&
            !$request->isTablet()
        ) {
            $em = $this->cx->getDb()->getEntityManager();
            $themeRepo = $em->getRepository('Cx\Core\View\Model\Entity\Theme');
            $theme = $themeRepo->getDefaultTheme(
                \Cx\Core\View\Model\Entity\Theme::THEME_TYPE_MOBILE,
                $page->getLang()
            );
            if ($theme) {
                return \Cx\Core\View\Model\Entity\Theme::THEME_TYPE_MOBILE;
            }
        }
        return \Cx\Core\View\Model\Entity\Theme::THEME_TYPE_WEB;
    }

    /**
     * Returns the theme for the given request
     * @todo Replace Request and Page by Response once it's used in the system
     * @param \Cx\Core\Routing\Model\Entity\Request $request Cx request
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page Page for request
     * @return \Cx\Core\View\Model\Entity\Theme Theme for given request
     */
    public function getTheme($request, $page) {
        return $this->getThemeFromChannel(
            $this->getChannel($request, $page),
            $page
        );
    }

    /**
     * Returns the theme for the given request
     * @param string $channel Channel identifier
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page Page
     * @return \Cx\Core\View\Model\Entity\Theme Theme for given request
     */
    public function getThemeFromChannel($channel, $page) {
        $themeRepo = new \Cx\Core\View\Model\Repository\ThemeRepository();
        if (
            $page->getSkin() &&
            (
                $channel == \Cx\Core\View\Model\Entity\Theme::THEME_TYPE_WEB ||
                $page->getUseSkinForAllChannels()
            )
        ) {
            return $themeRepo->findById($page->getSkin());
        }
        return $themeRepo->getDefaultTheme(
            $channel,
            $page->getLang()
        );
    }

    /**
     * Returns the content template file for the given request
     * @todo Replace Request and Page by Response once it's used in the system
     * @param \Cx\Core\Routing\Model\Entity\Request $request Cx request
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page Page for request
     * @return \Cx\Core\ViewManager\Model\Entity\ViewManagerFile Content template file
     */
    public function getContentTemplateFile($request, $page) {
        return $this->getContentTemplateFileFromTheme(
            $this->getTheme($request, $page),
            $this->getChannel($request, $page),
            $page
        );
    }

    /**
     * Returns the content template file for the given theme and channel
     * @param string $channel Channel identifier
     * @param \Cx\Core\Viewg\Model\Entity\Theme $theme Theme
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page Page
     * @return \Cx\Core\ViewManager\Model\Entity\ViewManagerFile Content template file
     */
    public function getContentTemplateFileFromChannel($channel, $theme, $page) {
        $contentFileName = 'content.html';
        if ($page->getModule() == 'Home') {
            $contentFileName = 'home.html';
        }
        $currentTheme = $theme;
        if (empty($page->getCustomContent())) {
            return $currentTheme->getFilePath(
                $currentTheme->getFoldername() . $contentFileName
            );
        }
        $em = $this->cx->getDb()->getEntityManager();
        $themeRepo = $em->getRepository('Cx\Core\View\Model\Entity\Theme');
        $defaultTheme = $themeRepo->getDefaultTheme(
            $channel,
            $page->getLang()
        );
        $fileInCurrentTheme = $currentTheme->getFilePath(
            $currentTheme->getFoldername() . $page->getCustomContent()
        );
        $fileInDefaultTheme = $defaultTheme->getFilePath(
            $defaultTheme->getFoldername() . $page->getCustomContent()
        );
        if (
            $channel == \Cx\Core\View\Model\Entity\Theme::THEME_TYPE_WEB ||
            $page->getUseCustomContentForAllChannels()
        ) {
            // get file from current theme
            if ($fileInCurrentTheme) {
                return $fileInCurrentTheme;
            }
            // get file from default theme
            if ($fileInDefaultTheme) {
                return $fileInDefaultTheme;
            }
        } else {
            // get file from default theme
            if ($fileInDefaultTheme) {
                return $fileInDefaultTheme;
            }
            // get file from current theme
            if ($fileInCurrentTheme) {
                return $fileInCurrentTheme;
            }
        }
    }
}
