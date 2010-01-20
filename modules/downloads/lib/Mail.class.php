<?php

/**
 * Downloads Settings Mail Object
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  lib_framework
 */

/**
 * Downloads Settings Mail Object
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  lib_framework
 */
class Downloads_Setting_Mail
{
    private $type;
    private $lang_id;
    private $sender_mail;
    private $sender_name;
    private $subject;
    private $format;
    private $body_text;
    private $body_html;

    /**
     * @access public
     */
    public $EOF;
    public $languageEOF;

    private $arrLoadedMails = array();


    /**
     * Contains the message if an error occurs
     *
     * @var string
     * @access private
     */
    private $error_msg;

    private static $arrAvailableFormats = array(
        'text'        => 'TXT_DOWNLOADS_ONLY_TEXT',
        'html'        => 'TXT_DOWNLOADS_HTML_UC',
        'multipart'   => 'TXT_DOWNLOADS_MULTIPART'
    );

    /**
     * @access private
     */
    private $arrAttributes = array(
        'type'          => 'string',
        'lang_id'       => 'int',
        'sender_mail'   => 'string',
        'sender_name'   => 'string',
        'subject'       => 'string',
        'format'        => 'string',
        'body_text'     => 'string',
        'body_html'     => 'string'
    );

    private $arrAvailableTypes = array(
        'new_entry' => array(
            'title'    => 'TXT_DOWNLOADS_NEW_ENTRY',
            'placeholders'    => array(
                'TXT_DOWNLOADS_GENERAL'   => array(
                    '[[ID]]'            => 'TXT_DOWNLOADS_ID_DESC',
                    '[[NAME]]'                => 'TXT_DOWNLOADS_NAME_DESC',
                    '[[DESCRIPTION]]'    => 'TXT_DOWNLOADS_DESCRIPTION_DESC',
                    '[[CATEGORY]]'      => 'TXT_DOWNLOADS_CATEGORY_DESC',
                    '[[IMAGE]]'            => 'TXT_DOWNLOADS_IMAGE_DESC',
                    '[[THUMBNAIL]]'            => 'TXT_DOWNLOADS_THUMBNAIL_DESC',
                ),
                'TXT_DOWNLOADS_PUBLISHER'   => array(
                    '[[PUBLISHER_GENDER]]'            => 'TXT_DOWNLOADS_GENDER_DESC',
                    '[[PUBLISHER_USERNAME]]'            => 'TXT_DOWNLOADS_USERNAME_DESC',
                    '[[PUBLISHER_FIRSTNAME]]'         => 'TXT_DOWNLOADS_FIRSTNAME_DESC',
                    '[[PUBLISHER_LASTNAME]]'          => 'TXT_DOWNLOADS_LASTNAME_DESC'
                ),
                'TXT_DOWNLOADS_RECIPIENT'   => array(
                    '[[RECIPIENT_TITLE]]'            => 'TXT_DOWNLOADS_TITLE_DESC',
                    '[[RECIPIENT_GENDER]]'            => 'TXT_DOWNLOADS_GENDER_DESC',
                    '[[RECIPIENT_USERNAME]]'            => 'TXT_DOWNLOADS_USERNAME_DESC',
                    '[[RECIPIENT_FIRSTNAME]]'         => 'TXT_DOWNLOADS_FIRSTNAME_DESC',
                    '[[RECIPIENT_LASTNAME]]'          => 'TXT_DOWNLOADS_LASTNAME_DESC'
                )
            ),
            'required'    => array(
            )
        )
    );


    public function __construct()
    {
        $this->clean();
    }

    /**
     * Clean data
     *
     * Reset all data for a new mail template.
     *
     */
    private function clean()
    {
        $this->type = null;
        $this->lang_id = null;
        $this->sender_mail = '';
        $this->sender_name = '';
        $this->subject = '';
        $this->format = null;
        $this->body_text = '';
        $this->body_html = '';
        $this->EOF = true;
    }


    /**
     * Load e-mail template
     *
     * Get attributes of an e-mail template from the database
     * and put them into the analogous class variables.
     *
     * @param string $type
     * @param integer $langId
     * @return unknown
     */
    public function load($type, $langId = 0)
    {
        if ($type) {
            if (!isset($this->arrLoadedMails[$type][$langId])) {
                return $this->loadMails($type, $langId);
            }
            foreach ($this->arrLoadedMails[$type][$langId] as $attribute => $value) {
                $this->{$attribute} = $value;
            }
            return true;
        }
        $this->clean();
        return true;
    }


    public function loadMails($type = null, $langId = null)
    {
        global $objDatabase;

        $this->arrLoadedMails = array();

        $query = '
            SELECT `'.implode('`,`', array_keys($this->arrAttributes)).'`
            FROM `'.DBPREFIX.'module_downloads_mail`'
            .(isset($type) || isset($langId) ? ' WHERE'.(isset($type) ? " `type` = '".$type."'".(isset($langId) ? " AND `lang_id` = ".$langId : '') : " `lang_id` = ".$langId)    : '')
            .' ORDER BY `type`, `lang_id`';
        $objMail = $objDatabase->Execute($query);

        if ($objMail && !$objMail->EOF) {
            while (!$objMail->EOF) {
                foreach ($objMail->fields as $attribute => $value) {
                    $this->arrLoadedMails[$objMail->fields['type']][$objMail->fields['lang_id']][$attribute] = $value;
                }
                $objMail->MoveNext();
            }
            $this->first();
            return true;
        }
        $this->clean();
        return false;
    }


