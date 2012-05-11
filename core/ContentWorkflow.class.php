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
    private $pageId = 0;
    private $strCmd = '';
    private $intPos = 0;
    
    //doctrine entity manager
    protected $em = null;
    //template object
    protected $tpl = null;
    //the mysql connection
    protected $db = null;
    //the init object
    protected $init = null;
    
    protected $nodeRepo = null;
    protected $pageRepo = null;
    protected $logRepo  = null;
    
    /**
    * Constructor
    *
    * @param     ADONewConnection
    * @param     HTML_Template_Sigma
    * @param     string    $act
    * @param     object    $init
    * @global    array     Configuration
    */
    function __construct($act, $template, $db, $init) {
        global $_CONFIG;
        
        parent::__construct($act, $template);
        
        $this->pageId     = isset($_GET['hId']) ? intval($_GET['hId']) : 0;
        $this->cmd        = $act;
        $this->defaultAct = 'showHistory';
        
        switch ($this->act) {
            case 'new':
            case 'updated':
            case 'deleted':
            case 'unvalidated':
                $this->act = 'showHistory';
                break;
        }
        
        $this->em = Env::em();
        $this->tpl = $template;
        $this->db = $db;
        $this->nodeRepo = $this->em->getRepository('Cx\Model\ContentManager\Node');
        $this->pageRepo = $this->em->getRepository('Cx\Model\ContentManager\Page');
        $this->logRepo  = $this->em->getRepository('Gedmo\Loggable\Entity\LogEntry');
        
        if (isset($_GET['pos'])) {
            $this->intPos = intval($_GET['pos']);
        }
        
        $this->tpl->setVariable(array(
            'CONTENT_TITLE'             => $this->strPageTitle,
            'CONTENT_OK_MESSAGE'        => $this->strOkMessage,
            'CONTENT_STATUS_MESSAGE'    => implode("<br />\n", $this->strErrMessage)
        ));
        
        $this->setNavigation();
    }
    
    /**
     * Sets the content workflow navigation
     * 
     * @global    HTML_Template_Sigma
     * @global    array    Core language
     */
    protected function setNavigation() {
        global $_CORELANG;
        
        $this->tpl->setVariable(
            'CONTENT_NAVIGATION',
            '<a href="index.php?cmd=workflow&amp;act=new" class="'.($this->cmd == 'new' ? 'active' : '').'">'.$_CORELANG['TXT_NEW_PAGES'].'</a>
             <a href="index.php?cmd=workflow&amp;act=updated" class="'.($this->cmd == 'updated' ? 'active' : '').'">'.$_CORELANG['TXT_UPDATED_PAGES'].'</a>
             <a href="index.php?cmd=workflow&amp;act=deleted" class="'.($this->cmd == 'deleted' ? 'active' : '').'">'.$_CORELANG['TXT_DELETED_PAGES'].'</a>
             <a href="index.php?cmd=workflow&amp;act=unvalidated" class="'.($this->cmd == 'unvalidated' ? 'active' : '').'">'.$_CORELANG['TXT_WORKFLOW_VALIDATE'].'</a>'
             //<a href="index.php?cmd=workflow&amp;act=showClean" class="'.($this->act == 'showClean' ? 'active' : '').'">'.$_CORELANG['TXT_WORKFLOW_CLEAN_TITLE'].'</a>
        );
    }
    
    /**
    * Show logfile-entries (new, updated or deleted)
    *
    * @global     HTML_Template_Sigma
    * @global     array        Core language
    * @global     array        Configuration
    */
    protected function showHistory() {
        global $_CORELANG, $_CONFIG;
        
        \Permission::checkAccess(75, 'static');
        
        switch ($this->cmd) {
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

        if ($this->cmd == 'deleted') {
            $template = 'content_history_deleted_pages.html';
        } else {
            $template = 'content_history.html';
        }

        $this->tpl->addBlockfile('ADMIN_CONTENT', 'content_history', $template);
        $this->tpl->setVariable(array(
            'TXT_TITLE'                 => $strTitle,
            'TXT_SUBTITLE_DATE'         => $_CORELANG['TXT_DATE'],
            'TXT_SUBTITLE_TRANSLATION'  => $_CORELANG['TXT_TRANSLATION'],
            'TXT_SUBTITLE_NAME'         => $_CORELANG['TXT_PAGETITLE'],
            'TXT_SUBTITLE_LANGUAGE'     => $_CORELANG['TXT_LANGUAGE'],
            'TXT_SUBTITLE_MODULE'       => $_CORELANG['TXT_MODULE'],
            'TXT_SUBTITLE_USER'         => $_CORELANG['TXT_USER'],
            'TXT_SUBTITLE_FUNCTIONS'    => $_CORELANG['TXT_FUNCTIONS'],
            'TXT_DELETED_RESTORE_JS'    => $_CORELANG['TXT_DELETED_RESTORE_JS']
        ));

        // Themes
        $objResult = $this->db->Execute('SELECT id, themesname
                                         FROM '.DBPREFIX.'skins');
        $arrThemes[0] = $_CORELANG['TXT_STANDARD'];
        while (!$objResult->EOF) {
            $arrThemes[$objResult->fields['id']] = $objResult->fields['themesname'];
            $objResult->MoveNext();
        }
        
        // Groups
        $objResult = $this->db->Execute('SELECT group_id, group_name
                                         FROM '.DBPREFIX.'access_user_groups');
        $arrGroups[0] = '-';
        while (!$objResult->EOF) {
            $arrGroups[$objResult->fields['group_id']] = $objResult->fields['group_name'];
            $objResult->MoveNext();
        }
        
        // Gets the quantity of log entries
        $countLogEntries = $this->logRepo->countLogEntries($this->cmd);
        
        // Paging
        $strPaging = getPaging($countLogEntries, $this->intPos, '&amp;cmd=workflow&amp;act='.$strPagingAct, '', true);
        $this->tpl->setVariable('HISTORY_PAGING', $strPaging);
        
        // Gets the log entries
        $logs  = $this->logRepo->getLogs($this->cmd, $this->intPos, $_CONFIG['corePagingLimit']);
        $pages = array();
        
        foreach ($logs as $log) {
            if ($log['action'] == 'remove') {
                $page = new \Cx\Model\ContentManager\Page();
                $page->setId($log['objectId']);
                $this->logRepo->revert($page, $log['version'] - 1);
            } else {
                $page = $this->pageRepo->findOneById($log['objectId']);
            }
            $data[$page->getId()] = array(
                'action'  => $log['action'],
                'version' => $log['version'],
                'updated' => $log['loggedAt'],
                'user'    => $log['username'],
                'page'    => $page,
            );
        }
        
        if (!empty($data)) {
            $intRowCount = 0;
            $le = new \Cx\Core\Routing\LanguageExtractor($this->db, DBPREFIX);
            
            foreach ($data as $pageId => $data) {
                $act     = $data['action'];
                $history = $data['version'] - 1;
                $updated = $data['updated'];
                $user    = json_decode($data['user']);
                $page    = $data['page'];
                
                $frontendGroups = '';
                $backendGroups  = '';
                $prefix         = '';
                
                // Only for new, updated and unvalidated pages
                if ($this->cmd  != 'deleted') {
                    $langDir     = $le->getShortNameOfLanguage($page->getLang());
                    $path        = $langDir.'/'.$this->pageRepo->getPath($page);
                    $historyLink = ASCMS_PATH_OFFSET.'/'.$path.'?history='.$history;
                }

                if ($page->getFrontendAccessId() != 0) {
                    $objRS = $this->db->Execute('
                        SELECT group_id
                          FROM '.DBPREFIX.'access_group_dynamic_ids
                         WHERE access_id = '.$page->getFrontendAccessId());
                    while (!$objRS->EOF) {
                        $frontendGroups .= $arrGroups[$objRS->fields['group_id']].',';
                        $objRS->MoveNext();
                    }
                    $frontendGroups = substr($frontendGroups, 0, strlen($frontendGroups)-1);
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
                    $backendGroups = substr($backendGroups, 0, strlen($backendGroups)-1);
                } else {
                    $backendGroups = $arrGroups[0];
                }

                switch ($this->cmd) {
                    case 'updated':
                        //$strIcon = '<a href="'.$historyLink.'" target="_blank" title="'.$_CORELANG['TXT_WORKFLOW_PAGE_PREVIEW'].'"><img src="images/icons/details.gif" width="16" height="16" border="0" align="middle" alt="'.$_CORELANG['TXT_WORKFLOW_PAGE_PREVIEW'].'" /></a>';
                        $strIcon = '<a href="'.CONTREXX_DIRECTORY_INDEX.'?cmd=content&amp;loadPage='.$pageId.'"><img src="images/icons/details.gif" alt="'.$_CORELANG['TXT_DETAILS'].'" title="'.$_CORELANG['TXT_DETAILS'].'" border="0" /></a>';
                        break;
                    case 'deleted':
                        $strIcon = '<a href="javascript:restoreDeleted(\''.$pageId.'\');"><img src="images/icons/import.gif" alt="'.$_CORELANG['TXT_DELETED_RESTORE'].'" title="'.$_CORELANG['TXT_DELETED_RESTORE'].'" border="0" align="middle" /></a>';
                        break;
                    case 'unvalidated':
                        //$strIcon  = '<a href="'.CONTREXX_DIRECTORY_INDEX.'?cmd=workflow&amp;act=validate&amp;acc=1&amp;id='.$pageId.'"><img src="images/icons/thumb_up.gif" alt="'.$_CORELANG['TXT_WORKFLOW_VALIDATE_ACCEPT'].'" title="'.$_CORELANG['TXT_WORKFLOW_VALIDATE_ACCEPT'].'" border="0" align="middle" /></a>&nbsp;';
                        //$strIcon .= '<a href="'.CONTREXX_DIRECTORY_INDEX.'?cmd=workflow&amp;act=validate&amp;acc=0&amp;id='.$pageId.'"><img src="images/icons/thumb_down.gif" alt="'.$_CORELANG['TXT_WORKFLOW_VALIDATE_DECLINE'].'" title="'.$_CORELANG['TXT_WORKFLOW_VALIDATE_DECLINE'].'" border="0" align="middle" /></a>&nbsp;';
                        //$strIcon .= '<a href="'.$historyLink.'" target="_blank" title="'.$_CORELANG['TXT_WORKFLOW_PAGE_PREVIEW'].'"><img src="images/icons/details.gif" width="16" height="16" border="0" align="middle" alt="'.$_CORELANG['TXT_WORKFLOW_PAGE_PREVIEW'].'" /></a>';
                        $strIcon = '<a href="'.CONTREXX_DIRECTORY_INDEX.'?cmd=content&amp;loadPage='.$pageId.'"><img src="images/icons/details.gif" alt="'.$_CORELANG['TXT_DETAILS'].'" title="'.$_CORELANG['TXT_DETAILS'].'" border="0" /></a>';
                        
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
                    default: // new
                        $strIcon = '<a href="'.CONTREXX_DIRECTORY_INDEX.'?cmd=content&amp;loadPage='.$pageId.'"><img src="images/icons/details.gif" alt="'.$_CORELANG['TXT_DETAILS'].'" title="'.$_CORELANG['TXT_DETAILS'].'" border="0" /></a>';
                }
                
                $this->tpl->setVariable(array(
                    'TXT_CONTENT_TITLE'         => $_CORELANG['TXT_PAGETITLE'],
                    'TXT_META_TITLE'            => $_CORELANG['TXT_META_TITLE'],
                    'TXT_META_DESCRIPTION'      => $_CORELANG['TXT_META_DESCRIPTION'],
                    'TXT_META_KEYWORD'          => $_CORELANG['TXT_META_KEYWORD'],
                    'TXT_CATEGORY'              => $_CORELANG['TXT_CATEGORY'],
                    'TXT_START_DATE'            => $_CORELANG['TXT_START_DATE'],
                    'TXT_END_DATE'              => $_CORELANG['TXT_END_DATE'],
                    'TXT_THEMES'                => $_CORELANG['TXT_THEMES'],
                    'TXT_OPTIONAL_CSS_NAME'     => $_CORELANG['TXT_CORE_CSSNAME'],
                    'TXT_MODULE'                => $_CORELANG['TXT_MODULE'],
                    'TXT_REDIRECT'              => $_CORELANG['TXT_REDIRECT'],
                    'TXT_SOURCE_MODE'           => $_CORELANG['TXT_SOURCE_MODE'],
                    'TXT_CACHING_STATUS'        => $_CORELANG['TXT_CACHING_STATUS'],
                    'TXT_FRONTEND'              => $_CORELANG['TXT_WEB_PAGES'],
                    'TXT_BACKEND'               => $_CORELANG['TXT_ADMINISTRATION_PAGES'],
                ));
                
                $this->tpl->setVariable(array(
                    'HISTORY_ROWCLASS'      => $intRowCount % 2 == 0 ? 'row0' : 'row1',
                    'HISTORY_IMGDETAILS'    => $strIcon,
                    'HISTORY_RID'           => $intRowCount,
                    'HISTORY_ID'            => $pageId,
                    'HISTORY_PID'           => $pageId,
                    'HISTORY_DATE'          => $updated,
                    'HISTORY_TRANSLATION'   => implode('&nbsp;&nbsp;', $this->pageRepo->getPageTranslations($pageId, $history)),
                    'HISTORY_LANGUAGE'      => \FWLanguage::getLanguageCodeById($page->getLang()),
                    'HISTORY_USER'          => $user->{'name'},
                    'HISTORY_PREFIX'        => $prefix,
                    'HISTORY_TITLE'         => $page->getTitle(),
                    'HISTORY_CONTENT_TITLE' => $page->getContentTitle(),
                    'HISTORY_METATITLE'     => $page->getMetatitle(),
                    'HISTORY_METADESC'      => $page->getMetadesc(),
                    'HISTORY_METAKEY'       => $page->getMetakeys(),
                    'HISTORY_STARTDATE'     => $page->getStart()->format('d.m.Y H:i'),
                    'HISTORY_ENDDATE'       => $page->getEnd()->format('d.m.Y H:i'),
                    'HISTORY_THEME'         => $page->getSkin() != '' ? $arrThemes[$page->getSkin()] : $arrThemes[0],
                    'HISTORY_OPTIONAL_CSS'  => $page->getCssName() == '' ? '-' : $page->getCssName(),
                    'HISTORY_MODULE'        => $page->getModule().' '.$page->getCmd(),
                    'HISTORY_CMD'           => $page->getCmd() == ''    ? '-' : $page->getCmd(),
                    'HISTORY_SECTION'       => $page->getModule() == '' ? '-' : $page->getModule(),
                    'HISTORY_REDIRECT'      => $page->getTarget() == '' ? '-' : $page->getTarget(),
                    'HISTORY_SOURCEMODE'    => $page->getSourceMode() == 1 ? 'Y' : 'N',
                    'HISTORY_CACHING_STATUS'=> $page->getCaching() == 1 ? 'Y' : 'N',
                    'HISTORY_FRONTEND'      => $frontendGroups,
                    'HISTORY_BACKEND'       => $backendGroups,
                    'HISTORY_CONTENT'       => $page->getContent(),
                ));

                $this->tpl->parse('showPages');
                $intRowCount++;
            }
        } else {
            $this->tpl->hideBlock('showPages');
        }
    }
    
    /**
     *  Restores a page from histroy.
     */
    protected function restoreHistory() {
        \Permission::checkAccess(77, 'static');
        
        $node = new \Cx\Model\ContentManager\Node();
        $node->setParent($this->nodeRepo->getRoot());
        $this->em->persist($node);
        
        $currentPage = new \Cx\Model\ContentManager\Page();
        $currentPage->setId($this->pageId);
        $logs = $this->logRepo->getLogEntries($currentPage);
        $this->logRepo->revert($currentPage, $logs[1]->getVersion());
        $nodeIdShadowed = $currentPage->getNodeIdShadowed();
        $currentPage->setNode($node);
        $this->em->persist($currentPage);
        $this->em->flush();
        
        // Delete 'remove' log
        $this->em->remove($logs[0]);
        $this->em->flush();
        unset($logs[0]);
        
        foreach ($logs as $log) {
            $log->setObjectId($currentPage->getId());
            $this->em->persist($log);
            $this->em->flush();
        }
        
        // Delete old log entries
        $logs = $this->logRepo->findByObjectId($this->pageId);
        foreach ($logs as $log) {
            $this->em->remove($log);
            $this->em->flush();
        }
        
        $currentPage->setNodeIdShadowed($node->getId());
        $this->em->persist($currentPage);
        $this->em->flush();
        
        $logsRemove = $this->logRepo->getLogsByAction('remove');
        foreach ($logsRemove as $logRemove) {
            $page = new \Cx\Model\ContentManager\Page();
            $page->setId($logRemove->getObjectId());
            $logs = $this->logRepo->getLogEntries($page);
            $this->logRepo->revert($page, $logRemove->getVersion() - 1);
            if ($page->getNodeIdShadowed() == $nodeIdShadowed) {
                $page->setNode($node);
                $this->em->persist($page);
                $this->em->flush();
                
                // Delete 'remove' log
                $this->em->remove($logs[0]);
                $this->em->flush();
                unset($logs[0]);
                
                foreach ($logs as $log) {
                    $log->setObjectId($page->getId());
                    $this->em->persist($log);
                    $this->em->flush();
                }
                
                // Delete old log entries
                $logs = $this->logRepo->findByObjectId($logRemove->getObjectId());
                foreach ($logs as $log) {
                    $this->em->remove($log);
                    $this->em->flush();
                }
                
                $page->setNodeIdShadowed($node->getId());
                $this->em->persist($page);
                $this->em->flush();
            }
        }
        
        $this->redirectPage($currentPage->getId());
    }

    /**
     * Redirect to content manager (open site)
     *
     * @param  integer  The page with this id will be shown in content manager.
     */
    protected function redirectPage($intPageId) {
        CSRF::header('location: index.php?cmd=content&loadPage='.$intPageId);
        exit;
    }

}
?>
