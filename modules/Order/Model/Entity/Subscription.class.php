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
     * @var Cx\Modules\Order\Model\Entity\Order
     */
    private $order;
    
    /**
     * @var Cx\Modules\Order\Model\Entity\Product 
     */
    private $product;
    
    /**
     * @var integer $orderId
     */
    private $orderId;
    
    /**
     * @var integer $productId
     */
    private $productId;
    
    /**
     * Constructor
     */
    public function __construct() {}
    
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
}
