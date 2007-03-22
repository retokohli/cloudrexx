<?php
/**
 * Modul Feed
 *
 * Class to manage cms news feed
 *
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author        Paulo M. Santos <pmsantos@astalavista.net>   
 * @package     contrexx
 * @subpackage  module_feed_old
 * @todo        Edit PHP DocBlocks!
 */


//error_reporting(E_ALL);

require_once ASCMS_LIBRARY_PATH . '/PEAR/XML/RSS.class.php';
require_once ASCMS_MODULE_PATH . '/feed/feedLib.class.php';


if (ini_get('allow_url_fopen') != 1){
	@ini_set('allow_url_fopen', '1');
	if (ini_get('allow_url_fopen') != 1){
		die("Please set the variable 'allow_url_fopen' to the value 1");
	}
}

// START CLASS feed
class feedManager extends feedLibrary
{
	var $_objTpl;
	var $pageTitle;
	var $statusMessage;
	var $feedpath;
	var $_objNewsML;
	
    // CONSTRUCTOR
    function feedManager()
    {
	    global  $_ARRAYLANG, $objTemplate;
    	
		$this->_objTpl = &new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/feed/template');
		$this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);
		
        // links
    	$objTemplate->setVariable("CONTENT_NAVIGATION", "
    	    <a href='?cmd=feed' >".$_ARRAYLANG['TXT_FEED_NEWS_FEED']."</a>
    	    <a href='?cmd=feed&amp;act=newsML'>NewsML</a>
    		<a href='?cmd=feed&amp;act=category' >".$_ARRAYLANG['TXT_FEED_CATEGORIES']."</a>
    	");
    	
    	//feed path
		$this->feedpath = ASCMS_FEED_PATH . '/';
		
		
	}
    
    // GET PAGE
    function getFeedPage()
    {
		global $_ARRAYLANG, $objTemplate;
		
    	if (!isset($_GET['act'])){
    	    $_GET['act'] = '';	
    	}
    	
        switch($_GET['act']){
			case 'edit':
				$this->_objTpl->loadTemplateFile('module_feed_edit.html', true, true);
			    $this->pageTitle = $_ARRAYLANG['TXT_FEED_EDIT_NEWS_FEED'];
			    $this->showEdit();
			    break;
			case 'category':
			    $this->_objTpl->loadTemplateFile('module_feed_category.html', true, true); 
			    $this->pageTitle = $_ARRAYLANG['TXT_FEED_CATEGORIES'];
			    $this->showCategory();		        	
			    break;
			case 'catedit':
			    $this->_objTpl->loadTemplateFile('module_feed_category_edit.html', true, true);
			    $this->pageTitle = $_ARRAYLANG['TXT_FEED_EDIT_CATEGORIES'];
			    $this->showCatEdit();
			    break;
			case 'newsML':
				require_once ASCMS_FRAMEWORK_PATH.'/NewsML.class.php';
				$this->_objNewsML = &new NewsML(true);
				$this->_showNewsML();
				break;
				
			default:
			    $this->_objTpl->loadTemplateFile('module_feed.html', true, true);
				$this->pageTitle = $_ARRAYLANG['TXT_FEED_NEWS_FEED'];
				$this->showNews();
		}
		
		if (!isset($_SESSION['statusMessage'])){
			$_SESSION['statusMessage'] = '';
		}
		
		$objTemplate->setVariable(array(
			'CONTENT_TITLE'	=> $this->pageTitle,
			'CONTENT_STATUS_MESSAGE'	=> $_SESSION['statusMessage']
		));
		unset($_SESSION['statusMessage']);
		
		$objTemplate->setVariable('ADMIN_CONTENT', $this->_objTpl->get());
    }
    
    function _showNewsML()
    {
    	if (!isset($_REQUEST['tpl'])) {
    		$_REQUEST['tpl'] = '';
    	}
    	
    	switch ($_REQUEST['tpl']) {
    	case 'details':
    		$this->_newsMLDetails();
    		break;
    		
    	case 'editCategory':
    		$this->_newsMLEditCategory();
    		break;
    		
    	case 'deleteDocument':
    		$this->_newsMLDeleteDocument();
    		$this->_newsMLDetails();
    		break;
    		
    	case 'deleteDocuments':
    		$this->_newsMLDeleteDocuments();
    		$this->_newsMLDetails();
    		break;
    		
    	default:
			$this->_newsMLOverview();
    		break;
    	}
    	
    	
    }
    
    function _newsMLEditCategory()
    {
    	global $_ARRAYLANG;
    	
    	$this->_objTpl->loadTemplateFile('module_feed_newsml_edit_category.html');
    	
    	$this->_objTpl->setVariable(array(
    		'FEED_CATEGORY_TITLE'	=> $_ARRAYLANG['TXT_FEED_EDIT_CAT'],
    		'TXT_FEED_NAME'			=> $_ARRAYLANG['TXT_FEED_NAME'],
    		'TXT_FEED_NEWSML_PROVIDER'	=> $_ARRAYLANG['TXT_FEED_NEWSML_PROVIDER'],
    		'TXT_FEED_NEWSML_SUBJECT_CODES'	=> 'ss',
    		'TXT_FEED_NEWSML_MSG_COUNT'		=> 'ss',
    		'TXT_FEED_LAYOUT'				=> $_ARRAYLANG['TXT_FEED_LAYOUT']
    	));
    	
    	$categoryId = intval($_REQUEST['categoryId']);
    	
    	if (isset($this->_objNewsML->_arrNewsMLProviders[$categoryId])) {
    		$this->_objTpl->setVariable(array(
    		'FEED_NEWSML_CATEGORY_NAME'	=> $this->_objNewsML->_arrNewsMLProviders[$categoryId]['name'],
			'FEED_NEWSML_PROVIDER_MENU'	=> $this->_objNewsML->getProviderMenu($categoryId, 'name="feedNewsMLProviderId"'),
			'FEED_NEWSML_SUBJECT_CODES_MENU'	=> $this->_objNewsML->getSubjectCodesMenu($categoryId, 'name="feedNewsMLSubjectCode"'),
			'FEED_NEWSML_CATEGORY_MSG_COUNT'	=> $this->_objNewsML->_arrNewsMLProviders[$categoryId]['limit'],
			'FEED_NEWSML_CATEGORY_TEMPLATE'	=> $this->_objNewsML->_arrNewsMLProviders[$categoryId]['template']
    		));
    	} else {
    		$this->_newsMLOverview();
    	}
    }
    
    function _newsMLDeleteDocument()
    {
    	$id = intval($_GET['publicIdentifier']);
    	$this->_objNewsML->deleteNewsMLDocument($id);
    }
    
    function _newsMLDeleteDocuments()
    {
    	if (isset($_POST['selectedNewsMLDocId']) && count($_POST['selectedNewsMLDocId'])>0) {
    		foreach ($_POST['selectedNewsMLDocId'] as $id) {
    			$this->_objNewsML->deleteNewsMLDocument(contrexx_addslashes($id));
    		}
    	}
    }
    
    function _newsMLDetails()
    {
    	global $_ARRAYLANG;
    	
    	if (isset($_REQUEST['providerId']) && isset($this->_objNewsML->_arrNewsMLProviders[$_REQUEST['providerId']])) {
    		$providerId = intval($_REQUEST['providerId']);
    		
    		$this->_objTpl->loadTemplateFile('module_feed_newsml_details.html');
    		$this->pageTitle = 'NewsML';
    		
    		$this->_objTpl->setVariable(array(
    			'FEED_NEWSML_PROVIDER_NAME'	=> $this->_objNewsML->_arrNewsMLProviders[$providerId]['name'],
    			'TXT_MARKED'				=> $_ARRAYLANG['TXT_MARKED'],
    			'TXT_SELECT_ALL'			=> $_ARRAYLANG['TXT_SELECT_ALL'],
    			'TXT_REMOVE_SELECTION'		=> $_ARRAYLANG['TXT_REMOVE_SELECTION']
    		));
    		
    		$this->_objTpl->setGlobalVariable('FEED_NEWSML_PROVIDERID', $providerId);
    		
    		
    		$rowNr = 0;
    		
    		$this->_objNewsML->readNewsMLDocuments($providerId);
    		$arrNewsMLDocuments = $this->_objNewsML->getNewsMLDocuments($providerId);
    		
    		foreach ($arrNewsMLDocuments as $newsMLDocumentId => $arrNewsMLDocument) {
	    		$this->_objTpl->setVariable(array(
	    			'FEED_NEWSML_ID'				=> $newsMLDocumentId,
	    			'FEED_NEWSML_LIST_ROW_CLASS'	=> $rowNr % 2 == 0 ? "row1" : "row2",
	    			'FEED_NEWSML_TITLE'				=> $arrNewsMLDocument['headLine'],
	    			'FEED_NEWSML_DATE'				=> date(ASCMS_DATE_FORMAT, $arrNewsMLDocument['thisRevisionDate']),
	    			'FEED_NEWSML_RANK'				=> $arrNewsMLDocument['urgency']
	    		));
	    		$this->_objTpl->parse('feed_newsml_list');
	    		
	    		$rowNr++;
    		}
    		
    		
    	} else {
    		$this->_newsMLOverview();
    	}
    }
    
