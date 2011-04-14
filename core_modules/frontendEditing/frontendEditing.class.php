<?php
/**
 * Frontend Edition
 *
 * @author Kaelin Thomas <thomas.kaelin@comvation.com>
 * @version 1.0
 * @package contrexx
 * @subpackage core_module_frontendEditing
 */
/*
 * ATTENTION: SEE CODE AT END OF THIS FILE FOR COMPLETE UNDERSTANDMENT OF CODE!!
 */

/**
 * @ignore
 */
define('BASE_FOLDER', '../../');

//Includes
include_once(BASE_FOLDER.'lib/DBG.php');
DBG::deactivate();
require_once(BASE_FOLDER.'config/configuration.php');
require_once(BASE_FOLDER.'config/settings.php');
require_once(BASE_FOLDER.'config/set_constants.php');
require_once(BASE_FOLDER.'config/version.php');
require_once(BASE_FOLDER.'core/API.php');
require_once(ASCMS_LIBRARY_PATH.'/CSRF.php');
require_once(ASCMS_CORE_PATH.'/Init.class.php');
require_once(ASCMS_CORE_PATH.'/wysiwyg.class.php');
require_once(ASCMS_CORE_PATH.'/permission.class.php');
require_once('frontendEditingLib.class.php');

/**
 * This class handels the frontend editing.
 *
 * @author Kaelin Thomas <thomas.kaelin@comvation.com>
 * @version 1.0
 * @package contrexx
 * @subpackage core_module_frontendEditing
 */
class frontendEditing extends frontendEditingLib {
	/**
	 * Template object.
	 */
	private $objTemplate;

	/**
	 * Path to template folder.
	 */
	private $strTemplatePath = 'template';

	/**
	 * This char will be used to separate the status code from the content.
	 */
	private $strSplitChar = ';;;';

	/**
	 * Is set to true if the history-function is enabled.
	 */
	private $boolHistoryEnabled;

	/**
	 * Will be set to true, if the user failed with his login-attempt.
	 */
	private $boolLoginFailed = false;

	/**
	 * Requested action.
	 */
	private $strAction;

	/**
	 * Error message.
	 */
	private $strErrorCode;

	/**
	 * ID of the requested page.
	 */
	private $intPageId;

	/**
	 * Section of the requested page.
	 */
	private $strPageSection;

	/**
	 * CMD of the requested page.
	 */
	private $strPageCommand;

	/**
	 * Title of the requested page.
	 */
	private $strTitle;

	/**
	 * Content of the requested page.
	 */
	private $strContent;

	/**
	 * User-Object for accessing userprofile.
	 */
	private $objUser;

	/**
	 * Used for checking accessrights of the requested page.
	 */
	private $intBackendAccessId = 0;

	/**
	 * Constructor for PHP5.
	 */
	public function __construct() {
		$this->init();
		$this->getParameters();
		$this->loadValuesFromDatabase();
	}

	/**
	 * Creates necessary objects for this class.
	 *
	 * @global array
	 */
	private function init() {
		global $_CONFIG;

		//Abort execution if frontend editing is not activated
		if ($_CONFIG['frontendEditingStatus'] == 'off') {
			exit;
		}

		//Empty error code
		$strErrorCode = '';

		//Template
		$this->objTemplate = new HTML_Template_Sigma($this->strTemplatePath);
        CSRF::add_placeholder($this->objTemplate);
		$this->objTemplate->setErrorHandling(PEAR_ERROR_DIE);

		//Configuration
		$this->boolHistoryEnabled = ($_CONFIG['contentHistoryStatus'] == 'on') ? true : false;

		//create user object
		$this->objUser = FWUser::getFWUserObject();
		$this->objUser->setMode(true);
	}

	/**
	 * Catches the parameters in the $_REQUEST array and validates them.
	 */
	private function getParameters() {
		$this->strAction 			= isset($_REQUEST['act']) ? $_REQUEST['act'] : '';
		$this->intPageId 			= intval($_REQUEST['page']);
		$this->strPageSection 		= isset($_REQUEST['section']) ? $_REQUEST['section'] : '';
		$this->strPageCommand		= isset($_REQUEST['cmd']) ? $_REQUEST['cmd'] : '';
	}

