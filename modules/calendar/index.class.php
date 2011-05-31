<?php
/**
 * Calendar
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_calendar".$this->mandateLink."
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 *
 */

if (CALENDAR_MANDATE == 1) {
    require_once ASCMS_MODULE_PATH . '/calendar/lib/calendarLib.class.php';
} else {
    require_once ASCMS_MODULE_PATH . '/calendar'.CALENDAR_MANDATE.'/lib/calendarLib.class.php';
}

require_once ASCMS_MODULE_PATH . '/calendar/lib/series.class.php';
require_once ASCMS_LIBRARY_PATH.'/FRAMEWORK/Image.class.php';

/**
 * Calendar
 *
 * Class to manage cms calendar
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @access      public
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_calendar".$this->mandateLink."
 */
class Calendar extends calendarLibrary
{
	var $objSeries;
	//var $eventList = array();
   /**
     * XML parser handle
     *
     * @var  array
     * @see  xml_parser_create()
     */


    /**
     * Constructor
     *
     * Construct the Calendar functions
     *
     * @access  public
     * @param string $pageContent
     */
    function __construct($pageContent)
    {
    	global $_ARRAYLANG;

        parent::__construct($_SERVER['SCRIPT_NAME']."index.php?section=calendar");
        $this->pageContent = $pageContent;
    }

    /**
     * Get Calendar Page
     *
     * Standard function, called by the index
     * file
     *
     * @access 	public
     */
    function getCalendarPage()
    {
    	if (!isset($_REQUEST['cmd'])) {
    		$_REQUEST['cmd'] = '';
    	}
        CSRF::add_code();

    	$this->_getEventList();

    	switch($_REQUEST['cmd']) {
    		case 'event':
    			$key 	= intval($_REQUEST['id']);
    			$id 	= intval($this->eventList[$key]['id']);

				//check export
				if(isset($_REQUEST['export'])){
					switch($_REQUEST['export']){
						case 'iCal':
			    			if($key > 0){
				    			$this->_iCalExport('event', $key);
			    			}
							break;

						case 'category':
							if ($key > 0) {
								$this->_iCalExport('category', $key);
							}
							break;
						case 'all':
							$this->_iCalExport('all');
							break;
						default:
							// do nothing
							break;
					}
				}else{
			    	return $this->_showEvent($key, $id);
				}
		    	break;
	    	case 'addevent':
                CSRF::check_code();
                return $this->_showAddEventForm();
                break;
		    case 'eventlist':
				return $this->_showEventList();
		    	break;

		    case 'sign':
				return $this->_showRegistrationForm();
		    	break;

		    case 'boxes':
		    	if ($_GET['act'] == "list") {
		    		return $this->_boxesEventList();
		    	} else {
		    		return $this->_showThreeBoxes();
		    	}
		    	break;

		    default:
		        return $this->_showStandardView();
		        break;
    	}
    }

    function _getEventList()
    {
    	global $_CONFIG;

    	//get startdates
    	if (empty($_POST['startDate'])) {
    		$day 	= isset($_REQUEST['dayID']) ? $_REQUEST['dayID'] : date("d", mktime());
    		$month 	= isset($_REQUEST['monthID']) ? $_REQUEST['monthID'] : date("m", mktime());
    		$year 	= isset($_REQUEST['yearID']) ? $_REQUEST['yearID'] : date("Y", mktime());

    		if($_GET['cmd'] == 'boxes'){
    		    if(empty($_GET['act']) || empty($_REQUEST['dayID'])) {
    	    		$day = 1;
    		    }
    		}

    		$startdate = mktime(0, 0, 0, $month, $day, $year);
    	} else {
    		$datearr = explode("-", $_POST['startDate']);
    		$startdate = mktime(0, 0, 0, $datearr[1], $datearr[2], $datearr[0]);
    		unset($datearr);
    	}

    	//get enddates
        if (empty($_POST['endDate'])) {
            $day     = isset($_REQUEST['dayEndID']) ? $_REQUEST['dayEndID'] : (isset($_REQUEST['dayID']) ? $_REQUEST['dayID'] : date("d", mktime()));
            $month     = isset($_REQUEST['monthEndID']) ? $_REQUEST['monthEndID'] : (isset($_REQUEST['monthID']) ? $_REQUEST['monthID'] : date("m", mktime()));
            $year     = isset($_REQUEST['yearEndID']) ? $_REQUEST['yearEndID'] : (isset($_REQUEST['yearID']) ? $_REQUEST['yearID'] : date("Y", mktime()));

            if($_GET['cmd'] == 'boxes'){
                if(empty($_GET['act'])){
                    $month = $month+2;
                    $day = 31;
                } else if ($_GET['act'] == 'list') {
                    $day     = isset($_REQUEST['dayID']) ? $_REQUEST['dayID'] : date("t", mktime(0, 0, 0, $month, $day, $year));
                    $month     = isset($_REQUEST['monthID']) ? $_REQUEST['monthID'] : date("m", mktime());
                    $year     = isset($_REQUEST['yearID']) ? $_REQUEST['yearID'] : date("Y", mktime());
                }

                $enddate = mktime(23, 59, 59, $month, $day, $year);
            } else {
                if(empty($_REQUEST['yearEndID']) && empty($_REQUEST['monthEndID'])) {
                    $year = $year+10;
                }

                $enddate = mktime(23, 59, 59, $month, $day, $year);
            }
        } else {
            $datearr = explode("-", $_POST['endDate']);
            $enddate = mktime(23, 59, 59, $datearr[1], $datearr[2], $datearr[0]);
            unset($datearr);
        }

    	//get search term
    	if (isset($_REQUEST['keyword'])) {
    		$term = contrexx_addslashes($_REQUEST['keyword']);
    	} else {
    		$term = null;
    	}

    	//check access
        $auth = $this->_checkAccess();

        //get category
        if (!empty($_GET['catid']) && $_GET['catid'] != 0) {
    		$category = intval($_GET['catid']);
    	} else {
    		$category = null;
    	}

    	//get maxsize for boxes
    	if($_GET['cmd'] == 'boxes'){
    		$count = 999;
    	} else {
    		$count = $_CONFIG['calendar'.$this->mandateLink.'defaultcount'];
    	}

        //get events list
        $this->objSeries 	= new seriesManager();
		$this->eventList 	= $this->objSeries->getEventList($startdate,$enddate,$count, $auth, $term, $category, true);

    }



    function _showStandardView()
    {
    	global $objDatabase, $_ARRAYLANG, $_CONFIG, $_LANGID;

    	$this->url = CONTREXX_DIRECTORY_INDEX."?section=calendar";
    	$this->_objTpl->setTemplate($this->pageContent);

        JS::activate('cx');
        $code = <<<JSCODE
//adds the datepicker to the date fields
cx.ready(function() {
    var dpOptions = {
        dateFormat: 'dd.mm.yy',
        onSelect: function(dateText, inst) {
            // adjust start or end date to avoid an invalid date range
            var startDate = \$J('input[name=startDate]').datepicker('getDate');
            var endDate = \$J('input[name=endDate]').datepicker('getDate');
            if (startDate > endDate) {
                if (\$J(this).attr('name') == 'startDate') {
                    \$J('input[name=endDate]').datepicker('setDate', dateText);
                } else {
                    \$J('input[name=startDate]').datepicker('setDate', dateText);
                }
            }
        }
    };

    \$J('input[name=startDate]').datepicker(dpOptions);
    \$J('input[name=endDate]').datepicker(dpOptions);
}, true);
JSCODE;
        JS::registerCode($code);

    	$this->_objTpl->setVariable(array(
            "CALENDAR_CATID"            => isset($_GET['catid']) ? intval($_GET['catid']) : 0,
    		"CALENDAR"					=> $calendarbox,
    		"TXT_CALENDAR_ALL_CAT"		=> $_ARRAYLANG['TXT_CALENDAR_ALL_CAT'],
    		"CALENDAR_CATEGORIES"		=> $this->category_list((isset($_GET['catid']) ? $_GET['catid'] : "")),
    		"CALENDAR_JAVASCRIPT"		=> $this->getJS(),
    		"CALENDAR_SEARCHED_KEYWORD" => stripslashes((isset($_POST['keyword']) ? $_POST['keyword'] : "")),
    		"CALENDAR_DATEPICKER_START"	=> $_POST['startDate'],
    		"CALENDAR_DATEPICKER_END"	=> $_POST['endDate'],
    		"TXT_CALENDAR_FROM"			=> $_ARRAYLANG['TXT_CALENDAR_FROM'],
    		"TXT_CALENDAR_TILL"			=> $_ARRAYLANG['TXT_CALENDAR_TILL'],
    		"TXT_CALENDAR_KEYWORD"		=> $_ARRAYLANG['TXT_CALENDAR_KEYWORD'],
    		"TXT_CALENDAR_SEARCH"		=> $_ARRAYLANG['TXT_CALENDAR_SEARCH'],
    		"CALENDAR_LIST_TITLE"		=> $listTitle,

    	));

    	$this->_showList();

    	return $this->_objTpl->get();
    }


