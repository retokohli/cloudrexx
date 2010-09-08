<?php

/**
 * Core Mail and Template Management
 * @version     3.0.0
 * @since       2.2.0
 * @package     contrexx
 * @subpackage  core
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @todo        Test!
 */

require_once ASCMS_CORE_PATH.'/SettingDb.class.php';
require_once ASCMS_CORE_PATH.'/Text.class.php';

/**
 * Core Mail and Template Class
 *
 * Manages e-mail templates in any language, accessible by module
 * and key for easy access.
 * Includes a nice wrapper for the phpmailer class that allows
 * sending all kinds of mail in plain text or HTML, also with
 * attachments.
 * @version     3.0.0
 * @since       2.2.0
 * @package     contrexx
 * @subpackage  core
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @todo        Test!
 */
class MailTemplate
{
    /**
     * Class constant for the mail template name Text key
     */
    const TEXT_NAME = 'core_mail_template_name';
    /**
     * Class constant for the mail template from Text key
     */
    const TEXT_FROM = 'core_mail_template_from';
    /**
     * Class constant for the mail template sender Text key
     */
    const TEXT_SENDER = 'core_mail_template_sender';
    /**
     * Class constant for the mail template reply Text key
     */
    const TEXT_REPLY = 'core_mail_template_reply';
    /**
     * Class constant for the mail template to Text key
     */
    const TEXT_TO = 'core_mail_template_to';
    /**
     * Class constant for the mail template cc Text key
     */
    const TEXT_CC = 'core_mail_template_cc';
    /**
     * Class constant for the mail template bcc Text key
     */
    const TEXT_BCC = 'core_mail_template_bcc';
    /**
     * Class constant for the mail template subject Text key
     */
    const TEXT_SUBJECT = 'core_mail_template_subject';
    /**
     * Class constant for the mail template message plain Text key
     */
    const TEXT_MESSAGE = 'core_mail_template_message';
    /**
     * Class constant for the mail template message HTML Text key
     */
    const TEXT_MESSAGE_HTML = 'core_mail_template_message_html';
    /**
     * Class constant for the mail template attachments Text key
     */
    const TEXT_ATTACHMENTS = 'core_mail_template_attachments';
    /**
     * Class constant for the mail template inline Text key
     */
    const TEXT_INLINE = 'core_mail_template_inline';

    /**
     * The language ID used when init() was called
     * @var integer
     */
    private static $lang_id = false;

    /**
     * The array of loaded mail templates
     * @var array
     */
    private static $arrTemplates = false;

    /**
     * Success notice
     * @var     string
     * @static
     * @access  private
     * @todo    Use the message system of the calling class or the core system
     */
    private static $strOkMessage = '';

    /**
     * Failure notice
     * @var     string
     * @static
     * @access  private
     * @todo    Use the message system of the calling class or the core system
     */
    private static $strErrMessage = '';

    /**
     * Returns a new, empty Mailtemplate
     *
     * Note that this is *NOT* a constructor, but a static method that
     * returns an empty template array with all fields set but empty.
     * @param   string    $key      The optional key
     * @return  array               The Mailtemplate array
     */
    static function getNew($key='')
    {
        return array(
            'key'                  => $key,
            'text_name_id'         => 0,
            'name'                 => '',
            'text_from_id'         => 0,
            'from'                 => '',
            'text_sender_id'       => 0,
            'sender'               => '',
            'text_reply_id'        => 0,
            'reply'                => '',
            'text_to_id'           => 0,
            'to'                   => '',
            'text_cc_id'           => 0,
            'cc'                   => '',
            'text_bcc_id'          => 0,
            'bcc'                  => '',
            'text_subject_id'      => 0,
            'subject'              => '',
            'text_message_id'      => 0,
            'message'              => '',
            'text_message_html_id' => 0,
            'message_html'         => '',
            'text_attachments_id'  => 0,
            'attachments'          => '',
            'text_inline_id'       => 0,
            'inline'               => '',
            'protected'            => 0,
            'html'                 => 0,
            'available'            => false,
        );
    }


    /**
     * Reset the class
     *
     * Forces a call to {@link init()} the next time content is accessed
     */
    static function reset()
    {
        self::$lang_id = false;
        self::$arrTemplates = false;
    }


