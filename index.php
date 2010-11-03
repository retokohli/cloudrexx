<?php

/**
 * The main page for the CMS
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team
 * @version     v2.1.0 beta
 * @package     contrexx
 * @subpackage  core
 * @link        http://www.contrexx.com/ contrexx homepage
 * @since       v0.0.0.0
 * @todo        Capitalize all class names in project
 * @uses        /config/configuration.php
 * @uses        /config/settings.php
 * @uses        /config/version.php
 * @uses        /core/API.php
 * @uses        /core_modules/cache/index.class.php
 * @uses        /core/error.class.php
 * @uses        /core_modules/banner/index.class.php
 * @uses        /core_modules/contact/index.class.php
 * @uses        /core_modules/login/index.class.php
 * @uses        /core_modules/media/index.class.php';
 * @uses        /core_modules/nettools/index.class.php
 * @uses        /core_modules/news/index.class.php
 * @uses        /core_modules/news/lib/headlines.class.php
 * @uses        /core_modules/news/lib/teasers.class.php
 * @uses        /core_modules/search/index.class.php
 * @uses        /core_modules/sitemap/index.class.php
 * @uses        /modules/block/index.class.php
 * @uses        /modules/calendar/headlines.class.php
 * @uses        /modules/calendar/HomeCalendar.class.php
 * @uses        /modules/calendar/index.class.php
 * @uses        /modules/community/index.class.php
 * @uses        /modules/directory/homeContent.class.php
 * @uses        /modules/directory/index.class.php
 * @uses        /modules/docsys/index.class.php
 * @uses        /modules/download/index.class.php
 * @uses        /modules/egov/index.class.php
 * @uses        /modules/feed/index.class.php
 * @uses        /modules/feed/newsML.class.php
 * @uses        /modules/forum/homeContent.class.php
 * @uses        /modules/forum/index.class.php
 * @uses        /modules/gallery/homeContent.class.php
 * @uses        /modules/gallery/index.class.php
 * @uses        /modules/guestbook/index.class.php
 * @uses        /modules/livecam/index.class.php
 * @uses        /modules/market/index.class.php
 * @uses        /modules/memberdir/index.class.php
 * @uses        /modules/newsletter/index.class.php
 * @uses        /modules/podcast/index.class.php
 * @uses        /modules/recommend/index.class.php
 * @uses        /modules/reservation/index.class.php
 * @uses        /modules/shop/index.class.php
 * @uses        /modules/voting/index.class.php
 * @uses        /modules/immo/index.class.php
 * @uses        /modules/blog/homeContent.class.php
 * @uses        /modules/blog/index.class.php
 * @uses        /lib/FRAMEWORK/SocialNetworks.class.php
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
include_once(dirname(__FILE__).'/lib/DBG.php');
DBG::deactivate();

//iconv_set_encoding('output_encoding', 'utf-8');
//iconv_set_encoding('input_encoding', 'utf-8');
//iconv_set_encoding('internal_encoding', 'utf-8');

$starttime = explode(' ', microtime());

/**
 * Path, database, FTP configuration settings
 *
 * Initialises global settings array and constants.
 */
include_once(dirname(__FILE__).'/config/configuration.php');
/**
 * User configuration settings
 *
 * This file is re-created by the CMS itself. It initializes the
 * {@link $_CONFIG[]} global array.
 */
$incSettingsStatus = include_once(dirname(__FILE__).'/config/settings.php');
/**
 * Version information
 *
 * Adds version information to the {@link $_CONFIG[]} global array.
 */
$incVersionStatus = include_once(dirname(__FILE__).'/config/version.php');

//-------------------------------------------------------
// Check if system is installed
//-------------------------------------------------------
if (!defined('CONTEXX_INSTALLED') || !CONTEXX_INSTALLED) {
    header('Location: installer/index.php');
    die(1);
} elseif ($incSettingsStatus === false || $incVersionStatus === false) {
    die('System halted: Unable to load basic configuration!');
}

//-------------------------------------------------------
// Check if system is running
//-------------------------------------------------------
if ($_CONFIG['systemStatus'] != 'on') {
    header('location: offline.html');
    die(1);
}

/**
 * Include all the required files.
 */
require_once dirname(__FILE__).'/core/API.php';
require_once dirname(__FILE__).'/lib/CSRF.php';
// Temporary fix until all GET operation requests will be replaced by POSTs
CSRF::setFrontendMode();

//-------------------------------------------------------
// Initialize database object
//-------------------------------------------------------
$errorMsg = '';
/**
 * Database object
 * @global ADONewConnection $objDatabase
 */
$objDatabase = getDatabaseObject($errorMsg);

if ($objDatabase === false) {
    die(
        'Database error.'.
        ($errorMsg != '' ? "<br />Message: $errorMsg" : '')
    );
}

if (DBG::getMode() & DBG_ADODB_TRACE) {
    DBG::enable_adodb_debug(true);
} elseif (DBG::getMode() & DBG_ADODB || DBG::getMode() & DBG_ADODB_ERROR) {
    DBG::enable_adodb_debug();
} else {
    DBG::disable_adodb_debug();
}

//-------------------------------------------------------
// Caching-System
//-------------------------------------------------------
/**
 * Include the cache module.  The cache is initialized right afterwards.
 */
require_once ASCMS_CORE_MODULE_PATH.'/cache/index.class.php';
$objCache = new Cache();
$objCache->startCache();

//-------------------------------------------------------
// Custom Yellowpay handling
//-------------------------------------------------------
// Yellowpay:  Restore the originating module (shop or egov),
// from which the payment was initiated.
// Also fix the cmd parameter, make sure it points to "success" for the shop,
// and to "" (the default page) for the egov module.
if (   isset($_GET['handler'])
    && isset($_GET['result'])) {
    // "source" must be set by a POST request by Yellowpay!
    if (!empty($_POST['source'])) {
        $_GET['section'] = $_POST['source'];
        $_POST['section'] = $_POST['source'];
        $_REQUEST['section'] = $_POST['source'];
        if ($_REQUEST['section'] == 'shop') {
            $_GET['cmd'] = 'success';
            $_POST['cmd'] = 'success';
            $_REQUEST['cmd'] = 'success';
        } elseif ($_REQUEST['section'] == 'egov') {
            $_GET['cmd'] = '';
            $_POST['cmd'] = '';
            $_REQUEST['cmd'] = '';
        }
    }
}


$section = isset($_REQUEST['section']) ? contrexx_addslashes($_REQUEST['section']) : '';

// To clone any module, use an optional integer cmd suffix.
// E.g.: "shop2", "gallery5", etc.
// Mind that you *MUST* copy all necessary database tables, and fix any
// references to your module (section and cmd parameters, database tables)
// using the MODULE_INDEX constant in the right place both in your code
// *AND* templates!
// See the Shop module for an example.
$arrMatch = array();
$plainSection = $section;
if (preg_match('/^(\D+)(\d+)$/', $section, $arrMatch)) {
    // The plain section/module name, used below
    $plainSection = $arrMatch[1];
}
// The module index.
// An empty or 1 (one) index represents the same (default) module,
// values 2 (two) and larger represent distinct instances.
$moduleIndex = (empty($arrMatch[2]) || $arrMatch[2] == 1 ? '' : $arrMatch[2]);
define('MODULE_INDEX', $moduleIndex);

//-------------------------------------------------------
// Load settings and configuration
//-------------------------------------------------------

$objInit = new InitCMS();

/**
 * Frontend language ID
 * @global integer $_LANGID
 * @todo    Globally replace this with either the FRONTEND_LANG_ID, or LANG_ID constant
 */
$_LANGID = $objInit->getFrontendLangId();
// Post-2.1
define('FRONTEND_LANG_ID', $_LANGID);
define('LANG_ID', $_LANGID);
/**
 * Core language data
 * @global array $_CORELANG
 */
$_CORELANG = $objInit->loadLanguageData('core');
/**
 * Module specific data
 * @global array $_ARRAYLANG
 */
$_ARRAYLANG = $objInit->loadLanguageData($plainSection);

//-------------------------------------------------------
// Webapp Intrusion Detection System
//-------------------------------------------------------

$objSecurity = new Security;
$_GET = $objSecurity->detectIntrusion($_GET);
$_POST = $objSecurity->detectIntrusion($_POST);
$_COOKIE = $objSecurity->detectIntrusion($_COOKIE);
$_REQUEST = $objSecurity->detectIntrusion($_REQUEST);


//-------------------------------------------------------
// Check Referer -> Redirect
//-------------------------------------------------------
require_once ASCMS_CORE_PATH.'/redirect.class.php';
//$objRedirect = new redirect();

//-------------------------------------------------------
// initialize objects
//-------------------------------------------------------
/**
 * Template object
 * @global HTML_Template_Sigma $objTemplate
 */
$objTemplate = new HTML_Template_Sigma(ASCMS_THEMES_PATH);
$objTemplate->setErrorHandling(PEAR_ERROR_DIE);

$command = isset($_REQUEST['cmd']) ? contrexx_addslashes($_REQUEST['cmd']) : '';
$page    = isset($_REQUEST['page']) ? intval($_GET['page']) : 0;
$history = isset($_REQUEST['history']) ? intval($_GET['history']) : 0;

if (!isset($_REQUEST['standalone']) || $_REQUEST['standalone'] == 'false') {
    $pageId  = $objInit->getPageID($page, $section, $command, $history);
}

$is_home = $objInit->is_home;

$objCounter = new statsLibrary();
$objCounter->checkForSpider();
$themesPages = $objInit->getTemplates();

require_once ASCMS_DOCUMENT_ROOT.'/lib/FRAMEWORK/Javascript.class.php';

//-------------------------------------------------------
// Frontend Editing: Collect parameters
//-------------------------------------------------------
$frontEditing           = isset($_REQUEST['frontEditing']) ? intval($_GET['frontEditing']) : 0;
$frontEditingContent    = isset($_REQUEST['previewContent']) ? preg_replace('/\[\[([A-Z0-9_-]+)\]\]/', '{\\1}' , html_entity_decode(stripslashes($_GET['previewContent']), ENT_QUOTES, CONTREXX_CHARSET)) : '';

if($frontEditing) {
    $themesPages['index']   = '{CONTENT_FILE}';
    $themesPages['content'] = '{CONTENT_TEXT}';
    $themesPages['home']    = '{CONTENT_TEXT}';
}

