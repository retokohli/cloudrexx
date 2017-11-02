<?php

/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2015
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Cloudrexx" is a registered trademark of Cloudrexx AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * Config
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Cloudrexx Development Team <info@cloudrexx.com>
 * @version     1.1.0
 * @package     cloudrexx
 * @subpackage  core_config
 * @todo        Edit PHP DocBlocks!
 */

namespace Cx\Core\Config\Controller;

/**
 * @ignore
 */
use Cx\Core\Core\Controller\Cx;
use Cx\Core\Csrf\Controller\Csrf;
use Cx\Lib\FileSystem\FileSystem;

isset($objInit) && $objInit->mode == 'backend' ? \Env::get('ClassLoader')->loadFile(ASCMS_CORE_MODULE_PATH.'/Cache/Controller/CacheManager.class.php') : null;

/**
 * Config
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Cloudrexx Development Team <info@cloudrexx.com>
 * @version     1.1.0
 * @package     cloudrexx
 * @subpackage  core_config
 * @todo        Edit PHP DocBlocks!
 */


class Config
{
    var $_objTpl;
    var $strPageTitle;
    protected $configFile;
    var $strErrMessage = array();
    var $strOkMessage;
    private $writable;

    private $act = '';

    function __construct()
    {
        $this->configFile = \Env::get('cx')->getWebsiteConfigPath() . '/Config.yml';
        self::init();
        $this->checkWritePermissions();
    }

    private function setNavigation()
    {
        global $objTemplate, $_ARRAYLANG;

        // TODO: instead of including the config section of MultiSite component
        // (as well as the one of Cache component) directly, the Config
        // component should provide a method for other components to include
        // their config sections in the base configuration section
        $componentRepo = \Env::get('cx')->getDb()->getEntityManager()->getRepository('Cx\Core\Core\Model\Entity\SystemComponent');
        $component     = $componentRepo->findOneBy(array('name' => 'multisite'));

        $multisiteNavigation = '';
        if ($component) {
            if (\Cx\Core_Modules\MultiSite\Controller\JsonMultiSiteController::isWebsiteOwner() &&
                \Cx\Core\Setting\Controller\Setting::getValue('websiteFtpUser','MultiSite')
            ) {
                $multisiteNavigation = '<a href="index.php?cmd=Config&amp;act=Ftp" class="'.
                    ($this->act == 'Ftp' ? 'active' : '').'">' . $_ARRAYLANG['TXT_SETTINGS_FTP'].'</a>';
            }

            \Cx\Core\Setting\Controller\Setting::init('MultiSite', 'website','FileSystem');
        }

        $objTemplate->setVariable('CONTENT_NAVIGATION','
            <a href="?cmd=Config" class="'.($this->act == '' ? 'active' : '').'">'.$_ARRAYLANG['TXT_SETTINGS_MENU_SYSTEM'].'</a>'
            .(in_array('CacheManager', \Env::get('cx')->getLicense()->getLegalComponentsList()) ? '<a href="?cmd=Config&amp;act=cache" class="'.($this->act == 'cache' ? 'active' : '').'">'.$_ARRAYLANG['TXT_SETTINGS_MENU_CACHE'].'</a>' : '')  .
            '<a href="?cmd=Config&amp;act=smtp" class="'.($this->act == 'smtp' ? 'active' : '').'">'.$_ARRAYLANG['TXT_EMAIL_SERVER'].'</a>
            <a href="index.php?cmd=Config&amp;act=image" class="'.($this->act == 'image' ? 'active' : '').'">'.$_ARRAYLANG['TXT_SETTINGS_IMAGE'].'</a>'
            .(in_array('Wysiwyg', \Env::get('cx')->getLicense()->getLegalComponentsList()) ? '<a href="index.php?cmd=Config&amp;act=Wysiwyg" class="'.($this->act == 'Wysiwyg' ? 'active' : '').'">'.$_ARRAYLANG['TXT_CORE_WYSIWYG'].'</a>' : '')
            .(in_array('Pdf', \Env::get('cx')->getLicense()->getLegalComponentsList()) ? '<a href="index.php?cmd=Config&amp;act=Pdf" class="'.($this->act == 'Pdf' ? 'active' : '').'">'.$_ARRAYLANG['TXT_CORE_CONFIG_PDF'].'</a>' : '')
            .(in_array('LicenseManager', \Env::get('cx')->getLicense()->getLegalComponentsList()) ? '<a href="index.php?cmd=License">'.$_ARRAYLANG['TXT_LICENSE'].'</a>' : '')
            . $multisiteNavigation
        );
    }

    /**
     * Check whether the configuration in the configurations file is correct or not
     * This method displays a warning message on top of the page when the ftp connection failed or the configuration
     * is disabled
     */
    protected function checkFtpAccess() {
        global $_ARRAYLANG;

        // Only check FTP access if SystemInfo has been licensed.
        // SystemInfo is a component that allows access to the webserver.
        // SystemInfo should only be licensed if the website is run on a self-hosted environment
        if (!in_array('SystemInfo', \Env::get('cx')->getLicense()->getLegalComponentsList())) {
            return;
        }

        // if ftp access is not activated or not possible to connect (not correct credentials)
        if(!\Cx\Lib\FileSystem\FileSystem::init()) {
            \Message::add(sprintf($_ARRAYLANG['TXT_SETTING_FTP_CONFIG_WARNING'], \Env::get('cx')->getWebsiteDocumentRootPath() . '/config/configuration.php'), \Message::CLASS_ERROR);
        }
    }

    private function checkWritePermissions() {
        global $_ARRAYLANG;

        $this->writable = true;
        if (!\Cx\Lib\FileSystem\FileSystem::makeWritable(self::getSettingsFile())) {
            $this->writable = false;
            \Message::warning(sprintf($_ARRAYLANG['TXT_SETTINGS_ERROR_NO_WRITE_ACCESS'], self::getSettingsFile()));
        }
        if (!\Cx\Lib\FileSystem\FileSystem::makeWritable($this->configFile)) {
            $this->writable = false;
            \Message::warning(sprintf($_ARRAYLANG['TXT_SETTINGS_ERROR_NO_WRITE_ACCESS'], $this->configFile));
        }
    }

    public function isWritable()
    {
        return $this->writable;
    }

    /**
     * Perform the requested function depending on $_GET['act']
     *
     * @global  array   Core language
     * @global  \Cx\Core\Html\Sigma
     * @return  void
     */
    function getPage()
    {
        global $_ARRAYLANG, $objTemplate;

        if(!isset($_GET['act'])){
            $_GET['act']='';
        }

        $boolShowStatus = true;

        switch ($_GET['act']) {
            case 'Ftp':
                $this->showFtp();
                break;

            case 'cache':
                if (in_array('CacheManager', \Env::get('cx')->getLicense()->getLegalComponentsList())) {
                    $boolShowStatus = false;
                    $objCache = new \Cx\Core_Modules\Cache\Controller\CacheManager();
                    $objCache->showSettings();
                } else {
                    \Permission::noAccess();
                }

                break;

            case 'Wysiwyg':
                if (in_array('Wysiwyg', \Env::get('cx')->getLicense()->getLegalComponentsList())) {
                    $boolShowStatus = false;
                    $this->showWysiwyg();
                } else {
                    \Permission::noAccess();
                }

                break;
            case 'Pdf':
                if (!in_array('Pdf', \Env::get('cx')->getLicense()->getLegalComponentsList())) {
                    \Permission::noAccess();
                }
                $boolShowStatus = false;
                $this->showPdf();
                break;

            case 'cache_update':
                $boolShowStatus = false;
                $objCache = new \Cx\Core_Modules\Cache\Controller\CacheManager();
                $objCache->updateSettings();
                $objCache->showSettings();
                break;

            case 'cache_empty':
                $boolShowStatus = false;
                $objCache = new \Cx\Core_Modules\Cache\Controller\CacheManager();
                $objCache->forceClearCache(isset($_GET['cache']) ? contrexx_input2raw($_GET['cache']) : null);
                $objCache->showSettings();
                break;

            case 'smtp':
                $this->smtp();
                break;

            case 'image':
                try {
                    $this->image($_POST);
                } catch (Exception $e) {
                    \DBG::msg('Image settings: '.$e->getMessage);
                }
                break;
            case 'thumbnail':
                $this->editThumbnails($_POST);
                break;

            case 'generateThumbnail':
                $this->generateThumbnail($_POST);
                break;

            case 'getThumbProgress':
                $this->getThumbProgress();
                break;

            default:
                $this->showSettings();
        }

        if ($boolShowStatus) {
            $objTemplate->setVariable(array(
                'CONTENT_TITLE'                =>     $this->strPageTitle,
                'CONTENT_OK_MESSAGE'        =>    $this->strOkMessage,
                'CONTENT_STATUS_MESSAGE'    =>     implode("<br />\n", $this->strErrMessage)
            ));
        }

        $this->act = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';
        $this->setNavigation();
    }

    protected function  showWysiwyg() {
        global $_ARRAYLANG, $objTemplate, $objInit;

        $cx = Cx::instanciate();
        $em = $cx->getDB()->getEntityManager();
        $componentRepo = $em->getRepository('Cx\Core\Core\Model\Entity\SystemComponent');
        $wysiwyg = $componentRepo->findOneBy(array('name'=>'Wysiwyg'));
        $wysiwygBackendController = $wysiwyg->getController('Backend');

        $objTpl = new \Cx\Core\Html\Sigma($wysiwyg->getDirectory(true) . '/View/Template/Backend');

        //merge language
        $langData = $objInit->loadLanguageData('Wysiwyg');
        $_ARRAYLANG = array_merge($_ARRAYLANG, $langData);
        $objTpl->setGlobalVariable($_ARRAYLANG);

        $objTpl->loadTemplatefile('Subnavigation.html');

        $tbl = isset($_GET['tpl']) ? contrexx_input2raw($_GET['tpl']) : '';

        switch ($tbl) {
            case 'Settings':
                $wysiwygBackendController->parsePage($objTpl, array('Settings'));
                break;
            case 'Functions':
                $wysiwygBackendController->parsePage($objTpl, array('Functions'));
                break;
            case '':
            default:
                $objTpl->addBlockfile('WYSIWYG_CONFIG_TEMPLATE', 'wysiwyg_template', 'Default.html');
                $wysiwygBackendController->parsePage($objTpl, array('WysiwygTemplate'));
                break;
        }

        \JS::registerCSS(substr($wysiwyg->getDirectory(false, true) . '/View/Style/Backend.css', 1));

        $objTemplate->setVariable(array(
            'CONTENT_TITLE' => $_ARRAYLANG['TXT_CORE_WYSIWYG'],
            'ADMIN_CONTENT' => $objTpl->get(),
        ));
    }


    /**
     * Show PDF
     */
    protected function showPdf()
    {
        $pdf = Cx::instanciate()->getComponent('Pdf');
        $pdfBackendController = $pdf->getController('Backend');
        $pdfBackendController->parsePage(Cx::instanciate()->getTemplate(), array('PdfTemplate'));
    }

