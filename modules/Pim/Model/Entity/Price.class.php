<?php

namespace Cx\Modules\Pim\Model\Entity;

/**
 * Cx\Modules\Pim\Model\Entity\Price
 */
class Price extends \Cx\Model\Base\EntityBase {
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var decimal $amount
     */
    protected $amount;

    /**
     * @var \Cx\Modules\Crm\Model\Entity\Currency
     */
    protected $currency;

    /**
     * @var \Cx\Modules\Pim\Model\Entity\Product
     */
    protected $product;


    /**
     * Get id
     *
     * @return integer $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set amount
     *
     * @param decimal $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * Get amount
     *
     * @return decimal $amount
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set currency
     *
     * @param \Cx\Modules\Crm\Model\Entity\Currency $currency
     */
    public function setCurrency(\Cx\Modules\Crm\Model\Entity\Currency $currency)
    {
        $this->currency = $currency;
    }

    /**
     * Get currency
     *
     * @return \Cx\Modules\Crm\Model\Entity\Currency $currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Set product
     *
     * @param \Cx\Modules\Pim\Model\Entity\Product $product
     */
    public function setProduct(\Cx\Modules\Pim\Model\Entity\Product $product)
    {
        $this->product = $product;
    }

    /**
     * Get product
     *
     * @return \Cx\Modules\Pim\Model\Entity\Product $product
     */
    public function getProduct()
    {
        return $this->product;
    }
}