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
        switch (\Cx\Core\Setting\Controller\Setting::getValue('mode')) {
            case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_HYBRID:
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

        switch ($website->getStatus()) {
            case \Cx\Core_Modules\MultiSite\Model\Entity\Website::STATE_INIT:
                return $website->setup($entityAttributes);
                break;

            case \Cx\Core_Modules\MultiSite\Model\Entity\Website::STATE_ONLINE:
// TODO: maybe add notification message to dashboard about extended subscription or send email about extended subscription
                break;

            case \Cx\Core_Modules\MultiSite\Model\Entity\Website::STATE_OFFLINE:
// TODO: reactivate website
                break;

            default:
                break;
        }
    }

    public function onEvent($eventName, array $eventArgs) {        
        $this->$eventName(current($eventArgs));
    }
}
