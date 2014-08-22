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
    /**
     * Overwriting the default Exception constructor
     * The default Exception constructor only accepts $message to be a string.
     * We do overwrite this here to also allow $message to be an array
     * that can then be sent back in the JsonData-response.
     */
    public function __construct($message = null, $code = 0, Exception $previous = null) {
        parent::__construct('', $code, $previous);
        $this->message = $message;
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
            'updateUser'            => new \Cx\Core\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false, array($this, 'auth')),
            'updateOwnUser'         => new \Cx\Core\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), true),
            'mapDomain'             => new \Cx\Core\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false, array($this, 'auth')),
            'unMapDomain'           => new \Cx\Core\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false, array($this, 'auth')),
            'updateDomain'          => new \Cx\Core\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false, array($this, 'auth')),
            'updateDefaultCodeBase' => new \Cx\Core\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), true, array($this, 'checkPermission')),
            'setWebsiteState'       => new \Cx\Core\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false, array($this, 'auth')),
            'updateWebsiteState'    => new \Cx\Core\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), true, array($this, 'checkPermission')),
            'ping'                  => new \Cx\Core\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false, array($this, 'auth')),
            'pong'                  => new \Cx\Core\Access\Model\Entity\Permission(array($multiSiteProtocol), array('post'), false, array($this, 'auth'))
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
        if (!isset($params['post']['multisite_email_address'])) {
            return;
        }

        self::verifyEmail($params['post']['multisite_email_address']);
    }

    public static function verifyEmail($email) {
        global $_ARRAYLANG;

        if (!\User::isUniqueEmail($email)) {
            global $_ARRAYLANG, $objInit;
            $langData = $objInit->loadLanguageData('MultiSite');
            $_ARRAYLANG = array_merge($_ARRAYLANG, $langData);

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

    public function address($params) {
        if (!isset($params['post']['multisite_address'])) {
            return;
        }
        try {
            \Cx\Core_Modules\MultiSite\Model\Entity\Website::validateName(contrexx_input2raw($params['post']['multisite_address']));
        } catch (\Cx\Core_Modules\MultiSite\Model\Entity\WebsiteException $e) {
            throw new MultiSiteJsonException(array('object' => 'address', 'type' => 'warning', 'message' => $e->getMessage()));
        }
    }

    /**
    * function signup
    * @param post parameters
    * */
    public function signup($params) {
        // load text-variables of module MultiSite
        global $_ARRAYLANG, $objInit;
        $langData = $objInit->loadLanguageData('MultiSite');
        $_ARRAYLANG = array_merge($_ARRAYLANG, $langData);
        $objUser = new \Cx\Core_Modules\MultiSite\Model\Entity\User();
        if (!empty($params['post'])) {
            $websiteName = contrexx_input2raw($params['post']['multisite_address']);
            $user = $this->createUser(array('post' => array('email' => $params['post']['multisite_email_address'])));
            // create a new CRM Contact and link it to the User account
            if (!empty($user['userId'])) {
                $objFWUser = \FWUser::getFWUserObject();
                $objUser   = $objFWUser->objUser->getUser(intval($user['userId']));
                
                $objUser->objAttribute->first();
                while (!$objUser->objAttribute->EOF) {
                    $arrProfile['fields'][] = array('special_type' => 'access_'.$objUser->objAttribute->getId());
                    $arrProfile['data'][] = $objUser->getProfileAttribute($objUser->objAttribute->getId());
                    $objUser->objAttribute->next();
                }
                
                $arrProfile['fields'][] = array('special_type' => 'access_email');
                $arrProfile['data'][]   = $objUser->getEmail();
                $objCrmLibrary = new \Cx\Modules\Crm\Controller\CrmLibrary('Crm');
                $crmContactId  = $objCrmLibrary->addCrmContact($arrProfile);
                
                try {
                    $id = 1;
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
                    $productRepository = \Env::get('em')->getRepository('Cx\Modules\Pim\Model\Entity\Product');
                    $product = $productRepository->findOneBy(array('id' => $id));
                    //create a new subscription for product #1 to order
                    $order = new \Cx\Modules\Order\Model\Entity\Order();
                    $order->setContactId($crmContactId);
                    $order->createSubscription($product, $subscriptionOptions);
                    \Env::get('em')->persist($order);
                    \Env::get('em')->flush();
            
                    return $order->complete();
                } catch (\Exception $e) {
                    throw new MultiSiteJsonException($e->getMessage());
                }
            } else {
                throw new MultiSiteJsonException('Problem in creating user');  
            }
        }
    }

    /**
     * Creates a new website
     * @param type $params  
    */
    public function createWebsite($params) {
        // load text-variables of module MultiSite
        global $_ARRAYLANG, $objInit;
        
        $objFWUser   = \FWUser::getFWUserObject();
        $objUser     = $objFWUser->objUser->getUser(contrexx_input2raw($params['post']['userId']));
        $websiteId   = isset($params['post']['websiteId']) ? contrexx_input2raw($params['post']['websiteId']) : '';
        $websiteName = isset($params['post']['websiteName']) ? contrexx_input2raw($params['post']['websiteName']) : '';
        
        //load language file 
        $langData = $objInit->loadLanguageData('MultiSite');
        $_ARRAYLANG = array_merge($_ARRAYLANG, $langData);
        
        $basepath = \Cx\Core\Setting\Controller\Setting::getValue('websitePath');
        $websiteServiceServer = null;
        if (\Cx\Core\Setting\Controller\Setting::getValue('mode') == ComponentController::MODE_MANAGER) {
            //get default service server
            $defaultWebsiteServiceServer = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer')
            ->findBy(array('isDefault' => 1));
            $websiteServiceServer = $defaultWebsiteServiceServer[0];
        }

        try {
            $objWebsite = new \Cx\Core_Modules\MultiSite\Model\Entity\Website($basepath, $websiteName, $websiteServiceServer, $objUser, false);
            \Env::get('em')->persist($objWebsite);
            if ($websiteId) {
                $objWebsite->setId($websiteId);
                $metadata = \Env::get('em')->getClassMetaData(get_class($objWebsite));
                $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
            }
            \Env::get('em')->flush();
            return $objWebsite->setup();
        } catch (\Cx\Core_Modules\MultiSite\Model\Entity\WebsiteException $e) {
            throw new MultiSiteJsonException($e->getMessage());    
        }
    }

    public function createUser($params) {
        if (!empty($params['post'])) {
            $objUser = new \Cx\Core_Modules\MultiSite\Model\Entity\User();
            if (!empty($params['post']['userId'])) {
                $objUser->setMultiSiteId($params['post']['userId']);
            }
            $objUser->setEmail(!empty($params['post']['email']) ? contrexx_input2raw($params['post']['email']) : '');
            $objUser->setActiveStatus(!empty($params['post']['active']) ? (bool)$params['post']['active'] : false);
            $objUser->setAdminStatus(!empty($params['post']['admin']) ? (bool)$params['post']['admin'] : false);
            $objUser->setPassword(\User::make_password(8,true));
            
            //check email validity
            if (!\FWValidator::isEmail($params['post']['email'])) {
                throw new MultiSiteJsonException('The email you entered is invalid.');
            }
            //check email existence
            self::verifyEmail($params['post']['email']);
            
            //call \User\store function to store all the info of new user
            if (!$objUser->store()) {
                throw new MultiSiteJsonException($objUser->error_msg);
            } else {
                return array('userId' => $objUser->getId());
            }
        }
    }

    /**
     * Update an existing user account
     * @param array $params POST-data based on with the account shall be updated to
     * @return  boolean     TRUE on success, FALSE on failure.
     */
    public function updateUser($params) {
        $objFWUser = \FWUser::getFWUserObject();
        
        switch(\Cx\Core\Setting\Controller\Setting::getValue('mode')) {
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
        $data = $params['post']['multisite_user_profile_attribute'];
        
        isset($data['multisite_user_username']) ? $objUser->setUsername(trim(contrexx_input2raw($data['multisite_user_username']))) : null;
        $objUser->setEmail(isset($data['multisite_user_email']) ? trim(contrexx_input2raw($data['multisite_user_email'])) : (isset($params['post']['multisite_user_account_email']) ? trim(contrexx_input2raw($params['post']['multisite_user_account_email'])) : $objUser->getEmail()));
        $currentLangId = $objUser->getFrontendLanguage();
        $objUser->setFrontendLanguage(isset($data['multisite_user_frontend_language']) ? intval($data['multisite_user_frontend_language']) : $objUser->getFrontendLanguage());
        $objUser->setBackendLanguage(isset($data['multisite_user_backend_language']) ? intval($data['multisite_user_backend_language']) : $objUser->getBackendLanguage());
        $objUser->setEmailAccess(isset($data['multisite_user_email_access']) && $objUser->isAllowedToChangeEmailAccess() ? contrexx_input2raw($data['multisite_user_email_access']) : $objUser->getEmailAccess());
        $objUser->setProfileAccess(isset($data['multisite_user_profile_access']) && $objUser->isAllowedToChangeProfileAccess() ? contrexx_input2raw($data['multisite_user_profile_access']) : $objUser->getProfileAccess());
        if (!empty($data['multisite_user_password']) || !empty($params['post']['multisite_user_account_password'])) {
            $password = !empty($data['multisite_user_password']) ? trim(contrexx_stripslashes($data['multisite_user_password'])) : (!empty($params['post']['multisite_user_account_password']) ? trim(contrexx_stripslashes($params['post']['multisite_user_account_password'])) : '');
            $confirmedPassword = !empty($data['multisite_user_password_confirmed']) ? trim(contrexx_stripslashes($data['multisite_user_password_confirmed'])) : (!empty($params['post']['multisite_user_account_password_confirmed']) ? trim(contrexx_stripslashes($params['post']['multisite_user_account_password_confirmed'])) : '');
            if (!$objUser->setPassword($password, $confirmedPassword)) {
                throw new MultiSiteJsonException(array(
                    'object'    => 'password',
                    'type'      => 'danger',
                    'message'   => $objUser->getErrorMsg(),
                ));
            }
        }
        
        $objUser->setProfile($data);
        if (!$objUser->store()) {
            throw new MultiSiteJsonException(array(
                'object'    => 'form',
                'type'      => 'danger',
                'message'   => $objUser->getErrorMsg(),
            ));
        }
                
        return true;
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

        // Only administrators or users with sufficient permissions
        // may update their own account.
        if (!\Permission::hasAllAccess() && !\Permission::checkAccess(31, 'static', true)) {
            throw new MultiSiteJsonException(array(
                'object'    => 'form',
                'type'      => 'danger',
                'message'   => 'Operation denied',
            ));
        }

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
                    $objWebsiteService = $WebsiteServiceServerRepository->findBy(array('hostname' => $authenticationValue['sender']));
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
    public function mapDomain($params){
        if (!empty($params['post']) && !empty($params['post']['domainName'])) {
            try {                
                $authenticationValue = isset($params['post']['auth']) ? json_decode($params['post']['auth'], true) : '';

                if (empty($authenticationValue) || !is_array($authenticationValue)) {
                    return false;
                }
                
                $componentId = 0;
                switch (true) {
                    case (!empty($params['post']['componentId'])):
                        $componentId = $params['post']['componentId'];
                        break;
                    case ($params['post']['componentType'] == \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_SERVICE):
                        $websiteServiceServer = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer')
                        ->findOneBy(array('hostname' => $authenticationValue['sender']));
                        $componentId = $websiteServiceServer->getId();
                        break;
                }
                
                $domainRepo = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Domain');
                $domain     = $domainRepo->findOneBy(array('name' => $authenticationValue['sender']));
                $website    = $domain ? $domain->getWebsite() : '';
                
                if (isset($website) && $website instanceof \Cx\Core_Modules\MultiSite\Model\Entity\Website) {
                    $objDomain = new \Cx\Core_Modules\MultiSite\Model\Entity\Domain($params['post']['domainName']);                
                    $website->mapDomain($objDomain);
                    $objDomain->setCoreNetDomainId($params['post']['coreNetDomainId']);
                    $objDomain->setComponentType($params['post']['componentType']);
                    $objDomain->setComponentId($componentId);
                
                    \Env::get('em')->persist($objDomain);
                    \Env::get('em')->persist($website);
                } else {
                    $objDomain = new \Cx\Core_Modules\MultiSite\Model\Entity\Domain($params['post']['domainName']);
                    $objDomain->setCoreNetDomainId($params['post']['coreNetDomainId']);
                    $objDomain->setComponentType($params['post']['componentType']);
                    $objDomain->setComponentId($componentId);
                    \Env::get('em')->persist($objDomain);
                }
                
                \Env::get('em')->flush();
                
            } catch (\Exception $e) {
                return $e->getMessage();
            }
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
        if (!empty($params['post']) && !empty($params['post']['domainName'])) {
            $authenticationValue = isset($params['post']['auth']) ? json_decode($params['post']['auth'], true) : '';

            if (empty($authenticationValue) || !is_array($authenticationValue)) {
                return false;
            }
            
            $domainRepo = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Domain');
            $domain     = $domainRepo->findOneBy(array('name' => $authenticationValue['sender']));
            $website    = $domain ? $domain->getWebsite() : '';
            
            if (isset($website) && $website instanceof \Cx\Core_Modules\MultiSite\Model\Entity\Website) {
                $website->unmapDomain($params['post']['domainName']);
                \Env::get('em')->persist($website);
            } else {
                $objDomainRepo = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Domain');
                $objDomain     = $objDomainRepo->findOneBy(array('name' => $params['post']['domainName']));
                \Env::get('em')->remove($objDomain);
                \Env::get('em')->persist($objDomain);
            }
            
            \Env::get('em')->flush();
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

        if (!empty($params['post']) && !empty($params['post']['domainName']) && !empty($params['post']['domainId'])) {
            $authenticationValue = isset($params['post']['auth']) ? json_decode($params['post']['auth'], true) : '';

            if (empty($authenticationValue) || !is_array($authenticationValue)) {
                return false;
            }
            try {
                switch (\Cx\Core\Setting\Controller\Setting::getValue('mode')) {
                    case ComponentController::MODE_MANAGER:
                    case ComponentController::MODE_HYBRID:
                        $domainRepo = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Domain');
                        $domain = $domainRepo->findOneBy(array('coreNetDomainId' => $params['post']['coreNetDomainId'], 'componentType' => $params['post']['componentType']));
                        $domain->setName($params['post']['domainName']);
                        break;

                    case ComponentController::MODE_SERVICE:
                        $domainRepo = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Domain');
                        $objDomain = $domainRepo->findOneBy(array('name' => $authenticationValue['sender']));
                        $domain = $domainRepo->findOneBy(array('componentId' => $objDomain->getWebsite()->getId(), 'coreNetDomainId' => $params['post']['coreNetDomainId']));
                        $domain->setName($params['post']['domainName']);
                        break;
                }
                \Env::get('em')->persist($domain);
                \Env::get('em')->flush();
            } catch (\Exception $e) {
                return $e->getMessage();
            }
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
        $resp = self::executeCommandOnManager('pong',array('action' => 'pong'));
        if ($resp->data->success = 'success') {
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

    public static function executeCommandOnManager($command, $params) {

        if (!in_array(\Cx\Core\Setting\Controller\Setting::getValue('mode'), array(ComponentController::MODE_MANAGER, ComponentController::MODE_SERVICE, ComponentController::MODE_HYBRID))) {
            throw new MultiSiteJsonException('Command executeCommandOnWebsite is only available in MultiSite-mode MANAGER, SERVICE or HYBRID.');
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
            throw new MultiSiteJsonException('Command executeCommandOnWebsite is only available in MultiSite-mode WEBSITE.');
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
            throw new MultiSiteJsonException('Command executeCommandOnWebsite is only available in MultiSite-mode MANAGER.');
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
            throw new MultiSiteJsonException('Command executeCommandOnWebsite is only available in MultiSite-mode MANAGER.');
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
            throw new MultiSiteJsonException('Command executeCommandOnWebsite is only available in MultiSite-mode HYBRID or SERVICE.');
        }
        $host = $website->getBaseDn()->getName();
        $installationId = $website->getInstallationId();
        $secretKey = $website->getSecretKey();
        $httpAuth = array(
            'httpAuthMethod' => \Cx\Core\Setting\Controller\Setting::getValue('websiteHttpAuthMethod'),
            'httpAuthUsername' => \Cx\Core\Setting\Controller\Setting::getValue('websiteHttpAuthUsername'),
            'httpAuthPassword' => \Cx\Core\Setting\Controller\Setting::getValue('websiteHttpAuthPassword'),
        );
        return self::executeCommand($host, $command, $params, $secretKey, $installationId, $httpAuth);
    }

}
