<?php
/**
 * Main script for Contrexx
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Michael Ritter <michael.ritter@comvation.com>
 * @package     contrexx
 * @subpackage  core
 * @link        http://www.contrexx.com/ contrexx homepage
 * @since       v3.1.0
 */

namespace {
    /**
     * Wrapper for new \Cx\Core\Cx()
     * This is necessary, because we cannot use namespaces in index.php
     * in order to catch errors with PHP versions prior to 5.3
     */
    function init($mode = null) {
        new \Cx\Core\Cx($mode);
    }
}

namespace Cx\Core {

    /**
     * This loads and controls everything
     * @todo Remove all instances of "global" or at least move them to a single place
     */
    class Cx {
        const MODE_CLI = 'cli';
        const MODE_FRONTEND = 'frontend';
        const MODE_BACKEND = 'backend';
        protected $startTime = array();
        protected $mode = null;

        /**
         * @var \Cx\Core\Html\Sigma
         */
        protected $template = null;

        /**
         * @var \Cx\Core\Db\Db
         */
        protected $db = null;

        /**
         * @var \Cx\Core\Routing\Url
         */
        protected $request = null;
        
        /**
         * @var \Cx\Core\Component\Controller\ComponentHandler
         */
        protected $ch = null;
        
        /**
         * @var \Cx\Core\ClassLoader\ClassLoader
         */
        protected $cl = null;
        
        /**
         * If null, customizing is deactivated
         * @var string
         */
        protected $customizingPath = null;
        
        /**
         * If null, page is not resolved yet
         * @var \Cx\Core\ContentManager\Model\Entity\Page
         */
        protected $resolvedPage = null;

        /**
         * Initialized the Cx class
         */
        public function __construct($mode = null) {
            try {
                $this->loadConfig();                    // loads config
                $this->handleCustomizing();             // handles customizings of this class (customizings die here)

                $this->startTimer();                    // start time measurement

                $this->setMode($mode);                  // set mode, default is self::MODE_FRONTEND

                // now we have a valid state, load it:
                $this->loadContrexx();
                
            } catch (\Exception $e) {
                //$e = new \Exception();
                //echo nl2br($e->getTraceAsString());
                echo file_get_contents('offline.html');
                \DBG::msg('Contrexx initialization failed! ' . get_class($e) . ': "' . $e->getMessage() . '"');
                die();
            }
        }
        
        /**
         * Loads Contrexx and initializes Contrexx
         */
        protected function loadContrexx() {
            // init
            $this->preInit();                           // APC, RAM
            $this->init();                              // ClassLoader, API, DB (incl. Doctrine)
            $this->postInit();                          // Components
            
            // init template
            $this->loadTemplate();                      // Sigma Template
            
            // @TODO: remove this
            $this->legacyGlobalsHook(1);                // $objUser, $objTemplate, $cl

            // resolve
            $this->preResolve();                        // Call pre resolve hook scripts
            $this->resolve();                           // Resolving, Language

            // @TODO: remove this
            $this->legacyGlobalsHook(2);                // $objInit, $_LANGID, $_CORELANG, $url, $virtualLanguageDirectory;

            $this->postResolve();                       // Call post resolve hook scripts

            // @TODO: remove this
            $this->legacyGlobalsHook(3);                // $objNavbar, $_ARRAYLANG, $pageId, $page, $plainSection, $objInit

            // load content
            $this->preContentLoad();                    // Call pre content load hook scripts
            $this->loadContent();                       // Init current module
            $this->postContentLoad();                   // Call post content load hook scripts

            $this->setPostContentLoadPlaceholders();    // Set Placeholders

            $this->preFinalize();                       // Call pre finalize hook scripts
            $this->finalize();                          // Set template vars and display content
            $this->postFinalize();                      // Call post finalize hook scripts
        }

        /**
         * Set the mode Contrexx is used in
         * @param mixed $mode Mode as string or true for front- or false for backend
         */
        protected function setMode($mode) {
            switch ($mode) {
                case self::MODE_BACKEND:
                case self::MODE_FRONTEND:
                case self::MODE_CLI:
                    break;
                default:
                    if ($mode === false) {
                        $mode = self::MODE_BACKEND;
                    } else {
                        $mode = self::MODE_FRONTEND;
                        if (isset($_GET['__cap'])) {
                            if (preg_match('#^' . ASCMS_PATH_OFFSET . '(/[a-z]{2})?(/admin|' . ASCMS_BACKEND_PATH . ')#', $_GET['__cap'])) {
                                // this does not belong here:
                                if (!preg_match('#^' . ASCMS_PATH_OFFSET . ASCMS_BACKEND_PATH . '#', $_GET['__cap'])) {
                                    header('Location: ' . ASCMS_PATH_OFFSET . ASCMS_BACKEND_PATH);
                                    die();
                                }
                                $mode = self::MODE_BACKEND;
                            }
                        }
                    }
                    break;
            }
            $this->mode = $mode;
        }
        
