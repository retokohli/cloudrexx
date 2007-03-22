<?php
/**
 * Market
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author		Astalavista Development Team <thun@astalvista.ch>
 * @access		public
 * @version		1.0.0
 * @package     contrexx
 * @subpackage  module_market
 * @todo        Edit PHP DocBlocks!
 */

//error_reporting (E_ALL);

/**
 * Includes
 */
require_once ASCMS_LIBRARY_PATH . '/FRAMEWORK/File.class.php';
require_once ASCMS_MODULE_PATH . '/market/lib/marketLib.class.php';

/**
 * Market
 *
 * Demo market class
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author		Astalavista Development Team <thun@astalvista.ch>
 * @access		public
 * @version		1.0.0
 * @package     contrexx
 * @subpackage  module_market
 */
class Market extends marketLibrary
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
	function Market()
	{
		$this->__construct();
	}

	/**
	* PHP5 constructor
	*
	* @global object $objTemplate
	* @global array $_ARRAYLANG
	*/
	function __construct(){

		global $_ARRAYLANG, $_CORELANG, $objTemplate, $objInit;

		$this->_objTpl = &new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/market/template');
		$this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);
		$this->mediaPath = ASCMS_MARKET_MEDIA_PATH . '/';
	    $this->mediaWebPath = ASCMS_MARKET_MEDIA_WEB_PATH . '/';
		$this->settings = $this->getSettings();

    	$objTemplate->setVariable("CONTENT_NAVIGATION", "<a href='index.php?cmd=market'>".$_CORELANG['TXT_OVERVIEW']."</a>
    													<a href='index.php?cmd=market&act=addCategorie'>".$_CORELANG['TXT_NEW_CATEGORY']."</a>
    													<a href='index.php?cmd=market&act=addEntry '>".$_ARRAYLANG['TXT_NEW_ENTRY']."</a>
    													<a href='index.php?cmd=market&act=entries'>".$_ARRAYLANG['TXT_ENTRIES']."</a>
    													<a href='index.php?cmd=market&act=settings'>".$_CORELANG['TXT_SETTINGS']."</a>");
	}

	/**
	* Set the backend page
	*
	* @access public
	* @global object $objTemplate
	* @global array $_ARRAYLANG
	*/
	function getPage() {

		global $objDatabase, $objTemplate, $objPerm;

    	if(!isset($_GET['act'])){
    	    $_GET['act']="";
    	}

		switch ($_GET['act']){
			case 'addCategorie':
				$objPerm->checkAccess(98, 'static');
				$this->addCategory();
			break;
			case 'editCategorie':
				$objPerm->checkAccess(98, 'static');
				$this->editCategorie();
			break;
			case 'statusCategorie':
				$objPerm->checkAccess(98, 'static');
				$this->statusCategorie();
				$this->overview();
			break;
			case 'deleteCategorie':
				$objPerm->checkAccess(98, 'static');
				$this->deleteCategorie();
				$this->overview();
			break;
			case 'sortCategorie':
				$objPerm->checkAccess(98, 'static');
				$this->sortCategorie();
				$this->overview();
			break;
			case 'addEntry':
				$objPerm->checkAccess(98, 'static');
				$this->addEntry();
			break;
			case 'statusEntry':
				$objPerm->checkAccess(98, 'static');
				$this->statusEntry();
				$this->entries();
			break;
			case 'statusEntry':
				$objPerm->checkAccess(98, 'static');
				$this->deleteEntry();
				$this->entries();
			break;
			case 'deleteEntry':
				$objPerm->checkAccess(98, 'static');
				$this->deleteEntry();
				$this->entries();
			break;
			case 'editEntry':
				$objPerm->checkAccess(98, 'static');
				$this->editEntry();
			break;
			case 'entries':
				$objPerm->checkAccess(98, 'static');
				$this->entries();
			break;
			case 'settings':
				$objPerm->checkAccess(98, 'static');
				$this->sysSettings();
			break;
			default:
				$objPerm->checkAccess(98, 'static');
				$this->overview();
			break;
		}


		$objTemplate->setVariable(array(
			'CONTENT_OK_MESSAGE'		=> $this->strOkMessage,
			'CONTENT_STATUS_MESSAGE'	=> $this->strErrMessage,
			'ADMIN_CONTENT'				=> $this->_objTpl->get(),
			'CONTENT_TITLE'				=> $this->_pageTitle,
		));
		return $this->_objTpl->get();
	}



	function overview() {
		global $objDatabase, $objTemplate, $_ARRAYLANG, $_CORELANG;

		$this->_pageTitle = $_CORELANG['TXT_OVERVIEW'];
		$this->_objTpl->loadTemplateFile('module_market_overview.html',true,true);

		$this->_objTpl->setVariable(array(
		    'TXT_IMG_EDIT'				=>	$_CORELANG['TXT_EDIT'],
	    	'TXT_IMG_DEL'				=>	$_CORELANG['TXT_DELETE'],
	    	'TXT_STATUS'				=>	$_CORELANG['TXT_STATUS'],
	    	'TXT_NAME'					=>	$_CORELANG['TXT_NAME'],
	    	'TXT_DESC'					=>	$_CORELANG['TXT_DESCRIPTION'],
	    	'TXT_ENTRIES_COUNT'			=>	$_ARRAYLANG['TXT_MARKET_AD_COUNT'],
	    	'TXT_ACTION'				=>	$_ARRAYLANG['TXT_MARKET_ACTION'],
	    	'TXT_SELECT_ACTION'			=>	$_CORELANG['TXT_SUBMIT_SELECT'],
	    	'TXT_SAVE_ORDER'			=>	$_CORELANG['TXT_SAVE'],
	    	'TXT_SELECT_ALL'			=>	$_CORELANG['TXT_SELECT_ALL'],
	    	'TXT_DESELECT_ALL'			=>	$_CORELANG['TXT_DESELECT_ALL'],
	    	'TXT_DELETE'				=>	$_CORELANG['TXT_DELETE'],
	    	'TXT_ACTIVATE'				=>	$_ARRAYLANG['TXT_MARKET_ACTIVATE'],
	    	'TXT_DEACTIVATE'			=>	$_ARRAYLANG['TXT_MARKET_DEACTIVATE'],
	    	'TXT_DELETE_CATEGORY'		=>	$_ARRAYLANG['TXT_MARKET_DELETE_ENTRIES']
		));

		$this->getCategories();

	   	$i = 0;
	   	foreach($this->categories as $catId => $catValue) {
	   		$this->categories[$catId]['status'] == 1 ? $led = 'led_green' : $led = 'led_red';
	   		$i%2 ? $row = 2 : $row = 1;

	   		$this->_objTpl->setVariable(array(
			    'CAT_NAME'				=>	$this->categories[$catId]['name'],
			    'CAT_ID'				=>	$catId,
			    'CAT_DESCRIPTION'		=>	$this->categories[$catId]['description'],
			    'CAT_COUNT_ENTRIES'		=>	$this->countEntries($catId),
			    'CAT_SORTING'			=>	$this->categories[$catId]['order'],
			    'CAT_ICON'				=>	$led,
			    'CAT_ROWCLASS'			=>	$row,
			    'CAT_STATUS'			=>	$this->categories[$catId]['status'],
			));

			$i++;
	   		$this->_objTpl->parse('showCategories');
	   	}
	}



	function statusCategorie() {

		global $objDatabase, $objTemplate, $_ARRAYLANG, $_CORELANG;

		$this->getCategories();

		if(isset($_POST['selectedCategoryId'])){
			foreach($_POST['selectedCategoryId'] as $arrKey => $catId) {
				$id = $catId;
				$_POST['frmShowCategories_MultiAction'] == 'activate' ? $newStatus = 1 : $newStatus = 0;
				$objResult = $objDatabase->Execute('UPDATE '.DBPREFIX.'module_market_categories SET status = '.$newStatus.' WHERE id = '.$id.'');
			   	if ($objResult !== false){
					$this->_statusMessage = $_ARRAYLANG['TXT_MARKET_STATUS_CHANGED'];
			   	}else{
			   		$this->strErrMessage = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
			   	}
			}
		}elseif($_GET['id']){
			$array = explode(',',$_GET['id']);
			$id 	= $array[0];
			$status =  $array[1];

			$status == 1 ? $newStatus = 0 : $newStatus = 1;
			$objResult = $objDatabase->Execute('UPDATE '.DBPREFIX.'module_market_categories SET status = '.$newStatus.' WHERE id = '.$id.'');
		   	if ($objResult !== false){
				$this->strOkMessage = $_ARRAYLANG['TXT_MARKET_STATUS_CHANGED'];
		   	}else{
		   		$this->strErrMessage = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
		   	}
		}
	}



	function deleteCategorie() {
		global $objDatabase, $objTemplate, $_ARRAYLANG;

		$arrDelete = array();
		$i = 0;

		if(isset($_POST['selectedCategoryId'])){
			foreach($_POST['selectedCategoryId'] as $arrKey => $catId) {
				$arrDelete[$i] = $catId;
				$i++;
			}
		}elseif($_GET['id']){
			$arrDelete[$i] = $_GET['id'];
		}

	   	foreach($arrDelete as $arrKey => $catId) {
			$objResult = $objDatabase->Execute('SELECT id FROM '.DBPREFIX.'module_market WHERE catid = '.$catId.'');
			if($objResult !== false){
				if($objResult->RecordCount() >= 1){
					$this->strErrMessage = $_ARRAYLANG['TXT_MARKET_CATEGORY_DELETE_ERROR'];
				}else{
					$objResultDel = $objDatabase->Execute('DELETE FROM '.DBPREFIX.'module_market_categories WHERE id = '.$catId.'');
					if($objResultDel !== false){
						$this->strOkMessage = $_ARRAYLANG['TXT_MARKET_CATEGORY_DELETE_SUCCESS'];
					}else{
						$this->strErrMessage = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
					}
				}
			}
		}
	}



	function sortCategorie() {
		global $objDatabase, $objTemplate, $_ARRAYLANG;

		foreach($_POST['sortCategory'] as $catId => $catSort) {
			$objResult = $objDatabase->Execute('UPDATE '.DBPREFIX.'module_market_categories SET displayorder = '.$catSort.' WHERE id = '.$catId.'');
		}

		$this->strOkMessage = $_ARRAYLANG['TXT_MARKET_CATEGORY_SORTING_UPDATED'];
	}



	function addCategory(){

		global $objDatabase, $objTemplate, $_ARRAYLANG, $_CORELANG;

		$this->_pageTitle = $_CORELANG['TXT_NEW_CATEGORY'];
		$this->_objTpl->loadTemplateFile('module_market_category.html',true,true);

		$this->_objTpl->setVariable(array(
		    'TXT_TITLE'				=>	$_CORELANG['TXT_NEW_CATEGORY'],
	    	'TXT_NAME'				=>	$_CORELANG['TXT_NAME'],
	    	'TXT_DESCRIPTION'		=>	$_CORELANG['TXT_DESCRIPTION'],
	    	'TXT_SAVE'				=>	$_CORELANG['TXT_SAVE'],
	    	'TXT_STATUS'			=>	$_CORELANG['TXT_STATUS'],
	    	'TXT_STATUS_ON'			=>	$_CORELANG['TXT_ACTIVATED'],
	    	'TXT_STATUS_OFF'		=>	$_CORELANG['TXT_DEACTIVATED'],
	    	'TXT_FIELDS_REQUIRED'	=>	$_ARRAYLANG['TXT_MARKET_CATEGORY_ADD_FILL_FIELDS']
		));

		$this->_objTpl->setVariable(array(
	    	'FORM_ACTION'			=>	"addCategorie",
	    	'CAT_STATUS_ON'			=>	"checked"
		));

		if(isset($_POST['submitCat'])){
			$objResult = $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_market_categories SET
		                      	name='".$_POST['name']."',
			                  	description='".$_POST['description']."',
								displayorder='0',
			                  	status='".$_POST['status']."'");

			if($objResult !== false){
				$this->strOkMessage = $_ARRAYLANG['TXT_MARKET_CATEGORY_ADD_SUCCESS'];
			} else {
				$this->strErrMessage = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
			}
		}
	}



	function editCategorie(){

		global $objDatabase, $objTemplate, $_ARRAYLANG, $_CORELANG;

		$this->_pageTitle = $_ARRAYLANG['TXT_MARKET_CATEGORY_EDIT'];
		$this->_objTpl->loadTemplateFile('module_market_category.html',true,true);

		$this->_objTpl->setVariable(array(
		    'TXT_TITLE'				=>	$_ARRAYLANG['TXT_MARKET_CATEGORY_EDIT'],
	    	'TXT_NAME'				=>	$_CORELANG['TXT_NAME'],
	    	'TXT_DESCRIPTION'		=>	$_CORELANG['TXT_DESCRIPTION'],
	    	'TXT_SAVE'				=>	$_CORELANG['TXT_SAVE'],
	    	'TXT_STATUS'			=>	$_CORELANG['TXT_STATUS'],
	    	'TXT_STATUS_ON'			=>	$_CORELANG['TXT_ACTIVATED'],
	    	'TXT_STATUS_OFF'		=>	$_CORELANG['TXT_DEACTIVATED'],
	    	'TXT_FIELDS_REQUIRED'	=>	$_ARRAYLANG['TXT_MARKET_CATEGORY_ADD_FILL_FIELDS']
		));

		$this->_objTpl->setVariable(array(
	    	'FORM_ACTION'			=>	"editCategorie",
		));

		if(isset($_REQUEST['id'])){
			$catId = $_REQUEST['id'];
			$objResult = $objDatabase->Execute('SELECT name, description, status FROM '.DBPREFIX.'module_market_categories WHERE id = '.$catId.' LIMIT 1');
			if($objResult !== false){
				while (!$objResult->EOF) {
					if($objResult->fields['status'] == 1 ){
						$catStatusOn 	= 'checked';
					    $catStatusOFF	= '';
					}else{
						$catStatusOn 	= '';
					    $catStatusOFF	= 'checked';
					}
					$this->_objTpl->setVariable(array(
					    'CAT_ID'				=>	$catId,
					    'CAT_NAME'				=>	$objResult->fields['name'],
					    'CAT_DESCRIPTION'		=>	$objResult->fields['description'],
					    'CAT_STATUS_ON'			=>	$catStatusOn,
					    'CAT_STATUS_OFF'		=>	$catStatusOFF,
					));

			   		$objResult->MoveNext();
			   	}
			}
		}else{
			$this->overview();
		}

		if(isset($_POST['submitCat'])){
			$objResult = $objDatabase->Execute("UPDATE ".DBPREFIX."module_market_categories SET name = '".$_POST['name']."', status = '".$_POST['status']."', description = '".$_POST['description']."' WHERE id = '".$_POST['id']."'");
			if($objResult !== false){
				$this->strOkMessage = $_ARRAYLANG['TXT_MARKET_CATEGORY_EDIT_SUCCESSFULL'];
				$this->overview();
			}else{
				$this->strErrMessage = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
			}
		}
	}



	function entries(){

		global $objDatabase, $objTemplate, $_ARRAYLANG, $_CORELANG;

		$this->_pageTitle = $_ARRAYLANG['TXT_ENTRIES'];
		$this->_objTpl->loadTemplateFile('module_market_entries.html',true,true);

		if(!isset($_GET['catid'])){
			$where 	= '';
			$like 	= '';
			$sortId	= '';
		}else{
			$where 	= 'catid';
			$like 	= $_GET['catid'];
			$sortId	= '&catid='.$_GET['catid'];
		}

		if(isset($_POST['term'])){
			$where 	= 'title';
			$like 	= "'%".$_POST['term']."%' OR description LIKE '%".$_POST['term']."%' OR id LIKE '%".$_POST['term']."%'";
		}

		// Sort
		if (isset($_GET['sort'])){
			switch ($_GET['sort']){
				case 'title':
				$_SESSION['market']['sort']=($_SESSION['market']['sort']=="title DESC")? "title ASC" : "title DESC";
				break;
				case 'type':
				$_SESSION['market']['sort']=($_SESSION['market']['sort']=="type DESC")? "type ASC" : "type DESC";
				break;
				case 'status':
				$_SESSION['market']['sort']=($_SESSION['market']['sort']=="status DESC")? "status ASC" : "status DESC";
				break;
				case 'addedby':
				$_SESSION['market']['sort']=($_SESSION['market']['sort']=="userid DESC")? "userid ASC" : "userid DESC";
				break;
				case 'regdate':
				$_SESSION['market']['sort']=($_SESSION['market']['sort']=="regdate DESC")? "regdate ASC" : "regdate DESC";
				break;
				case 'id':
				$_SESSION['market']['sort']=($_SESSION['market']['sort']=="id DESC")? "id ASC" : "id DESC";
				break;
			}
		}

		$this->_objTpl->setVariable(array(
		    'TXT_IMG_EDIT'				=>	$_CORELANG['TXT_EDIT'],
	    	'TXT_IMG_DEL'				=>	$_CORELANG['TXT_DELETE'],
	    	'TXT_STATUS'				=>	$_CORELANG['TXT_STATUS'],
	    	'TXT_DATE'					=>	$_CORELANG['TXT_DATE'],
	    	'TXT_TITLE'					=>	$_ARRAYLANG['TXT_MARKET_TITLE'],
	    	'TXT_DESC'					=>	$_CORELANG['TXT_DESCRIPTION'],
	    	'TXT_ACTION'				=>	$_ARRAYLANG['TXT_MARKET_ACTION'],
	    	'TXT_TYP'					=>	$_CORELANG['TXT_TYPE'],
	    	'TXT_ADDEDBY'				=>	$_ARRAYLANG['TXT_MARKET_ADDEDBY'],
	    	'TXT_SELECT_ACTION'			=>	$_CORELANG['TXT_SUBMIT_SELECT'],
	    	'TXT_SAVE_ORDER'			=>	$_CORELANG['TXT_SAVE'],
	    	'TXT_SELECT_ALL'			=>	$_CORELANG['TXT_SELECT_ALL'],
	    	'TXT_DESELECT_ALL'			=>	$_CORELANG['TXT_DESELECT_ALL'],
	    	'TXT_DELETE'				=>	$_CORELANG['TXT_DELETE'],
	    	'TXT_ACTIVATE'				=>	$_ARRAYLANG['TXT_MARKET_ACTIVATE'],
	    	'TXT_DEACTIVATE'			=>	$_ARRAYLANG['TXT_MARKET_DEACTIVATE'],
	    	'TXT_DELETE_CATEGORY'		=>	$_ARRAYLANG['TXT_MARKET_DELETE_ENTRIES'],
	    	'ENTRY_SORT_ID'				=>  $sortId,
	    	'TXT_DELETE_ENTRY'			=>  $_ARRAYLANG['TXT_MARKET_DELETE_ENTRIES'],
	    	'TXT_SEARCH'				=>	$_ARRAYLANG['TXT_SEARCH'],
	    	'TXT_OPTIONS'				=>	$_ARRAYLANG['TXT_MARKET_OPTIONS'],
		));

		$this->getEntries($_SESSION['market']['sort'], $where, $like);

		if(count($this->entries) != 0){
		   	$i = 0;
		   	foreach($this->entries as $entryId => $entryValue) {
		   		$this->entries[$entryId]['status'] == 1 ? $led = 'led_green' : $led = 'led_red';
		   		$this->entries[$entryId]['type'] == 'offer' ? $type = $_ARRAYLANG['TXT_MARKET_OFFER'] : $type = $_ARRAYLANG['TXT_MARKET_SEARCH'];
		   		$i%2 ? $row = 2 : $row = 1;
		   		$objResult = $objDatabase->Execute('SELECT username FROM '.DBPREFIX.'access_users WHERE id = '.$this->entries[$entryId]['userid'].' LIMIT 1');
				if($objResult !== false){
					$addedby = $objResult->fields('username');
				}

				$this->entries[$entryId]['regdate'] == '' ? $date = 'KEY: '.$this->entries[$entryId]['regkey'] : $date = date("d.m.Y", $this->entries[$entryId]['regdate']);

		   		$this->_objTpl->setVariable(array(
				    'ENTRY_TITLE'			=>	$this->entries[$entryId]['title'],
				    'ENTRY_DATE'			=>	$date,
				    'ENTRY_ID'				=>	$entryId,
				    'ENTRY_DESCRIPTION'		=>	$this->entries[$entryId]['description'],
				    'ENTRY_TYPE'			=>	$type,
				    'ENTRY_ADDEDBY'			=>	$addedby,
				    'ENTRY_ICON'			=>	$led,
				    'ENTRY_ROWCLASS'		=>	$row,
				    'ENTRY_SORT_ID_STATUS'	=>  $sortId,
				    'ENTRY_STATUS'			=>	$this->entries[$entryId]['status'],
				));

				$i++;
		   		$this->_objTpl->parse('showEntries');
		   	}
		}else{
			$this->_objTpl->hideBlock('showEntries');
			$this->strErrMessage = $_ARRAYLANG['TXT_MARKET_NO_ENTRIES_FOUND'];
		}
	}



	function addEntry(){

		global $objDatabase, $objTemplate, $_ARRAYLANG, $_CORELANG;

		$this->_pageTitle = $_ARRAYLANG['TXT_NEW_ENTRY'];
		$this->_objTpl->loadTemplateFile('module_market_entry.html',true,true);

		$this->getCategories();
		$categories = '';
		foreach($this->categories as $catId => $catValue) {
			$categories .= '<option value="'.$catId.'">'.$this->categories[$catId]['name'].'</option>';
		}

		if ($this->settings['maxdayStatus'] == 1) {
			$daysOnline = '';
			for($x = $this->settings['maxday']; $x >= 1; $x--){
				$daysOnline .= '<option value="'.$x.'">'.$x.'</option>';
			}
			
			$daysJS = 'if (days.value == "") {
					        errorMsg = errorMsg + "- '.$_ARRAYLANG['TXT_MARKET_DURATION'].'\n";
					   }
					   ';
		}

		$this->_objTpl->setVariable(array(
		    'TXT_TITLE'						=>	$_ARRAYLANG['TXT_NEW_ENTRY'],
		    'TXT_NAME'						=>	$_CORELANG['TXT_NAME'],
		    'TXT_E-MAIL'					=>	$_CORELANG['TXT_EMAIL'],
	    	'TXT_TITLE_ENTRY'				=>	$_ARRAYLANG['TXT_MARKET_TITLE'],
	    	'TXT_DESCRIPTION'				=>	$_CORELANG['TXT_DESCRIPTION'],
	    	'TXT_SAVE'						=>	$_CORELANG['TXT_SAVE'],
	    	'TXT_FIELDS_REQUIRED'			=>	$_ARRAYLANG['TXT_MARKET_CATEGORY_ADD_FILL_FIELDS'],
	    	'TXT_THOSE_FIELDS_ARE_EMPTY'	=>	$_ARRAYLANG['TXT_MARKET_FIELDS_NOT_CORRECT'],
	    	'TXT_PICTURE'					=>	$_ARRAYLANG['TXT_MARKET_IMAGE'],
	    	'TXT_CATEGORIE'					=>	$_CORELANG['TXT_CATEGORY'],
	    	'TXT_PRICE'						=>	$_ARRAYLANG['TXT_MARKET_PRICE'].$this->settings['currency'],
	    	'TXT_TYPE'						=>	$_CORELANG['TXT_TYPE'],
	    	'TXT_OFFER'						=>	$_ARRAYLANG['TXT_MARKET_OFFER'],
	    	'TXT_SEARCH'					=>	$_ARRAYLANG['TXT_MARKET_SEARCH'],
	    	'TXT_FOR_FREE'					=>	$_ARRAYLANG['TXT_MARKET_FREE'],
	    	'TXT_AGREEMENT'					=>	$_ARRAYLANG['TXT_MARKET_ARRANGEMENT'],
	    	'TXT_END_DATE'					=>	$_ARRAYLANG['TXT_MARKET_DURATION'],
	    	'END_DATE_JS'					=>	$daysJS,
	    	'TXT_ADDED_BY'					=>	$_ARRAYLANG['TXT_MARKET_ADDEDBY'],
	    	'TXT_USER_DETAIL'				=>	$_ARRAYLANG['TXT_MARKET_USERDETAILS'],
	    	'TXT_DETAIL_SHOW'				=>	$_ARRAYLANG['TXT_MARKET_SHOW_IN_ADVERTISEMENT'],
	    	'TXT_DETAIL_HIDE'				=>	$_ARRAYLANG['TXT_MARKET_NO_SHOW_IN_ADVERTISEMENT'],
	    	'TXT_PREMIUM'					=>	$_ARRAYLANG['TXT_MARKET_MARK_ADVERTISEMENT']
		));
		
		if ($this->settings['maxdayStatus'] != 1) {
			$this->_objTpl->hideBlock('end_date_dropdown');
		}
		
		$objReslut = $objDatabase->Execute("SELECT id, name, value FROM ".DBPREFIX."module_market_spez_fields WHERE lang_id = '1' AND active='1' ORDER BY id DESC");
      	if($objReslut !== false){
			while(!$objReslut->EOF){
				$this->_objTpl->setCurrentBlock('spez_fields');
				
				($i % 2)? $class = "row2" : $class = "row1";
				$input = '<input type="text" name="spez_'.$objReslut->fields['id'].'" style="width: 300px;" maxlength="100">';
				
				// initialize variables
				$this->_objTpl->setVariable(array(
					'SPEZ_FIELD_ROW_CLASS'		=> $class,
					'TXT_SPEZ_FIELD_NAME'		=> $objReslut->fields['value'],
					'SPEZ_FIELD_INPUT'  		=> $input,
				));
				
				$this->_objTpl->parse('spez_fields');
				$i++;
				$objReslut->MoveNext();
			}
      	}

		$this->_objTpl->setVariable(array(
	    	'FORM_ACTION'					=>	"addEntry",
	    	'CATEGORIES'					=>	$categories,
	    	'ENTRY_ADDEDBY'					=>	$_SESSION['auth']['username'],
	    	'ENTRY_USERDETAILS_ON'			=>	"checked",
	    	'ENTRY_TYPE_OFFER'				=>	"checked",
	    	'DAYS_ONLINE'					=>	$daysOnline
		));

		if(isset($_POST['submitEntry'])){
			$this->insertEntry('1');
		}
	}



	function statusEntry(){

		global $objDatabase, $objTemplate, $_ARRAYLANG, $_CORELANG;

		$arrStatus = array();
		$i=0;
		$today = mktime(0, 0, 0, date("m")  , date("d"), date("Y"));

		if(isset($_POST['selectedEntryId'])){
			foreach($_POST['selectedEntryId'] as $arrKey => $entryId) {
				$_POST['frmShowEntries_MultiAction'] == 'activate' ? $newStatus = 0 : $newStatus = 1;

				$arrStatus[$i] = $entryId.",".$newStatus;
				$i++;
			}
		}elseif($_GET['id']){
			$arrStatus[$i] = $_GET['id'];
		}

		foreach($arrStatus as $arrKey => $entryId) {
			$array 		= explode(',',$entryId);
			$id 		= $array[0];
			$status 	= $array[1];

			if($status == 0){
				$objResultDate = $objDatabase->Execute('SELECT regdate FROM '.DBPREFIX.'module_market WHERE id = '.$id.' LIMIT 1');
				if($objResultDate !== false){
					$regdate = $objResultDate->fields('regdate');
				}

				if($regdate == ''){
					$objResult = $objDatabase->Execute("UPDATE ".DBPREFIX."module_market SET status='1', regdate = '".$today."', regkey='' WHERE id = '".$id."'");
				}else{
					$objResult = $objDatabase->Execute("UPDATE ".DBPREFIX."module_market SET status='1' WHERE id = '".$id."'");
				}
			}else{
				$objResult = $objDatabase->Execute("UPDATE ".DBPREFIX."module_market SET status='0' WHERE id = '".$id."'");
			}
		}

		if ($objResult !== false){
			$this->strOkMessage = $_ARRAYLANG['TXT_MARKET_STATUS_CHANGED'];
	   	}else{
	   		$this->strErrMessage = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
	   	}
	}





	function deleteEntry(){

		global $objDatabase, $objTemplate, $_ARRAYLANG;

		$arrDelete = array();
		$i = 0;

		if(isset($_POST['selectedEntryId'])){
			foreach($_POST['selectedEntryId'] as $arrKey => $entryId) {
				$arrDelete[$i] = $entryId;
				$i++;
			}
		}elseif($_GET['id']){
			$arrDelete[$i] = $_GET['id'];
		}

	   	$this->removeEntry($arrDelete);
	}



	function editEntry(){

		global $objDatabase, $objTemplate, $_ARRAYLANG, $_CORELANG;

		$this->_pageTitle = $_ARRAYLANG['TXT_EDIT_ADVERTISEMENT'];
		$this->_objTpl->loadTemplateFile('module_market_entry.html',true,true);

		$this->_objTpl->setVariable(array(
		    'TXT_TITLE'						=>	$_ARRAYLANG['TXT_EDIT_ADVERTISEMENT'],
	    	'TXT_TITLE_ENTRY'				=>	$_ARRAYLANG['TXT_MARKET_TITLE'],
	    	'TXT_NAME'						=>	$_CORELANG['TXT_NAME'],
		    'TXT_E-MAIL'					=>	$_CORELANG['TXT_EMAIL'],
	    	'TXT_DESCRIPTION'				=>	$_CORELANG['TXT_DESCRIPTION'],
	    	'TXT_SAVE'						=>	$_CORELANG['TXT_SAVE'],
	    	'TXT_FIELDS_REQUIRED'			=>	$_ARRAYLANG['TXT_MARKET_CATEGORY_ADD_FILL_FIELDS'],
	    	'TXT_THOSE_FIELDS_ARE_EMPTY'	=>	$_ARRAYLANG['TXT_MARKET_FIELDS_NOT_CORRECT'],
	    	'TXT_PICTURE'					=>	$_ARRAYLANG['TXT_MARKET_IMAGE'],
	    	'TXT_CATEGORIE'					=>	$_CORELANG['TXT_CATEGORY'],
	    	'TXT_PRICE'						=>	$_ARRAYLANG['TXT_MARKET_PRICE']." ".$this->settings['currency'],
	    	'TXT_TYPE'						=>	$_CORELANG['TXT_TYPE'],
	    	'TXT_OFFER'						=>	$_ARRAYLANG['TXT_MARKET_OFFER'],
	    	'TXT_SEARCH'					=>	$_ARRAYLANG['TXT_MARKET_SEARCH'],
	    	'TXT_FOR_FREE'					=>	$_ARRAYLANG['TXT_MARKET_FREE'],
	    	'TXT_AGREEMENT'					=>	$_ARRAYLANG['TXT_MARKET_ARRANGEMENT'],
	    	'TXT_END_DATE'					=>	$_ARRAYLANG['TXT_MARKET_DURATION'],
	    	'TXT_ADDED_BY'					=>	$_ARRAYLANG['TXT_MARKET_ADDEDBY'],
	    	'TXT_ADDED_BY'					=>	$_ARRAYLANG['TXT_MARKET_ADDEDBY'],
	    	'TXT_USER_DETAIL'				=>	$_ARRAYLANG['TXT_MARKET_USERDETAILS'],
	    	'TXT_DETAIL_SHOW'				=>	$_ARRAYLANG['TXT_MARKET_SHOW_IN_ADVERTISEMENT'],
	    	'TXT_DETAIL_HIDE'				=>	$_ARRAYLANG['TXT_MARKET_NO_SHOW_IN_ADVERTISEMENT'],
	    	'TXT_PREMIUM'					=>	$_ARRAYLANG['TXT_MARKET_MARK_ADVERTISEMENT'],
	    	'FORM_ACTION'					=>	"editEntry",
	    	'TXT_DAYS'						=>	$_ARRAYLANG['TXT_MARKET_DAYS'],
		));

		if(isset($_REQUEST['id'])){
			$entryId = $_REQUEST['id'];
			$objResult = $objDatabase->Execute('SELECT type, title, description, premium, picture, catid, price, regdate, enddate, userid, name, email, userdetails, spez_field_1, spez_field_2, spez_field_3, spez_field_4, spez_field_5  FROM '.DBPREFIX.'module_market WHERE id = '.$entryId.' LIMIT 1');
			if($objResult !== false){
				while (!$objResult->EOF) {
					//entry type
					if($objResult->fields['type'] == 'offer'){
						$offer 	= 'checked';
					    $search	= '';
					}else{
						$offer 	= '';
					    $search	= 'checked';
					}
					//entry premium
					if($objResult->fields['premium'] == '1'){
						$premium 	= 'checked';
					}else{
					    $premium	= '';
					}
					//entry price
					if($objResult->fields['price'] == 'forfree'){
						$forfree 	= 'checked';
						$price 		= '';
						$agreement 	= '';
					}elseif($objResult->fields['price'] == 'agreement'){
					    $agreement	= 'checked';
					    $price 		= '';
					    $forfree 	= '';
					}else{
						$price 		= $objResult->fields['price'];
						$forfree 	= '';
						$agreement 	= '';
					}
					//entry user
					$objResultUser = $objDatabase->Execute('SELECT username FROM '.DBPREFIX.'access_users WHERE id = '.$objResult->fields['userid'].' LIMIT 1');
					if($objResultUser !== false){
						$addedby = $objResultUser->fields('username');
					}
					//entry userdetails
					if($objResult->fields['userdetails'] == '1'){
						$userdetailsOn 		= 'checked';
						$userdetailsOff 	= '';
					}else{
					    $userdetailsOn 		= '';
						$userdetailsOff 	= 'checked';
					}
					//entry picture
					if($objResult->fields['picture'] != ''){
						$picture 		= '<img src="'.$this->mediaWebPath.'pictures/'.$objResult->fields['picture'].'" border="0" alt="" /><br /><br />';
					}else{
					    $picture 		= '<img src="'.$this->mediaWebPath.'pictures/no_picture.gif" border="0" alt="" /><br /><br />';
					}
					//entry category
					$this->getCategories();
					$categories 	= '';
					$checked	 	= '';
					foreach($this->categories as $catId => $catValue) {
						$catId == $objResult->fields['catid'] ? $checked = 'selected' : $checked = '';
						$categories .= '<option value="'.$catId.'" '.$checked.'>'.$this->categories[$catId]['name'].'</option>';
					}



					//rest days
					$today = mktime(0, 0, 0, date("m")  , date("d"), date("Y"));
					$tempDays 	= date("d", $today);
					$tempMonth 	= date("m", $today);
					$tempYear 	= date("Y", $today);

					$x=0;
					while($objResult->fields['enddate'] >= $tempDate):
						$x++;
						$tempDate  = mktime(0, 0, 0, $tempMonth, $tempDays+$x,  $tempYear);
					endwhile;


					$restDays = $x-1;
					$daysOnline = '';
					for($x = $this->settings['maxday']; $x >= 0; $x--){
						$restDays == $x ? $selected = 'selected' : $selected = '';
						$daysOnline .= '<option value="'.$x.'" '.$selected.'>'.$x.'</option>';
					}
					
					//spez fields
					$objSpezFields = $objDatabase->Execute("SELECT id, name, value FROM ".DBPREFIX."module_market_spez_fields WHERE lang_id = '1' AND active='1' ORDER BY id DESC");
			      	if($objSpezFields !== false){
						while(!$objSpezFields->EOF){
							
							($i % 2)? $class = "row2" : $class = "row1";
							$input = '<input type="text" name="spez_'.$objSpezFields->fields['id'].'" value="'.$objResult->fields[$objSpezFields->fields['name']].'" style="width: 300px;" maxlength="100">';
							
							// initialize variables
							$this->_objTpl->setVariable(array(
								'SPEZ_FIELD_ROW_CLASS'		=> $class,
								'TXT_SPEZ_FIELD_NAME'		=> $objSpezFields->fields['value'],
								'SPEZ_FIELD_INPUT'  		=> $input,
							));
							
							$this->_objTpl->parse('spez_fields');
							$i++;
							$objSpezFields->MoveNext();
						}
			      	}

					$this->_objTpl->setVariable(array(
					    'ENTRY_ID'					=>	$entryId,
					    'ENTRY_TYPE_OFFER'			=>	$offer,
					    'ENTRY_TYPE_SEARCH'			=>	$search,
					    'ENTRY_TITLE'				=>	$objResult->fields['title'],
					    'ENTRY_DESCRIPTION'			=>	$objResult->fields['description'],
					    'ENTRY_PICTURE'				=>	$picture,
					    'ENTRY_PICTURE_OLD'			=>	$objResult->fields['picture'],
					    'CATEGORIES'				=>	$categories,
					    'ENTRY_PRICE'				=>	$price,
					    'ENTRY_FOR_FREE'			=>	$forfree,
					    'ENTRY_AGREEMENT'			=>	$agreement,
					    'ENTRY_PREMIUM'				=>	$premium,
					    'ENTRY_ADDEDBY'				=>	$addedby,
					    'ENTRY_ADDEDBY_ID'			=>	$objResult->fields['userid'],
					    'ENTRY_USERDETAILS_ON'		=>	$userdetailsOn,
					    'ENTRY_USERDETAILS_OFF'		=>	$userdetailsOff,
						'DAYS_ONLINE'				=>	$daysOnline,
						'ENTRY_NAME'				=>	$objResult->fields['name'],
					    'ENTRY_E-MAIL'				=>	$objResult->fields['email'],
					));
			   		$objResult->MoveNext();
			   	}
			}
		}else{
			$this->entries();
		}

		if(isset($_POST['submitEntry'])){
			if($_FILES['pic']['name'] != ""){
				$picture = $this->uploadPicture();
				if($picture != "error"){
					$objFile = new File();
					$status = $objFile->delFile($this->mediaPath, $this->mediaWebPath, "pictures/".$_POST['picOld']);
				}
			}else{
				$picture = $_POST['picOld'];
			}

			if($picture != "error"){
				if($_POST['forfree'] == 1){
					$price = "forfree";
				}elseif($_POST['agreement'] == 1){
					$price = "agreement";
				}else{
					$price = $_POST['price'];
				}

				$today = mktime(0, 0, 0, date("m")  , date("d"), date("Y"));
				$tempDays 	= date("d");
				$tempMonth 	= date("m");
				$tempYear 	= date("Y");
				$enddate  = mktime(0, 0, 0, $tempMonth, $tempDays+$_POST['days'],  $tempYear);

				$objResult = $objDatabase->Execute("UPDATE ".DBPREFIX."module_market SET
				                    type='".contrexx_addslashes($_POST['type'])."',
			                      	title='".contrexx_addslashes($_POST['title'])."',
			                      	description='".contrexx_addslashes($_POST['description'])."',
									premium='".contrexx_addslashes($_POST['premium'])."',
				                  	picture='".$picture."',
				                  	catid='".contrexx_addslashes($_POST['cat'])."',
				                  	price='".$price."',
				                  	enddate='".$enddate."',
				                  	userid='".contrexx_addslashes($_POST['userid'])."',
				                  	name='".contrexx_addslashes($_POST['name'])."',
				                  	email='".contrexx_addslashes($_POST['email'])."',
				                  	spez_field_1='".contrexx_addslashes($_POST['spez_1'])."',
				                  	spez_field_2='".contrexx_addslashes($_POST['spez_2'])."',
				                  	spez_field_3='".contrexx_addslashes($_POST['spez_3'])."',
				                  	spez_field_4='".contrexx_addslashes($_POST['spez_4'])."',
				                  	spez_field_5='".contrexx_addslashes($_POST['spez_5'])."',
				                  	userdetails='".contrexx_addslashes($_POST['userdetails'])."'
				                  	WHERE id='".intval($_POST['id'])."'");

				if($objResult !== false){
					$this->strOkMessage = $_ARRAYLANG['TXT_MARKET_EDIT_SUCCESSFULL'];
					$this->entries();
				}else{
					$this->strErrMessage = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
				}
			}else{
				$this->strErrMessage = $_ARRAYLANG['TXT_MARKET_IMAGE_UPLOAD_ERROR'];
			}
		}
	}


	function sysSettings()
	{
		global $_ARRAYLANG, $_CORELANG;
    	$this->_objTpl->loadTemplateFile('module_market_settings.html',true,true);
    	$this->_pageTitle = $_CORELANG['TXT_SETTINGS'];
    	
    	$_ARRAYLANG['TXT_MARKET_SPEZ_FIELDS']		= 'Zusatzfelder';
		$_ARRAYLANG['TXT_MARKET_PAYPAL']			= 'Zahlungsart';

    	$this->_objTpl->setGlobalVariable(array(
    		'TXT_SYSTEM'		=> $_CORELANG['TXT_SETTINGS_MENU_SYSTEM'],
    		'TXT_EMAIL'		    => $_ARRAYLANG['TXT_MARKET_VALIDATION_EMAIL'],
    		'TXT_EMAIL_CODE'	=> $_ARRAYLANG['TXT_MARKET_CLEARING_EMAIL'],
    		'TXT_SPEZ_FIELDS'	=> $_ARRAYLANG['TXT_MARKET_SPEZ_FIELDS'],
    		'TXT_PAYPAL'		=> $_ARRAYLANG['TXT_MARKET_PAYPAL'],
    	));

    	if(!isset($_GET['tpl'])){
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
    		case 'paypal':
    			$this->paypalSettings();
    			break;
    		case 'spez_fields':
    			$this->spezfieldsSettings();
    			break;
    		default:
    			$this->systemSettings();
    			break;
    	}

    	$this->_objTpl->parse('requests_block');
	}
	
	
	function spezfieldsSettings(){

		global $objDatabase, $objTemplate, $_ARRAYLANG, $_CORELANG;
		
		$_ARRAYLANG['TXT_MARKET_SPEZ_FIELDS']			= 'Zusatzfelder';
		$_ARRAYLANG['TXT_MARKET_PLACEHOLDER_CONTENT']	= 'Platzhalter f�r Inhalt';
		$_ARRAYLANG['TXT_MARKET_PLACEHOLDER_TITLE']		= 'Platzhalter f�r Titel';

		// initialize variables
		$this->_objTpl->addBlockfile('SYSTEM_REQUESTS_CONTENT', 'requests_block', 'module_market_settings_spez_fields.html');
		
		$this->_objTpl->setVariable(array(
		    'TXT_TITLE' 						=> $_ARRAYLANG['TXT_MARKET_SPEZ_FIELDS'],
			'TXT_STATUS' 						=> $_CORELANG['TXT_STATUS'],
	    	'TXT_NAME'							=> $_CORELANG['TXT_NAME'],
			'TXT_SAVE'							=> $_ARRAYLANG['TXT_SAVE'],
			'TXT_TYPE'							=> $_CORELANG['TXT_TYPE'],
			'TXT_TYPE'							=> $_ARRAYLANG['TXT_TYPE'],
			'TXT_TYPE'							=> $_ARRAYLANG['TXT_TYPE'],
			'TXT_PLACEHOLDER_TITLE'				=> $_ARRAYLANG['TXT_MARKET_PLACEHOLDER_TITLE'],
			'TXT_PLACEHOLDER_CONTENT'			=> $_ARRAYLANG['TXT_MARKET_PLACEHOLDER_CONTENT'],
		));
		
		$i=0;
		
      	$objReslut = $objDatabase->Execute("SELECT id, name, value, type, active FROM ".DBPREFIX."module_market_spez_fields WHERE lang_id = '1' ORDER BY active DESC");
      	if($objReslut !== false){
			while(!$objReslut->EOF){
				$this->_objTpl->setCurrentBlock('spez_fields');
				
				($i % 2)? $class = "row2" : $class = "row1";
				($objReslut->fields['active'] == 1)? $status = 'checked="checked"' : $status = "";
				($objReslut->fields['type'] == 1)? $type = "Textfeld" : $type = "Mehrzeiliges Textfeld";

				// initialize variables
				$this->_objTpl->setVariable(array(
					'SPEZ_FIELD_ROWCLASS'		=> $class,
					'SPEZ_FIELD_ID'				=> $objReslut->fields['id'],
					'SPEZ_FIELD_TITLE'  		=> $objReslut->fields['value'],
					'SPEZ_FIELD_NAME'  			=> $objReslut->fields['name'],
					'SPEZ_FIELD_STATUS'  		=> $status,
					'SPEZ_FIELD_TYPE'  			=> $type,
					'PLACEHOLDER_TITLE'  		=> "[[TXT_MARKET_".strtoupper($objReslut->fields['name'])."]]",
					'PLACEHOLDER_CONTENT'  		=> "[[MARKET_".strtoupper($objReslut->fields['name'])."]]",
				));
				$this->_objTpl->parseCurrentBlock('spez_fields');
				$i++;
				$objReslut->MoveNext();
			}
      	}
      	
      	if(isset($_POST['submitSettings'])){
			foreach ($_POST['setname'] as $id => $name) {
				$name		= contrexx_addslashes($name);
				$value		= contrexx_addslashes($_POST['settitle'][$id]);
				$status		= contrexx_addslashes($_POST['setstatus'][$id]);
				
				$objResult = $objDatabase->Execute("UPDATE ".DBPREFIX."module_market_spez_fields SET value='".$value."', active='".$status."' WHERE name='".$name."'");
			}

			if($objResult !== false){
				header('Location: ?cmd=market&act=settings&tpl=spez_fields');
				$this->strOkMessage = $_ARRAYLANG['TXT_MARKET_SETTINGS_UPDATED'];
			}else{
				$this->strErrMessage = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
			}
		}
	}



	function systemSettings(){

		global $objDatabase, $objTemplate, $_ARRAYLANG, $_CORELANG;

		// initialize variables
		$this->_objTpl->addBlockfile('SYSTEM_REQUESTS_CONTENT', 'requests_block', 'module_market_settings_system.html');

		$this->_objTpl->setVariable(array(
		    'TXT_TITLE' 						=> $_CORELANG['TXT_SETTINGS'],
			'TXT_DESCRIPTION' 					=> $_CORELANG['TXT_DESCRIPTION'],
			'TXT_VALUE' 						=> $_CORELANG['TXT_VALUE'],
			'TXT_SAVE'							=> $_CORELANG['TXT_SAVE'],
		));
		
		
		$_ARRAYLANG['TXT_MARKET_SET_MAXDAYS']		= 'max. Anzeigedauer (Tage)';
		$_ARRAYLANG['TXT_MARKET_SET_DESCRIPTION']	= 'Kategoriebeschreibung anzeigen';
		$_ARRAYLANG['TXT_MARKET_SET_PAGING']		= 'Anzahl Inserate pro Seite';
		$_ARRAYLANG['TXT_MARKET_SET_CURRENCY']		= 'W�hrung';
		$_ARRAYLANG['TXT_MARKET_SET_ADD_ENTRY_ONLY_COMMUNITY']		= 'Nur Mitglieder d�rfen Inserate hinzuf�gen (Community-Modul)';
		$_ARRAYLANG['TXT_MARKET_SET_ADD_ENTRY']		= 'Besuchern erlauben Inserate hinzuzuf�gen';
		$_ARRAYLANG['TXT_MARKET_SET_EDIT_ENTRY']	= 'Besuchern erlauben Inserate zu editieren';
		$_ARRAYLANG['TXT_MARKET_SET_INDEXVIEW']		= 'Index-Ansicht';
		$_ARRAYLANG['TXT_MARKET_SET_MAXDAYS_ON']	= 'Beschr�nkte Anzeigedauer';
		$_ARRAYLANG['TXT_MARKET_SET_EXP_SEARCH_PRICE']	= 'Auswahlfelder f�r erweiterte Suche nach "Preis bis"';

		//get settings
		$i=0;
      	$objReslut = $objDatabase->Execute("SELECT * FROM ".DBPREFIX."module_market_settings ORDER BY type ASC");
      	if($objReslut !== false){
			while(!$objReslut->EOF){
				$this->_objTpl->setCurrentBlock('settings');
				if ($objReslut->fields['type']== 1){
		            $setValueField = "<input type=\"text\" name=\"setvalue[".$objReslut->fields['id']."]\" value=\"".$objReslut->fields['value']."\" style='width: 300px;' maxlength='250'>";
				}elseif ($objReslut->fields['type']== 2){
					if($objReslut->fields['value'] == 1){
						$true = "checked";
						$false = "";
					}else{
						$false = "checked";
						$true = "";
					}
		            $setValueField = "<input type=\"radio\" name=\"setvalue[".$objReslut->fields['id']."]\" value=\"1\" ".$true.">&nbsp;Aktiviert&nbsp;<input type=\"radio\" name=\"setvalue[".$objReslut->fields['id']."]\" value=\"0\"".$false.">&nbsp;Deaktiviert&nbsp;";
				} else {
					 $setValueField = "<textarea name=\"setvalue[".$objReslut->fields['id']."]\" rows='5' style='width: 300px;'>".$objReslut->fields['value']."</textarea>";
				}

				($i % 2)? $class = "row2" : $class = "row1";

				// initialize variables
				$this->_objTpl->setVariable(array(
					'SETTINGS_ROWCLASS'		=> $class,
					'SETTINGS_SETVALUE'		=> $setValueField,
					'SETTINGS_DESCRIPTION'  => $_ARRAYLANG[$objReslut->fields['description']],
				));
				$this->_objTpl->parseCurrentBlock('settings');
				$i++;
				$objReslut->MoveNext();
			}
      	}

      	if(isset($_POST['submitSettings'])){
			foreach ($_POST['setvalue'] as $id => $value) {
				$objResult = $objDatabase->Execute("UPDATE ".DBPREFIX."module_market_settings SET value='".contrexx_addslashes($value)."' WHERE id=".intval($id));
			}

			if($objResult !== false){
				header('Location: ?cmd=market&act=settings');
				$this->strOkMessage = $_ARRAYLANG['TXT_MARKET_SETTINGS_UPDATED'];
			}else{
				$this->strErrMessage = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
			}
		}
	}



	function mailSettings(){

		global $objDatabase, $objTemplate, $_ARRAYLANG, $_CORELANG;

		// initialize variables
		$this->_objTpl->addBlockfile('SYSTEM_REQUESTS_CONTENT', 'requests_block', 'module_market_settings_mail.html');

		//get content
		$objReslut = $objDatabase->Execute("SELECT title, content, active, mailcc FROM ".DBPREFIX."module_market_mail WHERE id = '1'");
      	if($objReslut !== false){
			while(!$objReslut->EOF){
				$mailContent 	= $objReslut->fields['content'];
				$mailTitle 		= $objReslut->fields['title'];
				$mailCC 		= $objReslut->fields['mailcc'];
				$mailActive	 	= $objReslut->fields['active'];
				$objReslut->MoveNext();
			}
      	}

      	$mailActive == 1 ? $checked = 'checked' : $checked = '';

		$this->_objTpl->setVariable(array(
			'TXT_SAVE'							=> $_CORELANG['TXT_SAVE'],
			'TXT_EMAIL_TITLE'					=> $_CORELANG['TXT_EMAIL'],
			'TXT_PLACEHOLDER'					=> $_ARRAYLANG['TXT_MARKET_PLACEHOLDER'],
			'TXT_SETTINGS'						=> $_CORELANG['TXT_SETTINGS'],
			'TXT_MAIL_ON'						=> $_ARRAYLANG['TXT_MARKET_ACTIVATE_VALIDATION_EMAIL'],
			'TXT_MAIL_CC'						=> $_ARRAYLANG['TXT_MARKET_ADDITIONAL_RECIPENT'],
			'TXT_CONTENT'						=> $_CORELANG['TXT_CONTENT'],
			'TXT_SUBJECT'						=> $_ARRAYLANG['TXT_MARKET_SUBJECT'],
			'TXT_TEXT'							=> $_ARRAYLANG['TXT_MARKET_TEXT'],
			'TXT_URL'							=> $_ARRAYLANG['TXT_MARKET_URL'],
			'TXT_LINK'							=> $_ARRAYLANG['TXT_MARKET_LINK'],
			'TXT_NAME'							=> $_CORELANG['TXT_NAME'],
			'TXT_USERNAME'						=> $_CORELANG['TXT_USERNAME'],
			'TXT_ID'							=> $_ARRAYLANG['TXT_MARKET_ADVERTISEMENT_ID'],
			'TXT_TITLE'							=> $_ARRAYLANG['TXT_MARKET_ADVERTISEMENT_TITLE'],
			'TXT_DATE'							=> $_CORELANG['TXT_DATE'],
			'MAIL_CONTENT'						=> $mailContent,
			'MAIL_TITLE'						=> $mailTitle,
			'MAIL_CC'							=> $mailCC,
			'MAIL_ON'							=> $checked,
		));

		$this->_objTpl->setVariable(array(
			'MAIL_CONTENT'						=> $mailContent,
			'MAIL_TITLE'						=> $mailTitle,
			'MAIL_CC'							=> $mailCC,
			'MAIL_ON'							=> $checked,
		));

		if(isset($_POST['submitSettings'])){
			$objReslut = $objDatabase->Execute("UPDATE ".DBPREFIX."module_market_mail SET title='".$_POST['mailTitle']."', content='".$_POST['mailContent']."', mailcc='".$_POST['mailCC']."', active='".$_POST['mailOn']."' WHERE id='1'");
			if($objResult !== false){
				header('Location: ?cmd=market&act=settings&tpl=email');
				$this->strOkMessage = $_ARRAYLANG['TXT_MARKET_SETTINGS_UPDATED'];
			}else{
				$this->strErrMessage = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
			}
		}
	}


	function paypalSettings(){

		global $objDatabase, $objTemplate, $_ARRAYLANG, $_CORELANG;

		// initialize variables
		$this->_objTpl->addBlockfile('SYSTEM_REQUESTS_CONTENT', 'requests_block', 'module_market_settings_paypal.html');

		//get content
		$objReslut = $objDatabase->Execute("SELECT active, profile, price, price_premium FROM ".DBPREFIX."module_market_paypal WHERE id = '1'");
      	if($objReslut !== false){
			while(!$objReslut->EOF){
				$active 	= $objReslut->fields['active'];
				$profile 	= $objReslut->fields['profile'];
				$price 		= $objReslut->fields['price'];
				$premium 	= $objReslut->fields['price_premium'];
				$objReslut->MoveNext();
			}
      	}

      	$active == 1 ? $checked = 'checked' : $checked = '';

		$this->_objTpl->setVariable(array(
			'TXT_SAVE'							=> $_CORELANG['TXT_SAVE'],
			'TXT_PAYPAL_PROFILE'				=> $_ARRAYLANG['TXT_MARKTE_PAYPAL_EMAIL'],
			'TXT_PAYPAL_STATUS'					=> $_ARRAYLANG['TXT_MARKET_PAYPAL_ACTIVATE'],
			'TXT_PAYPAL_CURRENCIES'				=> $_ARRAYLANG['TXT_MARKET_PAYPAL_CURRENCIES'],
			'TXT_PAYPAL_PRICE'					=> $_ARRAYLANG['TXT_MARKET_PAYPAL_PRICE'],
			'TXT_PAYPAL_PREMIUM'				=> $_ARRAYLANG['TXT_MARKET_PAYPAL_MARKED'],
			'TXT_SETTINGS'						=> $_CORELANG['TXT_SETTINGS'],
			'STATUS_ON'							=> $checked,
			'PROFILE'							=> $profile,
			'PRICE'								=> $price,
			'PREMIUM'							=> $premium,
		));

		if(isset($_POST['submitSettings'])){
			$objReslut = $objDatabase->Execute("UPDATE ".DBPREFIX."module_market_paypal SET active='".$_POST['status']."', profile='".$_POST['profile']."', price='".$_POST['price']."', price_premium='".$_POST['premium']."' WHERE id='1'");
			if($objResult !== false){
				header('Location: ?cmd=market&act=settings&tpl=paypal');
				$this->strOkMessage = $_ARRAYLANG['TXT_MARKET_SETTINGS_UPDATED'];
			}else{
				$this->strErrMessage = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
			}
		}
	}


	function mail_codeSettings(){

		global $objDatabase, $objTemplate, $_ARRAYLANG, $_CORELANG;

		// initialize variables
		$this->_objTpl->addBlockfile('SYSTEM_REQUESTS_CONTENT', 'requests_block', 'module_market_settings_mail_code.html');

		//get content
		$objReslut = $objDatabase->Execute("SELECT title, content, active, mailcc FROM ".DBPREFIX."module_market_mail WHERE id = '2'");
      	if($objReslut !== false){
			while(!$objReslut->EOF){
				$mailContent 	= $objReslut->fields['content'];
				$mailTitle 		= $objReslut->fields['title'];
				$mailCC 		= $objReslut->fields['mailcc'];
				$mailActive	 	= $objReslut->fields['active'];
				$objReslut->MoveNext();
			}
      	}

      	$mailActive == 1 ? $checked = 'checked' : $checked = '';

		$this->_objTpl->setVariable(array(
			'TXT_SAVE'							=> $_CORELANG['TXT_SAVE'],
			'TXT_EMAIL_TITLE'					=> $_CORELANG['TXT_EMAIL'],
			'TXT_PLACEHOLDER'					=> $_ARRAYLANG['TXT_MARKET_PLACEHOLDER'],
			'TXT_SETTINGS'						=> $_CORELANG['TXT_SETTINGS'],
			'TXT_MAIL_ON'						=> $_ARRAYLANG['TXT_MARKET_CODE_SEND'],
			'TXT_MAIL_TO'						=> $_ARRAYLANG['TXT_MARKET_CODE_CLEARING_CODE'],
			'TXT_CONTENT'						=> $_CORELANG['TXT_CONTENT'],
			'TXT_SUBJECT'						=> $_ARRAYLANG['TXT_MARKET_SUBJECT'],
			'TXT_TEXT'							=> $_ARRAYLANG['TXT_MARKET_TEXT'],
			'TXT_URL'							=> $_ARRAYLANG['TXT_MARKET_URL'],
			'TXT_CODE'							=> $_ARRAYLANG['TXT_MARKET_CODE_CLEARINGCODE'],
			'TXT_NAME'							=> $_CORELANG['TXT_NAME'],
			'TXT_USERNAME'						=> $_CORELANG['TXT_USERNAME'],
			'TXT_ID'							=> $_ARRAYLANG['TXT_MARKET_ADVERTISEMENT_ID'],
			'TXT_TITLE'							=> $_ARRAYLANG['TXT_MARKET_ADVERTISEMENT_TITLE'],
			'TXT_DATE'							=> $_CORELANG['TXT_DATE']
		));

		$this->_objTpl->setVariable(array(
			'MAIL_CONTENT'						=> $mailContent,
			'MAIL_TITLE'						=> $mailTitle,
			'MAIL_TO'							=> $mailCC,
			'MAIL_ON'							=> $checked
		));

		if(isset($_POST['submitSettings'])){
			$objReslut = $objDatabase->Execute("UPDATE ".DBPREFIX."module_market_mail SET title='".$_POST['mailTitle']."', content='".$_POST['mailContent']."', mailcc='".$_POST['mailCC']."', active='".$_POST['mailOn']."' WHERE id='2'");
			if($objResult !== false){
				header('Location: ?cmd=market&act=settings&tpl=email_code');
				$this->strOkMessage = $_ARRAYLANG['TXT_MARKET_SETTINGS_UPDATED'];
			}else{
				$this->strErrMessage = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
			}
		}
	}
}
?>
