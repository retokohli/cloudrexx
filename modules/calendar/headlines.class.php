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
require_once ASCMS_MODULE_PATH . '/calendar/calendarLib.class.php';

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

    /**
     * Constructor php5
     */
    function __construct($pageContent)
    {
        $this->_pageContent = $pageContent;
        $this->_objTemplate = new HTML_Template_Sigma('.');
    }


    function getHeadlines()
    {
        global $_CONFIG, $objDatabase;

        $this->_objTemplate->setTemplate($this->_pageContent,true,true);
        $category_name = '';
        if ($_CONFIG['calendarheadlinescat'] != 0) {
            $query = "SELECT name FROM ".DBPREFIX."module_calendar_categories
                      WHERE id = {$_CONFIG['calendarheadlinescat']}";
            $objResult = $objDatabase->SelectLimit($query, 1);
            $category_name = $objResult->fields['name'];
        }
        $this->_objTemplate->setVariable(array("CALENDAR_EVENT_CATEGORY" => $category_name));
        $this->_objTemplate->setCurrentBlock('calendar_headlines_row');
        if ($_CONFIG['calendarheadlines']) {
            $today = time();
            //check access
            $auth = $this->_checkAccess();
            if ($auth == true) {
                $access = "";
            } else {
                $access = " AND access='0' ";
            }
            if ($_CONFIG['calendarheadlinescat'] == "0") {
                $query = '    SELECT     id
                            FROM    '.DBPREFIX.'module_calendar_categories
                            WHERE    lang='.FRONTEND_LANG_ID;
                $objResult = $objDatabase->Execute($query);
                if ($objResult->RecordCount() > 0) {
                    $strWhere = ' AND ( ';
                    while (!$objResult->EOF) {
                        $strWhere .= 'catid='.$objResult->fields['id'].' OR ';
                        $objResult->MoveNext();
                    }
                    $strWhere = substr($strWhere,0,strlen($strWhere)-4);
                    $strWhere .= ' ) ';

                    $query = "SELECT id, catid, startdate, pic, comment, enddate, name FROM ".DBPREFIX."module_calendar
                              WHERE enddate > $today  AND
                              active = 1
                              ".$strWhere.$access."
                              ORDER BY startdate ASC
                              LIMIT 0,".$_CONFIG['calendarheadlinescount'];
                }
            } else {
                $query = "SELECT id, catid, pic, startdate, enddate, comment, name FROM ".DBPREFIX."module_calendar
                  WHERE catid = {$_CONFIG['calendarheadlinescat']}
                  AND enddate > $today AND
                  active = 1
                  ".$access."
                  ORDER BY startdate ASC
                  LIMIT 0,".$_CONFIG['calendarheadlinescount'];
            }

            $objResult = $objDatabase->Execute($query);

            if ($objResult !== false && $objResult->RecordCount()>=0) {
                while (!$objResult->EOF) {
                    if (strlen($objResult->fields['comment']) > 100) {
                        $points = "...";
                    } else {
                        $points = "";
                    }

                    $parts= explode("\n", wordwrap($objResult->fields['comment'], 100, "\n"));

                    $this->_objTemplate->setVariable(array(
                        "CALENDAR_EVENT_ENDTIME"        => date("H:i", $objResult->fields['enddate']),
                        "CALENDAR_EVENT_ENDDATE"        => date(ASCMS_DATE_SHORT_FORMAT, $objResult->fields['enddate']),
                        "CALENDAR_EVENT_STARTTIME"        => date("H:i", $objResult->fields['startdate']),
                        "CALENDAR_EVENT_STARTDATE"        => date(ASCMS_DATE_SHORT_FORMAT, $objResult->fields['startdate']),
                        "CALENDAR_EVENT_NAME"            => stripslashes(htmlentities($objResult->fields['name'], ENT_QUOTES, CONTREXX_CHARSET)),
                        "CALENDAR_EVENT_THUMB"             => "<img src='".$objResult->fields['pic'].".thumb' border='0' alt='".htmlentities($objResult->fields['name'], ENT_QUOTES, CONTREXX_CHARSET)."' />",
                        "CALENDAR_EVENT_THUMB_SOURCE"     => $objResult->fields['pic'],
                        "CALENDAR_EVENT_ID"             => $objResult->fields['id'],
                        "CALENDAR_EVENT_COMMENT"        => $objResult->fields['comment'],
                        "CALENDAR_EVENT_SHORT_COMMENT"    => $parts[0].$points,
                    ));

                    $this->_objTemplate->parseCurrentBlock();
                    $objResult->MoveNext();
                }
                return $this->_objTemplate->get();
            }
        }
        $this->_objTemplate->hideBlock('calendar_headlines_row');
        return '';
    }
}

?>
