<?php
/**
 * Community
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_community
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once ASCMS_MODULE_PATH.'/community/lib/communityLib.class.php';

/**
 * Community
 *
 * class with methodes to manage the community
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author Comvation Development Team <info@comvation.com>
 * @access public
 * @version 1.0.0
 * @package     contrexx
 * @subpackage  module_community
 */
class Community extends Community_Library
{
	var $_pageTitle;
	var $_statusMessage;
	var $_objTpl;
	
	/**
	* constructor
	*/
	function Community()
	{
		$this->__construct();
	}
	
	/**
	* PHP5 constructor
	*
	* @global object $objTemplate
	* @global array $_ARRAYLANG
	* @see HTML_Template_Sigma::setErrorHandling, HTML_Template_Sigma::setVariable, initialize()
	*/
	function __construct()
	{
		global $objTemplate, $_ARRAYLANG;
		
		$this->_objTpl = &new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/community/template');
		$this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);
		
		$objTemplate->setVariable("CONTENT_NAVIGATION","<a href='index.php?cmd=community&amp;act=settings'>".$_ARRAYLANG['TXT_SETTINGS']."</a>
														<a href='index.php?cmd=user'>".$_ARRAYLANG['TXT_USER_ADMINISTRATION']."</a>");
		$this->initialize();
	}
	
	/**
	* Get the page
	*
	* @access public
	* @global object $objTemplate
	* @see _storeSettings(), _showSettingsPage(), $_pageTitle, $_statusMessage, HTML_Template_Sigma::get()
	*/
	function getPage()
	{
		global $objTemplate;
		
		if (!isset($_GET['act'])) {
			$_GET['act'] = "";
		}
		
		switch ($_GET['act']) {
		case 'settings':
			$this->_storeSettings();
			$this->_showSettingsPage();
			break;
			
		default:
			$this->_showSettingsPage();
			 break;
		}
		
    	$objTemplate->setVariable(array(
    		'CONTENT_TITLE'				=> $this->_pageTitle,
    		'CONTENT_STATUS_MESSAGE'	=> $this->_statusMessage,
    		'ADMIN_CONTENT'				=> $this->_objTpl->get()
    	));
	}
	
	/**
	* Store the settings
	*
	* @access private
	* @global object $objDatabase
	* @global array $_ARRAYLANG
	*/
	function _storeSettings()
	{
		global $objDatabase, $_ARRAYLANG;
		
		if (isset($_POST['community_save_settings'])) {
			$arrSelectedGroups = array();
			
			if (isset($_POST['communityAssignedGroups']) && is_array($_POST['communityAssignedGroups'])) {
				foreach ($_POST['communityAssignedGroups'] as $groupId) {
					array_push($arrSelectedGroups , intval($groupId));
				}
			}
			
			$selectedGroups = implode(",", $arrSelectedGroups);
			if ($objDatabase->Execute("UPDATE ".DBPREFIX."community_config SET value = '".$selectedGroups."' WHERE name = 'community_groups'") === false) {
				$this->_statusMessage = $_ARRAYLANG['TXT_DATABASE_QUERY_ERROR'];
			}
			
			$userActivation = 0;
			if (isset($_POST['communityUserActivation'])) {
				 $_POST['communityUserActivation'] = intval($_POST['communityUserActivation']);
				 if ($_POST['communityUserActivation'] == 1) {
					$userActivation = 1;
				 }
			}
			
			if ($objDatabase->Execute("UPDATE ".DBPREFIX."community_config SET status=".$userActivation." WHERE name='user_activation'") === false) {
				$this->_statusMessage = $_ARRAYLANG['TXT_DATEBASE_QUERY_ERROR'];
			}
			
			$userActivationTimeout = intval($_POST['communityUserActivationTimeout']);
			if ($userActivationTimeout < 0) {
				$userActivationTimeout = 0;
			} elseif ($userActivationTimeout > 24) {
				$userActivationTimeout = 24;
			}
			
			if ($userActivation && $userActivationTimeout != 0) {
				if (!$this->arrConfig['user_activation_timeout']['status'] || $userActivationTimeout != $this->arrConfig['user_activation_timeout']['value']) {
					if ($objDatabase->Execute("UPDATE ".DBPREFIX."community_config SET value='".$userActivationTimeout."', status=1 WHERE name='user_activation_timeout'") === false) {
						$this->_statusMessage = $_ARRAYLANG['TXT_DATEBASE_QUERY_ERROR'];
					}
				}
			} else {
				if ($this->arrConfig['user_activation_timeout']['status'] || $userActivationTimeout != $this->arrConfig['user_activation_timeout']['value']) {
					if ($objDatabase->Execute("UPDATE ".DBPREFIX."community_config SET value='".$userActivationTimeout."', status=0 WHERE name='user_activation_timeout'") === false) {
						$this->_statusMessage = $_ARRAYLANG['TXT_DATEBASE_QUERY_ERROR'];
					}
				}
			}
			
			$this->initialize();
		}
	}
	
