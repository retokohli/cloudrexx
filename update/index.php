<?php
/**
 * Contrexx Update System
 *
 * This class is used to update the system to a newer version of Contrexx.
 * 
 * @copyright   Contrexx WMS - Comvation AG
 * @author      Comvation Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  update
 */

// Debugging
require_once dirname(__FILE__).'/lib/DBG.php';
DBG::deactivate();

define('UPDATE_PATH', dirname(__FILE__));

// Config files
require_once dirname(UPDATE_PATH) . '/config/configuration.php';
require_once ASCMS_DOCUMENT_ROOT  . '/config/settings.php';
require_once ASCMS_DOCUMENT_ROOT  . '/config/version.php';


// Library and core files
require_once ASCMS_LIBRARY_PATH . '/PEAR/HTML/Template/Sigma/Sigma.php';
require_once ASCMS_LIBRARY_PATH . '/adodb/adodb.inc.php';
require_once ASCMS_CORE_PATH    . '/database.php';
require_once ASCMS_CORE_PATH    . '/session.class.php';
require_once ASCMS_CORE_PATH    . '/Init.class.php';

// Update files
require_once UPDATE_PATH . '/Contrexx_Update.class.php';
require_once UPDATE_PATH . '/lib/FRAMEWORK/UpdateUtil.class.php';
require_once UPDATE_PATH . '/config/configuration.php';

// Start session
$sessionObj = new cmsSession();
$sessionObj->cmsSessionStatusUpdate('backend');

// Start update
$objUpdate = new Contrexx_Update();
die($objUpdate->getPage());
