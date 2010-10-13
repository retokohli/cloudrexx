<?php
$_ARRAYLANG['TXT_NEWSLETTER_NOTIFY_ON_UNSUBSCRIBE'] = "Benachrichtigung bei Abmeldung";

/**
 * Newsletter
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @access      public
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_newsletter
 * @todo        Edit PHP DocBlocks!
 * @todo        make total mailrecipient count static in newsletter list (act=mails)
 *              (new count field)
 *              check if mail already sent when a user unsubscribes -> adjust count
 */

/**
 * @ignore
 */
require_once ASCMS_MODULE_PATH.'/newsletter/lib/NewsletterLib.class.php';

/**
 * Class newsletter
 *
 * Newsletter module class
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @access      public
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_newsletter
 */
class newsletter extends NewsletterLib
{
    public $_objTpl;
    public $_pageTitle;
    public $_strErrMessage = '';
    public $_strOkMessage = '';
    public $months = array();

    public $_arrMailFormat = array(
        'text'        => 'TXT_NEWSLETTER_ONLY_TEXT',
        'html'        => 'TXT_NEWSLETTER_HTML_UC',
        'html/text'    => 'TXT_NEWSLETTER_MULTIPART_TXT'
    );

    public $_stdMailFormat = 'text';

    public $_arrMailPriority = array(
        1    => 'TXT_NEWSLETTER_VERY_HIGH',
        2    => 'TXT_NEWSLETTER_HIGH',
        3    => 'TXT_NEWSLETTER_MEDIUM',
        4    => 'TXT_NEWSLETTER_LOW',
        5    => 'TXT_NEWSLETTER_VERY_LOW'
    );

    public $_stdMailPriority = 3;

    public $_attachmentPath = '/images/attach/';

    /**
     * PHP5 constructor
     * @global HTML_Template_Sigma
     * @global array $_ARRAYLANG
     */
    function __construct()
    {
        global $objTemplate, $_ARRAYLANG;

        $this->_objTpl = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/newsletter/template');
        CSRF::add_placeholder($this->_objTpl);
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);

        if (!isset($_REQUEST['standalone'])) {

            $objTemplate->setVariable("CONTENT_NAVIGATION", "
                                            <a href='index.php?cmd=newsletter'>".$_ARRAYLANG['TXT_NEWSLETTER_ADMINISTRATION']."</a>
                                            <a href='index.php?cmd=newsletter&amp;act=lists'>".$_ARRAYLANG['TXT_NEWSLETTER_LISTS']."</a>
                                            <a href='index.php?cmd=newsletter&amp;act=mails'>".$_ARRAYLANG['TXT_NEWSLETTER_EMAILS']."</a>
                                            <a href='index.php?cmd=newsletter&amp;act=templates'>".$_ARRAYLANG['TXT_NEWSLETTER_TEMPLATES']."</a>
                                            <a href='index.php?cmd=newsletter&amp;act=users'>".$_ARRAYLANG['TXT_NEWSLETTER_RECIPIENTS']."</a>
                                            <a href='index.php?cmd=newsletter&amp;act=news'>".$_ARRAYLANG['TXT_NEWSLETTER_NEWS']."</a>
                                            <a href='index.php?cmd=newsletter&amp;act=dispatch'>".$_ARRAYLANG['TXT_SETTINGS']."</a>");
        }
        $months = explode(',', $_ARRAYLANG['TXT_NEWSLETTER_MONTHS_ARRAY']);
        $i=0;
        foreach ($months as $month) {
            $this->months[++$i] = $month;
        }

    }

    /**
    * Set the backend page
    *
    * @access public
    * @global HTML_Template_Sigma
    * @global array $_ARRAYLANG
    */
    function getPage() {
        global $objTemplate, $_ARRAYLANG;

        if (!isset($_GET['act'])) {
            $_GET['act'] = '';
        }

        switch ($_GET['act']) {
            case "lists":
                $this->_lists();
                break;

            case "editlist":
                $this->_editList();
                break;
            case "flushList":
                $this->_flushList();
                $this->_lists();
                break;

            case "changeListStatus":
                $this->_changeListStatus();
                $this->_lists();
                break;

            case "deleteList":
                $this->_deleteList();
                $this->_lists();
                break;

            case "gethtml":
                $this->_getListHTML();
                break;

            case "mails":
                $this->_mails();
                break;

            case "deleteMail":
                $this->_deleteMail();
                break;

            case "copyMail":
                $this->_copyMail();
                break;

            case "editMail":
                $this->_editMail();
                break;

            case "showMail":
                $this->_showMail();
                break;

            case "sendMail":
                $this->_sendMailPage();
                break;

            case "send":
                $this->_sendMail();
                break;

            case "newsletter":
                $this->newsletterOverview();
                break;

            case "news":
                $this->_getNewsPage();
                break;

            case "users":
                $this->_users();
                break;

            case "config":
                $this->configOverview();
                break;
            case "system":
                $this->ConfigSystem();
                break;
            case "editusersort":
                $this->edituserSort();
                break;
            case "dispatch":
                $this->ConfigDispatch();
                break;
            case "confightml":
                $this->ConfigHTML();
                break;
            case "templates":
                $this->_templates();
                break;
            case "tpledit":
                $this->_editTemplate();
                break;

            case "tpldel":
                $this->delTemplate();
                $this->_templates();
                break;
            case "confirmmail":
                $this->ConfirmMail();
                break;
            case "notificationmail":
                $this->NotificationMail();
                break;
            case "activatemail":
                $this->ActivateMail();
                break;
            case "update":
                $this->_update();
                break;
            case "deleteInactive":
                $this->_deleteInactiveRecipients();
                $this->_users();
                break;
            default:
                $this->overview();
            break;
        }

        if (!isset($_REQUEST['standalone'])) {
            $objTemplate->setVariable(array(
                'CONTENT_TITLE'                => $this->_pageTitle,
                'CONTENT_OK_MESSAGE'        => $this->_strOkMessage,
                'CONTENT_STATUS_MESSAGE'    => $this->_strErrMessage,
                'ADMIN_CONTENT'                => $this->_objTpl->get()
            ));
        } else {
            $this->_objTpl->show();
            exit;
        }
    }

    /**
     * Lists
     *
     * Display the list administration page
     *
     * @access private
     * @global array $_ARRAYLANG
     * @global ADONewConnection
     */
    function _lists()
    {
        global $_ARRAYLANG, $objDatabase;

        $this->_objTpl->loadTemplateFile('module_newsletter_lists.html');
        $this->_pageTitle = $_ARRAYLANG['TXT_NEWSLETTER_LISTS'];
        $rowNr = 0;

        if (!empty($_GET["bulkdelete"]) && $_GET["bulkdelete"] == "exe") {
            $error=0;
            if (!empty($_POST['listid'])) {
                foreach ($_POST['listid'] as $listid) {
                    $listid=intval($listid);
                    if (    $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_newsletter_category WHERE id=$listid") !== false) {
                        $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_newsletter_rel_cat_news WHERE category=$listid");
                        $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_newsletter_rel_user_cat WHERE category=$listid");
                    } else {
                        $error=1;
                    }
                }
                if ($error) {
                    $this->_strErrMessage = $_ARRAYLANG['TXT_DATA_RECORD_DELETE_ERROR'];
                } else {
                    $this->_strOkMessage = $_ARRAYLANG['TXT_DATA_RECORD_DELETED_SUCCESSFUL'];
                }
            }
        }

        $arrLists = &$this->_getLists();

        $this->_objTpl->setVariable(array(
            'TXT_NEWSLETTER_CONFIRM_DELETE_LIST'            => $_ARRAYLANG['TXT_NEWSLETTER_CONFIRM_DELETE_LIST'],
            'TXT_NEWSLETTER_CANNOT_UNDO_OPERATION'          => $_ARRAYLANG['TXT_NEWSLETTER_CANNOT_UNDO_OPERATION'],
            'TXT_NEWSLETTER_LISTS'                          => $_ARRAYLANG['TXT_NEWSLETTER_LISTS'],
            'TXT_NEWSLETTER_ID_UC'                          => $_ARRAYLANG['TXT_NEWSLETTER_ID_UC'],
            'TXT_NEWSLETTER_STATUS'                         => $_ARRAYLANG['TXT_NEWSLETTER_STATUS'],
            'TXT_NEWSLETTER_NAME'                           => $_ARRAYLANG['TXT_NEWSLETTER_NAME'],
            'TXT_NEWSLETTER_LAST_EMAIL'                     => $_ARRAYLANG['TXT_NEWSLETTER_LAST_EMAIL'],
            'TXT_NEWSLETTER_RECIPIENTS'                     => $_ARRAYLANG['TXT_NEWSLETTER_RECIPIENTS'],
            'TXT_NEWSLETTER_FUNCTIONS'                      => $_ARRAYLANG['TXT_NEWSLETTER_FUNCTIONS'],
            'TXT_NEWSLETTER_CONFIRM_CHANGE_LIST_STATUS'     => $_ARRAYLANG['TXT_NEWSLETTER_CONFIRM_CHANGE_LIST_STATUS'],
            'TXT_NEWSLETTER_ADD_NEW_LIST'                   => $_ARRAYLANG['TXT_NEWSLETTER_ADD_NEW_LIST'],
            'TXT_EXPORT'                                    => $_ARRAYLANG['TXT_NEWSLETTER_EXPORT'],
            'TXT_NEWSLETTER_CHECK_ALL'                      => $_ARRAYLANG['TXT_NEWSLETTER_CHECK_ALL'],
            'TXT_NEWSLETTER_UNCHECK_ALL'                    => $_ARRAYLANG['TXT_NEWSLETTER_UNCHECK_ALL'],
            'TXT_NEWSLETTER_WITH_SELECTED'                  => $_ARRAYLANG['TXT_NEWSLETTER_WITH_SELECTED'],
            'TXT_NEWSLETTER_DELETE'                         => $_ARRAYLANG['TXT_NEWSLETTER_DELETE'],
            'TXT_NEWSLETTER_FLUSH'                          => $_ARRAYLANG['TXT_NEWSLETTER_FLUSH'],
            'TXT_CONFIRM_DELETE_DATA'                       => $_ARRAYLANG['TXT_CONFIRM_DELETE_DATA'],
            'TXT_NEWSLETTER_CONFIRM_FLUSH_LIST'             => $_ARRAYLANG['TXT_NEWSLETTER_CONFIRM_FLUSH_LIST'],
            'TXT_NEWSLETTER_EXPORT_ALL_LISTS'               => $_ARRAYLANG['TXT_NEWSLETTER_EXPORT_ALL_LISTS'],
        ));

        $this->_objTpl->setGlobalVariable(array(
            'TXT_NEWSLETTER_MODIFY'                         => $_ARRAYLANG['TXT_NEWSLETTER_MODIFY'],
            'TXT_NEWSLETTER_DELETE'                         => $_ARRAYLANG['TXT_NEWSLETTER_DELETE'],
            'TXT_NEWSLETTER_GENERATE_HTML_SOURCE_CODE'      => $_ARRAYLANG['TXT_NEWSLETTER_GENERATE_HTML_SOURCE_CODE'],
            'TXT_NEWSLETTER_SHOW_LAST_SENT_EMAIL'           => $_ARRAYLANG['TXT_NEWSLETTER_SHOW_LAST_SENT_EMAIL'],
            'TXT_NEWSLETTER_CREATE_NEW_EMAIL'               => $_ARRAYLANG['TXT_NEWSLETTER_CREATE_NEW_EMAIL'],
            'TXT_NEWSLETTER_NOTIFY_ON_UNSUBSCRIBE'                     => $_ARRAYLANG['TXT_NEWSLETTER_NOTIFY_ON_UNSUBSCRIBE'],
        ));

        foreach ($arrLists as $id => $arrList) {
            $this->_objTpl->setVariable(array(
                'NEWSLETTER_LIST_ID'                => $id,
                'NEWSLETTER_ROW_CLASS'              => $rowNr % 2 == 1 ? "row1" : "row2",
                'NEWSLETTER_LIST_STATUS_IMG'        => $arrList['status'] == 1 ? "folder_on.gif" : "folder_off.gif",
                'NEWSLETTER_LIST_NAME'              => htmlentities($arrList['name'], ENT_QUOTES, CONTREXX_CHARSET),
                'NEWSLETTER_LAST_MAIL_ID'           => $arrList['mail_id'],
                'NEWSLETTER_LIST_RECIPIENT'         => $arrList['recipients'] > 0 ? '<a href="index.php?cmd=newsletter&amp;act=users&amp;newsletterListId='.$id.'" title="'.
                                                        sprintf($_ARRAYLANG['TXT_NEWSLETTER_SHOW_RECIPIENTS_OF_LIST'], $arrList['name']).'">'.$arrList['recipients'].'</a>' : '-',
                'NEWSLETTER_LIST_STATUS_MSG'        => $arrList['status'] == 1 ? $_ARRAYLANG['TXT_NEWSLETTER_VISIBLE_STATUS_TXT'] : $_ARRAYLANG['TXT_NEWSLETTER_INVISIBLE_STATUS_TXT'],
                'NEWSLETTER_NOTIFICATION_EMAIL'     => trim($arrList['notification_email']) == '' ? '-' : htmlentities($arrList['notification_email'], ENT_QUOTES, CONTREXX_CHARSET),
            ));

            if ($arrList['mail_sent'] > 0) {
                $this->_objTpl->setVariable('NEWSLETTER_LIST_LAST_MAIL', date(ASCMS_DATE_SHORT_FORMAT, $arrList['mail_sent'])." (".htmlentities($arrList['mail_name'], ENT_QUOTES, CONTREXX_CHARSET).")");
                $this->_objTpl->touchBlock('newsletter_list_last_mail');
                $this->_objTpl->hideBlock('newsletter_list_no_last_mail');
            } else {
                $this->_objTpl->hideBlock('newsletter_list_last_mail');
                $this->_objTpl->touchBlock('newsletter_list_no_last_mail');
            }

            $this->_objTpl->parse('newsletter_lists');
            $rowNr++;
        }
    }

    function _flushList()
    {
        global $objDatabase, $_ARRAYLANG;
        $listID = (!empty($_GET['id'])) ? intval($_GET['id']) : false;
        if ($listID) {
            $query = "    DELETE FROM ".DBPREFIX."module_newsletter_rel_user_cat
                        WHERE category = $listID";
            if ($objDatabase->Execute($query) !== false) {
                $this->_strOkMessage = $_ARRAYLANG['TXT_NEWSLETTER_SUCCESSFULLY_FLUSHED'];
            } else {
                $this->_strErrMessage = $_ARRAYLANG['TXT_DATA_RECORD_DELETE_ERROR'];
            }
        } else {
            $this->_strErrMessage = $_ARRAYLANG['TXT_NEWSLETTER_NO_ID_SPECIFIED'];
        }
    }

    function _deleteList()
    {
        global $objDatabase, $_ARRAYLANG;
        $listId = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($listId > 0) {
            if (($arrList = &$this->_getList($listId)) !== false) {
                $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_newsletter_rel_cat_news WHERE category=".$listId);
                $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_newsletter_rel_user_cat WHERE category=".$listId);

                if ($objDatabase->Execute("DELETE FROM ".DBPREFIX."module_newsletter_category WHERE id=".$listId) !== false) {
                    $this->_strOkMessage .= sprintf($_ARRAYLANG['TXT_NEWSLETTER_LIST_SUCCESSFULLY_DELETED'], $arrList['name']);
                } else {
                    $this->_strErrMessage .= sprintf($_ARRAYLANG['TXT_NEWSLETTER_COULD_NOT_DELETE_LIST'], $arrList['name']);
                }
            }
        }
    }

    function _getList($listId)
    {
        global $objDatabase;

        $objList = $objDatabase->SelectLimit("SELECT `status`, `name`, `notification_email` FROM ".DBPREFIX."module_newsletter_category WHERE id=".$listId, 1);
        if ($objList !== false && $objList->RecordCount() == 1) {
            return array(
                'status' => $objList->fields['status'],
                'name'   => $objList->fields['name'],
                'notification_email'   => $objList->fields['notification_email'],
            );
        }
        return false;
    }

    function _changeListStatus()
    {
        global $objDatabase;

        $listId = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($listId > 0) {
            if (($arrList = &$this->_getList($listId)) !== false) {
                $objDatabase->Execute("UPDATE ".DBPREFIX."module_newsletter_category SET `status`=".($arrList['status'] == 1 ? "0" : "1")." WHERE id=".$listId);
            }
        }
    }

    function _editList()
    {
        global $_ARRAYLANG;

        $listId = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

        if (isset($_POST['save'])) {
            $listName = isset($_POST['newsletter_list_name']) ? contrexx_addslashes($_POST['newsletter_list_name']) : '';
            $listStatus = (isset($_POST['newsletter_list_status']) && intval($_POST['newsletter_list_status']) == '1') ? intval($_POST['newsletter_list_status']) : 0;
            if (!empty($listName)) {
                if ($this->_checkUniqueListName($listId, $listName) !== false) {
                    if ($listId == 0) {
                        if ($this->_addList($listName, $listStatus) !== false) {
                            $this->_strOkMessage .= sprintf($_ARRAYLANG['TXT_NEWSLETTER_LIST_SUCCESSFULLY_CREATED'], $listName);
                            return $this->_lists();
                        } else {
                            $this->_strErrMessage .= sprintf($_ARRAYLANG['TXT_NEWSLETTER_COULD_NOT_CREATE_LIST'], $listName);
                        }
                    } else {
                        if ($this->_updateList($listId, $listName, $listStatus) !== false) {
                            $this->_strOkMessage .= sprintf($_ARRAYLANG['TXT_NEWSLETTER_LIST_SUCCESSFULLY_UPDATED'], $listName);
                            return $this->_lists();
                        } else {
                            $this->_strErrMessage .= sprintf($_ARRAYLANG['TXT_NEWSLETTER_COULD_NOT_UPDATE_LIST'], $listName);
                        }
                    }
                } else {
                    $this->_strErrMessage .= $_ARRAYLANG['TXT_NEWSLETTER_DUPLICATE_LIST_NAME_MSG'];
                }
            } else {
                $this->_strErrMessage .= $_ARRAYLANG['TXT_NEWSLETTER_DEFINE_LIST_NAME_MSG'];
            }
        } elseif ($listId > 0 && ($arrList = &$this->_getList($listId)) !== false) {
            $listName = $arrList['name'];
            $listStatus = $arrList['status'];
            $listNotificationEmail = $arrList['notification_email'];
        } else {
            $listName = isset($_POST['newsletter_list_name']) ? contrexx_addslashes($_POST['newsletter_list_name']) : '';
            $listStatus = (isset($_POST['newsletter_list_status']) && intval($_POST['newsletter_list_status']) == '1') ? intval($_POST['newsletter_list_status']) : 0;
        }

        $this->_objTpl->loadTemplateFile('module_newsletter_list_edit.html');
        $this->_pageTitle = $listId > 0 ? $_ARRAYLANG['TXT_NEWSLETTER_MODIFY_LIST'] : $_ARRAYLANG['TXT_NEWSLETTER_ADD_NEW_LIST'];

        $this->_objTpl->setVariable(array(
            'TXT_NEWSLETTER_NAME'                   => $_ARRAYLANG['TXT_NEWSLETTER_NAME'],
            'TXT_NEWSLETTER_STATUS'                 => $_ARRAYLANG['TXT_NEWSLETTER_STATUS'],
            'TXT_NEWSLETTER_VISIBLE'                => $_ARRAYLANG['TXT_NEWSLETTER_VISIBLE'],
            'TXT_NEWSLETTER_BACK'                   => $_ARRAYLANG['TXT_NEWSLETTER_BACK'],
            'TXT_NEWSLETTER_SAVE'                   => $_ARRAYLANG['TXT_NEWSLETTER_SAVE'],
            'TXT_NEWSLETTER_NOTIFY_ON_UNSUBSCRIBE'  => $_ARRAYLANG['TXT_NEWSLETTER_NOTIFY_ON_UNSUBSCRIBE'],

        ));

        $this->_objTpl->setVariable(array(
            'NEWSLETTER_LIST_TITLE'        => $listId > 0 ? $_ARRAYLANG['TXT_NEWSLETTER_MODIFY_LIST'] : $_ARRAYLANG['TXT_NEWSLETTER_ADD_NEW_LIST'],
            'NEWSLETTER_LIST_ID'        => $listId,
            'NEWSLETTER_LIST_NAME'        => htmlentities($listName, ENT_QUOTES, CONTREXX_CHARSET),
            'NEWSLETTER_LIST_STATUS'    => $listStatus == 1 ? 'checked="checked"' : '',
        ));
        return true;
    }


