<?php
/**
 * Contact
 *
 * This module handles all HTML FORMs with action tags to the contact section.
 * It sends the contact email(s) and uploads data (optional)
 * Ex. <FORM name="form1" action="index.php?section=contact&cmd=thanks" method="post">
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.1.0
 * @package     contrexx
 * @subpackage  core_module_contact
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once ASCMS_CORE_MODULE_PATH.'/contact/lib/ContactLib.class.php';
require_once ASCMS_LIBRARY_PATH.'/FRAMEWORK/Validator.class.php';

/**
 * Contact
 *
 * This module handles all HTML FORMs with action tags to the contact section.
 * It sends the contact email(s) and uploads data (optional)
 * Ex. <FORM name="form1" action="index.php?section=contact&cmd=thanks" method="post">
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.1.0
 * @package     contrexx
 * @subpackage  core_module_contact
 */
class Contact extends ContactLib
{

    /**
     * List with the names of the formular fields
     *
     * @var array
     */
    var $arrFormFields = array();

    /**
     * Template object
     *
     * This object contains an instance of the HTML_Template_Sigma class
     * which is used as the template system.
     * @var unknown_type
     */
    var $objTemplate;

    /**
     * Contains the error message if an error occurs
     *
     * This variable will contain a message that describes
     * the error that happend.
     */
    var $errorMsg = '';

    var $captchaError = '';


    /**
     * Contact constructor
     *
     * The constructor does initialize a template system
     * which will be used to display the contact form or the
     * feedback/error message.
     * @param string Content page template
     * @see objTemplate, HTML_Template_Sigma::setErrorHandling(), HTML_Template_Sigma::setTemplate()
     */
    function __construct($pageContent)
    {
        $this->objTemplate = new HTML_Template_Sigma('.');
        $this->objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        $this->objTemplate->setTemplate($pageContent);
        $this->initContactForms(true);
    }

