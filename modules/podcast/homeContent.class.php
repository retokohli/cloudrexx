<?php

/**
 * Podcast home content
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_podcast
 * @todo        Edit PHP DocBlocks!
 */

/**
 * @ignore
 */
require_once ASCMS_MODULE_PATH.'/podcast/lib/podcastLib.class.php';

/**
 * podcast home content
 *
 * Show Forum Block Content
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @access      public
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_podcast
 */
class podcastHomeContent extends podcastLib {

    var $_pageContent;
    var $_objTpl;
    var $_langId;
    var $_latestMedia;

    /**
     * Constructor php5
     */
    function __construct($pageContent) {
        global $_LANGID;
        $this->_pageContent = $pageContent;
        $this->_objTpl = &new HTML_Template_Sigma('.');
        CSRF::add_placeholder($this->_objTpl);
        $this->_langId = $_LANGID;
           parent::__construct();
    }

    /**
     * Fetch latest entries and parse forumtemplate
     *
     * @return string parsed latest entries
     */
    function getContent()
    {
        $this->_objTpl->setTemplate($this->_pageContent, true, true);
        if(empty($this->_latestMedia)){
            $this->_latestMedia = &$this->_getLastestMedia();
        }
        $this->_showLatestMedia($this->_latestMedia);
        return $this->_objTpl->get();
    }

	public function getVideoContent()
    {
		$this->_objTpl->setTemplate($this->_pageContent, true, true);
		if ($this->showVideo()) {
			return $this->_objTpl->get();
		} else {
			return '';
                }
            }

	public function showVideo()
	{
		$mediumId = isset($_GET['podcastId']) ? intval($_GET['podcastId']) : 0;
		if (empty($mediumId)) {
			switch ($this->_arrSettings['default_medium']) {
			case 'selected':
				$mediumId = $this->_arrSettings['default_medium_id'];
				break;

			case 'newest':
				$mediumId = key($this->_getMedia(false, true, 1));
				break;

			case 'none':
			default:
				return false;
				break;
        }
    }

		$categoryId = isset($_GET['categoryId']) ? intval($_GET['categoryId']) : 0;
		$arrMedium = $this->_getMedium($mediumId, true);
		if ($arrMedium) {
			$podcastJavascript = $this->_getSetSizeJS();

			$arrTemplate = &$this->_getTemplate($arrMedium['template_id']);
			$mediumCode = $this->_getHtmlTag($arrMedium, $arrTemplate['template'], $arrTemplate['player_offset_width'], $arrTemplate['player_offset_height']);

			if ($arrTemplate['js_embed']) {
				$embedCode = $this->getJSEmbedCode($mediumCode);
			} else {
				$embedCode = $mediumCode;
			}
			$this->_objTpl->setVariable(array(
				'PODCAST_MEDIUM_ID'             => $mediumId,
				'PODCAST_MEDIUM_CATEGORY_ID'    => $categoryId,
				'PODCAST_MEDIUM_TITLE'          => htmlentities($arrMedium['title'], ENT_QUOTES, CONTREXX_CHARSET),
				'PODCAST_MEDIUM_DESCRIPTION'    => $arrMedium['description'],
				'PODCAST_MEDIUM_CODE'           => $embedCode,
				'PODCAST_MEDIUM_DATE'           => date(ASCMS_DATE_FORMAT, $arrMedium['date_added']),
				'PODCAST_MEDIUM_SHORT_DATE'     => date(ASCMS_DATE_SHORT_FORMAT, $arrMedium['date_added']),
				'PODCAST_MEDIUM_THUMBNAIL'      => htmlentities($arrMedium['thumbnail'], ENT_QUOTES, CONTREXX_CHARSET),
				'PODCAST_MEDIUM_URL'            => htmlentities($arrMedium['source'], ENT_QUOTES, CONTREXX_CHARSET),
				'PODCAST_MEDIUM_WIDTH'       	=> $arrMedium['width'] + $arrTemplate['player_offset_width'],
				'PODCAST_MEDIUM_HEIGHT'       	=> $arrMedium['height'] + $arrTemplate['player_offset_height'],
				'PODCAST_JAVASCRIPT' 			=> $podcastJavascript
			));

			// parse author
			$this->_objTpl->setVariable('PODCAST_MEDIUM_AUTHOR', empty($arrMedium['author']) ? '-' : htmlentities($arrMedium['author'], ENT_QUOTES, CONTREXX_CHARSET));
			if ($this->_objTpl->blockExists('podcast_medium_author')) {
				if (empty($arrMedium['author'])) {
					$this->_objTpl->hideBlock('podcast_medium_author');
				} else {
					$this->_objTpl->parse('podcast_medium_author');
				}
			}
			// parse playlength
			$this->_objTpl->setVariable(array(
				'PODCAST_MEDIUM_PLAYLENGHT' 		=> $this->_getPlaylengthFormatOfTimestamp($arrMedium['playlength']),
				'PODCAST_MEDIUM_SHORT_PLAYLENGHT'    => $this->_getShortPlaylengthFormatOfTimestamp($arrMedium['playlength'])
			));
			if ($this->_objTpl->blockExists('podcast_medium_playlength')) {
				if (empty($arrMedium['playlength'])) {
					$this->_objTpl->hideBlock('podcast_medium_playlength');
				} else {
					$this->_objTpl->parse('podcast_medium_playlength');
				}
			}
			// parse views
			$this->_objTpl->setVariable('PODCAST_MEDIUM_VIEWS', $this->_getViews($mediumId));
			if ($this->_objTpl->blockExists('podcast_medium_views')) {
				if (empty($arrMedium['views'])) {
					$this->_objTpl->hideBlock('podcast_medium_views');
				} else {
					$this->_objTpl->parse('podcast_medium_views');
				}
			}
			// parse filesize
			$this->_objTpl->setVariable('PODCAST_MEDIUM_FILESIZE', $this->_formatFileSize($arrMedium['size']));
			if ($this->_objTpl->blockExists('podcast_medium_filesize')) {
				if (empty($arrMedium['filesize'])) {
					$this->_objTpl->hideBlock('podcast_medium_filesize');
				} else {
					$this->_objTpl->parse('podcast_medium_filesize');
				}
			}

			return true;
		}

		return false;
	}


    /**
     * get the latest media
     *
     * @return array latest entries
     */
    function _getLastestMedia(){
        $homeContentCategories = $this->_getHomecontentCategories($this->_langId);
        if(empty($homeContentCategories)){
            $homeContentCategories = array();
        }
        return $this->_getMedia($homeContentCategories, true, $this->_arrSettings['latest_media_count']);
    }
}
