<?php

/**
 * Class podcast
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @access      public
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
 * Frontend of the podcast module
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @access        public
 * @version        1.0.0
 * @package     contrexx
 * @subpackage  module_podcast
 * @todo        Edit PHP DocBlocks!
 */
class podcast extends podcastLib
{
    /**
    * Template object
    *
    * @access private
    * @var object
    */
    var $_objTpl;

	private $page_title = '';

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
    function getPage()
    {
		if (!isset($_GET['cmd'])) {
			$_GET['cmd'] = '';
		}

        switch($_GET['cmd']){
            case 'selectSource':
                $this->_selectMediumSource();
            break;
            case 'modifyMedium':
                $this->_modifyMedium();
            break;
            default:
                $this->showMedium();
        }



        return $this->_objTpl->get();
    }

	public function getPageTitle($pageTitle)
	{
			return empty($this->page_title) ? $pageTitle : $this->page_title;
	}

	function _recommendMedium($id, $cid, $arrMedium){
	    global $_CONFIG, $_ARRAYLANG;

        require_once ASCMS_LIBRARY_PATH.'/spamprotection/captcha.class.php';
        $objCaptcha = new Captcha();

	    $reciepientEmail = stripslashes($_POST['podcastRecommendRecipientEmail']);
        $senderName      = stripslashes($_POST['podcastRecommendSenderName']);
        $senderEmail     = stripslashes($_POST['podcastRecommendSenderEmail']);
        $senderComment   = stripslashes($_POST['podcastRecommendComment']);
        $strOffset       = $_POST['podcastRecommendCaptchaOffset'];
        $strCaptcha      = strtoupper($_POST['podcastRecommendCaptcha']);
        $mediumURL       = ASCMS_PROTOCOL.'://'.$_CONFIG['domainUrl'].CONTREXX_SCRIPT_PATH.'?section=podcast&amp;id='.$id.'&amp;cid='.$cid;

        $error = array();
        if(!$objCaptcha->compare($strCaptcha, $strOffset) && empty($_POST['json'])){
            $this->_objTpl->setVariable(array(
                'TXT_PODCAST_ERR_INVALID_CAPTCHA'           => $_ARRAYLANG['TXT_PODCAST_ERR_INVALID_CAPTCHA']
            ));
            $error['Captcha'] = $_ARRAYLANG['TXT_PODCAST_ERR_INVALID_CAPTCHA'];
        }

        if(!FWValidator::isEmail($senderEmail)){
            $this->_objTpl->setVariable(array(
                'TXT_PODCAST_ERR_INVALID_SENDER_MAIL'       => $_ARRAYLANG['TXT_PODCAST_ERR_INVALID_MAIL']
            ));
            $error['SenderEmail'] = $_ARRAYLANG['TXT_PODCAST_ERR_INVALID_MAIL'];
        }

        if(!FWValidator::isEmail($reciepientEmail)){
            $this->_objTpl->setVariable(array(
                'TXT_PODCAST_ERR_INVALID_RECIPIENT_MAIL'    => $_ARRAYLANG['TXT_PODCAST_ERR_INVALID_MAIL']
            ));
            $error['RecipientEmail'] = $_ARRAYLANG['TXT_PODCAST_ERR_INVALID_MAIL'];
        }

        if(empty($senderName)){
            $this->_objTpl->setVariable(array(
                'TXT_PODCAST_ERR_INVALID_SENDER_NAME'       => $_ARRAYLANG['TXT_PODCAST_ERR_INVALID_NAME']
            ));
            $error['SenderName'] = $_ARRAYLANG['TXT_PODCAST_ERR_INVALID_NAME'];
        }

	    if(count($error) == 0){
            if (@include_once ASCMS_LIBRARY_PATH.'/phpmailer/class.phpmailer.php') {
                $objMail = new phpmailer();

                if ($_CONFIG['coreSmtpServer'] > 0 && @include_once ASCMS_CORE_PATH.'/SmtpSettings.class.php') {
                    if (($arrSmtp = SmtpSettings::getSmtpAccount($_CONFIG['coreSmtpServer'])) !== false) {
                        $objMail->IsSMTP();
                        $objMail->Host = $arrSmtp['hostname'];
                        $objMail->Port = $arrSmtp['port'];
                        $objMail->SMTPAuth = true;
                        $objMail->Username = $arrSmtp['username'];
                        $objMail->Password = $arrSmtp['password'];
                    }
                }

                $mailSubject = $_ARRAYLANG['TXT_PODCAST_EMAIL_SUBJECT'];
                $mailBody    = $_ARRAYLANG['TXT_PODCAST_EMAIL_TEMPLATE'];

                $mailSubject = str_replace('[SENDER_NAME]', $senderName, $mailSubject);
                $mailSubject = str_replace('[DOMAIN]', $_CONFIG['domainUrl'], $mailSubject);
                $mailBody    = str_replace('[SENDER_NAME]', $senderName, $mailBody);
                $mailBody    = str_replace('[SENDER_COMMENT]', $senderComment, $mailBody);
                $mailBody    = str_replace('[VIDEO_TITLE]', $arrMedium['title'], $mailBody);
                $mailBody    = str_replace('[VIDEO_DESCRIPTION]', $arrMedium['description'], $mailBody);
                $mailBody    = str_replace('[VIDEO_DATE]', date(ASCMS_DATE_SHORT_FORMAT, $arrMedium['date_added']), $mailBody);
                $mailBody    = str_replace('[VIDEO_URL]', $mediumURL, $mailBody);
                $mailBody    = str_replace('[DOMAIN]', $_CONFIG['domainUrl'], $mailBody);

                $objMail->CharSet = CONTREXX_CHARSET;
                $objMail->From = $senderEmail;
                $objMail->FromName = $senderName;
                $objMail->AddAddress($reciepientEmail);
                $objMail->Subject   = $mailSubject;
                $objMail->IsHTML(false);
                $objMail->Body      = strip_tags($mailBody);
                if($objMail->Send()){
                    $this->_objTpl->setVariable(array(
                        'TXT_PODCAST_SUCCESS_MESSAGE'   =>  sprintf($_ARRAYLANG['TXT_PODCAST_RECOMMENDATION_SENT_SUCCESSFULLY'], $reciepientEmail),
                    ));
                    if(!empty($_POST['json'])){
            	        die(json_encode(array(
            	           'success' => sprintf($_ARRAYLANG['TXT_PODCAST_RECOMMENDATION_SENT_SUCCESSFULLY'], $reciepientEmail),
            	        )));
            	    }
                    return true;
                } else {
                    $this->_objTpl->setVariable(array(
                        'TXT_PODCAST_ERROR_MESSAGE'     =>  $_ARRAYLANG['TXT_PODCAST_RECOMMENDATION_SEND_FAILED'],
                    ));
            	    if(!empty($_POST['json'])){
            	        die(json_encode(array(
            	           'error' => $_ARRAYLANG['TXT_PODCAST_RECOMMENDATION_SEND_FAILED'],
            	        )));
            	    }
                    return false;
                }
            }
	    } else {
      	    $this->_objTpl->setVariable(array(
                'PODCAST_RECOMMEND_RECIPIENT_EMAIL' => $reciepientEmail,
                'PODCAST_RECOMMEND_SENDER_NAME'     => $senderName,
                'PODCAST_RECOMMEND_SENDER_EMAIL'    => $senderEmail,
                'PODCAST_RECOMMEND_SENDER_COMMENT'  => $senderComment,
    	    ));
    	    if(!empty($_POST['json'])){
    	        die(json_encode(array(
    	           'error' => $error
    	        )));
    	    }
    	    return false;
	    }
	}

