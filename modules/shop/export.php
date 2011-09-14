<?php

/**
 * The Shop
 * @copyright   CONTREXX CMS - COMVATION AG
 * @package     contrexx
 * @subpackage  module_shop
 * @todo        Edit PHP DocBlocks!
 */

/**
 * @ignore
 */
require_once dirname(__FILE__).'/../../config/configuration.php';
require_once dirname(__FILE__).'/../../core/API.php';

$csvCreator = new CsvCreator(
    contrexx_input2raw($_REQUEST['content']),
    contrexx_input2raw($_REQUEST['name']),
    contrexx_input2raw($_REQUEST['type']));
$csvCreator->createOutput();
$csvCreator->send();
