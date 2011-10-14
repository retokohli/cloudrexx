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
 * @ignore
 */
require_once ASCMS_CORE_MODULE_PATH.'/news/lib/newsLib.class.php';
/**
 * @ignore
 */
require_once ASCMS_FRAMEWORK_PATH.'/Image.class.php';
/**
 * @ignore
 */
require_once ASCMS_CORE_MODULE_PATH.'/access/lib/AccessLib.class.php';

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
    public $newsTitle;
    public $arrSettings = array();
    public $_objTpl;
    public $_submitMessage;

    /**
     * Holds the teaser text when displaying details.
     * Accessed via news::getTeaser().
     * @var String $_teaser
     */
    private $_teaser = null;

    /**
     * Initializes the news module by loading the configuration options
     * and initializing the template object with $pageContent.
     * 
     * @param  string  News content page
     */
    public function __construct($pageContent)
    {
        $this->getSettings();

        $this->_objTpl = new HTML_Template_Sigma();
        CSRF::add_placeholder($this->_objTpl);
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);
        $this->_objTpl->setTemplate($pageContent);
    }

    /**
    * Get page
    *
    * @return string content
    */
    public function getNewsPage()
    {
        if (!isset($_REQUEST['cmd'])) {
            $_REQUEST['cmd'] = '';
        }

        switch ($_REQUEST['cmd']) {
        case 'details':
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
        case 'topnews':
             return $this->getTopNews();
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
    private function getDetails()
    {
        global $_CONFIG, $objDatabase, $_ARRAYLANG;

        $paging     = '';
        $pos        = 0;
        $i          = 0;

        if (isset($_GET['pos'])) {
            $pos = intval($_GET['pos']);
        }

        $newsid = intval($_GET['newsid']);

        if (!$newsid) {
            CSRF::header('Location: index.php?section=news');
            exit;
        }

        $objResult = $objDatabase->SelectLimit('SELECT  news.id                 AS id,
                                                        news.userid             AS userid,
                                                        news.source             AS source,
                                                        news.changelog          AS changelog,
                                                        news.url1               AS url1,
                                                        news.url2               AS url2,
                                                        news.date               AS date,
                                                        news.publisher          AS publisher,
                                                        news.publisher_id       AS publisherid,
                                                        news.author             AS author,
                                                        news.author_id          AS authorid,
                                                        news.changelog          AS changelog,
                                                        news.teaser_image_path  AS newsimage,
                                                        news.typeid             AS typeid,
                                                        news.catid              AS catid,
                                                        news.allow_comments     AS commentactive,
                                                        locale.text             AS text,
                                                        locale.title            AS title,
                                                        locale.teaser_text,
                                                        cat.name                AS catname
                                                  FROM  '.DBPREFIX.'module_news AS news
                                            INNER JOIN  '.DBPREFIX.'module_news_locale AS locale ON news.id = locale.news_id
                                            INNER JOIN  '.DBPREFIX.'module_news_categories_locale AS cat ON cat.category_id = news.catid
                                                WHERE   news.status = 1 AND
                                                        news.id = '.$newsid.' AND
                                                        locale.lang_id ='.FRONTEND_LANG_ID.' AND
                                                        cat.lang_id ='.FRONTEND_LANG_ID.' AND
                                                        (news.startdate <= \''.date('Y-m-d H:i:s').'\' OR news.startdate="0000-00-00 00:00:00") AND
                                                        (news.enddate >= \''.date('Y-m-d H:i:s').'\' OR news.enddate="0000-00-00 00:00:00")'
                                                       .($this->arrSettings['news_message_protection'] == '1' && !Permission::hasAllAccess() ? (
                                                            ($objFWUser = FWUser::getFWUserObject()) && $objFWUser->objUser->login() ?
                                                                " AND (frontend_access_id IN (".implode(',', array_merge(array(0), $objFWUser->objUser->getDynamicPermissionIds())).") OR userid = ".$objFWUser->objUser->getId().") "
                                                                :   " AND frontend_access_id=0 ")
                                                            :   '')
                                                , 1);
                                                

        if (!$objResult || $objResult->EOF) {
            CSRF::header('Location: index.php?section=news');
            exit;
        }

        $lastUpdate     = $objResult->fields['changelog'];
        $sourceHref     = contrexx_raw2encodedUrl($objResult->fields['source']);
        $url1Href       = contrexx_raw2encodedUrl($objResult->fields['url1']);
        $url2Href       = contrexx_raw2encodedUrl($objResult->fields['url2']);
        $source         = contrexx_raw2xhtml($objResult->fields['source']);
        $url1           = contrexx_raw2xhtml($objResult->fields['url1']);
        $url2           = contrexx_raw2xhtml($objResult->fields['url2']);
        $newsUrl        = '';
        $newsSource     = '';
        $newsLastUpdate = '';

        if (!empty($url1)) {
          $strUrl1 = contrexx_raw2xhtml($objResult->fields['url1']);
            if (strlen($strUrl1) > 40) {
                $strUrl1 = substr($strUrl1,0,26).'...'.substr($strUrl1,(strrpos($strUrl1,'.')));
            }
            $newsUrl = $_ARRAYLANG['TXT_IMPORTANT_HYPERLINKS'].'<br /><a target="_blank" href="'.$url1Href.'" title="'.$url1.'">'.$strUrl1.'</a><br />';
        }
        if (!empty($url2)) {
          $strUrl2 = contrexx_raw2xhtml($objResult->fields['url2']);
            if (strlen($strUrl2) > 40) {
                $strUrl2 = substr($strUrl2,0,26).'...'.substr($strUrl2,(strrpos($strUrl2,'.')));
            }
            $newsUrl .= '<a target="_blank" href="'.$url2Href.'" title="'.$url2.'">'.$strUrl2.'</a><br />';
        }
        if (!empty($source)) {
          $strSource = contrexx_raw2xhtml($objResult->fields['source']);
            if (strlen($strSource) > 40) {
                $strSource = substr($strSource,0,26).'...'.substr($strSource,(strrpos($strSource,'.')));
            }
            $newsSource = $_ARRAYLANG['TXT_NEWS_SOURCE'].'<br /><a target="_blank" href="'.$sourceHref.'" title="'.$source.'">'.$strSource.'</a><br />';
        }
        if (!empty($lastUpdate)) {
            $newsLastUpdate = $_ARRAYLANG['TXT_LAST_UPDATE'].'<br />'.date(ASCMS_DATE_FORMAT,$objResult->fields['changelog']);
        }

        $newstitle = contrexx_raw2xhtml($objResult->fields['title']);
// TODO: check if this makes actually sence to first convert the title to XHTML and afterwards to strip any HTML tags. Check what happens with $this->newsTitle later
        $this->newsTitle = strip_tags($newstitle);
        $newsTeaser = nl2br($objResult->fields['teaser_text']);

        $this->_objTpl->setVariable(array(
           'NEWS_DATE'          => date(ASCMS_DATE_FORMAT,$objResult->fields['date']),
           'NEWS_TITLE'         => $newstitle,
           'NEWS_TEXT'          => $objResult->fields['text'],
           'NEWS_TEASER_TEXT'   => $newsTeaser,
           'NEWS_LASTUPDATE'    => $newsLastUpdate,
           'NEWS_SOURCE'        => $newsSource,
           'NEWS_URL'           => $newsUrl,
           'NEWS_CATEGORY_NAME' => contrexx_raw2xhtml($objResult->fields['catname']),
// TODO: create a new methode from which we can fetch the name of the 'type' (do not fetch it from within the same SQL query of which we collect any other data!)
           //'NEWS_TYPE_NAME' => ($this->arrSettings['news_use_types'] == 1 ? htmlentities($objResult->fields['typename'], ENT_QUOTES, CONTREXX_CHARSET) : '')
        ));

        // parse author
        $this->parseUserAccountData($objResult->fields['authorid'], $objResult->fields['author'], 'news_author');
        // parse publisher
        $this->parseUserAccountData($objResult->fields['publisherid'], $objResult->fields['publisher'], 'news_publisher');

        // show comments
        $newsComment = $objResult->fields['commentactive'];        
        $this->parseMessageCommentForm($newsid, $newstitle, $newsComment);
        $this->parseCommentsOfMessage($newsid);        

        // Show related_messages
        $this->parseRelatedMessagesOfMessage($newsid, 'category', $objResult->fields['catid']);
        $this->parseRelatedMessagesOfMessage($newsid, 'type', $objResult->fields['typeid']);
        $this->parseRelatedMessagesOfMessage($newsid, 'publisher', $objResult->fields['publisherid']);
        $this->parseRelatedMessagesOfMessage($newsid, 'author', $objResult->fields['authorid']);

        /*
         * save the teaser text.
         * purpose of this: @link news::getTeaser()
         */
        $this->_teaser = contrexx_raw2xhtml($newsTeaser);

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
        
        $this->countNewsMessageView($newsid);
        $objResult->MoveNext();

        return $this->_objTpl->get();
    }

    private function countNewsMessageView($newsMessageId)
    {
        global $objDatabase, $objCounter;

        /*
         * count stat if option "top news" is activated
         */
        if (!$this->arrSettings['news_use_top']) {
            return;
        }

        if ($objCounter->isSpider()) {
            return;
        }

        $objDatabase->Execute("DELETE FROM `".DBPREFIX."module_news_stats_view` WHERE `time` < DATE_SUB(NOW(), INTERVAL ".intval($this->arrSettings['news_top_days'])." DAY)");
        
        $uniqueUserId = $objCounter->getUniqueUserId();

        $query = "
            SELECT 1
            FROM `".DBPREFIX."module_news_stats_view`
            WHERE user_sid = '".$uniqueUserId."' 
              AND news_id  = ".$newsMessageId."
              AND time     > DATE_SUB(NOW(), INTERVAL 1 DAY)";
        $objResult = $objDatabase->SelectLimit($query);
        if (!$objResult || !$objResult->EOF) {
            return;
        }

        $query = "INSERT INTO ".DBPREFIX."module_news_stats_view 
                     SET user_sid = '$uniqueUserId',
                         news_id  = '$newsMessageId'";
        $objDatabase->Execute($query);
    }

    /**
     * Lists all active comments of the news message specified by $messageId
     *
     * @param   integer News message-ID 
     * @global  ADONewConnection
     */
    private function parseCommentsOfMessage($messageId)
    {
        global $objDatabase;

        // abort if template block is missing
        if (!$this->_objTpl->blockExists('news_comments')) {
            return;
        }

        // abort if commenting system is not active
        if (!$this->arrSettings['news_comments_activated']) {
            $this->_objTpl->hideBlock('news_comments');
            return;
        }

        $query = '  SELECT      `title`,
                                `date`,
                                `poster_name`,
                                `userid`,
                                `text`
                    FROM        `'.DBPREFIX.'module_news_comments`
                    WHERE       `newsid` = '.$messageId.' AND `is_active` = "1"
                    ORDER BY    `date` DESC';

        $objResult = $objDatabase->Execute($query);

        // no comments for this message found
        if (!$objResult || $objResult->EOF) {
            if ($this->_objTpl->blockExists('news_no_comment')) {
                $this->_objTpl->setVariable('TXT_NEWS_COMMENTS_NONE_EXISTING', $_ARRAYLANG['TXT_NEWS_COMMENTS_NONE_EXISTING']);
                $this->_objTpl->parse('news_no_comment');
            }

            $this->_objTpl->hideBlock('news_comment_list');
            $this->_objTpl->parse('news_comments');

            return;
        }

// TODO: Add AJAX-based paging
        /*$count = $objResult->RecordCount();
        if ($count > intval($_CONFIG['corePagingLimit'])) {
            $paging = getPaging($count, $pos, '&amp;section=news&amp;cmd=details&amp;newsid='.$messageId, $_ARRAYLANG['TXT_NEWS_COMMENTS'], true);
        }
        $this->_objTpl->setVariable('COMMENTS_PAGING', $paging);*/

        $i = 0;
        while (!$objResult->EOF) {
            $this->parseUserAccountData($objResult->fields['userid'], $objResult->fields['poster_name'], 'news_comments_poster');

            $this->_objTpl->setVariable(array(
               'NEWS_COMMENTS_CSS'          => 'row'.($i % 2 + 1),
               'NEWS_COMMENTS_TITLE'        => contrexx_raw2xhtml($objResult->fields['title']),
               'NEWS_COMMENTS_MESSAGE'         => nl2br(contrexx_raw2xhtml($objResult->fields['text'])),
               'NEWS_COMMENTS_DATE'         => date(ASCMS_DATE_FORMAT, $objResult->fields['date']),
            ));

            $this->_objTpl->parse('news_comment');
            $i++;
            $objResult->MoveNext();
        }

        $this->_objTpl->parse('news_comment_list');
        $this->_objTpl->hideBlock('news_no_comment');
    }

    /**
     * Validates the submitted comment data and writes it to the databse if valid.
     * Additionally, a notification is send out to the administration about the comment
     * by e-mail (only if the corresponding configuration option is set to do so). 
     *
     * @param   integer News message ID for which the comment shall be stored
     * @param   string  Title of the news message for which the comment shall be stored.
     *                  The title will be used in the notification e-mail
     *                  {@link news::storeMessageComment()}
     * @global    array
     * @global    array
     * @return  array   Returns an array of two elements. The first is either TRUE on success or FALSE on failure.
     *                  The second element contains an error message on failure.  
     */
    private function parseMessageCommentForm($newsMessageId, $newsMessageTitle, $newsCommentActive)
    {
        global $_CORELANG, $_ARRAYLANG;

        // abort if template block is missing
        if (!$this->_objTpl->blockExists('news_add_comment')) {
            return;
        }

        // abort if comment system is deactivated
        if (!$this->arrSettings['news_comments_activated']) {
            return;
        }
        
        // abort if comment deactivated for this news
        if ($newsCommentActive == 0) {
            return;
        }

        // abort if request is unauthorized
        if (   $this->arrSettings['news_comments_anonymous'] == '0'
            && !FWUser::getFWUserObject()->objUser->login()
        ) {
            $this->_objTpl->hideBlock('news_add_comment');
            return;
        }
        
        $name = '';
        $title = '';
        $message = '';
        $error = '';

        $arrData = $this->fetchSubmittedCommentData();
        if ($arrData) {
            $name    = $arrData['name'];
            $title   = $arrData['title'];
            $message = $arrData['message'];
            list($status, $error) = $this->storeMessageComment($newsMessageId, $newsMessageTitle, $name, $title, $message);

            // new comment added successfully
            if ($status) {
                $this->_objTpl->hideBlock('news_add_comment');
                return;
            }
        }

        // create submit from
        if (FWUser::getFWUserObject()->objUser->login()) {
            $this->_objTpl->hideBlock('news_add_comment_name');
            $this->_objTpl->hideBlock('news_add_comment_captcha');
        } else {
            // Anonymous guests must enter their name as well as validate a CAPTCHA

            $this->_objTpl->setVariable(array(
                'NEWS_COMMENT_NAME' => contrexx_raw2xhtml($name),
                'TXT_NEWS_NAME'     => $_ARRAYLANG['TXT_NEWS_NAME'],
            ));
            $this->_objTpl->parse('news_add_comment_name');

            // parse CAPTCHA
            include_once ASCMS_LIBRARY_PATH.'/spamprotection/captcha.class.php';
            $captcha = new Captcha();

            $this->_objTpl->setVariable(array(
                'TXT_CORE_CAPTCHA_DESCRIPTION'  => $_CORELANG['TXT_CORE_CAPTCHA_DESCRIPTION'],
                'NEWS_COMMENT_CAPTCHA_URL'      => $captcha->getUrl(),
                'NEWS_COMMENT_CAPTCHA_ALT'      => $captcha->getAlt(),
                //'NEWS_COMMENT_CAPTCHA_ERROR'    => $this->captchaError,
            ));
            $this->_objTpl->parse('news_add_comment_captcha');
        }

        $this->_objTpl->setVariable(array(
            'NEWS_ID'               => $newsMessageId,
            'NEWS_ADD_COMMENT_ERROR'=> $error,
            'NEWS_COMMENT_TITLE'    => contrexx_raw2xhtml($title),
            'NEWS_COMMENT_MESSAGE'  => contrexx_raw2xhtml($message),
            'TXT_NEWS_ADD_COMMENT'  => $_ARRAYLANG['TXT_NEWS_ADD_COMMENT'],
            'TXT_NEWS_TITLE'        => $_ARRAYLANG['TXT_NEWS_TITLE'],
            'TXT_NEWS_COMMENT'      => $_ARRAYLANG['TXT_NEWS_COMMENT'],
            'TXT_NEWS_ADD'          => $_ARRAYLANG['TXT_NEWS_ADD'],
        ));

        $this->_objTpl->parse('news_add_comment');
    }


    /**
     * Parses a list of news messages that are related to a specific news message
     * (specified by $messageId) by the same relation object. The relation object
     * is specified by its kind ($relatedByKind) and its ID ($relatedKindId).
     * The relation kind can be one of the following:
     * - category
     * - type
     * - publisher
     * - author
     *
     * @param   integer News message-ID
     * @param   string  Relation kind
     * @param   integer Relation-ID
     * @global  ADONewConnection
     *
     */
    private function parseRelatedMessagesOfMessage($messageId, $relatedByKind, $relatedKindId)
    {
        global $objDatabase;

        static $arrRelatedKinds = array('category', 'type', 'publisher', 'author');

        // abort if no message ID has been supplied
        if (!$messageId) {
            return;
        }

        // abort if relation is unknown
        if (!in_array($relatedByKind, $arrRelatedKinds)) {
            return;
        }

        $relationTemplateBlock = "news_{$relatedByKind}_related_block";
        $imageTemplateBlock = "news_{$relatedByKind}_related_message_image";
        $messageTemplateBlock = "news_{$relatedByKind}_related_message";
        $placeholderPrefix = strtoupper($relatedByKind);
        $i = 0;

        // abort if template block of related messages doesn't exist
        if (!$this->_objTpl->blockExists($relationTemplateBlock)) {
            return false;
        }

        // abort if no ID of the related object has been supplied
        if (!$relatedKindId) {
            $this->_objTpl->hideBlock("news_{$relatedByKind}_related_block");
            return;
        }

        $query = '  SELECT  n.id                AS newsid,
                            n.userid            AS newsuid,
                            n.date              AS newsdate,
                            n.teaser_image_path,
                            n.teaser_image_thumbnail_path,
                            n.redirect,
                            n.publisher,
                            n.publisher_id,
                            n.author,
                            n.author_id,
                            nl.title            AS newstitle,
                            nl.text NOT REGEXP \'^(<br type="_moz" />)?$\' AS newscontent,
                            nl.teaser_text,
                            nc.name             AS name
                FROM        '.DBPREFIX.'module_news AS n
                INNER JOIN  '.DBPREFIX.'module_news_locale AS nl ON nl.news_id = n.id
                INNER JOIN  '.DBPREFIX.'module_news_categories_locale AS nc ON nc.category_id=n.catid
                WHERE       status = 1
                            AND nl.lang_id='.FRONTEND_LANG_ID.'
                            AND nc.lang_id='.FRONTEND_LANG_ID.'
                            '.($relatedByKind == 'category'  ? 'AND n.catid        ='.$relatedKindId : null)
                             .($relatedByKind == 'type'      ? 'AND n.typeid       ='.$relatedKindId : null)
                             .($relatedByKind == 'publisher' ? 'AND n.publisher_id ='.$relatedKindId : null)
                             .($relatedByKind == 'author'    ? 'AND n.authorid     ='.$relatedKindId : null).'
                            AND n.id !='.$messageId.'
                            AND (n.startdate<=\''.date('Y-m-d H:i:s').'\' OR n.startdate="0000-00-00 00:00:00")
                            AND (n.enddate>=\''.date('Y-m-d H:i:s').'\' OR n.enddate="0000-00-00 00:00:00")'
                           .($this->arrSettings['news_message_protection'] == '1' && !Permission::hasAllAccess() ? (
                                ($objFWUser = FWUser::getFWUserObject()) && $objFWUser->objUser->login() ?
                                    " AND (frontend_access_id IN (".implode(',', array_merge(array(0), $objFWUser->objUser->getDynamicPermissionIds())).") OR userid = ".$objFWUser->objUser->getId().") "
                                    :   " AND frontend_access_id=0 ")
                                :   '')
                .'ORDER BY newsdate DESC';

        $objResult = $objDatabase->Execute($query);

        // abort if no related messages were found or an error did occur
        if (!$objResult || $objResult->EOF) {
            $this->_objTpl->hideBlock("news_{$relatedByKind}_related_block");
            return;
        }

        while (!$objResult->EOF) {
            $newsid         = $objResult->fields['newsid'];
            $newstitle      = $objResult->fields['newstitle'];
            $newsUrl        = empty($objResult->fields['redirect'])
                                ? (empty($objResult->fields['newscontent'])
                                    ? ''
                                    : CONTREXX_SCRIPT_PATH.'?section=news&cmd=details&newsid='.$newsid)
                                : $objResult->fields['redirect'];

            $htmlLink       = self::parseLink($newsUrl, $newstitle, contrexx_raw2xhtml('['.$_ARRAYLANG['TXT_NEWS_MORE'].'...]'));
            $htmlLinkTitle  = self::parseLink($newsUrl, $newstitle, contrexx_raw2xhtml($newstitle));
            // in case that the message is a stub, we shall just display the news title instead of a html-a-tag with no href target
            if (empty($htmlLinkTitle)) {
                $htmlLinkTitle = contrexx_raw2xhtml($newstitle);
            }

            list($image, $htmlLinkImage, $imageSource) = self::parseImageThumbnail($objResult->fields['teaser_image_path'],
                                                                                   $objResult->fields['teaser_image_thumbnail_path'],
                                                                                   $newstitle,
                                                                                   $newsUrl);
            $author = FWUser::getParsedUserTitle($objResult->fields['author_id'], $objResult->fields['author']);
            $publisher = FWUser::getParsedUserTitle($objResult->fields['publisher_id'], $objResult->fields['publisher']);

            $this->_objTpl->setVariable(array(
               'NEWS_'.$placeholderPrefix.'_RELATED_MESSAGE_ID'            => $newsid,
               'NEWS_'.$placeholderPrefix.'_RELATED_MESSAGE_CSS'           => 'row'.($i % 2 + 1),
               'NEWS_'.$placeholderPrefix.'_RELATED_MESSAGE_TEASER'        => nl2br($objResult->fields['teaser_text']),
               'NEWS_'.$placeholderPrefix.'_RELATED_MESSAGE_TITLE'         => contrexx_raw2xhtml($newstitle),
               'NEWS_'.$placeholderPrefix.'_RELATED_MESSAGE_LONG_DATE'     => date(ASCMS_DATE_FORMAT,$objResult->fields['newsdate']),
               'NEWS_'.$placeholderPrefix.'_RELATED_MESSAGE_DATE'          => date(ASCMS_DATE_SHORT_FORMAT, $objResult->fields['newsdate']),
               'NEWS_'.$placeholderPrefix.'_RELATED_MESSAGE_LINK_TITLE'    => $htmlLinkTitle,
               'NEWS_'.$placeholderPrefix.'_RELATED_MESSAGE_LINK'          => $htmlLink,
               'NEWS_'.$placeholderPrefix.'_RELATED_MESSAGE_LINK_URL'      => contrexx_raw2xhtml($newsUrl),
               'NEWS_'.$placeholderPrefix.'_RELATED_MESSAGE_CATEGORY'      => stripslashes($objResult->fields['name']),
// TODO: fetch typename through a newly to be created separate methode
               //'NEWS_'.$placeholderPrefix.'_RELATED_MESSAGE_TYPE'          => ($this->arrSettings['news_use_types'] == 1 ? stripslashes($objResult->fields['typename']) : ''),
               'NEWS_'.$placeholderPrefix.'_RELATED_MESSAGE_PUBLISHER'     => contrexx_raw2xhtml($publisher),
               'NEWS_'.$placeholderPrefix.'_RELATED_MESSAGE_AUTHOR'        => contrexx_raw2xhtml($author),
            ));

            if (!empty($image)) {
                $this->_objTpl->setVariable(array(
                    'NEWS_'.$placeholderPrefix.'_RELATED_MESSAGE_IMAGE'         => $image,
                    'NEWS_'.$placeholderPrefix.'_RELATED_MESSAGE_IMAGE_SRC'     => contrexx_raw2xhtml($imageSource),
                    'NEWS_'.$placeholderPrefix.'_RELATED_MESSAGE_IMAGE_ALT'     => contrexx_raw2xhtml($newstitle),
                    'NEWS_'.$placeholderPrefix.'_RELATED_MESSAGE_IMAGE_LINK'    => $htmlLinkImage,
                ));

                if ($this->_objTpl->blockExists($imageTemplateBlock)) {
                    $this->_objTpl->parse($imageTemplateBlock);
                }
            } else {
                if ($this->_objTpl->blockExists($imageTemplateBlock)) {
                    $this->_objTpl->hideBlock($imageTemplateBlock);
                }
            }

            $this->_objTpl->parse($messageTemplateBlock);
            $i++;
            $objResult->MoveNext();
        }

        $this->_objTpl->setVariable(array(
            'TXT_NEWS_COMMENTS'                                => $_ARRAYLANG['TXT_NEWS_COMMENTS'],
            'TXT_NEWS_DATE'                                    => $_ARRAYLANG['TXT_DATE'],
            'TXT_NEWS_MESSAGE'                                 => $_ARRAYLANG['TXT_NEWS_MESSAGE'],
            'TXT_NEWS_RELATED_MESSAGES_OF_'.$placeholderPrefix => $_ARRAYLANG['TXT_NEWS_RELATED_MESSAGES_OF_'.$placeholderPrefix],
        ));
        $this->_objTpl->parse($relationTemplateBlock);
    }

    /**
     * Parses a user's account and profile data specified by $userId.
     * If the HTML_Template_Sigma template block specified by $blockName
     * exists, then the user's data will be parsed inside this block.
     * Otherwise, it will try to parse a template variable by the same
     * name. For instance, if $blockName is set to news_publisher,
     * it will first try to parse the template block news_publisher,
     * if unable it will parse the template variable NEWS_PUBLISHER.
     *
     * @param   integer User-ID
     * @param   string  User name/title that shall be used as fallback,
     *                  if no user account specified by $userId could be found
     * @param   string  Name of the HTML_Template_Sigma template block to parse.
     *                  For instance if you have a block like:
     *                      <!-- BEGIN/END news_publisher -->
     *                  set $blockName to:
     *                      news_publisher
     */
    private function parseUserAccountData($userId, $userTitle, $blockName)
    {
        $placeholderName = strtoupper($blockName);

        if ($userId && $objUser = FWUser::getFWUserObject()->objUser->getUser($userId)) {
            if ($this->_objTpl->blockExists($blockName)) {
                // fill the template block user (i.e. news_publisher) with the user account's data 
                $this->_objTpl->setVariable(array(
                    $placeholderName.'_ID'          => $objUser->getId(),
                    $placeholderName.'_USERNAME'    => contrexx_raw2xhtml($objUser->getUsername())
                ));
                
                $objAccessLib = new AccessLib($this->_objTpl);
                $objAccessLib->setModulePrefix($placeholderName.'_');
                $objAccessLib->setAttributeNamePrefix($blockName.'_profile_attribute');

                $objUser->objAttribute->first();
                while (!$objUser->objAttribute->EOF) {
                    $objAttribute = $objUser->objAttribute->getById($objUser->objAttribute->getId());
                    $objAccessLib->parseAttribute($objUser, $objAttribute->getId(), 0, false, FALSE, false, false, false);
                    $objUser->objAttribute->next();
                }
            } elseif ($this->_objTpl->placeholderExists($placeholderName)) {
                // fill the placeholder (i.e. NEWS_PUBLISHER) with the user title
                $userTitle = FWUser::getParsedUserTitle($userId);
                $this->_objTpl->setVariable($placeholderName, contrexx_raw2xhtml($userTitle));
            }
        } elseif (!empty($userTitle)) {
            if ($this->_objTpl->blockExists($blockName)) {
                // replace template block (i.e. news_publisher) by the user title
                $this->_objTpl->replaceBlock($blockName, contrexx_raw2xhtml($userTitle));
            } elseif ($this->_objTpl->placeholderExists($placeholderName)) {
                // fill the placeholder (i.e. NEWS_PUBLISHER) with the user title
                $this->_objTpl->setVariable($placeholderName, contrexx_raw2xhtml($userTitle));
            }
        }
    }

    /**
    * Gets the list with the headlines
    *
    * @global    array
    * @global    ADONewConnection
    * @global    array
    * @return    string    parsed content
    */
    private function getHeadlines() {
        global $_CONFIG, $objDatabase, $_ARRAYLANG;

        $selected_cat       = '';
        $selected_type      = '';
        $selectedPublisher = '';
        $selectedAuthor     = '';
        $newsfilter = '';
        $paging     = '';
        $pos        = 0;
        $i          = 0;

        if (isset($_GET['pos'])) {
            $pos = intval($_GET['pos']);
        }

        if (!empty($_REQUEST['category']) || (($_REQUEST['category'] = intval($_REQUEST['cmd'])) > 0)) {
            $newsfilter .= ' AND (';
            $boolFirst = true;

            $arrCategories = explode(',',$_REQUEST['category']);

            if (count($arrCategories) == 1) {
                $selected_cat = intval($arrCategories[0]);
            }

            foreach ($arrCategories as $intCategoryId) {
                if (!$boolFirst) {
                    $newsfilter .= 'OR ';
                }

                $newsfilter .= 'n.catid='.intval($intCategoryId).' ';
                $boolFirst = false;
            }
            $newsfilter .= ')';
        }

        if ($this->_objTpl->placeholderExists('NEWS_CAT_DROPDOWNMENU')) {
            $catMenu    =  '<select onchange="this.form.submit()" name="category">'."\n";
            $catMenu    .= '<option value="" selected="selected">'.$_ARRAYLANG['TXT_CATEGORY'].'</option>'."\n";
            $catMenu    .= $this->getCategoryMenu($selected_cat)."\n";
            $catMenu    .= '</select>'."\n";
            $this->_objTpl->setVariable('NEWS_CAT_DROPDOWNMENU', $catMenu);
        }

        if($this->arrSettings['news_use_types'] == 1) {
            if (!empty($_REQUEST['type'])) {
                $newsfilter .= ' AND (';
                $boolFirst = true;

                $arrTypes = explode(',',$_REQUEST['type']);

                if (count($arrTypes) == 1) {
                    $selected_type = intval($arrTypes[0]);
                }

                foreach ($arrTypes as $intTypeId) {
                    if (!$boolFirst) {
                        $newsfilter .= 'OR ';
                    }

                    $newsfilter .= 'n.typeid='.intval($intTypeId).' ';
                    $boolFirst = false;
                }
                $newsfilter .= ')';
            }

            if ($this->_objTpl->placeholderExists('NEWS_TYPE_DROPDOWNMENU')) {
                $typeMenu    =  '<select onchange="this.form.submit()" name="type">'."\n";
                $typeMenu    .= '<option value="" selected="selected">'.$_ARRAYLANG['TXT_TYPE'].'</option>'."\n";
                $typeMenu    .= $this->getTypeMenu($selected_type)."\n";
                $typeMenu    .= '</select>'."\n";
                $this->_objTpl->setVariable('NEWS_TYPE_DROPDOWNMENU', $typeMenu);
            }
        }
        
        if (!empty($_REQUEST['publisher'])) {
            $newsfilter .= ' AND (';
            $boolFirst = true;

            $arrPublishers = explode(',',  contrexx_input2raw($_REQUEST['publisher']));

            if (count($arrPublishers) == 1) {
                $selectedPublisher = $arrPublishers[0];
            }

            foreach ($arrPublishers as $intPublisherId) {
                if (!$boolFirst) {
                    $newsfilter .= 'OR ';
                }

                $newsfilter .= 'n.publisher_id='.intval($intPublisherId).' ';
                $boolFirst = false;
            }
            $newsfilter .= ')';
        }

        if ($this->_objTpl->placeholderExists('NEWS_PUBLISHER_DROPDOWNMENU')) {
            $publisherMenu    = '<select onchange="window.location=\'index.php?section=news&amp;cmd='.intval($_REQUEST['cmd']).'&amp;publisher=\'+this.value" name="publisher">'."\n";
            $publisherMenu   .= '<option value="" selected="selected">'.$_ARRAYLANG['TXT_NEWS_PUBLISHER'].'</option>'."\n";
            $publisherMenu   .= $this->getPublisherMenu($selectedPublisher, $selected_cat)."\n";
            $publisherMenu   .= '</select>'."\n";
            $this->_objTpl->setVariable('NEWS_PUBLISHER_DROPDOWNMENU', $publisherMenu);
        }
        
        if (!empty($_REQUEST['author'])) {
            $newsfilter .= ' AND (';
            $boolFirst = true;

            $arrAuthors = explode(',',  contrexx_input2raw($_REQUEST['author']));

            if (count($arrAuthors) == 1) {
                $selectedAuthor = $arrAuthors[0];
            }

            foreach ($arrAuthors as $intAuthorId) {
                if (!$boolFirst) {
                    $newsfilter .= 'OR ';
                }

                $newsfilter .= 'n.author_id='.intval($intAuthorId).' ';
                $boolFirst = false;
            }
            $newsfilter .= ')';
        }

        if ($this->_objTpl->placeholderExists('NEWS_AUTHOR_DROPDOWNMENU')) {
            $authorMenu    = '<select onchange="this.form.submit()" name="author">'."\n";
            $authorMenu   .= '<option value="" selected="selected">'.$_ARRAYLANG['TXT_NEWS_AUTHOR'].'</option>'."\n";
            $authorMenu   .= $this->getAuthorMenu($selectedAuthor)."\n";
            $authorMenu   .= '</select>'."\n";
            $this->_objTpl->setVariable('NEWS_AUTHOR_DROPDOWNMENU', $authorMenu);
        }

        $this->_objTpl->setVariable(array(
            'TXT_PERFORM'                   => $_ARRAYLANG['TXT_PERFORM'],
            'TXT_CATEGORY'                  => $_ARRAYLANG['TXT_CATEGORY'],
            'TXT_TYPE'                      => ($this->arrSettings['news_use_types'] == 1 ? $_ARRAYLANG['TXT_TYPE'] : ''),
            'TXT_DATE'                      => $_ARRAYLANG['TXT_DATE'],
            'TXT_TITLE'                     => $_ARRAYLANG['TXT_TITLE'],
            'TXT_NEWS_MESSAGE'              => $_ARRAYLANG['TXT_NEWS_MESSAGE']
        ));

        $query = '  SELECT      n.id                AS newsid,
                                n.userid            AS newsuid,
                                n.date              AS newsdate,
                                n.teaser_image_path,
                                n.teaser_image_thumbnail_path,
                                n.redirect,
                                n.publisher,
                                n.publisher_id,
                                n.author,
                                n.author_id,
                                nl.title            AS newstitle,
                                nl.text NOT REGEXP \'^(<br type="_moz" />)?$\' AS newscontent,
                                nl.teaser_text,
                                nc.name             AS name
                    FROM        '.DBPREFIX.'module_news AS n
                    INNER JOIN  '.DBPREFIX.'module_news_locale AS nl ON nl.news_id = n.id
                    INNER JOIN  '.DBPREFIX.'module_news_categories_locale AS nc ON nc.category_id=n.catid
                    WHERE       status = 1
                                AND nl.lang_id='.FRONTEND_LANG_ID.'
                                AND nc.lang_id='.FRONTEND_LANG_ID.'
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
            $category = '&amp;category='.$selected_cat;
        }

        if (isset($_REQUEST['type'])) {
            $type = '&amp;type='.$selected_type;
        }

        if (isset($_REQUEST['cmd'])) {
            if ($_REQUEST['cmd'] == $selected_cat) {
                $category = '&amp;cmd='.$_REQUEST['cmd'];
            } else {
                $category .= '&amp;cmd='.$_REQUEST['cmd'];
            }
        }

        if ($count>intval($_CONFIG['corePagingLimit'])) {
            $paging = getPaging($count, $pos, '&amp;section=news'.$category.$type, $_ARRAYLANG['TXT_NEWS_MESSAGES'], true);
        }
        $this->_objTpl->setVariable('NEWS_PAGING', $paging);
        $objResult = $objDatabase->SelectLimit($query, $_CONFIG['corePagingLimit'], $pos);
        /*** end paging ***/

        if ($count>=1) {
            while (!$objResult->EOF) {
                $newsid         = $objResult->fields['newsid'];
                $newstitle      = $objResult->fields['newstitle'];
                $newsUrl        = empty($objResult->fields['redirect'])
                                    ? (empty($objResult->fields['newscontent'])
                                        ? ''
                                        : CONTREXX_SCRIPT_PATH.'?section=news&cmd=details&newsid='.$newsid)
                                    : $objResult->fields['redirect'];

                $htmlLink       = self::parseLink($newsUrl, $newstitle, contrexx_raw2xhtml('['.$_ARRAYLANG['TXT_NEWS_MORE'].'...]'));
                $htmlLinkTitle  = self::parseLink($newsUrl, $newstitle, contrexx_raw2xhtml($newstitle));
                // in case that the message is a stub, we shall just display the news title instead of a html-a-tag with no href target
                if (empty($htmlLinkTitle)) {
                    $htmlLinkTitle = contrexx_raw2xhtml($newstitle);
                }

                list($image, $htmlLinkImage, $imageSource) = self::parseImageThumbnail($objResult->fields['teaser_image_path'],
                                                                                       $objResult->fields['teaser_image_thumbnail_path'],
                                                                                       $newstitle,
                                                                                       $newsUrl);
                $author = FWUser::getParsedUserTitle($objResult->fields['author_id'], $objResult->fields['author']);
                $publisher = FWUser::getParsedUserTitle($objResult->fields['publisher_id'], $objResult->fields['publisher']);

                $this->_objTpl->setVariable(array(
                   'NEWS_ID'            => $newsid,
                   'NEWS_CSS'           => 'row'.($i % 2 + 1),
                   'NEWS_TEASER'        => nl2br($objResult->fields['teaser_text']),
                   'NEWS_TITLE'         => contrexx_raw2xhtml($newstitle),
                   'NEWS_LONG_DATE'     => date(ASCMS_DATE_FORMAT,$objResult->fields['newsdate']),
                   'NEWS_DATE'          => date(ASCMS_DATE_SHORT_FORMAT, $objResult->fields['newsdate']),
                   'NEWS_LINK_TITLE'    => $htmlLinkTitle,
                   'NEWS_LINK'          => $htmlLink,
                   'NEWS_LINK_URL'      => contrexx_raw2xhtml($newsUrl),
                   'NEWS_CATEGORY'      => stripslashes($objResult->fields['name']),
// TODO: fetch typename from a newly to be created separate methode
                   //'NEWS_TYPE'          => ($this->arrSettings['news_use_types'] == 1 ? stripslashes($objResult->fields['typename']) : ''),
                   'NEWS_PUBLISHER'     => contrexx_raw2xhtml($publisher),
                   'NEWS_AUTHOR'        => contrexx_raw2xhtml($author),
                ));

                if (!empty($image)) {
                    $this->_objTpl->setVariable(array(
                        'NEWS_IMAGE'         => $image,
                        'NEWS_IMAGE_SRC'     => contrexx_raw2xhtml($imageSource),
                        'NEWS_IMAGE_ALT'     => contrexx_raw2xhtml($newstitle),
                        'NEWS_IMAGE_LINK'    => $htmlLinkImage,
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
                'NEWS_TYPE' => '',
            ));
            $this->_objTpl->parse('newsrow');
        }
        return $this->_objTpl->get();
    }


    private function listNews($type)
    {
// TODO: create a method that can be used to parse the message-list of the methods news::getTopNews(), news::getHeadlines()
/*
        switch($type) {
            case 'topnews':
                $order = 'ORDER BY (SELECT COUNT(*) FROM `'.DBPREFIX.'module_news_stats_view` WHERE `news_id`=n.`id` AND `time` > DATE_SUB(NOW(), INTERVAL '.intval($this->arrSettings['news_top_days']).' DAY)) DESC';
                break;

            case 'archive':

            case 'headlines':
            default:
                $order = 'ORDER BY `date` DESC';
                break;
        }

        $accessRestriction = '';
        if (   $this->arrSettings['news_message_protection'] == '1'
            && !Permission::hasAllAccess()
        ) {
            if (   ($objFWUser = FWUser::getFWUserObject())
                && $objFWUser->objUser->login()
            ) {
                $accessRestriction = " AND (frontend_access_id IN (".implode(',', array_merge(array(0), $objFWUser->objUser->getDynamicPermissionIds())).") OR userid = ".$objFWUser->objUser->getId().") ";
            } else {
                $accessRestriction = " AND frontend_access_id=0 ";
            }
        }

        $query = '  SELECT      tblNews.id                AS news_id,
                                tblNews.userid            AS news_user_id,
                                tblNews.date              AS news_date,
                                tblNews.teaser_image_path   AS news_teaser_image_path,
                                tblNews.teaser_image_thumbnail_path AS news_teaser_image_thumbnail_path,
                                tblNews.redirect    AS news_redirect,
                                tblNews.publisher   AS news_publisher,
                                tblNews.publisher_id    AS news_publisher_id,
                                tblNews.author  AS news_author,
                                tblNews.author_id   AS news_author_id,

                                tblNewsLocale.title            AS news_title,
                                tblNewsLocale.text             AS news_text,
                                tblNewsLocale.teaser_text       AS news_teaser_text,

                                tblCategoryLocale.name             AS category_name,
                                tblTypeLocale.name            AS type_name

                          FROM '.DBPREFIX.'module_news AS tblNews

                    INNER JOIN '.DBPREFIX.'module_news_locale AS tblNewsLocale
                            ON tblNewsLocale.news_id = tblNews.id

                    INNER JOIN '.DBPREFIX.'module_news_categories_locale AS tblCategoryLocale
                            ON tblCategoryLocale.category_id = tblNews.catid

                    LEFT JOIN '.DBPREFIX.'module_news_types_locale AS tblTypeLocale
                            ON tblTypeLocale.type_id = tblNews.typeid 

                    WHERE       tblNews.status = 1
                                AND tblNewsLocale.lang_id = '.FRONTEND_LANG_ID.'
                                AND tblCategoryLocale.lang_id = '.FRONTEND_LANG_ID.'
                                AND tblTypeLocale.lang_id = '.FRONTEND_LANG_ID.'
                                AND (tblNews.startdate <= \''.date('Y-m-d H:i:s').'\' OR tblNews.startdate="0000-00-00 00:00:00")
                                AND (tblNews.enddate >= \''.date('Y-m-d H:i:s').'\' OR tblNews.enddate="0000-00-00 00:00:00")
                                '.$newsfilter
                                $accessRestriction
                    .'ORDER BY    newsdate DESC';



*/
    }


    /**
    * Gets the list with the top news
    *
    * @global    array
    * @global    ADONewConnection
    * @global    array
    * @return    string    parsed content
    */
    private function getTopNews() {
        global $_CONFIG, $objDatabase, $_ARRAYLANG;

        $newsfilter = '';
        $paging     = '';
        $pos        = 0;
        $i          = 0;

        if (isset($_GET['pos'])) {
            $pos = intval($_GET['pos']);
        }
        
        $this->_objTpl->setVariable(array(
            'TXT_DATE'              => $_ARRAYLANG['TXT_DATE'],
            'TXT_TITLE'             => $_ARRAYLANG['TXT_TITLE'],
            'TXT_NEWS_MESSAGE'      => $_ARRAYLANG['TXT_NEWS_MESSAGE']
        ));
        
        $query = '  SELECT      n.id                AS newsid,
                                n.userid            AS newsuid,
                                n.date              AS newsdate,
                                n.teaser_image_path,
                                n.teaser_image_thumbnail_path,
                                n.redirect,
                                n.publisher,
                                n.publisher_id,
                                n.author,
                                n.author_id,
                                nl.title            AS newstitle,
                                nl.text NOT REGEXP \'^(<br type="_moz" />)?$\' AS newscontent,
                                nl.teaser_text,
                                nc.name             AS name
                    FROM        '.DBPREFIX.'module_news AS n
                    INNER JOIN  '.DBPREFIX.'module_news_locale AS nl ON nl.news_id = n.id
                    INNER JOIN  '.DBPREFIX.'module_news_categories_locale AS nc ON nc.category_id=n.catid
                    WHERE       status = 1
                                AND nl.lang_id='.FRONTEND_LANG_ID.'
                                AND nc.lang_id='.FRONTEND_LANG_ID.'
                                AND (n.startdate<=\''.date('Y-m-d H:i:s').'\' OR n.startdate="0000-00-00 00:00:00")
                                AND (n.enddate>=\''.date('Y-m-d H:i:s').'\' OR n.enddate="0000-00-00 00:00:00")
                                '.$newsfilter
                               .($this->arrSettings['news_message_protection'] == '1' && !Permission::hasAllAccess() ? (
                                    ($objFWUser = FWUser::getFWUserObject()) && $objFWUser->objUser->login() ?
                                        " AND (frontend_access_id IN (".implode(',', array_merge(array(0), $objFWUser->objUser->getDynamicPermissionIds())).") OR userid = ".$objFWUser->objUser->getId().") "
                                        :   " AND frontend_access_id=0 ")
                                    :   '')
                    .'ORDER BY (SELECT COUNT(*) FROM '.DBPREFIX.'module_news_stats_view WHERE news_id=n.id AND time>DATE_SUB(NOW(), INTERVAL '.intval($this->arrSettings['news_top_days']).' DAY)) DESC';
                    
        /***start paging ****/
        $objResult = $objDatabase->Execute($query);
        $count = $objResult->RecordCount();
        if ($count>intval($_CONFIG['corePagingLimit'])) {
            $paging = getPaging($count, $pos, '&amp;section=topnews', $_ARRAYLANG['TXT_NEWS_MESSAGES'], true);
        }
        $this->_objTpl->setVariable('NEWS_PAGING', $paging);
        $objResult = $objDatabase->SelectLimit($query, $_CONFIG['corePagingLimit'], $pos);
        /*** end paging ***/

        if ($count>=1) {
            while (!$objResult->EOF) {
                $newsid         = $objResult->fields['newsid'];
                $newstitle      = $objResult->fields['newstitle'];
                $newsUrl        = empty($objResult->fields['redirect'])
                                    ? (empty($objResult->fields['newscontent'])
                                        ? ''
                                        : CONTREXX_SCRIPT_PATH.'?section=news&cmd=details&newsid='.$newsid)
                                    : $objResult->fields['redirect'];

                $htmlLink       = self::parseLink($newsUrl, $newstitle, contrexx_raw2xhtml('['.$_ARRAYLANG['TXT_NEWS_MORE'].'...]'));
                $htmlLinkTitle  = self::parseLink($newsUrl, $newstitle, contrexx_raw2xhtml($newstitle));
                // in case that the message is a stub, we shall just display the news title instead of a html-a-tag with no href target
                if (empty($htmlLinkTitle)) {
                    $htmlLinkTitle = contrexx_raw2xhtml($newstitle);
                }

                list($image, $htmlLinkImage, $imageSource) = self::parseImageThumbnail($objResult->fields['teaser_image_path'],
                                                                                       $objResult->fields['teaser_image_thumbnail_path'],
                                                                                       $newstitle,
                                                                                       $newsUrl);
                $author = FWUser::getParsedUserTitle($objResult->fields['author_id'], $objResult->fields['author']);
                $publisher = FWUser::getParsedUserTitle($objResult->fields['publisher_id'], $objResult->fields['publisher']);

                $this->_objTpl->setVariable(array(
                   'NEWS_ID'            => $newsid,
                   'NEWS_CSS'           => 'row'.($i % 2 + 1),
                   'NEWS_TEASER'        => nl2br($objResult->fields['teaser_text']),
                   'NEWS_TITLE'         => contrexx_raw2xhtml($newstitle),
                   'NEWS_LONG_DATE'     => date(ASCMS_DATE_FORMAT,$objResult->fields['newsdate']),
                   'NEWS_DATE'          => date(ASCMS_DATE_SHORT_FORMAT, $objResult->fields['newsdate']),
                   'NEWS_LINK_TITLE'    => $htmlLinkTitle,
                   'NEWS_LINK'          => $htmlLink,
                   'NEWS_LINK_URL'      => contrexx_raw2xhtml($newsUrl),
                   'NEWS_CATEGORY'      => stripslashes($objResult->fields['name']),
// TODO: fetch typename from a newly to be created separate methode
                   //'NEWS_TYPE'          => ($this->arrSettings['news_use_types'] == 1 ? stripslashes($objResult->fields['typename']) : ''),
                   'NEWS_PUBLISHER'     => contrexx_raw2xhtml($publisher),
                   'NEWS_AUTHOR'        => contrexx_raw2xhtml($author),
                ));

                if (!empty($image)) {
                    $this->_objTpl->setVariable(array(
                        'NEWS_IMAGE'         => $image,
                        'NEWS_IMAGE_SRC'     => contrexx_raw2xhtml($imageSource),
                        'NEWS_IMAGE_ALT'     => contrexx_raw2xhtml($newstitle),
                        'NEWS_IMAGE_LINK'    => $htmlLinkImage,
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
                'NEWS_TEXT' => ''
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
    public function getPageTitle($pageTitle='')
    {
        if (empty($this->newsTitle)) {
            $this->newsTitle = $pageTitle;
        }
    }

    private function _notify_by_email($news_id) {
// TODO: Refactor this method
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

    private function _notify_user_by_email($user_id, $news_id) {
// TODO: Refactor this method
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
        $msg .= '  http://'.$_SERVER['SERVER_NAME'].  $serverPort.ASCMS_PATH_OFFSET . '/cadmin/index.php?cmd=news'
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
    * @global array
    * @global ADONewConnection
    * @see HTML_Template_Sigma::setTemplate(), modulemanager::getModules(), Permission::checkAccess()
    * @return string content
    */
    private function _submit()
    {
// TODO: refactor this method before activating it again. Framework usage is outdated!
        return false;

        global $_ARRAYLANG, $objDatabase;

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

        $newsCat = '';
        $newsType = '';
        $newsText = '';
        $newsTeaserText = '';
        $newsTitle = '';
        $newsRedirect = 'http://';
        $newsSource = 'http://';
        $newsUrl1 = 'http://';
        $newsUrl2 = 'http://';
        $insertStatus = false;

        require_once ASCMS_LIBRARY_PATH . '/spamprotection/captcha.class.php';
        $captcha = new Captcha();

        $offset = $captcha->getOffset();
        $alt = $captcha->getAlt();
        $url = $captcha->getUrl();

        if (isset($_POST['submitNews'])) {
            $objValidator = new FWValidator();

            $_POST['newsTitle'] = contrexx_input2raw(html_entity_decode($_POST['newsTitle'], ENT_QUOTES, CONTREXX_CHARSET));
            $_POST['newsTeaserText'] = contrexx_input2raw(html_entity_decode($_POST['newsTeaserText'], ENT_QUOTES, CONTREXX_CHARSET));
            $_POST['newsRedirect'] = $objValidator->getUrl(contrexx_input2raw(html_entity_decode($_POST['newsRedirect'], ENT_QUOTES, CONTREXX_CHARSET)));
            $_POST['newsText'] = contrexx_remove_script_tags(contrexx_input2raw(html_entity_decode($_POST['newsText'], ENT_QUOTES, CONTREXX_CHARSET)));
            $_POST['newsText'] = $this->filterBodyTag(html_entity_decode($_POST['newsText'], ENT_QUOTES, CONTREXX_CHARSET));
            $_POST['newsSource'] = $objValidator->getUrl(contrexx_input2raw(html_entity_decode($_POST['newsSource'], ENT_QUOTES, CONTREXX_CHARSET)));
            $_POST['newsUrl1'] = $objValidator->getUrl(contrexx_input2raw(html_entity_decode($_POST['newsUrl1'], ENT_QUOTES, CONTREXX_CHARSET)));
            $_POST['newsUrl2'] = $objValidator->getUrl(contrexx_input2raw(html_entity_decode($_POST['newsUrl2'], ENT_QUOTES, CONTREXX_CHARSET)));
            $_POST['newsCat'] = intval($_POST['newsCat']);
            $_POST['newsType'] = isset($_POST['newsType']) ? intval($_POST['newsType']) : 0;

            if (!$captcha->compare($_POST['captcha'], $_POST['offset'])) {
                $this->_submitMessage = $_ARRAYLANG['TXT_CAPTCHA_ERROR'] . '<br />';
            }

            if (!empty($_POST['newsTitle']) && $captcha->compare($_POST['captcha'], $_POST['offset']) &&
               (!empty($_POST['newsText']) || (!empty($_POST['newsRedirect']) && $_POST['newsRedirect'] != 'http://'))) {
                    $insertStatus = $this->_insert();
                    if (!$insertStatus) {
                        $newsTitle = $_POST['newsTitle'];
                        $newsTeaserText = $_POST['newsTeaserText'];
                        $newsRedirect = $_POST['newsRedirect'];
                        $newsSource = $_POST['newsSource'];
                        $newsUrl1 = $_POST['newsUrl1'];
                        $newsUrl2 = $_POST['newsUrl2'];
                        $newsCat = $_POST['newsCat'];
                        $newsType = $_POST['newsType'];
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
                    $newsType = $_POST['newsType'];
                    $newsText = $_POST['newsText'];

                    if ( (empty($_POST['newsTitle']) || (empty($_POST['newsText']) || $_POST['newsText'] == '&nbsp;' || $_POST['newsText'] == '<br />' )) &&
                         (empty($_POST['newsRedirect']) || $_POST['newsRedirect'] == 'http://') ) {
                        $this->_submitMessage .= $_ARRAYLANG['TXT_SET_NEWS_TITLE_AND_TEXT_OR_REDIRECT'].'<br /><br />';
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
            $wysiwygEditor = 'FCKeditor';
            $FCKeditorBasePath = '/editor/fckeditor/';

            $this->_objTpl->setVariable(array(
                'TXT_NEWS_MESSAGE'          => $_ARRAYLANG['TXT_NEWS_MESSAGE'],
                'TXT_TITLE'                 => $_ARRAYLANG['TXT_TITLE'],
                'TXT_CATEGORY'              => $_ARRAYLANG['TXT_CATEGORY'],
                'TXT_TYPE'                  => ($this->arrSettings['news_use_types'] == 1 ? $_ARRAYLANG['TXT_TYPE'] : ''),
                'TXT_HYPERLINKS'            => $_ARRAYLANG['TXT_HYPERLINKS'],
                'TXT_EXTERNAL_SOURCE'       => $_ARRAYLANG['TXT_EXTERNAL_SOURCE'],
                'TXT_LINK'                  => $_ARRAYLANG['TXT_LINK'],
                'TXT_NEWS_NEWS_CONTENT'     => $_ARRAYLANG['TXT_NEWS_NEWS_CONTENT'],
                'TXT_NEWS_TEASER_TEXT'      => $_ARRAYLANG['TXT_NEWS_TEASER_TEXT'],
                'TXT_SUBMIT_NEWS'           => $_ARRAYLANG['TXT_SUBMIT_NEWS'],
                'TXT_NEWS_REDIRECT'         => $_ARRAYLANG['TXT_NEWS_REDIRECT'],
                'TXT_NEWS_NEWS_URL'         => $_ARRAYLANG['TXT_NEWS_NEWS_URL'],
                'TXT_CAPTCHA'               => $_ARRAYLANG['TXT_CAPTCHA'],
                'NEWS_TEXT'                 => get_wysiwyg_editor('newsText', $newsText, 'news'),
                'NEWS_CAT_MENU'             => $this->getCategoryMenu($newsCat),
                'NEWS_TYPE_MENU'            => ($this->arrSettings['news_use_types'] == 1 ? $this->getTypeMenu($newsType) : ''),
                'NEWS_TITLE'                => contrexx_raw2xhtml($newsTitle),
                'NEWS_SOURCE'               => contrexx_raw2xhtml($newsSource),
                'NEWS_URL1'                 => contrexx_raw2xhtml($newsUrl1),
                'NEWS_URL2'                 => contrexx_raw2xhtml($newsUrl2),
                'NEWS_TEASER_TEXT'          => contrexx_raw2xhtml($newsTeaserText),
                'NEWS_REDIRECT'             => contrexx_raw2xhtml($newsRedirect),
                'CAPTCHA_OFFSET'            => $offset,
                'IMAGE_URL'                 => $url,
                'IMAGE_ALT'                 => $alt
            ));

            if ($this->_objTpl->blockExists('news_category_menu')) {
                $objResult = $objDatabase->Execute('SELECT category_id as catid, name FROM '.DBPREFIX.'module_news_categories_locale WHERE lang_id='.FRONTEND_LANG_ID.' ORDER BY name asc');

                if ($objResult !== false) {
                    while (!$objResult->EOF) {
                        $this->_objTpl->setVariable(array(
                            'NEWS_CATEGORY_ID'      => $objResult->fields['catid'],
                            'NEWS_CATEGORY_TITLE'   => contrexx_raw2xhtml($objResult->fields['name'])
                        ));
                        $this->_objTpl->parse('news_category_menu');
                        $objResult->MoveNext();
                    }
                }
            }

            if ($this->_objTpl->blockExists('news_type_menu') && $this->arrSettings['news_use_types'] == 1) {
                $objResult = $objDatabase->Execute('SELECT type_id as typeid, name FROM '.DBPREFIX.'module_news_types_locale WHERE lang_id='.FRONTEND_LANG_ID.' ORDER BY name asc');

                if ($objResult !== false) {
                    while (!$objResult->EOF) {
                        $this->_objTpl->setVariable(array(
                            'NEWS_CATEGORY_ID'      => $objResult->fields['catid'],
                            'NEWS_CATEGORY_TITLE'   => contrexx_raw2xhtml($objResult->fields['name']),
                            'NEWS_TYPE_ID'          => $objResult->fields['typeid'],
                            'NEWS_TYPE_TITLE'       => contrexx_raw2xhtml($objResult->fields['name'])

                        ));
                        $this->_objTpl->parse('news_type_menu');
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
    * @global ADONewConnection
    * @global array
    * @return boolean true on success - false on failure
    */
    private function _insert()
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
        $newstype       = isset($_POST['newstype']) ? $_POST['newstype'] : 0;
        $userid         = $objFWUser->objUser->getId();

        $enable = $this->arrSettings['news_activate_submitted_news'] == '1' ? '1' : '0';
        $query = SQL::insert('module_news', array(
            'date' => $date,
            'redirect' => $newsRedirect,
            'source' => $newssource,
            'url1' => $newsurl1,
            'url2' => $newsurl2,
            'catid' => $newscat,
            'typeid' => $newstype,
            'status' => $enable,
            'validated' => $enable,
            'userid' => $userid,
            'changelog' => $date,
            //the following are empty defaults for the text fields.
            //text fields can't have a default and we need one in SQL_STRICT_TRANS_MODE
            'teaser_frames' => '',
            'teaser_image_path' => '',
            'teaser_image_thumbnail_path' => '',
        ), array(
            'escape' => true
            ));

        $objResult = $objDatabase->Execute($query);
        if ($objResult !== false) {
            $this->_submitMessage = $_ARRAYLANG['TXT_NEWS_SUCCESSFULLY_SUBMITED'].'<br /><br />';
            $ins_id = $objDatabase->Insert_ID();
            $this->submitLocales($ins_id, $newstitle, $newstext, $newsTeaserText);
            return $ins_id;
        } else {
            $this->_submitMessage = $_ARRAYLANG['TXT_NEWS_SUBMIT_ERROR'].'<br /><br />';
            return false;
        }
    }


    /**
    * Show feed page
    * @todo Add proper docblock
    * @global array
    * @global integer
    * @return string Template output
    */
    private function _showFeed()
    {
        global $_ARRAYLANG, $_LANGID;

        $serverPort = $_SERVER['SERVER_PORT'] == 80 ? '' : ':'.intval($_SERVER['SERVER_PORT']);
        $rssFeedUrl = 'http://'.$_SERVER['SERVER_NAME'].$serverPort.ASCMS_PATH_OFFSET.'/feed/news_headlines_'.FWLanguage::getLanguageParameter($_LANGID, 'lang').'.xml';
        $jsFeedUrl = 'http://'.$_SERVER['SERVER_NAME'].$serverPort.ASCMS_PATH_OFFSET.'/feed/news_'.FWLanguage::getLanguageParameter($_LANGID, 'lang').'.js';
        $hostname = addslashes(htmlspecialchars($_SERVER['SERVER_NAME'], ENT_QUOTES, CONTREXX_CHARSET));

        $rss2jsCode = <<<RSS2JSCODE
&lt;script language="JavaScript" type="text/javascript"&gt;
&lt;!--
// {$_ARRAYLANG['TXT_NEWS_OPTIONAL_VARS']}
var rssFeedFontColor = '#000000'; // {$_ARRAYLANG['TXT_NEWS_FONT_COLOR']}
var rssFeedFontSize = 8; // {$_ARRAYLANG['TXT_NEWS_FONT_SIZE']}
var rssFeedFont = 'Arial, Verdana'; // {$_ARRAYLANG['TXT_NEWS_FONT']}
var rssFeedLimit = 10; // {$_ARRAYLANG['TXT_NEWS_DISPLAY_LIMIT']}
var rssFeedShowDate = true; // {$_ARRAYLANG['TXT_NEWS_SHOW_NEWS_DATE']}
var rssFeedTarget = '_blank'; // _blank | _parent | _self | _top
var rssFeedContainer = 'news_rss_feeds';
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
     * Returns Teaser Text if displaying a detail page.
     * Used in index.php to overwrite the meta description.
     *
     * @return String Teaser if displaying detail page, else null
     */
    public function getTeaser()
    {
        return $this->_teaser;
    }


    /**
     * Fetch news comment data that has been submitted via POST
     * and return it as array with three elements.
     * Where the first element is the name of the poster (if poster is anonymous),
     * the second is the title of the comment and the third is the comment
     * message by it self.
     *
     * @return array
     */
    private function fetchSubmittedCommentData()
    {
        // only proceed if the user did submit any data
        if (!isset($_POST['news_add_comment'])) {
            return false;
        }

        $arrData = array(
            'name'    => '',
            'title'   => '',
            'message' => '',
        );

        if (isset($_POST['news_comment_name'])) {
            $arrData['name'] = contrexx_input2raw(trim($_POST['news_comment_name']));
        }

        if (isset($_POST['news_comment_title'])) {
            $arrData['title'] = contrexx_input2raw(trim($_POST['news_comment_title']));
        }

        if (isset($_POST['news_comment_message'])) {
            $arrData['message'] = contrexx_input2raw(trim($_POST['news_comment_message']));
        }

        return $arrData;
    }


    /**
     * Validates the submitted comment data and writes it to the databse if valid.
     * Additionally, a notification is send out to the administration about the comment
     * by e-mail (only if the corresponding configuration option is set to do so). 
     *
     * @param   integer News message ID for which the comment shall be stored
     * @param   string  Title of the news message for which the comment shall be stored.
     *                  The title will be used in the notification e-mail
     * @param   string  The poster's name of the comment
     * @param   string  The comment's title
     * @param   string  The comment's message text
     * @global    ADONewConnection
     * @global    array
     * @global    array
     * @global    array
     * @return  array   Returns an array of two elements. The first is either TRUE on success or FALSE on failure.
     *                  The second element contains an error message on failure.  
     */
    private function storeMessageComment($newsMessageId, $newsMessageTitle, $name, $title, $message)
    {
        global $objDatabase, $_ARRAYLANG, $_CORELANG, $_CONFIG;

        // just comment
        if ($this->checkForCommentFlooding($newsMessageId)) {
            return array(   
                false,
                sprintf($_ARRAYLANG['TXT_NEWS_COMMENT_INTERVAL_MSG'],
                        //DateTimeTool::getLiteralStringOfSeconds($this->arrSettings['news_comments_timeout'])),
                        $this->arrSettings['news_comments_timeout']),
            );
        }

        if (empty($title)) {
            return array(false, $_ARRAYLANG['TXT_NEWS_MISSING_COMMENT_TITLE']);
        }

        if (empty($message)) {
            return array(false, $_ARRAYLANG['TXT_NEWS_MISSING_COMMENT_MESSAGE']);
        }


        $date = time();
        $userId = 0;
        if (FWUser::getFWUserObject()->objUser->login()) {
            $userId = FWUser::getFWUserObject()->objUser->getId();
            $name = FWUser::getParsedUserTitle($userId);
        } elseif ($this->arrSettings['news_comments_anonymous'] == '1') {
            // deny comment if the poster did not specify his name
            if (empty($name)) {
                return array(false, $_ARRAYLANG['TXT_NEWS_POSTER_NAME_MISSING']);
            }

            // check CAPTCHA for anonymous posters
            include_once ASCMS_LIBRARY_PATH.'/spamprotection/captcha.class.php';
            $captcha = new Captcha();

            if (!$captcha->check($_POST['news_comment_captcha'])) {
                $error = true;
                return array(false, $_CORELANG['TXT_CORE_INVALID_CAPTCHA']);
            }
        } else {
            // Anonymous comments are not allowed
            return array(false, null);
        }

        $isActive  = $this->arrSettings['news_comments_autoactivate'];
        $ipAddress = contrexx_input2raw($_SERVER['REMOTE_ADDR']);

        $objResult = $objDatabase->Execute("
            INSERT INTO `".DBPREFIX."module_news_comments` 
                    SET `title` = '".contrexx_raw2db($title)."',
                        `text` = '".contrexx_raw2db($message)."',
                        `newsid` = '".contrexx_raw2db($newsMessageId)."',
                        `date` = '".contrexx_raw2db($date)."',
                        `poster_name` = '".contrexx_raw2db($name)."',
                        `userid` = '".contrexx_raw2db($userId)."',
                        `ip_address` = '".contrexx_raw2db($ipAddress)."',
                        `is_active` = '".contrexx_raw2db($isActive)."'");
        if (!$objResult) {
            return array(false, $_ARRAYLANG['TXT_NEWS_COMMENT_SAVE_ERROR']);
        }

        /* Prevent comment flooding from same user:
           Either user is authenticated or had to validate a CAPTCHA.
           In either way, a Contrexx session had been initialized,
           therefore we are able to use the $_SESSION to log this comment */
        $_SESSION['news']['comments'][$newsMessageId] = $date;

        // Don't send a notification e-mail to the administrator
        if (!$this->arrSettings['news_comments_notification']) {
            return array(true, null);
        }

        // Send a notification e-mail to administrator
        if (!@include_once ASCMS_LIBRARY_PATH.'/phpmailer/class.phpmailer.php') {
            DBG::msg('Unable to send e-mail notification to admin');
            DBG::stack();
            return array(true, null);
        }

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

        $objMail->CharSet   = CONTREXX_CHARSET;
        $objMail->From      = $_CONFIG['coreAdminEmail'];
        $objMail->FromName  = $_CONFIG['coreGlobalPageTitle'];
        $objMail->IsHTML(false);
        $objMail->Subject   = sprintf($_ARRAYLANG['TXT_NEWS_COMMENT_NOTIFICATION_MAIL_SUBJECT'], $newsMessageTitle);
        $manageCommentsUrl = ASCMS_PROTOCOL.'://'
                                .$_CONFIG['domainUrl']
                                .($_SERVER['SERVER_PORT'] == 80 ? NULL : ':'.intval($_SERVER['SERVER_PORT']))
                                .ASCMS_ADMIN_WEB_PATH.'/index.php?cmd=news&act=comments&newsId='.$newsMessageId;
        $activateCommentTxt = $this->arrSettings['news_comments_autoactivate']
                              ? ''
                              : sprintf($_ARRAYLANG['TXT_NEWS_COMMENT_NOTIFICATION_MAIL_LINK'], $manageCommentsUrl);
        $objMail->Body      = sprintf($_ARRAYLANG['TXT_NEWS_COMMENT_NOTIFICATION_MAIL_BODY'],
                                        $_CONFIG['domainUrl'],
                                        $newsMessageTitle,
                                        FWUser::getParsedUserTitle($userId, $name),
                                        $title,
                                        nl2br($message),
                                        $activateCommentTxt);
        $objMail->AddAddress($_CONFIG['coreAdminEmail']);
        if (!$objMail->Send()) {
            DBG::msg('Sending of notification e-mail failed');
            DBG::stack();
        }

        return array(true, null);
    }


    /**
     * Check if the current user has already written a comment within
     * the definied timeout-time set by news_comments_timeout.
     *
     * @param   integer News message-ID
     * @global  object
     * @return  boolean TRUE, if the user hast just written a comment before.
     */
    private function checkForCommentFlooding($newsMessageId)
    {
        global $objDatabase;

        //Check cookie first
        if (!empty($_SESSION['news']['comments'][$newsMessageId])) {
            $intLastCommentTime = intval($_SESSION['news']['comments'][$newsMessageId]);
            if (time() < $intLastCommentTime + intval($this->arrSettings['news_comments_timeout'])) {
                //The current system-time is smaller than the time in the session plus timeout-time, so the user just submitted a comment
                return true;
            }
        }

        //Now check database (make sure the user didn't delete the cookie
        $objResult = $objDatabase->SelectLimit("SELECT 1 FROM `".DBPREFIX."module_news_comments`
                                                 WHERE  `ip_address` = '".contrexx_input2db($_SERVER['REMOTE_ADDR'])."'
                                                        AND `date` > ".(time() - intval($this->arrSettings['news_comments_timeout'])));
        if ($objResult && !$objResult->EOF) {
            return true;
        }

        //Nothing found, i guess the user didn't comment within the timeout-period.
        return false;
    }


    private function _getArchive()
    {
        global $_CONFIG, $objDatabase, $_ARRAYLANG, $_CORELANG;

// TODO: The News-Archive function has been disabled for now. Refactor this method before including it into the standard function-set of Contrexx
    
        return;

        $dateFilterName = 'date';
        $monthColumn = 3;
        $objFWUser = FWUser::getFWUserObject();
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
                                   AND nl.lang_id=".FRONTEND_LANG_ID."
                                   AND n.status = 1
                                   " .($this->arrSettings['news_message_protection'] == '1' && !Permission::hasAllAccess() ? (
                                    ($objFWUser = FWUser::getFWUserObject()) && $objFWUser->objUser->login() ?
                                        " AND (frontend_access_id IN (".implode(',', array_merge(array(0), $objFWUser->objUser->getDynamicPermissionIds())).") OR userid = ".$objFWUser->objUser->getId().") "
                                        :   " AND frontend_access_id=0 ")
                                    :   '')
                            ."ORDER BY date ASC";
        $objResult = $objDatabase->Execute($monthCountQuery);
        if ($objResult !== false) {
            $arrMonthTxts = explode(',', $_CORELANG['TXT_MONTH_ARRAY']);

            while (!$objResult->EOF) {
                $filterDate = $objResult->fields[$dateFilterName];
                $newsYear = date('Y', $filterDate);
                $newsMonth = date('m', $filterDate);
                if (!isset($monthlyStats[$newsYear . '_' . $newsMonth])) {
                    $monthlyStats[$newsYear . '_' . $newsMonth] = array();
                    $monthlyStats[$newsYear . '_' . $newsMonth]['name'] = $arrMonthTxts[date('n', $filterDate) - 1].' '.$newsYear;
                }
                $monthlyStats[$newsYear . '_' . $newsMonth]['news'][] = $objResult->fields;
                $objResult->MoveNext();
            }
        }
        if (count($monthlyStats) == 0) {
            $this->_objTpl->setVariable(array(
                'TXT_NEWS_NOT_FOUND' => $_ARRAYLANG['TXT_NEWS_NOT_FOUND'],
            ));
            $this->_objTpl->parse('news_not_found');
            $this->_objTpl->hideBlock('month_news_list');
        } else {
            $this->_objTpl->hideBlock('news_not_found');
            // create columns
            $columnCount = 0;
            $currentColumn = 1;
            $totalCount = count($monthlyStats);
            $columnSize = ceil($totalCount / $monthColumn);
            $this->_objTpl->setCurrentBlock('month_navigation_column');
            foreach ($monthlyStats as $key => $value) {
                $columnCount++;
                if ($columnCount > $currentColumn * $columnSize) {
                    $currentColumn++;
                    $this->_objTpl->parseCurrentBlock();
                    $this->_objTpl->setCurrentBlock('month_navigation_column');
                }
                $this->_objTpl->setVariable(array(
                    'NEWS_MONTH_NAME' => $value['name'],
                    'NEWS_MONTH_ARCHIVE' => count($value['news']),
                    'NEWS_MONTH_KEY' => $key,
                ));
                $this->_objTpl->parse('month_navigation_item');
                foreach ($value['news'] as $news) {
                    $newstitle = contrexx_raw2xhtml($news['newstitle']);
                    $this->_objTpl->setVariable(array(
                        'NEWS_LINK_TITLE'    => (empty($news['newsredirect'])) ? '<a href="'.CONTREXX_SCRIPT_PATH.'?section=news&amp;cmd=details&amp;newsid='.$news['id'].'" title="'.$newstitle.'">'.$newstitle.'</a>' : '<a href="'.$news['newsredirect'].'" title="'.$newstitle.'">'.$newstitle.'</a>',
                    ));
                    $this->_objTpl->parse('month_news_list_item');
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
