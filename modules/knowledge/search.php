<?php

/**
 * Search script
 *
 * This script is standalone because otherwise it would be too slow.
 * @author      Stefan Heinemann <info@comvation.com>
 * @copyright   COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_knowledge
 */

/**
 * Search script
 *
 * This script is standalone because otherwise it would be too slow.
 * @author      Stefan Heinemann <info@comvation.com>
 * @copyright   COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_knowledge
 */

global $objDatabase;
require_once dirname(__FILE__).'/../../core/Core/init.php';
$cx = init('minimal');
$objDatabase = $cx->getDb()->getAdoDb();

require_once(ASCMS_MODULE_PATH.'/knowledge/lib/databaseError.class.php');

//require_once '../../lib/CSRF.php';
// Temporary fix until all GET operation requests will be replaced by POSTs
//CSRF::setFrontendMode();

if (!defined('FRONTEND_LANG_ID') && !empty($_GET['lang'])) {
    define('FRONTEND_LANG_ID', \FWLanguage::getLanguageIdByCode($_GET['lang']));
}

require_once(ASCMS_MODULE_PATH.'/knowledge/lib/search.php');
$search = new Search();
$search->performSearch();
die();
