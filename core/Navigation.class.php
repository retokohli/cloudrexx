<?php

/**
 * Navigation
 * Note: modified 27/06/2006 by Sébastien Perret => sva.perret@bluewin.ch
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @version        1.0.0
 * @package     contrexx
 * @subpackage  core
 * @todo        Edit PHP DocBlocks!
 */

/**
 * @ignore
 */
require_once ASCMS_FRAMEWORK_PATH.'/System.class.php';
require_once ASCMS_CORE_PATH.'/NavigationPageTree.class.php';
require_once ASCMS_CORE_PATH.'/DropdownNavigationPageTree.class.php';
require_once ASCMS_CORE_PATH.'/NestedNavigationPageTree.class.php';

/**
 * Class Navigation
 *
 * This class creates the navigation tree
 * Note: modified 27/06/2006 by Sébastien Perret => sva.perret@bluewin.ch
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @access        public
 * @version        1.0.0
 * @package     contrexx
 * @subpackage  core
 */
class Navigation
{
    public $langId;
    public $data = array();
    public $table = array();
    public $tree = array();
    public $parents = array();
    public $pageId;
    public $styleNameActive = 'active';
    public $styleNameNormal = 'inactive';
    public $separator = ' > ';
    public $spacer = '&nbsp;';
    public $levelInfo = 'down';
    public $subNavSign = '&nbsp;&raquo;';
    public $subNavTag = '<ul id="menubuilder%s" class="menu">{SUB_MENU}</ul>';
    public $_cssPrefix = 'menu_level_';
    public $_objTpl;
    public $topLevelPageId;
    public $_menuIndex = 0;

    protected $page = null;


    /**
    * Constructor
    * @global   integer
    * @param     integer  $pageId
    * @param Cx\Model\ContentManager\Page $page
    */
    function __construct($pageId, $page)
    {
        global $_LANGID;

        $this->langId = $_LANGID;
        $this->pageId = $pageId;
        $this->page = $page;
        $this->_initialize();
        $this->_getParents();
        // $parcat is the starting parent id
        // optional $maxLevel is the maximum level, set to 0 to show all levels
        $this->_buildTree();
    }