    /**
     * Initialize the mail template array for the current module
     *
     * The module ID is determined by the global MODULE_ID constant.
     * Uses the given language ID $lang_id, if not empty, or the language
     * set in the FRONTEND_LANG_ID global constant.
     * Upon success, stores the language ID used in the $lang_id class
     * variable, so that the initialisation can be skipped if more
     * templates are accessed.
     * The $limit value defaults to the value of the
     * mailtemplate_per_page_backend setting from the core settings
     * (@see SettingDb}.
     * @param   integer     $lang_id        The optional language ID
     * @param   string      $order          The optional sorting order string,
     *                                      SQL syntax
     * @param   integer     $position       The optional position offset,
     *                                      defaults to zero
     * @param   integer     $limit          The optional limit for returned
     *                                      templates
     * @param   integer     $count          The actual count of templates
     *                                      available in total, by reference
     * @return  boolean                     True on success, false otherwise
     */
    static function init(
        $lang_id=0, $order='', $position=0, $limit=-1, &$count=0
    ) {
        global $objDatabase;

        // Use the current language if none is specified
        if (intval($lang_id) == 0) $lang_id = FRONTEND_LANG_ID;
        // Has the array been initialized with that language already?
        if (self::$lang_id === $lang_id) return true;

        self::reset();

        if (empty($limit)) $limit = SettingDb::getValue(
            'mailtemplate_per_page_backend');
        if (empty($limit)) $limit = 20;

        $arrSqlName = Text::getSqlSnippets(
            '`mail`.`text_name_id`', $lang_id, MODULE_ID,
            self::TEXT_NAME, 'name');
        $arrSqlFrom = Text::getSqlSnippets(
            '`mail`.`text_from_id`', $lang_id, MODULE_ID,
            self::TEXT_FROM, 'from');
        $arrSqlSender = Text::getSqlSnippets(
            '`mail`.`text_sender_id`', $lang_id, MODULE_ID,
            self::TEXT_SENDER, 'sender');
        $arrSqlReply = Text::getSqlSnippets(
            '`mail`.`text_reply_id`', $lang_id, MODULE_ID,
            self::TEXT_REPLY, 'reply');
        $arrSqlTo = Text::getSqlSnippets(
            '`mail`.`text_to_id`', $lang_id, MODULE_ID,
            self::TEXT_TO, 'to');
        $arrSqlCc = Text::getSqlSnippets(
            '`mail`.`text_cc_id`', $lang_id, MODULE_ID,
            self::TEXT_CC, 'cc');
        $arrSqlBcc = Text::getSqlSnippets(
            '`mail`.`text_bcc_id`', $lang_id, MODULE_ID,
            self::TEXT_BCC, 'bcc');
        $arrSqlSubject = Text::getSqlSnippets(
            '`mail`.`text_subject_id`', $lang_id, MODULE_ID,
            self::TEXT_SUBJECT, 'subject');
        $arrSqlMessage = Text::getSqlSnippets(
            '`mail`.`text_message_id`', $lang_id, MODULE_ID,
            self::TEXT_MESSAGE, 'message');
        $arrSqlMessageHtml = Text::getSqlSnippets(
            '`mail`.`text_message_html_id`', $lang_id, MODULE_ID,
            self::TEXT_MESSAGE_HTML, 'message_html');
        $arrSqlAttachments = Text::getSqlSnippets(
            '`mail`.`text_attachments_id`', $lang_id, MODULE_ID,
            self::TEXT_ATTACHMENTS, 'attachments');
        $arrSqlInline = Text::getSqlSnippets(
            '`mail`.`text_inline_id`', $lang_id, MODULE_ID,
            self::TEXT_INLINE, 'inline');

        $query_from = "
              FROM `".DBPREFIX."core_mail_template` AS `mail`".
                   $arrSqlName['join'].
                   $arrSqlFrom['join'].
                   $arrSqlSender['join'].
                   $arrSqlReply['join'].
                   $arrSqlTo['join'].
                   $arrSqlCc['join'].
                   $arrSqlBcc['join'].
                   $arrSqlSubject['join'].
                   $arrSqlMessage['join'].
                   $arrSqlMessageHtml['join'].
                   $arrSqlAttachments['join'].
                   $arrSqlInline['join']."
             WHERE `mail`.`module_id`=".MODULE_ID;
        $query_order = ($order ? " ORDER BY $order" : '');

        // The count of available templates needs to be initialized to zero
        // in case there is a problem with one of the queries ahead.
        // Ignore the code analyzer warning.
        $count = 0;
        $objResult = $objDatabase->SelectLimit("
            SELECT `mail`.`key`, `mail`.`protected`, `mail`.`html`".
                   $arrSqlName['field'].
                   $arrSqlFrom['field'].
                   $arrSqlSender['field'].
                   $arrSqlReply['field'].
                   $arrSqlTo['field'].
                   $arrSqlCc['field'].
                   $arrSqlBcc['field'].
                   $arrSqlSubject['field'].
                   $arrSqlMessage['field'].
                   $arrSqlMessageHtml['field'].
                   $arrSqlAttachments['field'].
                   $arrSqlInline['field']."
            $query_from
            $query_order",
            $limit, $position);
        if (!$objResult) return self::errorHandler();
//DBG::log("MailTemplate::init($lang_id): Result<br />".var_export($objResult, true)."<hr />");
        self::$arrTemplates = array();
        while (!$objResult->EOF) {
            $available = true;
            $key = $objResult->fields['key'];
            $text_name_id = $objResult->fields[$arrSqlName['name']];
            $strName = $objResult->fields[$arrSqlName['text']];
            if ($strName === null && $text_name_id) {
                $strName = Text::getById($text_name_id, 0)->getText();
                $available = false;
            }
            $text_from_id = $objResult->fields[$arrSqlFrom['name']];
            $strFrom = $objResult->fields[$arrSqlFrom['text']];
            if ($strFrom === null && $text_from_id) {
                $strFrom = Text::getById($text_from_id, 0)->getText();
                $available = false;
            }
            $text_sender_id = $objResult->fields[$arrSqlSender['name']];
            $strSender = $objResult->fields[$arrSqlSender['text']];
            if ($strSender === null && $text_sender_id) {
                $strSender = Text::getById($text_sender_id, 0)->getText();
                $available = false;
            }
            $text_reply_id = $objResult->fields[$arrSqlReply['name']];
            $strReply = $objResult->fields[$arrSqlReply['text']];
            if ($strReply === null && $text_reply_id) {
                $strReply = Text::getById($text_reply_id, 0)->getText();
                $available = false;
            }
            $text_to_id = $objResult->fields[$arrSqlTo['name']];
            $strTo = $objResult->fields[$arrSqlTo['text']];
            if ($strTo === null && $text_to_id) {
                $strTo = Text::getById($text_to_id, 0)->getText();
                $available = false;
            }
            $text_cc_id = $objResult->fields[$arrSqlCc['name']];
            $strCc = $objResult->fields[$arrSqlCc['text']];
            if ($strCc === null && $text_cc_id) {
                $strCc = Text::getById($text_cc_id, 0)->getText();
                $available = false;
            }
            $text_bcc_id = $objResult->fields[$arrSqlBcc['name']];
            $strBcc = $objResult->fields[$arrSqlBcc['text']];
            if ($strBcc === null && $text_bcc_id) {
                $strBcc = Text::getById($text_bcc_id, 0)->getText();
                $available = false;
            }
            $text_subject_id = $objResult->fields[$arrSqlSubject['name']];
            $strSubject = $objResult->fields[$arrSqlSubject['text']];
            if ($strSubject === null && $text_subject_id) {
                $strSubject = Text::getById($text_subject_id, 0)->getText();
                $available = false;
            }
            $text_message_id = $objResult->fields[$arrSqlMessage['name']];
            $strMessage = $objResult->fields[$arrSqlMessage['text']];
            if ($strMessage === null && $text_message_id) {
                $strMessage = Text::getById($text_message_id, 0)->getText();
                $available = false;
            }
            $text_message_html_id = $objResult->fields[$arrSqlMessageHtml['name']];
            $strMessageHtml = $objResult->fields[$arrSqlMessageHtml['text']];
            if ($strMessageHtml === null && $text_message_html_id) {
                $strMessageHtml = Text::getById($text_message_html_id, 0)->getText();
                $available = false;
            }
            $text_attachments_id = $objResult->fields[$arrSqlAttachments['name']];
            $strAttachments = $objResult->fields[$arrSqlAttachments['text']];
            if ($strAttachments === null && $text_attachments_id) {
                $strAttachments = Text::getById($text_attachments_id, 0)->getText();
                $available = false;
            }
            $text_inline_id = $objResult->fields[$arrSqlInline['name']];
            $strInline = $objResult->fields[$arrSqlInline['text']];
            if ($strInline === null && $text_inline_id) {
                $strInline = Text::getById($text_inline_id, 0)->getText();
                $available = false;
            }
// TODO: Hard to decide which should be mandatory, as any of them may
// be filled in "just in time". -- Time will tell.
            if (   $strName == ''
//                || $strFrom == ''
//                || $strSender == ''
//                || $strReply == ''
//                || $strTo == ''
//                || $strCc == ''
//                || $strBcc == ''
                || $strSubject == ''
                || $strMessage == ''
//                || $strMessageHtml == ''
//                || $strAttachments == ''
//                || $strInline == ''
            ) $available = false;

            self::$arrTemplates[$key] = array(
                'key'                  => $key,
                'text_name_id'         => $text_name_id,
                'name'                 => $strName,
                'text_from_id'         => $text_from_id,
                'protected'            => $objResult->fields['protected'],
                'html'                 => $objResult->fields['html'],
                'from'                 => $strFrom,
                'text_sender_id'       => $text_sender_id,
                'sender'               => $strSender,
                'text_reply_id'        => $text_reply_id,
                'reply'                => $strReply,
                'text_to_id'           => $text_to_id,
                'to'                   => $strTo,
                'text_cc_id'           => $text_cc_id,
                'cc'                   => $strCc,
                'text_bcc_id'          => $text_bcc_id,
                'bcc'                  => $strBcc,
                'text_subject_id'      => $text_subject_id,
                'subject'              => $strSubject,
                'text_message_id'      => $text_message_id,
                'message'              => $strMessage,
                'text_message_html_id' => $text_message_html_id,
                'message_html'         => $strMessageHtml,
                'text_attachments_id'  => $text_attachments_id,
                'attachments'          => eval("$strAttachments;"),
                'text_inline_id'       => $text_inline_id,
                'inline'               => eval("$strInline;"),
                'available'            => $available,
            );
            $objResult->MoveNext();
        }

        $objResult = $objDatabase->Execute("
            SELECT COUNT(*) AS `count` $query_from");
        if (!$objResult) return self::errorHandler();
        $count += $objResult->fields['count'];
        // Remember the language used
        self::$lang_id = $lang_id;
        return true;
    }


    /**
     * Returns the complete array of templates available for the current
     * module.
     *
     * If the optional $lang_id argument is empty, the global FRONTEND_LANG_ID
     * constant is used instead.
     * @param   integer   $lang_id    The optional language ID
     * @return  mixed                 The template array on success,
     *                                false otherwise
     */
    static function getTemplateArray(
        $lang_id=0,
        $order, $position, $limit, &$count
    ) {

//echo("MailTemplate::getTemplateArray(lang_id $lang_id, order $order, position $position, limit $limit, count $count): Entered<br />");

        if (empty($lang_id)) $lang_id = FRONTEND_LANG_ID;
        self::init($lang_id, $order, $position, $limit, $count);
        return self::$arrTemplates;
    }


    /**
     * Returns the selected mail template and associated fields
     * from the database.
     *
     * The $key parameter uniquely identifies the template for each
     * module.
     * The optional $lang_id may be provided to override the language ID
     * present in the global FRONTEND_LANG_ID constant.
     * @param   string  $key            The key identifying the template
     * @param   integer $lang_id        The optional language ID
     * @return  mixed                   The mail template array on success,
     *                                  false otherwise
     * @global  ADONewConnection
     * @static
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getTemplate($key, $lang_id=0)
    {
        if (empty($lang_id)) $lang_id = FRONTEND_LANG_ID;
        self::init($lang_id);
        if (isset(self::$arrTemplates[$key]))
            return self::$arrTemplates[$key];
//DBG::log("Template not found for key $key<br />");
        return false;
    }


    /**
     * Set up and send an email
     *
     * The array argument is searched for the following indices:
     *  key           The key of any mail template to be used
     *  sender        The sender name
     *  from          The sender e-mail address
     *  to            The recipient e-mail address(es), comma separated
     *  reply         The reply-to e-mail address
     *  cc            The carbon copy e-mail address(es), comma separated
     *  bcc           The blind carbon copy e-mail address(es), comma separated
     *  subject       The message subject
     *  message       The plain text message body
     *  message_html  The HTML message body
     *  html          If this evaluates to true, turns on HTML mode
     *  attachments   An array of file paths to attach.  The array keys may
     *                be used for the paths, and the values for the name.
     *                If the keys are numeric, the values are regarded as paths.
     *  inline        An array of inline (image) file paths to attach.
     *                If this is used, HTML mode is switched on automatically.
     *  search        The array of patterns to be replaced by...
     *  replace       The array of replacements for the patterns
     * If the key index is present, the corresponding mail template is loaded
     * first.  Other indices present (sender, from, to, subject, message)
     * will override the template fields.
     * Missing mandatory fields are filled with the
     * default values from the global $_CONFIG array (sender, from, to),
     * or some core language variables (subject, message).
     * A simple {@see str_replace()} is used for the search and replace
     * operation, and the placeholder names are quoted in the substitution,
     * so you cannot use regular expressions.
     * Note:  The attachment paths must comply with the requirements for
     * file paths as defined in the {@see File} class version 2.2.0.
     * @static
     * @param   array     $arrField         The array of template fields
     * @return  boolean                     True if the mail could be sent,
     *                                      false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function send($arrField)
    {
        global $_CONFIG; //, $_CORELANG;

        if (!@include_once ASCMS_LIBRARY_PATH.'/phpmailer/class.phpmailer.php') {
            return false;
        }
        $objMail = new phpmailer();
        if (   !empty($_CONFIG['coreSmtpServer'])
            && @include_once ASCMS_CORE_PATH.'/SmtpSettings.class.php') {
            $arrSmtp = SmtpSettings::getSmtpAccount($_CONFIG['coreSmtpServer']);
            if ($arrSmtp) {
                $objMail->IsSMTP();
                $objMail->SMTPAuth = true;
                $objMail->Host     = $arrSmtp['hostname'];
                $objMail->Port     = $arrSmtp['port'];
                $objMail->Username = $arrSmtp['username'];
                $objMail->Password = $arrSmtp['password'];
            }
        }

        $lang_id = (empty($arrField['lang_id'])
              ? FRONTEND_LANG_ID : $arrField['lang_id']);
        $arrTemplate = null;
        if (!empty($arrField['key']))
            $arrTemplate = self::getTemplate($arrField['key'], $lang_id);
        if (empty($arrTemplate)) return false;
//DBG::log("MailTemplate::send(): Template<br />".var_export($arrTemplate, true)."<hr />");
        $search  =
            (isset($arrField['search']) && is_array($arrField['search'])
              ? $arrField['search']  : null);
        $replace =
            (isset($arrField['replace']) && is_array($arrField['replace'])
              ? $arrField['replace'] : null);

        foreach ($arrTemplate as $field => &$value) {
            if (isset($arrField[$field])) $value = $arrField[$field];
// TODO: Fix the regex
//                preg_replace('/\015?\012/', "\015\012", $value);
            if ($search) {
                $value = str_replace($search, $replace, $value);
            }
            if (   isset($arrField['substitution'])
                && is_array($arrField['substitution'])) {
//echo("Substitution:<br />".var_export($arrField['substitution'], true)."<hr />");
                self::substitute($value, $arrField['substitution']);
            }
            self::clearEmptyPlaceholders($value);
        }
//DBG::log("MailTemplate::send(): Substituted: ".var_export($arrTemplate, true));
//echo("MailTemplate::send(): Substituted:<br /><pre>".nl2br(htmlentities(var_export($arrTemplate, true), ENT_QUOTES, CONTREXX_CHARSET))."</PRE><hr />");

        // Use defaults for missing mandatory fields
//        if (empty($arrTemplate['sender']))
//            $arrTemplate['sender'] = $_CONFIG['coreAdminName'];
        if (empty($arrTemplate['from']))
            $arrTemplate['from'] = $_CONFIG['coreAdminEmail'];
        if (empty($arrTemplate['to']))
            $arrTemplate['to'] = $_CONFIG['coreAdminEmail'];
//        if (empty($arrTemplate['subject']))
//            $arrTemplate['subject'] = $_CORELANG['TXT_CORE_MAILTEMPLATE_NO_SUBJECT'];
//        if (empty($arrTemplate['message']))
//            $arrTemplate['message'] = $_CORELANG['TXT_CORE_MAILTEMPLATE_NO_MESSAGE'];

        $objMail->FromName = $arrTemplate['sender'];
        $objMail->From = $arrTemplate['from'];
        $objMail->Subject = $arrTemplate['subject'];
        $objMail->CharSet = CONTREXX_CHARSET;
//        $objMail->IsHTML(false);
        if ($arrTemplate['html']) {
            $objMail->IsHTML(true);
            $objMail->Body = $arrTemplate['message_html'];
            $objMail->AltBody = $arrTemplate['message'];
        } else {
            $objMail->Body = $arrTemplate['message'];
        }
        foreach (preg_split('/\s*,\s*/', $arrTemplate['reply'], null, PREG_SPLIT_NO_EMPTY) as $address) {
            $objMail->AddReplyTo($address);
        }
//        foreach (preg_split('/\s*,\s*/', $arrTemplate['to'], null, PREG_SPLIT_NO_EMPTY) as $address) {
//            $objMail->AddAddress($address);
//        }
        foreach (preg_split('/\s*,\s*/', $arrTemplate['cc'], null, PREG_SPLIT_NO_EMPTY) as $address) {
            $objMail->AddCC($address);
        }
        foreach (preg_split('/\s*,\s*/', $arrTemplate['bcc'], null, PREG_SPLIT_NO_EMPTY) as $address) {
            $objMail->AddBCC($address);
        }

