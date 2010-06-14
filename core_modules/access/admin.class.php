<?php

$_ARRAYLANG['TXT_ACCESS_NEWSLETTER_LISTS'] = "Newsletter";

/**
* User Management
* @copyright    CONTREXX CMS - COMVATION AG
* @author       COMVATION Development Team <info@comvation.com>
* @package      contrexx
* @subpackage   core_module_access
* @version      1.0.0
*/

/**
 * @ignore
 */
require_once ASCMS_CORE_MODULE_PATH.'/access/lib/AccessLib.class.php';

/**
* User Management Backend
* @copyright    CONTREXX CMS - COMVATION AG
* @author       COMVATION Development Team <info@comvation.com>
* @package      contrexx
* @subpackage   core_module_access
* @version      1.0.0
*/
class AccessManager extends AccessLib
{
    /**
     * Contains the info messages about done operations
     *
     * @var array
     * @access private
     */
    private $arrStatusMsg = array('ok' => array(), 'error' => array());

    /**
     * Page title of the current section
     *
     * @var string
     * @access private
     */
    private $_pageTitle = '';

    /**
    * Constructor
    *
    * @global HTML_Template_Sigma
    * @global array
    */
    public function __construct()
    {
        global $objTemplate, $_ARRAYLANG;

        parent::__construct();
        $this->_objTpl = new HTML_Template_Sigma(ASCMS_CORE_MODULE_PATH.'/access/template');
        CSRF::add_placeholder($this->_objTpl);
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);

        $objTemplate->setVariable('CONTENT_NAVIGATION', /*' <a href="index.php?cmd=access" title="'.$_ARRAYLANG['TXT_ACCESS_OVERVIEW'].'">'.$_ARRAYLANG['TXT_ACCESS_OVERVIEW'].'</a>
                                                            '.*/(Permission::checkAccess(18, 'static', true) ? '<a href="index.php?cmd=access&amp;act=user" title="'.$_ARRAYLANG['TXT_ACCESS_USERS'].'">'.$_ARRAYLANG['TXT_ACCESS_USERS'].'</a>' : '').'
                                                            '.(Permission::checkAccess(18, 'static', true) ? '<a href="index.php?cmd=access&amp;act=group" title="'.$_ARRAYLANG['TXT_ACCESS_GROUPS'].'">'.$_ARRAYLANG['TXT_ACCESS_GROUPS'].'</a>' : '').'
                                                            '.(Permission::checkAccess(18, 'static', true) ? '<a href="index.php?cmd=access&amp;act=config" title="'.$_ARRAYLANG['TXT_ACCESS_SETTINGS'].'">'.$_ARRAYLANG['TXT_ACCESS_SETTINGS'].'</a>' : ''));
    }


  /**
    * export users of a group as CSV
    *
    * @param integer $groupId
    */
    function _exportUsers($groupId = 0, $langId = null)
    {
        global $_CORELANG, $objInit;

        $csvSeparator = ";";
        $groupId = intval($groupId);

        $objFWUser = FWUser::getFWUserObject();
        $arrLangs = FWLanguage::getLanguageArray();

        if($groupId){
            $objGroup = $objFWUser->objGroup->getGroup($groupId);
            $groupName = $objGroup->getName(LANG_ID);
        }else{
            $groupName = $_CORELANG['TXT_USER_ALL'];
        }

        header("Content-Type: text/comma-separated-values", true);
        header("Content-Disposition: attachment; filename=\"".str_replace(array(' ',',','.','\'','"'), '_', $groupName).($langId != null ? '_lang_'.$arrLangs[$langId]['lang'] : '').'.csv"', true);

        $arrFields = array ('active', 'frontend lang', 'backend lang', 'gender', 'title', 'firstname', 'lastname', 'username', 'email');
        foreach ($arrFields as $field) {
            print $this->_escapeCsvValue($field).$csvSeparator;
        }
        print "\n";

        $filter = array();
        if (!empty($groupId)) {
            $filter['group_id'] = $groupId;
        }
        if (!empty($langId)) {
            if (FWLanguage::getLanguageParameter($langId, 'is_default') == 'true') {
                $filter['frontend_lang_id'] = array($langId, 0);
            } else {
                $filter['frontend_lang_id'] = $langId;
            }
        }
        $objUser = $objFWUser->objUser->getUsers($filter, null, array('username'), array('active', 'frontend_lang_id', 'backend_lang_id', 'gender', 'title', 'firstname', 'lastname', 'username', 'email'));
        if ($objUser) {
            while (!$objUser->EOF) {
                $activeStatus = $objUser->getActiveStatus() ? $_CORELANG['TXT_YES'] : $_CORELANG['TXT_NO'];

                $frontendLangId = $objUser->getFrontendLanguage();
                if (empty($frontendLangId)) {
                    $frontendLangId = $objInit->getDefaultFrontendLangId();
                }
                $frontendLang = $arrLangs[$frontendLangId]['name']." (".$arrLangs[$frontendLangId]['lang'].")";

                $backendLangId = $objUser->getBackendLanguage();
                if (empty($backendLangId)) {
                    $backendLangId = $objInit->getDefaultBackendLangId();
                }
                $backendLang = $arrLangs[$backendLangId]['name']." (".$arrLangs[$backendLangId]['lang'].")";

                // gender
                switch ($objUser->getProfileAttribute('gender')) {
                    case 'gender_male':
                       $gender = $_CORELANG['TXT_ACCESS_MALE'];
                    break;

                    case 'gender_female':
                       $gender = $_CORELANG['TXT_ACCESS_FEMALE'];
                    break;

                    default:
                       $gender = $_CORELANG['TXT_ACCESS_NOT_SPECIFIED'];
                    break;
                }

                // title
                $title = '';
                $objAttribute = $objFWUser->objUser->objAttribute->getById('title');
                foreach ($objAttribute->getChildren() as $childAttributeId) {
                    $objChildAtrribute = $objAttribute->getById($childAttributeId);
                    if ($objChildAtrribute->getMenuOptionValue() == $objUser->getProfileAttribute('title')) {
                        $title = $objChildAtrribute->getName();
                        break;
                    }
                }

                print $this->_escapeCsvValue($activeStatus).$csvSeparator;
                print $this->_escapeCsvValue($frontendLang).$csvSeparator;
                print $this->_escapeCsvValue($backendLang).$csvSeparator;
                print $this->_escapeCsvValue($gender).$csvSeparator;
                print $this->_escapeCsvValue($title).$csvSeparator;
                print $this->_escapeCsvValue($objUser->getProfileAttribute('firstname')).$csvSeparator;
                print $this->_escapeCsvValue($objUser->getProfileAttribute('lastname')).$csvSeparator;
                print $this->_escapeCsvValue($objUser->getUsername()).$csvSeparator;
                print $this->_escapeCsvValue($objUser->getEmail()).$csvSeparator;
                print "\n";

                $objUser->next();
            }
        }
        exit;
    }


    /**
     * Escape a value that it could be inserted into a csv file.
     *
     * @param string $value
     * @return string
     */
    function _escapeCsvValue($value)
    {
        $csvSeparator = ";";
        $value = in_array(strtolower(CONTREXX_CHARSET), array('utf8', 'utf-8')) ? utf8_decode($value) : $value;
        $value = preg_replace('/\r\n/', "\n", $value);
        $valueModified = str_replace('"', '""', $value);

        if ($valueModified != $value || preg_match('/['.$csvSeparator.'\n]+/', $value)) {
            $value = '"'.$valueModified.'"';
        }
        return $value;
    }


    /**
    * Get page
    *
    * @global HTML_Template_Sigma
    */
    public function getPage()
    {
        global $objTemplate;

        if (!isset($_REQUEST['act'])) {
            $_REQUEST['act'] = '';
        }

        if (!isset($_REQUEST['tpl'])) {
            $_REQUEST['tpl'] = '';
        }

        $objFWUser = FWUser::getFWUserObject();

        switch ($_REQUEST['act']) {
            case 'export':
                $_GET['groupId'] = !empty($_GET['groupId']) ? intval($_GET['groupId']) : 0;
                $this->_exportUsers($_GET['groupId'], $_GET['langId']);
            break;
            case 'user':
                if (Permission::checkAccess(18, 'static', true) || (isset($_REQUEST['id']) && $_REQUEST['id'] == $objFWUser->objUser->getId() && Permission::checkAccess(31, 'static', true))) {
                    $this->user();
                } else {
                    header('Location: index.php?cmd=noaccess');
                    exit;
                }
                break;

            case 'group':
                 Permission::checkAccess(18, 'static');
                $this->_group();
                break;

            case 'config':
                 Permission::checkAccess(18, 'static');
                $this->_config();
                break;

            default:
                 Permission::checkAccess(18, 'static');
                /*$this->overview();*/
                $this->user();
                break;
        }

        $objTemplate->setVariable(array(
            'CONTENT_TITLE'             => $this->_pageTitle,
            'CONTENT_OK_MESSAGE'        => implode("<br />\n", $this->arrStatusMsg['ok']),
            'CONTENT_STATUS_MESSAGE'    => implode("<br />\n", $this->arrStatusMsg['error']),
            'ADMIN_CONTENT'             => $this->_objTpl->get()
        ));
    }


    private function overview()
    {
        global $_ARRAYLANG;

        $this->_objTpl->loadTemplatefile('module_access_overview.html');
        $this->_pageTitle = $_ARRAYLANG['TXT_ACCESS_OVERVIEW'];

        $this->_objTpl->setVariable(array(
            'TXT_ACCESS_OVERVIEW'   => $_ARRAYLANG['TXT_ACCESS_OVERVIEW']
        ));
    }


    /**
     * User Management Page
     *
     * This is the main user management section
     */
    private function user()
    {
        global $_ARRAYLANG;

        $this->_objTpl->loadTemplatefile('module_access_user.html');

        $this->_objTpl->setVariable(array(
            'TXT_ACCESS_OVERVIEW'           => $_ARRAYLANG['TXT_ACCESS_OVERVIEW'],
            'TXT_ACCESS_CREATE_NEW_USER'    => $_ARRAYLANG['TXT_ACCESS_CREATE_NEW_USER']
        ));

        switch ($_REQUEST['tpl']) {
            case 'modify':
                $this->modifyUser();
                break;

            case 'changeStatus':
                $this->changeUserStatus();
                break;

            case 'delete':
                $this->_deleteUser();
                break;

            default:
                $this->userList();
                break;
        }
    }


    /**
     * Group Management Page
     *
     * This is the main group management section
     *
     */
    function _group()
    {
        global $_ARRAYLANG;

        $this->_objTpl->loadTemplatefile('module_access_group.html');

        $this->_objTpl->setVariable(array(
            'TXT_ACCESS_OVERVIEW'               => $_ARRAYLANG['TXT_ACCESS_OVERVIEW'],
            'TXT_ACCESS_CREATE_NEW_USER_GROUP'  => $_ARRAYLANG['TXT_ACCESS_CREATE_NEW_USER_GROUP']
        ));

        switch ($_REQUEST['tpl']) {
            case 'create':
                $this->_createGroup();
                break;

            case 'modify':
                $this->_modifyGroup();
                break;

            case 'changeStatus':
                $this->changeGroupStatus();
                break;

            case 'delete':
                $this->_deleteGroup();
                break;

            default:
                $this->_groupList();
                break;
        }
    }


    /**
     * Group Overview Page
     *
     * This section lists all registered groups.
     */
    function _groupList()
    {
        global $_ARRAYLANG, $_CORELANG, $_CONFIG;

        $arrLangs = FWLanguage::getLanguageArray();

        $this->_objTpl->addBlockfile('ACCESS_GROUP_TEMPLATE', 'module_access_group_list', 'module_access_group_list.html');
        $this->_pageTitle = $_ARRAYLANG['TXT_ACCESS_GROUPS'];
        $objFWUser = FWUser::getFWUserObject();

        $rowNr = 0;
        $limitOffset = isset($_GET['pos']) ? intval($_GET['pos']) : 0;
        $orderDirection = !empty($_GET['sort']) ? $_GET['sort'] : 'asc';
        $orderBy = !empty($_GET['by']) ? $_GET['by'] : 'group_name';
        $groupTypeFilter = isset($_GET['group_type_filter']) && !empty($_GET['group_type_filter']) && in_array($_GET['group_type_filter'], array('frontend', 'backend')) ? $_GET['group_type_filter'] : null;

        $this->_objTpl->setVariable(array(
            'TXT_ACCESS_GROUP_LIST' => $_ARRAYLANG['TXT_ACCESS_GROUP_LIST'],
            'TXT_ACCESS_USERS'          => $_ARRAYLANG['TXT_ACCESS_USERS'],
            'TXT_ACCESS_FUNCTIONS'      => $_ARRAYLANG['TXT_ACCESS_FUNCTIONS'],

            'TXT_ACCESS_CONFIRM_DELETE_GROUP'   => $_ARRAYLANG['TXT_ACCESS_CONFIRM_DELETE_GROUP'],
            'TXT_ACCESS_OPERATION_IRREVERSIBLE' => $_ARRAYLANG['TXT_ACCESS_OPERATION_IRREVERSIBLE'],
            'TXT_ACCESS_CHANGE_SORT_DIRECTION'  => $_ARRAYLANG['TXT_ACCESS_CHANGE_SORT_DIRECTION'],

            'ACCESS_SORT_ID'                    => ($orderBy == 'group_id' && $orderDirection == 'asc') ? 'desc' : 'asc',
            'ACCESS_SORT_STATUS'                => ($orderBy == 'is_active' && $orderDirection == 'asc') ? 'desc' : 'asc',
            'ACCESS_SORT_NAME'                  => ($orderBy == 'group_name' && $orderDirection == 'asc') ? 'desc' : 'asc',
            'ACCESS_SORT_DESCRIPTION'           => ($orderBy == 'group_description' && $orderDirection == 'asc') ? 'desc' : 'asc',
            'ACCESS_SORT_TYPE'                  => ($orderBy == 'type' && $orderDirection == 'asc') ? 'desc' : 'asc',
            'ACCESS_ID'                         => $_ARRAYLANG['TXT_ACCESS_ID'].($orderBy == 'group_id' ? $orderDirection == 'asc' ? ' &uarr;' : ' &darr;' : ''),
            'ACCESS_STATUS'                     => $_ARRAYLANG['TXT_ACCESS_STATUS'].($orderBy == 'is_active' ? $orderDirection == 'asc' ? ' &uarr;' : ' &darr;' : ''),
            'ACCESS_NAME'                       => $_ARRAYLANG['TXT_ACCESS_NAME'].($orderBy == 'group_name' ? $orderDirection == 'asc' ? ' &uarr;' : ' &darr;' : ''),
            'ACCESS_DESCRIPTION'                => $_ARRAYLANG['TXT_ACCESS_DESCRIPTION'].($orderBy == 'group_description' ? $orderDirection == 'asc' ? ' &uarr;' : ' &darr;' : ''),
            'ACCESS_TYPE'                       => $_ARRAYLANG['TXT_ACCESS_TYPE'].($orderBy == 'type' ? $orderDirection == 'asc' ? ' &uarr;' : ' &darr;' : ''),
            'ACCESS_SORT_BY'                    => $orderBy,
            'ACCESS_GROUP_TYPE_FILTER'          => $groupTypeFilter,
            'ACCESS_GROUP_TYPE_MENU'            => $this->getGroupTypeMenu($groupTypeFilter, 'name="group_type_filter" onchange="window.location.replace(\''.CSRF::enhanceURI('index.php?cmd=access&amp;act=group').'&amp;sort='.$orderDirection.'&amp;by='.$orderBy.'&amp;group_type_filter=\'+this.value)"')
        ));

        $this->_objTpl->setGlobalVariable(array(
            'TXT_ACCESS_MODIFY_GROUP'  => $_ARRAYLANG['TXT_ACCESS_MODIFY_GROUP'],
            'TXT_USER_ALL'             => $_CORELANG['TXT_USER_ALL'],
            'TXT_EXPORT'               => $_CORELANG['TXT_EXPORT'],
        ));

        $filter = empty($groupTypeFilter) ? array() : array('type' => $groupTypeFilter);

        $objGroup = $objFWUser->objGroup->getGroups($filter, array($orderBy => $orderDirection), null, $_CONFIG['corePagingLimit'], $limitOffset);
        while (!$objGroup->EOF) {
            foreach ($arrLangs as $arrLang) {

                $this->_objTpl->setVariable(array(
                    'ACCESS_GROUP_ID'           => $objGroup->getId(),
                    'ACCESS_LANG_ID'            => $arrLang['id'],
                    'ACCESS_LANG_NAME'          => $arrLang['lang'],
                ));

                $this->_objTpl->parse('languages');
            }

            $this->_objTpl->setVariable(array(
                'ACCESS_ROW_CLASS_ID'           => $rowNr % 2 ? 1 : 2,
                'ACCESS_GROUP_STATUS_IMG'       => $objGroup->getActiveStatus() ? 'led_green.gif' : 'led_red.gif',
                'ACCESS_GRoUP_STATUS'           => $objGroup->getActiveStatus() ? $_ARRAYLANG['TXT_ACCESS_ACTIVE'] : $_ARRAYLANG['TXT_ACCESS_INACTIVE'],
                'ACCESS_GROUP_NAME'             => $objGroup->getName(),
                'ACCESS_GROUP_NAME_ESCAPED'     => urlencode($objGroup->getName()),
                'ACCESS_GROUP_DESCRIPTION'      => $objGroup->getDescription(),
                'ACCESS_GROUP_TYPE'             => $objGroup->getType(),
                'ACCESS_GROUP_USER_COUNT'       => $objGroup->getUserCount(),
                'ACCESS_SHOW_USERS_OF_GROUP'    => sprintf($_ARRAYLANG['TXT_ACCESS_SHOW_USERS_OF_GROUP'], htmlentities($objGroup->getName(), ENT_QUOTES, CONTREXX_CHARSET)),
                'ACCESS_GROUP_ID'               => $objGroup->getId(),
                'ACCESS_DELETE_GROUP'           => sprintf($_ARRAYLANG['TXT_ACCESS_DELETE_GROUP'], htmlentities($objGroup->getName(), ENT_QUOTES, CONTREXX_CHARSET)),
                'ACCESS_CHANGE_GROUP_STATUS_MSG'    => sprintf($objGroup->getActiveStatus() ? $_ARRAYLANG['TXT_ACCESS_DEACTIVATE_GROUP'] : $_ARRAYLANG['TXT_ACCESS_ACTIVATE_GROUP'], htmlentities($objGroup->getName(), ENT_QUOTES, CONTREXX_CHARSET))
            ));

            $this->_objTpl->parse('access_group_list');
            $rowNr++;

            $objGroup->next();
        }

        if ($objGroup->getGroupCount() > $_CONFIG['corePagingLimit']) {
            $this->_objTpl->setVariable('ACCESS_GROUP_PAGING', getPaging($objGroup->getGroupCount($filter), $limitOffset, '&amp;cmd=access&amp;act=group&amp;sort='.$orderDirection.'&amp;by='.$orderBy.'&amp;group_type_filter='.$groupTypeFilter, $_ARRAYLANG['TXT_ACCESS_GROUPS']));
        }

        $this->_objTpl->parse('module_access_group_list');
    }


    /**
     * Create Group Page
     *
     * This section is used to create a new group.
     *
     * @return unknown
     */
    function _createGroup()
    {
        global $_ARRAYLANG;

        if (isset($_POST['access_create_group'])) {
            $objFWUser = FWUser::getFWUserObject();
            if (!empty($_POST['access_group_type']) && in_array($_POST['access_group_type'], $objFWUser->objGroup->getTypes())) {
                $this->_modifyGroup();
                return;
            } else {
                $this->arrStatusMsg['error'][] = $_ARRAYLANG['TXT_ACCESS_SELECT_A_VALID_GROUP_TYPE'];
            }
        }

        $this->_objTpl->addBlockfile('ACCESS_GROUP_TEMPLATE', 'module_access_group_create', 'module_access_group_create.html');

        $this->_objTpl->setVariable(array(
            'TXT_ACCESS_CREATE_GROUP'               => $_ARRAYLANG['TXT_ACCESS_CREATE_GROUP'],
            'TXT_ACCESS_CREATE_GROUP_TYPE_QUESTION' => $_ARRAYLANG['TXT_ACCESS_CREATE_GROUP_TYPE_QUESTION'],
            'TXT_ACCESS_FRONTEND_DESC'              => $_ARRAYLANG['TXT_ACCESS_FRONTEND_DESC'],
            'TXT_ACCESS_BACKEND_DESC'               => $_ARRAYLANG['TXT_ACCESS_BACKEND_DESC'],
            'TXT_ACCESS_CANCEL'                     => $_ARRAYLANG['TXT_ACCESS_CANCEL'],
            'TXT_ACCESS_NEXT'                       => $_ARRAYLANG['TXT_ACCESS_NEXT']
        ));

        $this->_pageTitle = $_ARRAYLANG['TXT_ACCESS_CREATE_NEW_USER_GROUP'];

        $this->_objTpl->parse('module_access_group_create');
    }


