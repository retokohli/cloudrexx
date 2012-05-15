<?php

/**
 * Modul Admin Index
 *
 * CMS Administration
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Engineering Team
 * @package     contrexx
 * @subpackage  admin
 */

/**
 * Debug level, see lib/DBG.php
 *   DBG_PHP             - show PHP errors/warnings/notices
 *   DBG_ADODB           - show ADODB queries
 *   DBG_ADODB_TRACE     - show ADODB queries with backtrace
 *   DBG_ADODB_ERROR     - show ADODB queriy errors only
 *   DBG_LOG_FILE        - DBG: log to file (/dbg.log)
 *   DBG_LOG_FIREPHP     - DBG: log via FirePHP
 *
 * Use DBG::activate($level) and DBG::deactivate($level)
 * to activate/deactivate a debug level.
 * Calling these methods without specifying a debug level
 * will either activate or deactivate all levels.
 */
include_once '../lib/DBG.php';
DBG::deactivate();
$startTime = explode(' ', microtime());
//DBG::activate(DBG_PHP);
//enable gzip compressing of the output - up to 75% smaller responses!
//commented out with java uploader l10n using pear http_download
//ob_start("ob_gzhandler");

$adminPage = true;
$_CONFIG = null;
/**
 * Environment repository
 */
require_once dirname(__FILE__).'/../core/Env.class.php';
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
$incSettingsStatus = include_once '../config/settings.php';
/**
 * Version information
 *
 * Adds version information to the {@link $_CONFIG[]} global array.
 */
$incVersionStatus = include_once '../config/version.php';

Env::set('config', $_CONFIG);

/**
 * Doctrine configuration
 */
require_once '../config/doctrine.php';
// Check whether the system is installed
if (!defined('CONTEXX_INSTALLED') || !CONTEXX_INSTALLED) {
    header("Location: ../installer/index.php");
    exit;
}
if (!$incSettingsStatus || !$incVersionStatus) {
    die('System halted: Unable to load basic configuration!');
}

require_once '../core/API.php' ;
require_once '../lib/CSRF.php' ;
require_once '../core/PageGuard.class.php';

// Initialize database object
$strErrMessage = '';
$objDatabase = getDatabaseObject($strErrMessage);
Env::set('db', $objDatabase);
Env::set('pageguard', new PageGuard($objDatabase));

if ($objDatabase === false) {
    die('Database error: '.$strErrMessage);
}

if (DBG::getMode() & DBG_ADODB_TRACE) {
    DBG::enable_adodb_debug(true);
} elseif (DBG::getMode() & DBG_ADODB || DBG::getMode() & DBG_ADODB_ERROR) {
    DBG::enable_adodb_debug();
} else {
    DBG::disable_adodb_debug();
}

createModuleConversionTables();
// Load settings and configuration
$objInit = new InitCMS('backend', Env::em());
Env::set('init', $objInit);

$sessionObj = new cmsSession();
$sessionObj->cmsSessionStatusUpdate('backend');

$objInit->_initBackendLanguage();
$objInit->getUserFrontendLangId();

$_LANGID = $objInit->getBackendLangId();
$_FRONTEND_LANGID = $objInit->userFrontendLangId;
/**
 * Language constants
 *
 * Defined as follows:
 * - BACKEND_LANG_ID is set to the visible backend language
 *   in the backend *only*.  In the frontend, it is *NOT* defined!
 *   It indicates a backend user and her currently selected language.
 *   Use this in methods that are intended *for backend use only*.
 *   It *MUST NOT* be used to determine the language for any kind of content!
 * - FRONTEND_LANG_ID is set to the selected frontend or content language
 *   both in the back- and frontend.
 *   It *always* represents the language of content being viewed or edited.
 *   Use FRONTEND_LANG_ID for that purpose *only*!
 * - LANG_ID is set to the same value as BACKEND_LANG_ID in the backend,
 *   and to the same value as FRONTEND_LANG_ID in the frontend.
 *   It *always* represents the current users' selected language.
 *   It *MUST NOT* be used to determine the language for any kind of content!
 * @since 2.2.0
 */
