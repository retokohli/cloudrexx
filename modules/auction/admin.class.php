<?php


/**
 * Auction
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @access        public
 * @version        1.0.0
 * @package     contrexx
 * @subpackage  module_auction
 * @todo        Edit PHP DocBlocks!
 */

error_reporting (E_ALL);

/**
 * Includes
 */
require_once ASCMS_LIBRARY_PATH . '/FRAMEWORK/File.class.php';
require_once ASCMS_MODULE_PATH . '/auction/lib/auctionLib.class.php';

/**
 * Auction
 *
 * Demo auction class
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @access        public
 * @version        1.0.0
 * @package     contrexx
 * @subpackage  module_auction
 */
class Auction extends auctionLibrary
{

    var $_objTpl;
    var $_pageTitle;
    var $strErrMessage = '';
    var $strOkMessage = '';
    var $categories = array();
    var $entries = array();
    var $mediaPath;
    var $mediaWebPath;
    var $settings;

    /**
    * Constructor
    */
    function Auction()
    {
        $this->__construct();
    }

    /**
    * PHP5 constructor
    *
    * @global object $objTemplate
    * @global array $_ARRAYLANG
    */
    function __construct() {

        global $_ARRAYLANG, $_CORELANG, $objTemplate;

        $this->_objTpl = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/auction/template');
        CSRF::add_placeholder($this->_objTpl);
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);
        $this->mediaPath = ASCMS_AUCTION_UPLOAD_PATH . '/';
        $this->mediaWebPath = ASCMS_AUCTION_UPLOAD_WEB_PATH . '/';
        $this->settings = $this->getSettings();

        $objTemplate->setVariable("CONTENT_NAVIGATION",
            "<a href='index.php?cmd=auction'>".$_CORELANG['TXT_OVERVIEW']."</a>
            <a href='index.php?cmd=auction&act=addCategorie'>".$_CORELANG['TXT_NEW_CATEGORY']."</a>
            <a href='index.php?cmd=auction&act=addEntry'>".$_ARRAYLANG['TXT_NEW_ENTRY']."</a>
            <a href='index.php?cmd=auction&act=entries'>".$_ARRAYLANG['TXT_ENTRIES']."</a>
            <a href='index.php?cmd=auction&act=settings'>".$_CORELANG['TXT_SETTINGS']."</a>");
    }

    /**
    * Set the backend page
    *
    * @access public
    * @global object $objTemplate
    * @global array $_ARRAYLANG
    */
    function getPage() {

        global $objTemplate;

        if (!isset($_GET['act'])) {
            $_GET['act']="";
        }

        switch ($_GET['act']) {
            case 'addCategorie':
                Permission::checkAccess(130, 'static');
                $this->addCategory();
            break;
            case 'editCategorie':
                Permission::checkAccess(130, 'static');
                $this->editCategorie();
            break;
            case 'statusCategorie':
                Permission::checkAccess(130, 'static');
                $this->statusCategorie();
                $this->overview();
            break;
            case 'deleteCategorie':
                Permission::checkAccess(130, 'static');
                $this->deleteCategorie();
                $this->overview();
            break;
            case 'sortCategorie':
                Permission::checkAccess(130, 'static');
                $this->sortCategorie();
                $this->overview();
            break;
            case 'addEntry':
                Permission::checkAccess(130, 'static');
                $this->addEntry();
            break;
            case 'statusEntry':
                Permission::checkAccess(130, 'static');
                $this->statusEntry();
                $this->entries();
            break;
            case 'deleteEntry':
                Permission::checkAccess(130, 'static');
                $this->deleteEntry();
                $this->entries();
            break;
            case 'editEntry':
                Permission::checkAccess(130, 'static');
                $this->editEntry();
            break;
            case 'entries':
                Permission::checkAccess(130, 'static');
                $this->entries();
            break;
            case 'settings':
                Permission::checkAccess(130, 'static');
                $this->sysSettings();
            break;
            default:
                Permission::checkAccess(130, 'static');
                $this->overview();
            break;
        }


        $objTemplate->setVariable(array(
            'CONTENT_OK_MESSAGE'        => $this->strOkMessage,
            'CONTENT_STATUS_MESSAGE'    => $this->strErrMessage,
            'ADMIN_CONTENT'                => $this->_objTpl->get(),
            'CONTENT_TITLE'                => $this->_pageTitle,
        ));
        return $this->_objTpl->get();
    }


    /**
    * create categorie overview
    *
    * @access public
    * @global object $objTemplate
    * @global object $objDatabase
    * @global array $_ARRAYLANG
    * @global array $_CORELANG
    */
    function overview()
    {
        global $objTemplate, $_ARRAYLANG, $_CORELANG;

        $this->_pageTitle = $_CORELANG['TXT_OVERVIEW'];
        $this->_objTpl->loadTemplateFile('module_auction_overview.html',true,true);

        $this->_objTpl->setVariable(array(
            'TXT_IMG_EDIT'                =>    $_CORELANG['TXT_EDIT'],
            'TXT_IMG_DEL'                =>    $_CORELANG['TXT_DELETE'],
            'TXT_STATUS'                =>    $_CORELANG['TXT_STATUS'],
            'TXT_NAME'                    =>    $_CORELANG['TXT_NAME'],
            'TXT_DESC'                    =>    $_CORELANG['TXT_DESCRIPTION'],
            'TXT_ENTRIES_COUNT'            =>    $_ARRAYLANG['TXT_AUCTION_AD_COUNT'],
            'TXT_ACTION'                =>    $_ARRAYLANG['TXT_AUCTION_ACTION'],
            'TXT_SELECT_ACTION'            =>    $_CORELANG['TXT_SUBMIT_SELECT'],
            'TXT_SAVE_ORDER'            =>    $_CORELANG['TXT_SAVE'],
            'TXT_SELECT_ALL'            =>    $_CORELANG['TXT_SELECT_ALL'],
            'TXT_DESELECT_ALL'            =>    $_CORELANG['TXT_DESELECT_ALL'],
            'TXT_DELETE'                =>    $_CORELANG['TXT_DELETE'],
            'TXT_ACTIVATE'                =>    $_ARRAYLANG['TXT_AUCTION_ACTIVATE'],
            'TXT_DEACTIVATE'            =>    $_ARRAYLANG['TXT_AUCTION_DEACTIVATE'],
            'TXT_DELETE_CATEGORY'        =>    $_ARRAYLANG['TXT_AUCTION_DELETE_ENTRIES']
        ));

        $this->getCategories();

        $i = 0;
        foreach (array_keys($this->categories) as $catId) {
            $this->categories[$catId]['status'] == 1 ? $led = 'led_green' : $led = 'led_red';
            $this->_objTpl->setVariable(array(
              'CAT_NAME'                =>    $this->categories[$catId]['name'],
              'CAT_ID'                =>    $catId,
              'CAT_DESCRIPTION'        =>    $this->categories[$catId]['description'],
              'CAT_COUNT_ENTRIES'        =>    $this->countEntries($catId),
              'CAT_SORTING'            =>    $this->categories[$catId]['order'],
              'CAT_ICON'                =>    $led,
              'CAT_ROWCLASS'            =>    (++$i % 2 ? 2 : 1),
              'CAT_STATUS'            =>    $this->categories[$catId]['status'],
            ));
            $this->_objTpl->parse('showCategories');
        }
    }



    /**
    * change status from categories
    *
    * @access public
    * @global object $objTemplate
    * @global object $objDatabase
    * @global array $_ARRAYLANG
    * @global array $_CORELANG
    */
    function statusCategorie() {

        global $objDatabase, $objTemplate, $_ARRAYLANG, $_CORELANG;

        $this->getCategories();

        if (isset($_POST['selectedCategoryId'])) {
            foreach ($_POST['selectedCategoryId'] as $catId) {
                $id = $catId;
                $_POST['frmShowCategories_MultiAction'] == 'activate' ? $newStatus = 1 : $newStatus = 0;
                $objResult = $objDatabase->Execute('UPDATE '.DBPREFIX.'module_auction_categories SET status = '.$newStatus.' WHERE id = '.$id.'');
                   if ($objResult !== false) {
                    $this->_statusMessage = $_ARRAYLANG['TXT_AUCTION_STATUS_CHANGED'];
                   }else{
                       $this->strErrMessage = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
                   }
            }
        }elseif ($_GET['id']) {
            $array = explode(',',$_GET['id']);
            $id     = $array[0];
            $status =  $array[1];

            $status == 1 ? $newStatus = 0 : $newStatus = 1;
            $objResult = $objDatabase->Execute('UPDATE '.DBPREFIX.'module_auction_categories SET status = '.$newStatus.' WHERE id = '.$id.'');
               if ($objResult !== false) {
                $this->strOkMessage = $_ARRAYLANG['TXT_AUCTION_STATUS_CHANGED'];
               }else{
                   $this->strErrMessage = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
               }
        }
    }


    /**
    * delete categories
    *
    * @access public
    * @global object $objTemplate
    * @global object $objDatabase
    * @global array $_ARRAYLANG
    * @global array $_CORELANG
    */
    function deleteCategorie() {
        global $objDatabase, $objTemplate, $_ARRAYLANG;

        $arrDelete = array();
        $i = 0;

        if (isset($_POST['selectedCategoryId'])) {
            foreach ($_POST['selectedCategoryId'] as $catId) {
                $arrDelete[$i] = $catId;
                $i++;
            }
        }elseif ($_GET['id']) {
            $arrDelete[$i] = $_GET['id'];
        }

           foreach ($arrDelete as $catId) {
            $objResult = $objDatabase->Execute('SELECT id FROM '.DBPREFIX.'module_auction WHERE catid = '.$catId.'');
            if ($objResult !== false) {
                if ($objResult->RecordCount() >= 1) {
                    $this->strErrMessage = $_ARRAYLANG['TXT_AUCTION_CATEGORY_DELETE_ERROR'];
                }else{
                    $objResultDel = $objDatabase->Execute('DELETE FROM '.DBPREFIX.'module_auction_categories WHERE id = '.$catId.'');
                    if ($objResultDel !== false) {
                        $this->strOkMessage = $_ARRAYLANG['TXT_AUCTION_CATEGORY_DELETE_SUCCESS'];
                    }else{
// TODO: $_CORELANG is not available here
//                        $this->strErrMessage = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
                    }
                }
            }
        }
    }


