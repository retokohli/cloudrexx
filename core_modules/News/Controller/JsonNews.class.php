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
 * JsonNews
 * Json controller for news module
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  coremodule_news
 */

namespace Cx\Core_Modules\News\Controller;
use \Cx\Core\Json\JsonAdapter;

class JsonNewsException extends \Exception {};

/**
 * JsonNews
 * Json controller for news module
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  coremodule_news
 */
class JsonNews implements JsonAdapter {
    /**
     * List of messages
     * @var Array
     */
    private $messages = array();

    /**
     * Returns the internal name used as identifier for this adapter
     * @return String Name of this adapter
     */
    public function getName() {
        return 'News';
    }

    /**
     * Returns an array of method names accessable from a JSON request
     * @return array List of method names
     */
    public function getAccessableMethods() {
        return array(
            'getAllNews',
            'getNewsCategories',
            'getNewsArchiveList',
            'getTeaserFrame',
            'getHeadlines',
            'getTopNews',
            'getRecentComments',
        );
    }

    /**
     * Returns all messages as string
     * @return String HTML encoded error messages
     */
    public function getMessagesAsString() {
        return implode('<br />', $this->messages);
    }
    /**
     * Returns default permission as object
     * @return Object
     */
    public function getDefaultPermissions() {
        return new \Cx\Core_Modules\Access\Model\Entity\Permission(null, null, false);
    }