    /**
     * Set the cms system settings
     * @global  ADONewConnection
     * @global  array   Core language
     * @global  \Cx\Core\Html\Sigma
     */
   function showSettings() {
        global $objTemplate,$_ARRAYLANG;
        $template = new \Cx\Core\Html\Sigma();
        $objTemplate->addBlockfile('ADMIN_CONTENT', 'settings_system', 'settings.html');
        $templateObj = new \Cx\Core\Html\Sigma(ASCMS_CORE_PATH . '/Config/View/Template/Backend');
        $templateObj->loadTemplateFile('development_tools.html');
        $templateObj->setVariable(array(
            'TXT_TITLE_SET5'                            => $_ARRAYLANG['TXT_SETTINGS_TITLE_DEVELOPMENT'],
            'TXT_DEBUGGING_STATUS'                      => $_ARRAYLANG['TXT_DEBUGGING_STATUS'],
            'TXT_DEBUGGING_FLAGS'                       => $_ARRAYLANG['TXT_DEBUGGING_FLAGS'],
            'TXT_SETTINGS_DEBUGGING_FLAG_LOG'           => $_ARRAYLANG['TXT_SETTINGS_DEBUGGING_FLAG_LOG'],
            'TXT_SETTINGS_DEBUGGING_FLAG_PHP'           => $_ARRAYLANG['TXT_SETTINGS_DEBUGGING_FLAG_PHP'],
            'TXT_SETTINGS_DEBUGGING_FLAG_DB'            => $_ARRAYLANG['TXT_SETTINGS_DEBUGGING_FLAG_DB'],
            'TXT_SETTINGS_DEBUGGING_FLAG_DB_TRACE'      => $_ARRAYLANG['TXT_SETTINGS_DEBUGGING_FLAG_DB_TRACE'],
            'TXT_SETTINGS_DEBUGGING_FLAG_DB_CHANGE'     => $_ARRAYLANG['TXT_SETTINGS_DEBUGGING_FLAG_DB_CHANGE'],
            'TXT_SETTINGS_DEBUGGING_FLAG_DB_ERROR'      => $_ARRAYLANG['TXT_SETTINGS_DEBUGGING_FLAG_DB_ERROR'],
            'TXT_SETTINGS_DEBUGGING_FLAG_LOG_FILE'      => $_ARRAYLANG['TXT_SETTINGS_DEBUGGING_FLAG_LOG_FILE'],
            'TXT_SETTINGS_DEBUGGING_FLAG_LOG_FIREPHP'   => $_ARRAYLANG['TXT_SETTINGS_DEBUGGING_FLAG_LOG_FIREPHP'],
            'TXT_DEBUGGING_EXPLANATION'                 => $_ARRAYLANG['TXT_DEBUGGING_EXPLANATION'],
            'TXT_SAVE_CHANGES'                          => $_ARRAYLANG['TXT_SAVE'],
            'TXT_RADIO_ON'                              => $_ARRAYLANG['TXT_ACTIVATED'],
            'TXT_RADIO_OFF'                             => $_ARRAYLANG['TXT_DEACTIVATED']
            ));
        $cx = \Cx\Core\Core\Controller\Cx::instanciate();
        if (in_array('SystemInfo', $cx->getLicense()->getLegalComponentsList())) {
            if (isset($_POST['debugging'])) {
                $this->updateDebugSettings($_POST['debugging']);
            }
            $this->setDebuggingVariables($templateObj);
        }
        \Cx\Core\Setting\Controller\Setting::init('Config', null, 'Yaml', null, \Cx\Core\Setting\Controller\Setting::REPOPULATE);
        \Cx\Core\Setting\Controller\Setting::storeFromPost();

        \Cx\Core\Setting\Controller\Setting::setEngineType('Config', 'Yaml', 'site');
        \Cx\Core\Setting\Controller\Setting::show(
                $template,
                'index.php?cmd=Config',
                $_ARRAYLANG['TXT_CORE_CONFIG_SITE'],
                $_ARRAYLANG['TXT_CORE_CONFIG_SITE'],
                'TXT_CORE_CONFIG_',
                !$this->isWritable()
                );
        \Cx\Core\Setting\Controller\Setting::setEngineType('Config', 'Yaml', 'contactInformation');
        \Cx\Core\Setting\Controller\Setting::show(
                $template,
                'index.php?cmd=Config',
                $_ARRAYLANG['TXT_CORE_CONFIG_CONTACTINFORMATION'],
                $_ARRAYLANG['TXT_CORE_CONFIG_CONTACTINFORMATION'],
                'TXT_CORE_CONFIG_',
                !$this->isWritable()
                );
        \Cx\Core\Setting\Controller\Setting::setEngineType('Config', 'Yaml', 'administrationArea');
        \Cx\Core\Setting\Controller\Setting::show(
                $template,
                'index.php?cmd=Config',
                $_ARRAYLANG['TXT_CORE_CONFIG_ADMINISTRATIONAREA'],
                $_ARRAYLANG['TXT_CORE_CONFIG_ADMINISTRATIONAREA'],
                'TXT_CORE_CONFIG_',
                !$this->isWritable()
                );
        \Cx\Core\Setting\Controller\Setting::setEngineType('Config', 'Yaml', 'security');
        \Cx\Core\Setting\Controller\Setting::show(
                $template,
                'index.php?cmd=Config',
                $_ARRAYLANG['TXT_CORE_CONFIG_SECURITY'],
                $_ARRAYLANG['TXT_CORE_CONFIG_SECURITY'],
                'TXT_CORE_CONFIG_',
                !$this->isWritable()
                );
        if (in_array('SystemInfo', $cx->getLicense()->getLegalComponentsList())) {
            \Cx\Core\Setting\Controller\Setting::show_external(
                $template,
                $_ARRAYLANG['TXT_SETTINGS_TITLE_DEVELOPMENT'],
                $templateObj->get()
            );
        }
        \Cx\Core\Setting\Controller\Setting::setEngineType('Config', 'Yaml', 'otherConfigurations');
        \Cx\Core\Setting\Controller\Setting::show(
                $template,
                'index.php?cmd=Config',
                $_ARRAYLANG['TXT_CORE_CONFIG_OTHERCONFIGURATIONS'],
                $_ARRAYLANG['TXT_CORE_CONFIG_OTHERCONFIGURATIONS'],
                'TXT_CORE_CONFIG_',
                !$this->isWritable()
                );


        // show also hidden settins
        if (   in_array('SystemInfo', $cx->getLicense()->getLegalComponentsList())
            && \Permission::hasAllAccess()
            && isset($_GET['all'])
        ) {
            \Cx\Core\Setting\Controller\Setting::setEngineType('Config', 'Yaml', 'core');
            \Cx\Core\Setting\Controller\Setting::show(
                    $template,
                    'index.php?cmd=Config',
                    'CORE',
                    'CORE',
                    'TXT_CORE_CONFIG_',
                    true
                    );
            \Cx\Core\Setting\Controller\Setting::setEngineType('Config', 'Yaml', 'release');
            \Cx\Core\Setting\Controller\Setting::show(
                    $template,
                    'index.php?cmd=Config',
                    'RELEASE',
                    'RELEASE',
                    'TXT_CORE_CONFIG_',
                    true
                    );
            \Cx\Core\Setting\Controller\Setting::setEngineType('Config', 'Yaml', 'component');
            \Cx\Core\Setting\Controller\Setting::show(
                    $template,
                    'index.php?cmd=Config',
                    'COMPONENT',
                    'COMPONENT',
                    'TXT_CORE_CONFIG_',
                    !$this->isWritable()
                    );
            \Cx\Core\Setting\Controller\Setting::setEngineType('Config', 'Yaml', 'license');
            \Cx\Core\Setting\Controller\Setting::show(
                    $template,
                    'index.php?cmd=Config',
                    'LICENSE',
                    'LICENSE',
                    'TXT_CORE_CONFIG_',
                    true
                    );
            \Cx\Core\Setting\Controller\Setting::setEngineType('Config', 'Yaml', 'cache');
            \Cx\Core\Setting\Controller\Setting::show(
                    $template,
                    'index.php?cmd=Config',
                    'CACHE',
                    'CACHE',
                    'TXT_CORE_CONFIG_',
                    true
                    );
        }
        $scriptPath = $cx->getCodeBaseCoreWebPath() . '/Config/View/Script/Backend.js';
        \JS::registerJS(substr($scriptPath, 1));

        $this->checkFtpAccess();
        $objTemplate->setVariable('SETTINGS_TABLE', $template->get());
        $objTemplate->parse('settings_system');
    }

    /**
     * Returns all available timezones
     *
     * @access  private
     * @param   string      $selectedTimezone   name of the selected timezone
     * @return  string      $timezoneOptions    available timezones as HTML <option></option>
     */
    public static function getTimezoneOptions() {
        $timezoneOptions = array();
        foreach (timezone_identifiers_list() as $timezone) {
            $dateTimeZone = new \DateTimeZone($timezone);
            $dateTime     = new \DateTime('now', $dateTimeZone);
            $timeOffset   = $dateTimeZone->getOffset($dateTime);
            $plusOrMinus  = $timeOffset < 0 ? '-' : '+';
            $gmt          = 'GMT ' . $plusOrMinus . ' ' . gmdate('g:i', $timeOffset);
            $timezoneOptions[] = $timezone.":".$timezone."(".$gmt.")";
        }
        return implode(',',$timezoneOptions);
    }

    /**
     * Returns port options
     *
     * @return string  port options as string
     */
    public static function getPortOptions() {
        global $_ARRAYLANG;
        $options = array(
            'none:' .  $_ARRAYLANG['TXT_SETTINGS_FORCE_PROTOCOL_NONE'],
            'http:' .  $_ARRAYLANG['TXT_SETTINGS_FORCE_PROTOCOL_HTTP'],
            'https:' .  $_ARRAYLANG['TXT_SETTINGS_FORCE_PROTOCOL_HTTPS'],
        );
        return implode(',', $options);
    }

    /**
     * Returns captcha options
     *
     * @return string captcha options as string
     */
    public static function getCaptchaOptions()
    {
        global $_ARRAYLANG;

        $options = array(
            'contrexxCaptcha:' .  $_ARRAYLANG['TXT_CORE_CONFIG_CONTREXX_CAPTCHA_LABEL'],
            'reCaptcha:' .  $_ARRAYLANG['TXT_CORE_CONFIG_RECAPTCHA_LABEL']
        );
        return implode(',', $options);
    }

    /**
     * Sets debugging related template variables according to session state.
     *
     * @param template the Sigma tpl
     */
    protected function setDebuggingVariables($template) {
        $status = $_SESSION['debugging'];
        $flags = $_SESSION['debugging_flags'];

        $flags = $this->debuggingFlagArrayFromFlags($flags);

        $template->setVariable(array(
            'DEBUGGING_HIDE_FLAGS' => $this->stringIfTrue(!$status,'style="display:none;"'),
            'SETTINGS_DEBUGGING_ON' => $this->stringIfTrue($status,'checked="checked"'),
            'SETTINGS_DEBUGGING_OFF' => $this->stringIfTrue(!$status,'checked="checked"'),
            'SETTINGS_DEBUGGING_FLAG_LOG' => $this->stringIfTrue($flags['log'] || !$status,'checked="checked"'),
            'SETTINGS_DEBUGGING_FLAG_PHP' => $this->stringIfTrue($flags['php'] || !$status,'checked="checked"'),
            'SETTINGS_DEBUGGING_FLAG_DB' => $this->stringIfTrue($flags['db'],'checked="checked"'),
            'SETTINGS_DEBUGGING_FLAG_DB_TRACE' => $this->stringIfTrue($flags['db_trace'] || !$status,'checked="checked"'),
            'SETTINGS_DEBUGGING_FLAG_DB_CHANGE' => $this->stringIfTrue($flags['db_change'] || !$status,'checked="checked"'),
            'SETTINGS_DEBUGGING_FLAG_DB_ERROR' => $this->stringIfTrue($flags['db_error'] || !$status,'checked="checked"'),
            'SETTINGS_DEBUGGING_FLAG_LOG_FIREPHP' => $this->stringIfTrue($flags['log_firephp'],'checked="checked"'),
            'SETTINGS_DEBUGGING_FLAG_LOG_FILE' => $this->stringIfTrue($flags['log_file'],'checked="checked"')
        ));
    }

    /**
     * returns $str if $check is true, else ''
     */
    protected function stringIfTrue($check, $str) {
        if($check)
            return $str;
        return '';
    }

    /**
     * Checks whether the currently configured domain url is accessible
     * @param string $protocol the protocol to check for access
     * @return bool true if the domain is accessable
     */
    public static function checkAccessibility($protocol = 'http') {
        global $_CONFIG;
        if (!in_array($protocol, array('http', 'https'))) {
            return false;
        }

        try {
            // create request to port 443 (https), to check whether the request works or not
            $request = new \HTTP_Request2($protocol . '://' . $_CONFIG['domainUrl'] . ASCMS_ADMIN_WEB_PATH . '/index.php?cmd=JsonData');

            // ignore ssl issues
            // otherwise, cloudrexx does not activate 'https' when the server doesn't have an ssl certificate installed
            $request->setConfig(array(
                'ssl_verify_peer' => false,
                'ssl_verify_host' => false,
            ));

            // send the request
            // if this does not work, because there is no ssl support, an exception is thrown
            $objResponse = $request->send();

            // get the status code from the request
            $result = json_decode($objResponse->getBody());

            // get the status code from the request
            $status = $objResponse->getStatus();
            if (in_array($status, array(500))) {
                return false;
            }
            // the request should return a json object with the status 'error' if it is a cloudrexx installation
            if (!$result || $result->status != 'error') {
                return false;
            }
        } catch (\HTTP_Request2_Exception $e) {
            // https is not available, exception thrown
            return false;
        }
        return true;
    }

    /**
     * Calculates a flag value as passed to DBG::activate() from an array.
     * @param array flags array('php' => bool, 'db' => bool, 'db_error' => bool, 'log_firephp' => bool
     * @return int an int with the flags set.
     */
    protected function debuggingFlagsFromFlagArray($flags) {
        $ret = 0;
        if(isset($flags['log']) && $flags['log'])
            $ret |= DBG_LOG;
        if(isset($flags['php']) && $flags['php'])
            $ret |= DBG_PHP;
        if(isset($flags['db']) && $flags['db'])
            $ret |= DBG_DB;
        if(isset($flags['db_change']) && $flags['db_change'])
            $ret |= DBG_DB_CHANGE;
        if(isset($flags['db_error']) && $flags['db_error'])
            $ret |= DBG_DB_ERROR;
        if(isset($flags['db_trace']) && $flags['db_trace'])
            $ret |= DBG_DB_TRACE;
        if(isset($flags['log_file']) && $flags['log_file'])
            $ret |= DBG_LOG_FILE;
        if(isset($flags['log_firephp']) && $flags['log_firephp'])
            $ret |= DBG_LOG_FIREPHP;

        return $ret;
    }

    /**
     * Analyzes an int as passed to DBG::activate() and yields an array containing information about the flags.
     * @param int $flags
     * @return array('php' => bool, 'db' => bool, 'db_error' => bool, 'log_firephp' => bool
     */
    protected function debuggingFlagArrayFromFlags($flags) {
        return array(
            'log' => (bool)($flags & DBG_LOG),
            'php' => (bool)($flags & DBG_PHP),
            'db' => (bool)($flags & DBG_DB),
            'db_change' => (bool)($flags & DBG_DB_CHANGE),
            'db_error' => (bool)($flags & DBG_DB_ERROR),
            'db_trace' => (bool)($flags & DBG_DB_TRACE),
            'log_firephp' => (bool)($flags & DBG_LOG_FIREPHP),
            'log_file' => (bool)($flags & DBG_LOG_FILE)
        );
    }

