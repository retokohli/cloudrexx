<?php
/**
 * Db Class
 *
 * Database connection handler
 * @copyright   Comvation AG
 * @author      Michael Ritter <michael.ritter@comvation.com>
 * @package     contrexx
 * @subpackage  core_db
 * @todo        make class a pure library
 */

namespace {
    /**
     * Factory callback for AdoDB NewConnection
     * 
     * This is in global namespace for backwards compatibility to PHP 5.3
     * $ADODB_NEWCONNECTION = array($this, 'adodbPdoConnectionFactory');
     * leads to a "function name must be a string"
     * @deprecated Use Doctrine!
     * @return \Cx\Core\Model\CustomAdodbPdo 
     */
    function cxAdodbPdoConnectionFactory() {
        $obj = new \Cx\Core\Model\CustomAdodbPdo(\Env::get('pdo'));
        return $obj;
    }
}

namespace Cx\Core\Model {

    /**
     * DB Exception
     *
     * @copyright   Comvation AG
     * @author      Michael Ritter <michael.ritter@comvation.com>
     * @package     contrexx
     * @subpackage  core_db
     */
    class DbException extends \Exception {}

    /**
     * Db Class
     *
     * Database connection handler
     * @copyright   Comvation AG
     * @author      Michael Ritter <michael.ritter@comvation.com>
     * @package     contrexx
     * @subpackage  core_db
     */
    class Db {
        
        /**
         * Contrexx instance
         * @var \Cx\Core\Core\Controller\Cx
         */
        protected $cx = null;
        
        /**
         * PDO instance
         * @var \PDO
         */
        protected $pdo = null;
        
        /**
         * AdoDB instance
         * @var \ADONewConnection 
         */
        protected $adodb = null;
        
        /**
         * Doctrine entity manager instance
         * @var \Doctrine\ORM\EntityManager 
         */
        protected $em = null;
        
        /**
         * Doctrine LoggableListener instance
         * @var \Gedmo\Loggable\LoggableListener 
         */
        protected $loggableListener = null;
        
        /*
         * db instance
         * @var \Cx\Core\Model\Model\Entity/Db
         * */
        protected $db;
        
         /*
         * db user instance
         * @var \Cx\Core\Model\Model\Entity/DbUser
         * */
        protected $dbUser;

        /**
         * Creates a new instance of the database connection handler
         * @param \Cx\Core\Model\Model\Entity\Db $db Database connection details
         * @param \Cx\Core\Model\Model\Entity\DbUser $dbUser Database user details
         */
        public function __construct(\Cx\Core\Model\Model\Entity\Db $db, \Cx\Core\Model\Model\Entity\DbUser $dbUser) {
            $this->db = $db;
            $this->dbUser = $dbUser;
        }
        
        /**
         * Sets the username for loggable listener
         * @param string $username Username data as string
         */
        public function setUsername($username) {
            $this->loggableListener->setUsername($username);
        }

        /**
         * Initializes the PDO connection
         * @return \PDO PDO connection
         */
        public function getPdoConnection() {
            if ($this->pdo) {
                return $this->pdo;
            }
            $objDateTimeZone = new \DateTimeZone($this->db->getTimezone());
            $objDateTime = new \DateTime('now', $objDateTimeZone);
            $offset = $objDateTimeZone->getOffset($objDateTime);
            $offsetHours = floor(abs($offset)/3600); 
            $offsetMinutes = round((abs($offset)-$offsetHours*3600) / 60); 
            $offsetString = ($offset > 0 ? '+' : '-').($offsetHours < 10 ? '0' : '').$offsetHours.':'.($offsetMinutes < 10 ? '0' : '').$offsetMinutes;

            $dbCharSet = $this->db->getCharset();    
            $this->pdo = new \PDO(
                'mysql:dbname=' . $this->db->getName() . ';charset=' . $dbCharSet . ';host=' . preg_replace('/:/', ';port=', $this->db->getHost()),
                $this->dbUser->getName(),
                $this->dbUser->getPassword(),
                array(
                    // Setting the connection character set in the DSN (see below new \PDO()) prior to PHP 5.3.6 did not work.
                    // We will have to manually do it by executing the SET NAMES query when connection to the database.
                    \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES '.$dbCharSet,
                    \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET time_zone = \'' . $offsetString . '\'',
                )
            );
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);
            \Env::set('pdo', $this->pdo);
            return $this->pdo;
        }

        /**
         * Returns the AdoDB connection
         * @deprecated Use Doctrine (getEntityManager()) instead
         * @global string $ADODB_FETCH_MODE
         * @return \ADONewConnection 
         */
        public function getAdoDb() {
            if ($this->adodb) {
                return $this->adodb;
            }
            // Make sure, \Env::get('pdo') is set
            $this->getPdoConnection();

            global $ADODB_FETCH_MODE, $ADODB_NEWCONNECTION;

            // open db connection
            \Env::get('ClassLoader')->loadFile(ASCMS_LIBRARY_PATH.'/adodb/adodb.inc.php');
            $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
            $ADODB_NEWCONNECTION = 'cxAdodbPdoConnectionFactory';
            $this->adodb = \ADONewConnection('pdo');

            $errorNo = $this->adodb->ErrorNo();
            if ($errorNo != 0) {
                if ($errorNo == 1049) {
                    throw new DbException('The database is unavailable');
                } else {
                    throw new DbException($this->adodb->ErrorMsg() . '<br />');
                }
                unset($this->adodb);
                return false;
            }
            return $this->adodb;
        }
        
