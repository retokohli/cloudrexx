<?php
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
* Set installation statsu
* -------------------------------------------------------------------------
*/
define('CONTEXX_INSTALLED', false);

/**
* -------------------------------------------------------------------------
* Database configuration section
* -------------------------------------------------------------------------
*/
$_DBCONFIG['host'] = 'localhost'; // This is normally set to localhost
$_DBCONFIG['database'] = ''; // Database name
$_DBCONFIG['tablePrefix'] = 'contrexx_'; // Database table prefix
$_DBCONFIG['user'] = ''; // Database username
$_DBCONFIG['password'] = '; // Database password
$_DBCONFIG['dbType'] = 'mysql';	// Database type (e.g. mysql,postgres ..)
$_DBCONFIG['charset'] = 'default'; // Charset (default, latin1, utf8, ..)

/**
* -------------------------------------------------------------------------
* Site path specific configuration
* -------------------------------------------------------------------------
*/
$_PATHCONFIG['ascms_root'] = '';
$_PATHCONFIG['ascms_root_offset'] = ''; // example: '/cms';

/**
* -------------------------------------------------------------------------
* Ftp specific configuration
* -------------------------------------------------------------------------
*/
$_FTPCONFIG['is_activated'] = false; // Ftp support true or false
$_FTPCONFIG['use_passive'] = false;	// Use passive ftp mode
$_FTPCONFIG['host']	= 'localhost';// This is normally set to localhost
$_FTPCONFIG['port'] = 21; // Ftp remote port
$_FTPCONFIG['username'] = ''; // Ftp login username
$_FTPCONFIG['password']	= ''; // Ftp login password
$_FTPCONFIG['path']	= ''; // Ftp path to cms

/**
* -------------------------------------------------------------------------
* Optional customizing exceptions
* Shopnavbar : If set to TRUE the shopnavbar will appears on each page
* -------------------------------------------------------------------------
*/
$_CONFIGURATION['custom']['shopnavbar'] = false; // true|false
$_CONFIGURATION['custom']['shopJsCart'] = false; // true|false

/**
* Set character encoding
*/
$_CONFIG['coreCharacterEncoding'] = ''; // example 'UTF-8'
@ini_set('default_charset', $_CONFIG['coreCharacterEncoding']);

/**
* Set output url seperator
*/
@ini_set('arg_separator.output', '&amp;');

/**
* Set url rewriter tags
*/
@ini_set('url_rewriter.tags', 'a=href,area=href,frame=src,iframe=src,input=src,form=,fieldset=');

/**
* -------------------------------------------------------------------------
* Set constants
* -------------------------------------------------------------------------
*/
require_once dirname(__FILE__).'/set_constants.php';
?>
