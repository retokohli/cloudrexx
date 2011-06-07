<?php
/**
 * Cache
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.1
 * @package     contrexx
 * @subpackage  core_module_cache
 * @todo        Edit PHP DocBlocks!
 */
require_once ASCMS_CORE_MODULE_PATH.'/cache/lib/cacheLib.class.php';
require_once ASCMS_CORE_PATH.'/settings.class.php';

/**
 * Cache
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.1
 * @package     contrexx
 * @subpackage  core_module_cache
 */
class Cache extends cacheLib {
	var $objTpl;
	var $strCacheablePagesFile = 'index.php';
	var $arrSettings = array();

	private $objSettings;

	/**
	 * Constructor
	 *
	 */
	function Cache() {
		$this->__construct();
	}

	/**
	* PHP5 constructor
	*
	* @global 	object	$objTemplate
	* @global	array	$_CORELANG
	*/
	function __construct() {
		global $objTemplate, $_CORELANG;

		$this->objTpl = new HTML_Template_Sigma(ASCMS_CORE_MODULE_PATH.'/cache/template');
        CSRF::add_placeholder($this->objTpl);
		$this->objTpl->setErrorHandling(PEAR_ERROR_DIE);

		$this->arrSettings = $this->getSettings();
		$this->objSettings = new settingsManager();

		if (is_dir(ASCMS_CACHE_PATH)) {
			if (is_writable(ASCMS_CACHE_PATH)) {
				$this->strCachePath = ASCMS_CACHE_PATH.'/';
			} else {
				$objTemplate->SetVariable('CONTENT_STATUS_MESSAGE',$_CORELANG['TXT_CACHE_ERR_NOTWRITABLE'].ASCMS_CACHE_PATH);
			}
		} else {
			$objTemplate->SetVariable('CONTENT_STATUS_MESSAGE',$_CORELANG['TXT_CACHE_ERR_NOTEXIST'].ASCMS_CACHE_PATH);
		}
	}

