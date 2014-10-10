<?php

namespace Cx\Core\User\Model\Entity;

/**
 * Cx\Core\User\Model\Entity\CoreAttribute
 */
class CoreAttribute extends \Cx\Model\Base\EntityBase {
    /**
     * @var string $id
     */
    private $id;

    /**
     * @var string $mandatory
     */
    private $mandatory;

    /**
     * @var string $sort_type
     */
    private $sort_type;

    /**
     * @var integer $order_id
     */
    private $order_id;

    /**
     * @var string $access_special
     */
    private $access_special;

    /**
     * @var Cx\Core_Modules\Access\Model\Entity\AccessId
     */
    private $accessId;


    /**
     * Set id
     *
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get id
     *
     * @return string $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set mandatory
     *
     * @param string $mandatory
     */
    public function setMandatory($mandatory)
    {
        $this->mandatory = $mandatory;
    }

    /**
     * Get mandatory
     *
     * @return string $mandatory
     */
    public function getMandatory()
    {
        return $this->mandatory;
    }

    /**
     * Set sort_type
     *
     * @param string $sortType
     */
    public function setSortType($sortType)
    {
        $this->sort_type = $sortType;
    }

    /**
     * Get sort_type
     *
     * @return string $sortType
     */
    public function getSortType()
    {
        return $this->sort_type;
    }

    /**
     * Set order_id
     *
     * @param integer $orderId
     */
    public function setOrderId($orderId)
    {
        $this->order_id = $orderId;
    }

    /**
     * Get order_id
     *
     * @return integer $orderId
     */
    public function getOrderId()
    {
        return $this->order_id;
    }

    /**
     * Set access_special
     *
     * @param string $accessSpecial
     */
    public function setAccessSpecial($accessSpecial)
    {
        $this->access_special = $accessSpecial;
    }

    /**
     * Get access_special
     *
     * @return string $accessSpecial
     */
    public function getAccessSpecial()
    {
        return $this->access_special;
    }

    /**
     * Set accessId
     *
     * @param Cx\Core_Modules\Access\Model\Entity\AccessId $accessId
     */
    public function setAccessId(\Cx\Core_Modules\Access\Model\Entity\AccessId $accessId)
    {
        $this->accessId = $accessId;
    }

    /**
     * Get accessId
     *
     * @return Cx\Core_Modules\Access\Model\Entity\AccessId $accessId
     */
    public function getAccessId()
    {
        return $this->accessId;
    }
}