    /**
     * Modify Group Page
     *
     * This page shows the dialog to modify a group.
     *
     * @return unknown
     */
    function _modifyGroup()
    {
        global $_ARRAYLANG, $_CORELANG, $objDatabase;

        require_once ASCMS_CORE_PATH.'/Tree.class.php';
        $objContentTree = new ContentTree();

        $arrAreas = array();
        $associatedUsers = '';
        $notAssociatedUsers = '';
        $changeProtection = false;
        $scrollPos = 0;
        $objFWUser = FWUser::getFWUserObject();

        $objGroup = $objFWUser->objGroup->getGroup(isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0);
        if (isset($_POST['access_save_group']) || isset($_POST['access_change_protection'])) {
            // only administrators are allowed to modify a group
            if (!Permission::hasAllAccess()) {
                Permission::noAccess();
            }

            $objGroup->setName(!empty($_POST['access_group_name']) ? trim(contrexx_stripslashes($_POST['access_group_name'])) : '');
            $objGroup->setDescription(!empty($_POST['access_group_description']) ? trim(contrexx_stripslashes($_POST['access_group_description'])) : '');
            $objGroup->setActiveStatus(isset($_POST['access_group_status']) ? (bool)$_POST['access_group_status'] : false);
            $objGroup->setType(!empty($_POST['access_group_type']) ? $_POST['access_group_type'] : '');
            $objGroup->setHomepage(!empty($_POST['access_group_homepage']) ? trim(contrexx_stripslashes($_POST['access_group_homepage'])) : '');
            $objGroup->setUsers(isset($_POST['access_group_associated_users']) && is_array($_POST['access_group_associated_users']) ? $_POST['access_group_associated_users'] : array());
            $objGroup->setStaticPermissionIds(isset($_POST['access_area_id']) && is_array($_POST['access_area_id']) ? $_POST['access_area_id'] : array());

            // set dynamic access ids
            foreach ($objContentTree->getTree() as $arrPage) {
                if ($arrPage[$objGroup->getType().'_access_id']) {
                    $arrContentAccessIds[] = $arrPage[$objGroup->getType().'_access_id'];
                }
            }
            $arrNewAccessIds = isset($_POST['access_webpage_access_id']) && is_array($_POST['access_webpage_access_id']) ? $_POST['access_webpage_access_id'] : array();
            $arrCurrentAccessIds = $objGroup->getDynamicPermissionIds();
            foreach ($arrContentAccessIds as $accessId) {
                // add new access ids
                if (in_array($accessId, $arrNewAccessIds) && !in_array($accessId, $arrCurrentAccessIds)) {
                    $arrCurrentAccessIds[] = $accessId;
                }

                // delete access ids
                if (!in_array($accessId, $arrNewAccessIds) && in_array($accessId, $arrCurrentAccessIds)) {
                    unset($arrCurrentAccessIds[array_search($accessId, $arrCurrentAccessIds)]);
                }
            }
            $objGroup->setDynamicPermissionIds($arrCurrentAccessIds);

            if (isset($_POST['access_save_group'])) {
                if ($objGroup->store()) {
                    $this->arrStatusMsg['ok'][] = $_ARRAYLANG['TXT_ACCESS_GROUP_STORED_SUCCESSFULLY'];
                    $objFWUser->objUser->getDynamicPermissionIds(true);
                    $objFWUser->objUser->getStaticPermissionIds(true);
                    $this->_groupList();
                    return;
                } else {
                    $this->arrStatusMsg['error'][] = $objGroup->getErrorMsg();
                }
            } else {
                $changeProtection = true;

                $pageId = isset($_GET['pageId']) ? intval($_GET['pageId']) : 0;
                $protect = isset($_GET['protect']) && $_GET['protect'] == 'false' ? false : true;
                if (!$this->_changePageProtection($pageId, $protect, $objGroup->getType())) {
                    $this->arrStatusMsg['error'][] = $protect ? $_ARRAYLANG['TXT_ACCESS_FAILED_PROTECT_PAGE'] : $_ARRAYLANG['TXT_ACCESS_FAILED_RELEASE_PAGE'];
                }
            }

            if (isset($_GET['scrollPos'])) {
                $scrollPos = intval($_GET['scrollPos']);
            }
        } elseif (isset($_POST['access_create_group'])) {
            $objGroup->setType(isset($_POST['access_group_type']) ? $_POST['access_group_type'] : '');
        }

        $this->_objTpl->addBlockfile('ACCESS_GROUP_TEMPLATE', 'module_access_group_modify', 'module_access_group_modify.html');
        $this->_pageTitle = $objGroup->getId() ? $_ARRAYLANG['TXT_ACCESS_MODIFY_GROUP'] : $_ARRAYLANG['TXT_ACCESS_CREATE_NEW_USER_GROUP'];

        $objUser = $objFWUser->objUser->getUsers(null, null, array('username' => 'asc'), array('id', 'username', 'firstname', 'lastname'));
        if ($objUser) {
            $arrGroupUsers = $objGroup->getAssociatedUserIds();
            while (!$objUser->EOF) {
                $arrUsers[] = $objUser->getId();
                $objUser->next();
            }

            $arrOtherUsers = array_diff($arrUsers, $arrGroupUsers);

            foreach ($arrGroupUsers as $uId) {
                $objUser = $objFWUser->objUser->getUser($uId);
                if ($objUser) {
                    $associatedUsers .= "<option value=\"".$objUser->getId()."\">".htmlentities($objUser->getUsername(), ENT_QUOTES, CONTREXX_CHARSET).(($objUser->getProfileAttribute('lastname') != '' || $objUser->getProfileAttribute('firstname') != '')  ? " (".htmlentities($objUser->getProfileAttribute('firstname'), ENT_QUOTES, CONTREXX_CHARSET)." ".htmlentities($objUser->getProfileAttribute('lastname'), ENT_QUOTES, CONTREXX_CHARSET).")" : '')."</option>\n";
                }
            }
            foreach ($arrOtherUsers as $uId) {
                $objUser = $objFWUser->objUser->getUser($uId);
                if ($objUser) {
                    $notAssociatedUsers .= "<option value=\"".$objUser->getId()."\">".htmlentities($objUser->getUsername(), ENT_QUOTES, CONTREXX_CHARSET).(($objUser->getProfileAttribute('lastname') != '' || $objUser->getProfileAttribute('firstname') != '')  ? " (".htmlentities($objUser->getProfileAttribute('firstname'), ENT_QUOTES, CONTREXX_CHARSET)." ".htmlentities($objUser->getProfileAttribute('lastname'), ENT_QUOTES, CONTREXX_CHARSET).")" : '')."</option>\n";
                }
            }
        }

        $this->_objTpl->setVariable('ACCESS_WEBSITE_TAB_NAME', 'Webseiten');

        $arrModules = array();
        $objResult = $objDatabase->Execute("SELECT id, name FROM ".DBPREFIX."modules");
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $arrModules[$objResult->fields['id']] = $objResult->fields['name'];
                $objResult->MoveNext();
            }
        }

        $this->_objTpl->setGlobalVariable(array(
            'TXT_ACCESS_SHOW_PAGE_IN_NEW_DOCUMENT'      => $_ARRAYLANG['TXT_ACCESS_SHOW_PAGE_IN_NEW_DOCUMENT'],
            'TXT_ACCESS_MODIFY_PAGE_IN_NEW_DOCUMENT'    => $_ARRAYLANG['TXT_ACCESS_MODIFY_PAGE_IN_NEW_DOCUMENT'],
            'TXT_ACCESS_CHECK_ALL'                      => $_ARRAYLANG['TXT_ACCESS_CHECK_ALL'],
            'TXT_ACCESS_UNCHECK_ALL'                    => $_ARRAYLANG['TXT_ACCESS_UNCHECK_ALL']
        ));

        $rowNr = 0;
        foreach ($objContentTree->getTree() as $arrPage) {
            $s = isset($arrModules[$arrPage['moduleid']]) ? $arrModules[$arrPage['moduleid']] : '';
            $c = $arrPage['cmd'];
            $section = ($s=="") ? "" : "&amp;section=$s";
            $cmd = ($c=="") ? "" : "&amp;cmd=$c";
            $link = (!empty($s)) ? "?section=".$s.$cmd : "?page=".$arrPage['catid'].$section.$cmd;

            $this->_objTpl->setGlobalVariable('ACCESS_WEBPAGE_ID', $arrPage['catid']);

            $this->_objTpl->setVariable(array(
                'ACCESS_WEBPAGE_ACCESS_ID'  => $arrPage[$objGroup->getType().'_access_id'],
                'ACCESS_WEBPAGE_STYLE_NR'   => $rowNr % 2 + 1,
                'ACCESS_WEBPAGE_TEXT_INDENT'=> $arrPage['level'] * 20,
                'ACCESS_WEBPAGE_NAME'       => htmlentities($arrPage['catname'], ENT_QUOTES, CONTREXX_CHARSET),
                'ACCESS_WEBPAGE_LINK'       => ASCMS_PATH_OFFSET.'/index.php'.$link,
                'ACCESS_CLICK_TO_CHANGE_PROTECTION_TXT' => $objGroup->getType() == 'backend' ? ($arrPage[$objGroup->getType().'_access_id'] > 0 ? $_ARRAYLANG['TXT_ACCESS_CLICK_UNLOCK_PAGE_MODIF'] : $_ARRAYLANG['TXT_ACCESS_CLICK_LOCK_PAGE_MODIFY']) : ($arrPage[$objGroup->getType().'_access_id'] > 0 ? $_ARRAYLANG['TXT_ACCESS_CLICK_UNLOCK_PAGE_ACCESS'] : $_ARRAYLANG['TXT_ACCESS_CLICK_LOCK_PAGE_ACCESS'])
            ));

            if ($arrPage[$objGroup->getType().'_access_id'] > 0) {
                $this->_objTpl->setVariable(array(
                    'ACCESS_WEBPAGE_ALLOWED'    => in_array($arrPage[$objGroup->getType().'_access_id'], $objGroup->getDynamicPermissionIds()) ? 'checked="checked"' : '',
                    'ACCESS_WEBPAGE_STATUS_IMG' => 'lock_closed.gif',
                    'ACCESS_WEBPAGE_STATUS_TXT' => $_ARRAYLANG['TXT_ACCESS_PROTECTED'],
                    'ACCESS_WEBPAGE_STATUS'     => 1
                ));
                $this->_objTpl->parse('access_permission_webpage_box');
            } else {
                $this->_objTpl->setVariable(array(
                    'ACCESS_WEBPAGE_STATUS_IMG' => 'lock_open.gif',
                    'ACCESS_WEBPAGE_STATUS_TXT' => $_ARRAYLANG['TXT_ACCESS_PUBLIC'],
                    'ACCESS_WEBPAGE_STATUS'     => 0
                ));
                $this->_objTpl->hideBlock('access_permission_webpage_box');
            }

            $this->_objTpl->parse('access_permission_website');

            $rowNr++;
        }

        $objResult = $objDatabase->Execute("
            SELECT
                `area_id`,
                `area_name`,
                `access_id`,
                `is_active`,
                `type`,
                `scope`,
                `parent_area_id`
            FROM `".DBPREFIX."backend_areas`
            WHERE `is_active` = 1
            ORDER BY `parent_area_id`, `order_id`
            ");
        if ($objResult) {
            while (!$objResult->EOF) {
                $arrAreas[$objResult->fields['area_id']] = array(
                    'name'      => $objResult->fields['area_name'],
                    'access_id' => $objResult->fields['access_id'],
                    'status'    => $objResult->fields['is_active'],
                    'type'      => $objResult->fields['type'],
                    'scope'     => $objResult->fields['scope'],
                    'group_id'  => $objResult->fields['parent_area_id'],
                    'allowed'   => in_array($objResult->fields['access_id'], $objGroup->getStaticPermissionIds()) ? 1 : 0
                );
                $objResult->MoveNext();
            }
        }

        $tabNr = 1;
        foreach ($arrAreas AS $groupId => $arrAreaGroup ) {
            if ($arrAreaGroup['type'] == 'group') {
                $arrAreaTree = array();
                if ($arrAreaGroup['scope'] == $objGroup->getType() || $arrAreaGroup['scope'] == 'global') {
                    $arrAreaTree[] = array($groupId, $objGroup->getType());
                    $groupParsed = true;
                } else {
                    $groupParsed = false;
                }
                foreach ($arrAreas AS $navigationId => $arrAreaNavigation) {
                    if ($groupId == $arrAreaNavigation['group_id'] && $arrAreaNavigation['type'] == 'navigation') {
                        if ($arrAreaNavigation['scope'] == $objGroup->getType() || $arrAreaNavigation['scope'] == 'global') {
                            if (!$groupParsed) {
                                $arrAreaTree[] = array($groupId, $objGroup->getType());
                                $groupParsed = true;
                            }
                            $arrAreaTree[] = array($navigationId, $objGroup->getType());
                            $navigationParsed = true;
                        } else {
                            $navigationParsed = false;
                        }
                        foreach ($arrAreas AS $functionId => $arrAreaFunction) {
                            if ($navigationId == $arrAreaFunction['group_id'] && $arrAreaFunction['type'] == 'function') {
                                if ($arrAreaFunction['scope'] == $objGroup->getType() || $arrAreaFunction['scope'] == 'global') {
                                    if (!$navigationParsed) {
                                        if (!$groupParsed) {
                                            $arrAreaTree[] = array($groupId, $objGroup->getType());
                                            $groupParsed = true;
                                        }
                                        $arrAreaTree[] = array($navigationId, $objGroup->getType());
                                        $navigationParsed = true;
                                    }
                                    $arrAreaTree[] = array($functionId, $objGroup->getType());
                                }
                            }
                        }
                    }
                }

                if ($groupParsed) {
                    foreach ($arrAreaTree as $arrArea) {
                        $this->_parsePermissionAreas($arrAreas, $arrArea[0], $arrArea[1]);
                    }

                $this->_objTpl->setGlobalVariable('ACCESS_TAB_NR', $tabNr);
                    $this->_objTpl->setVariable('ACCESS_TAB_NAME', isset($_CORELANG[$arrAreaGroup['name']]) ? htmlentities($_CORELANG[$arrAreaGroup['name']], ENT_QUOTES, CONTREXX_CHARSET) : $arrAreaGroup['name']);
                $this->_objTpl->parse('access_permission_tab_menu');
                $this->_objTpl->parse('access_permission_tabs');
                $tabNr++;
                }
            }
        }
        if ($tabNr > 1) {
            $this->_objTpl->parse('access_permission_tabs_menu');
        } else {
            $this->_objTpl->hideBlock('access_permission_tabs_menu');
        }

        $this->attachJavaScriptFunction('accessSetWebpage');
        $this->attachJavaScriptFunction('accessSelectAllGroups');
        $this->attachJavaScriptFunction('accessDeselectAllGroups');
        $this->attachJavaScriptFunction('accessAddGroupToList');
        $this->attachJavaScriptFunction('accessRemoveGroupFromList');

        $this->_objTpl->setVariable(array(
            'TXT_ACCESS_GENERAL'            => $_ARRAYLANG['TXT_ACCESS_GENERAL'],
            'TXT_ACCESS_PERMISSIONS'        => $_ARRAYLANG['TXT_ACCESS_PERMISSIONS'],
            'TXT_ACCESS_NAME'               => $_ARRAYLANG['TXT_ACCESS_NAME'],
            'TXT_ACCESS_DESCRIPTION'        => $_ARRAYLANG['TXT_ACCESS_DESCRIPTION'],
            'TXT_ACCESS_STATUS'             => $_ARRAYLANG['TXT_ACCESS_STATUS'],
            'TXT_ACCESS_ACTIVE'             => $_ARRAYLANG['TXT_ACCESS_ACTIVE'],
            'TXT_ACCESS_CANCEL'             => $_ARRAYLANG['TXT_ACCESS_CANCEL'],
            'TXT_ACCESS_SAVE'               => $_ARRAYLANG['TXT_ACCESS_SAVE'],
            'TXT_ACCESS_USERS'              => $_ARRAYLANG['TXT_ACCESS_USERS'],
            'TXT_ACCESS_AVAILABLE_USERS'    => $_ARRAYLANG['TXT_ACCESS_AVAILABLE_USERS'],
            'TXT_ACCESS_CHECK_ALL'          => $_ARRAYLANG['TXT_ACCESS_CHECK_ALL'],
            'TXT_ACCESS_UNCHECK_ALL'        => $_ARRAYLANG['TXT_ACCESS_UNCHECK_ALL'],
            'TXT_ACCESS_ASSOCIATED_USERS'   => $_ARRAYLANG['TXT_ACCESS_ASSOCIATED_USERS'],
            'TXT_ACCESS_UNPROTECT_PAGE'     => $_ARRAYLANG['TXT_ACCESS_UNPROTECT_PAGE'],
            'TXT_ACCESS_PROTECT_PAGE'       => $_ARRAYLANG['TXT_ACCESS_PROTECT_PAGE'],
            'TXT_ACCESS_PROMT_EXEC_WARNING' => $_ARRAYLANG['TXT_ACCESS_PROMT_EXEC_WARNING'],
            'TXT_ACCESS_HOMEPAGE'           => $_ARRAYLANG['TXT_ACCESS_HOMEPAGE'],
            'TXT_ACCESS_HOMEPAGE_DESC'      => $_ARRAYLANG['TXT_ACCESS_HOMEPAGE_DESC'],
            'TXT_ACCESS_BROWSE'             => $_ARRAYLANG['TXT_ACCESS_BROWSE']
        ));

        $this->_objTpl->setVariable(array(
            'ACCESS_GROUP_ID'                   => $objGroup->getId(),
            'ACCESS_GROUP_NAME'                 => htmlentities($objGroup->getName(), ENT_QUOTES, CONTREXX_CHARSET),
            'ACCESS_GROUP_DESCRIPTION'          => htmlentities($objGroup->getDescription(), ENT_QUOTES, CONTREXX_CHARSET),
            'ACCESS_GROUP_STATUS'               => $objGroup->getActiveStatus() ? 'checked="checked"' : '',
            'ACCESS_GROUP_TYPE'                 => $objGroup->getType(),
            'ACCESS_GROUP_HOMEPAGE'             => $objGroup->getHomepage(),
            'ACCESS_GROUP_NOT_ASSOCIATED_USERS' => $notAssociatedUsers,
            'ACCESS_GROUP_ASSOCIATED_USERS'     => $associatedUsers,
            'ACCESS_PROTECT_PAGE_TXT'           => $objGroup->getType() == 'backend' ? $_ARRAYLANG['TXT_ACCESS_CONFIRM_LOCK_PAGE'] : $_ARRAYLANG['TXT_ACCESS_CONFIRM_PROTECT_PAGE'],
            'ACCESS_UNPROTECT_PAGE_TXT'         => $objGroup->getType() == 'backend' ? $_ARRAYLANG['TXT_ACCESS_CONFIRM_UNLOCK_PAGE'] : $_ARRAYLANG['TXT_ACCESS_CONFIRM_UNPROTECT_PAGE'],
            'ACCESS_GENERAL_TAB_MENU_STATUS'    => !$changeProtection ? 'class="active"' : '',
            'ACCESS_GENERAL_TAB_STATUS'         => !$changeProtection ? 'block' : 'none',
            'ACCESS_PERMISSION_TAB_MENU_STATUS' => $changeProtection ? 'class="active"' : '',
            'ACCESS_PERMISSION_TAB_STATUS'      => $changeProtection ? 'block' : 'none',
            'ACCESS_SCROLL_POS'                 => $scrollPos,
            'ACCESS_JAVASCRIPT_FUNCTIONS'       => $this->getJavaScriptCode()
        ));
        $this->_objTpl->parse('module_access_group_modify');
    }


    function _changePageProtection($pageId, $protect = true, $type)
    {
        global $objDatabase, $_CONFIG;

        $objFWUser = FWUser::getFWUserObject();

        if (in_array($type, $objFWUser->objGroup->getTypes())) {
            $objAccess = $objDatabase->SelectLimit('SELECT `'.$type.'_access_id` FROM `'.DBPREFIX.'content_navigation` WHERE `catid` = '.$pageId, 1);
            if ($objAccess !== false && $objAccess->RecordCount() == 1) {
                $currentAccessId = $objAccess->fields[$type.'_access_id'];
            } else {
                return false;
            }

            if ($protect) {
                $lastAccessId = $_CONFIG['lastAccessId'];
                $lastAccessId++;
                if ($objDatabase->Execute('UPDATE `'.DBPREFIX.'content_navigation` SET '.($type == 'frontend' ? '`protected` = 1, ' : '').'`'.$type.'_access_id` = '.$lastAccessId.' WHERE `catid` = '.$pageId) !== false) {
                    $_CONFIG['lastAccessId'] = $lastAccessId;
                    $objDatabase->Execute("UPDATE `".DBPREFIX."settings` SET `setvalue` = ".$lastAccessId." WHERE `setname` = 'lastAccessId'");

                    require_once(ASCMS_CORE_PATH.'/settings.class.php');
                    $objSettings = new settingsManager();
                    $objSettings->writeSettingsFile();

                    return true;
                }
            } else {
                if ($objDatabase->Execute('DELETE FROM `'.DBPREFIX.'access_group_dynamic_ids` WHERE `access_id` = '.$currentAccessId)) {
                    return $objDatabase->Execute('UPDATE `'.DBPREFIX.'content_navigation` SET '.($type == 'frontend' ? '`protected` = 0, ' : '').'`'.$type.'_access_id` = 0 WHERE `catid` = '.$pageId);
                }
            }
        }
        return false;
    }


    function _parsePermissionAreas($arrAreas, $areaId, $scope)
    {
        global $_CORELANG;

        $this->_objTpl->setVariable(array(
            'ACCESS_AREA_ID'            => $arrAreas[$areaId]['access_id'],
            'ACCESS_AREA_NAME'          => isset($_CORELANG[$arrAreas[$areaId]['name']]) ? htmlentities($_CORELANG[$arrAreas[$areaId]['name']], ENT_QUOTES, CONTREXX_CHARSET) : $arrAreas[$areaId]['name'],
            'ACCESS_AREA_STYLE_NR'      => $arrAreas[$areaId]['type'] == 'group' ? 3 : ($arrAreas[$areaId]['type'] == 'navigation' ? 1 : 2),
            'ACCESS_AREA_TEXT_INDENT'   => $arrAreas[$areaId]['type'] == 'group' ? 0 : ($arrAreas[$areaId]['type'] == 'navigation' ? 20 : 40),
            'ACCESS_AREA_EXTRA_STYLE'   => $arrAreas[$areaId]['type'] == 'group' ? 'font-weight:bold;' : '',
        ));

        if ($arrAreas[$areaId]['scope'] == $scope || $arrAreas[$areaId]['scope'] == 'global') {
            $this->_objTpl->setVariable(array(
                'ACCESS_AREA_ID'            => $arrAreas[$areaId]['access_id'],
            'ACCESS_AREA_ALLOWED'       => $arrAreas[$areaId]['allowed'] ? 'checked="checked"' : ''
        ));
            $this->_objTpl->parse('access_permission_in_scope');

            $this->_objTpl->setVariable('ACCESS_AREA_ID', $arrAreas[$areaId]['access_id']);
            $this->_objTpl->parse('access_permission_access_id');
        } else {
            $this->_objTpl->hideBlock('access_permission_in_scope');
            $this->_objTpl->hideBlock('access_permission_access_id');
        }

        $this->_objTpl->parse('access_permission_area');
    }


    private function changeGroupStatus()
    {
        global $_ARRAYLANG;

        // only administrators are allowed to delete a group
        if (!Permission::hasAllAccess()) {
            Permission::noAccess();
        }

        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
        $objFWUser = FWUser::getFWUserObject();
        $objGroup = $objFWUser->objGroup->getGroup($id);
        if ($objGroup->getId()) {
            $objGroup->setActiveStatus(!$objGroup->getActiveStatus());
            if ($objGroup->store()) {
                $this->arrStatusMsg['ok'][] = sprintf($objGroup->getActiveStatus() ? $_ARRAYLANG['TXT_ACCESS_GROUP_ACTIVATED_SUCCESSFULLY'] : $_ARRAYLANG['TXT_ACCESS_GROUP_DEACTIVATED_SUCCESSFULLY'], $objGroup->getName());
            } else {
                $this->arrStatusMsg['error'][] = $objGroup->getErrorMsg();
            }
        } else {
            $this->arrStatusMsg['error'][] = sprintf($_ARRAYLANG['TXT_ACCESS_NO_GROUP_WITH_ID'], $id);
        }
        return $this->_groupList();
    }


    function _deleteGroup()
    {
        global $_ARRAYLANG;

        // only administrators are allowed to delete a group
        if (!Permission::hasAllAccess()) {
            Permission::noAccess();
        }

        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
        $objFWUser = FWUser::getFWUserObject();
        $objGroup = $objFWUser->objGroup->getGroup($id);
        if ($objGroup->getId()) {
            if ($objGroup->delete()) {
                $this->arrStatusMsg['ok'][] = sprintf($_ARRAYLANG['TXT_ACCESS_GROUP_SUCCESSFULLY_DELETED'], $objGroup->getName());
            } else {
                $this->arrStatusMsg['error'][] = $objGroup->getErrorMsg();
            }
        } else {
            $this->arrStatusMsg['error'][] = sprintf($_ARRAYLANG['TXT_ACCESS_NO_GROUP_WITH_ID'], $id);
        }
        return $this->_groupList();
    }


    private function userList()
    {
        global $_ARRAYLANG, $_CORELANG, $_CONFIG;

        // add this to a new section maybe named like "maintenance"
        $this->removeUselessImages();

        $this->_objTpl->addBlockfile('ACCESS_USER_TEMPLATE', 'module_access_user_overview', 'module_access_user_list.html');
        $this->_pageTitle = $_ARRAYLANG['TXT_ACCESS_USERS'];
        $objFWUser = FWUser::getFWUserObject();

        $rowNr = 0;
        $groupId = isset($_REQUEST['groupId']) ? intval($_REQUEST['groupId']) : 0;
        $limitOffset = isset($_GET['pos']) ? intval($_GET['pos']) : 0;
        $orderDirection = !empty($_GET['sort']) ? $_GET['sort'] : 'desc';
        $orderBy = !empty($_GET['by']) ? $_GET['by'] : 'regdate';
        $search = isset($_REQUEST['search']) && !empty($_REQUEST['search']) ? preg_split('#\s+#', $_REQUEST['search']) : array();
        $usernameFilter = isset($_REQUEST['username_filter']) && $_REQUEST['username_filter'] != '' && in_array(ord($_REQUEST['username_filter']), array_merge(array(48), range(65, 90))) ? $_REQUEST['username_filter'] : null;
        $userStatusFilter = isset($_REQUEST['user_status_filter']) && $_REQUEST['user_status_filter'] != '' ? intval($_REQUEST['user_status_filter']) : null;
        $userRoleFilter = isset($_REQUEST['user_role_filter']) && $_REQUEST['user_role_filter'] != '' ? intval($_REQUEST['user_role_filter']) : null;

        $this->_objTpl->setVariable(array(
            'TXT_ACCESS_CONFIRM_DELETE_USER'        => $_ARRAYLANG['TXT_ACCESS_CONFIRM_DELETE_USER'],
            'TXT_ACCESS_CONFIRM_USER_NOTIFY_ABOUT_ACCOUNT_STATUS_NAMED' => $_ARRAYLANG['TXT_ACCESS_CONFIRM_USER_NOTIFY_ABOUT_ACCOUNT_STATUS_NAMED'],
            'TXT_ACCESS_OPERATION_IRREVERSIBLE'     => $_ARRAYLANG['TXT_ACCESS_OPERATION_IRREVERSIBLE'],
            'TXT_ACCESS_SEARCH'                     => $_ARRAYLANG['TXT_ACCESS_SEARCH'],
            'TXT_ACCESS_USER_LIST'                  => $_ARRAYLANG['TXT_ACCESS_USER_LIST'],
            'ACCESS_GROUP_MENU'                     => $this->getGroupMenu($groupId, 'name="access_group_id" onchange="window.location.replace(\''.CSRF::enhanceURI('index.php?cmd=access').'&amp;act=user&amp;groupId=\'+this.value+\'&amp;sort='.htmlspecialchars($orderDirection).'&amp;by='.htmlspecialchars($orderBy).'\')"'),
            'ACCESS_USER_STATUS_MENU'               => $this->getUserStatusMenu($userStatusFilter, 'name="user_status_filter" onchange="window.location.replace(\''.CSRF::enhanceURI('index.php?cmd=access').'&amp;act=user&amp;groupId='.$groupId.'&amp;sort='.htmlspecialchars($orderDirection).'&amp;by='.htmlspecialchars($orderBy).'&amp;user_status_filter=\'+this.value+\'&amp;user_role_filter='.$userRoleFilter.'\')"'),
            'ACCESS_USER_ROLE_MENU'                 => $this->getUserRoleMenu($userRoleFilter, 'name="user_role_filter" onchange="window.location.replace(\''.CSRF::enhanceURI('index.php?cmd=access').'&amp;act=user&amp;groupId='.$groupId.'&amp;sort='.htmlspecialchars($orderDirection).'&amp;by='.htmlspecialchars($orderBy).'&amp;user_status_filter='.$userStatusFilter.'&amp;user_role_filter=\'+this.value)"'),
            'ACCESS_GROUP_IP'                       => $groupId,
            'ACCESS_SEARCH_VALUE'                   => htmlentities(join(' ', $search), ENT_QUOTES, CONTREXX_CHARSET),
            'ACCESS_SORT_DIRECTION'                 => $orderDirection,
            'ACCESS_SORT_BY'                        => $orderBy,
            'ACCESS_SEARCH_VALUE_ESCAPED'           => urlencode(implode(' ',$search)),
            'ACCESS_USER_USERNAME_FILTER_ESCAPED'   => urlencode($usernameFilter),
            'ACCESS_USER_STATUS_FILTER_ESCAPED'     => urlencode($userStatusFilter),
            'ACCESS_USER_ROLE_FILTER_ESCAPED'       => urlencode($userRoleFilter)
        ));

        $this->parseLetterIndexList('index.php?cmd=access&amp;act=user&amp;groupId='.$groupId.'&amp;user_status_filter='.$userStatusFilter.'&amp;user_role_filter='.$userRoleFilter, 'username_filter', $usernameFilter);

        $objGroup = $objFWUser->objGroup->getGroup($groupId);
        $userCount = $objGroup->getUserCount();
        $userFilter = array();
        if ($groupId) {
            $userFilter['group_id'] = $groupId;
        }
        if ($usernameFilter !== null) {
            $userFilter['username'] = array('REGEXP' => '^'.($usernameFilter == '0' ? '[0-9]|-|_' : $usernameFilter));
        }
        if ($userStatusFilter !== null) {
            $userFilter['active'] = $userStatusFilter;
        }
        if ($userRoleFilter !== null) {
            $userFilter['is_admin'] = $userRoleFilter;
        }
        if ($orderBy == 'expiration') {
            $arrOrder['special'] = 'field( tblU.`expiration`, 0'.($orderDirection == 'desc' ? ', tblU.`expiration`' : null).')';
        }
        $arrOrder[$orderBy] = $orderDirection;

        if ($userCount > 0 && ($objUser = $objFWUser->objUser->getUsers($userFilter, $search, $arrOrder, null, $_CONFIG['corePagingLimit'], $limitOffset)) && $userCount = $objUser->getFilteredSearchUserCount()) {

            if ($userCount > $_CONFIG['corePagingLimit']) {
                $this->_objTpl->setVariable('ACCESS_USER_PAGING', getPaging($userCount, $limitOffset, "&amp;cmd=access&amp;act=user&amp;groupId=".$groupId."&amp;sort=".htmlspecialchars($orderDirection)."&amp;by=".htmlspecialchars($orderBy)."&amp;search=".urlencode(urlencode(implode(' ',$search)))."&amp;username_filter=".$usernameFilter."&amp;user_status_filter=".$userStatusFilter."&amp;user_role_filter=".$userRoleFilter, "<b>".$_ARRAYLANG['TXT_ACCESS_USER']."</b>"));
            }

            $this->_objTpl->setVariable(array(
                'TXT_ACCESS_LANGUAGE'               => $_ARRAYLANG['TXT_ACCESS_LANGUAGE'],
                'TXT_ACCESS_ADMINISTRATOR'          => $_ARRAYLANG['TXT_ACCESS_ADMINISTRATOR'],
                'TXT_ACCESS_FUNCTIONS'              => $_ARRAYLANG['TXT_ACCESS_FUNCTIONS'],
                'TXT_ACCESS_CHANGE_SORT_DIRECTION'  => $_ARRAYLANG['TXT_ACCESS_CHANGE_SORT_DIRECTION'],
                'ACCESS_SORT_ID'                    => ($orderBy == 'id' && $orderDirection == 'asc') ? 'desc' : 'asc',
                'ACCESS_SORT_STATUS'                => ($orderBy == 'active' && $orderDirection == 'asc') ? 'desc' : 'asc',
                'ACCESS_SORT_USERNAME'              => ($orderBy == 'username' && $orderDirection == 'asc') ? 'desc' : 'asc',
                'ACCESS_SORT_COMPANY'               => ($orderBy == 'company' && $orderDirection == 'asc') ? 'desc' : 'asc',
                'ACCESS_SORT_FIRSTNAME'             => ($orderBy == 'firstname' && $orderDirection == 'asc') ? 'desc' : 'asc',
                'ACCESS_SORT_LASTNAME'              => ($orderBy == 'lastname' && $orderDirection == 'asc') ? 'desc' : 'asc',
                'ACCESS_SORT_EMAIL'                 => ($orderBy == 'email' && $orderDirection == 'asc') ? 'desc' : 'asc',
                'ACCESS_SORT_REGDATE'               => ($orderBy == 'regdate' && $orderDirection == 'asc') ? 'desc' : 'asc',
                'ACCESS_SORT_LAST_ACTIVITY'         => ($orderBy == 'last_activity' && $orderDirection == 'asc') ? 'desc' : 'asc',
                'ACCESS_SORT_EXPIRATION'            => ($orderBy == 'expiration' && $orderDirection == 'asc') ? 'desc' : 'asc',
                'ACCESS_ID'                         => $_ARRAYLANG['TXT_ACCESS_ID'].($orderBy == 'id' ? $orderDirection == 'asc' ? ' &uarr;' : ' &darr;' : ''),
                'ACCESS_STATUS'                     => $_ARRAYLANG['TXT_ACCESS_STATUS'].($orderBy == 'active' ? $orderDirection == 'asc' ? ' &uarr;' : ' &darr;' : ''),
                'ACCESS_USERNAME'                   => $_ARRAYLANG['TXT_ACCESS_USERNAME'].($orderBy == 'username' ? $orderDirection == 'asc' ? ' &uarr;' : ' &darr;' : ''),
                'ACCESS_COMPANY'                    => $_CORELANG['TXT_ACCESS_COMPANY'].($orderBy == 'company' ? $orderDirection == 'asc' ? ' &uarr;' : ' &darr;' : ''),
                'ACCESS_FIRSTNAME'                  => $_CORELANG['TXT_ACCESS_FIRSTNAME'].($orderBy == 'firstname' ? $orderDirection == 'asc' ? ' &uarr;' : ' &darr;' : ''),
                'ACCESS_LASTNAME'                   => $_CORELANG['TXT_ACCESS_LASTNAME'].($orderBy == 'lastname' ? $orderDirection == 'asc' ? ' &uarr;' : ' &darr;' : ''),
                'ACCESS_EMAIL'                      => $_ARRAYLANG['TXT_ACCESS_EMAIL'].($orderBy == 'email' ? $orderDirection == 'asc' ? ' &uarr;' : ' &darr;' : ''),
                'ACCESS_REGISTERED_SINCE'           => $_ARRAYLANG['TXT_ACCESS_REGISTERED_SINCE'].($orderBy == 'regdate' ? $orderDirection == 'asc' ? ' &uarr;' : ' &darr;' : ''),
                'ACCESS_LAST_ACTIVITY'              => $_ARRAYLANG['TXT_ACCESS_LAST_ACTIVITY'].($orderBy == 'last_activity' ? $orderDirection == 'asc' ? ' &uarr;' : ' &darr;' : ''),
                'ACCESS_EXPIRATION'                 => $_ARRAYLANG['TXT_ACCESS_VALIDITY_EXPIRATION'].($orderBy == 'expiration' ? $orderDirection == 'asc' ? ' &uarr;' : ' &darr;' : ''),
                'ACCESS_SEARCH_VALUE_ESCAPED'       => urlencode(implode(' ',$search)),
                'ACCESS_USER_USERNAME_FILTER'       => $usernameFilter,
                'ACCESS_USER_STATUS_FILTER'         => $userStatusFilter,
                'ACCESS_USER_ROLE_FILTER'           => $userRoleFilter
            ));

            $this->_objTpl->setGlobalVariable(array(
                'TXT_ACCESS_MODIFY_USER_ACCOUNT'    => $_ARRAYLANG['TXT_ACCESS_MODIFY_USER_ACCOUNT'],
                'ACCESS_GROUP_ID'                   => $groupId,
            ));

            while (!$objUser->EOF) {
                $firstname = $objUser->getProfileAttribute('firstname');
                $lastname = $objUser->getProfileAttribute('lastname');
                $company = $objUser->getProfileAttribute('company');

                $this->_objTpl->setVariable(array(
                    'ACCESS_ROW_CLASS_ID'               => $rowNr % 2 ? 1 : 0,
                    'ACCESS_USER_ID'                    => $objUser->getId(),
                    'ACCESS_USER_STATUS_IMG'            => $objUser->getActiveStatus() ? 'led_green.gif' : 'led_red.gif',
                    'ACCESS_USER_STATUS'                => $objUser->getActiveStatus() ? $_ARRAYLANG['TXT_ACCESS_ACTIVE'] : $_ARRAYLANG['TXT_ACCESS_INACTIVE'],
                    'ACCESS_USER_USERNAME'              => htmlentities($objUser->getUsername(), ENT_QUOTES, CONTREXX_CHARSET),
                    'ACCESS_USER_COMPANY'               => !empty($company) ? htmlentities($company, ENT_QUOTES, CONTREXX_CHARSET) : '&nbsp;',
                    'ACCESS_USER_FIRSTNAME'             => !empty($firstname) ? htmlentities($firstname, ENT_QUOTES, CONTREXX_CHARSET) : '&nbsp;',
                    'ACCESS_USER_LASTNAME'              => !empty($lastname) ? htmlentities($lastname, ENT_QUOTES, CONTREXX_CHARSET) : '&nbsp;',
                    'ACCESS_USER_EMAIL'                 => htmlentities($objUser->getEmail(), ENT_QUOTES, CONTREXX_CHARSET),
                    'ACCESS_SEND_EMAIL_TO_USER'         => sprintf($_ARRAYLANG['TXT_ACCESS_SEND_EMAIL_TO_USER'], htmlentities($objUser->getUsername(), ENT_QUOTES, CONTREXX_CHARSET)),
                    'ACCESS_USER_ADMIN_IMG'             => $objUser->getAdminStatus() ? 'admin.gif' : 'no_admin.gif',
                    'ACCESS_USER_ADMIN_TXT'             => $objUser->getAdminStatus() ? $_ARRAYLANG['TXT_ACCESS_ADMINISTRATOR'] : $_ARRAYLANG['TXT_ACCESS_NO_ADMINISTRATOR'],
                    'ACCESS_DELETE_USER_ACCOUNT'        => sprintf($_ARRAYLANG['TXT_ACCESS_DELETE_USER_ACCOUNT'],htmlentities($objUser->getUsername(), ENT_QUOTES, CONTREXX_CHARSET)),
                    'ACCESS_USER_REGDATE'               => date(ASCMS_DATE_SHORT_FORMAT, $objUser->getRegistrationDate()),
                    'ACCESS_USER_LAST_ACTIVITY'         => $objUser->getLastActivityTime() ? date(ASCMS_DATE_SHORT_FORMAT, $objUser->getLastActivityTime()) : '-',
                    'ACCESS_USER_EXPIRATION'            => $objUser->getExpirationDate() ? date(ASCMS_DATE_SHORT_FORMAT, $objUser->getExpirationDate()) : '-',
                    'ACCESS_USER_EXPIRATION_STYLE'      => $objUser->getExpirationDate() && $objUser->getExpirationDate() < time() ? 'color:#f00; font-weight:bold;' : null,
                    'ACCESS_CHANGE_ACCOUNT_STATUS_MSG'  => sprintf($objUser->getActiveStatus() ? $_ARRAYLANG['TXT_ACCESS_DEACTIVATE_USER'] : $_ARRAYLANG['TXT_ACCESS_ACTIVATE_USER'], htmlentities($objUser->getUsername(), ENT_QUOTES, CONTREXX_CHARSET))
                ));
                $rowNr++;
                $this->_objTpl->parse('access_user_list');
                $objUser->next();
            }
            $this->_objTpl->parse('access_has_users');
            $this->_objTpl->hideBlock('access_no_user');
        } else {
            $this->_objTpl->setVariable('ACCESS_STATUS_MSG', count($search) || $usernameFilter != '' ? $_ARRAYLANG['TXT_ACCESS_NO_USERS_FOUND'] : sprintf($_ARRAYLANG['TXT_ACCESS_NO_USER_IN_GROUP'], '&laquo;'.htmlentities($objGroup->getName(), ENT_QUOTES, CONTREXX_CHARSET).'&raquo;'));
            $this->_objTpl->parse('access_no_user');
            $this->_objTpl->hideBlock('access_has_users');
        }
        $this->_objTpl->parse('module_access_user_overview');
    }


    private function changeUserStatus()
    {
        global $_ARRAYLANG;

        // only administrators are allowed to delete a user account
        if (!Permission::hasAllAccess()) {
            Permission::noAccess();
        }

        $objFWUser = FWUser::getFWUserObject();
        if (isset($_GET['id']) && ($userId = intval($_GET['id'])) && ($objUser = $objFWUser->objUser->getUser($userId)) && $objUser->getId()) {
            $objUser->setActiveStatus(!$objUser->getActiveStatus());
            if ($objUser->store()) {
                if (isset($_GET['notifyUser']) && $_GET['notifyUser'] == '1') {
                    $this->notifyUserAboutAccountStatusChange($objUser);
                }
                $this->arrStatusMsg['ok'][] = sprintf($objUser->getActiveStatus() ? $_ARRAYLANG['TXT_ACCESS_USER_ACTIVATED_SUCCESSFULLY'] : $_ARRAYLANG['TXT_ACCESS_USER_DEACTIVATED_SUCCESSFULLY'], $objUser->getUsername());
            } else {
                $this->arrStatusMsg['error'] = array_merge($this->arrStatusMsg['error'], $objUser->getErrorMsg());
            }
        } else {
            $this->arrStatusMsg['error'][] = sprintf($_ARRAYLANG['TXT_ACCESS_NO_USER_WITH_ID'], $userId);
        }
        return $this->userList();
    }


    function _deleteUser()
    {
        global $_ARRAYLANG;

        // only administrators are allowed to delete a user account
        if (!Permission::hasAllAccess()) {
            Permission::noAccess();
        }

        $arrIds = !empty($_REQUEST['id']) ? is_array($_REQUEST['id']) ? $_REQUEST['id'] : array($_REQUEST['id']) : array();
        $objFWUser = FWUser::getFWUserObject();

        if (count($arrIds) > 0) {
            foreach ($arrIds as $id) {
                $objUser = $objFWUser->objUser->getUser($id);
                if ($objUser) {
                    if ($objUser->delete()) {
                        $this->arrStatusMsg['ok'][] = sprintf($_ARRAYLANG['TXT_ACCESS_USER_SUCCESSFULLY_DELETED'], $objUser->getUsername());
                    } else {
                        $this->arrStatusMsg['error'] = array_merge($this->arrStatusMsg['error'], $objUser->getErrorMsg());
                    }
                } else {
                    $this->arrStatusMsg['error'][] = sprintf($_ARRAYLANG['TXT_ACCESS_NO_USER_WITH_ID'], $id);
                }
            }
        }
        return $this->userList();
    }


    private function modifyUser()
    {
        global $_ARRAYLANG, $_CONFIG;

        $associatedGroups = '';
        $notAssociatedGroups = '';

        $objFWUser = FWUser::getFWUserObject();
        if (($objUser = $objFWUser->objUser->getUser(isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0)) === false) {
            $objUser = new User();
        }
        
        if ($objFWUser->objUser->getAdminStatus()) {   
            $cssDisplayStatus = 'none';
        } else {      
            $cssDisplayStatus = '';    
        }

        if (isset($_POST['access_save_user'])) {
            // only administrators are allowed to change a users account. or users may be allowed to change their own account
            if (!Permission::hasAllAccess() && ($objUser->getId() != $objFWUser->objUser->getId() || !Permission::checkAccess(31, 'static', true))) {
                Permission::noAccess();
            }

            $objUser->setUsername(isset($_POST['access_user_username']) ? trim(contrexx_stripslashes($_POST['access_user_username'])) : '');

            $objUser->setEmail(isset($_POST['access_user_email']) ? trim(contrexx_stripslashes($_POST['access_user_email'])) : '');
            $objUser->setFrontendLanguage(isset($_POST['access_user_frontend_language']) ? intval($_POST['access_user_frontend_language']) : 0);
            $objUser->setBackendLanguage(isset($_POST['access_user_backend_language']) ? intval($_POST['access_user_backend_language']) : 0);

            $oldActiveStatus = $objUser->getActiveStatus();
            $objUser->setActiveStatus(isset($_POST['access_user_active']) ? (bool)$_POST['access_user_active'] : false);

            $objUser->setEmailAccess(isset($_POST['access_user_email_access']) && $objUser->isAllowedToChangeEmailAccess() ? trim(contrexx_stripslashes($_POST['access_user_email_access'])) : '');
            $objUser->setProfileAccess(isset($_POST['access_user_profile_access']) && $objUser->isAllowedToChangeProfileAccess() ? trim(contrexx_stripslashes($_POST['access_user_profile_access'])) : '');

            $objUser->setNewsletterCategories($_POST['access_user_newsletters']);

            if (isset($_POST['access_profile_attribute']) && is_array($_POST['access_profile_attribute'])) {
                $arrProfile = $_POST['access_profile_attribute'];

                if (isset($_FILES['access_profile_attribute_images']) && is_array($_FILES['access_profile_attribute_images'])) {
                    $upload_res = $this->addUploadedImagesToProfile($objUser, $arrProfile, $_FILES['access_profile_attribute_images']);
                    if (is_array($upload_res)) {
                        $this->arrStatusMsg['error'] = array_merge($this->arrStatusMsg['error'], $upload_res);
                    }
                }

                $objUser->setProfile($arrProfile);
            }

            // only administrators are allowed to change the group assigement
            if (Permission::hasAllAccess()) {
                if (isset($_POST['access_user_associated_groups']) && is_array($_POST['access_user_associated_groups'])) {
                    $objUser->setGroups($_POST['access_user_associated_groups']);
                } else {
                    $objUser->setGroups(array());
                }
            }

            $objUser->setPrimaryGroup(isset($_POST['access_user_primary_group']) ? $_POST['access_user_primary_group'] : 0);

            if ($objUser->setPassword(isset($_POST['access_user_password']) ? trim(contrexx_stripslashes($_POST['access_user_password'])) : '', isset($_POST['access_user_password_confirmed']) ? trim(contrexx_stripslashes($_POST['access_user_password_confirmed'])) : '') &&
                // only administrators are allowed to change the admin status and the account validity
                (!Permission::hasAllAccess() || $objUser->getId() == $objFWUser->objUser->getId() || (
                    $objUser->setAdminStatus(isset($_POST['access_user_is_admin']) ? (bool)$_POST['access_user_is_admin'] : false) &&
                    // set validity expiration date (administrator accounts cannot be restricted in their validity)
                    (!isset($_POST['access_user_validity']) || $_REQUEST['access_user_validity'] == 'current' || $objUser->setValidityTimePeriod(intval($_POST['access_user_validity'])))
                )) &&
                // administrators aren't forced to fill out all mandatory profile attributes
                (Permission::hasAllAccess() || $objUser->checkMandatoryCompliance()) &&
                $objUser->store()
            ) {
                $this->arrStatusMsg['ok'][] = $_ARRAYLANG['TXT_ACCESS_USER_ACCOUNT_STORED_SUCCESSFULLY'];
                $objFWUser->objUser->getDynamicPermissionIds(true);
                $objFWUser->objUser->getStaticPermissionIds(true);

                if ($oldActiveStatus != $objUser->getActiveStatus() &&
                    isset($_POST['access_user_status_notification']) &&
                    $_POST['access_user_status_notification'] == '1'
                ) {
                    // notify user about the status (in-/ active) of his account
                    $this->notifyUserAboutAccountStatusChange($objUser);
                }

                // process module specific extensions
                $this->processModuleSpecificExtensions($objUser);

                if (Permission::checkAccess(18, 'static', true)) {
                    return $this->userList();
                }
            } else {
                $this->arrStatusMsg['error'] = array_merge($this->arrStatusMsg['error'], $objUser->getErrorMsg());
            }
        } elseif (!$objUser->getId()) {
            $objUser->setActiveStatus(true);
        }


        $this->_objTpl->addBlockfile('ACCESS_USER_TEMPLATE', 'module_access_user_modify', 'module_access_user_modify.html');

        if ($objUser->getId()) {
            $this->_pageTitle = $_ARRAYLANG['TXT_ACCESS_MODIFY_USER_ACCOUNT'];
            $this->_objTpl->touchBlock('access_user_active_notification_function_call');
        } else {
            $this->_pageTitle = $_ARRAYLANG['TXT_ACCESS_CREATE_USER_ACCOUNT'];
            $this->_objTpl->hideBlock('access_user_active_notification_function_call');
        }

        if (Permission::hasAllAccess()) {
            $objGroup = $objFWUser->objGroup->getGroups();
            while (!$objGroup->EOF) {
                $var = in_array($objGroup->getId(), $objUser->getAssociatedGroupIds()) ? 'associatedGroups' : 'notAssociatedGroups';
                $$var .= "<option value=\"".$objGroup->getId()."\">".htmlentities($objGroup->getName(), ENT_QUOTES, CONTREXX_CHARSET)." [".$objGroup->getType()."]</option>\n";

                $objGroup->next();
            }

            $this->_objTpl->touchBlock('access_profile_group_assignment');

            $this->attachJavaScriptFunction('accessSelectAllGroups');
            $this->attachJavaScriptFunction('accessDeselectAllGroups');
            $this->attachJavaScriptFunction('accessAddGroupToList');
            $this->attachJavaScriptFunction('accessRemoveGroupFromList');
            $this->attachJavaScriptFunction('accessAssignGroupToUser');
            $this->attachJavaScriptFunction('confirmUserNotification');
        } else {
            $this->_objTpl->hideBlock('access_profile_group_assignment');
        }

        $this->attachJavaScriptFunction('accessSetWebsite');
        $this->attachJavaScriptFunction('jscalendarIncludes');

        $this->_objTpl->setVariable(array(
            'TXT_ACCESS_USER_ACCOUNT'                   => $_ARRAYLANG['TXT_ACCESS_USER_ACCOUNT'],
            'TXT_ACCESS_USER_GROUP_S'                   => $_ARRAYLANG['TXT_ACCESS_USER_GROUP_S'],
            'TXT_ACCESS_PROFILE'                        => $_ARRAYLANG['TXT_ACCESS_PROFILE'],
            'TXT_ACCESS_NEWSLETTER_LISTS'               => $_ARRAYLANG['TXT_ACCESS_NEWSLETTER_LISTS'],
            'TXT_ACCESS_USERNAME'                       => $_ARRAYLANG['TXT_ACCESS_USERNAME'],
            'TXT_ACCESS_PASSWORD'                       => $_ARRAYLANG['TXT_ACCESS_PASSWORD'],
            'TXT_ACCESS_CONFIRM_PASSWORD'               => $_ARRAYLANG['TXT_ACCESS_CONFIRM_PASSWORD'],
            'TXT_ACCESS_EMAIL'                          => $_ARRAYLANG['TXT_ACCESS_EMAIL'],
            'TXT_ACCESS_LANGUAGE'                       => $_ARRAYLANG['TXT_ACCESS_LANGUAGE'],
            'TXT_ACCESS_ADMINISTRATOR'                  => $_ARRAYLANG['TXT_ACCESS_ADMINISTRATOR'],
            'TXT_ACCESS_PASSWORD_MINIMAL_CHARACTERS'    => $_ARRAYLANG['TXT_ACCESS_PASSWORD_MINIMAL_CHARACTERS'],
            'TXT_ACCESS_USER_ADMIN_RIGHTS'              => $_ARRAYLANG['TXT_ACCESS_USER_ADMIN_RIGHTS'],
            'TXT_ACCESS_PASSWORD_FIELD_EMPTY'           => $_ARRAYLANG['TXT_ACCESS_PASSWORD_FIELD_EMPTY'],
            'TXT_ACCESS_PASSWORD_MD5_ENCRYPTED'         => $_ARRAYLANG['TXT_ACCESS_PASSWORD_MD5_ENCRYPTED'],
            'TXT_ACCESS_AVAILABLE_GROUPS'               => $_ARRAYLANG['TXT_ACCESS_AVAILABLE_GROUPS'],
            'TXT_ACCESS_ASSOCIATED_GROUPS'              => $_ARRAYLANG['TXT_ACCESS_ASSOCIATED_GROUPS'],
            'TXT_ACCESS_PRIMARY_GROUP'                  => $_ARRAYLANG['TXT_ACCESS_PRIMARY_GROUP'],
            'TXT_ACCESS_CHECK_ALL'                      => $_ARRAYLANG['TXT_ACCESS_CHECK_ALL'],
            'TXT_ACCESS_UNCHECK_ALL'                    => $_ARRAYLANG['TXT_ACCESS_UNCHECK_ALL'],
            'TXT_ACCESS_SAVE'                           => $_ARRAYLANG['TXT_ACCESS_SAVE'],
            'TXT_ACCESS_CANCEL'                         => $_ARRAYLANG['TXT_ACCESS_CANCEL'],
            'TXT_ACCESS_CHANGE_WEBSITE'                 => $_ARRAYLANG['TXT_ACCESS_CHANGE_WEBSITE'],
            'TXT_ACCESS_VISIT_WEBSITE'                  => $_ARRAYLANG['TXT_ACCESS_VISIT_WEBSITE'],
            'TXT_ACCESS_NO_SPECIFIED'                   => $_ARRAYLANG['TXT_ACCESS_NO_SPECIFIED'],
            'TXT_ACCESS_CHANGE_PROFILE_PIC'             => $_ARRAYLANG['TXT_ACCESS_CHANGE_PROFILE_PIC'],
            'TXT_ACCESS_STATUS'                         => $_ARRAYLANG['TXT_ACCESS_STATUS'],
            'TXT_ACCESS_ACTIVE'                         => $_ARRAYLANG['TXT_ACCESS_ACTIVE'],
            'TXT_ACCESS_CONFIRM_OPEN_URL'               => $_ARRAYLANG['TXT_ACCESS_CONFIRM_OPEN_URL'],
            'TXT_ACCESS_URL_OPEN_RISK_MSG'              => $_ARRAYLANG['TXT_ACCESS_URL_OPEN_RISK_MSG'],
            'TXT_ACCESS_PRIVACY'                        => $_ARRAYLANG['TXT_ACCESS_PRIVACY'],
            'TXT_ACCESS_FRONTEND_DESC'                  => $_ARRAYLANG['TXT_ACCESS_FRONTEND_DESC'],
            'TXT_ACCESS_BACKEND_DESC'                   => $_ARRAYLANG['TXT_ACCESS_BACKEND_DESC'],
            'TXT_ACCESS_VALIDITY_EXPIRATION'            => $_ARRAYLANG['TXT_ACCESS_VALIDITY_EXPIRATION']
        ));

        $this->parseAccountAttributes($objUser, true);
        if ($objUser->isAllowedToChangeEmailAccess() || $objUser->isAllowedToChangeProfileAccess()) {
            $this->_objTpl->touchBlock('access_user_privacy');
        } else {
            $this->_objTpl->hideBlock('access_user_privacy');
        }

        $userID = $objUser->getId();
        //$this->parseNewsletterLists($userID);

        $this->_objTpl->setVariable(array(
            'ACCESS_USER_ID'                        => $objUser->getId(),
            'ACCESS_USER_IS_ADMIN'                  => $objUser->getAdminStatus() ? 'checked="checked"' : '',
            'ACCESS_USER_ACTIVE'                    => $objUser->getActiveStatus() ? 'checked="checked"' : '',
            'ACCESS_USER_NOT_ASSOCIATED_GROUPS'     => $notAssociatedGroups,
            'ACCESS_USER_ASSOCIATED_GROUPS'         => $associatedGroups,
            'ACCESS_USER_PRIMARY_GROUP_MENU'        => $this->getGroupMenu($objUser->getPrimaryGroupId(), 'name="access_user_primary_group" id="access_user_primary_group" onchange="accessAssignGroupToUser(this,document.getElementById(\'access_user_not_associated_groups\'),document.getElementById(\'access_user_associated_groups\'))"', false),
            'ACCESS_USER_VALIDITY_EXPIRATION_MENU'  => $this->getUserValidityMenu($objUser->getValidityTimePeriod(), $objUser->getExpirationDate()),
            'ACCESS_USER_VALIDITY_OPTION_DISPLAY'   => $objUser->getAdminStatus() ? 'none' : '',
            'ACCESS_JAVASCRIPT_FUNCTIONS'           => $this->getJavaScriptCode(),
            'CSS_DISPLAY_STATUS'                    => $cssDisplayStatus
        ));

        $rowNr = 0;
        while (!$objUser->objAttribute->EOF) {
            $objAttribute = $objUser->objAttribute->getById($objUser->objAttribute->getId());

            if (!$objAttribute->isProtected() ||
                (
                    Permission::checkAccess($objAttribute->getAccessId(), 'dynamic', true) ||
                    $objAttribute->checkModifyPermission()
                )
            ) {
                $this->_objTpl->setVariable(array(
                    'ACCESS_ATTRIBUTE_ROW_CLASS'    => $rowNr++ % 2 + 1,
                    'ACCESS_PROFILE_ATTRIBUTE_DESC' => htmlentities($objUser->objAttribute->getName(), ENT_QUOTES, CONTREXX_CHARSET),
                    'ACCESS_PROFILE_ATTRIBUTE'      => $this->parseAttribute($objUser, $objAttribute->getId(), 0, true, true)
                ));
                $this->_objTpl->parse('access_profile_attribute_list');
            }

            $objUser->objAttribute->next();
        }


        $this->parseModuleSpecificExtensions();

        $this->_objTpl->parse('module_access_user_modify');
        return true;
    }

    /**
     * Parse the newsletter lists 
     *
     * @author      Stefan Heinemann <sh@adfinis.com>
     * @param       int $userID
     */
    private function parseNewsletterLists($userID) {
        global $objDatabase;

        $res = $this->getNewsletters($userID);
        if ($res !== false) {
            $row = 0;
            while (!$res->EOF) {
                $selected = $res->fields['selected'] ? 'checked="checked"' : '';
                $this->_objTpl->setVariable(
                    array(
                        'ACCESS_NEWSLETTER_ID'  => $res->fields['id'],
                        'ACCESS_NEWSLETTER_SELECTED' => $selected,
                        'ACCESS_NEWSLETTER_NAME' => $res->fields['name'],
                        'ACCESS_NEWSLETTER_ROW_CLASS' => ($row++ % 2) + 1
                    )
                );

                $this->_objTpl->parse('newsletter_list');
                    
                $res->MoveNext();
            }
        }
    }

    private function parseModuleSpecificExtensions()
    {
        global $_ARRAYLANG;

        $status = false;
        $rowNr = 0;
        $objModuleChecker = new ModuleChecker();

        // add a category in the digital asset management module
        if ($objModuleChecker->getModuleStatusById(53)) {
            $this->parseDigitalAssetManagementExtension($rowNr);
            $status = true;
            $this->_objTpl->parse('access_additional_functions_dma');
        } else {
            $this->_objTpl->hideBlock('access_additional_functions_dma');
        }

        if ($status) {
            $this->_objTpl->setGlobalVariable('TXT_ACCESS_ADDITIONAL_FUNCTIONS', $_ARRAYLANG['TXT_ACCESS_ADDITIONAL_FUNCTIONS']);
            $this->_objTpl->touchBlock('access_additional_functions_tab');
        } else {
            $this->_objTpl->hideBlock('access_additional_functions_tab');
        }
    }

    private function parseDigitalAssetManagementExtension(&$rowNr)
    {
        global $_ARRAYLANG;

        $this->_objTpl->setVariable(array(
            'TXT_ACCESS_DIGITAL_ASSET_MANAGEMENT'   => $_ARRAYLANG['TXT_ACCESS_DIGITAL_ASSET_MANAGEMENT'],
            'TXT_ACCESS_ADD_DAM_CATEGORY'           => $_ARRAYLANG['TXT_ACCESS_ADD_DAM_CATEGORY'],
            'ACCESS_ADDITIONAL_FUNCTION_ROW_CLASS'  => $rowNr++ % 2 ? 0 : 1,
            'ACCESS_USER_ADD_DMA_CATEGORY_CKECKED'  => !empty($_POST['access_user_add_dma_category']) ? 'checked="checked"' : ''
        ));
    }

    private function processModuleSpecificExtensions($objUser)
    {
        // add a category in the digital asset management module
        $objModuleChecker = new ModuleChecker();
        if ($objModuleChecker->getModuleStatusById(53)) {
            $this->processDigitalAssetManagementExtension($objUser);
        }
    }

    private function processDigitalAssetManagementExtension($objUser)
    {
        global $_ARRAYLANG, $_CONFIG;

        if (empty($_POST['access_user_add_dma_category'])) {
            return true;
        }

        include_once ASCMS_MODULE_PATH.'/downloads/lib/downloadsLib.class.php';

// TODO: Never used
//        $objFWUser = FWUser::getFWUserObject();
        $objDownloadLib = new DownloadsLibrary();
        $arrDownloadSettings = $objDownloadLib->getSettings();
        $objUser->setGroups(array_merge($objUser->getAssociatedGroupIds(), array_map('trim', explode(',', $arrDownloadSettings['associate_user_to_groups']))));
        $objUser->store();

        $firstname = $objUser->getProfileAttribute('firstname');
        $lastname = $objUser->getProfileAttribute('lastname');
        $userName = !empty($firstname) || !empty($lastname) ? trim($firstname.' '.$lastname) : $objUser->getUsername();

        $objGroup = new UserGroup();
        $objGroup->setName(sprintf($_ARRAYLANG['TXT_ACCESS_CUSTOMER_TITLE'], $userName));
        $objGroup->setDescription(sprintf($_ARRAYLANG['TXT_ACCESS_USER_ACCOUNT_GROUP_DESC'], $userName));
        $objGroup->setActiveStatus(true);
        $objGroup->setType('frontend');
        $objGroup->setUsers(array($objUser->getId()));
        $objGroup->setDynamicPermissionIds(array());
        $objGroup->setStaticPermissionIds(array());

        if (!$objGroup->store()) {
            $this->arrStatusMsg['error'] = array_merge($this->arrStatusMsg['error'], $objGroup->getErrorMsg());
            return false;
        }

        $arrLanguageIds = array_keys(FWLanguage::getLanguageArray());
        $arrNames = array();
        $arrDescription = array();
        foreach ($arrLanguageIds as $langId) {
            $arrNames[$langId] = sprintf($_ARRAYLANG['TXT_ACCESS_CUSTOMER_TITLE'], $userName);
            $arrDescription[$langId] = '';
        }

        $objCategory = new Category();
        $objCategory->setActiveStatus(true);
        $objCategory->setVisibility(false);
        $objCategory->setNames($arrNames);
        $objCategory->setDescriptions($arrDescription);
        $objCategory->setOwner($objUser->getId());
        $objCategory->setDeletableByOwner(false);
        $objCategory->setModifyAccessByOwner(false);
        $objCategory->setPermissions(array(
            'read'  => array(
                'protected' => true,
                'groups'    => array($objGroup->getId())
            ),
            'add_subcategories' => array(
                'protected' => true,
                'groups'    => array($objGroup->getId())
            ),
            'manage_subcategories' => array(
                'protected' => true,
                'groups'    => array($objGroup->getId())
            ),
            'add_files' => array(
                'protected' => true,
                'groups'    => array($objGroup->getId())
            ),
            'manage_files' => array(
                'protected' => true,
                'groups'    => array($objGroup->getId())
            )
        ));

        if (!$objCategory->store()) {
            $this->arrStatusMsg['error'] = array_merge($this->arrStatusMsg['error'], $objCategory->getErrorMsg());
            return false;
        }

        $damCategoryUri = '?cmd=downloads&amp;act=categories&amp;parent_id='.$objCategory->getId();
        $damCategoryAnchor = '<a href="'.$damCategoryUri.'">'.htmlentities($objCategory->getName(LANG_ID), ENT_QUOTES, CONTREXX_CHARSET).'</a>';
        $this->arrStatusMsg['ok'][] = sprintf($_ARRAYLANG['TXT_ACCESS_NEW_DAM_CATEGORY_CREATED_TXT'], htmlentities($objUser->getUsername(), ENT_QUOTES, CONTREXX_CHARSET), $damCategoryAnchor);

        return true;
    }

    private function notifyUserAboutAccountStatusChange($objUser)
    {
        global $_ARRAYLANG, $_CORELANG, $_CONFIG, $_LANGID;

        $objFWUser = FWUser::getFWUserObject();
        $objUserMail = $objFWUser->getMail();
        $mail2load = $objUser->getActiveStatus() ? 'user_activated' : 'user_deactivated';

        if (
            (
                $objUserMail->load($mail2load, $_LANGID) ||
                $objUserMail->load($mail2load)
            ) &&
            (include_once ASCMS_LIBRARY_PATH.'/phpmailer/class.phpmailer.php') &&
            ($objMail = new PHPMailer()) !== false
        ) {
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
            $objMail->From = $objUserMail->getSenderMail();
            $objMail->FromName = $objUserMail->getSenderName();
            $objMail->AddReplyTo($objUserMail->getSenderMail());
            $objMail->Subject = $objUserMail->getSubject();

            if (in_array($objUserMail->getFormat(), array('multipart', 'text'))) {
                $objUserMail->getFormat() == 'text' ? $objMail->IsHTML(false) : false;
                $objMail->{($objUserMail->getFormat() == 'text' ? '' : 'Alt').'Body'} = str_replace(
                    array(
                        '[[HOST]]',
                        '[[USERNAME]]',
                        '[[SENDER]]'
                    ),
                    array(
                        $_CONFIG['domainUrl'],
                        $objUser->getUsername(),
                        $objUserMail->getSenderName()
                    ),
                    $objUserMail->getBodyText()
                );
            }
            if (in_array($objUserMail->getFormat(), array('multipart', 'html'))) {
                $objUserMail->getFormat() == 'html' ? $objMail->IsHTML(true) : false;
                $objMail->Body = str_replace(
                    array(
                        '[[HOST]]',
                        '[[USERNAME]]',
                        '[[SENDER]]'
                    ),
                    array(
                        $_CONFIG['domainUrl'],
                        htmlentities($objUser->getUsername(), ENT_QUOTES, CONTREXX_CHARSET),
                        htmlentities($objUserMail->getSenderName(), ENT_QUOTES, CONTREXX_CHARSET)
                    ),
                    $objUserMail->getBodyHtml()
                );
            }
            $objMail->AddAddress($objUser->getEmail());
            if ($objMail->Send()) {
                return true;
            }
        }
        $userEmail = '<a href="mailto:'.$objUser->getEmail().'?subject='.($objUser->getActiveStatus() ? $_CORELANG['TXT_ACCESS_USER_ACCOUNT_ACTIVATED'] : $_CORELANG['TXT_ACCESS_USER_ACCOUNT_DEACTIVATED']).'" title="'.$objUser->getEmail().'">'.$objUser->getEmail().'</a>';
        $this->arrStatusMsg['error'][] = str_replace(array('%USER%', '%EMAIL%'), array(htmlentities($objUser->getUsername(), ENT_QUOTES, CONTREXX_CHARSET), $userEmail), $_ARRAYLANG['TXT_ACCESS_COULD_NOT_NOTIFY_USER_ABOUT_STATUS_CHANGE']);
        return false;
    }


    private function getGroupMenu($selectedGroupId, $attrs, $showAllOption = true)
    {
        global $_ARRAYLANG;

        $objFWUser = FWUser::getFWUserObject();
        $objGroup = $objFWUser->objGroup->getGroups(null, array('group_name', 'asc'), array('group_name', 'type'));

        $menu = "<select".(!empty($attrs) ? " ".$attrs : "").">\n";
        $menu .= "<option value=\"".($showAllOption ? '_' : '0')."\" style=\"border-bottom:1px solid #000000;\">".$_ARRAYLANG['TXT_ACCESS_SELECT_GROUP']."</option>\n";
        $showAllOption ? $menu .= "<option value=\"0\" style=\"text-indent:5px;\">".$_ARRAYLANG['TXT_ACCESS_ALL']."</option>\n" : false;
        while (!$objGroup->EOF) {
            $menu .= "<option value=\"".$objGroup->getId()."\" style=\"text-indent:5px;\"".($selectedGroupId == $objGroup->getId() ? " selected=\"selected\"" : "").">".htmlentities($objGroup->getName(), ENT_QUOTES, CONTREXX_CHARSET)." [".$objGroup->getType()."]</option>\n";
            $objGroup->next();
        }
        $menu .= "</select>\n";
        return $menu;
    }


    private function getGroupTypeMenu($selectedType, $attrs)
    {
        global $_ARRAYLANG;

        $menu = "<select".(!empty($attrs) ? " ".$attrs : null).">\n";
        $menu .= "<option value=\"\" style=\"border-bottom:1px solid #000000;\">".$_ARRAYLANG['TXT_ACCESS_GROUP_TYP']."</option>\n";
        $menu .= "<option value=\"frontend\" style=\"text-indent:5px;\"".($selectedType == 'frontend' ? " selected=\"selected\"" : "").">".$_ARRAYLANG['TXT_ACCESS_FRONTEND_DESC']."</option>\n";
        $menu .= "<option value=\"backend\" style=\"text-indent:5px;\"".($selectedType == 'backend' ? " selected=\"selected\"" : "").">".$_ARRAYLANG['TXT_ACCESS_BACKEND_DESC']."</option>\n";
        $menu .= "</select>\n";
        return $menu;
    }


    private function getUserStatusMenu($selectedStatus, $attrs)
    {
        global $_ARRAYLANG;

        $menu = "<select".(!empty($attrs) ? " ".$attrs : null).">\n";
        $menu .= "<option value=\"\" style=\"border-bottom:1px solid #000000;\">".$_ARRAYLANG['TXT_ACCESS_USER_STATUS']."</option>\n";
        $menu .= "<option value=\"1\" style=\"text-indent:5px;\"".($selectedStatus == '1' ? " selected=\"selected\"" : "").">".$_ARRAYLANG['TXT_ACCESS_ACTIVE']."</option>\n";
        $menu .= "<option value=\"0\" style=\"text-indent:5px;\"".($selectedStatus == '0' ? " selected=\"selected\"" : "").">".$_ARRAYLANG['TXT_ACCESS_INACTIVE']."</option>\n";
        $menu .= "</select>\n";
        return $menu;
    }


    private function getUserRoleMenu($selectedRole, $attrs)
    {
        global $_ARRAYLANG;

        $menu = "<select".(!empty($attrs) ? " ".$attrs : null).">\n";
        $menu .= "<option value=\"\" style=\"border-bottom:1px solid #000000;\">".$_ARRAYLANG['TXT_ACCESS_USER_ROLE']."</option>\n";
        $menu .= "<option value=\"1\" style=\"text-indent:5px;\"".($selectedRole == '1' ? " selected=\"selected\"" : "").">".$_ARRAYLANG['TXT_ACCESS_ADMINISTRATORS']."</option>\n";
        $menu .= "<option value=\"0\" style=\"text-indent:5px;\"".($selectedRole == '0' ? " selected=\"selected\"" : "").">".$_ARRAYLANG['TXT_ACCESS_USERS']."</option>\n";
        $menu .= "</select>\n";

        return $menu;
    }


    private function getUserValidityMenu($validity, $expirationDate)
    {
        $menu = '<select name="access_user_validity" '.($validity && $expirationDate < time() ? 'onchange="this.style.color = this.value == \'current\' ? \'#f00\' : \'#000\'"' : null).' style="width:300px;'.($validity && $expirationDate < time() ? 'color:#f00;font-weight:normal;' : 'color:#000;').'">';
        if ($validity) {
            $menu .= '<option value="current" selected="selected" style="border-bottom:1px solid #000;'.($expirationDate < time() ? 'color:#f00;font-weight:normal;' : null).'">'.FWUser::getValidityString($validity).' ('.date(ASCMS_DATE_SHORT_FORMAT, $expirationDate).')</option>';
        }
        $menu .= FWUser::getValidityMenuOptions(null, 'style="color:#000; font-weight:normal;"');
        $menu .= '</select>';
        return $menu;
    }


    private function getMailLanguageMenu($type, $lang, $attrs)
    {
        global $objDatabase;

        $arrUsedLangIds = array();
        $objResultSet = $objDatabase->Execute("SELECT `lang_id` FROM `".DBPREFIX."access_user_mail` WHERE `type` = '".$type."' AND `lang_id` != 0");
        if ($objResultSet !== false) {
            while (!$objResultSet->EOF) {
                array_push($arrUsedLangIds, $objResultSet->fields['lang_id']);
                $objResultSet->MoveNext();
            }
            $menu = '<select'.(!empty($attrs) ? ' '.$attrs : '').">\n";
            $arrLanguages = FWLanguage::getLanguageArray();
            foreach ($arrLanguages as $langId => $arrLanguage) {
                if (!in_array($langId, $arrUsedLangIds)) {
                    $menu .=
                        '<option value="'.$langId.'"'.
                        ($langId == $lang ? ' selected="selected"' : '').
                        '>'.
                        htmlentities(
                            $arrLanguage['name'], ENT_QUOTES, CONTREXX_CHARSET
                        )."</option>\n";
                }
            }
            $menu .= "</select>\n";
            return $menu;
        }
        return false;
    }


    private function getMailFormatMenu($selectedFormat, $attrs)
    {
        $objFWUser = FWUser::getFWUserObject();
        $objUserMail = $objFWUser->getMail();

        $menu = '<select'.($attrs ? ' '.$attrs : '').">\n";
        foreach ($objUserMail->getFormats() as $format => $formatTxt) {
            $menu .= '<option value="'.$format.'"'.($format == $selectedFormat ? ' selected="selected"' : '').'>'.$formatTxt."</option>\n";
        }
        $menu .= "</select>\n";
        return $menu;
    }


    function _config()
    {
        global $_ARRAYLANG;

        $this->_objTpl->loadTemplatefile('module_access_config.html');
        switch ($_REQUEST['tpl']) {
            case 'general':
                $this->_configGeneral();
                break;

            case 'community':
                $this->_configCommunity();
                break;

            case 'attributes':
                $this->_configAttributes();
                break;

            case 'modifyAttribute':
                $this->_configModifyAttribute();
                break;

            case 'deleteAttribute':
                $this->_configDeleteAttribute();
                break;

            case 'attributeCode':
                $this->_configAttributeCode();
                break;

            case 'mails':
                $this->_configMails();
                break;

            case 'modifyMail':
                $this->_configModifyMails();
                break;

            case 'copyMail':
                $this->_configModifyMails(true);
                break;

            case 'deleteMail':
                $this->_configDeleteMail();
                $this->_configMails();
                break;

            default:
                $this->_configGeneral();
                break;
        }

        $this->_pageTitle = $_ARRAYLANG['TXT_ACCESS_SETTINGS'];
        $this->_objTpl->setVariable(array(
            'TXT_ACCESS_GENERAL'            => $_ARRAYLANG['TXT_ACCESS_GENERAL'],
            'TXT_ACCESS_COMMUNITY'          => $_ARRAYLANG['TXT_ACCESS_COMMUNITY'],
            'TXT_ACCESS_PROFILE_ATTRIBUTES' => $_ARRAYLANG['TXT_ACCESS_PROFILE_ATTRIBUTES'],
            'TXT_ACCESS_EMAILS'             => $_ARRAYLANG['TXT_ACCESS_EMAILS']
        ));
    }


    function _configCommunity()
    {
        global $_ARRAYLANG, $_CORELANG, $_CONFIG;

        $assignedGroups = '';
        $notAssignedGroups = '';
        $status = true;

        $arrSettings = User_Setting::getSettings();
        $uriTos = ASCMS_PATH_OFFSET
            .($_CONFIG['useVirtualLanguagePath'] == 'on' ? '/'.FWLanguage::getLanguageParameter(FRONTEND_LANG_ID, 'lang') : NULL)
            .'/'.CONTREXX_DIRECTORY_INDEX
            .'?section=agb';
        $this->_objTpl->addBlockfile('ACCESS_CONFIG_TEMPLATE', 'module_access_config_community', 'module_access_config_community.html');
        $this->_objTpl->setVariable(array(
            'TXT_ACCESS_COMMUNITY'                              => $_ARRAYLANG['TXT_ACCESS_COMMUNITY'],
            'TXT_ACCESS_SAVE'                                   => $_ARRAYLANG['TXT_ACCESS_SAVE'],
            'TXT_ACCESS_GROUP_ASSOCIATION_TEXT'                 => $_ARRAYLANG['TXT_ACCESS_GROUP_ASSOCIATION_TEXT'],
            'TXT_ACCESS_AVAILABLE_GROUPS'                       => $_ARRAYLANG['TXT_ACCESS_AVAILABLE_GROUPS'],
            'TXT_ACCESS_CHECK_ALL'                              => $_ARRAYLANG['TXT_ACCESS_CHECK_ALL'],
            'TXT_ACCESS_UNCHECK_ALL'                            => $_ARRAYLANG['TXT_ACCESS_UNCHECK_ALL'],
            'TXT_ACCESS_ASSOCIATED_GROUPS'                      => $_ARRAYLANG['TXT_ACCESS_ASSOCIATED_GROUPS'],
            'TXT_ACCESS_TOS'                                    => $_ARRAYLANG['TXT_ACCESS_TOS'],
            'TXT_ACCESS_TOS_SINGUP_DESC'                        => sprintf($_ARRAYLANG['TXT_ACCESS_TOS_SINGUP_DESC'], $uriTos),
            'TXT_ACCESS_CAPTCHA'                                => $_ARRAYLANG['TXT_ACCESS_CAPTCHA'],
            'TXT_ACCESS_CAPTCHA_SIGNUP_DESC'                    => $_ARRAYLANG['TXT_ACCESS_CAPTCHA_SIGNUP_DESC'],
            'TXT_ACCESS_USER_ACCOUNT_ACTIVATION_METHOD_TEXT'    => $_ARRAYLANG['TXT_ACCESS_USER_ACCOUNT_ACTIVATION_METHOD_TEXT'],
            'TXT_ACCESS_ACTIVATION_BY_USER'                     => $_ARRAYLANG['TXT_ACCESS_ACTIVATION_BY_USER'],
            'TXT_ACCESS_ACTIVATION_BY_AUTHORIZED_PERSON'        => $_ARRAYLANG['TXT_ACCESS_ACTIVATION_BY_AUTHORIZED_PERSON'],
            'TXT_ACCESS_TIME_PERIOD_ACTIVATION_TIME'            => $_ARRAYLANG['TXT_ACCESS_TIME_PERIOD_ACTIVATION_TIME'],
            'TXT_ACCESS_ADDRESS_OF_USER_TO_NOTIFY'              => $_ARRAYLANG['TXT_ACCESS_ADDRESS_OF_USER_TO_NOTIFY']
        ));

        if (isset($_POST['access_save_settings'])) {
            // only administrators are allowed to modify the config
            if (!Permission::hasAllAccess()) {
                Permission::noAccess();
            }

            if (isset($_POST['access_user_associated_groups']) && is_array($_POST['access_user_associated_groups'])) {
                $arrSettings['assigne_to_groups']['value'] = implode(',', array_map('intval', $_POST['access_user_associated_groups']));
            } else {
                $arrSettings['assigne_to_groups']['value'] = '';
            }

            $arrSettings['user_accept_tos_on_signup']['status'] = !empty($_POST['accessUserTos']);
            $arrSettings['user_captcha']['status'] = !empty($_POST['accessUserCaptcha']);

            if (!empty($_POST['accessUserActivation']) && intval($_POST['accessUserActivation']) > 0) {
                $arrSettings['user_activation']['status'] = 1;

                $arrSettings['user_activation_timeout']['value'] = intval($_POST['accessUserActivationTimeout']);
                if ($arrSettings['user_activation_timeout']['value'] < 0) {
                    $arrSettings['user_activation_timeout']['value'] = 0;
                } elseif ($arrSettings['user_activation_timeout']['value'] > 24) {
                    $arrSettings['user_activation_timeout']['value'] = 24;
                }
                $arrSettings['user_activation_timeout']['status'] = (bool)$arrSettings['user_activation_timeout']['value'];
            } else {
                $arrSettings['user_activation']['status'] = 0;
                $arrSettings['user_activation_timeout']['status'] = 0;

                if (!empty($_POST['accessUserNotificationAddress'])) {
                    $arrSettings['notification_address']['value'] = trim($_POST['accessUserNotificationAddress']);
                    $objValidator = new FWValidator();
                    if (!$objValidator->isEmail($arrSettings['notification_address']['value'])) {
                        $status = false;
                        $this->arrStatusMsg['error'][] = $_ARRAYLANG['TXT_ACCESS_INVALID_ENTERED_EMAIL_ADDRESS'];
                    }
                }
            }

            if ($status) {
                if (User_Setting::setSettings($arrSettings)) {
                    array_push($this->arrStatusMsg['ok'], $_ARRAYLANG['TXT_ACCESS_CONFIG_SUCCESSFULLY_SAVED']);
                } else {
                    $this->arrStatusMsg['error'][] = $_ARRAYLANG['TXT_ACCESS_CONFIG_FAILED_SAVED'];
                    $this->arrStatusMsg['error'][] = $_ARRAYLANG['TXT_ACCESS_TRY_TO_REPEAT_OPERATION'];
                }
            }
        }

        $arrAssignedGroups = explode(',', $arrSettings['assigne_to_groups']['value']);

        $objFWUser = FWUser::getFWUserObject();
        $objGroup = $objFWUser->objGroup->getGroups();
        while (!$objGroup->EOF) {
            $groupVar = in_array($objGroup->getId(), $arrAssignedGroups) ? 'assignedGroups' : 'notAssignedGroups';
            $$groupVar .= '<option value="'.$objGroup->getId().'">'.htmlentities($objGroup->getName(), ENT_QUOTES, CONTREXX_CHARSET).' ['.$objGroup->getType().']</option>';
            $objGroup->next();
        }

        $this->_objTpl->setVariable(array(
            'ACCESS_USER_NOT_ASSOCIATED_GROUPS'     => $notAssignedGroups,
            'ACCESS_USER_ASSOCIATED_GROUPS'         => $assignedGroups,
            'ACCESS_USER_TOS'                       => $arrSettings['user_accept_tos_on_signup']['status'] ? 'checked="checked"' : '',
            'ACCESS_USER_CAPTCHA'                   => $arrSettings['user_captcha']['status'] ? 'checked="checked"' : '',
            'ACCESS_USER_ACTIVATION_1'              => $arrSettings['user_activation']['status'] ? 'checked="checked"' : '',
            'ACCESS_USER_ACTIVATION_0'              => $arrSettings['user_activation']['status'] ? '': 'checked="checked"',
            'ACCESS_USER_ACTIVATION_BOX_1'          => $arrSettings['user_activation']['status'] ? 'block' : 'none',
            'ACCESS_USER_ACTIVATION_BOX_0'          => $arrSettings['user_activation']['status'] ? 'none': 'block',
            'ACCESS_USER_ACTIVATION_TIMEOUT'        => $arrSettings['user_activation_timeout']['value'],
            'ACCESS_USER_NOTIFICATION_ADDRESS'      => $arrSettings['notification_address']['value']
        ));
        $this->_objTpl->parse('module_access_config_community');
    }


    function _configGeneral()
    {
        global $_ARRAYLANG, $_CORELANG;

        $status = true;
        $arrSettings = User_Setting::getSettings();

        $this->_objTpl->addBlockfile('ACCESS_CONFIG_TEMPLATE', 'module_access_config_general', 'module_access_config_general.html');
        $this->_objTpl->setVariable(array(
            'TXT_ACCESS_PROFILE'                                => $_ARRAYLANG['TXT_ACCESS_PROFILE'],
            'TXT_ACCESS_PROFILE_AVATAR_PIC'                     => $_ARRAYLANG['TXT_ACCESS_PROFILE_AVATAR_PIC'],
            'TXT_ACCESS_PERMISSIONS'                            => $_ARRAYLANG['TXT_ACCESS_PERMISSIONS'],
            'TXT_ACCESS_YES'                                    => $_ARRAYLANG['TXT_ACCESS_YES'],
            'TXT_ACCESS_NO'                                     => $_ARRAYLANG['TXT_ACCESS_NO'],
            'TXT_ACCESS_ALLOW_USERS_DELETE_ACCOUNT'             => $_ARRAYLANG['TXT_ACCESS_ALLOW_USERS_DELETE_ACCOUNT'],
            'TXT_ACCESS_ALLOW_USERS_SET_PROFILE_ACCESS'         => $_ARRAYLANG['TXT_ACCESS_ALLOW_USERS_SET_PROFILE_ACCESS'],
            'TXT_ACCESS_ALLOW_USERS_SET_EMAIL_ACCESS'           => $_ARRAYLANG['TXT_ACCESS_ALLOW_USERS_SET_EMAIL_ACCESS'],
            'TXT_ACCESS_FRONTEND_BLOCK_FUNCTIONS'               => $_ARRAYLANG['TXT_ACCESS_FRONTEND_BLOCK_FUNCTIONS'],
            'TXT_ACCESS_CURRENTLY_ONLINE'                       => $_ARRAYLANG['TXT_ACCESS_CURRENTLY_ONLINE'],
            'TXT_ACCESS_LAST_ACTIVE'                            => $_ARRAYLANG['TXT_ACCESS_LAST_ACTIVE'],
            'TXT_ACCESS_LATEST_REGISTERED_USERS'                => $_ARRAYLANG['TXT_ACCESS_LATEST_REGISTERED_USERS'],
            'TXT_ACCESS_BIRTHDAYS'                              => $_ARRAYLANG['TXT_ACCESS_BIRTHDAYS'],
            'TXT_ACCESS_ACTIVATE_BLOCK_FUNCTION'                => $_ARRAYLANG['TXT_ACCESS_ACTIVATE_BLOCK_FUNCTION'],
            'TXT_ACCESS_SHOW_USERS_ONLY_WITH_PHOTO'             => $_ARRAYLANG['TXT_ACCESS_SHOW_USERS_ONLY_WITH_PHOTO'],
            'TXT_ACCESS_MAX_USER_COUNT'                         => $_ARRAYLANG['TXT_ACCESS_MAX_USER_COUNT'],
            'TXT_ACCESS_SAVE'                                   => $_ARRAYLANG['TXT_ACCESS_SAVE'],
            'TXT_ACCESS_PROFILE_PIC'                            => $_CORELANG['TXT_ACCESS_PROFILE_PIC'],
            'TXT_ACCESS_MAX_WIDTH'                              => $_ARRAYLANG['TXT_ACCESS_MAX_WIDTH'],
            'TXT_ACCESS_MAX_HEIGHT'                             => $_ARRAYLANG['TXT_ACCESS_MAX_HEIGHT'],
            'TXT_ACCESS_MAX_FILE_SIZE'                          => $_ARRAYLANG['TXT_ACCESS_MAX_FILE_SIZE'],
            'TXT_ACCESS_THUMBNAIL_WIDTH'                        => $_ARRAYLANG['TXT_ACCESS_THUMBNAIL_WIDTH'],
            'TXT_ACCESS_THUMBNAIL_HEIGHT'                       => $_ARRAYLANG['TXT_ACCESS_THUMBNAIL_HEIGHT'],
            'TXT_ACCESS_MAX_THUMBNAIL_WIDTH'                    => $_ARRAYLANG['TXT_ACCESS_MAX_THUMBNAIL_WIDTH'],
            'TXT_ACCESS_MAX_THUMBNAIL_HEIGHT'                   => $_ARRAYLANG['TXT_ACCESS_MAX_THUMBNAIL_HEIGHT'],
            'TXT_ACCESS_PICTURES'                               => $_ARRAYLANG['TXT_ACCESS_PICTURES'],
            'TXT_ACCESS_OTHER_PICTURES'                         => $_ARRAYLANG['TXT_ACCESS_OTHER_PICTURES'],
            'TXT_ACCESS_MISCELLANEOUS'                          => $_ARRAYLANG['TXT_ACCESS_MISCELLANEOUS'],
            'TXT_ACCESS_STANDARD'                               => $_ARRAYLANG['TXT_ACCESS_STANDARD'],
            'TXT_ACCESS_EMAIL'                                  => $_ARRAYLANG['TXT_ACCESS_EMAIL'],
            'TXT_ACCESS_SESSION_ON_INTERVAL'                    => $_ARRAYLANG['TXT_ACCESS_SESSION_ON_INTERVAL'],
            'TXT_ACCESS_SESSION_DESCRIPTION'                    =>$_ARRAYLANG['TXT_ACCESS_SESSION_DESCRIPTION'],
            'TXT_ACCESS_SESSION_TITLE'                          => $_ARRAYLANG['TXT_ACCESS_SESSION_TITLE'],
            'TXT_ACCESS_USE_SELECTED_ACCESS_FOR_EVERYONE'       => $_ARRAYLANG['TXT_ACCESS_USE_SELECTED_ACCESS_FOR_EVERYONE'],
            'TXT_ACCESS_CROP_THUMBNAIL_TXT'                     => $_ARRAYLANG['TXT_ACCESS_CROP_THUMBNAIL_TXT'],
            'TXT_ACCESS_SCALE_THUMBNAIL_TXT'                    => $_ARRAYLANG['TXT_ACCESS_SCALE_THUMBNAIL_TXT'],
            'TXT_ACCESS_BACKGROUND_COLOR'                       => $_ARRAYLANG['TXT_ACCESS_BACKGROUND_COLOR'],
            'TXT_ACCESS_THUMBNAIL_GENERATION'                   => $_ARRAYLANG['TXT_ACCESS_THUMBNAIL_GENERATION']
        ));

        if (isset($_POST['access_save_settings'])) {
            // only administrators are allowed to modify the config
            if (!Permission::hasAllAccess()) {
                Permission::noAccess();
            }

            $arrSettings['user_delete_account']['status'] = !empty($_POST['access_permissions_delete_account']) ? intval($_POST['access_permissions_delete_account']) : 0;
            $arrSettings['user_config_profile_access']['status'] = !empty($_POST['access_permissions_profile_access']) ? intval($_POST['access_permissions_profile_access']) : 0;
            $arrSettings['user_config_email_access']['status'] = !empty($_POST['access_permissions_email_access']) ? intval($_POST['access_permissions_email_access']) : 0;
            $arrSettings['default_profile_access']['value'] = isset($_POST['access_user_profile_access']) && in_array($_POST['access_user_profile_access'], array('everyone', 'members_only', 'nobody')) ? $_POST['access_user_profile_access'] : 'members_only';
            $arrSettings['default_email_access']['value'] = isset($_POST['access_user_email_access']) && in_array($_POST['access_user_email_access'], array('everyone', 'members_only', 'nobody')) ? $_POST['access_user_email_access'] : 'members_only';

            if (!empty($_POST['access_blocks_currently_online_users'])) {
                $arrSettings['block_currently_online_users']['status'] = 1;
                $arrSettings['block_currently_online_users']['value'] = !empty($_POST['access_blocks_currently_online_users_user_count']) ? intval($_POST['access_blocks_currently_online_users_user_count']) : 0;
                $arrSettings['block_currently_online_users_pic']['status'] = !empty($_POST['access_blocks_currently_online_users_only_with_photo']) && intval($_POST['access_blocks_currently_online_users_only_with_photo']);
            } else {
                $arrSettings['block_currently_online_users']['status'] = 0;
            }
            if (!empty($_POST['access_blocks_last_active_users'])) {
                $arrSettings['block_last_active_users']['status'] = 1;
                $arrSettings['block_last_active_users']['value'] = !empty($_POST['access_blocks_last_active_users_user_count']) ? intval($_POST['access_blocks_last_active_users_user_count']) : 0;
                $arrSettings['block_last_active_users_pic']['status'] = !empty($_POST['access_blocks_last_active_users_only_with_photo']) && intval($_POST['access_blocks_last_active_users_only_with_photo']);
            } else {
                $arrSettings['block_last_active_users']['status'] = 0;
            }
            if (!empty($_POST['access_blocks_latest_registered_users'])) {
                $arrSettings['block_latest_reg_users']['status'] = 1;
                $arrSettings['block_latest_reg_users']['value'] = !empty($_POST['access_blocks_latest_registered_users_user_count']) ? intval($_POST['access_blocks_latest_registered_users_user_count']) : 0;
                $arrSettings['block_latest_reg_users_pic']['status'] = !empty($_POST['access_blocks_latest_registered_users_only_with_photo']) && intval($_POST['access_blocks_latest_registered_users_only_with_photo']);
            } else {
                $arrSettings['block_latest_reg_users']['status'] = 0;
            }
            if (!empty($_POST['access_blocks_birthday_users'])) {
                $arrSettings['block_birthday_users']['status'] = 1;
                $arrSettings['block_birthday_users']['value'] = !empty($_POST['access_blocks_birthday_users_user_count']) ? intval($_POST['access_blocks_birthday_users_user_count']) : 0;
                $arrSettings['block_birthday_users_pic']['status'] = !empty($_POST['access_blocks_birthday_users_only_with_photo']) && intval($_POST['access_blocks_birthday_users_only_with_photo']);
            } else {
                $arrSettings['block_birthday_users']['status'] = 0;
            }

            if (!empty($_POST['accessMaxProfilePicWidth'])) {
                $arrSettings['max_profile_pic_width']['value'] = intval($_POST['accessMaxProfilePicWidth']);
            }
            if (!empty($_POST['accessMaxProfilePicHeight'])) {
                $arrSettings['max_profile_pic_height']['value'] = intval($_POST['accessMaxProfilePicHeight']);
            }

            if (!empty($_POST['accessProfileThumbnailPicWidth'])) {
                $arrSettings['profile_thumbnail_pic_width']['value'] = intval($_POST['accessProfileThumbnailPicWidth']);
            }
            if (!empty($_POST['accessProfileThumbnailPicHeight'])) {
                $arrSettings['profile_thumbnail_pic_height']['value'] = intval($_POST['accessProfileThumbnailPicHeight']);
            }

            if (!empty($_POST['accessMaxProfilePicSize'])) {
// TODO
//                if ($this->getBytesOfLiteralSizeFormat($_POST['accessMaxProfilePicSize']) != $arrSettings['max_profile_pic_size']['value']) {
//                    // resize profile pics
//                }
                $arrSettings['max_profile_pic_size']['value'] = $this->getBytesOfLiteralSizeFormat($_POST['accessMaxProfilePicSize']);
            }

            if (isset($_POST['accessProfileThumbnailMethod']) && $_POST['accessProfileThumbnailMethod'] == 'scale') {
                $arrSettings['profile_thumbnail_method']['value'] = 'scale';
                $color = !empty($_POST['accessProfileThumbnailScaleColor']) ? contrexx_stripslashes($_POST['accessProfileThumbnailScaleColor']) : NULL;
                $arrSettings['profile_thumbnail_scale_color']['value'] = $this->validateHexRGBColor($color);
            } else {
                $arrSettings['profile_thumbnail_method']['value'] = 'crop';
            }

            if (!empty($_POST['accessMaxPicWidth'])) {
                $arrSettings['max_pic_width']['value'] = intval($_POST['accessMaxPicWidth']);
            }
            if (!empty($_POST['accessMaxPicHeight'])) {
                $arrSettings['max_pic_height']['value'] = intval($_POST['accessMaxPicHeight']);
            }

            if (!empty($_POST['accessMaxThumbnailPicWidth'])) {
                $arrSettings['max_thumbnail_pic_width']['value'] = intval($_POST['accessMaxThumbnailPicWidth']);
            }
            if (!empty($_POST['accessMaxThumbnailPicHeight'])) {
                $arrSettings['max_thumbnail_pic_height']['value'] = intval($_POST['accessMaxThumbnailPicHeight']);
            }

            if (!empty($_POST['accessMaxPicSize'])) {
// TODO
//                if ($this->getBytesOfLiteralSizeFormat($_POST['accessMaxPicSize']) != $arrSettings['max_pic_size']['value']) {
//                    // resize pics
//                }
                $arrSettings['max_pic_size']['value'] = $this->getBytesOfLiteralSizeFormat($_POST['accessMaxPicSize']);
            }

            $session_on_interval =  intval($_POST['sessioninterval']);
               if(trim($session_on_interval) != null) {

                if ($session_on_interval >=0 && $session_on_interval <= 300) {

                 $arrSettings['session_user_interval']['value'] = $session_on_interval;
                }
            }



            if ($status) {
                if (User_Setting::setSettings($arrSettings)) {
                    $this->arrStatusMsg['ok'][] = $_ARRAYLANG['TXT_ACCESS_CONFIG_SUCCESSFULLY_SAVED'];

                    if (!empty($_POST['access_force_selected_profile_access'])) {
                        if (!User::forceDefaultProfileAccess()) {
                            $this->arrStatusMsg['error'][] = $_ARRAYLANG['TXT_ACCESS_SET_DEFAULT_PROFILE_ACCESS_FAILED'];
                        }
                    }
                    if (!empty($_POST['access_force_selected_email_access'])) {
                        if (!User::forceDefaultEmailAccess()) {
                            $this->arrStatusMsg['error'][] = $_ARRAYLANG['TXT_ACCESS_SET_DEFAULT_EMAIL_ACCESS_FAILED'];
                        }
                    }

                } else {
                    $this->arrStatusMsg['error'][] = $_ARRAYLANG['TXT_ACCESS_CONFIG_FAILED_SAVED'];
                    $this->arrStatusMsg['error'][] = $_ARRAYLANG['TXT_ACCESS_TRY_TO_REPEAT_OPERATION'];
                }
            }
        }

        $this->parseAccountAttribute(null, 'profile_access', true, $arrSettings['default_profile_access']['value']);
        $this->parseAccountAttribute(null, 'email_access', true, $arrSettings['default_email_access']['value']);

        $this->_objTpl->setVariable(array(
            'ACCESS_ALLOW_USERS_DELETE_ACCOUNT'                     => $arrSettings['user_delete_account']['status'] ? 'checked="checked"' : '',
            'ACCESS_DONT_ALLOW_USERS_DELETE_ACCOUNT'                => $arrSettings['user_delete_account']['status'] ? '' : 'checked="checked"',
            'ACCESS_ALLOW_USERS_SET_PROFILE_ACCESS'                 => $arrSettings['user_config_profile_access']['status'] ? 'checked="checked"' : '',
            'ACCESS_DONT_ALLOW_USERS_SET_PROFILE_ACCESS'            => $arrSettings['user_config_profile_access']['status'] ? '' : 'checked="checked"',
            'ACCESS_ALLOW_USERS_SET_EMAIL_ACCESS'                   => $arrSettings['user_config_email_access']['status'] ? 'checked="checked"' : '',
            'ACCESS_DONT_ALLOW_USERS_SET_EMAIL_ACCESS'              => $arrSettings['user_config_email_access']['status'] ? '' : 'checked="checked"',
            'ACCESS_BLOCKS_CURRENTLY_ONLINE_USERS'                  => $arrSettings['block_currently_online_users']['status'] ? 'checked="checked"' : '',
            'ACCESS_BLOCKS_CURRENTLY_ONLINE_USERS_DISPLAY'          => $arrSettings['block_currently_online_users']['status'] ? '' : 'none',
            'ACCESS_BLOCKS_CURRENTLY_ONLINE_USERS_USER_COUNT'       => $arrSettings['block_currently_online_users']['value'],
            'ACCESS_BLOCKS_CURRENTLY_ONLINE_USERS_ONLY_WITH_PHOTO'  => $arrSettings['block_currently_online_users_pic']['status'] ? 'checked="checked"' : '',
            'ACCESS_BLOCKS_LAST_ACTIVE_USERS'                       => $arrSettings['block_last_active_users']['status'] ? 'checked="checked"' : '',
            'ACCESS_BLOCKS_LAST_ACTIVE_USERS_DISPLAY'               => $arrSettings['block_last_active_users']['status'] ? '' : 'none',
            'ACCESS_BLOCKS_LAST_ACTIVE_USERS_USER_COUNT'            => $arrSettings['block_last_active_users']['value'],
            'ACCESS_BLOCKS_LAST_ACTIVE_USERS_ONLY_WITH_PHOTO'       => $arrSettings['block_last_active_users_pic']['status'] ? 'checked="checked"' : '',
            'ACCESS_BLOCKS_LATEST_REGISTERED_USERS'                 => $arrSettings['block_latest_reg_users']['status'] ? 'checked="checked"' : '',
            'ACCESS_BLOCKS_LATEST_REGISTERED_USERS_DISPLAY'         => $arrSettings['block_latest_reg_users']['status'] ? '' : 'none',
            'ACCESS_BLOCKS_LATEST_REGISTERED_USERS_USER_COUNT'      => $arrSettings['block_latest_reg_users']['value'],
            'ACCESS_BLOCKS_LATEST_REGISTERED_USERS_ONLY_WITH_PHOTO' => $arrSettings['block_latest_reg_users_pic']['status'] ? 'checked="checked"' : '',
            'ACCESS_BLOCKS_BIRTHDAY_USERS'                          => $arrSettings['block_birthday_users']['status'] ? 'checked="checked"' : '',
            'ACCESS_BLOCKS_BIRTHDAY_USERS_DISPLAY'                  => $arrSettings['block_birthday_users']['status'] ? '' : 'none',
            'ACCESS_BLOCKS_BIRTHDAY_USERS_USER_COUNT'               => $arrSettings['block_birthday_users']['value'],
            'ACCESS_BLOCKS_BIRTHDAY_USERS_ONLY_WITH_PHOTO'          => $arrSettings['block_birthday_users_pic']['status'] ? 'checked="checked"' : '',
            'ACCESS_MAX_PROFILE_PIC_WIDTH'                          => $arrSettings['max_profile_pic_width']['value'],
            'ACCESS_MAX_PROFILE_PIC_HEIGHT'                         => $arrSettings['max_profile_pic_height']['value'],
            'ACCESS_PROFILE_THUMBNAIL_PIC_WIDTH'                    => $arrSettings['profile_thumbnail_pic_width']['value'],
            'ACCESS_PROFILE_THUMBNAIL_PIC_HEIGHT'                   => $arrSettings['profile_thumbnail_pic_height']['value'],
            'ACCESS_MAX_PROFILE_PIC_SIZE'                           => $this->getLiteralSizeFormat($arrSettings['max_profile_pic_size']['value']),
            'ACCESS_MAX_PIC_WIDTH'                                  => $arrSettings['max_pic_width']['value'],
            'ACCESS_MAX_PIC_HEIGHT'                                 => $arrSettings['max_pic_height']['value'],
            'ACCESS_MAX_THUMBNAIL_PIC_WIDTH'                        => $arrSettings['max_thumbnail_pic_width']['value'],
            'ACCESS_MAX_THUMBNAIL_PIC_HEIGHT'                       => $arrSettings['max_thumbnail_pic_height']['value'],
            'ACCESS_SESSION_USER_INTERVAL'                => $arrSettings['session_user_interval']['value'],
            'ACCESS_MAX_PIC_SIZE'                                   => $this->getLiteralSizeFormat($arrSettings['max_pic_size']['value']),
            'ACCESS_PROFILE_THUMBNAIL_CROP'                         => $arrSettings['profile_thumbnail_method']['value'] == 'crop' ? 'selected="selected"' : '',
            'ACCESS_PROFILE_THUMBNAIL_SCALE'                        => $arrSettings['profile_thumbnail_method']['value'] == 'scale' ? 'selected="selected"' : '',
            'ACCESS_PROFILE_THUMBNAIL_SCALE_BOX'                    => $arrSettings['profile_thumbnail_method']['value'] == 'scale' ? 'inline' : 'none',
            'ACCESS_PROFILE_THUMBNAIL_SCALE_COLOR'                  => $arrSettings['profile_thumbnail_scale_color']['value']
        ));
        $this->_objTpl->parse('module_access_config_general');
    }

    private function validateHexRGBColor($color)
    {
        if (preg_match('/^#(?:[a-z0-9]{3}|[a-z0-9]{6})$/i', $color, $match)) {
            if (strlen($match[0]) == 4) {
                $color = $match[0];
                $color[6] = $color[3];
                $color[5] = $color[3];
                $color[4] = $color[2];
                $color[3] = $color[2];
                $color[2] = $color[1];
            }

            return strtoupper($color);
        }

        return $this->defaultProfileThumbnailScaleColor;
    }


    function _configAttributes()
    {
        global $_ARRAYLANG;

        $this->_objTpl->addBlockfile('ACCESS_CONFIG_TEMPLATE', 'module_access_config_attribute_list', 'module_access_config_attribute_list.html');

        $this->attachJavaScriptFunction('accessSetWebsite');
        $this->attachJavaScriptFunction('jscalendarIncludes');

        $this->_objTpl->setVariable(array(
            'TXT_ACCESS_NAME'                       => $_ARRAYLANG['TXT_ACCESS_NAME'],
            'TXT_ACCESS_ATTRIBUTE'                  => $_ARRAYLANG['TXT_ACCESS_ATTRIBUTE'],
            'TXT_ACCESS_FUNCTIONS'                  => $_ARRAYLANG['TXT_ACCESS_FUNCTIONS'],
            'TXT_ACCESS_PREVIEW'                    => $_ARRAYLANG['TXT_ACCESS_PREVIEW'],
            'TXT_ACCESS_ADD_NEW_PROFILE_ATTRIBUTE'  => $_ARRAYLANG['TXT_ACCESS_ADD_NEW_PROFILE_ATTRIBUTE'],
            'TXT_ACCESS_ID'                         => $_ARRAYLANG['TXT_ACCESS_ID'],
            'ACCESS_JAVASCRIPT_FUNCTIONS'           => $this->getJavaScriptCode()
        ));

        $this->_objTpl->setGlobalVariable(array(
            'TXT_ACCESS_PROFILE_ATTRIBUTES'             => $_ARRAYLANG['TXT_ACCESS_PROFILE_ATTRIBUTES'],
            'TXT_ACCESS_MODIFY_ATTRIBUTE'               => $_ARRAYLANG['TXT_ACCESS_MODIFY_ATTRIBUTE'],
            'TXT_ACCESS_DELETE_ATTRIBUTE'               => $_ARRAYLANG['TXT_ACCESS_DELETE_ATTRIBUTE'],
            'TXT_ACCESS_CONFIRM_DELETE_ATTRIBUTE_MSG'   => str_replace("\n",'\n', $_ARRAYLANG['TXT_ACCESS_CONFIRM_DELETE_ATTRIBUTE_MSG']),
            'TXT_ACCESS_OPERATION_IRREVERSIBLE'         => $_ARRAYLANG['TXT_ACCESS_OPERATION_IRREVERSIBLE']
        ));

        $row = 0;
        $objAttribute = new User_Profile_Attribute();
        while (!$objAttribute->EOF) {
            $this->_objTpl->setVariable(array(
                'ACCESS_ATTRIBUTE_ROW_CLASS' => $row++ % 2,
                'ACCESS_ATTRIBUTE_NAME'      => htmlentities($objAttribute->getName(), ENT_QUOTES, CONTREXX_CHARSET),
                'ACCESS_ATTRIBUTE_NAME_JS'   => urlencode(htmlentities($objAttribute->getName(), ENT_QUOTES, CONTREXX_CHARSET)),
                'ACCESS_ATTRIBUTE'           => $this->parseAttribute(new User(), $objAttribute->getId(), 0, true, true)
            ));
            $this->_objTpl->setGlobalVariable('ACCESS_ATTRIBUTE_ID', $objAttribute->getId());

            if ($objAttribute->isModifiable()) {
                $this->_objTpl->touchBlock('access_attribute_modify_function');
            } else {
                $this->_objTpl->hideBlock('access_attribute_modify_function');
            }
            if ($objAttribute->isRemovable()) {
                $this->_objTpl->hideBlock('access_attribute_no_delete_function');
                $this->_objTpl->touchBlock('access_attribute_delete_function');
            } else {
                $this->_objTpl->touchBlock('access_attribute_no_delete_function');
                $this->_objTpl->hideBlock('access_attribute_delete_function');
            }
            $this->_objTpl->parse('access_attribute_list');
            $objAttribute->next();
        }
        $this->_objTpl->parse('module_access_config_attribute_list');
    }


    function _configModifyAttribute()
    {
        global $_ARRAYLANG;

        $setStatus = true;
        $associatedGroups = '';
        $notAssociatedGroups = '';

        $objFWUser = FWUser::getFWUserObject();
        $objAttribute = $objFWUser->objUser->objAttribute->getById(isset($_REQUEST['id']) ? $_REQUEST['id'] : 0);
        if (isset($_POST['access_store_attribute']) || isset($_POST['access_add_child'])) {
            // only administrators are allowed to modify the config
            if (!Permission::hasAllAccess()) {
                Permission::noAccess();
            }

            if (isset($_POST['access_attribute_name']) && is_array($_POST['access_attribute_name'])) {
                $objAttribute->setNames(array_map('contrexx_stripslashes', $_POST['access_attribute_name']));
            }

            if (isset($_POST['access_attribute_type'])) {
                $objAttribute->setType($_POST['access_attribute_type']);
            }

            if (isset($_POST['access_attribute_sort_type'])) {
                $objAttribute->setSortType($_POST['access_attribute_sort_type']);
            }

            if ($objAttribute->getSortType() == 'custom' && isset($_POST['access_attribute_sort_order']) && is_array($_POST['access_attribute_sort_order'])) {
                $objAttribute->setChildOrder($_POST['access_attribute_sort_order']);
            }

            if (isset($_POST['access_attribute_parent_id'])) {
                $setStatus = $objAttribute->setParent($_POST['access_attribute_parent_id']);
            }

            if (isset($_POST['access_attribute_all_access']) && $_POST['access_attribute_all_access']) {
                $objAttribute->removeProtection();
            } else {
                $objAttribute->setProtection(isset($_POST['access_attribute_associated_groups']) && is_array($_POST['access_attribute_associated_groups']) ? $_POST['access_attribute_associated_groups'] : array());
                $objAttribute->setSpecialProtection(isset($_POST['access_attribute_special_menu_access']) ? $_POST['access_attribute_special_menu_access'] : '');
            }

            $objAttribute->setMultiline(isset($_POST['access_text_multiline_option']) && intval($_POST['access_text_multiline_option']));
            $objAttribute->setMandatory((isset($_POST['access_attribute_mandatory']) ? intval($_POST['access_attribute_mandatory']) : 0));

            if ($setStatus && $objAttribute->store()) {
                if (isset($_POST['access_add_child'])) {
                    $objAttribute->createChild($objAttribute->getId());
                } elseif (isset($_POST['access_add_other_after_store'])) {
                    $objAttribute->createChild($objAttribute->getParent());
                } else {
                    $this->arrStatusMsg['ok'][] = $this->errorMsg = $objAttribute->getType() == 'menu_option' ? $_ARRAYLANG['TXT_ACCESS_SUCCESS_STORE_MENU_OPTION'] : ($objAttribute->getType() == 'frame' ? $_ARRAYLANG['TXT_ACCESS_SUCCESS_STORE_FRAME'] : $_ARRAYLANG['TXT_ACCESS_SUCCESS_STORE_ATTRIBUTE']);
                    if ($objAttribute->getParent()) {
                        $objAttribute->load($objAttribute->getParent());
                    } else {
                        $this->_configAttributes();
                        return;
                    }
                }
            } else {
                $this->arrStatusMsg['error'][] = $objAttribute->getErrorMsg();
            }
        }

        $this->_objTpl->addBlockfile('ACCESS_CONFIG_TEMPLATE', 'module_access_config_attribute_modify', 'module_access_config_attribute_modify.html');

        $this->attachJavaScriptFunction('accessSetWebsite');
        $this->attachJavaScriptFunction('jscalendarIncludes');
        $this->attachJavaScriptFunction('accessSelectAllGroups');
        $this->attachJavaScriptFunction('accessDeselectAllGroups');
        $this->attachJavaScriptFunction('accessAddGroupToList');
        $this->attachJavaScriptFunction('accessRemoveGroupFromList');

        $this->_objTpl->setVariable(array(
            'ACCESS_PROFILE_OPERATION_TITLE'            => $objAttribute->getId() ? $_ARRAYLANG['TXT_ACCESS_PROFILE_ATTRIBUTE_MODIFY'] : $_ARRAYLANG['TXT_ACCESS_ADD_NEW_PROFILE_ATTRIBUTE'],
            'ACCESS_ADD_CHILD_TXT'                      => $objAttribute->getType() == 'menu' ? $_ARRAYLANG['TXT_ACCESS_ADD_NEW_MENU_OPTION'] : ($objAttribute->getType() == 'group' ? $_ARRAYLANG['TXT_ACCESS_ADD_NEW_GROUP_FRAME'] : $_ARRAYLANG['TXT_ACCESS_ADD_NEW_PROFILE_ATTRIBUTE']),
            'ACCESS_PARENT_TYPE_TITLE'                  => $objAttribute->getParentTypeDescription(),
            'ACCESS_PARENT_TYPE'                        => $objAttribute->isCoreAttribute($objAttribute->getId()) ? $_ARRAYLANG['TXT_ACCESS_NEW_ATTRIBUTE'] : ($objAttribute->hasMovableOption() ? $objAttribute->getParentMenu('name="access_attribute_parent_id" style="width:300px;" onchange="accessCheckParentAttribute()"') : '<input type="hidden" name="access_attribute_parent_id" value="'.$objAttribute->getParent().'" />'.$objAttribute->getParentType()),
            'ACCESS_PARENT_ID'                          => $objAttribute->getParent(),
            'ACCESS_CANCEL_RETURN_SECTION'              => $objAttribute->getParent() ? 'modifyAttribute' : 'attributes',
            'ACCESS_MUST_STORE_BEFORE_CONTINUE_MSG'     => $objAttribute->getId() ? $_ARRAYLANG['TXT_ACCESS_STORE_CHANGED_ATTRIBUTE_MSG'] : $_ARRAYLANG['TXT_ACCESS_MUST_STORE_NEW_ATTRIBUTE_MSG'],
            'ACCESS_IS_NEW_ATTRIBUTE'                   => $objAttribute->getId() ? 'false' : 'true',
            'ACCESS_JAVASCRIPT_FUNCTIONS'               => $this->getJavaScriptCode(),
            'ACCESS_SORTABLE_TYPE_LIST'                 => implode("', '", $objAttribute->getSortableTypes()),
            'ACCESS_MANDATORY_TYPE_LIST'                => implode("', '", $objAttribute->getMandatoryTypes()),
            'TXT_ACCESS_INVALID_PARENT_ATTRIBUTE'       => $_ARRAYLANG['TXT_ACCESS_INVALID_PARENT_ATTRIBUTE'],
            'TXT_ACCESS_CHANGES_WILL_BE_LOST'           => $_ARRAYLANG['TXT_ACCESS_CHANGES_WILL_BE_LOST'],
            'TXT_ACCESS_EXTENDED'                       => $_ARRAYLANG['TXT_ACCESS_EXTENDED'],
            'TXT_ACCESS_TYPE'                           => $_ARRAYLANG['TXT_ACCESS_TYPE'],
            'TXT_ACCESS_MANDATORY_FIELD'                => $_ARRAYLANG['TXT_ACCESS_MANDATORY_FIELD'],
            'TXT_ACCESS_NO'                             => $_ARRAYLANG['TXT_ACCESS_NO'],
            'TXT_ACCESS_YES'                            => $_ARRAYLANG['TXT_ACCESS_YES'],
            'TXT_ACCESS_ATTRIBUTE'                      => $_ARRAYLANG['TXT_ACCESS_ATTRIBUTE'],
            'TXT_ACCESS_PREVIEW'                        => $_ARRAYLANG['TXT_ACCESS_PREVIEW'],
            'TXT_ACCESS_BACK'                           => $_ARRAYLANG['TXT_ACCESS_BACK'],
            'TXT_ACCESS_SAVE'                           => $_ARRAYLANG['TXT_ACCESS_SAVE'],
            'TXT_ACCESS_FUNCTIONS'                      => $_ARRAYLANG['TXT_ACCESS_FUNCTIONS'],
            'TXT_ACCESS_MULTILINE_TEXT'                 => $_ARRAYLANG['TXT_ACCESS_MULTILINE_TEXT'],
            'TXT_ACCESS_SORT'                           => $_ARRAYLANG['TXT_ACCESS_SORT'],
            'TXT_ACCESS_MODIFICATION_ACCESS'            => $_ARRAYLANG['TXT_ACCESS_MODIFICATION_ACCESS'],
            'TXT_ACCESS_EVERYONE_MOD_PERM'              => $_ARRAYLANG['TXT_ACCESS_EVERYONE_MOD_PERM'],
            'TXT_ACCESS_AVAILABLE_GROUPS'               => $_ARRAYLANG['TXT_ACCESS_AVAILABLE_GROUPS'],
            'TXT_ACCESS_CHECK_ALL'                      => $_ARRAYLANG['TXT_ACCESS_CHECK_ALL'],
            'TXT_ACCESS_UNCHECK_ALL'                    => $_ARRAYLANG['TXT_ACCESS_UNCHECK_ALL'],
            'TXT_ACCESS_ASSOCIATED_GROUPS'              => $_ARRAYLANG['TXT_ACCESS_ASSOCIATED_GROUPS'],
            'TXT_ACCESS_MODIFICATION_ACCESS'            => $_ARRAYLANG['TXT_ACCESS_MODIFICATION_ACCESS'],
            'TXT_ACCESS_MODIFY_PROFILE_ATTRIBUTE_MSG'   => $_ARRAYLANG['TXT_ACCESS_MODIFY_PROFILE_ATTRIBUTE_MSG'],
            'TXT_ACCESS_SELECT_ALLOWED_MODIFY_GROUPS'   => $_ARRAYLANG['TXT_ACCESS_SELECT_ALLOWED_MODIFY_GROUPS'],
            'TXT_ACCESS_SPECIAL_MENU_PERM'              => $_ARRAYLANG['TXT_ACCESS_SPECIAL_MENU_PERM'],
            'TXT_ACCESS_NONE'                           => $_ARRAYLANG['TXT_ACCESS_NONE'],
            'TXT_ACCESS_ONLY_HIGHER_OPTION_ACCESS'      => $_ARRAYLANG['TXT_ACCESS_ONLY_HIGHER_OPTION_ACCESS'],
            'TXT_ACCESS_ONLY_LOWER_OPTION_ACCESS'       => $_ARRAYLANG['TXT_ACCESS_ONLY_LOWER_OPTION_ACCESS'],
            'TXT_ACCESS_FRAMES'                         => $_ARRAYLANG['TXT_ACCESS_FRAMES'],
            'TXT_ACCESS_FRAME'                          => $_ARRAYLANG['TXT_ACCESS_FRAME'],
            'TXT_ACCESS_ADD_NEW_FRAME'                  => $_ARRAYLANG['TXT_ACCESS_ADD_NEW_FRAME'],
            'TXT_ACCESS_SELECT_OPTION'                  => $_ARRAYLANG['TXT_ACCESS_SELECT_OPTION'],
            'TXT_ACCESS_SELECT_OPTIONS'                 => $_ARRAYLANG['TXT_ACCESS_SELECT_OPTIONS'],
            'TXT_ACCESS_ADD_NEW_SELECT_OPTION'          => $_ARRAYLANG['TXT_ACCESS_ADD_NEW_SELECT_OPTION'],
            'TXT_ACCESS_ID'                             => $_ARRAYLANG['TXT_ACCESS_ID']
        ));

        $this->_objTpl->setGlobalVariable(array(
            'TXT_ACCESS_NAME'                           => $_ARRAYLANG['TXT_ACCESS_NAME'],
            'TXT_ACCESS_MODIFY_ATTRIBUTE'               => $_ARRAYLANG['TXT_ACCESS_MODIFY_ATTRIBUTE'],
            'TXT_ACCESS_DELETE_ATTRIBUTE'               => $_ARRAYLANG['TXT_ACCESS_DELETE_ATTRIBUTE'],
            'TXT_ACCESS_CONFIRM_DELETE_ATTRIBUTE_MSG'   => str_replace("\n", '\n', $_ARRAYLANG['TXT_ACCESS_CONFIRM_DELETE_ATTRIBUTE_MSG']),
            'TXT_ACCESS_OPERATION_IRREVERSIBLE'         => $_ARRAYLANG['TXT_ACCESS_OPERATION_IRREVERSIBLE'],
            'TXT_ACCESS_MOVE_UP'                        => $_ARRAYLANG['TXT_ACCESS_MOVE_UP'],
            'TXT_ACCESS_MOVE_DOWN'                      => $_ARRAYLANG['TXT_ACCESS_MOVE_DOWN'],
            'TXT_ACCESS_GENERAL'                        => $_ARRAYLANG['TXT_ACCESS_GENERAL'],
            'TXT_ACCESS_PROFILE_ATTRIBUTES'             => $_ARRAYLANG['TXT_ACCESS_PROFILE_ATTRIBUTES'],
            'ACCESS_CHILD_SORT_ORDER_DISPLAY'           => $objAttribute->getSortType() == 'custom' ? 'inline' : 'none'
        ));

        if ((!$objAttribute->getId() || $objAttribute->isCustomAttribute($objAttribute->getId())) && $objAttribute->getParent() !== 'title') {
            foreach (FWLanguage::getLanguageArray() as $langId => $arrLanguage) {
                if ($arrLanguage['frontend']) {
                    $this->_objTpl->setVariable(array(
                        'ACCESS_ATTRIBUTE_LANG_ID'      => $langId,
                        'ACCESS_ATTRIBUTE_LANG_NAME'    => htmlentities(FWLanguage::getLanguageParameter($langId, 'name'), ENT_QUOTES, CONTREXX_CHARSET),
                        'ACCESS_ATTRIBUTE_NAME'         => htmlentities($objAttribute->getName($langId), ENT_QUOTES, CONTREXX_CHARSET)
                    ));
                    $this->_objTpl->parse('access_attribute_name_list');
                }
            }

            $this->_objTpl->touchBlock('access_attribute_name');
            $this->_objTpl->hideBlock('access_attribute_core_name_edit');
            $this->_objTpl->hideBlock('access_attribute_core_name');
        } else {
            $this->_objTpl->setVariable('ACCESS_ATTRIBUTE_NAME', htmlentities($objAttribute->getName(), ENT_QUOTES, CONTREXX_CHARSET));
            $this->_objTpl->hideBlock('access_attribute_name');

            if ($objAttribute->isNamesModifiable()) {
                // only core child attributes should be allowed to be modifiable
                $this->_objTpl->touchBlock('access_attribute_core_name_edit');
                $this->_objTpl->hideBlock('access_attribute_core_name');
            } else {
                $this->_objTpl->hideBlock('access_attribute_core_name_edit');
                $this->_objTpl->touchBlock('access_attribute_core_name');
            }
        }

        $objGroup = $objFWUser->objGroup->getGroups();
        while (!$objGroup->EOF) {
            $var = in_array($objAttribute->getAccessId(), $objGroup->getDynamicPermissionIds()) ? 'associatedGroups' : 'notAssociatedGroups';
            $$var .= "<option value=\"".$objGroup->getId()."\">".htmlentities($objGroup->getName(), ENT_QUOTES, CONTREXX_CHARSET)." [".$objGroup->getType()."]</option>\n";
            $objGroup->next();
        }

        $this->_objTpl->setVariable(array(
            'ACCESS_ATTRIBUTE_ID'                       => $objAttribute->getId(),
            'ACCESS_ATTRIBUTE_NAME'                     => htmlentities($objAttribute->getName(), ENT_QUOTES, CONTREXX_CHARSET),
            'ACCESS_ATTRIBUTE_TYPE'                     => $objAttribute->getId() ? $objAttribute->getTypeDescription() : $objAttribute->getTypeMenu('name="access_attribute_type" onchange="accessSwitchType(this.value)" style="width:300px;"'),
            'ACCESS_TEXT_MULTILINE_OPTION_DISPLAY'      => $objAttribute->isTypeModifiable() && in_array($objAttribute->getType(), array('text', 'textarea')) ? 'inline' : 'none',
            'ACCESS_TEXT_MULTILINE_CHECKED'             => $objAttribute->isMultiline() ? 'checked="checked"' : '',
            'ACCESS_ATTRIBUTE_MANDATORY_FRAME_DISPLAY'  => $objAttribute->hasMandatoryOption() ? '' : 'none',
            'ACCESS_ATTRIBUTE_MANDATORY_YES'            => $objAttribute->isMandatory() ? 'checked="checked"' : '',
            'ACCESS_ATTRIBUTE_MANDATORY_NO'             => $objAttribute->isMandatory() ? '' : 'checked="checked"',
            'ACCESS_ATTRIBUTE_CHILD_FRAME_DISPLAY'      => $objAttribute->hasChildOption() ? '' : 'none',
            'ACCESS_ATTRIBUTE_CHILD_FRAME_ROWS'         => count($objAttribute->getChildren()) + 2,
            'ACCESS_ATTRIBUTE_SORT_FRAME_DISPLAY'       => $objAttribute->hasChildOption() ? '' : 'none',
            'ACCESS_ATTRIBUTE_SORT_FRAME_ROW'           => $objAttribute->hasMandatoryOption() && $objAttribute->hasSortableOption() ? 'row1' : 'row2',
            'ACCESS_ATTRIBUTE_SORT_TYPE'                => $objAttribute->isSortOrderModifiable() ? $objAttribute->getSortTypeMenu('name="access_attribute_sort_type" style="width:300px;" onchange="accessSwitchSortType(this.value)"') : $objAttribute->getSortTypeDescription(),
            'ACCESS_ATTRIBUTE_NOT_ASSOCIATED_GROUPS'    => $notAssociatedGroups,
            'ACCESS_USER_ASSOCIATED_GROUPS'             => $associatedGroups,
            'ACCESS_ATTRIBUTE_SELECT_ACCESS_DISPLAY'    => $objAttribute->isProtected() ? '' : 'none',
            'ACCESS_ATTRIBUTE_ACCESS_ALL_CHECKED'       => $objAttribute->isProtected() ? '' : 'checked="checked"',
            'ACCESS_PERMISSON_TAB_DISPLAY'              => $objAttribute->hasProtectionOption() ? '' : 'none',
            'ACCESS_ATTRIBUTE_SPECIAL_MENU_ACCESS'      => $objAttribute->getType() == 'menu' ? '' : 'none',
            'ACCESS_ATTRIBUTE_SPECIAL_NONE_CHECKED'     => $objAttribute->getSpecialProtection() == '' ? 'checked="checked"' : '',
            'ACCESS_ATTRIBUTE_SPECIAL_HIGHER_CHECKED'   => $objAttribute->getSpecialProtection() == 'menu_select_higher' ? 'checked="checked"' : '',
            'ACCESS_ATTRIBUTE_SPECIAL_LOWER_CHECKED'    => $objAttribute->getSpecialProtection() == 'menu_select_lower' ? 'checked="checked"' : '',
            'ACCESS_CHILDREN_TAB_DISPLAY'               => in_array($objAttribute->getType(), array('frame', 'history')) ? '' : 'none',
            'ACCESS_MENU_OPTION_TAB_DISPLAY'            => $objAttribute->getType() == 'menu' ? '' : 'none',
            'ACCESS_FRAMES_TAB_DISPLAY'                 => $objAttribute->getType() == 'group' ? '' : 'none',
        ));

        if ($objAttribute->getParent()) {
            switch ($objAttribute->getType()) {
                case 'menu_option':
                    $addOtherchildTxt = $_ARRAYLANG['TXT_ACCESS_ADD_OTHER_MENU_OPTION'];
                    break;

                case 'frame':
                    $addOtherchildTxt = $_ARRAYLANG['TXT_ACCESS_ADD_OTHER_FRAME'];
                    break;

                default:
                    $addOtherchildTxt = $_ARRAYLANG['TXT_ACCESS_ADD_OTHER_ATTRIBUTE'];
                    break;
            }

            $this->_objTpl->setVariable('ACCESS_ADD_OTHER_CHILD_TXT', $addOtherchildTxt);
            $this->_objTpl->parse('access_add_other_child_box');
        } else {
            $this->_objTpl->hideBlock('access_add_other_child_box');
        }

        $row = 1;
        $sortNr = 0;
        if ($objAttribute->hasChildOption() && count($objAttribute->getChildren())) {
            $frameBlock = $objAttribute->getType() == 'menu' ? 'access_attribute_menu_options' : ($objAttribute->getType() == 'group' ? 'access_attribute_frames' : 'access_attribute_children');
            $frameListBlock = $objAttribute->getType() == 'menu' ? 'access_attribute_menu_option_list' : ($objAttribute->getType() == 'group' ? 'access_attribute_frames_list' : 'access_attribute_child_list');
            $frameAddBlock = $objAttribute->getType() == 'menu' ? 'access_attribute_menu_option_add' : ($objAttribute->getType() == 'group' ? 'access_attribute_frame_add' : 'access_attribute_child_add');

            foreach ($objAttribute->getChildren() as $childAttributId) {
                $objChildAttribute = $objAttribute->getById($childAttributId);
                if (($objChildAttribute->getId())) {
                    if ($objAttribute->isSortOrderModifiable() && $objChildAttribute->isModifiable()) {
                        $this->_objTpl->setVariable(array(
                            'ACCESS_CHILD_ATTRIBUTE_ID'         => $childAttributId,
                            'ACCESS_CHILD_ATTRIBUTE_SORT_NR'    => $sortNr++
                        ));
                        $this->_objTpl->touchBlock($frameListBlock.'_sort');
                    } else {
                        $this->_objTpl->hideBlock($frameListBlock.'_sort');
                    }

                    if ($objChildAttribute->isModifiable()) {
                        $this->_objTpl->setVariable('ACCESS_CHILD_ATTRIBUTE_ID', $childAttributId);
                        $this->_objTpl->touchBlock($frameListBlock.'_function_modify');
                        $this->_objTpl->hideBlock($frameListBlock.'_function_no_modify');
                    } else {
                        $this->_objTpl->touchBlock($frameListBlock.'_function_no_modify');
                        $this->_objTpl->hideBlock($frameListBlock.'_function_modify');
                    }

                    if ($objAttribute->isChildrenModifiable() && $objChildAttribute->isRemovable()) {
                        $this->_objTpl->setVariable('ACCESS_CHILD_ATTRIBUTE_ID', $childAttributId);
                        $this->_objTpl->touchBlock($frameListBlock.'_function_delete');
                        $this->_objTpl->hideBlock($frameListBlock.'_function_no_delete');
                    } else {
                        $this->_objTpl->touchBlock($frameListBlock.'_function_no_delete');
                        $this->_objTpl->hideBlock($frameListBlock.'_function_delete');
                    }

                    if ($objAttribute->isChildrenModifiable()) {
                        $this->_objTpl->setVariable('ACCESS_CHILD_ATTRIBUTE_ROW_CLASS', $row % 2);
                        $this->_objTpl->touchBlock($frameAddBlock);
                    } else {
                        $this->_objTpl->hideBlock($frameAddBlock);
                    }

                    $this->_objTpl->setVariable(array(
                        'ACCESS_CHILD_ATTRIBUTE_ROW_CLASS'  => $row++ % 2,
                        'ACCESS_CHILD_ATTRIBUTE_NAME'       => htmlentities($objChildAttribute->getName(), ENT_QUOTES, CONTREXX_CHARSET),
                        'ACCESS_CHILD_ATTRIBUTE'            => $this->parseAttribute(new User(), $childAttributId, 0, true, true),
                        'ACCESS_CHILD_ATTRIBUTE_ID'         => $childAttributId,
                        'ACCESS_CHILD_ATTRIBUTE_NAME_JS'    => urlencode(htmlentities($objChildAttribute->getName(), ENT_QUOTES, CONTREXX_CHARSET))
                    ));

                    $this->_objTpl->parse($frameListBlock);
                }
            }

            $this->_objTpl->parse($frameBlock);
        } else {
            $this->_objTpl->hideBlock('access_attribute_children');
            $this->_objTpl->hideBlock('access_attribute_frames');
            $this->_objTpl->hideBlock('access_attribute_menu_options');
        }
        $this->_objTpl->parse('module_access_config_attribute_modify');
    }


    function _configDeleteAttribute()
    {
        global $_ARRAYLANG, $objDatabase;

        // only administrators are allowed to modify the config
        if (!Permission::hasAllAccess()) {
            Permission::noAccess();
        }

        $objAttribute = new User_Profile_Attribute();
        $attributeId = isset($_REQUEST['id']) ? contrexx_addslashes($_REQUEST['id']) : 0;
        if ($attributeId && $objAttribute->load($attributeId)) {
            if ($objAttribute->delete()) {
                $this->arrStatusMsg['ok'][] = $_ARRAYLANG['TXT_ACCESS_SUCCESS_DEL_ATTRIBUTE'];
                if ($objAttribute->getParent()) {
                    $_REQUEST['id'] = $objAttribute->getParent();
                    return $this->_configModifyAttribute();
                } else {
                    $_REQUEST['id'] = 0;
                    return $this->_configAttributes();
                }
            } else {
                $this->arrStatusMsg['error'][] = $objAttribute->getErrorMsg();
                if ($objAttribute->getParent()) {
                    $_REQUEST['id'] = $objAttribute->getParent();
                    return $this->_configModifyAttribute();
                } else {
                    $_REQUEST['id'] = 0;
                    return $this->_configAttributes();
                }
            }
        } else {
            $this->arrStatusMsg['error'][] = $_ARRAYLANG['TXT_ACCESS_INVALID_PROFILE_ATTRIBUTE_SPECIFIED'];
            $_REQUEST['id'] = 0;
            return $this->_configAttributes();
        }
    }


    /**
     * Show the HTML code of an attribute.
     *
     * @todo: This method is not yet usable. Extend to method $this->getUnparsedAtrributeCode() so that it returns the unparsed HTML code.
     */
    private function _configAttributeCode()
    {
        global $_ARRAYLANG;

        $objFWUser = FWUser::getFWUserObject();
        $objAttribute = new User_Profile_Attribute();
        $attributeId = isset($_GET['id']) ? contrexx_addslashes($_GET['id']) : 0;
        if ($attributeId && ($objAttribute = $objFWUser->objUser->objAttribute->getById($attributeId))) {
            $this->_objTpl->addBlockfile('ACCESS_CONFIG_TEMPLATE', 'module_access_config_attribute_code', 'module_access_config_attribute_code.html');
            $this->_objTpl->setVariable(array(
                'TXT_ACCESS_ATTRIBUTE_CODE_DESC'    => $_ARRAYLANG['TXT_ACCESS_ATTRIBUTE_CODE_DESC'],
                'TXT_ACCESS_BACK'                   => $_ARRAYLANG['TXT_ACCESS_BACK'],
                'ACCESS_ATTRIBUTE_CODE_TITLE'       => '',
                'ACCESS_ATTRIBUTE_CODE'             => $this->getUnparsedAtrributeCode($attributeId)
            ));
            $this->_objTpl->parse('module_access_config_attribute_code');
        } else {
            $this->arrStatusMsg['error'][] = $_ARRAYLANG['TXT_ACCESS_INVALID_PROFILE_ATTRIBUTE_SPECIFIED'];
            $_REQUEST['id'] = 0;
            return $this->_configAttributes();
        }
    }


    function _configMails()
    {
        global $_ARRAYLANG;

        $this->_objTpl->addBlockfile('ACCESS_CONFIG_TEMPLATE', 'module_access_config_mail', 'module_access_config_mail.html');
        $objFWUser = FWUser::getFWUserObject();

        $this->_objTpl->setVariable(array(
            'TXT_ACCESS_EMAIL_NOTIFICATIONS'    => $_ARRAYLANG['TXT_ACCESS_EMAIL_NOTIFICATIONS'],
            'TXT_ACCESS_TYPE'                   => $_ARRAYLANG['TXT_ACCESS_TYPE'],
            'TXT_ACCESS_LANGUAGE'               => $_ARRAYLANG['TXT_ACCESS_LANGUAGE'],
            'TXT_ACCESS_MAIL_SUBJECT'           => $_ARRAYLANG['TXT_ACCESS_MAIL_SUBJECT'],
            'TXT_ACCESS_FUNCTIONS'              => $_ARRAYLANG['TXT_ACCESS_FUNCTIONS']
        ));

        $this->_objTpl->setGlobalVariable(array(
            'TXT_ACCESS_CONFIRM_DELETE_MAIL'    => rawurlencode($_ARRAYLANG['TXT_ACCESS_CONFIRM_DELETE_MAIL']),
            'TXT_ACCESS_DELETE_MAIL_TEMPLATE'   => $_ARRAYLANG['TXT_ACCESS_DELETE_MAIL_TEMPLATE'],
            'TXT_ACCESS_COPY_MAIL_TEMPLATE'     => $_ARRAYLANG['TXT_ACCESS_COPY_MAIL_TEMPLATE'],
            'TXT_ACCESS_MODIFY_MAIL_TEMPLATE'   => $_ARRAYLANG['TXT_ACCESS_MODIFY_MAIL_TEMPLATE']
        ));

        $objUserMail = $objFWUser->getMail();
        $objUserMail->loadMails();
        while (!$objUserMail->EOF) {
            while (!$objUserMail->languageEOF) {
                $this->_objTpl->setVariable(array(
                    'ACCESS_ROW_CLASS_NR'    => $objUserMail->getLangId() ? 2 : 1,
                    'ACCESS_MAIL_TYPE'       => $objUserMail->getType(),
                    'ACCESS_MAIL_LANG'       => $objUserMail->getLangId(),
                    'ACCESS_MAIL_TYPE_TXT'   => $objUserMail->getLangId() ? '&rarr; '.$objUserMail->getTypeDescription() : $objUserMail->getTypeDescription(),
                    'ACCESS_MAIL_TYPE_STYLE' => $objUserMail->getLangId() ? 'text-indent:10px;' : '',
                    'ACCESS_MAIL_LANGUAGE'   => $objUserMail->getLangId() ? FWLanguage::getLanguageParameter($objUserMail->getLangId(), 'name') : $_ARRAYLANG['TXT_ACCESS_ALL'],
                    'ACCESS_MAIL_SUBJECT'    => htmlentities($objUserMail->getSubject(), ENT_QUOTES, CONTREXX_CHARSET)
                ));

                if ($objUserMail->getLangId()) {
                    $this->_objTpl->hideBlock('access_email_copy');
                    $this->_objTpl->hideBlock('access_email_delete_space');
                    $this->_objTpl->touchBlock('access_email_delete');
                } else {
                    $this->_objTpl->touchBlock('access_email_copy');
                    $this->_objTpl->touchBlock('access_email_delete_space');
                    $this->_objTpl->hideBlock('access_email_delete');
                }

                $this->_objTpl->parse('access_email_list');

                $objUserMail->nextLanguage();
            }
            $objUserMail->next();
        }
        $this->_objTpl->parse('module_access_config_mail');
    }


    function _configModifyMails($copy = false)
    {
        global $_ARRAYLANG;

        if (empty($_REQUEST['type'])) {
            return $this->_configMails();
        }

        $objFWUser = FWUser::getFWUserObject();
        $objUserMail = $objFWUser->getMail();

        if ($copy) {
            $objUserMail->load(contrexx_addslashes($_REQUEST['type']));
            $objUserMail->setLangId(!empty($_REQUEST['access_mail_lang']) ? intval($_REQUEST['access_mail_lang']) : 0);
        } elseif (!$objUserMail->load(contrexx_addslashes($_REQUEST['type']), !empty($_REQUEST['access_mail_lang']) ? intval($_REQUEST['access_mail_lang']) : null)) {
            return $this->_configMails();
        }

        if (isset($_POST['access_save_mail'])|| isset($_GET['access_change_format'])) {
            // only administrators are allowed to modify the config
            if (!Permission::hasAllAccess()) {
                Permission::noAccess();
            }

            $objUserMail->setFormat(!empty($_POST['access_mail_format']) ? $_POST['access_mail_format'] : null);
            $objUserMail->setSubject(!empty($_POST['access_mail_subject']) ? contrexx_stripslashes($_POST['access_mail_subject']) : '');
            $objUserMail->setSenderMail(!empty($_POST['access_mail_sender_address']) ? contrexx_stripslashes($_POST['access_mail_sender_address']) : '');
            $objUserMail->setSenderName(!empty($_POST['access_mail_sender_name']) ? contrexx_stripslashes($_POST['access_mail_sender_name']) : '');
            $objUserMail->setBodyText(!empty($_POST['access_mail_body_text']) ? contrexx_stripslashes($_POST['access_mail_body_text']) : '');
            $objUserMail->setBodyHtml(!empty($_POST['access_mail_body_html']) ? contrexx_stripslashes($_POST['access_mail_body_html']) : '');

            if (isset($_POST['access_save_mail'])) {
                if ($objUserMail->store()) {
                    $this->arrStatusMsg['ok'][] = $_ARRAYLANG['TXT_ACCESS_MAIL_STORED_SUCCESSFULLY'];
                    return $this->_configMails();
                } else {
                    $this->arrStatusMsg['error'] = array_merge($this->arrStatusMsg['error'], $objUserMail->getErrorMsg());
                }
            }
        }

        $this->_objTpl->addBlockfile('ACCESS_CONFIG_TEMPLATE', 'module_access_config_mail_modify', 'module_access_config_mail_modify.html');

        $this->_objTpl->setVariable(array(
            'TXT_ACCESS_MODIFY_EMAIL'           => $_ARRAYLANG['TXT_ACCESS_MODIFY_EMAIL'],
            'TXT_ACCESS_MAIL_SUBJECT'           => $_ARRAYLANG['TXT_ACCESS_MAIL_SUBJECT'],
            'TXT_ACCESS_SEND_AS'                => $_ARRAYLANG['TXT_ACCESS_SEND_AS'],
            'TXT_ACCESS_SENDER_ADDRESS'         => $_ARRAYLANG['TXT_ACCESS_SENDER_ADDRESS'],
            'TXT_ACCESS_SENDER_NAME'            => $_ARRAYLANG['TXT_ACCESS_SENDER_NAME'],
            'TXT_ACCESS_TEXT_BODY'              => $_ARRAYLANG['TXT_ACCESS_TEXT_BODY'],
            'TXT_ACCESS_CANCEL'                 => $_ARRAYLANG['TXT_ACCESS_CANCEL'],
            'TXT_ACCESS_SAVE'                   => $_ARRAYLANG['TXT_ACCESS_SAVE'],
            'TXT_ACCESS_TYPE'                   => $_ARRAYLANG['TXT_ACCESS_TYPE'],
            'TXT_ACCESS_LANGUAGE'               => $_ARRAYLANG['TXT_ACCESS_LANGUAGE'],
            'TXT_ACCESS_PLACEHOLDER_DIRECTORY'  => $_ARRAYLANG['TXT_ACCESS_PLACEHOLDER_DIRECTORY']
        ));

        if ($copy) {
            if (($language = $this->getMailLanguageMenu($objUserMail->getType(), $objUserMail->getLangId(), 'name="access_mail_lang" style="width:400px;"')) === false) {
                return $this->_configMails();
            }
        } elseif (!$objUserMail->getLangId()) {
            $language = '-';
        } else {
            $language = '<input type="hidden" name="access_mail_lang" value="'.$objUserMail->getLangId().'" />'.FWLanguage::getLanguageParameter($objUserMail->getLangId(), 'name');
        }

        $this->_objTpl->setVariable(array(
            'ACCESS_MAIL_ACTION'            => $copy ? 'copyMail' : 'modifyMail',
            'ACCESS_MAIL_TYPE'             => $objUserMail->getType(),
            'ACCESS_MAIL_TYPE_TXT'         => $objUserMail->getTypeDescription(),
            'ACCESS_MAIL_LANGUAGE'          => $language,
            'ACCESS_MAIL_SUBJECT'          => htmlentities($objUserMail->getSubject(), ENT_QUOTES, CONTREXX_CHARSET),
            'ACCESS_MAIL_FORMAT'           => $this->getMailFormatMenu($objUserMail->getFormat(), 'name="access_mail_format" onchange="document.getElementById(\'access_mail_form\').action=\'index.php?cmd=access&amp;act=config&amp;tpl='.($copy ? 'copyMail' : 'modifyMail').'&amp;type='.$objUserMail->getType().'&amp;access_mail_lang=\'+(typeof(document.getElementsByName(\'access_mail_lang\')[0]) != \'undefined\' ? document.getElementsByName(\'access_mail_lang\')[0].value : 0)+\'&amp;access_change_format=1\';document.getElementById(\'access_mail_form\').submit()" size="1" style="width:400px;"'),
            'ACCESS_MAIL_SENDER_ADDRESS'   => htmlentities($objUserMail->getSenderMail(), ENT_QUOTES, CONTREXX_CHARSET),
            'ACCESS_MAIL_SENDER_NAME'      => htmlentities($objUserMail->getSenderName(), ENT_QUOTES, CONTREXX_CHARSET),
            'ACCESS_MAIL_BODY_TEXT'        => htmlentities($objUserMail->getBodyText(), ENT_QUOTES, CONTREXX_CHARSET),
            'ACCESS_MAIL_BODY_HTML'        => $objUserMail->getFormat() != 'text' ? get_wysiwyg_editor('access_mail_body_html', $objUserMail->getBodyHtml()) : '<input type="hidden" name="access_mail_body_html" value="'.htmlentities($objUserMail->getBodyHtml(), ENT_QUOTES, CONTREXX_CHARSET).'" />',
            'ACCESS_MAIL_HTML_BODY_STAUTS' => $objUserMail->getFormat() != 'text' ? 'block' : 'none',
            'ACCESS_MAIL_TEXT_BODY_STAUTS' => $objUserMail->getFormat() == 'text' ? 'block' : 'none',
            'ACCESS_MAIL_HTML_BODY_CLASS'  => $objUserMail->getFormat() != 'text' ? 'active' : '',
            'ACCESS_MAIL_TEXT_BODY_CLASS'  => $objUserMail->getFormat() == 'text' ? 'active' : '',
        ));

        if ($objUserMail->getFormat() == 'text') {
            $this->_objTpl->setVariable('TXT_ACCESS_TEXT', $_ARRAYLANG['TXT_ACCESS_TEXT']);
            $this->_objTpl->touchBlock('access_mail_text_body');
            $this->_objTpl->hideBlock('access_mail_html_body');
        } elseif ($objUserMail->getFormat() == 'html') {
            $this->_objTpl->setVariable('TXT_ACCESS_HTML_UC', $_ARRAYLANG['TXT_ACCESS_HTML_UC']);
            $this->_objTpl->touchBlock('access_mail_html_body');
            $this->_objTpl->hideBlock('access_mail_text_body');
        } else {
            $this->_objTpl->setVariable(array(
                'TXT_ACCESS_HTML_UC'    => $_ARRAYLANG['TXT_ACCESS_HTML_UC'],
                'TXT_ACCESS_TEXT'       => $_ARRAYLANG['TXT_ACCESS_TEXT']
            ));
            $this->_objTpl->touchBlock('access_mail_html_body');
            $this->_objTpl->touchBlock('access_mail_text_body');
        }

        $nr = 0;
        foreach ($objUserMail->getPlaceholders() as $placeholder => $placeholderTxt) {
            $this->_objTpl->setVariable(array(
                'ACCESS_CLASS_ROW_NR'   => $nr++ % 2 ? 2 : 1,
                'ACCESS_PLACEHOLDER_TXT'    => $placeholderTxt,
                'ACCESS_PLACEHOLDER'        => $placeholder
            ));
            $this->_objTpl->parse('access_placeholder_list');
        }
        $this->_objTpl->parse('module_access_config_mail_modify');
        return true;
    }


    function _configDeleteMail()
    {
        global $_ARRAYLANG;

        // only administrators are allowed to modify the config
        if (!Permission::hasAllAccess()) {
            Permission::noAccess();
        }

        if (empty($_REQUEST['type'])) {
            return;
        }

        $objFWUser = FWUser::getFWUserObject();
        $objUserMail = $objFWUser->getMail();
        if (!$objUserMail->load(contrexx_addslashes($_REQUEST['type']), !empty($_REQUEST['access_mail_lang']) ? intval($_REQUEST['access_mail_lang']) : null)) {
            return;
        }

// TODO: Do you really need to reinitialize that?
        $objFWUser = FWUser::getFWUserObject();

        if ($objUserMail->delete()) {
            $this->arrStatusMsg['ok'][] = $_ARRAYLANG['TXT_ACCESS_EMAIL_DEL_SUCCESS'];
        } else {
            $this->arrStatusMsg['error'] = array_merge($this->arrStatusMsg['error'], $objUserMail->getErrorMsg());
        }
    }
}

?>
