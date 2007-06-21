<?PHP
/**
* Contact Module
*
* This module handles all HTML FORMs with action tags to the contact section. 
* It sends the contact email(s) and uploads data (optional)
* Ex. <FORM name="form1" action="index.php?section=contact&cmd=thanks" method="post">
*
* @copyright   CONTREXX CMS - Astalavista IT Engineering GmbH Thun
* @author      Comvation Development Team <info@comvation.com>
* @module      contact
* @modulegroup modules
* @version     1.1.0   
*/
require_once ASCMS_CORE_MODULE_PATH.'/contact/lib/ContactLib.class.php';

class Contact extends ContactLib
{
	
    var $enabledUploadFileExtensions = array("txt","doc","xls","pdf","ppt","gif","jpg","png","xml",
                                             "odt","ott","sxw","stw","dot","rtf","sdw","wpd","jtd",
                                             "jtt","hwp","wps","ods","ots","sxc","stc","dif","dbf",
                                             "xlw","xlt","sdc","vor","sdc","cvs","slk","wk1","wks",
                                             "123","odp","otp","sxi","sti","pps","pot","sxd","sda",
                                             "sdd","sdp","cgm","odg","otg","sxd","std","dxf","emf",
                                             "eps","met","pct","sgf","sgv","svm","wmf","bmp","jpeg",
                                             "jfif","jif","jpe","pbm","pcx","pgm","ppm","psd","ras",
                                             "tga","tif","tiff","xbm","xpm","pcd","oth","odm","sxg",
                                             "sgl","odb","odf","sxm","smf","mml","zip","rar"); 
                                             
    
    function contact($pageContent){
	    $this->pageContent = $pageContent;
		$this->objTemplate = &new HTML_Template_Sigma('.');
		$this->objTemplate->setErrorHandling(PEAR_ERROR_DIE);	    
	    $this->objTemplate->setTemplate($this->pageContent, true, true);	      
	}
	

    function getContactPage(){
    	
    	$arrFormData = &$this->_getPostData();
    	if (is_array($arrFormData) && !empty($arrFormData)) {
    		
	    	$this->_insertIntoDatabase($arrFormData);
	    	$this->_sendMail($arrFormData);
    	}
    	
    	$this->showContactPage();
    	return $this->objTemplate->get(); 
    }	
	
    
	function showContactPage(){		
		$this->objTemplate->parseCurrentBlock();
	}

	
	
	function _getPostData()
	{
		global $_ARRAYLANG, $_CONFIG;
		
		// $_POST is never empty!
		if (!isset($_POST) || empty($_POST)) {
			return false;
		}
		
		$arrFormData = array();
		$arrFormData['id'] = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
		$arrFormData['emails'] = $this->getContactFormEmails($arrFormData['id']);
		$arrFormData['fields'] = $this->getFormFields($arrFormData['id']);
		$arrFormData['uploadedFiles'] = $this->_uploadFiles($arrFormData['fields']);
		
		// set standard system contact emails if no form was specified
		if (!isset($this->arrForms[$arrFormData['id']])) {
			$arrFormData['emails'] = explode(',', $_CONFIG['contactFormEmail']);
		}
		
		foreach ($_POST as $key => $value) {
			if (strtolower($key) == "subject") {
				$arrFormData['subject'] = contrexx_strip_tags($value);
			} elseif (strtolower($key) != "submit" && !empty($value)) {
				$id = intval(substr($key, 17));
				if (isset($arrFormData['fields'][$id])) {
					$key = $arrFormData['fields'][$id]['name'];
				} else {
					$key = contrexx_strip_tags($key);
				}
				if (is_array($value)) {
					$value = implode(', ', $value);
				}
				
				$arrFormData['data'][$key] = $value;
			}
		}
		
		if (!isset($arrFormData['subject']) || empty($arrFormData['subject'])) {
		    $arrFormData['subject'] = $_ARRAYLANG['TXT_CONTACT_FORM']." ".$_SERVER['HTTP_HOST'];
		}		
		
		if (getenv("HTTP_X_FORWARDED_FOR")) {
			$arrFormData['meta']['ipaddress'] = @getenv("HTTP_X_FORWARDED_FOR");
		} else {
			$arrFormData['meta']['ipaddress'] = @getenv("REMOTE_ADDR");
		}
		
		$arrFormData['meta']['time'] = time();
		$arrFormData['meta']['host'] = contrexx_strip_tags(@gethostbyaddr($arrFormData['meta']['ipaddress']));
		$arrFormData['meta']['lang'] = contrexx_strip_tags(@getenv("HTTP_ACCEPT_LANGUAGE"));
		$arrFormData['meta']['browser'] = contrexx_strip_tags(@getenv("HTTP_USER_AGENT"));
		
		return $arrFormData;
	}

	
	