    protected function updateDebugSettings($settings) {
        $status = $settings['status'] == "on";
        $flags = array();

        if(isset($settings['flag_log'])) {
            $flags['log'] = $settings['flag_log'];
        }
        if(isset($settings['flag_php'])) {
            $flags['php'] = $settings['flag_php'];
        }
        if(isset($settings['flag_db'])) {
            $flags['db'] = $settings['flag_db'];
        }
        if(isset($settings['flag_db_change'])) {
            $flags['db_change'] = $settings['flag_db_change'];
        }
        if(isset($settings['flag_db_error'])) {
            $flags['db_error'] = $settings['flag_db_error'];
        }
        if(isset($settings['flag_db_trace'])) {
            $flags['db_trace'] = $settings['flag_db_trace'];
        }
        if(isset($settings['flag_log_firephp'])) {
            $flags['log_firephp'] = $settings['flag_log_firephp'];
        }
        if(isset($settings['flag_log_file'])) {
            $flags['log_file'] = $settings['flag_log_file'];
        }

        $flags = $this->debuggingFlagsFromFlagArray($flags);

        if ($status) {
            $_SESSION['debugging'] = true;
        } else {
            unset($_SESSION['debugging']);
        }
        $_SESSION['debugging_flags'] = $flags;
    }

    /**
     * Write all settings to the config file
     *
     */
    public static function updatePhpCache() {
        global $_ARRAYLANG, $_CONFIG;

        if (!\Cx\Lib\FileSystem\FileSystem::makeWritable(self::getSettingsFile())) {
            \Message::add(self::getSettingsFile().' '.$_ARRAYLANG['TXT_SETTINGS_ERROR_WRITABLE'], \Message::CLASS_ERROR);
            return false;
        }

        //get values from ymlsetting
        \Cx\Core\Setting\Controller\Setting::init('Config', NULL,'Yaml', null, \Cx\Core\Setting\Controller\Setting::REPOPULATE);
        $ymlArray = \Cx\Core\Setting\Controller\Setting::getArray('Config', null);
        $intMaxLen = 0;
        $ymlArrayValues = array();
        foreach ($ymlArray as $key => $ymlValue){
            $_CONFIG[$key] = $ymlValue['value'];
            $ymlArrayValues[$ymlValue['group']][$key] = $ymlValue['value'];

            // special case to add legacy domainUrl configuration option
            if ($key == 'mainDomainId') {
                $domainRepository = new \Cx\Core\Net\Model\Repository\DomainRepository();
                $objMainDomain = $domainRepository->findOneBy(array('id' => $ymlArray[$key]['value']));
                if ($objMainDomain) {
                    $domainUrl = $objMainDomain->getName();
                } else {
                    $domainUrl = $_SERVER['SERVER_NAME'];
                }
                $ymlArrayValues[$ymlValue['group']]['domainUrl'] = $domainUrl;
                if ($_CONFIG['xmlSitemapStatus'] == 'on') {
                    \Cx\Core\PageTree\XmlSitemapPageTree::write();
                }
            }

            $intMaxLen = (strlen($key) > $intMaxLen) ? strlen($key) : $intMaxLen;
        }
        $intMaxLen += strlen('$_CONFIG[\'\']') + 1; //needed for formatted output

        // update environment
        \Env::set('config', $_CONFIG);

        $strHeader  = "<?php\n";
        $strHeader .= "/**\n";
        $strHeader .= "* This file is generated by the \"settings\"-menu in your CMS.\n";
        $strHeader .= "* Do not try to edit it manually!\n";
        $strHeader .= "*/\n\n";

        $strFooter = "?>";

        //Write values
        $data = $strHeader;

        $strBody = '';
        foreach ($ymlArrayValues as $group => $sectionValues) {
            $strBody .= "/**\n";
            $strBody .= "* -------------------------------------------------------------------------\n";
            $strBody .= "* ".ucfirst($group)."\n";
            $strBody .= "* -------------------------------------------------------------------------\n";
            $strBody .= "*/\n";

            foreach($sectionValues as $sectionName => $sectionNameValue) {
                $strBody .= sprintf("%-".$intMaxLen."s",'$_CONFIG[\''.$sectionName.'\']');
                $strBody .= "= ";
                $strBody .= (self::isANumber($sectionNameValue) ? $sectionNameValue : '"'.str_replace('"', '\"', $sectionNameValue).'"').";\n";
            }
            $strBody .= "\n";
        }

        $data .= $strBody;
        $data .= $strFooter;

        try {
            $objFile = new \Cx\Lib\FileSystem\File(self::getSettingsFile());
            $objFile->write($data);

            // Drop complete cache (page and ESI)
            $cx = \Cx\Core\Core\Controller\Cx::instanciate();
            $cx->getComponent('Cache')->clearCache();
            return true;
        } catch (\Cx\Lib\FileSystem\FileSystemException $e) {
            \DBG::msg($e->getMessage());
        }

        return false;
    }

    /**
     * Check whether the given string is a number or not.
     * Integers with leading zero results in 0, this method prevents that.
     * @param string $value The value to check
     * @return bool true if the string is a number, false if not
     */
    static function isANumber($value) {
        // check whether the integer value has the same length like the entered string
        return is_numeric($value) && strlen(intval($value)) == strlen($value);
    }

    function smtp()
    {
        if (empty($_REQUEST['tpl'])) {
            $_REQUEST['tpl'] = '';
        }

        switch ($_REQUEST['tpl']) {
            case 'modify':
                $this->_smtpModify();
                break;

            case 'delete':
                $this->_smtpDeleteAccount();
                $this->_smtpOverview();
                break;

            case 'default':
                $this->_smtpDefaultAccount();
                $this->_smtpOverview();
                break;

            default:
                $this->_smtpOverview();
        }
    }


    function _smtpDefaultAccount()
    {
        global $_ARRAYLANG;

        $id = intval($_GET['id']);
        $arrSmtp = \SmtpSettings::getSmtpAccount($id, false);
        if ($arrSmtp || ($id = 0) !== false) {
            \Cx\Core\Setting\Controller\Setting::init('Config', 'core','Yaml');
            \Cx\Core\Setting\Controller\Setting::set('coreSmtpServer', $id);
            if (\Cx\Core\Setting\Controller\Setting::update('coreSmtpServer')) {
                $this->strOkMessage .= sprintf($_ARRAYLANG['TXT_SETTINGS_DEFAULT_SMTP_CHANGED'], htmlentities($arrSmtp['name'], ENT_QUOTES, CONTREXX_CHARSET)).'<br />';
            } else {
                $this->strErrMessage[] = $_ARRAYLANG['TXT_SETTINGS_CHANGE_DEFAULT_SMTP_FAILED'];
            }
        }
    }


    function _smtpDeleteAccount()
    {
        global $objDatabase, $_CONFIG, $_ARRAYLANG;

        $id = intval($_GET['id']);
        $arrSmtp = \SmtpSettings::getSmtpAccount($id, false);
        if ($arrSmtp !== false) {
            if ($id != $_CONFIG['coreSmtpServer']) {
                if ($objDatabase->Execute('DELETE FROM `'.DBPREFIX.'settings_smtp` WHERE `id`='.$id) !== false) {
                    $this->strOkMessage .= sprintf($_ARRAYLANG['TXT_SETTINGS_SMTP_DELETE_SUCCEED'], htmlentities($arrSmtp['name'], ENT_QUOTES, CONTREXX_CHARSET)).'<br />';
                } else {
                    $this->strErrMessage[] = sprintf($_ARRAYLANG['TXT_SETTINGS_SMTP_DELETE_FAILED'], htmlentities($arrSmtp['name'], ENT_QUOTES, CONTREXX_CHARSET));
                }
            } else {
                $this->strErrMessage[] = sprintf($_ARRAYLANG['TXT_SETTINGS_COULD_NOT_DELETE_DEAULT_SMTP'], htmlentities($arrSmtp['name'], ENT_QUOTES, CONTREXX_CHARSET));
            }
        }
    }


    function _smtpOverview()
    {
        global $_ARRAYLANG, $objTemplate, $_CONFIG;

        $objTemplate->addBlockfile('ADMIN_CONTENT', 'settings_smtp', 'settings_smtp.html');
        $this->strPageTitle = $_ARRAYLANG['TXT_SETTINGS_EMAIL_ACCOUNTS'];

        $objTemplate->setVariable(array(
            'TXT_SETTINGS_EMAIL_ACCOUNTS'            => $_ARRAYLANG['TXT_SETTINGS_EMAIL_ACCOUNTS'],
            'TXT_SETTINGS_ACCOUNT'                    => $_ARRAYLANG['TXT_SETTINGS_ACCOUNT'],
            'TXT_SETTINGS_HOST'                        => $_ARRAYLANG['TXT_SETTINGS_HOST'],
            'TXT_SETTINGS_USERNAME'                    => $_ARRAYLANG['TXT_SETTINGS_USERNAME'],
            'TXT_SETTINGS_STANDARD'                    => $_ARRAYLANG['TXT_SETTINGS_STANDARD'],
            'TXT_SETTINGS_FUNCTIONS'                => $_ARRAYLANG['TXT_SETTINGS_FUNCTIONS'],
            'TXT_SETTINGS_ADD_NEW_SMTP_ACCOUNT'        => $_ARRAYLANG['TXT_SETTINGS_ADD_NEW_SMTP_ACCOUNT'],
            'TXT_SETTINGS_CONFIRM_DELETE_ACCOUNT'    => $_ARRAYLANG['TXT_SETTINGS_CONFIRM_DELETE_ACCOUNT'],
            'TXT_SETTINGS_OPERATION_IRREVERSIBLE'    => $_ARRAYLANG['TXT_SETTINGS_OPERATION_IRREVERSIBLE']
        ));

        $objTemplate->setGlobalVariable(array(
            'TXT_SETTINGS_MODFIY'                    => $_ARRAYLANG['TXT_SETTINGS_MODFIY'],
            'TXT_SETTINGS_DELETE'                    => $_ARRAYLANG['TXT_SETTINGS_DELETE']
        ));

        $nr = 1;
        foreach (\SmtpSettings::getSmtpAccounts() as $id => $arrSmtp) {
            if ($id) {
                $objTemplate->setVariable(array(
                    'SETTINGS_SMTP_ACCOUNT_ID'    => $id,
                    'SETTINGS_SMTP_ACCOUNT_JS'    => htmlentities(addslashes($arrSmtp['name']), ENT_QUOTES, CONTREXX_CHARSET)
                ));
                $objTemplate->parse('settings_smtp_account_functions');
            } else {
                $objTemplate->hideBlock('settings_smtp_account_functions');
            }
            $objTemplate->setVariable(array(
                'SETTINGS_ROW_CLASS_ID'        => $nr++ % 2 + 1,
                'SETTINGS_SMTP_ACCOUNT_ID'    => $id,
                'SETTINGS_SMTP_ACCOUNT'        => htmlentities($arrSmtp['name'], ENT_QUOTES, CONTREXX_CHARSET),
                'SETTINGS_SMTP_HOST'        => !empty($arrSmtp['hostname']) ? htmlentities($arrSmtp['hostname'], ENT_QUOTES, CONTREXX_CHARSET) : '&nbsp;',
                'SETTINGS_SMTP_USERNAME'    => !empty($arrSmtp['username']) ? htmlentities($arrSmtp['username'], ENT_QUOTES, CONTREXX_CHARSET) : '&nbsp;',
                'SETTINGS_SMTP_DEFAULT'        => $id == $_CONFIG['coreSmtpServer'] ? 'checked="checked"' : '',
                'SETTINGS_SMTP_OPTION_DISABLED' => $this->isWritable() ? '' : 'disabled="disabled"'
            ));
            $objTemplate->parse('settings_smtp_accounts');
        }

        $objTemplate->parse('settings_smtp');
    }


