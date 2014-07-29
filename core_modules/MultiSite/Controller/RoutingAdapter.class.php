<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cx\Core_Modules\MultiSite\Controller;

class MultiSiteRoutingException extends \Exception {}

/**
 * Description of newPHPClass
 *
 * @author ritt0r
 */
class RoutingAdapter implements \Cx\Core\Json\JsonAdapter {
    protected $messages = '';
    
    public function getAccessableMethods() {
        return array('route','create','update','remove');
    }
    
    public function getMessagesAsString() {
        return $this->messages;
    }
    
    public function getName() {
        return 'RoutingAdapter';
    }
    
    /**
     * Returns default permission as object
     * @return Object
     */
    public function getDefaultPermissions() {
        return null;
    }
    
    /**
     * Routes all requests to the installation with the supplied email address
     * 
     * Failed requests to the login method of JsonUser adapter prevent returning
     * the installation name.
     * @param type $params 
     */
    public function route($params) {
        global $_ARRAYLANG, $sessionObj;
        
        $sessionObj = $sessionObj ? $sessionObj : new \cmsSession();
        if (empty($params['post'])) {
            $rawPostData = file_get_contents("php://input");
            if (!empty($rawPostData) && ($arrRawPostData = explode('&', $rawPostData)) && !empty($arrRawPostData)) {
                $arrPostData = array();
                foreach ($arrRawPostData as $postData) {
                    if (!empty($postData)) {
                        list($postKey, $postValue) = explode('=', $postData);
                        $arrPostData[$postKey] = $postValue;
                    }
                }
                $params['post'] = $arrPostData;
            }
        }

        $lang = 'de';
        if (isset($params['get']) && isset($params['get']['lang'])) {
            $lang = $params['get']['lang'];
        }
        \Env::get('ClassLoader')->loadFile(ASCMS_CORE_MODULE_PATH.'/MultiSite/lang/' . $lang . '/backend.php');
        if (
            !isset($params['get']) ||
            !isset($params['get']['mail']) ||
            !isset($params['get']['adapter']) ||
            !isset($params['get']['method'])
        ) {
            throw new MultiSiteRoutingException('Not enough arguments');
        }
        
        //dynamic use websites path
        \Cx\Core\Setting\Controller\Setting::init('MultiSite', 'config','FileSystem');
        $websitesPath=\Cx\Core\Setting\Controller\Setting::getValue('websitesPath');
        
        // search for website with mail
        $instRepo = \Env::get('em')->getRepository('\Cx\Core_Modules\MultiSite\Model\Entity\Website');
        $website = $instRepo->findByMail($params['get']['mail']);
        // perform sub request to website
        try {
            if (!$website) {
                throw new MultiSiteRoutingException($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_NO_SUCH_WEBSITE']);
            }
            $response = $this->performSubRequest($website, $params['get'], $params['post']);
        } catch (MultiSiteRoutingException $e) {
            // throw exception again if the user didn't try to log in
            // for login requests we have a special handling down below
            if ($params['get']['adapter'] != 'user' &&
                $params['get']['method'] != 'loginUser') {
                throw $e;
            }
        }
        
        // do not send normal answer on failed login for security reasons
        // (so someone cannot know if an website for this mail address
        // exists or if login has failed)
        if (
            $params['get']['adapter'] == 'user' &&
            $params['get']['method'] == 'loginUser'
        ) {
            // check captcha
            if (isset($_SESSION['auth']['loginLastAuthFailedCrossDomain']) && $_SESSION['auth']['loginLastAuthFailedCrossDomain']) {
                $_POST['coreCaptchaCode'] = strtoupper($params['post']['captcha']);
                if (!\FWCaptcha::getWebsite()->check()) {
                    $this->messages = $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_NO_SUCH_WEBSITE'];
                    return array(
                        'status' => 'error',
                        'phpsessionid' => session_id(),
                        'captcha' => 'failed',
                    );
                    //throw new MultiSiteRoutingException($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_NO_SUCH_WEBSITE']);
                }
            }
            
            unset($_SESSION['auth']['loginLastAuthFailedCrossDomain']);
            
            if (
                !$response ||
                $response->status != 'success' ||
                $response->data == false
            ) {
                $_SESSION['auth']['loginLastAuthFailedCrossDomain'] = true;
                $this->messages = $_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_NO_SUCH_WEBSITE'];
                return array(
                    'status' => 'error',
                    'phpsessionid' => session_id(),
                );
                //throw new MultiSiteRoutingException($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_NO_SUCH_WEBSITE']);
            }
            $response->data->websiteName = $website->getName();
            $response->data->websiteUrl = 'https://' . $website->getName() . '.' . substr(\Cx\Core\Setting\Controller\Setting::getValue('multiSiteDomain'), 0) . '/cadmin/index.php?autoLogin=' . $response->data->key;
        }
        
        // rethrow exceptions
        if ($response->status != 'success') {
            throw new \Exception($response->message);
        }
        
        // return answer
        $this->messages = $response->message;
        return $response->data;
    }
    
    protected function performSubRequest($website, $subRequestGetParams, $subRequestPostParams) {
        global $_ARRAYLANG;
        // make sub request
        $jd = new \Cx\Core\Json\JsonData();
        $url = 'https://' . $website->getName() . '.' . substr(\Cx\Core\Setting\Controller\Setting::getValue('multiSiteDomain'), 0) .
                '/cadmin/index.php?cmd=jsondata&object=' . $subRequestGetParams['adapter'] .
                '&act=' . $subRequestGetParams['method'];
        unset($subRequestGetParams['mail']);
        unset($subRequestGetParams['adapter']);
        unset($subRequestGetParams['method']);
        if (count($subRequestGetParams)) {
            $url .= '&' . implode('&', $subRequestGetParams);
        }

        return $jd->getJson(
            $url,
            $subRequestPostParams
        );
    }
    
    /**
     * create method, create a new user account
     * Create user if requested to.
     * @param type $params 
     * @return  boolean     TRUE on success, FALSE on failure.
     */
    public function create($params) {
        if (!isset($params['get']) ||
            !isset($params['get']['adapter']) ||
            !isset($params['get']['method'])
        ) {
            throw new MultiSiteRoutingException('Not enough arguments');
        }
        if ($params['get']['adapter'] == 'multisite' &&
            $params['get']['method'] == 'createUser'
        ) {
            $objUser = new \Cx\Core_Modules\MultiSite\Model\Entity\User();
            $objUser->setUsername(!empty($params['post']['userName']) ? trim(contrexx_stripslashes($params['post']['userName'])) : '');
            $objUser->setEmail(!empty($params['post']['emailAddress']) ? trim(contrexx_stripslashes($params['post']['emailAddress'])) : '');
            $objUser->setFrontendLanguage(!empty($params['post']['frontendLanguage']) ? intval($params['post']['frontendLanguage']) : 0);
            $objUser->setBackendLanguage(!empty($params['post']['backendLanguage']) ? intval($params['post']['backendLanguage']) : 0);
            $objUser->setActiveStatus(!empty($params['post']['userActive']) ? (bool)$params['post']['userActive'] : false);
            $objUser->setProfileAccess(!empty($params['post']['profileAccess']) && $objUser->isAllowedToChangeProfileAccess() ? trim(contrexx_stripslashes($params['post']['profileAccess'])) : '');
            $objUser->setPassword(!empty($params['post']['password']) ? trim(contrexx_stripslashes($params['post']['password'])) : '');
            
            $arrProfile = array(
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
            $objUser->setProfile($arrProfile);
            $objUser->store();
            return true;
        } else {
            throw new MultiSiteRoutingException('Invalid arguments');
        }
    } 
    /**
     * update an existing user account
     * Update user if user is exists
     * @param type $params 
     * @return  boolean     TRUE on success, FALSE on failure.
     */
    public function update($params) {
        if (!isset($params['get']) ||
            !isset($params['get']['adapter']) ||
            !isset($params['get']['method'])
        ) {
            throw new MultiSiteRoutingException('Not enough arguments');
        }
        if ($params['get']['adapter'] == 'multisite' &&
            $params['get']['method'] == 'updateUser'
        ) {
            if (!empty($params['post']['userId']) &&
                $params['post']['userId']!==0
            ) {
                $objFWUser = \FWUser::getFWUserObject();
                if (($objUser = $objFWUser->objUser->getUser(isset($params['post']['userId']) ? intval($params['post']['userId']) : 0)) === false) {
                    //$objUser = new \Cx\Core_Modules\MultiSite\Model\Entity\User();
                    throw new MultiSiteRoutingException('Invalid user');
                }
                // only administrators are allowed to change a users account. or users may be allowed to change their own account
                if (!\Permission::hasAllAccess() && ($objUser->getId() != $objFWUser->objUser->getId() || !\Permission::checkAccess(31, 'static', true))) {
                    \Permission::noAccess();
                }
                $objUser->setUsername(!empty($params['post']['userName']) ? trim(contrexx_stripslashes($params['post']['userName'])) : '');
                $objUser->setEmail(!empty($params['post']['emailAddress']) ? trim(contrexx_stripslashes($params['post']['emailAddress'])) : '');
                $objUser->setFrontendLanguage(!empty($params['post']['frontendLanguage']) ? intval($params['post']['frontendLanguage']) : 0);
                $objUser->setBackendLanguage(!empty($params['post']['backendLanguage']) ? intval($params['post']['backendLanguage']) : 0);
                $objUser->setActiveStatus(!empty($params['post']['userActive']) ? (bool)$params['post']['userActive'] : false);
                $objUser->setProfileAccess(!empty($params['post']['profileAccess']) && $objUser->isAllowedToChangeProfileAccess() ? trim(contrexx_stripslashes($params['post']['profileAccess'])) : '');
                $objUser->setPassword(!empty($params['post']['password']) ? trim(contrexx_stripslashes($params['post']['password'])) : '');
                
                $arrProfile = array(
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
                $objUser->setProfile($arrProfile);
                $objUser->store();
                return true;
            } else {
                throw new MultiSiteRoutingException('Invalid user Id');
            }
        } else {
            throw new MultiSiteRoutingException('Invalid arguments');
        }
    } 
    /**
     * Remove an existing user account
     * Remove user if user is exists
     * @param type $params 
     * @return  boolean     TRUE on success, FALSE on failure.
     */
    public function remove($params) {
        if (!isset($params['get']) ||
            !isset($params['get']['adapter']) ||
            !isset($params['get']['method'])
        ) {
            throw new MultiSiteRoutingException('Not enough arguments');
        }
        if ($params['get']['adapter'] == 'multisite' &&
            $params['get']['method'] == 'removeUser'
        ) {
            if (!empty($params['post']['userId']) &&
                $params['post']['userId']!=0
            ) { 
                $objFWUser = \FWUser::getFWUserObject();
                if (($objUser = $objFWUser->objUser->getUser(isset($params['post']['userId']) ? intval($params['post']['userId']) : 0)) === false) {
                    throw new MultiSiteRoutingException('Invalid user');
                }
                // only administrators are allowed to delete a users account. or users may be allowed to delete their own account
                if (!\Permission::hasAllAccess() && ($objUser->getId() != $objFWUser->objUser->getId() || !\Permission::checkAccess(31, 'static', true))) {
                    \Permission::noAccess();
                }
               
                if ($objUser->getUser($params['post']['userId']) === false) {
                    throw new MultiSiteRoutingException('Invalid user');
                }
                if ($objUser->delete()) {
                    return true;
                } else {
                    throw new MultiSiteRoutingException('Invalid user');
                }
                
            } else {
                throw new MultiSiteRoutingException('Invalid user Id');
            }
        } else {
            throw new MultiSiteRoutingException('Invalid arguments');
        }
    }   
}
