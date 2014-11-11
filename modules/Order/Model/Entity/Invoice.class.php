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

class InvoiceException extends \Exception {}

/**
 * Class Invoice
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_order
 */
class Invoice extends \Cx\Model\Base\EntityBase {
    /**
     *
     * @var integer $id
     */
    protected $id;
    
    /**
     * @var Cx\Modules\Order\Model\Entity\Order
     */
    protected $order;
    
    /**
     * @var Cx\Modules\Order\Model\Entity\Payment
     */
    protected $payments;
    
    /**
     * @var Cx\Modules\Order\Model\Entity\InvoiceItem
     */
    protected $invoiceItems;
    
    
    /**
     *
     * @var integer $paid
     */
    protected $paid;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->payments = new \Doctrine\Common\Collections\ArrayCollection();
        $this->invoiceItems = new \Doctrine\Common\Collections\ArrayCollection();
        $this->paid = false;
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
        
        if($payment->getAmount() == $this->getAmount()) {
            $this->paid = true;
        }
        
        if($payment->getAmount() > $this->getAmount()) {
            throw new InvoiceException('Amount of payment must not be greater than invoice amount');
        }
        
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
     * Get Sum of all the associated \Cx\Modules\Order\Model\Entity\InvoiceItem::$price
     * 
     * @return decimal the sum of all associated \Cx\Modules\Order\Model\Entity\InvoiceItem::$price
     */
    public function getAmount() {
        $totalInvoiceItemPrice = 0;
        foreach($this->invoiceItems as $invoiceItem) {
            $totalInvoiceItemPrice += $invoiceItem->getPrice();
        }
        return $totalInvoiceItemPrice;
    }
    
    /**
     * Get the paid
     * 
     * @return boolean $paid
     */
    public function getPaid() {
        return $this->paid;
    }
}
