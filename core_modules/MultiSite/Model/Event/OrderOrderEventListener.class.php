<?php

/**
 * OrderOrderEventListener
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */

namespace Cx\Core_Modules\MultiSite\Model\Event;

/**
 * Class OrderOrderEventListenerException
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class OrderOrderEventListenerException extends \Exception {}

/**
 * Class OrderOrderEventListener
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class OrderOrderEventListener implements \Cx\Core\Event\Model\Entity\EventListener {

    /**
     * @var boolean
     */
    protected $entitiesToFlushOnPostFlush = false;
    
    public function preUpdate($eventArgs) {
        global $_ARRAYLANG;
        
        $order = $eventArgs->getEntity();
        $em    = $eventArgs->getEntityManager();
        $uow   = $em->getUnitOfWork();
        
        if (!$eventArgs->hasChangedField('contactId')) {
            return;
        }
        
        $userId  = $order->getContactId();
        if (empty($userId)) {
            throw new OrderOrderEventListenerException($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_CHANGE_OWNER_USER_ERROR']);
        }

        $objUser = \FWUser::getFWUserObject()->objUser->getUser($userId);
        if (!$objUser) {
            throw new OrderOrderEventListenerException($_ARRAYLANG['TXT_CORE_MODULE_MULTISITE_CHANGE_OWNER_USER_ERROR']);
        }

        // in case contactId has been changed, we have to ensure the contactId is a CrmContact
        $crmContactId = $objUser->getCrmUserId();
        if (\FWValidator::isEmpty($crmContactId)) {
            // create a new CRM Contact and link it to the User account
            $crmContactId = \Cx\Modules\Crm\Controller\CrmLibrary::addCrmContactFromAccessUser($objUser);
        }
        $order->setContactId($crmContactId);
        $uow->computeChangeSet(
            $em->getClassMetadata('Cx\Modules\Order\Model\Entity\Order'),
            $order
        );

        //Update the website Owner
        $subscriptions = $order->getSubscriptions();
        $OrderSubscriptionEventListener = new OrderSubscriptionEventListener();
        foreach ($subscriptions as $subscription) {
            $OrderSubscriptionEventListener->updateWebsiteOwner($subscription);
        }
        $this->entitiesToFlushOnPostFlush = true;
    }

    public function postFlush($eventArgs) {
        // check if there are any new entities present we need to persist
        if (!$this->entitiesToFlushOnPostFlush) {
            return;
        }
        
        $this->entitiesToFlushOnPostFlush = false;
        // persist the new entities
        $em = \Cx\Core\Core\Controller\Cx::instanciate()->getDb()->getEntityManager();
        $em->flush();
    }
    
    public function onEvent($eventName, array $eventArgs) {
        \DBG::msg(__METHOD__.": $eventName");
        $this->$eventName(current($eventArgs));
    }
}
