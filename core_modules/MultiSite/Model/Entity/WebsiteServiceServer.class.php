<?php

namespace Cx\Core_Modules\MultiSite\Model\Entity;

/**
 * Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer
 */
class WebsiteServiceServer extends \Cx\Model\Base\EntityBase
{
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var string $hostname
     */
    protected $hostname;

    /**
     * @var string $label
     */
    protected $label;

    /**
     * @var string $secretKey
     */
    protected $secretKey;

    /**
     * @var string $installationId
     */
    protected $installationId;

    /**
     * @var Cx\Core_Modules\MultiSite\Model\Entity\Website
     */
    protected $websites;
    
    /**
     * @var string $httpAuthMethod
     */
    protected $httpAuthMethod;

    /**
     * @var string $httpAuthUsername
     */
    protected $httpAuthUsername;

    /**
     * @var string $httpAuthPassword
     */
    protected $httpAuthPassword;
    
    /**
     * @var Cx\Core_Modules\MultiSite\Model\Entity\WebsiteTemplate
     */
    protected $websiteTemplates;


    public function __construct()
    {
        $this->websites = new \Doctrine\Common\Collections\ArrayCollection();
        $this->websiteTemplates = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set id
     *
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
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
     * Set secretKey
     *
     * @param string $secretKey
     */
    public function setSecretKey($secretKey)
    {
        $this->secretKey = $secretKey;
    }

    /**
     * Get secretKey
     *
     * @return string $secretKey
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }

    /**
     * Set installationId
     *
     * @param string $installationId
     */
    public function setInstallationId($installationId)
    {
        $this->installationId = $installationId;
    }

    /**
     * Get installationId
     *
     * @return string $installationId
     */
    public function getInstallationId()
    {
        return $this->installationId;
    }

    /**
     * Add website
     *
     * @param Cx\Core_Modules\MultiSite\Model\Entity\Website $website
     */
    public function addWebsite(\Cx\Core_Modules\MultiSite\Model\Entity\Website $website)
    {
        $this->websites[] = $website;
    }

    /**
     * Get websites
     *
     * @return Doctrine\Common\Collections\Collection $websites
     */
    public function getWebsites()
    {
        return $this->websites;
    }
    
    /**
     * Add website template
     *
     * @param Cx\Core_Modules\MultiSite\Model\Entity\WebsiteTemplate $websiteTemplate
     */
    public function addWebsiteTemplate(WebsiteTemplate $WebsiteTemplate)
    {
        $this->websiteTemplates[] = $WebsiteTemplate;
    }
    
    /**
     * Get websiteTemplate
     * 
     * @return Doctrine\Common\Collections\Collection $websiteTemplate
     */
    public function getWebsiteTemplates()
    {
        return $this->websiteTemplates;
    }

    /**
     * Set httpAuthMethod
     *
     * @param string $httpAuthMethod
     */
    public function setHttpAuthMethod($httpAuthMethod)
    {
        $this->httpAuthMethod = $httpAuthMethod;
    }

    /**
     * Get httpAuthMethod
     *
     * @return string $httpAuthMethod
     */
    public function getHttpAuthMethod()
    {
        return $this->httpAuthMethod;
    }

    /**
     * Set httpAuthUsername
     *
     * @param string $httpAuthUsername
     */
    public function setHttpAuthUsername($httpAuthUsername)
    {
        $this->httpAuthUsername = $httpAuthUsername;
    }

    /**
     * Get httpAuthUsername
     *
     * @return string $httpAuthUsername
     */
    public function getHttpAuthUsername()
    {
        return $this->httpAuthUsername;
    }

    /**
     * Set httpAuthPassword
     *
     * @param string $httpAuthPassword
     */
    public function setHttpAuthPassword($httpAuthPassword)
    {
        $this->httpAuthPassword = $httpAuthPassword;
    }

    /**
     * Get httpAuthPassword
     *
     * @return string $httpAuthPassword
     */
    public function getHttpAuthPassword()
    {
        return $this->httpAuthPassword;
    }
}
