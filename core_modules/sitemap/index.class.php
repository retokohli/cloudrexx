<?php
/**
 * Sitemapping
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author Comvation Development Team <info@comvation.com>
 * @version 1.0.1
 * @package     contrexx
 * @subpackage  core_module_sitemap
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Sitemap
 *
 * Class for the sitemap
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author Comvation Development Team <info@comvation.com>
 * @access public
 * @version 1.0.1
 * @package     contrexx
 * @subpackage  core_module_sitemap
 */
class sitemap
{
    var $pageContent;
    var $_objTpl;
    var $_sitemapPageName = array();
    var $_sitemapPageURL = array();
    var $_sitemapPageLevel = array();
    var $_sitemapPageTarget = array();
    var $_arrName = array();
    var $_arrUrl = array();
    var $_arrTarget = array();
    var $_doSitemap = true;
    var $_sitemapBlock;
    var $_cssPrefix = "sitemap_level_";
    var $_subTagStart = "<ul>";
    var $_subTagEnd = "</ul>";


    /**
    * Constructor
    *
    * @param  string
    * @access public
    */
    function __construct($pageContent)
    {
        $this->pageContent = $pageContent;
        $this->_objTpl = new HTML_Template_Sigma('.');
        CSRF::add_placeholder($this->_objTpl);
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);

        $this->_objTpl->setTemplate($this->pageContent);

