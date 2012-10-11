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
require_once dirname(__FILE__).'/lib/DBG.php';
//DBG::activate(DBG_ADODB_ERROR|DBG_LOG_FIREPHP|DBG_PHP);
//\DBG::activate(DBG_PHP);

$php = phpversion();
if ($php < '5.3') {
    die('Das Contrexx CMS ben&uml;tigt mindestens PHP in der Version 5.3.<br />Auf Ihrem System l&auml;uft PHP '.$php);
}

require_once(dirname(__FILE__).'/init.php');