if (!isset($_REQUEST['standalone']) || $_REQUEST['standalone'] == 'false') {
    $query = "
          SELECT `c`.`content`, `c`.`title`, `c`.`redirect`,
                 `c`.`metatitle`, `c`.`metadesc`,
                 `c`.`metakeys`, `c`.`metarobots`,
                 `c`.`css_name`,
                 `n`.`catname`, `n`.`protected`,
                 `n`.`frontend_access_id`, `n`.`changelog`".
                 (!empty($history) ? ', `n`.`catid`' : '')."
            FROM `".DBPREFIX.(empty($history) ? 'content' : 'content_history')."` AS `c`,
                 `".DBPREFIX.(empty($history) ? 'content_navigation' : 'content_navigation_history')."` AS `n`
           WHERE `c`.`id`=".(empty($history) ? $pageId : $history)."
             AND `c`.`id`=".(!empty($history) ? '`n`.`id`' : "`n`.`catid`
             AND (`n`.`startdate`<=CURDATE() OR `n`.`startdate`='0000-00-00')
             AND (`n`.`enddate`>=CURDATE() OR `n`.`enddate`='0000-00-00')
             AND `n`.`activestatus`='1'
             AND `n`.`is_validated`='1'");
    $objResult = $objDatabase->SelectLimit($query, 1);
    if ($objResult === false || $objResult->EOF) {
        if ($plainSection == 'error') {
            // If the error module is not installed, show this
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        }
        CSRF::header('Location: index.php?section=error&id=404');
        exit;
    }

    // Frontend Editing: content has to be replaced with preview code if needed.
    $page_content   = ($frontEditing) ? ( ($frontEditingContent != '') ? $frontEditingContent : $objResult->fields['content']) : '<div id="fe_PreviewContent">'.$objResult->fields['content'].'</div>';
    $page_title     = htmlentities($objResult->fields['title'], ENT_QUOTES, CONTREXX_CHARSET);
    $page_catname   = $objResult->fields['catname'];
    $page_metatitle = htmlentities($objResult->fields['metatitle'], ENT_QUOTES, CONTREXX_CHARSET);
    $page_keywords  = htmlentities($objResult->fields['metakeys'], ENT_QUOTES, CONTREXX_CHARSET);
    $page_robots    = $objResult->fields['metarobots'];
    $pageCssName    = $objResult->fields['css_name'];
    $page_desc      = htmlentities($objResult->fields['metadesc'], ENT_QUOTES, CONTREXX_CHARSET);
    $page_redirect  = $objResult->fields['redirect'];
    $page_protected = $objResult->fields['protected'];
    $page_access_id = $objResult->fields['frontend_access_id'];
    $page_template  = $themesPages['content'];
    $page_modified  = $objResult->fields['changelog'];

    if ($history) {
        $objPageProtection = $objDatabase->SelectLimit('SELECT backend_access_id FROM '.DBPREFIX.'content_navigation WHERE catid='.$objResult->fields['catid'].' AND backend_access_id!=0', 1);
        if ($objPageProtection !== false) {
            if ($objPageProtection->RecordCount() == 1) {
                $page_protected = 1;
                $page_access_id = $objPageProtection->fields['backend_access_id'];
            }
        } else {
            $page_protected = 1;
        }
    }
}


$sessionObj = null;
$shopObj    = null;
//-------------------------------------------------------
// authentification for protected pages
//-------------------------------------------------------
if (($page_protected || $history || !empty($_COOKIE['PHPSESSID'])) && (!isset($_REQUEST['section']) || $_REQUEST['section'] != 'login')) {
    $sessionObj = new cmsSession();
    $sessionObj->cmsSessionStatusUpdate('frontend');

    $objFWUser = FWUser::getFWUserObject();
    if ($objFWUser->objUser->login()) {
        if ($page_protected) {
            if (!Permission::checkAccess($page_access_id, 'dynamic', true)) {
                $link=base64_encode(CONTREXX_SCRIPT_PATH.'?'.$_SERVER['QUERY_STRING']);
                CSRF::header ('Location: '.CONTREXX_SCRIPT_PATH.'?section=login&cmd=noaccess&redirect='.$link);
                exit;
            }
        }
        if ($history && !Permission::checkAccess(78, 'static', true)) {
            $link=base64_encode(CONTREXX_SCRIPT_PATH.'?'.$_SERVER['QUERY_STRING']);
            CSRF::header ('Location: '.CONTREXX_SCRIPT_PATH.'?section=login&cmd=noaccess&redirect='.$link);
            exit;
        }
    } elseif (!empty($_COOKIE['PHPSESSID']) && !$page_protected) {
        unset($_COOKIE['PHPSESSID']);
    } else {
        $link=base64_encode(CONTREXX_SCRIPT_PATH.'?'.$_SERVER['QUERY_STRING']);
        CSRF::header ('Location: '.CONTREXX_SCRIPT_PATH.'?section=login&redirect='.$link);
        exit;
    }
}

if (!empty($page_redirect)){
    CSRF::header('Location: ' . $page_redirect);
    exit;
}

// Initialize the navigation
$objNavbar  = new Navigation($pageId);

//-------------------------------------------------------
// Start page or default page for no section
//-------------------------------------------------------
if ($is_home){
    $page_template  = $themesPages['home'];
}

//-------------------------------------------------------
// make the replacements for the data module
//-------------------------------------------------------
$dataBlocksPath = 'modules/data/dataBlocks.class.php';
if (file_exists($dataBlocksPath)) {
    $lang = $objInit->loadLanguageData('data');
    require_once('modules/data/dataBlocks.class.php');
    $dataBlocks = new dataBlocks($lang);
    $page_content = $dataBlocks->replace($page_content);
    $themesPages = $dataBlocks->replace($themesPages);
    $page_template = $dataBlocks->replace($page_template);
}

$arrMatches = array();
//-------------------------------------------------------
// Set news teasers
//-------------------------------------------------------
if ($_CONFIG['newsTeasersStatus'] == '1') {
    // set news teasers in the content
    if (preg_match_all('/{TEASERS_([0-9A-Z_-]+)}/ms', $page_content, $arrMatches)) {
        $modulespath = 'core_modules/news/lib/teasers.class.php';
        if (file_exists($modulespath)) {
            /** @ignore */
            include_once($modulespath);
            $objTeasers = new Teasers();
            $objTeasers->setTeaserFrames($arrMatches[1], $page_content);
        }
    }

    // set news teasers in the page design
    if (preg_match_all('/{TEASERS_([0-9A-Z_-]+)}/ms', $page_template, $arrMatches)) {
        $modulespath = 'core_modules/news/lib/teasers.class.php';
        if (file_exists($modulespath)) {
            /** @ignore */
            include_once($modulespath);
            $objTeasers = new Teasers();
            $objTeasers->setTeaserFrames($arrMatches[1], $page_template);
        }
    }

    // set news teasers in the website design
    if (preg_match_all('/{TEASERS_([0-9A-Z_-]+)}/ms', $themesPages['index'], $arrMatches)) {
        $modulespath = 'core_modules/news/lib/teasers.class.php';
        if (file_exists($modulespath)) {
            /** @ignore */
            include_once($modulespath);
            $objTeasers = new Teasers();
            $objTeasers->setTeaserFrames($arrMatches[1], $themesPages['index']);
        }
    }
}

//-------------------------------------------------------
// Set download groups
//-------------------------------------------------------
if (preg_match_all('/{DOWNLOADS_GROUP_([0-9]+)}/ms', $page_content, $arrMatches)) {
    $modulespath = "modules/downloads/lib/downloadsLib.class.php";
    if (file_exists($modulespath)) {
        /**
         * @ignore
         */
        include_once($modulespath);
        $objDownloadLib = new DownloadsLibrary();
        $objDownloadLib->setGroups($arrMatches[1], $page_content);
    }
}

//-------------------------------------------------------
// Set NewsML messages
//-------------------------------------------------------
if ($_CONFIG['feedNewsMLStatus'] == '1') {
    if (preg_match_all('/{NEWSML_([0-9A-Z_-]+)}/ms', $page_content, $arrMatches)) {
        $modulespath = 'modules/feed/newsML.class.php';
        if (file_exists($modulespath)) {
            /** @ignore */
            require_once $modulespath;
            $objNewsML = new NewsML();
            $objNewsML->setNews($arrMatches[1], $page_content);
        }
    }
    if (preg_match_all('/{NEWSML_([0-9A-Z_-]+)}/ms', $page_template, $arrMatches)) {
        $modulespath = 'modules/feed/newsML.class.php';
        if (file_exists($modulespath)) {
            /** @ignore */
            require_once $modulespath;
            $objNewsML = new NewsML();
            $objNewsML->setNews($arrMatches[1], $page_template);
        }
    }
    if (preg_match_all('/{NEWSML_([0-9A-Z_-]+)}/ms', $themesPages['index'], $arrMatches)) {
        $modulespath = 'modules/feed/newsML.class.php';
        if (file_exists($modulespath)) {
            /** @ignore */
            require_once $modulespath;
            $objNewsML = new NewsML();
            $objNewsML->setNews($arrMatches[1], $themesPages['index']);
        }
    }
}


//-------------------------------------------------------
// Set popups
//-------------------------------------------------------
$modulespath = 'modules/popup/index.class.php';
if (file_exists($modulespath)) {
    /** @ignore */
    if (preg_match_all('/{POPUP_JS_FUNCTION}/ms', $themesPages['index'], $arrMatches)) {
        require_once $modulespath;
        $objPopup = new popup();
        if (preg_match_all('/{POPUP}/ms', $themesPages['index'], $arrMatches)) {
            $objPopup->setPopup($themesPages['index'], $pageId);
        }
        $objPopup->_setJS($themesPages['index']);
    }
}

