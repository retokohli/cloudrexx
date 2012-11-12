<?php

$documentRoot = dirname(dirname(__FILE__));

include($documentRoot.'/config/settings.php');                      // needed for doctrine.php
include($documentRoot.'/config/configuration.php');
include($documentRoot.'/core/ClassLoader/ClassLoader.class.php');

$customizing = null;
if (isset($_CONFIG['useCustomizings']) && $_CONFIG['useCustomizings'] == 'on') {
// TODO: webinstaller check: has ASCMS_CUSTOMIZING_PATH already been defined in the installation process?
    $customizing = ASCMS_CUSTOMIZING_PATH;
}

$cl = new \Cx\Core\ClassLoader\ClassLoader($documentRoot, true, $customizing);
\Env::set('ClassLoader', $cl);
$cl->loadFile('../../core/API.php');
include('../../config/doctrine.php');


include('PHPUnit/phpunit.php');