define('FRONTEND_LANG_ID', $_FRONTEND_LANGID);
define('BACKEND_LANG_ID', $_LANGID);
define('LANG_ID', $_LANGID);

/**
 * Core language data
 * @ignore
 */
$_CORELANG = $objInit->loadLanguageData('core');
Env::set('coreLang', $_CORELANG);

$cmd = isset($_REQUEST['cmd']) ? $_REQUEST['cmd'] : '';
$act = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';


// Load the JS helper class and set the offset
require_once ASCMS_DOCUMENT_ROOT.'/lib/FRAMEWORK/Javascript.class.php';
JS::setOffset('../');


// To clone any module, use an optional integer cmd suffix.
// E.g.: "shop2", "gallery5", etc.
// Mind that you *MUST* copy all necessary database tables, and fix any
// references to that module (section and cmd parameters, database tables)
// using the MODULE_INDEX constant in the right place both in your code
// *and* templates!
// See the Shop module for a working example and instructions on how to
// clone any module.
$arrMatch = array();
$plainCmd = $cmd;
if (preg_match('/^(\D+)(\d+)$/', $cmd, $arrMatch)) {
    // The plain section/module name, used below
    $plainCmd = $arrMatch[1];
}
// The module index.
// Set to the empty string for the first instance (#1),
// and to an integer number of 2 or greater for any clones.
// This guarantees full backward compatibility with old code, templates
// and database tables for the default instance.
$moduleIndex = (empty($arrMatch[2]) ? '' : $arrMatch[2]);

/**
 * @ignore
 */
define('MODULE_INDEX', (intval($moduleIndex) == 0) ? '' : intval($moduleIndex));
// Simple way to distinguish any number of cloned modules
// and apply individual access rights.  This offset is added
// to any static access ID before checking it.
$intAccessIdOffset = intval(MODULE_INDEX)*1000;

// If standalone is set, then we will not have to initialize/load any content page related stuff
$isRegularPageRequest = !isset($_REQUEST['standalone']) || $_REQUEST['standalone'] == 'false';

$objFWUser = FWUser::getFWUserObject();

/* authentification */
$loggedIn = $objFWUser->objUser->login(true); //check if the user is already logged in
if (!empty($_POST) && !$loggedIn) { //not logged in already - do captcha and password checks
    $objFWUser->checkAuth();
}

// User only gets the backend if he's logged in
if (!$objFWUser->objUser->login(true)) {
    $plainCmd = 'login';

    // If the user isn't logged in, the login mask will be showed.
    // This mask has its own template handling.
    // So we don't need to load any templates in the index.php.
    $isRegularPageRequest = false;
} else {
    $userData = array(
        'id'   => FWUser::getFWUserObject()->objUser->getId(),
        'name' => FWUser::getFWUserObject()->objUser->getUsername(),
    );
    $loggableListener->setUsername(json_encode($userData));
}

/**
 * Module specific language data
 * @ignore
 */
$_ARRAYLANG = $objInit->loadLanguageData($plainCmd);
$_ARRAYLANG = array_merge($_ARRAYLANG, $_CORELANG);
Env::set('lang', $_ARRAYLANG);

// CSRF code needs to be even in the login form. otherwise, we
// could not do a super-generic check later.. NOTE: do NOT move
// this above the "new cmsSession" line!
CSRF::add_code();

