<?php
/**
 * SubscriptionEventListener
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_order
 */

namespace Cx\Modules\Order\Model\Event;

/**
 * SubscriptionEventListener
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_order
 */
class SubscriptionEventListener implements \Cx\Core\Event\Model\Entity\EventListener {
    public function postPersist($eventArgs) {
        $em = $eventArgs->getEntityManager();
        $subscription = $eventArgs->getEntity();

        // the following is a workaround as $productEntity is not a Doctrine
        // Entity which causes its association to Subscription not to be
        // maintained automatically
        $productEntity = $subscription->getProductEntity();
        $em->persist($productEntity);
        $em->flush();
        $subscription->setProductEntity($productEntity);
        $em->persist($subscription);
        $em->flush();
    }
    
    public function onEvent($eventName, array $eventArgs) {        
        $this->$eventName(current($eventArgs));
    }
}

