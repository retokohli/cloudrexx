<?php
/**
 * Development library
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author		Comvation Development Team <info@comvation.com>            
 * @version		1.0.0
 * @package     contrexx
 * @subpackage  core_module_development
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Development library
 *
 * development library class
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author		Comvation Development Team <info@comvation.com>
 * @access		private
 * @version		1.0.0
 * @package     contrexx
 * @subpackage  core_module_development
 */
class DevelopmentLibrary
{
	/**
	* Modules array
	*
	* @access private
	* @var array
	*/
	var $_arrModules = null;
	
	/**
	* Areas array
	*
	* @access private
	* @var array
	*/
	var $_arrAreas = null;
	
	/**
	* Error messages
	*
	* @access private
	* @var array
	*/
	var $_arrErrorMessages = array();
	
	/**
	* Get modules
	*
	* Return an array of the modules
	*
	* @access private
	* @global object $objDatabase;
	* @return array array of the modules
	*/
	function _getModules()
	{
		global $objDatabase;
		
		if (!isset($this->_arrModules)) {
			$objModule = $objDatabase->Execute("SELECT id, name, description_variable, status, is_required, is_core FROM ".DBPREFIX."modules WHERE name != ''");
			if ($objModule !== false) {
				while (!$objModule->EOF) {
					$this->_arrModules[$objModule->fields['id']] = array(
						'name'					=> $objModule->fields['name'],
						'description_variable'	=> $objModule->fields['description_variable'],
						'status'				=> $objModule->fields['status'],
						'is_required'			=> $objModule->fields['is_required'],
						'is_core'				=> $objModule->fields['is_core']
					);
					$objModule->MoveNext();
				}
			}
		}
		return $this->_arrModules;
	}
	
