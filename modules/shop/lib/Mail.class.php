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

        // Reset the language ID used
        self::$lang_id = false;
        // Use the current language if none is specified
        if (empty($lang_id)) $lang_id = LANG_ID;
echo("Mail::init($lang_id): init()ing<br />");
        self::$arrTemplate = array();
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
echo("Mail::init($lang_id): arrSqlName: ".var_export($arrSqlName, true)."<br />");

        $objResult = $objDatabase->Execute("
            SELECT `mail`.`id`, `mail`.`protected`".
                   $arrSqlName['field'].$arrSqlFrom['field'].
                   $arrSqlSender['field'].$arrSqlSubject['field'].
                   $arrSqlMessage['field']."
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_mail` AS `mail`".
                   $arrSqlName['join'].$arrSqlFrom['join'].
                   $arrSqlSender['join'].$arrSqlSubject['join'].
                   $arrSqlMessage['join']
        );
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
//echo("getTemplateArray($lang_id): Entered<br />");
        // If the array has not been initialized, or with another
        // language, call init()
        if (   empty(self::$arrTemplate)
            || $lang_id !== self::$lang_id)
            self::init($lang_id);
/*if ($lang_id) {
            // If a language is specified, only init() if it is different
            // from the last one used.
            if ($lang_id != self::$lang_id) self::init($lang_id);
echo("getTemplateArray($lang_id): init()ed with language ID $lang_id<br />");
        } else {
echo("getTemplateArray($lang_id): init()ed without language ID<br />");
        }
*/
//echo("getTemplateArray($lang_id): returning ".var_export(self::$arrTemplate, true)."<br />");
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

        if (@include_once ASCMS_LIBRARY_PATH.'/phpmailer/class.phpmailer.php') {
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
        }
        return false;
    }


    /**
     * Pick a mail template from the database
     *
     * Get the selected mail template and associated fields from the database.
     * @static
     * @param   integer $template_id     The mail template ID
     * @param   integer $lang_id             The language ID
     * @global  ADONewConnection
     * @return  mixed                       The mail template array on success,
     *                                      false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getTemplate($template_id, $lang_id)
    {
        global $objDatabase;

        if (empty(self::$arrTemplate)) self::init($lang_id);
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
            '\.[a-z]{2,4}$/',               // sld, tld
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
        self::$arrTemplate = false;
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
             WHERE id=$template_id
        ");
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
        $objTextName = Text::replace(
            $text_name_id, FRONTEND_LANG_ID, $_POST['shopMailTemplate'],
            MODULE_ID, TEXT_SHOP_MAIL_NAME
        );
        $objTextFrom = Text::replace(
            $text_from_id, FRONTEND_LANG_ID, $_POST['shopMailFromAddress'],
            MODULE_ID, TEXT_SHOP_MAIL_FROM
        );
        $objTextSender = Text::replace(
            $text_sender_id, FRONTEND_LANG_ID, $_POST['shopMailFromName'],
            MODULE_ID, TEXT_SHOP_MAIL_SENDER
        );
        $objTextSubject = Text::replace(
            $text_subject_id, FRONTEND_LANG_ID, $_POST['shopMailSubject'],
            MODULE_ID, TEXT_SHOP_MAIL_SUBJECT
        );
        $objTextMessage = Text::replace(
            $text_message_id, FRONTEND_LANG_ID, $_POST['shopMailBody'],
            MODULE_ID, TEXT_SHOP_MAIL_MESSAGE
        );
        // If the template ID is known, update.
        // Note that the protected flag is not changed.
        // For newly inserted templates, the protected flag is always 0 (zero).
        $query = ($template_id
          ? "UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_mail
                SET `text_name_id`=".$objTextName->getId().",
                    `text_from_id`=".$objTextFrom->getId().",
                    `text_sender_id`=".$objTextSender->getId().",
                    `text_subject_id`=".$objTextSubject->getId().",
                    `text_message_id`=".$objTextMessage->getId()."
              WHERE `id`=$template_id"
          : "INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_mail (
                `protected`, `text_name_id`,
                `text_from_id`, `text_sender_id`,
                `text_subject_id`, `text_message_id`
            ) VALUES (
                0, ".
                $objTextName->getId().", ".
                $objTextFrom->getId().", ".
                $objTextSender->getId().", ".
                $objTextSubject->getId().", ".
                $objTextMessage->getId()."
            )"
        );
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        return true;
    }

}

?>
