<?php
/**
 * Install Wizard Controller
 *
 * The Install Wizard
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @version       $Id:     Exp $
 * @package     contrexx
 * @subpackage  installer
 * @todo        Edit PHP DocBlocks!
 */

@error_reporting (0);
@ini_set('display_errors', 0);
$php = phpversion();
if ($php < "5.2") {
	errorBox("Das Contrexx CMS benötigt mindestens PHP in der Version 5.2.<br>Auf Ihrem System läuft PHP ".$php);
}

/**
 * Display error message
 */
function errorBox($errmsg){
    print "<html><body>" .$errmsg . "</body></html>";
    die;
}


$offsetPath = "";
$arrDirectories = explode('/', $_SERVER['SCRIPT_NAME']);
for ($i = 0;$i < count($arrDirectories)-2;$i++) {
    if ($arrDirectories[$i] !== '') {
        $offsetPath .= '/'.$arrDirectories[$i];
    }
}
session_set_cookie_params(0, $offsetPath);
session_start();

$basePath = realpath(dirname(__FILE__));

define('ASCMS_LIBRARY_PATH', realpath(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'lib'));

if (!@include_once($basePath.'/config/config.php')) {
	die('Unable to load file '.$basePath.'/config/config.php');
}

@header('content-type: text/html; charset='.($useUtf8 ? 'UTF-8' : 'ISO-8859-1'));

if (!@include_once(ASCMS_LIBRARY_PATH.'/PEAR/HTML/Template/Sigma/Sigma.php')) {
	die('Unable to load file '.ASCMS_LIBRARY_PATH.'/PEAR/HTML/Template/Sigma/Sigma.php');
} elseif (!@include_once($basePath.'/common.class.php')) {
	die('Unable to load file '.$basePath.'/common.class.php');
} elseif (!@include_once($basePath.'/installer.class.php')) {
	die('Unable to load file '.$basePath.'/installer.class.php');
}

$objCommon = new CommonFunctions;
$objInstaller = new Installer;

$objCommon->initLanguage();

$objTpl = new HTML_Template_Sigma($templatePath);
$objTpl->setErrorHandling(PEAR_ERROR_DIE);
$objTpl->loadTemplateFile('index.html');
$objTpl->setVariable('CHARSET', $useUtf8 ? 'UTF-8' : 'ISO-8859-1');

$objTpl->setVariable($_ARRLANG);
$objInstaller->checkOptions();
$objInstaller->getNavigation();
$objInstaller->getPage();
$objInstaller->getContentNavigation();

$objTpl->show();
?>
