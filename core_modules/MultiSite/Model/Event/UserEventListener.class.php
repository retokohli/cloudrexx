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

    public function prePersist($eventArgs) {
        \DBG::msg('MultiSite (UserEventListener): prePersist');
         
        try {
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode')) {
                 case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_SERVICE:
                    if (!\Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::isIscRequest()) {
                        throw new \Exception('User management has been disabled as this Contrexx installation is being operated as a MultiSite Service Server.');
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
                    if ($websiteUserId == $objUser->getId() && !\Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::isIscRequest()) {
                        throw new \Exception('Website Owner can only be modified within the Customer Panel.');
                    }
                    break;
                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_SERVICE:
                    if (!\Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::isIscRequest()) {
                        throw new \Exception('User management has been disabled as this Contrexx installation is being operated as a MultiSite Service Server.');
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
                    if ($websiteUserId == $objUser->getId() && !\Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::isIscRequest()) {
                        throw new \Exception('Website Owner can only be modified within the Customer Panel.');
                    }
                    break;
                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_SERVICE:
                    if (!\Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::isIscRequest()) {
                        throw new \Exception('User management has been disabled as this Contrexx installation is being operated as a MultiSite Service Server.');
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
        //get user's profile details
        $objUser->objAttribute->first();
        while (!$objUser->objAttribute->EOF) {
            $arrUserDetails[$objUser->objAttribute->getId()][] = $objUser->getProfileAttribute($objUser->objAttribute->getId());
            $objUser->objAttribute->next();
        }
        //get user's other details
        $arrUserDetails['multisite_user_username'] = $objUser->getUsername();
        $arrUserDetails['multisite_user_email']    = $objUser->getEmail();
        $arrUserDetails['multisite_user_frontend_language'] = $objUser->getFrontendLanguage();
        $arrUserDetails['multisite_user_backend_language']  = $objUser->getBackendLanguage();
        $arrUserDetails['multisite_user_email_access']      = $objUser->getEmailAccess();
        $arrUserDetails['multisite_user_profile_access']    = $objUser->getProfileAccess();
        $arrUserDetails['multisite_password']               = $objUser->getHashedPassword();
        try {
            $objJsonData = new \Cx\Core\Json\JsonData();
            switch(\Cx\Core\Setting\Controller\Setting::getValue('mode')) {
                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_MANAGER:
                    //Find each associated service servers
                    $webServerRepo = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer');
                    $webSiteRepo   = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website');
                    $websites      = $webSiteRepo->findWebsitesByOwnerId($objUser->getId());
                    
                    if (!isset($websites)) {
                        return;
                    }
                    
                    foreach ($websites As $website) {
                        $websiteServiceServerId = $website->getWebsiteServiceServerId();
                        $websiteServiceServer   = $webServerRepo->findOneBy(array('id' => $websiteServiceServerId));
                    
                        if ($websiteServiceServer) {
                            $params = array(
                                'userId'                           => $objUser->getId(),
                                'multisite_user_profile_attribute' => $arrUserDetails,
                            );
                            \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::executeCommandOnServiceServer('updateUser', $params, $websiteServiceServer);
                        }
                    }
                    break;
                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_HYBRID:
                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_SERVICE:
                    //find User's Website
                    $webRepo   = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website');
                    $websites  = $webRepo->findBy(array('ownerId' => $objUser->getId()));
                    foreach ($websites As $website) {
                        $params = array(
                            'userId'                           => $objUser->getId(),
                            'multisite_user_profile_attribute' => $arrUserDetails,
                        );
                        \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::executeCommandOnWebsite('updateUser', $params, $website);
                    }
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

