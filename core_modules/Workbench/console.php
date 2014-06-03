<?php
/**
 * This is a temporary wrapper-script to access Workbench from console.
 * The nice way for this would be to directly access Cx from console like
 * > cx Workbench ....
 * @author Michael Ritter <michael.ritter@comvation.com>
 */

$rootDir = dirname(dirname(__DIR__));

include($rootDir . '/core/Core/init.php');

// Uncomment the following line if you want to debug workbench commandline script
//\DBG::activate(DBG_PHP);

// This loads Contrexx in CLI mode
$cx = init();

new \Cx\Core_Modules\Workbench\Model\Entity\ConsoleInterface($argv, $cx);