        if (   isset($arrField['attachments'])
            && is_array($arrField['attachments'])) {
            foreach ($arrField['attachments'] as $path => $name) {
                $arrTemplate[$path] = $name;
//echo("Field Attachment: $path / $name\n");
            }
        }
        $arrTemplate['attachments'] = self::attachmentsToArray($arrTemplate['attachments']);
//unset($_SESSION['hotelcard_order']);die("SEND");
        foreach ($arrTemplate['attachments'] as $path => $name) {
            if (is_numeric($path)) $path = $name;
            $objMail->AddAttachment(ASCMS_DOCUMENT_ROOT.'/'.$path, $name);
//echo("Template Attachment: $path / $name\n");
        }
        $arrTemplate['inline'] = self::attachmentsToArray($arrTemplate['inline']);
        if ($arrTemplate['inline']) $arrTemplate['html'] = true;
        foreach ($arrTemplate['inline'] as $path => $name) {
            if (is_numeric($path)) $path = $name;
            $objMail->AddEmbeddedImage(ASCMS_DOCUMENT_ROOT.'/'.$path, uniqid(), $name);
        }
        if (   isset($arrField['inline'])
            && is_array($arrField['inline'])) {
            foreach ($arrField['inline'] as $path => $name) {
                if (is_numeric($path)) $path = $name;
                $objMail->AddEmbeddedImage(ASCMS_DOCUMENT_ROOT.'/'.$path, uniqid(), $name);
            }
        }
//unset($_SESSION['hotelcard_order']);
//die("MailTemplate::send(): Attachments and inlines<br />".var_export($objMail, true));
        $objMail->CharSet = CONTREXX_CHARSET;
        $objMail->IsHTML($arrTemplate['html']);
//DBG::log("MailTemplate::send(): Sending: ".nl2br(htmlentities(var_export($objMail, true), ENT_QUOTES, CONTREXX_CHARSET))."<br />Sending...<hr />");
        $result = true;
        foreach (preg_split('/\s*,\s*/', $arrTemplate['to'], null, PREG_SPLIT_NO_EMPTY) as $address) {
            $objMail->ClearAddresses();
            $objMail->AddAddress($address);
//die("MailTemplate::send(): ".var_export($objMail, true));
// TODO: Comment for test only!
            $result &= $objMail->Send();
// TODO: $objMail->Send() seems to always return true on localhost where
// sending the mail is actually impossible.  Dunno why.
//echo("MailTemplate::send(): result: ".($result ? "OK" : "FAILED")."<br />");
        }
        return $result;
    }


    /**
     * Substitutes the placeholders found in the $substitution array in $value
     *
     * Each array key in $substitution is regarded as a placeholder name.
     * Each name is enclosed in square brackets ("[", "]") to form the
     * full placeholder.
     * If its value is an array, it represents a repeatable block with contents
     * in an (indexed) array, otherwise it's a simple replacement.
     *
     * Your template $string might look something like
     *  '[BLOCK]This line is repeated for each [ITEM] in the block.[BLOCK]'
     *  'A single [VALUE] is substituted here once'
     *
     * The $substitution array looks like
     *  array(
     *    'PLACEHOLDER' => 'Scalar replacement value',
     *    'BLOCK'       => array(
     *      index => array(
     *        'MORE_PLACEHOLDERS_OR_BLOCKS' => ...
     *      ),
     *      ... more ...
     *    ),
     *    ... more ...
     *  )
     *
     * Note that each block name *MUST* occur exactly twice in the string,
     * or it won't be recognized.
     *
     * Mind that the names in the substitution array *SHOULD* be unique,
     * or the order of the elements becomes relevant and the results may not
     * be what you expect!  The array is processed depth-first, so every time
     * a block array is encountered, it is completely substituted recursively
     * before the next value is even looked at.
     *
     * Final note:
     * To replace any blocks or placeholders from the string that have not been
     * substituted, you will need to provide a means yourself.
     * @param   string    $string         The string to be searched and replaced,
     *                                    by reference
     * @param   array     $substitution   The array of placeholders and values,
     *                                    by reference
     */
    static function substitute(&$string, &$substitution)
    {
        $match = array();
        foreach ($substitution as $placeholder => $value) {
            if (is_array($value)) {
                $block_quoted = preg_quote("[$placeholder]", '/');
                $block_re = '/'.$block_quoted.'(.+?)'.$block_quoted.'/s';
//echo("substitute(): BLOCK $block_quoted<br />");
                if (   preg_match($block_re, $string, $match)
                    && $match[1]) {
                    $block_template = $match[1];
//echo("substitute(): Block template: $block_template<hr />");
                    $block_parsed = '';
                    foreach ($value as $value_inner) {
                        $block = $block_template;
                        self::substitute($block, $value_inner);
                        $block_parsed .= $block;
//echo("substitute(): Block parsed: $block<hr />");
                    }
                    $string = preg_replace($block_re, $block_parsed, $string);
//echo("substitute(): Block substituted: $block_parsed<hr />");
//echo("substitute(): New string: $string<hr />");
                }
            } else {
                $placeholder_quoted = preg_quote("[$placeholder]", '/');
//echo("substitute(): PLACEHOLDER $placeholder_quoted<br />");
                $string = preg_replace(
                    '/'.$placeholder_quoted.'/', $value, $string);
//echo("substitute(): made $string<hr />");
            }
        }
//die("Leaving substitute($string, &$substitution)");
    }


    /**
     * Removes left over placeholders from the string
     * @param   string    $value        The string, by reference
     */
    static function clearEmptyPlaceholders(&$value)
    {
        // Replace left over blocks
        $value = preg_replace('/(\[[A-Z_]+\]).+?\1/s', '', $value);
        // Replace left over placeholders
        $value = preg_replace('/\[[A-Z_]+\]/', '', $value);
    }


    /**
     * Converts the attachment string from the database table into an array,
     * if necessary
     *
     * If the parameter value is an array already, it is returned unchanged.
     * If the parameter value is a string, it *MUST* be in one of the forms
     *  'return array();'
     * or
     *  'return array(index => "path/filename");'
     * or
     *  'return array("path" => "filename");'
     * with zero or more entries containing at least the "filename" as value
     * and an optional path as key.  "path" *MUST* be either a path relative
     * to the document root (including the file name), or a numeric key.
     * If "path" is numeric, it will be ignored and the "filename" will be used.
     * In this case, "filename" itself needs to contain the path to the
     * attachment.
     * The third form allows you to specify a file name different from the
     * original provided in "path" to be used for the e-mail.
     * @param   mixed     $attachments      The attachment string or array
     * @return  array                       The attachment array on success,
     *                                      the empty array otherwise
     */
    static function attachmentsToArray($attachments)
    {
        if (is_array($attachments)) return $attachments;
        $arrAttachments = array();
//echo("Attachment string: ".var_export($attachments, true)."\n");
        try {
            $arrAttachments = @eval($attachments);
        } catch (Exception $e) {
            DBG::log($e->__toString());
//echo("Eval error: /".$e->__toString()."/\n");
        }
//echo("Attachment array: ".var_export($arrAttachments, true)."\n");
//unset($_SESSION['hotelcard_order']);die("EVAL");
        if (!is_array($arrAttachments)) $arrAttachments = array();
        return $arrAttachments;
    }


    /**
     * Delete the template with the given key
     *
     * Protected (system) templates can not be deleted.
     * Deletes all languages available.
     * if the $key argument is left out, looks for a key in the
     * delete_mailtemplate_key index of the $_REQUEST array.
     * @param   string    $key    The optional template key
     * @return  boolean           True on success, false otherwise
     */
    static function deleteTemplate($key='')
    {
        global $objDatabase, $_CORELANG;

//echo("MailTemplate::deleteTemplate($key): Entered<br />");

        if (empty($key)) {
            if (empty($_REQUEST['delete_mailtemplate_key'])) return '';
            $key = $_REQUEST['delete_mailtemplate_key'];
            // Prevent this from being run twice
            unset($_REQUEST['delete_mailtemplate_key']);
        }

        $arrTemplate = self::getTemplate($key);
//echo("MailTemplate::deleteTemplate(): Loaded template<br />".var_export($arrTemplate, true)."<br />");
        // Cannot delete protected (system) templates
        if ($arrTemplate['protected']) {
            self::addError($_CORELANG['TXT_CORE_MAILTEMPLATE_IS_PROTECTED']);
            return false;
        }

        // Preemptively force a reinit
        self::reset();

        // Delete associated Text records
        if (!Text::deleteById($arrTemplate['text_name_id']))         return false;
        if (!Text::deleteById($arrTemplate['text_from_id']))         return false;
        if (!Text::deleteById($arrTemplate['text_sender_id']))       return false;
        if (!Text::deleteById($arrTemplate['text_reply_id']))        return false;
        if (!Text::deleteById($arrTemplate['text_to_id']))           return false;
        if (!Text::deleteById($arrTemplate['text_cc_id']))           return false;
        if (!Text::deleteById($arrTemplate['text_bcc_id']))          return false;
        if (!Text::deleteById($arrTemplate['text_subject_id']))      return false;
        if (!Text::deleteById($arrTemplate['text_message_id']))      return false;
        if (!Text::deleteById($arrTemplate['text_message_html_id'])) return false;
        if (!Text::deleteById($arrTemplate['text_attachments_id']))  return false;
        if (!Text::deleteById($arrTemplate['text_inline_id']))       return false;
        if (!$objDatabase->Execute("
            DELETE FROM `".DBPREFIX."core_mail_template`
             WHERE `key`='".addslashes($key)."'")) {
            self::addError($_CORELANG['TXT_CORE_MAILTEMPLATE_DELETING_FAILED']);
            return false;
        }
        self::addMessage($_CORELANG['TXT_CORE_MAILTEMPLATE_DELETED_SUCCESSFULLY']);
        $objDatabase->Execute("OPTIMIZE TABLE `".DBPREFIX."core_mail_template`");
        return true;
    }


    /**
     * Update or add a new template
     *
     * Stores the template for the current module ID from the global
     * MODULE_ID constant.
     * Uses the language ID from the lang_id index, if present,
     * or the FRONTEND_LANG_ID constant otherwise.
     *  key           The key of any mail template to be used
     *  lang_id       The language ID
     *  sender        The sender name
     *  from          The sender e-mail address
     *  to            The recipient e-mail address(es), comma separated
     *  reply         The reply-to e-mail address
     *  cc            The carbon copy e-mail address(es), comma separated
     *  bcc           The blind carbon copy e-mail address(es), comma separated
     *  subject       The message subject
     *  message       The plain text message body
     *  message_html  The HTML message body
     *  html          If this evaluates to true, turns on HTML mode
     *  attachments   An array of file paths to attach.  The array keys may
     *                be used for the paths, and the values for the name.
     *                If the keys are numeric, the values are regarded as paths.
     * The key index is mandatory.  If available, the corresponding mail
     * template is loaded, and updated.
     * Missing fields are filled with default values, which are generally empty.
     * The protected flag can neither be set nor cleared by calling this method,
     * but is always kept as-is.
     * Note:  The attachment paths must comply with the requirements for
     * file paths as defined in the {@see File} class version 2.2.0.
     * @param   array     $arrField   The field array
     * @return  boolean               True on success, false otherwise
     */
    static function storeTemplate($arrField)
    {
        global $objDatabase;

//DBG::log("MailTemplate::storeTemplate(".var_export($arrField, true).": Entered<hr />");

        if (empty($arrField['key'])) return false;

// TODO: Field verification
// This is non-trivial, as any placeholders must also be recognized and accepted!
//        if (!empty($arrField['from']) && !FWValidator::isEmail($arrField['from'])) ...

        $lang_id = (isset($arrField['lang_id'])
            ? $arrField['lang_id'] : FRONTEND_LANG_ID);
        $key = (isset($arrField['key'])
            ? $arrField['key'] : '');
        // Strip crap characters from the key
        $key = preg_replace('/[^_a-z\d]/i', '', $key);
        $text_name_id         = 0;
        $text_from_id         = 0;
        $text_sender_id       = 0;
        $text_reply_id        = 0;
        $text_to_id           = 0;
        $text_cc_id           = 0;
        $text_bcc_id          = 0;
        $text_subject_id      = 0;
        $text_message_id      = 0;
        $text_message_html_id = 0;
        $text_attachments_id  = 0;
        $text_inline_id       = 0;

        $update = false;
        // The original template is needed for the Text IDs and protected
        // flag only
        $arrTemplate = self::getTemplate($key, $lang_id);
//echo("MailTemplate::storeTemplate(): Loaded Template<br />".var_export($arrTemplate, true)."<hr />");
        if ($arrTemplate) { // && $arrTemplate['available']) {
            $update = true;
            $text_name_id          = $arrTemplate['text_name_id'];
            $text_from_id          = $arrTemplate['text_from_id'];
            $text_sender_id        = $arrTemplate['text_sender_id'];
            $text_reply_id         = $arrTemplate['text_reply_id'];
            $text_to_id            = $arrTemplate['text_to_id'];
            $text_cc_id            = $arrTemplate['text_cc_id'];
            $text_bcc_id           = $arrTemplate['text_bcc_id'];
            $text_subject_id       = $arrTemplate['text_subject_id'];
            $text_message_id       = $arrTemplate['text_message_id'];
            $text_message_html_id  = $arrTemplate['text_message_html_id'];
            $text_attachments_id   = $arrTemplate['text_attachments_id'];
            $text_inline_id        = $arrTemplate['text_inline_id'];
            $arrField['protected'] = $arrTemplate['protected'];
        }

        if (empty($arrField['name'])) {
            if ($text_name_id) {
                Text::deleteById($text_name_id, $lang_id);
            }
            $text_name_id = 0;
        } else {
            $text_name_id = Text::replace(
                $text_name_id, $lang_id, $arrField['name'],
                MODULE_ID, self::TEXT_NAME
            );
        }
        if (empty($arrField['from'])) {
            if ($text_from_id) {
                Text::deleteById($text_from_id, $lang_id);
            }
            $text_from_id = 0;
        } else {
            $text_from_id = Text::replace(
                $text_from_id, $lang_id, $arrField['from'],
                MODULE_ID, self::TEXT_FROM
            );
        }
        if (empty($arrField['sender'])) {
            if ($text_sender_id) {
                Text::deleteById($text_sender_id, $lang_id);
            }
            $text_sender_id = 0;
        } else {
            $text_sender_id = Text::replace(
                $text_sender_id, $lang_id, $arrField['sender'],
                MODULE_ID, self::TEXT_SENDER
            );
        }
        if (empty($arrField['reply'])) {
            if ($text_reply_id) {
                Text::deleteById($text_reply_id, $lang_id);
            }
            $text_reply_id = 0;
        } else {
            $text_reply_id = Text::replace(
                $text_reply_id, $lang_id, $arrField['reply'],
                MODULE_ID, self::TEXT_REPLY
            );
        }
        if (empty($arrField['to'])) {
            if ($text_to_id) {
                Text::deleteById($text_to_id, $lang_id);
            }
            $text_to_id = 0;
        } else {
            $text_to_id = Text::replace(
                $text_to_id, $lang_id, $arrField['to'],
                MODULE_ID, self::TEXT_TO
            );
        }
        if (empty($arrField['cc'])) {
            if ($text_cc_id) {
                Text::deleteById($text_cc_id, $lang_id);
            }
            $text_cc_id = 0;
        } else {
            $text_cc_id = Text::replace(
                $text_cc_id, $lang_id, $arrField['cc'],
                MODULE_ID, self::TEXT_CC
            );
        }
        if (empty($arrField['bcc'])) {
            if ($text_bcc_id) {
                Text::deleteById($text_bcc_id, $lang_id);
            }
            $text_bcc_id = 0;
        } else {
            $text_bcc_id = Text::replace(
                $text_bcc_id, $lang_id, $arrField['bcc'],
                MODULE_ID, self::TEXT_BCC
            );
        }
        if (empty($arrField['subject'])) {
            if ($text_subject_id) {
                Text::deleteById($text_subject_id, $lang_id);
            }
            $text_subject_id = 0;
        } else {
            $text_subject_id = Text::replace(
                $text_subject_id, $lang_id, $arrField['subject'],
                MODULE_ID, self::TEXT_SUBJECT
            );
        }
        if (empty($arrField['message'])) {
            if ($text_message_id) {
                Text::deleteById($text_message_id, $lang_id);
            }
            $text_message_id = 0;
        } else {
            $text_message_id = Text::replace(
                $text_message_id, $lang_id, $arrField['message'],
                MODULE_ID, self::TEXT_MESSAGE
            );
        }
        if (empty($arrField['message_html'])) {
            if ($text_message_html_id) {
                Text::deleteById($text_message_html_id, $lang_id);
            }
            $text_message_html_id = 0;
        } else {
            $text_message_html_id = Text::replace(
                $text_message_html_id, $lang_id, $arrField['message_html'],
                MODULE_ID, self::TEXT_MESSAGE_HTML
            );
        }
        if (empty($arrField['attachments'])) {
            if ($text_attachments_id) {
                Text::deleteById($text_attachments_id, $lang_id);
            }
            $text_attachments_id = 0;
        } else {
            // The attachment array is flattened to a PHP code string
            $text_attachments_id = Text::replace(
                $text_attachments_id, $lang_id,
                var_export($arrField['attachments'], true),
                MODULE_ID, self::TEXT_ATTACHMENTS
            );
        }
        if (empty($arrField['inline'])) {
            if ($text_inline_id) {
                Text::deleteById($text_inline_id, $lang_id);
            }
            $text_inline_id = 0;
        } else {
            // And so is the inline array
            $text_inline_id = Text::replace(
                $text_inline_id, $lang_id,
                var_export($arrField['inline'], true),
                MODULE_ID, self::TEXT_INLINE
            );
            $arrTemplate['html'] = true;
        }

        // If the key is present in the database, update the record.
        // Note that the key, module_id and protected fields are never changed!
        // For newly inserted templates, the protected flag is always 0 (zero).
        $query = ($update
          ? "UPDATE ".DBPREFIX."core_mail_template
                SET `text_name_id`=$text_name_id,
                    `text_from_id`=$text_from_id,
                    `text_sender_id`=$text_sender_id,
                    `text_reply_id`=$text_reply_id,
                    `text_to_id`=$text_to_id,
                    `text_cc_id`=$text_cc_id,
                    `text_bcc_id`=$text_bcc_id,
                    `text_subject_id`=$text_subject_id,
                    `text_message_id`=$text_message_id,
                    `text_message_html_id`=$text_message_html_id,
                    `text_attachments_id`=$text_attachments_id,
                    `text_inline_id`=$text_inline_id,
                    `html`=".(empty($arrField['html']) ? 0 : 1).",
                    `protected`=".(empty($arrField['protected']) ? 0 : 1)."
              WHERE `key`='".addslashes($key)."'
                AND `module_id`=".MODULE_ID
          : "INSERT INTO ".DBPREFIX."core_mail_template (
                `key`,
                `module_id`,
                `text_name_id`,
                `text_from_id`,
                `text_sender_id`,
                `text_reply_id`,
                `text_to_id`,
                `text_cc_id`,
                `text_bcc_id`,
                `text_subject_id`,
                `text_message_id`,
                `text_message_html_id`,
                `text_attachments_id`,
                `text_inline_id`,
                `html`,
                `protected`
            ) VALUES (
                '".addslashes($key)."', ".
                MODULE_ID.",
                $text_name_id,
                $text_from_id,
                $text_sender_id,
                $text_reply_id,
                $text_to_id,
                $text_cc_id,
                $text_bcc_id,
                $text_subject_id,
                $text_message_id,
                $text_message_html_id,
                $text_attachments_id,
                $text_inline_id,
                ".(empty($arrField['html']) ? 0 : 1).",
                ".(empty($arrField['protected']) ? 0 : 1)."
            )");
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        // Force reinit
        self::reset();
        return true;
    }


    /**
     * Show on overview of the mail templates for the current module
     *
     * Uses the MODULE_ID global constant.
     * @return  HTML_Template_Sigma     The template object
     */
    static function overview($limit=0)
    {
        global $_ARRAYLANG;

        // Anything to be stored?
        // If so, and if it fails, return to the edit view in order
        // to save the posted form
        if (self::storeTemplateFromPost() === false) return self::edit();
        self::deleteTemplate();

        $objTemplateLocal = new HTML_Template_Sigma(ASCMS_ADMIN_TEMPLATE_PATH);
        $objTemplateLocal->setErrorHandling(PEAR_ERROR_DIE);
        if (!$objTemplateLocal->loadTemplateFile('mailtemplate_overview.html', true, true))
            die("Failed to load template mailtemplate_overview.html");

        SettingDb::init();
        if (empty($limit))
            $limit = SettingDb::getValue('mailtemplate_per_page_backend');
        $uri = Html::getRelativeUri_entities();
        $active_tab = SettingDb::getTabIndex();
        Html::replaceUriParameter($uri, 'active_tab='.$active_tab);
//echo("Made uri for sorting: ".htmlentities($uri)."<br />");
        Html::stripUriParam($uri, 'key');
        Html::stripUriParam($uri, 'act');
        Html::stripUriParam($uri, 'delete_mailtemplate_key');
//echo("Made uri for sorting: ".htmlentities($uri)."<br />");
        $uri_edit = $uri_overview = $uri;
//echo("Made uri for sorting: ".htmlentities($uri)."<br />");
        Html::replaceUriParameter($uri_edit, 'act=mailtemplate_edit');
        Html::replaceUriParameter($uri_overview, 'act=mailtemplate_overview');
        $objSorting = new Sorting(
            $uri_overview,
            array(
                'name' => $_ARRAYLANG['TXT_CORE_MAILTEMPLATE_NAME'],
                'key'  => $_ARRAYLANG['TXT_CORE_MAILTEMPLATE_KEY'],
            ),
            true,
            'order_mailtemplate'
        );
        $count = 0;
        $arrTemplates = self::getTemplateArray(
            FRONTEND_LANG_ID,
            $objSorting->getOrder(),
            Paging::getPosition(),
            $limit,
            $count
        );
//echo("MailTemplate::overview(): got<br />".var_export($arrTemplates, true)."<hr />");
        //$cmd = (isset($_REQUEST['cmd']) ? $_REQUEST['cmd'] : '');

//echo("Made uri for paging: ".htmlentities($uri)."<br />");
        $objTemplateLocal->setGlobalVariable(
            $_ARRAYLANG
          + array(
            'TXT_CORE_MAILTEMPLATE_NAME' => $objSorting->getHeaderForField('name'),
            'TXT_CORE_MAILTEMPLATE_KEY' => $objSorting->getHeaderForField('key'),
            'TXT_CORE_MAILTEMPLATE_FUNCTIONS' => $_ARRAYLANG['TXT_CORE_HTML_FUNCTIONS'],
            'PAGING' => Paging::getPaging(
                $count, null, $uri_overview,
                $_ARRAYLANG['TXT_CORE_MAILTEMPLATE_PAGING'],
                true, $limit
            ),
            'URI_BASE' => $uri,
        ));

//echo("Made uri for template edit link: ".htmlentities($uri)."<br />");
        $i = 0;
        foreach ($arrTemplates as $arrTemplate) {
//echo("Protected: ".$arrTemplate['protected']." => ".(2*(1-$arrTemplate['protected']))."<br />");
            $objTemplateLocal->setVariable(array(
                'MAILTEMPLATE_ROWCLASS' => ++$i % 2 + 1,
                'MAILTEMPLATE_PROTECTED' =>
                    Html::getCheckmark($arrTemplate['protected']),
                'MAILTEMPLATE_HTML' =>
                    Html::getCheckmark($arrTemplate['html']),
                'MAILTEMPLATE_NAME' =>
                    '<a href="'.$uri_edit.
                    '&amp;key='.urlencode($arrTemplate['key']).'">'.
                      htmlentities($arrTemplate['name'], ENT_QUOTES, CONTREXX_CHARSET).
                    '</a>',
                'MAILTEMPLATE_KEY' => $arrTemplate['key'],
                'MAILTEMPLATE_FUNCTION' => Html::getBackendFunctions(
                    array(
                        'copy'   => $uri_edit.'&amp;copy=1&amp;key='.$arrTemplate['key'],
                        'edit'   => $uri_edit.'&amp;key='.$arrTemplate['key'],
                        'delete' => ($arrTemplate['protected']
                          ? ''
                          : $uri_overview.'&amp;delete_mailtemplate_key='.$arrTemplate['key']),
                    ),
                    array(
                        'delete' => $_ARRAYLANG['TXT_CORE_MAILTEMPLATE_DELETE_CONFIRM'],
                    )
                ),
            ));
            $objTemplateLocal->parse('core_mailtemplate_row');
        }

        $objTemplateLocal->setVariable(
            'TXT_CORE_MAILTEMPLATES', $_ARRAYLANG['TXT_CORE_MAILTEMPLATES']
        );
        self::showMessages();
        return $objTemplateLocal;
    }


    /**
     * Show the selected mail template for editing
     *
     * Uses the MODULE_ID global constant.
     * Stores the Mailtemplate if the 'bsubmit' parameter has been posted.
     * If the $key argument is empty, tries to pick the value from
     * $_REQUEST['key'].  If no key can be determined, calls
     * {@see Mailtemplate::overview()}.
     * @param   string    $key      The optional key of the mail template
     *                              to be edited
     * @return  boolean             True on success, false otherwise
     */
    static function edit($key='')
    {
        global $_ARRAYLANG;

        // Anything to be stored?
        //// If so, and if it succeeds, return to the overview
        //if (self::storeTemplateFromPost() === true) return self::overview();
        self::storeTemplateFromPost();

        // If the $key parameter is empty, check the request
        if (empty($key)) {
            if (isset($_REQUEST['key'])) $key = $_REQUEST['key'];
        }
        // Try to load an existing template anyway
        $arrTemplate = self::getTemplate($key, FRONTEND_LANG_ID);
        // If there is none, get an empty template
        if (!$arrTemplate) $arrTemplate = self::getNew($key);
        // Copy the template?
        if (isset($_REQUEST['copy'])) $arrTemplate['key'] = '';
//echo("MailTemplate::edit(): got<br />".var_export($arrTemplate, true)."<hr />");

        $objTemplate = new HTML_Template_Sigma(ASCMS_ADMIN_TEMPLATE_PATH);
        $objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        if (!$objTemplate->loadTemplateFile('mailtemplate_edit.html', true, true))
            die("Failed to load template mailtemplate_edit.html");

        $uri = Html::getRelativeUri_entities();
        Html::stripUriParam($uri, 'key');
        Html::stripUriParam($uri, 'act');
        $active_tab = SettingDb::getTabIndex();
        Html::replaceUriParameter($uri, 'active_tab='.$active_tab);
        $objTemplate->setGlobalVariable(
            $_ARRAYLANG
          + array(
            'CORE_MAILTEMPLATE_ACTIVE_TAB' => $active_tab,
            'URI_BASE' => $uri,
        ));

        $i = 0;
        foreach ($arrTemplate as $name => $value) {
            // See if there is a posted parameter value
            if (isset($_POST[$name])) $value = $_POST[$name];

            // IDs are set up as hidden fields.
            // They *MUST NOT* be edited!
            if (preg_match('/(?:_id)$/', $name)) {
                // For copies, IDs *MUST NOT* be reused!
                if (isset($_REQUEST['copy'])) $value = 0;
                $objTemplate->setVariable(
                    'MAILTEMPLATE_HIDDEN', Html::getHidden($name, $value)
                );
                $objTemplate->parse('core_mailtemplate_hidden');
                continue;
            }

            // Regular inputs of various kinds
            $input = '';
            $attribute = '';
//            $arrMimetype = '';
            switch ($name) {
              case 'available':
                continue 2;

              case 'message_html':
                // Show WYSIWYG only if HTML is enabled
                if (empty($arrTemplate['html']))
                    continue 2;
                $objTemplate->setVariable(array(
                    'MAILTEMPLATE_ROWCLASS' => (++$i % 2 + 1),
                    'MAILTEMPLATE_SPECIAL' => $_ARRAYLANG['TXT_CORE_MAILTEMPLATE_'.strtoupper($name)],
                ));
                $objTemplate->touchBlock('core_mailtemplate_special');
                $objTemplate->parse('core_mailtemplate_special');
                $objTemplate->setVariable(array(
                    'MAILTEMPLATE_ROWCLASS' => (++$i % 2 + 1),
                    'MAILTEMPLATE_SPECIAL' => get_wysiwyg_editor($name, $value),
                ));
                $objTemplate->touchBlock('core_mailtemplate_special');
                $objTemplate->parse('core_mailtemplate_special');
                continue 2;
                //$objTemplate->replaceBlock('core_mailtemplate_special', '', true);
              case 'message':
                $input =
                    Html::getTextarea($name, $value, '', 10,
                        'style="width: 600px;"');
                break;

              case 'protected':
                $attribute = HTML_ATTRIBUTE_DISABLED;
                $input = Html::getCheckbox($name, 1, '', $value, '', $attribute);
                break;
              case 'html':
                $input =
                    Html::getCheckbox($name, 1, '', $value, '', $attribute).
                    '&nbsp;'.
                    $_ARRAYLANG['TXT_CORE_MAILTEMPLATE_STORE_TO_SWITCH_EDITOR'];
                break;

              case 'inline':
              case 'attachments':
                continue 2;
// TODO: These do not work properly yet
/*
              // These are identical except for the MIME types
              case 'inline':
                $arrMimetype = Filetype::getImageMimetypes();
              case 'attachments':
                $arrAttachments = self::attachmentsToArray($arrTemplate[$name]);
                // Show at least one empty attachment/inline row
                if (empty($arrAttachments))
                    $arrAttachments = array(array('path' => '', ), );
                $i = 0;
                foreach ($arrAttachments as $arrAttachment) {
                    $div_id = $name.'-'.(++$i);
                    $element_name = $name.'['.$i.']';
                    $input .=
                        '<div id="'.$div_id.'">'.
                          Html::getHidden(
                              $element_name.'[old]', $arrAttachment['path'],
                              $name.'-hidden-'.$i).
                          $arrAttachment['path'].'&nbsp;'.
                          $_ARRAYLANG['TXT_CORE_MAILTEMPLATE_ATTACHMENT_UPLOAD'].
                          Html::getInputFileupload(
                              $element_name.'[new]', $name.'-file-'.$i,
                              Filetype::MAXIMUM_UPLOAD_FILE_SIZE,
                              $arrMimetype).
                          // Links for adding/removing inputs
                          Html::getRemoveAddLinks($div_id).
                        '</div>';
                }
//echo("$name => ".htmlentities($input)."<hr />");
                break;
*/

              // Once the key is defined, it cannot be changed.
              // To fix a wrong key, copy the old template and enter a new key,
              // then delete the old one.
              case 'key':
                $input = ($arrTemplate['key']
                    ? $value.Html::getHidden($name, $value)
                    : Html::getInputText($name, $value, '', 'style="width: 300px;"'));
//echo("Key /$key/ -> attr $attribute<br />");
                break;

              default:
                $input = Html::getInputText($name, $value, '', 'style="width: 300px;"');
            }
            $objTemplate->setVariable(array(
                'MAILTEMPLATE_ROWCLASS' => (++$i % 2 + 1),
                'MAILTEMPLATE_NAME' => $_ARRAYLANG['TXT_CORE_MAILTEMPLATE_'.strtoupper($name)],
                'MAILTEMPLATE_VALUE' => $input,
            ));
            $objTemplate->parse('core_mailtemplate_row');
        }
        self::showMessages();
        return $objTemplate;
    }


    static function storeTemplateFromPost()
    {
        global $_ARRAYLANG;

        if (   empty($_POST['key'])
//            || !isset($_POST['text_from_id'])
        ) return '';
        foreach ($_POST as &$value) {
            $value = contrexx_stripslashes($value);
        }
        if (self::storeTemplate($_POST)) {
            self::addMessage($_ARRAYLANG['TXT_CORE_MAILTEMPLATE_STORED_SUCCESSFULLY']);
            // Prevent this from being run twice
            unset($_POST['text_from_id']);
            return true;
        }
        self::addError($_ARRAYLANG['TXT_CORE_MAILTEMPLATE_STORING_FAILED']);
        // Prevent this from being run twice
        unset($_POST['text_from_id']);
        return false;
    }


    /**
     * Adds the string $strErrorMessage to the error messages.
     *
     * If necessary, inserts a line break tag (<br />) between
     * error messages.
     * @param   string  $strErrorMessage    The error message to add
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @todo    Use the message system of the calling class or the core system
     */
    function addError($strErrorMessage)
    {
        self::$strErrMessage .=
            (self::$strErrMessage != '' && $strErrorMessage != ''
                ? '<br />' : ''
            ).$strErrorMessage;
    }


    /**
     * Adds the string $strOkMessage to the success messages.
     *
     * If necessary, inserts a line break tag (<br />) between
     * messages.
     * @param   string  $strOkMessage       The message to add
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @todo    Use the message system of the calling class or the core system
     */
    function addMessage($strOkMessage)
    {
        self::$strOkMessage .=
            (self::$strOkMessage != '' && $strOkMessage != ''
                ? '<br />' : ''
            ).$strOkMessage;
    }


    /**
     * Add the status messages to the global template
     */
    static function showMessages()
    {
        global $objTemplate;

        if (   empty(self::$strOkMessage)
            && empty(self::$strErrMessage)) return;
        $objTemplate->setVariable(array(
            'CONTENT_OK_MESSAGE'     => self::$strOkMessage,
            'CONTENT_STATUS_MESSAGE' => self::$strErrMessage,
        ));
    }


    /**
     * Returns the status messages
     */
    static function getMessages()
    {
        return self::$strOkMessage;
    }


    /**
     * Returns the error messages
     */
    static function getErrors()
    {
        return self::$strErrMessage;
    }


    /**
     * Handles many problems caused by the database table
     * @return    boolean     False.  Always.
     */
    static function errorHandler()
    {
//die("MailTemplate::errorHandler(): Disabled!");

        require_once(ASCMS_CORE_PATH.'/DbTool.class.php');

        $table_name = DBPREFIX."core_mail_template";
        $table_structure = array(
            'key' => array('type' => 'TINYTEXT', 'default' => ''),
            'module_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'default' => null),
            'text_name_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'default' => null),
            'text_from_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'default' => null),
            'text_sender_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'default' => null),
            'text_reply_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'default' => null),
            'text_to_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'default' => null),
            'text_cc_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'default' => null),
            'text_bcc_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'default' => null),
            'text_subject_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'default' => null),
            'text_message_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'default' => null),
            'text_message_html_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'default' => null),
            'text_attachments_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'default' => null),
            'text_inline_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'default' => null),
            'html' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'default' => '1'),
            'protected' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'default' => '1'),
        );
        $table_index =  array(
            'id' => array(
                'fields' => array('key' => 32, 'module_id')
            ),
        );

        if (!DbTool::table_exists($table_name)) {
            // Make sure there are no bodies lying around
            Text::deleteByKey(self::TEXT_NAME);
            Text::deleteByKey(self::TEXT_FROM);
            Text::deleteByKey(self::TEXT_SENDER);
            Text::deleteByKey(self::TEXT_REPLY);
            Text::deleteByKey(self::TEXT_TO);
            Text::deleteByKey(self::TEXT_CC);
            Text::deleteByKey(self::TEXT_BCC);
            Text::deleteByKey(self::TEXT_SUBJECT);
            Text::deleteByKey(self::TEXT_MESSAGE);
            Text::deleteByKey(self::TEXT_MESSAGE_HTML);
            Text::deleteByKey(self::TEXT_ATTACHMENTS);
            Text::deleteByKey(self::TEXT_INLINE);
            // There is no previous version, so don't use DbTools::table()
            if (!DbTool::create_table($table_name, $table_structure, $table_index)) {
die("MailTemplate::errorHandler(): Error: failed to create table $table_name, code avs78idrjh");
            }
        }
        // The column for HTML messages went missing in some older pre-releases
        if (!DbTool::column_exists($table_name, 'text_message_html_id')) {
            if (!DbTool::check_columns($table_name, $table_structure, true)) {
die("MailTemplate::errorHandler(): Error: failed to add column 'text_message_html_id' to table $table_name, code aighkj44sd");
            }
        }

        // Import existing templates from the shop
        if (!@include_once(ASCMS_MODULE_PATH.'/shop/lib/Mail.class.php')) {
die("MailTemplate::errorHandler(): Error: failed to load previous Shop Mail class, code beiwjkbjh4s8s");

        }
        $arrKeys = array(
            1 => 'shop_mail_template_confirm',
            2 => 'shop_mail_template_complete',
            3 => 'shop_mail_template_login',
            4 => 'shop_mail_template_account',
        );
        foreach (array_keys(FWLanguage::getLanguageArray()) as $lang_id) {
            $arrTemplates = Mail::getTemplateArray($lang_id);
            if (empty($arrTemplates)) continue;
            foreach ($arrTemplates as $key => $arrTemplate) {
//echo("Found template:<br />".var_export($arrTemplate, true)."<hr />");
                $arrTemplate['key'] = (isset($arrKeys[$key])
                    ? $arrKeys[$key] : 'shop_mail_template_'.$key);
                $arrTemplate['protected'] = ($key < 5);
                // Replace any old format <PLACE_HOLDER> by [PLACE_HOLDER]
                foreach ($arrTemplate as $index => $value) {
                    $arrTemplate[$index] = preg_replace(
                        '/\<(\w+)\>/', '[$1]', $value);
                }
                MailTemplate::storeTemplate($arrTemplate);
//echo("Stored template:<br />".var_export(self::getTemplate($arrTemplate['key'], $lang_id), true)."<hr />");
            }
        }

        // More to come...

        // Always!
        return false;
    }

}

?>