//-------------------------------------------------------
// Set Blocks
//-------------------------------------------------------
if ($_CONFIG['blockStatus'] == '1') {
    $modulespath = 'modules/block/index.class.php';
    if (file_exists($modulespath)) {
        /** @ignore */
        require_once $modulespath;
        $objBlock = new block();
        if (preg_match_all('/{'.$objBlock->blockNamePrefix.'([0-9]+)}/ms', $page_content, $arrMatches)) {
            $objBlock->setBlock($arrMatches[1], $page_content);
        }
        if (preg_match_all('/{'.$objBlock->blockNamePrefix.'([0-9]+)}/ms', $page_template, $arrMatches)) {
            $objBlock->setBlock($arrMatches[1], $page_template);
        }
        if (preg_match_all('/{'.$objBlock->blockNamePrefix.'([0-9]+)}/ms', $themesPages['index'], $arrMatches)) {
            $objBlock->setBlock($arrMatches[1], $themesPages['index']);
        }
        if (preg_match_all('/{'.$objBlock->blockNamePrefix.'([0-9]+)}/ms', $themesPages['sidebar'], $arrMatches)) {
            $objBlock->setBlock($arrMatches[1], $themesPages['sidebar']);
        }

        if (preg_match_all('/{'.$objBlock->blockNamePrefix.'GLOBAL}/ms', $page_content, $arrMatches)) {
            $objBlock->setBlockGlobal($page_content, $pageId);
        }
        if (preg_match_all('/{'.$objBlock->blockNamePrefix.'GLOBAL}/ms', $page_template, $arrMatches)) {
            $objBlock->setBlockGlobal($page_template, $pageId);
        }
        if (preg_match_all('/{'.$objBlock->blockNamePrefix.'GLOBAL}/ms', $themesPages['index'], $arrMatches)) {
            $objBlock->setBlockGlobal($themesPages['index'], $pageId);
        }
        if (preg_match_all('/{'.$objBlock->blockNamePrefix.'GLOBAL}/ms', $themesPages['sidebar'], $arrMatches)) {
            $objBlock->setBlockGlobal($themesPages['sidebar'], $pageId);
        }

        if ($_CONFIG['blockRandom'] == '1') {
            //randomizer block 1
            if (preg_match_all('/{'.$objBlock->blockNamePrefix.'RANDOMIZER}/ms', $page_content, $arrMatches)) {
                $objBlock->setBlockRandom($page_content, 1);
            }
            if (preg_match_all('/{'.$objBlock->blockNamePrefix.'RANDOMIZER}/ms', $page_template, $arrMatches)) {
                $objBlock->setBlockRandom($page_template, 1);
            }
            if (preg_match_all('/{'.$objBlock->blockNamePrefix.'RANDOMIZER}/ms', $themesPages['index'], $arrMatches)) {
                $objBlock->setBlockRandom($themesPages['index'], 1);
            }
            if (preg_match_all('/{'.$objBlock->blockNamePrefix.'RANDOMIZER}/ms', $themesPages['sidebar'], $arrMatches)) {
                $objBlock->setBlockRandom($themesPages['sidebar'], 1);
            }

            //randomizer block 2
            if (preg_match_all('/{'.$objBlock->blockNamePrefix.'RANDOMIZER_2}/ms', $page_content, $arrMatches)) {
                $objBlock->setBlockRandom($page_content, 2);
            }
            if (preg_match_all('/{'.$objBlock->blockNamePrefix.'RANDOMIZER_2}/ms', $page_template, $arrMatches)) {
                $objBlock->setBlockRandom($page_template, 2);
            }
            if (preg_match_all('/{'.$objBlock->blockNamePrefix.'RANDOMIZER_2}/ms', $themesPages['index'], $arrMatches)) {
                $objBlock->setBlockRandom($themesPages['index'], 2);
            }
            if (preg_match_all('/{'.$objBlock->blockNamePrefix.'RANDOMIZER_2}/ms', $themesPages['sidebar'], $arrMatches)) {
                $objBlock->setBlockRandom($themesPages['sidebar'], 2);
            }

            //randomizer block 3
            if (preg_match_all('/{'.$objBlock->blockNamePrefix.'RANDOMIZER_3}/ms', $page_content, $arrMatches)) {
                $objBlock->setBlockRandom($page_content, 3);
            }
            if (preg_match_all('/{'.$objBlock->blockNamePrefix.'RANDOMIZER_3}/ms', $page_template, $arrMatches)) {
                $objBlock->setBlockRandom($page_template, 3);
            }
            if (preg_match_all('/{'.$objBlock->blockNamePrefix.'RANDOMIZER_3}/ms', $themesPages['index'], $arrMatches)) {
                $objBlock->setBlockRandom($themesPages['index'], 3);
            }
            if (preg_match_all('/{'.$objBlock->blockNamePrefix.'RANDOMIZER_3}/ms', $themesPages['sidebar'], $arrMatches)) {
                $objBlock->setBlockRandom($themesPages['sidebar'], 3);
            }
        }
    }
}


//-------------------------------------------------------
// Get Headlines
//-------------------------------------------------------
$modulespath = 'core_modules/news/lib/headlines.class.php';
/** @ignore */
if (file_exists($modulespath)) include_once($modulespath);
$newsHeadlinesObj = new newsHeadlines($themesPages['headlines']);
$page_content = str_replace('{HEADLINES_FILE}', $newsHeadlinesObj->getHomeHeadlines(), $page_content);
$themesPages['index'] = str_replace('{HEADLINES_FILE}', $newsHeadlinesObj->getHomeHeadlines(), $themesPages['index']);
$themesPages['sidebar'] = str_replace('{HEADLINES_FILE}', $newsHeadlinesObj->getHomeHeadlines(), $themesPages['sidebar']);
$page_template = str_replace('{HEADLINES_FILE}', $newsHeadlinesObj->getHomeHeadlines(), $page_template);


//-------------------------------------------------------
// Get Calendar Events
//-------------------------------------------------------
$modulespath = 'modules/calendar/headlines.class.php';
if (MODULE_INDEX < 2 && file_exists($modulespath)) {
    /** @ignore */
    include_once($modulespath);
    $calHeadlinesObj = new calHeadlines($themesPages['calendar_headlines']);
    $page_content = str_replace('{EVENTS_FILE}', $calHeadlinesObj->getHeadlines(), $page_content);
    $themesPages['index'] = str_replace('{EVENTS_FILE}', $calHeadlinesObj->getHeadlines(), $themesPages['index']);
    $themesPages['sidebar'] = str_replace('{EVENTS_FILE}', $calHeadlinesObj->getHeadlines(), $themesPages['sidebar']);
    $themesPages['home'] = str_replace('{EVENTS_FILE}', $calHeadlinesObj->getHeadlines(), $themesPages['home']);
    $page_template = str_replace('{EVENTS_FILE}', $calHeadlinesObj->getHeadlines(), $page_template);
}


//-------------------------------------------------------
// Get immo headline
//-------------------------------------------------------
$modulespath = 'modules/immo/headlines.class.php';
if (file_exists($modulespath)) {
    /** @ignore */
    include_once($modulespath);
    $immoHeadlines = new immoHeadlines($themesPages['immo']);

    $page_content = str_replace('{IMMO_FILE}', $immoHeadlines->getHeadlines(), $page_content);
    $themesPages['index'] = str_replace('{IMMO_FILE}', $immoHeadlines->getHeadlines(), $themesPages['index']);
    $themesPages['home'] = str_replace('{IMMO_FILE}', $immoHeadlines->getHeadlines(), $themesPages['home']);
    $page_template = str_replace('{IMMO_FILE}', $immoHeadlines->getHeadlines(), $page_template);
}


//-------------------------------------------------------
// get Newsletter
//-------------------------------------------------------
$modulespath = 'modules/newsletter/index.class.php';
if (file_exists($modulespath)) {
    /** @ignore */
    require_once($modulespath);
    $_ARRAYLANG = array_merge($_ARRAYLANG, $objInit->loadLanguageData('newsletter'));
    $newsletter = new newsletter('');
    if (preg_match_all('/{NEWSLETTER_BLOCK}/ms', $page_content, $arrMatches)) {
        $newsletter->setBlock($page_content);
    }
    if (preg_match_all('/{NEWSLETTER_BLOCK}/ms', $page_template, $arrMatches)) {
        $newsletter->setBlock($page_template);
    }
    if (preg_match_all('/{NEWSLETTER_BLOCK}/ms', $themesPages['index'], $arrMatches)) {
        $newsletter->setBlock($themesPages['index']);
    }
}


//-------------------------------------------------------
// get knowledge content
//-------------------------------------------------------
if (MODULE_INDEX < 2 && !empty($_CONFIG['useKnowledgePlaceholders'])) {
    $modulespath = 'modules/knowledge/interface.class.php';
    if (file_exists($modulespath)) {
        require_once($modulespath);
        $knowledgeInterface = new KnowledgeInterface();
        if (preg_match('/{KNOWLEDGE_[A-Za-z0-9_]+}/i', $page_content)) {
            $knowledgeInterface->parse($page_content);
        }
        if (preg_match('/{KNOWLEDGE_[A-Za-z0-9_]+}/i', $page_template)) {
            $knowledgeInterface->parse($page_template);
        }
        if (preg_match('/{KNOWLEDGE_[A-Za-z0-9_]+}/i', $themesPages['index'])) {
            $knowledgeInterface->parse($themesPages['index']);
        }
    }
}


//-------------------------------------------------------
// get Directory Homecontent
//-------------------------------------------------------
if ($_CONFIG['directoryHomeContent'] == '1') {
    $modulespath = 'modules/directory/homeContent.class.php';
    if (file_exists($modulespath)) {
        /** @ignore */
        require_once($modulespath);
        $dirc = $themesPages['directory_content'];
        if (preg_match_all('/{DIRECTORY_FILE}/ms', $page_content, $arrMatches)) {
            $page_content = str_replace('{DIRECTORY_FILE}', dirHomeContent::getObj($dirc)->getContent(), $page_content);
        }
        if (preg_match_all('/{DIRECTORY_FILE}/ms', $page_template, $arrMatches)) {
            $page_template = str_replace('{DIRECTORY_FILE}', dirHomeContent::getObj($dirc)->getContent(), $page_template);
        }
        if (preg_match_all('/{DIRECTORY_FILE}/ms', $themesPages['index'], $arrMatches)) {
            $themesPages['index'] = str_replace('{DIRECTORY_FILE}', dirHomeContent::getObj($dirc)->getContent(), $themesPages['index']);
        }
    }
}


//-------------------------------------------------------
// get + replace forum latest entries content
//-------------------------------------------------------
if ($_CONFIG['forumHomeContent'] == '1') {
    $modulespath = 'modules/forum/homeContent.class.php';
    if (file_exists($modulespath)) {
        /** @ignore */
        require_once($modulespath);
        $forumHomeContentInPageContent = false;
        $forumHomeContentInPageTemplate = false;
        $forumHomeContentInThemesPage = false;
        if (strpos($page_content, '{FORUM_FILE}') !== false) {
            $forumHomeContentInPageContent = true;
        }
        if (strpos($page_template, '{FORUM_FILE}') !== false) {
            $forumHomeContentInPageTemplate = true;
        }
        if (strpos($themesPages['index'], '{FORUM_FILE}') !== false) {
            $forumHomeContentInThemesPage = true;
        }
        if ($forumHomeContentInPageContent || $forumHomeContentInPageTemplate || $forumHomeContentInThemesPage) {
            $_ARRAYLANG = array_merge($_ARRAYLANG, $objInit->loadLanguageData('forum'));
            $objForum = new ForumHomeContent($themesPages['forum_content']);
        }
        if ($forumHomeContentInPageContent) {
            $page_content = str_replace('{FORUM_FILE}', $objForum->getContent(), $page_content);
        }
        if ($forumHomeContentInPageTemplate) {
            $page_template = str_replace('{FORUM_FILE}', $objForum->getContent(), $page_template);
        }
        if ($forumHomeContentInThemesPage) {
           $themesPages['index'] = str_replace('{FORUM_FILE}', $objForum->getContent(), $themesPages['index']);
        }
    }
}