    public function store()
    {
        global $objDatabase, $_ARRAYLANG;

        if (!$this->validateType() || !$this->validateSenderMail() || !$this->validateSenderName() || !$this->validateFormat() || !$this->validateBody()) {
            return false;
        }

        if (isset($this->arrLoadedMails[$this->type][$this->lang_id])) {
            if ($objDatabase->Execute("
                UPDATE `".DBPREFIX."module_downloads_mail`
                SET
                    `sender_mail` = '".addslashes($this->sender_mail)."',
                    `sender_name` = '".addslashes($this->sender_name)."',
                    `subject` = '".addslashes($this->subject)."',
                    `format` = '".$this->format."',
                    `body_text` = '".addslashes($this->body_text)."',
                    `body_html` = '".addslashes($this->body_html)."'
                WHERE
                    `type` = '".$this->type."'
                AND
                    `lang_id` = ".$this->lang_id
            ) === false) {
                $this->error_msg[] = $_ARRAYLANG['TXT_DOWNLOADS_MAIL_UPDATED_FAILED'];
                return false;
            }
        } else {
            if ($objDatabase->Execute("
                INSERT INTO `".DBPREFIX."module_downloads_mail` (
                    `type`,
                    `lang_id`,
                    `sender_mail`,
                    `sender_name`,
                    `subject`,
                    `format`,
                    `body_text`,
                    `body_html`
                ) VALUES (
                    '".$this->type."',
                    ".$this->lang_id.",
                    '".addslashes($this->sender_mail)."',
                    '".addslashes($this->sender_name)."',
                    '".addslashes($this->subject)."',
                    '".$this->format."',
                    '".addslashes($this->body_text)."',
                    '".addslashes($this->body_html)."'
                )"
            ) === false) {
                $this->error_msg[] = $_ARRAYLANG['TXT_DOWNLOADS_MAIL_ADDED_FAILED'];
                return false;
            }
        }

        return true;
    }

    public function delete()
    {
        global $_ARRAYLANG, $objDatabase;

        if ($this->isRemovable() && $objDatabase->Execute("DELETE FROM `".DBPREFIX."module_downloads_mail` WHERE `type` = '".$this->type."' AND `lang_id` = ".$this->lang_id) !== false) {
            $this->loadMails();
            return true;
        } else {
            $this->error_msg[] = $_ARRAYLANG['TXT_DOWNLOADS_EMAIL_DEL_FAILED'];
            return false;
        }
    }

    public function isRemovable()
    {
        return (bool)$this->lang_id;
    }

    /**
     * Load first mail
     *
     */
    public function first()
    {
        if (reset($this->arrLoadedMails) === false || !$this->firstLanguage()) {
            $this->EOF = true;
        } else {
            $this->EOF = false;
        }
    }

    /**
     * Load next mail
     *
     */
    public function next()
    {
        if (next($this->arrLoadedMails) === false || !$this->firstLanguage()) {
            $this->EOF = true;
        }
    }

    public function firstLanguage()
    {
        if (reset($this->arrLoadedMails[key($this->arrLoadedMails)]) === false ||
        !$this->load(key($this->arrLoadedMails), key($this->arrLoadedMails[key($this->arrLoadedMails)]))) {
            return !$this->languageEOF = true;
        } else {
            return !$this->languageEOF = false;
        }
    }

    public function nextLanguage()
    {
        if (next($this->arrLoadedMails[$this->type]) === false || !$this->load($this->type, key($this->arrLoadedMails[$this->type]))) {
            $this->languageEOF = true;
        }
    }

    private function validateType()
    {
        global $_ARRAYLANG;

        if (isset($this->arrAvailableTypes[$this->type])) {
            return true;
        } else {
            $this->error_msg[] = $_ARRAYLANG['TXT_DOWNLOADS_UNKNOWN_TYPE_SPECIFIED'];
            return false;
        }
    }

    private function validateSenderMail()
    {
        global $_ARRAYLANG;

        $objValidator = new FWValidator();

        if ($objValidator->isEmail($this->sender_mail)) {
            return true;
        } else {
            $this->error_msg[] = $_ARRAYLANG['TXT_DOWNLOADS_INVALID_SENDER_ADDRESS'];
            return false;
        }
    }

    private function validateSenderName()
    {
        global $_ARRAYLANG;

        if (empty($this->sender_name)) {
            $this->error_msg[] = $_ARRAYLANG['TXT_DOWNLOADS_SET_SENDER_NAME'];
            return false;
        } else {
            return true;
        }
    }

