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

    private $objSettings;

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

        $this->_objTpl = &new HTML_Template_Sigma(ASCMS_CORE_MODULE_PATH.'/alias/template');
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);

        $this->objSettings = new settingsManager();

        if ($this->objSettings->isWritable() && isset($_REQUEST['act']) && $_REQUEST['act'] == 'settings' && isset($_POST['alias_save'])) {
            if ($this->_setAliasAdministrationStatus(isset($_POST['alias_status']) && $_POST['alias_status'])) {
                $this->arrStatusMsg['ok'][] = $_ARRAYLANG['TXT_ALIAS_CONFIG_SUCCESSFULLY_APPLYED'];
            } else {
                $this->arrStatusMsg['error'][] = $_ARRAYLANG['TXT_ALIAS_CONFIG_FAILED_APPLY'];
            }
        }

        $arrConfig = $this->_getConfig();
		/* this must be tested against an empty string because the initial setting
         * of aliasStatus is defined as such. If aliasStatus is set to 0, then this will
         * mean that the alias modul has been deactivated and therefor there should be
         * no intention to activate the module automatically.
         */
        if ($arrConfig['aliasStatus'] == '') {
            if ($this->_isModRewriteInUse()) {
                $this->_setAliasAdministrationStatus(true);
                $this->_initConfig();
                $arrConfig = $this->_getConfig();
            }
        }

        $objTemplate->setVariable("CONTENT_NAVIGATION",
            ($arrConfig['aliasStatus'] == '1' && $this->objFWHtAccess->isHtAccessFileLoaded()
                ?   "<a href='index.php?cmd=alias'>".$_ARRAYLANG['TXT_ALIAS_ALIASES']."</a>"
                   ."<a href='index.php?cmd=alias&amp;act=modify'>".$_ARRAYLANG['TXT_ALIAS_ADD_ALIAS']."</a>"
                   ."<a href='index.php?cmd=alias&amp;act=maplist'>".$_ARRAYLANG['TXT_ALIAS_DOMAIN_MAPPINGS']."</a>"
                   ."<a href='index.php?cmd=alias&amp;act=mapmod'>".$_ARRAYLANG['TXT_ALIAS_DOMAIN_MAPPINGS_ADD']."</a>"
                : '')
            ."<a href='index.php?cmd=alias&amp;act=settings'>".$_ARRAYLANG['TXT_ALIAS_SETTINGS']."</a>"
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

        $arrConfig = $this->_getConfig();
        if (!$arrConfig['aliasStatus'] || !$this->objFWHtAccess->isHtAccessFileLoaded()) {
            $_REQUEST['act'] = 'settings';
        }

        if (!isset($_REQUEST['act'])) {
            $_REQUEST['act'] = '';
        }

        switch ($_REQUEST['act']) {
            case 'settings':
                $this->_settings();
                break;

            case 'modify':
                $this->_modifyAlias();
                break;

            case 'maplist':
                $this->_listMappings();
                break;

            case 'mapmod':
                $this->_modifyMapping();
                break;
                
            case 'deletemap':
            	$this->_deletemap();
            	break; 
                
            case 'delete':
                $this->_delete();

            case 'rewriteRules':
                $this->_rewriteRules();

            default:
                $this->_list();
                break;
        }

        $this->_pageTitle = $_ARRAYLANG['TXT_OVERVIEW'];

        $objTemplate->setVariable(array(
            'CONTENT_TITLE'                => $this->_pageTitle,
            'CONTENT_OK_MESSAGE'        => implode("<br />\n", $this->arrStatusMsg['ok']),
            'CONTENT_STATUS_MESSAGE'    => implode("<br />\n", $this->arrStatusMsg['error']),
            'ADMIN_CONTENT'                => $this->_objTpl->get()
        ));
    }

    function _rewriteRules()
    {
        $this->_activateRewriteEngine();
    }

    function _list()
    {
        global $_ARRAYLANG, $_CONFIG;

        $this->_objTpl->loadTemplateFile('module_alias_list.html');
        $this->_pageTitle = $_ARRAYLANG['TXT_ALIAS_ALIAS_ES'];
        $this->_objTpl->setGlobalVariable('TXT_ALIAS_ALIASES', $_ARRAYLANG['TXT_ALIAS_ALIASES']);

        $arrAliases = $this->_getAliases($_CONFIG['corePagingLimit']);
        $nr = 1;
        if (count($arrAliases[$this->langId])) {
            $this->_objTpl->setVariable(array(
                'TXT_ALIAS_PAGE'        => $_ARRAYLANG['TXT_ALIAS_PAGE'],
                'TXT_ALIAS_ALIAS'    => $_ARRAYLANG['TXT_ALIAS_ALIAS'],
                'TXT_ALIAS_FUNCTIONS'    => $_ARRAYLANG['TXT_ALIAS_FUNCTIONS'],
                'TXT_ALIAS_CONFIRM_DELETE_ALIAS'    => $_ARRAYLANG['TXT_ALIAS_CONFIRM_DELETE_ALIAS'],
                'TXT_ALIAS_OPERATION_IRREVERSIBLE'    => $_ARRAYLANG['TXT_ALIAS_OPERATION_IRREVERSIBLE']
            ));

            $this->_objTpl->setGlobalVariable(array(
                'TXT_ALIAS_DELETE'                  => $_ARRAYLANG['TXT_ALIAS_DELETE'],
                'TXT_ALIAS_MODIFY'                  => $_ARRAYLANG['TXT_ALIAS_MODIFY'],
                'TXT_ALIAS_OPEN_ALIAS_NEW_WINDOW'   => $_ARRAYLANG['TXT_ALIAS_OPEN_ALIAS_NEW_WINDOW']
            ));

            $langPathPrefix = $_CONFIG['useVirtualLanguagePath'] == 'on' ? '/'.FWLanguage::getLanguageParameter($this->langId, 'lang') : '';
            $arrRewriteInfo = $this->_getRewriteInfo();

            foreach ($arrAliases[$this->langId] as $aliasId => $arrAlias) {
                foreach ($arrAlias['sources'] as $arrAliasSource) {

                    $this->_objTpl->setVariable(array(
                        'ALIAS_SOURCE_REAL_URL' => 'http://'.$_CONFIG['domainUrl'].ASCMS_PATH_OFFSET.$langPathPrefix.'/'.stripslashes($arrAliasSource['url']),
                        'ALIAS_SOURCE_URL'      => 'http://'.$_CONFIG['domainUrl'].ASCMS_PATH_OFFSET.$langPathPrefix.'<strong>/'.stripslashes($arrAliasSource['url']).'</strong>',
                    ));

                    if ($arrAlias['type'] == 'local') {
                        // alias points to a local webpage
                        $target = $arrAlias['pageUrl'];
                    } else {
                        $target = $arrAlias['url'];
                    }

                    if ($_CONFIG['useVirtualLanguagePath'] == 'on') {
                        // virtual language path has be taken in account
                        $source = FWLanguage::getLanguageParameter($this->langId, 'lang').'/'.$arrAliasSource['url'];
                    } else {
                        $source = $arrAliasSource['url'];
                    }

                    if (   is_array($arrRewriteInfo) // check if there are any rewrite rules defined
                        && isset($arrRewriteInfo[$target]) // check if one of the rewrite rules uses our target URI
                        && in_array($source, $arrRewriteInfo[$target]) // check if the rewrite rule that uses our target URI also uses our source URI
                    ) {
                        $this->_objTpl->hideBlock('alias_source_not_set');
                    } else {
                        $this->_objTpl->setVariable('TXT_ALIAS_NOT_ACTIVE_ALIAS_MSG', $_ARRAYLANG['TXT_ALIAS_NOT_ACTIVE_ALIAS_MSG']);
                        $this->_objTpl->touchBlock('alias_source_not_set');
                    }
                    $this->_objTpl->parse('alias_source_list');
                }
                $this->_objTpl->setVariable(array(
                    'ALIAS_ROW_CLASS'    => $arrAlias['type'] == 'local' && empty($arrAlias['pageUrl']) && $nr++ ? 'rowWarn ' : 'row'.($nr++ % 2 + 1),
                    'ALIAS_TARGET_ID'        => $aliasId,
                    'ALIAS_TARGET_TITLE'    => $arrAlias['type'] == 'local' ? (!empty($arrAlias['pageUrl']) ? $arrAlias['title'].' ('.$arrAlias['pageUrl'].')' : $_ARRAYLANG['TXT_ALIAS_TARGET_PAGE_NOT_EXIST']) : htmlentities($arrAlias['url'], ENT_QUOTES, CONTREXX_CHARSET)
                ));
                $this->_objTpl->parse('aliases_list');
            }

            $this->_objTpl->parse('alias_data');
            $this->_objTpl->hideBlock('alias_no_data');

            if ($this->_getAliasesCount() > count($arrAliases[$this->langId])) {
                $this->_objTpl->setVariable('ALIAS_PAGING', '<br />'.getPaging($this->_getAliasesCount(), !empty($_GET['pos']) ? intval($_GET['pos']) : 0, '&amp;cmd=alias', $_ARRAYLANG['TXT_ALIAS_ALIASES']));
            }
        } else {
            $this->_objTpl->setVariable('TXT_ALIAS_NO_ALIASES_MSG', $_ARRAYLANG['TXT_ALIAS_NO_ALIASES_MSG']);

            $this->_objTpl->hideBlock('alias_data');
            $this->_objTpl->parse('alias_no_data');
        }
    }


    function _modifyMapping()
    {
        global $_ARRAYLANG, $_CONFIG;

        $mappingId = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
        $arrMapping = array();
        
        $this->_objTpl->loadTemplateFile('module_alias_mapping_modify.html');
        $this->_pageTitle = $mappingId ? $_ARRAYLANG['TXT_ALIAS_MAPPING_MODIFY'] : $_ARRAYLANG['TXT_ALIAS_MAPPING_ADD'];
        $this->_objTpl->setVariable($_ARRAYLANG);
        $error = false;
        
        if(isset($_REQUEST['alias_mapping_save'])){
	        $arrMapping['target'] = $_REQUEST['alias_mapping_page_id'];
	        $arrMapping['domain'] = $_REQUEST['alias_mapping_domain'];
	        if(empty($arrMapping['domain'])){
	        	$this->arrStatusMsg['error'][] = $_ARRAYLANG['TXT_ALIAS_MAPPING_MISSING_DOMAIN'];
	        	$error = true;
	        }       	
	        if(empty($arrMapping['target'])){
	        	$this->arrStatusMsg['error'][] = $_ARRAYLANG['TXT_ALIAS_MAPPING_MISSING_TARGET'];
	        	$error = true;
	        }        
	        
	        if (!$mappingId) { //new
	        	if(!$error){
	        		$this->_addMapping($arrMapping);        	        		
	        	}        	
	        } else {
	        	$this->_updateMapping($mappingId, $arrMapping);
	        }
	        
	        if(!$error){
				$this->arrStatusMsg['ok'][] = $_ARRAYLANG['TXT_ALIAS_MAPPING_SAVED_SUCCESSFULLY'];	
				return $this->_listMappings();
			}		        
        } 
        	
        $arrMapping = $this->_getMapping($mappingId);
                
        $this->_objTpl->setVariable(array(
       		'ALIAS_MAPPING_ID' 			=> $mappingId,
        	'ALIAS_MAPPING_TITLE_TXT' 	=> $this->_pageTitle,
        	'ALIAS_MAPPING_PAGE_ID' 	=> $arrMapping['target'],
        	'ALIAS_MAPPING_DOMAIN' 		=> $arrMapping['domain'],
        	'ALIAS_MAPPING_PAGE_URL'	=> $this->_getPageURL($arrMapping['target'])
		));           
    }

    function _listMappings()
    {
   		global $_ARRAYLANG, $_CONFIG;
   		
    	$this->_objTpl->loadTemplateFile('module_alias_mapping_list.html');
        $this->_pageTitle = $_ARRAYLANG['TXT_ALIAS_DOMAIN_MAPPINGS'];
        $this->_objTpl->setVariable($_ARRAYLANG);

        $arrMappings = $this->_getMappings($_CONFIG['corePagingLimit']);
        if($arrMappings){
        	$this->_objTpl->hideBlock('mapping_no_data');
        } else {
        	$this->_objTpl->hideBlock('mapping_data');
        }
        
        foreach($arrMappings as $arrMapping){
        	$this->_objTpl->setVariable(array(
        		'ALIAS_MAPPING_ID' 			=> $arrMapping['id'], 
        		'ALIAS_MAPPING_DOMAIN_URL' 	=> 'http://'.$arrMapping['domain'], 
        		'ALIAS_MAPPING_DOMAIN' 		=> $arrMapping['domain'],
        		'ALIAS_MAPPING_TITLE'  		=> $this->_getPageURL($arrMapping['target']),
        	));
        	$this->_objTpl->parse('mapping_list');    	
        }    
        $mappingCount = $this->_getMappingCount();
        if ($mappingCount > count($arrMappings)) {
        	$this->_objTpl->setVariable('ALIAS_PAGING', '<br />'.getPaging($mappingCount, !empty($_GET['pos']) ? intval($_GET['pos']) : 0, '&amp;cmd=alias&amp;act=maplist', $_ARRAYLANG['TXT_ALIAS_DOMAIN_MAPPINGS']));
        }	
    }

    function _modifyAlias()
    {
        global $_ARRAYLANG, $_CONFIG;

        $aliasId = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
        $arrSourceUrls = array();

        if (($arrAlias = $this->_getAlias($aliasId))) {
            $oldTarget = $arrAlias['type'] == 'local' ? $arrAlias['pageUrl'] : $arrAlias['url'];
        } else {
            $oldTarget = '';
            $arrAlias = array(
                'type'        => 'local',
                'url'        => '',
                'pageUrl'    => '',
                'sources'    => array()
            );
            $aliasId = 0;
        }


        if (isset($_POST['alias_save'])) {
            $arrAlias['type'] = in_array($_POST['alias_source_type'], $this->_arrAliasTypes) ? $_POST['alias_source_type'] : $this->_arrAliasTypes[0];

            if ($arrAlias['type'] == 'local') {
                $arrAlias['url'] = !empty($_POST['alias_local_source']) ? intval($_POST['alias_local_source']) : 0;
                $arrAlias['pageUrl'] = !empty($_POST['alias_local_page_url']) ? trim(contrexx_stripslashes($_POST['alias_local_page_url'])) : '';
            } else {
                $arrAlias['url'] = !empty($_POST['alias_url_source']) ? trim(contrexx_stripslashes($_POST['alias_url_source'])) : '';
            }

            $arrAlias['sources'] = array();
            if (!empty($_POST['alias_aliases']) && is_array($_POST['alias_aliases'])) {
                $nr = 0;
                foreach ($_POST['alias_aliases'] as $sourceId => $aliasSource) {
                    $aliasSource = trim(contrexx_stripslashes($aliasSource));
                    $aliasSource = str_replace(array(' ', '\\\ '), '\\ ', $aliasSource);
                    if (!empty($aliasSource)) {
                        $arrAlias['sources'][] = array(
                            'id'        => intval($sourceId),
                            'url'        => $aliasSource,
                            'isdefault' => isset($_POST['alias_use_default']) && $_POST['alias_use_default'] == "$sourceId" ? 1 : 0
                        );
                    }
                    $nr++;
                }
            }

            if (!empty($_POST['alias_aliases_new']) && is_array($_POST['alias_aliases_new'])) {

                foreach ($_POST['alias_aliases_new'] as $id => $newAliasSource) {
                    $newAliasSource = trim(str_replace(array(' ', '\\\ '), '\\ ', contrexx_stripslashes($newAliasSource)));
                    if (!empty($newAliasSource)) {
                        if (!$this->is_alias_valid($newAliasSource)) {
                            $this->arrStatusMsg['error'][] = sprintf($_ARRAYLANG['TXT_ALIAS_MUST_NOT_BE_A_FILE'], htmlentities($newAliasSource, ENT_QUOTES, CONTREXX_CHARSET));
                            continue;
                        }

                        $arrAlias['sources'][] = array(
                            'url'       => $newAliasSource,
                            'isdefault' => $_POST['alias_use_default'] == "newalias_$id" ? 1 : 0
                        );
                    }
                }
            }

            if (!empty($arrAlias['url'])) {
                if (!$this->_isUniqueAliasTarget($arrAlias['url'], $aliasId)) {
                    $this->arrStatusMsg['error'][] = sprintf(
                        $_ARRAYLANG['TXT_ALIAS_TARGET_ALREADY_IN_USE'],
                        htmlentities(
                            ($arrAlias['type'] == 'local'
                                ? $arrAlias['pageUrl'] : $arrAlias['url']),
                            ENT_QUOTES, CONTREXX_CHARSET));
                } elseif (count($arrAlias['sources'])) {
                    $error = false;

                    foreach ($arrAlias['sources'] as $arrSource) {
                        $target = $arrAlias['type'] == 'local' ? $arrAlias['pageUrl'] : $arrAlias['url'];
// TODO: _isUniqueAliasSource RETURNS FALSE -> IMPROVE THE CHECK SO THAT NO FALSE POSITIVE HAPPENDS
//print "!\$this->_isUniqueAliasSource( ${arrSource['url']}, $target, $oldTarget, ".(empty($arrSource['id']) ? 0 : $arrSource['id']).")";
                        if (   in_array($arrSource['url'], $arrSourceUrls)
                            || !$this->_isUniqueAliasSource(
                                    $arrSource['url'], $target, $oldTarget,
                                    (empty($arrSource['id']) ? 0 : $arrSource['id']))
                        ) {
                            $error = true;
                            $this->arrStatusMsg['error'][] = sprintf($_ARRAYLANG['TXT_ALIAS_ALREADY_IN_USE'], htmlentities($arrSource['url'], ENT_QUOTES, CONTREXX_CHARSET));
                        } elseif (!$this->is_alias_valid($arrSource['url'])) {
                            $error = true;
                            $this->arrStatusMsg['error'][] = sprintf($_ARRAYLANG['TXT_ALIAS_MUST_NOT_BE_A_FILE'], htmlentities($arrSource['url'], ENT_QUOTES, CONTREXX_CHARSET));
                        } else {
                            $arrSourceUrls[] = $arrSource['url'];
                        }
                    }

                    if (!$error) {
                        if (($aliasId ? $this->_updateAlias($aliasId, $arrAlias) : $this->_addAlias($arrAlias))) {
                            if ($_CONFIG['xmlSitemapStatus'] == 'on' && ($result = XMLSitemap::write()) !== true) {
                                $this->arrStatusMsg['error'][] = $result;
                            }
                            $this->arrStatusMsg['ok'][] = $aliasId ? $_ARRAYLANG['TXT_ALIAS_ALIAS_SUCCESSFULLY_UPDATED'] : $_ARRAYLANG['TXT_ALIAS_ALIAS_SUCCESSFULLY_ADDED'];
                            return $this->_list();
                        } else {
                            $this->arrStatusMsg['error'][] = $aliasId ? $_ARRAYLANG['TXT_ALIAS_ALIAS_UPDATE_FAILED'] : $_ARRAYLANG['TXT_ALIAS_ALIAS_ADD_FAILED'];
                            $this->arrStatusMsg['error'][] = $_ARRAYLANG['TXT_ALIAS_RETRY_OPERATION'];
                        }
                    }
                } else {
                    $this->arrStatusMsg['error'][] = $_ARRAYLANG['TXT_ALIAS_ONE_ALIAS_REQUIRED_MSG'];
                }
            } else {
                if ($arrAlias['type'] == 'local') {
                    $this->arrStatusMsg['error'][] = $_ARRAYLANG['TXT_ALIAS_PAGE_REQUIRED_MSG'];
                } else {
                    $this->arrStatusMsg['error'][] = $_ARRAYLANG['TXT_ALIAS_URL_REQUIRED_MSG'];
                }
            }
        }

        $this->_objTpl->loadTemplateFile('module_alias_modify.html');
        $this->_pageTitle = $aliasId ? $_ARRAYLANG['TXT_ALIAS_MODIFY_ALIAS'] : $_ARRAYLANG['TXT_ALIAS_ADD_ALIAS'];

        $this->_objTpl->setVariable(array(
            'TXT_ALIAS_TARGET_PAGE'                => $_ARRAYLANG['TXT_ALIAS_TARGET_PAGE'],
            'TXT_ALIAS_LOCAL'                    => $_ARRAYLANG['TXT_ALIAS_LOCAL'],
            'TXT_ALIAS_URL'                        => $_ARRAYLANG['TXT_ALIAS_URL'],
            'TXT_ALIAS_BROWSE'                    => $_ARRAYLANG['TXT_ALIAS_BROWSE'],
            'TXT_ALIAS_ALIAS_ES'                => $_ARRAYLANG['TXT_ALIAS_ALIAS_ES'],
            'TXT_ALIAS_DELETE'                    => $_ARRAYLANG['TXT_ALIAS_DELETE'],
            'TXT_ALIAS_CONFIRM_REMOVE_ALIAS'    => $_ARRAYLANG['TXT_ALIAS_CONFIRM_REMOVE_ALIAS'],
            'TXT_ALIAS_ADD_ANOTHER_ALIAS'        => $_ARRAYLANG['TXT_ALIAS_ADD_ANOTHER_ALIAS'],
            'TXT_ALIAS_CANCEL'                    => $_ARRAYLANG['TXT_ALIAS_CANCEL'],
            'TXT_ALIAS_SAVE'                    => $_ARRAYLANG['TXT_ALIAS_SAVE'],
            'TXT_ALIAS_STANDARD_RADIOBUTTON'    => $_ARRAYLANG['TXT_ALIAS_STANDARD_RADIOBUTTON']
        ));

        $langPathPrefix = $_CONFIG['useVirtualLanguagePath'] == 'on' ? '/'.FWLanguage::getLanguageParameter($this->langId, 'lang') : '';

        $this->_objTpl->setGlobalVariable(array(
            'TXT_ALIAS_DELETE'                    => $_ARRAYLANG['TXT_ALIAS_DELETE'],
            'ALIAS_DOMAIN_URL'                => 'http://'.$_CONFIG['domainUrl'].ASCMS_PATH_OFFSET.$langPathPrefix.'/',
            'TXT_ALIAS_STANDARD_RADIOBUTTON'    => $_ARRAYLANG['TXT_ALIAS_STANDARD_RADIOBUTTON']
        ));

        $this->_objTpl->setVariable(array(
            'ALIAS_ID'                    => $aliasId,
            'ALIAS_TITLE_TXT'            => $this->_pageTitle,
            'ALIAS_SELECT_LOCAL_PAGE'    => $arrAlias['type'] == 'local' ? 'checked="checked"' : '',
            'ALIAS_SELECT_URL_PAGE'        => $arrAlias['type'] == 'url' ? 'checked="checked"' : '',
            'ALIAS_SELECT_LOCAL_BOX'    => $arrAlias['type'] == 'local' ? 'block' : 'none',
            'ALIAS_LOCAL_SOURCE'        => $arrAlias['type'] == 'local' ? $arrAlias['url'] : '',
            'ALIAS_LOCAL_PAGE_URL'        => $arrAlias['type'] == 'local' ? htmlentities($arrAlias['pageUrl'], ENT_QUOTES, CONTREXX_CHARSET) : '',
            'ALIAS_SELECT_URL_BOX'        => $arrAlias['type'] == 'url' ? 'block' : 'none',
            'ALIAS_URL_SOURCE'            => $arrAlias['type'] == 'url' ? htmlentities($arrAlias['url'], ENT_QUOTES, CONTREXX_CHARSET) : 'http://'
        ));

        $nr = 0;

        foreach ($arrAlias['sources'] as $arrAliasSource) {
            $this->_objTpl->setVariable(array(
                'ALIAS_DOMAIN_URL'        => 'http://'.$_CONFIG['domainUrl'].ASCMS_PATH_OFFSET.$langPathPrefix.'/',
                'ALIAS_ALIAS_ID'        => !empty($arrAliasSource['id']) ? $arrAliasSource['id'] : '',
                'ALIAS_ALIAS_NR'        => $nr++,
                'ALIAS_IS_DEFAULT'      => $arrAliasSource['isdefault'] == 1 ? 'checked' : '',
                'ALIAS_ALIAS_PREFIX'    => empty($arrAliasSource['id']) ? '_new' : '',
                'ALIAS_ALIAS_URL'        => stripslashes(htmlentities($arrAliasSource['url'], ENT_QUOTES, CONTREXX_CHARSET))
            ));
            $this->_objTpl->parse('alias_list');
        }
    }

    function _settings()
    {
        global $_ARRAYLANG;

        $this->_objTpl->loadTemplateFile('module_alias_settings.html');

        $apacheEnv = $this->objFWHtAccess->checkForApacheServer();
        $modRewriteLoaded = $this->objFWHtAccess->checkForModRewriteModul();

        $this->_objTpl->setVariable(array(
            'TXT_ALIAS_SETTINGS'                    => $_ARRAYLANG['TXT_ALIAS_SETTINGS'],
            'TXT_ALIAS_REQUIREMENTS_DESC'            => $_ARRAYLANG['TXT_ALIAS_REQUIREMENTS_DESC'],
            'TXT_ALIAS_SAVE'                        => $_ARRAYLANG['TXT_ALIAS_SAVE']
        ));

        $this->_objTpl->setVariable(array(
            'ALIAS_REQUIREMENTS_STATUS_MSG'    => ($apacheEnv && $modRewriteLoaded) ? $_ARRAYLANG['TXT_ALIAS_HTACCESS_HINT'] : ($apacheEnv ? $_ARRAYLANG['TXT_ALIAS_MOD_REWRITE_MISSING'] : $_ARRAYLANG['TXT_ALIAS_APACHE_MISSING']),

        ));

        $arrConfig = $this->_getConfig();

        if ($apacheEnv && $modRewriteLoaded) {
            $this->_objTpl->setVariable(array(
                'TXT_ALIAS_USE_ALIAS_ADMINISTRATION'    => $_ARRAYLANG['TXT_ALIAS_USE_ALIAS_ADMINISTRATION'],
                'ALIAS_STATUS_CHECKED'                    => $arrConfig['aliasStatus'] == '1' ? 'checked="checked"' : ''
            ));

            $this->_objTpl->parse('alias_status_form');
            if ($this->objSettings->isWritable()) {
                $this->_objTpl->parse('alias_status_form_submit');
            } else {
                $this->arrStatusMsg['error'] = array_merge($this->arrStatusMsg['error'], implode('<br />', $this->objSettings->strErrMessage));
                $this->_objTpl->hideBlock('alias_status_form_submit');
            }
        } else {
            $this->_objTpl->hideBlock('alias_status_form');
            $this->_objTpl->hideBlock('alias_status_form_submit');
        }
    }

    function _setAliasAdministrationStatus($active = false)
    {
        global $objDatabase, $_CONFIG;

        if ($active) {
            if (!$this->_activateRewriteEngine()) {
                return false;
            }
        } else {
            if (!$this->_deactivateRewriteEngine()) {
                return false;
            }
        }

        if ($objDatabase->Execute("UPDATE `".DBPREFIX."settings` SET `setvalue` = '".($active ? '1' : '0')."' WHERE `setname` = 'aliasStatus' AND `setmodule` = 41") !== false) {
            $_CONFIG['aliasStatus'] = $active;

            // updagte settins.php
            $this->objSettings->writeSettingsFile();

            // update sitemap.xml
            if ($_CONFIG['xmlSitemapStatus'] == 'on' && ($result = XMLSitemap::write()) !== true) {
                $this->arrStatusMsg['error'][] = $result;
            }

            return true;
        } else {
            return false;
        }
    }

    function _deletemap()
    {
   		global $_ARRAYLANG;

        $mappingId = !empty($_GET['id']) ? intval($_GET['id']) : 0;

        if ($mappingId) {
            if ($this->_deleteMapping($mappingId)) {
                $this->arrStatusMsg['ok'][] = $_ARRAYLANG['TXT_ALIAS_MAPPING_SUCCESSFULLY_REMOVED'];
            } else {
                $this->arrStatusMsg['error'][] = $_ARRAYLANG['TXT_ALIAS_MAPPING_REMOVE_FAILED'];
                $this->arrStatusMsg['error'][] = $_ARRAYLANG['TXT_ALIAS_RETRY_OPERATION'];
            }
        }
        $this->_writeHtAccessMappings();
        $this->_listMappings();
    }
    
    function _delete()
    {
        global $_ARRAYLANG;

        $aliasId = !empty($_GET['id']) ? intval($_GET['id']) : 0;

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
?>
