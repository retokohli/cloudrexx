 <?php
/**
 * News manager
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author Comvation Development Team <info@comvation.com>
 * @version 1.0.0
 * @package     contrexx
 * @subpackage  core_module_news
 * @todo        Edit PHP DocBlocks!
 *              The news entry management is bulky! It should be rewritten in OOP
 */

/**
 * @ignore
 */
require_once ASCMS_CORE_MODULE_PATH . '/news/lib/newsLib.class.php';

/**
 * News manager
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author Comvation Development Team <info@comvation.com>
 * @access public
 * @version 1.0.0
 * @package     contrexx
 * @subpackage  core_module_news
 */
class newsManager extends newsLibrary {
    var $_objTpl;
    var $pageTitle;
    var $pageContent;
    var $strOkMessage;
    var $strErrMessage;
    var $_selectedLang;
    var $langId;
    var $arrSettings = array();
    var $_defaultTickerFileName = 'newsticker.txt';
    var $_arrCharsets = array(
        // European languages
        /*'ASCII',*/
        'ISO-8859-1',
        /*'ISO-8859-2',
        'ISO-8859-3',
        'ISO-8859-4',
        'ISO-8859-5',
        'ISO-8859-7',
        'ISO-8859-9',
        'ISO-8859-10',
        'ISO-8859-13',
        'ISO-8859-14',
        'ISO-8859-15',
        'ISO-8859-16',
        'KOI8-R',
        'KOI8-U',
        'KOI8-RU',
        'CP1250',
        'CP1251',
        'CP1252',
        'CP1253',
        'CP1254',
        'CP1257',
        'CP850',
        'CP866',
        'MacRoman',
        'MacCentralEurope',
        'MacIceland',
        'MacCroatian',
        'MacRomania',
        'MacCyrillic',
        'MacUkraine',
        'MacGreek',
        'MacTurkish',
        'Macintosh',
        // Semitic languages
        'ISO-8859-6',
        'ISO-8859-8',
        'CP1255',
        'CP1256',
        'CP862',
        'MacHebrew',
        'MacArabic',
        // Japanese
        'EUC-JP',
        'SHIFT_JIS',
        'CP932',
        'ISO-2022-JP',
        'ISO-2022-JP-2',
        'ISO-2022-JP-1',
        // Chinese
        'EUC-CN',
        'HZ',
        'GBK',
        'GB18030',
        'EUC-TW',
        'BIG5',
        'CP950',
        'BIG5-HKSCS',
        'ISO-2022-CN',
        'ISO-2022-CN-EXT',
        // Korean
        'EUC-KR',
        'CP949',
        'ISO-2022-KR',
        'JOHAB',
        // Armenian
        'ARMSCII-8',
        // Georgian
        'Georgian-Academy',
        'Georgian-PS',
        // Tajik
        'KOI8-T',
        // Thai
        'TIS-620',
        'CP874',
        'MacThai',
        // Laotian
        'MuleLao-1',
        'CP1133',
        // Vietnamese
        'VISCII',
        'TCVN',
        'CP1258',
        // Platform specifics
        'HP-ROMAN8',
        'NEXTSTEP',
        // Full Unicode*/
        'UTF-8',
        /*'UCS-2',
        'UCS-2BE',
        'UCS-2LE',
        'UCS-4',
        'UCS-4BE',
        'UCS-4LE',
        'UTF-16',
        'UTF-16BE',
        'UTF-16LE',
        'UTF-32',
        'UTF-32BE',
        'UTF-32LE',
        'UTF-7',
        'C99',
        'JAVA',*/
    );

    /**
    * Teaser object
    *
    * @access private
    * @var object
    */
    var $_objTeaser;

    /**
    * PHP4 Constructor
    *
    * @access public
    */
    function newsManager()
    {
        $this->__construct();
    }

    /**
    * PHP5 Constructor
    *
    * @access public
    */
    function __construct()
    {
        global  $_ARRAYLANG, $objInit, $objTemplate, $_CONFIG;

        $this->_objTpl = &new HTML_Template_Sigma(ASCMS_CORE_MODULE_PATH.'/news/template');
        CSRF::add_placeholder($this->_objTpl);
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);

        $this->_saveSettings();

        $objTemplate->setVariable("CONTENT_NAVIGATION","<a href='index.php?cmd=news'>".$_ARRAYLANG['TXT_NEWS_MANAGER']."</a>
            <a href='index.php?cmd=news&amp;act=add'>".$_ARRAYLANG['TXT_CREATE_NEWS']."</a>
            <a href='index.php?cmd=news&amp;act=newscat'>".$_ARRAYLANG['TXT_CATEGORY_MANAGER']."</a>
            <a href='index.php?cmd=news&amp;act=ticker'>".$_ARRAYLANG['TXT_NEWS_NEWSTICKER']."</a>
            ".($_CONFIG['newsTeasersStatus'] == '1' ? "<a href='index.php?cmd=news&amp;act=teasers'>".$_ARRAYLANG['TXT_TEASERS']."</a>" : "")."
            <a href='index.php?cmd=news&amp;act=placeholders'>".$_ARRAYLANG['TXT_PLACEHOLDER_DIRECTORY']."</a>
            <a href='index.php?cmd=news&amp;act=settings'>".$_ARRAYLANG['TXT_NEWS_SETTINGS']."</a>"
        );

