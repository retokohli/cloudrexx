<?php
/**
 * Contact
 *
 * This module handles all HTML FORMs with action tags to the contact section.
 * It sends the contact email(s) and uploads data (optional)
 * Ex. <FORM name="form1" action="index.php?section=contact&cmd=thanks" method="post">
 *
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author      Astalavista Development Team <thun@astalvista.ch>
 * @version     1.1.0
 * @package     contrexx
 * @subpackage  core_module_contact
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once ASCMS_CORE_MODULE_PATH.'/contact/lib/ContactLib.class.php';

/**
 * Contact
 *
 * This module handles all HTML FORMs with action tags to the contact section.
 * It sends the contact email(s) and uploads data (optional)
 * Ex. <FORM name="form1" action="index.php?section=contact&cmd=thanks" method="post">
 *
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author      Astalavista Development Team <thun@astalvista.ch>
 * @version     1.1.0
 * @package     contrexx
 * @subpackage  core_module_contact
 */
class Contact extends ContactLib
{
	/**
	 * File extensions that are allowed to upload
	 *
	 * This array contains all file extensions that are allowed
	 * to be uploaded. If a file's file extensions is not listed
	 * in this array then the contact request will be blocked and
	 * a error message will be return instead.
	 */
    var $enabledUploadFileExtensions = array(
		"txt","doc","xls","pdf","ppt","gif","jpg","png","xml",
		"odt","ott","sxw","stw","dot","rtf","sdw","wpd","jtd",
		"jtt","hwp","wps","ods","ots","sxc","stc","dif","dbf",
		"xlw","xlt","sdc","vor","sdc","cvs","slk","wk1","wks",
		"123","odp","otp","sxi","sti","pps","pot","sxd","sda",
		"sdd","sdp","cgm","odg","otg","sxd","std","dxf","emf",
		"eps","met","pct","sgf","sgv","svm","wmf","bmp","jpeg",
		"jfif","jif","jpe","pbm","pcx","pgm","ppm","psd","ras",
		"tga","tif","tiff","xbm","xpm","pcd","oth","odm","sxg",
		"sgl","odb","odf","sxm","smf","mml","zip","rar"
	);

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

	/**
	 * @ignore
	 */
    function Contact($pageContent)
    {
	    $this->__construct($pageContent);
	}

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
		$this->objTemplate = &new HTML_Template_Sigma('.');
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
    	if (isset($_POST['submitContactForm']) || isset($_POST['Submit'])) {
    		$showThanks = (isset($_GET['cmd']) && $_GET['cmd'] == 'thanks') ? true : false;

	    	$arrFormData = &$this->_getContactFormData();
	    	if ($arrFormData) {
	    		if ($this->_checkValues($arrFormData) && $this->_insertIntoDatabase($arrFormData)) {
		    		$this->_sendMail($arrFormData);
	    		} else {
	    			return $this->_showError();
	    		}

	    		if (!$showThanks) {
	    			$this->_showFeedback($arrFormData);
	    		}
	    	}
    	} else {
    		$this->_getParams();
    	}

    	if ($this->objTemplate->blockExists('contact_form')) {
	    	if (isset($arrFormData['showForm']) && !$arrFormData['showForm']) {
				$this->objTemplate->hideBlock('contact_form');
			} else {
				$this->objTemplate->touchBlock('contact_form');
			}
    	}

