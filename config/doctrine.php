<?php

use Doctrine\Common\Util\Debug as DoctrineDebug;

require_once('configuration.php');
require_once(ASCMS_CORE_PATH.'/Env.class.php');

$doctrineDir = ASCMS_LIBRARY_PATH.'/doctrine/';

require_once $doctrineDir.'vendor/doctrine-common/lib/Doctrine/Common/ClassLoader.php';

$classLoader = new \Doctrine\Common\ClassLoader('Doctrine\ORM', realpath($doctrineDir));
$classLoader->register();
$classLoader = new \Doctrine\Common\ClassLoader('Doctrine\DBAL', realpath($doctrineDir.'/vendor/doctrine-dbal/lib'));
$classLoader->register();
$classLoader = new \Doctrine\Common\ClassLoader('Doctrine\Common', realpath($doctrineDir.'/vendor/doctrine-common/lib'));
$classLoader->register();
$classLoader = new \Doctrine\Common\ClassLoader('Symfony', realpath($doctrineDir.'/vendor'));
$classLoader->register();
$classLoader = new \Doctrine\Common\ClassLoader('Cx\Model', ASCMS_MODEL_PATH.'/entities');
$classLoader->register();
$classLoader = new \Doctrine\Common\ClassLoader('Cx\Model\Proxies', ASCMS_MODEL_PROXIES_PATH);
$classLoader->register();

$classLoader = new \Doctrine\Common\ClassLoader('Gedmo', $doctrineDir);
$classLoader->register();

$config = new \Doctrine\ORM\Configuration();
$config->setMetadataCacheImpl(new \Doctrine\Common\Cache\ArrayCache);
//$config->setMetadataDriverImpl($driverImpl);

$config->setProxyDir(ASCMS_MODEL_PROXIES_PATH);
$config->setProxyNamespace('Cx\Model\Proxies');
$config->setAutoGenerateProxyClasses(true /*dev setting*/);

$connectionOptions = array(
    'driver' => 'pdo_mysql',
    'user' => $_DBCONFIG['user'],
    'password' => $_DBCONFIG['password'],
    'host' => $_DBCONFIG['host'],
    'dbname' => $_DBCONFIG['database']
                           );
/*$connectionOptions = array(
    'driver' => 'pdo_sqlite',
    'path' => 'database.sqlite'
);*/

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
$loggableListener->setUsername('currently_loggedin_user');
// in real world app the username should be loaded from session, example:
// Session::getInstance()->read('user')->getUsername();
$evm->addEventSubscriber($loggableListener);

//tree stuff
$treeListener = new \Gedmo\Tree\TreeListener();
$evm->addEventSubscriber($treeListener);
$config->setMetadataDriverImpl($chainDriverImpl);

//$config->setSqlLogger(new Doctrine\DBAL\Logging\EchoSQLLogger());

$em = \Doctrine\ORM\EntityManager::create($connectionOptions, $config, $evm);

//resolve enum, set errors
$conn = $em->getConnection();
$conn->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
$conn->getDatabasePlatform()->registerDoctrineTypeMapping('set', 'string');

Env::setEm($em);


