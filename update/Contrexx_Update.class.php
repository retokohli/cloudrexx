<?php
class Contrexx_Update
{
    public $arrStatusMsg = array('title' => '', 'button' => '', 'msg' => array(), 'error' => array());

    private $objTemplate;
    private $objDatabase;
    private $objJson;
    private $isAuth = false;
    private $lang;
    private $ajax = false;
    private $content = '';
    private $header = '';
    private $navigation = '';

    /**
     * Available languages
     *
     * @var array
     */
    private $_arrAvailableLanguages = array(
        'de' => 'Deutsch',
        //'en' => 'English',
    );

    function __construct()
    {
        global $_CORELANG, $objDatabase;

        @header('content-type: text/html; charset='.(UPDATE_UTF8 ? 'utf-8' : 'iso-8859-1'));
        $this->_loadLanguage();
        $this->objTemplate = new HTML_Template_Sigma(UPDATE_TPL);
        $this->objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        $this->objTemplate->loadTemplateFile('index.html');
        $this->objTemplate->setGlobalVariable(array(
            'TXT_UPDATE_CONTREXX_UPDATE_SYSTEM' => $_CORELANG['TXT_UPDATE_CONTREXX_UPDATE_SYSTEM'],
            'UPDATE_TPL_PATH'                   => UPDATE_TPL
        ));

        $errorMsg = '';
        $objDatabase = $this->getDatabaseObject($errorMsg);
        if (!$objDatabase) {
            die($errorMsg);
        }
        DBG::set_adodb_debug_mode();

        $this->_loadLanguage();
        if (!empty($_GET['ajax'])) {
            $this->ajax = true;
            if (!@include_once(UPDATE_LIB.'/PEAR/Services/JSON.php')) {
                die('unable to load the PEAR JSON library: '.UPDATE_LIB.'/PEAR/Services/JSON.php');
            }
            $this->objJson = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
            $this->parseJsonRequest();
        }
    }

    function parseJsonRequest()
    {
        $_POST = $this->objJson->decode($this->stripslashes($_GET['ajax']));
        if (!UPDATE_UTF8) {
            $_POST = array_map('utf8_decode', $_POST);
        }
    }

    function getPage()
    {
        if (empty($_GET['cmd'])) {
            $_GET['cmd'] = '';
        }
        switch ($_GET['cmd']) {
            case 'logout':
                $this->logout();
        }

        if ($this->auth() || $this->login()) {
            $this->setStep();
            $this->showStep();
        }

        $this->setHeader();

        if ($this->ajax) {
            if (!UPDATE_UTF8) {
                $this->content = utf8_encode($this->content);
            }
            die($this->objJson->encode(array('content' => $this->content, 'header' => $this->header, 'navigation' => $this->navigation)));
        }
        return $this->objTemplate->get();
    }

    function setStep()
    {
        if (empty($_SESSION['contrexx_update']['step'])) {
            $_SESSION['contrexx_update']['step'] = 0;
        }

        if (isset($_POST['updateNext']) || (isset($_POST['updateBack']) && $this->setPreviousStep())) {
            switch ($_SESSION['contrexx_update']['step'])
            {
                case 1:
                    $this->checkStart();
                    break;

                default:
                    $this->checkOverview();
                    break;
            }
        } elseif ($_SESSION['contrexx_update']['step'] == 2) {
            $this->setPreviousStep();
        }
    }

    function showStep()
    {
        switch ($_SESSION['contrexx_update']['step']) {
            case 1:
                $this->showUpdate();
                break;
            case 2:
                $this->processUpdate();
                break;
            default:
                $this->versionOverview();
        }
    }

    function setNextStep()
    {
        ++$_SESSION['contrexx_update']['step'];
    }

    function setPreviousStep()
    {
        --$_SESSION['contrexx_update']['step'];
    }

    function checkOverview()
    {
        if (!empty($_POST['updateVersion'])) {
            if (is_array($_POST['updateVersion'])) {
                $_POST['updateVersion'] = $_POST['updateVersion'][0];
            }
            $arrVersions = $this->getAvailabeVersions();
            if (in_array($this->stripslashes($_POST['updateVersion']), array_keys($arrVersions['compatible']))) {
                $_SESSION['contrexx_update']['version'] = stripslashes($_POST['updateVersion']);
                $this->setNextStep();
            }
        }
    }

