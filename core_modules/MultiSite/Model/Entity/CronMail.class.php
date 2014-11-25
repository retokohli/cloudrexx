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
     *
     * @var Cx\Core_Modules\MultiSite\Model\Entity\CronMailCriteria
     */
    protected $cronMailCriterias;
    
    /**
     *
     * @var Cx\Core_Modules\MultiSite\Model\Entity\CronMailLog
     */
    protected $cronMailLogs;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->cronMailCriterias = new \Doctrine\Common\Collections\ArrayCollection();
        $this->cronMailLogs = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Get the id
     * 
     * @return integer $id
     */
    public function getId() {
        return $this->id;
    }
    
    /**
     * Set the id
     * 
     * @return integer $id
     */
    public function setId($id) {
        $this->id = $id;
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
    
    /**
     *  Set the cron mail criterias
     * 
     * @param object $cronMailCriterias
     */
    public function setCronMailCriterias($cronMailCriterias) {
        $this->cronMailCriterias = $cronMailCriterias;
    }
    
    /**
     * Get the cron mail criterias
     * 
     * @return Cx\Core_Modules\MultiSite\Model\Entity\CronMailCriteria $cronMailCriterias
     */
    public function getCronMailCriterias() {
        return $this->cronMailCriterias;
    }
    
    /**
     * Add the CronMailCriteria
     * 
     * @param \Cx\Core_Modules\MultiSite\Model\Entity\CronMailCriteria $cronMailCriteria
     */
    public function addCronMailCriteria(CronMailCriteria $cronMailCriteria) {
        $cronMailCriteria->setCronMail($this);
        $this->cronMailCriterias[] = $cronMailCriteria;
    }
    
    /**
     *  Set the cron mail logs
     * 
     * @param object $cronMailLogs
     */
    public function setCronMailLogs($cronMailLogs) {
        $this->cronMailLogs = $cronMailLogs;
    }
    
    /**
     * Get the cron mail logs
     * 
     * @return Cx\Core_Modules\MultiSite\Model\Entity\CronMailLog $cronMailLogs
     */
    public function getCronMailLogs() {
        return $this->cronMailLogs;
    }
    
    /**
     * Add the CronMailLog
     * 
     * @param \Cx\Core_Modules\MultiSite\Model\Entity\CronMailLog $cronMailLog
     */
    public function addCronMailLog(CronMailLog $cronMailLog) {
        $cronMailLog->setCronMail($this);
        $this->cronMailLogs[] = $cronMailLog;
    }
}