<?php
/**
 * Frontend Edition
 *
 * @author Kaelin Thomas <thomas.kaelin@comvation.com>
 * @author Daeppen Thomas <thomas.daeppen@comvation.com>
 * @version 2.0
 * @package contrexx
 * @subpackage core_module_frontendEditing
 */

/**
 * @ignore
 */
require_once ASCMS_CORE_MODULE_PATH.'/frontendEditing/frontendEditingLib.class.php';
/**
 * @ignore
 */
require_once ASCMS_CORE_PATH.'/wysiwyg.class.php';

/**
 * This class handles the frontend editing.
 *
 * @author Kaelin Thomas <thomas.kaelin@comvation.com>
 * @author Daeppen Thomas <thomas.daeppen@comvation.com>
 * @version 2.0
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
	private $strTemplatePath = '';

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
     * currently edited Page's path, e.g. '/example/shop/terms'
     */
    protected $strPagePath = null;
	/**
	 * Section of the requested page.
	 */
	private $strPageSection;

	/**
	 * CMD of the requested page.
	 */
	private $strPageCommand;

    /**
     * Language ID of the currently edited page.
     */
    private $intPageLangId = 0;

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
     * Doctrine EntityManager
     */
    protected $em = null;
    /**
     * Doctrine Repository for entity Page
     */
    protected $pageRepo = null;
    /**
     * Doctrine Page entity of currently edited Page
     */
    protected $page = null;

	/**
	 * Constructor for PHP5.
	 */
	public function __construct($entityManager) {
        $this->em = $entityManager;
        $this->pageRepo = $this->em->getRepository('Cx\Model\ContentManager\Page');

        $this->intPageLangId = FRONTEND_LANG_ID;

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
		$this->strTemplatePath = ASCMS_CORE_MODULE_PATH.'/frontendEditing/template';
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
        $this->strPagePath          = isset($_REQUEST['page']) ? $_REQUEST['page'] : '';
	}

	/**
	 * Loads the values of the requested page from database.
	 *
	 * @global 	ADONewConnection
	 */
	private function loadValuesFromDatabase() {
        /*
          Find out whether there's a page in our language at the path specified
         */
        $page = $this->pageRepo->find($this->strPagePath);

        /*
          We've got a set of pages and we know our desired page exists - get it.
         */
        //get the right page object.
        if(!$page)
            return;
        
        $this->page = $page;

        //remember interesting properties.
        $this->strTitle = $this->page->getContentTitle();
        $this->strContent = $this->page->getContent();

		$this->strPageSection = $this->page->getModule();
		$this->strPageCommand = $this->page->getCmd();
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
					if (   empty($this->strPageSection)
                        || $_REQUEST['selection'] == 'false'
                        || in_array($this->strPageSection, frontendEditingLib::$arrSectionsWithoutBackend)
                    ) {
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

        exit;
	}

	/**
	 * Checks the access rights of the currently logged in user. Returns true, if the
	 * user is allowed to access this url.
	 *
	 * @return true, if the user is allowed to access the requested url.
	 */
	private function checkAccessRights() {
		//check for login
		if (isset($_POST['doLogin']) && $_POST['doLogin'] == 'true') {
			if (!empty($_POST['fe_LoginUsername']) && !empty($_POST['fe_LoginPassword'])) {

				//Assign variables for login
				$_POST['USERNAME'] 	= $_POST['fe_LoginUsername'];
				$_POST['PASSWORD'] 	= $_POST['fe_LoginPassword'];



                if (FWCaptcha::getInstance()->check()) {
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
                //unprotected page, edit
                if(!$this->page->isBackendProtected())
                    return true;
                
                if(Permission::checkAccess($this->page->getId(), 'page_backend', true));
					return true;
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
// TODO: proposal: why not using the regular login module?
		global $_CORELANG;

		$this->objTemplate->loadTemplateFile('login.html',true,true);

		$statusMessage = '<div class="fe_LoginSpacer">&nbsp;</div>';
		if ($this->boolLoginFailed) {
			$statusMessage = '<div class="fe_LoginError">'.$_CORELANG['TXT_FRONTEND_EDITING_LOGIN_FAILED'].'</div>';
		}

		$this->objTemplate->setVariable(array(	'TXT_LOGIN_TITLE'				=>	$_CORELANG['TXT_FRONTEND_EDITING_LOGIN_TITLE'],
												'TXT_LOGIN_USERNAME'			=>	$_CORELANG['TXT_FRONTEND_EDITING_LOGIN_USERNAME'],
												'TXT_LOGIN_PASSWORD'			=>	$_CORELANG['TXT_FRONTEND_EDITING_LOGIN_PASSWORD'],
												'TXT_LOGIN_CAPTCHA'				=>	$_CORELANG['TXT_CORE_CAPTCHA'],
												'TXT_LOGIN_AREA'				=>	$_CORELANG['TXT_FRONTEND_EDITING_LOGIN_AREA'],
												'TXT_LOGIN_AREA_FRONTEND'		=>	$_CORELANG['TXT_FRONTEND_EDITING_LOGIN_FRONTEND'],
												'TXT_LOGIN_AREA_BACKEND'		=>	$_CORELANG['TXT_FRONTEND_EDITING_LOGIN_BACKEND'],
												'TXT_LOGIN_SUBMIT'				=>	$_CORELANG['TXT_FRONTEND_EDITING_LOGIN_SUBMIT'],
												'TXT_LOGIN_CANCEL'				=>	$_CORELANG['TXT_FRONTEND_EDITING_LOGIN_CANCEL'],
												'TXT_LOGIN_PASSWORD_FORGOTTON'	=>	$_CORELANG['TXT_FRONTEND_EDITING_LOGIN_PASSWORD_FORGOTTON']
									));
        $loginUsername = isset($_POST['USERNAME']) ? $_POST['USERNAME'] : '';

        $lostPWPath = '';
        $crit = array(
             'module'   => 'login',
             'lang'     => FRONTEND_LANG_ID,
             'cmd'      => 'lostpw',
        );
        $page = $this->pageRepo->findOneBy($crit);
        if ($page && $page->isActive()) {
            $lostPWPath = ASCMS_PATH_OFFSET.Env::get('virtualLanguageDirectory').'/'.$this->pageRepo->getPath($page);
        }

		$this->objTemplate->setVariable(array(	'LOGIN_PAGE_ID'			=> $this->intPageId,
												'LOGIN_PAGE_SECTION'	=> $this->strPageSection,
												'LOGIN_PAGE_CMD'		=> $this->strPageCommand,
												'LOGIN_CAPTCHA_CODE'	=> FWCaptcha::getInstance()->getCode(),
												'LOGIN_USERNAME'		=> (get_magic_quotes_gpc() == 1 ? stripslashes($loginUsername) : $loginUsername),
												'LOGIN_STATUS_MESSAGE'	=> $statusMessage,
                                                'LOGIN_LOSTPW_URL'      => $lostPWPath,
                                                'JAVASCRIPT' => JS::getCode(),
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
		return 'admin'.$this->strSplitChar.ASCMS_ADMIN_WEB_PATH.'/?cmd='.$this->strPageSection.'&'.CSRF::param();
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

		$this->objTemplate->setVariable(array(	'TOOLBAR_PATH'				=>	ASCMS_PATH_OFFSET.frontendEditingLib::FRONTENDEDITING_PATH,
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

		$this->objTemplate->setVariable(array(	'SELECTION_IMAGE_PATH'	=>	ASCMS_PATH_OFFSET.frontendEditingLib::FRONTENDEDITING_PATH,
												'SELECTION_ADMIN_PATH'	=>	ASCMS_ADMIN_WEB_PATH.'/?cmd='.$this->strPageSection.'&amp;'.CSRF::param()));

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
        $this->page->setContentTitle(strip_tags($_POST['title']));
        $this->page->setContent(preg_replace('/\[\[([A-Z0-9_-]+)\]\]/', '{\\1}', html_entity_decode($_POST['content'], ENT_QUOTES, CONTREXX_CHARSET)));
        $this->page->setUser(strip_tags($this->objUser->objUser->getUsername()));
        $this->page->setUpdatedAtToNow();

        $this->em->persist($this->page);
        $this->em->flush();
	}
}
