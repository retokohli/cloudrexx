<?php
/**
 * Contrexx Update System
 *
 * This class is used to update the system to a newer version of Contrexx
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @version       $Id:     Exp $
 * @package     contrexx
 * @subpackage  update
 * @todo        Edit PHP DocBlocks!
 */

@error_reporting (0);
@ini_set('display_errors', 0);

define('UPDATE_PATH', dirname(__FILE__));

if (!@include_once(UPDATE_PATH.'/../config/configuration.php')) {
    die('Couldn\'t load configuration file <i>'.realpath(UPDATE_PATH.'/../config/configuration.php').'</i>!');
} elseif (!@include_once(ASCMS_DOCUMENT_ROOT.'/config/version.php')) {
    die('Couldn\'t load version file <i>'.ASCMS_DOCUMENT_ROOT.'/config/version.php'.'</i>!');
} elseif (!@include_once(ASCMS_CORE_PATH.'/API.php')) {
    die('Couldn\'t load contrexx API file <i>'.ASCMS_CORE_PATH.'/API.php</i>!');
} elseif (!@include_once(UPDATE_PATH.'/Contrexx_Update.class.php')) {
    die('Couldn\'t load contrexx update system <i>'.UPDATE_PATH.'/Contrexx_Update.class.php'.'</i>!');
} elseif (!@include_once(UPDATE_PATH.'/config/configuration.php')) {
    die('Couldn\'t load contrexx update system configuration file <i>'.UPDATE_PATH.'/config/configuration.php'.'</i>!');
} else {
    $_SYSCONFIG = false;
    @include_once(ASCMS_DOCUMENT_ROOT.'/config/settings.php');
    if (is_array($_SYSCONFIG)) {
        foreach ($_SYSCONFIG as $sysconfigKey => $sysconfValue) {
            $_CONFIG[$sysconfigKey] = $sysconfValue;
        }
    }

    $sessionObj = new cmsSession();
    $sessionObj->cmsSessionStatusUpdate('backend');

    $objUpdate = new Contrexx_Update();
    die($objUpdate->getPage());
}
?>