    function showMedium(){
        global $_ARRAYLANG, $_CONFIG, $_LANGID;
        $categoryId = isset($_REQUEST['cid']) ? (intval($_REQUEST['cid']) == 0 ? false : intval($_REQUEST['cid'])) : false;
        $mediumId = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
        $recommend = isset($_GET['recommend']) ? 1 : 0;
        if($mediumId > 0){
            $this->_updateViews($mediumId);
        }

        $this->_objTpl->setGlobalVariable(array(
            'TXT_PODCAST_PLAY'        => $_ARRAYLANG['TXT_PODCAST_PLAY'],
            'TXT_PODCAST_MEDIA_VIEWS' => $_ARRAYLANG['TXT_PODCAST_MEDIA_VIEWS'],
            'TXT_PODCAST_RECOMMEND_BY_EMAIL'        => $_ARRAYLANG['TXT_PODCAST_RECOMMEND_BY_EMAIL'],
            'TXT_PODCAST_ERR_INVALID_MAIL'			=> $_ARRAYLANG['TXT_PODCAST_ERR_INVALID_MAIL'],
            'TXT_PODCAST_ERR_INVALID_NAME'          => $_ARRAYLANG['TXT_PODCAST_ERR_INVALID_NAME'],
            'TXT_PODCAST_CLOSE'                     => $_ARRAYLANG['TXT_PODCAST_CLOSE'],
            'TXT_PODCAST_RECIPIENT'                 => $_ARRAYLANG['TXT_PODCAST_RECIPIENT'],
            'TXT_PODCAST_EMAIL'		               	=> $_ARRAYLANG['TXT_PODCAST_EMAIL'],
            'TXT_PODCAST_SENDER'	           		=> $_ARRAYLANG['TXT_PODCAST_SENDER'],
            'TXT_PODCAST_NAME'	             		=> $_ARRAYLANG['TXT_PODCAST_NAME'],
            'TXT_PODCAST_ADDITIONAL'	       		=> $_ARRAYLANG['TXT_PODCAST_ADDITIONAL'],
            'TXT_PODCAST_MESSAGE'		         	=> $_ARRAYLANG['TXT_PODCAST_MESSAGE'],
            'TXT_PODCAST_SPAM_PROTECTION'			=> $_ARRAYLANG['TXT_PODCAST_SPAM_PROTECTION'],
            'TXT_PODCAST_SUBMIT'        			=> $_ARRAYLANG['TXT_PODCAST_SUBMIT'],
            'TXT_PODCAST_RESET'	            		=> $_ARRAYLANG['TXT_PODCAST_RESET'],
            'TXT_PODCAST_ABORT'		               	=> $_ARRAYLANG['TXT_PODCAST_ABORT'],
            'TXT_PODCAST_RECOMMEND'		           	=> $_ARRAYLANG['TXT_PODCAST_RECOMMEND'],
            'DIRECTORY_INDEX'                       => CONTREXX_DIRECTORY_INDEX,
        ));

        if (($arrMedium = &$this->_getMedium($mediumId, true)) !== false) {
			$this->page_title = htmlentities($arrMedium['title'], ENT_QUOTES, CONTREXX_CHARSET);

			$recommended = false;
            if(!empty($_POST['podcastRecommendCaptchaOffset'])){ //we've got a recommend form posted!
                $recommended = $this->_recommendMedium($mediumId, $categoryId, $arrMedium);
            }

            if ($this->_objTpl->blockExists('podcast_medium')) {
				$arrCategory = $this->_getCategory($categoryId, true);
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
                    'PODCAST_MEDIUM_CATEGORY_TITLE' => htmlentities($arrCategory['title'], ENT_QUOTES, CONTREXX_CHARSET),
                    'PODCAST_MEDIUM_TITLE'          => htmlentities($arrMedium['title'], ENT_QUOTES, CONTREXX_CHARSET),
                    'PODCAST_MEDIUM_DESCRIPTION'    => $arrMedium['description'],
                    'PODCAST_MEDIUM_CODE'           => $embedCode,
                    'PODCAST_MEDIUM_DATE'           => date(ASCMS_DATE_FORMAT, $arrMedium['date_added']),
                    'PODCAST_MEDIUM_SHORT_DATE'     => date(ASCMS_DATE_SHORT_FORMAT, $arrMedium['date_added']),
                    'PODCAST_MEDIUM_THUMBNAIL'      => htmlentities($arrMedium['thumbnail'], ENT_QUOTES, CONTREXX_CHARSET),
                    'PODCAST_MEDIUM_URL'            => htmlentities($arrMedium['source'], ENT_QUOTES, CONTREXX_CHARSET),
                    'PODCAST_MEDIUM_ENTRY_URL'      => 'http://'.$_CONFIG['domainUrl'].CONTREXX_SCRIPT_PATH.'?section=podcast&amp;id='.$mediumId.'&amp;cid='.$categoryId,
					'PODCAST_MEDIUM_EMBED_CODE' 	=> htmlentities($mediumCode, ENT_QUOTES, CONTREXX_CHARSET),
					'PODCAST_MEDIUM_WIDTH'       	=> $arrMedium['width'] + $arrTemplate['player_offset_width'],
					'PODCAST_MEDIUM_HEIGHT'       	=> $arrMedium['height'] + $arrTemplate['player_offset_height']
                ));

		        if ($this->_arrSettings['enable_recommend_by_email'] > 0) {
                    if($this->_objTpl->blockExists('podcast_recommend_by_email_link')) $this->_objTpl->parse('podcast_recommend_by_email_link');

                    require_once ASCMS_LIBRARY_PATH.'/spamprotection/captcha.class.php';
                    $objCaptcha = new Captcha();
                    $this->_objTpl->setVariable(array(
                        'PODCAST_CAPTCHA_URL'         =>  $objCaptcha->getUrl(),
                        'PODCAST_CAPTCHA_ALT'         =>  $objCaptcha->getAlt(),
                        'PODCAST_CAPTCHA_OFFSET'      =>  $objCaptcha->getOffset()
                    ));
		        }

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
                    'PODCAST_MEDIUM_PLAYLENGTH'         => $this->_getPlaylengthFormatOfTimestamp($arrMedium['playlength']),
					'PODCAST_MEDIUM_SHORT_PLAYLENGHT'    => $this->_getShortPlaylengthFormatOfTimestamp($arrMedium['playlength']),
// TODO: Spelling error. Fix the template as well and remove this
					'PODCAST_MEDIUM_PLAYLENGHT' 		=> $this->_getPlaylengthFormatOfTimestamp($arrMedium['playlength']),
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
                // parse recommend form
		        if ($this->_arrSettings['enable_recommend_by_email'] < 1) {
                    if($this->_objTpl->blockExists('podcast_recommend_by_email_form')) $this->_objTpl->hideBlock('podcast_recommend_by_email_form');
                } else {
                    $class = 'normal';
                    if($recommend == 0 || $recommended){
                       $class = 'hidden';
                    }
                    $this->_objTpl->setVariable(array(
                        'PODCAST_RECOMMEND_FORM_CONTAINER_CLASS'    => $class,
                        'PODCAST_MEDIUM_ID'                         => $mediumId,
                        'PODCAST_MEDIUM_CATEGORY_ID'                => $categoryId,
                    ));
                    if($this->_objTpl->blockExists('podcast_recommend_by_email_form')) $this->_objTpl->parse('podcast_recommend_by_email_form');
                }

                $this->_objTpl->parse('podcast_medium');
            }
            if ($this->_objTpl->blockExists('podcast_no_medium')) {
                $this->_objTpl->hideBlock('podcast_no_medium');
            }