    	return $this->objTemplate->get();
    }

    /**
     * Get data from contact form submit
     *
     * Reads out the data that has been submited by the visitor.
     * @access private
     * @global array $_ARRAYLANG
     * @global array $_CONFIG
     * @see getContactFormDetails(), getFormFields(), _uploadFiles(),
     * @return mixed An array with the contact details or FALSE if an error occurs
     */
    function _getContactFormData()
    {
    	global $_ARRAYLANG, $_CONFIG;

		if (isset($_POST) && !empty($_POST)) {
			$arrFormData = array();
			$arrFormData['id'] = isset($_GET['cmd']) ? intval($_GET['cmd']) : 0;
			if ($this->getContactFormDetails($arrFormData['id'], $arrFormData['emails'], $arrFormData['subject'], $arrFormData['feedback'], $arrFormData['showForm'])) {
				$arrFormData['fields'] = $this->getFormFields($arrFormData['id']);
			} else {
				$arrFormData['id'] = 0;
				$arrFormData['emails'] = explode(',', $_CONFIG['contactFormEmail']);
				$arrFormData['subject'] = $_ARRAYLANG['TXT_CONTACT_FORM']." ".$_CONFIG['domainUrl'];
				$arrFormData['showForm'] = 1;
			}
			$arrFormData['uploadedFiles'] = $this->_uploadFiles($arrFormData['fields']);

			foreach ($_POST as $key => $value) {
				if (!empty($value) && $key != 'Submit' && $key != 'submitContactForm') {
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
	 * Upload submitted files
	 *
	 * Move all files that are allowed to be uploaded in the folder that
	 * has been specified in the configuration option "File upload deposition path"
	 * @access private
	 * @global array $_ARRAYLANG
	 * @param array Files that have been submited
	 * @see getSettings(), _cleanFileName(), enabledUploadFileExtensions, errorMsg, FWSystem::getMaxUploadFileSize()
	 * @return array A list of files that have been stored successfully in the system
	 */
	function _uploadFiles($arrFields)
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
						//Die hochgeladene Datei überschreitet die in der Anweisung upload_max_filesize in php.ini festgelegte Größe.
						include_once ASCMS_FRAMEWORK_PATH.'/System.class.php';
						$this->errorMsg .= sprintf($_ARRAYLANG['TXT_CONTACT_FILE_SIZE_EXCEEDS_LIMIT'], $fileName, FWSystem::getMaxUploadFileSize()).'<br />';
						break;

					case UPLOAD_ERR_FORM_SIZE:
						//Die hochgeladene Datei überschreitet die in dem HTML Formular mittels der Anweisung MAX_FILE_SIZE angegebene maximale Dateigröße.
						$this->errorMsg .= sprintf($_ARRAYLANG['TXT_CONTACT_FILE_TOO_LARGE'], $fileName).'<br />';
						break;

					case UPLOAD_ERR_PARTIAL:
						//Die Datei wurde nur teilweise hochgeladen.
						$this->errorMsg .= sprintf($_ARRAYLANG['TXT_CONTACT_FILE_CORRUPT'], $fileName).'<br />';
						break;

					case UPLOAD_ERR_NO_FILE:
						//Es wurde keine Datei hochgeladen.
						$id = intval(substr($file, 17));
						if (isset($arrFields[$id])) {
							$key = $arrFields[$id]['name'];
						} else {
							$key = contrexx_strip_tags($file);
						}
						$arrFiles[$key] = $fileName;
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

							if (preg_match('/\.([a-zA-Z0-9_]{1,4})$/', $fileName, $arrMaches) && in_array(strtolower($arrMaches[1]), $this->enabledUploadFileExtensions)) {
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
	* @param int    $maxlen Maximun permited string lenght
	* @return string Formatted file name
	*/
	function _cleanFileName($name, $maxlen=250){
	    $noalpha = 'áéíóúàèìòùäëïöüÁÉÍÓÚÀÈÌÒÙÄËÏÖÜâêîôûÂÊÎÔÛñçÇ@';
	    $alpha =   'aeiouaeiouaeiouAEIOUAEIOUAEIOUaeiouAEIOUncCa';
	    $name = substr ($name, 0, $maxlen);
	    $name = strtr ($name, $noalpha, $alpha);
	    $mixChars = array('Þ' => 'th', 'þ' => 'th', 'Ð' => 'dh', 'ð' => 'dh',
	                    'ß' => 'ss', 'Œ' => 'oe', 'œ' => 'oe', 'Æ' => 'ae',
	                    'æ' => 'ae', '$' => 's',  '¥' => 'y');
	    $name = strtr($name, $mixChars);
	    // not permitted chars are replaced with "_"
	    return ereg_replace ('[^a-zA-Z0-9,._\+\()\-]', '_', $name);
	}

	/**
	 * Checks the Values sent trough post
	 *
	 * Checks the Values sent trough post. Normally this is already done
	 * by Javascript, but it could be possible that the client doens't run
	 * JS, so this is done here again. Sadly, it is not possible to rewrite
	 * the posted values again
	 * @access private
	 * @global array $_ARRAYLANG
	 * @param array Submitted field values
	 * @see getSettings(), initCheckTypes(), arrCheckTypes, _isSpam(), errorMsg
	 * @return boolean Return FALSE if a field's value isn't valid, otherwise TRUE
	 */
	function _checkValues($arrFields)
	{
		global $_ARRAYLANG;

		$error = false;
		$arrSettings = $this->getSettings();
		$arrSpamKeywords = explode(',', $arrSettings['spamProtectionWordList']);
		$this->initCheckTypes();

		if (count($arrFields['fields']) > 0) {
			foreach ($arrFields['fields'] as $field) {
				$source = $field['type'] == 'file' ? 'uploadedFiles' : 'data';
				$regex = "%".$this->arrCheckTypes[$field['check_type']]['regex'] ."%";
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
		    if (preg_match("%$keyword%i",$string)) {
		        return true;
		    }
		}
		return false;
	}

    /**
     * Inserts the contact form submit into the database
     *
     * This method does store the request in the database
     * @access private
     * @global object $objDatabase
     * @global array $_ARRAYLANG
     * @param array Details of the contact request
     * @see errorMsg
     * @return boolean TRUE on succes, otherwise FALSE
     */
	function _insertIntoDatabase($arrFormData)
	{
		global $objDatabase, $_ARRAYLANG;

		$arrDbEntry = array();

		if(!empty($arrFormData['data'])) {
			foreach ($arrFormData['data'] as $key => $value) {
				array_push($arrDbEntry, base64_encode($key).",".base64_encode($value));
			}
		}

		foreach ($arrFormData['uploadedFiles'] as $key => $file) {
			array_push($arrDbEntry, base64_encode($key).",".base64_encode(contrexx_strip_tags($file)));
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
	 * @global array $_ARRAYLANG
	 * @global array $_CONFIG
	 * @param array Details of the contact request
	 * @see _getEmailAdressOfString(), phpmailer::From, phpmailer::FromName, phpmailer::AddReplyTo(), phpmailer::Subject, phpmailer::IsHTML(), phpmailer::Body, phpmailer::AddAddress(), phpmailer::Send(), phpmailer::ClearAddresses()
	 */
	function _sendMail($arrFormData)
	{
		global $_ARRAYLANG, $_CONFIG;

		$body = '';
		$replyAddress = '';

		if (count($arrFormData['uploadedFiles']) > 0) {
			$body .= $_ARRAYLANG['TXT_CONTACT_UPLOADS'].":\n";
			foreach ($arrFormData['uploadedFiles'] as $key => $file) {
				$body .= $key.": ".ASCMS_PROTOCOL."://".$_CONFIG['domainUrl'].ASCMS_PATH_OFFSET.contrexx_strip_tags($file)."\n";
			}
			$body .= "\n";
		}

		if(!empty($arrFormData['data'])) {
			if (!empty($arrFormData['fields'])) {
				foreach ($arrFormData['fields'] as $arrField) {
					if ($arrField['check_type'] == '2' && ($mail = trim($arrFormData['data'][$arrField['name']]))  && !empty($mail)) {
						$replyAddress = $mail;
						break;
					}
				}
			}

			foreach ($arrFormData['data'] as $key => $value) {
				$body .= $key.": \t\t".$value."\n";
				if (empty($replyAddress) && ($mail = $this->_getEmailAdressOfString($value))) {
					$replyAddress = $mail;
				}
			}
		}

		$message  = $_ARRAYLANG['TXT_CONTACT_TRANSFERED_DATA_FROM']." ".$_CONFIG['domainUrl']."\n\n";
		$message .= $_ARRAYLANG['TXT_CONTACT_DATE']." ".date(ASCMS_DATE_FORMAT, $arrFormData['meta']['time'])."\n\n";
		$message .= $body."\n\n";
		$message .= $_ARRAYLANG['TXT_CONTACT_HOSTNAME']." : ".$arrFormData['meta']['host']."\n";
		$message .= $_ARRAYLANG['TXT_CONTACT_IP_ADDRESS']." : ".$arrFormData['meta']['ipaddress']."\n";
		$message .= $_ARRAYLANG['TXT_CONTACT_BROWSER_LANGUAGE']." : ".$arrFormData['meta']['lang']."\n";
		$message .= $_ARRAYLANG['TXT_CONTACT_BROWSER_VERSION']." : ".$arrFormData['meta']['browser']."\n";

		if (@include_once ASCMS_LIBRARY_PATH.'/phpmailer/class.phpmailer.php') {
			$objMail = new phpmailer();

			if ($_CONFIG['coreSmtpServer'] > 0 && @include_once ASCMS_CORE_PATH.'/SmtpSettings.class.php') {
				$objSmtpSettings = new SmtpSettings();
				if (($arrSmtp = $objSmtpSettings->getSmtpAccount($_CONFIG['coreSmtpServer'])) !== false) {
					$objMail->IsSMTP();
					$objMail->Host = $arrSmtp['hostname'];
					$objMail->Port = $arrSmtp['port'];
					$objMail->SMTPAuth = true;
					$objMail->Username = $arrSmtp['username'];
					$objMail->Password = $arrSmtp['password'];
				}
			}

			$objMail->From = $_CONFIG['coreAdminEmail'];
			$objMail->FromName = $_CONFIG['coreGlobalPageTitle'];
			if (!empty($replyAddress)) {
				$objMail->AddReplyTo($replyAddress);
			}
			$objMail->Subject = $arrFormData['subject'];
			$objMail->IsHTML(false);
			$objMail->Body = $message;

			foreach ($arrFormData['emails'] as $sendTo) {
				if (!empty($sendTo)) {
					$objMail->AddAddress($sendTo);
					$objMail->Send();
					$objMail->ClearAddresses();
				}
			}
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
		if (preg_match('/[a-z0-9]+(?:[_\.-][a-z0-9]+)*@[a-z0-9]+(?:[\.-][a-z0-9]+)*\.[a-z]{2,4}/', $string, $arrMatch)) {
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
		$feedback = $arrFormData['feedback'];

		if (isset($arrFormData['fields'])) {
			foreach ($arrFormData['fields'] as $key => $field) {
				if (isset($_POST['contactFormField_'.$key])) {
					$name = $field['name'];
					$value = contrexx_strip_tags($_POST['contactFormField_'.$key]);

					$feedback = str_replace('[['.$name.']]', $value, $feedback);
				}
			}
		}

		$this->objTemplate->setVariable('CONTACT_FEEDBACK_TEXT', $this->_getError().nl2br(htmlentities(stripslashes($feedback), ENT_QUOTES, CONTREXX_CHARSET)).'<br /><br />');
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
     * @global object $objDatabase
     * @see HTML_Template_Sigma::setVariable
     */
    function _getParams()
    {
    	global $objDatabase;

		$arrFields = array();

		if (isset($_GET['cmd']) && ($formId = intval($_GET['cmd'])) && !empty($formId)) {
			$objFields = $objDatabase->Execute('SELECT `id` FROM `'.DBPREFIX.'module_contact_form_field` WHERE `id_form`='.$formId);
			if ($objFields !== false) {
				while (!$objFields->EOF) {
					if (!empty($_GET[$objFields->fields['id']])) {
						$arrFields[$objFields->fields['id'].'_VALUE'] = $_GET[$objFields->fields['id']];
		    		}
					$objFields->MoveNext();
				}

				$this->objTemplate->setVariable($arrFields);
			}
		}
    }
}
?>
