<?php 
namespace Cx\Core_Modules\MultiSite\Model\Entity;

class WebsiteException extends \Exception {}

class Website extends \Cx\Core\Core\Model\Entity\EntityBase {
    protected $basepath = null;
  
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $name
     */
    public $name;

    /**
     * @var string $codeBase
     */
    public $codeBase;

    /**
     * @var string $language
     */
    public $language;

    /**
     * @var integer $status
     */
    public $status;
    
    /**
     * @var integer $websiteServiceServerId
     */
    public $websiteServiceServerId;
    
    /**
     * @var Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer
     */
    public $websiteServiceServer;
       
    protected $pdo = null;
    
    protected $owner;
    
    private $websiteController;
   
    /**
     * @var string $ipAddress
     */
    public $ipAddress;
    
    /**
     * @var intiger $ownerId
     */
    public $ownerId;

    /*
     * Constructor
     * */
    public function __construct($basepath, $name, $websiteServiceServer = null, \Cx\Core_Modules\MultiSite\Model\Entity\User $userObj=null, $lazyLoad = true) {
        $this->basepath = $basepath;
        $this->name = $name;

        if ($lazyLoad) {
            return true;
        }

        $this->language = $userObj->getFrontendLanguage();
        $this->status = 0;
        $this->websiteServiceServerId = 0;
        $this->owner = $userObj;

        if ($websiteServiceServer) {
            $this->setWebsiteServiceServer($websiteServiceServer);
        }

        $this->validate();
    }

    public static function loadFromFileSystem($basepath, $name)
    {
        if (!file_exists($basepath.'/'.$name)) {
            throw new WebsiteException('No website found on path ' . $basepath . '/' . $name);
        }

        return new Website($basepath, $name);
    }
    
     /**
     * Set id
     *
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
   /**
     * Get id
     *
     * @return integer $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set codeBase
     *
     * @param string $codeBase
     */
    public function setCodeBase($codeBase)
    {
        $this->codeBase = $codeBase;
    }

    /**
     * Get codeBase
     *
     * @return string $codeBase
     */
    public function getCodeBase()
    {
        return $this->codeBase;
    }

    /**
     * Set language
     *
     * @param string $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * Get language
     *
     * @return string $language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set status
     *
     * @param integer $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Get status
     *
     * @return integer $status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set websiteServiceServerId
     *
     * @param integer $websiteServiceServerId
     */
    public function setWebsiteServiceServerId($websiteServiceServerId)
    {
        $this->websiteServiceServerId = $websiteServiceServerId;
    }

    /**
     * Get websiteServiceServerId
     *
     * @return integer $websiteServiceServerId
     */
    public function getWebsiteServiceServerId()
    {
        return $this->websiteServiceServerId;
    }
    
    /**
     * Set websiteServiceServer
     *
     * @param Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer $websiteServiceServer
     */
    public function setWebsiteServiceServer(\Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer $websiteServiceServer)
    {
        $this->websiteServiceServer = $websiteServiceServer;
        $this->setWebsiteServiceServerId($websiteServiceServer->getId());
    }

    /**
     * Get websiteServiceServer
     *
     * @return Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer $websiteServiceServer
     */
    public function getWebsiteServiceServer()
    {
        return $this->websiteServiceServer;
    }

    public function getOwner()
    {
        if (!isset($this->owner)) {
            $user = new \User();
            $this->owner = $user->getUser($this->ownerId);
        }
        return $this->owner;
    }
    
