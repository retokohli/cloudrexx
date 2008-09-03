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
    var $is_home;
    var $arrLang = array();
    var $arrLangNames = array();
    var $templates = array();
    var $arrModulePath = array();

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
            SELECT id, themesid, print_themes_id, pdf_themes_id,
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
        if ($objFWUser->objUser->login()) {
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
        setcookie ('backendLangId', $backendLangId, time()+3600*24*30);
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
            setcookie ("langId", $frontendLangId, time()+3600*24*30, ASCMS_PATH_OFFSET.'/');
        }

        if ($_CONFIG['useVirtualLanguagePath'] == 'on' && $this->mode == 'frontend' && empty($_SERVER['REDIRECT_CONTREXX_LANG_PREFIX'])) {
            header('Location: '.ASCMS_PATH_OFFSET.'/'.$this->arrLang[$frontendLangId]['lang']);
            exit;
        }

        $this->frontendLangId = $frontendLangId;
        if (isset($_GET['printview']) && $_GET['printview'] == 1) {
            $this->currentThemesId = $this->arrLang[$frontendLangId]['print_themes_id'];
        }elseif (isset($_GET['pdfview']) && $_GET['pdfview'] == 1){
            $this->currentThemesId = $this->arrLang[$frontendLangId]['pdf_themes_id'];
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
        if ($_CONFIG['languageDetection'] == 'on') {
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

        return $this->templates;
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

        if (ereg('media.?', $module)) {
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
    * Gets the page id
    *
    * gets the current pageId
    *
    * @global   ADONewConnection
    * @param     integer    $catID
    * @param     string        $command
    * @return    integer    $catID
    */
    function getPageID($catID = 0, $section='', $command='', $historyId=0)
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
                    header('Location: index.php?section=error');
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
                    header('Location: index.php?section=error');
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
                    header('Location: index.php?section=error');
                    exit;
                }
            }
            return $catID;
        } else {
            if (empty($historyId)) {
                $query='SELECT themes_id
                      FROM '.DBPREFIX.'content_navigation
                     WHERE catid = '.$catID;
            } else {
                $query='SELECT themes_id
                      FROM '.DBPREFIX.'content_navigation_history
                     WHERE id = '.$historyId;
            }

            $objResult = $objDatabase->SelectLimit($query, 1);
            if ($objResult !== false) {
                if (!$objResult->EOF) {
                    $this->_setCustomizedThemesId($objResult->fields['themes_id']);
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

        if(!isset($_GET['printview'])) {
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
        return ASCMS_PROTOCOL."://". $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        //return $_SERVER['SCRIPT_URI'];
    }


    function getCurrentPageUri()
    {
        return htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, CONTREXX_CHARSET);
    }
}
?>