<?php

use \Cx\Update\UpdatePageEventListener as PageEventListener;

require_once(UPDATE_PATH . '/core/UpdatePageEventListener.class.php');

$_DBCONFIG   = \Env::get('dbconfig');
$doctrineDir = ASCMS_LIBRARY_PATH . '/doctrine/';
require_once(UPDATE_PATH . '/lib/FRAMEWORK/DBG/DoctrineSQLLogger.class.php');

$config = new \Doctrine\ORM\Configuration();

$cache = new \Doctrine\Common\Cache\ArrayCache();
$config->setMetadataCacheImpl($cache);
$config->setQueryCacheImpl($cache);

$config->setProxyDir(ASCMS_MODEL_PROXIES_PATH);
$config->setProxyNamespace('Cx\Model\Proxies');
$config->setAutoGenerateProxyClasses(false);

$connection = new \PDO(
    'mysql:dbname=' . $_DBCONFIG['database'] . ';' . (!empty($_DBCONFIG['charset']) ? 'charset=' . $_DBCONFIG['charset'] . ';' : '') . 'host='.$_DBCONFIG['host'],
    $_DBCONFIG['user'],
    $_DBCONFIG['password'],
    array(
        // Setting the connection character set in the DSN (see below new \PDO()) prior to PHP 5.3.6 did not work.
        // We will have to manually do it by executing the SET NAMES query when connection to the database.
        \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES '.$_DBCONFIG['charset'],
    )
);
$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

$connectionOptions = array(
    'pdo' => $connection,
);

$evm = new \Doctrine\Common\EventManager();

$chainDriverImpl = new \Doctrine\ORM\Mapping\Driver\DriverChain();
$driverImpl = new \Doctrine\ORM\Mapping\Driver\YamlDriver(ASCMS_MODEL_PATH.'/yml');
$chainDriverImpl->addDriver($driverImpl, 'Cx\Model');
$driverImpl = new \Doctrine\ORM\Mapping\Driver\YamlDriver(ASCMS_CORE_PATH.'/ContentManager/Model/Yaml');
$chainDriverImpl->addDriver($driverImpl, 'Cx\Core\ContentManager');

//loggable stuff
$loggableDriverImpl = $config->newDefaultAnnotationDriver(array(
    UPDATE_CORE,
    $doctrineDir.'Gedmo/Loggable/Entity', // Document for ODM
));
$chainDriverImpl->addDriver($loggableDriverImpl, 'Gedmo\Loggable');

$loggableListener = new \Cx\Update\core\LoggableListener();
$evm->addEventSubscriber($loggableListener);
\Env::set('loggableListener', $loggableListener);

//tree stuff
$treeListener = new \Gedmo\Tree\TreeListener();
$evm->addEventSubscriber($treeListener);
$config->setMetadataDriverImpl($chainDriverImpl);

//table prefix
$prefixListener = new \DoctrineExtension\TablePrefixListener($_DBCONFIG['tablePrefix']);
$evm->addEventListener(\Doctrine\ORM\Events::loadClassMetadata, $prefixListener);

//page listener for unique slugs
$pageListener = new PageEventListener();
$evm->addEventListener(\Doctrine\ORM\Events::preUpdate, $pageListener);
$evm->addEventListener(\Doctrine\ORM\Events::onFlush, $pageListener);
$evm->addEventListener(\Doctrine\ORM\Events::postPersist, $pageListener);
$evm->addEventListener(\Doctrine\ORM\Events::preRemove, $pageListener);

$config->setSqlLogger(new \Cx\Lib\DBG\DoctrineSQLLogger());

$em = \Doctrine\ORM\EntityManager::create($connectionOptions, $config, $evm);

//resolve enum, set errors
$conn = $em->getConnection();
$conn->setCharset($_DBCONFIG['charset']); 
$conn->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
$conn->getDatabasePlatform()->registerDoctrineTypeMapping('set', 'string');

Env::setEm($em);
