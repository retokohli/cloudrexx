<?php
/**
 * This is just a wrapper to load the contrexx class
 * It is used in order to display a proper error message on hostings without
 * PHP 5.3 or newer.
 * 
 * DO NOT USE NAMESPACES WITHIN THIS FILE or else the error message won't be
 * displayed on these hostings.
 * 
 * Checks PHP version, loads debugger and initial config, checks if installed
 * and loads the Contrexx class (from customizing if configured to do so)
 * @version 3.1.0
 * @author Michael Ritter <michael.ritter@comvation.com>
 */

// Check php version (5.3 or newer is required)
$php = phpversion();
if (version_compare($php, '5.3.0') < 0) {
    die('Das Contrexx CMS ben&ouml;tigt mindestens PHP in der Version 5.3.<br />Auf Ihrem System l&auml;uft PHP '.$php);
}

/**
 * Debug level, see lib/FRAMEWORK/DBG/DBG.php
 *   DBG_PHP             - show PHP errors/warnings/notices
 *   DBG_ADODB           - show ADODB queries
 *   DBG_ADODB_TRACE     - show ADODB queries with backtrace
 *   DBG_ADODB_ERROR     - show ADODB queriy errors only
 *   DBG_LOG_FILE        - DBG: log to file (/dbg.log)
 *   DBG_LOG_FIREPHP     - DBG: log via FirePHP
 *
 * Use DBG::activate($level) and DBG::deactivate($level)
 * to activate/deactivate a debug level.
 * Calling these methods without specifying a debug level
 * will either activate or deactivate all levels.
 */
require_once dirname(__FILE__).'/lib/FRAMEWORK/DBG/DBG.php';
//\DBG::activate(DBG_PHP);

// Initialize global vars, in order to disable notices
// @todo: Don't use the global scope anymore
$_DBCONFIG = $_CONFIGURATION = $_CONFIG = null;

/**
 * User configuration settings
 *
 * This file is re-created by the CMS itself. It initializes the
 * {@link $_CONFIG[]} global array.
 */
$incSettingsStatus = include_once dirname(__FILE__).'/config/settings.php';

/**
 * Path, database, FTP configuration settings
 *
 * Initialises global settings array and constants.
 */
include_once dirname(__FILE__).'/config/configuration.php';

// Check if the system is installed
if (!defined('CONTEXX_INSTALLED') || !CONTEXX_INSTALLED) {
    header('Location: ../installer/index.php');
    exit;
} else if ($incSettingsStatus === false) {
    die('System halted: Unable to load basic configuration!');
}

// Check if the system is configured with enabled customizings
$customizing = null;
if (isset($_CONFIG['useCustomizings']) && $_CONFIG['useCustomizings'] == 'on') {
    $customizing = ASCMS_CUSTOMIZING_PATH;
}

/**
 * This is the old fashion way, requiring two separate files for front- and backend
 */
///*
if ($customizing && file_exists(ASCMS_CUSTOMIZING_PATH.'/core/initFrontend.php')) {
    require_once(ASCMS_CUSTOMIZING_PATH.'/core/initFrontend.php');
} else {
    require_once(ASCMS_CORE_PATH.'/initFrontend.php');
}//*/

/**
 * This is the new way (BETA). To test this, perform the following steps:
 * 1. Comment out the old fashion code above (just remove // on line 75)
 * 2. Uncomment the code below (add // on line 88)
 * 3. Remove cadmin from whitelist in .htaccess
 */
/*
// Load the Contrexx class (from customizing if enabled)
if ($customizing && file_exists(ASCMS_CUSTOMIZING_PATH.'/core/Cx.class.php')) {
    require_once(ASCMS_CUSTOMIZING_PATH.'/core/Cx.class.php');
} else {
    require_once(ASCMS_CORE_PATH.'/Cx.class.php');
}
// load in frontend mode as long as anything else than /cadmin is requested:
$frontend = $_GET['__cap'] != ASCMS_PATH_OFFSET.'/cadmin/index.php';

// Initialize the Contrexx class (we don't use the constructor in order to avoid namespaces:
init($frontend);//*/