    function checkStart()
    {
        if (isset($_POST['updateNext']) && isset($_POST['processUpdate'])) {
            if (!empty($_POST['update_module_repository']) && $_POST['update_module_repository'] == '1') {
                $_SESSION['contrexx_update']['updateRepository'] = true;
            } else {
                $_SESSION['contrexx_update']['updateRepository'] = false;
            }
            if (!empty($_POST['update_backend_areas']) && $_POST['update_backend_areas'] == '1') {
                $_SESSION['contrexx_update']['updateBackendAreas'] = true;
            } else {
                $_SESSION['contrexx_update']['updateBackendAreas'] = false;
            }
            if (!empty($_POST['update_module_table']) && $_POST['update_module_table'] == '1') {
                $_SESSION['contrexx_update']['updateModules'] = true;
            } else {
                $_SESSION['contrexx_update']['updateModules'] = false;
            }
            $this->setNextStep();
        } else {
            $this->setPreviousStep();
        }
    }

    function setHeader()
    {
        global $_CORELANG;

        $this->objTemplate->addBlockfile('HEADER', 'header', 'header.html');
        //$this->objTemplate->setVariable('UPDATE_LANG_MENU', $this->getLangMenu());

        if ($this->auth()) {
            $this->objTemplate->setVariable('TXT_UPDATE_LOGOUT', $_CORELANG['TXT_UPDATE_LOGOUT']);
            $this->objTemplate->parse('updateLogout');
        } else {
            $this->objTemplate->hideBlock('updateLogout');
        }

        $this->objTemplate->parse('header');
        if ($this->ajax) {
            $this->header = $this->objTemplate->get('header');
        }
    }

    function setNavigation($content)
    {
        if ($this->ajax) {
            $this->navigation = $content;
        } else {
            $this->objTemplate->setVariable('NAVIGATION', $content);
        }
    }

    function getLangMenu()
    {
        $menu = '<select class="lang" name="lang" onchange="window.location.href=\'?lang=\'+this.value">';
        foreach ($this->_arrAvailableLanguages as $lang => $desc) {
            $menu .= '<option value="'.$lang.'"'.($lang == $this->lang ? ' selected="selected"' : '').'>'.$desc.'</option>';
        }
        $menu .= '</select>';
        return $menu;
    }