        /**
         * Returns the mode this instance of Cx is in
         * @return string One of 'cli', 'frontend', 'backend'
         */
        public function getMode() {
            return $this->mode;
        }
        
        public function getRequest() {
            return $this->request;
        }
        
        public function getTemplate() {
            return $this->template;
        }
        
        public function getPage() {
            return $this->resolvedPage;
        }
        
        protected function loadConfig() {
            global $_DBCONFIG, $_CONFIGURATION, $_CONFIG, $_PATHCONFIG;

            /**
             * User configuration settings
             *
             * This file is re-created by the CMS itself. It initializes the
             * {@link $_CONFIG[]} global array.
             */
            $incSettingsStatus = include_once dirname(dirname(__FILE__)).'/config/settings.php';

            /**
             * Path, database, FTP configuration settings
             *
             * Initialises global settings array and constants.
             */
            include_once dirname(dirname(__FILE__)).'/config/configuration.php';

            // Check if the system is installed
            if (!defined('CONTEXX_INSTALLED') || !CONTEXX_INSTALLED) {
                header('Location: ../installer/index.php');
                exit;
            } else if ($incSettingsStatus === false) {
                die('System halted: Unable to load basic configuration!');
            }

            // Check if the system is configured with enabled customizings
            if (isset($_CONFIG['useCustomizings']) && $_CONFIG['useCustomizings'] == 'on') {
                $this->customizingPath = ASCMS_CUSTOMIZING_PATH;
            }
        }
        
        /**
         * Loads a subclass of this class from customizing if available
         * @todo make sure cx\customizing\core\cx is a subclass of self
         * @return null
         */
        protected function handleCustomizing() {
            if (!$this->customizingPath) {
                return;
            }
            if (!class_exists('\\Cx\\Customizing\\Core\\Cx')) {
                return;
            }
            // we have to use reflection here, since instanceof does not work if the child is no object
            $myReflection = new ReflectionClass('\\Cx\\Customizing\\Core\\Cx');
            if (!$myReflection->isSubclassOf(get_class($this))) {
                return;
            }
            new \Cx\Customizing\Core\Cx($this->getMode());
            die();
        }
        
        /**
         * Starts time measurement for page parsing time
         */
        protected function startTimer() {
            $this->startTime = explode(' ', microtime());
        }
        
        /**
         * Stops time measurement and returns page parsing time
         * @return int Time needed to parse page in seconds
         */
        protected function stopTimer() {
            $finishTime = explode(' ', microtime());
            return round(((float)$finishTime[0] + (float)$finishTime[1]) - ((float)$this->startTime[0] + (float)$this->startTime[1]), 5);
        }

        protected function preInit() {
            global $_CONFIG;
            
            // Check if system is running
            if ($_CONFIG['systemStatus'] != 'on') {
                header('Location: offline.html');
                die(1);
            }
            
            $this->tryToEnableApc();
            $this->tryToSetMemoryLimit();
        }

        protected function postInit() {
            $this->loadComponents();
        }

        protected function preResolve() {
            global $url;
            
            $this->ch->callPreResolveHooks('legacy');
            $this->request = $url;
            $this->ch->callPreResolveHooks('proper');
        }

        protected function resolve() {
            // implemented as pre- and post resolve hooks by now
        }

        protected function postResolve() {
            global $page, $page_title, $page_content;
            
            $this->ch->callPostResolveHooks('legacy');
            $this->resolvedPage = $page;
            $this->resolvedPage->setContentTitle($page_title);
            $this->resolvedPage->setContent($page_content);
            $this->ch->callPostResolveHooks('proper');
            $page_title = $this->resolvedPage->getContentTitle();
            $page_content = $this->resolvedPage->getContent();
        }

        protected function preContentLoad() {
            global $page_title, $page_content;
            
            $this->ch->callPreContentLoadHooks();
            $page_title = $this->resolvedPage->getContentTitle();
            $page_content = $this->resolvedPage->getContent();
        }

        protected function postContentLoad() {
            $this->ch->callPostContentLoadHooks();
        }
        
        protected function preFinalize() {
            $this->ch->callPreFinalizeHooks();
        }
        
        protected function postFinalize() {
            $this->ch->callPostFinalizeHooks();
        }

        /**
         * This tries to enable Alternate PHP Cache
         */
        protected function tryToEnableApc() {
            global $apcEnabled;

            $apcEnabled = false;
            if (extension_loaded('apc')) {
                if (ini_get('apc.enabled')) {
                    $apcEnabled = true;
                } else {
                    ini_set('apc.enabled', 1);
                    if (ini_get('apc.enabled')) {
                        $apcEnabled = true;
                    }
                }
            }
        }