    function _smtpModify()
    {
        global $objTemplate, $_ARRAYLANG;

        $error = false;
        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

        if (isset($_POST['settings_smtp_save'])) {
            $arrSmtp = array(
                'name'        => !empty($_POST['settings_smtp_account']) ? contrexx_stripslashes(trim($_POST['settings_smtp_account'])) : '',
                'hostname'    => !empty($_POST['settings_smtp_hostname']) ? contrexx_stripslashes(trim($_POST['settings_smtp_hostname'])) : '',
                'port'        => !empty($_POST['settings_smtp_port']) ? intval($_POST['settings_smtp_port']) : 25,
                'username'    => !empty($_POST['settings_smtp_username']) ? contrexx_stripslashes(trim($_POST['settings_smtp_username'])) : '',
                'password'    => !empty($_POST['settings_smtp_password']) ? contrexx_stripslashes($_POST['settings_smtp_password']) : ''
            );

            if (!$arrSmtp['port']) {
                $arrSmtp['port'] = 25;
            }

            if (empty($arrSmtp['name'])) {
                $error = true;
                $this->strErrMessage[] = $_ARRAYLANG['TXT_SETTINGS_EMPTY_ACCOUNT_NAME_TXT'];
            } elseif (!\SmtpSettings::_isUniqueSmtpAccountName($arrSmtp['name'], $id)) {
                $error = true;
                $this->strErrMessage[] = sprintf($_ARRAYLANG['TXT_SETTINGS_NOT_UNIQUE_SMTP_ACCOUNT_NAME'], htmlentities($arrSmtp['name']));
            }

            if (empty($arrSmtp['hostname'])) {
                $error = true;
                $this->strErrMessage[] = $_ARRAYLANG['TXT_SETTINGS_EMPTY_SMTP_HOST_TXT'];
            }

            if (!$error) {
                if ($id) {
                    if (\SmtpSettings::_updateSmtpAccount($id, $arrSmtp)) {
                        $this->strOkMessage .= sprintf($_ARRAYLANG['TXT_SETTINGS_SMTP_ACCOUNT_UPDATE_SUCCEED'], $arrSmtp['name']).'<br />';
                        return $this->_smtpOverview();
                    } else {
                        $this->strErrMessage[] = sprintf($_ARRAYLANG['TXT_SETTINGS_SMTP_ACCOUNT_UPDATE_FAILED'], $arrSmtp['name']);
                    }
                } else {
                    if (\SmtpSettings::_addSmtpAccount($arrSmtp)) {
                        $this->strOkMessage .= sprintf($_ARRAYLANG['TXT_SETTINGS_SMTP_ACCOUNT_ADD_SUCCEED'], $arrSmtp['name']).'<br />';
                        return $this->_smtpOverview();
                    } else {
                        $this->strErrMessage[] = $_ARRAYLANG['TXT_SETTINGS_SMTP_ACCOUNT_ADD_FAILED'];
                    }
                }
            }
        } else {
            $arrSmtp = \SmtpSettings::getSmtpAccount($id, false);
            if ($arrSmtp === false) {
                $id = 0;
                $arrSmtp = array(
                    'name'        => '',
                    'hostname'    => '',
                    'port'        => 25,
                    'username'    => '',
                    'password'    => 0
                );
            }
        }

        $objTemplate->addBlockfile('ADMIN_CONTENT', 'settings_smtp_modify', 'settings_smtp_modify.html');
        $this->strPageTitle = $id ? $_ARRAYLANG['TXT_SETTINGS_MODIFY_SMTP_ACCOUNT'] : $_ARRAYLANG['TXT_SETTINGS_ADD_NEW_SMTP_ACCOUNT'];

        $objTemplate->setVariable(array(
            'TXT_SETTINGS_ACCOUNT'                    => $_ARRAYLANG['TXT_SETTINGS_ACCOUNT'],
            'TXT_SETTINGS_NAME_OF_ACCOUNT'            => $_ARRAYLANG['TXT_SETTINGS_NAME_OF_ACCOUNT'],
            'TXT_SETTINGS_SMTP_SERVER'                => $_ARRAYLANG['TXT_SETTINGS_SMTP_SERVER'],
            'TXT_SETTINGS_HOST'                        => $_ARRAYLANG['TXT_SETTINGS_HOST'],
            'TXT_SETTINGS_PORT'                        => $_ARRAYLANG['TXT_SETTINGS_PORT'],
            'TXT_SETTINGS_AUTHENTICATION'            => $_ARRAYLANG['TXT_SETTINGS_AUTHENTICATION'],
            'TXT_SETTINGS_USERNAME'                    => $_ARRAYLANG['TXT_SETTINGS_USERNAME'],
            'TXT_SETTINGS_PASSWORD'                    => $_ARRAYLANG['TXT_SETTINGS_PASSWORD'],
            'TXT_SETTINGS_SMTP_AUTHENTICATION_TXT'    => $_ARRAYLANG['TXT_SETTINGS_SMTP_AUTHENTICATION_TXT'],
            'TXT_SETTINGS_BACK'                        => $_ARRAYLANG['TXT_SETTINGS_BACK'],
            'TXT_SETTINGS_SAVE'                        => $_ARRAYLANG['TXT_SETTINGS_SAVE']
        ));

        $objTemplate->setVariable(array(
            'SETTINGS_SMTP_TITLE'        => $id ? $_ARRAYLANG['TXT_SETTINGS_MODIFY_SMTP_ACCOUNT'] : $_ARRAYLANG['TXT_SETTINGS_ADD_NEW_SMTP_ACCOUNT'],
            'SETTINGS_SMTP_ID'            => $id,
            'SETTINGS_SMTP_ACCOUNT'        => htmlentities($arrSmtp['name'], ENT_QUOTES, CONTREXX_CHARSET),
            'SETTINGS_SMTP_HOST'        => htmlentities($arrSmtp['hostname'], ENT_QUOTES, CONTREXX_CHARSET),
            'SETTINGS_SMTP_PORT'        => $arrSmtp['port'],
            'SETTINGS_SMTP_USERNAME'    => htmlentities($arrSmtp['username'], ENT_QUOTES, CONTREXX_CHARSET),
            'SETTINGS_SMTP_PASSWORD'    => str_pad('', $arrSmtp['password'], ' ')
        ));

        $objTemplate->parse('settings_smtp_modify');
        return true;
    }

    /**
     * Shows the image settings page
     *
     * @access  public
     *
     * @param $arrData
     *
     * @throws \Exception
     * @return  boolean  true on success, false otherwise
     */
    public function image($arrData)
    {
        \JS::registerCSS(substr(ASCMS_CORE_MODULE_FOLDER . '/MediaBrowser/View/Style/mediabrowser.css', 1));
        global $objDatabase, $objTemplate, $_ARRAYLANG;

        $this->strPageTitle = $_ARRAYLANG['TXT_SETTINGS_IMAGE'];
        $objTemplate->addBlockfile('ADMIN_CONTENT', 'settings_image', 'settings_image.html');

        \ContrexxJavascript::getInstance()->setVariable(array(
            'publicTempPath'        => Cx::instanciate()->getWebsitePublicTempWebPath(),
        ), 'config/image');

        // Saves the settings
        if (isset($arrData['submit'])) {
            $arrSettings['image_cut_width']    = contrexx_input2db(intval($arrData['image_cut_width']));
            $arrSettings['image_cut_height']   = contrexx_input2db(intval($arrData['image_cut_height']));
            //$arrSettings['image_scale_width']  = contrexx_input2db(intval($arrData['image_scale_width']));
            //$arrSettings['image_scale_height'] = contrexx_input2db(intval($arrData['image_scale_height']));
            $arrSettings['image_compression']  = contrexx_input2db(intval($arrData['image_compression']));

            foreach ($arrSettings as $name => $value) {
                $query = '
                    UPDATE `'.DBPREFIX.'settings_image`
                    SET `value` = "'.$value.'"
                    WHERE `name` = "'.$name.'"
                ';
                $objResult = $objDatabase->Execute($query);
                if ($objResult === false) {
                    throw new \Exception('Could not update the settings');
                }
            }

            $this->strOkMessage = $_ARRAYLANG['TXT_SETTINGS_UPDATED'];
        }

        /**
         * @var $cx \Cx\Core\Core\Controller\Cx
         */
        $cx         = \Env::get('cx');
        $pdo        = $cx->getDb()->getPdoConnection();
        $sth        = $pdo->query('SELECT id, name, size FROM  `' . DBPREFIX . 'settings_thumbnail`');
        $thumbnails = $sth->fetchAll();

        $newThumbnailTemplate
            = new \Cx\Core\Html\Sigma($cx->getCodeBasePath());
        $newThumbnailTemplate->loadTemplateFile($cx->getCodeBaseCorePath() .'/Config/View/Template/Backend/settings_image_edit.html');
        $newThumbnailTemplate->removeUnknownVariables = false;
        $newThumbnailTemplate->setVariable(
            array(
                'TXT_IMAGE_TITLE'           => $_ARRAYLANG['TXT_SETTINGS_IMAGE_TITLE'],
                'TXT_IMAGE_CSRF'           => \Cx\Core\Csrf\Controller\Csrf::param(),
                'TXT_IMAGE_THUMBNAILS_DELETE'           => $_ARRAYLANG['TXT_IMAGE_THUMBNAILS_DELETE'],
                'TXT_IMAGE_CUT_WIDTH'       => $_ARRAYLANG['TXT_SETTINGS_IMAGE_CUT_WIDTH'],
                'TXT_IMAGE_CUT_HEIGHT'      => $_ARRAYLANG['TXT_SETTINGS_IMAGE_CUT_HEIGHT'],
                'TXT_IMAGE_THUMBNAILS'      => $_ARRAYLANG['TXT_IMAGE_THUMBNAILS'],
                //'TXT_IMAGE_SCALE_WIDTH'          => $_ARRAYLANG['TXT_SETTINGS_IMAGE_SCALE_WIDTH'],
                //'TXT_IMAGE_SCALE_HEIGHT'         => $_ARRAYLANG['TXT_SETTINGS_IMAGE_SCALE_HEIGHT'],
                'TXT_IMAGE_COMPRESSION'     => $_ARRAYLANG['TXT_SETTINGS_IMAGE_COMPRESSION'],
                'TXT_SAVE'                  => $_ARRAYLANG['TXT_SAVE'],
                'TXT_IMAGE_THUMBNAILS_ID'        => $_ARRAYLANG['TXT_IMAGE_THUMBNAILS_ID'],
                'TXT_IMAGE_THUMBNAILS_NAME'      => $_ARRAYLANG['TXT_IMAGE_THUMBNAILS_NAME'],
                'TXT_IMAGE_THUMBNAILS_SIZE'      => $_ARRAYLANG['TXT_IMAGE_THUMBNAILS_SIZE'],
                'TXT_SETTINGS_FUNCTIONS'      => $_ARRAYLANG['TXT_SETTINGS_FUNCTIONS'],
                'TXT_IMAGE_THUMBNAILS_RELOAD'      => $_ARRAYLANG['TXT_IMAGE_THUMBNAILS_RELOAD'],
                'TXT_IMAGE_THUMBNAILS_NEW'      => $_ARRAYLANG['TXT_IMAGE_THUMBNAILS_NEW'],

                'TXT_IMAGE_THUMBNAILS_MAX_SIZE' => $_ARRAYLANG['TXT_IMAGE_THUMBNAILS_MAX_SIZE'],
                'SETTINGS_IMAGE_CUT_WIDTH'  => !empty($arrSettings['image_cut_width']) ? $arrSettings['image_cut_width']
                    : 0,
                'SETTINGS_IMAGE_CUT_HEIGHT' => !empty($arrSettings['image_cut_height'])
                    ? $arrSettings['image_cut_height'] : 0,
                //'SETTINGS_IMAGE_SCALE_WIDTH'     => !empty($arrSettings['image_scale_width'])  ? $arrSettings['image_scale_width']  : 0,
                //'SETTINGS_IMAGE_SCALE_HEIGHT'    => !empty($arrSettings['image_scale_height']) ? $arrSettings['image_scale_height'] : 0,
            )
        );

        $objTemplate->setVariable(
            'CONFIG_THUMBNAIL_NEW_TEMPLATE',
            implode(' ', explode("\n", str_replace("'", "\"", $newThumbnailTemplate->get())))
        );

        foreach ($thumbnails as $thumbnail) {
            $objTemplate->setVariable(
                array(
                    'IMAGE_THUMBNAIL_ID' => $thumbnail['id'],
                    'IMAGE_THUMBNAIL_NAME' => $thumbnail['name'],
                    'IMAGE_THUMBNAIL_SIZE' => $thumbnail['size'],
                    'TXT_IMAGE_THUMBNAILS_MAXIMUM' => sprintf($_ARRAYLANG['TXT_IMAGE_THUMBNAILS_MAXIMUM'], $thumbnail['size'].'px'),
                )
            );

            $objTemplate->parse('settings_image_thumbnails_list');
        }


        // Gets the settings
        $query = '
            SELECT `name`, `value`
            FROM `'.DBPREFIX.'settings_image`
        ';
        $objResult = $objDatabase->Execute($query);
        if ($objResult !== false) {
            $arrSettings = array();
            while (!$objResult->EOF) {
                // Creates the settings array
                $arrSettings[$objResult->fields['name']] = $objResult->fields['value'];
                $objResult->MoveNext();
            }
        } else {
            throw new \Exception('Could not query the settings.');
        }

        // Defines the compression values
        $arrCompressionOptions = array();

        for ($i = 1; $i <= 20 ; $i++) {
            $arrCompressionOptions[] = $i * 5;
        }

        // Parses the compression options
        $imageCompression = !empty($arrSettings['image_compression']) ? intval($arrSettings['image_compression']) : 95;
        foreach ($arrCompressionOptions as $compression) {
            $objTemplate->setVariable(array(
                'IMAGE_COMPRESSION_VALUE' => $compression,
                'IMAGE_COMPRESSION_NAME'  => $compression,
                'OPTION_SELECTED'         => $compression == $imageCompression ? 'selected="selected"' : '',
            ));
            $objTemplate->parse('settings_image_compression_options');
        }

        // Parses the settings
        $objTemplate->setVariable(
            array(
                'TXT_IMAGE_TITLE'           => $_ARRAYLANG['TXT_SETTINGS_IMAGE_TITLE'],
                'TXT_IMAGE_CSRF'           => \Cx\Core\Csrf\Controller\Csrf::param(),
                'TXT_IMAGE_THUMBNAILS_DELETE'           => $_ARRAYLANG['TXT_IMAGE_THUMBNAILS_DELETE'],
                'TXT_IMAGE_CUT_WIDTH'       => $_ARRAYLANG['TXT_SETTINGS_IMAGE_CUT_WIDTH'],
                'TXT_IMAGE_CUT_HEIGHT'      => $_ARRAYLANG['TXT_SETTINGS_IMAGE_CUT_HEIGHT'],
                'TXT_IMAGE_THUMBNAILS'      => $_ARRAYLANG['TXT_IMAGE_THUMBNAILS'],
                //'TXT_IMAGE_SCALE_WIDTH'          => $_ARRAYLANG['TXT_SETTINGS_IMAGE_SCALE_WIDTH'],
                //'TXT_IMAGE_SCALE_HEIGHT'         => $_ARRAYLANG['TXT_SETTINGS_IMAGE_SCALE_HEIGHT'],
                'TXT_IMAGE_COMPRESSION'     => $_ARRAYLANG['TXT_SETTINGS_IMAGE_COMPRESSION'],
                'TXT_SAVE'                  => $_ARRAYLANG['TXT_SAVE'],
                'TXT_IMAGE_THUMBNAILS_ID'        => $_ARRAYLANG['TXT_IMAGE_THUMBNAILS_ID'],
                'TXT_IMAGE_THUMBNAILS_NAME'      => $_ARRAYLANG['TXT_IMAGE_THUMBNAILS_NAME'],
                'TXT_IMAGE_THUMBNAILS_SIZE'      => $_ARRAYLANG['TXT_IMAGE_THUMBNAILS_SIZE'],
                'TXT_SETTINGS_FUNCTIONS'      => $_ARRAYLANG['TXT_SETTINGS_FUNCTIONS'],
                'TXT_IMAGE_THUMBNAILS_RELOAD'      => $_ARRAYLANG['TXT_IMAGE_THUMBNAILS_RELOAD'],
                'TXT_IMAGE_THUMBNAILS_NEW'      => $_ARRAYLANG['TXT_IMAGE_THUMBNAILS_NEW'],

                'TXT_IMAGE_THUMBNAILS_MAX_SIZE' => $_ARRAYLANG['TXT_IMAGE_THUMBNAILS_MAX_SIZE'],
                'SETTINGS_IMAGE_CUT_WIDTH'  => !empty($arrSettings['image_cut_width']) ? $arrSettings['image_cut_width']
                    : 0,
                'SETTINGS_IMAGE_CUT_HEIGHT' => !empty($arrSettings['image_cut_height'])
                    ? $arrSettings['image_cut_height'] : 0,
                //'SETTINGS_IMAGE_SCALE_WIDTH'     => !empty($arrSettings['image_scale_width'])  ? $arrSettings['image_scale_width']  : 0,
                //'SETTINGS_IMAGE_SCALE_HEIGHT'    => !empty($arrSettings['image_scale_height']) ? $arrSettings['image_scale_height'] : 0,
            )
        );
        $objTemplate->parse('settings_image');

        \Cx\Core\Csrf\Controller\Csrf::add_placeholder($objTemplate);

        return true;
    }


