<?php

use Doctrine\Common\Util\Debug as DoctrineDebug;
use Cx\Model\Events\PageEventListener as PageEventListener;

require_once(ASCMS_CORE_PATH.'/Env.class.php');
require_once(ASCMS_MODEL_PATH.'/events/PageEventListener.class.php');

$doctrineDir = ASCMS_LIBRARY_PATH.'/doctrine/';

//require_once $doctrineDir.'vendor/doctrine-common/lib/Doctrine/Common/ClassLoader.php';
//use \Doctrine\Common\ClassLoader as ClassLoader;
require_once ASCMS_CORE_PATH.'/ClassLoader.class.php';
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

$config = new \Doctrine\ORM\Configuration();
$config->setMetadataCacheImpl(new \Doctrine\Common\Cache\ArrayCache);
//$config->setMetadataCacheImpl(new \Doctrine\Common\Cache\ApcCache);
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

//table prefix
$prefixListener = new \DoctrineExtension\TablePrefixListener($_DBCONFIG['tablePrefix']);
$evm->addEventListener(\Doctrine\ORM\Events::loadClassMetadata, $prefixListener);

//page listener for unique slugs
$pageListener = new PageEventListener();
$evm->addEventListener(\Doctrine\ORM\Events::onFlush, $pageListener);

//$config->setSqlLogger(new Doctrine\DBAL\Logging\EchoSQLLogger());

$em = \Doctrine\ORM\EntityManager::create($connectionOptions, $config, $evm);

//resolve enum, set errors
$conn = $em->getConnection();
$conn->setCharset($_DBCONFIG['charset']); 
$conn->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
$conn->getDatabasePlatform()->registerDoctrineTypeMapping('set', 'string');

Env::setEm($em);


