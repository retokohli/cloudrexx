<?php
/**
 * Search script
 *
 * This script is standalone because otherwise it would be too slow.
 * @author Stefan Heinemann <sh@comvation.com>
 * @copyright Comvation AG <info@comvation.com>
 */

include_once('../../lib/DBG.php');
require_once "../../config/configuration.php";
require_once "../../config/set_constants.php";
require_once "../../core/validator.inc.php";
require_once "../../core/database.php";
require_once "lib/databaseError.class.php";

//require_once '../../lib/CSRF.php';
// Temporary fix until all GET operation requests will be replaced by POSTs
//CSRF::setFrontendMode();

require_once ASCMS_LIBRARY_PATH.'/PEAR/HTML/Template/Sigma/Sigma.php';
require_once ASCMS_LIBRARY_PATH.'/adodb/adodb.inc.php';
$objDb = getDatabaseObject($errorMsg);
$objDatabase = &$objDb;
include("lib/search.php");
$search = new Search();
$search->performSearch();
die();
