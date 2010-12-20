<?php
require_once dirname(__FILE__).'/../../config/configuration.php';
require_once dirname(__FILE__).'/../../core/API.php';

$csvCreator = new CsvCreator($_REQUEST['content'],$_REQUEST['name'],$_REQUEST['type']);
$csvCreator->createOutput();
$csvCreator->send();
?>
