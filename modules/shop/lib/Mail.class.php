<?php

require_once ASCMS_CORE_PATH.'/Text.class.php';

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
        foreach ($arrLanguages as $arrLanguage) {
            if ($arrLanguage['frontend'] && $arrLanguage['is_default'] == 'true') {
                $defaultLangId = $arrLanguage['id'];
                break;
            }
        }

//        $arrSqlName = Text::getSqlSnippets(
//            '`mail`.`text_name_id`', $lang_id,
//            MODULE_ID, TEXT_SHOP_MAIL_NAME
//        );
//        $arrSqlFrom = Text::getSqlSnippets(
//            '`mail`.`text_from_id`', $lang_id,
//            MODULE_ID, TEXT_SHOP_MAIL_FROM
//        );
//        $arrSqlSender = Text::getSqlSnippets(
//            '`mail`.`text_sender_id`', $lang_id,
//            MODULE_ID, TEXT_SHOP_MAIL_SENDER
//        );
//        $arrSqlSubject = Text::getSqlSnippets(
//            '`mail`.`text_subject_id`', $lang_id,
//            MODULE_ID, TEXT_SHOP_MAIL_SUBJECT
//        );
//        $arrSqlMessage = Text::getSqlSnippets(
//            '`mail`.`text_message_id`', $lang_id,
//            MODULE_ID, TEXT_SHOP_MAIL_MESSAGE
//        );

