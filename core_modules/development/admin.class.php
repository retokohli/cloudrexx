<?php
/**
 * Development
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author		Comvation Development Team <info@comvation.com>            
 * @version		1.0.0
 * @package     contrexx
 * @subpackage  core_module_development
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once ASCMS_CORE_MODULE_PATH.'/development/lib/DevelopmentLib.class.php';

/**
 * Development
 *
 * development module class
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author		Comvation Development Team <info@comvation.com>
 * @access		public
 * @version		1.0.0
 * @package     contrexx
 * @subpackage  core_module_development
 */
class Development extends DevelopmentLibrary
{
	/**
	* Template object
	*
	* @access private
	* @var object
	*/
	var $_objTpl;
	
	/**
	* Page title
	*
	* @access private
	* @var string
	*/
	var $_pageTitle;
	
	/**
	* Status Error message
	*
	* @access private
	* @var string
	*/
	var $_statusMessage = '';
	
	
	/**
	* Status Success message
	*
	* @access private
	* @var string
	*/
	var $_strOkMessage = '';
	
	/**
	* Constructor
	*/
	function Development()
	{
		$this->__construct();
	}
	
	/**
	* PHP5 constructor
	*
	* @global object $objTemplate
	* @global array $_ARRAYLANG
	*/
	function __construct()
	{
		global $objTemplate, $_ARRAYLANG, $objDatabase;
		$objDatabase->debug=true;
		$this->_objTpl = &new HTML_Template_Sigma(ASCMS_CORE_MODULE_PATH.'/development/template');
        CSRF::add_placeholder($this->_objTpl);
		$this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);
    	
    	$objTemplate->setVariable("CONTENT_NAVIGATION", "	<a href='index.php?cmd=development&amp;act=modules'>TXT_DEVELOPMENT_MODULES</a>
    														<a href='index.php?cmd=development&amp;act=areas'>TXT_DEVELOPMENT_BACKEND_AREAS</a>
    														<a href='index.php?cmd=development&amp;act=languages'>TXT_DEVELOPMENT_LANGUAGES</a>");
	}
	
	/**
	* Get page
	*
	* Get the development page
	*
	* @access public
	* @global object $objTemplate
	*/
	function getPage() 
	{
		global $objTemplate;
		
		if (!isset($_REQUEST['act'])) {
			$_REQUEST['act'] = '';
		}
		
		switch ($_REQUEST['act']) {
		case 'areas':
			$this->_getAreasPage();
			break;
		
		case 'languages':	
			$this->_getLanguagesPage();
			break;
				
		default:
			$this->_getModulesPage();
			break;
		}
		
		if (count($this->_arrErrorMessages)>0) {
			foreach ($this->_arrErrorMessages as $errorMessage) {
				$this->_statusMessage .= $errorMessage."<br />";
			}
		}
		
		$objTemplate->setVariable(array(
			'CONTENT_TITLE'				=> $this->_pageTitle,
			'CONTENT_STATUS_MESSAGE'	=> $this->_statusMessage,
			'ADMIN_CONTENT'				=> $this->_objTpl->get()
		));
	}
	
	
	function _getLanguagesPage(){
		global $_ARRAYLANG, $objTemplate;
		$this->_objTpl->loadTemplateFile('module_development_modules_overview.html');
		
		
		$objTemplate->setVariable('CONTENT_OK_MESSAGE', "test");
	
		
		
			
	}
	
	
	function _getModulesPage()
	{
		if (!isset($_REQUEST['tpl'])) {
			$_REQUEST['tpl'] = '';
		}
		
		switch ($_REQUEST['tpl']) {
		case 'modifyModule':
			$this->_showModifyModule();
			break;
			
		case 'saveModule':
			$this->_saveModule();
			$this->_showModulesOverview();		
			break;
			
		case 'deleteModule':
			if (isset($_GET['moduleId'])) {
				$moduleId = intval($_GET['moduleId']);
				$this->_deleteModule($moduleId);
			}
			$this->_showModulesOverview();
			break;
			
		default:
			$this->_showModulesOverview();
		}
	}
	
