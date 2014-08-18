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
    public function onPrePay($eventArgs) {
        $subscription = $eventArgs->getEntity();
        $productEntityId = $subscription->getProductEntityId();
        $productEntity = $subscription->getProduct()->getEntityById($productEntityId);
        $productEntity->onSubscriptionPrePay();
    }

    public function onPostPay($eventArgs) {
        $subscription = $eventArgs->getEntity();
        if ($subscription->getProduct()->isRenewable()) {
            // update renewal period and date
            list($subscription->getRenewalUnit(), $subscription->getRenewalQuantifier()) = $subscription->getProduct()->getRenewalDefinition($subscription->getRenewalUnit(), $subscription->getRenewalQuantifier());
            $renewalDate = $subscription->getProduct()->getRenewalDate($subscription->getRenewalUnit(), $subscription->getRenewalQuantifier());
            $subscription->setRenewalDate($renewalDate);
            $subscription->setPaymentState(self::PAYMENT_RENEWAL);
        } else {
            $subscription->setPaymentState(self::PAYMENT_PAID);
        }
        $productEntity = $subscription->getProduct()->getEntityById($subscription->getProductEntityId());
        $productEntity->onSubscriptionPostPay();
    }

    public function onPreRenewal($eventArgs) {}

    public function onPostRenewal($eventArgs) {}

    public function onPreExpire($eventArgs) {}

    public function onPostExpire($eventArgs) {}

    public function onPreCancel($eventArgs) {}

    public function onPostCancel($eventArgs) {}
    
    public function onEvent($eventName, array $eventArgs) {        
        $this->$eventName(current($eventArgs));
    }
}

