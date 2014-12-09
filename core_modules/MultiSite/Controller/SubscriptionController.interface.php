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
     * @param string $domain
     * @param int $planId
     * @param string $subscriptionStatus
     * @param int $customerId
     * 
     * @return subcription id
     */
    public function createSubscription($domain, $planId, $subscriptionStatus = 0, $customerId = null);
    
    /**
     * Removes a subscription
     * 
     * @param int $subscriptionId
     * @throws MultiSiteDbException On error
     */
    public function removeSubscription($subscriptionId);
    
     /**
     * Creates a user account
      * 
     * @param string $domain
     * @param string $role
     * @param string $password
     * @return 
     */
    public function createUserAccount($domain, $role, $password);
    
    /**
     * Delete a user account
     * 
     * @param $userAccountId
     * @return 
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
}
