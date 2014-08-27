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
    protected $productEntity = null;
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
        // Important:
        // A subscription must always have a valid $product.
        // The following exception is for the sole purpose of making this class
        // compatible with the \Cx\Core\Html\Controller\ViewGenerator library
        // for autogenerating user-interfaces.
        if (!$product) {
            return;
        }
        $this->product = $product;
        $this->setProductEntity($product->getNewEntityForSale($options));
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
        return $this->expirationDate;
    }

    public function setExpirationDate($expirationDate) {
        $this->expirationDate = $expirationDate;
    }

    public function getProductEntityId() {
        return $this->productEntityId;
    }

    public function setProductEntityId($productEntityId) {
        $this->productEntityId = $productEntityId;
    }

    public function getProductEntity() {
        if (!$this->productEntity && $this->productEntityId) {
            $this->productEntity = $this->product->getEntityById($this->productEntityId);
        }
        return $this->productEntity;
    }

    public function setProductEntity($productEntity) {
        $this->productEntity = $productEntity;
        $entityIdKey = \Env::get('em')->getClassMetadata(get_class($productEntity))->getSingleIdentifierFieldName(); 
        $this->productEntityId = $productEntity->{'get'.ucfirst($entityIdKey)}();
    }

    public function getPaymentAmount() {
        return $this->paymentAmount;
    }

    public function setPaymentAmount($paymentAmount) {
        $this->paymentAmount = $paymentAmount;
    }

    public function getPaymentState() {
        return $this->paymentState;
    }

    public function setPaymentState($paymentState) {
        $this->paymentState = $paymentState;
    }

    public function getRenewalUnit() {
        return $this->renewalUnit;
    }

    public function setRenewalUnit($renewalUnit) {
        $this->renewalUnit = $renewalUnit;
    }

    public function getRenewalQuantifier() {
        return $this->renewalQuantifier;
    }

    public function setRenewalQuantifier($renewalQuantifier) {
        $this->renewalQuantifier = $renewalQuantifier;
    }

    public function getRenewalDate() {
        return $this->renewalDate;
    }

    public function setRenewalDate($renewalDate) {
        $this->renewalDate = $renewalDate;
    }

    public function payComplete() {
        if ($this->getProduct()->isRenewable()) {
            // update renewal period and date
            list($this->renewalUnit, $this->renewalQuantifier) = $this->getProduct()->getRenewalDefinition($this->renewalUnit, $this->renewalQuantifier);
            $renewalDate = $this->getProduct()->getRenewalDate($this->renewalUnit, $this->renewalQuantifier);
            $this->setRenewalDate($renewalDate);
            $this->setPaymentState(self::PAYMENT_RENEWAL);
        } else {
            $this->setPaymentState(self::PAYMENT_PAID);
        }

        \Env::get('cx')->getEvents()->triggerEvent('model/payComplete', array(new \Doctrine\ORM\Event\LifecycleEventArgs($this, \Env::get('em'))));
    }
}