    function versionOverview()
    {
        global $_CORELANG;

        $this->objTemplate->addBlockfile('CONTENT', 'overview', 'overview.html');
        $this->objTemplate->setVariable('TXT_UPDATE_VERSION_SELECTION', $_CORELANG['TXT_UPDATE_VERSION_SELECTION']);
        $arrVersions = $this->getAvailabeVersions();
        if (isset($arrVersions['compatible']) && count($arrVersions['compatible'])) {
            $this->objTemplate->setVariable(array(
                'TXT_UPDATE_SELECT_VERSION_MSG'        => $_CORELANG['TXT_UPDATE_SELECT_VERSION_MSG'],
                'TXT_UPDATE_AVAILABLE_VERSIONS'        => $_CORELANG['TXT_UPDATE_AVAILABLE_VERSIONS']
            ));
            foreach ($arrVersions['compatible'] as $versionPath => $arrVersion) {
                $this->objTemplate->setVariable(array(
                    'UPDATE_VERSION'            => str_replace(' Service Pack 0', '', preg_replace('#^(\d+\.\d+)\.(\d+)$#', '$1 Service Pack $2', $arrVersion['cmsVersion'])),
                    'UPDATE_VERSION_PATH'        => $versionPath,
                    'UPDATE_VERSION_NAME'        => $arrVersion['cmsName'],
                    'UPDATE_VERSION_EDITION'    => $arrVersion['cmsEdition'],
                    'UPDATE_VERSION_CHECKED'    => !empty($_SESSION['contrexx_update']['version']) && $_SESSION['contrexx_update']['version'] == $versionPath ? 'checked="checked"' : ''
                ));
                $this->objTemplate->parse('updateVersionList');
            }

            if (isset($_POST['updateVersions'])) {
                $this->objTemplate->setVariable('UPDATE_ERROR_MSG', $_CORELANG['TXT_UPDATE_MUST_SELECT_UPDATE']);
                $this->objTemplate->parse('updateNoVersionSelected');
            } else {
                $this->objTemplate->hideBlock('updateNoVersionSelected');
            }
            $this->objTemplate->parse('updateVersions');
            $this->objTemplate->hideBlock('updateNoVersions');
        } else {
            $this->objTemplate->setVariable('TXT_UPDATE_NO_VERSION_TO_UPGRADE_TO', $_CORELANG['TXT_UPDATE_NO_VERSION_TO_UPGRADE_TO']);
            $this->objTemplate->parse('updateNoVersions');
            $this->objTemplate->hideBlock('updateVersions');
        }

        if (isset($arrVersions['incompatible']) && count($arrVersions['incompatible'])) {
            $this->objTemplate->setVariable(array(
                'TXT_UPDATE_INCOMPATIBLE_VERSIONS_MSG' => $_CORELANG['TXT_UPDATE_INCOMPATIBLE_VERSIONS_MSG'],
                'TXT_UPDATE_INCOMPATIBLE_VERSIONS'     => $_CORELANG['TXT_UPDATE_INCOMPATIBLE_VERSIONS']
            ));

            foreach ($arrVersions['incompatible'] as $versionPath => $arrVersion) {
                $this->objTemplate->setVariable(array(
                    'UPDATE_VERSION'                     => str_replace(' Service Pack 0', '', preg_replace('#^(\d+\.\d+)\.(\d+)$#', '$1 Service Pack $2', $arrVersion['cmsVersion'])),
                    'UPDATE_VERSION_NAME'                => $arrVersion['cmsName'],
                    'UPDATE_VERSION_EDITION'             => $arrVersion['cmsEdition'],
                    'UPDATE_VERSION_INCOMPATIBLE_REASON' => $arrVersion['reason']
                ));
                $this->objTemplate->parse('updateIncompatibleVersionList');
            }
            $this->objTemplate->parse('updateIncompatibleVersions');
        } else {
            $this->objTemplate->hideBlock('updateIncompatibleVersions');
        }

        $this->objTemplate->parse('overview');
        if ($this->ajax) {
            $this->content = $this->objTemplate->get('overview');
        }
        $this->setNavigation('<input type="submit" value="'.$_CORELANG['TXT_UPDATE_NEXT'].'" name="updateNext" />');
    }

    function showUpdate()
    {
        global $_CORELANG;

        $this->objTemplate->addBlockfile('CONTENT', 'start', 'start.html');
        $this->objTemplate->setVariable(array(
            'UPDATE_PROCESS_SCREEN'                 => $_CORELANG['TXT_UPDATE_IS_READY'],
            'TXT_UPDATE_ADVANCED_CONFIGURATION'        => $_CORELANG['TXT_UPDATE_ADVANCED_CONFIGURATION'],
            'TXT_UPDATE_RENEW_MODULE_REPOSITORY'    => $_CORELANG['TXT_UPDATE_RENEW_MODULE_REPOSITORY'],
            'TXT_UPDATE_RENEW_BACKEND_AREAS'        => $_CORELANG['TXT_UPDATE_RENEW_BACKEND_AREAS'],
            'TXT_UPDATE_RENEW_MODULE_TABLE'            => $_CORELANG['TXT_UPDATE_RENEW_MODULE_TABLE'],
            'TXT_UPDATE_UPDATE_IS_READY'            => $_CORELANG['TXT_UPDATE_UPDATE_IS_READY']
        ));
        $this->objTemplate->parse('start');
        if ($this->ajax) {
            $this->content = $this->objTemplate->get('start');
        }
        $this->setNavigation('<input type="submit" value="'.$_CORELANG['TXT_UPDATE_BACK'].'" name="updateBack" onclick="try{doUpdate(true)} catch(e){return true;}" /> <input type="submit" value="'.$_CORELANG['TXT_UPDATE_START_UPDATE'].'" name="updateNext" /><input type="hidden" name="processUpdate" id="processUpdate" />');
    }

