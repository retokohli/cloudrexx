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
    
    public function postPersist($eventArgs) {
        \DBG::msg(__METHOD__);
        $this->assignToSubscription($eventArgs);
    }

    protected function assignToSubscription($eventArgs) {
        $websiteCollection = $eventArgs->getEntity();
        $tempData = $websiteCollection->getTempData();
        if (!empty($tempData['assignedSubscriptionId'])) {
            $subscription = \Env::get('em')->getRepository('Cx\Modules\Order\Model\Entity\Subscription')->findOneById($tempData['assignedSubscriptionId']);
            if ($subscription) {
                $subscription->setProductEntity($websiteCollection);
            }
            $websiteCollection->setTempData(array());
            \Cx\Core\Core\Controller\Cx::instanciate()->getDb()->getEntityManager()->flush();
        }
    }
    
    /**
     * Remove the websites under the websiteCollection
     * 
     * @param object $eventArgs
     * @throws WebsiteCollectionEventListenerException
     */
    public function preRemove($eventArgs) {
        \DBG::msg('MultiSite (WebsiteCollectionEventListener): preRemove');
        $websiteCollection = $eventArgs->getEntity();
        $websites = $websiteCollection->getWebsites();

        try {
            if (!\FWValidator::isEmpty($websites)) {
                foreach ($websites as $website) {
                    \Env::get('em')->remove($website);
                }
                \Env::get('em')->flush();
            }
        } catch (\Exception $e) {
            \DBG::log($e->getMessage());
            throw new WebsiteCollectionEventListenerException('Unable to delete the website.');
        }
    }

    public function onEvent($eventName, array $eventArgs) {        
        $this->$eventName(current($eventArgs));
    }
}
