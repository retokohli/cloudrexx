<?php

/**
 * The Shop
 * @copyright   CONTREXX CMS - COMVATION AG
 * @package     contrexx
 * @subpackage  module_shop
 * @todo        Edit PHP DocBlocks!
 */

$csvCreator = new CsvCreator(
    contrexx_input2raw($_REQUEST['content']),
    contrexx_input2raw($_REQUEST['name']),
    contrexx_input2raw($_REQUEST['type']));
$csvCreator->createOutput();
$csvCreator->send();
