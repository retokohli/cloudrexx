<?php

/**
 * Calendar headline news
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_calendar
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Headline news
 * Gets all the calendar headlines
 *
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
        global $_CONFIG, $_ARRAYLANG, $objInit, $plainSection;

        // load language data of calendar, if the request was made to an other page/section than the calendar module
        if ($plainSection != 'calendar') {
            $_ARRAYLANG = array_merge($_ARRAYLANG, $objInit->loadLanguageData('calendar'));
        }

        $this->_objTemplate->setTemplate($this->_pageContent,true,true);

        if ($_CONFIG['calendarheadlines']) {
            if (!empty($this->eventList)) {
                $i = 0;
                foreach ($this->eventList as $key => $array) {
                    //priority
                    switch ($array['priority'] ){
                        case 1:
                            $priority	 	= $_ARRAYLANG['TXT_CALENDAR_PRIORITY_VERY_HEIGHT'];
                            $priorityImg	= "<img src='images/modules/calendar/very_height.gif' border='0' title='".$_ARRAYLANG['TXT_CALENDAR_PRIORITY_VERY_HEIGHT']."' alt='".$_ARRAYLANG['TXT_CALENDAR_PRIORITY_VERY_HEIGHT']."' />";
                            break;
                        case 2:
                            $priority	 	= $_ARRAYLANG['TXT_CALENDAR_PRIORITY_HEIGHT'];
                            $priorityImg	= "<img src='images/modules/calendar/height.gif' border='0' title='".$_ARRAYLANG['TXT_CALENDAR_PRIORITY_HEIGHT']."' alt='".$_ARRAYLANG['TXT_CALENDAR_PRIORITY_HEIGHT']."' />";
                            break;
                        case 3:
                            $priority	 	= $_ARRAYLANG['TXT_CALENDAR_PRIORITY_NORMAL'];
                            $priorityImg	= "&nbsp;";
                            break;
                        case 4:
                            $priority	 	= $_ARRAYLANG['TXT_CALENDAR_PRIORITY_LOW'];
                            $priorityImg	= "<img src='images/modules/calendar/low.gif' border='0' title='".$_ARRAYLANG['TXT_CALENDAR_PRIORITY_LOW']."' alt='".$_ARRAYLANG['TXT_CALENDAR_PRIORITY_LOW']."' />";
                            break;
                        case 5:
                            $priority	 	= $_ARRAYLANG['TXT_CALENDAR_PRIORITY_VERY_LOW'];
                            $priorityImg	= "<img src='images/modules/calendar/very_low.gif' border='0' title='".$_ARRAYLANG['TXT_CALENDAR_PRIORITY_VERY_LOW']."' alt='".$_ARRAYLANG['TXT_CALENDAR_PRIORITY_VERY_LOW']."' />";
                            break;
                    }

                    if (strlen($array['comment']) > 100) {
                        $points = '...';
                    } else {
                        $points = '';
                    }
                    $parts= explode("\n", wordwrap($array['comment'], 100, "\n"));

                    $category     = isset($this->category) ? '&amp;catid='.intval($this->category) : '';
                    $link = 'index.php?section=calendar&amp;cmd=event'.$category.'&amp;id='.intval($key);

                    $pic_thumb_name = ImageManager::getThumbnailFilename(
                        $array['pic']);
                    $map_thumb_name = ImageManager::getThumbnailFilename(
                        $array['placeMap']);

                    $arrInfo          = getimagesize(ASCMS_PATH.$array['placeMap']); //ermittelt die Gr��e des Bildes
                    $picWidth         = $arrInfo[0]+20;
                    $picHeight        = $arrInfo[1]+20;
                    $attachNamePos    = strrpos($array['attachment'], '/');
                    $attachNamelength = strlen($array['attachment']);
                    $attachName       = substr($array['attachment'], $attachNamePos+1, $attachNamelength);

                    $this->_objTemplate->setVariable(array(
                        'CALENDAR_EVENT_ROW'                  => (++$i % 2 ? 'row1' : 'row2'),
                        'CALENDAR_EVENT_DETAIL_LINK'          => $link,

                        'CALENDAR_EVENT_ID'                   => $key.$category,
                        'CALENDAR_EVENT_TITLE'                => contrexx_raw2xhtml($array['name']),
                        'CALENDAR_EVENT_DESCRIPTION'          => $array['comment'],
                        'CALENDAR_EVENT_SHORT_DESCRIPTION'    => $parts[0].$points,
                        'CALENDAR_EVENT_CATEGORY'             => $this->getCategoryNameFromCategoryId($array['catid']), # cat name

                        'CALENDAR_EVENT_START'                => date(ASCMS_DATE_FORMAT_DATETIME, $array['startdate']),
                        'CALENDAR_EVENT_END'                  => date(ASCMS_DATE_FORMAT_DATETIME, $array['enddate']),
                        'CALENDAR_EVENT_START_SHOW'           => date(ASCMS_DATE_FORMAT_DATE, $array['startdate']),
                        'CALENDAR_EVENT_END_SHOW'             => date(ASCMS_DATE_FORMAT_DATE, $array['enddate']),
                        'CALENDAR_EVENT_START_TIME'           => date('H:i', $array['startdate']),
                        'CALENDAR_EVENT_END_TIME'             => date('H:i', $array['enddate']),

                        'CALENDAR_EVENT_LINK'                 => $array['link'] != '' ? "<a href='".$array['link']."' target='_blank' >".$array['link']."</a>" : "",
                        'CALENDAR_EVENT_LINK_SOURCE'          => $array['link'],

                        'CALENDAR_EVENT_PIC_THUMBNAIL'        => $array['pic'] != '' ? "<img src='".$pic_thumb_name."' border='0' alt='".contrexx_raw2xhtml($array['name'])."' />" : "",
                        'CALENDAR_EVENT_PIC_SOURCE'           => $array['pic'],
                        'CALENDAR_EVENT_PIC'                  => $array['pic'] != '' ? "<img src='".$array['pic']."' border='0' alt='".contrexx_raw2xhtml($array['name'])."' />" : "",

                        'CALENDAR_EVENT_SOURCE_ATTACHMENT'    => $array['attachment'],
                        'CALENDAR_EVENT_ATTACHMENT'           => $array['attachment'] != '' ? "<a href='".$array['attachment']."' target='_blank' >".$attachName."</a>" : "",

                        'CALENDAR_EVENT_PLACE'                => contrexx_raw2xhtml($array['placeName']),
                        'CALENDAR_EVENT_PLACE_STREET_NR'      => contrexx_raw2xhtml($array['placeStreet']),
                        'CALENDAR_EVENT_PLACE_ZIP'            => contrexx_raw2xhtml($array['placeZip']),
                        'CALENDAR_EVENT_PLACE_CITY'           => contrexx_raw2xhtml($array['placeCity']),
                        'CALENDAR_EVENT_PLACE_LINK'           => $array['placeLink'] != '' ? "<a href='".$array['placeLink']."' target='_blank' >".$array['placeLink']."</a>" : "",
                        'CALENDAR_EVENT_PLACE_LINK_SOURCE'    => contrexx_raw2xhtml($array['placeLink']),
                        'CALENDAR_EVENT_PLACE_MAP_LINK'       => $array['placeMap'] != '' ? '<a href="'.$array['placeMap'].'" onClick="window.open(this.href,\'\',\'resizable=no,location=no,menubar=no,scrollbars=no,status=no,toolbar=no,fullscreen=no,dependent=no,width='.$picWidth.',height='.$picHeight.',status\'); return false">'.$_ARRAYLANG['TXT_CALENDAR_MAP'].'</a>' : "",
                        'CALENDAR_EVENT_PLACE_MAP_THUMBNAIL'  => $array['placeMap'] != '' ? '<a href="'.$array['placeMap'].'" onClick="window.open(this.href,\'\',\'resizable=no,location=no,menubar=no,scrollbars=no,status=no,toolbar=no,fullscreen=no,dependent=no,width='.$picWidth.',height='.$picHeight.',status\'); return false"><img src="'.$map_thumb_name.'" border="0" alt="'.$array['placeName'].'" /></a>' : "",
                        'CALENDAR_EVENT_PLACE_MAP_SOURCE'     => $array['placeMap'],

                        'CALENDAR_EVENT_ORGANIZER'            => contrexx_raw2xhtml($array['organizerName']),
                        'CALENDAR_EVENT_ORGANIZER_STREET_NR'  => contrexx_raw2xhtml($array['organizerStreet']),
                        'CALENDAR_EVENT_ORGANIZER_PLACE'      => contrexx_raw2xhtml($array['organizerPlace']),
                        'CALENDAR_EVENT_ORGANIZER_ZIP'        => contrexx_raw2xhtml($array['organizerZip']),
                        'CALENDAR_EVENT_ORGANIZER_LINK_SOURCE'=> contrexx_raw2xhtml($array['organizerLink']),
                        'CALENDAR_EVENT_ORGANIZER_LINK'       => $array['organizerLink'] != '' ? "<a href='".$array['organizerLink']."' target='_blank' >".$array['organizerLink']."</a>" : "",
                        'CALENDAR_EVENT_ORGANIZER_MAIL_SOURCE'=> contrexx_raw2xhtml($array['organizerMail']),
                        'CALENDAR_EVENT_ORGANIZER_MAIL'       => $array['organizerMail'] != '' ? "<a href='mailto:".$array['organizerMail']."' >".$array['organizerMail']."</a>" : "",

                        'CALENDAR_EVENT_ACCESS'               => $array['access'] == 1 ? $_ARRAYLANG['TXT_CALENDAR_ACCESS_COMMUNITY'] : $_ARRAYLANG['TXT_CALENDAR_ACCESS_PUBLIC'],
                        'CALENDAR_EVENT_PRIORITY' 			  => $priority,
                        'CALENDAR_EVENT_PRIORITY_IMG' 		  => $priorityImg,

                        ////////////////////////////////////////////
                        // ALIASES for backwards compatibility
                        ////////////////////////////////////////////
                        // alias for CALENDAR_EVENT_START_SHOW
                        'CALENDAR_EVENT_STARTDATE'            => date(ASCMS_DATE_FORMAT_DATE, $array['startdate']),
                        // alias for CALENDAR_EVENT_START_TIME
                        'CALENDAR_EVENT_STARTTIME'            => date('H:i', $array['startdate']),
                        // alias for CALENDAR_EVENT_END_SHOW
                        'CALENDAR_EVENT_ENDDATE'              => date(ASCMS_DATE_FORMAT_DATE, $array['enddate']),
                        // alias for CALENDAR_EVENT_ENT_TIME
                        'CALENDAR_EVENT_ENDTIME'              => date('H:i', $array['enddate']),
                        // alias for CALENDAR_EVENT_TITLE
                        'CALENDAR_EVENT_NAME'                 => contrexx_raw2xhtml($array['name']),
                        // alias for CALENDAR_EVENT_PIC_THUMBNAIL
                        'CALENDAR_EVENT_THUMB'                => $array['pic'] != '' ? "<img src='".$pic_thumb_name."' border='0' alt='".contrexx_raw2xhtml($array['name'])."' />" : "",
                        // alias for CALENDAR_EVENT_PIC_SOURCE
                        'CALENDAR_EVENT_THUMB_SOURCE'         => $array['pic'],
                        // alias for CALENDAR_EVENT_DESCRIPTION
                        'CALENDAR_EVENT_COMMENT'              => $array['comment'],
                        // alias for CALENDAR_EVENT_SHORT_DESCRIPTION
                        'CALENDAR_EVENT_SHORT_COMMENT'        => $parts[0].$points,
                        // alias for CALENDAR_EVENT_CATEGORY
                        'CALENDAR_EVENT_CATEGORIE'            => $this->getCategoryNameFromCategoryId($array['catid']), # backwards comp.
                    ));
                    $this->_objTemplate->parse('calendar_headlines_row');
                }
            }
        } else {
            $this->_objTemplate->hideBlock('calendar_headlines_row');
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

