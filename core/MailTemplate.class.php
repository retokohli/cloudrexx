<?php

/**
 * Core Mail and Template Management
 * @version     2.2.0
 * @since       2.2.0
 * @package     contrexx
 * @subpackage  core
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @todo        Test!
 */
require_once ASCMS_CORE_PATH.'/Text.class.php';

/**
 * Core Mail and Template Class
 *
 * Manages e-mail templates in any language, accessible by module
 * and key for easy access.
 * Includes a nice wrapper for the phpmailer class that allows
 * sending all kinds of mail in plain text or HTML, also with
 * attachments.
 * @version     2.2.0
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
    const TEXT_NAME    = 'core_mail_template_name';
    /**
     * Class constant for the mail template from Text key
     */
    const TEXT_FROM    = 'core_mail_template_from';
    /**
     * Class constant for the mail template sender Text key
     */
    const TEXT_SENDER  = 'core_mail_template_sender';
    /**
     * Class constant for the mail template reply Text key
     */
    const TEXT_REPLY  = 'core_mail_template_reply';
    /**
     * Class constant for the mail template to Text key
     */
    const TEXT_TO  = 'core_mail_template_to';
    /**
     * Class constant for the mail template cc Text key
     */
    const TEXT_CC  = 'core_mail_template_cc';
    /**
     * Class constant for the mail template bcc Text key
     */
    const TEXT_BCC  = 'core_mail_template_bcc';
    /**
     * Class constant for the mail template subject Text key
     */
    const TEXT_SUBJECT = 'core_mail_template_subject';
    /**
     * Class constant for the mail template message Text key
     */
    const TEXT_MESSAGE = 'core_mail_template_message';
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
     * @param   integer     $lang_id        The optional language ID
     * @return  boolean                     True on success, false otherwise
     */
    static function init(
        $lang_id=0,
        $order='', $position=0, $limit=-1, &$count=0
    ) {
        global $objDatabase;

        // Use the current language if none is specified
        if (intval($lang_id) == 0) $lang_id = FRONTEND_LANG_ID;
        // Has the array been initialized with that language already?
        if (self::$lang_id === $lang_id) return true;

        self::reset();

        $arrSqlName = Text::getSqlSnippets(
            '`mail`.`text_name_id`', $lang_id, MODULE_ID, self::TEXT_NAME
        );
        $arrSqlFrom = Text::getSqlSnippets(
            '`mail`.`text_from_id`', $lang_id, MODULE_ID, self::TEXT_FROM
        );
        $arrSqlSender = Text::getSqlSnippets(
            '`mail`.`text_sender_id`', $lang_id, MODULE_ID, self::TEXT_SENDER
        );
        $arrSqlReply = Text::getSqlSnippets(
            '`mail`.`text_reply_id`', $lang_id, MODULE_ID, self::TEXT_REPLY
        );
        $arrSqlTo = Text::getSqlSnippets(
            '`mail`.`text_to_id`', $lang_id, MODULE_ID, self::TEXT_TO
        );
        $arrSqlCc = Text::getSqlSnippets(
            '`mail`.`text_cc_id`', $lang_id, MODULE_ID, self::TEXT_CC
        );
        $arrSqlBcc = Text::getSqlSnippets(
            '`mail`.`text_bcc_id`', $lang_id, MODULE_ID, self::TEXT_BCC
        );
        $arrSqlSubject = Text::getSqlSnippets(
            '`mail`.`text_subject_id`', $lang_id, MODULE_ID, self::TEXT_SUBJECT
        );
        $arrSqlMessage = Text::getSqlSnippets(
            '`mail`.`text_message_id`', $lang_id, MODULE_ID, self::TEXT_MESSAGE
        );
        $arrSqlAttachments = Text::getSqlSnippets(
            '`mail`.`text_attachments_id`', $lang_id, MODULE_ID, self::TEXT_ATTACHMENTS
        );
        $arrSqlInline = Text::getSqlSnippets(
            '`mail`.`text_inline_id`', $lang_id, MODULE_ID, self::TEXT_INLINE
        );

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
                   $arrSqlAttachments['join'].
                   $arrSqlInline['join']."
             WHERE `mail`.`module_id`=".MODULE_ID;
        $query_order = ($order ? " ORDER BY $order" : '');

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
                   $arrSqlAttachments['field'].
                   $arrSqlInline['field']."
              $query_from$query_order",
            $limit, $position);
        if (!$objResult) return self::errorHandler();
