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
 * Class EsiWidgetController
 *
 * @copyright   CLOUDREXX CMS - Cloudrexx AG Thun
 * @author      Project Team SS4U <info@comvation.com>
 * @package     cloudrexx
 * @subpackage  coremodule_news
 * @version     1.0.0
 */

namespace Cx\Core_Modules\News\Controller;

/**
 * JsonAdapter Controller to handle EsiWidgets
 * Usage:
 * - Create a subclass that implements parseWidget()
 * - Register it as a Controller in your ComponentController
 *
 * @copyright   CLOUDREXX CMS - Cloudrexx AG Thun
 * @author      Project Team SS4U <info@comvation.com>
 * @package     cloudrexx
 * @subpackage  coremodule_news
 * @version     1.0.0
 */

class EsiWidgetController extends \Cx\Core_Modules\Widget\Controller\EsiWidgetController {
    /**
     * currentThemeId
     *
     * @var integer
     */
    protected $currentThemeId;

    /**
     * Parses a widget
     *
     * @param string $name Widget name
     * @param \Cx\Core\Html\Sigma Widget template
     * @param string $locale RFC 3066 locale identifier
     */
    public function parseWidget($name, $template, $locale)
    {
        global $_CONFIG, $_ARRAYLANG;

        $langId          = \FWLanguage::getLangIdByIso639_1($locale);
        $themeRepository = new \Cx\Core\View\Model\Repository\ThemeRepository();
        $theme           = $themeRepository->findById($this->currentThemeId);

        // Parse Headlines
        $matches = null;
        if (preg_match('/^HEADLINES(\d{1,2}|)_FILE/', $name, $matches)) {
            $category   = null;
            $incSubCategories = false;
            $catMatches = null;

            $templateContent = $this->getFileContent(
                $theme,
                'headlines' . $matches[1] . '.html'
            );
            if (!$templateContent) {
                return;
            }

            $_ARRAYLANG = array_merge(
                $_ARRAYLANG,
                \Env::get('init')->loadLanguageData('News')
            );

            if (
                preg_match(
                    '/\{CATEGORY_([0-9]+)(_FULL)?\}/',
                    $templateContent,
                    $catMatches
                )
            ) {
                $category = $catMatches[1];
                $incSubCategories = !empty($catMatches[2]);
            }
            $newsHeadlines = new NewsHeadlines($templateContent);
            $content       = $newsHeadlines->getHomeHeadlines($category, $langId, $incSubCategories);
            $template->setVariable($name, $content);
        }

        // Parse Top news
        if ($name == 'TOP_NEWS_FILE') {
            $templateContent = $this->getFileContent($theme, 'top_news.html');
            if (!$templateContent) {
                return;
            }
            $newsTop = new NewsTop($templateContent);
            $content = $newsTop->getHomeTopNews(0, $langId);
            $template->setVariable($name, $content);
        }

        // Parse News categories
        if ($name == 'NEWS_CATEGORIES') {
            $newsLib = new NewsLibrary();
            $content = $newsLib->getNewsCategories($langId);
            $template->setVariable($name, $content);
        }

        // Parse News Archives
        if ($name == 'NEWS_ARCHIVES') {
            $newsLib = new NewsLibrary();
            $content = $newsLib->getNewsArchiveList($langId);
            $template->setVariable($name, $content);
        }

        // Parse recent News Comments
        if ($name == 'NEWS_RECENT_COMMENTS_FILE') {
            $pageContent = $this->getFileContent($theme, 'news_recent_comments.html');
            if (!$pageContent) {
                return;
            }
            $newsLib = new NewsRecentComments($pageContent);
            $content = $newsLib->getRecentNewsComments($langId);
            $template->setVariable($name, $content);
        }

        // Parse news teasers
        if ($_CONFIG['newsTeasersStatus'] != '1') {
            return;
        }

        if (preg_match('/TEASERS_([0-9a-zA-Z_-]+)/', $name, $matches)) {
            $teasers = new Teasers(false, $langId);
            $code    = '{' . $name . '}';
            $teasers->setTeaserFrames(array($matches[1]), $code);
            $template->setVariable($name, $code);
        }
    }

    /**
     * Returns the content of a widget
     *
     * @param array $params JsonAdapter parameters
     *
     * @return array Content in an associative array
     */
    public function getWidget($params)
    {
        if (isset($params['get']) && isset($params['get']['theme'])) {
            $this->currentThemeId = $params['get']['theme'];
        }
        return parent::getWidget($params);
    }

    /**
     * Get file content
     * 
     * @param \Cx\Core\View\Model\Entity\Theme $theme
     * @param type $fileName
     *
     * @return string
     */
    protected function getFileContent($theme, $fileName)
    {
        if (!($theme instanceof \Cx\Core\View\Model\Entity\Theme)) {
            return;
        }

        return file_get_contents(
            $theme->getFilePath($theme->getFolderName() . '/' . $fileName)
        );
    }
}
