<?php

/**
 * Class MailServiceServer
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */

namespace Cx\Core_Modules\MultiSite\Model\Entity;

/**
 * Class MailServiceServer
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class MailServiceServer extends \Cx\Model\Base\EntityBase {
    
    /**
     * @var int $id
     */
    protected $id;

    /**
     * @var string $label
     */
    protected $label;
    
    /**
     * @var string $type 
     */
    protected $type;
    
    /**
     * @var string $hostname
     */
    protected $hostname;
    
    /**
     *
     * @var string $authUsername
     */
    protected $authUsername;
    
    /**
     *
     * @var string $authPassword
     */
    protected $authPassword;
    
    /**
     *
     * @var string $ipAddress
     */
    protected $ipAddress;
    
    /**
     * @var Cx\Core_Modules\MultiSite\Model\Entity\Website
     */
    protected $websites;
    
    /**
     * array $config
     */
    protected $config = array();
    
    /**
     * @var string $apiVersion
     */
    protected $apiVersion;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->id = 0;
        $this->label = '';
        $this->type = '';
        $this->hostname = '';
        $this->authUsername = '';
        $this->authPassword = '';
        $this->config = '';
        $this->apiVersion = '';
        $this->websites = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Set id
     * 
     * @param integer $id
     */
    public function setId($id) 
    {
        $this->id = $id;
    }
    
    /**
     * Get id
     * 
     * @return integer $id
     */
    public function getId() 
    {
        return $this->id;
    }
    
    /**
     * Set label
     * 
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }
    
    /**
     * Get label
     * 
     * @return string $label
     */
    public function getLabel() 
    {
        return $this->label;
    }
    
    /**
     * Set type
     * 
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
    
    /**
     * Get type
     * 
     * @return string $type
     */
    public function getType() 
    {
        return $this->type;
    }
    
    /**
     * Set hostname
     * 
     * @param string $hostname
     */
    public function setHostname($hostname)
    {
        $this->hostname = $hostname;
    }
    
    /**
     * Get hostname
     * 
     * @return string $hostname
     */
    public function getHostname() 
    {
        return $this->hostname;
    }
    
    /**
     * Set auth username
     * 
     * @param string $authUsername
     */
    public function setAuthUsername($authUsername)
    {
        $this->authUsername = $authUsername;
    }
    
    /**
     * Get auth username
     * 
     * @return string $authUsername
     */
    public function getAuthUsername() 
    {
        return $this->authUsername;
    }
    
    /**
     * Set auth password
     * 
     * @param string $authPassword
     */
    public function setAuthPassword($authPassword)
    {
        $this->authPassword = $authPassword;
    }
    
    /**
     * Get auth password
     * 
     * @return string $authPassword
     */
    public function getAuthPassword() 
    {
        return $this->authPassword;
    }
            
    /**
     * Get ipAddress
     * 
     * @return string $ipAddress
     */
    public function getIpAddress() 
    {
        return $this->ipAddress;
    }
    
    /**
     * Set ipAddress
     * 
     * @param string $ipAddress
     */
    public function setIpAddress($ipAddress)
    {
        $this->ipAddress = $ipAddress;
    }
    

    /**
     * Set config
     * 
     * @param array $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }
    
    /**
     * Get config
     * 
     * @return array $config
     */
    public function getConfig() 
    {
        return $this->config;
    }
    
    /**
     * Set apiVersion
     *
     * @param string $apiVersion
     */
    public function setApiVersion($apiVersion)
    {
        $this->apiVersion = $apiVersion;
    }

    /**
     * Get apiVersion
     *
     * @return string $apiVersion
     */
    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    /**
     *  Add the website
     * 
     * @param object \Cx\Core_Modules\MultiSite\Model\Entity\Website $website
     * 
     * @return null
     */
    public function addWebsite(\Cx\Core_Modules\MultiSite\Model\Entity\Website $website)
    {
        $this->websites[] = $website;
        $website->setMailServiceServer($this);
    }
    
    /**
     *  Remove the website
     * 
     * @param object \Cx\Core_Modules\MultiSite\Model\Entity\Website $website
     * 
     * @return null
     */
    public function removeWebsite(\Cx\Core_Modules\MultiSite\Model\Entity\Website $website)
    {
        $this->websites->removeElement($website);
    }
    
    /**
     * Get the websites
     * 
     * @return array $websites
     */
    public function getWebsites()
    {
        return $this->websites;
    }
    
    /**
     * Create a new subscription
     * 
     * @param object \Cx\Core_Modules\MultiSite\Model\Entity\Website $website
     * 
     * @return integer $accountId
     */
    public function createAccount(\Cx\Core_Modules\MultiSite\Model\Entity\Website $website)
    {
        $hostingController = \Cx\Core_Modules\MultiSite\Controller\ComponentController::getMailServerHostingController($this);
        $resp = \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::executeCommandOnWebsite('getMainDomain', array(), $website);
        $mainDomain = '';
        if ($resp->status == 'success' && $resp->data->status == 'success') {
            $mainDomain = $resp->data->mainDomain;
        }
        
        $additionalData = null;
        $response = JsonMultiSite::executeCommandOnWebsite('getModuleAdditionalData', array('moduleName' => 'MultiSite', 'additionalType' => 'Mail'), $website);
        if ($response->status == 'success' && $response->data->status == 'success') {
            $additionalData = $response->data->additionalData;
        }
        
        $mailServicePlan = !\FWValidator::isEmpty($additionalData) && isset($additionalData['plan']) ? $additionalData['plan'] : null;
        $domain = $website->getBaseDn()->getName();
        $planId = isset($this->config['planId'][$mailServicePlan]) ? $this->config['planId'][$mailServicePlan] : null;
        $role = isset($this->config['userRoleId']) ? $this->config['userRoleId'] : null;
        if (empty($domain) || empty($mainDomain) || empty($role) || empty($this->ipAddress)) {
            \DBG::log('MailServiceServer(createAccount) Failed: Insufficent argument supplied.');
            return false;
        }
        $subscriptionId = $hostingController->createSubscription($mainDomain, $this->ipAddress, 1, $customerId = null, $planId);
        if ($subscriptionId) {
            if ($hostingController instanceof \Cx\Core_Modules\MultiSite\Controller\PleskController) {
                $hostingController->setWebspaceId($subscriptionId);
            }
            $this->addWebsite($website);
            $hostingController->createUserAccount('info@'.$domain, \User::make_password(8, true), $role, $subscriptionId);
            $domains = $website->getDomainAliases();
            if (!\FWValidator::isEmpty($domains)) {
                foreach ($domains as $domain) {
                    if ($domain->getName() != $mainDomain) {
                        $hostingController->createDomainAlias($domain->getName());
                    }
                }
            }
            return $subscriptionId;
        }
        return false;
    }
    
    /**
     * Delete a subscription
     * 
     * @param integer $accountId
     * 
     * @return boolean
     */
    public function deleteAccount($accountId)
    {
        $hostingController = \Cx\Core_Modules\MultiSite\Controller\ComponentController::getMailServerHostingController($this);
        if ($hostingController->removeSubscription($accountId)) {
            return true;
        }
        return false;
    }
    
    /**
     * Enable the mail service
     * 
     * @param integer $accountId
     * 
     * @return boolean
     */
    public function enableService($accountId)
    {
        $hostingController = \Cx\Core_Modules\MultiSite\Controller\ComponentController::getMailServerHostingController($this);
        if ($hostingController->enableMailService($accountId)) {
            return true;
        }
            return false;
    }
        
    /**
     * Disable the mail service
     * 
     * @param integer $accountId
     * 
     * @return boolean
     */
    public function disableService($accountId)
    {
        $hostingController = \Cx\Core_Modules\MultiSite\Controller\ComponentController::getMailServerHostingController($this);
        if ($hostingController->disableMailService($accountId)) {
            return true;
        }
            return false;
    }
}    