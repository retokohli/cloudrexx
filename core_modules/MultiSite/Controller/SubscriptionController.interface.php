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
     * Creates a Subscription
     * @param \Cx\Core\Model\Model\Entity\Subscription
     * @return 
     */
    public function createSubscription(\Cx\Core_Modules\MultiSite\Model\Entity\Customer $customer,\Cx\Core_Modules\MultiSite\Model\Entity\SubscriptionInfo $subscription);
    
    /**
     * Removes a Subscription
     * @param \Cx\Core\Model\Model\Entity\Subscription
     * @throws MultiSiteDbException On error
     */
    public function removeSubscription(\Cx\Core_Modules\MultiSite\Model\Entity\SubscriptionInfo $subscription);
    
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
