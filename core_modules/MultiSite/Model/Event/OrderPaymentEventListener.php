<?php

/**
 * OrderPaymentEventListener

 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */

namespace Cx\Core_Modules\MultiSite\Model\Event;

/**
 * Class OrderPaymentEventListenerException
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class OrderPaymentEventListenerException extends \Exception 
{
    
}

/**
 * Class OrderPaymentEventListener
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class OrderPaymentEventListener implements \Cx\Core\Event\Model\Entity\EventListener
{
    /**
     * PostPersist Event
     * 
     * @param type $eventArgs
     */
    public function postPersist($eventArgs)
    {
        $transaction = $eventArgs->getEntity();
        $transactionData = $transaction->getTransactionData();
        
        if (\FWValidator::isEmpty($transactionData)) {
            return;
        }
        
        $subscription = isset($transactionData['subscription']) ? $transactionData['subscription'] : $transactionData;
        if (\FWValidator::isEmpty($subscription)) {
            return;
        }
        
        $subscriptionId = isset($subscription['id']) ? $subscription['id'] : '';
        $subscriptionValidDate = isset($subscription['valid_until']) ? $subscription['valid_until'] : '';
        
        if (\FWValidator::isEmpty($subscriptionId) || \FWValidator::isEmpty($subscriptionValidDate)) {
            return;
        }
        
        $subscriptionRepo = \Env::get('em')->getRepository('Cx\Modules\Order\Model\Entity\Subscription');
        $objSubscription  = $subscriptionRepo->findOneBy(array('externalSubscriptionId' => $subscriptionId));
        
        if (\FWValidator::isEmpty($objSubscription)) {
            return;
        }
        
        $objSubscription->setRenewalDate(new \DateTime($subscriptionValidDate));
        
        \Env::get('em')->flush();
    }
    
    public function onEvent($eventName, array $eventArgs)
    {
        $this->$eventName(current($eventArgs));
    }
}