	/**
	* Show the settings page
	*
	* @access private
	* @global array $_ARRAYLANG
	*/
	function _showSettingsPage()
	{
		global $_ARRAYLANG;
		
		$this->_objTpl->loadTemplateFile('module_community_settings.html');
		$this->_pageTitle = $_ARRAYLANG['TXT_SETTINGS'];
		
		$objUser = &new FWUser();
		
		$notSelectedGroups = "";
		$selectedGroups = "";
		$arrSelectedGroups = explode(',', $this->arrConfig['community_groups']['value']);
		$arrGroups = $objUser->getGroups();
		
		foreach ($arrGroups as $id => $arrGroup) {
			if (in_array($id, $arrSelectedGroups)) {
				$group = 'selectedGroups';
			} else {
				$group = 'notSelectedGroups';
			}

			$$group .= '<option value="'.$id.'">'.$arrGroup['name'].' ['.$arrGroup['type'].']</option>';
		}
		
		$userActivation = $this->arrConfig['user_activation']['status'];
		$userActivationTimeout = $this->arrConfig['user_activation_timeout']['value'];
		
		$this->_objTpl->setVariable(array(
			'TXT_GROUP_SETTINGS_TEXT'					=> $_ARRAYLANG['TXT_GROUP_SETTINGS_TEXT'],
			'TXT_AVAILABLE_GROUPS'						=> $_ARRAYLANG['TXT_AVAILABLE_GROUPS'],
			'TXT_ASSIGNED_GROUPS'						=> $_ARRAYLANG['TXT_ASSIGNED_GROUPS'],
			'TXT_SELECT_ALL'							=> $_ARRAYLANG['TXT_SELECT_ALL'],
			'TXT_DELETE_MARK'							=> $_ARRAYLANG['TXT_DELETE_MARK'],
			'TXT_USER_ACCOUNT_ACTIVATION_METHOD_TEXT'	=> $_ARRAYLANG['TXT_USER_ACCOUNT_ACTIVATION_METHOD_TEXT'],
			'TXT_ACTIVATION_BY_USER'					=> $_ARRAYLANG['TXT_ACTIVATION_BY_USER'],
			'TXT_TIME_PERIOD_ACTIVATION_TIME'			=> $_ARRAYLANG['TXT_TIME_PERIOD_ACTIVATION_TIME'],
			'TXT_ACTIVATION_BY_AUTHORIZED_PERSON'		=> $_ARRAYLANG['TXT_ACTIVATION_BY_AUTHORIZED_PERSON'],
			'TXT_SETTINGS'								=> $_ARRAYLANG['TXT_SETTINGS'],
			'TXT_STORE'									=> $_ARRAYLANG['TXT_STORE'],
			'COMMUNITY_NOT_SELECTED_GROUPS'				=> $notSelectedGroups,
			'COMMUNITY_SELECTED_GROUPS'					=> $selectedGroups,
			'COMMUNITY_USER_ACTIVATION_1'				=> $userActivation ? "checked=\"checked\"" : "",
			'COMMUNITY_USER_ACTIVATION_0'				=> $userActivation ? "" : "checked=\"checked\"",
			'COMMUNITY_USER_ACTIVATION_TIMEOUT'			=> $userActivationTimeout
		));
	}
}
?>
