<?php

/**
 * Content Sitemap
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @version        1.0.0
 * @package     contrexx
 * @subpackage  core
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Content Sitemap
 *
 * navigation tree
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @access        public
 * @version        1.0.0
 * @package     contrexx
 * @subpackage  core
 * @todo        Edit PHP DocBlocks!
 */
class ContentSitemap
{
    public $navtable = array();
    public $navlinks = array();
    public $navparent = array();
    public $navparentId = array();
    public $navdisplayorder = array();
    public $currentid;
    public $treeArray = array();
    public $navUsername = array();
    public $navChangelog = array();
    public $navName = array();
    public $navModule = array();
    public $navCmd = array();
    public $navDisplaystatus = array();
    public $navActiveStatus = array();
    public $navEditStatus = array();
    public $navProtection = array();
    public $navSons = array();
    public $navIsValidated = array();
    public $navIsRedirect = array();
    public $langId;
    public $requiredModuleNames = array('home','ids','error','login','core');
    public $nestedLists = '';
    public $nestedNavigation;
    private $rowIndex = 0;

    /**
    * Constructor
    *
    * @global ADONewConnection
    * @global InitCMS
    * @param  integer  $currentid
    * @param  boolean  $adminmode
    * @access public
    */
    function __construct($currentid)
    {
        global $objDatabase, $objInit;

        $this->langId=$objInit->userFrontendLangId;

        $query = "SELECT id, name FROM ".DBPREFIX."modules";
        $objResult = $objDatabase->Execute($query);
        if ($objResult === false) {
            return "contentManager::contentManager() database error";
        }
        while (!$objResult->EOF) {
            $arrModules[$objResult->fields['id']]=$objResult->fields['name'];
            $objResult->MoveNext();
        }

        $query = "SELECT n.cmd AS cmd,
                         n.catid AS catid,
                         n.catname AS catname,
                         n.displayorder AS displayorder,
                         n.username AS username,
                         n.module AS section,
                         n.displaystatus AS displaystatus,
                         n.activestatus AS activestatus,
                         n.editstatus AS editstatus,
                         n.parcat AS parcat,
                         n.protected AS protected,
                         FROM_UNIXTIME(n.changelog,'%d.%m.%Y %T') AS changelog,
                         n.is_validated AS isValidated
                    FROM ".DBPREFIX."content_navigation AS n
                   WHERE n.lang=".$this->langId."
                ORDER BY n.parcat ASC, n.displayorder ASC";
        $objResult = $objDatabase->Execute($query);
        if ($objResult === false) {
            return "contentManager::contentManager() database error";
        }
        while (!$objResult->EOF) {
            $s= $arrModules[$objResult->fields['section']];
            // $s=$objResult->fields['section'];
            $c=$objResult->fields['cmd'];
            $section = ( ($s=="") ? "" : "&amp;section=$s" );
            $cmd     = ( ($c=="") ? "" : "&amp;cmd=$c" );
            $link    = $_SERVER['PHP_SELF']."?page=".$objResult->fields['catid'].$section.$cmd;

            $this->navtable[$objResult->fields['parcat']][$objResult->fields['catid']]=$objResult->fields['catname'];
            $this->navparent[$objResult->fields['catid']][$objResult->fields['parcat']]=$objResult->fields['catname'];
            $this->navparentId[$objResult->fields['catid']]=$objResult->fields['parcat'];
            $this->navName[$objResult->fields['catid']]=$objResult->fields['catname'];
            $this->navlinks[$objResult->fields['catid']]=$link;
            $this->navModule[$objResult->fields['catid']]= $arrModules[$objResult->fields['section']];//section
            $this->navCmd[$objResult->fields['catid']]=$c;//cmd
            $this->navdisplayorder[$objResult->fields['catid']]=$objResult->fields['displayorder'];
            $this->navUsername[$objResult->fields['catid']]=$objResult->fields['username'];
            $this->navChangelog[$objResult->fields['catid']]=$objResult->fields['changelog'];
            $this->navProtected[$objResult->fields['catid']]=$objResult->fields['protected'];
            $this->navDisplaystatus[$objResult->fields['catid']]=$objResult->fields['displaystatus'];
            $this->navActiveStatus[$objResult->fields['catid']]=$objResult->fields['activestatus'];
            $this->navEditStatus[$objResult->fields['catid']]=$objResult->fields['editstatus'];
            $this->navIsValidated[$objResult->fields['catid']] = $objResult->fields['isValidated'];
            $this->currentid = $currentid;

            $objSubResult = $objDatabase->Execute("
                SELECT redirect
                  FROM ".DBPREFIX."content
                 WHERE id=".$objResult->fields['catid'].' AND lang_id='.$this->langId
            );
            $this->navIsRedirect[$objResult->fields['catid']] = (empty($objSubResult->fields['redirect'])) ? false : true;
            $objResult->MoveNext();
        }
        unset($arrModules);
        $this->nestedNavigation = $this->buildNestedNavigationArray();
        return true;
    }

