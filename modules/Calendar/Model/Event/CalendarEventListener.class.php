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
use Cx\Core\MediaSource\Model\Entity\MediaSourceManager;
use Cx\Core\MediaSource\Model\Entity\MediaSource;
use Cx\Core\Event\Model\Entity\DefaultEventListener;

/**
 * EventListener for Calendar
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_calendar
 */
class CalendarEventListener extends DefaultEventListener {
   
    public function SearchFindContent($search) {
        $term_db = $search->getTerm();
        $query = \Cx\Modules\Calendar\Controller\CalendarEvent::getEventSearchQuery($term_db);
        $pageUrl = function($pageUri, $searchData) {
                        return $pageUri . '?id=' . $searchData['id'] . '&date=' . intval($searchData['startdate']);
                   };
        $result = new \Cx\Core_Modules\Listing\Model\Entity\DataSet($search->getResultArray($query, 'Calendar', 'detail', $pageUrl, $search->getTerm()));
        $search->appendResult($result);
    }

    public function mediasourceLoad(MediaSourceManager $mediaBrowserConfiguration)
    {
        global $_ARRAYLANG;
        $mediaType = new MediaSource('calendar',$_ARRAYLANG['TXT_CALENDAR'],array(
            $this->cx->getWebsiteImagesCalendarPath(),
            $this->cx->getWebsiteImagesCalendarWebPath(),
        ),array(16));
        $mediaBrowserConfiguration->addMediaType($mediaType);
    }
}
