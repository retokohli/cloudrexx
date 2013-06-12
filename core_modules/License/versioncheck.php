<?php
global $sessionObj, $_CONFIG, $_CORELANG, $_PATHCONFIG, $license, $objDatabase;

// Check php version (5.3 or newer is required)
$php = phpversion();
if (version_compare($php, '5.3.0') < 0) {
    die('Das Contrexx CMS ben&ouml;tigt mindestens PHP in der Version 5.3.<br />Auf Ihrem System l&auml;uft PHP '.$php);
}

include_once dirname(dirname(dirname(__FILE__))).'/config/configuration.php';

require_once $_PATHCONFIG['ascms_installation_root'].$_PATHCONFIG['ascms_installation_offset'].'/lib/FRAMEWORK/DBG/DBG.php';
//\DBG::activate(DBG_PHP);//*/

require_once($_PATHCONFIG['ascms_installation_root'].$_PATHCONFIG['ascms_installation_offset'].'/core/Cx.class.php');
$cx = init('minimal');

// Init user
if (!isset($sessionObj) || !is_object($sessionObj)) $sessionObj = new \cmsSession();
$objUser = \FWUser::getFWUserObject()->objUser;
$objUser->login();

// update license, return "false" if no connection to license server could be established
$license = $cx->getLicense();
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