    /**
     * builds the nested navigation array
     *
     * @param array $flatTree the flat navigation array (as in $this->navtable)
     * @param integer $level current level (helper argument)
     * @return array nested navigation array
     */
    function buildNestedNavigationArray($flatTree = array(), $level = 1)
    {
        if(count($flatTree) == 0){ //if empty, use full tree
            $flatTree = $this->navtable[0];
        }
        $arrNestedTree = array();
        foreach ($flatTree as $pageId => $title) {
        	if(array_key_exists($pageId, $this->navtable)){
                $arrNestedTree[$pageId] = array(
                    'pageId'    => $pageId,
                    'level'     => $level,
                    'title'     => $title,
                    'children'  => $this->buildNestedNavigationArray($this->navtable[$pageId], $level + 1),
                );
        	}else{
                $arrNestedTree[$pageId] = array(
                    'pageId'    => $pageId,
                    'level'     => $level,
                    'title'     => $title,
                );
        	}
        }
        return $arrNestedTree;
    }


    /**
     * returns the nested navigation tree as an unordered list (HTML string)
     *
     * @param integer $rootPageId the page ID of the root element, whole tree if not specified.
     * @return string HTML unordered list: the nested navigation tree
     */


    function _getNestedTemplate($index = 0){
        $template = file_get_contents(ASCMS_ADMIN_TEMPLATE_PATH.'/content_sitemap_nested_list.html');
        return str_replace('{INDEX}', $index, $template);
    }


