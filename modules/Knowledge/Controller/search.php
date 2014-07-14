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
require_once dirname(dirname(dirname(dirname(__FILE__)))).'/core/Core/init.php';
$cx = init('minimal');
$objDatabase = $cx->getDb()->getAdoDb();

\Env::get('ClassLoader')->loadFile(ASCMS_MODULE_PATH.'/Knowledge/Controller/DatabaseError.class.php');

//require_once '../../lib/CSRF.php';
// Temporary fix until all GET operation requests will be replaced by POSTs
//CSRF::setFrontendMode();

if (!defined('FRONTEND_LANG_ID') && !empty($_GET['lang'])) {
    define('FRONTEND_LANG_ID', \FWLanguage::getLanguageIdByCode($_GET['lang']));
}

$search = new \Cx\Modules\Knowledge\Controller\Search();
$search->performSearch();
die();
