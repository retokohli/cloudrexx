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
        $objUser->objAttribute->first();
        while (!$objUser->objAttribute->EOF) {
            $arrProfile[$objUser->objAttribute->getId()][] = $objUser->getProfileAttribute($objUser->objAttribute->getId());
            $objUser->objAttribute->next();
        }
        
        try { 
            \Cx\Core\Setting\Controller\Setting::init('MultiSite', '','FileSystem');
            $websiteUserId = \Cx\Core\Setting\Controller\Setting::getValue('websiteUserId');
            $crmId = $objUser->getCrmUserId();
            if(!empty($crmId)) {
                $objCrmLib = new \Cx\Modules\Crm\Controller\CrmLibrary('Crm');
                $objCrmLib->setContactPersonProfile($arrProfile, $objUser->getId());
            }
            
            $mode = \Cx\Core\Setting\Controller\Setting::getValue('mode');
            switch ($mode) {
                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_MANAGER:
                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_HYBRID:
                    if (empty($crmId)) {
                        /*here code for add new crm contact*/
                        foreach ($arrProfile as $key => $value) {
                           $arrProfile['fields'][] = array('special_type' => 'access_'.$key);
                           $arrProfile['data'][]   = $value[0];
                           unset($arrProfile[$key]);
                        }
                        $arrProfile['fields'][] = array('special_type' => 'access_email');
                        $arrProfile['data'][]   = $objUser->getEmail();
                        $objCrmLibrary = new \Cx\Modules\Crm\Controller\CrmLibrary('Crm');
                        $objCrmLibrary->addCrmContact($arrProfile);
                    }
                    break;
                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_WEBSITE:
                    if ($websiteUserId == 0) {
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
        $objUser->objAttribute->first();
        while (!$objUser->objAttribute->EOF) {
            $arrProfile[$objUser->objAttribute->getId()] = $objUser->getProfileAttribute($objUser->objAttribute->getId());
            $objUser->objAttribute->next();
        }
        
        try {
            \Cx\Core\Setting\Controller\Setting::init('MultiSite', '','FileSystem');
            $mode = \Cx\Core\Setting\Controller\Setting::getValue('mode');
            $websiteUserId = \Cx\Core\Setting\Controller\Setting::getValue('websiteUserId');
            switch ($mode) {
                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_WEBSITE:
                    //this aborting process still now working
                    if ($websiteUserId == $objUser->getId()) {
                        return;
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
        $objUser->objAttribute->first();
        while (!$objUser->objAttribute->EOF) {
            $arrProfile[$objUser->objAttribute->getId()] = $objUser->getProfileAttribute($objUser->objAttribute->getId());
            $objUser->objAttribute->next();
        }
        try {
            \Cx\Core\Setting\Controller\Setting::init('MultiSite', '','FileSystem');
            $mode = \Cx\Core\Setting\Controller\Setting::getValue('mode');
            $websiteUserId = \Cx\Core\Setting\Controller\Setting::getValue('websiteUserId');
            switch ($mode) {
                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_WEBSITE:
                    //this aborting process still now working
                    if ($websiteUserId == $objUser->getId()) {
                        return;
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
        $objUser->objAttribute->first();
        while (!$objUser->objAttribute->EOF) {
            $arrProfile[$objUser->objAttribute->getId()][] = $objUser->getProfileAttribute($objUser->objAttribute->getId());
            $objUser->objAttribute->next();
        }
        
        try {
            \Cx\Core\Setting\Controller\Setting::init('MultiSite', '','FileSystem');
            //find each associated websites
            $webRepo  = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website');
            $websites = $webRepo->findBy(array('ownerId' => $objUser->getId()));
            
            if (!isset($websites)) {
                return;
            }
            
            foreach($websites as $website) {
                switch(\Cx\Core\Setting\Controller\Setting::getValue('mode')) {
                    case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_MANAGER:
                        $hostName = $website->getWebsiteServiceServer()->getHostname();
                        $installationId = \Cx\Core\Setting\Controller\Setting::getValue('managerInstallationId');  
                        $secretKey = \Cx\Core\Setting\Controller\Setting::getValue('managerSecretKey');
                        $httpAuth = array(
                            'httpAuthMethod' => \Cx\Core\Setting\Controller\Setting::getValue('managerHttpAuthMethod'),
                            'httpAuthUsername' => \Cx\Core\Setting\Controller\Setting::getValue('managerHttpAuthUsername'),
                            'httpAuthPassword' => \Cx\Core\Setting\Controller\Setting::getValue('managerHttpAuthPassword'),
                        );
                        $param['post'] = array(
                            'websiteId' => $website->getId(),
                            'auth'      => \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::getAuthenticationObject($secretKey, $installationId)
                        );
                        break;
                    case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_HYBRID:
                        $hostName = $website->getBaseDn()->getName();
                        //getting secret key and installation id, httpAuth and params pending
                        break;
                    default:
                        break;
                }
            }
            
            $objJsonData = new \Cx\Core\Json\JsonData();
            $objJsonData->getJson('https://'.$hostName.'/cadmin/index.php?cmd=JsonData&object=MultiSite&act=updateUser', $params, false, '', $httpAuth);
        } catch (\Exception $e) {
            \DBG::msg($e->getMessage());
        }
    }
    
    public function onEvent($eventName, array $eventArgs) {        
        $this->$eventName(current($eventArgs));
    }
}

