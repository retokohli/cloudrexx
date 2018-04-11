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
 * EventListener for News
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  coremodule_news
 */

namespace Cx\Core_Modules\News\Model\Event;

/**
 * EventListener for News
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  coremodule_news
 */
class NewsEventListener implements \Cx\Core\Event\Model\Entity\EventListener {

    /**
     * Callable on trigger Event
     *
     * @param string $eventName Event name
     * @param array  $eventArgs Arguments for the event
     */
    public function onEvent($eventName, array $eventArgs) {
        if (method_exists($this, $eventName)) {
            $this->$eventName($eventArgs);
        }
    }

    /**
     * Global search event listener
     * Appends the News search results to the search object
     *
     * @param array $eventArgs
     */
    private function SearchFindContent(array $eventArgs) {
        $search = current($eventArgs);
        $term_db = contrexx_raw2db($search->getTerm());
        $newsLib = new \Cx\Core_Modules\News\Controller\NewsLibrary();
        $query = '
            SELECT
                `id`,
                `text` AS "content",
                `title`,
                `date`,
                `redirect`,
                MATCH (
                    `text`,`title`,`teaser_text`
                ) AGAINST (
                    "%' . $term_db . '%"
                ) AS `score`
            FROM
                `' . DBPREFIX  . 'module_news` AS `tblN`
            INNER JOIN
                `' . DBPREFIX  . 'module_news_locale` AS `nl`
            ON
                `nl`.`news_id` = `tblN`.`id`
            WHERE
                (
                   `text` LIKE ("%' . $term_db . '%")
                    OR `title` LIKE ("%' . $term_db . '%")
                    OR `teaser_text` LIKE ("%' . $term_db . '%")
                )' .
            $newsLib->getNewsFilterQuery('tblN', '', '');

        $pageUrl = function($pageUri, $searchData) {
            static $objNewsLib = null;
            if (!$objNewsLib) {
                $objNewsLib = new \Cx\Core_Modules\News\Controller\NewsLibrary();
            }
            if (empty($searchData['redirect'])) {
                $newsId         = $searchData['id'];
                $newsCategories = $objNewsLib->getCategoriesByNewsId($newsId);
                $objUrl         = \Cx\Core\Routing\Url::fromModuleAndCmd(
                                        'News',
                                        $objNewsLib->findCmdById(
                                            'details', array_keys($newsCategories)
                                        ),
                                        FRONTEND_LANG_ID,
                                        array('newsid' => $newsId)
                                  );
                $pageUrlResult  = $objUrl->toString();
            } else {
                $pageUrlResult = $searchData['redirect'];
            }
            return $pageUrlResult;
        };
        $result = new \Cx\Core_Modules\Listing\Model\Entity\DataSet($search->getResultArray($query, 'News', '', $pageUrl, $search->getTerm()));
        $search->appendResult($result);
    }

    /**
     * Clear all Ssi cache
     *
     * @param array $eventArgs Event args
     */
    protected function newsClearSsiCache(array $eventArgs)
    {
        // clear ssi cache
        $basicCacheAdaptors = array(
            'getTopNews',
            'getNewsCategories',
            'getNewsArchiveList',
            'getRecentComments',
        );
        foreach (\FWLanguage::getActiveFrontendLanguages() as $lang) {
            $langId  = $lang['id'];
            $themeId = $lang['themesid'];
            foreach ($basicCacheAdaptors as $adaptor) {
                //do special cases for the adaptors
                switch ($adaptor) {
                    case 'getNewsCategories':
                    case 'getNewsArchiveList':
                        $this->clearSsiCache($adaptor, null, $langId);
                        break;
                    default:
                        $this->clearSsiCache($adaptor, $themeId, $langId);
                        break;
                }
            }
            // clear headlines cache
            $headlinesList = $this->getHeadlinesList($themeId);
            foreach ($headlinesList as $headline) {
                $params = array(
                    'headline' => $headline,
                );
                $this->clearSsiCache('getHeadlines', null, $langId, $params);
            }
            // clear teaser cache
            $teaser = new \Cx\Core_Modules\News\Controller\Teasers(false, $langId);
            foreach ($teaser->arrTeaserFrames as $teaserFrame) {
                // language MUST be added manually since langId is not the
                // correct (auto-sorted) ESI param (correct would be lang).
                // Therefore we need to ensure correct param order.
                $params = array(
                    'langId' => contrexx_input2int($langId),
                    'teaserFrame' => strtoupper($teaserFrame['name']),
                );
                $this->clearSsiCache('getTeaserFrame', null, null, $params);
            }
        }
    }

    /**
     * Get all headlines file list from the theme
     *
     * @staticvar array $themes Staic var to store loaded themes
     *
     * @param integer $themeId Template id
     *
     * @return array List of available headlines
     */
    protected function getHeadlinesList($themeId = null)
    {
        $headlineList = array();
        if (null === $themeId) {
            return $headlineList;
        }

        static $themes = array();
        if (isset($themes[$themeId])) {
            return $themes[$themeId];
        }

        $themeRepo = new \Cx\Core\View\Model\Repository\ThemeRepository();
        $theme     = $themeRepo->findById($themeId);
        if (!$theme) {
            $themes[$themeId] = $headlineList;
            return $headlineList;
        }
        for ($i = 0; $i < 10; $i++) {
            $headline = 'headlines'. (!empty($i) ? $i : '');
            if ($theme->getFilePath($headline  .'.html')) {
                $headlineList[] = $headline;
            }
        }
        $themes[$themeId] = $headlineList;

        return $headlineList;
    }

    /**
     * Clears SSI cache page by given arguments
     *
     * @param string  $adaptor              Json adaptor name
     * @param integer $themeId              Template id
     * @param integer $langId               Language id
     * @param array   $additionalParams     Additional parameter needed for json request
     *
     * @return null
     */
    protected function clearSsiCache($adaptor = '', $themeId = null, $langId = null, $additionalParams = array())
    {
        if (empty($adaptor)) {
            return;
        }

        if (null !== $themeId) {
            $additionalParams['theme'] = contrexx_input2int($themeId);
        }
        if (null !== $langId) {
            $additionalParams['langId'] = contrexx_input2int($langId);
        }
        $cx = \Cx\Core\Core\Controller\Cx::instanciate();
        $cx->getComponent('Cache')->clearSsiCachePage('News', $adaptor, $additionalParams);
    }
}
