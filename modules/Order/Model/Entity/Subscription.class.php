<?php
/**
 * Class Subscription
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_order
 */

namespace Cx\Modules\Order\Model\Entity;

/**
 * Class Subscription
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_order
 */
class Subscription extends \Cx\Model\Base\EntityBase {
    /**
     * @var integer $id
     */
    protected $id;
    
    /**
     * @var Cx\Modules\Order\Model\Entity\Order
     */
    protected $order;
    
    /**
     * @var Cx\Modules\Pim\Model\Entity\Product
     */
    protected $product;

    protected $expirationDate = null;
    protected $productEntityId = null;
    protected $paymentAmount = null;
    protected $paymentState;
    protected $renewalUnit = null;
    protected $renewalQuantifier = null;
    protected $renewalDate = null;

    const PAYMENT_OPEN = 'open';
    const PAYMENT_PAID = 'paid';
    const PAYMENT_RENEWAL = 'renewal';

    /**
     * Constructor
     */
    public function __construct($product, $options) {
        if (!$product) {
            return;
        }
        $this->product = $product;
        $this->productEntityId = $product->initNewEntityForSale($options);
        $this->paymentAmount = $product->getPrice();
        $this->paymentState = self::PAYMENT_OPEN;
        if ($product->isExpirable()) {
            $this->expirationDate = $product->getExpirationDate();
        }
        if ($product->isRenewable()) {
            list($this->renewalUnit, $this->renewalQuantifier) = $product->getRenewalDefinition($options['renewalUnit'], $options['renewalQuantifier']);
            $this->renewalDate = $product->getRenewalDate($this->renewalUnit, $this->renewalQuantifier);
        }
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
     * Set the order
     * 
     * @param \Cx\Modules\Order\Model\Entity\Order $order
     */
    public function setOrder(Order $order) {
        $this->order = $order;
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
     * Set the product
     * 
     * @param integer $product
     */
    public function setProduct($product) {
        $this->product = $product;
    }
    
    /**
     * Get the product
     * 
     * @return integer $product
     */
    public function getProduct() {
        return $this->product;
    }

    public function getExpirationDate() {
        return $expirationDate;
    }

    public function setExpirationDate($expirationDate) {
        $this->expirationDate = $expirationDate;
    }

    public function getProductEntityId() {
        return $productEntityId;
    }

    public function setProductEntityId($productEntityId) {
        $this->productEntityId = $productEntityId;
    }

    public function getPaymentAmount() {
        return $paymentAmount;
    }

    public function setPaymentAmount($paymentAmount) {
        $this->paymentAmount = $paymentAmount;
    }

    public function getPaymentState() {
        return $paymentState;
    }

    public function setPaymentState($paymentState) {
        $this->paymentState = $paymentState;
    }

    public function getRenewalUnit() {
        return $renewalUnit;
    }

    public function setRenewalUnit($renewalUnit) {
        $this->renewalUnit = $renewalUnit;
    }

    public function getRenewalQuantifier() {
        return $renewalQuantifier;
    }

    public function setRenewalQuantifier($renewalQuantifier) {
        $this->renewalQuantifier = $renewalQuantifier;
    }

    public function getRenewalDate() {
        return $renewalDate;
    }

    public function setRenewalDate($renewalDate) {
        $this->renewalDate = $renewalDate;
    }
}
