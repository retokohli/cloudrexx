<?php
/**
 * Calendar
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Paulo M. Santos <pmsantos@astalavista.net>
 * @package     contrexx
 * @subpackage  module_calendar
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once ASCMS_MODULE_PATH. '/calendar/calendarLib.class.php';
require_once ASCMS_CORE_PATH.'/settings.class.php';

/**
 * Calendar
 *
 * Class to manage cms calendar
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Paulo M. Santos <pmsantos@astalavista.net>
 * @package     contrexx
 * @subpackage  module_calendar
 */
class calendarManager extends calendarLibrary
{
	/**
	 * PHP 5 Constructor
	 */
	function __construct()
	{
		$this->calendarManager();
	}

	/**
	 * PHP 4 Constructor
	 */
    function calendarManager()
    {
    	$this->calendarLibrary($_SERVER["SCRIPT_NAME"]."?cmd=calendar");
	    global $_ARRAYLANG, $objTemplate;

        // links
		$objTemplate->setVariable("CONTENT_TITLE", $_ARRAYLANG['TXT_CALENDAR']);

    	$objTemplate->setVariable("CONTENT_NAVIGATION","
            <a href='?cmd=calendar'> ".$_ARRAYLANG['TXT_CALENDAR_MENU_OVERVIEW']." </a>
    		<a href='?cmd=calendar&amp;act=new'> ".$_ARRAYLANG['TXT_CALENDAR_MENU_ENTRY']." </a>
    	    <a href='?cmd=calendar&amp;act=cat'> ".$_ARRAYLANG['TXT_CALENDAR_CATEGORIES']." </a>
    	    <a href='?cmd=calendar&amp;act=placeholder'>".$_ARRAYLANG['TXT_CALENDAR_PLACEHOLDER']."</a>
    	    <a href='?cmd=calendar&amp;act=settings'> ".$_ARRAYLANG['TXT_CALENDAR_MENU_SETTINGS']." </a>");

    	$this->showOnlyActive = false;
	}


	/**
	 * Get Calendar Page
	 */
    function getCalendarPage()
    {
    	global $objTemplate;

    	if (!isset($_REQUEST['act'])){
    		$_REQUEST['act'] = '';
    	}

        switch ($_REQUEST['act']) {
			case 'event':
				$this->_objTpl->loadTemplateFile('module_calendar_show_note.html');
				$action = $this->showEvent();
				break;

			case 'new':
    			$this->_objTpl->loadTemplateFile('module_calendar_new_note.html');
				$action = $this->newNote();
				break;

			case 'saveNew':
				$id = $this->writeNote();
				header("Location: ?cmd=calendar&act=event&id=$id");
				exit;
				break;

			case 'cat':
			    $this->_objTpl->loadTemplateFile('module_calendar_categories.html');
			    $action = $this->showCategories();
			    break;

			case 'catedit':
				$this->_objTpl->loadTemplateFile('module_calendar_categories_edit.html');
				$action = $this->categoriesEdit();
				break;

			case 'all':
    			$this->_objTpl->loadTemplateFile('module_calendar_show_all.html');
				$action = $this->delAllNote();
				break;

			case 'edit':
				$this->_objTpl->loadTemplateFile('module_calendar_edit_note.html');
				$action = $this->editNote();
				break;

			case 'saveEdited':
				$this->writeEditedNote();
				header("Location: ?cmd=calendar&act=event&id={$_POST['inputId']}");
				exit;
				break;

			case 'settings':
			    $this->_objTpl->loadTemplateFile('module_calendar_settings.html');
			    $action = $this->settings();
			    break;

			case 'saveSettings':
				$this->saveSettings();
				$this->setStdCat();
				header("Location: ?cmd=calendar&act=settings");
				exit;
				break;

			case 'event_actions':
				$this->multiDelete();
				break;

			case 'delete':
			    $this->delNote($_GET['id']);
			    $action = $this->showOverview();
			    break;

			case 'activate':
			    $this->activateNote();
			    $action = $this->showOverview();
			    break;

		 	case 'deactivate':
			    $this->deactivateNote();
			    $action = $this->showOverview();
			    break;

			case 'placeholder':
				$this->showPlaceholders();
				break;
			default:
				$action = $this->showOverview();
		}


		$objTemplate->setVariable(array(
			'CONTENT_OK_MESSAGE'		=> $this->strOkMessage,
			'CONTENT_STATUS_MESSAGE'	=> $this->strErrMessage,
			'ADMIN_CONTENT'				=> $this->_objTpl->get()
		));
    }



	/**
	 * Show Day
	 *
	 * Shows the three calendar boxes an the notes
	 */
	function showOverview()
	{
		global $objDatabase, $_ARRAYLANG, $_LANGID;

//		$objDatabase->debug = true;

		$this->_objTpl->loadTemplateFile('module_calendar_overview.html');
		$this->_objTpl->setVariable("CONTENT", $this->pageContent);

		// Stuff for the category selection
		$requestUri = str_replace('&catid='.$_GET['catid'], '', $_SERVER['REQUEST_URI']);
	    $query = "SELECT id,name,lang FROM ".DBPREFIX."module_calendar_categories ORDER BY pos";
	    $objResult = $objDatabase->Execute($query);
	    if ($objResult !== false) {
			while(!$objResult->EOF) {
				$select = '';
				if ($objResult->fields['id'] == $_GET['catid']){
					$select = ' selected="selected"';
				}

				$query = "SELECT lang
				               FROM ".DBPREFIX."languages
				              WHERE id = '".$objResult->fields['lang']."'";

				$objResult2 = $objDatabase->SelectLimit($query, 1);

				$this->_objTpl->setVariable(array(
				    'CALENDAR_CAT_ID'      => $objResult->fields['id'],
				    'CALENDAR_CAT_SELECT'  => $select,
				    'CALENDAR_CAT_NAME'    => $objResult->fields['name'],
				    'CALENDAR_CAT_LANG'    => $objResult2->fields['lang']
				));
				$this->_objTpl->parse("calendar_cat");
				$cats[$objResult->fields['id']] = $objResult->fields['name'];

				$objResult->MoveNext();
			}
		}

		// Checks the variables and gets the boxes
		if (isset($_GET['yearID']) && isset($_GET['monthID']) &&  isset($_GET['dayID'])) {
			$day = $_GET['dayID'];
			$month = $_GET['monthID'];
			$year = $_GET['yearID'];
			$startdate = mktime(00, 00, 00, $month, $day, $year);
			$enddate = mktime(23, 59, 59, $month, $day, $year);

			$calendarbox = $this->getBoxes(3, $year, $month, $day);
			$titledate = date(ASCMS_DATE_SHORT_FORMAT, mktime(0, 0, 0, $_GET['monthID'], $_GET['dayID'], $_GET['yearID']));

		} elseif (isset($_GET['yearID']) && isset($_GET['monthID']) && !isset($_GET['dayID'])) {
			$month = $_GET['monthID'];
			$year = $_GET['yearID'];
			$startdate = mktime(00, 00, 00, $month, 01, $year);
			$enddate = mktime(23, 59, 59, $month, 31, $year);

			$calendarbox = $this->getBoxes(3, $year, $month);
			$titledate = date("F", mktime(0, 0, 0, $_GET['monthID'], 1, $_GET['yearID']));

		} elseif (isset($_GET['yearID']) && !isset($_GET['monthID']) && !isset($_GET['dayID'])) {
			$month = date("Y");
			$year = $_GET['yearID'];
			$startdate = mktime(00, 00, 00, 1, 01, $year);
			$enddate = mktime(23, 59, 59, 12, 31, $year);

			$calendarbox = $this->getBoxes(3, $year);
			$titledate = sprintf("%4d", $_GET['yearID']);

		} else {
			$day = date("d");
			$month = date("m");
			$year = date("Y");
			$select_next_ten = true;

			$startdate = mktime(00, 00, 00, $month, $day, $year);

			$calendarbox = $this->getBoxes(3, $year, $month, $day);
		}

		if (isset($_POST['search'])) {
			$keyword = htmlentities(addslashes($_POST['inputKeyword']), ENT_QUOTES, CONTREXX_CHARSET);
			$query = "SELECT active, id, name, catid, startdate, enddate,
						MATCH (name, `comment`, place) AGAINST ('$keyword') as score
					  	FROM ".DBPREFIX."module_calendar
					  	WHERE (`name` LIKE '%$keyword%' OR
					  	`comment` LIKE '%$keyword%' OR
					  	`place` LIKE '%$keyword%' OR
					  	`id` LIKE '%$keyword%')
					    ORDER BY score ASC";
		} else {
			if ($select_next_ten && !empty($_GET['catid'])) {
				$query = "SELECT active, id, name, catid, startdate, enddate
					FROM ".DBPREFIX."module_calendar
					WHERE catid={$_GET['catid']} AND
					((startdate > $startdate) OR
					(enddate > $startdate))
					ORDER BY startdate ASC
					LIMIT 0,10
					";

			} elseif ($select_next_ten && empty($_GET['catid'])) {
				$query = "SELECT active, id, name, catid, startdate, enddate
					FROM ".DBPREFIX."module_calendar
					WHERE (startdate > $startdate) OR
					(enddate > $startdate)
					ORDER BY startdate ASC
					LIMIT 0,10
					";

			} elseif (!$select_next_ten && !empty($_GET['catid'])) {
				$query = "SELECT active, id, name, catid, startdate, enddate
					FROM ".DBPREFIX."module_calendar
					WHERE catid = {$_GET['catid']} AND
					((startdate BETWEEN $startdate AND $enddate) OR
					(enddate BETWEEN $startdate AND $enddate) OR
					(startdate < $startdate AND enddate > $startdate))
					ORDER BY startdate ASC";

			} elseif (!$select_next_ten && empty($_GET['catid'])) {
				$query = "SELECT active, id, name, catid, startdate, enddate
					FROM ".DBPREFIX."module_calendar
					WHERE (startdate BETWEEN $startdate AND $enddate) OR
					(enddate BETWEEN $startdate AND $enddate) OR
					(startdate < $startdate AND enddate > $startdate)
					ORDER BY startdate ASC";
			}
		}

		$objResult = $objDatabase->Execute($query);

		$rowcounter = 2;

		if ($objDatabase->Affected_Rows() > 0) {
			while(!$objResult->EOF) {
				$today = time();
				if ($objResult->fields['active'] == "0") {
					$status = "red";
					$event_led = $_ARRAYLANG['TXT_CALENDAR_LED_INACTIVE'];
				} elseif ($objResult->fields['startdate'] > $today || $objResult->fields['enddate'] > $today) {
					$status = "green";
					$event_led = $_ARRAYLANG['TXT_CALENDAR_LED_ACTIVE'];
				} else {
					$status = "red";
					$event_led = $_ARRAYLANG['TXT_CALENDAR_LED_OLD'];
				}

				$this->_objTpl->setVariable(array(
					'CALENDAR_ACTIVE_ICON'		=> $status,
					'CALENDAR_EVENT_ID'			=> $objResult->fields['id'],
					'CALENDAR_EVENT_STARTDATE'	=> date(ASCMS_DATE_FORMAT, $objResult->fields['startdate']),
					'CALENDAR_EVENT_ENDDATE'	=> date(ASCMS_DATE_FORMAT, $objResult->fields['enddate']),
					'CALENDAR_EVENT_TITLE'		=> htmlentities($objResult->fields['name'], ENT_QUOTES, CONTREXX_CHARSET),
					'CALENDAR_EVENT_CAT'		=> $cats[$objResult->fields['catid']],
					'CALENDAR_ROW'				=> "row".$rowcounter,
					'CALENDAR_EVENT_LED'		=> $event_led
				));

				$this->_objTpl->parse("event");
				if ($rowcounter == 2 ) {
					$rowcounter = 1;
				} else {
					$rowcounter = 2;
				}
				$objResult->moveNext();
			}
		}

		// The List of months
		$i = 1;
		$monthnames = split(",", $_ARRAYLANG['TXT_MONTH_ARRAY']);
		foreach($monthnames as $name) {
			if ($i == $month) {
				$selected = " selected=\"selected\"";
			} else {
				$selected = "";
			}

			$this->_objTpl->setVariable(array(
				'CALENDAR_MONTHLIST_VALUE'	=> $i,
				'CALENDAR_MONTHLIST_NAME'	=> $name,
				'CALENDAR_MONTHLIST_SELECTED' => $selected
			));

			$this->_objTpl->parse("calendar_monthlist");

			$i++;
		}

		// The lists of years
		for($i = date("Y")-10; $i<=2037; $i++) {
			if ($i == intval($year)) {
				$selected = " selected=\"selected\"";
			} else {
				$selected = "";
			}

			$this->_objTpl->setVariable(array(
				'CALENDAR_YEARLIST_VALUE'	=> $i,
				'CALENDAR_YEARLIST_SELECTED' => $selected
			));

			$this->_objTpl->parse("calendar_yearlist");
		}


		// Variable assignement
		$this->_objTpl->setVariable(array(
			'TXT_EVENTS'			=> $_ARRAYLANG['TXT_CALENDAR_EVENTS'],
			'TXT_CALENDAR_OVERVIEW'	=> $_ARRAYLANG['TXT_CALENDAR_MENU_OVERVIEW'],
			'TXT_CALENDAR'		   	=> $_ARRAYLANG['TXT_CALENDAR'],
			'TXT_CALENDAR_CAT'	  	=> $_ARRAYLANG['TXT_CALENDAR_CAT'],
			'TXT_CALENDAR_SEARCH'	=> $_ARRAYLANG['TXT_CALENDAR_SEARCH'],
			'TXT_CALENDAR_KEYWORD'	=> $_ARRAYLANG['TXT_CALENDAR_KEYWORD'],
			'TXT_GO'				=> $_ARRAYLANG['TXT_GO'],
			'TXT_SEARCH'			=> $_ARRAYLANG['TXT_CALENDAR_SEARCH'],
			'TXT_CALENDAR_START'	=> $_ARRAYLANG['TXT_CALENDAR_START'],
			'TXT_CALENDAR_END'		=> $_ARRAYLANG['TXT_CALENDAR_END'],
			'TXT_CALENDAR_TITLE'	=> $_ARRAYLANG['TXT_CALENDAR_TITLE'],
			'TXT_CALENDAR_CAT'		=> $_ARRAYLANG['TXT_CALENDAR_CAT'],
			'TXT_CALENDAR_ACTION'	=> $_ARRAYLANG['TXT_CALENDAR_ACTION'],
			'TXT_SUBMIT_SELECT'		=> $_ARRAYLANG['TXT_SUBMIT_SELECT'],
			'TXT_SUBMIT_DELETE'		=> $_ARRAYLANG['TXT_SUBMIT_DELETE'],
			'TXT_SUBMIT_ACTIVATE'		=> $_ARRAYLANG['TXT_SUBMIT_ACTIVATE'],
			'TXT_SUBMIT_DEACTIVATE'		=> $_ARRAYLANG['TXT_SUBMIT_DEACTIVATE'],
			'TXT_SELECT_ALL'		=> $_ARRAYLANG['TXT_SELECT_ALL'],
			'TXT_DESELECT_ALL'		=> $_ARRAYLANG['TXT_DESELECT_ALL'],
			'TXT_CALENDAR_DELETE_CONFIRM' => $_ARRAYLANG['TXT_CALENDAR_DELETE_CONFIRM'],
			'CALENDAR'			   	=> $calendarbox,
			'CALENDAR_DATE'		   	=> $titledate,
			'TXT_CALENDAR_ALL_CAT' 	=> $_ARRAYLANG['TXT_CALENDAR_ALL_CAT'],
			'CALENDAR_REQUEST_URI' 	=> $requestUri,
			'CALENDAR_CATID'       	=> $_GET['catid']
		));
	}


	/**
	 * Deletes multiple events
	 */
	function multiDelete()
	{
		global $objDatabase;

		if (!empty($_POST['selectedEventId'])) {
			foreach ($_POST['selectedEventId'] as $eventid) {
				$query = "DELETE FROM ".DBPREFIX."module_calendar WHERE id=$eventid";

				$objDatabase->Execute($query);
			}
		}

		header("LOCATION: ?cmd=calendar");
		exit;
	}


    // CASE NOTE
	function showEvent()
	{
		global $_ARRAYLANG;

		// get day note
		$this->getDayNote($_GET['id']);

	    // parse remains to template
		$this->_objTpl->setVariable(array(
			'TXT_CALENDAR'                => $_ARRAYLANG['TXT_CALENDAR'],
			'TXT_CALENDAR_DELETE_CONFIRM' => $_ARRAYLANG['TXT_CALENDAR_DELETE_CONFIRM']
		));
	}

	// delete note
	function delNote($id)
	{
		global $objDatabase, $_ARRAYLANG, $_LANGID;

		$objDatabase->Execute("DELETE FROM ".DBPREFIX."module_calendar WHERE id = ".intval($id));
		$objResult = $objDatabase->Execute("SELECT id FROM ".DBPREFIX."module_calendar");

		if ($objDatabase->Affected_Rows() == 0){
			$objDatabase->Execute("DELETE FROM ".DBPREFIX."module_calendar");
		}

		$this->strOkMessage = $_ARRAYLANG['TXT_CALENDAR_STAT_DEL']."<br />";
	}


	// delete all notes
	function delAllNote($catid)
	{
		global $objDatabase, $_ARRAYLANG, $_LANGID;

		if ($catid == 0){
			$objDatabase->Execute("DELETE FROM ".DBPREFIX."module_calendar");
		}
		else{
			$objDatabase->Execute("DELETE FROM ".DBPREFIX."module_calendar WHERE catid =".intval($catid));
			$objResult = $objDatabase->Execute("SELECT id FROM ".DBPREFIX."module_calendar");
			if ($objResult->Affected_Rows() == 0){
				$objDatabase->Execute("DELETE FROM ".DBPREFIX."module_calendar");
			}
		}
		header("Location: index.php?cmd=calendar&amp;act=all&amp;pos=0");
		exit;
	}



	/**
	 * Edit Note
	 *
	 * Shows the form for editing a note
	 */
	function editNote()
	{
		global $objDatabase, $_ARRAYLANG, $_LANGID;

		$id = intval($_GET['id']);

		$query = "
		    SELECT id, active, catid, startdate, enddate, priority, name, comment, place, info
		    FROM ".DBPREFIX."module_calendar
		    WHERE id = '".$id."'";
		$objResult 	= $objDatabase->SelectLimit($query, 1);

		$startdate  = $objResult->fields['startdate'];
		$enddate	= $objResult->fields['enddate'];

		$day		= date("d", $startdate);
		$end_day	= date("d", $enddate);
		$month		= date("m", $startdate);
		$end_month	= date("m", $enddate);
		$year		= date("Y", $startdate);
		$end_year	= date("Y", $enddate);
		$hour		= date("H", $startdate);
		$minutes	= date("i", $startdate);
		$end_hour	= date("H", $enddate);
		$end_minutes	= date("i", $enddate);
		$info      	= $objResult->fields['info'];

		$select1 = '';
		$select2 = '';
		$select3 = '';
		$select4 = '';


		$query = "SELECT id,
		                    name,
		                    lang
		               FROM ".DBPREFIX."module_calendar_categories
		            ORDER BY pos";

		$objResult2 = $objDatabase->Execute($query);

		if ($objResult2 !== false) {
			while(!$objResult2->EOF) {
				$query = "SELECT lang
				               FROM ".DBPREFIX."languages
				              WHERE id = '".$objResult2->fields['lang']."'";

				$objResult3 = $objDatabase->SelectLimit($query, 1);

				$selected = '';
				if ($objResult2->fields['id'] == $objResult->fields['catid']) {
					$selected = ' selected="selected"';
				}

				$this->_objTpl->setVariable(array(
				    'CALENDAR_CAT_ID'       => $objResult2->fields['id'],
				    'CALENDAR_CAT_SELECTED' => $selected,
			    	'CALENDAR_CAT_LANG'     => $objResult3->fields['lang'],
				    'CALENDAR_CAT_NAME'     => $objResult2->fields['name']
				));
				$this->_objTpl->parse("calendar_cat");

				$objResult2->MoveNext();
			}
		}

//		 select priority
//
//		for ($x = 1; $x <= 5; $x++) {
//	   		if ($x == 1) {
//	   			$this->_objTpl->setVariable('CALENDAR_IMG_PRIORITY', '2h');
//	   		}
//	   		if ($x == 2) {
//	   			$this->_objTpl->setVariable('CALENDAR_IMG_PRIORITY', 'h');
//	   		}
//	   		if ($x == 3) {
//	   			$this->_objTpl->setVariable('CALENDAR_IMG_PRIORITY', 'no');
//	   		}
//	   		if ($x == 4) {
//	   			$this->_objTpl->setVariable('CALENDAR_IMG_PRIORITY', 'l');
//	   		}
//	   		if ($x == 5) {
//	   			$this->_objTpl->setVariable('CALENDAR_IMG_PRIORITY', '2l');
//	   		}
//
//	   		if ($x == $objResult->fields['priority']) {
//	   			$this->_objTpl->setVariable('CALENDAR_PRIORITY_SELECT', " checked");
//	   		} else {
//	   			$this->_objTpl->setVariable('CALENDAR_PRIORITY_SELECT', "");
//	   		}
//
//			$this->_objTpl->setVariable('CALENDAR_PRIORITY', $x);
//
//			$this->_objTpl->parse("priority");
//		}

//		 select days
//	    $this->selectDay($day, 'day', 'CALENDAR_DAY_SELECT', 'CALENDAR_DAY');
//	    $this->selectDay($end_day, 'endday', 'CALENDAR_END_DAY_SELECT', 'CALENDAR_END_DAY');
//
//	   	 select months
//		$this->selectMonth($month, 'month', 'CALENDAR_MONTH', 'CALENDAR_MONTH_SELECT', 'CALENDAR_MONTH_NAME');
//		$this->selectMonth($end_month, 'endmonth', 'CALENDAR_END_MONTH', 'CALENDAR_END_MONTH_SELECT', 'CALENDAR_END_MONTH_NAME');

//	     select years
//	    $this->selectYear($year, 'year', 'CALENDAR_YEAR_SELECT', 'CALENDAR_YEAR');
//	    $this->selectYear($end_year, 'endyear', 'CALENDAR_END_YEAR_SELECT', 'CALENDAR_END_YEAR');

//	     Generate the time dropdowns
		$this->selectHour($hour, "hour", "CALENDAR_HOUR_SELECT", "CALENDAR_HOUR");
		$this->selectMinutes($minutes, "minutes", "CALENDAR_MINUTES_SELECT", "CALENDAR_MINUTES");
	    $this->selectHour($end_hour, "endhour", "CALENDAR_END_HOUR_SELECT", "CALENDAR_END_HOUR");
		$this->selectMinutes($end_minutes, "endminutes", "CALENDAR_END_MINUTES_SELECT", "CALENDAR_END_MINUTES");

		$ed = get_wysiwyg_editor('inputComment', $objResult->fields['comment'], 'active');

		if (!empty($objResult->fields['active'])) {
			$checked = "checked=\"checked\"";
		} else {
			$checked = "";
		}

		// parse to template
		$this->_objTpl->setVariable(array(
		    'CALENDAR_CAT'               => $objResult2->fields['name'],
		    'CALENDAR_ID'                => $objResult->fields['id'],
		    'CALENDAR_DAY'               => $day,
		    'CALENDAR_NAME'              => htmlentities($objResult->fields['name'], ENT_QUOTES, CONTREXX_CHARSET),
		    'CALENDAR_PLACE'             => htmlentities($objResult->fields['place'], ENT_QUOTES, CONTREXX_CHARSET),
		    'CALENDAR_COMMENT'           => htmlentities($objResult->fields['comment'], ENT_QUOTES, CONTREXX_CHARSET),
		    'CALENDAR_INFO'              => $info,
		    'CALENDAR_INFO_SELECT1'      => $select1,
		    'CALENDAR_INFO_SELECT2'      => $select2,
		    'CALENDAR_INFO_SELECT3'      => $select3,
		    'CALENDAR_INFO_SELECT4'      => $select4,
			'TXT_CALENDAR_ERROR_NAME'    => $_ARRAYLANG['TXT_CALENDAR_ERROR_NAME'],
			'TXT_CALENDAR_ERROR_PLACE'   => $_ARRAYLANG['TXT_CALENDAR_ERROR_PLACE'],
			'TXT_CALENDAR_ERROR_DATE'    => $_ARRAYLANG['TXT_CALENDAR_ERROR_DATE'],
			'TXT_CALENDAR_ERROR_COMMENT' => $_ARRAYLANG['TXT_CALENDAR_ERROR_COMMENT'],
			'TXT_CALENDAR_EDIT_EVENT'    => $_ARRAYLANG['TXT_CALENDAR_EDIT_EVENT'],
			'TXT_CALENDAR_NAME'	         => $_ARRAYLANG['TXT_CALENDAR_NAME'],
			'TXT_CALENDAR_PLACE'         => $_ARRAYLANG['TXT_CALENDAR_PLACE'],
//			'TXT_CALENDAR_PRIORITY'	     => $_ARRAYLANG['TXT_CALENDAR_PRIORITY'],
			'TXT_CALENDAR_START'         => $_ARRAYLANG['TXT_CALENDAR_START'],
			'TXT_CALENDAR_END'           => $_ARRAYLANG['TXT_CALENDAR_END'],
			'TXT_CALENDAR_COMMENT'       => $_ARRAYLANG['TXT_CALENDAR_COMMENT'],
			'TXT_CALENDAR_RESET'         => $_ARRAYLANG['TXT_CALENDAR_RESET'],
			'TXT_CALENDAR_SUBMIT'        => $_ARRAYLANG['TXT_CALENDAR_SUBMIT'],
			'TXT_CALENDAR_CAT'           => $_ARRAYLANG['TXT_CALENDAR_CAT'],
			'TXT_CALENDAR_ERROR_CATEGORY' => $_ARRAYLANG['TXT_CALENDAR_ERROR_CATEGORY'],
			'TXT_CALENDAR_INFO'          => $_ARRAYLANG['TXT_CALENDAR_INFO'],
			'TXT_CALENDAR_WHOLE_DAY'	 => $_ARRAYLANG['TXT_CALENDAR_WHOLE_DAY'],
			'TXT_CALENDAR_ACTIVE'		 => $_ARRAYLANG['TXT_CALENDAR_ACTIVE'],
			'CALENDAR_ACTIVE_CHECKED'	 => $checked,
			'CALENDAR_DESCRIPTION'		 => $ed,
			'CALENDAR_STARTDATE'		 => date("Y-m-d", $startdate),
			'CALENDAR_ENDDATE'			 => date("Y-m-d", $enddate)
		));
	}



	// select days
	function selectDay($day, $handle_name, $var1, $var2)
	{
	   	for ($x = 1; $x <= 31; $x++) {
	   		$x = str_pad($x, 2, '0', STR_PAD_LEFT);

	   		if ($x == $day) {
	   			$this->_objTpl->setVariable($var1, ' selected="selected"');
	   		}
	   		else {
	   			$this->_objTpl->setVariable($var1, '');
	   		}

			$this->_objTpl->setVariable($var2, $x);

			$this->_objTpl->parse($handle_name);
	   	}
	}

	/**
	 * Select Month
	 *
	 * Generates the selection dropdown for the month
	 */
	function selectMonth($month, $handle_name, $var1, $var2, $var3)
	{
		for ($x = 1; $x <= 12; $x++) {
			$x = str_pad($x, 2, '0', STR_PAD_LEFT);

			$this->_objTpl->setVariable($var1, $x);

			if ($x == $month) {
				$this->_objTpl->setVariable($var2, ' selected="selected');
			} else {
				$this->_objTpl->setVariable($var2, '');
			}

			$name = $this->monthName($x);
			$this->_objTpl->setVariable($var3, $name);

			$this->_objTpl->parse($handle_name);
		}
	}

	/**
	 * Select Year
	 *
	 * Makes a select dropdown for the years
	 */
	function selectYear($year, $handle_name, $var1, $var2)
	{
	    for ($x = $this->calStartYear; $x <= $this->calEndYear; $x++) {
	    	if ($x == $year) {
	    		$this->_objTpl->setVariable($var1, ' selected="selected"');
	    	} else {
	   			$this->_objTpl->setVariable($var1, '');
	   		}

			$this->_objTpl->setVariable($var2, $x);

			$this->_objTpl->parse($handle_name);
	    }
	}

	/**
	 * Select Hour
	 *
	 * Generates a selection dropdown for the hours
	 */
	function selectHour($hour, $handle_name, $bool_select, $varname)
	{
		for ($curhour = 0; $curhour <= 23; $curhour++) {
			if ($curhour == $hour) {
				$this->_objTpl->setVariable($bool_select, ' selected="selected"');
			} else {
				$this->_objTpl->setVariable($bool_select, '');
			}

			$this->_objTpl->setVariable($varname, sprintf("%02d", $curhour));
			$this->_objTpl->parse($handle_name);
		}
	}

	function selectMinutes($minutes, $handle_name, $bool_select, $varname)
	{
		for ($curmin = 0; $curmin <= 59; $curmin++) {
			if ($curmin == $minutes) {
				$this->_objTpl->setVariable($bool_select, ' selected="selected"');
			} else {
				$this->_objTpl->setVariable($bool_select, '');
			}

			$this->_objTpl->setVariable($varname, sprintf("%02d", $curmin));
			$this->_objTpl->parse($handle_name);
		}
	}


	/**
	 * write edited note
	 *
	 * Saves the changes of a note
	 */
	function writeEditedNote()
	{
		global $objDatabase, $_ARRAYLANG, $_LANGID;

	    $query = "SELECT id
		              FROM ".DBPREFIX."module_calendar
		      WHERE id = '".addslashes(intval($_POST['inputId']))."'";

	    $objResult = $objDatabase->Execute($query);

		if ($objDatabase->Affected_Rows() > 0) {
    		$catid  = intval($_POST['inputCategory']);
			$up_id       = intval($_POST['inputId']);
			$up_priority = intval($_POST['inputPriority']);
			$up_name     = contrexx_addslashes(contrexx_strip_tags($_POST['inputName']));
			$up_place    = contrexx_addslashes(contrexx_strip_tags($_POST['inputPlace']));
			$up_comment  = contrexx_addslashes($_POST['inputComment']);
			$info        = contrexx_addslashes(contrexx_strip_tags($_POST['inputInfo']));

			if (!empty($info)) {
				if (!preg_match("%^http:\/\/%", $info)) {
					$info = "http://".$info;
				}
			}

			$dateparts = split("-", $_POST['inputStartDate']);
			$startdate	 = mktime(intval($_POST['inputHour']), intval($_POST['inputMinutes']),00, $dateparts[1], $dateparts[2], $dateparts[0]);

			$dateparts = split("-", $_POST['inputEndDate']);
			$enddate	 = mktime(intval($_POST['inputEndHour']), intval($_POST['inputEndMinutes']),00, $dateparts[1], $dateparts[2], $dateparts[0]);

			if (!empty($_POST['inputActive'])) {
				$active = 1;
			} else{
				$active = 0;
			}

			$query = "UPDATE ".DBPREFIX."module_calendar
				SET catid = '".$catid."',
					active = '".$active."',
					startdate = '".$startdate."',
					enddate = '".$enddate."',
					priority = '".$up_priority."',
					name = '".$up_name."',
					comment = '".$up_comment."',
					place = '".$up_place."',
			        info = '".$info."'
				WHERE id = '".$up_id."'
			";

			$objDatabase->Execute($query);

			echo mysql_error();

			$this->strOkMessage = $_ARRAYLANG['TXT_CALENDAR_STAT_EDITED']."<br />";
		} else {
			$this->strErrMessage = $_ARRAYLANG['TXT_CALENDAR_STAT_ERROR_EXISTING']."<br />";
		}
	}



	/**
	 * New Note
	 *
	 * Form for a new note
	 */
	function newNote()
	{
	    global $objDatabase, $_ARRAYLANG, $_LANGID;

	    // categories
		$query = "SELECT id,
	                       name,
		                   lang
	                  FROM ".DBPREFIX."module_calendar_categories
	              ORDER BY 'pos'";

		$objResult = $objDatabase->Execute($query);

		if ($objResult !== false) {
			while(!$objResult->EOF) {
				$query = "SELECT lang
				               FROM ".DBPREFIX."languages
				              WHERE id = '".$objResult->fields['lang']."'";
				$objResult2 = $objDatabase->SelectLimit($query, 1);

				$this->_objTpl->setVariable(array(
			    	'CALENDAR_CAT_ID'       => $objResult->fields['id'],
			    	'CALENDAR_CAT_LANG'     => $objResult2->fields['lang'],
			    	'CALENDAR_CAT_NAME'     => $objResult->fields['name']
				));
				$this->_objTpl->parse("calendar_cat");
				$objResult->MoveNext();
			}
		}

	    // select days
		if (isset($_GET['time'])) {
			$timeDay = substr($_GET['time'], 0, 2);
		} else {
			$timeDay = strftime("%d");
		}

	    // select months
		if (isset($_GET['time'])) {
			$timeMonth = substr($_GET['time'], 2, 2);
		} else {
			$timeMonth = strftime("%m");
		}

	    // select years
		if (isset($_GET['time'])) {
			$timeYear = substr($_GET['time'], 4, 4);
		} else {
			$timeYear = strftime("%Y");
		}

	    // select time
	    $time_min = date("i") + 30;
	    $time_std = date("H") * 100;
	    if ($time_min >= 60){
	    	$time_min = $time_min - 60 + 100;
	    }

	    if (isset($_GET['time'])) {
	    	$time_std = substr($_GET['time'], 8, 2) * 100;
	    	$time_min = '00';
	    }

	    $this->selectHour(12, "hour", "CALENDAR_HOUR_SELECT", "CALENDAR_HOUR");
	    $this->selectMinutes(00, "minutes", "CALENDAR_MINUTES_SELECT", "CALENDAR_MINUTES");
	    $this->selectHour(13, "endhour", "CALENDAR_END_HOUR_SELECT", "CALENDAR_END_HOUR");
	    $this->selectMinutes(30, "endminutes", "CALENDAR_END_MINUTES_SELECT", "CALENDAR_END_MINUTES");

	    // select end time
	    $time_min = date("i");
	    $time_std = date("H") * 100 + 100;

	    if (isset($_GET['time'])) {
	    	$time_std = substr($_GET['time'], 8, 2) * 100;
	    	$time_min = '30';
	    }

	    $ed = get_wysiwyg_editor('inputComment',"", 'active');

	    // parse to template
		$this->_objTpl->setVariable(array(
			'TXT_CALENDAR_ERROR_CATEGORY' => $_ARRAYLANG['TXT_CALENDAR_ERROR_CATEGORY'],
			'TXT_CALENDAR_ACTIVE'		  => $_ARRAYLANG['TXT_CALENDAR_ACTIVE'],
			'TXT_CALENDAR_CAT'            => $_ARRAYLANG['TXT_CALENDAR_CAT'],
			'TXT_CALENDAR_ERROR_NAME'     => $_ARRAYLANG['TXT_CALENDAR_ERROR_NAME'],
			'TXT_CALENDAR_ERROR_PLACE'    => $_ARRAYLANG['TXT_CALENDAR_ERROR_PLACE'],
			'TXT_CALENDAR_ERROR_DATE'     => $_ARRAYLANG['TXT_CALENDAR_ERROR_DATE'],
			'TXT_CALENDAR_ERROR_COMMENT'  => $_ARRAYLANG['TXT_CALENDAR_ERROR_COMMENT'],
			'TXT_CALENDAR_NEW'            => $_ARRAYLANG['TXT_CALENDAR_NEW'],
			'TXT_CALENDAR_NAME'	          => $_ARRAYLANG['TXT_CALENDAR_NAME'],
			'TXT_CALENDAR_PLACE'          => $_ARRAYLANG['TXT_CALENDAR_PLACE'],
			'TXT_CALENDAR_PRIORITY'	      => $_ARRAYLANG['TXT_CALENDAR_PRIORITY'],
			'TXT_CALENDAR_START'          => $_ARRAYLANG['TXT_CALENDAR_START'],
			'TXT_CALENDAR_END'            => $_ARRAYLANG['TXT_CALENDAR_END'],
			'TXT_CALENDAR_COMMENT'        => $_ARRAYLANG['TXT_CALENDAR_COMMENT'],
			'TXT_CALENDAR_INFO'           => $_ARRAYLANG['TXT_CALENDAR_INFO'],
			'TXT_CALENDAR_RESET'          => $_ARRAYLANG['TXT_CALENDAR_RESET'],
			'TXT_CALENDAR_SUBMIT'         => $_ARRAYLANG['TXT_CALENDAR_SUBMIT'],
			'TXT_CALENDAR_WHOLE_DAY'	  => $_ARRAYLANG['TXT_CALENDAR_WHOLE_DAY'],
			'CALENDAR_TODAY'			  => date("Y-m-d"),
			'CALENDAR_DESCRIPTION'		  => $ed
		));
	}

	/**
	 * Write Note
	 *
	 * Saves a new note
	 */
	function writeNote()
	{
	    global $objDatabase, $_LANGID;

	    $_POST['inputInfo'] = str_replace(' ', '', $_POST['inputInfo']);

	    $catid    = intval($_POST['inputCategory']);
		$priority = intval($_POST['inputPriority']);
		$name     = contrexx_addslashes(contrexx_strip_tags($_POST['inputName']));
		$place    = contrexx_addslashes(contrexx_strip_tags($_POST['inputPlace']));
		$comment  = contrexx_addslashes($_POST['inputComment']);
		$info     = contrexx_addslashes(contrexx_strip_tags($_POST['inputInfo']));

		if (!empty($info)) {
			if (!preg_match("%^(?:ftp|http|https):\/\/%", $info)) {
				$info = "http://".$info;
			}
		}

		$dateparts = split("-", $_POST['inputStartDate']);
		$startdate	 = mktime(intval($_POST['inputHour']), intval($_POST['inputMinutes']),00, $dateparts[1], $dateparts[2], $dateparts[0]);

		if (empty($_POST['inputActive'])) {
			$active = 0;
		} else {
			$active = 1;
		}

		$dateparts = split("-", $_POST['inputEndDate']);
		$enddate	 = mktime(intval($_POST['inputEndHour']), intval($_POST['inputEndMinutes']),00, $dateparts[1], $dateparts[2], $dateparts[0]);


		$query = "INSERT INTO ".DBPREFIX."module_calendar (catid,
											active,
											startdate,
											enddate,
											priority,
											name,
											comment,
											place,
											info)
										VALUES ('$catid',
											'$active',
											'$startdate',
											'$enddate',
											'$priority',
											'$name',
											'$comment',
											'$place',
											'$info')";
		$objDatabase->Execute($query);

		return $objDatabase->Insert_ID();
	}

