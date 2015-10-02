<?php
/**
 * This file is used so index.php works under PHP 5.2 (namespaces!)
 * @author <michael.ritter@comvation.com>
 */
require_once($basePath.'/../core/Core/Controller/Cx.class.php');
require_once($basePath.'/../core/ClassLoader/ClassLoader.class.php');
require_once($basePath.'/InstallerCx.class.php');

$installerCx = new \InstallerCx($basePath);

$cl = new \Cx\Core\ClassLoader\ClassLoader($installerCx, false);
$cl->loadFile($basePath.'/../core/Env.class.php');
$cl->loadFile($basePath.'/../lib/FRAMEWORK/DBG/DBG.php');
\Env::set('cx', $installerCx);
