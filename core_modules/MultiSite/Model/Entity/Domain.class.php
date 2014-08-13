<?php

/**
 * Class Domain
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      ss4u <ss4ugroup@gmail.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */

namespace Cx\Core_Modules\MultiSite\Model\Entity;

/**
 * Class Domain
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      ss4u <ss4ugroup@gmail.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class Domain extends \Cx\Core\Net\Model\Entity\Domain {

    const TYPE_FQDN = 'fqdn';
    const TYPE_BASE_DOMAIN = 'baseDn';
    const TYPE_EXTERNAL_DOMAIN = 'alias';

    /**
     * @var integer $id
     */
    private $websiteId;

    /**
     * @var Cx\Core_Modules\MultiSite\Model\Entity\Website $website
     */
    private $website;

    /**
     * @var integer $id
     */
    public $type;

    /**
     * @var integer $id
     */
    public $pleskId;
    
    /**
     * @var integer $coreNetDomainId
     */
    public $coreNetDomainId;

    /*
     * Constructor
     * */

    public function __construct($name) {
        parent::__construct($name);
        $this->name = $name;
        $this->type = self::TYPE_EXTERNAL_DOMAIN;
    }

    /**
     * Set websiteid
     *
     * @param integer $websiteId
     */
    public function setWebsiteId($websiteId) {
        $this->websiteId = $websiteId;
    }

    /**
     * Set website
     *
     * @param Cx\Core_Modules\MultiSite\Model\Entity\Website $website
     */
    public function setWebsite(Website $website) {
        $this->website = $website;
    }

    /**
     * Set website
     *
     * @param Cx\Core_Modules\MultiSite\Model\Entity\Website $website
     */
    public function getWebsite() {
        return $this->website;
    }

    /**
     * Get websiteid
     *
     * @return integer $websiteId
     */
    public function getWebsiteId() {
        return $this->websiteId;
    }

    /**
     * Set type
     *
     * @param string $type
     */
    public function setType($type) {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return string $type
     */
    public function getType() {
        return $this->type;
    }

    /**
     * Set pleskId
     *
     * @param integer pleskId
     */
    public function setPleskId($pleskId) {
        $this->pleskId = $pleskId;
    }

    /**
     * Get pleskId
     *
     * @return integer $pleskId
     */
    public function getPleskId() {
        return $this->pleskId;
    }
    
    /**
     * Set coreNetDomainId
     *
     * @param integer coreNetDomainId
     */
    public function setCoreNetDomainId($coreNetDomainId) {
        $this->coreNetDomainId = $coreNetDomainId;
    }

    /**
     * Get coreNetDomainId
     *
     * @return integer $coreNetDomainId
     */
    public function getCoreNetDomainId() {
        return $this->coreNetDomainId;
    }
    
}

