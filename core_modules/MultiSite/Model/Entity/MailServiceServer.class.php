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
     *  Add the websites
     * 
     * @param object $websites
     */
    public function addWebsites(\Cx\Core_Modules\MultiSite\Model\Entity\Website $websites) {
        $this->websites[] = $websites;
        $websites->setMailServiceServer($this);
    }
    
    /**
     * Get the websites
     * 
     * @return Cx\Core_Modules\MultiSite\Model\Entity\Website $websites
     */
    public function getWebsites() {
        return $this->websites;
    }
    
    /**
     * Create a new subscription
     * 
     * @return integer $accountId
     */
    public function createAccount(\Cx\Core_Modules\MultiSite\Model\Entity\Website $website) {
        $hostingController = \Cx\Core_Modules\MultiSite\Controller\ComponentController::getMailServerHostingController($this);
        $domain = $website->getBaseDn();
        $planId = isset($this->config['planId']) ? $this->config['planId'] : null;
        $subscriptionId = $hostingController->createSubscription($domain, $planId, 1);
        if ($subscriptionId) {
            $this->addWebsites($website);
            $role = isset($this->config['userRoleId']) ? $this->config['userRoleId'] : null;
            $hostingController->createUserAccount('info@'.$domain, $role, \User::make_password(8, true));
        }
        return $subscriptionId;
    }
    
    /**
     * Delete a subscription
     * 
     * @param integer $accountId
     */
    public function deleteAccount($accountId) {
        $hostingController = \Cx\Core_Modules\MultiSite\Controller\ComponentController::getMailServerHostingController($this);
        if($hostingController->removeSubscription($accountId)) {
            return true;
        }
        return false;
    }
}    