<?php

/**
 * Block
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.1
 * @package     contrexx
 * @subpackage  module_block
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once ASCMS_MODULE_PATH.'/block/lib/blockLib.class.php';
require_once ASCMS_CORE_PATH.'/Tree.class.php';

/**
 * Block
 *
 * block module class
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @access      public
 * @version     1.0.1
 * @package     contrexx
 * @subpackage  module_block
 */
class blockManager extends blockLibrary
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
    * Okay message
    *
    * @access private
    * @var string
    */
    var $_strOkMessage = '';

    /**
    * error message
    *
    * @access private
    * @var string
    */
    var $_strErrMessage = '';

    /**
     * row class index
     *
     * @var integer
     */
    var $_index = 0;

    /**
    * Constructor
    */
    function blockManager()
    {
        $this->__construct();
    }

    /**
    * PHP5 constructor
    *
    * @global HTML_Template_Sigma
    * @global array
    * @global array
    * @global array
    */
    function __construct()
    {
        global $objTemplate, $_ARRAYLANG, $_CORELANG, $_CONFIG;

        $this->_objTpl = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/block/template');
        CSRF::add_placeholder($this->_objTpl);
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);

        if (isset($_POST['saveSettings'])) {
            $arrSettings = array(
                'blockStatus'   => isset($_POST['blockUseBlockSystem']) ? intval($_POST['blockUseBlockSystem']) : 0,
                'blockRandom'   => isset($_POST['blockUseBlockRandom']) ? intval($_POST['blockUseBlockRandom']) : 0
            );
            $this->_saveSettings($arrSettings);
            $this->_strOkMessage = $_CORELANG['TXT_SETTINGS_UPDATED'];
        }

        $objTemplate->setVariable("CONTENT_NAVIGATION", "   "
            .($_CONFIG['blockStatus'] == '1'
                 ? "<a href='index.php?cmd=block&amp;act=overview'>".$_ARRAYLANG['TXT_BLOCK_OVERVIEW']."</a>
                    <a href='index.php?cmd=block&amp;act=modify'>".$_ARRAYLANG['TXT_BLOCK_ADD_BLOCK']."</a>"
                 : "")
            ."<a href='index.php?cmd=block&amp;act=categories'>".$_ARRAYLANG['TXT_BLOCK_CATEGORIES']."</a>"
     	    ."<a href='index.php?cmd=block&amp;act=settings'>".$_ARRAYLANG['TXT_BLOCK_SETTINGS']."</a>");
    }

    /**
    * Get page
    *
    * Get a page of the block system administration
    *
    * @access public
    * @global HTML_Template_Sigma
    * @global array
    */
    function getPage()
    {
        global $objTemplate, $_CONFIG;

        if (!isset($_REQUEST['act'])) {
            $_REQUEST['act'] = '';
        }

        if ($_CONFIG['blockStatus'] != '1') {
            $_REQUEST['act'] = 'settings';
        }

        JS::activate('jquery');

        switch ($_REQUEST['act']) {
        case 'modify':
            $this->_showModifyBlock();
            break;

        case 'copy':
            $this->_showModifyBlock(true);
            break;

        case 'settings':
            $this->_showSettings();
            break;

        case 'del':
            $this->_delBlock();
            $this->_showOverview();
            break;

        case 'activate':
            $this->_activateBlock();
            $this->_showOverview();
            break;

        case 'deactivate':
            $this->_deactivateBlock();
            $this->_showOverview();
            break;

        case 'random':
            $this->_randomizeBlock();
            $this->_showOverview();
            break;

        case 'random_off':
            $this->_randomizeBlockOff();
            $this->_showOverview();
            break;

        case 'global':
            $this->_globalBlock();
            $this->_showOverview();
            break;

        case 'global_off':
            $this->_globalBlockOff();
            $this->_showOverview();
            break;
        case 'categories':
            if(!empty($_POST['frmCategorySubmit'])){
                $this->saveCategory();
            }
            $this->showCategories();
            break;
        case 'editCategory':
            $this->editCategory();
            break;
        case 'deleteCategory':
            $this->deleteCategory();
            $this->showCategories();
            break;
        case 'multiactionCategory':
            $this->doEntryMultiAction($_REQUEST['frmShowCategoriesMultiAction']);
            $this->showCategories();
            break;
        default:
            $this->_showOverview();
            break;
        }

        $objTemplate->setVariable(array(
            'CONTENT_TITLE'             => $this->_pageTitle,
            'CONTENT_OK_MESSAGE'        => $this->_strOkMessage,
            'CONTENT_STATUS_MESSAGE'    => $this->_strErrMessage,
            'ADMIN_CONTENT'             => $this->_objTpl->get()
        ));
    }

    /**
    * Show overview
    *
    * Show the blocks overview page
    *
    * @access private
    * @global array
    * @global ADONewConnection
    * @global array
    * @see blockLibrary::getBlocks(), blockLibrary::blockNamePrefix
    */
    function _showOverview()
    {
        global $_ARRAYLANG, $objDatabase, $_CORELANG;

        if (isset($_POST['displaysubmit'])) {
            foreach ($_POST['displayorder'] as $blockId => $value){
                $query = "UPDATE ".DBPREFIX."module_block_blocks SET `order`='".intval($value)."' WHERE id='".intval($blockId)."'";
                $objDatabase->Execute($query);
            }
        }

        $this->_pageTitle = $_ARRAYLANG['TXT_BLOCK_BLOCKS'];
        $this->_objTpl->loadTemplateFile('module_block_overview.html');

        $catId = !empty($_REQUEST['catId']) ? intval($_REQUEST['catId']) : 0;

        $this->_objTpl->setVariable(array(
            'TXT_BLOCK_BLOCKS'                  => $_ARRAYLANG['TXT_BLOCK_BLOCKS'],
            'TXT_BLOCK_NAME'                    => $_ARRAYLANG['TXT_BLOCK_NAME'],
            'TXT_BLOCK_PLACEHOLDER'             => $_ARRAYLANG['TXT_BLOCK_PLACEHOLDER'],
            'TXT_BLOCK_SUBMIT_SELECT'           => $_ARRAYLANG['TXT_BLOCK_SUBMIT_SELECT'],
            'TXT_BLOCK_SUBMIT_DELETE'           => $_ARRAYLANG['TXT_BLOCK_SUBMIT_DELETE'],
            'TXT_BLOCK_SUBMIT_ACTIVATE'         => $_ARRAYLANG['TXT_BLOCK_SUBMIT_ACTIVATE'],
            'TXT_BLOCK_SUBMIT_DEACTIVATE'       => $_ARRAYLANG['TXT_BLOCK_SUBMIT_DEACTIVATE'],
            'TXT_BLOCK_SUBMIT_RANDOM'           => $_ARRAYLANG['TXT_BLOCK_SUBMIT_RANDOM'],
            'TXT_BLOCK_SUBMIT_RANDOM_OFF'       => $_ARRAYLANG['TXT_BLOCK_SUBMIT_RANDOM_OFF'],
            'TXT_BLOCK_SUBMIT_GLOBAL'           => $_ARRAYLANG['TXT_BLOCK_SUBMIT_GLOBAL'],
            'TXT_BLOCK_SUBMIT_GLOBAL_OFF'       => $_ARRAYLANG['TXT_BLOCK_SUBMIT_GLOBAL_OFF'],
            'TXT_BLOCK_SELECT_ALL'              => $_ARRAYLANG['TXT_BLOCK_SELECT_ALL'],
            'TXT_BLOCK_DESELECT_ALL'            => $_ARRAYLANG['TXT_BLOCK_DESELECT_ALL'],
            'TXT_BLOCK_RANDOM'                  => $_ARRAYLANG['TXT_BLOCK_RANDOM'],
            'TXT_BLOCK_PLACEHOLDER'             => $_ARRAYLANG['TXT_BLOCK_PLACEHOLDER'],
            'TXT_BLOCK_FUNCTIONS'               => $_ARRAYLANG['TXT_BLOCK_FUNCTIONS'],
            'TXT_BLOCK_DELETE_SELECTED_BLOCKS'  => $_ARRAYLANG['TXT_BLOCK_DELETE_SELECTED_BLOCKS'],
            'TXT_BLOCK_CONFIRM_DELETE_BLOCK'    => $_ARRAYLANG['TXT_BLOCK_CONFIRM_DELETE_BLOCK'],
            'TXT_SAVE_CHANGES'                  => $_CORELANG['TXT_SAVE_CHANGES'],
            'TXT_BLOCK_OPERATION_IRREVERSIBLE'  => $_ARRAYLANG['TXT_BLOCK_OPERATION_IRREVERSIBLE'],
            'TXT_BLOCK_STATUS'                  => $_ARRAYLANG['TXT_BLOCK_STATUS'],
            'TXT_BLOCK_CATEGORY'                => $_ARRAYLANG['TXT_BLOCK_CATEGORY'],
            'TXT_BLOCK_CATEGORIES_ALL'          => $_ARRAYLANG['TXT_BLOCK_CATEGORIES_ALL'],
            'BLOCK_CATEGORIES_DROPDOWN'         => $this->_getCategoriesDropdown($catId),
            'DIRECTORY_INDEX'                   => CONTREXX_DIRECTORY_INDEX,
            'CSRF_KEY'                          => CSRF::key(),
            'CSRF_CODE'                         => CSRF::code(),
        ));

        $arrBlocks = $this->getBlocks($catId);
        if (count($arrBlocks)>0) {
            $rowNr = 0;
            foreach ($arrBlocks as $blockId => $arrBlock) {
                if ($arrBlock['active'] ==  '1') {
                    $status = "<a href='index.php?cmd=block&amp;act=deactivate&amp;blockId=".$blockId."' title='".$_ARRAYLANG['TXT_BLOCK_ACTIVE']."'><img src='images/icons/led_green.gif' width='13' height='13' border='0' alt='".$_ARRAYLANG['TXT_BLOCK_ACTIVE']."' /></a>";
                }else{
                    $status = "<a href='index.php?cmd=block&amp;act=activate&amp;blockId=".$blockId."' title='".$_ARRAYLANG['TXT_BLOCK_INACTIVE']."'><img src='images/icons/led_red.gif' width='13' height='13' border='0' alt='".$_ARRAYLANG['TXT_BLOCK_INACTIVE']."' /></a>";
                }

                if ($arrBlock['random'] ==  '1') {
                    $random = "<img src='images/icons/refresh.gif' width='16' height='16' border='0' alt='random 1' title='random 1' />";
                } else {
                    $random = "<img src='images/icons/pixel.gif' width='16' height='16' border='0' alt='' title='' />";
                }

                if ($arrBlock['random2'] ==  '1') {
                    $random2 = "<img src='images/icons/refresh2.gif' width='16' height='16' border='0' alt='random 2' title='random 2' />";
                } else {
                    $random2 = "<img src='images/icons/pixel.gif' width='16' height='16' border='0' alt='' title='' />";
                }

                if ($arrBlock['random3'] ==  '1') {
                    $random3 = "<img src='images/icons/refresh3.gif' width='16' height='16' border='0' alt='random 3' title='random 3' />";
                } else {
                    $random3 = "<img src='images/icons/pixel.gif' width='16' height='16' border='0' alt='' title='' />";
                }

                if ($arrBlock['random4'] ==  '1') {
                    $random3 = "<img src='images/icons/refresh.gif' width='16' height='16' border='0' alt='random 4' title='random 4' />";
                } else {
                    $random3 = "<img src='images/icons/pixel.gif' width='16' height='16' border='0' alt='' title='' />";
                }

                if ($arrBlock['global'] ==  '1') {
                    $global = "<img src='images/icons/upload.gif' width='16' height='16' border='0' alt='upload' title='upload' /> />";
                } else {
                    $global = "&nbsp;";
                }

                $this->_objTpl->setVariable(array(
                    'BLOCK_ROW_CLASS'       => $rowNr % 2 ? "row1" : "row2",
                    'BLOCK_ID'              => $blockId,
                    'BLOCK_RANDOM'          => $random,
                    'BLOCK_RANDOM_2'        => $random2,
                    'BLOCK_RANDOM_3'        => $random3,
                    'BLOCK_CATEGORY_NAME'   => $this->_categoryNames[$arrBlock['cat']],
                    'BLOCK_GLOBAL'          => $global,
                    'BLOCK_ORDER'           => $arrBlock['order'],
                    'BLOCK_PLACEHOLDER'     => $this->blockNamePrefix.$blockId,
                    'BLOCK_NAME'            => contrexx_raw2xhtml($arrBlock['name']),
                    'BLOCK_MODIFY'          => sprintf($_ARRAYLANG['TXT_BLOCK_MODIFY_BLOCK'], contrexx_raw2xhtml($arrBlock['name'])),
                    'BLOCK_COPY'            => sprintf($_ARRAYLANG['TXT_BLOCK_COPY_BLOCK'], contrexx_raw2xhtml($arrBlock['name'])),
                    'BLOCK_DELETE'          => sprintf($_ARRAYLANG['TXT_BLOCK_DELETE_BLOCK'], contrexx_raw2xhtml($arrBlock['name'])),
                    'BLOCK_STATUS'          => $status
                ));
                $this->_objTpl->parse('blockBlockList');

                $rowNr ++;
            }
        }
    }

    /**
     * show the categories
     *
     * @global array module language array
     */
    function showCategories()
    {
        global $_ARRAYLANG;

        $catId = !empty($_REQUEST['catId']) ? intval($_REQUEST['catId']) : 0;
        $this->_pageTitle = $_ARRAYLANG['TXT_BLOCK_CATEGORIES'];
        $this->_objTpl->loadTemplateFile('module_block_categories.html');

        $this->_objTpl->setVariable(array(
            'TXT_BLOCK_CATEGORIES'                  => $_ARRAYLANG['TXT_BLOCK_CATEGORIES'],
            'TXT_BLOCK_CATEGORIES_MANAGE'           => $_ARRAYLANG['TXT_BLOCK_CATEGORIES_MANAGE'],
            'TXT_BLOCK_CATEGORIES_ADD'              => $_ARRAYLANG['TXT_BLOCK_CATEGORIES_ADD'],
            'TXT_BLOCK_FUNCTIONS'                   => $_ARRAYLANG['TXT_BLOCK_FUNCTIONS'],
            'TXT_BLOCK_NAME'                        => $_ARRAYLANG['TXT_BLOCK_NAME'],
            'TXT_BLOCK_NONE'                        => $_ARRAYLANG['TXT_BLOCK_NONE'],
            'TXT_BLOCK_PARENT'                      => $_ARRAYLANG['TXT_BLOCK_PARENT'],
            'TXT_BLOCK_SELECT_ALL'                  => $_ARRAYLANG['TXT_BLOCK_SELECT_ALL'],
            'TXT_BLOCK_DESELECT_ALL'                => $_ARRAYLANG['TXT_BLOCK_DESELECT_ALL'],
            'TXT_BLOCK_SUBMIT_SELECT'               => $_ARRAYLANG['TXT_BLOCK_SUBMIT_SELECT'],
            'TXT_BLOCK_SUBMIT_DELETE'               => $_ARRAYLANG['TXT_BLOCK_SUBMIT_DELETE'],
            'TXT_BLOCK_NO_CATEGORIES_FOUND'         => $_ARRAYLANG['TXT_BLOCK_NO_CATEGORIES_FOUND'],
            'TXT_BLOCK_OPERATION_IRREVERSIBLE'      => $_ARRAYLANG['TXT_BLOCK_OPERATION_IRREVERSIBLE'],
            'BLOCK_CATEGORIES_PARENT_DROPDOWN'      => $this->_getCategoriesDropdown(),
            'DIRECTORY_INDEX'                       => CONTREXX_DIRECTORY_INDEX,
            'CSRF_KEY'                              => CSRF::key(),
            'CSRF_CODE'                             => CSRF::code(),
        ));

        $arrCategories = $this->_getCategories(true);
        if(count($arrCategories) == 0){
            $this->_objTpl->touchBlock('noCategories');
            return;
        }

        $this->_objTpl->hideBlock('noCategories');
        $this->_parseCategories($arrCategories[0]);  //first array contains all root categories (parent id 0)
    }

    function deleteCategory()
    {
        global $_ARRAYLANG;

        if($this->_deleteCategory($_REQUEST['id'])){
            $this->_strOkMessage  = $_ARRAYLANG['TXT_BLOCK_CATEGORIES_DELETE_OK'];
        } else {
            $this->_strErrMessage = $_ARRAYLANG['TXT_BLOCK_CATEGORIES_DELETE_ERROR'];
        }
    }

    /**
     * recursively parse the categories
     *
     * @param array $arrCategories
     * @param integer $level
     * @param integer $index
     */
    function _parseCategories($arrCategories, $level = 0)
    {
        foreach ($arrCategories as $parentId => $arrCategory) {
            $this->_objTpl->setVariable(array(
                'BLOCK_CATEGORY_ROWCLASS'   => $this->_index++ % 2 == 0 ? 'row1' : 'row2',
                'BLOCK_CATEGORY_ID'         => $arrCategory['id'],
                'BLOCK_CATEGORY_NAME'       => str_repeat('&nbsp;', $level*4).$arrCategory['name'],
            ));

            if(empty($this->_categories[$arrCategory['id']])){
                $this->_objTpl->touchBlock('deleteCategory');
                $this->_objTpl->touchBlock('checkboxCategory');
            } else {
                $this->_objTpl->touchBlock('deleteCategoryEmpty');
            }

            $this->_objTpl->parse('showCategories');
            if(!empty($this->_categories[$arrCategory['id']])){
                $this->_parseCategories($this->_categories[$arrCategory['id']], $level+1);
            }
        }
    }

    /**
     * prepare and show the edit category page
     *
     */
    function editCategory()
    {
        global $_ARRAYLANG, $_CORELANG;

        $catId = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
        $this->_pageTitle = $_ARRAYLANG['TXT_BLOCK_CATEGORIES_EDIT'];
        $this->_objTpl->loadTemplateFile('module_block_categories_edit.html');

        $arrCategory = $this->_getCategory($_GET['id']);

        $this->_objTpl->setVariable(array(
            'TXT_BLOCK_NAME'                        => $_ARRAYLANG['TXT_BLOCK_NAME'],
            'TXT_BLOCK_SAVE'                        => $_ARRAYLANG['TXT_BLOCK_SAVE'],
            'TXT_BLOCK_PARENT'                      => $_ARRAYLANG['TXT_BLOCK_PARENT'],
            'TXT_BLOCK_NONE'                        => $_ARRAYLANG['TXT_BLOCK_NONE'],
            'TXT_BLOCK_CATEGORIES_EDIT'             => $_ARRAYLANG['TXT_BLOCK_CATEGORIES_EDIT'],
            'TXT_BLOCK_BACK'                        => $_CORELANG['TXT_BACK'],
            'BLOCK_CATEGORY_ID'                     => $catId,
            'BLOCK_CATEGORIES_PARENT_DROPDOWN'      => $this->_getCategoriesDropdown($arrCategory['parent'], $catId),
            'BLOCK_CATEGORY_NAME'                   => $arrCategory['name'],
            'DIRECTORY_INDEX'                       => CONTREXX_DIRECTORY_INDEX,
            'CSRF_KEY'                              => CSRF::key(),
            'CSRF_CODE'                             => CSRF::code(),
        ));
    }


    /**
    * Performs the action for the dropdown-selection on the entry page.
    *
    * @param string $strAction: the action passed by the formular.
    */
    function doEntryMultiAction($strAction='')
    {
        global $_ARRAYLANG;

        $success = true;
        switch ($strAction) {
            case 'delete':
                foreach($_REQUEST['selectedCategoryId'] as $intEntryId) {
                    if(!$this->_deleteCategory($intEntryId)){
                        $success = false;
                    }
                }
                if(!$success){
                    $this->_strErrMessage = $_ARRAYLANG['TXT_BLOCK_CATEGORIES_DELETE_ERROR'];
                } else {
                    $this->_strOkMessage = $_ARRAYLANG['TXT_BLOCK_CATEGORIES_DELETE_OK'];
                }
                break;
            default:
                //do nothing!
        }
    }

    /**
     * saves a category
     *
     */
    function saveCategory()
    {
        global $_ARRAYLANG;

        $id     = !empty($_POST['frmCategoryId'    ])    ? $_POST['frmCategoryId'    ]    : 0;
        $parent = !empty($_POST['frmCategoryParent'])    ? $_POST['frmCategoryParent']    : 0;
        $name   = !empty($_POST['frmCategoryName'  ])    ? $_POST['frmCategoryName'  ]    : '-';
        $order  = !empty($_POST['frmCategoryOrder' ])    ? $_POST['frmCategoryOrder' ]    : 1;
        $status = !empty($_POST['frmCategoryStatus'])    ? $_POST['frmCategoryStatus']    : 1;

        if($this->_saveCategory($id, $parent, $name, $order, $status)){
            $this->_strOkMessage  = $_ARRAYLANG['TXT_BLOCK_CATEGORIES_ADD_OK'];
        } else {
            $this->_strErrMessage = $_ARRAYLANG['TXT_BLOCK_CATEGORIES_ADD_ERROR'];
        }
    }

    /**
     * parse the date and time from the form submit and convert into a timestamp
     *
     * @param string nameprefix of the form fields (${name}Date,${name}Hour,${name}Minute)
     * @return integer timestamps
     */
    function _parseTimestamp($name)
    {
        $date    = $_POST[$name.'Date'];
        $hour    = $_POST[$name.'Hour'];
        $minutes = $_POST[$name.'Minute'];
        $timestamp = strtotime("$date $hour:$minutes:00");

        return $timestamp !== false
            ? $timestamp
            : time();
    }

    /**
     * parses the hours dropdown
     *
     * @param integer $date selects the options according to timestamp $date
     * @return void
     */
    function _parseHours($date)
    {
        $options = array();
        for($hour = 0; $hour <= 23; $hour++){
            $selected = '';
            $hourFmt = sprintf('%02d', $hour);
            if($hourFmt == date('H', $date)){
                $selected = 'selected="selected"';
            }
            $options[] = '<option value="'.$hourFmt.'" '.$selected.'>'.$hourFmt.'</option>';
        }
        return implode('\n', $options);
    }

    /**
     * parses the minutes dropdown
     *
     * @param integer $date selects the options according to timestamp $date
     * @return void
     */
    function _parseMinutes($date)
    {
        $options = array();
        for($minute = 0; $minute <= 59; $minute++){
            $selected = '';
            $minuteFmt = sprintf('%02d', $minute);
            if($minuteFmt == date('i', $date)){
                $selected = 'selected="selected"';
            }
            $options[] = '<option value="'.$minuteFmt.'" '.$selected.'>'.$minuteFmt.'</option>';
        }
        return implode('\n', $options);
    }

    /**
    * Show modify block
    *
    * Show the block modification page
    *
    * @access private
    * @global array
    * @see blockLibrary::_getBlockContent(), blockLibrary::blockNamePrefix
    */
    private function _showModifyBlock($copy = false)
    {
        global $_ARRAYLANG;
        
        JS::activate('cx');

        $blockId                = !empty($_REQUEST['blockId']) ? intval($_REQUEST['blockId']) : 0;
        $blockCat               = 0;
        $blockName              = '';
        $blockStart             = time();
        $blockEnd               = time()+3600*24*365;
        $blockRandom            = 0;
        $blockRandom2           = 0;
        $blockRandom3           = 0;
        $blockRandom4           = 0;
        $blockGlobal            = 0;
        $blockContent           = array();
        $blockAssociatedPageIds = array();
        $blockLangActive        = array();

        $this->_objTpl->loadTemplateFile('module_block_modify.html');
        
        $this->_objTpl->setGlobalVariable(array(
            'TXT_BLOCK_CONTENT'                 => $_ARRAYLANG['TXT_BLOCK_CONTENT'],
            'TXT_BLOCK_NAME'                    => $_ARRAYLANG['TXT_BLOCK_NAME'],
            'TXT_BLOCK_RANDOM'                  => $_ARRAYLANG['TXT_BLOCK_RANDOM'],
            'TXT_BLOCK_GLOBAL'                  => $_ARRAYLANG['TXT_BLOCK_SHOW_IN_GLOBAL'],
            'TXT_BLOCK_SAVE'                    => $_ARRAYLANG['TXT_BLOCK_SAVE'],
            'TXT_BLOCK_DEACTIVATE'              => $_ARRAYLANG['TXT_BLOCK_DEACTIVATE'],
            'TXT_BLOCK_ACTIVATE'                => $_ARRAYLANG['TXT_BLOCK_ACTIVATE'],
            'TXT_SHOW_ON_ALL_PAGES'             => $_ARRAYLANG['TXT_SHOW_ON_ALL_PAGES'],
            'TXT_SHOW_ON_SELECTED_PAGES'        => $_ARRAYLANG['TXT_SHOW_ON_SELECTED_PAGES'],
            'TXT_BLOCK_CATEGORY'                => $_ARRAYLANG['TXT_BLOCK_CATEGORY'],
            'TXT_BLOCK_NONE'                    => $_ARRAYLANG['TXT_BLOCK_NONE'],
            'TXT_BLOCK_SHOW_FROM'               => $_ARRAYLANG['TXT_BLOCK_SHOW_FROM'],
            'TXT_BLOCK_SHOW_UNTIL'              => $_ARRAYLANG['TXT_BLOCK_SHOW_UNTIL'],
            'TXT_BLOCK_SHOW_TIMED'              => $_ARRAYLANG['TXT_BLOCK_SHOW_TIMED'],
            'TXT_BLOCK_SHOW_ALWAYS'             => $_ARRAYLANG['TXT_BLOCK_SHOW_ALWAYS'],
            'TXT_BLOCK_LANG_SHOW'               => $_ARRAYLANG['TXT_BLOCK_SHOW_BLOCK_IN_THIS_LANGUAGE'],
            'TXT_BLOCK_BASIC_DATA'              => $_ARRAYLANG['TXT_BLOCK_BASIC_DATA'],
            'TXT_BLOCK_ADDITIONAL_OPTIONS'      => $_ARRAYLANG['TXT_BLOCK_ADDITIONAL_OPTIONS'],
            'TXT_BLOCK_SELECTED_PAGES'          => $_ARRAYLANG['TXT_BLOCK_SELECTED_PAGES'],
            'TXT_BLOCK_AVAILABLE_PAGES'         => $_ARRAYLANG['TXT_BLOCK_AVAILABLE_PAGES'],
            'TXT_BLOCK_SELECT_ALL'              => $_ARRAYLANG['TXT_BLOCK_SELECT_ALL'],
            'TXT_BLOCK_UNSELECT_ALL'            => $_ARRAYLANG['TXT_BLOCK_UNSELECT_ALL'], 
            'TXT_BLOCK_GLOBAL_PLACEHOLDERS'     => $_ARRAYLANG['TXT_BLOCK_GLOBAL_PLACEHOLDERS'],
            'TXT_BLOCK_DISPLAY_TIME'            => $_ARRAYLANG['TXT_BLOCK_DISPLAY_TIME'],
            'TXT_BLOCK_FORM_DESC'               => $_ARRAYLANG['TXT_BLOCK_CONTENT'],
            'BLOCK_CONTENT_TEXT'                => get_wysiwyg_editor('blockTextEditor', '', 'shop'),
        ));

        if (isset($_POST['block_save_block'])) {
            $blockCat               = !empty($_POST['blockCat']) ? intval($_POST['blockCat']) : 0;
            $blockContent           = isset($_POST['blogFormText_']) ? array_map('contrexx_input2raw', $_POST['blogFormText_']) : array();
            $blockName              = !empty($_POST['blockName']) ? contrexx_input2raw($_POST['blockName']) : $_ARRAYLANG['TXT_BLOCK_NO_NAME'];
            $blockStart             = strtotime($_POST['inputStartDate']);
            $blockEnd               = strtotime($_POST['inputEndDate']);
            $blockRandom            = !empty($_POST['blockRandom']) ? intval($_POST['blockRandom']) : 0;
            $blockRandom2           = !empty($_POST['blockRandom2']) ? intval($_POST['blockRandom2']) : 0;
            $blockRandom3           = !empty($_POST['blockRandom3']) ? intval($_POST['blockRandom3']) : 0;
            $blockRandom4           = !empty($_POST['blockRandom4']) ? intval($_POST['blockRandom4']) : 0;
            $blockGlobal            = !empty($_POST['blockGlobal']) ? intval($_POST['blockGlobal']) : 0;
            $blockAssociatedPageIds = isset($_POST['selectedPages']) ? array_map('intval', $_POST['selectedPages']) : array();
            $blockLangActive        = isset($_POST['blockFormLanguages']) ? array_map('intval', $_POST['blockFormLanguages']) : array();

            if ($blockId) {
                if ($this->_updateBlock($blockId, $blockCat, $blockContent, $blockName, $blockStart, $blockEnd, $blockRandom, $blockRandom2, $blockRandom3, $blockRandom4, $blockGlobal, $blockAssociatedPageIds, $blockLangActive)) {
                    $this->_strOkMessage = $_ARRAYLANG['TXT_BLOCK_BLOCK_UPDATED_SUCCESSFULLY'];
                    return $this->_showOverview();
                } else {
                    $this->_strErrMessage = $_ARRAYLANG['TXT_BLOCK_BLOCK_COULD_NOT_BE_UPDATED'];
                }
            } else { 
                if ($this->_addBlock($blockCat, $blockContent, $blockName, $blockStart, $blockEnd, $blockRandom, $blockRandom2, $blockRandom3, $blockRandom4, $blockGlobal, $blockAssociatedPageIds, $blockLangActive)) {
                    $this->_strOkMessage = sprintf($_ARRAYLANG['TXT_BLOCK_BLOCK_ADDED_SUCCESSFULLY'], contrexx_raw2xhtml($blockName));
                    return $this->_showOverview();
                } else {
                    $this->_strErrMessage = $_ARRAYLANG['TXT_BLOCK_BLOCK_COULD_NOT_BE_ADDED'];
                }
            }
        } elseif (($arrBlock = $this->_getBlock($blockId)) !== false) {
            $blockStart         = $arrBlock['start'];
            $blockEnd           = $arrBlock['end'];
            $blockCat           = $arrBlock['cat'];
            $blockRandom        = $arrBlock['random'];
            $blockRandom2       = $arrBlock['random2'];
            $blockRandom3       = $arrBlock['random3'];
            $blockRandom4       = $arrBlock['random4'];
            $blockGlobal        = $arrBlock['global'];
            $blockActive        = $arrBlock['active'];
            $blockContent       = $arrBlock['content'];
            $blockLangActive    = $arrBlock['lang_active'];
            $blockName          = $arrBlock['name'];
            $blockAssociatedPageIds = $this->_getAssociatedPageIds($blockId);
        }

        $pageTitle = $blockId != 0 ? sprintf(($copy ? $_ARRAYLANG['TXT_BLOCK_COPY_BLOCK'] : $_ARRAYLANG['TXT_BLOCK_MODIFY_BLOCK']), contrexx_raw2xhtml($blockName)) : $_ARRAYLANG['TXT_BLOCK_ADD_BLOCK'];
        $this->_pageTitle = $pageTitle;

        if ($copy) {
            $blockId = 0;
        }
        
        $this->_objTpl->setVariable(array(
            'BLOCK_ID'                          => $blockId,
            'BLOCK_MODIFY_TITLE'                => $pageTitle,
            'BLOCK_NAME'                        => contrexx_raw2xhtml($blockName),
            'BLOCK_CATEGORIES_PARENT_DROPDOWN'  => $this->_getCategoriesDropdown($blockCat),
            'BLOCK_START'                       => strftime('%Y-%m-%d %H:%M', $blockStart),
            'BLOCK_END'                         => strftime('%Y-%m-%d %H:%M', $blockEnd),
            'BLOCK_RANDOM'                      => $blockRandom == '1' ? 'checked="checked"' : '',
            'BLOCK_RANDOM_2'                    => $blockRandom2 == '1' ? 'checked="checked"' : '',
            'BLOCK_RANDOM_3'                    => $blockRandom3 == '1' ? 'checked="checked"' : '',
            'BLOCK_RANDOM_4'                    => $blockRandom4 == '1' ? 'checked="checked"' : '',
            'BLOCK_GLOBAL_0'                    => $blockGlobal == '0' ? 'checked="checked"' : '',
            'BLOCK_GLOBAL_1'                    => $blockGlobal == '1' ? 'checked="checked"' : '',
            'BLOCK_GLOBAL_2'                    => $blockGlobal == '2' ? 'checked="checked"' : '',
            'BLOCK_SHOW_PAGE_SELECTOR'          => $blockGlobal == '2' ? 'block' : 'none',
        ));

        // create new ContentTree instance
        $objContentTree = new ContentTree($langId);
        $strSelectedPages   = '';
        $strUnselectedPages = '';

        foreach ($objContentTree->getTree() as $arrData) {
            $strSpacer  = '';
            $intLevel   = intval($arrData['level']);

            for ($i = 0; $i < $intLevel; $i++) {
                $strSpacer .= '&nbsp;&nbsp;';
            }

            if (in_array($arrData['node_id'],$blockAssociatedPageIds)) {
                $langStatus .= $arrData['catname'].", ";
                $strSelectedPages .= '<option value="'.$arrData['node_id'].'">'.$strSpacer.$arrData['catname'].' ('.$arrData['node_id'].') </option>'."\n";
            } else {
                $strUnselectedPages .= '<option value="'.$arrData['node_id'].'">'.$strSpacer.$arrData['catname'].' ('.$arrData['node_id'].') </option>'."\n";
            }
        }

        $this->_objTpl->setVariable(array(
            'BLOCK_RELATION_PAGES_UNSELECTED'   => $strUnselectedPages,
            'BLOCK_RELATION_PAGES_SELECTED'     => $strSelectedPages,
        ));

        $arrActiveSystemFrontendLanguages = FWLanguage::getActiveFrontendLanguages();
        if (count($arrActiveSystemFrontendLanguages) > 0) {
            $intLanguageCounter = 0;
            $boolFirstLanguage  = true;
            $arrLanguages       = array(0 => '', 1 => '', 2 => '');
            $strJsTabToDiv      = '';

            foreach($arrActiveSystemFrontendLanguages as $langId => $arrLanguage) {
                $boolLanguageIsActive = $blockId == 0 && $intLanguageCounter == 0 ? true : (($blockLangActive[$langId] == 1) ? true : false);
                
                $arrLanguages[$intLanguageCounter%3] .= '<input id="languagebar_'.$langId.'" '.(($boolLanguageIsActive) ? 'checked="checked"' : '').' type="checkbox" name="blockFormLanguages['.$langId.']" value="1" onclick="switchBoxAndTab(this, \'lang_blockContent_'.$langId.'\');" /><label for="languagebar_'.$langId.'">'.contrexx_raw2xhtml($arrLanguage['name']).' ['.$arrLanguage['lang'].']</label><br />';
                $strJsTabToDiv .= 'arrTabToDiv["lang_blockContent_'.$langId.'"] = "langTab_'.$langId.'";'."\n";
                ++$intLanguageCounter;
            }

            $this->_objTpl->setVariable(array(
                'TXT_BLOCK_LANGUAGE'      => $_ARRAYLANG['TXT_BLOCK_LANGUAGE'],
                'EDIT_LANGUAGES_1'        => $arrLanguages[0],
                'EDIT_LANGUAGES_2'        => $arrLanguages[1],
                'EDIT_LANGUAGES_3'        => $arrLanguages[2],
                'EDIT_JS_TAB_TO_DIV'      => $strJsTabToDiv
            ));
        }        
        
        $arrLanguages = FWLanguage::getLanguageArray();
        $i=0;    
        $activeFlag = 0;
        foreach ($arrLanguages as $langId => $arrLanguage) {            
            
            if($arrLanguage['frontend'] != 1) {
                continue;
            }

            $tmpBlockContent       = isset($blockContent[$langId]) ? $blockContent[$langId] : '';
            $tmpBlockLangActive    = isset($blockLangActive[$langId]) ? $blockLangActive[$langId] : 0;
            $tmpBlockContent       = preg_replace('/\{([A-Z0-9_-]+)\}/', '[[\\1]]' ,$tmpBlockContent);
            
            if ($blockId != 0 && $activeFlag == 0 && $activeClass == '') {
                $activeClass = $blockLangActive[$langId] == 1 ? 'active' : '';
                $activeFlag = 1;
            }            
            
            $this->_objTpl->setVariable(array(
                'BLOCK_LANG_TAB_LANG_ID'        => intval($langId),
                'BLOCK_LANG_TAB_CLASS'          => $blockId == 0 && $i == 0 ? 'active' : $activeClass,
                'TXT_BLOCK_LANG_TAB_LANG_NAME'  => contrexx_raw2xhtml($arrLanguage['name']),            
                'BLOCK_LANGTAB_DISPLAY'         => $tmpBlockLangActive == 1 ? 'display:inline;' : ($blockId == 0 && $i == 0 ? 'display:inline;' : 'display:none;')
            ));
            $this->_objTpl->parse('block_language_tabs');

            $this->_objTpl->setVariable(array(
                'BLOCK_LANG_ID'                 => intval($langId),                           
                'BLOCK_CONTENT_TEXT_HIDDEN'     => $tmpBlockContent,                
            ));
            $this->_objTpl->parse('block_language_content');
            $activeClass = '';
            $i++;
        }
    }

    /**
    * del block
    *
    * delete a block
    *
    * @access private
    * @global array
    * @global ADONewConnection
    */
    function _delBlock()
    {
        global $_ARRAYLANG, $objDatabase;

        $arrDelBlocks = array();
        $arrFailedBlock = array();
        $arrBlockNames = array();

        if (isset($_GET['blockId']) && ($blockId = intval($_GET['blockId'])) > 0) {
            $blockId = intval($_GET['blockId']);
            array_push($arrDelBlocks, $blockId);
            $arrBlock = &$this->_getBlock($blockId);
            $arrBlockNames[$blockId] = htmlentities($arrBlock['name'], ENT_QUOTES, CONTREXX_CHARSET);
        } elseif (isset($_POST['selectedBlockId']) && is_array($_POST['selectedBlockId'])) {
            foreach ($_POST['selectedBlockId'] as $blockId) {
                $id = intval($blockId);
                if ($id > 0) {
                    array_push($arrDelBlocks, $id);
                    $arrBlock = &$this->_getBlock($id);
                    $arrBlockNames[$id] = htmlentities($arrBlock['name'], ENT_QUOTES, CONTREXX_CHARSET);
                }
            }
        }

        if (count($arrDelBlocks) > 0) {
            foreach ($arrDelBlocks as $blockId) {
                foreach ($arrDelBlocks as $blockId) {
                    if ($objDatabase->Execute("DELETE FROM ".DBPREFIX."module_block_rel_lang_content WHERE block_id=".$blockId) === false || $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_block_blocks WHERE id=".$blockId) === false || $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_block_rel_pages WHERE block_id=".$blockId) === false) {
                        array_push($arrFailedBlock, $blockId);
                    }
                }
            }

            if (count($arrFailedBlock) == 1) {
                $this->_strErrMessage = sprintf($_ARRAYLANG['TXT_BLOCK_COULD_NOT_DELETE_BLOCK'], $arrBlockNames[$arrFailedBlock[0]]);
            } elseif (count($arrFailedBlock) > 1) {
                $this->_strErrMessage = sprintf($_ARRAYLANG['TXT_BLOCK_FAILED_TO_DELETE_BLOCKS'], implode(', ', $arrBlockNames));
            } elseif (count($arrDelBlocks) == 1) {
                $this->_strOkMessage = sprintf($_ARRAYLANG['TXT_BLOCK_SUCCESSFULLY_DELETED'], $arrBlockNames[$arrDelBlocks[0]]);
            } else {
                $this->_strOkMessage = $_ARRAYLANG['TXT_BLOCK_BLOCKS_SUCCESSFULLY_DELETED'];
            }
        }
    }

    /**
    * activate block
    *
    * change the status from a block
    *
    * @access private
    * @global array
    * @global ADONewConnection
    */
    function _activateBlock()
    {
        global $_ARRAYLANG, $objDatabase;

        $arrStatusBlocks = $_POST['selectedBlockId'];
        if($arrStatusBlocks != null){
            foreach ($arrStatusBlocks as $blockId){
                $query = "UPDATE ".DBPREFIX."module_block_blocks SET active='1' WHERE id=$blockId";
                $objDatabase->Execute($query);
            }
        }else{
            if(isset($_GET['blockId'])){
                $blockId = $_GET['blockId'];
                $query = "UPDATE ".DBPREFIX."module_block_blocks SET active='1' WHERE id=$blockId";
                $objDatabase->Execute($query);
            }
        }

        CSRF::header("Location: index.php?cmd=block");
    }

    /**
    * deactivate block
    *
    * change the status from a block
    *
    * @access private
    * @global array
    * @global ADONewConnection
    */
    function _deactivateBlock()
    {
        global $_ARRAYLANG, $objDatabase;

        $arrStatusBlocks = $_POST['selectedBlockId'];
        if($arrStatusBlocks != null){
            foreach ($arrStatusBlocks as $blockId){
                $query = "UPDATE ".DBPREFIX."module_block_blocks SET active='0' WHERE id=$blockId";
                $objDatabase->Execute($query);
            }
        }else{
            if(isset($_GET['blockId'])){
                $blockId = $_GET['blockId'];
                $query = "UPDATE ".DBPREFIX."module_block_blocks SET active='0' WHERE id=$blockId";
                $objDatabase->Execute($query);
            }
        }

        CSRF::header("Location: index.php?cmd=block");
    }

    /**
    * add to random
    *
    * change the status from a block
    *
    * @access private
    * @global array
    * @global ADONewConnection
    */
    function _randomizeBlock()
    {
        global $_ARRAYLANG, $objDatabase;

        $arrStatusBlocks = $_POST['selectedBlockId'];
        if($arrStatusBlocks != null){
            foreach ($arrStatusBlocks as $blockId){
                $query = "UPDATE ".DBPREFIX."module_block_blocks SET random='1' WHERE id=$blockId";
                $objDatabase->Execute($query);
            }
        }
    }

    /**
    * del the random
    *
    * change the status from a block
    *
    * @access private
    * @global array
    * @global ADONewConnection
    */
    function _randomizeBlockOff()
    {
        global $_ARRAYLANG, $objDatabase;

        $arrStatusBlocks = $_POST['selectedBlockId'];
        if($arrStatusBlocks != null){
            foreach ($arrStatusBlocks as $blockId){
                $query = "UPDATE ".DBPREFIX."module_block_blocks SET random='0' WHERE id=$blockId";
                $objDatabase->Execute($query);
            }
        }
    }

    /**
    * add to global
    *
    * change the status from a block
    *
    * @access private
    * @global array
    * @global ADONewConnection
    */
    function _globalBlock()
    {
        global $_ARRAYLANG, $objDatabase;

        $arrStatusBlocks = $_POST['selectedBlockId'];
        if($arrStatusBlocks != null){
            foreach ($arrStatusBlocks as $blockId){
                $query = "UPDATE ".DBPREFIX."module_block_blocks SET global='1' WHERE id=$blockId";
                $objDatabase->Execute($query);
            }
        }
    }

    /**
    * del the global
    *
    * change the status from a block
    *
    * @access private
    * @global ADONewConnection
    */
    function _globalBlockOff()
    {
        global $objDatabase;

        $arrStatusBlocks = $_POST['selectedBlockId'];
        if($arrStatusBlocks != null){
            foreach ($arrStatusBlocks as $blockId){
                $query = "UPDATE ".DBPREFIX."module_block_blocks SET global='0' WHERE id=".intval($blockId);
                $objDatabase->Execute($query);
            }
        }
    }

    /**
    * Show settings
    *
    * Show the settings page
    *
    * @access private
    * @global array
    * @global array
    * @global ADONewConnection
    */
    function _showSettings()
    {
        global $_ARRAYLANG, $_CONFIG, $objDatabase;

        $this->_pageTitle = $_ARRAYLANG['TXT_BLOCK_SETTINGS'];
        $this->_objTpl->loadTemplateFile('module_block_settings.html');

        $this->_objTpl->setVariable(array(
            'TXT_BLOCK_SETTINGS'                        => $_ARRAYLANG['TXT_BLOCK_SETTINGS'],
            'TXT_BLOCK_USE_BLOCK_SYSTEM'                => $_ARRAYLANG['TXT_BLOCK_USE_BLOCK_SYSTEM'],
            'TXT_BLOCK_USE_BLOCK_RANDOM'                => $_ARRAYLANG['TXT_BLOCK_USE_BLOCK_RANDOM'],
            'TXT_BLOCK_USE_BLOCK_RANDOM_PLACEHOLDER'    => $_ARRAYLANG['TXT_BLOCK_USE_BLOCK_RANDOM_PLACEHOLDER'],
            'TXT_PLACEHOLDERS'                          => $_ARRAYLANG['TXT_BLOCK_PLACEHOLDER'],
            'TXT_BLOCK_BLOCK_RANDOM'                    => $_ARRAYLANG['TXT_BLOCK_BLOCK_RANDOM'],
            'TXT_BLOCK_BLOCK_GLOBAL'                    => $_ARRAYLANG['TXT_BLOCK_BLOCK_GLOBAL'],
            'TXT_BLOCK_GLOBAL_SEPERATOR'                => $_ARRAYLANG['TXT_BLOCK_GLOBAL_SEPERATOR'],
            'TXT_BLOCK_GLOBAL_SEPERATOR_INFO'           => $_ARRAYLANG['TXT_BLOCK_GLOBAL_SEPERATOR_INFO'],
            'TXT_BLOCK_SAVE'                            => $_ARRAYLANG['TXT_SAVE'],
        ));

        $objResult = $objDatabase->Execute("SELECT  value
                                            FROM    ".DBPREFIX."module_block_settings
                                            WHERE   name='blockGlobalSeperator'
                                            ");
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $blockGlobalSeperator   = $objResult->fields['value'];
                $objResult->MoveNext();
            }
        }

        $this->_objTpl->setVariable(array(
            'BLOCK_GLOBAL_SEPERATOR'                        => addslashes($blockGlobalSeperator),
        ));

        $this->_objTpl->setVariable('BLOCK_USE_BLOCK_SYSTEM', $_CONFIG['blockStatus'] == '1' ? 'checked="checked"' : '');
        $this->_objTpl->setVariable('BLOCK_USE_BLOCK_RANDOM', $_CONFIG['blockRandom'] == '1' ? 'checked="checked"' : '');


        if (isset($_POST['saveSettings'])) {
            foreach ($_POST['blockSettings'] as $setName => $setValue){
                $query = "UPDATE ".DBPREFIX."module_block_settings SET value='".contrexx_addslashes($setValue)."' WHERE name='".$setName."'";
                $objDatabase->Execute($query);
            }

            CSRF::header('Location: index.php?cmd=block&act=settings');
        }
    }
}

?>
