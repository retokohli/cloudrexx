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
     * @var integer $orderId
     */
    private $orderId;

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
     * Set orderId
     *
     * @param integer $orderId
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * Get orderId
     *
     * @return integer $orderId
     */
    public function getOrderId()
    {
        return $this->orderId;
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