    /**
     * recursively parse the nested sitemap
     *
     * @param array $arrNestedNavigation nested array of pages
     * @param string $ulRootId ID of the roo UL element
     * @param string $ulClass class ot the UL elements
     * @param string $liClasses classes of the LI elements
     * @param string $handlerClass class of the drag handler
     * @param HTML_Template_Sigma $objTpl sigma template object, must be null on initial call
     * @return string parsed nested list
     */
    function parseNestedSitemap($arrNestedNavigation, $ulRootId = 'sortableNavigation', $ulClass = 'sortableNavigation', $liClasses = 'sortablePage', $handlerClass = 'sort-handle', &$objTpl = null)
    {
        global $_CORELANG;

        $first = false;
        if(is_null($objTpl)){
            $objTpl = new HTML_Template_Sigma(ASCMS_ADMIN_TEMPLATE_PATH);
            $objTpl->setErrorHandling(PEAR_ERROR_DIE);
            $objTpl->loadTemplateFile('content_sitemap_nested_list.html', true, true);
            $objTpl->setGlobalVariable(array(
                'TXT_EDIT'                      => $_CORELANG['TXT_EDIT'],
                'TXT_DELETE'                    => $_CORELANG['TXT_DELETE'],
                'TXT_TEMPLATE'                  => $_CORELANG['TXT_TEMPLATE'],
                'TXT_PAGE_ACTIVATE'             => $_CORELANG['TXT_PAGE_ACTIVATE'],
                'TXT_ADD_REPOSITORY'            => $_CORELANG['TXT_ADD_REPOSITORY'],
       	        'DIRECTORY_INDEX'               => CONTREXX_DIRECTORY_INDEX,
            ));
            $first = true;
        }
        if($first){
            $objTpl->setVariable(array(
                'SITEMAP_ROOT_UL_ID' => 'id="'.$ulRootId.'"',
            ));
        }

        $objTpl->setVariable(array(
            'SITEMAP_UL_CLASS'  => $ulClass,
        ));

        $objTpl->parse('list_start');
   	    $objTpl->parse('list');

   	    $arrIndex = 0;
            $objFWUser = FWUser::getFWUserObject();
            $isAdmin = $objFWUser->objUser->getAdminStatus();
            $currentUser = $objFWUser->objUser->getUsername();
        foreach ($arrNestedNavigation as $pageId => $arrPage) {
            /*
             * UnApproved page will be visible only to admin and Owner of the page
             */
            if(!$this->navIsValidated[$pageId] && $this->navUsername[$pageId] != $currentUser && !$isAdmin){
                continue;
            }
            $arrIndex++;
        	$rc      = "row".($this->rowIndex % 2 == 0 ? 1 : 2);         //rowclass
        	if($this->navIsValidated[$pageId] == 0){
        	    $rc = "rowWarn";
        	}
        	$this->rowIndex++;
        	$hasChildren = isset($arrPage['children']) && is_array($arrPage['children']);

            if (!$this->navCmd[$pageId] && $this->navModule[$pageId]) {
                $objTpl->touchBlock('repository');
            } else {
                $objTpl->hideBlock('repository');
            }

            if($hasChildren){
                $objTpl->parse('treenodeicon');
            }

            $moduleReference = '';
            if (empty($this->navModule[$pageId])) {
                $this->navModule[$pageId] = "&nbsp;";
            } else {
                $moduleName = $this->navModule[$pageId];
                // Set $moduleName for
                //  news, calendar, community, directory, docsys, egov, feed,
                //  forum, gallery, guestbook, livecam, market, media\d&archive=archive1,
                //  memberdir, newsletter, podcast, recommend, shop, voting, blog (soon),
                //  support (soon), contact (no content for the time being),
                //  (more to come).
                // Clear $moduleName for
                //  core, error, login (-> user?), agb, imprint, privacy, search,
                //  sitemap, home, ids, (more to come).
                // Don't link to these modules.
                $moduleReference = preg_replace(
                    '/^(?:core|error|login||agb|imprint|privacy|search|sitemap|home|ids)$/',
                    '',
                    $moduleName
                );
                // Fix the following URI parts to include necessary parts.
                $moduleReference = preg_replace(
                    '/^media(\d)$/',
                    'media&amp;archive=archive$1',
                    $moduleReference
                );
            }

            $iconPartRedirect = $this->navIsRedirect[$pageId] ? '_redirect' : '';
            $iconPartLocked = '';
            if($this->navProtected[$pageId]){
                $iconPartRedirect = '';
                $iconPartLocked = '_locked';
            }

            $pageVisible = $this->navDisplaystatus[$pageId] == 'on';
            $objTpl->setVariable(array(
       	        'SITEMAP_PAGE_ID'               => $arrPage['pageId'],
       	        'SITEMAP_UL_CLASS'              => $ulClass,
       	        'SITEMAP_LI_CLASSES'            => $liClasses,
       	        'SITEMAP_HANDLER_CLASS'         => $handlerClass,
       	        'SITEMAP_ROWCLASS'              => $rc,
       	        'SITEMAP_LED_COLOR'             => $this->navActiveStatus[$pageId] == 1 ? 'green' : 'red',
       	        'IS_CORE'                       => in_array($this->navModule[$pageId], $this->requiredModuleNames) ? '_core' : '',
       	        'IS_VISIBLE'                    => $pageVisible ? 'on' : 'off',
       	        'IS_REDIRECT'                   => $iconPartRedirect,
       	        'IS_LOCKED'                     => $iconPartLocked,
       	        'SITEMAP_PAGE_TITLE'            => htmlentities($arrPage['title'], ENT_QUOTES, CONTREXX_CHARSET),
       	        'SITEMAP_PAGE_TITLE_HREF'       => $this->_getPageClickHref($moduleReference, $pageId),
       	        'SITEMAP_USERNAME'              => $this->navUsername[$pageId],
       	        'SITEMAP_LAST_EDITED'           => sprintf('%s (%s)', $this->navChangelog[$pageId], $_CORELANG['TXT_CONTENT_EDITSTATUS_'.strtoupper($this->navEditStatus[$pageId])]),
       	        'SITEMAP_PAGE_NODE_CLASS'       => $hasChildren ? 'hasChildren nodeExpanded' : 'hasNoChildren',
       	        'SITEMAP_MODULE'                => $this->navModule[$pageId],
       	        'SITEMAP_PAGE_CMD'              => $this->navCmd[$pageId],
       	        'TXT_STATUS_VISIBILITY'         => $_CORELANG[$pageVisible ? 'TXT_STATUS_VISIBLE' : 'TXT_STATUS_INVISIBLY'],
       	    ));
            $objTpl->hideBlock('list_start');

            $objTpl->parse('item_start');
            $objTpl->parse('list');

           	if($hasChildren){
           	    $this->parseNestedSitemap($arrPage['children'], $ulRootId, $ulClass, $liClasses, $handlerClass, $objTpl);
        	}
        	$objTpl->touchBlock('item_end');
            $objTpl->parse('item_end');
            $objTpl->parse('list');
        }

        $objTpl->touchBlock('list_end');
        $objTpl->parse('list_end');
        $objTpl->parse('list');

        if($first){
            return $objTpl->get();
        }
    }

