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

    public function preUpdate($eventArgs) {
        global $_ARRAYLANG;
        
        $order = $eventArgs->getEntity();
        
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
        $eventArgs->setNewValue('contactId', $crmContactId);
        $order->setContactId($crmContactId);

        //Update the website Owner
        $subscriptions = $order->getSubscriptions();
        $OrderSubscriptionEventListener = new OrderSubscriptionEventListener();
        foreach ($subscriptions as $subscription) {
            $OrderSubscriptionEventListener->updateWebsiteOwner($subscription, $eventArgs);
        }
    }

    public function onEvent($eventName, array $eventArgs) {
        \DBG::msg(__METHOD__.": $eventName");
        $this->$eventName(current($eventArgs));
    }
}
