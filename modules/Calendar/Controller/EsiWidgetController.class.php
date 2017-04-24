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
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_calendar
 * @version     1.0.0
 */

namespace Cx\Modules\Calendar\Controller;

/**
 * JsonAdapter Controller to handle EsiWidgets
 * Usage:
 * - Create a subclass that implements parseWidget()
 * - Register it as a Controller in your ComponentController
 *
 * @copyright   CLOUDREXX CMS - Cloudrexx AG Thun
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_calendar
 * @version     1.0.0
 */

class EsiWidgetController extends \Cx\Core_Modules\Widget\Controller\EsiWidgetController {
    /**
     * Parses a widget
     *
     * @param string                                 $name     Widget name
     * @param \Cx\Core\Html\Sigma                    $template Widget template
     * @param \Cx\Core\Routing\Model\Entity\Response $response Response object
     * @param array                                  $params   Get parameters
     */
    public function parseWidget($name, $template, $response, $params)
    {
        global $_LANGID;

        $matches = null;
        if (
            !preg_match('/^EVENTS(\d{1,2}|)_FILE$/', $name, $matches) ||
            MODULE_INDEX >= 2 ||
            !\Cx\Core\Setting\Controller\Setting::getValue(
                'calendarheadlines',
                'Config'
            )
        ) {
            return;
        }

        $category   = null;
        $catMatches = null;
        //The global $_LANGID is required in the method
        //CalendarHeadlines::getHeadlines()
        $_LANGID    = $params['lang'];
        if (!$params['theme']) {
            return;
        }

        $filePath   = $params['theme']->getFolderName() . '/' . 'events' .
            $matches[1] . '.html';
        $fileSystem = \Cx\Core\Core\Controller\Cx::instanciate()
            ->getMediaSourceManager()
            ->getMediaType('themes')
            ->getFileSystem();
        $file = new \Cx\Core\ViewManager\Model\Entity\ViewManagerFile(
            $filePath,
            $fileSystem
        );
        if (!$fileSystem->fileExists($file)) {
            return;
        }

        $content = file_get_contents($fileSystem->getFullPath($file));
        if (
            preg_match(
                '/\{CALENDAR_CATEGORY_([0-9]+)\}/',
                $content,
                $catMatches
            )
        ) {
            $category = $catMatches[1];
        }
        $headlines = new CalendarHeadlines($content);
        $template->setVariable($name, $headlines->getHeadlines($category));

        //Set expiration date
        // get next event
        $calendarLib = new \Cx\Modules\Calendar\Controller\CalendarLibrary('.');
        $calendarLib->getSettings();

        $startDate = new \DateTime();
        //if ($calendarLib->arrSettings['frontendPastEvents'] == 0) {
            // get next ending event starting from today 0:01
            // the event's day on midnight is our expiration date
            $startDate->setTime(0, 0, 0);
        /*} else {
            // get next ending event starting NOW
            // the event's ending time is our expiration date
            $offsetSeconds = abs($calendarLib->getInternDateTimeFromUser()->getOffset());
            $startDate->sub(new \DateInterval('PT' . $offsetSeconds . 'S'));
        }*/
        $eventManager = new \Cx\Modules\Calendar\Controller\CalendarEventManager(
            $startDate,
            null,
            null,
            null,
            true,
            false,
            true,
            0,
            1
        );
        $eventManager->getEventList();
        $cacheExpirationDate = null;
        if (isset($eventManager->eventList[0])) {
            $cacheExpirationDate = $eventManager->eventList[0]->endDate;
            $cacheExpirationDate->setTime(23, 59, 59);
        }
        if (!$cacheExpirationDate) {
            $cacheExpirationDate = new \DateTime();
            $additionalYears = intval(
                $calendarLib->arrSettings['maxSeriesEndsYear']
            ) + 1;
            $cacheExpirationDate->modify(
                '+' . $additionalYears . ' years'
            );
        }
        $response->setExpirationDate($cacheExpirationDate);
    }
}
