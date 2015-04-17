<?php

/**
 * Class AffiliatePayout
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */

namespace Cx\Core_Modules\MultiSite\Model\Entity;

/**
 * Class AffiliatePayout
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */

class AffiliatePayout extends \Cx\Model\Base\EntityBase {
    
    /**
     *
     * @var integer
     */
    protected $id;
    
    /**
     *
     * @var datetime
     */
    protected $date;
    
    /**
     *
     * @var decimal
     */
    protected $amount;
    
    /**
     *
     * @var Cx\Modules\Crm\Model\Entity\Currency
     */
    protected $currency;
    
    /**
     *
     * @var Cx\Core\User\Model\Entity\User
     */
    protected $referee;
    
    /**
     *
     * @var Cx\Core_Modules\MultiSite\Model\Entity\AffiliateCredit
     */
    protected $credits;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->credits = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Get the id
     * 
     * @return integer
     */
    public function getId() {
        return $this->id;
    }
    
    /**
     * Set the payout date
     * 
     * @param datetime $date
     */
    public function setDate($date) {
        $this->date = $date; 
    }
    
    /**
     * get the payout date
     * 
     * @return \Datetime
     */
    public function getDate() {
        return $this->date;
    }
    
    /**
     * Set the amount
     * 
     * @param decimal $amount
     */
    public function setAmount($amount) {
        $this->amount = $amount;
    }
    
    /**
     * Get the amount
     * 
     * @return decimal
     */
    public function getAmount() {
        return $this->amount;
    }

    /**
     * Set the currency
     * 
     * @param \Cx\Modules\Crm\Model\Entity\Currency $currency
     */
    public function setCurrency(\Cx\Modules\Crm\Model\Entity\Currency $currency) {
        $this->currency = $currency;
    }
    
    /**
     * Get the currency
     * 
     * @return \Cx\Modules\Crm\Model\Entity\Currency
     */
    public function getCurrency() {
        return $this->currency;
    }
    
    /**
     * Set the referee
     * 
     * @param \Cx\Core\User\Model\Entity\User $referee
     */
    public function setReferee(\Cx\Core\User\Model\Entity\User $referee) {
        $this->referee = $referee;
    }
    
    /**
     * Get the referee
     * 
     * @return \Cx\Core\User\Model\Entity\User
     */
    public function getReferee() {
        return $this->referee;
    }
    
    /**
     * Add the Affiliate Credits
     * 
     * @param \Cx\Core_Modules\MultiSite\Model\Entity\AffiliateCredit $credit
     */
    public function addCredit(AffiliateCredit $credit) {
        $this->credits[] = $credit;
        $credit->setPayout($this);
    }
    
    /**
     * Get the Affiliate Credits
     * 
     * @return \Cx\Core_Modules\MultiSite\Model\Entity\AffiliateCredit
     */
    public function getCredits() {
        return $this->credits;
    }
}
