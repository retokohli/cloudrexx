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

/**
 * Includes
 */
require_once ASCMS_CORE_PATH.'/Tree.class.php';
require_once ASCMS_CORE_PATH.'/'.'XMLSitemap.class.php';
require_once ASCMS_CORE_MODULE_PATH.'/cache/admin.class.php';

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
class ContentWorkflow
{
    var $strPageTitle;
    var $strErrMessage = array();
    var $strOkMessage = '';
    var $strWarningMessage = '';

    /**
    * Constructor
    * @global     ADONewConnection
    * @global     HTML_Template_Sigma
    * @global     array        Core language
    * @global     array        Configuration
    */
    function __construct()
    {
        global $objDatabase,$objTemplate,$_CORELANG,$_CONFIG;

        $objDatabase->Execute('DELETE FROM  '.DBPREFIX.'content_logfile WHERE history_id=0');
        $objDatabase->Execute('OPTIMIZE TABLE '.DBPREFIX.'content_logfile');
        $objDatabase->Execute('OPTIMIZE TABLE '.DBPREFIX.'content_history');
        $objDatabase->Execute('OPTIMIZE TABLE '.DBPREFIX.'content_navigation_history');

        $objTemplate->setVariable(  'CONTENT_NAVIGATION',
                                    '<a href="index.php?cmd=workflow&amp;act=new">'.$_CORELANG['TXT_NEW_PAGES'].'</a>
                                     <a href="index.php?cmd=workflow&amp;act=updated">'.$_CORELANG['TXT_UPDATED_PAGES'].'</a>
                                     <a href="index.php?cmd=workflow&amp;act=deleted">'.$_CORELANG['TXT_DELETED_PAGES'].'</a>
                                     <a href="index.php?cmd=workflow&amp;act=unvalidated">'.$_CORELANG['TXT_WORKFLOW_VALIDATE'].'</a>
                                     <a href="index.php?cmd=workflow&amp;act=showClean">'.$_CORELANG['TXT_WORKFLOW_CLEAN_TITLE'].'</a>
                                ');

        if ($_CONFIG['contentHistoryStatus'] == 'off') {
            $this->strErrMessage[] = $_CORELANG['TXT_WORKFLOW_NOT_ACTIVE'];
        }
    }


    /**
     * Calls the requested page function
     * @global     HTML_Template_Sigma
     * @global     array        Core language
     */
    function getPage()
    {
        global $objTemplate, $_CORELANG;

        if(!isset($_GET['act'])){
            $_GET['act'] = '';
        }

        switch ($_GET['act']) {
            //perm 76 is for validating pages
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
                $this->validatePage($_GET['id'],$_GET['acc'],$_GET['prompt']);
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

        $objTemplate->setVariable(array(
            'CONTENT_TITLE'             => $this->strPageTitle,
            'CONTENT_OK_MESSAGE'        => $this->strOkMessage,
            'CONTENT_WARNING_MESSAGE'   => $this->strWarningMessage,
            'CONTENT_STATUS_MESSAGE'    => implode("<br />\n", $this->strErrMessage)
        ));
    }


    /**
     * Show logfile entries (new, updated or deleted)
     * @global     HTML_Template_Sigma
     * @global     ADONewConnection
     * @global     array        Core language
     * @global     array        Configuration
     */
    function showHistory($strAction='new')
    {
        global $objTemplate,$objDatabase,$_CORELANG,$_CONFIG;

        switch ($strAction) {
            case 'updated':
                $this->strPageTitle = $_CORELANG['TXT_UPDATED_PAGES'];
                $strTitle           = $_CORELANG['TXT_UPDATED_PAGES'];
                $strPagingAct       = 'updated';
                $strQueryWhere      = 'WHERE action="update" AND is_validated="1"';
            break;
            case 'deleted':
                $this->strPageTitle = $_CORELANG['TXT_DELETED_PAGES'];
                $strTitle           = $_CORELANG['TXT_DELETED_PAGES'];
                $strPagingAct       = 'deleted';
                $strQueryWhere      = 'WHERE action="delete" AND is_validated="1"';
            break;
            case 'unvalidated':
                $this->strPageTitle = $_CORELANG['TXT_WORKFLOW_VALIDATE'];
                $strTitle           = $_CORELANG['TXT_WORKFLOW_VALIDATE'];
                $strPagingAct       = 'unvalidated';
                $strQueryWhere      = 'WHERE is_validated="0"';
            break;
            default:
                $this->strPageTitle = $_CORELANG['TXT_NEW_PAGES'];
                $strTitle           = $_CORELANG['TXT_NEW_PAGES'];
                $strPagingAct       = 'new';
                $strQueryWhere      = 'WHERE action="new" AND is_validated="1"';
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
        $objResult = $objDatabase->Execute('SELECT  id,
                                                    themesname
                                            FROM    '.DBPREFIX.'skins
                                            ');
        $arrThemes[0] = $_CORELANG['TXT_STANDARD'];
        while (!$objResult->EOF) {
            $arrThemes[$objResult->fields['id']] = $objResult->fields['themesname'];
            $objResult->MoveNext();
        }
    //MODULES
        $objResult = $objDatabase->Execute('SELECT  id,
                                                    name
                                            FROM    '.DBPREFIX.'modules
                                        ');
        while (!$objResult->EOF) {
            $arrModules[$objResult->fields['id']] = $objResult->fields['name'];
            $objResult->MoveNext();
        }
        $arrModules[0] = '-';
    //GROUPS
        $objResult = $objDatabase->Execute('SELECT  group_id,
                                                    group_name
                                            FROM    '.DBPREFIX.'access_user_groups
                                        ');
        $arrGroups[0] = '-';
        while (!$objResult->EOF) {
            $arrGroups[$objResult->fields['group_id']] = $objResult->fields['group_name'];
            $objResult->MoveNext();
        }
    //PAGES
        $objResult = $objDatabase->Execute('SELECT  id,
                                                    history_id,
                                                    action
                                            FROM    '.DBPREFIX.'content_logfile
                                            '.$strQueryWhere.'
                                        ');
        /** start paging **/
        $strPaging = getPaging($objResult->RecordCount(),intval($_GET['pos']),'&amp;cmd=workflow&amp;act='.$strPagingAct,'', true);
        $objTemplate->setVariable('HISTORY_PAGING',$strPaging);
        /** end paging **/

        $objResult = $objDatabase->SelectLimit('SELECT      id,
                                                            history_id,
                                                            action
                                                FROM        '.DBPREFIX.'content_logfile
                                                '.$strQueryWhere.'
                                                ORDER BY    id DESC',
                                                $_CONFIG['corePagingLimit'],
                                                intval($_GET['pos'])
                                            );

        while (!$objResult->EOF) {
                $arrHistory[$objResult->fields['history_id']] = array(  'id'        =>  $objResult->fields['id'],
                                                                        'action'    =>  $objResult->fields['action']
                                                                    );
            $objResult->MoveNext();
        }

        $iRowId = 0;

        if (count($arrHistory) > 0) {
            $intRowCount = 0;
            foreach ($arrHistory as $intHistoryId => $arrInner) {

                $strQueryAction = $arrInner['action'];
                $intLogfileId   = $arrInner['id'];

                $objResult = $objDatabase->SelectLimit('SELECT  navTable.id                 AS navID,
                                                                navTable.catid              AS navPageId,
                                                                navTable.is_active          AS navActive,
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
                                                    WHERE       navTable.id = '.$intHistoryId, 1);
                $strBackendGroups   = '';
                $strFrontendGroups  = '';

                if (!empty($objResult->fields['navPageId'])) {
                    $objSubResult = $objDatabase->SelectLimit('    SELECT    catid
                                                            FROM    '.DBPREFIX.'content_navigation
                                                            WHERE    catid='.intval($objResult->fields['navPageId']), 1);
                    if ($objSubResult->RecordCount() == 1) {
                        $boolPageExists = true;
                    } else {
                        $boolPageExists = false;
                    }
                } else {
                    $boolPageExists = false;
                }

                if ($objResult->fields['navBAccess'] != 0) {
                    $objSubResult = $objDatabase->Execute('
                        SELECT group_id
                          FROM '.DBPREFIX.'access_group_dynamic_ids
                         WHERE access_id='.$objResult->fields['navBAccess']);
                    while (!$objSubResult->EOF) {
                        $strBackendGroups .= $arrGroups[$objSubResult->fields['group_id']].',';
                        $objSubResult->MoveNext();
                    }
                    $strBackendGroups = substr($strBackendGroups,0,strlen($strBackendGroups)-1);
                } else {
                    $strBackendGroups = $arrGroups[0];
                }

                if ($objResult->fields['navFAccess'] != 0) {
                    $objSubResult = $objDatabase->Execute('
                        SELECT group_id
                          FROM '.DBPREFIX.'access_group_dynamic_ids
                         WHERE access_id='.$objResult->fields['navFAccess']);
                    while (!$objSubResult->EOF) {
                        $strFrontendGroups .= $arrGroups[$objSubResult->fields['group_id']].',';
                        $objSubResult->MoveNext();
                    }
                    $strFrontendGroups = substr($strFrontendGroups,0,strlen($strFrontendGroups)-1);
                } else {
                    $strFrontendGroups = $arrGroups[0];
                }

                switch ($strAction) {
                    case 'updated':
                        if(!$boolPageExists) {
                            $strIcon = '<img src="images/icons/empty.gif" alt="'.$_CORELANG['TXT_HISTORY_DELETED'].'" title="'.$_CORELANG['TXT_HISTORY_DELETED'].'" border="0" />';
                        } else {
                            $s = isset($arrModules[$objResult->fields['navModule']]) ? $arrModules[$objResult->fields['navModule']] : '';
                            $c = $objResult->fields['navCMD'];
                            $section = ($s=="" || $s == '-') ? "" : "&amp;section=$s";
                            $cmd = ($c=="") ? "" : "&amp;cmd=$c";
                            $strIcon = '<a href="../index.php?page='.$objResult->fields['navPageId'].$section.$cmd.'&amp;history='.$intHistoryId.'&amp;langId='.$objResult->fields['navLang'].'" target="_blank" title="'.$_CORELANG['TXT_WORKFLOW_PAGE_PREVIEW'].'"><img src="images/icons/details.gif" width="16" height="16" border="0" align="middle" alt="'.$_CORELANG['TXT_WORKFLOW_PAGE_PREVIEW'].'" /></a>';
                        }
                    break;
                    case 'deleted':
                        $strIcon = '<a href="javascript:restoreDeleted(\''.$objResult->fields['navID'].'\');"><img src="images/icons/import.gif" alt="'.$_CORELANG['TXT_DELETED_RESTORE'].'" title="'.$_CORELANG['TXT_DELETED_RESTORE'].'" border="0" align="middle" /></a>';
                    break;
                    case 'unvalidated':
                        $strIcon = '<a href="'.CONTREXX_DIRECTORY_INDEX.'?cmd=workflow&amp;act=validate&amp;acc=1&amp;prompt=warn&amp;id='.$intLogfileId.'"><img src="images/icons/thumb_up.gif" alt="'.$_CORELANG['TXT_WORKFLOW_VALIDATE_ACCEPT'].'" title="'.$_CORELANG['TXT_WORKFLOW_VALIDATE_ACCEPT'].'" border="0" align="middle" /></a>&nbsp;';
                        $strIcon .= '<a href="'.CONTREXX_DIRECTORY_INDEX.'?cmd=workflow&amp;act=validate&amp;acc=0&amp;prompt=warn&amp;id='.$intLogfileId.'"><img src="images/icons/thumb_down.gif" alt="'.$_CORELANG['TXT_WORKFLOW_VALIDATE_DECLINE'].'" title="'.$_CORELANG['TXT_WORKFLOW_VALIDATE_DECLINE'].'" border="0" align="middle" /></a>&nbsp;';
                        $s = isset($arrModules[$objResult->fields['navModule']]) ? $arrModules[$objResult->fields['navModule']] : '';
                        $c = $objResult->fields['navCMD'];
                        $section = ($s=="" || $s == '-') ? "" : "&amp;section=$s";
                        $cmd = ($c=="") ? "" : "&amp;cmd=$c";
                        $strIcon .= '<a href="../index.php?page='.$objResult->fields['navPageId'].$section.$cmd.'&amp;history='.$intHistoryId.'&amp;langId='.$objResult->fields['navLang'].'" target="_blank" title="'.$_CORELANG['TXT_WORKFLOW_PAGE_PREVIEW'].'"><img src="images/icons/details.gif" width="16" height="16" border="0" align="middle" alt="'.$_CORELANG['TXT_WORKFLOW_PAGE_PREVIEW'].'" /></a>';

                        switch ($strQueryAction) {
                            case 'new':
                                $strPrefix = $_CORELANG['TXT_VALIDATE_PREFIX_NEW'].'&nbsp;';
                            break;
                            case 'delete':
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
                            $strIcon = '<a href="'.CONTREXX_DIRECTORY_INDEX.'?cmd=content&amp;act=edit&amp;pageId='.$objResult->fields['navPageId'].'"><img src="images/icons/details.gif" alt="'.$_CORELANG['TXT_DETAILS'].'" title="'.$_CORELANG['TXT_DETAILS'].'" border="0" /></a>';
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
                    'TXT_OPTIONAL_CSS_NAME'     =>  $_CORELANG['TXT_OPTIONAL_CSS_NAME'],
                    'TXT_MODULE'                =>  $_CORELANG['TXT_MODULE'],
                    'TXT_REDIRECT'              =>  $_CORELANG['TXT_REDIRECT'],
                    'TXT_SOURCE_MODE'           =>  $_CORELANG['TXT_SOURCE_MODE'],
                    'TXT_CACHING_STATUS'        =>  $_CORELANG['TXT_CACHING_STATUS'],
                    'TXT_FRONTEND'              =>  $_CORELANG['TXT_WEB_PAGES'],
                    'TXT_BACKEND'               =>  $_CORELANG['TXT_ADMINISTRATION_PAGES'],
                ));

                $objTemplate->setVariable(array(
                    'HISTORY_ROWCLASS'      =>  ($intRowCount % 2 == 0) ? 'row0' : 'row1',
                    'HISTORY_IMGDETAILS'    =>  $strIcon,
                    'HISTORY_RID'           =>  $iRowId,
                    'HISTORY_ID'            =>  $objResult->fields['navID'],
                    'HISTORY_PID'           =>  $objResult->fields['navPageId'],
                    'HISTORY_DATE'          =>  date('d.m.Y H:i:s',$objResult->fields['navChangelog']),
                    'HISTORY_USER'          =>  $objResult->fields['navUsername'],
                    'HISTORY_PREFIX'        =>  $strPrefix,
                    'HISTORY_TITLE'         =>  stripslashes($objResult->fields['navCatname']),
                    'HISTORY_CONTENT_TITLE' =>  stripslashes($objResult->fields['conTitle']),
                    'HISTORY_METATITLE'     =>  stripslashes($objResult->fields['conMetaTitle']),
                    'HISTORY_METADESC'      =>  stripslashes($objResult->fields['conMetaDesc']),
                    'HISTORY_METAKEY'       =>  stripslashes($objResult->fields['conMetaKeywords']),
                    'HISTORY_STARTDATE'     =>  $objResult->fields['navStartdate'],
                    'HISTORY_ENDDATE'       =>  $objResult->fields['navEnddate'],
                    'HISTORY_THEME'         =>  stripslashes($arrThemes[$objResult->fields['navTheme']]),
                    'HISTORY_OPTIONAL_CSS'  =>  (empty($objResult->fields['conCssName'])) ? '-' : stripslashes($objResult->fields['conCssName']),
                    'HISTORY_MODULE'        =>  $arrModules[$objResult->fields['navModule']].' '.$objResult->fields['navCMD'],
                    'HISTORY_CMD'           =>  (empty($objResult->fields['navCMD'])) ? '-' : $objResult->fields['navCMD'],
                    'HISTORY_SECTION'       =>  $arrModules[$objResult->fields['navModule']],
                    'HISTORY_REDIRECT'      =>  (empty($objResult->fields['conRedirect'])) ? '-' : $objResult->fields['conRedirect'],
                    'HISTORY_SOURCEMODE'    =>  strtoupper($objResult->fields['conExpertMode']),
                    'HISTORY_CACHING_STATUS'=>  ($objResult->fields['navCachingStatus'] == 1) ? 'Y' : 'N',
                    'HISTORY_FRONTEND'      =>  stripslashes($strFrontendGroups),
                    'HISTORY_BACKEND'       =>  stripslashes($strBackendGroups),
                    'HISTORY_CONTENT'       =>  stripslashes(htmlspecialchars($objResult->fields['conContent'], ENT_QUOTES, CONTREXX_CHARSET)),
                ));

                $objTemplate->parse('showPages');
                $intRowCount++;
                $iRowId++;
            }
        } else {
            $objTemplate->hideBlock('showPages');
        }
    }


    /**
     * Load a page from history
     * @global   ADONewConnection
     * @param    integer  $intHistoryId  The entry with this id will be loaded
     * @param    boolean  $boolInsert    This parameter has to set to true, if the page was deleted before
     * @return   integer       $intPageId: The id of the page which was loaded

     */
    function restoreHistory($intHistoryId, $boolInsert=false)
    {
        global $objDatabase;

        $objResult = $objDatabase->Execute('SELECT  `catid`, `lang`


                                            FROM    '.DBPREFIX.'content_navigation_history
                                            WHERE   id='.$intHistoryId.'
                                            LIMIT   1');

        $objDatabase->Execute(' UPDATE  '.DBPREFIX.'content_navigation_history
                                SET     is_active="0"
                                WHERE   catid='.$objResult->fields['catid'].' AND lang='.$objResult->fields['lang']);

        $objDatabase->Execute(' UPDATE  '.DBPREFIX.'content_navigation_history
                                SET     is_active="1"
                                WHERE   id='.$intHistoryId.'
                                LIMIT   1');

        $objResult = $objDatabase->Execute('SELECT  *
                                            FROM    '.DBPREFIX.'content_navigation_history
                                            WHERE   id='.$intHistoryId.'
                                            LIMIT   1');

        $objContentTree = new ContentTree();
        $arrCategory = $objContentTree->getThisNode($objResult->fields['parcat']);

        if (!is_array($arrCategory) && $boolInsert) {
                $objSubResult = $objDatabase->Execute(' SELECT  `catid`
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
                                    WHERE   `action`="delete" AND
                                            history_id='.$intHistoryId.'
                                    LIMIT   1
                                ');
        }
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
                ON DUPLICATE KEY UPDATE catid='.$objResult->fields['catid'].',
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
                                        themes_id='.$objResult->fields['themes_id']);

        $objResult = $objDatabase->Execute('SELECT  *
                                            FROM    '.DBPREFIX.'content_history
                                            WHERE   id='.$intHistoryId.'
                                            LIMIT   1
                                        ');
        $objDatabase->Execute(' INSERT
                                INTO    '.DBPREFIX.'content
                                SET     id='.$objResult->fields['page_id'].',
                                        lang_id="'.addslashes($objResult->fields['lang_id']).'",
                                        content="'.addslashes($objResult->fields['content']).'",
                                        title="'.addslashes($objResult->fields['title']).'",
                                        metatitle="'.addslashes($objResult->fields['metatitle']).'",
                                        metadesc="'.addslashes($objResult->fields['metadesc']).'",
                                        metakeys="'.addslashes($objResult->fields['metakeys']).'",
                                        metarobots="'.addslashes($objResult->fields['metarobots']).'",
                                        css_name="'.addslashes($objResult->fields['css_name']).'",
                                        redirect="'.addslashes($objResult->fields['redirect']).'",
                                        expertmode="'.addslashes($objResult->fields['expertmode']).'"
                ON DUPLICATE KEY UPDATE id='.$objResult->fields['page_id'].',
                                        lang_id="'.addslashes($objResult->fields['lang_id']).'",
                                        content="'.addslashes($objResult->fields['content']).'",
                                        title="'.addslashes($objResult->fields['title']).'",
                                        metatitle="'.addslashes($objResult->fields['metatitle']).'",
                                        metadesc="'.addslashes($objResult->fields['metadesc']).'",
                                        metakeys="'.addslashes($objResult->fields['metakeys']).'",
                                        metarobots="'.addslashes($objResult->fields['metarobots']).'",
                                        css_name="'.addslashes($objResult->fields['css_name']).'",
                                        redirect="'.addslashes($objResult->fields['redirect']).'",
                                        expertmode="'.addslashes($objResult->fields['expertmode']).'"');
    }


    /**
     * Load a page from history
     * @global   ADONewConnection
     * @global     array        Core language
     * @global     array        Configuration
     * @param    integer      $intHistoryId: The entry with this id will be loaded
     * @param     boolean        $boolInsert: This parameter has to set to true, if the page was deleted before
     * @return   integer       $intPageId: The id of the page which was loaded
     */
    function loadHistory($intHistoryId,$boolInsert=false)
    {
        global $objDatabase, $_CORELANG, $_CONFIG;

        $intHistoryId = intval($intHistoryId);

        if ($intHistoryId > 0) {
            $objRSAction = $objDatabase->Execute('SELECT 1
                                                  FROM    '.DBPREFIX.'content_logfile
                                                  WHERE   history_id='.$intHistoryId."
                                                  AND     `action` = 'delete'
                                                  LIMIT   1");
            $objResult   = $objDatabase->Execute('SELECT  `catid`, `lang`
                                                  FROM    '.DBPREFIX.'content_navigation_history
                                                  WHERE   id='.$intHistoryId.'
                                                  LIMIT   1');

            if($objRSAction->RecordCount() == 1){
                $objHistoryResult = $objDatabase->Execute("SELECT `h`.`id`
                                                           FROM `".DBPREFIX."content_navigation_history` AS `h`
                                                           LEFT JOIN `".DBPREFIX."content_logfile` AS `l` ON ( `l`.`history_id` = `h`.`id` )
                                                           WHERE catid=".$objResult->fields['catid']."
                                                           AND `action` = 'delete'");

                while (!$objHistoryResult->EOF) {
                    $this->restoreHistory($objHistoryResult->fields['id'], $boolInsert);
                    $objHistoryResult->MoveNext();
                }
            }else{
                $this->restoreHistory($intHistoryId, $boolInsert);
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
        return intval($objResult->fields['catid']);
    }


    /**
     * Delete a history entry
     * @global   ADONewConnection
     * @global     array        Core language
     * @param    integer      $intHistoryId    The entry with this id will be deleted
     * @return   integer       $intPageId       The history entry deleted belonged to this pageid
     */
    function deleteHistory($intHistoryId)
    {
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
     * @param    integer     The page with this id will be shown in content manager
     */
    function redirectPage($intPageId)
    {
        header('location:index.php?cmd=content&act=edit&pageId='.intval($intPageId));
        exit;
    }

    /**
     * Validate an incoming page
     * @global   ADONewConnection
     * @global     array        Core language
     * @param    integer      $intHistoryId       The entry with this id will be validated
     * @return   integer       $intValidateStatus  1 -> accept page, 0 -> decline page
     */
    function validatePage($intLogfileId,$intValidateStatus,$prompt)
    {
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

            $objResult = $objDatabase->Execute('SELECT  catid, lang, changelog
                                                FROM    '.DBPREFIX.'content_navigation_history
                                                WHERE   id = '.$intHistoryId.'
                                                LIMIT   1
                                            ');
            $row = $objResult->FetchRow();
            $intPageId = $row['catid'];
            $langId    = $row['lang'];
            $changeLog = $row['changelog'];
            
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
                                                  AND   lang='.$langId.'
                                                LIMIT   1
                                            ');
                        $objDatabase->Execute(' UPDATE  '.DBPREFIX.'content_navigation
                                                SET     is_validated="1",
                                                        activestatus="1",
                                                        changelog='.time().'
                                                WHERE   catid='.$intPageId.'
                                                AND     lang='.$langId.'
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
                                                  AND   lang_id='.$langId.'
                                                LIMIT   1
                                            ');
                        $objDatabase->Execute(' DELETE
                                                FROM    '.DBPREFIX.'content_navigation
                                                WHERE   catid='.$intPageId.'
                                                  AND   lang='.$langId.'
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
                                                  AND   lang_id='.$langId.'
                                                LIMIT   1
                                            ');
                        $objDatabase->Execute(' DELETE
                                                FROM    '.DBPREFIX.'content_navigation
                                                WHERE   catid='.$intPageId.'
                                                  AND   lang='.$langId.'
                                                LIMIT   1
                                            ');
                        /*
                         * Delete Older updates from log table
                         */
                        $objResult = $objDatabase->Execute(' SELECT id
                                                FROM    '.DBPREFIX.'content_navigation_history
                                                WHERE   catid='.$intPageId.'
                                                  AND   lang='.$langId.'
                                                  AND   is_active="0"
                                            ');
                        while(!$objResult->EOF){
                            $objDatabase->Execute(' DELETE
                                                FROM    '.DBPREFIX.'content_logfile
                                                WHERE   history_id='.$objResult->fields['id'].'
                                                LIMIT   1
                                            ');
                            $objResult->MoveNext();
                        }

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
                default://update
                    if ($intValidateStatus == 1) {
                        //allow update
                        $this->loadHistory($intHistoryId,false);
                        $objDatabase->Execute(' UPDATE  '.DBPREFIX.'content_logfile
                                                SET     is_validated="1"
                                                WHERE   id='.$intLogfileId.'
                                                LIMIT   1
                                            ');
                        /*
                         * Page made active and validated
                         */
                        $objDatabase->Execute(' UPDATE  '.DBPREFIX.'content_navigation
                                                SET     is_validated="1",
                                                        activestatus="1",
                                                        changelog='.time().'
                                                WHERE   catid='.$intPageId.'
                                                AND     lang='.$langId.'
                                                LIMIT   1
                                            ');
                        /*
                         * Remove the older version of the accepted page
                         *
                         * Fetch pageid, langid and changelog with selected id
                         */
                        $objResult = $objDatabase->Execute('SELECT navTable.changelog   AS navChangeLog,
                                                                   navTable.catid       AS navCatId,
                                                                   navTable.lang        AS navLang
                                                    FROM        '.DBPREFIX.'content_navigation_history AS navTable
                                                    WHERE       id = (
                                                                SELECT      history_id
                                                                FROM        '.DBPREFIX.'content_logfile
                                                                WHERE       id= '.$intLogfileId.'
                                                                )
                                                    LIMIT       1');

                        $row = $objResult->FetchRow();
                        $navChangeLog = $row['navChangeLog'];
                        $navCatId = $row['navCatId'];
                        $navLang = $row['navLang'];

                        /*
                         * Select id of the matched pages that are inactive and older than selected version
                         */
                        $objResult = $objDatabase->Execute('SELECT navTable.id   AS navId
                                                    FROM        '.DBPREFIX.'content_navigation_history AS navTable
                                                    LEFT JOIN   '.DBPREFIX.'content_logfile AS navLog
                                                    ON          navTable.id = navLog.history_id
                                                    WHERE       navTable.catid = '.$navCatId.'
                                                    AND         navTable.lang = '.$navLang.'
                                                    AND         (navTable.is_active = "0" OR navLog.action = "new")
                                                    AND         navTable.changelog < '.$navChangeLog);

                        while(!$objResult->EOF){
                             $objDatabase->Execute(' DELETE
                                                FROM    '.DBPREFIX.'content_logfile
                                                WHERE   history_id='.$objResult->fields['navId']);
                             $objDatabase->Execute(' DELETE
                                                FROM    '.DBPREFIX.'content_history
                                                WHERE   id='.$objResult->fields['navId']);
                             $objDatabase->Execute(' DELETE
                                                FROM    '.DBPREFIX.'content_navigation_history
                                                WHERE   id='.$objResult->fields['navId']);
                            $objResult->MoveNext();
                        }

                        $this->strOkMessage = $_CORELANG['TXT_WORKFLOW_VALIDATE_UPDATE_ACCEPT'];
                    } else {
                        //decline update
                        /*
                         * Display warning for rejecting the older version pages
                         */
                        if($prompt == 'warn'){
                            $objResult = $objDatabase->Execute('SELECT navTable.catname            AS navCatname,
                                                                       navTable.username           AS navUsername,
                                                                       navTable.changelog          AS navChangelog
                                                                FROM        '.DBPREFIX.'content_navigation_history AS navTable
                                                                LEFT JOIN   '.DBPREFIX.'content_logfile AS navLog
                                                                ON          navTable.id = navLog.history_id
                                                                WHERE       navTable.catid = '.$intPageId.'
                                                                AND         navTable.lang = '.$langId.'
                                                                AND         (navTable.is_active = "0" OR navLog.action = "new")
                                                                AND         navTable.changelog <= '.$changeLog);

                            $this->strWarningMessage = "Please confirm deletion of multiple versions<br/>List of revisions:<br/><br/>";
                            while(!$objResult->EOF){
                                $this->strWarningMessage .= "Page '".$objResult->fields['navCatname']."' By ".$objResult->fields['navUsername']." on ".date('d.m.Y H:i:s',$objResult->fields['navChangelog'])."<br/><br/>";
                                $objResult->MoveNext();
                            }
                            $this->strWarningMessage .= "<a title='Cancel' href='".CONTREXX_DIRECTORY_INDEX."?cmd=workflow'>Cancel</a>&nbsp;&nbsp;&nbsp;";
                            $this->strWarningMessage .= "<a title='Continue' href='".CONTREXX_DIRECTORY_INDEX."?cmd=workflow&amp;act=validate&amp;acc=0&amp;id=".$intLogfileId."'>Continue</a>";
                        } else {
                            /*
                             * Fetch ids of all the versions of rejected page and delete related data
                             */
                            $objResult = $objDatabase->Execute('SELECT navTable.id AS navId
                                                    FROM        '.DBPREFIX.'content_navigation_history AS navTable
                                                    LEFT JOIN   '.DBPREFIX.'content_logfile AS navLog
                                                    ON          navTable.id = navLog.history_id
                                                    WHERE       navTable.catid = '.$intPageId.'
                                                    AND         navTable.lang = '.$langId.'
                                                    AND         (navTable.is_active= "0" OR navLog.action = "new")
                                                    AND         navTable.changelog <= '.$changeLog);
                            while(!$objResult->EOF){
                                $objDatabase->Execute(' DELETE
                                                    FROM    '.DBPREFIX.'content_logfile
                                                    WHERE   history_id='.$objResult->fields['navId']);
                                $objDatabase->Execute(' DELETE
                                                    FROM    '.DBPREFIX.'content_history
                                                    WHERE   id='.$objResult->fields['navId']);
                                $objDatabase->Execute(' DELETE
                                                    FROM    '.DBPREFIX.'content_navigation_history
                                                    WHERE   id='.$objResult->fields['navId']);
                                $objResult->MoveNext();
                            }
                            $this->strOkMessage = $_CORELANG['TXT_WORKFLOW_VALIDATE_UPDATE_DECLINE'];
                        }
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
     * @global     HTML_Template_Sigma
     * @global     ADONewConnection
     * @global     array        Core language
     */
    function showClean()
    {
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
     * @global     ADONewConnection
     * @global     array
     * @param    integer        $intNumberOfDays: Entries older than this value will be removed
     */
    function cleanHistory($intNumberOfDays)
    {
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
