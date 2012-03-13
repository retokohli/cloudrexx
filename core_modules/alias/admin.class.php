<?php
/**
 * AliasAdmin
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @version        1.0.0
 * @package     contrexx
 * @subpackage  core_module_alias
 * @todo        Edit PHP DocBlocks!
 */
/**
 * @ignore
 */
require_once ASCMS_CORE_MODULE_PATH.'/alias/lib/aliasLib.class.php';
/**
 * @ignore
 */
require_once ASCMS_CORE_PATH.'/settings.class.php';
/**
 * @ignore
 */
require_once ASCMS_CORE_PATH.'/'.'XMLSitemap.class.php';

/**
 * AliasAdmin
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @version        1.0.0
 * @package     contrexx
 * @subpackage  core_module_alias
 * @todo        Edit PHP DocBlocks!
 */
class AliasAdmin extends aliasLib
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
    * Status message
    *
    * @access private
    * @var array
    */
    var $arrStatusMsg = array('ok' => array(), 'error' => array());

    /**
    * PHP5 constructor
    *
    * @global HTML_Template_Sigma
    * @global array
    */
    function __construct()
    {
        global $objTemplate, $_ARRAYLANG;

        // initialize FWHtAccess object
        parent::__construct();

        $this->_objTpl = new HTML_Template_Sigma(ASCMS_CORE_MODULE_PATH.'/alias/template');
        CSRF::add_placeholder($this->_objTpl);
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);

        $objTemplate->setVariable("CONTENT_NAVIGATION",
            ("<a href='index.php?cmd=alias'>".$_ARRAYLANG['TXT_ALIAS_ALIASES']."</a>"
            ."<a href='index.php?cmd=alias&amp;act=modify'>".$_ARRAYLANG['TXT_ALIAS_ADD_ALIAS']."</a>")
        );
    }

    /**
    * Set the backend page
    *
    * @access public
    * @global HTML_Template_Sigma
    * @global array
    */
    function getPage()
    {
        global $objTemplate, $_ARRAYLANG;

        if (!isset($_REQUEST['act'])) {
            $_REQUEST['act'] = '';
        }

        switch ($_REQUEST['act']) {
            case 'modify':
                $this->_modifyAlias();
                break;

            case 'delete':
                $this->_delete();

            default:
                $this->_list();
                break;
        }

        $this->_pageTitle = $_ARRAYLANG['TXT_OVERVIEW'];

        $objTemplate->setVariable(array(
            'CONTENT_TITLE'                     => $this->_pageTitle,
            'CONTENT_OK_MESSAGE'                => implode("<br />\n", $this->arrStatusMsg['ok']),
            'CONTENT_STATUS_MESSAGE'            => implode("<br />\n", $this->arrStatusMsg['error']),
            'ADMIN_CONTENT'                     => $this->_objTpl->get()
        ));
    }

    function _list()
    {
        global $_ARRAYLANG, $_CONFIG;

        $this->_objTpl->loadTemplateFile('module_alias_list.html');
        $this->_pageTitle = $_ARRAYLANG['TXT_ALIAS_ALIAS_ES'];
        $this->_objTpl->setGlobalVariable('TXT_ALIAS_ALIASES', $_ARRAYLANG['TXT_ALIAS_ALIASES']);

        $arrAliases = $this->_getAliases($_CONFIG['corePagingLimit']);
        $nr = 1;
        if (count($arrAliases)) {
            $this->_objTpl->setVariable(array(
                'TXT_ALIAS_PAGE'                    => $_ARRAYLANG['TXT_ALIAS_PAGE'],
                'TXT_ALIAS_ALIAS'                   => $_ARRAYLANG['TXT_ALIAS_ALIAS'],
                'TXT_ALIAS_FUNCTIONS'               => $_ARRAYLANG['TXT_ALIAS_FUNCTIONS'],
                'TXT_ALIAS_CONFIRM_DELETE_ALIAS'    => $_ARRAYLANG['TXT_ALIAS_CONFIRM_DELETE_ALIAS'],
                'TXT_ALIAS_OPERATION_IRREVERSIBLE'  => $_ARRAYLANG['TXT_ALIAS_OPERATION_IRREVERSIBLE'],
            ));

            $this->_objTpl->setGlobalVariable(array(
                'TXT_ALIAS_DELETE'                  => $_ARRAYLANG['TXT_ALIAS_DELETE'],
                'TXT_ALIAS_MODIFY'                  => $_ARRAYLANG['TXT_ALIAS_MODIFY'],
                'TXT_ALIAS_OPEN_ALIAS_NEW_WINDOW'   => $_ARRAYLANG['TXT_ALIAS_OPEN_ALIAS_NEW_WINDOW'],
            ));

            foreach ($arrAliases as $page) {

                $sourceURL = $page->getSlug();

                    $this->_objTpl->setVariable(array(
                        'ALIAS_TARGET_ID'       => $page->getNode()->getId(),
                        'ALIAS_SOURCE_REAL_URL' => 'http://'.$_CONFIG['domainUrl'].ASCMS_PATH_OFFSET.'/'.stripslashes($sourceURL),
                        'ALIAS_SOURCE_URL'      => 'http://'.$_CONFIG['domainUrl'].ASCMS_PATH_OFFSET.'<strong>/'.stripslashes($sourceURL).'</strong>',
                    ));

                    $target = "";
                    if ($this->_isLocalAliasTarget($page)) {
                        // alias points to a local webpage
                        $targetPage = $this->_fetchTarget($page);
                        $target = $targetPage->getTitle();
                        $targetURL = $this->_getURL($targetPage);
                        $target_title = $target . " (" . $targetURL . ")";
                    } else {
                        $target = $page->getTarget();
                        $targetURL = $target;
                        $target_title = $target;
                    }
                    $this->_objTpl->hideBlock('alias_source_not_set');
                    $this->_objTpl->parse('alias_source_list');
                    
                $this->_objTpl->setVariable(array(
                    // if target is local (target != targetURL) and target is empty: class is rowWarn
                    'ALIAS_ROW_CLASS'       => $target != $targetURL && empty($target) && $nr++ ? 'rowWarn ' : 'row'.($nr++ % 2 + 1),
                    'ALIAS_TARGET_TITLE'    => $target_title,
                ));
                $this->_objTpl->parse('aliases_list');
            }

            $this->_objTpl->parse('alias_data');
            $this->_objTpl->hideBlock('alias_no_data');

            if ($this->_getAliasesCount() > count($arrAliases)) {
                $this->_objTpl->setVariable('ALIAS_PAGING', '<br />'.getPaging($this->_getAliasesCount(), !empty($_GET['pos']) ? intval($_GET['pos']) : 0, '&amp;cmd=alias', $_ARRAYLANG['TXT_ALIAS_ALIASES']));
            }
        } else {
            $this->_objTpl->setVariable('TXT_ALIAS_NO_ALIASES_MSG', $_ARRAYLANG['TXT_ALIAS_NO_ALIASES_MSG']);

            $this->_objTpl->hideBlock('alias_data');
            $this->_objTpl->parse('alias_no_data');
        }
    }

    function _modifyAlias()
    {
        global $_ARRAYLANG, $_CONFIG;

        $aliasId = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

        // search for existing alias
        if (($alias = $this->_getAlias($aliasId))) {
            $target = $this->_fetchTarget($alias);
        } else {
            $alias = $this->_createTemporaryAlias();
            // add a | at the end, to make it a local target by default
            $alias->setTarget("|");
            $aliasId = 0;
        }

        // handle form submit
        if (isset($_POST['alias_save'])) {
            // set target and -type
            $newtype = in_array($_POST['alias_source_type'], $this->_arrAliasTypes) ? $_POST['alias_source_type'] : $this->_arrAliasTypes[0];
            
            if ($newtype == 'local') {
                $newtarget = !empty($_POST['alias_local_source']) ? $_POST['alias_local_source'] : 0;
            } else {
                $newtarget = !empty($_POST['alias_url_source']) ? trim(contrexx_stripslashes($_POST['alias_url_source'])) : '';
            }

            // handle existing slugs pointing to the target
            $aliasses = array();
            if (!empty($_POST['alias_aliases']) && is_array($_POST['alias_aliases'])) {
                $nr = 0;
                foreach ($_POST['alias_aliases'] as $sourceId => $aliasSource) {
                    if (!empty($aliasSource)) {
                        $aliasses[intval($sourceId)] = $aliasSource;
                    }
                    $nr++;
                }
            }
            
            // delete removed
            $sources = $this->_getAliassesWithSameTarget($alias);
            foreach ($sources as $sourceAlias) {
                if (!isset($aliasses[$sourceAlias->getNode()->getId()])) {
                    // alias is no longer listet in POST: delete it
                    $this->_delete($sourceAlias->getNode()->getId());
                }
            }

            // handle enw slugs pointing to the target
            $newaliasses = array();
            if (!empty($_POST['alias_aliases_new']) && is_array($_POST['alias_aliases_new'])) {
                foreach ($_POST['alias_aliases_new'] as $id => $newAliasSource) {
                    if (!empty($newAliasSource)) {
                        $newaliasses[] = $newAliasSource;
                    }
                }
            }
            
            // save information
            if (!empty($newtarget)) {
                if (count($aliasses) || count($newaliasses)) {
                    $error = false;

                    foreach ($aliasses as $id=>$slug) {
                        if (!$this->_saveAlias($slug, $newtarget, $newtype == 'local', $id)) {
                            $this->arrStatusMsg['error'][] = $aliasId ? $_ARRAYLANG['TXT_ALIAS_ALIAS_UPDATE_FAILED'] : $_ARRAYLANG['TXT_ALIAS_ALIAS_ADD_FAILED'];
                            $this->arrStatusMsg['error'][] = $_ARRAYLANG['TXT_ALIAS_RETRY_OPERATION'];
                            $error = true;
                            break;
                        }
                    }
                    if (!$error) {
                        foreach ($newaliasses as $id=>$slug) {
                            if (!$this->_saveAlias($slug, $newtarget, $newtype == 'local')) {
                                $this->arrStatusMsg['error'][] = $aliasId ? $_ARRAYLANG['TXT_ALIAS_ALIAS_UPDATE_FAILED'] : $_ARRAYLANG['TXT_ALIAS_ALIAS_ADD_FAILED'];
                                $this->arrStatusMsg['error'][] = $_ARRAYLANG['TXT_ALIAS_RETRY_OPERATION'];
                                break;
                            }
                        }
                        // load new aliasses
                    }
                    if ($_CONFIG['xmlSitemapStatus'] == 'on' && ($result = XMLSitemap::write()) !== true) {
                        $this->arrStatusMsg['error'][] = $result;
                    }
                    $this->arrStatusMsg['ok'][] = $aliasId ? $_ARRAYLANG['TXT_ALIAS_ALIAS_SUCCESSFULLY_UPDATED'] : $_ARRAYLANG['TXT_ALIAS_ALIAS_SUCCESSFULLY_ADDED'];
                    return $this->_list();
                } else {
                    $this->arrStatusMsg['error'][] = $_ARRAYLANG['TXT_ALIAS_ONE_ALIAS_REQUIRED_MSG'];
                }
            } else {
                if ($newtype == 'local') {
                    $this->arrStatusMsg['error'][] = $_ARRAYLANG['TXT_ALIAS_PAGE_REQUIRED_MSG'];
                } else {
                    $this->arrStatusMsg['error'][] = $_ARRAYLANG['TXT_ALIAS_URL_REQUIRED_MSG'];
                }
            }
        }

        // prepare template
        $this->_objTpl->loadTemplateFile('module_alias_modify.html');
        $this->_pageTitle = $aliasId ? $_ARRAYLANG['TXT_ALIAS_MODIFY_ALIAS'] : $_ARRAYLANG['TXT_ALIAS_ADD_ALIAS'];

        $this->_objTpl->setVariable(array(
            'TXT_ALIAS_TARGET_PAGE'             => $_ARRAYLANG['TXT_ALIAS_TARGET_PAGE'],
            'TXT_ALIAS_LOCAL'                   => $_ARRAYLANG['TXT_ALIAS_LOCAL'],
            'TXT_ALIAS_URL'                     => $_ARRAYLANG['TXT_ALIAS_URL'],
            'TXT_ALIAS_BROWSE'                  => $_ARRAYLANG['TXT_ALIAS_BROWSE'],
            'TXT_ALIAS_ALIAS_ES'                => $_ARRAYLANG['TXT_ALIAS_ALIAS_ES'],
            'TXT_ALIAS_DELETE'                  => $_ARRAYLANG['TXT_ALIAS_DELETE'],
            'TXT_ALIAS_CONFIRM_REMOVE_ALIAS'    => $_ARRAYLANG['TXT_ALIAS_CONFIRM_REMOVE_ALIAS'],
            'TXT_ALIAS_ADD_ANOTHER_ALIAS'       => $_ARRAYLANG['TXT_ALIAS_ADD_ANOTHER_ALIAS'],
            'TXT_ALIAS_CANCEL'                  => $_ARRAYLANG['TXT_ALIAS_CANCEL'],
            'TXT_ALIAS_SAVE'                    => $_ARRAYLANG['TXT_ALIAS_SAVE'],
            'TXT_ALIAS_STANDARD_RADIOBUTTON'    => $_ARRAYLANG['TXT_ALIAS_STANDARD_RADIOBUTTON']
        ));

        $langPathPrefix = FWLanguage::getLanguageParameter($this->langId, 'lang');

        $this->_objTpl->setGlobalVariable(array(
            'TXT_ALIAS_DELETE'                  => $_ARRAYLANG['TXT_ALIAS_DELETE'],
            'ALIAS_DOMAIN_URL'                  => 'http://'.$_CONFIG['domainUrl'].ASCMS_PATH_OFFSET.'/',
            'TXT_ALIAS_STANDARD_RADIOBUTTON'    => $_ARRAYLANG['TXT_ALIAS_STANDARD_RADIOBUTTON']
        ));
        
        $is_local = $this->_isLocalAliasTarget($alias);
        if ($is_local) {
            // alias points to a local webpage
            $targetPage = $this->_fetchTarget($alias);
            if ($targetPage) {
                $target = $targetPage->getTitle();
                $targetURL = $this->_getURL($targetPage);
                $target_title = $target . " (" . $targetURL . ")";
            } else {
                $target = "";
                $targetURL = "";
                $target_title = "";
            }
        } else {
            $target = $alias->getTarget();
            $targetURL = $target;
            $target_title = $target;
        }

        $this->_objTpl->setVariable(array(
            'ALIAS_ID'                          => $aliasId,
            'ALIAS_TITLE_TXT'                   => $this->_pageTitle,
            'ALIAS_SELECT_LOCAL_PAGE'           => $is_local ? 'checked="checked"' : '',
            'ALIAS_SELECT_URL_PAGE'             => !$is_local ? 'checked="checked"' : '',
            'ALIAS_SELECT_LOCAL_BOX'            => $is_local ? 'block' : 'none',
            'ALIAS_LOCAL_SOURCE'                => $is_local ? $alias->getTarget() : '',
            'ALIAS_LOCAL_PAGE_URL'              => $is_local ? htmlentities($targetURL, ENT_QUOTES, CONTREXX_CHARSET) : '',
            'ALIAS_SELECT_URL_BOX'              => !$is_local ? 'block' : 'none',
            'ALIAS_URL_SOURCE'                  => !$is_local ? htmlentities($targetURL, ENT_QUOTES, CONTREXX_CHARSET) : 'http://'
        ));

        $nr = 0;
        
        $sources = $this->_getAliassesWithSameTarget($alias);

        foreach ($sources as $sourceAlias) {
            $url = $sourceAlias->getSlug();
            $this->_objTpl->setVariable(array(
                'ALIAS_DOMAIN_URL'              => 'http://'.$_CONFIG['domainUrl'].ASCMS_PATH_OFFSET.'/',
                'ALIAS_ALIAS_ID'                => $sourceAlias->getNode()->getId(),
                'ALIAS_ALIAS_NR'                => $nr++,
                'ALIAS_IS_DEFAULT'              => '',
                'ALIAS_ALIAS_PREFIX'            => '',
                'ALIAS_ALIAS_URL'               => stripslashes(htmlentities($url, ENT_QUOTES, CONTREXX_CHARSET))
            ));
            $this->_objTpl->parse('alias_list');
        }
    }
    

    function _delete($aliasId = "")
    {
        global $_ARRAYLANG;

        if ($aliasId == "") {
            $aliasId = !empty($_GET['id']) ? intval($_GET['id']) : 0;
        }

        if ($aliasId) {
            if ($this->_deleteAlias($aliasId)) {
                $this->arrStatusMsg['ok'][] = $_ARRAYLANG['TXT_ALIAS_ALIAS_SUCCESSFULLY_REMOVED'];
            } else {
                $this->arrStatusMsg['error'][] = $_ARRAYLANG['TXT_ALIAS_ALIAS_REMOVE_FAILED'];
                $this->arrStatusMsg['error'][] = $_ARRAYLANG['TXT_ALIAS_RETRY_OPERATION'];
            }
        }
    }
}
