<?php

/**
 * Media  Directory
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @subpackage  module_mediadir
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once ASCMS_MODULE_PATH . '/mediadir/lib/lib.class.php';
require_once ASCMS_MODULE_PATH . '/mediadir/lib/entry.class.php';
require_once ASCMS_MODULE_PATH . '/mediadir/lib/category.class.php';
require_once ASCMS_MODULE_PATH . '/mediadir/lib/level.class.php';
require_once ASCMS_MODULE_PATH . '/mediadir/lib/form.class.php';
require_once ASCMS_MODULE_PATH . '/mediadir/lib/inputfield.class.php';
require_once ASCMS_MODULE_PATH . '/mediadir/lib/mail.class.php';
require_once ASCMS_MODULE_PATH . '/mediadir/lib/search.class.php';
require_once ASCMS_MODULE_PATH . '/mediadir/lib/voting.class.php';
require_once ASCMS_MODULE_PATH . '/mediadir/lib/comment.class.php';



class mediaDirectory extends mediaDirectoryLibrary
{

    var $pageTitle;
    var $metaTitle;

    var $arrNavtree = array();

    /**
     * Constructor
     */
    function __construct($pageContent)
    {
        global $_ARRAYLANG;

        //globals
        parent::getSettings();
        parent::checkDisplayduration();
        
        $_ARRAYLANG['TXT_MEDIADIR_GOOGLEMAPS_LINK'] = 'Link zu Googe Maps';
        $_ARRAYLANG['TXT_MEDIADIR_DISPLAYNAME'] = 'Anzeigename';

        $this->pageContent = $pageContent;

        $this->_objTpl = new HTML_Template_Sigma('.');
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);
    }

    /**
     * get oage
     *
     * Reads the act and selects the right action
     *
     * @access   public
     * @return   string  parsed content
     */
    function getPage()
    {
        global $_CONFIG;
        
        JS::activate('shadowbox');

        if($this->arrSettings['settingsAllowVotes']) {
            $objVoting = new mediaDirectoryVoting();
            $this->setJavascript($objVoting->getVoteJavascript());

            if(isset($_GET['vote']) && intval($_GET['eid']) != 0) {
                $objVoting->saveVote(intval($_GET['eid']), intval($_GET['vote']));
            }
        }

        if($this->arrSettings['settingsAllowComments'] == 1) {
            $objComment = new mediaDirectoryComment();
            $this->setJavascript($objComment->getCommentJavascript());

            if($_GET['comment'] == 'add' && intval($_GET['eid']) != 0) {
                $objComment->saveComment(intval($_GET['eid']), $_POST);
            }

            if($_GET['comment'] == 'refresh' && intval($_GET['eid']) != 0) {
                $objComment->refreshComments(intval($_GET['eid']), $_GET['pageSection'], $_GET['pageCmd']);
            }
        }

        switch ($_REQUEST['cmd']) {
            case 'add':
                parent::checkAccess('add_entry');
                $this->modifyEntry();
                break;
            case 'edit':
                if((intval($_REQUEST['eid']) != 0) || (intval($_REQUEST['entryId']) != 0)) {
                    parent::checkAccess('edit_entry');
                    $this->modifyEntry();
                } else {
                    header("Location: index.php?section=mediadir");
                    exit;
                }
                break;
            case 'delete':
                if((intval($_REQUEST['eid']) != 0) || (intval($_REQUEST['entryId']) != 0)) {
                    parent::checkAccess('delete_entry');
                    $this->deleteEntry();
                } else {
                    header("Location: index.php?section=mediadir");
                    exit;
                }
                break;
            case 'latest':
                $this->showLatest();
                break;
            case 'popular':
                $this->showPopular();
                break;
            case 'map':
                if(!empty($_CONFIG['googleMapsAPIKey'])) {
                    $this->showMap();
                } else {
                    header("Location: index.php?section=mediadir");
                    exit;
                }
                break;
            case 'detail':
                parent::checkAccess('show_entry');
                $this->showEntry();
                break;
            default:
                if(isset($_REQUEST['check'])) {
                    parent::checkDisplayduration();
                }
                
                if(isset($_REQUEST['search'])) {
                    $this->showSearch();
                } else {
                    $this->overview();
                }
        }

        $this->_objTpl->setVariable(array(
            'MEDIADIR_JAVASCRIPT' =>  $this->getJavascript(),
        ));

        return $this->_objTpl->get();
    }

    function overview()
    {
        global $_ARRAYLANG, $_CORELANG;

        $this->_objTpl->setTemplate($this->pageContent, true, true);

        //search existing category&level blocks
        $arrExistingBlocks = array();

        for($i = 1; $i <= 10; $i++){
            if($this->_objTpl->blockExists('mediadirCategoriesLevels_row_'.$i)){
                array_push($arrExistingBlocks, $i);
            }
        }

        //get ids
        if(isset($_GET['cmd'])) {
            $arrIds = explode("-", $_GET['cmd']);
        }

        if($this->arrSettings['settingsShowLevels'] == 1) {
            if(intval($arrIds[0]) != 0) {
                $intLevelId = intval($arrIds[0]);
            } else {
                $intLevelId = 0;
            }

            $intLevelId = isset($_GET['lid']) ? intval($_GET['lid']) : $intLevelId;

            if(intval($arrIds[1]) != 0) {
                $intCategoryCmd = $arrIds[1];
            } else {
                $intCategoryCmd = 0;
            }
        } else {
            $intLevelId = 0;

            if(intval($arrIds[0]) != 0) {
                $intCategoryCmd = $arrIds[0];
            } else {
                $intCategoryCmd = 0;
            }
        }

        if($intCategoryCmd != 0) {
            $intCategoryId = intval($intCategoryCmd);
        } else {
            $intCategoryId = 0;
        }

        $intCategoryId = isset($_GET['cid']) ? intval($_GET['cid']) : $intCategoryId;

        //get navtree
        if($this->_objTpl->blockExists('mediadirNavtree') && ($intCategoryId != 0 || $intLevelId != 0)){
            $this->getNavtree($intCategoryId, $intLevelId);
        }

        //get searchform
        if($this->_objTpl->blockExists('mediadirSearchform')){
            $objSearch = new mediaDirectorySearch();
            $objSearch->getSearchform($this->_objTpl, 1);
        }

        //get level / category details
        if($this->_objTpl->blockExists('mediadirCategoryLevelDetail')){
            if ($intCategoryId == 0 && $intLevelId != 0 && $this->arrSettings['settingsShowLevels'] == 1) {
                $objLevel = new mediaDirectoryLevel($intLevelId, null, 0);
                $objLevel->listLevels($this->_objTpl, 5, $intLevelId);
            }

            if($intCategoryId != 0) {
                $objCategory = new mediaDirectoryCategory($intCategoryId, null, 0);
                $objCategory->listCategories($this->_objTpl, 5, $intCategoryId);

            }
        }

        //list levels / categories
        if($this->_objTpl->blockExists('mediadirCategoriesLevelsList')){
            if($this->arrSettings['settingsShowLevels'] == 1 && $intCategoryId == 0 ) {
                $objLevels = new mediaDirectoryLevel(null, $intLevelId, 1);
                $objCategories = new mediaDirectoryCategory(null, $intCategoryId, 1);
                $objLevels->listLevels($this->_objTpl, 2, null, null, null, $arrExistingBlocks);
                $this->_objTpl->clearVariables();
                $this->_objTpl->parse('mediadirCategoriesLevelsList');
            }

            if($objLevel->arrLevels[$intLevelId]['levelShowCategories'] == 1 || $this->arrSettings['settingsShowLevels'] == 0 || $intCategoryId != 0) {
                $objCategories = new mediaDirectoryCategory(null, $intCategoryId, 1);
                $objCategories->listCategories($this->_objTpl, 2, null, null, null, $arrExistingBlocks);
                $this->_objTpl->clearVariables();
                $this->_objTpl->parse('mediadirCategoriesLevelsList');
            }

            if(empty($objLevel->arrLevels) && empty($objCategories->arrCategories)) {
                $this->_objTpl->hideBlock('mediadirCategoriesLevelsList');
                $this->_objTpl->clearVariables();
            }
        }

        //list entries
        if($this->_objTpl->blockExists('mediadirEntryList')){
            $intLimitStart = isset($_GET['pos']) ? intval($_GET['pos']) : 0;

            if($intCategoryId == 0 && $intLevelId == 0) {
                $bolLatest = true;
                $intLimitEnd = intval($this->arrSettings['settingsLatestNumOverview']);
            } else {
                $bolLatest = false;
                $intLimitEnd = intval($this->arrSettings['settingsPagingNumEntries']);

                $strPagingCmdParam = $intCategoryCmd != 0 ? "&amp;cmd=".$intCategoryCmd : '';
                $strPagingCatParam = $intCategoryId != 0 ? "&amp;cid=".$intCategoryId : '';
                $strPagingLevelParam = $intLevelId != 0 ? "&amp;lid=".$intLevelId : '';
            }

            if($objLevel->arrLevels[$intLevelId]['levelShowEntries'] == 1 || $objCategory->arrCategories[$intCategoryId]['catShowEntries'] == 1 || $bolLatest == true) {
                $objEntries = new mediaDirectoryEntry();
                if(!$bolLatest) {
                    $intNumEntries = intval($objEntries->countEntries($intCategoryId, $intLevelId));
                    if($intNumEntries > $intLimitEnd) {
                        $strPaging = getPaging($intNumEntries, $intLimitStart, "&amp;section=mediadir".$strPagingCmdParam.$strPagingLevelParam.$strPagingCatParam, "<b>".$_ARRAYLANG['TXT_MEDIADIR_ENTRIES']."</b>", true, $intLimitEnd);
                        $this->_objTpl->setGlobalVariable(array(
                            'MEDIADIR_PAGING' =>  $strPaging
                        ));
                    }
                }

                $objEntries->getEntries(null,$intLevelId,$intCategoryId,null,$bolLatest,null,1,$intLimitStart, $intLimitEnd);
                $objEntries->listEntries($this->_objTpl, 2);
            }

            if(empty($objEntries->arrEntries)) {
                $this->_objTpl->hideBlock('mediadirEntryList');
                $this->_objTpl->clearVariables();
            }
        }
    }



    function showSearch()
    {
        global $_ARRAYLANG, $_CORELANG;

        $this->_objTpl->setTemplate($this->pageContent, true, true);

        //get searchform
        if($this->_objTpl->blockExists('mediadirSearchform')){
            $objSearch = new mediaDirectorySearch();
            $objSearch->getSearchform($this->_objTpl);
        }

        $_GET['term'] = trim($_GET['term']);


        $intLimitStart = isset($_GET['pos']) ? intval($_GET['pos']) : 0;
        $intLimitEnd = intval($this->arrSettings['settingsPagingNumEntries']);

        foreach ($_GET as $strCmdName => $strCmdValue) {
            if($strCmdName != "pos") {
                $strPaigingParams .= $strCmdName."=".$strCmdValue."&amp;";
            }
        }

        if(!empty($_GET['term']) || $_GET['type'] == 'exp') {
            $objSearch = new mediaDirectorySearch();
            $objSearch->searchEntries($_GET);

            $objEntries = new mediaDirectoryEntry();

            if(!empty($objSearch->arrFoundIds)) {
                $intNumEntries = count($objSearch->arrFoundIds);

                for($i=$intLimitStart; $i < ($intLimitStart+$intLimitEnd); $i++) {
                    $intEntryId = $objSearch->arrFoundIds[$i];
                    if(intval($intEntryId) != 0) {
                        $objEntries->getEntries($intEntryId, null, null, null, null, null, 1, 0, 1, null, null);
                    }
                }

                $objEntries->listEntries($this->_objTpl, 2);

                if($intNumEntries > $intLimitEnd) {
                    $strPaging = getPaging($intNumEntries, $intLimitStart, "&amp;".$strPaigingParams, "<b>".$_ARRAYLANG['TXT_MEDIADIR_ENTRIES']."</b>", true, $intLimitEnd);
                    $this->_objTpl->setGlobalVariable(array(
                        'MEDIADIR_PAGING' =>  $strPaging
                    ));
                }
            } else {
                $this->_objTpl->setVariable(array(
                    'TXT_MEDIADIR_SEARCH_MESSAGE' =>  $_ARRAYLANG['TXT_MEDIADIR_NO_ENTRIES_FOUND'],
                ));
            }
        } else {
            $this->_objTpl->setVariable(array(
                'TXT_MEDIADIR_SEARCH_MESSAGE' =>  $_ARRAYLANG['TXT_MEDIADIR_NO_SEARCH_TERM'],
            ));
        }

    }




    function showEntry()
    {
        global $_ARRAYLANG, $_CORELANG;

        $this->_objTpl->setTemplate($this->pageContent, true, true);

        //get ids
        $intCategoryId = isset($_GET['cid']) ? intval($_GET['cid']) : 0;
        $intLevelId = isset($_GET['lid']) ? intval($_GET['lid']) : 0;
        $intEntryId = isset($_GET['eid']) ? intval($_GET['eid']) : 0;

        //get navtree
        if($this->_objTpl->blockExists('mediadirNavtree') && ($intCategoryId != 0 || $intLevelId != 0)){
            $this->getNavtree($intCategoryId, $intLevelId);
        }

        if($intEntryId != 0 && $this->_objTpl->blockExists('mediadirEntryList')) {
            $objEntry = new mediaDirectoryEntry();
            $objEntry->getEntries($intEntryId,$intLevelId,$intCategoryId,null,null,null,1,null,1);
            $objEntry->listEntries($this->_objTpl, 2);
            $objEntry->updateHits($intEntryId);

            //set meta title
            $this->metaTitle .= " - ".$objEntry->arrEntries[$intEntryId]['entryFields'][0];
            $this->pageTitle = $objEntry->arrEntries[$intEntryId]['entryFields'][0];

            if(empty($objEntry->arrEntries)) {
                $this->_objTpl->hideBlock('mediadirEntryList');
                $this->_objTpl->clearVariables();

                header("Location: index.php?section=mediadir");
			    exit;
            }
        } else {
            header("Location: index.php?section=mediadir");
			exit;
        }
    }



    function showMap()
    {
        global $_ARRAYLANG, $_CORELANG;

        $this->_objTpl->setTemplate($this->pageContent, true, true);

        $objEntry = new mediaDirectoryEntry();
        $objEntry->getEntries(null,null,null,null,null,null,true);
        $objEntry->listEntries($this->_objTpl, 4);
    }



    function showLatest()
    {
        global $_ARRAYLANG, $_CORELANG;

        $this->_objTpl->setTemplate($this->pageContent, true, true);

        //get searchform
        if($this->_objTpl->blockExists('mediadirSearchform')){
            $objSearch = new mediaDirectorySearch();
            $objSearch->getSearchform($this->_objTpl, 1);
        }

        $objEntry = new mediaDirectoryEntry();
        $objEntry->getEntries(null, null, null, null, true, null, true, null, $this->arrSettings['settingsLatestNumFrontend']);
        $objEntry->listEntries($this->_objTpl, 2);
    }



    function getHeadlines($arrExistingBlocks)
    {
        global $_ARRAYLANG, $_CORELANG, $objTemplate;

        $objEntry = new mediaDirectoryEntry();
        $objEntry->getEntries(null, null, null, null, null, null, true, null, $this->arrSettings['settingsLatestNumHeadlines']);

        $i=0;
        $r=0;
        $numBlocks = count($arrExistingBlocks);

        if(!empty($objEntry->arrEntries)){
            foreach ($objEntry->arrEntries as $key => $arrEntry) {

                if($objEntry->checkPageCmd('detail'.intval($arrEntry['entryFormId']))) {
                    $strDetailCmd = 'detail'.intval($arrEntry['entryFormId']);
                } else {
                    $strDetailCmd = 'detail';
                }

                $objTemplate->setVariable(array(
                    'MEDIADIR_LATEST_ROW_CLASS' =>  $r%2==0 ? 'row1' : 'row2',
                    'MEDIADIR_LATEST_ENTRY_ID' =>  $arrEntry['entryId'],
                    'MEDIADIR_LATEST_ENTRY_VALIDATE_DATE' =>  date("H:i:s - d.m.Y",$arrEntry['entryValdateDate']),
                    'MEDIADIR_LATEST_ENTRY_CREATE_DATE' =>  date("H:i:s - d.m.Y",$arrEntry['entryCreateDate']),
                    'MEDIADIR_LATEST_ENTRY_HITS' =>  $arrEntry['entryHits'],
                    'MEDIADIR_ENTRY_DETAIL_URL' =>  'index.php?section=mediadir&amp;cmd='.$strDetailCmd.'&amp;eid='.$arrEntry['entryId'],
                    'TXT_MEDIADIR_ENTRY_DETAIL' =>  $_CORELANG['TXT_DETAIL'],
                ));

                foreach ($arrEntry['entryFields'] as $key => $strFieldValue) {
                    $intPos = $key+1;

                    $objTemplate->setVariable(array(
                        'MEDIADIR_LATEST_ENTRY_FIELD_'.$intPos.'_POS' => $strFieldValue
                    ));
                }

                $blockId = $arrExistingBlocks[$i];
                $objTemplate->parse('mediadirLatest_row_'.$blockId);
                if ($i < $numBlocks-1) {
                    ++$i;
                } else {
                    $i = 0;
                }
            }
        }
    }



    function showPopular()
    {
        global $_ARRAYLANG, $_CORELANG;

        $this->_objTpl->setTemplate($this->pageContent, true, true);

        //get searchform
        if($this->_objTpl->blockExists('mediadirSearchform')){
            $objSearch = new mediaDirectorySearch();
            $objSearch->getSearchform($this->_objTpl, 1);
        }

        $objEntry = new mediaDirectoryEntry();
        $objEntry->getEntries(null, null, null, null, null, null, true, null, $this->arrSettings['settingsPopularNumFrontend'], null, true);
        $objEntry->listEntries($this->_objTpl, 2);
    }



    function modifyEntry()
    {
        global $_ARRAYLANG, $_CORELANG;

        $this->_objTpl->setTemplate($this->pageContent, true, true);

        //check id
        if(isset($_GET['eid']) && $_GET['eid'] != 0) {
            $intEntryId = intval($_GET['eid']);
        } else {
            $intEntryId = null;
        }

        //count forms
        $objForms = new mediaDirectoryForm();
        $arrActiveForms = array();

        foreach ($objForms->arrForms as $intFormId => $arrForm) {
            if($arrForm['formActive'] == 1) {
                $arrActiveForms[] = $intFormId;
            }
        }

        $intCountForms = count($arrActiveForms);

        if($intCountForms > 0){
            //check form
            if(intval($intEntryId) == 0 && (intval($_REQUEST['selectedFormId']) == 0 && intval($_POST['formId']) == 0) && $intCountForms > 1) {
                $intFormId = null;

                //get form selector
                $objForms = new mediaDirectoryForm();
                $objForms->listForms($this->_objTpl, 3, $intFormId);

                //parse blocks
                $this->_objTpl->hideBlock('mediadirInputfields');
            } else {
                //save entry data
                if(isset($_POST['submitEntryModfyForm'])) {
                    $objEntry = new mediaDirectoryEntry();
                    $strStatus = $objEntry->saveEntry($_POST, intval($_POST['entryId']));

                    if(!empty($_POST['entryId'])) {
                        if($strStatus == true) {
                            $strOkMessage = $_ARRAYLANG['TXT_MEDIADIR_ENTRY']." ".$_ARRAYLANG['TXT_MEDIADIR_SUCCESSFULLY_EDITED'];
                        } else {
                            $strErrMessage = $_ARRAYLANG['TXT_MEDIADIR_ENTRY']." ".$_ARRAYLANG['TXT_MEDIADIR_CORRUPT_EDITED'];
                        }
                    } else {
                        if($strStatus == true) {
                            $strOkMessage = $_ARRAYLANG['TXT_MEDIADIR_ENTRY']." ".$_ARRAYLANG['TXT_MEDIADIR_SUCCESSFULLY_ADDED'];
                        } else {
                            $strErrMessage = $_ARRAYLANG['TXT_MEDIADIR_ENTRY']." ".$_ARRAYLANG['TXT_MEDIADIR_CORRUPT_ADDED'];
                        }
                    }
                } else {
                    //get form id
                    if(intval($intEntryId) != 0) {
                        //get entry data
                        $objEntry = new mediaDirectoryEntry();
                        $objEntry->getEntries($intEntryId);
                        $intFormId = $objEntry->arrEntries[$intEntryId]['entryFormId'];
                    } else {
                         //set form id
                        if($intCountForms == 1) {
                            $intFormId = intval($arrActiveForms[0]);
                        } else {
                            $intFormId = intval($_REQUEST['selectedFormId']);
                        }
                    }

                    //get inputfield object
                    $objInputfields = new mediaDirectoryInputfield($intFormId);

                    //list inputfields
                    $objInputfields->listInputfields($this->_objTpl, 2, $intEntryId);

                    //generate javascript
                    parent::setJavascript($this->getSelectorJavascript());
                    parent::setJavascript($objInputfields->getInputfieldJavascript());

                    //get form onsubmit
                    $strOnSubmit = $this->getFormOnSubmit();

                    //parse blocks
                    $this->_objTpl->hideBlock('mediadirForms');
                }
            }

            //parse global variables
            $this->_objTpl->setVariable(array(
                'MEDIADIR_ENTRY_ID' => $intEntryId,
                'MEDIADIR_FORM_ID' => $intFormId,
                'TXT_MEDIADIR_SUBMIT' =>  $_CORELANG['TXT_SAVE'],
                'MEDIADIR_FORM_ONSUBMIT' =>  $strOnSubmit,
                'TXT_MEDIADIR_PLEASE_CHECK_INPUT' =>  $_ARRAYLANG['TXT_MEDIADIR_PLEASE_CHECK_INPUT'],
                'TXT_MEDIADIR_OK_MESSAGE' =>  $strOkMessage,
                'TXT_MEDIADIR_ERROR_MESSAGE' =>  $strErrMessage,
                'MEDIADIR_MAX_LEVEL_SELECT' =>  $this->arrSettings[''],
                'MEDIADIR_MAX_CATEGORY_SELECT' =>  $strErrMessage,
            ));

            if(!empty($strOkMessage)) {
                $this->_objTpl->parse('mediadirEntryOkMessage');
                $this->_objTpl->hideBlock('mediadirEntryErrMessage');
                $this->_objTpl->hideBlock('mediadirEntryModifyForm');
            } else if(!empty($strErrMessage)) {
                $this->_objTpl->hideBlock('mediadirEntryOkMessage');
                $this->_objTpl->parse('mediadirEntryErrMessage');
                $this->_objTpl->hideBlock('mediadirEntryModifyForm');
            } else {
                $this->_objTpl->hideBlock('mediadirEntryOkMessage');
                $this->_objTpl->hideBlock('mediadirEntryErrMessage');
                $this->_objTpl->parse('mediadirEntryModifyForm');
            }
        } else {
			header("Location: index.php?section=".$_GET['section']);
			exit;
        }

    }



    function deleteEntry()
    {
        global $_ARRAYLANG, $_CORELANG;

        $this->_objTpl->setTemplate($this->pageContent, true, true);

        //save entry data
        if(isset($_POST['submitEntryModfyForm']) && intval($_POST['entryId'])) {
            $objEntry = new mediaDirectoryEntry();
            $strStatus = $objEntry->deleteEntry(intval($_POST['entryId']));

            if($strStatus == true) {
                $strOkMessage = $_ARRAYLANG['TXT_MEDIADIR_ENTRY']." ".$_ARRAYLANG['TXT_MEDIADIR_SUCCESSFULLY_DELETED'];
            } else {
                $strErrMessage = $_ARRAYLANG['TXT_MEDIADIR_ENTRY']." ".$_ARRAYLANG['TXT_MEDIADIR_CORRUPT_DELETED'];
            }
        }

         //check id
        if(intval($_GET['eid']) != 0) {
            $intEntryId = intval($_GET['eid']);
        } else {
            $intEntryId = null;
        }

        $objEntry = new mediaDirectoryEntry();
        $objEntry->getEntries($intEntryId,null,null,null,null,null,1,null,1);
        $objEntry->listEntries($this->_objTpl, 2);

        //parse global variables
        $this->_objTpl->setVariable(array(
            'MEDIADIR_ENTRY_ID' => $intEntryId,
            'TXT_MEDIADIR_DELETE' =>  $_CORELANG['TXT_ACCESS_DELETE_ENTRY'],
            'TXT_MEDIADIR_ABORT' =>  $_CORELANG['TXT_CANCEL'],
            'TXT_MEDIADIR_OK_MESSAGE' =>  $strOkMessage,
            'TXT_MEDIADIR_ERROR_MESSAGE' =>  $strErrMessage,
        ));

        if(!empty($strOkMessage)) {
            $this->_objTpl->parse('mediadirEntryOkMessage');
            $this->_objTpl->hideBlock('mediadirEntryErrMessage');
            $this->_objTpl->hideBlock('mediadirEntryModifyForm');
        } else if(!empty($strErrMessage)) {
            $this->_objTpl->hideBlock('mediadirEntryOkMessage');
            $this->_objTpl->parse('mediadirEntryErrMessage');
            $this->_objTpl->parse('mediadirEntryModifyForm');
        } else {
            $this->_objTpl->hideBlock('mediadirEntryOkMessage');
            $this->_objTpl->hideBlock('mediadirEntryErrMessage');
            $this->_objTpl->parse('mediadirEntryModifyForm');
        }
    }



    function getNavtree($intCategoryId, $intLevelId)
    {
        global $_ARRAYLANG;

        if($intCategoryId != 0) {
           $this->getNavtreeCategories($intCategoryId);
        }

        if($intLevelId != 0 && $this->arrSettings['settingsShowLevels'] == 1) {
           $this->getNavtreeLevels($intLevelId);
        }

        //set pagetitle
        krsort($this->arrNavtree);
        $this->metaTitle = $this->pageTitle." - ".strip_tags(join(" - ", $this->arrNavtree));

        if(isset($_GET['cmd'])) {
            $strOverviewCmd = '&amp;cmd='.$_GET['cmd'];
        } else {
            $strOverviewCmd = null;
        }

        $this->arrNavtree[] = '<a href="?section=mediadir'.$strOverviewCmd.'">'.$_ARRAYLANG['TXT_MEDIADIR_OVERVIEW'].'</a>';
        krsort($this->arrNavtree);

        if(!empty($this->arrNavtree)) {
            $i = 0;
            foreach ($this->arrNavtree as $key => $strName) {
                $strClass = $i == 0 ? 'class="first_navtree_element"' : '';

                $this->_objTpl->setVariable(array(
                    'MEDIADIR_NAVTREE_LINK'    =>  $strName,
                    'MEDIADIR_NAVTREE_LINK_CLASS'    =>  $strClass
                ));

                $i++;
                $this->_objTpl->parse('mediadirNavtreeElement');
            }
            $this->_objTpl->parse('mediadirNavtree');
        } else {
            $this->_objTpl->hideBlock('mediadirNavtree');
        }
    }



    function getNavtreeCategories($intCategoryId)
    {
        $objCategory = new mediaDirectoryCategory($intCategoryId, null, 0);
        $objCategory->arrCategories[$intCategoryId];

        $strLevelId = isset($_GET['lid']) ? "&amp;lid=".intval($_GET['lid']) : '';
        if(isset($_GET['cmd'])) {
            $strCategoryCmd = '&amp;cmd='.$_GET['cmd'];
        } else {
            $strCategoryCmd = null;
        }
        $this->arrNavtree[] = '<a href="?section=mediadir'.$strCategoryCmd.$strLevelId.'&amp;cid='.$objCategory->arrCategories[$intCategoryId]['catId'].'">'.$objCategory->arrCategories[$intCategoryId]['catName'][0].'</a>';

        if($objCategory->arrCategories[$intCategoryId]['catParentId'] != 0) {
            $this->getNavtreeCategories($objCategory->arrCategories[$intCategoryId]['catParentId']);
        }
    }



    function getNavtreeLevels($intLevelId)
    {
        $objLevel = new mediaDirectoryLevel($intLevelId, null, 0);
        $objLevel->arrLevels[$intLevelId];

        if(isset($_GET['cmd'])) {
            $strLevelCmd = '&amp;cmd='.$_GET['cmd'];
        } else {
            $strLevelCmd = null;
        }
        $this->arrNavtree[] = '<a href="?section=mediadir'.$strLevelCmd.'&amp;lid='.$objLevel->arrLevels[$intLevelId]['levelId'].'">'.$objLevel->arrLevels[$intLevelId]['levelName'][0].'</a>';

        if($objLevel->arrLevels[$intLevelId]['levelParentId'] != 0) {
            $this->getNavtreeLevels($objLevel->arrLevels[$intLevelId]['levelParentId']);
        }
    }


    function getPageTitle() {
        return $this->pageTitle;
    }

    function getMetaTitle() {
        return $this->metaTitle;
    }
}
?>