//        $objResult = $objDatabase->Execute("
//            SELECT `mail`.`id`, `mail`.`protected`".
//                   $arrSqlName['field'].$arrSqlFrom['field'].
//                   $arrSqlSender['field'].$arrSqlSubject['field'].
//                   $arrSqlMessage['field']."
//              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_mail` AS `mail`".
//                   $arrSqlName['join'].$arrSqlFrom['join'].
//                   $arrSqlSender['join'].$arrSqlSubject['join'].
//                   $arrSqlMessage['join']
//        );
        $objResult = $objDatabase->Execute("
            SELECT `mail`.`id`, `mail`.`tplname`, `mail`.`protected`
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_mail` AS `mail`
        ");
        if (!$objResult) return false;
        while (!$objResult->EOF) {
            $id = $objResult->fields['id'];
            self::$arrTemplate[$id] = array(
                'id' => $id,
                'name' => $objResult->fields['tplname'],
                'protected' => $objResult->fields['protected'],
                // *MUST* be set!
                'available' => false,
            );
            $objResult->MoveNext();
        }
           $objResult = $objDatabase->Execute("
            SELECT `content`.`tpl_id`,
                   `content`.`from_mail`, `content`.`xsender`,
                   `content`.`subject`, `content`.`message`
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_mail_content` AS `content`
            ORDER BY FIELD(`content`.`lang_id`, $defaultLangId, $lang_id) DESC
        ");
        if (!$objResult) return false;
        while (!$objResult->EOF) {
//            $id = $objResult->fields['id'];
//            $available = true;
//            $text_name_id = $objResult->fields[$arrSqlName['name']];
//            $strName = $objResult->fields[$arrSqlName['text']];
//            if ($strName === null) {
//                $available = false;
//                $objText = Text::getById($text_name_id, 0);
//                if ($objText) $strName = $objText->getText();
//            }
//            $text_from_id = $objResult->fields[$arrSqlFrom['name']];
//            $strFrom = $objResult->fields[$arrSqlFrom['text']];
//            if ($strFrom === null) {
//                $available = false;
//                $objText = Text::getById($text_from_id, 0);
//                if ($objText) $strFrom = $objText->getText();
//            }
//            $text_sender_id = $objResult->fields[$arrSqlSender['name']];
//            $strSender = $objResult->fields[$arrSqlSender['text']];
//            if ($strSender === null) {
//                $available = false;
//                $objText = Text::getById($text_sender_id, 0);
//                if ($objText) $strSender = $objText->getText();
//            }
//            $text_subject_id = $objResult->fields[$arrSqlSubject['name']];
//            $strSubject = $objResult->fields[$arrSqlSubject['text']];
//            if ($strSubject === null) {
//                $available = false;
//                $objText = Text::getById($text_subject_id, 0);
//                if ($objText) $strSubject = $objText->getText();
//            }
//            $text_message_id = $objResult->fields[$arrSqlMessage['name']];
//            $strMessage = $objResult->fields[$arrSqlMessage['text']];
//            if ($strMessage === null) {
//                $available = false;
//                $objText = Text::getById($text_message_id, 0);
//                if ($objText) $strMessage = $objText->getText();
//            }
//            self::$arrTemplate[$id] = array(
//                'id' => $id,
//                'protected' => $objResult->fields['protected'],
//                'available' => $available,
//                'text_name_id' => $text_name_id,
//                'name' => $strName,
//                'text_from_id' => $text_from_id,
//                'from' => $strFrom,
//                'text_sender_id' => $text_sender_id,
//                'sender' => $strSender,
//                'text_subject_id' => $text_subject_id,
//                'subject' => $strSubject,
//                'text_message_id' => $text_message_id,
//                'message' => $strMessage,
//            );
//            $id = $objResult->fields['id'];
//            self::$arrTemplate[$id] = array(
//                'id' => $id,
//                'protected' => $objResult->fields['protected'],
//                'available' => true, // post-2.1
//                'name' => $objResult->fields['tplname'],
//                'from' => $objResult->fields['from_mail'],
//                'sender' => $objResult->fields['xsender'],
//                'subject' => $objResult->fields['subject'],
//                'message' => $objResult->fields['message'],
//            );
            $id = $objResult->fields['tpl_id'];
            if (!self::$arrTemplate[$id]['available']) {
                self::$arrTemplate[$id]['available'] = true;
                self::$arrTemplate[$id]['from'] = $objResult->fields['from_mail'];
                self::$arrTemplate[$id]['sender'] = $objResult->fields['xsender'];
                self::$arrTemplate[$id]['subject'] = $objResult->fields['subject'];
                self::$arrTemplate[$id]['message'] = $objResult->fields['message'];
            }
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


    /**
     * Validate the email address
     *
     * Does an extensive syntax check to determine whether the string argument
     * is a real email address.
     * Note that this doesn't mean that the address is necessarily valid,
     * but only that it isn't just an arbitrary character sequence.
     * @todo    Some valid addresses are rejected by this method,
     * such as *%+@mymail.com.
     * Valid (atom) characters are: "!#$%&'*+-/=?^_`{|}~" (without the double quotes),
     * see {@link http://rfc.net/rfc2822.html RFC 2822} for details.
     * @todo    The rules applied to host names are not correct either, see
     * {@link http://rfc.net/rfc1738.html RFC 1738} and {@link http://rfc.net/rfc3986.html}.
     * Excerpt from RFC 1738:
     * - hostport       = host [ ":" port ]
     * - host           = hostname | hostnumber
     * - hostname       = *[ domainlabel "." ] toplabel
     * - domainlabel    = alphadigit | alphadigit *[ alphadigit | "-" ] alphadigit
     * - toplabel       = alpha | alpha *[ alphadigit | "-" ] alphadigit
     * - alphadigit     = alpha | digit
     * Excerpt from RFC 3986:
     * "Non-ASCII characters must first be encoded according to UTF-8 [STD63],
     * and then each octet of the corresponding UTF-8 sequence must be percent-
     * encoded to be represented as URI characters".
     * @todo    This doesn't really belong here.  Should be placed into a
     *          proper core e-mail class as a static method.
     * @version 1.0
     * @param   string  $string
     * @return  boolean
     */
    function isValidAddress($string)
    {
        if (preg_match(
            '/^[a-z0-9]+([-_\.a-z0-9]+)*'.  // user
            '@([a-z0-9]+([-\.a-z0-9]+)*)+'. // domain
            '\.[a-z]{2,4}$/i',              // sld, tld
            $string
        )) return true;
        return false;
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
// post-2.1
//        // Delete all Text records
//        if (!Text::deleteById(self::$arrTemplate[$template_id]['text_name_id'])) return false;
//        if (!Text::deleteById(self::$arrTemplate[$template_id]['text_from_id'])) return false;
//        if (!Text::deleteById(self::$arrTemplate[$template_id]['text_sender_id'])) return false;
//        if (!Text::deleteById(self::$arrTemplate[$template_id]['text_subject_id'])) return false;
//        if (!Text::deleteById(self::$arrTemplate[$template_id]['text_message_id'])) return false;
//        $objResult = $objDatabase->Execute("
//            DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_mail
//             WHERE id=$template_id
//        ");
        $objResult = $objDatabase->Execute("
            DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_mail
             WHERE id=$template_id
        ");
        if (!$objResult) return false;
        $objResult = $objDatabase->Execute("
            DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_mail_content
             WHERE tpl_id=$template_id
        ");
        if (!$objResult) return false;
// post-2.1
//        $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_mail");
        $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_mail");
        $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_mail_content");
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
// pre-2.1
        if (empty($_POST['langId'])) return '';
        $lang_id = $_POST['langId'];
        self::init($lang_id);

//        $text_name_id = 0;
//        $text_from_id = 0;
//        $text_sender_id = 0;
//        $text_subject_id = 0;
//        $text_message_id = 0;
        if ($template_id) {
            $arrTemplate = self::$arrTemplate[$template_id];
            if ($arrTemplate) {
//                $text_name_id = $arrTemplate['text_name_id'];
//                $text_from_id = $arrTemplate['text_from_id'];
//                $text_sender_id = $arrTemplate['text_sender_id'];
//                $text_subject_id = $arrTemplate['text_subject_id'];
//                $text_message_id = $arrTemplate['text_message_id'];
            } else {
                // Template not found.  Clear the ID.
                $template_id = 0;
            }
        }
// Note: Text::replace() now returns the ID, not the object!
//        $objTextName = Text::replace(
//            $text_name_id, FRONTEND_LANG_ID, $_POST['shopMailTemplate'],
//            MODULE_ID, TEXT_SHOP_MAIL_NAME
//        );
//        $objTextFrom = Text::replace(
//            $text_from_id, FRONTEND_LANG_ID, $_POST['shopMailFromAddress'],
//            MODULE_ID, TEXT_SHOP_MAIL_FROM
//        );
//        $objTextSender = Text::replace(
//            $text_sender_id, FRONTEND_LANG_ID, $_POST['shopMailFromName'],
//            MODULE_ID, TEXT_SHOP_MAIL_SENDER
//        );
//        $objTextSubject = Text::replace(
//            $text_subject_id, FRONTEND_LANG_ID, $_POST['shopMailSubject'],
//            MODULE_ID, TEXT_SHOP_MAIL_SUBJECT
//        );
//        $objTextMessage = Text::replace(
//            $text_message_id, FRONTEND_LANG_ID, $_POST['shopMailBody'],
//            MODULE_ID, TEXT_SHOP_MAIL_MESSAGE
//        );
        // If the template ID is known, update.
        // Note that the protected flag is not changed.
        // For newly inserted templates, the protected flag is always 0 (zero).
// post 2.1 -> REMOVE
        $query =
            (   $template_id
             && isset(self::$arrTemplate[$template_id])
            ? "UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_mail
                  SET `tplname`='".contrexx_addslashes($_POST['shopMailTemplate'])."'
                WHERE `id`=$template_id"
             : "INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_mail (
                    `protected`, `tplname`
                ) VALUES (
                    0,
                    '".contrexx_addslashes($_POST['shopMailTemplate'])."'
                )"
         );
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        if (empty($template_id))
            $template_id = $objDatabase->Insert_ID();
        $query =
            (   $template_id
             && self::$arrTemplate[$template_id]['available']
// post 2.1
//          ? "UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_mail
//                SET `text_name_id`=".$objTextName->getId().",
//                    `text_from_id`=".$objTextFrom->getId().",
//                    `text_sender_id`=".$objTextSender->getId().",
//                    `text_subject_id`=".$objTextSubject->getId().",
//                    `text_message_id`=".$objTextMessage->getId()."
//                WHERE `id`=$template_id"
//             : "INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_mail (
//                `protected`,
//                `text_name_id`,
//                `text_from_id`, `text_sender_id`,
//                `text_subject_id`, `text_message_id`
//            ) VALUES (
//                0, ".
//                $objTextName->getId().", ".
//                $objTextFrom->getId().", ".
//                $objTextSender->getId().", ".
//                $objTextSubject->getId().", ".
//                $objTextMessage->getId()."
//            )"
            ? "UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_mail_content
                  SET `from_mail`='".contrexx_addslashes($_POST['shopMailFromAddress'])."',
                      `xsender`='".contrexx_addslashes($_POST['shopMailFromName'])."',
                      `subject`='".contrexx_addslashes($_POST['shopMailSubject'])."',
                      `message`='".contrexx_addslashes($_POST['shopMailBody'])."'
                WHERE `tpl_id`=$template_id
                  AND `lang_id`=$lang_id" //FRONTEND_LANG_ID"
            : "INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_mail_content (
                    `tpl_id`, `lang_id`,
                    `from_mail`, `xsender`,
                    `subject`, `message`
                ) VALUES (
                    $template_id, $lang_id,
                    '".contrexx_addslashes($_POST['shopMailFromAddress'])."',
                    '".contrexx_addslashes($_POST['shopMailFromName'])."',
                    '".contrexx_addslashes($_POST['shopMailSubject'])."',
                    '".contrexx_addslashes($_POST['shopMailBody'])."'
                )"
         );
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        return true;
    }

}

?>
