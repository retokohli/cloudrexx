<?php
/**
 * Modul Admin Index
 *
 * CMS Administration
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Comvation Engineering Team
 * @version       $Id:    Exp $
 * @package     contrexx
 * @subpackage  admin
 * @todo        Edit PHP DocBlocks!
 */

//-------------------------------------------------------
// Set error reporting
//-------------------------------------------------------
if (0) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}


$startTime = explode(' ', microtime());
$adminPage = true;

/**
 * Path, database, FTP configuration settings
 *
 * Initialises global settings array and constants.
 */
include_once('../config/configuration.php');
/**
 * User configuration settings
 *
 * This file is re-created by the CMS itself. It initializes the
 * {@link $_CONFIG[]} global array.
 */
$incSettingsStatus = include_once('../config/settings.php');
/**
 * Version information
 *
 * Adds version information to the {@link $_CONFIG[]} global array.
 */
$incVersionStatus = include_once('../config/version.php');

//-------------------------------------------------------
// Check if system is installed
//-------------------------------------------------------
if (!defined('CONTEXX_INSTALLED') || !CONTEXX_INSTALLED) {
    header("Location: ../installer/index.php");
} elseif ($incSettingsStatus === false || $incVersionStatus === false) {
	die('System halted: Unable to load basic configuration!');
}

require_once '../core/API.php';

//-------------------------------------------------------
// Initialize database object
//-------------------------------------------------------
$errorMsg = '';
$objDatabase = getDatabaseObject($errorMsg);
if ($objDatabase === false) {
    die('Database error.');
}

//global $objDatabase; $objDatabase->debug = 1;

//-------------------------------------------------------
// Load settings and configuration
//-------------------------------------------------------

$objInit= new InitCMS($mode="backend");

$sessionObj= &new cmsSession();
$sessionObj->cmsSessionStatusUpdate($status="backend");

$objInit->_initBackendLanguage();
$objInit->getUserFrontendLangId();

$_LANGID = $objInit->getBackendLangId();
$_FRONTEND_LANGID = $objInit->userFrontendLangId;

//-------------------------------------------------------
// language array for the core system
//-------------------------------------------------------
$_CORELANG = $objInit->loadLanguageData('core');

//-------------------------------------------------------
// language array for all modules
//-------------------------------------------------------
$_ARRAYLANG = $objInit->loadLanguageData();
$_ARRAYLANG = array_merge($_ARRAYLANG, $_CORELANG);

$cmd = isset($_REQUEST['cmd']) ? $_REQUEST['cmd'] : "";

$objTemplate = &new HTML_Template_Sigma(ASCMS_ADMIN_TEMPLATE_PATH);
$objTemplate->setErrorHandling(PEAR_ERROR_DIE);

// language object from the Framework
$objLanguage = &new FWLanguage();

// Module object
$objModules = &new ModuleChecker();

$objAuth =&new Auth($type='backend');
$objPerm =&new Permission($type='backend');