    /**
    * NewsML overview
    *
    * Show NewsML categories page
    *
    * @access private
    * @global object $objDatabase
    */
    function _newsMLOverview()
    {
    	global $_ARRAYLANG;
    	
    	$this->_objTpl->loadTemplateFile('module_feed_newsml_overview.html');
		$this->pageTitle = 'NewsML';
		
		$rowNr = 0;
		
		$this->_objTpl->setVariable(array(
			'TXT_FEED_NEWSML_CATEGORIES'	=> $_ARRAYLANG['TXT_FEED_NEWSML_CATEGORIES'],
			'TXT_FEED_CATEGORY'				=> $_ARRAYLANG['TXT_FEED_CATEGORY'],
			'TXT_FEED_TEMPLATE_PLACEHOLDER'	=> $_ARRAYLANG['TXT_FEED_TEMPLATE_PLACEHOLDER'],
			'TXT_FEED_NEWSML_PROVIDER'		=> $_ARRAYLANG['TXT_FEED_NEWSML_PROVIDER'],
			'TXT_FEED_FUNCTIONS'			=> $_ARRAYLANG['TXT_FEED_FUNCTIONS'],
			'TXT_FEED_SHOW_DETAILS'			=> $_ARRAYLANG['TXT_FEED_SHOW_DETAILS'],
			'TXT_FEED_EDIT_CATEGORY'		=> $_ARRAYLANG['TXT_FEED_EDIT_CATEGORY']
		));
		
		foreach ($this->_objNewsML->_arrNewsMLProviders as $newsMLProviderId => $arrNewsMLProvider) {
			$this->_objTpl->setVariable(array(
			'FEED_NEWSML_CATEGORY_ID'		=> $newsMLProviderId,
			'FEED_NEWSML_ID'				=> $newsMLProviderId,
			'FEED_NEWSML_LIST_ROW_CLASS'	=> $rowNr % 2 == 0 ? "row1" : "row2",
			'FEED_NEWSML_NAME'				=> $arrNewsMLProvider['name'],
			'FEED_NEWSML_PLACEHOLDER'		=> 'NEWSML_'.strtoupper($arrNewsMLProvider['name']),
			'FEED_NEWSML_PROVIDER'			=> $arrNewsMLProvider['providerName']
			));
			$this->_objTpl->parse('feed_newsml_list');
			
			$rowNr++;
		}
    }
	
