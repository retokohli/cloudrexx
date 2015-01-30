<?php

/**
 * AccessUserEventListener

 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */

namespace Cx\Core_Modules\MultiSite\Model\Event;

/**
 * AccessUserEventListenerException
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class AccessUserEventListenerException extends \Exception {}

/**
 * AccessUserEventListener
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class AccessUserEventListener implements \Cx\Core\Event\Model\Entity\EventListener {
    public function postPersist($eventArgs) {
        \DBG::msg('MultiSite (AccessUserEventListener): postPersist');
        $objUser = $eventArgs->getEntity();
        try {
            \Cx\Core\Setting\Controller\Setting::init('MultiSite', '','FileSystem');
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode')) {
                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_WEBSITE:
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
    
    /**
     * PrePersist Event
     * 
     * @param type $eventArgs
     * @throws \Cx\Core\Error\Model\Entity\ShinyException
     */
    public function prePersist($eventArgs) {
        \DBG::msg('MultiSite (AccessUserEventListener): prePersist');
        $objUser = $eventArgs->getEntity();
        
        try {
            \Cx\Core\Setting\Controller\Setting::init('MultiSite', '','FileSystem');
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode')) {
                 case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_SERVICE:
                    if (!\Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::isIscRequest()) {
// TODO: add language variable
                        throw new \Exception('User management has been disabled as this Contrexx installation is being operated as a MultiSite Service Server.');
                    }
                    break;
                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_WEBSITE:
                    //Check Admin Users quota
                    self::checkAdminUsersQuota($objUser);
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
        \DBG::msg('MultiSite (AccessUserEventListener): preUpdate');
        $objUser = $eventArgs->getEntity();
        
        try {
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode')) {
                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_WEBSITE:
                    //Check Admin Users quota
                    $adminUsersList = \Cx\Core_Modules\MultiSite\Controller\ComponentController::getAllAdminUsers();
                    if (!array_key_exists($objUser->getId(), $adminUsersList)) {
                        self::checkAdminUsersQuota($objUser);
                    }
                    
                    $websiteUserId = \Cx\Core\Setting\Controller\Setting::getValue('websiteUserId');
                    if ($websiteUserId == $objUser->getId() && !\Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::isIscRequest()) {
                        if (!$objUser->isVerified()) {
                            throw new \Exception('Diese Funktion ist noch nicht freigeschalten. Aus Sicherheitsgründen bitten wir Sie, Ihre Anmeldung &uuml;ber den im Willkommens-E-Mail hinterlegten Link zu best&auml;tigen. Anschliessend wird Ihnen diese Funktion zur Verf&uuml;gung stehen. <a href="javascript:window.history.back()">Zur&uuml;ck</a>');
                        }
                        
                        $objWebsiteOwner = \FWUser::getFWUserObject()->objUser->getUser($websiteUserId);
                        $response = \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::executeCommandOnMyServiceServer('executeOnManager', array('command' => 'isUniqueEmail', 'params' => array('currentEmail'=> $objWebsiteOwner->getEmail(),'newEmail' => $objUser->getEmail())));
                        if ($response && $response->data->status == 'error') {
                            throw new \Exception("The email ".$objUser->getEmail()." can't be used for this website owner as there is already another website owner used that email.");
                        }
                        
                        $params = self::fetchUserData($objUser);
                        try {
                            $objJsonData = new \Cx\Core\Json\JsonData();
                            $resp = \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::executeCommandOnMyServiceServer('executeOnManager', array('command' => 'updateUser', 'params' => $params));
                            if ($resp->status == 'error' || $resp->data->status == 'error') {
                                if (isset($resp->log)) {
                                    \DBG::appendLogs(array_map(function($logEntry) {return '(Website: './*$this->getName().*/') '.$logEntry;}, $resp->log));
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
        \DBG::msg('MultiSite (AccessUserEventListener): preRemove');
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
        \DBG::msg('MultiSite (AccessUserEventListener): postUpdate');
        
        $objUser = $eventArgs->getEntity();
        $params = self::fetchUserData($objUser);
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

    public static function fetchUserData($objUser) {
        //get user's profile details
        $objUser->objAttribute->first();
        $arrUserDetails = array();
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
        if ($objUser->getId()) {
            $params['userId'] = $objUser->getId();
        }
        return $params;
    }
    
    /**
     * Check the Admin Users Quota
     * 
     * @param \User $objUser
     * @throws \Cx\Core\Error\Model\Entity\ShinyException
     */
    public static function checkAdminUsersQuota(\User $objUser) {
        global $objInit, $_ARRAYLANG;
        
        $langData = $objInit->loadLanguageData('MultiSite');
        $_ARRAYLANG = array_merge($_ARRAYLANG, $langData);
                
        $userGroupIds     = $objUser->getAssociatedGroupIds();
        $backendGroupIds  = \Cx\Core_Modules\MultiSite\Controller\ComponentController::getBackendGroupIds();
        $backendGroupUser = count(array_intersect($backendGroupIds, $userGroupIds));
        if ($objUser->getAdminStatus() || $backendGroupUser)  {
            $options = \Cx\Core_Modules\MultiSite\Controller\ComponentController::getModuleAdditionalDataByType('Access');
            if (!empty($options['AdminUser']) && $options['AdminUser'] > 0) {
                $adminUsers = \Cx\Core_Modules\MultiSite\Controller\ComponentController::getAllAdminUsers();
                $adminUsersCount = count($adminUsers);
                if ($adminUsersCount >= $options['AdminUser']) {
                    $errMsg = sprintf($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_MAXIMUM_ADMINS_REACHED'], $options['AdminUser']);
                    if(!\Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::isIscRequest()) {
                        throw new \Cx\Core\Error\Model\Entity\ShinyException($errMsg.' <a href="index.php?cmd=Access">'.$_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_GO_TO_OVERVIEW'].'</a>');
                    }
                    throw new \Cx\Core\Error\Model\Entity\ShinyException($errMsg);
                
                }
            }
        }
        
        return true;
    }
    
    public function onEvent($eventName, array $eventArgs) {        
        $this->$eventName(current($eventArgs));
    }
}
