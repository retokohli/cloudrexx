<?php
/**
 * WebsiteCollectionEventListener class
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */

namespace Cx\Core_Modules\MultiSite\Model\Event;

/**
 * WebsiteCollectionEventListenerException
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class WebsiteCollectionEventListenerException extends \Exception {}

/**
 * WebsiteCollectionEventListener
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class WebsiteCollectionEventListener implements \Cx\Core\Event\Model\Entity\EventListener {
    
    /**
     * Pay Complete Event
     * 
     * @param type $eventArgs
     */
    public function payComplete($eventArgs) {
        \DBG::msg('MultiSite (WebsiteCollectionEventListener): payComplete');
        $subscription           = $eventArgs->getEntity();
        $websiteCollection      = $subscription->getProductEntity();
        $entityAttributes       = $subscription->getProduct()->getEntityAttributes();
        
        if (!($websiteCollection instanceof \Cx\Core_Modules\MultiSite\Model\Entity\WebsiteCollection)) {
            return;
        }
        
        foreach ($websiteCollection->getWebsites() as $website) {
            if (!($website instanceof \Cx\Core_Modules\MultiSite\Model\Entity\Website)) {
                continue;
            }

            switch ($website->getStatus()) {
                case \Cx\Core_Modules\MultiSite\Model\Entity\Website::STATE_INIT:
                    $entityAttributes['initialSignUp'] = true;
                    $website->setup($entityAttributes);
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
        
    }
    /**
     * Terminated Event Change the website status to offline
     * 
     * @param object $eventArgs
     */
    public function terminated($eventArgs) {
        \DBG::msg('MultiSite (WebsiteCollectionEventListener): terminated');
        $subscription      = $eventArgs->getEntity();
        $websiteCollection = $subscription->getProductEntity();
        
        //Set all the associated websiteCollections website to offline
        if ($websiteCollection instanceof \Cx\Core_Modules\MultiSite\Model\Entity\WebsiteCollection) {
            foreach ($websiteCollection->getWebsites() as $website) {
                $website->setStatus(\Cx\Core_Modules\MultiSite\Model\Entity\Website::STATE_OFFLINE);
            }
        }
        
    }

    public function onEvent($eventName, array $eventArgs) {        
        $this->$eventName(current($eventArgs));
    }
}