	function showNews()
	{
		global $objDatabase, $_ARRAYLANG, $_LANGID;
		
		//refresh
		if (isset($_GET['ref']) and $_GET['ref'] == 1 and isset($_GET['id']) and $_GET['id'] != ''){
			$id   = intval($_GET['id']);
			$time = time();
			$this->showNewsRefresh($id, $time, $this->feedpath);
			$_SESSION['statusMessage'] = $_ARRAYLANG['TXT_FEED_MESSAGE_REFRESH_NEWS_FEED'];
			$this->goToReplace('');
			die;			
		}
		//preview
		if (isset($_GET['show']) and $_GET['show'] == 1 and isset($_GET['id']) and $_GET['id'] != ''){	
			$id = intval($_GET['id']);
			$this->showNewsPreview($id);
			die;			
		}
		//new
		if (isset($_GET['new']) and $_GET['new'] == 1){
			if ($_POST['form_category'] != '' and $_POST['form_name'] != '' and $_POST['form_articles'] != '' and $_POST['form_articles'] < 51 and $_POST['form_cache'] != '' and $_POST['form_image'] != '' and $_POST['form_status'] != '')
			{
				if ($_POST['form_file_name'] != '0' and $_POST['form_link'] == '' or $_POST['form_file_name'] == '0' and $_POST['form_link'] != ''){
					$category  = intval($_POST['form_category']);
					$name      = get_magic_quotes_gpc() ? strip_tags($_POST['form_name']) : addslashes(strip_tags($_POST['form_name']));
					if ($_POST['form_file_name'] != '0'){
					    $link     = '';
					    $filename = get_magic_quotes_gpc() ? strip_tags($_POST['form_file_name']) : addslashes(strip_tags($_POST['form_file_name']));
					}else{
						$link     = get_magic_quotes_gpc() ? strip_tags($_POST['form_link']) : addslashes(strip_tags($_POST['form_link']));
						$filename = '';
					}
					$articles  = intval($_POST['form_articles']);
					$cache     = intval($_POST['form_cache']);
					$time      = time();
					$image     = intval($_POST['form_image']);
					$status    = intval($_POST['form_status']);
					$this->showNewsNew($category, $name, $link, $filename, $articles, $cache, $time, $image, $status);
					$_SESSION['feedCategorySort'] = $category;
					$_SESSION['statusMessage'] = $_ARRAYLANG['TXT_FEED_MESSAGE_SUCCESSFULL_NEWS'];;
				}else{
					$_SESSION['statusMessage'] = $_ARRAYLANG['TXT_FEED_MESSAGE_ERROR_FILL_IN_ALL'];
				}
			}else{
				$_SESSION['statusMessage'] = $_ARRAYLANG['TXT_FEED_MESSAGE_ERROR_FILL_IN_ALL'];
			}
			
			$this->goToReplace('');
			die;
		}
		
		//sortbycategory
		if (isset($_GET['sort']) and $_GET['sort'] != 0){
		    $_SESSION['feedCategorySort'] = $_GET['sort'];
		    $this->goToReplace('');
			die;
		}elseif (isset($_GET['sort']) and $_GET['sort'] == 0){
		    unset($_SESSION['feedCategorySort']);
		    $this->goToReplace('');
			die;
		}
		
		if (isset($_GET['chg']) and $_GET['chg'] == 1 and isset($_POST['form_selected']) and is_array($_POST['form_selected'])){
			//delete
			if ($_POST['form_delete'] != ''){
				$ids = $_POST['form_selected'];
				$this->showNewsDelete($ids);
				$_SESSION['statusMessage'] = $_ARRAYLANG['TXT_FEED_MESSAGE_DELETED_NEWS'];
				$this->goToReplace('');
				die;
			}
			//changestatus
			if ($_POST['form_activate'] != '' or $_POST['form_deactivate'] != ''){
				$ids = $_POST['form_selected'];
				if ($_POST['form_activate'] != ''){
					$this->showNewsChange($ids, 1);
				}
				if ($_POST['form_deactivate'] != ''){
					$this->showNewsChange($ids, 0);
				}
				$_SESSION['statusMessage'] = $_ARRAYLANG['TXT_FEED_MESSAGE_STATUS'];
				$this->goToReplace('');
				die;
			}
		}
		
		//sort
		if (isset($_GET['chg']) and $_GET['chg'] == 1 and $_POST['form_sort'] != ''){
			$ids = $_POST['form_id'];
			$pos = $_POST['form_pos'];
			$this->showNewsChangePos($ids, $pos);
			$_SESSION['statusMessage'] = $_ARRAYLANG['TXT_FEED_MESSAGE_SORT'];
			$this->goToReplace('');
			die;
		}
		if (isset($_GET['chg'])){
			$ids = $_POST['form_id'];
			$pos = $_POST['form_pos'];
			$this->showNewsChangePos($ids, $pos);
			$_SESSION['statusMessage'] = $_ARRAYLANG['TXT_FEED_MESSAGE_SORT'];
			$this->goToReplace('');
			die;
		}
		
		//categories
		$query = "SELECT id,
		                   name
		              FROM ".DBPREFIX."module_feed_category
		          ORDER BY pos";
		$objResult = $objDatabase->Execute($query);
		
		while (!$objResult->EOF) {
			$this->_objTpl->setVariable(array(
			    'FEED_CATEGORY_ID'  => $objResult->fields['id'],
			    'FEED_CATEGORY'     => $objResult->fields['name']
			));
			$this->_objTpl->parse('feed_table_option');
			$objResult->MoveNext();
		}
		
		$query = "SELECT id,
		                   name
		              FROM ".DBPREFIX."module_feed_category
		          ORDER BY pos";
		$objResult = $objDatabase->Execute($query);
		
		//categories for show only
		while (!$objResult->EOF) {
			$query = "SELECT subid
			               FROM ".DBPREFIX."module_feed_news
			              WHERE subid = '".$objResult->fields['id']."'";
			$objResult2 = $objDatabase->Execute($query);
			
			if ($objResult2->RecordCount() != 0){
				$selected = '';
				if ($_SESSION['feedCategorySort'] == $objResult->fields['id']){
					$selected = ' selected';
				}
				
				$this->_objTpl->setVariable(array(
				    'FEED_CATEGORY_ID'  => $objResult->fields['id'],
				    'FEED_SELECTED'     => $selected,
				    'FEED_CATEGORY'     => $objResult->fields['name']
				));
				$this->_objTpl->parse('feed_category_option');
			}
			$objResult->MoveNext();
		}
		
		//directory
		$filename = array();
		$dir = opendir ($this->feedpath);
		while ($file = readdir($dir)) {
			if ($file != '.' and $file != '..' and $file != 'index.html' and substr($file, 0, 5) != 'feed_'){
  				$filename[] = $file;
			}
		}
		closedir($dir);
		
		asort($filename);
		foreach($filename as $file){
			$this->_objTpl->setVariable('FEED_NAME', $file);
			$this->_objTpl->parse('feed_table_option_name');
		}
		
		//lang
		$to_lang    = '';
		$to_lang[0] = '';
		
		$query = "SELECT id,
	                       lang 
	                  FROM ".DBPREFIX."languages
	                 WHERE id<>0 
	                 ORDER BY id";
		$objResult = $objDatabase->Execute($query);
		
		while (!$objResult->EOF) {
			$to_lang[$objResult->fields['id']] = $objResult->fields['lang'];
			$objResult->MoveNext();
		}
		
		//table
		if (!isset($_SESSION['feedCategorySort']) or $_SESSION['feedCategorySort'] == 0){
			$query = "SELECT id,
			                   name,
			                   lang
			              FROM ".DBPREFIX."module_feed_category
			          ORDER BY pos";
			$objResult = $objDatabase->Execute($query);
		} else {
			$query = "SELECT id,
			                   name,
			                   lang
			              FROM ".DBPREFIX."module_feed_category
			             WHERE id = '".$_SESSION['feedCategorySort']."'
			          ORDER BY pos";
			$objResult = $objDatabase->Execute($query);
			
			$query = "SELECT id
			               FROM ".DBPREFIX."module_feed_news
			              WHERE subid = '".$_SESSION['feedCategorySort']."'";
			$objResult2 = $objDatabase->Execute($query);
			
			if ($objResult2->RecordCount() == 0){
				unset($_SESSION['feedCategorySort']);
				$query = "SELECT id,
			                       name,
			                       lang
			                  FROM ".DBPREFIX."module_feed_category
			              ORDER BY pos";
				$objResult = $objDatabase->Execute($query);
			}
		}
	
		$i             = 0;
		$total_records = 0;
		while (!$objResult->EOF) {
		    $query = 	"SELECT id,
                                name,
	                            articles,
	                            cache,
                  FROM_UNIXTIME(time, '%H:%i - %d.%m.%Y') AS time,
	                            status,
                                pos
	                       FROM ".DBPREFIX."module_feed_news
	                      WHERE subid = '".$objResult->fields['id']."'
	                   ORDER BY pos";
		    $objResult2 = $objDatabase->Execute($query);
			
		    $total_records = $total_records + $objResult->RecordCount();
			
			while (!$objResult2->EOF) {
				($i % 2)                 ? $class  = 'row1'   : $class  = 'row2';
				($objResult2->fields['status'] == 1) ? $status = 'green'  : $status = 'red';
				
				$this->_objTpl->setVariable(array(
				    'FEED_CLASS'           => $class,
				    'FEED_STATUS'          => $status,
					'FEED_ID'              => $objResult2->fields['id'],
					'FEED_POS'             => $objResult2->fields['pos'],
					'FEED_NAME'            => $objResult2->fields['name'],
					'FEED_LANG'            => $to_lang[$objResult->fields['lang']],
					'FEED_CATEGORY'        => $objResult->fields['name'],
					'FEED_ARTICLE'         => $objResult2->fields['articles'],
					'FEED_CACHE'           => $objResult2->fields['cache'],
					'FEED_TIME'            => $objResult2->fields['time'],
				    'TXT_FEED_EDIT'        => $_ARRAYLANG['TXT_FEED_EDIT'],
				    'TXT_FEED_UPDATE'      => $_ARRAYLANG['TXT_FEED_UPDATE'],
				    'TXT_FEED_PREVIEW'     => $_ARRAYLANG['TXT_FEED_PREVIEW']
				));

				$this->_objTpl->parse('feed_table_row');
				$objResult2->MoveNext();
				$i++;
			}
			$objResult->MoveNext();
		}
		
		$this->_objTpl->setVariable('FEED_TOTAL_RECORDS', $total_records);
		
		//make visible
		if ($i > 0)
		{
			$this->_objTpl->setVariable(array(
			    'FEED_RECORDS_HIDDEN'           => '&nbsp;',
			    'TXT_FEED_MARK_ALL'             => $_ARRAYLANG['TXT_FEED_MARK_ALL'],
			    'TXT_FEED_REMOVE_CHOICE'        => $_ARRAYLANG['TXT_FEED_REMOVE_CHOICE'],
			    'TXT_FEED_SELECT_OPERATION'     => $_ARRAYLANG['TXT_FEED_SELECT_OPERATION'],
			    'TXT_FEED_SAVE_SORTING'         => $_ARRAYLANG['TXT_FEED_SAVE_SORTING'],
			    'TXT_FEED_ACTIVATE_NEWS_FEED'   => $_ARRAYLANG['TXT_FEED_ACTIVATE_NEWS_FEED'],
			    'TXT_FEED_DEACTIVATE_NEWS_FEED' => $_ARRAYLANG['TXT_FEED_DEACTIVATE_NEWS_FEED'],
			    'TXT_FEED_DELETE_NEWS_FEED'     => $_ARRAYLANG['TXT_FEED_DELETE_NEWS_FEED']
			));
			$this->_objTpl->parse('feed_table_hidden');		
		}
		
		//parse $_ARRAYLANG
		$this->_objTpl->setVariable(array(
		    'TXT_FEED_INSERT_NEW_FEED'      => $_ARRAYLANG['TXT_FEED_INSERT_NEW_FEED'],
		    'TXT_FEED_CATEGORY'             => $_ARRAYLANG['TXT_FEED_CATEGORY'],
		    'TXT_FEED_CHOOSE_CATEGORY'      => $_ARRAYLANG['TXT_FEED_CHOOSE_CATEGORY'],
		    'TXT_FEED_NAME'                 => $_ARRAYLANG['TXT_FEED_NAME'],
		    'TXT_FEED_LINK'                 => $_ARRAYLANG['TXT_FEED_LINK'],
		    'TXT_FEED_FILE_NAME'            => $_ARRAYLANG['TXT_FEED_FILE_NAME'],
		    'TXT_FEED_CHOOSE_FILE_NAME'     => $_ARRAYLANG['TXT_FEED_CHOOSE_FILE_NAME'],
		    'TXT_FEED_NUMBER_ARTICLES'      => $_ARRAYLANG['TXT_FEED_NUMBER_ARTICLES'],
		    'TXT_FEED_CACHE_TIME'           => $_ARRAYLANG['TXT_FEED_CACHE_TIME'],
		    'TXT_FEED_SHOW_LOGO'            => $_ARRAYLANG['TXT_FEED_SHOW_LOGO'],
		    'TXT_FEED_NO'                   => $_ARRAYLANG['TXT_FEED_NO'],
		    'TXT_FEED_YES'                  => $_ARRAYLANG['TXT_FEED_YES'],
		    'TXT_FEED_STATUS'               => $_ARRAYLANG['TXT_FEED_STATUS'],
		    'TXT_FEED_INACTIVE'             => $_ARRAYLANG['TXT_FEED_INACTIVE'],
		    'TXT_FEED_ACTIVE'               => $_ARRAYLANG['TXT_FEED_ACTIVE'],
		    'TXT_FEED_SAVE'                 => $_ARRAYLANG['TXT_FEED_SAVE'],
		    'TXT_FEED_SORTING'              => $_ARRAYLANG['TXT_FEED_SORTING'],
		    'TXT_FEED_STATUS'               => $_ARRAYLANG['TXT_FEED_STATUS'],
		    'TXT_FEED_ID'                   => $_ARRAYLANG['TXT_FEED_ID'],
		    'TXT_FEED_NEWS_NAME'            => $_ARRAYLANG['TXT_FEED_NEWS_NAME'],
		    'TXT_FEED_LANGUAGE'             => $_ARRAYLANG['TXT_FEED_LANGUAGE'],
		    'TXT_FEED_ALL_CATEGORIES'       => $_ARRAYLANG['TXT_FEED_ALL_CATEGORIES'],
		    'TXT_FEED_ARTICLE'              => $_ARRAYLANG['TXT_FEED_ARTICLE'],
		    'TXT_FEED_CACHE_TIME'           => $_ARRAYLANG['TXT_FEED_CACHE_TIME'],
		    'TXT_FEED_LAST_UPDATE'          => $_ARRAYLANG['TXT_FEED_LAST_UPDATE'],
		    'TXT_FEED_FORMCHECK_CATEGORY'   => $_ARRAYLANG['TXT_FEED_FORMCHECK_CATEGORY'],
		    'TXT_FEED_FORMCHECK_NAME'       => $_ARRAYLANG['TXT_FEED_FORMCHECK_NAME'],
		    'TXT_FEED_FORMCHECK_LINK_FILE'  => $_ARRAYLANG['TXT_FEED_FORMCHECK_LINK_FILE'],
		    'TXT_FEED_FORMCHECK_ARTICLES'   => $_ARRAYLANG['TXT_FEED_FORMCHECK_ARTICLES'],
		    'TXT_FEED_FORMCHECK_CACHE'      => $_ARRAYLANG['TXT_FEED_FORMCHECK_CACHE'],
		    'TXT_FEED_FORMCHECK_IMAGE'      => $_ARRAYLANG['TXT_FEED_FORMCHECK_IMAGE'],
		    'TXT_FEED_FORMCHECK_STATUS'     => $_ARRAYLANG['TXT_FEED_FORMCHECK_STATUS'],
		    'TXT_FEED_DELETE_CONFIRM'       => $_ARRAYLANG['TXT_FEED_DELETE_CONFIRM'],
		    'TXT_FEED_NO_SELECT_OPERATION'  => $_ARRAYLANG['TXT_FEED_NO_SELECT_OPERATION']
		));
	}
	    