	// CASE cat
	function showCategories()
	{
	    global $objDatabase, $_ARRAYLANG, $_LANGID;

	    //new categorie
	    if (isset($_GET['new']) and $_GET['new'] == 1){
	    	if ($_POST['name'] != '' and $_POST['status'] != '' and $_POST['lang']){
	    		$_POST['name'] = CONTREXX_ESCAPE_GPC ? strip_tags($_POST['name']) : addslashes(strip_tags($_POST['name']));
			    $query = "SELECT id
				              FROM ".DBPREFIX."module_calendar_categories
				      WHERE BINARY name = '".$_POST['name']."'";

			    $objResult = $objDatabase->Execute($query);

				if ($objDatabase->Affected_Rows() == 0){
		    		$query = "INSERT INTO ".DBPREFIX."module_calendar_categories
		    		                    SET name = '".$_POST['name']."',
		    							    status = '".intval($_POST['status'])."',
		    		                        lang = '".intval($_POST['lang'])."'";
		    		$objDatabase->Execute($query);
		    		$this->strOkMessage = $_ARRAYLANG['TXT_CALENDAR_MESSAGE_NEW_CATRGORY'];
				}else{
					$this->strErrMessage = $_ARRAYLANG['TXT_CALENDAR_MESSAGE_ERROR_EXISTING'];
				}
	    	}else{
	    		$this->strErrMessage = $_ARRAYLANG['TXT_CALENDAR_MESSAGE_ERROR_FILL_IN_ALL'];
	    	}
	    }

	    //chg
	    if (isset($_GET['chg']) and $_GET['chg'] == 1 and isset($_POST['selected']) and is_array($_POST['selected'])) {
	    	//delete
	    	if ($_POST['form_delete'] != ''){
				$ids = $_POST['selected'];
				$x = 0;

				foreach($ids as $id){
					$query = "SELECT id
					              FROM ".DBPREFIX."module_calendar
					             WHERE catid = '".intval($id)."'";

					$objResult = $objDatabase->Execute($query);

					if ($objDatabase->Affected_Rows() == 0){
						$query = "DELETE FROM ".DBPREFIX."module_calendar_categories
				                          WHERE id = '".intval($id)."'";
						$objDatabase->SelectLimit($query, 1);
					} else{
						$x++;
						continue;
					}
				}

				if ($x > 0){
					$this->strOkMessage = $_ARRAYLANG['TXT_CALENDAR_CAT_RECORDS'];
				} else {
					$this->strOkMessage = $_ARRAYLANG['TXT_CALENDAR_CAT_DELETE'];
				}

				$query = "SELECT id FROM ".DBPREFIX."module_calendar_categories";

				$objResult = $objDatabase->Execute($query);

				if ($objDatabase->Affected_Rows() == 0){
					$query = "DELETE FROM ".DBPREFIX."module_calendar_categories";
					$objDatabase->Execute($query);
				}
			}

			//status
			if ($_POST['form_activate'] != '' or $_POST['form_deactivate'] != ''){
				$ids = $_POST['selected'];
				if ($_POST['form_activate'] != ''){
					$to = 1;
				}

				if ($_POST['form_deactivate'] != ''){
					$to = 0;
				}

				foreach($ids as $id){
					$query = "UPDATE ".DBPREFIX."module_calendar_categories
					               SET status = '".$to."'
					             WHERE id = '".intval($id)."'";
					$objDatabase->SelectLimit($query, 1);
				}

				$this->strOkMessage = $_ARRAYLANG['TXT_CALENDAR_CAT_CHANGE_STATUS'];

			}
	    }

		//sort
		if (isset($_GET['chg']) and $_GET['chg'] == 1 and $_POST['form_sort'] == 1){
			for ($x = 0; $x < count($_POST['form_id']); $x++){
				$query = "UPDATE ".DBPREFIX."module_calendar_categories
				               SET pos = '".intval($_POST['form_pos'][$x])."'
				             WHERE id = '".intval($_POST['form_id'][$x])."'";
				$objDatabase->Execute($query);
			}
			$this->strOkMessage = $_ARRAYLANG['TXT_CALENDAR_SORTING_COMPLETE'];
		}


	    //lang
	    $query = "SELECT id, name FROM ".DBPREFIX."languages";
	    $objResult = $objDatabase->Execute($query);

	    if ($objResult !== false) {
		    while(!$objResult->EOF) {
		    	$selected = '';
		    	if ($_LANGID == $objResult->fields['id']){
		    		$selected = ' selected="selected"';
		    	}

		    	$this->_objTpl->setVariable(array(
		    		'CALENDAR_LANG_ID'       => $objResult->fields['id'],
		    		'CALENDAR_LANG_SELECTED' => $selected,
		    		'CALENDAR_LANG_NAME'     => $objResult->fields['name']
		    	));
		    	$this->_objTpl->parse("calendar_lang");
		    	$objResult->MoveNext();
		    }
	    }

		$this->_objTpl->setVariable(array(
		    'TXT_CALENDAR_NO_OPERATION'        => $_ARRAYLANG['TXT_CALENDAR_NO_OPERATION'],
		    'TXT_CALENDAR_DELETE_CONFIRM'      => $_ARRAYLANG['TXT_CALENDAR_DELETE_CONFIRM'],
			'TXT_CALENDAR_INSERT_CATEGORY'     => $_ARRAYLANG['TXT_CALENDAR_INSERT_CATEGORY'],
			'TXT_CALENDAR_CATEGORY_NAME'       => $_ARRAYLANG['TXT_CALENDAR_CATEGORY_NAME'],
			'TXT_CALENDAR_STATUS'              => $_ARRAYLANG['TXT_CALENDAR_STATUS'],
			'TXT_CALENDAR_LANG'                => $_ARRAYLANG['TXT_CALENDAR_LANG'],
			'TXT_CALENDAR_INACTIVE'            => $_ARRAYLANG['TXT_CALENDAR_INACTIVE'],
			'TXT_CALENDAR_ACTIVE'              => $_ARRAYLANG['TXT_CALENDAR_ACTIVE'],
			'TXT_CALENDAR_SAVE'                => $_ARRAYLANG['TXT_CALENDAR_SAVE'],
			'TXT_CALENDAR_SORTING'             => $_ARRAYLANG['TXT_CALENDAR_SORTING'],
			'TXT_CALENDAR_ID'                  => $_ARRAYLANG['TXT_CALENDAR_ID'],
			'TXT_CALENDAR_FORMCHECK_NAME'      => $_ARRAYLANG['TXT_CALENDAR_FORMCHECK_NAME'],
			'TXT_CALENDAR_FORMCHECK_STATUS'    => $_ARRAYLANG['TXT_CALENDAR_FORMCHECK_STATUS'],
			'TXT_CALENDAR_FORMCHECK_LANG'      => $_ARRAYLANG['TXT_CALENDAR_FORMCHECK_LANG'],
			'TXT_CALENDAR_RECORDS'             => $_ARRAYLANG['TXT_CALENDAR_RECORDS']
		));

		//table
	    $query = "SELECT * FROM ".DBPREFIX."module_calendar_categories ORDER BY pos, id DESC";
	    $objResult = $objDatabase->Execute($query);
	    $i = 0;

	    if ($objResult !== false) {
		    while(!$objResult->EOF) {
		    	($i % 2)                ? $class  = 'row1'  : $class  = 'row2';
				($objResult->fields['status'] == 1) ? $status = 'green' : $status = 'red';

				$query = "SELECT id
				               FROM ".DBPREFIX."module_calendar
				              WHERE catid = '".intval($objResult->fields['id'])."'";
				$objResult2	= $objDatabase->Execute($query);
				$records = $objDatabase->Affected_Rows();

				$query = "SELECT name
				               FROM ".DBPREFIX."languages
				              WHERE id = '".intval($objResult->fields['lang'])."'";
				$objResult2 = $objDatabase->SelectLimit($query, 1);

				$lang = $objResult2->fields['name'];

				$this->_objTpl->setVariable(array(
				    'CALENDAR_CLASS'             => $class,
				    'CALENDAR_ID'                => $objResult->fields['id'],
				    'CALENDAR_POS'               => $objResult->fields['pos'],
				    'CALENDAR_STATUS'            => $status,
				    'TXT_CALENDAR_EDIT'          => $_ARRAYLANG['TXT_CALENDAR_EDIT'],
				    'CALENDAR_NAME'              => $objResult->fields['name'],
				    'CALENDAR_RECORDS'           => $records,
				    'CALENDAR_CAT_LANG'          => $lang
				));
				$this->_objTpl->parse("calendar_cat_row");
				$i++;
				$objResult->MoveNext();
		    }
	    }

	    $this->_objTpl->setVariable(array(
	        'TXT_CALENDAR_MARK_ALL'           => $_ARRAYLANG['TXT_CALENDAR_MARK_ALL'],
	        'TXT_CALENDAR_REMOVE_CHOICE'      => $_ARRAYLANG['TXT_CALENDAR_REMOVE_CHOICE'],
	        'TXT_CALENDAR_SELECT_OPERATION'   => $_ARRAYLANG['TXT_CALENDAR_SELECT_OPERATION'],
	        'TXT_CALENDAR_SAVE_SORTING'       => $_ARRAYLANG['TXT_CALENDAR_SAVE_SORTING'],
	        'TXT_CALENDAR_ACTIVATE_CAT'       => $_ARRAYLANG['TXT_CALENDAR_ACTIVATE_CAT'],
	        'TXT_CALENDAR_DEACTIVATE_CAT'     => $_ARRAYLANG['TXT_CALENDAR_DEACTIVATE_CAT'],
	        'TXT_CALENDAR_DELETE_CAT'         => $_ARRAYLANG['TXT_CALENDAR_DELETE_CAT']
	    ));
	}