    /**
     * Show the contact page
     *
     * Parse a contact form submit request and show the contact page
     * @see _getContactFormData(), _checkValues(), _insertIntoDatabase(), _sendMail(), _showError(), _showFeedback(), _getParams(), HTML_Template_Sigma::get(), HTML_Template_Sigma::blockExists(), HTML_Template_Sigma::hideBlock(), HTML_Template_Sigma::touchBlock()
     * @return string Parse contact form page
     */
    function getContactPage()
    {
        global $_ARRAYLANG, $_LANGID, $objDatabase;

        $formId = isset($_GET['cmd']) ? intval($_GET['cmd']) : 0;
        $useCaptcha = $this->getContactFormCaptchaStatus($formId);
        $arrFields  = $this->getFormFields($formId);
        $isLoggedin = $this->setProfileData();
        
        $this->objTemplate->setVariable(array(
            'TXT_NEW_ENTRY_ERORR'   => $_ARRAYLANG['TXT_NEW_ENTRY_ERORR'],
            'TXT_CONTACT_SUBMIT'    => $_ARRAYLANG['TXT_CONTACT_SUBMIT'],
            'TXT_CONTACT_RESET'     => $_ARRAYLANG['TXT_CONTACT_RESET'],
            'CONTACT_JAVASCRIPT'    => $this->_getJsSourceCode($formId, $arrFields)
        ));

        if ($this->objTemplate->blockExists('contact_form')) {
            $recipients = $this->getRecipients($formId);
            foreach ($arrFields as $fieldId => $arrField) {
            /*
             * Set values for special field types if the user is authenticated
             */
             if ($isLoggedin) {
                switch ($arrField['special_type']) {
                case 'access_picture':
                    $arrField['lang'][$_LANGID]['value'] = '[[ACCESS_PROFILE_ATTRIBUTE_PICTURE]]';
                    break;
                case 'access_gender':
                    $arrField['lang'][$_LANGID]['value'] = '[[ACCESS_PROFILE_ATTRIBUTE_GENDER]]';
                    break;
                case 'access_title':
                    $arrField['lang'][$_LANGID]['value'] = '[[ACCESS_PROFILE_ATTRIBUTE_TITLE]]';
                    break;
                case 'access_firstname':
                    $arrField['lang'][$_LANGID]['value'] = '[[ACCESS_PROFILE_ATTRIBUTE_FIRSTNAME]]';
                    break;
                case 'access_lastname':
                    $arrField['lang'][$_LANGID]['value'] = '[[ACCESS_PROFILE_ATTRIBUTE_LASTNAME]]';
                    break;
                case 'access_company':
                    $arrField['lang'][$_LANGID]['value'] = '[[ACCESS_PROFILE_ATTRIBUTE_COMPANY]]';
                    break;
                case 'access_address':
                    $arrField['lang'][$_LANGID]['value'] = '[[ACCESS_PROFILE_ATTRIBUTE_ADDRESS]]';
                    break;
                case 'access_city':
                    $arrField['lang'][$_LANGID]['value'] = '[[ACCESS_PROFILE_ATTRIBUTE_CITY]]';
                    break;
                case 'access_zip':
                    $arrField['lang'][$_LANGID]['value'] = '[[ACCESS_PROFILE_ATTRIBUTE_ZIP]]';
                    break;
                case 'access_country':
                    $arrField['lang'][$_LANGID]['value'] = '[[ACCESS_PROFILE_ATTRIBUTE_COUNTRY]]';
                    break;
                case 'access_phone_office':
                    $arrField['lang'][$_LANGID]['value'] = '[[ACCESS_PROFILE_ATTRIBUTE_PHONE_OFFICE]]';
                    break;
                case 'access_phone_private':
                    $arrField['lang'][$_LANGID]['value'] = '[[ACCESS_PROFILE_ATTRIBUTE_PHONE_PRIVATE]]';
                    break;
                case 'access_phone_mobile':
                    $arrField['lang'][$_LANGID]['value'] = '[[ACCESS_PROFILE_ATTRIBUTE_PHONE_MOBILE]]';
                    break;
                case 'access_phone_fax':
                    $arrField['lang'][$_LANGID]['value'] = '[[ACCESS_PROFILE_ATTRIBUTE_PHONE_FAX]]';
                    break;
                case 'access_birthday':
                    $arrField['lang'][$_LANGID]['value'] = '[[ACCESS_PROFILE_ATTRIBUTE_BIRTHDAY]]';
                    break;
                case 'access_website':
                    $arrField['lang'][$_LANGID]['value'] = '[[ACCESS_PROFILE_ATTRIBUTE_WEBSITE]]';
                    break;
                case 'access_profession':
                    $arrField['lang'][$_LANGID]['value'] = '[[ACCESS_PROFILE_ATTRIBUTE_PROFESSION]]';
                    break;
                case 'access_interests':
                    $arrField['lang'][$_LANGID]['value'] = '[[ACCESS_PROFILE_ATTRIBUTE_INTERESTS]]';
                    break;
                case 'access_signature':
                    $arrField['lang'][$_LANGID]['value'] = '[[ACCESS_PROFILE_ATTRIBUTE_SIGNATURE]]';
                    break;
                }
             }
             
             /*
              * Set Default field value through URL Modifiers
              */
                if (!empty($_GET[$fieldId])) {
                    switch ($arrField['type']) {
                    case 'checkboxGroup' :
                    case 'radio' :
                        $index= array_search($_GET[$fieldId], explode(',' ,$arrField['lang'][$_LANGID]['value']));
                        $this->objTemplate->setVariable(array(
                            'SELECTED_'.$fieldId.'_'.$index => 'checked="checked"'
                        ));
                        break;
                    case 'select':
                    case 'recipient':
                        break;
                    default:
                        $arrField['lang'][$_LANGID]['value'] = htmlentities($_GET[$fieldId], ENT_QUOTES, CONTREXX_CHARSET);
                    }
                }

                $arrField['lang'][$_LANGID]['value'] = preg_replace('/\[\[([A-Z0-9_]+)\]\]/', '{$1}', $arrField['lang'][$_LANGID]['value']);

                $this->objTemplate->setVariable(array(
                    $formId.'_FORM_NAME'    => wordwrap($this->arrForms[$formId]['lang'][$_LANGID]['name'], 90, "<br/>\n", true),
                    $formId.'_FORM_TEXT'    => $this->arrForms[$formId]['lang'][$_LANGID]['text'],
                    $fieldId.'_LABEL'       => ($arrField['lang'][$_LANGID]['name'] != "") ? wordwrap($arrField['lang'][$_LANGID]['name'], 90, "<br/>\n", true) : "&nbsp;"
                ));

                /*
                 * Generate values for dropdown checkbox and radio fields
                 */
                $userProfileRegExp = '/\{([A-Z_]+)\}/';
                $fieldType = ($arrField['type'] != 'special') ? $arrField['type'] : $arrField['special_type'];
                switch ($fieldType) {
                case 'checkbox':
                    if ($arrField['lang'][$_LANGID]['value'] == 1) {
                        $this->objTemplate->setVariable(array(
                            'SELECTED_'.$fieldId => 'checked="checked"'
                        ));
                    }
                    break;
                case 'checkboxGroup':
                case 'radio':
                    $options = explode(',', $arrField['lang'][$_LANGID]['value']);
                    foreach ($options as $index => $option) {
                        if (preg_match($userProfileRegExp, $option)) {
                            $valuePlaceholderBlock = 'contact_value_placeholder_block_'.$fieldId.'_'.$index;
                            $this->objTemplate->addBlock($fieldId.'_'.$index.'_VALUE', $valuePlaceholderBlock, $option);
                        } else {
                            $this->objTemplate->setVariable(array(
                                $fieldId.'_'.$index.'_VALUE' => $option
                            ));
                        }
                    }
                    break;
                case 'select':
                    $options = explode(',', $arrField['lang'][$_LANGID]['value']);
                    foreach ($options as $index => $option) {
                        if (preg_match($userProfileRegExp, $option)) {
                            $valuePlaceholderBlock = 'contact_value_placeholder_block_'.$fieldId.'_'.$index;
                            $this->objTemplate->addBlock($fieldId.'_VALUE', $valuePlaceholderBlock, $option);
                        } else {
                            $this->objTemplate->setVariable(array(
                                $fieldId.'_VALUE' => $option
                            ));
                        }
                        if (!empty($_GET[$fieldId])) {
                            if ($index == array_search($_GET[$fieldId], explode(',' ,$arrField['lang'][$_LANGID]['value']))) {
                                $this->objTemplate->setVariable(array(
                                    'SELECTED_'.$fieldId => 'selected = "selected"'
                                ));
                            }
                        }
                        $this->objTemplate->parse('field_'.$fieldId);
                    }
                    break;
                case 'recipient':
                    foreach ($recipients as $index => $recipient) {
                        $recipient['lang'][$_LANGID] = preg_replace('/\[\[([A-Z0-9_]+)\]\]/', '{$1}', $recipient['lang'][$_LANGID]);
                        if (preg_match($userProfileRegExp, $recipient['lang'][$_LANGID])) {
                            $valuePlaceholderBlock = 'contact_value_placeholder_block_'.$fieldId.'_'.$index;
                            $this->objTemplate->addBlock($fieldId.'_VALUE', $valuePlaceholderBlock, $recipient['lang'][$_LANGID]);
                        } else {
                            $this->objTemplate->setVariable(array(
                                $fieldId.'_VALUE'    => $recipient['lang'][$_LANGID]
                            ));
                        }
                        $this->objTemplate->setVariable(array(
                            $fieldId.'_VALUE_ID'    => $index
                        ));
                        if (!empty($_GET[$fieldId])) {
                            if ($recipient['lang'][$_LANGID] == $_GET[$fieldId] ) {
                                $this->objTemplate->setVariable(array(
                                    'SELECTED_'.$fieldId => 'selected = "selected"'
                                ));
                            }
                        }
                        $this->objTemplate->parse('field_'.$fieldId);
                    }
                    break;
                case 'access_country':
                case 'country':
                    $objResult = $objDatabase->Execute("SELECT * FROM " . DBPREFIX . "lib_country");
                    if (preg_match($userProfileRegExp, $arrField['lang'][$_LANGID]['value'])) {
                        $arrField['lang'][$_LANGID]['value'] = $this->objTemplate->_variables[trim($arrField['lang'][$_LANGID]['value'],'{}')];
                    }
                    while (!$objResult->EOF) {
                        $this->objTemplate->setVariable(array(
                            $fieldId.'_VALUE'    => $objResult->fields['name']
                        ));
                        if ((!empty($_GET[$fieldId]) &&
                            strcasecmp($objResult->fields['name'], $_GET[$fieldId]) == 0) ||
                            ($objResult->fields['name'] == $arrField['lang'][$_LANGID]['value'])) {
                                $this->objTemplate->setVariable(array(
                                    'SELECTED_'.$fieldId => 'selected = "selected"'
                                ));
                        }
                        $objResult->MoveNext();
                        $this->objTemplate->parse('field_'.$fieldId);
                    }
                    $this->objTemplate->setVariable(array(
                        'TXT_CONTACT_PLEASE_SELECT' => $_ARRAYLANG['TXT_CONTACT_PLEASE_SELECT'],
                        'TXT_CONTACT_NOT_SPECIFIED' => $_ARRAYLANG['TXT_CONTACT_NOT_SPECIFIED']
                    ));
                    break;
                default :
                    /*
                     * Set default field value through User profile attribute
                     */
                    if (preg_match($userProfileRegExp, $arrField['lang'][$_LANGID]['value'])) {
                        $valuePlaceholderBlock = 'contact_value_placeholder_block_'.$fieldId;
                        $this->objTemplate->addBlock($fieldId.'_VALUE', $valuePlaceholderBlock, $arrField['lang'][$_LANGID]['value']);
                    } else {
                        $this->objTemplate->setVariable(array(
                            $fieldId.'_VALUE'       => $arrField['lang'][$_LANGID]['value']
                        ));
                    }
                }
                /*
                 * Parse the blocks created for parsing user profile data using addBlock()
                 */
                if($this->objTemplate->blockExists($valuePlaceholderBlock)){
                    $this->objTemplate->parse($valuePlaceholderBlock);
                }
            }
        }

        if ($isLoggedin) {
            $useCaptcha = false;
        } else {
            $this->setCaptcha($useCaptcha);
        }
        
        if (isset($_POST['submitContactForm']) || isset($_POST['Submit'])) {
            $showThanks = (isset($_GET['cmd']) && $_GET['cmd'] == 'thanks') ? true : false;
           // $this->_getParams();
            $arrFormData = &$this->_getContactFormData();
            if ($arrFormData) {
                if ($this->_checkValues($arrFormData, $useCaptcha) && $this->_insertIntoDatabase($arrFormData)) {
                    $this->_sendMail($arrFormData);
                    if (isset($arrFormData['showForm']) && !$arrFormData['showForm']) {
                        $this->objTemplate->hideBlock("formText");
                        $this->objTemplate->hideBlock('contact_form');
                    }
                } else {
                    return $this->_showError();
                }

                if (!$showThanks) {
                    $this->_showFeedback($arrFormData);
                } else {
                    if ($this->objTemplate->blockExists("formText")) {
                        $this->objTemplate->hideBlock("formText");
                    }
                }
            }
        } else {
            if ($this->objTemplate->blockExists('formText')) {
                $this->objTemplate->touchBlock('formText');
            }
            //$this->_getParams();
        }
        
        return $this->objTemplate->get();
    }

