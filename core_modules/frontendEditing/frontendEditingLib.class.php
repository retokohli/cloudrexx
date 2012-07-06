<?php
/**
 * Frontend Editing
 * 
 * @author Kaelin Thomas <thomas.kaelin@comvation.com>
 * @version 1.0
 * @package contrexx
 * @subpackage core_module_frontendEditing
 */
/**
 * Defines some basic constants for the frontend editing. The class also offers static methods which can be used to create
 * links to the frontend editing.
 * 
 * @author Kaelin Thomas <thomas.kaelin@comvation.com>
 * @version 1.0
 * @package contrexx
 * @subpackage core_module_frontendEditing
 */
class frontendEditingLib {
    /**
     * Path to the parent-directory of this file, relative to contrexx-root.
     */
    const FRONTENDEDITING_PATH = '/core_modules/frontendEditing/';

    /**
     * ID of the access key which should be used for frontend editing.
     */
    const ACCESS_KEY = 9;

    /**
     * Stores the authorization id for editing pages.
     */
    const AUTH_ID_FOR_PAGE_EDITING = 35;

    /**
     * Name of the SESSION-Field, which stores the visibility-status.
     */
    const SESSION_TOOLBAR_FIELD = 'frontendEditing_ToolbarVisibility';
    
    /**
     * Array containing all disallowed sections.
     */
    protected static $arrDisallowedSections = array(0	=>	'login');

    /**
     * Array containg all sections without a backend-part.
     */
    protected static $arrSectionsWithoutBackend = array(	0	=>	'home',
                                                            1	=>	'login',
                                                            2	=>	'sitemap',
                                                            3	=>	'imprint',
                                                            4	=>	'agb',
                                                            5	=>	'privacy',
                                                            6	=>	'error',
                                                            7	=>	'ids',
                                                            8	=>	'search');
                                            

                
    /**
     * Returns html-code with all include-statements.
     *
     * @return html-code with all include-statements
     */
    public static function getIncludeCode() {
        JS::activate('cx');
        JS::activate('ckeditor');
        
        $strFeInclude = '<link rel="stylesheet" type="text/css" href="'.ASCMS_PATH_OFFSET.frontendEditingLib::FRONTENDEDITING_PATH.'css/style.css" />'."\n";

        JS::registerJS(ASCMS_PATH_OFFSET.frontendEditingLib::FRONTENDEDITING_PATH.'js/frontEditing.js');
        JS::activate('jqueryui');
        
        return $strFeInclude;
    }


    /**
     * Returns html-code for a login-link.
     *
     * @return html-code for a login-link
     */
    public static function getLinkCode($pageId) {
        global $_CORELANG;
        
        if (FWUser::getFWUserObject()->objUser->login()) {
            $em = Env::em();
            $pageRepo = $em->getRepository('Cx\Model\ContentManager\Page');
            $page = $pageRepo->find($pageId);

            if (FWUser::getFWUserObject()->objUser->getAdminStatus() || 
                (Permission::checkAccess(frontendEditingLib::AUTH_ID_FOR_PAGE_EDITING, 'static', true) && (!$page->isBackendProtected() || Permission::checkAccess($page->getId(), 'page_backend', true)))
                ) {
                $strLinkDescription = $_CORELANG['TXT_FRONTEND_EDITING_TOOLBAR_EDIT'];
            } else {
                return;
            }
        } else {
            $strLinkDescription = $_CORELANG['TXT_FRONTEND_EDITING_LOGIN'];
        }
                
        return '<a href="javascript:void(0)" onclick="fe_setToolbarVisibility(true); fe_loadToolbar(true);" accesskey="'.frontendEditingLib::ACCESS_KEY.'" title="[ALT + '.frontendEditingLib::ACCESS_KEY.'] '.$strLinkDescription.'">'.$strLinkDescription.'</a>';
    }

    /**
     * Returns html-code with needed content-elements.
     *
     * @return html-code with needed content-elements.
     */
    public static function getContentCode($pageId) {
        //Is user logged in?
        $userIsLoggedIn = FWUser::getFWUserObject()->objUser->login() ? 'true' : 'false';
        
        //Should toolbar be shown?
        $showToolbar = 'false';
        if (!empty($_SESSION[frontendEditingLib::SESSION_TOOLBAR_FIELD])) {
            $showToolbar = 'true';
        }

        $frontendEditingJS = <<<FE_JS
var fe_userIsLoggedIn = $userIsLoggedIn;
var fe_userWantsToolbar = $showToolbar;
var fe_pageId = $pageId;
FE_JS;
        JS::registerCode($frontendEditingJS);

        $strFeContent  = '<div id="fe_Container" style="display: none;"></div>'."\n";
        $strFeContent .= '<div id="fe_Loader" style="display: none;"></div>'."\n";
        
        return $strFeContent;
    }
}