        /**
         * This tries to set the memory limit if its lower than 32 megabytes
         */
        protected function tryToSetMemoryLimit() {
            global $memoryLimit, $apcEnabled;

            preg_match('/^\d+/', ini_get('memory_limit'), $memoryLimit);
            if ($apcEnabled) {
                if ($memoryLimit[0] < 32) {
                    ini_set('memory_limit', '32M');
                }
            } else {
                if ($memoryLimit[0] < 48) {
                    ini_set('memory_limit', '48M');
                }
            }
        }

        protected function loadComponents() {
            $this->ch = new \Cx\Core\Component\Controller\ComponentHandler($this->mode == self::MODE_FRONTEND, $this->db->getEntityManager());
            $this->ch->initComponents();
        }
        
        public function getUser() {
            return \FWUser::getFWUserObject();
        }
        
        /**
         * Init template object
         */
        protected function loadTemplate() {
            $this->template = new \Cx\Core\Html\Sigma(($this->mode == self::MODE_FRONTEND) ? ASCMS_THEMES_PATH : ASCMS_ADMIN_TEMPLATE_PATH);
            $this->template->setErrorHandling(PEAR_ERROR_DIE);
            if ($this->mode == self::MODE_BACKEND) {
                $this->template->loadTemplateFile('index.html');
                $this->template->addBlockfile('CONTENT_FILE', 'index_content', 'index_content.html');
            }
        }
        
        /**
         * This populates globals for legacy code
         * @global type $objFWUser
         * @param type $no 
         */
        protected function legacyGlobalsHook($no) {
            global $objFWUser, $objTemplate, $cl,
                    $objInit, $_LANGID, $_CORELANG, $url, $virtualLanguageDirectory,
                    $objNavbar, $_ARRAYLANG, $pageId, $page, $plainSection, $objInit;
            
            switch ($no) {
                case 1:
                    // Get instance of FWUser object
                    $objFWUser = $this->getUser();
                    // populate template
                    $objTemplate = $this->template;
                    // populate classloader
                    $cl = $this->cl;
                    break;
                
                case 2:
                    // Code to set language
                    // @todo: move this to somewhere else
                    // in backend it's in Language->postResolve
                    if ($this->mode == self::MODE_FRONTEND) {
                        $objInit->setFrontendLangId($_LANGID);
                        define('FRONTEND_LANG_ID', $_LANGID);
                        define('LANG_ID', $_LANGID);
                        // Load interface language data
                        /**
                        * Core language data
                        * @global array $_CORELANG
                        */
                        $_CORELANG = $objInit->loadLanguageData('core');
                    }

                    // Resolver code
                    // @todo: move to resolver
                    //expose the virtual language directory to the rest of the cms
                    //please do not access this variable directly, use Env::get().
                    $virtualLanguageDirectory = '/'.$url->getLangDir();
                    \Env::set('virtualLanguageDirectory', $virtualLanguageDirectory);
                    // TODO: this constanst used to be located in config/set_constants.php, but needed to be relocated to this very place,
                    // because it depends on Env::get('virtualLanguageDirectory').
                    // Find an other solution; probably best is to replace CONTREXX_SCRIPT_PATH by a prettier method
                    define('CONTREXX_SCRIPT_PATH',
                        ASCMS_PATH_OFFSET.
                        \Env::get('virtualLanguageDirectory').
                        '/'.
                        CONTREXX_DIRECTORY_INDEX);
                    break;
                    
                case 3:
                    // Initialize the navigation
                    $objNavbar = new \Navigation($pageId, $page);
                    break;
            }
        }

        /**
         * Loading ClassLoader, Env, DB, API and InitCMS
         * (Env, API and InitCMS are deprecated)
         * @global type $incDoctrineStatus
         * @global type $_CONFIG
         * @global type $_FTPCONFIG
         * @global type $objDatabase
         * @global \InitCMS $objInit
         * @global string $errorMsg 
         */
        protected function init() {
            global $incDoctrineStatus, $_CONFIG, $_FTPCONFIG, $objDatabase,
                    $objInit, $errorMsg;

            /**
             * This needs to be initialized before loading config/doctrine.php
             * Because we overwrite the Gedmo model (so we need to load our model
             * before doctrine loads the Gedmo one)
             */
            require_once(ASCMS_CORE_PATH.'/ClassLoader/ClassLoader.class.php');
            $this->cl = new \Cx\Core\ClassLoader\ClassLoader(ASCMS_DOCUMENT_ROOT, true, $this->customizingPath);

            /**
             * Environment repository
             */
            require_once($this->cl->getFilePath(ASCMS_CORE_PATH.'/Env.class.php'));
            \Env::set('cx', $this);
            \Env::set('ClassLoader', $this->cl);            
            \Env::set('config', $_CONFIG);
            \Env::set('ftpConfig', $_FTPCONFIG);

            /**
             * Include all the required files.
             */
            $this->cl->loadFile(ASCMS_CORE_PATH.'/API.php');
            // Temporary fix until all GET operation requests will be replaced by POSTs
            \CSRF::setFrontendMode();

            $this->db = new \Cx\Core\Db\Db();
            $objDatabase = $this->db->getAdoDb();
            \Env::set('db', $objDatabase);
            $em = $this->db->getEntityManager();
            \Env::set('em', $em);
            \Env::set('pageguard', new \PageGuard($this->db->getAdoDb()));

            \DBG::set_adodb_debug_mode();

            createModuleConversionTables();

            // Initialize base system
            $objInit = new \InitCMS($this->mode == self::MODE_FRONTEND ? 'frontend' : 'backend', \Env::em());
            \Env::set('init', $objInit);
        }