    function _showList()
    {
    	global $objDatabase, $_ARRAYLANG, $_LANGID;

    	if (!empty($this->eventList)) {
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

				if (empty($_POST['startDate'])) {
				    $day 	= isset($_REQUEST['dayID']) ? '&amp;dayID='.$_REQUEST['dayID'] : '';
    	    		$month 	= isset($_REQUEST['monthID']) ? '&amp;monthID='.$_REQUEST['monthID'] : '';
    	    		$year 	= isset($_REQUEST['yearID']) ? '&amp;yearID='.$_REQUEST['yearID'] : '';

    	    		if($_GET['cmd'] == 'boxes' && $_GET['act'] == 'list') {
                        $day     = isset($_REQUEST['dayID']) ? '&amp;dayID='.$_REQUEST['dayID'] : '&amp;dayID=1';
                        $month   = isset($_REQUEST['monthID']) ? '&amp;monthID='.$_REQUEST['monthID'] : '&amp;monthID='.date("m", mktime());
                        $year    = isset($_REQUEST['yearID']) ? '&amp;yearID='.$_REQUEST['yearID'] : '&amp;yearID='.date("Y", mktime());
                    }
            	} else {
            		$datearr = explode("-", $_POST['startDate']);
            		$startdate = mktime(0, 0, 0, $datearr[1], $datearr[2], $datearr[0]);

            		$day 	=  '&amp;dayID='.date("d", $startdate);
    	    		$month 	=  '&amp;monthID='.date("m", $startdate);
    	    		$year 	=  '&amp;yearID='.date("Y", $startdate);
            	}

				if (empty($_POST['endDate'])) {
				    $dayEnd 	= isset($_REQUEST['dayEndID']) ? '&amp;dayEndID='.$_REQUEST['dayEndID'] : '';
    	    		$monthEnd 	= isset($_REQUEST['monthEndID']) ? '&amp;monthEndID='.$_REQUEST['monthEndID'] : '';
    	    		$yearEnd 	= isset($_REQUEST['yearEndID']) ? '&amp;yearEndID='.$_REQUEST['yearEndID'] : '';

                    if($_GET['cmd'] == 'boxes' && $_GET['act'] == 'list') {
                        $dayEnd     = isset($_REQUEST['dayID']) ? '&amp;dayEndID='.$_REQUEST['dayID'] : '&amp;dayEndID='.date("t", mktime(0, 0, 0, $month, $day, $year));
                        $monthEnd   = isset($_REQUEST['monthID']) ? '&amp;monthEndID='.$_REQUEST['monthID'] : '&amp;monthEndID='.date("m", mktime());
                        $yearEnd    = isset($_REQUEST['yearID']) ? '&amp;yearEndID='.$_REQUEST['yearID'] : '&amp;yearEndID='.date("Y", mktime());
                    }
            	} else {
            		$datearr = explode("-", $_POST['endDate']);
            		$enddate = mktime(0, 0, 0, $datearr[1], $datearr[2], $datearr[0]);

            		$dayEnd 	=  '&amp;dayEndID='.date("d", $enddate);
    	    		$monthEnd 	=  '&amp;monthEndID='.date("m", $enddate);
    	    		$yearEnd 	=  '&amp;yearEndID='.date("Y", $enddate);
            	}

                $category 	= isset($_REQUEST['catid']) ? '&amp;catid='.intval($_REQUEST['catid']) : '';
            	$term 	    = isset($_REQUEST['keyword']) ? '&amp;keyword='.$_REQUEST['keyword'] : '';

				$link = '<a href="'.CONTREXX_DIRECTORY_INDEX.'?section=calendar&amp;cmd=event'.$year.$month.$day.$yearEnd.$monthEnd.$dayEnd.$term.$category.'&amp;id='.intval($key).'">'.htmlentities($array['name'], ENT_QUOTES, CONTREXX_CHARSET).'</a>';

				$this->_objTpl->setVariable(array(
					'CALENDAR_PRIORITY' 			=> $priority,
					'CALENDAR_PRIORITY_IMG' 		=> $priorityImg,
					'CALENDAR_PLACE' 				=> htmlentities($array['placeName'], ENT_QUOTES, CONTREXX_CHARSET),
					'CALENDAR_TITLE' 				=> htmlentities($array['name'], ENT_QUOTES, CONTREXX_CHARSET),
					'CALENDAR_START'		 		=> date("Y-m-d", $array['startdate']),
					'CALENDAR_END'			 		=> date("Y-m-d", $array['enddate']),
					'CALENDAR_START_SHOW'		 	=> date("d.m.Y", $array['startdate']),
					'CALENDAR_END_SHOW'			 	=> date("d.m.Y", $array['enddate']),
					'CALENDAR_START_TIME'		 	=> date("H:i", $array['startdate']),
					'CALENDAR_END_TIME'			 	=> date("H:i", $array['enddate']),
					"CALENDAR_ROW"	 				=> $i % 2 == 0 ? "row1" : "row2",
					"CALENDAR_ID"	 				=> intval($key),
					"CALENDAR_DETAIL_LINK"	 		=> $link,
                    "CALENDAR_CATEGORIE"            => $this->getCategoryNameFromCategoryId($array['catid']), # backwards comp.
                    "CALENDAR_CATEGORY"             => $this->getCategoryNameFromCategoryId($array['catid']), # cat name
				));

				$i++;

				$this->_objTpl->parse("event");
	    	}
    	} else {
    		$this->_objTpl->setVariable(array(
				"TXT_CALENDAR_NO_EVENTS"	 => $_ARRAYLANG['TXT_CALENDAR_EVENTS_NO'],
			));
    	}
    }


    /**
     * Shows the list with the next events
     */
	function _showEventList()
	{
		global $objDatabase, $_ARRAYLANG, $_CONFIG;
		$this->_objTpl->setTemplate($this->pageContent);

		if(intval($_GET['catid']) == 0){
			$exportLinks = '<a href="'.CONTREXX_DIRECTORY_INDEX.'?section=calendar&amp;cmd=event&amp;export=all"
							   title="'.$_ARRAYLANG['TXT_CALENDAR_EXPORT_ICAL_ALL'].'">
            					'.$_ARRAYLANG['TXT_CALENDAR_EXPORT_ICAL_ALL'].'
            					<img style="padding-top: -1px;" border="0"
            						 src="images/modules/calendar/ical_export.gif"
            						 alt="'.$_ARRAYLANG['TXT_CALENDAR_EXPORT_ICAL_ALL'].'"
            						 title="'.$_ARRAYLANG['TXT_CALENDAR_EXPORT_ICAL_ALL'].'" />
							</a>';

			$exportImg = '<a href="'.CONTREXX_DIRECTORY_INDEX.'?section=calendar&amp;cmd=event&amp;export=all"
							   title="'.$_ARRAYLANG['TXT_CALENDAR_EXPORT_ICAL_ALL'].'">
            					<img border="0"
            						 src="images/modules/calendar/ical_export.gif"
            						 alt="'.$_ARRAYLANG['TXT_CALENDAR_EXPORT_ICAL_ALL'].'"
            						 title="'.$_ARRAYLANG['TXT_CALENDAR_EXPORT_ICAL_ALL'].'" />
							</a>';

		}else{
			$exportLinks = '<a href="'.CONTREXX_DIRECTORY_INDEX.'?section=calendar&amp;cmd=event&amp;export=category&amp;id='.intval($_REQUEST['catid']).'"
							   title="'.$_ARRAYLANG['TXT_CALENDAR_EXPORT_ICAL'].'">
            					'.$_ARRAYLANG['TXT_CALENDAR_EXPORT_ICAL'].'
            					<img style="padding-top: -1px;" border="0"
            						 src="images/modules/calendar/ical_export.gif"
            						 alt="'.$_ARRAYLANG['TXT_CALENDAR_EXPORT_ICAL'].'"
            						 title="'.$_ARRAYLANG['TXT_CALENDAR_EXPORT_ICAL'].'" />
            				</a>';

			$exportImg = '<a href="'.CONTREXX_DIRECTORY_INDEX.'?section=calendar&amp;cmd=event&amp;export=category&amp;id='.intval($_REQUEST['catid']).'"
							   title="'.$_ARRAYLANG['TXT_CALENDAR_EXPORT_ICAL'].'">
            					<img border="0"
            						 src="images/modules/calendar/ical_export.gif"
            						 alt="'.$_ARRAYLANG['TXT_CALENDAR_EXPORT_ICAL'].'"
            						 title="'.$_ARRAYLANG['TXT_CALENDAR_EXPORT_ICAL'].'" />
            				</a>';
		}

		$this->_objTpl->setVariable(array(
			"TXT_CALENDAR_STARTDATE" 		=> $_ARRAYLANG['TXT_CALENDAR_STARTDATE'],
			"TXT_CALENDAR_ENDDATE"	 		=> $_ARRAYLANG['TXT_CALENDAR_ENDDATE'],
			"TXT_CALENDAR_TITLE"	 		=> $_ARRAYLANG['TXT_CALENDAR_TITLE'],
			"TXT_CALENDAR_ALL_CAT"	 		=> $_ARRAYLANG['TXT_CALENDAR_ALL_CAT'],
    		"CALENDAR_CATEGORIES"	 		=> $this->category_list($_GET['catid']),
    		"CALENDAR_JAVASCRIPT"	 		=> $this->getJS(),
    		"CALENDAR_ICAL_EXPORT"   		=> $exportLinks,
    		"CALENDAR_ICAL_EXPORT_IMG"   	=> $exportImg,
		));

		//$this->_getEventList();
		$this->_showList();

		return $this->_objTpl->get();
	}

	function _showThreeBoxes()
	{
		global $_ARRAYLANG, $_LANGID, $objDatabase;

		$this->url = CONTREXX_DIRECTORY_INDEX."?section=calendar&cmd=boxes&act=list";
	    $this->monthnavurl = CONTREXX_DIRECTORY_INDEX."?section=calendar&cmd=boxes";

	    $this->_objTpl->setTemplate($this->pageContent);

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

		$java_script  = "<script language=\"JavaScript\" type=\"text/javascript\">\n<!--\nfunction goTo()\n{\nwindow.location.href = \"". CSRF::enhanceURI(CONTREXX_DIRECTORY_INDEX."?section=calendar"). "&catid=".$_GET['catid']."&month=\"+document.goToForm.goToMonth.value+\"&year=\"+document.goToForm.goToYear.value;\n}\n\n\n";
		$java_script .= "function categories()\n{\nwindow.location.href = \"".CSRF::enhanceURI($requestUri)."&catid=\"+document.selectCategory.inputCategory.value;\n}\n// -->\n</script>";


		$this->_objTpl->setVariable(array(
			"CALENDAR"				=> $calendarbox,
			"JAVA_SCRIPT"      		=> $java_script,
			"TXT_CALENDAR_ALL_CAT"	=> $_ARRAYLANG['TXT_CALENDAR_ALL_CAT'],
			"CALENDAR_CATEGORIES"	=> $this->category_list($_GET['catid']),
			"CALENDAR_JAVASCRIPT"	=> $javascript.$this->getJS()
		));

		return $this->_objTpl->get();
	}


	function _boxesEventList()
	{
		global $_ARRAYLANG, $_LANGID, $objDatabase;

		$this->_objTpl->setTemplate($this->pageContent);

		$this->_objTpl->hideBlock("boxes");

		$this->_showList();

		return $this->_objTpl->get();
	}


    /**
     * Show Event
     *
     * Shows the detailed view of a event...
     * Yet strange stuff
     */
	function _showEvent($key, $id)
	{
		global $_ARRAYLANG;

		if (!isset($_GET['id'])) {
		    if ($this->mandate == 1) {
                CSRF::header("Location: ".CONTREXX_DIRECTORY_INDEX."?section=calendar");
		    } else {
		        CSRF::header("Location: ".CONTREXX_DIRECTORY_INDEX."?section=calendar".$this->mandate);
		    }
			exit;
		}
		$this->_objTpl->setTemplate($this->pageContent);

		$access = $this->getNoteData($id, "show", 0);

		if ($access == true) {
			if (!$this->_checkAccess($id)) {
				CSRF::header("Location: ".CONTREXX_DIRECTORY_INDEX."?section=calendar");
				exit;
			}
		}

		$this->_objTpl->setVariable(array(
			'CALENDAR_START'		 		=> date("Y-m-d", $this->eventList[$key]['startdate']),
			'CALENDAR_END'			 		=> date("Y-m-d", $this->eventList[$key]['enddate']),
			'CALENDAR_START_SHOW'		 	=> date("d.m.Y", $this->eventList[$key]['startdate']),
			'CALENDAR_END_SHOW'			 	=> date("d.m.Y",$this->eventList[$key]['enddate']),
			'CALENDAR_START_TIME'		 	=> date("H:i", $this->eventList[$key]['startdate']),
			'CALENDAR_END_TIME'			 	=> date("H:i", $this->eventList[$key]['enddate']),
			'TXT_CALENDAR_CAT'            	=> $_ARRAYLANG['TXT_CALENDAR_CAT'],
			'TXT_CALENDAR_NEW'            	=> $_ARRAYLANG['TXT_CALENDAR_NEW'],
			'TXT_CALENDAR_NAME'	          	=> $_ARRAYLANG['TXT_CALENDAR_NAME'],
			'TXT_CALENDAR_PLACE'         	=> $_ARRAYLANG['TXT_CALENDAR_PLACE'],
			'TXT_CALENDAR_PRIORITY'	      	=> $_ARRAYLANG['TXT_CALENDAR_PRIORITY'],
			'TXT_CALENDAR_START'          	=> $_ARRAYLANG['TXT_CALENDAR_START'],
			'TXT_CALENDAR_END'            	=> $_ARRAYLANG['TXT_CALENDAR_END'],
			'TXT_CALENDAR_COMMENT'        	=> $_ARRAYLANG['TXT_CALENDAR_COMMENT'],
			'TXT_CALENDAR_LINK'           	=> $_ARRAYLANG['TXT_CALENDAR_INFO'],
			'TXT_CALENDAR_RESET'          	=> $_ARRAYLANG['TXT_CALENDAR_RESET'],
			'TXT_CALENDAR_EVENT' 		  	=> $_ARRAYLANG['TXT_CALENDAR_TERMIN'],
			'TXT_CALENDAR_STREET_NR' 		=> $_ARRAYLANG['TXT_CALENDAR_STREET_NR'],
			'TXT_CALENDAR_ZIP' 		  		=> $_ARRAYLANG['TXT_CALENDAR_ZIP'],
			'TXT_CALENDAR_LINK' 		  	=> $_ARRAYLANG['TXT_CALENDAR_LINK'],
			'TXT_CALENDAR_MAP' 		  		=> $_ARRAYLANG['TXT_CALENDAR_MAP'],
			'TXT_CALENDAR_ORGANIZER' 		=> $_ARRAYLANG['TXT_CALENDAR_ORGANIZER'],
			'TXT_CALENDAR_MAIL' 		  	=> $_ARRAYLANG['TXT_CALENDAR_MAIL'],
			'TXT_CALENDAR_ORGANIZER_NAME' 	=> $_CORELANG['TXT_NAME'],
			'TXT_CALENDAR_TITLE' 			=> $_ARRAYLANG['TXT_CALENDAR_TITLE'],
			'TXT_CALENDAR_ACCESS' 			=> $_ARRAYLANG['TXT_CALENDAR_ACCESS'],
			'TXT_CALENDAR_ATTACHMENT' 		=> $_ARRAYLANG['TXT_CALENDAR_ATTACHMENT'],
			'TXT_CALENDAR_PRIORITY' 		=> $_ARRAYLANG['TXT_CALENDAR_PRIORITY'],
			'TXT_CALENDAR_DATE'            	=> $_ARRAYLANG['TXT_CALENDAR_DATE'],
            'TXT_CALENDAR_BACK'         	=> $_ARRAYLANG['TXT_CALENDAR_BACK'],
            'TXT_CALENDAR_REGISTRATION'     => $_ARRAYLANG['TXT_CALENDAR_REGISTRATION'],
            'TXT_CALENDAR_REGISTRATION_INFO'=> $_ARRAYLANG['TXT_CALENDAR_REGISTRATION_INFO'],
			'CALENDAR_REGISTRATION_LINK'	=> '<a href="'.CONTREXX_DIRECTORY_INDEX.'?section=calendar'.$this->mandateLink.'&amp;cmd=sign&amp;id='.$id.'&amp;date='.$this->eventList[$key]['startdate'].'">'.$_ARRAYLANG['TXT_CALENDAR_REGISTRATION_LINK'].'</a>',

            'CALENDAR_ICAL_EXPORT'      	=> '<a href="'.CONTREXX_DIRECTORY_INDEX.'?section=calendar&amp;cmd=event&amp;export=iCal&amp;id='.$id.'" title="'.$_ARRAYLANG['TXT_CALENDAR_EXPORT_ICAL_EVENT'].'">
            									'.$_ARRAYLANG['TXT_CALENDAR_EXPORT_ICAL_EVENT'].' <img style="padding-top: -1px;" border="0" src="images/modules/calendar/ical_export.gif" alt="'.$_ARRAYLANG['TXT_CALENDAR_EXPORT_ICAL_EVENT'].'" title="'.$_ARRAYLANG['TXT_CALENDAR_EXPORT_ICAL_EVENT'].'" />
            								</a>',
            'CALENDAR_ICAL_EXPORT_IMG'      => '<a href="'.CONTREXX_DIRECTORY_INDEX.'?section=calendar&amp;cmd=event&amp;export=iCal&amp;id='.$id.'" title="'.$_ARRAYLANG['TXT_CALENDAR_EXPORT_ICAL_EVENT'].'">
            									<img border="0" src="images/modules/calendar/ical_export.gif" alt="'.$_ARRAYLANG['TXT_CALENDAR_EXPORT_ICAL_EVENT'].'" title="'.$_ARRAYLANG['TXT_CALENDAR_EXPORT_ICAL_EVENT'].'" />
            								</a>',
        ));



		//delet old series events
        /*if(!empty($this->eventList[$key]['series_type'])) {
            $startdate = mktime(0, 0, 0, date("m", mktime()), date("d", mktime()), date("Y", mktime()));
            $enddate = mktime(23, 59, 59, date("m", mktime()), date("d", mktime()), date("Y", mktime())+10);
            $count = $_CONFIG['calendar'.$this->mandateLink.'defaultcount'];
            $auth = $this->_checkAccess();


            $this->objSeries     = new seriesManager();
            $this->objSeries->getEventList($startdate,$enddate,$count, $auth, null, null, false, false);
            $this->objSeries->updateMainEvent($id);
        }*/

       return $this->_objTpl->get();
	}

	function getJS()
	{
		return 	'<script type="text/javascript">
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

	/**
    * Show Registrations Form
    *
    *
    */
	function _showRegistrationForm()
	{
		global $objDatabase, $_ARRAYLANG, $_CONFIG, $_CORELANG;

		$this->_objTpl->setTemplate($this->pageContent);

		$check = false;

		if (!empty($_POST['id'])){
            CSRF::check_code();
			//insert registration data
			$time	    = mktime();
            $noteId	    = intval($_POST['id']);
			$noteDate	= intval($_POST['date']);
			$type       = intval($_POST['type']);
			$ip		    = "";
			$host 	    = "";

			$query = "INSERT INTO ".DBPREFIX."module_calendar".$this->mandateLink."_registrations   (`note_id`,
			                                                                   `note_date`,
																		       `time`,
																		   	   `host`,
																			   `ip_address`,
																			   `type`)
														   			   VALUES ('$noteId',
														   			   		   '$noteDate',
														   			   		   '$time',
														   			   		   '$host',
														   			   		   '$ip',
																               '$type')";
			$objResultReg = $objDatabase->Execute($query);

			if ($objResultReg !== false) {
				//insertfield data
				$regId = $objDatabase->Insert_ID();

				foreach ($_POST['signForm'] as $fieldId => $fieldData) {

					$fieldData = contrexx_addslashes(contrexx_strip_tags($fieldData));

					$query = "INSERT INTO ".DBPREFIX."module_calendar".$this->mandateLink."_form_data       (`reg_id`,
																				       `field_id`,
																					   `data`)
																   			   VALUES ('$regId',
																   			   		   '$fieldId',
																		               '$fieldData')";
					$objResultFields = $objDatabase->Execute($query);
				}

				if ($objResultFields !== false) {
					//email
					$query = " SELECT id
					           FROM ".DBPREFIX."module_calendar".$this->mandateLink."_form_fields
					           WHERE note_id='".$noteId."'
					           AND `key`='6'
					           LIMIT 1";
				    $objResult = $objDatabase->Execute($query);
					if ($objResult !== false) {
						$mailId = $objResult->fields['id'];
					}

					//firstane
					$query = " SELECT id
					           FROM ".DBPREFIX."module_calendar".$this->mandateLink."_form_fields
					           WHERE note_id='".$noteId."'
					           AND `key`='1'
					           LIMIT 1";
				    $objResult = $objDatabase->Execute($query);
					if ($objResult !== false) {
						$firstnameId = $objResult->fields['id'];
					}

					//lastname
					$query = " SELECT id
					           FROM ".DBPREFIX."module_calendar".$this->mandateLink."_form_fields
					           WHERE note_id='".$noteId."'
					           AND `key`='2'
					           LIMIT 1";
				    $objResult = $objDatabase->Execute($query);
					if ($objResult !== false) {
						$lastnameId = $objResult->fields['id'];
					}

					if (!empty($_POST['userid'])) {
						$userId = intval($_POST['userid']);
						$this->_sendConfirmation($userId, $noteId, $regId);
					} else {
						if (!empty($_POST['signForm'][$mailId])) {
							$this->_sendConfirmation($_POST['signForm'][$mailId], $noteId, $regId);
						}
					}

					$this->_sendNotification($_POST['signForm'][$mailId], $_POST['signForm'][$firstnameId], $_POST['signForm'][$lastnameId], $noteId, $regId);

					$day 	= '&amp;dayID='.date("d", $noteDate);
    	    		$month 	= '&amp;monthID='.date("m", $noteDate);
    	    		$year 	= '&amp;yearID='.date("Y", $noteDate);

					$this->_objTpl->setVariable(array(
						'CALENDAR_REGISTRATIONS_STATUS'          => $_ARRAYLANG['TXT_CALENDAR_REGISTRATION_SUCCESSFUL'],
						'CALENDAR_LINK_BACK'         			 => '<a href="'.CONTREXX_DIRECTORY_INDEX.'?section=calendar'.$this->mandateLink.'&amp;cmd=event&amp;id='.$id.$day.$month.$year.'">'.$_CORELANG['TXT_BACK'].'</a>',
			        ));

			        $this->_objTpl->hideBlock("signForm");
					$this->_objTpl->parse("signStatus");
				}
			}
		} else {
			//get key
			if (isset($_GET['key']) || isset($_GET['id']) ) {

				//get key or id
				if (isset($_GET['key']) && empty($_GET['id'])) {
					$getKey 	= base64_decode($_GET['key']);

					$arrGet		= explode("#", $getKey);

					$noteId		= intval($arrGet[0]);
					$userId		= intval($arrGet[1]);
					$noteKeyGet	= $arrGet[2];
				} elseif (isset($_GET['id']) && empty($_GET['key'])) {
					$noteId		= intval($_GET['id']);
				}

				//get note details
				$query 			= "SELECT `id`, `key`, `public`, `all_groups`, `groups`, `num`
                                    FROM ".DBPREFIX."module_calendar".$this->mandateLink."
                                    WHERE id = '".$noteId."'";

				$objResult 		= $objDatabase->SelectLimit($query, 1);

				$noteKey		= $objResult->fields['key'];
				$noteGroups		= $objResult->fields['groups'];
				$notePublic		= $objResult->fields['public'];
				$noteSubscriber	= $this->_countSubscriber($noteId);

                if (($noteSubscriber < $objResult->fields['num']) || $objResult->fields['num'] == 0 || $objResult->fields['num'] == '') {
                    //check key
                    if ($notePublic == 1 || ($noteKeyGet == $noteKey)) {
                        if (!empty($userId)) {
                            //get user details
                            $objFWUser = FWUser::getFWUserObject();
                            if (($objUser = $objFWUser->objUser->getUser($userId)) && $objUser->getActiveStatus()) {
                                if ($objResult->fields['all_groups']) {
                                    $x=1;
                                } else {
                                    $arrUserGroups  =  $objUser->getAssociatedGroupIds();
                                    $arrNoteGroups  =  explode(";",$noteGroups);

                                    $x=0;
                                    foreach ($arrUserGroups as $arrKey => $groupId){
                                        if (in_array($groupId, $arrNoteGroups)) {
                                            $x++;
                                        }
                                    }
                                }

                                if ($x>0) {
                                    $arrFieldData = array(
                                        '1'     => htmlentities($objUser->getProfileAttribute('firstname'), ENT_QUOTES, CONTREXX_CHARSET),
                                        '2'     => htmlentities($objUser->getProfileAttribute('lastname'), ENT_QUOTES, CONTREXX_CHARSET),
                                        '3'     => htmlentities($objUser->getProfileAttribute('address'), ENT_QUOTES, CONTREXX_CHARSET),
                                        '4'     => htmlentities($objUser->getProfileAttribute('zip'), ENT_QUOTES, CONTREXX_CHARSET),
                                        '5'     => htmlentities($objUser->getProfileAttribute('city'), ENT_QUOTES, CONTREXX_CHARSET),
                                        '6'     => htmlentities($objUser->getEmail(), ENT_QUOTES, CONTREXX_CHARSET),
                                        '7'     => htmlentities($objUser->getProfileAttribute('website'), ENT_QUOTES, CONTREXX_CHARSET),
                                        '8'     => htmlentities($objUser->getProfileAttribute('phone_office'), ENT_QUOTES, CONTREXX_CHARSET),
                                        '9'     => htmlentities($objUser->getProfileAttribute('phone_mobile'), ENT_QUOTES, CONTREXX_CHARSET),
                                        '10'    => htmlentities($objUser->getProfileAttribute('interests'), ENT_QUOTES, CONTREXX_CHARSET),
                                        '11'    => htmlentities($objUser->getProfileAttribute('profession'), ENT_QUOTES, CONTREXX_CHARSET),
                                        '12'    => htmlentities($objUser->getProfileAttribute('company'), ENT_QUOTES, CONTREXX_CHARSET)
                                    );

                                    $this->_objTpl->setVariable(array(
                                        'CALENDAR_USER_ID'          => $userId,
                                    ));

                                    $this->_getFormular($noteId, "frontend", $arrFieldData);

                                    $check = true;
                                }
                            }
                        } else {
                            $this->_getFormular($noteId, "frontend");

                            $check = true;
                        }
                    }

                    if ($check == false) {
                        $this->_objTpl->setVariable(array(
                            'CALENDAR_REGISTRATIONS_STATUS'          => $_ARRAYLANG['TXT_CALENDAR_WRONG_REGISTRATION'],
                            'TXT_CALENDAR_BACK'                      => $_CORELANG['TXT_BACK'],
                        ));

                        $this->_objTpl->hideBlock("signForm");
                        $this->_objTpl->parse("signStatus");
                    }
                } else {
                    $this->_objTpl->setVariable(array(
                        'CALENDAR_REGISTRATIONS_STATUS'          => $_ARRAYLANG['TXT_CALENDAR_REGISTRATION_NO_PLACE'],
                        'TXT_CALENDAR_BACK'                      => $_CORELANG['TXT_BACK'],
                    ));

                    $this->_objTpl->hideBlock("signForm");
                    $this->_objTpl->parse("signStatus");
                }
			}
		}

        return $this->_objTpl->get();
	}


	/**
	 * this function displays the form to add events from within the frontend
	 *
	 * @todo this function is way too huge. split it into subfunctions und make this file smaller
	 *
	 * @return boolean
	 */
    function _showAddEventForm() {
        global $objDatabase, $_ARRAYLANG, $_CORELANG, $_CONFIG, $objFWUser;
        $this->_objTpl->setTemplate($this->pageContent);
        $showForm = true;

/*
        ini_set('display_errors',1);
        error_reporting(E_ALL);
*/
        $objUser = $objFWUser->objUser;


        include_once(ASCMS_CORE_PATH.'/wysiwyg.class.php');
        $AuthorisationFlag = $this->settings->get('fe_entries_ability');
        if($AuthorisationFlag == 0) {
            return $_ARRAYLANG['TXT_CALENDAR_ACTION_NOT_ACTIVATED'];
        } elseif ($AuthorisationFlag == 1 && !$objFWUser->objUser->login(true) && !$objFWUser->checkAuth()) {
            return $_ARRAYLANG['TXT_CALENDAR_NOT_LOGGEDIN'];
        }

        $form = array(
            'catid'         => $_POST['catid'],
            'title'         => $_POST['title'],
            'description'   => $_POST['description'],
            'startdate'     => (isset($_POST['startdate'])) ? $_POST['startdate'] : date("Y-m-d"),
            'enddate'       => (isset($_POST['enddate'])) ? $_POST['enddate'] : date("Y-m-d"),
            'minutes'       => (isset($_POST['minutes'])) ? $_POST['minutes'] : 30,
            'endminutes'    => (isset($_POST['endminutes'])) ? $_POST['endminutes'] : 30,
            'hour'          => (isset($_POST['hour'])) ? $_POST['hour'] : 12,
            'endhour'       => (isset($_POST['endhour'])) ? $_POST['endhour'] : 13,

            'placeName'     => (isset($_POST['placeName'])) ? $_POST['placeName'] : '',
            'placeLink'     => (isset($_POST['placeLink'])) ? $_POST['placeLink'] : '',
            'place'         => (isset($_POST['place'])) ? $_POST['place'] : '',
            'zip'           => (isset($_POST['zip'])) ? $_POST['zip'] : '',
            'street'        => (isset($_POST['street'])) ? $_POST['street'] : '',
            'link'          => (isset($_POST['link'])) ? $_POST['link'] : '',

            'organizer'     => (isset($_POST['organizer'])) ? $_POST['organizer'] : '',
            'organizerid'   => (isset($_POST['organizerid'])) ? $_POST['organizerid'] : false,
            'organizername' => (isset($_POST['organizername'])) ? $_POST['organizername'] : '',
            'organizermail' => (isset($_POST['organizermail'])) ? $_POST['organizermail'] : '',
            'organizerstreet' => (isset($_POST['organizerstreet'])) ? $_POST['organizerstreet'] : '',
            'organizerLink' => (isset($_POST['organizerLink'])) ? $_POST['organizerLink'] : '',
            'organizerplace'=> (isset($_POST['organizerplace'])) ? $_POST['organizerplace'] : '',
            'organizerzip'  => (isset($_POST['organizerzip'])) ? $_POST['organizerzip'] : '',
            'registrations' => (isset($_POST['registrations'])) ? $_POST['registrations'] : false,
            'countregistrations' => (isset($_POST['countregistrations'])) ? $_POST['countregistrations'] : '',
            'accesstype'    => (isset($_POST['accesstype'])) ? $_POST['accesstype'] : 0,
            'pic'           => '',
            'attachment'    => '',
            'notification'  => (isset($_POST['notification'])) ? $_POST['notification'] : false,
            'inputSeriesStatus' => (isset($_POST['inputSeriesStatus']) ? $_POST['inputSeriesStatus'] : false),
            'inputSeriesType'   => (isset($_POST['inputSeriesType']) ? $_POST['inputSeriesType'] : false),
            'inputSeriesWeeklyDays' => (isset($_POST['inputSeriesWeeklyDays']) ? $_POST['inputSeriesWeeklyDays'] : array()),
            'inputSeriesMonthlyMonth_1'    => (isset($_POST['inputSeriesMonthlyMonth_1'])) ? $_POST['inputSeriesMonthlyMonth_1'] : '',
            'inputSeriesMonthlyMonth_2'    => (isset($_POST['inputSeriesMonthlyMonth_2'])) ? $_POST['inputSeriesMonthlyMonth_2'] : '',
            'inputSeriesMonthlyDay'    => (isset($_POST['inputSeriesMonthlyDay'])) ? $_POST['inputSeriesMonthlyDay'] : '',
            'inputSeriesMonthly'    => (isset($_POST['inputSeriesMonthly'])) ? $_POST['inputSeriesMonthly'] : '',
            'inputSeriesMonthlyDayCount' => (isset($_POST['inputSeriesMonthlyDayCount'])) ? $_POST['inputSeriesMonthlyDayCount'] : null,
            'inputSeriesMonthlyWeekday' => (isset($_POST['inputSeriesMonthlyWeekday'])) ? $_POST['inputSeriesMonthlyWeekday'] : null,
            'inputSeriesDailyDays'  => (isset($_POST['inputSeriesDailyDays']) ? $_POST['inputSeriesDailyDays'] : null ),
            'inputSeriesDaily'      => (isset($_POST['inputSeriesDaily']) ? $_POST['inputSeriesDaily'] : null ),
            'inputSeriesDouranceNotes'      => (isset($_POST['inputSeriesDouranceNotes']) ? $_POST['inputSeriesDouranceNotes'] : null ),
            'inputSeriesDouranceType'      => (isset($_POST['inputSeriesDouranceType']) ? $_POST['inputSeriesDouranceType'] : null ),
            'inputRepeatDouranceEnd'      => (isset($_POST['inputRepeatDouranceEnd']) ? $_POST['inputRepeatDouranceEnd'] : null ),





        );

        if (!empty($form['organizerLink'])) {
            if (!preg_match('%^(?:ftp|http|https):\/\/%', $form['organizerLink'])) {
                $form['organizerLink'] = "http://".$form['organizerLink'];
            }
        }


        $dateparts          = split("-", $form['enddate']);
        $enddate            = mktime(intval($form['endhour']), intval($form['endminutes']),00, $dateparts[1], $dateparts[2], $dateparts[0]);
        $dateparts          = split("-", $form['startdate']);
        $startdate          = mktime(intval($form['hour']), intval($form['minutes']),00, $dateparts[1], $dateparts[2], $dateparts[0]);
        $form['startdateunix'] = $startdate;
        $form['enddateunix'] = $enddate;

        try {
            if(!empty($_POST['saveNewEvent'])) {
                $insertError = false;
                try {
                    $this->_checkUploadTypes('pic', $this->uploadImgTypes);
                    $form['pic'] = $this->_uploadImageIfNot('pic');
                } catch (Exception $e) {
                    $this->_clearUpload('pic');
                    $_ARRAYLANG['TXT_CALENDAR_PICTUREUPLOAD_NOTE'] .= "<br />".$this->errorBox($_ARRAYLANG['TXT_CALENDAR_WRONG_FILETYPE']);
                    $insertError = true;
                }

                try {
                    $this->_checkUploadTypes('attachment', $this->uploadFileTypes);
                    $form['attachment'] = ($this->_uploadImageIfNot('attachment') ? $_SESSION['calendar']['uploadedimagerealname']['attachment'] : '');
                } catch (Exception $e) {
                    $this->_clearUpload('attachment');
                    $_ARRAYLANG['TXT_CALENDAR_ATTACHMENT_NOTE'] .= "<br />".$this->errorBox($_ARRAYLANG['TXT_CALENDAR_WRONG_FILETYPE']);
                    $insertError = true;
                }

                if($insertError) {
                    throw new Exception($_ARRAYLANG['TXT_CALENDAR_GENERAL_ERROR']);
                }

                //these are the essentials
                $boolHasOrganizer = ($form['organizerid'] or (!empty($form['organizer']) && !empty($form['organizermail'])));
                if(empty($form['catid']) or empty($form['title']) or !$boolHasOrganizer) {
                    throw new Exception($_ARRAYLANG['TXT_CALENDAR_INSERT_RESTRICTION']);
                }

                if(!empty($form['pic'])) {
                    $filename = $this->_moveUploadedImage('pic');
                    $form['pic'] = $this->uploadImgWebPath.$filename;
                    $ImageManager = new ImageManager();
                    $pathinfoImg = pathinfo($this->uploadImgPath.$filename);
                    $ImageManager->_createThumbWhq($this->uploadImgPath, $this->uploadImgWebPath, $pathinfoImg['basename'], 400, 500, 100);
                }

                if(!empty($form['attachment'])) {
                    $filename = $this->_moveUploadedImage('attachment');
                    $form['attachment'] = $this->uploadImgWebPath.$filename;
                }

                //cleanup the upload directory
                $this->_cleanupFileUploads();



                /**
                 * @todo put this into a function.. same procedure is used in the backend.. uses way too much codespace
                 */
        		//series pattern
        		$seriesStatus 				= intval($form['inputSeriesStatus']);
        		$seriesType 				= intval($form['inputSeriesType']);
        		$seriesPatternCount			= 0;
        		$seriesPatternWeekday		= 0;
        		$seriesPatternDay			= 0;
        		$seriesPatternWeek			= 0;
        		$seriesPatternMonth			= 0;
        		$seriesPatternType			= 0;
        		$seriesPatternDouranceType	= 0;
        		$seriesPatternEnd			= 0;

        		switch($seriesType) {
        			case 1;
        				if ($seriesStatus == 1) {
        					$seriesPatternType			= intval($form['inputSeriesDaily']);
        					if($seriesPatternType == 1) {
        						$seriesPatternWeekday	= 0;
        						$seriesPatternDay		= intval($form['inputSeriesDailyDays']);;
        					} else {
        						$seriesPatternWeekday	= "1111100";
        						$seriesPatternDay		= 0;
        					}

        					$seriesPatternWeek			= 0;
        					$seriesPatternMonth			= 0;
        					$seriesPatternCount			= 0;

        					$seriesPatternDouranceType	= intval($form['inputSeriesDouranceType']);
        					$dateparts 					= split("-", $startdate);
        					switch($seriesPatternDouranceType) {
        						case 1:
        							$seriesPatternEnd	= 0;
        						break;
        						case 2:
        							$seriesPatternEnd	= intval($form['inputSeriesDouranceNotes']);
        						break;
        						case 3:
        							$dateparts 			= split("-", $form['inputRepeatDouranceEnd']);
        							$seriesPatternEnd	= mktime(00, 00,00, $dateparts[1], $dateparts[2], $dateparts[0]);
        						break;
        					}
        				}
        			break;
        			case 2;
        				if ($seriesStatus == 1) {
        					$seriesPatternWeek			= intval($form['inputSeriesWeeklyWeeks']);

        					for($i=1; $i <= 7; $i++) {
        						if (isset($form['inputSeriesWeeklyDays'][$i])) {
        							$weekdayPattern .= "1";
        						} else {
        							$weekdayPattern .= "0";
        						}
        					}

        					$seriesPatternWeekday		= $weekdayPattern;

        					$seriesPatternCount			= 0;
        					$seriesPatternDay			= 0;
        					$seriesPatternMonth			= 0;
        					$seriesPatternType			= 0;

        					$seriesPatternDouranceType	= intval($form['inputSeriesDouranceType']);
        					$dateparts 					= split("-",$startdate);
        					switch($seriesPatternDouranceType) {
        						case 1:
        							$seriesPatternEnd	= 0;
        						break;
        						case 2:
        							$seriesPatternEnd	= intval($form['inputSeriesDouranceNotes']);
        						break;
        						case 3:
        							$dateparts 			= split("-", $form['inputRepeatDouranceEnd']);
        							$seriesPatternEnd	= mktime(00, 00,00, $dateparts[1], $dateparts[2], $dateparts[0]);
        						break;
        					}
        				}
        			break;
        			case 3;
        				if ($seriesStatus == 1) {
        					$seriesPatternType			= intval($form['inputSeriesMonthly']);
        					if($seriesPatternType == 1) {
        						$seriesPatternMonth		= intval($form['inputSeriesMonthlyMonth_1']);
        						$seriesPatternDay		= intval($form['inputSeriesMonthlyDay']);
        						$seriesPatternWeekday	= 0;
        					} else {
        						$seriesPatternCount		= intval($form['inputSeriesMonthlyDayCount']);
        						$seriesPatternMonth		= intval($form['inputSeriesMonthlyMonth_2']);
        						$seriesPatternWeekday	= $form['inputSeriesMonthlyWeekday'];
        						$seriesPatternDay		= 0;
        					}

        					$seriesPatternWeek			= 0;

        					$seriesPatternDouranceType	= intval($form['inputSeriesDouranceType']);
        					$dateparts 					= split("-", $startdate);
        					switch($seriesPatternDouranceType) {
        						case 1:
        							$seriesPatternEnd	= 0;
        						break;
        						case 2:
        							$seriesPatternEnd	= intval($form['inputSeriesDouranceNotes']);
        						break;
        						case 3:
        							$dateparts 			= split("-", $form['inputRepeatDouranceEnd']);
        							$seriesPatternEnd	= mktime(00, 00,00, $dateparts[1], $dateparts[2], $dateparts[0]);
        						break;
        					}
        				}
        			break;
        		}

                // sorry about this.. i didnt like the naming in the DB sheme
                $eventValues = array(
                    'startdate'     => $form['startdateunix'],
                    'enddate'       => $form['enddateunix'],
                    'access'        => $form['accesstype'],
                    'name'          => $form['title'],
                    'comment'       => $form['description'],
                    'placeName'     => $form['placeName'],
                    'link'          => $form['link'],
                    'pic'           => $form['pic'],
                    'attachment'    => $form['attachment'],
                    'placeStreet'   => $form['street'],
                    'placeZip'      => $form['zip'],
                    'placeCity'     => $form['place'],
                    'placeLink'     => $form['placeLink'],
                    'placeMap'      => '',
                    'organizerName' => $form['organizer'],
                    'organizerStreet'=>$form['organizerstreet'],
                    'organizerZip'  => $form['organizerzip'],
                    'organizerPlace'=> $form['organizerplace'],
                    'organizerMail' => $form['organizermail'],
                    'organizerLink' => $form['organizerLink'],
                    'catid'         => $form['catid'],
                    'key'           => '',
                    'num'           => $form['countregistrations'],
                    'mailTitle'     => '',
                    'mailContent'   => '',
                    'registration'  => $form['registrations'],
                    'groups'        => '',
                    'all_groups'    => 0,
                    'notification'  => $form['notification'],
                    'notificationAddress' => $form['organizermail'],
                    'public '       => '',
                    'series_type'   => $form['inputSeriesType'],
                    'series_status' => $form['inputSeriesStatus'],
                    'series_status' => $seriesStatus,
                    'series_type'   => $seriesType,
                    'series_pattern_count'      => $seriesPatternCount,
                    'series_pattern_weekday'    => $seriesPatternWeekday,
                    'series_pattern_day'        => $seriesPatternDay,
                    'series_pattern_week'       => $seriesPatternWeek,
                    'series_pattern_month'      => $seriesPatternMonth,
                    'series_pattern_type'       => $seriesPatternType,
                    'series_pattern_dourance_type' => $seriesPatternDouranceType,
                    'series_pattern_end'        => $seriesPatternEnd,
                );

                // insert the event.. it has to work
                $eventId = $this->objEvent->insert($eventValues);
                $this->strOkMessage = $_ARRAYLANG['TXT_CALENDAR_FRONTENDSAVEOK'];
                $showForm = false;

            }
        } catch (Exception $e) {
            $this->strErrMessage .= $e->getMessage()."<br>";
        }





        if($showForm) {

            $query = "SELECT id, name, lang
                          FROM ".DBPREFIX."module_calendar".MODULE_INDEX."_categories
                      ORDER BY 'pos'
            ";
            $objResultCat = $objDatabase->Execute($query);
            if ($objResultCat !== false) {
                while (!$objResultCat->EOF) {
                    $query = "SELECT lang
                                FROM ".DBPREFIX."languages
                               WHERE id = '".$objResultCat->fields['lang']."'";
                    $objResultLang = $objDatabase->SelectLimit($query, 1);

                    $this->_objTpl->setVariable(array(
                        'CALENDAR_CAT_ID'       => $objResultCat->fields['id'],
                        'CALENDAR_CAT_LANG'     => $objResultLang->fields['lang'],
                        'CALENDAR_CAT_NAME'     => $objResultCat->fields['name'],
                        'CALENDAR_CAT_SELECTED' => ($objResultCat->fields['id'] == $form['catid']) ? " selected='selected' " : ''
                    ));
                    $this->_objTpl->parse("calendar_cat");
                    $objResultCat->MoveNext();
                }
            }

            $this->selectHour($form['hour'], "hour", "CALENDAR_HOUR_SELECT", "CALENDAR_HOUR");
            $this->selectMinutes($form['minutes'], "minutes", "CALENDAR_MINUTES_SELECT", "CALENDAR_MINUTES");
            $this->selectHour($form['endhour'], "endhour", "CALENDAR_END_HOUR_SELECT", "CALENDAR_END_HOUR");
            $this->selectMinutes($form['endminutes'], "endminutes", "CALENDAR_END_MINUTES_SELECT", "CALENDAR_END_MINUTES");


			$arrWeekdays = array(
				"1000000" => $_ARRAYLANG['TXT_CALENDAR_DAYS_MONDAY'],
				"0100000" => $_ARRAYLANG['TXT_CALENDAR_DAYS_TUESDAY'],
				"0010000" => $_ARRAYLANG['TXT_CALENDAR_DAYS_WEDNESDAY'],
				"0001000" => $_ARRAYLANG['TXT_CALENDAR_DAYS_THURSDAY'],
				"0000100" => $_ARRAYLANG['TXT_CALENDAR_DAYS_FRIDAY'],
				"0000010" => $_ARRAYLANG['TXT_CALENDAR_DAYS_SATURDAY'],
				"0000001" => $_ARRAYLANG['TXT_CALENDAR_DAYS_SUNDAY'],
			);

			$arrCount = array(
				1 => $_ARRAYLANG['TXT_CALENDAR_SERIES_PATTERN_FIRST'],
				2 => $_ARRAYLANG['TXT_CALENDAR_SERIES_PATTERN_SECOND'],
				3 => $_ARRAYLANG['TXT_CALENDAR_SERIES_PATTERN_THIRD'],
				4 => $_ARRAYLANG['TXT_CALENDAR_SERIES_PATTERN_FOURTH'],
				5 => $_ARRAYLANG['TXT_CALENDAR_SERIES_PATTERN_LAST'],
			);

			foreach ($arrWeekdays as $value => $name) {
				$weekdays .= '<option value="'.$value.'" '.($value == $form['inputSeriesMonthlyWeekday'] ? 'selected="selected"': '').'>'.$name.'</option>';
			}

			foreach ($arrCount as $value => $name) {
				$count .= '<option value="'.$value.'" '.($value == $form['inputSeriesMonthlyDayCount'] ? 'selected="selected"': '').'>'.$name.'</option>';
			}

		    //data
		    $this->_objTpl->setVariable(array(
				'CALENDAR_START'			  				=> date("Y-m-d"),
				'CALENDAR_END'				  				=> date("Y-m-d"),
				'CALENDAR_DESCRIPTION'		  				=> $ed,
				'CALENDAR_ACTIVE'		  					=> "checked",
				'CALENDAR_FORM_ACTION'		  				=> "saveNew",
				'CALENDAR_ACCESS_PUBLIC'					=> "selected='selected'",
				'CALENDAR_PRIORITY_NORMAL'					=> "selected='selected'",
				'CALENDAR_REGISTRATIONS_GROUPS_UNSELECTED' 	=> $this->_getUserGroups('', 0),
				'CALENDAR_REGISTRATIONS_GROUPS_SELECTED' 	=> $this->_getUserGroups('', 1),
				'CALENDAR_MAIL_TITLE' 						=> $mailTitle,
				'CALENDAR_MAIL_CONTENT' 					=> $mailContent,
				'CALENDAR_NOTIFICATION_ACTIVATED' 			=> 'checked="checked"',
				'CALENDAR_NOTIFICATION_ADDRESS' 			=> $_CONFIG['coreAdminEmail'],

				'CALENDAR_SERIES_PATTERN_MONTHLY_COUNT' 	=> $count,
				'CALENDAR_SERIES_PATTERN_MONTHLY_WEEKDAY' 	=> $weekdays,
				'CALENDAR_SERIES_PATTERN_DAILY_1' 			=> 'checked="checked"',
				'CALENDAR_SERIES_PATTERN_MONTHLY_1' 		=> 'checked="checked"',
				'CALENDAR_SERIES_PATTERN_DAILY_DAYS' 		=> 1,
				'CALENDAR_SERIES_PATTERN_WEEKLY_WEEKS' 		=> 1,
				'CALENDAR_SERIES_PATTERN_MONTHLY_DAY' 		=> 1,
				'CALENDAR_SERIES_PATTERN_MONTHLY_MONTH_1' 	=> 1,
				'CALENDAR_SERIES_PATTERN_MONTHLY_MONTH_2' 	=> 1,
				'CALENDAR_SERIES_PATTERN_ENDS_AFTER' 		=> 1,
				'CALENDAR_SERIES_PATTERN_START' 			=> date("Y-m-d"),
				'CALENDAR_SERIES_PATTERN_DOURANCE_1' 		=> 'checked="checked"',
		    ));

            $this->_objTpl->setVariable(array(
                'EV_TITLE'          => $form['title'],
                'DESCRIPTION'       => $form['description'],
                'CALENDAR_START'    => $form['startdate'],
                'CALENDAR_END'      => $form['enddate'],
                'PLACENAME'         => $form['placeName'],
                'PLACE'             => $form['place'],
                'ZIP'               => $form['zip'],
                'STREET'            => $form['zip'],
                'ORGANIZER'         => $form['organizer'],
                'ORGANIZERMAIL'     => $form['organizermail'],
                'ORGANIZERZIP'      => $form['organizerzip'],
                'ORGANIZERPLACE'    => $form['organizerplace'],
                'ORGANIZERSTREET'   => $form['organizerstreet'],
                'ORGANIZERID'       => ($form['organizerid']) ? "checked='checked'" : '',
                'USERNAME'          => ($objUser ? $objUser->getProfileAttribute('firstname') ." ". $objUser->getProfileAttribute('lastname') : ''),
                'USERSTREET'        => ($objUser ? $objUser->getProfileAttribute('address') : ''),
                'USERZIP'           => ($objUser ? $objUser->getProfileAttribute('zip') : ''),
                'USERPLACE'         => ($objUser ? $objUser->getProfileAttribute('city') : ''),
                'USERMAIL'          => ($objUser ? $objUser->getEmail() : ''),
                'ACCESSTYPE_'.$form['accesstype'] => " selected='selected' ",
                'REGISTRATIONS'     => ($form['registrations']) ? " checked='checked' " : '',
                'COUNTREGISTRATIONS'=> $form['countregistrations'],
                'UPLOADEDPIC'       => $form['pic'],
                'NOTIFICATION'      => (!empty($form['notification'])) ? "checked='checked'" : '',
                'LINK'              => $form['link'],
                'PLACELINK'         => $form['placeLink'],
                'ORGANIZERLINK'     => $form['organizerLink'],
                'ATTACHMENT'        => $form['attachment'],
                'CALENDAR_SERIES_STATUS'    => ($form['inputSeriesStatus']) ? " checked='checked' " : '',
                'CALENDAR_SERIES_PATTERN_DAILY' => ($form['inputSeriesType'] == 1) ? " selected='selected' " : '',
                'CALENDAR_SERIES_PATTERN_WEEKLY' => ($form['inputSeriesType'] == 2) ? " selected='selected' " : '',
                'CALENDAR_SERIES_PATTERN_MONTHLY' => ($form['inputSeriesType'] == 3) ? " selected='selected' " : '',
                'CALENDAR_SERIES_PATTERN_WEEKLY_MONDAY'     => ($form['inputSeriesWeeklyDays'][1]) ? " checked='checked' " : '',
                'CALENDAR_SERIES_PATTERN_WEEKLY_TUESDAY'    => ($form['inputSeriesWeeklyDays'][2]) ? " checked='checked' " : '',
                'CALENDAR_SERIES_PATTERN_WEEKLY_WEDNESDAY'  => ($form['inputSeriesWeeklyDays'][3]) ? " checked='checked' " : '',
                'CALENDAR_SERIES_PATTERN_WEEKLY_THURSDAY'   => ($form['inputSeriesWeeklyDays'][4]) ? " checked='checked' " : '',
                'CALENDAR_SERIES_PATTERN_WEEKLY_FRIDAY'     => ($form['inputSeriesWeeklyDays'][5]) ? " checked='checked' " : '',
                'CALENDAR_SERIES_PATTERN_WEEKLY_SATURDAY'   => ($form['inputSeriesWeeklyDays'][6]) ? " checked='checked' " : '',
                'CALENDAR_SERIES_PATTERN_WEEKLY_SUNDAY'     => ($form['inputSeriesWeeklyDays'][7]) ? " checked='checked' " : '',
                'CALENDAR_SERIES_PATTERN_MONTHLY_1'         => ($form['inputSeriesMonthly'] == 1) ? " checked='checked' " : '',
                'CALENDAR_SERIES_PATTERN_MONTHLY_2'         => ($form['inputSeriesMonthly'] == 2) ? " checked='checked' " : '',
                'CALENDAR_SERIES_PATTERN_MONTHLY_DAY'       => ($form['inputSeriesMonthlyDay']),
                'CALENDAR_SERIES_PATTERN_MONTHLY_MONTH_1'   => ($form['inputSeriesMonthlyMonth_1']),
                'CALENDAR_SERIES_PATTERN_MONTHLY_MONTH_2'   => ($form['inputSeriesMonthlyMonth_2']),
                'CALENDAR_SERIES_PATTERN_DAILY_1'           => ($form['inputSeriesDaily'] == 1 ? "checked='checked'" : null),
                'CALENDAR_SERIES_PATTERN_DAILY_2'           => ($form['inputSeriesDaily'] == 2 ? "checked='checked'" : null),
                'CALENDAR_SERIES_PATTERN_DAILY_DAYS'        => $form['inputSeriesDailyDays'],
                'CALENDAR_SERIES_PATTERN_DOURANCE_1'        => ($form['inputSeriesDouranceType'] == 1 ? "checked='checked'" : null),
                'CALENDAR_SERIES_PATTERN_DOURANCE_2'        => ($form['inputSeriesDouranceType'] == 2 ? "checked='checked'" : null),
                'CALENDAR_SERIES_PATTERN_DOURANCE_3'        => ($form['inputSeriesDouranceType'] == 3 ? "checked='checked'" : null),
                'CALENDAR_SERIES_PATTERN_ENDS_AFTER'        => $form['inputSeriesDouranceNotes'],
                'CALENDAR_SERIES_PATTERN_ENDS'              => $form['inputRepeatDouranceEnd'],



            ));



            $this->_objTpl->setVariable(array(
    			'TXT_CALENDAR_SERIES_ACTIVATE' 			=> $_ARRAYLANG['TXT_CALENDAR_SERIES_ACTIVATE'],
    			'TXT_CALENDAR_SERIES' 					=> $_ARRAYLANG['TXT_CALENDAR_SERIES'],
    			'TXT_CALENDAR_SERIES_PATTERN_DAILY' 	=> $_ARRAYLANG['TXT_CALENDAR_SERIES_PATTERN_DAILY'],
    			'TXT_CALENDAR_SERIES_PATTERN_WEEKLY' 	=> $_ARRAYLANG['TXT_CALENDAR_SERIES_PATTERN_WEEKLY'],
    			'TXT_CALENDAR_SERIES_PATTERN_MONTHLY' 	=> $_ARRAYLANG['TXT_CALENDAR_SERIES_PATTERN_MONTHLY'],

    			'TXT_CALENDAR_DAYS' 				=> $_ARRAYLANG['TXT_CALENDAR_DAYS'],
    			'TXT_CALENDAR_DAYS_DAY' 			=> $_ARRAYLANG['TXT_CALENDAR_DAYS_DAY'],
    			'TXT_CALENDAR_DAYS_MONDAY' 			=> $_ARRAYLANG['TXT_CALENDAR_DAYS_MONDAY'],
    			'TXT_CALENDAR_DAYS_TUESDAY' 		=> $_ARRAYLANG['TXT_CALENDAR_DAYS_TUESDAY'],
    			'TXT_CALENDAR_DAYS_WEDNESDAY' 		=> $_ARRAYLANG['TXT_CALENDAR_DAYS_WEDNESDAY'],
    			'TXT_CALENDAR_DAYS_THURSDAY' 		=> $_ARRAYLANG['TXT_CALENDAR_DAYS_THURSDAY'],
    			'TXT_CALENDAR_DAYS_FRIDAY' 			=> $_ARRAYLANG['TXT_CALENDAR_DAYS_FRIDAY'],
    			'TXT_CALENDAR_DAYS_SATURDAY' 		=> $_ARRAYLANG['TXT_CALENDAR_DAYS_SATURDAY'],
    			'TXT_CALENDAR_DAYS_SUNDAY' 			=> $_ARRAYLANG['TXT_CALENDAR_DAYS_SUNDAY'],
    			'TXT_CALENDAR_DAYS_WORKDAY' 		=> $_ARRAYLANG['TXT_CALENDAR_DAYS_WORKDAY'],

    			'TXT_CALENDAR_AT' 					=> $_ARRAYLANG['TXT_CALENDAR_AT'],
    			'TXT_CALENDAR_EVERY_1' 				=> $_ARRAYLANG['TXT_CALENDAR_EVERY_1'],
    			'TXT_CALENDAR_ALL' 					=> $_ARRAYLANG['TXT_CALENDAR_ALL'],
    			'TXT_CALENDAR_EVERY_2' 				=> $_ARRAYLANG['TXT_CALENDAR_EVERY_2'],
    			'TXT_CALENDAR_WEEKS' 				=> $_ARRAYLANG['TXT_CALENDAR_WEEKS'],
    			'TXT_CALENDAR_MONTHS' 				=> $_ARRAYLANG['TXT_CALENDAR_MONTHS'],

    			'TXT_CALENDAR_SERIES_PATTERN_BEGINS' 		=> $_ARRAYLANG['TXT_CALENDAR_SERIES_PATTERN_BEGINS'],
    			'TXT_CALENDAR_SERIES_PATTERN_NO_ENDDATE' 	=> $_ARRAYLANG['TXT_CALENDAR_SERIES_PATTERN_NO_ENDDATE'],
    			'TXT_CALENDAR_SERIES_PATTERN_ENDS_AFTER' 	=> $_ARRAYLANG['TXT_CALENDAR_SERIES_PATTERN_ENDS_AFTER'],
    			'TXT_CALENDAR_SERIES_PATTERN_APPONTMENTS' 	=> $_ARRAYLANG['TXT_CALENDAR_SERIES_PATTERN_APPONTMENTS'],
    			'TXT_CALENDAR_SERIES_PATTERN_ENDS' 			=> $_ARRAYLANG['TXT_CALENDAR_SERIES_PATTERN_ENDS'],
                'TXT_PICTUREUPLOAD_NOTE'        => $_ARRAYLANG['TXT_CALENDAR_PICTUREUPLOAD_NOTE'],
                'TXT_ATTACHMENT_NOTE'           => $_ARRAYLANG['TXT_CALENDAR_ATTACHMENT_NOTE'],
                'TXT_PICTURE'                   => $_ARRAYLANG['TXT_CALENDAR_THUMBNAIL'],
                'TXT_TITLE'                     => $_ARRAYLANG['TXT_CALENDAR_TITLE'],
                'TXT_DESCRIPTION'               => $_ARRAYLANG['TXT_CALENDAR_COMMENT'],
                'TXT_ORGANIZER'                 => $_ARRAYLANG['TXT_CALENDAR_ORGANIZER'],
                'TXT_ORGANIZERSTREET'           => $_ARRAYLANG['TXT_CALENDAR_STREET_NR'],
                'TXT_ORGANIZERZIP'              => $_ARRAYLANG['TXT_CALENDAR_ZIP'],
                'TXT_ORGANIZERPLACE'            => $_ARRAYLANG['TXT_CALENDAR_PLACE'],
                'TXT_ORGANIZERMAIL'             => $_ARRAYLANG['TXT_CALENDAR_MAIL'],
                'TXT_PLACE'                     => $_ARRAYLANG['TXT_CALENDAR_PLACE'],
                'TXT_STREET'                    => $_ARRAYLANG['TXT_CALENDAR_STREET_NR'],
                'TXT_ZIP'                       => $_ARRAYLANG['TXT_CALENDAR_ZIP'],
                'TXT_REGISTRATIONS'             => $_ARRAYLANG['TXT_CALENDAR_REGISTRATIONS_ACTIVATED'],
                'TXT_COUNTREGISTRATIONS'        => $_ARRAYLANG['TXT_CALENDAR_REGISTRATIONS_SUBSCRIBER'],
                'TXT_ACCESSTYPE'                => $_ARRAYLANG['TXT_CALENDAR_ACCESS'],
                'TXT_ACCESS_PUBLIC'             => $_ARRAYLANG['TXT_CALENDAR_ACCESS_PUBLIC'],
                'TXT_ACCESS_COMMUNITY'          => $_ARRAYLANG['TXT_CALENDAR_ACCESS_COMMUNITY'],
                'TXT_CALENDAR_NEW'              => $_ARRAYLANG['TXT_CALENDAR_TERMIN'],
                'TXT_REGSUBSCRIBER_NOTE'        => $_ARRAYLANG['TXT_CALENDAR_REG_SUBSCRIBERNOTE'],
                'TXT_MYSELF'                    => $_ARRAYLANG['TXT_CALENDAR_MYSELF'],
                'TXT_LINK'                      => $_ARRAYLANG['TXT_CALENDAR_INFO'],
                'TXT_CALENDAR_NAME'             => $_ARRAYLANG['TXT_CALENDAR_NAME'],
                'TXT_NOTIFICATION'              => $_ARRAYLANG['TXT_CALENDAR_NOTIFICATION_ACTIVATE'],
                'TXT_ATTACHMENT'                => $_ARRAYLANG['TXT_CALENDAR_ATTACHMENT'],
                'TXT_NAME'                      => $_CORELANG['TXT_NAME'],
                'TXT_PLACENAME'                 => $_CORELANG['TXT_NAME'],
                'TXT_NOTIFICATION'              => $_ARRAYLANG['TXT_CALENDAR_NOTIFICATION_ACTIVATE'],
                'TXT_COPYPLACE'                 => $_ARRAYLANG['TXT_CALENDAR_COPYPLACE'],
                'TXT_COPYPLACE_NOTE'            => $_ARRAYLANG['TXT_CALENDAR_COPYPLACE_NOTE'],
                'TXT_SERIES_NOTE'               => $_ARRAYLANG['TXT_CALENDAR_SERIES_NOTE'],

                'TXT_CALENDAR_ERROR_CATEGORY'     => $_ARRAYLANG['TXT_CALENDAR_ERROR_CATEGORY'],
                'TXT_CALENDAR_ACTIVE'              => $_ARRAYLANG['TXT_CALENDAR_ACTIVE'],
                'TXT_CALENDAR_CAT'                => $_ARRAYLANG['TXT_CALENDAR_CAT'],
                'TXT_CALENDAR_ERROR_NAME'         => $_ARRAYLANG['TXT_CALENDAR_ERROR_NAME'],
                'TXT_CALENDAR_ERROR_PLACE'        => $_ARRAYLANG['TXT_CALENDAR_ERROR_PLACE'],
                'TXT_CALENDAR_ERROR_DATE'         => $_ARRAYLANG['TXT_CALENDAR_ERROR_DATE'],
                'TXT_CALENDAR_ERROR_COMMENT'      => $_ARRAYLANG['TXT_CALENDAR_ERROR_COMMENT'],
                'TXT_CALENDAR_PLACE'             => $_ARRAYLANG['TXT_CALENDAR_PLACE'],
                'TXT_CALENDAR_PRIORITY'              => $_ARRAYLANG['TXT_CALENDAR_PRIORITY'],
                'TXT_CALENDAR_START'              => $_ARRAYLANG['TXT_CALENDAR_START'],
                'TXT_CALENDAR_END'                => $_ARRAYLANG['TXT_CALENDAR_END'],
                'TXT_CALENDAR_COMMENT'            => $_ARRAYLANG['TXT_CALENDAR_COMMENT'],
                'TXT_CALENDAR_RESET'              => $_ARRAYLANG['TXT_CALENDAR_RESET'],
                'TXT_CALENDAR_SUBMIT'             => $_ARRAYLANG['TXT_CALENDAR_SUBMIT'],
                'TXT_CALENDAR_WHOLE_DAY'          => $_ARRAYLANG['TXT_CALENDAR_WHOLE_DAY'],
                'TXT_CALENDAR_EVENT'               => $_ARRAYLANG['TXT_CALENDAR_TERMIN'],
                'TXT_CALENDAR_STREET_NR'         => $_ARRAYLANG['TXT_CALENDAR_STREET_NR'],
                'TXT_CALENDAR_ZIP'                   => $_ARRAYLANG['TXT_CALENDAR_ZIP'],
                'TXT_CALENDAR_LINK'               => $_ARRAYLANG['TXT_CALENDAR_LINK'],
                'TXT_CALENDAR_MAP'                   => $_ARRAYLANG['TXT_CALENDAR_MAP'],
                'TXT_CALENDAR_ORGANIZER'         => $_ARRAYLANG['TXT_CALENDAR_ORGANIZER'],
                'TXT_CALENDAR_MAIL'               => $_ARRAYLANG['TXT_CALENDAR_MAIL'],
                'TXT_CALENDAR_ORGANIZER_NAME'     => $_CORELANG['TXT_NAME'],
                'TXT_CALENDAR_TITLE'             => $_ARRAYLANG['TXT_CALENDAR_TITLE'],
                'TXT_CALENDAR_OPTIONS'             => $_ARRAYLANG['TXT_CALENDAR_OPTIONS'],
                'TXT_CALENDAR_THUMBNAIL'         => $_ARRAYLANG['TXT_CALENDAR_THUMBNAIL'],
                'TXT_CALENDAR_BROWSE'             => $_CORELANG['TXT_BROWSE'],
                'TXT_CALENDAR_ACCESS'             => $_ARRAYLANG['TXT_CALENDAR_ACCESS'],
                'TXT_CALENDAR_ACCESS_PUBLIC'     => $_ARRAYLANG['TXT_CALENDAR_ACCESS_PUBLIC'],
                'TXT_CALENDAR_ACCESS_COMMUNITY' => $_ARRAYLANG['TXT_CALENDAR_ACCESS_COMMUNITY'],
                'TXT_CALENDAR_REPEAT_MASK'         => $_ARRAYLANG['TXT_CALENDAR_REPEAT_MASK'],
                'TXT_CALENDAR_REPEAT_DURANCE'     => $_ARRAYLANG['TXT_CALENDAR_REPEAT_DURANCE'],
                'TXT_CALENDAR_PRIORITY'         => $_ARRAYLANG['TXT_CALENDAR_PRIORITY'],
                'TXT_CALENDAR_PRIORITY_VERY_HEIGHT'         => $_ARRAYLANG['TXT_CALENDAR_PRIORITY_VERY_HEIGHT'],
                'TXT_CALENDAR_PRIORITY_HEIGHT'     => $_ARRAYLANG['TXT_CALENDAR_PRIORITY_HEIGHT'],
                'TXT_CALENDAR_PRIORITY_NORMAL'     => $_ARRAYLANG['TXT_CALENDAR_PRIORITY_NORMAL'],
                'TXT_CALENDAR_PRIORITY_LOW'     => $_ARRAYLANG['TXT_CALENDAR_PRIORITY_LOW'],
                'TXT_CALENDAR_PRIORITY_VERY_LOW'=> $_ARRAYLANG['TXT_CALENDAR_PRIORITY_VERY_LOW'],
                'TXT_CALENDAR_REGISTRATIONS'    => $_ARRAYLANG['TXT_CALENDAR_REGISTRATIONS'],
                'TXT_CALENDAR_FORMULAR'            => $_ARRAYLANG['TXT_CALENDAR_FORMULAR'],
                'TXT_CALENDAR_FIELD_TYPE'        => $_ARRAYLANG['TXT_CALENDAR_FIELD_TYPE'],
                'TXT_CALENDAR_FIELD_NAME'        => $_ARRAYLANG['TXT_CALENDAR_FIELD_NAME'],
                'TXT_CALENDAR_FIELD_REQUIRED'    => $_ARRAYLANG['TXT_CALENDAR_FIELD_REQUIRED'],
                'TXT_CALENDAR_FIELD_STATUS'        => $_ARRAYLANG['TXT_CALENDAR_FIELD_STATUS'],
                'TXT_CALENDAR_REGISTRATIONS_ACTIVATED'                => $_ARRAYLANG['TXT_CALENDAR_REGISTRATIONS_ACTIVATED'],
                'TXT_CALENDAR_REGISTRATIONS_ADDRESSER'                => $_ARRAYLANG['TXT_CALENDAR_REGISTRATIONS_ADDRESSER'],
                'TXT_CALENDAR_REGISTRATIONS_SELECT_GROUP'            => $_ARRAYLANG['TXT_CALENDAR_REGISTRATIONS_SELECT_GROUP'],
                'TXT_CALENDAR_REGISTRATIONS_ADDRESSER_ALL'            => $_ARRAYLANG['TXT_CALENDAR_REGISTRATIONS_ADDRESSER_ALL'],
                'TXT_CALENDAR_REGISTRATIONS_ADDRESSER_ALL_USER'        => $_ARRAYLANG['TXT_CALENDAR_REGISTRATIONS_ADDRESSER_ALL_USER'],
                'TXT_CALENDAR_REGISTRATIONS_ADDRESSER_SELECT_GROUP'    => $_ARRAYLANG['TXT_CALENDAR_REGISTRATIONS_ADDRESSER_SELECT_GROUP'],
                'TXT_CALENDAR_MAIL_TEMPLATE'    => $_ARRAYLANG['TXT_CALENDAR_MAIL_TEMPLATE'],
                'TXT_CALENDAR_PLACEHOLDERS'        => $_ARRAYLANG['TXT_CALENDAR_PLACEHOLDERS'],
                'TXT_CALENDAR_FIRSTNAME'        => $_ARRAYLANG['TXT_CALENDAR_FIRSTNAME'],
                'TXT_CALENDAR_LASTNAME'            => $_ARRAYLANG['TXT_CALENDAR_LASTNAME'],
                'TXT_CALENDAR_REG_LINK'            => $_ARRAYLANG['TXT_CALENDAR_REG_LINK'],
                'TXT_CALENDAR_TITLE'            => $_ARRAYLANG['TXT_CALENDAR_TITLE'],
                'TXT_CALENDAR_START_DATE'        => $_ARRAYLANG['TXT_CALENDAR_START_DATE'],
                'TXT_CALENDAR_END_DATE'            => $_ARRAYLANG['TXT_CALENDAR_END_DATE'],
                'TXT_CALENDAR_DATE'                => $_ARRAYLANG['TXT_CALENDAR_DATE'],
                'TXT_CALENDAR_MAIL_CONTENT'        => $_ARRAYLANG['TXT_CALENDAR_MAIL_CONTENT'],
                'TXT_CALENDAR_TEXT'                => $_ARRAYLANG['TXT_CALENDAR_TEXT'],
                'TXT_CALENDAR_HOST_URL'            => $_ARRAYLANG['TXT_CALENDAR_HOST_URL'],
                'TXT_CALENDAR_REGISTRATIONS_SUBSCRIBER'            => $_ARRAYLANG['TXT_CALENDAR_REGISTRATIONS_SUBSCRIBER'],
                'TXT_CALENDAR_REGISTRATIONS_SUBSCRIBER_INFO'    => $_ARRAYLANG['TXT_CALENDAR_REGISTRATIONS_SUBSCRIBER_INFO'],
                'TXT_CALENDAR_SEND_MAIL_AGAIN'    => $_ARRAYLANG['TXT_CALENDAR_SEND_MAIL_AGAIN'],
                'TXT_CALENDAR_REGISTRATIONS_ADDRESSER_INFO'    => $_ARRAYLANG['TXT_CALENDAR_REGISTRATIONS_ADDRESSER_INFO'],
                'TXT_CALENDAR_NOTIFICATION_ACTIVATE'         => $_ARRAYLANG['TXT_CALENDAR_NOTIFICATION_ACTIVATE'],
                'TXT_CALENDAR_NOTIFICATION_ADDRESS'         => $_ARRAYLANG['TXT_CALENDAR_NOTIFICATION_ADDRESS'],
                'TXT_CALENDAR_NOTIFICATION_ADDRESS_INFO'     => $_ARRAYLANG['TXT_CALENDAR_NOTIFICATION_ADDRESS_INFO'],
            ));

            if(!$objUser) {
                $this->_objTpl->hideBlock('boxMyself');
            }
        } else {
            $this->_objTpl->hideBlock('addeventForm');
        }




        if(!empty($this->strOkMessage)) {
            $this->_objTpl->setVariable('SUCCESS', $this->strOkMessage);
        }
        if(!empty($this->strErrMessage)) {
            $this->_objTpl->setVariable('ERROR', $this->strErrMessage);
        }

        return $this->_objTpl->get();
    }

    /**
     * uploads an image to an upload folder. if a file is allready uploaded, return its path
     *
     *
     * @param string $formfield formname of upload field. (used as array key in $_FILES)
     * @return string $formfield filepath of the file or false on error
     */
    function _uploadImageIfNot($formfield) {
        $fileNew = '';
        $pathAdd = 'uploads/';
        if(!$_SESSION['calendar']['uploadedimage'][$formfield] or (!empty($_FILES[$formfield]) > 0) && !empty($_FILES[$formfield]['name'])) {
            //upload a file if present

            if(count($_FILES) < 1) {
                return false;
            }
            $sessid = session_id();
            $copyFiles = $_FILES;
            $firstFile = $_FILES[$formfield];
            $pathinfo = pathinfo($firstFile['name']);
            $extension = ".".$pathinfo['extension'];
            if(!empty($_SESSION['calendar']['uploadedimagebasename'][$formfield])) {
                $sessid = $_SESSION['calendar']['uploadedimagebasename'][$formfield];
            } else {
                while(file_exists($this->uploadImgPath.$pathAdd.$sessid.$extension)) {
                    $sessid++;
                }
            }
            $fileNew = $this->uploadImgWebPath.$pathAdd.$sessid.$extension;
            $rc = File::uploadFileHttp($formfield, $fileNew, $this->uploadImgMaxSize);
            if($rc) {
                $_SESSION['calendar']['uploadedimage'][$formfield] = $this->uploadImgWebPath.$pathAdd.$sessid.$extension;
                $_SESSION['calendar']['uploadedimagepath'][$formfield] = $this->uploadImgPath.$pathAdd.$sessid.$extension;
                $_SESSION['calendar']['uploadedimageext'][$formfield] = $extension;
                $_SESSION['calendar']['uploadedimagerealname'][$formfield] = $pathinfo['basename'];
                $_SESSION['calendar']['uploadedimagebasename'][$formfield] = $sessid;
                return $this->uploadImgWebPath.$pathAdd.$sessid.$extension;
            } else {
                return false;
            }
        } else {
            return trim($_SESSION['calendar']['uploadedimage'][$formfield]);
        }
    }

    function _checkUploadTypes($formfield, $filetypes) {
        if($this->_hasUploadFile($formfield)) {
            $firstFile = $_FILES[$formfield];
            $pathinfo = pathinfo($firstFile['name']);
            $extension = $pathinfo['extension'];
            if(is_array($filetypes) && in_array($extension, $filetypes)) {
                return true;
            } else {
                throw new Exception($_ARRAYLANG['TXT_CALENDAR_GENERAL_ERROR']);
            }
        }
        return false;
    }


    /**
     * checks if the user really uploaded a file through http fileupload
     *
     * @param string $formfield
     * @return boolean
     */
    function _hasUploadFile($formfield) {
        if(isset($_FILES[$formfield]) &&  $_FILES[$formfield]['name'] && !empty($_FILES[$formfield]['name'])) {
            return true;
        }
        return false;
    }


    /**
     * Enter description here...
     *
     * @param unknown_type $formfield
     */
    function _clearUpload($formfield) {
        if(!empty($formfield) && $_SESSION['calendar']['uploadedimagepath'][$formfield]) {
            unlink($_SESSION['calendar']['uploadedimagepath'][$formfield]);
            unset($_SESSION['calendar']['uploadedimage'][$formfield]);
            unset($_SESSION['calendar']['uploadedimagepath'][$formfield]);
            unset($_SESSION['calendar']['uploadedimageext'][$formfield]);
            unset($_SESSION['calendar']['uploadedimagerealname'][$formfield]);
            unset($_SESSION['calendar']['uploadedimagebasename'][$formfield]);

        }
    }

    /**
     * moves our uploaded event image from the temporary folder to its definitive folder.
     *
     * If The File allready exists, it appends a numeric suffix
     *
     * @return true on success, false otherwise
     */
    function _moveUploadedImage($formfield) {
        if($_SESSION['calendar']['uploadedimagepath'][$formfield] && file_exists($_SESSION['calendar']['uploadedimagepath'][$formfield])) {

            $file = $_SESSION['calendar']['uploadedimagepath'][$formfield];
            $sessid = session_id();

            $i = 1;
            $finfo = pathinfo($this->uploadImgPath.$sessid.$_SESSION['calendar']['uploadedimageext'][$formfield]);
            $finfoWeb = pathinfo($this->uploadImgWebPath.$sessid.$_SESSION['calendar']['uploadedimageext'][$formfield]);
            $dirname = $finfo['dirname']."/";
            $filename = $finfo['filename'];
            $ext = ".".$finfo['extension'];
            $dirWeb = $finfoWeb['dirname']."/";

            $targetFile = $this->uploadImgPath.$sessid.$_SESSION['calendar']['uploadedimageext'][$formfield];
            $targetFilename = $filename.$ext;
            $targetWeb  = $this->uploadImgWebPath.$sessid.$_SESSION['calendar']['uploadedimageext'][$formfield];
            while(file_exists($targetFile)) {
                $targetFile = $dirname.$filename."-".$i.$ext;
                $targetWeb  = $dirWeb.$filename."-".$i.$ext;
                $targetFilename = $filename."-".$i.$ext;
                $i++;
            }
            $rc = rename($file, $targetFile);
            if(!$rc) {
                return $rc;
            } else {
                return $targetFilename;
            }
        }
        return true;
    }

    /**
     * returns the string within errorbox
     *
     * @param string $message
     * @return string
     */
    function errorBox($message) {
        return "<div class='errorbox'>$message</div>";
    }
}
?>