	//---------------------------------------------------------------------------------------------
	
	//FUNC preview
	function showNewsPreview($id)
	{
		global $objDatabase, $_ARRAYLANG;
		
		$query = "SELECT filename,
		     FROM_UNIXTIME(time, '%d. %M %Y, %H:%i') AS time
			          FROM ".DBPREFIX."module_feed_news
			         WHERE id = '".$id."'";
		$objResult = $objDatabase->Execute($query);
		
		$filename = $this->feedpath.$objResult->fields['filename'];
		
		//rss class
		$rss =& new XML_RSS($filename);
		$rss->parse();
		
		//channel info
		$info = $rss->getChannelInfo();
		echo "<b>".strip_tags($info['title'])."</b><br />";
		echo $_ARRAYLANG['TXT_FEED_LAST_UPDATE'].": ".$objResult->fields['time']."<br />";
		
		//image
		foreach($rss->getImages() as $img) {
			if ($img['url'] != '') {
				echo '<img src="'.strip_tags($img['url']).'" /><br />';
			}
		}
		
		echo '<br />';
		echo '<i>'.$_ARRAYLANG['TXT_FEED_MESSAGE_IMPORTANT'].'</i><br />';
		
		//items
		foreach ($rss->getItems() as $value) {
			echo '<li>'.strip_tags($value['title']).'</li>';
		}
	}
	
	//FUNC new
	function showNewsNew($category, $name, $link, $filename, $articles, $cache, $time, $image, $status)
	{
		global $objDatabase, $_ARRAYLANG;
		
		$query = "SELECT id
		              FROM ".DBPREFIX."module_feed_news
		      WHERE BINARY name = '".$name."'";
		$objResult = $objDatabase->Execute($query);
		
		if ($objResult->RecordCount() == 0){
			if ($link != ''){				
				//copy
				$filename = "feed_".$time."_".basename($link);
				if (!copy($link, $this->feedpath.$filename)){
				    $_SESSION['statusMessage'] = $_ARRAYLANG['TXT_FEED_MESSAGE_ERROR_LINK_NO_NEWS'];
				    $this->goToReplace('');
				    die;
		        }
		        
		        /*$str = file_get_contents($link);
                $fp = fopen($this->feedpath.$filename, "w");
                fwrite($fp, $str);
                fclose($fp);*/
		        
				//rss class
				$rss =& new XML_RSS($this->feedpath.$filename);
				$rss->parse();
				$content = '';
				foreach($rss->getStructure() as $array){
					$content .= $array;
				}
				if ($content == ''){
					unlink($this->feedpath.$filename);
					$_SESSION['statusMessage'] = $_ARRAYLANG['TXT_FEED_MESSAGE_ERROR_LINK_NO_NEWS'];
				    $this->goToReplace('');
				    die;
				}
			}
			
			//add new
			$query = "INSERT INTO ".DBPREFIX."module_feed_news
						        SET subid = '".$category."',
			                        name = '".$name."',
			                        link = '".$link."',
			                        filename = '".$filename."',
			                        articles = '".$articles."',
			                        cache = '".$cache."',
			                        status = '".$status."',
			                        time = '".$time."',
			                        image = '".$image."'";
			$objResult = $objDatabase->Execute($query);
		} else{
			$_SESSION['statusMessage'] = $_ARRAYLANG['TXT_FEED_MESSAGE_ERROR_EXISTING_NEWS'];
			$this->goToReplace('');
			die;
		}
	}
	
	//FUNC delete
	function showNewsDelete($ids)
	{
		global $objDatabase, $_ARRAYLANG;
		
		foreach($ids as $id){
			$query = "SELECT id,
			                   link,
			                   filename
			              FROM ".DBPREFIX."module_feed_news
			             WHERE id = '".intval($id)."'";
			$objResult = $objDatabase->Execute($query);
			
			$link     = $objResult->fields['link'];
			$filename = $objResult->fields['filename'];
			
			if ($link != '') {
			    @unlink($this->feedpath.$filename);
			}
			
			$query = "DELETE FROM ".DBPREFIX."module_feed_news
	                     WHERE id = '".intval($id)."'";
			$objDatabase->Execute($query);
		}
		
		$query = "SELECT id
		              FROM ".DBPREFIX."module_feed_news";
		$objResult = $objDatabase->Execute($query);
		
		if ($objResult->RecordCount() == 0){
			$objDatabase->Execute("DELETE FROM ".DBPREFIX."module_feed_news");
		}
	}
	
	//FUNC changestatus
	function showNewsChange($ids, $to)
	{
		global $objDatabase, $_ARRAYLANG, $_LANGID;
		
		foreach($ids as $id){
			$query = "UPDATE ".DBPREFIX."module_feed_news
			               SET status = '".$to."'
			             WHERE id = '".intval($id)."'";
			$objDatabase->Execute($query);
		}
	}

