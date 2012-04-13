<?php
/**
 * Content Workflow
 * @copyright   CONTREXX CMS - COMVATION AG
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  core
 * @todo        Edit PHP DocBlocks!
 */

use Doctrine\Common\Util\Debug as DoctrineDebug;

require ASCMS_CORE_PATH.'/BackendTable.class.php';
require ASCMS_CORE_PATH.'/Module.class.php';
require ASCMS_CORE_PATH.'/routing/LanguageExtractor.class.php';
require_once 'JSONPage.class.php';

require_once ASCMS_CORE_PATH.'/Tree.class.php';
require_once ASCMS_CORE_PATH.'/'.'XMLSitemap.class.php';
require_once ASCMS_CORE_MODULE_PATH.'/cache/admin.class.php';

class ContentWorkflowException extends ModuleException {}

/**
 * Content Workflow
 *
 * Class for managing the content history
 * @copyright   CONTREXX CMS - COMVATION AG
 * @access      public
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  core
 * @todo        Edit PHP DocBlocks!
 */
class ContentWorkflow extends Module {
    private $strErrMessage = array();
    private $strPageTitle = '';
    private $strOkMessage = '';
    private $strCmd = '';
    private $intPos = 0;
    
    //doctrine entity manager
    protected $em = null;
    //the mysql connection
    protected $db = null;
    //the init object
    protected $init = null;
    
    protected $pageRepository = null;
    protected $logRepository = null;
    
    /**
    * Constructor
    *
    * @global     ADONewConnection
    * @global     HTML_Template_Sigma
    * @global     array        Core language
    * @global     array        Configuration
    */
    function __construct($act, $template, $db, $init) {
        global $_CONFIG;
        
        parent::__construct($act, $template);
        
        $this->strCmd     = $act;
        $this->defaultAct = 'showHistory';
        
        switch ($this->act) {
            case 'new':
            case 'updated':
            case 'deleted':
                $this->act = 'showHistory';
                break;
        }
        
        $this->em = Env::em();
        $this->db = $db;
        $this->pageRepository     = $this->em->getRepository('Cx\Model\ContentManager\Page');
        $this->logRepository = $this->em->getRepository('Gedmo\Loggable\Entity\LogEntry');
        
        $this->db->Execute('OPTIMIZE TABLE '.DBPREFIX.'ext_log_entries');
        
        if (isset($_GET['pos'])) {
            $this->intPos = intval($_GET['pos']);
        }
        
        if ($_CONFIG['contentHistoryStatus'] == 'off') {
            $this->strErrMessage[] = $_CORELANG['TXT_WORKFLOW_NOT_ACTIVE'];
        }
        
        $template->setVariable(array(
            'CONTENT_TITLE'             => $this->strPageTitle,
            'CONTENT_OK_MESSAGE'        => $this->strOkMessage,
            'CONTENT_STATUS_MESSAGE'    => implode("<br />\n", $this->strErrMessage)
        ));
        
        $this->setNavigation();
    }
    
