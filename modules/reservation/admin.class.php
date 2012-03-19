<?php

/**
 * Reservations Manager Class
 *
 * Class for managing the reservations
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author Comvation Development Team <info@comvation.com>
 * @version v1.0.0
 * @uses ImportExport
 * @package     contrexx
 * @subpackage  module_reservation
 * @todo        Edit PHP DocBlocks!
 */

require_once ASCMS_MODULE_PATH . "/reservation/lib/reservationLib.class.php";

class reservationManager extends reservationLib
{
    var $okMessage='';
    var $_objTpl;
    var $pageTitle='';
    var $statusMessage='';
    var $langId;

    /**
     * php 4 constructor
     *
     * @return MemberDirManager
     */
    function reservationManager()
    {
        $this->__construct();
    }

    private $act = '';

    /**
     * Constructor
     *
     */
    function __construct()
    {
        global $_ARRAYLANG, $objTemplate, $_FRONTEND_LANGID;

        $objTemplate->setVariable("CONTENT_TITLE", $_ARRAYLANG['TXT_RESERVATION']);

        $this->_objTpl = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/reservation/template');
        CSRF::add_placeholder($this->_objTpl);

        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);

        $this->showOnlyActive = false;

        $this->langId = $_FRONTEND_LANGID;
        $this->setOptions();