	// CASE catedit
	function categoriesEdit()
	{
		global $objDatabase, $_ARRAYLANG, $_LANGID;

		//set
		if (isset($_GET['set']) and $_GET['set'] == 1) {
			if ($_POST['id'] != '' and $_POST['name'] != '' and $_POST['status'] != '' and $_POST['lang']) {
				$_POST['name'] = CONTREXX_ESCAPE_GPC ? strip_tags($_POST['name']) : addslashes(strip_tags($_POST['name']));
				$query = "SELECT id
				              FROM ".DBPREFIX."module_calendar_categories
				      WHERE BINARY name = '".$_POST['name']."'
				               AND id <> '".intval($_POST['id'])."'";

				$objResult = $objDatabase->Execute($query);

				if ($objDatabase->affected_rows() == 0){
					$query = "UPDATE ".DBPREFIX."module_calendar_categories
								   SET name = '".$_POST['name']."',
					                   status = '".intval($_POST['status'])."',
					                   lang = '".intval($_POST['lang'])."'
					             WHERE id = '".intval($_POST['id'])."'";
					$objDatabase->Execute($query);

					$this->strOkMessage = $_ARRAYLANG['TXT_CALENDAR_MESSAGE_SUCCESSFULL_EDIT_CAT'];
				} else {
					$this->strErrMessage = $_ARRAYLANG['TXT_CALENDAR_MESSAGE_ERROR_EXISTING'];
				}
			} else {
				$this->strErrMessage = $_ARRAYLANG['TXT_CALENDAR_MESSAGE_ERROR_FILL_IN_ALL'];
			}
		}

		//-----------------------------------------------------------------------------------------

		if (isset($_GET['id']) and $_GET['id'] != '') {
			$this->_objTpl->setVariable(array(
				'TXT_CALENDAR_EDIT_CAT'            => $_ARRAYLANG['TXT_CALENDAR_EDIT_CAT'],
				'TXT_CALENDAR_CATEGORY_NAME'       => $_ARRAYLANG['TXT_CALENDAR_CATEGORY_NAME'],
				'TXT_CALENDAR_STATUS'              => $_ARRAYLANG['TXT_CALENDAR_STATUS'],
				'TXT_CALENDAR_LANG'                => $_ARRAYLANG['TXT_CALENDAR_LANG'],
				'TXT_CALENDAR_INACTIVE'            => $_ARRAYLANG['TXT_CALENDAR_INACTIVE'],
				'TXT_CALENDAR_ACTIVE'              => $_ARRAYLANG['TXT_CALENDAR_ACTIVE'],
				'TXT_CALENDAR_SAVE'                => $_ARRAYLANG['TXT_CALENDAR_SAVE'],
				'TXT_CALENDAR_RESET'               => $_ARRAYLANG['TXT_CALENDAR_RESET'],
				'TXT_CALENDAR_FORMCHECK_NAME'      => $_ARRAYLANG['TXT_CALENDAR_FORMCHECK_NAME'],
				'TXT_CALENDAR_FORMCHECK_STATUS'    => $_ARRAYLANG['TXT_CALENDAR_FORMCHECK_STATUS'],
				'TXT_CALENDAR_FORMCHECK_LANG'      => $_ARRAYLANG['TXT_CALENDAR_FORMCHECK_LANG']
			));

			$query = "SELECT id,
			                   name,
			                   status,
			                   lang
			              FROM ".DBPREFIX."module_calendar_categories
			             WHERE id = '".intval($_GET['id'])."'";

			$objResult = $objDatabase->SelectLimit($query, 1);

			if ($objDatabase->Affected_Rows() == 0) {
				header("Location: index.php?cmd=calendar&act=cat");
				die;
			}

			if ($objResult->fields['status'] == 0) {
				$status0 = ' selected="selected"';
				$status1 = '';
			} else {
				$status0 = '';
				$status1 = ' selected="selected"';
			}

			$query = "SELECT id,
			                    name
			               FROM ".DBPREFIX."languages";

			$objResult2 = $objDatabase->Execute($query);

			if ($objResult !== false) {
				while(!$objResult2->EOF) {
					$selected = '';
					if ($objResult->fields['lang'] == $objResult2->fields['id']) {
						$selected = ' selected="selected"';
					}

					$this->_objTpl->setVariable(array(
					    'CALENDAR_LANG_ID'       => $objResult2->fields['id'],
					    'CALENDAR_LANG_SELECTED' => $selected,
					    'CALENDAR_LANG_NAME'     => $objResult2->fields['name']
					));

					$this->_objTpl->parse("calendar_lang");
					$objResult2->MoveNext();
				}
			}

			$this->_objTpl->setVariable(array(
				'CALENDAR_CAT_ID'        => $objResult->fields['id'],
				'CALENDAR_CAT_NAME'      => $objResult->fields['name'],
				'CALENDAR_STATUS0'       => $status0,
				'CALENDAR_STATUS1'       => $status1
			));
		} else {
			header("Location: index.php?cmd=calendar&act=cat");
			die;
		}

	}