    /**
    * Initialize the data hash from the database
    * @global ADONewConnection
    * @global InitCMS
    * @global Array
    * @access private
    */
    function _initialize()
    {
        global $objDatabase, $objInit, $_CONFIG;

        $objFWUser = FWUser::getFWUserObject();

        //check for preview and if theme exists in database
        $currentThemesId='';
        if (!empty($_GET['preview'])) {
            $objRS = $objDatabase->SelectLimit("
                SELECT id
                  FROM ".DBPREFIX."skins
                 WHERE id = ".intval($_GET['preview']), 1);
            if ($objRS->RecordCount() == 1) {
                $currentThemesId = intval($_GET['preview']);
            }
        }

        $query = "SELECT n.cmd,
                         n.catid,
                         n.catname,
                         n.target,
                         n.parcat,
                         n.css_name,
                         n.displaystatus,
                         c.redirect,
                         m.name AS section,
                         a_s.url AS alias_url
                    FROM ".DBPREFIX."content_navigation     AS n
              INNER JOIN ".DBPREFIX."content                AS c    ON c.id = n.catid
              INNER JOIN ".DBPREFIX."modules                AS m    ON m.id = n.module
         LEFT OUTER JOIN ".DBPREFIX."module_alias_target    AS a_t  ON a_t.url = n.catid
         LEFT OUTER JOIN ".DBPREFIX."module_alias_source    AS a_s  ON a_s.target_id = a_t.id AND a_s.isdefault = 1
                   WHERE (n.displaystatus = 'on' OR n.catid='".$this->pageId."')
                     AND n.activestatus='1'
                     AND n.is_validated='1'
                     AND n.lang='".$this->langId."'
                         ".(
                            $objFWUser->objUser->login() ?
                                // user is authenticated
                                (
                                    !$objFWUser->objUser->getAdminStatus() ?
                                         // user is not administrator
                                        'AND (n.protected=0'.(count($objFWUser->objUser->getDynamicPermissionIds()) ? ' OR n.frontend_access_id IN ('.implode(', ', $objFWUser->objUser->getDynamicPermissionIds()).')' : '').')' :
                                        // user is administrator
                                        ''
                                )
                                : (   empty($_CONFIG['coreListProtectedPages'])
                                   || $_CONFIG['coreListProtectedPages'] == 'off'
                                    ? 'AND n.protected=0' : ''
                                  )
                            )."
                     AND (n.startdate<=CURDATE() OR n.startdate='0000-00-00')
                     AND (n.enddate>=CURDATE() OR n.enddate='0000-00-00')
                ORDER BY n.parcat DESC, n.displayorder";
        $objResult = $objDatabase->Execute($query);

        $langPrefix = '/'.FWLanguage::getLanguageParameter($this->langId, 'lang');

        if ($objDatabase->Affected_Rows() > 0) {
            while (!$objResult->EOF) {
                $catid = $objResult->fields['catid'];
                // generate array $this->table
                if (!isset($this->table[$objResult->fields['parcat']])) {
                    $this->table[$objResult->fields['parcat']] = array();
                }
                $this->table[$objResult->fields['parcat']][$catid] = stripslashes($objResult->fields['catname']);
                // generate array $this->parentId
                $this->parentId[$catid] = $objResult->fields['parcat'];
                // generate array $this->arrPages
                if (!isset($this->arrPages[$objResult->fields['parcat']])) {
                    $this->arrPages[$objResult->fields['parcat']] = array();
                }
                array_push($this->arrPages[$objResult->fields['parcat']], $catid);
                if ($catid == $this->pageId && !isset($this->topLevelPageId)) {
                    $this->topLevelPageId = ($objResult->fields['parcat'] == 0 ? $catid : $objResult->fields['parcat']);
                }
                // generate array $this->data
                $s = $objResult->fields['section'];
                $c = $objResult->fields['cmd'];
                $section = ($s == '') ? '' : "&amp;section=$s";
                $cmd = ($c == '') ? '' : "&amp;cmd=$c";
                $useAlias = false;
                if (   $_CONFIG['aliasStatus']
                    && (   $objResult->fields['alias_url']
                        || $_CONFIG['useVirtualLanguagePath'] == 'on' && $s == 'home')
                ) {
                    $useAlias = true;
                }
                // Create alias link if alias is present for this page...
                if ($useAlias) {
                    if ($_CONFIG['useVirtualLanguagePath'] == 'on') {
                        // the homepage should not use an alias, but only a slash
                        if ($s == 'home') {
                            if ($this->langId == $objInit->getDefaultFrontendLangId()) {
                                // the default language should not use the virtual language path
                                $menu_url = '/';
                            } else {
                                $menu_url = $langPrefix.'/';
                            }
                        } else {
                            $menu_url = $langPrefix.'/'.$objResult->fields['alias_url'];
                        }
                    } else {
                        $menu_url = '/'.$objResult->fields['alias_url'];
                    }
                    $menu_url = FWSystem::mkurl($menu_url);
                } elseif (!empty($objResult->fields['redirect'])) {
                    if (self::is_local_url($objResult->fields['redirect'])) {
                        if ($_CONFIG['useVirtualLanguagePath'] == 'on') {
                            $menu_url = ASCMS_PATH_OFFSET.$langPrefix.'/'.htmlspecialchars($objResult->fields['redirect']);
                        } else {
                            $menu_url = ASCMS_PATH_OFFSET.'/'.htmlspecialchars($objResult->fields['redirect']);
                        }
                    }
                    else {
                        $menu_url = htmlspecialchars($objResult->fields['redirect']);
                    }
                } else {
                    $link = (!empty($s)) ? "?section=".$s.$cmd : "?page=".$objResult->fields['catid'].$section.$cmd;
                    $menu_url = CONTREXX_SCRIPT_PATH
                        .$link
                        .(($currentThemesId && !strpos($this->data[$id]['url'],'preview')) ? '&amp;preview='.$currentThemesId : '');
                }
                $this->data[$objResult->fields['catid']]= array(
                    'catid'    => $objResult->fields['catid'],
                    'url'      => $menu_url,
                    'catname'  => stripslashes($objResult->fields['catname']),
                    'target'   => $objResult->fields['target'],
                    'css_name' => $objResult->fields['css_name'],
                    'status' => $objResult->fields['displaystatus']
                );
                $objResult->MoveNext();
            }
            ksort($this->table);
            // generate page tree
            if (count($this->arrPages)>1) {
                while (count($this->arrPages) > 1) {
                    $countBefore = count($this->arrPages);
                    foreach ($this->arrPages as $pageId => $arrSubPages) {
                        if ($pageId != 0) {
                            $mount = true;
                            foreach ($arrSubPages as $subPageId) {
                                if (!is_array($subPageId) && isset($this->arrPages[$subPageId])) {
                                    $mount = false;
                                    break;
                                }
                            }
                            if ($mount) {
                                $position = false;
                                foreach ($this->arrPages as $mainPageId => $arrSubPages2) {
                                    foreach ($arrSubPages2 as $key => $subPageId2) {
                                        if ($pageId == $subPageId2) {
                                            $keyId = $key;
                                            $position = $mainPageId;
                                            break 2;
                                        }
                                    }
                                }
                                if ($position !== false) {
                                    if ($position != 0 && $this->topLevelPageId == $pageId) {
                                        $this->topLevelPageId = $position;
                                    }
                                    $this->arrPages[$position][$keyId] = array($pageId => $this->arrPages[$pageId]);
                                }
                                unset($this->arrPages[$pageId]);
                            }
                        }
                    }
                    if ($countBefore == count($this->arrPages)) {
                        break;
                    }
                }
            }
        }
    }


