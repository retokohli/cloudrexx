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

    public function onEvent($eventName, array $eventArgs) {
        $this->$eventName(current($eventArgs));
    }
   
    public static function SearchFindContent($search) {
        $term_db = $search->getTerm();
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
                        if (empty($searchData['redirect'])) {
                            $pageUrlResult = $pageUri . '?newsid=' . $searchData['id'];
                        } else {
                            $pageUrlResult = $searchData['redirect'];
                        }
                        return $pageUrlResult;
                   };
        $result = new \Cx\Core_Modules\Listing\Model\Entity\DataSet($search->getResultArray($query, 'News', 'details', $pageUrl, $search->getTerm()));
        $search->appendResult($result);
    }

}
