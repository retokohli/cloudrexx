<?php

/**
 * Initialize CMS
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @version        1.0.0
 * @package     contrexx
 * @subpackage  core
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Class Initialize
 *
 * init CMS
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @access        public
 * @version        1.0.0
 * @package     contrexx
 * @subpackage  core
 */
class InitCMS
{
    var $defaultBackendLangId;
    var $backendLangCharset;
    var $backendLangId;

    var $defaultFrontendLangId;
    var $frontendLangCharset;
    var $frontendLangId;
    var $frontendLangName;
    var $userFrontendLangId;

    var $currentThemesId;
	var $customContentTemplate;
    var $is_home;
    var $arrLang = array();
    var $arrLangNames = array();
    var $templates = array();
    var $arrModulePath = array();

    /**
    * int $isMobileDevice
    * whether we're dealing with a mobile device.
    * values 1 or 0.
    * @see InitCMS::_initFrontendLanguage()
    * @see InitCMS::_setCustomizedThemesId()
    * @access private
    */
    private $isMobileDevice = 0;


    private $themesPath;

    /**
    * string $mode
    * frontend or backend
    */
    var $mode;


    /**
    * Constructor
    *
    */
    function __construct($mode='frontend')
    {
        global $objDatabase;

        $this->is_home=false;
        $this->mode=$mode;

        $objResult = $objDatabase->Execute("
            SELECT id, themesid, print_themes_id, pdf_themes_id,mobile_themes_id,
                   lang, name, charset, backend, frontend, is_default
              FROM ".DBPREFIX."languages
        ");
        if ($objResult) {
            while (!$objResult->EOF) {
                $this->arrLang[$objResult->fields['id']]= array(
                    'id'         => $objResult->fields['id'],
                    'themesid'   => $objResult->fields['themesid'],
                    'print_themes_id' => $objResult->fields['print_themes_id'],
                    'pdf_themes_id' => $objResult->fields['pdf_themes_id'],
                    'mobile_themes_id' => $objResult->fields['mobile_themes_id'],
                    'lang'       => $objResult->fields['lang'],
                    'name'       => $objResult->fields['name'],
                    'charset'    => $objResult->fields['charset'],
                    'backend'    => $objResult->fields['backend'],
                    'frontend'   => $objResult->fields['frontend'],
                    'is_default' => $objResult->fields['is_default']);
                $this->arrLangNames[$objResult->fields['lang']] = $objResult->fields['id'];
                if ($objResult->fields['is_default']=="true") {
                    $this->defaultBackendLangId = $objResult->fields['id'];
                    $this->defaultFrontendLangId = $objResult->fields['id'];
                }
                $objResult->MoveNext();
            }
        }
        if ($mode == 'frontend') {
            //$this->_initBackendLanguage();
            $this->getUserFrontendLangId();
        }
        $this->_initFrontendLanguage();
        $this->loadModulePaths();
    }

    /**
     * Backend language initialization
     */
    function _initBackendLanguage()
    {
        $objFWUser = FWUser::getFWUserObject();
        if ($objFWUser->objUser->login(true)) {
            $backendLangId = $objFWUser->objUser->getBackendLanguage();
        } elseif (!empty($_COOKIE['backendLangId'])) {
            $backendLangId = intval($_COOKIE['backendLangId']);
        } else {
            $backendLangId = $this->defaultBackendLangId;
        }
        if ($this->arrLang[$backendLangId]['backend'] != 1) {
            $backendLangId = $this->defaultBackendLangId;
        }
        $this->backendLangId = $this->arrLang[$backendLangId]['id'];
        $this->currentThemesId = $this->arrLang[$backendLangId]['themesid'];
        $this->backendLangCharset = $this->arrLang[$backendLangId]['charset'];
        setcookie('backendLangId', $backendLangId, time()+3600*24*30, ASCMS_PATH_OFFSET.'/');
    }


    function _initFrontendLanguage()
    {
        global $_CONFIG;

        // Frontend language initialization
        $setCookie = false;

        if (!empty($_REQUEST['setLang'])) {
            $frontendLangId = intval($_REQUEST['setLang']);
            $setCookie = true;
        } elseif (!empty($_GET['langId'])) {
            $frontendLangId = intval($_GET['langId']);
        } elseif (!empty($_POST['langId'])) {
            $frontendLangId = intval($_POST['langId']);
        } elseif (!empty($_COOKIE['langId'])) {
            $frontendLangId = intval($_COOKIE['langId']);
            $setCookie = true;
        } else {
            $frontendLangId = $this->_selectBestLanguage();
        }
        if ($this->arrLang[$frontendLangId]['frontend'] != 1) {
            $frontendLangId = $this->defaultFrontendLangId;
        }

        if ($setCookie) {
            setcookie("langId", $frontendLangId, time()+3600*24*30, ASCMS_PATH_OFFSET.'/');
        }

        if (isset($_CONFIG['useVirtualLanguagePath'])
            && $_CONFIG['useVirtualLanguagePath'] == 'on'
            && $this->mode == 'frontend'
            && empty($_SERVER['REDIRECT_CONTREXX_LANG_PREFIX'])
            && basename($_SERVER['SCRIPT_FILENAME']) != 'frontendEditing.class.php'
        ) {
            CSRF::header('Location: '.ASCMS_PATH_OFFSET.'/'.$this->arrLang[$frontendLangId]['lang'].'/'.CONTREXX_DIRECTORY_INDEX.(empty($_GET) ? '' : '?'.implode('&', array_map(create_function('$a,$b', 'return contrexx_stripslashes($a.\'=\'.$b);'), array_keys($_GET), $_GET))));
            exit;
        }

        // small screen view (mobile etc). use index.php?smallscreen=1 to
        // enable, ?smallscreen=0 to disable.
        $this->isMobileDevice = 0;
        // only set the smallscreen environment if there's actually a mobile theme defined.
        if(isset($_GET['smallscreen']) ) {
            // user wants to enable/disable smallscreen mode.
            if ($_GET['smallscreen'] && $this->arrLang[$frontendLangId]['mobile_themes_id']) {
                // enable
                setcookie('smallscreen', 1, 0, ASCMS_PATH_OFFSET.'/');
                $this->isMobileDevice = 1;
            }
            else {
                // now: either smallscreen=1 requested, but no smallscreen theme
                // available, or disabling requested. Both cases require the
                // cookie to be set to zero, so the javascript doesn't redirect
                // all the time!
                setcookie('smallscreen', 0, 0, ASCMS_PATH_OFFSET.'/');
                $this->isMobileDevice = 0;
            }
        }
        elseif(isset($_COOKIE['smallscreen'])) {
            // no need to check mobile_themes_id here: it's been checked
            // when the cookie was set.
            $this->isMobileDevice =intval($_COOKIE['smallscreen']);
        }
        else {
            // auto detection
            if($this->_is_mobile_phone() && $this->arrLang[$frontendLangId]['mobile_themes_id']) {
                // same here: only set smallscreen mode if there IS a smallscreen theme
                setcookie('smallscreen', 1, 0, ASCMS_PATH_OFFSET.'/');
                $this->isMobileDevice = 1;
            }
            else {
                // Don't even think about setting the cookie
                // to 0 in this case: 0 means the user disabled
                // smallscreen mode INTENTIONALLY! The friendly javascript
                // detector only enables smallscreen mode if the user
                // didn't decide by himself.
            }
        }

        $this->frontendLangId = $frontendLangId;
        if (isset($_GET['printview']) && $_GET['printview'] == 1) {
            $this->currentThemesId = $this->arrLang[$frontendLangId]['print_themes_id'];

        }elseif (isset($_GET['pdfview']) && $_GET['pdfview'] == 1){
            $this->currentThemesId = $this->arrLang[$frontendLangId]['pdf_themes_id'];

        }elseif ($this->isMobileDevice and $this->arrLang[$frontendLangId]['mobile_themes_id']) {
            $this->currentThemesId = $this->arrLang[$frontendLangId]['mobile_themes_id'];

        } else {
            $this->currentThemesId = $this->arrLang[$frontendLangId]['themesid'];
        }

        $this->frontendLangCharset = $this->arrLang[$frontendLangId]['charset'];
    }


    /**
     * Select best language
     *
     * Selects the best language for the client and returns
     * its name.
     *
     */
    function _selectBestLanguage()
    {
        global $_CONFIG;
        if (   isset($_CONFIG['languageDetection'])
            && $_CONFIG['languageDetection'] == 'on') {
            $arrAcceptedLanguages = $this->_getClientAcceptedLanguages();
        foreach (array_keys($arrAcceptedLanguages) as $language) {
            if (in_array($language, array_keys($this->arrLangNames))) {
                return $this->arrLangNames[$language];
            } elseif (in_array($strippedLanguage = substr($language, 0, strpos($language, '-')), array_keys($this->arrLangNames))) {
                return $this->arrLangNames[$strippedLanguage];
            }
        }
        }
        return $this->defaultFrontendLangId;
    }


    /**
     * Get client accepted languages
     *
     * Returns an array with the accepted languages and their associated quality of the client.
     *
     * @access private
     * @return array
     */
    function _getClientAcceptedLanguages()
    {
        $arrLanguages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        $arrAcceptedLanguages = array();
        foreach ($arrLanguages as $languageString) {
            $arrLanguage = explode(';q=', trim($languageString));
            $language = trim($arrLanguage[0]);
            $quality = isset($arrLanguage[1]) ? trim($arrLanguage[1]) : 1;
            $arrAcceptedLanguages[$language] = (float) $quality;
        }
        arsort($arrAcceptedLanguages, SORT_NUMERIC);
        return $arrAcceptedLanguages;
    }


    /**
    * Gets the selected User Frontend Language id
    *
    * This method is only for the backend use!
    *
    * @return   string $this->userFrontendLangId
    */
    function getUserFrontendLangId()
    {
        if (isset($_POST['userFrontendLangId']) && !empty($_POST['userFrontendLangId'])) {
            $id=intval($_POST['userFrontendLangId']);
        } elseif (isset($_SESSION['userFrontendLangId']) && !empty($_SESSION['userFrontendLangId'])) {
            $id = intval($_SESSION['userFrontendLangId']);
        } else {
            $id = $this->defaultFrontendLangId;
        }

        if($this->arrLang[$id]['frontend']!=1){
            $id=$this->defaultFrontendLangId;
        }

        $this->userFrontendLangId= $id;
        $_SESSION['userFrontendLangId'] = $id;
        return $this->userFrontendLangId;
    }


    function getDefaultFrontendLangId()
    {
          return $this->defaultFrontendLangId;
    }


    function getDefaultBackendLangId()
    {
          return $this->defaultFrontendLangId;
    }


    function getFrontendLangId()
    {
          return $this->frontendLangId;
    }


    function getFrontendLangName()
    {
        return $this->arrLang[$this->frontendLangId]['lang'];
    }


    function getBackendLangId()
    {
          return $this->backendLangId;
    }


    /**
    * gets all languages as an array
    *
    * @access public
    * @return array $arrLang
    */
    function getLanguageArray()
    {
        return $this->arrLang;
    }


    /**
    * gets the current language charset for the html header
    *
    * @param string   charset
    * @access public
    */
    function getFrontendLangCharset()
    {
        if (empty($this->frontendLangCharset)){
            return CONTREXX_CHARSET;
        }else{
            return $this->frontendLangCharset;
        }
    }


    /**
    * gets the current language charset for the html header
    *
    * @param string   charset
    * @access public
    */
    function getBackendLangCharset()
    {
        if (empty($this->backendLangCharset)){
            return CONTREXX_CHARSET;
        }else{
            return $this->backendLangCharset;
        }
    }


    /**
    * getDefaultLangId
    *
    * @param string   current language id
    * @access public
    */
    function getFrontendDefaultLangId()
    {
          return $this->defaultFrontendLangId;
    }


    /**
    * getDefaultLangId
    *
    * @param string   current language id
    * @access public
    */
    function getBackendDefaultLangId()
    {
          return $this->defaultBackendLangId;
    }


    /**
    * getTemplate Function
    *
    * @param  array    Template strings
    * @access public
    */
    function getTemplates()
    {
        global $objDatabase;
		
		if (isset($_GET['custom_content']) && preg_match('/^[a-zA-Z0-9_]+$/', $_GET['custom_content'])) {
			$this->customContentTemplate=$_GET['custom_content'];
		}
		
        if(isset($_GET['preview']) && intval($_GET['preview'])){
            $objRS = $objDatabase->SelectLimit("
                SELECT id
                                        FROM ".DBPREFIX."skins
                 WHERE id=".intval($_GET['preview']), 1
            );
            if($objRS->RecordCount()==1){
                $this->currentThemesId=intval($_GET['preview']);
            }
        }

        $objResult = $objDatabase->SelectLimit("
            SELECT  id,
                                    themesname,
                                    foldername
                               FROM ".DBPREFIX."skins
                              WHERE id = '$this->currentThemesId'",1);
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $themesPath = $objResult->fields['foldername'];
                $objResult->MoveNext();
            }
        }
        #die ("themesid={$this->currentThemesId}, name = $themesPath");

        $this->themesPath = $themesPath;

        $this->templates['index'] = file_get_contents(ASCMS_THEMES_PATH.'/'.$themesPath.'/index.html');
        $this->templates['content'] = file_get_contents(ASCMS_THEMES_PATH.'/'.$themesPath.'/content.html');
        $this->templates['home'] = file_get_contents(ASCMS_THEMES_PATH.'/'.$themesPath.'/home.html');
        $this->templates['navbar'] = file_get_contents(ASCMS_THEMES_PATH.'/'.$themesPath.'/navbar.html');
        $this->templates['subnavbar'] = file_get_contents(ASCMS_THEMES_PATH.'/'.$themesPath.'/subnavbar.html');
        $this->templates['subnavbar2'] = file_exists(ASCMS_THEMES_PATH.'/'.$themesPath.'/subnavbar2.html') ? file_get_contents(ASCMS_THEMES_PATH.'/'.$themesPath.'/subnavbar2.html') : '';
        $this->templates['subnavbar3'] = file_exists(ASCMS_THEMES_PATH.'/'.$themesPath.'/subnavbar3.html') ? file_get_contents(ASCMS_THEMES_PATH.'/'.$themesPath.'/subnavbar3.html') : '';
        $this->templates['sidebar'] = file_get_contents(ASCMS_THEMES_PATH.'/'.$themesPath.'/sidebar.html');
        $this->templates['shopnavbar'] = file_get_contents(ASCMS_THEMES_PATH.'/'.$themesPath.'/shopnavbar.html');
        $this->templates['headlines'] = file_get_contents(ASCMS_THEMES_PATH.'/'.$themesPath.'/headlines.html');
        $this->templates['javascript'] = file_get_contents(ASCMS_THEMES_PATH.'/'.$themesPath.'/javascript.js');
        //$this->templates['style'] = file_get_contents(ASCMS_THEMES_PATH.'/'.$themesPath.'/style.css');
        $this->templates['buildin_style'] = file_get_contents(ASCMS_THEMES_PATH.'/'.$themesPath.'/buildin_style.css');
        @$this->templates['calendar_headlines'] = file_get_contents(ASCMS_THEMES_PATH.'/'.$themesPath.'/events.html');
        @$this->templates['directory_content'] = file_get_contents(ASCMS_THEMES_PATH.'/'.$themesPath.'/directory.html');
        @$this->templates['forum_content'] = file_get_contents(ASCMS_THEMES_PATH.'/'.$themesPath.'/forum.html');
        @$this->templates['podcast_content'] = file_get_contents(ASCMS_THEMES_PATH.'/'.$themesPath.'/podcast.html');
        @$this->templates['blog_content'] = file_get_contents(ASCMS_THEMES_PATH.'/'.$themesPath.'/blog.html');
        @$this->templates['immo'] = file_get_contents(ASCMS_THEMES_PATH.'/'.$themesPath.'/immo.html');

		if ($this->customContentTemplate) {
          	$this->templates['content'] = file_get_contents(ASCMS_THEMES_PATH.'/'.$themesPath.'/content_'.$this->customContentTemplate.'.html');
		}
		
		$template_files = scandir(ASCMS_THEMES_PATH.'/'.$themesPath);
		foreach ($template_files as $f) {
			$match = '';
			if (preg_match('/^(content|home)_(.+).html$/', $f, $match)) {
				$this->templates['custom_content'][$match[2]] = file_get_contents(ASCMS_THEMES_PATH.'/'.$themesPath.'/'.$f);
			}
		}

        return $this->templates;
    }

