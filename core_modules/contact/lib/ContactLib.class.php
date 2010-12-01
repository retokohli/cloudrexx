<?php
/**
 * Contact library
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  core_module_contact
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Contact library
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @access      public
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  core_module_contact
 */
class ContactLib
{
    protected $_arrRecipients = array();
    protected $_lastRecipientId;
    var $arrForms;
    var $_arrSettings;

    /**
     * Regexpression list
     */
    var $arrCheckTypes;

    /**
     * return the last recipient id
     *
     * @return integer
     */
    public function getLastRecipientId($refresh = false)
    {
        global $objDatabase;
        if (empty($this->_lastRecipientId) || $refresh) {
            $this->_lastRecipientId = intval($objDatabase->SelectLimit('SELECT MAX(`id`) as `max` FROM `'.DBPREFIX.'module_contact_recipient`', 1)->fields['max']);
        }
        return $this->_lastRecipientId;
    }

    /**
     * return the highest sort value of a recipient list
     *
     * @return integer
     */
    public function getHighestSortValue($formId)
    {
        global $objDatabase;
        return intval($objDatabase->SelectLimit('SELECT MAX(`sort`) as `max` FROM `'.DBPREFIX.'module_contact_recipient` WHERE `id_form` = '.$formId, 1)->fields['max']);
    }

