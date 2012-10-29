<?php
/**
 * Search script
 *
 * This script is standalone because otherwise it would be too slow.
 * @author Stefan Heinemann <sh@comvation.com>
 * @copyright Comvation AG <info@comvation.com>
 */

require_once(dirname(__FILE__).'/../../config/settings.php');
require_once(dirname(__FILE__).'/../../config/configuration.php');

include_once(ASCMS_LIBRARY_PATH.'/DBG.php');
//\DBG::activate(DBG_PHP);

require_once(ASCMS_CORE_PATH.'/ClassLoader/ClassLoader.class.php');
$classLoader = new \Cx\Core\ClassLoader\ClassLoader(ASCMS_DOCUMENT_ROOT);

require_once(ASCMS_CORE_PATH.'/Env.class.php');
\Env::set('ClassLoader', $classLoader);

require_once(ASCMS_CORE_PATH.'/validator.inc.php');
require_once(ASCMS_CORE_PATH.'/database.php');
require_once(ASCMS_MODULE_PATH.'/knowledge/lib/databaseError.class.php');

//require_once '../../lib/CSRF.php';
// Temporary fix until all GET operation requests will be replaced by POSTs
//CSRF::setFrontendMode();

require_once(ASCMS_LIBRARY_PATH.'/PEAR/HTML/Template/Sigma/Sigma.php');
require_once(ASCMS_LIBRARY_PATH.'/adodb/adodb.inc.php');
$objDb = getDatabaseObject($errorMsg);
$objDatabase = &$objDb;

require_once(ASCMS_MODULE_PATH.'/knowledge/lib/search.php');
$search = new Search();
$search->performSearch();
die();
