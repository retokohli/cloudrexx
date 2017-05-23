<?php

/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2015
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Cloudrexx" is a registered trademark of Cloudrexx AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * ContactLib
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Cloudrexx Development Team <info@cloudrexx.com>
 * @version     1.0.0
 * @package     cloudrexx
 * @subpackage  coremodule_contact
 * @todo        Edit PHP DocBlocks!
 */

namespace Cx\Core_Modules\Contact\Controller;

/**
 * ContactLib
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Cloudrexx Development Team <info@cloudrexx.com>
 * @access      public
 * @version     1.0.0
 * @package     cloudrexx
 * @subpackage  coremodule_contact
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
     * @param null|string $order the order for the sql query (comes from Sorting class)
     */
    function initContactForms($order = null)
    {
        global $objDatabase;

        $this->arrForms = array();

        if ($order) {
            $order = ' ORDER BY ' . $order;
        }
        // load form meta information
        $query = 'SELECT `f`.`id`,
                         `f`.`mails`,
                         `f`.`showForm`,
                         `f`.`use_captcha`,
                         `f`.`use_custom_style`,
                         `f`.`save_data_in_crm`,
                         `f`.`send_copy`,
                         `f`.`use_email_of_sender`,
                         `f`.`html_mail`,
                         `f`.`send_attachment`,
                         `f`.`crm_customer_groups`,
                         (SELECT COUNT(`id`) FROM `'.DBPREFIX.'module_contact_form_data` AS `d` WHERE `d`.`id_form` = `f`.`id`)  AS `numberOfEntries`,
                         (SELECT MAX(`time`) FROM `'.DBPREFIX.'module_contact_form_data` AS `d` WHERE `d`.`id_form` = `f`.`id`) AS `latestEntry`
                    FROM `'.DBPREFIX.'module_contact_form` AS `f`
                    LEFT JOIN `'.DBPREFIX.'module_contact_form_lang` AS `l`
                        ON `f`.`id` = `l`.`formID`
                    WHERE `l`.`langID` = ' . FRONTEND_LANG_ID . ' ' . $order;
        $objResult = $objDatabase->Execute($query);
        if ($objResult) {
            while (!$objResult->EOF) {
                $this->arrForms[$objResult->fields['id']] = array(
                    'emails'            => $objResult->fields['mails'],
                    'showForm'          => $objResult->fields['showForm'],
                    'useCaptcha'        => $objResult->fields['use_captcha'],
                    'saveDataInCRM'     => $objResult->fields['save_data_in_crm'],
                    'useCustomStyle'    => $objResult->fields['use_custom_style'],
                    'sendCopy'          => $objResult->fields['send_copy'],
                    'useEmailOfSender'  => $objResult->fields['use_email_of_sender'],
                    'htmlMail'          => $objResult->fields['html_mail'],
                    'sendAttachment'    => $objResult->fields['send_attachment'],
                    'recipients'        => $this->getRecipients($objResult->fields['id'], true),
                    'number'            => 0,
                    'last'              => 0,
                    'crmCustomerGroups' => $objResult->fields['crm_customer_groups'] ? unserialize($objResult->fields['crm_customer_groups']) : array()
                );
                $objResult->MoveNext();
            }
        }

        // load localizations
        $query = 'SELECT `formID` AS `id`,
                         `is_active`,
                         `name`,
                         `langID`,
                         `text`,
                         `feedback`,
                         `mailTemplate`,
                         `subject`
                    FROM `'.DBPREFIX.'module_contact_form_lang`';
        $objResult = $objDatabase->Execute($query);
        if ($objResult) {
            while (!$objResult->EOF) {
                // $this->arrForms[$formId]['lang'][$langId]
                $this->arrForms[$objResult->fields['id']]['lang'][$objResult->fields['langID']] = array(
                    'is_active'     => $objResult->fields['is_active'],
                    'name'          => $objResult->fields['name'],
                    'text'          => $objResult->fields['text'],
                    'feedback'      => $objResult->fields['feedback'],
                    'mailTemplate'  => $objResult->fields['mailTemplate'],
                    'subject'       => $objResult->fields['subject']
                );
                $objResult->MoveNext();
            }
        }

        // load info about submitted data
        $query = 'SELECT `id_form` AS `id`,
                         COUNT(id) AS `number`,
                         MAX(time) AS `last`
                    FROM `'.DBPREFIX.'module_contact_form_data`
                   GROUP BY `id_form`
                   ORDER BY last DESC';
        $objResult = $objDatabase->Execute($query);
        if ($objResult) {
            while (!$objResult->EOF) {
                $this->arrForms[$objResult->fields['id']]['number'] = intval($objResult->fields['number']);
                $this->arrForms[$objResult->fields['id']]['last'] = intval($objResult->fields['last']);
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
                'regex'     => \FWValidator::REGEX_EMAIL_JS,
                'name'      => 'TXT_CONTACT_REGEX_EMAIL',
                'modifiers' => 'i'
            ),
            3   => array(
                'regex'     => \FWValidator::REGEX_URI_JS,
                'name'      => 'TXT_CONTACT_REGEX_URL',
                'modifiers' => 'i'
            ),
        /*a bit redundant, because we want a minimum of one non-space character.
          the query does a [spaceorchar]*[char]+[spaceorchar]* to ensure this. */
            4   => array(
                'regex'     => '^[a-zäàáüâûôñèöéè\ ]*'.
                               '[a-zäàáüâûôñèöéè]+'.
                               '[a-zäàáüâûôñèöéè\ ]*$',
                'name'      => 'TXT_CONTACT_REGEX_TEXT',
                'modifiers' => 'i'
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

    function getContactFormDetails($id, &$arrEmails, &$subject, &$feedback, &$mailTemplate, &$showForm, &$useCaptcha, &$sendCopy, &$useEmailOfSender, &$htmlMail, &$sendAttachment, &$saveDataInCRM, &$crmCustomerGroups)
    {
        global $objDatabase, $_CONFIG, $_ARRAYLANG, $_LANGID;

        $objContactForm = $objDatabase->SelectLimit("SELECT f.mails, l.subject, l.feedback, l.mailTemplate, f.showForm,
                                                            f.use_captcha, f.send_copy, f.use_email_of_sender, f.html_mail, f.send_attachment,
                                                            f.save_data_in_crm, f.crm_customer_groups
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
            $useEmailOfSender    = $objContactForm->fields['use_email_of_sender'];
            $htmlMail            = $objContactForm->fields['html_mail'];
            $sendAttachment      = $objContactForm->fields['send_attachment'];
            $saveDataInCRM       = $objContactForm->fields['save_data_in_crm'];
            $crmCustomerGroups   = $objContactForm->fields['crm_customer_groups'] ? unserialize($objContactForm->fields['crm_customer_groups']) : array();
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
     * @author      Cloudrexx AG <info@cloudrexx.com>
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
                    `f`.`special_type`,
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
                            'special_type'  => $res->fields['special_type'],
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
     * @author      Cloudrexx AG <info@cloudrexx.com>
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
        $useEmailOfSender,
        $sendHtmlMail,
        $sendAttachment,
        $saveDataInCrm,
        $crmCustomerGroups
    )
    {
        global $objDatabase;
        $formEntity = $this->getFormEntity(
                        $formID,
                        $emails,
                        $showForm,
                        $useCaptcha,
                        $useCustomStyle,
                        $sendCopy,
                        $useEmailOfSender,
                        $sendHtmlMail,
                        $sendAttachment,
                        $saveDataInCrm,
                        $crmCustomerGroups
        );
        \Env::get('cx')->getEvents()->triggerEvent('model/preUpdate', array(new \Doctrine\ORM\Event\LifecycleEventArgs($formEntity, \Env::get('em'))));
        $objDatabase->Execute("
            UPDATE
                `".DBPREFIX."module_contact_form`
            SET
                mails               = '".addslashes($emails)."',
                showForm            = ".$showForm.",
                use_captcha         = ".$useCaptcha.",
                use_custom_style    = ".$useCustomStyle.",
                send_copy           = ".$sendCopy.",
                use_email_of_sender = ".$useEmailOfSender.",
                html_mail           = ".$sendHtmlMail.",
                send_attachment     = ".$sendAttachment.",
                `save_data_in_crm`  = ".$saveDataInCrm.",
                `crm_customer_groups`  = \"" . contrexx_input2db(serialize($crmCustomerGroups)) . "\"
            WHERE
                id = ".$formID
        );
        \Env::get('cx')->getEvents()->triggerEvent('model/postUpdate', array(new \Doctrine\ORM\Event\LifecycleEventArgs($formEntity, \Env::get('em'))));
        $this->initContactForms();
    }

    /**
     * Add a new form
     *
     * @author      Cloudrexx AG <info@Cloudrexx.com>
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
        $useEmailOfSender,
        $sendHtmlMail,
        $sendAttachment,
        $saveDataInCrm,
        $crmCustomerGroups
    )
    {
        global $objDatabase, $_FRONTEND_LANGID;
        $entity = $this->getFormEntity(0,
                    $emails,
                    $showForm,
                    $useCaptcha,
                    $useCustomStyle,
                    $sendCopy,
                    $useEmailOfSender,
                    $sendHtmlMail,
                    $sendAttachment,
                    $saveDataInCrm,
                    $crmCustomerGroups
        );
        \Env::get('cx')->getEvents()->triggerEvent('model/prePersist', array(new \Doctrine\ORM\Event\LifecycleEventArgs($entity, \Env::get('em'))));
        $query = "
            INSERT INTO
                ".DBPREFIX."module_contact_form
            (
                `mails`,
                `showForm`,
                `use_captcha`,
                `use_custom_style`,
                `send_copy`,
                `use_email_of_sender`,
                `html_mail`,
                `send_attachment`,
                `save_data_in_crm`,
                `crm_customer_groups`
            )
            VALUES
            (
                '".addslashes($emails)."',
                ".$showForm.",
                ".$useCaptcha.",
                ".$useCustomStyle.",
                ".$sendCopy.",
                ".$useEmailOfSender.",
                ".$sendHtmlMail.",
                ".$sendAttachment.",
                ".$saveDataInCrm.",
                \"". contrexx_input2db(serialize($crmCustomerGroups)) . "\"
            )";

        if ($objDatabase->Execute($query) !== false) {
            $formId = $objDatabase->Insert_ID();
            \Env::get('cx')->getEvents()->triggerEvent('model/postPersist', array(new \Doctrine\ORM\Event\LifecycleEventArgs($entity, \Env::get('em'))));
            /*
            foreach ($arrFields as $fieldId => $arrField) {
                $this->_addFormField($formId, $arrField['name'], $arrField['type'], $arrField['attributes'], $arrField['order_id'], $arrField['is_required'], $arrField['check_type']);
            }
             */
        }
        $_REQUEST['formId'] = $formId;

        $this->initContactForms();

        return $formId;
    }

    /**
     * Get the form entity
     *
     * @param       int $id
     * @param       string $emails
     * @param       bool $showForm
     * @param       bool $useCaptcha
     * @param       bool $useCustomStyle
     * @param       bool $sendCopy
     */
    protected function getFormEntity(
        $id,
        $emails,
        $showForm,
        $useCaptcha,
        $useCustomStyle,
        $sendCopy,
        $useEmailOfSender,
        $sendHtmlMail,
        $sendAttachment,
        $saveDataInCrm,
        $crmCustomerGroups
    ) {
        if($id) {
            $entity = \Env::get('em')->getRepository('Cx\Core_Modules\Contact\Model\Entity\Form')->findOneBy(array('id' => $id));
        } else {
            $entity = new \Cx\Core_Modules\Contact\Model\Entity\Form();
        }
        $entity->setMails($emails);
        $entity->setShowForm($showForm);
        $entity->setUseCaptcha($useCaptcha);
        $entity->setUseCustomStyle($useCustomStyle);
        $entity->setSendCopy($sendCopy);
        $entity->setUseEmailOfSender($useEmailOfSender);
        $entity->setHtmlMail($sendHtmlMail);
        $entity->setSendAttachment($sendAttachment);
        $entity->setSaveDataInCrm($saveDataInCrm);
        $entity->setCrmCustomerGroups($crmCustomerGroups);
        return $entity;
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
        $name         = contrexx_raw2db($name);
        $text         = contrexx_raw2db($text);
        $feedback     = contrexx_raw2db($feedback);
        $mailTemplate = contrexx_raw2db($mailTemplate);
        $subject      = contrexx_raw2db($subject);

        if ($isActive == 1) {
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
                    `is_active`    = ".$isActive.",
                    `text`         = '".$text."',
                    `feedback`     = '".$feedback."',
                    `mailTemplate` = '".$mailTemplate."',
                    `subject`      = '".$subject."'
            ";
        } else {
            /*
             * Remove Form configurations for inactive language
             */
            $query = "DELETE FROM `".DBPREFIX."module_contact_form_lang`
                        WHERE `formID`  = ".$formID." AND `langID` = ".$langID;
        }
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
     * @author      Cloudrexx AG <info@cloudrexx.com>
     */
    protected function deleteForm($id)
    {
        global $objDatabase;

        $id = intval($id);
        $form = \Env::get('em')->getRepository('Cx\Core_Modules\Contact\Model\Entity\Form')->findOneBy(array('id' => $id));
        \Env::get('cx')->getEvents()->triggerEvent('model/preRemove', array(new \Doctrine\ORM\Event\LifecycleEventArgs($form, \Env::get('em'))));
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
            $this->_deleteFormFieldsAndDataByFormId($id);
            $this->_deleteFormRecipients($id);
            \Env::get('cx')->getEvents()->triggerEvent('model/postRemove', array(new \Doctrine\ORM\Event\LifecycleEventArgs($form, \Env::get('em'))));
            $this->initContactForms();

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
    protected function updateFormField($field)
    {
        global $objDatabase, $_ARRAYLANG;

        $fieldID = $field['id'];
        $query = '
            UPDATE
                `'.DBPREFIX.'module_contact_form_field`
            SET
                `type`          = "'.$field['type'].'",
                `special_type`  = "'.$field['special_type'].'",
                `is_required`   = "'.$field['is_required'].'",
                `check_type`    = "'.$field['check_type'].'",
                `order_id`      = "'.$field['order_id'].'"
            WHERE
                `id` = '.$fieldID;

        $objDatabase->execute($query);

        foreach ($field['lang'] as $langID => $values) {
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
                `special_type`,
                `is_required`,
                `check_type`,
                `order_id`
            )
            VALUES
            (
                "'.$formID.'",
                "'.$field['type'].'",
                "'.$field['special_type'].'",
                "'.$field['is_required'].'",
                "'.$field['check_type'].'",
                "'.$field['order_id'].'"
            )
            ';

        $objDatabase->execute($query);
        $fieldID = $objDatabase->insert_id();

        foreach ($field['lang'] as $langID => $values) {
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

        /*
         * Deletes language attributes for fields of inactive languages
         */
        $langId = array();
        foreach ($_POST['contactFormLanguages'] as $key => $value) {
            $langId[] = $key;
        }
        $activeLang = implode(', ', $langId);
        foreach ($formFields as $fieldId) {
            $query = "DELETE FROM `".DBPREFIX."module_contact_form_field_lang`
                      WHERE `fieldID` = ".$fieldId."
                      AND `langID` NOT IN (".$activeLang.")
                      ";
            $objDatabase->execute($query);
        }
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

         /*
         * Empty fields name and value inactive languages
         */
        $langId = array();
        foreach ($_POST['contactFormLanguages'] as $key => $value) {
            $langId[] = $key;
        }
        $activeLang = implode(', ', $langId);
        foreach ($recipients as $recipientId) {
            $query = "UPDATE `".DBPREFIX."module_contact_recipient_lang`
                      SET `name` = ''
                      WHERE `recipient_id` = ".$recipientId."
                      AND `langID` NOT IN (".$activeLang.")
                      ";
            $objDatabase->execute($query);
        }
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

        $name = contrexx_raw2db($values['name']);
        $value = contrexx_raw2db($values['value']);

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

    /**
     * Delete form fields and data
     *
     * @author      Cloudrexx AG <info@cloudrexx.com>
     * @param       int $id
     */
    private function _deleteFormFieldsAndDataByFormId($id)
    {
        global $objDatabase;

        $query = "
            DELETE
                `f`, `l`, `sd`, `d`
            FROM
                `".DBPREFIX."module_contact_form_field_lang`    AS `l`
            LEFT JOIN
                `".DBPREFIX."module_contact_form_field`         AS `f`
            ON
                `l`.`fieldID` = `f`.`id`
            LEFT JOIN
                `".DBPREFIX."module_contact_form_submit_data` AS `sd`
            ON
                `f`.`id` = `sd`.`id_field`
            LEFT JOIN
                `".DBPREFIX."module_contact_form_data` AS  `d`
            ON
                `f`.`id_form` = `d`.`id_form`
            WHERE
                `f`.`id_form` = ".$id;

        $objDatabase->Execute($query);
    }

    function deleteFormEntry($id)
    {
        global $objDatabase;
        //let's search for uploaded files left.
        $rs = $objDatabase->Execute("SELECT `d`.`id_form`, `sd`.`formvalue`
                                     FROM `".DBPREFIX."module_contact_form_data` AS `d`
                                        LEFT JOIN
                                            `".DBPREFIX."module_contact_form_submit_data` AS `sd`
                                        ON
                                            `d`.`id` = `sd`.`id_entry`
                                        INNER JOIN
                                             `".DBPREFIX."module_contact_form_field` AS `f`
                                        ON
                                            `f`.`id` = `sd`.`id_field`
                                        AND
                                            `f`.`type` IN ('multi_file', 'file')
                                        WHERE `d`.`id`=".$id);
        if ($rs) {
            while (!$rs->EOF) {
                $data = $rs->fields['formvalue'];
                $formId = $rs->fields['id_form'];

                //get all form data into arrData
                $arrData = array();
                foreach (explode(';', $data) as $keyValue) {
                    $arrTmp = explode(',', $keyValue);
                        $arrData[base64_decode($arrTmp[0])] = base64_decode($arrTmp[1]);
                }

                //load contact form fields - we need to know which ones have the type 'file'
                $this->initContactForms();
                $arrFormFields = $this->getFormFields($formId);

                foreach($arrFormFields as $arrField) {
                    //see if it's a file field...
                    if($arrField['type'] == 'file') {
                        //...and delete the files if yes:
                        $val = isset($arrData[$arrField['name']]) ? $arrData[$arrField['name']] : $data;
                        if(strpos($val,'*')) {
                            //new style entry, multiple files
                            $arrFiles = explode('*', $val);
                        }
                        else {
                            //old style entry, single file
                            $arrFiles = array($val);
                        }

                        //nice, we have all the files. delete them.
                        foreach($arrFiles as $file) {
                            \Cx\Lib\FileSystem\FileSystem::delete_file(\Env::get('cx')->getWebsiteDocumentRootPath().$file);
                        }
                    }
                }
                $rs->MoveNext();
            }
        }

        $objDatabase->Execute("DELETE `d`, `sd` FROM
                                `".DBPREFIX."module_contact_form_data` AS `d`
                               LEFT JOIN
                                `".DBPREFIX."module_contact_form_submit_data` AS `sd`
                               ON
                                `d`.`id` = `sd`.`id_entry`
                               WHERE `d`.`id`=".$id);
    }

    function getFormEntries($formId, &$arrCols, $pagingPos, &$paging, $limit = true)
    {
        global $objDatabase, $_CONFIG, $_ARRAYLANG;

        $arrEntries = array();
        $arrCols    = array();
        $query      = "SELECT `id`, `id_lang`, `time`, `host`, `lang`, `ipaddress`
                      FROM ".DBPREFIX."module_contact_form_data
                      WHERE id_form = ".$formId."
                      ORDER BY `time` DESC";
        $objEntry = $objDatabase->Execute($query);

        $count = $objEntry->RecordCount();
        if ($limit && $count > intval($_CONFIG['corePagingLimit'])) {
            $paging   = getPaging($count, $pagingPos, "&cmd=Contact&act=forms&tpl=entries&formId=".$formId, $_ARRAYLANG['TXT_CONTACT_FORM_ENTRIES']);
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

// TODO: What is this good for?
//                    if($field_id == 'unique_id') //skip unique id of each entry, we do not want to display this.
//                        continue;

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

    function getFormEntry($entryId)
    {
        global $objDatabase;

        $arrEntry = null;
        $objEntry = $objDatabase->SelectLimit('
            SELECT `id`, `id_lang`, `time`, `host`, `lang`, `ipaddress`, `id_form`
            FROM `'.DBPREFIX.'module_contact_form_data`
            WHERE `id` = '.$entryId
        , 1);

        if ($objEntry !== false) {
            $formId = $objEntry->fields['id'];

            $objResult = $objDatabase->SelectLimit('
                SELECT `id_field`, `formlabel`, `formvalue`
                FROM `'.DBPREFIX.'module_contact_form_submit_data`
                WHERE `id_entry` = '.$objEntry->fields['id'].'
                ORDER BY `id`
            ');

            $fileFieldId = 0;
// TODO: what is with legacyMode uploads??
            if (!$this->legacyMode) {
                $rs = $objDatabase->SelectLimit('
                    SELECT `id`
                    FROM `'.DBPREFIX.'module_contact_form_field`
                    WHERE (`type` = "file") AND (`id_form` = '.$formId.')'
                , 1);
                if (($rs !== false) && (!$rs->EOF)) {
                    $fileFieldId = $rs->fields['id'];
                }
            }

            $arrData = array();
            while (!$objResult->EOF){
                $fieldId = $objResult->fields['id_field'];

                $arrData[$fieldId]['label'] = $objResult->fields['formlabel'];
                $arrData[$fieldId]['value'] = $objResult->fields['formvalue'];

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
     * @todo    The javascript code must be loaded using JS:registerCode()
     */
    function _getJsSourceCode($id, $formFields, $preview = false, $show = false)
    {
        global $objInit, $_ARRAYLANG;
        $this->initCheckTypes();

        \JS::activate('jqueryui');

        $code = "<script type=\"text/javascript\">\n";
        $code .= "/* <![CDATA[ */\n";
        $code .= 'cx.ready(function() { cx.jQuery(\'.datetime\').datetimepicker(); });';
        $code .= 'cx.ready(function() { cx.jQuery(\'.date\').datepicker(); });'; // New Field-Type: only Display Datepicker (without time)

        $code .= "fields = new Array();\n";

        foreach ($formFields as $key => $field) {
            $modifiers = isset($this->arrCheckTypes[$field['check_type']]['modifiers']) ? $this->arrCheckTypes[$field['check_type']]['modifiers'] : '';

            $code .= "fields[$key] = Array(\n";
// TODO: do we have to change FRONTEND_LANG_ID to selectedInterfaceLanguage ?
            $code .= "\t'". contrexx_raw2xhtml($field['lang'][FRONTEND_LANG_ID]['name']) ."',\n";
            $code .= "\t".  ($field['is_required'] ? 'true' : 'false' ) .",\n";

            $code .= "\t".(!empty($this->arrCheckTypes[$field['check_type']]['regex']) ? '/'.($this->arrCheckTypes[$field['check_type']]['regex']).'/'.$modifiers : "''").",\n";
            $code .= "\t'". (($field['type'] != 'special') ? $field['type'] : $field['special_type']) ."');\n";
        }
        $captchaValidationCode = \Cx\Core_Modules\Captcha\Controller\Captcha::getInstance()->getJSValidationFn();
        $captchaErrorMsg = addslashes($_ARRAYLANG['TXT_CONTACT_RECAPTCHA_ERROR']);
        $code .= <<<JS_checkAllFields
function checkAllFields() {
    var isOk = true, isCaptchaOk = true;
    var captchaError = '$captchaErrorMsg';

    for (var field in fields) {
        var type = fields[field][3];
        if (type != null && type != undefined) {
        if ((type == 'text') || (type == 'password') || (type == 'textarea') || (type == 'date') || ((type.match(/access_/) != null) && (type != 'access_country') && (type != 'access_title') && (type != 'access_gender'))) {
            value = document.getElementsByName('contactFormField_' + field)[0].value;
            if ((\$J.trim(value) == '') && isRequiredNorm(fields[field][1], value)) {
                isOk = false;
                \$J('#contactFormFieldId_'+field).css('border', '1px solid red');
            } else if ((value != '') && !matchType(fields[field][2], value)) {
                isOk = false;
                \$J('#contactFormFieldId_'+field).css('border', '1px solid red');
            } else {
                \$J('#contactFormFieldId_'+field).attr('style', '');
            }
        } else if (type == 'checkbox') {
            if (!isRequiredCheckbox(fields[field][1], field)) {
                isOk = false;
                \$J('#contactFormFieldId_'+field).css('outline', '1px solid red');
            } else {
                \$J('#contactFormFieldId_'+field).css('outline', '');
            }
        } else if (type == 'checkboxGroup') {
            if (!isRequiredCheckBoxGroup(fields[field][1], field)) {
                isOk = false;
                \$J('#contactFormFieldId_'+field).css('outline', '1px solid red');
            } else {
                \$J('#contactFormFieldId_'+field).css('outline', '');
            }
        } else if (type == 'radio') {
            if (!isRequiredRadio(fields[field][1], field)) {
                isOk = false;
                \$J('#contactFormFieldId_'+field).css('outline', '1px solid red');
            } else {
                \$J('#contactFormFieldId_'+field).css('outline', '');
            }
        } else if (type == 'file' || type == 'multi_file') {
            var required = fields[field][1];
            if(required && angular.element('#contactFormUpload_'+field+ ' div.mediaBrowserfolderWidget').scope().isEmpty()) {
                isOk = false;
                \$J('#contactFormFieldId_'+field).css('outline', '1px solid red');
            } else {
                \$J('#contactFormFieldId_'+field).css('outline', '');
            }
        } else if (type == 'select' || type == 'country' || type == 'access_country' || type == 'access_title' || type == 'access_gender') {
            if (!isRequiredSelect(fields[field][1], field)) {
                isOk = false;
            }
        }
    }
    }
    $captchaValidationCode
    document.getElementById('contactFormError').style.display = "none";
    if (!isOk) {
        document.getElementById('contactFormError').style.display = "block";
    }

    if (\$J('#contactFormCaptchaError').length) {
        \$J('#contactFormCaptchaError').remove();
    }
    if (!isCaptchaOk) {
        \$J('<div />')
        .addClass('text-danger')
        .attr('id', 'contactFormCaptchaError')
        .text(captchaError)
        .prependTo('#captcha');
        return false;
    }
    return isOk;
}
JS_checkAllFields;

        // This is for checking normal text input field if they are required.
        // If yes, it also checks if the field is set. If it is not set, it returns true.
        $code .= <<<JS_isRequiredNorm
function isRequiredNorm(required, value) {
    if (required == 1) {
        if (\$J.trim(value) == "") {
            return true;
        }
    }
    return false;
}

JS_isRequiredNorm;

        // Matches the type of the value and pattern. Returns true if it matched, false if not.
        $code .= <<<JS_matchType
function matchType(pattern, value) {
    return value.match(new RegExp(pattern)) != null;
}

JS_matchType;

        // Checks if a checkbox is required but not set. Returns false when finding an error.
        $code .= <<<JS_isRequiredCheckbox
function isRequiredCheckbox(required, field) {
    if (required == 1) {
        if (!document.getElementsByName('contactFormField_' + field)[0].checked) {
            return false;
        }
    }

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
            return true;
        } else {
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
            return true;
        } else {
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

        $code .= <<<JS_misc
/* ]]> */
</script>

JS_misc;
        return $code;
    }

    /*
     * Generates the HTML Source code of the Submission form designed in backend
     * @id      Submission form id
     * @lang    Language for which source code to be generated
     * @preview Boolean, generated preview source or raw source
     * @show    Boolean, generated frontend code
     */
    function getSourceCode($id, $lang, $preview = false, $show = false)
    {
        global $_ARRAYLANG, $objInit, $objDatabase;

        $hasFileInput = false; //remember if we added a file input -> this would need the uploader to be initialized

        $arrFields = $this->getFormFields($id);
        $sourcecode = array();
        $this->initContactForms();

        $sourcecode[] = '<div class="text-warning" id="contactFeedback">{CONTACT_FEEDBACK_TEXT}</div>
<div id="contactDescription"><!-- BEGIN formText -->'.($preview ? $this->arrForms[$id]['lang'][$lang]['text'] : '{'.$id.'_FORM_TEXT}').'<!-- END formText --></div>
<div class="text-danger" id="contactFormError">'.($preview ? $_ARRAYLANG['TXT_NEW_ENTRY_ERORR'] : '{TXT_NEW_ENTRY_ERORR}').'</div>
<!-- BEGIN contact_form -->
<form role="form" id="contactForm'.(($this->arrForms[$id]['useCustomStyle'] > 0) ? '_'.$id : '').'" class="contactForm'.(($this->arrForms[$id]['useCustomStyle'] > 0) ? '_'.$id : '').'" action="'.($preview ? '../' : '').'index.php?section=Contact&amp;cmd='.$id.'" method="post" enctype="multipart/form-data" onsubmit="return checkAllFields();">
    <fieldset id="contactFrame">
    <legend>'.($preview ? $this->arrForms[$id]['lang'][$lang]['name'] : '{'.$id.'_FORM_NAME}').'</legend>';

        foreach ($arrFields as $fieldId => $arrField) {
            if ($arrField['is_required']) {
                $required = '<strong class="is_required">*</strong>';
            } else {
                $required = '';
            }

            if ($arrField['type'] != 'fieldset' && $arrField['type'] != 'hidden') {
                $sourcecode[] = '<div class="contact row form-group">';
            }

            switch ($arrField['type']) {
                case 'label':
                case 'hidden':
                case 'horizontalLine':
                case 'checkbox':
                    break;
                case 'fieldset':
                    $sourcecode[] = '</fieldset>';
                    $sourcecode[] = '<fieldset id="contactFormFieldId_'.$fieldId.'">';
                    $sourcecode[] = "<legend>".($preview ? contrexx_raw2xhtml($arrField['lang'][$lang]['name']) : "{".$fieldId."_LABEL}")."</legend>";
                    break;
                case 'checkboxGroup':
                case 'radio':
                    $sourcecode[] = '<label>'.
                                    ($preview ? contrexx_raw2xhtml($arrField['lang'][$lang]['name']) : "{".$fieldId."_LABEL}")
                                    .$required.'</label>';
                    break;
                case 'date':
                    $sourcecode[] = '<label for="contactFormFieldId_'.$fieldId.'">'.
                                    ($preview ? contrexx_raw2xhtml($arrField['lang'][$lang]['name']) : "{".$fieldId."_LABEL}")
                                    .$required.'</label>';
                    break;
                case 'datetime':
                    $sourcecode[] = '<label for="contactFormFieldId_'.$fieldId.'">'.
                                    ($preview ? contrexx_raw2xhtml($arrField['lang'][$lang]['name']) : "{".$fieldId."_LABEL}")
                                    .$required.'</label>';
                    break;
                default:
                    $sourcecode[] = '<label for="contactFormFieldId_'.$fieldId.'">'.
                                    ($preview ? contrexx_raw2xhtml($arrField['lang'][$lang]['name']) : "{".$fieldId."_LABEL}")
                                    .$required.'</label>';
            }

            $arrField['lang'][$lang]['value'] = preg_replace('/\[\[([A-Z0-9_]+)\]\]/', '{$1}', $arrField['lang'][$lang]['value']);
            $fieldType                        = ($arrField['type'] != 'special') ? $arrField['type'] : $arrField['special_type'];
            switch ($fieldType) {
                case 'label':
                    $sourcecode[] = '<div class="contactFormClass_'.$arrField['type'].'">'.contrexx_raw2xhtml($arrField['lang'][$lang]['value']).'</div>';
                    break;

                case 'checkbox':
                    $sourcecode[] = '<div class="checkbox"><label for="contactFormFieldId_'.$fieldId.'"><input class="contactFormClass_'.$arrField['type'].'" id="contactFormFieldId_'.$fieldId.'" type="checkbox" name="contactFormField_'.$fieldId.'" value="1" {SELECTED_'.$fieldId.'} />'.
                                    ($preview ? contrexx_raw2xhtml($arrField['lang'][$lang]['name']) : "{".$fieldId."_LABEL}")
                                    .$required.'</label></div>';
                    break;

                case 'checkboxGroup':
                    $selectedLang = $preview ? FRONTEND_LANG_ID : $lang;
                    $sourcecode[] = '<div class="contactFormGroup" id="contactFormFieldId_'.$fieldId.'">';
                    $options      = explode(',', $arrField['lang'][$selectedLang]['value']);
                    foreach ($options as $index => $option) {
                        $sourcecode[] = '<div class="checkbox"><label class="noCaption" for="contactFormField_'.$index.'_'.$fieldId.'"><input type="checkbox" class="contactFormClass_'.$arrField['type'].'" name="contactFormField_'.$fieldId.'[]" id="contactFormField_'.$index.'_'.$fieldId.'" value="'.contrexx_raw2xhtml($option).'" {SELECTED_'.$fieldId.'_'.$index.'}/>'.($preview ? contrexx_raw2xhtml($option) : '{'.$fieldId.'_'.$index.'_VALUE}').'</label></div>';
                    }
                    $sourcecode[] = '</div>';
                    break;

                case 'country':
                case 'access_country':
                    $sourcecode[] = '<select class="contactFormClass_'.$arrField['type'].'" name="contactFormField_'.$fieldId.'" id="contactFormFieldId_'.$fieldId.'">';
                    if ($arrField['is_required'] == 1) {
                        $sourcecode[] = "<option value=\"".($preview ? $_ARRAYLANG['TXT_CONTACT_PLEASE_SELECT'] : '{TXT_CONTACT_PLEASE_SELECT}')."\">".($preview ? $_ARRAYLANG['TXT_CONTACT_PLEASE_SELECT'] : '{TXT_CONTACT_PLEASE_SELECT}')."</option>";
                    } else {
                        $sourcecode[] = "<option value=\"".($preview ? $_ARRAYLANG['TXT_CONTACT_NOT_SPECIFIED'] : '{TXT_CONTACT_NOT_SPECIFIED}')."\">".($preview ? $_ARRAYLANG['TXT_CONTACT_NOT_SPECIFIED'] : '{TXT_CONTACT_NOT_SPECIFIED}')."</option>";
                    }
                    if ($preview) {
                        $lang = $arrField['lang'][$lang]['name'];
                        $country = \Cx\Core\Country\Controller\Country::getNameArray(true, $lang);

                        foreach ($country as $id => $name) {
                            $sourcecode[] = "<option value=\"" . $name . "\" >" . $name . "</option>";
                        }
                    } else {
                        $sourcecode[] = "<!-- BEGIN field_".$fieldId." -->";
                        $sourcecode[] = "<option value=\"{".$fieldId."_VALUE}\" {SELECTED_".$fieldId."} >{".$fieldId."_VALUE}</option>";
                        $sourcecode[] = "<!-- END field_".$fieldId." -->";
                    }
                    $sourcecode[] = "</select>";
                    break;

                case 'date':
                    $sourcecode[] = '<input class="contactFormClass_'.$arrField['type'].' date" type="text" name="contactFormField_'.$fieldId.'" id="contactFormFieldId_'.$fieldId.'" value="{'.$fieldId.'_VALUE}" />';
                    break;
                case 'datetime':
                    $sourcecode[] = '<input class="contactFormClass_'.$arrField['type'].' datetime" type="text" name="contactFormField_'.$fieldId.'" id="contactFormFieldId_'.$fieldId.'" value="{'.$fieldId.'_VALUE}" />';
                    break;
                case 'access_birthday':
                    $sourcecode[] = '<input class="contactFormClass_'.$arrField['type'].' date" id="contactFormFieldId_'.$fieldId.'" type="text" name="contactFormField_'.$fieldId.'" value="'.($preview ? contrexx_raw2xhtml($arrField['lang'][$lang]['value']) : '{'.$fieldId.'_VALUE}').'" />';
                    break;

                case 'file':
                case 'multi_file':
                    $sourcecode[] = '<div class="contactFormUpload" id="contactFormUpload_'.$fieldId.'">{CONTACT_UPLOADER_FOLDER_WIDGET_'.$fieldId.'}<input type="hidden" name="contactFormUploadId_'.$fieldId.'" value = "{CONTACT_UPLOADER_ID_'.$fieldId.'}"/>';
                    $sourcecode[] = '<input class="contactFormClass_'.$arrField['type'].'" id="contactFormFieldId_'.$fieldId.'" type="file" name="contactFormField_'.$fieldId.'" disabled="disabled"/></div>';
                    break;

                case 'hidden':
                    $sourcecode[] = '<input class="contactFormClass_'.$arrField['type'].'" id="contactFormFieldId_'.$fieldId.'" type="hidden" name="contactFormField_'.$fieldId.'" value="'.($preview ? contrexx_raw2xhtml($arrField['lang'][$lang]['value']) : "{".$fieldId."_VALUE}").'" />';
                    break;

                case 'horizontalLine':
                    $sourcecode[] = '<hr />';
                    break;

                case 'password':
                    $sourcecode[] = '<input class="contactFormClass_'.$arrField['type'].'" id="contactFormFieldId_'.$fieldId.'" type="password" name="contactFormField_'.$fieldId.'" value="" />';
                    break;

                case 'radio':
                    $selectedLang = $preview ? FRONTEND_LANG_ID : $lang;
                    $sourcecode[] = '<div class="contactFormGroup" id="contactFormFieldId_'.$fieldId.'">';
                    $options      = explode(',', $arrField['lang'][$selectedLang]['value']);
                    foreach ($options as $index => $option) {
                        $sourcecode[] .= '<div class="radio"><label class="noCaption" for="contactFormField_'.$index.'_'.$fieldId.'"><input class="contactFormClass_'.$arrField['type'].'" type="radio" name="contactFormField_'.$fieldId.'" id="contactFormField_'.$index.'_'.$fieldId.'" value="'.($preview ? contrexx_raw2xhtml($option) : '{'.$fieldId.'_'.$index.'_VALUE}').'" {SELECTED_'.$fieldId.'_'.$index.'} />'.($preview ? contrexx_raw2xhtml($option) : '{'.$fieldId.'_'.$index.'_VALUE}').'</label></div>';
                    }
                    $sourcecode[] = '</div>';
                    break;

                case 'access_title':
                case 'access_gender':
                    // collect user attribute options
                    $arrOptions = array();
                    $accessAttributeId = str_replace('access_', '', $fieldType);
                    $objAttribute = \FWUser::getFWUserObject()->objUser->objAttribute->getById($accessAttributeId);

                    // get options
                    $arrAttribute = $objAttribute->getChildren();
                    foreach ($arrAttribute as $attributeId) {
                        $objAttribute = \FWUser::getFWUserObject()->objUser->objAttribute->getById($attributeId);
                        $arrOptions[] = $objAttribute->getName(FRONTEND_LANG_ID);
                    }

                    // options will be used for select input generation
                    $arrField['lang'][FRONTEND_LANG_ID]['value'] = implode(',', $arrOptions);

                case 'select':
                    $sourcecode[] = '<select class="contactFormClass_'.$arrField['type'].'" name="contactFormField_'.$fieldId.'" id="contactFormFieldId_'.$fieldId.'">';
                    if ($preview) {
                        $options = explode(',', $arrField['lang'][FRONTEND_LANG_ID]['value']);
                        foreach ($options as $index => $option) {
                            $sourcecode[] = "<option value='".contrexx_raw2xhtml($option)."'>". contrexx_raw2xhtml($option) ."</option>";
                        }
                    } else {
                        $sourcecode[] = "<!-- BEGIN field_".$fieldId." -->";
                        $sourcecode[] = "<option value='{".$fieldId."_VALUE}' {SELECTED_".$fieldId."}>". '{'.$fieldId.'_VALUE}'."</option>";
                        $sourcecode[] = "<!-- END field_".$fieldId." -->";
                    }
                    $sourcecode[] = "</select>";
                    break;

                case 'textarea':
                    $sourcecode[] = '<textarea class="contactFormClass_'.$arrField['type'].'" name="contactFormField_'.$fieldId.'" id="contactFormFieldId_'.$fieldId.'" rows="5" cols="20">{'.$fieldId.'_VALUE}</textarea>';
                    break;
                case 'recipient':
                    $sourcecode[] = '<select class="contactFormClass_'.$arrField['type'].'" name="contactFormField_'.$fieldId.'" id="contactFormFieldId_'.$fieldId.'">';
                    if ($preview) {
                        foreach ($this->arrForms[$id]['recipients'] as $index => $arrRecipient) {
                            $sourcecode[] = "<option value='".$index."'>". $arrRecipient['lang'][$lang] ."</option>";
                        }
                    } else {
                        $sourcecode[] = "<!-- BEGIN field_".$fieldId." -->";
                        $sourcecode[] = "<option value='{".$fieldId."_VALUE_ID}' {SELECTED_".$fieldId."} >". '{'.$fieldId.'_VALUE}'."</option>";
                        $sourcecode[] = "<!-- END field_".$fieldId." -->";
                    }
                    $sourcecode[] = "</select>";
                    break;
                case 'fieldset':
                    break;
                default:
                    $sourcecode[] = '<input class="contactFormClass_'.$arrField['type'].'" id="contactFormFieldId_'.$fieldId.'" type="text" name="contactFormField_'.$fieldId.'" value="'.($preview ? contrexx_raw2xhtml($arrField['lang'][$lang]['value']) : '{'.$fieldId.'_VALUE}').'" />';
                    break;
            }

            if ($arrField['type'] != 'fieldset' && $arrField['type'] != 'hidden') {
                $sourcecode[] = '</div>';
            }
        }

        if ($preview) {
            $themeId = $objInit->arrLang[FRONTEND_LANG_ID]['themesid'];
            if (($objRS = $objDatabase->SelectLimit("SELECT `foldername` FROM `".DBPREFIX."skins` WHERE `id` = ".$themeId, 1)) !== false) {
                $themePath = $objRS->fields['foldername'];
            }
            $sourcecode[] = '<link href="../core_modules/Contact/View/Style/form.css" rel="stylesheet" type="text/css" />';

            if ($this->arrForms[$id]['useCaptcha']) {
                $sourcecode[] = '<div class="contact row form-group">';
                $sourcecode[] = '<label>'.$_ARRAYLANG["TXT_CONTACT_CAPTCHA"].'</label>';
                $sourcecode[] = \Cx\Core_Modules\Captcha\Controller\Captcha::getInstance()->getCode();
                $sourcecode[] = '</div>';
            }
        } else {
            $sourcecode[] = "<!-- BEGIN contact_form_captcha -->";
            $sourcecode[] = '<div class="contact row form-group">';
            $sourcecode[] = '<label>{TXT_CONTACT_CAPTCHA}</label>';
            $sourcecode[] = '{CONTACT_CAPTCHA_CODE}';
            $sourcecode[] = '</div>';
            $sourcecode[] = "<!-- END contact_form_captcha -->";
        }

        $sourcecode[] = '<div class="contact row form-group">';
        $sourcecode[] = '<input class="contactFormClass_button btn btn-default" type="submit" name="submitContactForm" value="'.($preview ? $_ARRAYLANG['TXT_CONTACT_SUBMIT'] : '{TXT_CONTACT_SUBMIT}').'" /><input class="contactFormClass_button btn btn-default" type="reset" value="'.($preview ? $_ARRAYLANG['TXT_CONTACT_RESET'] : '{TXT_CONTACT_RESET}').'" />';
        $sourcecode[] = '<input type="hidden" name="unique_id" value="{CONTACT_UNIQUE_ID}" />';
        $sourcecode[] = '</div>';
        $sourcecode[] = "</fieldset>";
        $sourcecode[] = "</form>";
        $sourcecode[] = "<!-- END contact_form -->";

        $sourcecode[] = $preview ? $this->_getJsSourceCode($id, $arrFields, $preview, $show) : "{CONTACT_JAVASCRIPT}";

        if ($show) {
            $sourcecode = preg_replace('/\{([A-Z0-9_-]+)\}/', '[[\\1]]', $sourcecode);
        }

        return implode("\n", $sourcecode);
    }
}
