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
// TODO: add language variable
                        throw new \Exception('User management has been disabled as this Contrexx installation is being operated as a MultiSite Service Server.');
                    }
                    break;
                default:
                    break;
            }
        } catch (\Exception $e) {
            \DBG::msg($e->getMessage());
            throw new \Cx\Core\Error\Model\Entity\ShinyException($e->getMessage());
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
                        if (!$objUser->isVerified()) {
                            throw new \Exception('Diese Funktion ist noch nicht freigeschalten. Aus Sicherheitsgründen bitten wir Sie, Ihre Anmeldung &uuml;ber den im Willkommens-E-Mail hinterlegten Link zu best&auml;tigen. Anschliessend wird Ihnen diese Funktion zur Verf&uuml;gung stehen. <a href="javascript:window.history.back()">Zur&uuml;ck</a>');
                        }

                        //get user's profile details
                        $objUser->objAttribute->first();
                        while (!$objUser->objAttribute->EOF) {
                            $arrUserDetails[$objUser->objAttribute->getId()][] = $objUser->getProfileAttribute($objUser->objAttribute->getId());
                            $objUser->objAttribute->next();
                        }
                        //get user's other details
                        $params = array(
                            'multisite_user_profile_attribute'          => $arrUserDetails,
                            'multisite_user_account_username'           => $objUser->getUsername(),
                            'multisite_user_account_email'              => $objUser->getEmail(),
                            'multisite_user_account_frontend_language'  => $objUser->getFrontendLanguage(),
                            'multisite_user_account_backend_language'   => $objUser->getBackendLanguage(),
                            'multisite_user_account_email_access'       => $objUser->getEmailAccess(),
                            'multisite_user_account_profile_access'     => $objUser->getProfileAccess(),
                            'multisite_user_account_verified'           => $objUser->isVerified(),
                            'multisite_user_account_restore_key'        => $objUser->getRestoreKey(),
                            'multisite_user_account_restore_key_time'   => $objUser->getRestoreKeyTime(),
                            'multisite_user_md5_password'               => $objUser->getHashedPassword(),
                        );
                        try {
                            $objJsonData = new \Cx\Core\Json\JsonData();
                            $resp = \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::executeCommandOnMyServiceServer('executeOnManager', array('command' => 'updateUser', 'params' => $params));
                            if ($resp->status == 'error' || $resp->data->status == 'error') {
                                if (isset($resp->log)) {
                                    \DBG::appendLogsToMemory(array_map(function($logEntry) {return '(Website: './*$this->getName().*/') '.$logEntry;}, $resp->log));
                                }
                                throw new \Exception('Die Aktualisierung des Benutzerkontos hat leider nicht geklapt. <a href="javascript:window.history.back()">Zur&uuml;ck</a>');
                            }
                        } catch (\Exception $e) {
                            \DBG::msg($e->getMessage());
                        }
// TODO: add language variable
                        //throw new \Exception('Das Benutzerkonto des Websitebetreibers kann nicht ge&auml;ndert werden. <a href="javascript:window.history.back()">Zur&uuml;ck</a>');
                    }
                    break;
                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_SERVICE:
                    if (!\Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::isIscRequest()) {
// TODO: add language variable
                        throw new \Exception('User management has been disabled as this Contrexx installation is being operated as a MultiSite Service Server.');
                    }
                    break;
                default:
                    break;
            }
        } catch (\Exception $e) {
            \DBG::msg($e->getMessage());
            throw new \Cx\Core\Error\Model\Entity\ShinyException($e->getMessage());
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
// TODO: add language variable
                        throw new \Exception('Das Benutzerkonto des Websitebetreibers kann nicht ge&auml;ndert werden. <a href="javascript:window.history.back()">Zur&uuml;ck</a>');
                    }
                    break;
                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_SERVICE:
                    if (!\Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::isIscRequest()) {
// TODO: add language variable
                        throw new \Exception('User management has been disabled as this Contrexx installation is being operated as a MultiSite Service Server.');
                    }
                    break;
                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_MANAGER:
                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_HYBRID:
                    $websiteRepository = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website');
                    $website = $websiteRepository->findBy(array('ownerId' => $objUser->getId()));
                    if ($website) {
                        throw new \Exception('This user is linked with Websites, cannot able to delete');
                    }
                    
                    if (\Cx\Core\Setting\Controller\Setting::getValue('mode') == \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_MANAGER) {
                        $websiteServiceServers = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer')->findAll();
                        foreach ($websiteServiceServers as $serviceServer) {
                            $resp = \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::executeCommandOnServiceServer('removeUser', array('userId' => $objUser->getId()), $serviceServer);
                            if (   (isset($resp->status) && $resp->status == 'error')
                                || (isset($resp->data->status) && $resp->data->status == 'error')
                            ) {
                                throw new \Exception('Failed to delete this user');
                            }
                        }
                    }
                    break;
                default:
                    break;
            }
        } catch (\Exception $e) {
            \DBG::msg($e->getMessage());
            throw new \Cx\Core\Error\Model\Entity\ShinyException($e->getMessage());
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
        $params = array(
            'userId'                                    => $objUser->getId(),
            'multisite_user_profile_attribute'          => $arrUserDetails,
            'multisite_user_account_username'           => $objUser->getUsername(),
            'multisite_user_account_email'              => $objUser->getEmail(),
            'multisite_user_account_frontend_language'  => $objUser->getFrontendLanguage(),
            'multisite_user_account_backend_language'   => $objUser->getBackendLanguage(),
            'multisite_user_account_email_access'       => $objUser->getEmailAccess(),
            'multisite_user_account_profile_access'     => $objUser->getProfileAccess(),
            'multisite_user_account_verified'           => $objUser->isVerified(),
            'multisite_user_account_restore_key'        => $objUser->getRestoreKey(),
            'multisite_user_account_restore_key_time'   => $objUser->getRestoreKeyTime(),
            'multisite_user_md5_password'               => $objUser->getHashedPassword(),
        );
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