        protected function loadContent() {
            global $objTemplate, $page_content, $boolShop, $moduleStyleFile,
                    $moduleManager, $plainSection, $objDatabase, $_CORELANG,
                    $subMenuTitle, $objFWUser, $act, $objInit, $plainCmd, $_ARRAYLANG;

            if ($this->mode == self::MODE_FRONTEND) {
                $this->setPreContentLoadPlaceholders($objTemplate);        
                //replace the {NODE_<ID>_<LANG>}- placeholders
                \LinkGenerator::parseTemplate($page_content);

                $boolShop = false;
                $moduleStyleFile = null;
            } else {
                // Skip the nav/language bar for modules which don't make use of either.
                // TODO: Remove language selector for modules which require navigation but bring their own language management.
                $skipMaster = array('content');
                if (in_array($plainCmd, $skipMaster)) {
                    $objTemplate->addBlockfile('CONTENT_OUTPUT', 'content_master', 'content_master_stripped.html');
                } else {
                    $objTemplate->addBlockfile('CONTENT_OUTPUT', 'content_master', 'content_master.html');
                }
                $plainSection = $plainCmd;
                //var_dump($plainCmd);
            }

            // this is a 1:1 copy from backend, rewrite to be used in front- and backend
            $moduleManager = new \modulemanager();

            // init module language
            /**
             * Module specific data
             * @global array $_ARRAYLANG
             */
            try {
                $this->ch->loadComponent($this, $plainSection, $this->resolvedPage);
            } catch (\Cx\Core\Component\Controller\ComponentException $e) {
                try {
                    $em = \Env::get('em');
                    $moduleManager->loadModule($plainSection, $this->cl, $objDatabase, $_CORELANG, $subMenuTitle, $objTemplate, $objFWUser, $act, $objInit, $_ARRAYLANG, $em, $this);
                } catch (\ModuleManagerException $e) {
                    $_ARRAYLANG = $objInit->loadLanguageData($plainSection);
                    $moduleManager->loadLegacyModule($plainSection, $this->cl, $objDatabase, $_CORELANG, $subMenuTitle, $objTemplate, $objFWUser, $act, $objInit, $_ARRAYLANG);
                }
            }
        }

        protected function setPreContentLoadPlaceholders($objTemplate) {
            global $themesPages, $page_template, $page, $url, $objNavbar,
                    $page_content, $_CONFIG, $page_title, $objInit;

            $objTemplate->setTemplate($themesPages['index']);
            $objTemplate->addBlock('CONTENT_FILE', 'page_template', $page_template);
            $objNavbar->setLanguagePlaceholders($page, $url, $objTemplate);

            // Set global content variables.
            $page_content = str_replace('{PAGE_URL}',        htmlspecialchars($objInit->getPageUri()), $page_content);
            $page_content = str_replace('{STANDARD_URL}',    $objInit->getUriBy('smallscreen', 0),     $page_content);
            $page_content = str_replace('{MOBILE_URL}',      $objInit->getUriBy('smallscreen', 1),     $page_content);
            $page_content = str_replace('{PRINT_URL}',       $objInit->getUriBy('printview', 1),       $page_content);
            $page_content = str_replace('{PDF_URL}',         $objInit->getUriBy('pdfview', 1),         $page_content);
            $page_content = str_replace('{APP_URL}',         $objInit->getUriBy('appview', 1),         $page_content);
            $page_content = str_replace('{LOGOUT_URL}',      $objInit->getUriBy('section', 'logout'),  $page_content);
            $page_content = str_replace('{TITLE}',           $page_title, $page_content);
            $page_content = str_replace('{CONTACT_EMAIL}',   isset($_CONFIG['contactFormEmail']) ? contrexx_raw2xhtml($_CONFIG['contactFormEmail']) : '', $page_content);
            $page_content = str_replace('{CONTACT_COMPANY}', isset($_CONFIG['contactCompany'])   ? contrexx_raw2xhtml($_CONFIG['contactCompany'])   : '', $page_content);
            $page_content = str_replace('{CONTACT_ADDRESS}', isset($_CONFIG['contactAddress'])   ? contrexx_raw2xhtml($_CONFIG['contactAddress'])   : '', $page_content);
            $page_content = str_replace('{CONTACT_ZIP}',     isset($_CONFIG['contactZip'])       ? contrexx_raw2xhtml($_CONFIG['contactZip'])       : '', $page_content);
            $page_content = str_replace('{CONTACT_PLACE}',   isset($_CONFIG['contactPlace'])     ? contrexx_raw2xhtml($_CONFIG['contactPlace'])     : '', $page_content);
            $page_content = str_replace('{CONTACT_COUNTRY}', isset($_CONFIG['contactCountry'])   ? contrexx_raw2xhtml($_CONFIG['contactCountry'])   : '', $page_content);
            $page_content = str_replace('{CONTACT_PHONE}',   isset($_CONFIG['contactPhone'])     ? contrexx_raw2xhtml($_CONFIG['contactPhone'])     : '', $page_content);
            $page_content = str_replace('{CONTACT_FAX}',     isset($_CONFIG['contactFax'])       ? contrexx_raw2xhtml($_CONFIG['contactFax'])       : '', $page_content);
        }