    private function setNavigation()
    {
        global $objTemplate, $_CORELANG;

        $objTemplate->setVariable(  'CONTENT_NAVIGATION',
                                    '<a href="index.php?cmd=workflow&amp;act=new" class="'.($this->act == 'new' ? 'active' : '').'">'.$_CORELANG['TXT_NEW_PAGES'].'</a>
                                     <a href="index.php?cmd=workflow&amp;act=updated" class="'.($this->act == 'updated' ? 'active' : '').'">'.$_CORELANG['TXT_UPDATED_PAGES'].'</a>
                                     <a href="index.php?cmd=workflow&amp;act=deleted" class="'.($this->act == 'deleted' ? 'active' : '').'">'.$_CORELANG['TXT_DELETED_PAGES'].'</a>
                                     <a href="index.php?cmd=workflow&amp;act=unvalidated" class="'.($this->act == 'unvalidated' ? 'active' : '').'">'.$_CORELANG['TXT_WORKFLOW_VALIDATE'].'</a>
                                     <a href="index.php?cmd=workflow&amp;act=showClean" class="'.($this->act == 'showClean' ? 'active' : '').'">'.$_CORELANG['TXT_WORKFLOW_CLEAN_TITLE'].'</a>
                                ');
    }

    /**
    * Calls the requested function
    *
    * @global     HTML_Template_Sigma
    * @global     array        Core language
    */
    /*function getPage()
    {
        global $objTemplate, $_CORELANG;

        if(!isset($_GET['act'])){
            $_GET['act'] = '';
        }

        switch ($_GET['act']) {
            case 'restoreHistory':
                Permission::checkAccess(77, 'static');
                $intPageId = $this->loadHistory($_GET['hId'],true);
                $this->redirectPage($intPageId);
            break;
            case 'activateHistory':
                Permission::checkAccess(79, 'static');
                $intPageId = $this->loadHistory($_GET['hId']);
                $this->redirectPage($intPageId);
            break;
            case 'deleteHistory':
                Permission::checkAccess(80, 'static');
                $intPageId = $this->deleteHistory($_GET['hId']);
                $this->redirectPage($intPageId);
            break;
            case 'new':
                Permission::checkAccess(75, 'static');
                $this->showHistory();
            break;
            case 'updated':
                Permission::checkAccess(75, 'static');
                $this->showHistory('updated');
            break;
            case 'deleted':
                Permission::checkAccess(75, 'static');
                $this->showHistory('deleted');
            break;
            case 'validate':
                Permission::checkAccess(78, 'static');
                $this->validatePage($_GET['id'],$_GET['acc']);
                $this->showHistory('unvalidated');
            break;
            case 'showClean':
                Permission::checkAccess(126, 'static');
                $this->showClean();
                break;
            case 'cleanHistory':
                Permission::checkAccess(126, 'static');
                $this->cleanHistory($_GET['days']);
                $this->showClean();
                break;
            case 'unvalidated':
            default:
                Permission::checkAccess(75, 'static');
                $this->showHistory('unvalidated');

        }
    }*/

    /**
    * Show logfile-entries (new, updated or deleted)
    *
    * @global     HTML_Template_Sigma
    * @global     array        Core language
    * @global     array        Configuration
    */
    protected function showHistory() {
        global $objTemplate, $_CORELANG, $_CONFIG;
        
        \Permission::checkAccess(75, 'static');
        
        switch ($this->strCmd) {
            case 'updated':
                $this->strPageTitle = $_CORELANG['TXT_UPDATED_PAGES'];
                $strTitle           = $_CORELANG['TXT_UPDATED_PAGES'];
                $strPagingAct       = 'updated';
                break;
            case 'deleted':
                $this->strPageTitle = $_CORELANG['TXT_DELETED_PAGES'];
                $strTitle           = $_CORELANG['TXT_DELETED_PAGES'];
                $strPagingAct       = 'deleted';
                break;
            case 'unvalidated':
                $this->strPageTitle = $_CORELANG['TXT_WORKFLOW_VALIDATE'];
                $strTitle           = $_CORELANG['TXT_WORKFLOW_VALIDATE'];
                $strPagingAct       = 'unvalidated';
                break;
            default:
                $this->strPageTitle = $_CORELANG['TXT_NEW_PAGES'];
                $strTitle           = $_CORELANG['TXT_NEW_PAGES'];
                $strPagingAct       = 'new';
        }

        $objTemplate->addBlockfile('ADMIN_CONTENT', 'content_history', 'content_history.html');
        $objTemplate->setVariable(array(
            'TXT_TITLE'                 => $strTitle,
            'TXT_SUBTITLE_DATE'         => $_CORELANG['TXT_DATE'],
            'TXT_SUBTITLE_NAME'         => $_CORELANG['TXT_PAGETITLE'],
            'TXT_SUBTITLE_MODULE'       => $_CORELANG['TXT_MODULE'],
            'TXT_SUBTITLE_USER'         => $_CORELANG['TXT_USER'],
            'TXT_SUBTITLE_FUNCTIONS'    => $_CORELANG['TXT_FUNCTIONS'],
            'TXT_DELETED_RESTORE_JS'    => $_CORELANG['TXT_DELETED_RESTORE_JS']
        ));

    //THEMES
        $objResult = $this->db->Execute('SELECT id, themesname
                                         FROM '.DBPREFIX.'skins');
        $arrThemes[0] = $_CORELANG['TXT_STANDARD'];
        while (!$objResult->EOF) {
            $arrThemes[$objResult->fields['id']] = $objResult->fields['themesname'];
            $objResult->MoveNext();
        }
        
    //GROUPS
        $objResult = $this->db->Execute('SELECT group_id, group_name
                                         FROM '.DBPREFIX.'access_user_groups');
        $arrGroups[0] = '-';
        while (!$objResult->EOF) {
            $arrGroups[$objResult->fields['group_id']] = $objResult->fields['group_name'];
            $objResult->MoveNext();
        }
        
    //PAGES
        // Gets the quantity of log entries
        $countLogEntries = $this->logRepository->countLogEntries($this->strCmd);

        /** start paging **/
        $strPaging = getPaging($countLogEntries, $this->intPos, '&amp;cmd=workflow&amp;act='.$strPagingAct, '', true);
        $objTemplate->setVariable('HISTORY_PAGING', $strPaging);
        /** end paging **/
        
        // Gets the log entries
        $logs  = $this->logRepository->getLogs($this->strCmd, $this->intPos, $_CONFIG['corePagingLimit']);
        $pages = array();
        
        foreach ($logs as $log) {
            $page = $this->pageRepository->findOneById($log['objectId']);
            $data[$page->getId()] = array(
                'action'  => $log['action'],
                'version' => $log['version'],
                'page'    => $page,
            );
        }
        
        if (!empty($data)) {
            $intRowCount = 0;
            foreach ($data as $pageId => $data) {
                $act     = $data['action'];
                $history = $data['version'] - 1;
                $page    = $data['page'];
                
                $frontendGroups = '';
                $backendGroups  = '';
                $prefix         = '';

                if ($page->getFrontendAccessId() != 0) {
                    $objRS = $this->db->Execute('
                        SELECT group_id
                          FROM '.DBPREFIX.'access_group_dynamic_ids
                         WHERE access_id = '.$page->getFrontendAccessId());
                    while (!$objRS->EOF) {
                        $frontendGroups .= $arrGroups[$objRS->fields['group_id']].',';
                        $objRS->MoveNext();
                    }
                    $frontendGroups = substr($frontendGroups,0,strlen($frontendGroups)-1);
                } else {
                    $frontendGroups = $arrGroups[0];
                }
                
                if ($page->getBackendAccessId() != 0) {
                    $objRS = $this->db->Execute('
                        SELECT group_id
                          FROM '.DBPREFIX.'access_group_dynamic_ids
                         WHERE access_id = '.$page->getBackendAccessId());
                    while (!$objRS->EOF) {
                        $backendGroups .= $arrGroups[$objRS->fields['group_id']].',';
                        $objRS->MoveNext();
                    }
                    $backendGroups = substr($backendGroups,0,strlen($backendGroups)-1);
                } else {
                    $backendGroups = $arrGroups[0];
                }

                switch ($this->strCmd) {
                    case 'updated':
                        $s = $page->getModule();
                        $c = $page->getCmd();
                        $section = $s == '' || $s == '-' ? '' : '&amp;section='.$s;
                        $cmd = $c == '' ? '' : '&amp;cmd='.$c;
                        $strIcon = '<a href="../index.php?page='.$pageId.$section.$cmd.'&amp;history='.$history.'&amp;langId='.$page->getLang().'" target="_blank" title="'.$_CORELANG['TXT_WORKFLOW_PAGE_PREVIEW'].'"><img src="images/icons/details.gif" width="16" height="16" border="0" align="middle" alt="'.$_CORELANG['TXT_WORKFLOW_PAGE_PREVIEW'].'" /></a>';
                        break;
                    case 'deleted':
                        $strIcon = '<a href="javascript:restoreDeleted(\''.$pageId.'\');"><img src="images/icons/import.gif" alt="'.$_CORELANG['TXT_DELETED_RESTORE'].'" title="'.$_CORELANG['TXT_DELETED_RESTORE'].'" border="0" align="middle" /></a>';
                        break;
                    case 'unvalidated':
                        $strIcon = '<a href="'.CONTREXX_DIRECTORY_INDEX.'?cmd=workflow&amp;act=validate&amp;acc=1&amp;id='.$pageId.'"><img src="images/icons/thumb_up.gif" alt="'.$_CORELANG['TXT_WORKFLOW_VALIDATE_ACCEPT'].'" title="'.$_CORELANG['TXT_WORKFLOW_VALIDATE_ACCEPT'].'" border="0" align="middle" /></a>&nbsp;';
                        $strIcon .= '<a href="'.CONTREXX_DIRECTORY_INDEX.'?cmd=workflow&amp;act=validate&amp;acc=0&amp;id='.$pageId.'"><img src="images/icons/thumb_down.gif" alt="'.$_CORELANG['TXT_WORKFLOW_VALIDATE_DECLINE'].'" title="'.$_CORELANG['TXT_WORKFLOW_VALIDATE_DECLINE'].'" border="0" align="middle" /></a>&nbsp;';
                        $s = $page->getModule();
                        $c = $page->getCmd();
                        $section = $s == '' || $s == '-' ? '' : '&amp;section='.$s;
                        $cmd = $c == '' ? '' : '&amp;cmd='.$c;
                        $strIcon .= '<a href="../index.php?page='.$pageId.$section.$cmd.'&amp;history='.$history.'&amp;langId='.$page->getLang().'" target="_blank" title="'.$_CORELANG['TXT_WORKFLOW_PAGE_PREVIEW'].'"><img src="images/icons/details.gif" width="16" height="16" border="0" align="middle" alt="'.$_CORELANG['TXT_WORKFLOW_PAGE_PREVIEW'].'" /></a>';

                        switch ($act) {
                            case 'create':
                                $prefix = $_CORELANG['TXT_VALIDATE_PREFIX_NEW'].'&nbsp;';
                                break;
                            case 'remove':
                                $prefix = $_CORELANG['TXT_VALIDATE_PREFIX_DELETE'].'&nbsp;';
                                break;
                            default: // update
                                $prefix = $_CORELANG['TXT_VALIDATE_PREFIX_UPDATE'].'&nbsp;';
                        }

                        break;
                    default:
                        $strIcon = '<a href="'.CONTREXX_DIRECTORY_INDEX.'?cmd=content&amp;loadPage='.$pageId.'"><img src="images/icons/details.gif" alt="'.$_CORELANG['TXT_DETAILS'].'" title="'.$_CORELANG['TXT_DETAILS'].'" border="0" /></a>';
                }
                
                $objTemplate->setVariable(array(
                    'TXT_CONTENT_TITLE'         =>  $_CORELANG['TXT_PAGETITLE'],
                    'TXT_META_TITLE'            =>  $_CORELANG['TXT_META_TITLE'],
                    'TXT_META_DESCRIPTION'      =>  $_CORELANG['TXT_META_DESCRIPTION'],
                    'TXT_META_KEYWORD'          =>  $_CORELANG['TXT_META_KEYWORD'],
                    'TXT_CATEGORY'              =>  $_CORELANG['TXT_CATEGORY'],
                    'TXT_START_DATE'            =>  $_CORELANG['TXT_START_DATE'],
                    'TXT_END_DATE'              =>  $_CORELANG['TXT_END_DATE'],
                    'TXT_THEMES'                =>  $_CORELANG['TXT_THEMES'],
                    'TXT_OPTIONAL_CSS_NAME'     =>  $_CORELANG['TXT_CORE_CSSNAME'],
                    'TXT_MODULE'                =>  $_CORELANG['TXT_MODULE'],
                    'TXT_REDIRECT'              =>  $_CORELANG['TXT_REDIRECT'],
                    'TXT_SOURCE_MODE'           =>  $_CORELANG['TXT_SOURCE_MODE'],
                    'TXT_CACHING_STATUS'        =>  $_CORELANG['TXT_CACHING_STATUS'],
                    'TXT_FRONTEND'              =>  $_CORELANG['TXT_WEB_PAGES'],
                    'TXT_BACKEND'               =>  $_CORELANG['TXT_ADMINISTRATION_PAGES'],
                ));
                
                $objTemplate->setVariable(array(
                    'HISTORY_ROWCLASS'      =>  $intRowCount % 2 == 0 ? 'row0' : 'row1',
                    'HISTORY_IMGDETAILS'    =>  $strIcon,
                    'HISTORY_RID'           =>  $intRowCount,
                    'HISTORY_ID'            =>  $pageId,
                    'HISTORY_PID'           =>  $pageId,
                    'HISTORY_DATE'          =>  $page->getUpdatedAt()->format('d.m.Y H:i'),
                    'HISTORY_USER'          =>  $page->getUsername(),
                    'HISTORY_PREFIX'        =>  $prefix,
                    'HISTORY_TITLE'         =>  $page->getTitle(),
                    'HISTORY_CONTENT_TITLE' =>  $page->getContentTitle(),
                    'HISTORY_METATITLE'     =>  $page->getMetatitle(),
                    'HISTORY_METADESC'      =>  $page->getMetadesc(),
                    'HISTORY_METAKEY'       =>  $page->getMetakeys(),
                    'HISTORY_STARTDATE'     =>  $page->getStart()->format('d.m.Y H:i'),
                    'HISTORY_ENDDATE'       =>  $page->getEnd()->format('d.m.Y H:i'),
                    'HISTORY_THEME'         =>  $page->getSkin() != '' ? $arrThemes[$page->getSkin()] : $arrThemes[0],
                    'HISTORY_OPTIONAL_CSS'  =>  $page->getCssName() == '' ? '-' : $page->getCssName(),
                    'HISTORY_MODULE'        =>  $page->getModule().' '.$page->getCmd(),
                    'HISTORY_CMD'           =>  $page->getCmd() == ''    ? '-' : $page->getCmd(),
                    'HISTORY_SECTION'       =>  $page->getModule() == '' ? '-' : $page->getModule(),
                    'HISTORY_REDIRECT'      =>  $page->getTarget() == '' ? '-' : $page->getTarget(),
                    'HISTORY_SOURCEMODE'    =>  $page->getSourceMode() == 1 ? 'Y' : 'N',
                    'HISTORY_CACHING_STATUS'=>  $page->getCaching() == 1 ? 'Y' : 'N',
                    'HISTORY_FRONTEND'      =>  $frontendGroups,
                    'HISTORY_BACKEND'       =>  $backendGroups,
                    'HISTORY_CONTENT'       =>  $page->getContent(),
                ));

                $objTemplate->parse('showPages');
                $intRowCount++;
            }
        } else {
            $objTemplate->hideBlock('showPages');
        }
    }

    /**
    * Load a page from history
    *
    * @global    array      Core language
    * @global    array      Configuration
    * @param     integer    $intHistoryId: The entry with this id will be loaded
    * @param     boolean    $boolInsert: This parameter has to set to true, if the page was deleted before
    * @return    integer    $intPageId: The id of the page which was loaded
    */
    function loadHistory($intHistoryId, $boolInsert = false) {
        global $_CORELANG, $_CONFIG;

        $intHistoryId = intval($intHistoryId);

        if ($intHistoryId > 0) {
            $objResult = $objDatabase->Execute('SELECT  catid
                                                FROM    '.DBPREFIX.'content_navigation_history
                                                WHERE   id='.$intHistoryId.'
                                                LIMIT   1
                                            ');
            $objDatabase->Execute(' UPDATE  '.DBPREFIX.'content_navigation_history
                                    SET     is_active="0"
                                    WHERE   catid='.$objResult->fields['catid'].'
                                ');

            $objDatabase->Execute(' UPDATE  '.DBPREFIX.'content_navigation_history
                                    SET     is_active="1"
                                    WHERE   id='.$intHistoryId.'
                                    LIMIT   1
                                ');

            $objResult = $objDatabase->Execute('SELECT  *
                                                FROM    '.DBPREFIX.'content_navigation_history
                                                WHERE   id='.$intHistoryId.'
                                                LIMIT   1
                                            ');

            $objContentTree = new ContentTree();
            $arrCategory = $objContentTree->getThisNode($objResult->fields['parcat']);

            if (!is_array($arrCategory) && $boolInsert) {
                    $objSubResult = $objDatabase->Execute(' SELECT  catid
                                                            FROM    '.DBPREFIX.'content_navigation
                                                            WHERE   module=1 AND
                                                                    cmd="lost_and_found" AND
                                                                    lang='.$objResult->fields['lang'].'
                                                            LIMIT   1
                                                        ');
                    $intParcat = intval($objSubResult->fields['catid']);
                } else {
                    $intParcat = ($objResult->fields['parcat']);
                }

            if ($boolInsert) {
                //remove entry from logfile first
                $objDatabase->Execute(' DELETE
                                        FROM    '.DBPREFIX.'content_logfile
                                        WHERE   action="delete" AND
                                                history_id='.$intHistoryId.'
                                        LIMIT   1
                                    ');

                $objDatabase->Execute(' INSERT
                                        INTO    '.DBPREFIX.'content_navigation
                                        SET     catid='.$objResult->fields['catid'].',
                                                parcat='.$intParcat.',
                                                catname="'.addslashes($objResult->fields['catname']).'",
                                                target="'.addslashes($objResult->fields['target']).'",
                                                displayorder='.$objResult->fields['displayorder'].',
                                                displaystatus="'.$objResult->fields['displaystatus'].'",
                                                cachingstatus="'.$objResult->fields['cachingstatus'].'",
                                                username="'.addslashes($objResult->fields['username']).'",
                                                changelog='.time().',
                                                cmd="'.addslashes($objResult->fields['cmd']).'",
                                                lang='.$objResult->fields['lang'].',
                                                module='.$objResult->fields['module'].',
                                                startdate="'.$objResult->fields['startdate'].'",
                                                enddate="'.$objResult->fields['enddate'].'",
                                                protected='.$objResult->fields['protected'].',
                                                frontend_access_id='.$objResult->fields['frontend_access_id'].',
                                                backend_access_id='.$objResult->fields['backend_access_id'].',
                                                themes_id='.$objResult->fields['themes_id'].'
                                    ');

                $objResult = $objDatabase->Execute('SELECT  *
                                                    FROM    '.DBPREFIX.'content_history
                                                    WHERE   id='.$intHistoryId.'
                                                    LIMIT   1
                                                ');
                $objDatabase->Execute(' INSERT
                                        INTO    '.DBPREFIX.'content
                                        SET     id='.$objResult->fields['page_id'].',
                                                content="'.addslashes($objResult->fields['content']).'",
                                                title="'.addslashes($objResult->fields['title']).'",
                                                metatitle="'.addslashes($objResult->fields['metatitle']).'",
                                                metadesc="'.addslashes($objResult->fields['metadesc']).'",
                                                metakeys="'.addslashes($objResult->fields['metakeys']).'",
                                                metarobots="'.addslashes($objResult->fields['metarobots']).'",
                                                css_name="'.addslashes($objResult->fields['css_name']).'",
                                                redirect="'.addslashes($objResult->fields['redirect']).'",
                                                expertmode="'.addslashes($objResult->fields['expertmode']).'"
                                    ');
            } else {
                $objDatabase->Execute(' UPDATE  '.DBPREFIX.'content_navigation
                                        SET     parcat='.$intParcat.',
                                                catname="'.addslashes($objResult->fields['catname']).'",
                                                target="'.addslashes($objResult->fields['target']).'",
                                                displayorder='.$objResult->fields['displayorder'].',
                                                displaystatus="'.$objResult->fields['displaystatus'].'",
                                                cachingstatus="'.$objResult->fields['cachingstatus'].'",
                                                username="'.addslashes($objResult->fields['username']).'",
                                                changelog='.time().',
                                                cmd="'.addslashes($objResult->fields['cmd']).'",
                                                lang='.$objResult->fields['lang'].',
                                                module='.$objResult->fields['module'].',
                                                startdate="'.$objResult->fields['startdate'].'",
                                                enddate="'.$objResult->fields['enddate'].'",
                                                protected='.$objResult->fields['protected'].',
                                                frontend_access_id='.$objResult->fields['frontend_access_id'].',
                                                backend_access_id='.$objResult->fields['backend_access_id'].',
                                                themes_id='.$objResult->fields['themes_id'].'
                                        WHERE   catid='.$objResult->fields['catid'].'
                                        LIMIT   1
                                    ');

                $objResult = $objDatabase->Execute('SELECT  *
                                                    FROM    '.DBPREFIX.'content_history
                                                    WHERE   id='.$intHistoryId.'
                                                    LIMIT   1
                                                ');
                $objDatabase->Execute(' UPDATE  '.DBPREFIX.'content
                                        SET     content="'.addslashes($objResult->fields['content']).'",
                                                title="'.addslashes($objResult->fields['title']).'",
                                                metatitle="'.addslashes($objResult->fields['metatitle']).'",
                                                metadesc="'.addslashes($objResult->fields['metadesc']).'",
                                                metakeys="'.addslashes($objResult->fields['metakeys']).'",
                                                metarobots="'.addslashes($objResult->fields['metarobots']).'",
                                                css_name="'.addslashes($objResult->fields['css_name']).'",
                                                redirect="'.addslashes($objResult->fields['redirect']).'",
                                                expertmode="'.addslashes($objResult->fields['expertmode']).'"
                                        WHERE   id='.$objResult->fields['page_id'].'
                                        LIMIT   1
                                    ');
            }

            //write caching-file if enabled
            $objCache = new Cache();
            $objCache->writeCacheablePagesFile();

            //write xml sitemap
            if (($result = XMLSitemap::write()) !== true) {
                $this->strErrMessage[] = $result;
            }

            $this->strOkMessage = $_CORELANG['TXT_HISTORY_RESTORED'];
        }

        return intval($objResult->fields['page_id']);
    }

    /**
    * Delete an history entry
    *
    * @global   ADONewConnection
    * @global     array        Core language
    * @param    integer      $intHistoryId    The entry with this id will be deleted
    * @return   integer       $intPageId       The history entry deleted belonged to this pageid
    */
    function deleteHistory($intHistoryId) {
        global $objDatabase, $_CORELANG;

        $intHistoryId = intval($intHistoryId);

        if ($intHistoryId > 0) {
            $objResult = $objDatabase->Execute('SELECT  is_active,
                                                        catid
                                                FROM    '.DBPREFIX.'content_navigation_history
                                                WHERE   id='.$intHistoryId.'
                                                LIMIT   1
                                            ');
            if (intval($objResult->fields['is_active']) != 1) {
                $objDatabase->Execute(' DELETE
                                        FROM    '.DBPREFIX.'content_logfile
                                        WHERE   history_id='.$intHistoryId.' AND
                                                (action="update" OR
                                                action="new")
                                        LIMIT   1
                                    ');
                $objDatabase->Execute(' DELETE
                                        FROM    '.DBPREFIX.'content_navigation_history
                                        WHERE   id='.$intHistoryId.'
                                        LIMIT   1
                                    ');
                $objDatabase->Execute(' DELETE
                                        FROM    '.DBPREFIX.'content_history
                                        WHERE   id='.$intHistoryId.'
                                        LIMIT   1
                                    ');

                $this->strOkMessage = $_CORELANG['TXT_HISTORY_DELETE_DONE'];
            } else {
                //this history-entry is currently active, don't allow to delete
                $this->strErrMessage[] = $_CORELANG['TXT_HISTORY_DELETE_ACTIVE'];
            }
        }

        return intval($objResult->fields['catid']);
    }


    /**
    * Redirect to content manager (open site)
    *
    * @param    integer     The page with this id will be shown in content manager
    */
    function redirectPage($intPageId) {
        CSRF::header('location:index.php?cmd=content&act=edit&pageId='.intval($intPageId));
        exit;
    }

    /**
    * Validate an incoming page
    *
    * @global   ADONewConnection
    * @global     array        Core language
    * @param    integer      $intHistoryId       The entry with this id will be validated
    * @return   integer       $intValidateStatus  1 -> accept page, 0 -> decline page
    */
    function validatePage($intLogfileId,$intValidateStatus) {
        global $objDatabase, $_CORELANG;

        $intLogfileId = intval($intLogfileId);
        $intValidateStatus = intval($intValidateStatus);

        if ($intLogfileId != 0) {
            $objResult = $objDatabase->Execute('SELECT  action,
                                                        history_id
                                                FROM    '.DBPREFIX.'content_logfile
                                                WHERE   id='.$intLogfileId.' AND
                                                        is_validated="0"
                                                LIMIT   1
                                            ');

            $intHistoryId = $objResult->fields['history_id'];
            $strAction = $objResult->fields['action'];

            $objResult = $objDatabase->Execute('SELECT  page_id
                                                FROM    '.DBPREFIX.'content_history
                                                WHERE   id = '.$intHistoryId.'
                                                LIMIT   1
                                            ');
            $row = $objResult->FetchRow();
            $intPageId = $row['page_id'];

            switch ($strAction) {
                case 'new':
                    if ($intValidateStatus == 1) {
                        $objDatabase->Execute(' UPDATE  '.DBPREFIX.'content_logfile
                                                SET     is_validated="1"
                                                WHERE   id='.$intLogfileId.'
                                                LIMIT   1
                                            ');
                        $objDatabase->Execute(' UPDATE  '.DBPREFIX.'content_navigation_history
                                                SET     changelog='.time().'
                                                WHERE   id='.$intHistoryId.' AND
                                                        catid='.$intPageId.'
                                                LIMIT   1
                                            ');
                        $objDatabase->Execute(' UPDATE  '.DBPREFIX.'content_navigation
                                                SET     is_validated="1",
                                                        activestatus="1",
                                                        changelog='.time().'
                                                WHERE   catid='.$intPageId.'
                                                LIMIT   1
                                            ');
                        $this->strOkMessage = $_CORELANG['TXT_WORKFLOW_VALIDATE_NEW_ACCEPT'];
                    } else {
                        $objDatabase->Execute(' DELETE
                                                FROM    '.DBPREFIX.'content_logfile
                                                WHERE   id='.$intLogfileId.'
                                                LIMIT   1
                                            ');
                        $objDatabase->Execute(' DELETE
                                                FROM    '.DBPREFIX.'content_navigation_history
                                                WHERE   id='.$intHistoryId.'
                                                LIMIT   1
                                            ');
                        $objDatabase->Execute(' DELETE
                                                FROM    '.DBPREFIX.'content_history
                                                WHERE   id='.$intHistoryId.'
                                                LIMIT   1
                                            ');
                        $objDatabase->Execute(' DELETE
                                                FROM    '.DBPREFIX.'content
                                                WHERE   id='.$intPageId.'
                                                LIMIT   1
                                            ');
                        $objDatabase->Execute(' DELETE
                                                FROM    '.DBPREFIX.'content_navigation
                                                WHERE   catid='.$intPageId.'
                                                LIMIT   1
                                            ');
                        $this->strOkMessage = $_CORELANG['TXT_WORKFLOW_VALIDATE_NEW_DECLINE'];
                    }
                break;
                case 'delete':
                    if ($intValidateStatus == 1) {
                        //really delete page
                        $objDatabase->Execute(' UPDATE  '.DBPREFIX.'content_logfile
                                                SET     is_validated="1"
                                                WHERE   id='.$intLogfileId.'
                                                LIMIT   1
                                            ');
                        $objDatabase->Execute(' DELETE
                                                FROM    '.DBPREFIX.'content
                                                WHERE   id='.$intPageId.'
                                                LIMIT   1
                                            ');
                        $objDatabase->Execute(' DELETE
                                                FROM    '.DBPREFIX.'content_navigation
                                                WHERE   catid='.$intPageId.'
                                                LIMIT   1
                                            ');
                        $this->strOkMessage = $_CORELANG['TXT_DATA_RECORD_DELETED_SUCCESSFUL'];
                    } else {
                        //decline delete
                        $objDatabase->Execute(' DELETE
                                                FROM    '.DBPREFIX.'content_logfile
                                                WHERE   id='.$intLogfileId.'
                                                LIMIT   1
                                            ');
                        $this->strOkMessage = $_CORELANG['TXT_WORKFLOW_VALIDATE_DELETE_DECLINE'];
                    }
                break;
                default: //update
                    if ($intValidateStatus == 1) {
                        //allow update
                        $this->loadHistory($intHistoryId,false);
                        $objDatabase->Execute(' UPDATE  '.DBPREFIX.'content_logfile
                                                SET     is_validated="1"
                                                WHERE   id='.$intLogfileId.'
                                                LIMIT   1
                                            ');
                        $this->strOkMessage = $_CORELANG['TXT_WORKFLOW_VALIDATE_UPDATE_ACCEPT'];
                    } else {
                        //decline update
                        $objDatabase->Execute(' DELETE
                                                FROM    '.DBPREFIX.'content_logfile
                                                WHERE   id='.$intLogfileId.'
                                                LIMIT   1
                                            ');
                        $objDatabase->Execute(' DELETE
                                                FROM    '.DBPREFIX.'content_navigation_history
                                                WHERE   id='.$intHistoryId.'
                                                LIMIT   1
                                            ');
                        $objDatabase->Execute(' DELETE
                                                FROM    '.DBPREFIX.'content_history
                                                WHERE   id='.$intHistoryId.'
                                                LIMIT   1
                                            ');
                        $this->strOkMessage = $_CORELANG['TXT_WORKFLOW_VALIDATE_UPDATE_DECLINE'];
                    }
            }

            //write caching-file if enabled
            $objCache = new Cache();
            $objCache->writeCacheablePagesFile();

            //write xml sitemap
            if (($result = XMLSitemap::write()) !== true) {
                $this->strErrMessage[] = $result;
            }
        }
    }


    /**
    * Show logfile-entries (new, updated or deleted)
    *
    * @global     HTML_Template_Sigma
    * @global     ADONewConnection
    * @global     array        Core language
    */
    function showClean() {
        global $objTemplate, $objDatabase, $_CORELANG;

        $this->strPageTitle = $_CORELANG['TXT_WORKFLOW_CLEAN_TITLE'];
        $objTemplate->addBlockfile('ADMIN_CONTENT', 'content_history_clean', 'content_history_clean.html');
        $objTemplate->setVariable(array(
            'TXT_HISTORY_CLEAN_TITLE'       => $_CORELANG['TXT_WORKFLOW_CLEAN_TITLE'],
            'TXT_HISTORY_CLEAN_DESCRIPTION' => $_CORELANG['TXT_WORKFLOW_CLEAN_DESCRIPTION'],
            'TXT_HISTORY_CLEAN_OCCUPIED'    => $_CORELANG['TXT_WORKFLOW_CLEAN_OCCUPIED'],
            'TXT_HISTORY_CLEAN_MIN_AGE'     => $_CORELANG['TXT_WORKFLOW_CLEAN_MINIMUM_AGE'],
            'TXT_HISTORY_CLEAN_DAYS'        => $_CORELANG['TXT_WORKFLOW_CLEAN_DAYS'],
            'TXT_HISTORY_CLEAN_SUBMIT'      => $_CORELANG['TXT_EXECUTE'],
            'TXT_HISTORY_CLEAN_JS_CONFIRM'  => $_CORELANG['TXT_WORKFLOW_CLEAN_CONFIRM']
        ));

        //Figure out how much space is occupied by the workflow
        $intBytes = 0;

        $objResult = $objDatabase->Execute('SHOW TABLE STATUS LIKE "'.DBPREFIX.'content_history";');
        $intBytes += $objResult->fields['Data_length'];
        $intBytes += $objResult->fields['Index_length'];

        $objResult = $objDatabase->Execute('SHOW TABLE STATUS LIKE "'.DBPREFIX.'content_navigation_history";');
        $intBytes += $objResult->fields['Data_length'];
        $intBytes += $objResult->fields['Index_length'];

        $objResult = $objDatabase->Execute('SHOW TABLE STATUS LIKE "'.DBPREFIX.'content_logfile";');
        $intBytes += $objResult->fields['Data_length'];
        $intBytes += $objResult->fields['Index_length'];

        $objTemplate->setVariable('HISTORY_CLEAN_OCCUPIED', round($intBytes / 1024, 2));

        //Get old values
        $intDays = (isset($_GET['days'])) ? intval($_GET['days']) : 30;
        $intDays = ($intDays < 1) ? 1 : $intDays;

        $objTemplate->setVariable('HISTORY_CLEAN_DAYS', $intDays);
    }

    /**
     * Removes old workflow entries which are older than $intNumberOfDays.
     *
     * @global     ADONewConnection
     * @global     array
     * @param    integer        $intNumberOfDays: Entries older than this value will be removed
     */
    function cleanHistory($intNumberOfDays) {
        global $objDatabase, $_CORELANG;

        $intNumberOfDays = intval($intNumberOfDays);
        if ($intNumberOfDays < 1) {
            $intNumberOfDays = 1;
        }

        $intTimeStamp = time()-($intNumberOfDays*24*60*60);

        //Look for deleted pages older than XX days
        $objResult = $objDatabase->Execute('SELECT      log.history_id as id
                                            FROM        '.DBPREFIX.'content_logfile AS log
                                            INNER JOIN  '.DBPREFIX.'content_navigation_history AS nav
                                            ON          log.history_id = nav.id
                                            WHERE       log.action="delete" And
                                                        nav.changelog < '.$intTimeStamp);

        while (!$objResult->EOF) {
            $objDatabase->Execute(' DELETE
                                    FROM    '.DBPREFIX.'content_logfile
                                    WHERE    history_id='.$objResult->fields['id']);

            $objDatabase->Execute(' DELETE
                                    FROM    '.DBPREFIX.'content_navigation_history
                                    WHERE    id='.$objResult->fields['id'].'
                                    LIMIT    1');

            $objDatabase->Execute(' DELETE
                                    FROM    '.DBPREFIX.'content_history
                                    WHERE    id='.$objResult->fields['id'].'
                                    LIMIT    1');

            $objResult->MoveNext();
        }

        //Look for not active entries older than XX days
        $objResult = $objDatabase->Execute('SELECT  id
                                            FROM    '.DBPREFIX.'content_navigation_history
                                            WHERE   is_active="0" AND
                                                    changelog < '.$intTimeStamp);

        while (!$objResult->EOF) {
            $objDatabase->Execute(' DELETE
                                    FROM    '.DBPREFIX.'content_logfile
                                    WHERE   history_id='.$objResult->fields['id'].' AND
                                            (action="update" OR action="new")');

            $objDatabase->Execute(' DELETE
                                    FROM    '.DBPREFIX.'content_navigation_history
                                    WHERE    id='.$objResult->fields['id'].'
                                    LIMIT    1');

            $objDatabase->Execute(' DELETE
                                    FROM    '.DBPREFIX.'content_history
                                    WHERE    id='.$objResult->fields['id'].'
                                    LIMIT    1');

            $objResult->MoveNext();
        }

        $this->strOkMessage = str_replace('[DAYS]', $intNumberOfDays, $_CORELANG['TXT_WORKFLOW_CLEAN_SUCCESS']);
    }
}
?>
