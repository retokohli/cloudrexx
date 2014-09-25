<?php

/**
 * JSON Adapter for Multisite
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Sudhir Parmar <sudhirparmar@cdnsol.com>
 * @version     4.0.0
 * @package     contrexx
 * @subpackage  Multisite
*/

namespace Cx\Core_Modules\MultiSite\Controller;

class MultiSiteJsonException extends \Exception {
    protected $object;
    protected $type = 'danger';

    /**
     * Overwriting the default Exception constructor
     * The default Exception constructor only accepts $message to be a string.
     * We do overwrite this here to also allow $message to be an array
     * that can then be sent back in the JsonData-response.
     */
    public function __construct($message = null, $code = 0, Exception $previous = null) {
        if (is_array($message)) {
            $msg = $message['message'];
            if (isset($message['object'])) {
                $this->object = $message['object'];
            }
            if (isset($message['type'])) {
                $this->type = $message['type'];
            }
        } else {
            $msg = $message;
        }
        parent::__construct($msg, $code, $previous);
        // overwrite $message to pass exception data to JsonData
        $this->message=$message;
    }

    public function getObject() {
        return $this->object;
    }

    public function getType() {
        return $this->type;
    }
}
/**
 * JSON Adapter for Multisite
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Sudhir Parmar <sudhirparmar@cdnsol.com>
 * @version     4.0.0
 * @package     contrexx
 * @subpackage  Multisite
*/
class JsonMultiSite implements \Cx\Core\Json\JsonAdapter {

    /**
     * @var boolean 
     */
    static $isIscRequest = false;
    
    /**
    * Returns the internal name used as identifier for this adapter
    * @return String Name of this adapter
    */
    public function getName() {
        return 'MultiSite';
    }