    function processUpdate()
    {
        global $_CORELANG, $arrVersions;

        $this->objTemplate->addBlockfile('CONTENT', 'process', 'process.html');
        if (($return = $this->_loadUpdateLanguage()) !== true) {
            $this->objTemplate->setVariable('UPDATE_ERROR_MSG', $return);

            $this->objTemplate->parse('updateProcessError');
        } elseif (($arrVersions = $this->getAvailabeVersions()) === false || !@include_once(UPDATE_UPDATES.'/'.$_SESSION['contrexx_update']['version'].'/'.$arrVersions['compatible'][$_SESSION['contrexx_update']['version']]['script'])) {
            $this->objTemplate->setVariable('UPDATE_ERROR_MSG', $_CORELANG['TXT_UPDATE_UNABLE_TO_START']);
            $this->objTemplate->parse('updateProcessError');
        } else {
            $result = executeContrexxUpdate($_SESSION['contrexx_update']['updateRepository'], $_SESSION['contrexx_update']['updateBackendAreas'], $_SESSION['contrexx_update']['updateModules']);
            if ($result !== true) {
                if (!empty($this->arrStatusMsg['error'])) {
                    $this->objTemplate->setVariable('UPDATE_ERROR_MSG', implode('<br />', $this->arrStatusMsg['error']));
                    $this->objTemplate->parse('updateProcessError');
                }
                if (empty($this->arrStatusMsg['title'])) {
                    $this->arrStatusMsg['title'] = 'Update Fehler';
                }
                if (empty($this->arrStatusMsg['button'])) {
                    $this->arrStatusMsg['button'] = '<input type="submit" value="'.$_CORELANG['TXT_UPDATE_TRY_AGAIN'].'" name="updateNext" /><input type="hidden" name="processUpdate" id="processUpdate" />';
                }
                $this->objTemplate->setVariable(array(
                    'UPDATE_PROCESS_TITLE'    => $_CORELANG['TXT_UPDATE_UPDATE_PROCESS'],
                    'UPDATE_STATUS_TITLE'    => $this->arrStatusMsg['title'],
                    'UPDATE_STATUS'            => str_replace('[[SQL_INFO_TITLE]]', $this->arrStatusMsg['title'], implode('<br />', $this->arrStatusMsg['msg']))
                ));
                $this->setNavigation($this->arrStatusMsg['button']);
            } else {
                $this->objTemplate->hideBlock('updateProcessError');
                $this->objTemplate->setVariable(array(
                    'UPDATE_PROCESS_TITLE'    => $_CORELANG['TXT_UPDATE_UPDATE_FINISHED'],
                    'UPDATE_STATUS_TITLE'    => '<strong>'.$_CORELANG['TXT_UPDATE_UPDATE_FINISHED_SUCCESSFULL'].'</strong>',
                    'UPDATE_STATUS'            => implode('<br />', $this->arrStatusMsg['msg'])
                ));
                $this->setNavigation('<input type="submit" value="'.$_CORELANG['TXT_UPDATE_NEXT'].'" name="update" />');
                $_SESSION['contrexx_update']['step'] = 0;
                $_SESSION['contrexx_update']['update'] = array();
            }
        }
        $this->objTemplate->parse('process');
        if ($this->ajax) {
            $this->content = $this->objTemplate->get('process');
        }
    }

    function getMySQLServerVersion()
    {
        global $objDatabase;

        $version = array();
        $objVersion = $objDatabase->SelectLimit('SELECT VERSION() AS mysqlversion', 1);
        if ($objVersion !== false && $objVersion->RecordCount() == 1 && preg_match('#^([0-9.]+)#', $objVersion->fields['mysqlversion'], $version)) {
            return $version[1];
        }
        return false;
    }

