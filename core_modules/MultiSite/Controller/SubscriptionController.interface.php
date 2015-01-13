<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cx\Core_Modules\MultiSite\Controller;


/**
 * Description of newPHPClass
 *
 * @author ritt0r
 */
interface SubscriptionController {

    /**
     * Creates a subscription
     * 
     * @param string  $domain
     * @param integer $ipAddress
     * @param integer $subscriptionStatus
     * @param integer $customerId default null
     * @param integer $planId default null
     * 
     * @return subcription id
     */
    public function createSubscription($domain, $ipAddress, $subscriptionStatus = 0, $customerId = null, $planId = null);
    
    /**
     * Removes a subscription
     * 
     * @param int $subscriptionId
     * 
     * @throws MultiSiteDbException On error
     */
    public function removeSubscription($subscriptionId);
        
    /**
     * Rename a subscription
     * 
     * @param string $domain domain name
     * 
     * @return subscription id
     */
    public function renameSubscriptionName($domain);
    
    /**
     * Creates a user account
     * 
     * @param string  $domain
     * @param string  $password
     * @param string  $role
     * @param integer $accountId
     * 
     * @return id
     */
    public function createUserAccount($domain, $password, $role, $accountId = null);
    
    /**
     * Delete a user account
     * 
     * @param $userAccountId
     * 
     * @return id 
     */
    public function deleteUserAccount($userAccountId);

    /**
     * Creaye a Customer
     * @param \Cx\Core\Model\Model\Entity\Subscription
     * @throws MultiSiteDbException On error
     */
    public function createCustomer(\Cx\Core_Modules\MultiSite\Model\Entity\Customer $customer);

    /**
     * Create new domain alias
     * @param string $aliasName alias name
     */
    public function createDomainAlias($aliasName);
    
    /**
     * Rename the domain alias
     * @param string $oldAliasName old alias name
     * @param string $newAliasName new alias name
     */
    public function renameDomainAlias($oldAliasName, $newAliasName);
    
    /**
     * Remove the domain alias by name
     * @param string $aliasName alias name to delete
     */
    public function deleteDomainAlias($aliasName);
    
    /**
     * Change the plan of the subscription
     * 
     * @param id     $subscriptionId  subcription id
     * @param string $planGuid        planGuid
     * @param string $planExternalId  planExternalId
     */
    public function changePlanOfSubscription($subscriptionId, $planGuid, $planExternalId);
}