	/**
	* Get language variable
	*
	* Return the content and status of a language variable
	*
	* @access private
	* @param string $variable
	* @global object $objDatabase
	* @global object $objLanguage
	* @return mixed array on success, false on failure
	*/
	function _getLanguageVariable($variable)
	{
		global $objDatabase, $objLanguage;
		
		$arrLanguages = &$objLanguage->getLanguageArray();
		$arrVariable = array();
		
		$objVariable = $objDatabase->Execute("SELECT tblNames.id AS id, tblContent.content AS content, tblContent.status AS status, tblContent.lang_id AS lang_id
			FROM ".DBPREFIX."language_variable_names AS tblNames, ".DBPREFIX."language_variable_content AS tblContent
			WHERE tblNames.name='".$variable."' AND tblNames.id=tblContent.varid");
		if ($objVariable !== false) {
			if ($objVariable->RecordCount()>0) {
				while (!$objVariable->EOF) {
					if (isset($arrLanguages[$objVariable->fields['lang_id']])) {
						$arrVariable[$objVariable->fields['id']][$objVariable->fields['lang_id']] = array(
							'content'	=> $objVariable->fields['content'],
							'status'	=> $objVariable->fields['status']
						);
					}
					$objVariable->MoveNext();
				}
			} else {
				return false;
			}
		}
		
		return $arrVariable;
	}
	
	/**
	* Update module
	*
	* Update the attributes of a module
	*
	* @access private
	* @param integer $moduleId
	* @param string $name
	* @param array $arrDescription
	* @param boolean $isCore
	* @param boolean $isRequired
	* @param boolean $status
	* @global object $objDatabase
	* @return boolean true on success, false on failure
	*/
	function _updateModule($moduleId, $name, $arrDescription, $isCore, $isRequired, $status)
	{
		global $objDatabase;
		
		if ($this->_isUniqueModuleName($name, $moduleId)) {
			if ($objDatabase->Execute("UPDATE ".DBPREFIX."modules SET name='".$name."', status='".$status."', is_required=".$isRequired.", is_core=".$isCore." WHERE id=".$moduleId) === false) {
				array_push($this->_arrErrorMessages, 'Die Eigenschaften des Modules konnten nicht aktualisiert werden');
				return false;
			} else {
				$arrModules = &$this->_getModules();
				$moduleDescriptionVariable = isset($arrModules[$moduleId]) ? $arrModules[$moduleId]['description_variable'] : '';
				$languageVariableName = 'TXT_'.strtoupper($name).'_MODULE_DESCRIPTION';
				
				if ($moduleDescriptionVariable != $languageVariableName) {
					if (!$this->_isUniqueLanguageVariableName($languageVariableName)) {
						return false;
					}
				}
				
				$languageVariableId = &$this->_getLanguageVariable($languageVariableName);
				$languageVariableId = is_array($languageVariableId) ? key($languageVariableId) : 0;
				if ($languageVariableId > 0) {
					return $this->_updateLanguageVariable($languageVariableId, $languageVariableName, $arrDescription);
				} else {
					foreach ($arrModules as $moduleId => $arrModule) {
						if ($arrModule['name'] == 'core') {
							break;
						}
					}
					return $this->_addLanguageVariable($languageVariableName, $moduleId, true, false, $arrDescription);
				}
			}
		} else {
			return false;
		}
	}
	
	/**
	* Add module
	*
	* Add a new module
	*
	* @access private
	* @param string $name
	* @param array $arrDescription
	* @param boolean $isCore
	* @param boolean $isRequired
	* @param boolean $status
	* @global object $objDatabase
	* @return boolean true on success, false on failure
	*/
	function _addModule($name, $arrDescription, $isCore, $isRequired, $status) {
		global $objDatabase;
		
		if ($this->_isUniqueModuleName($name)) {
			$languageVariableName = 'TXT_'.strtoupper($name).'_MODULE_DESCRIPTION';
			$nr = 1;
			while (!$this->_isUniqueLanguageVariableName($languageVariableName)) {
				$languageVariableName = 'TXT_'.strtoupper($name).'_MODULE_DESCRIPTION'.$nr;
				$nr++;
			}
			
			
			if ($objDatabase->Execute("INSERT INTO ".DBPREFIX."modules (
				`name`,
				`description_variable`,
				`status`,
				`is_required`,
				`is_core`
				) VALUES (
				'".$name."',
				'".$languageVariableName."',
				".$status.",
				".$isRequired.",
				".$isCore.")") === false) {
				array_push($this->_arrErrorMessages, 'Das Modul konnten nicht hinzugefügt werden!');
				return false;
			} else {
				$arrModules = &$this->_getModules();
				
				foreach ($arrModules as $moduleId => $arrModule) {
					if ($arrModule['name'] == 'core') {
						break;
					}
				}
				return $this->_addLanguageVariable($languageVariableName, $moduleId, true, false, $arrDescription);
			}
		} else {
			return false;
		}
	}
	
	/**
	* Delete module
	*
	* Delete a module from the system
	*
	* @access private
	* @param integer $moduleId
	* @global object $objDatabase
	*/
	function _deleteModule($moduleId)
	{
		global $objDatabase;
		
		if ($objDatabase->Execute("DELETE FROM ".DBPREFIX."modules WHERE id=".$moduleId) !== false) {
			if ($this->_deleteBackendAreasOfModule($moduleId) && $this->_deleteLanguageVariablesOfModule($moduleId)) {
				return true;
			} else {
				return false;
			}
		} else {
			array_push($this->_arrErrorMessages, 'Das Modul konnte nicht gelöscht werden!');
			return false;
		}
	}
	
	/**
	* Delete backend areas of module
	*
	* Delete all backend areas of a module
	*
	* @access private
	* @param integer $moduleId
	* @global object $objDatabase
	* @return boolean true on success, false on failure
	*/
	function _deleteBackendAreasOfModule($moduleId)
	{
		global $objDatabase;
		
		if ($objDatabase->Execute("DELETE FROM ".DBPREFIX."backend_areas WHERE module_id=".$moduleId) !== false) {
			return true;
		} else {
			array_push($this->_arrErrorMessages, 'Konnte die administrations Bereiche nicht löschen');
			return false;
		}
	}
	
	function _deleteBackendArea()
	{
		global $objDatabase;
		
		
	}
	
	/**
	* Delete language variables of module
	*
	* Delete all language variables of a module
	*
	* @access private
	* @param integer $moduleId
	* @global object $objDatabase
	* @return boolean true on success, false on failure
	*/
	function _deleteLanguageVariablesOfModule($moduleId)
	{
		global $objDatabase;
		
		if ($objDatabase->Execute("DELETE tblNames, tblContent
				FROM ".DBPREFIX."language_variable_names AS tblNames,
					 ".DBPREFIX."language_variable_content AS tblContent
				WHERE tblNames.module_id=".$moduleId." AND tblContent.varid=tblNames.id") !== false) {
			return true;
		} else {
			array_push($this->_arrErrorMessages, 'Konnte die Sprachvariablen nicht löschen!');
			return false;
		}
	}
	
	
	/**
	* Is unique module name
	*
	* Check if the module name is unique
	*
	* @access private
	* @param string $name
	* @param integer $moduleId
	* @global object $objDatabase
	* @return boolean true on uniqueness, otherwise false
	*/
	function _isUniqueModuleName($name, $moduleId = 0)
	{
		global $objDatabase;
		
		if ($objDatabase->SelectLimit("SELECT id FROM ".DBPREFIX."modules WHERE name='".$name."' AND id!=".$moduleId, 1) !== false) {
			if ($objDatabase->Affected_Rows() == 0) {
				return true;
			} else {
				return false;
			}
		} else {
			array_push($this->_arrErrorMessages, 'Es konnte nicht überprüft werden, ob der Modulname eindeutig ist!');
			return false;
		}
	}
	
	/**
	* Is unique language variable name
	*
	* Check if the language variable name is unique
	*
	* @access private
	* @param string $name
	* @param integer $variableId
	* @global object $objDatabase
	* @return boolean true on uniqueness, otherwise false
	*/
	function _isUniqueLanguageVariableName($name, $variableId = 0)
	{
		global $objDatabase;
		
		if ($objDatabase->SelectLimit("SELECT id FROM ".DBPREFIX."language_variable_names WHERE name='".$name."' AND id!=".$variableId, 1) !== false) {
			if ($objDatabase->Affected_Rows() == 0) {
				return true;
			} else {
				return false;
			}
		} else {
			array_push($this->_arrErrorMessages, 'Es konnte nicht überprüft werden, ob der Sprachvariable Name eindeutig ist!');
			return false;
		}
	}
	
	/**
	* Update language variable
	*
	* Update the content and status of a language variable
	*
	* @access private
	* @param integer $id
	* @param string $name
	* @param array $arrLanguages
	* @global object $objDatabase
	* @global object $objLanguage
	* @return boolean true on success, false on failure
	*/
	function _updateLanguageVariable($id, $name, $arrLanguages)
	{
		global $objDatabase, $objLanguage;
		
		$status = true;
		
		if ($objDatabase->Execute("UPDATE ".DBPREFIX."language_variable_names SET name='".$name."' WHERE id=".$id) !== false) {
			foreach ($arrLanguages as $langId => $arrLanguage) {
				if ($objDatabase->Execute("UPDATE ".DBPREFIX."language_variable_content SET content='".$arrLanguage['content']."', status=".$arrLanguage['status']." WHERE varid=".$id." AND lang_id=".$langId) === false) {
					$languageName = &$objLanguage->getLanguageParameter($langId, 'name');
					$status = false;
					array_push($this->_arrErrorMessages, str_replace('%LANGUAGE%', $languageName ? $languageName : 'Unbekannt', 'Die Sprachvariable konnte für die Sprache %LANGUAGE% nicht aktualisiert werden!'));
				}
			}
		} else {
			array_push($this->_arrErrorMessages, 'Die Sprachvariable konnte nicht aktualisiert werden!');
			return false;
		}
		
		$this->_createLanguageFiles();
		return $status;
	}
	
	/**
	* Add language variable
	*
	* Add a new language variable
	*
	* @access private
	* @param string $name
	* @param integer $moduleId
	* @param boolean $backendStatus
	* @param boolean $frontendStatus
	* @param array $arrLanguages
	* @global object $objDatabase
	* @global object $objLanguage
	* @return boolean true on success, false on failure
	*/
	function _addLanguageVariable($name, $moduleId, $backendStatus, $frontendStatus, $arrLanguages)
	{
		global $objDatabase, $objLanguage;
		
		$status = true;
		
		if ($objDatabase->Execute("INSERT INTO ".DBPREFIX."language_variable_names (`name`, `module_id`, `backend`, `frontend`) VALUES ('".$name."', ".$moduleId.", ".$backendStatus.", ".$frontendStatus.")") !== false) {
			$varId = $objDatabase->Insert_ID();
			
			foreach ($arrLanguages as $langId => $arrLanguage) {
				if ($objDatabase->Execute("INSERT INTO ".DBPREFIX."language_variable_content (`varid`, `content`, `lang_id`, `status`) VALUES (".$varId.", '".$arrLanguage['content']."', ".$langId.", ".$arrLanguage['status'].")") === false) {
					$languageName = &$objLanguage->getLanguageParameter($langId, 'name');
					$status = false;
					array_push($this->_arrErrorMessages, str_replace('%LANGUAGE%', $languageName ? $languageName : 'Unbekannt', 'Die Sprachvariable konnte für die Sprache %LANGUAGE% nicht hinzugefügt werden!'));
				}
			}
		} else {
			array_push($this->_arrErrorMessages, 'Die Sprachvariable konnte nicht hinzugefügt werden!');
			return false;
		}
		
		$this->_createLanguageFiles();
		return $status;
	}
	
	/**
    * Create language files
    *
    * Create the language files based on the language variables in the database
    *
    * @access private
	* @global	string		$objDatabase
	* @global	array		$_CORELANG
    */
    function _createLanguageFiles()
    {
    	global $objDatabase, $_CORELANG;
    	
    	require_once ASCMS_FRAMEWORK_PATH.'/File.class.php';
    	
    	$arrModules = array();
    	$arrLanguages = array();
    	$arrModulesPath = array();
    	$arrModuleVariables = array();
    	$arrErrorFiles = array();
    	$objFile = new File();
    	
    	$strHeader = "/**\n* Contrexx CMS\n* generated date ".date('r',time())."\n**/\n\n";
    	
    	// generate the arrays $arrModulesPath and $arrModules
		$query = "SELECT id, name, is_core FROM ".DBPREFIX."modules";
		$objResult = $objDatabase->Execute($query);
		if ($objResult !== false) {
			while (!$objResult->EOF) {
				if(strlen($objResult->fields['name'])>0){
					switch($objResult->fields['name']){
						case 'core':
							$arrModulesPath[$objResult->fields['name']]['sys'] = ASCMS_DOCUMENT_ROOT;
							$arrModulesPath[$objResult->fields['name']]['web'] = ASCMS_PATH_OFFSET;
							break;
						case 'media1':
							$arrModulesPath['media']['sys'] = ASCMS_CORE_MODULE_PATH.'/media';
							$arrModulesPath['media']['web'] = ASCMS_CORE_MODULE_WEB_PATH.'/media';
							$objResult->fields['name'] = 'media';
							break;
						case 'media2':
						case 'media3':
							$objResult->fields['name'] = "";
							break;
						default:
						$arrModulesPath[$objResult->fields['name']]['sys'] = ($objResult->fields['is_core'] == 1 ? ASCMS_CORE_MODULE_PATH : ASCMS_MODULE_PATH).'/'.$objResult->fields['name'];
						$arrModulesPath[$objResult->fields['name']]['web'] = ($objResult->fields['is_core'] == 1 ? ASCMS_CORE_MODULE_WEB_PATH : ASCMS_MODULE_WEB_PATH).'/'.$objResult->fields['name'];
					}
					if (!empty($objResult->fields['name'])) {
						$arrModulesPath[$objResult->fields['name']]['sys'] .= '/lang/';
						$arrModulesPath[$objResult->fields['name']]['web'] .= '/lang/';
					}
				}
				$arrModules[$objResult->fields['id']] = array(
					'id'	=>	$objResult->fields['id'],
					'name'	=>	$objResult->fields['name']
    			);
    			$objResult->MoveNext();
			}
		}
		
    	// get language array
    	$query = "SELECT id, lang FROM ".DBPREFIX."languages";
    	$objResult = $objDatabase->Execute($query);
    	if ($objResult !== false) {
    		while (!$objResult->EOF) {
    			$arrLanguages[$objResult->fields['id']] = array(
					'id'	=> $objResult->fields['id'],
					'lang'	=> $objResult->fields['lang']
				);
				$objResult->MoveNext();
    		}
    	}
    	
    	// get language variables
    	$query = "SELECT vn.name, vn.module_id, vn.backend, vn.frontend, vc.content, vc.lang_id
    				FROM ".DBPREFIX."language_variable_names AS vn,
    					 ".DBPREFIX."language_variable_content AS vc
				   WHERE vn.id=vc.varid";
    	
    	// generate array $arrModuleVariables including the variables
    	$objResult = $objDatabase->Execute($query);
    	if ($objResult !== false) {
    		while (!$objResult->EOF) {
				if($objResult->fields['module_id'] == 0){
					$moduleId = 1;
				} else {
					$moduleId = $objResult->fields['module_id'];
				}
				if($objResult->fields['backend'] == 1){
					$arrModuleVariables[$moduleId][$objResult->fields['lang_id']]['backend'][$objResult->fields['name']] = $objResult->fields['content'];
				}
				if($objResult->fields['frontend'] == 1){
					$arrModuleVariables[$moduleId][$objResult->fields['lang_id']]['frontend'][$objResult->fields['name']] = $objResult->fields['content'];
				}
				$objResult->MoveNext();
    		}
    	}
    	// generate array $arrOutput with the data to write into files
    	foreach ($arrModuleVariables as $moduleId => $arrLanguageVariables){
    		if (!isset($arrModules[$moduleId])) {
    			continue;
    		}
    		foreach ($arrLanguageVariables as $langId => $arrModeVariables){
    			$filePath = $arrModulesPath[$arrModules[$moduleId]['name']]['sys'].$arrLanguages[$langId]['lang'].'/';
    			$webFilePath = $arrModulesPath[$arrModules[$moduleId]['name']]['web'].$arrLanguages[$langId]['lang'].'/';
    			foreach ($arrModeVariables as $strMode => $arrVariables){
    				$fileName = $strMode.".php";
    				$arrOutput[$filePath.$fileName]['filename'] = $fileName;
    				$arrOutput[$filePath.$fileName]['path'] = $filePath;
    				$arrOutput[$filePath.$fileName]['webpath'] = $webFilePath;
    				foreach ($arrVariables as $strName => $strContent){
    					$strContent = stripslashes($strContent);
    					$strContent = str_replace("\"", "\\\"", $strContent);
    					$arrOutput[$filePath.$fileName]['content'] .= "$"."_ARRAYLANG['".$strName."'] = \"".$strContent."\";\n";
    				}
    			}
    		}
    	}
    	unset($arrModuleVariables);
    	
    	// write variables to files
    	foreach ($arrOutput as $file => $strOutput){
    		//$objFile->setChmod($strOutput['path'], $strOutput['webpath'], $strOutput['filename']);
    		$fileHandle = @fopen($file,"w");
			if($fileHandle)
			{
				@fwrite($fileHandle,"<?php\n".$strHeader.$strOutput['content']."?>");
				@fclose($fileHandle);
			} else {
				array_push($arrErrorFiles,$file);
			}
    	}
    	
    	unset($arrOutput);
    	if(count($arrErrorFiles)>0){
    		foreach ($arrErrorFiles as $file){
    			array_push($this->_arrErrorMessages, $_CORELANG['TXT_COULD_NOT_WRITE_TO_FILE']." (".$file.")");
    		}
    	} else {
    		array_push($this->_arrErrorMessages, $_CORELANG['TXT_SUCCESSFULLY_EXPORTED_TO_FILES']);
    	}
    }
    
    /**
    * Get areas
    *
    * Get an array with the system areas
    *
    * @access private
    * @global object $objDatabase
    * @see DevelopmentLibrary::_arrAreas
    * @return array DevelopmentLibrary::_arrAreas
    */
    function _getAreas()
    {
    	global $objDatabase;
    	
    	if (!isset($this->_arrAreas)) {
			$objArea = $objDatabase->Execute("SELECT tblAreas.area_id,
					tblAreas.parent_area_id,
					tblAreas.type,
					tblAreas.area_name,
					tblAreas.is_active,
					tblAreas.uri,
					tblAreas.target,
					tblAreas.module_id,
					tblModules.name,
					tblAreas.order_id,
					tblAreas.access_id
				FROM ".DBPREFIX."backend_areas AS tblAreas,
					 ".DBPREFIX."modules AS tblModules
				WHERE tblModules.id=tblAreas.module_id OR tblAreas.module_id=0 ORDER by order_id");
			if ($objArea !== false) {
				while (!$objArea->EOF) {
					$this->_arrAreas[$objArea->fields['area_id']] = array(
						'parent_area_id'	=> $objArea->fields['parent_area_id'],
						'type'				=> $objArea->fields['type'],
						'area_name'			=> $objArea->fields['area_name'],
						'is_active'			=> $objArea->fields['is_active'],
						'uri'				=> $objArea->fields['uri'],
						'target'			=> $objArea->fields['target'],
						'module_id'			=> $objArea->fields['module_id'],
						'module_name'		=> $objArea->fields['name'],
						'order_id'			=> $objArea->fields['order_id'],
						'access_id'			=> $objArea->fields['access_id']
					);
					$objArea->MoveNext();
				}
			}
			
			$arrTables = $objDatabase->MetaTables('TABLES');
			if ($arrTables !== false) {
				$arrModules = &$this->_getModules();
				
				foreach ($arrModules as $id => $module) {
					if (in_array(DBPREFIX."module_".$module['name']."_access", $arrTables)) {
						$objResult = $objDatabase->Execute("SELECT access_id, description FROM ".DBPREFIX."module_".$module['name']."_access");
						if ($objResult !== false) {
							while (!$objResult->EOF) {
								$this->_arrAreas[$objResult->fields['access_id']] = array(
									'parent_area_id'	=> $id,
									'type'				=> 'function',
									'area_name'			=> $objArea->fields['description'],
									'is_active'			=> 1,
									'uri'				=> '',
									'target'			=> '',
									'module_id'			=> $id,
									'module_name'		=> $module['name'],
									'order_id'			=> 0,
									'access_id'			=> $objArea->fields['access_id']
								);
								$objResult->MoveNext();
							}
						}
					}
				}
		}
			
		}
		return $this->_arrAreas;
    }
}
?>
