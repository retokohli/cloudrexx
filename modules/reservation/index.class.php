<?php

/**
 * Reservations module
 *
 * Frontend Reservations Class
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @access      public
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_reservation
 * @todo        Edit PHP DocBlocks!
 */

require_once ASCMS_PATH . "/lib/activecalendar/activecalendar.php";
require_once ASCMS_MODULE_PATH . "/reservation/lib/reservationLib.class.php";

/**
 * Frontend Reservations Class
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @access      public
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_reservation
 * @todo        Edit PHP DocBlocks!
 */
class reservations extends reservationLib
{
    var $langId;
    var $_objTpl;
    var $statusMessage;
    var $error;

    /**
     * PHP5 constructor
     * @param  string  $pageContent
     * @global InitCMS $objInit
     */
    function __construct($pageContent)
    {
        global $objInit;

        $this->pageContent = $pageContent;

        $this->_objTpl = new HTML_Template_Sigma('.');
        CSRF::add_placeholder($this->_objTpl);
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);
        $this->_objTpl->setTemplate($this->pageContent, true, true);
        parent::__construct();
        $this->langId = $objInit->userFrontendLangId;
        $this->setOptions();
    }

    /**
     * Get Page
     *
     * @access public
     * @return string Page content
     */
    function getPage()
    {
        global $objTemplate;

        if (!isset($_GET['cmd'])){
            $_GET['cmd'] = '';
        }

        if (!isset($_GET['act'])) {
            $_GET['act'] = '';
        }

        switch ($_GET['cmd']) {
            case 'reserve':
                $this->reserve();
                break;
            default:
                if ($_GET['act'] == "save") {
                    $this->save();
                } elseif ($_GET['act'] == "confirm") {
                    $this->confirm();
                }
                $this->dayView();
        }

        return $this->_objTpl->get();
    }

    /**
     * Shows a day
     *
     * @global $_ARRAYLANG
     */
    function dayView()
    {
        global $_ARRAYLANG, $objDatabase, $_CORELANG;

        if (empty($_GET['yearID']) && empty($_GET['monthID']) && empty($_GET['dayID'])) {
            list ($year, $month, $day) = split("-", date("Y-n-j"));
        } elseif (empty($_GET['dayID']) && !empty($_GET['monthID']) && !empty($_GET['yearID'])) {
            $year = $_GET['yearID'];
            $month = $_GET['monthID'];
            $day = "1";
        } else {
            $year = $_GET['yearID'];
            $month = $_GET['monthID'];
            $day = $_GET['dayID'];
        }

        $cal = new activeCalendar($year, $month, $day);
        $cal->enableMonthNav("?section=reservation");
        $cal->enableDayLinks("?section=reservation");
        $cal->setMonthNames(split(",", $_CORELANG['TXT_MONTH_ARRAY']));

        $this->_objTpl->setVariable(array(
            "RESERVATION_CALENDAR"      => $cal->showMonth(),
            "RESERVATION_DESC"          => nl2br($this->options['description']),
            "RESERVATION_DATE"          => strftime("%A, %d.%m.%Y", mktime(1, 1, 1, $month, $day, $year))
        ));

        $date = sprintf("%04d-%02d-%02d", $year, $month, $day);

        $query = "SELECT id, confirmed, status, unit, lang_id FROM ".DBPREFIX."module_reservation
                  WHERE day = '".$date."' AND
                  lang_id = '".$this->langId."'";
        $objResult = $objDatabase->Execute($query);

        $data = array();
        if ($objResult) {
            while (!$objResult->EOF) {
                $data["{$objResult->fields['unit']}"] = array(
                    "status"    => $objResult->fields['status'],
                    "confirmed" => $objResult->fields['confirmed']
                );

                $objResult->MoveNext();
            }
        }

        $framestart = $counter = $this->options['framestart'];
        $frameend = $this->options['frameend'];
        $unit = 1;

        while ($counter <= $frameend) {
            $this->_objTpl->setVariable(array(
                    "RESERVATION_START_HOUR"      => date("H", $counter),
                    "RESERVATION_START_MINUTES"   => date("i", $counter),
                    "RESERVATION_END_HOUR"      => date("H", $counter+$this->options['unit']),
                    "RESERVATION_END_MINUTES"   => date("i", $counter+$this->options['unit']),
                    "RESERVATION_DAY"       => sprintf("%04d-%02d-%02d", $year, $month, $day),
                    "RESERVATION_UNIT"      => $unit
            ));
            if (isset($data["$unit"])) {
                $this->_objTpl->setVariable(array(
                    "RESERVATION_ROWNAME"   => ($data["$unit"]['confirmed'] && $data["$unit"]['status']) ? "confirmed" : "occupied",
                    "TXT_RESERVATION_STATE" => ($data["$unit"]['confirmed'] && $data["$unit"]['status']) ? $_ARRAYLANG['TXT_CONFIRMED'] : $_ARRAYLANG['TXT_OCCUPIED']
                ));
                $this->_objTpl->hideBlock("link_head");
                $this->_objTpl->hideBlock("link_trail");
            } else {
                $this->_objTpl->setVariable(array(
                    "RESERVATION_ROWNAME"   => "available",
                    "TXT_RESERVATION_STATE" => $_ARRAYLANG['TXT_AVAILABLE']
                ));
                $this->_objTpl->touchBlock("link_head");
                $this->_objTpl->touchBlock("link_trail");
                $this->_objTpl->parse("link_head");
                $this->_objTpl->parse("link_trail");
            }
            $this->_objTpl->parse("row");

            $counter += $this->options['unit'];
            $unit++;
        }
    }


    /**
     * Shows the reserve form
     *
     * @global $_ARRAYLANG
     */
    function reserve()
    {
        global $_ARRAYLANG;

        $day = isset($_GET['day']) ? $_GET['day'] : '';
        $unit = isset($_GET['unit']) ? $_GET['unit'] : 0;

        if (empty($day) || empty($unit)) {
            CSRF::header("Location: index.php?section=reservation");
        }

        $outDay = strftime("%A, %d.%m.%Y", mktime(1, 1, 1, substr($day, 5, 2), substr($day, 8, 2), substr($day, 0, 4)));
        $outTime = strftime("%H:%M", $this->options['framestart'] + ($unit-1) * $this->options['unit']);

        $this->_objTpl->setVariable(array(
            "TXT_NAME"      => $_ARRAYLANG['TXT_NAME'],
            "TXT_EMAIL"     => $_ARRAYLANG['TXT_EMAIL'],
            "TXT_PHONE"     => $_ARRAYLANG['TXT_PHONE'],
            "TXT_COMMENTS"  => $_ARRAYLANG['TXT_COMMENTS'],
            "TXT_RESERVE"   => $_ARRAYLANG['TXT_RESERVE'],
            "TXT_CANCEL"    => $_ARRAYLANG['TXT_CANCEL'],
            "TXT_ERROR"     => $_ARRAYLANG['TXT_ERROR'],
            "RESERVATION_DATE"  => $outDay,
            "RESERVATION_TIME"  => $outTime,
            "RESERVATION_DAY"   => $day,
            "RESERVATION_UNIT"  => $unit
        ));

    }


    function save()
    {
        global $objDatabase, $_ARRAYLANG, $_CONFIG;

        $day = isset($_GET['day']) ? $_GET['day'] : '';
        $unit = isset($_GET['unit']) ? $_GET['unit'] : 0;

//        $objDatabase->debug = true;

        $query = "SELECT unit FROM ".DBPREFIX."module_reservation
                  WHERE day = '".$day."' AND unit = '".$unit."'";

        $objResult = $objDatabase->Execute($query);
        if ($objResult->RecordCount() > 0) {
            // Already occupied
            $this->_objTpl->setVariable("TXT_ERROR", $_ARRAYLANG['TXT_ALREADY_OCCUPIED']);
            $this->_objTpl->parse("error");
            return;
        } else {
            // Still free. check validity
            $chkTime = $this->options['framestart'] + ($unit * $this->options['unit']);
            if ($chkTime <= $this->options['frameend']) {
                // Valid
                $status = 0;
                $confirmed = ($this->options['confirmation']) ? 0 : 1;
                $name = contrexx_addslashes($_POST['name']);
                $email = contrexx_addslashes($_POST['email']);
                $phone = contrexx_addslashes($_POST['phone']);
                $comments = contrexx_addslashes($_POST['comments']);

                $day = contrexx_addslashes($_POST['day']);
                $unit = contrexx_addslashes($_POST['unit']);

                $time = time();
                $hash = md5(rand(0, 223232) . $time);

                $query = "INSERT INTO ".DBPREFIX."module_reservation
                         (`status`, `confirmed`, `day`, `unit`, `name`, `email`, `phone`,
                          `comments`, `lang_id`, `time`, `hash`) VALUES
                         ('".$status."', '".$confirmed."', '".$day."', '".$unit."', '".$name."', '".$email."', '".$phone."',
                          '".$comments."', '".$this->langId."', '".$time."', '".$hash."')";
                if ($objDatabase->Execute($query)) {
                // sucessfull. Send mail now

                $insertId = $objDatabase->Insert_ID();
                $url = "http://".$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']."?section=reservation&id=".$insertId."&act=confirm&hash=".$hash;

                $mailtext = str_replace("<URL>", $url, $this->options['mailtext']);

                if (@include_once ASCMS_LIBRARY_PATH.'/phpmailer/class.phpmailer.php') {
                        $objMail = new phpmailer();

                        if ($_CONFIG['coreSmtpServer'] > 0 && @include_once ASCMS_CORE_PATH.'/SmtpSettings.class.php') {
                            if (($arrSmtp = SmtpSettings::getSmtpAccount($_CONFIG['coreSmtpServer'])) !== false) {
                                $objMail->IsSMTP();
                                $objMail->Host = $arrSmtp['hostname'];
                                $objMail->Port = $arrSmtp['port'];
                                $objMail->SMTPAuth = true;
                                $objMail->Username = $arrSmtp['username'];
                                $objMail->Password = $arrSmtp['password'];
                            }
                        }

                        $objMail->CharSet = CONTREXX_CHARSET;
                        $objMail->From = $_CONFIG['coreAdminEmail'];
                        $objMail->FromName = $_CONFIG['coreAdminName'];
                        $objMail->AddReplyTo($_CONFIG['coreAdminEmail']);
                        $objMail->Subject = "Reservation";
                        $objMail->IsHTML(false);
                        $objMail->Body = $mailtext;
                        $objMail->AddAddress($email);
                        $objMail->Send();
                    }

                   $this->_objTpl->setVariable("TXT_SUCCEDED", $_ARRAYLANG['TXT_SUCCEDED']);
                   $this->_objTpl->parse("successful");
               } else {
                   echo $objDatabase->ErrorMsg();
                   $this->_objTpl->setVariable("TXT_ERROR", $_ARRAYLANG['TXT_ERROR_RESERVATION']);
               }

           } else {
               // Invalid
               CSRF::header("Location: index.php?section=recommend");
           }
        }

        $_GET['yearID'] = substr($day, 0, 4);
        $_GET['monthID'] = substr($day, 5, 2);
        $_GET['dayID'] = substr($day, 8, 2);
    }



    function confirm()
    {
        global $objDatabase, $_ARRAYLANG;

        if (!isset($_GET['hash'])) {
            $_GET['hash'] = '';
        }
        
        $id = contrexx_addslashes($_GET['id']);

        $query = "SELECT hash, confirmed, day FROM ".DBPREFIX."module_reservation
                  WHERE id = '".$id."'";
        $objResult = $objDatabase->Execute($query);

        if ($objResult) {
            if ($objResult->fields['hash'] == $_GET['hash']) {
                $query = "UPDATE ".DBPREFIX."module_reservation
                          SET status = '1' WHERE id = '".$id."'";
                $objDatabase->Execute($query);

                if ($objResult->fields['confirmed']) {
                    $this->_objTpl->setVariable("TXT_SUCCEDED", $_ARRAYLANG['TXT_FINAL_RESERVATION_SUCCEEDED']);
                } else {
                    $this->_objTpl->setVariable("TXT_SUCCEDED", $_ARRAYLANG['TXT_NEED_TO_CONFIRM']);
                }
                $this->_objTpl->parse("successful");

            } else {
                $this->_objTpl->setVariable("TXT_ERROR", $_ARRAYLANG['TXT_WRONG_HASH']);
                $this->_objTpl->parse("error");
            }
        }


        $day = $objResult->fields['day'];

        $_GET['yearID'] = substr($day, 0, 4);
        $_GET['monthID'] = substr($day, 5, 2);
        $_GET['dayID'] = substr($day, 8, 2);
    }
}

?>