    /**
     * Return the current themes path
     *
     * @access public
     * @author Stefan Heinemann
     * @return string
     */
    public function getCurrentThemesPath()
    {
        return $this->themesPath;
    }


    function loadModulePaths()
    {
        global $objDatabase;

        // generate "module paths" array
        $query = "SELECT name, is_core FROM ".DBPREFIX."modules";
        $objResult = $objDatabase->Execute($query);
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                if(strlen($objResult->fields['name'])>0){
                    switch($objResult->fields['name']){
                        case 'core':
                            $this->arrModulePath[$objResult->fields['name']] = ASCMS_DOCUMENT_ROOT.'/lang/';
                            break;
                        case 'home':
                            // home is not a real module
                            break;
                        default:
                        $this->arrModulePath[$objResult->fields['name']] = ($objResult->fields['is_core'] == 1 ? ASCMS_CORE_MODULE_PATH : ASCMS_MODULE_PATH).'/'.$objResult->fields['name'].'/lang/';
                    }
                }
                $objResult->MoveNext();
            }
            // add special modules
            $this->arrModulePath['media'] = ASCMS_CORE_MODULE_PATH.'/media/lang/';;
        }
    }


    /**
    *  initialise the setting array
    *
    * @return    array   $arrayLanguageSettings
    */
    function loadLanguageData($module = '')
    {
        global $objInit, $_CORELANG, $_CONFIG, $objDatabase;

        $_ARRAYLANG = array();
        if ($objInit->mode == 'backend') {
            $langId = $this->backendLangId;
        } else {
            $langId = $this->frontendLangId;
        }

        // check which module will be loaded
        if(empty($module)){
            if ($objInit->mode == 'backend') {
                $module = isset($_REQUEST['cmd']) ? addslashes(strip_tags($_REQUEST['cmd'])) : 'core';
            } else {
                $module = isset($_REQUEST['section']) ? addslashes(strip_tags($_REQUEST['section'])) : 'core';
            }
        }

        if (preg_match('/^media\d+$/', $module)) {
            $module = 'media';
        }

        // change module for core components
        if (!array_key_exists($module,$objInit->arrModulePath) && $module != 'media') {
            $module = '';
        } else {
            // check if the language file exist
            $path = $objInit->arrModulePath[$module].$objInit->arrLang[$langId]['lang'].'/'.$objInit->mode.'.php';
               if(!file_exists($path)){
                $langId = $objInit->mode == 'backend' ? $objInit->getBackendDefaultLangId() : $objInit->getFrontendDefaultLangId();
                $path = $objInit->arrModulePath[$module].$objInit->arrLang[$langId]['lang'].'/'.$objInit->mode.'.php';
                if(!file_exists($path)){
                    $path = '';
                }
            }
        }

        // load variables
        if(empty($module)){
            return $_CORELANG;
        } else {
            if(!empty($path)){
                //require_once($path);
                require($path);
                // remove escape characters
                foreach (array_keys($_ARRAYLANG) as $langTxtId) {
                    $_ARRAYLANG[$langTxtId] = ereg_replace("\\\"", "\"", $_ARRAYLANG[$langTxtId]);
                    if (isset($_CONFIG['langDebugIds']) && $_CONFIG['langDebugIds'] == 'on') {
                        $objRS = $objDatabase->Execute("SELECT id FROM ".DBPREFIX."modules WHERE name = '$module' LIMIT 1");
                        $moduleID = $objRS->fields['id'];
                        $objRS = $objDatabase->SelectLimit("SELECT id FROM ".DBPREFIX."language_variable_names
                                                        WHERE module_id = $moduleID
                                                        AND name = '$langTxtId'", 1);
                        if($objRS){
                            $_ARRAYLANG[$langTxtId] .= " ( ".$objRS->fields['id']." )";
                        }
                    }
                }
                return $_ARRAYLANG;
            } else {
                //die("init::loadLanguageData() error (".$objInit->arrModulePath[$module].$objInit->arrLang[$_LANGID]['lang'].'/'.$objInit->mode.'.php'.")");
                return $_CORELANG;
            }
        }
    }


    /**
     * Returns the current page ID
     *
     * Also define()s the global MODULE_ID constant according to the value of
     * the module ID.
     * @global  ADONewConnection  $objDatabase
     * @param   integer           $page_id      The optional page ID
     * @param   string            $section      The optional section/cmd parameter value
     * @param   string            $command      The optional cmd/act parameter value
     * @param   integer           $history_id   The optional history ID
     * @return  integer           $page_id      The page ID
     * @author  Reto Kohli <reto.kohli@comvation.com> (Version 2.1)
     * @version 2.1
     */
    function getPageID($page_id=0, $section='', $command='', $history_id=0)
    {
        global $objDatabase;

        switch ($section) {
            case 'home':
                $this->is_home = true;
                $section = 'home';
                $command = '';
                break;
            case 'logout':
                $section = 'login';
                break;
            case 'media':
                $section .= (MODULE_INDEX == '' ? 1 : MODULE_INDEX);
                break;
        }
        if (empty($page_id)) {
            if (empty($section)) {
                $this->is_home = true;
                $section = 'home';
                $command = '';
            }
            // if the section is given, we need to search the command too,
            // even if it's empty. Otherwise, on ?section=access it could be
            // that another "access" page shows up as the cmd is not explicitly
            // defined as empty.
            $query = "
                  SELECT n.catid, n.themes_id, n.module
                    FROM ".DBPREFIX."modules AS m
                   INNER JOIN ".DBPREFIX."content_navigation AS n
                      ON n.module=m.id
                   WHERE 1
                   ".(empty($section) ? '' : " AND m.name='$section'")."
                   ".(empty($section) ? '' : " AND n.cmd ='$command'")."
                     AND n.lang=".FRONTEND_LANG_ID."
                   ORDER BY parcat ASC
            ";
            $objResult = $objDatabase->SelectLimit($query, 1);
            if ($objResult && !$objResult->EOF) {
                $page_id = $objResult->fields['catid'];
            }
            if (!$page_id) {
                CSRF::header('Location: index.php?section=error');
                exit;
            }
            $this->_setCustomizedThemesId($objResult->fields['themes_id']);
            define('MODULE_ID', $objResult->fields['module']);
            return $page_id;
        }
        if (empty($history_id)) {
            $query = "
                SELECT themes_id, custom_content
                  FROM ".DBPREFIX."content_navigation
                 WHERE catid=$page_id
            ";
        } else {
            $query = "
                SELECT themes_id, custom_content
                  FROM ".DBPREFIX."content_navigation_history
                 WHERE id=$history_id
            ";
        }
        $objResult = $objDatabase->SelectLimit($query, 1);
        if ($objResult !== false) {
          if (!$objResult->EOF) {
            $this->_setCustomizedThemesId($objResult->fields['themes_id']);
            $this->customContentTemplate = $objResult->fields['custom_content'];
          }
        }

        define('MODULE_ID', null);
        return $page_id;
    }


    /**
     * OBSOLETE -- Kept for informational purposes only
     * Gets the page id
     *
     * gets the current pageId
     * @global   ADONewConnection
     * @param     integer    $catID
     * @param     string        $command
     * @return    integer    $catID
     */
    function getPageID_old($catID = 0, $section='', $command='', $historyId=0)
    {
        global $objDatabase;

        $langId = $this->getFrontendLangId();
        switch ($section) {
            case 'home':
                $this->is_home = true;
                break;
            case 'logout':
                $section = 'login';
                break;
			case 'media':
                $section .= (MODULE_INDEX == '') ? 1 : intval(MODULE_INDEX);
                break;

            default:
        }
        if (empty($catID)) {
            if (empty($section) && empty($command)) {
                $query = 'SELECT n.catid AS catid,
                               n.module AS module,
                               n.themes_id AS themes_id
                          FROM '.DBPREFIX.'modules AS m
                    INNER JOIN '.DBPREFIX.'content_navigation AS n
                    ON n.module = m.id
                         WHERE m.name = \'home\'
                           AND n.lang='.$langId;
                $objResult = $objDatabase->SelectLimit($query, 1);
                if ($objResult !== false) {
                    $catID=$objResult->fields['catid'];
                    $this->_setCustomizedThemesId($objResult->fields['themes_id']);
                    $this->is_home=true;
                } else {
                    CSRF::header('Location: index.php?section=error');
                    exit;
                }
            }
            // section without command is given!
            elseif (!empty($section) && empty($command)) {
                $query='SELECT n.catid,
                               n.themes_id
                         FROM '.DBPREFIX.'modules AS m
                   INNER JOIN '.DBPREFIX.'content_navigation AS n
                           ON n.module = m.id
                        WHERE m.name = \''.$section.'\'
                          AND n.cmd = \'\'
                          AND n.lang='.$langId.'
                        ORDER BY parcat ASC';

                //$query="SELECT catid FROM ".DBPREFIX."content_navigation WHERE section = '$section' LIMIT 1"
                $objResult = $objDatabase->SelectLimit($query, 1);
                if ($objResult !== false) {
                    if (!$objResult->EOF) {
                        $catID = $objResult->fields['catid'];
                        $this->_setCustomizedThemesId($objResult->fields['themes_id']);
                    }
                } else {
                    CSRF::header('Location: index.php?section=error');
                    exit;
                }
            } else {
                $query='SELECT n.catid,
                               n.themes_id
                          FROM '.DBPREFIX.'content_navigation AS n
                    INNER JOIN '.DBPREFIX.'modules AS m
                            ON n.module = m.id
                         WHERE m.name = \''.$section.'\'
                           AND n.cmd = \''.$command.'\'
                           AND n.lang='.$langId;

                $objResult = $objDatabase->SelectLimit($query,1);
                if ($objResult !== false) {
                    if (!$objResult->EOF) {
                        $catID = $objResult->fields['catid'];
                        $this->_setCustomizedThemesId($objResult->fields['themes_id']);
                    }
                }
                if (empty($catID)) {
                    CSRF::header('Location: index.php?section=error');
                    exit;
                }
            }
            return $catID;
        } else {
            if (empty($historyId)) {
                $query='SELECT themes_id, custom_content
                      FROM '.DBPREFIX.'content_navigation
                     WHERE catid = '.$catID;
            } else {
                $query='SELECT themes_id, custom_content
                      FROM '.DBPREFIX.'content_navigation_history
                     WHERE id = '.$historyId;
            }

            $objResult = $objDatabase->SelectLimit($query, 1);
            if ($objResult !== false) {
                if (!$objResult->EOF) {
                    $this->_setCustomizedThemesId($objResult->fields['themes_id']);
					$this->customContentTemplate = $objResult->fields['custom_content'];
                }
            }
            return $catID;
        }
    }


    /**
    * Sets the customized ThemesId
    *
    * This method set the currentThemesId if a customized themesId is set
    * in the navigation table.
    *
    * @access private
    * @param optional string $themesId
    */
    function _setCustomizedThemesId($themesId='')
    {
        global $objDatabase;

	$mobileThemeDefinedAndRequested = $this->arrLang[$this->frontendLangId]['mobile_themes_id'] && $this->isMobileDevice;
	//only set customized theme if not in printview AND no mobile devic
        if(!isset($_GET['printview']) && !$mobileThemeDefinedAndRequested) {
            $themesId=intval($themesId);
            if($themesId>0){
                $objResult = $objDatabase->Execute("SELECT id FROM ".DBPREFIX."skins");
                if ($objResult !== false) {
                    while(!$objResult->EOF) {
                        if ($objResult->fields['id']==$themesId) {
                            $this->currentThemesId=intval($themesId);
                        }
                        $objResult->MoveNext();
                    }
                }
            }
        }
    }


    function getUserFrontendLangMenu()
    {
        $i = 0;
        $arrVars = array();
        if (isset($_SERVER['QUERY_STRING'])) {
            parse_str($_SERVER['QUERY_STRING'], $arrVars);
        }
        $query = isset($arrVars['cmd']) ? "?cmd=".$arrVars['cmd'] : "";
        $return = "\n<form action='index.php".$query."' method='post' name='userFrontendLangIdForm'>\n";
        $return .= "<select name='userFrontendLangId' size='1' onchange=\"document.forms['userFrontendLangIdForm'].submit()\">\n";
        foreach($this->arrLang as $id=>$value){
            if($this->arrLang[$id]['frontend']==1) {
                $i++;
                if($id==$this->userFrontendLangId) {
                    $return .= "<option value='".$id."' selected='selected'>Frontend [".htmlentities($value['name'], ENT_QUOTES, CONTREXX_CHARSET)."]</option>\n";
                } else {
                    $return .= "<option value='".$id."'>Frontend [".htmlentities($value['name'], ENT_QUOTES, CONTREXX_CHARSET)."]</option>\n";
                }
            }
        }
        $return .= "</select>\n</form>\n";
        return ($i>1) ? $return : "";
    }


    function getPrintUri()
    {
        return CONTREXX_DIRECTORY_INDEX.'?'.(!empty($_SERVER['QUERY_STRING']) ? htmlspecialchars($_SERVER['QUERY_STRING'], ENT_QUOTES, CONTREXX_CHARSET).'&amp;' : '').'printview=1';
                }


    function getPDFUri()
    {
        return CONTREXX_DIRECTORY_INDEX.'?'.(!empty($_SERVER['QUERY_STRING']) ? htmlspecialchars($_SERVER['QUERY_STRING'], ENT_QUOTES, CONTREXX_CHARSET).'&amp;' : '').'pdfview=1';
    }


    function getPageUri()
    {
        global $_CONFIG;

        return ASCMS_PROTOCOL."://". $_CONFIG['domainUrl']. $_SERVER['REQUEST_URI'];
        //return $_SERVER['SCRIPT_URI'];
    }


    function getCurrentPageUri()
    {
        return htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, CONTREXX_CHARSET);
    }

    /**
     * Returns true if the user agent is a mobile device (smart phone, PDA etc.)
     *
     * TODO: maybe put this in a separate class
     */
    function _is_mobile_phone() {
        $isMobile = false;
        $old_er = error_reporting(0);
        $op = strtolower($_SERVER['HTTP_X_OPERAMINI_PHONE']);
        $ua = strtolower($_SERVER['HTTP_USER_AGENT']);
        $ac = strtolower($_SERVER['HTTP_ACCEPT']);

        error_reporting($old_er);

        $isMobile = strpos($ac, 'application/vnd.wap.xhtml+xml') !== false
            || $op != ''
            || strpos($ua, 'htc') !== false
            || strpos($ua, 'sony') !== false
            || strpos($ua, 'symbian') !== false
            || strpos($ua, 'nokia') !== false
            || strpos($ua, 'samsung') !== false
            || strpos($ua, 'mobile') !== false
            || strpos($ua, 'windows ce') !== false
            || strpos($ua, 'epoc') !== false
            || strpos($ua, 'opera mini') !== false
            || strpos($ua, 'nitro') !== false
            || strpos($ua, 'j2me') !== false
            || strpos($ua, 'midp-') !== false
            || strpos($ua, 'cldc-') !== false
            || strpos($ua, 'netfront') !== false
            || strpos($ua, 'mot') !== false
            || strpos($ua, 'up.browser') !== false
            || strpos($ua, 'up.link') !== false
            || strpos($ua, 'audiovox') !== false
            || strpos($ua, 'blackberry') !== false
            || strpos($ua, 'ericsson,') !== false
            || strpos($ua, 'panasonic') !== false
            || strpos($ua, 'philips') !== false
            || strpos($ua, 'sanyo') !== false
            || strpos($ua, 'sharp') !== false
            || strpos($ua, 'sie-') !== false
            || strpos($ua, 'portalmmm') !== false
            || strpos($ua, 'blazer') !== false
            || strpos($ua, 'avantgo') !== false
            || strpos($ua, 'danger') !== false
            || strpos($ua, 'palm') !== false
            || strpos($ua, 'series60') !== false
            || strpos($ua, 'palmsource') !== false
            || strpos($ua, 'pocketpc') !== false
            || strpos($ua, 'smartphone') !== false
            || strpos($ua, 'rover') !== false
            || strpos($ua, 'ipaq') !== false
            || strpos($ua, 'au-mic,') !== false
            || strpos($ua, 'alcatel') !== false
            || strpos($ua, 'ericy') !== false
            || strpos($ua, 'up.link') !== false
            || strpos($ua, 'vodafone/') !== false
            || strpos($ua, 'wap1.') !== false
            || strpos($ua, 'wap2.') !== false;
        return $isMobile;
    }


}
?>
