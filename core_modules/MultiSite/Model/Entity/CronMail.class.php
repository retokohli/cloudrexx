<?php

/**
 * Class CronMail
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */

namespace Cx\Core_Modules\MultiSite\Model\Entity;

/**
 * Class CronMail
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class CronMail extends \Cx\Model\Base\EntityBase {
    /**
     * @var integer $id
     */
    protected $id;
    
    /**
     * @var boolean $active 
     */
    protected $active;
    
    /**
     * @var string $mailTemplateKey
     */
    protected $mailTemplateKey;
    
    /**
     * Constructor
     */
    public function __construct() {}
    
    /**
     * Get the id
     * 
     * @return integer $id
     */
    public function getId() {
        return $this->id;
    }
    
    /**
     * Set the active status
     * 
     * @param boolean $active
     */
    public function setActive($active) {
        $this->active = $active;
    }
    
    /**
     * Get the active status
     * 
     * @return boolean $active
     */
    public function getActive() {
        return $this->active;
    }
    
    /**
     * Set the mail template key
     * 
     * @param string $mailTemplateKey
     */
    public function setMailTemplateKey($mailTemplateKey) {
        $this->mailTemplateKey = $mailTemplateKey;
    }
    
    /**
     * Get the mail template key
     * 
     * @return integer $mailTemplateKey
     */
    public function getMailTemplateKey() {
        return $this->mailTemplateKey;
    }
}