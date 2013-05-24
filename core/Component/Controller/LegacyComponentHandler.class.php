<?php

/*
 * This file is the global trash.
 * @todo: empty trash
 */

namespace Cx\Core\Component\Controller;

/**
 * This handles exceptions for new Component structure. This is old code
 * and should be replaced so that this class becomes unnecessary
 * @todo: Remove this code (move all exceptions to components)
 * @author Michael Ritter <michael.ritter@comvation.com>
 */
class LegacyComponentHandler {
    private $exceptions;
    
    public function hasExceptionFor($frontend, $action, $componentName) {
        if (!isset($this->exceptions[$frontend ? 'frontend' : 'backend'][$action])) {
            return false;
        }
        return isset($this->exceptions[$frontend ? 'frontend' : 'backend'][$action][$componentName]);
    }
    
    public function executeException($frontend, $action, $componentName) {
        if (!$this->hasExceptionFor($frontend, $action, $componentName)) {
            return false;
        }
        return $this->exceptions[$frontend ? 'frontend' : 'backend'][$action][$componentName]();
    }
    
    public function __construct() {
        // now follows the loooooooooooong list of old code:
        $this->exceptions = array(
            'frontend' => array(
                'preResolve' => array(
                    'License' => function() {
                        global $license, $_CONFIG, $objDatabase;

                        // make sure license data is up to date (this updates active available modules)
                        // @todo move to core_module license
                        $license = \Cx\Core_Modules\License\License::getCached($_CONFIG, $objDatabase);
                        $oldState = $license->getState();
                        $license->check();
                        if ($oldState != $license->getState()) {
                            $license->save(new \settingsManager(), $objDatabase);
                        }
                        if ($license->isFrontendLocked()) {
                            print file_get_contents(ASCMS_DOCUMENT_ROOT.'/offline.html');
                            die(1);
                        }
                    },
                    'Resolver' => function() {
                        global $request, $url, $resolver, $aliaspage, $_LANGID, $redirectToCorrectLanguageDir, $_CONFIG, $objInit, $extractedLanguage;
                        
                        $request = !empty($_GET['__cap']) ? $_GET['__cap'] : '';
                        $url = \Cx\Core\Routing\Url::fromCapturedRequest($request, ASCMS_INSTANCE_OFFSET, $_GET);
                        $resolver = new \Cx\Core\Routing\Resolver($url, null, \Env::em(), null, null);
                        \Env::set('Resolver', $resolver);
                        $aliaspage = $resolver->resolveAlias();

                        $_LANGID = '';
                        $redirectToCorrectLanguageDir = function() use ($url, &$_LANGID, $_CONFIG) {
                            $url->setLangDir(\FWLanguage::getLanguageCodeById($_LANGID));

                            \CSRF::header('Location: '.$url);
                            exit;
                        };

                        if ($aliaspage != null) {
                            $_LANGID = $aliaspage->getTargetLangId();
                        } else {
                            /**
                            * Frontend language ID
                            * @global integer $_LANGID
                            * @todo    Globally replace this with either the FRONTEND_LANG_ID, or LANG_ID constant
                            */
                            $_LANGID = $objInit->getFallbackFrontendLangId();

                            //try to find the language in the url
                            $extractedLanguage = 0;

                            $extractedLanguage = \FWLanguage::getLanguageIdByCode($url->getLangDir());
                            if (!$extractedLanguage) {
                                $redirectToCorrectLanguageDir();
                            }
                            //only set langid according to url if the user has not explicitly requested a language change.
                            if(!isset($_REQUEST['setLang'])) {
                                $_LANGID = $extractedLanguage;
                            }
                            else if($_LANGID != $extractedLanguage) { //the user wants to change the language, but we're still inside the wrong language directory.
                                $redirectToCorrectLanguageDir();
                            }
                        }
                    },
                    'Security' => function() {
                        global $objSecurity;

                        // Webapp Intrusion Detection System
                        $objSecurity = new \Security;
                        $_GET = $objSecurity->detectIntrusion($_GET);
                        $_POST = $objSecurity->detectIntrusion($_POST);
                        $_COOKIE = $objSecurity->detectIntrusion($_COOKIE);
                        $_REQUEST = $objSecurity->detectIntrusion($_REQUEST);
                    },
                ),
                'postResolve' => array(
                    'License' => function() {
                        global $license, $_LANGID, $redirectToCorrectLanguageDir;

                        if (!$license->isInLegalComponents('fulllanguage') && $_LANGID != \FWLanguage::getDefaultLangId()) {
                            $_LANGID = \FWLanguage::getDefaultLangId();
                            $redirectToCorrectLanguageDir();
                        }
                    },
                    'Cache' => function() {
                        global $objCache;

                        // Caching-System
                        /**
                        * Include the cache module.  The cache is initialized right afterwards.
                        */
                        $objCache = new \Cache();
                        $objCache->startCache();
                    },
                    'Resolver' => function() {
                        global $section, $command, $page, $history, $sessionObj, $resolver, $url, $_CORELANG,
                                $page, $pageAccessId, $page_protected, $page_redirect, $pageId, $themesPages,
                                $page_content, $page_template, $page_title, $page_metatitle, $page_catname,
                                $page_keywords, $page_desc, $page_robots, $pageCssName, $page_modified,
                                $isRegularPageRequest, $now, $start, $end, $logRepo, $objInit, $plainSection,
                                $license, $_CONFIG;

                        $section = isset($_REQUEST['section']) ? $_REQUEST['section'] : '';
                        $command = isset($_REQUEST['cmd']) ? contrexx_addslashes($_REQUEST['cmd']) : '';
                        $page    = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 0;
                        $history = isset($_REQUEST['history']) ? intval($_REQUEST['history']) : 0;


                        // Initialize page meta
                        $page = null;
                        $pageAccessId = 0;
                        $page_protected = 0;
                        $page_protected = $page_redirect = $pageId = $themesPages =
                        $page_content = $page_template = $page_title = $page_metatitle =
                        $page_catname = $page_keywords = $page_desc = $page_robots =
                        $pageCssName = $page_modified = null;

                        function setModuleIndexAndReturnPlainSection($section) {
                            // To clone any module, use an optional integer cmd suffix.
                            // E.g.: "shop2", "gallery5", etc.
                            // Mind that you *MUST* copy all necessary database tables, and fix any
                            // references to your module (section and cmd parameters, database tables)
                            // using the MODULE_INDEX constant in the right place both in your code
                            // *AND* templates!
                            // See the Shop module for an example.
                            $arrMatch = array();
                            if (preg_match('/^(\D+)(\d+)$/', $section, $arrMatch)) {
                                // The plain section/module name, used below
                                $plainSection = $arrMatch[1];
                            } else {
                                $plainSection = $section;
                            }
                            // The module index.
                            // An empty or 1 (one) index represents the same (default) module,
                            // values 2 (two) and larger represent distinct instances.
                            $moduleIndex = (empty($arrMatch[2]) || $arrMatch[2] == 1 ? '' : $arrMatch[2]);
                            define('MODULE_INDEX', $moduleIndex);

                            return $plainSection;
                        }

                        // If standalone is set, then we will not have to initialize/load any content page related stuff
                        $isRegularPageRequest = !isset($_REQUEST['standalone']) || $_REQUEST['standalone'] == 'false';


                        // Regular page request
                        if ($isRegularPageRequest) {
                        // TODO: history (empty($history) ? )
                            if (isset($_GET['pagePreview']) && $_GET['pagePreview'] == 1 && empty($sessionObj)) {
                                $sessionObj = new cmsSession();
                            }
                            $resolver->init($url, FRONTEND_LANG_ID, \Env::em(), ASCMS_INSTANCE_OFFSET.\Env::get('virtualLanguageDirectory'), \FWLanguage::getFallbackLanguageArray());
                            try {
                                $resolver->resolve();
                                $page = $resolver->getPage();
                        // TODO: should this check (for type 'application') moved to \Cx\Core\ContentManager\Model\Entity\Page::getCmd()|getModule() ?
                                // only set $section and $command if the requested page is an application
                                $command = $resolver->getCmd();
                                $section = $resolver->getSection();
                            }
                            catch (\Cx\Core\Routing\ResolverException $e) {
                                try {
                                    $resolver->legacyResolve($url, $section, $command);
                                    $page = $resolver->getPage();
                                    $command = $resolver->getCmd();
                                    $section = $resolver->getSection();
                                } catch(\Cx\Core\Routing\ResolverException $e) {
                                    // legacy resolving also failed.
                                    // provoke a 404
                                    $page = null;
                                }
                            }

                            if(!$page || !$page->isActive() ||
                                    (!empty($section) && !$license->isInLegalFrontendComponents($section))) {
                                //fallback for inexistant error page
                                if($section == 'error') {
                                    // If the error module is not installed, show this
                                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                                }
                                else {
                                    //page not found, redirect to error page.
                                    header('Location: '.\Cx\Core\Routing\Url::fromModuleAndCmd('error'));
                                    exit;
                                }
                            }

                        // TODO: question: what do we need this for? I think there is no need for this (had been added in r15026)
                            //legacy: re-populate cmd and section into $_GET
                            $_GET['cmd'] = $command;
                            $_GET['section'] = $section;
                        // END of TODO question

                            //check whether the page is active
                            $now = new \DateTime('now');
                            $start = $page->getStart();
                            $end = $page->getEnd();

                            $pageId = $page->getId();

                            //access: frontend access id for default requests
                            $pageAccessId = $page->getFrontendAccessId();
                            //revert the page if a history param has been given
                            if($history) {
                                //access: backend access id for history requests
                                $pageAccessId = $page->getBackendAccessId();
                                $logRepo = \Env::em()->getRepository('Cx\Core\ContentManager\Model\Entity\LogEntry');
                                try {
                                    $logRepo->revert($page, $history);
                                }
                                catch(\Gedmo\Exception\UnexpectedValueException $e) {
                                }

                                $logRepo->revert($page, $history);
                            }
                            /*
                            //404 for inactive pages
                            if(($start > $now && $start != null) || ($now > $end && $end != null)) {
                                if ($section == 'error') {
                                    // If the error module is not installed, show this
                                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                                }
                                CSRF::header('Location: index.php?section=error&id=404');
                                exit;
                                }*/


                            $objInit->setCustomizedTheme($page->getSkin(), $page->getCustomContent());

                            $themesPages = $objInit->getTemplates();

                            //replace the {NODE_<ID>_<LANG>}- placeholders
                            \LinkGenerator::parseTemplate($themesPages);

                            // Frontend Editing: content has to be replaced with preview code if needed.
                            $page_content = $page->getContent();

                            $page_catname = contrexx_raw2xhtml($page->getTitle());

                            $page_title     = contrexx_raw2xhtml($page->getContentTitle());
                            $page_metatitle = contrexx_raw2xhtml($page->getMetatitle());
                            $page_keywords  = contrexx_raw2xhtml($page->getMetakeys());
                            $page_robots    = contrexx_raw2xhtml($page->getMetarobots());
                            $pageCssName    = $page->getCssName();
                            $page_desc      = contrexx_raw2xhtml($page->getMetadesc());
                        //TODO: analyze those, take action.
                            //$page_redirect  = $objResult->fields['redirect'];
                            //$page_protected = $objResult->fields['protected'];
                            $page_protected = $page->isFrontendProtected();

                            //$page_access_id = $objResult->fields['frontend_access_id'];
                            $page_template  = $themesPages['content'];
                            $page_modified  = $page->getUpdatedAt()->getTimestamp();

                        //TODO: history
                        }

                        // TODO: refactor system to be able to remove this backward compatibility
                        // Backwards compatibility for code pre Contrexx 3.0 (update)
                        $_GET['cmd']     = $_POST['cmd']     = $_REQUEST['cmd']     = $command;
                        $_GET['section'] = $_POST['section'] = $_REQUEST['section'] = $section;


                        $plainSection = setModuleIndexAndReturnPlainSection($section);

                        // Authentification for protected pages
                        $resolver->checkPageFrontendProtection($page, $history);

                        // Start page or default page for no section
                        if ($section == 'home') {
                            if (!$objInit->hasCustomContent()){
                                $page_template = $themesPages['home'];
                            } else {
                                $page_template = $themesPages['content'];
                            }
                        }
                    },
                ),
                'preContentLoad' => array(
                    'Uploader' => function() {
                        global $section, $sessionObj, $cl, $_CORELANG, $objUploadModule;

                        if ($section == 'upload') {//handle uploads separately, since they have no content
                            $sessionObj = new cmsSession();
                            if (!$cl->loadFile(ASCMS_CORE_MODULE_PATH.'/upload/index.class.php'))
                                die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                            $objUploadModule = new Upload();
                            $objUploadModule->getPage();
                            //execution never reaches this point
                        }
                    },
                    'Captcha' => function() {
                        global $section;

                        if ($section == 'captcha') {
                            /*
                            * Captcha Module
                            *
                            * Generates no output, requests are answered by a die()
                            * @since   2.1.5
                            */
                            FWCaptcha::getInstance()->getPage();
                        }
                    },
                    'JsonData' => function() {
                        global $section, $json, $adapter, $method, $arguments;

                        if ($section == 'jsondata') {
                        // TODO: move this code to /core/Json/...
                        // TODO: handle expired sessions in any xhr callers.
                            $json = new \Cx\Core\Json\JsonData();
                        // TODO: Verify that the arguments are actually present!
                            $adapter = contrexx_input2raw($_GET['object']);
                            $method = contrexx_input2raw($_GET['act']);
                        // TODO: Replace arguments by something reasonable
                            $arguments = array('get' => $_GET, 'post' => $_POST);
                            echo $json->jsondata($adapter, $method, $arguments);
                            die();
                        }
                    },
                    'Newsletter' => function() {
                        global $section, $newsletter, $isRegularPageRequest, $plainSection, $cl, $_CORELANG,
                                $newsletter, $_ARRAYLANG, $page_content, $page_template, $themesPages, $objInit;

                        if ($section == "newsletter" && Newsletter::isTrackLink()) {//handle link tracker from newsletter, since user should be redirected to the link url
                            /*
                            * Newsletter Module
                            *
                            * Generates no output, requests are answered by a redirect to foreign site
                            *
                            */
                            $newsletter = new Newsletter();
                            $newsletter->trackLink();
                            //execution should never reach this point
                        }

                        if (!$isRegularPageRequest) {
                            // ATTENTION: These requests are not protected by the content manager
                            //            and must therefore be authorized by the calling component itself!
                            switch ($plainSection) {
                                case 'newsletter':
                                    /** @ignore */
                                    if (!$cl->loadFile(ASCMS_MODULE_PATH.'/newsletter/index.class.php'))
                                        die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                                    $newsletter = new newsletter();
                                    $newsletter->getPage();
                                    exit;
                                    break;
                            }
                        }

                        // get Newsletter
                        /** @ignore */
                        if ($cl->loadFile(ASCMS_MODULE_PATH.'/newsletter/index.class.php')) {
                            $_ARRAYLANG = array_merge($_ARRAYLANG, $objInit->loadLanguageData('newsletter'));
                            $newsletter = new \newsletter('');
                            if (preg_match('/{NEWSLETTER_BLOCK}/', $page_content)) {
                                $newsletter->setBlock($page_content);
                            }
                            if (preg_match('/{NEWSLETTER_BLOCK}/', $page_template)) {
                                $newsletter->setBlock($page_template);
                            }
                            if (preg_match('/{NEWSLETTER_BLOCK}/', $themesPages['index'])) {
                                $newsletter->setBlock($themesPages['index']);
                            }
                        }
                    },
                    'Immo' => function() {
                        global $isRegularPageRequest, $plainSection, $cl, $_CORELANG, $objImmo, $modulespath,
                                $immoHeadlines, $themesPages, $immoHomeHeadlines, $page_content, $page_template;

                        if (!$isRegularPageRequest) {
                            // ATTENTION: These requests are not protected by the content manager
                            //            and must therefore be authorized by the calling component itself!
                            switch ($plainSection) {
                                case 'immo':
                                    /** @ignore */
                                    if (!$cl->loadFile(ASCMS_MODULE_PATH.'/immo/index.class.php'))
                                        die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                                    $objImmo = new Immo('');
                                    $objImmo->getPage();
                                    exit;
                                    break;
                            }
                        }

                        // Get immo headline
                        $modulespath = ASCMS_MODULE_PATH.'/immo/headlines/index.class.php';
                        if (file_exists($modulespath)) {
                            $immoHeadlines = new immoHeadlines($themesPages['immo']);
                            $immoHomeHeadlines = $immoHeadlines->getHeadlines();
                            $page_content = str_replace('{IMMO_FILE}', $immoHomeHeadlines, $page_content);
                            $themesPages['index'] = str_replace('{IMMO_FILE}', $immoHomeHeadlines, $themesPages['index']);
                            $page_template = str_replace('{IMMO_FILE}', $immoHomeHeadlines, $page_template);
                        }
                    },
                    'Stats' => function() {
                        global $objCounter;

                        // Initialize counter and track search engine robot
                        $objCounter = new \statsLibrary();
                        $objCounter->checkForSpider();
                    },
                    'Block' => function() {
                        global $_CONFIG, $cl, $page_content, $page, $themesPages, $page_template;

                        if ($_CONFIG['blockStatus'] == '1') {
                            /** @ignore */
                            if ($cl->loadFile(ASCMS_MODULE_PATH.'/block/index.class.php')) {
                                \block::setBlocks($page_content, $page);
                                \block::setBlocks($themesPages, $page);
                        // TODO: this call in unhappy, becase the content/home template already gets parsed just the line above
                                \block::setBlocks($page_template, $page);
                            }
                        }
                    },
                    'Data' => function() {
                        global $_CONFIG, $cl, $lang, $objInit, $dataBlocks, $lang, $page_content,
                                $dataBlocks, $themesPages, $page_template;

                        // make the replacements for the data module
                        if ($_CONFIG['dataUseModule'] && $cl->loadFile(ASCMS_MODULE_PATH.'/data/dataBlocks.class.php')) {
                            $lang = $objInit->loadLanguageData('data');
                            $dataBlocks = new \dataBlocks($lang);
                            $page_content = $dataBlocks->replace($page_content);
                            $themesPages = $dataBlocks->replace($themesPages);
                            $page_template = $dataBlocks->replace($page_template);
                        }
                    },
                    'Teasers' => function() {
                        global $_CONFIG, $page_content, $arrMatches, $cl, $objTeasers, $page_template,
                                $themesPages;

                        $arrMatches = array();
                        // Set news teasers
                        if ($_CONFIG['newsTeasersStatus'] == '1') {
                            // set news teasers in the content
                            if (preg_match_all('/{TEASERS_([0-9A-Z_-]+)}/', $page_content, $arrMatches)) {
                                /** @ignore */
                                if ($cl->loadFile(ASCMS_CORE_MODULE_PATH.'/news/lib/teasers.class.php')) {
                                    $objTeasers = new Teasers();
                                    $objTeasers->setTeaserFrames($arrMatches[1], $page_content);
                                }
                            }
                            // set news teasers in the page design
                            if (preg_match_all('/{TEASERS_([0-9A-Z_-]+)}/', $page_template, $arrMatches)) {
                                /** @ignore */
                                if ($cl->loadFile(ASCMS_CORE_MODULE_PATH.'/news/lib/teasers.class.php')) {
                                    $objTeasers = new Teasers();
                                    $objTeasers->setTeaserFrames($arrMatches[1], $page_template);
                                }
                            }
                            // set news teasers in the website design
                            if (preg_match_all('/{TEASERS_([0-9A-Z_-]+)}/', $themesPages['index'], $arrMatches)) {
                                /** @ignore */
                                if ($cl->loadFile(ASCMS_CORE_MODULE_PATH.'/news/lib/teasers.class.php')) {
                                    $objTeasers = new Teasers();
                                    $objTeasers->setTeaserFrames($arrMatches[1], $themesPages['index']);
                                }
                            }
                        }
                    },
                    'Downloads' => function() {
                        global $page_content, $arrMatches, $cl, $objDownloadLib, $downloadBlock, $matches,
                                $objDownloadsModule;

                        // Set download groups
                        if (preg_match_all('/{DOWNLOADS_GROUP_([0-9]+)}/', $page_content, $arrMatches)) {
                            /** @ignore */
                            if ($cl->loadFile(ASCMS_MODULE_PATH.'/downloads/lib/downloadsLib.class.php')) {
                                $objDownloadLib = new DownloadsLibrary();
                                $objDownloadLib->setGroups($arrMatches[1], $page_content);
                            }
                        }

                        //--------------------------------------------------------
                        // Parse the download block 'downloads_category_#ID_list'
                        //--------------------------------------------------------
                        $downloadBlock = preg_replace_callback(
                            "/<!--\s+BEGIN\s+downloads_category_(\d+)_list\s+-->(.*)<!--\s+END\s+downloads_category_\g1_list\s+-->/s",
                            function($matches) {
                                if (isset($matches[0]) && $cl->loadFile(ASCMS_MODULE_PATH.'/downloads/index.class.php')) {
                                    $objDownloadsModule = new downloads($matches[0], array('category' => $matches[1]));
                                    return $objDownloadsModule->getPage();
                                }
                            },
                            $page_content);
                        $page_content = $downloadBlock;
                    },
                    'Feed' => function() {
                        global $_CONFIG, $objNewsML, $page_content, $arrMatches, $page_template, $themesPages;

                        // Set NewsML messages
                        if ($_CONFIG['feedNewsMLStatus'] == '1') {
                            if (preg_match_all('/{NEWSML_([0-9A-Z_-]+)}/', $page_content, $arrMatches)) {
                                /** @ignore */
                                if ($cl->loadFile(ASCMS_MODULE_PATH.'/feed/newsML.class.php')) {
                                    $objNewsML = new NewsML();
                                    $objNewsML->setNews($arrMatches[1], $page_content);
                                }
                            }
                            if (preg_match_all('/{NEWSML_([0-9A-Z_-]+)}/', $page_template, $arrMatches)) {
                                /** @ignore */
                                if ($cl->loadFile(ASCMS_MODULE_PATH.'/feed/newsML.class.php')) {
                                    $objNewsML = new NewsML();
                                    $objNewsML->setNews($arrMatches[1], $page_template);
                                }
                            }
                            if (preg_match_all('/{NEWSML_([0-9A-Z_-]+)}/', $themesPages['index'], $arrMatches)) {
                                /** @ignore */
                                if ($cl->loadFile(ASCMS_MODULE_PATH.'/feed/newsML.class.php')) {
                                    $objNewsML = new NewsML();
                                    $objNewsML->setNews($arrMatches[1], $themesPages['index']);
                                }
                            }
                        }
                    },
                    'Popup' => function() {
                        global $themesPages, $cl, $objPopup, $page;

                        // Set popups
                        if (preg_match('/{POPUP_JS_FUNCTION}/', $themesPages['index'])) {
                            /** @ignore */
                            if ($cl->loadFile(ASCMS_MODULE_PATH.'/popup/index.class.php')) {
                                $objPopup = new popup();
                                if (preg_match('/{POPUP}/', $themesPages['index'])) {
                                    $objPopup->setPopup($themesPages['index'], $page->getNode()->getId());
                                }
                                $objPopup->_setJS($themesPages['index']);
                            }
                        }
                    },
                    'News' => function() {
                        global $modulespath, $headlinesNewsPlaceholder, $page_content, $themesPages, $page_template,
                                $newsHeadlinesObj, $homeHeadlines, $topNewsPlaceholder, $homeTopNews;

                        // Get Headlines
                        $modulespath = ASCMS_CORE_MODULE_PATH.'/news/lib/headlines.class.php';
                        $headlinesNewsPlaceholder = '{HEADLINES_FILE}';
                        if (   file_exists($modulespath)
                            && (   strpos($page_content, $headlinesNewsPlaceholder) !== false
                                || strpos($themesPages['index'], $headlinesNewsPlaceholder) !== false
                                || strpos($themesPages['sidebar'], $headlinesNewsPlaceholder) !== false
                                || strpos($page_template, $headlinesNewsPlaceholder) !== false)
                        ) {
                            $newsHeadlinesObj = new \newsHeadlines($themesPages['headlines']);
                            $homeHeadlines = $newsHeadlinesObj->getHomeHeadlines();
                            $page_content           = str_replace($headlinesNewsPlaceholder, $homeHeadlines, $page_content);
                            $themesPages['index']   = str_replace($headlinesNewsPlaceholder, $homeHeadlines, $themesPages['index']);
                            $themesPages['sidebar'] = str_replace($headlinesNewsPlaceholder, $homeHeadlines, $themesPages['sidebar']);
                            $page_template          = str_replace($headlinesNewsPlaceholder, $homeHeadlines, $page_template);
                        }


                        // Get Top news
                        $modulespath = ASCMS_CORE_MODULE_PATH.'/news/lib/top_news.class.php';
                        $topNewsPlaceholder = '{TOP_NEWS_FILE}';
                        if (   file_exists($modulespath)
                            && (   strpos($page_content, $topNewsPlaceholder) !== false
                                || strpos($themesPages['index'], $topNewsPlaceholder) !== false
                                || strpos($themesPages['sidebar'], $topNewsPlaceholder) !== false
                                || strpos($page_template, $topNewsPlaceholder) !== false)
                        ) {
                            $newsTopObj = new newsTop($themesPages['top_news']);
                            $homeTopNews = $newsTopObj->getHomeTopNews();
                            $page_content           = str_replace($topNewsPlaceholder, $homeTopNews, $page_content);
                            $themesPages['index']   = str_replace($topNewsPlaceholder, $homeTopNews, $themesPages['index']);
                            $themesPages['sidebar'] = str_replace($topNewsPlaceholder, $homeTopNews, $themesPages['sidebar']);
                            $page_template          = str_replace($topNewsPlaceholder, $homeTopNews, $page_template);
                        }
                    },
                    'Calendar' => function() {
                        global $modulespath, $eventsPlaceholder, $_CONFIG, $page_content, $themesPages, $page_template,
                                $calHeadlinesObj, $calHeadlines;

                        // Get Calendar Events
                        $modulespath = ASCMS_MODULE_PATH.'/calendar/headlines.class.php';
                        $eventsPlaceholder = '{EVENTS_FILE}';
                        if (   MODULE_INDEX < 2
                            && $_CONFIG['calendarheadlines']
                            && (   strpos($page_content, $eventsPlaceholder) !== false
                                || strpos($themesPages['index'], $eventsPlaceholder) !== false
                                || strpos($themesPages['sidebar'], $eventsPlaceholder) !== false
                                || strpos($page_template, $eventsPlaceholder) !== false)
                            && file_exists($modulespath)
                        ) {
                            $calHeadlinesObj = new \calHeadlines($themesPages['calendar_headlines']);
                            $calHeadlines = $calHeadlinesObj->getHeadlines();
                            $page_content           = str_replace($eventsPlaceholder, $calHeadlines, $page_content);
                            $themesPages['index']   = str_replace($eventsPlaceholder, $calHeadlines, $themesPages['index']);
                            $themesPages['sidebar'] = str_replace($eventsPlaceholder, $calHeadlines, $themesPages['sidebar']);
                            $page_template          = str_replace($eventsPlaceholder, $calHeadlines, $page_template);
                        }
                    },
                    'Knowledge' => function() {
                        global $_CONFIG, $cl, $knowledgeInterface, $page_content, $page_template, $themesPages;

                        // get knowledge content
                        if (MODULE_INDEX < 2 && !empty($_CONFIG['useKnowledgePlaceholders'])) {
                            if ($cl->loadFile(ASCMS_MODULE_PATH.'/knowledge/interface.class.php')) {

                                $knowledgeInterface = new \KnowledgeInterface();
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
                    },
                    'Directory' => function() {
                        global $_CONFIG, $cl, $dirc, $themesPages, $page_content, $page_template, $themesPages;

                        // get Directory Homecontent
                        if ($_CONFIG['directoryHomeContent'] == '1') {
                            if ($cl->loadFile(ASCMS_MODULE_PATH.'/directory/homeContent.class.php')) {

                                $dirc = $themesPages['directory_content'];
                                if (preg_match('/{DIRECTORY_FILE}/', $page_content)) {
                                    $page_content = str_replace('{DIRECTORY_FILE}', dirHomeContent::getObj($dirc)->getContent(), $page_content);
                                }
                                if (preg_match('/{DIRECTORY_FILE}/', $page_template)) {
                                    $page_template = str_replace('{DIRECTORY_FILE}', dirHomeContent::getObj($dirc)->getContent(), $page_template);
                                }
                                if (preg_match('/{DIRECTORY_FILE}/', $themesPages['index'])) {
                                    $themesPages['index'] = str_replace('{DIRECTORY_FILE}', dirHomeContent::getObj($dirc)->getContent(), $themesPages['index']);
                                }
                            }
                        }
                    },
                    'Forum' => function() {
                        global $_CONFIG, $cl, $forumHomeContentInPageContent, $forumHomeContentInPageTemplate,
                                $forumHomeContentInThemesPage, $page_content, $page_template, $themesPages,
                                $homeForumContent, $_ARRAYLANG, $objInit, $objForum, $objForumHome,
                                $forumHomeTagCloudInContent, $forumHomeTagCloudInTemplate, $forumHomeTagCloudInTheme,
                                $forumHomeTagCloudInSidebar, $strTagCloudSource;

                        // get + replace forum latest entries content
                        if ($_CONFIG['forumHomeContent'] == '1') {
                            /** @ignore */
                            if ($cl->loadFile(ASCMS_MODULE_PATH.'/forum/homeContent.class.php')) {
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
                                $homeForumContent = '';
                                if ($forumHomeContentInPageContent || $forumHomeContentInPageTemplate || $forumHomeContentInThemesPage) {
                                    $_ARRAYLANG = array_merge($_ARRAYLANG, $objInit->loadLanguageData('forum'));
                                    $objForum = new ForumHomeContent($themesPages['forum_content']);
                                    $homeForumContent = $objForum->getContent();
                                }
                                if ($forumHomeContentInPageContent) {
                                    $page_content = str_replace('{FORUM_FILE}', $homeForumContent, $page_content);
                                }
                                if ($forumHomeContentInPageTemplate) {
                                    $page_template = str_replace('{FORUM_FILE}', $homeForumContent, $page_template);
                                }
                                if ($forumHomeContentInThemesPage) {
                                    $themesPages['index'] = str_replace('{FORUM_FILE}', $homeForumContent, $themesPages['index']);
                                }
                            }
                        }

                        // get + replace forum tagcloud
                        if (!empty($_CONFIG['forumTagContent'])) {
                            /** @ignore */
                            if ($cl->loadFile(ASCMS_MODULE_PATH.'/forum/homeContent.class.php')) {
                                $objForumHome = new ForumHomeContent();
                                //Forum-TagCloud
                                $forumHomeTagCloudInContent = $objForumHome->searchKeywordInContent('FORUM_TAG_CLOUD', $page_content);
                                $forumHomeTagCloudInTemplate = $objForumHome->searchKeywordInContent('FORUM_TAG_CLOUD', $page_template);
                                $forumHomeTagCloudInTheme = $objForumHome->searchKeywordInContent('FORUM_TAG_CLOUD', $themesPages['index']);
                                $forumHomeTagCloudInSidebar = $objForumHome->searchKeywordInContent('FORUM_TAG_CLOUD', $themesPages['sidebar']);
                                if (   $forumHomeTagCloudInContent
                                    || $forumHomeTagCloudInTemplate
                                    || $forumHomeTagCloudInTheme
                                    || $forumHomeTagCloudInSidebar
                                ) {
                                    $strTagCloudSource = $objForumHome->getHomeTagCloud();
                                    $page_content = $objForumHome->fillVariableIfActivated('FORUM_TAG_CLOUD', $strTagCloudSource, $page_content, $forumHomeTagCloudInContent);
                                    $page_template = $objForumHome->fillVariableIfActivated('FORUM_TAG_CLOUD', $strTagCloudSource, $page_template, $forumHomeTagCloudInTemplate);
                                    $themesPages['index'] = $objForumHome->fillVariableIfActivated('FORUM_TAG_CLOUD', $strTagCloudSource, $themesPages['index'], $forumHomeTagCloudInTheme);
                                    $themesPages['sidebar'] = $objForumHome->fillVariableIfActivated('FORUM_TAG_CLOUD', $strTagCloudSource, $themesPages['sidebar'], $forumHomeTagCloudInSidebar);
                                }
                            }
                        }
                    },
                    'Gallery' => function() {
                        global $cl, $objGalleryHome, $page_content, $page_template, $themesPages, $latestImage;

                        // Get Gallery-Images (Latest, Random)
                        /** @ignore */
                        if ($cl->loadFile(ASCMS_MODULE_PATH.'/gallery/homeContent.class.php')) {
                            $objGalleryHome = new \GalleryHomeContent();
                            if ($objGalleryHome->checkRandom()) {
                                if (preg_match('/{GALLERY_RANDOM}/', $page_content)) {
                                    $page_content = str_replace('{GALLERY_RANDOM}', $objGalleryHome->getRandomImage(), $page_content);
                                }
                                if (preg_match('/{GALLERY_RANDOM}/', $page_template))  {
                                    $page_template = str_replace('{GALLERY_RANDOM}', $objGalleryHome->getRandomImage(), $page_template);
                                }
                                if (preg_match('/{GALLERY_RANDOM}/', $themesPages['index'])) {
                                    $themesPages['index'] = str_replace('{GALLERY_RANDOM}', $objGalleryHome->getRandomImage(), $themesPages['index']);
                                }
                                if (preg_match('/{GALLERY_RANDOM}/', $themesPages['sidebar'])) {
                                    $themesPages['sidebar'] = str_replace('{GALLERY_RANDOM}', $objGalleryHome->getRandomImage(), $themesPages['sidebar']);
                                }
                            }
                            if ($objGalleryHome->checkLatest()) {
                                $latestImage = $objGalleryHome->getLastImage();
                                if (preg_match('/{GALLERY_LATEST}/', $page_content)) {
                                    $page_content = str_replace('{GALLERY_LATEST}', $latestImage, $page_content);
                                }
                                if (preg_match('/{GALLERY_LATEST}/', $page_template)) {
                                    $page_template = str_replace('{GALLERY_LATEST}', $latestImage, $page_template);
                                }
                                if (preg_match('/{GALLERY_LATEST}/', $themesPages['index'])) {
                                    $themesPages['index'] = str_replace('{GALLERY_LATEST}', $latestImage, $themesPages['index']);
                                }
                                if (preg_match('/{GALLERY_LATEST}/', $themesPages['sidebar'])) {
                                    $themesPages['sidebar'] = str_replace('{GALLERY_LATEST}', $latestImage, $themesPages['sidebar']);
                                }
                            }
                        }
                    },
                    'Podcast' => function() {
                        global $podcastFirstBlock, $podcastContent, $_CONFIG, $cl, $podcastHomeContentInPageContent,
                                $podcastHomeContentInPageTemplate, $podcastHomeContentInThemesPage, $page_content,
                                $page_template, $themesPages, $_ARRAYLANG, $objInit, $objPodcast, $podcastBlockPos,
                                $contentPos;

                        // get latest podcast entries
                        $podcastFirstBlock = false;
                        $podcastContent = null;
                        if (!empty($_CONFIG['podcastHomeContent'])) {
                            /** @ignore */
                            if ($cl->loadFile(ASCMS_MODULE_PATH.'/podcast/homeContent.class.php')) {
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
                                if (   $podcastHomeContentInPageContent
                                    || $podcastHomeContentInPageTemplate
                                    || $podcastHomeContentInThemesPage) {
                                    $_ARRAYLANG = array_merge($_ARRAYLANG, $objInit->loadLanguageData('podcast'));
                                    $objPodcast = new podcastHomeContent($themesPages['podcast_content']);
                                    $podcastContent = $objPodcast->getContent();
                                    if ($podcastHomeContentInPageContent) {
                                        $page_content = str_replace('{PODCAST_FILE}', $podcastContent, $page_content);
                                    }
                                    if ($podcastHomeContentInPageTemplate) {
                                        $page_template = str_replace('{PODCAST_FILE}', $podcastContent, $page_template);
                                    }
                                    if ($podcastHomeContentInThemesPage) {
                                        $podcastFirstBlock = false;
                                        if (strpos($_SERVER['REQUEST_URI'], 'section=podcast')){
                                            $podcastBlockPos = strpos($themesPages['index'], '{PODCAST_FILE}');
                                            $contentPos = strpos($themesPages['index'], '{CONTENT_FILE}');
                                            $podcastFirstBlock = $podcastBlockPos < $contentPos ? true : false;
                                        }
                                        $themesPages['index'] = str_replace('{PODCAST_FILE}',
                                            $objPodcast->getContent($podcastFirstBlock), $themesPages['index']);
                                    }
                                }
                            }
                        }
                    },
                    'Voting' => function() {
                        global $cl, $_ARRAYLANG, $objInit, $themesPages, $arrMatches, $page_content, $page_template;

                        // get voting
                        /** @ignore */
                        if ($cl->loadFile(ASCMS_MODULE_PATH.'/voting/index.class.php')) {
                            $_ARRAYLANG = array_merge($_ARRAYLANG, $objInit->loadLanguageData('voting'));
                        //  if ($objTemplate->blockExists('voting_result')) {
                        //      $objTemplate->_blocks['voting_result'] = setVotingResult($objTemplate->_blocks['voting_result']);
                        //  }
                            if (preg_match('@<!--\s+BEGIN\s+(voting_result)\s+-->(.*)<!--\s+END\s+\1\s+-->@m', $themesPages['sidebar'], $arrMatches)) {
                                $themesPages['sidebar'] = preg_replace('@(<!--\s+BEGIN\s+(voting_result)\s+-->.*<!--\s+END\s+\2\s+-->)@m', setVotingResult($arrMatches[2]), $themesPages['sidebar']);
                            }
                            if (preg_match('@<!--\s+BEGIN\s+(voting_result)\s+-->(.*)<!--\s+END\s+\1\s+-->@m', $themesPages['index'], $arrMatches)) {
                                $themesPages['index'] = preg_replace('@(<!--\s+BEGIN\s+(voting_result)\s+-->.*<!--\s+END\s+\2\s+-->)@m', setVotingResult($arrMatches[2]), $themesPages['index']);
                            }
                            if (preg_match('@<!--\s+BEGIN\s+(voting_result)\s+-->(.*)<!--\s+END\s+\1\s+-->@m', $page_content, $arrMatches)) {
                                $page_content = preg_replace('@(<!--\s+BEGIN\s+(voting_result)\s+-->.*<!--\s+END\s+\2\s+-->)@m', setVotingResult($arrMatches[2]), $page_content);
                            }
                            if (preg_match('@<!--\s+BEGIN\s+(voting_result)\s+-->(.*)<!--\s+END\s+\1\s+-->@m', $page_template, $arrMatches)) {
                                $page_template = preg_replace('@(<!--\s+BEGIN\s+(voting_result)\s+-->.*<!--\s+END\s+\2\s+-->)@m', setVotingResult($arrMatches[2]), $page_template);
                            }
                        }
                    },
                    'Blog' => function() {
                        global $cl, $objBlogHome, $themesPages, $page_content, $page_template, $_ARRAYLANG, $objInit,
                                $blogHomeContentInContent, $blogHomeContentInTemplate, $blogHomeContentInTheme, $blogHomeContentInSidebar, $strContentSource,
                                $blogHomeCalendarInContent, $blogHomeCalendarInTemplate, $blogHomeCalendarInTheme, $blogHomeCalendarInSidebar, $strCalendarSource,
                                $blogHomeTagCloudInContent, $blogHomeTagCloudInTemplate, $blogHomeTagCloudInTheme, $blogHomeTagCloudInSidebar, $strTagCloudSource,
                                $blogHomeTagHitlistInContent, $blogHomeTagHitlistInTemplate, $blogHomeTagHitlistInTheme, $blogHomeTagHitlistInSidebar, $strTagHitlistSource,
                                $blogHomeCategorySelectInContent, $blogHomeCategorySelectInTemplate, $blogHomeCategorySelectInTheme, $blogHomeCategorySelectInSidebar, $strCategoriesSelect,
                                $blogHomeCategoryListInContent, $blogHomeCategoryListInTemplate, $blogHomeCategoryListInTheme, $blogHomeCategoryListInSidebar, $strCategoriesList,
                                $x;

                        // Get content for the blog-module.
                        /** @ignore */
                        if ($cl->loadFile(ASCMS_MODULE_PATH.'/blog/homeContent.class.php')) {
                            $objBlogHome = new \BlogHomeContent($themesPages['blog_content']);
                            if ($objBlogHome->blockFunktionIsActivated()) {
                                //Blog-File
                                $blogHomeContentInContent = $objBlogHome->searchKeywordInContent('BLOG_FILE', $page_content);
                                $blogHomeContentInTemplate = $objBlogHome->searchKeywordInContent('BLOG_FILE', $page_template);
                                $blogHomeContentInTheme = $objBlogHome->searchKeywordInContent('BLOG_FILE', $themesPages['index']);
                                $blogHomeContentInSidebar = $objBlogHome->searchKeywordInContent('BLOG_FILE', $themesPages['sidebar']);
                                if ($blogHomeContentInContent || $blogHomeContentInTemplate || $blogHomeContentInTheme || $blogHomeContentInSidebar) {
                                    $_ARRAYLANG = array_merge($_ARRAYLANG, $objInit->loadLanguageData('blog'));
                                    $strContentSource = $objBlogHome->getLatestEntries();
                                    $page_content = $objBlogHome->fillVariableIfActivated('BLOG_FILE', $strContentSource, $page_content, $blogHomeContentInContent);
                                    $page_template = $objBlogHome->fillVariableIfActivated('BLOG_FILE', $strContentSource, $page_template, $blogHomeContentInTemplate);
                                    $themesPages['index'] = $objBlogHome->fillVariableIfActivated('BLOG_FILE', $strContentSource, $themesPages['index'], $blogHomeContentInTheme);
                                    $themesPages['sidebar'] = $objBlogHome->fillVariableIfActivated('BLOG_FILE', $strContentSource, $themesPages['sidebar'], $blogHomeContentInSidebar);
                                }
                                //Blog-Calendar
                                $blogHomeCalendarInContent = $objBlogHome->searchKeywordInContent('BLOG_CALENDAR', $page_content);
                                $blogHomeCalendarInTemplate = $objBlogHome->searchKeywordInContent('BLOG_CALENDAR', $page_template);
                                $blogHomeCalendarInTheme = $objBlogHome->searchKeywordInContent('BLOG_CALENDAR', $themesPages['index']);
                                $blogHomeCalendarInSidebar = $objBlogHome->searchKeywordInContent('BLOG_CALENDAR', $themesPages['sidebar']);
                                if ($blogHomeCalendarInContent || $blogHomeCalendarInTemplate || $blogHomeCalendarInTheme || $blogHomeCalendarInSidebar) {
                                    $strCalendarSource = $objBlogHome->getHomeCalendar();
                                    $page_content = $objBlogHome->fillVariableIfActivated('BLOG_CALENDAR', $strCalendarSource, $page_content, $blogHomeCalendarInContent);
                                    $page_template = $objBlogHome->fillVariableIfActivated('BLOG_CALENDAR', $strCalendarSource, $page_template, $blogHomeCalendarInTemplate);
                                    $themesPages['index'] = $objBlogHome->fillVariableIfActivated('BLOG_CALENDAR', $strCalendarSource, $themesPages['index'], $blogHomeCalendarInTheme);
                                    $themesPages['sidebar'] = $objBlogHome->fillVariableIfActivated('BLOG_CALENDAR', $strCalendarSource, $themesPages['sidebar'], $blogHomeCalendarInSidebar);
                                }
                                //Blog-TagCloud
                                $blogHomeTagCloudInContent = $objBlogHome->searchKeywordInContent('BLOG_TAG_CLOUD', $page_content);
                                $blogHomeTagCloudInTemplate = $objBlogHome->searchKeywordInContent('BLOG_TAG_CLOUD', $page_template);
                                $blogHomeTagCloudInTheme = $objBlogHome->searchKeywordInContent('BLOG_TAG_CLOUD', $themesPages['index']);
                                $blogHomeTagCloudInSidebar = $objBlogHome->searchKeywordInContent('BLOG_TAG_CLOUD', $themesPages['sidebar']);
                                if ($blogHomeTagCloudInContent || $blogHomeTagCloudInTemplate || $blogHomeTagCloudInTheme || $blogHomeTagCloudInSidebar) {
                                    $strTagCloudSource = $objBlogHome->getHomeTagCloud();
                                    $page_content = $objBlogHome->fillVariableIfActivated('BLOG_TAG_CLOUD', $strTagCloudSource, $page_content, $blogHomeTagCloudInContent);
                                    $page_template = $objBlogHome->fillVariableIfActivated('BLOG_TAG_CLOUD', $strTagCloudSource, $page_template, $blogHomeTagCloudInTemplate);
                                    $themesPages['index'] = $objBlogHome->fillVariableIfActivated('BLOG_TAG_CLOUD', $strTagCloudSource, $themesPages['index'], $blogHomeTagCloudInTheme);
                                    $themesPages['sidebar'] = $objBlogHome->fillVariableIfActivated('BLOG_TAG_CLOUD', $strTagCloudSource, $themesPages['sidebar'], $blogHomeTagCloudInSidebar);
                                }
                                //Blog-TagHitlist
                                $blogHomeTagHitlistInContent = $objBlogHome->searchKeywordInContent('BLOG_TAG_HITLIST', $page_content);
                                $blogHomeTagHitlistInTemplate = $objBlogHome->searchKeywordInContent('BLOG_TAG_HITLIST', $page_template);
                                $blogHomeTagHitlistInTheme = $objBlogHome->searchKeywordInContent('BLOG_TAG_HITLIST', $themesPages['index']);
                                $blogHomeTagHitlistInSidebar = $objBlogHome->searchKeywordInContent('BLOG_TAG_HITLIST', $themesPages['sidebar']);
                                if ($blogHomeTagHitlistInContent || $blogHomeTagHitlistInTemplate || $blogHomeTagHitlistInTheme || $blogHomeTagHitlistInSidebar) {
                                    $strTagHitlistSource = $objBlogHome->getHomeTagHitlist();
                                    $page_content = $objBlogHome->fillVariableIfActivated('BLOG_TAG_HITLIST', $strTagHitlistSource, $page_content, $blogHomeTagHitlistInContent);
                                    $page_template = $objBlogHome->fillVariableIfActivated('BLOG_TAG_HITLIST', $strTagHitlistSource, $page_template, $blogHomeTagHitlistInTemplate);
                                    $themesPages['index'] = $objBlogHome->fillVariableIfActivated('BLOG_TAG_HITLIST', $strTagHitlistSource, $themesPages['index'], $blogHomeTagHitlistInTheme);
                                    $themesPages['sidebar'] = $objBlogHome->fillVariableIfActivated('BLOG_TAG_HITLIST', $strTagHitlistSource, $themesPages['sidebar'], $blogHomeTagHitlistInSidebar);
                                }
                                //Blog-Categories (Select)
                                $blogHomeCategorySelectInContent = $objBlogHome->searchKeywordInContent('BLOG_CATEGORIES_SELECT', $page_content);
                                $blogHomeCategorySelectInTemplate = $objBlogHome->searchKeywordInContent('BLOG_CATEGORIES_SELECT', $page_template);
                                $blogHomeCategorySelectInTheme = $objBlogHome->searchKeywordInContent('BLOG_CATEGORIES_SELECT', $themesPages['index']);
                                $blogHomeCategorySelectInSidebar = $objBlogHome->searchKeywordInContent('BLOG_CATEGORIES_SELECT', $themesPages['sidebar']);
                                if ($blogHomeCategorySelectInContent || $blogHomeCategorySelectInTemplate || $blogHomeCategorySelectInTheme || $blogHomeCategorySelectInSidebar) {
                                    $strCategoriesSelect = $objBlogHome->getHomeCategoriesSelect();
                                    $page_content = $objBlogHome->fillVariableIfActivated('BLOG_CATEGORIES_SELECT', $strCategoriesSelect, $page_content, $blogHomeCategorySelectInContent);
                                    $page_template = $objBlogHome->fillVariableIfActivated('BLOG_CATEGORIES_SELECT', $strCategoriesSelect, $page_template, $blogHomeCategorySelectInTemplate);
                                    $themesPages['index'] = $objBlogHome->fillVariableIfActivated('BLOG_CATEGORIES_SELECT', $strCategoriesSelect, $themesPages['index'], $blogHomeCategorySelectInTheme);
                                    $themesPages['sidebar'] = $objBlogHome->fillVariableIfActivated('BLOG_CATEGORIES_SELECT', $strCategoriesSelect, $themesPages['sidebar'], $blogHomeCategorySelectInSidebar);
                                }
                                //Blog-Categories (List)
                                $blogHomeCategoryListInContent = $objBlogHome->searchKeywordInContent('BLOG_CATEGORIES_LIST', $page_content);
                                $blogHomeCategoryListInTemplate = $objBlogHome->searchKeywordInContent('BLOG_CATEGORIES_LIST', $page_template);
                                $blogHomeCategoryListInTheme = $objBlogHome->searchKeywordInContent('BLOG_CATEGORIES_LIST', $themesPages['index']);
                                $blogHomeCategoryListInSidebar = $objBlogHome->searchKeywordInContent('BLOG_CATEGORIES_LIST', $themesPages['sidebar']);
                                if ($blogHomeCategoryListInContent || $blogHomeCategoryListInTemplate || $blogHomeCategoryListInTheme || $blogHomeCategoryListInSidebar) {
                                    $strCategoriesList = $objBlogHome->getHomeCategoriesList();
                                    $page_content = $objBlogHome->fillVariableIfActivated('BLOG_CATEGORIES_LIST', $strCategoriesList, $page_content, $blogHomeCategoryListInContent);
                                    $page_template = $objBlogHome->fillVariableIfActivated('BLOG_CATEGORIES_LIST', $strCategoriesList, $page_template, $blogHomeCategoryListInTemplate);
                                    $themesPages['index'] = $objBlogHome->fillVariableIfActivated('BLOG_CATEGORIES_LIST', $strCategoriesList, $themesPages['index'], $blogHomeCategoryListInTheme);
                                    $themesPages['sidebar'] = $objBlogHome->fillVariableIfActivated('BLOG_CATEGORIES_LIST', $strCategoriesList, $themesPages['sidebar'], $blogHomeCategoryListInSidebar);
                                }
                            }
                        }
                    },
                    'MediaDir' => function() {
                        global $cl, $objMadiadirPlaceholders, $page_content, $page_template, $themesPages;

                        // Media directory: set placeholders I
                        /** @ignore */
                        if ($cl->loadFile(ASCMS_MODULE_PATH.'/mediadir/placeholders.class.php')) {
                            $objMadiadirPlaceholders = new \mediaDirectoryPlaceholders();
                            // Level/Category Navbar
                            if (preg_match('/{MEDIADIR_NAVBAR}/', $page_content)) {
                                $page_content = str_replace('{MEDIADIR_NAVBAR}', $objMadiadirPlaceholders->getNavigationPlacholder(), $page_content);
                            }
                            if (preg_match('/{MEDIADIR_NAVBAR}/', $page_template)) {
                                $page_template = str_replace('{MEDIADIR_NAVBAR}', $objMadiadirPlaceholders->getNavigationPlacholder(), $page_template);
                            }
                            if (preg_match('/{MEDIADIR_NAVBAR}/', $themesPages['index'])) {
                                $themesPages['index'] = str_replace('{MEDIADIR_NAVBAR}', $objMadiadirPlaceholders->getNavigationPlacholder(), $themesPages['index']);
                            }
                            if (preg_match('/{MEDIADIR_NAVBAR}/', $themesPages['sidebar'])) {
                                $themesPages['sidebar'] = str_replace('{MEDIADIR_NAVBAR}', $objMadiadirPlaceholders->getNavigationPlacholder(), $themesPages['sidebar']);
                            }
                            // Latest Entries
                            if (preg_match('/{MEDIADIR_LATEST}/', $page_content)) {
                                $page_content = str_replace('{MEDIADIR_LATEST}', $objMadiadirPlaceholders->getLatestPlacholder(), $page_content);
                            }
                            if (preg_match('/{MEDIADIR_LATEST}/', $page_template)) {
                                $page_template = str_replace('{MEDIADIR_LATEST}', $objMadiadirPlaceholders->getLatestPlacholder(), $page_template);
                            }
                            if (preg_match('/{MEDIADIR_LATEST}/', $themesPages['index'])) {
                                $themesPages['index'] = str_replace('{MEDIADIR_LATEST}', $objMadiadirPlaceholders->getLatestPlacholder(), $themesPages['index']);
                            }
                            if (preg_match('/{MEDIADIR_LATEST}/', $themesPages['sidebar'])) {
                                $themesPages['sidebar'] = str_replace('{MEDIADIR_LATEST}', $objMadiadirPlaceholders->getLatestPlacholder(), $themesPages['sidebar']);
                            }
                        }
                    },
                    'FwUser' => function() {
                        global $page_content;

                        // ACCESS: parse access_logged_in[1-9] and access_logged_out[1-9] blocks
                        \FWUser::parseLoggedInOutBlocks($page_content);
                    },
                    /*'FrontendEditing' => function() {
                        $frontendEditing = new \Cx\Core_Modules\FrontendEditing\Controller\ComponentController();
                        $frontendEditing->preContentLoad();
                    },*/
                ),
                'postContentLoad' => array(
                    'Shop' => function() {
                        global $boolShop;

                        // Show the Shop navbar in the Shop, or on every page if configured to do so
                        if (!$boolShop
                        // Optionally limit to the first instance
                        // && MODULE_INDEX == ''
                        ) {
                            \SettingDb::init('shop', 'config');
                            if (\SettingDb::getValue('shopnavbar_on_all_pages')) {
                                \Shop::init();
                                \Shop::setNavbar();
                                $boolShop = true;
                            }
                        }
                    },
                    'Directory' => function() {
                        global $directoryCheck, $objTemplate, $cl, $objDirectory, $_CORELANG;

                        // Directory Show Latest
                        //$directoryCheck = $objTemplate->blockExists('directoryLatest_row_1');
                        $directoryCheck = array();
                        for ($i = 1; $i <= 10; $i++) {
                            if ($objTemplate->blockExists('directoryLatest_row_'.$i)) {
                                array_push($directoryCheck, $i);
                            }
                        }
                        if (   !empty($directoryCheck)
                            /** @ignore */
                            && $cl->loadFile(ASCMS_MODULE_PATH.'/directory/index.class.php')) {
                            $objDirectory = new rssDirectory('');
                            if (!empty($directoryCheck)) {
                                $objTemplate->setVariable('TXT_DIRECTORY_LATEST', $_CORELANG['TXT_DIRECTORY_LATEST']);
                                $objDirectory->getBlockLatest($directoryCheck);
                            }
                        }
                    },
                    'Market' => function() {
                        global $marketCheck, $objTemplate, $cl, $objMarket, $_CORELANG;

                        // Market Show Latest
                        $marketCheck = $objTemplate->blockExists('marketLatest');
                        if (   $marketCheck
                            /** @ignore */
                            && $cl->loadFile(ASCMS_MODULE_PATH.'/market/index.class.php')) {
                            $objMarket = new Market('');
                            $objTemplate->setVariable('TXT_MARKET_LATEST', $_CORELANG['TXT_MARKET_LATEST']);
                            $objMarket->getBlockLatest();
                        }
                    },
                    'Banner' => function() {
                        global $objBanner, $_CONFIG, $cl, $objTemplate, $page;

                        // Set banner variables
                        $objBanner = null;
                        if (   $_CONFIG['bannerStatus']
                            /** @ignore */
                            && $cl->loadFile(ASCMS_CORE_MODULE_PATH.'/banner/index.class.php')) {
                            $objBanner = new Banner();
                            $objTemplate->setVariable(array(
                                'BANNER_GROUP_1' => $objBanner->getBannerCode(1, $page->getNode()->getId()),
                                'BANNER_GROUP_2' => $objBanner->getBannerCode(2, $page->getNode()->getId()),
                                'BANNER_GROUP_3' => $objBanner->getBannerCode(3, $page->getNode()->getId()),
                                'BANNER_GROUP_4' => $objBanner->getBannerCode(4, $page->getNode()->getId()),
                                'BANNER_GROUP_5' => $objBanner->getBannerCode(5, $page->getNode()->getId()),
                                'BANNER_GROUP_6' => $objBanner->getBannerCode(6, $page->getNode()->getId()),
                                'BANNER_GROUP_7' => $objBanner->getBannerCode(7, $page->getNode()->getId()),
                                'BANNER_GROUP_8' => $objBanner->getBannerCode(8, $page->getNode()->getId()),
                                'BANNER_GROUP_9' => $objBanner->getBannerCode(9, $page->getNode()->getId()),
                                'BANNER_GROUP_10' => $objBanner->getBannerCode(10, $page->getNode()->getId()),
                            ));
                            if (isset($_REQUEST['bannerId'])) {
                                $objBanner->updateClicks(intval($_REQUEST['bannerId']));
                            }
                        }
                    },
                    'MediaDir' => function() {
                        global $mediadirCheck, $objTemplate, $cl, $_CORELANG;

                        // Media directory: Set placeholders II (latest / headline)
                        $mediadirCheck = array();
                        for ($i = 1; $i <= 10; ++$i) {
                            if ($objTemplate->blockExists('mediadirLatest_row_'.$i)){
                                array_push($mediadirCheck, $i);
                            }
                        }
                        if (   $mediadirCheck
                            /** @ignore */
                            && $cl->loadFile(ASCMS_MODULE_PATH.'/mediadir/index.class.php')) {
                            $objMediadir = new mediaDirectory('');
                            $objTemplate->setVariable('TXT_MEDIADIR_LATEST', $_CORELANG['TXT_DIRECTORY_LATEST']);
                            $objMediadir->getHeadlines($mediadirCheck);
                        }
                    },
                    'FwUser' => function() {
                        global $objTemplate, $cl;

                        // ACCESS: parse access_logged_in[1-9] and access_logged_out[1-9] blocks
                        \FWUser::parseLoggedInOutBlocks($objTemplate);

                        // currently online users
                        $objAccessBlocks = false;
                        if ($objTemplate->blockExists('access_currently_online_member_list')) {
                            if (    FWUser::showCurrentlyOnlineUsers()
                                && (    $objTemplate->blockExists('access_currently_online_female_members')
                                    ||  $objTemplate->blockExists('access_currently_online_male_members')
                                    ||  $objTemplate->blockExists('access_currently_online_members'))) {
                                if ($cl->loadFile(ASCMS_CORE_MODULE_PATH.'/access/lib/blocks.class.php'))
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
                                    && $cl->loadFile(ASCMS_CORE_MODULE_PATH.'/access/lib/blocks.class.php'))
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
                                    && $cl->loadFile(ASCMS_CORE_MODULE_PATH.'/access/lib/blocks.class.php'))
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
                                    && $cl->loadFile(ASCMS_CORE_MODULE_PATH.'/access/lib/blocks.class.php'))
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
                    },
                    /*'FrontendEditing' => function() {
                        $frontendEditing = new \Cx\Core_Modules\FrontendEditing\Controller\ComponentController();
                        $frontendEditing->preFinalize();
                    },*/
                ),
                'load' => array(
                    'shop' => function() {
                        global $cl, $_CORELANG, $objTemplate, $page_content, $boolShop, $_ARRAYLANG, $objInit, $plainSection;
                        
                        $_ARRAYLANG = $objInit->loadLanguageData($plainSection);
                        if (!$cl->loadFile(ASCMS_MODULE_PATH.'/shop/index.class.php'))
                            die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                        $objTemplate->setVariable('CONTENT_TEXT', \Shop::getPage($page_content));
                        $boolShop = true;
                    }
                ),
            ),
            'backend' => array(
                'preResolve' => array(
                    'Resolver' => function() {
                        global $request, $url, $cmd, $act, $isRegularPageRequest;
                        
                        // this makes \Env::get('Resolver')->getUrl() return a sensful result
                        $request = ASCMS_PATH_OFFSET.'/cadmin';
                        $url = \Cx\Core\Routing\Url::fromCapturedRequest($request, ASCMS_PATH_OFFSET, $_GET);
                        \Env::set('Resolver', new \Cx\Core\Routing\Resolver($url, null, \Env::em(), null, null));
                        
                        $cmd = isset($_REQUEST['cmd']) ? $_REQUEST['cmd'] : '';
                        $act = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';
                        
                        // If standalone is set, then we will not have to initialize/load any content page related stuff
                        $isRegularPageRequest = !isset($_REQUEST['standalone']) || $_REQUEST['standalone'] == 'false';
                    },
                    'Session' => function() {
                        global $sessionObj;
                        
                        if (empty($sessionObj)) $sessionObj = new \cmsSession();
                        $sessionObj->cmsSessionStatusUpdate('backend');
                    },
                    'Js' => function() {
                        // Load the JS helper class and set the offset
                        \JS::setOffset('../');
                        \JS::activate('backend');
                        \JS::activate('cx');
                        \JS::activate('chosen');
                    },
                    'ComponentHandler' => function() {
                        global $arrMatch, $plainCmd, $cmd, $moduleIndex, $intAccessIdOffset;
                        
                        // To clone any module, use an optional integer cmd suffix.
                        // E.g.: "shop2", "gallery5", etc.
                        // Mind that you *MUST* copy all necessary database tables, and fix any
                        // references to that module (section and cmd parameters, database tables)
                        // using the MODULE_INDEX constant in the right place both in your code
                        // *and* templates!
                        // See the Shop module for a working example and instructions on how to
                        // clone any module.
                        $arrMatch = array();
                        if (!isset($plainCmd)) {
                            $plainCmd = $cmd;
                        }
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
                    },
                    'FwUser' => function() {
                        global $objFWUser, $loggedIn, $plainCmd, $isRegularPageRequest, $userData, $loggableListener,
                                $objUser, $firstname, $lastname, $txtProfile, $objTemplate;
                        
                        $objFWUser = \FWUser::getFWUserObject();

                        /* authentification */
                        $loggedIn = $objFWUser->objUser->login(true); //check if the user is already logged in
                        if (!empty($_POST) && !$loggedIn && 
                                (
                                    (!isset($_GET['cmd']) || $_GET['cmd'] !== 'login') &&
                                    (!isset($_GET['act']) || $_GET['act'] !== 'resetpw')
                                )) { //not logged in already - do captcha and password checks
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
                                'id'   => \FWUser::getFWUserObject()->objUser->getId(),
                                'name' => \FWUser::getFWUserObject()->objUser->getUsername(),
                            );
                            $loggableListener->setUsername(json_encode($userData));
                        }
                        
                        $objUser = \FWUser::getFWUserObject()->objUser;
                        $firstname = $objUser->getProfileAttribute('firstname');
                        $lastname = $objUser->getProfileAttribute('lastname');

                        if (!empty($firstname) && !empty($lastname)) {
                            $txtProfile = $firstname.' '.$lastname;
                        } else {
                            $txtProfile = $objUser->getUsername();
                        }
                        
                        $objTemplate->setVariable(array(
                            'TXT_PROFILE'               => $txtProfile,
                            'USER_ID'                   => $objFWUser->objUser->getId(),
                        ));
                        
                        
                        if (isset($_POST['redirect']) && preg_match('/\.php/', $_POST['redirect'])) {
                            \CSRF::header('location: '.$_POST['redirect']);
                        }
                    },
                    'Csrf' => function() {
                        // CSRF code needs to be even in the login form. otherwise, we
                        // could not do a super-generic check later.. NOTE: do NOT move
                        // this above the "new cmsSession" line!
                        \CSRF::add_code();
                    },
                    'License' => function() {
                        global $license, $_CONFIG, $objDatabase, $objTemplate;
                        
                        $license = \Cx\Core_Modules\License\License::getCached($_CONFIG, $objDatabase);
                        
                        $objTemplate->touchBlock('backend_metanavigation');
                        if ($objTemplate->blockExists('upgradable')) {
                            if ($license->isUpgradable()) {
                                $objTemplate->touchBlock('upgradable');
                            } else {
                                $objTemplate->hideBlock('upgradable');
                            }
                        }
                    },
                ),
                'postResolve' => array(
                    'License' => function() {
                        global $plainCmd, $objDatabase, $loggedIn, $lc, $_CONFIG, $_CORELANG, $license;
                        
                        // check if the requested module is active:
                        if (!in_array($plainCmd, array('login', 'license', 'noaccess', ''))) {
                            $query = '
                                SELECT
                                    modules.is_active
                                FROM
                                    '.DBPREFIX.'modules AS modules,
                                    '.DBPREFIX.'backend_areas AS areas
                                WHERE
                                    areas.module_id = modules.id
                                    AND (
                                        areas.uri LIKE "%cmd=' . contrexx_raw2db($plainCmd) . '&%"
                                        OR areas.uri LIKE "%cmd=' . contrexx_raw2db($plainCmd) . '"
                                    )
                            ';
                            $res = $objDatabase->Execute($query);
                            if (!$res->fields['is_active']) {
                                $plainCmd = 'license';
                            }
                        }
                        if ($loggedIn) {
                            $license->check();
                            if ($license->getState() == \Cx\Core_Modules\License\License::LICENSE_NOK) {
                                $plainCmd = 'license';
                                $license->save(new \settingsManager(), $objDatabase);
                            }
                            $lc = \Cx\Core_Modules\License\LicenseCommunicator::getInstance($_CONFIG);
                            $lc->addJsUpdateCode($_CORELANG, $license, $plainCmd == 'license');
                        }
                    },
                    'Language' => function() {
                        global $objInit, $_LANGID, $_FRONTEND_LANGID, $_CORELANG, $_ARRAYLANG, $plainCmd;
                        
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
                        \Env::set('coreLang', $_CORELANG);
                        
                        /**
                        * Module specific language data
                        * @ignore
                        */
                        $_ARRAYLANG = $objInit->loadLanguageData($plainCmd);
                        $_ARRAYLANG = array_merge($_ARRAYLANG, $_CORELANG);
                        \Env::set('lang', $_ARRAYLANG);
                    },
                    'Csrf' => function() {
                        global $plainCmd;
                        
                        // CSRF protection.
                        // Note that we only do the check as long as there's no
                        // cmd given; this is so we can reload the main screen if
                        // the check has failed somehow.
                        // fileBrowser is an exception, as it eats CSRF codes like
                        // candy. We're doing CSRF::check_code() in the relevant
                        // parts in the module instead.
                        // The CSRF code needn't to be checked in the login module
                        // because the user isn't logged in at this point.
                        // TODO: Why is upload excluded? The CSRF check doesn't take place in the upload module!
                        if (!empty($plainCmd) and !in_array($plainCmd, array('fileBrowser', 'upload', 'login'))) {
                            \CSRF::check_code();
                        }
                    },
                ),
                'postContentLoad' => array(
                    'Message' => function() {
                        global $objTemplate;
                        
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
                    },
                    'Csrf' => function() {
                        global $objTemplate;
                        
                        \CSRF::add_placeholder($objTemplate);
                    },
                ),
            ),
        );
    }
}