//------------------------------
// get + replace forum tagcloud
//------------------------------
if (!empty($_CONFIG['forumTagContent'])) {
    $modulespath = 'modules/forum/homeContent.class.php';
    if (file_exists($modulespath)) {
        require_once($modulespath);
        $objForumHome = new ForumHomeContent();
        //Forum-TagCloud
        $forumHomeTagCloudInContent     = $objForumHome->searchKeywordInContent('FORUM_TAG_CLOUD', $page_content);
        $forumHomeTagCloudInTemplate = $objForumHome->searchKeywordInContent('FORUM_TAG_CLOUD', $page_template);
        $forumHomeTagCloudInTheme    = $objForumHome->searchKeywordInContent('FORUM_TAG_CLOUD', $themesPages['index']);
        $forumHomeTagCloudInSidebar    = $objForumHome->searchKeywordInContent('FORUM_TAG_CLOUD', $themesPages['sidebar']);
        if ($forumHomeTagCloudInContent || $forumHomeTagCloudInTemplate || $forumHomeTagCloudInTheme || $forumHomeTagCloudInSidebar) {
            $strTagCloudSource = $objForumHome->getHomeTagCloud();
            $page_content            = $objForumHome->fillVariableIfActivated('FORUM_TAG_CLOUD', $strTagCloudSource, $page_content, $forumHomeTagCloudInContent);
            $page_template            = $objForumHome->fillVariableIfActivated('FORUM_TAG_CLOUD', $strTagCloudSource, $page_template, $forumHomeTagCloudInTemplate);
            $themesPages['index']     = $objForumHome->fillVariableIfActivated('FORUM_TAG_CLOUD', $strTagCloudSource, $themesPages['index'], $forumHomeTagCloudInTheme);
            $themesPages['sidebar'] = $objForumHome->fillVariableIfActivated('FORUM_TAG_CLOUD', $strTagCloudSource, $themesPages['sidebar'], $forumHomeTagCloudInSidebar);
        }
    }
}




//-------------------------------------------------------
// Get Gallery-Images (Latest, Random)
//-------------------------------------------------------
$modulespath = 'modules/gallery/homeContent.class.php';
if (file_exists($modulespath)) {
    /** @ignore */
    require_once($modulespath);
    $objGalleryHome = new GalleryHomeContent();
    if ($objGalleryHome->checkRandom()) {
        if (preg_match_all('/{GALLERY_RANDOM}/ms', $page_content, $arrMatches)) {
            $page_content = str_replace('{GALLERY_RANDOM}', $objGalleryHome->getRandomImage(), $page_content);
        }
        if (preg_match_all('/{GALLERY_RANDOM}/ms', $page_template, $arrMatches))  {
            $page_template = str_replace('{GALLERY_RANDOM}', $objGalleryHome->getRandomImage(), $page_template);
        }
        if (preg_match_all('/{GALLERY_RANDOM}/ms', $themesPages['index'], $arrMatches)) {
            $themesPages['index'] = str_replace('{GALLERY_RANDOM}', $objGalleryHome->getRandomImage(), $themesPages['index']);
        }
        if (preg_match_all('/{GALLERY_RANDOM}/ms', $themesPages['sidebar'], $arrMatches)) {
            $themesPages['sidebar'] = str_replace('{GALLERY_RANDOM}', $objGalleryHome->getRandomImage(), $themesPages['sidebar']);
        }
    }
    if ($objGalleryHome->checkLatest()) {
        if (preg_match_all('/{GALLERY_LATEST}/ms', $page_content, $arrMatches)) {
            $page_content = str_replace('{GALLERY_LATEST}', $objGalleryHome->getLastImage(), $page_content);
        }
        if (preg_match_all('/{GALLERY_LATEST}/ms', $page_template, $arrMatches)) {
            $page_template = str_replace('{GALLERY_LATEST}', $objGalleryHome->getLastImage(), $page_template);
        }
        if (preg_match_all('/{GALLERY_LATEST}/ms', $themesPages['index'], $arrMatches)) {
            $themesPages['index'] = str_replace('{GALLERY_LATEST}', $objGalleryHome->getLastImage(), $themesPages['index']);
        }
        if (preg_match_all('/{GALLERY_LATEST}/ms', $themesPages['sidebar'], $arrMatches)) {
            $themesPages['sidebar'] = str_replace('{GALLERY_LATEST}', $objGalleryHome->getLastImage(), $themesPages['sidebar']);
        }
    }
}


//-------------------------------------------------------
// get latest podcast entries
//-------------------------------------------------------
$podcastFirstBlock = false;
if (!empty($_CONFIG['podcastHomeContent'])) {
    $modulespath = 'modules/podcast/homeContent.class.php';
    if (file_exists($modulespath)) {
        /** @ignore */
        require_once($modulespath);
        $podcastHomeContentInPageContent = false;
        $podcastHomeContentInPageTemplate = false;
        $podcastHomeContentInThemesPage = false;
        if (strpos($page_content, '{PODCAST_FILE}') !== false) {
            $podcastHomeContentInPageContent = true;
        }
        if (strpos($page_template, '{PODCAST_FILE}') !== false) {
            $podcastHomeContentInPageTemplate = true;
        }
        if (strpos($themesPages['index'], '{PODCAST_FILE}') !== false) {
            $podcastHomeContentInThemesPage = true;
        }
        if ($podcastHomeContentInPageContent || $podcastHomeContentInPageTemplate || $podcastHomeContentInThemesPage) {
            $_ARRAYLANG = array_merge($_ARRAYLANG, $objInit->loadLanguageData('podcast'));
            $objPodcast = new podcastHomeContent($themesPages['podcast_content']);
        }
        if ($podcastHomeContentInPageContent) {
            $page_content = str_replace('{PODCAST_FILE}', $objPodcast->getContent(), $page_content);
        }
        if ($podcastHomeContentInPageTemplate) {
            $page_template = str_replace('{PODCAST_FILE}', $objPodcast->getContent(), $page_template);
        }
        if ($podcastHomeContentInThemesPage) {
            $podcastFirstBlock = false;
            if(strpos($_SERVER['REQUEST_URI'], 'section=podcast')){
                $podcastBlockPos = strpos($themesPages['index'], '{PODCAST_FILE}');
                $contentPos      = strpos($themesPages['index'], '{CONTENT_FILE}');
                $podcastFirstBlock   = $podcastBlockPos < $contentPos ? true : false;
            }
            $themesPages['index'] = str_replace('{PODCAST_FILE}', $objPodcast->getContent($podcastFirstBlock), $themesPages['index']);
        }
    }
}


//-------------------------------------------------------
// Load JavaScript Cart
//-------------------------------------------------------
if (MODULE_INDEX < 2 && $_CONFIGURATION['custom']['shopJsCart']
    && (   $_CONFIGURATION['custom']['shopnavbar']
        ||    isset($_REQUEST['section'])
           && $_REQUEST['section'] == 'shop')) {
    $modulespath = 'modules/shop/index.class.php';
    if (file_exists($modulespath)) {
        /** @ignore */
        require_once($modulespath);
        $_ARRAYLANG = array_merge($_ARRAYLANG, $objInit->loadLanguageData('shop'));
        if (preg_match_all('@<!--\s+BEGIN\s+(shopJsCart)\s+-->(.*?)<!--\s+END\s+\1\s+-->@s', $themesPages['sidebar'], $arrMatches, PREG_SET_ORDER)) {
            $themesPages['sidebar'] = preg_replace('@(<!--\s+BEGIN\s+(shopJsCart)\s+-->.*?<!--\s+END\s+\2\s+-->)@s', Shop::setJsCart($arrMatches[0][2]), $themesPages['sidebar']);
        }
        if (preg_match_all('@<!--\s+BEGIN\s+(shopJsCart)\s+-->(.*?)<!--\s+END\s+\1\s+-->@s', $themesPages['shopnavbar'], $arrMatches, PREG_SET_ORDER)) {
            $themesPages['shopnavbar'] = preg_replace('@(<!--\s+BEGIN\s+(shopJsCart)\s+-->.*?<!--\s+END\s+\2\s+-->)@s', Shop::setJsCart($arrMatches[0][2]), $themesPages['shopnavbar']);
        }
        if (preg_match_all('@<!--\s+BEGIN\s+(shopJsCart)\s+-->(.*?)<!--\s+END\s+\1\s+-->@s', $themesPages['index'], $arrMatches, PREG_SET_ORDER)) {
            $themesPages['index'] = preg_replace('@(<!--\s+BEGIN\s+(shopJsCart)\s+-->.*?<!--\s+END\s+\2\s+-->)@s', Shop::setJsCart($arrMatches[0][2]), $themesPages['index']);
        }
        if (preg_match_all('@<!--\s+BEGIN\s+(shopJsCart)\s+-->(.*?)<!--\s+END\s+\1\s+-->@s', $page_content, $arrMatches, PREG_SET_ORDER)) {
            $page_content = preg_replace('@(<!--\s+BEGIN\s+(shopJsCart)\s+-->.*?<!--\s+END\s+\2\s+-->)@s', Shop::setJsCart($arrMatches[0][2]), $page_content);
        }
        if (preg_match_all('@<!--\s+BEGIN\s+(shopJsCart)\s+-->(.*?)<!--\s+END\s+\1\s+-->@s', $page_template, $arrMatches, PREG_SET_ORDER)) {
            $page_template = preg_replace('@(<!--\s+BEGIN\s+(shopJsCart)\s+-->.*?<!--\s+END\s+\2\s+-->)@s', Shop::setJsCart($arrMatches[0][2]), $page_template);
        }
    }
}


//-------------------------------------------------------
// get voting
//-------------------------------------------------------
$modulespath = 'modules/voting/index.class.php';
if (file_exists($modulespath)) {
    require_once($modulespath);
    $_ARRAYLANG = array_merge($_ARRAYLANG, $objInit->loadLanguageData('voting'));
//  if ($objTemplate->blockExists('voting_result')) {
//      $objTemplate->_blocks['voting_result'] = setVotingResult($objTemplate->_blocks['voting_result']);
//  }
    if (preg_match_all('@<!--\s+BEGIN\s+(voting_result)\s+-->(.*)<!--\s+END\s+\1\s+-->@sm', $themesPages['sidebar'], $arrMatches, PREG_SET_ORDER)) {
        $themesPages['sidebar'] = preg_replace('@(<!--\s+BEGIN\s+(voting_result)\s+-->.*<!--\s+END\s+\2\s+-->)@sm', setVotingResult($arrMatches[0][2]), $themesPages['sidebar']);
    }
    if (preg_match_all('@<!--\s+BEGIN\s+(voting_result)\s+-->(.*)<!--\s+END\s+\1\s+-->@sm', $themesPages['index'], $arrMatches, PREG_SET_ORDER)) {
        $themesPages['index'] = preg_replace('@(<!--\s+BEGIN\s+(voting_result)\s+-->.*<!--\s+END\s+\2\s+-->)@sm', setVotingResult($arrMatches[0][2]), $themesPages['index']);
    }
    if (preg_match_all('@<!--\s+BEGIN\s+(voting_result)\s+-->(.*)<!--\s+END\s+\1\s+-->@sm', $page_content, $arrMatches, PREG_SET_ORDER)) {
        $page_content = preg_replace('@(<!--\s+BEGIN\s+(voting_result)\s+-->.*<!--\s+END\s+\2\s+-->)@sm', setVotingResult($arrMatches[0][2]), $page_content);
    }
    if (preg_match_all('@<!--\s+BEGIN\s+(voting_result)\s+-->(.*)<!--\s+END\s+\1\s+-->@sm', $page_template, $arrMatches, PREG_SET_ORDER)) {
        $page_template = preg_replace('@(<!--\s+BEGIN\s+(voting_result)\s+-->.*<!--\s+END\s+\2\s+-->)@sm', setVotingResult($arrMatches[0][2]), $page_template);
    }
}