	function _showModulesOverview()
	{
		global $_ARRAYLANG;
		
		$this->_objTpl->loadTemplateFile('module_development_modules_overview.html');
		
		$arrModules = &$this->_getModules();
		
		if (is_array($arrModules) && count($arrModules)>0) {
			$rowNr = 0;
			
			$this->_objTpl->setVariable(array(
				'TXT_DEVELOPMENT_MODULES'				=> 'Module',
				'TXT_DEVELOPMENT_NAME'					=> 'Name',
				'TXT_DEVELOPMENT_DESCRIPTION'			=> 'Beschreibung',
				'TXT_DEVELOPMENT_FUNCTIONS'				=> 'Funktionen',
				'TXT_DEVELOPMENT_ADD_NEW_MODULE'		=> 'Neues Modul hinzufügen',
				'TXT_DEVELOPMENT_CONFIRM_DELETE_MODULE'	=> 'Möchten Sie dieses Modul wirklick löschen?',
				'TXT_DEVELOPMENT_ACTION_COULD_NOT_BE_UNDONE'	=> 'Diese Aktion kann nicht rückgänig gemacht werden!'
			));
			
			$this->_objTpl->setGlobalVariable(array(
				'TXT_DEVELOPMENT_MODIFY'				=> 'Bearbeiten',
				'TXT_DEVELOPMENT_DELETE'				=> 'Löschen'
			));
			
			foreach ($arrModules as $moduleId => $arrModule) {
				$this->_objTpl->setVariable(array(
					'DEVELOPMENT_ROW_CLASS'				=> $rowNr % 2 == 1 ? "row2" : "row1",
					'DEVELOPMENT_MODULE_ID'				=> $moduleId,
					'DEVELOPMENT_MODULE_ICON'			=> ASCMS_ADMIN_WEB_PATH.'/images/icons/folder_'.($arrModule['status'] == 'y' ? 'on' : 'off').($arrModule['is_required'] == 1 ? '_locked' : '').($arrModule['is_core'] == 1 ? '_core' : '').'.gif',
					'DEVELOPMENT_MODULE_NAME'			=> $arrModule['name'],
					'DEVELOPMENT_MODULE_DESCRIPTION'	=> isset($_ARRAYLANG[$arrModule['description_variable']]) && !empty($_ARRAYLANG[$arrModule['description_variable']]) ? $_ARRAYLANG[$arrModule['description_variable']] : "&nbsp;"
				));
				$this->_objTpl->parse('development_module_list');
				
				$rowNr++;
			}
		} else {
			$this->_showModifyModule();
		}
	}
	
	function _showModifyModule()
	{
		global $objLanguage;
		
		$this->_objTpl->loadTemplateFile('module_development_modules_modify.html');
		
		$arrLanguages = &$objLanguage->getLanguageArray();
		
		$moduleId = isset($_REQUEST['moduleId']) ? intval($_REQUEST['moduleId']) : 0;
		
		$arrModules = &$this->_getModules();
		
		if (isset($arrModules[$moduleId])) {
			
			
			$this->_objTpl->setVariable(array(
				'DEVELOPMENT_MODULE_ID'		=> $moduleId,
				'DEVELOPMENT_MODIFY_TITLE'	=> 'Modul bearbeiten',
				'DEVELOPMENT_MODULE_NAME'	=> $arrModules[$moduleId]['name'],
				'DEVELOPMENT_MODULE_IS_CORE_CHECKED'		=> $arrModules[$moduleId]['is_core'] == 1 ? 'checked="checked"' : '',
				'DEVELOPMENT_MODULE_IS_REQUIRED_CHECKED'	=> $arrModules[$moduleId]['is_required'] == 1 ? 'checked="checked"' : '',
				'DEVELOPMENT_MODULE_STATUS_CHECKED'			=> $arrModules[$moduleId]['status'] == 'y' ? 'checked="checked"' : ''
			));
		} else {
			$this->_objTpl->setVariable(array(
				'DEVELOPMENT_MODIFY_TITLE'	=>	'Neues Modul hinzufügen'
			));
		}
		
		
		$this->_objTpl->setVariable(array(
			'TXT_DEVELOPMENT_NAME'							=> 'Name',
			'TXT_DEVELOPMENT_DESCRIPTION'					=> 'Beschreibung',
			'TXT_DEVELOPMENT_MODIFY'						=> 'Bearbeiten',
			'TXT_DEVELOPMENT_OPTIONS'						=> 'Optionen',
			'TXT_DEVELOPMENT_CORE_MODULE'					=> 'Core Modul',
			'TXT_DEVELOPMENT_ESSENTIAL'						=> 'Notwendig',
			'TXT_DEVELOPMENT_DISPLAY_IN_MODULE_MANAGER'		=> 'Im Modulmanager anzeigen',
			'TXT_DEVELOPMENT_SAVE'							=> 'Speichern'
		));
		
		$arrVariable = &$this->_getLanguageVariable($arrModules[$moduleId]['description_variable']);
		$variableId = is_array($arrVariable) ? key($arrVariable) : 0;
		foreach ($arrLanguages as $languageId => $arrLanguage) {
			$this->_objTpl->setVariable(array(
				'DEVELOPMENT_LANGUAGE_ID'			=> $languageId,
				'DEVELOPMENT_LANGUAGE'				=> $arrLanguage['name']." (".$arrLanguage['lang'].")"
			));
			
			if ($variableId > 0) {
				$this->_objTpl->setVariable(array(
					'DEVELOPMENT_MODULE_DESCRIPTION'	=> isset($arrVariable[$variableId][$languageId]) ? $arrVariable[$variableId][$languageId]['content'] : '',
					'DEVELOPMENT_LANGUAGE_STATUS'		=> isset($arrVariable[$variableId][$languageId]) ? ($arrVariable[$variableId][$languageId]['status'] == 1 ? 'checked="checked"' : '') : ''
				));
			}
			
			$this->_objTpl->parse('development_module_descriptions');
		}
	}
	
