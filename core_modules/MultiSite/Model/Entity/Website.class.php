<?php 
namespace Cx\Core_Modules\MultiSite\Model\Entity;

class WebsiteException extends \Exception {}

class Website extends \Cx\Model\Base\EntityBase {
    
    /**
     * Status online
     */
    const STATE_ONLINE = 'online';
    
    /**
     * Status offline
     */
    const STATE_OFFLINE = 'offline';
    
    /**
     * Status init
     */
    const STATE_INIT = 'init';
    
    /**
     * Status setup
     */
    const STATE_SETUP =  'setup';
        
    protected $basepath = null;
  
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var string $name
     */
    protected $name;

    /**
     * @var string $codeBase
     */
    protected $codeBase;

    /**
     * @var string $language
     */
// TODO: do we still need this??
    protected $language;

    /**
     * @var string $status
     */
    protected $status;
    
    /**
     * @var integer $websiteServiceServerId
     */
    protected $websiteServiceServerId;
    
    /**
     * @var Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer
     */
    protected $websiteServiceServer;
    
    protected $owner;
    
    protected $websiteController;
   
    /**
     * @var string $ipAddress
     */
    protected $ipAddress;

    /**
     * @var integer $ownerId
     */
    protected $ownerId;
    
    /**
     * @var string $secretKey
     */
    protected $secretKey;
    
    /**
     * @var string $installationId
     */
    protected $installationId;

    /**
     * @var Cx\Core_Modules\MultiSite\Model\Entity\Domain
     */
    protected $fqdn;
    /**
     * @var Cx\Core_Modules\MultiSite\Model\Entity\Domain
     */
    protected $baseDn;

    /**
     * @var Cx\Core_Modules\MultiSite\Model\Entity\Domain
     */
    protected $domainAliases;
    
    /**
     * @var Cx\Core_Modules\MultiSite\Model\Entity\Domain
     */
    protected $domains;
    
    /**
     * @var string $ftpUser
     */
    protected $ftpUser;
    
    /**
     * @var string $themeId
     */
    protected $themeId;
    
