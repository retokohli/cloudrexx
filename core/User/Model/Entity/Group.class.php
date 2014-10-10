<?php

namespace Cx\Core\User\Model\Entity;

/**
 * Cx\Core\User\Model\Entity\Group
 */
class Group extends \Cx\Model\Base\EntityBase {
    /**
     * @var integer $group_id
     */
    private $group_id;

    /**
     * @var string $group_name
     */
    private $group_name;

    /**
     * @var string $group_description
     */
    private $group_description;

    /**
     * @var integer $is_active
     */
    private $is_active;

    /**
     * @var string $type
     */
    private $type;

    /**
     * @var string $homepage
     */
    private $homepage;

    /**
     * @var Cx\Core\User\Model\Entity\User
     */
    private $user;

    /**
     * @var Cx\Core_Modules\Access\Model\Entity\AccessId
     */
    private $accessId2;

    /**
     * @var Cx\Core_Modules\Access\Model\Entity\AccessId
     */
    private $accessId;

    public function __construct()
    {
        $this->user = new \Doctrine\Common\Collections\ArrayCollection();
    $this->accessId2 = new \Doctrine\Common\Collections\ArrayCollection();
    $this->accessId = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Get group_id
     *
     * @return integer $groupId
     */
    public function getGroupId()
    {
        return $this->group_id;
    }

    /**
     * Set group_name
     *
     * @param string $groupName
     */
    public function setGroupName($groupName)
    {
        $this->group_name = $groupName;
    }

    /**
     * Get group_name
     *
     * @return string $groupName
     */
    public function getGroupName()
    {
        return $this->group_name;
    }

    /**
     * Set group_description
     *
     * @param string $groupDescription
     */
    public function setGroupDescription($groupDescription)
    {
        $this->group_description = $groupDescription;
    }

    /**
     * Get group_description
     *
     * @return string $groupDescription
     */
    public function getGroupDescription()
    {
        return $this->group_description;
    }

    /**
     * Set is_active
     *
     * @param integer $isActive
     */
    public function setIsActive($isActive)
    {
        $this->is_active = $isActive;
    }

    /**
     * Get is_active
     *
     * @return integer $isActive
     */
    public function getIsActive()
    {
        return $this->is_active;
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
     * Set homepage
     *
     * @param string $homepage
     */
    public function setHomepage($homepage)
    {
        $this->homepage = $homepage;
    }

    /**
     * Get homepage
     *
     * @return string $homepage
     */
    public function getHomepage()
    {
        return $this->homepage;
    }

    /**
     * Add user
     *
     * @param Cx\Core\User\Model\Entity\User $user
     */
    public function addUser(\Cx\Core\User\Model\Entity\User $user)
    {
        $this->user[] = $user;
    }

    /**
     * Get user
     *
     * @return Doctrine\Common\Collections\Collection $user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add accessId2
     *
     * @param Cx\Core_Modules\Access\Model\Entity\AccessId $accessId2
     */
    public function addAccessId2(\Cx\Core_Modules\Access\Model\Entity\AccessId $accessId2)
    {
        $this->accessId2[] = $accessId2;
    }

    /**
     * Get accessId2
     *
     * @return Doctrine\Common\Collections\Collection $accessId2
     */
    public function getAccessId2()
    {
        return $this->accessId2;
    }

    /**
     * Add accessId
     *
     * @param Cx\Core_Modules\Access\Model\Entity\AccessId $accessId
     */
    public function addAccessId(\Cx\Core_Modules\Access\Model\Entity\AccessId $accessId)
    {
        $this->accessId[] = $accessId;
    }

    /**
     * Get accessId
     *
     * @return Doctrine\Common\Collections\Collection $accessId
     */
    public function getAccessId()
    {
        return $this->accessId;
    }
}