	function _saveModule()
	{
		if (isset($_POST['save'])) {
			$arrDescription = array();
			$name = isset($_POST['developmentModuleName']) ? contrexx_strip_tags($_POST['developmentModuleName']) : '';
			$moduleId = isset($_GET['moduleId']) ? intval($_GET['moduleId']) : 0;
			$isCore = isset($_POST['developmentModuleIsCore']) && intval($_POST['developmentModuleIsCore']) == 1 ? 1 : 0;
			$isRequired = isset($_POST['developmentModuleIsRequired']) && intval($_POST['developmentModuleIsRequired']) == 1 ? 1 : 0;
			$status = isset($_POST['developmentModuleStatus']) && intval($_POST['developmentModuleStatus']) == 1 ? 1 : 0;
			
			
			if (isset($_POST['developmentModuleDescription'])) {
				foreach ($_POST['developmentModuleDescription'] as $langId => $description) {
					$arrDescription[intval($langId)] = array(
						'content'	=> contrexx_strip_tags($description),
						'status'	=> isset($_POST['developmentModuleDescriptionStatus'][$langId]) && intval($_POST['developmentModuleDescriptionStatus'][$langId]) == 1 ? 1 : 0
					);
				}
			}
			
			if (!empty($name)) {
				if ($moduleId > 0) {
					if ($this->_updateModule($moduleId, $name, $arrDescription, $isCore, $isRequired, $status)) {
						$this->_statusMessage .= "Das Modul wurde erfolgreich aktualisiert<br />";
					}
				} else {
					if ($this->_addModule($name, $arrDescription, $isCore, $isRequired, $status)) {
						$this->_statusMessage .= "Das Modul wurde erfolgreich hinzugefügt<br />";
					}
				}
			}
		}
	}
	
	function _getAreasPage()
	{
		if (!isset($_REQUEST['tpl'])) {
			$_REQUEST['tpl'] = '';
		}
		
		switch ($_REQUEST['tpl']) {
		case 'modify':
			$this->_showAreaModify();
			break;
			
		default:
			$this->_showAreasOverview();
		}
	}
	