	/**
	 * Loads the values of the requested page from database.
	 *
	 * @global 	ADONewConnection
	 */
	private function loadValuesFromDatabase() {
		global $objDatabase;

		$objResult = $objDatabase->Execute('	SELECT		content.title,
															content.content,
															navigation.backend_access_id
												FROM		'.DBPREFIX.'content				AS content
												INNER JOIN	'.DBPREFIX.'content_navigation	AS navigation
												ON			content.id = navigation.catid
												WHERE		id='.$this->intPageId.'
												LIMIT		1
											');

		if ($objResult->RecordCount() == 1) {
			$this->strTitle 			= html_entity_decode($objResult->fields['title'], ENT_QUOTES, CONTREXX_CHARSET);
			$this->strContent			= $objResult->fields['content'];
			$this->intBackendAccessId	= intval($objResult->fields['backend_access_id']);
		}
	}

	/**
	 * Selects the action to perform depending on $_GET['act'] value.
	 */
	public function performAction() {
		if ($this->checkAccessRights()) {
			//User is logged in
			switch ($this->strAction) {
				case 'getToolbar':
					echo $this->getToolbarPage();
					break;
				case 'setToolbarVisibility':
					$this->setToolbarVisibility($_REQUEST['status']);
					break;
				case 'getEditor':
					if (empty($this->strPageSection) || $_REQUEST['selection'] == 'false' || in_array($this->strPageSection, frontendEditingLib::$arrSectionsWithoutBackend)) {
						echo $this->getEditorPage();
					} else {
						echo $this->getSelectionPage();
					}
					break;
				case 'doUpdate':
					echo $this->updatePage();
					break;
				case 'getAdmin':
					echo $this->getAdminPage();
					break;
			}
		} else {
			//User is not logged in or not allowed to edit¨
			switch($this->strErrorCode) {
				case 'login':
					echo $this->getLoginPage();
					break;
				case 'disallowed':
					echo $this->getDisallowedPage();
					break;

			}
		}
	}

	/**
	 * Checks the access rights of the currently logged in user. Returns true, if the
	 * user is allowed to access this url.
	 *
	 * @return true, if the user is allowed to access the requested url.
	 */
	private function checkAccessRights() {
		//check for login
		if ($_POST['doLogin'] == 'true') {
			if (!empty($_POST['username']) && !empty($_POST['password']) && !empty($_POST['seckey']) && !empty($_POST['type'])) {

				//Assign variables for login
				$_POST['USERNAME'] 	= $_POST['username'];
				$_POST['PASSWORD'] 	= $_POST['password'];
                $captchaKey = strtoupper($_POST['seckey']);
                $captchaOffset = $_POST['seckeyOffset'];
                include_once ASCMS_LIBRARY_PATH.'/spamprotection/captcha.class.php';

                $captcha = new Captcha();
                $captchaPassed = $captcha->check($captchaKey);


                if ($captchaPassed) {
                    if ($this->objUser->checkAuth() && (Permission::hasAllAccess() || $this->isUserInBackendGroup())) {
                        //Login successfull
                        $_SESSION[frontendEditingLib::SESSION_LOGIN_FIELD] = true;
                        $this->setToolbarVisibility(true);
                    } else {
                        $this->boolLoginFailed = true;
                    }
                }
                else {
                    $this->boolLoginFailed = true;
                }
			} else {
				$this->boolLoginFailed = true;
			}
		}

		//check for enough rights to perform an action
		if (frontendEditingLib::isUserLoggedIn()) {
			//check for toolbar-loading (can be done by everyone)
			if ($this->strAction == 'getToolbar' || $this->strAction == 'hideToolbar') {
				return true;
			}

			//filter out disallowed sections
			if (in_array($this->strPageSection, frontendEditingLib::$arrDisallowedSections)) {
				$this->strErrorCode = 'disallowed';
				return false;
			}

			//User is admin and is allowed to do everything
			if ($this->objUser->objUser->getAdminStatus()) {
				return true;
			}

			//No admin, figure out what the user is allowed to do
			if (Permission::checkAccess(frontendEditingLib::AUTH_ID_FOR_PAGE_EDITING, 'static', true)) {
				//Page is not restricted or allowed for this usergroup
				if($this->intBackendAccessId == 0 || Permission::checkAccess($this->intBackendAccessId, 'dynamic', true)) {
					return true;
				}
			}

			$this->strErrorCode = 'disallowed';
		} else {
			$this->strErrorCode = 'login';

		}

		return false;
	}

	/**
	 * Checks, if the current user is in a backend group.
	 *
	 * @return boolean	true, if the user is in a backend group.
	 */
	private function isUserInBackendGroup() {
		$objGroup = $this->objUser->objGroup->getGroups(array('is_active' => true, 'type' => 'backend'), null, 'group_id');
		$arrAssociatedGroups = $this->objUser->objUser->getAssociatedGroupIds();

        while (!$objGroup->EOF) {
            if (in_array($objGroup->getId(), $arrAssociatedGroups)) {
                return true;
			}
            $objGroup->next();
		}

		return false;
	}

	/**
	 * Returns html source of the login page.
	 *
	 * @return html source of the login page.
	 */
	private function getLoginPage() {
		global $_CORELANG;

		$this->objTemplate->loadTemplateFile('login.html',true,true);

		$statusMessage = '<div class="fe_LoginSpacer">&nbsp;</div>';
		if ($this->boolLoginFailed) {
			$statusMessage = '<div class="fe_LoginError">'.$_CORELANG['TXT_FRONTEND_EDITING_LOGIN_FAILED'].'</div>';
		}

        include_once ASCMS_LIBRARY_PATH.'/spamprotection/captcha.class.php';
        $captcha = new Captcha();

		$this->objTemplate->setVariable(array(	'TXT_LOGIN_TITLE'				=>	$_CORELANG['TXT_FRONTEND_EDITING_LOGIN_TITLE'],
												'TXT_LOGIN_USERNAME'			=>	$_CORELANG['TXT_FRONTEND_EDITING_LOGIN_USERNAME'],
												'TXT_LOGIN_PASSWORD'			=>	$_CORELANG['TXT_FRONTEND_EDITING_LOGIN_PASSWORD'],
												'TXT_LOGIN_SECKEY'				=>	$_CORELANG['TXT_FRONTEND_EDITING_LOGIN_SECKEY'],
												'TXT_LOGIN_AREA'				=>	$_CORELANG['TXT_FRONTEND_EDITING_LOGIN_AREA'],
												'TXT_LOGIN_AREA_FRONTEND'		=>	$_CORELANG['TXT_FRONTEND_EDITING_LOGIN_FRONTEND'],
												'TXT_LOGIN_AREA_BACKEND'		=>	$_CORELANG['TXT_FRONTEND_EDITING_LOGIN_BACKEND'],
												'TXT_LOGIN_SUBMIT'				=>	$_CORELANG['TXT_FRONTEND_EDITING_LOGIN_SUBMIT'],
												'TXT_LOGIN_CANCEL'				=>	$_CORELANG['TXT_FRONTEND_EDITING_LOGIN_CANCEL'],
												'TXT_LOGIN_PASSWORD_FORGOTTON'	=>	$_CORELANG['TXT_FRONTEND_EDITING_LOGIN_PASSWORD_FORGOTTON']
									));

		$this->objTemplate->setVariable(array(	'LOGIN_PAGE_ID'			=>	$this->intPageId,
												'LOGIN_PAGE_SECTION'	=>	$this->strPageSection,
												'LOGIN_PAGE_CMD'		=>	$this->strPageCommand,
												'LOGIN_SECURITY_IMAGE'	=>	$captcha->getURL(),
												'LOGIN_USERNAME'		=>	(get_magic_quotes_gpc() == 1 ? stripslashes($_POST['USERNAME']) : $_POST['USERNAME']),
												'LOGIN_STATUS_MESSAGE'	=>	$statusMessage,
										));

		return 'login'.$this->strSplitChar.$this->objTemplate->get();
	}

	/**
	 * Returns html source of the disallowed page.
	 *
	 * @return html source of the disallowed page.
	 */
	private function getDisallowedPage() {
		$this->objTemplate->loadTemplateFile('disallowed.html',true,true);

		return 'disallowed'.$this->strSplitChar.$this->objTemplate->get();
	}

	/**
	 * Returns url for accessing the admin-interface.
	 *
	 * @return url for accessing the admin-interface.
	 */
	private function getAdminPage() {
		return 'admin'.$this->strSplitChar.frontendEditingLib::ADMIN_PATH ;
	}

	/**
	 * Returns html source of the toolbar.
	 *
	 * @return html source of the toolbar.
	 */
	private function getToolbarPage() {
		global $_CORELANG;

		$this->objTemplate->loadTemplateFile('toolbar.html',true,true);

		$this->objTemplate->setVariable(array(	'TXT_TOOLBAR_USER'			=>	$_CORELANG['TXT_FRONTEND_EDITING_TOOLBAR_USER'],
												'TXT_TOOLBAR_PREVIEW'		=>	$_CORELANG['TXT_FRONTEND_EDITING_TOOLBAR_PREVIEW'],
												'TXT_TOOLBAR_EDIT'			=>	$_CORELANG['TXT_FRONTEND_EDITING_TOOLBAR_EDIT'],
												'TXT_TOOLBAR_ADMIN'			=>	$_CORELANG['TXT_FRONTEND_EDITING_TOOLBAR_ADMIN'],
												'TXT_TOOLBAR_CLOSE'			=>	$_CORELANG['TXT_FRONTEND_EDITING_TOOLBAR_CLOSE'],
												'TXT_TOOLBAR_LOGOUT'		=>	$_CORELANG['TXT_FRONTEND_EDITING_TOOLBAR_LOGOUT']
										));

		$this->objTemplate->setVariable(array(	'TOOLBAR_PATH'				=>	frontendEditingLib::FRONTENDEDITING_PATH,
												'TOOLBAR_USERNAME'			=>	$this->objUser->objUser->getUsername()
										));

		return 'editor'.$this->strSplitChar.$this->objTemplate->get();
	}

	/**
	 * Setter-Method for (de-)activating the toolbar visibility.
	 *
	 * @param $isToolbarVisible
	 */
	private function setToolbarVisibility($isToolbarVisible) {
		$_SESSION[frontendEditingLib::SESSION_TOOLBAR_FIELD] = $isToolbarVisible;
	}

	/**
	 * Returns html source of the selection-page.
	 *
	 * @return html source of the selection-page.
	 */
	private function getSelectionPage() {
		global $_CORELANG;

		$this->objTemplate->loadTemplateFile('selection.html',true,true);

		$this->objTemplate->setVariable(array(	'TXT_SELECTION_TITLE'			=>	$_CORELANG['TXT_FRONTEND_EDITING_SELECTION_TITLE'],
												'TXT_SELECTION_TEXT'			=>	$_CORELANG['TXT_FRONTEND_EDITING_SELECTION_TEXT'],
												'TXT_SELECTION_MODE_PAGE'		=>	$_CORELANG['TXT_FRONTEND_EDITING_SELECTION_MODE_PAGE'],
												'TXT_SELECTION_MODE_CONTENT'	=>	$_CORELANG['TXT_FRONTEND_EDITING_SELECTION_MODE_CONTENT']
								));

		$this->objTemplate->setVariable(array(	'SELECTION_IMAGE_PATH'	=>	frontendEditingLib::FRONTENDEDITING_PATH,
												'SELECTION_ADMIN_PATH'	=>	frontendEditingLib::ADMIN_PATH .'?cmd='.$this->strPageSection));

		return 'selection'.$this->strSplitChar.$this->objTemplate->get();
	}

	/**
	 * Returns html source of the editor.
	 *
	 * @return html source of the editor.
	 */
	private function getEditorPage() {
		global $_CORELANG;

		$this->objTemplate->loadTemplateFile('editor.html',true,true);

		$this->objTemplate->setVariable(array(	'TXT_EDIT_TITLE'			=>	$_CORELANG['TXT_FRONTEND_EDITING_TOOLBAR_EDIT'],
												'TXT_EDIT_PAGETITLE'		=>	$_CORELANG['TXT_FRONTEND_EDITING_EDIT_PAGETITLE'],
												'TXT_EDIT_CONTENT'			=>	$_CORELANG['TXT_FRONTEND_EDITING_EDIT_CONTENT'],
												'TXT_EDIT_PREVIEW'			=>	$_CORELANG['TXT_FRONTEND_EDITING_EDIT_PREVIEW'],
												'TXT_EDIT_SUBMIT'			=>	$_CORELANG['TXT_FRONTEND_EDITING_EDIT_SUBMIT']
								));

		$this->objTemplate->setVariable(array(	'EDIT_TITLE'			=>	$this->strTitle,
												'EDIT_WYSIWYG'			=>	$this->getWysiwyg()
										));

		return 'editor'.$this->strSplitChar.$this->objTemplate->get();
	}

	/**
	 * Returns an instance of the wysiwyg-editor filled with content of the desired page.
	 *
	 * @param	integer		$intPageId: the content of the page with this id will loaded into the editor
	 */
	private function getWysiwyg() {
		$strContent = preg_replace('/\{([A-Z0-9_-]+)\}/', '[[\\1]]', $this->strContent);
		return get_wysiwyg_editor('fe_FormContent', $strContent, 'frontendEditing');
	}

	/**
	 * Updates the page with the values submitted in $_REQUEST and collected by <pre>getParameters()</pre>.
	 *
	 * @global 	ADONewConnection
	 */
	private function updatePage() {
		global $objDatabase;

		//Collect existing values
		$objResult = $objDatabase->Execute('	SELECT		content.content,
															content.title,
															content.metatitle,
															content.metadesc,
															content.metakeys,
															content.metarobots,
															content.css_name AS c_css_name,
															content.redirect,
															content.expertmode,
															navigation.is_validated,
															navigation.parcat,
															navigation.catname,
															navigation.target,
															navigation.displayorder,
															navigation.displaystatus,
															navigation.activestatus,
															navigation.cachingstatus,
															navigation.username,
															navigation.changelog,
															navigation.cmd,
															navigation.lang,
															navigation.module,
															navigation.startdate,
															navigation.enddate,
															navigation.protected,
															navigation.frontend_access_id,
															navigation.backend_access_id,
															navigation.themes_id,
															navigation.css_name AS n_css_name
												FROM		'.DBPREFIX.'content				AS content
												INNER JOIN	'.DBPREFIX.'content_navigation	AS navigation
												ON			content.id = navigation.catid
												WHERE		id='.$this->intPageId.'
												LIMIT		1
											');

		$strOld_C_Content 		= $objResult->fields['content'];
		$strOld_C_Title 		= $objResult->fields['title'];
		$strOld_C_MetaTitle 	= $objResult->fields['metatitle'];
		$strOld_C_MetaDesc 		= $objResult->fields['metadesc'];
		$strOld_C_MetaKeys 		= $objResult->fields['metakeys'];
		$strOld_C_MetaRobots 	= $objResult->fields['metarobots'];
		$strOld_C_CssName 		= $objResult->fields['c_css_name'];
		$strOld_C_Redirect 		= $objResult->fields['redirect'];
		$strOld_C_ExpertMode 	= $objResult->fields['expertmode'];

		$strOld_N_IsValidated	= $objResult->fields['is_validated'];
		$strOld_N_ParCat		= $objResult->fields['parcat'];
		$strOld_N_CatName		= $objResult->fields['catname'];
		$strOld_N_Target		= $objResult->fields['target'];
		$strOld_N_DisplayOrder	= $objResult->fields['displayorder'];
		$strOld_N_DisplayStatus	= $objResult->fields['displaystatus'];
		$strOld_N_ActiveStatus	= $objResult->fields['activestatus'];
		$strOld_N_CachingStatus	= $objResult->fields['cachingstatus'];
		$strOld_N_UserName		= $objResult->fields['username'];
		$strOld_N_ChangeLog		= $objResult->fields['changelog'];
		$strOld_N_Command		= $objResult->fields['cmd'];
		$strOld_N_Language		= $objResult->fields['lang'];
		$strOld_N_Module		= $objResult->fields['module'];
		$strOld_N_Startdate		= $objResult->fields['startdate'];
		$strOld_N_Enddate		= $objResult->fields['enddate'];
		$strOld_N_Protected		= $objResult->fields['protected'];
		$strOld_N_FrontendAcc	= $objResult->fields['frontend_access_id'];
		$strOld_N_BackendAcc	= $objResult->fields['backend_access_id'];
		$strOld_N_ThemesId		= $objResult->fields['themes_id'];
		$strOld_N_CssName		= $objResult->fields['n_css_name'];

		//Collect new values
		$strNew_C_Title 	= contrexx_addslashes(strip_tags($_POST['title']));
		$strNew_C_Content 	= preg_replace('/\[\[([A-Z0-9_-]+)\]\]/', '{\\1}', contrexx_addslashes($_POST['content']));

		$strNew_N_UserName	= contrexx_addslashes(strip_tags($this->objUser->objUser->getUsername()));
		$strNew_N_ChangeLog = time();

		//Update database
		$objResult = $objDatabase->Execute('	UPDATE		'.DBPREFIX.'content 			AS content
												INNER JOIN	'.DBPREFIX.'content_navigation	AS navigation
												ON			content.id = navigation.catid
												SET			content.title = "'.$strNew_C_Title.'",
															content.content = "'.$strNew_C_Content.'",
															navigation.username = "'.$strNew_N_UserName.'",
															navigation.changelog = '.$strNew_N_ChangeLog.'
												WHERE		content.id='.$this->intPageId.'
											');

		//Write history
		if ($this->boolHistoryEnabled) {
			$objDatabase->Execute('	UPDATE	'.DBPREFIX.'content_navigation_history
									SET		is_active="0"
									WHERE	catid='.$this->intPageId);

			$objDatabase->Execute('	INSERT
									INTO	'.DBPREFIX.'content_navigation_history
									SET		is_active="1",
											catid='.$this->intPageId.',
											parcat="'.$strOld_N_CatId.'",
					                    	catname="'.$strOld_N_CatName.'",
					                    	target="'.$$strOld_N_Target.'",
					                    	displayorder='.$strOld_N_DisplayOrder.',
					                    	displaystatus="'.$strOld_N_DisplayStatus.'",
					                    	activestatus="'.$strOld_N_ActiveStatus.'",
					                    	cachingstatus="'.$strOld_N_CachingStatus.'",
					                    	username="'.$strNew_N_UserName.'",
					                    	changelog="'.$strNew_N_ChangeLog.'",
					                   	 	cmd="'.$strOld_N_Command.'",
					                    	lang="'.$strOld_N_Language.'",
					                    	module="'.$strOld_N_Module.'",
					                    	startdate="'.$strOld_N_Startdate.'",
					                    	enddate="'.$strOld_N_Enddate.'",
					                    	protected='.$strOld_N_Protected.',
					                    	frontend_access_id='.$strOld_N_FrontendAcc.',
					                    	backend_access_id='.$strOld_N_BackendAcc.',
					                    	themes_id="'.$$strOld_N_ThemesId.'",
					                    	css_name="'.$strOld_N_CssName.'"
						               ');

			$intHistoryId = $objDatabase->insert_id();

			$objDatabase->Execute('	INSERT
									INTO	'.DBPREFIX.'content_history
						            SET 	id='.$intHistoryId.',
						            		page_id='.$this->intPageId.',
						                   	content="'.$strNew_C_Content.'",
						                   	title="'.$strNew_C_Title.'",
						                   	metatitle="'.$strOld_C_MetaTitle.'",
							                metadesc="'.$strOld_C_MetaDesc.'",
						                   	metakeys="'.$strOld_C_MetaKeys.'",
						                   	metarobots="'.$strOld_C_MetaRobots.'",
						                   	css_name="'.$strOld_C_CssName.'",
						                   	redirect="'.$strOld_C_Redirect.'",
						                  	expertmode="'.$strOld_C_ExpertMode.'"'
									);

			$objDatabase->Execute('	INSERT
									INTO	'.DBPREFIX.'content_logfile
									SET		action="update",
											history_id='.$intHistoryId.',
											is_validated="1"
								');
		}

	}
}

//Instantiate database-object. Has to be global because of included classes!
$objDatabase = getDatabaseObject($strErrorCode);

//Instantiate language-array. Has to be global because of included classes!
$objInit = new InitCMS();
$_CORELANG = $objInit->loadLanguageData('core');

if(!isset($sessionObj)) {
    //Instantiate session-object. Has to be global because of included classes!
    $sessionObj = new cmsSession();
}

//Instantiate Front editing
$objFrontendEditing = new frontendEditing();
$objFrontendEditing->performAction();
?>