    /**
    * set sort order for categories
    *
    * @access public
    * @global object $objTemplate
    * @global object $objDatabase
    * @global array $_ARRAYLANG
    * @global array $_CORELANG
    */
    function sortCategorie() {
        global $objDatabase, $objTemplate, $_ARRAYLANG;

        foreach ($_POST['sortCategory'] as $catId => $catSort) {
            $objDatabase->Execute('UPDATE '.DBPREFIX.'module_auction_categories SET displayorder = '.$catSort.' WHERE id = '.$catId.'');
        }

        $this->strOkMessage = $_ARRAYLANG['TXT_AUCTION_CATEGORY_SORTING_UPDATED'];
    }


    /**
    * add a categorie
    *
    * @access public
    * @global object $objTemplate
    * @global object $objDatabase
    * @global array $_ARRAYLANG
    * @global array $_CORELANG
    */
    function addCategory() {

        global $objDatabase, $objTemplate, $_ARRAYLANG, $_CORELANG;

        $this->_pageTitle = $_CORELANG['TXT_NEW_CATEGORY'];
        $this->_objTpl->loadTemplateFile('module_auction_category.html',true,true);

        $this->_objTpl->setVariable(array(
            'TXT_TITLE'                =>    $_CORELANG['TXT_NEW_CATEGORY'],
            'TXT_NAME'                =>    $_CORELANG['TXT_NAME'],
            'TXT_DESCRIPTION'        =>    $_CORELANG['TXT_DESCRIPTION'],
            'TXT_SAVE'                =>    $_CORELANG['TXT_SAVE'],
            'TXT_STATUS'            =>    $_CORELANG['TXT_STATUS'],
            'TXT_STATUS_ON'            =>    $_CORELANG['TXT_ACTIVATED'],
            'TXT_STATUS_OFF'        =>    $_CORELANG['TXT_DEACTIVATED'],
            'TXT_FIELDS_REQUIRED'    =>    $_ARRAYLANG['TXT_AUCTION_CATEGORY_ADD_FILL_FIELDS']
        ));

        $this->_objTpl->setVariable(array(
            'FORM_ACTION'            =>    "addCategorie",
            'CAT_STATUS_ON'            =>    "checked"
        ));

        if (isset($_POST['submitCat'])) {
            $objResult = $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_auction_categories SET
                                  name='".$_POST['name']."',
                                  description='".$_POST['description']."',
                                displayorder='0',
                                  status='".$_POST['status']."'");

            if ($objResult !== false) {
                $this->strOkMessage = $_ARRAYLANG['TXT_AUCTION_CATEGORY_ADD_SUCCESS'];
            } else {
                $this->strErrMessage = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
            }
        }
    }


    /**
    * edit a categories
    *
    * @access public
    * @global object $objTemplate
    * @global object $objDatabase
    * @global array $_ARRAYLANG
    * @global array $_CORELANG
    */
    function editCategorie() {

        global $objDatabase, $objTemplate, $_ARRAYLANG, $_CORELANG;

        $this->_pageTitle = $_ARRAYLANG['TXT_AUCTION_CATEGORY_EDIT'];
        $this->_objTpl->loadTemplateFile('module_auction_category.html',true,true);

        $this->_objTpl->setVariable(array(
            'TXT_TITLE'                =>    $_ARRAYLANG['TXT_AUCTION_CATEGORY_EDIT'],
            'TXT_NAME'                =>    $_CORELANG['TXT_NAME'],
            'TXT_DESCRIPTION'        =>    $_CORELANG['TXT_DESCRIPTION'],
            'TXT_SAVE'                =>    $_CORELANG['TXT_SAVE'],
            'TXT_STATUS'            =>    $_CORELANG['TXT_STATUS'],
            'TXT_STATUS_ON'            =>    $_CORELANG['TXT_ACTIVATED'],
            'TXT_STATUS_OFF'        =>    $_CORELANG['TXT_DEACTIVATED'],
            'TXT_FIELDS_REQUIRED'    =>    $_ARRAYLANG['TXT_AUCTION_CATEGORY_ADD_FILL_FIELDS']
        ));

        $this->_objTpl->setVariable(array(
            'FORM_ACTION'            =>    "editCategorie",
        ));

        if (isset($_REQUEST['id'])) {
            $catId = $_REQUEST['id'];
            $objResult = $objDatabase->Execute('SELECT name, description, status FROM '.DBPREFIX.'module_auction_categories WHERE id = '.$catId.' LIMIT 1');
            if ($objResult !== false) {
                while (!$objResult->EOF) {
                    if ($objResult->fields['status'] == 1 ) {
                        $catStatusOn     = 'checked';
                        $catStatusOFF    = '';
                    }else{
                        $catStatusOn     = '';
                        $catStatusOFF    = 'checked';
                    }
                    $this->_objTpl->setVariable(array(
                        'CAT_ID'                =>    $catId,
                        'CAT_NAME'                =>    $objResult->fields['name'],
                        'CAT_DESCRIPTION'        =>    $objResult->fields['description'],
                        'CAT_STATUS_ON'            =>    $catStatusOn,
                        'CAT_STATUS_OFF'        =>    $catStatusOFF,
                    ));

                       $objResult->MoveNext();
                   }
            }
        }else{
            $this->overview();
        }

        if (isset($_POST['submitCat'])) {
            $objResult = $objDatabase->Execute("UPDATE ".DBPREFIX."module_auction_categories SET name = '".$_POST['name']."', status = '".$_POST['status']."', description = '".$_POST['description']."' WHERE id = '".$_POST['id']."'");
            if ($objResult !== false) {
                $this->strOkMessage = $_ARRAYLANG['TXT_AUCTION_CATEGORY_EDIT_SUCCESSFULL'];
                $this->overview();
            }else{
                $this->strErrMessage = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
            }
        }
    }



    /**
    * show auction entries
    *
    * @access public
    * @global object $objTemplate
    * @global object $objDatabase
    * @global array $_ARRAYLANG
    * @global array $_CORELANG
    */
    function entries() {

        global $objDatabase, $objTemplate, $_ARRAYLANG, $_CORELANG;

        $this->_pageTitle = $_ARRAYLANG['TXT_ENTRIES'];
        $this->_objTpl->loadTemplateFile('module_auction_entries.html',true,true);

        if (!isset($_GET['catid'])) {
            $where     = '';
            $like     = '';
            $sortId    = '';
        }else{
            $where     = 'catid';
            $like     = $_GET['catid'];
            $sortId    = '&catid='.$_GET['catid'];
        }

        if (isset($_POST['term'])) {
            $where     = 'title';
            $like     = "'%".$_POST['term']."%' OR description LIKE '%".$_POST['term']."%' OR id LIKE '%".$_POST['term']."%'";
        }

        // Sort
        if (isset($_GET['sort'])) {
            switch ($_GET['sort']) {
                case 'title':
                $_SESSION['auction']['sort']=($_SESSION['auction']['sort']=="title DESC")? "title ASC" : "title DESC";
                break;
                case 'type':
                $_SESSION['auction']['sort']=($_SESSION['auction']['sort']=="type DESC")? "type ASC" : "type DESC";
                break;
                case 'status':
                $_SESSION['auction']['sort']=($_SESSION['auction']['sort']=="status DESC")? "status ASC" : "status DESC";
                break;
                case 'addedby':
                $_SESSION['auction']['sort']=($_SESSION['auction']['sort']=="userid DESC")? "userid ASC" : "userid DESC";
                break;
                case 'regdate':
                $_SESSION['auction']['sort']=($_SESSION['auction']['sort']=="regdate DESC")? "regdate ASC" : "regdate DESC";
                break;
                case 'id':
                $_SESSION['auction']['sort']=($_SESSION['auction']['sort']=="id DESC")? "id ASC" : "id DESC";
                break;
            }
        }

        $this->_objTpl->setVariable(array(
            'TXT_IMG_EDIT'                =>    $_CORELANG['TXT_EDIT'],
            'TXT_IMG_DEL'                =>    $_CORELANG['TXT_DELETE'],
            'TXT_STATUS'                =>    $_CORELANG['TXT_STATUS'],
            'TXT_DATE'                    =>    $_CORELANG['TXT_DATE'],
            'TXT_TITLE'                    =>    $_ARRAYLANG['TXT_AUCTION_TITLE'],
            'TXT_DESC'                    =>    $_CORELANG['TXT_DESCRIPTION'],
            'TXT_ACTION'                =>    $_ARRAYLANG['TXT_AUCTION_ACTION'],
            'TXT_TYP'                    =>    $_CORELANG['TXT_TYPE'],
            'TXT_ADDEDBY'                =>    $_ARRAYLANG['TXT_AUCTION_ADDEDBY'],
            'TXT_SELECT_ACTION'            =>    $_CORELANG['TXT_SUBMIT_SELECT'],
            'TXT_SAVE_ORDER'            =>    $_CORELANG['TXT_SAVE'],
            'TXT_SELECT_ALL'            =>    $_CORELANG['TXT_SELECT_ALL'],
            'TXT_DESELECT_ALL'            =>    $_CORELANG['TXT_DESELECT_ALL'],
            'TXT_DELETE'                =>    $_CORELANG['TXT_DELETE'],
            'TXT_ACTIVATE'                =>    $_ARRAYLANG['TXT_AUCTION_ACTIVATE'],
            'TXT_DEACTIVATE'            =>    $_ARRAYLANG['TXT_AUCTION_DEACTIVATE'],
            'TXT_DELETE_CATEGORY'        =>    $_ARRAYLANG['TXT_AUCTION_DELETE_ENTRIES'],
            'ENTRY_SORT_ID'                =>  $sortId,
            'TXT_DELETE_ENTRY'            =>  $_ARRAYLANG['TXT_AUCTION_DELETE_ENTRIES'],
            'TXT_SEARCH'                =>    $_ARRAYLANG['TXT_SEARCH'],
            'TXT_OPTIONS'                =>    $_ARRAYLANG['TXT_AUCTION_OPTIONS'],
            'TXT_AUCTION_SHOWING_UP'                =>    $_ARRAYLANG['TXT_AUCTION_SHOWING_UP'],
        ));

        $this->getEntries($_SESSION['auction']['sort'], $where, $like);

        if (count($this->entries) != 0) {
               $i = 0;
               foreach (array_keys($this->entries) as $entryId) {
                   $this->entries[$entryId]['status'] == 1 ? $led = 'led_green' : $led = 'led_red';
                   $this->entries[$entryId]['type'] == 'offer' ? $type = $_ARRAYLANG['TXT_AUCTION_OFFER'] : $type = $_ARRAYLANG['TXT_AUCTION_SEARCH'];
                   $i%2 ? $row = 2 : $row = 1;
                   $objResult = $objDatabase->Execute('SELECT username FROM '.DBPREFIX.'access_users WHERE id = '.$this->entries[$entryId]['userid'].' LIMIT 1');
                if ($objResult !== false) {
                    $addedby = $objResult->fields['username'];
                }

                $objAuthorUser 	= FWUser::getFWUserObject();
                $objAuthor		= $objAuthorUser->objUser->getUsers($filter = array('id' => $this->entries[$entryId]['userid']));
		        if ($objAuthor) {
		            $addedby 	= $objAuthor->getUsername();
		        }

                $this->entries[$entryId]['regdate'] == '' ? $date = 'KEY: '.$this->entries[$entryId]['regkey'] : $date = date("d.m.Y", $this->entries[$entryId]['regdate']);

                $entry_end = (time() > $this->entries[$entryId]['enddate']) ? $_ARRAYLANG['TXT_AUCTION_AUCTION_IS_CLOSED'] : date("d.m.Y", $this->entries[$entryId]['enddate']);

                   $this->_objTpl->setVariable(array(
                    'ENTRY_TITLE'            =>    $this->entries[$entryId]['title'],
                    'ENTRY_DATE'            =>    $date,
                    'ENTRY_END'            =>    $entry_end,
                    'ENTRY_ID'                =>    $entryId,
                    'ENTRY_DESCRIPTION'        =>    $this->entries[$entryId]['description'],
                    'ENTRY_TYPE'            =>    $type,
                    'ENTRY_ADDEDBY'            =>    $addedby,
                    'ENTRY_ICON'            =>    $led,
                    'ENTRY_ROWCLASS'        =>    $row,
                    'ENTRY_SORT_ID_STATUS'    =>  $sortId,
                    'ENTRY_STATUS'            =>    $this->entries[$entryId]['status'],
                ));

                $i++;
                   $this->_objTpl->parse('showEntries');
               }
        }else{
            $this->_objTpl->hideBlock('showEntries');
            $this->strErrMessage = $_ARRAYLANG['TXT_AUCTION_NO_ENTRIES_FOUND'];
        }
    }


