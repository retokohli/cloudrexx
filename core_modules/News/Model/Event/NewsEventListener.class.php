<?php

/**
 * EventListener for News
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_news
 */

namespace Cx\Core_Modules\News\Model\Event;

/**
 * EventListener for News
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
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
        $query = "SELECT id, text AS content, title, date, redirect,
               MATCH (text,title,teaser_text) AGAINST ('%$term_db%') AS score
          FROM " . DBPREFIX . "module_news AS tblN
         INNER JOIN " . DBPREFIX . "module_news_locale AS tblL ON tblL.news_id = tblN.id
         WHERE (   text LIKE ('%$term_db%')
                OR title LIKE ('%$term_db%')
                OR teaser_text LIKE ('%$term_db%'))
           AND lang_id=" . FRONTEND_LANG_ID . "
           AND status=1
           AND is_active=1
           AND (startdate<='" . date('Y-m-d') . "' OR startdate='0000-00-00')
           AND (enddate>='" . date('Y-m-d') . "' OR enddate='0000-00-00')";
        
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
}
