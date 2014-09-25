<?php

namespace Cx\Core_Modules\MultiSite\Model\Entity;

/**
 * Cx\Core_Modules\MultiSite\Model\Entity\WebsiteTemplate
 */
class WebsiteTemplate extends \Cx\Model\Base\EntityBase
{
    /**
     * @var integer $id
     */
    protected $id;
    
    /**
     * codeBase
     * 
     * @var string $codeBase 
     */
    protected $codeBase;
    
    /**
     * websiteServiceServer
     * 
     * @var Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer
     */
    protected $websiteServiceServer;
    
    /**
     * licensedComponents
     * 
     * @var string $licensedComponents
     */
    protected $licensedComponents;
    
    /**
     * licenseMessage
     * 
     * @var string  $licenseMessage
     */
    protected $licenseMessage;
    
    /**
     * Constructor
     */
    public function __construct() {}
    
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
     * Set codeBase
     * 
     * @param string $codeBase
     */
    public function setCodeBase($codeBase)
    {
        $this->codeBase = $codeBase;
    }
    
    /**
     * Get codeBase
     * 
     * @return string codeBase
     */
    public function getCodeBase() 
    {
        return $this->codeBase;
    }
    
    /**
     * Set websiteServiceServer
     * 
     * @param \Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer $websiteServiceServer
     */
    public function setWebsiteServiceServer(WebsiteServiceServer $websiteServiceServer) 
    {
        $this->websiteServiceServer = $websiteServiceServer;
    }
    
    /**
     * Get websiteServiceServer
     * 
     * @return Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer $websiteServiceServer
     */
    public function getWebsiteServiceServer() 
    {
        return $this->websiteServiceServer;
    }
    
    /**
     * Set licensedComponents
     * 
     * @param string $licensedComponents
     */
    public function setLicensedComponents($licensedComponents) 
    {
        $this->licensedComponents = $licensedComponents;
    }
    
    /**
     * Get licensedComponents
     * 
     * @return string $licensedComponents
     */
    public function getLicensedComponents() 
    {
        return $this->licensedComponents;
    }
    
    /**
     * Set licenseMessage
     * 
     * @param string $licenseMessage
     */
    public function setLicenseMessage($licenseMessage) 
    {
        $this->licenseMessage = $licenseMessage;
    }
    
    /**
     * Get licenseMessage
     * 
     * @return string $licenseMessage
     */
    public function getLicenseMessage() 
    {
        return $this->licenseMessage;
    }

}