    public function editThumbnails($post){
        /**
         * @var $cx \Cx\Core\Core\Controller\Cx
         */
        $cx  = \Env::get('cx');
        $pdo = $cx->getDb()->getPdoConnection();

        if (isset($_GET['deleteid'])) {
            $sth = $pdo->prepare(
                'DELETE FROM  `' . DBPREFIX . 'settings_thumbnail` WHERE id = :id'
            );
            $sth->bindParam(':id', $_GET['deleteid']);
            $sth->execute();
        }

        if (isset($_POST['name']) && isset($_POST['size'])) {
            $stmt = $pdo->prepare(
                'REPLACE INTO `' . DBPREFIX
                . 'settings_thumbnail`(id, name, size) VALUES (:id, :name, :size)'
            );
            $stmt->bindParam(':id', $_POST['id']);
            $stmt->bindParam(':name', $_POST['name']);
            $stmt->bindParam(':size', intval($_POST['size']));
            $stmt->execute();
        }
        Csrf::header('Location: index.php?cmd=Config&act=image');
        die;
    }

    /**
     * Load a settings.php file and return its configuration ($_CONFIG) as array
     *
     * @param   string  $file   The path to the settings.php file to load the $_CONFIG from
     * @return  array           Returns an array containing the loaded $_CONFIG from $file.
     *                          If $file does not exists or on error, it returns an empty array
     */
    static function fetchConfigFromSettingsFile($file) {
        if (!file_exists($file)) {
            return array();
        }

        $settingsContent = file_get_contents($file);
        // Execute code to load the settings into variable $_CONFIG.
        //
        // We must use eval() here as we must not use include(_once) here.
        // As we are not populating the loaded $_CONFIG array into the global space,
        // any later running components (in particular Cx\Core\Core\Controller\Cx)
        // would not be able to load the $_CONFIG array as the settings.php file
        // has already been loaded.
        //
        // The closing PHP tag is required as $settingsContent starts with a opening PHP tag (<?php).
        try {
            eval('?>' . $settingsContent);
        } catch (\Exception $e) {
            return array();
        }

        if (!isset($_CONFIG)) {
            return array();
        }

        return $_CONFIG;
    }

     /**
     * Initialize basic config of Cloudrexx
     *
     * @return  boolean                 False.  Always.
     * @throws  \Cx\Lib\Update_DatabaseException
     */
    static function init($configPath = null) {
        global $_CONFIG;

        try {
            // fetch $_CONFIG data from settings.php file
            // will be used for migration of basic configuration from contrexx_settings to \Cx\Core\Setting
            $existingConfig = self::fetchConfigFromSettingsFile(self::getSettingsFile());

            //site group
            \Cx\Core\Setting\Controller\Setting::init('Config', 'site','Yaml', $configPath);
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('systemStatus')
                && !\Cx\Core\Setting\Controller\Setting::add('systemStatus', isset($existingConfig['systemStatus']) ? $existingConfig['systemStatus'] : 'on', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_RADIO, 'on:TXT_ACTIVATED,off:TXT_DEACTIVATED', 'site')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for Page Status");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('languageDetection')
                && !\Cx\Core\Setting\Controller\Setting::add('languageDetection', isset($existingConfig['languageDetection']) ? $existingConfig['languageDetection'] : 'on', 2,
                \Cx\Core\Setting\Controller\Setting::TYPE_RADIO, 'on:TXT_ACTIVATED,off:TXT_DEACTIVATED', 'site')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for Auto Detect Language");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('coreGlobalPageTitle')
                && !\Cx\Core\Setting\Controller\Setting::add('coreGlobalPageTitle', isset($existingConfig['coreGlobalPageTitle']) ? $existingConfig['coreGlobalPageTitle'] : 'Cloudrexx Example Website', 3,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'site')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for Global Page Title");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('mainDomainId')
                    && !\Cx\Core\Setting\Controller\Setting::add('mainDomainId',  isset($existingConfig['mainDomainId']) ? $existingConfig['mainDomainId'] : '0', 4,
                    \Cx\Core\Setting\Controller\Setting::TYPE_DROPDOWN, '{src:\\'.__CLASS__.'::getDomains()}', 'site') ) {
                throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for Main Domain");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('forceDomainUrl')
                && !\Cx\Core\Setting\Controller\Setting::add('forceDomainUrl', isset($existingConfig['forceDomainUrl']) ? $existingConfig['forceDomainUrl'] : 'off', 5,
                \Cx\Core\Setting\Controller\Setting::TYPE_RADIO, 'on:TXT_ACTIVATED,off:TXT_DEACTIVATED', 'site')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for Home Page Url");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('coreListProtectedPages')
                && !\Cx\Core\Setting\Controller\Setting::add('coreListProtectedPages', isset($existingConfig['coreListProtectedPages']) ? $existingConfig['coreListProtectedPages'] : 'off', 6,
               \Cx\Core\Setting\Controller\Setting::TYPE_RADIO, 'on:TXT_ACTIVATED,off:TXT_DEACTIVATED', 'site')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for Protected Pages");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('searchVisibleContentOnly')
                && !\Cx\Core\Setting\Controller\Setting::add('searchVisibleContentOnly', isset($existingConfig['searchVisibleContentOnly']) ? $existingConfig['searchVisibleContentOnly'] : 'on', 7,
               \Cx\Core\Setting\Controller\Setting::TYPE_RADIO, 'on:TXT_ACTIVATED,off:TXT_DEACTIVATED', 'site')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for Visible Contents");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('advancedUploadFrontend')
                && !\Cx\Core\Setting\Controller\Setting::add('advancedUploadFrontend', isset($existingConfig['advancedUploadFrontend']) ? $existingConfig['advancedUploadFrontend'] : 'off', 8,
               \Cx\Core\Setting\Controller\Setting::TYPE_RADIO, 'on:TXT_ACTIVATED,off:TXT_DEACTIVATED', 'site')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for Visible Contents");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('forceProtocolFrontend')
                && !\Cx\Core\Setting\Controller\Setting::add('forceProtocolFrontend', isset($existingConfig['forceProtocolFrontend']) ? $existingConfig['forceProtocolFrontend'] : 'none', 9,
                \Cx\Core\Setting\Controller\Setting::TYPE_DROPDOWN, '{src:\\'.__CLASS__.'::getPortOptions()}', 'site')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for Protocol In Use");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('portFrontendHTTP')
                && !\Cx\Core\Setting\Controller\Setting::add('portFrontendHTTP', isset($existingConfig['portFrontendHTTP']) ? $existingConfig['portFrontendHTTP'] : 80, 10,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'site')){
                    \DBG::log("Failed to add Setting entry for core HTTP Port (Frontend)");
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for core HTTP Port (Frontend)");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('portFrontendHTTPS')
                && !\Cx\Core\Setting\Controller\Setting::add('portFrontendHTTPS', isset($existingConfig['portFrontendHTTPS']) ? $existingConfig['portFrontendHTTPS'] : 443, 11,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'site')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for core HTTPS Port (Frontend)");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('favicon')
                && !\Cx\Core\Setting\Controller\Setting::add('favicon', 'favicon.ico', 12,
                \Cx\Core\Setting\Controller\Setting::TYPE_IMAGE, '{"type":"copy"}', 'site')) {
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for favicon");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('defaultLocaleId')
                && !\Cx\Core\Setting\Controller\Setting::add('defaultLocaleId',  isset($existingConfig['defaultLocaleId']) ? $existingConfig['defaultLocaleId'] : 1, 13,
                    \Cx\Core\Setting\Controller\Setting::TYPE_DROPDOWN, '{src:\\'.__CLASS__.'::getLocales()}', 'site') ) {
                throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for default Locale (Frontend)");
            }

            //administrationArea group
            \Cx\Core\Setting\Controller\Setting::init('Config', 'administrationArea','Yaml', $configPath);
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('dashboardNews')
                && !\Cx\Core\Setting\Controller\Setting::add('dashboardNews', isset($existingConfig['dashboardNews']) ? $existingConfig['dashboardNews'] : 'off', 1,
               \Cx\Core\Setting\Controller\Setting::TYPE_RADIO, 'on:TXT_ACTIVATED,off:TXT_DEACTIVATED', 'administrationArea')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for Dashboard News");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('dashboardNewsSrc')
                && !\Cx\Core\Setting\Controller\Setting::add('dashboardNewsSrc', isset($existingConfig['dashboardNewsSrc']) ? $existingConfig['dashboardNewsSrc'] : 'http://www.cloudrexx.com/feed/news_headlines_de.xml', 1,
                    \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'component')){
                throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for dashboardNewsSrc");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('dashboardStatistics')
                && !\Cx\Core\Setting\Controller\Setting::add('dashboardStatistics', isset($existingConfig['dashboardStatistics']) ? $existingConfig['dashboardStatistics'] : 'on', 2,
               \Cx\Core\Setting\Controller\Setting::TYPE_RADIO, 'on:TXT_ACTIVATED,off:TXT_DEACTIVATED', 'administrationArea')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for Dashboard Statistics");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('advancedUploadBackend')
                && !\Cx\Core\Setting\Controller\Setting::add('advancedUploadBackend', isset($existingConfig['advancedUploadBackend']) ? $existingConfig['advancedUploadBackend'] : 'on', 3,
               \Cx\Core\Setting\Controller\Setting::TYPE_RADIO, 'on:TXT_ACTIVATED,off:TXT_DEACTIVATED', 'administrationArea')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for advanced Upload Tools");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('sessionLifeTime')
                && !\Cx\Core\Setting\Controller\Setting::add('sessionLifeTime', isset($existingConfig['sessionLifeTime']) ? $existingConfig['sessionLifeTime'] : '3600', 4,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'administrationArea')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for session Length");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('sessionLifeTimeRememberMe')
                && !\Cx\Core\Setting\Controller\Setting::add('sessionLifeTimeRememberMe', isset($existingConfig['sessionLifeTimeRememberMe']) ? $existingConfig['sessionLifeTimeRememberMe'] : '1209600', 5,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'administrationArea')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for session Length Remember");
            }

