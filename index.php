<?php

/**
 * The main page for the CMS
 * @copyright    CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team
 * @version     v1.0.9.10.1 stable
 * @package        contrexx
 * @subpackage    core
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
 * @uses        /modules/immo/index.class.php";
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
    header("Location: installer/index.php");
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

//-------------------------------------------------------
// Initialize database object
//-------------------------------------------------------
$errorMsg = '';
$objDatabase = getDatabaseObject($errorMsg);

if ($objDatabase === false) {
    die('Database error.');
}

//-------------------------------------------------------
// Caching-System
//-------------------------------------------------------
/**
 * Include the cache module.  The cache is initialized right afterwards.
 */
require_once ASCMS_CORE_MODULE_PATH.'/cache/index.class.php';
$objCache = &new Cache();
$objCache->startCache();

//-------------------------------------------------------
// Load settings and configuration
//-------------------------------------------------------

$objInit = &new InitCMS();
$_LANGID = $objInit->getFrontendLangId();
$_CORELANG = $objInit->loadLanguageData('core');
$_ARRAYLANG = $objInit->loadLanguageData();

//-------------------------------------------------------
// Webapp Intrusion Detection System
//-------------------------------------------------------

$objSecurity = &new Security;
$_GET = $objSecurity->detectIntrusion($_GET);
$_POST = $objSecurity->detectIntrusion($_POST);
$_COOKIE = $objSecurity->detectIntrusion($_COOKIE);
$_REQUEST = $objSecurity->detectIntrusion($_REQUEST);


//-------------------------------------------------------
// Check Referer -> Redirect
//-------------------------------------------------------
require_once ASCMS_CORE_PATH.'/redirect.class.php';
//$objRedirect = &new redirect();


//-------------------------------------------------------
// initialize objects
//-------------------------------------------------------
$objTemplate = &new HTML_Template_Sigma(ASCMS_THEMES_PATH);

$objTemplate->setErrorHandling(PEAR_ERROR_DIE);

$section = isset($_REQUEST['section']) ? contrexx_addslashes($_REQUEST['section']) : '';
$command = isset($_REQUEST['cmd']) ? contrexx_addslashes($_REQUEST['cmd']) : '';
$page    = isset($_REQUEST['page']) ? intval($_GET['page']) : 0;
$history = isset($_REQUEST['history']) ? intval($_GET['history']) : 0;

$pageId  = $objInit->getPageID($page, $section, $command, $history);
$is_home = $objInit->is_home;

$objNavbar  = &new Navigation($pageId);
$objCounter = &new statsLibrary();
$objCounter->checkForSpider();
$themesPages = $objInit->getTemplates();
$query="SELECT c.content,
               c.title,
               n.catname,
               c.redirect,
               c.metatitle,
               c.metadesc,
               c.metakeys,
               c.metarobots,
               c.css_name,
               n.protected,
               n.frontend_access_id
               ".(!empty($history) ? ',n.catid' : '')."
          FROM ".DBPREFIX.(empty($history) ? 'content' : 'content_history')." AS c,
               ".DBPREFIX.(empty($history) ? 'content_navigation' : 'content_navigation_history')." AS n
         WHERE c.id = ".(empty($history) ? $pageId : $history)."
           AND c.id = ".(!empty($history) ? 'n.id' : "n.catid
           AND (n.startdate<=CURDATE() OR n.startdate='0000-00-00')
           AND (n.enddate>=CURDATE() OR n.enddate='0000-00-00')
           AND n.activestatus='1'
           AND n.is_validated='1'");
$objResult = $objDatabase->SelectLimit($query, 1);

