<?php
/**
 * News
 *
 * This module will get all the news pages
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author Comvation Development Team <info@comvation.com>
 * @version 1.0.0
 * @package     contrexx
 * @subpackage  core_module_news
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once ASCMS_CORE_MODULE_PATH . '/news/lib/newsLib.class.php';
require_once(ASCMS_FRAMEWORK_PATH.DIRECTORY_SEPARATOR.'Image.class.php');

/**
 * News
 *
 * This module will get all the news pages
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author Comvation Development Team <info@comvation.com>
 * @access public
 * @version 1.0.0
 * @package     contrexx
 * @subpackage  core_module_news
 */
class news extends newsLibrary {
    var $newsTitle;
    var $langId;
    var $arrSettings = array();
    var $_objTpl;
    var $_submitMessage;

    var $_commentErrorMessage = '';
	var $_commentTitle = '';
	var $_commentMessage = '';
	var $_commentAuthor = '';
    
    /**
    * Constructor
    *
    * @param  string
    * @access public
    */
    function news($pageContent)
    {
        $this->__construct($pageContent);
    }

    /**
     * PHP5 constructor
     * @param  string  $pageContent
     * @global integer
     * @access public
     */
    function __construct($pageContent)
    {
        global $_LANGID;

        $this->getSettings();
        $this->pageContent = $pageContent;
        $this->langId = $_LANGID;

        $this->_objTpl = new HTML_Template_Sigma();
        CSRF::add_placeholder($this->_objTpl);
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);
    }

    /**
    * Get page
    *
    * @access public
    * @return string content
    */
    function getNewsPage()
    {
        if (!isset($_REQUEST['cmd'])) {
            $_REQUEST['cmd'] = '';
        }

        switch($_REQUEST['cmd']) {
        case 'details':
        	if (isset($_REQUEST['comment']) && $_REQUEST['comment'] == 'add') {
        		$this->addComment();
        	}
            return $this->getDetails();
            break;

        case 'submit':
            return $this->_submit();
            break;
        case 'feed':
            return $this->_showFeed();
            break;
        case 'archive':
            return $this->_getArchive();
            break;
        default:
            return $this->getHeadlines();
            break;
        }
    }

    /**
    * Gets the news details
    *
    * @global    array
    * @global    ADONewConnection
    * @global    array
    * @return    string    parsed content
    */
    function getDetails()
    {
        global $_CONFIG, $objDatabase, $_ARRAYLANG;

        $paging     = '';
        $pos        = 0;
        $i 			= 0;

        if (isset($_GET['pos'])) {
            $pos = intval($_GET['pos']);
        }

        $this->_objTpl->setTemplate($this->pageContent);
        $newsid = intval($_GET['newsid']);

        if ($newsid!=0) {
            $objResult = $objDatabase->SelectLimit('SELECT  news.id                 AS id,
                                                            news.userid             AS userid,
                                                            news.source             AS source,
                                                            news.changelog          AS changelog,
                                                            news.url1               AS url1,
                                                            news.url2               AS url2,
                                                            locale.text             AS text,
                                                            news.date               AS date,
                                                            news.changelog          AS changelog,
                                                            locale.title            AS title,
                                                            news.teaser_image_path  AS newsimage,
                                                            locale.teaser_text      AS teasertext,
                                                            catl.name                AS catname
                                                    FROM    '.DBPREFIX.'module_news AS news
                                                      INNER JOIN '.DBPREFIX.'module_news_categories AS cat ON cat.catid = news.catid
                                                      INNER JOIN '.DBPREFIX.'module_news_categories_locale AS catl ON catl.category_id = news.catid
                                                      INNER JOIN '.DBPREFIX.'module_news_locale AS locale ON news.id = locale.news_id 
                                                    WHERE   news.status = 1 AND
                                                            news.id = '.$newsid.' AND
                                                            locale.lang_id ='.$this->langId.' AND
                                                            catl.lang_id ='.$this->langId.' AND
                                                            (news.startdate <= \''.date('Y-m-d H:i:s').'\' OR news.startdate="0000-00-00 00:00:00") AND
                                                            (news.enddate >= \''.date('Y-m-d H:i:s').'\' OR news.enddate="0000-00-00 00:00:00")'
                                                           .($this->arrSettings['news_message_protection'] == '1' && !Permission::hasAllAccess() ? (
                                                                ($objFWUser = FWUser::getFWUserObject()) && $objFWUser->objUser->login() ?
                                                                    " AND (frontend_access_id IN (".implode(',', array_merge(array(0), $objFWUser->objUser->getDynamicPermissionIds())).") OR userid = ".$objFWUser->objUser->getId().") "
                                                                    :   " AND frontend_access_id=0 ")
                                                                :   '')
                                                    , 1);

            if ($objResult !== false) {
                while (!$objResult->EOF) {
                    $lastUpdate     = $objResult->fields['changelog'];
                    $source         = htmlspecialchars($objResult->fields['source'], ENT_QUOTES, CONTREXX_CHARSET);
                    $url1           = htmlspecialchars($objResult->fields['url1'], ENT_QUOTES, CONTREXX_CHARSET);
                    $url2           = htmlspecialchars($objResult->fields['url2'], ENT_QUOTES, CONTREXX_CHARSET);
                    $newsUrl        = '';
                    $newsSource     = '';
                    $newsLastUpdate = '';

                    if (!empty($url1)) {
                        $strUrl1 = $objResult->fields['url1'];
                        if (strlen($strUrl1) > 40) {
                            $strUrl1 = substr($strUrl1,0,26).'...'.substr($strUrl1,(strrpos($strUrl1,'.')));
                        }
                        $newsUrl = $_ARRAYLANG['TXT_IMPORTANT_HYPERLINKS'].'<br /><a target="_blank" href="'.$url1.'" title="'.$url1.'">'.$strUrl1.'</a><br />';
                    }
                    if (!empty($url2)) {
                        $strUrl2 = $objResult->fields['url2'];
                        if (strlen($strUrl2) > 40) {
                            $strUrl2 = substr($strUrl2,0,26).'...'.substr($strUrl2,(strrpos($strUrl2,'.')));
                        }
                        $newsUrl .= '<a target="_blank" href="'.$url2.'" title="'.$url2.'">'.$strUrl2.'</a><br />';
                    }
                    if (!empty($source)) {
                        $strSource = $objResult->fields['source'];
                        if (strlen($strSource) > 40) {
                            $strSource = substr($strSource,0,26).'...'.substr($strSource,(strrpos($strSource,'.')));
                        }
                        $newsSource = $_ARRAYLANG['TXT_NEWS_SOURCE'].'<br /><a target="_blank" href="'.$source.'" title="'.$source.'">'.$strSource.'</a><br />';
                    }
                    if (!empty($lastUpdate)) {
                        $newsLastUpdate = $_ARRAYLANG['TXT_LAST_UPDATE'].'<br />'.date(ASCMS_DATE_FORMAT,$objResult->fields['changelog']);
                    }

                    if ($objResult->fields['userid'] && ($objFWUser = FWUser::getFWUserObject()) && ($objUser = $objFWUser->objUser->getUser($objResult->fields['userid']))) {
                        $firstname = $objUser->getProfileAttribute('firstname');
                        $lastname = $objUser->getProfileAttribute('lastname');
                        if (!empty($firstname) && !empty($lastname)) {
                            $author = htmlentities($firstname.' '.$lastname, ENT_QUOTES, CONTREXX_CHARSET);
                        } else {
                            $author = htmlentities($objUser->getUsername(), ENT_QUOTES, CONTREXX_CHARSET);
                        }
                    } else {
                        $author = $_ARRAYLANG['TXT_ANONYMOUS'];
                    }

                    $newstitle = htmlspecialchars(stripslashes($objResult->fields['title']), ENT_QUOTES, CONTREXX_CHARSET);
                    
                    //Show comment-part
			        if ($this->arrSettings['news_comments_activated']) {
			            //comments are activated
                    
	                    // get comments list
	                    $query = '  SELECT      title            AS commentstitle,
				                                date             AS commentsdate,
				                                poster_name      AS commentsauthor,
				                                userid           AS userid,
				                                text             AS commentscontent
				                    FROM        '.DBPREFIX.'module_news_comments
				                    WHERE       newsid = '.$newsid.' AND is_active = "1"
				                    ORDER BY    date DESC';
				
				        /***start paging ****/
				        $comResult = $objDatabase->Execute($query);
				        $count = $comResult->RecordCount();
				        if ($count > intval($_CONFIG['corePagingLimit'])) {
				            $paging = getPaging($count, $pos, "&amp;section=news&amp;cmd=details&amp;newsid=".$newsid, $_ARRAYLANG['TXT_NEWS_COMMENTS'], true);
				        }
				        $this->_objTpl->setVariable("COMMENTS_PAGING", $paging);
				        $comResult = $objDatabase->SelectLimit($query, $_CONFIG['corePagingLimit'], $pos);
				        /*** end paging ***/
				        if ($count >= 1) {
				            while (!$comResult->EOF) {
				                ($i % 2) ? $class  = 'row2' : $class  = 'row1';
				                $strUserName 	= '';
			                	$strUserAvatar	= '<img src="'.ASCMS_ACCESS_PROFILE_IMG_WEB_PATH.'/'.User_Profile::$arrNoAvatar['src'].'" alt="'.$strUserName.'" />';
								$objUser = $objFWUser->objUser->getUser($comResult->fields['userid']);
								if ($comResult->fields['userid'] == 0 || $objUser === false) {
									$strUserName = htmlentities($comResult->fields['commentsauthor'], ENT_QUOTES, CONTREXX_CHARSET);
			                	} else {
									$strUserName = htmlentities($objUser->getUsername(), ENT_QUOTES, CONTREXX_CHARSET);
			
									if ($objUser->getProfileAttribute('picture') != '') {
										$strUserAvatar	= '<img src="'.ASCMS_ACCESS_PROFILE_IMG_WEB_PATH.'/'.$objUser->getProfileAttribute('picture').'" alt="'.$strUserName.'" />';
									}
			                	}
				                
				                $commentstitle = htmlentities(stripslashes($comResult->fields['commentstitle']), ENT_QUOTES, CONTREXX_CHARSET);
				                $commentsbody = contrexx_stripslashes(nl2br($comResult->fields['commentscontent']));
				
				                $this->_objTpl->setVariable(array(
				                           'NEWS_COMMENTS_CSS'          => $class,
				                           'NEWS_COMMENTS_TITLE'        => $commentstitle,
				                           'NEWS_COMMENTS_BODY'		    => $commentsbody,
				                           'NEWS_COMMENTS_DATE'         => $this->getPostedByString($strUserName, date(ASCMS_DATE_FORMAT,$comResult->fields['commentsdate'])),
				                           'NEWS_COMMENTS_AVATAR'		=> $strUserAvatar
				                ));
				                
				                $this->_objTpl->parse('commentsrow');
				                $i++;
				                $comResult->MoveNext();
				            }
				        } else {
				        	$this->_objTpl->setVariable('TXT_NEWS_COMMENTS_NONE_EXISTING', $_ARRAYLANG['TXT_NEWS_COMMENTS_NONE_EXISTING']);
	                		$this->_objTpl->parse('showNoComment');
				        }
				        
				        $objFWUser = FWUser::getFWUserObject();
		                $userid = $objFWUser->objUser->getId();
				        if ($this->arrSettings['news_comments_anonymous'] == '1' || $userid) {
                			//Anonymous comments allowed or user is logged in
					        // generate form for adding comments
							// generate captcha code for comments
		                    $captcha = "";
		                    $authorInput = '<input type="text" name="cAuthor" id="cAuthor" value="' . htmlentities($this->_commentAuthor, ENT_QUOTES, CONTREXX_CHARSET) . '" />';
		                    if (!$userid) { // need captcha
		                    	require_once ASCMS_LIBRARY_PATH.'/spamprotection/captcha.class.php';
		                		$objCaptcha = new Captcha();
		                    	$captcha = "<p><label>" . $_ARRAYLANG['TXT_SPAM_PREVENT'] . "</label>
		                    	<img src='" . $objCaptcha->getUrl() . "' 
		                    		alt='" . $objCaptcha->getAlt() . "'
		                    		title='" . $objCaptcha->getAlt() . "' /><br/>
		    					<input type='hidden' name='cOffset' value='" . $objCaptcha->getOffset() . "' />
		    					<input type='text' name='cCaptcha'/></p>";
		                    } else {
		                    	$this->_commentAuthor = htmlentities($objFWUser->objUser->getUsername(), ENT_QUOTES, CONTREXX_CHARSET);
		                    	
		                    	$authorInput = $this->_commentAuthor . '<input type="hidden" name="cAuthor" id="cAuthor" value="' . $this->_commentAuthor . '" /><input type="hidden" name="cAuthorID" value="' . $userid . '" />';
		                    }
		                    $this->_objTpl->setVariable(array(
		                    	'COMMENT_ERROR_MESSAGE' => $this->_commentErrorMessage,
								'COMMENT_TITLE' 		=> htmlentities($this->_commentTitle, ENT_QUOTES, CONTREXX_CHARSET),
								'COMMENT_MESSAGE' 	=> htmlentities($this->_commentMessage, ENT_QUOTES, CONTREXX_CHARSET),
								'AUTHOR_INPUT'	 	=> $authorInput,
								'COMMENT_CAPTCHA'	=> $captcha,
								'TARGET_NEWS_ID' 	=> $newsid,
							   	'TXT_NEWS_COMMENTS' 			=> $_ARRAYLANG['TXT_NEWS_COMMENTS'],
							   	'TXT_NEWS_COMMENTS_ADD_COMMENT' => $_ARRAYLANG['TXT_NEWS_COMMENTS_ADD_COMMENT'],
							   	'TXT_NEWS_COMMENTS_AUTHOR' 		=> $_ARRAYLANG['TXT_NEWS_COMMENTS_AUTHOR'],
							   	'TXT_NEWS_COMMENTS_TITLE' 		=> $_ARRAYLANG['TXT_NEWS_COMMENTS_TITLE'],
							   	'TXT_NEWS_COMMENTS_MESSAGE' 	=> $_ARRAYLANG['TXT_NEWS_COMMENTS_MESSAGE'],
							   	'TXT_NEWS_COMMENTS_ADD' 		=> $_ARRAYLANG['TXT_NEWS_COMMENTS_ADD']
		                    ));
				        } else {
				        	// hide add comment form
				        	$this->_objTpl->hideBlock('addComment');
				        }
				        
			        } else {
			        	$this->_objTpl->hideBlock('showComments');
					}

                    $this->_objTpl->setVariable(array(
                       'NEWS_DATE'          => date(ASCMS_DATE_FORMAT,$objResult->fields['date']),
                       'NEWS_TITLE'         => $newstitle,
                       'NEWS_TEXT'          => stripslashes($objResult->fields['text']),
                       'NEWS_TEASER_TEXT'   => nl2br($objResult->fields['teasertext']),
                       'NEWS_LASTUPDATE'    => $newsLastUpdate,
                       'NEWS_SOURCE'        => $newsSource,
                       'NEWS_URL'           => $newsUrl,
                       'NEWS_AUTHOR'        => $author,
					   'NEWS_CATEGORY_NAME' => htmlentities($objResult->fields['catname'], ENT_QUOTES, CONTREXX_CHARSET)
                    ));

                    if (!empty($objResult->fields['newsimage'])) {
                        $this->_objTpl->setVariable(array(
                            'NEWS_IMAGE'         => '<img src="'.$objResult->fields['newsimage'].'" alt="'.$newstitle.'" />',
                            'NEWS_IMAGE_SRC'     => $objResult->fields['newsimage'],
                            'NEWS_IMAGE_ALT'     => $newstitle
                        ));

                        if ($this->_objTpl->blockExists('news_image')) {
                            $this->_objTpl->parse('news_image');
                        }
                    } else {
                        if ($this->_objTpl->blockExists('news_image')) {
                            $this->_objTpl->hideBlock('news_image');
                        }
                    }

                    $objResult->MoveNext();
                }
            }
        } else {
            CSRF::header("Location: index.php?section=news");
            exit;
        }

        $this->newsTitle = strip_tags($newstitle);
        return $this->_objTpl->get();
    }

    /**
    * Gets the list with the headlines
    *
    * @global    array
    * @global    ADONewConnection
    * @global    array
    * @return    string    parsed content
    */
    function getHeadlines() {
        global $_CONFIG, $objDatabase, $_ARRAYLANG;

        $selected   = '';
        $newsfilter = '';
        $paging     = '';
        $pos        = 0;
        $i          = 0;

        if (isset($_GET['pos'])) {
            $pos = intval($_GET['pos']);
        }

        $this->_objTpl->setTemplate($this->pageContent);

        if (!empty($_REQUEST['category']) || (($_REQUEST['category'] = intval($_REQUEST['cmd'])) > 0)) {
            $newsfilter = ' AND ';
            $boolFirst = true;

            $arrCategories = explode(',',$_REQUEST['category']);

            if (count($arrCategories) == 1) {
                $selected = $arrCategories[0];
        }

            foreach ($arrCategories as $intCategoryId) {
                if (!$boolFirst) {
                    $newsfilter .= 'OR ';
                }

                $newsfilter .= 'n.catid='.intval($intCategoryId).' ';
                $boolFirst = false;
            }
        }

        $catMenu    =  '<select onchange="this.form.submit()" name="category">'."\n";
        $catMenu    .= '<option value="" selected="selected">'.$_ARRAYLANG['TXT_CATEGORY'].'</option>'."\n";
        $catMenu    .= $this->getCategoryMenu($this->langId, $selected)."\n";
        $catMenu    .= '</select>'."\n";

        $this->_objTpl->setVariable(array(
            'NEWS_CAT_DROPDOWNMENU' => $catMenu,
            'TXT_PERFORM'           => $_ARRAYLANG['TXT_PERFORM'],
            'TXT_CATEGORY'          => $_ARRAYLANG['TXT_CATEGORY'],
            'TXT_DATE'              => $_ARRAYLANG['TXT_DATE'],
            'TXT_TITLE'             => $_ARRAYLANG['TXT_TITLE'],
            'TXT_NEWS_MESSAGE'      => $_ARRAYLANG['TXT_NEWS_MESSAGE']
        ));

        $query = '  SELECT      n.id                AS newsid,
                                n.userid            AS newsuid,
                                n.date              AS newsdate,
                                nl.title            AS newstitle,
                                nl.text             AS newscontent,
                                n.teaser_image_path AS newsimage,
                                n.teaser_image_thumbnail_path AS newsimagethumbnail,
                                n.redirect          AS newsredirect,
                                nl.teaser_text      AS teasertext,
                                ncl.name             AS name
                    FROM        '.DBPREFIX.'module_news AS n
                        LEFT JOIN  '.DBPREFIX.'module_news_locale AS nl ON nl.news_id = n.id
                        INNER JOIN  '.DBPREFIX.'module_news_categories AS nc ON n.catid=nc.catid
                        INNER JOIN  '.DBPREFIX.'module_news_categories_locale AS ncl ON ncl.category_id=nc.catid
                    WHERE       status = 1
                                AND nl.lang_id='.$this->langId.'
                                AND ncl.lang_id='.$this->langId.'
                                AND (n.startdate<=\''.date('Y-m-d H:i:s').'\' OR n.startdate="0000-00-00 00:00:00")
                                AND (n.enddate>=\''.date('Y-m-d H:i:s').'\' OR n.enddate="0000-00-00 00:00:00")
                                '.$newsfilter
                               .($this->arrSettings['news_message_protection'] == '1' && !Permission::hasAllAccess() ? (
                                    ($objFWUser = FWUser::getFWUserObject()) && $objFWUser->objUser->login() ?
                                        " AND (frontend_access_id IN (".implode(',', array_merge(array(0), $objFWUser->objUser->getDynamicPermissionIds())).") OR userid = ".$objFWUser->objUser->getId().") "
                                        :   " AND frontend_access_id=0 ")
                                    :   '')
                    .'ORDER BY    newsdate DESC';

        /***start paging ****/
        $objResult = $objDatabase->Execute($query);
        $count = $objResult->RecordCount();
        if (isset($_REQUEST['category'])) {
            $category = "&amp;category=".$selected;
        }

        if (isset($_REQUEST['cmd'])) {
            if ($_REQUEST['cmd'] == $selected) {
                $category = "&amp;cmd=".$_REQUEST['cmd'];
            } else {
                $category .= "&amp;cmd=".$_REQUEST['cmd'];
            }
        }


        if ($count>intval($_CONFIG['corePagingLimit'])) {
            $paging = getPaging($count, $pos, "&amp;section=news".$category, $_ARRAYLANG['TXT_NEWS_MESSAGES'], true);
        }
        $this->_objTpl->setVariable("NEWS_PAGING", $paging);
        $objResult = $objDatabase->SelectLimit($query, $_CONFIG['corePagingLimit'], $pos);
        /*** end paging ***/

        if ($count>=1) {
            while (!$objResult->EOF) {
                ($i % 2) ? $class  = 'row2' : $class  = 'row1';

                if ($objResult->fields['newsuid'] && ($objFWUser = FWUser::getFWUserObject()) && ($objUser = $objFWUser->objUser->getUser($objResult->fields['newsuid']))) {
                    $firstname = $objUser->getProfileAttribute('firstname');
                    $lastname = $objUser->getProfileAttribute('lastname');
                    if (!empty($firstname) && !empty($lastname)) {
                        $author = htmlentities($firstname.' '.$lastname, ENT_QUOTES, CONTREXX_CHARSET);
                    } else {
                        $author = htmlentities($objUser->getUsername(), ENT_QUOTES, CONTREXX_CHARSET);
                    }
                } else {
                    $author = $_ARRAYLANG['TXT_ANONYMOUS'];
                }

                $newstitle = htmlspecialchars(stripslashes($objResult->fields['newstitle']), ENT_QUOTES, CONTREXX_CHARSET);
                if (!empty($objResult->fields['newsimagethumbnail'])) {
                    $image = '<img src="'.$objResult->fields['newsimagethumbnail'].'" alt="'.$newstitle.'" />';
                    $imageSrc = $objResult->fields['newsimagethumbnail'];
                } elseif (!empty($objResult->fields['newsimage']) && file_exists(ASCMS_PATH.ImageManager::getThumbnailFilename($objResult->fields['newsimage']))) {
                    $image = '<img src="'.ImageManager::getThumbnailFilename($objResult->fields['newsimage']).'" alt="'.$newstitle.'" />';
                    $imageSrc = ImageManager::getThumbnailFilename($objResult->fields['newsimage']);
                } elseif (!empty($objResult->fields['newsimage'])) {
                    $image = '<img src="'.$objResult->fields['newsimage'].'" alt="'.$newstitle.'" />';
                    $imageSrc = $objResult->fields['newsimage'];
                } else {
                    $image = "";
                }

                $this->_objTpl->setVariable(array(
                           'NEWS_CSS'           => $class,
                           'NEWS_TEASER'        => $objResult->fields['teasertext'],
                           'NEWS_TITLE'         => $newstitle,
                           'NEWS_LONG_DATE'     => date(ASCMS_DATE_FORMAT,$objResult->fields['newsdate']),
                           'NEWS_DATE'          => date(ASCMS_DATE_SHORT_FORMAT, $objResult->fields['newsdate']),
                           'NEWS_LINK_TITLE'    => (empty($objResult->fields['newsredirect'])) ? '<a href="'.CONTREXX_SCRIPT_PATH.'?section=news&amp;cmd=details&amp;newsid='.$objResult->fields['newsid'].'" title="'.$newstitle.'">'.$newstitle.'</a>' : '<a href="'.$objResult->fields['newsredirect'].'" title="'.$newstitle.'">'.$newstitle.'</a>',
                           'NEWS_LINK'             => (empty($objResult->fields['newsredirect'])) ? (empty($objResult->fields['newscontent']) || $objResult->fields['newscontent'] == '<br type="_moz" />' ? '' :'<a href="'.CONTREXX_SCRIPT_PATH.'?section=news&amp;cmd=details&amp;newsid='.$objResult->fields['newsid'].'" title="'.$newstitle.'">['.$_ARRAYLANG['TXT_NEWS_MORE'].'...]</a>') : '<a href="'.$objResult->fields['newsredirect'].'" title="'.$newstitle.'">['.$_ARRAYLANG['TXT_NEWS_MORE'].'...]</a>',
                           'NEWS_CATEGORY'      => stripslashes($objResult->fields['name']),
                           'NEWS_AUTHOR'        => $author
                ));

                if (!empty($image)) {
                    $this->_objTpl->setVariable(array(
                        'NEWS_IMAGE'         => $image,
                        'NEWS_IMAGE_SRC'     => $imageSrc,
                        'NEWS_IMAGE_ALT'     => $newstitle,
                        'NEWS_IMAGE_LINK'    => empty($objResult->fields['newsredirect']) ? '<a href="'.CONTREXX_SCRIPT_PATH.'?section=news&amp;cmd=details&amp;newsid='.$objResult->fields['newsid'].'" title="'.$newstitle.'">'.$image.'</a>' : '<a href="'.$objResult->fields['newsredirect'].'" title="'.$newstitle.'">'.$image.'</a>'
                    ));

                    if ($this->_objTpl->blockExists('news_image')) {
                        $this->_objTpl->parse('news_image');
                    }
                } else {
                    if ($this->_objTpl->blockExists('news_image')) {
                        $this->_objTpl->hideBlock('news_image');
                    }
                }

                $this->_objTpl->parse('newsrow');
                $i++;
                $objResult->MoveNext();
            }
        } else {
            $this->_objTpl->setVariable(array(
                'NEWS_DATE'     => '',
                'NEWS_LINK'     => $_ARRAYLANG['TXT_NO_NEWS_FOUND'],
                'NEWS_CATEGORY' => '',
            ));
            $this->_objTpl->parse('newsrow');
        }
        return $this->_objTpl->get();
    }

    /**
    * Gets the global page title
    *
    * @param     string    (optional)$pageTitle
    */
    function getPageTitle($pageTitle="")
    {
        if (empty($this->newsTitle)) {
            $this->newsTitle = $pageTitle;
        }
    }

    function _notify_by_email($news_id) {
        global $objDatabase;
        $user_id  = intval($this->arrSettings['news_notify_user']);
        $group_id = intval($this->arrSettings['news_notify_group']);
        $users_in_group = array();

        if ($group_id > 0) {
            $objFWUser = FWUser::getFWUserObject();

            if ($objGroup = $objFWUser->objGroup->getGroup($group_id)) {
                $users_in_group = $objGroup->getAssociatedUserIds();
            }
        }

        if ($user_id > 0) {
            $users_in_group[] = $user_id;
        }

        // Now we have fetched all user IDs that
        // are to be notified. Now send those emails!
        foreach ($users_in_group as $user_id) {
            $this->_notify_user_by_email($user_id, $news_id);
        }
    }

    function _notify_user_by_email($user_id, $news_id) {
        global $_ARRAYLANG, $_CONFIG;
        // First, load recipient infos.
        try {
            $objFWUser = FWUser::getFWUserObject();
            $objUser = $objFWUser->objUser->getUser($user_id);
        }
        catch (Exception $e) {
        }

        if (!$objUser) {
            return false;
        }
        $user  = $objUser->getUsername();
        $first = $objUser->getProfileAttribute('firstname');
        $last  = $objUser->getProfileAttribute('lastname');
        $email = $objUser->getEmail();

        // Adress user with username if no first, lastname is defined.
        $name = ($first && $last) ? "$first $last" : $user;

        $msg  = $_ARRAYLANG['TXT_NOTIFY_ADDRESS'] . " $name\n\n";
        // Split the message text into lines
        $words = preg_split('/\s+/s', $_ARRAYLANG['TXT_NOTIFY_MESSAGE']);
        $line = '';
        for ($idx = 0; $idx < sizeof($words); $idx++) {
            if (strlen($line . ' ' . $words[$idx]) < 80) {
                // Line not full yet
                if ($line) $line .= ' ' . $words[$idx];
                else       $line =        $words[$idx];
            }
            else {
                // Line is full. add to message and empty.
                $msg .= "$line\n";
                $line = $words[$idx];
            }
        }
        $msg .= "$line\n";
        $msg .= "  http://".$_SERVER['SERVER_NAME'].  $serverPort.ASCMS_PATH_OFFSET . "/cadmin/index.php?cmd=news"
            . "&act=edit&newsId=$news_id&validate=true";
        $msg .= "\n\n";
        $msg .= $_CONFIG['coreAdminName'];

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

            $objMail->CharSet = CONTREXX_CHARSET;
            $objMail->From = $_CONFIG['coreAdminEmail'];
            $objMail->FromName = $_CONFIG['coreAdminName'];
            $objMail->Subject = $_ARRAYLANG['TXT_NOTIFY_SUBJECT'];
            $objMail->IsHTML(false);
            $objMail->Body = $msg;

            $objMail->AddAddress($email, $name);
            $objMail->Send();
        }
        return true;
    }

    /**
    * Get the submit page
    *
    * Get the submit, login or the noaccess page depending on the configuration
    *
    * @access private
    * @global array
    * @global ADONewConnection
    * @see HTML_Template_Sigma::setTemplate(), modulemanager::getModules(), Permission::checkAccess()
    * @return string content
    */
    function _submit()
    {
        global $_ARRAYLANG, $objDatabase;

        $this->_objTpl->setTemplate($this->pageContent);

        require_once ASCMS_CORE_PATH.'/modulemanager.class.php';
        $objModulManager = new modulemanager();
        $arrInstalledModules = $objModulManager->getModules();
        if (in_array(23, $arrInstalledModules)) {
            $communityModul = true;
        } else {
            $communityModul = false;
        }

        if (!$this->arrSettings['news_submit_news'] == '1' || (!$communityModul && $this->arrSettings['news_submit_only_community'] == '1')) {
            CSRF::header('Location: '.CONTREXX_SCRIPT_PATH.'?section=news');
            exit;
        } elseif ($this->arrSettings['news_submit_only_community'] == '1') {
            $objFWUser = FWUser::getFWUserObject();
            if ($objFWUser->objUser->login()) {
                if (!Permission::checkAccess(61, 'static')) {
                    CSRF::header('Location: '.CONTREXX_DIRECTORY_INDEX.'?section=login&cmd=noaccess');
                    exit;
                }
            } else {
                $link = base64_encode(CONTREXX_DIRECTORY_INDEX.'?'.$_SERVER['QUERY_STRING']);
                CSRF::header('Location: '.CONTREXX_DIRECTORY_INDEX.'?section=login&redirect='.$link);
                exit;
            }
        }

        $newsCat = "";
        $newsText = "";
        $newsTeaserText = '';
        $newsTitle = "";
        $newsRedirect = 'http://';
        $newsSource = "http://";
        $newsUrl1 = "http://";
        $newsUrl2 = "http://";
        $insertStatus = false;

        require_once ASCMS_LIBRARY_PATH . "/spamprotection/captcha.class.php";
        $captcha = new Captcha();

        $offset = $captcha->getOffset();
        $alt = $captcha->getAlt();
        $url = $captcha->getUrl();

        if (isset($_POST['submitNews'])) {
            $objValidator = new FWValidator();

            $_POST['newsTitle'] = contrexx_strip_tags(html_entity_decode($_POST['newsTitle']));
            $_POST['newsTeaserText'] = contrexx_stripslashes($_POST['newsTeaserText']);
            $_POST['newsRedirect'] = $objValidator->getUrl(contrexx_strip_tags(html_entity_decode($_POST['newsRedirect'])));
            $_POST['newsText'] = get_magic_quotes_gpc() ? stripslashes($_POST['newsText']) : $_POST['newsText'];
            $_POST['newsText'] = $this->filterBodyTag($_POST['newsText']);
            $_POST['newsSource'] = $objValidator->getUrl(contrexx_strip_tags(html_entity_decode($_POST['newsSource'])));
            $_POST['newsUrl1'] = $objValidator->getUrl(contrexx_strip_tags(html_entity_decode($_POST['newsUrl1'])));
            $_POST['newsUrl2'] = $objValidator->getUrl(contrexx_strip_tags(html_entity_decode($_POST['newsUrl2'])));
            $_POST['newsCat'] = intval($_POST['newsCat']);

            if (!$captcha->compare($_POST['captcha'], $_POST['offset'])) {
                $this->_submitMessage = $_ARRAYLANG['TXT_CAPTCHA_ERROR'] . "<br />";
            }

            if (!empty($_POST['newsTitle']) && $captcha->compare($_POST['captcha'], $_POST['offset']) && (!empty($_POST['newsText']) || (!empty($_POST['newsRedirect']) && $_POST['newsRedirect'] != 'http://'))) {
                    $insertStatus = $this->_insert();
                    if (!$insertStatus) {
                        $newsTitle = $_POST['newsTitle'];
                        $newsTeaserText = $_POST['newsTeaserText'];
                        $newsRedirect = $_POST['newsRedirect'];
                        $newsSource = $_POST['newsSource'];
                        $newsUrl1 = $_POST['newsUrl1'];
                        $newsUrl2 = $_POST['newsUrl2'];
                        $newsCat = $_POST['newsCat'];
                        $newsText = $_POST['newsText'];
                    }
                    else {
                        $this->_notify_by_email($insertStatus);
                    }
                } else {
                    $newsTitle = $_POST['newsTitle'];
                    $newsTeaserText = $_POST['newsTeaserText'];
                    $newsRedirect = $_POST['newsRedirect'];
                    $newsSource = $_POST['newsSource'];
                    $newsUrl1 = $_POST['newsUrl1'];
                    $newsUrl2 = $_POST['newsUrl2'];
                    $newsCat = $_POST['newsCat'];
                    $newsText = $_POST['newsText'];

                    if ( (empty($_POST['newsTitle']) || (empty($_POST['newsText']) || $_POST['newsText'] == '&nbsp;' || $_POST['newsText'] == '<br />' )) && 
                         (empty($_POST['newsRedirect']) || $_POST['newsRedirect'] == 'http://') ) {                        
                             
                        $this->_submitMessage .= $_ARRAYLANG['TXT_SET_NEWS_TITLE_AND_TEXT_OR_REDIRECT']."<br /><br />";
                    }
                }
        }

        if ($insertStatus) {
            if ($this->_objTpl->blockExists('news_submit_form')) {
                $this->_objTpl->hideBlock('news_submit_form');
            }
            if ($this->_objTpl->blockExists('news_submitted')) {
                $this->_objTpl->touchBlock('news_submitted');
            }
        } else {
            require ASCMS_CORE_PATH.'/wysiwyg.class.php';
            global $wysiwygEditor, $FCKeditorBasePath;
            $wysiwygEditor = "FCKeditor";
            $FCKeditorBasePath = "/editor/fckeditor/";



            $this->_objTpl->setVariable(array(
                'TXT_NEWS_MESSAGE'          => $_ARRAYLANG['TXT_NEWS_MESSAGE'],
                'TXT_TITLE'                 => $_ARRAYLANG['TXT_TITLE'],
                'TXT_CATEGORY'              => $_ARRAYLANG['TXT_CATEGORY'],
                'TXT_HYPERLINKS'            => $_ARRAYLANG['TXT_HYPERLINKS'],
                'TXT_EXTERNAL_SOURCE'       => $_ARRAYLANG['TXT_EXTERNAL_SOURCE'],
                'TXT_LINK'                  => $_ARRAYLANG['TXT_LINK'],
                'TXT_NEWS_NEWS_CONTENT'     => $_ARRAYLANG['TXT_NEWS_NEWS_CONTENT'],
                'TXT_NEWS_TEASER_TEXT'      => $_ARRAYLANG['TXT_NEWS_TEASER_TEXT'],
                'TXT_SUBMIT_NEWS'           => $_ARRAYLANG['TXT_SUBMIT_NEWS'],
                'TXT_NEWS_REDIRECT'         => $_ARRAYLANG['TXT_NEWS_REDIRECT'],
                'TXT_NEWS_NEWS_URL'         => $_ARRAYLANG['TXT_NEWS_NEWS_URL'],
                "TXT_CAPTCHA"               => $_ARRAYLANG['TXT_CAPTCHA'],
                'NEWS_TEXT'                 => get_wysiwyg_editor('newsText', $newsText, 'news'),
                'NEWS_CAT_MENU'             => $this->getCategoryMenu($this->langId, $newsCat),
                'NEWS_TITLE'                => $newsTitle,
                'NEWS_SOURCE'               => $newsSource,
                'NEWS_URL1'                 => $newsUrl1,
                'NEWS_URL2'                 => $newsUrl2,
                'NEWS_TEASER_TEXT'          => $newsTeaserText,
                'NEWS_REDIRECT'             => $newsRedirect,
                "CAPTCHA_OFFSET"            => $offset,
                "IMAGE_URL"                 => $url,
                "IMAGE_ALT"                 => $alt
            ));

            if ($this->_objTpl->blockExists('news_category_menu')) {
                $objResult = $objDatabase->Execute('SELECT category_id as catid, name FROM '.DBPREFIX.'module_news_categories_locale WHERE lang_id='.$this->langId.' ORDER BY category_id asc');

                if ($objResult !== false) {
                    while (!$objResult->EOF) {
                        $this->_objTpl->setVariable(array(
                            'NEWS_CATEGORY_ID'      => $objResult->fields['catid'],
                            'NEWS_CATEGORY_TITLE'   => htmlentities($objResult->fields['name'], ENT_QUOTES, CONTREXX_CHARSET)
                        ));
                        $this->_objTpl->parse('news_category_menu');
                        $objResult->MoveNext();
                    }
                }
            }

            if ($this->_objTpl->blockExists('news_submit_form')) {
                $this->_objTpl->parse('news_submit_form');
            }
        }

        $this->_objTpl->setVariable('NEWS_STATUS_MESSAGE', $this->_submitMessage);
        return $this->_objTpl->get();
    }



    /**
    * Insert a new news message
    *
    * @access private
    * @global ADONewConnection
    * @global array
    * @return boolean true on success - false on failure
    */
    function _insert()
    {
        global $objDatabase, $_ARRAYLANG;

        $objFWUser = FWUser::getFWUserObject();

        $date         = time();
        $newstitle    = $_POST['newsTitle'];
        $newstext     = $_POST['newsText'];
        $newsRedirect = $_POST['newsRedirect'];
        if ($newsRedirect == 'http://') {
            $newsRedirect = '';
        }
        $newsTeaserText = $_POST['newsTeaserText'];
        $newssource     = $_POST['newsSource'];
        $newsurl1       = $_POST['newsUrl1'];
        $newsurl2       = $_POST['newsUrl2'];
        $newscat        = $_POST['newsCat'];
        $userid         = $objFWUser->objUser->getId();

        $insert_fields = array(
            'date',     /* 'title',     'text', */    'redirect',
            'source',     'url1',      'url2',        'catid',
            /*'lang',       'startdate', 'enddate', */'status',
            'validated',  'userid',    /*'teaser_text',*/ 'changelog'

        );

        $enable = $this->arrSettings['news_activate_submitted_news'] == '1' ? "1" : "0";
        $insert_values = array(
            $date,       /*   $newstitle, $newstext, */   $newsRedirect,
            $newssource,    $newsurl1,  $newsurl2,        $newscat,
            /*$this->langId, '',         '',      */       $enable,
            $enable,        $userid,    /*$newsTeaserText,*/  $date
        );
        
        // title, text and teasertext stored in locale table


        $into   = "`" . join("`, `", $insert_fields) . "`";
        $values = "'" . join("', '", array_map('contrexx_addslashes', $insert_values)) . "'";

        $objResult = $objDatabase->Execute("
            INSERT INTO ".DBPREFIX."module_news ( $into)
            VALUES ( $values )"
        );

        if ($objResult !== false) {
            $this->_submitMessage = $_ARRAYLANG['TXT_NEWS_SUCCESSFULLY_SUBMITED']."<br /><br />";
            $ins_id = $objDatabase->Insert_ID();
            $this->submitLocales($ins_id, $newstitle, $newstext, $newsTeaserText);
            return $ins_id;
        } else {
            $this->_submitMessage = $_ARRAYLANG['TXT_NEWS_SUBMIT_ERROR']."<br /><br />";
            return false;
        }
    }

    /**
    * Show feed page
    *
    * @access private
    * @global array
    * @global integer
    * @return string Template output
    */
    function _showFeed()
    {
        global $_ARRAYLANG, $_LANGID;

        $this->_objTpl->setTemplate($this->pageContent);
        $serverPort = $_SERVER['SERVER_PORT'] == 80 ? "" : ":".intval($_SERVER['SERVER_PORT']);
        $rssFeedUrl = "http://".$_SERVER['SERVER_NAME'].$serverPort.ASCMS_PATH_OFFSET."/feed/news_headlines_".FWLanguage::getLanguageParameter($_LANGID, 'lang').".xml";
        $jsFeedUrl = "http://".$_SERVER['SERVER_NAME'].$serverPort.ASCMS_PATH_OFFSET."/feed/news_".FWLanguage::getLanguageParameter($_LANGID, 'lang').".js";
        $hostname = addslashes(htmlspecialchars($_SERVER['SERVER_NAME'], ENT_QUOTES, CONTREXX_CHARSET));

        $rss2jsCode = <<<RSS2JSCODE
&lt;script language="JavaScript" type="text/javascript"&gt;
&lt;!--
// {$_ARRAYLANG['TXT_NEWS_OPTIONAL_VARS']}
var rssFeedFontColor = "#000000"; // {$_ARRAYLANG['TXT_NEWS_FONT_COLOR']}
var rssFeedFontSize = 8; // {$_ARRAYLANG['TXT_NEWS_FONT_SIZE']}
var rssFeedFont = "Arial, Verdana"; // {$_ARRAYLANG['TXT_NEWS_FONT']}
var rssFeedLimit = 10; // {$_ARRAYLANG['TXT_NEWS_DISPLAY_LIMIT']}
var rssFeedShowDate = true; // {$_ARRAYLANG['TXT_NEWS_SHOW_NEWS_DATE']}
var rssFeedTarget = "_blank"; // _blank | _parent | _self | _top
var rssFeedContainer = "news_rss_feeds";
// --&gt;
&lt;/script&gt;
&lt;script type="text/javascript" language="JavaScript" src="$jsFeedUrl"&gt;&lt;/script&gt;
&lt;noscript&gt;
&lt;a href="$rssFeedUrl"&gt;$hostname - {$_ARRAYLANG['TXT_NEWS_SHOW_NEWS']}&lt;/a&gt;
&lt;/noscript&gt;
&lt;div id="news_rss_feeds"&gt;&nbsp;&lt;/div&gt;
RSS2JSCODE;

        $this->_objTpl->setVariable(array(
            'NEWS_HOSTNAME'     => $hostname,
            'NEWS_RSS2JS_CODE'  => $rss2jsCode,
            'NEWS_RSS2JS_URL'   => $jsFeedUrl,
            'NEWS_RSS_FEED_URL' => $rssFeedUrl
        ));
        return $this->_objTpl->get();
    }
    
    /**
     * Validate comment before saving
     * (check all fields to be not null & captcha)
     * @global    ADONewConnection
     * 
     * @return boolean
     */
    function _validateComment()
    {
    	global $_ARRAYLANG;
    	if (!isset($_REQUEST['cAuthorID']) && (!isset($_REQUEST['cAuthor']) || $_REQUEST['cAuthor'] == "")) {
    		$this->_commentErrorMessage = $_ARRAYLANG['TXT_NEWS_COMMENTS_NOT_VALID_AUTHOR'];
    	} elseif (!isset($_REQUEST['cTitle']) || $_REQUEST['cTitle'] == "") {
    		$this->_commentErrorMessage = $_ARRAYLANG['TXT_NEWS_COMMENTS_NOT_VALID_TITLE'];
    	} elseif (!isset($_REQUEST['cMessage']) || $_REQUEST['cMessage'] == "") {
    		$this->_commentErrorMessage = $_ARRAYLANG['TXT_NEWS_COMMENTS_NOT_VALID_MESSAGE'];
    	} elseif (isset($_REQUEST['cOffset'])) {
    		require_once ASCMS_LIBRARY_PATH . "/spamprotection/captcha.class.php";
			$captcha = new Captcha();

			$offset = $captcha->getOffset();
			if (!$captcha->compare($_POST['cCaptcha'], $_POST['cOffset'])) {
            	$this->_commentErrorMessage = $_ARRAYLANG['TXT_NEWS_COMMENTS_NOT_VALID_CAPTCHA'];
            }
    	}
    	
    	if ("" == $this->_commentErrorMessage) {
    		return true;
    	} else {
    		$this->_commentTitle = $_POST['cTitle'];
    		$this->_commentMessage = $_POST['cMessage'];
    		$this->_commentAuthor = $_POST['cAuthor'];
    		return false;
    	}
    }
    
    /**
     * Insert comment into database
     * @global    ADONewConnection
     * @global    array langData
     *
     */
    function _addComment()
    {
    	global $objDatabase, $_ARRAYLANG, $_CONFIG;

        $date = time();
        $comment_title = contrexx_addslashes(contrexx_strip_tags($_POST['cTitle']));
        $comment_message = contrexx_addslashes(contrexx_strip_tags($_POST['cMessage']));
        $comment_author = contrexx_addslashes(contrexx_strip_tags($_POST['cAuthor']));
        $news_id = intval($_REQUEST['newsid']);
        $user_id = (isset($_POST['cAuthorID'])) ? intval($_POST['cAuthorID']) : 0;
        
        $intIsActive = intval($this->arrSettings['news_comments_autoactivate']);
        
        $objResult = $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_news_comments (
            title,
            text,
            newsid,
            date,
            poster_name,
            userid,
            ip_address,
            is_active
            ) VALUES (
            '$comment_title',
            '$comment_message',
            $news_id,
            $date,
            '$comment_author',
            $user_id,
            '" . $_SERVER['REMOTE_ADDR'] . "',
            '$intIsActive')"
        );

        if ($objResult === false) {
            $this->_commentErrorMessage = $_ARRAYLANG['TXT_NEWS_COMMENTS_SAVING_ERROR'];
            return false;
        }
        
        //Set a cookie with the current timestamp. Avoids flooding.
        setcookie('NewsCommentLast', $date, 0, ASCMS_PATH_OFFSET.'/');
        
        $intIsNotification = intval($this->arrSettings['news_comments_notification']);
		if ($intIsNotification) {
            //Send notification to administrator
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

                if ($user_id > 0) {
                    $objFWUser = FWUser::getFWUserObject();
                    $strName = htmlentities($objFWUser->objUser->getUsername(), ENT_QUOTES, CONTREXX_CHARSET);
                }

                $strMailSubject = str_replace('[SUBJECT]', $comment_title, $_ARRAYLANG['TXT_NEWS_FRONTEND_DETAILS_COMMENT_INSERT_MAIL_SUBJECT']);
                $strMailBody    = str_replace('[USERNAME]', $strName, $_ARRAYLANG['TXT_NEWS_FRONTEND_DETAILS_COMMENT_INSERT_MAIL_BODY']);
                $strMailBody    = str_replace('[SUBJECT]', $comment_title, $strMailBody);
                $strMailBody    = str_replace('[COMMENT]', $comment_message, $strMailBody);

                $objMail->CharSet = CONTREXX_CHARSET;
                $objMail->From = $_CONFIG['coreAdminEmail'];
                $objMail->FromName = $_CONFIG['coreGlobalPageTitle'];
                $objMail->AddAddress($_CONFIG['coreAdminEmail']);
                $objMail->Subject   = $strMailSubject;
                $objMail->IsHTML(false);
                $objMail->Body      = $strMailBody;
                $objMail->Send();
            }
        }
    }
    
    function addComment()
    {
    	global $_ARRAYLANG;
    	//Check for activated function
        if (!$this->arrSettings['news_comments_activated']) {
            $this->_commentErrorMessage = $_ARRAYLANG['TXT_NEWS_FRONTEND_DETAILS_COMMENT_INSERT_ERROR_ACTIVATED'];
            return;
        }
        // just comment
        if ($this->hasUserJustCommented()) {
            $this->_commentErrorMessage = str_replace('[SECONDS]', intval($this->arrSettings['news_comments_timeout']), $_ARRAYLANG['TXT_NEWS_FRONTEND_DETAILS_COMMENT_INSERT_ERROR_TIMEOUT']);
            return;
        }
        
    	if($this->_validateComment()) {        		
    		$this->_addComment();
    	}
    }
    
    /**
     * Check if the current user has already written a comment within the definied timeout-time (settings-value).
     *
     * @return  boolean     true, if the user hast just written a comment before.
     */
    function hasUserJustCommented()
	{
        global $objDatabase;

        //Check cookie first
        if (isset($_COOKIE['NewsCommentLast'])) {
            $intLastCommentTime = intval($_COOKIE['NewsCommentLast']);

            if (time() < $intLastCommentTime + intval($this->arrSettings['news_comments_timeout'])) {
                //The current system-time is smaller than the time in the cookie plus timeout-time, so the user just wrote a comment
                return true;
            }
        }

        //Now check database (make sure the user didn't delete the cookie
        $objCommentResult = $objDatabase->Execute(' SELECT  id
                                                    FROM    '.DBPREFIX.'module_news_comments
                                                    WHERE   ip_address="'.$_SERVER['REMOTE_ADDR'].'" AND
                                                            date > '.(time() - intval($this->arrSettings['news_comments_timeout'])).'
                                                    LIMIT   1
                                                ');

        if ($objCommentResult->RecordCount() == 1) {
            return true;
        }

        //Nothing found, i guess the user didn't comment within within the timeout-period.
        return false;
    }
    
    function _getArchive() 
    {
        global $_CONFIG, $objDatabase, $_ARRAYLANG;

        $dateFilterName = 'date';
        $monthColumn = 3;
        
        $objFWUser = FWUser::getFWUserObject();
        
        $this->_objTpl->setTemplate($this->pageContent);
        
        // month filter
        // archive list
        $monthCountQuery = "SELECT n.id             AS id,
                                   n.date           AS date, 
                                   n.changelog      AS changelog,
                                   n.redirect       AS newsredirect,
                                   nl.title         AS newstitle
                            FROM   ".DBPREFIX."module_news AS n
                                LEFT JOIN  ".DBPREFIX."module_news_locale AS nl ON nl.news_id = n.id
                            WHERE  n.validated='1'
                                   AND nl.lang_id=".$this->langId."
                                   AND n.status = 1
                                   " .($this->arrSettings['news_message_protection'] == '1' && !Permission::hasAllAccess() ? (
                                    ($objFWUser = FWUser::getFWUserObject()) && $objFWUser->objUser->login() ?
                                        " AND (frontend_access_id IN (".implode(',', array_merge(array(0), $objFWUser->objUser->getDynamicPermissionIds())).") OR userid = ".$objFWUser->objUser->getId().") "
                                        :   " AND frontend_access_id=0 ")
                                    :   '')
                            ."ORDER BY date ASC";
                                                        
        $objResult = $objDatabase->Execute($monthCountQuery);
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $filterDate = $objResult->fields[$dateFilterName];
                $newsYear = date("Y", $filterDate);
                $newsMonth = date("m", $filterDate);
                
                if(!isset($monthlyStats[$newsYear . "_" . $newsMonth])) {
                    $monthlyStats[$newsYear . "_" . $newsMonth] = array();
                    $monthlyStats[$newsYear . "_" . $newsMonth]["name"] = $_ARRAYLANG['TXT_NEWS_MONTH_' . date("n", $filterDate)] . " " . $newsYear;
                }
                $monthlyStats[$newsYear . "_" . $newsMonth]["news"][] = $objResult->fields;
                $monthlyStats[$newsYear . "_" . $newsMonth]["archive"]++;
                $objResult->MoveNext();
            }
        }
        
        if(count($monthlyStats) == 0) {
            $this->_objTpl->setVariable(array(
                'TXT_NEWS_NOT_FOUND' => $_ARRAYLANG['TXT_NEWS_NOT_FOUND'],
            ));
            $this->_objTpl->parse("news_not_found");
            $this->_objTpl->hideBlock("month_news_list");
        } else {
            
            $this->_objTpl->hideBlock("news_not_found");
            // create columns
            $columnCount = 0;
            $currentColumn = 1;
            $totalCount = count($monthlyStats);
            $columnSize = ceil($totalCount / $monthColumn);
            
            $this->_objTpl->setCurrentBlock('month_navigation_column');
            foreach ($monthlyStats as $key => $value) {
                $columnCount++;
                if($columnCount > $currentColumn * $columnSize) {
                    $currentColumn++;
                    $this->_objTpl->parseCurrentBlock();
                    $this->_objTpl->setCurrentBlock('month_navigation_column');
                }
                
                $this->_objTpl->setVariable(array(
                    'NEWS_MONTH_NAME' => $value['name'],
                    'NEWS_MONTH_ARCHIVE' => count($value['news']),
                    'NEWS_MONTH_KEY' => $key,
                ));
                $this->_objTpl->parse("month_navigation_item");
                
                foreach ($value['news'] as $news) {
                    $newstitle = htmlspecialchars(stripslashes($news['newstitle']), ENT_QUOTES, CONTREXX_CHARSET);
                    $this->_objTpl->setVariable(array(
                        'NEWS_LINK_TITLE'    => (empty($news['newsredirect'])) ? '<a href="'.CONTREXX_SCRIPT_PATH.'?section=news&amp;cmd=details&amp;newsid='.$news['id'].'" title="'.$newstitle.'">'.$newstitle.'</a>' : '<a href="'.$news['newsredirect'].'" title="'.$newstitle.'">'.$newstitle.'</a>',
                    ));
                    $this->_objTpl->parse("month_news_list_item");
                }
                
                $this->_objTpl->setVariable(array(
                    'NEWS_LIST_MONTH_NAME' => $value['name'],
                    'NEWS_MONTH_KEY' => $key,
                ));
                
                $this->_objTpl->parse('month_news_list');
            }
            $this->_objTpl->parseCurrentBlock();
            
        }
        
        return $this->_objTpl->get();
    }
}
?>
