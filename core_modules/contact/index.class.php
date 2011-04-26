<?php
class ContactException extends Exception
{}
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
     * An id unique per form submission and user.
     * This means an user can submit the same form twice at the same time,
     * and the form gets a different submission id for each submit.
     * @var integer
     */
    protected $submissionId = 0;

    /**
     * we're in legacy mode if true.
     * this means file uploads are coming directly from inputs, rather than being
     * handled by the contrexx upload core-module.
     * @var boolean
     */
    protected $legacyMode;

    /**
     * used by @link Contact::_uploadFiles() .
     * remembers the directory made in the first call to _uploadFiles.
     * @var string
     */
    protected $depositionTarget;

    /**
     * Determines whether this has a form fiel.
     * 
     * @var boolean
     */
    protected $hasFileField;

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
        CSRF::add_placeholder($this->objTemplate);
        $this->objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        $this->objTemplate->setTemplate($pageContent);
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
        global $_ARRAYLANG;
        $formId = isset($_GET['cmd']) ? intval($_GET['cmd']) : 0;
        $useCaptcha = $this->getContactFormCaptchaStatus($formId);
        $this->handleUniqueId();

        $this->objTemplate->setVariable(array(
            'TXT_NEW_ENTRY_ERORR'   => $_ARRAYLANG['TXT_NEW_ENTRY_ERORR'],
            'TXT_CONTACT_SUBMIT'    => $_ARRAYLANG['TXT_CONTACT_SUBMIT'],
            'TXT_CONTACT_RESET'     => $_ARRAYLANG['TXT_CONTACT_RESET']
        ));
        
        if (isset($_POST['submitContactForm']) || isset($_POST['Submit'])) {
            $this->checkLegacyMode();

            $showThanks = (isset($_GET['cmd']) && $_GET['cmd'] == 'thanks') ? true : false;
            $this->_getParams();
            $arrFormData =& $this->_getContactFormData();


            if ($arrFormData) {
                if ($this->_checkValues($arrFormData, $useCaptcha) && $this->_insertIntoDatabase($arrFormData)) {
                    $this->_sendMail($arrFormData);
                } else {
                    $this->setCaptcha($useCaptcha);
                    $this->searchFileField($arrFormData);
                    $this->initUploader();
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
            $this->_getParams();
        }

        $this->setCaptcha($useCaptcha);
        if ($this->objTemplate->blockExists('contact_form')) {
            if (isset($arrFormData['showForm']) && !$arrFormData['showForm']) {
                $this->objTemplate->hideBlock('contact_form');
            } else {
                $this->objTemplate->touchBlock('contact_form');
            }
        }

        $this->initUploader();

        return $this->objTemplate->get();
    }

    /**
     * Searches a file field in the array given. Format of array as used everywhere here.
     * @see hasFileField
     * @see _getContactFormData()
     *
     */
    protected function searchFileField($arrFormFields) {
        $this->hasFileField = false;
        foreach($arrFormFields['fields'] as $field) {
            if($field['type'] == 'file') {
                $this->hasFileField = true;
                return;
            }
        }
    }

    /**
     * generates an unique id for each form and user.
     * @see Contact::$submissionId
     */
    protected function handleUniqueId() {
        global $sessionObj;
        if (!isset($sessionObj)) $sessionObj = new cmsSession();
        
        $id = 0;
        if(isset($_REQUEST['unique_id'])) { //an id is specified - we're handling a page reload
            $id = intval($_REQUEST['unique_id']);
        }
        else { //generate a new id
            if(!isset($_SESSION['contact_last_id']))
                $_SESSION['contact_last_id'] = 0;
            $id = ++$_SESSION['contact_last_id'];
        }
        $this->objTemplate->setVariable('CONTACT_UNIQUE_ID', $id);
        $this->submissionId = $id;
    }

    /**
     * Inits the uploader when displaying a contact form.
     */
    protected function initUploader() {
        try {
            //init the uploader       
            JS::activate('cx'); //the uploader needs the framework
            require_once(ASCMS_CORE_MODULE_PATH.'/upload/share/uploadFactory.class.php');
            $f = UploadFactory::getInstance();
        
            //retrieve temporary location for uploaded files
            $tup = self::getTemporaryUploadPath($this->submissionId);
            // TODO: check if $tup[0] === false -> $tup[0] is $sessionObj->getTempPath() 

            //create the folder
            $fm = new File();
            if (!is_dir($tup[0].'/'.$tup[2]) && !$fm->mkdir($tup[0], $tup[1], '/'.$tup[2])) {
                throw new ContactException("Could not create temporary upload directory '".$tup[0].'/'.$tup[2]."'");
            }

            if (!is_writable($tup[0].'/'.$tup[2]) && !$fm->setChmod($tup[0], $tup[1], '/'.$tup[2])) {
                throw new ContactException("Could not chmod temporary upload directory '".$tup[0].'/'.$tup[2]."'");
            }
            //initialize the widget displaying the folder contents
        
            $folderWidget = $f->newFolderWidget($tup[0].'/'.$tup[2]);

            $this->objTemplate->setVariable('UPLOAD_WIDGET_CODE',$folderWidget->getXHtml('#contactFormField_uploadWidget','uploadWidget'));
        
            $uploader = $f->newUploader('exposedCombo');       
            $uploader->setJsInstanceName('exposed_combo_uploader');
            $uploader->setFinishedCallback(array(ASCMS_CORE_MODULE_PATH.'/contact/index.class.php','Contact','uploadFinished'));
            $uploader->setData($this->submissionId);

            $this->objTemplate->setVariable('UPLOADER_CODE',$uploader->getXHtml());
        }
        catch (Exception $e) {
            $this->objTemplate->setVariable('UPLOADER_CODE','<!-- failed initializing uploader, exception '.get_class($e).' with message "'.$e->getMessage().'" -->');            
        }
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
                'CONTACT_CAPTCHA_URL'                => $captcha->getUrl(),
                'TXT_CONTACT_CAPTCHA_DESCRIPTION'    => $_ARRAYLANG['TXT_CONTACT_CAPTCHA_DESCRIPTION'],
                'CONTACT_CAPTCHA_ERROR'                => $this->captchaError
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
        global $_ARRAYLANG, $_CONFIG;

        if (isset($_POST) && !empty($_POST)) {
            $arrFormData = array();
            $arrFormData['id'] = isset($_GET['cmd']) ? intval($_GET['cmd']) : 0;
            if ($this->getContactFormDetails($arrFormData['id'], $arrFormData['emails'], $arrFormData['subject'], $arrFormData['feedback'], $arrFormData['showForm'], $arrFormData['useCaptcha'], $arrFormData['sendCopy'])) {
                $arrFormData['fields'] = $this->getFormFields($arrFormData['id']);

                foreach ($arrFormData['fields'] as $field) {
                    $this->arrFormFields[] = $field['name'];
                }
            } else {
                $arrFormData['id'] = 0;
                $arrFormData['emails'] = explode(',', $_CONFIG['contactFormEmail']);
                $arrFormData['subject'] = $_ARRAYLANG['TXT_CONTACT_FORM']." ".$_CONFIG['domainUrl'];
                $arrFormData['showForm'] = 1;
                //$arrFormData['sendCopy'] = 0;
            }

            $this->searchFileField($arrFormData); //determine whether the form has a file field
            $arrFormData['uploadedFiles'] = $this->_uploadFiles($arrFormData['fields']);

            foreach ($_POST as $key => $value) {
                if (!empty($value) && !in_array($key, array('Submit', 'submitContactForm', 'contactFormCaptcha'))) {
                    $id = intval(substr($key, 17));
                    if (isset($arrFormData['fields'][$id])) {
                        $key = $arrFormData['fields'][$id]['name'];
                    } else {
                        $key = stripslashes(contrexx_strip_tags($key));
                    }
                    if (is_array($value)) {
                        $value = implode(', ', $value);
                    }

                    $arrFormData['data'][$key] = stripslashes(contrexx_strip_tags($value));
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
     * Checks whether this is an old form and sets $this->legacyMode.
     * @see Contact::$legacyMode
     */
    protected function checkLegacyMode() {
        $this->legacyMode = !isset($_REQUEST['unique_id']);
    }
    
    /**
     * Handle uploads
     * @see Contact::_uploadFilesLegacy()
     * @param array $arrFields
     * @param boolean move should the files be moved or 
     *                do we just want an array of filenames?
     *                defaults to false. no effect in legacy mode.
     * @return array A list of files that have been stored successfully in the system
     */
    protected function _uploadFiles($arrFields, $move = false) {
        /* the field unique_id has been introduced with the new uploader.
         * it helps us to tell whether we're handling an form generated
         * before the new uploader using the classic input fields or
         * if we have to treat the files already uploaded by the uploader.
         */
        if(!$this->legacyMode) { //new uploader used
            if(!$this->hasFileField) //nothing to do for us, no files
                return array();
                
            $id = intval($_REQUEST['unique_id']);
            $tup = self::getTemporaryUploadPath($id);
            $tmpUploadDir = $tup[0].'/'.$tup[2].'/'; //all the files uploaded are in here
            $arrFiles = array(); //we'll collect name => path of all files here and return this

            $depositionTarget = ""; //target folder            

            //on the first call, _uploadFiles is called with move=false.
            //this is done in order to get an array of the moved files' names, but
            //the files are left in place.
            //the second call is done with move=true - here we finally move the
            //files.
            //
            //the target folder is created in the first call, because if we can't
            //create the folder, the target path is left pointing at the path
            //specified by $arrSettings['fileUploadDepositionPath'].
            //
            //to remember the target folder for the second call, it is stored in
            //$this->depositionTarget.
            if(!$move) { //first call - create folder
                //determine where formular uploads are stored
                $arrSettings = $this->getSettings();
                $depositionTarget = $arrSettings['fileUploadDepositionPath'].'/';

                //find an unique folder name for the uploaded files
                $folderName = date("Ymd");
                $suffix = "";
                if(file_exists(ASCMS_DOCUMENT_ROOT.$depositionTarget.$folderName)) {
                    $suffix = 1;
                    while(file_exists(ASCMS_DOCUMENT_ROOT.$depositionTarget.$folderName.'-'.$suffix))
                        $suffix++;

                    $suffix = '-'.$suffix;
                }
                $folderName .= $suffix;
                
                //try to make the folder and change target accordingly on success
                if(mkdir(ASCMS_DOCUMENT_ROOT.$depositionTarget.$folderName.'/')) {
                    $depositionTarget .= $folderName.'/';
                }
                $this->depositionTarget = $depositionTarget;
            }
            else //second call - restore remembered target
            {
                $depositionTarget = $this->depositionTarget;
            }

            //move all files
            if(!file_exists($tmpUploadDir))
                throw new ContactException("could not find temporary upload directory '$tmpUploadDir'");

            $h = opendir($tmpUploadDir);
            while(false !== ($f = readdir($h))) {
                if($f != '..' && $f != '.') {
                    //do not overwrite existing files.
                    $prefix = '';
                    while (file_exists(ASCMS_DOCUMENT_ROOT.$depositionTarget.$prefix.$f)) {
                        if (empty($prefix)) {
                            $prefix = 0;
                        }
                        $prefix ++;
                    }
                    
                    if($move)
                        rename($tmpUploadDir.$f,ASCMS_DOCUMENT_ROOT.$depositionTarget.$prefix.$f);
                    $arrFiles[$f] = $depositionTarget.$prefix.$f;
                }                    
            }
            //cleanup
            //todo: this does not work for certain reloads - add cleanup routine
            //@rmdir($tmpUploadDir);
            return $arrFiles;
        }
        else { //legacy function for old uploader
            return $this->_uploadFilesLegacy($arrFields);
        }
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
    function _uploadFilesLegacy($arrFields)
    {
        global $_ARRAYLANG;

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
                            $prefix = '';
                            while (file_exists(ASCMS_DOCUMENT_ROOT.$arrSettings['fileUploadDepositionPath'].'/'.$prefix.$fileName)) {
                                if (empty($prefix)) {
                                    $prefix = 0;
                                }
                                $prefix++;
                            }

                            $arrMatch = array();
                            if (FWValidator::is_file_ending_harmless($fileName)) {
                                if (@move_uploaded_file($fileTmpName, ASCMS_DOCUMENT_ROOT.$arrSettings['fileUploadDepositionPath'].'/'.$prefix.$fileName)) {
                                    $id = intval(substr($file, 17));
                                    if (isset($arrFields[$id])) {
                                        $key = $arrFields[$id]['name'];
                                    } else {
                                        $key = contrexx_strip_tags($file);
                                    }
                                    $arrFiles[$key] = $arrSettings['fileUploadDepositionPath'].'/'.$prefix.$fileName;
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
        $name = $this->_strtr_utf8 ($name, $noalpha, $alpha);
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
        global $_ARRAYLANG;

        $error = false;
        $arrSettings = $this->getSettings();
        $arrSpamKeywords = explode(',', $arrSettings['spamProtectionWordList']);
        $this->initCheckTypes();
        if (count($arrFields['fields']) > 0) {
            foreach ($arrFields['fields'] as $field) {
                $source = $field['type'] == 'file' ? 'uploadedFiles' : 'data';
                $regex = "#".$this->arrCheckTypes[$field['check_type']]['regex'] ."#";
                if ($field['is_required'] && empty($arrFields[$source][$field['name']])) {
                    $error = true;
                } elseif (empty($arrFields[$source][$field['name']])) {
                    continue;
                } elseif(!preg_match($regex, $arrFields[$source][$field['name']])) {
                    $error = true;
                } elseif ($this->_isSpam($arrFields[$source][$field['name']], $arrSpamKeywords)) {
                    $error = true;
                }
            }
        }

        if ($useCaptcha) {
            include_once ASCMS_LIBRARY_PATH.'/spamprotection/captcha.class.php';
            $captcha = new Captcha();

            if (!$captcha->check($_POST['contactFormCaptcha'])) {
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
                if (preg_match("#{$keyword}#i",$string)) {
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
        global $objDatabase, $_ARRAYLANG;
        
        if (!empty($this->errorMsg)) return false;

        $arrDbEntry = array();
        
        //handle files and collect the filenames
        //for legacy mode. this has already been done in the first
        //_uploadFiles() call in getContactPage().
        if(!$this->legacyMode)
            $arrFormData['uploadedFiles'] = $this->_uploadFiles($arrFormData['fields'], true);

        if(!empty($arrFormData['data'])) {
            foreach ($arrFormData['data'] as $key => $value) {
                array_push($arrDbEntry, base64_encode($key).",".base64_encode($value));
            }
        }

        if($this->legacyMode) { //store files according to their inputs name*/
            foreach ($arrFormData['uploadedFiles'] as $key => $file) {
                array_push($arrDbEntry, base64_encode($key).",".base64_encode(contrexx_strip_tags($file)));
            }
        }
        else if(count($arrFormData['uploadedFiles']) > 0) { //assign all files uploaded to the uploader fields name
            $arrTmp = array();
            foreach ($arrFormData['uploadedFiles'] as $key => $file) {
                array_push($arrTmp, $file);
            }
            //a * in front of the file names marks a 'new style' entry
            $files = '*'.implode('*', $arrTmp);
            //find the file field's name
            $fileFieldName = null;
            foreach($arrFormData['fields'] as $field) {
                if($field['type'] == 'file') {
                    $fileFieldName = $field['name'];
                    break;
                }
            }
            if($fileFieldName === null)
                throw new ContactException('could not find file field for form with id ' . $arrFormData['id']);

            array_push($arrDbEntry, base64_encode($fileFieldName).','.base64_encode($files));
        }
        
        $message = implode(';', $arrDbEntry);

        if ($objDatabase->Execute("INSERT INTO ".DBPREFIX."module_contact_form_data (`id_form`, `time`, `host`, `lang`, `browser`, `ipaddress`, `data`) VALUES (".$arrFormData['id'].", ".$arrFormData['meta']['time'].", '".$arrFormData['meta']['host']."', '".$arrFormData['meta']['lang']."', '".$arrFormData['meta']['browser']."', '".$arrFormData['meta']['ipaddress']."', '".$message."')") !== false) {
            return true;
        } else {
            $this->errorMsg .= $_ARRAYLANG['TXT_CONTACT_FAILED_SUBMIT_REQUEST'].'<br />';
            return false;
        }
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
        global $_ARRAYLANG, $_CONFIG, $objDatabase;

        $body = '';
        $replyAddress = '';

        if (count($arrFormData['uploadedFiles']) > 0) {
            $body .= $_ARRAYLANG['TXT_CONTACT_UPLOADS'].":\n";
            foreach ($arrFormData['uploadedFiles'] as $key => $file) {
                $body .= $key.": ".ASCMS_PROTOCOL."://".$_CONFIG['domainUrl'].ASCMS_PATH_OFFSET.contrexx_strip_tags($file)."\n";
            }
            $body .= "\n";
        }

        $arrRecipients = $this->getRecipients(intval($_GET['cmd']));

        if(!empty($arrFormData['data'])) {
            if (!empty($arrFormData['fields'])) {
                foreach ($arrFormData['fields'] as $arrField) {
                    if ($arrField['check_type'] == '2' && ($mail = trim($arrFormData['data'][$arrField['name']]))  && !empty($mail)) {
                        $replyAddress = $mail;
                        break;
                    }
                }
            }

            //we need to know all textareas - they're indented differently
            $query = 'SELECT name FROM '.DBPREFIX.'module_contact_form_field WHERE type=\'textarea\' AND id_form = ' . intval($arrFormData['id']);

            $result = $objDatabase->Execute($query);
            $textAreaKeys = array();
            while(!$result->EOF) {
                $textAreaKeys[] = $result->fields['name'];
                $result->MoveNext();
            }

            uksort($arrFormData['data'], array($this, '_sortFormData'));
            foreach ($arrFormData['data'] as $key => $value) {
                if($key == 'contactFormField_recipient'){
                    $key    = $_ARRAYLANG['TXT_CONTACT_RECEIVER_ADDRESSES_SELECTION'];
                    $value  = $arrRecipients[$value]['name'];
                }
                if($key == 'unique_id') //generated for uploader. no interesting mail content.
                    continue;

                if(!in_array($key, $textAreaKeys)) { //it's no textarea, indent normally
                    $spaces = 30-strlen($key);
                    $body .= $key.":".str_repeat(" ", $spaces).$value."\n";
                }
                else { //we're dealing with a textearea
                    $body .= $key.":\n".$value."\n";
                }

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
        if ($arrSettings['fieldMetaIP'])   {
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
            $objMail->IsHTML(false);
            $objMail->Body = $message;
            $arrRecipients = $this->getRecipients(intval($_GET['cmd']));
            if(!empty($arrFormData['data']['contactFormField_recipient'])){
                foreach (explode(',', $arrRecipients[intval($arrFormData['data']['contactFormField_recipient'])]['email']) as $sendTo) {
                	 if (!empty($sendTo)) {
                        $objMail->AddAddress($sendTo);
                        $objMail->Send();
                        $objMail->ClearAddresses();
                    }
                }
            }else{
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
        if (isset($arrFormData['fields']) && preg_match_all(
            '#\[\[('.
                implode(
                    '|',
                    array_unique(
                        array_merge(
                            $this->arrFormFields,
                            array_keys($arrFormData['data'])
                        )
                    )
                )
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
        global $objDatabase;

        $arrFields = array();
        if (isset($_GET['cmd']) && ($formId = intval($_GET['cmd']))) {
            $objFields = $objDatabase->Execute('SELECT `id`, `type`, `attributes` FROM `'.DBPREFIX.'module_contact_form_field` WHERE `id_form`='.$formId);
            if ($objFields !== false) {
                while (!$objFields->EOF) {
                    if($objFields->fields['type'] == 'recipient'){
                        if(!empty($_GET['contactFormField_recipient'])){
                            $_POST['contactFormField_recipient'] = $_GET['contactFormField_recipient'];
                        }
                        if(!empty($_POST['contactFormField_recipient'])){
                            $arrFields['SELECTED_'.$objFields->fields['id'].'_'.$_POST['contactFormField_recipient']] = 'selected="selected"';
                        }
                    }
                    if (!empty($_GET[$objFields->fields['id']])) {
                        if(in_array($objFields->fields['type'], array('select', 'radio'))){
                            $index = array_search($_GET['contactFormField_'.$objFields->fields['id']], explode(',' ,$objFields->fields['attributes']));
                            $arrFields['SELECTED_'.$objFields->fields['id'].'_'.$index] = 'selected="selected"';
                        }
                        $arrFields[$objFields->fields['id'].'_VALUE'] = $_GET[$objFields->fields['id']];
                    }
                    if(!empty($_POST['contactFormField_'.$objFields->fields['id']])){
                        if(in_array($objFields->fields['type'], array('select', 'radio'))){
                            $index = array_search($_POST['contactFormField_'.$objFields->fields['id']], explode(',' ,$objFields->fields['attributes']));
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

    /**
     * Gets the temporary upload location for files.
     * @param integer $submissionId
     * @return array('path','webpath', 'dirname')
     */
    protected static function getTemporaryUploadPath($submissionId) {
        global $sessionObj;

        if (!isset($sessionObj)) $sessionObj = new cmsSession();

        $dirname = 'contact_files_'.$submissionId;
        $result = array(
            $sessionObj->getTempPath(),
            $sessionObj->getWebTempPath(),
            $dirname
        );
        return $result;
    }

    //Uploader callback
    public static function uploadFinished($tempPath, $tempWebPath, $data, $uploadId) {
        //todo: remove files with bad file extensions
        //todo: rename files
        $tup = self::getTemporaryUploadPath($data);
        return array($tup[0].'/'.$tup[2],$tup[1].'/'.$tup[2]);
    }
}
?>
