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
     * Change the password from a user account
     * 
     * @param int $userAccountId user id
     * @param string $password
     * 
     * @return id 
     */
    public function changeUserAccountPassword($userAccountId, $password);

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
     */
    public function changePlanOfSubscription($subscriptionId, $planGuid);
    
    /**
     * Create a new auto-login url for Panel.
     * 
     * @param integer $subscriptionId subscription id
     * @param string  $ipAddress      ip address
     * @param string  $sourceAddress  source address
     */
    public function getPanelAutoLoginUrl($subscriptionId, $ipAddress, $sourceAddress);  
    
    /**
     * Get the all available service plans of mail service server
     */
    public function getAvailableServicePlansOfMailServer();
    
    /**
     * Create new site/domain
     * 
     * @param string  $domain         Name of the site/domain to create
     * @param integer $subscriptionId Id of the Subscription assigned for the new site/domain
     * @param string  $documentRoot   Document root to create the site/domain
     */
    public function createSite($domain, $subscriptionId, $documentRoot = 'httpdocs');
    
    /**
     * Renaming the site/domain
     * 
     * @param string $oldDomainName old domain name
     * @param string $newDomainName new domain name
     */
    public function renameSite($oldDomainName, $newDomainName);
    
    /**
     * Remove the site by the domain name.
     * 
     * @param string $domain Domain name to remove
     */
    public function deleteSite($domain);
    
    /**
     * Get all the sites under the existing subscription
     */
    public function getAllSites();

    /**
     * Install the SSL Certificate for the domain
     * 
     * @param string $name                      Certificate name
     * @param string $domain                    Domain name
     * @param string $certificatePrivateKey     certificate private key
     * @param string $certificateBody           certificate body
     * @param string $certificateAuthority      certificate authority
     */
    public function installSSLCertificate($name, $domain, $certificatePrivateKey, $certificateBody = null, $certificateAuthority = null);
    
    /**
     * Fetch the SSL Certificate details
     * 
     * @param string $domain domain name
     */
    public function getSSLCertificates($domain);
    
    /**
     * Remove the SSL Certificates
     * 
     * @param string $domain domain name
     * @param array  $names  certificate names
     */
    public function removeSSLCertificates($domain, $names = array());
}