	// do search
	function search()
	{
	    global $objDatabase, $_ARRAYLANG, $_LANGID;

	    $this->_objTpl->loadTemplateFile('module_calendar_overview.html');
		$this->_objTpl->setVariable("CONTENT", $this->pageContent);

		$i = 0;

		$search   = $_POST['inputName'];
		if ($_POST['inputName'] == ''){
			$search = "[a-z0-9]";
		}

		$query = "
			SELECT * FROM ".DBPREFIX."module_calendar
			ORDER BY startdate ASC, priority ASC, id ASC";

		$objResult = $objDatabase->Execute($query);

		$objResult = $objDatabase->Execute($query);

		if ($objDatabase->Affected_Rows() > 0) {
			while(!$objResult->EOF) {
				$today = time();
				if ($objResult->fields['startdate'] > $today || $objResult->fields['enddate'] > $today) {
					$status = "green";
				} else {
					$status = "red";
				}

				$this->_objTpl->setVariable(array(
					'CALENDAR_ACTIVE_ICON'		=> $status,
					'CALENDAR_EVENT_ID'			=> $objResult->fields['id'],
					'CALENDAR_EVENT_STARTDATE'	=> date(ASCMS_DATE_FORMAT, $objResult->fields['startdate']),
					'CALENDAR_EVENT_ENDDATE'	=> date(ASCMS_DATE_FORMAT, $objResult->fields['enddate']),
					'CALENDAR_EVENT_TITLE'		=> $objResult->fields['name'],
					'CALENDAR_EVENT_CAT'		=> $cats[$objResult->fields['catid']]
				));

				$this->_objTpl->parse("event");
				$objResult->moveNext();
			}
		}
	}

