<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$_CONFIG = null;
$rootDir = dirname(dirname(__DIR__));

include($rootDir . '/config/configuration.php');
include($rootDir . '/config/settings.php');

require_once $rootDir . '/core/ClassLoader/ClassLoader.class.php';
$cl = new \Cx\Core\ClassLoader\ClassLoader($rootDir);
\Env::set('cl', $cl);

new \Cx\Core_Modules\Workbench\Model\Entity\ConsoleInterface($argv);
