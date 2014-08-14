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
class Subscription {
    /**
     *
     * @var integer $id
     */
    private $id;
    
    /**
     * @var Cx\Modules\Order\Model\Entity\Order
     */
    protected $order;
    
    /**
     * @var Cx\Modules\Order\Model\Entity\Product 
     */
    protected $product;
    
    /**
     * @var integer $orderId
     */
    private $orderId;
    
    /**
     * @var integer $productId
     */
    private $productId;
    
    /**
     * @var string $paymentType
     */
    public $paymentType;
    
    /**
     * Payment type free
     */
    const PAYMENT_TYPE_FREE = 'free';
    
    /**
     * Payment type one time
     */
    const PAYMENT_TYPE_ONE_TIME = 'oneTime';
    
    /**
     * Payment type recurrent
     */
    const PAYMENT_TYPE_RECURRENT = 'recurrent';

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
     * @param type $product
     */
    public function setProduct($product) {
        $this->product = $product;
    }
    
    /**
     * Get the product
     * 
     * @return type
     */
    public function getProduct() {
        return $this->product;
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
    
    /**
     * Set the productId
     * 
     * @param integer $productId
     */
    public function setProductId($productId) {
        $this->productId = $productId;
    }
    
    /**
     * Get the productId
     * 
     * @return integer $productId
     */
    public function getProductId() {
        return $this->productId;
    }
    
    public function getPaymentType() {
        return $this->paymentType;
    }
    
    public function setPaymentType($paymentType) {
        $this->paymentType = $paymentType;
    }
}
