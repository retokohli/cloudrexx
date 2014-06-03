<?php
define('UPDATE_PATH', dirname(dirname(__FILE__)));
define('UPDATE_TIME', time());
define('UPDATE_TIMEOUT_TIME', UPDATE_TIME + 55);
define('UPDATE_LIB', UPDATE_PATH.'/lib');
define('UPDATE_CORE', UPDATE_PATH.'/core');
define('UPDATE_TPL', 'template/contrexx');
define('UPDATE_LANG', UPDATE_PATH.'/lang');
define('UPDATE_UPDATES', UPDATE_PATH.'/updates');
define('UPDATE_SUPPORT_FORUM_URI', 'http://www.contrexx.com/forum/');
define('UPDATE_UTF8', true);
define('UPDATE_TIMEZONE', 'Europe/Zurich');
date_default_timezone_set(UPDATE_TIMEZONE);