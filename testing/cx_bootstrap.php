<?php

$documentRoot = dirname(dirname(__FILE__));

require_once($documentRoot.'/core/Core/init.php');
$cx = init('minimal');

include('PHPUnit/phpunit.php');