	//FUNC sort
	function showNewsChangePos($ids, $pos)
	{
		global $objDatabase, $_ARRAYLANG, $_LANGID;
		
		for($x = 0; $x < count($ids); $x++){
			$query = "UPDATE ".DBPREFIX."module_feed_news
			               SET pos = '".intval($pos[$x])."'
			             WHERE id = '".intval($ids[$x])."'";
			$objDatabase->Execute($query);
		}
	}
	
///////////////////////////////////////////////////////////////////////////////////////////////////
	
	// CASE EDIT
	/////////////////////////
	
	function showEdit()
	{
		global $objDatabase, $_ARRAYLANG, $_LANGID;
		
		// check
		if (!isset($_GET['set'])){
			if (!isset($_GET['id']) or $_GET['id'] == ''){
				$this->goToReplace('');
				die;
			}
		}
		
		//set
		if (isset($_GET['set']) and $_GET['set'] == 1)
		{
			if ($_POST['form_id'] != '' and $_POST['form_category'] != '' and $_POST['form_name'] != '' and $_POST['form_articles'] != '' and $_POST['form_articles'] < 51 and $_POST['form_cache'] != '' and $_POST['form_image'] != '' and $_POST['form_status'] != '')
			{
				if ($_POST['form_link'] != '' and $_POST['form_file_name'] == '0' or $_POST['form_link'] == '' and $_POST['form_file_name'] != '0')
				{
					$id       = intval($_POST['form_id']);
					$subid    = intval($_POST['form_category']);
					$name     = get_magic_quotes_gpc() ? strip_tags($_POST['form_name']) : addslashes(strip_tags($_POST['form_name']));
					if ($_POST['form_file_name'] != '0')
					{
						$link     = '';
					    $filename = get_magic_quotes_gpc() ? strip_tags($_POST['form_file_name']) : addslashes(strip_tags($_POST['form_file_name']));
					}
					else
					{
						$link     = get_magic_quotes_gpc() ? strip_tags($_POST['form_link']) : addslashes(strip_tags($_POST['form_link']));
						$filename = '';
					}					
					$articles = intval($_POST['form_articles']);
					$cache    = intval($_POST['form_cache']);
					$time     = time();
					$image    = intval($_POST['form_image']);
					$status   = intval($_POST['form_status']);
					
					$this->showEditSetNew($id, $subid, $name, $link, $filename, $articles, $cache, $time, $image, $status);
					$_SESSION['statusMessage'] = $_ARRAYLANG['TXT_FEED_MESSAGE_SUCCESSFULL_EDIT_NEWS'];
					$this->goToReplace('');
					die;
				}
				else
				{
					$_SESSION['statusMessage'] = $_ARRAYLANG['TXT_FEED_MESSAGE_ERROR_FILL_IN_ALL'];
					$this->goToReplace('&act=edit&id='.$_POST['form_id']);
					die;
				}
			}
			else
			{
				$_SESSION['statusMessage'] = $_ARRAYLANG['TXT_FEED_MESSAGE_ERROR_FILL_IN_ALL'];
				$this->goToReplace('&act=edit&id='.$_POST['form_id']);
				die;
			}
		}
		
		//---------------------------------------------------------------------------------------------
		
		$query = "SELECT id,
		                   subid,
		                   name,
		                   link,
		                   filename,
		                   articles,
		                   cache,
		                   image,
		                   status
		              FROM ".DBPREFIX."module_feed_news
		             WHERE id = '".intval($_GET['id'])."'";
		$objResult = $objDatabase->Execute($query);
		
		$id        = $objResult->fields['id'];
		$subid     = $objResult->fields['subid'];
		$name      = $objResult->fields['name'];
		$link      = $objResult->fields['link'];
		$filename  = $objResult->fields['filename'];
		$articles  = $objResult->fields['articles'];
		$cache     = $objResult->fields['cache'];
		$image     = $objResult->fields['image'];
		$status    = $objResult->fields['status'];
		
		if ($image == 0) {
			$status_img0 = ' selected';
			$status_img1 = '';
		} else {
			$status_img0 = '';
			$status_img1 = ' selected';
		}
		
		if ($status == 0) {
			$status0 = ' selected';
			$status1 = '';
		} else {
			$status0 = '';
			$status1 = ' selected';
		}
		
		$this->_objTpl->setVariable(array(
		    'FEED_ID'           => $id,
		    'FEED_POS'          => $pos,
		    'FEED_NAME'         => $name,
		    'FEED_LINK'         => $link,
		    'FEED_ARTICLES'     => $articles,
		    'FEED_CACHE'        => $cache,
		    'FEED_IMG_STATUS0'  => $status_img0,
		    'FEED_IMG_STATUS1'  => $status_img1,
		    'FEED_STATUS0'      => $status0,
		    'FEED_STATUS1'      => $status1
		));
		
		// category
		$query = "SELECT id,
		                   name
		              FROM ".DBPREFIX."module_feed_category
		          ORDER BY pos";
		$objResult = $objDatabase->Execute($query);
		
		while(!$objResult->EOF) {
			if ($subid == $objResult->fields['id']) {
				$selected = ' selected';
			} else {
				$selected = '';
			}
			
			$this->_objTpl->setVariable(array(
			    'FEED_CATEGORY_ID'       => $objResult->fields['id'],
			    'FEED_CATEGORY_SELECTED' => $selected,
			    'FEED_CATEGORY'          => $objResult->fields['name']
			));
			$this->_objTpl->parse('feed_table_option');	
			$objResult->MoveNext();
		}
		
		//filename
		$allfiles = array();
		$dir = opendir ($this->feedpath);
		while($file = readdir($dir)) {
			if ($file != '.' and $file != '..' and $file != 'index.html' and substr($file, 0, 5) != 'feed_') {
  				$allfiles[] = $file;
			}
		}
		closedir($dir);
		
		asort($allfiles);
		foreach($allfiles as $file)
		{
			$status = '';
			if ($filename == $file)
			{
				$status = ' selected';
			}
			
			$this->_objTpl->setVariable(array(
			    'FEED_FILE'          => $file,
			    'FEED_FILE_SELECTED' => $status
			));
			$this->_objTpl->parse('feed_table_option_name');
		}
		
		//parse $_ARRAYLANG
		$this->_objTpl->setVariable(array(
		    'TXT_FEED_EDIT_NEWS_FEED'          => $_ARRAYLANG['TXT_FEED_EDIT_NEWS_FEED'],
		    'TXT_FEED_CATEGORY'                => $_ARRAYLANG['TXT_FEED_CATEGORY'],
		    'TXT_FEED_NAME'                    => $_ARRAYLANG['TXT_FEED_NAME'],
		    'TXT_FEED_LINK'                    => $_ARRAYLANG['TXT_FEED_LINK'],
		    'TXT_FEED_FILE_NAME'               => $_ARRAYLANG['TXT_FEED_FILE_NAME'],
		    'TXT_FEED_CHOOSE_FILE_NAME'        => $_ARRAYLANG['TXT_FEED_CHOOSE_FILE_NAME'],
		    'TXT_FEED_NUMBER_ARTICLES'         => $_ARRAYLANG['TXT_FEED_NUMBER_ARTICLES'],
		    'TXT_FEED_CACHE_TIME'              => $_ARRAYLANG['TXT_FEED_CACHE_TIME'],
		    'TXT_FEED_SHOW_LOGO'               => $_ARRAYLANG['TXT_FEED_SHOW_LOGO'],
		    'TXT_FEED_NO'                      => $_ARRAYLANG['TXT_FEED_NO'],
		    'TXT_FEED_YES'                     => $_ARRAYLANG['TXT_FEED_YES'],
		    'TXT_FEED_STATUS'                  => $_ARRAYLANG['TXT_FEED_STATUS'],
		    'TXT_FEED_INACTIVE'                => $_ARRAYLANG['TXT_FEED_INACTIVE'],
		    'TXT_FEED_ACTIVE'                  => $_ARRAYLANG['TXT_FEED_ACTIVE'],
		    'TXT_FEED_RESET'                   => $_ARRAYLANG['TXT_FEED_RESET'],
		    'TXT_FEED_SAVE'                    => $_ARRAYLANG['TXT_FEED_SAVE'],
		    'TXT_FEED_FORMCHECK_ERROR_INTERN'  => $_ARRAYLANG['TXT_FEED_FORMCHECK_ERROR_INTERN'],
		    'TXT_FEED_FORMCHECK_CATEGORY'      => $_ARRAYLANG['TXT_FEED_FORMCHECK_CATEGORY'],
		    'TXT_FEED_FORMCHECK_NAME'          => $_ARRAYLANG['TXT_FEED_FORMCHECK_NAME'],
		    'TXT_FEED_FORMCHECK_LINK_FILE'     => $_ARRAYLANG['TXT_FEED_FORMCHECK_LINK_FILE'],
		    'TXT_FEED_FORMCHECK_ARTICLES'      => $_ARRAYLANG['TXT_FEED_FORMCHECK_ARTICLES'],
		    'TXT_FEED_FORMCHECK_CACHE'         => $_ARRAYLANG['TXT_FEED_FORMCHECK_CACHE'],
		    'TXT_FEED_FORMCHECK_IMAGE'         => $_ARRAYLANG['TXT_FEED_FORMCHECK_IMAGE'],
		    'TXT_FEED_FORMCHECK_STATUS'        => $_ARRAYLANG['TXT_FEED_FORMCHECK_STATUS']
		));
	}
		