// Site start
if ($isRegularPageRequest) {
    $objTemplate = new HTML_Template_Sigma(ASCMS_ADMIN_TEMPLATE_PATH);
    // TODO: Does CSRF::add_placeholder() really work before a template is loaded?
    CSRF::add_placeholder($objTemplate);
    $objTemplate->setErrorHandling(PEAR_ERROR_DIE);

    $objTemplate->loadTemplateFile('index.html');
    $objTemplate->addBlockfile('CONTENT_FILE', 'index_content', 'index_content.html');
    $objTemplate->touchBlock('backend_metanavigation');
    JS::activate('backend');
    JS::activate('tipmessage');
    JS::activate('chosen');
    
    // No longer needed in v3.0
    /*if (Permission::checkAccess(35, 'static', true)) {
        $objTemplate->addBlockfile('QUICKLINKS_CONTENT', 'quicklinks', 'quicklinks.html');
    }*/
    
    $objTemplate->setVariable(array(
        'TXT_PAGE_ID'      => $_CORELANG['TXT_PAGE_ID'],
        'CONTREXX_CHARSET' => CONTREXX_CHARSET,
        'CONTAINER_CLASS'  => empty($plainCmd) ? 'backend dashboard' : 'backend',
        'USER_ID'          => $objFWUser->objUser->getId(),
    ));
    
    // Skip the nav/language bar for modules which don't make use of either.
    // TODO: Remove language selector for modules which require navigation but bring their own language management.
    $skipMaster = array('content');
    if (in_array($plainCmd, $skipMaster)) {
        $objTemplate->addBlockfile('CONTENT_OUTPUT', 'content_master', 'content_master_stripped.html');
    } else {
        $objTemplate->addBlockfile('CONTENT_OUTPUT', 'content_master', 'content_master.html');
    }
}

// CSRF protection. From this point on, we can assume that
// the user is logged in, but nothing else has happened.
// Note that we only do the check as long as there's no
// cmd given; this is so we can reload the main screen if
// the check has failed somehow.
// fileBrowser is an exception, as it eats CSRF codes like
// candy. We're doing CSRF::check_code() in the relevant
// parts in the module instead.
if (!empty($plainCmd) and !in_array($plainCmd, array('fileBrowser', 'upload'))) {
    CSRF::check_code();
}

