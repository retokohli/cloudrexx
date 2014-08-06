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

class MultiSiteJsonException extends \Exception {}
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
// TODO: remove 'get' from 'signup' command once API/Command-mode has been implemented
        return array(
            'signup'                => new \Cx\Core\Access\Model\Entity\Permission(array('https'), array('post', 'get'), false),
            'createWebsite'         => new \Cx\Core\Access\Model\Entity\Permission(array('https'), array('post'), false, array($this, 'auth')),
            'mapDomain'             => new \Cx\Core\Access\Model\Entity\Permission(array('https'), array('post'), false, array($this, 'auth')),
            'unmapDomain'           => new \Cx\Core\Access\Model\Entity\Permission(array('https'), array('post'), false, array($this, 'auth')),
            'updateDefaultCodeBase' => new \Cx\Core\Access\Model\Entity\Permission(array('https'), array('post', 'get'), false),
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
                throw new MultiSiteJsonException('An account with this email does already exist. Click here to login.');
            }

            //call \User\store function to store all the info of new user
            if (!$objUser->store()) {
                throw new MultiSiteJsonException($objUser->error_msg);
            }
            //call createWebsite method.
            return $this->createWebsite($objUser,$websiteName);
        } else if ($params['get']['fetchForm']==1){
            $Template = new \Cx\Core\Html\Sigma(ASCMS_CORE_MODULE_PATH . '/MultiSite/View/Template/Frontend');
            $Template->loadTemplateFile('Signup.html');
            $Template->setErrorHandling(PEAR_ERROR_DIE);
            // get website minimum and maximum Name length
            $websiteNameMinLength=\Cx\Core\Setting\Controller\Setting::getValue('websiteNameMinLength');
            $websiteNameMaxLength=\Cx\Core\Setting\Controller\Setting::getValue('websiteNameMaxLength');
            // TODO: implement protocol support / the protocol to use should be defined by a configuration option
            $protocol = 'https';
            if (in_array(\Cx\Core\Setting\Controller\Setting::getValue('mode'), array('manager', 'hybrid'))) {
                $configs = \Env::get('config');
                $multiSiteDomain = $protocol.'://'.$configs['domainUrl'];
            } else {           
                $multiSiteDomain = $protocol.'://'.\Cx\Core\Setting\Controller\Setting::getValue('managerHostname');
            }
            \JS::activate('cx');
            //Add jquery validations library (jquery.validate.min.js)
            \JS::registerJs('lib/javascript/jquery/jquery.validate.min.js');
            \ContrexxJavascript::getInstance()->setVariable('baseUrl', $multiSiteDomain, 'MultiSite');
            $setVariable=array(
                                'TITLE'         => $_ARRAYLANG['TXT_MULTISITE_TITLE'],
                                'TXT_MULTISITE_EMAIL_ADDRESS' => $_ARRAYLANG['TXT_MULTISITE_EMAIL_ADDRESS'],
                                'TXT_MULTISITE_ADDRESS'  => $_ARRAYLANG['TXT_MULTISITE_SITE_ADDRESS'],
                                'TXT_MULTISITE_CREATE_WEBSITE'   => $_ARRAYLANG['TXT_MULTISITE_SUBMIT_BUTTON'],
                                'MULTISITE_DOMAIN'     => \Cx\Core\Setting\Controller\Setting::getValue('multiSiteDomain'),
                                'POST_URL'      => '',
                            );
            $Template->setVariable($setVariable);
            return $Template->get();
        } else {
            $cxJs =  file_get_contents('lib/javascript/cx/contrexxJs.js');
            $cxJs .= file_get_contents('lib/javascript/cx/contrexxJs-tools.js');
            $cxJs .= file_get_contents('lib/javascript/jquery/ui/jquery-ui-1.8.7.custom.min.js');
            $cxJs .= file_get_contents('lib/javascript/cx/ui.js');
            $cxJs .= file_get_contents('core_modules/MultiSite/View/Script/Frontend.js');
            echo $cxJs;die;
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
            $ObjWebsite = new \Cx\Core_Modules\MultiSite\Model\Entity\Website($basepath, $websiteName, $websiteServiceServer, $objUser, false);
            if($websiteId!=''){
                $ObjWebsite->setId($websiteId);
            }
            \Env::get('em')->persist($ObjWebsite);
            \Env::get('em')->flush();
            return $ObjWebsite->setup();
        } catch (\Cx\Core_Modules\MultiSite\Model\Entity\WebsiteException $e) {
            throw new MultiSiteJsonException($e->getMessage());    
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

        if (empty($authenticationValue) || !is_array($authenticationValue)) {
            return false;
        }
    
        switch (\Cx\Core\Setting\Controller\Setting::getValue('mode')) {
            case 'manager':
                try {
                    $WebsiteServiceServerRepository = \Env::get('em')->getRepository('\Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer');
                    $objWebsiteService = $WebsiteServiceServerRepository->findBy(array('hostName' => $authenticationValue['sender']));
                    $secretKey = $objWebsiteService->getSecretKey();
                    $installationId = $objWebsiteService->getInstallationId();
                } catch(\Exception $e) {
                    return $e->getMessage();
                }
                break;

            case 'service':
            case 'hybrid':
                //Check if the sender is manager or not
                if ($authenticationValue['sender'] == \Cx\Core\Setting\Controller\Setting::getValue('managerHostname')) {
                    $secretKey = \Cx\Core\Setting\Controller\Setting::getValue('managerSecretKey');
                    $installationId = \Cx\Core\Setting\Controller\Setting::getValue('managerInstallationId');
                } else {
                    try {
                        $websiteRepository = \Env::get('em')->getRepository('\Cx\Core_Modules\MultiSite\Model\Repository\WebsiteRepository');
                        $objWebsite = $websiteRepository->findBy(array('name' => $authenticationValue['sender']));
                        $secretKey  = $objWebsite->getSecretKey();
                        $installationId = $objWebsite->getInstallationId();
                    } catch (\Exception $e) {
                        return $e->getMessage();
                    }
                }
                break;
                
            case 'Website':
                $secretKey = \Cx\Core\Setting\Controller\Setting::getValue('serviceSecretKey');
                $installationId = \Cx\Core\Setting\Controller\Setting::getValue('serviceInstallationId');
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
     * @param type $params
     * @return type
     */
    public function updateDefaultCodeBase($params) 
    {
        if (!empty($params['post'])) {
            \Cx\Core\Setting\Controller\Setting::init('MultiSite', 'websiteSetup','FileSystem');
            \Cx\Core\Setting\Controller\Setting::set('defaultCodeBase',$params['post']['defaultCodeBase']);
            \Cx\Core\Setting\Controller\Setting::update('defaultCodeBase');
        }
    }
}
