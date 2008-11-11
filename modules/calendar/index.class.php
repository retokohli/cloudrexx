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

    	$this->_getEventList();

    	switch($_REQUEST['cmd']) {
    		case 'event':
    			$key 	= intval($_REQUEST['id']);
    			$id 	= intval($this->eventList[$key]['id']);

    			//check access
    			//if ($this->_checkAccess($id)){
				//check export
				if(isset($_REQUEST['export'])){
					switch($_REQUEST['export']){
						case 'iCal':
			    			if($id > 0){
				    			$this->_iCalExport('event', $id);
			    			}
							break;

						case 'category':
							if ($id > 0) {
								$this->_iCalExport('category', $id);
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

    		if($_GET['cmd'] == 'boxes' && empty($_GET['act'])){
	    		$day = 1;
    		}

    		$startdate = mktime(0, 0, 0, $month, $day, $year);
    	} else {

    		$datearr = explode("-", $_POST['startDate']);
    		$startdate = mktime(0, 0, 0, $datearr[1], $datearr[2], $datearr[0]);
    		unset($datearr);
    	}

    	//get enddates
    	if (empty($_POST['endDate'])) {
    		if($_GET['cmd'] == 'boxes'){
    			$day 	= isset($_GET['dayID']) ? $_GET['dayID'] : date("d", mktime());
    			//$day 	= isset($_GET['dayID']) ? $_GET['dayID'] : 31;
	    		$month 	= isset($_REQUEST['monthID']) ? $_REQUEST['monthID'] : date("m", mktime());
	    		$year 	= isset($_REQUEST['yearID']) ? $_REQUEST['yearID'] : date("Y", mktime());

	    		if(empty($_GET['act'])){
	    			$month = $month+2;
	    			$day = 31;
	    		}

    			$enddate = mktime(23, 59, 59, $month, $day, $year);
    		} else {
    			$enddate = 0;
    		}
    	} else {
    		$datearr = explode("-", $_POST['endDate']);
    		$enddate = mktime(23, 59, 59, $datearr[1], $datearr[2], $datearr[0]);
    		unset($datearr);
    	}

    	//get search term
    	if (!empty($_GET['act']) && $_GET['act'] == "search") {
    		$term = htmlentities(addslashes($_POST['keyword']), ENT_QUOTES, CONTREXX_CHARSET);
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
		$this->eventList 	= $this->objSeries->getEventList($startdate,$enddate,$count, $auth, $term, $category);

    }



    function _showStandardView()
    {
    	global $objDatabase, $_ARRAYLANG, $_CONFIG, $_LANGID;

    	$this->url = CONTREXX_DIRECTORY_INDEX."?section=calendar";
    	$this->_objTpl->setTemplate($this->pageContent);


    	$this->_objTpl->setVariable(array(
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

				$day 	= isset($_REQUEST['dayID']) ? '&amp;dayID='.$_REQUEST['dayID'] : '';
	    		$month 	= isset($_REQUEST['monthID']) ? '&amp;monthID='.$_REQUEST['monthID'] : '';
	    		$year 	= isset($_REQUEST['yearID']) ? '&amp;yearID='.$_REQUEST['yearID'] : '';

				$link = '<a href="?section=calendar&amp;cmd=event'.$year.$month.$day.'&amp;id='.intval($key).'">'.htmlentities($array['name'], ENT_QUOTES, CONTREXX_CHARSET).'</a>';

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

		$java_script  = "<script language=\"JavaScript\" type=\"text/javascript\">\n<!--\nfunction goTo()\n{\nwindow.location.href = \"".CONTREXX_DIRECTORY_INDEX."?section=calendar&catid=".$_GET['catid']."&month=\"+document.goToForm.goToMonth.value+\"&year=\"+document.goToForm.goToYear.value;\n}\n\n\n";
		$java_script .= "function categories()\n{\nwindow.location.href = \"".$requestUri."&catid=\"+document.selectCategory.inputCategory.value;\n}\n// -->\n</script>";


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
                header("Location: ".CONTREXX_DIRECTORY_INDEX."?section=calendar");
		    } else {
		        header("Location: ".CONTREXX_DIRECTORY_INDEX."?section=calendar".$this->mandate);
		    }
			exit;
		}
		$this->_objTpl->setTemplate($this->pageContent);

		$access = $this->getNoteData($id, "show", 0);

		if ($access == true) {
			if (!$this->_checkAccess($id)) {
				header("Location: ".CONTREXX_DIRECTORY_INDEX."?section=calendar");
				exit;
			}
		}

		//delet old series events
		//$this->objSeries->updateMainEvent($id);

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

            'CALENDAR_ICAL_EXPORT'      	=> '<a href="'.CONTREXX_DIRECTORY_INDEX.'?section=calendar&amp;cmd=event&amp;export=iCal&amp;id='.$id.'" title="'.$_ARRAYLANG['TXT_CALENDAR_EXPORT_ICAL_EVENT'].'">
            									'.$_ARRAYLANG['TXT_CALENDAR_EXPORT_ICAL_EVENT'].' <img style="padding-top: -1px;" border="0" src="images/modules/calendar/ical_export.gif" alt="'.$_ARRAYLANG['TXT_CALENDAR_EXPORT_ICAL_EVENT'].'" title="'.$_ARRAYLANG['TXT_CALENDAR_EXPORT_ICAL_EVENT'].'" />
            								</a>',
            'CALENDAR_ICAL_EXPORT_IMG'      => '<a href="'.CONTREXX_DIRECTORY_INDEX.'?section=calendar&amp;cmd=event&amp;export=iCal&amp;id='.$id.'" title="'.$_ARRAYLANG['TXT_CALENDAR_EXPORT_ICAL_EVENT'].'">
            									<img border="0" src="images/modules/calendar/ical_export.gif" alt="'.$_ARRAYLANG['TXT_CALENDAR_EXPORT_ICAL_EVENT'].'" title="'.$_ARRAYLANG['TXT_CALENDAR_EXPORT_ICAL_EVENT'].'" />
            								</a>',
        ));

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
					window.location.href = href;
				}
				/* ]]> */
				</script>

				<script src="lib/datepickercontrol/datepickercontrol.js" type="text/javascript">
				</script>

				<script type="text/javascript">
				/* <![CDATA[ */
				  DatePickerControl.onSelect = function(inputid)
				  {
				    var startdate = document.getElementById("searchform").startDate.value.replace(/-/g, "");
				    var enddate = document.getElementById("searchform").endDate.value.replace(/-/g, "");

				    if (startdate > enddate) {
				   	var date = document.getElementById("searchform").startDate.value;
				   	document.getElementById("searchform").endDate.value = date;
				  }
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
			//insert registration data
			$time	= mktime();
			$noteId	= intval($_POST['id']);
			$type	= intval($_POST['type']);
			$ip		= "";
			$host 	= "";

			$query = "INSERT INTO ".DBPREFIX."module_calendar".$this->mandateLink."_registrations   (`note_id`,
																		       `time`,
																		   	   `host`,
																			   `ip_address`,
																			   `type`)
														   			   VALUES ('$noteId',
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

					$this->_objTpl->setVariable(array(
						'CALENDAR_REGISTRATIONS_STATUS'          => $_ARRAYLANG['TXT_CALENDAR_REGISTRATION_SUCCESSFUL'],
						'TXT_CALENDAR_BACK'         			 => $_CORELANG['TXT_BACK'],
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
}
?>
