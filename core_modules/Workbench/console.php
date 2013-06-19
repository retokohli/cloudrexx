<?php

$rootDir = dirname(dirname(__DIR__));

include($rootDir . '/init.php');

// Uncomment the following line if you want to debug workbench commandline script
//\DBG::activate(DBG_PHP);

// This loads Contrexx in CLI mode
$cx = init();

new \Cx\Core_Modules\Workbench\Model\Entity\ConsoleInterface($argv, $cx);