    /**
    * add a entree
    *
    * @access public
    * @global object $objTemplate
    * @global object $objDatabase
    * @global array $_ARRAYLANG
    * @global array $_CORELANG
    */
    function addEntry() {

        global $objDatabase, $objTemplate, $_ARRAYLANG, $_CORELANG;

        $this->_pageTitle = $_ARRAYLANG['TXT_NEW_ENTRY'];
        $this->_objTpl->loadTemplateFile('module_auction_entry.html',true,true);

        $objFWUser = FWUser::getFWUserObject();

        $this->getCategories();
        $categories = '';
        foreach (array_keys($this->categories) as $catId) {
            $categories .= '<option value="'.$catId.'">'.$this->categories[$catId]['name'].'</option>';
        }

        if ($this->settings['maxdayStatus'] == 1) {
            $daysOnline = '';
            for($x = $this->settings['maxday']; $x >= 1; $x--) {
                $daysOnline .= '<option value="'.$x.'">'.$x.'</option>';
            }

            $daysJS = 'if (days.value == "") {
                            errorMsg = errorMsg + "- '.$_ARRAYLANG['TXT_AUCTION_DURATION'].'\n";
                       }
                       ';
        }

        for($x=date("Y"); $x<=date("Y")+5; $x++){
        	$SelectedText = ($x==date("Y")) ? 'selected' : '';
        	$Options['auctionend']['year'] .= '<option value="'.$x.'" '.$SelectedText.'>'.$x.'</option>';
        }

        for($x=1; $x<=12; $x++){
        	$SelectedText = ($x==intval(date("m"))) ? 'selected' : '';
        	$Options['auctionend']['month'] .= '<option value="'.$x.'" '.$SelectedText.'>'.$x.'</option>';
        }

        for($x=1; $x<=31; $x++){
        	$SelectedText = ($x==intval(date("d"))) ? 'selected' : '';
        	$Options['auctionend']['day'] .= '<option value="'.$x.'" '.$SelectedText.'>'.$x.'</option>';
        }

        for($x=1; $x<=23; $x++){
        	$SelectedText = ($x==intval(date("H"))) ? 'selected' : '';
        	$Options['auctionend']['hour'] .= '<option value="'.$x.'" '.$SelectedText.'>'.$x.'</option>';
        }

        for($x=0; $x<=59; $x++){
        	$SelectedText = ($x==intval(date("i"))) ? 'selected' : '';
        	$Options['auctionend']['minutes'] .= '<option value="'.$x.'" '.$SelectedText.'>'.$x.'</option>';
        }

        $this->_objTpl->setVariable(array(
            'TXT_TITLE'                        =>    $_ARRAYLANG['TXT_NEW_ENTRY'],
            'TXT_NAME'                        =>    $_CORELANG['TXT_NAME'],
            'TXT_E-MAIL'                    =>    $_CORELANG['TXT_EMAIL'],
            'TXT_TITLE_ENTRY'                =>    $_ARRAYLANG['TXT_AUCTION_TITLE'],
            'TXT_DESCRIPTION'                =>    $_CORELANG['TXT_DESCRIPTION'],
            'TXT_SAVE'                        =>    $_CORELANG['TXT_SAVE'],
            'TXT_FIELDS_REQUIRED'            =>    $_ARRAYLANG['TXT_AUCTION_CATEGORY_ADD_FILL_FIELDS'],
            'TXT_THOSE_FIELDS_ARE_EMPTY'    =>    $_ARRAYLANG['TXT_AUCTION_FIELDS_NOT_CORRECT'],
            'TXT_PICTURE'                    =>    $_ARRAYLANG['TXT_AUCTION_IMAGE'],
            'TXT_CATEGORIE'                    =>    $_CORELANG['TXT_CATEGORY'],
            'TXT_PRICE'                        =>    $_ARRAYLANG['TXT_AUCTION_PRICE'].$this->settings['currency'],
            'TXT_TYPE'                        =>    $_CORELANG['TXT_TYPE'],
            'TXT_OFFER'                        =>    $_ARRAYLANG['TXT_AUCTION_OFFER'],
            'TXT_SEARCH'                    =>    $_ARRAYLANG['TXT_AUCTION_SEARCH'],
            'TXT_FOR_FREE'                    =>    $_ARRAYLANG['TXT_AUCTION_FREE'],
            'TXT_AGREEMENT'                    =>    $_ARRAYLANG['TXT_AUCTION_ARRANGEMENT'],
            'TXT_END_DATE'                    =>    $_ARRAYLANG['TXT_AUCTION_DURATION'],
            'END_DATE_JS'                    =>    $daysJS,
            'TXT_ADDED_BY'                    =>    $_ARRAYLANG['TXT_AUCTION_ADDEDBY'],
            'TXT_USER_DETAIL'                =>    $_ARRAYLANG['TXT_AUCTION_USERDETAILS'],
            'TXT_DETAIL_SHOW'                =>    $_ARRAYLANG['TXT_AUCTION_SHOW_IN_ADVERTISEMENT'],
            'TXT_DETAIL_HIDE'                =>    $_ARRAYLANG['TXT_AUCTION_NO_SHOW_IN_ADVERTISEMENT'],
            'TXT_PREMIUM'                    =>    $_ARRAYLANG['TXT_AUCTION_MARK_ADVERTISEMENT'],
            'TXT_AUCTION_STARTPRICE'         	=>    $_ARRAYLANG['TXT_AUCTION_STARTPRICE'],
            'TXT_AUCTION_INCR_STEPS'          	=>    $_ARRAYLANG['TXT_AUCTION_INCR_STEPS'],
            'TXT_AUCTION'          				=>    $_ARRAYLANG['TXT_AUCTION'],
            'TXT_SETTINGS'          			=>    $_ARRAYLANG['TXT_SETTINGS'],
            'TXT_IMAGES'          				=>    $_ARRAYLANG['TXT_IMAGES'],
            'TXT_AUCTION_TITLE'      			=>    $_ARRAYLANG['TXT_AUCTION_TITEL2'],
            'TXT_AUCTION_SHOWING_UP'      		=>    $_ARRAYLANG['TXT_AUCTION_SHOWING_UP'],
            'TXT_AUCTION_CONDITIONS'      		=>    $_ARRAYLANG['TXT_AUCTION_CONDITIONS'],
            'TXT_AUCTION_OTHER_SETTINGS'      	=>    $_ARRAYLANG['TXT_AUCTION_OTHER_SETTINGS'],
            'TXT_AUCTION_DELETE_IMAGE'      	=>    $_ARRAYLANG['TXT_AUCTION_DELETE_IMAGE'],
            'TXT_AUCTION_SHIPPING'      		=>    $_ARRAYLANG['TXT_AUCTION_SHIPPING'],
            'TXT_AUCTION_PAYMENT'      			=>    $_ARRAYLANG['TXT_AUCTION_PAYMENT'],
            'ENTRY_DESC'					=> get_wysiwyg_editor('description', ''),
            'ENTRY_END_YEAR'				=> $Options['auctionend']['year'],
            'ENTRY_END_MONTH'				=> $Options['auctionend']['month'],
            'ENTRY_END_DAY'					=> $Options['auctionend']['day'],
            'ENTRY_END_HOUR'				=> $Options['auctionend']['hour'],
            'ENTRY_END_MINUTES'				=> $Options['auctionend']['minutes'],
        ));



