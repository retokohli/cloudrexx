<?PHP

include_once('../lib/DBG.php');
/**
 * Debug level, see lib/DBG.php
 *   DBG_NONE            - Turn debugging off
 *   DBG_PHP             - show PHP errors/warnings/notices
 *   DBG_ADODB           - show ADODB queries
 *   DBG_ADODB_TRACE     - show ADODB queries with backtrace
 *   DBG_LOG_FILE        - DBG: log to file (/dbg.log)
 *   DBG_LOG_FIREPHP     - DBG: log via FirePHP
 *   DBG_ALL             - sets all debug flags
 */
define('_DEBUG', DBG_PHP);

//-------------------------------------------------------
// Set error reporting
//-------------------------------------------------------
if (_DEBUG) {
// These globals are both unused and unnecessary.  Please use the constants.
//    $_DBG['dbgPHP']         = (_DEBUG & DBG_PHP         ? true : false);
//    $_DBG['dbgADODB']       = (_DEBUG & DBG_ADODB       ? true : false);
//    $_DBG['dbgADODBTrace']  = (_DEBUG & DBG_ADODB_TRACE ? true : false);
//    $_DBG['dbgLogFile']     = (_DEBUG & DBG_LOG_FILE    ? true : false);
//    $_DBG['dbgLogFirePHP']  = (_DEBUG & DBG_LOG_FIREPHP ? true : false);
    DBG::enable_all();
    if (_DEBUG & DBG_LOG_FILE)                              DBG::setup('dbg.log', 'w');
    if (_DEBUG & DBG_LOG_FIREPHP)                           DBG::enable_firephp();
    if ((_DEBUG & DBG_ADODB) or (_DEBUG & DBG_ADODB_TRACE)) DBG::enable_adodb();
}

if (_DEBUG & DBG_PHP) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

include_once('../config/configuration.php');
include_once('../config/settings.php');
include_once('../config/version.php');
require_once '../core/API.php';
$errorMsg = '';
/**
 * Database object
 * @global ADONewConnection $objDatabase
 */
$objDatabase = getDatabaseObject($errorMsg);

if ($objDatabase === false) {
    die(
        'Database error.'.
        ($errorMsg != '' ? "<br />Message: $errorMsg" : '')
    );
}

$errmsg = '';
function error($msg) {
    global $errmsg;
    $errmsg = $msg;
}
define('NEED_FIX',      true);
define('NO_FIX_NEEDED', false);

if (_DEBUG & DBG_ADODB_TRACE) {
    $objDatabase->debug = 99;
} elseif (_DEBUG & DBG_ADODB) {
    $objDatabase->debug = 1;
} else {
    $objDatabase->debug = 0;
}
echo "<h1>Installing hotfixes...</h1>";
echo "<pre>";
foreach (glob('fixes/*.php') as $fix_file) {
    $parts    = explode('_', $fix_file);
    $nr       = intval(array_shift($parts));
    $func     = preg_replace('#.php$#', '', join('_', $parts));
    include_once($fix_file);
    
    $check    = "check_$func";
    $fix      = "fix_$func";

    echo "fix $func... ";
    if (!call_user_func($check)) {
        echo "not needed\n";
        continue;
    }
    if (call_user_func($fix)) {
        echo "fixed\n";
    }
    else {
        echo "fail!\n";
        echo $errmsg;
        exit;
    }
}

function fix_status($check, $message) {
    if ($check) {
        return true;
    }
    error($message);
    return false;
}


