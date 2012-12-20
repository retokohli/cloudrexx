<?php
/**
 * Contrexx Update System
 *
 * This class is used to update the system to a newer version of Contrexx.
 * 
 * @copyright   Contrexx WMS - Comvation AG
 * @author      Comvation Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  update
 */

// Debugging
require_once dirname(__FILE__).'/core/DBG.php';
\DBG::deactivate();

// Try to enable APC
$apcEnabled = false;
if (extension_loaded('apc')) {
    if (ini_get('apc.enabled')) {
        $apcEnabled = true;
    } else {
        ini_set('apc.enabled', 1);
        if (ini_get('apc.enabled')) {
            $apcEnabled = true;
        }
    }
}

// Disable eAccelerator if active
if (extension_loaded('eaccelerator')) {
    ini_set('eaccelerator.enable', 0);
    ini_set('eaccelerator.optimizer', 0);
}

// Try to set required memory_limit if not enough
preg_match('/^\d+/', ini_get('memory_limit'), $memoryLimit);
if ($apcEnabled) {
    if ($memoryLimit[0] < 32) {
        ini_set('memory_limit', '32M');
    }
} else {
    if ($memoryLimit[0] < 48) {
        ini_set('memory_limit', '48M');
    }
}

// Update configuration
require_once(dirname(__FILE__).'/config/configuration.php');

// Config files
$incSettingsStatus = require_once(dirname(UPDATE_PATH) . '/config/settings.php');
require_once(dirname(UPDATE_PATH) . '/config/configuration.php');
// Contrexx 3.0.0+ do not have a version.php. The information is included in settings.php
$incVersionStatus = @include_once(ASCMS_DOCUMENT_ROOT . '/config/version.php');

// Check if the system is installed
if (!defined('CONTEXX_INSTALLED') || !CONTEXX_INSTALLED) {
    header('Location: ../installer/index.php');
    exit;
} else if ($incSettingsStatus === false) {
    die('System halted: Unable to load basic configuration!');
}

require_once(UPDATE_CORE . '/Env.class.php');

Env::set('config', $_CONFIG);
Env::set('dbconfig', $_DBCONFIG);
Env::set('ftpConfig', $_FTPCONFIG);

// Library and core files
require_once(UPDATE_CORE . '/database.php');
require_once(UPDATE_CORE . '/validator.inc.php');
require_once(UPDATE_CORE . '/session.class.php');
require_once(UPDATE_CORE . '/Init.class.php');

require_once(UPDATE_LIB . '/PEAR/HTML/Template/Sigma/Sigma.php');
require_once(UPDATE_LIB . '/adodb/adodb.inc.php');
require_once(UPDATE_LIB . '/FRAMEWORK/Language.class.php');
require_once(UPDATE_LIB . '/FRAMEWORK/cxjs/ContrexxJavascript.class.php');
require_once(UPDATE_LIB . '/FRAMEWORK/Javascript.class.php');

$objDatabase = getDatabaseObject($errorMsg);
if (!$objDatabase) {
    die($errorMsg);
}
Env::set('db', $objDatabase);

// Start session
$sessionObj = new cmsSession();
$sessionObj->cmsSessionStatusUpdate('backend');

// Initialize base system
$objInit = new \InitCMS('update', \Env::em());
Env::set('init', $objInit);

JS::activate('cx');
JS::activate('jquery-tools');
JS::registerJS('lib/contrexxUpdate.php');
JS::registerJS('lib/javascript/html2dom.js');

// Update files
require_once(UPDATE_PATH . '/ContrexxUpdate.class.php');
require_once(UPDATE_LIB . '/FRAMEWORK/UpdateUtil.class.php');

// Start update
$objUpdate = new ContrexxUpdate();
$output = $objUpdate->getPage();
JS::findJavascripts($output);
$output = str_replace('javascript_inserting_here', \JS::getCode(), $output);

die($output);