    /**
     * Creates a new website
     */
    public function setup() {
        global $_DBCONFIG, $_ARRAYLANG;

        if (\Cx\Core\Setting\Controller\Setting::getValue('websiteController')) {
            //creating object of plesk controller to call plesk API RPC
            $this->websiteController = \Cx\Core_Modules\MultiSite\Controller\PleskController::fromConfig();
            $this->websiteController->setWebspaceId(\Cx\Core\Setting\Controller\Setting::getValue('pleskWebsitesSubscriptionId'));
        } else {
            //create \Cx\Core\Model\Model\Entity\Db() object
            //initialized with default configuration
            $dbObj = new \Cx\Core\Model\Model\Entity\Db($_DBCONFIG);
            //create \Cx\Core\Model\Model\Entity\DbUser() object
            //initialized with default configuration
            $dbUserObj = new \Cx\Core\Model\Model\Entity\DbUser($_DBCONFIG); //creating Db user class object    
            //set website controller object with XampController called when used on localhost
            $this->websiteController = new \Cx\Core_Modules\MultiSite\Controller\XamppController($dbObj, $dbUserObj); 
        }
        //website name
        $websiteName = $this->getName();
        //user Email
        $websiteMail = $this->owner->getEmail(); 
        
        $websitePassword = \User::make_password(8, true);
        // language
        $lang = $this->owner->getBackendLanguage();
        $langId = \FWLanguage::getLanguageIdByCode($lang);
        
        if ($langId === false) {
            $langId = \FWLanguage::getDefaultLangId();
        }
        $isServiceServer = true;
        //check if the current server is running as the website manager
        if ($this->websiteServiceServer instanceof \Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer) {
            $isServiceServer = false;
            //hostName
            $hostname = $this->websiteServiceServer->getHostname();
            $httpAuth = array(
                'httpAuthMethod' => $this->websiteServiceServer->getHttpAuthMethod(),
                'httpAuthUsername' => $this->websiteServiceServer->getHttpAuthUsername(),
                'httpAuthPassword' => $this->websiteServiceServer->getHttpAuthPassword(),
            );        
            $params = array(
                    'command'     => 'createWebsite',
                    'userId'      => $this->owner->getId(),
                    'userEmail'   => $websiteMail,
                    'websiteName' => $websiteName,
                    'websiteId'   => $this->getId(),
                    'auth'        => \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::getAuthenticationObject($this->websiteServiceServer->getSecretKey(), $this->websiteServiceServer->getInstallationId())
                );
            $jd = new \Cx\Core\Json\JsonData();
            $resp = $jd->getJson('https://'.$hostname.'/cadmin/index.php?cmd=jsondata&object=MultiSite&act=createWebsite', $params,
             false, '', $httpAuth);
            if(!$resp || $resp->status == 'error'){
                $errMsg = isset($resp->message) ? $resp->message : '';
                throw new WebsiteException('Problem in creating website '.$errMsg);    
            }
        } else {
            $objDb = new \Cx\Core\Model\Model\Entity\Db($_DBCONFIG);
            $objDbUser = new \Cx\Core\Model\Model\Entity\DbUser();
            $this->setupDatabase($langId, $this->owner, $objDb, $objDbUser);
            $this->setupDataFolder($websiteName);
            $this->setupConfiguration($websiteName, $objDb, $objDbUser);
        }

        if(!$isServiceServer){
        // Add DNS records for new website
        $dnsRecordId = $this->websiteController->addDnsRecord('A', \Cx\Core\Setting\Controller\Setting::getValue('pleskMasterSubscriptionId'),
         $websiteName, $this->websiteServiceServer->getDefaultWebsiteIp());

        $websiteDomain = $websiteName.'.'.\Cx\Core\Setting\Controller\Setting::getValue('multiSiteDomain');
        // write mail
        \MailTemplate::init('MultiSite');
        if (!\MailTemplate::send(array(
            'section' => 'MultiSite',
            'lang_id' => $langId,
            'key' => 'createInstance',
            'to' => $websiteMail,
            'search' => array('[[WEBSITE_DOMAIN]]', '[[WEBSITE_NAME]]', '[[WEBSITE_MAIL]]', '[[WEBSITE_PASSWORD]]'),
            'replace' => array($websiteDomain, $websiteName, $websiteMail, $websitePassword),
        ))) {
// TODO: Implement proper error handler:
//       removeWebsite() must not be called from within this method.
//       Instead, in case the setup process fails, a proper exception must be thrown.
//       Then the object that executed the setup() method must handle the exception
//       and call the removeWebsite() method if required.
            //$this->removeWebsite($websiteName);
            throw new WebsiteException('Could not create website (Mail send failed)');
            }
        }
        $this->messages = $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_CREATED'];
        return 'success';
    }
    