//-----------------------------------------------------------------------------------------------
// Authentification start
//-----------------------------------------------------------------------------------------------
if (!$objAuth->checkAuth()) {
    $modulespath = ASCMS_CORE_PATH . "/imagecreator.php";
    if (file_exists($modulespath)) require_once($modulespath);
    else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);

    switch ($cmd) {
        case "secure":
            $_SESSION['auth']['secid'] = strtoupper(substr(md5(microtime()), 0, 4));
            getSecurityImage($id=$_SESSION['auth']['secid']);
            exit;
            break;

        case "lostpw":
            $objTemplate->loadTemplateFile('login_index.html');
            $objTemplate->addBlockfile('CONTENT_FILE', 'CONTENT_BLOCK', 'login_lost_password.html');
            $objTemplate->setVariable('TITLE', $_CORELANG['TXT_RESET_PASSWORD']);
            $objAuth->lostPassword($objTemplate);
            $objTemplate->show();
            exit;
            break;

        case "resetpw":
            $objTemplate->loadTemplateFile('login_index.html');
            $objTemplate->addBlockfile('CONTENT_FILE', 'CONTENT_BLOCK', 'login_reset_password.html');
            $objTemplate->setVariable('TITLE', $_CORELANG['TXT_SET_NEW_PASSWORD']);
            $objAuth->resetPassword($objTemplate);
            $objTemplate->show();
            exit;
            break;

        default:
            if(checkGDExtension()) {
                $loginSecurityCode = "<img src=\"index.php?cmd=secure\" alt=\"Security Code\" title=\"Security Code\"/>";
            } else {
                $_SESSION['auth']['secid'] = strtoupper(substr(md5(microtime()), 0, 4));
                $loginSecurityCode = $_SESSION['auth']['secid'];
            }

            $objTemplate->loadTemplateFile('login_index.html',true,true);
            $objTemplate->addBlockfile('CONTENT_FILE', 'CONTENT_BLOCK', 'login.html');

            $objTemplate->setVariable(array(
            'TXT_SECURITY_CODE'       => $_CORELANG['TXT_SECURITY_CODE'],
            'TXT_ENTER_SECURITY_CODE' => $_CORELANG['TXT_ENTER_SECURITY_CODE'],
            'TXT_USER_NAME'           => $_CORELANG['TXT_USER_NAME'],
            'TXT_PASSWORD'            => $_CORELANG['TXT_PASSWORD'],
            'TXT_LOGIN'               => $_CORELANG['TXT_LOGIN'],
            'TXT_PASSWORD_LOST'			=> $_CORELANG['TXT_PASSWORD_LOST'],
            'UID'                     => isset($_COOKIE['username']) ? $_COOKIE['username'] : '',
            'TITLE'                   => $_CORELANG['TXT_LOGIN'],
            'LOGIN_IMAGE'             => $loginSecurityCode,
            'LOGIN_ERROR_MESSAGE'     => $objAuth->errorMessage()
            ));

            $objTemplate->show();
            exit;
    }
}

//-----------------------------------------------------------------------------------------------
// Site start
//-----------------------------------------------------------------------------------------------
if (!isset($_REQUEST['standalone']) || $_REQUEST['standalone'] == 'false') {
    $objTemplate->loadTemplateFile('index.html');
    $objTemplate->addBlockfile('QUICKLINKS_CONTENT', 'quicklinks', 'quicklinks.html');
    $objTemplate->setVariable(
    	array(
    		'TXT_PAGE_ID'		=> $_CORELANG['TXT_PAGE_ID'],
    		'CONTREXX_CHARSET'	=> CONTREXX_CHARSET
    	)
    );
    $objTemplate->addBlockfile('CONTENT_OUTPUT', 'content_master', 'content_master.html');
}

