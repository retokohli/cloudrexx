<?php

/**
 * Class CronMailLog
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */

namespace Cx\Core_Modules\MultiSite\Model\Entity;

/**
 * Class CronMailLog
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class CronMailLog extends \Cx\Model\Base\EntityBase {

    /**
     * @var integer $id
     */
    protected $id;
    
    /**
     * @var integer $userId 
     */
    protected $userId;

    /**
     * @var integer $websiteId 
     */
    protected $websiteId;

    /**
     * @var integer $success; 
     */
    protected $success;

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
     * Set the id
     * 
     * @param integer $id
     */
    public function setId($id) {
        $this->id = $id;
    }
    
    /**
     * Get the userid
     * 
     * @return integer $userId
     */
    public function getUserId() {
        return $this->userId;
    }

    /**
     * Set the userid
     * 
     * @param integer $userId
     */
    public function setUserId($userId) {
        $this->userId = $userId;
    }

    /**
     * Get the websiteId
     * 
     * @return integer $websiteId
     */
    public function getWebsiteId() {
        return $this->websiteId;
    }

    /**
     * Set the websiteId
     * 
     * @param integer $websiteId
     */
    public function setWebsiteId($websiteId) {
        $this->websiteId = $websiteId;
    }

    /**
     * Get the success
     * 
     * @return integer $success
     */
    public function getSuccess() {
        return $this->success;
    }

    /**
     * Set the success
     * 
     * @param integer $success
     */
    public function setSuccess($success) {
        $this->success = $success;
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
