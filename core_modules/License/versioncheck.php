<?php
/*ini_set('display_errors', 1);
error_reporting(E_ALL);
echo '<pre>';//*/

global $_CONFIG, $_FTPCONFIG, $_DBCONFIG, $sessionObj, $objInit, $objDatabase, $documentRoot;

// when included in installer, this is set
if (!isset($documentRoot)) {
    $documentRoot = dirname(dirname(dirname(__FILE__)));
}

// load requirements
if (!class_exists("DBG")) {
    require_once($documentRoot.'/lib/FRAMEWORK/DBG/DBG.php');
}
//DBG::activate(DBG_ERROR_FIREPHP);
require_once($documentRoot.'/config/settings.php');              // needed for configuration.php
require_once($documentRoot.'/config/configuration.php');         // needed for API
require_once($documentRoot.'/core/validator.inc.php');

$customizing = null;
if (isset($_CONFIG['useCustomizings']) && $_CONFIG['useCustomizings'] == 'on') {
// TODO: webinstaller check: has ASCMS_CUSTOMIZING_PATH already been defined in the installation process?
    $customizing = ASCMS_CUSTOMIZING_PATH;
}

require_once($documentRoot.'/core/ClassLoader/ClassLoader.class.php');
$cl = new \Cx\Core\ClassLoader\ClassLoader($documentRoot, true, $customizing);

Env::set('ClassLoader', $cl);
Env::set('config', $_CONFIG);
Env::set('ftpConfig', $_FTPCONFIG);

// core/API.php is not available in tmp/legacyClassClache.tmp, therefore we have to load it manually
// if the class_exists check is not here, the update will fail because the api.php file loads the validator script
if (!class_exists('HTML_Template_Sigma', false)) {
    $cl->loadFile($documentRoot.'/core/API.php'); // needed for getDatabaseObject()
}

$db = new \Cx\Core\Db\Db();
$objDatabase = $db->getAdoDb();
\Env::set('db', $objDatabase);
$objInit = new InitCMS('backend', null);

$objInit->_initBackendLanguage();
$_LANGID = $objInit->getBackendLangId();
define('LANG_ID', $_LANGID);

// load interface texts, might be used by the license system in case of communication errors
$_CORELANG = $objInit->loadLanguageData('core');

// Init user
if (!isset($sessionObj) || !is_object($sessionObj)) $sessionObj = new \cmsSession();
$objUser = \FWUser::getFWUserObject()->objUser;
$objUser->login();

// update license, return "false" if no connection to license server could be established
$license = \Cx\Core_Modules\License\License::getCached($_CONFIG, $objDatabase);
$licenseCommunicator = \Cx\Core_Modules\License\LicenseCommunicator::getInstance($_CONFIG);
try {
    $licenseCommunicator->update(
        $license,
        $_CONFIG,
        (isset($_GET['force']) && $_GET['force'] == 'true'),
        false,
        $_CORELANG,
        (isset($_POST['response']) && $objUser->getAdminStatus() ? contrexx_input2raw($_POST['response']) : '')
    );
} catch (\Exception $e) {
    $license->check();
    if (!isset($_GET['nosave']) || $_GET['nosave'] != 'true') {
        $license->save(new \settingsManager(), $objDatabase);
    }
    if (!isset($_GET['silent']) || $_GET['silent'] != 'true') {
        echo "false";
    }
    return;
}
$license->check();
if (!isset($_GET['nosave']) || $_GET['nosave'] != 'true') {
    $license->save(new \settingsManager(), $objDatabase);
}

if (!$objUser->login(true)) {
    // do not use die() here, or installer will not show success page
    return;
}

if (isset($_GET['silent']) && $_GET['silent'] == 'true') {
    return true;
}

// show info
$message = $license->getMessage(false, \FWLanguage::getLanguageCodeById(LANG_ID), $_CORELANG);
echo json_encode(array(
    'status' => contrexx_raw2xhtml($license->getState()),
    'link' => contrexx_raw2xhtml($message->getLink()),
    'target' => contrexx_raw2xhtml($message->getLinkTarget()),
    'text' => contrexx_raw2xhtml($message->getText()),
    'class' => contrexx_raw2xhtml($message->getType()),
));
