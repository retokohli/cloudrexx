<?php
/**
 * Debug level, see lib/DBG.php
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
include_once '../lib/DBG.php';
//DBG::activate(DBG_ERROR_FIREPHP);
//\DBG::activate(DBG_PHP);

$php = phpversion();
if ($php < '5.3') {
    die('Das Contrexx CMS ben&uml;tigt mindestens PHP in der Version 5.3.<br />Auf Ihrem System l&auml;uft PHP '.$php);
}

$_DBCONFIG = $_CONFIGURATION = $_CONFIG = null;
/**
 * User configuration settings
 *
 * This file is re-created by the CMS itself. It initializes the
 * {@link $_CONFIG[]} global array.
 */
$incSettingsStatus = include_once dirname(dirname(__FILE__)).'/config/settings.php';
/**
 * Path, database, FTP configuration settings
 *
 * Initialises global settings array and constants.
 */
include_once dirname(dirname(__FILE__)).'/config/configuration.php';

$customizing = null;
if (isset($_CONFIG['useCustomizings']) && $_CONFIG['useCustomizings'] == 'on') {
    $customizing = ASCMS_CUSTOMIZING_PATH;
}

if ($customizing && file_exists(ASCMS_CUSTOMIZING_PATH.'/core/initBackend.php')) {
    require_once(ASCMS_CUSTOMIZING_PATH.'/core/initBackend.php');
} else {
    require_once(ASCMS_CORE_PATH.'/initBackend.php');
}
