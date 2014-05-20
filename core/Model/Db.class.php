<?php
/**
 * Db Class
 *
 * Database connection handler
 * @copyright   Comvation AG
 * @author      Michael Ritter <michael.ritter@comvation.com>
 * @package     contrexx
 * @subpackage  core_db
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
         * @param \Cx\Core\Core\Controller\Cx $cx Main class
         */                         
        public function __construct(\Cx\Core\Model\Model\Entity\Db $db, \Cx\Core\Model\Model\Entity\DbUser $dbUser, $cacheEngine) {
            $this->cacheEngine = $cacheEngine;
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
         * @global array $_DBCONFIG Database configuration
         * @global array $_CONFIG Configuration
         * @return \PDO PDO connection
         */
        public function getPdoConnection() {
            global $_DBCONFIG, $_CONFIG;

            if ($this->pdo) {
                return $this->pdo;
            }
            $objDateTimeZone = new \DateTimeZone($this->db->getTimezone());
            $objDateTime = new \DateTime('now', $objDateTimeZone);
            $offset = $objDateTimeZone->getOffset($objDateTime);
            $offsetHours = round(abs($offset)/3600); 
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
         * @global array $_DBCONFIG Database configuration
         * @return \Doctrine\ORM\EntityManager 
         */
        public function getEntityManager() {
            if ($this->em) {
                return $this->em;
            }

            //global $_DBCONFIG;

            $config = new \Doctrine\ORM\Configuration();
            switch ($this->cacheEngine) {
                case \Cx\Core\Core\Controller\Cx::CACHE_ENGINE_APC:
                    $cache = new \Doctrine\Common\Cache\ApcCache();
                    break;
                case \Cx\Core\Core\Controller\Cx::CACHE_ENGINE_MEMCACHE:
                    $cache = new \Doctrine\Common\Cache\MemcacheCache();
                    $memcache = \Env::get('memcache');
                    $cache->setMemcache($memcache);
                    break;
                case \Cx\Core\Core\Controller\Cx::CACHE_ENGINE_XCACHE:
                    $cache = new \Doctrine\Common\Cache\XcacheCache();
                    break;
                default:
                    $cache = new \Doctrine\Common\Cache\ArrayCache();
                    break;
            }
            
            $config->setResultCacheImpl($cache);
            $config->setMetadataCacheImpl($cache);
            $config->setQueryCacheImpl($cache);

            $config->setProxyDir(ASCMS_MODEL_PROXIES_PATH);
            $config->setProxyNamespace('Cx\Model\Proxies');
            $config->setAutoGenerateProxyClasses(false);
            
            $connectionOptions = array(
                'pdo' => $this->getPdoConnection(),
            );

            $evm = new \Doctrine\Common\EventManager();

            $chainDriverImpl = new \Doctrine\ORM\Mapping\Driver\DriverChain();
            $driverImpl = new \Doctrine\ORM\Mapping\Driver\YamlDriver(array(
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

            $em = \Doctrine\ORM\EntityManager::create($connectionOptions, $config, $evm);

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
