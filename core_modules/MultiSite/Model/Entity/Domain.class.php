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
     * @var integer $id
     */
    public $type;

    /**
     * @var integer $id
     */
    public $pleskId;

    /*
     * Constructor
     * */

    public function __construct($name) {
        parent::__construct($name);
        $this->name = $name;
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

}