    /**
     * Read the contact forms 
     */
    function initContactForms($allLanguages = false)
    {
        global $objDatabase, $_FRONTEND_LANGID;
        
        if ($allLanguages) {
            $sqlWhere = '';
        } else {
            $sqlWhere = "WHERE tblData.id_lang=".$_FRONTEND_LANGID;
        }

        $this->arrForms = array();

        $objResult = $objDatabase->Execute("
            SELECT
                tblForm.id,
                tblForm.mails,
                tblForm.showForm,
                tblForm.`use_captcha`,
                tblForm.`use_custom_style`,
                tblForm.`send_copy`,
                tblForm.`html_mail`,
                # subquery sure aren't optimal here, but it doesn't work
                # with the join...
                (
                    SELECT
                        COUNT(id)
                    FROM
                        `".DBPREFIX."module_contact_form_data`
                    WHERE
                        `tblForm`.`id` = `id_form`
                )                                       AS number,
                (
                    SELECT
                        MAX(time)
                    FROM
                        `".DBPREFIX."module_contact_form_data`
                    WHERE
                        `tblForm`.`id` = `id_form`
                )                                       AS last,
                `tblLang`.`is_active`                   AS `is_active`,
                `tblLang`.`name`                        AS `name`,
                `tblLang`.`langID`                      AS `langID`,
                `tblLang`.`text`                        AS `text`,
                `tblLang`.`feedback`                    AS `feedback`,
                `tblLang`.`mailTemplate`                AS `mailTemplate`,
                `tblLang`.`subject`                     AS `subject`

            FROM 
                ".DBPREFIX."module_contact_form         AS tblForm

            LEFT JOIN
                `".DBPREFIX."module_contact_form_lang`  AS `tblLang`
            ON
                `tblForm`.`id` = `tblLang`.`formID`

            LEFT JOIN 
                `".DBPREFIX."module_contact_form_data`  AS `tblData`
            ON 
                `tblForm`.`id` = `tblData`.`id_form`

            ".$sqlWhere."

            ORDER BY 
                last
            DESC
        ");

        $lastID = 0;

        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $fields = &$objResult->fields; // shorten the access
                if ($fields['id'] != $lastID) {
                    // create a new array
                    $lastID = $fields['id'];

                    $this->arrForms[$fields['id']] = array(
                        // actually the following variables are different for
                        // every language. I let them stay as sort of default values
                        'name'              => $fields['name'], 
                        'subject'           => $fields['subject'],
                        'text'              => contrexx_stripslashes($fields['text']),
                        'feedback'          => contrexx_stripslashes($fields['feedback']),
                        'mailTemplate'      => contrexx_stripslashes($fields['mailTemplate']),

                        'emails'            => $fields['mails'],
                        'number'            => intval($fields['number']),
                        'last'              => intval($fields['last']),
                        'showForm'          => $fields['showForm'],
                        'useCaptcha'        => $fields['use_captcha'],
                        'useCustomStyle'    => $fields['use_custom_style'],
                        'sendCopy'          => $fields['send_copy'],
                        'htmlMail'          => $fields['html_mail'],
                        'recipients'        => $this->getRecipients($lastID, true),
                        'lang'              => array(
                            $fields['langID'] => array(
                                'is_active'   => $fields['is_active'],
                                'name'        => $fields['name'],
                                'text'        => stripslashes($fields['text']),
                                'feedback'    => stripslashes($fields['feedback']),
                                'mailTemplate'=> stripslashes($fields['mailTemplate']),
                                'subject'     => $fields['subject']
                            )
                        )
                    );
                } else {
                    // only append the lang variables to the array
                    $this->arrForms[$fields['id']]['lang'][$fields['langID']] = array(
                        'is_active'   => $fields['is_active'],
                        'name'        => $fields['name'],
                        'text'        => stripslashes($fields['text']),
                        'feedback'    => stripslashes($fields['feedback']),
                        'mailTemplate'=> stripslashes($fields['mailTemplate']),
                        'subject'     => $fields['subject']
                    );
                }

                $objResult->MoveNext();
            }
        }
    }

    function initCheckTypes()
    {
        global $objDatabase;

        $this->arrCheckTypes = array(
            1   => array(
                'regex' => '.*',
                'name'  => 'TXT_CONTACT_REGEX_EVERYTHING'
            ),
            2   => array(
                'regex' => '^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*\.(([0-9]{1,3})|([a-zA-Z]{2,3})|(aero|coop|info|museum|name))$',
                'name'  => 'TXT_CONTACT_REGEX_EMAIL'
            ),
            3   => array(
                'regex' => '^(ht|f)tp[s]?\:\/\/[A-Za-z0-9\-\:\.\?\&\=\/\#\%]*$',
                'name'  => 'TXT_CONTACT_REGEX_URL'
            ),
            4   => array(
                'regex' => '^[A-Za-z'.(strtolower(CONTREXX_CHARSET) == 'utf-8' ? utf8_encode('äàáüâûôñèöéè') : 'äàáüâûôñèöéè').'\ ]*$',
                'name'  => 'TXT_CONTACT_REGEX_TEXT'
            ),
            5   => array(
                'regex' => '^[0-9]*$',
                'name'  => 'TXT_CONTACT_REGEX_NUMBERS'
            )
        );
    }

    function initSettings()
    {
        global $objDatabase;

        $this->_arrSettings = array();
        $objSettings = $objDatabase->Execute("SELECT setname, setvalue FROM ".DBPREFIX."module_contact_settings");

        if ($objSettings !== false) {
            while (!$objSettings->EOF) {
                $this->_arrSettings[$objSettings->fields['setname']] = $objSettings->fields['setvalue'];
                $objSettings->MoveNext();
            }
        }
    }

    function getSettings($reinitialize = false)
    {
        if (!isset($this->_arrSettings) || $reinitialize) {
            $this->initSettings();
        }
        return $this->_arrSettings;
    }

    function getContactFormDetails($id, &$arrEmails, &$subject, &$feedback, &$mailTemplate, &$showForm, &$useCaptcha, &$sendCopy, &$htmlMail)
    {
        global $objDatabase, $_CONFIG, $_ARRAYLANG, $_LANGID;

        $objContactForm = $objDatabase->SelectLimit("SELECT f.mails, l.subject, l.feedback, l.mailTemplate, f.showForm,
                                                            f.use_captcha, f.send_copy, f.html_mail
                                                     FROM ".DBPREFIX."module_contact_form AS f
                                                     LEFT JOIN ".DBPREFIX."module_contact_form_lang AS l
                                                     ON ( f.id = l.formID )
                                                     WHERE f.id = ".$id."
                                                     AND l.langID = ".$_LANGID
                          , 1);

        if ($objContactForm !== false && $objContactForm->RecordCount() == 1) {
            $this->arrForms[$id] = array();
            $arrEmails           = explode(',', $objContactForm->fields['mails']);
            $subject             = !empty($objContactForm->fields['subject']) ? $objContactForm->fields['subject'] : $_ARRAYLANG['TXT_CONTACT_FORM']." ".$_CONFIG['domainUrl'];
            $feedback            = $objContactForm->fields['feedback'];
            $mailTemplate        = $objContactForm->fields['mailTemplate'];
            $showForm            = $objContactForm->fields['showForm'];
            $useCaptcha          = $objContactForm->fields['use_captcha'];
            $sendCopy            = $objContactForm->fields['send_copy'];
            $htmlMail            = $objContactForm->fields['html_mail'];
            return true;
        } else {
            return false;
        }
    }

    function getContactFormCaptchaStatus($id)
    {
        global $objDatabase;

        $objContactForm = $objDatabase->SelectLimit("SELECT use_captcha FROM ".DBPREFIX."module_contact_form WHERE id=".$id, 1);
        if ($objContactForm !== false && $objContactForm->RecordCount() == 1) {
            return $objContactForm->fields['use_captcha'];
        } else {
            return false;
        }
    }

    /**
     * Get the form fields
     *
     * @author      Comvation AG <info@comvation.com>
     * @author      Stefan Heinemann <sh@adfinis.com>
     * @param       int $formID
     * @return      array
     */
    function getFormFields($formID)
    {
        if ($formID <= 0) {
            return array();
        }

        global $objDatabase;

        $arrFields = array();

        if (isset($this->arrForms[$formID])) {
            $query = "
                SELECT 
                    `f`.`id`,
                    `f`.`type`,
                    `f`.`is_required`,
                    `f`.`check_type`,
                    `l`.`name`,
                    `l`.`langID`,
                    `l`.`attributes`
                FROM 
                    `".DBPREFIX."module_contact_form_field`         AS `f`

                LEFT JOIN
                    `".DBPREFIX."module_contact_form_field_lang`    AS `l`
                ON
                    `f`.`id` = `l`.`fieldID`

                WHERE 
                    `id_form` = ".$formID."

                ORDER BY 
                    `f`.`order_id`,
                    `f`.`id`
            ";
            $res  = $objDatabase->Execute($query);

            $lastID = 0;
            if ($res !== false) {
                while (!$res->EOF) {
                    $id = $res->fields['id'];
                    if ($lastID != $id) {
                        $lastID = $id;

                        $arrFields[$id] = array(
                            'type'          => $res->fields['type'],
                            'is_required'   => $res->fields['is_required'],
                            'check_type'    => $res->fields['check_type'],
                            'editType'     => 'edit'
                        );
                    }

                    $arrFields[$id]['lang'][$res->fields['langID']] = array(
                        'name'  => $res->fields['name'],
                        'value' => $res->fields['attributes']
                    );

                    $res->MoveNext();
                }
            }
            return $arrFields;
        } else {
            return array();
        }
    }

    /**
     * Return the recipients of a form
     *
     * @author      Stefan Heinemann <sh@adfinis.com>
     * @param       int $formID
     * @return      array
     */
    protected function getRecipients($formID, $allLanguages = true)
    {
        global $objDatabase;

        $formID = intval($formID);

        if ($formID == 0) {
            return array();
        }

        if ($allLanguages == false) {
            $sqlWhere = "";
        }

        $query = '
            SELECT
                `r`.`id`,
                `r`.`email`,
                `r`.`sort`,
                `l`.`name`,
                `l`.`langID`
            FROM
                `'.DBPREFIX.'module_contact_recipient`      AS `r`

            LEFT JOIN
                `'.DBPREFIX.'module_contact_recipient_lang` AS `l`
            ON
                `l`.`recipient_id` = `r`.`id`

            WHERE
                `r`.`id_form` = '.$formID.'

            ORDER BY
                `sort`,
                `r`.`id`
        ';

        $res = $objDatabase->execute($query);
        $lastID = 0;
        $recipients = array();
        if ($res !== false) {
            foreach ($res as $recipient) {
                if ($lastID != $recipient['id']) {
                    $recipients[$recipient['id']] = array(
                        'id'        => $recipient['id'],
                        'email'     => contrexx_stripslashes($recipient['email']),
                        'sort'      => $recipient['sort'],
                        'editType' => 'edit'
                    );
                    $lastID = $recipient['id'];
                }

                $recipients[$lastID]['lang'][$recipient['langID']] = 
                    contrexx_stripslashes($recipient['name']);
            }
        }
        
        return $recipients;
    }

    /**
     * Add a new recipient
     *
     * @author      Stefan Heinemann <sh@adfinis.com>
     * @param       int $formID
     * @param       array $recipient
     */
    protected function addRecipient($formID, $recipient) 
    {
        global $objDatabase;

        $email = contrexx_addslashes($recipient['email']);
        $sort = intval($recipient['sort']);

        $query = '
            INSERT INTO
                `'.DBPREFIX.'module_contact_recipient`
            (
                `id_form`,
                `email`,
                `sort`
            )
            VALUES
            (
                '.$formID.',
                "'.$email.'",
                '.$sort.'
            )
        ';

        $objDatabase->execute($query);
        $recipientID = $objDatabase->insert_id();

        foreach ($recipient['lang'] as $langID => $name) {
            $this->setRecipientLang($recipientID, $langID, $name);
        }

        return $recipientID;
    }

    /**
     * Update the recipient
     *
     * @author      Stefan Heinemann <sh@adfinis.com>
     * @param       array $recipient
     */
    protected function updateRecipient($recipient)
    {
        global $objDatabase;

        $id = intval($recipient['id']);
        $email = contrexx_addslashes($recipient['email']);
        $sort = intval($recipient['sort']);

        $query = '
            UPDATE
                `'.DBPREFIX.'module_contact_recipient`
            SET
                `email` = "'.$email.'",
                `sort` = '.$sort.'
            WHERE
                `id`  = '.$id.'
        ';

        $objDatabase->execute($query);

        foreach ($recipient['lang'] as $langID => $name) {
            $this->setRecipientLang($id, $langID, $name);
        }
    }

    /**
     * Set the recipient name of a lang
     *
     * @author      Stefan Heinemann <sh@adfinis.com>
     * @param       int $rcID
     * @param       int $langID
     * @param       string $name
     */
    private function setRecipientLang($rcID, $langID, $name) 
    {
        global $objDatabase;

        $rcID = intval($rcID);
        $langID = intval($langID);
        $name = contrexx_addslashes($name);

        $query = '
            INSERT INTO
                `'.DBPREFIX.'module_contact_recipient_lang`
            (
                `recipient_id`,
                `name`,
                `langID`
            )
            VALUES
            (
                '.$rcID.',
                "'.$name.'",
                '.$langID.'
            )
            ON DUPLICATE KEY UPDATE
                `name` = "'.$name.'"';

        $objDatabase->execute($query);
    }

    function getFormFieldNames($id)
    {
        global $objDatabase;

        $arrFieldNames = array();
        
        if (isset($this->arrForms[$id])) {
            $objFields = $objDatabase->Execute("SELECT `f`.`id`, `l`.`name`
                                                 FROM `".DBPREFIX."module_contact_form_field` as `f`
                                                 LEFT JOIN `".DBPREFIX."module_contact_form_field_lang` as `l`
                                                 ON `f`.`id` = `l`.`fieldID`
                                                 WHERE `f`.`id_form` = ".$id."
                                                 ORDER BY `f`.`order_id`");

            if ($objFields !== false) {
                while (!$objFields->EOF) {
                    $arrFieldNames[$objFields->fields['name']] = $objFields->fields['id'];
                    $objFields->MoveNext();
                }
            }
            return $arrFieldNames;
        } else {
            return false;
        }
    }

    /**
     * Check if there already exist a form with this name
     *
     * @author      Stefan Heinemann <sh@adfinis.com>
     * @param       string $name
     * @param       int $id
     * @param       int $lang
     * @return      boolean
     */
    function isUniqueFormName($name, $lang, $id = 0)
    {
        global $objDatabase;

        $name = contrexx_addslashes($name);

        $query = "
            SELECT
                `f`.`id`
            FROM
                `".DBPREFIX."module_contact_form`       AS `f`
            LEFT JOIN
                `".DBPREFIX."module_contact_form_lang`  AS `l`
            ON
                `f`.`id` = `l`.`formID`
            AND
                `l`.`langID` = ".intval($lang)."
            WHERE
                `l`.`name` = '".$name."'
        ";

        $res = $objDatabase->Execute($query);

        if ($id == 0) {
            return $res->RecordCount() == 0;
        } else {
            return $res->RecordCount() == 0 || $res->fields[$id] == $id;
        }

        // this is crap. Why does it always read all of the forms?
        // ok, admittedly, t's also crap to query the db for each language
        // ... but i don't fucking care right now.
        /*
        if (is_array($this->arrForms)) {
            foreach ($this->arrForms as $formId => $arrForm) {
                if ($formId != $id && $arrForm['name'] == $name) {
                    return false;
                }
            }
        }
        return true;
         */
    }

    /**
     * Update an existing form
     *
     * @author      Comvation AG <info@comvation.com>
     * @author      Stefan Heinemann <sh@adfinis.com>
     * @param       int $formID
     * @param       string $emails
     * @param       bool $showForm
     * @param       bool $useCaptcha
     * @param       bool $useCustomStyle
     * @param       bool $sendCopy
     */
    function updateForm(
        $formID,
        $emails,
        $showForm,
        $useCaptcha,
        $useCustomStyle,
        $sendCopy,
        $sendHtmlMail
    )
    {
        global $objDatabase;

        $objDatabase->Execute("
            UPDATE 
                `".DBPREFIX."module_contact_form`
            SET 
                mails               = '".addslashes($emails)."',
                showForm            = ".$showForm.",
                use_captcha         = ".$useCaptcha.",
                use_custom_style    = ".$useCustomStyle.",
                send_copy           = ".$sendCopy." ,
                html_mail           = ".$sendHtmlMail."
            WHERE 
                id = ".$formID
        );

        $this->initContactForms(true);
    }

    /**
     * Add a new form
     *
     * @author      Comvation AG <info@Comvation.com>
     * @author      Stefan Heinemann <sh@adfinis.com>
     * @param       string $emails
     * @param       bool $showForm
     * @param       bool $useCaptcha
     * @param       bool $useCustomStyle
     * @param       bool $sendCopy
     */
    function addForm(
        $emails,
        $showForm,
        $useCaptcha,
        $useCustomStyle,
        $sendCopy,
        $sendHtmlMail
    )
    {
        global $objDatabase, $_FRONTEND_LANGID;

        $query = "
            INSERT INTO 
                ".DBPREFIX."module_contact_form
            (
                `mails`,
                `showForm`,
                `use_captcha`,
                `use_custom_style`,
                `send_copy`,
                `html_mail`
            )
            VALUES
            (
                '".addslashes($emails)."',
                ".$showForm.",
                ".$useCaptcha.",
                ".$useCustomStyle.",
                ".$sendCopy.",
                ".$sendHtmlMail."
            )";

        if ($objDatabase->Execute($query) !== false) {
            $formId = $objDatabase->Insert_ID();

            /*
            foreach ($arrFields as $fieldId => $arrField) {
                $this->_addFormField($formId, $arrField['name'], $arrField['type'], $arrField['attributes'], $arrField['order_id'], $arrField['is_required'], $arrField['check_type']);
            }
             */
        }
        $_REQUEST['formId'] = $formId;

        $this->initContactForms(true);

        return $formId;
    }

    /**
     * Insert the language values, update them if they already exist
     *
     * @author      Stefan Heinemann <sh@adfinis.com>
     * @param       int $formID
     * @param       int $langID
     * @param       string $name
     * @param       string $text
     * @param       string $feedback
     * @param       string $subject
     */
    protected function insertFormLangValues(
        $formID,
        $langID,
        $isActive,
        $name,
        $text,
        $feedback,
        $mailTemplate,
        $subject
    ) {
        global $objDatabase;

        $formID       = intval($formID);
        $langID       = intval($langID);
        $isActive     = intval($isActive);
        $name         = contrexx_addslashes($name);
        $text         = contrexx_addslashes($text);
        $feedback     = contrexx_addslashes($feedback);
        $mailTemplate = contrexx_addslashes($mailTemplate);
        $subject      = contrexx_addslashes($subject);

        $query = "
            INSERT INTO
                `".DBPREFIX."module_contact_form_lang`
            (
                `formID`,
                `langID`,
                `is_active`,
                `name`,
                `text`,
                `feedback`,
                `mailTemplate`,
                `subject`
            )
            VALUES
            (
                ".$formID.",
                ".$langID.",
                ".$isActive.",
                '".$name."',
                '".$text."',
                '".$feedback."',
                '".$mailTemplate."',
                '".$subject."'
            )
            ON DUPLICATE KEY UPDATE
                `name`         = '".$name."',
                `is_active`    = '".$isActive."',
                `text`         = '".$text."',
                `feedback`     = '".$feedback."',
                `mailTemplate` = '".$mailTemplate."',
                `subject`      = '".$subject."'
        ";

        $objDatabase->execute($query);
    }

    /**
     * delete recipients
     *
     * @param integer $id
     * @return bool
     */
    function _deleteFormRecipients($id){
        global $objDatabase;

        $query = "
            DELETE
                `l`
            FROM
                `".DBPREFIX."module_contact_recipient_lang`     AS `l`
            LEFT JOIN
                `".DBPREFIX."module_contact_recipient`          AS `r`
            ON
                `r`.`id` =  `l`.`recipient_id`
            WHERE
                `r`.`id_form` = ".$id;

        $objDatabase->query($query);

        $query = "
            DELETE FROM 
                ".DBPREFIX."module_contact_recipient 
            WHERE 
                id_form = ".$id;
        if($objDatabase->Execute($query)){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Delete a form 
     *
     * @author      Comvation AG <info@comvation.com>
     */
    protected function deleteForm($id)
    {
        global $objDatabase;

        $id = intval($id);

        $query = "
            DELETE FROM
                `".DBPREFIX."module_contact_form_lang`
            WHERE
                `formID` = ".$id;

        $objDatabase->execute($query);

        $query = "
            DELETE FROM 
                ".DBPREFIX."module_contact_form 
            WHERE 
                id = ".$id;

        $res = $objDatabase->Execute($query);
        if ($res !== false) {
            $this->_deleteFormFieldsByFormId($id);
            $this->_deleteFormDataByFormId($id);
            $this->_deleteFormRecipients($id);
            $this->initContactForms(true);

            return true;
        } else {
            return false;
        }
    }

    /**
     * Update a form field
     *
     * @author      Stefan Heinemann <sh@adfinis.com>
     * @param       int $formID
     * @param       array $field
     */
    protected function updateFormField(
        $field
    )
    {
        global $objDatabase, $_ARRAYLANG;

        $fieldID = $field['id'];
        $query = '
            UPDATE
                `'.DBPREFIX.'module_contact_form_field`
            SET
                `type`          = "'.$field['type'].'",
                `is_required`   = "'.$field['is_required'].'",
                `check_type`    = "'.$field['check_type'].'",
                `order_id`      = "'.$field['order_id'].'"
            WHERE
                `id` = '.$fieldID;

        $objDatabase->execute($query);
        foreach ($field['lang'] as $langID => $values) {
            if ($field['type'] == 'select') {
                $replaceString = $_ARRAYLANG['TXT_CONTACT_PLEASE_SELECT'].',';
                if ($field['is_required'] == 1) {
                    if (strpos($values['value'], $replaceString) === false) {
                        $values['value'] = $replaceString.$values['value'];
                    }
                } else {
                    $values['value'] = str_replace($replaceString, '', $values['value']);
                }
            }
            $this->setFormFieldLang($fieldID, $langID, $values);
        }
    }

    /**
     * Add a form field to the database
     *
     * @author      Stefan Heinemann <sh@adfinis.com>
     * @param       int $formID
     * @param       array $field
     * @return      int 
     */
    protected function addFormField($formID, $field) 
    {
        global $objDatabase, $_ARRAYLANG;
        
        $query = '
            INSERT INTO
                `'.DBPREFIX.'module_contact_form_field`
            (
                `id_form`,
                `type`,
                `is_required`,
                `check_type`,
                `order_id`
            )
            VALUES
            (
                "'.$formID.'",
                "'.$field['type'].'",
                "'.$field['is_required'].'",
                "'.$field['check_type'].'",
                "'.$field['order_id'].'"
            )
            ';

        $objDatabase->execute($query);
        $fieldID = $objDatabase->insert_id();

        foreach ($field['lang'] as $langID => $values) {
            if ($field['type'] == 'select' && $field['is_required'] == 1) {
                    $values['value'] = $_ARRAYLANG['TXT_CONTACT_PLEASE_SELECT'].','.$values['value'];
            }
            $this->setFormFieldLang($fieldID, $langID, $values);
        }

        return $fieldID;
    }

    /**
     * Remove the form fields that are not in the given list
     *
     * @author      Stefan Heinemann <sh@adfinis.com>
     * @param       int $formID
     * @param       array $formFields
     */
    protected function cleanFormFields($formID, $formFields) {
        global $objDatabase;

        if (count($formFields) == 0) {
            return;
        }

        $list = implode(', ', $formFields);
        $formID = intval($formID);

        $query = '
            DELETE 
                `l`
            FROM
                `'.DBPREFIX.'module_contact_form_field_lang` AS  `l`
            LEFT JOIN
                `'.DBPREFIX.'module_contact_form_field`      AS `f`
            ON
                `fieldID` = `f`.`id`
            WHERE
                `fieldID` NOT IN ('.$list.')
            AND
                `id_Form` = '.$formID;

        $objDatabase->execute($query);

        $query = '
            DELETE FROM
                `'.DBPREFIX.'module_contact_form_field`
            WHERE
                `id` NOT IN ('.$list.')
            AND
                `id_form` = '.$formID;

        $objDatabase->execute($query);
    }

    /**
     * Delete the recipients that aren't wanted anymore
     *
     * @author      Stefan Heinemann <sh@adfinis.com>
     * @param       int $formID
     * @param       array $recipients
     */
    protected function cleanRecipients($formID, $recipients) {
        global $objDatabase;

        if (count($recipients) == 0) {
            return;
        }

        $list = implode(', ', $recipients);
        $formID = intval($formID);

        $query = '
            DELETE
                `l`
            FROM
                `'.DBPREFIX.'module_contact_recipient_lang`  AS `l`

            LEFT JOIN
                `'.DBPREFIX.'module_contact_recipient`       AS `r`
            ON
                `recipient_id` = `r`.`id`

            WHERE
                `recipient_id` NOT IN ('.$list.')

            AND
                `id_form` = '.$formID;

        $objDatabase->execute($query);

        $query = '
            DELETE FROM
                `'.DBPREFIX.'module_contact_recipient`
            WHERE
                `id` NOT IN ('.$list.')
            AND
                `id_form` = '.$formID;

        $objDatabase->execute($query);

    }

    /**
     * Add a form lang to a field
     *
     * In case it already exists, update the value
     * @author      Stefan Heinemann <sh@adfinis.com>
     * @param       int $fieldID
     * @param       array $values
     */
    protected function setFormFieldLang($fieldID, $langID, $values) 
    {
        global $objDatabase;

        $name = contrexx_addslashes($values['name']);
        $value = contrexx_addslashes($values['value']);

        $query = '
            INSERT INTO
                `'.DBPREFIX.'module_contact_form_field_lang`
            (
                `fieldID`,
                `name`,
                `attributes`,
                `langID`
            )
            VALUES
            (
                "'.$fieldID.'",
                "'.$name.'",
                "'.$value.'",
                "'.$langID.'"
            )
            ON DUPLICATE KEY UPDATE
                `name` = "'.$name.'",
                `attributes` = "'.$value.'"
            ';

        $objDatabase->execute($query);
    }


    /*
    function _addFormField($formId, $name, $type, $attributes, $orderId, $isRequired, $checkType)
    {
        global $objDatabase;

        $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_contact_form_field (`id_form`, `name`, `type`, `attributes`, `order_id`, `is_required`, `check_type`) VALUES (".$formId.", '".addslashes($name)."', '".$type."', '".addslashes($attributes)."', ".$orderId.", '".$isRequired."', '".$checkType."')");
    }
     */


    function _deleteFormField($id)
    {
        global $objDatabase;

        $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_contact_form_field WHERE id=".$id);
    }

    /**
     * Delete form fields
     *
     * @author      Comvation AG <info@comvation.com>
     * @param       int $id
     */
    private function _deleteFormFieldsByFormId($id)
    {
        global $objDatabase;

        $query = "
            DELETE 
                `l`
            FROM
                `".DBPREFIX."module_contact_form_field_lang`    AS `l`
            LEFT JOIN
                `".DBPREFIX."module_contact_form_field`         AS `f`
            ON
                `l`.`fieldID` = `f`.`id`
            WHERE
                `f`.`id_form` = ".$id;

        $objDatabase->Execute($query);

        $query = "
            DELETE FROM 
                ".DBPREFIX."module_contact_form_field 
            WHERE 
                id_form = ".$id;

        $objDatabase->Execute($query);
    }

    /**
     * Delete form data 
     *
     * @author      Comvation AG <info@comvation.com>
     * @param       int $id
     */
    private function _deleteFormDataByFormId($id)
    {
        global $objDatabase;

        $query = "
            DELETE FROM 
                `".DBPREFIX."module_contact_form_data`
            WHERE 
                `id_form` = ".$id;
        $objDatabase->Execute($query);
    }

    function deleteFormEntry($id)
    {
        global $objDatabase;

        $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_contact_form_data WHERE id=".$id);
    }

    /**
     * Creates an array containing all frontend-languages.
     *
     * Contents:
     * $arrValue[$langId]['short']      =>  For Example: en, de, fr, ...
     * $arrValue[$langId]['long']       =>  For Example: 'English', 'Deutsch', 'French', ...
     * @global  ADONewConnection
     * @return  array       $arrReturn
     */
    function createLanguageArray() {
        global $objDatabase;

        $arrReturn = array();

        $objResult = $objDatabase->Execute('SELECT      id,
                                                        lang,
                                                        name
                                            FROM        '.DBPREFIX.'languages
                                            WHERE       frontend=1
                                            ORDER BY    id
                                        ');
        while (!$objResult->EOF) {
            $arrReturn[$objResult->fields['id']] = array(   'short' =>  stripslashes($objResult->fields['lang']),
                                                            'long'  =>  htmlentities(stripslashes($objResult->fields['name']),ENT_QUOTES, CONTREXX_CHARSET)
                                                        );
            $objResult->MoveNext();
        }

        return $arrReturn;
    }
    
    function getFormEntries($formId, &$arrCols, $pagingPos, &$paging, $limit = true)
    {
        global $objDatabase, $_CONFIG;

        $arrEntries = array();
        $arrCols    = array();
        $arrFields  = $this->getFormFields($formId);
        $query      = "SELECT `id`, `id_lang`, `time`, `host`, `lang`, `ipaddress`
                      FROM ".DBPREFIX."module_contact_form_data
                      WHERE id_form = ".$formId."
                      ORDER BY `time` DESC";
        $objEntry = $objDatabase->Execute($query);

        $count = $objEntry->RecordCount();
        if ($limit && $count > intval($_CONFIG['corePagingLimit'])) {
            $paging   = getPaging($count, $pagingPos, "&amp;cmd=contact&amp;act=forms&amp;tpl=entries&amp;formId=".$formId, $_ARRAYLANG['TXT_CONTACT_FORM_ENTRIES']);
            $objEntry = $objDatabase->SelectLimit($query, $_CONFIG['corePagingLimit'], $pagingPos);
        }

        if ($objEntry !== false) {
            while (!$objEntry->EOF) {
                $arrData   = array();
                $objResult = $objDatabase->SelectLimit("SELECT `id_field`, `formlabel`, `formvalue`
                                                    FROM ".DBPREFIX."module_contact_form_submit_data
                                                    WHERE id_entry=".$objEntry->fields['id']."
                                                    ORDER BY id");

                while (!$objResult->EOF) {
                    $field_id = $objResult->fields['id_field'];
                    $arrData[$field_id] = $objResult->fields['formvalue'];

                    if (!in_array($field_id, $arrCols)) {
                        array_push($arrCols, $field_id);
                    }
                    $objResult->MoveNext();
                }

                $arrEntries[$objEntry->fields['id']] = array(
                    'langId'    => $objEntry->fields['id_lang'],
                    'time'      => $objEntry->fields['time'],
                    'host'      => $objEntry->fields['host'],
                    'lang'      => $objEntry->fields['lang'],
                    'ipaddress' => $objEntry->fields['ipaddress'],
                    'data'      => $arrData
                );
                $objEntry->MoveNext();
            }
        }
        
        return $arrEntries;
    }

    function getFormEntry($id)
    {
        global $objDatabase;

        $arrEntry;
        $objEntry = $objDatabase->SelectLimit("SELECT `id`, `id_lang`, `time`, `host`, `lang`, `ipaddress`
                                               FROM ".DBPREFIX."module_contact_form_data
                                               WHERE id=".$id, 1);

        if ($objEntry !== false) {
            $objResult = $objDatabase->SelectLimit("SELECT `id_field`, `formlabel`, `formvalue`
                                                    FROM ".DBPREFIX."module_contact_form_submit_data
                                                    WHERE id_entry=".$objEntry->fields['id']."
                                                    ORDER BY id");
            $arrData = array();
            while (!$objResult->EOF){
                $arrData[$objResult->fields['id_field']] = $objResult->fields['formvalue'];
                $objResult->MoveNext();
            }

            $arrEntry = array(
                'langId'    => $objEntry->fields['id_lang'],
                'time'      => $objEntry->fields['time'],
                'host'      => $objEntry->fields['host'],
                'lang'      => $objEntry->fields['lang'],
                'ipaddress' => $objEntry->fields['ipaddress'],
                'data'      => $arrData
            );
        }

        return $arrEntry;
    }

    /**
     * Get Javascript Source
     *
     * Makes the sourcecode for the javascript based
     * field checking
     */
    function _getJsSourceCode($id, $formFields, $preview = false, $show = false)
    {
        $code  = "<script src=\"lib/datepickercontrol/datepickercontrol.js\" type=\"text/javascript\"></script>\n";
        $code .= "<script type=\"text/javascript\">\n";
        $code .= "/* <![CDATA[ */\n";

        $code .= "fields = new Array();\n";

        foreach ($formFields as $key => $field) {
            $code .= "fields[$key] = Array(\n";
            $code .= "\t'".addslashes($field['lang'][$objInit->userFrontendLangId]['name'])."',\n";
            $code .= "\t{$field['is_required']},\n";
            if ($preview) {
                $code .= "\t'". addslashes($this->arrCheckTypes[$field['check_type']]['regex']) ."',\n";
            } elseif ($show) {
                $code .= "\t'". addslashes($this->arrCheckTypes[$field['check_type']]['regex']) ."',\n";
            } else {
                $code .= "\t'". addslashes($this->arrCheckTypes[$field['check_type']]['regex']) ."',\n";
            }
            $code .= "\t'".$field['type']."');\n";
        }

        $code .= <<<JS_checkAllFields
function checkAllFields() {
    var isOk = true;

    for (var field in fields) {
        var type = fields[field][3];
        if (type == 'text' || type == 'file' || type == 'password' || type == 'textarea') {
            value = document.getElementsByName('contactFormField_' + field)[0].value;
            if (value == "" && isRequiredNorm(fields[field][1], value)) {
                isOk = false;
                document.getElementsByName('contactFormField_' + field)[0].style.border = "red 1px solid";
            } else if (value != "" && !matchType(fields[field][2], value)) {
                isOk = false;
                document.getElementsByName('contactFormField_' + field)[0].style.border = "red 1px solid";
            } else {
                document.getElementsByName('contactFormField_' + field)[0].style.borderColor = '';
            }
        } else if (type == 'checkbox') {
            if (!isRequiredCheckbox(fields[field][1], field)) {
                isOk = false;
            }
        } else if (type == 'checkboxGroup') {
            if (!isRequiredCheckBoxGroup(fields[field][1], field)) {
                isOk = false;
            }
        } else if (type == 'radio') {
            if (!isRequiredRadio(fields[field][1], field)) {
                isOk = false;
            }
        } else if (type == 'select' || type == 'country') {
            if (!isRequiredSelect(fields[field][1], field)) {
                isOk = false;
            }
        }
    }

    if (!isOk) {
        document.getElementById('contactFormError').style.display = "block";
    }
    return isOk;
}

JS_checkAllFields;

        // This is for checking normal text input field if they are required.
        // If yes, it also checks if the field is set. If it is not set, it returns true.
        $code .= <<<JS_isRequiredNorm
function isRequiredNorm(required, value) {
    if (required == 1) {
        if (value == "") {
            return true;
        }
    }
    return false;
}

JS_isRequiredNorm;

        // Matches the type of the value and pattern. Returns true if it matched, false if not.
        $code .= <<<JS_matchType
function matchType(pattern, value) {
    var reg = new RegExp(pattern);
    if (value.match(reg)) {
        return true;
    }
    return false;
}

JS_matchType;

        // Checks if a checkbox is required but not set. Returns false when finding an error.
        $code .= <<<JS_isRequiredCheckbox
function isRequiredCheckbox(required, field) {
    if (required == 1) {
        if (!document.getElementsByName('contactFormField_' + field)[0].checked) {
            document.getElementsByName('contactFormField_' + field)[0].style.border = "red 1px solid";
            return false;
        }
    }
    document.getElementsByName('contactFormField_' + field)[0].style.borderColor = '';

    return true;
}

JS_isRequiredCheckbox;

        // Checks if a multile checkbox is required but not set. Returns false when finding an error.
        $code .= <<<JS_isRequiredCheckBoxGroup
function isRequiredCheckBoxGroup(required, field) {
    if (required == true) {
        var boxes = document.getElementsByName('contactFormField_' + field + '[]');
        var checked = false;
        for (var i = 0; i < boxes.length; i++) {
            if (boxes[i].checked) {
                checked = true;
            }
        }
        if (checked) {
            setListBorder('contactFormField_' + field + '[]', false);
            return true;
        } else {
            setListBorder('contactFormField_' + field + '[]', '1px red solid');
            return false;
        }
    } else {
        return true;
    }
}

JS_isRequiredCheckBoxGroup;

        // Checks if some radio button need to be checked. Returns false if it finds an error
        $code .= <<<JS_isRequiredRadio
function isRequiredRadio(required, field) {
    if (required == 1) {
        var buttons = document.getElementsByName('contactFormField_' + field);
        var checked = false;
        for (var i = 0; i < buttons.length; i++) {
            if (buttons[i].checked) {
                checked = true;
            }
        }
        if (checked) {
            setListBorder('contactFormField_' + field, false);
            return true;
        } else {
            setListBorder('contactFormField_' + field, '1px red solid');
            return false;
        }
    } else {
        return true;
    }
}

JS_isRequiredRadio;

        $code .=<<<JS_isRequiredSelect
function isRequiredSelect(required, field){
    if(required == 1){
        menuIndex = document.getElementById('contactFormFieldId_' + field).selectedIndex;
        if (menuIndex == 0) {
            document.getElementsByName('contactFormField_' + field)[0].style.border = "red 1px solid";
            return false;
        }
    }
    document.getElementsByName('contactFormField_' + field)[0].style.borderColor = '';
    return true;
}

JS_isRequiredSelect;

        // Sets the border attribute of a group of checkboxes or radiobuttons
        $code .= <<<JS_setListBorder
function setListBorder(field, borderColor) {
    var boxes = document.getElementsByName(field);
    for (var i = 0; i < boxes.length; i++) {
        if (borderColor) {
            boxes[i].style.border = borderColor;
        } else {
            boxes[i].style.borderColor = '';
        }
    }
}


JS_setListBorder;

        $code .= <<<JS_misc
/* ]]> */
</script>

JS_misc;
        return $code;
    }
}
?>