//-------------------------------------------------------
// Get content for the blog-module.
//-------------------------------------------------------
$modulespath = 'modules/blog/homeContent.class.php';
if (file_exists($modulespath)) {
    /** @ignore */
    require_once($modulespath);
    $objBlogHome = new BlogHomeContent($themesPages['blog_content']);
    if ($objBlogHome->blockFunktionIsActivated()) {
        //Blog-File
        $blogHomeContentInContent   = $objBlogHome->searchKeywordInContent('BLOG_FILE', $page_content);
        $blogHomeContentInTemplate  = $objBlogHome->searchKeywordInContent('BLOG_FILE', $page_template);
        $blogHomeContentInTheme     = $objBlogHome->searchKeywordInContent('BLOG_FILE', $themesPages['index']);
        $blogHomeContentInSidebar   = $objBlogHome->searchKeywordInContent('BLOG_FILE', $themesPages['sidebar']);
        if ($blogHomeContentInContent || $blogHomeContentInTemplate || $blogHomeContentInTheme || $blogHomeContentInSidebar) {
            $_ARRAYLANG = array_merge($_ARRAYLANG, $objInit->loadLanguageData('blog'));
            $strContentSource = $objBlogHome->getLatestEntries();
            $page_content           = $objBlogHome->fillVariableIfActivated('BLOG_FILE', $strContentSource, $page_content, $blogHomeContentInContent);
            $page_template          = $objBlogHome->fillVariableIfActivated('BLOG_FILE', $strContentSource, $page_template, $blogHomeContentInTemplate);
            $themesPages['index']   = $objBlogHome->fillVariableIfActivated('BLOG_FILE', $strContentSource, $themesPages['index'], $blogHomeContentInTheme);
            $themesPages['sidebar'] = $objBlogHome->fillVariableIfActivated('BLOG_FILE', $strContentSource, $themesPages['sidebar'], $blogHomeContentInSidebar);
        }
        //Blog-Calendar
        $blogHomeCalendarInContent  = $objBlogHome->searchKeywordInContent('BLOG_CALENDAR', $page_content);
        $blogHomeCalendarInTemplate = $objBlogHome->searchKeywordInContent('BLOG_CALENDAR', $page_template);
        $blogHomeCalendarInTheme    = $objBlogHome->searchKeywordInContent('BLOG_CALENDAR', $themesPages['index']);
        $blogHomeCalendarInSidebar  = $objBlogHome->searchKeywordInContent('BLOG_CALENDAR', $themesPages['sidebar']);
        if ($blogHomeCalendarInContent || $blogHomeCalendarInTemplate || $blogHomeCalendarInTheme || $blogHomeCalendarInSidebar) {
            $strCalendarSource = $objBlogHome->getHomeCalendar();
            $page_content           = $objBlogHome->fillVariableIfActivated('BLOG_CALENDAR', $strCalendarSource, $page_content, $blogHomeCalendarInContent);
            $page_template          = $objBlogHome->fillVariableIfActivated('BLOG_CALENDAR', $strCalendarSource, $page_template, $blogHomeCalendarInTemplate);
            $themesPages['index']   = $objBlogHome->fillVariableIfActivated('BLOG_CALENDAR', $strCalendarSource, $themesPages['index'], $blogHomeCalendarInTheme);
            $themesPages['sidebar'] = $objBlogHome->fillVariableIfActivated('BLOG_CALENDAR', $strCalendarSource, $themesPages['sidebar'], $blogHomeCalendarInSidebar);
        }
        //Blog-TagCloud
        $blogHomeTagCloudInContent  = $objBlogHome->searchKeywordInContent('BLOG_TAG_CLOUD', $page_content);
        $blogHomeTagCloudInTemplate = $objBlogHome->searchKeywordInContent('BLOG_TAG_CLOUD', $page_template);
        $blogHomeTagCloudInTheme    = $objBlogHome->searchKeywordInContent('BLOG_TAG_CLOUD', $themesPages['index']);
        $blogHomeTagCloudInSidebar  = $objBlogHome->searchKeywordInContent('BLOG_TAG_CLOUD', $themesPages['sidebar']);
        if ($blogHomeTagCloudInContent || $blogHomeTagCloudInTemplate || $blogHomeTagCloudInTheme || $blogHomeTagCloudInSidebar) {
            $strTagCloudSource = $objBlogHome->getHomeTagCloud();
            $page_content           = $objBlogHome->fillVariableIfActivated('BLOG_TAG_CLOUD', $strTagCloudSource, $page_content, $blogHomeTagCloudInContent);
            $page_template          = $objBlogHome->fillVariableIfActivated('BLOG_TAG_CLOUD', $strTagCloudSource, $page_template, $blogHomeTagCloudInTemplate);
            $themesPages['index']   = $objBlogHome->fillVariableIfActivated('BLOG_TAG_CLOUD', $strTagCloudSource, $themesPages['index'], $blogHomeTagCloudInTheme);
            $themesPages['sidebar'] = $objBlogHome->fillVariableIfActivated('BLOG_TAG_CLOUD', $strTagCloudSource, $themesPages['sidebar'], $blogHomeTagCloudInSidebar);
        }
        //Blog-TagHitlist
        $blogHomeTagHitlistInContent    = $objBlogHome->searchKeywordInContent('BLOG_TAG_HITLIST', $page_content);
        $blogHomeTagHitlistInTemplate   = $objBlogHome->searchKeywordInContent('BLOG_TAG_HITLIST', $page_template);
        $blogHomeTagHitlistInTheme      = $objBlogHome->searchKeywordInContent('BLOG_TAG_HITLIST', $themesPages['index']);
        $blogHomeTagHitlistInSidebar    = $objBlogHome->searchKeywordInContent('BLOG_TAG_HITLIST', $themesPages['sidebar']);
        if ($blogHomeTagHitlistInContent || $blogHomeTagHitlistInTemplate || $blogHomeTagHitlistInTheme || $blogHomeTagHitlistInSidebar) {
            $strTagHitlistSource = $objBlogHome->getHomeTagHitlist();
            $page_content           = $objBlogHome->fillVariableIfActivated('BLOG_TAG_HITLIST', $strTagHitlistSource, $page_content, $blogHomeTagHitlistInContent);
            $page_template          = $objBlogHome->fillVariableIfActivated('BLOG_TAG_HITLIST', $strTagHitlistSource, $page_template, $blogHomeTagHitlistInTemplate);
            $themesPages['index']   = $objBlogHome->fillVariableIfActivated('BLOG_TAG_HITLIST', $strTagHitlistSource, $themesPages['index'], $blogHomeTagHitlistInTheme);
            $themesPages['sidebar'] = $objBlogHome->fillVariableIfActivated('BLOG_TAG_HITLIST', $strTagHitlistSource, $themesPages['sidebar'], $blogHomeTagHitlistInSidebar);
        }
        //Blog-Categories (Select)
        $blogHomeCategorySelectInContent    = $objBlogHome->searchKeywordInContent('BLOG_CATEGORIES_SELECT', $page_content);
        $blogHomeCategorySelectInTemplate   = $objBlogHome->searchKeywordInContent('BLOG_CATEGORIES_SELECT', $page_template);
        $blogHomeCategorySelectInTheme      = $objBlogHome->searchKeywordInContent('BLOG_CATEGORIES_SELECT', $themesPages['index']);
        $blogHomeCategorySelectInSidebar    = $objBlogHome->searchKeywordInContent('BLOG_CATEGORIES_SELECT', $themesPages['sidebar']);
        if ($blogHomeCategorySelectInContent || $blogHomeCategorySelectInTemplate || $blogHomeCategorySelectInTheme || $blogHomeCategorySelectInSidebar) {
            $strCategoriesSelect = $objBlogHome->getHomeCategoriesSelect();
            $page_content           = $objBlogHome->fillVariableIfActivated('BLOG_CATEGORIES_SELECT', $strCategoriesSelect, $page_content, $blogHomeCategorySelectInContent);
            $page_template          = $objBlogHome->fillVariableIfActivated('BLOG_CATEGORIES_SELECT', $strCategoriesSelect, $page_template, $blogHomeCategorySelectInTemplate);
            $themesPages['index']   = $objBlogHome->fillVariableIfActivated('BLOG_CATEGORIES_SELECT', $strCategoriesSelect, $themesPages['index'], $blogHomeCategorySelectInTheme);
            $themesPages['sidebar'] = $objBlogHome->fillVariableIfActivated('BLOG_CATEGORIES_SELECT', $strCategoriesSelect, $themesPages['sidebar'], $blogHomeCategorySelectInSidebar);
        }
        //Blog-Categories (List)
        $blogHomeCategoryListInContent  = $objBlogHome->searchKeywordInContent('BLOG_CATEGORIES_LIST', $page_content);
        $blogHomeCategoryListInTemplate = $objBlogHome->searchKeywordInContent('BLOG_CATEGORIES_LIST', $page_template);
        $blogHomeCategoryListInTheme    = $objBlogHome->searchKeywordInContent('BLOG_CATEGORIES_LIST', $themesPages['index']);
        $blogHomeCategoryListInSidebar  = $objBlogHome->searchKeywordInContent('BLOG_CATEGORIES_LIST', $themesPages['sidebar']);
        if ($blogHomeCategoryListInContent || $blogHomeCategoryListInTemplate || $blogHomeCategoryListInTheme || $blogHomeCategoryListInSidebar) {
            $strCategoriesList = $objBlogHome->getHomeCategoriesList();
            $page_content           = $objBlogHome->fillVariableIfActivated('BLOG_CATEGORIES_LIST', $strCategoriesList, $page_content, $blogHomeCategoryListInContent);
            $page_template          = $objBlogHome->fillVariableIfActivated('BLOG_CATEGORIES_LIST', $strCategoriesList, $page_template, $blogHomeCategoryListInTemplate);
            $themesPages['index']   = $objBlogHome->fillVariableIfActivated('BLOG_CATEGORIES_LIST', $strCategoriesList, $themesPages['index'], $blogHomeCategoryListInTheme);
            $themesPages['sidebar'] = $objBlogHome->fillVariableIfActivated('BLOG_CATEGORIES_LIST', $strCategoriesList, $themesPages['sidebar'], $blogHomeCategoryListInSidebar);
        }
    }
}


//-------------------------------------------------------
// Load design template
//-------------------------------------------------------
$objTemplate->setTemplate($themesPages['index']);
$objTemplate->addBlock('CONTENT_FILE', 'page_template', $page_template);


$boolShop = false;

//-------------------------------------------------------
// set global content variables
//-------------------------------------------------------
$page_content = str_replace('{PAGE_URL}',  htmlspecialchars($objInit->getPageUri()), $page_content);
$page_content = str_replace('{PRINT_URL}',  $objInit->getPrintUri(), $page_content);
$page_content = str_replace('{PDF_URL}',  $objInit->getPDFUri(), $page_content);
$page_content = str_replace('{TITLE}',  $page_title, $page_content);