    /**
     * parse the global config template in the sitemap overview
     *
     * @param HTML_Template_Sigma $objTpl
     *
     * @global array $_CORELANG[]
     * @global array $_CONFIG[]
     * @global ADOConnection $objDatabase
     */
    private function _setupSiteConfig($objTpl){
        global $_CORELANG, $_CONFIG, $objDatabase;

        $arrLanguages = FWLanguage::getLanguageArray();
        $langName = $arrLanguages[FRONTEND_LANG_ID]['name'];
        $objTpl->setGlobalVariable(array(
            'TXT_SYSTEM_SETTINGS'                               => $_CORELANG['TXT_SYSTEM_SETTINGS'],
            'TXT_SETTINGS_GLOBAL_TITLE'                         => $_CORELANG['TXT_SETTINGS_GLOBAL_TITLE'],
            'TXT_SAVE'                                          => $_CORELANG['TXT_SAVE'],
            'TXT_UPDATE_ALL_SITES'                              => sprintf($_CORELANG['TXT_UPDATE_ALL_SITES'], $langName),
            'TXT_CACHING_STATUS'                                => $_CORELANG['TXT_CACHING_STATUS'],
            'TXT_THEMES'                                        => $_CORELANG['TXT_THEMES'],
            'TXT_PROTECTION'                                    => $_CORELANG['TXT_PROTECTION'],
            'TXT_PROTECTION_CHANGE'                             => $_CORELANG['TXT_PROTECTION_CHANGE'],
            'TXT_RECURSIVE_CHANGE'                              => $_CORELANG['TXT_RECURSIVE_CHANGE'],
            'TXT_GROUPS'                                        => $_CORELANG['TXT_GROUPS'],
            'TXT_GROUPS_DEST'                                   => $_CORELANG['TXT_GROUPS_DEST'],
            'TXT_SELECT_ALL'                                    => $_CORELANG['TXT_SELECT_ALL'],
            'TXT_DESELECT_ALL'                                  => $_CORELANG['TXT_DESELECT_ALL'],
            'TXT_ACCEPT_CHANGES'                                => $_CORELANG['TXT_ACCEPT_CHANGES'],
            'TXT_PUBLIC_PAGE'                                   => $_CORELANG['TXT_PUBLIC_PAGE'],
            'TXT_BACKEND_RELEASE'                               => $_CORELANG['TXT_BACKEND_RELEASE'],
            'TXT_LIMIT_GROUP_RIGHTS'                            => $_CORELANG['TXT_LIMIT_GROUP_RIGHTS'],
            'TXT_TARGET_BLANK'                                  => $_CORELANG['TXT_TARGET_BLANK'],
            'TXT_TARGET_TOP'                                    => $_CORELANG['TXT_TARGET_TOP'],
            'TXT_TARGET_PARENT'                                 => $_CORELANG['TXT_TARGET_PARENT'],
            'TXT_TARGET_SELF'                                   => $_CORELANG['TXT_TARGET_SELF'],
            'TXT_OPTIONAL_CSS_NAME'                             => $_CORELANG['TXT_OPTIONAL_CSS_NAME'],
            'TXT_FRONTEND_PERMISSION'                           => $_CORELANG['TXT_FRONTEND_PERMISSION'],
            'TXT_CONFIRM_SET_GLOBAL_THEMES_ID'                  => $_CORELANG['TXT_CONFIRM_SET_GLOBAL_THEMES_ID'],
            'TXT_CONFIRM_SET_GLOBAL_CSSNAME'                    => $_CORELANG['TXT_CONFIRM_SET_GLOBAL_CSSNAME'],
            'TXT_CONFIRM_SET_GLOBAL_CSSNAME_NAV'                => $_CORELANG['TXT_CONFIRM_SET_GLOBAL_CSSNAME_NAV'],
            'TXT_CONFIRM_SET_GLOBAL_REDIRECT_TARGET'            => $_CORELANG['TXT_CONFIRM_SET_GLOBAL_REDIRECT_TARGET'],
            'TXT_CONFIRM_SET_GLOBAL_ROBOTS'                     => $_CORELANG['TXT_CONFIRM_SET_GLOBAL_ROBOTS'],
            'TXT_CONFIRM_SET_GLOBAL_CACHINGSTATUS'              => $_CORELANG['TXT_CONFIRM_SET_GLOBAL_CACHINGSTATUS'],
            'TXT_CONFIRM_SET_GLOBAL_FRONTEND_PERMISSION'        => $_CORELANG['TXT_CONFIRM_SET_GLOBAL_FRONTEND_PERMISSION'],
            'TXT_CONFIRM_SET_GLOBAL_BACKEND_PERMISSION'         => $_CORELANG['TXT_CONFIRM_SET_GLOBAL_BACKEND_PERMISSION'],
            'TXT_NAVIGATION'                                    => $_CORELANG['TXT_NAVIGATION'],
            'TXT_TARGET'                                        => $_CORELANG['TXT_TARGET'],
            'TXT_META_ROBOTS'                                   => $_CORELANG['TXT_META_ROBOTS'],
            'TXT_RELATEDNESS'                                   => $_CORELANG['TXT_BACKEND_RELATEDNESS'],

        ));
        $objContentManager = new ContentManager();
        $existingFrontendGroups = $existingBackendGroups = '';
        foreach ($objContentManager->arrAllFrontendGroups as $id => $name) {
            $existingFrontendGroups .= '<option value="'.$id.'">'.$name."</option>\n";
        }
        // Backend Groups
        foreach ($objContentManager->arrAllBackendGroups as $id => $name) {
            $existingBackendGroups .= '<option value="'.$id.'">'.$name."</option>\n";
        }

        $objTpl->setVariable(array(
            'SETTINGS_GLOBAL_TITLE'                             => $_CONFIG['coreGlobalPageTitle'],
            'CONTENT_THEMES_MENU'                               => $objContentManager->_getThemesMenu(),
            'CONTENT_EXISTING_GROUPS'                           => $existingFrontendGroups,
            'CONTENT_EXISTING_BACKEND_GROUPS'                   => $existingBackendGroups,
        ));
    }

