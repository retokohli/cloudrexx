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
    public $id;

    /**
     * @var string $hostname
     */
    public $hostname;

    /**
     * @var string $label
     */
    public $label;

    /**
     * @var string $secretKey
     */
    public $secretKey;

    /**
     * @var string $installationId
     */
    public $installationId;

    /**
     * @var Cx\Core_Modules\MultiSite\Model\Entity\Website
     */
    private $websites;
    
    /**
     * @var integer $isDefault
     */
    public $isDefault;
    
    /**
     * @var string $httpAuthMethod
     */
    public $httpAuthMethod;

    /**
     * @var string $httpAuthUsername
     */
    public $httpAuthUsername;

    /**
     * @var string $httpAuthPassword
     */
    public $httpAuthPassword;
    
    public function __construct()
    {
        $this->websites = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set isDefault
     *
     * @param integer $isDefault
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = $isDefault;
        if ($isDefault){
            $websiteServiceServers = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer')->findAll();
            foreach ($websiteServiceServers as $website){
                echo $website->getId();
                if ($website->getId() == $this->getId()){
                    $this->isDefault = $isDefault;
                }else{
                    $website->isDefault=0;    
                }
            }
        }
    }

    /**
     * Get isDefault
     *
     * @return integer $isDefault
     */
    public function getIsDefault()
    {
        return $this->isDefault;
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
