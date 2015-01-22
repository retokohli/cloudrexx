<?php
/**
 * This listener is responsible to maintain the relations
 * to the associated domains (Cx\Core_Modules\MultiSite\Model\Entity\Domain)
 * of a website.
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */

namespace Cx\Core_Modules\MultiSite\Model\Event;

/**
 * WebsiteEventListenerException
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class WebsiteEventListenerException extends \Exception {}

/**
 * WebsiteEventListener
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class WebsiteEventListener implements \Cx\Core\Event\Model\Entity\EventListener {
    public function preUpdate($eventArgs) {
        \DBG::msg('MultiSite (WebsiteEventListener): preUpdate');
        $em      = $eventArgs->getEntityManager();
        $uow     = $em->getUnitOfWork();
        $website = $eventArgs->getEntity();
        $domains = $website->getDomains();

        foreach ($domains as $domain) {
            \DBG::msg('Update domain (map to new IP of Website): '.$domain->getName());
            if ($domain->getComponentType() == \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_WEBSITE
                && $domain->getWebsite()
                && !$domain->getComponentId()
            ) {
                \DBG::msg('Domain: Set component ID to website ID: '.$domain->getWebsite()->getId());
                $domain->setComponentId($domain->getWebsite()->getId());
                $uow->computeChangeSet(
                    $em->getClassMetadata('Cx\Core_Modules\MultiSite\Model\Entity\Domain'),
                    $domain
                );
            }
        }
    }

    public function postUpdate($eventArgs) {
        \DBG::msg('MultiSite (WebsiteEventListener): postUpdate');
        $em      = $eventArgs->getEntityManager();
        $website = $eventArgs->getEntity();
        $isWebsiteInactive = ($website->getStatus() == \Cx\Core_Modules\MultiSite\Model\Entity\Website::STATE_DISABLED
                            || $website->getStatus() == \Cx\Core_Modules\MultiSite\Model\Entity\Website::STATE_OFFLINE);
        
        switch (\Cx\Core\Setting\Controller\Setting::getValue('mode')) {
            case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_HYBRID:
                //disable website mail service
                if ($isWebsiteInactive) {
                    $this->disableWebsiteMailService($website);
                }
                
            case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_SERVICE:
                $websiteConfigPath = \Cx\Core\Setting\Controller\Setting::getValue('websitePath') . '/' . $website->getName() . \Env::get('cx')->getConfigFolderName();
                if (!file_exists($websiteConfigPath)) {
                    break;
                }
                \Cx\Core\Setting\Controller\Setting::init('MultiSite', '', 'FileSystem', $websiteConfigPath);
                \Cx\Core\Setting\Controller\Setting::set('websiteState', $website->getStatus());
                \Cx\Core\Setting\Controller\Setting::update('websiteState');
                // we must re-initialize the original MultiSite settings of the main installation
                \Cx\Core\Setting\Controller\Setting::init('MultiSite', '', 'FileSystem');
                break;

            case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_MANAGER:
// TODO: updating ip address of domain is somehow redundant. it should only be done if ip address of website has changed
                //disable website mail services
                if ($isWebsiteInactive) {
                    $this->disableWebsiteMailService($website);
                }
                
                $domains = $website->getDomains();
                foreach ($domains as $domain) {
                    \DBG::msg('Update domain (map to new IP of Website): '.$domain->getName());
                    \Env::get('cx')->getEvents()->triggerEvent('model/preUpdate', array(new \Doctrine\ORM\Event\LifecycleEventArgs($domain, $em)));
                }
                
                //hostName
                $websiteServiceServer = $website->getWebsiteServiceServer();

                $params = array(
                    'websiteId'   => $website->getId(),
                    'status'      => $website->getStatus(),
                );
                \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::executeCommandOnServiceServer('setWebsiteState', $params, $websiteServiceServer);
                break;
        }
    }
    
    /**
     * Disable website mail service
     * 
     * @param \Cx\Core_Modules\MultiSite\Model\Entity\Website $website website
     * 
     * @return boolean
     * @throws \Cx\Core_Modules\MultiSite\Model\Entity\WebsiteException
     */
    public function disableWebsiteMailService(\Cx\Core_Modules\MultiSite\Model\Entity\Website $website) {
        if (\FWValidator::isEmpty($website)) {
            return;
        }
        try {
            $mailServiceServer = $website->getMailServiceServer();
            $mailAccountId     = $website->getMailAccountId();

            if (\FWValidator::isEmpty($mailServiceServer) || \FWValidator::isEmpty($mailAccountId)) {
                return;
            }
            
            $hostingController = \Cx\Core_Modules\MultiSite\Controller\ComponentController::getMailServerHostingController($mailServiceServer);
            $status = $hostingController->getMailServiceStatus($mailAccountId);
            if ($status == 'true') {
                $mailServiceServer->disableService($mailAccountId);
            }
        } catch (Exception $e) {
            \DBG::log('Unable to disable the website mail service: ' . $e->getMessage());
            throw new \Cx\Core_Modules\MultiSite\Model\Entity\WebsiteException('Unable to disable the website mail service');
        }
    }
    
    public function preRemove($eventArgs) {
        \DBG::msg('MultiSite (WebsiteEventListener): preRemove');
        $website = $eventArgs->getEntity();
        
        try {
             $website->destroy();
        } catch (\Exception $e) {
            throw new \Cx\Core_Modules\MultiSite\Model\Entity\WebsiteException('Unable to delete the website'.  $e->getMessage());
        } 
    }

    public function payComplete($eventArgs) {
        \DBG::msg('MultiSite (WebsiteEventListener): payComplete');
        $subscription = $eventArgs->getEntity();
        $website      = $subscription->getProductEntity();
        $entityAttributes = $subscription->getProduct()->getEntityAttributes();
        // abort in case the event has been triggered by a Subscription that is not based on a Website product
        if (!($website instanceof \Cx\Core_Modules\MultiSite\Model\Entity\Website)) {
            return;
        }

        $entityAttributes['initialSignUp'] = false;
        switch ($website->getStatus()) {
            case \Cx\Core_Modules\MultiSite\Model\Entity\Website::STATE_INIT:
                // perform initial sign-up in case the user has not yet been verified
                $entityAttributes['initialSignUp'] = !$website->getOwner()->isVerified();
                return $website->setup($entityAttributes);
                break;

            case \Cx\Core_Modules\MultiSite\Model\Entity\Website::STATE_DISABLED:
                $website->setStatus(\Cx\Core_Modules\MultiSite\Model\Entity\Website::STATE_OFFLINE);

            case \Cx\Core_Modules\MultiSite\Model\Entity\Website::STATE_ONLINE:
            case \Cx\Core_Modules\MultiSite\Model\Entity\Website::STATE_OFFLINE:
                $website->setupLicense($entityAttributes);
                break;

            default:
                break;
        }
    }

    public function onEvent($eventName, array $eventArgs) {        
        $this->$eventName(current($eventArgs));
    }
}