        if (isset($this->_objTpl->_blocks['sitemap'])) {
            $this->_initialize();
            $this->_doSitemapArray();
        } else {
            $this->_doSitemap = false;
        }
    }



    function getSitemapContent() {
        return $this->doSitemap();
    }


    function _initialize()
    {
        global $objDatabase, $_LANGID, $_CONFIG, $objInit;

        $objFWUser = FWUser::getFWUserObject();
        $query = "SELECT n.cmd,
                         n.catid,
                         n.catname,
                         n.target,
                         n.parcat,
                         c.redirect,
                         m.name AS section,
                         a_s.url AS alias_url
                    FROM ".DBPREFIX."content_navigation     AS n
              INNER JOIN ".DBPREFIX."content                AS c        ON c.id = n.catid
              INNER JOIN ".DBPREFIX."modules                AS m        ON m.id = n.module
         LEFT OUTER JOIN ".DBPREFIX."module_alias_target    AS a_t      ON a_t.url = n.catid
         LEFT OUTER JOIN ".DBPREFIX."module_alias_source    AS a_s      ON a_s.target_id = a_t.id AND a_s.isdefault = 1 AND a_s.lang_id = ".$_LANGID."
                   WHERE n.displaystatus = 'on'
                     AND n.activestatus = '1'
                     AND n.is_validated='1'
                     AND n.lang=".$_LANGID."
                     ".(
                        !$objFWUser->objUser->login() ?
                            // user is not authenticated
                            ($_CONFIG['coreListProtectedPages'] == 'off' ? 'AND n.protected=0' : '') :
                            // user is authenticated
                            (
                                !$objFWUser->objUser->getAdminStatus() ?
                                     // user is not administrator
                                    'AND (n.protected=0'.(count($objFWUser->objUser->getDynamicPermissionIds()) ? ' OR n.frontend_access_id IN ('.implode(', ', $objFWUser->objUser->getDynamicPermissionIds()).')' : '').')' :
                                    // user is administrator
                                    ''
                            )
                        )."
                     AND (n.startdate<=CURDATE() OR n.startdate='0000-00-00')
                     AND (n.enddate>=CURDATE() OR n.enddate='0000-00-00')
                ORDER BY n.parcat DESC, n.displayorder";

        $objResult = $objDatabase->Execute($query);
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $s = $objResult->fields['section'];
                $c = $objResult->fields['cmd'];
                $section = ( ($s=="") ? "" : "&amp;section=$s" );
                $cmd     = ( ($c=="") ? "" : "&amp;cmd=$c" );

                $useAlias = false;

                if ($_CONFIG['aliasStatus']
                    && ($objResult->fields['alias_url']
                        || $_CONFIG['useVirtualLanguagePath'] == 'on' && $s == 'home'
                    )
                ) {
                    $useAlias = true;
                }

                if ($useAlias) {
                    if ($_CONFIG['useVirtualLanguagePath'] == 'on') {
                        // the homepage should not use an alias, but only a slash
                        if ($s == 'home') {
                            if ($_LANGID == $objInit->defaultFrontendLangId) {
                                // the default language should not use the virtual language path
                                $menu_url = '/';
                            } else {
                                $menu_url = CONTREXX_VIRTUAL_LANGUAGE_PATH.'/';
                            }
                        } else {
                            $menu_url = CONTREXX_VIRTUAL_LANGUAGE_PATH.'/'.rawurlencode(stripslashes($objResult->fields['alias_url']));
                        }
                    } else {
                        $menu_url = CONTREXX_VIRTUAL_LANGUAGE_PATH.'/'.rawurlencode(stripslashes($objResult->fields['alias_url']));
                    }
                    $link = FWSystem::mkurl($menu_url);
                } elseif (!empty($objResult->fields['redirect'])) {
                    $link = $objResult->fields['redirect'];
                } elseif (!empty($s)) {
                    $link = CONTREXX_SCRIPT_PATH."?section=".$s.$cmd;
                } else {
                    $link = CONTREXX_SCRIPT_PATH."?page=".$objResult->fields['catid'].$section.$cmd;
                }
                $this->_arrName[$objResult->fields['parcat']][$objResult->fields['catid']] = stripslashes($objResult->fields['catname']);
                $this->_arrUrl[$objResult->fields['catid']] = $link;
                $this->_arrTarget[$objResult->fields['catid']] = empty($objResult->fields['target']) ? "_self" : $objResult->fields['target'];
                $objResult->MoveNext();
            }
        }
    }


    /*
    * Do shop categories menu
    *
    * @param    integer  $parcat
    * @param    integer  $level
    * @param    integer  $selectedid
    * @return   string   $result
    */
    function _doSitemapArray($parcat=0,$level=0)
    {
        $list = $this->_arrName[$parcat];
        if (is_array($list)) {
            while (list($key,$val) = each($list)) {
                $this->_sitemapPageName[$key] = $val;
                $this->_sitemapPageURL[$key] = $this->_arrUrl[$key];
                $this->_sitemapPageLevel[$key] = $level;
                $this->_sitemapPageTarget[$key] = $this->_arrTarget[$key];

                if (isset($this->_arrName[$key])) {
                    $this->_doSitemapArray($key,$level+1);
                }
            }
        }
    }


    /**
    * Do Sitemap rows
    *
    */
    function doSitemap()
    {
        if ($this->_doSitemap && is_array($this->_sitemapPageName)) {
            $this->_sitemapBlock = trim($this->_objTpl->_blocks['sitemap']);
            if (ereg('.*{SUB_MENU}.*', $this->_sitemapBlock)) {
                $nestedSitemap = $this->_subTagStart.$this->_buildNestedSitemap().$this->_subTagEnd."\n";
                return ereg_replace('<!-- BEGIN sitemap -->.*<!-- END sitemap -->', $nestedSitemap, $this->pageContent);
            } else {
                while (list($key,$val) = each($this->_sitemapPageName)) {
                    $lvl = $this->_sitemapPageLevel[$key]+1;
                    $cssStyle = $this->_cssPrefix.$lvl;
                    if ($this->_sitemapPageLevel[$key]!=0){
                        $width=$this->_sitemapPageLevel[$key]*25;
                    } else {
                        $width=1;
                    }
                    $spacer = "<img src='".ASCMS_MODULE_IMAGE_WEB_PATH."/sitemap/spacer.gif' width='$width' height='12' alt='' />";

                    $this->_objTpl->setVariable(array(
                        'STYLE'     => $cssStyle,
                        'SPACER'    => $spacer,
                        'NAME'      => $val,
                        'TARGET'    => $this->_sitemapPageTarget[$key],
                        'URL'       => $this->_sitemapPageURL[$key]
                    ));
                    $this->_objTpl->parse("sitemap");
                }
            }
        }
        return $this->_objTpl->get();
    }




    function _buildNestedSitemap($key = 0)
    {
        $sitemapBlock = "";

        foreach ($this->_arrName[$key] as $pageId => $pageTitle) {
            if (isset($this->_arrName[$pageId])) {
                $subPages = $this->_subTagStart.$this->_buildNestedSitemap($pageId).$this->_subTagEnd."\n";
            } else {
                $subPages = "";
            }

            if ($this->_sitemapPageLevel[$pageId] != 0) {
                $width = $this->_sitemapPageLevel[$pageId]*25;
            } else {
                $width = 1;
            }
            $spacer = "<img src='".ASCMS_MODULE_IMAGE_WEB_PATH."/sitemap/spacer.gif' width='$width' height='12' alt='' />";

            $tmpSitemapBlock = $this->_sitemapBlock;

            $tmpSitemapBlock = str_replace('{STYLE}', $this->_cssPrefix.($this->_sitemapPageLevel[$pageId]+1), $tmpSitemapBlock);
            $tmpSitemapBlock = str_replace('{SPACER}', $spacer, $tmpSitemapBlock);
            $tmpSitemapBlock = str_replace('{NAME}', $this->_sitemapPageName[$pageId], $tmpSitemapBlock);
            $tmpSitemapBlock = str_replace('{TARGET}', $this->_sitemapPageTarget[$pageId], $tmpSitemapBlock);
            $tmpSitemapBlock = str_replace('{URL}', $this->_sitemapPageURL[$pageId], $tmpSitemapBlock);
            $tmpSitemapBlock = str_replace('{SUB_MENU}', $subPages, $tmpSitemapBlock);

            $sitemapBlock .= $tmpSitemapBlock."\n";
        }
        return $sitemapBlock;
    }
}
?>
