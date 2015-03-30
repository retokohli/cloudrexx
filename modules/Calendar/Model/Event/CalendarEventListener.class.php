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
use Cx\Core\Core\Controller\Cx;
use Cx\Core_Modules\MediaBrowser\Controller\MediaBrowserConfiguration;
use Cx\Core\Model\Model\Entity\MediaType;

/**
 * EventListener for Calendar
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_calendar
 */
class CalendarEventListener implements \Cx\Core\Event\Model\Entity\EventListener {

    /**
     * @var Cx
     */
    protected $cx;

    function __construct(Cx $cx)
    {
        $this->cx = $cx;
    }


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

    public function LoadMediaTypes(MediaBrowserConfiguration $mediaBrowserConfiguration)
    {
        global $_ARRAYLANG;
        $mediaType = new MediaType();
        $mediaType->setName('calendar');
        $mediaType->setHumanName($_ARRAYLANG['TXT_CALENDAR']);
        $mediaType->setDirectory(array(
            $this->cx->getWebsiteImagesCalendarPath(),
            $this->cx->getWebsiteImagesCalendarWebPath(),
        ));
        $mediaType->setAccessIds(array(16));
        $mediaBrowserConfiguration->addMediaType($mediaType);
    }
}