    /**
    * Gets admin tree array
    *
    * @global   array
    * @return   string   parsed content
    */
    public function getSiteMap()
    {
        global $_CORELANG;

        $objTpl = new HTML_Template_Sigma(ASCMS_ADMIN_TEMPLATE_PATH);
        $objTpl->setErrorHandling(PEAR_ERROR_DIE);
        $objTpl->loadTemplateFile('content_sitemap.html',true,true);

        // 3 cases for the sitemap
        // 1. normal(show parent cats)  :: $_GET[act]=collaps
        // 2. expand all                :: $_GET[act]=expandAll
        // 3. expand current cat tree   :: $_GET[act]=expand, $_GET[catId]=xxx
        if (!isset($_SESSION['content']['expandAll'])) {
            $_SESSION['content']['expandAll'] = false;
        }
        if (!isset($_SESSION['content']['expandCat'])) {
            $_SESSION['content']['expandCat'] = 0;
        }

        if ($_GET['act'] == "collaps") {
            $_SESSION['content']['expandAll'] = false;
            $_SESSION['content']['expandCat'] = 0;
        }

        if ($_GET['act'] == "expandAll") {
            $_SESSION['content']['expandAll'] = true;
        }

        if ($_GET['act'] == "expand") {
            $_SESSION['content']['expandAll'] = false;
            if ($_SESSION['content']['expandCat'] == 0) { // no category is set
                if (isset($_GET['catId'])){
                    $_SESSION['content']['expandCat']=intval($_GET['catId']);
                }
            } else { // a category is already set
                if (isset($_GET['catId'])) {
                    if ($_SESSION['content']['expandCat'] == intval($_GET['catId'])) {
                        $_SESSION['content']['expandCat'] = $this->navparentId[intval($_GET['catId'])];
                    } else {
                        $_SESSION['content']['expandCat'] = intval($_GET['catId']);
                    }
                } else {
                    $_SESSION['content']['expandCat'] = 0;
                }
            }
        }
        $expandCatId = $_SESSION['content']['expandCat'];
        $expandAll = $_SESSION['content']['expandAll'];

        $objTpl->setGlobalVariable(array(
            'TXT_CONFIRM_DELETE_DATA'    => $_CORELANG['TXT_CONFIRM_DELETE_DATA'],
            'TXT_CONFIRM_CHANGESTATUS'   => $_CORELANG['TXT_CONFIRM_CHANGESTATUS'],
            'TXT_ACTION_IS_IRREVERSIBLE' => $_CORELANG['TXT_ACTION_IS_IRREVERSIBLE'],
            'TXT_PAGE'                   => $_CORELANG['TXT_PAGE'],
            'TXT_MODULE'                 => $_CORELANG['TXT_MODULE'],
            'TXT_LAST_EDIT'              => $_CORELANG['TXT_LAST_EDIT'],
            'TXT_USER'                   => $_CORELANG['TXT_USER'],
            'TXT_FUNCTIONS'              => $_CORELANG['TXT_FUNCTIONS'],
            'TXT_SAVE_CHANGES'           => $_CORELANG['TXT_SAVE_CHANGES'],
            'TXT_COLLAPS_LINK'           => $_CORELANG['TXT_COLLAPS_LINK'],
            'TXT_EXPAND_LINK'            => $_CORELANG['TXT_EXPAND_LINK'],
            'TXT_CONFIRM_REPOSITORY'     => $_CORELANG['TXT_CONFIRM_REPOSITORY'],
            'TXT_CONFIRM_DELETE_CONTENT' => $_CORELANG['TXT_CONFIRM_DELETE_CONTENT'],
            'TXT_DELETE'                 => $_CORELANG['TXT_DELETE'],
            'TXT_DELETE_ALL'             => $_CORELANG['TXT_DELETE_HISTORY_ALL'],
            'TXT_TEMPLATE'               => $_CORELANG['TXT_TEMPLATE'],
            'TXT_EDIT'                   => $_CORELANG['TXT_EDIT'],
            'TXT_COPY_CONTENT'           => $_CORELANG['TXT_COPY_CONTENT'],
            'TXT_SELECT_ALL'             => $_CORELANG['TXT_SELECT_ALL'],
            'TXT_DESELECT_ALL'           => $_CORELANG['TXT_DESELECT_ALL'],
            'TXT_SUBMIT_SELECT'          => $_CORELANG['TXT_MULTISELECT_SELECT'],
            'TXT_SUBMIT_DELETE'          => $_CORELANG['TXT_MULTISELECT_DELETE'],
            'TXT_SUBMIT_ACTIVATE'        => $_CORELANG['TXT_MULTISELECT_ACTIVATE'],
            'TXT_SUBMIT_DEACTIVATE'      => $_CORELANG['TXT_MULTISELECT_DEACTIVATE'],
            'TXT_DATABASE_QUERY_ERROR'   => $_CORELANG['TXT_DATABASE_QUERY_ERROR'],
            'SITEMAP_EXPAND_ALL'         => $expandAll ? 'true' : 'false',
            'SITEMAP_EXPAND_CAT_ID'      => $expandCatId,
            'DIRECTORY_INDEX'            => CONTREXX_DIRECTORY_INDEX,
            'CSRF_KEY'                   => CSRF::key(),
            'CSRF_CODE'                  => CSRF::code(),
        ));

        foreach (FWLanguage::getLanguageArray() as $arrLang){
            if($arrLang['frontend'] == 0) continue;
            $tabClass = '';
            if($this->langId == $arrLang['id'])
            $tabClass = 'active';
            $objTpl->setVariable(array(
                'LANGUAGE_ID'    => $arrLang['id'],
                'LANGUAGE_NAME'  => $arrLang['name'],
                'LANGUAGE_TITLE' => $arrLang['name'].'_'.$arrLang['id'],
                'TAB_CLASS'      => $tabClass,
            ));
            $objTpl->parse('languages_tab');
        }

        $objTpl->setVariable(array(
            'CONTENT_ID'     => $this->langId,
            'CONTENT_NAME'   => FWLanguage::getLanguageParameter($this->langId, 'name'),
        ));
        $objTpl->parse('header');

        $this->_setupSiteConfig($objTpl);

        $objTpl->setVariable('SITEMAP_NESTED_PAGES', $this->parseNestedSitemap($this->nestedNavigation, 'sortableNavigation'));

        // New in 2.0: editmode selector window
        $objTpl->setVariable(array(
            'TXT_EDITMODE_TITLE'   => $_CORELANG['TXT_FRONTEND_EDITING_SELECTION_TITLE'],
            'TXT_EDITMODE_TEXT'    => $_CORELANG['TXT_FRONTEND_EDITING_SELECTION_TEXT'],
            'TXT_EDITMODE_CODE'    => $_CORELANG['TXT_FRONTEND_EDITING_SELECTION_MODE_PAGE'],
            'TXT_EDITMODE_CONTENT' => $_CORELANG['TXT_FRONTEND_EDITING_SELECTION_MODE_CONTENT'],
        ));
        return $objTpl->get();
    }

