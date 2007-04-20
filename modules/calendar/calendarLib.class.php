<?php
/**
 * Calendar
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author 		Astalavista Development Team <thun@astalvista.ch>
 * @version 	1.1.0
 * @package     contrexx
 * @subpackage  module_calendar
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once dirname(__FILE__) . "/lib/activecalendar/activecalendar.php";

/**
 * Calendar
 *
 * LibClass to manage cms calendar
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author Astalavista Development Team <thun@astalvista.ch>
 * @access public
 * @version 1.1.0
 * @package     contrexx
 * @subpackage  module_calendar
 */
class calendarLibrary
{
	var $_filename = '';
    var $_objTpl;
    var $strErrMessage = '';
    var $strOkMessage = '';
    var $calDay;
    var $calMonth;
    var $calYear;
    var $calDate;
    var $calDate2;
    var $calDate3;
    var $calendarDay;

    var $calStartYear;
    var $calEndYear;
    var $paging;

    var $calendarMonth;

    var $url;
    var $monthnavur=null;

    var $showOnlyActive = true;

   	var $_cachedCatNames = array();

    /**
     * PHP 5 Constructor
     */
    function __construct($url)
    {
        $this->calendarLibrary($url);
    }


    /**
     * Constructor for php 4
     */
    function calendarLibrary($url)
    {
        global $_CONFIG;
        $this->calStartYear = 2004;
        $this->calEndYear   = 2037;
        $this->paging       = intval($_CONFIG['corePagingLimit']);

        $this->_objTpl = &new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/calendar/template');
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);

