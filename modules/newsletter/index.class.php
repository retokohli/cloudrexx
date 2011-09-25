<?php

/**
 * Newsletter Modul
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author Comvation Development Team <info@comvation.com>
 * @access public
 * @version 1.1.0
 * @package     contrexx
 * @subpackage  module_newsletter
 * @todo        Edit PHP DocBlocks!
 */
/**
 * @ignore
 */
require_once ASCMS_MODULE_PATH.'/newsletter/lib/NewsletterLib.class.php';

/**
 * Newsletter Modul
 *
 * frontend newsletter class
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author Comvation Development Team <info@comvation.com>
 * @access public
 * @version 1.1.0
 * @package     contrexx
 * @subpackage  module_newsletter
 * @todo        Edit PHP DocBlocks!
 */
class newsletter extends NewsletterLib
{
    public $_objTpl;
    public $months = array();

    /**
     * Constructor
     * @param  string  $pageContent
     */
    function __construct($pageContent)
    {
        global $_ARRAYLANG;
        $this->pageContent = $pageContent;
        $this->_objTpl = new HTML_Template_Sigma('.');
        CSRF::add_placeholder($this->_objTpl);
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);
        $months = explode(',', $_ARRAYLANG['TXT_NEWSLETTER_MONTHS_ARRAY']);
        $i=0;
        foreach ($months as $month) {
            $this->months[++$i] = $month;
        }
    }

    function getPage()
    {
        if (!isset($_REQUEST['cmd'])) {
            $_REQUEST['cmd'] = '';
        }

        switch($_REQUEST['cmd']) {
            case 'profile':
                $this->_profile();
                break;
            case 'unsubscribe':
                $this->_unsubscribe();
                break;
             case 'subscribe':
                $this->_profile();
                break;
            case 'confirm':
                $this->_confirm();
                break;
			case 'displayInBrowser':
                $this->displayInBrowser();
                break;
            default:
                $this->_profile();
                break;
        }
        return $this->_objTpl->get();
    }


    function _confirm()
    {
        global $objDatabase, $_ARRAYLANG, $_CONFIG;
        $this->_objTpl->setTemplate($this->pageContent, true, true);

        $query         = "SELECT id FROM ".DBPREFIX."module_newsletter_user where status=0 and email='".contrexx_addslashes($_GET['email'])."'";
        $objResult     = $objDatabase->Execute($query);
        $count         = $objResult->RecordCount();
        $userId        = $objResult->fields['id'];

        if($count == 1){
            $objResult     = $objDatabase->Execute("UPDATE ".DBPREFIX."module_newsletter_user SET status=1 where email='".contrexx_addslashes($_GET['email'])."'");
            if ($objResult !== false) {
                $this->_objTpl->setVariable("NEWSLETTER_MESSAGE", $_ARRAYLANG['TXT_NEWSLETTER_CONFIRMATION_SUCCESSFUL']);

                //send notification
                $this->_sendNotificationEmail(1, $userId);

                //send mail
                $query = "SELECT id, sex, salutation, firstname, lastname, email, code FROM ".DBPREFIX."module_newsletter_user WHERE email='".contrexx_addslashes($_GET['email'])."'";
                $objResult = $objDatabase->Execute($query);

                if ($objResult !== false) {
                    $userFirstname    = $objResult->fields['firstname'];
                    $userLastname    = $objResult->fields['lastname'];
                    $userTitle        = $objResult->fields['title'];
                    $userSex = $objResult->fields['sex'];

                    $arrRecipientTitles = &$this->_getRecipientTitles();
                    $userTitle = $arrRecipientTitles[$userTitle];


                    switch($userSex){
                        case "m":
                            $userSex = $_ARRAYLANG['TXT_NEWSLETTER_MALE'];
                            break;

                        case "f":
                            $userSex = $_ARRAYLANG['TXT_NEWSLETTER_FEMALE'];
                            break;

                        default:
                            $userSex = '';
                            break;
                    }

                    $query_conf         = "SELECT setvalue FROM ".DBPREFIX."module_newsletter_settings WHERE setid=1";
                    $objResult_conf     = $objDatabase->Execute($query_conf);
                    if ($objResult_conf !== false) {
                        $value_sender_emailDEF     = $objResult_conf->fields['setvalue'];
                    }

                    $query_conf         = "SELECT setvalue FROM ".DBPREFIX."module_newsletter_settings WHERE setid=2";
                    $objResult_conf     = $objDatabase->Execute($query_conf);
                    if ($objResult_conf !== false) {
                        $value_sender_nameDEF     = $objResult_conf->fields['setvalue'];
                    }

                    $query_conf         = "SELECT setvalue FROM ".DBPREFIX."module_newsletter_settings WHERE setid=3";
                    $objResult_conf     = $objDatabase->Execute($query_conf);
                    if ($objResult_conf !== false) {
                        $value_reply_mailDEF     = $objResult_conf->fields['setvalue'];
                    }

                    $query_content         = "SELECT title, content FROM ".DBPREFIX."module_newsletter_confirm_mail WHERE id='2'";
                    $objResult_content      = $objDatabase->Execute($query_content );
                    if ($objResult_content !== false) {
                        $subject     = $objResult_content->fields['title'];
                        $content     = $objResult_content->fields['content'];
                    }

                    require_once ASCMS_LIBRARY_PATH . '/phpmailer/class.phpmailer.php';

                    $url            = $_SERVER['SERVER_NAME'];
                    $now             = date(ASCMS_DATE_FORMAT);



                    //replase placeholder
                    $array_1 = array('[[sex]]', '[[title]]', '[[firstname]]', '[[lastname]]', '[[url]]', '[[date]]');
                    $array_2 = array($userSex, $userTitle, $userFirstname, $userLastname, $url, $now);

                    $mailTitle = str_replace($array_1, $array_2, $subject);
                    $mailContent = str_replace($array_1, $array_2, $content);


                    $mail = new phpmailer();

                    if ($_CONFIG['coreSmtpServer'] > 0 && @include_once ASCMS_CORE_PATH.'/SmtpSettings.class.php') {
                        if (($arrSmtp = SmtpSettings::getSmtpAccount($_CONFIG['coreSmtpServer'])) !== false) {
                            $mail->IsSMTP();
                            $mail->Host = $arrSmtp['hostname'];
                            $mail->Port = $arrSmtp['port'];
                            $mail->SMTPAuth = true;
                            $mail->Username = $arrSmtp['username'];
                            $mail->Password = $arrSmtp['password'];
                        }
                    }

                    $mail->CharSet = CONTREXX_CHARSET;
                    $mail->From             = $value_sender_emailDEF;
                    $mail->FromName         = $value_sender_nameDEF;
                    $mail->AddReplyTo($value_reply_mailDEF);
                    $mail->Subject             = $mailTitle;
                    $mail->Priority         = 3;
                    $mail->IsHTML(false);
                    $mail->Body             = $mailContent;
                    $mail->AddAddress($_GET['email']);
                    $mail->Send();

                }
            }
        }else{
            $this->_objTpl->setVariable("NEWSLETTER_MESSAGE", '<font color="red">'.$_ARRAYLANG['TXT_NOT_VALID_EMAIL'].'</font>');
        }
    }

    /**
     * Create and select the date dropdowns for choosing the birthday
     *
     * @param (array|string) $birthday
     */
    function _createDatesDropdown($birthday = '')
    {
        if(!empty($birthday)){
            $birthday = (is_array($birthday)) ? $birthday : explode('-', $birthday);
            $day = !empty($birthday[0]) ? $birthday[0] : '01';
            $month = !empty($birthday[1]) ? $birthday[1] : '01';
            $year = !empty($birthday[2]) ? $birthday[2] : date("Y");
        }else{
            $day     = '01';
            $month     = '01';
            $year     = date("Y");
        }

        for($i=1;$i<=31;$i++){
            $selected = ($day == str_pad($i,2,'0',STR_PAD_LEFT)) ? 'selected="selected"' : '' ;
            $this->_objTpl->setVariable(array(
                'USERS_BIRTHDAY_DAY'        => str_pad($i,2,'0', STR_PAD_LEFT),
                'USERS_BIRTHDAY_DAY_NAME'    => $i,
                'SELECTED_DAY'                => $selected
            ));
            if($this->_objTpl->blockExists('birthday_day')){
                $this->_objTpl->parse('birthday_day');
            }
        }

        for($i=1;$i<=12;$i++){
            $selected = ($month == str_pad($i,2,'0',STR_PAD_LEFT)) ? 'selected="selected"' : '' ;
            $this->_objTpl->setVariable(array(
                'USERS_BIRTHDAY_MONTH'        => str_pad($i, 2, '0', STR_PAD_LEFT),
                'USERS_BIRTHDAY_MONTH_NAME'    => $this->months[$i],
                'SELECTED_MONTH'            => $selected
            ));
            if($this->_objTpl->blockExists('birthday_month')){
                $this->_objTpl->parse('birthday_month');
            }
        }

        for($i=date("Y");$i>=1900;$i--){
            $selected = ($year == $i) ? 'selected="selected"' : '' ;
            $this->_objTpl->setVariable(array(
                'USERS_BIRTHDAY_YEAR'         => $i,
                'SELECTED_YEAR'                => $selected
            ));
            if($this->_objTpl->blockExists('birthday_year')){
                $this->_objTpl->parse('birthday_year');
            }
        }
    }

    function _unsubscribe()
    {
        global $objDatabase, $_ARRAYLANG;

        $this->_objTpl->setTemplate($this->pageContent);
        $message = '';

        if (($objUser = $objDatabase->SelectLimit("SELECT id FROM ".DBPREFIX."module_newsletter_user WHERE code='".contrexx_addslashes($_REQUEST['code'])."' AND email='".urldecode(contrexx_addslashes($_REQUEST['mail']))."' AND status='1'", 1)) && $objUser->RecordCount() == 1) {
            $objSystem = $objDatabase->Execute("SELECT `setname`, `setvalue` FROM `".DBPREFIX."module_newsletter_settings`");
            if ($objSystem !== false) {
                while (!$objSystem->EOF) {
                    $arrSystem[$objSystem->fields['setname']] = $objSystem->fields['setvalue'];
                    $objSystem->MoveNext();
                }
            }

            if ($arrSystem['defUnsubscribe'] == 1) {
                //delete
                //send notification before trying to delete the record
                $this->_sendNotificationEmail(2, $objUser->fields['id']);
                if ($objDatabase->Execute("DELETE FROM ".DBPREFIX."module_newsletter_rel_user_cat WHERE user=".$objUser->fields['id']) && $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_newsletter_user WHERE id=".$objUser->fields['id'])) {
                    $message = $_ARRAYLANG['TXT_EMAIL_SUCCESSFULLY_DELETED'];
                } else {
                    $message = $_ARRAYLANG['TXT_NEWSLETTER_FAILED_REMOVING_FROM_SYSTEM'];
                }
            } else {
                //deactivate
                if ($objDatabase->Execute("UPDATE ".DBPREFIX."module_newsletter_user SET status='0' WHERE id='".$objUser->fields['id']."'")) {
                    //send notification
                    $this->_sendNotificationEmail(2, $objUser->fields['id']);
                    $message = $_ARRAYLANG['TXT_EMAIL_SUCCESSFULLY_DELETED'];
                } else {
                    $message = $_ARRAYLANG['TXT_NEWSLETTER_FAILED_REMOVING_FROM_SYSTEM'];
                }
            }
        } else {
            $message = '<font color="red">'.$_ARRAYLANG['TXT_AUTHENTICATION_FAILED'].'</font>';
        }

        $this->_objTpl->setVariable("NEWSLETTER_MESSAGE", $message);
    }

    function _profile()
    {
        global $_ARRAYLANG, $objDatabase;

        $this->_objTpl->setTemplate($this->pageContent);

        $showForm = true;
        $arrStatusMessage = array('ok' => array(), 'error' => array());

        $recipientId = 0;
        $recipientEmail = '';
        $recipientUri = '';
        $recipientSex = '';
        $recipientSalutation = 0;
        $recipientTitle = '';
        $recipientPosition = '';
        $recipientIndustrySector = '';
        $recipientPhoneMobile = '';
        $recipientPhonePrivate = '';
        $recipientFax = '';
        $recipientNotes = '';
        $recipientLastname = '';
        $recipientFirstname = '';
        $recipientCompany = '';
        $recipientAddress = '';
        $recipientZip = '';
        $recipientCity = '';
        $recipientCountry = '';
        $recipientPhoneOffice = '';
        $recipientBirthday = '';
        $recipientLanguage = '';
        $recipientStatus = 0;
        $requestedMail = isset($_REQUEST['mail']) ? $_REQUEST['mail'] : '';
        $arrAssociatedLists = array();
        $arrPreAssociatedInactiveLists = array();
        $code = isset($_REQUEST['code']) ? contrexx_addslashes($_REQUEST['code']) : '';

        if (!empty($code) && !empty($requestedMail)) {
            $objRecipient = $objDatabase->SelectLimit("SELECT id FROM ".DBPREFIX."module_newsletter_user WHERE status=1 AND code='".$code."' AND email='".contrexx_addslashes(urldecode($requestedMail))."'", 1);
            if ($objRecipient && $objRecipient->RecordCount() == 1) {
                $recipientId = $objRecipient->fields['id'];
            }
        }

        if (isset($_POST['recipient_save'])) {
            if (isset($_POST['email'])) {
                $recipientEmail = $_POST['email'];
            }
            if (isset($_POST['uri'])) {
                $recipientUri = $_POST['uri'];
            }
            if (isset($_POST['sex'])) {
                $recipientSex = in_array($_POST['sex'], array('f', 'm')) ? $_POST['sex'] : '';
            }
            if (isset($_POST['salutation'])) {
                $arrRecipientTitles = $this->_getRecipientTitles();
                $recipientSalutation = in_array($_POST['salutation'], array_keys($arrRecipientTitles)) ? intval($_POST['salutation']) : 0;
            }
            if (isset($_POST['title'])) {
                $recipientTitle = $_POST['title'];
            }
            if (isset($_POST['lastname'])) {
                $recipientLastname = $_POST['lastname'];
            }
            if (isset($_POST['firstname'])) {
                $recipientFirstname = $_POST['firstname'];
            }
            if (isset($_POST['position'])) {
                $recipientPosition = $_POST['position'];
            }
            if (isset($_POST['company'])) {
                $recipientCompany = $_POST['company'];
            }
            if (isset($_POST['industry_sector'])) {
                $recipientIndustrySector = $_POST['industry_sector'];
            }
            if (isset($_POST['address'])) {
                $recipientAddress = $_POST['address'];
            }
            if (isset($_POST['zip'])) {
                $recipientZip = $_POST['zip'];
            }
            if (isset($_POST['city'])) {
                $recipientCity = $_POST['city'];
            }
            if (isset($_POST['newsletter_country_id'])) {
                $recipientCountry = $_POST['newsletter_country_id'];
            }
            if (isset($_POST['phone_office'])) {
                $recipientPhoneOffice = $_POST['phone_office'];
            }
            if (isset($_POST['phone_private'])) {
                $recipientPhonePrivate = $_POST['phone_private'];
            }
            if (isset($_POST['phone_mobile'])) {
                $recipientPhoneMobile = $_POST['phone_mobile'];
            }
            if (isset($_POST['fax'])) {
                $recipientFax = $_POST['fax'];
            }
            if (isset($_POST['day']) && isset($_POST['month']) && isset($_POST['year'])) {
                $recipientBirthday = str_pad(intval($_POST['day']),2,'0',STR_PAD_LEFT).'-'.str_pad(intval($_POST['month']),2,'0',STR_PAD_LEFT).'-'.intval($_POST['year']);
            }
            if (isset($_POST['language'])) {
                $recipientLanguage = $_POST['language'];
            }
            if (isset($_POST['notes'])) {
                $recipientNotes = $_POST['notes'];
            }

            if (isset($_POST['list'])) {
                foreach ($_POST['list'] as $listId => $status) {
                    if (intval($status) == 1) {
                        array_push($arrAssociatedLists, intval($listId));
                    }
                }
            }

            $arrPreAssociatedInactiveLists = $this->_getAssociatedListsOfRecipient($recipientId, false);
            $arrAssociatedInactiveLists = array_intersect($arrPreAssociatedInactiveLists, $arrAssociatedLists);

            $objValidator = new FWValidator();
            if ($objValidator->isEmail($recipientEmail)) {
                if ($this->_isUniqueRecipientEmail($recipientEmail, $recipientId)) {
                    if (!empty($arrAssociatedInactiveLists) || !empty($arrAssociatedLists) && ($objList = $objDatabase->SelectLimit('SELECT id FROM '.DBPREFIX.'module_newsletter_category WHERE status=1 AND (id='.implode(' OR id=', $arrAssociatedLists).')' , 1)) && $objList->RecordCount() > 0) {
                        if ($recipientId > 0) {
                            if ($this->_updateRecipient($recipientId, $recipientEmail, $recipientUri, $recipientSex, $recipientSalutation, $recipientTitle, $recipientLastname, $recipientFirstname, $recipientPosition, $recipientCompany, $recipientIndustrySector, $recipientAddress, $recipientZip, $recipientCity, $recipientCountry, $recipientPhoneOffice, $recipientPhonePrivate, $recipientPhoneMobile, $recipientFax, $recipientNotes, $recipientBirthday, $recipientStatus, $arrAssociatedLists, $recipientLanguage)) {
                                array_push($arrStatusMessage['ok'], $_ARRAYLANG['TXT_NEWSLETTER_YOUR_DATE_SUCCESSFULLY_UPDATED']);
                                $showForm = false;
                            } else {
                                array_push($arrStatusMessage['error'], $_ARRAYLANG['TXT_NEWSLETTER_FAILED_UPDATE_YOUR_DATA']);
                            }
                        } else {
                            if ($this->_addRecipient($recipientEmail, $recipientUri, $recipientSex, $recipientSalutation, $recipientTitle, $recipientLastname, $recipientFirstname, $recipientPosition, $recipientCompany, $recipientIndustrySector, $recipientAddress, $recipientZip, $recipientCity, $recipientCountry, $recipientPhoneOffice, $recipientPhonePrivate, $recipientPhoneMobile, $recipientFax, $recipientNotes, $recipientBirthday, $recipientStatus, $arrAssociatedLists, $recipientLanguage)) {
                                if ($this->_sendAuthorizeEmail($recipientEmail, $recipientSex, $recipientSalutation, $recipientFirstname, $recipientLastname)) {
                                    array_push($arrStatusMessage['ok'], $_ARRAYLANG['TXT_NEWSLETTER_SUBSCRIBE_OK']);
                                    $showForm = false;
                                } else {
                                    $objDatabase->Execute("DELETE tblU, tblR FROM ".DBPREFIX."module_newsletter_user AS tblU, ".DBPREFIX."module_newsletter_rel_user_cat AS tblR WHERE tblU.email='".contrexx_addslashes($recipientEmail)."' AND tblR.user = tblU.id");
                                    array_push($arrStatusMessage['error'], $_ARRAYLANG['TXT_NEWSLETTER_SUBSCRIPTION_CANCELED_BY_EMAIL']);
                                }
                            } else {
                                array_push($arrStatusMessage['error'], $_ARRAYLANG['TXT_NEWSLETTER_FAILED_ADDING_YOU']);
                            }
                        }
                    } else {
                        array_push($arrStatusMessage['error'], $_ARRAYLANG['TXT_NEWSLETTER_MUST_SELECT_LIST']);
                    }
                } else {
                    array_push($arrStatusMessage['error'], $_ARRAYLANG['TXT_NEWSLETTER_SUBSCRIBER_ALREADY_INSERTED']);
                }
            } else {
                array_push($arrStatusMessage['error'], $_ARRAYLANG['TXT_NOT_VALID_EMAIL']);
            }
        } elseif ($recipientId > 0) {
            $objRecipient = $objDatabase->SelectLimit("SELECT uri, sex, salutation, title, lastname, firstname, position, company, industry_sector, address, zip, city, country_id, phone_office, phone_private, phone_mobile, fax, notes, birthday, status, language FROM ".DBPREFIX."module_newsletter_user WHERE id=".$recipientId, 1);
            if ($objRecipient !== false && $objRecipient->RecordCount() == 1) {
                $recipientEmail = urldecode($_REQUEST['mail']);
                $recipientUri = $objRecipient->fields['uri'];
                $recipientSex = $objRecipient->fields['sex'];
                $recipientSalutation = $objRecipient->fields['salutation'];
                $recipientTitle = $objRecipient->fields['title'];
                $recipientLastname = $objRecipient->fields['lastname'];
                $recipientFirstname = $objRecipient->fields['firstname'];
                $recipientPosition = $objRecipient->fields['position'];
                $recipientCompany = $objRecipient->fields['company'];
                $recipientIndustrySector = $objRecipient->fields['industry_sector'];
                $recipientAddress = $objRecipient->fields['address'];
                $recipientZip = $objRecipient->fields['zip'];
                $recipientCity = $objRecipient->fields['city'];
                $recipientCountry = $objRecipient->fields['country_id'];
                $recipientPhoneOffice = $objRecipient->fields['phone_office'];
                $recipientPhonePrivate = $objRecipient->fields['phone_private'];
                $recipientPhoneMobile = $objRecipient->fields['phone_mobile'];
                $recipientFax = $objRecipient->fields['fax'];
                $recipientBirthday = $objRecipient->fields['birthday'];
                $recipientLanguage = $objRecipient->fields['language'];
                $recipientNotes = $objRecipient->fields['notes'];

                $arrAssociatedLists = $this->_getAssociatedListsOfRecipient($recipientId, false);
                $arrPreAssociatedInactiveLists = $this->_getAssociatedListsOfRecipient($recipientId, false, true);
            } else {
                array_push($arrStatusMessage['error'], $_ARRAYLANG['TXT_NEWSLETTER_AUTHENTICATION_FAILED']);
                $showForm = false;
            }
        }

        $this->_createDatesDropdown($recipientBirthday);

        // only show newsletter-lists that are visible for new users (not yet registered ones)
        $excludeDisabledLists = $recipientId == 0;
        $arrLists = self::getLists($excludeDisabledLists);
        if (count($arrLists)) {
            if (count($arrLists) == 1) {
                $this->_objTpl->replaceBlock('newsletter_lists', '<input type="hidden" name="list['.key($arrLists).']" value="1" />');
                $this->_objTpl->touchBlock('newsletter_lists');
            } else {
                foreach ($arrLists as $listId => $arrList) {
                    if ($arrList['status'] || in_array($listId, $arrPreAssociatedInactiveLists)) {
                        $this->_objTpl->setVariable(array(
                            'NEWSLETTER_LIST_ID'        => $listId,
                            'NEWSLETTER_LIST_NAME'      => contrexx_raw2xhtml($arrList['name']),
                            'NEWSLETTER_LIST_SELECTED'  => in_array($listId, $arrAssociatedLists) ? 'checked="checked"' : ''
                        ));
                        $this->_objTpl->parse('newsletter_lists');
                    }
                }
            }
        } else {
            $this->_objTpl->hideBlock('newsletter_lists');
        }

        if (count($arrStatusMessage['ok']) > 0) {
            $this->_objTpl->setVariable('NEWSLETTER_OK_MESSAGE', implode('<br />', $arrStatusMessage['ok']));
            $this->_objTpl->parse('newsletter_ok_message');
        } else {
            $this->_objTpl->hideBlock('newsletter_ok_message');
        }
        if (count($arrStatusMessage['error']) > 0) {
            $this->_objTpl->setVariable('NEWSLETTER_ERROR_MESSAGE', implode('<br />', $arrStatusMessage['error']));
            $this->_objTpl->parse('newsletter_error_message');
        } else {
            $this->_objTpl->hideBlock('newsletter_error_message');
        }

        $languages = '<select name="language" class="selectLanguage" id="language" >';
        $objLanguage = $objDatabase->Execute("SELECT id, name FROM ".DBPREFIX."languages WHERE frontend = 1 ORDER BY name");
        $languages .= '<option value="0">'.$_ARRAYLANG['TXT_NEWSLETTER_LANGUAGE_PLEASE_CHOSE'].'</option>';
        while (!$objLanguage->EOF) {
            $selected = ($objLanguage->fields['id'] == $recipientLanguage) ? 'selected' : '';
            $languages .= '<option value="'.$objLanguage->fields['id'].'" '.$selected.'>'.contrexx_raw2xhtml($objLanguage->fields['name']).'</option>';
            $objLanguage->MoveNext();
        }
        $languages .= '</select>';

        if ($showForm) {
            $this->_objTpl->setVariable(array(
                'NEWSLETTER_PROFILE_MAIL'    => urlencode($requestedMail),
                'NEWSLETTER_EMAIL'        => htmlentities($recipientEmail, ENT_QUOTES, CONTREXX_CHARSET),
                'NEWSLETTER_USER_CODE'    => $code,
                'NEWSLETTER_URI'          => htmlentities($recipientUri, ENT_QUOTES, CONTREXX_CHARSET),
                'NEWSLETTER_SEX_F'        => $recipientSex == 'f' ? 'checked="checked"' : '',
                 'NEWSLETTER_SEX_M'        => $recipientSex == 'm' ? 'checked="checked"' : '',
                'NEWSLETTER_SALUTATION'        => $this->_getRecipientTitleMenu($recipientTitle, 'name="salutation" size="1"'),
                'NEWSLETTER_TITLE'    => htmlentities($recipientTitle, ENT_QUOTES, CONTREXX_CHARSET),
                'NEWSLETTER_LASTNAME'    => htmlentities($recipientLastname, ENT_QUOTES, CONTREXX_CHARSET),
                'NEWSLETTER_FIRSTNAME'    => htmlentities($recipientFirstname, ENT_QUOTES, CONTREXX_CHARSET),
                'NEWSLETTER_POSITION'    => htmlentities($recipientPosition, ENT_QUOTES, CONTREXX_CHARSET),
                'NEWSLETTER_COMPANY'    => htmlentities($recipientCompany, ENT_QUOTES, CONTREXX_CHARSET),
                'NEWSLETTER_INDUSTRY_SECTOR'    => htmlentities($recipientIndustrySector, ENT_QUOTES, CONTREXX_CHARSET),
                'NEWSLETTER_ADDRESS'        => htmlentities($recipientAddress, ENT_QUOTES, CONTREXX_CHARSET),
                'NEWSLETTER_ZIP'        => htmlentities($recipientZip, ENT_QUOTES, CONTREXX_CHARSET),
                'NEWSLETTER_CITY'        => htmlentities($recipientCity, ENT_QUOTES, CONTREXX_CHARSET),
                'NEWSLETTER_COUNTRY'    => $this->getCountryMenu($recipientCountry),
                'NEWSLETTER_PHONE'        => htmlentities($recipientPhoneOffice, ENT_QUOTES, CONTREXX_CHARSET),
                'NEWSLETTER_PHONE_PRIVATE'        => htmlentities($recipientPhonePrivate, ENT_QUOTES, CONTREXX_CHARSET),
                'NEWSLETTER_PHONE_MOBILE'        => htmlentities($recipientPhoneMobile, ENT_QUOTES, CONTREXX_CHARSET),
                'NEWSLETTER_FAX'        => htmlentities($recipientFax, ENT_QUOTES, CONTREXX_CHARSET),
                'NEWSLETTER_NOTES'        => htmlentities($recipientNotes, ENT_QUOTES, CONTREXX_CHARSET),
                'NEWSLETTER_LANGUAGE'     => $languages
            ));

            $this->_objTpl->setVariable(array(
                'TXT_NEWSLETTER_EMAIL_ADDRESS'    => $_ARRAYLANG['TXT_NEWSLETTER_EMAIL_ADDRESS'],
                'TXT_NEWSLETTER_URI'            => $_ARRAYLANG['TXT_NEWSLETTER_URI'],
                'TXT_NEWSLETTER_SALUTATION'     => $_ARRAYLANG['TXT_NEWSLETTER_SALUTATION'],
                'TXT_NEWSLETTER_SEX'            => $_ARRAYLANG['TXT_NEWSLETTER_SEX'],
                'TXT_NEWSLETTER_FEMALE'            => $_ARRAYLANG['TXT_NEWSLETTER_FEMALE'],
                'TXT_NEWSLETTER_MALE'            => $_ARRAYLANG['TXT_NEWSLETTER_MALE'],
                'TXT_NEWSLETTER_TITLE'        => $_ARRAYLANG['TXT_NEWSLETTER_TITLE'],
                'TXT_NEWSLETTER_LASTNAME'        => $_ARRAYLANG['TXT_NEWSLETTER_LASTNAME'],
                'TXT_NEWSLETTER_FIRSTNAME'        => $_ARRAYLANG['TXT_NEWSLETTER_FIRSTNAME'],
                'TXT_NEWSLETTER_POSITION'        => $_ARRAYLANG['TXT_NEWSLETTER_POSITION'],
                'TXT_NEWSLETTER_COMPANY'        => $_ARRAYLANG['TXT_NEWSLETTER_COMPANY'],
                'TXT_NEWSLETTER_INDUSTRY_SECTOR'        => $_ARRAYLANG['TXT_NEWSLETTER_INDUSTRY_SECTOR'],
                'TXT_NEWSLETTER_ADDRESS'            => $_ARRAYLANG['TXT_NEWSLETTER_ADDRESS'],
                'TXT_NEWSLETTER_ZIP'            => $_ARRAYLANG['TXT_NEWSLETTER_ZIP'],
                'TXT_NEWSLETTER_CITY'            => $_ARRAYLANG['TXT_NEWSLETTER_CITY'],
                'TXT_NEWSLETTER_COUNTRY'        => $_ARRAYLANG['TXT_NEWSLETTER_COUNTRY'],
                'TXT_NEWSLETTER_PHONE_PRIVATE'            => $_ARRAYLANG['TXT_NEWSLETTER_PHONE_PRIVATE'],
                'TXT_NEWSLETTER_PHONE_MOBILE'            => $_ARRAYLANG['TXT_NEWSLETTER_PHONE_MOBILE'],
                'TXT_NEWSLETTER_FAX'            => $_ARRAYLANG['TXT_NEWSLETTER_FAX'],
                'TXT_NEWSLETTER_PHONE'            => $_ARRAYLANG['TXT_NEWSLETTER_PHONE'],
                'TXT_NEWSLETTER_NOTES'            => $_ARRAYLANG['TXT_NEWSLETTER_NOTES'],
                'TXT_NEWSLETTER_BIRTHDAY'        => $_ARRAYLANG['TXT_NEWSLETTER_BIRTHDAY'],
                'TXT_NEWSLETTER_LISTS'             => $_ARRAYLANG['TXT_NEWSLETTER_LISTS'],
                'TXT_NEWSLETTER_SAVE'          => $_ARRAYLANG['TXT_NEWSLETTER_SAVE'],
                'TXT_NEWSLETTER_LANGUAGE'      => $_ARRAYLANG['TXT_NEWSLETTER_LANGUAGE']
            ));

            $this->_objTpl->parse('newsletterForm');
        } else {
            $this->_objTpl->hideBlock('newsletterForm');
        }
    }

    function _sendAuthorizeEmail($recipientEmail, $recipientSex, $recipientTitle, $recipientFirstname, $recipientLastname)
    {
        global $_CONFIG, $_ARRAYLANG, $objDatabase;

        if (!@include_once ASCMS_LIBRARY_PATH.'/phpmailer/class.phpmailer.php') {
            return false;
        }

        $arrRecipientTitles = &$this->_getRecipientTitles();
        $recipientTitleTxt = $arrRecipientTitles[$recipientTitle];

        switch ($recipientSex) {
             case 'm':
                 $recipientSexTxt = $_ARRAYLANG['TXT_NEWSLETTER_MALE'];
                 break;

             case 'f':
                 $recipientSexTxt = $_ARRAYLANG['TXT_NEWSLETTER_FEMALE'];
                 break;

             default:
                 $recipientSexTxt = '';
                 break;
         }

        if (!($objConfirmMail = $objDatabase->SelectLimit("SELECT title, content FROM ".DBPREFIX."module_newsletter_confirm_mail WHERE id='1'", 1)) || $objConfirmMail->RecordCount() == 0) {
            return false;
        }

        $arrParsedTxts = str_replace(
            array('[[sex]]', '[[title]]', '[[firstname]]', '[[lastname]]', '[[code]]', '[[url]]', '[[date]]'),
            array($recipientSexTxt, $recipientTitleTxt, $recipientFirstname, $recipientLastname, ASCMS_PROTOCOL.'://'.$_CONFIG['domainUrl'].CONTREXX_SCRIPT_PATH.'?section=newsletter&cmd=confirm&email='.$recipientEmail, $_CONFIG['domainUrl'], date(ASCMS_DATE_FORMAT)),
            array($objConfirmMail->fields['title'], $objConfirmMail->fields['content'])
        );

        $arrSettings = &$this->_getSettings();

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
        $objMail->From = $arrSettings['sender_mail']['setvalue'];
        $objMail->FromName = $arrSettings['sender_name']['setvalue'];
        $objMail->AddReplyTo($arrSettings['reply_mail']['setvalue']);
        $objMail->Subject = $arrParsedTxts[0];
        $objMail->Priority = 3;
        $objMail->IsHTML(false);
        $objMail->Body = $arrParsedTxts[1];
        $objMail->AddAddress($recipientEmail);
        if ($objMail->Send()) {
            return true;
        } else {
            return false;
        }
    }

    function _sendNotificationEmail($action, $recipientId)
    {
        global $_CONFIG, $_ARRAYLANG, $objDatabase;
        //action: 1 = subscribe | 2 = unsubscribe

        $objSettings = $objDatabase->Execute("SELECT `setname`, `setvalue` FROM `".DBPREFIX."module_newsletter_settings` WHERE `setname` = 'notificationSubscribe' OR  `setname` = 'notificationUnsubscribe' ");
        if ($objSettings !== false) {
            while (!$objSettings->EOF) {
                $arrSettings[$objSettings->fields['setname']] = $objSettings->fields['setvalue'];
                $objSettings->MoveNext();
            }
        }

        if (   ($arrSettings['notificationSubscribe'] == 1 && $action == 1)
            || ($arrSettings['notificationUnsubscribe'] == 1 && $action == 2)) {

            if (!@include_once ASCMS_LIBRARY_PATH.'/phpmailer/class.phpmailer.php') {
                return false;
            }
            $objRecipient = $objDatabase->SelectLimit("SELECT sex, salutation, lastname, firstname, email FROM ".DBPREFIX."module_newsletter_user WHERE id=".$recipientId, 1);
            if ($objRecipient !== false) {
                $arrRecipient['sex'] = $objRecipient->fields['sex'];
                $arrRecipient['title'] = $objRecipient->fields['title'];
                $arrRecipient['lastname'] = $objRecipient->fields['lastname'];
                $arrRecipient['firstname'] = $objRecipient->fields['firstname'];
                $arrRecipient['email'] = $objRecipient->fields['email'];
            }

            $objRecipientTitle = $objDatabase->SelectLimit("SELECT salutation FROM ".DBPREFIX."module_newsletter_user_title WHERE id=".$arrRecipient['title'], 1);
            if ($objRecipientTitle !== false) {
                $arrRecipientTitle = $objRecipientTitle->fields['title'];
            }

            $objNotificationMail = $objDatabase->SelectLimit("SELECT title, content, recipients FROM ".DBPREFIX."module_newsletter_confirm_mail WHERE id='3'", 1);

            if($action == 1) {
                $txtAction = $_ARRAYLANG['TXT_NEWSLETTER_NOTIFICATION_SUBSCRIBE'];
            } else {
                $txtAction = $_ARRAYLANG['TXT_NEWSLETTER_NOTIFICATION_UNSUBSCRIBE'];
            }

            $arrParsedTxts = str_replace(
                array('[[action]]', '[[url]]', '[[date]]', '[[sex]]', '[[title]]', '[[lastname]]', '[[firstname]]', '[[e-mail]]'),
                array($txtAction, $_CONFIG['domainUrl'], date(ASCMS_DATE_FORMAT), $arrRecipient['sex'], $arrRecipientTitle, $arrRecipient['lastname'], $arrRecipient['firstname'], $arrRecipient['email']),
                array($objNotificationMail->fields['title'], $objNotificationMail->fields['content'])
            );

            $arrRecipients = explode(',', $objNotificationMail->fields['recipients']);

            $arrSettings = &$this->_getSettings();

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
            $objMail->From = $arrSettings['sender_mail']['setvalue'];
            $objMail->FromName = $arrSettings['sender_name']['setvalue'];
            $objMail->AddReplyTo($arrSettings['reply_mail']['setvalue']);
            $objMail->Subject = $arrParsedTxts[0];
            $objMail->Priority = 3;
            $objMail->IsHTML(false);
            $objMail->Body = $arrParsedTxts[1];

            foreach ($arrRecipients as $key => $recipientEmail) {
                $objMail->AddAddress($recipientEmail);
            }

            if ($objMail->Send()) {
                return true;
            }
        }
// TODO: This used to return *nothing* when notifications were turned off.
// Probably true should be returned in this case instead.
// -- See the condition way above.
        return false;
    }



    function setBlock(&$code)
    {
        $html = $this->_getHTML();
        $code = str_replace("{NEWSLETTER_BLOCK}", $html, $code);
    }
    
    
        /**
     * displays newsletter contentn in browser
     *
     */
	function displayInBrowser() {
    	global $objDatabase, $_ARRAYLANG, $_CONFIG;
        
        $id    = !empty($_GET['id']) ? $_GET['id'] : "";
    	$email = !empty($_GET['email']) ? $_GET['email'] : "";
    	$code  = !empty($_GET['code']) ? $_GET['code'] : "";
    	
    	if (!$this->checkCode($email, $code)) {
    		header("location: index.php");
    		exit();
    	}
    	
    	//get newsletter content and template
		$query = "  SELECT
						newsletter.content,
						template.html	
					FROM
						".DBPREFIX."module_newsletter as newsletter
					LEFT JOIN
						".DBPREFIX."module_newsletter_template as template
					ON
						template.id = newsletter.template
					WHERE
						newsletter.id = '".contrexx_addslashes($id)."';";
		
        $objResult = $objDatabase->Execute($query);
		
        if ($objResult->RecordCount()) {
        	$html 	 = $objResult->fields['html'];
			$content = $objResult->fields['content'];
		} else {
			header("location: index.php");
    		exit();
		}
		
		if (empty($content)) {
			header("location: index.php");
    		exit();
		}
		
		
		//get user details
		$query = "select id, code, sex, email, uri, salutation, lastname, firstname, address, zip, city, phone_office, birthday, status, emaildate from ".DBPREFIX."module_newsletter_user where email = '".$email."';";
        $objResult = $objDatabase->Execute($query);

        if ($objResult->RecordCount()) {
            $code   	= $objResult->fields['code'];
			$lastname  	= $objResult->fields['lastname'];
            $firstname  = $objResult->fields['firstname'];
            $address  	= $objResult->fields['address'];
            $zip   		= $objResult->fields['zip'];
            $city   	= $objResult->fields['city'];
            $birthday  	= $objResult->fields['birthday'];
            $email   	= $objResult->fields['email'];
            $uri       	= $objResult->fields['uri'];
            $phoneOffice= $objResult->fields['phone_office'];
            $date  	 	= date("d.m.Y", $objResult->fields['emaildate']);

            switch($objResult->fields['sex']) {
                case 'm':
                    $sex = $_ARRAYLANG['TXT_NEWSLETTER_MALE'];
                    break;
                case 'f':
                    $sex = $_ARRAYLANG['TXT_NEWSLETTER_FEMALE'];
                    break;
                default:
                    $sex = '';
                    break;
             }

            $arrRecipientTitles = &$this->_getRecipientTitles();
            $title = $arrRecipientTitles[$objResult->fields['title']];

            //replace placeholders
            $array_1 = array(
                '[[email]]', '[[uri]]', '[[sex]]', '[[title]]', '[[lastname]]', '[[firstname]]',
                '[[address]]', '[[zip]]', '[[city]]', '[[phone_office]]', '[[birthday]]', '[[date]]',
                '[[unsubscribe]]', '[[profile_setup]]', '[[display_in_browser_url]]'
            );
            
            $array_2 = array(
                $email, $uri, $sex, $title, $lastname, $firstname,
                $address, $zip, $city, $phoneOffice, $birthday, $date,
                '<a href="index.php?section=newsletter&cmd=unsubscribe&code='.$code.'&mail='.$email.'">Abmelden</a>',
                '<a href="index.php?section=newsletter&cmd=profile&code='.$code.'&mail='.$email.'">Profil bearbeiten</a>',
                $_CONFIG['domainUrl'].'/index.php?section=newsletter&cmd=displayInBrowser&standalone=true&code='.$code.'&email='.$email.'&id='.$id
            );
            
            //in content and template
            $content = str_replace($array_1, $array_2, $content);
            $html 	 = str_replace($array_1, $array_2, $html);
        }
		
        //finally replace content placeholder in template
		$html = str_replace("[[content]]", $content, $html);
		
		//output
		die($html);
    }
    
    
    /**
     * checks if given code matches given email adress
     *
     * @param  string $email
     * @param  string $code
     * @return boolean
     */
    function checkCode($email, $code){
    	global $objDatabase;

        $query = "SELECT code FROM ".DBPREFIX."module_newsletter_user WHERE email = '".$email."';";
        $objResult = $objDatabase->Execute($query);

        if ($objResult !== false) {
			if($objResult->fields['code'] == $code) {
				return true;
			}
		}
		
		return false;
    }
    
    /**
     * track link: save feedback to database
     *
     * @return boolean
     */
    function trackLink() 
    {
        global $objDatabase;
        
        $recipientId = 0;
        $recipient = isset($_GET['r']) ? urldecode($_GET['r']) : '';
        $emailId = isset($_GET['n']) ? intval($_GET['n']) : 0;
        $linkId = isset($_GET['l']) ? intval($_GET['l']) : 0;
        $url = isset($_GET['s']) ? urldecode($_GET['s']) : '';
        
        // find out recipient type
        if (preg_match('/[0-9]+/', $recipient)) {
            $objUser = FWUser::getFWUserObject()->objUser->getUser(intval($recipient));
            if ($objUser !== false) {
                $recipientId = $objUser->getId();
                $recipientType = 'access';
            }
        }
        else {
            $objUser = $objDatabase->SelectLimit("SELECT `id` FROM ".DBPREFIX."module_newsletter_user WHERE email='".contrexx_raw2db($recipient)."'", 1);
            if ($objUser !== false && $objUser->RecordCount() == 1) {
                $recipientId = $objUser->fields['id'];
                $recipientType = 'newsletter';
            }
            /*$objUser = FWUser::getFWUserObject()->objUser->getUser($recipient);
            if ($objUser !== false) {
                $recipientId = $objUser->getId();
                $recipientType = 'newsletter';
            }*/
        }
        
        /*
        * Request must be redirected to the newsletter $linkId URL. If the $linkId 
        * can�t be looked up in the database (by what reason  so ever), then the request shall be 
        * redirected to the URL provided by the url-modificator s of the request
        */
        $objLink = $objDatabase->SelectLimit("SELECT `url` FROM ".DBPREFIX."module_newsletter_email_link WHERE id=".$linkId." AND email_id=".$emailId, 1);
        if ($objLink !== false && $objLink->RecordCount() == 1) {
            $url = $objLink->fields['url'];
        }     
        
        // we allow to collect stat even if we didn't find link above
        if (intval($recipientId)) {
            // save feedback for valid user
            $query = "INSERT IGNORE INTO ".DBPREFIX."module_newsletter_email_link_feedback
                (link_id, email_id, recipient_id, recipient_type) VALUES (
                ".$linkId.", ".$emailId.", ".$recipientId.", '".$recipientType."'
                )";
            $objDatabase->Execute($query);
        }
        
        if ($url) {
            CSRF::header('Location: '.$url);
            exit;
        }
    }

}

?>