            if (in_array('SystemInfo', \Env::get('cx')->getLicense()->getLegalComponentsList())) {
                if (!\Cx\Core\Setting\Controller\Setting::isDefined('dnsServer')
                    && !\Cx\Core\Setting\Controller\Setting::add('dnsServer', isset($existingConfig['dnsServer']) ? $existingConfig['dnsServer'] : '8.8.8.8', 6,
                    \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'administrationArea')){
                        throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for Dns Server");
                }
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('timezone')
                && !\Cx\Core\Setting\Controller\Setting::add('timezone', isset($existingConfig['timezone']) ? $existingConfig['timezone'] : 'Europe/Zurich', 7,
                \Cx\Core\Setting\Controller\Setting::TYPE_DROPDOWN, '{src:\\'.__CLASS__.'::getTimezoneOptions()}', 'administrationArea')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for Time zone");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('forceProtocolBackend')
                && !\Cx\Core\Setting\Controller\Setting::add('forceProtocolBackend', isset($existingConfig['forceProtocolBackend']) ? $existingConfig['forceProtocolBackend'] : 'none', 8,
                \Cx\Core\Setting\Controller\Setting::TYPE_DROPDOWN, '{src:\\'.__CLASS__.'::getPortOptions()}', 'administrationArea')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for Protocol In Use Administrator");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('portBackendHTTP')
                && !\Cx\Core\Setting\Controller\Setting::add('portBackendHTTP', isset($existingConfig['portBackendHTTP']) ? $existingConfig['portBackendHTTP'] : 80, 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'administrationArea')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for core HTTP Port (Backend)");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('portBackendHTTPS')
                && !\Cx\Core\Setting\Controller\Setting::add('portBackendHTTPS', isset($existingConfig['portBackendHTTPS']) ? $existingConfig['portBackendHTTPS'] : 443, 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'administrationArea')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for core HTTPS Port (Backend)");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('defaultLanguageId')
                && !\Cx\Core\Setting\Controller\Setting::add('defaultLanguageId',  isset($existingConfig['defaultLanguageId']) ? $existingConfig['defaultLanguageId'] : 1, 9,
                    \Cx\Core\Setting\Controller\Setting::TYPE_DROPDOWN, '{src:\\'.__CLASS__.'::getBackendLanguages()}', 'administrationArea') ) {
                throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for default language (Backend)");
            }

