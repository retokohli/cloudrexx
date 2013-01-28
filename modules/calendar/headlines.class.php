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
    public $_objTpl;
    public $objSeries;
    public $category;


    /**
     * Constructor php5
     */
    function __construct($pageContent) {
        $this->_pageContent = $pageContent;
        $this->_objTpl = new \Cx\Core\Html\Sigma('.');
        CSRF::add_placeholder($this->_objTpl);
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
        //$this->_showThreeBoxes();
        //$this->_boxesEventList();
        return $this->_objTpl->get();
    }


    function _showList()
    {
        global $_CONFIG;

        $this->_objTpl->setTemplate($this->_pageContent,true,true);

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
                    $this->_objTpl->setVariable(array(
                        'CALENDAR_EVENT_ENDTIME'       => date('H:i', $array['enddate']),
                        'CALENDAR_EVENT_ENDDATE'       => date(ASCMS_DATE_FORMAT_DATE, $array['enddate']),
                        'CALENDAR_EVENT_STARTTIME'     => date('H:i', $array['startdate']),
                        'CALENDAR_EVENT_STARTDATE'     => date(ASCMS_DATE_FORMAT_DATE, $array['startdate']),
                        'CALENDAR_EVENT_NAME'          => htmlentities($array['name'], ENT_QUOTES, CONTREXX_CHARSET),
                        'CALENDAR_EVENT_THUMB'         =>
                            '<img src="'.ImageManager::getThumbnailFilename(
                            $array['pic']).'" alt="'.
                            htmlentities($array['name'], ENT_QUOTES, CONTREXX_CHARSET).
                            '" />',
                        'CALENDAR_EVENT_THUMB_SOURCE'  => $array['pic'],
                        'CALENDAR_EVENT_DETAIL_LINK'   => $link,
                        'CALENDAR_EVENT_ID'            => $key.$category,
                        'CALENDAR_EVENT_COMMENT'       => $array['comment'],
                        'CALENDAR_EVENT_SHORT_COMMENT' => $parts[0].$points,
                        'CALENDAR_EVENT_ROW'           => (++$i % 2 ? 'row1' : 'row2'),
                    ));
                    $this->_objTpl->parse('calendar_headlines_row');
                }
            }
        } else {
            $this->_objTpl->hideBlock('calendar_headlines_row');
        }
    }
    
    
    function showThreeBoxes()
    {
        global $_ARRAYLANG, $_LANGID, $objDatabase;

        $this->url = CONTREXX_DIRECTORY_INDEX."?section=calendar&cmd=boxes&act=list";
	$this->monthnavurl = CONTREXX_DIRECTORY_INDEX."?section=calendar&cmd=boxes";

	$this->_objTpl->setTemplate($this->_pageContent);

        if (empty($_GET['catid'])) {
            $catid = 0;
        } else {
            $catid = $_GET['catid'];
        }

	if (isset($_GET['yearID']) && isset($_GET['monthID']) &&  isset($_GET['dayID'])) {
            $day 	= $_GET['dayID'];
            $month 	= $_GET['monthID'];
            $year 	= $_GET['yearID'];
	} elseif (isset($_GET['yearID']) && isset($_GET['monthID']) && !isset($_GET['dayID'])) {
            $day 	= 0;
            $month 	= $_GET['monthID'];
            $year 	= $_GET['yearID'];
	} elseif (isset($_GET['yearID']) && !isset($_GET['monthID']) && !isset($_GET['dayID'])) {
            $day 	= 0;
            $month 	= 0;
            $year 	= $_GET['yearID'];
	} else {
            $day 	= date("d");
            $month 	= date("m");
            $year 	= date("Y");
	}

	$calendarbox 	= $this->getBoxes(3, $year, $month, $day, $catid);
        $requestUri = '';
	$java_script  = "<script language=\"JavaScript\" type=\"text/javascript\">\n<!--\nfunction goTo()\n{\nwindow.location.href = \"". CSRF::enhanceURI(CONTREXX_DIRECTORY_INDEX."?section=calendar"). "&catid=".$catid."&month=\"+document.goToForm.goToMonth.value+\"&year=\"+document.goToForm.goToYear.value;\n}\n\n\n";
	$java_script .= "function categories()\n{\nwindow.location.href = \"".CSRF::enhanceURI($requestUri)."&catid=\"+document.selectCategory.inputCategory.value;\n}\n// -->\n</script>";
        
	/*$this->_objTpl->setVariable(array(
		"CALENDAR"              => $calendarbox,
		"JAVA_SCRIPT"      	=> $java_script,
		"TXT_CALENDAR_ALL_CAT"	=> $_ARRAYLANG['TXT_CALENDAR_ALL_CAT'],
		"CALENDAR_CATEGORIES"	=> $this->category_list($catid),
		"CALENDAR_JAVASCRIPT"	=> $this->getJS()
	));*/
        
        
        return '<div class="calendar-headlines">
                    <div id="calendar-boxes">'.$calendarbox.'</div>
                </div>';
    }
    
    
    /**
     * javascript block uesd for the tree bock
     * 
     * @return string        
     */
    function getJS()
    {
    return '<script type="text/javascript">
            /* <![CDATA[ */
		function changecat()
		{
                    var href = window.location.href;
                    var catid = document.getElementById("selectcat").categories.value;
                    href = href.replace(/&catid=[0-9]+/g, \'\');
                    href = href.replace(/&act=search/g, \'\');
                    href += "&catid=" + catid;
                    href += "&'.CSRF::param().'";
                    window.location.href = href;
		}
            /* ]]> */
	</script>';
    }
}

?>