    private function setProfileData()
    {
        if (!FWUser::getFWUserObject()->objUser->login()) {
            return false;
        }

        $objUser = FWUser::getFWUserObject()->objUser;

        $this->objTemplate->setVariable(array(
            'ACCESS_USER_ID'        => $objUser->getId(),
            'ACCESS_USER_USERNAME'  => htmlentities($objUser->getUsername(), ENT_QUOTES, CONTREXX_CHARSET),
            'ACCESS_USER_EMAIL'     => htmlentities($objUser->getEmail(), ENT_QUOTES, CONTREXX_CHARSET),
        ));

        $objUser->objAttribute->reset();
        while (!$objUser->objAttribute->EOF) {
            $objAttribute = $objUser->objAttribute->getById($objUser->objAttribute->getId());

            switch ($objAttribute->getType())
            {
                case 'menu':
                    if ($objAttribute->isCoreAttribute()) {
                        foreach ($objAttribute->getChildren() as $childAttributeId) {
                            $objChildAtrribute = $objAttribute->getById($childAttributeId);
                            if ($objChildAtrribute->getMenuOptionValue() == $objUser->getProfileAttribute($objAttribute->getId())) {
                                $value = $objChildAtrribute->getName();
                                break;
                            }
                        }
                    } else {
                        $objSelectedAttribute = $objAttribute->getById($objUser->getProfileAttribute($objAttribute->getId()));
                        $value = $objSelectedAttribute->getName();
                    }
                break;

                case 'date':
                    $value = $objUser->getProfileAttribute($objAttribute->getId());
                    $value = $value !== false && $value !== '' ? date(ASCMS_DATE_SHORT_FORMAT, intval($value)) : '';
                break;

                default:
                    $value = $objUser->getProfileAttribute($objAttribute->getId());
                break;
            }
            
            $this->objTemplate->setVariable('ACCESS_PROFILE_ATTRIBUTE_'.strtoupper($objAttribute->getId()), htmlentities($value, ENT_QUOTES, CONTREXX_CHARSET));
            $objUser->objAttribute->next();
        }
        return true;
    }

    function setCaptcha($useCaptcha)
    {
        global $_ARRAYLANG;

        if (!$this->objTemplate->blockExists('contact_form_captcha')) {
            return;
        }

        if ($useCaptcha) {
            include_once ASCMS_LIBRARY_PATH.'/spamprotection/captcha.class.php';
            $captcha = new Captcha();

            $this->objTemplate->setVariable(array(
                'CONTACT_CAPTCHA_OFFSET'             => $captcha->getOffset(),
                'CONTACT_CAPTCHA_URL'                => $captcha->getUrl(),
                'CONTACT_CAPTCHA_ALT'                => $captcha->getAlt(),
                'TXT_CONTACT_CAPTCHA_DESCRIPTION'    => $_ARRAYLANG['TXT_CONTACT_CAPTCHA_DESCRIPTION'],
                'TXT_CONTACT_CAPTCHA'                => $_ARRAYLANG['TXT_CONTACT_CAPTCHA'],
                'CONTACT_CAPTCHA_ERROR'              => $this->captchaError
            ));

            $this->objTemplate->parse('contact_form_captcha');
        } else {
            $this->objTemplate->hideBlock('contact_form_captcha');
        }
    }

