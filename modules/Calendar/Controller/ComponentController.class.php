<?php
/**
 * Main controller for Calendar
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_calendar
 */

namespace Cx\Modules\Calendar\Controller;

/**
 * Main controller for Calendar
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_calendar
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {
    public function getControllerClasses() {
        // Return an empty array here to let the component handler know that there
        // does not exist a backend, nor a frontend controller of this component.
        return array();
    }

     /**
     * Load your component.
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function load(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        global $_CORELANG, $subMenuTitle, $objTemplate;
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:

                define('CALENDAR_MANDATE', MODULE_INDEX);
                
                $objCalendar = new \Cx\Modules\Calendar\Controller\Calendar(\Env::get('cx')->getPage()->getContent(), MODULE_INDEX);
                \Env::get('cx')->getPage()->setContent($objCalendar->getCalendarPage());
               
                break;

            case \Cx\Core\Core\Controller\Cx::MODE_BACKEND:
                $this->cx->getTemplate()->addBlockfile('CONTENT_OUTPUT', 'content_master', 'LegacyContentMaster.html');
                $objTemplate = $this->cx->getTemplate();
                \Permission::checkAccess(16, 'static');
                $subMenuTitle = $_CORELANG['TXT_CALENDAR'];
                $objCalendarManager = new \Cx\Modules\Calendar\Controller\CalendarManager();
                $objCalendarManager->getCalendarPage();
                break;

            default:
                break;
        }
    }
    /**
     * Do something before content is loaded from DB
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function preContentLoad(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        global $modulespath, $eventsPlaceholder, $_CONFIG, $themesPages, $page_template,
                                $calHeadlinesObj, $calHeadlines, $_ARRAYLANG;
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:
                // Get Calendar Events
                $modulespath = ASCMS_MODULE_PATH.'/Calendar/Controller/CalendarHeadlines.class.php';
                $eventsPlaceholder = '{EVENTS_FILE}';
                if (   MODULE_INDEX < 2
                    && $_CONFIG['calendarheadlines']
                    && (   strpos(\Env::get('cx')->getPage()->getContent(), $eventsPlaceholder) !== false
                        || strpos($themesPages['index'], $eventsPlaceholder) !== false
                        || strpos($themesPages['sidebar'], $eventsPlaceholder) !== false
                        || strpos($page_template, $eventsPlaceholder) !== false)
                    && file_exists($modulespath)
                ) {
                    $_ARRAYLANG = array_merge($_ARRAYLANG, \Env::get('init')->loadLanguageData('Calendar'));
                    $calHeadlinesObj = new \Cx\Modules\Calendar\Controller\CalendarHeadlines($themesPages['calendar_headlines']);
                    $calHeadlines = $calHeadlinesObj->getHeadlines();
                    \Env::get('cx')->getPage()->setContent(str_replace($eventsPlaceholder, $calHeadlines, \Env::get('cx')->getPage()->getContent()));
                    $themesPages['index']   = str_replace($eventsPlaceholder, $calHeadlines, $themesPages['index']);
                    $themesPages['sidebar'] = str_replace($eventsPlaceholder, $calHeadlines, $themesPages['sidebar']);
                    $page_template          = str_replace($eventsPlaceholder, $calHeadlines, $page_template);
                }
                break;
            default:
                break;
        }
        
    }
    
    /**
     * Do something for search the content
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function preContentParse(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        $eventListener = new \Cx\Modules\Calendar\Model\Event\CalendarEventListener($this->cx);
        $this->cx->getEvents()->addEventListener('SearchFindContent', $eventListener);
        $this->cx->getEvents()->addEventListener('LoadMediaTypes', $eventListener);
   }    
}
