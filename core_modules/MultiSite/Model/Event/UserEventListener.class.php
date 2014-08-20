<?php

/**
 * UserEventListener

 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */

namespace Cx\Core_Modules\MultiSite\Model\Event;

/**
 * UserEventListenerException
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class UserEventListenerException extends \Exception {}

/**
 * UserEventListener
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class UserEventListener implements \Cx\Core\Event\Model\Entity\EventListener {
    public function postPersist($eventArgs) {
        \DBG::msg('MultiSite (UserEventListener): postPersist');
        $objUser = $eventArgs->getEntity();
        
        try {
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode')) {
                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_WEBSITE:
                    \Cx\Core\Setting\Controller\Setting::init('MultiSite', '','FileSystem');
                    $websiteUserId = \Cx\Core\Setting\Controller\Setting::getValue('websiteUserId');
                    if (empty($websiteUserId)) {
                        //set user's id to websiteUserId
                        \Cx\Core\Setting\Controller\Setting::set('websiteUserId', $objUser->getId());
                        \Cx\Core\Setting\Controller\Setting::update('websiteUserId');
                        //set the user as Administrator
                        $objUser->setAdminStatus(1);
                        $objUser->store();
                    }
                    break;
                default:
                    break;
            }
        } catch (\Exception $e) {
            \DBG::msg($e->getMessage());
        }
    }
    
    public function preUpdate($eventArgs) {
        \DBG::msg('MultiSite (UserEventListener): preUpdate');
        $objUser = $eventArgs->getEntity();
        
        try {
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode')) {
                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_WEBSITE:
                    $websiteUserId = \Cx\Core\Setting\Controller\Setting::getValue('websiteUserId');
                    if ($websiteUserId == $objUser->getId()) {
                        throw new \Exception('Website Owner can only be modified within the Customer Panel.');
                    }
                    break;
                default:
                    break;
            }
        } catch (\Exception $e) {
            \DBG::msg($e->getMessage());
        }
    }
    
    public function preRemove($eventArgs) {
        \DBG::msg('MultiSite (UserEventListener): preRemove');
        $objUser = $eventArgs->getEntity();
        
        try {
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode')) {
                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_WEBSITE:
                    $websiteUserId = \Cx\Core\Setting\Controller\Setting::getValue('websiteUserId');
                    if ($websiteUserId == $objUser->getId()) {
                        throw new \Exception('Website Owner can only be modified within the Customer Panel.');
                    }
                    break;
                default:
                    break;
            }
        } catch (\Exception $e) {
            \DBG::msg($e->getMessage());
        }
    }
    
    public function postUpdate($eventArgs) {
        \DBG::msg('MultiSite (UserEventListener): postUpdate');
        
        $objUser = $eventArgs->getEntity();
        foreach($_POST as $key => $value)
        {
            $keyVal = str_replace("access_", "multisite_", $key);
            $postedArray[$keyVal] = $value; 
        }
       
        try {
            $objJsonData = new \Cx\Core\Json\JsonData();
            switch(\Cx\Core\Setting\Controller\Setting::getValue('mode')) {
                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_MANAGER:
                    //Find each associated service servers
                    $webServerRepo  = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer');
                    $webServices = $webServerRepo->findAll();
                    
                    if (!isset($webServices)) {
                        return;
                    }
                    
                    foreach($webServices as $webService) {
                        $hostName             = $webService->getHostname();
                        $httpAuth = array(
                            'httpAuthMethod'   => $webService->getHttpAuthMethod(),
                            'httpAuthUsername' => $webService->getHttpAuthUsername(),
                            'httpAuthPassword' => $webService->getHttpAuthPassword()
                        );
                        $params = array(
                          'auth'   =>   \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::getAuthenticationObject($webService->getSecretKey(), $webService->getInstallationId()),
                          'userId' =>   $objUser->getId(),
                          'multisite_user_profile_attribute' => $postedArray
                        );
                        
                        $objJsonData->getJson(\Cx\Core_Modules\MultiSite\Controller\ComponentController::getApiProtocol().$hostName.'/cadmin/index.php?cmd=JsonData&object=MultiSite&act=updateUser', $params, false, '', $httpAuth);
                    }
                    
                    break;
                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_HYBRID:
                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_SERVICE:
                    //find User's Website
                    $webRepo   = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website');
                    $website   = $webRepo->findBy(array('ownerId' => $objUser->getId()));
                    $hostName  = $website->getBaseDn()->getName();
                    $params = array(
                        'auth'   => \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::getAuthenticationObject($website->getSecretKey(), $website->getInstallationId()),
                        'userId' => $objUser->getId(),
                        'multisite_user_profile_attribute' => $postedArray   
                    );
                    $objJsonData->getJson(\Cx\Core_Modules\MultiSite\Controller\ComponentController::getApiProtocol().$hostName.'/cadmin/index.php?cmd=JsonData&object=MultiSite&act=updateUser', $params, false, '', null);
                    break;
                default:
                    break;
            }
            
        } catch (\Exception $e) {
            \DBG::msg($e->getMessage());
        }
    }
    
    public function onEvent($eventName, array $eventArgs) {        
        $this->$eventName(current($eventArgs));
    }
}

