<?php
/**
 * Class podcast
 *
 * podcast class
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author		Comvation Development Team <info@comvation.com>
 * @access		public
 * @version		1.0.0
 * @package     contrexx
 * @subpackage  module_podcast
 * @todo        Edit PHP DocBlocks!
 */
require_once ASCMS_MODULE_PATH.'/podcast/lib/podcastLib.class.php';
$_ARRAYLANG['TXT_PODCAST_PLAY'] = "Abspielen";
$_ARRAYLANG['TXT_PODCAST_MEDIA_VIEWS'] = "Aufrufe";

class podcast extends podcastLib
{
	/**
	* Template object
	*
	* @access private
	* @var object
	*/
	var $_objTpl;

	/**
	* Constructor
	*/
	function podcast($pageContent)
	{
		$this->__construct($pageContent);
	}

	/**
	* PHP5 constructor
	*
	* @global object $objTemplate
	* @global array $_ARRAYLANG
	*/
	function __construct($pageContent)
	{
	    $this->_objTpl = &new HTML_Template_Sigma('.');
		$this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);
		$this->_objTpl->setTemplate($pageContent);
		parent::__construct();
	}

	/**
	* Get content page
	*
	* @access public
	*/
	function getPage($blockFirst = false)
	{
		global $_ARRAYLANG, $_CONFIG, $_LANGID;

		$categoryId = isset($_REQUEST['cid']) ? (intval($_REQUEST['cid']) == 0 ? false : intval($_REQUEST['cid'])) : false;
		$mediumId = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
		if($mediumId > 0){
			$this->_updateViews($mediumId);
		}

		$this->_objTpl->setGlobalVariable(array(
			'TXT_PODCAST_PLAY' 		  => $_ARRAYLANG['TXT_PODCAST_PLAY'],
			'TXT_PODCAST_MEDIA_VIEWS' => $_ARRAYLANG['TXT_PODCAST_MEDIA_VIEWS'],
		));

		$maxSize = $this->_arrSettings['thumb_max_size'];
		$tmpOnload = ($blockFirst) ? 'try{tmp();}catch(e){}' : '';

		$embedCode = <<< EOF
<script type="text/javascript">
//<![CDATA[
	var thumbSizeMax = $maxSize;
	var previewSizeMax = 180;

	tmp = window.onload;
	if(tmp == null){
		tmp = function(){};
	}
	window.onload = function(){
		try{
			document.getElementById("podcast_container").innerHTML = '%s';
		}catch(e){}
		setSize(document.getElementById("podcast_preview"), previewSizeMax);
		mThumbnails = document.getElementsByName("podcast_thumbnails");
		for(i=0;i<mThumbnails.length;i++){
			setSize(mThumbnails[i], thumbSizeMax);
		}
		$tmpOnload
	}

//]]>
</script>
EOF;

		if (($arrMedium = &$this->_getMedium($mediumId, true)) !== false) {
			if ($this->_objTpl->blockExists('podcast_medium')) {
				$arrTemplate = &$this->_getTemplate($arrMedium['template_id']);

				$mediumCode = sprintf($embedCode, addcslashes($this->_getHtmlTag($arrMedium, $arrTemplate['template']), "\r\n'"));
				$this->_objTpl->setVariable(array(
					'PODCAST_MEDIUM_ID'				=> $mediumId,
					'PODCAST_MEDIUM_CATEGORY_ID'	=> $categoryId,
					'PODCAST_MEDIUM_TITLE'			=> htmlentities($arrMedium['title'], ENT_QUOTES, CONTREXX_CHARSET),
					'PODCAST_MEDIUM_AUTHOR'			=> empty($arrMedium['author']) ? '-' : htmlentities($arrMedium['author'], ENT_QUOTES, CONTREXX_CHARSET),
					'PODCAST_MEDIUM_DESCRIPTION'	=> htmlentities($arrMedium['description'], ENT_QUOTES, CONTREXX_CHARSET),
					'PODCAST_MEDIUM_CODE'			=> $mediumCode,
					'PODCAST_MEDIUM_DATE'			=> date(ASCMS_DATE_FORMAT, $arrMedium['date_added']),
					'PODCAST_MEDIUM_SHORT_DATE'		=> date(ASCMS_DATE_SHORT_FORMAT, $arrMedium['date_added']),
					'PODCAST_MEDIUM_THUMBNAIL'		=> htmlentities($arrMedium['thumbnail'], ENT_QUOTES, CONTREXX_CHARSET),
					'PODCAST_MEDIUM_URL'			=> htmlentities($arrMedium['source'], ENT_QUOTES, CONTREXX_CHARSET),
					'PODCAST_MEDIUM_PLAYLENGHT'		=> $this->_getPlaylenghtFormatOfTimestamp($arrMedium['playlenght']),
					'PODCAST_MEDIUM_VIEWS'			=> $this->_getViews($mediumId),
					'PODCAST_MEDIUM_FILESIZE'		=> $this->_formatFileSize($arrMedium['size'])
				));

				$this->_objTpl->parse('podcast_medium');
			}
			if ($this->_objTpl->blockExists('podcast_no_medium')) {
				$this->_objTpl->hideBlock('podcast_no_medium');
			}
		} else {
			$podcastJavascript = sprintf($embedCode, '');
			if ($this->_objTpl->blockExists('podcast_no_medium')) {
				$this->_objTpl->touchBlock('podcast_no_medium');
			}
			if ($this->_objTpl->blockExists('podcast_medium')) {
				$this->_objTpl->hideBlock('podcast_medium');
			}
		}

		$menu = $this->_getCategoriesMenu($categoryId, 'id="podcast_category_menu"', true, true);
		if ($menu !== false) {
			$this->_objTpl->setVariable('PODCAST_CATEGORY_MENU', $menu.' <input type="button" onclick="window.location.href=\'index.php?section=podcast&amp;cid=\'+document.getElementById(\'podcast_category_menu\').value" value="'.$_ARRAYLANG['TXT_PODCAST_SHOW'].'" />');
		}
		if(intval($categoryId) == 0){
			$categories = array_keys($this->_getCategories(true, false, $_LANGID));
		}else{
			$categories = $categoryId;
		}
		if ($this->_objTpl->blockExists('podcast_media')) {
			$arrMedia = &$this->_getMedia($categories, true);
			if (count($arrMedia) > 0) {
				foreach ($arrMedia as $mediumId => $arrMedium) {
					$this->_objTpl->setVariable(array(
						'PODCAST_MEDIA_ID'					=> $mediumId,
						'PODCAST_MEDIA_CATEGORY_ID'			=> $categoryId,
						'PODCAST_MEDIA_TITLE'				=> htmlentities($arrMedium['title'], ENT_QUOTES, CONTREXX_CHARSET),
						'PODCAST_MEDIA_AUTHOR'				=> htmlentities($arrMedium['author'], ENT_QUOTES, CONTREXX_CHARSET),
						'PODCAST_MEDIA_DESCRIPTION'			=> empty($arrMedium['description']) ? '-' : htmlentities($arrMedium['description'], ENT_QUOTES, CONTREXX_CHARSET),
						'PODCAST_MEDIA_DATE'				=> date(ASCMS_DATE_FORMAT, $arrMedium['date_added']),
						'PODCAST_MEDIA_SHORT_DATE'			=> date(ASCMS_DATE_SHORT_FORMAT, $arrMedium['date_added']),
						'PODCAST_MEDIA_URL'					=> htmlentities($arrMedium['source'], ENT_QUOTES, CONTREXX_CHARSET),
						'PODCAST_MEDIA_THUMBNAIL'			=> htmlentities($arrMedium['thumbnail'], ENT_QUOTES, CONTREXX_CHARSET),
						'PODCAST_MEDIA_VIEWS'				=> $this->_getViews($mediumId),
						'PODCAST_MEDIA_PLAYLENGHT'			=> $this->_getPlaylenghtFormatOfTimestamp($arrMedium['playlenght']),
						'PODCAST_MEDIA_SHORT_PLAYLENGHT'	=> $this->_getShortPlaylenghtFormatOfTimestamp($arrMedium['playlenght'])
					));
					$this->_objTpl->parse('podcast_media');
				}
			}

			$mediaCount = &$this->_getMediaCount($categoryId, true);

			if ($mediaCount > $_CONFIG['corePagingLimit']) {
				$pos = isset($_GET['pos']) ? intval($_GET['pos']) : 0;
				$paging = getPaging($mediaCount, $pos, '&amp;section=podcast&amp;cid='.$categoryId, $_ARRAYLANG['TXT_PODCAST_MEDIA']);
				$this->_objTpl->setVariable('PODCAST_PAGING', $paging);
			}
		}
		$setSizeFunction = $this->_getSetSizeJS();

		$podcastJavascript .= <<< EOF
	<script type="text/javascript">
	//<![CDATA[
	if(typeof(setSize == 'undefined')){
		$setSizeFunction
	}
	//]]>
	</script>
EOF;

		$this->_objTpl->setVariable('PODCAST_JAVASCRIPT', $podcastJavascript);

		return $this->_objTpl->get();
	}
}
?>