	/**
	 * Settings
	 *
	 * Shows the settings page
	 */
	function settings()
	{
	    global $objDatabase, $_ARRAYLANG, $_LANGID, $_CONFIG;

		if ($_CONFIG['calendarheadlines']) {
			$headlines_checked = "checked=\"checked\"";
		} else {
			$headlines_checked = "";
		}

		// makes the category list
    	$query = "SELECT id,name,lang FROM ".DBPREFIX."module_calendar_categories ORDER BY pos";
		$objResult = $objDatabase->Execute($query);

		while (!$objResult->EOF) {
			if ($objResult->fields['id'] == $_CONFIG['calendarheadlinescat']) {
				$selected = " selected=\"selected\"";
			} else {
				$selected = "";
			}

			$calendar_categories .= "<option value=\"".$objResult->fields['id']."\"$selected>".$objResult->fields['name']."</option>";
			$objResult->MoveNext();
		}

		$query = "SELECT id,name,lang FROM ".DBPREFIX."module_calendar_categories ORDER BY pos";
		$objResult = $objDatabase->Execute($query);

//		 Parse
		$this->_objTpl->setVariable(array(
		    'TXT_CALENDAR_MENU_SETTINGS'        => $_ARRAYLANG['TXT_CALENDAR_MENU_SETTINGS'],
	        'TXT_CALENDAR_STD_CAT'              => $_ARRAYLANG['TXT_CALENDAR_STD_CAT'],
	        'TXT_CALENDAR_SET_HEADLINES'		=> $_ARRAYLANG['TXT_CALENDAR_SET_HEADLINES'],
	        'CALENDAR_HEADLINES_CHECKED'		=> $headlines_checked,
	        'TXT_CALENDAR_SET_HEADLINESCOUNT'	=> $_ARRAYLANG['TXT_CALENDAR_SET_HEADLINESCOUNT'],
	        'CALENDAR_HEADLINESCOUNT'			=> $_CONFIG['calendarheadlinescount'],
	        'TXT_SUBMIT'						=> $_ARRAYLANG['TXT_SUBMIT'],
	        'TXT_CALENDAR_ALL_CAT'				=> $_ARRAYLANG['TXT_CALENDAR_ALL_CAT'],
	        'TXT_CALENDAR_SET_HEADLINESCAT'	  	=> $_ARRAYLANG['TXT_CALENDAR_SET_HEADLINESCAT'],
	        'CALENDAR_CATEGORIES'				=> $calendar_categories,
	        'TXT_CALENDAR_SET_STDCOUNT'			=> $_ARRAYLANG['TXT_CALENDAR_SET_STDCOUNT'],
	        'CALENDAR_STANDARDNUMBER'			=> $_CONFIG['calendardefaultcount']
	    ));

	    $this->_objTpl->setGlobalVariable('TXT_CALENDAR_STD_CAT_NONE', $_ARRAYLANG['TXT_CALENDAR_STD_CAT_NONE']);

//		set standard
		$query = "SELECT stdCat
		              FROM ".DBPREFIX."module_calendar_style
		             WHERE id = '2'";

		$objResult = $objDatabase->SelectLimit($query, 1);

	    $array1 = explode(' ', $objResult->fields['stdCat']);
		$cats   = '';
//
		foreach($array1 as $out){
			$array2           = explode('>', $out);
			$cats[$array2[0]] = $array2[1];
		}

		// get active languages
		$lang = array(0=>"foo"); // this is needed because otherwise the first index would be 0, that's false and doesn't work with the if query below
		$query = "SELECT id FROM ".DBPREFIX."languages
					WHERE frontend = 1";
		$objResult = $objDatabase->Execute($query);
		while(!$objResult->EOF) {
			$lang[] = $objResult->fields['id'];
			$objResult->MoveNext();
		}

		$query = "SELECT id,name FROM ".DBPREFIX."languages ORDER BY id";
		$objResult = $objDatabase->Execute($query);
		$i = 0;


		if ($objResult !== false) {
			while(!$objResult->EOF) {
				if (array_search($objResult->fields['id'], $lang) != false) {
					$query = "SELECT id,
					                    name
					               FROM ".DBPREFIX."module_calendar_categories
					              WHERE lang = '".$objResult->fields['id']."'
					                AND status = '1'
					           ORDER BY pos";
					$objResult2 = $objDatabase->Execute($query);

					$cal_option = '';
					while(!$objResult2->EOF){
						$select = '';
						if ($cats[$objResult->fields['id']] == $objResult->fields['id']){
							$select = ' selected';
						}
						$cal_option .= "<option value=\"".$objResult2->fields['id']."\"".$select.">".$objResult2->fields['name']."</option>\n";
						$objResult2->MoveNext();
					}

					($i % 2) ? $class = 'row2' : $class = 'row1';

					$this->_objTpl->setVariable(array(
					    'CALENDAR_STD_CLASS'    => $class,
					    'CALENDAR_STD_LANG'     => $objResult->fields['name'],
					    'CALENDAR_STD_LANG_ID'  => $objResult->fields['id'],
					    'CALENDAR_OPTION'       => $cal_option
					));
					$this->_objTpl->parse("calendar_std_lang");
					$i++;
				}
				$objResult->MoveNext();
			}
		}
	}