    /**
    * Returns an array of method names accessable from a JSON request
    * @return array List of method names
    */
    public function getAccessableMethods() {
        $multiSiteProtocol = (\Cx\Core\Setting\Controller\Setting::getValue('multiSiteProtocol') == 'mixed')? \Env::get('cx')->getRequest()->getUrl()->getProtocol(): \Cx\Core\Setting\Controller\Setting::getValue('multiSiteProtocol');
        return array(
            'signup'                => new \Cx\Core\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false),
            'email'                 => new \Cx\Core\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false),
            'address'               => new \Cx\Core\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false),
            'createWebsite'         => new \Cx\Core\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false, array($this, 'auth')),
            // protocol workaround as option multiSiteProtocol is not set on WEBSITE
            'createUser'            => new \Cx\Core\Access\Model\Entity\Permission(array('http', 'https'), array('post'), false, array($this, 'auth')),
            'updateUser'            => new \Cx\Core\Access\Model\Entity\Permission(array('http', 'https'), array('post'), false, array($this, 'auth')),
            'updateOwnUser'         => new \Cx\Core\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), true),
            'mapDomain'             => new \Cx\Core\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false, array($this, 'auth')),
            'unMapDomain'           => new \Cx\Core\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false, array($this, 'auth')),
            'updateDomain'          => new \Cx\Core\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false, array($this, 'auth')),
            'updateDefaultCodeBase' => new \Cx\Core\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), true, array($this, 'checkPermission')),
            'setWebsiteState'       => new \Cx\Core\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false, array($this, 'auth')),
            'updateWebsiteState'    => new \Cx\Core\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), true, array($this, 'checkPermission')),
            'ping'                  => new \Cx\Core\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false, array($this, 'auth')),
            'pong'                  => new \Cx\Core\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false, array($this, 'auth')),
            'setLicense'            => new \Cx\Core\Access\Model\Entity\Permission(array('http', 'https'), array('post'), false, array($this, 'auth')),
            'setupConfig'           => new \Cx\Core\Access\Model\Entity\Permission(array('http', 'https'), array('post'), false, array($this, 'auth')),
            'getDefaultWebsiteIp'   => new \Cx\Core\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false, array($this, 'auth')),
            'setDefaultLanguage'    => new \Cx\Core\Access\Model\Entity\Permission(array('http', 'https'), array('post'), false, array($this, 'auth')),
            'resetFtpPassword'      => new \Cx\Core\Access\Model\Entity\Permission(array('http', 'https'), array('post'), false, array($this, 'checkResetFtpPasswordAccess')),
            'updateServiceServerSetup' => new \Cx\Core\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false, array($this, 'auth')),
            'destroyWebsite'        => new \Cx\Core\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false, array($this, 'auth'))
        );  
    }

    /**
    * Returns all messages as string
    * @return String HTML encoded error messages
    */
    public function getMessagesAsString() {
        return '';
    }
    
    /**
     * Returns default permission as object
     * @return Object
     */
    public function getDefaultPermissions() {
        return null;
    }

    /**
     * Check if there is already an account present
     * by the supplied email address.
     * @param array $params supplied arguments from JsonData-request
     * @return void Returns nothing in case the email is not yet registered
     * @throws MultiSiteJsonException An array with further information about the already used email address
     */
    public function email($params) {
        global $_ARRAYLANG;

        if (!isset($params['post']['multisite_email_address'])) {
            return;
        }

        //check email validity
        if (!\FWValidator::isEmail($params['post']['multisite_email_address'])) {
            $this->loadLanguageData();
            throw new MultiSiteJsonException(array(
                'object'    => 'email',
                'type'      => 'danger',
                'message'   => $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_INVALID_EMAIL'],
            ));
        }

        if (!\User::isUniqueEmail($params['post']['multisite_email_address'])) {
            $this->loadLanguageData();

// TODO: set login url
            $loginUrl = '';
            $loginLink = '<a class="alert-link" href="'.$loginUrl.'" target="_blank">'.$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_LOGIN'].'</a>';
            throw new MultiSiteJsonException(array(
                'object'    => 'email',
                'type'      => 'info',
                'message'   => sprintf($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_EMAIL_IN_USE'], $loginLink),
            ));
        }
    }

    protected function loadLanguageData() {
        global $_ARRAYLANG, $objInit;
        $langData = $objInit->loadLanguageData('MultiSite');
        $_ARRAYLANG = array_merge($_ARRAYLANG, $langData);
    }

    public function address($params) {
        if (!isset($params['post']['multisite_address'])) {
            return;
        }
        try {
            \Cx\Core_Modules\MultiSite\Model\Entity\Website::validateName(contrexx_input2raw($params['post']['multisite_address']));
        } catch (\Cx\Core_Modules\MultiSite\Model\Entity\WebsiteException $e) {
            throw new MultiSiteJsonException(array(
                'object'    => 'address',
                'type'      => 'warning',
                'message'   => $e->getMessage()
            ));
        }
    }

    /**
    * function signup
    * @param post parameters
    * */
    public function signup($params) {
        global $_ARRAYLANG;

        // abort in case command has been requested without any data
        if (empty($params['post'])) {
            return;
        }

        if (\Cx\Core\Setting\Controller\Setting::getValue('sendSetupError')) {
            \DBG::activate(DBG_PHP | DBG_LOG_MEMORY);
        }

        // Validate address and email before starting with the actual sign up process.
        // Those methods throw an individual exception that can be parsed by the sign up form.
        // Therefore, those shall not be called in the below try/catch block
        $this->address($params);
        $this->email($params);

        try {
            // load text-variables of module MultiSite
            $this->loadLanguageData();

            // set website name and website theme
            $websiteName = contrexx_input2raw($params['post']['multisite_address']);
            $websiteThemeId = contrexx_input2raw($params['post']['themeId']);

            // create new user account
            $arrSettings = \User_Setting::getSettings();
            $user = $this->createUser(array('post' => array(
                'active'=> true,
                'email' => $params['post']['multisite_email_address'],
                'groups'=> explode(',', $arrSettings['assigne_to_groups']['value']),
            )));

            $objFWUser = \FWUser::getFWUserObject();
            $objUser = $objFWUser->objUser->getUser(intval($user['userId']));
            if (!$objUser) {
                throw new MultiSiteJsonException("Unable to load user account {$user['userId']}.");
            }

            // create a new CRM Contact and link it to the User account
            $objUser->objAttribute->first();
            while (!$objUser->objAttribute->EOF) {
                $arrProfile['fields'][] = array('special_type' => 'access_'.$objUser->objAttribute->getId());
                $arrProfile['data'][] = $objUser->getProfileAttribute($objUser->objAttribute->getId());
                $objUser->objAttribute->next();
            }
            
            $arrProfile['fields'][] = array('special_type' => 'access_email');
            $arrProfile['data'][] = $objUser->getEmail();
            $objCrmLibrary = new \Cx\Modules\Crm\Controller\CrmLibrary('Crm');
            $crmContactId = $objCrmLibrary->addCrmContact($arrProfile);
        
            // create a new order 
            $order = new \Cx\Modules\Order\Model\Entity\Order();
            $order->setContactId($crmContactId);

// TODO: Product ID should be supplied by POST-data.
//       If not set, then the ID should be taken from a MultiSite configuration option 'defaultProductId'
            $id = 1;
            $productRepository = \Env::get('em')->getRepository('Cx\Modules\Pim\Model\Entity\Product');
            $product = $productRepository->findOneBy(array('id' => $id));

            // create new subscription of selected product
            $subscriptionOptions = array(
                // set hard-coded to 'month'
                // later we shall use $_POST['renewalUnit'] instead
                'renewalUnit'       => \Cx\Modules\Pim\Model\Entity\Product::UNIT_MONTH,
                // set hard-coded to '1'
                // later we shall use $_POST['renewalQuantifier'] instead
                'renewalQuantifier' => 1,
                'websiteName'       => $websiteName,
                'customer'          => $objUser,
            );
            
            //pass the website's theme id to subscription option, if $themeId set
            if (!empty($websiteThemeId)) {
                $subscriptionOptions['themeId'] = $websiteThemeId;
            }
            
            $order->createSubscription($product, $subscriptionOptions);

            \Env::get('em')->persist($order);
            \Env::get('em')->flush();

            // create the website in the payComplete event
            $order->complete();

            // fetch the newly build website from the repository
            $websiteRepository = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website');
            $website   = $websiteRepository->findOneBy(array('name' => $websiteName));

// TODO: remove once setup process works flawlessly
            // send setup protocol anyway
            if (\Cx\Core\Setting\Controller\Setting::getValue('sendSetupError')) {
                $config = \Env::get('config');
                \Cx\Core\MailTemplate\Controller\MailTemplate::init('MultiSite');
                \Cx\Core\MailTemplate\Controller\MailTemplate::send(array(
                    'section' => 'MultiSite',
                    'key' => 'setupError',
                    'to' => $config['coreAdminEmail'],
                    'search' => array(
                        '[[ERROR]]',
                        '[[WEBSITE_NAME]]',
                        '[[CUSTOMER_EMAIL]]',
                        '[[DBG_LOG]]',
                    ),
                    'replace' => array(
                        'SETUP SUCCESSFUL',
                        $websiteName,
                        $params['post']['multisite_email_address'],
                        join("\n", \DBG::getMemoryLogs()),
                    ),
                ));
            }
            $websiteLink = '<a href="'.ComponentController::getApiProtocol().$website->getBaseDn()->getName().'" target="_blank">'.$website->getBaseDn()->getName().'</a>';
            return array(
                'status'    => 'success',
                'message'   => sprintf($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_CREATED'], $websiteLink),
            );
        } catch (\Exception $e) {
            $config = \Env::get('config');
            if (\Cx\Core\Setting\Controller\Setting::getValue('sendSetupError')) {
                \Cx\Core\MailTemplate\Controller\MailTemplate::init('MultiSite');
                \Cx\Core\MailTemplate\Controller\MailTemplate::send(array(
                    'section' => 'MultiSite',
                    'key' => 'setupError',
                    'to' => $config['coreAdminEmail'],
                    'search' => array(
                        '[[ERROR]]',
                        '[[WEBSITE_NAME]]',
                        '[[CUSTOMER_EMAIL]]',
                        '[[DBG_LOG]]',
                    ),
                    'replace' => array(
                        $e->getMessage(),
                        $websiteName,
                        $params['post']['multisite_email_address'],
                        join("\n", \DBG::getMemoryLogs()),
                    ),
                ));
            }
            \DBG::msg($e->getMessage());
            throw new MultiSiteJsonException(array(
                'object'    => 'form',
                'type'      => 'danger',
                'message'   => sprintf($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_CREATION_ERROR'], contrexx_raw2xhtml($objUser->getEmail())),
            ));
        }
    }

    /**
     * Creates a new website
     * @param type $params  
    */
    public function createWebsite($params) {
        if (\Cx\Core\Setting\Controller\Setting::getValue('sendSetupError')) {
            \DBG::activate(DBG_PHP | DBG_LOG_MEMORY);
        }

// TODO: what do we actually need the language data for? We should load the language data at the certain place where it is actually being used
        $this->loadLanguageData('MultiSite');
        
        $objFWUser   = \FWUser::getFWUserObject();
        $objUser     = $objFWUser->objUser->getUser(contrexx_input2raw($params['post']['userId']));
        $websiteId   = isset($params['post']['websiteId']) ? contrexx_input2raw($params['post']['websiteId']) : '';
        $websiteName = isset($params['post']['websiteName']) ? contrexx_input2raw($params['post']['websiteName']) : '';
        $themeId     = isset($params['post']['themeId']) ? contrexx_input2raw($params['post']['themeId']) : '';
        
        $basepath = \Cx\Core\Setting\Controller\Setting::getValue('websitePath');
        $websiteServiceServer = null;
        if (\Cx\Core\Setting\Controller\Setting::getValue('mode') == ComponentController::MODE_MANAGER) {
            //get default service server
            $defaultWebsiteServiceServer = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer')
            ->findBy(array('id' => \Cx\Core\Setting\Controller\Setting::getValue('defaultWebsiteServiceServer')));
            $websiteServiceServer = $defaultWebsiteServiceServer[0];
        }

        try {
            $objWebsite = new \Cx\Core_Modules\MultiSite\Model\Entity\Website($basepath, $websiteName, $websiteServiceServer, $objUser, false, $themeId);
            \Env::get('em')->persist($objWebsite);
            if ($websiteId) {
                $objWebsite->setId($websiteId);
                $metadata = \Env::get('em')->getClassMetaData(get_class($objWebsite));
                $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
            }
            \Env::get('em')->flush();
            return $objWebsite->setup($params['post']['options']);
        } catch (\Cx\Core_Modules\MultiSite\Model\Entity\WebsiteException $e) {
            throw new MultiSiteJsonException(array(
                'log'      => \DBG::getMemoryLogs(),
                'message'   => $e->getMessage(),
            ));
        }
    }

    public function createUser($params) {
        if (\Cx\Core\Setting\Controller\Setting::getValue('sendSetupError')) {
            if (\DBG::getMode() ^ DBG_PHP || \DBG::getMode() ^ DBG_LOG_MEMORY) {
                \DBG::activate(DBG_PHP | DBG_LOG_MEMORY);
            }
        }

        try {
            if (empty($params['post'])) {
                throw new MultiSiteJsonException('Invalid arguments specified for command JsonMultiSite::createUser.');
            }

            $objUser = new \Cx\Core_Modules\MultiSite\Model\Entity\User();
            if (!empty($params['post']['userId'])) {
                $objUser->setMultiSiteId($params['post']['userId']);
            }
            $objUser->setEmail(!empty($params['post']['email']) ? contrexx_input2raw($params['post']['email']) : '');
            $objUser->setActiveStatus(!empty($params['post']['active']) ? (bool)$params['post']['active'] : false);
            $objUser->setAdminStatus(!empty($params['post']['admin']) ? (bool)$params['post']['admin'] : false);
            $objUser->setPassword(\User::make_password(8,true));
            
            if (!empty($params['post']['groups'])) {
                $objUser->setGroups($params['post']['groups']);
            }

            if (!$objUser->store()) {
                \DBG::msg('Adding user failed: '.$objUser->getErrorMsg());
                throw new MultiSiteJsonException($objUser->getErrorMsg());
            } else {
                \DBG::msg('User successfully created');
                return array('userId' => $objUser->getId());
            }
        } catch (\Exception $e) {
            throw new MultiSiteJsonException(array(
                'log'       => \DBG::getMemoryLogs(),
                'message'   => $e->getMessage(),
            ));
        }
    }

    /**
     * Update an existing user account
     * @param array $params POST-data based on with the account shall be updated to
     * @return  boolean     TRUE on success, FALSE on failure.
     */
    public function updateUser($params) {
        if (empty($params['post'])) {
            throw new MultiSiteJsonException(array(
                'object'    => 'form',
                'type'      => 'danger',
                'message'   => 'No data supplied',
            ));
        }

        $objFWUser = \FWUser::getFWUserObject();
        $data = $params['post'];
        
        switch(\Cx\Core\Setting\Controller\Setting::getValue('mode')) {
            case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_MANAGER:
            case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_HYBRID:
            case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_SERVICE:
                $objUser = $objFWUser->objUser->getUser(intval($params['post']['userId']));
                if (!$objUser) {
                    throw new MultiSiteJsonException(array(
                        'object'    => 'form',
                        'type'      => 'danger',
                        'message'   => 'Unknown user account',
                    ));
                }
                break;
            case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_WEBSITE:
                $websiteUserId = \Cx\Core\Setting\Controller\Setting::getValue('websiteUserId');
                $objUser = $objFWUser->objUser->getUser(intval($websiteUserId));
                if (!$objUser) {
                    throw new MultiSiteJsonException(array(
                        'object'    => 'form',
                        'type'      => 'danger',
                        'message'   => 'Unknown user account',
                    ));
                }
                break;
            default:
                break;
        }
        
        // set account data
        if (isset($data['multisite_user_account_username'])) {
            $objUser->setUsername(trim(contrexx_input2raw($data['multisite_user_account_username'])));
        }
        if (isset($data['multisite_user_account_email'])) {
            $objUser->setEmail(trim(contrexx_input2raw($data['multisite_user_account_email'])));
        }
        if (isset($data['multisite_user_account_frontend_language'])) {
            $objUser->setFrontendLanguage(intval($data['multisite_user_account_frontend_language']));
        }
        if (isset($data['multisite_user_account_backend_language'])) {
            $objUser->setBackendLanguage(intval($data['multisite_user_account_backend_language']));
        }
        if (isset($data['multisite_user_account_email_access']) && $objUser->isAllowedToChangeEmailAccess()) {
            $objUser->setEmailAccess(contrexx_input2raw($data['multisite_user_account_email_access']));
        }
        if (isset($data['multisite_user_account_profile_access']) && $objUser->isAllowedToChangeProfileAccess()) {
            $objUser->setProfileAccess(contrexx_input2raw($data['multisite_user_account_profile_access']));
        }

        // set profile data
        if (isset($data['multisite_user_profile_attribute'])) {
            $objUser->setProfile($data['multisite_user_profile_attribute']);
        }

        // set md5 hashed password
        if (isset($data['multisite_user_md5_password'])) {
            $objUser->setHashedPassword(contrexx_input2raw($data['multisite_user_md5_password']));
        }

        // set new plain text password
        if (!empty($data['multisite_user_account_password'])) {
            $password = contrexx_input2raw($data['multisite_user_account_password']);
            $confirmedPassword = !empty($data['multisite_user_account_password_confirmed']) ? contrexx_input2raw($data['multisite_user_account_password_confirmed']) : '';
            if (!$objUser->setPassword($password, $confirmedPassword)) {
                \DBG::msg("JsonMultiSite (updateUser): Failed to update {$objUser->getId()}: ".join("\n", $objUser->getErrorMsg()));
                throw new MultiSiteJsonException(array(
                    'object'    => 'password',
                    'type'      => 'danger',
                    'message'   => join("\n", $objUser->getErrorMsg()),
                ));
            }
        }

        if (!$objUser->store()) {
            \DBG::msg("JsonMultiSite (updateUser): Failed to update {$objUser->getId()}: ".join("\n", $objUser->getErrorMsg()));
            throw new MultiSiteJsonException(array(
                'object'    => 'form',
                'type'      => 'danger',
                'message'   => join("\n", $objUser->getErrorMsg()),
            ));
        }
                
        \DBG::msg("JsonMultiSite (updateUser): User {$objUser->getId()} successfully updated.");
        return array('status' => 'success');
    } 

    /**
     * Update the user account of the signed-in user
     * @param array $params POST-data based on with the account shall be updated to
     * @return  boolean     TRUE on success, FALSE on failure.
     */
    public function updateOwnUser($params) {
        $objFWUser = \FWUser::getFWUserObject();
        $objUser = $objFWUser->objUser;
        if (!$objUser->login()) {
            throw new MultiSiteJsonException(array(
                'object'    => 'form',
                'type'      => 'danger',
                'message'   => 'Operation denied',
            ));
        }

        // NOTE: This command is used in frontend,
        //       Therefore, all users that are logged-in
        //       are allowed to update their profile.
        //       The following permission check has been
        //       deactivated therefore.
        //
        // Only administrators or users with sufficient permissions
        // may update their own account.
        /*if (!\Permission::hasAllAccess() && !\Permission::checkAccess(31, 'static', true)) {
            throw new MultiSiteJsonException(array(
                'object'    => 'form',
                'type'      => 'danger',
                'message'   => 'Operation denied',
            ));
        }*/

        $params['post']['userId'] = $objUser->getId();
        return $this->updateUser($params);
    } 

    /**
     *  callback authentication for verifing secret key and installation id based on mode
     * 
     * @return boolean
     */
    public function auth(array $params = array()) 
    {
        $authenticationValue = isset($params['post']['auth']) ? json_decode($params['post']['auth'], true) : '';

        if (   empty($authenticationValue)
            || !is_array($authenticationValue)
            || !isset($authenticationValue['sender'])
            || !isset($authenticationValue['key'])
        ) {
            return false;
        }
        $config = \Env::get('config');
        $installationId = $config['installationId'];
        switch (\Cx\Core\Setting\Controller\Setting::getValue('mode')) {
            case ComponentController::MODE_MANAGER:
                try {
                    $WebsiteServiceServerRepository = \Env::get('em')->getRepository('\Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer');
                    $objWebsiteService = $WebsiteServiceServerRepository->findOneBy(array('hostname' => $authenticationValue['sender']));
                    if (!$objWebsiteService) {
                        return false;
                    }
                    $secretKey = $objWebsiteService->getSecretKey();
                } catch(\Exception $e) {
                    \DBG::msg($e->getMessage());
                    return false;
                }
                break;

            case ComponentController::MODE_SERVICE:
            case ComponentController::MODE_HYBRID:
                //Check if the sender is manager or not
                if ($authenticationValue['sender'] == \Cx\Core\Setting\Controller\Setting::getValue('managerHostname')) {
                    $secretKey = \Cx\Core\Setting\Controller\Setting::getValue('managerSecretKey');
                } else {
                    try {
                        $domainRepo = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Domain');
                        $domain     = $domainRepo->findOneBy(array('name' => $authenticationValue['sender']));
                        if (!$domain || !$domain->getWebsite()) {
                            return false;
                        }
                        $secretKey  = $domain->getWebsite()->getSecretKey();
                    } catch (\Exception $e) {
                        \DBG::msg($e->getMessage());
                        return false;
                    }
                }
                break;
                
            case ComponentController::MODE_WEBSITE:
                $secretKey = \Cx\Core\Setting\Controller\Setting::getValue('serviceSecretKey');
                break;
        }
        
        if (md5($secretKey.$installationId) === $authenticationValue['key']) {
            self::$isIscRequest = true;
            return true;
        }
        
        return false;
    }

    /**
     * Callback authentication for checking the user's access permission
     * 
     * @return boolean
     */
    public function checkPermission() 
    {
        if (\Permission::checkAccess(183, 'static', true)) {
            return true;
        }
        
        return false;
    }
    
    /**
     *  Get the Authentication Object
     * 
     * @param String $secretKey
     * @param String $remoteInstallationId
     * 
     * @return json
     */
    public static function getAuthenticationObject($secretKey, $remoteInstallationId) 
    {
        $key = md5($secretKey . $remoteInstallationId);
        $config = \Env::get('config');

        return json_encode(array(
            'key'     => $key,
            'sender' => $config['domainUrl'],
        ));
    }
    /**
     *  Get the auto-generated SecretKey
     * 
     * @return string 
     */
    public static function generateSecretKey(){
        return bin2hex(openssl_random_pseudo_bytes(16));    
    }
    
     /**
     * Map the website domain
     * 
     * @param type $params
     * @return type
     */
    public function mapDomain($params) {
        if (   empty($params['post'])
            || empty($params['post']['domainName'])
            || empty($params['post']['auth'])
            || empty($params['post']['componentType'])
            || !isset($params['post']['componentId'])
            || !isset($params['post']['coreNetDomainId'])
        ) {
            throw new MultiSiteJsonException('JsonMultiSite::mapDomain() failed: Insufficient mapping information supplied.');
        }

        $authenticationValue = json_decode($params['post']['auth'], true);
        if (empty($authenticationValue) || !is_array($authenticationValue)) {
            throw new MultiSiteJsonException('JsonMultiSite::mapDomain() failed: Insufficient mapping information supplied.');
        }

        try {
            // create a new domain entity that shall be used for the mapping
            $objDomain = new \Cx\Core_Modules\MultiSite\Model\Entity\Domain($params['post']['domainName']);

            $domainRepo = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Domain');
            switch ($params['post']['componentType']) {
                case ComponentController::MODE_SERVICE:
                    // If componentType is MODE_SERVICE, then we are about to
                    // map the domain to a Service Server. Therefore, we have
                    // to fetch the ID of the Service Server the domain shall
                    // be mapped to.
                    $websiteServiceServer = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer')
                        ->findOneBy(array('hostname' => $authenticationValue['sender']));
                    if (!$websiteServiceServer) {
                        throw new MultiSiteJsonException('JsonMultiSite::mapDomain() failed: Unkown Service Server: '.$authenticationValue['sender']);
                    }
                    $componentId = $websiteServiceServer->getId();
                    break;

                case ComponentController::MODE_WEBSITE:
                    switch (\Cx\Core\Setting\Controller\Setting::getValue('mode')) {
                        case ComponentController::MODE_MANAGER:
                            // componentId is the ID of a Website that the domain shall be mapped to
                            $componentId = $params['post']['componentId'];
                            $website = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website')->findOneById($componentId);
                            if (!$website) {
                                throw new MultiSiteJsonException('JsonMultiSite::mapDomain() failed: Unkown Website-ID: '.$componentId);
                            }
                            break;

                        case ComponentController::MODE_HYBRID:
                        case ComponentController::MODE_SERVICE:
                            // componentId is the ID of the Website the request was made from
                            $objWebsiteDomain = $domainRepo->findOneBy(array('name' => $authenticationValue['sender']));
                            if (!$objWebsiteDomain) {
                                throw new MultiSiteJsonException('JsonMultiSite::mapDomain() failed: Unkown Website: '.$authenticationValue['sender']);
                            }
                            $website = $objWebsiteDomain->getWebsite();
                            if (!$website) {
                                throw new MultiSiteJsonException('JsonMultiSite::mapDomain() failed: Unkown Website: '.$authenticationValue['sender']);
                            }
                            $componentId = $website->getId();
                            break;

                        default:
                            throw new MultiSiteJsonException('JsonMultiSite::mapDomain() failed: Command not available for mode: '.\Cx\Core\Setting\Controller\Setting::getValue('mode'));
                            break;
                    }

                    $website->mapDomain($objDomain);
// TODO: will this trigger a request to the service server, then update the domains there and then again trigger a request back to the manager and will then end in an infinite loop????
                    \Env::get('em')->persist($website);
                    break;

                case ComponentController::MODE_MANAGER:
                case ComponentController::MODE_HYBRID:
                default:
                    // If componentType is MANAGER or HYBRID, then we are about to map
                    // a Net-domain to the own system. Therefore setting componentId to null 
                    // will reference the domain to be mapped to ourself.
                    // Using NULL instead of 0 is important. As to the database scheme
                    // the domain map/unmap process would not work properly, if we use 0 at this point.
                    $componentId = null;
                    break;
            }

            $objDomain->setComponentType($params['post']['componentType']);
            $objDomain->setComponentId($componentId);
            $objDomain->setCoreNetDomainId($params['post']['coreNetDomainId']);
            \Env::get('em')->persist($objDomain);
            \Env::get('em')->flush();
            return true;
        } catch (\Exception $e) {
            throw new MultiSiteJsonException($e->getMessage());
        }
    }        
    
    /**
     * Unmap the website domain
     * 
     * @param type $params
     * @return type
     */
    public function unMapDomain($params)
    {
        if (   empty($params['post'])
            || empty($params['post']['domainName'])
            || empty($params['post']['auth'])
            || empty($params['post']['componentType'])
            || !isset($params['post']['componentId'])
            || !isset($params['post']['coreNetDomainId'])
        ) {
            throw new MultiSiteJsonException('JsonMultiSite::unMapDomain() failed: Insufficient mapping information supplied.');
        }

        $authenticationValue = json_decode($params['post']['auth'], true);
        if (empty($authenticationValue) || !is_array($authenticationValue)) {
            throw new MultiSiteJsonException('JsonMultiSite::unMapDomain() failed: Insufficient mapping information supplied.');
        }

        try {
            $website = null;

            $domainRepo = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Domain');
            switch ($params['post']['componentType']) {
                case ComponentController::MODE_SERVICE:
                    // If componentType is MODE_SERVICE, then we are about to
                    // unmap the domain from a Service Server. Therefore, we have
                    // to fetch the ID of the Service Server the domain shall
                    // be unmapped from.
                    $websiteServiceServer = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer')
                        ->findOneBy(array('hostname' => $authenticationValue['sender']));
                    if (!$websiteServiceServer) {
                        throw new MultiSiteJsonException('JsonMultiSite::unMapDomain() failed: Unkown Service Server: '.$authenticationValue['sender']);
                    }
                    $componentId = $websiteServiceServer->getId();
                    break;

                case ComponentController::MODE_WEBSITE:
                    switch (\Cx\Core\Setting\Controller\Setting::getValue('mode')) {
                        case ComponentController::MODE_MANAGER:
                            // componentId is the ID of a Website that the domain shall be unmapped from
                            $componentId = $params['post']['componentId'];
                            $website = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website')->findOneById($componentId);
                            if (!$website) {
                                throw new MultiSiteJsonException('JsonMultiSite::unMapDomain() failed: Unkown Website-ID: '.$componentId);
                            }
                            break;

                        case ComponentController::MODE_HYBRID:
                        case ComponentController::MODE_SERVICE:
                            // componentId is the ID of the Website the request was made from
                            $objWebsiteDomain = $domainRepo->findOneBy(array('name' => $authenticationValue['sender']));
                            if (!$objWebsiteDomain) {
                                throw new MultiSiteJsonException('JsonMultiSite::unMapDomain() failed: Unkown Website: '.$authenticationValue['sender']);
                            }
                            $website = $objWebsiteDomain->getWebsite();
                            if (!$website) {
                                throw new MultiSiteJsonException('JsonMultiSite::unMapDomain() failed: Unkown Website: '.$authenticationValue['sender']);
                            }
                            $componentId = $website->getId();
                            break;

                        default:
                            throw new MultiSiteJsonException('JsonMultiSite::unMapDomain() failed: Command not available for mode: '.\Cx\Core\Setting\Controller\Setting::getValue('mode'));
                            break;
                    }
                    break;

                case ComponentController::MODE_MANAGER:
                case ComponentController::MODE_HYBRID:
                default:
                    // If componentType is MANAGER or HYBRID, then we are about to unmap
                    // a Net-domain from the own system. Therefore setting componentId to null 
                    // will reference the domain to be mapped to ourself.
                    $componentId = null;
                    break;
            }
            $objDomain = $domainRepo->findOneBy(array(
                'name'              => $params['post']['domainName'],
                'componentType'     => $params['post']['componentType'],
                'componentId'       => $componentId,
                'coreNetDomainId'   => $params['post']['coreNetDomainId'],
            ));

            if (!$objDomain) {
                throw new MultiSiteJsonException('JsonMultiSite::unMapDomain() failed: Domain to remove not found.');
            }

            if ($website && $objDomain->getWebsite() == $website) {
                // Website::unMapDomain() does also call remove() on the entity manager
                $website->unMapDomain($objDomain);
            } else {
                \Env::get('em')->remove($objDomain);
            }
            \Env::get('em')->flush();
            return true;
        } catch (\Exception $e) {
            throw new MultiSiteJsonException($e->getMessage());
        }
    }

    /**
     * update the default codeBase
     * 
     * @param array $params
     * 
     * @return string
     */
    public function updateDefaultCodeBase($params) 
    {
        global $_ARRAYLANG,$objInit;
        $langData = $objInit->loadLanguageData('MultiSite');
        $_ARRAYLANG = array_merge($_ARRAYLANG, $langData);
        if (!empty($params['post'])) {
            \Cx\Core\Setting\Controller\Setting::init('MultiSite', 'websiteSetup','FileSystem');
            \Cx\Core\Setting\Controller\Setting::set('defaultCodeBase',$params['post']['defaultCodeBase']);
            if (\Cx\Core\Setting\Controller\Setting::update('defaultCodeBase')) {
                return $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_DEFAULTCODEBASE_SUCCESSFUL_CREATION'];
            }
        }
    }
    /**
     * update the domain alias
     * 
     * @param array $params
     * 
     * @return string
     */
    public function updateDomain($params) {
        if (   empty($params['post'])
            || empty($params['post']['domainName'])
            || empty($params['post']['auth'])
            || empty($params['post']['componentType'])
            || !isset($params['post']['componentId'])
            || !isset($params['post']['coreNetDomainId'])
        ) {
            \DBG::dump($params);
            throw new MultiSiteJsonException('JsonMultiSite::updateDomain() on '.\Cx\Core\Setting\Controller\Setting::getValue('mode').' failed: Insufficient mapping information supplied: '.var_export($params, true));
        }

        $authenticationValue = json_decode($params['post']['auth'], true);
        if (empty($authenticationValue) || !is_array($authenticationValue)) {
            \DBG::dump($params);
            throw new MultiSiteJsonException('JsonMultiSite::updateDomain() on '.\Cx\Core\Setting\Controller\Setting::getValue('mode').' failed: Insufficient mapping information supplied.'.var_export($params, true));
        }

        try {
            $website = null;

            $domainRepo = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Domain');
            switch ($params['post']['componentType']) {
                case ComponentController::MODE_SERVICE:
                    // If componentType is MODE_SERVICE, then we are about to
                    // update the domain of a Service Server. Therefore, we have
                    // to fetch the ID of the Service Server the domain shall
                    // be updated of.
                    $websiteServiceServer = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer')
                        ->findOneBy(array('hostname' => $authenticationValue['sender']));
                    if (!$websiteServiceServer) {
                        throw new MultiSiteJsonException('JsonMultiSite::updateDomain() failed: Unkown Service Server: '.$authenticationValue['sender']);
                    }
                    $componentId = $websiteServiceServer->getId();
                    break;

                case ComponentController::MODE_WEBSITE:
                    switch (\Cx\Core\Setting\Controller\Setting::getValue('mode')) {
                        case ComponentController::MODE_MANAGER:
                            // componentId is the ID of a Website that the domain shall be updated of
                            $componentId = $params['post']['componentId'];
                            $website = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website')->findOneById($componentId);
                            if (!$website) {
                                throw new MultiSiteJsonException('JsonMultiSite::updateDomain() failed: Unkown Website-ID: '.$componentId);
                            }
                            break;

                        case ComponentController::MODE_HYBRID:
                        case ComponentController::MODE_SERVICE:
                            // componentId is the ID of the Website the request was made from
                            $objWebsiteDomain = $domainRepo->findOneBy(array('name' => $authenticationValue['sender']));
                            if (!$objWebsiteDomain) {
                                throw new MultiSiteJsonException('JsonMultiSite::updateDomain() failed: Unkown Website: '.$authenticationValue['sender']);
                            }
                            $website = $objWebsiteDomain->getWebsite();
                            if (!$website) {
                                throw new MultiSiteJsonException('JsonMultiSite::updateDomain() failed: Unkown Website: '.$authenticationValue['sender']);
                            }
                            $componentId = $website->getId();
                            break;

                        default:
                            throw new MultiSiteJsonException('JsonMultiSite::updateDomain() failed: Command not available for mode: '.\Cx\Core\Setting\Controller\Setting::getValue('mode'));
                            break;
                    }
                    break;

                case ComponentController::MODE_MANAGER:
                case ComponentController::MODE_HYBRID:
                default:
                    // If componentType is MANAGER or HYBRID, then we are about to update
                    // a Net-domain from the own system. Therefore setting componentId to null 
                    // will reference the domain to be mapped to ourself.
                    $componentId = null;
                    break;
            }
            $objDomain = $domainRepo->findOneBy(array(
                'componentType'     => $params['post']['componentType'],
                'componentId'       => $componentId,
                'coreNetDomainId'   => $params['post']['coreNetDomainId'],
            ));

            if (!$objDomain) {
                throw new MultiSiteJsonException('JsonMultiSite::updateDomain() failed: Domain to update not found.');
            }

            $objDomain->setName($params['post']['domainName']);
            \Env::get('em')->flush();
            return true;
        } catch (\Exception $e) {
            throw new MultiSiteJsonException($e->getMessage());
        }
    }

    /**
     * set Website State
     * 
     * @param array $params
     * 
     */
    public function setWebsiteState($params) {
         if (!empty($params['post'])) {
            $webRepo = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website');
            $website = $webRepo->findOneById($params['post']['websiteId']);
            if (!$website) {
                throw new MultiSiteJsonException('JsonMultiSite::setWebsiteState() failed: Website by ID '.$params['post']['websiteId'].' not found.');
            }
            $website->setStatus($params['post']['status']);
            \Env::get('em')->persist($website);
            \Env::get('em')->flush();
            return true;
        }
    }
    
     /**
     * update Website State
     * 
     * @param array $params
     * 
     */
     public function updateWebsiteState($params) {
         
        global $_ARRAYLANG, $objInit;
        
        $langData = $objInit->loadLanguageData('MultiSite');
        $_ARRAYLANG = array_merge($_ARRAYLANG, $langData);
        if (!empty($params['post'])) {
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode')) {
                case ComponentController::MODE_MANAGER:
                case ComponentController::MODE_HYBRID:
                    if ($this->setWebsiteState($params)){
                        return $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_WEBSITE_STATUS_CHANGED_SUCCESSFUL'];
                    }
                    break;

                case ComponentController::MODE_SERVICE:
                    //find User's Website
                    $webRepo   = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website');
                    $website   = $webRepo->findOneById($params['post']['websiteId']);
                    $params    = array(
                        'websiteId'   => $params['post']['websiteId'],
                        'status'      => $params['post']['status'],
                    );
                    \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::executeCommandOnWebsite('setWebsiteState', $params, $website);
                    break;

                case ComponentController::MODE_WEBSITE:
                    break;
            }
        }
    }
    
    /**
     * Set the license
     * 
     * @global ADOConnection $objDatabase
     * @param array $params
     * 
     * @return boolean
     * @throws MultiSiteJsonException
     */
    public function setLicense($params) {
        global $objDatabase;
        
        try {
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode')) {
                case ComponentController::MODE_SERVICE:
                    $webRepo = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website');
                    $website = $webRepo->findOneById($params['post']['websiteId']);
                    $resp = self::executeCommandOnWebsite('setLicense', array('legalComponents' => $params['post']['legalComponents']), $website);                   
                    if ($resp && $resp->data->status == 'success') {
                        return array('status' => 'success');
                    }
                    break;
                case ComponentController::MODE_WEBSITE:
                    $license = \Env::get('cx')->getLicense();
                    if (isset($params['post']['state'])) {
                        $license->setState($params['post']['state']);
                    }
                    if (isset($params['post']['validTo'])) {
                        $license->setValidToDate($params['post']['validTo']);
                    }
                    if (isset($params['post']['updateInterval'])) {
                        $license->setUpdateInterval($params['post']['updateInterval']);
                    }
                    if (isset($params['post']['dashboardMessages'])) {
                        $dashboardMessages = array();
                        foreach ($params['post']['dashboardMessages'] as $lang => $value) {
                            $dashboardMessages[] = new \Cx\Core_Modules\License\Message($lang, $value['text'], $value['type'], $value['link'], $value['linkTarget'], true);
                        }
                        $license->setDashboardMessages($dashboardMessages);
                    }
                    if (isset($params['post']['legalComponents'])) {
                        $license->setAvailableComponents($params['post']['legalComponents']);
                        $license->setLegalComponents($params['post']['legalComponents']);
                    }
                    if (isset($params['post']['isUpgradable'])) {
                        $license->setIsUpgradable($params['post']['isUpgradable']);
                    }
                    try {
                        $license->save($objDatabase);
                        return array('status' => 'success');
                    } catch (\Exception $e) {
                        throw new MultiSiteJsonException('Unable to save the setup license'.$e->getMessage());
                    }
                    break;
            }
            return array('status' => 'error');
        } catch (\Exception $e) {
            throw new MultiSiteJsonException('Unable to setup license: '.$e->getMessage());
        }
    }

    /**
     * Returns the $isIscRequest value
     * 
     * @return boolean
     */
    public static function isIscRequest() {
        return self::$isIscRequest;
    }
    
    /**
     * Return the status message
     * 
     * @return array
     */
    public function ping() 
    {
        global $_CONFIG;
        
        //Check the system is in maintenance mode or not
        if ($_CONFIG['systemStatus'] !='on') {
            return array('status' => 'error', 'message' => 'Service Server is currently in maintenance mode');
        }
        
        $resp = self::executeCommandOnManager('pong');
        if ($resp && $resp->status == 'success' && $resp->data->status == 'success') {
            return array('status' => 'success');
        }
        
        return array('status' => 'error', 'message' => 'Reverse connection failed');
    }
    
    /**
     * Return the status message
     * 
     * @return array
     */
    public function pong()
    {
        return array('status' => 'success');
    }
    
    public static function executeCommand($host, $command, $params, $secretKey, $installationId, $httpAuth) {

        $params['auth'] = self::getAuthenticationObject($secretKey, $installationId);
        $objJsonData = new \Cx\Core\Json\JsonData();
        return $objJsonData->getJson(\Cx\Core_Modules\MultiSite\Controller\ComponentController::getApiProtocol() . $host . '/cadmin/index.php?cmd=JsonData&object=MultiSite&act=' . $command, $params, false, '', $httpAuth);
    }

    /*
     * This method will be used by the Website Service to execute commands on the Website Manager
     * Fetch connection data to Manager and pass it to the method executeCommand()
     */

    public static function executeCommandOnManager($command, $params = array()) {

        if (!in_array(\Cx\Core\Setting\Controller\Setting::getValue('mode'), array(ComponentController::MODE_MANAGER, ComponentController::MODE_SERVICE, ComponentController::MODE_HYBRID))) {
            throw new MultiSiteJsonException('Command'.__METHOD__.'executeCommandOnWebsite is only available in MultiSite-mode MANAGER, SERVICE or HYBRID.');
        }
        if (\Cx\Core\Setting\Controller\Setting::getValue('mode') == ComponentController::MODE_MANAGER) {
\DBG::msg('JsonMultiSite: execut directly on manager');
            $config = \Env::get('config');
            $params['auth'] = json_encode(array('sender' => $config['domainUrl']));
            try {
                $objJsonMultiSite = new self();
                $result = $objJsonMultiSite->$command(array('post' => $params));
                // Convert $result (which is an array) into an object
                // as JsonData->getJson (called by self::executeCommand())
                // would do/return that.
                return json_decode(json_encode(array('status' => 'success', 'data' => $result)));
            } catch (\Exception $e) {
                throw new MultiSiteJsonException($e->getMessage());
            }
        }
        $host = \Cx\Core\Setting\Controller\Setting::getValue('managerHostname');
        $installationId = \Cx\Core\Setting\Controller\Setting::getValue('managerInstallationId');
        $secretKey = \Cx\Core\Setting\Controller\Setting::getValue('managerSecretKey');
        $httpAuth = array(
            'httpAuthMethod' => \Cx\Core\Setting\Controller\Setting::getValue('managerHttpAuthMethod'),
            'httpAuthUsername' => \Cx\Core\Setting\Controller\Setting::getValue('managerHttpAuthUsername'),
            'httpAuthPassword' => \Cx\Core\Setting\Controller\Setting::getValue('managerHttpAuthPassword'),
        );
        return self::executeCommand($host, $command, $params, $secretKey, $installationId, $httpAuth);
    }

    /*
     * This method will be used by a Websites to execute commands on its Website Service
     * Fetch connection data to Service and pass it to the method executeCommand()
     */

    public static function executeCommandOnMyServiceServer($command, $params) {

        if (!in_array(\Cx\Core\Setting\Controller\Setting::getValue('mode'), array(ComponentController::MODE_WEBSITE))) {
            throw new MultiSiteJsonException('Command'.__METHOD__.'executeCommandOnWebsite is only available in MultiSite-mode WEBSITE.');
        }
        $host = \Cx\Core\Setting\Controller\Setting::getValue('serviceHostname');
        $installationId = \Cx\Core\Setting\Controller\Setting::getValue('serviceInstallationId');
        $secretKey = \Cx\Core\Setting\Controller\Setting::getValue('serviceSecretKey');
        $httpAuth = array(
            'httpAuthMethod' => \Cx\Core\Setting\Controller\Setting::getValue('serviceHttpAuthMethod'),
            'httpAuthUsername' => \Cx\Core\Setting\Controller\Setting::getValue('serviceHttpAuthUsername'),
            'httpAuthPassword' => \Cx\Core\Setting\Controller\Setting::getValue('serviceHttpAuthPassword'),
        );
        return self::executeCommand($host, $command, $params, $secretKey, $installationId, $httpAuth);
    }

    /*
     * This method will be used by the Website Manager to execute commands on the Website Service
     * Fetch connection data to Service and pass it to the method executeCommand()
     */

    public static function executeCommandOnServiceServerOfWebsite($command, $params, $website) {

        if (!in_array(\Cx\Core\Setting\Controller\Setting::getValue('mode'), array(ComponentController::MODE_MANAGER))) {
            throw new MultiSiteJsonException('Command'.__METHOD__.'executeCommandOnWebsite is only available in MultiSite-mode MANAGER.');
        }
        $websiteServiceServer = $website->getWebsiteServiceServer();
        $host = $websiteServiceServer->getHostname();
        $installationId = $websiteServiceServer->getInstallationId();
        $secretKey = $websiteServiceServer->getSecretKey();
        $httpAuth = array(
            'httpAuthMethod' => $websiteServiceServer->getHttpAuthMethod(),
            'httpAuthUsername' => $websiteServiceServer->getHttpAuthUsername(),
            'httpAuthPassword' => $websiteServiceServer->getHttpAuthPassword(),
        );
        return self::executeCommand($host, $command, $params, $secretKey, $installationId, $httpAuth);
    }

    /*
     * This method will be used by the Website Manager to execute commands on a Website Service
     * Fetch connection data to Service and pass it to the method executeCommand():
     */

    public static function executeCommandOnServiceServer($command, $params, $websiteServiceServer) {
        
        if (!in_array(\Cx\Core\Setting\Controller\Setting::getValue('mode'), array(ComponentController::MODE_MANAGER))) {
            throw new MultiSiteJsonException('Command'.__METHOD__.'executeCommandOnWebsite is only available in MultiSite-mode MANAGER.');
        }
        $host = $websiteServiceServer->getHostname();
        $installationId = $websiteServiceServer->getInstallationId();
        $secretKey = $websiteServiceServer->getSecretKey();
        $httpAuth = array(
            'httpAuthMethod' => $websiteServiceServer->getHttpAuthMethod(),
            'httpAuthUsername' => $websiteServiceServer->getHttpAuthUsername(),
            'httpAuthPassword' => $websiteServiceServer->getHttpAuthPassword(),
        );
        return self::executeCommand($host, $command, $params, $secretKey, $installationId, $httpAuth);
    }

    /*
     * This method will be used by the Website Service to execute commands on a Website
     * Fetch connection data to Website and pass it to the method executeCommand():
     */

    public static function executeCommandOnWebsite($command, $params, $website) {

        if (!in_array(\Cx\Core\Setting\Controller\Setting::getValue('mode'), array(ComponentController::MODE_HYBRID, ComponentController::MODE_SERVICE))) {
            throw new MultiSiteJsonException('Command'.__METHOD__.'executeCommandOnWebsite is only available in MultiSite-mode HYBRID or SERVICE.');
        }
        // JsonData requests shall be made to the FQDN of the Website,
        // as the BaseDn might not yet work as it depends on the DNS synchronization.
        $host = $website->getFqdn()->getName();
        $installationId = $website->getInstallationId();
        $secretKey = $website->getSecretKey();
        $httpAuth = array(
            'httpAuthMethod' => \Cx\Core\Setting\Controller\Setting::getValue('websiteHttpAuthMethod'),
            'httpAuthUsername' => \Cx\Core\Setting\Controller\Setting::getValue('websiteHttpAuthUsername'),
            'httpAuthPassword' => \Cx\Core\Setting\Controller\Setting::getValue('websiteHttpAuthPassword'),
        );
        return self::executeCommand($host, $command, $params, $secretKey, $installationId, $httpAuth);
    }
    
     /**
     * setup the config options
     */
    public function setupConfig() {
        global $_CONFIG;
        
        \Cx\Core\Setting\Controller\Setting::init('Config', '','Yaml');
        if (!\Cx\Core\Setting\Controller\Setting::isDefined('installationId')
                && !\Cx\Core\Setting\Controller\Setting::add('installationId', $_CONFIG['installationId'], 1, \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'core')) {
            throw new MultiSiteJsonException("Failed to add Setting entry for installationId");
        }
        if (!\Cx\Core\Setting\Controller\Setting::isDefined('dashboardNewsSrc') 
                && !\Cx\Core\Setting\Controller\Setting::add('dashboardNewsSrc', $_CONFIG['dashboardNewsSrc'], 2, \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'component')) {
            throw new MultiSiteJsonException("Failed to add Setting entry for dashboardNewsSrc");
        }
        if (!\Cx\Core\Setting\Controller\Setting::isDefined('coreAdminEmail') 
                && !\Cx\Core\Setting\Controller\Setting::add('coreAdminEmail', $_CONFIG['coreAdminEmail'], 3, \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'contactInformation')) {
            throw new MultiSiteJsonException("Failed to add Setting entry for coreAdminEmail");
        }
        if (!\Cx\Core\Setting\Controller\Setting::isDefined('contactFormEmail') 
                && !\Cx\Core\Setting\Controller\Setting::add('contactFormEmail', $_CONFIG['contactFormEmail'], 4, \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'contactInformation')) {
            throw new MultiSiteJsonException("Failed to add Setting entry for contactFormEmail");
        }
        \Cx\Core\Config\Controller\Config::init();

        // we must re-initialize the original MultiSite settings of the main installation
        \Cx\Core\Setting\Controller\Setting::init('MultiSite', '', 'FileSystem');
        return array('status' => 'success');
    }

    public function getDefaultWebsiteIp() {
        if (\Cx\Core\Setting\Controller\Setting::getValue('mode') != ComponentController::MODE_SERVICE) {
            throw new MultiSiteJsonException('JsonMultiSite::getDefaultWebsiteIp() failed: Command is only on Website Service Server available.');
        }

        return array(
            'status'            => 'success',
            'defaultWebsiteIp'  => \Cx\Core\Setting\Controller\Setting::getValue('defaultWebsiteIp'),
        );
    }
    
    /**
     * Set the default language
     * 
     * @global \Cx\Core_Modules\MultiSite\Controller\ADOConnection $objDatabase
     * @param array $params
     * 
     * @return boolean
     * @throws MultiSiteJsonException
     */
    public function setDefaultLanguage($params) {
        global $objDatabase;
        
        if (empty($params['post']['langId'])) {
            throw new MultiSiteJsonException('JsonMultiSite::setDefaultLanguage() failed: No language specified.');
        }
        
        try {
            $deactivateIds = array();
            $arrLang = \FWLanguage::getLanguageArray();
            foreach ($arrLang As $key => $value) {
                if ($key != $params['post']['langId']) {
                    $deactivateIds[] = $key;
                }
            }
            
            //deactivate all the languages except the lang $params['post']['langId']
            $deactivateQuery = \SQL::update('languages', array('backend' => 0, 'frontend' => 0, 'is_default' => 'false'), array('escape' => true)) . ' WHERE `id` In (' . implode(', ', $deactivateIds) . ')';
            
            //set the lang($params['post']['langId']) as default
            $activateQuery = \SQL::update('languages', array('backend' => 1, 'frontend' => 1, 'is_default' => 'true'), array('escape' => true)) . ' WHERE `id` = ' . $params['post']['langId'];
            
            if ($objDatabase->Execute($deactivateQuery) !== false && $objDatabase->Execute($activateQuery) !== false) {
                return array('status' => 'success');
            }
            
        } catch (\Exception $e) {
            throw new MultiSiteJsonException('JsonMultiSite::setDefaultLanguage() failed: Updating Language status.' . $e->getMessage());
        }
    }
    
    /**
     * Check current user is website owner or not
     * 
     * @return boolean
     */
    public function isWebsiteOwner() {
        //check user logged in or not
        if (!\FWUser::getFWUserObject()->objUser->login()) {
            return false;
        }
        
        //Check the user is website owner or not
        if (\FWUser::getFWUserObject()->objUser->getId() == \Cx\Core\Setting\Controller\Setting::getValue('websiteUserId')) {
            return true;
        }
        return false;
    }
    
    /**
     * Reset the FTP Password
     * 
     * @param array $params
     * 
     * @return array
     * @throws MultiSiteJsonException
     */
    public function resetFtpPassword($params) {
        // load text-variables of module MultiSite
        global $_ARRAYLANG, $objInit;
        
        //load language file 
        $langData = $objInit->loadLanguageData('MultiSite');
        $_ARRAYLANG = array_merge($_ARRAYLANG, $langData);
        
        try {
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode')) {
                case ComponentController::MODE_WEBSITE:
                    $response = self::executeCommandOnMyServiceServer('resetFtpPassword', array());
                    if ($response && $response->status == 'success' && $response->data->status == 'success') {
                        return array('status' => 'success', 'message' => $_ARRAYLANG['TXT_MULTISITE_RESET_FTP_PASS_MSG'], 'password' => $response->data->password);
                    }
                    break;
                case ComponentController::MODE_SERVICE:
                case ComponentController::MODE_HYBRID:
                    $authenticationValue = json_decode($params['post']['auth'], true);
                    if (empty($authenticationValue) || !is_array($authenticationValue)) {
                        \DBG::dump($params);
                        throw new MultiSiteJsonException('JsonMultiSite::resetFtpPassword() on ' . \Cx\Core\Setting\Controller\Setting::getValue('mode') . ' failed: Insufficient reset information supplied.' . var_export($params, true));
                    }

                    $domainRepo = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Domain');
                    $domain = $domainRepo->findOneBy(array('name' => $authenticationValue['sender']));
                    if (!$domain) {
                        throw new MultiSiteJsonException('JsonMultiSite::resetFtpPassword() failed: Unkown Website: ' . $authenticationValue['sender']);
                    }

                    $website = $domain->getWebsite();
                    if (!$website) {
                        throw new MultiSiteJsonException('JsonMultiSite::resetFtpPassword() failed: Unkown Website: ' . $authenticationValue['sender']);
                    }

                    $hostingController = \Cx\Core_Modules\MultiSite\Controller\ComponentController::getHostingController();
                    $password = \User::make_password(8, true);
                    if ($hostingController->changeFtpAccountPassword($website->getName(), $password)) {
                        return array('status' => 'success', 'password' => $password);
                    }
                    break;
            }

            return array('status' => 'failed', 'message' => $_ARRAYLANG['TXT_MULTISITE_RESET_FTP_PASS_ERROR_MSG']);
        } catch (\Exception $e) {
            throw new MultiSiteJsonException('JsonMultiSite::resetFtpPassword() failed: Updating FTP password.' . $e->getMessage());
        }
    }
    
    /**
     * Check the Authentication access for resetting the FTP password
     * 
     * @return boolean
     */
    public function checkResetFtpPasswordAccess($params) {
        switch (\Cx\Core\Setting\Controller\Setting::getValue('mode')) {
            case ComponentController::MODE_WEBSITE:
                if ($this->isWebsiteOwner()) {
                    return true;
                }
                break;
            case ComponentController::MODE_SERVICE:
            case ComponentController::MODE_HYBRID:
                if ($this->auth($params)) {
                    return true;
                }
                break;
        }
        return false;
    }
        
    /*
     * Updating setup data in servers
     * 
     * @return boolean
     */
    public function updateServiceServerSetup($params) {

        if (empty($params['post']['setupArray'])) {
            throw new MultiSiteJsonException('JsonMultiSite::updateServiceServerSetup(): Updating setup data in server failed due to empty params in post method.');
        }
        
        try {
            \Cx\Core\Setting\Controller\Setting::init('MultiSite', 'setup', 'FileSystem');
            $setupValues = $params['post']['setupArray'];
            foreach($setupValues as $valuesName => $value) {
                \Cx\Core\Setting\Controller\Setting::set($valuesName, $value['value']);
                \Cx\Core\Setting\Controller\Setting::update($valuesName);
            }
        } catch (\Exception $e) {
            throw new MultiSiteJsonException('JsonMultiSite::updateServiceServerSetup() failed: Updating setup data in server .' . $e->getMessage());
        }
    }
    
    /*
     * Completely removes an website
     * 
     */
    public function destroyWebsite($params) {
        
        if (empty($params['post']['websiteId'])) {
            throw new MultiSiteJsonException('JsonMultiSite (destroyWebsite): failed to destroy the website due to the empty param $websiteId');
        }

        try {
            $webRepo  = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website');
            $website  = $webRepo->findOneById($params['post']['websiteId']);
            if (!$website) {
                return array(
                    'status' => 'success'
                );            
            }
            $website->destroy();
            return array(
                'status' => 'success'
            );
        } catch (\Exception $e) {
            throw new MultiSiteJsonException('JsonMultiSite (destroyWebsite): failed to destroy the website.' . $e->getMessage());
        }
    }

    /**
     * Set the Website Theme
     * 
     * @global \Cx\Core_Modules\MultiSite\Controller\ADOConnection $objDatabase
     * @param array $params
     * 
     * @return array
     * @throws MultiSiteJsonException
     */
    public function setWebsiteTheme($params) {
        global $objDatabase;
        
        if (empty($params['post']['themeId'])) {
            throw new MultiSiteJsonException('JsonMultiSite (setWebsiteTheme): failed to set the website theme due to the empty param $themeId');
        }
        
        try {
            $themeRepo = new \Cx\Core\View\Model\Repository\ThemeRepository();
            if (!$themeRepo->findById($params['post']['themeId'])) {
                throw new MultiSiteJsonException('JsonMultiSite (setWebsiteTheme): failed to set the website theme due to no one theme exists with param $themeId');
            }

            $langId = \FWLanguage::getDefaultLangId();
            //set the theme $themeId as standard and mobile theme
            $objResult = $objDatabase->Execute('UPDATE ' . DBPREFIX . 'languages '
                                                . 'SET `themesid` = ' . intval($params['post']['themeId']) . ', `mobile_themes_id` = ' . intval($params['post']['themeId'])
                                                . ' WHERE id = ' . intval($langId));
            if ($objResult !== false) {
                return array('status' => 'success');
            }
        } catch (\Exception $e) {
            throw new MultiSiteJsonException('JsonMultiSite (setWebsiteTheme): failed to set the website theme.' . $e->getMessage());
        }
    }
}