    function checkMySQLVersion($requiredVersion)
    {
        static $installedVersion;

        if (!isset($installedVersion)) {
            $installedVersion = $this->getMySQLServerVersion();
        }
        if (!$installedVersion) {
            return false;
        }
        return !$this->_isNewerVersion($installedVersion, $requiredVersion);
    }

    function checkPHPVersion($requiredVersion)
    {
        if (preg_match('#(?:[0-9]+\.?)+#', phpversion(), $arrMatch)) {
            return !$this->_isNewerVersion($arrMatch[0], $requiredVersion);
        } else {
            return false;
        }
    }

    function getAvailabeVersions()
    {
        global $_CONFIG, $_CORELANG;
        static $arrVersions = array();

        if (!count($arrVersions)) {
            if (($dh = opendir(UPDATE_UPDATES)) === false) {
                return false;
            }
            $file = readdir($dh);
            while ($file) {
                if (!in_array($file, array('.', '..'))) {
                    $arrUpdate = false;
                    if (@include_once(UPDATE_UPDATES.'/'.$file.'/config.inc.php')) {
                        if (is_array($arrUpdate)) {
                            if ($_CONFIG['coreCmsEdition'] != $arrUpdate['cmsEdition']) {
                                $arrVersions['incompatible'][$file] = $arrUpdate;
                                $arrVersions['incompatible'][$file]['reason'] = $_CORELANG['TXT_UPDATE_INCOMPATIBLE_EDITION'];
                            } elseif (!$this->_isNewerVersion($_CONFIG['coreCmsVersion'], $arrUpdate['cmsVersion'], $_CONFIG['coreCmsStatus'], $arrUpdate['cmsStatus'])) {
                                $arrVersions['incompatible'][$file] = $arrUpdate;
                                $arrVersions['incompatible'][$file]['reason'] = $_CORELANG['TXT_UPDATE_INSTALLED_VERSION_IS_NEWER'];
                            } elseif ($this->_isNewerVersion($_CONFIG['coreCmsVersion'], $arrUpdate['cmsFromVersion'])) {
                                $arrVersions['incompatible'][$file] = $arrUpdate;
                                $arrVersions['incompatible'][$file]['reason'] = sprintf($_CORELANG['TXT_UPDATE_INSTALLED_VERSION_IS_TOO_OLD'], $arrUpdate['cmsFromVersion']);
                            } elseif (!$this->checkPHPVersion($arrUpdate['cmsRequiredPHP'])) {
                                $arrVersions['incompatible'][$file] = $arrUpdate;
                                $arrVersions['incompatible'][$file]['reason'] = sprintf($_CORELANG['TXT_UPDATE_PHP_VERSION_TOO_OLD'], $arrUpdate['cmsRequiredPHP'], phpversion());
                            } elseif (!$this->checkMySQLVersion($arrUpdate['cmsRequiredMySQL'])) {
                                $arrVersions['incompatible'][$file] = $arrUpdate;
                                $arrVersions['incompatible'][$file]['reason'] = sprintf($_CORELANG['TXT_UPDATE_MYSQL_VERSION_TOO_OLD'], $arrUpdate['cmsRequiredMySQL'], $this->getMySQLServerVersion());
                            } else {
                                $arrVersions['compatible'][$file] = $arrUpdate;
                            }
                        }
                    }
                    unset($arrUpdate);
                }
                $file = readdir($dh);
            }
        }
        return $arrVersions;
    }

    /**
     * Check for newer version
     *
     * Returns TRUE if $newVersion has a higher version number than $installedVersion.
	 *
	 * @param string $installedVersion
	 * @param string $newVersion
	 * @return boolean
     */
    function _isNewerVersion($installedVersion, $newVersion)
    {
        $arrInstalledVersion = explode('.', $installedVersion);
        $arrNewVersion = explode('.', $newVersion);
        $maxSubVersion = count($arrInstalledVersion) > count($arrNewVersion) ? count($arrInstalledVersion) : count($arrNewVersion);
        for ($nr = 0; $nr < $maxSubVersion; $nr++) {
            if (!isset($arrInstalledVersion[$nr])) {
                return true;
            } elseif (!isset($arrNewVersion[$nr])) {
                return false;
            } elseif ($arrNewVersion[$nr] > $arrInstalledVersion[$nr]) {
                return true;
            } elseif ($arrNewVersion[$nr] < $arrInstalledVersion[$nr]) {
                return false;
            }
        }
//        if ($this->_isNewerStatus($installedStatus, $newStatus)) {
//            return true;
//        }
        return false;
    }

