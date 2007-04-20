<?php
/**
 * Installer config
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author        Astalavista Development Team <thun@astalvista.ch>
 * @version       1.0.0
 * @package     contrexx
 * @subpackage  core
 * @todo        Edit PHP DocBlocks!
 */

$requiredPHPVersion = "4.3";
$requiredMySQLVersion = "3.23";
$requiredGDVersion = "1.6";
$dbType = "mysql";
$defaultLanguage = "de";
$licenseFileCommerce = "data".DIRECTORY_SEPARATOR."contrexx_lizenz_de.txt";
$licenseFileOpenSource = "data".DIRECTORY_SEPARATOR."contrexx_lizenz_opensource_de.txt";
$configFile = "/config/configuration.php";
$configTemplateFile = "data".DIRECTORY_SEPARATOR."configuration.tpl";
$versionFile = "/config/version.php";
$versionTemplateFile = "data".DIRECTORY_SEPARATOR."version.tpl";
$sqlDumpFile = DIRECTORY_SEPARATOR."installer".DIRECTORY_SEPARATOR."data".DIRECTORY_SEPARATOR."contrexx_dump";
$dbPrefix = "contrexx_";
$templatePath = "template/contrexx/";
$supportEmail = "support@contrexx.com";
$supportURI = "http://www.contrexx.com/index.php?page=754";
$forumURI = "http://www.contrexx.com/forum/";
$contrexxURI = "http://www.contrexx.com/";


$arrDefaultConfig = array(
	'dbHostname'	=> 'localhost',
	'dbUsername'	=> '',
	'dbPassword'	=> '',
	'dbDatabaseName'	=> '',
	'dbTablePrefix'	=> 'contrexx_',
	'ftpHostname'	=> 'localhost',
	'ftpUsername'	=> '',
	'ftpPassword'	=> ''
);

$arrLanguages = array(
	1	=> array(
		'id'	=> 1,
		'lang'	=> 'de',
		'name'	=> 'Deutsch',
		'is_default'	=> true
	),
	2	=> array(
		'id'	=> 2,
		'lang'	=> 'en',
		'name'	=> 'English',
		'is_default'	=> false
	)/*,
	3	=> array(
		'id'	=> 3,
		'lang'	=> 'fr',
		'name'	=> 'French',
		'is_default'	=> false
	),
	4	=> array(
		'id'	=> 4,
		'lang'	=> 'it',
		'name'	=> 'Italian',
		'is_default'	=> false
	),
	5	=> array(
		'id'	=> 5,
		'lang'	=> 'dk',
		'name'	=> 'Danish',
		'is_default'	=> false
	)*/
);

$arrFiles = array(
	'/admin/backup' => array(
		'mode'		=> '0777',
		'mode_oct'	=> 0777
	),
	'/feed'	=> array(
		'mode'		=> '0777',
		'mode_oct'	=> 0777,
		'sub_dirs'	=> true
	),
	'/media'	=> array(
		'mode'		=> '0777',
		'mode_oct'	=> 0777,
		'sub_dirs'	=> true
	),
	'/images'	=> array(
		'mode'		=> '0777',
		'mode_oct'	=> 0777,
		'sub_dirs'	=> true
	),
	'/themes'	=> array(
		'mode'		=> '0777',
		'mode_oct'	=> 0777,
		'sub_dirs'	=> true
	),
	'/tmp'		=> array(
		'mode'		=> '0777',
		'mode_oct'	=> 0777,
		'sub_dirs'	=> true
	),
	$configFile => array(
		'mode'		=> '0706',
		'mode_oct'	=> 0706
	),
	$versionFile => array(
		'mode'		=> '0706',
		'mode_oct'	=> 0706
	),
	'/config/settings.php'	=> array(
		'mode'		=> '0666',
		'mode_oct'	=> 0706
	),
	'/cache'	=> array(
		'mode'		=> '0777',
		'mode_oct'	=> 0777
	),
	'/sitemap.xml'	=> array(
		'mode'		=> '0777',
		'mode_oct'	=> 0777
	)
);

$arrDatabaseTables = array(/*DB_TABLES_ARRAY*/);
?>