        if ($this->settings['maxdayStatus'] != 1) {
            $this->_objTpl->hideBlock('end_date_dropdown');
        }

        $objResult = $objDatabase->Execute("SELECT id, name, value FROM ".DBPREFIX."module_auction_spez_fields WHERE lang_id = '1' AND active='1' ORDER BY id DESC");
          if ($objResult !== false) {
            $i = 0;
            while (!$objResult->EOF) {
                $this->_objTpl->setCurrentBlock('spez_fields');

                ($i % 2)? $class = "row2" : $class = "row1";
                $input = '<input type="text" name="spez_'.$objResult->fields['id'].'" style="width: 300px;" maxlength="100">';

                // initialize variables
                $this->_objTpl->setVariable(array(
                    'SPEZ_FIELD_ROW_CLASS'        => $class,
                    'TXT_SPEZ_FIELD_NAME'        => $objResult->fields['value'],
                    'SPEZ_FIELD_INPUT'          => $input,
                ));

                $this->_objTpl->parse('spez_fields');
                $i++;
                $objResult->MoveNext();
            }
          }

        $this->_objTpl->setVariable(array(
            'FORM_ACTION'            => "addEntry",
            'CATEGORIES'            => $categories,
            'ENTRY_ADDEDBY'            => htmlentities($objFWUser->objUser->getUsername(), ENT_QUOTES, CONTREXX_CHARSET),
            'ENTRY_USERDETAILS_ON'    => "checked",
            'ENTRY_TYPE_OFFER'        => "checked",
            'DAYS_ONLINE'            => $daysOnline
        ));