    function _isNewerStatus($installedStatus, $newStatus)
    {
        $arrStatusInstalled = array();
        $arrStatusNew = array();
        $arrLifeCycleOrder = array(
            'alpha',
            'beta',
            'rc',
            'release'
        );

        if (preg_match('#.*(alpha|beta|rc([0-9]+)|release).*#i', $installedStatus, $arrStatusInstalled)) {
            if (isset($arrStatusInstalled[2])) {
                $arrStatusInstalled[1] = 'rc';
            }
        }
        if (preg_match('#.*(alpha|beta|rc([0-9]+)|release).*#i', $newStatus, $arrStatusNew)) {
            if (isset($arrStatusNew[2])) {
                $arrStatusNew[1] = 'rc';
            }
        }
        if (isset($arrStatusInstalled[2]) && isset($arrStatusNew[2])) {
            if ($arrStatusInstalled[2] < $arrStatusNew[2]) {
                return true;
            }
            return false;
        } elseif (strtolower($arrStatusInstalled[1]) == strtolower($arrStatusNew[1])) {
            return false;
        } elseif (array_search(strtolower($arrStatusInstalled[1]), $arrLifeCycleOrder) < array_search(strtolower($arrStatusNew[1]), $arrLifeCycleOrder)) {
            return true;
        }
        return false;
    }

    function logout()
    {
        $_SESSION = array();
        $_SESSION['contrexx_update']['lang'] = $this->lang;
        $this->isAuth = false;
    }

    function login()
    {
        global $_CORELANG;

        $authFailed = false;
        if (isset($_POST['updateNext'])) {
            if (!empty($_POST['updateUser']) && !empty($_POST['updatePass']) && $this->auth($username = $this->addslashes($_POST['updateUser']), $password = md5($this->stripslashes($_POST['updatePass'])))) {
                $_SESSION['contrexx_update']['step'] = 0;
                $_SESSION['contrexx_update']['username'] = $username;
                $_SESSION['contrexx_update']['password'] = $password;
                return true;
            }
            $authFailed = true;
        }

        $this->objTemplate->addBlockfile('CONTENT', 'login', 'login.html');

        if ($authFailed) {
            $this->objTemplate->setVariable('UPDATE_ERROR_MSG', $_CORELANG['TXT_UPDATE_AUTH_FAILED']);
            $this->objTemplate->parse('updateAuthFailedBox');
        } else {
            $this->objTemplate->hideBlock('updateAuthFailedBox');
        }

        $this->objTemplate->setVariable(array(
            'TXT_UPDATE_INTRO_MSG' => $_CORELANG['TXT_UPDATE_INTRO_MSG'],
            'TXT_UPDATE_USERNAME'  => $_CORELANG['TXT_UPDATE_USERNAME'],
            'TXT_UPDATE_PASSWORD'  => $_CORELANG['TXT_UPDATE_PASSWORD'],
            'TXT_UPDATE_LOGIN'     => $_CORELANG['TXT_UPDATE_LOGIN'],
        ));
        $this->objTemplate->parse('login');
        if ($this->ajax) {
            $this->content = $this->objTemplate->get('login');
        }
        return false;
    }

    function auth($user='', $pass='')
    {
        global $objDatabase;

        if ($this->isAuth) {
            return true;
        }

        if (empty($user)) {
            if (!empty($_SESSION['contrexx_update']['username']) && !empty($_SESSION['contrexx_update']['password'])) {
                $user = $_SESSION['contrexx_update']['username'];
                $pass = $_SESSION['contrexx_update']['password'];
            } else {
                return false;
            }
        }

        $objAuth = $objDatabase->SelectLimit("SELECT 1 FROM `".DBPREFIX."access_users` WHERE `username` = '".$user."' AND `password` = '".$pass."' AND `is_admin` = 1 AND `active` = 1", 1);
        if ($objAuth !== false && $objAuth->RecordCount() == 1) {
            $this->isAuth = true;
            return true;
        }
        return false;
    }