switch($cmd) {
        //-----------------------------------------------------------------------------------------------
        // e-government
        //-----------------------------------------------------------------------------------------------
    case "egov":
        $modulespath = ASCMS_MODULE_PATH . "/egov/admin.class.php";
        if (file_exists($modulespath)) require_once($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_EGOVERNMENT'];
        $objEgov = &new eGov();
        $objEgov->getPage();
        break;

	    //-----------------------------------------------------------------------------------------------
	    // banner management
	    //-----------------------------------------------------------------------------------------------
    case "banner":
        // $objPerm->checkAccess(??, 'static');
        $modulespath = ASCMS_CORE_MODULE_PATH . "/banner/admin.class.php";
        if (file_exists($modulespath)) require_once($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_BANNER_ADMINISTRATION'];
        $objBanner = &new Banner();
        $objBanner->getPage();
        break;

        //-----------------------------------------------------------------------------------------------
        // file browser
        //-----------------------------------------------------------------------------------------------
    case "fileBrowser":
        $modulespath = ASCMS_CORE_MODULE_PATH . "/fileBrowser/admin.class.php";
        if (file_exists($modulespath)) require_once($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objFileBrowser = &new FileBrowser();
        $objFileBrowser->getPage();
        exit;
        break;

        //-----------------------------------------------------------------------------------------------
        // community
        //-----------------------------------------------------------------------------------------------
    case "community":
        //$objPerm->checkAccess(18, 'static');
        $modulespath = ASCMS_MODULE_PATH . "/community/admin.class.php";
        if (file_exists($modulespath)) require_once($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_COMMUNITY'];
        $objCommunity = &new Community();
        $objCommunity->getPage();
        break;

        //-----------------------------------------------------------------------------------------------
        // usermanagement
        //-----------------------------------------------------------------------------------------------
    case "user":
        //$objPerm->checkAccess(18, 'static');
        $modulespath = ASCMS_CORE_PATH . "/usermanagement.class.php";
        if (file_exists($modulespath)) require_once($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_USER_ADMINISTRATION'];
        $objUsers = &new userManagement();
        $objUsers->getPage();
        break;

        //-----------------------------------------------------------------------------------------------
        // feed
        //-----------------------------------------------------------------------------------------------
    case "feed":
        $objPerm->checkAccess(27, 'static');
        $modulespath = ASCMS_MODULE_PATH . "/feed/admin.class.php";
        if (file_exists($modulespath)) include($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle  = $_CORELANG['TXT_NEWS_SYNDICATION'];
        $objFeed  = &new feedManager();
        $objFeed->getFeedPage();
        break;

        //-----------------------------------------------------------------------------------------------
        // news-management
        //-----------------------------------------------------------------------------------------------
    case "server":
        $objPerm->checkAccess(4, 'static');
        $modulespath = ASCMS_CORE_PATH . "/serverSettings.class.php";
        if (file_exists($modulespath)) require_once($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_SERVER_INFO'];
        $objServer = &new serverSettings();
        $objServer->getPage();
        break;

        //-----------------------------------------------------------------------------------------------
        // log manager
        //-----------------------------------------------------------------------------------------------
    case "log":
        $objPerm->checkAccess(18, 'static');
        $modulespath = ASCMS_CORE_PATH . "/log.class.php";
        if (file_exists($modulespath)) require_once($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_LOG_ADMINISTRATION'];
        $objLogManager = &new logmanager();
        $objLogManager->getLogPage();
        break;

        //-----------------------------------------------------------------------------------------------
        // module manager
        //-----------------------------------------------------------------------------------------------
    case "shop":
        $objPerm->checkAccess(13, 'static');
        $modulespath = ASCMS_MODULE_PATH . "/shop/admin.class.php";
        if (file_exists($modulespath)) require_once($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_SHOP_ADMINISTRATION'];
        $objShopManager = &new shopmanager();
        $objShopManager->getShopPage();
        break;

        //-----------------------------------------------------------------------------------------------
        // themes: skins
        //-----------------------------------------------------------------------------------------------
    case "skins":
        //$objPerm->checkAccess(18, 'static');
        $modulespath = ASCMS_CORE_PATH . "/skins.class.php";
        if (file_exists($modulespath)) require_once($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_DESIGN_MANAGEMENT'];
        $objSkins = &new skins();
        $objSkins->getPage();
        break;

        //-----------------------------------------------------------------------------------------------
        // content management
        //-----------------------------------------------------------------------------------------------
    case "content":
        $modulespath = ASCMS_CORE_PATH . "/ContentManager.class.php";
        if (file_exists($modulespath)) require_once($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_CONTENT_MANAGER'];
        $objContent = &new ContentManager();
        $objContent->getPage();
        break;

        //-----------------------------------------------------------------------------------------------
        // content workflow
        //-----------------------------------------------------------------------------------------------
    case "workflow":
        $modulespath = ASCMS_CORE_PATH . "/ContentWorkflow.class.php";
        if (file_exists($modulespath)) require_once($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_CONTENT_HISTORY'];
        $objWorkflow = &new ContentWorkflow();
        $objWorkflow->getPage();
        break;

        //-----------------------------------------------------------------------------------------------
        // Document System Module
        //-----------------------------------------------------------------------------------------------
    case "docsys":
        $objPerm->checkAccess(11, 'static');
        $modulespath = ASCMS_MODULE_PATH . "/docsys/admin.class.php";
        if (file_exists($modulespath)) require_once($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_DOC_SYS_MANAGER'];
        $objDocSys = &new docSysManager();
        $objDocSys->getDocSysPage();
        break;

        //-----------------------------------------------------------------------------------------------
        // news-management
        //-----------------------------------------------------------------------------------------------
    case "news":
        $objPerm->checkAccess(10, 'static');
        $modulespath = ASCMS_CORE_MODULE_PATH . "/news/admin.class.php";
        if (file_exists($modulespath)) require_once($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_NEWS_MANAGER'];
        $objNews = &new NewsManager();
        $objNews->getPage();
        break;

        //-----------------------------------------------------------------------------------------------
        // contact-management
        //-----------------------------------------------------------------------------------------------
    case "contact":
        // $objPerm->checkAccess(10, 'static');
        $modulespath = ASCMS_CORE_MODULE_PATH . "/contact/admin.class.php";
        if (file_exists($modulespath)) require_once($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = "Kontaktmanager";
        $objContact = &new contactManager();
        $objContact->getPage();
        break;

        //-----------------------------------------------------------------------------------------------
        // Immo-management
        //-----------------------------------------------------------------------------------------------
	case "immo":
		$modulespath = ASCMS_MODULE_PATH . "/immo/admin.class.php";
		if (file_exists($modulespath)) require_once($modulespath);
		else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
	    $subMenuTitle = $_CORELANG['TXT_IMMO_MANAGEMENT'];
		$objImmo = &new Immo();
		$objImmo->getPage();
	break;

        //-----------------------------------------------------------------------------------------------
        // Livecam
        //-----------------------------------------------------------------------------------------------
    case "livecam":
        // $objPerm->checkAccess(9, 'static');
        $modulespath = ASCMS_MODULE_PATH . "/livecam/admin.class.php";
        if (file_exists($modulespath)) require_once($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_LIVECAM'];
        $objLivecam = &new LivecamManager();
        $objLivecam->getPage();
        break;

        //-----------------------------------------------------------------------------------------------
        // guestbook
        //-----------------------------------------------------------------------------------------------
    case "guestbook":
        $objPerm->checkAccess(9, 'static');
        $modulespath = ASCMS_MODULE_PATH . "/guestbook/admin.class.php";
        if (file_exists($modulespath)) require_once($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_GUESTBOOK'];
        $objGuestbook = &new GuestbookManager();
        $objGuestbook->getPage();
        break;

        //-----------------------------------------------------------------------------------------------
        // Memberdir
        //-----------------------------------------------------------------------------------------------
        case "memberdir":
            $objPerm->checkAccess(83, 'static');
            $modulespath = ASCMS_MODULE_PATH . "/memberdir/admin.class.php";
            if (file_exists($modulespath)) require_once($modulespath);
            else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
            $subMenuTitle = $_CORELANG['TXT_MEMBERDIR'];
            $objMemberdir = &new MemberDirManager();
            $objMemberdir->getPage();
        break;


        //-----------------------------------------------------------------------------------------------
        // Download
        //-----------------------------------------------------------------------------------------------
    case "download":
        $objPerm->checkAccess(57, 'static');
        $modulespath = ASCMS_MODULE_PATH . "/download/admin.class.php";
        if (file_exists($modulespath)) require_once($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_DOWNLOAD_MANAGER'];
        $objDownload = &new DownloadManager();
        $objDownload->getPage();
        break;

        //-----------------------------------------------------------------------------------------------
        // media manager
        //-----------------------------------------------------------------------------------------------
    case "media":
        $modulespath = ASCMS_CORE_MODULE_PATH . "/media/admin.class.php";
        if (file_exists($modulespath)) require_once($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle  = $_CORELANG['TXT_MEDIA_MANAGER'];
        $objMedia  = &new MediaManager();
        $objMedia->getMediaPage();
        break;


		//-----------------------------------------------------------------------------------------------
		// development
		//-----------------------------------------------------------------------------------------------
	case "development":
		     $objPerm->checkAccess(81, 'static');
		  $modulespath = ASCMS_CORE_MODULE_PATH . "/development/admin.class.php";
		  if (file_exists($modulespath)) require_once($modulespath);
		  else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
		  $subMenuTitle = $_CORELANG['TXT_DEVELOPMENT'];
		  $objDevelopment = &new Development();
		     $objDevelopment->getPage();
		 break;

        //-----------------------------------------------------------------------------------------------
        // backup
        //-----------------------------------------------------------------------------------------------
    case "backup":
        $objPerm->checkAccess(20, 'static');
        $modulespath = ASCMS_CORE_PATH . "/backup.class.php";
        if (file_exists($modulespath)) include($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle= $_CORELANG['TXT_OVERVIEW'];
        $statustxt = "";

        if(!isset($_GET['act'])){
            $_GET['act']="";
        }

        switch($_GET['act']){
            case "create":
                $objPerm->checkAccess(41, 'static');
                $strOkMessage = backup_create();
                break;
            case "restore":
                $objPerm->checkAccess(42, 'static');
                $strOkMessage = backup_restore();
                break;
            case "delete":
                $objPerm->checkAccess(43, 'static');
                $strOkMessage  =backup_delete();
                break;
            case "view":
                $objPerm->checkAccess(45, 'static');
                $othertxt = backup_view();
                break;
            case "viewtables":
                $objPerm->checkAccess(45, 'static');
                $othertxt = backup_viewTables();
                break;
            case "download":
                $objPerm->checkAccess(44, 'static');
                backup_download();
                break;
        }

        $objTemplate->setVariable(array(
        'CONTENT_OK_MESSAGE'		=> $strOkMessage,
        'CONTENT_STATUS_MESSAGE'	=> $strErrMessage,
        'CONTENT_TITLE'				=> $_CORELANG['TXT_BACKUP'],
        'CONTENT_NAVIGATION'		=> "<a href='?cmd=backup'>".$_CORELANG['TXT_OVERVIEW']."</a>"
        ));

        if (isset($othertxt)){
            $objTemplate->setVariable('ADMIN_CONTENT',$othertxt);
        } else {
            backup_showList();
        }
        break;
        //----------------------------------------------------------------------------------------------
        //stats
        //----------------------------------------------------------------------------------------------
    case "stats":
        $objPerm->checkAccess(19, 'static');
        $modulespath = ASCMS_CORE_MODULE_PATH . "/stats/admin.class.php";
        if (file_exists($modulespath)) include($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_STATISTIC'];
        $statistic= &new stats();
        $statistic->getContent();
        break;

        //----------------------------------------------------------------------------------------------
        // system update
        //----------------------------------------------------------------------------------------------
    case "systemUpdate":
        $objPerm->checkAccess(58, 'static');
        $modulespath = ASCMS_CORE_MODULE_PATH . "/systemUpdate/admin.class.php";
        if (file_exists($modulespath)) include($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_SYSTEM_UPDATE'];
        $systemUpdate = &new systemUpdate();
        $systemUpdate->getContent();
        break;

        //----------------------------------------------------------------------------------------------
        // alias
        //----------------------------------------------------------------------------------------------
	case "alias":
		$objPerm->checkAccess(115, 'static');
		$modulespath = ASCMS_CORE_MODULE_PATH . "/alias/admin.class.php";
		if (file_exists($modulespath)) include($modulespath);
		else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
		$subMenuTitle = $_CORELANG['TXT_ALIAS_ADMINISTRATION'];
		$objAlias = &new AliasAdmin();
		$objAlias->getPage();
		break;

        //-----------------------------------------------------------------------------------------------
        // nettools
        //-----------------------------------------------------------------------------------------------
    case "nettools":
        $objPerm->checkAccess(54, 'static');
        $modulespath = ASCMS_CORE_MODULE_PATH."/nettools/admin.class.php";
        if (file_exists($modulespath)) include($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_NETWORK_TOOLS'];
        $nettools = &new netToolsManager();
        $nettools->getContent();
        break;

        //-----------------------------------------------------------------------------------------------
        // newsletter
        //-----------------------------------------------------------------------------------------------
    case "newsletter":
        $modulespath = ASCMS_MODULE_PATH . "/newsletter/admin.class.php";
        if (file_exists($modulespath)) require_once($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_NEWSLETTER'];
        $objNewsletter = &new newsletter();
        $objNewsletter->getPage();
        break;

        //-----------------------------------------------------------------------------------------------
        // settings
        //-----------------------------------------------------------------------------------------------
    case "settings":
        $objPerm->checkAccess(17, 'static');
        $modulespath = ASCMS_CORE_PATH . "/settings.class.php";
        if (file_exists($modulespath)) require_once($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_SYSTEM_SETTINGS'];
        $objSettings = &new settingsManager();
        $objSettings->getPage();
        break;

        //-----------------------------------------------------------------------------------------------
        // language management
        //-----------------------------------------------------------------------------------------------
    case "language":
        $objPerm->checkAccess(22, 'static');
        $modulespath = ASCMS_CORE_PATH . "/language.class.php";
        if (file_exists($modulespath)) require_once($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_LANGUAGE_SETTINGS'];
        $objLanguage = &new LanguageManager();
        $objLanguage->getLanguagePage();
        break;

        //-----------------------------------------------------------------------------------------------
        // module manager
        //-----------------------------------------------------------------------------------------------
    case "modulemanager":
        $objPerm->checkAccess(23, 'static');
        $modulespath = ASCMS_CORE_PATH . "/modulemanager.class.php";
        if (file_exists($modulespath)) require_once($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle  = $_CORELANG['TXT_MODULE_MANAGER'];
        $objModuleManager = &new modulemanager();
        $objModuleManager->getModulesPage();
        break;

        //-----------------------------------------------------------------------------------------------
        // voting
        //-----------------------------------------------------------------------------------------------
    case "voting":
        $objPerm->checkAccess(14, 'static');
        $modulespath = ASCMS_MODULE_PATH . "/voting/admin.class.php";
        if (file_exists($modulespath)) include($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle  = $_CORELANG['TXT_CONTENT_MANAGER'];
        $objvoting  = &new votingmanager();
        $objvoting->getVotingPage();
        break;


        //-----------------------------------------------------------------------------------------------
        // survey
        //-----------------------------------------------------------------------------------------------
    case "survey":
    	$objPerm->checkAccess(111, 'static');
        $modulespath = ASCMS_MODULE_PATH . "/survey/admin.class.php";
        if (file_exists($modulespath)) include($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle  = $_CORELANG['TXT_SURVEY'];
        $objSurvey  = &new SurveyAdmin();
        $objSurvey->getPage();
        break;

        //-----------------------------------------------------------------------------------------------
        // calendar
        //-----------------------------------------------------------------------------------------------
    case "calendar":
        $objPerm->checkAccess(16, 'static');
        $modulespath = ASCMS_MODULE_PATH . "/calendar/admin.class.php";
        if (file_exists($modulespath)) include($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle  = $_CORELANG['TXT_CALENDAR'];
        $objCalendar  = &new calendarManager();
        $objCalendar->getCalendarPage();
        break;

    case "reservation":
        $modulespath = ASCMS_MODULE_PATH . "/reservation/admin.class.php";
        if (file_exists($modulespath)) require_once($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_RESERVATION_MODULE'];
        $objReservationModule = &new reservationManager();
        $objReservationModule->getPage();
    break;

        //-----------------------------------------------------------------------------------------------
        // Recommend
        //-----------------------------------------------------------------------------------------------
    case "recommend":
        $objPerm->checkAccess(64, 'static');
        $modulespath = ASCMS_MODULE_PATH . "/recommend/admin.class.php";
        if (file_exists($modulespath)) include($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle  = $_CORELANG['TXT_RECOMMEND'];
        $objCalendar  = &new RecommendManager();
        $objCalendar->getPage();
        break;

        //-----------------------------------------------------------------------------------------------
        // forum
        //-----------------------------------------------------------------------------------------------
    case "forum":
    	$objPerm->checkAccess(106, 'static');
        $modulespath = ASCMS_MODULE_PATH . "/forum/admin.class.php";
        if (file_exists($modulespath)) include($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle  = $_CORELANG['TXT_FORUM'];
        $objForum  = &new ForumAdmin();
        $objForum->getPage();
        break;

        //-----------------------------------------------------------------------------------------------
        // thumbnail gallery
        //-----------------------------------------------------------------------------------------------
    case "gallery":
        $objPerm->checkAccess(12, 'static');
        $modulespath = ASCMS_MODULE_PATH . "/gallery/admin.class.php";
        if (file_exists($modulespath)) require_once($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_GALLERY_TITLE'];
        $objGallery = &new galleryManager();
        $objGallery->getPage();
        break;

        //-----------------------------------------------------------------------------------------------
        // themes: directory
        //-----------------------------------------------------------------------------------------------
    case "directory":
        //$objPerm->checkAccess(18, 'static');
        $modulespath = ASCMS_MODULE_PATH . "/directory/admin.class.php";
        if (file_exists($modulespath)) require_once($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_LINKS_MODULE_DESCRIPTION'];
        $objDirectory = &new rssDirectory();
        $objDirectory->getPage();
        break;

        //-----------------------------------------------------------------------------------------------
        // block system
        //-----------------------------------------------------------------------------------------------
    case "block":
        $objPerm->checkAccess(76, 'static');
        $modulespath = ASCMS_MODULE_PATH . "/block/admin.class.php";
        if (file_exists($modulespath)) require_once($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_BLOCK_SYSTEM'];
        $objBlock = &new blockManager();
        $objBlock->getPage();
        break;

        //-----------------------------------------------------------------------------------------------
        // block system
        //-----------------------------------------------------------------------------------------------
    case "popup":
        $objPerm->checkAccess(117, 'static');
        $modulespath = ASCMS_MODULE_PATH . "/popup/admin.class.php";
        if (file_exists($modulespath)) require_once($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_POPUP_SYSTEM'];
        $objPopup = &new popupManager();
        $objPopup->getPage();
        break;


        //-----------------------------------------------------------------------------------------------
        // market
        //-----------------------------------------------------------------------------------------------
    case "market":
        $objPerm->checkAccess(98, 'static');
        $modulespath = ASCMS_MODULE_PATH . "/market/admin.class.php";
        if (file_exists($modulespath)) require_once($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_MARKET_TITLE'];
        $objMarket = &new Market();
        $objMarket->getPage();
        break;

        //-----------------------------------------------------------------------------------------------
        // podcast
        //-----------------------------------------------------------------------------------------------
    case "podcast":
        $objPerm->checkAccess(87, 'static');
        $modulespath = ASCMS_MODULE_PATH . "/podcast/admin.class.php";
        if (file_exists($modulespath)) require_once($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_PODCAST'];
        $objPodcast = &new podcastManager();
        $objPodcast->getPage();
        break;

    /**
     * Support System Module
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @since   1.2.0
     * @version 0.0.1 alpha
     */
    case "support":
        $objPerm->checkAccess(87, 'static');
        $modulespath = ASCMS_MODULE_PATH."/support/admin.class.php";
        if (file_exists($modulespath)) {
            require_once($modulespath);
        } else {
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        }
        $subMenuTitle = $_CORELANG['TXT_SUPPORT_SYSTEM'];
        $objSupport = new Support();
        $objSupport->getPage();
        break;

        //-----------------------------------------------------------------------------------------------
        // access denied
        //-----------------------------------------------------------------------------------------------
    case "noaccess":
        //Temporary no-acces-file and comment
        $subMenuTitle=$_CORELANG['TXT_ACCESS_DENIED'];
        $objTemplate->setVariable(array(
        'CONTENT_TITLE'				=> $_CORELANG['TXT_ACCESS_DENIED'],
        'CONTENT_NAVIGATION'		=> $_CONFIG['coreCmsName'],
        'CONTENT_STATUS_MESSAGE'	=> '',
        'ADMIN_CONTENT'				=> "<img src='images/stop_hand.gif' alt='' /><br /><br />".$_CORELANG['TXT_ACCESS_DENIED_DESCRIPTION']
        ));
        break;

        //-----------------------------------------------------------------------------------------------
        // logout
        //-----------------------------------------------------------------------------------------------
    case "logout":
        $objAuth->logout();
        exit;
        break;

        //-----------------------------------------------------------------------------------------------
        // show default admin page
        //-----------------------------------------------------------------------------------------------
    default:
        $modulespath = ASCMS_CORE_PATH."/myAdmin.class.php";
        if (file_exists($modulespath)) include($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_ADMINISTRATION_INDEX'];
        $objAdminNav = &new myAdminManager();
        $objAdminNav->getPage();
        break;
}

//-----------------------------------------------------------------------------------------------
// page parsing
//-----------------------------------------------------------------------------------------------

$finishTime = explode(' ', microtime());
$parsingTime = round(((float)$finishTime[0] + (float)$finishTime[1]) - ((float)$startTime[0] + (float)$startTime[1]), 5);


$objAdminNav = &new adminMenu();
$objAdminNav->getAdminNavbar();
$objTemplate->setVariable(array(
'SUB_MENU_TITLE' => $subMenuTitle,
'FRONTEND_LANG_MENU' => $objInit->getUserFrontendLangMenu(),
'TXT_GENERATED_IN' => $_CORELANG['TXT_GENERATED_IN'],
'TXT_SECONDS' => $_CORELANG['TXT_SECONDS'],
'TXT_LOGOUT_WARNING' => $_CORELANG['TXT_LOGOUT_WARNING'],
'PARSING_TIME'=> $parsingTime,
'LOGGED_NAME' => $_SESSION['auth']['name'],
'TXT_LOGGED_IN_AS' => $_CORELANG['TXT_LOGGED_IN_AS'],
'TXT_LOG_OUT' => $_CORELANG['TXT_LOG_OUT'],
'CONTENT_WYSIWYG_CODE' => get_wysiwyg_code()
));

if (isset($objTemplate->_variables['CONTENT_STATUS_MESSAGE']) && !empty($objTemplate->_variables['CONTENT_STATUS_MESSAGE'])) {
    $objTemplate->_variables['CONTENT_STATUS_MESSAGE'] = "<div id=\"alertbox\" style='overflow:auto'>".$objTemplate->_variables['CONTENT_STATUS_MESSAGE']."</div><br />";
}

if (!empty($objTemplate->_variables['CONTENT_OK_MESSAGE'])) {
	if (!isset($objTemplate->_variables['CONTENT_STATUS_MESSAGE'])) {
		$objTemplate->_variables['CONTENT_STATUS_MESSAGE'] = '';
	}
    $objTemplate->_variables['CONTENT_STATUS_MESSAGE'] .= "<div id=\"okbox\" style='overflow:auto'>".$objTemplate->_variables['CONTENT_OK_MESSAGE']."</div><br />";
}

$objTemplate->show();
?>
