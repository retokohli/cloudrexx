<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
/**
 * This is just a wrapper to load the contrexx class
 * It is used in order to display a proper error message on hostings without
 * PHP 5.3 or newer.
 * 
 * DO NOT USE NAMESPACES WITHIN THIS FILE or else the error message won't be
 * displayed on these hostings.
 * 
 * Checks PHP version, loads debugger and initial config, checks if installed
 * and loads the Contrexx class
 * @version 3.1.0
 * @author Michael Ritter <michael.ritter@comvation.com>
 */

// Check php version (5.3 or newer is required)
$php = phpversion();
if (version_compare($php, '5.3.0') < 0) {
    die('Das Contrexx CMS ben&ouml;tigt mindestens PHP in der Version 5.3.<br />Auf Ihrem System l&auml;uft PHP '.$php);
}

global $_PATHCONFIG;
/**
 * Load config for this instance
 */
include_once dirname(__FILE__).'/config/settings.php';
include_once dirname(__FILE__).'/config/configuration.php';

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
require_once $_PATHCONFIG['ascms_installation_root'].$_PATHCONFIG['ascms_installation_offset'].'/lib/FRAMEWORK/DBG/DBG.php';
//\DBG::activate(DBG_PHP);//*/
//var_dump($_PATHCONFIG);
require_once($_PATHCONFIG['ascms_installation_root'].$_PATHCONFIG['ascms_installation_offset'].'/core/Cx.class.php');
init();