			$this->_objTpl->hideBlock('podcast_media');
			if ($this->_objTpl->blockExists('podcast_media_list')) {
				$this->_objTpl->hideBlock('podcast_media_list');
			}
			$this->_objTpl->hideBlock('podcast_selected_category');
			$this->_objTpl->hideBlock('podcast_categories');
			$this->_objTpl->hideBlock('podcast_latest');
			$this->_objTpl->hideBlock('podcast_latest_block');
        } else {
            if ($this->_objTpl->blockExists('podcast_no_medium')) {
                $this->_objTpl->touchBlock('podcast_no_medium');
            }
            if ($this->_objTpl->blockExists('podcast_medium')) {
                $this->_objTpl->hideBlock('podcast_medium');
        }

        $menu = $this->_getCategoriesMenu($categoryId, 'id="podcast_category_menu"', true, true);
        if ($menu !== false) {
            $this->_objTpl->setVariable('PODCAST_CATEGORY_MENU', $menu.' <input type="button" onclick="window.location.href=\'index.php?section=podcast&amp;cid=\'+document.getElementById(\'podcast_category_menu\').value" value="'.$_ARRAYLANG['TXT_PODCAST_SHOW'].'" />');
        }

			if ($categoryId) {
				$this->category($categoryId);
				$this->mediaList($categoryId);

				$this->_objTpl->hideBlock('podcast_categories');
				$this->_objTpl->hideBlock('podcast_latest');
				$this->_objTpl->hideBlock('podcast_latest_block');
			} else {
				$this->categoryList();

				$this->_objTpl->hideBlock('podcast_media');
				if ($this->_objTpl->blockExists('podcast_media_list')) {
					$this->_objTpl->hideBlock('podcast_media_list');
				}
				$this->_objTpl->hideBlock('podcast_selected_category');

				$arrLatestMedia = $this->_getMedia(false, true, $this->_arrSettings['latest_media_count']);
				$this->_showLatestMedia($arrLatestMedia);
				$this->_objTpl->touchBlock('podcast_latest_block');
			}
        }

        $this->_objTpl->setVariable('PODCAST_JAVASCRIPT', $this->_getRecommendJS($mediumId, $categoryId).$this->_getSetSizeJS().$this->getResizeThumbnailsJS($this->_arrSettings['thumb_max_size']));
    }

    private function _getRecommendJS($id, $cid)
    {
        return <<< EOJS
<script type="text/javascript">
//<![CDATA[
(function(){
    var formContainer;
    $(function(){ //document ready
        formContainer       = $('#podcastRecommendFormContainer');
        var form            = $('#podcastRecommendForm');
        var abortElements   = $('#podcastRecommendHideForm,#podcastRecommendAbort');

        $('#podcastRecommendShowForm').click(function(){
            formContainer.removeClass('hidden');
            return false;
        });

        abortElements.click(function(){
            formContainer.addClass('hidden');
            return false;
        });

        form.submit(function(){
            $.ajax({
                url: '{DIRECTORY_INDEX}?section=podcast&recommend=1&id=$id&cid=$cid',
                type: 'POST',
                dataType: 'json',
                data: form.serialize()+'&json=1',
                success: checkRecommendResponse
            })
            return false;
        })

        $('#podcastCaptcha').addClass('hidden');
    });

    var checkRecommendResponse = function(data){
        $('#podcastRecommendForm span.error').text(''); //clear all errors
        if(data.error){
            setErrors(data.error);
        }
        if(data.success){
            $('#podcastSuccessMessage').text(data.success);
            $('#podcastRecommendForm').get(0).reset();
            formContainer.addClass('hidden');
        }
    }

    var setErrors = function(error){
        if(typeof error == 'string'){
            $('#podcastErrorMessage').text(error);
        } else {
            for(name in error){
                $('#podcastError'+name).text(error[name]);
            }
        }
    }
})();
//]]>
</script>
EOJS;
    }

	private function category($categoryId)
	{
		$arrCategory = $this->_getCategory($categoryId, true);
		if ($arrCategory) {
			$this->page_title = htmlentities($arrCategory['title'], ENT_QUOTES, CONTREXX_CHARSET);

			$this->_objTpl->setVariable(array(
				'PODCAST_CATEGORY_TITLE' 		=> htmlentities($arrCategory['title'], ENT_QUOTES, CONTREXX_CHARSET),
				'PODCAST_CATEGORY_DESCRIPTION'  => htmlentities($arrCategory['description'], ENT_QUOTES, CONTREXX_CHARSET)
			));
			$this->_objTpl->parse('podcast_selected_category');
		}
	}

	private function categoryList()
	{
		$columnCount = 2;
		$arrCategories = $this->_getCategories(true, false, true);
		$categoryCount = count($arrCategories);
		$rowsPerColumnAverage = $categoryCount / $columnCount;
		$rowsPerColumn = intval(floor($rowsPerColumnAverage));
		$rowsPerColumnOffset = intval(ceil($columnCount * ($rowsPerColumnAverage - $rowsPerColumn)));

		$row = 0;
		$col = 1;
		foreach ($arrCategories as $categoryId => $arrCategory) {
			$this->_objTpl->setVariable(array(
				'PODCAST_CATEGORY_ID' 			=> $categoryId,
				'PODCAST_CATEGORY_TITLE' 		=> htmlentities($arrCategory['title'], ENT_QUOTES, CONTREXX_CHARSET),
				'PODCAST_CATEGORY_MEDIA_COUNT' 	=> $arrCategory['media_count']
			));
			$this->_objTpl->parse('podcast_category');

			$row++;
			if ($col > $rowsPerColumnOffset && $row == $rowsPerColumn || $row > $rowsPerColumn) {
				$this->_objTpl->parse('podcast_categories');
				$col++;
				$row = 0;
			}
		}
	}

	private function mediaList($categoryId)
	{
        global $_ARRAYLANG, $_CONFIG, $_LANGID;

        if(intval($categoryId) == 0){
			$categories = array_keys($this->_getCategories(true, false, true));
        }else{
            $categories = $categoryId;
        }
        if ($this->_objTpl->blockExists('podcast_media')) {
            $pos = isset($_GET['pos']) ? intval($_GET['pos']) : 0;
            $arrMedia = &$this->_getMedia($categories, true, $_CONFIG['corePagingLimit'], $pos);
            if (count($arrMedia) > 0) {
				$i=0;
                foreach ($arrMedia as $mediumId => $arrMedium) {
					$shortDescription = strip_tags($arrMedium['description']);
					if (strlen($shortDescription) > 100) {
						$shortDescription = substr($shortDescription, 0, 97).'...';
					}
                    $this->_objTpl->setVariable(array(
                        'PODCAST_MEDIUM_ROW'                => $i%2==0 ? 'row1' : 'row2',
                        'PODCAST_MEDIA_ID'                  => $mediumId,
                        'PODCAST_MEDIA_CATEGORY_ID'         => $categoryId,
                        'PODCAST_MEDIA_TITLE'               => htmlentities($arrMedium['title'], ENT_QUOTES, CONTREXX_CHARSET),
						'PODCAST_MEDIA_DESCRIPTION'         => empty($arrMedium['description']) ? '-' : $arrMedium['description'],
						'PODCAST_MEDIA_SHORT_DESCRIPTION'   => $shortDescription,
                        'PODCAST_MEDIA_DATE'                => date(ASCMS_DATE_FORMAT, $arrMedium['date_added']),
                        'PODCAST_MEDIA_SHORT_DATE'          => date(ASCMS_DATE_SHORT_FORMAT, $arrMedium['date_added']),
                        'PODCAST_MEDIA_URL'                 => htmlentities($arrMedium['source'], ENT_QUOTES, CONTREXX_CHARSET),
						'PODCAST_MEDIA_THUMBNAIL'           => htmlentities($arrMedium['thumbnail'], ENT_QUOTES, CONTREXX_CHARSET)
					));

					// parse author
					$this->_objTpl->setVariable('PODCAST_MEDIA_AUTHOR', empty($arrMedium['author']) ? '-' : htmlentities($arrMedium['author'], ENT_QUOTES, CONTREXX_CHARSET));
					if ($this->_objTpl->blockExists('podcast_media_author')) {
						if (empty($arrMedium['author'])) {
							$this->_objTpl->hideBlock('podcast_media_author');
						} else {
							$this->_objTpl->parse('podcast_media_author');
						}
					}
					// parse playlength
					$this->_objTpl->setVariable(array(
                        'PODCAST_MEDIA_PLAYLENGTH'          => $this->_getPlaylengthFormatOfTimestamp($arrMedium['playlength']),
                        'PODCAST_MEDIA_SHORT_PLAYLENGTH'    => $this->_getShortPlaylengthFormatOfTimestamp($arrMedium['playlength']),
// TODO: Spelling error. Fix the template as well and remove this
						'PODCAST_MEDIA_PLAYLENGHT' 		    => $this->_getPlaylengthFormatOfTimestamp($arrMedium['playlength']),
						'PODCAST_MEDIA_SHORT_PLAYLENGHT'    => $this->_getShortPlaylengthFormatOfTimestamp($arrMedium['playlength'])
                    ));
					if ($this->_objTpl->blockExists('podcast_media_playlength')) {
						if (empty($arrMedium['playlength'])) {
							$this->_objTpl->hideBlock('podcast_media_playlength');
						} else {
							$this->_objTpl->parse('podcast_media_playlength');
						}
					}
					// parse views
					$this->_objTpl->setVariable('PODCAST_MEDIA_VIEWS', $this->_getViews($mediumId));
					if ($this->_objTpl->blockExists('podcast_media_views')) {
						if (empty($arrMedium['views'])) {
							$this->_objTpl->hideBlock('podcast_media_views');
						} else {
							$this->_objTpl->parse('podcast_media_views');
						}
					}
					// parse filesize
					$this->_objTpl->setVariable('PODCAST_MEDIA_FILESIZE', $this->_formatFileSize($arrMedium['size']));
					if ($this->_objTpl->blockExists('podcast_media_filesize')) {
						if (empty($arrMedium['filesize'])) {
							$this->_objTpl->hideBlock('podcast_media_filesize');
						} else {
							$this->_objTpl->parse('podcast_media_filesize');
						}
					}

                    $i++;
                    $this->_objTpl->parse('podcast_media');
                }
            }

			$mediaCount = &$this->getAmountOfLoadedMedia();

            if ($mediaCount > $_CONFIG['corePagingLimit']) {
                $paging = getPaging($mediaCount, $pos, '&amp;section=podcast&amp;cid='.$categoryId, $_ARRAYLANG['TXT_PODCAST_MEDIA']);
                $this->_objTpl->setVariable('PODCAST_PAGING', $paging);
            }
        }

		if ($this->_objTpl->blockExists('podcast_media_list')) {
			$this->_objTpl->touchBlock('podcast_media_list');
			$this->_objTpl->parse('podcast_media_list');
    }
    }

}
