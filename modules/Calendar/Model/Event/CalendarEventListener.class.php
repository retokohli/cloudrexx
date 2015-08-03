<?php

/**
 * Contrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Comvation AG 2007-2015
 * @version   Contrexx 4.0
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
 * "Contrexx" is a registered trademark of Comvation AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */
 
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
