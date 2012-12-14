<?php
define('UPDATE_PATH', dirname(dirname(__FILE__)));
define('UPDATE_TIMEOUT_TIME', time() + (ini_get('max_execution_time') ? ini_get('max_execution_time') : 300 /* Default Apache and IIS Timeout */) - (ini_get('max_execution_time') > 5 ? 5 : 0) /* Add a time buffer of 6 seconds */);
define('UPDATE_LIB', UPDATE_PATH.'/lib');
define('UPDATE_TPL', 'template/contrexx');
define('UPDATE_LANG', UPDATE_PATH.'/lang');
define('UPDATE_UPDATES', UPDATE_PATH.'/updates');
define('UPDATE_SUPPORT_FORUM_URI', 'http://www.contrexx.com/forum/');
define('UPDATE_UTF8', true);
define('UPDATE_TIMEZONE', 'Europe/Zurich');
date_default_timezone_set(UPDATE_TIMEZONE);