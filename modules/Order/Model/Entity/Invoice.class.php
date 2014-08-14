<?php

/**
 * Class Invoice
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_order
 */

namespace Cx\Modules\Order\Model\Entity;

/**
 * Class Invoice
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_order
 */
class Invoice {
    /**
     *
     * @var integer $id
     */
    private $id;
    
    /**
     * @var Cx\Modules\Order\Model\Entity\Order
     */
    public $order;
    
    /**
     * @var Cx\Modules\Order\Model\Entity\Payment
     */
    public $payments;
    
    /**
     * @var integer $orderId
     */
    private $orderId;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->payments = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Get the order
     * 
     * @return \Cx\Modules\Order\Model\Entity\Order $order
     */
    public function getOrder() {
        return $this->order;
    }
    
    /**
     * Set the order
     * 
     * @param \Cx\Modules\Order\Model\Entity\Order $order
     */
    public function setOrder(Order $order) {
        $this->order = $order;
    }
    
    /**
     * Add the payment
     * 
     * @param \Cx\Modules\Order\Model\Entity\Payment $payment
     */
    public function addPayment(Payment $payment) {
        $payment->setInvoice($this);
        $this->setPayments($payment);
    }
    
    /**
     * 
     * @return \Cx\Modules\Order\Model\Entity\Payment $payments
     */
    public function getPayments() {
        return $this->payments;
    }
    
    /**
     * Set the payment
     * 
     * @param object $payments
     */
    public function setPayments($payments) {
        $this->payments[] = $payments;
    }
    
    /**
     * Set the orderId
     * 
     * @param integer $orderId
     */
    public function setOrderId($orderId) {
        $this->orderId = $orderId;
    }
    
    /**
     * Get the orderId
     * 
     * @return integer $orderId
     */
    public function getOrderId() {
        return $this->orderId;
    }
}
