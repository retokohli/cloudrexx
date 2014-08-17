<?php

/**
 * Class Payment
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
class Payment extends \Cx\Model\Base\EntityBase {
    /**
     *
     * @var integer $id
     */
    protected $id;
    
    /**
     * @var \Cx\Modules\Order\Model\Entity\Invoice $invoice 
     */
    protected $invoice;
    
    /**
     *
     * @var DateTime $date
     */
    protected $date;
    
    /**
     *
     * @var double $amount
     */
    protected $amount;
    
    /**
     *
     * @var string $transactionReference
     */
    protected $transactionReference;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->date = new \DateTime();
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
     * Get the transaction reference
     * @return  string  The transaction reference of the payment
     */
    public function getTransactionReference() {
        return $this->transactionReference;
    }

    /**
     * Set the transaction reference
     * @param   string  $transactionReference The transaction reference to set
     */
    public function setTransactionReference($transactionReference) {
        $this->transactionReference = $transactionReference;
    }

    /**
     * Get the payment amount
     * @return double
     */
    public function getAmount() {
        return $this->amount;
    }

    /**
     * Set the payment amount
     * @param   double $amount The amount the payment to set to
     */
    public function setAmount($amount) {
        $this->amount = $amount;
    }

    /**
     * Get the payment date of the payment
     * @return  DateTime    The date the payment was made
     */
    public function getDate() {
        return $this->date;
    }

    /**
     * Set the date of the payment
     * @param   DateTime    The date the payment was made
     */
    public function setDate($date) {
        $this->date = $date;
    }

    /**
     * Get the invoice
     * 
     * @return \Cx\Modules\Order\Model\Entity\Invoice $invoice
     */
    public function getInvoice() {
        return $this->invoice;
    }
    
    /**
     * Set the invoice
     * 
     * @param \Cx\Modules\Order\Model\Entity\Invoice $invoice
     */
    public function setInvoice(Invoice $invoice) {
        $this->invoice = $invoice;
    }
}