	//---------------------------------------------------------------------------------------------
	
	function showEditSetNew($id, $subid, $name, $link, $filename, $articles, $cache, $time, $image, $status)
	{
		global $objDatabase, $_ARRAYLANG;
		
		//delete old #01
		$query = "SELECT link,
		                   filename
		              FROM ".DBPREFIX."module_feed_news
		             WHERE id = '".$id."'";
		$objResult = $objDatabase->Execute($query);
		
		$old_link     = $objResult->fields['link'];
		$old_filename = $objResult->fields['filename'];
		
		//new
		$query = "SELECT id
		              FROM ".DBPREFIX."module_feed_news
		      WHERE BINARY name = '".$name."'
		               AND id <> '".$id."'";
		$objResult = $objDatabase->Execute($query);
		
		if ($objResult->RecordCount() == 0) {
			if ($link != '') {
				$filename = "feed_".$time."_".basename($link);				
				if (!copy($link, $this->feedpath.$filename)) {
				    $_SESSION['statusMessage'] = $_ARRAYLANG['TXT_FEED_MESSAGE_ERROR_NEWS_FEED'];
				    $this->goToReplace('&act=edit&id='.$id);
				    die;
		        }
		        
				//rss class
				$rss =& new XML_RSS($this->feedpath.$filename);
				$rss->parse();
				$content = '';
				
				foreach($rss->getStructure() as $array) {
					$content .= $array;
				}
				if ($content == '') {
					unlink($this->feedpath.$filename);
					$_SESSION['statusMessage'] = $_ARRAYLANG['TXT_FEED_MESSAGE_ERROR_NEWS_FEED'];
				    $this->goToReplace('&act=edit&id='.$id);
				    die;
				}
			}
			
			$query = "UPDATE ".DBPREFIX."module_feed_news
						   SET subid = '".$subid."',
			                   name = '".$name."',
			                   link = '".$link."',
			                   filename = '".$filename."',
			                   articles = '".$articles."',
			                   cache = '".$cache."',
			                   time = '".$time."',
			                   image = '".$image."',
			                   status = '".$status."'
			             WHERE id = '".$id."'";
			$objResult = $objDatabase->Execute($query);
		} else {
			$_SESSION['statusMessage'] = $_ARRAYLANG['TXT_FEED_MESSAGE_ERROR_EXISTING_NEWS'];
			$this->goToReplace('&act=edit&id='.$id);
			die;
		}
		
		//delete old #02		
		if ($old_link != '') {
			if (!unlink($this->feedpath.$old_filename)) {
		        $_SESSION['statusMessage'] = $_ARRAYLANG['TXT_FEED_MESSAGE_ERROR_DELETE'];
			    $this->goToReplace('');
			    die;
		    }
		}
	}

///////////////////////////////////////////////////////////////////////////////////////////////////
	
    // CASE CATEGORY
    /////////////////////////
    
