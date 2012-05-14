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
        
        $this->pageId     = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $this->cmd        = $act;
        $this->defaultAct = 'showHistory';
        
        switch ($this->act) {
            case 'new':
            case 'updated':
            case 'unvalidated':
                $this->act = 'showHistory';
                break;
            case 'deleted':
                $this->act = 'showHistoryDeleted';
                break;
        }
        
        $this->em = \Env::em();
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
            '<a href="index.php?cmd=workflow&amp;act=new" class="'.($this->cmd == 'new' || $this->cmd == '' ? 'active' : '').'">'.$_CORELANG['TXT_NEW_PAGES'].'</a>
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

        $this->tpl->addBlockfile('ADMIN_CONTENT', 'content_history', 'content_history.html');
        
        switch ($this->cmd) {
            case 'updated':
                $this->strPageTitle = $_CORELANG['TXT_UPDATED_PAGES'];
                $strPagingAct       = 'updated';
                break;
            case 'unvalidated':
                $this->strPageTitle = $_CORELANG['TXT_WORKFLOW_VALIDATE'];
                $strPagingAct       = 'unvalidated';
                break;
            default:
                $this->strPageTitle = $_CORELANG['TXT_NEW_PAGES'];
                $strPagingAct       = 'new';
        }
        
        $this->setTextVariables();
        
        // Gets the quantity of log entries
        $countLogEntries = $this->logRepo->countLogEntries($this->cmd);
        
        // Paging
        $strPaging = getPaging($countLogEntries, $this->intPos, '&amp;cmd=workflow&amp;act='.$strPagingAct, '', true);
        $this->tpl->setVariable('HISTORY_PAGING', $strPaging);
        
        // Gets the log entries
        $logs  = $this->logRepo->getLogs($this->cmd, $this->intPos, $_CONFIG['corePagingLimit']);
        
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
                'user'    => json_decode($log['username']),
                'page'    => $page,
            );
        }
        
        if (!empty($data)) {
            $intRowCount = 0;
            $le = new \Cx\Core\Routing\LanguageExtractor($this->db, DBPREFIX);
            
            foreach ($data as $pageId => $data) {
                $act      = $data['action'];
                $history  = $data['version'] - 1;
                $updated  = $data['updated'];
                $username = $data['user']->{'name'};
                $page     = $data['page'];
                $type     = $this->getTypeByPage($page);
                $prefix   = '';
                
                // Only for new, updated and unvalidated pages
                if ($this->cmd  != 'deleted') {
                    $langDir     = $le->getShortNameOfLanguage($page->getLang());
                    $path        = $langDir.'/'.$this->pageRepo->getPath($page);
                    $historyLink = ASCMS_PATH_OFFSET.'/'.$path.'?history='.$history;
                }

                switch ($this->cmd) {
                    case 'deleted':
                        $strIcon = '<a href="javascript:restoreDeleted(\''.$pageId.'\');"><img src="images/icons/import.gif" alt="'.$_CORELANG['TXT_DELETED_RESTORE'].'" title="'.$_CORELANG['TXT_DELETED_RESTORE'].'" border="0" align="middle" /></a>';
                        break;
                    case 'unvalidated':
                        $strIcon = '<a href="'.CONTREXX_DIRECTORY_INDEX.'?cmd=content&amp;loadPage='.$pageId.'#page_content" target="_blank"><img src="images/icons/details.gif" alt="'.$_CORELANG['TXT_DETAILS'].'" title="'.$_CORELANG['TXT_DETAILS'].'" border="0" /></a>';
                        
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
                        $strIcon  = '<a href="../'.$page->getSlug().'" target="_blank"><img src="images/icons/details.gif" alt="'.$_CORELANG['TXT_WORKFLOW_PAGE_PREVIEW'].'" title="'.$_CORELANG['TXT_WORKFLOW_PAGE_PREVIEW'].'" border="0" /></a>';
                        $strIcon .= '<a href="'.CONTREXX_DIRECTORY_INDEX.'?cmd=content&amp;loadPage='.$pageId.'#page_content" target="_blank"><img src="images/icons/edit.gif" alt="'.$_CORELANG['TXT_EDIT_PAGE'].'" title="'.$_CORELANG['TXT_EDIT_PAGE'].'" border="0" /></a>';
                }
                
                $this->tpl->setVariable(array(
                    'HISTORY_ROWCLASS'              => $intRowCount % 2 == 0 ? 'row0' : 'row1',
                    'HISTORY_IMGDETAILS'            => $strIcon,
                    'HISTORY_RID'                   => $intRowCount,
                    'HISTORY_DATE'                  => $updated,
                    'HISTORY_LANGUAGE'              => \FWLanguage::getLanguageCodeById($page->getLang()),
                    'HISTORY_TYPE'                  => $type,
                    'HISTORY_USER'                  => $username,
                    'HISTORY_PREFIX'                => $prefix,
                    'HISTORY_TITLE'                 => $page->getTitle(),
                    'HISTORY_STARTDATE'             => $page->getStart() ? $page->getStart()->format('d.m.Y H:i') : '',
                    'HISTORY_ENDDATE'               => $page->getEnd() ? $page->getEnd()->format('d.m.Y H:i') : '',
                    'HISTORY_SLUG'                  => $page->getSlug(),
                ));

                $this->tpl->parse('page_row');
                $intRowCount++;
            }
        } else {
            $this->tpl->hideBlock('page_row');
        }
    }

    protected function showHistoryDeleted() {
        global $_CORELANG, $_CONFIG;
        
        \Permission::checkAccess(75, 'static');
        
        $this->tpl->addBlockfile('ADMIN_CONTENT', 'content_history', 'content_history_deleted.html');
        $this->strPageTitle = $_CORELANG['TXT_DELETED_PAGES'];
        $this->setTextVariables($_CORELANG['TXT_DELETED_PAGES']);
        
        // Gets the quantity of log entries
        $countLogEntries = $this->logRepo->countLogEntries('deleted');
        
        // Paging
        $strPaging = getPaging($countLogEntries, $this->intPos, '&amp;cmd=workflow&amp;act=deleted', '', true);
        $this->tpl->setVariable('HISTORY_PAGING', $strPaging);
        
        // Gets the log entries
        $logsByNodeId  = $this->logRepo->getLogs('deleted', $this->intPos, $_CONFIG['corePagingLimit']);
        $dataByNodeId  = array();
        
        foreach ($logsByNodeId as $nodeId => $logsByLang) {
            $dataByLang = array();
            
            foreach ($logsByLang as $lang => $log) {
                $page = new \Cx\Model\ContentManager\Page();
                $page->setId($log['objectId']);
                $this->logRepo->revert($page, $log['version'] - 1);
                
                $dataByLang[$lang] = array(
                    'version' => $log['version'],
                    'updated' => $log['loggedAt'],
                    'user'    => json_decode($log['username']),
                    'page'    => $page,
                );
            }
            
            $dataByNodeId[$nodeId] = $dataByLang;
        }
        
        if (!empty($dataByNodeId)) {
            $intRowCount = 0;
            foreach ($dataByNodeId as $dataByLang) {
                if (!empty($dataByLang)) {
                    ksort($dataByLang);
                    $style = '';
                    
                    foreach ($dataByLang as $data) {
                        $history  = $data['version'] - 1;
                        $updated  = $data['updated'];
                        $username = $data['user']->{'name'};
                        $page     = $data['page'];
                        $pageId   = $page->getId();
                        $langCode = \FWLanguage::getLanguageCodeById($page->getLang());
                        $type     = $this->getTypeByPage($page);

                        $translationLinks = '';
                        $translations = $this->pageRepo->getPageTranslations($pageId, $history);
                        foreach ($translations as $translation) {
                            if ($translation == $langCode) {
                                $translationLinks .= $translation.'&nbsp;&nbsp;';
                            } else {
                                $translationLinks .= '<a href="#" onclick="$J(\'tr.'.$page->getNodeIdShadowed().'\').hide(); $J(\'tr.'.$page->getNodeIdShadowed().'.'.$translation.'\').show(); return false;">'.$translation.'</a>&nbsp;&nbsp;';
                            }
                        }

                        $this->tpl->setVariable(array(
                            'HISTORY_ROW'                   => $intRowCount % 2 == 0 ? 'row1' : 'row2',
                            'HISTORY_CLASSES'               => $page->getNodeIdShadowed().' '.$langCode,
                            'HISTORY_STYLE'                 => $style,
                            'HISTORY_IMGDETAILS'            => '<a href="javascript:restoreDeleted(\''.$pageId.'\');"><img src="images/icons/import.gif" alt="'.$_CORELANG['TXT_DELETED_RESTORE'].'" title="'.$_CORELANG['TXT_DELETED_RESTORE'].'" border="0" align="middle" /></a>',
                            'HISTORY_RID'                   => $intRowCount,
                            'HISTORY_DATE'                  => $updated,
                            'HISTORY_TRANSLATION'           => $translationLinks,
                            'HISTORY_LANGUAGE'              => \FWLanguage::getLanguageCodeById($page->getLang()),
                            'HISTORY_TYPE'                  => $type,
                            'HISTORY_USER'                  => $username,
                            'HISTORY_TITLE'                 => $page->getTitle(),
                            'HISTORY_STARTDATE'             => $page->getStart() ? $page->getStart()->format('d.m.Y H:i') : '',
                            'HISTORY_ENDDATE'               => $page->getEnd() ? $page->getEnd()->format('d.m.Y H:i') : '',
                            'HISTORY_SLUG'                  => $page->getSlug(),
                        ));
                        
                        $this->tpl->parse('page_row');
                        $style = 'style="display: none;"';
                    }
                    
                    $intRowCount++;
                }
            }
        } else {
            $this->tpl->hideBlock('node_row');
        }
    }

    private function setTextVariables() {
        global $_CORELANG;
        
        $this->tpl->setVariable(array(
            'TXT_TITLE'                 => $this->strPageTitle,
            'TXT_DATE'                  => $_CORELANG['TXT_DATE'],
            'TXT_NAVIGATION_TITLE'      => $_CORELANG['TXT_NAVIGATION_TITLE'],
            'TXT_CONTENT_TITLE'         => $_CORELANG['TXT_PAGETITLE'],
            'TXT_LANGUAGE'              => $_CORELANG['TXT_LANGUAGE'],
            'TXT_PUBLICATION_PERIOD'    => $_CORELANG['TXT_PUBLICATION_PERIOD'],
            'TXT_TYPE'                  => $_CORELANG['TXT_TYPE'],
            'TXT_ACCESS_PROTECTION'     => $_CORELANG['TXT_ACCESS_PROTECTION'],
            'TXT_SLUG'                  => $_CORELANG['TXT_CORE_CM_SLUG'],
            'TXT_USER'                  => $_CORELANG['TXT_USER'],
            'TXT_FUNCTIONS'             => $_CORELANG['TXT_FUNCTIONS'],
            'TXT_DELETED_RESTORE_JS'    => $_CORELANG['TXT_DELETED_RESTORE_JS'],
        ));
    }
    
    private function getTypeByPage($page) {
        global $_CORELANG;
        
        switch ($page->getType()) {
            case 'redirect':
                $criteria = array(
                    'nodeIdShadowed' => $page->getTargetNodeId(),
                    'lang'           => $page->getLang(),
                );
                $target = $this->pageRepo->findOneBy($criteria)->getTitle();
                $type  = $_CORELANG['TXT_CORE_CM_TYPE_REDIRECT'].': ';
                $type .= $target;
                break;
            case 'application':
                $type  = $_CORELANG['TXT_CORE_CM_TYPE_APPLICATION'].': ';
                $type .= $page->getModule();
                $type .= $page->getCmd() != '' ? ' | '.$page->getCmd() : '';
                break;
            case 'fallback':
                $fallbackLangId = \FWLanguage::getFallbackLanguageIdById($page->getLang());
                if ($fallbackLangId == 0) {
                    $fallbackLangId = \FWLanguage::getDefaultLangId();
                }
                $type  = $_CORELANG['TXT_CORE_CM_TYPE_FALLBACK'].' ';
                $type .= \FWLanguage::getLanguageCodeById($fallbackLangId);
                break;
            default:
                $type = $_CORELANG['TXT_CORE_CM_TYPE_CONTENT'];
        }
        
        return $type;
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
        \CSRF::header('location: index.php?cmd=content&loadPage='.$intPageId.'#page_content');
        exit;
    }

}
?>
