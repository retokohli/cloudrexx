<?php

/**
 * Calendar headline news
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_calendar
 * @todo        Edit PHP DocBlocks!
 */

/**
 * @ignore
 */
require_once ASCMS_FRAMEWORK_PATH.'/Image.class.php';
require_once ASCMS_MODULE_PATH.'/calendar/lib/calendarLib.class.php';
require_once ASCMS_MODULE_PATH.'/calendar/lib/series.class.php';

/**
 * Headline news
 *
 * Gets all the calendar headlines
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @access      public
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_calendar
 */
class calHeadlines extends calendarLibrary
{
    public $_pageContent;
    public $_objTemplate;
    public $objSeries;
    public $category;


    /**
     * Constructor php5
     */
    function __construct($pageContent) {
        $this->_pageContent = $pageContent;
        $this->_objTemplate = new HTML_Template_Sigma('.');
        CSRF::add_placeholder($this->_objTemplate);
    }


    function getHeadlines()
    {
        global $_CONFIG;

        //get startdates
        $day     = isset($_REQUEST['dayID']) ? $_REQUEST['dayID'] : date('d', mktime());
        $month     = isset($_REQUEST['monthID']) ? $_REQUEST['monthID'] : date('m', mktime());
        $year     = isset($_REQUEST['yearID']) ? $_REQUEST['yearID'] : date('Y', mktime());
        $startdate = mktime(0, 0, 0, $month, $day, $year);
        $enddate = mktime(23, 59, 59, $month, $day, $year+10);

        //get category      
        if ($_CONFIG['calendarheadlinescat'] != 0) {
            $this->category = $_CONFIG['calendarheadlinescat'];
        } else {
            $this->category = null;
        }

        //check access
        $auth = $this->_checkAccess();
        //get maxsize
        $count = $_CONFIG['calendarheadlinescount'];
        
        //get events list
        $this->objSeries     = new seriesManager();
        $this->eventList     = $this->objSeries->getEventList($startdate,$enddate,$count, $auth, null, $this->category, true);
        
        
        //generate list
        $this->_showList();
        return $this->_objTemplate->get();
    }


    function _showList()
    {
        global $_CONFIG;

        $this->_objTemplate->setTemplate($this->_pageContent,true,true);

        if ($_CONFIG['calendarheadlines']) {
            if (!empty($this->eventList)) {
                $i = 0;
                foreach ($this->eventList as $key => $array) {
                    if (strlen($array['comment']) > 100) {
                        $points = '...';
                    } else {
                        $points = '';
                    }
                    $category     = isset($this->category) ? '&amp;catid='.intval($this->category) : '';
                    $link = 'index.php?section=calendar&amp;cmd=event'.$category.'&amp;id='.intval($key);

                    $parts= explode("\n", wordwrap($array['comment'], 100, "\n"));
                    $this->_objTemplate->setVariable(array(
                        'CALENDAR_EVENT_ENDTIME'       => date('H:i', $array['enddate']),
                        'CALENDAR_EVENT_ENDDATE'       => date(ASCMS_DATE_SHORT_FORMAT, $array['enddate']),
                        'CALENDAR_EVENT_STARTTIME'     => date('H:i', $array['startdate']),
                        'CALENDAR_EVENT_STARTDATE'     => date(ASCMS_DATE_SHORT_FORMAT, $array['startdate']),
                        'CALENDAR_EVENT_NAME'          => htmlentities($array['name'], ENT_QUOTES, CONTREXX_CHARSET),
                        'CALENDAR_EVENT_THUMB'         =>
                            '<img src="'.ImageManager::getThumbnailFilename(
                            $array['pic']).'" border="0" alt="'.
                            htmlentities($array['name'], ENT_QUOTES, CONTREXX_CHARSET).
                            '" />',
                        'CALENDAR_EVENT_THUMB_SOURCE'  => $array['pic'],
                        'CALENDAR_EVENT_DETAIL_LINK'   => $link,
                        'CALENDAR_EVENT_ID'            => $key.$category,
                        'CALENDAR_EVENT_COMMENT'       => $array['comment'],
                        'CALENDAR_EVENT_SHORT_COMMENT' => $parts[0].$points,
                        'CALENDAR_EVENT_ROW'           => (++$i % 2 ? 'row1' : 'row2'),
                    ));
                    $this->_objTemplate->parse('calendar_headlines_row');
                }
            }
        } else {
            $this->_objTemplate->hideBlock('calendar_headlines_row');
        }
    }

}

?>