    /**
    * builds the navigation tree sorted by parents
    * @access private
     * @param   integer   $parentId (Optional)
     * @param   integer   $maxlevel (Optional)
     * @param   integer   $level    (Optional)
    */
    function _buildTree($parentId=0,$maxlevel=0,$level=0)
    {
        if (!empty($this->table[$parentId])) {
            foreach (array_keys($this->table[$parentId]) as $id) {
                $this->tree[$id]=$level+1;
                if (   isset($this->table[$id])
                    && (   $maxlevel >= $level+1
                        || $maxlevel == 0)) {
                    $this->_buildTree($id,$maxlevel,$level+1);
                }
            }
        }
    }


    /**
     * Gets the parsed navigation
     * @access private
     * @param string  $templateContent
     * @param   boolean $boolShop         If true, parse the shop navigation
     *                                    into {SHOPNAVBAR_FILE}
    * @return mixed parsed navigation
    */
    function getNavigation($templateContent, $boolShop=false, $rootNode=null)
    {
        $this->_objTpl = new HTML_Template_Sigma('.');
        CSRF::add_placeholder($this->_objTpl);
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);
        $this->_objTpl->setTemplate($templateContent);

        if ($boolShop) {
            $this->_objTpl->setVariable('SHOPNAVBAR_FILE', Shop::getNavbar());
        }