	function _insertIntoDatabase(&$arrFormData)
	{
		$arrDbEntry = array();
		
		if(!empty($arrFormData['data'])) {
			foreach ($arrFormData['data'] as $key => $value) {
				array_push($arrDbEntry, base64_encode($key).",".base64_encode(contrexx_strip_tags($value)));
			}
		}
		
		foreach ($arrFormData['uploadedFiles'] as $key => $file) {
			array_push($arrDbEntry, base64_encode($key).",".base64_encode(contrexx_strip_tags($file)));
		}
		
		$message = implode(';', $arrDbEntry);
		$this->_insertIntoDb($arrFormData['id'], $message, $arrFormData['meta']['time'], $arrFormData['meta']['host'], $arrFormData['meta']['lang'], $arrFormData['meta']['browser'], $arrFormData['meta']['ipaddress']);
	}
	
	
	
	
	function _sendMail(&$arrFormData)
	{
		global $_ARRAYLANG, $_CONFIG;
		
		$body = '';
		
		if (count($arrFormData['uploadedFiles']) > 0) {
			$body .= $_ARRAYLANG['TXT_CONTACT_UPLOADS'].":\n";
			foreach ($arrFormData['uploadedFiles'] as $key => $file) {
				$body .= $key.": ".ASCMS_PROTOCOL."://".$_SERVER['HTTP_HOST'].ASCMS_PATH_OFFSET.contrexx_strip_tags($file)."\n";
			}
			$body .= "\n";
		}
		
		
		if(!empty($arrFormData['data'])) {
			foreach ($arrFormData['data'] as $key => $value) {
				$body .= $key.": \t\t".htmlspecialchars(contrexx_strip_tags($value))."\n";
			}
		}
		
		$message  = $_ARRAYLANG['TXT_CONTACT_TRANSFERED_DATA_FROM']." ".$_SERVER['HTTP_HOST']."\n\n";
		$message .= $_ARRAYLANG['TXT_CONTACT_DATE']." ".date(ASCMS_DATE_FORMAT, $arrFormData['meta']['time'])."\n\n";
		$message .= $body."\n\n";
		$message .= $_ARRAYLANG['TXT_CONTACT_HOSTNAME']." : ".$arrFormData['meta']['host']."\n";
		$message .= $_ARRAYLANG['TXT_CONTACT_IP_ADDRESS']." : ".$arrFormData['meta']['ipaddress']."\n";
		$message .= $_ARRAYLANG['TXT_CONTACT_BROWSER_LANGUAGE']." : ".$arrFormData['meta']['lang']."\n";
		$message .= $_ARRAYLANG['TXT_CONTACT_BROWSER_VERSION']." : ".$arrFormData['meta']['browser']."\n";
		
		foreach ($arrFormData['emails'] as $sendTo) {
			@mail($sendTo, $arrFormData['subject'], $message, "From: ".$_CONFIG['coreAdminEmail']."\r\n"."Reply-To: ".$sendTo."\r\n"."X-Mailer: PHP/" . phpversion());
		}
	}
	
	
	
	
	function _insertIntoDb($formId, $message, $time, $host, $lang, $browser, $ipaddress)
	{
		global $objDatabase;
		
		$objDatabase->Execute("INSERT INTO ".DBPREFIX."module_contact_form_data (`id_form`, `time`, `host`, `lang`, `browser`, `ipaddress`, `data`) VALUES (".$formId.", ".$time.", '".$host."', '".$lang."', '".$browser."', '".$ipaddress."', '".$message."')");
	}
	
	
	
	
	/**
	* Format a file name to be safe
	*
	* @param    string $file   The string file name
	* @param    int    $maxlen Maximun permited string lenght
	* @return   string Formatted file name
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
	
	
	
	
	function _uploadFiles($arrFields)
	{
		$arrSettings = &$this->getSettings();
		
		$arrFiles = array();
		if (isset($_FILES) && is_array($_FILES)) {
			foreach (array_keys($_FILES) as $file) {
				$fileName = $this->_cleanFileName($_FILES[$file]['name']);
				$fileTmpName = $_FILES[$file]['tmp_name'];
				
				if (!empty($fileTmpName)) {
					$prefix = '';
					while (file_exists(ASCMS_DOCUMENT_ROOT.$arrSettings['fileUploadDepositionPath'].'/'.$prefix.$fileName)) {
						if (empty($prefix)) {
							$prefix = 0;
						}
						$prefix++;
					}
					
					if (preg_match('/.([a-zA-Z0-9_]{1,4})$/', $fileName, $arrMaches)) {
						if (in_array($arrMaches[1], $this->enabledUploadFileExtensions)) {
							if (move_uploaded_file($fileTmpName, ASCMS_DOCUMENT_ROOT.$arrSettings['fileUploadDepositionPath'].'/'.$prefix.$fileName)) {
								$id = intval(substr($file, 17));
								if (isset($arrFields[$id])) {
									$key = $arrFields[$id]['name'];
								} else {
									$key = contrexx_strip_tags($file);
								}
								$arrFiles[$key] = $arrSettings['fileUploadDepositionPath'].'/'.$prefix.$fileName;
							}
						}
					}
				}
			}
		}
		
		return $arrFiles;
	}
}
?>
