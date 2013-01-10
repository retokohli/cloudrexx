<?php

use \Cx\Update\UpdatePageEventListener as PageEventListener;

require_once(UPDATE_PATH . '/core/UpdatePageEventListener.class.php');

$_DBCONFIG   = \Env::get('dbconfig');
$doctrineDir = ASCMS_LIBRARY_PATH . '/doctrine/';

//require_once $doctrineDir.'vendor/doctrine-common/lib/Doctrine/Common/ClassLoader.php';
//use \Doctrine\Common\ClassLoader as ClassLoader;
require_once(ASCMS_CORE_PATH.'/ClassLoader.class.php');
use \Cx\ClassLoader as ClassLoader;

$classLoader = new ClassLoader('Doctrine\ORM', realpath($doctrineDir));
$classLoader->register();
$classLoader = new ClassLoader('Doctrine\DBAL', realpath($doctrineDir.'/vendor/doctrine-dbal/lib'));
$classLoader->register();
$classLoader = new ClassLoader('Doctrine\Common', realpath($doctrineDir.'/vendor/doctrine-common/lib'));
$classLoader->register();
$classLoader = new ClassLoader('Symfony', realpath($doctrineDir.'/vendor'));
$classLoader->register();
$classLoader = new ClassLoader('Cx\Model', ASCMS_MODEL_PATH.'/entities');
$classLoader->register();
$classLoader = new ClassLoader('Cx\Model\Proxies', ASCMS_MODEL_PROXIES_PATH);
$classLoader->register();

$classLoader = new ClassLoader('DoctrineExtension', ASCMS_MODEL_PATH.'/extensions');
$classLoader->register();

$classLoader = new ClassLoader('Gedmo\Loggable\Entity', ASCMS_MODEL_PATH.'/entities');
$classLoader->register();

$classLoader = new ClassLoader('Gedmo', $doctrineDir);
$classLoader->register();

require_once(UPDATE_PATH . '/lib/FRAMEWORK/DBG/DoctrineSQLLogger.class.php');

$config = new \Doctrine\ORM\Configuration();

$cache = new \Doctrine\Common\Cache\ArrayCache();
$config->setMetadataCacheImpl($cache);
$config->setQueryCacheImpl($cache);

$config->setProxyDir(ASCMS_MODEL_PROXIES_PATH);
$config->setProxyNamespace('Cx\Model\Proxies');
$config->setAutoGenerateProxyClasses(false);

$connection = new \PDO(
    'mysql:dbname=' . $_DBCONFIG['database'] . ';host='.$_DBCONFIG['host'],
    $_DBCONFIG['user'],
    $_DBCONFIG['password']
);
$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

$connectionOptions = array(
    'pdo' => $connection,
);

$evm = new \Doctrine\Common\EventManager();

$chainDriverImpl = new \Doctrine\ORM\Mapping\Driver\DriverChain();
$driverImpl = new \Doctrine\ORM\Mapping\Driver\YamlDriver(ASCMS_MODEL_PATH.'/yml');
$chainDriverImpl->addDriver($driverImpl, 'Cx\Model');

//loggable stuff
$loggableDriverImpl = $config->newDefaultAnnotationDriver(
    $doctrineDir.'Gedmo/Loggable/Entity' // Document for ODM
);
$chainDriverImpl->addDriver($loggableDriverImpl, 'Gedmo\Loggable');

$loggableListener = new \Gedmo\Loggable\LoggableListener();
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
