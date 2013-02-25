<?php
$_CONFIG = null;
include('../config/configuration.php');

require_once ASCMS_CORE_PATH.'/ClassLoader/ClassLoader.class.php';
$cl = new \Cx\Core\ClassLoader\ClassLoader(ASCMS_DOCUMENT_ROOT);

include('../config/doctrine.php');

$helpers = array(
    'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($em->getConnection()),
    'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($em)
);