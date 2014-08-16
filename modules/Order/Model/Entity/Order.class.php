<?php

/**
 * Class Order
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_order
 */

namespace Cx\Modules\Order\Model\Entity;

/**
 * Class Order
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_order
 */
class Order extends \Cx\Model\Base\EntityBase {
    /**
     *
     * @var integer $id
     */
    protected $id;
    
    /**
     * @var integer $contactId
     */
    protected $contactId;

    /**
     * @var array $subscriptions
     */
    protected $subscriptions;

    /**
     * @var array $invoices
     */
    protected $invoices;

    /**
     * Constructor
     */
    public function __construct() {
        $this->subscriptions = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @param integer $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * Set the contactId
     *
     * @param integer $contactId
     */
    public function setContactId($contactId) {
        $this->contactId = $contactId;
    }
    
    /**
     * get the contactId
     *
     * @return integer $contactId
     */
    public function getContactId() {
        return $this->contactId;
    }

    /**
     * Add the subscription
     * 
     * @param \Cx\Modules\Order\Model\Entity\Subscription $subscription
     */
    public function addSubscription(Subscription $subscription) {
        $subscription->setOrder($this);
        $this->setSubscriptions($subscription); 
    }
    
    /**
     * Get the subscription
     * 
     * @return \Cx\Modules\Order\Model\Entity\subscription $subscriptions
     */
    public function getSubscriptions() {
        return $this->subscriptions;
    }
    
    /**
     * Set the subscription
     * 
     * @param object $subscriptions
     */
    public function setSubscriptions($subscriptions) {
        $this->subscriptions[] = $subscriptions;
    }
    
    /**
     * Get the Invoices
     * 
     * @return array
     */
    public function getInvoices() {
        return $this->invoices;
    }
    
    /**
     * Set the invoices
     * 
     * @param array $invoices
     */
    public function setInvoices($invoices) {
        $this->invoices = $invoices;
    }
}
