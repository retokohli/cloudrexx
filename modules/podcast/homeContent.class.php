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
    function getContent($blockFirst = false)
    {
        $this->_objTpl->setTemplate($this->_pageContent, true, true);
        if(empty($this->_latestMedia)){
            $this->_latestMedia = &$this->_getLastestMedia();
        }
        $this->_showLatestMedia($this->_latestMedia, $blockFirst);
        return $this->_objTpl->get();
    }

    /**
     * show the latest media
     *
     * @param array $arrMedia
     */
    function _showLatestMedia($arrMedia, $blockFirst = false)
    {
        global $_ARRAYLANG;

        $tmpOnload = (!$blockFirst) ? 'try{tmp();}catch(e){}' : '';
        $setSizeFunction = $this->_getSetSizeJS();
        $maxSize = $this->_arrSettings['thumb_max_size_homecontent'];
        $thumbailJS = <<< EOF
<script type="text/javascript">
//<![CDATA[
    var thumbSizeMaxBlock = $maxSize;

    if(typeof(setSize == 'undefined')){
        $setSizeFunction
    }

    tmp = window.onload;
    if(tmp == null){
        tmp = function(){};
    }
    window.onload = function(){
        bThumbnails = document.getElementsByName("podcast_thumbnails_block");
        for(i=0;i<bThumbnails.length;i++){
            try{
                setSize(bThumbnails[i], thumbSizeMaxBlock);
            }catch(e){}
        }
        $tmpOnload
    }
//]]>
</script>
EOF;

        $this->_objTpl->setGlobalVariable(array(
            'TXT_PODCAST_PLAY'             => $_ARRAYLANG['TXT_PODCAST_PLAY'],
            'TXT_PODCAST_MEDIA_VIEWS'     => $_ARRAYLANG['TXT_PODCAST_MEDIA_VIEWS'],
            'PODCAST_LATEST_JAVASCRIPT' => $thumbailJS,
        ));

        if (count($arrMedia) > 0) {
            if ($this->_objTpl->blockExists('podcast_latest')) {
                foreach ($arrMedia as $mediumId => $arrMedium) {
                    $this->_objTpl->setVariable(array(
                        'PODCAST_MEDIA_ID'                    => $mediumId,
                        'PODCAST_MEDIA_TITLE'                => htmlentities($arrMedium['title'], ENT_QUOTES, CONTREXX_CHARSET),
                        'PODCAST_MEDIA_AUTHOR'                => htmlentities($arrMedium['author'], ENT_QUOTES, CONTREXX_CHARSET),
                        'PODCAST_MEDIA_DESCRIPTION'            => empty($arrMedium['description']) ? '-' : htmlentities($arrMedium['description'], ENT_QUOTES, CONTREXX_CHARSET),
                        'PODCAST_MEDIA_DATE'                => date(ASCMS_DATE_FORMAT, $arrMedium['date_added']),
                        'PODCAST_MEDIA_SHORT_DATE'            => date(ASCMS_DATE_SHORT_FORMAT, $arrMedium['date_added']),
                        'PODCAST_MEDIA_URL'                    => htmlentities($arrMedium['source'], ENT_QUOTES, CONTREXX_CHARSET),
                        'PODCAST_MEDIA_THUMBNAIL'            => htmlentities($arrMedium['thumbnail'], ENT_QUOTES, CONTREXX_CHARSET),
// TODO: Spelling error. Fix the template as well and remove this
                        'PODCAST_MEDIA_PLAYLENGHT'            => $this->_getPlaylengthFormatOfTimestamp($arrMedium['playlength']),
                        'PODCAST_MEDIA_PLAYLENGTH'            => $this->_getPlaylengthFormatOfTimestamp($arrMedium['playlength']),
// TODO: Spelling error. Fix the template as well and remove this
                        'PODCAST_MEDIA_SHORT_PLAYLENGHT'    => $this->_getShortPlaylengthFormatOfTimestamp($arrMedium['playlength']),
                        'PODCAST_MEDIA_SHORT_PLAYLENGTH'    => $this->_getShortPlaylengthFormatOfTimestamp($arrMedium['playlength']),
                        'PODCAST_MEDIA_VIEWS'                => $this->_getViews($mediumId),
                    ));
                    $this->_objTpl->parse('podcast_latest');
                }
            }
        }
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

?>