	function showCategory()
	{
		error_reporting(E_ALL);
		global $objDatabase, $_ARRAYLANG, $_LANGID, $_CONFIG;
		
		unset($_SESSION['feedCategorySort']);
		
		//new
		if (isset($_GET['new']) and $_GET['new'] == 1) {
			if (isset($_POST['form_name']) and isset($_POST['form_lang']) and isset($_POST['form_status'])) {
				$name   = CONTREXX_ESCAPE_GPC ? strip_tags($_POST['form_name']) : addslashes(strip_tags($_POST['form_name']));
				$lang   = intval($_POST['form_lang']);
				$status = intval($_POST['form_status']);
				$time   = time();
				$this->showCategoryNew($name, $lang, $status, $time);
				$_SESSION['statusMessage'] = $_ARRAYLANG['TXT_FEED_MESSAGE_SUCCESSFULL_CAT'];
			} else {
				$_SESSION['statusMessage'] = $_ARRAYLANG['TXT_FEED_MESSAGE_ERROR_FILL_IN_ALL'];
			}
			
			$this->goToReplace('&act=category');
			die;
		}
		if (isset($_GET['chg']) and $_GET['chg'] == 1 and isset($_POST['form_selected']) and is_array($_POST['form_selected'])) {
			//discharge
			if ($_POST['form_discharge'] != '') {
				$ids = $_POST['form_selected'];
				$this->showCategoryDischarge($ids);
				$_SESSION['statusMessage'] = $_ARRAYLANG['TXT_FEED_MESSAGE_SUCCESSFUL_DISCHARGE'];
				$this->goToReplace('&act=category');
				die;
			}
			//delete
			if ($_POST['form_delete'] != '') {
				$ids = $_POST['form_selected'];
				$this->showCategoryDelete($ids);
				$this->goToReplace('&act=category');
				die;
			}
			//changestatus
			if ($_POST['form_activate'] != '' or $_POST['form_deactivate'] != '') {
				$ids = $_POST['form_selected'];
				if ($_POST['form_activate'] != '') {
					$this->showCategoryChange($ids, 1);
				}
				if ($_POST['form_deactivate'] != '') {
					$this->showCategoryChange($ids, 0);
				}
				$_SESSION['statusMessage'] = $_ARRAYLANG['TXT_FEED_MESSAGE_STATUS'];
				$this->goToReplace('&act=category');
				die;
			}
		}
		//sort
		if (isset($_GET['chg']) and $_GET['chg'] == 1 and $_POST['form_sort'] != '') {
			$ids = $_POST['form_id'];
			$pos = $_POST['form_pos'];
			$this->showCategoryChangePos($ids, $pos);
			$_SESSION['statusMessage'] = $_ARRAYLANG['TXT_FEED_MESSAGE_SORT'];
			$this->goToReplace('&act=category');
			die;
		}
			
		//lang
		$query = "SELECT id,
	                       name 
	                  FROM ".DBPREFIX."languages
	                 WHERE id<>0 
	                 ORDER BY id";
		$objResult = $objDatabase->Execute($query);
		
		while(!$objResult->EOF) {
			$selected = '';
			if ($_LANGID == $objResult->fields['id']) {
			    $selected = ' selected';
			}
			
			$this->_objTpl->setVariable(array(
		    	'FEED_LANG_ID'       => $objResult->fields['id'],
		    	'FEED_LANG_SELECTED' => $selected,
		    	'FEED_LANG_NAME'     => $objResult->fields['name']
			));
			$this->_objTpl->parse('feed_lang');
			$objResult->MoveNext();
		}
		
		//table
		$query = "SELECT id,
		                 name,
		                 status,
		                 FROM_UNIXTIME(time, '%H:%i - %d.%m.%Y') AS time,
		                 lang,
		                 pos
		            FROM ".DBPREFIX."module_feed_category
		        ORDER BY pos";
		
		//paging
		$objResult = $objDatabase->Execute($query);
		$count = $objResult->RecordCount();
		
		if (isset($_GET['pos'])) {
		    $pos = intval($_GET['pos']);
		} else {
			$pos = 0;
		}
		
		if (!is_numeric($pos)) {
		    $pos = 0;
	    }
	    if ($count > intval($_CONFIG['corePagingLimit'])) {	    
			$paging = getPaging($count, $pos, "&cmd=feed&act=category", "<b>".$_ARRAYLANG['TXT_FEED_ENTRIES']."</b>", true);
	    } else {
	    	$paging = '';
	    }
		
		$pagingLimit = intval($_CONFIG['corePagingLimit']);
	    
	    $objResult = $objDatabase->SelectLimit($query, $pagingLimit, $pos);
		
		//
		
		$total_records = $objResult->RecordCount();
		$this->_objTpl->setVariable(array(
		    'TOTAL_RECORDS'       => $total_records
		));
		
		$i = 0;
		while(!$objResult->EOF) {
			($i % 2)                ? $class  = 'row1'  : $class  = 'row2';
			($objResult->fields['status'] == 1) ? $status = 'green' : $status = 'red';
			
			//records
			$query = "SELECT subid
			               FROM ".DBPREFIX."module_feed_news
			              WHERE subid = '".$objResult->fields['id']."'";
			$objResult2 = $objDatabase->Execute($query);
  			$records = $objResult->RecordCount();
  			
  			//lang
  			$query = "SELECT name 
	                       FROM ".DBPREFIX."languages
	                      WHERE id = '".$objResult->fields['lang']."'";
  			$objResult2 = $objDatabase->Execute($query);
			
			//parser
			$this->_objTpl->setVariable(array(
			    'FEED_CLASS'           => $class,
			    'FEED_POS'             => $objResult->fields['pos'],
			    'FEED_STATUS'          => $status,
				'FEED_ID'              => $objResult->fields['id'],
				'FEED_NAME'            => $objResult->fields['name'],
				'FEED_LANG'            => $objResult2->fields['name'],
				'FEED_TIME'            => $objResult->fields['time'],
				'FEED_RECORDS'         => $records,
			    'TXT_FEED_EDIT'        => $_ARRAYLANG['TXT_FEED_EDIT']
			));
			$this->_objTpl->parse('feed_table_row');
			$objResult->MoveNext();
			$i++;
		}
		
		//make visible
		if ($i > 0) {
			$this->_objTpl->setVariable(array(
			    'FEED_RECORDS_HIDDEN'        => '&nbsp;',
			    'TXT_FEED_MARK_ALL'          => $_ARRAYLANG['TXT_FEED_MARK_ALL'],
			    'TXT_FEED_REMOVE_CHOICE'     => $_ARRAYLANG['TXT_FEED_REMOVE_CHOICE'],
			    'TXT_FEED_SELECT_OPERATION'  => $_ARRAYLANG['TXT_FEED_SELECT_OPERATION'],
			    'TXT_FEED_SAVE_SORTING'      => $_ARRAYLANG['TXT_FEED_SAVE_SORTING'],
			    'TXT_FEED_ACTIVATE_CAT'      => $_ARRAYLANG['TXT_FEED_ACTIVATE_CAT'],
			    'TXT_FEED_DEACTIVATE_CAT'    => $_ARRAYLANG['TXT_FEED_DEACTIVATE_CAT'],
			    'TXT_FEED_DELETE_RECORDS'    => $_ARRAYLANG['TXT_FEED_DELETE_RECORDS'],
			    'TXT_FEED_DELETE_CAT'        => $_ARRAYLANG['TXT_FEED_DELETE_CAT']
			));			
			$this->_objTpl->parse('feed_table_hidden');		
		}
		
		//
		
		$this->_objTpl->setVariable(array(
		    'FEED_CATEGORY_PAGING'       => $paging
		));
		
		//parse $_ARRAYLANG
		$this->_objTpl->setVariable(array(
		    'TXT_FEED_INSERT_CATEGORY'        => $_ARRAYLANG['TXT_FEED_INSERT_CATEGORY'],
		    'TXT_FEED_NAME'                   => $_ARRAYLANG['TXT_FEED_NAME'],
		    'TXT_FEED_LANGUAGE'               => $_ARRAYLANG['TXT_FEED_LANGUAGE'],
		    'TXT_FEED_STATUS'                 => $_ARRAYLANG['TXT_FEED_STATUS'],
		    'TXT_FEED_INACTIVE'               => $_ARRAYLANG['TXT_FEED_INACTIVE'],
		    'TXT_FEED_ACTIVE'                 => $_ARRAYLANG['TXT_FEED_ACTIVE'],
		    'TXT_FEED_SAVE'                   => $_ARRAYLANG['TXT_FEED_SAVE'],
		    'TXT_FEED_SORTING'                => $_ARRAYLANG['TXT_FEED_SORTING'],
		    'TXT_FEED_STATUS'                 => $_ARRAYLANG['TXT_FEED_STATUS'],
		    'TXT_FEED_ID'                     => $_ARRAYLANG['TXT_FEED_ID'],
		    'TXT_FEED_CAT_NAME'               => $_ARRAYLANG['TXT_FEED_CAT_NAME'],
		    'TXT_FEED_LANGUAGE'               => $_ARRAYLANG['TXT_FEED_LANGUAGE'],
		    'TXT_FEED_RECORDS'                => $_ARRAYLANG['TXT_FEED_RECORDS'],
		    'TXT_FEED_BUILT_EDITED'           => $_ARRAYLANG['TXT_FEED_BUILT_EDITED'],
		    'TXT_FEED_FORMCHECK_NAME'         => $_ARRAYLANG['TXT_FEED_FORMCHECK_NAME'],
		    'TXT_FEED_FORMCHECK_LANGUAGE'     => $_ARRAYLANG['TXT_FEED_FORMCHECK_LANGUAGE'],
		    'TXT_FEED_FORMCHECK_STATUS'       => $_ARRAYLANG['TXT_FEED_FORMCHECK_STATUS'],
		    'TXT_FEED_DELETE_RECORDS_CONFIRM' => $_ARRAYLANG['TXT_FEED_DELETE_RECORDS_CONFIRM'],
		    'TXT_FEED_DELETE_CONFIRM'         => $_ARRAYLANG['TXT_FEED_DELETE_CONFIRM'],
		    'TXT_FEED_NO_SELECT_OPERATION'    => $_ARRAYLANG['TXT_FEED_NO_SELECT_OPERATION']
		));
	}
	
	//---------------------------------------------------------------------------------------------
	
	//FUNC new
	function showCategoryNew($name, $lang, $status, $time)
	{
		global $objDatabase, $_ARRAYLANG, $_LANGID;
		
		$query = "SELECT id
		              FROM ".DBPREFIX."module_feed_category
		      WHERE BINARY name = '".$name."'";
		$objResult = $objDatabase->Execute($query);
		
		if ($objResult->RecordCount() == 0) {
		
			$query = "INSERT INTO ".DBPREFIX."module_feed_category
						        SET name = '".$name."',
			                        lang = '".$lang."',
			                        status = '".$status."',
			                        time = '".$time."'";
			$objResult = $objDatabase->Execute($query);
		} else {
			$_SESSION['statusMessage'] = $_ARRAYLANG['TXT_FEED_MESSAGE_ERROR_EXISTING_CAT'];
			$this->goToReplace('&act=category');
			die;
		}
	}
	
	//FUNC discharge
	function showCategoryDischarge($ids)
	{		
		global $objDatabase, $_ARRAYLANG, $_LANGID;
		
		foreach($ids as $id) {
			$query = "SELECT id,
			                   link,
			                   filename
			              FROM ".DBPREFIX."module_feed_news
			             WHERE subid = '".intval($id)."'";
			$objResult = $objDatabase->Execute($query);
			
			while(!$objResult->EOF) {
				$link     = $objResult->fields['link'];
				$filename = $objResult->fields['filename'];
				
				if ($link != '') {
				    if (!unlink($this->feedpath.$filename)) {
				        $_SESSION['statusMessage'] = $_ARRAYLANG['TXT_FEED_MESSAGE_ERROR_DELETE'];
					    $this->goToReplace('&act=category');
					    die;
				    }
				}
				$objResult->MoveNext();
			}
			$query = "DELETE FROM ".DBPREFIX."module_feed_news
			                  WHERE subid = '".intval($id)."'";
			$objDatabase->Execute($query);
		}
		
		$query = "SELECT id
		              FROM ".DBPREFIX."module_feed_news";
		$objResult = $objDatabase->Execute($query);
		
		if ($objResult->RecordCount() == 0) {
			$query = "DELETE FROM ".DBPREFIX."module_feed_news";
		}
	}
	
