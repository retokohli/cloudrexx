<?php

namespace Cx\Core\User\Model\Entity;

/**
 * Cx\Core\User\Model\Entity\UserAttribute
 */
class UserAttribute extends \Cx\Model\Base\EntityBase {
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $type
     */
    private $type;

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
     * @var Cx\Core\User\Model\Entity\UserAttribute
     */
    private $parent;

    /**
     * @var Cx\Core\User\Model\Entity\UserAttributeName
     */
    private $userAttributeName;

    /**
     * @var Cx\Core\User\Model\Entity\UserAttribute
     */
    private $children;

    /**
     * @var Cx\Core_Modules\Access\Model\Entity\AccessId
     */
    private $accessId;

    /**
     * @var Cx\Core\User\Model\Entity\UserProfile
     */
    private $userProfile;

    public function __construct()
    {
        $this->parent = new \Doctrine\Common\Collections\ArrayCollection();
    $this->userAttributeName = new \Doctrine\Common\Collections\ArrayCollection();
    $this->userProfile = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
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
     * Set type
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return string $type
     */
    public function getType()
    {
        return $this->type;
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
     * Add parent
     *
     * @param Cx\Core\User\Model\Entity\UserAttribute $parent
     */
    public function addParent(\Cx\Core\User\Model\Entity\UserAttribute $parent)
    {
        $this->parent[] = $parent;
    }

    /**
     * Get parent
     *
     * @return Doctrine\Common\Collections\Collection $parent
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add userAttributeName
     *
     * @param Cx\Core\User\Model\Entity\UserAttributeName $userAttributeName
     */
    public function addUserAttributeName(\Cx\Core\User\Model\Entity\UserAttributeName $userAttributeName)
    {
        $this->userAttributeName[] = $userAttributeName;
    }

    /**
     * Get userAttributeName
     *
     * @return Doctrine\Common\Collections\Collection $userAttributeName
     */
    public function getUserAttributeName()
    {
        return $this->userAttributeName;
    }

    /**
     * Set children
     *
     * @param Cx\Core\User\Model\Entity\UserAttribute $children
     */
    public function setChildren(\Cx\Core\User\Model\Entity\UserAttribute $children)
    {
        $this->children = $children;
    }

    /**
     * Get children
     *
     * @return Cx\Core\User\Model\Entity\UserAttribute $children
     */
    public function getChildren()
    {
        return $this->children;
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

    /**
     * Add userProfile
     *
     * @param Cx\Core\User\Model\Entity\UserProfile $userProfile
     */
    public function addUserProfile(\Cx\Core\User\Model\Entity\UserProfile $userProfile)
    {
        $this->userProfile[] = $userProfile;
    }

    /**
     * Get userProfile
     *
     * @return Doctrine\Common\Collections\Collection $userProfile
     */
    public function getUserProfile()
    {
        return $this->userProfile;
    }
}