        parent::__construct();
    }

    private function setNavigation()
    {
        global $objTemplate, $_ARRAYLANG;
        
        $objTemplate->setVariable("CONTENT_NAVIGATION","
            <a href='?cmd=reservation' class='".($this->act == '' ? 'active' : '')."'>".$_ARRAYLANG['TXT_OVERVIEW']."</a>
    	    <a href='?cmd=reservation&amp;act=settings' class='".($this->act == 'settings' ? 'active' : '')."'>".$_ARRAYLANG['TXT_SETTINGS']."</a>");
    }

    function getPage()
    {
        global $objTemplate;

        if (empty($_GET['act'])) {
            $_GET['act'] = "";
        }

        switch ($_GET['act']) {
            case 'saveSettings':
                $this->_saveSettings();
                $this->setOptions();
            case 'settings':
                $this->_settings();
                break;
            case 'delete':
                $this->_delete();
                $this->_overview();
                break;
            case 'edit':
                $this->edit();
                $this->_overview();
                break;
            case 'confirm':
                $this->_confirm();
                $this->_overview();
            default:
                $this->_overview();
        }

        $objTemplate->setVariable(array(
                'CONTENT_TITLE'	=> $this->pageTitle,
                'CONTENT_STATUS_MESSAGE' => $this->statusMessage,
                'CONTENT_OK_MESSAGE'	 => $this->okMessage,
                'ADMIN_CONTENT'	=> $this->_objTpl->get()
        ));
        
        $this->act = $_REQUEST['act'];
        $this->setNavigation();
    }


    /**
     * Shows all reservations
     *
     */
    function _overview() {
        global $_ARRAYLANG, $objDatabase;

        $this->_objTpl->loadTemplateFile('module_reservation_overview.html',true,true);
        $this->pageTitle = $_ARRAYLANG['TXT_OVERVIEW'];

        $this->_objTpl->setGlobalVariable(array(
                "TXT_OVERVIEW"  => $_ARRAYLANG['TXT_OVERVIEW'],
                "TXT_STATUS"    => $_ARRAYLANG['TXT_STATUS'],
                "TXT_TIME"      => $_ARRAYLANG['TXT_TIME'],
                "TXT_NAME"      => $_ARRAYLANG['TXT_NAME'],
                "TXT_ACTION"    => $_ARRAYLANG['TXT_ACTION'],
                "TXT_EMAIL"     => $_ARRAYLANG['TXT_EMAIL'],
                "TXT_PHONE"     => $_ARRAYLANG['TXT_PHONE'],
                "TXT_CONFIRM_DELETE_DATA"   => $_ARRAYLANG['TXT_CONFIRM_DELETE_DATA'],
                "TXT_ACTION_IS_IRREVERSIBLE"    => $_ARRAYLANG['TXT_ACTION_IS_IRREVERSIBLE']
        ));

        $today = date("Y")."-".date("m")."-".date("d");
        $days = array();

        $query = "SELECT id, day, unit, name, status, email, confirmed, phone FROM ".DBPREFIX."module_reservation
		          WHERE status = '1' AND lang_id = '".$this->langId."' ORDER BY unit ASC";

        $objResult = $objDatabase->Execute($query);
        if ($objResult) {
            while (!$objResult->EOF) {
                $dsDay = $objResult->fields['day'];
                if (!isset($days[$dsDay])) {
                    $days[$dsDay] = array();
                }
                $days[$dsDay][] = $objResult->fields;

                $objResult->MoveNext();
            }
        }

        foreach ($days as $daykey => $day) {
            $cur_day = strftime("%A, %d.%m.%Y ", mktime(1, 1, 1, substr($daykey, 5, 2), substr($daykey, 8, 2), substr($daykey, 0, 4)));
            $this->_objTpl->setVariable("CURRENT_DAY", $cur_day);


            foreach ($day as $date) {
                $time = date("H:i", $this->options['framestart'] + ($date['unit']-1) * $this->options['unit']);
                $status = ($date['confirmed'] == "1") ? "green" : "red";
                $this->_objTpl->setVariable(array(
                        "RESERVATION_TIME"      => $time,
                        "RESERVATION_STATUS"    => $status,
                        "RESERVATION_NAME"      => $date['name'],
                        "RESERVATION_EMAIL"     => $date['email'],
                        "RESERVATION_PHONE"     => $date['phone'],
                        "RESERVATION_ID"        => $date['id']
                ));
                $this->_objTpl->parse("row");
            }
            $this->_objTpl->parse("day");
        }

    }


    /**
     * Shows the settings page
     *
     * @global $_ARRAYLANG, $objDatabase;
     */
    function _settings() {
        global $_ARRAYLANG, $objDatabase;

        $this->_objTpl->loadTemplateFile('module_reservation_settings.html',true,true);
        $this->pageTitle = $_ARRAYLANG['TXT_OVERVIEW'];

        $minutes_all = $this->options['unit'] / 60;
        if ($minutes_all >= 60) {
            $unit_hours = 0;
            $unit_minutes = $minutes_all;
            for ($i=60; $i<=$minutes_all; $i+=60) {
                $unit_hours++;
                $unit_minutes -= 60;
            }
        } else {
            $unit_minutes = $minutes_all;
            $unit_hours = 0;
        }

        $framestart_hour = date("H", $this->options['framestart']);
        $framestart_minute = date("i", $this->options['framestart']);
        $frameend_hour = date("H", $this->options['frameend']);
        $frameend_minute = date("i", $this->options['frameend']);

        $this->_objTpl->setVariable(array(
                "TXT_CONTACT_SAVE"          => $_ARRAYLANG['TXT_SAVE'],
                "TXT_SETTINGS"              => $_ARRAYLANG['TXT_SETTINGS'],
                "TXT_SETTINGS_UNIT"         => $_ARRAYLANG['TXT_SETTINGS_UNIT'],
                "TXT_SETTINGS_FRAMESTART"   => $_ARRAYLANG['TXT_SETTINGS_FRAMESTART'],
                "TXT_SETTINGS_FRAMEEND"     => $_ARRAYLANG['TXT_SETTINGS_FRAMEEND'],
                "TXT_SETTINGS_DESCRIPTION"  => $_ARRAYLANG['TXT_SETTINGS_DESCRIPTION'],
                "TXT_SETTINGS_MAILTEXT"     => $_ARRAYLANG['TXT_SETTINGS_MAILTEXT'],
                "TXT_YES"                   => $_ARRAYLANG['TXT_YES'],
                "TXT_NO"                    => $_ARRAYLANG['TXT_NO'],
                "TXT_SETTINGS_CONFIRMATION" => $_ARRAYLANG['TXT_SETTINGS_CONFIRMATION'],
                "TXT_SETTINGS_UNIT_DESCRIPTION"         => $_ARRAYLANG['TXT_SETTINGS_UNIT_DESCRIPTION'],
                "TXT_SETTINGS_FRAMESTART_DESCRIPTION"   => $_ARRAYLANG['TXT_SETTINGS_FRAMESTART_DESCRIPTION'],
                "TXT_SETTINGS_FRAMEEND_DESCRIPTION"     => $_ARRAYLANG['TXT_SETTINGS_FRAMEEND_DESCRIPTION'],
                "TXT_SETTINGS_CONFIRMATION_DESCRIPTION" => $_ARRAYLANG['TXT_SETTINGS_CONFIRMATION_DESCRIPTION'],
                "TXT_SETTINGS_MAILTEXT_DESCRIPTION"     => $_ARRAYLANG['TXT_SETTINGS_MAILTEXT_DESCRIPTION'],
                "UNIT_HOUR_LIST"            => $this->_getTimeList($unit_hours, 0, 12, 1),
                "UNIT_MINUTE_LIST"          => $this->_getTimeList($unit_minutes, 0, 59, 5),
                "FRAMESTART_HOUR_LIST"      => $this->_getTimeList($framestart_hour, 0, 23, 1),
                "FRAMESTART_MINUTE_LIST"    => $this->_getTimeList($framestart_minute, 0, 59, 5),
                "FRAMEEND_HOUR_LIST"        => $this->_getTimeList($frameend_hour, 0, 23, 1),
                "FRAMEEND_MINUTE_LIST"      => $this->_getTimeList($frameend_minute, 0, 59, 1),
                "SETTINGS_DESC"             => $this->options['description'],
                "SETTINGS_MAILTEXT"         => $this->options['mailtext'],
                "YES_CHECKED"               => ($this->options['confirmation']) ? "selected=\"selected\"" : "",
                "NO_CHECKED"               => ($this->options['confirmation']) ? "" : "selected=\"selected\"",
        ));

    }

    function _saveSettings() {
        global $objDatabase, $_ARRAYLANG;

        $error = false;

        $unit = ($_POST['unit_hour'] * 60 * 60) + ($_POST['unit_minute'] * 60);
        $framestart = mktime($_POST['framestart_hour'], $_POST['framestart_minute'], 0, 1, 1, 2006);
        $frameend = mktime($_POST['frameend_hour'], $_POST['frameend_minute'], 0, 1, 1, 2006);
        $description = contrexx_addslashes($_POST['description']);
        $confirmation = contrexx_addslashes($_POST['confirmation']);
        $mailtext = contrexx_addslashes($_POST['mailtext']);

        $query = "UPDATE ".DBPREFIX."module_reservation_settings
                  SET setvalue = '".$unit."'
                  WHERE setname = 'unit'";
        if (!$objDatabase->Execute($query)) {
            $error = true;
        }

        $query = "UPDATE ".DBPREFIX."module_reservation_settings
                  SET setvalue = '".$framestart."'
                  WHERE setname = 'framestart'";
        if (!$objDatabase->Execute($query)) {
            $error = true;
        }

        $query = "UPDATE ".DBPREFIX."module_reservation_settings
                  SET setvalue = '".$frameend."'
                  WHERE setname = 'frameend'";
        if (!$objDatabase->Execute($query)) {
            $error = true;
        }

        $query = "UPDATE ".DBPREFIX."module_reservation_settings
                  SET setvalue = '".$description."'
                  WHERE setname = 'description'";
        if (!$objDatabase->Execute($query)) {
            $error = true;
        }

        $query = "UPDATE ".DBPREFIX."module_reservation_settings
                  SET setvalue = '".$confirmation."'
                  WHERE setname = 'confirmation'";
        if (!$objDatabase->Execute($query)) {
            $error = true;
        }

        $query = "UPDATE ".DBPREFIX."module_reservation_settings
                  SET setvalue = '".$mailtext."'
                  WHERE setname = 'mailtext'";
        if (!$objDatabase->Execute($query)) {
            $error = true;
        }

        if ($error) {
            $this->statusMessage = $_ARRAYLANG['TXT_DATABASE_WRITE_ERROR'];
        } else {
            $this->okMessage = $_ARRAYLANG['TXT_DATABASE_SUCCESSFUL'];
        }
    }

    /**
     * Gets a time list (select dropdown)
     */
    function _getTimeList($select, $start=0, $end=59, $unit=1) {
        $retval = "";

        for ($i=$start;$i<=$end;$i+=$unit) {
            $selected = ($select == $i) ? "selected=\"selected\"" : "";
            $retval .= "<option value=\"".sprintf("%02d", $i)."\" $selected>".sprintf("%02d", $i)."</option>";
        }

        return $retval;
    }


    function _delete() {
        global $objDatabase, $_ARRAYLANG;

        $id = intval($_GET['id']);

        $query = "DELETE FROM ".DBPREFIX."module_reservation
                  WHERE id = '".$id."'";
        if ($objDatabase->Execute($query)) {
            $this->okMessage = $_ARRAYLANG['TXT_DELETE_SUCCESSFUL'];
        } else {
            $this->statusMessage = $_ARRAYLANG['TXT_DELETE_ERROR'];
        }
    }


    function _confirm() {
        global $objDatabase, $_ARRAYLANG;

        $id = intval($_GET['id']);

        $query = "SELECT confirmed FROM ".DBPREFIX."module_reservation
                  WHERE id = '".$id."'";
        if ($objDatabase->Execute($query)) {
            $this->okMessage = $_ARRAYLANG['TXT_CONFIRM_SUCCESSFUL'];
        } else {
            $this->statusMessage = $_ARRAYLANG['TXT_CONFIRM_ERROR'];
        }
    }


    function _edit() {

    }

}
