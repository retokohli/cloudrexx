<?php

/**
 * @copyright   CONTREXX CMS - COMVATION AG
 * @package     contrexx
 * @subpackage  config
 * @todo        Edit PHP DocBlocks!
 */

global $_PATHCONFIG, $_DBCONFIG, $_CONFIG;
static $match = null;

/**
 * Define constants
 */
define('ASCMS_PATH',                        $_PATHCONFIG['ascms_installation_root']);
define('ASCMS_PATH_OFFSET',                 $_PATHCONFIG['ascms_installation_offset']); // example '/cms'
define('ASCMS_INSTANCE_PATH',               $_PATHCONFIG['ascms_root']);
define('ASCMS_INSTANCE_OFFSET',             $_PATHCONFIG['ascms_root_offset']);
define('ASCMS_BACKEND_PATH',                '/cadmin');
define('ASCMS_PROTOCOL',                    empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off' ? 'http' : 'https');
define('ASCMS_WEBSERVER_SOFTWARE',          !empty($_SERVER['SERVER_SOFTWARE']) && stristr($_SERVER['SERVER_SOFTWARE'], 'apache') ? 'apache' : (!empty($_SERVER['SERVER_SOFTWARE']) && stristr($_SERVER['SERVER_SOFTWARE'], 'iis') ? 'iis' : ''));

define('CONTREXX_ESCAPE_GPC',               get_magic_quotes_gpc());
define('CONTREXX_CHARSET',                  $_CONFIG['coreCharacterEncoding']);
define('CONTREXX_PHP5',                     version_compare(PHP_VERSION, '5', '>='));
define('CONTREXX_DIRECTORY_INDEX',          'index.php');

define('DBPREFIX',                          $_DBCONFIG['tablePrefix']);
define('ASCMS_DOCUMENT_ROOT',               ASCMS_PATH.ASCMS_PATH_OFFSET);
define('ASCMS_CUSTOMIZING_PATH',            ASCMS_INSTANCE_PATH.ASCMS_INSTANCE_OFFSET.'/customizing');
define('ASCMS_CUSTOMIZING_WEB_PATH',        ASCMS_INSTANCE_OFFSET.'/customizing');

require_once ASCMS_DOCUMENT_ROOT.'/config/settings.php';

if (
    isset($_CONFIG['useCustomizings']) && $_CONFIG['useCustomizings'] == 'on' &&
    file_exists(ASCMS_CUSTOMIZING_PATH.'/config/SetCustomizableConstants.php')
) {
    require_once ASCMS_CUSTOMIZING_PATH.'/config/SetCustomizableConstants.php';
    
    // load constants from basepath
} else if (file_exists(ASCMS_DOCUMENT_ROOT.'/config/SetCustomizableConstants.php')) {
    require_once ASCMS_DOCUMENT_ROOT.'/config/SetCustomizableConstants.php';
}