	// set std cat
	function setStdCat()
	{
	    global $objDatabase, $_ARRAYLANG;

	    $string = '';
	    for ($x = 0; $x < count($_POST['stdCatLang']); $x++)
	    {
	    	$string .= intval($_POST['stdCatLang'][$x]).">".intval($_POST['stdCat'][$x])." ";
	    }
	    $string = trim($string);

		$query = "
		    UPDATE ".DBPREFIX."module_calendar_style
		    SET stdCat = '".$string."'
			WHERE id = '2'";

		$objDatabase->Execute($query);

		$this->strOkMessage = $_ARRAYLANG['TXT_CALENDAR_STYLE_MODIFIED']."<br />";
	}


	/**
	 * Save Settings
	 *
	 * Saves the settings
	 */
	function saveSettings()
	{
		global $objDatabase, $_CORELANG, $objSettings;

		if (empty($_POST['headlines'])) {
			$val = "0";
		} else {
			$val = "1";
		}

		$query = "UPDATE ".DBPREFIX."settings
				SET setvalue = '$val'
				WHERE setname = 'calendarheadlines'";
		$objDatabase->Execute($query);

		$query = "UPDATE ".DBPREFIX."settings
				SET setvalue = '".intval($_POST['headlinescount'])."'
				WHERE setname = 'calendarheadlinescount'";
		$objDatabase->Execute($query);

		$query = "UPDATE ".DBPREFIX."settings
				SET setvalue = ".intval($_POST['headlinescat'])."
				WHERE setname = 'calendarheadlinescat'";
		$objDatabase->Execute($query);

		$query = "UPDATE ".DBPREFIX."settings
				SET setvalue = ".intval($_POST['defaultlistcount'])."
				WHERE setname = 'calendardefaultcount'";
		$objDatabase->Execute($query);

		$objSettings = &new settingsManager();
       	$objSettings->writeSettingsFile();
	}

