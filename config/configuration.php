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
define('CONTEXX_INSTALLED', true);

/**
* -------------------------------------------------------------------------
* Database configuration section
* -------------------------------------------------------------------------
*/
$_DBCONFIG['host'] = 'localhost'; // This is normally set to localhost
$_DBCONFIG['database'] = 'contrexx_2_1'; // Database name
$_DBCONFIG['tablePrefix'] = 'contrexx_'; // Database table prefix
$_DBCONFIG['user'] = 'root'; // Database username
$_DBCONFIG['password'] = '1234'; // Database password
$_DBCONFIG['dbType'] = 'mysql';	// Database type (e.g. mysql,postgres ..)
$_DBCONFIG['charset'] = 'utf8'; // Charset (default, latin1, utf8, ..)

/**
* -------------------------------------------------------------------------
* Site path specific configuration
* -------------------------------------------------------------------------
*/
$_PATHCONFIG['ascms_root'] = '/home/srz/s/wr';
$_PATHCONFIG['ascms_root_offset'] = '/contrexx_2_2'; // example: '/cms';

/**
* -------------------------------------------------------------------------
* Ftp specific configuration
* -------------------------------------------------------------------------
*/
$_FTPCONFIG['is_activated'] = true; // Ftp support true or false
$_FTPCONFIG['use_passive'] = false;	// Use passive ftp mode
$_FTPCONFIG['host']	= 'localhost';// This is normally set to localhost
$_FTPCONFIG['port'] = 21; // Ftp remote port
$_FTPCONFIG['username'] = 'contrexx_ftp'; // Ftp login username
$_FTPCONFIG['password']	= 'contrexx_ftp'; // Ftp login password
$_FTPCONFIG['path']	= '/home/srz/web/root'; // Ftp path to cms

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
$_CONFIG['coreCharacterEncoding'] = 'UTF-8'; // example 'UTF-8'
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
