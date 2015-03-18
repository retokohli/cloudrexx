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
    const TYPE_MAIL_DOMAIN = 'mail';
    const TYPE_WEBMAIL_DOMAIN = 'webmail';

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var integer
     */
    protected $componentId;

    /**
     * @var string
     */
    protected $componentType;

    /**
     * @var Cx\Core_Modules\MultiSite\Model\Entity\Website $website
     */
    protected $website;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var integer
     */
    protected $pleskId;
    
    /**
     * @var integer
     */
    protected $coreNetDomainId;

    /*
     * Constructor
     * */

    public function __construct($name) {
        parent::__construct($name);
        $this->type = self::TYPE_EXTERNAL_DOMAIN;
    }

    /**
     * Set componentId
     *
     * @param integer $componentId
     */
    public function setComponentId($componentId) {
        $this->componentId = $componentId;
    }

    /**
     * Set componentType
     *
     * @param string $componentType
     */
    public function setComponentType($componentType) {
        $this->componentType = $componentType;
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
     * Get website
     *
     * @param Cx\Core_Modules\MultiSite\Model\Entity\Website $website
     */
    public function getWebsite() {
        if (!$this->website) {
            $query = \Env::get('em')->createQuery('SELECT website FROM Cx\Core_Modules\MultiSite\Model\Entity\Website website JOIN website.domains domain WHERE domain.id = ?1');
            $query->setParameter(1, $this->id);
            $query->setMaxResults(1);
            $websites = $query->getResult();
            if (isset($websites[0])) {
                $this->website = $websites[0];
            }
        }

        return $this->website;
    }

    /**
     * Get componentId
     *
     * @return integer $componentId
     */
    public function getComponentId() {
        return $this->componentId;
    }

    /**
     * Get componentType
     *
     * @return integer $componentType
     */
    public function getComponentType() {
        return $this->componentType;
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