            //security group
            \Cx\Core\Setting\Controller\Setting::init('Config', 'security','Yaml', $configPath);
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('coreIdsStatus')
                && !\Cx\Core\Setting\Controller\Setting::add('coreIdsStatus', isset($existingConfig['coreIdsStatus']) ? $existingConfig['coreIdsStatus'] : 'off', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_RADIO, 'on:TXT_ACTIVATED,off:TXT_DEACTIVATED', 'security')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for Security system notifications ");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('passwordComplexity')
                && !\Cx\Core\Setting\Controller\Setting::add('passwordComplexity', isset($existingConfig['passwordComplexity']) ? $existingConfig['passwordComplexity'] : 'off', 2,
                \Cx\Core\Setting\Controller\Setting::TYPE_RADIO, 'on:TXT_ACTIVATED,off:TXT_DEACTIVATED', 'security')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for Passwords must meet the complexity requirements");
            }
            if (
                !\Cx\Core\Setting\Controller\Setting::isDefined('captchaMethod') &&
                !\Cx\Core\Setting\Controller\Setting::add(
                    'captchaMethod',
                    (isset($existingConfig['captchaMethod'])
                        ? $existingConfig['captchaMethod']
                        : 'contrexxCaptcha'
                    ),
                    3,
                    \Cx\Core\Setting\Controller\Setting::TYPE_DROPDOWN,
                    '{src:\\'.__CLASS__.'::getCaptchaOptions()}',
                    'security'
                )
            ) {
                throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for Security check Captcha");
            }
            if (
                !\Cx\Core\Setting\Controller\Setting::isDefined('recaptchaSiteKey') &&
                !\Cx\Core\Setting\Controller\Setting::add(
                    'recaptchaSiteKey',
                    (isset($existingConfig['recaptchaSiteKey'])
                        ? $existingConfig['recaptchaSiteKey']
                        : ''
                    ),
                    4,
                    \Cx\Core\Setting\Controller\Setting::TYPE_TEXT,
                    null,
                    'security'
                )
            ) {
                throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for reCAPTCHA site key");
            }
            if (
                !\Cx\Core\Setting\Controller\Setting::isDefined('recaptchaSecretKey') &&
                !\Cx\Core\Setting\Controller\Setting::add(
                    'recaptchaSecretKey',
                    (isset($existingConfig['recaptchaSecretKey'])
                        ? $existingConfig['recaptchaSecretKey']
                        : ''
                    ),
                    5,
                    \Cx\Core\Setting\Controller\Setting::TYPE_TEXT,
                    null,
                    'security'
                )
            ) {
                throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for reCAPTCHA secret key");
            }

            //contactInformation group
            \Cx\Core\Setting\Controller\Setting::init('Config', 'contactInformation','Yaml', $configPath);
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('coreAdminName')
                && !\Cx\Core\Setting\Controller\Setting::add('coreAdminName', isset($existingConfig['coreAdminName']) ? $existingConfig['coreAdminName'] : 'Administrator', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'contactInformation')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for core Admin Name");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('coreAdminEmail')
                && !\Cx\Core\Setting\Controller\Setting::add('coreAdminEmail', isset($existingConfig['coreAdminEmail']) ? $existingConfig['coreAdminEmail'] : 'info@example.com', 2,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'contactInformation')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for core Admin Email");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('contactFormEmail')
                && !\Cx\Core\Setting\Controller\Setting::add('contactFormEmail', isset($existingConfig['contactFormEmail']) ? $existingConfig['contactFormEmail'] : 'info@example.com', 3,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'contactInformation')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for contact Form Email");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('contactCompany')
                && !\Cx\Core\Setting\Controller\Setting::add('contactCompany', isset($existingConfig['contactCompany']) ? $existingConfig['contactCompany'] : 'Ihr Firmenname', 4,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'contactInformation')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for contact Company");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('contactAddress')
                && !\Cx\Core\Setting\Controller\Setting::add('contactAddress', isset($existingConfig['contactAddress']) ? $existingConfig['contactAddress'] : 'Musterstrasse 12', 5,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'contactInformation')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for contact Address");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('contactZip')
                && !\Cx\Core\Setting\Controller\Setting::add('contactZip', isset($existingConfig['contactZip']) ? $existingConfig['contactZip'] : '3600', 6,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'contactInformation')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for contact Zip");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('contactPlace')
                && !\Cx\Core\Setting\Controller\Setting::add('contactPlace', isset($existingConfig['contactPlace']) ? $existingConfig['contactPlace'] : 'Musterhausen', 7,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'contactInformation')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for contact Place");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('contactCountry')
                && !\Cx\Core\Setting\Controller\Setting::add('contactCountry', isset($existingConfig['contactCountry']) ? $existingConfig['contactCountry'] : 'Musterland', 8,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'contactInformation')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for contact Country");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('contactPhone')
                && !\Cx\Core\Setting\Controller\Setting::add('contactPhone', isset($existingConfig['contactPhone']) ? $existingConfig['contactPhone'] : '033 123 45 67', 9,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'contactInformation')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for contact Phone");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('contactFax')
                && !\Cx\Core\Setting\Controller\Setting::add('contactFax', isset($existingConfig['contactFax']) ? $existingConfig['contactFax'] : '033 123 45 68', 10,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'contactInformation')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for contact Fax");
            }

            //otherConfigurations group
            \Cx\Core\Setting\Controller\Setting::init('Config', 'otherConfigurations','Yaml', $configPath);
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('xmlSitemapStatus')
                && !\Cx\Core\Setting\Controller\Setting::add('xmlSitemapStatus', isset($existingConfig['xmlSitemapStatus']) ? $existingConfig['xmlSitemapStatus'] : 'on', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_RADIO, 'on:TXT_ACTIVATED,off:TXT_DEACTIVATED', 'otherConfigurations')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for XML Sitemap");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('frontendEditingStatus')
                && !\Cx\Core\Setting\Controller\Setting::add('frontendEditingStatus', isset($existingConfig['frontendEditingStatus']) ? $existingConfig['frontendEditingStatus'] : 'on', 2,
                \Cx\Core\Setting\Controller\Setting::TYPE_RADIO, 'on:TXT_ACTIVATED,off:TXT_DEACTIVATED', 'otherConfigurations')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for Frontend Editing");
            }
            if (in_array('SystemInfo', \Env::get('cx')->getLicense()->getLegalComponentsList())) {
                if (!\Cx\Core\Setting\Controller\Setting::isDefined('useCustomizings')
                    && !\Cx\Core\Setting\Controller\Setting::add('useCustomizings', isset($existingConfig['useCustomizings']) ? $existingConfig['useCustomizings'] : 'off', 3,
                    \Cx\Core\Setting\Controller\Setting::TYPE_RADIO, 'on:TXT_ACTIVATED,off:TXT_DEACTIVATED', 'otherConfigurations')){
                        throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for Customizing");
                }
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('corePagingLimit')
                && !\Cx\Core\Setting\Controller\Setting::add('corePagingLimit', isset($existingConfig['corePagingLimit']) ? $existingConfig['corePagingLimit'] : '30', 4,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'otherConfigurations')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for Records per page");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('searchDescriptionLength')
                && !\Cx\Core\Setting\Controller\Setting::add('searchDescriptionLength', isset($existingConfig['searchDescriptionLength']) ? $existingConfig['searchDescriptionLength'] : '150', 5,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'otherConfigurations')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for Number of Characters in Search Results");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('googleMapsAPIKey')
                && !\Cx\Core\Setting\Controller\Setting::add('googleMapsAPIKey', isset($existingConfig['googleMapsAPIKey']) ? $existingConfig['googleMapsAPIKey'] : '', 6,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'otherConfigurations')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for Google-Map API key ");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('googleAnalyticsTrackingId')
                && !\Cx\Core\Setting\Controller\Setting::add('googleAnalyticsTrackingId', isset($existingConfig['googleAnalyticsTrackingId']) ? $existingConfig['googleAnalyticsTrackingId'] : '', 7,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'otherConfigurations')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for Google Analytics Tracking ID");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('defaultMetaimage')
                && !\Cx\Core\Setting\Controller\Setting::add('defaultMetaimage', isset($existingConfig['defaultMetaimage']) ? $existingConfig['defaultMetaimage'] : '/themes/standard_4_0/images/og_logo_social_media.jpg', 8,
                    \Cx\Core\Setting\Controller\Setting::TYPE_IMAGE, '{"type":"reference"}', 'otherConfigurations')) {
                throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for default meta image");
            }

            // core
            \Cx\Core\Setting\Controller\Setting::init('Config', 'core','Yaml', $configPath);
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('coreSmtpServer')
                && !\Cx\Core\Setting\Controller\Setting::add('coreSmtpServer', isset($existingConfig['coreSmtpServer']) ? $existingConfig['coreSmtpServer'] : '0', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'core')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for coreSmtpServer");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('lastAccessId')
                && !\Cx\Core\Setting\Controller\Setting::add('lastAccessId', isset($existingConfig['lastAccessId']) ? $existingConfig['lastAccessId'] : '1', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, '', 'core')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for lastAccessId");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('installationId')
                && !\Cx\Core\Setting\Controller\Setting::add('installationId', isset($existingConfig['installationId']) ? $existingConfig['installationId'] : '', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'core')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for installationId");
            }

            // component
            \Cx\Core\Setting\Controller\Setting::init('Config', 'component','Yaml', $configPath);
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('bannerStatus')
                && !\Cx\Core\Setting\Controller\Setting::add('bannerStatus', isset($existingConfig['bannerStatus']) ? $existingConfig['bannerStatus'] : '0', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_RADIO, '1:TXT_ACTIVATED,0:TXT_DEACTIVATED', 'component')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for bannerStatus");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('spamKeywords')
                && !\Cx\Core\Setting\Controller\Setting::add('spamKeywords', isset($existingConfig['spamKeywords']) ? $existingConfig['spamKeywords'] : 'sex, viagra', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXTAREA, '', 'component')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for spamKeywords");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('newsTeasersStatus')
                && !\Cx\Core\Setting\Controller\Setting::add('newsTeasersStatus', isset($existingConfig['newsTeasersStatus']) ? $existingConfig['newsTeasersStatus'] : '0', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_RADIO, '1:TXT_ACTIVATED,0:TXT_DEACTIVATED', 'component')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for newsTeasersStatus");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('feedNewsMLStatus')
                && !\Cx\Core\Setting\Controller\Setting::add('feedNewsMLStatus', isset($existingConfig['feedNewsMLStatus']) ? $existingConfig['feedNewsMLStatus'] : '0', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_RADIO, '1:TXT_ACTIVATED,0:TXT_DEACTIVATED', 'component')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for feedNewsMLStatus");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('calendarheadlines')
                && !\Cx\Core\Setting\Controller\Setting::add('calendarheadlines', isset($existingConfig['calendarheadlines']) ? $existingConfig['calendarheadlines'] : '0', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_RADIO, '1:TXT_ACTIVATED,0:TXT_DEACTIVATED', 'component')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for calendarheadlines");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('calendarheadlinescount')
                && !\Cx\Core\Setting\Controller\Setting::add('calendarheadlinescount', isset($existingConfig['calendarheadlinescount']) ? $existingConfig['calendarheadlinescount'] : '5', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, '', 'component')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for calendarheadlinescount");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('calendardefaultcount')
                && !\Cx\Core\Setting\Controller\Setting::add('calendardefaultcount', isset($existingConfig['calendardefaultcount']) ? $existingConfig['calendardefaultcount'] : '16', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, '', 'component')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for calendardefaultcount");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('calendarheadlinescat')
                && !\Cx\Core\Setting\Controller\Setting::add('calendarheadlinescat', isset($existingConfig['calendarheadlinescat']) ? $existingConfig['calendarheadlinescat'] : '0', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, '', 'component')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for calendarheadlinescat");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('blockStatus')
                && !\Cx\Core\Setting\Controller\Setting::add('blockStatus', isset($existingConfig['blockStatus']) ? $existingConfig['blockStatus'] : '1', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_RADIO, '1:TXT_ACTIVATED,0:TXT_DEACTIVATED', 'component')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for blockStatus");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('blockRandom')
                && !\Cx\Core\Setting\Controller\Setting::add('blockRandom', isset($existingConfig['blockRandom']) ? $existingConfig['blockRandom'] : '1', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_RADIO, '1:TXT_ACTIVATED,0:TXT_DEACTIVATED', 'component')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for blockRandom");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('directoryHomeContent')
                && !\Cx\Core\Setting\Controller\Setting::add('directoryHomeContent', isset($existingConfig['directoryHomeContent']) ? $existingConfig['directoryHomeContent'] : '0', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_RADIO, '1:TXT_ACTIVATED,0:TXT_DEACTIVATED', 'component')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for directoryHomeContent");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('forumHomeContent')
                && !\Cx\Core\Setting\Controller\Setting::add('forumHomeContent', isset($existingConfig['forumHomeContent']) ? $existingConfig['forumHomeContent'] : '0', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_RADIO, '1:TXT_ACTIVATED,0:TXT_DEACTIVATED', 'component')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for forumHomeContent");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('podcastHomeContent')
                && !\Cx\Core\Setting\Controller\Setting::add('podcastHomeContent', isset($existingConfig['podcastHomeContent']) ? $existingConfig['podcastHomeContent'] : '0', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_RADIO, '1:TXT_ACTIVATED,0:TXT_DEACTIVATED', 'component')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for podcastHomeContent");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('forumTagContent')
                && !\Cx\Core\Setting\Controller\Setting::add('forumTagContent', isset($existingConfig['forumTagContent']) ? $existingConfig['forumTagContent'] : '0', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_RADIO, '1:TXT_ACTIVATED,0:TXT_DEACTIVATED', 'component')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for forumTagContent");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('dataUseModule')
                && !\Cx\Core\Setting\Controller\Setting::add('dataUseModule', isset($existingConfig['dataUseModule']) ? $existingConfig['dataUseModule'] : '0', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_RADIO, '1:TXT_ACTIVATED,0:TXT_DEACTIVATED', 'component')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for dataUseModule");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('useKnowledgePlaceholders')
                && !\Cx\Core\Setting\Controller\Setting::add('useKnowledgePlaceholders', isset($existingConfig['useKnowledgePlaceholders']) ? $existingConfig['useKnowledgePlaceholders'] : '0', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_RADIO, '1:TXT_ACTIVATED,0:TXT_DEACTIVATED', 'component')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for useKnowledgePlaceholders");
            }


            // release
            \Cx\Core\Setting\Controller\Setting::init('Config', 'release','Yaml', $configPath);
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('coreCmsEdition')
                && !\Cx\Core\Setting\Controller\Setting::add('coreCmsEdition', isset($existingConfig['coreCmsEdition']) ? $existingConfig['coreCmsEdition'] : 'Open Source', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'release')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for coreCmsEdition");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('coreCmsVersion')
                && !\Cx\Core\Setting\Controller\Setting::add('coreCmsVersion', isset($existingConfig['coreCmsVersion']) ? $existingConfig['coreCmsVersion'] : '5.0.0', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'release')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for coreCmsVersion");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('coreCmsCodeName')
                && !\Cx\Core\Setting\Controller\Setting::add('coreCmsCodeName', isset($existingConfig['coreCmsCodeName']) ? $existingConfig['coreCmsCodeName'] : 'Nandri', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'release')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for coreCmsCodeName");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('coreCmsStatus')
                && !\Cx\Core\Setting\Controller\Setting::add('coreCmsStatus', isset($existingConfig['coreCmsStatus']) ? $existingConfig['coreCmsStatus'] : 'Stable', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'release')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for coreCmsStatus");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('coreCmsReleaseDate')
                && !\Cx\Core\Setting\Controller\Setting::add('coreCmsReleaseDate', isset($existingConfig['coreCmsReleaseDate']) ? $existingConfig['coreCmsReleaseDate'] : '1348783200', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_DATE, null, 'release')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for coreCmsReleaseDate");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('coreCmsName')
                && !\Cx\Core\Setting\Controller\Setting::add('coreCmsName', isset($existingConfig['coreCmsName']) ? $existingConfig['coreCmsName'] : 'Cloudrexx', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'release')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for coreCmsName");
            }

            // license
            \Cx\Core\Setting\Controller\Setting::init('Config', 'license','Yaml', $configPath);
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('licenseKey')
                && !\Cx\Core\Setting\Controller\Setting::add('licenseKey', isset($existingConfig['licenseKey']) ? $existingConfig['licenseKey'] : '', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'license')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for licenseKey");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('licenseState')
                && !\Cx\Core\Setting\Controller\Setting::add('licenseState', isset($existingConfig['licenseState']) ? $existingConfig['licenseState'] : 'OK', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'license')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for licenseState");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('licenseValidTo')
                && !\Cx\Core\Setting\Controller\Setting::add('licenseValidTo', isset($existingConfig['licenseValidTo']) ? $existingConfig['licenseValidTo'] : '0', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_DATETIME, null, 'license')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for licenseValidTo");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('licenseMessage')
                && !\Cx\Core\Setting\Controller\Setting::add('licenseMessage', isset($existingConfig['licenseMessage']) ? $existingConfig['licenseMessage'] : '', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'license')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for licenseMessage");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('licensePartner')
                && !\Cx\Core\Setting\Controller\Setting::add('licensePartner', isset($existingConfig['licensePartner']) ? $existingConfig['licensePartner'] : '', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'license')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for licensePartner");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('licenseCustomer')
                && !\Cx\Core\Setting\Controller\Setting::add('licenseCustomer', isset($existingConfig['licenseCustomer']) ? $existingConfig['licenseCustomer'] : '', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'license')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for licenseCustomer");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('upgradeUrl')
                && !\Cx\Core\Setting\Controller\Setting::add('upgradeUrl', isset($existingConfig['upgradeUrl']) ? $existingConfig['upgradeUrl'] : 'http://license.contrexx.com/', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'license')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for upgradeUrl");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('licenseGrayzoneMessages')
                && !\Cx\Core\Setting\Controller\Setting::add('licenseGrayzoneMessages', isset($existingConfig['licenseGrayzoneMessages']) ? $existingConfig['licenseGrayzoneMessages'] : '', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'license')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for licenseGrayzoneMessages");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('licenseGrayzoneTime')
                && !\Cx\Core\Setting\Controller\Setting::add('licenseGrayzoneTime', isset($existingConfig['licenseGrayzoneTime']) ? $existingConfig['licenseGrayzoneTime'] : '14', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'license')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for licenseGrayzoneTime");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('licenseLockTime')
                && !\Cx\Core\Setting\Controller\Setting::add('licenseLockTime', isset($existingConfig['licenseLockTime']) ? $existingConfig['licenseLockTime'] : '', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'license')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for licenseLockTime");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('licenseUpdateInterval')
                && !\Cx\Core\Setting\Controller\Setting::add('licenseUpdateInterval', isset($existingConfig['licenseUpdateInterval']) ? $existingConfig['licenseUpdateInterval'] : '24', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'license')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for licenseUpdateInterval");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('licenseFailedUpdate')
                && !\Cx\Core\Setting\Controller\Setting::add('licenseFailedUpdate', isset($existingConfig['licenseFailedUpdate']) ? $existingConfig['licenseFailedUpdate'] : '0', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'license')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for licenseFailedUpdate");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('licenseSuccessfulUpdate')
                && !\Cx\Core\Setting\Controller\Setting::add('licenseSuccessfulUpdate', isset($existingConfig['licenseSuccessfulUpdate']) ? $existingConfig['licenseSuccessfulUpdate'] : '0', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'license')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for licenseSuccessfulUpdate");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('licenseCreatedAt')
                && !\Cx\Core\Setting\Controller\Setting::add('licenseCreatedAt', isset($existingConfig['licenseCreatedAt']) ? $existingConfig['licenseCreatedAt'] : '', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_DATE, null, 'license')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for licenseCreatedAt");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('licenseDomains')
                && !\Cx\Core\Setting\Controller\Setting::add('licenseDomains', isset($existingConfig['licenseDomains']) ? $existingConfig['licenseDomains'] : '', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'license')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for licenseDomains");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('availableComponents')
                && !\Cx\Core\Setting\Controller\Setting::add('availableComponents', isset($existingConfig['availableComponents']) ? $existingConfig['availableComponents'] : '', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'license')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for availableComponents");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('dashboardMessages')
                && !\Cx\Core\Setting\Controller\Setting::add('dashboardMessages', isset($existingConfig['dashboardMessages']) ? $existingConfig['dashboardMessages'] : '', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'license')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for dashboardMessages");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('isUpgradable')
                && !\Cx\Core\Setting\Controller\Setting::add('isUpgradable', isset($existingConfig['isUpgradable']) ? $existingConfig['isUpgradable'] : 'on', 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_RADIO, 'on:TXT_ACTIVATED,off:TXT_DEACTIVATED', 'license')){
                    throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for isUpgradable");
            }

            // cache
            if (in_array('SystemInfo', \Env::get('cx')->getLicense()->getLegalComponentsList())) {
                \Cx\Core\Setting\Controller\Setting::init('Config', 'cache','Yaml', $configPath);
                if (!\Cx\Core\Setting\Controller\Setting::isDefined('cacheEnabled')
                    && !\Cx\Core\Setting\Controller\Setting::add('cacheEnabled', isset($existingConfig['cacheEnabled']) ? $existingConfig['cacheEnabled'] : 'off', 1,
                    \Cx\Core\Setting\Controller\Setting::TYPE_RADIO, 'on:TXT_ACTIVATED,off:TXT_DEACTIVATED', 'cache')){
                        throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for cacheEnabled");
                }
                if (!\Cx\Core\Setting\Controller\Setting::isDefined('cacheExpiration')
                    && !\Cx\Core\Setting\Controller\Setting::add('cacheExpiration', isset($existingConfig['cacheExpiration']) ? $existingConfig['cacheExpiration'] : '86400', 1,
                    \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'cache')){
                        throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for cacheExpiration");
                }
                if (!\Cx\Core\Setting\Controller\Setting::isDefined('cacheOpStatus')
                    && !\Cx\Core\Setting\Controller\Setting::add('cacheOpStatus', isset($existingConfig['cacheOpStatus']) ? $existingConfig['cacheOpStatus'] : 'off', 1,
                    \Cx\Core\Setting\Controller\Setting::TYPE_RADIO, 'on:TXT_ACTIVATED,off:TXT_DEACTIVATED', 'cache')){
                        throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for cacheOpStatus");
                }
                if (!\Cx\Core\Setting\Controller\Setting::isDefined('cacheDbStatus')
                    && !\Cx\Core\Setting\Controller\Setting::add('cacheDbStatus', isset($existingConfig['cacheDbStatus']) ? $existingConfig['cacheDbStatus'] : 'off', 1,
                    \Cx\Core\Setting\Controller\Setting::TYPE_RADIO, 'on:TXT_ACTIVATED,off:TXT_DEACTIVATED', 'cache')){
                        throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for cacheDbStatus");
                }
                if (!\Cx\Core\Setting\Controller\Setting::isDefined('cacheReverseProxy')
                    && !\Cx\Core\Setting\Controller\Setting::add('cacheReverseProxy', isset($existingConfig['cacheReverseProxy']) ? $existingConfig['cacheReverseProxy'] : 'none', 1,
                    \Cx\Core\Setting\Controller\Setting::TYPE_RADIO, '{src:\\' . __CLASS__ . '::getReverseProxyTypes()}', 'cache')){
                        throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for cacheReverseProxy");
                }
                if (!\Cx\Core\Setting\Controller\Setting::isDefined('cacheSsiOutput')
                    && !\Cx\Core\Setting\Controller\Setting::add('cacheSsiOutput', isset($existingConfig['cacheSsiOutput']) ? $existingConfig['cacheSsiOutput'] : 'intern', 1,
                    \Cx\Core\Setting\Controller\Setting::TYPE_DROPDOWN, '{src:\\'.__CLASS__.'::getSsiOutputModes()}', 'cache')){
                        throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for cacheSsiOutput");
                }
                if (!\Cx\Core\Setting\Controller\Setting::isDefined('cacheSsiType')
                    && !\Cx\Core\Setting\Controller\Setting::add('cacheSsiType', isset($existingConfig['cacheSsiType']) ? $existingConfig['cacheSsiType'] : 'varnish', 1,
                    \Cx\Core\Setting\Controller\Setting::TYPE_DROPDOWN, '{src:\\'.__CLASS__.'::getSsiTypes()}', 'cache')){
                        throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for cacheSsiType");
                }
                if (!\Cx\Core\Setting\Controller\Setting::isDefined('cacheUserCache')
                    && !\Cx\Core\Setting\Controller\Setting::add('cacheUserCache', isset($existingConfig['cacheUserCache']) ? $existingConfig['cacheUserCache'] : 'off', 1,
                    \Cx\Core\Setting\Controller\Setting::TYPE_RADIO, 'on:TXT_ACTIVATED,off:TXT_DEACTIVATED', 'cache')){
                        throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for cacheUserCache");
                }
                if (!\Cx\Core\Setting\Controller\Setting::isDefined('cacheOPCache')
                    && !\Cx\Core\Setting\Controller\Setting::add('cacheOPCache', isset($existingConfig['cacheOPCache']) ? $existingConfig['cacheOPCache'] : 'off', 1,
                    \Cx\Core\Setting\Controller\Setting::TYPE_RADIO, 'on:TXT_ACTIVATED,off:TXT_DEACTIVATED', 'cache')){
                        throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for cacheOPCache");
                }
                if (!\Cx\Core\Setting\Controller\Setting::isDefined('cacheProxyCacheConfig')
                    && !\Cx\Core\Setting\Controller\Setting::add('cacheProxyCacheConfig', isset($existingConfig['cacheProxyCacheConfig']) ? $existingConfig['cacheProxyCacheConfig'] : '{"ip":"127.0.0.1","port":"8080"}', 1,
                    \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'cache')){
                        throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for cacheProxyCacheConfig");
                }
                if (!\Cx\Core\Setting\Controller\Setting::isDefined('cacheSsiProcessorConfig')
                    && !\Cx\Core\Setting\Controller\Setting::add('cacheSsiProcessorConfig', isset($existingConfig['cacheSsiProcessorConfig']) ? $existingConfig['cacheSsiProcessorConfig'] : '{"ip":"127.0.0.1","port":"8080"}', 1,
                    \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'cache')){
                        throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for cacheSsiProcessorConfig");
                }
                if (!\Cx\Core\Setting\Controller\Setting::isDefined('internalSsiCache')
                    && !\Cx\Core\Setting\Controller\Setting::add('internalSsiCache', isset($existingConfig['internalSsiCache']) ? $existingConfig['internalSsiCache'] : 'off', 1,
                    \Cx\Core\Setting\Controller\Setting::TYPE_RADIO, 'on:TXT_ACTIVATED,off:TXT_DEACTIVATED', 'cache')){
                        throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for internalSsiCache");
                }
                if (!\Cx\Core\Setting\Controller\Setting::isDefined('cacheUserCacheMemcacheConfig')
                    && !\Cx\Core\Setting\Controller\Setting::add('cacheUserCacheMemcacheConfig', isset($existingConfig['cacheUserCacheMemcacheConfig']) ? $existingConfig['cacheUserCacheMemcacheConfig'] : '{"ip":"127.0.0.1","port":11211}', 1,
                    \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'cache')){
                        throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for cacheUserCacheMemcacheConfig");
                }
                // The following is temporary until the LanguageManager replacement (component 'Locale') is here:
                if (!\Cx\Core\Setting\Controller\Setting::isDefined('useVirtualLanguageDirectories')
                    && !\Cx\Core\Setting\Controller\Setting::add('useVirtualLanguageDirectories', isset($existingConfig['useVirtualLanguageDirectories']) ? $existingConfig['useVirtualLanguageDirectories'] : 'on', 1,
                    \Cx\Core\Setting\Controller\Setting::TYPE_RADIO, 'on:TXT_ACTIVATED,off:TXT_DEACTIVATED', 'lang')){
                        throw new \Cx\Lib\Update_DatabaseException("Failed to add Setting entry for useVirtualLanguageDirectories");
                }
            }
        } catch (\Exception $e) {
            \DBG::msg($e->getMessage());
        }
        // Always
        return false;
    }
    /**
     * Shows the all domains page
     *
     * @access  private
     * @return  string
     */
    public static function getDomains() {
        $objMainDomain = new \Cx\Core\Net\Model\Repository\DomainRepository();
        $domains = $objMainDomain->findAll();
        $display = array();
        foreach ($domains As $domain) {
            $display[] = $domain->getId() . ':' . $domain->getNameWithPunycode();
        }
        return implode(',', $display);
    }

    /**
     * Shows all backend languages
     *
     * @access  public
     * @return  string
     */
    public static function getBackendLanguages() {
        $cx = Cx::instanciate();
        $em = $cx->getDb()->getEntityManager();
        $backendLangRepo = $em->getRepository('Cx\Core\Locale\Model\Entity\Backend');
        $languages = $backendLangRepo->findAll();
        $display = array();
        foreach ($languages As $language) {
            $display[] = $language->getId() . ':' . $language->getIso1();
        }
        return implode(',', $display);
    }

    /**
     * Shows all locales
     *
     * @access  public
     * @return  string
     */
    public static function getLocales() {
        $cx = Cx::instanciate();
        $em = $cx->getDb()->getEntityManager();
        $localeRepository = $em->getRepository('Cx\Core\Locale\Model\Entity\Locale');
        $locales = $localeRepository->findAll();
        $display = array();
        foreach ($locales As $locale) {
            $display[] = $locale->getId() . ':' . $locale->getLabel();
        }
        return implode(',', $display);
    }

    /**
     * Gets the list of reverse proxy types
     * @return string Comma separated list of reverse proxy types
     */
    public static function getReverseProxyTypes() {
        $reverseProxyTypes = array(
            'none',
            'varnish',
            'nginx',
        );
        $reverseProxyTypeTexts = array();
        foreach ($reverseProxyTypes as $reverseProxyType) {
            $reverseProxyTypeTexts[$reverseProxyType] = 'SETTINGS_REVERSE_PROXY_CACHE_STATUS_' . strtoupper($reverseProxyType);
        }
        return implode(',', $reverseProxyTypeTexts);
    }

    /**
     * Gets the list of ESI/SSI output modes
     * @return string Comma separated list of ESI/SSI output modes
     */
    public static function getSsiOutputModes() {
        $ssiModes = array(
            'intern',
            'ssi',
            'esi',
        );
        $ssiModeTexts = array();
        foreach ($ssiModes as $ssiMode) {
            $ssiModeTexts[$ssiMode] = 'SETTINGS_SSI_CACHE_STATUS_' . strtoupper($ssiMode);
        }
        return implode(',', $ssiModeTexts);
    }

    /**
     * Gets the list of supported system types for external ESI/SSI processing
     * 
     * This is important in order to drop invalid cache objects!
     * @return string Comma separated list of supported system types for external ESI/SSI processing
     */
    public static function getSsiTypes() {
        $ssiTypes = array(
            'varnish',
            'nginx',
        );
        $ssiTypeTexts = array();
        foreach ($ssiTypes as $ssiType) {
            $ssiTypeTexts[$ssiType] = 'SETTINGS_SSI_CACHE_TYPE_' . strtoupper($ssiType);
        }
        return implode(',', $ssiTypeTexts);
    }

    public function showFtp() {
        global $_ARRAYLANG, $objTemplate, $_CONFIG;

        $this->strPageTitle = $_ARRAYLANG['TXT_SETTINGS_FTP'];
        $objTemplate->addBlockfile('ADMIN_CONTENT', 'settings_ftp', 'settings_ftp.html');

        //get the ftp server name
        $domainRepo  = \Env::get('em')->getRepository('Cx\Core\Net\Model\Entity\Domain');
        $objDomain   = $domainRepo->findOneBy(array('id' => 0));
        //get the ftp user name
        \Cx\Core\Setting\Controller\Setting::init('MultiSite', 'website','FileSystem');
        $ftpUserName = \Cx\Core\Setting\Controller\Setting::getValue('websiteFtpUser','MultiSite');

        if (empty($ftpUserName)) {
            throw new \Exception('FTP Failed to load: Website Ftp User is empty');
        }

        $objTemplate->setVariable(array(
            'FTP_SERVER_NAME'   => 'ftp://' . $objDomain->getName(),
            'FTP_USER_NAME'     => $ftpUserName,
        ));

        $objTemplate->setVariable(array(
            'TXT_SETTINGS_FTP'            => $_ARRAYLANG['TXT_SETTINGS_FTP'],
            'TXT_SETTINGS_FTP_SERVER'     => $_ARRAYLANG['TXT_SETTINGS_FTP_SERVER'],
            'TXT_SETTINGS_FTP_USER'       => $_ARRAYLANG['TXT_SETTINGS_FTP_USER'],
            'TXT_SETTINGS_FTP_PASSWORD'   => $_ARRAYLANG['TXT_SETTINGS_FTP_PASSWORD'],
            'TXT_SETTINGS_RESET_PASSWORD' => $_ARRAYLANG['TXT_SETTINGS_RESET_PASSWORD'],
        ));
    }

    /**
     * get the settings file path
     *
     * @return  string
     */
    static function getSettingsFile() {
        return \Env::get('cx')->getWebsiteConfigPath() . '/settings.php';
    }

    /**
     * Regenerate the thumbnails
     *
     * @param array $post $_POST values
     */
    protected  function generateThumbnail($post)
    {
        // release the locks, session not needed
        $cx = Cx::instanciate();

        $session = $cx->getComponent('Session')->getSession();
        $session->releaseLocks();
        session_write_close();

        $key = $_GET['key'];
        if (!preg_match("/[A-Z0-9]{5}/i", $key)){
            die;
        }

        $processFile = $session->getTempPath() .'/progress' . $key . '.txt';
        if (\Cx\Lib\FileSystem\FileSystem::exists($processFile)) {
            die;
        }

        try {
            $objProcessFile = new \Cx\Lib\FileSystem\File($processFile);
            $objProcessFile->touch();
        } catch (\Cx\Lib\FileSystem\FileSystemException $ex) {
            die;
        }

        $recursiveIteratorIterator
            = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($cx->getWebsiteImagesPath().'/'), \RecursiveIteratorIterator::SELF_FIRST);
        $jsonFileArray = array();

        $thumbnailList = Cx::instanciate()->getMediaSourceManager()
            ->getThumbnailGenerator()
            ->getThumbnails();

        $imageManager = new \ImageManager();

        $fileCounter = 0;
        $generalSuccess = true;


        $imageFiles = array();

        foreach ($recursiveIteratorIterator as $file) {
            /**
             * @var $file \SplFileInfo
             */
            $extension = 'Dir';
            if (!$file->isDir()) {
                $extension = ucfirst(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
            }
            $filePathinfo  = pathinfo($file->getRealPath());
            $fileNamePlain = $filePathinfo['filename'];
            // set preview if image
            $preview = 'none';

            $fileInfos = array(
                'filepath'  => mb_strcut($file->getPath() . '/' . $file->getFilename(), mb_strlen($cx->getCodeBasePath())),
                // preselect in mediabrowser or mark a folder
                'name'      => $file->getFilename(),
                'cleansize' => $file->getSize(),
                'extension' => ucfirst(mb_strtolower($extension)),
                'type'      => $file->getType()
            );


            // filters
            if (
                $fileInfos['name'] == '.' || preg_match('/\.thumb/', $fileInfos['name'])
                || $fileInfos['name'] == 'index.php'
                || (0 === strpos($fileInfos['name'], '.'))
            ) {
                continue;
            }


            if (!preg_match("/(jpg|jpeg|gif|png)/i", ucfirst($extension))) {
                continue;
            }

            $imageFiles[] = $file;

        }

        $imageFilesCount = count($imageFiles);

        if ($imageFilesCount == 0) {
            $objProcessFile->write(100);
            die;
        }


        foreach ($imageFiles as $file) {

            /**
             * @var $file \SplFileInfo
             */
            $extension = 'Dir';
            if (!$file->isDir()) {
                $extension = ucfirst(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
            }
            $filePathinfo  = pathinfo($file->getRealPath());
            $fileNamePlain = $filePathinfo['filename'];

            $fileInfos = array(
                'filepath'  => mb_strcut($file->getPath() . '/' . $file->getFilename(), mb_strlen(ASCMS_PATH)),
                // preselect in mediabrowser or mark a folder
                'name'      => $file->getFilename(),
                'cleansize' => $file->getSize(),
                'extension' => ucfirst(mb_strtolower($extension)),
                'type'      => $file->getType()
            );

            $filePathinfo  = pathinfo($file->getRealPath());
            $fileExtension = isset($filePathinfo['extension']) ? $filePathinfo['extension'] : '';

            $preview = $cx->getCodeBaseOffsetPath() . str_replace($cx->getCodeBaseDocumentRootPath(), '', $file->getRealPath());

            $previewList = array();
            foreach ($thumbnailList as $thumbnail) {
                $previewList[] = str_replace(
                    '.' . lcfirst($extension), $thumbnail['value'] . '.' . lcfirst($extension), $preview
                );
            }

            $allThumbnailsExists = true;
            foreach ($previewList as $previewImage) {
                if (!FileSystem::exists($previewImage)) {
                    $allThumbnailsExists = false;
                }
            }

            if (!$allThumbnailsExists) {
                if ($imageManager->_isImage($file->getRealPath())) {
                    $cx->getMediaSourceManager()
                        ->getThumbnailGenerator()
                        ->createThumbnail(
                        $file->getPath(), $fileNamePlain, $fileExtension, $imageManager, true
                        );
                }
            }

            $fileCounter++;
            $objProcessFile->write($fileCounter / $imageFilesCount * 100);
        }

        $objProcessFile->write(100);
        die;
    }

    /**
     * Get the thumbnail generation progress from the temp file
     */
    function getThumbProgress()
    {
        // release the locks, session not needed
        $cx = Cx::instanciate();

        $session = $cx->getComponent('Session')->getSession();
        $session->releaseLocks();
        session_write_close();

        $key         = isset($_GET['key']) ?  $_GET['key'] : '';
        $processFile = $session->getTempPath() .'/progress' . $key . '.txt';

        $process = 0;
        if (file_exists($processFile)) {
            $process = file_get_contents($processFile);
            if ($process == 100) {
                \Cx\Lib\FileSystem\FileSystem::delete_file($processFile);
            }
        }

        echo $process;
        die;
    }
}