//-------------------------------------------------------
// start module switches
//-------------------------------------------------------
switch ($plainSection) {
    //-------------------------------------------------------
    // Access module
    //-------------------------------------------------------
    case 'access':
        $modulespath = 'core_modules/access/index.class.php';
        if (file_exists($modulespath)) require_once($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objAccess = new Access($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $objAccess->getPage($page_metatitle, $page_title));
        break;

    //-------------------------------------------------------
    // Login module
    //-------------------------------------------------------
    case 'login':
        $modulespath = 'core_modules/login/index.class.php';
        /** @ignore */
        if (file_exists($modulespath)) require_once($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objLogin = new Login($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $objLogin->getContent());
        break;

    //-------------------------------------------------------
    // Nettools
    //-------------------------------------------------------
    case 'nettools':
        $modulespath = 'core_modules/nettools/index.class.php';
        /** @ignore */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objNetTools = new NetTools($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $objNetTools->getPage());
        break;


    //-------------------------------------------------------
    // eCommerce Module
    //-------------------------------------------------------
    case 'shop':
        $modulespath = 'modules/shop/index.class.php';
        /** @ignore */
        if (file_exists($modulespath)) require_once($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        if (empty($sessionObj)) $sessionObj = new cmsSession();
        $shopObj = new Shop($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $shopObj->getShopPage());
        $objTemplate->setVariable('SHOPNAVBAR_FILE', $shopObj->getShopNavbar($themesPages['shopnavbar']));
        $boolShop = true;
        break;

    //-------------------------------------------------------
    // News module
    //-------------------------------------------------------
    case 'news':
        $modulespath = 'core_modules/news/index.class.php';
        /** @ignore */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $newsObj= new news($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $newsObj->getNewsPage());
        $newsObj->getPageTitle($page_title);

        //set the meta page description to the teaser if we're displaying news details.
        $teaser = $newsObj->getTeaser();
        if($teaser !== null) //news details, else getTeaser would return null
            $page_desc = $teaser;

        $page_title = $newsObj->newsTitle;
        $page_metatitle = $page_title;
        break;

    //-------------------------------------------------------
    // Livecam
    //-------------------------------------------------------
    case 'livecam':
        $modulespath = 'modules/livecam/index.class.php';
        /** @ignore */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objLivecam = new Livecam($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $objLivecam->getPage());
        //$moduleStyleFile = 'modules/calendar'.MODULE_INDEX.'/frontend_style.css';
        $moduleStyleFile = 'modules/livecam/datepicker/datepickercontrol.css';
        break;

    //-------------------------------------------------------
    // Guestbook
    //-------------------------------------------------------
    case 'guestbook':
        $modulespath = 'modules/guestbook/index.class.php';
        /** @ignore */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objGuestbook = new Guestbook($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $objGuestbook->getPage());
        break;

    //-------------------------------------------------------
    // Memberdir
    //-------------------------------------------------------
    case 'memberdir':
        $modulespath = 'modules/memberdir/index.class.php';
        /** @ignore */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objMemberDir = new memberDir($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $objMemberDir->getPage());
        break;

    //-------------------------------------------------------
    // Data Module
    //-------------------------------------------------------
    case 'data':
        $modulespath = 'modules/data/index.class.php';
        /** @ignore */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        //if (!isset($sessionObj) || !is_object($sessionObj)) $sessionObj = new cmsSession();
        #if (!isset($objAuth) || !is_object($objAuth)) $objAuth = &new Auth($type = 'frontend');

        $objData = new Data($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $objData->getPage());
        break;

    //-------------------------------------------------------
    // Download
    //-------------------------------------------------------
    case 'download':
        $modulespath = 'modules/download/index.class.php';
        /** @ignore */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objDownload = new Download($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $objDownload->getPage());
        break;

    //-------------------------------------------------------
    // Recommend
    //-------------------------------------------------------
    case 'recommend':
        $modulespath = 'modules/recommend/index.class.php';
        /** @ignore */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objRecommend = new Recommend($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $objRecommend->getPage());
        break;

    //-------------------------------------------------------
    // E-Card
    //-------------------------------------------------------
    case 'ecard':
        $modulespath = 'modules/ecard/index.class.php';
        /** @ignore */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objEcard = new Ecard($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $objEcard->getPage());
        break;

    //-------------------------------------------------------
    // Tools
    //-------------------------------------------------------
    case 'tools':
        $modulespath = 'modules/tools/index.class.php';
        /** @ignore */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objTools = new Tools($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $objTools->getPage());
        break;

    //-------------------------------------------------------
    // Dataviewer
    //-------------------------------------------------------
    case 'dataviewer':
        $modulespath = 'modules/dataviewer/index.class.php';
        /** @ignore */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objDataviewer = new Dataviewer($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $objDataviewer->getPage());
        break;

    //-------------------------------------------------------
    // DocumentSystem module
    //-------------------------------------------------------
    case 'docsys':
        $modulespath = 'modules/docsys/index.class.php';
        /** @ignore */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $docSysObj= new docSys($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $docSysObj->getDocSysPage());
        $docSysObj->getPageTitle($page_title);
        $page_title = $docSysObj->docSysTitle;
        $page_metatitle = $docSysObj->docSysTitle;
        break;

    //-------------------------------------------------------
    // Search Module
    //-------------------------------------------------------
    case 'search':
        $modulespath = 'core_modules/search/index.class.php';
        /** @ignore */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $pos = (isset($_GET['pos'])) ? intval($_GET['pos']) : '';
        $objTemplate->setVariable('CONTENT_TEXT', search_getSearchPage($pos, $page_content));
        unset($pos);
        break;

    //-------------------------------------------------------
    // Contact Module
    //-------------------------------------------------------
    case 'contact':
        $modulespath = 'core_modules/contact/index.class.php';
        /** @ignore */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $contactObj= new Contact($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $contactObj->getContactPage());
        $moduleStyleFile = 'core_modules/contact/frontend_style.css';
        break;

    //-------------------------------------------------------
    // Sitemap Core
    //-------------------------------------------------------
    case 'ids':
        $objTemplate->setVariable('CONTENT_TEXT', $page_content);
        break;

    //-------------------------------------------------------
    // Sitemapping
    //-------------------------------------------------------
    case 'sitemap':
        $modulespath = 'core_modules/sitemap/index.class.php';
        /** @ignore */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $sitemap = new sitemap($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $sitemap->getSitemapContent());
        break;

    //-------------------------------------------------------
    // media Core
    //-------------------------------------------------------
    case 'media':
        $modulespath = ASCMS_CORE_MODULE_PATH . '/media/index.class.php';
        /** @ignore */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objMedia = new MediaManager($page_content, $plainSection.MODULE_INDEX);
        $objTemplate->setVariable('CONTENT_TEXT', $objMedia->getMediaPage());
        break;

    //-------------------------------------------------------
    // newsletter Module
    //-------------------------------------------------------
    case 'newsletter':
        $modulespath = 'modules/newsletter/index.class.php';
        /** @ignore */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $newsletter = new newsletter($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $newsletter->getPage());
        break;

    //-------------------------------------------------------
    // gallery Module
    //-------------------------------------------------------
    case 'gallery':
        $modulespath = 'modules/gallery/index.class.php';
        /** @ignore */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objGallery = new Gallery($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $objGallery->getPage());

        $topGalleryName = $objGallery->getTopGalleryName();
        if ($topGalleryName) {
            $page_title = $topGalleryName;
            $page_metatitle = $topGalleryName;
        }
        break;

    //-------------------------------------------------------
    // Voting
    //-------------------------------------------------------
    case 'voting':
        $modulespath = 'modules/voting/index.class.php';
        /** @ignore */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objTemplate->setVariable('CONTENT_TEXT', votingShowCurrent($page_content));
        break;

    //-------------------------------------------------------
    // file uploader
    //-------------------------------------------------------
    case 'fileUploader':
        $modulespath = ASCMS_MODULE_PATH.'/fileUploader/index.class.php';
        if (file_exists($modulespath)) require_once($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objFileUploader = new FileUploader();
        $objFileUploader->getPage();
        exit;
        break;

    //-------------------------------------------------------
    // News Feed Module
    //-------------------------------------------------------
    case 'feed':
        $modulespath = 'modules/feed/index.class.php';
        /** @ignore */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objFeed = new feed($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $objFeed->getFeedPage());
        break;

    //-------------------------------------------------------
    // immo Module
    //-------------------------------------------------------
    case 'immo':
        $modulespath = 'modules/immo/index.class.php';
        /** @ignore */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objImmo = new Immo($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $objImmo->getPage());
        if(!empty($_GET['cmd']) && $_GET['cmd'] == 'showObj'){
            $page_title = $objImmo->getPageTitle($page_title);
            $page_metatitle = $page_title;
        }
        break;

    //-------------------------------------------------------
    // Calendar Module
    //-------------------------------------------------------
    case 'calendar':
        $modulespath = 'modules/calendar'.MODULE_INDEX.'/index.class.php';;
        $moduleStyleFile = 'modules/calendar'.MODULE_INDEX.'/frontend_style.css';
        define('CALENDAR_MANDATE', MODULE_INDEX);
        /** @ignore */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objCalendar = new Calendar($page_content, MODULE_INDEX);
        $objTemplate->setVariable('CONTENT_TEXT', $objCalendar->getCalendarPage());
        if(!empty($objCalendar->pageTitle)) {
            $page_metatitle = $objCalendar->pageTitle;
            $page_title = $objCalendar->pageTitle;
        }
        break;

    //-------------------------------------------------------
    // Reservation Module
    //-------------------------------------------------------
    case 'reservation':
        $modulespath = 'modules/reservation/index.class.php';
        /** @ignore */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
            $objReservationModule = new reservations($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $objReservationModule->getPage());
        $moduleStyleFile = 'modules/reservation/frontend_style.css';
        break;

    //-------------------------------------------------------
    // Directory Module
    //-------------------------------------------------------
    case 'directory':
        $modulespath = 'modules/directory/index.class.php';
        /** @ignore */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $directory = new rssDirectory($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $directory->getPage());
        $directory_pagetitle = $directory->getPageTitle();
        if(!empty($directory_pagetitle)) {
            $page_metatitle = $directory_pagetitle;
            $page_title = $directory_pagetitle;
        }
        if($_GET['cmd'] == 'detail' && isset($_GET['id'])) {
            $objTemplate->setVariable(array(
                'DIRECTORY_ENTRY_ID' => intval($_GET['id']),
            ));
        }
        break;

    //-------------------------------------------------------
    // Market Module
    //-------------------------------------------------------
    case 'market':
        $modulespath = 'modules/market/index.class.php';
        /** @ignore */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $market = new Market($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $market->getPage());
        break;

    //-------------------------------------------------------
    // Podcast Module
    //-------------------------------------------------------
    case 'podcast':
        $modulespath = 'modules/podcast/index.class.php';
        /** @ignore */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objPodcast = new podcast($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $objPodcast->getPage($podcastFirstBlock));
        break;

    //-------------------------------------------------------
    // Forum Module
    //-------------------------------------------------------
    case 'forum':
        $modulespath = 'modules/forum/index.class.php';
        /** @ignore */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objForum = new Forum($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $objForum->getPage());
//        $moduleStyleFile = 'modules/forum/css/frontend_style.css';
        break;

    //-------------------------------------------------------
    // Blog Module
    //-------------------------------------------------------
    case 'blog':
        $modulespath = 'modules/blog/index.class.php';
        /** @ignore */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objBlog = new Blog($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $objBlog->getPage());
        break;

    //-------------------------------------------------------
    // Knowledge Module
    //-------------------------------------------------------
    case 'knowledge':
        $modulespath = 'modules/knowledge/index.class.php';
        /** @ignore */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objKnowledge = new Knowledge($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $objKnowledge->getPage());
        if (!empty($objKnowledge->pageTitle)) {
            $page_title = $objKnowledge->pageTitle;
            $page_metatitle = $objKnowledge->pageTitle;
        }
        break;

    //-------------------------------------------------------
    // jobs module
    //-------------------------------------------------------
    case 'jobs':
        $modulespath = 'modules/jobs/index.class.php';
        /** @ignore */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $jobsObj= new jobs($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $jobsObj->getJobsPage());
        $jobsObj->getPageTitle($page_title);
        $page_title = $jobsObj->jobsTitle;
        $page_metatitle = $jobsObj->jobsTitle;
        break;

    //-------------------------------------------------------
    // logout
    //-------------------------------------------------------
    case 'logout':
        if (isset($objFWUser) && is_object($objFWUser) && $objFWUser->objUser->login()) {
            $objFWUser->logout();
        }
        break;

    //-------------------------------------------------------
    // error module
    //-------------------------------------------------------
    case 'error':
        $modulespath = 'core/error.class.php';
        /** @ignore */
        if (file_exists($modulespath)) require($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $errorObj= new error($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $errorObj->getErrorPage());
        break;

    //-------------------------------------------------------
    // E-Government Module
    //-------------------------------------------------------
    case 'egov':
        $modulespath = 'modules/egov/index.class.php';
        /** @ignore */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objEgov = new eGov($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $objEgov->getPage());
        break;

    case 'support':
        /**
         * Support System Module
         * @author  Reto Kohli <reto.kohli@comvation.com>
         * @since   1.2.0
         * @version 0.0.1 alpha
         */
        if (!file_exists(ASCMS_MODULE_PATH.'/support/index.class.php'))
            die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        /** @ignore */
        require_once(ASCMS_MODULE_PATH.'/support/index.class.php');
        $objSupport = new support($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $objSupport->getPage());
        break;

    //-------------------------------------------------------
    // Partners Module
    //-------------------------------------------------------
    case 'partners':
        $modulespath = 'modules/partners/index.class.php';
        /** @ignore */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objPartners = new Partners($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $objPartners->getPage());
        break;

    //-------------------------------------------------------
    // U2U Module
    //-------------------------------------------------------
    case 'u2u':
        $modulespath = 'modules/u2u/index.class.php';
        /** @ignore */
        if (file_exists($modulespath)) require_once($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objU2u = new u2u($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $objU2u->getPage($page_metatitle, $page_title));
        break;

    //-------------------------------------------------------
    // Auction Module
    //-------------------------------------------------------
    case 'auction':
        $modulespath = 'modules/auction/index.class.php';
        /** @ignore */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $auction = new Auction($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $auction->getPage());
        break;

    //-------------------------------------------------------
    // Download Module
    //-------------------------------------------------------
    case 'downloads':
        $modulespath = 'modules/downloads/index.class.php';
        /** @ignore */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objDownloadsModule = new downloads($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $objDownloadsModule->getPage());
        $downloads_pagetitle = $objDownloadsModule->getPageTitle();
        if(!empty($downloads_pagetitle)) {
            $page_metatitle = $downloads_pagetitle;
            $page_title = $downloads_pagetitle;
        }
        break;

    case 'hotelcard':
        /**
         * Hotelcard Module
         * @author  Reto Kohli <reto.kohli@comvation.com>
         * @since   2.2.0
         * @version 2.2.0
         */
        if (!file_exists(ASCMS_MODULE_PATH.'/hotelcard/index.class.php'))
            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        if (empty($sessionObj)) $sessionObj = new cmsSession();
        /** @ignore */
        require_once(ASCMS_MODULE_PATH.'/hotelcard/index.class.php');
        $objTemplate->setVariable(
            'CONTENT_TEXT', Hotelcard::getPage($page_content));
        break;

    //-------------------------------------------------------
    // default case
    //-------------------------------------------------------
    default:
        $objTemplate->setVariable('CONTENT_TEXT', $page_content);
}

//-------------------------------------------------------
// show shop navbar on each page
//-------------------------------------------------------
if (   MODULE_INDEX < 2
    && isset($_CONFIGURATION['custom']['shopnavbar'])
    && $_CONFIGURATION['custom']['shopnavbar'] == true) {
    if (empty($shopObj)) {
        $modulespath = 'modules/shop/index.class.php';
        if (file_exists($modulespath)){
            /** @ignore */
            require_once($modulespath);
            $sessionObj = new cmsSession();
            $_ARRAYSHOPLANG = $objInit->loadLanguageData('shop');
            $_ARRAYLANG = array_merge($_ARRAYLANG, $_ARRAYSHOPLANG);
            $boolShop = true;
            $shopObj = new Shop();
            $objTemplate->setVariable('SHOPNAVBAR_FILE',
                $shopObj->getShopNavbar($themesPages['shopnavbar']));
        }
    }
}

//-------------------------------------------------------
// Calendar
//-------------------------------------------------------
// print_r($objTemplate->getPlaceholderList());
if (MODULE_INDEX < 2) {
$calendarCheck1 = $objTemplate->placeholderExists('CALENDAR');
$calendarCheck2 = $objTemplate->placeholderExists('CALENDAR_EVENTS');
if(!empty($calendarCheck1) OR !empty($calendarCheck2)) {
    $modulespath = 'modules/calendar/HomeCalendar.class.php';
    if (file_exists($modulespath)){
        /** @ignore */
        require_once($modulespath);
        $objHomeCalendar = new HomeCalendar();
        if(!empty($calendarCheck1)) {
            $objTemplate->setVariable('CALENDAR', $objHomeCalendar->getHomeCalendar());
        }
        if(!empty($calendarCheck2)) {
            $objTemplate->setVariable('CALENDAR_EVENTS', $objHomeCalendar->getHomeCalendarEvents());
        }
    }
}
}


//-------------------------------------------------------
// Directory Show Latest
//-------------------------------------------------------


//$directoryCheck = $objTemplate->blockExists('directoryLatest_row_1');

$directoryCheck = array();

for($i = 1; $i <= 10; $i++){
    if($objTemplate->blockExists('directoryLatest_row_'.$i)){
        array_push($directoryCheck, $i);
    }
}

if(!empty($directoryCheck)) {
    $modulespath = 'modules/directory/index.class.php';
    if (file_exists($modulespath)){
        /** @ignore */
        require_once($modulespath);
        $objDirectory = new rssDirectory('');
        if(!empty($directoryCheck)) {
            $objTemplate->setVariable('TXT_DIRECTORY_LATEST', $_CORELANG['TXT_DIRECTORY_LATEST']);
               $objDirectory->getBlockLatest($directoryCheck);
        }
    }
}

//-------------------------------------------------------
// Market Show Latest
//-------------------------------------------------------
$marketCheck = $objTemplate->blockExists('marketLatest');

if(!empty($marketCheck)) {
    $modulespath = 'modules/market/index.class.php';
    if (file_exists($modulespath)){
        /** @ignore */
        require_once($modulespath);
        $objMarket = new Market('');
        if(!empty($marketCheck)) {
            $objTemplate->setVariable('TXT_MARKET_LATEST', $_CORELANG['TXT_MARKET_LATEST']);
               $objMarket->getBlockLatest();
        }
    }
}

//-------------------------------------------------------
// Set banner variables
//-------------------------------------------------------

if ($_CONFIG['bannerStatus'] == '1') {
    $modulespath = 'core_modules/banner/index.class.php';
    if (file_exists($modulespath)) {
        /** @ignore */
        include_once($modulespath);
        $objBanner = new Banner();
        $objTemplate->setVariable(array(
        'BANNER_GROUP_1'    => $objBanner->getBannerCode(1, $pageId),
        'BANNER_GROUP_2'     => $objBanner->getBannerCode(2, $pageId),
        'BANNER_GROUP_3'    => $objBanner->getBannerCode(3, $pageId),
        'BANNER_GROUP_4'    => $objBanner->getBannerCode(4, $pageId),
        'BANNER_GROUP_5'     => $objBanner->getBannerCode(5, $pageId),
        'BANNER_GROUP_6'    => $objBanner->getBannerCode(6, $pageId),
        'BANNER_GROUP_7'     => $objBanner->getBannerCode(7, $pageId),
        'BANNER_GROUP_8'     => $objBanner->getBannerCode(8, $pageId),
        'BANNER_GROUP_9'     => $objBanner->getBannerCode(9, $pageId),
        'BANNER_GROUP_10'    => $objBanner->getBannerCode(10, $pageId)
        ));
    }
    if(isset($_REQUEST['bannerId'])){
        $objBanner->updateClicks(intval($_REQUEST['bannerId']));
    }
}


//-------------------------------------------------------
// Frontend Editing: prepare needed code-fragments
//-------------------------------------------------------
$strFeInclude = $strFeLink = $strFeContent = null;
if ($_CONFIG['frontendEditingStatus'] == 'on') {
    $modulespath = 'core_modules/frontendEditing/frontendEditingLib.class.php';
    if (file_exists($modulespath)) {
        /**
         * @ignore
         */
        include_once($modulespath);
        $strFeInclude   = frontendEditingLib::getIncludeCode();
        $strFeLink      = frontendEditingLib::getLinkCode();
        $strFeContent   = frontendEditingLib::getContentCode($pageId, $section, $command);
    }
}


// remove the registered-sign from the cms name
$contrexxCmsName = $_CONFIG['coreCmsName'];
$contrexxCmsName[8] = ' ';
$contrexxCmsName[9] = ' ';

//-------------------------------------------------------
// set global template variables
//-------------------------------------------------------
$objTemplate->setVariable(array(
    'CHARSET'              => $objInit->getFrontendLangCharset(),
    'TITLE'                => $page_title,
    'METATITLE'            => $page_metatitle,
    'NAVTITLE'             => $page_catname,
    'GLOBAL_TITLE'         => $_CONFIG['coreGlobalPageTitle'],
    'DOMAIN_URL'           => $_CONFIG['domainUrl'],
    'BASE_URL'             => ASCMS_PROTOCOL.'://'.$_CONFIG['domainUrl'].ASCMS_PATH_OFFSET,
    'METAKEYS'             => $page_keywords,
    'METADESC'             => $page_desc,
    'METAROBOTS'           => $page_robots,
    'CONTENT_TITLE'        => '<span id="fe_PreviewTitle">'.$page_title.'</span>',
    'CSS_NAME'             => $pageCssName,
    'PRINT_URL'            => $objInit->getPrintUri(),
    'PDF_URL'              => $objInit->getPDFUri(),
    'PAGE_URL'             => htmlspecialchars($objInit->getPageUri()),
    'CURRENT_URL'          => $objInit->getCurrentPageUri(),
    'DATE'                 => showFormattedDate(),
    'TIME'                 => date('H:i', time()),
    'NAVTREE'              => $objNavbar->getTrail(),
    'SUBNAVBAR_FILE'       => $objNavbar->getNavigation($themesPages['subnavbar'],$boolShop),
    'SUBNAVBAR2_FILE'      => $objNavbar->getNavigation($themesPages['subnavbar2'],$boolShop),
    'SUBNAVBAR3_FILE'      => $objNavbar->getNavigation($themesPages['subnavbar3'],$boolShop),
    'NAVBAR_FILE'          => $objNavbar->getNavigation($themesPages['navbar'],$boolShop),
    'ONLINE_USERS'         => $objCounter->getOnlineUsers(),
    'VISITOR_NUMBER'       => $objCounter->getVisitorNumber(),
    'COUNTER'              => $objCounter->getCounterTag(),
    'BANNER'               => isset($objBanner) ? $objBanner->getBannerJS() : '',
    'VERSION'              => $contrexxCmsName,
    'LANGUAGE_NAVBAR'      => $objNavbar->getFrontendLangNavigation(),
    'ACTIVE_LANGUAGE_NAME' => $objInit->getFrontendLangName(),
    'RANDOM'               => md5(microtime()),
    'TXT_SEARCH'           => $_CORELANG['TXT_SEARCH'],
    'MODULE_INDEX'         => MODULE_INDEX,
    'LOGIN_INCLUDE'        => (isset($strFeInclude) ? $strFeInclude : ''),
    'LOGIN_URL'            => (isset($strFeLink) ? $strFeLink : ''),
    'LOGIN_CONTENT'        => (isset($strFeContent) ? $strFeContent : ''),
    'JAVASCRIPT'           => 'javascript_inserting_here',
    'TXT_CORE_LAST_MODIFIED_PAGE' => $_CORELANG['TXT_CORE_LAST_MODIFIED_PAGE'],
    'LAST_MODIFIED_PAGE'   => date(ASCMS_DATE_SHORT_FORMAT, $page_modified),
));

//include and initialize handler to fill Social Network template variables
require_once('lib/SocialNetworks.class.php');
$socialNetworkTemplater = new SocialNetworks($_CONFIG['domainUrl'].$objInit->getCurrentPageUri());
//set Social Network template variables
$objTemplate->setVariable('SN_FACEBOOK_LIKE', 'aaaa');

if ($objTemplate->blockExists('access_logged_in')) {
    $objFWUser = FWUser::getFWUserObject();
    if ($objFWUser->objUser->login()) {
        $objFWUser->setLoggedInInfos();
        $objTemplate->parse('access_logged_in');
    } else {
        $objTemplate->hideBlock('access_logged_in');
    }
}
if ($objTemplate->blockExists('access_logged_out')) {
    $objFWUser = FWUser::getFWUserObject();
    if ($objFWUser->objUser->login()) {
        $objTemplate->hideBlock('access_logged_out');
    } else {
        $objTemplate->touchBlock('access_logged_out');
    }
}

// currently online users
$modulespath = 'core_modules/access/lib/blocks.class.php';
$objAccessBlocks = false;
if ($objTemplate->blockExists('access_currently_online_member_list')) {
    if (    FWUser::showCurrentlyOnlineUsers()
        && (    $objTemplate->blockExists('access_currently_online_female_members')
            ||  $objTemplate->blockExists('access_currently_online_male_members')
            ||  $objTemplate->blockExists('access_currently_online_members'))) {
        if (file_exists($modulespath) && require_once($modulespath))
            $objAccessBlocks = new Access_Blocks();
        if ($objTemplate->blockExists('access_currently_online_female_members'))
            $objAccessBlocks->setCurrentlyOnlineUsers('female');
        if ($objTemplate->blockExists('access_currently_online_male_members'))
            $objAccessBlocks->setCurrentlyOnlineUsers('male');
        if ($objTemplate->blockExists('access_currently_online_members'))
            $objAccessBlocks->setCurrentlyOnlineUsers();
    } else {
        $objTemplate->hideBlock('access_currently_online_member_list');
    }
}

// last active users
if ($objTemplate->blockExists('access_last_active_member_list')) {
    if (    FWUser::showLastActivUsers()
        && (    $objTemplate->blockExists('access_last_active_female_members')
            ||  $objTemplate->blockExists('access_last_active_male_members')
            ||  $objTemplate->blockExists('access_last_active_members'))) {
        if (   !$objAccessBlocks
            && file_exists($modulespath) && require_once($modulespath))
            $objAccessBlocks = new Access_Blocks();
        if ($objTemplate->blockExists('access_last_active_female_members'))
            $objAccessBlocks->setLastActiveUsers('female');
        if ($objTemplate->blockExists('access_last_active_male_members'))
            $objAccessBlocks->setLastActiveUsers('male');
        if ($objTemplate->blockExists('access_last_active_members'))
            $objAccessBlocks->setLastActiveUsers();
    } else {
        $objTemplate->hideBlock('access_last_active_member_list');
    }
}

// latest registered users
if ($objTemplate->blockExists('access_latest_registered_member_list')) {
    if (    FWUser::showLatestRegisteredUsers()
        && (    $objTemplate->blockExists('access_latest_registered_female_members')
            ||  $objTemplate->blockExists('access_latest_registered_male_members')
            ||  $objTemplate->blockExists('access_latest_registered_members'))) {
        if (   !$objAccessBlocks
            && file_exists($modulespath) && require_once($modulespath))
            $objAccessBlocks = new Access_Blocks();
        if ($objTemplate->blockExists('access_latest_registered_female_members'))
            $objAccessBlocks->setLatestRegisteredUsers('female');
        if ($objTemplate->blockExists('access_latest_registered_male_members'))
            $objAccessBlocks->setLatestRegisteredUsers('male');
        if ($objTemplate->blockExists('access_latest_registered_members'))
            $objAccessBlocks->setLatestRegisteredUsers();
    } else {
        $objTemplate->hideBlock('access_latest_registered_member_list');
    }
}

// birthday users
if ($objTemplate->blockExists('access_birthday_member_list')) {
    if (    FWUser::showBirthdayUsers()
        && (    $objTemplate->blockExists('access_birthday_female_members')
            ||  $objTemplate->blockExists('access_birthday_male_members')
            ||  $objTemplate->blockExists('access_birthday_members'))) {
        if (   !$objAccessBlocks
            && file_exists($modulespath) && require_once($modulespath))
            $objAccessBlocks = new Access_Blocks();
        if ($objAccessBlocks->isSomeonesBirthdayToday()) {
            if ($objTemplate->blockExists('access_birthday_female_members'))
                $objAccessBlocks->setBirthdayUsers('female');
            if ($objTemplate->blockExists('access_birthday_male_members'))
                $objAccessBlocks->setBirthdayUsers('male');
            if ($objTemplate->blockExists('access_birthday_members'))
                $objAccessBlocks->setBirthdayUsers();
            $objTemplate->touchBlock('access_birthday_member_list');
        } else {
            $objTemplate->hideBlock('access_birthday_member_list');
        }
    } else {
        $objTemplate->hideBlock('access_birthday_member_list');
    }
}


//-------------------------------------------------------
// parse system
//-------------------------------------------------------
$parsingtime = explode(' ', microtime());
$time = round(((float)$parsingtime[0] + (float)$parsingtime[1]) - ((float)$starttime[0] + (float)$starttime[1]), 5);
$objTemplate->setVariable('PARSING_TIME', $time);

//Allow PRINT_URL & PDF_URL in sidebar
$themesPages['sidebar'] = str_replace('{PRINT_URL}',$objInit->getPrintUri(), $themesPages['sidebar']);
$themesPages['sidebar'] = str_replace('{PDF_URL}',$objInit->getPDFUri(), $themesPages['sidebar']);

$objTemplate->setVariable(array(
    'SIDEBAR_FILE'     => $themesPages['sidebar'],
    'JAVASCRIPT_FILE'  => $themesPages['javascript'],
    'BUILDIN_STYLE_FILE'  => $themesPages['buildin_style'],
    'DATE_YEAR'           => date('Y'),
    'DATE_MONTH'          => date('m'),
    'DATE_DAY'            => date('d'),
    'DATE_TIME'           => date('H:i'),
    'BUILDIN_STYLE_FILE'  => $themesPages['buildin_style'],
    'JAVASCRIPT_LIGHTBOX' =>
        '<script type="text/javascript" src="lib/lightbox/javascript/mootools.js"></script>
        <script type="text/javascript" src="lib/lightbox/javascript/slimbox.js"></script>',
    'JAVASCRIPT_MOBILE_DETECTOR' =>
        '<script type="text/javascript" src="lib/mobiledetector.js"></script>',
));

if (!empty($moduleStyleFile))
    $objTemplate->setVariable(
        'STYLE_FILE',
        "<link rel=\"stylesheet\" href=\"$moduleStyleFile\" type=\"text/css\" media=\"screen, projection\" />"
    );

if (isset($_GET['pdfview']) && intval($_GET['pdfview']) == 1) {
    require_once ASCMS_CORE_PATH.'/pdf.class.php';
    $objPDF          = new PDF();
    $objPDF->title   = $page_title.(empty($page_title) ? null : '.pdf');
    $objPDF->content = $objTemplate->get();
    $objPDF->Create();
} else {
    /**
     * Get all javascripts in the code, replace them with nothing, and register the js file
     * to the javascript lib. This is because we don't want something twice, and there could be
     * a theme that requires a javascript, which then could be used by a module too and therefore would
     * be loaded twice.
     */
    $endcode = $objTemplate->get();
    /* Finds all uncommented script tags, strips them out of the HTML and
     * stores them internally so we can put them in the placeholder later
     * (see JS::getCode() below)
     */
    JS::findJavascripts($endcode);
    /*
     * Proposal:  Use this
     *     $endcode = preg_replace_callback('/<script .*?src=(["\'])(.*?)(\1).*?\/?>(?:<\/script>)?/i', 'JS::registerFromRegex', $endcode);
     * and change JS::registerFromRegex to use index 2
     */
    // i know this is ugly, but is there another way
    $endcode = str_replace('javascript_inserting_here', JS::getCode(), $endcode);
    echo $endcode;
}
$objCache->endCache();

?>