if ($objResult === false || $objResult->EOF) {
    if ($section == "error") {
        // If the error module is not installed, show this
        die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
    } else {
        header("Location: ?section=error&id=404");
    }
    exit;
} else {
    $page_content   = $objResult->fields["content"];
    $page_title     = $objResult->fields["title"];
    $page_catname   = $objResult->fields["catname"];
    $page_metatitle = htmlentities($objResult->fields["metatitle"], ENT_QUOTES, CONTREXX_CHARSET);
    $page_keywords  = htmlentities($objResult->fields["metakeys"], ENT_QUOTES, CONTREXX_CHARSET);
    $page_robots    = $objResult->fields["metarobots"];
    $pageCssName    = $objResult->fields["css_name"];
    $page_desc      = htmlentities($objResult->fields["metadesc"], ENT_QUOTES, CONTREXX_CHARSET);
    $page_redirect  = $objResult->fields["redirect"];
    $page_protected = $objResult->fields["protected"];
    $page_access_id = $objResult->fields["frontend_access_id"];
    $page_template  = $themesPages['content'];

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

//-------------------------------------------------------
// authentification for protected pages
//-------------------------------------------------------
if ($page_protected || $history) {
    $sessionObj=&new cmsSession();
    $sessionObj->cmsSessionStatusUpdate($status="frontend");

    $objAuth = &new Auth($type='frontend');
    if ($objAuth->checkAuth()) {
        $objPerm =&new Permission($type='frontend');
        if ($page_protected) {
	        if (!$objPerm->checkAccess($page_access_id, 'dynamic')) {
	            $link=base64_encode($_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);
	            header ("Location: ?section=login&cmd=noaccess&redirect=".$link);
	            exit;
	        }
        }
        if ($history && !$objPerm->checkAccess(78, 'static')) {
			$link=base64_encode($_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);
			header ("Location: ?section=login&cmd=noaccess&redirect=".$link);
			exit;
        }
    } else {
        $link=base64_encode($_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);
        header ("Location: ?section=login&redirect=".$link);
        exit;
    }
    $loginStatus = $objAuth->status();
}

if (!empty($page_redirect)){
    header("Location: " . $page_redirect);
    exit;
}

//-------------------------------------------------------
// Start page or default page for no section
//-------------------------------------------------------
if ($is_home){
    $page_template  = $themesPages['home'];
}


//-------------------------------------------------------
// Set news teasers
//-------------------------------------------------------
if ($_CONFIG['newsTeasersStatus'] == '1') {
    // set news teasers in the content
    if (preg_match_all('/{TEASERS_([0-9A-Z_-]+)}/ms', $page_content, $arrMatches)) {
        $modulespath = "core_modules/news/lib/teasers.class.php";
        if (file_exists($modulespath)) {
            /**
             * @ignore
             */
            include_once($modulespath);
            $objTeasers = &new Teasers();
            $objTeasers->setTeaserFrames($arrMatches[1], $page_content);
        }
    }

    // set news teasers in the page design
    if (preg_match_all('/{TEASERS_([0-9A-Z_-]+)}/ms', $page_template, $arrMatches)) {
        $modulespath = "core_modules/news/lib/teasers.class.php";
        if (file_exists($modulespath)) {
            /**
             * @ignore
             */
            include_once($modulespath);
            $objTeasers = &new Teasers();
            $objTeasers->setTeaserFrames($arrMatches[1], $page_template);
        }
    }

    // set news teasers in the website design
    if (preg_match_all('/{TEASERS_([0-9A-Z_-]+)}/ms', $themesPages['index'], $arrMatches)) {
        $modulespath = "core_modules/news/lib/teasers.class.php";
        if (file_exists($modulespath)) {
            /**
             * @ignore
             */
            include_once($modulespath);
            $objTeasers = &new Teasers();
            $objTeasers->setTeaserFrames($arrMatches[1], $themesPages['index']);
        }
    }
}

//-------------------------------------------------------
// Set NewsML messages
//-------------------------------------------------------
if ($_CONFIG['feedNewsMLStatus'] == '1') {
    if (preg_match_all('/{NEWSML_([0-9A-Z_-]+)}/ms', $page_content, $arrMatches)) {
        $modulespath = "modules/feed/newsML.class.php";
        if (file_exists($modulespath)) {
            /**
             * @ignore
             */
            require_once $modulespath;
            $objNewsML = &new NewsML();
            $objNewsML->setNews($arrMatches[1], $page_content);
        }
    }
    if (preg_match_all('/{NEWSML_([0-9A-Z_-]+)}/ms', $page_template, $arrMatches)) {
        $modulespath = "modules/feed/newsML.class.php";
        if (file_exists($modulespath)) {
            /**
             * @ignore
             */
            require_once $modulespath;
            $objNewsML = &new NewsML();
            $objNewsML->setNews($arrMatches[1], $page_template);
        }
    }
    if (preg_match_all('/{NEWSML_([0-9A-Z_-]+)}/ms', $themesPages['index'], $arrMatches)) {
        $modulespath = "modules/feed/newsML.class.php";
        if (file_exists($modulespath)) {
            /**
             * @ignore
             */
            require_once $modulespath;
            $objNewsML = &new NewsML();
            $objNewsML->setNews($arrMatches[1], $themesPages['index']);
        }
    }
}


//-------------------------------------------------------
// Set popups
//-------------------------------------------------------
$modulespath = "modules/popup/index.class.php";
if (file_exists($modulespath)) {
    /**
     * @ignore
     */
    if (preg_match_all('/{POPUP_JS_FUNCTION}/ms', $themesPages['index'], $arrMatches)) {
		require_once $modulespath;
		$objPopup = &new popup();

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
    $modulespath = "modules/block/index.class.php";
    if (file_exists($modulespath)) {
        /**
         * @ignore
         */
        require_once $modulespath;
        $objBlock = &new block();
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
$modulespath = "core_modules/news/lib/headlines.class.php";
/**
 * @ignore
 */
if (file_exists($modulespath)) include_once($modulespath);
$newsHeadlinesObj = &new newsHeadlines($themesPages['headlines']);
$page_content = str_replace('{HEADLINES_FILE}', $newsHeadlinesObj->getHomeHeadlines(), $page_content);
$themesPages['index'] = str_replace('{HEADLINES_FILE}', $newsHeadlinesObj->getHomeHeadlines(), $themesPages['index']);
$page_template = str_replace('{HEADLINES_FILE}', $newsHeadlinesObj->getHomeHeadlines(), $page_template);


//-------------------------------------------------------
// Get Calendar Events
//-------------------------------------------------------
$modulespath = "modules/calendar/headlines.class.php";
if (file_exists($modulespath)) {
    /**
     * @ignore
     */
    include_once($modulespath);
    $calHeadlinesObj = &new calHeadlines($themesPages['calendar_headlines']);
    $page_content = str_replace('{EVENTS_FILE}', $calHeadlinesObj->getHeadlines(), $page_content);
    $themesPages['index'] = str_replace('{EVENTS_FILE}', $calHeadlinesObj->getHeadlines(), $themesPages['index']);
    $themesPages['home'] = str_replace('{EVENTS_FILE}', $calHeadlinesObj->getHeadlines(), $themesPages['home']);
    $page_template = str_replace('{EVENTS_FILE}', $calHeadlinesObj->getHeadlines(), $page_template);
}


//-------------------------------------------------------
// get Newsletter
//-------------------------------------------------------

$modulespath = "modules/newsletter/index.class.php";
if (file_exists($modulespath)) {
    /**
     * @ignore
     */
    require_once($modulespath);
    $_ARRAYLANG = array_merge($_ARRAYLANG, $objInit->loadLanguageData('newsletter'));
    $newsletter = &new newsletter('');
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
// get Directory Homecontent
//-------------------------------------------------------

if ($_CONFIG['directoryHomeContent'] == '1') {
    $modulespath = "modules/directory/homeContent.class.php";
    if (file_exists($modulespath)) {
        /**
         * @ignore
         */
        require_once($modulespath);

        $directoryHomeContentInPageContent = false;
        $directoryHomeContentInPageTemplate = false;
        $directoryHomeContentInThemesPage = false;

        if (preg_match_all('/{DIRECTORY_FILE}/ms', $page_content, $arrMatches)) {
            $directoryHomeContentInPageContent = true;
        }
        if (preg_match_all('/{DIRECTORY_FILE}/ms', $page_template, $arrMatches)) {
            $directoryHomeContentInPageTemplate = true;
        }
        if (preg_match_all('/{DIRECTORY_FILE}/ms', $themesPages['index'], $arrMatches)) {
            $directoryHomeContentInThemesPage = true;
        }

        if ($directoryHomeContentInPageContent || $directoryHomeContentInPageTemplate || $directoryHomeContentInThemesPage) {
            $_ARRAYLANG = array_merge($_ARRAYLANG, $objInit->loadLanguageData('directory'));
            $dirObj = &new dirHomeContent($themesPages['directory_content']);
        }

        if ($directoryHomeContentInPageContent) {
            $page_content = str_replace('{DIRECTORY_FILE}', $dirObj->getContent(), $page_content);
        }
        if ($directoryHomeContentInPageTemplate) {
            $page_template = str_replace('{DIRECTORY_FILE}', $dirObj->getContent(), $page_template);
        }
        if ($directoryHomeContentInThemesPage) {
            $themesPages['index'] = str_replace('{DIRECTORY_FILE}', $dirObj->getContent(), $themesPages['index']);
        }
    }
}

//-------------------------------------------------------
// get Forum latest entries content
//-------------------------------------------------------
if ($_CONFIG['forumHomeContent'] == '1') {
    $modulespath = "modules/forum/homeContent.class.php";
    if (file_exists($modulespath)) {
        /**
         * @ignore
         */
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
            $objForum = &new ForumHomeContent($themesPages['forum_content']);
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

//-------------------------------------------------------
// Get Gallery-Images (Latest, Random)
//-------------------------------------------------------
$modulespath = "modules/gallery/homeContent.class.php";
if (file_exists($modulespath)) {

    /**
     * @ignore
     */
    require_once($modulespath);
    $objGalleryHome = &new GalleryHomeContent();

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
// Load JavaScript Cart
//-------------------------------------------------------
if ($_CONFIGURATION['custom']['shopJsCart'] && ($_CONFIGURATION['custom']['shopnavbar'] || (!empty($_REQUEST['section']) && $_REQUEST['section'] == 'shop'))) {
    $modulespath = "modules/shop/index.class.php";
    if (file_exists($modulespath)) {
        /**
         * @ignore
         */
        require_once($modulespath);
        $_ARRAYLANG = array_merge($_ARRAYLANG, $objInit->loadLanguageData('shop'));

        if (preg_match_all('@<!--\s+BEGIN\s+(shopJsCart)\s+-->(.*)<!--\s+END\s+\1\s+-->@sm', $themesPages['sidebar'], $regs, PREG_SET_ORDER)) {
            $themesPages['sidebar'] = preg_replace('@(<!--\s+BEGIN\s+(shopJsCart)\s+-->.*<!--\s+END\s+\2\s+-->)@sm', Shop::setJsCart($regs[0][2]), $themesPages['sidebar']);
        }
        if (preg_match_all('@<!--\s+BEGIN\s+(shopJsCart)\s+-->(.*)<!--\s+END\s+\1\s+-->@sm', $themesPages['shopnavbar'], $regs, PREG_SET_ORDER)) {
            $themesPages['shopnavbar'] = preg_replace('@(<!--\s+BEGIN\s+(shopJsCart)\s+-->.*<!--\s+END\s+\2\s+-->)@sm', Shop::setJsCart($regs[0][2]), $themesPages['shopnavbar']);
        }
        if (preg_match_all('@<!--\s+BEGIN\s+(shopJsCart)\s+-->(.*)<!--\s+END\s+\1\s+-->@sm', $themesPages['index'], $regs, PREG_SET_ORDER)) {
            $themesPages['index'] = preg_replace('@(<!--\s+BEGIN\s+(shopJsCart)\s+-->.*<!--\s+END\s+\2\s+-->)@sm', Shop::setJsCart($regs[0][2]), $themesPages['index']);
        }
        if (preg_match_all('@<!--\s+BEGIN\s+(shopJsCart)\s+-->(.*)<!--\s+END\s+\1\s+-->@sm', $page_content, $regs, PREG_SET_ORDER)) {
            $page_content = preg_replace('@(<!--\s+BEGIN\s+(shopJsCart)\s+-->.*<!--\s+END\s+\2\s+-->)@sm', Shop::setJsCart($regs[0][2]), $page_content);
        }
        if (preg_match_all('@<!--\s+BEGIN\s+(shopJsCart)\s+-->(.*)<!--\s+END\s+\1\s+-->@sm', $page_template, $regs, PREG_SET_ORDER)) {
            $page_template = preg_replace('@(<!--\s+BEGIN\s+(shopJsCart)\s+-->.*<!--\s+END\s+\2\s+-->)@sm', Shop::setJsCart($regs[0][2]), $page_template);
        }
    }
}

//-------------------------------------------------------
// get voting
//-------------------------------------------------------
$modulespath = "modules/voting/index.class.php";
if (file_exists($modulespath)) {
	require_once($modulespath);
	$_ARRAYLANG = array_merge($_ARRAYLANG, $objInit->loadLanguageData('voting'));
//
//	if ($objTemplate->blockExists('voting_result')) {
//		$objTemplate->_blocks['voting_result'] = setVotingResult($objTemplate->_blocks['voting_result']);
//	}
//
	if (preg_match_all('@<!--\s+BEGIN\s+(voting_result)\s+-->(.*)<!--\s+END\s+\1\s+-->@sm', $themesPages['sidebar'], $regs, PREG_SET_ORDER)) {
		$themesPages['sidebar'] = preg_replace('@(<!--\s+BEGIN\s+(voting_result)\s+-->.*<!--\s+END\s+\2\s+-->)@sm', setVotingResult($regs[0][2]), $themesPages['sidebar']);
	}
	if (preg_match_all('@<!--\s+BEGIN\s+(voting_result)\s+-->(.*)<!--\s+END\s+\1\s+-->@sm', $themesPages['index'], $regs, PREG_SET_ORDER)) {
		$themesPages['index'] = preg_replace('@(<!--\s+BEGIN\s+(voting_result)\s+-->.*<!--\s+END\s+\2\s+-->)@sm', setVotingResult($regs[0][2]), $themesPages['index']);
	}
	if (preg_match_all('@<!--\s+BEGIN\s+(voting_result)\s+-->(.*)<!--\s+END\s+\1\s+-->@sm', $page_content, $regs, PREG_SET_ORDER)) {
		$page_content = preg_replace('@(<!--\s+BEGIN\s+(voting_result)\s+-->.*<!--\s+END\s+\2\s+-->)@sm', setVotingResult($regs[0][2]), $page_content);
	}
	if (preg_match_all('@<!--\s+BEGIN\s+(voting_result)\s+-->(.*)<!--\s+END\s+\1\s+-->@sm', $page_template, $regs, PREG_SET_ORDER)) {
		$page_template = preg_replace('@(<!--\s+BEGIN\s+(voting_result)\s+-->.*<!--\s+END\s+\2\s+-->)@sm', setVotingResult($regs[0][2]), $page_template);
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

$page_content = str_replace('{PAGE_URL}',  $objInit->getPageUri(), $page_content);
$page_content = str_replace('{TITLE}',  $page_title, $page_content);

//-------------------------------------------------------
// start module switches
//-------------------------------------------------------
switch ($section) {

//-------------------------------------------------------
// Login module
//-------------------------------------------------------

    case "login":
        $modulespath = "core_modules/login/index.class.php";
        /**
         * @ignore
         */
        if (file_exists($modulespath)) require_once($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        if (!isset($sessionObj) || !is_object($sessionObj)) $sessionObj=&new cmsSession();
        if (!isset($objAuth) || !is_object($objAuth)) $objAuth = &new Auth($type='frontend');
        if (!isset($objPerm) || !is_object($objPerm)) $objPerm =&new Permission($type='frontend');
        $objLogin = &new Login($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $objLogin->getContent());
    break;

//-------------------------------------------------------
// Nettools
//-------------------------------------------------------
    case "nettools":
        $modulespath = "core_modules/nettools/index.class.php";
        /**
         * @ignore
         */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objNetTools = &new NetTools($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $objNetTools->getPage());
    break;


//-------------------------------------------------------
// eCommerce Module
//-------------------------------------------------------
    case "shop":
        $modulespath = "modules/shop/index.class.php";
        /**
         * @ignore
         */
        if (file_exists($modulespath)) require_once($modulespath);
        else die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        if (shopUseSession() && (!isset($sessionObj) || !is_object($sessionObj))) $sessionObj = new cmsSession();
        $shopObj = new Shop($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $shopObj->getShopPage());
        $objTemplate->setVariable('SHOPNAVBAR_FILE', $shopObj->getShopNavbar($themesPages['shopnavbar']));
        $boolShop = true;
    break;

//-------------------------------------------------------
// Community module
//-------------------------------------------------------
    case "community":
        $modulespath = "modules/community/index.class.php";
        /**
         * @ignore
         */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        if (!isset($sessionObj) || !is_object($sessionObj)) $sessionObj=&new cmsSession();
        if (!isset($objAuth) || !is_object($objAuth)) $objAuth = &new Auth($type = 'frontend');
        $communityObj = &new Community($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $communityObj->getCommunityPage());
    break;

//-------------------------------------------------------
// News module
//-------------------------------------------------------
    case "news":
        $modulespath = "core_modules/news/index.class.php";
        /**
         * @ignore
         */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        if (isset($_GET['cmd']) && $_GET['cmd'] == "submit") {
            if (!isset($sessionObj) || !is_object($sessionObj)) $sessionObj=&new cmsSession();
            $sessionObj->cmsSessionStatusUpdate($status="frontend");
            if (!isset($objAuth) || !is_object($objAuth)) $objAuth = &new Auth($type = 'frontend');
            if (!isset($objPerm) || !is_object($objPerm)) $objPerm = &new Permission();
            /**
             * @ignore
             */
            require_once ASCMS_CORE_PATH.'/wysiwyg.class.php';
        }

        $newsObj= &new news($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $newsObj->getNewsPage());
        $newsObj->getPageTitle($page_title);
        $page_title = $newsObj->newsTitle;
    break;

//-------------------------------------------------------
// Livecam
//-------------------------------------------------------
    case "livecam":
        $modulespath = "modules/livecam/index.class.php";
        /**
         * @ignore
         */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objLivecam = &new Livecam($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $objLivecam->getPage());
    break;

//-------------------------------------------------------
// Guestbook
//-------------------------------------------------------
    case "guestbook":
        $modulespath = "modules/guestbook/index.class.php";
        /**
         * @ignore
         */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objGuestbook = &new Guestbook($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $objGuestbook->getPage());
    break;

//-------------------------------------------------------
// Memberdir
//-------------------------------------------------------
    case "memberdir":
        $modulespath = "modules/memberdir/index.class.php";
        /**
         * @ignore
         */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objMemberDir = &new memberDir($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $objMemberDir->getPage());
    break;


//-------------------------------------------------------
// Download
//-------------------------------------------------------
    case "download":
        $modulespath = "modules/download/index.class.php";
        /**
         * @ignore
         */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objDownload = &new Download($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $objDownload->getPage());
    break;

//-------------------------------------------------------
// Recommend
//-------------------------------------------------------
    case "recommend":
        $modulespath = "modules/recommend/index.class.php";
        /**
         * @ignore
         */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objRecommend = &new Recommend($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $objRecommend->getPage());
    break;

//-------------------------------------------------------
// DocumentSystem module
//-------------------------------------------------------
    case "docsys":
        $modulespath = "modules/docsys/index.class.php";
        /**
         * @ignore
         */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $docSysObj= &new docSys($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $docSysObj->getDocSysPage());
        $docSysObj->getPageTitle($page_title);
        $page_title = $docSysObj->docSysTitle;
    break;

//-------------------------------------------------------
// Search Module
//-------------------------------------------------------
    case "search":
        $modulespath = "core_modules/search/index.class.php";
        /**
         * @ignore
         */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $pos = (isset($_GET['pos'])) ? intval($_GET['pos']) : "";
        $objTemplate->setVariable('CONTENT_TEXT', search_getSearchPage($pos, $page_content));
        unset($pos);
    break;

//-------------------------------------------------------
// Contact Module
//-------------------------------------------------------
    case "contact":
        $modulespath = "core_modules/contact/index.class.php";
        /**
         * @ignore
         */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $contactObj= &new Contact($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $contactObj->getContactPage());
        $moduleStyleFile = "core_modules/contact/frontend_style.css";
    break;

//-------------------------------------------------------
// Sitemap Core
//-------------------------------------------------------
    case "ids":
        $objTemplate->setVariable('CONTENT_TEXT', $page_content);
        break;

//-------------------------------------------------------
// Sitemapping
//-------------------------------------------------------
    case "sitemap":
        $modulespath = "core_modules/sitemap/index.class.php";
        /**
         * @ignore
         */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $sitemap = &new sitemap($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $sitemap->getSitemapContent());
    break;

//-------------------------------------------------------
// media Core
//-------------------------------------------------------
    case "media1":
    case "media2":
    case "media3":
    case "media4":
        if(!isset($sessionObj)|| !is_object($sessionObj)) $sessionObj = &new cmsSession();
        $modulespath = ASCMS_CORE_MODULE_PATH . '/media/index.class.php';
        /**
         * @ignore
         */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objMedia = &new MediaManager($page_content, $section);
        $objTemplate->setVariable('CONTENT_TEXT', $objMedia->getMediaPage());
    break;

//-------------------------------------------------------
// newsletter Module
//-------------------------------------------------------
    case "newsletter":
        $modulespath = "modules/newsletter/index.class.php";
        /**
         * @ignore
         */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $newsletter = &new newsletter($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $newsletter->getPage());
    break;

//-------------------------------------------------------
// gallery Module
//-------------------------------------------------------
    case "gallery":
        $modulespath = "modules/gallery/index.class.php";
        /**
         * @ignore
         */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objGallery = &new Gallery($page_content);
        $objTemplate->setVariable("CONTENT_TEXT", $objGallery->getPage());

        // Optional change: Show gallery name instead of page title
        //$topGalleryName = $objGallery->getTopGalleryName();
        //if ($topGalleryName) {
        //    $page_title = $topGalleryName;
        //}

    break;

//-------------------------------------------------------
// Voting
//-------------------------------------------------------
    case "voting":
        $modulespath = "modules/voting/index.class.php";
        /**
         * @ignore
         */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objTemplate->setVariable("CONTENT_TEXT", votingShowCurrent($page_content));
    break;

//-------------------------------------------------------
// News Feed Module
//-------------------------------------------------------
    case "feed":
        $modulespath = "modules/feed/index.class.php";
        /**
         * @ignore
         */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objFeed = &new feed($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $objFeed->getFeedPage());
    break;

//-------------------------------------------------------
// immo Module
//-------------------------------------------------------
    case "immo":
        $modulespath = "modules/immo/index.class.php";
        /**
         * @ignore
         */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objImmo = &new Immo($page_content);
        $objTemplate->setVariable("CONTENT_TEXT", $objImmo->getPage());
        if(!empty($_GET['cmd']) && $_GET['cmd'] == 'showObj'){
            $page_title = $objImmo->getPageTitle($page_title);
        }
    break;

//-------------------------------------------------------
// Calendar Module
//-------------------------------------------------------
    case "calendar":
        $modulespath = "modules/calendar/index.class.php";
        /**
         * @ignore
         */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objCalendar = &new Calendar($page_content);
        $objTemplate->setVariable("CONTENT_TEXT", $objCalendar->getCalendarPage());
        $moduleStyleFile = "modules/calendar/frontend_style.css";
    break;

//-------------------------------------------------------
// Reservation Module
//-------------------------------------------------------
    case "reservation":
    $modulespath = "modules/reservation/index.class.php";
    /**
     * @ignore
     */
    if (file_exists($modulespath)) require_once($modulespath);
    else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
    $objReservationModule = &new reservations($page_content);
    $objTemplate->setVariable('CONTENT_TEXT', $objReservationModule->getPage());
    $moduleStyleFile = "modules/reservation/frontend_style.css";
break;

//-------------------------------------------------------
// Directory Module
//-------------------------------------------------------
  case "directory":
        $modulespath = "modules/directory/index.class.php";
        /**
         * @ignore
         */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);

        if (!isset($sessionObj) || !is_object($sessionObj)) $sessionObj=&new cmsSession();
        $sessionObj->cmsSessionStatusUpdate($status="frontend");
        if (!isset($objAuth) || !is_object($objAuth)) $objAuth = &new Auth($type = 'frontend');
        if (!isset($objPerm) || !is_object($objPerm)) $objPerm = &new Permission();

        $directory = &new rssDirectory($page_content);
        $objTemplate->setVariable("CONTENT_TEXT", $directory->getPage());

        $page_metatitle = $directory->getPageTitle();

        break;

//-------------------------------------------------------
// Market Module
//-------------------------------------------------------
  case "market":
        $modulespath = "modules/market/index.class.php";
        /**
         * @ignore
         */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);

        if (!isset($sessionObj) || !is_object($sessionObj)) $sessionObj=&new cmsSession();
        $sessionObj->cmsSessionStatusUpdate($status="frontend");
        if (!isset($objAuth) || !is_object($objAuth)) $objAuth = &new Auth($type = 'frontend');
        if (!isset($objPerm) || !is_object($objPerm)) $objPerm = &new Permission();

        $market = &new Market($page_content);
        $objTemplate->setVariable("CONTENT_TEXT", $market->getPage());
        break;

//-------------------------------------------------------
// Podcast Module
//-------------------------------------------------------
  case "podcast":
        $modulespath = "modules/podcast/index.class.php";
        /**
         * @ignore
         */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objPodcast = &new podcast($page_content);
        $objTemplate->setVariable("CONTENT_TEXT", $objPodcast->getPage());
        break;

//-------------------------------------------------------
// Forum Module
//-------------------------------------------------------
    case "forum":
        $modulespath = "modules/forum/index.class.php";
        /**
         * @ignore
         */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        if (!isset($sessionObj) || !is_object($sessionObj)) $sessionObj = &new cmsSession();
        if (!isset($objPerm) || !is_object($objPerm)) $objPerm = &new Permission($type='frontend');
        $objForum = &new Forum($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $objForum->getPage());
//        $moduleStyleFile = "modules/forum/css/frontend_style.css";
    break;

//          $sessionObj=&new cmsSession();
//        $modulespath = "modules/forum/index.class.php";
//        /**
//         * @ignore
//         */
//        if (file_exists($modulespath))require_once($modulespath);
//        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
//        $forum = &new forum($page_content);
//        if ($_REQUEST['forumLogin']) {
//            if ($_SESSION['forum']['userstatus']!=1) {
//                if (!$forum->checklogin()){
//                    $link=base64_encode($_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);
//                    header ("Location: ?section=login&redirect=".$link);
//                } else {
//                    $_SESSION['forum']['userstatus']=1;
//                }
//            }
//        }
//        $objTemplate->setVariable('CONTENT_TEXT', $forum->getPage());
//    break;

//-------------------------------------------------------
// logout
//-------------------------------------------------------
    case "logout":
        $sessionObj=&new cmsSession();
        $objAuth =&new Auth($type='public');
        $objAuth->logout();
    break;

//-------------------------------------------------------
// error module
//-------------------------------------------------------
    case "error":
        $modulespath = "core/error.class.php";
        /**
         * @ignore
         */
        if (file_exists($modulespath)) require($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $errorObj= &new error($page_content);
        $objTemplate->setVariable('CONTENT_TEXT', $errorObj->getErrorPage());
    break;

//-------------------------------------------------------
// E-Government Module
//-------------------------------------------------------
  case "egov":
        $modulespath = "modules/egov/index.class.php";
        /**
         * @ignore
         */
        if (file_exists($modulespath)) require_once($modulespath);
        else die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
        $objEgov = &new eGov($page_content);
        $objTemplate->setVariable("CONTENT_TEXT", $objEgov->getPage());
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
if (isset($_CONFIGURATION['custom']['shopnavbar']) AND $_CONFIGURATION['custom']['shopnavbar'] == TRUE) {
    if (!is_object($shopObj)){
        $modulespath = "modules/shop/index.class.php";
        if (file_exists($modulespath)){
            /**
             * @ignore
             */
            require_once($modulespath);
            if (!is_object($sessionObj)) $sessionObj=&new cmsSession();
            $_ARRAYSHOPLANG = $objInit->loadLanguageData('shop');
            $_ARRAYLANG = array_merge($_ARRAYLANG, $_ARRAYSHOPLANG);
            $boolShop = true;
            $shopObj = new Shop();
            $objTemplate->setVariable('SHOPNAVBAR_FILE', $shopObj->getShopNavbar($themesPages['shopnavbar']));
        }
    }
}

//-------------------------------------------------------
// Calendar
//-------------------------------------------------------
// print_r($objTemplate->getPlaceholderList());
/*
$calendarCheck1 = $objTemplate->placeholderExists('CALENDAR');
$calendarCheck2 = $objTemplate->placeholderExists('CALENDAR_EVENTS');
if(!empty($calendarCheck1) OR !empty($calendarCheck2)) {
    $modulespath = "modules/calendar/HomeCalendar.class.php";
    if (file_exists($modulespath)){
        /**
         * @ignore
         */
/*
        require_once($modulespath);
        $objHomeCalendar = &new HomeCalendar();
        if(!empty($calendarCheck1)) {
            $objTemplate->setVariable('CALENDAR', $objHomeCalendar->getHomeCalendar());
        }
        if(!empty($calendarCheck2)) {
            $objTemplate->setVariable('CALENDAR_EVENTS', $objHomeCalendar->getHomeCalendarEvents());
        }
    }
}
*/


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
    $modulespath = "modules/directory/index.class.php";
    if (file_exists($modulespath)){
        /**
         * @ignore
         */
        require_once($modulespath);
        $objDirectory = &new rssDirectory('');
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
    $modulespath = "modules/market/index.class.php";
    if (file_exists($modulespath)){
        /**
         * @ignore
         */
        require_once($modulespath);
        $objMarket = &new Market('');
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
    $modulespath = "core_modules/banner/index.class.php";
    if (file_exists($modulespath)) {
        /**
         * @ignore
         */
        include_once($modulespath);
        $objBanner = &new Banner();

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
// set global template variables
//-------------------------------------------------------

if(!isset($loginStatus)) $loginStatus='';

$objTemplate->setVariable(array(
    'CHARSET'                => $objInit->getFrontendLangCharset(),
    'TITLE'                 => $page_title,
    'METATITLE'                => $page_metatitle,
    'NAVTITLE'                => $page_catname,
    'GLOBAL_TITLE'            => $_CONFIG['coreGlobalPageTitle'],
    'DOMAIN_URL'            => $_CONFIG['domainUrl'],
    'METAKEYS'                => $page_keywords,
    'METADESC'                => $page_desc,
    'METAROBOTS'            => $page_robots,
    'CONTENT_TITLE'               => $page_title,
    'CSS_NAME'              => $pageCssName,
    'PRINT_URL'             => $objInit->getPrintUri(),
    'PAGE_URL'              => $objInit->getPageUri(),
    'CURRENT_URL'             => $objInit->getCurrentPageUri(),
    'DATE'                    => showFormattedDate(),
    'NAVTREE'                => $objNavbar->getTrail(),
    'SUBNAVBAR_FILE'          => $objNavbar->getNavigation($themesPages['subnavbar'],$boolShop),
    'NAVBAR_FILE'              => $objNavbar->getNavigation($themesPages['navbar'],$boolShop),
    'ONLINE_USERS'            => $objCounter->getOnlineUsers(),
    'VISITOR_NUMBER'        => $objCounter->getVisitorNumber(),
    'COUNTER'                => $objCounter->getCounterTag(),
    'BANNER'                => isset($objBanner) ? $objBanner->getBannerJS() : '',
    'VERSION'                   => $_CONFIG['coreCmsName'],
    'LANGUAGE_NAVBAR'        => $objNavbar->getFrontendLangNavigation(),
    'ACTIVE_LANGUAGE_NAME'  => $objInit->getFrontendLangName(),
    'LOGGING_STATUS'        => $loginStatus,
    'RANDOM'                => md5(microtime()),
    'TXT_SEARCH'            => $_CORELANG['TXT_SEARCH']
));

//-------------------------------------------------------
// parse system
//-------------------------------------------------------
$parsingtime = explode(' ', microtime());
$time = round(((float)$parsingtime[0] + (float)$parsingtime[1]) - ((float)$starttime[0] + (float)$starttime[1]), 5);
$objTemplate->setVariable('PARSING_TIME', $time);

//Allow PRINT_URL in sidebar
$themesPages['sidebar'] = str_replace('{PRINT_URL}',$objInit->getPrintUri(), $themesPages['sidebar']);

$objTemplate->setVariable(array(
    'SIDEBAR_FILE'     => $themesPages['sidebar'],
    'JAVASCRIPT_FILE'  => $themesPages['javascript'],
    'BUILDIN_STYLE_FILE'  => $themesPages['buildin_style']
));

if (!empty($moduleStyleFile)) {
    $objTemplate->setVariable(array(
        'STYLE_FILE' => "<link rel=\"stylesheet\" href=\"$moduleStyleFile\" type=\"text/css\" media=\"screen, projection\" />"
    ));
}

$objTemplate->show();
$objCache->endCache();
?>
