<?php

/**
 * Mail class
 * @todo        Replace by the new core/Mailtemplate.class!
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     2.1.0
 * @package     contrexx
 * @subpackage  module_shop
 */

/**
 * Language
 */
require_once ASCMS_CORE_PATH.'/Text.class.php';

/**
 * Mail class
 * @todo        Replace by the new core/Mailtemplate.class!
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     2.1.0
 * @package     contrexx
 * @subpackage  module_shop
 */
class Mail
{
    /**
     * The language ID used when init() was called
     * @var integer
     */
    private static $lang_id = false;

    /**
     * The array of mail templates
     * @var array
     */
    private static $arrTemplate = false;


    /**
     * Reset the class
     *
     * Forces an {@link init()} the next time content is accessed
     */
    static function reset()
    {
        self::$lang_id = false;
    }


    /**
     * Initialize the mail template array
     *
     * Uses the given language ID, if any, or the language set in the
     * LANG_ID global constant.
     * Upon success, stores the language ID used in the $lang_id class
     * variable.
     * @param   integer     $lang_id        The optional language ID
     * @return  boolean                     True on success, false otherwise
     */
    static function init($lang_id=0)
    {
        global $objDatabase;

        // The array has been initialized with that language already
        if (self::$lang_id === $lang_id) return true;

        // Reset the language ID used
        self::$lang_id = false;
        // Use the current language if none is specified
        if (empty($lang_id)) $lang_id = FRONTEND_LANG_ID;
        self::$arrTemplate = array();

        $arrLanguages = FWLanguage::getLanguageArray();
// TODO: Why isn't there a method for that:
//        $defaultLangId = FWLanguage::getDefaultLanguageId()
// ?
        foreach ($arrLanguages as $arrLanguage) {
            if ($arrLanguage['frontend'] && $arrLanguage['is_default'] == 'true') {
                $defaultLangId = $arrLanguage['id'];
                break;
            }
        }

        $arrSqlName = Text::getSqlSnippets(
            '`mail`.`text_name_id`', $lang_id,
            MODULE_ID, TEXT_SHOP_MAIL_NAME
        );
        $arrSqlFrom = Text::getSqlSnippets(
            '`mail`.`text_from_id`', $lang_id,
            MODULE_ID, TEXT_SHOP_MAIL_FROM
        );
        $arrSqlSender = Text::getSqlSnippets(
            '`mail`.`text_sender_id`', $lang_id,
            MODULE_ID, TEXT_SHOP_MAIL_SENDER
        );
        $arrSqlSubject = Text::getSqlSnippets(
            '`mail`.`text_subject_id`', $lang_id,
            MODULE_ID, TEXT_SHOP_MAIL_SUBJECT
        );
        $arrSqlMessage = Text::getSqlSnippets(
            '`mail`.`text_message_id`', $lang_id,
            MODULE_ID, TEXT_SHOP_MAIL_MESSAGE
        );

        $objResult = $objDatabase->Execute("
            SELECT `mail`.`id`, `mail`.`protected`".
                   $arrSqlName['field'].$arrSqlFrom['field'].
                   $arrSqlSender['field'].$arrSqlSubject['field'].
                   $arrSqlMessage['field']."
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_mail` AS `mail`".
                   $arrSqlName['join'].$arrSqlFrom['join'].
                   $arrSqlSender['join'].$arrSqlSubject['join'].
                   $arrSqlMessage['join']."
             ORDER BY FIELD(`content`.`lang_id`, $defaultLangId, $lang_id) DESC");
        if (!$objResult) return false;
        while (!$objResult->EOF) {
            $id = $objResult->fields['id'];
            $available = true;
            $text_name_id = $objResult->fields[$arrSqlName['name']];
            $strName = $objResult->fields[$arrSqlName['text']];
            if ($strName === null) {
                $available = false;
                $objText = Text::getById($text_name_id, 0);
                if ($objText) $strName = $objText->getText();
            }
            $text_from_id = $objResult->fields[$arrSqlFrom['name']];
            $strFrom = $objResult->fields[$arrSqlFrom['text']];
            if ($strFrom === null) {
                $available = false;
                $objText = Text::getById($text_from_id, 0);
                if ($objText) $strFrom = $objText->getText();
            }
            $text_sender_id = $objResult->fields[$arrSqlSender['name']];
            $strSender = $objResult->fields[$arrSqlSender['text']];
            if ($strSender === null) {
                $available = false;
                $objText = Text::getById($text_sender_id, 0);
                if ($objText) $strSender = $objText->getText();
            }
            $text_subject_id = $objResult->fields[$arrSqlSubject['name']];
            $strSubject = $objResult->fields[$arrSqlSubject['text']];
            if ($strSubject === null) {
                $available = false;
                $objText = Text::getById($text_subject_id, 0);
                if ($objText) $strSubject = $objText->getText();
            }
            $text_message_id = $objResult->fields[$arrSqlMessage['name']];
            $strMessage = $objResult->fields[$arrSqlMessage['text']];
            if ($strMessage === null) {
                $available = false;
                $objText = Text::getById($text_message_id, 0);
                if ($objText) $strMessage = $objText->getText();
            }
            self::$arrTemplate[$id] = array(
                'id' => $id,
                'protected' => $objResult->fields['protected'],
                'available' => $available,
                'text_name_id' => $text_name_id,
                'name' => $strName,
                'text_from_id' => $text_from_id,
                'from' => $strFrom,
                'text_sender_id' => $text_sender_id,
                'sender' => $strSender,
                'text_subject_id' => $text_subject_id,
                'subject' => $strSubject,
                'text_message_id' => $text_message_id,
                'message' => $strMessage,
            );
            $objResult->MoveNext();
        }
        // Remember the language used
        self::$lang_id = $lang_id;
        return true;
    }


    static function getTemplateArray($lang_id=0)
    {
        if (empty($lang_id)) return false;
        self::init($lang_id);
        return self::$arrTemplate;
    }


    /**
     * Set up and send an email from the shop.
     * @static
     * @param   string    $mailTo           Recipient mail address
     * @param   string    $mailFrom         Sender mail address
     * @param   string    $mailSender       Sender name
     * @param   string    $mailSubject      Message subject
     * @param   string    $mailBody         Message body
     * @return  boolean                     True if the mail could be sent,
     *                                      false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function send(
        $mailTo, $mailFrom, $mailSender, $mailSubject, $mailBody
    ) {
        global $_CONFIG;

        if (!@include_once ASCMS_LIBRARY_PATH.'/phpmailer/class.phpmailer.php') {
            return false;
        }
        $objMail = new phpmailer();
        if (   isset($_CONFIG['coreSmtpServer'])
            && $_CONFIG['coreSmtpServer'] > 0
            && @include_once ASCMS_CORE_PATH.'/SmtpSettings.class.php') {
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
        $objMail->From = preg_replace('/\015\012/', '', $mailFrom);
        $objMail->FromName = preg_replace('/\015\012/', '', $mailSender);
        //$objMail->AddReplyTo($_CONFIG['coreAdminEmail']);
        $objMail->Subject = $mailSubject;
        $objMail->IsHTML(false);
        $objMail->Body = preg_replace('/\015\012/', "\012", $mailBody);
        $objMail->AddAddress($mailTo);
        if ($objMail->Send()) return true;
        return false;
    }


    /**
     * Pick a mail template from the database
     *
     * Get the selected mail template and associated fields from the database.
     * @static
     * @param   integer $template_id    The mail template ID
     * @param   integer $lang_id        The language ID
     * @global  ADONewConnection
     * @return  mixed                   The mail template array on success,
     *                                  false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getTemplate($template_id, $lang_id=0)
    {
        if (empty($lang_id)) $lang_id = FRONTEND_LANG_ID;
        self::init($lang_id);
        return self::$arrTemplate[$template_id];
    }


    static function store()
    {
        if (empty(self::$arrTemplate)) self::init();
        $total_result = true;
        $result = self::deleteTemplate();
        if ($result !== '') $total_result &= $result;
        $result = self::storeTemplate();
        if ($result !== '') $total_result &= $result;
        // Force reinit after storing, or the user might not
        // see any changes at first
        self::reset();
        return $total_result;
    }


    /**
     * Delete template
     */
    static function deleteTemplate()
    {
        global $objDatabase;

        if (empty($_GET['delTplId'])) return '';
        $template_id = $_GET['delTplId'];
        // Cannot delete protected (system) templates
        if (self::$arrTemplate[$template_id]['protected']) return false;
        // Delete all Text records
        if (!Text::deleteById(self::$arrTemplate[$template_id]['text_name_id'])) return false;
        if (!Text::deleteById(self::$arrTemplate[$template_id]['text_from_id'])) return false;
        if (!Text::deleteById(self::$arrTemplate[$template_id]['text_sender_id'])) return false;
        if (!Text::deleteById(self::$arrTemplate[$template_id]['text_subject_id'])) return false;
        if (!Text::deleteById(self::$arrTemplate[$template_id]['text_message_id'])) return false;
        $objResult = $objDatabase->Execute("
            DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_mail
             WHERE id=$template_id");
        if (!$objResult) return false;
        $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_mail");
        return true;
    }


    /**
     * Update or add new template
     */
    function storeTemplate()
    {
        global $objDatabase;

        if (empty($_POST['mails'])) return '';
        // Use the posted template ID only if the "store as new" checkbox
        // hasn't been marked
        $template_id =
            (empty($_POST['shopMailSaveNew']) && !empty($_POST['tplId'])
                ? $_POST['tplId'] : 0
            );
        if (empty($_POST['langId'])) return '';
        $lang_id = $_POST['langId'];
        self::init($lang_id);

        $text_name_id = 0;
        $text_from_id = 0;
        $text_sender_id = 0;
        $text_subject_id = 0;
        $text_message_id = 0;
        if ($template_id) {
            $arrTemplate = self::$arrTemplate[$template_id];
            if ($arrTemplate) {
                $text_name_id = $arrTemplate['text_name_id'];
                $text_from_id = $arrTemplate['text_from_id'];
                $text_sender_id = $arrTemplate['text_sender_id'];
                $text_subject_id = $arrTemplate['text_subject_id'];
                $text_message_id = $arrTemplate['text_message_id'];
            } else {
                // Template not found.  Clear the ID.
                $template_id = 0;
            }
        }
// Note: Text::replace() now returns the ID, not the object!
        $text_name_id = Text::replace(
            $text_name_id, FRONTEND_LANG_ID, $_POST['shopMailTemplate'],
            MODULE_ID, TEXT_SHOP_MAIL_NAME
        );
        $text_from_id = Text::replace(
            $text_from_id, FRONTEND_LANG_ID, $_POST['shopMailFromAddress'],
            MODULE_ID, TEXT_SHOP_MAIL_FROM
        );
        $text_sender_id = Text::replace(
            $text_sender_id, FRONTEND_LANG_ID, $_POST['shopMailFromName'],
            MODULE_ID, TEXT_SHOP_MAIL_SENDER
        );
        $text_subject_id = Text::replace(
            $text_subject_id, FRONTEND_LANG_ID, $_POST['shopMailSubject'],
            MODULE_ID, TEXT_SHOP_MAIL_SUBJECT
        );
        $text_message_id = Text::replace(
            $text_message_id, FRONTEND_LANG_ID, $_POST['shopMailBody'],
            MODULE_ID, TEXT_SHOP_MAIL_MESSAGE
        );
        // If the template ID is known, update.
        // Note that the protected flag is not changed.
        // For newly inserted templates, the protected flag is always 0 (zero).
        $query =
            (   $template_id
             && self::$arrTemplate[$template_id]['available']
            ? "UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_mail
                SET `text_name_id`=".$objTextName->getId().",
                    `text_from_id`=".$objTextFrom->getId().",
                    `text_sender_id`=".$objTextSender->getId().",
                    `text_subject_id`=".$objTextSubject->getId().",
                    `text_message_id`=".$objTextMessage->getId()."
                WHERE `id`=$template_id"
            : "INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_mail (
                `protected`,
                `text_name_id`,
                `text_from_id`, `text_sender_id`,
                `text_subject_id`, `text_message_id`
              ) VALUES (
                  0, ".
                  $objTextName->getId().", ".
                  $objTextFrom->getId().", ".
                  $objTextSender->getId().", ".
                  $objTextSubject->getId().", ".
                  $objTextMessage->getId()."
              )");
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        return true;
    }

}

?>