        /**
         * Adds YAML directories to entity manager
         * @param array $paths List of paths
         */
        public function addSchemaFileDirectories(array $paths) {
            if (!$this->em) {
                $this->getEntityManager();
            }
            
            $drivers = $this->em->getConfiguration()->getMetadataDriverImpl()->getDrivers();
            $drivers['Cx']->addPaths($paths);
        }

        /**
         * Returns the doctrine entity manager
         * @return \Doctrine\ORM\EntityManager 
         */
        public function getEntityManager() {
            if ($this->em) {
                return $this->em;
            }

            global $objCache;

            $config = new \Doctrine\ORM\Configuration();

            $userCacheEngine = $objCache->getUserCacheEngine();
            if (!$objCache->getUserCacheActive()) {
                $userCacheEngine = \Cx\Core_Modules\Cache\Controller\Cache::CACHE_ENGINE_OFF;
            }

            $arrayCache = new \Doctrine\Common\Cache\ArrayCache();
            switch ($userCacheEngine) {
                case \Cx\Core_Modules\Cache\Controller\Cache::CACHE_ENGINE_APC:
                    $cache = new \Doctrine\Common\Cache\ApcCache();
                    $cache->setNamespace($this->db->getName() . '.' . $this->db->getTablePrefix());
                    break;
                case \Cx\Core_Modules\Cache\Controller\Cache::CACHE_ENGINE_MEMCACHE:
                    $memcache = $objCache->getMemcache();
                    if ($memcache instanceof \Memcache) {
                        $cache = new \Doctrine\Common\Cache\MemcacheCache();
                        $cache->setMemcache($memcache);
                    } elseif ($memcache instanceof \Memcached) {
                        $cache = new \Cx\Core_Modules\Cache\Controller\Doctrine\CacheDriver\MemcachedCache();
                        $cache->setMemcache($memcache);
                    }
                    $cache->setNamespace($this->db->getName() . '.' . $this->db->getTablePrefix());
                    break;
                case \Cx\Core_Modules\Cache\Controller\Cache::CACHE_ENGINE_XCACHE:
                    $cache = new \Doctrine\Common\Cache\XcacheCache();
                    $cache->setNamespace($this->db->getName() . '.' . $this->db->getTablePrefix());
                    break;
                case \Cx\Core_Modules\Cache\Controller\Cache::CACHE_ENGINE_FILESYSTEM:
                    $cache = new \Cx\Core_Modules\Cache\Controller\Doctrine\CacheDriver\FileSystemCache(ASCMS_CACHE_PATH);                    
                    break;
                default:
                    $cache = $arrayCache;
                    break;
            }
            \Env::set('cache', $cache);
            $config->setResultCacheImpl($cache);
            $config->setMetadataCacheImpl($arrayCache);
            $config->setQueryCacheImpl($cache);

            $config->setProxyDir(ASCMS_MODEL_PROXIES_PATH);
            $config->setProxyNamespace('Cx\Model\Proxies');
            
            /**
             * This should be set to true if workbench is present and active.
             * Just checking for workbench.config is not really a good solution.
             * Since ConfigurationFactory used by EM caches auto generation
             * config value, there's no possibility to set this later.
             */
            $config->setAutoGenerateProxyClasses(file_exists(ASCMS_DOCUMENT_ROOT.'/workbench.config'));
            
            $connectionOptions = array(
                'pdo' => $this->getPdoConnection(),
            );

            $evm = new \Doctrine\Common\EventManager();

            $chainDriverImpl = new \Doctrine\ORM\Mapping\Driver\DriverChain();
            $driverImpl = new \Cx\Core\Model\Controller\YamlDriver(array(
                ASCMS_CORE_PATH.'/Core'.'/Model/Yaml',             // Component YAML files
            ));
            $chainDriverImpl->addDriver($driverImpl, 'Cx');

            //loggable stuff
            $loggableDriverImpl = $config->newDefaultAnnotationDriver(
                ASCMS_LIBRARY_PATH.'/doctrine/Gedmo/Loggable/Entity' // Document for ODM
            );
            $chainDriverImpl->addDriver($loggableDriverImpl, 'Gedmo\Loggable');

            $this->loggableListener = new \Gedmo\Loggable\LoggableListener();
            $this->loggableListener->setUsername('currently_loggedin_user');
            // in real world app the username should be loaded from session, example:
            // Session::getInstance()->read('user')->getUsername();
            $evm->addEventSubscriber($this->loggableListener);

            //tree stuff
            $treeListener = new \Gedmo\Tree\TreeListener();
            $evm->addEventSubscriber($treeListener);
            $config->setMetadataDriverImpl($chainDriverImpl);

            //table prefix
            $prefixListener = new \DoctrineExtension\TablePrefixListener($this->db->getTablePrefix());
            $evm->addEventListener(\Doctrine\ORM\Events::loadClassMetadata, $prefixListener);

            $config->setSqlLogger(new \Cx\Lib\DBG\DoctrineSQLLogger());

            $em = \Cx\Core\Model\Controller\EntityManager::create($connectionOptions, $config, $evm);

            //resolve enum, set errors
            $conn = $em->getConnection();
            $conn->setCharset($this->db->getCharset()); 
            $conn->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
            $conn->getDatabasePlatform()->registerDoctrineTypeMapping('set', 'string');
            
            $this->em = $em;
            return $this->em;
        }
    }
}
