<?php
/**
 * Search script
 *
 * This script is standalone because otherwise it would be too slow.
 * @author Stefan Heinemann <sh@comvation.com>
 * @copyright Comvation AG <info@comvation.com>
 */

define("_DEBUG", 0);

if (_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    if(include_once('../../lib/DBG.php')){
        $objDBG = new DBG(true); //pass false to disable firephp and enable logging to a file (see following DBG::setup)
        $objDBG->setup('dbg.log', 'w');
        $objDBG->enable_all();
    }
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}


require_once "../../config/configuration.php";
require_once "../../config/set_constants.php";
require_once "../../core/validator.inc.php";
require_once "../../core/database.php";
require_once "lib/databaseError.class.php";

require_once ASCMS_LIBRARY_PATH.'/PEAR/HTML/Template/Sigma/Sigma.php';
require_once ASCMS_LIBRARY_PATH.'/adodb/adodb.inc.php';
$objDb = getDatabaseObject($errorMsg);
$objDatabase = &$objDb;
include("lib/search.php");
$search = new Search();
$search->performSearch();
die();
