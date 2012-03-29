<?php

/**
 * Content Sitemap
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  core
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Content Sitemap
 *
 * navigation tree
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @access      public
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  core
 * @todo        Edit PHP DocBlocks!
 */
class ContentSitemap
{
    var $navtable = array();
    var $navlinks = array();
    var $navparent = array();
    var $navparentId = array();
    var $navdisplayorder = array();
    var $currentid;
    var $treeArray = array();
    var $navUsername = array();
    var $navChangelog = array();
    var $navName = array();
    var $navModule = array();
    var $navCmd = array();
    var $navDisplaystatus = array();
    var $navActiveStatus = array();
    var $navProtection = array();
    var $navSons = array();
    var $navIsValidated = array();
    var $navIsRedirect = array();
    var $langId;
    var $requiredModuleNames = array('home','ids','error','login','core');

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
            $this->navIsValidated[$objResult->fields['catid']] = $objResult->fields['isValidated'];
            $this->currentid = $currentid;

            $objSubResult = $objDatabase->Execute("
                SELECT redirect
                  FROM ".DBPREFIX."content
                 WHERE id=".$objResult->fields['catid']
            );
            $this->navIsRedirect[$objResult->fields['catid']] = (empty($objSubResult->fields['redirect'])) ? false : true;
            $objResult->MoveNext();
        }
        unset($arrModules);
        return true;
    }

    /**
     * Gets admin tree array
     *
     * @global   array
     * @return   string   parsed content
     */
    function getSiteMap()
    {
        global $_CORELANG;

        $objTpl = new HTML_Template_Sigma(ASCMS_ADMIN_TEMPLATE_PATH);
        CSRF::add_placeholder($objTpl);
        $objTpl->setErrorHandling(PEAR_ERROR_DIE);
        $objTpl->loadTemplateFile('content_sitemap.html',true,true);

        // 3 cases for the sitemap
        // 1. normal(show parent cats)  :: $_GET[act]=collaps
        // 2. expand all                :: $_GET[act]=expandAll
        // 3. expand current cat tree   :: $_GET[act]=expand, $_GET[catId]=xxx
        if (!isset($_SESSION['content']['expandAll'])) {
            $_SESSION['content']['expandAll']=false;
        }
        if (!isset($_SESSION['content']['expandCat'])) {
            $_SESSION['content']['expandCat']=0;
        }

        if ($_GET['act']=="collaps") {
            $_SESSION['content']['expandAll']=false;
            $_SESSION['content']['expandCat']=0;
        }

        if ($_GET['act']=="expandAll") {
            $_SESSION['content']['expandAll']=true;
        }

        if ($_GET['act']=="expand") {
            $_SESSION['content']['expandAll']=false;

            if ($_SESSION['content']['expandCat']==0) { // no category is set
                if (isset($_GET['catId'])){
                    $_SESSION['content']['expandCat']=intval($_GET['catId']);
                }
            } else { // a category is already set
                if (isset($_GET['catId'])) {
                    if ($_SESSION['content']['expandCat']==intval($_GET['catId'])) {
                        $_SESSION['content']['expandCat']=$this->navparentId[intval($_GET['catId'])];
                    } else {
                        $_SESSION['content']['expandCat']=intval($_GET['catId']);
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
        ));

        $objTpl->setCurrentBlock('siteRow');
        $objTpl->setVariable(array(
            'CONTENT_ID'     => $this->langId,
            'CONTENT_NAME'   => FWLanguage::getLanguageParameter($this->langId, 'name'),
        ));
        $objTpl->parseCurrentBlock();

        $objTpl->setCurrentBlock('pageRow');
        $treeArray = $this->doAdminTreeArray();
        $treeArrayCopy = $treeArray;
        $arrLevel = array();

        $i=0;
        foreach ($treeArrayCopy as $key => $value) {
           $arrLevel[$key]=$value;
           $arrKey[$i]=$key;
           $i++;
        }

        $i=0;
        $n=0;
        $arrayTreeParents = $this->getCurrentTreeArray($expandCatId);
        $thisTree = false;
        $topLevelId = 0;

        foreach ($treeArray as $key => $value) {
            $expand = false;
            $level = intval($value);
            if (isset($arrKey[$n+1])) {
                $nextLevel = $arrLevel[$arrKey[$n+1]];
            } else {
                $nextLevel = 0;
            }
            //echo "level: $level  Key : $key NextLevel : $nextLevel<br>";

            if ($this->navIsValidated[$key] == 0) {
                $class = 'rowWarn';
            } else {
                $class = (($i % 2) == 0) ? "row1" : "row2";
            }

            if ($expandAll OR $level==0) {
                $expand=true;
                $topLevelId = $expandCatId;
            } else {
                if ((in_array ($this->navparentId[$key], $arrayTreeParents)) || $thisTree || $topLevelId==$this->navparentId[$key]) {
                    $thisTree = ($key==$expandCatId) ? true : false;
                    $expand = true;
                }
            }

            if ($expand) {
                $width=($level)*18;
                $requiredModule = in_array($this->navModule[$key], $this->requiredModuleNames) ? "_core" : "";
                $isRedirect = '';
                if (   empty($requiredModule)
                    && $this->navIsRedirect[$key]) {
                    $isRedirect = '_redirect';
                }

                // start active or inactive folder icon
                $folderIcon = "<a href='javascript:changeStatus($key);'><img src='images/icons/folder_off".$requiredModule.$isRedirect.".gif' width=15 height='13' border='0' title='".$_CORELANG['TXT_STATUS_INVISIBLY']."' alt='".$_CORELANG['TXT_STATUS_INVISIBLY']."' /></a>&nbsp;";
                if ($this->navProtected[$key]) {
                    $folderIcon = "<a href='javascript:changeStatus($key);'><img src='images/icons/folder_off_locked".$requiredModule.".gif' width='15' border='0' height='13' title='".$_CORELANG['TXT_STATUS_INVISIBLY']."' alt='".$_CORELANG['TXT_STATUS_INVISIBLY']."' /></a>&nbsp;";
                }

                if ($this->navDisplaystatus[$key]=="on") {
                    $folderIcon = "<a href='javascript:changeStatus($key);'><img src='images/icons/folder_on".$requiredModule.$isRedirect.".gif' width='15' height='13' border='0' title='".$_CORELANG['TXT_STATUS_VISIBLE']."' alt='".$_CORELANG['TXT_STATUS_VISIBLE']."' /></a>&nbsp;";
                    if ($this->navProtected[$key]){
                        $folderIcon = "<a href='javascript:changeStatus($key);'><img src='images/icons/folder_on_locked".$requiredModule.".gif' width='15' height='13' border='0' title='".$_CORELANG['TXT_STATUS_VISIBLE']."' alt='".$_CORELANG['TXT_STATUS_VISIBLE']."' /></a>&nbsp;";
                    }
                } // end active or inactive folder icon

                if ($this->navActiveStatus[$key]=='1') {
                    $activeIcon = '<a href="?cmd=content&amp;act=changeActiveStatus&amp;id='.$key.'"><img src="images/icons/led_green.gif" border="0" title="'.$_CORELANG['TXT_PAGE_ACTIVATE'].'" alt="'.$_CORELANG['TXT_PAGE_ACTIVATE'].'" /></a>';
                } else {
                    $activeIcon = '<a href="?cmd=content&amp;act=changeActiveStatus&amp;id='.$key.'"><img src="images/icons/led_red.gif" border="0" title="'.$_CORELANG['TXT_PAGE_ACTIVATE'].'" alt="'.$_CORELANG['TXT_PAGE_ACTIVATE'].'" /></a>';
                }

                $folderLinkIcon = "<img src='images/icons/pixel.gif' width='11' height='11' alt='' title='' />&nbsp;";
                if ($nextLevel>$level) {
                    if ($expandAll AND $expandCatId==0) {
                        $folderLinkIcon = "<a href='?cmd=content&amp;act=expand&amp;catId=$key'><img src='images/icons/minuslink.gif' border='0' width='11' height='11' alt='' title='' /></a>&nbsp;";
                    } elseif ($key==$expandCatId) {
                        $folderLinkIcon = "<a href='?cmd=content&amp;act=expand&amp;catId=$key'><img src='images/icons/minuslink.gif' border='0' width='11' height='11' alt='' title='' /></a>&nbsp;";
                    } else {
                        $folderLinkIcon = "<a href='?cmd=content&amp;act=expand&amp;catId=$key'><img src='images/icons/pluslink.gif' border='0' width='11' height='11' alt='' title='' /></a>&nbsp;";
                    }
                }
                if (!$this->navCmd[$key] && $this->navModule[$key]) {
                    $repository= "<a href=\"javascript:repositoryPage('$key')\"><img src='images/icons/upload.gif' border='0' alt='".$_CORELANG['TXT_ADD_REPOSITORY']."' title='".$_CORELANG['TXT_ADD_REPOSITORY']."' /></a>";
                } else {
                    $repository= "<img src='images/icons/pixel.gif' width='16' height='16' border='0' alt='' title='' />";
                }

                $moduleReference = '';
                if (empty($this->navModule[$key])) {
                    $this->navModule[$key]="&nbsp;";
                } else {
                    $moduleName = $this->navModule[$key];
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

                $objTpl->setVariable(array(
                    'SITEMAP_DISPLAYORDER_DISABLED'    => (Permission::checkAccess(35, 'static', $return = true) ? '' : 'disabled="disabled"'),
                    'SITEMAP_PAGE_MODULE'              => $this->navModule[$key],
                    'SITEMAP_PAGE_CMD'                 => $this->navCmd[$key],
                    'SITEMAP_PAGE_DISPLAYORDER'        => $this->navdisplayorder[$key],
                    'SITEMAP_PAGE_USERNAME'            => $this->navUsername[$key],
                    'SITEMAP_PAGE_CHANGELOG'           => $this->navChangelog[$key],
                    'SITEMAP_ROWCLASS'                 => $class,
                    'SITEMAP_ROW_PADDING'              => $width,
                    'SITEMAP_PAGE_LEVEL'               => $folderLinkIcon.$activeIcon.'&nbsp;'.$folderIcon,
                    'SITEMAP_PAGE_ID'                  => $key,
                    'SITEMAP_PAGE_NAME'                => htmlentities($this->navName[$key], ENT_QUOTES, CONTREXX_CHARSET),
                    'SITEMAP_REPOSITORY'               => $repository,
                    // New behavior: Go to module administration or content
                    'SITEMAP_PAGE_LINK'                => (!empty($moduleReference) ? "javascript:showEditModeWindow('".$moduleReference."','".$key."');" : "index.php?cmd=content&amp;act=edit&amp;pageId=$key"),
                ));
                $objTpl->parseCurrentBlock();
                $i++;
            }
            $n++;
        }

        // Added in 2.0: edit mode selector window
        $objTpl->setVariable(array(
            'TXT_EDITMODE_TITLE'   => $_CORELANG['TXT_FRONTEND_EDITING_SELECTION_TITLE'],
            'TXT_EDITMODE_TEXT'    => $_CORELANG['TXT_FRONTEND_EDITING_SELECTION_TEXT'],
            'TXT_EDITMODE_CODE'    => $_CORELANG['TXT_FRONTEND_EDITING_SELECTION_MODE_PAGE'],
            'TXT_EDITMODE_CONTENT' => $_CORELANG['TXT_FRONTEND_EDITING_SELECTION_MODE_CONTENT']
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
        $list = $this->navtable[$currentid];
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
}

?>
