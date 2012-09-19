<?php
/*ini_set('display_errors', 1);
error_reporting(E_ALL);*/

// load requirements
require_once('../../config/settings.php');              // needed for configuration.php
require_once('../../config/configuration.php');         // needed for API
require_once('../API.php');                             // needed for getDatabaseObject()
require_once('../../lib/PEAR/HTTP/Request2.php');
require_once('../settings.class.php');
require_once('../ClassLoader/ClassLoader.class.php');
new \Cx\Core\ClassLoader\ClassLoader('../../', false);
$objDatabase = getDatabaseObject($strErrMessage);
global $_CONFIG;

// update license
$license = \Cx\Core\License\License::getCached($_CONFIG, $objDatabase);
$licenseCommunicator = \Cx\Core\License\LicenseCommunicator::getInstance();
$licenseCommunicator->update($license, $_CONFIG);
$license->check();
$license->save(new settingsManager(), $objDatabase);

// show info
echo $license->getState().' '.$license->getVersion()->getNumber().' '.$license->getEditionName();
