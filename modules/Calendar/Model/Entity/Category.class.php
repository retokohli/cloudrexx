<?php

namespace Cx\Modules\Calendar\Model\Entity;

/**
 * Cx\Modules\Calendar\Model\Entity\Category
 */
class Category
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var integer $pos
     */
    private $pos;

    /**
     * @var integer $status
     */
    private $status;

    /**
     * @var Cx\Modules\Calendar\Model\Entity\CategoryName
     */
    private $categoryNames;

    /**
     * @var Cx\Modules\Calendar\Model\Entity\Event
     */
    private $events;

    public function __construct()
    {
        $this->categoryNames = new \Doctrine\Common\Collections\ArrayCollection();
    $this->events = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set pos
     *
     * @param integer $pos
     */
    public function setPos($pos)
    {
        $this->pos = $pos;
    }

    /**
     * Get pos
     *
     * @return integer $pos
     */
    public function getPos()
    {
        return $this->pos;
    }

    /**
     * Set status
     *
     * @param integer $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Get status
     *
     * @return integer $status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Add categoryNames
     *
     * @param Cx\Modules\Calendar\Model\Entity\CategoryName $categoryNames
     */
    public function addCategoryNames(\Cx\Modules\Calendar\Model\Entity\CategoryName $categoryNames)
    {
        $this->categoryNames[] = $categoryNames;
    }

    /**
     * Get categoryNames
     *
     * @return Doctrine\Common\Collections\Collection $categoryNames
     */
    public function getCategoryNames()
    {
        return $this->categoryNames;
    }

    /**
     * Add events
     *
     * @param Cx\Modules\Calendar\Model\Entity\Event $events
     */
    public function addEvents(\Cx\Modules\Calendar\Model\Entity\Event $events)
    {
        $this->events[] = $events;
    }

    /**
     * Get events
     *
     * @return Doctrine\Common\Collections\Collection $events
     */
    public function getEvents()
    {
        return $this->events;
    }
}