    private function validateFormat()
    {
        global $_ARRAYLANG;

        if (in_array($this->format, array_keys(self::$arrAvailableFormats))) {
            return true;
        } else {
            $this->error_msg[] = $_ARRAYLANG['TXT_DOWNLOADS_UNKOWN_FORMAT_SPECIFIED'];
            return false;
        }
    }

    private function validateBody()
    {
        $status = true;

        $arrFormat =
            ($this->format == 'multipart'
                ? array('text', 'html') : array($this->format)
            );
        foreach ($arrFormat as $format) {
            if (!$this->isValidBody($format)) {
                $status = false;
            }
        }

        return $status;
    }



    private function isValidBody($format)
    {
        global $_ARRAYLANG;

        $arrPlaceholders = array();
        $formatUC = strtoupper($format);
        if (preg_match_all('/\[\[[0-9A-Za-z_-]+\]\]/', $this->{'body_'.$format}, $arrPlaceholders)) {
            $arrMissedPlaceholders = array_diff($this->arrAvailableTypes[$this->type]['required'], $arrPlaceholders[0]);
            if (count($arrMissedPlaceholders) > 1) {
                $this->error_msg[] = sprintf($_ARRAYLANG['TXT_DOWNLOADS_REQUIRED_PLACEHOLDERS_IN_'.$formatUC], implode(', ', $arrMissedPlaceholders));
                return false;
            } elseif (count($arrMissedPlaceholders) == 1) {
                $this->error_msg[] = sprintf($_ARRAYLANG['TXT_DOWNLOADS_REQUIRED_PLACEHOLDER_IN_'.$formatUC], current($arrMissedPlaceholders));
                return false;
            } else  {
                return true;
            }
        } elseif (count($this->arrAvailableTypes[$this->type]['required']) > 1) {
            $this->error_msg[] = sprintf($_ARRAYLANG['TXT_DOWNLOADS_REQUIRED_PLACEHOLDERS_IN_'.$formatUC], implode(', ', $this->arrAvailableTypes[$this->type]['required']));
            return false;
        } elseif (count($this->arrAvailableTypes[$this->type]['required']) == 1) {
            $this->error_msg[] = sprintf($_ARRAYLANG['TXT_DOWNLOADS_REQUIRED_PLACEHOLDER_IN_'.$formatUC], current($this->arrAvailableTypes[$this->type]['required']));
            return false;
        } else {
            return true;
        }
    }




    public function setType($type)
    {
        $this->type = $type;
    }

    public function setLangId($langId)
    {
        $this->lang_id = $langId;
    }

    public function setSenderMail($senderMail)
    {
        $this->sender_mail = $senderMail;
    }

    public function setSenderName($senderName)
    {
        $this->sender_name = $senderName;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function setFormat($format)
    {
        $this->format = $format;
    }

    public function setBodyText($bodyText)
    {
        $this->body_text = $bodyText;
    }

    public function setBodyHtml($bodyHtml)
    {
        $this->body_html = $bodyHtml;
    }

    public function getErrorMsg()
    {
        return $this->error_msg;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getTypeDescription()
    {
        global $_ARRAYLANG;

        return $_ARRAYLANG[$this->arrAvailableTypes[$this->type]['title']];
    }

    public function getLangId()
    {
        return $this->lang_id;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function getFormat()
    {
        return $this->format;
    }

    public function getSenderMail()
    {
        return $this->sender_mail;
    }

    public function getSenderName()
    {
        return $this->sender_name;
    }

    public function getBodyText()
    {
        return $this->body_text;
    }

    public function getBodyHtml()
    {
        return $this->body_html;
    }

    public function getPlaceholders()
    {
        return $this->arrAvailableTypes[$this->type]['placeholders'];
    }

    public function getFormats()
    {
        return array_map(create_function('$langVar', 'global $_ARRAYLANG;return $_ARRAYLANG[$langVar];'), self::$arrAvailableFormats);
    }

    public static function getMailLanguageMenu($type, $lang, $attrs)
    {
        global $objDatabase;

        $arrUsedLangIds = array();
        $objResultSet = $objDatabase->Execute("SELECT `lang_id` FROM `".DBPREFIX."module_downloads_mail` WHERE `type` = '".$type."' AND `lang_id` != 0");
        if ($objResultSet !== false) {
            while (!$objResultSet->EOF) {
                array_push($arrUsedLangIds, $objResultSet->fields['lang_id']);
                $objResultSet->MoveNext();
            }
            $menu = '<select'.(!empty($attrs) ? ' '.$attrs : '').">\n";
            $arrLanguages = FWLanguage::getLanguageArray();
            foreach ($arrLanguages as $langId => $arrLanguage) {
                if (!in_array($langId, $arrUsedLangIds)) {
                    $menu .=
                        '<option value="'.$langId.'"'.
                        ($langId == $lang ? ' selected="selected"' : '').
                        '>'.
                        htmlentities(
                            $arrLanguage['name'], ENT_QUOTES, CONTREXX_CHARSET
                        )."</option>\n";
                }
            }
            $menu .= "</select>\n";
            return $menu;
        }
        return false;
    }
}

?>
