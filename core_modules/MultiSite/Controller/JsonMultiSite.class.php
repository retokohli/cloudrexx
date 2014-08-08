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
        return array(
            'signup'                => new \Cx\Core\Access\Model\Entity\Permission(array('https'), array('post'), false),
            'email'                 => new \Cx\Core\Access\Model\Entity\Permission(array('https'), array('post'), false),
            'createWebsite'         => new \Cx\Core\Access\Model\Entity\Permission(array('https'), array('post'), false, array($this, 'auth')),
            'createUser'            => new \Cx\Core\Access\Model\Entity\Permission(array('https'), array('post'), false, array($this, 'auth')),
            'mapDomain'             => new \Cx\Core\Access\Model\Entity\Permission(array('https'), array('post'), false, array($this, 'auth')),
            'unmapDomain'           => new \Cx\Core\Access\Model\Entity\Permission(array('https'), array('post'), false, array($this, 'auth')),
            'updateDefaultCodeBase' => new \Cx\Core\Access\Model\Entity\Permission(array('https'), array('post'), true)
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
        if (!\User::isUniqueEmail($params['post']['multisite_email_address'])) {
            //return array('status' => 'login');
            throw new MultiSiteJsonException(array('object' => 'email', 'type' => 'info', 'message' => 'An account with this email does already exist. <a href="" target="_blank">Login now</a>'));
        }
    }

    /**
    * function signup
    * @param post parameters
    * */
    public function signup($params){
        // load text-variables of module MultiSite
        global $_ARRAYLANG, $objInit;
        $langData = $objInit->loadLanguageData('MultiSite');
        $_ARRAYLANG = array_merge($_ARRAYLANG, $langData);
        $objUser = new \Cx\Core_Modules\MultiSite\Model\Entity\User();
        if (!empty($params['post'])) {
            $post = $params['post'];
            $websiteName = contrexx_input2raw($post['multisite_address']);
            //set email of the new user
            $objUser->setEmail(contrexx_input2raw($post['multisite_email_address']));
            //set frontend language id 
            //$objUser->setFrontendLanguage(contrexx_input2raw($post['langId']));
            //set backend language id 
            //$objUser->setBackendLanguage(contrexx_input2raw($post['langId']));
            //set password 
            $objUser->setPassword($objUser->make_password(8, true));
            //check email validity
            if (!\FWValidator::isEmail($post['multisite_email_address'])) {
                throw new MultiSiteJsonException('The email you entered is invalid.');
            }
            //check email existence
            if (!\User::isUniqueEmail($post['multisite_email_address'])) {
                throw new MultiSiteJsonException(array('object' => 'email', 'type' => 'info', 'message' => 'An account with this email does already exist. <a href="" target="_blank">Login now</a>'));
            }

            //call \User\store function to store all the info of new user
            if (!$objUser->store()) {
                throw new MultiSiteJsonException($objUser->error_msg);
            }
            //call createWebsite method.
            return $this->createWebsite($objUser,$websiteName);
        }
    }

    /**
     * Creates a new website
     * @param type $params  
    */
    public function createWebsite($params,$websiteName='') {
        // load text-variables of module MultiSite
        global $_ARRAYLANG, $objInit;
        if (is_array($params)) {
            $objUser = new \Cx\Core_Modules\MultiSite\Model\Entity\User();
            //set email of the new user
            $objUser->setEmail(contrexx_input2raw($params['post']['userEmail']));
            //set user id of the new user
            $objUser->setId(contrexx_input2raw($params['post']['userId']));
            $websiteId = contrexx_input2raw($params['post']['websiteId']);
            $websiteName = contrexx_input2raw($params['post']['websiteName']);
        } else {
            $objUser = $params;
            $websiteId = '';
        }
        //load language file 
        $langData = $objInit->loadLanguageData('MultiSite');
        $_ARRAYLANG = array_merge($_ARRAYLANG, $langData);
        
        $basepath = \Cx\Core\Setting\Controller\Setting::getValue('websitePath');
        $websiteServiceServer = null;
        if (\Cx\Core\Setting\Controller\Setting::getValue('mode') == 'manager') {
            //get default service server
            $defaultWebsiteServiceServer = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer')
            ->findBy(array('isDefault' => 1));
            $websiteServiceServer = $defaultWebsiteServiceServer[0];
        }

        try {
            $objWebsite = new \Cx\Core_Modules\MultiSite\Model\Entity\Website($basepath, $websiteName, $websiteServiceServer, $objUser, false);
            if($websiteId!=''){
                $objWebsite->setId($websiteId);
            }
            \Env::get('em')->persist($objWebsite);
            \Env::get('em')->flush();
            return $objWebsite->setup();
        } catch (\Cx\Core_Modules\MultiSite\Model\Entity\WebsiteException $e) {
            throw new MultiSiteJsonException($e->getMessage());    
        }
    }

    public function createUser($params) {
        if (!empty($params['post'])) {
            $objUser = new \Cx\Core_Modules\MultiSite\Model\Entity\User();
            $objUser->setEmail(!empty($params['post']['email']) ? contrexx_input2raw($params['post']['email']) : '');
            $objUser->setActiveStatus(!empty($params['post']['active']) ? (bool)$params['post']['active'] : false);
            $objUser->setAdminStatus(!empty($params['post']['admin']) ? (bool)$params['post']['admin'] : false);
            $objUser->setPassword(\User::make_password(8,true));
            
            //$objUser->setProfileAccess(!empty($params['post']['profileAccess']) && $objUser->isAllowedToChangeProfileAccess() ? trim(contrexx_stripslashes($params['post']['profileAccess'])) : '');
            //$objUser->setFrontendLanguage(!empty($params['post']['frontendLanguage']) ? intval($params['post']['frontendLanguage']) : 0);
            //$objUser->setBackendLanguage(!empty($params['post']['backendLanguage']) ? intval($params['post']['backendLanguage']) : 0);
            /*$arrProfile = array(
                                'picture'       =>array((!empty($params['post']['picture']) ? contrexx_stripslashes($params['post']['picture']) : '')),
                                'gender'        =>array((!empty($params['post']['gender'])  ? contrexx_stripslashes($params['post']['gender']) : '')),
                                'title'         =>array((!empty($params['post']['picture']) ? contrexx_stripslashes($params['post']['title']) : '')),
                                'firstname'     =>array((!empty($params['post']['firstname']) ? contrexx_stripslashes($params['post']['firstname']) : '')),
                                'lastname'      =>array((!empty($params['post']['lastname']) ? contrexx_stripslashes($params['post']['lastname']) : '')),
                                'company'       =>array((!empty($params['post']['company']) ? contrexx_stripslashes($params['post']['company']) : '')),
                                'address'       =>array((!empty($params['post']['address']) ? contrexx_stripslashes($params['post']['address']) : '')),
                                'city'          =>array((!empty($params['post']['city']) ? contrexx_stripslashes($params['post']['city']) : '')),
                                'zip'           =>array((!empty($params['post']['zip']) ? contrexx_stripslashes($params['post']['zip']) : '')),
                                'country'       =>array((!empty($params['post']['country']) ? contrexx_stripslashes($params['post']['country']) : '')),
                                'phone_office'  =>array((!empty($params['post']['phone_office']) ? contrexx_stripslashes($params['post']['phone_office']) : '')),
                                'phone_private' =>array((!empty($params['post']['phone_private']) ? contrexx_stripslashes($params['post']['phone_private']) : '')),
                                'phone_fax'     =>array((!empty($params['post']['phone_fax']) ? contrexx_stripslashes($params['post']['phone_fax']) : '')),
                                'birthday'      =>array((!empty($params['post']['birthday']) ? contrexx_stripslashes($params['post']['birthday']) : '')),
                                'website'       =>array((!empty($params['post']['website']) ? contrexx_stripslashes($params['post']['website']) : '')),
                                'profession'    =>array((!empty($params['post']['profession']) ? contrexx_stripslashes($params['post']['profession']) : '')),
                                'interests'     =>array((!empty($params['post']['interests']) ? contrexx_stripslashes($params['post']['interests']) : '')),
                                'signature'     =>array((!empty($params['post']['signature']) ? contrexx_stripslashes($params['post']['signature']) : ''))
                            );
            $objUser->setProfile($arrProfile);*/
            $objUser->store();
            return true;
        }
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
            case 'manager':
                try {
                    $WebsiteServiceServerRepository = \Env::get('em')->getRepository('\Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer');
                    $objWebsiteService = $WebsiteServiceServerRepository->findBy(array('hostName' => $authenticationValue['sender']));
                    $secretKey = $objWebsiteService->getSecretKey();
                } catch(\Exception $e) {
                    return $e->getMessage();
                }
                break;

            case 'service':
            case 'hybrid':
                //Check if the sender is manager or not
                if ($authenticationValue['sender'] == \Cx\Core\Setting\Controller\Setting::getValue('managerHostname')) {
                    $secretKey = \Cx\Core\Setting\Controller\Setting::getValue('managerSecretKey');
                } else {
                    try {
                        $objWebsiteRepo = \Env::get('em')->getRepository('\Cx\Core_Modules\MultiSite\Model\Entity\Website');
                        list($websiteName) = explode('.', $authenticationValue['sender']);
                        $objWebsite = $objWebsiteRepo->findBy(array('name' => $websiteName));
                        $secretKey  = $objWebsite[0]->getSecretKey();
                    } catch (\Exception $e) {
                        return $e->getMessage();
                    }
                }
                break;
                
            case 'website':
                $secretKey = \Cx\Core\Setting\Controller\Setting::getValue('serviceSecretKey');
                break;
        }
        
        if (md5($secretKey.$installationId) === $authenticationValue['key']) {
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
                $objWebsiteRepo = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website');
                if (empty($params['post']['websiteId'])) {
                    list($websiteName) = explode('.', $authenticationValue['sender']);
                    $website = $objWebsiteRepo->findByName($websiteName);
                } else {
                    $website[0] = $objWebsiteRepo->findById($params['post']['websiteId']);
                }
                
                $objDomain = new \Cx\Core_Modules\MultiSite\Model\Entity\Domain($params['post']['domainName']);                
                $website[0]->mapDomain($objDomain);
                
                \Env::get('em')->persist($objDomain);
                \Env::get('em')->persist($website[0]);
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
            
            $objWebsiteRepo = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website');
            if (empty($params['post']['websiteId'])) {
                list($websiteName) = explode('.', $authenticationValue['sender']);
                $website = $objWebsiteRepo->findByName($websiteName);
            } else {
                $website[0] = $objWebsiteRepo->findById($params['post']['websiteId']);
            }
            
            $website[0]->unmapDomain($params['post']['domainName']);
            \Env::get('em')->persist($website[0]);
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
    
}
