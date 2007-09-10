<?php
/**
 * Class podcast manager
 *
 * podcast manager class
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author		Comvation Development Team <info@comvation.com>
 * @access		public
 * @version		1.0.1
 * @package     contrexx
 * @subpackage  module_podcast
 * @todo        Edit PHP DocBlocks!
 */
require_once ASCMS_MODULE_PATH.'/podcast/lib/podcastLib.class.php';
require_once ASCMS_CORE_MODULE_PATH.'/cache/admin.class.php';

class podcastManager extends podcastLib
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
	* Error status message
	*
	* @access private
	* @var string
	*/
	var $_strErrMessage = '';

   /**
	* Ok status message
	*
	* @access private
	* @var string
	*/
	var $_strOkMessage = '';

	/**
	 * The default thumbnail picture
	 *
	 * @access private
	 * @var string path to the default thumbnail, relative to the backend admin path
	 */
	var $_defaultThumbnail = 'images/content/podcast/no_picture.gif';

	/**
	 * allowed characters in a YouTube Video ID (regex class)
	 *
	 * @access private
	 * @var string allowed characters in a YouTube Video ID
	 */
	var $_youTubeAllowedCharacters = '[a-zA-Z0-9_-]';

	/**
	 * length of a YouTube Video ID used in the ID regex
	 *
	 * @access private
	 * @var string length of a YouTube Video ID
	 */
	var $_youTubeIdLenght = '11';


	/**
	 * Youtube ID Regex
	 *
	 * @access private
	 * @var string
	 */
	var $_youTubeIdRegex;

	/**
	 * YouTube default flashobject width
	 *
	 * @access private
	 * @var int
	 */
	var $_youTubeDefaultWidth = 425;

	/**
	 * YouTube default flashobject height
	 *
	 * @access private
	 * @var int
	 */
	var $_youTubeDefaultHeight = 350;

	/**
	* Constructor
	*/
	function podcastManager()
	{
		$this->__construct();
	}

	/**
	* PHP5 constructor
	*
	* @global object $objTemplate
	* @global array $_ARRAYLANG
	*/
	function __construct()
	{
		global $objTemplate, $_ARRAYLANG;

		$this->_objTpl = &new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/podcast/template');
		$this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);

    	$objTemplate->setVariable("CONTENT_NAVIGATION", "<a href='index.php?cmd=podcast'>".$_ARRAYLANG['TXT_PODCAST_MEDIA']."</a>
    													<a href='index.php?cmd=podcast&amp;act=selectSource'>".$_ARRAYLANG['TXT_PODCAST_ADD_MEDIUM']."</a>
    													<a href='index.php?cmd=podcast&amp;act=categories'>".$_ARRAYLANG['TXT_PODCAST_CATEGORIES']."</a>
    													<a href='index.php?cmd=podcast&amp;act=templates'>".$_ARRAYLANG['TXT_PODCAST_TEMPLATES']."</a>
    													<a href='index.php?cmd=podcast&amp;act=settings'>".$_ARRAYLANG['TXT_PODCAST_SETTINGS']."</a>"
    													);
    	$this->_youTubeIdRegex = "#.*[\?&]v=(".$this->_youTubeAllowedCharacters."{".$this->_youTubeIdLenght."}).*#";
    	parent::__construct();
	}

	/**
	* Set the backend page
	*
	* @access public
	* @global object $objTemplate
	* @global array $_ARRAYLANG
	*/
	function getPage()
	{
		global $objTemplate, $_ARRAYLANG, $objDatabase;


		if (!isset($_REQUEST['act'])) {
			$_REQUEST['act'] = '';
		}

		switch ($_REQUEST['act']) {
			case 'showMedium':
				$this->_showMedium();
				break;

			case 'selectSource':
				$this->_selectMediumSource();
				break;

			case 'modifyMedium':
				$this->_modifyMedium();
				break;

			case 'deleteMedium':
				$this->_deleteMediumProcess();
				break;

			case 'getHtml':
				$this->_getHtml();
				break;

			case 'categories':
				$this->_categories();
				break;

			case 'modifyCategory':
				$this->_modifyCategory();
				break;

			case 'deleteCategory':
				$this->_deleteCategoryProcess();
				break;

			case 'templates':
				$this->_templates();
				break;

			case 'modifyTemplate':
				$this->_modifyTemplate();
				break;

			case 'deleteTemplate':
				$this->_deleteTemplateProcess();
				break;

			case 'settings':
				$this->_settings();
				break;

			case 'media':
			default:
				$this->_media();
				break;
		}


		$objTemplate->setVariable(array(
			'CONTENT_TITLE'				=> $this->_pageTitle,
			'CONTENT_OK_MESSAGE'		=> $this->_strOkMessage,
			'CONTENT_STATUS_MESSAGE'	=> $this->_strErrMessage,
			'ADMIN_CONTENT'				=> $this->_objTpl->get()
		));
	}

	function _media()
	{
		global $_ARRAYLANG, $_CONFIG;

		$this->_objTpl->loadTemplatefile('module_podcast_media.html');
		$this->_pageTitle = $_ARRAYLANG['TXT_PODCAST_MEDIA'];

		$this->_objTpl->setVariable(array(
			'TXT_PODCAST_STATUS'					=> $_ARRAYLANG['TXT_PODCAST_STATUS'],
			'TXT_PODCAST_TITLE'						=> $_ARRAYLANG['TXT_PODCAST_TITLE'],
			'TXT_PODCAST_AUTHOR'					=> $_ARRAYLANG['TXT_PODCAST_AUTHOR'],
			'TXT_PODCAST_DATE'						=> $_ARRAYLANG['TXT_PODCAST_DATE'],
			'TXT_PODCAST_TEMPLATE'					=> $_ARRAYLANG['TXT_PODCAST_TEMPLATE'],
			'TXT_PODCAST_FUNCTIONS'					=> $_ARRAYLANG['TXT_PODCAST_FUNCTIONS'],
			'TXT_PODCAST_ADD_MEDIUM'				=> $_ARRAYLANG['TXT_PODCAST_ADD_MEDIUM'],
			'TXT_PODCAST_CONFIRM_DELETE_MEDIUM_MSG'	=> $_ARRAYLANG['TXT_PODCAST_CONFIRM_DELETE_MEDIUM_MSG'],
			'TXT_PODCAST_OPERATION_IRREVERSIBLE'	=> $_ARRAYLANG['TXT_PODCAST_OPERATION_IRREVERSIBLE'],
			'TXT_PODCAST_CHECK_ALL'					=> $_ARRAYLANG['TXT_PODCAST_CHECK_ALL'],
			'TXT_PODCAST_UNCHECK_ALL'				=> $_ARRAYLANG['TXT_PODCAST_UNCHECK_ALL'],
			'TXT_PODCAST_WITH_SELECTED'				=> $_ARRAYLANG['TXT_PODCAST_WITH_SELECTED'],
			'TXT_PODCAST_DELETE'					=> $_ARRAYLANG['TXT_PODCAST_DELETE'],
			'TXT_PODCAST_CONFIRM_DELETE_MEDIA_MSG'	=> $_ARRAYLANG['TXT_PODCAST_CONFIRM_DELETE_MEDIA_MSG'],
			'TXT_PODCAST_SHOW_MEDIUM'				=> $_ARRAYLANG['TXT_PODCAST_SHOW_MEDIUM']
		));

		$this->_objTpl->setGlobalVariable(array(
			'TXT_PODCAST_SHOW_HTML_SOURCE_CODE'	=> $_ARRAYLANG['TXT_PODCAST_SHOW_HTML_SOURCE_CODE'],
			'TXT_PODCAST_MODIFY_MEDIUM'			=> $_ARRAYLANG['TXT_PODCAST_MODIFY_MEDIUM'],
			'TXT_PODCAST_DELETE_MEDIUM'			=> $_ARRAYLANG['TXT_PODCAST_DELETE_MEDIUM']
		));

		$rowNr = 0;
		$paging = "";
		$categoryId = false;
		$arrCategory = false;

		if (isset($_GET['categoryId']) && ($arrCategory = &$this->_getCategory(intval($_GET['categoryId']))) !== false) {
			$categoryId = intval($_GET['categoryId']);
			$this->_objTpl->setVariable('PODCAST_MEDIA_TITLE_TXT', sprintf($_ARRAYLANG['TXT_PODCAST_MEDIA_OF_CATEGORY'], $arrCategory['title']));
		} else {
			$this->_objTpl->setVariable('PODCAST_MEDIA_TITLE_TXT',$_ARRAYLANG['TXT_PODCAST_MEDIA']);
		}

		$arrMedia = &$this->_getMedia($categoryId);
		$mediaCount = &$this->_getMediaCount($categoryId);

		if ($mediaCount > $_CONFIG['corePagingLimit']) {
			$pos = isset($_GET['pos']) ? intval($_GET['pos']) : 0;
			$paging = getPaging($mediaCount, $pos, '&amp;cmd=podcast&amp;categoryId='.$categoryId, $_ARRAYLANG['TXT_PODCAST_MEDIA']);
			$this->_objTpl->setVariable('PODCAST_PAGING', $paging."<br /><br />\n");
		}

		if ($mediaCount > 0) {
			$arrTemplates = &$this->_getTemplates();

			foreach ($arrMedia as $mediumId => $arrMedium) {
				$this->_objTpl->setVariable(array(
					'PODCAST_ROW_CLASS'			=> $rowNr % 2 == 1 ? 'row1' : 'row2',
					'PODCAST_MEDIUM_ID'			=> $mediumId,
					'PODCAST_MEDIUM_STATUS_IMG'	=> $arrMedium['status'] == 1 ? 'led_green.gif' : 'led_red.gif',
					'PODCAST_MEDIUM_STATUS_TXT'	=> $arrMedium['status'] == 1 ? $_ARRAYLANG['TXT_PODCAST_ACTIVE'] : $_ARRAYLANG['TXT_PODCAST_INACTIVE'],
					'PODCAST_MEDIUM_DATE'		=> date(ASCMS_DATE_FORMAT, $arrMedium['date_added']),
					'PODCAST_MEDIUM_TITLE'		=> htmlentities($arrMedium['title'], ENT_QUOTES, CONTREXX_CHARSET),
					'PODCAST_MEDIUM_AUTHOR'		=> !empty($arrMedium['author']) ? htmlentities($arrMedium['author'], ENT_QUOTES, CONTREXX_CHARSET) : '-',
					'PODCAST_MEDIUM_TEMPLATE'	=> htmlentities($arrTemplates[$arrMedium['template_id']]['description'], ENT_QUOTES, CONTREXX_CHARSET)
				));
				$this->_objTpl->parse('podcast_media_list');
				$rowNr++;
			}

			$this->_objTpl->hideBlock('podcast_media_no_data');
			$this->_objTpl->touchBlock('podcast_media_data');
			$this->_objTpl->touchBlock('podcast_media_multi_select_action');
		} else {
			if ($arrCategory) {
				$this->_objTpl->setVariable('PODCAST_EMPTY_CATEGORY_MSG_TXT', sprintf($_ARRAYLANG['TXT_PODCAST_EMPTY_CATEGORY_MSG'], $arrCategory['title']));
			} else {
				$this->_objTpl->setVariable('PODCAST_EMPTY_CATEGORY_MSG_TXT', 'Die Medien Bibliothek ist leer!');
			}
			$this->_objTpl->touchBlock('podcast_media_no_data');
			$this->_objTpl->hideBlock('podcast_media_data');
			$this->_objTpl->hideBlock('podcast_media_multi_select_action');
		}

		if ($mediaCount > 0 || $categoryId) {
			$this->_objTpl->setVariable('PODCAST_CATEGORY_MENU', $this->_getCategoriesMenu($categoryId, 'onchange="window.location.href=\'index.php?cmd=podcast&amp;categoryId=\'+this.value"'));
			$this->_objTpl->touchBlock('podcast_category_menu');
		} else {
			$this->_objTpl->hideBlock('podcast_category_menu');
		}
	}

	function _showMedium()
	{
		global $_ARRAYLANG;

		$mediumId = isset($_GET['id']) ? intval($_GET['id']) : 0;
		if (($arrMedium = &$this->_getMedium($mediumId)) === false) {
			return $this->_media();
		}

		$this->_objTpl->loadTemplatefile('module_podcast_show_medium.html');
		$this->_pageTitle = $_ARRAYLANG['TXT_PODCAST_SHOW_MEDIUM'];

		$this->_objTpl->setVariable(array(
			'TXT_PODCAST_MEDIUM'	=> $_ARRAYLANG['TXT_PODCAST_MEDIUM'],
			'TXT_PODCAST_BACK'		=> $_ARRAYLANG['TXT_PODCAST_BACK']
		));

		$arrTemplate = &$this->_getTemplate($arrMedium['template_id']);
		$this->_objTpl->setVariable(array(
			'PODCAST_MEDIUM_TITLE'			=> $arrMedium['title'],
			'PODCAST_MEDIUM_INCLUDE_CODE'	=> $this->_getHtmlTag($arrMedium, $arrTemplate['template'])
		));
	}

	function _selectMediumSource()
	{
		global $_ARRAYLANG;
		$youtubeIdError = false;
		if (isset($_POST['podcast_select_source']) && in_array($_POST['podcast_medium_source_type'], array('local', 'remote', 'youtube'))) {
			$sourceType = $_POST['podcast_medium_source_type'];
			if ($sourceType == 'local') {
				$source = isset($_POST['podcast_medium_local_source']) ? $_POST['podcast_medium_local_source'] : '';
			} elseif($sourceType == 'remote') {
				$source = isset($_POST['podcast_medium_remote_source']) ? $_POST['podcast_medium_remote_source'] : '';
			} else{
				$source = isset($_POST['podcast_medium_youtube_source']) ? $_POST['podcast_medium_youtube_source'] : '';
				preg_match("#".$this->_youTubeAllowedCharacters."{".$this->_youTubeIdLenght."}#", $_POST['youtubeID'], $match);
				if(strlen($match[0]) != $this->_youTubeIdLenght){
					$youtubeIdError = true;
				}
			}

			if (!empty($source) && !$youtubeIdError) {
				return $this->_modifyMedium();
			} elseif ($youtubeIdError){
				$this->_strErrMessage = $_ARRAYLANG['TXT_PODCAST_YOUTUBE_SPECIFY_ID'];
			} else {
				$this->_strErrMessage = $_ARRAYLANG['TXT_PODCAST_SELECT_SOURCE_ERR_MSG'];
			}
		} else {
			$sourceType = 'local';
			$source = '';
		}

		$this->_objTpl->loadTemplatefile('module_podcast_select_medium_source.html');
		$this->_pageTitle = $_ARRAYLANG['TXT_PODCAST_ADD_MEDIUM'];

		$this->_objTpl->setVariable(array(
			'TXT_PODCAST_SELECT_SOURCE'		=> $_ARRAYLANG['TXT_PODCAST_SELECT_SOURCE'],
			'TXT_PODCAST_SELECT_SOURCE_TXT'	=> $_ARRAYLANG['TXT_PODCAST_SELECT_SOURCE_TXT'],
			'TXT_PODCAST_LOCAL'				=> $_ARRAYLANG['TXT_PODCAST_LOCAL'],
			'TXT_PODCAST_ADD_MEDIUM'		=> $_ARRAYLANG['TXT_PODCAST_ADD_MEDIUM'],
			'TXT_PODCAST_STEP'				=> $_ARRAYLANG['TXT_PODCAST_STEP'],
			'TXT_PODCAST_REMOTE'			=> $_ARRAYLANG['TXT_PODCAST_REMOTE'],
			'TXT_PODCAST_YOUTUBE'			=> $_ARRAYLANG['TXT_PODCAST_YOUTUBE'],
			'TXT_PODCAST_BROWSE'			=> $_ARRAYLANG['TXT_PODCAST_BROWSE'],
			'TXT_PODCAST_NEXT'				=> $_ARRAYLANG['TXT_PODCAST_NEXT'],
			'TXT_PODCAST_YOUTUBE_ID_VALID'	=> $_ARRAYLANG['TXT_PODCAST_YOUTUBE_ID_VALID'],
			'TXT_PODCAST_YOUTUBE_ID_INVALID'=> $_ARRAYLANG['TXT_PODCAST_YOUTUBE_ID_INVALID'],
			'TXT_PODCAST_YOUTUBE_SPECIFY_ID'=> $_ARRAYLANG['TXT_PODCAST_YOUTUBE_SPECIFY_ID']
		));

		$this->_objTpl->setVariable(array(
			'PODCAST_SELECT_LOCAL_MEDIUM'		=> $sourceType == 'local' ? 'checked="checked"' : '',
			'PODCAST_SELECT_LOCAL_MEDIUM_BOX'	=> $sourceType == 'local' ? 'block' : 'none',
			'PODCAST_SELECT_REMOTE_MEDIUM'		=> $sourceType == 'remote' ? 'checked="checked"' : '',
			'PODCAST_SELECT_REMOTE_MEDIUM_BOX'	=> $sourceType == 'remote' ? 'block' : 'none',
			'PODCAST_SELECT_YOUTUBE_MEDIUM'		=> $sourceType == 'youtube' ? 'checked="checked"' : '',
			'PODCAST_SELECT_YOUTUBE_MEDIUM_BOX'	=> $sourceType == 'youtube' ? 'block' : 'none',
			'PODCAST_LOCAL_SOURCE'				=> $sourceType == 'local' ? $source : '',
			'PODCAST_REMOTE_SOURCE'				=> $sourceType == 'remote' ? $source : 'http://',
			'PODCAST_YOUTUBE_SOURCE'			=> $sourceType == 'youtube' ? $source : '',
			'PODCAST_YOUTUBE_ID_CHARACTERS'		=> $this->_youTubeAllowedCharacters,
			'PODCAST_YOUTUBE_ID_LENGTH'			=> $this->_youTubeIdLenght
		));
	}

	function _modifyMedium()
	{
		global $_ARRAYLANG, $_CONFIG, $objLanguage;

		$mediumId = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
		$mediumTitle = '';
		$mediumYoutubeID = '';
		$mediumAuthor = '';
		$mediumDescription = '';
		$mediumSource = '';
		$mediumThumbnail = '';
		$mediumTemplate = '';
		$mediumWidth = 0;
		$mediumHeight = 0;
		$mediumPlaylenght = 0;
		$mediumSize = 0;
		$mediumStatus = 1;
		$mediumCategories = array();
		$saveStatus = true;

		$this->_objTpl->loadTemplatefile('module_podcast_modify_medium.html');
		$this->_pageTitle = $mediumId > 0 ? $_ARRAYLANG['TXT_PODCAST_MODIFY_MEDIUM'] : $_ARRAYLANG['TXT_PODCAST_ADD_MEDIUM'];

		$this->_objTpl->setVariable(array(
			'TXT_PODCAST_TITLE'				=> $_ARRAYLANG['TXT_PODCAST_TITLE'],
			'TXT_PODCAST_DESCRIPTION'		=> $_ARRAYLANG['TXT_PODCAST_DESCRIPTION'],
			'TXT_PODCAST_SOURCE'			=> $_ARRAYLANG['TXT_PODCAST_SOURCE'],
			'TXT_PODCAST_TEMPLATE'			=> $_ARRAYLANG['TXT_PODCAST_TEMPLATE'],
			'TXT_PODCAST_DIMENSIONS'		=> $_ARRAYLANG['TXT_PODCAST_DIMENSIONS'],
			'TXT_PODCAST_PIXEL_WIDTH'		=> $_ARRAYLANG['TXT_PODCAST_PIXEL_WIDTH'],
			'TXT_PODCAST_PIXEL_HEIGHT'		=> $_ARRAYLANG['TXT_PODCAST_PIXEL_HEIGHT'],
			'TXT_PODCAST_CATEGORIES'		=> $_ARRAYLANG['TXT_PODCAST_CATEGORIES'],
			'TXT_PODCAST_STATUS'			=> $_ARRAYLANG['TXT_PODCAST_STATUS'],
			'TXT_PODCAST_ACTIVE'			=> $_ARRAYLANG['TXT_PODCAST_ACTIVE'],
			'TXT_PODCAST_SAVE'				=> $_ARRAYLANG['TXT_PODCAST_SAVE'],
			'TXT_PODCAST_PLAYLENGHT'		=> $_ARRAYLANG['TXT_PODCAST_PLAYLENGHT'],
			'TXT_PODCAST_PLAYLENGHT_FORMAT'	=> $_ARRAYLANG['TXT_PODCAST_PLAYLENGHT_FORMAT'],
			'TXT_PODCAST_FILESIZE'			=> $_ARRAYLANG['TXT_PODCAST_FILESIZE'],
			'TXT_PODCAST_BYTES'				=> $_ARRAYLANG['TXT_PODCAST_BYTES'],
			'TXT_PODCAST_AUTHOR'			=> $_ARRAYLANG['TXT_PODCAST_AUTHOR'],
			'TXT_PODCAST_EDIT_OR_ADD_IMAGE'	=> $_ARRAYLANG['TXT_PODCAST_EDIT_OR_ADD_IMAGE'],
			'TXT_PODCAST_THUMBNAIL'			=> $_ARRAYLANG['TXT_PODCAST_THUMBNAIL'],
			'TXT_PODCAST_SHOW_FILE'			=> $_ARRAYLANG['TXT_PODCAST_SHOW_FILE']
		));

		if (isset($_POST['podcast_medium_save'])) {
			if (isset($_POST['podcast_medium_title'])) {
				$mediumTitle = trim($_POST['podcast_medium_title']);
			}
			if (isset($_POST['podcast_medium_author'])) {
				$mediumAuthor = trim($_POST['podcast_medium_author']);
			}
			if (isset($_POST['podcast_medium_description'])) {
				$mediumDescription = trim($_POST['podcast_medium_description']);
			}
			if (isset($_POST['podcast_medium_template'])) {
				$mediumTemplate = intval($_POST['podcast_medium_template']);
			}

			$mediumWidth = isset($_POST['podcast_medium_width']) ? intval($_POST['podcast_medium_width']) : 0;
			$mediumHeight = isset($_POST['podcast_medium_height']) ? intval($_POST['podcast_medium_height']) : 0;
			$mediumSize = isset($_POST['podcast_medium_filesize']) ? intval($_POST['podcast_medium_filesize']) : 0;

			if (!empty($_POST['podcast_medium_playlenght'])) {
				if (preg_match('/^(([0-9]*):)?(([0-9]*):)?([0-9]*)$/', $_POST['podcast_medium_playlenght'], $arrPlaylenght)) {
					$minutes = empty($arrPlaylenght[3]) ? $arrPlaylenght[2] : $arrPlaylenght[4];
					$hours = empty($arrPlaylenght[3]) ? $arrPlaylenght[4] : $arrPlaylenght[2];
					$mediumPlaylenght = $hours * 3600 + $minutes * 60 + $arrPlaylenght[5];
				}
			}

			if (isset($_POST['podcast_medium_source'])) {
				$mediumSource = trim($_POST['podcast_medium_source']);
			}

			if (isset($_POST['podcast_medium_thumbnail'])) {
				$mediumThumbnail = trim($_POST['podcast_medium_thumbnail']);
			}

			if (isset($_POST['podcast_youtubeID'])) {
				$mediumYoutubeID = trim($_POST['podcast_youtubeID']);
				$mediumSize = 0;
				$mediumTemplate = $this->_getYoutubeTemplate();
			}
			$mediumStatus = isset($_POST['podcast_medium_status']) ? intval($_POST['podcast_medium_status']) : 0;

			if (isset($_POST['podcast_medium_associated_category'])) {
				foreach ($_POST['podcast_medium_associated_category'] as $categoryId => $status) {
					if (intval($status) == 1) {
						array_push($mediumCategories, intval($categoryId));
					}
				}
			}

			if (empty($mediumTitle)) {
				$saveStatus = false;
				$this->_strErrMessage .= $_ARRAYLANG['TXT_PODCAST_EMPTY_MEDIUM_TITLE_MSG']."<br />\n";
			} /*elseif (!$this->_isUniqueMediumTitle($mediumTitle, $mediumId)) {
				$saveStatus = false;
				$this->_strErrMessage .= $_ARRAYLANG['TXT_PODCAST_DUPLICATE_MEDIUM_TITLE_MSG']."<br />\n";
			}*/

			if (empty($mediumTemplate)) {
				$saveStatus = false;
				$this->_strErrMessage .= $_ARRAYLANG['TXT_PODCAST_EMPTY_MEDIUM_TEMPLATE_MSG']."<br />\n";
			}

			if ($saveStatus) {
				if ($mediumId > 0) {
					if ($this->_updateMedium($mediumId, $mediumTitle, $mediumYoutubeID, $mediumAuthor, $mediumDescription, $mediumThumbnail, $mediumTemplate, $mediumWidth, $mediumHeight, $mediumPlaylenght, $mediumSize, $mediumCategories, $mediumStatus)) {
						$this->_strOkMessage = $_ARRAYLANG['TXT_PODCAST_MEDIUM_ADDED_SUCCESSFULL'];
						$objCache = &new Cache();
						$objCache->deleteAllFiles();
						$this->_createRSS();
						return $this->_media();
					} else {
						$this->_strErrMessage = $_ARRAYLANG['TXT_PODCAST_MEDIUM_ADDED_FAILED'];
					}
				} else {
					if ($this->_addMedium($mediumTitle, $mediumYoutubeID, $mediumAuthor, $mediumDescription, $mediumSource, $mediumThumbnail, $mediumTemplate, $mediumWidth, $mediumHeight, $mediumPlaylenght, $mediumSize, $mediumCategories, $mediumStatus)) {
						$this->_strOkMessage = $_ARRAYLANG['TXT_PODCAST_MEDIUM_UPDATED_SUCCESSFULL'];
						$objCache = &new Cache();
						$objCache->deleteAllFiles();
						$this->_createRSS();
						return $this->_media();
					} else {
						$this->_strErrMessage = $_ARRAYLANG['TXT_PODCAST_MEDIUM_UPDATED_FAILED'];
					}
				}
			}
		} elseif ($mediumId > 0 && ($arrMedium = &$this->_getMedium($mediumId)) !== false) {
			$mediumTitle = $arrMedium['title'];
			$mediumAuthor = $arrMedium['author'];
			$mediumDescription = $arrMedium['description'];
			$mediumYoutubeID = $arrMedium['youtube_id'];
			$mediumSource = $arrMedium['source'];
			$mediumThumbnail = $arrMedium['thumbnail'];
			$mediumTemplate = $arrMedium['template_id'];
			$mediumWidth = $arrMedium['width'];
			$mediumHeight = $arrMedium['height'];
			$mediumStatus = $arrMedium['status'];
			$mediumCategories = $arrMedium['category'];
			$mediumPlaylenght = $arrMedium['playlenght'];
			$mediumSize = $arrMedium['size'];
		} elseif ($mediumId == 0) {
			$mediumSource = '';
			if (isset($_POST['podcast_medium_source_type']) && in_array($_POST['podcast_medium_source_type'], array('local', 'remote', 'youtube'))) {
				if ($_POST['podcast_medium_source_type'] == 'local') {
					if (isset($_POST['podcast_medium_local_source'])) {
						if (($hasOffsetPath = strpos($_POST['podcast_medium_local_source'], ASCMS_PATH_OFFSET)) === 0) {
							$mediumSource =  ASCMS_PROTOCOL.'://%domain%%offset%'.substr($_POST['podcast_medium_local_source'], strlen(ASCMS_PATH_OFFSET));
						} else {
							$mediumSource =  ASCMS_PROTOCOL.'://%domain%%offset%'.$_POST['podcast_medium_local_source'];
						}
					}
				} elseif ($_POST['podcast_medium_source_type'] == 'youtube') {
				    $mediumYoutubeID = contrexx_addslashes(trim($_POST['youtubeID']));
    				$mediumSource = 'http://youtube.com/v/'.$mediumYoutubeID;
				} elseif (isset($_POST['podcast_medium_remote_source'])) {
					$mediumSource = $_POST['podcast_medium_remote_source'];
				}
			}

			if (empty($mediumSource)) {
				return $this->_selectMediumSource();
			}

			if(!empty($mediumYoutubeID)){
				$mediumTitle = $this->_getYoutubeTitle($mediumYoutubeID);
				$mediumThumbnail = ASCMS_PATH_OFFSET.$this->_saveYoutubeThumbnail($mediumYoutubeID);
				$mediumTemplate = &$this->_getYoutubeTemplate();
				$mediumWidth = $this->_youTubeDefaultWidth;
				$mediumSize = 0;
				$mediumHeight = $this->_youTubeDefaultHeight;
			}else{
				$mediumTitle = ($lastSlash = strrpos($mediumSource, '/')) !== false ? substr($mediumSource, $lastSlash+1) : $mediumSource;
				$mediumTemplate = &$this->_getSuitableTemplate($mediumSource);
				$dimensions = isset($_POST['podcast_medium_local_source']) ? @getimagesize(ASCMS_PATH.$_POST['podcast_medium_local_source']) : false;
				if ($dimensions) {
					$mediumWidth = $dimensions[0];
					$mediumHeight = $dimensions[1];
				} else {
					$mediumWidth = $this->_arrSettings['default_width'];
					$mediumHeight = $this->_arrSettings['default_height'];
				}
				$mediumSize = isset($_POST['podcast_medium_local_source']) ? filesize(ASCMS_PATH.$_POST['podcast_medium_local_source']) : 0;
				$mediumSource = htmlentities(str_replace(array('%domain%', '%offset%'), array($_CONFIG['domainUrl'], ASCMS_PATH_OFFSET), $mediumSource), ENT_QUOTES, CONTREXX_CHARSET);
			}
		}

		$this->_objTpl->setVariable(array(
			'PODCAST_MODIFY_TITLE'				=> $mediumId > 0 ? $_ARRAYLANG['TXT_PODCAST_MODIFY_MEDIUM'] : $_ARRAYLANG['TXT_PODCAST_ADD_MEDIUM'].' ('.$_ARRAYLANG['TXT_PODCAST_STEP'].' 2: '.$_ARRAYLANG['TXT_PODCAST_CONFIG_MEDIUM'].')',
			'PODCAST_MEDIUM_ID'					=> $mediumId,
			'PODCAST_MEDIUM_TITLE'				=> htmlentities($mediumTitle, ENT_QUOTES, CONTREXX_CHARSET),
			'PODCAST_MEDIUM_AUTHOR'				=> htmlentities($mediumAuthor, ENT_QUOTES, CONTREXX_CHARSET),
			'PODCAST_MEDIUM_DESCRIPTION'		=> htmlentities($mediumDescription, ENT_QUOTES, CONTREXX_CHARSET),
			'PODCAST_MEDIUM_SOURCE'				=> $mediumSource,
			'PODCAST_MEDIUM_SOURCE_URL'			=> htmlentities($mediumSource, ENT_QUOTES, CONTREXX_CHARSET),
			'PODCAST_MEDIUM_TEMPLATE_MENU'		=> $this->_getTemplateMenu($mediumTemplate, 'name="podcast_medium_template" style="width:450px;"'),
			'PODCAST_MEDIUM_WIDTH'				=> $mediumWidth,
			'PODCAST_MEDIUM_HEIGHT'				=> $mediumHeight,
			'PODCAST_MEDIUM_PLAYLENGHT'			=> $this->_getShortPlaylenghtFormatOfTimestamp($mediumPlaylenght),
			'PODCAST_MEDIUM_FILESIZE'			=> $mediumSize,
			'PODCAST_MEDIUM_THUMBNAIL_SRC'		=> !empty($mediumThumbnail) ? $mediumThumbnail : $this->_noThumbnail,
			'PODCAST_MEDIUM_STATUS'				=> $mediumStatus == 1 ? 'checked="checked"' : '',
			'PODCAST_MEDIUM_YOUTUBE_DISABLED'	=> !empty($mediumYoutubeID) ? 'disabled="disabled"' : '',
			'PODCAST_MEDIUM_YOUTUBE_ID'			=> !empty($mediumYoutubeID) ? $mediumYoutubeID : ''
		));

		$arrCategories = &$this->_getCategories();
		$categoryNr = 0;
		$arrLanguages = &$objLanguage->getLanguageArray();

		foreach ($arrCategories as $categoryId => $arrCategory) {
			$column = $categoryNr % 3;
			$arrCatLangIds = &$this->_getLangIdsOfCategory($categoryId);
			array_walk($arrCatLangIds, create_function('&$cat, $k, $arrLanguages', '$cat = $arrLanguages[$cat]["lang"];'), $arrLanguages);
			$arrCategory['title'] .= ' ('.implode(', ', $arrCatLangIds).')';

			$this->_objTpl->setVariable(array(
				'PODCAST_CATEGORY_ID'					=> $categoryId,
				'PODCAST_CATEGORY_ASSOCIATED' 			=> in_array($categoryId, $mediumCategories) ? 'checked="checked"' : '',
				'PODCAST_SHOW_MEDIA_OF_CATEGORY_TXT'	=> sprintf($_ARRAYLANG['TXT_PODCAST_SHOW_MEDIA_OF_CATEGORY'], $arrCategory['title']),
				'PODCAST_CATEGORY_NAME'					=> $arrCategory['title']
			));
			$this->_objTpl->parse('podcast_medium_associated_category_'.$column);

			$categoryNr++;
		}
	}

	function _saveYoutubeThumbnail($youTubeID)
	{
		error_reporting(E_ALL);ini_set('display_errors',1);
		$httpRequest = '';
		$response = '';
		$mediumTitle = '';
		$s = @fsockopen('img.youtube.com', 80, $errno, $errmsg, 5);
		if(is_resource($s)){
			$httpRequest = 	"GET /vi/%s/default.jpg HTTP/1.1\r\n".
							"Host: img.youtube.com\r\n".
							"User-Agent: ".$_SERVER['HTTP_USER_AGENT']."\r\n".
							"Accept: ".$_SERVER['HTTP_ACCEPT']."\r\n".
							"Accept-Language: ".$_SERVER['HTTP_ACCEPT_LANGUAGE']."\r\n".
							"Accept-Encoding: \r\n".
							"Accept-Charset: ".$_SERVER['HTTP_ACCEPT_CHARSET'].";q=0.7,*\r\n".
							"Cache-Control: max-age=0\r\n".
							"Connection: close\r\n\r\n";
			fwrite($s, sprintf($httpRequest, $youTubeID));
			fflush($s);

			$response = fread($s, 512);
			preg_match('#Content-Length: ([0-9]+)#', $response, $match);
			$contentLength = $match[1];
			while(!feof($s)){
				$response .= fread($s, 512);
			}
			@fclose($s);
			$response = substr($response, -$contentLength);
			$mediumThumbnail = '/images/content/podcast/youtube_thumbnails/youtube_'.$youTubeID.'.jpg';
			$hImg = fopen(ASCMS_DOCUMENT_ROOT.$mediumThumbnail, 'w');
			fwrite($hImg, $response, $contentLength);
			fclose($hImg);
		}
		return $mediumThumbnail;
	}

	function _getYoutubeTitle($youTubeID)
	{
		$httpRequest = '';
		$response = '';
		$mediumTitle = '';
		$s = @fsockopen('www.youtube.com', 80, $errno, $errmsg, 5);
		if(is_resource($s)){
			$httpRequest = 	"GET /watch?v=".$youTubeID." HTTP/1.1\r\n".
							"Host: www.youtube.com\r\n".
							"User-Agent: ".$_SERVER['HTTP_USER_AGENT']."\r\n".
							"Accept: ".$_SERVER['HTTP_ACCEPT']."\r\n".
							"Accept-Language: ".$_SERVER['HTTP_ACCEPT_LANGUAGE']."\r\n".
							"Accept-Encoding: \r\n".
							"Accept-Charset: ".$_SERVER['HTTP_ACCEPT_CHARSET'].";q=0.7,*\r\n".
							"Cache-Control: max-age=0\r\n".
							"Connection: close\r\n\r\n";
			fwrite($s, $httpRequest);
			fflush($s);
			while(!feof($s)){
				$response .= fread($s, 512);
			}
			@fclose($s);
			preg_match('#<title>YouTube - ([^<]+)</title>#', $response, $match);
			$mediumTitle = $match[1];
		}
		return $mediumTitle;
	}

	function _deleteMediumProcess()
	{
		global $_ARRAYLANG;

		$arrRemoveMediumIds = array();
		$deleteStatus = true;

		if (isset($_POST['podcast_medium_selected']) && is_array($_POST['podcast_medium_selected'])) {
			foreach ($_POST['podcast_medium_selected'] as $mediumId) {
				array_push($arrRemoveMediumIds, intval($mediumId));
			}
		} elseif (isset($_GET['id'])) {
			array_push($arrRemoveMediumIds, intval($_GET['id']));
		}

		if (count($arrRemoveMediumIds) > 0) {
			foreach ($arrRemoveMediumIds as $mediumId) {
				if (($arrMedium = &$this->_getMedium($mediumId)) !== false) {
					if (!$this->_deleteMedium($mediumId)) {
						$deleteStatus = false;
					}
				}
			}

			if ($deleteStatus) {
				if (count($arrRemoveMediumIds) > 1) {
					$this->_strOkMessage = $_ARRAYLANG['TXT_PODCAST_DELETE_MEDIA_SUCCESSFULL_MSG'];
				} else {
					$this->_strOkMessage = sprintf($_ARRAYLANG['TXT_PODCAST_DELETE_MEDIUM_SUCCESSFULL_MSG'], $arrMedium['title']);
				}

				$objCache = &new Cache();
				$objCache->deleteAllFiles();
				$this->_createRSS();
			} else {
				if (count($arrRemoveMediumIds) > 1) {
					$this->_strErrMessage = $_ARRAYLANG['TXT_PODCAST_DELETE_MEDIA_FAILED_MSG'];
				} else {
					$this->_strErrMessage = sprintf($_ARRAYLANG['TXT_PODCAST_DELETE_MEDIUM_FAILED_MSG'], $arrMedium['title']);
				}
			}
		}

		return $this->_media();
	}

	function _categories()
	{
		global $_ARRAYLANG, $_CONFIG;

		$categoryCount = &$this->_getCategoriesCount();
		if ($categoryCount == 0) {
			return $this->_modifyCategory();
		}

		$rowNr = 0;
		$this->_pageTitle = $_ARRAYLANG['TXT_PODCAST_CATEGORIES'];
		$this->_objTpl->loadTemplatefile('module_podcast_categories.html');

		if ($categoryCount > $_CONFIG['corePagingLimit']) {
			$pos = isset($_GET['pos']) ? intval($_GET['pos']) : 0;
			$paging = getPaging($categoryCount, $pos, '&amp;cmd=podcast&amp;act=categories', $_ARRAYLANG['TXT_PODCAST_CATEGORIES']);
			$this->_objTpl->setVariable('PODCAST_PAGING', $paging."<br /><br />\n");
		}

		$this->_objTpl->setVariable(array(
			'TXT_PODCAST_CATEGORIES'					=> $_ARRAYLANG['TXT_PODCAST_CATEGORIES'],
			'TXT_PODCAST_STATUS'						=> $_ARRAYLANG['TXT_PODCAST_STATUS'],
			'TXT_PODCAST_TITLE'							=> $_ARRAYLANG['TXT_PODCAST_TITLE'],
			'TXT_PODCAST_DESCRIPTION'					=> $_ARRAYLANG['TXT_PODCAST_DESCRIPTION'],
			'TXT_PODCAST_MEDIA_COUNT'					=> $_ARRAYLANG['TXT_PODCAST_MEDIA_COUNT'],
			'TXT_PODCAST_FUNCTIONS'						=> $_ARRAYLANG['TXT_PODCAST_FUNCTIONS'],
			'TXT_PODCAST_ADD_NEW_CATEGORY'				=> $_ARRAYLANG['TXT_PODCAST_ADD_NEW_CATEGORY'],
			'TXT_PODCAST_CONFIRM_DELETE_CATEGORY_MSG'	=> $_ARRAYLANG['TXT_PODCAST_CONFIRM_DELETE_CATEGORY_MSG'],
			'TXT_PODCAST_OPERATION_IRREVERSIBLE'		=> $_ARRAYLANG['TXT_PODCAST_OPERATION_IRREVERSIBLE']
		));

		$this->_objTpl->setGlobalVariable(array(
			'TXT_PODCAST_MODIFY_CATEGORY'	=> $_ARRAYLANG['TXT_PODCAST_MODIFY_CATEGORY'],
			'TXT_PODCAST_DELETE_CATEGORY'	=> $_ARRAYLANG['TXT_PODCAST_DELETE_CATEGORY']
		));

		$arrCategories = &$this->_getCategories(false, true);
		foreach ($arrCategories as $categoryId => $arrCategory) {
			$mediaCount = &$this->_getMediaCount($categoryId);

			$this->_objTpl->setVariable(array(
				'PODCAST_ROW_CLASS'					=> $rowNr % 2 == 1 ? 'row1' : 'row2',
				'PODCAST_CATEGORY_ID'				=> $categoryId,
				'PODCAST_CATEGORY_STATUS_IMG'		=> $arrCategory['status'] == 1 ? 'led_green.gif' : 'led_red.gif',
				'PODCAST_CATEGORY_STATUS_TXT'		=> $arrCategory['status'] == 1 ? $_ARRAYLANG['TXT_PODCAST_ACTIVE'] : $_ARRAYLANG['TXT_PODCAST_INACTIVE'],
				'PODCAST_CATEGORY_TITLE'			=> htmlentities($arrCategory['title'], ENT_QUOTES, CONTREXX_CHARSET),
				'PODCAST_CATEGORY_DESCRIPTION'		=> htmlentities($arrCategory['description'], ENT_QUOTES, CONTREXX_CHARSET),
				'PODCAST_CATEGORY_DESCRIPTION_CUT'	=> htmlentities(strlen($arrCategory['description']) > 50 ? substr($arrCategory['description'],0, 47).'...' : $arrCategory['description'], ENT_QUOTES, CONTREXX_CHARSET),
				'PODCAST_CATEGORY_MEDIA_COUNT'		=> $mediaCount > 0 ? '<a href="index.php?cmd=podcast&amp;categoryId='.$categoryId.'" title="'.sprintf($_ARRAYLANG['TXT_PODCAST_SHOW_MEDIA_OF_CATEGORY'], $arrCategory['title']).'">'.$mediaCount.'</a>' : '-'
			));
			$this->_objTpl->parse('podcast_categories_list');
			$rowNr++;
		}
	}

	function _modifyCategory()
	{
		global $_ARRAYLANG, $objLanguage;

		$categoryId = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
		$categoryTitle = '';
		$categoryDescription = '';
		$categoryAssociatedLangIds = array();
		$categoryStatus = 0;
		$saveStatus = true;

		if (isset($_POST['podcast_category_save'])) {
			if (isset($_POST['podcast_category_title'])) {
				$categoryTitle = trim($_POST['podcast_category_title']);
			}
			if (isset($_POST['podcast_category_description'])) {
				$categoryDescription = trim($_POST['podcast_category_description']);
			}

			if (isset($_POST['podcast_category_associated_language'])) {
				foreach ($_POST['podcast_category_associated_language'] as $langId => $status) {
					if (intval($status) == 1) {
						array_push($categoryAssociatedLangIds, intval($langId));
					}
				}
			}

			$categoryStatus = isset($_POST['podcast_category_status']) ? intval($_POST['podcast_category_status']) : 0;

			if (empty($categoryTitle)) {
				$saveStatus = false;
				$this->_strErrMessage = $_ARRAYLANG['TXT_PODCAST_EMPTY_CATEGORY_TITLE_MSG'];
			} elseif (!$this->_isUniqueCategoryTitle($categoryTitle, $categoryId)) {
				$saveStatus = false;
				$this->_strErrMessage = $_ARRAYLANG['TXT_PODCAST_DUPLICATE_CATEGORY_TITLE_MSG'];
			}

			if ($saveStatus) {
				if ($categoryId > 0) {
					if ($this->_updateCategory($categoryId, $categoryTitle, $categoryDescription, $categoryAssociatedLangIds, $categoryStatus)) {
						$this->_strOkMessage = $_ARRAYLANG['TXT_PODCAST_CATEGORY_UPDATED_SUCCESSFULL'];
						$objCache = &new Cache();
						$objCache->deleteAllFiles();
						$this->_createRSS();
						return $this->_categories();
					} else {
						$this->_strErrMessage = $_ARRAYLANG['TXT_PODCAST_CATEGORY_UPDATED_FAILED'];
					}
				} else {
					if ($this->_addCategory($categoryTitle, $categoryDescription, $categoryAssociatedLangIds, $categoryStatus)) {
						$this->_strOkMessage = $_ARRAYLANG['TXT_PODCAST_CATEGORY_CREATED_SUCCESSFULL'];
						$objCache = &new Cache();
						$objCache->deleteAllFiles();
						$this->_createRSS();
						return $this->_categories();
					} else {
						$this->_strErrMessage = $_ARRAYLANG['TXT_PODCAST_CATEGORY_CREATED_FAILED'];
					}
				}
			}
		} elseif ($categoryId > 0 && ($arrCategory = &$this->_getCategory($categoryId)) !== false) {
			$categoryTitle = &$arrCategory['title'];
			$categoryDescription = &$arrCategory['description'];
			$categoryAssociatedLangIds = &$this->_getLangIdsOfCategory($categoryId);
			$categoryStatus = &$arrCategory['status'];
		}

		$this->_pageTitle = $categoryId > 0 ? $_ARRAYLANG['TXT_PODCAST_MODIFY_CATEGORY'] : $_ARRAYLANG['TXT_PODCAST_ADD_NEW_CATEGORY'];
		$this->_objTpl->loadTemplatefile('module_podcast_modify_category.html');

		$this->_objTpl->setVariable(array(
			'TXT_PODCAST_TITLE'					=> $_ARRAYLANG['TXT_PODCAST_TITLE'],
			'TXT_PODCAST_DESCRIPTION'			=> $_ARRAYLANG['TXT_PODCAST_DESCRIPTION'],
			'TXT_PODCAST_STATUS'				=> $_ARRAYLANG['TXT_PODCAST_STATUS'],
			'TXT_PODCAST_ACTIVE'				=> $_ARRAYLANG['TXT_PODCAST_ACTIVE'],
			'TXT_PODCAST_SAVE'					=> $_ARRAYLANG['TXT_PODCAST_SAVE'],
			'TXT_PODCAST_BACK'					=> $_ARRAYLANG['TXT_PODCAST_BACK'],
			'TXT_PODCAST_FRONTEND_LANGUAGES'	=> $_ARRAYLANG['TXT_PODCAST_FRONTEND_LANGUAGES']
		));

		$this->_objTpl->setVariable(array(
			'PODCAST_CATEGORY_ID'			=> $categoryId,
			'PODCAST_CATEGORY_MODIFY_TITLE'	=> $categoryId > 0 ? $_ARRAYLANG['TXT_PODCAST_MODIFY_CATEGORY'] : $_ARRAYLANG['TXT_PODCAST_ADD_NEW_CATEGORY'],
			'PODCAST_CATEGORY_TITLE'		=> htmlentities($categoryTitle, ENT_QUOTES, CONTREXX_CHARSET),
			'PODCAST_CATEGORY_DESCRIPTION'	=> htmlentities($categoryDescription, ENT_QUOTES, CONTREXX_CHARSET),
			'PODCAST_CATEGORY_STATUS'		=> $categoryStatus == 1 ? 'checked="checked"' : ''
		));

		$arrLanguages = &$objLanguage->getLanguageArray();
		$langNr = 0;

		foreach ($arrLanguages as $langId => $arrLanguage) {
			$column = $langNr % 3;

			$this->_objTpl->setVariable(array(
				'PODCAST_LANG_ID'					=> $langId,
				'PODCAST_LANG_ASSOCIATED' 			=> in_array($langId, $categoryAssociatedLangIds) ? 'checked="checked"' : '',
				'PODCAST_LANG_NAME'					=> $arrLanguage['name'].' ('.$arrLanguage['lang'].')'
			));
			$this->_objTpl->parse('podcast_category_associated_language_'.$column);

			$langNr++;
		}
	}

	function _deleteCategoryProcess()
	{
		global $_ARRAYLANG;

		$categoryId = isset($_GET['id']) ? intval($_GET['id']) : 0;

		if (($arrCategory = &$this->_getCategory($categoryId)) !== false) {
			if ($this->_getMediaCount($categoryId) == 0) {
				if ($this->_deleteCategory($categoryId)) {
					$this->_strOkMessage = sprintf($_ARRAYLANG['TXT_PODCAST_DELETE_CATEGORY_SUCCESSFULL_MSG'], $arrCategory['title']);
					$objCache = &new Cache();
					$objCache->deleteAllFiles();
					$this->_createRSS();
				} else {
					$this->_strErrMessage = sprintf($_ARRAYLANG['TXT_PODCAST_DELETE_CATEGORY_FAILED_MSG'], $arrCategory['title']);
				}
			} else {
				$this->_strErrMessage = sprintf($_ARRAYLANG['TXT_PODCAST_CATEGORY_STILL_IN_USE_MSG'], $arrCategory['title']);
			}
		}

		return $this->_categories();
	}

	function _templates()
	{
		global $_ARRAYLANG, $_CONFIG;

		$this->_objTpl->loadTemplatefile('module_podcast_templates.html');
		$this->_pageTitle = $_ARRAYLANG['TXT_PODCAST_TEMPLATES'];

		$limitPos = isset($_GET['pos']) ? intval($_GET['pos']) : 0;

		if (($templateCount = &$this->_getTemplateCount()) > $_CONFIG['corePagingLimit']) {
			$paging = getPaging($templateCount, $limitPos, '&amp;cmd=podcast&amp;act=templates', $_ARRAYLANG['TXT_PODCAST_TEMPLATES']);

			$this->_objTpl->setVariable('PODCAST_PAGING', $paging."<br /><br />\n");
		}

		$arrTemplates = &$this->_getTemplates(true, $limitPos);
		if (count($arrTemplates) > 0) {
			$this->_objTpl->setVariable(array(
				'TXT_PODCAST_TEMPLATES'					=> $_ARRAYLANG['TXT_PODCAST_TEMPLATES'],
				'TXT_PODCAST_DESCRIPTION'				=> $_ARRAYLANG['TXT_PODCAST_DESCRIPTION'],
				'TXT_PODCAST_FUNCTIONS'					=> $_ARRAYLANG['TXT_PODCAST_FUNCTIONS'],
				'TXT_PODCAST_ADD_NEW_TEMPLATE'			=> $_ARRAYLANG['TXT_PODCAST_ADD_NEW_TEMPLATE'],
				'TXT_PODCAST_CONFIRM_DELETE_TEMPLATE'	=> $_ARRAYLANG['TXT_PODCAST_CONFIRM_DELETE_TEMPLATE'],
				'TXT_PODCAST_OPERATION_IRREVERSIBLE'	=> $_ARRAYLANG['TXT_PODCAST_OPERATION_IRREVERSIBLE']
			));

			$this->_objTpl->setGlobalVariable(array(
				'TXT_PODCAST_MODIFY_TEMPLATE'	=> $_ARRAYLANG['TXT_PODCAST_MODIFY_TEMPLATE'],
				'TXT_PODCAST_DELETE_TEMPLATE'	=> $_ARRAYLANG['TXT_PODCAST_DELETE_TEMPLATE']
			));

			$rowNr = 0;
			foreach ($arrTemplates as $templateId => $arrTemplate) {
				$this->_objTpl->setVariable(array(
					'PODCAST_TEMPLATE_ID'			=> $templateId,
					'PODCAST_TEMPLATE_DESCRIPTION'	=> htmlentities($arrTemplate['description'], ENT_QUOTES, CONTREXX_CHARSET),
					'PODCAST_ROW_CLASS'				=> $rowNr % 2 == 1 ? 'row1' : 'row2'
				));
				$rowNr++;

				$this->_objTpl->parse('podcast_templates');
			}

		} else {
			$this->_modifyTemplate();
		}

	}

	function _modifyTemplate()
	{
		global $_ARRAYLANG;

		$templateId = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
		$description = '';
		$template = '';
		$extensions = '';
		$saveStatus = true;

		if (isset($_POST['podcast_template_save'])) {
			if (isset($_POST['podcast_template_description'])) {
				$description = trim($_POST['podcast_template_description']);
			}

			if (isset($_POST['podcast_template_template'])) {
				$template = $_POST['podcast_template_template'];
			}

			if (isset($_POST['podcast_template_file_extensions'])) {
				$arrCleanedExtensions = array();
				$arrExtensions = explode(',', $_POST['podcast_template_file_extensions']);
				foreach ($arrExtensions as $extension) {
					$extension = trim($extension);
					if (preg_match('/^[a-z0-9_-]*$/i', $extension)) {
						array_push($arrCleanedExtensions, $extension);
					}
				}
				$extensions = implode(', ', $arrCleanedExtensions);
			}

			if (empty($description)) {
				$saveStatus = false;
				$this->_strErrMessage = $_ARRAYLANG['TXT_PODCAST_DEFINE_TEMPLATE_DESCRIPTION'];
			} elseif (!$this->_isUniqueTemplateDescription($templateId, $description)) {
				$saveStatus = false;
				$this->_strErrMessage = sprintf($_ARRAYLANG['TXT_PODCAST_UNIQUE_TEMPLATE_DESCRIPTION_MSG'], $description);
			}

			if ($saveStatus) {
				if ($templateId > 0 ) {
					if ($this->_updateTemplate($templateId, $description, $template, $extensions)) {
						$this->_strOkMessage = sprintf($_ARRAYLANG['TXT_PODCAST_TEMPLATE_UPDATED_SUCCESSFULL'], $description);
						$objCache = &new Cache();
						$objCache->deleteAllFiles();
						$this->_createRSS();
						return $this->_templates();
					} else {
						$this->_strErrMessage = $_ARRAYLANG['TXT_PODCAST_TEMPLATE_UPDATED_FAILED'];
					}
				} else {
					if ($this->_addTemplate($description, $template, $extensions)) {
						$this->_strOkMessage = sprintf($_ARRAYLANG['TXT_PODCAST_TEMPLATE_ADDED_SUCCESSFULL'], $description);
						$objCache = &new Cache();
						$objCache->deleteAllFiles();
						$this->_createRSS();
						return $this->_templates();
					} else {
						$this->_strErrMessage = $_ARRAYLANG['TXT_PODCAST_TEMPLATE_ADDED_FAILED'];
					}
				}
			}
		} elseif ($templateId > 0 && ($arrTemplate = &$this->_getTemplate($templateId)) !== false) {
			$description = $arrTemplate['description'];
			$template = $arrTemplate['template'];
			$extensions = $arrTemplate['extensions'];
		}

		$this->_objTpl->loadTemplatefile('module_podcast_modify_template.html');
		$this->_pageTitle = $templateId > 0 ? $_ARRAYLANG['TXT_PODCAST_MODIFY_TEMPLATE'] : $_ARRAYLANG['TXT_PODCAST_ADD_NEW_TEMPLATE'];

		$this->_objTpl->setVariable(array(
			'TXT_PODCAST_DESCRIPTION'		=> $_ARRAYLANG['TXT_PODCAST_DESCRIPTION'],
			'TXT_PODCAST_TEMPLATE'			=> $_ARRAYLANG['TXT_PODCAST_TEMPLATE'],
			'TXT_PODCAST_FILE_EXTENSIONS'	=> $_ARRAYLANG['TXT_PODCAST_FILE_EXTENSIONS'],
			'TXT_PODCAST_BACK'				=> $_ARRAYLANG['TXT_PODCAST_BACK'],
			'TXT_PODCAST_SAVE'				=> $_ARRAYLANG['TXT_PODCAST_SAVE']
		));

		$this->_objTpl->setVariable(array(
			'PODCAST_TEMPLATE_ID'				=> $templateId,
			'PODCAST_TEMPLATE_DESCRIPTION'		=> htmlentities($description, ENT_QUOTES, CONTREXX_CHARSET),
			'PODCAST_TEMPLATE_TEMPLATE'			=> htmlentities($template, ENT_QUOTES, CONTREXX_CHARSET),
			'PODCAST_TEMPLATE_FILE_EXTENSIONS'	=> $extensions,
			'PODCAST_TEMPLATE_MODIFY_TITLE'		=> $templateId > 0 ? $_ARRAYLANG['TXT_PODCAST_MODIFY_TEMPLATE'] : $_ARRAYLANG['TXT_PODCAST_ADD_NEW_TEMPLATE']
		));
	}

	function _deleteTemplateProcess()
	{
		global $_ARRAYLANG;

		$templateId = isset($_GET['id']) ? intval($_GET['id']) : 0;

		if (($arrTemplate = &$this->_getTemplate($templateId)) !== false) {
			if (!$this->_isTemplateInUse($templateId)) {
				if ($this ->_deleteTemplate($templateId)) {
					$this->_strOkMessage = sprintf($_ARRAYLANG['TXT_PODCAST_TEMPLATE_DELETED_SUCCESSFULL'], $arrTemplate['description']);
					$objCache = &new Cache();
					$objCache->deleteAllFiles();
					$this->_createRSS();
				} else {
					$this->_strErrMessage = sprintf($_ARRAYLANG['TXT_PODCAST_TEMPLATE_DELETED_FAILURE'], $arrTemplate['description']);
				}
			} else {
				$this->_strErrMessage = sprintf($_ARRAYLANG['TXT_PODCAST_TEMPLATE_STILL_IN_USE_MSG'], $arrTemplate['description']);
			}
		}

		$this->_templates();
	}

	function _getHtml()
	{
		global $_ARRAYLANG;

		$mediumId = isset($_GET['id']) ? intval($_GET['id']) : 0;

		if (($arrMedium = &$this->_getMedium($mediumId)) === false) {
			return $this->_media();
		}

		$arrTemplate = &$this->_getTemplate($arrMedium['template_id']);

		$this->_objTpl->loadTemplatefile('module_podcast_medium_source_code.html');
		$this->_pageTitle = $_ARRAYLANG['TXT_PODCAST_SOURCE_CODE'];

		$this->_objTpl->setVariable(array(
			'TXT_PODCAST_BACK'			=> $_ARRAYLANG['TXT_PODCAST_BACK'],
			'TXT_PODCAST_SELECT_ALL'	=> $_ARRAYLANG['TXT_PODCAST_SELECT_ALL']
		));

		$this->_objTpl->setVariable(array(
			'PODCAST_HTML_SOURCE_CODE_OF_MEDIUM_TXT'	=> sprintf($_ARRAYLANG['TXT_PODCAST_SOURCE_CODE_OF_MEDIUM'], $arrMedium['title']),
			'PODCAST_MEDIUM_SOURCE_CODE'				=> $this->_getHtmlTag($arrMedium, $arrTemplate['template'])
		));

	}

	function _settings()
	{
		global $_ARRAYLANG, $_CONFIG, $objLanguage;

		$arrSettingsTabs = array("general", "block");
		$defaultTab = 'general';
		$selectedTab = !empty($_POST['podcast_settings_tab']) ? strtolower($_POST['podcast_settings_tab']) : $defaultTab;

		$this->_objTpl->loadTemplatefile('module_podcast_settings.html');
		$this->_pageTitle = $_ARRAYLANG['TXT_PODCAST_SETTINGS'];

		$this->_objTpl->setVariable(array(
			'TXT_PODCAST_SETTINGS'				=> $_ARRAYLANG['TXT_PODCAST_SETTINGS'],
			'TXT_PODCAST_STANDARD_DIMENSIONS'	=> $_ARRAYLANG['TXT_PODCAST_STANDARD_DIMENSIONS'],
			'TXT_PODCAST_PIXEL_WIDTH'			=> $_ARRAYLANG['TXT_PODCAST_PIXEL_WIDTH'],
			'TXT_PODCAST_PIXEL_HEIGHT'			=> $_ARRAYLANG['TXT_PODCAST_PIXEL_HEIGHT'],
			'TXT_PODCAST_LATEST_MEDIA_COUNT'	=> $_ARRAYLANG['TXT_PODCAST_LATEST_MEDIA_COUNT'],
			'TXT_PODCAST_FEED_TITLE'			=> $_ARRAYLANG['TXT_PODCAST_FEED_TITLE'],
			'TXT_PODCAST_FEED_DESCRIPTION'		=> $_ARRAYLANG['TXT_PODCAST_FEED_DESCRIPTION'],
			'TXT_PODCAST_FEED_IMAGE'			=> $_ARRAYLANG['TXT_PODCAST_FEED_IMAGE'],
			'TXT_PODCAST_BROWSE'				=> $_ARRAYLANG['TXT_PODCAST_BROWSE'],
			'TXT_PODCAST_FEED_LINK'				=> $_ARRAYLANG['TXT_PODCAST_FEED_LINK'],
			'TXT_PODCAST_SAVE'					=> $_ARRAYLANG['TXT_PODCAST_SAVE'],
			'TXT_PODCAST_PLACEHOLDERS'			=> $_ARRAYLANG['TXT_PODCAST_PLACEHOLDERS'],
			'TXT_PODCAST_GENERAL'				=> $_ARRAYLANG['TXT_PODCAST_GENERAL'],
			'TXT_PODCAST_BLOCK_TEMPLATE'		=> $_ARRAYLANG['TXT_PODCAST_BLOCK_TEMPLATE'],
			'TXT_PODCAST_BLOCK_SETTINGS'		=> $_ARRAYLANG['TXT_PODCAST_BLOCK_SETTINGS'],
			'TXT_PODCAST_SHOW_HOME_CONTENT'		=> $_ARRAYLANG['TXT_PODCAST_SHOW_HOME_CONTENT'],
			'TXT_PODCAST_DEACTIVATE'			=> $_ARRAYLANG['TXT_PODCAST_DEACTIVATE'],
			'TXT_PODCAST_ACTIVATE'				=> $_ARRAYLANG['TXT_PODCAST_ACTIVATE'],
			'TXT_PODCAST_HOMECONTENT_USAGE'		=> $_ARRAYLANG['TXT_PODCAST_HOMECONTENT_USAGE'],
			'TXT_PODCAST_HOMECONTENT_USAGE_TEXT'=> $_ARRAYLANG['TXT_PODCAST_HOMECONTENT_USAGE_TEXT'],
			'TXT_PODCAST_CATEGORIES'			=> $_ARRAYLANG['TXT_PODCAST_CATEGORIES'],
			'TXT_PODCAST_THUMB_MAX_SIZE'		=> $_ARRAYLANG['TXT_PODCAST_THUMB_MAX_SIZE'],
			'TXT_PODCAST_THUMB_MAX_SIZE_HOMECONTENT' => $_ARRAYLANG['TXT_PODCAST_THUMB_MAX_SIZE_HOMECONTENT'],
			'TXT_PODCAST_PIXEL'					=> $_ARRAYLANG['TXT_PODCAST_PIXEL'],
			'TXT_PODCAST_PLAY'					=> $_ARRAYLANG['TXT_PODCAST_PLAY'],
			'TXT_PODCAST_MEDIA_DATE'			=> $_ARRAYLANG['TXT_PODCAST_MEDIA_DATE'],
			'TXT_PODCAST_MEDIA_TITLE'			=> $_ARRAYLANG['TXT_PODCAST_MEDIA_TITLE'],
			'TXT_PODCAST_MEDIA_PLAYLENGHT'		=> $_ARRAYLANG['TXT_PODCAST_MEDIA_PLAYLENGHT'],
			'TXT_PODCAST_MEDIA_ID'				=> $_ARRAYLANG['TXT_PODCAST_MEDIA_ID'],
			'TXT_PODCAST_MEDIA_VIEWS_COUNT'		=> $_ARRAYLANG['TXT_PODCAST_MEDIA_VIEWS_COUNT'],
			'TXT_PODCAST_MEDIA_VIEWS'			=> $_ARRAYLANG['TXT_PODCAST_MEDIA_VIEWS'],
			'TXT_PODCAST_MEDIA_AUTHOR'			=> $_ARRAYLANG['TXT_PODCAST_MEDIA_AUTHOR'],
			'TXT_PODCAST_MEDIA_SHORT_PLAYLENGHT'=> $_ARRAYLANG['TXT_PODCAST_MEDIA_SHORT_PLAYLENGHT'],
			'TXT_PODCAST_MEDIA_PLAYLENGHT'		=> $_ARRAYLANG['TXT_PODCAST_MEDIA_PLAYLENGHT'],
			'TXT_PODCAST_MEDIA_URL'				=> $_ARRAYLANG['TXT_PODCAST_MEDIA_URL'],
			'TXT_PODCAST_MEDIA_THUMBNAIL'		=> $_ARRAYLANG['TXT_PODCAST_MEDIA_THUMBNAIL'],
			'TXT_PODCAST_MEDIA_SHORT_DATE'		=> $_ARRAYLANG['TXT_PODCAST_MEDIA_SHORT_DATE'],
			'TXT_PODCAST_MEDIA_DESCRIPTION'		=> $_ARRAYLANG['TXT_PODCAST_MEDIA_DESCRIPTION']
		));

		if (isset($_POST['podcast_save_settings'])) {
			if (!empty($_POST['podcast_settings_default_width'])) {
				$arrNewSettings['default_width'] = intval($_POST['podcast_settings_default_width']);
			}
			if (!empty($_POST['podcast_settings_default_height'])) {
				$arrNewSettings['default_height'] = intval($_POST['podcast_settings_default_height']);
			}

			$arrNewSettings['latest_media_count'] = !empty($_POST['podcast_settings_latest_media_count']) && intval($_POST['podcast_settings_latest_media_count']) > 0 ? intval($_POST['podcast_settings_latest_media_count']) : 1;
			$arrNewSettings['thumb_max_size'] = !empty($_POST['podcast_settings_thumb_max_size']) && intval($_POST['podcast_settings_thumb_max_size']) > 0 ? intval($_POST['podcast_settings_thumb_max_size']) : 50;
			$arrNewSettings['thumb_max_size_homecontent'] = !empty($_POST['podcast_settings_thumb_max_size_homecontent']) && intval($_POST['podcast_settings_thumb_max_size_homecontent']) > 0 ? intval($_POST['podcast_settings_thumb_max_size_homecontent']) : 50;

			$arrNewSettings['feed_title'] = isset($_POST['podcast_settings_feed_title']) ? $_POST['podcast_settings_feed_title'] : '';
			$arrNewSettings['feed_description'] = isset($_POST['podcast_settings_feed_description']) ? $_POST['podcast_settings_feed_description'] : '';
			$arrNewSettings['feed_image'] = isset($_POST['podcast_settings_feed_image']) ? $_POST['podcast_settings_feed_image'] : '';

			if ($this->_updateSettings($arrNewSettings) && $this->_updateHomeContentSettings()) {
				$this->_createRSS();
				$this->_strOkMessage = $_ARRAYLANG['TXT_PODCAST_UPDATE_SETTINGS_SUCCESSFULL'];
			} else {
				$this->_strErrMessage = $_ARRAYLANG['TXT_PODCAST_UPDATE_SETTINGS_FAILED'];
			}
		}

		$this->_objTpl->setVariable(array(
			'PODCAST_SETTINGS_DEFAULT_WIDTH'						=> $this->_arrSettings['default_width'],
			'PODCAST_SETTINGS_DEFAULT_HEIGHT'						=> $this->_arrSettings['default_height'],
			'PODCAST_SETTINGS_LATEST_MEDIA_COUNT'					=> $this->_arrSettings['latest_media_count'],
			'PODCAST_SETTINGS_THUMB_MAX_SIZE'					=> $this->_arrSettings['thumb_max_size'],
			'PODCAST_SETTINGS_THUMB_MAX_SIZE_HOMECONTENT'		=> $this->_arrSettings['thumb_max_size_homecontent'],
			'PODCAST_SHOW_CONTENT_'.$_CONFIG['podcastHomeContent']	=> 'checked="checked"',
			'PODCAST_SETTINGS_FEED_TITLE'							=> $this->_arrSettings['feed_title'],
			'PODCAST_SETTINGS_FEED_DESCRIPTION'						=> $this->_arrSettings['feed_description'],
			'PODCAST_SETTINGS_FEED_IMAGE'							=> $this->_arrSettings['feed_image'],
			'PODCAST_SETTINGS_TAB'									=> $selectedTab,
			'PODCAST_SETTINGS_FEED_URL'								=> ASCMS_PROTOCOL.'://'.$_CONFIG['domainUrl'].ASCMS_FEED_WEB_PATH.'/podcast.xml'
		));

		if(!in_array($selectedTab, $arrSettingsTabs)){
			$selectedTab = $defaultTab;
		}
		foreach ($arrSettingsTabs as $tab) {
			$this->_objTpl->setVariable(array(
				'PODCAST_SETTINGS_'.strtoupper($tab).'_DIV_DISPLAY'	=>	sprintf('style="display: %s;"', ($selectedTab == $tab ? 'block' : 'none')),
				'PODCAST_SETTINGS_'.strtoupper($tab).'_TAB_CLASS'	=>	$selectedTab == $tab ? 'class="active"' : '',
			));
		}
		$mediumCategories = array();

		if (isset($_POST['podcast_save_settings'])) {

			$arrPostCategories = !empty($_POST['podcast_medium_associated_category']) ? $_POST['podcast_medium_associated_category'] : array();
			foreach ($arrPostCategories as $categoryId => $status) {
				if (intval($status) == 1) {
					array_push($mediumCategories, intval($categoryId));
				}
			}
			$this->_setHomecontentCategories($mediumCategories);
		} else {
			$mediumCategories = $this->_getHomecontentCategories();
		}

		$arrCategories = &$this->_getCategories();
		$categoryNr = 0;
		$arrLanguages = &$objLanguage->getLanguageArray();

		foreach ($arrCategories as $categoryId => $arrCategory) {
			$column = $categoryNr % 3;
			$arrCatLangIds = &$this->_getLangIdsOfCategory($categoryId);
			array_walk($arrCatLangIds, create_function('&$cat, $k, $arrLanguages', '$cat = $arrLanguages[$cat]["lang"];'), $arrLanguages);
			$arrCategory['title'] .= ' ('.implode(', ', $arrCatLangIds).')';
			$this->_objTpl->setVariable(array(
				'PODCAST_CATEGORY_ID'					=> $categoryId,
				'PODCAST_CATEGORY_ASSOCIATED' 			=> in_array($categoryId, $mediumCategories) ? 'checked="checked"' : '',
				'PODCAST_SHOW_MEDIA_OF_CATEGORY_TXT'	=> sprintf($_ARRAYLANG['TXT_PODCAST_SHOW_MEDIA_OF_CATEGORY'], $arrCategory['title']),
				'PODCAST_CATEGORY_NAME'					=> $arrCategory['title']
			));
			$this->_objTpl->parse('podcast_medium_associated_category_'.$column);
			$categoryNr++;
		}
	}

	function _updateHomeContentSettings()
	{
		global $objDatabase, $_CONFIG;
		require_once(ASCMS_CORE_PATH.'/settings.class.php');
		$objResult = $objDatabase->Execute("UPDATE ".DBPREFIX."settings SET setvalue='".intval($_POST['setHomeContent'])."' WHERE setname='podcastHomeContent'");
		if($objResult !== false){
			$objSettings = &new settingsManager();
			$objSettings->writeSettingsFile();
			$_CONFIG['podcastHomeContent'] = intval($_POST['setHomeContent']);
			return true;
		}
		return false;
	}

	function _createRSS()
	{
		global $_CONFIG, $objLanguage, $objDatabase;
		$this->_arrSettings = &$this->_getSettings();
		$arrMedia = array();
		$objMedium = $objDatabase->Execute("
			SELECT tblMedium.id,
				   tblMedium.title,
				   tblMedium.author,
				   tblMedium.description,
				   tblMedium.source,
				   tblMedium.size,
				   tblMedium.date_added,
				   tblCategory.id AS categoryId,
				   tblCategory.title AS categoryTitle
			FROM ".DBPREFIX."module_podcast_medium AS tblMedium
			LEFT JOIN ".DBPREFIX."module_podcast_rel_medium_category AS tblRel ON tblRel.medium_id=tblMedium.id
			LEFT JOIN ".DBPREFIX."module_podcast_category AS tblCategory ON tblCategory.id=tblRel.category_id
			WHERE tblMedium.status=1
			ORDER BY tblMedium.date_added DESC");
		if ($objMedium !== false) {
			while (!$objMedium->EOF) {
				if (!isset($arrMedia[$objMedium->fields['id']])) {
					$arrMedia[$objMedium->fields['id']] = array(
						'title'			=> $objMedium->fields['title'],
						'author'		=> $objMedium->fields['author'],
						'description'	=> $objMedium->fields['description'],
						'source'		=> str_replace(array('%domain%', '%offset%'), array($_CONFIG['domainUrl'], ASCMS_PATH_OFFSET), $objMedium->fields['source']),
						'size'			=> $objMedium->fields['size'],
						'date_added'	=> $objMedium->fields['date_added'],
						'categories'	=> array()
					);
				}
				if (!empty($objMedium->fields['id'])) {
					$arrMedia[$objMedium->fields['id']]['categories'][$objMedium->fields['categoryId']] = $objMedium->fields['categoryTitle'];
				}

				$objMedium->MoveNext();
			}
		}

		require_once ASCMS_FRAMEWORK_PATH.'/RSSWriter.class.php';

		$objRSSWriter = new RSSWriter();

		$objRSSWriter->characterEncoding = CONTREXX_CHARSET;
		$objRSSWriter->channelTitle = $this->_arrSettings['feed_title'];
		$objRSSWriter->channelLink = 'http://'.$_CONFIG['domainUrl'].($_SERVER['SERVER_PORT'] == 80 ? "" : ":".intval($_SERVER['SERVER_PORT'])).ASCMS_PATH_OFFSET.'/index.php?section=podcast';
		$objRSSWriter->channelDescription = $this->_arrSettings['feed_description'];
		$objRSSWriter->channelCopyright = 'Copyright '.date('Y').', http://'.$_CONFIG['domainUrl'];

		if (!empty($this->_arrSettings['feed_image'])) {
			$objRSSWriter->channelImageUrl = 'http://'.$_CONFIG['domainUrl'].($_SERVER['SERVER_PORT'] == 80 ? "" : ":".intval($_SERVER['SERVER_PORT'])).$this->_arrSettings['feed_image'];
			$objRSSWriter->channelImageTitle = $objRSSWriter->channelTitle;
			$objRSSWriter->channelImageLink = $objRSSWriter->channelLink;
		}
		$objRSSWriter->channelWebMaster = $_CONFIG['coreAdminEmail'];

		$itemLink = "http://".$_CONFIG['domainUrl'].($_SERVER['SERVER_PORT'] == 80 ? "" : ":".intval($_SERVER['SERVER_PORT'])).ASCMS_PATH_OFFSET."/index.php?section=podcast&amp;id=";
		$categoryLink = "http://".$_CONFIG['domainUrl'].($_SERVER['SERVER_PORT'] == 80 ? "" : ":".intval($_SERVER['SERVER_PORT'])).ASCMS_PATH_OFFSET."/index.php?section=podcast&amp;cid=";

		// create podcast feed
		$objRSSWriter->xmlDocumentPath = ASCMS_FEED_PATH.'/podcast.xml';
		foreach ($arrMedia as $mediumId => $arrMedium) {
			$arrCategories = array();
			foreach ($arrMedium['categories'] as $categoryId => $categoryTitle) {
				array_push($arrCategories, array(
					'domain'	=> htmlspecialchars($categoryLink.$categoryId, ENT_QUOTES, CONTREXX_CHARSET),
					'title'		=> htmlspecialchars($categoryTitle, ENT_QUOTES, CONTREXX_CHARSET)
				));
			}

			$objRSSWriter->addItem(
				htmlspecialchars($arrMedium['title'], ENT_QUOTES, CONTREXX_CHARSET),
				$itemLink.$mediumId,
				htmlspecialchars($arrMedium['description'], ENT_QUOTES, CONTREXX_CHARSET),
				htmlspecialchars($arrMedium['author'], ENT_QUOTES, CONTREXX_CHARSET),
				$arrCategories,
				'',
				array('url' => htmlspecialchars($arrMedium['source'], ENT_QUOTES, CONTREXX_CHARSET), 'length' => !empty($arrMedium['size']) ? $arrMedium['size'] : 'N/A', 'type' => 'application/x-video'),
				'',
				$arrMedium['date_added']
			);
		}
		$status = $objRSSWriter->write();

		if (count($objRSSWriter->arrErrorMsg) > 0) {
			$this->_strErrMessage .= implode('<br />', $objRSSWriter->arrErrorMsg);
		}
		if (count($objRSSWriter->arrWarningMsg) > 0) {
			$this->_strErrMessage .= implode('<br />', $objRSSWriter->arrWarningMsg);
		}
		return $status;
	}
}
?>