	function _showAreasOverview()
	{
		$this->_objTpl->loadTemplateFile('module_development_areas_overview.html');
		
		$this->_objTpl->setVariable(array(
			'TXT_DEVELOPMENT_AREAS'				=> 'Bereiche',
			'TXT_DEVELOPMENT_TYPE'				=> 'Typ',
			'TXT_DEVELOPMENT_PARENT_ELEMENT'	=> 'Übergeortnetes Element',
			'TXT_DEVELOPMENT_NAME'				=> 'Name',
			'TXT_DEVELOPMENT_URI'				=> 'URI',
			'TXT_DEVELOPMENT_TARGET'			=> 'Ziel',
			'TXT_DEVELOPMENT_MODULE'			=> 'Modul',
			'TXT_DEVELOPMENT_ACCESS_ID'			=> 'Zugriffs ID'
		));
		
		$this->_objTpl->setGlobalVariable(array(
			'TXT_DEVELOPMENT_MODIFY'			=> 'Bearbeiten',
			'TXT_DEVELOPMENT_DELETE'			=> 'Löschen'
		));
		
		$arrAreas = $this->_getAreas();
		
		if (is_array($arrAreas)) {
			foreach ($arrAreas as $groupId => $arrGroup) {
				if ($arrGroup['type'] == 'group') {
					$this->_parseAreaRow($groupId, $arrGroup);
					foreach ($arrAreas as $navigationId => $arrNavigation) {
						if ($groupId == $arrNavigation['parent_area_id'] && $arrNavigation['type'] == 'navigation') {
							$this->_parseAreaRow($navigationId, $arrNavigation);
							foreach ($arrAreas as $functionId => $arrFunction) {
								if ($navigationId == $arrFunction['parent_area_id'] && $arrFunction['type'] == 'function') {
									$this->_parseAreaRow($functionId, $arrFunction);
								}
							}
						}
					}
				}
			}
			
			
		}
	}
	
	function _showAreaModify()
	{
		$this->_objTpl->loadTemplateFile('module_development_areas_modify.html');
		
		$this->_objTpl->setVariable(array(
			'TXT_DEVELOPMENT_ATTRIBUTE'		=> 'Eigenschaft',
			'TXT_DEVELOPMENT_VALUE'			=> 'Wert',
			'TXT_DEVELOPMENT_STATUS'		=> 'Status',
			'TXT_DEVELOPMENT_ACTIVE'		=> 'Aktiv',
			'TXT_DEVELOPMENT_TYPE'			=> 'Typ',
			'TXT_DEVELOPMENT_PARENT_AREA'	=> 'Übergeortnetes Element',
			'TXT_DEVELOPMENT_LANGUAGE_VARIABLE'	=> 'Sprachvariable',
			'TXT_DEVELOPMENT_URI'			=> 'URI',
			'TXT_DEVELOPMENT_TARGET'		=> 'Ziel',
			'TXT_DEVELOPMENT_MODULE'		=> 'Modul'
		));
		
		$areaId = isset($_REQUEST['areaId']) ? intval($_REQUEST['areaId']) : 0;
		if ($areaId > 0) {
			$arrAreas = &$this->_getAreas();
			$this->_objTpl->setVariable(array(
				'DEVELOPMENT_AREA_TITLE'		=> 'Bereich bearbeiten',
				'DEVELOPMENT_AREA_STATUS'		=> $arrAreas[$areaId]['is_active'] == 1 ? 'checked="checked"' : '',
				'DEVELOPMENT_AREA_TYPE_MENU'	=> $this->_areaTypeMenu('developmentAreaType', $arrAreas[$areaId]['type'], 'style="width:300px;"'),
				'DEVELOPMENT_AREA_PARENT_MENU'	=> $this->_parentAreaMenu('developmentAreaParentAreaId', $areaId, 'style="width:300px;"'),
				'DEVELOPMENT_AREA_LANGUAGE_VARIABLE'	=> $arrAreas[$areaId]['area_name'],
				'DEVELOPMENT_AREA_URI'			=> $arrAreas[$areaId]['uri'],
				'DEVELOPMENT_AREA_TARGET_MENU'	=> $this->_areaTargetMenu('developmentAreaTarget', $arrAreas[$areaId]['target'], 'style="width:300px;"'),
				'DEVELOPMENT_AREA_MODULE_MENU'	=> $this->_modulesMenu('developmentAreaModuleId', $arrAreas[$areaId]['module_id'], 'style="width:300px;"')
			));
			
		}
	
		
		
		
	}
	