        if (isset($_POST['submitEntry'])) {
            //$this->insertEntry('1');
            $this->insertAuction();
        }
    }


    /**
    * change status from entries
    *
    * @access public
    * @global object $objTemplate
    * @global object $objDatabase
    * @global array $_ARRAYLANG
    * @global array $_CORELANG
    */
    function statusEntry() {

        global $objDatabase, $objTemplate, $_ARRAYLANG, $_CORELANG;

        $arrStatus = array();
        $i=0;
        $today = mktime(0, 0, 0, date("m")  , date("d"), date("Y"));

        if (isset($_POST['selectedEntryId'])) {
            foreach ($_POST['selectedEntryId'] as $entryId) {
                $_POST['frmShowEntries_MultiAction'] == 'activate' ? $newStatus = 0 : $newStatus = 1;

                $arrStatus[$i] = $entryId.",".$newStatus;
                $i++;
            }
        }elseif ($_GET['id']) {
            $arrStatus[$i] = $_GET['id'];
        }

        foreach ($arrStatus as $entryId) {
            $array         = explode(',',$entryId);
            $id         = $array[0];
            $status     = $array[1];

            if ($status == 0) {
                $objResultDate = $objDatabase->Execute('SELECT regdate FROM '.DBPREFIX.'module_auction WHERE id = '.$id.' LIMIT 1');
                if ($objResultDate !== false) {
                    $regdate = $objResultDate->fields['regdate'];
                }

                if ($regdate == '') {
                    $objResult = $objDatabase->Execute("UPDATE ".DBPREFIX."module_auction SET status='1', regdate = '".$today."', regkey='' WHERE id = '".$id."'");
                }else{
                    $objResult = $objDatabase->Execute("UPDATE ".DBPREFIX."module_auction SET status='1' WHERE id = '".$id."'");
                }

                $this->sendMail($id);
            }else{
                $objResult = $objDatabase->Execute("UPDATE ".DBPREFIX."module_auction SET status='0' WHERE id = '".$id."'");
            }
        }

        if ($objResult !== false) {
            $this->strOkMessage = $_ARRAYLANG['TXT_AUCTION_STATUS_CHANGED'];
           }else{
           $this->strErrMessage = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
           }
    }




    /**
    * delete a entree
    *
    * @access public
    * @global object $objTemplate
    * @global object $objDatabase
    * @global array $_ARRAYLANG
    * @global array $_CORELANG
    */
    function deleteEntry() {

        global $objDatabase, $objTemplate, $_ARRAYLANG;

        $arrDelete = array();
        $i = 0;

        if (isset($_POST['selectedEntryId'])) {
            foreach ($_POST['selectedEntryId'] as $entryId) {
                $arrDelete[$i] = $entryId;
                $i++;
            }
        }elseif ($_GET['id']) {
            $arrDelete[$i] = $_GET['id'];
        }

           $this->removeEntry($arrDelete);
    }


    /**
    * edit a entree
    *
    * @access public
    * @global object $objTemplate
    * @global object $objDatabase
    * @global array $_ARRAYLANG
    * @global array $_CORELANG
    */
    function editEntry() {

        global $objDatabase, $objTemplate, $_ARRAYLANG, $_CORELANG;


        /* UPDATE ENTRY */

        if (isset($_POST['submitEntry']) || $_REQUEST['image_for_delete']!='') {

        	if(!file_exists(ASCMS_AUCTION_UPLOAD_PATH)){
				mkdir (ASCMS_AUCTION_UPLOAD_PATH);
			}

			$tmb_width = 80;
			if($this->settings['thumbnails_width']>0){
				$tmb_width = $this->settings['thumbnails_width'];
			}

        	// backup
//            if ($_FILES['pic']['name'] != "") {
//                $picture = $this->uploadPicture();
///* TODO: Never used
//                if ($picture != "error") {
//                    $objFile = new File();
//                    $status = $objFile->delFile($this->mediaPath, $this->mediaWebPath, "pictures/".$_POST['picOld']);
//                }
//*/
//                        }else{
//                $picture = $_POST['picOld'];
//            }

			// get actual data
			// -------------------------------------------
			$objResultTMP = $objDatabase->Execute('SELECT * FROM '.DBPREFIX.'module_auction WHERE id='.intval($_REQUEST['id']).' LIMIT 1');
            if ($objResultTMP !== false) {
                $tmpPictures[1] = $objResultTMP->fields['picture_1'];
                $tmpPictures[2] = $objResultTMP->fields['picture_2'];
                $tmpPictures[3] = $objResultTMP->fields['picture_3'];
                $tmpPictures[4] = $objResultTMP->fields['picture_4'];
                $tmpPictures[5] = $objResultTMP->fields['picture_5'];
            }

            // images
            // --------------------------------------------
            if($_REQUEST['image_for_delete']!=''){
            	@unlink (ASCMS_AUCTION_UPLOAD_PATH.'/'.$tmpPictures[intval($_REQUEST['image_for_delete'])]);
            	@unlink (ASCMS_AUCTION_UPLOAD_PATH.'/tmb_'.$tmpPictures[intval($_REQUEST['image_for_delete'])]);
            	$objDatabase->Execute('UPDATE '.DBPREFIX.'module_auction SET picture_'.intval($_REQUEST['image_for_delete']).'="" WHERE id='.intval($_REQUEST['id']).'');
            }

            for($x=1; $x<6; $x++){
            	if($_FILES['pic_'.$x]['name']!=''){
            		@unlink (ASCMS_AUCTION_UPLOAD_PATH.'/'.$tmpPictures[$x]);
            		@unlink (ASCMS_AUCTION_UPLOAD_PATH.'/tmb_'.$tmpPictures[$x]);

            		$HashCode = $this->GetHash();

            		$objDatabase->Execute('UPDATE '.DBPREFIX.'module_auction SET picture_'.$x.'="'.$HashCode.'_'.$_FILES['pic_'.$x]['name'].'" WHERE id='.intval($_REQUEST['id']).'');

            		if(@move_uploaded_file($_FILES['pic_'.$x]['tmp_name'], ASCMS_AUCTION_UPLOAD_PATH.'/'.$HashCode.'_'.$_FILES['pic_'.$x]['name'])) {

            			chmod(ASCMS_AUCTION_UPLOAD_PATH.'/'.$HashCode.'_'.$_FILES['pic_'.$x]['name'], 0777);

	                    // thumb
	            		// ------------------------------------------
	            		$LegalType = false;
	            		if($_FILES['pic_'.$x]['type']=='image/jpeg'){
	            			$image 		= @imagecreatefromjpeg (ASCMS_AUCTION_UPLOAD_PATH.'/'.$HashCode.'_'.$_FILES['pic_'.$x]['name'])
										or die ("GD-Image-Stream error");
							$LegalType 	= true;
	            		}
	            		if($_FILES['pic_'.$x]['type']=='image/gif'){
	            			$image 		= @imagecreatefromgif (ASCMS_AUCTION_UPLOAD_PATH.'/'.$HashCode.'_'.$_FILES['pic_'.$x]['name'])
										or die ("GD-Image-Stream error");
							$LegalType 	= true;
	            		}
	            		if($_FILES['pic_'.$x]['type']=='image/png'){
	            			$image 		= @imagecreatefrompng (ASCMS_AUCTION_UPLOAD_PATH.'/'.$HashCode.'_'.$_FILES['pic_'.$x]['name'])
										or die ("GD-Image-Stream error");
							$LegalType 	= true;
	            		}

	            		$sourceFile = ASCMS_AUCTION_UPLOAD_PATH.'/'.$HashCode.'_'.$_FILES['pic_'.$x]['name'];


	            		if($LegalType){
							$imageInfos 	= @getimagesize($sourceFile);
							$width 			= $imageInfos[0];
							$height 		= $imageInfos[1];
							$prozent		= ($tmb_width * 100) / $width;
							$new_width		= $tmb_width;
							$new_height		= ($height * $prozent) / 100;

							$thumb 			= imagecreatetruecolor($new_width, $new_height);

							@imagecopyresampled($thumb, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

							if($imageInfos['mime']=='image/jpeg'){
								imagejpeg($thumb, ASCMS_AUCTION_UPLOAD_PATH.'/tmb_'.$HashCode.'_'.$_FILES['pic_'.$x]['name']);
							}
							if($imageInfos['mime']=='image/gif'){
								imagegif($thumb, ASCMS_AUCTION_UPLOAD_PATH.'/tmb_'.$HashCode.'_'.$_FILES['pic_'.$x]['name']);
							}
							if($imageInfos['mime']=='image/png'){
								imagepng($thumb, ASCMS_AUCTION_UPLOAD_PATH.'/tmb_'.$HashCode.'_'.$_FILES['pic_'.$x]['name']);
							}

							chmod(ASCMS_AUCTION_UPLOAD_PATH.'/tmb_'.$HashCode.'_'.$_FILES['pic_'.$x]['name'], 0755);

//					        $objFile 	= new File();
//		                    $objFile->setChmod(ASCMS_AUCTION_PATH, ASCMS_AUCTION_PATH, '/tmb_'.$_FILES['pic_'.$x]['name']);
	            		}
            		}

            	}
            }


            if ($picture != "error") {
                if ($_POST['forfree'] == 1) {
                    $price = "forfree";
                }elseif ($_POST['agreement'] == 1) {
                    $price = "agreement";
                }else{
                    $price = $_POST['price'];
                }

//                $today = mktime(0, 0, 0, date("m")  , date("d"), date("Y"));
//                $tempDays     = date("d");
//                $tempMonth     = date("m");
//                $tempYear     = date("Y");
//                $enddate  = mktime(0, 0, 0, $tempMonth, $tempDays+$_POST['days'],  $tempYear);

                $auctionsEnd = mktime($_REQUEST["end_hour"], $_REQUEST["end_minutes"], 0, $_REQUEST["end_month"], $_REQUEST["end_day"], $_REQUEST["end_year"]);

                $objFWUser 			= FWUser::getFWUserObject();
				if ($objFWUser->objUser->login()) {
					$FromUser = $objFWUser->objUser->getId();
				}

                $objResult = $objDatabase->Execute("UPDATE ".DBPREFIX."module_auction SET
                                    type='".contrexx_addslashes($_POST['type'])."',
                                      title='".contrexx_addslashes($_POST['title'])."',
                                      description='".contrexx_addslashes($_POST['description'])."',
                                    premium='".contrexx_addslashes($_POST['premium'])."',
                                      catid='".contrexx_addslashes($_POST['cat'])."',
                                      price='".$price."',
                                      startprice='".$_REQUEST["startprice"]."',
                                      incr_step='".$_REQUEST["incr_steps"]."',
                                      enddate='".$auctionsEnd."',
                                      userid='".$FromUser."',
                                      name='".contrexx_addslashes($_POST['name'])."',
                                      email='".contrexx_addslashes($_POST['email'])."',
                                      shipping='".contrexx_addslashes($_POST['shipping'])."',
                                      payment='".contrexx_addslashes($_POST['payment'])."',
                                      spez_field_1='".contrexx_addslashes($_POST['spez_1'])."',
                                      spez_field_2='".contrexx_addslashes($_POST['spez_2'])."',
                                      spez_field_3='".contrexx_addslashes($_POST['spez_3'])."',
                                      spez_field_4='".contrexx_addslashes($_POST['spez_4'])."',
                                      spez_field_5='".contrexx_addslashes($_POST['spez_5'])."',
                                      userdetails='".contrexx_addslashes($_POST['userdetails'])."'
                                      WHERE id='".intval($_POST['id'])."'");

                if ($objResult !== false) {
                    $this->strOkMessage = $_ARRAYLANG['TXT_AUCTION_EDIT_SUCCESSFULL'];
                    //$this->entries();
                }else{
                    $this->strErrMessage = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
                }
            }else{
                $this->strErrMessage = $_ARRAYLANG['TXT_AUCTION_IMAGE_UPLOAD_ERROR'];
            }
        }

        /* *********************************************************** */


        $this->_pageTitle = $_ARRAYLANG['TXT_EDIT_ADVERTISEMENT'];
        $this->_objTpl->loadTemplateFile('module_auction_entry.html',true,true);

        $this->_objTpl->setVariable(array(
            'TXT_TITLE'                        =>    $_ARRAYLANG['TXT_EDIT_ADVERTISEMENT'],
            'TXT_TITLE_ENTRY'                =>    $_ARRAYLANG['TXT_AUCTION_TITLE'],
            'TXT_NAME'                        =>    $_CORELANG['TXT_NAME'],
            'TXT_E-MAIL'                    =>    $_CORELANG['TXT_EMAIL'],
            'TXT_DESCRIPTION'                =>    $_CORELANG['TXT_DESCRIPTION'],
            'TXT_SAVE'                        =>    $_CORELANG['TXT_SAVE'],
            'TXT_FIELDS_REQUIRED'            =>    $_ARRAYLANG['TXT_AUCTION_CATEGORY_ADD_FILL_FIELDS'],
            'TXT_THOSE_FIELDS_ARE_EMPTY'    =>    $_ARRAYLANG['TXT_AUCTION_FIELDS_NOT_CORRECT'],
            'TXT_PICTURE'                    =>    $_ARRAYLANG['TXT_AUCTION_IMAGE'],
            'TXT_CATEGORIE'                    =>    $_CORELANG['TXT_CATEGORY'],
            'TXT_PRICE'                        =>    $_ARRAYLANG['TXT_AUCTION_PRICE']." ".$this->settings['currency'],
            'TXT_TYPE'                        =>    $_CORELANG['TXT_TYPE'],
            'TXT_OFFER'                        =>    $_ARRAYLANG['TXT_AUCTION_OFFER'],
            'TXT_SEARCH'                    =>    $_ARRAYLANG['TXT_AUCTION_SEARCH'],
            'TXT_FOR_FREE'                    =>    $_ARRAYLANG['TXT_AUCTION_FREE'],
            'TXT_AGREEMENT'                    =>    $_ARRAYLANG['TXT_AUCTION_ARRANGEMENT'],
            'TXT_END_DATE'                    =>    $_ARRAYLANG['TXT_AUCTION_DURATION'],
            'TXT_ADDED_BY'                    =>    $_ARRAYLANG['TXT_AUCTION_ADDEDBY'],
            'TXT_ADDED_BY'                    =>    $_ARRAYLANG['TXT_AUCTION_ADDEDBY'],
            'TXT_USER_DETAIL'                =>    $_ARRAYLANG['TXT_AUCTION_USERDETAILS'],
            'TXT_DETAIL_SHOW'                =>    $_ARRAYLANG['TXT_AUCTION_SHOW_IN_ADVERTISEMENT'],
            'TXT_DETAIL_HIDE'                =>    $_ARRAYLANG['TXT_AUCTION_NO_SHOW_IN_ADVERTISEMENT'],
            'TXT_PREMIUM'                    =>    $_ARRAYLANG['TXT_AUCTION_MARK_ADVERTISEMENT'],
            'FORM_ACTION'                    =>    "editEntry",
            'TXT_DAYS'                        =>    $_ARRAYLANG['TXT_AUCTION_DAYS'],
            'TXT_AUCTION_STARTPRICE'         	=>    $_ARRAYLANG['TXT_AUCTION_STARTPRICE'],
            'TXT_AUCTION_INCR_STEPS'          	=>    $_ARRAYLANG['TXT_AUCTION_INCR_STEPS'],
            'TXT_AUCTION'          				=>    $_ARRAYLANG['TXT_AUCTION'],
            'TXT_SETTINGS'          			=>    $_ARRAYLANG['TXT_SETTINGS'],
            'TXT_IMAGES'          				=>    $_ARRAYLANG['TXT_IMAGES'],
            'TXT_AUCTION_TITLE'      			=>    $_ARRAYLANG['TXT_AUCTION_TITEL2'],
            'TXT_AUCTION_SHOWING_UP'      		=>    $_ARRAYLANG['TXT_AUCTION_SHOWING_UP'],
            'TXT_AUCTION_CONDITIONS'      		=>    $_ARRAYLANG['TXT_AUCTION_CONDITIONS'],
            'TXT_AUCTION_OTHER_SETTINGS'      	=>    $_ARRAYLANG['TXT_AUCTION_OTHER_SETTINGS'],
            'TXT_AUCTION_DELETE_IMAGE'      	=>    $_ARRAYLANG['TXT_AUCTION_DELETE_IMAGE'],
            'TXT_AUCTION_SHIPPING'      		=>    $_ARRAYLANG['TXT_AUCTION_SHIPPING'],
            'TXT_AUCTION_PAYMENT'      			=>    $_ARRAYLANG['TXT_AUCTION_PAYMENT'],
        ));



        if (isset($_REQUEST['id'])) {
            $entryId = $_REQUEST['id'];

            $objResult = $objDatabase->Execute('SELECT type, title, description, premium, picture_1, picture_2, picture_3, picture_4, picture_5, catid, startprice, incr_step, price, regdate, enddate, userid, name, email, userdetails, spez_field_1, spez_field_2, spez_field_3, spez_field_4, spez_field_5, shipping, payment  FROM '.DBPREFIX.'module_auction WHERE id = '.$entryId.' LIMIT 1');

            if ($objResult !== false) {
                while (!$objResult->EOF) {
                    //entry type
                    if ($objResult->fields['type'] == 'offer') {
                        $offer     = 'checked';
                        $search    = '';
                    }else{
                        $offer     = '';
                        $search    = 'checked';
                    }
                    //entry premium
                    if ($objResult->fields['premium'] == '1') {
                        $premium     = 'checked';
                    }else{
                        $premium    = '';
                    }
                    //entry price
                    if ($objResult->fields['price'] == 'forfree') {
                        $forfree     = 'checked';
                        $price         = '';
                        $agreement     = '';
                    }elseif ($objResult->fields['price'] == 'agreement') {
                        $agreement    = 'checked';
                        $price         = '';
                        $forfree     = '';
                    }else{
                        $price         = $objResult->fields['price'];
                        $forfree     = '';
                        $agreement     = '';
                    }
                    //entry user
                    $objResultUser = $objDatabase->Execute('SELECT username FROM '.DBPREFIX.'access_users WHERE id = '.$objResult->fields['userid'].' LIMIT 1');
                    if ($objResultUser !== false) {
                        $addedby = $objResultUser->fields['username'];
                    }
                    //entry userdetails
                    if ($objResult->fields['userdetails'] == '1') {
                        $userdetailsOn         = 'checked';
                        $userdetailsOff     = '';
                    }else{
                        $userdetailsOn         = '';
                        $userdetailsOff     = 'checked';
                    }
//                    entry picture
//                    if ($objResult->fields['picture'] != '') {
//                        $picture         = '<img src="'.$this->mediaWebPath.'pictures/'.$objResult->fields['picture'].'" border="0" alt="" /><br /><br />';
//                    }else{
//                        $picture         = '<img src="'.$this->mediaWebPath.'pictures/no_picture.gif" border="0" alt="" /><br /><br />';
//                    }

					// pictures
					$PicturesValue = array();
					for($x=1;$x<6;$x++){
						if($objResult->fields['picture_'.$x]!=''){
							$PicturesValue[$x] = '<a href="'.ASCMS_AUCTION_UPLOAD_WEB_PATH.'/'.$objResult->fields['picture_'.$x].'" target="_blank">'.$objResult->fields['picture_'.$x].'</a> &nbsp;&nbsp;&nbsp;<a href="javascript: DeleteImage('.$x.');">['.$_ARRAYLANG['TXT_DELETE'].']</a>';
						}else{
							$PicturesValue[$x] = '';
						}
					}

                    //entry category
                    $this->getCategories();
                    $categories     = '';
                    $checked         = '';
                    foreach (array_keys($this->categories) as $catId) {
                        $catId == $objResult->fields['catid'] ? $checked = 'selected' : $checked = '';
                        $categories .= '<option value="'.$catId.'" '.$checked.'>'.$this->categories[$catId]['name'].'</option>';
                    }

                    //rest days
                    $today = mktime(0, 0, 0, date("m")  , date("d"), date("Y"));
                    $tempDays     = date("d", $today);
                    $tempMonth     = date("m", $today);
                    $tempYear     = date("Y", $today);
                    $tempDate = '';

                    $x=0;
                    while ($objResult->fields['enddate'] >= $tempDate):
                        $x++;
                        $tempDate  = mktime(0, 0, 0, $tempMonth, $tempDays+$x,  $tempYear);
                    endwhile;

//                    $restDays = $x-1;
//                    $daysOnline = '';
//                    for($x = $this->settings['maxday']; $x >= 0; $x--) {
//                        $restDays == $x ? $selected = 'selected' : $selected = '';
//                        $daysOnline .= '<option value="'.$x.'" '.$selected.'>'.$x.'</option>';
//                    }

                    // Auktionsende
                    // --------------------------------------------
                    $Options 				= array();
                    $AuctionEnd				= array();
                    $AuctionEndTimestamp	= $objResult->fields['enddate'];
                    $AuctionEnd['year']		= date('Y', $AuctionEndTimestamp);
                    $AuctionEnd['month']	= date('m', $AuctionEndTimestamp);
                    $AuctionEnd['day']		= date('d', $AuctionEndTimestamp);
                    $AuctionEnd['hour']		= date('H', $AuctionEndTimestamp);
                    $AuctionEnd['minutes']	= date('i', $AuctionEndTimestamp);

                    for($x=date("Y"); $x<=date("Y")+5; $x++){
                    	$SelectedText = ($x==$AuctionEnd['year']) ? 'selected' : '';
                    	$Options['auctionend']['year'] .= '<option value="'.$x.'" '.$SelectedText.'>'.$x.'</option>';
                    }

                    for($x=1; $x<=12; $x++){
                    	$SelectedText = ($x==intval($AuctionEnd['month'])) ? 'selected' : '';
                    	$Options['auctionend']['month'] .= '<option value="'.$x.'" '.$SelectedText.'>'.$x.'</option>';
                    }

                    for($x=1; $x<=31; $x++){
                    	$SelectedText = ($x==intval($AuctionEnd['day'])) ? 'selected' : '';
                    	$Options['auctionend']['day'] .= '<option value="'.$x.'" '.$SelectedText.'>'.$x.'</option>';
                    }

                    for($x=1; $x<=23; $x++){
                    	$SelectedText = ($x==intval($AuctionEnd['hour'])) ? 'selected' : '';
                    	$Options['auctionend']['hour'] .= '<option value="'.$x.'" '.$SelectedText.'>'.$x.'</option>';
                    }

                    for($x=0; $x<=59; $x++){
                    	$SelectedText = ($x==intval($AuctionEnd['minutes'])) ? 'selected' : '';
                    	$Options['auctionend']['minutes'] .= '<option value="'.$x.'" '.$SelectedText.'>'.$x.'</option>';
                    }


                    //spez fields
                    $objSpezFields = $objDatabase->Execute("SELECT id, name, value FROM ".DBPREFIX."module_auction_spez_fields WHERE lang_id = '1' AND active='1' ORDER BY id DESC");
                    if ($objSpezFields !== false) {
                        $i = 0;
                        while (!$objSpezFields->EOF) {
                            ($i % 2)? $class = "row2" : $class = "row1";
                            $input = '<input type="text" name="spez_'.$objSpezFields->fields['id'].'" value="'.$objResult->fields[$objSpezFields->fields['name']].'" style="width: 300px;" maxlength="100">';
                            // initialize variables
                            $this->_objTpl->setVariable(array(
                                'SPEZ_FIELD_ROW_CLASS'        => $class,
                                'TXT_SPEZ_FIELD_NAME'        => $objSpezFields->fields['value'],
                                'SPEZ_FIELD_INPUT'          => $input,
                            ));
                            $this->_objTpl->parse('spez_fields');
                            $i++;
                            $objSpezFields->MoveNext();
                        }
                    }


                    $this->_objTpl->setVariable(array(
                        'ENTRY_ID'                    	=>    $entryId,
                        'ENTRY_TYPE_OFFER'            	=>    $offer,
                        'ENTRY_TYPE_SEARCH'            	=>    $search,
                        'ENTRY_TITLE'                	=>    $objResult->fields['title'],
                        'ENTRY_DESCRIPTION'            	=>    $objResult->fields['description'],
                        'ENTRY_PICTURE_OLD'            	=>    $objResult->fields['picture'],
                        'CATEGORIES'                	=>    $categories,
                        'ENTRY_PRICE'                	=>    $price,
                        'ENTRY_FOR_FREE'            	=>    $forfree,
                        'ENTRY_AGREEMENT'            	=>    $agreement,
                        'ENTRY_PREMIUM'                	=>    $premium,
                        'ENTRY_ADDEDBY'                	=>    $addedby,
                        'ENTRY_ADDEDBY_ID'            	=>    $objResult->fields['userid'],
                        'ENTRY_USERDETAILS_ON'        	=>    $userdetailsOn,
                        'ENTRY_USERDETAILS_OFF'        	=>    $userdetailsOff,
                        'DAYS_ONLINE'                	=>    $daysOnline,
                        'ENTRY_NAME'                	=>    $objResult->fields['name'],
                        'ENTRY_E-MAIL'                	=>    $objResult->fields['email'],
                        'ENTRY_STARTPRICE'				=> $objResult->fields['startprice'],
                        'ENTRY_INCR_STEPS'				=> $objResult->fields['incr_step'],
                        'ENTRY_DESC'					=> get_wysiwyg_editor('description', $objResult->fields['description']),
                        'ENTRY_END_YEAR'				=> $Options['auctionend']['year'],
                        'ENTRY_END_MONTH'				=> $Options['auctionend']['month'],
                        'ENTRY_END_DAY'					=> $Options['auctionend']['day'],
                        'ENTRY_END_HOUR'				=> $Options['auctionend']['hour'],
                        'ENTRY_END_MINUTES'				=> $Options['auctionend']['minutes'],
                        'ENTRY_PICTURE_1'				=> $PicturesValue[1],
                        'ENTRY_PICTURE_2'				=> $PicturesValue[2],
                        'ENTRY_PICTURE_3'				=> $PicturesValue[3],
                        'ENTRY_PICTURE_4'				=> $PicturesValue[4],
                        'ENTRY_PICTURE_5'				=> $PicturesValue[5],
                        'ENTRY_SHIPPING'				=> $objResult->fields['shipping'],
                        'ENTRY_PAYMENT'					=> $objResult->fields['payment'],
                    ));
                       $objResult->MoveNext();
                   }


            }
        }else{
            $this->entries();
        }


    }

    /**
    * show system settings
    *
    * @access public
    * @global object $objTemplate
    * @global object $objDatabase
    * @global array $_ARRAYLANG
    * @global array $_CORELANG
    */
    function sysSettings()
    {
        global $_ARRAYLANG, $_CORELANG;
        $this->_objTpl->loadTemplateFile('module_auction_settings.html',true,true);
        $this->_pageTitle = $_CORELANG['TXT_SETTINGS'];

        $this->_objTpl->setGlobalVariable(array(
            'TXT_SYSTEM'      => $_CORELANG['TXT_SETTINGS_MENU_SYSTEM'],
            'TXT_EMAIL'       => $_ARRAYLANG['TXT_AUCTION_VALIDATION_EMAIL'],
            'TXT_EMAIL_CODE'  => $_ARRAYLANG['TXT_AUCTION_CLEARING_EMAIL'],
            'TXT_SPEZ_FIELDS' => $_ARRAYLANG['TXT_AUCTION_SPEZ_FIELDS'],
            'TXT_AUCTION_PLACEHOLDER' => $_ARRAYLANG['TXT_AUCTION_PLACEHOLDER'],
        ));

        if (!isset($_GET['tpl'])) {
            $_GET['tpl'] = "";
        }

        switch ($_GET['tpl']) {
            case 'system':
                $this->systemSettings();
                break;
            case 'email':
                $this->mailSettings();
                break;
            case 'email_code':
                $this->mail_codeSettings();
                break;
            case 'spez_fields':
                $this->spezfieldsSettings();
                break;
            case 'placeholder':
                $this->placeholderSettings();
                break;
            default:
                $this->systemSettings();
                break;
        }

        $this->_objTpl->parse('requests_block');
    }

    function placeholderSettings(){
    	global $objDatabase, $objTemplate, $_ARRAYLANG, $_CORELANG;

        // initialize variables
        $this->_objTpl->addBlockfile('SYSTEM_REQUESTS_CONTENT', 'requests_block', 'module_auction_settings_placeholders.html');

        $this->_objTpl->setVariable(array(
            'TXT_AUCTION_PLACEHOLDER'       	=> $_ARRAYLANG['TXT_AUCTION_PLACEHOLDER'],
            'TXT_AUCTION_SEARCH_TITLE'       	=> $_ARRAYLANG['TXT_AUCTION_SEARCH_TITLE'],
            'TXT_AUCTION_SEARCH_TITLE_DESC'   	=> $_ARRAYLANG['TXT_AUCTION_SEARCH_TITLE_DESC'],
            'TXT_AUCTION_SEARCH_DESC'   		=> $_ARRAYLANG['TXT_AUCTION_SEARCH_DESC'],
            'TXT_AUCTION_CATEGORIES_DESC'   	=> $_ARRAYLANG['TXT_AUCTION_CATEGORIES_DESC'],
            'AUCTION_CATEGORY_DESCRIPTION_DESC' => $_ARRAYLANG['AUCTION_CATEGORY_DESCRIPTION_DESC'],
            'TXT_AUCTION_TITLE_DESC' 			=> $_ARRAYLANG['TXT_AUCTION_TITLE_DESC'],
            'AUCTION_ENDDATE_DESC' 				=> $_ARRAYLANG['AUCTION_ENDDATE_DESC'],
            'AUCTION_BIDS_DESC' 				=> $_ARRAYLANG['AUCTION_BIDS_DESC'],
            'AUCTION_PRICE_DESC' 				=> $_ARRAYLANG['AUCTION_PRICE_DESC'],
            'AUCTION_STARTPRICE_DESC' 			=> $_ARRAYLANG['AUCTION_STARTPRICE_DESC'],
            'AUCTION_INCREASE_STEP_DESC' 		=> $_ARRAYLANG['AUCTION_INCREASE_STEP_DESC'],
            'AUCTION_NEXT_BID_DESC' 			=> $_ARRAYLANG['AUCTION_NEXT_BID_DESC'],
            'AUCTION_DESCRIPTION_DESC' 			=> $_ARRAYLANG['AUCTION_DESCRIPTION_DESC'],
            'AUCTION_SHIPPING_DESC' 			=> $_ARRAYLANG['AUCTION_SHIPPING_DESC'],
            'AUCTION_PAYMENT_DESC' 				=> $_ARRAYLANG['AUCTION_PAYMENT_DESC'],
        ));
    }

    /**
    * show settings for spezial fields
    *
    * @access public
    * @global object $objTemplate
    * @global object $objDatabase
    * @global array $_ARRAYLANG
    * @global array $_CORELANG
    */
    function spezfieldsSettings() {

        global $objDatabase, $objTemplate, $_ARRAYLANG, $_CORELANG;

        // initialize variables
        $this->_objTpl->addBlockfile('SYSTEM_REQUESTS_CONTENT', 'requests_block', 'module_auction_settings_spez_fields.html');

        $this->_objTpl->setVariable(array(
            'TXT_TITLE'               => $_ARRAYLANG['TXT_AUCTION_SPEZ_FIELDS'],
            'TXT_STATUS'              => $_CORELANG['TXT_STATUS'],
            'TXT_NAME'                => $_CORELANG['TXT_NAME'],
            'TXT_SAVE'                => $_ARRAYLANG['TXT_SAVE'],
            'TXT_TYPE'                => $_CORELANG['TXT_TYPE'],
            'TXT_TYPE'                => $_ARRAYLANG['TXT_TYPE'],
            'TXT_TYPE'                => $_ARRAYLANG['TXT_TYPE'],
            'TXT_PLACEHOLDER_TITLE'   => $_ARRAYLANG['TXT_AUCTION_PLACEHOLDER_TITLE'],
            'TXT_PLACEHOLDER_CONTENT' => $_ARRAYLANG['TXT_AUCTION_PLACEHOLDER_CONTENT'],
        ));

        $i=0;

          $objResult = $objDatabase->Execute("SELECT id, name, value, type, active FROM ".DBPREFIX."module_auction_spez_fields WHERE lang_id = '1' ORDER BY active DESC");
          if ($objResult !== false) {
            while (!$objResult->EOF) {
                $this->_objTpl->setCurrentBlock('spez_fields');

                ($i % 2)? $class = "row2" : $class = "row1";
                ($objResult->fields['active'] == 1)? $status = 'checked="checked"' : $status = "";
                ($objResult->fields['type'] == 1)? $type = "Textfeld" : $type = "Mehrzeiliges Textfeld";

                // initialize variables
                $this->_objTpl->setVariable(array(
                    'SPEZ_FIELD_ROWCLASS' => $class,
                    'SPEZ_FIELD_ID'       => $objResult->fields['id'],
                    'SPEZ_FIELD_TITLE'    => $objResult->fields['value'],
                    'SPEZ_FIELD_NAME'     => $objResult->fields['name'],
                    'SPEZ_FIELD_STATUS'   => $status,
                    'SPEZ_FIELD_TYPE'     => $type,
                    'PLACEHOLDER_TITLE'   => "[[TXT_AUCTION_".strtoupper($objResult->fields['name'])."]]",
                    'PLACEHOLDER_CONTENT' => "[[AUCTION_".strtoupper($objResult->fields['name'])."]]",
                ));
                $this->_objTpl->parseCurrentBlock('spez_fields');
                $i++;
                $objResult->MoveNext();
            }
          }

          if (isset($_POST['submitSettings'])) {
            foreach ($_POST['setname'] as $id => $name) {
                $name        = contrexx_addslashes($name);
                $value        = contrexx_addslashes($_POST['settitle'][$id]);
                $status        = contrexx_addslashes($_POST['setstatus'][$id]);

                $objResult = $objDatabase->Execute("UPDATE ".DBPREFIX."module_auction_spez_fields SET value='".$value."', active='".$status."' WHERE name='".$name."'");
            }

            if ($objResult !== false) {
                CSRF::header('Location: ?cmd=auction&act=settings&tpl=spez_fields');
                $this->strOkMessage = $_ARRAYLANG['TXT_AUCTION_SETTINGS_UPDATED'];
            }else{
                $this->strErrMessage = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
            }
        }
    }


    /**
    * show system settings
    *
    * @access public
    * @global object $objTemplate
    * @global object $objDatabase
    * @global array $_ARRAYLANG
    * @global array $_CORELANG
    */
    function systemSettings() {

        global $objDatabase, $objTemplate, $_ARRAYLANG, $_CORELANG;

        // initialize variables
        $this->_objTpl->addBlockfile('SYSTEM_REQUESTS_CONTENT', 'requests_block', 'module_auction_settings_system.html');

        $this->_objTpl->setVariable(array(
            'TXT_TITLE'       => $_CORELANG['TXT_SETTINGS'],
            'TXT_DESCRIPTION' => $_CORELANG['TXT_DESCRIPTION'],
            'TXT_VALUE'       => $_CORELANG['TXT_VALUE'],
            'TXT_SAVE'        => $_CORELANG['TXT_SAVE'],
        ));

        //get settings
        $i=0;
          $objResult = $objDatabase->Execute("SELECT * FROM ".DBPREFIX."module_auction_settings ORDER BY type ASC");
          if ($objResult !== false) {
            while (!$objResult->EOF) {
                $this->_objTpl->setCurrentBlock('settings');
                if ($objResult->fields['type']== 1) {
                    $setValueField = "<input type=\"text\" name=\"setvalue[".$objResult->fields['id']."]\" value=\"".$objResult->fields['value']."\" style='width: 300px;' maxlength='250'>";
                }elseif ($objResult->fields['type']== 2) {
                    if ($objResult->fields['value'] == 1) {
                        $true = "checked";
                        $false = "";
                    }else{
                        $false = "checked";
                        $true = "";
                    }
                    $setValueField = "<input type=\"radio\" name=\"setvalue[".$objResult->fields['id']."]\" value=\"1\" ".$true.">&nbsp;Aktiviert&nbsp;<input type=\"radio\" name=\"setvalue[".$objResult->fields['id']."]\" value=\"0\"".$false.">&nbsp;Deaktiviert&nbsp;";
                } else {
                     $setValueField = "<textarea name=\"setvalue[".$objResult->fields['id']."]\" rows='5' style='width: 300px;'>".$objResult->fields['value']."</textarea>";
                }

                ($i % 2)? $class = "row2" : $class = "row1";

                // initialize variables
                $this->_objTpl->setVariable(array(
                    'SETTINGS_ROWCLASS'        => $class,
                    'SETTINGS_SETVALUE'        => $setValueField,
                    'SETTINGS_DESCRIPTION'  => $_ARRAYLANG[$objResult->fields['description']],
                ));
                $this->_objTpl->parseCurrentBlock('settings');
                $i++;
                $objResult->MoveNext();
            }
          }

          if (isset($_POST['submitSettings'])) {

            foreach ($_POST['setvalue'] as $id => $value) {
                $objResult = $objDatabase->Execute("UPDATE ".DBPREFIX."module_auction_settings SET value='".contrexx_addslashes($value)."' WHERE id=".intval($id));

                if ($id == 11) {
                    if ($value == '0') {
                        $objResult = $objDatabase->Execute("UPDATE ".DBPREFIX."module_auction_mail SET mailto='admin' WHERE id='2'");
                    } else {
                        $objResult = $objDatabase->Execute("UPDATE ".DBPREFIX."module_auction_mail SET mailto='advertiser' WHERE id='2'");
                    }
                }
            }

            if ($objResult !== false) {
                CSRF::header('Location: ?cmd=auction&act=settings');
                $this->strOkMessage = $_ARRAYLANG['TXT_AUCTION_SETTINGS_UPDATED'];
            }else{
                $this->strErrMessage = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
            }
        }
    }




    /**
    * show settings for mail
    *
    * @access public
    * @global object $objTemplate
    * @global object $objDatabase
    * @global array $_ARRAYLANG
    * @global array $_CORELANG
    */
    function mailSettings() {

        global $objDatabase, $objTemplate, $_ARRAYLANG, $_CORELANG, $_CONFIG;

        // initialize variables
        $this->_objTpl->addBlockfile('SYSTEM_REQUESTS_CONTENT', 'requests_block', 'module_auction_settings_mail.html');

        //get content
        $objResult = $objDatabase->Execute("SELECT title, content, active, mailcc FROM ".DBPREFIX."module_auction_mail WHERE id = '1'");
          if ($objResult !== false) {
            while (!$objResult->EOF) {
                $mailContent     = $objResult->fields['content'];
                $mailTitle         = $objResult->fields['title'];
                $mailCC         = $objResult->fields['mailcc'];
                $mailActive         = $objResult->fields['active'];
                $objResult->MoveNext();
            }
          }

          $mailActive == 1 ? $checked = 'checked' : $checked = '';

        $this->_objTpl->setVariable(array(
            'TXT_SAVE'        => $_CORELANG['TXT_SAVE'],
            'TXT_EMAIL_TITLE' => $_CORELANG['TXT_EMAIL'],
            'TXT_PLACEHOLDER' => $_ARRAYLANG['TXT_AUCTION_PLACEHOLDER'],
            'TXT_SETTINGS'    => $_CORELANG['TXT_SETTINGS'],
            'TXT_MAIL_ON'     => $_ARRAYLANG['TXT_AUCTION_ACTIVATE_VALIDATION_EMAIL'],
            'TXT_MAIL_CC'     => $_ARRAYLANG['TXT_AUCTION_ADDITIONAL_RECIPENT'],
            'TXT_CONTENT'     => $_CORELANG['TXT_CONTENT'],
            'TXT_SUBJECT'     => $_ARRAYLANG['TXT_AUCTION_SUBJECT'],
            'TXT_TEXT'        => $_ARRAYLANG['TXT_AUCTION_TEXT'],
            'TXT_URL'         => $_ARRAYLANG['TXT_AUCTION_URL'],
            'TXT_LINK'        => $_ARRAYLANG['TXT_AUCTION_LINK'],
            'TXT_NAME'        => $_CORELANG['TXT_NAME'],
            'TXT_USERNAME'    => $_CORELANG['TXT_USERNAME'],
            'TXT_ID'          => $_ARRAYLANG['TXT_AUCTION_ADVERTISEMENT_ID'],
            'TXT_TITLE'       => $_ARRAYLANG['TXT_AUCTION_ADVERTISEMENT_TITLE'],
            'TXT_DATE'        => $_CORELANG['TXT_DATE'],
            'MAIL_CONTENT'    => $mailContent,
            'MAIL_TITLE'      => $mailTitle,
            'MAIL_CC'         => $mailCC,
            'MAIL_ON'         => $checked,
        ));

        $this->_objTpl->setVariable(array(
            'MAIL_CONTENT' => $mailContent,
            'MAIL_TITLE'   => $mailTitle,
            'MAIL_CC'      => $mailCC,
            'MAIL_ON'      => $checked,
        ));

        if (isset($_POST['submitSettings'])) {
            $objResult = $objDatabase->Execute("UPDATE ".DBPREFIX."module_auction_mail SET title='".$_POST['mailTitle']."', content='".$_POST['mailContent']."', mailcc='".$_POST['mailCC']."', active='".$_POST['mailOn']."' WHERE id='1'");
            if ($objResult !== false) {
                CSRF::header('Location: ?cmd=auction&act=settings&tpl=email');
                $this->strOkMessage = $_ARRAYLANG['TXT_AUCTION_SETTINGS_UPDATED'];
            }else{
                $this->strErrMessage = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
            }
        }
    }

    /**
    * show settings for mail code
    *
    * @access public
    * @global object $objTemplate
    * @global object $objDatabase
    * @global array $_ARRAYLANG
    * @global array $_CORELANG
    */
    function mail_codeSettings() {

        global $objDatabase, $objTemplate, $_ARRAYLANG, $_CORELANG;

        // initialize variables
        $this->_objTpl->addBlockfile('SYSTEM_REQUESTS_CONTENT', 'requests_block', 'module_auction_settings_mail_code.html');

        //get content
        $objResult = $objDatabase->Execute("SELECT title, content, mailto, mailcc FROM ".DBPREFIX."module_auction_mail WHERE id = '2'");
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $mailContent         = $objResult->fields['content'];
                $mailTitle             = $objResult->fields['title'];
                $mailTo             = $objResult->fields['mailto'];
                $mailCC                = $objResult->fields['mailcc'];
                $objResult->MoveNext();
            }
        }

        $mailTo == 'admin' ? $admin = 'checked' : $admin = '';
        $mailTo == 'advertiser' || $mailTo == '' ? $advertiser = 'checked' : $advertiser = '';

        $this->_objTpl->setVariable(array(
            'TXT_SAVE'        => $_CORELANG['TXT_SAVE'],
            'TXT_EMAIL_TITLE' => $_CORELANG['TXT_EMAIL'],
            'TXT_PLACEHOLDER' => $_ARRAYLANG['TXT_AUCTION_PLACEHOLDER'],
            'TXT_SETTINGS'    => $_CORELANG['TXT_SETTINGS'],
            'TXT_MAIL_CC'     => $_ARRAYLANG['TXT_AUCTION_ADDITIONAL_RECIPENT'],
            'TXT_MAIL_TO'     => $_ARRAYLANG['TXT_AUCTION_CODE_CLEARING_CODE'],
            'TXT_CONTENT'     => $_CORELANG['TXT_CONTENT'],
            'TXT_SUBJECT'     => $_ARRAYLANG['TXT_AUCTION_SUBJECT'],
            'TXT_TEXT'        => $_ARRAYLANG['TXT_AUCTION_TEXT'],
            'TXT_URL'         => $_ARRAYLANG['TXT_AUCTION_URL'],
            'TXT_CODE'        => $_ARRAYLANG['TXT_AUCTION_CODE_CLEARINGCODE'],
            'TXT_NAME'        => $_CORELANG['TXT_NAME'],
            'TXT_USERNAME'    => $_CORELANG['TXT_USERNAME'],
            'TXT_ID'          => $_ARRAYLANG['TXT_AUCTION_ADVERTISEMENT_ID'],
            'TXT_TITLE'       => $_ARRAYLANG['TXT_AUCTION_ADVERTISEMENT_TITLE'],
            'TXT_DATE'        => $_CORELANG['TXT_DATE'],
            'TXT_ADMIN'       => $_CORELANG['TXT_ADMIN_STATUS'],
            'TXT_ADVERTISER'  => $_ARRAYLANG['TXT_AUCTION_ADVERTISER']
        ));

        $this->_objTpl->setVariable(array(
            'MAIL_CONTENT'                 => $mailContent,
            'MAIL_TITLE'                   => $mailTitle,
            'MAIL_TO_ADVERTISER'          => $advertiser,
            'MAIL_TO_ADMIN'              => $admin,
            'MAIL_CC'                      => $mailCC
        ));

        if (isset($_POST['submitSettings'])) {
            $objResult = $objDatabase->Execute("UPDATE ".DBPREFIX."module_auction_mail SET title='".$_POST['mailTitle']."', content='".$_POST['mailContent']."', mailcc='".$_POST['mailCC']."', active='1', mailto='".$_POST['mailTo']."' WHERE id='2'");

            if ($_POST['mailTo'] == 'admin') {
                $objResult = $objDatabase->Execute("UPDATE ".DBPREFIX."module_auction_settings SET value='0' WHERE id='11'");
            } else {
                $objResult = $objDatabase->Execute("UPDATE ".DBPREFIX."module_auction_settings SET value='1' WHERE id='11'");
            }

            if ($objResult !== false) {
                CSRF::header('Location: ?cmd=auction&act=settings&tpl=email_code');
                $this->strOkMessage = $_ARRAYLANG['TXT_AUCTION_SETTINGS_UPDATED'];
            }else{
                $this->strErrMessage = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
            }
        }
    }


}

?>
