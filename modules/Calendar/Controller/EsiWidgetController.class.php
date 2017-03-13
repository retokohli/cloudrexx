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
 * @author      Project Team SS4U <info@comvation.com>
 * @package     cloudrexx
 * @subpackage  module_calendar
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
     * @param string $name Widget name
     * @param \Cx\Core\Html\Sigma Widget template
     * @param string $locale RFC 3066 locale identifier
     */
    public function parseWidget($name, $template, $locale)
    {
        global $_CONFIG, $_ARRAYLANG, $_LANGID;

        $matches = null;
        if (
            !preg_match('/^EVENTS(\d{1,2}|)_FILE/', $name, $matches) ||
            MODULE_INDEX >= 2 ||
            !$_CONFIG['calendarheadlines']
        ) {
            return;
        }

        $category   = null;
        $catMatches = null;
        $_LANGID    = \FWLanguage::getLangIdByIso639_1($locale);
        $_ARRAYLANG = array_merge(
            $_ARRAYLANG,
            \Env::get('init')->loadLanguageData('Calendar')
        );
        $themeRepository = new \Cx\Core\View\Model\Repository\ThemeRepository();
        $theme           = $themeRepository->findById($this->currentThemeId);
        if (!$theme) {
            return;
        }
        $filePath   = $theme->getFolderName() . '/' . 'events' . $matches[1] . '.html';
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
        $headlines        = new CalendarHeadlines($content);
        $headlinesContent = $headlines->getHeadlines($category);
        $template->setVariable($name, $headlinesContent);
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
        if (!isset($params['get']['name'])) {
            return parent::getWidget($params);
        }

        if (isset($params['get']) && isset($params['get']['theme'])) {
            $this->currentThemeId = $params['get']['theme'];
        }

        $matches = null;
        if (
            !preg_match('/^EVENTS(\d{1,2}|)_FILE/', $params['get']['name'], $matches) ||
            MODULE_INDEX >= 2 ||
            !\Cx\Core\Setting\Controller\Setting::getValue('calendarheadlines', 'Config')
        ) {
            return parent::getWidget($params);
        }

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
        $params['response']->setExpirationDate($cacheExpirationDate);
        return parent::getWidget($params);
    }
}
