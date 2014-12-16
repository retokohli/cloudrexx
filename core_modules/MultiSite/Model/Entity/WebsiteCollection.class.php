<?php

/**
 * Class WebsiteCollection
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */

namespace Cx\Core_Modules\MultiSite\Model\Entity;

/**
 * Class WebsiteCollection
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */

class WebsiteCollection extends \Cx\Model\Base\EntityBase {
    
    /**
     *
     * @var integer $id 
     */
    protected $id;
    /**
     * @var integer $quota
     */
    protected $quota;
    
    /**
     *
     * @var Cx\Core_Modules\MultiSite\Model\Entity\Website
     */
    protected $websites;
    
    /**
     *
     * @var Cx\Core_Modules\MultiSite\Model\Entity\WebsiteTemplate
     */
    protected $websiteTemplate;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->websites = new \Doctrine\Common\Collections\ArrayCollection();        
    }
    
    /**
     * Get the id
     * 
     * @return integer id
     */
    public function getId() {
        return $this->id;
    }
    
    /**
     * Set the id
     * 
     * @param integer $id
     */
    public function setId($id) {
        $this->id = $id;
    }
    
    /**
     * Get the quota value
     * 
     * @return integer value of the quota
     */
    public function getQuota() {
        return $this->quota;
    }
    
    /**
     * Set the quota value
     * 
     * @param integer $quota
     */
    public function setQuota($quota) {
        $this->quota = $quota;
    }

    /**
     * Get the websites
     * 
     * @return $websites
     */
    public function getWebsites()
    {
        return $this->websites;
    }
    
    /**
     * Set the website
     *  
     * @param \Cx\Core_Modules\MultiSite\Model\Entity\Website $website
     */
    public function setWebsite(Website $website)
    {
        $this->websites = $website;
    }
    
    /**
     * Add the website
     * 
     * @param \Cx\Core_Modules\MultiSite\Model\Entity\Website $website
     */
    public function addWebsite(Website $website) {
        $this->websites[] = $website;
    }
    
    /**
     * Get the website Template
     * 
     * @return array $websiteTemplate
     */
    public function getWebsiteTemplate() {
        return $this->websiteTemplate;
    }
    
    /**
     * Set the website Template
     * 
     * @param \Cx\Core_Modules\MultiSite\Model\Entity\WebsiteTemplate $websiteTemplate
     */
    public function setWebsiteTemplate(WebsiteTemplate $websiteTemplate) {
        $this->websiteTemplate = $websiteTemplate;
    }
}