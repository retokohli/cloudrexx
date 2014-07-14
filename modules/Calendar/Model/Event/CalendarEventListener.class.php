<?php

/**
 * EventListener for Calendar
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_calendar
 */

namespace Cx\Modules\Calendar\Model\Event;

/**
 * EventListener for Calendar
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_calendar
 */
class CalendarEventListener implements \Cx\Core\Event\Model\Entity\EventListener {

    public function onEvent($eventName, array $eventArgs) {
        $this->$eventName(current($eventArgs));
    }
   
    public static function SearchFindContent($search) {
        $term_db = $search->getTerm();
        $query = \Cx\Modules\Calendar\Controller\CalendarEvent::getEventSearchQuery($term_db);
        $pageUrl = function($pageUri, $searchData) {
                        return $pageUri . '?id=' . $searchData['id'] . '&date=' . intval($searchData['startdate']);
                   };
        $result = new \Cx\Core_Modules\Listing\Model\Entity\DataSet($search->getResultArray($query, 'Calendar', 'detail', $pageUrl, $search->getTerm()));
        $search->appendResult($result);
    }

}