    /**
    * Get trail
    *
    * @param     integer  $currentid
    * @return    integer  $allparents
    */
    function getCurrentTreeArray($currentid=1)
    {
        $arrCurrentTree = array();
        while ($currentid!=0) {
            $x=$this->navparent[$currentid];

            if (!is_array($x)) {
                $arrCurrentTree[]=0;
                $currentid=0;
            } else {
                $result=each($x);
                $arrCurrentTree[] = $result[0];
                $currentid = $result[0];
            }
        }
        return $arrCurrentTree;
    }

    /**
    * Get catid sons
    *
    * @param     integer  $currentid
    * @return    integer  $allparents
    */
    function getCurrentSonArray($currentid)
    {
        $list = !empty($this->navtable[$currentid]) ? $this->navtable[$currentid] : '';
        if (is_array($list)) {
            foreach (array_keys($list) as $pageId) {
                array_push($this->navSons, $pageId);
                $this->getCurrentSonArray($pageId);
            }
        }
        return $this->navSons;
    }

    /**
    * Do admin tree array
    *
    * @param    integer  $parcat
    * @param    integer  $level
    * @param    integer  $maxlevel
    * @return   array    $this->treeArray
    */
    function doAdminTreeArray($parcat=0, $level=0, $maxlevel=0)
    {
        $list = $this->navtable[$parcat];
        if (is_array($list)) {
            foreach (array_keys($list) as $pageId) {
                $this->treeArray[$pageId] = $level;
                if (isset($this->navtable[$pageId]) && ($maxlevel > $level+1 || $maxlevel == '0')) {
                    $this->doAdminTreeArray($pageId, $level+1, $maxlevel);
                }
            }
        }
        return $this->treeArray;
    }