    function getDatabaseObject(&$errorMsg, $newInstance = false)
    {
        global $_ARRLANG, $_DBCONFIG, $ADODB_FETCH_MODE;
        static $objDatabase;

        if (is_object($objDatabase) && !$newInstance) {
            return $objDatabase;
        }
        // open db connection
        $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

        $objDb = ADONewConnection($_DBCONFIG['dbType']);
        @$objDb->Connect($_DBCONFIG['host'], $_DBCONFIG['user'], $_DBCONFIG['password'], $_DBCONFIG['database']);

        $errorNo = $objDb->ErrorNo();
        if ($errorNo != 0) {
            if ($errorNo == 1049) {
                $errorMsg .= str_replace("[DATABASE]", $_DBCONFIG['database'], $_ARRLANG['TXT_DATABASE_DOES_NOT_EXISTS']."<br />");
            } else {
                $errorMsg .=  $objDb->ErrorMsg()."<br />";
            }
            unset($objDb);
            return false;
        }

        if (empty($_DBCONFIG['charset']) || $objDb->Execute('SET CHARACTER SET '.$_DBCONFIG['charset']) && $objDb) {
            if ($newInstance) {
                return $objDb;
            } else {
                $objDatabase = $objDb;
                return $objDb;
            }
        } else {
            $errorMsg .= $_ARRLANG['TXT_CANNOT_CONNECT_TO_DB_SERVER']."<i>&nbsp;(".$objDb->ErrorMsg().")</i><br />";
            unset($objDb);
        }
        return false;
    }

    function addslashes($string)
    {
      // if magic quotes is on the string is already quoted,
      // just return it
      if (get_magic_quotes_gpc()) return $string;
      return addslashes($string);
    }

    /**
    * stripslashes wrapper to check for gpc_magic_quotes
    *
    * @param string    $string
    * @return string $string
    */
    function stripslashes($string)
    {
        if (get_magic_quotes_gpc()) return stripslashes($string);
        return $string;
    }

    function _loadLanguage()
    {
        global $_CORELANG;

        $lang = &$this->_selectBestLanguage();
        if (isset($_REQUEST["lang"])) {
            if ($_REQUEST["lang"]!="") {
                $lang = $_REQUEST["lang"];
            }
        }
        if (@file_exists(UPDATE_LANG.'/'.$lang.'.lang.php')) {
            require_once(UPDATE_LANG.'/'.$lang.'.lang.php');
            $_SESSION['contrexx_update']['lang'] = $lang;
            $this->lang = $_SESSION['contrexx_update']['lang'];
        } else {
            die("Couldn't load language '".$lang."'");
        }
    }

