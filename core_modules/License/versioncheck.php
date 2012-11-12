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
require_once($documentRoot.'/lib/DBG.php');
require_once($documentRoot.'/config/settings.php');              // needed for configuration.php
require_once($documentRoot.'/config/configuration.php');         // needed for API

$customizing = null;
if (isset($_CONFIG['useCustomizings']) && $_CONFIG['useCustomizings'] == 'on') {
// TODO: webinstaller check: has ASCMS_CUSTOMIZING_PATH already been defined in the installation process?
    $customizing = ASCMS_CUSTOMIZING_PATH;
}

require_once($documentRoot.'/core/ClassLoader/ClassLoader.class.php');
$cl = new \Cx\Core\ClassLoader\ClassLoader($documentRoot, false, $customizing);

$cl->loadFile($documentRoot.'/core/Env.class.php');               // needed for FileSystem
Env::set('ClassLoader', $cl);
Env::set('config', $_CONFIG);
Env::set('ftpConfig', $_FTPCONFIG);

$cl->loadFile($documentRoot.'/core/API.php');                             // needed for getDatabaseObject()
$cl->loadFile($documentRoot.'/lib/FRAMEWORK/User/User_Setting_Mail.class.php');
$cl->loadFile($documentRoot.'/lib/FRAMEWORK/User/User_Setting.class.php');
$cl->loadFile($documentRoot.'/lib/FRAMEWORK/User/User_Profile_Attribute.class.php');
$cl->loadFile($documentRoot.'/lib/FRAMEWORK/User/User_Profile.class.php');
$cl->loadFile($documentRoot.'/lib/FRAMEWORK/User/UserGroup.class.php');
$cl->loadFile($documentRoot.'/lib/FRAMEWORK/User/User.class.php');
$cl->loadFile($documentRoot.'/lib/FRAMEWORK/Language.class.php');
$cl->loadFile($documentRoot.'/lib/FRAMEWORK/FWUser.class.php');
$cl->loadFile($documentRoot.'/lib/PEAR/HTTP/Request2.php');
$cl->loadFile($documentRoot.'/core/Init.class.php');
$cl->loadFile($documentRoot.'/core/settings.class.php');
$cl->loadFile($documentRoot.'/core/session.class.php');

$objDatabase = getDatabaseObject($strErrMessage, true);
$objInit = new InitCMS('backend', null);

$objInit->_initBackendLanguage();
$_LANGID = $objInit->getBackendLangId();
define('LANG_ID', $_LANGID);

// load interface texts, might be used by the license system in case of communication errors
$_CORELANG = $objInit->loadLanguageData('core');

// update license
$license = \Cx\Core_Modules\License\License::getCached($_CONFIG, $objDatabase);
$licenseCommunicator = \Cx\Core_Modules\License\LicenseCommunicator::getInstance($_CONFIG);
$licenseCommunicator->update($license, $_CONFIG, (isset($_GET['force']) && $_GET['force'] == 'true'), false, $_CORELANG);
$license->check();
if (!isset($_GET['nosave']) || $_GET['nosave'] != 'true') {
    $license->save(new \settingsManager(), $objDatabase);
}

if (!isset($sessionObj) || !is_object($sessionObj)) $sessionObj = new \cmsSession();

$objUser = \FWUser::getFWUserObject()->objUser;
if (!$objUser->login(true)) {
    // do not use die() here, or installer will not show success page
    return;
}

if (isset($_GET['silent']) && $_GET['silent'] == 'true') {
    return;
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