        protected function setPostContentLoadPlaceholders() {
            global $objInit, $page_title, $page_metatitle, $page_catname, $_CONFIG,
                    $page_keywords, $page_desc, $page_robots, $pageCssName,
                    $objNavbar, $themesPages, $license, $boolShop, $objCounter,
                    $objBanner, $_CORELANG, $page_modified, $page, $url;

            if ($this->mode == self::MODE_BACKEND) {
                $this->template->setGlobalVariable(array(
                    'TXT_FRONTEND'              => $_CORELANG['TXT_FRONTEND'],
                    'TXT_UPGRADE'               => $_CORELANG['TXT_UPGRADE'],
                ));
                $this->template->setVariable(array(
                    'TXT_LOGOUT'                => $_CORELANG['TXT_LOGOUT'],
                    'TXT_PAGE_ID'               => $_CORELANG['TXT_PAGE_ID'],
                    'CONTAINER_BACKEND_CLASS'   => 'backend',
                    'CONTREXX_CHARSET'          => CONTREXX_CHARSET,
                ));
                return;
            }

            // set global template variables
            $this->template->setVariable(array(
                'CHARSET'                        => $objInit->getFrontendLangCharset(),
                'TITLE'                          => $page_title,
                'METATITLE'                      => $page_metatitle,
                'NAVTITLE'                       => $page_catname,
                'GLOBAL_TITLE'                   => $_CONFIG['coreGlobalPageTitle'],
                'DOMAIN_URL'                     => $_CONFIG['domainUrl'],
                'PATH_OFFSET'                    => ASCMS_PATH_OFFSET,
                'BASE_URL'                       => ASCMS_PROTOCOL.'://'.$_CONFIG['domainUrl'].ASCMS_PATH_OFFSET,
                'METAKEYS'                       => $page_keywords,
                'METADESC'                       => $page_desc,
                'METAROBOTS'                     => $page_robots,
                'CONTENT_TITLE'                  => '<span id="fe_PreviewTitle">'.$page_title.'</span>',
                'CSS_NAME'                       => $pageCssName,
                'STANDARD_URL'                   => $objInit->getUriBy('smallscreen', 0),
                'MOBILE_URL'                     => $objInit->getUriBy('smallscreen', 1),
                'PRINT_URL'                      => $objInit->getUriBy('printview', 1),
                'PDF_URL'                        => $objInit->getUriBy('pdfview', 1),
                'APP_URL'                        => $objInit->getUriBy('appview', 1),
                'LOGOUT_URL'                     => $objInit->getUriBy('section', 'logout'),
                'PAGE_URL'                       => htmlspecialchars($objInit->getPageUri()),
                'CURRENT_URL'                    => $objInit->getCurrentPageUri(),
                'DATE'                           => showFormattedDate(),
                'TIME'                           => date('H:i', time()),
                'NAVTREE'                        => $objNavbar->getTrail(),
                'SUBNAVBAR_FILE'                 => $objNavbar->getSubnavigation($themesPages['subnavbar'], $license,$boolShop),
                'SUBNAVBAR2_FILE'                => $objNavbar->getSubnavigation($themesPages['subnavbar2'], $license,$boolShop),
                'SUBNAVBAR3_FILE'                => $objNavbar->getSubnavigation($themesPages['subnavbar3'], $license,$boolShop),
                'NAVBAR_FILE'                    => $objNavbar->getNavigation($themesPages['navbar'], $license, $boolShop),
                'NAVBAR2_FILE'                   => $objNavbar->getNavigation($themesPages['navbar2'], $license, $boolShop),
                'NAVBAR3_FILE'                   => $objNavbar->getNavigation($themesPages['navbar3'], $license, $boolShop),
                'ONLINE_USERS'                   => $objCounter->getOnlineUsers(),
                'VISITOR_NUMBER'                 => $objCounter->getVisitorNumber(),
                'COUNTER'                        => $objCounter->getCounterTag(),
                'BANNER'                         => isset($objBanner) ? $objBanner->getBannerJS() : '',
                'VERSION'                        => contrexx_raw2xhtml($_CONFIG['coreCmsName']),
                'LANGUAGE_NAVBAR'                => $objNavbar->getFrontendLangNavigation($page, $url),
                'LANGUAGE_NAVBAR_SHORT'          => $objNavbar->getFrontendLangNavigation($page, $url, true),
                'ACTIVE_LANGUAGE_NAME'           => $objInit->getFrontendLangName(),
                'RANDOM'                         => md5(microtime()),
                'TXT_SEARCH'                     => $_CORELANG['TXT_SEARCH'],
                'MODULE_INDEX'                   => MODULE_INDEX,
                'LOGIN_URL'                      => '<a href="' . $objInit->getUriBy('section', 'login') . '">' . $_CORELANG['TXT_FRONTEND_EDITING_LOGIN'] . '</a>',
                'JAVASCRIPT'                     => 'javascript_inserting_here',
                'TXT_CORE_LAST_MODIFIED_PAGE'    => $_CORELANG['TXT_CORE_LAST_MODIFIED_PAGE'],
                'LAST_MODIFIED_PAGE'             => date(ASCMS_DATE_FORMAT_DATE, $page_modified),
                'CONTACT_EMAIL'                  => isset($_CONFIG['contactFormEmail']) ? contrexx_raw2xhtml($_CONFIG['contactFormEmail']) : '',
                'CONTACT_COMPANY'                => isset($_CONFIG['contactCompany'])   ? contrexx_raw2xhtml($_CONFIG['contactCompany'])   : '',
                'CONTACT_ADDRESS'                => isset($_CONFIG['contactAddress'])   ? contrexx_raw2xhtml($_CONFIG['contactAddress'])   : '',
                'CONTACT_ZIP'                    => isset($_CONFIG['contactZip'])       ? contrexx_raw2xhtml($_CONFIG['contactZip'])       : '',
                'CONTACT_PLACE'                  => isset($_CONFIG['contactPlace'])     ? contrexx_raw2xhtml($_CONFIG['contactPlace'])     : '',
                'CONTACT_COUNTRY'                => isset($_CONFIG['contactCountry'])   ? contrexx_raw2xhtml($_CONFIG['contactCountry'])   : '',
                'CONTACT_PHONE'                  => isset($_CONFIG['contactPhone'])     ? contrexx_raw2xhtml($_CONFIG['contactPhone'])     : '',
                'CONTACT_FAX'                    => isset($_CONFIG['contactFax'])       ? contrexx_raw2xhtml($_CONFIG['contactFax'])       : '',
                'FACEBOOK_LIKE_IFRAME'           => '<div id="fb-root"></div>
                                                    <script type="text/javascript">
                                                        (function(d, s, id) {
                                                            var js, fjs = d.getElementsByTagName(s)[0];
                                                            if (d.getElementById(id)) return;
                                                            js = d.createElement(s); js.id = id;
                                                            js.src = "//connect.facebook.net/de_DE/all.js#xfbml=1";
                                                            fjs.parentNode.insertBefore(js, fjs);
                                                        }(document, \'script\', \'facebook-jssdk\'));
                                                    </script>
                                                    <div class="fb-like" data-href="http://'.$_CONFIG['domainUrl'].$objInit->getCurrentPageUri().'" data-send="false" data-layout="button_count" data-show-faces="false" data-font="segoe ui"></div>',
                'GOOGLE_PLUSONE'                 => '<div class="g-plusone" data-href="http://'.$_CONFIG['domainUrl'].$objInit->getCurrentPageUri().'"></div>
                                                    <script type="text/javascript">
                                                        window.___gcfg = {lang: \'de\'};

                                                        (function() {
                                                            var po = document.createElement(\'script\'); po.type = \'text/javascript\'; po.async = true;
                                                            po.src = \'https://apis.google.com/js/plusone.js\';
                                                            var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(po, s);
                                                        })();
                                                    </script>',
                'GOOGLE_ANALYTICS'               => '<script type="text/javascript">
                                                        var _gaq = _gaq || [];
                                                        _gaq.push([\'_setAccount\', \''.(isset($_CONFIG['googleAnalyticsTrackingId']) ? contrexx_raw2xhtml($_CONFIG['googleAnalyticsTrackingId']) : '').'\']);
                                                        _gaq.push([\'_trackPageview\']);

                                                        (function() {
                                                            var ga = document.createElement(\'script\'); ga.type = \'text/javascript\'; ga.async = true;
                                                            ga.src = (\'https:\' == document.location.protocol ? \'https://ssl\' : \'http://www\') + \'.google-analytics.com/ga.js\';
                                                            var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(ga, s);
                                                        })();
                                                    </script>',
            ));
        }

        protected function finalize() {
            global $themesPages, $moduleStyleFile, $objCache, $_CONFIG,
                    $objInit, $page_title, $parsingtime, $starttime, $subMenuTitle,
                    $_CORELANG, $objFWUser, $plainCmd, $cmd, $startTime;

            if ($this->mode == self::MODE_FRONTEND) {
                // parse system
                $time = $this->stopTimer();
                $this->template->setVariable('PARSING_TIME', $time);

                $themesPages['sidebar'] = str_replace('{STANDARD_URL}',    $objInit->getUriBy('smallscreen', 0),    $themesPages['sidebar']);
                $themesPages['sidebar'] = str_replace('{MOBILE_URL}',      $objInit->getUriBy('smallscreen', 1),    $themesPages['sidebar']);
                $themesPages['sidebar'] = str_replace('{PRINT_URL}',       $objInit->getUriBy('printview', 1),      $themesPages['sidebar']);
                $themesPages['sidebar'] = str_replace('{PDF_URL}',         $objInit->getUriBy('pdfview', 1),        $themesPages['sidebar']);
                $themesPages['sidebar'] = str_replace('{APP_URL}',         $objInit->getUriBy('appview', 1),        $themesPages['sidebar']);
                $themesPages['sidebar'] = str_replace('{LOGOUT_URL}',      $objInit->getUriBy('section', 'logout'), $themesPages['sidebar']);
                $themesPages['sidebar'] = str_replace('{CONTACT_EMAIL}',   isset($_CONFIG['contactFormEmail']) ? contrexx_raw2xhtml($_CONFIG['contactFormEmail']) : '', $themesPages['sidebar']);
                $themesPages['sidebar'] = str_replace('{CONTACT_COMPANY}', isset($_CONFIG['contactCompany'])   ? contrexx_raw2xhtml($_CONFIG['contactCompany'])   : '', $themesPages['sidebar']);
                $themesPages['sidebar'] = str_replace('{CONTACT_ADDRESS}', isset($_CONFIG['contactAddress'])   ? contrexx_raw2xhtml($_CONFIG['contactAddress'])   : '', $themesPages['sidebar']);
                $themesPages['sidebar'] = str_replace('{CONTACT_ZIP}',     isset($_CONFIG['contactZip'])       ? contrexx_raw2xhtml($_CONFIG['contactZip'])       : '', $themesPages['sidebar']);
                $themesPages['sidebar'] = str_replace('{CONTACT_PLACE}',   isset($_CONFIG['contactPlace'])     ? contrexx_raw2xhtml($_CONFIG['contactPlace'])     : '', $themesPages['sidebar']);
                $themesPages['sidebar'] = str_replace('{CONTACT_COUNTRY}', isset($_CONFIG['contactCountry'])   ? contrexx_raw2xhtml($_CONFIG['contactCountry'])   : '', $themesPages['sidebar']);
                $themesPages['sidebar'] = str_replace('{CONTACT_PHONE}',   isset($_CONFIG['contactPhone'])     ? contrexx_raw2xhtml($_CONFIG['contactPhone'])     : '', $themesPages['sidebar']);
                $themesPages['sidebar'] = str_replace('{CONTACT_FAX}',     isset($_CONFIG['contactFax'])       ? contrexx_raw2xhtml($_CONFIG['contactFax'])       : '', $themesPages['sidebar']);

                $this->template->setVariable(array(
                    'SIDEBAR_FILE' => $themesPages['sidebar'],
                    'JAVASCRIPT_FILE' => $themesPages['javascript'],
                    'BUILDIN_STYLE_FILE' => $themesPages['buildin_style'],
                    'DATE_YEAR' => date('Y'),
                    'DATE_MONTH' => date('m'),
                    'DATE_DAY' => date('d'),
                    'DATE_TIME' => date('H:i'),
                    'BUILDIN_STYLE_FILE' => $themesPages['buildin_style'],
                    'JAVASCRIPT_LIGHTBOX' =>
                        '<script type="text/javascript" src="lib/lightbox/javascript/mootools.js"></script>
                        <script type="text/javascript" src="lib/lightbox/javascript/slimbox.js"></script>',
                    'JAVASCRIPT_MOBILE_DETECTOR' =>
                        '<script type="text/javascript" src="lib/mobiledetector.js"></script>',
                ));

                if (!empty($moduleStyleFile))
                    $this->template->setVariable(
                        'STYLE_FILE',
                        "<link rel=\"stylesheet\" href=\"$moduleStyleFile\" type=\"text/css\" media=\"screen, projection\" />"
                    );

                if (isset($_GET['pdfview']) && intval($_GET['pdfview']) == 1) {
                    $this->cl->loadFile(ASCMS_CORE_PATH.'/pdf.class.php');
                    $objPDF          = new PDF();
                    $objPDF->title   = $page_title.(empty($page_title) ? null : '.pdf');
                    $objPDF->content = $this->template->get();
                    $objPDF->Create();
                    exit;
                }

                //enable gzip compressing of the output - up to 75% smaller responses!
                //commented out because of certain php.inis generating a
                //WARNING: ob_start(): output handler 'ob_gzhandler' cannot be used after 'URL-Rewriter
                //ob_start("ob_gzhandler");

                // fetch the parsed webpage
                $endcode = $this->template->get();

                /**
                 * Get all javascripts in the code, replace them with nothing, and register the js file
                 * to the javascript lib. This is because we don't want something twice, and there could be
                 * a theme that requires a javascript, which then could be used by a module too and therefore would
                 * be loaded twice.
                 */
                /* Finds all uncommented script tags, strips them out of the HTML and
                 * stores them internally so we can put them in the placeholder later
                 * (see JS::getCode() below)
                 */
                \JS::findJavascripts($endcode);
                /*
                 * Proposal:  Use this
                 *     $endcode = preg_replace_callback('/<script\s.*?src=(["\'])(.*?)(\1).*?\/?>(?:<\/script>)?/i', array('JS', 'registerFromRegex'), $endcode);
                 * and change JS::registerFromRegex to use index 2
                 */
                // i know this is ugly, but is there another way
                $endcode = str_replace('javascript_inserting_here', \JS::getCode(), $endcode);

                // do a final replacement of all those node-urls ({NODE_<ID>_<LANG>}- placeholders) that haven't been captured earlier
                $endcode = preg_replace('/\\[\\[([A-Z0-9_-]+)\\]\\]/', '{\\1}', $endcode);
                \LinkGenerator::parseTemplate($endcode);

                // replace links from before contrexx 3
                $ls = new \LinkSanitizer(
                    ASCMS_PATH_OFFSET.\Env::get('virtualLanguageDirectory').'/',
                    $endcode);
                $endcode = $ls->replace();

                echo $endcode;

                $objCache->endCache();
            } else {
                // page parsing
                $parsingTime = $this->stopTimer();
//                var_dump($parsingTime);
    /*echo ($finishTime[0] - $startTime[0]) . '<br />';
    if (!isset($_SESSION['asdf1']) || isset($_GET['reset'])) {
        $_SESSION['asdf1'] = 0;
        $_SESSION['asdf2'] = 0;
    }
    echo $_SESSION['asdf1'] . '<br />';
    if ($_SESSION['asdf1'] > 0) {
        echo $_SESSION['asdf2'] / $_SESSION['asdf1'];
    }
    $_SESSION['asdf1']++;
    $_SESSION['asdf2'] += ($finishTime[0] - $startTime[0]);//*/
                $objAdminNav = new \adminMenu($plainCmd);
                $objAdminNav->getAdminNavbar();
                $this->template->setVariable(array(
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
                //    'CONTENT_WYSIWYG_CODE' => get_wysiwyg_code(),
                    // Mind: The module index is not used in any non-module template
                    // for the time being, but is provided for future use and convenience.
                    'MODULE_INDEX' => MODULE_INDEX,
                    // The Shop module for one heavily uses custom JS code that is properly
                    // handled by that class -- finally
                    'JAVASCRIPT' => \JS::getCode(),
                ));


                // Style parsing
                if (file_exists(ASCMS_ADMIN_TEMPLATE_PATH.'/css/'.$cmd.'.css')) {
                    // check if there's a css file in the core section
                    $this->template->setVariable('ADD_STYLE_URL', ASCMS_ADMIN_TEMPLATE_WEB_PATH.'/css/'.$cmd.'.css');
                    $this->template->parse('additional_style');
                } elseif (file_exists(ASCMS_MODULE_PATH.'/'.$cmd.'/template/backend.css')) {
                    // of maybe in the current module directory
                    $this->template->setVariable('ADD_STYLE_URL', ASCMS_MODULE_WEB_PATH.'/'.$cmd.'/template/backend.css');
                    $this->template->parse('additional_style');
                } elseif (file_exists(ASCMS_CORE_MODULE_PATH.'/'.$cmd.'/template/backend.css')) {
                    // or in the core module directory
                    $this->template->setVariable('ADD_STYLE_URL', ASCMS_CORE_MODULE_WEB_PATH.'/'.$cmd.'/template/backend.css');
                    $this->template->parse('additional_style');
                } else {
                    $this->template->hideBlock('additional_style');
                }


                //enable gzip compressing of the output - up to 75% smaller responses!
                //commented out because of certain php.inis generating a 
                //WARNING: ob_start(): output handler 'ob_gzhandler' cannot be used after 'URL-Rewriter
                //ob_start("ob_gzhandler");

                $this->template->show();
                /*echo '<pre>';
                print_r($_SESSION);
                /*echo '<b>Overall time: ' . (microtime(true) - $timeAtStart) . 's<br />';
                echo 'Max RAM usage: ' . formatBytes(memory_get_peak_usage()) . '<br />';
                echo 'End RAM usage: ' . formatBytes(memory_get_usage()) . '<br /></b>';*/
            }
        }
    }
}