    function _loadUpdateLanguage()
    {
        global $_CORELANG, $_ARRAYLANG;

        $arrVersions = $this->getAvailabeVersions();
        if (in_array($this->lang, $arrVersions['compatible'][$_SESSION['contrexx_update']['version']]['lang'])) {
            $lang = $this->lang;
        } else {
            $lang = $arrVersions['compatible'][$_SESSION['contrexx_update']['version']['lang'][0]];
        }
        if (@file_exists(UPDATE_UPDATES.'/'.$_SESSION['contrexx_update']['version'].'/lang/'.$lang.'.lang.php') && @include_once(UPDATE_UPDATES.'/'.$_SESSION['contrexx_update']['version'].'/lang/'.$lang.'.lang.php')) {
            return true;
        }
        return sprintf($_CORELANG['TXT_UPDATE_UNABLE_TO_LOAD_UPDATE_LANG'], UPDATE_UPDATES.'/'.$_SESSION['contrexx_update']['version'].'/lang/'.$lang.'.lang.php');
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
        $arrAcceptedLanguages = &$this->_getClientAcceptedLanguages();

        if (!empty($_SESSION['contrexx_update']['lang']) && in_array($_SESSION['contrexx_update']['lang'], array_keys($this->_arrAvailableLanguages))) {
            return $_SESSION['contrexx_update']['lang'];
        }

        foreach (array_keys($arrAcceptedLanguages) as $language) {
            if (in_array($language, array_keys($this->_arrAvailableLanguages))) {
                return $language;
            } elseif (in_array($strippedLanguage = substr($language, 0, strpos($language, '-')), array_keys($this->_arrAvailableLanguages))) {
                return $strippedLanguage;
            }
        }
        return in_array($this->_defaultLanguage, array_keys($this->_arrAvailableLanguages)) ? $this->_defaultLanguage : key($this->_arrAvailableLanguages);
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
}

function _databaseError($query, $errorMsg)
{
    global $_CORELANG, $objUpdate, $_CONFIG, $arrVersions;

    $msg = sprintf($_CORELANG['TXT_UPDATE_DB_ERROR'], htmlspecialchars($query), htmlspecialchars($errorMsg));
    $objUpdate->arrStatusMsg['error'][] = $msg;
    $objUpdate->arrStatusMsg['msg'][] = sprintf($_CORELANG['TXT_UPDATE_DB_ERROR_HELP_MSG'], UPDATE_SUPPORT_FORUM_URI, $_CONFIG['coreCmsVersion'], str_replace(' Service Pack 0', '', preg_replace('#^(\d+\.\d+)\.(\d+)$#', '$1 Service Pack $2', $arrVersions['compatible'][$_SESSION['contrexx_update']['version']]['cmsVersion'])), $msg);
    return false;
}

function setUpdateMsg($msg, $type='error')
{
    global $objUpdate;

    if (!in_array($type, array('title', 'msg', 'error', 'button'))){
        $type = 'error';
    }
    switch ($type) {
        case 'title':
        case 'button':
            $objUpdate->arrStatusMsg[$type] = $msg;
            break;
        default:
            $objUpdate->arrStatusMsg[$type][] = $msg;
    }
}

function checkMemoryLimit()
{
    global $_CORELANG;
    static $memoryLimit, $MB2;

    if (!isset($memoryLimit)) {
        @include_once(ASCMS_FRAMEWORK_PATH.'/System.class.php');
        $objSystem = new FWSystem();
        if ($objSystem === false) {
            setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_API_LOAD_FAILED'], ASCMS_FRAMEWORK_PATH.'/System.class.php'));
            return false;
        }
        $memoryLimit = $objSystem->_getBytes(@ini_get('memory_limit'));
        if (empty($memoryLimit)) {
            // set default php memory limit of 8MBytes
            $memoryLimit = 8*pow(1024, 2);
        }
        $MB2 = 2 * pow(1024, 2);
    }
    $potentialRequiredMemory = memory_get_usage() + $MB2;
    if ($potentialRequiredMemory > $memoryLimit) {
        // try to set a higher memory_limit
        if (!@ini_set('memory_limit', $potentialRequiredMemory)) {
            setUpdateMsg($_CORELANG['TXT_UPDATE_PROCESS_HALTED'], 'title');
            setUpdateMsg($_CORELANG['TXT_UPDATE_PROCESS_HALTED_RAM_MSG'].'<br /><br />', 'msg');
            setUpdateMsg('<input type="submit" value="'.$_CORELANG['TXT_CONTINUE_UPDATE'].'" name="updateNext" /><input type="hidden" name="processUpdate" id="processUpdate" />', 'button');
            return false;
        }
    }
    return true;
}

function checkTimeoutLimit()
{
    global $_CORELANG;

    if (UPDATE_TIMEOUT_TIME > time()) {
        return true;
    }
    setUpdateMsg($_CORELANG['TXT_UPDATE_PROCESS_HALTED'], 'title');
    setUpdateMsg($_CORELANG['TXT_UPDATE_PROCESS_HALTED_TIME_MSG'].'<br /><br />', 'msg');
    setUpdateMsg('<input type="submit" value="'.$_CORELANG['TXT_CONTINUE_UPDATE'].'" name="updateNext" /><input type="hidden" name="processUpdate" id="processUpdate" />', 'button');
    return false;
}

?>
