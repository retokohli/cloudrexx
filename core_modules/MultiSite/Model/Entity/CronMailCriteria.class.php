<?php

/**
 * Class CronMailCriteria
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */

namespace Cx\Core_Modules\MultiSite\Model\Entity;

/**
 * Class CronMailCriteria
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class CronMailCriteria extends \Cx\Model\Base\EntityBase {

    /**
     * @var integer $id
     */
    protected $id;
    
    /**
     * @var string $attribute 
     */
    protected $attribute;

    /**
     * @var string $criteria
     */
    protected $criteria;

    /**
     *
     * @var Cx\Core_Modules\MultiSite\Model\Entity\CronMail $cronMail
     */
    protected $cronMail;
    
    /**
     * Constructor
     */
    public function __construct() {}

    /**
     * Get the id
     * 
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Get the attribute
     * 
     * @return string $attribute
     */
    public function getAttribute() {
        return $this->attribute;
    }

    /**
     * Set the attribute
     * 
     * @param string $attribute
     */
    public function setAttribute($attribute) {
        $this->attribute = $attribute;
    }

    /**
     * Get the criteria
     * 
     * @return string $criteria
     */
    public function getCriteria() {
        return $this->criteria;
    }

    /**
     * set the criteria
     * 
     * @param string $criteria
     */
    public function setCriteria($criteria) {
        $this->criteria = $criteria;
    }

    /**
     * Set the cron mail
     * 
     * @param \Cx\Core_Modules\MultiSite\Model\Entity\CronMail $cronMail
     */
    public function setCronMail(CronMail $cronMail) {
        $this->cronMail = $cronMail;
    }
    
    /**
     * Get the cron mail
     * 
     * @return \Cx\Core_Modules\MultiSite\Model\Entity\CronMail $cronMail
     */
    public function getCronMail() {
        return $this->cronMail;
    }
}
