<?php

namespace Cx\Core\User\Model\Entity;

/**
 * Cx\Core\User\Model\Entity\ProfileTitle
 */
class ProfileTitle extends \Cx\Model\Base\EntityBase {
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $title
     */
    private $title;

    /**
     * @var integer $order_id
     */
    private $order_id;

    /**
     * @var Cx\Core\User\Model\Entity\UserProfile
     */
    private $userProfile;

    public function __construct()
    {
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
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get title
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
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