	/**
	 * Show Placeholder
	 *
	 * Shows the list of the placeholder for the
	 * startpage events
	 */
	function showPlaceholders()
	{
		global $objDatabase, $_ARRAYLANG, $_LANGID;

//		$objDatabase->debug = true;

		$this->_objTpl->loadTemplateFile('module_calendar_placeholder.html');

		$this->_objTpl->setVariable(array(
			"TXT_USAGE"		=> $_ARRAYLANG['TXT_USAGE'],
			"TXT_CALENDAR_PLACEHOLDER_INTRO"	=> $_ARRAYLANG['TXT_CALENDAR_PLACEHOLDER_INTRO'],
			"TXT_CALENDAR_PLACEHOLDER_LIST"		=> $_ARRAYLANG['TXT_CALENDAR_PLACEHOLDER_LIST'],
			"TXT_CALENDAR_EVENT_STARTDATE"		=> $_ARRAYLANG['TXT_CALENDAR_EVENT_STARTDATE'],
			"TXT_CALENDAR_EVENT_STARTTIME"		=> $_ARRAYLANG['TXT_CALENDAR_EVENT_STARTTIME'],
			"TXT_CALENDAR_EVENT_ENDDATE"		=> $_ARRAYLANG['TXT_CALENDAR_EVENT_ENDDATE'],
			"TXT_CALENDAR_EVENT_ENDTIME"		=> $_ARRAYLANG['TXT_CALENDAR_EVENT_ENDTIME'],
			"TXT_CALENDAR_EVENT_NAME"			=> $_ARRAYLANG['TXT_CALENDAR_EVENT_NAME'],
			"TXT_CALENDAR_EVENT_ID"				=> $_ARRAYLANG['TXT_CALENDAR_EVENT_ID']
		));
	}

	/**
	* activate note
	*
	* change the status from a note
	*
	* @access private
	* @global array $_ARRAYLANG
	*/
	function activateNote()
	{
		global $_ARRAYLANG, $objDatabase;

		$arrStatusNote = $_POST['selectedEventId'];
		if($arrStatusNote != null){
			foreach ($arrStatusNote as $noteId){
				$query = "UPDATE ".DBPREFIX."module_calendar SET active='1' WHERE id=$noteId";
				$objDatabase->Execute($query);
			}
		}
	}

	/**
	* deactivate note
	*
	* change the status from a note
	*
	* @access private
	* @global array $_ARRAYLANG
	*/
	function deactivateNote()
	{
		global $_ARRAYLANG, $objDatabase;

		$arrStatusNote = $_POST['selectedEventId'];
		if($arrStatusNote != null){
			foreach ($arrStatusNote as $noteId){
				$query = "UPDATE ".DBPREFIX."module_calendar SET active='0' WHERE id=$noteId";
				$objDatabase->Execute($query);
			}
		}
	}
}
?>
