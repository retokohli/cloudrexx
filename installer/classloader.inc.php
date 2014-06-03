<?php
/**
 * This file is used so index.php works under PHP 5.2 (namespaces!)
 * @author <michael.ritter@comvation.com>
 */
require_once($basePath.'/../core/ClassLoader/ClassLoader.class.php');
new \Cx\Core\ClassLoader\ClassLoader($basePath, false);