//DBG::log("MailTemplate::init($lang_id): Result<br />".var_export($objResult, true)."<hr />");
        self::$arrTemplates = array();
        while (!$objResult->EOF) {
            $key = $objResult->fields['key'];
            $available = true;
            $text_name_id = $objResult->fields[$arrSqlName['name']];
            $strName = $objResult->fields[$arrSqlName['text']];
            $text_from_id = $objResult->fields[$arrSqlFrom['name']];
            $strFrom = $objResult->fields[$arrSqlFrom['text']];
            $text_sender_id = $objResult->fields[$arrSqlSender['name']];
            $strSender = $objResult->fields[$arrSqlSender['text']];
            $text_reply_id = $objResult->fields[$arrSqlReply['name']];
            $strReply = $objResult->fields[$arrSqlReply['text']];
            $text_to_id = $objResult->fields[$arrSqlTo['name']];
            $strTo = $objResult->fields[$arrSqlTo['text']];
            $text_cc_id = $objResult->fields[$arrSqlCc['name']];
            $strCc = $objResult->fields[$arrSqlCc['text']];
            $text_bcc_id = $objResult->fields[$arrSqlBcc['name']];
            $strBcc = $objResult->fields[$arrSqlBcc['text']];
            $text_subject_id = $objResult->fields[$arrSqlSubject['name']];
            $strSubject = $objResult->fields[$arrSqlSubject['text']];
            $text_message_id = $objResult->fields[$arrSqlMessage['name']];
            $strMessage = $objResult->fields[$arrSqlMessage['text']];
            $text_attachments_id = $objResult->fields[$arrSqlAttachments['name']];
            $strAttachments = $objResult->fields[$arrSqlAttachments['text']];
            $text_inline_id = $objResult->fields[$arrSqlInline['name']];
            $strInline = $objResult->fields[$arrSqlInline['text']];
            if (   $strName === null
//                || $strFrom === null
//                || $strSender === null
//                || $strReply === null
//                || $strTo === null
//                || $strCc === null
//                || $strBcc === null
                || $strSubject === null
                || $strMessage === null
//                || $strAttachments === null
//                || $strInline === null
            ) $available = false;

            self::$arrTemplates[$key] = array(
                'key'                 => $key,
                'text_name_id'        => $text_name_id,
                'name'                => $strName,
                'text_from_id'        => $text_from_id,
                'from'                => $strFrom,
                'text_sender_id'      => $text_sender_id,
                'sender'              => $strSender,
                'text_reply_id'       => $text_reply_id,
                'reply'               => $strReply,
                'text_to_id'          => $text_to_id,
                'to'                  => $strTo,
                'text_cc_id'          => $text_cc_id,
                'cc'                  => $strCc,
                'text_bcc_id'         => $text_bcc_id,
                'bcc'                 => $strBcc,
                'text_subject_id'     => $text_subject_id,
                'subject'             => $strSubject,
                'text_message_id'     => $text_message_id,
                'message'             => $strMessage,
                'text_attachments_id' => $text_attachments_id,
                'attachments'         => $strAttachments,
                'text_inline_id'      => $text_inline_id,
                'inline'              => $strInline,
                'protected'           => $objResult->fields['protected'],
                'html'                => $objResult->fields['html'],
                'available'           => $available,
            );
            $objResult->MoveNext();
        }

        $objResult = $objDatabase->Execute("
            SELECT COUNT(*) AS `count` $query_from");
        if (!$objResult) return self::errorHandler();
        $count = $objResult->fields['count'];

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
/*        array(
            'key'                 => $key,
            'text_name_id'        => 0,
            'name'                => '',
            'text_from_id'        => 0,
            'from'                => '',
            'text_sender_id'      => 0,
            'sender'              => '',
            'text_reply_id'       => 0,
            'reply'               => '',
            'text_to_id'          => 0,
            'to'                  => '',
            'text_cc_id'          => 0,
            'cc'                  => '',
            'text_bcc_id'         => 0,
            'bcc'                 => '',
            'text_subject_id'     => 0,
            'subject'             => '',
            'text_message_id'     => 0,
            'message'             => '',
            'text_attachments_id' => 0,
            'attachments'         => '',
            'text_inline_id'      => 0,
            'inline'              => '',
            'protected'           => 0,
            'html'                => 0,
            'available'           => false,
        );
*/
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
     *  message       The message body
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
     * A simple {@see str_replace()} is used for the pattern
     * substitution, so you cannot use regular expressions.
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

//DBG::log("MailTemplate::send(".var_export($arrField, true)."): Entered<hr />");

        if (!@include_once ASCMS_LIBRARY_PATH.'/phpmailer/class.phpmailer.php')
            return false;
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
        $arrTemplate = array();
        if (!empty($arrField['key']))
            $arrTemplate = self::getTemplate($arrField['key'], $lang_id);
        if (empty($arrTemplate)) return false;
//DBG::log("MailTemplate::send(): Template<br />".var_export($arrTemplate, true)."<hr />");
        $search  =
            (isset($arrField['search']) && is_array($arrField['search'])
              ? $arrField['search']  : array());
        $replace =
            (isset($arrField['replace']) && is_array($arrField['replace'])
              ? $arrField['replace'] : array());

        foreach ($arrTemplate as $field => $value) {
            $arrTemplate[$field] =
                preg_replace('/\015\012/', "\012",
                    str_replace($search, $replace,
                        (empty($value)
                          ? (empty($arrTemplate[$field])
                              ? ''
                              : $arrTemplate[$field])
                          : $value)));
        }
//DBG::log("MailTemplate::send(): Fixed<br />".var_export($arrTemplate, true)."<hr />");

        try {
            $arrTemplate['attachments'] = @eval($arrTemplate['attachments']);
        } catch (Exception $e) { DBG::log($e->__toString()); }
        if (!is_array($arrTemplate['attachments']))
            $arrTemplate['attachments'] = array();

        try {
            $arrTemplate['inline'] = @eval($arrTemplate['inline']);
        } catch (Exception $e) { DBG::log($e->__toString()); }
        if (!is_array($arrTemplate['inline']))
            $arrTemplate['inline'] = array();
        if ($arrTemplate['inline']) $arrTemplate['html'] = true;
//DBG::log("MailTemplate::send(): Attachments and inlines<br />".var_export($arrTemplate, true)."<hr />");

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

        $objMail->CharSet = CONTREXX_CHARSET;
        $objMail->IsHTML($arrTemplate['html']);
        $objMail->FromName = $arrTemplate['sender'];
        $objMail->From = $arrTemplate['from'];
        $objMail->AddReplyTo($arrTemplate['reply']);
        $objMail->Subject = $arrTemplate['subject'];
        $objMail->Body = $arrTemplate['message'];
        foreach (preg_split('/\s*,\s*/', $arrTemplate['to'],  null, PREG_SPLIT_NO_EMPTY) as $address) {
            $objMail->AddAddress($address);
        }
        foreach (preg_split('/\s*,\s*/', $arrTemplate['cc'],  null, PREG_SPLIT_NO_EMPTY) as $address) {
            $objMail->AddCC($address);
        }
        foreach (preg_split('/\s*,\s*/', $arrTemplate['bcc'], null, PREG_SPLIT_NO_EMPTY) as $address) {
            $objMail->AddBCC($address);
        }
        foreach ($arrTemplate['attachments'] as $path => $name) {
            if (is_numeric($path)) $path = $name;
            $objMail->AddAttachment(ASCMS_DOCUMENT_ROOT.'/'.$path, $name);
        }
        foreach ($arrTemplate['inline'] as $path => $name) {
            if (is_numeric($path)) $path = $name;
            $objMail->AddEmbeddedImage(ASCMS_DOCUMENT_ROOT.'/'.$path, uniqid(), $name);
        }
//DBG::log("MailTemplate::send(): Mail<br />".var_export($objMail, true)."<br />Sending...<hr />");
        return $objMail->Send();
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
     * @todo    The regex here doesn't do a very good job.
     *          There's a better one used in some validator class, I think...?
     * @version 1.0
     * @param   string    $string   The string to be checked
     * @return  boolean             True if the argument looks like an e-mail
     *                              address, false otherwise
     */
    function isValidAddress($string)
    {
        return (boolean)preg_match(
            '/^[a-z0-9]+([-_\.a-z0-9]+)*'.  // user
            '@([a-z0-9]+([-\.a-z0-9]+)*)+'. // domain
            '\.[a-z]{2,6}$/',               // sld, tld
            $string
        );
    }


    /**
     * Delete the template with the given key
     *
     * Protected (system) templates can not be deleted.
     * Deletes all languages available.
     * @param   string    $key    The template key
     * @return  boolean           True on success, false otherwise
     */
    static function deleteTemplateByKey($key)
    {
        global $objDatabase;

        if (empty($key)) return false;

        $arrTemplate = self::getTemplate($key);
        // Cannot delete protected (system) templates
        if ($arrTemplate['protected']) return false;
        // Delete associated Text records
        if (!Text::deleteById($arrTemplate['text_name_id']))        return false;
        if (!Text::deleteById($arrTemplate['text_from_id']))        return false;
        if (!Text::deleteById($arrTemplate['text_sender_id']))      return false;
        if (!Text::deleteById($arrTemplate['text_reply_id']))       return false;
        if (!Text::deleteById($arrTemplate['text_to_id']))          return false;
        if (!Text::deleteById($arrTemplate['text_cc_id']))          return false;
        if (!Text::deleteById($arrTemplate['text_bcc_id']))         return false;
        if (!Text::deleteById($arrTemplate['text_subject_id']))     return false;
        if (!Text::deleteById($arrTemplate['text_message_id']))     return false;
        if (!Text::deleteById($arrTemplate['text_attachments_id'])) return false;
        if (!Text::deleteById($arrTemplate['text_inline_id']))      return false;

        if (!$objDatabase->Execute("
            DELETE FROM `".DBPREFIX."core_mail_template`
             WHERE `key`='".addslashes($key)."'")) return false;
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
     *  message       The message body
     *  html          If this evaluates to true, turns on HTML mode
     *  attachments   An array of file paths to attach.  The array keys may
     *                be used for the paths, and the values for the name.
     *                If the keys are numeric, the values are regarded as paths.
     * The key index is mandatory.  If available, the corresponding mail
     * template is loaded, and updated.
     * Missing fields are filled with default values, which are generally empty.
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

        if (empty($arrField['lang_id']))     $arrField['lang_id']     = FRONTEND_LANG_ID;
        if (empty($arrField['name']))        $arrField['name']        = '';
        if (empty($arrField['from']))        $arrField['from']        = '';
        if (empty($arrField['sender']))      $arrField['sender']      = '';
        if (empty($arrField['reply']))       $arrField['reply']       = '';
        if (empty($arrField['to']))          $arrField['to']          = '';
        if (empty($arrField['cc']))          $arrField['cc']          = '';
        if (empty($arrField['bcc']))         $arrField['bcc']         = '';
        if (empty($arrField['subject']))     $arrField['subject']     = '';
        if (empty($arrField['message']))     $arrField['message']     = '';
        if (empty($arrField['attachments'])) $arrField['attachments'] = '';
        if (empty($arrField['inline']))      $arrField['inline']      = '';
        if (empty($arrField['html']))        $arrField['html']        = '';

        $text_name_id        = null;
        $text_from_id        = null;
        $text_sender_id      = null;
        $text_reply_id       = null;
        $text_to_id          = null;
        $text_cc_id          = null;
        $text_bcc_id         = null;
        $text_subject_id     = null;
        $text_message_id     = null;
        $text_attachments_id = null;
        $text_inline_id      = null;

        $update = false;
        $arrTemplate = self::getTemplate($arrField['key'], $arrField['lang_id']);

//DBG::log("MailTemplate::storeTemplate(): Loaded Template<br />".var_export($arrTemplate, true)."<hr />");

        if ($arrTemplate && $arrTemplate['available']) {
            $update = true;
            $text_name_id        = $arrTemplate['text_name_id'];
            $text_from_id        = $arrTemplate['text_from_id'];
            $text_sender_id      = $arrTemplate['text_sender_id'];
            $text_reply_id       = $arrTemplate['text_reply_id'];
            $text_to_id          = $arrTemplate['text_to_id'];
            $text_cc_id          = $arrTemplate['text_cc_id'];
            $text_bcc_id         = $arrTemplate['text_bcc_id'];
            $text_subject_id     = $arrTemplate['text_subject_id'];
            $text_message_id     = $arrTemplate['text_message_id'];
            $text_attachments_id = $arrTemplate['text_attachments_id'];
            $text_inline_id      = $arrTemplate['text_inline_id'];
        }

        $objTextName = Text::replace(
            $text_name_id, $arrField['lang_id'], $arrField['name'],
            MODULE_ID, self::TEXT_NAME
        );
        $objTextFrom = Text::replace(
            $text_from_id, $arrField['lang_id'], $arrField['from'],
            MODULE_ID, self::TEXT_FROM
        );
        $objTextSender = Text::replace(
            $text_sender_id, $arrField['lang_id'], $arrField['sender'],
            MODULE_ID, self::TEXT_SENDER
        );
        $objTextReply = Text::replace(
            $text_reply_id, $arrField['lang_id'], $arrField['reply'],
            MODULE_ID, self::TEXT_REPLY
        );
        $objTextTo = Text::replace(
            $text_to_id, $arrField['lang_id'], $arrField['to'],
            MODULE_ID, self::TEXT_TO
        );
        $objTextCc = Text::replace(
            $text_cc_id, $arrField['lang_id'], $arrField['cc'],
            MODULE_ID, self::TEXT_CC
        );
        $objTextBcc = Text::replace(
            $text_bcc_id, $arrField['lang_id'], $arrField['bcc'],
            MODULE_ID, self::TEXT_BCC
        );
        $objTextSubject = Text::replace(
            $text_subject_id, $arrField['lang_id'], $arrField['subject'],
            MODULE_ID, self::TEXT_SUBJECT
        );
        $objTextMessage = Text::replace(
            $text_message_id, $arrField['lang_id'], $arrField['message'],
            MODULE_ID, self::TEXT_MESSAGE
        );
        // The attachment array is flattened to a PHP code string
        $objTextAttachments = Text::replace(
            $text_attachments_id, $arrField['lang_id'],
            var_export($arrField['attachments'], true),
            MODULE_ID, self::TEXT_ATTACHMENTS
        );
        // And so is the inline array
        $objTextInline = Text::replace(
            $text_inline_id, $arrField['lang_id'],
            var_export($arrField['inline'], true),
            MODULE_ID, self::TEXT_INLINE
        );

        // If the key is present in the database, update the record.
        // Note that the key, module_id and protected fields are never changed!
        // For newly inserted templates, the protected flag is always 0 (zero).
        $query = ($update
          ? "UPDATE ".DBPREFIX."core_mail_template
                SET `text_name_id`=".$objTextName->getId().",
                    `text_from_id`=".$objTextFrom->getId().",
                    `text_sender_id`=".$objTextSender->getId().",
                    `text_reply_id`=".$objTextReply->getId().",
                    `text_to_id`=".$objTextTo->getId().",
                    `text_cc_id`=".$objTextCc->getId().",
                    `text_bcc_id`=".$objTextBcc->getId().",
                    `text_subject_id`=".$objTextSubject->getId().",
                    `text_message_id`=".$objTextMessage->getId().",
                    `text_attachments_id`=".$objTextAttachments->getId().",
                    `text_inline_id`=".$objTextInline->getId()."
              WHERE `key`='".addslashes($arrField['key'])."'
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
                `text_attachments_id`,
                `text_inline_id`
            ) VALUES (
                '".addslashes($arrField['key'])."', ".
                MODULE_ID.", ".
                $objTextName->getId().", ".
                $objTextFrom->getId().", ".
                $objTextSender->getId().", ".
                $objTextReply->getId().", ".
                $objTextTo->getId().", ".
                $objTextCc->getId().", ".
                $objTextBcc->getId().", ".
                $objTextSubject->getId().", ".
                $objTextMessage->getId().", ".
                $objTextAttachments->getId().", ".
                $objTextInline->getId()."
            )");
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        // Force reinit
        self::reset();
        return true;
    }


    /**
     * TODO
     * Show on overview of the mail templates for the current module
     *
     * Uses the MODULE_ID global constant.
     * @return  HTML_Template_Sigma     The template object
     */
    static function overview($limit=0)
    {
        global $_ARRAYLANG;

        $objTemplate = new HTML_Template_Sigma(ASCMS_ADMIN_TEMPLATE_PATH);
        CSRF::add_placeholder($objTemplate);
        $objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        if (!$objTemplate->loadTemplateFile('mailtemplate_overview.html', true, true))
            die("Failed to load template settingDb.html");

        if (empty($limit))
            $limit = SettingDb::getValue('mailtemplate_per_page_backend');
        $uri = CONTREXX_DIRECTORY_INDEX.'?'.$_SERVER['QUERY_STRING'];
//echo("Made uri for sorting: ".htmlentities($uri)."<br />");
        $objSorting = new Sorting(
            $uri,
            array(
                'text_1`.`text',
            ),
            array(
                $_ARRAYLANG['TXT_CORE_MAILTEMPLATE_NAME'],
            ),
            true,
            'order_mailtemplate'
        );
        SettingDb::init();
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

        // Strip the old action from the URI
        $uri = preg_replace('/act\=[^&]*\&?/', '', $uri);
//echo("Made uri for template edit link: ".htmlentities($uri)."<br />");
        foreach ($arrTemplates as $arrTemplate) {
            $objTemplate->setVariable(array(
                'MAILTEMPLATE_NAME' =>
                    '<a href="'.
                      $uri.
                      '&amp;'.$objSorting->getOrderUriEncoded().
                      '&amp;pos='.Paging::getPosition().
                      '&act=mailtemplate_edit'.
                      '">'.
                    $arrTemplate['name'].
                    '</a>',
            ));
            $objTemplate->parse('core_mailtemplate_row');
        }

        // Add action parameter for editing the template
        $uri .= '&act=mailtemplate_overview';
//echo("Made uri for paging: ".htmlentities($uri)."<br />");
        $objTemplate->setGlobalVariable(array(
            'TXT_MAILTEMPLATE_NAME' => $objSorting->getHeaderForField('text_1`.`text'),
            'TXT_MAILTEMPLATE_NEW' => $_ARRAYLANG['TXT_CORE_MAILTEMPLATE_NEW'],
            'PAGING' => Paging::getPaging(
                $count,
                Paging::getPosition(),
                $uri,
                $_ARRAYLANG['TXT_CORE_MAILTEMPLATE_PAGING'],
                true,
                $limit
            ),
        ));
        return $objTemplate;
    }


    /**
     * TODO
     * Show the selected mail template for editing
     *
     * Uses the MODULE_ID global constant.
     * @param   string    $key      The key of the mail template to be edited
     * @return  boolean             True on success, false otherwise
     */
    static function edit($key)
    {
        return (bool)$key;
    }


    /**
     * Handles many problems caused by the database table
     * @return    boolean     False.  Always.
     */
    static function errorHandler()
    {
        global $objDatabase;

        $arrTables = $objDatabase->MetaTables('TABLES');
        if (!in_array(DBPREFIX."core_mail_template", $arrTables)) {
            $query = "
                CREATE TABLE ".DBPREFIX."core_mail_template (
                  `key` TINYTEXT NOT NULL DEFAULT '' COMMENT '',
                  `module_id` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT '',
                  `text_name_id` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT '',
                  `text_from_id` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT '',
                  `text_sender_id` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT '',
                  `text_reply_id` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT '',
                  `text_to_id` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT '',
                  `text_cc_id` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT '',
                  `text_bcc_id` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT '',
                  `text_subject_id` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT '',
                  `text_message_id` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT '',
                  `text_attachments_id` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT '',
                  `text_inline_id` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT '',
                  `html` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '',
                  `protected` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '',
                  PRIMARY KEY (`key`(32), `module_id`)
                ) ENGINE=MYISAM";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return false;
//DBG::log("MailTemplate::errorHandler(): Created table ".DBPREFIX."core_mail_template<br />");

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
            Text::deleteByKey(self::TEXT_ATTACHMENTS);
            Text::deleteByKey(self::TEXT_INLINE);
        }

        // More to come...

        // Always!
        return false;
    }

}

?>
