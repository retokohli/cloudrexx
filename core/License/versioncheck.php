<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
echo '<pre>';
// load requirements
require_once('../../config/settings.php');              // needed for configuration.php
require_once('../../config/configuration.php');         // needed for API
require_once('../API.php');                             // needed for getDatabaseObject()
require_once('../../lib/FRAMEWORK/User/User_Setting_Mail.class.php');
require_once('../../lib/FRAMEWORK/User/User_Setting.class.php');
require_once('../../lib/FRAMEWORK/User/User_Profile_Attribute.class.php');
require_once('../../lib/FRAMEWORK/User/User_Profile.class.php');
require_once('../../lib/FRAMEWORK/User/UserGroup.class.php');
require_once('../../lib/FRAMEWORK/User/User.class.php');
require_once('../../lib/FRAMEWORK/FWUser.class.php');
require_once('../../lib/PEAR/HTTP/Request2.php');
require_once('../../lib/DBG.php');
require_once('../../core/Init.class.php');
require_once('../settings.class.php');
require_once('../session.class.php');
require_once('../ClassLoader/ClassLoader.class.php');
new \Cx\Core\ClassLoader\ClassLoader('../../', false);

global $_CONFIG, $sessionObj, $objInit, $objDatabase;

$objDatabase = getDatabaseObject($strErrMessage);
$objInit = new InitCMS('backend', null);

// update license
$license = \Cx\Core\License\License::getCached($_CONFIG, $objDatabase);
$licenseCommunicator = \Cx\Core\License\LicenseCommunicator::getInstance($_CONFIG);
$licenseCommunicator->update($license, $_CONFIG, (isset($_GET['force']) && $_GET['force'] == 'true'));
$license->check();
if (!isset($_GET['nosave']) || $_GET['nosave'] != 'true') {
    $license->save(new \settingsManager(), $objDatabase);
}

if (!isset($sessionObj) || !is_object($sessionObj)) $sessionObj = new \cmsSession();

$objUser = \FWUser::getFWUserObject()->objUser;
if (!$objUser->login(true)) {
    die();
}

// show info
$lang = 'de';
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
}
$message = $license->getMessage($lang);
if (!$message) {
    $message = $license->getMessage('de');
}
if (!$message) {
    die('No message');
}
echo json_encode(array(
    'status' => $license->getState(),
    'link' => $message->getLink(),
    'target' => $message->getLinkTarget(),
    'text' => $message->getText(),
    'class' => $message->getType(),
));
