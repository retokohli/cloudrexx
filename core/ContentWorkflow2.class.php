<?php
/**
 * Content Workflow
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Kaelin <thomas.kaelin@astalvista.ch>
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
 * @author      Thomas Kaelin <thomas.kaelin@astalvista.ch>
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
    private $intPos = 0;
    
    //doctrine entity manager
    protected $em = null;
    //the mysql connection
    protected $db = null;
    //the init object
    protected $init = null;

    protected $pageRepository = null;
    protected $nodeRepository = null;
    
    /**
    * Constructor
    *
    * @global     ADONewConnection
    * @global     HTML_Template_Sigma
    * @global     array        Core language
    * @global     array        Configuration
    */
    function __construct($act, $template, $db, $init) {
        /*global $objDatabase, $objTemplate, $_CORELANG, $_CONFIG;*/
        global $_CONFIG;
        
        parent::__construct($act, $template);
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
        /*$this->init = $init;
        $this->pageRepository = $this->em->getRepository('Cx\Model\ContentManager\Page');
        $this->nodeRepository = $this->em->getRepository('Cx\Model\ContentManager\Node');*/
        
        /*$objDatabase->Execute('DELETE FROM  '.DBPREFIX.'content_logfile WHERE history_id=0');
        $objDatabase->Execute('OPTIMIZE TABLE '.DBPREFIX.'content_logfile');
        $objDatabase->Execute('OPTIMIZE TABLE '.DBPREFIX.'content_history');
        $objDatabase->Execute('OPTIMIZE TABLE '.DBPREFIX.'content_navigation_history');*/
        
        if(isset($_GET['pos'])) {
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
        
        switch ($this->act) {
            case 'updated':
                $this->strPageTitle = $_CORELANG['TXT_UPDATED_PAGES'];
                $strTitle           = $_CORELANG['TXT_UPDATED_PAGES'];
                $strPagingAct       = 'updated';
                $strQueryWhere      = "WHERE l.action = 'update'";
                break;
            case 'deleted':
                $this->strPageTitle = $_CORELANG['TXT_DELETED_PAGES'];
                $strTitle           = $_CORELANG['TXT_DELETED_PAGES'];
                $strPagingAct       = 'deleted';
                $strQueryWhere      = "WHERE l.action = 'remove'";
                break;
            case 'unvalidated':
                $this->strPageTitle = $_CORELANG['TXT_WORKFLOW_VALIDATE'];
                $strTitle           = $_CORELANG['TXT_WORKFLOW_VALIDATE'];
                $strPagingAct       = 'unvalidated';
                $strQueryWhere      = "";
                break;
            default:
                $this->strPageTitle = $_CORELANG['TXT_NEW_PAGES'];
                $strTitle           = $_CORELANG['TXT_NEW_PAGES'];
                $strPagingAct       = 'new';
                $strQueryWhere      = "WHERE l.action = 'create'";
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
        $query = $this->em->createQuery("
            SELECT count(p.id)
            FROM Cx\Model\ContentManager\Page p
            WHERE p.editingStatus = ''
        ");
        $count = $query->getSingleScalarResult();

        /** start paging **/
        $strPaging = getPaging($count, $this->intPos, '&amp;cmd=workflow&amp;act='.$strPagingAct, '', true);
        $objTemplate->setVariable('HISTORY_PAGING', $strPaging);
        /** end paging **/

        $query = $this->em->createQuery("
            SELECT l.id, l.objectId, l.action
            FROM Gedmo\Loggable\Entity\LogEntry l
            ".$strQueryWhere."
            ORDER BY l.id DESC
        ");
        $query->setFirstResult($this->intPos);
        $query->setMaxResults($_CONFIG['corePagingLimit']);
        $logs = $query->getResult();
        
        foreach ($logs as $log) {
            $arrHistory[$log['id']] = array(
                                          'id'     => $log['objectId'],
                                          'action' => $log['action']
                                      );
        }
        
        if (count($arrHistory) > 0) {
            $intRowCount = 0;
            foreach ($arrHistory as $intHistoryId => $arrInner) {
                $intLogfileId   = $arrInner['id'];
                $strQueryAction = $arrInner['action'];
                
                $query = $this->em->createQuery("
                    SELECT p.id,
                           p.title,
                           p.username,
                           p.updatedAt,
                           p.start,
                           p.end,
                           p.caching,
                           p.skin,
                           p.cmd,
                           p.module,
                           p.frontendAccessId,
                           p.backendAccessId,
                           p.lang,
                           p.contentTitle,
                           p.metatitle,
                           p.metadesc,
                           p.metakeys,
                           p.content,
                           p.cssName,
                           p.target,
                           p.sourceMode
                    FROM Cx\Model\ContentManager\Page p
                    WHERE p.id = ".$intHistoryId."
                ");
                $query->setMaxResults(1);
                $page = $query->getResult();
                
                if (!empty($page)) {
                    $page = $page[0];
                } else {
                    continue;
                }
                
                /*$objResult = $this->db->SelectLimit('SELECT  navTable.id                 AS navID,
                                                                navTable.catid              AS navPageId,
                                                                navTable.catname            AS navCatname,
                                                                navTable.username           AS navUsername,
                                                                navTable.changelog          AS navChangelog,
                                                                navTable.startdate          AS navStartdate,
                                                                navTable.enddate            AS navEnddate,
                                                                navTable.cachingstatus      AS navCachingStatus,
                                                                navTable.themes_id          AS navTheme,
                                                                navTable.cmd                AS navCMD,
                                                                navTable.module             AS navModule,
                                                                navTable.frontend_access_id AS navFAccess,
                                                                navTable.backend_access_id  AS navBAccess,
                                                                navTable.lang               AS navLang,
                                                                conTable.title              AS conTitle,
                                                                conTable.metatitle          AS conMetaTitle,
                                                                conTable.metadesc           AS conMetaDesc,
                                                                conTable.metakeys           AS conMetaKeywords,
                                                                conTable.content            AS conContent,
                                                                conTable.css_name           AS conCssName,
                                                                conTable.redirect           AS conRedirect,
                                                                conTable.expertmode         AS conExpertMode
                                                    FROM        '.DBPREFIX.'content_navigation_history AS navTable
                                                    INNER JOIN  '.DBPREFIX.'content_history AS conTable
                                                    ON          conTable.id = navTable.id
                                                    WHERE       navTable.id = '.$intHistoryId, 1);*/
                $strBackendGroups  = '';
                $strFrontendGroups = '';
                $strPrefix         = '';

                if (!empty($page['id'])) {
                    $boolPageExists = true;
                } else {
                    $boolPageExists = false;
                }

                if ($page['backendAccessId'] != 0) {
                    $objRS = $this->db->Execute('
                        SELECT group_id
                          FROM '.DBPREFIX.'access_group_dynamic_ids
                         WHERE access_id = '.$page['backendAccessId']);
                    while (!$objRS->EOF) {
                        $strBackendGroups .= $arrGroups[$objRS->fields['group_id']].',';
                        $objRS->MoveNext();
                    }
                    $strBackendGroups = substr($strBackendGroups,0,strlen($strBackendGroups)-1);
                } else {
                    $strBackendGroups = $arrGroups[0];
                }

                if ($page['frontendAccessId'] != 0) {
                    $objRS = $this->db->Execute('
                        SELECT group_id
                          FROM '.DBPREFIX.'access_group_dynamic_ids
                         WHERE access_id = '.$page['frontendAccessId']);
                    while (!$objRS->EOF) {
                        $strFrontendGroups .= $arrGroups[$objRS->fields['group_id']].',';
                        $objRS->MoveNext();
                    }
                    $strFrontendGroups = substr($strFrontendGroups,0,strlen($strFrontendGroups)-1);
                } else {
                    $strFrontendGroups = $arrGroups[0];
                }

                switch ($this->act) {
                    case 'updated':
                        if(!$boolPageExists) {
                            $strIcon = '<img src="images/icons/empty.gif" alt="'.$_CORELANG['TXT_HISTORY_DELETED'].'" title="'.$_CORELANG['TXT_HISTORY_DELETED'].'" border="0" />';
                        } else {
                            $s = $page['module'];
                            $c = $page['cmd'];
                            $section = $s == '' || $s == '-' ? '' : '&amp;section='.$s;
                            $cmd = $c == '' ? '' : '&amp;cmd='.$c;
                            $strIcon = '<a href="../index.php?page='.$page['id'].$section.$cmd.'&amp;history='.$intHistoryId.'&amp;langId='.$page['lang'].'" target="_blank" title="'.$_CORELANG['TXT_WORKFLOW_PAGE_PREVIEW'].'"><img src="images/icons/details.gif" width="16" height="16" border="0" align="middle" alt="'.$_CORELANG['TXT_WORKFLOW_PAGE_PREVIEW'].'" /></a>';
                        }
                        break;
                    case 'deleted':
                        $strIcon = '<a href="javascript:restoreDeleted(\''.$page['id'].'\');"><img src="images/icons/import.gif" alt="'.$_CORELANG['TXT_DELETED_RESTORE'].'" title="'.$_CORELANG['TXT_DELETED_RESTORE'].'" border="0" align="middle" /></a>';
                        break;
                    case 'unvalidated':
                        $strIcon = '<a href="'.CONTREXX_DIRECTORY_INDEX.'?cmd=workflow&amp;act=validate&amp;acc=1&amp;id='.$intLogfileId.'"><img src="images/icons/thumb_up.gif" alt="'.$_CORELANG['TXT_WORKFLOW_VALIDATE_ACCEPT'].'" title="'.$_CORELANG['TXT_WORKFLOW_VALIDATE_ACCEPT'].'" border="0" align="middle" /></a>&nbsp;';
                        $strIcon .= '<a href="'.CONTREXX_DIRECTORY_INDEX.'?cmd=workflow&amp;act=validate&amp;acc=0&amp;id='.$intLogfileId.'"><img src="images/icons/thumb_down.gif" alt="'.$_CORELANG['TXT_WORKFLOW_VALIDATE_DECLINE'].'" title="'.$_CORELANG['TXT_WORKFLOW_VALIDATE_DECLINE'].'" border="0" align="middle" /></a>&nbsp;';
                        $s = $page['module'];
                        $c = $page['cmd'];
                        $section = $s == '' || $s == '-' ? '' : '&amp;section='.$s;
                        $cmd = $c == '' ? '' : '&amp;cmd='.$c;
                        $strIcon .= '<a href="../index.php?page='.$page['id'].$section.$cmd.'&amp;history='.$intHistoryId.'&amp;langId='.$page['lang'].'" target="_blank" title="'.$_CORELANG['TXT_WORKFLOW_PAGE_PREVIEW'].'"><img src="images/icons/details.gif" width="16" height="16" border="0" align="middle" alt="'.$_CORELANG['TXT_WORKFLOW_PAGE_PREVIEW'].'" /></a>';

                        switch ($strQueryAction) {
                            case 'create':
                                $strPrefix = $_CORELANG['TXT_VALIDATE_PREFIX_NEW'].'&nbsp;';
                            break;
                            case 'remove':
                                $strPrefix = $_CORELANG['TXT_VALIDATE_PREFIX_DELETE'].'&nbsp;';
                            break;
                            default: //update
                                $strPrefix = $_CORELANG['TXT_VALIDATE_PREFIX_UPDATE'].'&nbsp;';
                        }

                        break;
                    default:
                        if(!$boolPageExists) {
                            $strIcon = '<img src="images/icons/empty.gif" alt="'.$_CORELANG['TXT_HISTORY_DELETED'].'" title="'.$_CORELANG['TXT_HISTORY_DELETED'].'" border="0" />';
                        } else {
                            $strIcon = '<a href="'.CONTREXX_DIRECTORY_INDEX.'?cmd=content&amp;loadPage='.$page['id'].'"><img src="images/icons/details.gif" alt="'.$_CORELANG['TXT_DETAILS'].'" title="'.$_CORELANG['TXT_DETAILS'].'" border="0" /></a>';
                        }
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
                    'HISTORY_ID'            =>  $page['id'],
                    'HISTORY_PID'           =>  $page['id'],
                    'HISTORY_DATE'          =>  $page['updatedAt'],
                    'HISTORY_USER'          =>  $page['username'],
                    'HISTORY_PREFIX'        =>  $strPrefix,
                    'HISTORY_TITLE'         =>  stripslashes($page['title']),
                    'HISTORY_CONTENT_TITLE' =>  stripslashes($page['contentTitle']),
                    'HISTORY_METATITLE'     =>  stripslashes($page['metatitle']),
                    'HISTORY_METADESC'      =>  stripslashes($page['metadesc']),
                    'HISTORY_METAKEY'       =>  stripslashes($page['metakeys']),
                    'HISTORY_STARTDATE'     =>  $page['start'],
                    'HISTORY_ENDDATE'       =>  $page['end'],
                    'HISTORY_THEME'         =>  $page['skin'] != '' ? stripslashes($arrThemes[$page['skin']]) : stripslashes($arrThemes[0]),
                    'HISTORY_OPTIONAL_CSS'  =>  empty($page['cssName']) ? '-' : stripslashes($page['cssName']),
                    'HISTORY_MODULE'        =>  $page['module'].' '.$page['cmd'],
                    'HISTORY_CMD'           =>  empty($page['cmd']) ? '-' : $page['cmd'],
                    'HISTORY_SECTION'       =>  $page['module'],
                    'HISTORY_REDIRECT'      =>  empty($page['target']) ? '-' : $page['target'],
                    'HISTORY_SOURCEMODE'    =>  $page['sourceMode'] == 1 ? 'Y' : 'N',
                    'HISTORY_CACHING_STATUS'=>  $page['caching'] == 1 ? 'Y' : 'N',
                    'HISTORY_FRONTEND'      =>  stripslashes($strFrontendGroups),
                    'HISTORY_BACKEND'       =>  stripslashes($strBackendGroups),
                    'HISTORY_CONTENT'       =>  stripslashes(htmlspecialchars($page['content'], ENT_QUOTES, CONTREXX_CHARSET)),
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
    * @global   ADONewConnection
    * @global     array        Core language
    * @global     array        Configuration
    * @param    integer      $intHistoryId: The entry with this id will be loaded
    * @param     boolean        $boolInsert: This parameter has to set to true, if the page was deleted before
    * @return   integer       $intPageId: The id of the page which was loaded
    */
    function loadHistory($intHistoryId,$boolInsert=false) {
        global $objDatabase, $_CORELANG, $_CONFIG;

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