switch ($plainCmd) {
    case 'login':
        if (!include_once(ASCMS_CORE_MODULE_PATH.'/login/admin.class.php'))
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objLoginManager = new LoginManager();
        $objLoginManager->getPage();
        break;
    case 'access':
        if (!include_once ASCMS_CORE_MODULE_PATH."/access/admin.class.php")
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_COMMUNITY'];
        $objAccessManager = new AccessManager();
        $objAccessManager->getPage();
        break;
    case 'egov':
        Permission::checkAccess(109, 'static');
        if (!include_once ASCMS_MODULE_PATH.'/egov/admin.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_EGOVERNMENT'];
        $objEgov = new eGov();
        $objEgov->getPage();
        break;
    case 'banner':
        // Permission::checkAccess(??, 'static');
        if (!include_once ASCMS_CORE_MODULE_PATH.'/banner/admin.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_BANNER_ADMINISTRATION'];
        $objBanner = new Banner();
        $objBanner->getPage();
        break;
    case 'jobs':
        Permission::checkAccess(11, 'static');
        if (!include_once ASCMS_MODULE_PATH.'/jobs/admin.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_JOBS_MANAGER'];
        $objJobs = new jobsManager();
        $objJobs->getJobsPage();
        break;
    case 'fileBrowser':
        if (!include_once ASCMS_CORE_MODULE_PATH.'/fileBrowser/admin.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objFileBrowser = new FileBrowser();
        $objFileBrowser->getPage();
        exit;
        break;
    case 'feed':
        Permission::checkAccess(27, 'static');
        if (!include_once ASCMS_MODULE_PATH.'/feed/admin.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_NEWS_SYNDICATION'];
        $objFeed = new feedManager();
        $objFeed->getFeedPage();
        break;
    case 'server':
        Permission::checkAccess(4, 'static');
        if (!include_once ASCMS_CORE_PATH.'/serverSettings.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_SERVER_INFO'];
        $objServer = new serverSettings();
        $objServer->getPage();
        break;
    case 'log':
        Permission::checkAccess(18, 'static');
        if (!include_once ASCMS_CORE_PATH.'/log.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_LOG_ADMINISTRATION'];
        $objLogManager = new logmanager();
        $objLogManager->getLogPage();
        break;
    case 'shop':
        Permission::checkAccess($intAccessIdOffset+13, 'static');
        if (!include_once ASCMS_MODULE_PATH.'/shop/admin.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_SHOP_ADMINISTRATION'];
        $objShopManager = new shopmanager();
        $objShopManager->getPage();
        break;
    case 'skins':
        //Permission::checkAccess(18, 'static');
        if (!include_once ASCMS_CORE_PATH.'/skins.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_DESIGN_MANAGEMENT'];
        $objSkins = new skins();
        $objSkins->getPage();
        break;
// TODO: Remove this and cleanup other remnants of old CM
    case 'content_old':
        if (!include_once ASCMS_CORE_PATH.'/ContentManager.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_CONTENT_MANAGER'];
        $objContent = new ContentManager();
        $objContent->getPage();
        break;
    case 'content':
        if (!include_once ASCMS_CORE_PATH.'/ContentManager2.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_CONTENT_MANAGER'];
        $cm = new ContentManager($act, $objTemplate, $objDatabase, $objInit);
        $cm->getPage();
        break;
// TODO: handle expired sessions in any xhr callers.
    case 'jsondata':
        if (!include_once ASCMS_CORE_PATH.'/json/JsonData.class.php')
// TODO: This probably doesn't handle an error message very well?
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $json = new \Cx\Core\Json\JsonData();
        $adapter = contrexx_input2raw($_GET['object']);
        $method = contrexx_input2raw($_GET['act']);
// TODO: Replace arguments by something reasonable
        $arguments = array('get' => $_GET, 'post' => $_POST);
        echo $json->jsondata($adapter, $method, $arguments);
        die();
    case 'workflow':
        if (!include_once ASCMS_CORE_PATH.'/ContentWorkflow.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_CONTENT_HISTORY'];
        $wf = new ContentWorkflow($act, $objTemplate, $objDatabase, $objInit);
        $wf->getPage();
        break;
    case 'docsys':
        Permission::checkAccess(11, 'static');
        if (!include_once ASCMS_MODULE_PATH.'/docsys/admin.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_DOC_SYS_MANAGER'];
        $objDocSys = new docSysManager();
        $objDocSys->getDocSysPage();
        break;
    case 'news':
        Permission::checkAccess(10, 'static');
        if (!include_once ASCMS_CORE_MODULE_PATH.'/news/admin.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_NEWS_MANAGER'];
        $objNews = new NewsManager();
        $objNews->getPage();
        break;
    case 'contact':
        // Permission::checkAccess(10, 'static');
        if (!include_once ASCMS_CORE_MODULE_PATH.'/contact/admin.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_CONTACTS'];
        $objContact = new contactManager();
        $objContact->getPage();
        break;
    case 'immo':
        if (!include_once ASCMS_MODULE_PATH.'/immo/admin.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_IMMO_MANAGEMENT'];
        $objImmo = new Immo();
        $objImmo->getPage();
        break;
    case 'livecam':
        // Permission::checkAccess(9, 'static');
        if (!include_once ASCMS_MODULE_PATH.'/livecam/admin.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_LIVECAM'];
        $objLivecam = new LivecamManager();
        $objLivecam->getPage();
        break;
    case 'guestbook':
        Permission::checkAccess(9, 'static');
        if (!include_once ASCMS_MODULE_PATH.'/guestbook/admin.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_GUESTBOOK'];
        $objGuestbook = new GuestbookManager();
        $objGuestbook->getPage();
        break;
        // dataviewer
    case 'dataviewer':
        Permission::checkAccess(9, 'static');
        if (!include_once ASCMS_MODULE_PATH.'/dataviewer/admin.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_DATAVIEWER'];
        $objDataviewer = new Dataviewer();
        $objDataviewer->getPage();
        break;
    case 'memberdir':
        Permission::checkAccess(83, 'static');
        if (!include_once ASCMS_MODULE_PATH.'/memberdir/admin.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_MEMBERDIR'];
        $objMemberdir = new MemberDirManager();
        $objMemberdir->getPage();
        break;
    case 'download':
        Permission::checkAccess(57, 'static');
        if (!include_once ASCMS_MODULE_PATH.'/download/admin.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_DOWNLOAD_MANAGER'];
        $objDownload = new DownloadManager();
        $objDownload->getPage();
        break;
    case 'media':
        if (!include_once ASCMS_CORE_MODULE_PATH.'/media/admin.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_MEDIA_MANAGER'];
        $objMedia = new MediaManager();
        $objMedia->getMediaPage();
        break;
    case 'development':
        Permission::checkAccess(81, 'static');
        if (!include_once ASCMS_CORE_MODULE_PATH.'/development/admin.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_DEVELOPMENT'];
        $objDevelopment = new Development();
        $objDevelopment->getPage();
        break;
    case 'dbm':
        if (!include_once ASCMS_CORE_PATH.'/DatabaseManager.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_DATABASE_MANAGER'];
        $objDatabaseManager = new DatabaseManager();
        $objDatabaseManager->getPage();
        break;
    case 'stats':
        Permission::checkAccess(19, 'static');
        if (!include_once ASCMS_CORE_MODULE_PATH.'/stats/admin.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_STATISTIC'];
        $statistic= new stats();
        $statistic->getContent();
        break;
    case 'alias':
        Permission::checkAccess(115, 'static');
        Permission::checkAccess(78, 'static');
        if (!include_once ASCMS_CORE_MODULE_PATH.'/alias/admin.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_ALIAS_ADMINISTRATION'];
        $objAlias = new AliasAdmin();
        $objAlias->getPage();
        break;
    case 'nettools':
        Permission::checkAccess(54, 'static');
        if (!include_once ASCMS_CORE_MODULE_PATH.'/nettools/admin.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_NETWORK_TOOLS'];
        $nettools = new netToolsManager();
        $nettools->getContent();
        break;
    case 'newsletter':
        if (!include_once ASCMS_MODULE_PATH.'/newsletter/admin.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_NEWSLETTER'];
        $objNewsletter = new newsletter();
        $objNewsletter->getPage();
        break;
    case 'settings':
        Permission::checkAccess(17, 'static');
        if (!include_once ASCMS_CORE_PATH.'/settings.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_SYSTEM_SETTINGS'];
        $objSettings = new settingsManager();
        $objSettings->getPage();
        break;
    case 'language':
        Permission::checkAccess(22, 'static');
        if (!include_once ASCMS_CORE_PATH.'/language.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_LANGUAGE_SETTINGS'];
        $objLangManager = new LanguageManager();
        $objLangManager->getLanguagePage();
        break;
    case 'modulemanager':
        Permission::checkAccess(23, 'static');
        if (!include_once ASCMS_CORE_PATH.'/modulemanager.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_MODULE_MANAGER'];
        $objModuleManager = new modulemanager();
        $objModuleManager->getModulesPage();
        break;
    case 'ecard':
        if (!include_once ASCMS_MODULE_PATH.'/ecard/admin.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_ECARD_TITLE'];
        $objEcard = new ecard();
        $objEcard->getPage();
        break;
    case 'voting':
        Permission::checkAccess(14, 'static');
        if (!include_once ASCMS_MODULE_PATH.'/voting/admin.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_CONTENT_MANAGER'];
        $objvoting = new votingmanager();
        $objvoting->getVotingPage();
        break;
    case 'survey':
        Permission::checkAccess(111, 'static');
        if (!include_once ASCMS_MODULE_PATH.'/survey/admin.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_SURVEY'];
        $objSurvey = new SurveyAdmin();
        $objSurvey->getPage();
        break;
    case 'calendar':
        Permission::checkAccess(16, 'static');
        define('CALENDAR_MANDATE', MODULE_INDEX);
        if (!include_once ASCMS_MODULE_PATH.'/calendar'.MODULE_INDEX.'/admin.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_CALENDAR'];
        $objCalendar = new calendarManager();
        $objCalendar->getCalendarPage();
        break;
    case 'reservation':
        if (!include_once ASCMS_MODULE_PATH.'/reservation/admin.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_RESERVATION_MODULE'];
        $objReservationModule = new reservationManager();
        $objReservationModule->getPage();
        break;
    case 'recommend':
        Permission::checkAccess(64, 'static');
        if (!include_once ASCMS_MODULE_PATH.'/recommend/admin.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_RECOMMEND'];
        $objCalendar = new RecommendManager();
        $objCalendar->getPage();
        break;
    case 'forum':
        Permission::checkAccess(106, 'static');
        if (!include_once ASCMS_MODULE_PATH.'/forum/admin.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_FORUM'];
        $objForum = new ForumAdmin();
        $objForum->getPage();
        break;
    case 'gallery':
        Permission::checkAccess(12, 'static');
        if (!include_once ASCMS_MODULE_PATH.'/gallery/admin.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_GALLERY_TITLE'];
        $objGallery = new galleryManager();
        $objGallery->getPage();
        break;
    case 'directory':
        //Permission::checkAccess(18, 'static');
        if (!include_once ASCMS_MODULE_PATH.'/directory/admin.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_LINKS_MODULE_DESCRIPTION'];
        $objDirectory = new rssDirectory();
        $objDirectory->getPage();
        break;
    case 'block':
        Permission::checkAccess(76, 'static');
        if (!include_once ASCMS_MODULE_PATH.'/block/admin.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_BLOCK_SYSTEM'];
        $objBlock = new blockManager();
        $objBlock->getPage();
        break;
    case 'popup':
        Permission::checkAccess(117, 'static');
        if (!include_once ASCMS_MODULE_PATH.'/popup/admin.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_POPUP_SYSTEM'];
        $objPopup = new popupManager();
        $objPopup->getPage();
        break;
    case 'market':
        Permission::checkAccess(98, 'static');
        if (!include_once ASCMS_MODULE_PATH.'/market/admin.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_MARKET_TITLE'];
        $objMarket = new Market();
        $objMarket->getPage();
        break;
    case 'data':
        Permission::checkAccess(122, 'static'); // ID !!
        if (!include_once ASCMS_MODULE_PATH."/data/admin.class.php")
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_DATA_MODULE'];
        $objData = new DataAdmin();
        $objData->getPage();
        break;
    case 'podcast':
        Permission::checkAccess(87, 'static');
        if (!include_once ASCMS_MODULE_PATH.'/podcast/admin.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_PODCAST'];
        $objPodcast = new podcastManager();
        $objPodcast->getPage();
        break;
    case 'support':
        // TODO: Assign a proper access ID to the support module
        //Permission::checkAccess(??, 'static');
        Permission::checkAccess(87, 'static');
        if (!include_once ASCMS_MODULE_PATH.'/support/admin.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_SUPPORT_SYSTEM'];
        $objSupport = new Support();
        $objSupport->getPage();
        break;
    case 'blog':
        Permission::checkAccess(119, 'static');
        if (!include_once ASCMS_MODULE_PATH.'/blog/admin.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_BLOG_MODULE'];
        $objBlog = new BlogAdmin();
        $objBlog->getPage();
        break;
    case 'knowledge':
        Permission::checkAccess(129, 'static');
        if (!include_once ASCMS_MODULE_PATH.'/knowledge/admin.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_KNOWLEDGE'];
        $objKnowledge = new KnowledgeAdmin();
        $objKnowledge->getPage();
        break;
    case 'u2u':
        Permission::checkAccess(141, 'static');
        if (!include_once ASCMS_MODULE_PATH.'/u2u/admin.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_U2U_MODULE'];
        $objU2u = new u2uAdmin();
        $objU2u->getPage();
        break;
    case 'partners':
        Permission::checkAccess(140, 'static');
        if (!include_once ASCMS_MODULE_PATH.'/partners/admin.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_PARTNERS_MODULE'];
        $objPartner = new PartnersAdmin();
        $objPartner->getPage();
        break;
    case 'auction':
        Permission::checkAccess(143, 'static');
        if (!include_once ASCMS_MODULE_PATH.'/auction/admin.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_AUCTION_TITLE'];
        $objAuction = new Auction();
        $objAuction->getPage();
        break;
    case 'upload':
        if (!include_once ASCMS_CORE_MODULE_PATH.'/upload/admin.class.php')
            die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objUploadModule = new Upload();
        $objUploadModule->getPage();
        //execution never reaches this point
        break;
    case 'noaccess':
        //Temporary no-acces-file and comment
        $subMenuTitle = $_CORELANG['TXT_ACCESS_DENIED'];
        $objTemplate->setVariable(array(
            'CONTENT_TITLE' => $_CORELANG['TXT_ACCESS_DENIED'],
            'CONTENT_NAVIGATION' => contrexx_raw2xhtml($_CONFIG['coreCmsName']),
//            'CONTENT_STATUS_MESSAGE' => '',
            'ADMIN_CONTENT' =>
                '<img src="images/stop_hand.gif" alt="" /><br /><br />'.
                $_CORELANG['TXT_ACCESS_DENIED_DESCRIPTION'],
        ));
        break;
    case 'logout':
        $objFWUser->logout();
        exit;
    case 'downloads':
        if (!include_once ASCMS_MODULE_PATH.'/downloads/admin.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_DOWNLOADS'];
        $objDownloadsModule = new downloads();
        $objDownloadsModule->getPage();
        break;
    case 'country':
// TODO: Move this define() somewhere else, allocate the IDs properly
        define('PERMISSION_COUNTRY_VIEW', 145);
        define('PERMISSION_COUNTRY_EDIT', 146);
        Permission::checkAccess(PERMISSION_COUNTRY_VIEW, 'static');
        if (!include_once ASCMS_CORE_PATH.'/Country.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_CORE_COUNTRY'];
        Country::getPage();
        break;
    case 'mediadir':
        Permission::checkAccess(153, 'static');
        if (!include_once ASCMS_MODULE_PATH.'/mediadir/admin.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $subMenuTitle = $_CORELANG['TXT_MEDIADIR_MODULE'];
        $objMediaDirectory = new mediaDirectoryManager();
        $objMediaDirectory->getPage();
        break;
    default:
        if (!include_once ASCMS_CORE_PATH.'/myAdmin.class.php')
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objFWUser = FWUser::getFWUserObject();
        $subMenuTitle = $_CORELANG['TXT_WELCOME_MESSAGE'].", <a href='index.php?cmd=access&amp;act=user&amp;tpl=modify&amp;id=".$objFWUser->objUser->getId()."' title='".$objFWUser->objUser->getId()."'>".($objFWUser->objUser->getProfileAttribute('firstname') || $objFWUser->objUser->getProfileAttribute('lastname') ? htmlentities($objFWUser->objUser->getProfileAttribute('firstname'), ENT_QUOTES, CONTREXX_CHARSET).' '.htmlentities($objFWUser->objUser->getProfileAttribute('lastname'), ENT_QUOTES, CONTREXX_CHARSET) : htmlentities($objFWUser->objUser->getUsername(), ENT_QUOTES, CONTREXX_CHARSET))."</a>";
        $objAdminNav = new myAdminManager();
        $objAdminNav->getPage();
        break;
}

if (isset($_POST['redirect']) && preg_match('/\.php/', $_POST['redirect'])) {
    CSRF::header('location: '.$_POST['redirect']);
}

// page parsing
$finishTime = explode(' ', microtime());
$parsingTime = round(((float)$finishTime[0] + (float)$finishTime[1]) - ((float)$startTime[0] + (float)$startTime[1]), 5);

$objAdminNav = new adminMenu($plainCmd);
$objAdminNav->getAdminNavbar();
$objTemplate->setVariable(array(
    'SUB_MENU_TITLE' => $subMenuTitle,
    'FRONTEND_LANG_MENU' => $objInit->getUserFrontendLangMenu(),
    'TXT_GENERATED_IN' => $_CORELANG['TXT_GENERATED_IN'],
    'TXT_SECONDS' => $_CORELANG['TXT_SECONDS'],
    'TXT_LOGOUT_WARNING' => $_CORELANG['TXT_LOGOUT_WARNING'],
    'PARSING_TIME'=> $parsingTime,
    'LOGGED_NAME' => htmlentities($objFWUser->objUser->getProfileAttribute('firstname').' '.$objFWUser->objUser->getProfileAttribute('lastname'), ENT_QUOTES, CONTREXX_CHARSET),
    'TXT_LOGGED_IN_AS' => $_CORELANG['TXT_LOGGED_IN_AS'],
    'TXT_LOG_OUT' => $_CORELANG['TXT_LOG_OUT'],
// TODO: This function call returns the empty string -- always!  What's the use?
    'CONTENT_WYSIWYG_CODE' => get_wysiwyg_code(),
    // Mind: The module index is not used in any non-module template
    // for the time being, but is provided for future use and convenience.
    'MODULE_INDEX' => MODULE_INDEX,
    // The Shop module for one heavily uses custom JS code that is properly
    // handled by that class -- finally
    'JAVASCRIPT' => JS::getCode(),
));

// TODO: This would better be handled by the Message class
if (!empty($objTemplate->_variables['CONTENT_STATUS_MESSAGE'])) {
    $objTemplate->_variables['CONTENT_STATUS_MESSAGE'] =
        '<div id="alertbox">'.
        $objTemplate->_variables['CONTENT_STATUS_MESSAGE'].'</div>';
}
if (!empty($objTemplate->_variables['CONTENT_OK_MESSAGE'])) {
    if (!isset($objTemplate->_variables['CONTENT_STATUS_MESSAGE'])) {
        $objTemplate->_variables['CONTENT_STATUS_MESSAGE'] = '';
    }
    $objTemplate->_variables['CONTENT_STATUS_MESSAGE'] .=
        '<div id="okbox">'.
        $objTemplate->_variables['CONTENT_OK_MESSAGE'].'</div>';
}
if (!empty($objTemplate->_variables['CONTENT_WARNING_MESSAGE'])) {
    $objTemplate->_variables['CONTENT_STATUS_MESSAGE'] .=
        '<div class="warningbox">'.
        $objTemplate->_variables['CONTENT_WARNING_MESSAGE'].'</div>';
}

if (empty($cmd)) {
    $cmd = 'dashboard';
}

// Style parsing
if (file_exists(ASCMS_ADMIN_TEMPLATE_PATH.'/css/'.$cmd.'.css')) {
    // check if there's a css file in the core section
    $objTemplate->setVariable('ADD_STYLE_URL', ASCMS_ADMIN_TEMPLATE_WEB_PATH.'/css/'.$cmd.'.css');
    $objTemplate->parse('additional_style');
} elseif (file_exists(ASCMS_MODULE_PATH.'/'.$cmd.'/template/backend.css')) {
    // of maybe in the current module directory
    $objTemplate->setVariable('ADD_STYLE_URL', ASCMS_MODULE_WEB_PATH.'/'.$cmd.'/template/backend.css');
    $objTemplate->parse('additional_style');
} elseif (file_exists(ASCMS_CORE_MODULE_PATH.'/'.$cmd.'/template/backend.css')) {
    // or in the core module directory
    $objTemplate->setVariable('ADD_STYLE_URL', ASCMS_CORE_MODULE_WEB_PATH.'/'.$cmd.'/template/backend.css');
    $objTemplate->parse('additional_style');
} else {
    $objTemplate->hideBlock('additional_style');
}

CSRF::add_placeholder($objTemplate);
$objTemplate->show();
