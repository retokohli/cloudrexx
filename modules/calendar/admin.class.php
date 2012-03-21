<?php
/**
 * Calendar
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Paulo M. Santos <pmsantos@astalavista.net>
 * @package     contrexx
 * @subpackage  module_calendar".$this->mandateLink."
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
if (CALENDAR_MANDATE == 1) {
    require_once ASCMS_MODULE_PATH . '/calendar/lib/calendarLib.class.php';
} else {
    require_once ASCMS_MODULE_PATH . '/calendar'.CALENDAR_MANDATE.'/lib/calendarLib.class.php';
}
require_once ASCMS_CORE_PATH.'/settings.class.php';
require_once ASCMS_MODULE_PATH . '/calendar/lib/series.class.php';

/**
 * Calendar
 *
 * Class to manage cms calendar
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Paulo M. Santos <pmsantos@astalavista.net>
 * @package     contrexx
 * @subpackage  module_calendar".$this->mandateLink."
 */
class calendarManager extends calendarLibrary
{
    var $pageTitle;
    var $_csvSeparator = ';';

    private $act = '';
    
    /**
     * PHP 5 Constructor
     */
    function __construct()
    {
        $this->calendarManager();
    }
    private function setNavigation()
    {
        global $objTemplate, $_ARRAYLANG;

        $objTemplate->setVariable("CONTENT_NAVIGATION","
            <a href='index.php?cmd=calendar".$this->mandateLink."' class='".($this->act == '' ? 'active' : '')."'> ".$_ARRAYLANG['TXT_CALENDAR_MENU_OVERVIEW']." </a>
            <a href='index.php?cmd=calendar".$this->mandateLink."&amp;act=new' class='".($this->act == 'new' ? 'active' : '')."'> ".$_ARRAYLANG['TXT_CALENDAR_MENU_ENTRY']." </a>
            <a href='index.php?cmd=calendar".$this->mandateLink."&amp;act=cat' class='".($this->act == 'cat' ? 'active' : '')."'> ".$_ARRAYLANG['TXT_CALENDAR_CATEGORIES']." </a>
            <a href='index.php?cmd=calendar".$this->mandateLink."&amp;act=placeholder' class='".($this->act == 'placeholder' ? 'active' : '')."'>".$_ARRAYLANG['TXT_CALENDAR_PLACEHOLDER']."</a>
            <a href='index.php?cmd=calendar".$this->mandateLink."&amp;act=settings' class='".($this->act == 'settings' ? 'active' : '')."'> ".$_ARRAYLANG['TXT_CALENDAR_MENU_SETTINGS']." </a>");
    }

    /**
     * PHP 4 Constructor
     */
    function calendarManager()
    {
        global $_ARRAYLANG, $objTemplate;

        parent::__construct($_SERVER["SCRIPT_NAME"]."?cmd=calendar".$this->mandateLink);
        // links
        $this->pageTitle = $_ARRAYLANG['TXT_CALENDAR'];
        
        $this->showOnlyActive = false;
    }


    /**
     * Get Calendar Page
     */
    function getCalendarPage()
    {
        global $objTemplate, $objDatabase;

        if (!isset($_REQUEST['act'])){
            $_REQUEST['act'] = '';
        }

        switch ($_REQUEST['act']) {
            case 'event':
                $this->showEvent(intval($_GET['id']));
                break;

            case 'reg':
                $this->_objTpl->loadTemplateFile('module_calendar_reg_overview.html');
                $this->showRegistrationList(intval($_GET['id']));
                break;

            case 'regdetail':
                $this->_objTpl->loadTemplateFile('module_calendar_reg_show.html');
                $this->showRegistrationDetail(intval($_GET['id']));
                break;

            case 'regdelete':
                $this->deleteRegistration(intval($_GET['id']));
                $this->_objTpl->loadTemplateFile('module_calendar_reg_overview.html');
                $this->showRegistrationList(intval($_GET['note_id']));
                break;

            case 'regsign':
                $this->signRegistration(intval($_GET['type']));
                $this->_objTpl->loadTemplateFile('module_calendar_reg_overview.html');
                $this->showRegistrationList(intval($_GET['note_id']));
                break;

            case 'new':
                $this->_objTpl->loadTemplateFile('module_calendar_note_modify.html');
                $this->modifyNote();
                break;

            case 'saveNew':
                $id = $this->writeNote('');
                CSRF::header("Location: index.php?cmd=calendar".$this->mandateLink."&act=event&id=$id");
                exit;
                break;

            case 'getCSV':
                $id = intval($_GET['id']);
                $this->_getCsv($id);
                break;

            case 'cat':
                $this->_objTpl->loadTemplateFile('module_calendar_categories.html');
                $this->showCategories();
                break;

            case 'catedit':
                $this->_objTpl->loadTemplateFile('module_calendar_categories_edit.html');
                $this->categoriesEdit();
                break;

            case 'edit':
                $this->_objTpl->loadTemplateFile('module_calendar_note_modify.html');
                $this->modifyNote(intval($_GET['id']));
                break;

            case 'saveEdited':
                $id = $this->writeNote();
                CSRF::header("Location: index.php?cmd=calendar".$this->mandateLink);
                exit;
                break;

            case 'settings':
                $this->_objTpl->loadTemplateFile('module_calendar_settings.html');
                $this->settings();
                break;

            case 'saveSettings':
                $this->saveSettings();
                $this->setStdCat();
                CSRF::header("Location: index.php?cmd=calendar".$this->mandateLink."&act=settings");
                exit;
                break;

            case 'event_actions':
                $this->multiDelete();
                break;

            case 'delete':
                $this->delNote($_GET['id']);
                $this->showOverview();
                break;

            case 'activate':
                $this->activateNote();
                $this->showOverview();
                break;

            case 'deactivate':
                $this->deactivateNote();
                $this->showOverview();
                break;

            case 'placeholder':
                $this->showPlaceholders();
                break;
            case 'toggle_event':
                $this->_toggleEvent();
                $this->showOverview();
                break;
            default:
                $this->showOverview();
        }

        $objTemplate->setVariable(array(
            'CONTENT_OK_MESSAGE'        => $this->strOkMessage,
            'CONTENT_STATUS_MESSAGE'    => $this->strErrMessage,
            'ADMIN_CONTENT'             => $this->_objTpl->get(),
            'CONTENT_TITLE'             => $this->pageTitle,
        ));

        $this->act = $_REQUEST['act'];
        $this->setNavigation();
    }


    /**
     * Show Day
     *
     * Shows the three calendar boxes an the notes
     */
    function showOverview()
    {
        global $objDatabase, $_ARRAYLANG, $_LANGID;

        $this->_objTpl->loadTemplateFile('module_calendar_overview.html');

        $catid = (isset($_GET['catid'])) ? $_GET['catid'] : "";

        // Stuff for the category selection
        $requestUri = str_replace('&catid='.$catid, '', $_SERVER['REQUEST_URI']);
        $query = " SELECT id,name,lang
                   FROM ".DBPREFIX."module_calendar".$this->mandateLink."_categories
                   ORDER BY pos";
        $objResult = $objDatabase->Execute($query);
        if ($objResult !== false) {
            while(!$objResult->EOF) {
                $select = '';
                if ($objResult->fields['id'] == $catid){
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

		$select_next_ten = false;
		// Checks the variables and gets the boxes
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

		$startdate = mktime(0, 0, 0, $month, 1, $year);
    	$enddate = mktime(23, 59, 59, $month+3, 1, $year);

    	//get category
        if (!empty($_GET['catid'])) {
    		$category = intval($_GET['catid']);
    	} else {
    		$category = null;
    	}

    	$this->objSeries 	= new seriesManager($this->mandateLink);
		$this->eventList 	= $this->objSeries->getEventList($startdate,$enddate,999, 1, array_key_exists('search', $_POST) ? $_POST['search'] : '', $category);

		$calendarbox 	= $this->getBoxes(3, $year, $month, $day, $catid);

		//build query
		if (isset($_POST['search'])) {
			$keyword = htmlentities(contrexx_addslashes($_POST['inputKeyword']), ENT_QUOTES, CONTREXX_CHARSET);
			$query = "SELECT active, id, name, catid, startdate, enddate, series_status, series_pattern_dourance_type, series_pattern_end
					  	FROM ".DBPREFIX."module_calendar".$this->mandateLink."
					  	WHERE (`name` LIKE '%$keyword%' OR
					  	`comment` LIKE '%$keyword%' OR
					  	`id` LIKE '%$keyword%')"
                        .($category ? " AND `catid` = $category " : '')
					    ."ORDER BY startdate";
		} else {
			if (empty($_GET['catid'])) {
				$query = "SELECT active, id, name, catid, startdate, enddate, series_status, series_pattern_dourance_type, series_pattern_end
					FROM ".DBPREFIX."module_calendar".$this->mandateLink."
					WHERE ((startdate > $startdate) OR
					(enddate > $startdate)) OR
					(series_status = 1)
					ORDER BY startdate ASC";

			} else {
				$query = "SELECT active, id, name, catid, startdate, enddate, series_status, series_pattern_dourance_type, series_pattern_end
					FROM ".DBPREFIX."module_calendar".$this->mandateLink."
					WHERE catid = $category AND
					((startdate BETWEEN $startdate AND $enddate) OR
					(enddate BETWEEN $startdate AND $enddate) OR
					(startdate < $startdate AND enddate > $startdate) OR
					(series_status = 1))
					ORDER BY startdate ASC";
			}
		}

        $objResult = $objDatabase->Execute($query);
        $rowcounter = 2;

        if ($objDatabase->Affected_Rows() > 0) {
            while(!$objResult->EOF) {
                $today = time();

                //
                // checks if the series would be active in dependency of the time... activestate will be checked below
                //
				if ($objResult->fields['series_status'] == 1) {
					if ($objResult->fields['series_pattern_dourance_type'] == 3 && $objResult->fields['series_pattern_end'] < $today) {
						$status = "red";
						$event_led = $_ARRAYLANG['TXT_CALENDAR_LED_OLD'];
					} else {
						$status = "green";
						$event_led = $_ARRAYLANG['TXT_CALENDAR_LED_ACTIVE'];
					}

					$series = '<img src="images/icons/refresh.gif" alt="'.$_ARRAYLANG['TXT_CALENDAR_SERIES'].'" border="0">';
				} else {
					$series = '';
				}

                if ($objResult->fields['active'] == "0") {
                    $status = "red";
                    $event_led = $_ARRAYLANG['TXT_CALENDAR_LED_INACTIVE'];
                } elseif ($objResult->fields['startdate'] > $today || $objResult->fields['enddate'] > $today  || $status == 'green') {
                    $status = "green";
                    $event_led = $_ARRAYLANG['TXT_CALENDAR_LED_ACTIVE'];
                } else {
                    $status = "red";
                    $event_led = $_ARRAYLANG['TXT_CALENDAR_LED_OLD'];
                }

				$reg_signoff = $this->_countRegistrations($objResult->fields['id']);

				$this->_objTpl->setVariable(array(
					'CALENDAR_SERIES'			=> $series,
					'CALENDAR_ACTIVE_ICON'		=> $status,
					'CALENDAR_EVENT_ID'			=> $objResult->fields['id'],
					'CALENDAR_EVENT_STARTDATE'	=> date(ASCMS_DATE_FORMAT, $objResult->fields['startdate']),
					'CALENDAR_EVENT_ENDDATE'	=> date(ASCMS_DATE_FORMAT, $objResult->fields['enddate']),
					'CALENDAR_EVENT_TITLE'		=> htmlentities($objResult->fields['name'], ENT_QUOTES, CONTREXX_CHARSET),
					'CALENDAR_EVENT_CAT'		=> $cats[$objResult->fields['catid']],
					'CALENDAR_ROW'				=> "row".$rowcounter,
					'CALENDAR_EVENT_LED'		=> $event_led,
					'CALENDAR_EVENT_COUNT_REG'	=> $reg_signoff[0],
					'CALENDAR_EVENT_COUNT_SIGNOFF'	=> $reg_signoff[1],
					'CALENDAR_EVENT_COUNT_SUBSCRIBER'	=> $this->_countSubscriber($objResult->fields['id']),
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
                'CALENDAR_MONTHLIST_VALUE'  => $i,
                'CALENDAR_MONTHLIST_NAME'   => $name,
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
                'CALENDAR_YEARLIST_VALUE'   => $i,
                'CALENDAR_YEARLIST_SELECTED' => $selected
            ));

            $this->_objTpl->parse("calendar_yearlist");
        }

		// Variable assignement
		$this->_objTpl->setVariable(array(
			'TXT_EVENTS'					=> $_ARRAYLANG['TXT_CALENDAR_EVENTS'],
			'TXT_SERIES'					=> $_ARRAYLANG['TXT_CALENDAR_SERIES'],
			'TXT_CALENDAR_REGISTRATIONS'	=> $_ARRAYLANG['TXT_CALENDAR_REGISTRATIONS'],
			'TXT_CALENDAR_SUBSCRIBER'		=> $_ARRAYLANG['TXT_CALENDAR_REGISTRATIONS_SUBSCRIBER'],
			'TXT_CALENDAR_OVERVIEW'			=> $_ARRAYLANG['TXT_CALENDAR_MENU_OVERVIEW'],
			'TXT_CALENDAR'		   			=> $_ARRAYLANG['TXT_CALENDAR'],
			'TXT_CALENDAR_CAT'	  			=> $_ARRAYLANG['TXT_CALENDAR_CAT'],
			'TXT_CALENDAR_SEARCH'			=> $_ARRAYLANG['TXT_CALENDAR_SEARCH'],
			'TXT_CALENDAR_KEYWORD'			=> $_ARRAYLANG['TXT_CALENDAR_KEYWORD'],
			'TXT_GO'						=> $_ARRAYLANG['TXT_GO'],
			'TXT_SEARCH'					=> $_ARRAYLANG['TXT_CALENDAR_SEARCH'],
			'TXT_CALENDAR_START'			=> $_ARRAYLANG['TXT_CALENDAR_START'],
			'TXT_CALENDAR_END'				=> $_ARRAYLANG['TXT_CALENDAR_END'],
			'TXT_CALENDAR_TITLE'			=> $_ARRAYLANG['TXT_CALENDAR_TITLE'],
			'TXT_CALENDAR_CAT'				=> $_ARRAYLANG['TXT_CALENDAR_CAT'],
			'TXT_CALENDAR_ACTION'			=> $_ARRAYLANG['TXT_CALENDAR_ACTION'],
			'TXT_SUBMIT_SELECT'				=> $_ARRAYLANG['TXT_SUBMIT_SELECT'],
			'TXT_SUBMIT_DELETE'				=> $_ARRAYLANG['TXT_SUBMIT_DELETE'],
			'TXT_SUBMIT_ACTIVATE'			=> $_ARRAYLANG['TXT_SUBMIT_ACTIVATE'],
			'TXT_SUBMIT_DEACTIVATE'			=> $_ARRAYLANG['TXT_SUBMIT_DEACTIVATE'],
			'TXT_SELECT_ALL'				=> $_ARRAYLANG['TXT_SELECT_ALL'],
			'TXT_DESELECT_ALL'				=> $_ARRAYLANG['TXT_DESELECT_ALL'],
			'TXT_CALENDAR_DELETE_CONFIRM' 	=> addslashes($_ARRAYLANG['TXT_CALENDAR_DELETE_CONFIRM']),
			'CALENDAR'			   			=> $calendarbox,
			# 'CALENDAR_DATE'		   			=> $titledate,  // TODO: this variable is not defined!
			'TXT_CALENDAR_ALL_CAT' 			=> $_ARRAYLANG['TXT_CALENDAR_ALL_CAT'],
			'CALENDAR_REQUEST_URI' 			=> $requestUri,
			'CALENDAR_CATID'       			=> $catid,
			'TXT_CALENDAR_CSV_FILE'       	=> $_ARRAYLANG['TXT_CALENDAR_CSV_FILE']
		));
	}

    /**
     * Show Registrations
     *
     * Shows egistrations for each note
     */
    function showRegistrationList($id)
    {
        global $objDatabase, $_ARRAYLANG, $_CORELANG , $_LANGID;

        $this->pageTitle=$_ARRAYLANG['TXT_CALENDAR_REGISTRATIONS'];

        //get fields
        $query = "SELECT id
                    FROM ".DBPREFIX."module_calendar".$this->mandateLink."_registrations
                   WHERE note_id='".$id."'
                   ORDER BY time";

        $objResult  = $objDatabase->Execute($query);
        $count      = $objResult->RecordCount();

        $i=0;
        if ($objResult !== false && $count != 0) {
            while(!$objResult->EOF) {
                $this->getRegData($objResult->fields['id']);

                $this->_objTpl->setVariable(array(
                    'CALENDAR_ROW'       => ($i % 2) ? $class = 'row2' : $class = 'row1',
                ));

                $this->_objTpl->parse("registrations");

                $i++;

                $objResult->moveNext();
            }
        } else {
            $this->_objTpl->setVariable(array(
                'TXT_CALENDAR_NO_REGISTRATIONS'       => $_ARRAYLANG['TXT_CALENDAR_NO_REGISTRATIONS'],
            ));

            $this->_objTpl->parse("noRegistrations");
        }

        // Variable assignement
        $this->_objTpl->setVariable(array(
            'TXT_CALENDAR_REGISTRATIONS'    => $_ARRAYLANG['TXT_CALENDAR_REGISTRATIONS'],
            'TXT_CALENDAR_REGISTRATION'     => $_ARRAYLANG['TXT_CALENDAR_REG_REGISTRATION'],
            'TXT_CALENDAR_TERMIN'           => $_ARRAYLANG['TXT_CALENDAR_TERMIN'],
            'TXT_CALENDAR_ACTION'           => $_ARRAYLANG['TXT_CALENDAR_ACTION'],
            'TXT_SUBMIT_SELECT'             => $_ARRAYLANG['TXT_SUBMIT_SELECT'],
            'TXT_SUBMIT_DELETE'             => $_ARRAYLANG['TXT_SUBMIT_DELETE'],
            'TXT_SELECT_ALL'                => $_ARRAYLANG['TXT_SELECT_ALL'],
            'TXT_DESELECT_ALL'              => $_ARRAYLANG['TXT_DESELECT_ALL'],
            'TXT_CALENDAR_DELETE_CONFIRM'   => addslashes($_ARRAYLANG['TXT_CALENDAR_DELETE_CONFIRM']),
            'TXT_CALENDAR_DATE'             => $_CORELANG['TXT_DATE'],
            'TXT_CALENDAR_NAME'             => $_CORELANG['TXT_NAME'],
            'TXT_CALENDAR_SECOND'           => $arrFields[$fieldIdSecond],
            'TXT_CALENDAR_EMAIL'            => $_ARRAYLANG['TXT_CALENDAR_MAIL'],
            'CALENDAR_NOTE_ID'              => $id,
            'TXT_CALENDAR_ACTION'           => $_ARRAYLANG['TXT_CALENDAR_ACTION'],
            'TXT_CALENDAR_REG_TYPE'         => $_ARRAYLANG['TXT_CALENDAR_FIELD_STATUS'],
            'TXT_SUBMIT_SIGN_OFF'           => $_ARRAYLANG['TXT_SUBMIT_SIGN_OFF'],
            'TXT_SUBMIT_SIGN_ON'            => $_ARRAYLANG['TXT_SUBMIT_SIGN_ON']
        ));
    }


    /**
     * Show Registration
     *
     * Shows registrations detail
     */
    function showRegistrationDetail($id)
    {
        global $objDatabase, $_ARRAYLANG, $_CORELANG , $_LANGID;

        $this->pageTitle=$_ARRAYLANG['TXT_CALENDAR_REGISTRATIONS'];

        $arrFieldsData = $this->getRegData($id);

        $i=0;

        if(!empty($arrFieldsData)){
            foreach($arrFieldsData as $fieldName => $fieldValue) {
                if($fieldName != '' || $fieldValue != ''){
                    $this->_objTpl->setVariable(array(
                        'CALENDAR_ROW'                  => ($i % 2) ? $class = 'row2' : $class = 'row1',
                        'CALENDAR_REG_FIELD_NAME'       => htmlentities($fieldName, ENT_QUOTES, CONTREXX_CHARSET),
                        'CALENDAR_REG_FIELD_VALUE'      => htmlentities($fieldValue, ENT_QUOTES, CONTREXX_CHARSET),
                    ));

                    $this->_objTpl->parse("registration");

                    $i++;
                }
            }
        }
        $this->_objTpl->setVariable(array(
            'TXT_CALENDAR_FIELD'            => $_ARRAYLANG['TXT_CALENDAR_FIELD'],
            'TXT_CALENDAR_VALUE'            => $_ARRAYLANG['TXT_CALENDAR_VALUE'],
            'TXT_CALENDAR_BACK'             => $_ARRAYLANG['TXT_CALENDAR_BACK'],
            'TXT_CALENDAR_DELETE'           => $_ARRAYLANG['TXT_CALENDAR_DELETE'],
            'TXT_CALENDAR_DELETE_CONFIRM'   => addslashes($_ARRAYLANG['TXT_CALENDAR_DELETE_CONFIRM'])
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
                $query = "DELETE FROM ".DBPREFIX."module_calendar".$this->mandateLink." WHERE id=$eventid";

                $objResultDel = $objDatabase->Execute($query);

                if ($objResultDel !== false) {
                    $this->deleteFormular($eventid);
                }
            }
        }

        if ($this->mandate == 1) {
            CSRF::header("Location: index.php?cmd=calendar".$this->mandateLink."");
        } else {
            CSRF::header("Location: index.php?cmd=calendar".$this->mandateLink."".$this->mandate);
        }
        exit;
    }


    // CASE NOTE
    function showEvent($id)
    {
        global $_ARRAYLANG, $_CORELANG;

        $this->_objTpl->loadTemplateFile('module_calendar_note_show.html');

        // get day note
        $this->getNoteData($id, 'show', 3);

        // parse remains to template
        $this->_objTpl->setVariable(array(
            'TXT_CALENDAR'                  => $_ARRAYLANG['TXT_CALENDAR'],
            'TXT_CALENDAR_PLACE'            => $_ARRAYLANG['TXT_CALENDAR_PLACE'],
            'TXT_CALENDAR_EVENT'            => $_ARRAYLANG['TXT_CALENDAR_TERMIN'],
            'TXT_CALENDAR_ORGANIZER'        => $_ARRAYLANG['TXT_CALENDAR_ORGANIZER'],
            'TXT_CALENDAR_OPTIONS'          => $_ARRAYLANG['TXT_CALENDAR_OPTIONS'],
            'TXT_CALENDAR_CAT'              => $_ARRAYLANG['TXT_CALENDAR_CAT'],
            'TXT_CALENDAR_NAME'             => $_ARRAYLANG['TXT_CALENDAR_NAME'],
            'TXT_CALENDAR_PLACE'            => $_ARRAYLANG['TXT_CALENDAR_PLACE'],
            'TXT_CALENDAR_PRIORITY'         => $_ARRAYLANG['TXT_CALENDAR_PRIORITY'],
            'TXT_CALENDAR_START'            => $_ARRAYLANG['TXT_CALENDAR_START'],
            'TXT_CALENDAR_END'              => $_ARRAYLANG['TXT_CALENDAR_END'],
            'TXT_CALENDAR_COMMENT'          => $_ARRAYLANG['TXT_CALENDAR_COMMENT'],
            'TXT_CALENDAR_LINK'             => $_ARRAYLANG['TXT_CALENDAR_INFO'],
            'TXT_CALENDAR_EVENT'            => $_ARRAYLANG['TXT_CALENDAR_TERMIN'],
            'TXT_CALENDAR_STREET_NR'        => $_ARRAYLANG['TXT_CALENDAR_STREET_NR'],
            'TXT_CALENDAR_ZIP'              => $_ARRAYLANG['TXT_CALENDAR_ZIP'],
            'TXT_CALENDAR_LINK'             => $_ARRAYLANG['TXT_CALENDAR_LINK'],
            'TXT_CALENDAR_MAP'              => $_ARRAYLANG['TXT_CALENDAR_MAP'],
            'TXT_CALENDAR_ORGANIZER'        => $_ARRAYLANG['TXT_CALENDAR_ORGANIZER'],
            'TXT_CALENDAR_MAIL'             => $_ARRAYLANG['TXT_CALENDAR_MAIL'],
            'TXT_CALENDAR_ORGANIZER_NAME'   => $_CORELANG['TXT_NAME'],
            'TXT_CALENDAR_TITLE'            => $_ARRAYLANG['TXT_CALENDAR_TITLE'],
            'TXT_CALENDAR_OPTIONS'          => $_ARRAYLANG['TXT_CALENDAR_OPTIONS'],
            'TXT_CALENDAR_THUMBNAIL'        => $_ARRAYLANG['TXT_CALENDAR_THUMBNAIL'],
            'TXT_CALENDAR_ACCESS'           => $_ARRAYLANG['TXT_CALENDAR_ACCESS'],
            'TXT_CALENDAR_ATTACHMENT'       => $_ARRAYLANG['TXT_CALENDAR_ATTACHMENT'],
            'TXT_CALENDAR_PRIORITY'         => $_ARRAYLANG['TXT_CALENDAR_PRIORITY'],
            'TXT_CALENDAR_REGISTRATIONS'    => $_ARRAYLANG['TXT_CALENDAR_REGISTRATIONS'],
            'TXT_CALENDAR_SUBSCRIBER'       => $_ARRAYLANG['TXT_CALENDAR_REGISTRATIONS_SUBSCRIBER']
        ));
    }

    // delete note
    function delNote($id)
    {
        global $objDatabase, $_ARRAYLANG, $_LANGID;

        $objResultDel = $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_calendar".$this->mandateLink." WHERE id = ".intval($id));

        if ($objResultDel !== false) {
            $this->deleteFormular($id);
            $this->strOkMessage = $_ARRAYLANG['TXT_CALENDAR_STAT_DEL']."<br />";
        }
    }



    /**
     * New Note
     *
     * Form for a new note
     */
    function modifyNote($id=null)
    {
        global $objDatabase, $_ARRAYLANG, $_LANGID, $_CORELANG, $_CONFIG;

        if(!empty($id)) {
            //edit note
            $this->pageTitle=$_ARRAYLANG['TXT_CALENDAR_TERMIN'];

            //load data
            $this->getNoteData($id, "edit", 0);

            //get editor
            $ed = get_wysiwyg_editor('inputComment');

            //show send mail again
            $this->_objTpl->parse('sendMailAgain');

            //get formular
            $this->_getFormular($id, 'backend');

             // data
            $this->_objTpl->setVariable(array(
                'CALENDAR_ID'                   => $id,
                'CALENDAR_FORM_ACTION'          => "saveEdited",
            ));

        } else {
            //new note
            $this->pageTitle=$_ARRAYLANG['TXT_CALENDAR_NEW'];

            //hide send mail again
            $this->_objTpl->hideBlock('sendMailAgain');

            // categories
            $query = "SELECT    id,
                                name,
                                lang
                          FROM  ".DBPREFIX."module_calendar".$this->mandateLink."_categories
                      ORDER BY  'pos'";

            $objResultCat = $objDatabase->Execute($query);

            if ($objResultCat !== false) {
                while(!$objResultCat->EOF) {
                    $query = "SELECT lang
                                FROM ".DBPREFIX."languages
                               WHERE id = '".$objResultCat->fields['lang']."'";
                    $objResultLang = $objDatabase->SelectLimit($query, 1);

                    $this->_objTpl->setVariable(array(
                        'CALENDAR_CAT_ID'       => $objResultCat->fields['id'],
                        'CALENDAR_CAT_LANG'     => $objResultLang->fields['lang'],
                        'CALENDAR_CAT_NAME'     => $objResultCat->fields['name']
                    ));
                    $this->_objTpl->parse("calendar_cat");
                    $objResultCat->MoveNext();
                }
            }

            $ed = get_wysiwyg_editor('inputComment');

            //get mail template
            $query          = "SELECT setvalue
                                 FROM ".DBPREFIX."module_calendar".$this->mandateLink."_settings
                                WHERE setid = '1'";

            $objResult      = $objDatabase->SelectLimit($query, 1);
            $mailTitle      = $objResult->fields['setvalue'];

            $query          = "SELECT setvalue
                                 FROM ".DBPREFIX."module_calendar".$this->mandateLink."_settings
                                WHERE setid = '2'";

            $objResult      = $objDatabase->SelectLimit($query, 1);
            $mailContent    = $objResult->fields['setvalue'];

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

            $weekdays = '';
			foreach ($arrWeekdays as $value => $name) {
				$weekdays .= '<option value="'.$value.'">'.$name.'</option>';
			}

            $count = '';
			foreach ($arrCount as $value => $name) {
				$count .= '<option value="'.$value.'">'.$name.'</option>';
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

           $this->_getFormular('', 'backend');

            $this->selectHour(12, "hour", "CALENDAR_HOUR_SELECT", "CALENDAR_HOUR");
            $this->selectMinutes(00, "minutes", "CALENDAR_MINUTES_SELECT", "CALENDAR_MINUTES");
            $this->selectHour(13, "endhour", "CALENDAR_END_HOUR_SELECT", "CALENDAR_END_HOUR");
            $this->selectMinutes(30, "endminutes", "CALENDAR_END_MINUTES_SELECT", "CALENDAR_END_MINUTES");
        }

	    // parse to template
		$this->_objTpl->setVariable(array(
			'TXT_CALENDAR_ERROR_CATEGORY' 	=> $_ARRAYLANG['TXT_CALENDAR_ERROR_CATEGORY'],
			'TXT_CALENDAR_ACTIVE'		  	=> $_ARRAYLANG['TXT_CALENDAR_ACTIVE'],
			'TXT_CALENDAR_CAT'            	=> $_ARRAYLANG['TXT_CALENDAR_CAT'],
			'TXT_CALENDAR_ERROR_NAME'     	=> $_ARRAYLANG['TXT_CALENDAR_ERROR_NAME'],
			'TXT_CALENDAR_ERROR_PLACE'    	=> $_ARRAYLANG['TXT_CALENDAR_ERROR_PLACE'],
			'TXT_CALENDAR_ERROR_DATE'     	=> $_ARRAYLANG['TXT_CALENDAR_ERROR_DATE'],
			'TXT_CALENDAR_ERROR_COMMENT'  	=> $_ARRAYLANG['TXT_CALENDAR_ERROR_COMMENT'],
			'TXT_CALENDAR_NEW'            	=> $_ARRAYLANG['TXT_CALENDAR_NEW'],
			'TXT_CALENDAR_NAME'	          	=> $_ARRAYLANG['TXT_CALENDAR_NAME'],
			'TXT_CALENDAR_PLACE'         	=> $_ARRAYLANG['TXT_CALENDAR_PLACE'],
			'TXT_CALENDAR_PRIORITY'	      	=> $_ARRAYLANG['TXT_CALENDAR_PRIORITY'],
			'TXT_CALENDAR_START'          	=> $_ARRAYLANG['TXT_CALENDAR_START'],
			'TXT_CALENDAR_END'            	=> $_ARRAYLANG['TXT_CALENDAR_END'],
			'TXT_CALENDAR_COMMENT'        	=> $_ARRAYLANG['TXT_CALENDAR_COMMENT'],
			'TXT_CALENDAR_LINK'           	=> $_ARRAYLANG['TXT_CALENDAR_INFO'],
			'TXT_CALENDAR_RESET'          	=> $_ARRAYLANG['TXT_CALENDAR_RESET'],
			'TXT_CALENDAR_SUBMIT'         	=> $_ARRAYLANG['TXT_CALENDAR_SUBMIT'],
			'TXT_CALENDAR_WHOLE_DAY'	  	=> $_ARRAYLANG['TXT_CALENDAR_WHOLE_DAY'],
			'TXT_CALENDAR_EVENT' 		  	=> $_ARRAYLANG['TXT_CALENDAR_TERMIN'],
			'TXT_CALENDAR_STREET_NR' 		=> $_ARRAYLANG['TXT_CALENDAR_STREET_NR'],
			'TXT_CALENDAR_ZIP' 		  		=> $_ARRAYLANG['TXT_CALENDAR_ZIP'],
			'TXT_CALENDAR_LINK' 		  	=> $_ARRAYLANG['TXT_CALENDAR_LINK'],
			'TXT_CALENDAR_MAP' 		  		=> $_ARRAYLANG['TXT_CALENDAR_MAP'],
			'TXT_CALENDAR_ORGANIZER' 		=> $_ARRAYLANG['TXT_CALENDAR_ORGANIZER'],
			'TXT_CALENDAR_MAIL' 		  	=> $_ARRAYLANG['TXT_CALENDAR_MAIL'],
			'TXT_CALENDAR_ORGANIZER_NAME' 	=> $_CORELANG['TXT_NAME'],
			'TXT_CALENDAR_TITLE' 			=> $_ARRAYLANG['TXT_CALENDAR_TITLE'],
			'TXT_CALENDAR_OPTIONS' 			=> $_ARRAYLANG['TXT_CALENDAR_OPTIONS'],
			'TXT_CALENDAR_THUMBNAIL' 		=> $_ARRAYLANG['TXT_CALENDAR_THUMBNAIL'],
			'TXT_CALENDAR_BROWSE' 			=> $_CORELANG['TXT_BROWSE'],
			'TXT_CALENDAR_ACCESS' 			=> $_ARRAYLANG['TXT_CALENDAR_ACCESS'],
			'TXT_CALENDAR_ACCESS_PUBLIC' 	=> $_ARRAYLANG['TXT_CALENDAR_ACCESS_PUBLIC'],
			'TXT_CALENDAR_ACCESS_COMMUNITY' => $_ARRAYLANG['TXT_CALENDAR_ACCESS_COMMUNITY'],
			'TXT_CALENDAR_SERIES_PATTERN' 	=> $_ARRAYLANG['TXT_CALENDAR_SERIES_PATTERN'],
			'TXT_CALENDAR_SERIES_PATTERN_DURANCE' 	=> $_ARRAYLANG['TXT_CALENDAR_SERIES_PATTERN_DURANCE'],
			'TXT_CALENDAR_ATTACHMENT' 		=> $_ARRAYLANG['TXT_CALENDAR_ATTACHMENT'],
			'TXT_CALENDAR_PRIORITY' 		=> $_ARRAYLANG['TXT_CALENDAR_PRIORITY'],
			'TXT_CALENDAR_PRIORITY_VERY_HEIGHT' 		=> $_ARRAYLANG['TXT_CALENDAR_PRIORITY_VERY_HEIGHT'],
			'TXT_CALENDAR_PRIORITY_HEIGHT' 	=> $_ARRAYLANG['TXT_CALENDAR_PRIORITY_HEIGHT'],
			'TXT_CALENDAR_PRIORITY_NORMAL' 	=> $_ARRAYLANG['TXT_CALENDAR_PRIORITY_NORMAL'],
			'TXT_CALENDAR_PRIORITY_LOW' 	=> $_ARRAYLANG['TXT_CALENDAR_PRIORITY_LOW'],
			'TXT_CALENDAR_PRIORITY_VERY_LOW'=> $_ARRAYLANG['TXT_CALENDAR_PRIORITY_VERY_LOW'],
			'TXT_CALENDAR_REGISTRATIONS'	=> $_ARRAYLANG['TXT_CALENDAR_REGISTRATIONS'],
			'TXT_CALENDAR_FORMULAR'			=> $_ARRAYLANG['TXT_CALENDAR_FORMULAR'],
			'TXT_CALENDAR_FIELD_TYPE'		=> $_ARRAYLANG['TXT_CALENDAR_FIELD_TYPE'],
			'TXT_CALENDAR_FIELD_NAME'		=> $_ARRAYLANG['TXT_CALENDAR_FIELD_NAME'],
			'TXT_CALENDAR_FIELD_REQUIRED'	=> $_ARRAYLANG['TXT_CALENDAR_FIELD_REQUIRED'],
			'TXT_CALENDAR_FIELD_STATUS'		=> $_ARRAYLANG['TXT_CALENDAR_FIELD_STATUS'],
			'TXT_CALENDAR_REGISTRATIONS_ACTIVATED'				=> $_ARRAYLANG['TXT_CALENDAR_REGISTRATIONS_ACTIVATED'],
			'TXT_CALENDAR_REGISTRATIONS_ADDRESSER'				=> $_ARRAYLANG['TXT_CALENDAR_REGISTRATIONS_ADDRESSER'],
			'TXT_CALENDAR_REGISTRATIONS_SELECT_GROUP'			=> $_ARRAYLANG['TXT_CALENDAR_REGISTRATIONS_SELECT_GROUP'],
			'TXT_CALENDAR_REGISTRATIONS_ADDRESSER_ALL'			=> $_ARRAYLANG['TXT_CALENDAR_REGISTRATIONS_ADDRESSER_ALL'],
			'TXT_CALENDAR_REGISTRATIONS_ADDRESSER_ALL_USER'		=> $_ARRAYLANG['TXT_CALENDAR_REGISTRATIONS_ADDRESSER_ALL_USER'],
			'TXT_CALENDAR_REGISTRATIONS_ADDRESSER_SELECT_GROUP'	=> $_ARRAYLANG['TXT_CALENDAR_REGISTRATIONS_ADDRESSER_SELECT_GROUP'],
			'TXT_CALENDAR_MAIL_TEMPLATE'	=> $_ARRAYLANG['TXT_CALENDAR_MAIL_TEMPLATE'],
			'TXT_CALENDAR_PLACEHOLDERS'		=> $_ARRAYLANG['TXT_CALENDAR_PLACEHOLDERS'],
			'TXT_CALENDAR_FIRSTNAME'		=> $_ARRAYLANG['TXT_CALENDAR_FIRSTNAME'],
			'TXT_CALENDAR_LASTNAME'			=> $_ARRAYLANG['TXT_CALENDAR_LASTNAME'],
			'TXT_CALENDAR_REG_LINK'			=> $_ARRAYLANG['TXT_CALENDAR_REG_LINK'],
			'TXT_CALENDAR_TITLE'			=> $_ARRAYLANG['TXT_CALENDAR_TITLE'],
			'TXT_CALENDAR_START_DATE'		=> $_ARRAYLANG['TXT_CALENDAR_START_DATE'],
			'TXT_CALENDAR_END_DATE'			=> $_ARRAYLANG['TXT_CALENDAR_END_DATE'],
			'TXT_CALENDAR_DATE'				=> $_ARRAYLANG['TXT_CALENDAR_DATE'],
			'TXT_CALENDAR_MAIL_CONTENT'		=> $_ARRAYLANG['TXT_CALENDAR_MAIL_CONTENT'],
			'TXT_CALENDAR_TEXT'				=> $_ARRAYLANG['TXT_CALENDAR_TEXT'],
			'TXT_CALENDAR_HOST_URL'			=> $_ARRAYLANG['TXT_CALENDAR_HOST_URL'],
			'TXT_CALENDAR_REGISTRATIONS_SUBSCRIBER'			=> $_ARRAYLANG['TXT_CALENDAR_REGISTRATIONS_SUBSCRIBER'],
			'TXT_CALENDAR_REGISTRATIONS_SUBSCRIBER_INFO'	=> $_ARRAYLANG['TXT_CALENDAR_REGISTRATIONS_SUBSCRIBER_INFO'],
			'TXT_CALENDAR_SEND_MAIL_AGAIN'	=> $_ARRAYLANG['TXT_CALENDAR_SEND_MAIL_AGAIN'],
			'TXT_CALENDAR_REGISTRATIONS_ADDRESSER_INFO'	=> $_ARRAYLANG['TXT_CALENDAR_REGISTRATIONS_ADDRESSER_INFO'],
			'TXT_CALENDAR_NOTIFICATION' 				=> $_ARRAYLANG['TXT_CALENDAR_MAIL_NOTIFICATION'],
			'TXT_CALENDAR_NOTIFICATION_ACTIVATE' 		=> $_ARRAYLANG['TXT_CALENDAR_NOTIFICATION_ACTIVATE'],
			'TXT_CALENDAR_NOTIFICATION_ADDRESS' 		=> $_ARRAYLANG['TXT_CALENDAR_NOTIFICATION_ADDRESS'],
			'TXT_CALENDAR_NOTIFICATION_ADDRESS_INFO' 	=> $_ARRAYLANG['TXT_CALENDAR_NOTIFICATION_ADDRESS_INFO'],

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

            'TXT_CALENDAR_REGISTRATION_UNCHECKED_WARNING' => $_ARRAYLANG['TXT_CALENDAR_REGISTRATION_UNCHECKED_WARNING']
		));
	}


    /**
     * Write Note
     *
     * Saves a new note
     */
    function writeNote()
    {
        global $objDatabase, $_LANGID, $_ARRAYLANG;

        $return = 0;

        //options
        $catid          = intval($_POST['inputCategory']);
        $priority       = intval($_POST['inputPriority']);
        $access         = intval($_POST['inputAccess']);
        $id             = intval($_POST['inputEventId']);
        $active         = intval($_POST['inputActive']);

        //event
        $title          = contrexx_addslashes(contrexx_strip_tags($_POST['inputTitle']));
        $link           = contrexx_addslashes(contrexx_strip_tags($_POST['inputEventLink']));
        $pic            = contrexx_addslashes(contrexx_strip_tags($_POST['inputEventImage']));
        $attach         = contrexx_addslashes(contrexx_strip_tags($_POST['inputEventAttachment']));
        $comment        = contrexx_addslashes($_POST['inputComment']);

        //start 'n' end
        $dateparts      = split("-", $_POST['inputStartDate']);
        $startdate      = mktime(intval($_POST['inputHour']), intval($_POST['inputMinutes']),00, $dateparts[1], $dateparts[2], $dateparts[0]);

        $dateparts      = split("-", $_POST['inputEndDate']);
        $enddate        = mktime(intval($_POST['inputEndHour']), intval($_POST['inputEndMinutes']),00, $dateparts[1], $dateparts[2], $dateparts[0]);

        //place
        $place          = contrexx_addslashes(contrexx_strip_tags($_POST['inputPlace']));
        $placeStreet    = contrexx_addslashes(contrexx_strip_tags($_POST['inputPlaceStreetNr']));
        $placeZip       = contrexx_addslashes(contrexx_strip_tags($_POST['inputPlaceZip']));
        $placeCity      = contrexx_addslashes(contrexx_strip_tags($_POST['inputPlaceCity']));
        $placeLink      = contrexx_addslashes(contrexx_strip_tags($_POST['inputPlaceLink']));
        $placeMap       = contrexx_addslashes(contrexx_strip_tags($_POST['inputPlaceMap']));

        //organizer
        $organizer      = contrexx_addslashes(contrexx_strip_tags($_POST['inputOrganizer']));
        $organizerStreet= contrexx_addslashes(contrexx_strip_tags($_POST['inputOrganizerStreetNr']));
        $organizerZip   = contrexx_addslashes(contrexx_strip_tags($_POST['inputOrganizerZip']));
        $organizerPlace = contrexx_addslashes(contrexx_strip_tags($_POST['inputOrganizerPlace']));
        $organizerLink  = contrexx_addslashes(contrexx_strip_tags($_POST['inputOrganizerLink']));
        $organizerMail  = contrexx_addslashes(contrexx_strip_tags($_POST['inputOrganizerMail']));

        //registrations
        $registration                   = intval($_POST['inputRegistrations']);
        $registrationAdresser           = intval($_POST['inputRegistrationsAddresser']);
        $registrationGroups             = $_POST['selectedGroups'];
        $registrationArrFieldIds        = $_POST['arrFieldIds'];
        $registrationArrFieldStatus     = $_POST['arrFieldStatus'];
        $registrationArrFieldOrder      = $_POST['arrFieldOrder'];
        $registrationArrFieldName       = $_POST['arrFieldName'];
        $registrationArrFieldType       = $_POST['arrFieldType'];
        $registrationArrFieldRequired   = $_POST['arrFieldRequired'];
        $registrationSubscriber         = intval($_POST['inputRegistrationSubscriber']);

        //mail
        $mailTitle      = contrexx_addslashes(contrexx_strip_tags($_POST['registrationMailTitle']));
        $mailContent    = contrexx_addslashes(contrexx_strip_tags($_POST['registrationMailContent']));
        $mailSendAgain  = !empty($_POST['inputSendMailAgain']);

        //notification
        $notification   = intval($_POST['inputNotification']);

		//series pattern
		$seriesStatus 				= intval(!empty($_POST['inputSeriesStatus']));
		$seriesType 				= intval($_POST['inputSeriesType']);
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
					$seriesPatternType			= intval($_POST['inputSeriesDaily']);
					if($seriesPatternType == 1) {
						$seriesPatternWeekday	= 0;
						$seriesPatternDay		= intval($_POST['inputSeriesDailyDays']);;
					} else {
						$seriesPatternWeekday	= "1111100";
						$seriesPatternDay		= 0;
					}

					$seriesPatternWeek			= 0;
					$seriesPatternMonth			= 0;
					$seriesPatternCount			= 0;

					$seriesPatternDouranceType	= intval($_POST['inputSeriesDouranceType']);
					$dateparts 					= split("-", $startdate);
					switch($seriesPatternDouranceType) {
						case 1:
							$seriesPatternEnd	= 0;
						break;
						case 2:
							$seriesPatternEnd	= intval($_POST['inputSeriesDouranceNotes']);
						break;
						case 3:
							$dateparts 			= split("-", $_POST['inputRepeatDouranceEnd']);
							$seriesPatternEnd	= mktime(00, 00,00, $dateparts[1], $dateparts[2], $dateparts[0]);
						break;
					}
				}
			break;
			case 2;
				if ($seriesStatus == 1) {
					$seriesPatternWeek			= intval($_POST['inputSeriesWeeklyWeeks']);

					for($i=1; $i <= 7; $i++) {
						if (isset($_POST['inputSeriesWeeklyDays'][$i])) {
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

					$seriesPatternDouranceType	= intval($_POST['inputSeriesDouranceType']);
					$dateparts 					= split("-",$startdate);
					switch($seriesPatternDouranceType) {
						case 1:
							$seriesPatternEnd	= 0;
						break;
						case 2:
							$seriesPatternEnd	= intval($_POST['inputSeriesDouranceNotes']);
						break;
						case 3:
							$dateparts 			= split("-", $_POST['inputRepeatDouranceEnd']);
							$seriesPatternEnd	= mktime(00, 00,00, $dateparts[1], $dateparts[2], $dateparts[0]);
						break;
					}
				}
			break;
			case 3;
				if ($seriesStatus == 1) {
					$seriesPatternType			= intval($_POST['inputSeriesMonthly']);
					if($seriesPatternType == 1) {
						$seriesPatternMonth		= intval($_POST['inputSeriesMonthlyMonth_1']);
						$seriesPatternDay		= intval($_POST['inputSeriesMonthlyDay']);
						$seriesPatternWeekday	= 0;
					} else {
						$seriesPatternCount		= intval($_POST['inputSeriesMonthlyDayCount']);
						$seriesPatternMonth		= intval($_POST['inputSeriesMonthlyMonth_2']);
                        if ($seriesPatternMonth < 1) {
                            // the increment must be at least once a month, otherwise we will end up in a endless loop in the presence
                            $seriesPatternMonth = 1;
                        }
						$seriesPatternWeekday	= $_POST['inputSeriesMonthlyWeekday'];
						$seriesPatternDay		= 0;
					}

					$seriesPatternWeek			= 0;

					$seriesPatternDouranceType	= intval($_POST['inputSeriesDouranceType']);
					$dateparts 					= split("-", $startdate);
					switch($seriesPatternDouranceType) {
						case 1:
							$seriesPatternEnd	= 0;
						break;
						case 2:
							$seriesPatternEnd	= intval($_POST['inputSeriesDouranceNotes']);
						break;
						case 3:
							$dateparts 			= split("-", $_POST['inputRepeatDouranceEnd']);
							$seriesPatternEnd	= mktime(00, 00,00, $dateparts[1], $dateparts[2], $dateparts[0]);
						break;
					}
				}
			break;
		}

		if ($notification == 1) {
			$notificationAddress = contrexx_addslashes(contrexx_strip_tags($_POST['inputNotificationAddress']));
		}

        if (!empty($link)) {
            if (!preg_match('%^(?:ftp|http|https):\/\/%', $link)) {
                $link = "http://".$link;
            }
        }

        if (!empty($placeLink)) {
            if (!preg_match('%^(?:ftp|http|https):\/\/%', $placeLink)) {
                $placeLink = "http://".$placeLink;
            }
        }

        if (!empty($organizerLink)) {
            if (!preg_match('%^(?:ftp|http|https):\/\/%', $organizerLink)) {
                $organizerLink = "http://".$organizerLink;
            }
        }

        $groups                 = "";
        if($registration == 1){
            switch($registrationAdresser){
                case 0:
                    $all_groups     = 0;
                    $public         = 1;
                    break;
                case 1:
                    $all_groups     = 1;
                    $public         = 0;
                    break;
                case 2:
                    $all_groups     = 0;
                    $public         = 0;

                    foreach ($registrationGroups as $groupId) {
                        $groups .= $groupId.";";
                    }

                    break;
            }
        } else {
            $registration           = 0;
            $all_groups             = 0;
            $public                 = 0;
            $registrationSubscriber = 0;
        }

        if(!empty($id)) {
            $query = "SELECT id
                        FROM ".DBPREFIX."module_calendar".$this->mandateLink."
                       WHERE id = '".addslashes($id)."'";

            $objResult = $objDatabase->Execute($query);

	    	if ($objDatabase->Affected_Rows() > 0) {
                $query = SQL::update('module_calendar'.$this->mandateLink, array(
                    'active' => $active,
                    'catid' => $catid,
                    'startdate' => array('val' => $startdate, 'omitEmpty' => true),
                    'enddate' => array('val' => $enddate, 'omitEmpty' => true),
                    'priority' => array('val' => $priority, 'omitEmpty' => true),
                    'access' => array('val' => $access, 'omitEmpty' => true),
                    'name' => $title,
                    'comment' => $comment,
                    'link' => array('val' => $link, 'omitEmpty' => true),
                    'pic' => $pic,
                    'attachment' => $attach,
                    'placeName' => $place,
                    'placeStreet' => $placeStreet,
                    'placeZip' => $placeZip,
                    'placeCity' => $placeCity,
                    'placeLink' => $placeLink,
                    'placeMap' => $placeMap,
                    'organizerName' => $organizer,
                    'organizerStreet' => $organizerStreet,
                    'organizerZip' => $organizerZip,
                    'organizerPlace' => $organizerPlace,
                    'organizerMail' => $organizerMail,
                    'organizerLink' => $organizerLink,
                    'registration' => array('val' => $registration, 'omitEmpty' => true),
                    'groups' => $groups,
                    'all_groups' => array('val' => $all_groups, 'omitEmpty' => true),
                    'public' => array('val' => $public, 'omitEmpty' => true),
                    'mailTitle' => array('val' => $mailTitle, 'omitEmpty' => true),
                    'mailContent' => $mailContent,
                    'num' => $registrationSubscriber,
                    'notification' => array('val' => $notification, 'omitEmpty' => true),
                    'notification_address' => $notificationAddress,
                    'series_status' => $seriesStatus,
                    'series_type' => $seriesType,
                    'series_pattern_count' => $seriesPatternCount,
                    'series_pattern_weekday' => $seriesPatternWeekday,
                    'series_pattern_day' => $seriesPatternDay,
                    'series_pattern_week' => $seriesPatternWeek,
                    'series_pattern_month' => $seriesPatternMonth,
                    'series_pattern_type' => $seriesPatternType,
                    'series_pattern_dourance_type' => $seriesPatternDouranceType,
                    'series_pattern_end' => $seriesPatternEnd,
                ))." WHERE id = $id";

				$objResult = $objDatabase->Execute($query);

                if ($objResult !== false) {
                    if ($registration == 1) {
                        $query = "DELETE FROM ".DBPREFIX."module_calendar".$this->mandateLink."_form_fields WHERE note_id='".$id."'";
                        $objResultFields = $objDatabase->Execute($query);

                        if ($objResultFields !== false) {
                            //input fields
                            foreach (array_keys($registrationArrFieldStatus) as $fieldKey) {
                                $fieldId        = intval($registrationArrFieldIds[$fieldKey]);
                                $fieldName      = $registrationArrFieldName[$fieldKey];
                                $fieldType      = empty($registrationArrFieldType[$fieldKey]) || $registrationArrFieldType[$fieldKey] == 0 ? 1 : $registrationArrFieldType[$fieldKey];
                                $fieldRequired  = intval(!empty($registrationArrFieldRequired[$fieldKey]));
                                $fieldOrder     = $registrationArrFieldOrder[$fieldKey];

                                $query = "INSERT INTO ".DBPREFIX."module_calendar".$this->mandateLink."_form_fields (`id`,
                                                                                               `note_id`,
                                                                                               `name`,
                                                                                               `type`,
                                                                                               `required`,
                                                                                               `order`,
                                                                                               `key`)
                                                                                       VALUES ('$fieldId',
                                                                                               '$id',
                                                                                               '$fieldName',
                                                                                               '$fieldType',
                                                                                               '$fieldRequired',
                                                                                               '$fieldOrder',
                                                                                               '$fieldKey')";
                                $objResultFields = $objDatabase->Execute($query);
                            }
                        }

                        if ($registrationAdresser != 0 && $mailSendAgain == 1) {
                            $this->_sendRegistration($id);
                        }
                    }

                    $this->strOkMessage = $_ARRAYLANG['TXT_CALENDAR_STAT_EDITED']."<br />";
                    $return = $id;
                } else {
                    $this->strErrMessage = $_ARRAYLANG['TXT_CALENDAR_STAT_EDITED_ERROR']."<br />";
                }
            } else {
                $this->strErrMessage = $_ARRAYLANG['TXT_CALENDAR_STAT_ERROR_EXISTING']."<br />";
            }
        } else {

            $md5 = md5(uniqid(rand()));
            $key = substr($md5,0,10);

            $query = SQL::insert('module_calendar'.$this->mandateLink, array(
                'active' => $active,
                'catid' => $catid,
                'startdate' => array('val' => $startdate, 'omitEmpty' => true),
                'enddate' => array('val' => $enddate, 'omitEmpty' => true),
                'priority' => array('val' => $priority, 'omitEmpty' => true),
                'access' => array('val' => $access, 'omitEmpty' => true),
                'name' => $title,
                'comment' => $comment,
                'link' => array('val' => $link, 'omitEmpty' => true),
                'pic' => $pic,
                'attachment' => $attach,
                'placeName' => $place,
                'placeStreet' => $placeStreet,
                'placeZip' => $placeZip,
                'placeCity' => $placeCity,
                'placeLink' => $placeLink,
                'placeMap' => $placeMap,
                'organizerName' => $organizer,
                'organizerStreet' => $organizerStreet,
                'organizerZip' => $organizerZip,
                'organizerPlace' => $organizerPlace,
                'organizerMail' => $organizerMail,
                'organizerLink' => $organizerLink,
                'registration' => array('val' => $registration, 'omitEmpty' => true),
                'groups' => $groups,
                'all_groups' => array('val' => $all_groups, 'omitEmpty' => true),
                'public' => array('val' => $public, 'omitEmpty' => true),
                'mailTitle' => array('val' => $mailTitle, 'omitEmpty' => true),
                'mailContent' => $mailContent,
                'key' => $key,
                'num' => $registrationSubscriber,
                'notification' => array('val' => $notification, 'omitEmpty' => true),
                'notification_address' => $notificationAddress,
                'series_status' => $seriesStatus,
                'series_type' => $seriesType,
                'series_pattern_count' => $seriesPatternCount,
                'series_pattern_weekday' => $seriesPatternWeekday,
                'series_pattern_day' => $seriesPatternDay,
                'series_pattern_week' => $seriesPatternWeek,
                'series_pattern_month' => $seriesPatternMonth,
                'series_pattern_type' => $seriesPatternType,
                'series_pattern_dourance_type' => $seriesPatternDouranceType,
                'series_pattern_end' => $seriesPatternEnd,
                'series_pattern_exceptions' => '', //provide a default value, no null allowed
            ));

			$objResult = $objDatabase->Execute($query);

            if ($objResult !== false) {

                $noteId = $objDatabase->Insert_ID();

                if ($registration == 1) {
                    //input fields
                    foreach (array_keys($registrationArrFieldStatus) as $fieldKey) {

                        $fieldName      = $registrationArrFieldName[$fieldKey];
                        $fieldType      = empty($registrationArrFieldType[$fieldKey]) || $registrationArrFieldType[$fieldKey] == 0 ? 1 : $registrationArrFieldType[$fieldKey];
                        $fieldRequired  = $registrationArrFieldRequired[$fieldKey];
                        $fieldOrder     = $registrationArrFieldOrder[$fieldKey];

                        $query = "INSERT INTO ".DBPREFIX."module_calendar".$this->mandateLink."_form_fields (`note_id`,
                                                                                       `name`,
                                                                                       `type`,
                                                                                       `required`,
                                                                                       `order`,
                                                                                       `key`)
                                                                               VALUES ('$noteId',
                                                                                       '$fieldName',
                                                                                       '$fieldType',
                                                                                       '$fieldRequired',
                                                                                       '$fieldOrder',
                                                                                       '$fieldKey')";
                        $objResultFields = $objDatabase->Execute($query);

                    }

                    if ($registrationAdresser != 0) {
                        //$this->_sendRegistration($noteId);
                    }
                }

                $this->strOkMessage = $_ARRAYLANG['TXT_CALENDAR_STAT_ADDED']."<br />";

                $return = $noteId;
            } else {
                $this->strErrMessage = $_ARRAYLANG['TXT_CALENDAR_STAT_ADDED_ERROR']."<br />";
            }
        }

        return $return;
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
                              FROM ".DBPREFIX."module_calendar".$this->mandateLink."_categories
                      WHERE BINARY name = '".$_POST['name']."'";

                $objResult = $objDatabase->Execute($query);

                if ($objDatabase->Affected_Rows() == 0){
                    $query = "INSERT INTO ".DBPREFIX."module_calendar".$this->mandateLink."_categories
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
                                  FROM ".DBPREFIX."module_calendar".$this->mandateLink."
                                 WHERE catid = '".intval($id)."'";

                    $objResult = $objDatabase->Execute($query);

                    if ($objDatabase->Affected_Rows() == 0){
                        $query = "DELETE FROM ".DBPREFIX."module_calendar".$this->mandateLink."_categories
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

                $query = "SELECT id FROM ".DBPREFIX."module_calendar".$this->mandateLink."_categories";

                $objResult = $objDatabase->Execute($query);

                if ($objDatabase->Affected_Rows() == 0){
                    $query = "DELETE FROM ".DBPREFIX."module_calendar".$this->mandateLink."_categories";
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
                    $query = "UPDATE ".DBPREFIX."module_calendar".$this->mandateLink."_categories
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
                $query = "UPDATE ".DBPREFIX."module_calendar".$this->mandateLink."_categories
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
            'TXT_CALENDAR_DELETE_CONFIRM'      => addslashes($_ARRAYLANG['TXT_CALENDAR_DELETE_CONFIRM']),
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
        $query = " SELECT * FROM ".DBPREFIX."module_calendar".$this->mandateLink."_categories
                   ORDER BY pos, id DESC";
        $objResult = $objDatabase->Execute($query);
        $i = 0;

        if ($objResult !== false) {
            while(!$objResult->EOF) {
                ($i % 2)                ? $class  = 'row1'  : $class  = 'row2';
                ($objResult->fields['status'] == 1) ? $status = 'green' : $status = 'red';

                $query = "SELECT id
                               FROM ".DBPREFIX."module_calendar".$this->mandateLink."
                              WHERE catid = '".intval($objResult->fields['id'])."'";
                $objResult2 = $objDatabase->Execute($query);
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
                              FROM ".DBPREFIX."module_calendar".$this->mandateLink."_categories
                      WHERE BINARY name = '".$_POST['name']."'
                               AND id <> '".intval($_POST['id'])."'";

                $objResult = $objDatabase->Execute($query);

                if ($objDatabase->affected_rows() == 0){
                    $query = "UPDATE ".DBPREFIX."module_calendar".$this->mandateLink."_categories
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
                          FROM ".DBPREFIX."module_calendar".$this->mandateLink."_categories
                         WHERE id = '".intval($_GET['id'])."'";

            $objResult = $objDatabase->SelectLimit($query, 1);

            if ($objDatabase->Affected_Rows() == 0) {
                CSRF::header("Location: index.php?cmd=calendar".$this->mandateLink."&act=cat");

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

            if ($objResult2 !== false) {
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
            CSRF::header("Location: index.php?cmd=calendar".$this->mandateLink."&act=cat");

            die;
        }
    }


    // do search
    function search()
    {
        global $objDatabase, $_ARRAYLANG, $_LANGID;

        $this->_objTpl->loadTemplateFile('module_calendar_overview.html');
        $this->_objTpl->setVariable("CONTENT", $this->pageContent);

// TODO: $search is never used
//        $search = $_POST['inputName'];
//        if ($_POST['inputName'] == '') {
//            $search = "[a-z0-9]";
//        }

        $query = "
            SELECT * FROM ".DBPREFIX."module_calendar".$this->mandateLink."
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
                    'CALENDAR_ACTIVE_ICON'      => $status,
                    'CALENDAR_EVENT_ID'         => $objResult->fields['id'],
                    'CALENDAR_EVENT_STARTDATE'  => date(ASCMS_DATE_FORMAT, $objResult->fields['startdate']),
                    'CALENDAR_EVENT_ENDDATE'    => date(ASCMS_DATE_FORMAT, $objResult->fields['enddate']),
                    'CALENDAR_EVENT_TITLE'      => $objResult->fields['name'],
                    'CALENDAR_EVENT_CAT'        => $cats[$objResult->fields['catid']]
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

        if ($_CONFIG['calendar'.$this->mandateLink.'headlines']) {
            $headlines_checked = "checked=\"checked\"";
        } else {
            $headlines_checked = "";
        }

        // makes the category list
        $query = "SELECT id,name,lang FROM ".DBPREFIX."module_calendar".$this->mandateLink."_categories
                   ORDER BY pos";
        $objResult = $objDatabase->Execute($query);

        while (!$objResult->EOF) {
            if ($objResult->fields['id'] == $_CONFIG['calendar'.$this->mandateLink.'headlinescat']) {
                $selected = " selected=\"selected\"";
            } else {
                $selected = "";
            }

            $calendar_categories .= "<option value=\"".$objResult->fields['id']."\"$selected>".$objResult->fields['name']."</option>";
            $objResult->MoveNext();
        }

        $query      = "SELECT id,name,lang FROM ".DBPREFIX."module_calendar".$this->mandateLink."_categories
                          ORDER BY pos";
        $objResult  = $objDatabase->Execute($query);

        //get mail templates
        $query          = "SELECT setvalue
                             FROM ".DBPREFIX."module_calendar".$this->mandateLink."_settings
                            WHERE setid = '1'";

        $objResult      = $objDatabase->SelectLimit($query, 1);
        $mailTitle      = $objResult->fields['setvalue'];

        $query          = "SELECT setvalue
                             FROM ".DBPREFIX."module_calendar".$this->mandateLink."_settings
                            WHERE setid = '2'";

        $objResult      = $objDatabase->SelectLimit($query, 1);
        $mailContent    = $objResult->fields['setvalue'];

        $query          = "SELECT setvalue
                             FROM ".DBPREFIX."module_calendar".$this->mandateLink."_settings
                            WHERE setid = '3'";

        $objResult      = $objDatabase->SelectLimit($query, 1);
        $mailConTitle   = $objResult->fields['setvalue'];

        $query          = "SELECT setvalue
                             FROM ".DBPREFIX."module_calendar".$this->mandateLink."_settings
                            WHERE setid = '4'";

        $objResult      = $objDatabase->SelectLimit($query, 1);
        $mailConContent = $objResult->fields['setvalue'];

        $query          = "SELECT setvalue
                             FROM ".DBPREFIX."module_calendar".$this->mandateLink."_settings
                            WHERE setid = '5'";

        $objResult      = $objDatabase->SelectLimit($query, 1);
        $mailNotTitle       = $objResult->fields['setvalue'];

        $query          = "SELECT setvalue
                             FROM ".DBPREFIX."module_calendar".$this->mandateLink."_settings
                            WHERE setid = '6'";

        $objResult      = $objDatabase->SelectLimit($query, 1);
        $mailNotContent     = $objResult->fields['setvalue'];

        //Parse
        $this->_objTpl->setVariable(array(
            'TXT_CALENDAR_MENU_SETTINGS'        => $_ARRAYLANG['TXT_CALENDAR_MENU_SETTINGS'],
            'TXT_CALENDAR_STD_CAT'              => $_ARRAYLANG['TXT_CALENDAR_STD_CAT'],
            'TXT_CALENDAR_SET_HEADLINES'        => $_ARRAYLANG['TXT_CALENDAR_SET_HEADLINES'],
            'CALENDAR_HEADLINES_CHECKED'        => $headlines_checked,
            'TXT_CALENDAR_SET_HEADLINESCOUNT'   => $_ARRAYLANG['TXT_CALENDAR_SET_HEADLINESCOUNT'],
            'CALENDAR_HEADLINESCOUNT'           => $_CONFIG['calendar'.$this->mandateLink.'headlinescount'],
            'TXT_SUBMIT'                        => $_ARRAYLANG['TXT_SUBMIT'],
            'TXT_CALENDAR_ALL_CAT'              => $_ARRAYLANG['TXT_CALENDAR_ALL_CAT'],
            'TXT_CALENDAR_SET_HEADLINESCAT'     => $_ARRAYLANG['TXT_CALENDAR_SET_HEADLINESCAT'],
            'CALENDAR_CATEGORIES'               => $calendar_categories,
            'TXT_CALENDAR_SET_STDCOUNT'         => $_ARRAYLANG['TXT_CALENDAR_SET_STDCOUNT'],
            'CALENDAR_STANDARDNUMBER'           => $_CONFIG['calendar'.$this->mandateLink.'defaultcount'],
            'TXT_CALENDAR_MAIL_TEMPLATE'        => $_ARRAYLANG['TXT_CALENDAR_MAIL_TEMPLATE'],
            'TXT_CALENDAR_TITLE'                => $_ARRAYLANG['TXT_CALENDAR_TITLE'],
            'TXT_CALENDAR_TEXT'                 => $_ARRAYLANG['TXT_CALENDAR_TEXT'],
            'CALENDAR_MAIL_TITLE'               => $mailTitle,
            'CALENDAR_MAIL_CONTENT'             => $mailContent,
            'CALENDAR_NOT_MAIL_TITLE'           => $mailNotTitle,
            'CALENDAR_NOT_MAIL_CONTENT'         => $mailNotContent,
            'CALENDAR_CON_MAIL_TITLE'           => $mailConTitle,
            'CALENDAR_CON_MAIL_CONTENT'         => $mailConContent,
            'FE_ENTRIES_OPTIONS'                => $this->getFeEntriesOptionList($this->settings->get('fe_entries_ability')),
            'TXT_CALENDAR_PLACEHOLDERS'         => $_ARRAYLANG['TXT_CALENDAR_PLACEHOLDERS'],
            'TXT_CALENDAR_EMAIL'                => $_ARRAYLANG['TXT_CALENDAR_MAIL'],
            'TXT_CALENDAR_FIRSTNAME'            => $_ARRAYLANG['TXT_CALENDAR_FIRSTNAME'],
            'TXT_CALENDAR_LASTNAME'             => $_ARRAYLANG['TXT_CALENDAR_LASTNAME'],
            'TXT_CALENDAR_REG_LINK'             => $_ARRAYLANG['TXT_CALENDAR_REG_LINK'],
            'TXT_CALENDAR_TITLE'                => $_ARRAYLANG['TXT_CALENDAR_TITLE'],
            'TXT_CALENDAR_START_DATE'           => $_ARRAYLANG['TXT_CALENDAR_START_DATE'],
            'TXT_CALENDAR_END_DATE'             => $_ARRAYLANG['TXT_CALENDAR_END_DATE'],
            'TXT_CALENDAR_DATE'                 => $_ARRAYLANG['TXT_CALENDAR_DATE'],
            'TXT_CALENDAR_MAIL_CONTENT'         => $_ARRAYLANG['TXT_CALENDAR_MAIL_CONTENT'],
            'TXT_CALENDAR_TEXT'                 => $_ARRAYLANG['TXT_CALENDAR_TEXT'],
            'TXT_CALENDAR_HOST_URL'             => $_ARRAYLANG['TXT_CALENDAR_HOST_URL'],
            'TXT_CALENDAR_REGISTRATIONS_SUBSCRIBER'         => $_ARRAYLANG['TXT_CALENDAR_REGISTRATIONS_SUBSCRIBER'],
            'TXT_CALENDAR_REGISTRATION_TYPE'                => $_ARRAYLANG['TXT_CALENDAR_REGISTRATION_TYPE'],
            'TXT_CALENDAR_MAIL_PUBLICATION'                 => $_ARRAYLANG['TXT_CALENDAR_MAIL_PUBLICATION'],
            'TXT_CALENDAR_MAIL_NOTIFICATION'                => $_ARRAYLANG['TXT_CALENDAR_MAIL_NOTIFICATION'],
            'TXT_CALENDAR_MAIL_CONFIRMATION'                => $_ARRAYLANG['TXT_CALENDAR_MAIL_CONFIRMATION'],
            'TXT_CALENDAR_FRONTEND_SETTINGS'    => $_ARRAYLANG['TXT_CALENDAR_FRONTEND_SETTINGS'],
            'TXT_CALENDAR_SETTINGS_FE_ENTRIES_ENABLED' => $_ARRAYLANG['TXT_CALENDAR_SETTINGS_FE_ENTRIES_ENABLED']
        ));

        $this->_objTpl->setGlobalVariable('TXT_CALENDAR_STD_CAT_NONE', $_ARRAYLANG['TXT_CALENDAR_STD_CAT_NONE']);

//      set standard
        $query      = "SELECT stdCat
                         FROM ".DBPREFIX."module_calendar".$this->mandateLink."_style
                        WHERE id = '2'";

        $objResult  = $objDatabase->SelectLimit($query, 1);

        $array1     = explode(' ', $objResult->fields['stdCat']);
        $cats       = '';

        foreach($array1 as $out){
            $array2           = explode('>', $out);
            $cats[$array2[0]] = $array2[1];
        }

        // get active languages
        $lang   = array(0=>"foo"); // this is needed because otherwise the first index would be 0, that's false and doesn't work with the if query below
        $query  = "SELECT id FROM ".DBPREFIX."languages
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
                                   FROM ".DBPREFIX."module_calendar".$this->mandateLink."_categories
                                  WHERE lang = '".$objResult->fields['id']."'
                                    AND status = '1'
                               ORDER BY pos";
                    $objResult2 = $objDatabase->Execute($query);

                    $cal_option = '';
                    while(!$objResult2->EOF){
                        $select = '';
                        if ($cats[$objResult->fields['id']] == $objResult2->fields['id']){
                            $select = ' selected="selected"';
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
            UPDATE ".DBPREFIX."module_calendar".$this->mandateLink."_style
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


        (isset($_POST['fe_entries_ability'])) ? $this->settings->set('fe_entries_ability', $_POST['fe_entries_ability']) : null;

        $query = "SELECT setvalue
                  FROM ".DBPREFIX."settings
                  WHERE setname = 'calendar".$this->mandateLink."headlines'";
        $objRs = $objDatabase->Execute($query);
        if ($objRs->RecordCount() == 0) {
            $query = "INSERT INTO ".DBPREFIX."settings
                      (setname, setvalue)
                      VALUES
                      ('calendar".$this->mandateLink."headlines',
                       '".$val."')";
        } else {
            $query = "UPDATE ".DBPREFIX."settings
                    SET setvalue = '$val'
                    WHERE setname = 'calendar".$this->mandateLink."headlines'";
        }
        $objDatabase->Execute($query);

        $query = "SELECT setvalue
                  FROM ".DBPREFIX."settings
                  WHERE setname = 'calendar".$this->mandateLink."headlinescount'";
        $objRs = $objDatabase->Execute($query);
        if ($objRs->RecordCount() == 0) {
            $query = "INSERT INTO ".DBPREFIX."settings
                      (setname, setvalue)
                      VALUES
                      ('calendar".$this->mandateLink."headlinescount',
                       '".intval($_POST['headlinescount'])."')";
        } else {
            $query = "UPDATE ".DBPREFIX."settings
                    SET setvalue = '".intval($_POST['headlinescount'])."'
                    WHERE setname = 'calendar".$this->mandateLink."headlinescount'";
        }
        $objDatabase->Execute($query);

        $query = "SELECT setvalue
                  FROM ".DBPREFIX."settings
                  WHERE setname = 'calendar".$this->mandateLink."headlinescat'";
        $objRs = $objDatabase->Execute($query);
        if ($objRs->RecordCount() == 0) {
            $query = "INSERT INTO ".DBPREFIX."settings
                      (setname, setvalue)
                      VALUES
                      ('calendar".$this->mandateLink."headlinescat',
                       '".intval($_POST['headlinescat'])."')";
        } else {
            $query = "UPDATE ".DBPREFIX."settings
                    SET setvalue = ".intval($_POST['headlinescat'])."
                    WHERE setname = 'calendar".$this->mandateLink."headlinescat'";
        }
        $objDatabase->Execute($query);

        $query = "SELECT setvalue
                  FROM ".DBPREFIX."settings
                  WHERE setname = 'calendar".$this->mandateLink."defaultcount'";
        $objRs = $objDatabase->Execute($query);
        if ($objRs->RecordCount() == 0) {
            $query = "INSERT INTO ".DBPREFIX."settings
                      (setname, setvalue)
                      VALUES
                      ('calendar".$this->mandateLink."defaultcount',
                       '".intval($_POST['defaultlistcount'])."')";
        } else {
            $query = "UPDATE ".DBPREFIX."settings
                    SET setvalue = ".intval($_POST['defaultlistcount'])."
                    WHERE setname = 'calendar".$this->mandateLink."defaultcount'";
        }
        $objDatabase->Execute($query);

        $query = "UPDATE ".DBPREFIX."module_calendar".$this->mandateLink."_settings
                SET setvalue = '".contrexx_addslashes($_POST['registrationMailTitle'])."'
                WHERE setid = '1'";
        $objDatabase->Execute($query);

        $query = "UPDATE ".DBPREFIX."module_calendar".$this->mandateLink."_settings
                SET setvalue = '".contrexx_addslashes($_POST['registrationMailContent'])."'
                WHERE setid = '2'";
        $objDatabase->Execute($query);

        $query = "UPDATE ".DBPREFIX."module_calendar".$this->mandateLink."_settings
                SET setvalue = '".contrexx_addslashes($_POST['registrationConMailTitle'])."'
                WHERE setid = '3'";
        $objDatabase->Execute($query);

        $query = "UPDATE ".DBPREFIX."module_calendar".$this->mandateLink."_settings
                SET setvalue = '".contrexx_addslashes($_POST['registrationConMailContent'])."'
                WHERE setid = '4'";
        $objDatabase->Execute($query);

        $query = "UPDATE ".DBPREFIX."module_calendar".$this->mandateLink."_settings
                SET setvalue = '".contrexx_addslashes($_POST['registrationNotMailTitle'])."'
                WHERE setid = '5'";
        $objDatabase->Execute($query);

        $query = "UPDATE ".DBPREFIX."module_calendar".$this->mandateLink."_settings
                SET setvalue = '".contrexx_addslashes($_POST['registrationNotMailContent'])."'
                WHERE setid = '6'";
        $objDatabase->Execute($query);

        $objSettings = new settingsManager();
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

        $this->_objTpl->loadTemplateFile('module_calendar_placeholder.html');

        $this->_objTpl->setVariable(array(
            "TXT_USAGE"     => $_ARRAYLANG['TXT_USAGE'],
            "TXT_CALENDAR_PLACEHOLDER_INTRO"    => $_ARRAYLANG['TXT_CALENDAR_PLACEHOLDER_INTRO'],
            "TXT_CALENDAR_PLACEHOLDER_LIST"     => $_ARRAYLANG['TXT_CALENDAR_PLACEHOLDER_LIST'],
            "TXT_CALENDAR_EVENT_STARTDATE"      => $_ARRAYLANG['TXT_CALENDAR_EVENT_STARTDATE'],
            "TXT_CALENDAR_EVENT_STARTTIME"      => $_ARRAYLANG['TXT_CALENDAR_EVENT_STARTTIME'],
            "TXT_CALENDAR_EVENT_ENDDATE"        => $_ARRAYLANG['TXT_CALENDAR_EVENT_ENDDATE'],
            "TXT_CALENDAR_EVENT_ENDTIME"        => $_ARRAYLANG['TXT_CALENDAR_EVENT_ENDTIME'],
            "TXT_CALENDAR_EVENT_NAME"           => $_ARRAYLANG['TXT_CALENDAR_EVENT_NAME'],
            "TXT_CALENDAR_EVENT_ID"             => $_ARRAYLANG['TXT_CALENDAR_EVENT_ID'],
            "TXT_CALENDAR_EVENT_THUMBNAIL"      => $_ARRAYLANG['TXT_CALENDAR_THUMBNAIL'],
            "TXT_CALENDAR_EVENT_SHORT_DESC"     => $_ARRAYLANG['TXT_CALENDAR_EVENT_SHORT_DESC']
        ));
    }

    /**
    * activate note
    *
    * change the status from a note
    *
    * @access private
    * @global array
    * @global ADONewConnection
    */
    function activateNote()
    {
        global $_ARRAYLANG, $objDatabase;

        $arrStatusNote = $_POST['selectedEventId'];
        if($arrStatusNote != null){
            foreach ($arrStatusNote as $noteId){
                $query = "UPDATE ".DBPREFIX."module_calendar".$this->mandateLink." SET active='1' WHERE id=$noteId";
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
    * @global array
    * @global ADONewConnection
    */
    function deactivateNote()
    {
        global $_ARRAYLANG, $objDatabase;

        $arrStatusNote = $_POST['selectedEventId'];
        if($arrStatusNote != null){
            foreach ($arrStatusNote as $noteId){
                $query = "UPDATE ".DBPREFIX."module_calendar".$this->mandateLink." SET active='0' WHERE id=$noteId";
                $objDatabase->Execute($query);
            }
        }
    }


    /**
     * sign on/off registration
     *
     * @param int $type
     */
    function signRegistration($type)
    {
        global $objDatabase;

        foreach ($_POST['selectedRegId'] as $arrKey => $arrId) {
            $query = "UPDATE ".DBPREFIX."module_calendar".$this->mandateLink."_registrations SET type=$type WHERE id=$arrId";
            $objDatabase->Execute($query);
        }
    }


    /**
     * del registration
     *
     * @param int $regId
     */
    function deleteRegistration($regId)
    {
        global $objDatabase;

        if ($regId == 0) {
            foreach ($_POST['selectedRegId'] as $arrKey => $arrId) {
                //del registration
                $query = "DELETE FROM ".DBPREFIX."module_calendar".$this->mandateLink."_registrations WHERE id='".$arrId."'";
                $objResultDelete = $objDatabase->Execute($query);

                //del data
                $query = "DELETE FROM ".DBPREFIX."module_calendar".$this->mandateLink."_form_data WHERE reg_id='".$arrId."'";
                $objResultDelete = $objDatabase->Execute($query);
            }
        } else {
            //del registration
            $query = "DELETE FROM ".DBPREFIX."module_calendar".$this->mandateLink."_registrations WHERE id='".$regId."'";
            $objResultDelete = $objDatabase->Execute($query);

            //del data
            $query = "DELETE FROM ".DBPREFIX."module_calendar".$this->mandateLink."_form_data WHERE reg_id='".$regId."'";
            $objResultDelete = $objDatabase->Execute($query);
        }
    }


    /**
     * del formular
     *
     * @param int $noteId
     */
    function deleteFormular($noteId)
    {
        global $objDatabase;

        //del formular
        $query = "DELETE FROM ".DBPREFIX."module_calendar".$this->mandateLink."_form_fields WHERE note_id='".$noteId."'";
        $objResultDelete = $objDatabase->Execute($query);

        //del registrations
        $queryReg       = "SELECT id
                             FROM ".DBPREFIX."module_calendar".$this->mandateLink."_registrations
                            WHERE note_id = '".$noteId."'";

        $objResultReg   = $objDatabase->Execute($queryReg);

        if ($objResultReg !== false) {
            while(!$objResultReg->EOF) {
                $this->deleteRegistration($objResultReg->fields['id']);
                $objResultReg->moveNext();
            }
        }
    }


    /**
     * Get CSV File
     *
     * @access private
     * @global ADONewConnection
     * @global array
     * @global array
     */
    function _getCsv($id)
    {
        global $objDatabase, $_ARRAYLANG, $_CONFIG;

        if (empty($id)) {
            CSRF::header("Location: index.php?cmd=calendar".$this->mandateLink);
            return;
        }

        //note title
        $queryNote      = "SELECT `name` FROM ".DBPREFIX."module_calendar".$this->mandateLink." WHERE id=".$id."";
        $objResultNote  = $objDatabase->SelectLimit($queryNote, 1);

        $filename = $objResultNote->fields['name'].".csv";

        //note form fields
        $queryFields    = "SELECT id,`name`
                           FROM ".DBPREFIX."module_calendar".$this->mandateLink."_form_fields
                           WHERE note_id=".$id."
                           ORDER BY `order`, `key`";
        $objResultFields    = $objDatabase->Execute($queryFields);

        if ($objResultFields !== false) {
            while (!$objResultFields->EOF) {
                $arrFormFields[$objResultFields->fields['id']] = htmlentities($objResultFields->fields['name'], ENT_QUOTES, CONTREXX_CHARSET);
                $objResultFields->MoveNext();
            }
        }

        // Because we return a csv, we need to set the correct header
        header("Content-Type: text/comma-separated-values; charset=".CONTREXX_CHARSET, true);
        header("Content-Disposition: attachment; filename=\"$filename\"", true);

        $value = '';

        print ($_ARRAYLANG['TXT_CALENDAR_DATE'].$this->_csvSeparator.$_ARRAYLANG['TXT_CALENDAR_REGISTRATION_TYPE'].$this->_csvSeparator);

        foreach ($arrFormFields as $arrFieldId => $arrFieldName) {
            print $this->_escapeCsvValue($arrFieldName).$this->_csvSeparator;
        }

        print ("\r\n");

        $queryReg       = "SELECT id,time,host,ip_address,type
                           FROM ".DBPREFIX."module_calendar".$this->mandateLink."_registrations
                           WHERE note_id=".$id."
                           ORDER BY `time` DESC, `type` DESC";
        $objResultReg   = $objDatabase->Execute($queryReg);
        if ($objResultReg !== false) {
            while (!$objResultReg->EOF)
            {
                print (date("d.m.Y H:i:s", $objResultReg->fields['time']).$this->_csvSeparator);
                print ($objResultReg->fields['type'] == 1 ?  $this->_escapeCsvValue($_ARRAYLANG['TXT_CALENDAR_REG_REGISTRATION']).$this->_csvSeparator :  $this->_escapeCsvValue($_ARRAYLANG['TXT_CALENDAR_REG_SIGNOFF']).$this->_csvSeparator);

                foreach ($arrFormFields as $arrFieldId => $arrFieldName) {
                    $queryData      = "SELECT `data`
                                       FROM ".DBPREFIX."module_calendar".$this->mandateLink."_form_data
                                       WHERE field_id=".$arrFieldId."
                                       AND reg_id=".$objResultReg->fields['id'];
                    $objResultData  = $objDatabase->SelectLimit($queryData, 1);

                    print ($this->_escapeCsvValue($objResultData->fields['data']).$this->_csvSeparator);
                }

                print ("\r\n");

                $objResultReg->MoveNext();
            }
        }

        exit();
    }

    /**
     * Escape a value that it could be inserted into a csv file.
     *
     * @param string $value
     * @return string
     */
    function _escapeCsvValue(&$value)
    {
        $value = preg_replace('/\r\n/', "\n", $value);
        $valueModified = str_replace('"', '""', $value);

        if ($valueModified != $value || preg_match('/['.$this->_csvSeparator.'\n]+/', $value)) {
            $value = '"'.$valueModified.'"';
        }
        return strtolower(CONTREXX_CHARSET) == 'utf-8' ? utf8_decode($value) : $value;
    }

    /**
     * toggles the active state of an event we got by $_GET
     *
     * @todo take event ID from other than $_GET (or maybe reset the location header
     *
     */
    function _toggleEvent() {

        $id = intval($_GET['id']);
        if($this->objEvent->get($id)) {
            $active = $this->objEvent->getActive();
            $this->objEvent->setActive(!$active, $id);
        }
    }

}

?>
