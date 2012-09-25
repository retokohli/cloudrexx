<?php
/*ini_set('display_errors', 1);
error_reporting(E_ALL);*/

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
require_once('../../core/Init.class.php');
require_once('../settings.class.php');
require_once('../ClassLoader/ClassLoader.class.php');
new \Cx\Core\ClassLoader\ClassLoader('../../', false);
$objDatabase = getDatabaseObject($strErrMessage);
$objInit = new InitCMS('backend', null);
global $_CONFIG;

// update license
$license = \Cx\Core\License\License::getCached($_CONFIG, $objDatabase);
$licenseCommunicator = \Cx\Core\License\LicenseCommunicator::getInstance($_CONFIG);
$licenseCommunicator->update($license, $_CONFIG, (isset($_GET['force']) && $_GET['force'] == 'true'));
$license->check();
$license->save(new settingsManager(), $objDatabase);

// show info
//echo $license->getState().' '.$license->getVersion()->getNumber().' '.$license->getEditionName();
$message = $license->getMessage();
echo json_encode(array(
    'link' => $message->getLink(),
    'target' => $message->getLinkTarget(),
    'text' => $message->getText(),
    'class' => $message->getType(),
));