	function _parseAreaRow($areaId, $arrArea)
	{
		global $_ARRAYLANG;
		
		$this->_objTpl->setVariable(array(
			'DEVELOPMENT_AREA_ID'				=> $areaId,
			'DEVELOPMENT_AREA_ROW_CLASS'		=> $arrArea['type'] == 'group' ? 'row3' : ($arrArea['type'] == 'navigation' ? 'row1' : 'row2'),
			'DEVELOPMENT_AREA_INDENT'			=> $arrArea['type'] == 'group' ? 0 : ($arrArea['type'] == 'navigation' ? 20 : 40),
			'DEVELOPMENT_AREA_STATUS'			=> $arrArea['is_active'] == 1 ? 'green' : 'red',
			'DEVELOPMENT_AREA_STATUS_TXT'		=> $arrArea['is_active'] == 1 ? 'Aktiv' : 'Inaktiv',
			'DEVELOPMENT_AREA_ORDER_ID'			=> $arrArea['order_id'],
			//'DEVELOPMENT_AREA_TYPE_MENU'		=> $arrArea['type'],
			//'DEVELOPMENT_AREA_PARENT_MENU'		=> isset($_ARRAYLANG[$this->_arrAreas[$arrArea['parent_area_id']]['area_name']]) ? $_ARRAYLANG[$this->_arrAreas[$arrArea['parent_area_id']]['area_name']] : $this->_arrAreas[$arrArea['parent_area_id']]['area_name'],
			'DEVELOPMENT_AREA_NAME'				=> ($arrArea['type'] == 'group' ? '<b>' : '').(isset($_ARRAYLANG[$arrArea['area_name']]) ? $_ARRAYLANG[$arrArea['area_name']] : $arrArea['area_name']).($arrArea['type'] == 'group' ? '</b>' : ''),
			'DEVELOPMENT_AREA_URI'				=> empty($arrArea['uri']) ? '&nbsp;' : $arrArea['uri'],
			//'DEVELOPMENT_AREA_TARGET_MENU'		=> $arrArea['target'], //$this->_areaTargetMenu('developmentAreaTarget['.$areaId.']', $arrArea['target']),
			//'DEVELOPMENT_AREA_MODULE_MENU'		=> $arrArea['module_name'],
			'DEVELOPMENT_AREA_ACCESS_ID'		=> $arrArea['access_id']
		));
		$this->_objTpl->parse('developmentAreaList');
	}
	
	function _areaTargetMenu($name, $target, $attrs = '')
	{
		$arrTypes = array('_blank', '_self', '_parent', '_top');
		
		$menu = "<select name=\"".$name."\" ".$attrs.">\n";
		
		foreach ($arrTypes as $type) {
			$menu .= "<option".($type == $target ? ' selected="selected"' : '').">".$type."</option>\n";
		}
		
		$menu .= "</select>\n";
		
		return $menu;
	}
	
	function _modulesMenu($name, $moduleId, $attrs = '')
	{
		$menu = "<select name=\"".$name."\" ".$attrs.">\n";
		
		$arrModules = &$this->_getModules();
		
		foreach ($arrModules as $moduleId => $arrModule) {
			$menu .= "<option value=\"".$moduleId."\"".($moduleId == $moduleId ? ' selected="selected"' : '').">".$arrModule['name']."</option>\n";
		}
		
		$menu .= "</select>\n";
		
		return $menu;
	}
	
	function _parentAreaMenu($name, $areaId, $attrs = '')
	{
		global $_ARRAYLANG;
		
		$arrAreas = &$this->_getAreas();
		if ($arrAreas[$areaId]['type'] == 'group') {
			return '-';
		}
		
		$menu = "<select name=\"".$name."\" ".$attrs.">\n";
		$showType = $arrAreas[$areaId]['type'] == 'function' ? 'navigation' : 'group';
		
		foreach ($arrAreas as $id => $arrArea) {
			if ($arrArea['type'] == $showType) {
				$menu .= "<option value=\"".$id."\"".($arrAreas[$areaId]['parent_area_id'] == $id ? ' selected="selected"' : '').">".(isset($_ARRAYLANG[$arrArea['area_name']]) ? $_ARRAYLANG[$arrArea['area_name']] : $arrArea['area_name'])."</option>\n";
			}
		}
		
		$menu .= "</select>\n";
		
		return $menu;
		
	}
	
	function _areaTypeMenu($name, $areaType, $attrs = '')
	{
		$arrTypes = array('group', 'navigation', 'function');
		
		$menu = "<select name=\"".$name."\" ".$attrs.">\n";
		
		foreach ($arrTypes as $type) {
			$menu .= "<option".($areaType == $type ? ' selected="selected"' : '').">".$type."</option>\n";
		}
		
		$menu .= "</select>\n";
		
		return $menu;
	}
}
?>
