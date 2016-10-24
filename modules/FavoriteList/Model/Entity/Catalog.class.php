<?php

namespace Cx\Modules\FavoriteList\Model\Entity;

/**
 * Cx\Modules\FavoriteList\Model\Entity\Catalog
 */
class Catalog extends \Cx\Model\Base\EntityBase
{
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var string $session_id
     */
    protected $session_id;

    /**
     * @var string $name
     */
    protected $name;

    /**
     * @var date $date
     */
    protected $date;

    /**
     * @var Cx\Modules\FavoriteList\Model\Entity\Favorite
     */
    protected $favorites;

    public function __construct()
    {
        $this->favorites = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set id
     *
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get session_id
     *
     * @return string $sessionId
     */
    public function getSessionId()
    {
        return $this->session_id;
    }

    /**
     * Set session_id
     *
     * @param string $sessionId
     */
    public function setSessionId($sessionId)
    {
        $this->session_id = $sessionId;
    }

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get date
     *
     * @return date $date
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set date
     *
     * @param date $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * Add favorites
     *
     * @param Cx\Modules\FavoriteList\Model\Entity\Favorite $favorites
     */
    public function addFavorites(\Cx\Modules\FavoriteList\Model\Entity\Favorite $favorites)
    {
        $this->favorites[] = $favorites;
    }

    /**
     * Get favorites
     *
     * @return Doctrine\Common\Collections\Collection $favorites
     */
    public function getFavorites()
    {
        return $this->favorites;
    }

    public function __toString()
    {
        return $this->getName();
    }
}