    /*
    * function validate to validate website name
    * @param $websiteName
    * @param$websiteMail
    * */
    private function validate()
    {
        global $_ARRAYLANG, $objInit;;
        //load language file 
        $langData = $objInit->loadLanguageData('MultiSite');
        $_ARRAYLANG = array_merge($_ARRAYLANG, $langData);

        $websiteName = $this->getName();
        $websiteMail = $this->owner->getEmail();
        $unavailablePrefixesValue = explode(',',\Cx\Core\Setting\Controller\Setting::getValue('unavailablePrefixes'));
        if (in_array($websiteName, $unavailablePrefixesValue)) {
            throw new WebsiteException($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_NAME_NOT_AVAILABLE']);
        }
        if (preg_match('/[^a-z0-9]/', $websiteName)) {
            throw new WebsiteException($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_NAME_WRONG_CHARS']);
        }

        if (strlen($websiteName) < \Cx\Core\Setting\Controller\Setting::getValue('websiteNameMinLength')) {
            throw new WebsiteException(str_replace('{digits}',\Cx\Core\Setting\Controller\Setting::getValue('websiteNameMinLength'),$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_NAME_TOO_SHORT']));
        }
        if (strlen($websiteName) > \Cx\Core\Setting\Controller\Setting::getValue('websiteNameMaxLength')) {
            throw new WebsiteException(str_replace('{digits}',\Cx\Core\Setting\Controller\Setting::getValue('websiteNameMaxLength'),$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_NAME_TOO_LONG']));
        }
        // existing website
        if (file_exists(\Cx\Core\Setting\Controller\Setting::getValue('websitePath').'/'.$websiteName)) {
            throw new WebsiteException($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_ALREADY_EXISTS']);
        }
        
        /* commented as we have removed email attribute from this class
        // website with that mail
        $webRepo = new \Cx\Core_Modules\MultiSite\Model\Repository\WebsiteRepository();
        $website = $webRepo->findByMail(\Cx\Core\Setting\Controller\Setting::getValue('websitePath'), $websiteMail);
        if ($website) {
            throw new WebsiteException($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_ALREADY_EXISTS']);
        }
        */ 
    }
    
    /*
    * function setupDatabase to create database
    * and populate database with basic data
    * @param $langId language ID of the website
    * */
    private function setupDatabase($langId, $objUser, $objDb, $objDbUser){
        $objDbUser->setPassword(\User::make_password(8, true));
        $objDbUser->setName(\Cx\Core\Setting\Controller\Setting::getValue('websiteDatabaseUserPrefix').$this->id);      

        $objDb->setHost(\Cx\Core\Setting\Controller\Setting::getValue('websiteDatabaseHost'));
        $objDb->setName(\Cx\Core\Setting\Controller\Setting::getValue('websiteDatabasePrefix').$this->id);

        $websitedb = $this->initDatabase($objDb, $objDbUser);
        if (!$websitedb) {
            throw new WebsiteException('Database could not be created');
        }
        if (!$this->initDbStructure($objUser, $objDbUser, $langId, $websitedb)) {
            throw new WebsiteException('Database structure could not be initialized');
        }
        if (!$this->initDbData($objUser, $objDbUser, $langId, $websitedb)) {
            throw new WebsiteException('Database data could not be initialized');
        }    
    }
    /*
    * function setupDataFolder to create folders for 
    * website like configurations files
    * @param $websiteName name of the website
    * */
    private function setupDataFolder($websiteName){
        $this->cl = \Env::get('ClassLoader');
        // create folders and chmod with 0755
        // otherwise the file system class will set 777
        \Cx\Lib\FileSystem\FileSystem::make_folder(\Cx\Core\Setting\Controller\Setting::getValue('websitePath').'/'.$websiteName);
            
        \Cx\Lib\FileSystem\FileSystem::makeWritable(\Cx\Core\Setting\Controller\Setting::getValue('websitePath').'/'.$websiteName);
        \Cx\Lib\FileSystem\FileSystem::make_folder(\Cx\Core\Setting\Controller\Setting::getValue('websitePath').'/'.$websiteName . '/config');
        \Cx\Lib\FileSystem\FileSystem::makeWritable(\Cx\Core\Setting\Controller\Setting::getValue('websitePath').'/'.$websiteName . '/config');
        \Cx\Lib\FileSystem\FileSystem::make_folder(\Cx\Core\Setting\Controller\Setting::getValue('websitePath').'/'.$websiteName . '/tmp');
        \Cx\Lib\FileSystem\FileSystem::makeWritable(\Cx\Core\Setting\Controller\Setting::getValue('websitePath').'/'.$websiteName . '/tmp');
// TODO: Add /themes to Website
        // create media folders
        \Cx\Lib\FileSystem\FileSystem::make_folder(\Cx\Core\Setting\Controller\Setting::getValue('websitePath').'/'.$websiteName . '/media');
        \Cx\Lib\FileSystem\FileSystem::makeWritable(\Cx\Core\Setting\Controller\Setting::getValue('websitePath').'/'.$websiteName . '/media');
        \Cx\Lib\FileSystem\FileSystem::make_folder(\Cx\Core\Setting\Controller\Setting::getValue('websitePath').'/'.$websiteName . '/media/checkout');
        \Cx\Lib\FileSystem\FileSystem::makeWritable(\Cx\Core\Setting\Controller\Setting::getValue('websitePath').'/'.$websiteName . '/media/checkout');
        \Cx\Lib\FileSystem\FileSystem::make_folder(\Cx\Core\Setting\Controller\Setting::getValue('websitePath').'/'.$websiteName . '/images');
        \Cx\Lib\FileSystem\FileSystem::makeWritable(\Cx\Core\Setting\Controller\Setting::getValue('websitePath').'/'.$websiteName . '/images');
        \Cx\Lib\FileSystem\FileSystem::make_folder(\Cx\Core\Setting\Controller\Setting::getValue('websitePath').'/'.$websiteName . '/images/content');
        \Cx\Lib\FileSystem\FileSystem::makeWritable(\Cx\Core\Setting\Controller\Setting::getValue('websitePath').'/'.$websiteName . '/images/content');
    }    
     /*
    * function setupConfiguration to create configuration
    * files
    * @param $website Name name of the website
    * */
    private function setupConfiguration($websiteName, $objDb, $objDbUser){
        global $_PATHCONFIG;
        // copy templated files
        // config/configuration.php
        # copy file
        try {
            $conf = new \Cx\Lib\FileSystem\File($this->cl->getFilePath(ASCMS_CORE_MODULE_PATH.'/MultiSite/Data/WebsitesTemplate/config/configuration.php'));
            $conf->copy(\Cx\Core\Setting\Controller\Setting::getValue('websitePath').'/'.$websiteName . '/config/configuration.php');
        } catch (\Cx\Lib\FileSystem\FileSystemException $e) {
            //\DBG::msg($e->getMessage());
            throw new WebsiteException('error in copy configuration file');
        }
        
        // update db config
        # Read data from file
        try {
            $newConf = new \Cx\Lib\FileSystem\File(\Cx\Core\Setting\Controller\Setting::getValue('websitePath').'/'.$websiteName . '/config/configuration.php');
            $newConfData = $newConf->getData();
        } catch (\Cx\Lib\FileSystem\FileSystemException $e) {
            //\DBG::msg($e->getMessage());
            throw new WebsiteException('error in getting data from configuration file');
        }

       # Write data to file
        try {
            $newConfData = preg_replace('/\\$_DBCONFIG\\[\'host\'\\] = \'.*?\';/', '$_DBCONFIG[\'host\'] = \'' .$objDb->getHost() . '\';', $newConfData);
            $newConfData = preg_replace('/\\$_DBCONFIG\\[\'tablePrefix\'\\] = \'.*?\';/', '$_DBCONFIG[\'tablePrefix\'] = \'' .$objDb->getTablePrefix() . '\';', $newConfData);
            $newConfData = preg_replace('/\\$_DBCONFIG\\[\'dbType\'\\] = \'.*?\';/', '$_DBCONFIG[\'dbType\'] = \'' .$objDb->getdbType() . '\';', $newConfData);
            $newConfData = preg_replace('/\\$_DBCONFIG\\[\'charset\'\\] = \'.*?\';/', '$_DBCONFIG[\'charset\'] = \'' .$objDb->getCharset() . '\';', $newConfData);
            $newConfData = preg_replace('/\\$_DBCONFIG\\[\'timezone\'\\] = \'.*?\';/', '$_DBCONFIG[\'timezone\'] = \'' .$objDb->getTimezone() . '\';', $newConfData);
            $newConfData = preg_replace('/\\$_DBCONFIG\\[\'database\'\\] = \'.*?\';/', '$_DBCONFIG[\'database\'] = \'' .$objDb->getName() . '\';', $newConfData);
            $newConfData = preg_replace('/\\$_DBCONFIG\\[\'user\'\\] = \'.*?\';/', '$_DBCONFIG[\'user\'] = \'' . $objDbUser->getName() . '\';', $newConfData);
            $newConfData = preg_replace('/\\$_DBCONFIG\\[\'password\'\\] = \'.*?\';/', '$_DBCONFIG[\'password\'] = \'' . $objDbUser->getPassword() . '\';', $newConfData);
            
            //adding path config
            $newConfData = preg_replace('/\\$_PATHCONFIG\\[\'ascms_root\'\\] = \'.*?\';/', '$_PATHCONFIG[\'ascms_root\'] = \'' . \Cx\Core\Setting\Controller\Setting::getValue('websitePath').'/'.$websiteName . '\';', $newConfData);
            $newConfData = preg_replace('/\\$_PATHCONFIG\\[\'ascms_installation_root\'\\] = \'.*?\';/', '$_PATHCONFIG[\'ascms_installation_root\'] = \'' . $_PATHCONFIG['ascms_installation_root'] . '\';', $newConfData);          
                        
            $newConf->write($newConfData);
        } catch (\Cx\Lib\FileSystem\FileSystemException $e) {
            //\DBG::msg($e->getMessage());
            throw new WebsiteException('error in modifying data in configuration file');
        }

        // config/settings.php
        # copy file
        try {
            $settings = new \Cx\Lib\FileSystem\File($this->cl->getFilePath(ASCMS_CORE_MODULE_PATH.'/MultiSite/Data/WebsitesTemplate/config/settings.php'));
            $settings->copy(\Cx\Core\Setting\Controller\Setting::getValue('websitePath').'/'.$websiteName . '/config/settings.php');
        } catch (\Cx\Lib\FileSystem\FileSystemException $e) {
            //\DBG::msg($e->getMessage());
            throw new WebsiteException('error in copying setting file');
        }
        
        // update domain url
        # Write data to file
        try {
            $newSettings = new \Cx\Lib\FileSystem\File(\Cx\Core\Setting\Controller\Setting::getValue('websitePath').'/'.$websiteName . '/config/settings.php');
            $newSettings->write(preg_replace('/\\$_CONFIG\\[\'domainUrl\'\\](?:[ ]+)= \"(?:[a-z-\\.]+)\";/', '$_CONFIG[\'domainUrl\'] = \'' . $websiteName . '.' . \Cx\Core\Setting\Controller\Setting::getValue('multiSiteDomain') . '\';', $newSettings->getData()));
            $newSettings->write(preg_replace('/\\$_CONFIG\\[\'licenseCreatedAt\'\\](?:[ ]+)= (?:[0-9]+);/', '$_CONFIG[\'licenseCreatedAt\'] = ' . time() . ';', $newSettings->getData()));
        } catch (\Cx\Lib\FileSystem\FileSystemException $e) {
            //\DBG::msg($e->getMessage());
            throw new WebsiteException('error in writing data into setting file');
        }
        
        // tmp/legacyClassCache.tmp
        # copy file
        try {
            $classCache = new \Cx\Lib\FileSystem\File($this->cl->getFilePath(ASCMS_CORE_MODULE_PATH.'/MultiSite/Data/WebsitesTemplate/tmp/legacyClassCache.tmp'));
            $classCache->copy(\Cx\Core\Setting\Controller\Setting::getValue('websitePath').'/'.$websiteName . '/tmp/legacyClassCache.tmp');
        } catch (\Cx\Lib\FileSystem\FileSystemException $e) {
            //\DBG::msg($e->getMessage());
            throw new WebsiteException('error in copying legacyClassCache.tmp');
        }    
    }

    /**
     * Removes non-activated websites that are older than 60 days
    */
    public function cleanup() {
throw new WebsiteException('implement secret-key algorithm first!');
        $instRepo = new \Cx\Core_Modules\MultiSite\Model\Repository\WebsiteRepository();
        $websites = new \Cx\Core_Modules\Listing\Model\Entity\DataSet($instRepo->findAll(\Cx\Core\Setting\Controller\Setting::getValue('websitePath')));
        $someTimeAgo = strtotime('60 days ago');
        foreach ($websites as $website) {
            if (!$website->isActivated() && $website->getCreateDate() < $someTimeAgo) {
                $this->removeWebsite($website->getName());
            }
        }
    }
    
    /**
     * Completely removes an website
     * @param type $websiteName 
     */
    public function removeWebsite($websiteName, $silent = false) {
        if (is_array($websiteName)) {
            if (isset($websiteName['post']) && isset($websiteName['post']['websiteName'])) {
                $websiteName = $websiteName['post']['websiteName'];
            } else {
                $websiteName = '';
            }
        }
        if (empty($websiteName)) {
            $websiteName = current(explode('.', substr($_SERVER['HTTP_ORIGIN'], 8)));
        }
        
        // check if installation exists
        if (!file_exists(\Cx\Core\Setting\Controller\Setting::getValue('websitePath').'/'.$websiteName)) {
            if ($silent) {
                return false;
            }
            throw new MultiSiteException('No website with that name');
        }
        
// TODO: remove database user
        // remove database
        $dbName = \Cx\Core\Setting\Controller\Setting::getValue('websiteDatabasePrefix').$this->id;
        $dbObj = new \Cx\Core\Model\Model\Entity\Db();
        $dbObj->setName($dbName);
        $this->websiteController->removeDb($dbObj);

        // remove files
        \Cx\Lib\FileSystem\FileSystem::delete_folder(\Cx\Core\Setting\Controller\Setting::getValue('websitePath').'/'.$websiteName, true);

        return 'success';
    }
    
    protected function initDatabase($objDb, $objDbUser)
    {
        //call db controller method to create new db
        $this->websiteController->createDb($objDb, $objDbUser);

        //call core db class to create db connection object
        $dbClass = new \Cx\Core\Model\Db($objDb, $objDbUser);
        $websitedb = $dbClass->getAdoDb();       

        return $websitedb;
    }

    protected function initDbStructure($objUser, $objDbUser, $langId, $websitedb) {
        return $this->initDb('structure', $objUser, $objDbUser, $langId, $websitedb);
    }
    
    protected function initDbData($objUser, $objDbUser, $langId, $initDbData) {
        return $this->initDb('data', $objUser, $objDbUser, $langId, $initDbData);
    }
    
    /**
     *
     * @param type $dbPrefix
     * @param type $type
     * @param type $mail
     * @return boolean|string
     * @throws \Exception 
     */
    protected function initDb($type, $objUser, $objDbUser, $langId, $websitedb) {
        global $_CONFIG;
        $fp = @fopen(\Env::get('ClassLoader')->getFilePath(ASCMS_CORE_MODULE_PATH . '/MultiSite/Data/contrexx_dump_' . $type . '.sql'), "r");
        if ($fp === false) {
            throw new \Exception('File not found');
        }

        $line = 1;
        if (!isset($_SESSION['MultiSite'])) {
            $_SESSION['MultiSite'] = array();
        }
        if (!isset($_SESSION['MultiSite']['sqlqueries'])) {
            $_SESSION['MultiSite']['sqlqueries'] = array();
        }
        if (!isset($_SESSION['MultiSite']['sqlqueries'][$type])) {
            $_SESSION['MultiSite']['sqlqueries'][$type] = 0;
        }
        $sqlQuery = '';
        $statusMsg = '';
        while (!feof($fp)) {
            if ($_SESSION['MultiSite']['sqlqueries'][$type] >= $line) {
                $line++;
                continue;
            }
            $buffer = fgets($fp);
            if ((substr($buffer,0,1) != "#") && (substr($buffer,0,2) != "--")) {
                $sqlQuery .= $buffer;
                if (preg_match("/;[ \t\r\n]*$/", $buffer)) {
                    // Don't have to replace prefix, because it is in a separate db.
                    // This would be required when using single-database-mode.
                    // Single-database-mode has not yet been implemented.
                    //$sqlQuery = preg_replace($dbPrefixRegexp, '`'.$dbsuffix.'$1`', $sqlQuery);
                    $sqlQuery = preg_replace('#CONSTRAINT(\s)*`([0-9a-z_]*)`(\s)*FOREIGN KEY#', 'CONSTRAINT FOREIGN KEY', $sqlQuery);
                    $sqlQuery = preg_replace('/TYPE=/', 'ENGINE=', $sqlQuery);
// TODO: migrate content to have info@cloudrexx.com or similar set as default
                    $sqlQuery = str_replace('info@payrexx.com', $objUser->getEmail(), $sqlQuery);
                    $sqlQuery = preg_replace('/'.md5('5xMdtue3BL').'/', md5($objDbUser->getPassword()), $sqlQuery);
                    $result = $websitedb->Execute($sqlQuery);
                    if ($result === false) {
                        $statusMsg .= "<br />".htmlentities($sqlQuery, ENT_QUOTES, 'UTF-8')."<br /> (".$websitedb->ErrorMsg().")<br />";
                        return $statusMsg;
/*                    } else {
                        echo $sqlQuery;*/
                    }
                    $sqlQuery = '';
                }
            }
            $_SESSION['MultiSite']['sqlqueries'][$type] = $line;
            $line++;
        }
        
        if ($type == 'data') {
            // set default language for user
            $result = $websitedb->Execute(
                    'UPDATE `contrexx_access_users`
                        SET `frontend_lang_id` = ' . $langId . ',
                            `backend_lang_id`  = ' . $langId . '
                        WHERE `email` = \'' . $objUser->getEmail() . '\''
            );
            if ($result === false) {
                $statusMsg .= "<br />".htmlentities($sqlQuery, ENT_QUOTES, 'UTF-8')."<br /> (".$websitedb->ErrorMsg().")<br />";
                return $statusMsg;
            }

            // set default language for installation
            $result = $websitedb->Execute('
                    UPDATE
                        `contrexx_languages`
                    SET
                        `is_default` =
                            CASE `id`
                                WHEN ' . $langId . '
                                THEN \'true\'
                                ELSE \'false\'
                            END'
            );
            if ($result === false) {
                $statusMsg .= "<br />".htmlentities($sqlQuery, ENT_QUOTES, 'UTF-8')."<br /> (".$websitedb->ErrorMsg().")<br />";
                return $statusMsg;
            }
        }
        
        global $_DBCONFIG;
        unset($_SESSION['MultiSite']['sqlqueries'][$type]);

        if (empty($statusMsg)) {
            return true;
        } else {
            //echo $statusMsg;
            return $statusMsg;
        }
    }

}