    /**
     * get all news list
     *
     * @return json result
     */
    public function getAllNews($data = array())
    {
        global $objDatabase;

        $searchTerm  = isset($data['get']['term'])
            ? contrexx_input2raw($data['get']['term'])
            : '';

        $id = isset($data['get']['id'])
            ? contrexx_input2int($data['get']['id'])
            : 0;

        $langId = isset($data['get']['langId'])
            ? contrexx_input2int($data['get']['langId'])
            : 0;

        if (empty($searchTerm)) {
            $this->messages[] = '';//TODO Show error message
        }

        $query = '
            SELECT
                    n.`id`,
                    nl.`title`

            FROM `'     . DBPREFIX . 'module_news`          AS `n`
            LEFT JOIN ' . DBPREFIX . 'module_news_locale    AS `nl`
            ON      nl.`news_id` = n.`id`
            WHERE   nl.`is_active`="1"
            AND     n.`status`="1"'
            . (!empty($id)
                ? ' AND n.`id`!="' . $id . '"'
                : ''
            )
            . (!empty($langId)
                ? ' AND nl.`lang_id`="' . $langId . '"'
                : ''
            )
            . ' AND (
                        nl.title        LIKE "%' .  contrexx_raw2db($searchTerm) . '%"
                    OR  nl.teaser_text  LIKE "%' .  contrexx_raw2db($searchTerm) . '%"
                )
            ORDER BY nl.`title`';
        $result = array();
        $objResult = $objDatabase->Execute($query);
        if (    $objResult
            &&  $objResult->RecordCount() > 0
        ) {
            while (!$objResult->EOF) {
                $result[$objResult->fields['id']] = $objResult->fields['title'];
                $objResult->MoveNext();
            }
        }
        return $result;
    }

    /**
     * Generates the formated ul/li of categories
     * Used in the template's
     *
     * @param array $params Get/Post parameters
     *
     * @return array Contains the formated ul/li of categories
     */
    public function getNewsCategories($params)
    {
        $newsLib = new NewsLibrary();

        $langId   = !empty($params['get']['langId']) ? contrexx_input2int($params['get']['langId']) : 0;
        return array('content' => $newsLib->getNewsCategories($langId));
    }

    /**
     * Generates the formated ul/li of Archive list
     *
     * @param type $params
     *
     * @return array Contains the formated ul/li of Archive list
     */
    public function getNewsArchiveList($params)
    {
        $newsLib = new NewsLibrary();

        $langId   = !empty($params['get']['langId']) ? contrexx_input2int($params['get']['langId']) : 0;
        return array('content' => $newsLib->getNewsArchiveList($langId));
    }

    /**
     * Get the Teaser text from by teaser id and template id
     *
     * @param array $params Get/Post parameters
     *
     * @return array Contains the teaser content
     */
    public function getTeaserFrame($params)
    {
        $langId      = !empty($params['get']['langId']) ? contrexx_input2int($params['get']['langId']) : null;
        $teaserFrame = !empty($params['get']['teaserFrame']) ? contrexx_input2raw($params['get']['teaserFrame']) : '';
        if (empty($teaserFrame)) {
            return array('content' => '');
        }

        $newsTeaser = new Teasers(false, $langId);
        $arrTeaserFramesNames = array_flip($newsTeaser->arrTeaserFrameNames);

        $arrMatches = preg_grep('/^'.$teaserFrame.'$/i', $arrTeaserFramesNames);
        if (empty($arrMatches)) {
            return array('content' => '');
        }
        $frameId    = array_keys($arrMatches);
        $id         = $frameId[0];
        $templateId = $newsTeaser->arrTeaserFrames[$id]['frame_template_id'];

        return array('content' => $newsTeaser->_getTeaserFrame($id, $templateId));
    }

    /**
     * Parse News for the headlines files
     *
     * @param array $params User input array
     *
     * @return array
     */
    public function getHeadlines($params)
    {
        $headline = !empty($params['get']['headline']) ? contrexx_input2raw($params['get']['headline']) : '';
        if (empty($headline)) {
            \DBG::log(__METHOD___ . ': The headline can not be empty');
            return array('content' => '');
        }
        try {
            $theme   = $this->getThemeFromInput($params);
            $content = $this->getContentFromThemeFile($theme, $headline . '.html');
        } catch (JsonNewsException $e) {
            \DBG::log($e->getMessage());
            return array('content' => '');
        }
        $category = 0;
        $matches  = array();
        if (preg_match('/\{CATEGORY_([0-9]+)\}/', trim($content), $matches)) {
            $category = contrexx_input2int($matches[1]);
        }
        $langId   = !empty($params['get']['langId']) ? contrexx_input2int($params['get']['langId']) : 0;

        $newsHeadlines = new NewsHeadlines($content);
        return array('content' => $newsHeadlines->getHomeHeadlines($category, $langId));
    }

    /**
     * Parse Top news for the template
     *
     * @param array $params User input array
     *
     * @return array
     */
    public function getTopNews($params)
    {
        try {
            $theme   = $this->getThemeFromInput($params);
            $content = $this->getContentFromThemeFile($theme, 'top_news.html');
        } catch (JsonNewsException $e) {
            \DBG::log($e->getMessage());
            return array('content' => '');
        }
        $langId = !empty($params['get']['langId']) ? contrexx_input2int($params['get']['langId']) : 0;
        $newsTopNews = new NewsTop($content);
        return array('content' => $newsTopNews->getHomeTopNews(0, $langId));
    }

    /**
     * Parse the recent comments for the template
     *
     * @param array $params User input array
     *
     * @return array
     */
    public function getRecentComments($params)
    {
        try {
            $theme   = $this->getThemeFromInput($params);
            $content = $this->getContentFromThemeFile($theme, 'news_recent_comments.html');
        } catch (JsonNewsException $e) {
            \DBG::log($e->getMessage());
            return array('content' => '');
        }

        $langId         = !empty($params['get']['langId']) ? contrexx_input2int($params['get']['langId']) : 0;
        $recentComments = new NewsRecentComments($content);
        return array('content' => $recentComments->getRecentNewsComments($langId));
    }

    /**
     * Get theme from the user input
     *
     * @param array $params User input array
     * @return \Cx\Core\View\Model\Entity\Theme Theme instance
     * @throws JsonNewsException When theme id empty or theme does not exits in the system
     */
    protected function getThemeFromInput($params)
    {
        $themeId  = !empty($params['get']['template']) ? contrexx_input2int($params['get']['template']) : 0;
        if (empty($themeId)) {
            throw new JsonNewsException('The theme id is empty in the request');
        }
        $themeRepository = new \Cx\Core\View\Model\Repository\ThemeRepository();
        $theme           = $themeRepository->findById($themeId);
        if (!$theme) {
            throw new JsonNewsException('The theme id '. $themeId .' does not exists.');
        }
        return $theme;
    }

    /**
     * Get the contents from the given theme file path
     *
     * @param \Cx\Core\View\Model\Entity\Theme $theme   Theme instance
     * @param string                           $file    Relative file path
     * @return string File content
     * @throws JsonNewsException When file not exists in the theme
     */
    protected function getContentFromThemeFile(\Cx\Core\View\Model\Entity\Theme $theme, $file)
    {
        $filePath = $theme->getFilePath($file);
        if (empty($filePath)) {
            throw new JsonNewsException('The file => '. $file .' not exists in Theme => ' . $theme->getThemesname());
        }

        $content = file_get_contents($filePath);
        return $content;
    }
}