    /**
     * Get data from contact form submit
     *
     * Reads out the data that has been submited by the visitor.
     * @access private
     * @global array
     * @global array
     * @see getContactFormDetails(), getFormFields(), _uploadFiles(),
     * @return mixed An array with the contact details or FALSE if an error occurs
     */
    function _getContactFormData()
    {
        global $_ARRAYLANG, $_CONFIG, $_LANGID;

        if (isset($_POST) && !empty($_POST)) {
            $arrFormData = array();
            $arrFormData['id'] = isset($_GET['cmd']) ? intval($_GET['cmd']) : 0;
            if ($this->getContactFormDetails($arrFormData['id'], $arrFormData['emails'], $arrFormData['subject'], $arrFormData['feedback'], $arrFormData['mailTemplate'], $arrFormData['showForm'], $arrFormData['useCaptcha'], $arrFormData['sendCopy'], $arrFormData['htmlMail'])) {
                $arrFormData['fields'] = $this->getFormFields($arrFormData['id']);
                foreach ($arrFormData['fields'] as $field) {
                    $this->arrFormFields[] = $field['lang'][$_LANGID]['name'];
                }
            } else {
                $arrFormData['id'] = 0;
                $arrFormData['emails'] = explode(',', $_CONFIG['contactFormEmail']);
                $arrFormData['subject'] = $_ARRAYLANG['TXT_CONTACT_FORM']." ".$_CONFIG['domainUrl'];
                $arrFormData['showForm'] = 1;
                //$arrFormData['sendCopy'] = 0;
                $arrFormData['htmlMail'] = 1;
            }
            $arrFormData['uploadedFiles'] = $this->_uploadFiles($arrFormData['fields']);
            
            foreach ($_POST as $key => $value) {
                if (!empty($value) && !in_array($key, array('Submit', 'submitContactForm', 'contactFormCaptcha', 'contactFormCaptchaOffset'))) {
                    $id = intval(substr($key, 17));
                    if (isset($arrFormData['fields'][$id])) {
                        $key = $arrFormData['fields'][$id]['lang'][$_LANGID]['name'];
                    } else {
                        $key = stripslashes(contrexx_strip_tags($key));
                    }
                    if (is_array($value)) {
                        $value = implode(', ', $value);
                    }

                    $arrFormData['data'][$id] = stripslashes(contrexx_strip_tags($value));
                }
            }

            if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]) && !empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
                $arrFormData['meta']['ipaddress'] = $_SERVER["HTTP_X_FORWARDED_FOR"];
            } else {
                $arrFormData['meta']['ipaddress'] = $_SERVER["REMOTE_ADDR"];
            }

            $arrFormData['meta']['time'] = time();
            $arrFormData['meta']['host'] = contrexx_strip_tags(@gethostbyaddr($arrFormData['meta']['ipaddress']));
            $arrFormData['meta']['lang'] = contrexx_strip_tags($_SERVER["HTTP_ACCEPT_LANGUAGE"]);
            $arrFormData['meta']['browser'] = contrexx_strip_tags($_SERVER["HTTP_USER_AGENT"]);
            
            return $arrFormData;
        }
        return false;
    }

    /**
     * Upload submitted files
     *
     * Move all files that are allowed to be uploaded in the folder that
     * has been specified in the configuration option "File upload deposition path"
     * @access private
     * @global array
     * @param array Files that have been submited
     * @see getSettings(), _cleanFileName(), errorMsg, FWSystem::getMaxUploadFileSize()
     * @return array A list of files that have been stored successfully in the system
     */
    function _uploadFiles($arrFields)
    {
        global $_ARRAYLANG, $_LANGID;
        
        $arrSettings = $this->getSettings();

        $arrFiles = array();
        if (isset($_FILES) && is_array($_FILES)) {
            foreach (array_keys($_FILES) as $file) {
                $fileName = !empty($_FILES[$file]['name']) ? $this->_cleanFileName($_FILES[$file]['name']) : '';
                $fileTmpName = !empty($_FILES[$file]['tmp_name']) ? $_FILES[$file]['tmp_name'] : '';

                switch ($_FILES[$file]['error']) {
                    case UPLOAD_ERR_INI_SIZE:
                        //Die hochgeladene Datei überschreitet die in der Anweisung upload_max_filesize in php.ini festgelegte Grösse.
                        include_once ASCMS_FRAMEWORK_PATH.'/System.class.php';
                        $this->errorMsg .= sprintf($_ARRAYLANG['TXT_CONTACT_FILE_SIZE_EXCEEDS_LIMIT'], $fileName, FWSystem::getMaxUploadFileSize()).'<br />';
                        break;

                    case UPLOAD_ERR_FORM_SIZE:
                        //Die hochgeladene Datei überschreitet die in dem HTML Formular mittels der Anweisung MAX_FILE_SIZE angegebene maximale Dateigrösse.
                        $this->errorMsg .= sprintf($_ARRAYLANG['TXT_CONTACT_FILE_TOO_LARGE'], $fileName).'<br />';
                        break;

                    case UPLOAD_ERR_PARTIAL:
                        //Die Datei wurde nur teilweise hochgeladen.
                        $this->errorMsg .= sprintf($_ARRAYLANG['TXT_CONTACT_FILE_CORRUPT'], $fileName).'<br />';
                        break;

                    case UPLOAD_ERR_NO_FILE:
                        //Es wurde keine Datei hochgeladen.
                        continue;
                        break;

                    default:
                        if (!empty($fileTmpName)) {
                            $arrFile = pathinfo($fileName);
                            $i = '';
                            $suffix = '';
                            $filePath = $arrSettings['fileUploadDepositionPath'].'/'.$arrFile['filename'].$suffix.'.'.$arrFile['extension'];
                            while (file_exists(ASCMS_DOCUMENT_ROOT.$filePath)) {
                                $suffix = '-'.++$i;
                                $filePath = $arrSettings['fileUploadDepositionPath'].'/'.$arrFile['filename'].$suffix.'.'.$arrFile['extension'];
                            }
            
                            $arrMatch = array();
                            if (FWValidator::is_file_ending_harmless($fileName)) {
                                if (@move_uploaded_file($fileTmpName, ASCMS_DOCUMENT_ROOT.$filePath)) {
                                    $id = intval(substr($file, 17));
                                    $arrFiles[$id] = array(
                                        'path' => $filePath,
                                        'name' => $fileName
                                    );
                                } else {
                                    $this->errorMsg .= sprintf($_ARRAYLANG['TXT_CONTACT_FILE_UPLOAD_FAILED'], htmlentities($fileName, ENT_QUOTES, CONTREXX_CHARSET)).'<br />';
                                }
                            } else {
                                $this->errorMsg .= sprintf($_ARRAYLANG['TXT_CONTACT_FILE_EXTENSION_NOT_ALLOWED'], htmlentities($fileName, ENT_QUOTES, CONTREXX_CHARSET)).'<br />';
                            }
                        }
                        break;
                }
            }
        }
        
        return $arrFiles;
    }

    /**
    * Format a file name to be safe
    *
    * Replace non valid filename chars with a undercore.
    * @access private
    * @param string $file   The string file name
    * @param int    $maxlen Maximun permited string length
    * @return string Formatted file name
    */
    function _cleanFileName($name, $maxlen=250){
        $noalpha = 'áéíóúàèìòùäëïöüÁÉÍÓÚÀÈÌÒÙÄËÏÖÜâêîôûÂÊÎÔÛñçÇ@';
        $alpha =   'aeiouaeiouaeiouAEIOUAEIOUAEIOUaeiouAEIOUncCa';
        $name = substr ($name, 0, $maxlen);
        $name = strtr($name, $noalpha, $alpha);
        $mixChars = array('Þ' => 'th', 'þ' => 'th', 'Ð' => 'dh', 'ð' => 'dh',
                          'ß' => 'ss', 'Œ' => 'oe', 'œ' => 'oe', 'Æ' => 'ae',
                          'æ' => 'ae', '$' => 's',  '¥' => 'y');
        $name = strtr($name, $mixChars);
        // not permitted chars are replaced with "_"
        return ereg_replace ('[^a-zA-Z0-9,._\+\()\-]', '_', $name);
    }

    /**
     * Workaround for 3-argument-strtr with utf8 characters
     * used like PHP's strtr() with 3 arguments
     * @access private
     * @param string $str where to search
     * @param string $from which chars to look for and...
     * @param string $to ...the chars to replace by
     * @return the strtr()ed result
     */
    function _strtr_utf8($str, $from, $to) {
        if(!isset($to))
        {
            //2-argument call. no need to change anything, just pass to strtr
            return strtr($str, $from);
        }

        $keys = array();
        $values = array();
    
        //let php put all the symbols into an array based on the current charset
        //(which is utf8)
        preg_match_all('/./u', $from, $keys);
        preg_match_all('/./u', $to, $values);
        //create a mapping, so strtr() doesn't get confused with the multi-byte chars
        $mapping = array_combine($keys[0], $values[0]);
        //finally strtr
        return strtr($str, $mapping);
    }

    /**
     * Checks the Values sent trough post
     *
     * Checks the Values sent trough post. Normally this is already done
     * by Javascript, but it could be possible that the client doens't run
     * JS, so this is done here again. Sadly, it is not possible to rewrite
     * the posted values again
     * @access private
     * @global array
     * @param array Submitted field values
     * @see getSettings(), initCheckTypes(), arrCheckTypes, _isSpam(), errorMsg
     * @return boolean Return FALSE if a field's value isn't valid, otherwise TRUE
     */
    function _checkValues($arrFields, $useCaptcha)
    {
        global $_ARRAYLANG, $_LANGID;

        $error = false;
        $arrSettings = $this->getSettings();
        $arrSpamKeywords = explode(',', $arrSettings['spamProtectionWordList']);
        $this->initCheckTypes();
        if (count($arrFields['fields']) > 0) {
            foreach ($arrFields['fields'] as $fieldId => $field) {
                $source = $field['type'] == 'file' ? 'uploadedFiles' : 'data';
                $regex = "#".$this->arrCheckTypes[$field['check_type']]['regex'] ."#";
                $arrFields[$source][$fieldId] = trim($arrFields[$source][$fieldId]);
                if ($field['is_required'] && empty($arrFields[$source][$fieldId])) {
                    $error = true;
                } elseif (empty($arrFields['data'][$fieldId]) && empty($arrFields['uploadedFiles'])) {
                    continue;
                }

                $fieldValue = $arrFields[$source][$fieldId];
                if ($field['type'] == 'file') {
                    // $fieldValue is an array of the form array('path' => file_path, 'name' => 'file_name')
                    $fieldValue = $fieldValue['path'];
                }

                if (!preg_match($regex, $fieldValue)) {
                    $error = true;
                } elseif ($this->_isSpam($fieldValue, $arrSpamKeywords)) {
                    $error = true;
                }
            }
        }

        if ($useCaptcha) {
            include_once ASCMS_LIBRARY_PATH.'/spamprotection/captcha.class.php';
            $captcha = new Captcha();

            if (!$captcha->compare($_POST['contactFormCaptcha'], $_POST['contactFormCaptchaOffset'])) {
                $error = true;
                $this->captchaError = $_ARRAYLANG['TXT_CONTACT_INVALID_CAPTCHA_CODE'];
            }
        }

        if ($error) {
            $this->errorMsg = $_ARRAYLANG['TXT_FEEDBACK_ERROR'].'<br />';
            return false;
        } else {
            return true;
        }
    }

    /**
    * Checks a string for spam keywords
    *
    * This method looks for forbidden words in a string that have been defined
    * in the option "Spam protection word list"
    * @access private
    * @param string String to check for forbidden words
    * @param array Forbidden word list
    * @return boolean Return TRUE if the string contains an forbidden word, otherwise FALSE
    */
    function _isSpam($string, $arrKeywords)
    {
        foreach ($arrKeywords as $keyword) {
            if (!empty($keyword)) {
                if (preg_match("#{$keyword}#i", $string)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Inserts the contact form submit into the database
     *
     * This method does store the request in the database
     * @access private
     * @global ADONewConnection
     * @global array
     * @param array Details of the contact request
     * @see errorMsg
     * @return boolean TRUE on succes, otherwise FALSE
     */
    function _insertIntoDatabase($arrFormData)
    {
        global $objDatabase, $_ARRAYLANG, $_LANGID;

        if (!empty($this->errorMsg)) return false;
        
        $objResult = $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_contact_form_data
                                        (`id_form`, `id_lang`, `time`, `host`, `lang`, `browser`, `ipaddress`)
                                        VALUES
                                        (".$arrFormData['id'].",
                                         ".$_LANGID.",
                                         ".$arrFormData['meta']['time'].",
                                         '".$arrFormData['meta']['host']."',
                                         '".$arrFormData['meta']['lang']."',
                                         '".$arrFormData['meta']['browser']."',
                                         '".$arrFormData['meta']['ipaddress']."')");
        if ($objResult === false) {
            $this->errorMsg .= $_ARRAYLANG['TXT_CONTACT_FAILED_SUBMIT_REQUEST'].'<br />';
            return false;
        }

        $lastInsertId = $objDatabase->insert_id();
        foreach ($arrFormData['fields'] as $key => $arrField) {
            $value = "";

            if ($arrField['type'] == 'file' ) {
                if (isset($arrFormData['uploadedFiles'][$key])) {
                    $value = contrexx_strip_tags(serialize($arrFormData['uploadedFiles'][$key]));
                }
            } else {
                if (isset($arrFormData['data'][$key])) {
                    $value = html_entity_decode(contrexx_addslashes($arrFormData['data'][$key]), ENT_QUOTES, CONTREXX_CHARSET);
                }
            }

            if ($value != "") {
                $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_contact_form_submit_data
                                        (`id_entry`, `id_field`, `formlabel`, `formvalue`)
                                        VALUES
                                        (".$lastInsertId.",
                                         ".$key.",
                                         '".$arrField['lang'][$_LANGID]['name']."',
                                         '".$value."')");
            }
        }
        return true;
    }

    /**
     * Sends an email with the contact details to the responsible persons
     *
     * This methode sends an email to all email addresses that are defined in the
     * option "Receiver address(es)" of the requested contact form.
     * @access private
     * @global array
     * @global array
     * @param array Details of the contact request
     * @see _getEmailAdressOfString(), phpmailer::From, phpmailer::FromName, phpmailer::AddReplyTo(), phpmailer::Subject, phpmailer::IsHTML(), phpmailer::Body, phpmailer::AddAddress(), phpmailer::Send(), phpmailer::ClearAddresses()
     */
    function _sendMail($arrFormData)
    {
        global $_ARRAYLANG, $_CONFIG, $_LANGID;
        
        $body = '';
        $replyAddress = '';
        $htmlMessage  = '';
        $isHtml = $arrFormData['htmlMail'] == 1
                  ? true : false;
        if ($isHtml) {
            /*
             * Generate HTML Code For Mail
             */
            $htmlTemplate = new HTML_Template_Sigma('.');
            $htmlTemplate->setErrorHandling(PEAR_ERROR_DIE);
            $htmlTemplate->setTemplate($arrFormData['mailTemplate']);

            $htmlTemplate->setVariable(array(
                    'DATE'              => date(ASCMS_DATE_FORMAT, $arrFormData['meta']['time']),
                    'HOSTNAME'          => htmlentities($arrFormData['meta']['host'], ENT_QUOTES, CONTREXX_CHARSET),
                    'IP_ADDRESS'        => htmlentities($arrFormData['meta']['ipaddress'], ENT_QUOTES, CONTREXX_CHARSET),
                    'BROWSER_LANGUAGE'  => htmlentities($arrFormData['meta']['lang'], ENT_QUOTES, CONTREXX_CHARSET),
                    'BROWSER_VERSION'   => htmlentities($arrFormData['meta']['browser'], ENT_QUOTES, CONTREXX_CHARSET)
                ));

            /*
             * Fetch Field name and values of the whole form
             */
            if ($htmlTemplate->blockExists('form_field')) {
                foreach ($arrFormData['fields'] as $key => $arrField) {
                    $field_value = '';
                    switch ($arrField['type']) {
                    case 'file':
                        if (isset($arrFormData['uploadedFiles'][$arrField['lang'][$_LANGID]['name']])) {
                            $field_value = "<a href='".ASCMS_PROTOCOL."://".$_CONFIG['domainUrl'].ASCMS_PATH_OFFSET.$arrFormData['uploadedFiles'][$arrField['lang'][$_LANGID]['name']]['path']."' >".$arrFormData['uploadedFiles'][$arrField['lang'][$_LANGID]['name']]['name']."</a>";
                        }
                        break;
                    case 'checkbox':
                        $field_value = ($arrFormData['data'][$arrField['lang'][$_LANGID]['name']] == 1)
                                        ? $_ARRAYLANG['TXT_CONTACT_YES']
                                        : $_ARRAYLANG['TXT_CONTACT_NO'] ;
                        break;
                    default :
                        $field_value = htmlentities($arrFormData['data'][$arrField['lang'][$_LANGID]['name']], ENT_QUOTES, CONTREXX_CHARSET);
                        break;
                    }

                    if ($field_value != "") {
                        $htmlTemplate->setVariable(array(
                            'FIELD_LABEL'   => $arrField['lang'][$_LANGID]['name'],
                            'FIELD_VALUE'   => $field_value
                        ));
                        $htmlTemplate->parse('form_field');
                    }
                }
            }

            /*
             * Block to fetch form field names and values of individual fields
             */
            foreach ($arrFormData['fields'] as $key => $arrField) {
                if ($htmlTemplate->blockExists('field_'.$key)) {
                    $field_value = '';
                    switch ($arrField['type']) {
                    case 'file':
                        if (isset($arrFormData['uploadedFiles'][$arrField['lang'][$_LANGID]['name']])) {
                            $field_value = "<a href='".ASCMS_PROTOCOL."://".$_CONFIG['domainUrl'].ASCMS_PATH_OFFSET.$arrFormData['uploadedFiles'][$arrField['lang'][$_LANGID]['name']]['path']."' >".$arrFormData['uploadedFiles'][$arrField['lang'][$_LANGID]['name']]['name']."</a>";
                        }
                        break;
                    case 'checkbox':
                        $field_value = ($arrFormData['data'][$arrField['lang'][$_LANGID]['name']] == 1)
                                        ? $_ARRAYLANG['TXT_CONTACT_YES']
                                        : $_ARRAYLANG['TXT_CONTACT_NO'] ;
                        break;
                    default :
                        $field_value = htmlentities($arrFormData['data'][$arrField['lang'][$_LANGID]['name']], ENT_QUOTES, CONTREXX_CHARSET);
                        break;
                    }

                    if ($field_value != "") {
                        $htmlTemplate->setVariable(array(
                            'FIELD_'.$key.'_LABEL' => $arrField['lang'][$_LANGID]['name'],
                            'FIELD_'.$key.'_VALUE' => $field_value
                        ));
                        $htmlTemplate->parse('field_'.$key);
                    } else {
                        $htmlTemplate->hideBlock('field_'.$key);
                    }
                }
            }

            $htmlMessage = $htmlTemplate->get();
        }
        
        if (count($arrFormData['uploadedFiles']) > 0) {
            $body .= $_ARRAYLANG['TXT_CONTACT_UPLOADS'].":\n";
            foreach ($arrFormData['uploadedFiles'] as $key => $file) {
                $body .= $key.": ".(contrexx_strip_tags($file['name']))."\n";
            }
            $body .= "\n";
        }

        $arrRecipients = $this->getRecipients(intval($_GET['cmd']));

        if (!empty($arrFormData['data'])) {
            if (!empty($arrFormData['fields'])) {
                foreach ($arrFormData['fields'] as $arrField) {
                    if ($arrField['check_type'] == '2' && ($mail = trim($arrFormData['data'][$arrField['lang'][$_LANGID]['name']]))  && !empty($mail)) {
                        $replyAddress = $mail;
                        break;
                    }
                }
            }

            uksort($arrFormData['data'], array($this, '_sortFormData'));
            foreach ($arrFormData['data'] as $key => $value) {
                $tabCount = ceil((strlen($key)+1) / 6);
                $tabs = 7 - $tabCount;
                if((strlen($key)+1) % 6 == 0){
                    $tabs--;
                }
                if($key == 'contactFormField_recipient'){
                    $key    = $_ARRAYLANG['TXT_CONTACT_RECEIVER_ADDRESSES_SELECTION'];
                    $value  = $arrRecipients[$value]['name'];
                }
                $body .= $key.":".str_repeat("\t", $tabs).$value."\n";
                if (empty($replyAddress) && ($mail = $this->_getEmailAdressOfString($value))) {
                    $replyAddress = $mail;
                }
            }
        }
        
        $arrSettings = $this->getSettings();        
        
        $message  = $_ARRAYLANG['TXT_CONTACT_TRANSFERED_DATA_FROM']." ".$_CONFIG['domainUrl']."\n\n";
        if ($arrSettings['fieldMetaDate']) {
            $message .= $_ARRAYLANG['TXT_CONTACT_DATE']." ".date(ASCMS_DATE_FORMAT, $arrFormData['meta']['time'])."\n\n";
        }
        $message .= $body."\n\n";
        if ($arrSettings['fieldMetaHost']) {
            $message .= $_ARRAYLANG['TXT_CONTACT_HOSTNAME']." : ".$arrFormData['meta']['host']."\n";
        }
        if ($arrSettings['fieldMetaIP']) {
            $message .= $_ARRAYLANG['TXT_CONTACT_IP_ADDRESS']." : ".$arrFormData['meta']['ipaddress']."\n";
        }
        if ($arrSettings['fieldMetaLang']) {
            $message .= $_ARRAYLANG['TXT_CONTACT_BROWSER_LANGUAGE']." : ".$arrFormData['meta']['lang']."\n";
        }
        $message .= $_ARRAYLANG['TXT_CONTACT_BROWSER_VERSION']." : ".$arrFormData['meta']['browser']."\n";

        if (@include_once ASCMS_LIBRARY_PATH.'/phpmailer/class.phpmailer.php') {
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
            $objMail->From = $_CONFIG['coreAdminEmail'];
            $objMail->FromName = $_CONFIG['coreGlobalPageTitle'];
            if (!empty($replyAddress)) {
                $objMail->AddReplyTo($replyAddress);

                if ($arrFormData['sendCopy'] == 1) {
                    $objMail->AddAddress($replyAddress);
                }

            }
            $objMail->Subject = $arrFormData['subject'];

            if ($isHtml) {
                $objMail->Body = $htmlMessage;
                $objMail->AltBody = $message;
            } else {
                $objMail->IsHTML(false);
                $objMail->Body = $message;
            }

            if (count($arrFormData['uploadedFiles']) > 0) {
                foreach ($arrFormData['uploadedFiles'] as $key => $file) {
                    $objMail->AddAttachment(ASCMS_DOCUMENT_ROOT.$file['path'], $file['name']);
                }
            }

            $arrRecipients = $this->getRecipients(intval($_GET['cmd']));
            if (!empty($arrFormData['data']['contactFormField_recipient'])) {
                foreach (explode(',', $arrRecipients[intval($arrFormData['data']['contactFormField_recipient'])]['email']) as $sendTo) {
                     if (!empty($sendTo)) {
                        $objMail->AddAddress($sendTo);
                        $objMail->Send();
                        $objMail->ClearAddresses();
                    }
                }
            } else {
                foreach ($arrFormData['emails'] as $sendTo) {
                    if (!empty($sendTo)) {
                        $objMail->AddAddress($sendTo);
                        $objMail->Send();
                        $objMail->ClearAddresses();
                    }
                }
            }
        }
    }

    /**
     * Sort the form input data
     *
     * Sorts the input data of the form according of the field's order.
     * This method is used as the comparison function of uksort.
     *
     * @param string $a
     * @param string $b
     * @return integer
     */
    function _sortFormData($a, $b)
    {
        if (array_search($a, $this->arrFormFields) < array_search($b, $this->arrFormFields)) {
            return -1;
        } elseif (array_search($a, $this->arrFormFields) > array_search($b, $this->arrFormFields)) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * Searches for a valid e-mail address
     *
     * Returns the first e-mail address that occours in the given string $string
     * @access private
     * @param string $string
     * @return mixed Returns an e-mail addess as string, or a boolean false if there is no valid e-mail address in the given string
     */
    function _getEmailAdressOfString($string)
    {
        $arrMatch = array();
        if (preg_match('/[a-z0-9]+(?:[_\.-][a-z0-9]+)*@[a-z0-9]+(?:[\.-][a-z0-9]+)*\.[a-z]{2,6}/', $string, $arrMatch)) {
            return $arrMatch[0];
        } else {
            return false;
        }
    }

    /**
     * Shows the feedback message
     *
     * This parsed the feedback message and outputs it
     * @access private
     * @param array Details of the requested form
     * @see _getError(), HTML_Template_Sigma::setVariable
     */
    function _showFeedback($arrFormData)
    {
        global $_ARRAYLANG;

        $feedback = $arrFormData['feedback'];

        $arrMatch = array();
        if (isset($arrFormData['fields']) && preg_match_all('#\[\[('.
                implode('|', array_unique(array_merge($this->arrFormFields, array_keys($arrFormData['data']))))
            .')\]\]#',
            html_entity_decode($feedback, ENT_QUOTES, CONTREXX_CHARSET),
            $arrMatch)
        ) {
            foreach ($arrFormData['fields'] as $field) {
                if (in_array($field['name'], $arrMatch[1])) {
                    switch ($field['type']) {
                    case 'checkbox':
                        $value = isset($arrFormData['data'][$field['name']]) ? $_ARRAYLANG['TXT_CONTACT_YES'] : $_ARRAYLANG['TXT_CONTACT_NO'];
                        break;

                    case 'textarea':
                        $value = nl2br(htmlentities((isset($arrFormData['data'][$field['name']]) ? $arrFormData['data'][$field['name']] : ''), ENT_QUOTES, CONTREXX_CHARSET));
                        break;

                    default:
                        $value = htmlentities((isset($arrFormData['data'][$field['name']]) ? $arrFormData['data'][$field['name']] : ''), ENT_QUOTES, CONTREXX_CHARSET);
                        break;
                    }
                    $feedback = str_replace('[['.htmlentities($field['name'], ENT_QUOTES, CONTREXX_CHARSET).']]', $value, $feedback);
                }
            }
        }

        $this->objTemplate->setVariable('CONTACT_FEEDBACK_TEXT', $this->_getError().stripslashes($feedback).'<br /><br />');
    }

    /**
     * Show Error
     *
     * Set the error message
     * @access private
     * @see HTML_Template_Sigma::setVariable(), HTML_Template_Sigma::get()
     * @return string Contact page
     */
    function _showError()
    {
        $this->objTemplate->setVariable('CONTACT_FEEDBACK_TEXT', $this->_getError());
        return $this->objTemplate->get();
    }

    /**
     * Get the error message
     *
     * Returns a formatted string with error messages if there
     * happened any errors
     * @access private
     * @see errorMsg
     * @return string Error messages
     */
    function _getError()
    {
        if (!empty($this->errorMsg)) {
            return '<span style="color:red;">'.$this->errorMsg.'</span>';
        } else {
            return '';
        }
    }

    /**
     * Get request parameters
     *
     * If a form field's value has been set through the http request,
     * then this method will parse the value and will set it in the template
     * @access private
     * @global ADONewConnection
     * @see HTML_Template_Sigma::setVariable
     */
    function _getParams()
    {
        global $objDatabase, $_LANGID;

        $arrFields = array();
        if (isset($_GET['cmd']) && ($formId = intval($_GET['cmd'])) && !empty($formId)) {
            $objFields = $objDatabase->Execute('SELECT `tblField`.`id`, `tblField`.`type`, `tblFieldLang`.`attributes` FROM `'
                                                    .DBPREFIX.'module_contact_form_field` AS tblField LEFT JOIN `'
                                                    .DBPREFIX.'module_contact_form_field_lang` AS tblFieldLang
                                                        ON `tblField`.`id` = `tblFieldLang`.`fieldID`
                                                        WHERE `tblField`.`id_form`='.$formId.'
                                                        AND `tblFieldLang`.`langID`='.$_LANGID);
            if ($objFields !== false) {
                while (!$objFields->EOF) {
                    if ($objFields->fields['type'] == 'recipient') {
                        if (!empty($_GET['contactFormField_recipient'])) {
                            $_POST['contactFormField_recipient'] = $_GET['contactFormField_recipient'];
                        }
                        if (!empty($_POST['contactFormField_recipient'])) {
                            $arrFields['SELECTED_'.$objFields->fields['id'].'_'.$_POST['contactFormField_recipient']] = 'selected="selected"';
                        }
                    }
                    if (!empty($_GET[$objFields->fields['id']])) {
                        if (in_array($objFields->fields['type'], array('select', 'radio'))) {
                            $index                                                      = array_search($_GET['contactFormField_'.$objFields->fields['id']], explode(',' ,$objFields->fields['attributes']));
                            $arrFields['SELECTED_'.$objFields->fields['id'].'_'.$index] = 'selected="selected"';
                        }
                        $arrFields[$objFields->fields['id'].'_VALUE'] = $_GET[$objFields->fields['id']];
                    }
                    if (!empty($_POST['contactFormField_'.$objFields->fields['id']])) {
                        if (in_array($objFields->fields['type'], array('select', 'radio'))) {
                            $index                                                      = array_search($_POST['contactFormField_'.$objFields->fields['id']], explode(',', $objFields->fields['attributes']));
                            $arrFields['SELECTED_'.$objFields->fields['id'].'_'.$index] = 'selected="selected"';
                        }
                        $arrFields[$objFields->fields['id'].'_VALUE'] = contrexx_stripslashes($_POST['contactFormField_'.$objFields->fields['id']]);
                    }
                    $objFields->MoveNext();
                }
                $this->objTemplate->setVariable($arrFields);
            }
        }
    }
}
?>
