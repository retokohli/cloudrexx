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
    require_once ASCMS_MODULE_PATH . '/calendar/calendarLib.class.php';
} else {
    require_once ASCMS_MODULE_PATH . '/calendar'.CALENDAR_MANDATE.'/calendarLib.class.php';
}

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
     * @access  public
     */
    function getCalendarPage()
    {
        if (!isset($_REQUEST['cmd'])) {
            $_REQUEST['cmd'] = '';
        }

        switch($_REQUEST['cmd']) {
            case 'event':
                $id = intval($_REQUEST['id']);

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
                    return $this->showEvent();
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
                    return $this->_boxesEvents();
                } else {
                    return $this->_showThreeBoxes();
                }
                break;

            default:
                return $this->_standardView();
                break;
        }
    }


    function _standardView()
    {
        global $objDatabase, $_ARRAYLANG, $_CONFIG, $_LANGID;

        $this->url = CONTREXX_DIRECTORY_INDEX."?section=calendar";

        $this->_objTpl->setTemplate($this->pageContent);

        $monthNames = explode(",", $_ARRAYLANG['TXT_CALENDAR_MONTH_ARRAY']);
        $dayNames = explode(',', $_ARRAYLANG['TXT_CALENDAR_DAY_ARRAY']);

        //check access
        $auth = $this->_checkAccess();



        if ($auth == true) {
            $where = "";
        } else {
            $where = " AND access='0' ";
        }

        if (!empty($_GET['act']) && $_GET['act'] == "search") {

            $datearr = explode("-", $_POST['startDate']);
            $startdate = mktime(0, 0, 0, $datearr[1], $datearr[2], $datearr[0]);
            unset($datearr);
            $datearr = explode("-", $_POST['endDate']);
            $enddate = mktime(23, 59, 59, $datearr[1], $datearr[2], $datearr[0]);

            $keyword = htmlentities(addslashes($_POST['keyword']), ENT_QUOTES, CONTREXX_CHARSET);

            $query = "SELECT cal.id, cal.catid, cal.name, cal.startdate, cal.enddate, cal.placeName,
                MATCH (cal.name,cal.comment,cal.placeName) AGAINST ('%$keyword%') AS score
                FROM ".DBPREFIX."module_calendar".$this->mandateLink." as cal
                LEFT JOIN ".DBPREFIX."module_calendar".$this->mandateLink."_categories as cat ON
                    (cat.id = cal.catid)
                WHERE cat.lang = $_LANGID
                AND (cal.`name` LIKE '%$keyword%' OR
                cal.`comment` LIKE '%$keyword%' OR
                cal.`placeName` LIKE '%$keyword%') AND
                ((cal.startdate BETWEEN $startdate AND $enddate) OR
                (cal.enddate BETWEEN $startdate AND $enddate) OR
                (cal.startdate < $startdate AND cal.enddate > $startdate)) $where
                ORDER BY score ASC";

            $calendarbox = $this->getBoxes(3, date("Y"), date("m"));

        } else {
            // Checks the variables and gets the boxes
            if (isset($_GET['yearID']) && isset($_GET['monthID']) &&  isset($_GET['dayID'])) {
                $day            = $_GET['dayID'];
                $month          = $_GET['monthID'];
                $year           = $_GET['yearID'];
                $startdate      = mktime(00, 00, 00, $month, $day, $year);
                $enddate        = mktime(23, 59, 59, $month, $day, $year);
                $listTitle      = '<a href="'.CONTREXX_DIRECTORY_INDEX.'?section=calendar&amp;catid='.$_GET['catid'].'&amp;yearID='.$year.'&amp;monthID='.$month.'&amp;dayID='.$day.'" target="_self" >'.$day.'. '.$monthNames[$month-1].' '.$_GET['yearID'].'</a>';

                $calendarbox = $this->getBoxes(3, $year, $month, $day);

            } elseif (isset($_GET['yearID']) && isset($_GET['monthID']) && !isset($_GET['dayID'])) {
                $month          = $_GET['monthID'];
                $year           = $_GET['yearID'];
                $startdate      = mktime(00, 00, 00, $month, 01, $year);
                $enddate        = mktime(23, 59, 59, $month, 31, $year);
                $listTitle      = '<a href="'.CONTREXX_DIRECTORY_INDEX.'?section=calendar&amp;catid='.$_GET['catid'].'&amp;yearID='.$year.'&amp;monthID='.$month.'" target="_self" >'.$monthNames[$month-1].' '.$_GET['yearID'].'</a>';

                $calendarbox    = $this->getBoxes(3, $year, $month);

            } else {
                $day            = date("d");
                $month          = date("m");
                $year           = date("Y");
                $select_next_ten = true;
                $startdate      = mktime(00, 00, 00, $month, $day, $year);
                $listTitle      = '<a href="#">'.$_ARRAYLANG['TXT_NEXT_CALENDAR_EVENTS'].'</a>';


                $calendarbox = $this->getBoxes(3, $year, $month, $day);
            }

            if ($select_next_ten && !empty($_GET['catid'])) {
                $query = "SELECT id, catid, name, startdate, enddate, placeName
                    FROM ".DBPREFIX."module_calendar".$this->mandateLink."
                    WHERE catid={$_GET['catid']} AND
                    active = 1 AND
                    ((startdate > $startdate) OR
                    (enddate > $startdate)) $where
                    ORDER BY startdate ASC
                    LIMIT 0,".$_CONFIG['calendardefaultcount'];

            } elseif ($select_next_ten && empty($_GET['catid'])) {
                $query = "SELECT id, catid, name, startdate, enddate, placeName
                    FROM ".DBPREFIX."module_calendar".$this->mandateLink."
                    WHERE active = 1 AND
                    ((startdate > $startdate) OR
                    (enddate > $startdate)) $where
                    ORDER BY startdate ASC
                    LIMIT 0,".$_CONFIG['calendardefaultcount'];

            } elseif (!$select_next_ten && !empty($_GET['catid'])) {
                $query = "SELECT id, catid, name, startdate, enddate, placeName
                    FROM ".DBPREFIX."module_calendar".$this->mandateLink."
                    active = 1 AND
                    ((startdate BETWEEN $startdate AND $enddate) OR
                    (enddate BETWEEN $startdate AND $enddate) OR
                    (startdate < $startdate AND enddate > $startdate)) $where
                    ORDER BY startdate ASC";

            } elseif (!$select_next_ten && empty($_GET['catid'])) {
                $query = "SELECT id, catid, name, startdate, enddate, placeName
                    FROM ".DBPREFIX."module_calendar".$this->mandateLink."
                    ((startdate BETWEEN $startdate AND $enddate) OR
                    (enddate BETWEEN $startdate AND $enddate) OR
                    (startdate < $startdate AND enddate > $startdate)) $where
                    ORDER BY startdate ASC";
            }
        }

        if (empty($_POST['startDate'])) {
            $datepicker_startdate = date("Y-m-d");
        } else {
            $datepicker_startdate = $_POST['startDate'];
        }

        if (empty($_POST['endDate'])) {
            $datepicker_enddate = date("Y-m-d", mktime(0,0,0,date("m"),31,date("Y")));
        } else {
            $datepicker_enddate = $_POST['endDate'];
        }

        $this->_objTpl->setVariable(array(
            "CALENDAR"                  => $calendarbox,
            "TXT_CALENDAR_ALL_CAT"      => $_ARRAYLANG['TXT_CALENDAR_ALL_CAT'],
            "CALENDAR_CATEGORIES"       => $this->category_list((isset($_GET['catid']) ? $_GET['catid'] : "")),
            "CALENDAR_JAVASCRIPT"       => $this->getJS(),
            "CALENDAR_SEARCHED_KEYWORD" => stripslashes((isset($_POST['keyword']) ? $_POST['keyword'] : "")),
            "CALENDAR_DATEPICKER_START" => $datepicker_startdate,
            "CALENDAR_DATEPICKER_END"   => $datepicker_enddate,
            "TXT_CALENDAR_FROM"         => $_ARRAYLANG['TXT_CALENDAR_FROM'],
            "TXT_CALENDAR_TILL"         => $_ARRAYLANG['TXT_CALENDAR_TILL'],
            "TXT_CALENDAR_KEYWORD"      => $_ARRAYLANG['TXT_CALENDAR_KEYWORD'],
            "TXT_CALENDAR_SEARCH"       => $_ARRAYLANG['TXT_CALENDAR_SEARCH'],
            "CALENDAR_LIST_TITLE"       => $listTitle,

        ));

        $this->_showList($query);

        return $this->_objTpl->get();

    }

    function _showList($query)
    {
        global $objDatabase, $_ARRAYLANG, $_LANGID;

        $objResult = $objDatabase->Execute($query);
        if ($objResult !== false) {
            $count = $objResult->RecordCount();
            $i=0;

            $arrCats = array();

            $catQuery = "   SELECT `id`
                            FROM `".DBPREFIX."module_calendar".$this->mandateLink."_categories`
                            WHERE `lang` = ".$_LANGID;


            if(($objRSCats = $objDatabase->Execute($catQuery)) !== false){
                while(!$objRSCats->EOF){
                    $arrCats[] = $objRSCats->fields['id'];
                    $objRSCats->MoveNext();
                }
            }

            if ($count >= 1) {
                while (!$objResult->EOF) {
                    if(!in_array($objResult->fields['catid'], $arrCats)){
                        $objResult->MoveNext();
                        continue;
                    }
                    //load data
                    $this->getNoteData($objResult->fields['id'], "show", 1);

                    $this->_objTpl->setVariable(array(
                        "CALENDAR_ROW"          => $i % 2 == 0 ? "row1" : "row2",
                    ));

                    $i++;

                    $this->_objTpl->parse("event");
                    $objResult->MoveNext();
                }
            } else {
                $this->_objTpl->setVariable(array(
                    "TXT_CALENDAR_NO_EVENTS"     => $_ARRAYLANG['TXT_CALENDAR_EVENTS_NO'],
                ));
            }
        }
    }

    /**
     * Shows the list with the next 20 events
     */
    function _showEventList()
    {
        global $objDatabase, $_ARRAYLANG, $_CONFIG;

        $this->_objTpl->setTemplate($this->pageContent);

        //check access
        $auth = $this->_checkAccess();
        if ($auth == true) {
            $where = "";
        } else {
            $where = " AND access='0' ";
        }

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
            "TXT_CALENDAR_STARTDATE"        => $_ARRAYLANG['TXT_CALENDAR_STARTDATE'],
            "TXT_CALENDAR_ENDDATE"          => $_ARRAYLANG['TXT_CALENDAR_ENDDATE'],
            "TXT_CALENDAR_TITLE"            => $_ARRAYLANG['TXT_CALENDAR_TITLE'],
            "TXT_CALENDAR_ALL_CAT"          => $_ARRAYLANG['TXT_CALENDAR_ALL_CAT'],
            "CALENDAR_CATEGORIES"           => $this->category_list($_GET['catid']),
            "CALENDAR_JAVASCRIPT"           => $this->getJS(),
            "CALENDAR_ICAL_EXPORT"          => $exportLinks,
            "CALENDAR_ICAL_EXPORT_IMG"      => $exportImg,
        ));

        $startdate = !empty($_REQUEST['s']) ? intval($_REQUEST['s']) : time();
        $orderBy =  !empty($_REQUEST['o']) && $_REQUEST['o'] == 'asc' ? 'ASC' : 'DESC';

        if (empty($_GET['catid'])) {
            $query = "SELECT id, catid, name, startdate, enddate, placeName
                        FROM ".DBPREFIX."module_calendar".$this->mandateLink."
                        WHERE active = 1 AND
                        (startdate > $startdate OR
                        enddate > $startdate) $where
                        ORDER BY startdate $orderBy";
        } else {
            $query = "SELECT id, catid, name, startdate, enddate, placeName
                        FROM ".DBPREFIX."module_calendar".$this->mandateLink."
                        WHERE catid = ".addslashes($_GET['catid'])."
                        AND active = 1
                        AND (startdate > $startdate OR
                        enddate > $startdate) $where
                        ORDER BY startdate $orderBy";
        }

        $this->_showList($query);

        return $this->_objTpl->get();

    }


    /**
     * Show thee calendar boxes
     */
    function _showThreeBoxes()
    {
        global $_ARRAYLANG, $_LANGID, $objDatabase;
        $this->url = CONTREXX_DIRECTORY_INDEX."?section=calendar&cmd=boxes&act=list";
        $this->monthnavurl = CONTREXX_DIRECTORY_INDEX."?section=calendar&cmd=boxes";
        // http://www.contrexx.com/index.php?section=calendar&month=01&year=2006&catid=1
        $this->_objTpl->setTemplate($this->pageContent);

        // get std cat
        if (!isset($_GET['catid'])) {
            $query = "SELECT stdCat FROM ".DBPREFIX."module_calendar".$this->mandateLink."_style WHERE id = '2'";
            $objResult = $objDatabase->SelectLimit($query, 1);

            $array1 = explode(' ', stripslashes($objResult->fields["stdCat"]));
            $cats   = '';

            foreach($array1 as $out) {
                $array2 = explode('>', $out);
                $cats[$array2[0]] = $array2[1];
            }

            $_GET['catid'] = $cats[$_LANGID];
            if ($_GET['catid'] == '') {
                $_GET['catid'] = 0;
            }
        }

        if ($_GET['catid'] != 0) {
            $query = "SELECT id
                          FROM ".DBPREFIX."module_calendar".$this->mandateLink."_categories
                         WHERE id = '".intval($_GET['catid'])."'
                           AND lang = '".$_LANGID."'
                           AND status = '1'";
            $objResult = $objDatabase->SelectLimit($query, 1);

            if ($objDatabase->Affected_Rows() == 0) {
                $_GET['catid'] = 0;
            }
        }

        // request_uri
        $requestUri = str_replace('&catid='.$_GET['catid'], '', $_SERVER['REQUEST_URI']);

        // select category
        $this->_objTpl->setVariable(array(
            'TXT_CALENDAR_ALL_CAT' => $_ARRAYLANG['TXT_CALENDAR_ALL_CAT']
        ));

        $query = "SELECT id,
                           name
                      FROM ".DBPREFIX."module_calendar".$this->mandateLink."_categories
                     WHERE lang = '".$_LANGID."'
                       AND status = '1'
                  ORDER BY pos";

        $objResult = $objDatabase->Execute($query);

        $cats = '';
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $select = ($objResult->fields['id'] == $_GET['catid']) ? 'selected' : '';

                $this->_objTpl->setVariable(array(
                    'CALENDAR_CAT_ID'      => $objResult->fields['id'],
                    'CALENDAR_CAT_SELECT'  => $select,
                    'CALENDAR_CAT_NAME'    => htmlentities($objResult->fields['name'], ENT_QUOTES, CONTREXX_CHARSET)
                ));
                //$this->_objTpl->parse("calendar_cat");
                $cats[] = $objResult->fields['id'];

                $objResult->MoveNext();
            }
        }

        // make cat ids
        if (!isset($_GET['catid']) or $_GET['catid'] == '') {
            $_GET['catid'] = 0;
        }

        // TODO what is this for???
        if (isset($_GET['catid']) and $_GET['catid'] != 0) {
            $catslang = "AND catid = '".intval($_GET['catid'])."'";
        } else {
            if (is_array($cats)) {
                for ($x = 0; $x < count($cats); $x++) {
                    if ($x == 0) {
                        $catslang  = "AND (catid = '".$cats[$x]."' ";
                    } else {
                        $catslang .= "OR catid = '".$cats[$x]."' ";
                    }

                }
                $catslang .= ')';
            } else {
                $catslang = '';
            }
        }
        //

        if (isset($_GET['yearID']) && isset($_GET['monthID']) &&  isset($_GET['dayID'])) {
            $calendarbox = $this->getBoxes(3, $_GET['yearID'], $_GET['monthID'], $_GET['dayID']);
            //$this->_showList($_GET['yearID'], $_GET['monthID'], $_GET['dayID']);
            $titledate = date(ASCMS_DATE_SHORT_FORMAT, mktime(0, 0, 0, $_GET['monthID'], $_GET['dayID'], $_GET['yearID']));

        } elseif (isset($_GET['yearID']) && isset($_GET['monthID']) && !isset($_GET['dayID'])) {
            $calendarbox = $this->getBoxes(3, $_GET['yearID'], $_GET['monthID'], $_GET['dayID']);
            //$this->_showList($_GET['yearID'], $_GET['monthID']);
            $titledate = date("F", mktime(0, 0, 0, $_GET['monthID'], 1, $_GET['yearID']));

        } elseif (isset($_GET['yearID']) && !isset($_GET['monthID']) && !isset($_GET['dayID'])) {
            $calendarbox = $this->getBoxes(3, $_GET['yearID']);
            //$this->_showList($_GET['yearID']);
            $titledate = sprintf("%4d", $_GET['yearID']);

        } else {
            $day = date("d");
            $month = date("m");
            $year = date("Y");

            $calendarbox = $this->getBoxes(3, $year, $month, $day, $url);
            //$this->_showList($year, $month, $day);
        }


        $java_script  = "<script language=\"JavaScript\" type=\"text/javascript\">\n<!--\nfunction goTo()\n{\nwindow.location.href = \"".CONTREXX_DIRECTORY_INDEX."?section=calendar&catid=".$_GET['catid']."&month=\"+document.goToForm.goToMonth.value+\"&year=\"+document.goToForm.goToYear.value;\n}\n\n\n";
        $java_script .= "function categories()\n{\nwindow.location.href = \"".$requestUri."&catid=\"+document.selectCategory.inputCategory.value;\n}\n// -->\n</script>";


        $this->_objTpl->setVariable(array(
            "CALENDAR"              => $calendarbox,
            "JAVA_SCRIPT"           => $java_script,
            "CALENDAR_DATE"         => $titledate,
            "TXT_CALENDAR_ALL_CAT"  => $_ARRAYLANG['TXT_CALENDAR_ALL_CAT'],
            "CALENDAR_CATEGORIES"   => $this->category_list($_GET['catid']),
            "CALENDAR_JAVASCRIPT"       => $javascript.$this->getJS()
        ));

        $this->_objTpl->hideBlock("list");

        return $this->_objTpl->get();
    }

    function _boxesEvents()
    {
        global $_ARRAYLANG, $_LANGID, $objDatabase;

        $this->_objTpl->setTemplate($this->pageContent);

        $this->_objTpl->hideBlock("boxes");


        if (!empty($_GET['monthID']) && !empty($_GET['dayID'])) {
            $day = intval($_GET['dayID']);
            $month = intval($_GET['monthID']);
            $year = intval($_GET['yearID']);
            $startdate = mktime(00, 00, 00, $month, $day, $year);
            $enddate = mktime(23, 59, 59, $month, $day, $year);

            $cur_date   = date(ASCMS_DATE_SHORT_FORMAT, $startdate);
        } elseif (!empty($_GET['monthID']) && empty($_GET['dayID'])) {
            $startdate  = mktime(00, 00, 00, $_GET['monthID'], 01, $_GET['yearID']);
            $enddate    = mktime(23, 59, 59, $_GET['monthID'], 31, $_GET['yearID']);
            $year       = date("Y", $startdate);
            $month      = date("m", $startdate);
            $monthnames = explode(",", $_ARRAYLANG['TXT_MONTH_ARRAY']);
            $cur_date   = $monthnames[$month-1]." ".$year;

        } else {
            if ($this->mandate>1) {
                header("Location: ".CONTREXX_DIRECTORY_INDEX."?section=calendar".$this->mandate."&cmd=boxes");
            } else {
                header("Location: ".CONTREXX_DIRECTORY_INDEX."?section=calendar&cmd=boxes");
            }
            exit;
        }

        //check access
        $auth = $this->_checkAccess();
        if ($auth == true) {
            $where = "";
        } else {
            $where = " AND access='0' ";
        }

        $this->_objTpl->setVariable(array(
            "TXT_CALENDAR_STARTDATE" => $_ARRAYLANG['TXT_CALENDAR_STARTDATE'],
            "TXT_CALENDAR_ENDDATE"   => $_ARRAYLANG['TXT_CALENDAR_ENDDATE'],
            "TXT_CALENDAR_TITLE"     => $_ARRAYLANG['TXT_CALENDAR_TITLE'],
            "CALENDAR_DATE"          => $cur_date,
            "CALENDAR_CATEGORIES"    => $this->category_list($_GET['catid']),
            "TXT_CALENDAR_ALL_CAT"   => $_ARRAYLANG['TXT_CALENDAR_ALL_CAT'],
            "CALENDAR_JAVASCRIPT"    => $this->getJS()
        ));

        if (!empty($_GET['catid'])) {
            $query = "SELECT * FROM ".DBPREFIX."module_calendar".$this->mandateLink."
                WHERE catid = {$_GET['catid']} AND
                active = 1 AND
                ((startdate BETWEEN $startdate AND $enddate) OR
                (enddate BETWEEN $startdate AND $enddate) OR
                (startdate < $startdate AND enddate > $startdate)) ".$where."
                ORDER BY startdate ASC";
        } else {
            $query = "SELECT * FROM ".DBPREFIX."module_calendar".$this->mandateLink."
                WHERE active = 1 AND
                ((startdate BETWEEN $startdate AND $enddate) OR
                (enddate BETWEEN $startdate AND $enddate) OR
                (startdate < $startdate AND enddate > $startdate)) ".$where."
                ORDER BY startdate ASC";
        }

        $this->_showList($query);

        return $this->_objTpl->get();
    }


    /**
     * Show Event
     *
     * Shows the detailed view of a event...
     * Yet strange stuff
     */
    function showEvent()
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

        $access = $this->getNoteData(intval($_GET['id']), "show", 0);

        if ($access == true) {
            if (!$this->_checkAccess(intval($_GET['id']))) {
                header("Location: ".CONTREXX_DIRECTORY_INDEX."?section=calendar");
                exit;
            }
        }

        $this->_objTpl->setVariable(array(
            'TXT_CALENDAR_CAT'              => $_ARRAYLANG['TXT_CALENDAR_CAT'],
            'TXT_CALENDAR_NEW'              => $_ARRAYLANG['TXT_CALENDAR_NEW'],
            'TXT_CALENDAR_NAME'             => $_ARRAYLANG['TXT_CALENDAR_NAME'],
            'TXT_CALENDAR_PLACE'            => $_ARRAYLANG['TXT_CALENDAR_PLACE'],
            'TXT_CALENDAR_PRIORITY'         => $_ARRAYLANG['TXT_CALENDAR_PRIORITY'],
            'TXT_CALENDAR_START'            => $_ARRAYLANG['TXT_CALENDAR_START'],
            'TXT_CALENDAR_END'              => $_ARRAYLANG['TXT_CALENDAR_END'],
            'TXT_CALENDAR_COMMENT'          => $_ARRAYLANG['TXT_CALENDAR_COMMENT'],
            'TXT_CALENDAR_LINK'             => $_ARRAYLANG['TXT_CALENDAR_INFO'],
            'TXT_CALENDAR_RESET'            => $_ARRAYLANG['TXT_CALENDAR_RESET'],
            'TXT_CALENDAR_EVENT'            => $_ARRAYLANG['TXT_CALENDAR_TERMIN'],
            'TXT_CALENDAR_STREET_NR'        => $_ARRAYLANG['TXT_CALENDAR_STREET_NR'],
            'TXT_CALENDAR_ZIP'              => $_ARRAYLANG['TXT_CALENDAR_ZIP'],
            'TXT_CALENDAR_LINK'             => $_ARRAYLANG['TXT_CALENDAR_LINK'],
            'TXT_CALENDAR_MAP'              => $_ARRAYLANG['TXT_CALENDAR_MAP'],
            'TXT_CALENDAR_ORGANIZER'        => $_ARRAYLANG['TXT_CALENDAR_ORGANIZER'],
            'TXT_CALENDAR_MAIL'             => $_ARRAYLANG['TXT_CALENDAR_MAIL'],
            'TXT_CALENDAR_ORGANIZER_NAME'   => $_CORELANG['TXT_NAME'],
            'TXT_CALENDAR_TITLE'            => $_ARRAYLANG['TXT_CALENDAR_TITLE'],
            'TXT_CALENDAR_ACCESS'           => $_ARRAYLANG['TXT_CALENDAR_ACCESS'],
            'TXT_CALENDAR_ATTACHMENT'       => $_ARRAYLANG['TXT_CALENDAR_ATTACHMENT'],
            'TXT_CALENDAR_PRIORITY'         => $_ARRAYLANG['TXT_CALENDAR_PRIORITY'],
            'TXT_CALENDAR_DATE'             => $_ARRAYLANG['TXT_CALENDAR_DATE'],
            'TXT_CALENDAR_BACK'             => $_ARRAYLANG['TXT_CALENDAR_BACK'],
            'TXT_CALENDAR_REGISTRATION'     => $_ARRAYLANG['TXT_CALENDAR_REGISTRATION'],
            'TXT_CALENDAR_REGISTRATION_INFO'=> $_ARRAYLANG['TXT_CALENDAR_REGISTRATION_INFO'],

            'CALENDAR_ICAL_EXPORT'          => '<a href="'.CONTREXX_DIRECTORY_INDEX.'?section=calendar&amp;cmd=event&amp;export=iCal&amp;id='.intval($_GET['id']).'" title="'.$_ARRAYLANG['TXT_CALENDAR_EXPORT_ICAL_EVENT'].'">
                                                '.$_ARRAYLANG['TXT_CALENDAR_EXPORT_ICAL_EVENT'].' <img style="padding-top: -1px;" border="0" src="images/modules/calendar/ical_export.gif" alt="'.$_ARRAYLANG['TXT_CALENDAR_EXPORT_ICAL_EVENT'].'" title="'.$_ARRAYLANG['TXT_CALENDAR_EXPORT_ICAL_EVENT'].'" />
                                            </a>',
            'CALENDAR_ICAL_EXPORT_IMG'      => '<a href="'.CONTREXX_DIRECTORY_INDEX.'?section=calendar&amp;cmd=event&amp;export=iCal&amp;id='.intval($_GET['id']).'" title="'.$_ARRAYLANG['TXT_CALENDAR_EXPORT_ICAL_EVENT'].'">
                                                <img border="0" src="images/modules/calendar/ical_export.gif" alt="'.$_ARRAYLANG['TXT_CALENDAR_EXPORT_ICAL_EVENT'].'" title="'.$_ARRAYLANG['TXT_CALENDAR_EXPORT_ICAL_EVENT'].'" />
                                            </a>',
        ));

       return $this->_objTpl->get();
    }

    function getJS()
    {
        return  '<script type="text/javascript">
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
            $time   = mktime();
            $noteId = intval($_POST['id']);
            $type   = intval($_POST['type']);
            $ip     = "";
            $host   = "";

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
                        'TXT_CALENDAR_BACK'                      => $_CORELANG['TXT_BACK'],
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
                    $getKey     = base64_decode($_GET['key']);

                    $arrGet     = explode("#", $getKey);

                    $noteId     = intval($arrGet[0]);
                    $userId     = intval($arrGet[1]);
                    $noteKeyGet = $arrGet[2];
                } elseif (isset($_GET['id']) && empty($_GET['key'])) {
                    $noteId     = intval($_GET['id']);
                }

                //get note details
                $query          = "SELECT `id`, `key`, `public`, `all_groups`, `groups`, `num`
                                    FROM ".DBPREFIX."module_calendar".$this->mandateLink."
                                    WHERE id = '".$noteId."'";

                $objResult      = $objDatabase->SelectLimit($query, 1);

                $noteKey        = $objResult->fields['key'];
                $noteGroups     = $objResult->fields['groups'];
                $notePublic     = $objResult->fields['public'];
                $noteSubscriber = $this->_countSubscriber($noteId);

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