	/**
	 * Creates an array containing all important cache-settings
	 *
	 * @global 	object	$objDatabase
	 * @return	array	$arrSettings
	 */
	function getSettings() {
		global $objDatabase;

		$arrSettings = array();

		$objResult = $objDatabase->Execute('SELECT	setname,
													setvalue
											FROM	'.DBPREFIX.'settings
											WHERE	setname LIKE "cache%"
										');
		while (!$objResult->EOF) {
					$arrSettings[$objResult->fields['setname']] = $objResult->fields['setvalue'];
					$objResult->MoveNext();
		}

		return $arrSettings;
	}

	/**
	 * Show settings of the module
	 *
	 * @global 	object	$objTemplate
	 * @global 	array	$_CORELANG
	 */
	function showSettings() {
		global $objTemplate, $_CORELANG;

		$this->objTpl->loadTemplateFile('settings.html');
		$this->objTpl->setVariable(array(
			'TXT_TAB1'						=>	$_CORELANG['TXT_SETTINGS'],
			'TXT_TAB2'						=>	$_CORELANG['TXT_CACHE_EMPTY'],
			'TXT_TAB3'						=>	$_CORELANG['TXT_CACHE_STATS'],
			'TXT_SETTINGS_SAVE'				=>	$_CORELANG['TXT_SAVE'],
			'TXT_SETTINGS_ON'				=>	$_CORELANG['TXT_ACTIVATED'],
			'TXT_SETTINGS_OFF'				=>	$_CORELANG['TXT_DEACTIVATED'],
			'TXT_SETTINGS_STATUS'			=>	$_CORELANG['TXT_CACHE_SETTINGS_STATUS'],
			'TXT_SETTINGS_STATUS_HELP'		=>	$_CORELANG['TXT_CACHE_SETTINGS_STATUS_HELP'],
			'TXT_SETTINGS_EXPIRATION'		=>	$_CORELANG['TXT_CACHE_SETTINGS_EXPIRATION'],
			'TXT_SETTINGS_EXPIRATION_HELP'	=>	$_CORELANG['TXT_CACHE_SETTINGS_EXPIRATION_HELP'],
			'TXT_EMPTY_BUTTON'				=>	$_CORELANG['TXT_CACHE_EMPTY'],
			'TXT_EMPTY_DESC'				=>	$_CORELANG['TXT_CACHE_EMPTY_DESC'],
			'TXT_STATS_FILES'				=>	$_CORELANG['TXT_CACHE_STATS_FILES'],
			'TXT_STATS_FOLDERSIZE'			=>	$_CORELANG['TXT_CACHE_STATS_FOLDERSIZE']
		));

		if ($this->objSettings->isWritable()) {
            $this->objTpl->parse('cache_submit_button');
        } else {
            $this->objTpl->hideBlock('cache_submit_button');
            $objTemplate->SetVariable('CONTENT_STATUS_MESSAGE', implode("<br />\n", $this->objSettings->strErrMessage));
        }

		$intFoldersize = 0;
		$intFiles = 0;

		$handleFolder = opendir($this->strCachePath);
		if ($handleFolder) {
			while ($strFile = readdir($handleFolder)) {
			    if ($strFile != '.' && $strFile != '..' && $strFile != $this->strCacheablePagesFile) {

			    	$intFoldersize += filesize($this->strCachePath.$strFile);
			    	++$intFiles;
			    }
			}
			closedir($handleFolder);
		}

		$this->objTpl->setVariable(array(
			'SETTINGS_STATUS_ON'		=>	($this->arrSettings['cacheEnabled'] == 'on') ? 'checked' : '',
			'SETTINGS_STATUS_OFF'		=>	($this->arrSettings['cacheEnabled'] == 'off') ? 'checked' : '',
			'SETTINGS_EXPIRATION'		=>	intval($this->arrSettings['cacheExpiration']),
			'STATS_FILES'				=>	$intFiles,
			'STATS_FOLDERSIZE'			=>	number_format($intFoldersize / 1024,2,'.','\''),
		));

		$objTemplate->setVariable(array(
			'CONTENT_TITLE'		=>	$_CORELANG['TXT_SETTINGS_MENU_CACHE'],
			'ADMIN_CONTENT'		=> $this->objTpl->get()
		));
	}

	/**
	 * Update settings and write them to the database
	 *
	 * @global 	object	$objDatabase
	 * @global 	object	$objTemplate
	 * @global 	array	$_CORELANG
	 */
	function updateSettings() {
		global $objDatabase, $objTemplate, $_CORELANG;

		if (!isset($_POST['frmSettings_Submit'])) {
		    return;
		}

		$strStatus 		= ($_POST['cachingStatus'] == 'on') ? 'on' : 'off';
		$intExpiration 	= intval($_POST['cachingExpiration']);

		$objDatabase->Execute('	UPDATE	'.DBPREFIX.'settings
								SET		setvalue="'.$strStatus.'"
								WHERE	setname="cacheEnabled"
								LIMIT	1
							');

		$objDatabase->Execute('	UPDATE	'.DBPREFIX.'settings
								SET		setvalue="'.$intExpiration.'"
								WHERE	setname="cacheExpiration"
								LIMIT	1
							');

		$this->arrSettings = $this->getSettings();

		$this->objSettings->writeSettingsFile();

		if (!count($this->objSettings->strErrMessage)) {
            $objTemplate->SetVariable('CONTENT_OK_MESSAGE', $_CORELANG['TXT_SETTINGS_UPDATED']);
		} else {
		    $objTemplate->SetVariable('CONTENT_STATUS_MESSAGE', implode("<br />\n", $this->objSettings->strErrMessage));
		}
	}

	/**
	 * Write a file containing an array with all cacheable pages
	 *
	 * @global 	object	$objDatabase
	 */
	function writeCacheablePagesFile() {
		global $objDatabase;

		if (is_writable($this->strCachePath) && $this->arrSettings['cacheEnabled'] == 'on') {
			$handleFile = fopen($this->strCachePath.$this->strCacheablePagesFile,'w+');
			if ($handleFile) {
			//Header & Footer
				$strHeader	= "<?php\n";
				$strHeader .= "/**\n";
				$strHeader .= "* This is a system-generated file. Do not try to edit it manually!\n";
				$strHeader .= "*\n";
				$strHeader .= "*/\n\n";

				$strFooter .= "?>";


				$objResult = $objDatabase->Execute('SELECT		catid
													FROM		'.DBPREFIX.'content_navigation
													WHERE		cachingstatus="1"
													ORDER BY	catid ASC
												');
				if ($objResult->RecordCount() > 0) {
					while (!$objResult->EOF) {
						$strPages .= $objResult->fields['catid'].",";
						$objResult->MoveNext();
					}
					$strPages = substr($strPages,0,strlen($strPages)-1);
				}

				flock($handleFile, LOCK_EX); //semaphore
				@fwrite($handleFile,$strHeader);
				@fwrite($handleFile,"\$_CACHEPAGES = array(");
				@fwrite($handleFile,$strPages);
				@fwrite($handleFile,");\n\n");
				@fwrite($handleFile,$strFooter);
				flock($handleFile, LOCK_UN);

				fclose($handleFile);
			}
		}
	}


	/**
    * Delete all files in cache-folder
    *
    * @global 	object	$objTemplate
    * @global 	array	$_CORELANG
    */
	function deleteAllFiles() {
		global $_CORELANG,$objTemplate;

		$this->_deleteAllFiles();

		$objTemplate->SetVariable('CONTENT_OK_MESSAGE',$_CORELANG['TXT_CACHE_FOLDER_EMPTY']);
	}


	/**
    * Delete all specific file from cache-folder
    *
    * @global 	object	$objDatabase
    */
	function deleteSingleFile($intPageId) {
		global $objDatabase;

		$intPageId = intval($intPageId);
		if ($intPageId > 0) {
			$arrPageContent = array(	'url'		=>	'/index.php?page='.$intPageId,
										'request'	=>	array(	'page' => strval($intPageId))
								);
			$arrFileNames[0] = md5(serialize($arrPageContent));

			$objResult = $objDatabase->Execute('SELECT		id
												FROM		'.DBPREFIX.'languages
												ORDER BY	id ASC
											');
			while (!$objResult->EOF) {
				$arrLanguages[$objResult->fields['id']] = $objResult->fields['id'];
				$objResult->MoveNext();
			}


			$i = 2;
			foreach ($arrLanguages as $intKey1 => $intLangId1) {
				foreach ($arrLanguages as $intKey2 => $intLangId2) {
					unset($arrPageContent);
					$arrPageContent = array('url'		=>	'/index.php?page='.$intPageId,
											'request'	=>	array(	'backendLangId'	=>	$intLangId1,
																	'langId'		=>	$intLangId2,
																	'page' 			=> 	strval($intPageId)

																)
										);
					$arrFileNames[$i] = md5(serialize($arrPageContent));
					$i++;
				}
			}

			foreach ($arrFileNames as $intKey => $strFileName) {
				if (is_file($this->strCachePath.$strFileName)) {
					@unlink($this->strCachePath.$strFileName);
				}
			}
		}
	}
}
?>
