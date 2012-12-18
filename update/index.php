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
require_once dirname(__FILE__).'/lib/DBG.php';
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
require_once(ASCMS_DOCUMENT_ROOT . '/config/version.php');

// Check if the system is installed
if (!defined('CONTEXX_INSTALLED') || !CONTEXX_INSTALLED) {
    header('Location: ../installer/index.php');
    exit;
} else if ($incSettingsStatus === false) {
    die('System halted: Unable to load basic configuration!');
}

// Library and core files
require_once(ASCMS_LIBRARY_PATH . '/PEAR/HTML/Template/Sigma/Sigma.php');
require_once(ASCMS_LIBRARY_PATH . '/adodb/adodb.inc.php');
require_once(ASCMS_CORE_PATH . '/database.php');
require_once(ASCMS_CORE_PATH . '/validator.inc.php');
require_once(ASCMS_CORE_PATH . '/session.class.php');
require_once(ASCMS_CORE_PATH . '/Init.class.php');

// Update files
require_once(UPDATE_PATH . '/Contrexx_Update.class.php');
require_once(UPDATE_PATH . '/lib/FRAMEWORK/UpdateUtil.class.php');

require_once(UPDATE_PATH . '/lib/Env.class.php');
\Env::set('config', $_CONFIG);
\Env::set('dbconfig', $_DBCONFIG);
\Env::set('ftpConfig', $_FTPCONFIG);

// Global doctrine loggable listener
$loggableListener = null;

// Global doctrine inclusion status
$incDoctrineStatus = false;

// Start session
$sessionObj = new cmsSession();
$sessionObj->cmsSessionStatusUpdate('backend');

// Start update
$objUpdate = new Contrexx_Update();
die($objUpdate->getPage());