    /**
     * find a path to a key of any-dimensional arrays
     * this is a helper function only
     *
     * @param mixed $needle the key to search for (any valid array key)
     * @param array $haystack the array to search in
     * @param array $path recursion helper argument
     * @return array path to the key if found, otherwise false
     */
    function getSubArrayPathByKey($needle, $haystack, $path = array())
    {
        if(!is_array($haystack))
            return false;
        foreach($haystack as $key => $val) {
            if(is_array($val) && $subPath = getSubArrayPathByKey($needle, $val, $path)) {
                $path = array_merge($path, array($key), $subPath);
                return $path;
            }elseif($key == $needle){
                $path[] = $key;
                return $path;
            }
        }
        return false;
    }

    /**
     * find a subarray by key
     *
     * @param mixed $needle the key to search for
     * @param array $haystack the arary to search in
     * @return array the sub array if the key $needle was foud in the array $haystack, otherwise false
     */
    function getSubArrayByKey($needle, $haystack)
    {
        $arrPath = getSubArrayPathByKey($needle, $haystack);
        if($arrPath){
            foreach ($arrPath as $key) {
            	$haystack = $haystack[$key];
            }
            return $haystack;
        }else{
            return false;
        }
    }


    /**
     * returns the string for the <a> href for the current page
     *
     * @param integer $pageId
     * @return string href
     */
    function _getPageClickHref($moduleReference, $pageId)
    {
        $strHref = '';
        if(empty($moduleReference)){
            $strHref = CONTREXX_DIRECTORY_INDEX.'?cmd=content&amp;act=edit&amp;pageId='.$pageId;
        }else{
            $strHref = "javascript:showEditModeWindow('".$moduleReference."', '".$pageId."')";
        }
        return $strHref;
    }
}

?>
