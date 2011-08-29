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
$_DBCONFIG['database'] = 'cm_2_3';
$_DBCONFIG['tablePrefix'] = 'contrexx_'; // Database table prefix
$_DBCONFIG['user'] = 'root';
$_DBCONFIG['password'] = '1234';
$_DBCONFIG['dbType'] = 'mysql';	// Database type (e.g. mysql,postgres ..)
$_DBCONFIG['charset'] = 'utf8';

/**
* -------------------------------------------------------------------------
* Site path specific configuration
* -------------------------------------------------------------------------
*/
$_PATHCONFIG['ascms_root'] = '/home/srz/web/root';
$_PATHCONFIG['ascms_root_offset'] = '/cm_2_3';

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
$_FTPCONFIG['path']	= ''; // Ftp path to cms (must not include ascms_root_offset)

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
$_CONFIG['coreCharacterEncoding'] = 'UTF-8';
$_CONFIG['coreCharacterEncoding'] = 'UTF-8';

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