	//FUNC delete
	function showCategoryDelete($ids)
	{
		global $objDatabase, $_ARRAYLANG, $_LANGID;
		$y = 0;
		
		foreach($ids as $id) {
			$query = "SELECT subid
			          FROM ".DBPREFIX."module_feed_news
		             WHERE subid = '".intval($id)."'";
			$objResult = $objDatabase->Execute($query);
		
			if ($objResult->RecordCount() > 0) {
				$y++;
			} else {
				$query = "DELETE FROM ".DBPREFIX."module_feed_category
		                  WHERE id = '".intval($id)."'";
				$objDatabase->Execute($query);
		    }
		}
		
		$query = "SELECT id
		              FROM ".DBPREFIX."module_feed_category";
		$objResult = $objDatabase->Execute($query);
		
		if ($objResult->RecordCount() == 0) {
			$objDatabase->Execute("DELETE FROM ".DBPREFIX."module_feed_category");
		}
		
		if ($y == 0) {
		    $_SESSION['statusMessage'] = $_ARRAYLANG['TXT_FEED_MESSAGE_SUCCESSFUL_DELETE'];
		} else {
			$_SESSION['statusMessage'] = $_ARRAYLANG['TXT_FEED_MESSAGE_UNSUCCESSFUL_DELETE'];
		}
	}
	
	//FUNC changestatus
	function showCategoryChange($ids, $to)
	{
		global $objDatabase, $_ARRAYLANG, $_LANGID;
		
		foreach($ids as $id) {
			$query = "UPDATE ".DBPREFIX."module_feed_category
			               SET status = '".intval($to)."'
			             WHERE id = '".intval($id)."'";
			$objDatabase->Execute($query);
		}
	}
	
	//FUNC sort
	function showCategoryChangePos($ids, $pos)
	{
		global $objDatabase, $_ARRAYLANG, $_LANGID;
		
		for($x = 0; $x < count($ids); $x++) {
			$query = "UPDATE ".DBPREFIX."module_feed_category
			               SET pos = '".intval($pos[$x])."'
			             WHERE id = '".intval($ids[$x])."'";
			$objDatabase->Execute($query);
		}
	}
	
///////////////////////////////////////////////////////////////////////////////////////////////////
	
	// CASE CAT_EDIT
	/////////////////////////
	
	function showCatEdit()
	{
		global $objDatabase, $_ARRAYLANG, $_LANGID;
		
		// check
		if (!isset($_GET['set'])) {
			if (!isset($_GET['id']) or $_GET['id'] == '') {
				$this->goToReplace('&act=category');
				die;
			}
		}
		
		//set
		if (isset($_GET['set']) and $_GET['set'] == 1) {
			if ($_POST['form_id'] != '' and $_POST['form_name'] != '' and $_POST['form_status'] != '' and $_POST['form_lang'] != '') {
				$id       = intval($_POST['form_id']);
				$name     = CONTREXX_ESCAPE_GPC ? strip_tags($_POST['form_name']) : addslashes(strip_tags($_POST['form_name']));
				$status   = intval($_POST['form_status']);
				$time     = time();
				$lang     = intval($_POST['form_lang']);
				
				$this->showCatEditSet($id, $name, $status, $time, $lang);
				$_SESSION['statusMessage'] = $_ARRAYLANG['TXT_FEED_MESSAGE_SUCCESSFUL_EDIT_CAT'];
				$this->goToReplace('&act=category');
				die;
			} else {
				$_SESSION['statusMessage'] = $_ARRAYLANG['TXT_FEED_MESSAGE_ERROR_FILL_IN_ALL'];
				$this->goToReplace('&act=catedit&id='.$_POST['form_id']);
				die;
			}
		}
		
		//-----------------------------------------------------------------------------------------
		
		$query = "SELECT id,
		                   name,
		                   status,
		                   lang
		              FROM ".DBPREFIX."module_feed_category
		             WHERE id = '".intval($_GET['id'])."'";
		$objResult = $objDatabase->Execute($query);
		
		$id        = $objResult->fields['id'];
		$name      = $objResult->fields['name'];
		$status    = $objResult->fields['status'];
		$lang      = $objResult->fields['lang'];
		
		if ($status == 0) {
			$status0 = ' selected';
			$status1 = '';
		} else {
			$status0 = '';
			$status1 = ' selected';
		}
		
		$this->_objTpl->setVariable(array(
		    'FEED_ID'           => $id,
		    'FEED_NAME'         => $name,
		    'FEED_LINK'         => $link,
		    'FEED_STATUS0'      => $status0,
		    'FEED_STATUS1'      => $status1
		));
		
		//lang
		$query = "SELECT id,
	                       name 
	                  FROM ".DBPREFIX."languages
	                 WHERE id<>0 
	                 ORDER BY id";
		$objResult = $objDatabase->Execute($query);
		
		while (!$objResult->EOF) {
			$selected = '';
			if ($lang == $objResult->fields['id']) {
			    $selected = ' selected';
			}
			
			$this->_objTpl->setVariable(array(
		    	'FEED_LANG_ID'       => $objResult->fields['id'],
		    	'FEED_LANG_SELECTED' => $selected,
		    	'FEED_LANG_NAME'     => $objResult->fields['name']
			));
			$this->_objTpl->parse('feed_lang');
			$objResult->MoveNext();
		}
		
		//parse $_ARRAYLANG
		$this->_objTpl->setVariable(array(
		    'TXT_FEED_EDIT_CAT'           => $_ARRAYLANG['TXT_FEED_EDIT_CAT'],
		    'TXT_FEED_NAME'               => $_ARRAYLANG['TXT_FEED_NAME'],
		    'TXT_FEED_LANGUAGE'           => $_ARRAYLANG['TXT_FEED_LANGUAGE'],
		    'TXT_FEED_STATUS'             => $_ARRAYLANG['TXT_FEED_STATUS'],
		    'TXT_FEED_INACTIVE'           => $_ARRAYLANG['TXT_FEED_INACTIVE'],
		    'TXT_FEED_ACTIVE'             => $_ARRAYLANG['TXT_FEED_ACTIVE'],
		    'TXT_FEED_RESET'              => $_ARRAYLANG['TXT_FEED_RESET'],
		    'TXT_FEED_SAVE'               => $_ARRAYLANG['TXT_FEED_SAVE'],
		    'TXT_FEED_FORMCHECK_NAME'     => $_ARRAYLANG['TXT_FEED_FORMCHECK_NAME'],
		    'TXT_FEED_FORMCHECK_LANGUAGE' => $_ARRAYLANG['TXT_FEED_FORMCHECK_LANGUAGE'],
		    'TXT_FEED_FORMCHECK_STATUS'   => $_ARRAYLANG['TXT_FEED_FORMCHECK_STATUS']
		));
	}

	
	
	
	
	//---------------------------------------------------------------------------------------------
		
	function showCatEditSet($id, $name, $status, $time, $lang)
	{
		global $objDatabase, $_ARRAYLANG, $_LANGID;
		
		$query = "SELECT id
		              FROM ".DBPREFIX."module_feed_category
		      WHERE BINARY name = '".$name."'
		               AND id <> '".$id."'";
		$objResult = $objDatabase->Execute($query);
		
		if ($objResult->RecordCount() == 0) {			
			$query = "UPDATE ".DBPREFIX."module_feed_category
						   SET name = '".$name."',
			                   status ='".$status."',
			                   time = '".$time."',
			                   lang = '".$lang."'
			             WHERE id = '".$id."'";
			$objResult = $objDatabase->Execute($query);
		} else {
			$_SESSION['statusMessage'] = $_ARRAYLANG['TXT_FEED_MESSAGE_ERROR_EXISTING_CAT'];
			$this->goToReplace('&act=catedit&id='.$id);
			die;
		}
	}
	
///////////////////////////////////////////////////////////////////////////////////////////////////

	function goToReplace($add)
	{
		header("Location: index.php?cmd=feed".$add);
	}

}

?>