        $this->url = $url;
    }


	/**
	 * check what to export and call specific funciton
	 *
	 * @param string $what to export
	 * @param integer $id the ID of the event/category
	 */
    function _iCalExport($what, $id = 0){
    	switch($what){
    		case 'event':
    			$this->_iCalExportEvent($id);
    			break;
    		case 'category':
    			$this->_iCalExportCategory($id);
    			break;
    		case 'all':
    			$this->_iCalExportAll();
    			break;
    	}
    }

    /**
     * create iCal file and send it to the client
     *
     * @param array $arrEvents array of events to export
     */
    function _sendICal($arrEvents){
    	require_once(ASCMS_LIBRARY_PATH.'/iCalcreator/iCalcreator.class.php');

    	$c = new vcalendar();
		$c->setMethod('PUBLISH');

		foreach ($arrEvents as $arrEvent) {
			$comment 		= $this->_filterHTML($arrEvent['comment']);
			$place 			= $this->_filterHTML($arrEvent['place']);
			$name 			= $this->_filterHTML($arrEvent['name']);
			$categoryName 	= $this->_filterHTML($this->_getCategoryNameByEventId($arrEvent['id']));
			$infoURL		= $this->_filterHTML($arrEvent['info']);

			$ev = new vevent();
	    	$ev->setDtstart(array('timestamp' => $arrEvent['startdate']));
	    	$ev->setDtend(array('timestamp' => $arrEvent['enddate']));
	    	$ev->setAction('DISPLAY');
	    	if(!empty($comment)){
		    	$ev->setComment($comment);
		    	$ev->setDescription($comment);
	    	}
	    	if(!empty($place)){
				$ev->setLocation($place);
	    	}
	    	if(!empty($arrEvent['priority'])){
				$ev->setPriority($arrEvent['priority']);
	    	}
			if(!empty($name)){
				$ev->setSummary($name);
			}
			if(!empty($categoryName)){
				$ev->setCategories($categoryName);
			}
			if(!empty($infoURL)){
				$ev->setUrl($infoURL);
			}
			$ev->setClass('PUBLIC');

			$c->addComponent($ev);
		}

		if(trim($this->_filename) == ''){
			$this->_filename = 'event';
		}

		header('Content-Type: text/calendar; charset='.CONTREXX_CHARSET);
		header('Content-Disposition: attachment; filename="'.$this->_filename.'.ics"');
		die($c->createCalendar());

    }


	/**
	 * export calendar event as iCal-file
	 *
	 * @param integer $id ID of the event
	 * @return bool false on error
	 */
    function _iCalExportEvent($id){
		require_once(ASCMS_LIBRARY_PATH.'/iCalcreator/iCalcreator.class.php');
		//wrap this in an array, since it is only one event (see _sendICal() to understand)
		$this->_sendICal(array($this->getEventByID($id)));
    }

	/**
	 * export calendar category with all events as iCal-file
	 *
	 * @param integer $id ID of the category
	 * @return void
	 */
    function _iCalExportCategory($catID){
    	if($catID == 0){
    		$this->_iCalExportAll();
    	}
		require_once(ASCMS_LIBRARY_PATH.'/iCalcreator/iCalcreator.class.php');
		$this->_filename = html_entity_decode($objRS->fields['name'], ENT_QUOTES, CONTREXX_CHARSET);
		$this->_sendICal($this->getEventsFromCategoryByID($catID));
    }


    /**
     * export all calendar events as iCal-file
     *
     * @return void
     */
    function _iCalExportAll(){
    	$this->_filename = 'all';
		$this->_sendICal($this->_getAllEvents());
    }


    /**
     * return all Events in an array
     *
     * @return array $arrEvents all Events
     */
    function _getAllEvents(){
    	global $objDatabase, $_ARRAYLANG;
		$query = "	SELECT 	`id`, `catid`, 	`startdate`, 	`enddate`,	`priority`,
    						`name`, 	`comment`,		`place`,	`info`
    				FROM `".DBPREFIX."module_calendar`";

		if(($objRS = $objDatabase->Execute($query)) !== false){
    		if($objRS->RecordCount() < 1){
    			return false;
    		}
			$arrEvents = array();
			while(!$objRS->EOF){
	    		// cache the categoryNames to reduce amount of DB queries
	    		if(!isset($this->_cachedCatNames[$objRS->fields['catid']])){
	    			$categoryName = $this->_cachedCatNames[$objRS->fields['catid']] = $this->_getCategoryNameByEventId($objRS->fields['id']);
	    		}else{
	    			$categoryName = $this->_cachedCatNames[$objRS->fields['catid']];
	    		}

				$arrEvents[] = array(
					'id'			=> $objRS->fields['id'],
					'catid' 		=> $objRS->fields['catid'],
					'startdate'		=> $objRS->fields['startdate'],
					'enddate' 		=> $objRS->fields['enddate'],
					'priority' 		=> $objRS->fields['priority'],
					'name' 			=> html_entity_decode($objRS->fields['name'], ENT_QUOTES, CONTREXX_CHARSET),
					'categoryname'	=> html_entity_decode($categoryName, ENT_QUOTES, CONTREXX_CHARSET),
					'comment' 		=> html_entity_decode($objRS->fields['comment'], ENT_QUOTES, CONTREXX_CHARSET),
					'place' 		=> html_entity_decode($objRS->fields['place'], ENT_QUOTES, CONTREXX_CHARSET),
					'info' 			=> html_entity_decode($objRS->fields['info'], ENT_QUOTES, CONTREXX_CHARSET),
				);
				$objRS->MoveNext();
			}
			return $arrEvents;
    	}else{
    		$this->strErrMessage = $_ARRAYLANG['TXT_DATABASE_READ_ERROR'];
    	}
    }

    /**
     * return event(s) by ID
     *
     * @param integer $eventID
     * return array $arrEvents;
     */
    function getEventByID($eventID){
		global $objDatabase, $_ARRAYLANG;
		$query = "	SELECT 	`catid`, 	`startdate`, 	`enddate`,	`priority`,
    						`name`, 	`comment`,		`place`,	`info`
    				FROM `".DBPREFIX."module_calendar`
    				WHERE `id` = ".$eventID;
    	if(($objRS = $objDatabase->SelectLimit($query, 1)) !== false){
    		if($objRS->RecordCount() < 1){
    			return false;
    		}

    		// cache the categoryNames to reduce amount of DB queries
    		if(!isset($this->_cachedCatNames[$objRS->fields['catid']])){
    			$categoryName = $this->_cachedCatNames[$objRS->fields['catid']] = $this->_getCategoryNameByEventId($eventID);
    		}else{
    			$categoryName = $this->_cachedCatNames[$objRS->fields['catid']];
    		}
			return array(
				'id'			=> $objRS->fields['id'],
				'catid' 		=> $objRS->fields['catid'],
				'startdate'		=> $objRS->fields['startdate'],
				'enddate' 		=> $objRS->fields['enddate'],
				'priority' 		=> $objRS->fields['priority'],
				'name' 			=> html_entity_decode($objRS->fields['name'], ENT_QUOTES, CONTREXX_CHARSET),
				'categoryname'	=> html_entity_decode($categoryName, ENT_QUOTES, CONTREXX_CHARSET),
				'comment' 		=> html_entity_decode($objRS->fields['comment'], ENT_QUOTES, CONTREXX_CHARSET),
				'place' 		=> html_entity_decode($objRS->fields['place'], ENT_QUOTES, CONTREXX_CHARSET),
				'info' 			=> html_entity_decode($objRS->fields['info'], ENT_QUOTES, CONTREXX_CHARSET),
			);
    	}else{
    		$this->strErrMessage = $_ARRAYLANG['TXT_DATABASE_READ_ERROR'];
    	}
    }


	/**
	 * return events from a category
	 *
	 * @param integer $categoryID
	 * @return array $arrEvents
	 */
    function getEventsFromCategoryByID($categoryID){
    	global $objDatabase, $_ARRAYLANG;

    	$query = "	SELECT `id` FROM `".DBPREFIX."module_calendar`
    				WHERE `catid` = ".$categoryID;
    	if(($objRS = $objDatabase->Execute($query)) !== false){
    		if($objRS->RecordCount() < 1){
    			return false;
    		}
    		$this->_filename = $this->getCategoryNameFromCategoryId($categoryID);
    		$arrEvents = array();
			while(!$objRS->EOF){
				array_push($arrEvents, $this->getEventByID($objRS->fields['id']));
				$objRS->MoveNext();
			}
			return $arrEvents;
    	}else{
    		$this->strErrMessage = $_ARRAYLANG['TXT_DATABASE_READ_ERROR'];
    	}
    }


    /**
     * return the categoryname for the specified category ID
     *
     * @param integer $catId
     */
    function getCategoryNameFromCategoryId($catId){
		global $objDatabase, $_ARRAYLANG;

    	$query = "	SELECT `name` FROM `".DBPREFIX."module_calendar_categories`
    				WHERE `id` = ".$catId;
    	if(($objRS = $objDatabase->SelectLimit($query, 1)) !== false){
    		if($objRS->RecordCount() < 1){
    			return false;
    		}
			return $objRS->fields['name'];
    	}else{
    		$this->strErrMessage = $_ARRAYLANG['TXT_DATABASE_READ_ERROR'];
    	}
    }


	/**
	 * return catgeory name by eventID
	 *
	 * @param integer $eventID
	 * @return string $categoryName, bool false on failure
	 *
	 */
    function _getCategoryNameByEventId($eventID){
    	global $objDatabase, $_LANGID;

    	$query = "	SELECT `c`.`name` FROM `".DBPREFIX."module_calendar` AS `e`
    				INNER JOIN `".DBPREFIX."module_calendar_categories` AS `c`
    				ON (`e`.`catid` = `c`.`id`)
    				WHERE `lang` = ".$_LANGID."
    				AND `e`.`id` = ".$eventID;
    	if( ($objRS = $objDatabase->SelectLimit($query, 1)) !== false){
    		if($objRS->RecordCount() < 1){
    			return false;
    		}
    		return $objRS->fields['name'];
    	}else{
    		return false;
    	}


    }


    /**
     * remove HTML tags from a string
     *
     * @param string $str to strip HTML tags from
     * @return string $str_without_html_tags
     */
    function _filterHTML($str){
    	$str = preg_replace("#<([^>]+)>#s", '', $str);
		return preg_replace("#[\s\t\r\n]{2,}+#s", "\n", $str);
    }

    // write month names
    function monthName($month)
    {
        global $_ARRAYLANG;

        $months = explode(',', $_ARRAYLANG['TXT_MONTH_ARRAY']);
        $name = $months[$month - 1];
        return $name;
    }

    // get day note
    function getDayNote($id, $showboxes=true)
    {
        global $objDatabase, $_ARRAYLANG, $_LANGID;

        $query = "SELECT id,
                           catid,
                           startdate,
                           enddate,
                           priority,
                           name,
                           comment,
                           place,
                           info
                      FROM ".DBPREFIX."module_calendar
                     WHERE id = '".intval($id)."'";

        $objResult = $objDatabase->Execute($query);

        $title        = date(ASCMS_DATE_SHORT_FORMAT, $objResult->fields['startdate']);
        $start        = date(ASCMS_DATE_FORMAT, $objResult->fields['startdate']);
        $end        = date(ASCMS_DATE_FORMAT, $objResult->fields['enddate']);
        $comment       = stripslashes($objResult->fields['comment']);

        $startdate = date("d.m.Y", $objResult->fields['startdate']);
        $enddate = date("d.m.Y", $objResult->fields['enddate']);
        $starttime = date("H:i", $objResult->fields['startdate']);
        $endtime = date("H:i", $objResult->fields['enddate']);

        if ($showboxes) {
            $boxes = $this->getBoxes(3, date("Y", $objResult->fields['startdate']),
                date("m", $objResult->fields['startdate']),
                date("d", $objResult->fields['startdate']));
        }

        $this->_objTpl->setVariable("CALENDAR", $boxes);

        if( $objResult->fields['priority'] == 1){
            $priority_gif = 'priority2h';
        }
        elseif ($objResult->fields['priority'] == 2) {
            $priority_gif = 'priorityh';
        }
        elseif ($objResult->fields['priority'] == 3) {
            $priority_gif = 'priorityno';
        }
        elseif ($objResult->fields['priority'] == 4) {
            $priority_gif = 'priorityl';
        }
        elseif ($objResult->fields['priority'] == 5) {
            $priority_gif = 'priority2l';
        }

        $query = "SELECT name
                       FROM ".DBPREFIX."module_calendar_categories
                      WHERE id = '".$objResult->fields['catid']."'";
        $objResult2 = $objDatabase->SelectLimit($query);

        // parse to template
        $this->_objTpl->setVariable(array(
            'TXT_CALENDAR_CAT'          => $_ARRAYLANG['TXT_CALENDAR_CAT'],
            'TXT_CALENDAR_DATE'            => $_ARRAYLANG['TXT_CALENDAR_DATE'],
            'TXT_CALENDAR_NAME'            => $_ARRAYLANG['TXT_CALENDAR_NAME'],
            'TXT_CALENDAR_PLACE'        => $_ARRAYLANG['TXT_CALENDAR_PLACE'],
            'TXT_CALENDAR_PRIORITY'        => $_ARRAYLANG['TXT_CALENDAR_PRIORITY'],
            'TXT_CALENDAR_START'        => $_ARRAYLANG['TXT_CALENDAR_START'],
            'TXT_CALENDAR_END'            => $_ARRAYLANG['TXT_CALENDAR_END'],
            'TXT_CALENDAR_COMMENT'        => $_ARRAYLANG['TXT_CALENDAR_COMMENT'],
            'TXT_CALENDAR_BACK'         => $_ARRAYLANG['TXT_CALENDAR_BACK'],
            'CALENDAR_TITLE'            => $title,
            'CALENDAR_NAME'             => stripslashes(htmlentities($objResult->fields['name'], ENT_QUOTES, CONTREXX_CHARSET)),
            'CALENDAR_PLACE'            => stripslashes(htmlentities($objResult->fields['place'], ENT_QUOTES, CONTREXX_CHARSET)),
            'CALENDAR_PRIORITY_GIF'     => $priority_gif,
            'CALENDAR_PRIORITY'            => $objResult->fields['priority'],
            'CALENDAR_START'            => $start,
            'CALENDAR_END'                => $end,
            'CALENDAR_STARTTIME'        => $starttime,
            'CALENDAR_ENDTIME'            => $endtime,
            'CALENDAR_STARTDATE'        => $startdate,
            'CALENDAR_ENDDATE'            => $enddate,
            'CALENDAR_COMMENT'            => $comment,
            'CALENDAR_ID'                => $id,
            'CALENDAR_CAT'              => stripslashes($objResult2->fields['name']),
            'CALENDAR_ICAL_EXPORT'      => '<a href="?section=calendar&amp;cmd=event&amp;export=iCal&amp;id='.$id.'" title="'.$_ARRAYLANG['TXT_CALENDAR_EXPORT_ICAL_EVENT'].'">
            									'.$_ARRAYLANG['TXT_CALENDAR_EXPORT_ICAL_EVENT'].' <img style="padding-top: -1px;" border="0" src="images/modules/calendar/ical_export.gif" alt="'.$_ARRAYLANG['TXT_CALENDAR_EXPORT_ICAL_EVENT'].'" title="'.$_ARRAYLANG['TXT_CALENDAR_EXPORT_ICAL_EVENT'].'" />
            								</a>',
        ));

        if (!empty($objResult->fields['info'])) {
            $info = $info_href = $objResult->fields['info'];
            if (strlen($info) > 50) {
                $info = substr($info, 0, 47);
                $info .= "...";
            }
            $this->_objTpl->setVariable(array(
                'TXT_CALENDAR_INFO'         => $_ARRAYLANG['TXT_CALENDAR_INFO'],
                'CALENDAR_INFO'             => $info,
                'CALENDAR_INFO_HREF'        => $info_href
            ));

        } else {
            $this->_objTpl->hideBlock("infolink");
        }
    }

	  /**
	   *  function dateNumber
	   *
	   *  convert date-number from one-digit to two-digit
	   */
	  function dateNumber($number)
	  {
	      $number = intval($number);
	      if(strlen($number)==1)
	      {
	          $number = '0'.$number;
	      }
	      return $number;
	  }


    /**
     * Get Boxes
     *
     * Returns 3 calendar Boxes
     */
    function getBoxes($howmany, $year, $month=0, $day=0, $catid=NULL)
    {
        global $objDatabase, $_ARRAYLANG;

        $url = htmlentities($this->url, ENT_QUOTES, CONTREXX_CHARSET);

        if (empty($catid)) {
            if (empty($_GET['catid'])) {
                $catid = 0;
            } else {
                $catid = $_GET['catid'];
            }
        }

        $url.="&amp;catid=$catid";

        $month = intval($month);
        $year = intval($year);
        $day = intval($day);
        $firstblock = true;

        $monthnames = explode(",", $_ARRAYLANG['TXT_CALENDAR_MONTH_ARRAY']);
        $daynames = explode(',', $_ARRAYLANG['TXT_CALENDAR_DAY_ARRAY']);

        for ($i=0; $i<$howmany; $i++) {

            $cal = new activeCalendar($year, $month, $day);
            $cal->setMonthNames($monthnames);
            $cal->setDayNames($daynames);
            if ($firstblock) {
                $cal->enableMonthNav($url);
            } else {
                // This is necessary for the modification of the linkname
                // The modification makes a link on the monthname
                $cal->urlNav=$url;
            }

            // for seperate variable for the month links
            if (!empty($this->monthnavurl)) {
                $cal->urlMonthNav = htmlentities($this->monthnavurl, ENT_QUOTES, CONTREXX_CHARSET);
            }

            // get events
            if (empty($catid)) {
                $query = "SELECT * FROM ".DBPREFIX."module_calendar";
            } else {
                $query = "SELECT * FROM ".DBPREFIX."module_calendar
                          WHERE catid=$catid";
            }
            $objResult = $objDatabase->Execute($query);

            while (!$objResult->EOF) {
                if (($objResult->fields['active'] == 1 && $this->showOnlyActive) || !$this->showOnlyActive) {
                    $startdate = $objResult->fields['startdate'];
                    $enddate = $objResult->fields['enddate'];

                    $eventYear     = date("Y", $startdate);
                    $eventMonth = date("m", $startdate);
                    $eventDay    = date("d", $startdate);

                    $eventEndDay = date("d", $enddate);
                    $eventEndMonth = date("m", $enddate);

                    // do only something when the event is in the current month
                    if ($eventMonth == $month || $eventEndMonth == $month) {
                        // if the event is longer than one day but every day is in the same month
                        if ($eventEndDay > $eventDay && $eventMonth == $eventEndMonth) {
                            $curday = $eventDay;
                            while ($curday <= $eventEndDay) {
                                $eventurl = $url."&amp;yearID=$eventYear&amp;monthID=$eventMonth&amp;dayID=$curday";
                                $cal->setEvent("$eventYear", "$eventMonth", "$curday", false, $eventurl);

                                $curday++;
                            }
                        } elseif ($eventEndMonth > $eventMonth) {
                            if ($eventMonth == $month) {
                                // Show the part of the event in the starting month
                                $curday = $eventDay;
                                while ($curday <= 31) {
                                    $eventurl = $url."&amp;yearID=$eventYear&amp;monthID=$eventMonth&amp;dayID=$curday";
                                    $cal->setEvent("$eventYear", "$eventMonth", "$curday", false, $eventurl);

                                    $curday++;
                                }
                            } elseif ($eventEndMonth == $month) {
                                // show the part of the event in the ending month
                                $curday = $eventEndDay;
                                while ($curday > 0) {
                                    $eventurl = $url."&amp;yearID=$eventYear&amp;monthID=$eventMonth&amp;dayID=$curday";
                                    $cal->setEvent("$eventYear", "$eventEndMonth", "$curday", false, $eventurl);

                                    $curday--;
                                }
                            }
                        } else {
                            $eventurl = $url."&amp;yearID=$eventYear&amp;monthID=$eventMonth&amp;dayID=$eventDay";
                            $cal->setEvent("$eventYear", "$eventMonth", "$eventDay", false, $eventurl);
                        }
                    }
                }
                $objResult->MoveNext();
            }
            $retval .= $cal->showMonth();

            if ($month == 12) {
                $year++;
                $month = 1;
            } else {
                $month++;
            }
            $day = 0;

            $firstblock = false;
        }

        return $retval;
    }


    /**
     * Category List
     *
     * Returns multiple <option> tags for the
     * list of categories
     */
    function category_list($selected_var, $name="categories") {
        global $objDatabase, $_ARRAYLANG;

        $calendar_categories = "<form action=\"#\" id=\"selectcat\">
            <select name=\"$name\" onchange=\"changecat()\">
                <option value=\"0\">".$_ARRAYLANG['TXT_CALENDAR_ALL_CAT']."</option>";

        // makes the category list
        $query = "SELECT id,name,lang FROM ".DBPREFIX."module_calendar_categories WHERE status = '1' ORDER BY pos";
        $objResult = $objDatabase->Execute($query);

        while (!$objResult->EOF) {
            if ($objResult->fields['id'] == $selected_var) {
                $selected = " selected=\"selected\"";
            } else {
                $selected = "";
            }


            $calendar_categories .= "<option value=\"".$objResult->fields['id']."\"$selected>".$objResult->fields['name']."</option>";
            $objResult->MoveNext();
        }

        $calendar_categories .= "
                </select>
            </form>";

        return $calendar_categories;
    }
}
?>
