<?php

/**
 * Class AffiliateCredit
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */

namespace Cx\Core_Modules\MultiSite\Model\Entity;

/**
 * Class AffiliateCredit
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class AffiliateCredit extends \Cx\Model\Base\EntityBase {
    
    /**
     *
     * @var integer $id
     */
    protected $id;
    
    /**
     *
     * @var \Cx\Modules\Order\Model\Entity\Subscription
     */
    protected $subscription;

    /**
     *
     * @var boolean $credited
     */
    protected $credited;

    /**
     *
     * @var decimal $amount
     */
    protected $amount;

    /**
     *
     * @var \Cx\Modules\Crm\Model\Entity\Currency
     */
    protected $currency;

    /**
     *
     * @var \Cx\Core\User\Model\Entity\User
     */
    protected $referee;

    /**
     *
     * @var \Cx\Core_Modules\MultiSite\Model\Entity\AffiliatePayout
     */
    protected $payout;

    /**
     * Constructor
     */
    public function __construct() {}
    
    /**
     * Set the id
     * 
     * @param integer $id
     */
    public function setId($id) {
        $this->id = $id;
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
     * set the subscription
     * 
     * @param \Cx\Modules\Order\Model\Entity\Subscription $subscription
     */
    public function setSubscription(\Cx\Modules\Order\Model\Entity\Subscription $subscription) {
        $this->subscription = $subscription;
    }
    
    /**
     * Get the subscription
     * 
     * @return \Cx\Modules\Order\Model\Entity\Subscription
     */
    public function getSubscription() {
        return $this->subscription;
    }
    
    /**
     * Set the status of credit
     * 
     * @param boolean $credited
     */
    public function setCredited($credited) {
        $this->credited = $credited; 
    }
    
    /**
     * get the status of credit
     * 
     * @return boolean
     */
    public function getCredited() {
        return $this->credited;
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
     * Set the payout
     * 
     * @param \Cx\Core_Modules\MultiSite\Model\Entity\AffiliatePayout $payout
     */
    public function setPayout(AffiliatePayout $payout) {
        $this->payout = $payout;
    }
    
    /**
     * Get the payout
     * 
     * @return \Cx\Core_Modules\MultiSite\Model\Entity\AffiliatePayout
     */
    public function getpayout() {
        return $this->payout;
    }
}