        if (isset($this->_objTpl->_blocks['navigation_dropdown'])) {
            // set submenu tag
            if ($this->_objTpl->blockExists('sub_menu')) {
                $this->subNavTag = trim($this->_objTpl->_blocks['sub_menu']);
                $templateContent = ereg_replace('<!-- BEGIN sub_menu -->.*<!-- END sub_menu -->', NULL, $templateContent);
            }
            //$this->_buildDropDownNavigation($this->arrPages[0],1, true);
            $navi = new DropdownNavigationPageTree(Env::em(), 0, $rootNode, $this->langId, $this->page);
            $navi->setTemplate($this->_objTpl);
            $renderedNavi = $navi->render();
            return  ereg_replace('<!-- BEGIN level_. -->.*<!-- END level_. -->', $renderedNavi, $templateContent);
        } elseif (isset($this->_objTpl->_blocks['navigation'])) {
            //$this->_buildNavigation();
            $navi = new NavigationPageTree(Env::em(), 0, $rootNode, $this->langId, $this->page);
            $navi->setTemplate($this->_objTpl);
            $navi->render();
        } elseif (isset($this->_objTpl->_blocks['nested_navigation'])) {
            // Create a nested list, formatted with ul and li-Tags
            //$nestedNavigation = $this->_buildNestedNavigation();
            $navi = new NestedNavigationPageTree(Env::em(), 0, $rootNode, $this->langId, $this->page);
            $navi->setTemplate($this->_objTpl);
            $renderedNavi = $navi->render();
            return ereg_replace('<!-- BEGIN nested_navigation -->.*<!-- END nested_navigation -->', $renderedNavi, $templateContent);
        }
        return $this->_objTpl->get();
    }


    /**
     * Build navigation menu with a continuous list (default)
     * @return   string   $result
     */
    function _buildNavigation()
    {
        // not set
        $topLevelBlockName = NULL;
        $blockName = NULL;
        $htmlOutput = NULL;
        $navigationId[] = 0; // CSS styling ID's

        $array_ids = array_keys( $this->tree );
        // foreach ($this->tree as $id => $level)
        foreach ( $array_ids as $key => $id ) {
            $level = $this->tree[$id];

            if (!isset($navigationId[$level])) {
                $navigationId[$level] = 0;
            }

            $navigationId[$level]++;

            if (isset($array_ids[$key+1])) {
                $nextlevel = ( isset( $this->tree[$array_ids[$key+1]] ) ) ? $this->tree[$array_ids[$key+1]] : 0;
            } else {
                $nextlevel = 0;
            }

            $hideLevel=false;

            // checks if the page is in the current tree
            if (in_array($this->parentId[$id], $this->parents)){
                // checks for customized blocks. e.g. "level_1"
                if ($this->_objTpl->blockExists('level_'.$level)){
                    // gets the top level block name from the template
                    if (!isset($topLevelBlockName)){
                        $topLevelBlockName='level_'.$level;
                    }
                    $blockName = 'level_'.$level;
                }
                // no customized blocks for this level
                else {
                    // we already had customized blocks
                    if (isset($topLevelBlockName)){
                        // checks for the standard block e.g. "level"
                        if ($this->_objTpl->blockExists('level')){
                            $blockName = 'level';
                        } else {
                            $hideLevel=true;
                        }
                    }
                }

                if (isset($topLevelBlockName)){
                    if (!$hideLevel){
                        // checks if we are in the active tree
                        $activeTree = (in_array($id, $this->parents)) ? true : false;
                        if ($this->data[$id]['status'] == 'on') {
                            // gets the style sheets value for active or inactive
                            $style = ($activeTree) ? $this->styleNameActive : $this->styleNameNormal;
                            // get information about the next level id -> down or empty                            // $levelInfo = (($level > $nextlevel) OR $activeTree AND $id <> $this->pageId) ? $this->levelInfo : "";
                            $levelInfo = ($level > $nextlevel) ? $this->levelInfo : "";
                            $target = empty($this->data[$id]['target']) ? "_self" : $this->data[$id]['target'];
                            $this->_objTpl->setCurrentBlock($blockName);
                            $this->_objTpl->setVariable('URL',$this->data[$id]['url']);
                            $this->_objTpl->setVariable('NAME',htmlentities($this->data[$id]['catname'], ENT_QUOTES, CONTREXX_CHARSET));
                            $this->_objTpl->setVariable('TARGET',$target);
                            $this->_objTpl->setVariable('LEVEL_INFO',$levelInfo);
                            $this->_objTpl->setVariable('NAVIGATION_ID',$navigationId[$level]);
                            $this->_objTpl->setVariable('STYLE',$style);
                            $this->_objTpl->setVariable('CSS_NAME',$this->data[$id]['css_name']);
                            $this->_objTpl->parse($blockName);
                            $htmlOutput.=$this->_objTpl->get($blockName, true);
                        }
                    }
                }
            }
        }
        unset($navigationId);

        if (isset($topLevelBlockName)){
            // replaces the top level block with the complete parsed navigation
            // this is because the Sigma Template system don't support nested blocks
            // with difference object based orders
            $this->_objTpl->replaceBlock($topLevelBlockName, $htmlOutput, true);
            $this->_objTpl->touchBlock($topLevelBlockName);
            if ($this->_objTpl->blockExists('navigation')){
                $this->_objTpl->parse('navigation');
            }
        }
    }


    /**
     * Build nested navigation menu with unordered list
     * if [[nested_navigation]] is placed in navbar.
     * Formatting should be done with CSS.
     * Tags (ul and li) are inserted by the code.
     *
     * Navigation can be restricted to specific levels with the tag [[levels_AB]],
     * where A and B can take following values:
     *    starting level A: [1-9]
     *    ending level B: [1-9], [+] or [];
     *              [+]: any level starting from A;
     *              [] : just level A;
     *    examples: [[levels_24]] means navigation levels 2 to 4;
     *              [[levels_3+]] means any navigation levels starting from 3;
     *              [[levels_1]] means navigation level 1 only;
     * @return   string   $result
     */
    function _buildNestedNavigation()
    {
        $navigationBlock = "";

        // Checks which levels to use
        $match = array();
        if (!preg_match('/levels_([1-9])([1-9\+]*)/', trim($this->_objTpl->_blocks['nested_navigation']), $match)) {
            $match[1] = 1;
            $match[2] = '+';
        }

        $array_ids = array_keys( $this->tree );

        // Make an array with visible items only
        foreach ($array_ids as $key => $id ) {
            $level = $this->tree[$id];
            // Checks if the menu item is in the current tree
             if (in_array($this->parentId[$id], $this->parents)) {
                // Checks if the menu item level is visible
                if (($level>=$match[1] && $level<=$match[2]) || ($level>=$match[1] && $match[2] == "+")) {
                    if ($this->data[$id]['status'] == 'on') {
                        $array_visible_ids[] = $array_ids[$key];
                    }
                }
            }
        }

        // Build nested menu list
        $navigationId = array();
        foreach ($array_visible_ids as $key => $id) {
            $level = $this->tree[$id];
            $nextLevel = $this->tree[$array_visible_ids[$key+1]];
            if (!isset($navigationId[$level])) {
                $navigationId[$level] = 0;
            }
            $navigationId[$level]++;
            if ($nextLevel == NULL) $nextLevel = $match[1];
            $closingTags = '';
            for ($i=1; $i<=($level - $nextLevel); $i++) {
                $closingTags .= "\n</ul>\n</li>";
            }

            // Current block
            $this->_rowBlock = trim($this->_objTpl->_blocks['level']);

            // Build menu structure
            if ($level < $nextLevel) {
                $cssStyle = $this->_cssPrefix.($nextLevel);
// TODO: Must not use the "id" attribute here!
                $nestedRow = "<li>".$this->_rowBlock."\n<ul id='$cssStyle'>";
            } elseif ($level > $match[1] && $level > $nextLevel) {
                $nestedRow = "<li>".$this->_rowBlock."</li>".$closingTags;
            } else {
                $nestedRow = "<li>".$this->_rowBlock."</li>";
            }

            // Checks if this is an active menu item
            $style = (in_array($id, $this->parents)) ? $this->styleNameActive : $this->styleNameNormal;
            $target = empty($this->data[$id]['target']) ? "_self" : $this->data[$id]['target'];
            $tmpNavigationBlock = str_replace('{NAME}', $this->data[$id]['catname'], $nestedRow);
            $tmpNavigationBlock = str_replace('<li>', '<li class="'.$style.'">', $tmpNavigationBlock);
            $tmpNavigationBlock = str_replace('{URL}', $this->data[$id]['url'], $tmpNavigationBlock);
            $tmpNavigationBlock = str_replace('{TARGET}', $target, $tmpNavigationBlock);
            $tmpNavigationBlock = str_replace('{CSS_NAME}',  $this->data[$id]['css_name'], $tmpNavigationBlock);
            $tmpNavigationBlock = str_replace('{NAVIGATION_ID}', $navigationId[$level], $tmpNavigationBlock);

            $navigationBlock .= $tmpNavigationBlock."\n";
        }
        if ($navigationBlock != "") {
            // Return nested menu
            return "<ul id='".$this->_cssPrefix.$match[1]."'>\n".$navigationBlock."</ul>";
        }
        return '';
    }


    /**
    * Build a drop down navigation menu
    * @access private
    * @param mixed $arrPage
    * @param integer $level
    * @param boolean $mainPage
    * @return string $navigation
    */
    function _buildDropDownNavigation($arrPage, $level, $mainPage = false)
    {
        $navigation = "";
        $tmpNavigation = "";
        $mainCat = 1;
        $navigationId = array();
        foreach ($arrPage as $page) {
            // prevent undefined variable notice
            if (!isset($navigationId[$level])) {
                $navigationId[$level] = 0;
            }
            ++$navigationId[$level];
            // page has children
            if (is_array($page)) {
                // get page id
                $keys = array_keys($page);
                $id = $keys[0];
                // get sub navigation
                $subNavigation = $this->_buildDropDownNavigation($page[$id], $level + 1);
                if (!empty($subNavigation)) {
                    $subNavigation = str_replace("{SUB_MENU}", $subNavigation, sprintf($this->subNavTag, $this->_menuIndex++)); //sprintf for js dropdown unique ID
                    if (($this->_objTpl->blockExists('level_all') || $this->_objTpl->blockExists('level_'.($level+1))) && !$mainPage ) {
                        if (!empty($subNavigation) && ($id != $this->parentId[$this->pageId] )) { //)$this->data[$id]['status'] == 'on') {
                            $this->data[$id]['catname'] .= $this->subNavSign;
                        }
                    }
                }
            } else {
                $subNavigation = "";
                $id = $page;
            }

            if ($this->_objTpl->blockExists('level_'.$level)) {
                $tmpNavigation = trim($this->_objTpl->_blocks['level_'.$level]);
            }

            if (!empty($tmpNavigation)) {
                if ($this->data[$id]['status'] == 'on') {
                    $target = empty($this->data[$id]['target']) ? "_self" : $this->data[$id]['target'];

                    if ($level == 1 && $this->topLevelPageId == $id) {
                        $tmpNavigation = str_replace('{STYLE}', "starter_active", $tmpNavigation);
                        $mainCat++; //inc if new maincat
                    } elseif ($level == 1) {
                        $tmpNavigation = str_replace('{STYLE}', "starter_normal", $tmpNavigation);
                        $mainCat++; //inc if new maincat
                    } else {
                        $tmpNavigation = str_replace('{STYLE}', $id == $this->pageId || in_array($id, $this->parents) ? $this->styleNameActive : $this->styleNameNormal, $tmpNavigation);
                    }

                    $tmpNavigation = str_replace('{URL}', $this->data[$id]['url'], $tmpNavigation);
                    $tmpNavigation = str_replace('{NAME}', $this->data[$id]['catname'], $tmpNavigation);
                    $tmpNavigation = str_replace('{TARGET}', $target, $tmpNavigation);
                    $tmpNavigation = str_replace('{NAVIGATION_ID}', $navigationId[$level], $tmpNavigation);
                    $tmpNavigation = str_replace('{SUB_MENU}', $subNavigation, $tmpNavigation);
                    $tmpNavigation = str_replace('{CSS_NAME}', $this->data[$id]['css_name'], $tmpNavigation);

                    $navigation .= $tmpNavigation;
                }
            }
        }
        return $navigation;
    }


    /**
     * Builds the array containing all parent IDs of the current page
     *
     * The array starts with the ID of the parent of the current page, and
     * continues up the hierarchy up to and including the root (ID 0).
     * The array contains the page ID as keys, and their respective parents
     * as values.  {@see get_parent_id()} relies on that structure.
     * @access  private
     */
    function _getParents()
    {
        $parentId = !empty($this->parentId[$this->pageId]) ? $this->parentId[$this->pageId] : 0;
        while ($parentId!=0) {
            if (is_array($this->table[$parentId])) {
                array_push($this->parents, $parentId);
                if (!empty($this->parentId[$parentId])) {
                    $parentId = $this->parentId[$parentId];
                } else {
                    $parentId = 0;
                }
            }
        }
        // adds the current pageId and the root id 0 to the parents array
        array_push($this->parents, $this->pageId, 0);
    }


    /**
     * Get trail
     * @return    string     The trail with links
     */
    function getTrail()
    {
        $return = '';
        $parentId = (isset($this->parentId[$this->pageId])
            ? $this->parentId[$this->pageId] : 0);
        while ($parentId!=0) {
            if (!empty($this->data[$parentId])) {
                if (!is_array($this->table[$parentId])) {
                    return $return;
                }
                $n = $this->data[$parentId]['catname'];
                if ($n == "") $this->separator = "";
                $u = $this->data[$parentId]['url'];
                $trail = "<a href=\"".$u."\" title=\"".htmlentities($n, ENT_QUOTES, CONTREXX_CHARSET)."\">".htmlentities($n, ENT_QUOTES, CONTREXX_CHARSET)."</a>".$this->separator;
                $return=$trail.$return;
                $parentId = $this->parentId[$parentId];
            }  else {
                $parentId = 0;
            }
        }
        return $return;
    }


    /**
     * getFrontendLangNavigation()
     * @access public
     * @global InitCMS
     * @global array
     */
    function getFrontendLangNavigation()
    {
        global $objInit, $_CONFIG;

        $this->arrLang = $objInit->getLanguageArray();
        $langNavigation = '';
        if (count($this->arrLang)>1) {
            foreach ($this->arrLang as $id => $value) {
                if ($this->arrLang[$id]['frontend'] == 1) {
                    if ($_CONFIG['useVirtualLanguagePath'] == 'on') {
                        $uri = ASCMS_PATH_OFFSET.'/'.$this->arrLang[$id]['lang'].'/';
                    } else {
                        $uri = CONTREXX_SCRIPT_PATH."?setLang=".$id;
                    }
                    $langNavigation .= " [ <a href='".$uri."' title='".$value['name']."' >".$value['name']."</a> ] ";
                }
            }
        }
        return $langNavigation;
    }


    static function is_local_url($url)
    {
        $url = strtolower($url);
        if (strpos($url, 'http://' ) === 0) return false;
        if (strpos($url, 'https://') === 0) return false;
        if (strpos($url, '/'       ) === 0) return false;
        return true;
    }


    function _debug($obj)
    {
        echo "<pre>";
        print_r($obj);
        echo "</pre>";
    }

}

?>
