<?php
global $_DBCONFIG, $loggableListener;

use Doctrine\Common\Util\Debug as DoctrineDebug;
use Cx\Core\ContentManager\Model\Doctrine\Event\PageEventListener as PageEventListener;

require_once(ASCMS_CORE_PATH.'/Env.class.php');

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
/*$classLoader = new ClassLoader('Cx\Model', ASCMS_MODEL_PATH.'/entities');
$classLoader->register();*/
$classLoader = new ClassLoader('Cx\Model\Proxies', ASCMS_MODEL_PROXIES_PATH);
$classLoader->register();

$classLoader = new ClassLoader('DoctrineExtension', ASCMS_MODEL_PATH.'/extensions');
$classLoader->register();

$classLoader = new ClassLoader('Gedmo\Loggable\Entity', ASCMS_MODEL_PATH.'/entities');
$classLoader->register();

$classLoader = new ClassLoader('Gedmo', $doctrineDir);
$classLoader->register();

$config = new \Doctrine\ORM\Configuration();

$cache = new \Doctrine\Common\Cache\ArrayCache();
$config->setMetadataCacheImpl($cache);
$config->setQueryCacheImpl($cache);

$config->setProxyDir(ASCMS_MODEL_PROXIES_PATH);
$config->setProxyNamespace('Cx\Model\Proxies');
$config->setAutoGenerateProxyClasses(false);

$connection = new \PDO(
    'mysql:dbname=' . $_DBCONFIG['database'] . ';charset=' . $_DBCONFIG['charset'] . ';host='.$_DBCONFIG['host'],
    $_DBCONFIG['user'],
    $_DBCONFIG['password']
);
$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

$connectionOptions = array(
    'pdo' => $connection,
);

$evm = new \Doctrine\Common\EventManager();

$chainDriverImpl = new \Doctrine\ORM\Mapping\Driver\DriverChain();
$driverImpl = new \Doctrine\ORM\Mapping\Driver\YamlDriver(array(
    ASCMS_MODEL_PATH.'/yml',
    ASCMS_CORE_PATH.'/Component'.'/Model/Doctrine/Yaml',
    ASCMS_CORE_PATH.'/ContentManager'.'/Model/Doctrine/Yaml',
));
$chainDriverImpl->addDriver($driverImpl, 'Cx');

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
$evm->addEventListener(\Doctrine\ORM\Events::prePersist,  $pageListener);
$evm->addEventListener(\Doctrine\ORM\Events::postPersist, $pageListener);
$evm->addEventListener(\Doctrine\ORM\Events::preUpdate,   $pageListener);
$evm->addEventListener(\Doctrine\ORM\Events::postUpdate,  $pageListener);
$evm->addEventListener(\Doctrine\ORM\Events::preRemove,   $pageListener);
$evm->addEventListener(\Doctrine\ORM\Events::postRemove,  $pageListener);
$evm->addEventListener(\Doctrine\ORM\Events::onFlush,     $pageListener);

$config->setSqlLogger(new \Cx\Lib\DBG\DoctrineSQLLogger());

$em = \Doctrine\ORM\EntityManager::create($connectionOptions, $config, $evm);

//resolve enum, set errors
$conn = $em->getConnection();
$conn->setCharset($_DBCONFIG['charset']); 
$conn->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
$conn->getDatabasePlatform()->registerDoctrineTypeMapping('set', 'string');

Env::setEm($em);