    /*
     * Constructor
     * */
    public function __construct($basepath, $name, $websiteServiceServer = null, \User $userObj=null, $lazyLoad = true, $themeId = 0) {
        $this->basepath = $basepath;
        $this->name = $name;

        if ($lazyLoad) {
            return true;
        }

        $this->domains = new \Doctrine\Common\Collections\ArrayCollection();      
        $this->language = $userObj->getFrontendLanguage();
        $this->status = self::STATE_INIT;
        $this->websiteServiceServerId = 0;
        $this->owner = $userObj;
        $this->ownerId = $userObj->getId();
        $this->installationId = $this->generateInstalationId();
        $this->themeId = $themeId;

        if ($websiteServiceServer) {
            $this->setWebsiteServiceServer($websiteServiceServer);
        }

        // set IP of Website
        switch (\Cx\Core\Setting\Controller\Setting::getValue('mode')) {
            case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_MANAGER:
                if ($this->id) {
                    break;
                }
                $resp = \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::executeCommandOnServiceServer('getDefaultWebsiteIp', array(), $this->websiteServiceServer);
                if(!$resp || $resp->status == 'error'){
                    $errMsg = isset($resp->message) ? $resp->message : '';
                    if (isset($resp->log)) {
                        \DBG::appendLogsToMemory($resp->log);
                    }
                    throw new WebsiteException('Unable to fetch defaultWebsiteIp from Service Server: '.$errMsg);    
                }
                if (isset($resp->log)) {
                    \DBG::appendLogsToMemory($resp->log);
                }
                $this->ipAddress = $resp->data->defaultWebsiteIp;
                break;

            case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_HYBRID:
            case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_SERVICE:
                $this->ipAddress = \Cx\Core\Setting\Controller\Setting::getValue('defaultWebsiteIp');
                break;

            default:
                break;
        }

        $this->secretKey = \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::generateSecretKey();
        $this->validate();
        $this->codeBase = \Cx\Core\Setting\Controller\Setting::getValue('defaultCodeBase');
        $this->setFqdn();
        $this->setBaseDn();
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
     * Set secretKey
     *
     * @param string $secretKey
     */
    public function setSecretKey($secretKey)
    {
        $this->secretKey = $secretKey;
    }

    /**
     * Get secretKey
     *
     * @return string $secretKey
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }
    /**
     * Set ipAddress
     *
     * @param string $ipAddress
     */
    public function setIpAddress($ipAddress)
    {
        $this->ipAddress = $ipAddress;
    }

    /**
     * Get ipAddress
     *
     * @return string $ipAddress
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    /**
     * Set ownerId
     *
     * @param integer $ownerId
     */
    public function setOwnerId($ownerId)
    {
        $this->ownerId = $ownerId;
    }

    /**
     * Get ownerId
     *
     * @return integer $ownerId
     */
    public function getOwnerId()
    {
        return $this->ownerId;
    }
    
    /**
     * Set themeId
     *
     * @param integer $themeId
     */
    public function setThemeId($themeId)
    {
        $this->themeId = $themeId;
    }

    /**
     * Get themeId
     *
     * @return integer $themeId
     */
    public function getThemeId()
    {
        return $this->themeId;
    }
    
     /**
     * Set installationId
     *
     * @param string $installationId
     */
    public function setInstallationId($installationId)
    {
        $this->installationId = $installationId;
    }

    /**
     * Get installationId
     *
     * @return string $installationId
     */
    public function getInstallationId()
    {
        return $this->installationId;
    }
    
    /**
     * Set the FTP user name
     * 
     * @param string $ftpUser
     */
    public function setFtpUser($ftpUser) {
        $this->ftpUser = $ftpUser;
    }
    
    /**
     * Get the FTP user name
     * 
     * @return string
     */
    public function getFtpUser() {
        return $this->ftpUser;
    }
    
    /**
     * Creates a new website
     */
    public function setup($options) {
        global $_DBCONFIG, $_ARRAYLANG;
        
        \DBG::msg('Website::setup()');
        $this->status = self::STATE_SETUP;
        \Env::get('em')->persist($this);
        \Env::get('em')->flush();
        
        $this->websiteController = \Cx\Core_Modules\MultiSite\Controller\ComponentController::getHostingController();

        $websiteName = $this->getName();
        $websiteMail = $this->owner->getEmail(); 
        $websiteThemeId = $this->getThemeId(); 
        $websiteIp = null;

        // language
        $lang = $this->owner->getBackendLanguage();
        $langId = \FWLanguage::getLanguageIdByCode($lang);
        
        if ($langId === false) {
            $langId = \FWLanguage::getDefaultLangId();
        }
        $isServiceServer = true;
        //check if the current server is running as the website manager
        if ($this->websiteServiceServer instanceof \Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer) {
            \DBG::msg('Website: Forward setup() to Website Service Server');
            $isServiceServer = false;
            //create user account in website service server
            $resp = \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::executeCommandOnServiceServer('createUser', array('userId' => $this->owner->getId(), 'email'  => $this->owner->getEmail()), $this->websiteServiceServer);
            if(!$resp || $resp->status == 'error'){
                $errMsg = isset($resp->message) ? $resp->message : '';
                \DBG::dump($errMsg);
                if (isset($resp->log)) {
                    \DBG::appendLogsToMemory($resp->log);
                }
                throw new WebsiteException('Problem in creating website owner '.$errMsg);    
            }
            if (isset($resp->log)) {
                \DBG::appendLogsToMemory($resp->log);
            }
            //create website in website service server
            $params = array(
                'userId'      => $this->owner->getId(),
                'websiteName' => $websiteName,
                'websiteId'   => $this->getId(),
                'options'     => $options,
                'themeId'     => $websiteThemeId
                );
            $resp = \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::executeCommandOnServiceServer('createWebsite', $params, $this->websiteServiceServer);
            if(!$resp || $resp->status == 'error'){
                $errMsg = isset($resp->message) ? $resp->message : '';
                \DBG::dump($errMsg);
                if (isset($resp->log)) {
                    \DBG::appendLogsToMemory($resp->log);
                }
                throw new WebsiteException('Problem in creating website '.$errMsg);    
            }
            if (isset($resp->log)) {
                \DBG::appendLogsToMemory($resp->log);
            }
            $this->ipAddress = $resp->data->websiteIp;
            $this->codeBase  = $resp->data->codeBase;
            $this->status    = $resp->data->state;
            $ftpAccountPassword  = $resp->data->ftpPassword;
        } else {
            \DBG::msg('Website: setup process..');

            $objDb = new \Cx\Core\Model\Model\Entity\Db($_DBCONFIG);
            $objDbUser = new \Cx\Core\Model\Model\Entity\DbUser();

            \DBG::msg('Website: setupDatabase..');
            $this->setupDatabase($langId, $this->owner, $objDb, $objDbUser);

            \DBG::msg('Website: setupDataFolder..');
            $this->setupDataFolder($websiteName);

            \DBG::msg('Website: setupFtpAccount..');
            $ftpAccountPassword = $this->setupFtpAccount($websiteName);

            \DBG::msg('Website: setupConfiguration..');
            $this->setupConfiguration($websiteName, $objDb, $objDbUser);

            \DBG::msg('Website: setupMultiSiteConfig..');
            $this->setupMultiSiteConfig($websiteName);

            \DBG::msg('Website: setupLicense..');
            $this->setupLicense($options);
            
            \DBG::msg('Website: initializeConfig..');
            $this->initializeConfig($websiteName);

            \DBG::msg('Website: initializeLanguage..');
            $this->initializeLanguage();

            \DBG::msg('Website: setupTheme..');
            $this->setupTheme($websiteThemeId);
            
            // \DBG::msg('Website: setupRobotsFile..');
            // $this->setupRobotsFile($websiteName);

            \DBG::msg('Website: createContrexxUser..');
            $this->createContrexxUser($websiteName);

            \DBG::msg('Website: setup process.. DONE');

            \DBG::msg('Website: Set state to '.self::STATE_ONLINE);
            $this->status = self::STATE_ONLINE;
            $websiteIp = \Cx\Core\Setting\Controller\Setting::getValue('defaultWebsiteIp');
        }

        //set ftp user name if ftp not empty
        if (!empty($ftpAccountPassword)) {
            $this->ftpUser = $websiteName;
        }
        
        \Env::get('em')->persist($this);
        \Env::get('em')->flush();

        if (\Cx\Core\Setting\Controller\Setting::getValue('mode') == \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_WEBSITE) {
            throw new WebsiteException('MultiSite mode was set to Website at the end of setup process. No E-Mail was sent to '.$this->owner->getEmail());
        }
        if (\Cx\Core\Setting\Controller\Setting::getValue('mode') == \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_MANAGER
            || \Cx\Core\Setting\Controller\Setting::getValue('mode') == \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_HYBRID
        ) {
            $websiteDomain = $websiteName.'.'.\Cx\Core\Setting\Controller\Setting::getValue('multiSiteDomain');
            $websiteUrl = \Cx\Core_Modules\MultiSite\Controller\ComponentController::getApiProtocol().$websiteName.'.'.\Cx\Core\Setting\Controller\Setting::getValue('multiSiteDomain');

            // set user account password
            $websitePassword = '';
            $websitePasswordUrl = '';
            $websiteVerificationUrl = '';
            switch (\Cx\Core\Setting\Controller\Setting::getValue('passwordSetupMethod')) {
                case 'interactive':
                    \DBG::msg('Website: generate reset password link for Cloudrexx user..');
                    $passwordBlock = 'WEBSITE_PASSWORD_INTERACTIVE';
                    $websitePasswordUrl = $this->generatePasswordRestoreUrl();
                    break;

                case 'auto-with-verification':
                    \DBG::msg('Website: set verification state to pending on Cloudrexx user..');
                    // set state of user account to unverified
                    $this->owner->setVerification(false);
                    $this->owner->store();
                    $websiteVerificationUrl = $this->generateVerificationUrl();

                    // important: intentionally no break for this case!

                case 'auto':
                default:
                    \DBG::msg('Website: generate password for Cloudrexx user..');
                    $passwordBlock = 'WEBSITE_PASSWORD_AUTO';
                    $websitePassword = $this->generateAccountPassword();
                    break;
            }

            \DBG::msg('Website: SETUP COMPLETED > OK');

            // write mail
            \Cx\Core\MailTemplate\Controller\MailTemplate::init('MultiSite');
            // send ADMIN mail
            \DBG::msg('Website: send notification email > ADMIN');
            \Cx\Core\MailTemplate\Controller\MailTemplate::send(array(
                'section' => 'MultiSite',
                'lang_id' => $langId,
                'key' => 'notifyAboutNewWebsite',
                //'to' => $websiteMail,
                'search' => array(
                    '[[MULTISITE_DOMAIN]]',
                    '[[WEBSITE_DOMAIN]]',
                    '[[WEBSITE_URL]]',
                    '[[WEBSITE_NAME]]',
                    '[[CUSTOMER_EMAIL]]',
                    '[[CUSTOMER_NAME]]',
                    '[[SUBSCRIPTION_NAME]]'),
                'replace' => array(
                    \Cx\Core\Setting\Controller\Setting::getValue('multiSiteDomain'),
                    $websiteDomain,
                    $websiteUrl,
                    $websiteName,
                    $websiteMail,
                    '<customer-name>',
                    '<subscription:trial / business>'),
            ));
            // send CUSTOMER mail
            $info = array(
                'section' => 'MultiSite',
                'lang_id' => $langId,
                'key' => 'createInstance',
                'to' => $websiteMail,
                'search' => array('[[WEBSITE_DOMAIN]]', '[[WEBSITE_NAME]]', '[[WEBSITE_MAIL]]'),
                'replace' => array($websiteDomain, $websiteName, $websiteMail),
                'substitution' => array(
                    $passwordBlock => array(
                        '0' => array(
                            'WEBSITE_PASSWORD' => $websitePassword,
                            'WEBSITE_MAIL' => $websiteMail,
                            'WEBSITE_PASSWORD_URL' => $websitePasswordUrl,
                        ),
                    )
                ),
            );
            // If email verification is required,
            // parse related block in notification email.
            if ($websiteVerificationUrl) {
                $info['substitution']['WEBSITE_EMAIL_VERIFICATION'] = array(
                    '0' => array(
                        'WEBSITE_VERIFICATION_URL' => $websiteVerificationUrl,
                    )
                );
            }
            //If $ftpAccountPassword is set, then add related entry to substitution
            if (isset($ftpAccountPassword)) {
                $info['substitution']['WEBSITE_FTP'] = array(
                    '0' => array(
                        'WEBSITE_DOMAIN'       => $websiteDomain,
                        'WEBSITE_FTP_USER'     => $websiteName,
                        'WEBSITE_FTP_PASSWORD' => $ftpAccountPassword
                    )
                );
            }
            \DBG::msg('Website: send notification email > CUSTOMER');
            if (!\Cx\Core\MailTemplate\Controller\MailTemplate::send($info)) {
                throw new WebsiteException(__METHOD__.': Unable to send welcome e-mail to user');
            }
            \DBG::msg('Website: SETUP COMPLETED > ALL DONE');
            return array(
                'status' => 'success',
            );
        }

        \DBG::msg('Website: send setup response to Manager..');
        return array(
            'status'      => 'success',
            'websiteIp'   => $websiteIp,
            'codeBase'    => $this->codeBase,
            'state'       => $this->status,
            'ftpPassword' => $ftpAccountPassword
        );
    }
    
    /*
    * function validate to validate website name
    * */
    public function validate()
    {
        self::validateName($this->getName());
    }

    public static function validateName($name) {
        global $_ARRAYLANG, $objInit;

        $langData = $objInit->loadLanguageData('MultiSite');
        $_ARRAYLANG = array_merge($_ARRAYLANG, $langData);
        $websiteName = $name;

        // verify that name is not a blocked word
        $unavailablePrefixesValue = explode(',',\Cx\Core\Setting\Controller\Setting::getValue('unavailablePrefixes'));
        if (in_array($websiteName, $unavailablePrefixesValue)) {
            throw new WebsiteException(sprintf($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_ALREADY_EXISTS'], "<strong>$websiteName</strong>"));
        }

        // verify that name complies with naming scheme
        if (preg_match('/[^a-z0-9]/', $websiteName)) {
            throw new WebsiteException($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_NAME_WRONG_CHARS']);
        }
        if (strlen($websiteName) < \Cx\Core\Setting\Controller\Setting::getValue('websiteNameMinLength')) {
            throw new WebsiteException(sprintf($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_NAME_TOO_SHORT'], \Cx\Core\Setting\Controller\Setting::getValue('websiteNameMinLength')));
        }
        if (strlen($websiteName) > \Cx\Core\Setting\Controller\Setting::getValue('websiteNameMaxLength')) {
            throw new WebsiteException(sprintf($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_NAME_TOO_LONG'], \Cx\Core\Setting\Controller\Setting::getValue('websiteNameMaxLength')));
        }

        // existing website
        if (\Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website')->findOneBy(array('name' => $websiteName))) {
            throw new WebsiteException(sprintf($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_ALREADY_EXISTS'], "<strong>$websiteName</strong>"));
        }
    }
    
    /*
    * function setupDatabase to create database
    * and populate database with basic data
    * @param $langId language ID of the website
    * */
    protected function setupDatabase($langId, $objUser, $objDb, $objDbUser){
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
    protected function setupDataFolder($websiteName){
        // website's data repository
        $codeBaseOfWebsite = !empty($this->codeBase) ? \Cx\Core\Setting\Controller\Setting::getValue('codeBaseRepository').'/'.$this->codeBase  :  \Env::get('cx')->getCodeBaseDocumentRootPath();
        $codeBaseWebsiteSkeletonPath = $codeBaseOfWebsite . \Env::get('cx')->getCoreModuleFolderName() . '/MultiSite/Data/WebsiteSkeleton';
        if(!\Cx\Lib\FileSystem\FileSystem::copy_folder($codeBaseWebsiteSkeletonPath, \Cx\Core\Setting\Controller\Setting::getValue('websitePath').'/'.$websiteName)) {
            throw new WebsiteException('Unable to setup data folder');
        }
    }    
     /*
    * function setupConfiguration to create configuration
    * files
    * @param $website Name name of the website
    * */
    protected function setupConfiguration($websiteName, $objDb, $objDbUser){
        global $_PATHCONFIG;

        // setup base configuration (configuration.php)
        try {
            $newConf = new \Cx\Lib\FileSystem\File(\Cx\Core\Setting\Controller\Setting::getValue('websitePath').'/'.$websiteName . '/config/configuration.php');
            $newConfData = $newConf->getData();
            $installationRootPath = !empty($this->codeBase) ? \Cx\Core\Setting\Controller\Setting::getValue('codeBaseRepository').'/'.$this->codeBase : $_PATHCONFIG['ascms_installation_root'];

            // set database configuration
            $newConfData = preg_replace('/\\$_DBCONFIG\\[\'host\'\\] = \'.*?\';/', '$_DBCONFIG[\'host\'] = \'' .$objDb->getHost() . '\';', $newConfData);
            $newConfData = preg_replace('/\\$_DBCONFIG\\[\'tablePrefix\'\\] = \'.*?\';/', '$_DBCONFIG[\'tablePrefix\'] = \'' .$objDb->getTablePrefix() . '\';', $newConfData);
            $newConfData = preg_replace('/\\$_DBCONFIG\\[\'dbType\'\\] = \'.*?\';/', '$_DBCONFIG[\'dbType\'] = \'' .$objDb->getdbType() . '\';', $newConfData);
            $newConfData = preg_replace('/\\$_DBCONFIG\\[\'charset\'\\] = \'.*?\';/', '$_DBCONFIG[\'charset\'] = \'' .$objDb->getCharset() . '\';', $newConfData);
            $newConfData = preg_replace('/\\$_DBCONFIG\\[\'timezone\'\\] = \'.*?\';/', '$_DBCONFIG[\'timezone\'] = \'' .$objDb->getTimezone() . '\';', $newConfData);
            $newConfData = preg_replace('/\\$_DBCONFIG\\[\'database\'\\] = \'.*?\';/', '$_DBCONFIG[\'database\'] = \'' .$objDb->getName() . '\';', $newConfData);
            $newConfData = preg_replace('/\\$_DBCONFIG\\[\'user\'\\] = \'.*?\';/', '$_DBCONFIG[\'user\'] = \'' . $objDbUser->getName() . '\';', $newConfData);
            $newConfData = preg_replace('/\\$_DBCONFIG\\[\'password\'\\] = \'.*?\';/', '$_DBCONFIG[\'password\'] = \'' . $objDbUser->getPassword() . '\';', $newConfData);
            
            // set path configuration
            $newConfData = preg_replace('/\\$_PATHCONFIG\\[\'ascms_root\'\\] = \'.*?\';/', '$_PATHCONFIG[\'ascms_root\'] = \'' . \Cx\Core\Setting\Controller\Setting::getValue('websitePath').'/'.$websiteName . '\';', $newConfData);
            $newConfData = preg_replace('/\\$_PATHCONFIG\\[\'ascms_installation_root\'\\] = \'.*?\';/', '$_PATHCONFIG[\'ascms_installation_root\'] = \'' . $installationRootPath . '\';', $newConfData);          
                        
            $newConf->write($newConfData);
        } catch (\Cx\Lib\FileSystem\FileSystemException $e) {
            throw new WebsiteException('Unable to setup configuration file: '.$e->getMessage());
        }

        // setup basic configuration (settings.php)
        try {
            $newSettings = new \Cx\Lib\FileSystem\File(\Cx\Core\Setting\Controller\Setting::getValue('websitePath').'/'.$websiteName . '/config/settings.php');
            $settingsData = preg_replace_callback(
                '/(\$_CONFIG\[([\'"])((?:(?!\2).)*)\2\]\s*=\s*([\'"]))(?:(?:(?!\4).)*)(\4;)/',
                function($match) {
                    $originalString = $match[0];
                    $optionString = $match[1];
                    $settingsOption = $match[3];
                    $delimiter = $match[4];
                    $closure = $match[5];
                    $escapedDelimiter = addslashes($delimiter);
                    switch ($settingsOption) {
                        case 'domainUrl':
                            $value = $this->getBaseDn()->getName();
                            break;
                        case 'installationId':
                            $value = $this->installationId;
                            break;
                        case 'dashboardNewsSrc':
                            $value = \Cx\Core\Setting\Controller\Setting::getValue('dashboardNewsSrc');
                            break;
                        case 'coreAdminEmail':
                        case 'contactFormEmail':
                            $value = $this->owner->getEmail();
                            break;
                        default:
                            return $originalString;
                            break;
                    }
                    $escapedValue = str_replace($delimiter, $escapedDelimiter, $value);
                    return  $optionString . $escapedValue . $closure;
                },
                $newSettings->getData()
            );
            $newSettings->write($settingsData);
        } catch (\Cx\Lib\FileSystem\FileSystemException $e) {
            throw new WebsiteException('Unable to setup settings file: '.$e->getMessage());
        }
          
    }

    protected function initializeConfig($websiteName) {
        
        try {
            $resp = \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::executeCommandOnWebsite('setupConfig', array(), $this);
            if(!$resp || $resp->status == 'error'){
                $errMsg = isset($resp->message) ? $resp->message : '';
                if (isset($resp->log)) {
                    \DBG::appendLogsToMemory($resp->log);
                }
                throw new WebsiteException($errMsg);    
            }
            if (isset($resp->log)) {
                \DBG::appendLogsToMemory($resp->log);
            }
        } catch (\Exception $e) {
            throw new WebsiteException('Unable to setup config Config.yml on Website: '.$e->getMessage());    
        }
    }

    protected function setupMultiSiteConfig($websiteName)
    {
        $websitePath = \Cx\Core\Setting\Controller\Setting::getValue('websitePath');
        $websiteConfigPath = $websitePath . '/' . $websiteName . \Env::get('cx')->getConfigFolderName();

        $config = \Env::get('config');
        $serviceInstallationId = $config['installationId'];
        $serviceHostname = $config['domainUrl'];
        $websiteHttpAuthMethod   = \Cx\Core\Setting\Controller\Setting::getValue('websiteHttpAuthMethod');
        $websiteHttpAuthUsername = \Cx\Core\Setting\Controller\Setting::getValue('websiteHttpAuthUsername');
        $websiteHttpAuthPassword = \Cx\Core\Setting\Controller\Setting::getValue('websiteHttpAuthPassword');
        
        try {
            \Cx\Core\Setting\Controller\Setting::init('MultiSite', 'config','FileSystem', $websiteConfigPath);
            if (\Cx\Core\Setting\Controller\Setting::getValue('mode') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('mode', \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_WEBSITE, 1,
                \Cx\Core\Setting\Controller\Setting::TYPE_DROPDOWN, \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_WEBSITE.':'.\Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_WEBSITE, 'config')){
                    throw new WebsiteException("Failed to add Setting entry for MultiSite mode");
            }
            \Cx\Core\Setting\Controller\Setting::init('MultiSite', 'website','FileSystem', $websiteConfigPath);
            if (\Cx\Core\Setting\Controller\Setting::getValue('serviceHostname') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('serviceHostname', $serviceHostname, 2,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'website')){
                    throw new WebsiteException("Failed to add Setting entry for Hostname of Website Service");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('serviceSecretKey') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('serviceSecretKey', $this->secretKey, 3,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'website')){
                    throw new WebsiteException("Failed to add Setting entry for SecretKey of Website Service");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('serviceInstallationId') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('serviceInstallationId', $serviceInstallationId, 4,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'website')){
                    throw new WebsiteException("Failed to add Setting entry for InstallationId of Website Service");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('websiteUserId') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('websiteUserId', 0, 5,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'website')){
                    throw new WebsiteException("Failed to add Setting entry for InstallationId of Website User Id");
            }
// TODO: HTTP-Authentication details of Website Service Server must be set
            if (\Cx\Core\Setting\Controller\Setting::getValue('serviceHttpAuthMethod') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('serviceHttpAuthMethod', $websiteHttpAuthMethod, 5,
                \Cx\Core\Setting\Controller\Setting::TYPE_DROPDOWN, 'none:none, basic:basic, digest:digest', 'website')){
                    throw new WebsiteException("Failed to add Setting entry for HTTP Authentication Method of Website Service");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('serviceHttpAuthUsername') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('serviceHttpAuthUsername', $websiteHttpAuthUsername, 6,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'website')){
                    throw new WebsiteException("Failed to add Setting entry for HTTP Authentication Username of Website Service");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('serviceHttpAuthPassword') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('serviceHttpAuthPassword', $websiteHttpAuthPassword, 7,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'website')){
                    throw new WebsiteException("Failed to add Setting entry for HTTP Authentication Password of Website Service");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('websiteState') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('websiteState', $this->status, 8,
                \Cx\Core\Setting\Controller\Setting::TYPE_DROPDOWN, self::STATE_ONLINE.':'.self::STATE_ONLINE.','.self::STATE_OFFLINE.':'.self::STATE_OFFLINE.','.self::STATE_INIT.':'.self::STATE_INIT.','.self::STATE_SETUP.':'.self::STATE_SETUP, 'website')){
                    throw new WebsiteException("Failed to add website entry for website state");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('websiteName') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('websiteName', $this->name, 9,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'website')){
                    throw new WebsiteException("Failed to add website entry for website name");
            }
            if (\Cx\Core\Setting\Controller\Setting::getValue('websiteFtpUser') === NULL
                && !\Cx\Core\Setting\Controller\Setting::add('websiteFtpUser', $this->name, 10,
                \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'website')){
                    throw new WebsiteException("Failed to add website entry for website FTP user");
            }
        } catch (\Exception $e) {
            // we must re-initialize the original MultiSite settings of the main installation
            \Cx\Core\Setting\Controller\Setting::init('MultiSite', '','FileSystem');
            throw new WebsiteException('Error in setting up the MultiSite configuration:'. $e->getMessage());
        }

        // we must re-initialize the original MultiSite settings of the main installation
        \Cx\Core\Setting\Controller\Setting::init('MultiSite', '','FileSystem');
    }

    /**
     * setup Robots File
     * 
     * @param string $websiteName websitename
     * 
     * @throws WebsiteException
     */
    protected function setupRobotsFile($websiteName) {
        try {
            $codeBaseOfWebsite = !empty($this->codeBase) ? \Cx\Core\Setting\Controller\Setting::getValue('codeBaseRepository').'/'.$this->codeBase  :  \Env::get('cx')->getCodeBaseDocumentRootPath();
            $setupRobotFile = new \Cx\Lib\FileSystem\File($codeBaseOfWebsite . \Env::get('cx')->getCoreModuleFolderName() . '/MultiSite/Data/WebsiteSkeleton/robots.txt');
            $setupRobotFile->copy(\Cx\Core\Setting\Controller\Setting::getValue('websitePath').'/'.$websiteName . '/robots.txt');
        }  catch (\Cx\Lib\FileSystem\FileSystemException $e) {
            throw new WebsiteException('Unable to setup robot file: '.$e->getMessage());
        }
    }

    protected function createContrexxUser($websiteName)
    {
        $params = array(
            'email' => $this->owner->getEmail(),
            'active'=> 1,
            'admin' => 1,
            // assign user to first user group 
            'groups' => array(1),
        );
        $resp = \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::executeCommandOnWebsite('createUser', $params, $this);
        if(!$resp || $resp->status == 'error'){
            $errMsg = isset($resp->message) ? $resp->message : '';
            \DBG::dump($resp);
            \DBG::msg($errMsg);
            throw new WebsiteException('Unable to create admin user account.');
        }
    }

    /**
     * Removes non-activated websites that are older than 60 days
    */
    public function cleanup() {
throw new WebsiteException('implement secret-key algorithm first!');
        $instRepo = \Env::get('em')->getRepository('\Cx\Core_Modules\MultiSite\Model\Entity\Website');
        $websites = new \Cx\Core_Modules\Listing\Model\Entity\DataSet($instRepo->findAll());
        $someTimeAgo = strtotime('60 days ago');
        foreach ($websites as $website) {
            if (!$website->isActivated() && $website->getCreateDate() < $someTimeAgo) {
                $this->removeWebsite($website->getName());
            }
        }
    }
    
    /**
     * Completely removes an website
     */
    public function destroy() {
        global $_DBCONFIG;
        
        \DBG::msg('MultiSite (Website): destroy');
        
        try {
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode')) {
                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_MANAGER:
                    $websiteServiceServerRepo = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer');
                    $websiteServiceServer = $websiteServiceServerRepo->findOneBy(array('id' => $this->websiteServiceServerId));
                    $resp = \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::executeCommandOnServiceServer('destroyWebsite', array('websiteId' => $this->id), $websiteServiceServer);
                    if (!$resp || $resp->status == 'error') {
                        $errMsg = isset($resp->message) ? $resp->message : '';
                        if (isset($resp->log)) {
                            \DBG::appendLogsToMemory($resp->log);
                        }
                        throw new WebsiteException('Unable to delete the website: ' . $errMsg);
                    }
                    break;
                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_SERVICE:
                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_HYBRID:
                    //remove the FTP Account if there
                    $hostingController = \Cx\Core_Modules\MultiSite\Controller\ComponentController::getHostingController();
                    if ($this->ftpUser) {
                        if (!$hostingController->removeFtpAccount($this->ftpUser)) {
                            throw new WebsiteException('Unable to delete the FTP Account');
                        }
                    }

                    //remove the database and its user
                    //remove the database user
                    $objDbUser = new \Cx\Core\Model\Model\Entity\DbUser();
                    $objDbUser->setName(\Cx\Core\Setting\Controller\Setting::getValue('websiteDatabaseUserPrefix') . $this->id);
                    $hostingController->removeDbUser($objDbUser);

                    //remove the database
                    $objDb = new \Cx\Core\Model\Model\Entity\Db($_DBCONFIG);
                    $objDb->setHost(\Cx\Core\Setting\Controller\Setting::getValue('websiteDatabaseHost'));
                    $objDb->setName(\Cx\Core\Setting\Controller\Setting::getValue('websiteDatabasePrefix') . $this->id);
                    $hostingController->removeDb($objDb);

                    //remove the website's data repository
                    if(file_exists(\Cx\Core\Setting\Controller\Setting::getValue('websitePath') . '/' . $this->name)) {
                        if (!\Cx\Lib\FileSystem\FileSystem::delete_folder(\Cx\Core\Setting\Controller\Setting::getValue('websitePath') . '/' . $this->name, true)) {
                            throw new WebsiteException('Unable to delete the website data repository');
                        }
                    }

                    //unmap all the domains
                    foreach ($this->domains as $domain) {
                        \Env::get('em')->remove($domain);
                        \Env::get('em')->getUnitOfWork()->computeChangeSet(\Env::get('em')->getClassMetadata('Cx\Core_Modules\Multisite\Model\Entity\Domain'), $domain);
                    }                    
                    break;
            }
        } catch (\Exception $e) {
            throw new WebsiteException('Website (destroy): Unable to delete the website' . $e->getMessage());
        }
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
     * @throws WebsiteException
     */
    protected function initDb($type, $objUser, $objDbUser, $langId, $websitedb) {
        $dumpFilePath = !empty($this->codeBase) ? \Cx\Core\Setting\Controller\Setting::getValue('codeBaseRepository').'/'.$this->codeBase  :  \Env::get('cx')->getCodeBaseDocumentRootPath();
        $fp = @fopen($dumpFilePath.'/installer/data/contrexx_dump_' . $type . '.sql', "r");
        if ($fp === false) {
            throw new WebsiteException('File not found');
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
// TODO: create default user
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

    function generateInstalationId(){
        $randomHash = \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::generateSecretKey();
        $installationId = $randomHash . str_pad(dechex(crc32($randomHash)), 8, '0', STR_PAD_LEFT);    
        return $installationId;
    }

    /**
     * Set Fqdn
     *
     */    
    function setFqdn(){
        $config = \Env::get('config');
        if (\Cx\Core\Setting\Controller\Setting::getValue('mode') == \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_MANAGER) {
            $serviceServerHostname = $this->websiteServiceServer->getHostname();
        } else {
            $serviceServerHostname = $config['domainUrl'];
        }
        $fqdn = new Domain($this->name.'.'.$serviceServerHostname);
        $fqdn->setType(Domain::TYPE_FQDN);
        $fqdn->setComponentType(\Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_WEBSITE);
        $this->mapDomain($fqdn);
        \Env::get('em')->persist($fqdn);
    }
    
    /**
     * get Fqdn
     *
     */    
    public function getFqdn(){
        // fetch FQDN from Domain repository
        if (!$this->fqdn) {
            foreach ($this->domains as $domain) {
                if ($domain->getType() == Domain::TYPE_FQDN) {
                    $this->fqdn = $domain;
                    break;
                }
            }
        }

        return $this->fqdn;
    }   
    
    /**
     * Set BaseDn
     *
     */    
    function setBaseDn(){
        $baseDn = new Domain($this->name.'.'.\Cx\Core\Setting\Controller\Setting::getValue('multiSiteDomain'));
        $baseDn->setType(Domain::TYPE_BASE_DOMAIN);
        $baseDn->setComponentType(\Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_WEBSITE);
        $this->mapDomain($baseDn);
        \Env::get('em')->persist($baseDn);
    }
    
    /**
     * Get BaseDn
     *
     */    
    public function getBaseDn(){
        // fetch baseDn from Domain repository
        if (!$this->baseDn) {
            foreach ($this->domains as $domain) {
                if ($domain->getType() == Domain::TYPE_BASE_DOMAIN) {
                    $this->baseDn = $domain;
                    break;
                }
            }
        }

        return $this->baseDn;
    }
    
    /**
     * Get DomainAliases
     *
     */   
    public function getDomainAliases(){
        // fetch domain aliases from Domain repository
        if (!$this->domainAliases) {
            foreach ($this->domains as $domain) {
                if ($domain->getType() == Domain::TYPE_EXTERNAL_DOMAIN) {
                    $this->domainAliases[] = $domain;
                }
            }
        }

        return $this->domainAliases;
    }

    /**
     * Get domains
     *
     * @return Doctrine\Common\Collections\Collection $domains
     */
    public function getDomains() {
        return $this->domains;
    }
    
    /**
     * mapDomain
     * 
     * @param Cx\Core_Modules\MultiSite\Model\Entity\Domain $domain
     */  
    public function mapDomain(Domain $domain) {
        $domain->setWebsite($this);
        $this->domains[] = $domain;

        switch ($domain->getType()) {
            case DOMAIN::TYPE_FQDN:
                $this->fqdn = $domain;
                break;

            case DOMAIN::TYPE_BASE_DOMAIN:
                $this->baseDn = $domain;
                break;

            case DOMAIN::TYPE_EXTERNAL_DOMAIN:
            default:
                $domain->settype(DOMAIN::TYPE_EXTERNAL_DOMAIN);
                $this->domainAliases[] = $domain;
                break;
        }
    }
    
    /**
     * unMapDomain
     *
     * @param string $name websitename
     */  
    public function unMapDomain($domain){
        foreach ($this->getDomainAliases() as $domainAlias) {
            if($domainAlias == $domain) {
                \Env::get('em')->remove($domain);
                break;
            }   
        }
    }
    
    /**
     * Set up the license
     * 
     * @param array $options
     * 
     * @return boolean
     */
    public function setupLicense($options) {
        if (empty($options['subscription'])) {
            throw new WebsiteException('Unable to setup license due to subscription details empty.');
        }

        switch ($options['subscription']) {
            case 'Trial':
                $legalComponents = array('Access', 'Agb', 'Alias', 'Block', 'Cache', 'Captcha', 'ComponentManager', 'Config', 'Contact', 'ContentManager',
                                            'ContentWorkflow', 'core', 'Csrf', 'Error', 'FileBrowser', 'FileSharing', 'FrontendEditing', 'fulllanguage',
                                            'Home', 'Ids', 'Imprint', 'JavaScript', 'JsonData', 'language', 'LanguageManager', 'License', 'Login', 'logout',
                                            'Media', 'Media1', 'Media2', 'Media3', 'Media4', 'Message', 'MultiSite', 'Net', 'News', 'Newsletter', 
                                            'Privacy', 'Search', 'Security', 'Session', 'Shell', 'Sitemap', 'Stats', 'U2u', 'Upload',
                                            'ViewManager');
                break;
        }

        if (!empty($legalComponents)) {
            $params = array(
                'websiteId'         => $this->id,
                'legalComponents'   => $legalComponents,
                'state'             => \Cx\Core_Modules\License\License::LICENSE_OK,
                'validTo'           => 2733517333,
                'updateInterval'    => 8760,
                'isUpgradable'      => true,
                'dashboardMessages' => array(
                    \FWLanguage::getLanguageCodeById(1) => array(
                        'text' => 'Testen Sie unsere gesamte Leistungsvielfalt bis zum 08.09.2014 völlig kostenlos!
                          In der kostenlosen Testphase bis zum 08.09.2014 haben Sie uneingeschränkten Zugriff auf sämtliche Funktionen. Sie können unser Produkt also ausgiebig testen. Überzeugen Sie sich von unserer Leistungsvielfalt und wählen Sie dann einfach die für Ihre Anforderungen geeignete Mitgliedschaftsart aus. Für eine individuelle Beratung, auch im Zusammenhang mit der Anwendung unserer Software, stehen wir Ihnen gerne zur Verfügung!
                          Nach dieser Testphase erhält ihr Account automatisch den Status FREE (kostenlos)!',
                        'type'       => 'okbox',
                        'link'       => null,
                        'linkTarget' => null
                    ),
                )
            );
            //send the JSON Request 'setLicense' command from service to website
            try {
                $resp = \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::executeCommandOnWebsite('setLicense', $params, $this);
                if ($resp && $resp->status == 'success' && $resp->data->status == 'success') {
                    return true;
                } else {
                    throw new WebsiteException('Unable to setup license: Error in setup license in Website');
                }
            } catch (\Cx\Core_Modules\MultiSite\Controller\MultiSiteJsonException $e) {
                throw new WebsiteException('Unable to setup license: '.$e->getMessage());
            }           
        } 
    }
    
    /**
     * Initialize the language
     */
    public function initializeLanguage() {
        //send the JSON Request 'setDefaultLanguage' command from service to website
        try {
            $resp = \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::executeCommandOnWebsite('setDefaultLanguage', array('langId' => $this->language), $this);
            if ($resp && $resp->status == 'success' && $resp->data->status == 'success') {
                return true;
            } else {
                throw new WebsiteException('Unable to initialize the language: Error in initializing language in Website');
            }
        } catch (\Cx\Core_Modules\MultiSite\Controller\MultiSiteJsonException $e) {
            throw new WebsiteException('Unable to initialize the language: '.$e->getMessage());
        }        
    }
    
    /**
     * Initialize the language
     */
    public function setupTheme($websiteThemeId) {
        //send the JSON Request 'setWebsiteTheme' command from service to website
        try {
            if (empty($websiteThemeId)) {
                return;
            }
            
            $resp = \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::executeCommandOnWebsite('setWebsiteTheme', array('themeId' => $websiteThemeId), $this);
            if ($resp && $resp->status == 'success' && $resp->data->status == 'success') {
                return true;
            } else {
                throw new WebsiteException('Unable to setup the theme: Error in setting theme in Website');
            }
        } catch (\Cx\Core_Modules\MultiSite\Controller\MultiSiteJsonException $e) {
            throw new WebsiteException('Unable to setup the theme: '.$e->getMessage());
        }        
    }
    
    /**
     * Create the Ftp-Account
     * 
     * @param string $websiteName website's name
     * 
     * @return boolean
     */
    public function setupFtpAccount($websiteName) {
        try {
            if (\Cx\Core\Setting\Controller\Setting::getValue('createFtpAccountOnSetup')) {
                //create FTP-Account
                $password = \User::make_password(8, true);
                $accountId = $this->websiteController->addFtpAccount($websiteName, $password, \Cx\Core\Setting\Controller\Setting::getValue('websiteFtpPath') . '/' . $websiteName, \Cx\Core\Setting\Controller\Setting::getValue('pleskWebsitesSubscriptionId'));

                if ($accountId) {
                    return $password;
                }
            }

            return false;
        } catch (\Exception $e) {
            throw new WebsiteException('Unable to setup ftp account: '.$e->getMessage());
        }    
    }
    
    /**
     * generate password restore url
     * 
     * @return string
     */
    public function generatePasswordRestoreUrl() {
        $this->owner->setRestoreKey();
        // hard-coded to 1 day
        $this->owner->setRestoreKeyTime(86400);
        $this->owner->store();
        $websitePasswordUrl = \FWUser::getPasswordRestoreLink(false, $this->owner, \Cx\Core\Setting\Controller\Setting::getValue('customerPanelDomain'));
        return $websitePasswordUrl;
    }

    public function generateVerificationUrl() {
        $this->owner->setRestoreKey();
        $this->owner->store();
        $websiteVerificationUrl = \FWUser::getVerificationLink(true, $this->owner, $this->baseDn->getName());
        return $websiteVerificationUrl;
    }

    public function generateAuthToken() {
        try {
            $resp = \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::executeCommandOnWebsite('generateAuthToken', array(), $this);
            if ($resp && $resp->status == 'success' && $resp->data->status == 'success') {
                return array($resp->data->userId, $resp->data->authToken);
            } else {
                throw new WebsiteException('Command generateAuthToken failed');
            }
        } catch (\Cx\Core_Modules\MultiSite\Controller\MultiSiteJsonException $e) {
            throw new WebsiteException('Unable to generate auth token: '.$e->getMessage());
        }  
    }

    /**
     * generate account password
     * 
     * @return string
     */
    public function generateAccountPassword() {
        
        $newPassword = \User::make_password(8, true);
        $params = array(
            'userId' => $this->ownerId,
            'multisite_user_account_password'           => $newPassword,
            'multisite_user_account_password_confirmed' => $newPassword,
        );
        try {
            $resp = \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::executeCommandOnManager('updateUser', $params);
            if ($resp && $resp->status == 'success' && $resp->data->status == 'success') {
                return $newPassword;
            } else {
                throw new WebsiteException('Unable to generate account password: Error in generate account password');
            }
        } catch (\Cx\Core_Modules\MultiSite\Controller\MultiSiteJsonException $e) {
            throw new WebsiteException('Unable to generate account password: '.$e->getMessage());
        }  
    }

}
