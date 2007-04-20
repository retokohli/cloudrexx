<?php
/**
 * Install Wizard Controller
 *
 * The Install Wizard
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author        Astalavista Development Team <thun@astalavista.ch>
 * @version       $Id:     Exp $
 * @package     contrexx
 * @subpackage  installer
 * @todo        Edit PHP DocBlocks!
 */

@error_reporting (0);
@ini_set('display_errors', 0);
@ini_set('default_charset', 'UTF-8');
$php = phpversion();
if ($php < "4.3") {
	errorBox("Das Contrexx CMS benötigt mindestens PHP in der Version 4.3.<br>Auf Ihrem System läuft PHP ".$php);
}

/**
 * Display error message
 */
function errorBox($errmsg){
    print "<html><body>" .$errmsg . "</body></html>";
    die;
}



session_start();

$basePath = realpath(dirname(__FILE__));

define('ASCMS_LIBRARY_PATH', realpath(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'lib'));

require_once 'config/config.php';
require_once '../config/version.php';
require_once '../lib/PEAR/HTML/Template/Sigma/Sigma.php';
require_once 'common.class.php';
require_once 'installer.class.php';

$objCommon = new CommonFunctions;
$objInstaller = new Installer;

$objCommon->initLanguage();

$objTpl = &new HTML_Template_Sigma($templatePath);
$objTpl->setErrorHandling(PEAR_ERROR_DIE);
$objTpl->loadTemplateFile('index.html',true,true);

$objTpl->setVariable($_ARRLANG);
$objInstaller->checkOptions();
$objInstaller->getNavigation();
$objInstaller->getPage();
$objInstaller->getContentNavigation();

$objTpl->show();
?>