        $this->pageTitle = $_ARRAYLANG['TXT_NEWS_MANAGER'];
        $this->langId = $objInit->userFrontendLangId;
        $this->getSettings();
    }

    /**
    * Do the requested newsaction
    *
    * @global    HTML_Template_Sigma
    * @return    string    parsed content
    */
    function getPage()
    {
        global $objTemplate;

        if (!isset($_GET['act'])) {
            $_GET['act'] = '';
        }

        switch ($_GET['act']) {
            case 'add':
                $this->add();
                break;

            case 'edit':
                $this->edit();
                break;

            case 'copy':
                $this->edit(true);
                break;

            case 'delete':
                $this->delete();
                $this->overview();
                break;

            case 'update':
                $this->update();
                $this->overview();
                break;

            case 'newscat':
                $this->manageCategories();
                break;

            case 'delcat':
                $this->deleteCat();
                $this->manageCategories();
                break;

            case 'changeStatus':
                $this->changeStatus();
                $this->overview();
                break;

            case 'invertStatus':
                $this->invertStatus($_GET['newsId']);
                $this->overview();
                break;

            case 'settings':
                $this->settings();
                break;

            case 'rss':
                $this->rss();
                break;

            case 'ticker':
                $this->_ticker();
                break;

            case 'teasers':
                $this->_teasers();
                break;

            case 'placeholders':
                $this->_placeholders();
                break;

            default:
                (intval($this->arrSettings['news_settings_activated'])==0) ? $this->settings() : $this->overview();
                break;
        }

        $objTemplate->setVariable(array(
            'CONTENT_TITLE'             => $this->pageTitle,
            'CONTENT_OK_MESSAGE'        => $this->strOkMessage,
            'CONTENT_STATUS_MESSAGE'    => $this->strErrMessage,
            'ADMIN_CONTENT'             => $this->_objTpl->get()
        ));
    }



    /**
    * List up the news for edit or delete
    *
    * @global    ADONewConnection
    * @global    array
    * @global    array
    * @param     integer   $newsid
    * @param     string    $what
    * @access  private
    * @todo     use SQL_CALC_FOUND_ROWS and drop 'n.validated' in where clause instead of calling same query four times
    */
    function overview()
    {
        global $objDatabase, $_ARRAYLANG, $_CONFIG;

        if (!count($this->getCategories())) {
            return $this->manageCategories();
        }

        $query = 'SELECT 1 FROM `'.DBPREFIX.'module_news` WHERE `lang` = '.$this->langId;
        $objNewsCount = $objDatabase->SelectLimit($query, 1);
        if ($objNewsCount === false || $objNewsCount->RecordCount() == 0) {
            return $this->add();
        }

        $objFWUser = FWUser::getFWUserObject();

        // initialize variables
        $paging = "";

        $this->_objTpl->loadTemplateFile('module_news_overview.html',true,true);
        $this->pageTitle = $_ARRAYLANG['TXT_NEWS_MANAGER'];

        $messageNr = 0;
        $validatorNr = 0;

        $this->_objTpl->setVariable(array(
            'TXT_EDIT_NEWS_MESSAGE'      => $_ARRAYLANG['TXT_EDIT_NEWS_MESSAGE'],
            'TXT_EDIT_NEWS_ID'           => $_ARRAYLANG['TXT_EDIT_NEWS_MESSAGE'],
            'TXT_ID'                     => $_ARRAYLANG['TXT_ID'],
            'TXT_DATE'                   => $_ARRAYLANG['TXT_DATE'],
            'TXT_TITLE'                  => $_ARRAYLANG['TXT_TITLE'],
            'TXT_USER'                   => $_ARRAYLANG['TXT_USER'],
            'TXT_LAST_EDIT'              => $_ARRAYLANG['TXT_LAST_EDIT'],
            'TXT_ACTION'                 => $_ARRAYLANG['TXT_ACTION'],
	    'TXT_REPUBLISHING'           => $_ARRAYLANG['TXT_REPUBLISHING'],
            'TXT_CATEGORY'               => $_ARRAYLANG['TXT_CATEGORY'],
            'TXT_CONFIRM_DELETE_DATA'    => $_ARRAYLANG['TXT_NEWS_DELETE_CONFIRM'],
            'TXT_ACTION_IS_IRREVERSIBLE' => $_ARRAYLANG['TXT_ACTION_IS_IRREVERSIBLE'],
            'TXT_SELECT_ALL'             => $_ARRAYLANG['TXT_SELECT_ALL'],
            'TXT_REMOVE_SELECTION'       => $_ARRAYLANG['TXT_REMOVE_SELECTION'],
            'TXT_DELETE_MARKED'          => $_ARRAYLANG['TXT_DELETE_MARKED'],
            'TXT_MARKED'                 => $_ARRAYLANG['TXT_MARKED'],
            'TXT_ACTIVATE'               => $_ARRAYLANG['TXT_ACTIVATE'],
            'TXT_DEACTIVATE'             => $_ARRAYLANG['TXT_DEACTIVATE'],
            'TXT_STATUS'                 => $_ARRAYLANG['TXT_STATUS'],
            'TXT_CONFIRM_AND_ACTIVATE'  => $_ARRAYLANG['TXT_CONFIRM_AND_ACTIVATE'],
            'TXT_INVALIDATED_ENTRIES'   => $_ARRAYLANG['TXT_INVALIDATED_ENTRIES']
        ));

        $this->_objTpl->setGlobalVariable(array(
            'TXT_ARCHIVE'                   => $_ARRAYLANG['TXT_ARCHIVE'],
            'TXT_EDIT'                      => $_ARRAYLANG['TXT_EDIT'],
            'TXT_COPY'                      => $_ARRAYLANG['TXT_COPY'],
            'TXT_DELETE'                    => $_ARRAYLANG['TXT_DELETE'],
            'TXT_NEWS_MESSAGE_PROTECTED'    => $_ARRAYLANG['TXT_NEWS_MESSAGE_PROTECTED'],
            'TXT_NEWS_READ_ALL_ACCESS_DESC' => $_ARRAYLANG['TXT_NEWS_READ_ALL_ACCESS_DESC']
        ));

        // set archive list
        $query = "SELECT n.id AS id,
                         n.date AS date,
                         n.changelog AS changelog,
                         n.title AS title,
                         n.status AS status,
                         n.validated AS validated,
                         n.frontend_access_id,
                         n.userid,
                         l.name AS name,
                         nc.name AS catname
                    FROM ".DBPREFIX."module_news_categories AS nc,
                         ".DBPREFIX."languages AS l,
                         ".DBPREFIX."module_news AS n
                   WHERE n.lang=l.id
                     AND n.lang=".$this->langId."
                     AND nc.catid=n.catid
                     AND n.validated='1'"
                     .($this->arrSettings['news_message_protection'] == '1' && !Permission::hasAllAccess() ? " AND (n.backend_access_id IN (".implode(',', array_merge(array(0), $objFWUser->objUser->getDynamicPermissionIds())).") OR n.userid = ".$objFWUser->objUser->getId().") " : '')
                ."ORDER BY date DESC";
        $objResult = $objDatabase->Execute($query);
        if ($objResult !== false) {
            $count = $objResult->RecordCount();

            if (isset($_GET['show']) && $_GET['show'] == 'archive' && isset($_GET['pos'])) {
                $pos = intval($_GET['pos']);
            } else {
                $pos = 0;
            }

            if ($count>intval($_CONFIG['corePagingLimit'])) {
                $paging = getPaging($count, $pos, "&amp;cmd=news&amp;show=archive", $_ARRAYLANG['TXT_NEWS_MESSAGES'],true);
            }
            $objResult = $objDatabase->SelectLimit($query, $_CONFIG['corePagingLimit'], $pos);

            if ($count<1) {
                $this->_objTpl->hideBlock('newstable');
            } else {
                while (!$objResult->EOF) {
                    $statusPicture = "status_red.gif";
                    if($objResult->fields['status']==1) {
                        $statusPicture = "status_green.gif";
                    }

                    ($messageNr % 2) ? $class = "row2" : $class = "row1";
                    $messageNr++;

                    if ($objResult->fields['userid'] && ($objUser = $objFWUser->objUser->getUser($objResult->fields['userid']))) {
                        $author = htmlentities($objUser->getUsername(), ENT_QUOTES, CONTREXX_CHARSET);
                    } else {
                        $author = $_ARRAYLANG['TXT_ANONYMOUS'];
                    }

		    /*require_once('../lib/SocialNetworks.class.php');
		    $socialNetworkTemplater = new SocialNetworks();

		    $socialNetworkTemplater->setUrl($_CONFIG['domainUrl'].ASCMS_PATH_OFFSET.'/index.php?section=news&cmd=details&newsid='.$objResult->fields['id']);*/

                    $this->_objTpl->setVariable(array(
                        'NEWS_ID'               => $objResult->fields['id'],
                        'NEWS_DATE'             => date(ASCMS_DATE_FORMAT, $objResult->fields['date']),
                        'NEWS_TITLE'            => contrexx_raw2html($objResult->fields['title']),
                        'NEWS_USER'             => $author,
                        'NEWS_CHANGELOG'        => date(ASCMS_DATE_FORMAT, $objResult->fields['changelog']),
                        'NEWS_LIST_PARSING'     => $paging,
                        'NEWS_CLASS'            => $class,
                        'NEWS_CATEGORY'         => stripslashes($objResult->fields['catname']),
                        'NEWS_STATUS'           => $objResult->fields['status'],
                        'NEWS_STATUS_PICTURE'   => $statusPicture,
			//'NEWS_FACEBOOK_SHARE_BUTTON'  => $socialNetworkTemplater->getFacebookShareButton(),
                    ));

                    $this->_objTpl->setVariable(array(
                        'NEWS_ACTIVATE'         =>  $_ARRAYLANG['TXT_ACTIVATE'],
                        'NEWS_DEACTIVATE'       =>  $_ARRAYLANG['TXT_DEACTIVATE']
                    ));

                    if ($this->arrSettings['news_message_protection'] == '1' && $objResult->fields['frontend_access_id']) {
                        $this->_objTpl->touchBlock('news_message_protected_icon');
                        $this->_objTpl->hideBlock('news_message_not_protected_icon');
                    } else {
                        $this->_objTpl->touchBlock('news_message_not_protected_icon');
                        $this->_objTpl->hideBlock('news_message_protected_icon');
                    }

                    $this->_objTpl->parse('newsrow');
                    $objResult->MoveNext();
                }
            }
        }

        // set unvalidated list
        $query = "SELECT n.id AS id,
                         n.date AS date,
                         n.changelog AS changelog,
                         n.title AS title,
                         n.status AS status,
                         n.validated AS validated,
                         n.userid,
                         l.name AS name,
                         nc.name AS catname
                    FROM ".DBPREFIX."module_news_categories AS nc,
                         ".DBPREFIX."languages AS l,
                         ".DBPREFIX."module_news AS n
                   WHERE n.lang=l.id
                     AND n.lang=".$this->langId."
                     AND nc.catid=n.catid
                     AND n.validated='0'
                ORDER BY date DESC";

        $objResult = $objDatabase->Execute($query);

        if ($objResult !== false) {
            $count = $objResult->RecordCount();
            if (isset($_GET['show']) && $_GET['show'] == 'archive' && isset($_GET['pos'])) {
                $pos = 0;
            } else {
                $pos = isset($_GET['pos']) ? intval($_GET['pos']) : 0;
            }

            if ($count>intval($_CONFIG['corePagingLimit'])) {
                $paging = getPaging($count, $pos, "&amp;cmd=news", $_ARRAYLANG['TXT_NEWS_MESSAGES'],true);
            } else {
                $paging = "";
            }
            $objResult = $objDatabase->SelectLimit($query, $_CONFIG['corePagingLimit'], $pos);

            if ($count < 1) {
                $this->_objTpl->hideBlock('news_tabmenu');
                $this->_objTpl->hideBlock('news_validator');
                $this->_objTpl->setVariable('NEWS_ARCHIVE_DISPLAY_STATUS', "block");
            } else {
                if (isset($_GET['show']) && $_GET['show'] == 'archive') {
                    $this->_objTpl->setVariable(array(
                        'NEWS_ARCHIVE_DISPLAY_STATUS'       => "block",
                        'NEWS_UNVALIDATED_DISPLAY_STATUS'   => "none",
                        'NEWS_ARCHIVE_TAB_CALSS'            => "class=\"active\"",
                        'NEWS_UNVALIDATED_TAB_CALSS'        => ""
                    ));
                } else {
                    $this->_objTpl->setVariable(array(
                        'NEWS_ARCHIVE_DISPLAY_STATUS'       => "none",
                        'NEWS_UNVALIDATED_DISPLAY_STATUS'   => "block",
                        'NEWS_ARCHIVE_TAB_CALSS'            => "",
                        'NEWS_UNVALIDATED_TAB_CALSS'        => "class=\"active\""
                    ));
                }

                $this->_objTpl->setVariable(array(
                    'NEWS_LIST_UNVALIDATED_PARSING'     => $paging,
                ));

                $this->_objTpl->touchBlock('news_tabmenu');

                while (!$objResult->EOF) {
                    ($validatorNr % 2) ? $class = "row2" : $class = "row1";
                    $validatorNr++;

                    $statusPicture = "status_red.gif";
                    if($objResult->fields['status']==1) {
                        $statusPicture = "status_green.gif";
                    }

                    if ($objResult->fields['userid'] && ($objUser = $objFWUser->objUser->getUser($objResult->fields['userid']))) {
                        $author = htmlentities($objUser->getUsername(), ENT_QUOTES, CONTREXX_CHARSET);
                    } else {
                        $author = $_ARRAYLANG['TXT_ANONYMOUS'];
                    }

                    $this->_objTpl->setVariable(array(
                        'NEWS_ID'               => $objResult->fields['id'],
                        'NEWS_DATE'             => date(ASCMS_DATE_FORMAT, $objResult->fields['date']),
                        'NEWS_TITLE'            => contrexx_raw2html($objResult->fields['title']),
                        'NEWS_USER'             => $author,
                        'NEWS_CHANGELOG'        => date(ASCMS_DATE_FORMAT, $objResult->fields['changelog']),
                        'NEWS_CLASS'            => $class,
                        'NEWS_CATEGORY'         => stripslashes($objResult->fields['catname']),
                        'NEWS_STATUS'           => $objResult->fields['status'],
                        'NEWS_STATUS_PICTURE'   => $statusPicture,
                    ));

                    $this->_objTpl->parse('news_validator_row');
                    $objResult->MoveNext();
                }
            }
        }
    }




    /**
    * adds a news entry
    *
    * @global   array
    * @global   array
    * @global   ADONewConnection
    */
    function add()
    {
        global $_ARRAYLANG, $_CONFIG, $objDatabase;

        if (!count($this->getCategories())) {
            return $this->manageCategories();
        }

        $objFWValidator = &new FWValidator();
        $objFWUser = FWUser::getFWUserObject();

        if (preg_match('/^([0-9]{1,2})\:([0-9]{1,2})\:([0-9]{1,2})\s*([0-9]{1,2})\.([0-9]{1,2})\.([0-9]{1,4})/', $_POST['newsDate'], $arrDate)) {
            $date = mktime(intval($arrDate[1]), intval($arrDate[2]), intval($arrDate[3]), intval($arrDate[5]), intval($arrDate[4]), intval($arrDate[6]));
        } else {
            $date = time();
        }
        $newstitle              = contrexx_addslashes($_POST['newsTitle']);
        $newstext               = contrexx_addslashes($_POST['newsText']);
        $newstext               = $this->filterBodyTag($newstext);
        $newsredirect           = contrexx_strip_tags($_POST['newsRedirect']);
        $newssource             = $objFWValidator->getUrl(contrexx_strip_tags($_POST['newsSource']));
        $newsurl1               = $objFWValidator->getUrl(contrexx_strip_tags($_POST['newsUrl1']));
        $newsurl2               = $objFWValidator->getUrl(contrexx_strip_tags($_POST['newsUrl2']));
        $newscat                = intval($_POST['newsCat']);
        $userid                 = $objFWUser->objUser->getId();
        $startDate              = (!preg_match('/^\d{4}-\d{2}-\d{2}(\s+\d{2}:\d{2}:\d{2})?$/',$_POST['startDate'])) ? '0000-00-00 00:00:00' : $_POST['startDate'];
        $endDate                = (!preg_match('/^\d{4}-\d{2}-\d{2}(\s+\d{2}:\d{2}:\d{2})?$/',$_POST['endDate'])) ? '0000-00-00 00:00:00' : $_POST['endDate'];
        $status                 = intval($_POST['status']);
        $newsTeaserOnly         = isset($_POST['newsUseOnlyTeaser']) ? intval($_POST['newsUseOnlyTeaser']) : 0;
        $newsTeaserText         = contrexx_addslashes($_POST['newsTeaserText']);
        $newsTeaserImagePath    = contrexx_addslashes($_POST['newsTeaserImagePath']);
        $newsTeaserImageThumbnailPath    = contrexx_addslashes($_POST['newsTeaserImageThumbnailPath']);
        $newsTeaserShowLink     = isset($_POST['newsTeaserShowLink']) ? intval($_POST['newsTeaserShowLink']) : intval(!count($_POST));
        $newsTeaserFrames       = '';
        $arrNewsTeaserFrames = array();
        $newsFrontendAccess     = !empty($_POST['news_read_access']);
        $newsFrontendGroups     = $newsFrontendAccess && isset($_POST['news_read_access_associated_groups']) && is_array($_POST['news_read_access_associated_groups']) ? array_map('intval', $_POST['news_read_access_associated_groups']) : array();
        $newsBackendAccess     = !empty($_POST['news_modify_access']);
        $newsBackendGroups     = $newsBackendAccess && isset($_POST['news_modify_access_associated_groups']) && is_array($_POST['news_modify_access_associated_groups']) ? array_map('intval', $_POST['news_modify_access_associated_groups']) : array();

        if (isset($_POST['newsTeaserFramesAsso']) && count($_POST['newsTeaserFramesAsso'])>0) {
            foreach ($_POST['newsTeaserFramesAsso'] as $frameId) {
                $arrNewsTeaserFrames[] = intval($frameId);
                intval($frameId) > 0 ? $newsTeaserFrames .= ";".intval($frameId) : false;
            }
        }

        if(empty($status)) {
            $status = 0;
        }

        if ($this->arrSettings['news_message_protection'] == '1' && $newsFrontendAccess) {
            if ($this->arrSettings['news_message_protection_restricted'] == '1' && !Permission::hasAllAccess()) {
                $arrUserGroupIds = $objFWUser->objUser->getAssociatedGroupIds();

                $newsFrontendGroups = array_intersect($newsFrontendGroups, $arrUserGroupIds);
            }

            $newsFrontendAccessId = Permission::createNewDynamicAccessId();
            if (count($newsFrontendGroups)) {
                Permission::setAccess($newsFrontendAccessId, 'dynamic', $newsFrontendGroups);
            }
        } else {
            $newsFrontendAccessId = 0;
        }

        if ($this->arrSettings['news_message_protection'] == '1' && $newsBackendAccess) {
            if ($this->arrSettings['news_message_protection_restricted'] == '1' && !Permission::hasAllAccess()) {
                $arrUserGroupIds = $objFWUser->objUser->getAssociatedGroupIds();

                $newsBackendGroups = array_intersect($newsBackendGroups, $arrUserGroupIds);
            }

            $newsBackendAccessId = Permission::createNewDynamicAccessId();
            if (count($newsBackendGroups)) {
                Permission::setAccess($newsBackendAccessId, 'dynamic', $newsBackendGroups);
            }
        } else {
            $newsBackendAccessId = 0;
        }

        $objFWUser->objUser->getDynamicPermissionIds(true);

        if (!empty($newstitle)){
            $objResult = $objDatabase->Execute('INSERT
                                            INTO '.DBPREFIX.'module_news
                                            SET date='.$date.',
                                                title="'.$newstitle.'",
                                                text="'.$newstext.'",
                                                redirect="'.$newsredirect.'",
                                                source="'.$newssource.'",
                                                url1="'.$newsurl1.'",
                                                url2="'.$newsurl2.'",
                                                catid='.$newscat.',
                                                lang='.$this->langId.',
                                                startdate="'.$startDate.'",
                                                enddate="'.$endDate.'",
                                                status='.$status.',
                                                validated="1",
                                                frontend_access_id="'.$newsFrontendAccessId.'",
                                                backend_access_id="'.$newsBackendAccessId.'",
                                                teaser_only="'.$newsTeaserOnly.'",
                                                teaser_frames="'.$newsTeaserFrames.'",
                                                teaser_text="'.$newsTeaserText.'",
                                                teaser_show_link="'.$newsTeaserShowLink.'",
                                                teaser_image_path="'.$newsTeaserImagePath.'",
                                                teaser_image_thumbnail_path="'.$newsTeaserImageThumbnailPath.'",
                                                userid='.$userid.',
                                                changelog='.$date
                                        );

            if ($objResult !== false) {
                $this->strOkMessage = $_ARRAYLANG['TXT_DATA_RECORD_ADDED_SUCCESSFUL'];
                $this->createRSS();
                return $this->overview();
            } else {
                $this->strErrMessage = $_ARRAYLANG['TXT_DATABASE_QUERY_ERROR'];
            }
        }


        $this->_objTpl->loadTemplateFile('module_news_modify.html');
        $this->pageTitle = $_ARRAYLANG['TXT_CREATE_NEWS'];

        require_once ASCMS_CORE_MODULE_PATH . '/news/lib/teasers.class.php';
        $objTeaser = &new Teasers(true);

        $frameIds = "";
        foreach ($objTeaser->arrTeaserFrameNames as $frameName => $frameId) {
            if (in_array($frameId, $arrNewsTeaserFrames)) {
                $associatedFrameIds .= "<option value=\"".$frameId."\">".$frameName."</option>\n";
            } else {
            $frameIds .= "<option value=\"".$frameId."\">".$frameName."</option>\n";
        }
        }

        $this->_objTpl->setVariable(array(
            'TXT_NEWS_MESSAGE'              => $_ARRAYLANG['TXT_NEWS_MESSAGE'],
            'TXT_TITLE'                     => $_ARRAYLANG['TXT_TITLE'],
            'TXT_CATEGORY'                  => $_ARRAYLANG['TXT_CATEGORY'],
            'TXT_HYPERLINKS'                => $_ARRAYLANG['TXT_HYPERLINKS'],
            'TXT_EXTERNAL_SOURCE'           => $_ARRAYLANG['TXT_EXTERNAL_SOURCE'],
            'TXT_LINK'                      => $_ARRAYLANG['TXT_LINK'],
            'TXT_NEWS_NEWS_CONTENT'         => $_ARRAYLANG['TXT_NEWS_NEWS_CONTENT'],
            'TXT_DATE'                      => $_ARRAYLANG['TXT_DATE'],
            'TXT_PUBLISHING'                => $_ARRAYLANG['TXT_PUBLISHING'],
            'TXT_STARTDATE'                 => $_ARRAYLANG['TXT_STARTDATE'],
            'TXT_ENDDATE'                   => $_ARRAYLANG['TXT_ENDDATE'],
            'TXT_OPTIONAL'                  => $_ARRAYLANG['TXT_OPTIONAL'],
            'TXT_ACTIVE'                    => $_ARRAYLANG['TXT_ACTIVE'],
            'TXT_HEADLINES'                 => $_ARRAYLANG['TXT_HEADLINES'],
            'TXT_TEASERS'                   => $_ARRAYLANG['TXT_TEASERS'],
            'TXT_NEWS_TEASER_TEXT'          => $_ARRAYLANG['TXT_NEWS_TEASER_TEXT'],
            'TXT_IMAGE'                     => $_ARRAYLANG['TXT_IMAGE'],
            'TXT_NEWS_THUMBNAIL'            => $_ARRAYLANG['TXT_NEWS_THUMBNAIL'],
            'TXT_BROWSE'                    => $_ARRAYLANG['TXT_BROWSE'],
            'TXT_NUMBER_OF_CHARS'           => $_ARRAYLANG['TXT_NUMBER_OF_CHARS'],
            'TXT_TEASER_SHOW_NEWS_LINK'     => $_ARRAYLANG['TXT_TEASER_SHOW_NEWS_LINK'],
            'TXT_NEWS_TYPE'                 => $_ARRAYLANG['TXT_NEWS_TYPE'],
            'TXT_NEWS_TYPE_REDIRECT'        => $_ARRAYLANG['TXT_NEWS_REDIRECT_TITLE'],
            'TXT_NEWS_TYPE_REDIRECT_HELP'   => $_ARRAYLANG['TXT_NEWS_TYPE_REDIRECT_HELP'],
            'TXT_NEWS_TYPE_DEFAULT'         => $_ARRAYLANG['TXT_NEWS_TYPE_DEFAULT'],
            'TXT_NEWS_DEFINE_LINK_ALT_TEXT' => $_ARRAYLANG['TXT_NEWS_DEFINE_LINK_ALT_TEXT'],
            'TXT_NEWS_INSERT_LINK'          => $_ARRAYLANG['TXT_NEWS_INSERT_LINK'],
            'TXT_NEWS_BASIC_DATA'           => $_ARRAYLANG['TXT_BASIC_DATA'],
            'TXT_NEWS_MORE_OPTIONS'         => $_ARRAYLANG['TXT_MORE_OPTIONS'],
            'TXT_NEWS_PERMISSIONS'          => $_ARRAYLANG['TXT_NEWS_PERMISSIONS'],
            'TXT_NEWS_READ_ACCESS'          => $_ARRAYLANG['TXT_NEWS_READ_ACCESS'],
            'TXT_NEWS_MODIFY_ACCESS'        => $_ARRAYLANG['TXT_NEWS_MODIFY_ACCESS'],
            'TXT_NEWS_AVAILABLE_USER_GROUPS'    => $_ARRAYLANG['TXT_NEWS_AVAILABLE_USER_GROUPS'],
            'TXT_NEWS_ASSIGNED_USER_GROUPS' => $_ARRAYLANG['TXT_NEWS_ASSIGNED_USER_GROUPS'],
            'TXT_NEWS_CHECK_ALL'            => $_ARRAYLANG['TXT_NEWS_CHECK_ALL'],
            'TXT_NEWS_UNCHECK_ALL'          => $_ARRAYLANG['TXT_NEWS_UNCHECK_ALL'],
            'TXT_NEWS_READ_ALL_ACCESS_DESC' => $_ARRAYLANG['TXT_NEWS_READ_ALL_ACCESS_DESC'],
            'TXT_NEWS_READ_SELECTED_ACCESS_DESC'    => $_ARRAYLANG['TXT_NEWS_READ_SELECTED_ACCESS_DESC'],
            'TXT_NEWS_MODIFY_ALL_ACCESS_DESC'       => $_ARRAYLANG['TXT_NEWS_MODIFY_ALL_ACCESS_DESC'],
            'TXT_NEWS_MODIFY_SELECTED_ACCESS_DESC'  => $_ARRAYLANG['TXT_NEWS_MODIFY_SELECTED_ACCESS_DESC'],
            'NEWS_TEXT'                     => get_wysiwyg_editor('newsText', contrexx_stripslashes($newstext)),
            'NEWS_TITLE'                    => contrexx_stripslashes(htmlspecialchars($newstitle, ENT_QUOTES, CONTREXX_CHARSET)),
            'NEWS_FORM_ACTION'              => "add",
            'NEWS_STORED_FORM_ACTION'       => "add",
            'NEWS_STATUS'                   => $status ? 'checked="checked"' : '',
            'NEWS_ID'                       => "0",
            'NEWS_TOP_TITLE'                => $_ARRAYLANG['TXT_CREATE_NEWS'],
            'NEWS_CAT_MENU'                 => $this->getCategoryMenu($this->langId, $newscat),
            'NEWS_STARTDATE'                => $startDate,
            'NEWS_ENDDATE'                  => $endDate,
            'NEWS_DATE'                     => date('Y-m-d H:i:s'),
            'NEWS_CREATE_DATE'              => date(ASCMS_DATE_FORMAT),
            'NEWS_SOURCE'                   => htmlentities($newssource, ENT_QUOTES, CONTREXX_CHARSET),
            'NEWS_URL1'                     => htmlentities($newsurl1, ENT_QUOTES, CONTREXX_CHARSET),
            'NEWS_URL2'                     => htmlentities($newsurl2, ENT_QUOTES, CONTREXX_CHARSET),
            'NEWS_SUBMIT_NAME_TEXT'         => $_ARRAYLANG['TXT_STORE'],
            'NEWS_TEASER_SHOW_LINK_CHECKED' => $newsTeaserShowLink ? 'checked="checked"' : '',
            'NEWS_TEASER_TEXT'              => htmlentities(contrexx_stripslashes($newsTeaserText), ENT_QUOTES, CONTREXX_CHARSET),
            'NEWS_TEASER_TEXT_LENGTH'       => strlen($newsTeaserText),
            'NEWS_TYPE_SELECTION_CONTENT'   => empty($newsredirect) ? 'style="display: block;"' : 'style="display: none"',
            'NEWS_TYPE_SELECTION_REDIRECT'  => empty($newsredirect) ? 'style="display: none;"' : 'style="display: block"',
            'NEWS_TYPE_CHECKED_CONTENT'     => empty($newsredirect) ? 'checked="checked"' : '',
            'NEWS_TYPE_CHECKED_REDIRECT'    => empty($newsredirect) ? '' : 'checked="checked"',
            'NEWS_TEASER_IMAGE_PATH'        => htmlentities($newsTeaserImagePath, ENT_QUOTES, CONTREXX_CHARSET),
            'NEWS_TEASER_IMAGE_THUMBNAIL_PATH' => htmlentities($newsTeaserImageThumbnailPath, ENT_QUOTES, CONTREXX_CHARSET)
        ));

        if ($_CONFIG['newsTeasersStatus'] == '1') {
            $this->_objTpl->parse('newsTeaserOptions');
            $this->_objTpl->setVariable(array(
                'TXT_USAGE'                     => $_ARRAYLANG['TXT_USAGE'],
                'TXT_USE_ONLY_TEASER_TEXT'      => $_ARRAYLANG['TXT_USE_ONLY_TEASER_TEXT'],
                'TXT_TEASER_TEASER_BOXES'       => $_ARRAYLANG['TXT_TEASER_TEASER_BOXES'],
                'TXT_AVAILABLE_BOXES'           => $_ARRAYLANG['TXT_AVAILABLE_BOXES'],
                'TXT_SELECT_ALL'                => $_ARRAYLANG['TXT_SELECT_ALL'],
                'TXT_DESELECT_ALL'              => $_ARRAYLANG['TXT_DESELECT_ALL'],
                'TXT_ASSOCIATED_BOXES'          => $_ARRAYLANG['TXT_ASSOCIATED_BOXES'],
                'NEWS_HEADLINES_TEASERS_TXT'    => $_ARRAYLANG['TXT_HEADLINES'].' / '.$_ARRAYLANG['TXT_TEASERS'],
                'NEWS_USE_ONLY_TEASER_CHECKED'  => $newsTeaserOnly ? 'checked="checked"' : '',
                'NEWS_TEASER_FRAMES'            => $frameIds,
                'NEWS_TEASER_ASSOCIATED_FRAMES' => $associatedFrameIds
            ));
        } else {
            $this->_objTpl->hideBlock('newsTeaserOptions');
            $this->_objTpl->setVariable('NEWS_HEADLINES_TEASERS_TXT', $_ARRAYLANG['TXT_HEADLINES']);
        }

        if ($this->arrSettings['news_message_protection'] == '1') {
            if ($this->arrSettings['news_message_protection_restricted'] == '1') {
                $userGroupIds = $objFWUser->objUser->getAssociatedGroupIds();
            }

            $readAccessGroups = '';
            $modifyAccessGroups = '';
            $objGroup = $objFWUser->objGroup->getGroups();
            if ($objGroup) {
            while (!$objGroup->EOF) {
                if (Permission::hasAllAccess() || $this->arrSettings['news_message_protection_restricted'] != '1' || in_array($objGroup->getId(), $userGroupIds)) {
                    ${$objGroup->getType() == 'frontend' ? 'readAccessGroups' : 'modifyAccessGroups'} .= '<option value="'.$objGroup->getId().'">'.htmlentities($objGroup->getName(), ENT_QUOTES, CONTREXX_CHARSET).'</option>';
                }
                $objGroup->next();
                }
            }

            $this->_objTpl->setVariable(array(
                'NEWS_READ_ACCESS_NOT_ASSOCIATED_GROUPS'    => $readAccessGroups,
                'NEWS_READ_ACCESS_ASSOCIATED_GROUPS'        => '',
                'NEWS_READ_ACCESS_ALL_CHECKED'              => 'checked="checked"',
                'NEWS_READ_ACCESS_DISPLAY'                  => 'none',
                'NEWS_MODIFY_ACCESS_NOT_ASSOCIATED_GROUPS'  => $modifyAccessGroups,
                'NEWS_MODIFY_ACCESS_ASSOCIATED_GROUPS'      => '',
                'NEWS_MODIFY_ACCESS_ALL_CHECKED'            => 'checked="checked"',
                'NEWS_MODIFY_ACCESS_DISPLAY'                => 'none'
            ));

            $this->_objTpl->parse('news_permission_tab');
        } else {
            $this->_objTpl->hideBlock('news_permission_tab');
        }
    }

    /**
    * Deletes a news entry
    *
    * @global    ADONewConnection
    * @global    array
    * @return    -
    */
    function delete(){
        global $objDatabase, $_ARRAYLANG;

        //have we deleted a news entry?
        $entryDeleted=false;

        $newsId = "";
        if(isset($_GET['newsId'])){
            $newsId = intval($_GET['newsId']);
            if ($objDatabase->Execute("DELETE FROM ".DBPREFIX."module_news WHERE id = ".$newsId) !== false) {
                $this->strOkMessage = $_ARRAYLANG['TXT_DATA_RECORD_DELETED_SUCCESSFUL'];
                $this->createRSS();
            } else {
                $this->strErrMessage = $_ARRAYLANG['TXT_DATABASE_QUERY_ERROR'];
            }
            $entryDeleted=true;
        }

        if(isset($_POST['selectedNewsId']) && is_array($_POST['selectedNewsId'])){
            foreach ($_POST['selectedNewsId'] AS $value){
                if (!empty($value)){
                    if ($objDatabase->Execute("DELETE FROM ".DBPREFIX."module_news WHERE id = ".intval($value)) !== false) {
                        $this->strOkMessage = $_ARRAYLANG['TXT_DATA_RECORD_DELETED_SUCCESSFUL'];
                        $this->createRSS();
                    } else {
                        $this->strErrMessage = $_ARRAYLANG['TXT_DATABASE_QUERY_ERROR'];
                    }
                }
                $entryDeleted=true;
            }
        } elseif (isset($_POST['selectedUnvalidatedNewsId']) && is_array($_POST['selectedUnvalidatedNewsId'])) {
            foreach ($_POST['selectedUnvalidatedNewsId'] AS $value){
                if (!empty($value)){
                    if ($objDatabase->Execute("DELETE FROM ".DBPREFIX."module_news WHERE id = ".intval($value)) !== false) {
                        $this->strOkMessage = $_ARRAYLANG['TXT_DATA_RECORD_DELETED_SUCCESSFUL'];
                        $this->createRSS();
                    } else {
                        $this->strErrMessage = $_ARRAYLANG['TXT_DATABASE_QUERY_ERROR'];
                    }
                }
                $entryDeleted=true;
            }
        }
        if(!$entryDeleted)
            $this->strOkMessage = $_ARRAYLANG['TXT_NEWS_NOTICE_NOTHING_SELECTED'];
    }




    /**
    * Edit the news, or if $copy is true, it copies an entry
    *
    * @global    ADONewConnection
    * @global    array
    * @global    array
    * @param     string     $pageContent
    */
    function edit($copy = false)
    {
        global $objDatabase,$_ARRAYLANG, $_CONFIG;

        if (!count($this->getCategories())) {
            return $this->manageCategories();
        }

        $objFWUser = FWUser::getFWUserObject();

        $status = "";
        $startDate = "";
        $endDate = "";

        $this->_objTpl->loadTemplateFile('module_news_modify.html',true,true);
        $this->pageTitle = (($copy) ? $_ARRAYLANG['TXT_CREATE_NEWS'] : $_ARRAYLANG['TXT_EDIT_NEWS_CONTENT']);

        $this->_objTpl->setVariable(array(
            'TXT_COPY'                      => $_ARRAYLANG['TXT_NEWS_COPY'],
            'TXT_NEWS_MESSAGE'              => $_ARRAYLANG['TXT_NEWS_MESSAGE'],
            'TXT_TITLE'                     => $_ARRAYLANG['TXT_TITLE'],
            'TXT_CATEGORY'                  => $_ARRAYLANG['TXT_CATEGORY'],
            'TXT_HYPERLINKS'                => $_ARRAYLANG['TXT_HYPERLINKS'],
            'TXT_EXTERNAL_SOURCE'           => $_ARRAYLANG['TXT_EXTERNAL_SOURCE'],
            'TXT_LINK'                      => $_ARRAYLANG['TXT_LINK'],
            'TXT_NEWS_NEWS_CONTENT'         => $_ARRAYLANG['TXT_NEWS_NEWS_CONTENT'],
            'TXT_PUBLISHING'                => $_ARRAYLANG['TXT_PUBLISHING'],
            'TXT_STARTDATE'                 => $_ARRAYLANG['TXT_STARTDATE'],
            'TXT_ENDDATE'                   => $_ARRAYLANG['TXT_ENDDATE'],
            'TXT_OPTIONAL'                  => $_ARRAYLANG['TXT_OPTIONAL'],
            'TXT_ACTIVE'                    => $_ARRAYLANG['TXT_ACTIVE'],
            'TXT_DATE'                      => $_ARRAYLANG['TXT_DATE'],
            'TXT_HEADLINES'                 => $_ARRAYLANG['TXT_HEADLINES'],
            'TXT_TEASERS'                   => $_ARRAYLANG['TXT_TEASERS'],
            'TXT_NEWS_TEASER_TEXT'          => $_ARRAYLANG['TXT_NEWS_TEASER_TEXT'],
            'TXT_IMAGE'                     => $_ARRAYLANG['TXT_IMAGE'],
            'TXT_NEWS_THUMBNAIL'            => $_ARRAYLANG['TXT_NEWS_THUMBNAIL'],
            'TXT_BROWSE'                    => $_ARRAYLANG['TXT_BROWSE'],
            'TXT_NUMBER_OF_CHARS'           => $_ARRAYLANG['TXT_NUMBER_OF_CHARS'],
            'TXT_TEASER_SHOW_NEWS_LINK'     => $_ARRAYLANG['TXT_TEASER_SHOW_NEWS_LINK'],
            'TXT_NEWS_DEFINE_LINK_ALT_TEXT' => $_ARRAYLANG['TXT_NEWS_DEFINE_LINK_ALT_TEXT'],
            'TXT_NEWS_INSERT_LINK'          => $_ARRAYLANG['TXT_NEWS_INSERT_LINK'],
            'TXT_NEWS_REDIRECT_TITLE'       => $_ARRAYLANG['TXT_NEWS_REDIRECT_TITLE'],
            'TXT_NEWS_TYPE'                 => $_ARRAYLANG['TXT_NEWS_TYPE'],
            'TXT_NEWS_TYPE_REDIRECT'        => $_ARRAYLANG['TXT_NEWS_REDIRECT_TITLE'],
            'TXT_NEWS_TYPE_REDIRECT_HELP'   => $_ARRAYLANG['TXT_NEWS_TYPE_REDIRECT_HELP'],
            'TXT_NEWS_TYPE_DEFAULT'         => $_ARRAYLANG['TXT_NEWS_TYPE_DEFAULT'],
            'TXT_NEWS_BASIC_DATA'           => $_ARRAYLANG['TXT_BASIC_DATA'],
            'TXT_NEWS_MORE_OPTIONS'         => $_ARRAYLANG['TXT_MORE_OPTIONS'],
            'TXT_NEWS_PERMISSIONS'          => $_ARRAYLANG['TXT_NEWS_PERMISSIONS'],
            'TXT_NEWS_READ_ACCESS'          => $_ARRAYLANG['TXT_NEWS_READ_ACCESS'],
            'TXT_NEWS_MODIFY_ACCESS'        => $_ARRAYLANG['TXT_NEWS_MODIFY_ACCESS'],
            'TXT_NEWS_AVAILABLE_USER_GROUPS'    => $_ARRAYLANG['TXT_NEWS_AVAILABLE_USER_GROUPS'],
            'TXT_NEWS_ASSIGNED_USER_GROUPS' => $_ARRAYLANG['TXT_NEWS_ASSIGNED_USER_GROUPS'],
            'TXT_NEWS_CHECK_ALL'            => $_ARRAYLANG['TXT_NEWS_CHECK_ALL'],
            'TXT_NEWS_UNCHECK_ALL'          => $_ARRAYLANG['TXT_NEWS_UNCHECK_ALL'],
            'TXT_NEWS_READ_ALL_ACCESS_DESC' => $_ARRAYLANG['TXT_NEWS_READ_ALL_ACCESS_DESC'],
            'TXT_NEWS_READ_SELECTED_ACCESS_DESC'    => $_ARRAYLANG['TXT_NEWS_READ_SELECTED_ACCESS_DESC'],
            'TXT_NEWS_AVAILABLE_USER_GROUPS'        => $_ARRAYLANG['TXT_NEWS_AVAILABLE_USER_GROUPS'],
            'TXT_NEWS_ASSIGNED_USER_GROUPS'         => $_ARRAYLANG['TXT_NEWS_ASSIGNED_USER_GROUPS'],
            'TXT_NEWS_MODIFY_ALL_ACCESS_DESC'       => $_ARRAYLANG['TXT_NEWS_MODIFY_ALL_ACCESS_DESC'],
            'TXT_NEWS_MODIFY_SELECTED_ACCESS_DESC'  => $_ARRAYLANG['TXT_NEWS_MODIFY_SELECTED_ACCESS_DESC'],
        ));

        $newsid = intval($_REQUEST['newsId']);
        $objResult = $objDatabase->SelectLimit("SELECT  catid,
                                                        date,
                                                        lang,
                                                        id,
                                                        title,
                                                        text,
                                                        redirect,
                                                        source,
                                                        url1,
                                                        url2,
                                                        startdate,
                                                        enddate,
                                                        status,
                                                        userid,
                                                        frontend_access_id,
                                                        backend_access_id,
                                                        teaser_only,
                                                        teaser_text,
                                                        teaser_show_link,
                                                        teaser_image_path,
                                                        teaser_image_thumbnail_path
                                                FROM    ".DBPREFIX."module_news
                                                WHERE   id = '".$newsid."'", 1);
												
        if ($objResult !== false && !$objResult->EOF && ($this->arrSettings['news_message_protection'] != '1' || Permission::hasAllAccess() || !$objResult->fields['backend_access_id'] || Permission::checkAccess($objResult->fields['backend_access_id'], 'dynamic', true) || $objResult->fields['userid'] == $objFWUser->objUser->getId())) {
            $newsCat=$objResult->fields['catid'];
            $id = $objResult->fields['id'];
            $newsText = $objResult->fields['text'];
            $teaserText = $objResult->fields['teaser_text'];
            $teaserShowLink = $objResult->fields['teaser_show_link'];

            if($objResult->fields['status']==1){
                $status = "checked=\"checked\"";
            }
            if($objResult->fields['startdate']!="0000-00-00 00:00:00"){
                $startDate = $objResult->fields['startdate'];
            }
            if($objResult->fields['enddate']!="0000-00-00 00:00:00"){
                $endDate = $objResult->fields['enddate'];
            }

            if (empty($objResult->fields['redirect'])) {
                $this->_objTpl->setVariable(array(
                    'NEWS_TYPE_SELECTION_CONTENT'   => 'style="display: block;"',
                    'NEWS_TYPE_SELECTION_REDIRECT'  => 'style="display: none;"',
                    'NEWS_TYPE_CHECKED_CONTENT'     => 'checked="checked"',
                    'NEWS_TYPE_CHECKED_REDIRECT'    => ''
                ));
            } else {
                $this->_objTpl->setVariable(array(
                    'NEWS_TYPE_SELECTION_CONTENT'   => 'style="display: none;"',
                    'NEWS_TYPE_SELECTION_REDIRECT'  => 'style="display: block;"',
                    'NEWS_TYPE_CHECKED_CONTENT'     => '',
                    'NEWS_TYPE_CHECKED_REDIRECT'    => 'checked="checked"'
                ));
            }

            require_once ASCMS_CORE_MODULE_PATH . '/news/lib/teasers.class.php';
            $objTeaser = &new Teasers(true);

            $frameIds = "";
            $associatedFrameIds = "";
            $arrAssociatedFrameIds = explode(';', $objTeaser->arrTeasers[$newsid]['teaser_frames']);
            foreach ($arrAssociatedFrameIds as $teaserFrameId) {
                if (empty($teaserFrameId)) {
                    continue;
                }
                $associatedFrameIds .= "<option value=\"".$teaserFrameId."\">".$objTeaser->arrTeaserFrames[$teaserFrameId]['name']."</option>\n";
            }
            foreach ($objTeaser->arrTeaserFrameNames as $frameName => $frameId) {
                if (!in_array($frameId, $arrAssociatedFrameIds)) {
                    $frameIds .= "<option value=\"".$frameId."\">".$frameName."</option>\n";
                }
            }

            $this->_objTpl->setVariable(array(
                'NEWS_ID'                       => (($copy) ? '' : $id),
                'NEWS_STORED_ID'                => (($copy) ? '' : $id),
                'NEWS_TITLE'                    => contrexx_raw2html($objResult->fields['title']),
                'NEWS_TEXT'                     => get_wysiwyg_editor('newsText', $newsText),
                'NEWS_REDIRECT'                 => htmlentities($objResult->fields['redirect'], ENT_QUOTES, CONTREXX_CHARSET),
                'NEWS_SOURCE'                   => htmlentities($objResult->fields['source'], ENT_QUOTES, CONTREXX_CHARSET),
                'NEWS_URL1'                     => htmlentities($objResult->fields['url1'], ENT_QUOTES, CONTREXX_CHARSET),
                'NEWS_URL2'                     => htmlentities($objResult->fields['url2'], ENT_QUOTES, CONTREXX_CHARSET),
                'NEWS_CREATE_DATE'              => date('H:i:s d.m.Y',$objResult->fields['date']),
                'NEWS_STARTDATE'                => $startDate,
                'NEWS_ENDDATE'                  => $endDate,
                'NEWS_STATUS'                   => isset($_GET['validate']) ? "checked=\"checked\"" : $status,
                'NEWS_TEASER_TEXT'              => htmlentities($teaserText, ENT_QUOTES, CONTREXX_CHARSET),
                'NEWS_TEASER_SHOW_LINK_CHECKED' => $teaserShowLink ? 'checked="checked"' : '',
                'NEWS_TEASER_TEXT_LENGTH'       => strlen($teaserText),
                'NEWS_TEASER_IMAGE_PATH'        => htmlentities($objResult->fields['teaser_image_path'], ENT_QUOTES, CONTREXX_CHARSET),
                'NEWS_TEASER_IMAGE_THUMBNAIL_PATH' => htmlentities($objResult->fields['teaser_image_thumbnail_path'], ENT_QUOTES, CONTREXX_CHARSET),
                'NEWS_DATE'                     => date('Y-m-d H:i:s'),
                'NEWS_SUBMIT_NAME'              => isset($_GET['validate']) ? 'validate' : 'store',
                'NEWS_SUBMIT_NAME_TEXT'         => isset($_GET['validate']) ? $_ARRAYLANG['TXT_CONFIRM'] : $_ARRAYLANG['TXT_STORE']
            ));

            if ($this->arrSettings['news_message_protection'] == '1') {
                if ($this->arrSettings['news_message_protection_restricted'] == '1') {
                    $userGroupIds = $objFWUser->objUser->getAssociatedGroupIds();
                }

                if ($objResult->fields['frontend_access_id']) {
                    $objFrontendGroups = $objFWUser->objGroup->getGroups(array('dynamic' => $objResult->fields['frontend_access_id']));
                    $arrFrontendGroups = $objFrontendGroups ? $objFrontendGroups->getLoadedGroupIds() : array();
                } else {
                    $arrFrontendGroups = array();
                }

                if ($objResult->fields['backend_access_id']) {
                    $objBackendGroups = $objFWUser->objGroup->getGroups(array('dynamic' => $objResult->fields['backend_access_id']));
                    $arrBackendGroups = $objBackendGroups ? $objBackendGroups->getLoadedGroupIds() : array();
                } else {
                    $arrBackendGroups = array();
                }

                $readAccessGroups = '';
                $readNotAccessGroups = '';
                $modifyAccessGroups = '';
                $modifyNotAccessGroups = '';
                $objGroup = $objFWUser->objGroup->getGroups();
                if ($objGroup) {
                while (!$objGroup->EOF) {
                    ${$objGroup->getType() == 'frontend' ?
                        (in_array($objGroup->getId(), $arrFrontendGroups) ? 'readAccessGroups' : 'readNotAccessGroups')
                      : (in_array($objGroup->getId(), $arrBackendGroups) ? 'modifyAccessGroups' : 'modifyNotAccessGroups')}
                      .= '<option value="'.$objGroup->getId().'"'.(!Permission::hasAllAccess() && $this->arrSettings['news_message_protection_restricted'] == '1' && !in_array($objGroup->getId(), $userGroupIds) ? ' disabled="disabled"' : '').'>'.htmlentities($objGroup->getName(), ENT_QUOTES, CONTREXX_CHARSET).'</option>';
                    $objGroup->next();
                    }
                }

                $this->_objTpl->setVariable(array(
                    'NEWS_READ_ACCESS_NOT_ASSOCIATED_GROUPS'    => $readNotAccessGroups,
                    'NEWS_READ_ACCESS_ASSOCIATED_GROUPS'        => $readAccessGroups,
                    'NEWS_READ_ACCESS_ALL_CHECKED'              => $objResult->fields['frontend_access_id'] ? '' : 'checked="checked"',
                    'NEWS_READ_ACCESS_SELECTED_CHECKED'         => $objResult->fields['frontend_access_id'] ? 'checked="checked"' : '',
                    'NEWS_READ_ACCESS_DISPLAY'                  => $objResult->fields['frontend_access_id'] ? '' : 'none',
                    'NEWS_MODIFY_ACCESS_NOT_ASSOCIATED_GROUPS'  => $modifyNotAccessGroups,
                    'NEWS_MODIFY_ACCESS_ASSOCIATED_GROUPS'      => $modifyAccessGroups,
                    'NEWS_MODIFY_ACCESS_ALL_CHECKED'            => $objResult->fields['backend_access_id'] ? '' : 'checked="checked"',
                    'NEWS_MODIFY_ACCESS_SELECTED_CHECKED'       => $objResult->fields['backend_access_id'] ? 'checked="checked"' : '',
                    'NEWS_MODIFY_ACCESS_DISPLAY'                => $objResult->fields['backend_access_id'] ? '' : 'none',
                ));

                $this->_objTpl->setVariable(array(



                ));

                $this->_objTpl->parse('news_permission_tab');
            } else {
                $this->_objTpl->hideBlock('news_permission_tab');
            }
        }
		else {
			$this->strErrMessage = $_ARRAYLANG['TXT_NEWS_ENTRY_NOT_FOUND'];
			$this->overview();
			return;
		}

        if ($_CONFIG['newsTeasersStatus'] == '1') {
            $this->_objTpl->parse('newsTeaserOptions');
            $this->_objTpl->setVariable(array(
                'TXT_USAGE'                     => $_ARRAYLANG['TXT_USAGE'],
                'TXT_USE_ONLY_TEASER_TEXT'      => $_ARRAYLANG['TXT_USE_ONLY_TEASER_TEXT'],
                'TXT_TEASER_TEASER_BOXES'       => $_ARRAYLANG['TXT_TEASER_TEASER_BOXES'],
                'TXT_AVAILABLE_BOXES'           => $_ARRAYLANG['TXT_AVAILABLE_BOXES'],
                'TXT_SELECT_ALL'                => $_ARRAYLANG['TXT_SELECT_ALL'],
                'TXT_DESELECT_ALL'              => $_ARRAYLANG['TXT_DESELECT_ALL'],
                'TXT_ASSOCIATED_BOXES'          => $_ARRAYLANG['TXT_ASSOCIATED_BOXES'],
                'NEWS_HEADLINES_TEASERS_TXT'    => $_ARRAYLANG['TXT_HEADLINES'].' / '.$_ARRAYLANG['TXT_TEASERS'],
                'NEWS_USE_ONLY_TEASER_CHECKED'  => $objResult->fields['teaser_only'] == '1' ? "checked=\"checked\"" : "",
                'NEWS_TEASER_FRAMES'            => $frameIds,
                'NEWS_TEASER_ASSOCIATED_FRAMES' => $associatedFrameIds
            ));
        } else {
            $this->_objTpl->hideBlock('newsTeaserOptions');
            $this->_objTpl->setVariable('NEWS_HEADLINES_TEASERS_TXT', $_ARRAYLANG['TXT_HEADLINES']);
        }

        $this->_objTpl->setVariable("NEWS_CAT_MENU",$this->getCategoryMenu($this->langId, $newsCat));
        $this->_objTpl->setVariable("NEWS_FORM_ACTION",(($copy) ? 'add' : 'update'));
        $this->_objTpl->setVariable("NEWS_STORED_FORM_ACTION","update");
        $this->_objTpl->setVariable("NEWS_TOP_TITLE",$_ARRAYLANG['TXT_EDIT_NEWS_CONTENT']);
    }



    /**
    * Update news
    *
    * @global    ADONewConnection
    * @global    array
    * @global    array
    * @param     integer   $newsid
    * @return    boolean   result
    */
    function update(){
        global $objDatabase, $_ARRAYLANG, $_CONFIG;

        if (!count($this->getCategories())) {
            return $this->manageCategories();
        }

        if (isset($_POST['newsId'])) {
            $objFWValidator = &new FWValidator();
            $objFWUser = FWUser::getFWUserObject();

            $id = intval($_POST['newsId']);
            $userId = $objFWUser->objUser->getId();
            $changelog = mktime();

            if (preg_match('/^([0-9]{1,2})\:([0-9]{1,2})\:([0-9]{1,2})\s*([0-9]{1,2})\.([0-9]{1,2})\.([0-9]{1,4})/', $_POST['newsDate'], $arrDate)) {
                $date = mktime(intval($arrDate[1]), intval($arrDate[2]), intval($arrDate[3]), intval($arrDate[5]), intval($arrDate[4]), intval($arrDate[6]));
            } else {
                $date = time();
            }            
            $title      = contrexx_addslashes($_POST['newsTitle']);
            $text       = contrexx_addslashes($_POST['newsText']);
            $text       = $this->filterBodyTag($text);
            $redirect   = contrexx_strip_tags($_POST['newsRedirect']);

            $source     = $objFWValidator->getUrl(contrexx_strip_tags($_POST['newsSource']));
            $url1       = $objFWValidator->getUrl(contrexx_strip_tags($_POST['newsUrl1']));
            $url2       = $objFWValidator->getUrl(contrexx_strip_tags($_POST['newsUrl2']));
            $catId      = intval($_POST['newsCat']);

            $status     = empty($_POST['status']) ? $status = 0 : intval($_POST['status']);

            $newsTeaserOnly = isset($_POST['newsUseOnlyTeaser']) ? intval($_POST['newsUseOnlyTeaser']) : 0;
            $newsTeaserText = contrexx_addslashes($_POST['newsTeaserText']);
            $newsTeaserShowLink = isset($_POST['newsTeaserShowLink']) ? intval($_POST['newsTeaserShowLink']) : 0;

            $newsTeaserImagePath = contrexx_addslashes($_POST['newsTeaserImagePath']);
            $newsTeaserImageThumbnailPath = contrexx_addslashes($_POST['newsTeaserImageThumbnailPath']);
            $newsTeaserFrames = '';

            if (isset($_POST['newsTeaserFramesAsso']) && count($_POST['newsTeaserFramesAsso'])>0) {
                foreach ($_POST['newsTeaserFramesAsso'] as $frameId) {
                    intval($frameId) > 0 ? $newsTeaserFrames .= ";".intval($frameId) : false;
                }
            }

            $startDate      = (!preg_match('/^\d{4}-\d{2}-\d{2}(\s+\d{2}:\d{2}:\d{2})?$/',$_POST['startDate'])) ? '0000-00-00 00:00:00' : $_POST['startDate'];
            $endDate        = (!preg_match('/^\d{4}-\d{2}-\d{2}(\s+\d{2}:\d{2}:\d{2})?$/',$_POST['endDate'])) ? '0000-00-00 00:00:00' : $_POST['endDate'];

            $newsFrontendAccess     = !empty($_POST['news_read_access']);
            $newsFrontendGroups     = $newsFrontendAccess && isset($_POST['news_read_access_associated_groups']) && is_array($_POST['news_read_access_associated_groups']) ? array_map('intval', $_POST['news_read_access_associated_groups']) : array();
            $newsBackendAccess     = !empty($_POST['news_modify_access']);
            $newsBackendGroups     = $newsBackendAccess && isset($_POST['news_modify_access_associated_groups']) && is_array($_POST['news_modify_access_associated_groups']) ? array_map('intval', $_POST['news_modify_access_associated_groups']) : array();

            $objResult = $objDatabase->SelectLimit('SELECT `frontend_access_id`, `backend_access_id`, `userid` FROM `'.DBPREFIX.'module_news` WHERE `id` = '.$id, 1);
            if ($objResult && $objResult->RecordCount() == 1) {
                $newsFrontendAccessId = $objResult->fields['frontend_access_id'];
                $newsBackendAccessId = $objResult->fields['backend_access_id'];
                $newsUserId = $objResult->fields['userid'];
            } else {
                $newsFrontendAccessId = 0;
                $newsBackendAccessId = 0;
                $newsUserId = 0;
            }

            if ($this->arrSettings['news_message_protection'] == '1') {
                if ($newsBackendAccessId && !Permission::hasAllAccess() && !Permission::checkAccess($newsBackendAccessId, 'dynamic', true) && $newsUserId != $objFWUser->objUser->getId()) {
                    return false;
                }

                if ($newsFrontendAccess) {
                    if ($newsFrontendAccessId) {
                        $objGroup = $objFWUser->objGroup->getGroups(array('dynamic' => $newsFrontendAccessId));
                        $arrFormerFrontendGroupIds = $objGroup ? $objGroup->getLoadedGroupIds() : array();

                        $arrNewGroups = array_diff($newsFrontendGroups, $arrFormerFrontendGroupIds);
                        $arrRemovedGroups = array_diff($arrFormerFrontendGroupIds, $newsFrontendGroups);

                        if ($this->arrSettings['news_message_protection_restricted'] == '1' && !Permission::hasAllAccess()) {
                            $arrUserGroupIds = $objFWUser->objUser->getAssociatedGroupIds();

                            $arrUnknownNewGroups = array_diff($arrNewGroups, $arrUserGroupIds);
                            foreach ($arrUnknownNewGroups as $groupId) {
                                if (!in_array($groupId, $arrFormerFrontendGroupIds)) {
                                    unset($arrNewGroups[array_search($groupId, $arrNewGroups)]);
                                }
                            }

                            $arrUnknownRemovedGroups = array_diff($arrRemovedGroups, $arrUserGroupIds);
                            foreach ($arrUnknownRemovedGroups as $groupId) {
                                if (in_array($groupId, $arrFormerFrontendGroupIds)) {
                                    unset($arrRemovedGroups[array_search($groupId, $arrRemovedGroups)]);
                                }
                            }
                        }

                        if (count($arrRemovedGroups)) {
                            Permission::removeAccess($newsFrontendAccessId, 'dynamic', $arrRemovedGroups);
                        }
                        if (count($arrNewGroups)) {
                            Permission::setAccess($newsFrontendAccessId, 'dynamic', $arrNewGroups);
                        }
                    } else {
                        if ($this->arrSettings['news_message_protection_restricted'] == '1' && !Permission::hasAllAccess()) {
                            $arrUserGroupIds = $objFWUser->objUser->getAssociatedGroupIds();

                            $newsFrontendGroups = array_intersect($newsFrontendGroups, $arrUserGroupIds);
                        }

                        $newsFrontendAccessId = Permission::createNewDynamicAccessId();
                        if (count($newsFrontendGroups)) {
                            Permission::setAccess($newsFrontendAccessId, 'dynamic', $newsFrontendGroups);
                        }
                    }
                } else {
                    if ($newsFrontendAccessId) {
                        Permission::removeAccess($newsFrontendAccessId, 'dynamic');
                    }
                    $newsFrontendAccessId = 0;
                }

                if ($newsBackendAccess) {
                    if ($newsBackendAccessId) {
                        $objGroup = $objFWUser->objGroup->getGroups(array('dynamic' => $newsBackendAccessId));
                        $arrFormerBackendGroupIds = $objGroup ? $objGroup->getLoadedGroupIds() : array();

                        $arrNewGroups = array_diff($newsBackendGroups, $arrFormerBackendGroupIds);
                        $arrRemovedGroups = array_diff($arrFormerBackendGroupIds, $newsBackendGroups);

                        if ($this->arrSettings['news_message_protection_restricted'] == '1' && !Permission::hasAllAccess()) {
                            $arrUserGroupIds = $objFWUser->objUser->getAssociatedGroupIds();

                            $arrUnknownNewGroups = array_diff($arrNewGroups, $arrUserGroupIds);
                            foreach ($arrUnknownNewGroups as $groupId) {
                                if (!in_array($groupId, $arrFormerBackendGroupIds)) {
                                    unset($arrNewGroups[array_search($groupId, $arrNewGroups)]);
                                }
                            }

                            $arrUnknownRemovedGroups = array_diff($arrRemovedGroups, $arrUserGroupIds);
                            foreach ($arrUnknownRemovedGroups as $groupId) {
                                if (in_array($groupId, $arrFormerBackendGroupIds)) {
                                    unset($arrRemovedGroups[array_search($groupId, $arrRemovedGroups)]);
                                }
                            }
                        }

                        if (count($arrRemovedGroups)) {
                            Permission::removeAccess($newsBackendAccessId, 'dynamic', $arrRemovedGroups);
                        }
                        if (count($arrNewGroups)) {
                            Permission::setAccess($newsBackendAccessId, 'dynamic', $arrNewGroups);
                        }
                    } else {
                        if ($this->arrSettings['news_message_protection_restricted'] == '1' && !Permission::hasAllAccess()) {
                            $arrUserGroupIds = $objFWUser->objUser->getAssociatedGroupIds();

                            $newsBackendGroups = array_intersect($newsBackendGroups, $arrUserGroupIds);
                        }

                        $newsBackendAccessId = Permission::createNewDynamicAccessId();
                        if (count($newsBackendGroups)) {
                            Permission::setAccess($newsBackendAccessId, 'dynamic', $newsBackendGroups);
                        }
                    }
                } else {
                    if ($newsBackendAccessId) {
                        Permission::removeAccess($newsBackendAccessId, 'dynamic');
                    }
                    $newsBackendAccessId = 0;
                }
            }

            $objFWUser->objUser->getDynamicPermissionIds(true);

            // find out original user's id
            $orig_user_sql = "
                SELECT userid
                FROM ".DBPREFIX."module_news
                WHERE id = '$id'
            ";
            $orig_user_rs = $objDatabase->Execute($orig_user_sql);
            if ($orig_user_rs == false) {
                DBG::msg("We're in trouble! sql failure: $orig_user_sql");
            }
            else {
                $orig_userid = $orig_user_rs->fields['userid'];
            }
            $set_userid = $orig_userid ? $orig_userid : $userId;

            // $finishednewstext = $newstext."<br>".$_ARRAYLANG['TXT_LAST_EDIT'].": ".$date;
            $objResult = $objDatabase->Execute("UPDATE  ".DBPREFIX."module_news
                                                SET     title='".$title."',
                                                        date='".$date."',
                                                        text='".$text."',
                                                        redirect='".$redirect."',
                                                        source='".$source."',
                                                        url1='".$url1."',
                                                        url2='".$url2."',
                                                        catid='".$catId."',
                                                        lang='".$this->langId."',
                                                        userid = '".$set_userid."',
                                                        status = '".$status."',
                                                        ".(isset($_POST['validate']) ? "validated='1'," : "")."
                                                        startdate = '".$startDate."',
                                                        enddate = '".$endDate."',
                                                        frontend_access_id = '".$newsFrontendAccessId."',
                                                        backend_access_id = '".$newsBackendAccessId."',
                                                        ".($_CONFIG['newsTeasersStatus'] == '1' ? "teaser_only = '".$newsTeaserOnly."',
                                                        teaser_frames = '".$newsTeaserFrames."'," : "")."
                                                        teaser_text = '".$newsTeaserText."',
                                                        teaser_show_link = ".$newsTeaserShowLink.",
                                                        teaser_image_path = '".$newsTeaserImagePath."',
                                                        teaser_image_thumbnail_path = '".$newsTeaserImageThumbnailPath."',
                                                        changelog = '".$changelog."'
                                                WHERE   id = '".$id."'");
           if($objResult === false){
                $this->strErrMessage = $_ARRAYLANG['TXT_DATABASE_QUERY_ERROR'];
           } else {
                $this->createRSS();
                $this->strOkMessage = $_ARRAYLANG['TXT_DATA_RECORD_UPDATED_SUCCESSFUL'];
           }
        }
    }

    /**
    * Change status of multiple messages
    *
    * @global    ADONewConnection
    * @global    array
    * @global    array
    * @param     integer   $newsid
    * @return    boolean   result
    */
    function changeStatus(){
        global $objDatabase, $_ARRAYLANG, $_CONFIG;

        //have we modified any news entry? (validated, (de)activated)
        $entryModified = false;

        if(isset($_POST['deactivate']) AND !empty($_POST['deactivate'])){
            $status = 0;
        }
        if(isset($_POST['activate']) AND !empty($_POST['activate'])){
            $status = 1;
        }

        //(de)activate news where ticked
        if(isset($status)){
            if(isset($_POST['selectedNewsId']) && is_array($_POST['selectedNewsId'])){
                foreach ($_POST['selectedNewsId'] as $value){
                    if (!empty($value)){
                        $objResult = $objDatabase->Execute("UPDATE ".DBPREFIX."module_news SET status = '".$status."' WHERE id = '".intval($value)."'");
			$entryModified = true;
                    }
                    if($objResult === false){
                        $this->strErrMessage = $_ARRAYLANG['TXT_DATABASE_QUERY_ERROR'];
                    } else{
                        $this->strOkMessage = $_ARRAYLANG['TXT_DATA_RECORD_UPDATED_SUCCESSFUL'];
                    }
                }
            }
        }
        
        //validate unvalidated news where ticks set
        if (isset($_POST['validate']) && isset($_POST['selectedUnvalidatedNewsId']) && is_array($_POST['selectedUnvalidatedNewsId'])) {
            foreach ($_POST['selectedUnvalidatedNewsId'] as $value) {
                $objDatabase->Execute("UPDATE ".DBPREFIX."module_news SET status=1, validated='1' WHERE id=".intval($value));
		$entryModified = true;
            }
        }
        
        if(!$entryModified)
            $this->strOkMessage = $_ARRAYLANG['TXT_NEWS_NOTICE_NOTHING_SELECTED'];

        $this->createRSS();
    }

    /**
     * Invert status of a single message
     *
     * @global  ADONewConnection
     * @global  array
     * @param   integer     $intNewsId
     */
    function invertStatus($intNewsId) {
        global $objDatabase,$_ARRAYLANG;

        $intNewsId = intval($intNewsId);

        if ($intNewsId != 0) {
            $objResult = $objDatabase->Execute('SELECT  status
                                                FROM    '.DBPREFIX.'module_news
                                                WHERE   id='.$intNewsId.'
                                                LIMIT   1
                                            ');
            if ($objResult->RecordCount() == 1) {
                $intNewStatus = ($objResult->fields['status'] == 0) ? 1 : 0;

                $objDatabase->Execute(' UPDATE  '.DBPREFIX.'module_news
                                        SET     status="'.$intNewStatus.'"
                                        WHERE   id='.$intNewsId.'
                                        LIMIT   1
                                    ');

                $this->createRSS();

                 $this->strOkMessage = $_ARRAYLANG['TXT_DATA_RECORD_UPDATED_SUCCESSFUL'];
            }
        }
    }

    /**
    * Add or edit the news categories
    *
    * @global    ADONewConnection
    * @global    array
    * @param     string     $pageContent
    */
    function manageCategories()
    {
        global $objDatabase, $_ARRAYLANG;

        $this->_objTpl->loadTemplateFile('module_news_category.html',true,true);
        $this->pageTitle = $_ARRAYLANG['TXT_CATEGORY_MANAGER'];

        $this->_objTpl->setVariable(array(
            'TXT_ADD_NEW_CATEGORY'                       => $_ARRAYLANG['TXT_ADD_NEW_CATEGORY'],
            'TXT_NAME'                                   => $_ARRAYLANG['TXT_NAME'],
            'TXT_ADD'                                    => $_ARRAYLANG['TXT_ADD'],
            'TXT_CATEGORY_LIST'                          => $_ARRAYLANG['TXT_CATEGORY_LIST'],
            'TXT_ID'                                     => $_ARRAYLANG['TXT_ID'],
            'TXT_ACTION'                                 => $_ARRAYLANG['TXT_ACTION'],
            'TXT_ACCEPT_CHANGES'                         => $_ARRAYLANG['TXT_ACCEPT_CHANGES'],
            'TXT_CONFIRM_DELETE_DATA'                    => $_ARRAYLANG['TXT_CONFIRM_DELETE_DATA'],
            'TXT_ACTION_IS_IRREVERSIBLE'                 => $_ARRAYLANG['TXT_ACTION_IS_IRREVERSIBLE'],
            'TXT_ATTENTION_SYSTEM_FUNCTIONALITY_AT_RISK' => $_ARRAYLANG['TXT_ATTENTION_SYSTEM_FUNCTIONALITY_AT_RISK']
        ));

        $this->_objTpl->setGlobalVariable(array('TXT_DELETE' => $_ARRAYLANG['TXT_DELETE']));

        // Add a new category
        if (isset($_POST['addCat']) AND ($_POST['addCat']==true)){
             $catName=contrexx_strip_tags($_POST['newCategorieName']);
             $tmp=trim($catName);
             if(empty($tmp)){
		 $this->strErrMessage = $_ARRAYLANG['TXT_NEWS_CATEGORY_ADD_ERROR_EMPTY'];
             }
             else if($objDatabase->Execute("INSERT INTO ".DBPREFIX."module_news_categories (name,lang)
                                 VALUES ('$catName','$this->langId')") !== false) {
                $this->strOkMessage = $_ARRAYLANG['TXT_DATA_RECORD_ADDED_SUCCESSFUL'];
             } else {
                $this->strErrMessage = $_ARRAYLANG['TXT_DATABASE_QUERY_ERROR'];
             }

        }

        // Modify a new category
        if (isset($_POST['modCat']) AND ($_POST['modCat']==true)){
            foreach ($_POST['newsCatName'] as $id => $name) {
                $name=contrexx_strip_tags($name);
                $id=intval($id);

                if($objDatabase->Execute("UPDATE ".DBPREFIX."module_news_categories SET name='".contrexx_addslashes($name)."',lang=$this->langId WHERE catid=".intval($id)) !== false) {
                    $this->strOkMessage = $_ARRAYLANG['TXT_DATA_RECORD_UPDATED_SUCCESSFUL'];
                } else {
                    $this->strErrMessage = $_ARRAYLANG['TXT_DATABASE_QUERY_ERROR'];
                }
            }
        }

        $objResult = $objDatabase->Execute("SELECT catid,
                           name,
                           lang
                      FROM ".DBPREFIX."module_news_categories
                     WHERE lang=".$this->langId."
                  ORDER BY catid asc");

        $this->_objTpl->setCurrentBlock('newsRow');
        $i=0;

        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $cssStyle = (($i % 2) == 0) ? "row1" : "row2";
                $this->_objTpl->setVariable(array(
                    'NEWS_ROWCLASS'   => $cssStyle,
                    'NEWS_CAT_ID'     => $objResult->fields['catid'],
                    'NEWS_CAT_NAME'   => stripslashes($objResult->fields['name'])
                ));
                $this->_objTpl->parseCurrentBlock();
                $i++;
                $objResult->MoveNext();
            };
        }
    }





    /**
    * Delete the news categories
    *
    * @global    ADONewConnection
    * @global    array
    * @param     string     $pageContent
    */
    function deleteCat(){
        global $objDatabase, $_ARRAYLANG;

        if(isset($_GET['catId'])) {
            $catId=intval($_GET['catId']);
            $objResult = $objDatabase->Execute("SELECT id FROM ".DBPREFIX."module_news WHERE catid=".$catId);

            if ($objResult !== false) {
                if (!$objResult->EOF) {
                     $this->strErrMessage = $_ARRAYLANG['TXT_CATEGORY_NOT_DELETED_BECAUSE_IN_USE'];
                }
                else {
                    if($objDatabase->Execute("DELETE FROM ".DBPREFIX."module_news_categories WHERE catid=".$catId) !== false) {
                        $this->strOkMessage = $_ARRAYLANG['TXT_DATA_RECORD_DELETED_SUCCESSFUL'];
                    } else {
                        $this->strErrMessage = $_ARRAYLANG['TXT_DATABASE_QUERY_ERROR'];
                    }
                }
            }
        }
    }






    /**
    * Create the RSS-Feed
    *
    */
    function createRSS(){
        global $_CONFIG, $objDatabase, $_FRONTEND_LANGID;

        require_once ASCMS_FRAMEWORK_PATH.'/RSSWriter.class.php';

        if (intval($this->arrSettings['news_feed_status']) == 1) {
            $arrNews = array();
            $objRSSWriter = new RSSWriter();

            $objRSSWriter->characterEncoding = CONTREXX_CHARSET;
            $objRSSWriter->channelTitle = $this->arrSettings['news_feed_title'];
            $objRSSWriter->channelLink = 'http://'.$_CONFIG['domainUrl'].($_SERVER['SERVER_PORT'] == 80 ? "" : ":".intval($_SERVER['SERVER_PORT'])).ASCMS_PATH_OFFSET.($_CONFIG['useVirtualLanguagePath'] == 'on' ? '/'.FWLanguage::getLanguageParameter($_FRONTEND_LANGID, 'lang') : null).'/'.CONTREXX_DIRECTORY_INDEX.'?section=news';
            $objRSSWriter->channelDescription = $this->arrSettings['news_feed_description'];
            $objRSSWriter->channelLanguage = FWLanguage::getLanguageParameter($_FRONTEND_LANGID, 'lang');
            $objRSSWriter->channelCopyright = 'Copyright '.date('Y').', http://'.$_CONFIG['domainUrl'];

            if (!empty($this->arrSettings['news_feed_image'])) {
                $objRSSWriter->channelImageUrl = 'http://'.$_CONFIG['domainUrl'].($_SERVER['SERVER_PORT'] == 80 ? "" : ":".intval($_SERVER['SERVER_PORT'])).$this->arrSettings['news_feed_image'];
                $objRSSWriter->channelImageTitle = $objRSSWriter->channelTitle;
                $objRSSWriter->channelImageLink = $objRSSWriter->channelLink;
            }
            $objRSSWriter->channelWebMaster = $_CONFIG['coreAdminEmail'];

            $itemLink = "http://".$_CONFIG['domainUrl'].($_SERVER['SERVER_PORT'] == 80 ? "" : ":".intval($_SERVER['SERVER_PORT'])).ASCMS_PATH_OFFSET.($_CONFIG['useVirtualLanguagePath'] == 'on' ? '/'.FWLanguage::getLanguageParameter($_FRONTEND_LANGID, 'lang') : null).'/'.CONTREXX_DIRECTORY_INDEX.'?section=news&amp;cmd=details&amp;newsid=';

            $query = "
                SELECT      tblNews.id,
                            tblNews.date,
                            tblNews.title,
                            tblNews.text,
                            tblNews.redirect,
                            tblNews.source,
                            tblNews.catid AS categoryId,
                            tblNews.teaser_frames AS teaser_frames,
                            tblNews.teaser_text,
                            tblCategory.name AS category
                FROM        ".DBPREFIX."module_news AS tblNews
                INNER JOIN  ".DBPREFIX."module_news_categories AS tblCategory
                USING       (catid)
                WHERE       tblNews.status=1
                    AND     tblNews.lang = ".$_FRONTEND_LANGID."
                    AND     (tblNews.startdate <= CURDATE() OR tblNews.startdate = '0000-00-00 00:00:00')
                    AND     (tblNews.enddate >= CURDATE() OR tblNews.enddate = '0000-00-00 00:00:00')"
                    .($this->arrSettings['news_message_protection'] == '1' ? " AND tblNews.frontend_access_id=0 " : '')
                            ."ORDER BY tblNews.date DESC";

            if (($objResult = $objDatabase->SelectLimit($query, 20)) !== false && $objResult->RecordCount() > 0) {
                while (!$objResult->EOF) {
                    if (empty($objRSSWriter->channelLastBuildDate)) {
                        $objRSSWriter->channelLastBuildDate = date('r', $objResult->fields['date']);
                    }
                    $arrNews[$objResult->fields['id']] = array(
                        'date'          => $objResult->fields['date'],
                        'title'         => $objResult->fields['title'],
                        'text'          => empty($objResult->fields['redirect']) ? (!empty($objResult->fields['teaser_text']) ? nl2br($objResult->fields['teaser_text']).'<br /><br />' : '').$objResult->fields['text'] : (!empty($objResult->fields['teaser_text']) ? nl2br($objResult->fields['teaser_text']) : ''),
                        'redirect'      => $objResult->fields['redirect'],
                        'source'        => $objResult->fields['source'],
                        'category'      => $objResult->fields['category'],
                        'teaser_frames' => explode(';', $objResult->fields['teaser_frames']),
                        'categoryId'    => $objResult->fields['categoryId']
                    );
                    $objResult->MoveNext();
                }
            }

            // create rss feed
            $objRSSWriter->xmlDocumentPath = ASCMS_FEED_PATH.'/news_'.FWLanguage::getLanguageParameter($_FRONTEND_LANGID, 'lang').'.xml';
            foreach ($arrNews as $newsId => $arrNewsItem) {
                $objRSSWriter->addItem(
                    contrexx_raw2html($arrNewsItem['title']),
                    (empty($arrNewsItem['redirect'])) ? ($itemLink.$newsId.(isset($arrNewsItem['teaser_frames'][0]) ? '&amp;teaserId='.$arrNewsItem['teaser_frames'][0] : '')) : htmlspecialchars($arrNewsItem['redirect'], ENT_QUOTES, CONTREXX_CHARSET),
                    contrexx_raw2html($arrNewsItem['text']),
                    '',
                    array('domain' => "http://".$_CONFIG['domainUrl'].($_SERVER['SERVER_PORT'] == 80 ? "" : ":".intval($_SERVER['SERVER_PORT'])).ASCMS_PATH_OFFSET.($_CONFIG['useVirtualLanguagePath'] == 'on' ? '/'.FWLanguage::getLanguageParameter($_FRONTEND_LANGID, 'lang') : null).'/'.CONTREXX_DIRECTORY_INDEX.'?section=news&amp;category='.$arrNewsItem['categoryId'], 'title' => $arrNewsItem['category']),
                    '',
                    '',
                    '',
                    $arrNewsItem['date'],
                    array('url' => htmlspecialchars($arrNewsItem['source'], ENT_QUOTES, CONTREXX_CHARSET), 'title' => contrexx_raw2html($arrNewsItem['title']))
               );
            }
            $status = $objRSSWriter->write();

            // create headlines rss feed
            $objRSSWriter->removeItems();
            $objRSSWriter->xmlDocumentPath = ASCMS_FEED_PATH.'/news_headlines_'.FWLanguage::getLanguageParameter($_FRONTEND_LANGID, 'lang').'.xml';
            foreach ($arrNews as $newsId => $arrNewsItem) {
                $objRSSWriter->addItem(
                    contrexx_raw2html($arrNewsItem['title']),
                    $itemLink.$newsId.(isset($arrNewsItem['teaser_frames'][0]) ? "&amp;teaserId=".$arrNewsItem['teaser_frames'][0] : ""),
                    '',
                    '',
                    array('domain' => 'http://'.$_CONFIG['domainUrl'].($_SERVER['SERVER_PORT'] == 80 ? '' : ':'.intval($_SERVER['SERVER_PORT'])).ASCMS_PATH_OFFSET.($_CONFIG['useVirtualLanguagePath'] == 'on' ? '/'.FWLanguage::getLanguageParameter($_FRONTEND_LANGID, 'lang') : null).'/'.CONTREXX_DIRECTORY_INDEX.'?section=news&amp;category='.$arrNewsItem['categoryId'], 'title' => $arrNewsItem['category']),
                    '',
                    '',
                    '',
                    $arrNewsItem['date']
                );
            }
            $statusHeadlines = $objRSSWriter->write();

            $objRSSWriter->feedType = 'js';
            $objRSSWriter->xmlDocumentPath = ASCMS_FEED_PATH.'/news_'.FWLanguage::getLanguageParameter($_FRONTEND_LANGID, 'lang').'.js';
            $objRSSWriter->write();

            if (count($objRSSWriter->arrErrorMsg) > 0) {
                $this->strErrMessage .= implode('<br />', $objRSSWriter->arrErrorMsg);
            }
            if (count($objRSSWriter->arrWarningMsg) > 0) {
                $this->strErrMessage .= implode('<br />', $objRSSWriter->arrWarningMsg);
            }
        } else {
            @unlink(ASCMS_FEED_PATH.'/news_'.FWLanguage::getLanguageParameter($_FRONTEND_LANGID, 'lang').'.xml');
            @unlink(ASCMS_FEED_PATH.'/news_headlines_'.FWLanguage::getLanguageParameter($_FRONTEND_LANGID, 'lang').'.xml');
            @unlink(ASCMS_FEED_PATH.'/news_'.FWLanguage::getLanguageParameter($_FRONTEND_LANGID, 'lang').'.js');
        }
    }


    /**
    * Save settings
    *
    * Save the news settings
    *
    * @access private
    * @global ADONewConnection
    * @global array
    * @global array
    * @see createRSS()
    */
    function _saveSettings()
    {
        global $objDatabase, $_CONFIG, $_ARRAYLANG;

        // Store settings
        if(isset($_GET['act']) && $_GET['act'] == 'settings' && isset($_POST['store'])) {
            $objDatabase->Execute("UPDATE ".DBPREFIX."module_news_settings
                              SET value='".contrexx_strip_tags($_POST['newsFeedTitle'])."'
                            WHERE name = 'news_feed_title'");

            $objDatabase->Execute("UPDATE ".DBPREFIX."module_news_settings
                              SET value='".intval($_POST['newsFeedStatus'])."'
                            WHERE name = 'news_feed_status'");

            $objDatabase->Execute("UPDATE ".DBPREFIX."module_news_settings
                              SET value='".contrexx_strip_tags($_POST['newsFeedDescription'])."'
                            WHERE name = 'news_feed_description'");

            $objDatabase->Execute("UPDATE ".DBPREFIX."module_news_settings
                              SET value='".contrexx_addslashes($_POST['newsFeedImage'])."'
                            WHERE name='news_feed_image'");

            $objDatabase->Execute("UPDATE ".DBPREFIX."module_news_settings
                              SET value='".intval($_POST['headlinesLimit'])."'
                            WHERE name = 'news_headlines_limit'");

            // Notify-user. 0 = disabled.
            $this->_store_settings_item('news_notify_user', intval($_POST['newsNotifySelectedUser']));
            // Notify-Group. 0 = disabled.
            $this->_store_settings_item('news_notify_group', intval($_POST['newsNotifySelectedGroup']));

            $objDatabase->Execute("UPDATE ".DBPREFIX."module_news_settings SET value='1' WHERE name = 'news_settings_activated'");

            $submitNews = isset($_POST['newsSubmitNews']) ? intval($_POST['newsSubmitNews']) : 0;
            $submitNewsCommunity = isset($_POST['newsSubmitOnlyCommunity']) ? intval($_POST['newsSubmitOnlyCommunity']) : 0;
            $activateSubmittedNews = isset($_POST['newsActivateSubmittedNews']) ? intval($_POST['newsActivateSubmittedNews']) : 0;

            $objDatabase->Execute("UPDATE ".DBPREFIX."module_news_settings SET value='".$submitNews."' WHERE name='news_submit_news'");
            $objDatabase->Execute("UPDATE ".DBPREFIX."module_news_settings SET value='".$submitNewsCommunity."' WHERE name='news_submit_only_community'");
            $objDatabase->Execute("UPDATE ".DBPREFIX."module_news_settings SET value='".$activateSubmittedNews."' WHERE name='news_activate_submitted_news'");
            $objDatabase->Execute("UPDATE ".DBPREFIX."module_news_settings SET value='".!empty($_POST['newsMessageProtection'])."' WHERE name='news_message_protection'");
            $objDatabase->Execute("UPDATE ".DBPREFIX."module_news_settings SET value='".!empty($_POST['newsMessageProtectionRestricted'])."' WHERE name='news_message_protection_restricted'");

            $_CONFIG['newsTeasersStatus'] = isset($_POST['newsUseTeasers']) ? intval($_POST['newsUseTeasers']) : 0;
            $objDatabase->Execute("UPDATE ".DBPREFIX."settings SET setvalue='".$_CONFIG['newsTeasersStatus']."' WHERE setname='newsTeasersStatus'");

            $this->strOkMessage = $_ARRAYLANG['TXT_NEWS_SETTINGS_SAVED'];
            $this->getSettings();
            $this->createRSS();

            require_once(ASCMS_CORE_PATH.'/settings.class.php');
            $objSettings = &new settingsManager();
            $objSettings->writeSettingsFile();
        }
    }

    function _store_settings_item($name_in_db, $value) {
        global $objDatabase;
        $objDatabase->Execute("
            UPDATE ".DBPREFIX."module_news_settings
            SET    value = '$value'
            WHERE  name  = '$name_in_db';"
        );
        if ($objDatabase->Affected_Rows() == 0) {
            $objDatabase->Execute("
                DELETE FROM ".DBPREFIX."module_news_settings
                WHERE name = '$name_in_db';"
            );
            $objDatabase->Execute("
                INSERT INTO ".DBPREFIX."module_news_settings (name,          value)
                VALUES                                       ('$name_in_db', '$value');"
            );
        }
    }

    function settings(){
        global $objDatabase, $_ARRAYLANG, $_CONFIG;

        $this->pageTitle = $_ARRAYLANG['TXT_NEWS_SETTINGS'];
        $this->_objTpl->loadTemplateFile('module_news_settings.html',true,true);

        // Show settings
        $objResult = $objDatabase->Execute("SELECT lang FROM ".DBPREFIX."languages WHERE id='".$this->langId."'");
        if ($objResult !== false) {
            $newsFeedPath =  "http://".$_SERVER['SERVER_NAME'].ASCMS_FEED_WEB_PATH."/news_headlines_".$objResult->fields['lang'].".xml";
        }

        if (intval($this->arrSettings['news_feed_status'])==1) {
            $status = "checked='checked'";
            $icon = "<a href='".$newsFeedPath."' target='_blank' title='".$newsFeedPath."'><img src='".ASCMS_CORE_MODULE_WEB_PATH."/news/images/rss.gif' border='0' alt='".$newsFeedPath."' /></a>";
        } else {
            $status ="";
            $icon ="";
        }

        $this->_objTpl->setVariable(array(
            'NEWS_FEED_TITLE'                       => stripslashes($this->arrSettings['news_feed_title']),
            'NEWS_FEED_STATUS'                      => $status,
            'NEWS_FEED_ICON'                        => $icon,
            'NEWS_FEED_DESCRIPTION'                 => stripslashes($this->arrSettings['news_feed_description']),
            'NEWS_FEED_IMAGE'                       => htmlentities($this->arrSettings['news_feed_image'], ENT_QUOTES, CONTREXX_CHARSET),
            'NEWS_HEADLINES_LIMIT'                  =>(intval($this->arrSettings['news_headlines_limit'])),
            'NEWS_FEED_PATH'                        => $newsFeedPath,
            'NEWS_SUBMIT_NEWS'                      => $this->arrSettings['news_submit_news'] == '1' ? "checked=\"checked\"" : "",
            'NEWS_SUBMIT_ONLY_COMMUNITY'            => $this->arrSettings['news_submit_only_community'] == '1' ? "checked=\"checked\"" : "",
            'NEWS_ACTIVATE_SUBMITTED_NEWS'          => $this->arrSettings['news_activate_submitted_news'] == '1' ? "checked=\"checked\"" : "",
            'NEWS_USE_TEASERS_CHECKED'              => $_CONFIG['newsTeasersStatus'] == '1' ? "checked=\"checked\"" : "",
            'TXT_STORE'                             => $_ARRAYLANG['TXT_STORE'],
            'TXT_NAME'                              => $_ARRAYLANG['TXT_NAME'],
            'TXT_VALUE'                             => $_ARRAYLANG['TXT_VALUE'],
            'TXT_NEWS_SETTINGS'                     => $_ARRAYLANG['TXT_NEWS_SETTINGS'],
            'TXT_NEWS_FEED_STATUS'                  => $_ARRAYLANG['TXT_NEWS_FEED_STATUS'],
            'TXT_NEWS_FEED_TITLE'                   => $_ARRAYLANG['TXT_NEWS_FEED_TITLE'],
            'TXT_NEWS_FEED_DESCRIPTION'             => $_ARRAYLANG['TXT_NEWS_FEED_DESCRIPTION'],
            'TXT_NEWS_FEED_IMAGE'                   => $_ARRAYLANG['TXT_NEWS_FEED_IMAGE'],
            'TXT_BROWSE'                            => $_ARRAYLANG['TXT_BROWSE'],
            'TXT_NEWS_HEADLINES_LIMIT'              => $_ARRAYLANG['TXT_NEWS_HEADLINES_LIMIT'],
            'TXT_NEWS_SETTINGS_SAVED'               => $_ARRAYLANG['TXT_NEWS_SETTINGS_SAVED'],
            'TXT_SUBMIT_NEWS'                       => $_ARRAYLANG['TXT_SUBMIT_NEWS'],
            'TXT_ALLOW_USERS_SUBMIT_NEWS'           => $_ARRAYLANG['TXT_ALLOW_USERS_SUBMIT_NEWS'],
            'TXT_ALLOW_ONLY_MEMBERS_SUBMIT_NEWS'    => $_ARRAYLANG['TXT_ALLOW_ONLY_MEMBERS_SUBMIT_NEWS'],
            'TXT_AUTO_ACTIVATE_SUBMITTED_NEWS'      => $_ARRAYLANG['TXT_AUTO_ACTIVATE_SUBMITTED_NEWS'],
            'TXT_USE_TEASERS'                       => $_ARRAYLANG['TXT_USE_TEASERS'],
            'TXT_NOTIFY_GROUP'                      => $_ARRAYLANG['TXT_NOTIFY_GROUP'],
            'TXT_NOTIFY_USER'                       => $_ARRAYLANG['TXT_NOTIFY_USER'],
            'TXT_DEACTIVATE'                        => $_ARRAYLANG['TXT_DEACTIVATE'],
            'NEWS_NOTIFY_GROUP_LIST'                => $this->_generate_notify_group_list(),
            'NEWS_NOTIFY_USER_LIST'                 => $this->_generate_notify_user_list(),
            'TXT_NEWS_PROTECTION'                   => $_ARRAYLANG['TXT_NEWS_PROTECTION'],
            'TXT_NEWS_ACTIVE'                       => $_ARRAYLANG['TXT_NEWS_ACTIVE'],
            'TXT_NEWS_MESSAGE_PROTECTION_RESTRICTED'    => $_ARRAYLANG['TXT_NEWS_MESSAGE_PROTECTION_RESTRICTED'],
            'NEWS_MESSAGE_PROTECTION_CHECKED'       => $this->arrSettings['news_message_protection'] == '1' ? 'checked="checked"' : '',
            'NEWS_MESSAGE_PROTECTION_RESTRICTED_DISPLAY'    => $this->arrSettings['news_message_protection'] == '1' ? '' : 'none',
            'NEWS_MESSAGE_PROTECTION_RESTRICTED_CHECKED'    => $this->arrSettings['news_message_protection_restricted'] == '1' ? 'checked="checked"' : ''
        ));
    }
    function _generate_notify_group_list() {
        $active_grp = $this->arrSettings['news_notify_group'];
        if (!empty($_POST['newsNotifySelectedGroup'])) {
            $active_grp = intval($_POST['newsNotifySelectedGroup']);
        }
        return $this->_generate_notify_list('group', $active_grp);
    }
    function _generate_notify_user_list() {
        $active_user= $this->arrSettings['news_notify_user'];
        if (!empty($_POST['newsNotifySelectedUser'])) {
            $active_user = intval($_POST['newsNotifySelectedUser']);
        }
        return $this->_generate_notify_list('user', $active_user);
    }

    /**
     * Generates a list of <option> lines, including a "disable" entry on top of the list.
     * For this to work, the function needs to know the following parameters:
     * @param id_col    The column of the table that contains the "value" part of the option
     * @param label_col The column that will be displayed to the user.
     * @param table     The table from where to select data. without pefix!
     * @param active_id The id which should be pre-selected.
     */
    function _generate_notify_list($type, $active_id) {
        global $_ARRAYLANG, $objDatabase;
        $res = array();

        $none_selected = $active_id == 0 ? 'selected' : '';
        $res[] = "<option value=\"0\" $none_selected>(".$_ARRAYLANG['TXT_DEACTIVATE'].")</option>";

        $objFWUser = FWUser::getFWUserObject();
        if ($type == 'user') {
            $objData = $objFWUser->objUser->getUsers(null, null, array('username' => 'desc'), array('id', 'username', 'firstname', 'lastname'));
        } else {
            $objData = $objFWUser->objGroup->getGroups(null, array('group_name' => 'desc'), array('group_id', 'group_name', 'group_description'));
        }

        while (!$objData->EOF) {
            $id       = $objData->getId();
            $name     = $type == 'user' ? "{$objData->getUsername()} ({$objData->getProfileAttribute('firstname')} {$objData->getProfileAttribute('lastname')})" : "{$objData->getName()} ({$objData->getDescription()})";
            $selected = $id == $active_id ? 'selected' : '';

            $res[] = "<option value=\"$id\" $selected>$name</option>";
            $objData->next();
        }

        return join("\n\t", $res);
    }

    function _ticker()
    {
        global $_ARRAYLANG;

        $this->_objTpl->loadTemplatefile('module_news_ticker.html');

        $this->_objTpl->setVariable(array(
            'TXT_NEWS_OVERVIEW'         => $_ARRAYLANG['TXT_NEWS_OVERVIEW'],
            'TXT_NEWS_CREATE_TICKER'    => $_ARRAYLANG['TXT_NEWS_CREATE_TICKER']
        ));

        $tpl = !empty($_REQUEST['tpl']) ? $_REQUEST['tpl'] : '';
        switch ($tpl) {
            case 'modify':
                $this->_modifyTicker();
                break;

            case 'delete':
                $this->_deleteTicker();

            default:
                $this->_tickerOverview();
                break;
        }
    }

    function _deleteTicker()
    {
        global $objDatabase, $_ARRAYLANG;

        $arrIds = array();
        $status = true;

        if (isset($_POST['news_ticker_id']) && is_array($_POST['news_ticker_id'])) {
            foreach ($_POST['news_ticker_id'] as $id) {
                array_push($arrIds, intval($id));
            }
        } elseif (!empty($_GET['id'])) {
            array_push($arrIds, intval($_GET['id']));
        }

        foreach ($arrIds as $id) {
            $arrTicker = $this->_getTicker($id);
            if ($objDatabase->Execute("DELETE FROM `".DBPREFIX."module_news_ticker` WHERE `id` = ".$id) === false) {
                $status = false;
            } else {
                @unlink(ASCMS_FEED_PATH.'/'.$arrTicker['name']);
            }
        }

        if ($status) {
            if (count($arrIds) > 1) {
                $this->strOkMessage = $_ARRAYLANG['TXT_NEWS_TICKERS_SCCESSFULLY_DELETED'];
            } elseif (count($arrIds) == 1) {
                $this->strOkMessage = sprintf($_ARRAYLANG['TXT_NEWS_TICKER_SUCCESSFULLY_DELETED'], $arrTicker['name']);
            }
        } else {
            if (count($arrIds) > 1) {
                $this->strErrMessage = $_ARRAYLANG['TXT_NEWS_TICKERS_FAILED_DELETE'];
            } else {
                $this->strErrMessage = sprintf($_ARRAYLANG['TXT_NEWS_TICKER_FAILED_DELETE'], $arrTicker['name']);
            }
        }

        return $status;
    }

    function _modifyTicker()
    {
        global $_ARRAYLANG, $objDatabase;

        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
        $pos = !empty($_REQUEST['pos']) ? intval($_REQUEST['pos']) : 0;
        $defaultCharset = CONTREXX_CHARSET;
        if ($arrTicker = $this->_getTicker($id)) {
            $this->pageTitle = $_ARRAYLANG['TXT_NEWS_MODIFY_TICKER'];
            $name = $arrTicker['name'];
            $charset = $arrTicker['charset'];
            $urlencode = $arrTicker['urlencode'];
            $prefix = $arrTicker['prefix'];
        } else {
            $id = 0;
            $this->pageTitle = $_ARRAYLANG['TXT_NEWS_CREATE_TICKER'];
            $name = '';
            $charset = $defaultCharset;
            $content = '';
            $urlencode = 0;
            $prefix = '';
        }

        if (isset($_POST['news_save_ticker'])) {
            $newName = isset($_POST['news_ticker_filename']) ? contrexx_stripslashes(trim($_POST['news_ticker_filename'])) : '';
            $charset = isset($_POST['news_ticker_charset']) ? addslashes($_POST['news_ticker_charset']) : '';
            $content = isset($_POST['news_ticker_content']) ? contrexx_stripslashes($_POST['news_ticker_content']) : '';
            $urlencode = isset($_POST['news_ticker_urlencode']) ? intval($_POST['news_ticker_urlencode']) : 0;
            $prefix = isset($_POST['news_ticker_prefix']) ? contrexx_stripslashes($_POST['news_ticker_prefix']) : '';

            if (!empty($newName)) {
                if ($name != $newName && file_exists(ASCMS_FEED_PATH.'/'.$newName)) {
                    $this->strErrMessage = sprintf($_ARRAYLANG['TXT_NEWS_FILE_DOES_ALREADY_EXIST'], htmlentities($newName, ENT_QUOTES, CONTREXX_CHARSET), ASCMS_FEED_PATH)."<br />";
                    $this->strErrMessage .= $_ARRAYLANG['TXT_NEWS_SELECT_OTHER_FILENAME'];
                } elseif ($name != $newName && !@touch(ASCMS_FEED_PATH.'/'.$newName)) {
                    $this->strErrMessage = sprintf($_ARRAYLANG['TXT_NEWS_COULD_NOT_ATTACH_FILE'], htmlentities($newName, ENT_QUOTES, CONTREXX_CHARSET), ASCMS_FEED_PATH.'/')."<br />";
                    $this->strErrMessage .= sprintf($_ARRAYLANG['TXT_NEWS_SET_CHMOD'], ASCMS_FEED_PATH.'/');
                } else {
                    if ($objDatabase->Execute(($id > 0 ? "UPDATE" : "INSERT INTO")." `".DBPREFIX."module_news_ticker` SET `name` = '".addslashes($newName)."', `charset` = '".addslashes($charset)."', `urlencode` = ".$urlencode.", `prefix` = '".addslashes($prefix)."'".($id > 0 ?" WHERE `id` = ".$id : ''))) {
                        require_once ASCMS_FRAMEWORK_PATH.'/File.class.php';

                        $objFile = &new File();
                        $objFile->setChmod(ASCMS_FEED_PATH, ASCMS_FEED_WEB_PATH, $newName);

                        $fpTicker = @fopen(ASCMS_FEED_PATH.'/'.$newName, 'wb');
                        if ($fpTicker) {
                            if ($defaultCharset != $charset) {
                                $content = iconv($defaultCharset, $charset, $content);
                                $prefix = iconv($defaultCharset, $charset, $prefix);
                            }
                            $content2w = $prefix.($urlencode ? rawurlencode($content) : $content);
                            if (@fwrite($fpTicker, $content2w) !== false) {
                                $this->strOkMessage = $_ARRAYLANG['TXT_NEWS_NEWSTICKER_SUCCESSFULLY_UPDATED'];
                                if ($name != $newName && file_exists(ASCMS_FEED_PATH.'/'.$name)) {
                                    @unlink(ASCMS_FEED_PATH.'/'.$name);
                                }
                                return $this->_tickerOverview();
                            } else {
                                $this->strErrMessage = sprintf($_ARRAYLANG['TXT_NEWS_UNABLE_TO_UPDATE_FILE'], htmlentities($newName, ENT_QUOTES, CONTREXX_CHARSET), ASCMS_FEED_PATH.'/')."<br />";
                                $this->strErrMessage .= sprintf($_ARRAYLANG['TXT_NEWS_SET_CHMOD'], ASCMS_FEED_PATH.'/'.$newName);
                            }
                        } else {
                            $this->strErrMessage = sprintf($_ARRAYLANG['TXT_NEWS_FILE_DOES_NOT_EXIST'], ASCMS_FEED_PATH.'/'.$newName);
                        }
                    } else {
                        $this->strErrMessage = $_ARRAYLANG['TXT_NEWS_UNABLE_TO_RENAME_NEWSTICKER'];
                        @unlink(ASCMS_FEED_PATH.'/'.$newName);
                    }
                }
            } else {
                $this->strErrMessage = $_ARRAYLANG['TXT_NEWS_YOU_MUST_SET_FILENAME'];
            }

            $name = $newName;
        } elseif ($id > 0) {
            if (!file_exists(ASCMS_FEED_PATH.'/'.$name) && !@touch(ASCMS_FEED_PATH.'/'.$name)) {
                $this->strErrMessage = sprintf($_ARRAYLANG['TXT_NEWS_COULD_NOT_ATTACH_FILE'], htmlentities($name, ENT_QUOTES, CONTREXX_CHARSET), ASCMS_FEED_PATH.'/')."<br />";
                $this->strErrMessage .= sprintf($_ARRAYLANG['TXT_NEWS_SET_CHMOD'], ASCMS_FEED_PATH.'/');
            } else {
                $content = file_get_contents(ASCMS_FEED_PATH.'/'.$name);
                if (!empty($prefix) && strpos($content, $prefix) === 0) {
                    $content = substr($content, strlen($prefix));
                }
                if ($urlencode) {
                    $content = rawurldecode($content);
                }
                if ($charset != $defaultCharset) {
                    $content = iconv($charset, $defaultCharset, $content);
                    $prefix = iconv($charset, $defaultCharset, $prefix);
                }
            }
        }

        $this->_objTpl->addBlockfile('NEWS_TICKER_TEMPLATE', 'module_news_ticker_modify', 'module_news_ticker_modify.html');

        $this->_objTpl->setVariable(array(
            'TXT_NEWS_FILENAME'             => $_ARRAYLANG['TXT_NEWS_FILENAME'],
            'TXT_NEWS_MODIFY_FILENAME'      => $_ARRAYLANG['TXT_NEWS_MODIFY_FILENAME'],
            'TXT_NEWS_CONTENT'              => $_ARRAYLANG['TXT_NEWS_CONTENT'],
            'TXT_NEWS_CHARSET'              => $_ARRAYLANG['TXT_NEWS_CHARSET'],
            'TXT_NEWS_SAVE'                 => $_ARRAYLANG['TXT_NEWS_SAVE'],
            'TXT_NEWS_CANCEL'               => $_ARRAYLANG['TXT_NEWS_CANCEL'],
            'TXT_NEWS_URL_ENCODING'         => $_ARRAYLANG['TXT_NEWS_URL_ENCODING'],
            'TXT_NEWS_URL_ENCODING_TXT'     => $_ARRAYLANG['TXT_NEWS_URL_ENCODING_TXT'],
            'TXT_NEWS_PREFIX'               => $_ARRAYLANG['TXT_NEWS_PREFIX'],
            'TXT_NEWS_TICKER_PREFIX_MSG'    => $_ARRAYLANG['TXT_NEWS_TICKER_PREFIX_MSG'],
            'TXT_NEWS_GENERAL'              => $_ARRAYLANG['TXT_NEWS_GENERAL'],
            'TXT_NEWS_ADVANCED'             => $_ARRAYLANG['TXT_NEWS_ADVANCED']
        ));

        $this->_objTpl->setVariable(array(
            'NEWS_MODIFY_TITLE_TXT'     => $id > 0 ? $_ARRAYLANG['TXT_NEWS_MODIFY_TICKER'] : $_ARRAYLANG['TXT_NEWS_CREATE_TICKER'],
            'NEWS_TICKER_ID'            => $id,
            'NEWS_TICKER_FILENAME'      => htmlentities($name, ENT_QUOTES, CONTREXX_CHARSET),
            'NEWS_TICKER_CHARSET_MENU'  => $this->_getCharsetMenu($charset, 'name="news_ticker_charset"'),
            'NEWS_TICKER_CONTENT'       => htmlentities($content, ENT_QUOTES, CONTREXX_CHARSET),
            'NEWS_TICKER_URLENCODE'     => $urlencode ? 'checked="checked"' : '',
            'NEWS_TICKER_POS'           => $pos,
            'NEWS_TICKER_PREFIX'        => $prefix
        ));

        $this->_objTpl->parse('module_news_ticker_modify');
    }

    function _getCharsetMenu($selectedCharset, $attrs = '')
    {
        $menu = '<select'.(!empty($attrs) ? ' '.$attrs : '').">\n";
        foreach ($this->_arrCharsets as $charset) {
            $menu .= "<option".($charset == $selectedCharset ? ' selected="selected"' : '')." value=\"".$charset."\">".$charset."</option>\n";
        }
        $menu .= "</select>\n";

        return $menu;
    }

    function _tickerOverview()
    {
        global $_ARRAYLANG, $_CONFIG;

        $this->pageTitle = $_ARRAYLANG['TXT_NEWS_TICKERS'];

        $paging = '';
        $pos = isset($_REQUEST['pos']) ? intval($_REQUEST['pos']) : 0;
        $count = $this->_tickerCount();

        $this->_objTpl->addBlockfile('NEWS_TICKER_TEMPLATE', 'module_news_ticker_list', 'module_news_ticker_list.html');

        $this->_objTpl->setVariable(array(
            'TXT_NEWS_TICKERS'                  => $_ARRAYLANG['TXT_NEWS_TICKERS'],
            'TXT_NEWS_TICKER'                   => $_ARRAYLANG['TXT_NEWS_TICKER'],
            'TXT_NEWS_CONTENT'                  => $_ARRAYLANG['TXT_NEWS_CONTENT'],
            'TXT_NEWS_CHARSET'                  => $_ARRAYLANG['TXT_NEWS_CHARSET'],
            'TXT_NEWS_FUNCTIONS'                => $_ARRAYLANG['TXT_NEWS_FUNCTIONS'],
            'TXT_NEWS_CONFIRM_DELETE_TICKER'    => $_ARRAYLANG['TXT_NEWS_CONFIRM_DELETE_TICKER'],
            'TXT_NEWS_ACTION_IS_IRREVERSIBLE'   => $_ARRAYLANG['TXT_NEWS_ACTION_IS_IRREVERSIBLE']
        ));

        $this->_objTpl->setGlobalVariable(array(
            'TXT_NEWS_SHOW_TICKER_FILE' => $_ARRAYLANG['TXT_NEWS_SHOW_TICKER_FILE'],
            'TXT_NEWS_MODIFY_TICKER'    => $_ARRAYLANG['TXT_NEWS_MODIFY_TICKER'],
            'NEWS_TICKER_POS'           => $pos
        ));

        if ($count > $_CONFIG['corePagingLimit']) {
            $paging = getPaging($count, $pos, "&amp;cmd=news&amp;act=ticker", 'Ticker');
        }

        $displayCharset = CONTREXX_CHARSET;

        $arrTickers = $this->_getTickers($pos);
        if (count($arrTickers) > 0) {
            $nr = 0;
            foreach ($arrTickers as $tickerId => $arrTicker) {
                if (!file_exists(ASCMS_FEED_PATH.'/'.$arrTicker['name']) && !@touch(ASCMS_FEED_PATH.'/'.$arrTicker['name'])) {
                    $this->strErrMessage .= sprintf($_ARRAYLANG['TXT_NEWS_COULD_NOT_ATTACH_FILE'], htmlentities($arrTicker['name'], ENT_QUOTES, CONTREXX_CHARSET), ASCMS_FEED_PATH.'/')."<br />";
                    $this->strErrMessage .= sprintf($_ARRAYLANG['TXT_NEWS_SET_CHMOD'], ASCMS_FEED_PATH.'/');
                } else {
                    $content = file_get_contents(ASCMS_FEED_PATH.'/'.$arrTicker['name']);
                    if (!empty($arrTicker['prefix']) && strpos($content, $arrTicker['prefix']) === 0) {
                        $content = substr($content, strlen($arrTicker['prefix']));
                    }
                    if ($arrTicker['urlencode']) {
                        $content = rawurldecode($content);
                    }
                    $content = iconv($arrTicker['charset'], $displayCharset, $content);


                    if (strlen($content) > 100) {
                        $content = substr($content, 0, 100).'...';
                    }
                }

                $this->_objTpl->setVariable(array(
                    'NEWS_TICKER_ID'            => $tickerId,
                    'NEWS_TICKER_NAME'          => htmlentities($arrTicker['name'], ENT_QUOTES, CONTREXX_CHARSET),
                    'NEWS_TICKER_ADDRESS'       => ASCMS_PROTOCOL.'://'.$_CONFIG['domainUrl'].ASCMS_FEED_WEB_PATH.'/'.htmlentities($arrTicker['name'], ENT_QUOTES, CONTREXX_CHARSET),
                    'NEWS_TICKER_CONTENT'       => !empty($content) ? htmlentities($content, ENT_QUOTES, CONTREXX_CHARSET) : '&nbsp;',
                    'NEWS_TICKER_CHARSET'       => $arrTicker['charset'],
                    'NEWS_TICKER_ROW_CLASS'     => $nr % 2 ? 1 : 2,
                    'NEWS_DELETE_TICKER_TXT'    => sprintf($_ARRAYLANG['TXT_NEWS_DELETE_TICKER'], htmlentities($arrTicker['name'], ENT_QUOTES, CONTREXX_CHARSET))
                ));
                $nr++;

                $this->_objTpl->parse('news_ticker_list');
            }

            $this->_objTpl->setVariable(array(
                'TXT_NEWS_CHECK_ALL'                    => $_ARRAYLANG['TXT_NEWS_CHECK_ALL'],
                'TXT_NEWS_UNCHECK_ALL'                  => $_ARRAYLANG['TXT_NEWS_UNCHECK_ALL'],
                'TXT_NEWS_WITH_SELECTED'                => $_ARRAYLANG['TXT_NEWS_WITH_SELECTED'],
                'TXT_NEWS_DELETE'                       => $_ARRAYLANG['TXT_NEWS_DELETE'],
                'TXT_NEWS_CONFIRM_DELETE_TICKERS_MSG'   => $_ARRAYLANG['TXT_NEWS_CONFIRM_DELETE_TICKERS_MSG']
            ));

            $this->_objTpl->parse('news_ticker_data');
            $this->_objTpl->hideBlock('news_ticker_no_data');
            $this->_objTpl->touchBlock('news_ticker_multi_select_action');

            if (!empty($paging)) {
                $this->_objTpl->setVariable('PAGING', "<br />\n".$paging);
            }
        } else {
            $this->_objTpl->setVariable('TXT_NEWS_NO_TICKER_AVAILABLE', $_ARRAYLANG['TXT_NEWS_NO_TICKER_AVAILABLE']);

            $this->_objTpl->parse('news_ticker_no_data');
            $this->_objTpl->hideBlock('news_ticker_data');
            $this->_objTpl->hideBlock('news_ticker_multi_select_action');
        }

        $this->_objTpl->parse('module_news_ticker_list');
    }

    function _getTickers($offSet = 0)
    {
        global $objDatabase, $_CONFIG;

        $arrTickers = array();
        $query = "SELECT `id`, `name`, `charset`, `urlencode`, `prefix` FROM `".DBPREFIX."module_news_ticker` ORDER BY `name`";
        $objTicker = $objDatabase->SelectLimit($query, $_CONFIG['corePagingLimit'], $offSet);
        if ($objTicker !== false) {
            while (!$objTicker->EOF) {
                $arrTickers[$objTicker->fields['id']] = array(
                    'name'      => $objTicker->fields['name'],
                    'charset'   => $objTicker->fields['charset'],
                    'urlencode' => $objTicker->fields['urlencode'],
                    'prefix'    => $objTicker->fields['prefix']
                );
                $objTicker->MoveNext();
            }
        }

        return $arrTickers;
    }

    function _getTicker($id)
    {
        global $objDatabase;

        $objTicker = $objDatabase->SelectLimit("SELECT `name`, `charset`, `urlencode`, `prefix` FROM `".DBPREFIX."module_news_ticker` WHERE `id` = ".$id, 1);
        if ($objTicker !== false && $objTicker->RecordCount() == 1) {
            return (array(
                'name'      => $objTicker->fields['name'],
                'charset'   => $objTicker->fields['charset'],
                'urlencode' => $objTicker->fields['urlencode'],
                'prefix'    => $objTicker->fields['prefix']
            ));
        } else {
            return false;
        }
    }

    function _tickerCount()
    {
        global $objDatabase;

        $objCount = $objDatabase->SelectLimit('SELECT SUM(1) AS `count` FROM `'.DBPREFIX.'module_news_ticker`', 1);
        if ($objCount !== false) {
            return $objCount->fields['count'];
        } else {
            return false;
        }
    }

    function _teasers()
    {
        global $_ARRAYLANG;

        require_once ASCMS_CORE_MODULE_PATH . '/news/lib/teasers.class.php';
        $this->_objTeaser = &new Teasers(true);

        $this->_objTpl->loadTemplateFile('module_news_teasers.html');

        $this->_objTpl->setGlobalVariable(array(
            'TXT_TEASER_TEASER_BOXES'       => $_ARRAYLANG['TXT_TEASER_TEASER_BOXES'],
            'TXT_TEASER_BOX_TEMPLATES'      => $_ARRAYLANG['TXT_TEASER_BOX_TEMPLATES']
        ));

        if (!isset($_REQUEST['tpl'])) {
            $_REQUEST['tpl'] = '';
        }

        switch ($_REQUEST['tpl']) {
        case 'teasers':
            $this->_showTeasers();
            break;

        case 'frames':
            $this->_showTeaserFrames();
            break;

        case 'editFrame':
            $this->_editTeaserFrame();
            break;

        case 'deleteFrame':
            $this->_deleteTeaserFrame();
            $this->_showTeaserFrames();
            break;

        case 'updateFrame':
            $this->_updateTeaserFrame();
            break;

        case 'frameTemplates':
            $this->_showTeaserFrameTemplates();
            break;

        case 'editFrameTemplate':
            $this->_editTeaserFrameTemplate();
            break;

        case 'updateFrameTemplate':
            $this->_updateTeaserFrameTemplate();
            break;

        case 'deleteFrameTemplate':
            $this->_deleteTeaserFrameTeamplate();
            $this->_showTeaserFrameTemplates();
            break;

        default:
            $this->_showTeaserFrames();
            break;
        }
    }

    /**
    * Delete teaser frame template
    *
    * Deletes a teaser frame template
    *
    * @access private
    * @see Teaser::deleteTeaserFrameTemplate()
    */
    function _deleteTeaserFrameTeamplate()
    {
    	global $_ARRAYLANG;
    	
        $templateId = intval($_GET['id']);

        $result = $this->_objTeaser->deleteTeaserFrameTeamplte($templateId);
        if ($result !== false && $result !== true) {
            $this->strOkMessage .= $result;
        }
        
        $this->_objTeaser = &new Teasers(true);

        $this->_objTpl->loadTemplateFile('module_news_teasers.html');

        $this->_objTpl->setGlobalVariable(array(
            'TXT_TEASER_TEASER_BOXES'       => $_ARRAYLANG['TXT_TEASER_TEASER_BOXES'],
            'TXT_TEASER_BOX_TEMPLATES'      => $_ARRAYLANG['TXT_TEASER_BOX_TEMPLATES']
        ));
    }

    function _showTeaserFrameTemplates()
    {
        global $_ARRAYLANG;

        if (count($this->_objTeaser->arrTeaserFrameTemplates) > 0) {
            $this->pageTitle = $_ARRAYLANG['TXT_TEASER_BOX_TEMPLATES'];
            $this->_objTpl->addBlockFile('NEWS_TEASERS_FILE', 'news_teaser_frame_templates', 'module_news_teasers_frame_templates.html');

            $this->_objTpl->setVariable(array(
                'TXT_DESCRIPTION'                   => $_ARRAYLANG['TXT_DESCRIPTION'],
                'TXT_FUNCTIONS'                     => $_ARRAYLANG['TXT_FUNCTIONS'],
                'TXT_ADD_TEMPLATE'                  => $_ARRAYLANG['TXT_ADD_TEMPLATE'],
                'TXT_CONFIRM_DELETE_BOX_TPL_TEXT'   => $_ARRAYLANG['TXT_CONFIRM_DELETE_BOX_TPL_TEXT']
            ));

            $this->_objTpl->setGlobalVariable(array(
                'TXT_EDIT_BOX_TEMPLATE' => $_ARRAYLANG['TXT_EDIT_BOX_TEMPLATE'],
                'TXT_DELETE_TEMPLATE'       => $_ARRAYLANG['TXT_DELETE_TEMPLATE']
            ));

            $rowNr = 0;

            foreach ($this->_objTeaser->arrTeaserFrameTemplates as $id => $arrTeaserFrameTemplate) {
                $this->_objTpl->setVariable(array(
                    'NEWS_TEASER_FRAME_TPL_ROW_CLASS'   => $rowNr % 2 == 0 ? "row1" : "row2",
                    'NEWS_TEASER_FRAME_TPL_ID'          => $id,
                    'NEWS_TEASER_FRAME_TPL_DESCRIPTION' => htmlspecialchars($arrTeaserFrameTemplate['description'], ENT_QUOTES, CONTREXX_CHARSET)
                ));
                $this->_objTpl->parse('news_teaser_frame_template_list');

                $rowNr++;
            }

            $this->_objTpl->parse('news_teaser_frame_templates');
        } else {
            $this->_editTeaserFrameTemplate();
        }

    }

    function _editTeaserFrameTemplate()
    {
        global $_ARRAYLANG;

        $this->_objTpl->addBlockFile('NEWS_TEASERS_FILE', 'news_teaser_modify_frame_templates', 'module_news_teasers_modify_frame_template.html');

        // get teaser frame template id
        if (isset($_GET['templateId'])) {
            $templateId = intval($_GET['templateId']);
        } else {
            $templateId = 0;
        }

        // set teaser frame template description
        if (isset($_POST['teaserFrameTplDescription'])) {
            $templateDescription = htmlentities(contrexx_strip_tags($_POST['teaserFrameTplDescription']), ENT_QUOTES, CONTREXX_CHARSET);
        } elseif (isset($this->_objTeaser->arrTeaserFrameTemplates[$templateId]['description'])) {
            $templateDescription = $this->_objTeaser->arrTeaserFrameTemplates[$templateId]['description'];
        } else {
            $templateDescription = "";
        }

        // set wysiwyg or html mode
        if (isset($_GET['source'])) {
            $sourceCode = intval($_GET['source']);
        } elseif (isset($this->_objTeaser->arrTeaserFrameTemplates[$templateId]['source_code_mode'])) {
            $sourceCode = $this->_objTeaser->arrTeaserFrameTemplates[$templateId]['source_code_mode'];
        } else {
            $sourceCode = 0;
        }

        if (isset($_POST['teaserFrameTplHtml'])) {
            $templateHtml = $_POST['teaserFrameTplHtml'];
        } elseif (isset($this->_objTeaser->arrTeaserFrameTemplates[$templateId])) {
            $templateHtml = $this->_objTeaser->arrTeaserFrameTemplates[$templateId]['html'];
        } else {
            $templateHtml = "";
        }
        $templateHtml = preg_replace('/\{([A-Za-z0-9_]*?)\}/', '[[\\1]]', $templateHtml);
        $templateHtml = htmlentities($templateHtml, ENT_QUOTES, CONTREXX_CHARSET);

        $this->pageTitle = $templateId != 0 ? $_ARRAYLANG['TXT_EDIT_BOX_TEMPLATE'] : $_ARRAYLANG['TXT_ADD_BOX_TEMPLATE'];

        $this->_objTpl->setVariable(array(
            'TXT_PLACEHOLDER_DIRECTORY' => $_ARRAYLANG['TXT_PLACEHOLDER_DIRECTORY'],
            'TXT_DESCRIPTION'           => $_ARRAYLANG['TXT_DESCRIPTION'],
            'TXT_SOURCE_CODE_MODE'      => $_ARRAYLANG['TXT_SOURCE_CODE_MODE'],
            'TXT_CANCEL'            => $_ARRAYLANG['TXT_CANCEL'],
            'TXT_SAVE'              => $_ARRAYLANG['TXT_SAVE'],
            'TXT_PLACEHOLDER_LIST'      => $_ARRAYLANG['TXT_PLACEHOLDER_LIST'],
            'TXT_EXAMPLE'               => $_ARRAYLANG['TXT_EXAMPLE'],
            'TXT_BOX_TEMPLATE'      => $_ARRAYLANG['TXT_BOX_TEMPLATE'],
            'TXT_NEWS_LINK_DESCRIPTION' => $_ARRAYLANG['TXT_NEWS_LINK_DESCRIPTION'],
            'TXT_CATEGORY'  => $_ARRAYLANG['TXT_CATEGORY'],
            'TXT_NEWS_DATE_DESCRIPTION'     => $_ARRAYLANG['TXT_NEWS_DATE_DESCRIPTION'],
            'TXT_NEWS_TITLE_DESCRIPTION'    => $_ARRAYLANG['TXT_NEWS_TITLE_DESCRIPTION'],
            'TXT_NEWS_IMAGE_PATH_DESCRIPTION'   => $_ARRAYLANG['TXT_NEWS_IMAGE_PATH_DESCRIPTION'],
            'TXT_TEASER_ROW_DESCRIPTION'        => $_ARRAYLANG['TXT_TEASER_ROW_DESCRIPTION'],
            'TXT_CONTINUE'                      => $_ARRAYLANG['TXT_CONTINUE']
        ));

        $this->_objTpl->setVariable(array(
            'NEWS_TEASER_FRAME_TPL_ID'              => $templateId,
            'NEWS_TEASER_FRAME_TPL_DESCRIPTION'     => htmlentities($templateDescription, ENT_QUOTES, CONTREXX_CHARSET),
            'NEWS_TEASER_FRAME_TEMPLATE_WYSIWYG'    => $sourceCode ? get_wysiwyg_editor('teaserFrameTplHtml', $templateHtml, 'html') : get_wysiwyg_editor('teaserFrameTplHtml', $templateHtml),
            'NEWS_TEASER_FRAME_TPL_SOURCE_CHECKED'  => $sourceCode ? "checked=\"checked\"" : "",
            'NEWS_TEASER_TITLE_TXT'                 => $templateId != 0 ? $_ARRAYLANG['TXT_EDIT_BOX_TEMPLATE'] : $_ARRAYLANG['TXT_ADD_BOX_TEMPLATE']
        ));
        $this->_objTpl->parse('news_teaser_modify_frame_templates');
    }

    function _updateTeaserFrameTemplate()
    {
        global $_ARRAYLANG;

        if (isset($_POST['saveTeaserFrameTemplate']) && isset($_GET['templateId']) && isset($_POST['teaserFrameTplDescription']) && isset($_POST['teaserFrameTplHtml'])) {
            $templateId = intval($_GET['templateId']);
            $sourceCodeMode = isset($_POST['teaserFrameTplSource']) ? intval($_POST['teaserFrameTplSource']) : 0;

            $templateDescription = contrexx_strip_tags($_POST['teaserFrameTplDescription']);

	    $tmp = trim($templateDescription);
            if (empty($tmp)) {
                $this->strErrMessage .= $_ARRAYLANG['TXT_SET_TEMPLATE_DESCRIPTION_TEXT'];
                $this->_editTeaserFrameTemplate();
                return;
            }

            $templateHtml = contrexx_addslashes($_POST['teaserFrameTplHtml']);
            $templateHtml = preg_replace('/\[\[([A-Za-z0-9_]*?)\]\]/', '{\\1}', $templateHtml);

            if ($templateId != 0) {
                $this->_objTeaser->updateTeaserFrameTemplate($templateId, $templateDescription, $templateHtml, $sourceCodeMode);
            } else {
                $this->_objTeaser->addTeaserFrameTemplate($templateDescription, $templateHtml, $sourceCodeMode);
            }
            $this->_objTeaser->initializeTeaserFrameTemplates($templateId);

            $this->_showTeaserFrameTemplates();
        } elseif (isset($_POST['cancel'])) {
            $this->_showTeaserFrameTemplates();
        } else {
            $this->_editTeaserFrameTemplate();
        }
    }

    function _deleteTeaserFrame()
    {
        global $_ARRAYLANG;

        $frameId = intval($_GET['id']);
        if ($this->_objTeaser->deleteTeaserFrame($frameId)) {
            $this->_objTeaser->initializeTeaserFrames();
            $this->strOkMessage .= $_ARRAYLANG['TXT_DATA_RECORD_DELETED_SUCCESSFUL'];
        } else {
            $this->strErrMessage .= $_ARRAYLANG['TXT_DATABASE_QUERY_ERROR'];
        }
    }

    function _showTeaserFrames()
    {
        global $_ARRAYLANG;

        $this->pageTitle = $_ARRAYLANG['TXT_TEASER_TEASER_BOXES'];

        $this->_objTeaser->initializeTeaserFrames();
        $arrTeaserFrames = $this->_objTeaser->arrTeaserFrames;

        if (count($this->_objTeaser->arrTeaserFrames) > 0) {
            $this->_objTpl->addBlockFile('NEWS_TEASERS_FILE', 'news_teasers_block', 'module_news_teasers_frames.html');

            $rowNr = 1;

            $this->_objTpl->setVariable(array(
                'TXT_BOX_NAME'                  => $_ARRAYLANG['TXT_BOX_NAME'],
                'TXT_PLACEHOLDER'                   => $_ARRAYLANG['TXT_PLACEHOLDER'],
                'TXT_BOX_TEMPLATE'              => $_ARRAYLANG['TXT_BOX_TEMPLATE'],
                'TXT_FUNCTIONS'                     => $_ARRAYLANG['TXT_FUNCTIONS'],
                'TXT_ADD_BOX'                       => $_ARRAYLANG['TXT_ADD_BOX'],
                'TXT_CONFIRM_DELETE_TEASER_BOX' => $_ARRAYLANG['TXT_CONFIRM_DELETE_TEASER_BOX']
            ));

            $this->_objTpl->setGlobalVariable(array(
                'TXT_SHOW_TEASER_BOX'               => $_ARRAYLANG['TXT_SHOW_TEASER_BOX'],
                'TXT_EDIT_BOX_TEMPLATE'         => $_ARRAYLANG['TXT_EDIT_BOX_TEMPLATE'],
                'TXT_EDIT_TEASER_BOX'               => $_ARRAYLANG['TXT_EDIT_TEASER_BOX'],
                'TXT_DELETE_TEASER_BOX'         => $_ARRAYLANG['TXT_DELETE_TEASER_BOX']
            ));

            foreach ($arrTeaserFrames as $teaserFrameId => $arrTeaserFrame) {
                $this->_objTpl->setVariable(array(
                    'NEWS_TEASER_FRAME_ROW_CLASS'               => $rowNr % 2 == 0 ? "row1" : "row2",
                    'NEWS_TEASER_FRAME_NAME'                    => $arrTeaserFrame['name'],
                    'NEWS_TEASER_FRAME_TEMPLATE_PLACEHOLDER'    => "[[TEASERS_".strtoupper($arrTeaserFrame['name'])."]]",
                    'NEWS_TEASER_FRAME_ID'                  => $teaserFrameId,
                    'NEWS_TEASER_FRAME_TPL_NAME'                    => $this->_objTeaser->arrTeaserFrameTemplates[$arrTeaserFrame['frame_template_id']]['description'],
                    'NEWS_TEASER_FRAME_TPL_ID'                  => $arrTeaserFrame['frame_template_id']
                ));

                $this->_objTpl->parse('news_teaser_frames_list');

                $rowNr++;
            }
            $this->_objTpl->parse('news_teasers_block');
        } else {
            $this->_editTeaserFrame();
        }
    }

    function _showTeaserFrame()
    {
        $this->_objTpl->addBlockFile('NEWS_TEASERS_FILE', 'news_teasers_block', 'module_news_teasers_show_frame.html');

        $teaserFrameId = intval($_REQUEST['frameId']);
        $this->_objTpl->setVariable('NEWS_TEASER_FRAME', $this->_objTeaser->getTeaserFrame($teaserFrameId));

        $this->_objTpl->parse('news_teasers_block');
    }

    function _updateTeaserFrame()
    {
        global $_ARRAYLANG;

        if (isset($_POST['saveTeaserFrame']) && isset($_GET['frameId']) && isset($_POST['teaserFrameName']) && isset($_POST['teaserFrameTemplateId'])) {
            $id = intval($_GET['frameId']);
            $name = preg_replace('/[^a-zA-Z0-9]+/', '', $_POST['teaserFrameName']);
            $name = contrexx_strip_tags($name);
            $templateId = intval($_POST['teaserFrameTemplateId']);

            if (empty($name)) {
                $this->strErrMessage .= $_ARRAYLANG['TXT_SET_FRMAE_NAME_TEXT'];
                $this->_editTeaserFrame();
                return;
            } elseif (!$this->_objTeaser->isUniqueFrameName($id, $name)) {
                $this->strErrMessage .= $_ARRAYLANG['TXT_BOX_NAME_EXISTS_TEXT'];
                $this->_editTeaserFrame();
                return;
            }

            if ($id != 0) {
                $this->_objTeaser->updateTeaserFrame($id, $templateId, $name);
                $this->strOkMessage = $_ARRAYLANG['TXT_NEWS_TEASER_BOX_UPDATED'];
            } else {
                $this->_objTeaser->addTeaserFrame($id, $templateId, $name);
                $this->strOkMessage = $_ARRAYLANG['TXT_NEWS_TEASER_BOX_ADDED'];
            }
            $this->_objTeaser->initializeTeaserFrames($id);

            $this->_showTeaserFrames();
        } elseif (isset($_POST['cancel']) && isset($_GET['frameId']) && ($_GET['frameId'] == 0)) {
            $this->_showTeaserFrames();
        } elseif (isset($_POST['cancel']) && isset($_GET['frameId'])) {
            $this->_showTeaserFrame();
        } else {
            $this->_editTeaserFrame();
        }
    }

    function _editTeaserFrame()
    {
        global $_ARRAYLANG;

        $this->_objTpl->addBlockFile('NEWS_TEASERS_FILE', 'news_teasers_block', 'module_news_teasers_modify_frame.html');

        $this->_objTpl->setVariable(array(
            'TXT_BOX_NAME'      => $_ARRAYLANG['TXT_BOX_NAME'],
            'TXT_BOX_TEMPLATE'  => $_ARRAYLANG['TXT_BOX_TEMPLATE'],
            'TXT_CANCEL'            => $_ARRAYLANG['TXT_CANCEL'],
            'TXT_SAVE'              => $_ARRAYLANG['TXT_SAVE']
        ));

        // get teaser frame id
        if (isset($_GET['frameId'])) {
            $teaserFrameId = intval($_GET['frameId']);
        } else {
            $teaserFrameId = 0;
        }

        // set teaser frame name
        if (isset($_POST['teaserFrameName'])) {
            $teaserFrameName = preg_replace('/[^a-zA-Z0-9]+/', '', $_POST['teaserFrameName']);
            $teaserFrameName = htmlentities(contrexx_strip_tags($teaserFrameName), ENT_QUOTES, CONTREXX_CHARSET);
        } elseif (isset($this->_objTeaser->arrTeaserFrames[$teaserFrameId])) {
            $teaserFrameName = $this->_objTeaser->arrTeaserFrames[$teaserFrameId]['name'];
        } else {
            $teaserFrameName = "";
        }

        // set teaser frame template
        if (isset($_POST['teaserFrameTemplateId'])) {
            $teaserFrameTemplateId = intval($_POST['teaserFrameTemplateId']);
        } elseif (isset($this->_objTeaser->arrTeaserFrames[$teaserFrameId])) {
             $teaserFrameTemplateId = $this->_objTeaser->arrTeaserFrames[$teaserFrameId]['frame_template_id'];
        } else {
            $teaserFrameTemplateId = $this->_objTeaser->getFirstTeaserFrameTemplateId();
        }

        $this->pageTitle = $teaserFrameId != 0 ? $_ARRAYLANG['TXT_EDIT_TEASER_BOX'] : $_ARRAYLANG['TXT_ADD_TEASER_BOX'];

        $this->_objTpl->setVariable(array(
            'NEWS_TEASER_FRAME_ID'              => $teaserFrameId,
            'NEWS_TEASER_FRAME_NAME'            => $teaserFrameName,
            'NEWS_TEASER_FRAME_TEMPLATE_MENU'   => $this->_objTeaser->getTeaserFrameTemplateMenu($teaserFrameTemplateId),
            'NEWS_TEASER_FRAME_PREVIEW'         => $this->_objTeaser->_getTeaserFrame($teaserFrameId, $teaserFrameTemplateId),
            'NEWS_TEASER_TITLE_TXT'             => $teaserFrameId != 0 ? $_ARRAYLANG['TXT_EDIT_TEASER_BOX'] : $_ARRAYLANG['TXT_ADD_TEASER_BOX']
        ));
        $this->_objTpl->parse('news_teasers_block');
    }

    function _placeholders()
    {
        global $_ARRAYLANG, $_CORELANG;

        $this->_objTpl->loadTemplateFile('module_news_placeholders.html');

        $this->_objTpl->setVariable(array(
            'TXT_PERFORM'                               => $_ARRAYLANG['TXT_PERFORM'],
            'TXT_CATEGORY'                              => $_ARRAYLANG['TXT_CATEGORY'],
            'TXT_DATE'                                  => $_ARRAYLANG['TXT_DATE'],
            'TXT_TITLE'                                 => $_ARRAYLANG['TXT_TITLE'],
            'TXT_NEWS_SITE'                             => $_ARRAYLANG['TXT_NEWS_SITE'],
            'TXT_NEWS_MESSAGE'                          => $_ARRAYLANG['TXT_NEWS_MESSAGE'],
            'TXT_HEADLINES'                             => $_ARRAYLANG['TXT_HEADLINES'],
            'TXT_TEASERS'                               => $_ARRAYLANG['TXT_TEASERS'],
            'TXT_USAGE'                                 => $_ARRAYLANG['TXT_USAGE'],
            'TXT_NEWS_PLACEHOLLDERS_USAGE_TEXT'         => $_ARRAYLANG['TXT_NEWS_PLACEHOLLDERS_USAGE_TEXT'],
            'TXT_PLACEHOLDER_LIST'                      => $_ARRAYLANG['TXT_PLACEHOLDER_LIST'],
            'TXT_LANGUAGE_VARIABLES'                    => $_ARRAYLANG['TXT_LANGUAGE_VARIABLES'],
            'TXT_GENERAL'                               => $_ARRAYLANG['TXT_GENERAL'],
            'TXT_NEWS_PAGIN_DESCRIPTION'                => $_ARRAYLANG['TXT_NEWS_PAGIN_DESCRIPTION'],
            'TXT_NEWS_NO_CATEGORY_DESCRIPTION'          => $_ARRAYLANG['TXT_NEWS_NO_CATEGORY_DESCRIPTION'],
            'TXT_NEWS_CAT_DROPDOWNMENU_DESCRIPTION'     => $_ARRAYLANG['TXT_NEWS_CAT_DROPDOWNMENU_DESCRIPTION'],
            'TXT_NEWS_DATE_DESCRIPTION'                 => $_ARRAYLANG['TXT_NEWS_DATE_DESCRIPTION'],
            'TXT_NEWS_LONG_DATE_DESCRIPTION'            => $_ARRAYLANG['TXT_NEWS_LONG_DATE_DESCRIPTION'],
            'TXT_NEWS_AUTHOR_DESCRIPTION'               => $_ARRAYLANG['TXT_NEWS_AUTHOR_DESCRIPTION'],
            'TXT_NEWS_LINK_DESCRIPTION'                 => $_ARRAYLANG['TXT_NEWS_LINK_DESCRIPTION'],
            'TXT_NEWS_CATEGORY_DESCRIPTION'             => $_ARRAYLANG['TXT_NEWS_CATEGORY_DESCRIPTION'],
            'TXT_NEWS_CSS_DESCRIPTION'                  => $_ARRAYLANG['TXT_NEWS_CSS_DESCRIPTION'],
            'TXT_NEWSROW_DESCRIPTION'                   => $_ARRAYLANG['TXT_NEWSROW_DESCRIPTION'],
            'TXT_EXAMPLE'                               => $_ARRAYLANG['TXT_EXAMPLE'],
            'TXT_NEWS_MESSAGES'                         => $_ARRAYLANG['TXT_NEWS_MESSAGES'],
            'TXT_NEWS_DETAILS_PLACEHOLLDERS_USAGE'      => $_ARRAYLANG['TXT_NEWS_DETAILS_PLACEHOLLDERS_USAGE'],
            'TXT_NEWS_TITLE_DESCRIPTION'                => $_ARRAYLANG['TXT_NEWS_TITLE_DESCRIPTION'],
            'TXT_NEWS_TEXT_DESCRIPTION'                 => $_ARRAYLANG['TXT_NEWS_TEXT_DESCRIPTION'],
            'TXT_NEWS_URL_DESCRIPTION'                  => $_ARRAYLANG['TXT_NEWS_URL_DESCRIPTION'],
            'TXT_PUBLISHED_ON'                          => $_ARRAYLANG['TXT_PUBLISHED_ON'],
            'TXT_HEADLINE_PLACEHOLLDERS_USAGE'          => $_ARRAYLANG['TXT_HEADLINE_PLACEHOLLDERS_USAGE'],
            'TXT_NEWS_IMAGE_PATH_DESCRIPTION'           => $_ARRAYLANG['TXT_NEWS_IMAGE_PATH_DESCRIPTION'],
            'TXT_HEADLINE_TEXT_DESCRIPTION'             => $_ARRAYLANG['TXT_HEADLINE_TEXT_DESCRIPTION'],
            'TXT_MORE_NEWS'                             => $_CORELANG['TXT_MORE_NEWS'],
            'TXT_TEASER_PLACEHOLLDERS_USAGE'            => $_ARRAYLANG['TXT_TEASER_PLACEHOLLDERS_USAGE'],
            'TXT_NEWS_LASTUPDATE_DESCRIPTION'           => $_ARRAYLANG['TXT_NEWS_LASTUPDATE_DESCRIPTION'],
            'TXT_NEWS_SOURCE_DESCRIPTION'               => $_ARRAYLANG['TXT_NEWS_SOURCE_DESCRIPTION'],
            'TXT_NEWS_IMAGE_DESCRIPTION'                => $_ARRAYLANG['TXT_NEWS_IMAGE_DESCRIPTION'],
            'TXT_HEADLINE_ID_DESCRIPTION'               => $_ARRAYLANG['TXT_HEADLINE_ID_DESCRIPTION'],
            'TXT_NEWS_IMAGE_LINK_DESCRIPTION'           => $_ARRAYLANG['TXT_NEWS_IMAGE_LINK_DESCRIPTION'],
            'TXT_NEWS_IMAGE_SRC_DESCRIPTION'            => $_ARRAYLANG['TXT_NEWS_IMAGE_SRC_DESCRIPTION'],
            'TXT_NEWS_IMAGE_ALT_DESCRIPTION'            => $_ARRAYLANG['TXT_NEWS_IMAGE_ALT_DESCRIPTION'],
            'TXT_NEWS_CATEGORY_NAME_DESCRIPTION'        => $_ARRAYLANG['TXT_NEWS_CATEGORY_NAME_DESCRIPTION'],
            'TXT_NEWS_IMAGE_ROW_DESCRIPTION'            => $_ARRAYLANG['TXT_NEWS_IMAGE_ROW_DESCRIPTION'],
            'TXT_NEWS_TEASER_TEXT_DESCRIPTION'          => $_ARRAYLANG['TXT_NEWS_TEASER_TEXT_DESCRIPTION'],
            'TXT_HEADLINE_AUTHOR_DESCRIPTION'           => $_ARRAYLANG['TXT_HEADLINE_AUTHOR_DESCRIPTION']
        ));
    }
}
?>
