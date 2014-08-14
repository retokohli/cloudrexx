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
class Payment {
    /**
     *
     * @var integer $id
     */
    private $id;
    
    /**
     * @var \Cx\Modules\Order\Model\Entity\Invoice $invoice 
     */
    protected $invoice;
    
    /**
     *
     * @var dateTime $date
     */
    protected $date;
    
    /**
     *
     * @var decimal $amount
     */
    protected $amount;
    
    /**
     *
     * @var string $transactionReference
     */
    protected $transactionReference;
    
    /**
     *
     * @var integer $invoiceId
     */
    private $invoiceId;
    
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
     * Set the id
     * @param integer $id
     */
    public function setId($id) {
        $this->id = $id;
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
    
    /**
     * Set the invoiceId
     * 
     * @param integer $invoiceId
     */
    public function setInvoiceId($invoiceId) {
        $this->invoiceId = $invoiceId;
    }
    
    /**
     * Get the invoiceId
     * 
     * @return integer $invoiceId
     */
    public function getInvoiceId() {
        return $this->invoiceId;
    }
}
