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
     * @var Cx\Core_Modules\MultiSite\Model\Entity\Website
     */
    protected $websites;
    
    /**
     * array $config
     */
    protected $config = array();
    
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
        $domain = $website->getBaseDn();
        $planId = isset($this->config['planId']) ? $this->config['planId'] : null;
        $subscriptionId = $hostingController->createSubscription($domain, 1, $customerId = null, $planId);
        if ($subscriptionId) {
            $this->addWebsite($website);
            $role = isset($this->config['userRoleId']) ? $this->config['userRoleId'] : null;
            $hostingController->createUserAccount('info@'.$domain, \User::make_password(8, true), $role, $subscriptionId);
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