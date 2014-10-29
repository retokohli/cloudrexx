<?php
global $_DBCONFIG, $_PATHCONFIG, $_FTPCONFIG, $_CONFIG;
/**
* @exclude
*
* Contrexx CMS Web Installer
* Please use the Contrexx CMS installer to configure this file
* or edit this file and configure the parameters for your site and
* database manually.
*/

/**
* -------------------------------------------------------------------------
* Set installation status
* -------------------------------------------------------------------------
*/
define('CONTEXX_INSTALLED', true);

/**
* -------------------------------------------------------------------------
* Database configuration section
* -------------------------------------------------------------------------
*/
$_DBCONFIG['host'] = 'localhost'; // This is normally set to localhost
$_DBCONFIG['database'] = 'dev'; // Database name
$_DBCONFIG['tablePrefix'] = 'contrexx_'; // Database table prefix
$_DBCONFIG['user'] = 'root'; // Database username
$_DBCONFIG['password'] = ''; // Database password
$_DBCONFIG['dbType'] = 'mysql';    // Database type (e.g. mysql,postgres ..)
$_DBCONFIG['charset'] = 'utf8'; // Charset (default, latin1, utf8, ..)
$_DBCONFIG['timezone'] = 'Europe/Zurich'; // Controller's timezone for model
$_DBCONFIG['collation'] = 'utf8_unicode_ci';

/**
* -------------------------------------------------------------------------
* Site path specific configuration
* -------------------------------------------------------------------------
*/
$_PATHCONFIG['ascms_root'] = '';
$_PATHCONFIG['ascms_root_offset'] = ''; // example: '/cms';
$_PATHCONFIG['ascms_installation_root'] = $_PATHCONFIG['ascms_root'];
$_PATHCONFIG['ascms_installation_offset'] = $_PATHCONFIG['ascms_root_offset']; // example: '/cms';

/**
* -------------------------------------------------------------------------
* Ftp specific configuration
* -------------------------------------------------------------------------
*/
$_FTPCONFIG['is_activated'] = false; // Ftp support true or false
$_FTPCONFIG['host']    = 'localhost';// This is normally set to localhost
$_FTPCONFIG['port'] = 21; // Ftp remote port
$_FTPCONFIG['username'] = ''; // Ftp login username
$_FTPCONFIG['password'] = ''; // Ftp login password
$_FTPCONFIG['path'] = ''; // Ftp path to cms (must not include ascms_root_offset)

/**
* -------------------------------------------------------------------------
* Base setup (altering might break the system!)
* -------------------------------------------------------------------------
*/
// Set character encoding
$_CONFIG['coreCharacterEncoding'] = 'UTF-8'; // example 'UTF-8'