    function _updateList($listId, $listName, $listStatus)
    {
        global $objDatabase;

        if ($objDatabase->Execute("
            UPDATE ".DBPREFIX."module_newsletter_category
               SET `name`='$listName',
                   `status`=$listStatus
             WHERE id=".intval($listId))) {
            return true;
        }
        return false;
    }



    function _addList($listName, $listStatus)
    {
        global $objDatabase;

        if ($objDatabase->Execute("INSERT INTO ".DBPREFIX."module_newsletter_category (`name`, `status`)
                                    VALUES ('".$listName."', ".$listStatus.")") !== false) {
            return true;
        } else {
            return false;
        }
    }

    function _editMail($copy = false)
    {
        global $objDatabase, $_ARRAYLANG, $_CONFIG;

        $mailId = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
        $arrAttachment = array();
        $attachmentNr = 0;
        $arrAssociatedLists = array();
        $status = true;

        $mailSubject = isset($_POST['newsletter_mail_subject']) ? contrexx_stripslashes($_POST['newsletter_mail_subject']) : '';

        $arrTemplates = $this->_getTemplates();
        $mailTemplate = isset($_POST['newsletter_mail_template']) ? intval($_POST['newsletter_mail_template']) : key($arrTemplates);

        if (isset($_POST['newsletter_mail_html_content'])) {
            $mailHtmlContent = preg_replace('/\[\[([A-Z0-9_]*?)\]\]/', '{\\1}' , contrexx_stripslashes($_POST['newsletter_mail_html_content']));
            $mailHtmlContent = $this->_getBodyContent($mailHtmlContent);
        } else {
            $mailHtmlContent = '';
        }
        if (isset($_POST['newsletter_mail_text_content'])) {
            $mailTextContent = preg_replace('/\[\[([A-Z0-9_]*?)\]\]/', '{\\1}' , contrexx_stripslashes($_POST['newsletter_mail_text_content']));
            $mailTextContent = $this->_getBodyContent($mailTextContent);
        } else {
            $mailTextContent = '';
        }
        if (isset($_POST['newsletter_mail_attachment']) && is_array($_POST['newsletter_mail_attachment'])) {
            foreach ($_POST['newsletter_mail_attachment'] as $attachment) {
                array_push($arrAttachment, contrexx_addslashes($attachment));
            }
        }

        $mailFormat = isset($_POST['newsletter_mail_format']) ? contrexx_stripslashes($_POST['newsletter_mail_format']) : $this->_stdMailFormat;

        if (isset($_POST['newsletter_mail_priority'])) {
            $mailPriority = intval($_POST['newsletter_mail_priority']);
            if ($mailPriority < 1 || $mailPriority > 5) {
                $mailPriority = $this->_stdMailPriority;
            }
        } else {
            $mailPriority = $this->_stdMailPriority;
        }

        if (isset($_POST['newsletter_mail_associated_list'])) {
            foreach ($_POST['newsletter_mail_associated_list'] as $listId => $status) {
                if (intval($status) == 1) {
                    array_push($arrAssociatedLists, intval($listId));
                }
            }
        }

        $arrSettings = $this->_getSettings();
        $mailSenderMail = isset($_POST['newsletter_mail_sender_mail']) ? contrexx_stripslashes($_POST['newsletter_mail_sender_mail']) : $arrSettings['sender_mail']['setvalue'];
        $mailSenderName = isset($_POST['newsletter_mail_sender_name']) ? contrexx_stripslashes($_POST['newsletter_mail_sender_name']) : $arrSettings['sender_name']['setvalue'];
        $mailReply = isset($_POST['newsletter_mail_sender_reply']) ? contrexx_stripslashes($_POST['newsletter_mail_sender_reply']) : $arrSettings['reply_mail']['setvalue'];
        $mailSmtpServer = isset($_POST['newsletter_mail_smtp_account']) ? intval($_POST['newsletter_mail_smtp_account']) : $_CONFIG['coreSmtpServer'];


        $this->_objTpl->loadTemplateFile('module_newsletter_mail_edit.html');
        $this->_pageTitle = $mailId > 0 ? ($copy ? $_ARRAYLANG['TXT_NEWSLETTER_COPY_EMAIL'] : $_ARRAYLANG['TXT_NEWSLETTER_MODIFY_EMAIL']) : $_ARRAYLANG['TXT_NEWSLETTER_CREATE_NEW_EMAIL'];

        $this->_objTpl->setVariable(array(
            'NEWSLETTER_MAIL_EDIT_TITLE'    => $mailId > 0 ? ($copy ? $_ARRAYLANG['TXT_NEWSLETTER_COPY_EMAIL'] : $_ARRAYLANG['TXT_NEWSLETTER_MODIFY_EMAIL']) : $_ARRAYLANG['TXT_NEWSLETTER_CREATE_NEW_EMAIL']
        ));

        if (isset($_POST['newsletter_mail_save'])) {
            $objAttachment = $objDatabase->Execute("SELECT file_name FROM ".DBPREFIX."module_newsletter_attachment WHERE newsletter=".$mailId);
            if ($objAttachment !== false) {
                $arrCurrentAttachments = array();
                while (!$objAttachment->EOF) {
                    array_push($arrCurrentAttachments, ASCMS_NEWSLETTER_ATTACH_WEB_PATH.'/'.$objAttachment->fields['file_name']);
                    $objAttachment->MoveNext();
                }

                $arrNewAttachments = array_diff($arrAttachment, $arrCurrentAttachments);
                $arrRemovedAttachments = array_diff($arrCurrentAttachments, $arrAttachment);
            }

            if ($mailId > 0) {
                $status = $this->_updateMail($mailId, $mailSubject, $mailFormat, $mailTemplate, $mailSenderMail, $mailSenderName, $mailReply, $mailSmtpServer, $mailPriority, $arrAttachment, $mailHtmlContent, $mailTextContent);
            } else {
                $mailId = $this->_addMail($mailSubject, $mailFormat, $mailTemplate, $mailSenderMail, $mailSenderName, $mailReply, $mailSmtpServer, $mailPriority, $arrAttachment, $mailHtmlContent, $mailTextContent);
                if ($mailId === false) {
                    $status = false;
                }
            }

            if ($status) {
                $this->_setMailLists($mailId, $arrAssociatedLists);

                foreach ($arrNewAttachments as $attachment) {
                    $this->_addMailAttachment($attachment, $mailId);
                }

                foreach ($arrRemovedAttachments as $attachment) {
                    $this->_removeMailAttachment($attachment, $mailId);
                }

                $this->_strOkMessage .= $_ARRAYLANG['TXT_DATA_RECORD_STORED_SUCCESSFUL'];

                if (isset($_GET['sendMail']) && $_GET['sendMail'] == '1') {
                    return $this->_sendMailPage();
                } else {
                    return $this->_mails();
                }
            }
        } elseif ((!isset($_GET['setFormat']) || $_GET['setFormat'] != '1') && $mailId > 0) {
            $objMail = $objDatabase->SelectLimit("SELECT
                subject,
                template,
                content,
                content_text,
                attachment,
                format,
                priority,
                sender_email,
                sender_name,
                return_path,
                smtp_server
                FROM ".DBPREFIX."module_newsletter
                WHERE id=".$mailId, 1);
            if ($objMail !== false) {
                if ($objMail->RecordCount() == 1) {
                    $mailSubject = $objMail->fields['subject'];
                    $mailTemplate = $objMail->fields['template'];
                    $mailHtmlContent = $objMail->fields['content'];
                    $mailTextContent = $objMail->fields['content_text'];
                    $mailFormat = $objMail->fields['format'];
                    $mailPriority = $objMail->fields['priority'];
                    $mailSenderMail = $objMail->fields['sender_email'];
                    $mailSenderName = $objMail->fields['sender_name'];
                    $mailReply = $objMail->fields['return_path'];
                    $mailSmtpServer = $objMail->fields['smtp_server'];

                    $objList = $objDatabase->Execute("SELECT category FROM ".DBPREFIX."module_newsletter_rel_cat_news WHERE newsletter=".$mailId);
                    if ($objList !== false) {
                        while (!$objList->EOF) {
                            array_push($arrAssociatedLists, $objList->fields['category']);
                            $objList->MoveNext();
                        }

                    }

                    if ($objMail->fields['attachment'] == '1') {
                        $objAttachment = $objDatabase->Execute("SELECT file_name FROM ".DBPREFIX."module_newsletter_attachment WHERE newsletter=".$mailId);
                        if ($objAttachment !== false) {
                            while (!$objAttachment->EOF) {
                                array_push($arrAttachment, ASCMS_NEWSLETTER_ATTACH_WEB_PATH.'/'.$objAttachment->fields['file_name']);
                                $objAttachment->MoveNext();
                            }
                        }
                    }
                } else {
                    return $this->_mails();
                }
            }
        } else {
            $arrSettings = $this->_getSettings();

            $mailSenderMail = $arrSettings['sender_mail']['setvalue'];
            $mailSenderName = $arrSettings['sender_name']['setvalue'];
            $mailReply = $arrSettings['reply_mail']['setvalue'];
            $mailSmtpServer = $_CONFIG['coreSmtpServer'];

            if (!empty($_POST['textfield'])) {
                $mailHtmlContent =  nl2br($_POST['textfield']);
                $mailTextContent =  $_POST['textfield'];
            }
        }

        require_once(ASCMS_CORE_PATH.'/SmtpSettings.class.php');

        $act = $copy ? 'copyMail' : 'editMail';

        $this->_objTpl->setVariable(array(
            'NEWSLETTER_MAIL_ID'                  => ($copy ? 0 : $mailId),
            'NEWSLETTER_MAIL_SUBJECT'             => htmlentities($mailSubject, ENT_QUOTES, CONTREXX_CHARSET),
            'NEWSLETTER_MAIL_HTML_CONTENT'        => $mailFormat != 'text' ? get_wysiwyg_editor('newsletter_mail_html_content', $mailHtmlContent, null, null, true) : '<input type="hidden" name="newsletter_mail_html_content" value="'.htmlentities($mailHtmlContent, ENT_QUOTES, CONTREXX_CHARSET).'" />',
            'NEWSLETTER_MAIL_TEXT_CONTENT'        => '<textarea name="newsletter_mail_text_content" style="width: 100%; height: 447px;">'.htmlentities($mailTextContent, ENT_QUOTES, CONTREXX_CHARSET).'</textarea>',
            'NEWSLETTER_MAIL_HTML_CONTENT_STAUTS' => $mailFormat != 'text' ? 'block' : 'none',
            'NEWSLETTER_MAIL_TEXT_CONTENT_STAUTS' => $mailFormat == 'text' ? 'block' : 'none',
            'NEWSLETTER_MAIL_HTML_CONTENT_CLASS'  => $mailFormat != 'text' ? 'active' : '',
            'NEWSLETTER_MAIL_TEXT_CONTENT_CLASS'  => $mailFormat == 'text' ? 'active' : '',
            'NEWSLETTER_MAIL_FORMAT_MENU'         => $this->_getMailFormatMenu($mailFormat, 'name="newsletter_mail_format" id="newsletter_mail_format" onchange="document.getElementById(\'newsletter_mail_form\').action=\'index.php?cmd=newsletter&amp;act='.$act.'&amp;id='.$mailId.'&amp;setFormat=1\';document.getElementById(\'newsletter_mail_form\').submit()" style="width:300px;"'),
            'NEWSLETTER_MAIL_PRIORITY_MENU'       => $this->_getMailPriorityMenu($mailPriority, 'name="newsletter_mail_priority" style="width:300px;"'),
            'NEWSLETTER_MAIL_TEMPLATE_MENU'       => $this->_getTemplateMenu($mailTemplate, 'name="newsletter_mail_template" style="width:300px;" onchange="document.getElementById(\'newsletter_mail_form\').action=\'index.php?cmd=newsletter&amp;act='.$act.'&amp;id='.$mailId.'&amp;setFormat=1\';document.getElementById(\'newsletter_mail_form\').submit()"'),
            'NEWSLETTER_MAIL_SENDER_MAIL'         => htmlentities($mailSenderMail, ENT_QUOTES, CONTREXX_CHARSET),
            'NEWSLETTER_MAIL_SENDER_NAME'         => htmlentities($mailSenderName, ENT_QUOTES, CONTREXX_CHARSET),
            'NEWSLETTER_MAIL_REPLY'               => htmlentities($mailReply, ENT_QUOTES, CONTREXX_CHARSET),
            'NEWSLETTER_MAIL_SMTP_SERVER'         => SmtpSettings::getSmtpAccountMenu($mailSmtpServer, 'name="newsletter_mail_smtp_account" style="width:300px;"'),
            'NEWSLETTER_MAIL_SEND'                => $_GET['act'] == 'sendMail' ? 1 : 0
        ));

        if ($mailFormat == 'text') {
            $this->_objTpl->setVariable('TXT_NEWSLETTER_TEXT', $_ARRAYLANG['TXT_NEWSLETTER_TEXT']);
            $this->_objTpl->touchBlock('newsletter_mail_text_content');
            $this->_objTpl->hideBlock('newsletter_mail_html_content');
        } elseif ($mailFormat == 'html') {
            $this->_objTpl->setVariable('TXT_NEWSLETTER_HTML_UC', $_ARRAYLANG['TXT_NEWSLETTER_HTML_UC']);
            $this->_objTpl->touchBlock('newsletter_mail_html_content');
            $this->_objTpl->hideBlock('newsletter_mail_text_content');
        } else {
            $this->_objTpl->setVariable(array(
                'TXT_NEWSLETTER_HTML_UC' => $_ARRAYLANG['TXT_NEWSLETTER_HTML_UC'],
                'TXT_NEWSLETTER_TEXT'    => $_ARRAYLANG['TXT_NEWSLETTER_TEXT']
            ));
            $this->_objTpl->touchBlock('newsletter_mail_html_content');
            $this->_objTpl->touchBlock('newsletter_mail_text_content');
        }

        $arrLists = &$this->_getLists('tblCategory.name');
        $listNr = 0;
        foreach ($arrLists as $listId => $arrList) {
            $column = $listNr % 3;
            $this->_objTpl->setVariable(array(
                'NEWSLETTER_LIST_ID'                     => $listId,
                'NEWSLETTER_LIST_NAME'                   => $arrList['name'],
                'NEWSLETTER_SHOW_RECIPIENTS_OF_LIST_TXT' => sprintf($_ARRAYLANG['TXT_NEWSLETTER_SHOW_RECIPIENTS_OF_LIST'], $arrList['name']),
                'NEWSLETTER_LIST_ASSOCIATED'             => in_array($listId, $arrAssociatedLists) ? 'checked="checked"' : ''
            ));
            $this->_objTpl->parse('newsletter_mail_associated_list_'.$column);
            $listNr++;
        }

        if (count($arrAttachment) > 0) {
            foreach ($arrAttachment as $attachment) {
                $this->_objTpl->setVariable(array(
                    'NEWSLETTER_MAIL_ATTACHMENT_NR'   => $attachmentNr,
                    'NEWSLETTER_MAIL_ATTACHMENT_NAME' => substr($attachment, strrpos($attachment, '/')+1),
                    'NEWSLETTER_MAIL_ATTACHMENT_URL'  => $attachment,
                ));
                $this->_objTpl->parse('newsletter_mail_attachment_list');
                $attachmentNr++;
            }
        } else {
            $this->_objTpl->hideBlock('newsletter_mail_attachment_list');
        }

        $this->_objTpl->setVariable(array(
            'NEWSLETTER_MAIL_ATTACHMENT_NR'  => $attachmentNr,
            'NEWSLETTER_MAIL_ATTACHMENT_BOX' => $attachmentNr > 0 ? 'block' : 'none',
        ));

        $this->_objTpl->setVariable(array(
            'TXT_NEWSLETTER_EMAIL_ACCOUNT'         => $_ARRAYLANG['TXT_NEWSLETTER_EMAIL_ACCOUNT'],
            'TXT_NEWSLETTER_SUBJECT'               => $_ARRAYLANG['TXT_NEWSLETTER_SUBJECT'],
            'TXT_NEWSLETTER_SEND_AS'               => $_ARRAYLANG['TXT_NEWSLETTER_SEND_AS'],
            'TXT_NEWSLETTER_TEMPLATE'              => $_ARRAYLANG['TXT_NEWSLETTER_TEMPLATE'],
            'TXT_NEWSLETTER_SENDER'                => $_ARRAYLANG['TXT_NEWSLETTER_SENDER'],
            'TXT_NEWSLETTER_EMAIL'                 => $_ARRAYLANG['TXT_NEWSLETTER_EMAIL'],
            'TXT_NEWSLETTER_URI'                    => $_ARRAYLANG['TXT_NEWSLETTER_URI'],
            'TXT_NEWSLETTER_NAME'                  => $_ARRAYLANG['TXT_NEWSLETTER_NAME'],
            'TXT_NEWSLETTER_REPLY_ADDRESS'         => $_ARRAYLANG['TXT_NEWSLETTER_REPLY_ADDRESS'],
            'TXT_NEWSLETTER_PRIORITY'              => $_ARRAYLANG['TXT_NEWSLETTER_PRIORITY'],
            'TXT_NEWSLETTER_PRIORITY'              => $_ARRAYLANG['TXT_NEWSLETTER_PRIORITY'],
            'TXT_NEWSLETTER_ASSOCIATED_LISTS'      => $_ARRAYLANG['TXT_NEWSLETTER_ASSOCIATED_LISTS'],
            'TXT_NEWSLETTER_ATTACH'                => $_ARRAYLANG['TXT_NEWSLETTER_ATTACH'],
            'TXT_NEWSLETTER_DISPLAY_FILE'          => $_ARRAYLANG['TXT_NEWSLETTER_DISPLAY_FILE'],
            'TXT_NEWSLETTER_REMOVE_FILE'           => $_ARRAYLANG['TXT_NEWSLETTER_REMOVE_FILE'],
            'TXT_NEWSLETTER_ATTACH_FILE'           => $_ARRAYLANG['TXT_NEWSLETTER_ATTACH_FILE'],
            'TXT_NEWSLETTER_HTML_CONTENT'          => $_ARRAYLANG['TXT_NEWSLETTER_HTML_CONTENT'],
            'TXT_NEWSLETTER_TEXT_CONTENT'          => $_ARRAYLANG['TXT_NEWSLETTER_TEXT_CONTENT'],
            'TXT_NEWSLETTER_PLACEHOLDER_DIRECTORY' => $_ARRAYLANG['TXT_NEWSLETTER_PLACEHOLDER_DIRECTORY'],
            'TXT_NEWSLETTER_USER_DATA'             => $_ARRAYLANG['TXT_NEWSLETTER_USER_DATA'],
            'TXT_NEWSLETTER_EMAIL_ADDRESS'         => $_ARRAYLANG['TXT_NEWSLETTER_EMAIL_ADDRESS'],
            'TXT_NEWSLETTER_SEX'                   => $_ARRAYLANG['TXT_NEWSLETTER_SEX'],
            'TXT_NEWSLETTER_TITLE'                 => $_ARRAYLANG['TXT_NEWSLETTER_TITLE'],
            'TXT_NEWSLETTER_LASTNAME'              => $_ARRAYLANG['TXT_NEWSLETTER_LASTNAME'],
            'TXT_NEWSLETTER_FIRSTNAME'             => $_ARRAYLANG['TXT_NEWSLETTER_FIRSTNAME'],
            'TXT_NEWSLETTER_STREET'                => $_ARRAYLANG['TXT_NEWSLETTER_STREET'],
            'TXT_NEWSLETTER_ZIP'                   => $_ARRAYLANG['TXT_NEWSLETTER_ZIP'],
            'TXT_NEWSLETTER_CITY'                  => $_ARRAYLANG['TXT_NEWSLETTER_CITY'],
            'TXT_NEWSLETTER_COUNTRY'               => $_ARRAYLANG['TXT_NEWSLETTER_COUNTRY'],
            'TXT_NEWSLETTER_PHONE'                 => $_ARRAYLANG['TXT_NEWSLETTER_PHONE'],
            'TXT_NEWSLETTER_BIRTHDAY'              => $_ARRAYLANG['TXT_NEWSLETTER_BIRTHDAY'],
            'TXT_NEWSLETTER_GENERAL'               => $_ARRAYLANG['TXT_NEWSLETTER_GENERAL'],
            'TXT_NEWSLETTER_MODIFY_PROFILE'        => $_ARRAYLANG['TXT_NEWSLETTER_MODIFY_PROFILE'],
            'TXT_NEWSLETTER_UNSUBSCRIBE'           => $_ARRAYLANG['TXT_NEWSLETTER_UNSUBSCRIBE'],
            'TXT_NEWSLETTER_DATE'                  => $_ARRAYLANG['TXT_NEWSLETTER_DATE'],
            'TXT_NEWSLETTER_SAVE'                  => $_ARRAYLANG['TXT_NEWSLETTER_SAVE'],
            'TXT_NEWSLETTER_BACK'                  => $_ARRAYLANG['TXT_NEWSLETTER_BACK']
        ));
        return true;
    }


    function _showMail()
    {
        global $objDatabase, $_ARRAYLANG;

        $mailId = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
        $mailSubject = '';
        $mailTemplate = '';
        $mailHtmlContent = '';
        $mailTextContent = '';
        $arrAttachment = array();
        $mailFormat = $this->_stdMailFormat;
        $mailPriority = $this->_stdMailPriority;
        $mailSenderMail = '';
        $mailSenderName = '';
        $mailReply = '';
        $mailSmtpServer = 0;
        $arrAssociatedLists = array();

        $this->_objTpl->loadTemplateFile('module_newsletter_mail_show.html');
        $this->_pageTitle = 'E-Mail anzeigen';

        $objMail = $objDatabase->SelectLimit("SELECT
            subject,
            template,
            content,
            content_text,
            attachment,
            format,
            priority,
            sender_email,
            sender_name,
            return_path,
            smtp_server
            FROM ".DBPREFIX."module_newsletter
            WHERE id=".$mailId, 1);
        if ($objMail !== false) {
            if ($objMail->RecordCount() == 1) {
                $mailSubject = $objMail->fields['subject'];
                $mailTemplate = $objMail->fields['template'];
                $mailHtmlContent = $objMail->fields['content'];
                $mailTextContent = $objMail->fields['content_text'];
                $mailFormat = $objMail->fields['format'];
                $mailPriority = $objMail->fields['priority'];
                $mailSenderMail = $objMail->fields['sender_email'];
                $mailSenderName = $objMail->fields['sender_name'];
                $mailReply = $objMail->fields['return_path'];
                $mailSmtpServer = $objMail->fields['smtp_server'];

                $objList = $objDatabase->Execute("SELECT category FROM ".DBPREFIX."module_newsletter_rel_cat_news WHERE newsletter=".$mailId);
                if ($objList !== false) {
                    while (!$objList->EOF) {
                        array_push($arrAssociatedLists, $objList->fields['category']);
                        $objList->MoveNext();
                    }

                }

                if ($objMail->fields['attachment'] == '1') {
                    $objAttachment = $objDatabase->Execute("SELECT file_name FROM ".DBPREFIX."module_newsletter_attachment WHERE newsletter=".$mailId);
                    if ($objAttachment !== false) {
                        while (!$objAttachment->EOF) {
                            array_push($arrAttachment, ASCMS_NEWSLETTER_ATTACH_WEB_PATH.'/'.$objAttachment->fields['file_name']);
                            $objAttachment->MoveNext();
                        }
                    }
                }
            } else {
                return $this->_mails();
            }
        }

        require_once(ASCMS_CORE_PATH.'/SmtpSettings.class.php');
        $arrSmtp = SmtpSettings::getSmtpAccount($mailSmtpServer);
        if ($arrSmtp === false) {
            $arrSmtp = SmtpSettings::getSystemSmtpAccount();
        }

        $arrTemplates = &$this->_getTemplates();
        $this->_objTpl->setVariable(array(
            'NEWSLETTER_MAIL_SUBJECT'        => htmlentities($mailSubject, ENT_QUOTES, CONTREXX_CHARSET),
            'NEWSLETTER_MAIL_HTML_CONTENT'    => nl2br(htmlentities($mailHtmlContent, ENT_QUOTES, CONTREXX_CHARSET)),
            'NEWSLETTER_MAIL_TEXT_CONTENT'    => nl2br(htmlentities($mailTextContent, ENT_QUOTES, CONTREXX_CHARSET)),
            'NEWSLETTER_MAIL_FORMAT'        => $_ARRAYLANG[$this->_arrMailFormat[$mailFormat]],
            'NEWSLETTER_MAIL_PRIORITY'        => $_ARRAYLANG[$this->_arrMailPriority[$mailPriority]],
            'NEWSLETTER_MAIL_TEMPLATE'        => htmlentities($arrTemplates[$mailTemplate]['name'], ENT_QUOTES, CONTREXX_CHARSET).' ('.htmlentities($arrTemplates[$mailTemplate]['description'], ENT_QUOTES, CONTREXX_CHARSET).')',
            'NEWSLETTER_MAIL_SENDER_MAIL'    => htmlentities($mailSenderMail, ENT_QUOTES, CONTREXX_CHARSET),
            'NEWSLETTER_MAIL_SENDER_NAME'    => htmlentities($mailSenderName, ENT_QUOTES, CONTREXX_CHARSET),
            'NEWSLETTER_MAIL_REPLY'            => htmlentities($mailReply, ENT_QUOTES, CONTREXX_CHARSET),
            'NEWSLETTER_MAIL_SMTP_SERVER'    => htmlentities($arrSmtp['name'], ENT_QUOTES, CONTREXX_CHARSET),
            'NEWSLETTER_MAIL_HTML_CONTENT_STAUTS'    => $mailFormat != 'text' ? 'block' : 'none',
            'NEWSLETTER_MAIL_TEXT_CONTENT_STAUTS'    => $mailFormat == 'text' ? 'block' : 'none',
            'NEWSLETTER_MAIL_HTML_CONTENT_CLASS'    => $mailFormat != 'text' ? 'active' : '',
            'NEWSLETTER_MAIL_TEXT_CONTENT_CLASS'    => $mailFormat == 'text' ? 'active' : ''
        ));

        if ($mailFormat == 'text') {
            $this->_objTpl->setVariable('TXT_NEWSLETTER_TEXT', $_ARRAYLANG['TXT_NEWSLETTER_TEXT']);
            $this->_objTpl->touchBlock('newsletter_mail_text_content');
            $this->_objTpl->hideBlock('newsletter_mail_html_content');
        } elseif ($mailFormat == 'html') {
            $this->_objTpl->setVariable('TXT_NEWSLETTER_HTML_UC', $_ARRAYLANG['TXT_NEWSLETTER_HTML_UC']);
            $this->_objTpl->touchBlock('newsletter_mail_html_content');
            $this->_objTpl->hideBlock('newsletter_mail_text_content');
        } else {
            $this->_objTpl->setVariable(array(
                'TXT_NEWSLETTER_HTML_UC'            => $_ARRAYLANG['TXT_NEWSLETTER_HTML_UC'],
                'TXT_NEWSLETTER_TEXT'                => $_ARRAYLANG['TXT_NEWSLETTER_TEXT']
            ));
            $this->_objTpl->touchBlock('newsletter_mail_html_content');
            $this->_objTpl->touchBlock('newsletter_mail_text_content');
        }

        $arrLists = &$this->_getLists();
        $listNr = 0;
        foreach ($arrLists as $listId => $arrList) {
            if (in_array($listId, $arrAssociatedLists)) {
                $column = $listNr % 3;
                $this->_objTpl->setVariable(array(
                    'NEWSLETTER_LIST_ID'    => $listId,
                    'NEWSLETTER_LIST_NAME'    => $arrList['name'],
                    'NEWSLETTER_SHOW_RECIPIENTS_OF_LIST_TXT'    => sprintf($_ARRAYLANG['TXT_NEWSLETTER_SHOW_RECIPIENTS_OF_LIST'], $arrList['name'])
                ));
                $this->_objTpl->parse('newsletter_mail_associated_list_'.$column);

                $listNr++;
            }
        }

        if (count($arrAttachment) > 0) {
            foreach ($arrAttachment as $attachment) {
                $this->_objTpl->setVariable(array(
                    'NEWSLETTER_MAIL_ATTACHMENT_NAME'    => substr($attachment, strrpos($attachment, '/')+1),
                    'NEWSLETTER_MAIL_ATTACHMENT_URL'    => $attachment
                ));
                $this->_objTpl->parse('newsletter_mail_attachment_list');
            }
        } else {
            $this->_objTpl->hideBlock('newsletter_mail_attachment_list');
        }

        $this->_objTpl->setVariable(array(
            'TXT_NEWSLETTER_EMAIL_ACCOUNT'    => $_ARRAYLANG['TXT_NEWSLETTER_EMAIL_ACCOUNT'],
            'TXT_NEWSLETTER_DISPLAY_EMAIL'    => $_ARRAYLANG['TXT_NEWSLETTER_DISPLAY_EMAIL'],
            'TXT_NEWSLETTER_SUBJECT'        => $_ARRAYLANG['TXT_NEWSLETTER_SUBJECT'],
            'TXT_NEWSLETTER_SEND_AS'        => $_ARRAYLANG['TXT_NEWSLETTER_SEND_AS'],
            'TXT_NEWSLETTER_TEMPLATE'        => $_ARRAYLANG['TXT_NEWSLETTER_TEMPLATE'],
            'TXT_NEWSLETTER_SENDER'            => $_ARRAYLANG['TXT_NEWSLETTER_SENDER'],
            'TXT_NEWSLETTER_EMAIL'            => $_ARRAYLANG['TXT_NEWSLETTER_EMAIL'],
            'TXT_NEWSLETTER_NAME'            => $_ARRAYLANG['TXT_NEWSLETTER_NAME'],
            'TXT_NEWSLETTER_REPLY_ADDRESS'    => $_ARRAYLANG['TXT_NEWSLETTER_REPLY_ADDRESS'],
            'TXT_NEWSLETTER_PRIORITY'        => $_ARRAYLANG['TXT_NEWSLETTER_PRIORITY'],
            'TXT_NEWSLETTER_ASSOCIATED_LISTS'    => $_ARRAYLANG['TXT_NEWSLETTER_ASSOCIATED_LISTS'],
            'TXT_NEWSLETTER_HTML_CONTENT'        => $_ARRAYLANG['TXT_NEWSLETTER_HTML_CONTENT'],
            'TXT_NEWSLETTER_Text_CONTENT'        => $_ARRAYLANG['TXT_NEWSLETTER_Text_CONTENT'],
            'TXT_NEWSLETTER_PLACEHOLDER_DIRECTORY'    => $_ARRAYLANG['TXT_NEWSLETTER_PLACEHOLDER_DIRECTORY'],
            'TXT_NEWSLETTER_USER_DATA'                => $_ARRAYLANG['TXT_NEWSLETTER_USER_DATA'],
            'TXT_NEWSLETTER_EMAIL_ADDRESS'            => $_ARRAYLANG['TXT_NEWSLETTER_EMAIL_ADDRESS'],
            'TXT_NEWSLETTER_TITLE'                    => $_ARRAYLANG['TXT_NEWSLETTER_TITLE'],
            'TXT_NEWSLETTER_SEX'                    => $_ARRAYLANG['TXT_NEWSLETTER_SEX'],
            'TXT_NEWSLETTER_LASTNAME'                => $_ARRAYLANG['TXT_NEWSLETTER_LASTNAME'],
            'TXT_NEWSLETTER_FIRSTNAME'                => $_ARRAYLANG['TXT_NEWSLETTER_FIRSTNAME'],
            'TXT_NEWSLETTER_STREET'                    => $_ARRAYLANG['TXT_NEWSLETTER_STREET'],
            'TXT_NEWSLETTER_ZIP'                    => $_ARRAYLANG['TXT_NEWSLETTER_ZIP'],
            'TXT_NEWSLETTER_CITY'                    => $_ARRAYLANG['TXT_NEWSLETTER_CITY'],
            'TXT_NEWSLETTER_COUNTRY'                => $_ARRAYLANG['TXT_NEWSLETTER_COUNTRY'],
            'TXT_NEWSLETTER_PHONE'                    => $_ARRAYLANG['TXT_NEWSLETTER_PHONE'],
            'TXT_NEWSLETTER_BIRTHDAY'                => $_ARRAYLANG['TXT_NEWSLETTER_BIRTHDAY'],
            'TXT_NEWSLETTER_GENERAL'                => $_ARRAYLANG['TXT_NEWSLETTER_GENERAL'],
            'TXT_NEWSLETTER_MODIFY_PROFILE'            => $_ARRAYLANG['TXT_NEWSLETTER_MODIFY_PROFILE'],
            'TXT_NEWSLETTER_UNSUBSCRIBE'            => $_ARRAYLANG['TXT_NEWSLETTER_UNSUBSCRIBE'],
            'TXT_NEWSLETTER_DATE'                    => $_ARRAYLANG['TXT_NEWSLETTER_DATE'],
            'TXT_NEWSLETTER_BACK'                    => $_ARRAYLANG['TXT_NEWSLETTER_BACK']
        ));
        return true;
    }

    function _recipientOverview($limit = 10)
    {
        global $objDatabase, $_ARRAYLANG;

        $rowNr = 0;

        $this->_objTpl->setVariable('TXT_NEWSLETTER_NEWEST_RECIPIENTS', $_ARRAYLANG['TXT_NEWSLETTER_NEWEST_RECIPIENTS']);
        $this->_objTpl->setGlobalVariable('TXT_NEWSLETTER_MODIFY_RECIPIENT', $_ARRAYLANG['TXT_NEWSLETTER_MODIFY_RECIPIENT']);

        $objUser = $objDatabase->SelectLimit("SELECT id, email, lastname, firstname, street, zip, city, country, status, emaildate FROM ".DBPREFIX."module_newsletter_user ORDER BY emaildate DESC", $limit);
        if ($objUser !== false && $objUser->RecordCount() > 0) {
            $this->_objTpl->setVariable(array(
                'TXT_NEWSLETTER_STATUS'                            => $_ARRAYLANG['TXT_NEWSLETTER_STATUS'],
                'TXT_NEWSLETTER_EMAIL_ADDRESS'                    => $_ARRAYLANG['TXT_NEWSLETTER_EMAIL_ADDRESS'],
                'TXT_NEWSLETTER_LASTNAME'                        => $_ARRAYLANG['TXT_NEWSLETTER_LASTNAME'],
                'TXT_NEWSLETTER_FIRSTNAME'                        => $_ARRAYLANG['TXT_NEWSLETTER_FIRSTNAME'],
                'TXT_NEWSLETTER_STREET'                            => $_ARRAYLANG['TXT_NEWSLETTER_STREET'],
                'TXT_NEWSLETTER_ZIP'                            => $_ARRAYLANG['TXT_NEWSLETTER_ZIP'],
                'TXT_NEWSLETTER_CITY'                            => $_ARRAYLANG['TXT_NEWSLETTER_CITY'],
                'TXT_NEWSLETTER_COUNTRY'                        => $_ARRAYLANG['TXT_NEWSLETTER_COUNTRY'],
                'TXT_NEWSLETTER_REGISTRATION_DATE'                => $_ARRAYLANG['TXT_NEWSLETTER_REGISTRATION_DATE'],
                'TXT_NEWSLETTER_GO_TO_RECIPIENT_ADMINISTRATION'    => $_ARRAYLANG['TXT_NEWSLETTER_GO_TO_RECIPIENT_ADMINISTRATION']
            ));

            while (!$objUser->EOF) {
                $this->_objTpl->setVariable(array(
                    'NEWSLETTER_RECIPIENT_ROW_CLASS'            => $rowNr % 2 == 1 ? 'row1' : 'row2',
                    'NEWSLETTER_RECIPIENT_STATUS_IMG'            => $objUser->fields['status'] == 1 ? 'led_green.gif' : 'led_red.gif',
                    'NEWSLETTER_RECIPIENT_STATUS'                => $objUser->fields['status'] == 1 ? 'Aktiv' : 'Pendent',
                    'NEWSLETTER_RECIPIENT_ID'                    => $objUser->fields['id'],
                    'NEWSLETTER_RECIPIENT_EMAIL'                => htmlentities($objUser->fields['email'], ENT_QUOTES, CONTREXX_CHARSET),
                    'NEWSLETTER_RECIPIENT_LASTNAME'                => !empty($objUser->fields['lastname']) ? htmlentities($objUser->fields['lastname'], ENT_QUOTES, CONTREXX_CHARSET) : '-',
                    'NEWSLETTER_RECIPIENT_FIRSTNAME'            => !empty($objUser->fields['firstname']) ? htmlentities($objUser->fields['firstname'], ENT_QUOTES, CONTREXX_CHARSET) : '-',
                    'NEWSLETTER_RECIPIENT_STREET'                => !empty($objUser->fields['street']) ? htmlentities($objUser->fields['street'], ENT_QUOTES, CONTREXX_CHARSET) : '-',
                    'NEWSLETTER_RECIPIENT_ZIP'                    => !empty($objUser->fields['zip']) ? htmlentities($objUser->fields['zip'], ENT_QUOTES, CONTREXX_CHARSET) : '-',
                    'NEWSLETTER_RECIPIENT_CITY'                    => !empty($objUser->fields['city']) ? htmlentities($objUser->fields['city'], ENT_QUOTES, CONTREXX_CHARSET) : '-',
                    'NEWSLETTER_RECIPIENT_COUNTRY'                => !empty($objUser->fields['country']) ? htmlentities($objUser->fields['country'], ENT_QUOTES, CONTREXX_CHARSET) : '-',
                    'NEWSLETTER_RECIPIENT_REGISTRATION_DATE'    => date(ASCMS_DATE_FORMAT, $objUser->fields['emaildate'])
                ));
                $this->_objTpl->parse('newsletter_recipients');
                $rowNr++;
                $objUser->MoveNext();
            }

            $this->_objTpl->touchBlock('newsletter_recipients_list');
            $this->_objTpl->hideBlock('newsletter_recipients_no_data');
        } else {
            $this->_objTpl->setVariable('TXT_NEWSLETTER_NO_RECIPIENTS_MSG', $_ARRAYLANG['TXT_NEWSLETTER_NO_RECIPIENTS_MSG']);

            $this->_objTpl->touchBlock('newsletter_recipients_no_data');
            $this->_objTpl->hideBlock('newsletter_recipients_list');
        }

    }


    function _update()
    {
        global $objDatabase;



        $objColumns = $objDatabase->MetaColumns(DBPREFIX."module_newsletter");

        if ($objColumns !== false) {
            if ($objColumns['DATE_CREATE']->type != 'int') {
                $query = "SELECT `id`, `date_create` FROM ".DBPREFIX."module_newsletter";
                $objNewsletter = $objDatabase->Execute($query);
                if ($objNewsletter !== false) {
                    $arrNewsletter = array();
                    while (!$objNewsletter->EOF) {
                        $arrNewsletter[$objNewsletter->fields['id']] = $objNewsletter->fields['date_create'];
                        $objNewsletter->MoveNext();
                    }

                    $query = "ALTER TABLE ".DBPREFIX."module_newsletter CHANGE `date_create` `date_create` INT( 14 ) UNSIGNED NOT NULL";
                    if ($objDatabase->Execute($query) === false) {
                        die('DB error: '.$query);
                    }

                    foreach ($arrNewsletter as $id => $dateCreate) {
                        $date = mktime(0,0,0,intval(substr($dateCreate,5,2)),intval(substr($dateCreate,8,2)),intval(substr($dateCreate,0,4)));
                        $query = "UPDATE ".DBPREFIX."module_newsletter SET `date_create`=".$date." WHERE `id`=".$id;
                        if ($objDatabase->Execute($query) === false) {
                            print "DB error: ".$query."<br />";
                        }
                    }
                }
            }

            if ($objColumns['DATE_SENT']->type != 'int') {
                $query = "SELECT `id`, `date_sent` FROM ".DBPREFIX."module_newsletter";
                $objNewsletter = $objDatabase->Execute($query);
                if ($objNewsletter !== false) {
                    $arrNewsletter = array();
                    while (!$objNewsletter->EOF) {
                        $arrNewsletter[$objNewsletter->fields['id']] = $objNewsletter->fields['date_sent'];
                        $objNewsletter->MoveNext();
                    }

                    $query = "ALTER TABLE ".DBPREFIX."module_newsletter CHANGE `date_sent` `date_sent` INT( 14 ) UNSIGNED NOT NULL";
                    if ($objDatabase->Execute($query) === false) {
                        die('DB error: '.$query);
                    }

                    foreach ($arrNewsletter as $id => $dateSent) {
                        $date = mktime(0,0,0,intval(substr($dateSent,5,2)),intval(substr($dateSent,8,2)),intval(substr($dateSent,0,4)));
                        $query = "UPDATE ".DBPREFIX."module_newsletter SET `date_sent`=".$date." WHERE `id`=".$id;
                        if ($objDatabase->Execute($query) === false) {
                            print "DB error: ".$query."<br />";
                        }
                    }
                }
            }
        }
    }




    function _mails()
    {
        global $objDatabase, $_ARRAYLANG, $_CONFIG;

        $this->_pageTitle = $_ARRAYLANG['TXT_NEWSLETTER_EMAILS'];
        $this->_objTpl->loadTemplateFile('module_newsletter_mails.html');
        $rowNr = 0;
        $pos = isset($_GET['pos']) ? intval($_GET['pos']) : 0;

        $this->_objTpl->setVariable(array(
            'TXT_NEWSLETTER_SUBJECT'                        => $_ARRAYLANG['TXT_NEWSLETTER_SUBJECT'],
            'TXT_NEWSLETTER_EMAILS'                            => $_ARRAYLANG['TXT_NEWSLETTER_EMAILS'],
            'TXT_NEWSLETTER_SENT'                            => $_ARRAYLANG['TXT_NEWSLETTER_SENT'],
            'TXT_NEWSLETTER_SENDER'                            => $_ARRAYLANG['TXT_NEWSLETTER_SENDER'],
            'TXT_NEWSLETTER_FORMAT'                            => $_ARRAYLANG['TXT_NEWSLETTER_FORMAT'],
            'TXT_NEWSLETTER_TEMPLATE'                        => $_ARRAYLANG['TXT_NEWSLETTER_TEMPLATE'],
            'TXT_NEWSLETTER_DATE'                            => $_ARRAYLANG['TXT_NEWSLETTER_DATE'],
            'TXT_NEWSLETTER_FUNCTIONS'                        => $_ARRAYLANG['TXT_NEWSLETTER_FUNCTIONS'],
            'TXT_NEWSLETTER_CHECK_ALL'                        => $_ARRAYLANG['TXT_NEWSLETTER_CHECK_ALL'],
            'TXT_NEWSLETTER_UNCHECK_ALL'                    => $_ARRAYLANG['TXT_NEWSLETTER_UNCHECK_ALL'],
            'TXT_NEWSLETTER_WITH_SELECTED'                    => $_ARRAYLANG['TXT_NEWSLETTER_WITH_SELECTED'],
            'TXT_NEWSLETTER_DELETE'                            => $_ARRAYLANG['TXT_NEWSLETTER_DELETE'],
            'TXT_NEWSLETTER_CREATE_NEW_EMAIL'                => $_ARRAYLANG['TXT_NEWSLETTER_CREATE_NEW_EMAIL'],
            'TXT_NEWSLETTER_CONFIRM_DELETE_MAIL'            => $_ARRAYLANG['TXT_NEWSLETTER_CONFIRM_DELETE_MAIL'],
            'TXT_NEWSLETTER_CANNOT_UNDO_OPERATION'            => $_ARRAYLANG['TXT_NEWSLETTER_CANNOT_UNDO_OPERATION'],
            'TXT_NEWSLETTER_CONFIRM_DELETE_CHECKED_EMAILS'    => $_ARRAYLANG['TXT_NEWSLETTER_CONFIRM_DELETE_CHECKED_EMAILS']
        ));

        $this->_objTpl->setGlobalVariable(array(
            'TXT_NEWSLETTER_SEND_EMAIL'                        => $_ARRAYLANG['TXT_NEWSLETTER_SEND_EMAIL'],
            'TXT_NEWSLETTER_MODIFY_EMAIL'                    => $_ARRAYLANG['TXT_NEWSLETTER_MODIFY_EMAIL'],
            'TXT_NEWSLETTER_DISPLAY_EMAIL'                    => $_ARRAYLANG['TXT_NEWSLETTER_DISPLAY_EMAIL'],
            'TXT_NEWSLETTER_COPY_EMAIL'                        => $_ARRAYLANG['TXT_NEWSLETTER_COPY_EMAIL'],
            'TXT_NEWSLETTER_DELETE_EMAIL'                    => $_ARRAYLANG['TXT_NEWSLETTER_DELETE_EMAIL']
        ));

        $objMailCount = $objDatabase->SelectLimit("SELECT COUNT(1) AS mail_count FROM ".DBPREFIX."module_newsletter", 1);
        if ($objMailCount !== false) {
            $mailCount = $objMailCount->fields['mail_count'];
        } else {
            $mailCount = 0;
        }

        if ($mailCount > $_CONFIG['corePagingLimit']) {
            $paging = getPaging($mailCount, $pos, "&amp;cmd=newsletter&amp;act=mails", "", false, $_CONFIG['corePagingLimit']);
        }

        $arrTemplates = &$this->_getTemplates();

        $objMail = $objDatabase->SelectLimit("SELECT
            tblMail.id,
            tblMail.subject,
            tblMail.format,
            tblMail.date_create,
            tblMail.sender_email,
            tblMail.sender_name,
            tblMail.template,
            tblMail.status,
            tblMail.`count`,
            tblMail.date_sent
            FROM ".DBPREFIX."module_newsletter AS tblMail
            ORDER BY status, id DESC", $_CONFIG['corePagingLimit'], $pos);
        if ($objMail !== false) {
            $arrMailRecipientCount = $this->_getMailRecipientCount(NULL, $_CONFIG['corePagingLimit'], $pos);
            while (!$objMail->EOF) {
                $this->_objTpl->setVariable(array(
                    'NEWSLETTER_MAIL_ROW_CLASS'        => $rowNr % 2 == 1 ? 'row1' : 'row2',
                    'NEWSLETTER_MAIL_SUBJECT'        => htmlentities($objMail->fields['subject'], ENT_QUOTES, CONTREXX_CHARSET),
                    'NEWSLETTER_MAIL_SENDER_NAME'    => htmlentities($objMail->fields['sender_name'], ENT_QUOTES, CONTREXX_CHARSET),
                    'NEWSLETTER_MAIL_SENDER_EMAIL'    => htmlentities($objMail->fields['sender_email'], ENT_QUOTES, CONTREXX_CHARSET),
                    'NEWSLETTER_MAIL_SENT_DATE'        => $objMail->fields['date_sent'] > 0 ? date(ASCMS_DATE_FORMAT, $objMail->fields['date_sent']) : '-',
                    'NEWSLETTER_MAIL_FORMAT'        => $objMail->fields['format'],
                    'NEWSLETTER_MAIL_TEMPLATE'        => htmlentities($arrTemplates[$objMail->fields['template']]['name'], ENT_QUOTES, CONTREXX_CHARSET),
                    'NEWSLETTER_MAIL_DATE'            => date(ASCMS_DATE_FORMAT, $objMail->fields['date_create']),
                    'NEWSLETTER_MAIL_COUNT'            => $objMail->fields['count'],
					'NEWSLETTER_MAIL_USERS'			=> isset($arrMailRecipientCount[$objMail->fields['id']]) ? $arrMailRecipientCount[$objMail->fields['id']] : 0
                ));

                $this->_objTpl->setGlobalVariable('NEWSLETTER_MAIL_ID', $objMail->fields['id']);

                if ($objMail->fields['date_sent'] > 0) {
                    $this->_objTpl->touchBlock('newsletter_mail_show');
                    $this->_objTpl->hideBlock('newsletter_mail_edit');
                } else {
                    $this->_objTpl->touchBlock('newsletter_mail_edit');
                    $this->_objTpl->hideBlock('newsletter_mail_show');
                }

                $this->_objTpl->parse("newsletter_list");
                $objMail->MoveNext();
                $rowNr++;
            }
            if ($rowNr > 0) {
                $this->_objTpl->touchBlock("newsletter_list_multiAction");

                $this->_objTpl->setVariable('NEWSLETTER_MAILS_PAGING', "<br />".$paging."<br />");
            } else {
                $this->_objTpl->hideBlock("newsletter_list_multiAction");
            }
        }
    }

    function _deleteMail()
    {
        global $objDatabase, $_ARRAYLANG;

        $status = true;
        $arrMailIds = array();
        if (isset($_GET['id'])) {
            array_push($arrMailIds, intval($_GET['id']));
        } elseif (isset($_POST['newsletter_mail_selected'])) {
            foreach ($_POST['newsletter_mail_selected'] as $mailId) {
                array_push($arrMailIds, intval($mailId));
            }
        }

        if (count($arrMailIds) > 0) {
            foreach ($arrMailIds as $mailId) {
                if ($objDatabase->Execute("DELETE FROM ".DBPREFIX."module_newsletter_attachment where newsletter=".$mailId) !== false &&
                    $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_newsletter_rel_cat_news where newsletter=".$mailId) !== false &&
                    $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_newsletter_tmp_sending where newsletter=".$mailId) !== false &&
                    $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_newsletter where id=".$mailId)) {
                } else {
                    $status = false;
                }
            }

            if ($status) {
                $this->_strOkMessage = count($arrMailIds) > 1 ? $_ARRAYLANG['TXT_NEWSLETTER_EMAILS_DELETED'] : $_ARRAYLANG['TXT_NEWSLETTER_EMAIL_DELETED'];
            } else {
                $this->_strErrMessage = count($arrMailIds) > 1 ? $_ARRAYLANG['TXT_NEWSLETTER_ERROR_DELETE_EMAILS'] : $_ARRAYLANG['TXT_NEWSLETTER_ERROR_DELETE_EMAIL'];
            }
        }
        $this->_mails();
    }


    function _copyMail()
    {
        $this->_editMail(true);
    }

    function _getBodyContent($fullContent)
    {
        $posBody = 0;
        $posStartBodyContent = 0;
        $arrayMatches = array();
        $res = preg_match_all('/<body[^>]*>/i', $fullContent, $arrayMatches);
        if ($res==true) {
            $bodyStartTag = $arrayMatches[0][0];
            // Position des Start-Tags holen
            $posBody = strpos($fullContent, $bodyStartTag, 0);
            // Beginn des Contents ohne Body-Tag berechnen
            $posStartBodyContent = $posBody + strlen($bodyStartTag);
        }
        $posEndTag=strlen($fullContent);
        $res = preg_match_all('/<\/body>/i', $fullContent, $arrayMatches);
        if ($res == true) {
            $bodyEndTag=$arrayMatches[0][0];
            // Position des End-Tags holen
            $posEndTag = strpos($fullContent, $bodyEndTag, 0);
            // Content innerhalb der Body-Tags auslesen
         }
         $content = substr($fullContent, $posStartBodyContent, $posEndTag  - $posStartBodyContent);
         return $content;
    }



    function _addMailAttachment($attachment, $mailId = 0)
    {
        global $objDatabase;

        $fileName = substr($attachment, strrpos($attachment, '/')+1);

        $objAttachment = $objDatabase->SelectLimit("SELECT id FROM ".DBPREFIX."module_newsletter_attachment WHERE file_name='".$fileName."'", 1);
        if ($objAttachment !== false) {
            if ($objAttachment->RecordCount() == 1) {
                $md5Current = @md5_file(ASCMS_NEWSLETTER_ATTACH_PATH.'/'.$fileName);
                $md5New = @md5_file(ASCMS_PATH.$attachment);

                if ($md5Current !== false && $md5Current === $md5New) {
                    if ($objDatabase->Execute("    INSERT INTO ".DBPREFIX."module_newsletter_attachment (`newsletter`, `file_name`)
                                                VALUES (".$mailId.", '".$fileName."')") !== false) {
                        return true;
                    }
                }
            }

            $nr = 0;
            $fileNameTmp = $fileName;
            while (file_exists(ASCMS_NEWSLETTER_ATTACH_PATH.'/'.$fileNameTmp)) {
                $md5Current = @md5_file(ASCMS_NEWSLETTER_ATTACH_PATH.'/'.$fileNameTmp);
                $md5New = @md5_file(ASCMS_PATH.$attachment);

                if ($md5Current !== false && $md5Current === $md5New) {
                    if ($objDatabase->Execute("INSERT INTO ".DBPREFIX."module_newsletter_attachment (`newsletter`, `file_name`) VALUES (".$mailId.", '".$fileNameTmp."')") !== false) {
                        return true;
                    }
                }
                $nr++;
                $PathInfo = pathinfo($fileName);
                $fileNameTmp = substr($PathInfo['basename'],0,strrpos($PathInfo['basename'],'.')).$nr.'.'.$PathInfo['extension'];
            }

            if (copy(ASCMS_PATH.$attachment, ASCMS_NEWSLETTER_ATTACH_PATH.'/'.$fileNameTmp)) {
                if ($objDatabase->Execute("INSERT INTO ".DBPREFIX."module_newsletter_attachment (`newsletter`, `file_name`) VALUES (".$mailId.", '".$fileNameTmp."')") !== false) {
                    return true;
                }
            }
        }
        return false;
    }

    function _removeMailAttachment($attachment, $mailId = 0)
    {
        global $objDatabase;

        $fileName = substr($attachment, strrpos($attachment, '/')+1);
        $objAttachment = $objDatabase->SelectLimit("SELECT id FROM ".DBPREFIX."module_newsletter_attachment WHERE file_name='".$fileName."'", 2);
        if ($objAttachment !== false) {
            if ($objAttachment->RecordCount() < 2) {
                @unlink(ASCMS_NEWSLETTER_ATTACH_PATH.'/'.$fileName);
            }

            if ($objDatabase->SelectLimit("DELETE FROM ".DBPREFIX."module_newsletter_attachment WHERE file_name='".$fileName."' AND newsletter=".$mailId, 1) !== false) {
                return true;
            }
        }
        return false;
    }

    function _getMailPriorityMenu($selectedPriority = 3, $attributes = '')
    {
        global $_ARRAYLANG;

        $menu = "<select".(!empty($attributes) ? " ".$attributes : "").">\n";
        foreach ($this->_arrMailPriority as $priorityId => $priority) {
            $menu .= "<option value=\"".$priorityId."\"".($selectedPriority == $priorityId ? "selected=\"selected\"" : "").">".$_ARRAYLANG[$priority]."</option>\n";
        }
        $menu .= "</select>\n";

        return $menu;
    }

    function _getMailFormatMenu($selectedFormat = '', $attributes = '')
    {
        global $_ARRAYLANG;

        $menu = "<select".(!empty($attributes) ? " ".$attributes : "").">\n";
        foreach ($this->_arrMailFormat as $format => $formatTXT) {
            $menu .= "<option value=\"".$format."\"".($selectedFormat == $format ? " selected=\"selected\"" : "").">".$_ARRAYLANG[$formatTXT]."</option>\n";
        }
        $menu .= "</select>\n";

        return $menu;
    }

    function _addMail($subject, $format, $template, $senderMail, $senderName, $replyMail, $smtpServer, $priority, $arrAttachment, $htmlContent, $textContent)
    {
        global $objDatabase, $_ARRAYLANG;

        if (!empty($subject)) {
            if ($objDatabase->Execute("INSERT INTO ".DBPREFIX."module_newsletter
                (subject,
                template,
                content,
                content_text,
                attachment,
                format,
                priority,
                sender_email,
                sender_name,
                return_path,
                smtp_server,
                date_create
                ) VALUES (
                '".addslashes($subject)."',
                ".intval($template).",
                '".addslashes($htmlContent)."',
                '".addslashes($textContent)."',
                '".(count($arrAttachment) > 0 ? '1' : '0')."',
                '".addslashes($format)."',
                ".intval($priority).",
                '".addslashes($senderMail)."',
                '".addslashes($senderName)."',
                '".addslashes($replyMail)."',
                ".intval($smtpServer).",
                ".time().")") !== false) {
                $mailId = $objDatabase->Insert_ID();
                return $mailId;
            } else {
                $this->_strErrMessage .= $_ARRAYLANG['TXT_NEWSLETTER_ERROR_SAVE_RETRY'];
            }
        } else {
            $this->_strErrMessage .= $_ARRAYLANG['TXT_NEWSLETTER_ERROR_NO_SUBJECT'];
        }
        return false;
    }

    function _updateMail($mailId, $subject, $format, $template, $senderMail, $senderName, $replyMail, $smtpServer, $priority, $arrAttachment, $htmlContent, $textContent)
    {
        global $objDatabase, $_ARRAYLANG;

        if (!empty($subject)) {
            if ($objDatabase->Execute("UPDATE ".DBPREFIX."module_newsletter
                SET subject='".addslashes($subject)."',
                template=".intval($template).",
                content='".addslashes($htmlContent)."',
                content_text='".addslashes($textContent)."',
                attachment='".(count($arrAttachment) > 0 ? '1' : '0')."',
                format='".addslashes($format)."',
                priority=".intval($priority).",
                sender_email='".addslashes($senderMail)."',
                sender_name='".addslashes($senderName)."',
                return_path='".addslashes($replyMail)."',
                smtp_server=".intval($smtpServer)."
                WHERE id=".$mailId) !== false) {
                return true;
            } else {
                $this->_strErrMessage .= $_ARRAYLANG['TXT_NEWSLETTER_ERROR_SAVE_RETRY'];
            }
        } else {
            $this->_strErrMessage .= $_ARRAYLANG['TXT_NEWSLETTER_ERROR_NO_SUBJECT'];
        }
        return false;
    }

    function _setMailLists($mailId, $arrLists)
    {
        global $objDatabase;

        $arrCurrentList = array();

        $objRelList = $objDatabase->Execute("SELECT category FROM ".DBPREFIX."module_newsletter_rel_cat_news WHERE newsletter=".$mailId);
        if (!$objRelList) {
            return false;
        }
        while (!$objRelList->EOF) {
            array_push($arrCurrentList, $objRelList->fields['category']);
            $objRelList->MoveNext();
        }

        $arrNewLists = array_diff($arrLists, $arrCurrentList);
        $arrRemovedLists = array_diff($arrCurrentList, $arrLists);

        foreach ($arrNewLists as $listId) {
            $objDatabase->Execute("
                INSERT INTO ".DBPREFIX."module_newsletter_rel_cat_news (
                    `newsletter`, `category`
                ) VALUES (
                    $mailId, $listId
                )
            ");
        }

        foreach ($arrRemovedLists as $listId) {
            $objDatabase->Execute("
                DELETE FROM ".DBPREFIX."module_newsletter_rel_cat_news
                 WHERE newsletter=$mailId
                   AND category=$listId
            ");
        }
        return true;
    }

    function _checkUniqueListName($listId, $listName) {
        global $objDatabase;

        $result = $objDatabase->SelectLimit("SELECT id FROM ".DBPREFIX."module_newsletter_category WHERE `name`='".$listName."' AND `id`!=".$listId, 1);
        if ($result !== false) {
            if ($result->RecordCount() == 0) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    function ConfigHTML()
    {
        global $_ARRAYLANG;

        $this->_pageTitle = $_ARRAYLANG['TXT_SETTINGS'];
        $this->_objTpl->loadTemplateFile('newsletter_config_html.html');

        $this->_objTpl->setVariable(array(
            'HTML_CODE'             => htmlentities($this->_getHTML(), ENT_QUOTES, CONTREXX_CHARSET),
            'TXT_TITLE'                => $_ARRAYLANG['TXT_GENERATE_HTML'],
            'TXT_SELECT_ALL'         => $_ARRAYLANG['TXT_SELECT_ALL'],
            'TXT_DISPATCH_SETINGS'    => $_ARRAYLANG['TXT_DISPATCH_SETINGS'],
            'TXT_GENERATE_HTML'        => $_ARRAYLANG['TXT_GENERATE_HTML'],
            'TXT_PLACEHOLDER'        => $_ARRAYLANG['TXT_PLACEHOLDER'],
            'TXT_CONFIRM_MAIL'         => $_ARRAYLANG['TXT_NEWSLETTER_CONFIRMATION_EMAIL'],
            'TXT_ACTIVATE_MAIL'        => $_ARRAYLANG['TXT_NEWSLETTER_ACTIVATION_EMAIL'],
            'TXT_NOTIFICATION_MAIL'     => $_ARRAYLANG['TXT_NEWSLETTER_NOTIFICATION_MAIL'],
            'TXT_SYSTEM_SETINGS'        => "System",
        ));
    }

    function ConfigDispatch() {
        global $objDatabase, $_ARRAYLANG;
        $this->_pageTitle = $_ARRAYLANG['TXT_SETTINGS'];
        $this->_objTpl->loadTemplateFile('newsletter_config_dispatch.html');
        $this->_objTpl->setVariable('TXT_TITLE', $_ARRAYLANG['TXT_DISPATCH_SETINGS']);

        if ($_POST["update"]=="exe") {
            $objDatabase->Execute("UPDATE ".DBPREFIX."module_newsletter_settings SET setvalue='".contrexx_addslashes($_POST['sender_email'])."' WHERE setname='sender_mail'");
            $objDatabase->Execute("UPDATE ".DBPREFIX."module_newsletter_settings SET setvalue='".contrexx_addslashes($_POST['sender_name'])."' WHERE setname='sender_name'");
            $objDatabase->Execute("UPDATE ".DBPREFIX."module_newsletter_settings SET setvalue='".contrexx_addslashes($_POST['return_path'])."' WHERE setname='reply_mail'");
            $objDatabase->Execute("UPDATE ".DBPREFIX."module_newsletter_settings SET setvalue='".intval($_POST['mails_per_run'])."' WHERE setname='mails_per_run'");
            //$objDatabase->Execute("UPDATE ".DBPREFIX."module_newsletter_settings SET setvalue='".contrexx_addslashes($_POST['bcc_mail'])."' WHERE setname='bcc_mail'");
            $objDatabase->Execute("UPDATE ".DBPREFIX."module_newsletter_settings SET setvalue='".contrexx_addslashes($_POST['test_mail'])."' WHERE setname='test_mail'");
            $objDatabase->Execute("UPDATE ".DBPREFIX."module_newsletter_settings SET setvalue='".intval($_POST['overview_entries'])."' WHERE setname='overview_entries_limit'");
            $objDatabase->Execute("UPDATE ".DBPREFIX."module_newsletter_settings SET setvalue='".intval($_POST['text_break_after'])."' WHERE setname='text_break_after'");

            $objDatabase->Execute("UPDATE ".DBPREFIX."module_newsletter_settings SET setvalue='".contrexx_addslashes($_POST['newsletter_rejected_mail_task'])."' WHERE setname='rejected_mail_operation'");
        }

        // Load Values
        // -------------
        $objSettings = $objDatabase->Execute("SELECT setname, setvalue FROM ".DBPREFIX."module_newsletter_settings");
        if ($objSettings !== false) {
            while (!$objSettings->EOF) {
                $arrSettings[$objSettings->fields['setname']] = $objSettings->fields['setvalue'];
                $objSettings->MoveNext();
            }
        }
        $this->_objTpl->setVariable(array(
            'TXT_SETTINGS'                    => $_ARRAYLANG['TXT_SETTINGS'],
            'TXT_SENDER'                     => $_ARRAYLANG['TXT_SENDER'],
            'TXT_LASTNAME'                     => $_ARRAYLANG['TXT_LASTNAME'],
            'TXT_RETURN_PATH'                 => $_ARRAYLANG['TXT_RETURN_PATH'],
            'TXT_SEND_LIMIT'                 => $_ARRAYLANG['TXT_SEND_LIMIT'],
            'TXT_SAVE'                        => $_ARRAYLANG['TXT_SAVE'],
            'TXT_FILL_OUT_ALL_REQUIRED_FIELDS' => $_ARRAYLANG['TXT_FILL_OUT_ALL_REQUIRED_FIELDS'],
            'SENDERMAIL_VALUE'                => $arrSettings['sender_mail'],
            'SENDERNAME_VALUE'                => $arrSettings['sender_name'],
            'RETURNPATH_VALUE'                => $arrSettings['reply_mail'],
            'MAILSPERRUN_VALUE'                => $arrSettings['mails_per_run'],
            //'BCC_VALUE'                    => $arrSettings['bcc_mail'],
            'OVERVIEW_ENTRIES_VALUE'        => $arrSettings['overview_entries_limit'],
            'TEST_MAIL_VALUE'                => $arrSettings['test_mail'],
            'BREAK_AFTER_VALUE'                => $arrSettings['text_break_after'],
            'TXT_WILDCART_INFOS'            => $_ARRAYLANG['TXT_WILDCART_INFOS'],
            'TXT_USER_DATA'                    => $_ARRAYLANG["TXT_USER_DATA"],
            'TXT_EMAIL_ADDRESS'                => $_ARRAYLANG['TXT_EMAIL_ADDRESS'],
            'TXT_LASTNAME'                    => $_ARRAYLANG['TXT_LASTNAME'],
            'TXT_FIRSTNAME'                    => $_ARRAYLANG['TXT_FIRSTNAME'],
            'TXT_STREET'                    => $_ARRAYLANG['TXT_STREET'],
            'TXT_ZIP'                        => $_ARRAYLANG['TXT_ZIP'],
            'TXT_CITY'                        => $_ARRAYLANG['TXT_CITY'],
            'TXT_COUNTRY'                    => $_ARRAYLANG['TXT_COUNTRY'],
            'TXT_PHONE'                        => $_ARRAYLANG['TXT_PHONE'],
            'TXT_BIRTHDAY'                    => $_ARRAYLANG['TXT_BIRTHDAY'],
            'TXT_GENERALLY'                    => $_ARRAYLANG['TXT_GENERALLY'],
            'TXT_DATE'                        => $_ARRAYLANG['TXT_DATE'],
            'TXT_NEWSLETTER_CONTENT'        => $_ARRAYLANG['TXT_NEWSLETTER_CONTENT'],
            'TXT_CONFIRM_MAIL'              => $_ARRAYLANG['TXT_NEWSLETTER_CONFIRMATION_EMAIL'],

            'TXT_NOTIFICATION_MAIL'     => $_ARRAYLANG['TXT_NEWSLETTER_NOTIFICATION_MAIL'],
               'TXT_ACTIVATE_MAIL'             => $_ARRAYLANG['TXT_NEWSLETTER_ACTIVATION_EMAIL'],
            'TXT_DISPATCH_SETINGS'            => $_ARRAYLANG['TXT_DISPATCH_SETINGS'],
            'TXT_GENERATE_HTML'                => $_ARRAYLANG['TXT_GENERATE_HTML'],
            'TXT_BREAK_AFTER'                => $_ARRAYLANG['TXT_NEWSLETTER_BREAK_AFTER'],
            'TXT_TEST_MAIL'                    => $_ARRAYLANG['TXT_NEWSLETTER_TEST_RECIPIENT'],
            'TXT_FAILED'                    => $_ARRAYLANG['TXT_NEWSLETTER_FAILED'],
//            'TXT_BCC'                        => $_ARRAYLANG['TXT_NEWSLETTER_BCC'],
            'TXT_NEWSLETTER_OVERVIEW_ENTRIES'   => $_ARRAYLANG['TXT_NEWSLETTER_OVERVIEW_ENTRIES'],
            'TXT_NEWSLETTER_REPLY_EMAIL'        => $_ARRAYLANG['TXT_NEWSLETTER_REPLY_EMAIL'],
            'TXT_SYSTEM_SETINGS'        => "System",
            'TXT_NEWSLETTER_DO_NOTING'            => $_ARRAYLANG['TXT_NEWSLETTER_DO_NOTING'],
            'TXT_NEWSLETTER_TASK_REJECTED_EMAIL'    => $_ARRAYLANG['TXT_NEWSLETTER_TASK_REJECTED_EMAIL'],
            'TXT_NEWSLETTER_DEACTIVATE_EMAIL'    => $_ARRAYLANG['TXT_NEWSLETTER_DEACTIVATE_EMAIL'],
            'TXT_NEWSLETTER_DELETE_EMAIL_ADDRESS'        => $_ARRAYLANG['TXT_NEWSLETTER_DELETE_EMAIL_ADDRESS'],
            'NEWSLETTER_REJECTED_MAIL_IGNORE'    => $arrSettings['rejected_mail_operation'] == 'ignore' ? 'checked="checked"' : '',
            'NEWSLETTER_REJECTED_MAIL_DEACTIVATE'    => $arrSettings['rejected_mail_operation'] == 'deactivate' ? 'checked="checked"' : '',
            'NEWSLETTER_REJECTED_MAIL_DELETE'        => $arrSettings['rejected_mail_operation'] == 'delete' ? 'checked="checked"' : ''
        ));

    }

    function _templates()
    {
        global $objDatabase, $_ARRAYLANG;

        $rowNr = 0;
        $this->_pageTitle = $_ARRAYLANG['TXT_NEWSLETTER_TEMPLATES'];
        $this->_objTpl->loadTemplateFile('module_newsletter_templates.html');

        $this->_objTpl->setVariable(array(
            'TXT_NEWSLETTER_CANNOT_UNDO_OPERATION'        => $_ARRAYLANG['TXT_NEWSLETTER_CANNOT_UNDO_OPERATION'],
            'TXT_NEWSLETTER_CONFIRM_DELETE_TEMPLATE'    => $_ARRAYLANG['TXT_NEWSLETTER_CONFIRM_DELETE_TEMPLATE'],
            'TXT_NEWSLETTER_TEMPLATES'                    => $_ARRAYLANG['TXT_NEWSLETTER_TEMPLATES'],
            'TXT_NEWSLETTER_ID_UC'                        => $_ARRAYLANG['TXT_NEWSLETTER_ID_UC'],
            'TXT_NEWSLETTER_NAME'                        => $_ARRAYLANG['TXT_NEWSLETTER_NAME'],
            'TXT_NEWSLETTER_DESCRIPTION'                => $_ARRAYLANG['TXT_NEWSLETTER_DESCRIPTION'],
            'TXT_NEWSLETTER_FUNCTIONS'                    => $_ARRAYLANG['TXT_NEWSLETTER_FUNCTIONS'],
            'TXT_TEMPLATE_ADD_NEW_TEMPLATE'                => $_ARRAYLANG['TXT_TEMPLATE_ADD_NEW_TEMPLATE']
        ));

        $this->_objTpl->setGlobalVariable(array(
            'TXT_NEWSLETTER_MODIFY_TEMPLATE'    => $_ARRAYLANG['TXT_NEWSLETTER_MODIFY_TEMPLATE'],
            'TXT_NEWSLETTER_DELETE_TEMPLATE'    => $_ARRAYLANG['TXT_NEWSLETTER_DELETE_TEMPLATE']
        ));

        $objTemplate = $objDatabase->Execute("SELECT id, name, required, description FROM ".DBPREFIX."module_newsletter_template ORDER BY id DESC");
        if ($objTemplate !== false) {
            while (!$objTemplate->EOF) {
                if ($objTemplate->fields['required'] == 0) {
                    $this->_objTpl->touchBlock('newsletter_template_delete');
                    $this->_objTpl->hideBlock('newsletter_templalte_spacer');
                } else {
                    $this->_objTpl->hideBlock('newsletter_template_delete');
                    $this->_objTpl->touchBlock('newsletter_templalte_spacer');
                }

                $this->_objTpl->setVariable(array(
                    'NEWSLETTER_TEMPLATE_ROW_CLASS'        => $rowNr % 2 == 1 ? 'row1' : 'row2',
                    'NEWSLETTER_TEMPLATE_ID'             => $objTemplate->fields['id'],
                    'NEWSLETTER_TEMPLATE_NAME'            => htmlentities($objTemplate->fields['name'], ENT_QUOTES, CONTREXX_CHARSET),
                    'NEWSLETTER_TEMPLATE_NAME_JS'        => htmlentities(addslashes($objTemplate->fields['name']), ENT_QUOTES, CONTREXX_CHARSET),
                    'NEWSLETTER_TEMPLATE_DESCRIPTION'    => htmlentities($objTemplate->fields['description'], ENT_QUOTES, CONTREXX_CHARSET)
                ));

                $rowNr++;
                $this->_objTpl->parse("templates_row");
                $objTemplate->MoveNext();
            }
        }
    }


    function _updateTemplate($id, $name, $description, $html, $text)
    {
        global $objDatabase;
        if ($objDatabase->Execute("UPDATE ".DBPREFIX."module_newsletter_template SET name='".addslashes($name)."', description='".addslashes($description)."', html='".addslashes($html)."', text='".addslashes($text)."' WHERE id=".$id) !== false) {
            return true;
        } else {
             return false;
        }
    }

    function _addTemplate($name, $description, $html, $text)
    {
        global $objDatabase;
        if ($objDatabase->Execute("INSERT INTO ".DBPREFIX."module_newsletter_template (`name`, `description`, `html`, `text`) VALUES ('".addslashes($name)."', '".addslashes($description)."', '".addslashes($html)."', '".addslashes($text)."')") !== false) {
            return true;
        } else {
             return false;
        }
    }

    function _editTemplate()
    {
        global $objDatabase, $_ARRAYLANG;

        $id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
        $name = '';
        $description = '';
        $html = "<html>\n<head>\n<title>[[subject]]</title>\n</head>\n<body>\n[[content]]\n<br />\n<br />\n[[profile_setup]]\n[[unsubscribe]]\n</body>\n</html>";
        $text = "[[content]]\n\n\n[[profile_setup]]\n[[unsubscribe]]";
        $saveStatus = true;

        if (isset($_POST['newsletter_template_save'])) {
            if (!empty($_POST['template_edit_name'])) {
                $name = contrexx_stripslashes($_POST['template_edit_name']);
            } else {
                $saveStatus = false;
                $this->_strErrMessage .= $_ARRAYLANG['TXT_NEWSLETTER_DEFINE_TEMPLATE_NAME']."<br />";
            }

            if (isset($_POST['template_edit_description'])) {
                $description = contrexx_stripslashes($_POST['template_edit_description']);
            }

            if (isset($_POST['template_edit_html'])) {
                $html = contrexx_stripslashes($_POST['template_edit_html']);
            }
            $arrContentMatches = array();
            if (preg_match_all('/\[\[content\]\]/', $html, $arrContentMatches)) {
                if (count($arrContentMatches[0]) > 1) {
                    $saveStatus = false;
                    $this->_strErrMessage .= $_ARRAYLANG['TXT_NEWSLETTER_MAX_CONTENT_PLACEHOLDER_HTML_MSG']."<br />";
                }
            } else {
                $saveStatus = false;
                $this->_strErrMessage .= $_ARRAYLANG['TXT_NEWSLETTER_MIN_CONTENT_PLACEHOLDER_HTML_MSG']."<br />";
            }

            if (isset($_POST['template_edit_text'])) {
                $text = contrexx_stripslashes($_POST['template_edit_text']);
            }
            if (preg_match_all('/\[\[content\]\]/', $text, $arrContentMatches)) {
                if (count($arrContentMatches[0]) > 1) {
                    $saveStatus = false;
                    $this->_strErrMessage .= $_ARRAYLANG['TXT_NEWSLETTER_MAX_CONTENT_PLACEHOLDER_TEXT_MSG']."<br />";
                }
            } else {
                $saveStatus = false;
                $this->_strErrMessage .= $_ARRAYLANG['TXT_NEWSLETTER_MIN_CONTENT_PLACEHOLDER_TEXT_MSG']."<br />";
            }

            if ($saveStatus) {
                $objTemplate = $objDatabase->SelectLimit("SELECT id FROM ".DBPREFIX."module_newsletter_template WHERE id!=".$id." AND name='".addslashes($name)."'", 1);
                if ($objTemplate !== false && $objTemplate->RecordCount() == 0) {
                    if ($id > 0) {
                        $this->_updateTemplate($id, $name, $description, $html, $text);
                    } else {
                        $this->_addTemplate($name, $description, $html, $text);
                    }

                    return $this->_templates();
                } else {
                    $this->_strErrMessage = $_ARRAYLANG['TXT_NEWSLETTER_DUPLICATE_LIST_NAME_MSG'];
                }
            }
        } elseif ($id > 0) {
            $objTemplate = $objDatabase->SelectLimit("SELECT id, name, description, html, text FROM ".DBPREFIX."module_newsletter_template WHERE id=".$id, 1);
            if ($objTemplate !== false && $objTemplate->RecordCount() == 1) {
                $name = $objTemplate->fields['name'];
                $description = $objTemplate->fields['description'];
                $html = $objTemplate->fields['html'];
                $text = $objTemplate->fields['text'];
            }
        }

        $this->_pageTitle = $_ARRAYLANG['TXT_NEWSLETTER_TEMPLATES'];
        $this->_objTpl->loadTemplateFile('module_newsletter_template_edit.html');

        $this->_objTpl->setVariable(array(
            'TXT_NEWSLETTER_PLACEHOLDER_DIRECTORY'    => $_ARRAYLANG['TXT_NEWSLETTER_PLACEHOLDER_DIRECTORY'],
            'TXT_NEWSLETTER_NAME'                    => $_ARRAYLANG['TXT_NEWSLETTER_NAME'],
            'TXT_NEWSLETTER_DESCRIPTION'            => $_ARRAYLANG['TXT_NEWSLETTER_DESCRIPTION'],
            'TXT_NEWSLETTER_HTML_TEMPLATE'            => $_ARRAYLANG['TXT_NEWSLETTER_HTML_TEMPLATE'],
            'TXT_NEWSLETTER_TEXT_TEMPLATE'            => $_ARRAYLANG['TXT_NEWSLETTER_TEXT_TEMPLATE'],
            'TXT_NEWSLETTER_BACK'                    => $_ARRAYLANG['TXT_NEWSLETTER_BACK'],
            'TXT_NEWSLETTER_SAVE'                    => $_ARRAYLANG['TXT_NEWSLETTER_SAVE'],
            'TXT_NEWSLETTER_USER_DATA'                => $_ARRAYLANG['TXT_NEWSLETTER_USER_DATA'],
            'TXT_NEWSLETTER_EMAIL_ADDRESS'            => $_ARRAYLANG['TXT_NEWSLETTER_EMAIL_ADDRESS'],
            'TXT_NEWSLETTER_URI'            => $_ARRAYLANG['TXT_NEWSLETTER_URI'],
            'TXT_NEWSLETTER_SEX'                    => $_ARRAYLANG['TXT_NEWSLETTER_SEX'],
            'TXT_NEWSLETTER_TITLE'                    => $_ARRAYLANG['TXT_NEWSLETTER_TITLE'],
            'TXT_NEWSLETTER_LASTNAME'                => $_ARRAYLANG['TXT_NEWSLETTER_LASTNAME'],
            'TXT_NEWSLETTER_FIRSTNAME'                => $_ARRAYLANG['TXT_NEWSLETTER_FIRSTNAME'],
            'TXT_NEWSLETTER_STREET'                    => $_ARRAYLANG['TXT_NEWSLETTER_STREET'],
            'TXT_NEWSLETTER_ZIP'                    => $_ARRAYLANG['TXT_NEWSLETTER_ZIP'],
            'TXT_NEWSLETTER_CITY'                    => $_ARRAYLANG['TXT_NEWSLETTER_CITY'],
            'TXT_NEWSLETTER_COUNTRY'                => $_ARRAYLANG['TXT_NEWSLETTER_COUNTRY'],
            'TXT_NEWSLETTER_PHONE'                    => $_ARRAYLANG['TXT_NEWSLETTER_PHONE'],
            'TXT_NEWSLETTER_BIRTHDAY'                => $_ARRAYLANG['TXT_NEWSLETTER_BIRTHDAY'],
            'TXT_NEWSLETTER_GENERAL'                => $_ARRAYLANG['TXT_NEWSLETTER_GENERAL'],
            'TXT_NEWSLETTER_CONTENT'                => $_ARRAYLANG['TXT_NEWSLETTER_CONTENT'],
            'TXT_NEWSLETTER_PROFILE_SETUP'            => $_ARRAYLANG['TXT_NEWSLETTER_PROFILE_SETUP'],
            'TXT_NEWSLETTER_UNSUBSCRIBE'            => $_ARRAYLANG['TXT_NEWSLETTER_UNSUBSCRIBE'],
            'TXT_NEWSLETTER_DATE'                    => $_ARRAYLANG['TXT_NEWSLETTER_DATE']
        ));

        $this->_objTpl->setVariable(array(
            'NEWSLETTER_TEMPLATE_ID'            => $id,
            'NEWSLETTER_TEMPLATE_NAME'            => htmlentities($name, ENT_QUOTES, CONTREXX_CHARSET),
            'NEWSLETTER_TEMPLATE_DESCRIPTION'    => htmlentities($description, ENT_QUOTES, CONTREXX_CHARSET),
            'NEWSLETTER_TEMPLATE_HTML'             => get_wysiwyg_editor('template_edit_html', $html, 'fullpage', null, true),
            'NEWSLETTER_TEMPLATE_TEXT'             => htmlentities($text, ENT_QUOTES, CONTREXX_CHARSET),
            'NEWSLETTER_TEMPLATE_TITLE_TEXT'    => $id > 0 ? $_ARRAYLANG['TXT_NEWSLETTER_MODIFY_TEMPLATE'] : $_ARRAYLANG['TXT_NEWSLETTER_TEMPLATE_ADD']
        ));
        return true;
    }

    function delTemplate()
    {
        global $objDatabase, $_ARRAYLANG;

        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($id > 0) {
            $objResult = $objDatabase->SelectLimit("SELECT id FROM ".DBPREFIX."module_newsletter WHERE template=".$id, 1);
            if ($objResult !== false) {
                if ($objResult->RecordCount() == 1) {
                    $this->_strErrMessage = $_ARRAYLANG['TXT_NEWSLETTER_TEMPLATE_STILL_IN_USE'];
                    return false;
                } else {
                    if ($objDatabase->Execute("DELETE FROM ".DBPREFIX."module_newsletter_template WHERE required=0 AND id=".$id) !== false) {
                        $this->_strOkMessage = $_ARRAYLANG['TXT_NEWSLETTER_TEMPLATE_DELETED'];
                        return true;
                    }
                }
            }
        }

        $this->_strErrMessage = $_ARRAYLANG['TXT_NEWSLETTER_TEMPLATE_DELETE_ERROR'];
        return false;
    }

    function ActivateMail() {
        global $objDatabase, $_ARRAYLANG;
        $this->_pageTitle = $_ARRAYLANG['TXT_SETTINGS'];
        $this->_objTpl->loadTemplateFile('newsletter_config_activatemail.html');
        $this->_objTpl->setVariable('TXT_TITLE', $_ARRAYLANG['TXT_DISPATCH_SETINGS']);

        if ($_POST["update"]=="exe") {
            if ($objDatabase->Execute("
                UPDATE ".DBPREFIX."module_newsletter_confirm_mail
                   SET title='".contrexx_addslashes($_POST["mailSubject"])."',
                       content='".contrexx_addslashes($_POST["mailContent"])."'
                 WHERE id=1")) {
                $this->_strOkMessage = $_ARRAYLANG['TXT_DATA_RECORD_STORED_SUCCESSFUL'];
            } else {
                $this->_strErrMessage = $_ARRAYLANG['TXT_DATABASE_ERROR'];
            }
        }

        $query         = "SELECT id, title, content FROM ".DBPREFIX."module_newsletter_confirm_mail WHERE id='1'";
        $objResult     = $objDatabase->Execute($query);
        if ($objResult !== false) {
            $subject = $objResult->fields['title'];
            $content = $objResult->fields['content'];
        }

        $this->_objTpl->setVariable(array(
            'TXT_LASTNAME'                 => $_ARRAYLANG['TXT_LASTNAME'],
            'TXT_WILDCART_INFOS'        => $_ARRAYLANG['TXT_WILDCART_INFOS'],
            'TXT_NEWSLETTER_SEX'        => $_ARRAYLANG['TXT_NEWSLETTER_SEX'],
            'TXT_U_TITLE'                => $_ARRAYLANG['TXT_NEWSLETTER_TITLE'],
            'TXT_FIRSTNAME'                => $_ARRAYLANG['TXT_NEWSLETTER_FIRSTNAME'],
            'TXT_DATE'                    => $_ARRAYLANG['TXT_NEWSLETTER_REGISTRATION_DATE'],
            'TXT_CONTENT'                => $_ARRAYLANG['TXT_NEWSLETTER_CONTENT'],
            'TXT_SUBJECT'                => $_ARRAYLANG['TXT_NEWSLETTER_SUBJECT'],
            'TXT_TEXT'                    => $_ARRAYLANG['TXT_NEWSLETTER_TEXT'],
            'TXT_URL'                    => $_ARRAYLANG['TXT_NEWSLETTER_URL'],
            'TXT_CONFIRMMAIL'            => $_ARRAYLANG['TXT_NEWSLETTER_ACTIVATION_EMAIL'],
            'TXT_CODE'                    => $_ARRAYLANG['TXT_NEWSLETTER_ACTIVATION_CODE'],
            'TXT_SAVE'                    => $_ARRAYLANG['TXT_SAVE'],
            'MAIL_SUBJECT'                => $subject,
            'MAIL_CONTENT'                => $content,
            'TXT_CONFIRM_MAIL'            => $_ARRAYLANG['TXT_NEWSLETTER_CONFIRMATION_EMAIL'],
            'TXT_ACTIVATE_MAIL'            => $_ARRAYLANG['TXT_NEWSLETTER_ACTIVATION_EMAIL'],
            'TXT_DISPATCH_SETINGS'        => $_ARRAYLANG['TXT_DISPATCH_SETINGS'],
            'TXT_GENERATE_HTML'            => $_ARRAYLANG['TXT_GENERATE_HTML'],
            'TXT_SYSTEM_SETINGS'        => "System",
            'TXT_NOTIFICATION_MAIL'     => $_ARRAYLANG['TXT_NEWSLETTER_NOTIFICATION_MAIL'],
        ));
    }

    function ConfirmMail() {
        global $objDatabase, $_ARRAYLANG;
        $this->_pageTitle = $_ARRAYLANG['TXT_SETTINGS'];
        $this->_objTpl->loadTemplateFile('newsletter_config_confirmmail.html');
        $this->_objTpl->setVariable('TXT_TITLE', $_ARRAYLANG['TXT_DISPATCH_SETINGS']);

        //Update
        if ($_POST["update"]=="exe") {
            if ($objDatabase->Execute("UPDATE ".DBPREFIX."module_newsletter_confirm_mail SET title='".contrexx_addslashes($_POST["mailSubject"])."', content='".contrexx_addslashes($_POST["mailContent"])."' WHERE id=2") !== false) {
                $this->_strOkMessage = $_ARRAYLANG['TXT_NEWSLETTER_CONFIRM_MAIL_UPDATED_SUCCESSFULLY'];
            } else {
                $this->_strErrMessage = $_ARRAYLANG['TXT_NEWSLETTER_ERROR_SAVE_CONFIRM_MAIL'];
            }
        }

        $query         = "SELECT id, title, content FROM ".DBPREFIX."module_newsletter_confirm_mail WHERE id='2'";
        $objResult     = $objDatabase->Execute($query);
        if ($objResult !== false) {
            $subject = $objResult->fields['title'];
            $content = $objResult->fields['content'];
        }

        $this->_objTpl->setVariable(array(
            'TXT_LASTNAME'                 => $_ARRAYLANG['TXT_NEWSLETTER_LASTNAME'],
            'TXT_WILDCART_INFOS'        => $_ARRAYLANG['TXT_WILDCART_INFOS'],
            'TXT_U_TITLE'                => $_ARRAYLANG['TXT_NEWSLETTER_TITLE'],
            'TXT_NEWSLETTER_SEX'        => $_ARRAYLANG['TXT_NEWSLETTER_SEX'],
            'TXT_FIRSTNAME'                => $_ARRAYLANG['TXT_NEWSLETTER_FIRSTNAME'],
            'TXT_DATE'                    => $_ARRAYLANG['TXT_NEWSLETTER_DATE'],
            'TXT_CONTENT'                => $_ARRAYLANG['TXT_NEWSLETTER_CONTENT'],
            'TXT_SUBJECT'                => $_ARRAYLANG['TXT_NEWSLETTER_SUBJECT'],
            'TXT_TEXT'                    => $_ARRAYLANG['TXT_NEWSLETTER_TEXT'],
            'TXT_URL'                    => $_ARRAYLANG['TXT_NEWSLETTER_URL'],
            'TXT_CONFIRMMAIL'            => $_ARRAYLANG['TXT_NEWSLETTER_ACTIVATION_EMAIL'],
            'TXT_SAVE'                    => $_ARRAYLANG['TXT_SAVE'],
            'MAIL_SUBJECT'                => $subject,
            'MAIL_CONTENT'                => $content,
            'TXT_CONFIRM_MAIL'            => $_ARRAYLANG['TXT_NEWSLETTER_CONFIRMATION_EMAIL'],
            'TXT_ACTIVATE_MAIL'            => $_ARRAYLANG['TXT_NEWSLETTER_ACTIVATION_EMAIL'],
            'TXT_DISPATCH_SETINGS'        => $_ARRAYLANG['TXT_DISPATCH_SETINGS'],
            'TXT_GENERATE_HTML'            => $_ARRAYLANG['TXT_GENERATE_HTML'],
            'TXT_SYSTEM_SETINGS'        => "System",
            'TXT_NOTIFICATION_MAIL'     => $_ARRAYLANG['TXT_NEWSLETTER_NOTIFICATION_MAIL'],
        ));
    }


    function NotificationMail() {
        global $objDatabase, $_ARRAYLANG, $_CORELANG;
        $this->_pageTitle = $_ARRAYLANG['TXT_SETTINGS'];
        $this->_objTpl->loadTemplateFile('newsletter_config_notificationmail.html');
        $this->_objTpl->setVariable('TXT_TITLE', $_ARRAYLANG['TXT_DISPATCH_SETINGS']);

        //Update
        if ($_POST["update"]=="exe") {
            if ($objDatabase->Execute("UPDATE ".DBPREFIX."module_newsletter_confirm_mail SET title='".contrexx_addslashes($_POST["mailSubject"])."', content='".contrexx_addslashes($_POST["mailContent"])."', recipients='".contrexx_addslashes($_POST["mailRecipients"])."' WHERE id=3") !== false) {
                if ($objDatabase->Execute("UPDATE ".DBPREFIX."module_newsletter_settings SET setvalue='".intval($_POST["mailSendSubscribe"])."' WHERE setname='notificationSubscribe'") !== false) {
                    if ($objDatabase->Execute("UPDATE ".DBPREFIX."module_newsletter_settings SET setvalue='".intval($_POST["mailSendUnsubscribe"])."' WHERE setname='notificationUnsubscribe'") !== false) {
                        $this->_strOkMessage = $_ARRAYLANG['TXT_NEWSLETTER_CONFIRM_MAIL_UPDATED_SUCCESSFULLY'];
                    } else {
                        $this->_strErrMessage = $_ARRAYLANG['TXT_NEWSLETTER_ERROR_SAVE_CONFIRM_MAIL'];
                    }
                } else {
                    $this->_strErrMessage = $_ARRAYLANG['TXT_NEWSLETTER_ERROR_SAVE_CONFIRM_MAIL'];
                }
            } else {
                $this->_strErrMessage = $_ARRAYLANG['TXT_NEWSLETTER_ERROR_SAVE_CONFIRM_MAIL'];
            }
        }

        $query         = "SELECT id, title, content, recipients FROM ".DBPREFIX."module_newsletter_confirm_mail WHERE id='3'";
        $objResult     = $objDatabase->Execute($query);
        if ($objResult !== false) {
            $subject = $objResult->fields['title'];
            $content = $objResult->fields['content'];
            $recipients = $objResult->fields['recipients'];
        }

        $query         = "SELECT setvalue FROM ".DBPREFIX."module_newsletter_settings WHERE setname='notificationSubscribe'";
        $objResult     = $objDatabase->Execute($query);
        if ($objResult !== false) {
            if($objResult->fields['setvalue'] == 1) {
                $sendBySubscribeOn = 'checked="checked"';
                $sendBySubscribeOff = '';
            } else {
                $sendBySubscribeOn = '';
                $sendBySubscribeOff = 'checked="checked"';
            }
        }

        $query         = "SELECT setvalue FROM ".DBPREFIX."module_newsletter_settings WHERE setname='notificationUnsubscribe'";
        $objResult     = $objDatabase->Execute($query);
        if ($objResult !== false) {
            if($objResult->fields['setvalue'] == 1) {
                $sendByUnsubscribeOn = 'checked="checked"';
                $sendByUnsubscribeOff = '';
            } else {
                $sendByUnsubscribeOn = '';
                $sendByUnsubscribeOff = 'checked="checked"';
            }
        }

        $this->_objTpl->setVariable(array(
            'TXT_WILDCART_INFOS'        => $_ARRAYLANG['TXT_WILDCART_INFOS'],
            'TXT_RECIPIENTS'        => $_ARRAYLANG['TXT_NEWSLETTER_RECIPIENTS'],
            'TXT_DATE'                    => $_ARRAYLANG['TXT_NEWSLETTER_DATE'],
            'TXT_CONTENT'                => $_ARRAYLANG['TXT_NEWSLETTER_CONTENT'],
            'TXT_SUBJECT'                => $_ARRAYLANG['TXT_NEWSLETTER_SUBJECT'],
            'TXT_TEXT'                    => $_ARRAYLANG['TXT_NEWSLETTER_TEXT'],
            'TXT_URL'                    => $_ARRAYLANG['TXT_NEWSLETTER_URL'],
            'TXT_CONFIRMMAIL'            => $_ARRAYLANG['TXT_NEWSLETTER_ACTIVATION_EMAIL'],
            'TXT_SAVE'                    => $_ARRAYLANG['TXT_SAVE'],
            'MAIL_SUBJECT'                => $subject,
            'MAIL_CONTENT'                => $content,
            'MAIL_RECIPIENTS'          => $recipients,
            'SEND_BY_SUBSCRIBE_ON'          => $sendBySubscribeOn,
            'SEND_BY_SUBSCRIBE_OFF'          => $sendBySubscribeOff,
            'SEND_BY_UNSUBSCRIBE_ON'          => $sendByUnsubscribeOn,
            'SEND_BY_UNSUBSCRIBE_OFF'          => $sendByUnsubscribeOff,
            'TXT_CONFIRM_MAIL'            => $_ARRAYLANG['TXT_NEWSLETTER_CONFIRMATION_EMAIL'],
            'TXT_ACTIVATE_MAIL'            => $_ARRAYLANG['TXT_NEWSLETTER_ACTIVATION_EMAIL'],
            'TXT_DISPATCH_SETINGS'        => $_ARRAYLANG['TXT_DISPATCH_SETINGS'],
            'TXT_GENERATE_HTML'            => $_ARRAYLANG['TXT_GENERATE_HTML'],
            'TXT_SYSTEM_SETINGS'        => "System",
            'TXT_NOTIFICATION_MAIL'     => $_ARRAYLANG['TXT_NEWSLETTER_NOTIFICATION_MAIL'],
            'TXT_NOTIFICATION_SETTINGS'     => $_ARRAYLANG['TXT_SETTINGS'],
            'TXT_SEND_BY_SUBSCRIBE'     => $_ARRAYLANG['TXT_NEWSLETTER_NOTIFICATION_SEND_BY_SUBSCRIBE'],
            'TXT_SEND_BY_UNSUBSCRIBE'     => $_ARRAYLANG['TXT_NEWSLETTER_NOTIFICATION_SEND_BY_UNSUBSCRIBE'],
            'TXT_ACTION'     => $_ARRAYLANG['TXT_NEWSLETTER_NOTIFICATION_ACTION'],
            'TXT_NOTIFICATION_ACTIVATE'     => $_ARRAYLANG['TXT_NEWSLETTER_ACTIVATE'],
            'TXT_NOTIFICATION_DEACTIVATE'     => $_ARRAYLANG['TXT_NEWSLETTER_DEACTIVATE'],
            'TXT_SEX'     => $_ARRAYLANG['TXT_NEWSLETTER_SEX'],
            'TXT_TITLE'     => $_ARRAYLANG['TXT_NEWSLETTER_TITLE'],
            'TXT_FIRSTNAME'     => $_ARRAYLANG['TXT_NEWSLETTER_FIRSTNAME'],
            'TXT_LASTNAME'     => $_ARRAYLANG['TXT_NEWSLETTER_LASTNAME'],
            'TXT_E-MAIL'     => $_ARRAYLANG['TXT_EMAIL'],
        ));
    }


    function _sendMailPage()
    {
        global $_ARRAYLANG;
        if (isset($_POST['newsletter_mail_edit'])) {
            return $this->_editMail();
        } elseif (!isset($_REQUEST['id'])) {
            return $this->_mails();
        }

        $mailId = intval($_REQUEST['id']);
        $this->_pageTitle = $_ARRAYLANG['TXT_NEWSLETTER_SEND_EMAIL'];

        $this->_objTpl->loadTemplateFile('module_newsletter_mail_send.html');

        if (isset($_POST['newsletter_mail_send_test'])) {
            $status = $this->_sendTestMail($mailId);
        } else {
            $status = true;
        }

        if ((isset($_GET['testSent']) || isset($_POST['test_sent'])) && $status) {
            $this->_objTpl->setVariable(array(
                'TXT_NEWSLETTER_SEND_TESTMAIL_FIRST'    => '',
                'NEWSLETTER_TESTMAIL_SENT2'              => 'test_sent'
            ));
            $this->_objTpl->touchBlock("bulkSend");
        } else {
            $this->_objTpl->setVariable(array(
                "NEWSLETTER_TESTMAIL_SENT"                 => "&amp;testSent=1",
                'TXT_NEWSLETTER_SEND_TESTMAIL_FIRST'     => $_ARRAYLANG['TXT_NEWSLETTER_SEND_TESTMAIL_FIRST']
            ));
            $this->_objTpl->hideBlock("bulkSend");
        }

        $arrSettings = &$this->_getSettings();
        $testmail = $arrSettings['test_mail']['setvalue'];

        $this->_objTpl->setVariable(array(
            'TXT_NEWSLETTER_EMAIL_ADDRESS'    => $_ARRAYLANG['TXT_NEWSLETTER_EMAIL_ADDRESS'],
            'TXT_NEWSLETTER_SEND'            => $_ARRAYLANG['TXT_NEWSLETTER_SEND'],
            'TXT_SEND_TEST_EMAIL'            => $_ARRAYLANG['TXT_SEND_TEST_EMAIL'],
            'TXT_NEWSLETTER_MODIFY_EMAIL'    => $_ARRAYLANG['TXT_NEWSLETTER_MODIFY_EMAIL'],
            'TXT_NEWSLETTER_NOTICE_TESTMAIL' => $_ARRAYLANG['TXT_NEWSLETTER_NOTICE_TESTMAIL'],
            'TXT_NEWSLETTER_NOTICE'            => $_ARRAYLANG['TXT_NEWSLETTER_NOTICE'],
        ));

        $this->_objTpl->setVariable(array(
            'NEWSLETTER_MAIL_ID'            => $mailId,
            'NEWSLETTER_MAIL_TEST_EMAIL'    => $testmail
        ));

        if ($status) {
            $mailRecipientCount = $this->_getMailRecipientCount($mailId);
            if ($mailRecipientCount > 0) {
                $this->_objTpl->touchBlock('newsletter_mail_send_status');
                $this->_objTpl->hideBlock('newsletter_mail_list_required');
            } else {
                $this->_objTpl->setVariable(array(
                    'TXT_NEWSLETTER_MAIL_LIST_REQUIRED_TXT'    => $_ARRAYLANG['TXT_CATEGORY_ERROR']
                ));

                $this->_objTpl->touchBlock('newsletter_mail_list_required');
                $this->_objTpl->hideBlock('newsletter_mail_send_status');
            }
        }
        return true;
    }

    function _sendTestMail($mailId)
    {
        global $_ARRAYLANG;

        $objValidator = new FWValidator();

        if (!empty($_POST['newsletter_test_mail']) && $objValidator->isEmail($_POST["newsletter_test_mail"])) {
            if ($this->SendEmail(0, $mailId, $_POST["newsletter_test_mail"], 0) !== false) {
                $this->_strOkMessage = str_replace("%s", $_POST["newsletter_test_mail"], $_ARRAYLANG['TXT_TESTMAIL_SEND_SUCCESSFUL']);
                return true;
            } else {
                $this->_strErrMessage .= $_ARRAYLANG['TXT_SENDING_MESSAGE_ERROR'];
                return false;
            }
        } else {
            $this->_strErrMessage = $_ARRAYLANG['TXT_INVALID_EMAIL_ADDRESS'];
            return false;
        }
    }

    private function _getMailRecipientCount($mailId = null, $limit = 0, $pos = 0)
    {
        global $objDatabase;

        $count = empty($mailId) ? array() : 0;

        $objResult = $objDatabase->Execute("
            SELECT `id`, `tmp_copy`
            FROM   `".DBPREFIX."module_newsletter`
            ".(!empty($mailId) ? "WHERE `id` = ".$mailId : '')."
			ORDER BY status, id DESC
            ".($limit ? "LIMIT $pos, $limit" : ''));
        if ($objResult !== false) {
            if (empty($mailId)) {
                $count = $this->getFinalMailRecipientCount();
                while (!$objResult->EOF) {
                    if (!$objResult->fields['tmp_copy']) {
                        $count[$objResult->fields['id']] = $this->getCurrentMailRecipientCount($objResult->fields['id']);
                    }
                    $objResult->MoveNext();
                }
            } else {
                if ($objResult->fields['tmp_copy']) {
                    $count = $this->getFinalMailRecipientCount($mailId);
                } else {
                    $count = $this->getCurrentMailRecipientCount($mailId);
                }
            }

        }

        return $count;
    }

    private function getFinalMailRecipientCount($mailId = null)
    {
        global $objDatabase;

        $count = empty($mailId) ? array() : 0;

		$objResult = $objDatabase->Execute("
			SELECT
                `id`,
				`recipient_count`
			FROM
				`".DBPREFIX."module_newsletter`
            ".(!empty($mailId) ? "WHERE `id` = ".$mailId : ''));
		if ($objResult !== false && $objResult->RecordCount() > 0) {
            if (empty($mailId)) {
                while (!$objResult->EOF) {
                    $count[$objResult->fields['id']] = $objResult->fields['recipient_count'];
                    $objResult->MoveNext();
                }
            } else {
                $count = $objResult->fields['recipient_count'];
            }
		}

        return $count;
    }

	private function getCurrentMailRecipientCount($mailId)
    {
        global $objDatabase;

        /**
         * with subquery support:
         *
         * "SELECT sum( mailCount )
            FROM (
                SELECT COUNT( 1 ) AS mailCount
                FROM ".DBPREFIX."module_newsletter_rel_cat_news AS tblRelListMail
                RIGHT JOIN ".DBPREFIX."module_newsletter_rel_user_cat AS tblRelUserList ON tblRelListMail.category = tblRelUserList.category
                RIGHT JOIN ".DBPREFIX."module_newsletter_user AS tblUsers ON tblRelUserList.user = tblUsers.id
                AND tblUsers.status =1
                WHERE newsletter =22
                GROUP BY tblRelListMail.id
            ) AS foo
            LIMIT 1";
         *
         *
         */

        $objMail = $objDatabase->SelectLimit("
            SELECT
                COUNT( DISTINCT u.`id` ) AS recipientCount
            FROM
                `".DBPREFIX."module_newsletter_user` AS u
                INNER JOIN `".DBPREFIX."module_newsletter_rel_user_cat` AS relU ON relU.`user` = u.`id`
                INNER JOIN `".DBPREFIX."module_newsletter_rel_cat_news` AS relN USING ( `category` )
            WHERE
                relN.`newsletter` = ".$mailId."
                AND u.`status` =1
            GROUP BY relN.`newsletter`", 1
        );
        if ($objMail !== false && $objMail->RecordCount() == 1) {
            return $objMail->fields['recipientCount'];
        } else {
            return 0;
        }
    }


    function _sendMail()
    {
        global $objDatabase, $_ARRAYLANG;

        if (!isset($_REQUEST['id'])) {
            die($_ARRAYLANG['TXT_NEWSLETTER_INVALID_EMAIL']);
        }
        $mailId = intval($_REQUEST['id']);

        $mailRecipientCount = $this->_getMailRecipientCount($mailId);
        if ($mailRecipientCount == 0) {
            die($_ARRAYLANG['TXT_CATEGORY_ERROR']);
        }

        $this->_objTpl->loadTemplateFile('module_newsletter_mail_send_status.html');

        $objMail = $objDatabase->SelectLimit("SELECT
            id,
            subject,
            status,
            `count`,
            tmp_copy
            FROM ".DBPREFIX."module_newsletter WHERE id=".$mailId, 1);
        if ($objMail !== false) {
            $subject         = $objMail->fields['subject'];
            $status         = $objMail->fields['status'];
            $count             = $objMail->fields['count'];
            $tmp_copy         = $objMail->fields['tmp_copy'];
        }

        $statusBarWidth = round(200 / $mailRecipientCount * $count, 0);

        $this->_objTpl->setVariable(array(
            'TXT_NEWSLETTER_SUBJECT'            => $_ARRAYLANG['TXT_NEWSLETTER_SUBJECT'],
            'TXT_NEWSLETTER_MAILS_SENT'            => $_ARRAYLANG['TXT_NEWSLETTER_SENT_EMAILS']
        ));

        $this->_objTpl->setVariable(array(
            'CONTREXX_CHARSET'                => CONTREXX_CHARSET,
            'NEWSLETTER_MAIL_ID'            => $mailId,
            'NEWSLETTER_MAIL_USERES'        => $mailRecipientCount,
            'NEWSLETTER_SENDT'                => $count,
            'NEWSLETTER_MAIL_SUBJECT'        => htmlentities($subject, ENT_QUOTES, CONTREXX_CHARSET),
            'NEWSLETTER_STATUSBAR_WIDTH'    => $statusBarWidth
        ));

        if ($status == 0) {
            if (isset($_GET['send']) && $_GET['send'] == '1') {
                if ($tmp_copy==0) {
                    $this->_setTmpSending($mailId);
                    $this->_objTpl->setVariable('NEWSLETTER_MAIL_RELOAD_SEND_STATUS_FRAME', '<script type="text/javascript" language="javascript">setTimeout("newsletterSendMail()",1000);</script>');
                } else {
                    $arrSettings = &$this->_getSettings();
                    $mails_per_run = $arrSettings['mails_per_run']['setvalue'];
                    $timeout = time() + (ini_get('max_execution_time') ? ini_get('max_execution_time') : 300 /* Default Apache and IIS Timeout */);

                    $objSend = $objDatabase->SelectLimit("SELECT tblUser.id, tblUser.email FROM ".DBPREFIX."module_newsletter_tmp_sending AS tblSend
                    RIGHT JOIN ".DBPREFIX."module_newsletter_user AS tblUser ON tblUser.email=tblSend.email
                    WHERE tblSend.sendt=0 AND tblSend.newsletter=".$mailId, $mails_per_run, 0);
                    if ($objSend !== false) {
                        while (!$objSend->EOF) {
                            $beforeSend = time();

                            if (($count = $this->SendEmail($objSend->fields['id'], $mailId, $objSend->fields['email'], 1)) !== false) {
                                $this->_objTpl->setVariable('NEWSLETTER_MAIL_RELOAD_SEND_STATUS_FRAME', '<script type="text/javascript" language="javascript">setTimeout("newsletterSendMail()",1000);</script>');
                            }

                            // timeout prevention
                            if (time() >= $timeout - (time() - $beforeSend) * 2) {
                                break;
                            }

                            $objSend->MoveNext();
                        }
                    }
                }

                // update sent count
                $statusBarWidth = round(200 / $mailRecipientCount * $count, 0);
                $this->_objTpl->setVariable(array(
                    'NEWSLETTER_SENDT'                => $count,
                    'NEWSLETTER_STATUSBAR_WIDTH'    => $statusBarWidth
                ));

                $this->_objTpl->touchBlock('newsletter_mail_stop_button');
                $this->_objTpl->hideBlock('newsletter_mail_send_button');
                $this->_objTpl->setVariable(array(
                    'TXT_SENDING'            => $_ARRAYLANG['TXT_SENDING']
                ));
            } else {
                $this->_objTpl->touchBlock('newsletter_mail_send_button');
                $this->_objTpl->hideBlock('newsletter_mail_stop_button');
            }
        } else {
            //check if there are new recipients since the letter was sent
            $query="SELECT `count` FROM ".DBPREFIX."module_newsletter WHERE id=$mailId";
            $objRS = $objDatabase->Execute($query);
            if (!empty($objRS)) {
                $sentCount = $objRS->fields['count'];
            }
            $recipientCount = $this->_getMailRecipientCount($mailId);
            if ($recipientCount > $sentCount) {
                $this->_objTpl->hideBlock('newsletter_mail_stop_button');
                $this->_objTpl->touchBlock('newsletter_mail_send_button');
            } else {
                $this->_objTpl->hideBlock('newsletter_mail_stop_button');
                $this->_objTpl->hideBlock('newsletter_mail_send_button');
            }
        }
    }

    function _setTmpSending($mailId)
    {
        global $objDatabase;
        $objMail = $objDatabase->Execute("
            SELECT
                `email`
            FROM
                `".DBPREFIX."module_newsletter_rel_cat_news` AS relN
                INNER JOIN `".DBPREFIX."module_newsletter_rel_user_cat` AS relU USING (`category`)
                INNER JOIN `".DBPREFIX."module_newsletter_user` AS u ON u.`id`=relU.`user`
            WHERE
                `newsletter`=".$mailId."
                AND u.`status`=1
            GROUP BY relU.`user`"
        );

        if ($objMail !== false) {
            while (!$objMail->EOF) {
                $objSend = $objDatabase->Execute("SELECT
                    newsletter,
                    email
                    FROM ".DBPREFIX."module_newsletter_tmp_sending
                    WHERE email='".$objMail->fields['email']."' AND newsletter=".$mailId);
                if ($objSend->RecordCount() == 0) {
                    $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_newsletter_tmp_sending (`newsletter`, `email`) VALUES (".$mailId.", '".$objMail->fields['email']."')");
                }
                $objMail->MoveNext();
            }
            $date = time();

            $objResult = $objDatabase->Execute("SELECT COUNT(1) as recipient_count FROM `".DBPREFIX."module_newsletter_tmp_sending` WHERE `newsletter` = $mailId GROUP BY `newsletter`");
            if ($objResult !== false) {
                $recipientCount = $objResult->fields['recipient_count'];
                $objDatabase->Execute("UPDATE ".DBPREFIX."module_newsletter SET tmp_copy=1, date_sent=".$date.", recipient_count=$recipientCount WHERE id=".$mailId);
            }
        }
    }

    function SendEmail($UserID, $NewsletterID, $TargetEmail, $TmpEntry) {
        global $objDatabase, $_ARRAYLANG, $_DBCONFIG;

        require_once ASCMS_LIBRARY_PATH . '/phpmailer/class.phpmailer.php';

        $queryNewsletterValues = "select id, subject, template, content, content_text, attachment, format, priority, sender_email, sender_name, return_path, smtp_server, status, count, date_create, date_sent from ".DBPREFIX."module_newsletter where id=".$NewsletterID."";
        $objResultNewsletterValues = $objDatabase->Execute($queryNewsletterValues);
        if ($objResultNewsletterValues !== false) {
            $subject         = $objResultNewsletterValues->fields['subject'];
            $template         = $objResultNewsletterValues->fields['template'];
            $content         = $objResultNewsletterValues->fields['content'];
            $content_text     = $objResultNewsletterValues->fields['content_text'];
            $format         = $objResultNewsletterValues->fields['format'];
            $priority         = $objResultNewsletterValues->fields['priority'];
            $sender_email     = $objResultNewsletterValues->fields['sender_email'];
            $sender_name     = $objResultNewsletterValues->fields['sender_name'];
            $return_path     = $objResultNewsletterValues->fields['return_path'];
            $count             = $objResultNewsletterValues->fields['count'];
            $smtpAccount    = $objResultNewsletterValues->fields['smtp_server'];
        }
        /*
        $queryBCC = "select setvalue from ".DBPREFIX."module_newsletter_settings where setvalue='bcc_mail'";
        $objResultBCC = $objDatabase->Execute($queryBCC);
        if ($objResultBCC !== false) {
            $bcc         = $objResultBCC->fields['setvalue'];
        }
        */
        $queryBreak = "select setvalue from ".DBPREFIX."module_newsletter_settings where setvalue='text_break_after'";
        $objResultBreak = $objDatabase->Execute($queryBreak);
        if ($objResultBreak !== false) {
            $break         = $objResultBreak->fields['setvalue'];
        }
        $break = (intval($break)==0) ? 80 : $break;

        $HTML_TemplateSource = $this->GetTemplateSource($template, 'html');
        $TEXT_TemplateSource = $this->GetTemplateSource($template, 'text');

        $NewsletterBody_HTML = $this->ParseNewsletter($UserID, $subject, $content, $HTML_TemplateSource, "html", $TargetEmail);
        $NewsletterBody_TEXT = $this->ParseNewsletter($UserID, $subject, $content_text, $TEXT_TemplateSource, "text", $TargetEmail);
        $NewsletterBody_TEXT = wordwrap($NewsletterBody_TEXT, $break);

        // Work around an oddity in phpmailer: it detects
        // whether it's multipart/alternative by checking
        // the length of the AltBody attribute. Now if we
        // set it to empty (because it's not entered), it
        // fucks up and uses the HTML content and handles
        // it as plaintext. So we try to extract the text
        // from the HTML code and use this.
        if (($format == 'text/html' or $format == 'html/text' ) and $content_text == '') {
            $new_textcontent = preg_replace('#<br/?>#i', "\r\n",     html_entity_decode($content, ENT_COMPAT, CONTREXX_CHARSET));
            $new_textcontent = preg_replace('#<p/?>#i',  "\r\n\r\n", $new_textcontent);
            $new_textcontent = strip_tags($new_textcontent);
            # TODO: if there's tables, we probably should handle them in a special way...
            $NewsletterBody_TEXT = $this->ParseNewsletter($UserID, $subject, $new_textcontent, $TEXT_TemplateSource, "text", $TargetEmail);
        }

        $mail = new phpmailer();

        if ($smtpAccount > 0) {
            require_once ASCMS_CORE_PATH.'/SmtpSettings.class.php';
            if (($arrSmtp = SmtpSettings::getSmtpAccount($smtpAccount)) !== false) {
                $mail->IsSMTP();
                $mail->Host = $arrSmtp['hostname'];
                $mail->Port = $arrSmtp['port'];
                $mail->SMTPAuth = $arrSmtp['username'] == '-' ? false : true;
                $mail->Username = $arrSmtp['username'];
                $mail->Password = $arrSmtp['password'];
            }
        }

        $mail->CharSet = CONTREXX_CHARSET;
        $mail->From     = $sender_email;
        $mail->FromName = $sender_name;
        $mail->AddReplyTo($return_path);
        $mail->Subject     = $subject;
        $mail->Priority = $priority;
        //$mail->AddBCC($bcc, '');
        switch ($format) {
            case "text/html": # Some joker decided that we need to make the format spec as a string. Cause Constants
            case "html/text": # are for idiots, right? Everybody can remember the right way to specify a string.. RIGHT?
                $mail->Body     = $NewsletterBody_HTML;
                $mail->AltBody     = $NewsletterBody_TEXT;
            break;
            case "html":
                $mail->IsHTML(true);
                $mail->Body     = $NewsletterBody_HTML;
            break;
            case "text":
                $mail->IsHTML(false);
                $mail->Body     = $NewsletterBody_TEXT;
            break;
            default:
                $mail->Body     = $NewsletterBody_HTML;
                $mail->AltBody     = $NewsletterBody_TEXT;
            break;
        }
        $queryATT         = "SELECT newsletter, file_name FROM ".DBPREFIX."module_newsletter_attachment where newsletter=".$NewsletterID."";
        $objResultATT     = $objDatabase->Execute($queryATT);
        if ($objResultATT !== false) {
            while (!$objResultATT->EOF) {
                $mail->AddAttachment(ASCMS_NEWSLETTER_ATTACH_PATH."/".$objResultATT->fields['file_name'], $objResultATT->fields['file_name']);
                $objResultATT->MoveNext();
            }
        }

        $mail->AddAddress($TargetEmail);

        if ($UserID) {
            // mark recipient as in-action to prevent multiple tries of sending the newsletter to the same recipient
            $query = "UPDATE ".DBPREFIX."module_newsletter_tmp_sending SET sendt=2 where email='".$TargetEmail."' AND newsletter=".$NewsletterID." AND sendt=0";
            if ($objDatabase->Execute($query) === false || $objDatabase->Affected_Rows() == 0) {
                return $count;
            }
        }

        if ($mail->Send()) {
            $ReturnVar = $count++;
            if ($TmpEntry==1) {
                // Insert TMP-ENTRY Sended Email & Count++
                // ---------------------------------------
                $query = "UPDATE ".DBPREFIX."module_newsletter_tmp_sending SET sendt=1 where email='".$TargetEmail."' AND newsletter=".$NewsletterID."";
                if ($objDatabase->Execute($query) === false) {
                    if ($_DBCONFIG['dbType'] == 'mysql' && $objDatabase->ErrorNo() == 2006) {
                        @$objDatabase->Connect($_DBCONFIG['host'], $_DBCONFIG['user'], $_DBCONFIG['password'], $_DBCONFIG['database'], true);
                        if ($objDatabase->Execute($query) === false) {
                            return false;
                        }
                    }
                }

                $objDatabase->Execute("
                    UPDATE ".DBPREFIX."module_newsletter
                       SET count=count+1
                     WHERE id=$NewsletterID
                ");
                $queryCheck     = "SELECT 1 FROM ".DBPREFIX."module_newsletter_tmp_sending where newsletter=".$NewsletterID." and sendt=0";
                $objResultCheck = $objDatabase->SelectLimit($queryCheck, 1);
                if ($objResultCheck->RecordCount() == 0) {
                    $objDatabase->Execute("
                        UPDATE ".DBPREFIX."module_newsletter
                           SET status=1
                         WHERE id=$NewsletterID
                    ");
                }
            } elseif ($mail->error_count) {
                if (strstr($mail->ErrorInfo, 'authenticate')) {
                    $this->_strErrMessage .= sprintf($_ARRAYLANG['TXT_NEWSLETTER_MAIL_AUTH_FAILED'], htmlentities($arrSmtp['name'], ENT_QUOTES, CONTREXX_CHARSET)).'<br />';
                    $ReturnVar = false;
                }
            }
        } else {
            if (strstr($mail->ErrorInfo, 'authenticate')) {
                $this->_strErrMessage .= sprintf($_ARRAYLANG['TXT_NEWSLETTER_MAIL_AUTH_FAILED'], htmlentities($arrSmtp['name'], ENT_QUOTES, CONTREXX_CHARSET)).'<br />';
            } elseif (strstr($mail->ErrorInfo, 'from_failed')) {
                $this->_strErrMessage .= sprintf($_ARRAYLANG['TXT_NEWSLETTER_FROM_ADDR_REJECTED'], htmlentities($sender_email, ENT_QUOTES, CONTREXX_CHARSET)).'<br />';
            } elseif (strstr($mail->ErrorInfo, 'recipients_failed')) {
                $this->_strErrMessage .= sprintf($_ARRAYLANG['TXT_NEWSLETTER_RECIPIENT_FAILED'], htmlentities($TargetEmail, ENT_QUOTES, CONTREXX_CHARSET)).'<br />';
            } elseif (strstr($mail->ErrorInfo, 'instantiate')) {
                $this->_strErrMessage .= $_ARRAYLANG['TXT_NEWSLETTER_LOCAL_SMTP_FAILED'].'<br />';
            } elseif (strstr($mail->ErrorInfo, 'connect_host')) {
                $this->_strErrMessage .= $_ARRAYLANG['TXT_NEWSLETTER_CONNECT_SMTP_FAILED'].'<br />';
            } else {
                $this->_strErrMessage .= $mail->ErrorInfo.'<br />';
            }
            $ReturnVar = false;

            if ($TmpEntry == 1) {
                $arrSettings = $this->_getSettings();

                if ($arrSettings['rejected_mail_operation'] != 'ignore') {
                    $objRecipient = $objDatabase->SelectLimit('SELECT `id` FROM `'.DBPREFIX.'module_newsletter_user` WHERE `email` = \''.addslashes($TargetEmail).'\'', 1);
                    if ($objRecipient !== false && $objRecipient->RecordCount() == 1) {
                        switch ($arrSettings['rejected_mail_operation']['setvalue']) {
                            case 'deactivate':
                                if ($objDatabase->Execute("DELETE FROM `".DBPREFIX."module_newsletter_tmp_sending` WHERE `email` ='".addslashes($TargetEmail)."'") !== false) {
                                    $objDatabase->Execute("UPDATE `".DBPREFIX."module_newsletter_user` SET `status` = 0 WHERE `id` = ".$objRecipient->fields['id']);
                                }
                                break;

                            case 'delete':
                                $this->_deleteRecipient($objRecipient->fields['id']);
                                break;
                        }
                    }
                }

                $ReturnVar = $count;
            }
        }
        $mail->ClearAddresses();
        $mail->ClearAttachments();

        return $ReturnVar;
    }

    function GetTemplateSource($TemplateID, $format) {
        global $objDatabase;
        $TemplateSource = '';
        $queryPN = "select id, name, description, ".$format." from ".DBPREFIX."module_newsletter_template where id=".$TemplateID."";
        $objResultPN = $objDatabase->Execute($queryPN);
        if ($objResultPN !== false) {
            $TemplateSource = $objResultPN->fields[$format];
        }
        return $TemplateSource;
    }

    function _createDatesDropdown($birthday = '') {
        if (!empty($birthday)) {
            $birthday = (is_array($birthday)) ? $birthday : explode('-', $birthday);
            $day = !empty($birthday[0]) ? $birthday[0] : '01';
            $month = !empty($birthday[1]) ? $birthday[1] : '01';
            $year = !empty($birthday[2]) ? $birthday[2] : date("Y");
        } else {
            $day = '01';
            $month = '01';
            $year = date("Y");
        }

        for($i=1;$i<=31;$i++) {
            $selected = ($day == str_pad($i,2,'0',STR_PAD_LEFT)) ? 'selected="selected"' : '' ;
            $this->_objTpl->setVariable(array(
                'USERS_BIRTHDAY_DAY'        => str_pad($i,2,'0', STR_PAD_LEFT),
                'USERS_BIRTHDAY_DAY_NAME'    => $i,
                'SELECTED_DAY'                => $selected
            ));
            $this->_objTpl->parse('birthday_day');
        }

        for($i=1;$i<=12;$i++) {
            $selected = ($month == str_pad($i,2,'0',STR_PAD_LEFT)) ? 'selected="selected"' : '' ;
            $this->_objTpl->setVariable(array(
                'USERS_BIRTHDAY_MONTH'        => str_pad($i, 2, '0', STR_PAD_LEFT),
                'USERS_BIRTHDAY_MONTH_NAME'    => $this->months[$i],
                'SELECTED_MONTH'            => $selected
            ));
            $this->_objTpl->parse('birthday_month');
        }

        for($i=date("Y");$i>=1900;$i--) {
            $selected = ($year == $i) ? 'selected="selected"' : '' ;
            $this->_objTpl->setVariable(array(
                'USERS_BIRTHDAY_YEAR'         => $i,
                'SELECTED_YEAR'                => $selected
            ));
            $this->_objTpl->parse('birthday_year');
        }
    }

    function ParseNewsletter($UserID, $subject, $content_text, $TemplateSource, $format, $TargetEmail) {
        global $objDatabase, $_ARRAYLANG;
        $NewsletterBody = '';

        if ($UserID!=0) {
            $queryPN = "select id, code, sex, email, uri, title, lastname, firstname, street, zip, city, country, phone, birthday, status, emaildate from ".DBPREFIX."module_newsletter_user where id=".$UserID."";
            $objResultPN = $objDatabase->Execute($queryPN);

            if ($objResultPN !== false) {
                $code   = $objResultPN->fields['code'];
                $lastname  = $objResultPN->fields['lastname'];
                $firstname  = $objResultPN->fields['firstname'];
                $street  = $objResultPN->fields['street'];
                $zip   = $objResultPN->fields['zip'];
                $city   = $objResultPN->fields['city'];
                $country  = $objResultPN->fields['country'];
                $birthday  = $objResultPN->fields['birthday'];
                $email   = $objResultPN->fields['email'];
                $uri       = $objResultPN->fields['uri'];
                $phone   = $objResultPN->fields['phone'];

                switch($objResultPN->fields['sex']) {
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
			    $title = isset($arrRecipientTitles[$objResultPN->fields['title']]) ? $arrRecipientTitles[$objResultPN->fields['title']] : '';

                $array_1 = array(
                    '[[email]]', '[[uri]]', '[[sex]]', '[[title]]', '[[lastname]]', '[[firstname]]',
                    '[[street]]', '[[zip]]', '[[city]]', '[[country]]', '[[phone]]', '[[birthday]]'
                );
                $array_2 = array(
                    $email, $uri, $sex, $title, $lastname, $firstname,
                    $street, $zip, $city, $country, $phone, $birthday
                );
                $content_text     = str_replace($array_1, $array_2, $content_text);
                $TemplateSource = str_replace($array_1, $array_2, $TemplateSource);
            }
        }

        if ($format=="text") {
            $array_1 = array('[[profile_setup]]', '[[unsubscribe]]', '[[date]]');
            $array_2 = array($this->GetProfileSource($code, $TargetEmail, "text"), $this->GetUnsubscribeSource($code, $TargetEmail, "text"), date(ASCMS_DATE_SHORT_FORMAT));
            $content_text = str_replace($array_1, $array_2, $content_text);
        } else {
            $array_1 = array('[[profile_setup]]', '[[unsubscribe]]', '[[date]]');
            $array_2 = array($this->GetProfileSource($code, $TargetEmail, "html"), $this->GetUnsubscribeSource($code, $TargetEmail, "html"), date(ASCMS_DATE_SHORT_FORMAT));
            $content_text = str_replace($array_1, $array_2, $content_text);

            $allImg = array();
            preg_match_all("|src=\"(.*)\"|U", $content_text, $allImg, PREG_PATTERN_ORDER);
            $size = sizeof($allImg[1]);
            $i = 0;
            $port = $_SERVER['SERVER_PORT'] != 80 ? ':'.intval($_SERVER['SERVER_PORT']) : '';

            while ($i < $size) {
                $URLforReplace = $allImg[1][$i];
                if (substr($URLforReplace, 0, 7) != ASCMS_PROTOCOL.'://') {
                    $ReplaceWith = '"'.ASCMS_PROTOCOL.'://'.$_SERVER['SERVER_NAME'].$port.$URLforReplace.'"';
                } else {
                    $ReplaceWith = $URLforReplace;
                }
                $content_text = str_replace('"'.$URLforReplace.'"', $ReplaceWith, $content_text);
                $i++;
            }
        }

        $array_1         = array('[[profile_setup]]', '[[unsubscribe]]', '[[date]]');
        $array_2         = array($this->GetProfileSource($code, $TargetEmail, $format), $this->GetUnsubscribeSource($code, $TargetEmail, $format), date(ASCMS_DATE_SHORT_FORMAT));
        $TemplateSource = str_replace($array_1, $array_2, $TemplateSource);

        $NewsletterBody = str_replace("[[subject]]", $subject, $TemplateSource);
        $NewsletterBody = str_replace("[[content]]", $content_text, $TemplateSource);

        return $NewsletterBody;
    }

    function GetUnsubscribeSource($code, $email, $format)
    {
        global $_ARRAYLANG, $_CONFIG;

        $uri = ASCMS_PROTOCOL.'://'
            .$_CONFIG['domainUrl']
            .($_SERVER['SERVER_PORT'] == 80 ? NULL : ':'.intval($_SERVER['SERVER_PORT']))
            .ASCMS_PATH_OFFSET
            .($_CONFIG['useVirtualLanguagePath'] == 'on' ? '/'.FWLanguage::getLanguageParameter(FWLanguage::getDefaultLangId(), 'lang') : NULL)
            .'/'.CONTREXX_DIRECTORY_INDEX
            .'?section=newsletter&cmd=unsubscribe&code='.$code.'&mail='.urlencode($email);

        if ($format=="html") {
            return '<a href="'.$uri.'">'.$_ARRAYLANG['TXT_UNSUBSCRIBE'].'</a>';
        } else {
            return $_ARRAYLANG['TXT_UNSUBSCRIBE'].' '.$uri."\n\n";
        }
    }



    function GetProfileSource($code, $email, $format)
    {
        global $_ARRAYLANG, $_CONFIG;

        $uri = ASCMS_PROTOCOL.'://'
            .$_CONFIG['domainUrl']
            .($_SERVER['SERVER_PORT'] == 80 ? NULL : ':'.intval($_SERVER['SERVER_PORT']))
            .ASCMS_PATH_OFFSET
            .($_CONFIG['useVirtualLanguagePath'] == 'on' ? '/'.FWLanguage::getLanguageParameter(FWLanguage::getDefaultLangId(), 'lang') : NULL)
            .'/'.CONTREXX_DIRECTORY_INDEX
            .'?section=newsletter&cmd=profile&code='.$code.'&mail='.urlencode($email);

        if ($format=="html") {
            return '<a href="'.$uri.'">'.$_ARRAYLANG['TXT_EDIT_PROFILE'].'</a>';
        } else {
            return $_ARRAYLANG['TXT_EDIT_PROFILE'].' '.$uri."\n\n";
        }
    }



    function _getNewsPage()
    {
        global $objDatabase, $_ARRAYLANG;
        $this->_pageTitle = 'Newsmeldungen';
        $this->_objTpl->loadTemplateFile('newsletter_news.html');
        $this->_objTpl->setVariable(array(
            'TXT_SHOW_NEWS'                      => $_ARRAYLANG['TXT_SHOW_NEWS'],
            'TXT_SELECT_ALL'                      => $_ARRAYLANG['TXT_SELECT_ALL'],
            'TXT_NEWSLETTER_CREATE_FROM_NEWS'   => $_ARRAYLANG['TXT_NEWSLETTER_CREATE_FROM_NEWS'],
            'TXT_TITLE'                           => 'Newsmeldungen',
            'TXT_COUNT'                           => 'Anzahl festlegen',
            'TXT_PREVIEW'                       => 'Vorschau',
        ));
        $this->_objTpl->setVariable("NEWSLETTER_CONTENT_TITLE", $this->_NEWSLETTER_CONFIG['newsletterContentTitle']);
        if ($_POST['newslimit']=="" OR !is_numeric($_POST['newslimit']) ) {
            $newslimit = 20;
        } else {
            $newslimit = intval($_POST['newslimit']);
        }
        $objNews = $objDatabase->SelectLimit("SELECT `id`, `date`, `title`, `text` FROM ".DBPREFIX."module_news ORDER BY id DESC", $newslimit);
        if ($objNews !== false) {
            while (!$objNews->EOF) {
                $this->_objTpl->setVariable(array(
                    'NEWSLETTER_NEWS_DATE'    => date(ASCMS_DATE_SHORT_FORMAT, $objNews->fields['date']),
                    'NEWSLETTER_NEWS_TITLE'    => $objNews->fields['title']."\n"
                ));
                $this->_objTpl->parse("row");

                $this->_objTpl->setVariable(array(
                    'NEWSLETTER_NEWS_DETAIL_DATE'    => date(ASCMS_DATE_SHORT_FORMAT, $objNews->fields['date']),
                    'NEWSLETTER_NEWS_DETAIL_TITLE'    => $objNews->fields['title']
                ));
                $newstext = substr(ltrim(strip_tags($objNews->fields['text'])), 0, 800);
                $newstext .= "[....]";
                $newslink = $this->newsletterUri.ASCMS_PROTOCOL."://".$_SERVER['HTTP_HOST'].ASCMS_PATH_OFFSET."/index.php?section=news&cmd=details&newsid=".$objNews->fields['id'];
                $this->_objTpl->setVariable(array(
                    'NEWSLETTER_NEWS_DETAIL_TEXT' => $newstext,
                    'TXT_LINK_TO_REPORT_INFO_SOURCES'     => $_ARRAYLANG['TXT_LINK_TO_REPORT_INFO_SOURCES'],
                    'NEWSLETTER_NEWS_DETAIL_LINK' => $newslink
                ));
                $this->_objTpl->parse("detailrow");

                $objNews->MoveNext();
            }
        }
    }



    function exportuser() {
        global $objDatabase, $_ARRAYLANG;

        $listId = isset($_REQUEST['listId']) ? intval($_REQUEST['listId']) : 0;

        $arrRecipientTitles = &$this->_getRecipientTitles();

        if ($listId > 0) {
            $WhereStatement = " WHERE category=".$listId;
            $list = $this->_getList($listId);
            $listname = $list['name'];
        } else {
            $listname = "all_lists";
        }
        $query    = "    SELECT * FROM ".DBPREFIX."module_newsletter_rel_user_cat
                    RIGHT JOIN ".DBPREFIX."module_newsletter_user
                        ON ".DBPREFIX."module_newsletter_rel_user_cat.user=".DBPREFIX."module_newsletter_user.id ".
                    $WhereStatement." GROUP BY user";

        $objResult     = $objDatabase->Execute($query);
        $StringForFile = '';
        $separator = ';';
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $StringForFile .= $objResult->fields['status'].$separator;
                $StringForFile .= $objResult->fields['email'].$separator;
                $StringForFile .= $objResult->fields['uri'].$separator;
                $StringForFile .= $objResult->fields['sex'].$separator;
                $StringForFile .= $arrRecipientTitles[$objResult->fields['title']].$separator;
                $StringForFile .= $objResult->fields['lastname'].$separator;
                $StringForFile .= $objResult->fields['firstname'].$separator;
                $StringForFile .= $objResult->fields['company'].$separator;
                $StringForFile .= $objResult->fields['street'].$separator;
                $StringForFile .= $objResult->fields['zip'].$separator;
                $StringForFile .= $objResult->fields['city'].$separator;
                $StringForFile .= $objResult->fields['country'].$separator;
                $StringForFile .= $objResult->fields['phone'].$separator;
                $StringForFile .= $objResult->fields['birthday'];
                $StringForFile .= chr(13).chr(10);
                $objResult->MoveNext();
            }
        }

        if (strtolower(CONTREXX_CHARSET) == 'utf-8') {
            $StringForFile = utf8_decode($StringForFile);
        }

        header("Content-Type: text/comma-separated-values");
        header('Content-Disposition: attachment; filename="'.date('Y_m_d')."-".$listname.'.csv"');
        die($StringForFile);
    }


    function edituserSort()
    {
        global $_CONFIG, $objDatabase;
        $output = '';
        $fieldValues = array('status', 'email', 'uri', 'lastname', 'firstname', 'street', 'zip', 'city', 'country', 'emaildate', );
        $field  = (!empty($_REQUEST['field'])) ? contrexx_addslashes($_REQUEST['field']) : 'emaildate';
        $order  = (!empty($_REQUEST['order'])) ? contrexx_addslashes($_REQUEST['order']) : 'asc';
        $listId = (!empty($_REQUEST['list']))  ? intval($_REQUEST['list']) : '';
        $limit  = (!empty($_REQUEST['limit'])) ? intval($_REQUEST['limit']) : $_CONFIG['corePagingLimit'];

		$keyword      = contrexx_addslashes($_SESSION['backend_newsletter_users_search_keyword']);
		$searchfield  = contrexx_addslashes($_SESSION['backend_newsletter_users_search_SearchFields']);
		$searchstatus = contrexx_addslashes($_SESSION['backend_newsletter_users_search_SearchStatus']) . '';

		// don't ignore search stuff
		$search_where = '';
		if ( (!empty($searchfield)) && (!empty($keyword)) ) {
			$search_where = "AND `$searchfield` LIKE '%$keyword%'";
		}
		// PHP sucks. empty() doesn't work for numbers, even if they're
		// strings. PHP considers "0" to be empty.
		if (strlen("$searchstatus") != 0) {
			$search_where .= " AND `status` = $searchstatus ";
		}


        if(!in_array($field, $fieldValues) && ( $order != 'asc' || $order != 'desc' )){
            return false;
        }
        if($listId == ''){
            $query = "SELECT id, email, uri, lastname, firstname, street, zip, city, country, `status`, emaildate
            FROM ".DBPREFIX."module_newsletter_user
			WHERE 1 = 1 $search_where
            ORDER BY $field $order";
        }else{
            $query = "SELECT tblUser.id, email, uri, lastname, firstname, street, zip, city, country, `status`, emaildate
            FROM ".DBPREFIX."module_newsletter_user AS tblUser, "
                  .DBPREFIX."module_newsletter_rel_user_cat AS tblRel
            WHERE tblUser.id=tblRel.user and tblRel.category=".$listId."
				 $search_where
            ORDER BY $field $order";
        }

        $objRS = $objDatabase->SelectLimit($query, $limit);
        $limit = ($limit > $objRS->RecordCount()) ? $objRS->RecordCount() : $limit;
        for($i=0; $i<$limit; $i++){
            $output .= $objRS->fields['id'];
            $output .= '#'.$objRS->fields['status'];
            $output .= '#'.$objRS->fields['email'];
            $output .= '#'.$objRS->fields['uri'];
            $output .= '#'.$objRS->fields['lastname'];
            $output .= '#'.$objRS->fields['firstname'];
            $output .= '#'.$objRS->fields['street'];
            $output .= '#'.$objRS->fields['zip'];
            $output .= '#'.$objRS->fields['city'];
            $output .= '#'.$objRS->fields['country'];
            $output .= '#'.date(ASCMS_DATE_FORMAT, $objRS->fields['emaildate']);
            $output .= '%';
            $objRS->MoveNext();
        }
        die($output);
    }


    function importuser()
    {
        global $objDatabase, $_ARRAYLANG;

        $objTpl = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/newsletter/template');
        CSRF::add_placeholder($objTpl);
        $objTpl->setErrorHandling(PEAR_ERROR_DIE);

        require_once ASCMS_LIBRARY_PATH . "/importexport/import.class.php";
        $objImport = new Import();
        $arrFields = array(
            'email'     => $_ARRAYLANG['TXT_NEWSLETTER_EMAIL_ADDRESS'],
            'uri'       => $_ARRAYLANG['TXT_NEWSLETTER_URI'],
            'sex'       => $_ARRAYLANG['TXT_NEWSLETTER_SEX'],
            'title'     => $_ARRAYLANG['TXT_NEWSLETTER_TITLE'],
            'lastname'  => $_ARRAYLANG['TXT_NEWSLETTER_LASTNAME'],
            'firstname' => $_ARRAYLANG['TXT_NEWSLETTER_FIRSTNAME'],
            'company'   => $_ARRAYLANG['TXT_NEWSLETTER_COMPANY'],
            'street'    => $_ARRAYLANG['TXT_NEWSLETTER_STREET'],
            'zip'       => $_ARRAYLANG['TXT_NEWSLETTER_ZIP'],
            'city'      => $_ARRAYLANG['TXT_NEWSLETTER_CITY'],
            'country'   => $_ARRAYLANG['TXT_NEWSLETTER_COUNTRY'],
            'phone'     => $_ARRAYLANG['TXT_NEWSLETTER_PHONE'],
            'brithday'  => $_ARRAYLANG['TXT_NEWSLETTER_BIRTHDAY'],
        );

        if (isset($_POST['import_cancel'])) {
            // Abbrechen. Siehe Abbrechen
            $objImport->cancel();
            CSRF::header("Location: index.php?cmd=newsletter&act=users&tpl=import");
            exit;
        } elseif ($_POST['fieldsSelected']) {
            // Speichern der Daten. Siehe Final weiter unten.
            $arrRecipients = $objImport->getFinalData($arrFields);

            if ($_POST['category'] == '') {
                $arrLists = array_keys($this->_getLists());
            } else {
                $arrLists = array(intval($_POST['category']));
            }

            $EmailCount = 0;
            $arrBadEmails = array();
            $ExistEmails = 0;
            $NewEmails = 0;

            foreach ($arrRecipients as $arrRecipient) {
                if (empty($arrRecipient['email'])) {
                    continue;
                }
                if (!strpos($arrRecipient['email'],'@')) {
                    continue;
                }

                if ($this->check_email($arrRecipient['email']) != 1) {
                    array_push($arrBadEmails, $arrRecipient['email']);
                } else {
                    $EmailCount++;
                    $objRecipient = $objDatabase->SelectLimit("SELECT `id` FROM `".DBPREFIX."module_newsletter_user` WHERE `email` = '".addslashes($arrRecipient['email'])."'", 1);
                    if ($objRecipient->RecordCount() == 1) {
                        foreach ($arrLists as $listId) {
                            $this->_addRecipient2List($objRecipient->fields['id'], $listId);
                        }
                        $ExistEmails++;
                    } else {
                        $NewEmails ++;

                        if (in_array($arrRecipient['title'], $this->_getRecipientTitles())) {
                            $arrRecipientTitles = array_flip($this->_getRecipientTitles());
                            $recipientTitleId = $arrRecipientTitles[$arrRecipient['title']];
                        } else {
                            $recipientTitleId = $this->_addRecipientTitle($arrRecipient['title']);
                        }

                        if (!$this->_addRecipient($arrRecipient['email'], $arrRecipient['uri'], $arrRecipient['sex'], $recipientTitleId, $arrRecipient['lastname'], $arrRecipient['firstname'], $arrRecipient['company'], $arrRecipient['street'], $arrRecipient['zip'], $arrRecipient['city'], $arrRecipient['country'],'', '', 1, $arrLists)) {
                            array_push($arrBadEmails, $arrRecipient['email']);
                        }
                    }
                }
            }
            $this->_strOkMessage = $_ARRAYLANG['TXT_DATA_IMPORT_SUCCESSFUL']."<br/>".$_ARRAYLANG['TXT_CORRECT_EMAILS'].": ".$EmailCount."<br/>".$_ARRAYLANG['TXT_NOT_VALID_EMAILS'].": ".implode(', ', $arrBadEmails)."<br/>".$_ARRAYLANG['TXT_EXISTING_EMAILS'].": ".$ExistEmails."<br/>".$_ARRAYLANG['TXT_NEW_ADDED_EMAILS'].": ".$NewEmails;


            $objImport->initFileSelectTemplate($objTpl);

            $objTpl->setVariable(array(
                "IMPORT_ACTION"        => "?cmd=newsletter&amp;act=users&amp;tpl=import",
                'TXT_FILETYPE'        => $_ARRAYLANG['TXT_NEWSLETTER_FILE_TYPE'],
                'TXT_HELP'            => $_ARRAYLANG['TXT_NEWSLETTER_IMPORT_HELP'],
                'IMPORT_ADD_NAME'    => $_ARRAYLANG['TXT_NEWSLETTER_LIST'],
                'IMPORT_ADD_VALUE'   => $this->CategoryDropDown(),
                'IMPORT_ROWCLASS'    => 'row2'
            ));
            $objTpl->parse("additional");

            $this->_objTpl->setVariable('NEWSLETTER_USER_FILE', $objTpl->get());
        } elseif ($_FILES['importfile']['size'] == 0 || (isset($_POST['imported']) && $_REQUEST['category'] == 'selectcategory')) {
            // Dateiauswahldialog. Siehe Fileselect

            $this->_pageTitle = $_ARRAYLANG['TXT_IMPORT'];
            $this->_objTpl->addBlockfile('NEWSLETTER_USER_FILE', 'module_newsletter_user_import', 'module_newsletter_user_import.html');

            if (isset($_POST['imported']) && $_REQUEST['category'] == 'selectcategory') {
                $this->_strErrMessage = $_ARRAYLANG['TXT_NEWSLETTER_SELECT_CATEGORY'];
            }



            $objImport->initFileSelectTemplate($objTpl);

            $objTpl->setVariable(array(
                "IMPORT_ACTION"    => "?cmd=newsletter&amp;act=users&amp;tpl=import",
                'TXT_FILETYPE'    => 'Dateityp',
                'TXT_HELP'        => $_ARRAYLANG['TXT_NEWSLETTER_IMPORT_HELP'],
                'IMPORT_ADD_NAME'    => 'Liste',
                'IMPORT_ADD_VALUE'   => $this->CategoryDropDown(),
                'IMPORT_ROWCLASS'    => 'row2'
            ));
            $objTpl->parse("additional");

            $this->_objTpl->setVariable(array(
                'TXT_NEWSLETTER_IMPORT_FROM_FILE'    => $_ARRAYLANG['TXT_NEWSLETTER_IMPORT_FROM_FILE'],
                'TXT_IMPORT'                => $_ARRAYLANG['TXT_IMPORT'],
                'TXT_IMPORT_IN_CATEGORY'    => $_ARRAYLANG['TXT_IMPORT_IN_CATEGORY'],
                'TXT_ENTER_EMAIL_ADDRESS'     => $_ARRAYLANG['TXT_ENTER_EMAIL_ADDRESS'],
            ));

            $this->_objTpl->setVariable(array(
                'NEWSLETTER_CATEGORY_MENU'     => $this->CategoryDropDown(),
                'NEWSLETTER_IMPORT_FRAME'    => $objTpl->get()
            ));



            if (isset($_POST['newsletter_import_plain'])) {
                if ($_REQUEST['category'] == 'selectcategory') {
                    $this->_strErrMessage = $_ARRAYLANG['TXT_NEWSLETTER_SELECT_CATEGORY'];
                } else {
                    if ($_REQUEST['category'] == '') {
                        $arrLists = array_keys($this->_getLists());
                    } else {
                        $arrLists = array(intval($_REQUEST['category']));
                    }

                    $NLine = chr(13).chr(10);
                    $EmailList = str_replace(array(']','[',"\t","\n","\r"), ' ', $_REQUEST["Emails"]);
                    $EmailArray = split("[ '\",;:<>".$NLine."]", contrexx_stripslashes($EmailList));
                    $EmailCount = 0;
                    $arrBadEmails = array();
                    $ExistEmails = 0;
                    $NewEmails = 0;
                    foreach ($EmailArray as $email) {
                        if (empty($email)) {
                            continue;
                        }
                        if (!strpos($email,'@')) {
                            continue;
                        }

                        if ($this->check_email($email) != 1) {
                            array_push($arrBadEmails, $email);
                        } else {
                            $EmailCount++;
                            $objRecipient = $objDatabase->SelectLimit("SELECT `id` FROM `".DBPREFIX."module_newsletter_user` WHERE `email` = '".addslashes($email)."'", 1);
                            if ($objRecipient->RecordCount() == 1) {
                                foreach ($arrLists as $listId) {
                                    $this->_addRecipient2List($objRecipient->fields['id'], $listId);
                                }
                                $ExistEmails++;
                            } else {
                                $NewEmails ++;
                                if ($objDatabase->Execute("
                                    INSERT INTO `".DBPREFIX."module_newsletter_user` (
                                        `code`, `email`, `status`, `emaildate`
                                    ) VALUES (
                                        '".$this->_emailCode()."', '".addslashes($email)."', 1, ".time()."
                                    )"
                                ) !== false) {
                                    $this->_setRecipientLists($objDatabase->Insert_ID(), $arrLists);
                                } else {
                                    array_push($arrBadEmails, $email);
                                }
                            }
                        }
                    }
                    $this->_strOkMessage = $_ARRAYLANG['TXT_DATA_IMPORT_SUCCESSFUL']."<br/>".$_ARRAYLANG['TXT_CORRECT_EMAILS'].": ".$EmailCount."<br/>".$_ARRAYLANG['TXT_NOT_VALID_EMAILS'].": ".implode(', ', $arrBadEmails)."<br/>".$_ARRAYLANG['TXT_EXISTING_EMAILS'].": ".$ExistEmails."<br/>".$_ARRAYLANG['TXT_NEW_ADDED_EMAILS'].": ".$NewEmails;
                }

            }

            $this->_objTpl->parse('module_newsletter_user_import');
        } else {
            // Felderzuweisungsdialog. Siehe Fieldselect
            $objImport->initFieldSelectTemplate($objTpl, $arrFields);
            $objTpl->setVariable('TXT_REMOVE_PAIR', 'Paar entfernen');

            $objTpl->setVariable(array(
                'IMPORT_HIDDEN_NAME'    => 'category',
                'IMPORT_HIDDEN_VALUE'   => !empty($_POST['category']) ? intval($_POST['category']) : '',
            ));

            $this->_objTpl->setVariable('NEWSLETTER_USER_FILE', $objTpl->get());
        }
    }


    /**
     * Sets the list-categories for an User
     * @param int $CreatedID the ID of the user in the Database
     */
    function _setCategories($CreatedID) {
        global $objDatabase, $_ARRAYLANG;
        if ($_REQUEST['category']=="") {
            $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_newsletter_rel_user_cat WHERE user=$CreatedID");
            $queryIC         = "SELECT id FROM ".DBPREFIX."module_newsletter_category";
            $objResultIC     = $objDatabase->Execute($queryIC);
            if ($objResultIC !== false) {
                while (!$objResultIC->EOF) {
                    $objDatabase->Execute("
                        INSERT INTO ".DBPREFIX."module_newsletter_rel_user_cat (
                            user, category
                        ) VALUES (
                            $CreatedID, ".$objResultIC->fields['id']."
                        )
                    ");
                    $objResultIC->MoveNext();
                }
            }
        } else {
            $currentCategories = array(intval($_REQUEST['category']));
            //fetch all current categories that this user is in
            $query = "SELECT * from ".DBPREFIX."module_newsletter_rel_user_cat WHERE user=$CreatedID";
            $objRS = $objDatabase->Execute($query);
            while(!$objRS->EOF) {
                $currentCategories[] = $objRS->fields['category'];
                $objRS->MoveNext();
            }
            //make the categories-array unique
            $uniqueCategories = array_unique($currentCategories);
            //delete all relations from this user
            $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_newsletter_rel_user_cat WHERE user=$CreatedID");

            //re-import the unique categories
            foreach ($uniqueCategories as $catId) {
                if ($catId != 0) {
                    if ($objDatabase->Execute("INSERT INTO ".DBPREFIX."module_newsletter_rel_user_cat
                                    (user, category)
                                    VALUES (".$CreatedID.", ".$catId.")") === false) {
                        return $this->_strErrMessage = $_ARRAYLANG['TXT_DATABASE_ERROR'];
                    }
                }
            }
        }
        return true;
    }

    function check_email($email) {
        return preg_match('#^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]{2,}(\.[a-zA-Z0-9-]+)*\.(([0-9]{1,3})|([a-zA-Z]{2,6}))$#', $email);
    }

    /**
     * delete all inavtice recipients
     *
     * @return void
     */
    function _deleteInactiveRecipients() {
        global $objDatabase, $_ARRAYLANG;
        $count = 0;
        if ( ($objRS = $objDatabase->Execute('SELECT `id` FROM `'.DBPREFIX.'module_newsletter_user` WHERE `status` = 0 ')) !== false ) {
            while(!$objRS->EOF) {
                $objDatabase->Execute('DELETE FROM `'.DBPREFIX.'module_newsletter_user` WHERE `id` = '. $objRS->fields['id']);
                $objDatabase->Execute('DELETE FROM `'.DBPREFIX.'module_newsletter_rel_user_cat` WHERE `user` = '. $objRS->fields['id']);
                $objRS->MoveNext();
                $count++;
            }
            $this->_strOkMessage = $_ARRAYLANG['TXT_NEWSLETTER_INACTIVE_RECIPIENTS_SUCCESSFULLY_DELETED'] . ' ( '. $count .' )';
        } else {
            $this->_strErrMessage = $_ARRAYLANG['TXT_DATABASE_ERROR'] . $objDatabase->ErrorMsg();
        }
    }


    function overview() {
        global $objDatabase, $_ARRAYLANG;

        $this->_pageTitle = $_ARRAYLANG['TXT_NEWSLETTER_ADMINISTRATION'];
        $this->_objTpl->loadTemplateFile('module_newsletter_administration.html');

        $objSettings = $objDatabase->SelectLimit("SELECT setvalue FROM ".DBPREFIX."module_newsletter_settings WHERE setname='overview_entries_limit'", 1);
        if ($objSettings !== false) {
            $limit = $objSettings->fields['setvalue'];
        }

        $this->_listOverview($limit);
        $this->_mailOverview($limit);
        $this->_recipientOverview($limit);

        $this->_objTpl->setVariable(array(
            'TXT_NEWSLETTER_ADMINISTRATION'    => $_ARRAYLANG['TXT_NEWSLETTER_ADMINISTRATION'],
            'TXT_NEWSLETTER_CREATE_NEW_EMAIL'    => $_ARRAYLANG['TXT_NEWSLETTER_CREATE_NEW_EMAIL'],
            'TXT_NEWSLETTER_ADD_NEW_LIST'        => $_ARRAYLANG['TXT_NEWSLETTER_ADD_NEW_LIST'],
            'TXT_NEWSLETTER_IMPORT_RECIPIENTS'    => $_ARRAYLANG['TXT_NEWSLETTER_IMPORT_RECIPIENTS'],
            'TXT_NEWSLETTER_EXPORT_RECIPIENTS'    => $_ARRAYLANG['TXT_NEWSLETTER_EXPORT_RECIPIENTS'],

        ));

        $this->_objTpl->setVariable(array(
            'TXT_NEWSLETTER_NOT_SENT_NEWSLETTERS'        => $_ARRAYLANG['TXT_NEWSLETTER_NOT_SENT_NEWSLETTERS'],
            'TXT_NEWSLETTER_SENT_NEWSLETTERS'            => $_ARRAYLANG['TXT_NEWSLETTER_SENT_NEWSLETTERS'],
            'TXT_NEWSLETTER_ANNOUNCED_USERS'            => $_ARRAYLANG['TXT_NEWSLETTER_ANNOUNCED_USERS'],
            'NEWSLETTER_RECIPIENT_COUNT'                => $this->UserCount(),
            'VALUE_SENT_NEWSLETTERS'                    => $this->NewsletterSendCount(),
            'VALUE_NOT_SENT_NEWSLETTERS'                => $this->NewsletterNotSendCount()
            ));

    }

    function newsletterOverview() {
        global $objDatabase, $_ARRAYLANG;

        $this->_pageTitle = $_ARRAYLANG['TXT_NEWSLETTER'];
        $this->_objTpl->loadTemplateFile('newsletter_newsletter.html');
        $this->_objTpl->setVariable('TXT_TITLE', $_ARRAYLANG['TXT_ACTION']);

        $this->_objTpl->setVariable(array(
            'TXT_NEWSLETTER_EDIT'        => $_ARRAYLANG['TXT_NEWSLETTER_EDIT'],
            'TXT_NEWSLETTER_NEW'        => $_ARRAYLANG['TXT_NEWSLETTER_NEW'],
            'TXT_SEND_NEWSLETTER'        => $_ARRAYLANG['TXT_SEND_NEWSLETTER'],
            'TXT_NEWSLETTER_EDIT_SEND' => $_ARRAYLANG["TXT_NEWSLETTER_EDIT_SEND"]
            ));
    }

    function _users()
    {
        global $objDatabase, $_ARRAYLANG;

        $this->_pageTitle = $_ARRAYLANG['TXT_NEWSLETTER_RECIPIENTS'];
        $this->_objTpl->loadTemplateFile('module_newsletter_user.html');

        $this->_objTpl->setVariable(array(
            'TXT_NEWSLETTER_OVERVIEW'    => $_ARRAYLANG['TXT_NEWSLETTER_OVERVIEW'],
            'TXT_NEWSLETTER_IMPORT'        => $_ARRAYLANG['TXT_NEWSLETTER_IMPORT'],
            'TXT_NEWSLETTER_EXPORT'        => $_ARRAYLANG['TXT_NEWSLETTER_EXPORT'],
            'TXT_NEWSLETTER_ADD_USER'    => $_ARRAYLANG['TXT_NEWSLETTER_ADD_USER'],
        ));

        if (!isset($_REQUEST['tpl'])) {
            $_REQUEST['tpl'] = '';
        }

        switch ($_REQUEST['tpl']) {
            case 'edit':
                $this->_editUser();
                break;

            case 'import':
                $this->importuser();
                break;

            case 'export':
                $this->exportuser();
                break;

            default:
                $this->_userList();
                break;
        }
    }

    function configOverview() {
        global $objDatabase, $_ARRAYLANG;

        $this->_pageTitle = $_ARRAYLANG['TXT_SETTINGS'];
        $this->_objTpl->loadTemplateFile('newsletter_configuration.html');
        $this->_objTpl->setVariable('TXT_TITLE', $_ARRAYLANG['TXT_SETTINGS']);


        $this->_objTpl->setVariable(array(
            'TXT_DISPATCH_SETINGS'    => $_ARRAYLANG['TXT_DISPATCH_SETINGS'],
            'TXT_GENERATE_HTML'        => $_ARRAYLANG['TXT_GENERATE_HTML'],
            'TXT_CONFIRM_MAIL'        => "Aktivierungs E-Mail",
            'TXT_NOTIFICATION_MAIL'     => $_ARRAYLANG['TXT_NEWSLETTER_NOTIFICATION_MAIL'],
            ));

    }

    function UserCount() {
        global $objDatabase;
        $objResult_value = $objDatabase->SelectLimit("SELECT
                                                    count(*) as counter
                                                    FROM ".DBPREFIX."module_newsletter_user
                                                    ", 1);
        if ($objResult_value !== false && !$objResult_value->EOF) {
            return $objResult_value->fields["counter"];
        } else {
            return 0;
        }
    }

    function NewsletterSendCount() {
        global $objDatabase;
        $objResult_value = $objDatabase->SelectLimit("SELECT
                                                    count(*) as counter
                                                    FROM ".DBPREFIX."module_newsletter
                                                    WHERE status=1", 1);
        if ($objResult_value !== false && !$objResult_value->EOF) {
            return $objResult_value->fields["counter"];
        } else {
            return 0;
        }
    }

    function NewsletterNotSendCount() {
        global $objDatabase;
        $objResult_value = $objDatabase->SelectLimit("SELECT
                                                    count(*) as counter
                                                    FROM ".DBPREFIX."module_newsletter
                                                    WHERE status=0", 1);
        if ($objResult_value !== false && !$objResult_value->EOF) {
            return $objResult_value->fields["counter"];
        } else {
            return 0;
        }
    }

    function _editUser()
    {
        global $objDatabase, $_ARRAYLANG;

        $recipientId = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
        $recipientEmail = '';
        $recipientUri = '';
        $recipientSex = '';
        $recipientTitle = 0;
        $recipientLastname = '';
        $recipientFirstname = '';
        $recipientCompany = '';
        $recipientStreet = '';
        $recipientZip = '';
        $recipientCity = '';
        $recipientCountry = '';
        $recipientPhone = '';
        $recipientBirthday = '';
        $recipientStatus = 1;
        $arrAssociatedLists = array();

        if (isset($_POST['newsletter_recipient_email'])) {
            $recipientEmail = $_POST['newsletter_recipient_email'];
        }
        if (isset($_POST['newsletter_recipient_uri'])) {
            $recipientUri = $_POST['newsletter_recipient_uri'];
        }
        if (isset($_POST['newsletter_recipient_sex'])) {
            $recipientSex = in_array($_POST['newsletter_recipient_sex'], array('f', 'm')) ? $_POST['newsletter_recipient_sex'] : '';
        }
        if (isset($_POST['newsletter_recipient_title'])) {
            $arrRecipientTitles = $this->_getRecipientTitles();
            $recipientTitle = in_array($_POST['newsletter_recipient_title'], array_keys($arrRecipientTitles)) ? intval($_POST['newsletter_recipient_title']) : 0;
        }
        if (isset($_POST['newsletter_recipient_lastname'])) {
            $recipientLastname = $_POST['newsletter_recipient_lastname'];
        }
        if (isset($_POST['newsletter_recipient_firstname'])) {
            $recipientFirstname = $_POST['newsletter_recipient_firstname'];
        }
        if (isset($_POST['newsletter_recipient_company'])) {
            $recipientCompany = $_POST['newsletter_recipient_company'];
        }
        if (isset($_POST['newsletter_recipient_street'])) {
            $recipientStreet = $_POST['newsletter_recipient_street'];
        }
        if (isset($_POST['newsletter_recipient_zip'])) {
            $recipientZip = $_POST['newsletter_recipient_zip'];
        }
        if (isset($_POST['newsletter_recipient_city'])) {
            $recipientCity = $_POST['newsletter_recipient_city'];
        }
        if (isset($_POST['newsletter_recipient_country'])) {
            $recipientCountry = $_POST['newsletter_recipient_country'];
        }
        if (isset($_POST['newsletter_recipient_phone'])) {
            $recipientPhone = $_POST['newsletter_recipient_phone'];
        }
        if (isset($_POST['day']) && isset($_POST['month']) && isset($_POST['year'])) {
            $recipientBirthday = str_pad(intval($_POST['day']),2,'0',STR_PAD_LEFT).'-'.str_pad(intval($_POST['month']),2,'0',STR_PAD_LEFT).'-'.intval($_POST['year']);
        }
        if (isset($_POST['newsletter_recipient_status'])) {
            $recipientStatus = $_POST['newsletter_recipient_status'];
        } else {
            $recipientStatus = 0;
        }

        if (isset($_POST['newsletter_recipient_associated_list'])) {
            foreach ($_POST['newsletter_recipient_associated_list'] as $listId => $status) {
                if (intval($status) == 1) {
                    array_push($arrAssociatedLists, intval($listId));
                }
            }
        }

        if (isset($_POST['newsletter_recipient_save'])) {
            $objValidator = new FWValidator();
            if ($objValidator->isEmail($recipientEmail)) {
                if ($this->_isUniqueRecipientEmail($recipientEmail, $recipientId)) {
                    if ($recipientId > 0) {
                        if ($this->_updateRecipient($recipientId, $recipientEmail, $recipientUri, $recipientSex, $recipientTitle, $recipientLastname, $recipientFirstname, $recipientCompany, $recipientStreet, $recipientZip, $recipientCity, $recipientCountry, $recipientPhone, $recipientBirthday, $recipientStatus, $arrAssociatedLists)) {
                            $this->_strOkMessage .= $_ARRAYLANG['TXT_NEWSLETTER_RECIPIENT_UPDATED_SUCCESSFULLY'];
                            return $this->_userList();
                        } else {
                            $this->_strErrMessage .= $_ARRAYLANG['TXT_NEWSLETTER_ERROR_UPDATE_RECIPIENT'];
                        }
                    } else {
                        if ($this->_addRecipient($recipientEmail, $recipientUri, $recipientSex, $recipientTitle, $recipientLastname, $recipientFirstname, $recipientCompany, $recipientStreet, $recipientZip, $recipientCity, $recipientCountry, $recipientPhone, $recipientBirthday, $recipientStatus, $arrAssociatedLists)) {
                            $this->_strOkMessage .= $_ARRAYLANG['TXT_NEWSLETTER_RECIPIENT_SAVED_SUCCESSFULLY'];
                            return $this->_userList();
                        } else {
                            $this->_strErrMessage .= $_ARRAYLANG['TXT_NEWSLETTER_ERROR_SAVE_RECIPIENT'];
                        }
                    }
                } else {
                    $this->_strErrMessage .= $_ARRAYLANG['TXT_NEWSLETTER_DUPLICATE_EMAIL_ADDRESS'];
                }
            } else {
                $this->_strErrMessage .= $_ARRAYLANG['TXT_NEWSLETTER_INVALIDE_EMAIL_ADDRESS'];
            }
        } elseif ($recipientId > 0) {
            $objRecipient = $objDatabase->SelectLimit("SELECT email, uri, sex, title, lastname, firstname, company, street, zip, city, country, phone, birthday, status FROM ".DBPREFIX."module_newsletter_user WHERE id=".$recipientId, 1);
            if ($objRecipient !== false && $objRecipient->RecordCount() == 1) {
                $recipientEmail = $objRecipient->fields['email'];
                $recipientUri = $objRecipient->fields['uri'];
                $recipientSex = $objRecipient->fields['sex'];
                $recipientTitle = $objRecipient->fields['title'];
                $recipientLastname = $objRecipient->fields['lastname'];
                $recipientFirstname = $objRecipient->fields['firstname'];
                $recipientCompany = $objRecipient->fields['company'];
                $recipientStreet = $objRecipient->fields['street'];
                $recipientZip = $objRecipient->fields['zip'];
                $recipientCity = $objRecipient->fields['city'];
                $recipientCountry = $objRecipient->fields['country'];
                $recipientPhone = $objRecipient->fields['phone'];
                $recipientBirthday = $objRecipient->fields['birthday'];
                $recipientStatus = $objRecipient->fields['status'];

                $objList = $objDatabase->Execute("SELECT category FROM ".DBPREFIX."module_newsletter_rel_user_cat WHERE user=".$recipientId);
                if ($objList !== false) {
                    while (!$objList->EOF) {
                        array_push($arrAssociatedLists, $objList->fields['category']);
                        $objList->MoveNext();
                    }
                }
            } else {
                return $this->_userList();
            }
        }

        $this->_pageTitle = $recipientId > 0 ? $_ARRAYLANG['TXT_NEWSLETTER_MODIFY_RECIPIENT'] : $_ARRAYLANG['TXT_NEWSLETTER_ADD_NEW_RECIPIENT'];
        $this->_objTpl->addBlockfile('NEWSLETTER_USER_FILE', 'module_newsletter_user_edit', 'module_newsletter_user_edit.html');
        $this->_objTpl->setVariable('TXT_NEWSLETTER_USER_TITLE', $recipientId > 0 ? $_ARRAYLANG['TXT_NEWSLETTER_MODIFY_RECIPIENT'] : $_ARRAYLANG['TXT_NEWSLETTER_ADD_NEW_RECIPIENT']);

        $this->_createDatesDropdown($recipientBirthday);

        $arrLists = &$this->_getLists('tblCategory.name');
        $listNr = 0;
        foreach ($arrLists as $listId => $arrList) {
            $column = $listNr % 3;
            $this->_objTpl->setVariable(array(
                'NEWSLETTER_LIST_ID'                        => $listId,
                'NEWSLETTER_LIST_NAME'                        => $arrList['name'],
                'NEWSLETTER_SHOW_RECIPIENTS_OF_LIST_TXT'    => sprintf($_ARRAYLANG['TXT_NEWSLETTER_SHOW_RECIPIENTS_OF_LIST'], $arrList['name']),
                'NEWSLETTER_LIST_ASSOCIATED'                => in_array($listId, $arrAssociatedLists) ? 'checked="checked"' : ''
            ));
            $this->_objTpl->parse('newsletter_mail_associated_list_'.$column);

            $listNr++;
        }

        $this->_objTpl->setVariable(array(
            'NEWSLETTER_RECIPIENT_ID'            => $recipientId,
            'NEWSLETTER_RECIPIENT_EMAIL'        => htmlentities($recipientEmail, ENT_QUOTES, CONTREXX_CHARSET),
            'NEWSLETTER_RECIPIENT_URI'        => htmlentities($recipientUri, ENT_QUOTES, CONTREXX_CHARSET),
            'NEWSLETTER_RECIPIENT_FEMALE'        => $recipientSex == 'f' ? 'checked="checked"' : '',
            'NEWSLETTER_RECIPIENT_MALE'            => $recipientSex == 'm' ? 'checked="checked"' : '',
            'NEWSLETTER_RECIPIENT_TITLE'        => $this->_getRecipientTitleMenu($recipientTitle, 'name="newsletter_recipient_title" size="1"'),
            'NEWSLETTER_RECIPIENT_LASTNAME'        => htmlentities($recipientLastname, ENT_QUOTES, CONTREXX_CHARSET),
            'NEWSLETTER_RECIPIENT_FIRSTNAME'    => htmlentities($recipientFirstname, ENT_QUOTES, CONTREXX_CHARSET),
            'NEWSLETTER_RECIPIENT_COMPANY'        => htmlentities($recipientCompany, ENT_QUOTES, CONTREXX_CHARSET),
            'NEWSLETTER_RECIPIENT_STREET'        => htmlentities($recipientStreet, ENT_QUOTES, CONTREXX_CHARSET),
            'NEWSLETTER_RECIPIENT_ZIP'            => htmlentities($recipientZip, ENT_QUOTES, CONTREXX_CHARSET),
            'NEWSLETTER_RECIPIENT_CITY'            => htmlentities($recipientCity, ENT_QUOTES, CONTREXX_CHARSET),
            'NEWSLETTER_RECIPIENT_COUNTRY'        => htmlentities($recipientCountry, ENT_QUOTES, CONTREXX_CHARSET),
            'NEWSLETTER_RECIPIENT_PHONE'        => htmlentities($recipientPhone, ENT_QUOTES, CONTREXX_CHARSET),
            'NEWSLETTER_RECIPIENT_BIRTHDAY'        => htmlentities($recipientBirthday, ENT_QUOTES, CONTREXX_CHARSET),
            'NEWSLETTER_RECIPIENT_STATUS'        => $recipientStatus == '1' ? 'checked="checked"' : ''
        ));
        $this->_objTpl->setVariable(array(
            'TXT_NEWSLETTER_EMAIL_ADDRESS'    => $_ARRAYLANG['TXT_NEWSLETTER_EMAIL_ADDRESS'],
            'TXT_NEWSLETTER_URI'              => $_ARRAYLANG['TXT_NEWSLETTER_URI'],
            'TXT_NEWSLETTER_TITLE'            => $_ARRAYLANG['TXT_NEWSLETTER_TITLE'],
            'TXT_NEWSLETTER_SEX'              => $_ARRAYLANG['TXT_NEWSLETTER_SEX'],
            'TXT_NEWSLETTER_FEMALE'           => $_ARRAYLANG['TXT_NEWSLETTER_FEMALE'],
            'TXT_NEWSLETTER_MALE'             => $_ARRAYLANG['TXT_NEWSLETTER_MALE'],
            'TXT_NEWSLETTER_LASTNAME'         => $_ARRAYLANG['TXT_NEWSLETTER_LASTNAME'],
            'TXT_NEWSLETTER_FIRSTNAME'        => $_ARRAYLANG['TXT_NEWSLETTER_FIRSTNAME'],
            'TXT_NEWSLETTER_COMPANY'          => $_ARRAYLANG['TXT_NEWSLETTER_COMPANY'],
            'TXT_NEWSLETTER_STREET'           => $_ARRAYLANG['TXT_NEWSLETTER_STREET'],
            'TXT_NEWSLETTER_ZIP'              => $_ARRAYLANG['TXT_NEWSLETTER_ZIP'],
            'TXT_NEWSLETTER_CITY'             => $_ARRAYLANG['TXT_NEWSLETTER_CITY'],
            'TXT_NEWSLETTER_COUNTRY'          => $_ARRAYLANG['TXT_NEWSLETTER_COUNTRY'],
            'TXT_NEWSLETTER_PHONE'            => $_ARRAYLANG['TXT_NEWSLETTER_PHONE'],
            'TXT_NEWSLETTER_BIRTHDAY'         => $_ARRAYLANG['TXT_NEWSLETTER_BIRTHDAY'],
            'TXT_NEWSLETTER_ASSOCIATED_LISTS' => $_ARRAYLANG['TXT_NEWSLETTER_ASSOCIATED_LISTS'],
            'TXT_NEWSLETTER_STATUS'           => $_ARRAYLANG['TXT_NEWSLETTER_STATUS'],
            'TXT_NEWSLETTER_SAVE'             => $_ARRAYLANG['TXT_NEWSLETTER_SAVE'],
            'JAVASCRIPTCODE'                  => $this->JSadduser(),
        ));
        $this->_objTpl->parse('module_newsletter_user_edit');
        return true;
    }


    function _userList()
    {
        global $objDatabase, $_ARRAYLANG, $_CONFIG, $_CORELANG;

        $this->_pageTitle = $_ARRAYLANG['TXT_NEWSLETTER_USER_ADMINISTRATION'];
        $this->_objTpl->addBlockfile('NEWSLETTER_USER_FILE', 'module_newsletter_user_overview', 'module_newsletter_user_overview.html');

        $limit = (!empty($_GET['limit'])) ? intval($_GET['limit']) : $_CONFIG['corePagingLimit'];

        if (isset($_REQUEST['newsletterListId'])) {
            $newsletterListId = intval($_REQUEST['newsletterListId']);
        }
        $this->_objTpl->setVariable(array(
            'TXT_TITLE'                            => $_ARRAYLANG['TXT_SEARCH'],
            'TXT_CHANGELOG_SUBMIT'                   => $_CORELANG['TXT_MULTISELECT_SELECT'],
            'TXT_CHANGELOG_SUBMIT_DEL'             => $_CORELANG['TXT_MULTISELECT_DELETE'],
            'TXT_SELECT_ALL'                     => $_CORELANG['TXT_SELECT_ALL'],
            'TXT_DESELECT_ALL'                   => $_CORELANG['TXT_DESELECT_ALL'],
            'TXT_DELETE_HISTORY_MSG_ALL'        => $_CORELANG['TXT_DELETE_HISTORY_ALL'],
            'TXT_NEWSLETTER_REGISTRATION_DATE'    => $_ARRAYLANG['TXT_NEWSLETTER_REGISTRATION_DATE'],
            'TXT_NEWSLETTER_ROWS_PER_PAGE'        => $_ARRAYLANG['TXT_NEWSLETTER_ROWS_PER_PAGE'],
            'TXT_NEWSLETTER_RECIPIENTS'            => $_ARRAYLANG['TXT_NEWSLETTER_RECIPIENTS'],
            'TXT_NEWSLETTER_DELETE_ALL_INACTIVE' => $_ARRAYLANG['TXT_NEWSLETTER_DELETE_ALL_INACTIVE'],
            'TXT_NEWSLETTER_REALLY_DELETE_ALL_INACTIVE' => $_ARRAYLANG['TXT_NEWSLETTER_REALLY_DELETE_ALL_INACTIVE'],
            'TXT_NEWSLETTER_CANNOT_UNDO_OPERATION' => $_ARRAYLANG['TXT_NEWSLETTER_CANNOT_UNDO_OPERATION'],
            'NEWSLETTER_PAGING_LIMIT'            => $limit,
            'NEWSLETTER_CATEGORY_ID'            => '&amp;newsletterListId='.$newsletterListId,
        ));

		$search_params = addslashes("&SearchStatus={$_REQUEST['SearchStatus']}&keyword={$_REQUEST['keyword']}&SearchFields={$_REQUEST['SearchFields']}");
        $this->_objTpl->setVariable('NEWSLETTER_LIST_MENU', $this->CategoryDropDown('newsletterListId', $newsletterListId, "id='newsletterListId' onchange=\"window.location.replace('index.php?cmd=newsletter&".CSRF::param()."&act=users&newsletterListId='+this.value + '$search_params')\""));
        if($_GET["addmailcode"]=="exe"){
            $query        = "SELECT id, code FROM ".DBPREFIX."module_newsletter_user where code=''";
            $objResult = $objDatabase->Execute($query);
            if ($objResult !== false) {
                while (!$objResult->EOF) {
                    $objDatabase->Execute("
                        UPDATE ".DBPREFIX."module_newsletter_user
                           SET code='".$this->_emailCode()."'
                         WHERE id=".$objResult->fields['id']
                    );
                    $objResult->MoveNext();
                }
            }
        }

        if ($_GET["delete"]=="exe") {
            $recipientId = intval($_GET["id"]);
            if ($this->_deleteRecipient($recipientId)) {
                $this->_strOkMessage = $_ARRAYLANG['TXT_DATA_RECORD_DELETED_SUCCESSFUL'];
            } else {
                $this->_strErrMessage = $_ARRAYLANG['TXT_DATA_RECORD_DELETE_ERROR'];
            }
        }

        if ($_GET["bulkdelete"]=="exe") {
            $error=0;
            if (!empty($_POST['userid'])) {
                foreach ($_POST['userid'] as $userid) {
                    $userid=intval($userid);
                    if (!$this->_deleteRecipient($userid)) {
                        $error=1;
                    }
                }
                if ($error) {
                    $this->_strErrMessage = $_ARRAYLANG['TXT_DATA_RECORD_DELETE_ERROR'];
                } else {
                    $this->_strOkMessage = $_ARRAYLANG['TXT_DATA_RECORD_DELETED_SUCCESSFUL'];
                }
            }
        }


        $queryCHECK         = "SELECT id, code FROM ".DBPREFIX."module_newsletter_user where code=''";
        $objResultCHECK     = $objDatabase->Execute($queryCHECK);
        $count         = $objResultCHECK->RecordCount();
        if ($count > 0) {
            $email_code_check = '<div style="color: red;">'.$_ARRAYLANG['TXT_EMAIL_WITHOUT_CODE_MESSAGE'].'!<br/><a href="index.php?cmd=newsletter&act=users&addmailcode=exe">'.$_ARRAYLANG['TXT_ADD_EMAIL_CODE_LINK'].' ></a></div/><br/>';
            $this->_objTpl->setVariable('EMAIL_CODE_CHECK', $email_code_check);
        }

        $this->_objTpl->setVariable(array(
            'TXT_NEWSLETTER_CHECK_ALL'    => $_ARRAYLANG['TXT_NEWSLETTER_CHECK_ALL'],
            'TXT_NEWSLETTER_UNCHECK_ALL'    => $_ARRAYLANG['TXT_NEWSLETTER_UNCHECK_ALL'],
            'TXT_NEWSLETTER_WITH_SELECTED'    => $_ARRAYLANG['TXT_NEWSLETTER_WITH_SELECTED'],
            'TXT_NEWSLETTER_DELETE'            => $_ARRAYLANG['TXT_NEWSLETTER_DELETE'],
            'TXT_STATUS'            => $_ARRAYLANG['TXT_STATUS'],
            'TXT_SEARCH'            => $_ARRAYLANG['TXT_SEARCH'],
            'TXT_EMAIL_ADDRESS'        => $_ARRAYLANG['TXT_EMAIL_ADDRESS'],
            'TXT_LASTNAME'            => $_ARRAYLANG['TXT_LASTNAME'],
            'TXT_FIRSTNAME'            => $_ARRAYLANG['TXT_FIRSTNAME'],
            'TXT_STREET'            => $_ARRAYLANG['TXT_STREET'],
            'TXT_ZIP'                => $_ARRAYLANG['TXT_ZIP'],
            'TXT_CITY'                => $_ARRAYLANG['TXT_CITY'],
            'TXT_COUNTRY'            => $_ARRAYLANG['TXT_COUNTRY'],
            'TXT_PHONE'                => $_ARRAYLANG['TXT_PHONE'],
            'TXT_BIRTHDAY'            => $_ARRAYLANG['TXT_BIRTHDAY'],
            'TXT_USER_DATA'            => $_ARRAYLANG['TXT_USER_DATA'],
            'TXT_NEWSLETTER_CATEGORYS'    => $_ARRAYLANG['TXT_NEWSLETTER_CATEGORYS'],
            'TXT_STATUS'            => $_ARRAYLANG['TXT_STATUS'],
            'SELECTLIST_FIELDS'        => $this->SelectListFields(),
            'SELECTLIST_CATEGORY'    => $this->SelectListCategory(),
            'SELECTLIST_STATUS'        => $this->SelectListStatus(),
            'JAVASCRIPTCODE'        => $this->JSedituser(),
            'TXT_EDIT'                => $_ARRAYLANG['TXT_EDIT'],
            'TXT_ADD'                => $_ARRAYLANG['TXT_ADD'],
            'TXT_IMPORT'            => $_ARRAYLANG['TXT_IMPORT'],
            'TXT_EXPORT'            => $_ARRAYLANG['TXT_EXPORT'],
            'TXT_FUNCTIONS'            => $_CORELANG['TXT_FUNCTIONS'],
            'NEWSLETTER_SEARCH_LIST_ID' => $newsletterListId,
            ));

        $this->_objTpl->setGlobalVariable(array(
            'TXT_NEWSLETTER_MODIFY_RECIPIENT'    => $_ARRAYLANG['TXT_NEWSLETTER_MODIFY_RECIPIENT'],
            'TXT_NEWSLETTER_DELETE_RECIPIENT'    => $_ARRAYLANG['TXT_NEWSLETTER_DELETE_RECIPIENT'],
        ));

        $where_statement = '';
		// Set session entries so edituserSort() uses the right values when it's called..
		$_SESSION['backend_newsletter_users_search_SearchStatus'] = $_REQUEST["SearchStatus"];
		$_SESSION['backend_newsletter_users_search_SearchFields'] = $_REQUEST["SearchFields"];
		$_SESSION['backend_newsletter_users_search_keyword']      = $_REQUEST["keyword"];
        $this->_objTpl->setVariable(array(
			'NEWSLETTER_SEARCH_STATUS'  => $_REQUEST["SearchStatus"],
			'NEWSLETTER_SEARCH_FIELDS'  => $_REQUEST["SearchFields"],
			'NEWSLETTER_SEARCH_KEYWORD' => $_REQUEST["keyword"]
		));
        if ($_REQUEST["keyword"]!="") {
            if ($_REQUEST["SearchFields"]!="") {
                $where_statement .= ' and '.contrexx_addslashes($_REQUEST["SearchFields"]).' LIKE "%'.contrexx_addslashes($_REQUEST["keyword"]).'%" ';
            } else {
                $where_statement .= '    and (email LIKE "%'.contrexx_addslashes($_REQUEST["keyword"]).'%"
                                        or lastname LIKE "%'.contrexx_addslashes($_REQUEST["keyword"]).'%"
                                        or firstname LIKE "%'.contrexx_addslashes($_REQUEST["keyword"]).'%"
                                        or street LIKE "%'.contrexx_addslashes($_REQUEST["keyword"]).'%"
                                        or zip LIKE "%'.contrexx_addslashes($_REQUEST["keyword"]).'%"
                                        or city LIKE "%'.contrexx_addslashes($_REQUEST["keyword"]).'%"
                                        or country LIKE "%'.contrexx_addslashes($_REQUEST["keyword"]).'%"
                                        or phone LIKE "%'.contrexx_addslashes($_REQUEST["keyword"]).'%"
                                        or birthday LIKE "%'.contrexx_addslashes($_REQUEST["keyword"]).'%")';
            }
        }
        // kategoriesuche noch einbauen
        if ($_REQUEST["SearchCategory"]!="") {
            $where_statement .= ' ';
        }

        if ($_REQUEST["SearchStatus"]!="") {
            $where_statement .= ' and status='.intval($_REQUEST["SearchStatus"]).' ';
        }

        if ($newsletterListId > 0) {
            $where_statement .= " and tblUser.id=tblRel.user and tblRel.category=".$newsletterListId;
        }

        $pos = intval($_GET['pos']);


        if ($where_statement == '') {
            $query         = "    SELECT id, email, lastname, firstname, street, zip, city, country, emaildate, `status`
                            FROM ".DBPREFIX."module_newsletter_user
                            GROUP BY id
                            ORDER BY emaildate DESC";
        } elseif ($newsletterListId == 0) {
            $query         = "    SELECT tblUser.id, email, lastname, firstname, street, zip, city, country, emaildate, `status`
                            FROM ".DBPREFIX."module_newsletter_user AS tblUser
                            where 1=1 ".$where_statement."
                            GROUP BY id
                            ORDER BY emaildate DESC";
        } else {
            $query         = "    SELECT tblUser.id, email, lastname, firstname, street, zip, city, country, emaildate, `status`
                            FROM ".DBPREFIX."module_newsletter_user AS tblUser, "
                            .DBPREFIX."module_newsletter_rel_user_cat AS tblRel
                            where 1=1 ".$where_statement."
                            GROUP BY tblUser.id
                            ORDER BY emaildate DESC";
        }

        //show only one record set if _REQUEST['id'] is set (meaning the client comes from user details)
        if (!empty($_REQUEST['id']) && $_GET['delete'] != 'exe' && $_GET['bulkdelete'] != 'exe') {
            $objResult = $objDatabase->Execute('SELECT tblUser.id, email, lastname, firstname, street, zip, city, country, emaildate, `status`
                                                FROM '.DBPREFIX.'module_newsletter_user AS tblUser, '.DBPREFIX.'module_newsletter_rel_user_cat AS tblRel
                                                WHERE tblUser.id ='.intval($_REQUEST['id'])
                                                .' GROUP BY tblUser.id order by email LIMIT 1');
        } else {
            $objResult = $objDatabase->SelectLimit($query, $limit, $pos);
        }

        if ($where_statement == '') {
            $query_2 = "    SELECT COUNT(1) as cnt
                            FROM ".DBPREFIX."module_newsletter_user";
        } elseif ($newsletterListId == 0) {
            $query_2 = "    SELECT COUNT(1) as cnt
                            FROM ".DBPREFIX."module_newsletter_user AS tblUser
                            WHERE 1=1 ".$where_statement;
        } else {
            $query_2 = "    SELECT COUNT(1) as cnt
                            FROM ".DBPREFIX."module_newsletter_user AS tblUser, "
                            .DBPREFIX."module_newsletter_rel_user_cat AS tblRel
                            WHERE 1=1 ".$where_statement;
        }
        $objResult_2 = $objDatabase->Execute($query_2);
        $count = $objResult_2->fields['cnt'];
        $rowNr = 0;
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                if ($objResult->fields['status']==1) {
                    $StatusImg = '<img src="'.ASCMS_ADMIN_WEB_PATH.'/images/icons/led_green.gif" width="13" height="13" border="0" alt="'.$_ARRAYLANG['TXT_ACTIVE'].'" />';
                } else {
                    $StatusImg = '<img src="'.ASCMS_ADMIN_WEB_PATH.'/images/icons/led_red.gif" width="13" height="13" border="0" alt="'.$_ARRAYLANG['TXT_OPEN_ISSUE'].'" />';
                }
                $this->_objTpl->setVariable(array(
                    'NEWSLETTER_USER_ID'                    => $objResult->fields['id'],
                    'NEWSLETTER_USER_EMAIL'                    => htmlentities($objResult->fields['email'], ENT_QUOTES, CONTREXX_CHARSET),
                    'NEWSLETTER_USER_LASTNAME'                => (trim($objResult->fields['lastname'])=='') ? '-' : htmlentities($objResult->fields['lastname'], ENT_QUOTES, CONTREXX_CHARSET),
                    'NEWSLETTER_USER_FIRSTNAME'                => (trim($objResult->fields['firstname'])=='')  ? '-' : htmlentities($objResult->fields['firstname'], ENT_QUOTES, CONTREXX_CHARSET),
                    'NEWSLETTER_USER_STREET'                => (trim($objResult->fields['street'])=='')  ? '-' : htmlentities($objResult->fields['street'], ENT_QUOTES, CONTREXX_CHARSET),
                    'NEWSLETTER_USER_ZIP'                    => (trim($objResult->fields['zip'])=='')  ? '-' : htmlentities($objResult->fields['zip'], ENT_QUOTES, CONTREXX_CHARSET),
                    'NEWSLETTER_USER_CITY'                    => (trim($objResult->fields['city'])=='')  ? '-' : htmlentities($objResult->fields['city'], ENT_QUOTES, CONTREXX_CHARSET),
                    'NEWSLETTER_USER_COUNTRY'                => (trim($objResult->fields['country'])=='')  ? '-' : htmlentities($objResult->fields['country'], ENT_QUOTES, CONTREXX_CHARSET),
                    'NEWSLETTER_USER_REGISTRATION_DATE'        => (trim($objResult->fields['emaildate'])=='')  ? '-' : date(ASCMS_DATE_FORMAT, $objResult->fields['emaildate']),
                    'STATUS_IMG'                            => $StatusImg,
                    'ROW_CLASS'                                => $rowNr % 2 == 1 ? 'row1' : 'row2'
                ));
                $rowNr++;
                $this->_objTpl->parse("newsletter_user");
                $objResult->MoveNext();
            }
        }
        if (!empty($limit)) {
            $_CONFIG['corePagingLimit'] = $limit;
        }


        $paging = getPaging($count, $pos, "&amp;cmd=newsletter&amp;act=users".
        "&amp;limit=".$_CONFIG['corePagingLimit'].
        "&amp;newsletterListId=".$newsletterListId.
        "&amp;SearchFields=".$_REQUEST["SearchFields"].
        "&amp;keyword=".$_REQUEST["keyword"].
        "&amp;SearchStatus=".$_REQUEST["SearchStatus"], "", true, $_CONFIG['corePagingLimit']);
        $this->_objTpl->setVariable("USER_PAGING", $paging);
        $this->_objTpl->setVariable('TXT_EDIT', $_ARRAYLANG['TXT_EDIT']);
        $this->_objTpl->parse('module_newsletter_user_overview');
    }

    function SelectListStatus() {
        global $objDatabase, $_ARRAYLANG;
        $ReturnVar = '
            <select name="SearchStatus">
                <option value="">-- '.$_ARRAYLANG['TXT_STATUS'].' --</option>
                <option value="0">'.$_ARRAYLANG['TXT_OPEN_ISSUE'].'</option>
                <option value="1">'.$_ARRAYLANG['TXT_ACTIVE'].'</option>
            </select>
        ';
		$selected = $_REQUEST['SearchStatus'];
		$ReturnVar = str_replace("value=\"$selected\"", "selected=\"selected\" value=\"$selected\"", $ReturnVar);
        return $ReturnVar;
    }


    function SelectListCategory() {
        global $objDatabase, $_ARRAYLANG;
        $ReturnVar = '<select name="SearchCategory">';
        $ReturnVar .= '<option value="">-- '.$_ARRAYLANG['TXT_NEWSLETTER_CATEGORYS'].' --</option>';
        $queryPS         = "SELECT * FROM ".DBPREFIX."module_newsletter_category order by name";
        $objResultPS     = $objDatabase->Execute($queryPS);
        if ($objResultPS !== false) {
            while (!$objResultPS->EOF) {
                $ReturnVar .= '<option value="'.$objResultPS->fields['id'].'" >'.$objResultPS->fields['name'].'</option>';
                $objResultPS->MoveNext();
            }
        }
        $ReturnVar .= '</select>';
        return $ReturnVar;
    }


    function SelectListFields() {
        global $objDatabase, $_ARRAYLANG;
        $ReturnVar = '
            <select name="SearchFields">
                <option value="">-- '.$_ARRAYLANG['TXT_SEARCH_ON'].' --</option>
                <option value="email">'.$_ARRAYLANG['TXT_EMAIL_ADDRESS'].'</option>
                <option value="lastname">'.$_ARRAYLANG['TXT_LASTNAME'].'</option>
                <option value="firstname">'.$_ARRAYLANG['TXT_FIRSTNAME'].'</option>
                <option value="street">'.$_ARRAYLANG['TXT_STREET'].'</option>
                <option value="zip">'.$_ARRAYLANG['TXT_ZIP'].'</option>
                <option value="city">'.$_ARRAYLANG['TXT_CITY'].'</option>
                <option value="country">'.$_ARRAYLANG['TXT_COUNTRY'].'</option>
                <option value="phone">'.$_ARRAYLANG['TXT_PHONE'].'</option>
                <option value="birthday">'.$_ARRAYLANG['TXT_BIRTHDAY'].'</option>
            </select>
        ';
		$selected = $_REQUEST['SearchFields'];
		$ReturnVar = str_replace("value=\"$selected\"", "selected=\"selected\" value=\"$selected\"", $ReturnVar);
        return $ReturnVar;
    }


    function JSadduser() {
        global $objDatabase, $_ARRAYLANG;
        Return '
            <script language="javascript">
                function SubmitAddForm() {
                    if (CheckMail(document.adduser.email.value)==true) {
                            document.adduser.submit();
                    } else {
                        alert("'.$_ARRAYLANG['TXT_MAILERROR'].'");
                        document.adduser.email.focus();
                    }
                }

                function CheckMail(s) {
                     var a = false;
                     var res = false;
                     if (typeof(RegExp) == "function") {
                          var b = new RegExp("abc");
                          if (b.test("abc") == true) {a = true;}
                      }
                    if (a == true) {
                          reg = new RegExp("^([a-zA-Z0-9\\-\\.\\_]+)"+
                                           "(\\@)([a-zA-Z0-9\\-\\.]+)"+
                                           "(\\.)([a-zA-Z]{2,4})$");
                        res = (reg.test(s));
                    } else {
                        res = (s.search("@") >= 1 &&
                        s.lastIndexOf(".") > s.search("@") &&
                        s.lastIndexOf(".") >= s.length-5)
                    }
                    return(res);
                }
            </script>
        ';
    }

    function JSedituser() {
        global $_ARRAYLANG;
        Return '
            <script type="text/javascript">
            //<![CDATA[
                function DeleteUser(UserID, email) {
                    strConfirmMsg = "'.$_ARRAYLANG['TXT_NEWSLETTER_CONFIRM_DELETE_RECIPIENT_OF_ADDRESS'].'";
                    if (confirm(strConfirmMsg.replace("%s", email)+"\n'.$_ARRAYLANG['TXT_NEWSLETTER_CANNOT_UNDO_OPERATION'].'")) {
                        document.location.href = "index.php?cmd=newsletter&'.CSRF::param().'&act=users&delete=exe&id="+UserID;
                    }
                }

                function MultiAction()
                {
                    with (document.userlist)
                    {
                        switch (userlist_MultiAction.value) {
                            case \'delete\':
                                if (confirm(\''.$_ARRAYLANG['TXT_NEWSLETTER_CONFIRM_DELETE_SELECTED_RECIPIENTS'].'\n'.$_ARRAYLANG['TXT_NEWSLETTER_CANNOT_UNDO_OPERATION'].'\')) {
                                    submit();
                                }
                            break;
                            default: //do nothing

                        }
                    }
                }
            //]]>
            </script>
        ';
    }

    function DateForDB() {
        return date("Y")."-".date("m")."-".date("d");
    }

    function _getTemplateMenu($selectedTemplate = '', $attributes = '')
    {
        $menu = "<select".(!empty($attributes) ? " ".$attributes : "").">\n";
        foreach ($this->_getTemplates() as $templateId => $arrTemplate) {
            $menu .= "<option value=\"".$templateId."\"".($templateId == $selectedTemplate ? "selected=\"selected\"" : "").">".htmlentities($arrTemplate['name'], ENT_QUOTES, CONTREXX_CHARSET)."</option>\n";
        }
        $menu .= "</select>\n";

        return $menu;
    }


    function _getTemplates()
    {
        global $objDatabase;

        $arrTemplates = array();

        $objTemplate = $objDatabase->Execute("SELECT id, name, description, html, text FROM ".DBPREFIX."module_newsletter_template");
        if ($objTemplate !== false) {
            while (!$objTemplate->EOF) {
                $arrTemplates[$objTemplate->fields['id']] = array(
                    'name'            => $objTemplate->fields['name'],
                    'description'    => $objTemplate->fields['description'],
                    'html'            => $objTemplate->fields['html'],
                    'text'            => $objTemplate->fields['text']
                );
                $objTemplate->MoveNext();
            }
        }

        return $arrTemplates;
    }


    function CategoryDropDown($name = 'category', $selected = 0, $attrs = '') {
        global $objDatabase, $_ARRAYLANG;
        $ReturnVar = '<select name="'.$name.'"'.(!empty($attrs) ? ' '.$attrs : '').'>
        <option value="selectcategory">'.$_ARRAYLANG['TXT_NEWSLETTER_SELECT_CATEGORY'].'</option>
        <option value="">'.$_ARRAYLANG['TXT_NEWSLETTER_ALL'].'</option>';
        $queryCS         = "SELECT id, name FROM ".DBPREFIX."module_newsletter_category";
        $objResultCS     = $objDatabase->Execute($queryCS);
        if ($objResultCS !== false) {
            $CategorysFounded = 1;
            while (!$objResultCS->EOF) {
                $ReturnVar .= '<option value="'.$objResultCS->fields['id'].'"'.($objResultCS->fields['id'] == $selected ? 'selected="selected"' : '').'>'.htmlentities($objResultCS->fields['name'], ENT_QUOTES, CONTREXX_CHARSET).'</option>';
                $objResultCS->MoveNext();
            }
        }
        $ReturnVar .= '</select>';
        if ($CategorysFounded!=1) {
            $ReturnVar = '';
        }
        return $ReturnVar;
    }

    function _listOverview($limit = 10)
    {
        global $objDatabase, $_ARRAYLANG;

        $rowNr = 0;

        $this->_objTpl->setVariable(array(
            'TXT_NEWSLETTER_LISTS'    => $_ARRAYLANG['TXT_NEWSLETTER_LISTS']
        ));
        
        $objList = $objDatabase->SelectLimit("SELECT tblList.id AS listId, tblList.status, tblList.name FROM ".DBPREFIX."module_newsletter_category AS tblList GROUP BY tblList.id", $limit);
        
        if ($objList !== false && $objList->RecordCount() > 0) {
            $this->_objTpl->setVariable(array(
                'TXT_NEWSLETTER_ID_UC'                        => $_ARRAYLANG['TXT_NEWSLETTER_ID_UC'],
                'TXT_NEWSLETTER_STATUS'                        => $_ARRAYLANG['TXT_NEWSLETTER_STATUS'],
                'TXT_NEWSLETTER_NAME'                        => $_ARRAYLANG['TXT_NEWSLETTER_NAME'],
                'TXT_NEWSLETTER_LAST_EMAIL'                    => $_ARRAYLANG['TXT_NEWSLETTER_LAST_EMAIL'],
                'TXT_NEWSLETTER_RECIPIENTS'                    => $_ARRAYLANG['TXT_NEWSLETTER_RECIPIENTS'],
                'TXT_NEWSELTTER_GO_TO_LIST_ADMINISTRATION'    => $_ARRAYLANG['TXT_NEWSELTTER_GO_TO_LIST_ADMINISTRATION'],
                'TXT_NEWSLETTER_LIST_COUNT'                    => $_ARRAYLANG['TXT_NEWSLETTER_LIST_COUNT']
            ));

            $this->_objTpl->setGlobalVariable('TXT_NEWSLETTER_MODIFY_LIST', $_ARRAYLANG['TXT_NEWSLETTER_MODIFY_LIST']);

            while (!$objList->EOF) {
                $objRecipient = $objDatabase->SelectLimit("SELECT COUNT(1) AS recipients_count FROM ".DBPREFIX."module_newsletter_rel_user_cat AS tblRel WHERE tblRel.category=".$objList->fields['listId']." GROUP BY tblRel.category", 1);
                if ($objRecipient !== false && $objRecipient->RecordCount() == 1) {
                    $recipientCount = $objRecipient->fields['recipients_count'];
                } else {
                    $recipientCount = 0;
                }
                
                $objMail = $objDatabase->SelectLimit("SELECT subject, id, date_sent FROM ".DBPREFIX."module_newsletter_rel_cat_news INNER JOIN  ".DBPREFIX."module_newsletter ON  ".DBPREFIX."module_newsletter_rel_cat_news.newsletter = ".DBPREFIX."module_newsletter.id WHERE category = ".$objList->fields['listId']." AND date_sent != 0 ORDER BY date_sent DESC", 1);
                
                
                $this->_objTpl->setVariable(array(
                    'NEWSLETTER_LIST_ROW_CLASS'            => $rowNr % 2 == 1 ? "row1" : "row2",
                    'NEWSLETTER_LIST_ID'                => $objList->fields['listId'],
                    'NEWSLETTER_LIST_STATUS_IMG'        => $objList->fields['status'] == 1 ? "folder_on.gif" : "folder_off.gif",
                    'NEWSLETTER_LIST_NAME'                => htmlentities($objList->fields['name'], ENT_QUOTES, CONTREXX_CHARSET),
                    'NEWSLETTER_LIST_RECIPIENT_COUNT'    => $recipientCount > 0 ? '<a href="index.php?cmd=newsletter&amp;act=users&amp;newsletterListId='.$objList->fields['listId'].'" title="'.sprintf($_ARRAYLANG['TXT_NEWSLETTER_SHOW_RECIPIENTS_OF_LIST'], htmlentities($objList->fields['name'], ENT_QUOTES, CONTREXX_CHARSET)).'">'.$recipientCount.'</a>' : '-',
                    'NEWSLETTER_LIST_LAST_SENT_MAIL'    => $objMail->fields['date_sent'] > 0 ? '<a href="index.php?cmd=newsletter&amp;act=showMail&amp;id='.$objMail->fields['mailId'].'" title="'.$_ARRAYLANG['TXT_NEWSLETTER_DISPLAY_EMAIL'].'">'.htmlentities($objMail->fields['subject'], ENT_QUOTES, CONTREXX_CHARSET).' ('.date(ASCMS_DATE_FORMAT, $objMail->fields['date_sent']).')</a>' : '-',
                ));

                $rowNr++;
                $this->_objTpl->parse('newsletter_lists');

                $objList->MoveNext();
            }

            $objList = $objDatabase->Execute("SELECT COUNT(1) AS list_count FROM ".DBPREFIX."module_newsletter_category");
            if ($objList !== false) {
                $listCount = $objList->fields['list_count'];
            } else {
                $listCount = '-';
            }

            $this->_objTpl->setVariable('NEWSLETTER_LIST_COUNT', $listCount);

            $this->_objTpl->touchBlock('newsletter_lists_list');
            $this->_objTpl->hideBlock('newsletter_lists_no_list');
        } else {
            $this->_objTpl->setVariable('TXT_NEWSLETTER_ADD_NEW_LIST_MSG', $_ARRAYLANG['TXT_NEWSLETTER_ADD_NEW_LIST_MSG']);

            $this->_objTpl->hideBlock('newsletter_lists_list');
            $this->_objTpl->touchBlock('newsletter_lists_no_list');
        }

    }

    function _mailOverview($limit = 10)
    {
        global $objDatabase, $_ARRAYLANG;

        $rowNr = 0;
        $this->_objTpl->setVariable('TXT_NEWSLETTER_SENT_EMAILS', $_ARRAYLANG['TXT_NEWSLETTER_SENT_EMAILS']);

        $objMail = $objDatabase->SelectLimit("SELECT id, subject, date_sent FROM ".DBPREFIX."module_newsletter WHERE status='1' ORDER BY date_sent DESC", $limit);
        if ($objMail !== false && $objMail->RecordCount() > 0) {
            $this->_objTpl->setVariable(array(
                'TXT_NEWSLETTER_ID_UC'                        => $_ARRAYLANG['TXT_NEWSLETTER_ID_UC'],
                'TXT_NEWSLETTER_SUBJECT'                    => $_ARRAYLANG['TXT_NEWSLETTER_SUBJECT'],
                'TXT_NEWSLETTER_SENT'                        => $_ARRAYLANG['TXT_NEWSLETTER_SENT'],
                'TXT_NEWSLETTER_GO_TO_EMAIL_ADMINISTRATION'    => $_ARRAYLANG['TXT_NEWSLETTER_GO_TO_EMAIL_ADMINISTRATION'],
                'TXT_NEWSLETTER_TOTAL_SENT_EMAILS'            => $_ARRAYLANG['TXT_NEWSLETTER_TOTAL_SENT_EMAILS'],
                'TXT_NEWSLETTER_UNSENDET_EMAILS'            => $_ARRAYLANG['TXT_NEWSLETTER_UNSENDET_EMAILS']
            ));

            $this->_objTpl->setGlobalVariable('TXT_NEWSLETTER_DISPLAY_EMAIL', $_ARRAYLANG['TXT_NEWSLETTER_DISPLAY_EMAIL']);

            while (!$objMail->EOF) {
                $this->_objTpl->setVariable(array(
                    'NEWSLETTER_MAIL_ROW_CLASS'    => $rowNr % 2 == 1 ? "row1" : "row2",
                    'NEWSLETTER_MAIL_ID'        => $objMail->fields['id'],
                    'NEWSLETTER_MAIL_SUBJECT'    => htmlentities($objMail->fields['subject'], ENT_QUOTES, CONTREXX_CHARSET),
                    'NEWSLETTER_MAIL_SENT_DATE'    => date(ASCMS_DATE_FORMAT, $objMail->fields['date_sent'])
                ));

                $rowNr++;
                $this->_objTpl->parse('newsletter_mails');
                $objMail->MoveNext();
            }

            $this->_objTpl->touchBlock('newsletter_mails_list');
            $this->_objTpl->hideBlock('newsletter_mails_no_mail');
        } else {
            $this->_objTpl->setVariable('TXT_NEWSLETTER_CREATE_NEW_EMAIL_MSG', $_ARRAYLANG['TXT_NEWSLETTER_CREATE_NEW_EMAIL_MSG']);

            $this->_objTpl->touchBlock('newsletter_mails_no_mail');
            $this->_objTpl->hideBlock('newsletter_mails_list');
        }
    }


    function _getListHTML() {
        global $_ARRAYLANG;

        $listId = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($listId == 0) {
            return $this->_lists();
        }

        $this->_pageTitle = $_ARRAYLANG['TXT_NEWSLETTER_LISTS'];
        $this->_objTpl->loadTemplateFile('module_newsletter_list_sourcecode.html');
        $this->_objTpl->setVariable(array(
            'TXT_NEWSLETTER_GENERATE_HTML_SOURCE_CODE'    => $_ARRAYLANG['TXT_NEWSLETTER_GENERATE_HTML_SOURCE_CODE'],
            'TXT_NEWSLETTER_BACK'                        => $_ARRAYLANG['TXT_NEWSLETTER_BACK'],
            'NEWSLETTER_HTML_CODE'                        => htmlentities($this->_getHTML($listId), ENT_QUOTES, CONTREXX_CHARSET)
        ));
        return true;
    }


    function ConfigSystem()
    {
        global $objDatabase, $_ARRAYLANG, $_CORELANG;
        $this->_pageTitle = $_ARRAYLANG['TXT_SETTINGS'];
        $this->_objTpl->loadTemplateFile('newsletter_config_system.html');
        $this->_objTpl->setVariable('TXT_TITLE', $_CORELANG['TXT_SETTINGS_MENU_SYSTEM']);
        if ($_POST["update"] == "exe") {
            $objDatabase->Execute("UPDATE ".DBPREFIX."module_newsletter_settings SET setvalue='".intval($_POST['def_unsubscribe'])."' WHERE setname='defUnsubscribe'");
        }

        // Load Values
        $objSystem = $objDatabase->Execute("SELECT setname, setvalue FROM ".DBPREFIX."module_newsletter_settings");
        if ($objSystem !== false) {
            while (!$objSystem->EOF) {
                $arrSystem[$objSystem->fields['setname']] = $objSystem->fields['setvalue'];
                $objSystem->MoveNext();
            }
        }

        if ($arrSystem['defUnsubscribe'] == 1) {
            $delete = 'checked="checked"';
            $deactivate = '';
        } else {
            $delete = '';
            $deactivate = 'checked="checked"';
        }

        $this->_objTpl->setVariable(array(
            'TXT_NEWSLETTER_ACTIVATE'       => $_ARRAYLANG['TXT_NEWSLETTER_ACTIVATE'],
            'TXT_NEWSLETTER_DEACTIVATE'     => $_ARRAYLANG['TXT_NEWSLETTER_DEACTIVATE'],
            'TXT_NEWSLETTER_NOTIFY_ON_UNSUBSCRIBE'     => $_ARRAYLANG['TXT_NEWSLETTER_NOTIFY_ON_UNSUBSCRIBE'],
            'TXT_CONFIRM_MAIL'              => $_ARRAYLANG['TXT_NEWSLETTER_CONFIRMATION_EMAIL'],
            'TXT_ACTIVATE_MAIL'             => $_ARRAYLANG['TXT_NEWSLETTER_ACTIVATION_EMAIL'],
            'TXT_DISPATCH_SETINGS'          => $_ARRAYLANG['TXT_DISPATCH_SETINGS'],
            'TXT_GENERATE_HTML'             => $_ARRAYLANG['TXT_GENERATE_HTML'],

            'TXT_NOTIFICATION_MAIL'         => $_ARRAYLANG['TXT_NEWSLETTER_NOTIFICATION_MAIL'],
            'TXT_SYSTEM_SETINGS'            => $_CORELANG['TXT_SETTINGS_MENU_SYSTEM'],
            'TXT_DEF_UNSUBSCRIBE'           => $_ARRAYLANG['TXT_STATE_OF_SUBSCRIBED_USER'],
            'UNSUBSCRIBE_DEACTIVATE'        => $_CORELANG['TXT_DEACTIVATED'],
            'UNSUBSCRIBE_DELETE'            => $_CORELANG['TXT_DELETED'],
            'TXT_SAVE'                      => $_CORELANG['TXT_SETTINGS_SAVE'],
            'UNSUBSCRIBE_DEACTIVATE_ON'     => $deactivate,
            'UNSUBSCRIBE_DELETE_ON'         => $delete,
        ));
    }
}

?>
