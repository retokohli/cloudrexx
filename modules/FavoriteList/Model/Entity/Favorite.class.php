<?php

namespace Cx\Modules\FavoriteList\Model\Entity;

/**
 * Cx\Modules\FavoriteList\Model\Entity\Favorite
 */
class Favorite extends \Cx\Model\Base\EntityBase
{
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var string $title
     */
    protected $title;

    /**
     * @var string $link
     */
    protected $link;

    /**
     * @var text $description
     */
    protected $description;

    /**
     * @var text $message
     */
    protected $message;

    /**
     * @var float $price
     */
    protected $price;

    /**
     * @var string $image1
     */
    protected $image1;

    /**
     * @var string $image2
     */
    protected $image2;

    /**
     * @var string $image3
     */
    protected $image3;

    /**
     * @var Cx\Modules\FavoriteList\Model\Entity\Catalog
     */
    protected $catalog;


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
     * Get title
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
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
     * Get link
     *
     * @return string $link
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Set link
     *
     * @param string $link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    /**
     * Get description
     *
     * @return text $description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description
     *
     * @param text $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get message
     *
     * @return text $message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set message
     *
     * @param text $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * Get price
     *
     * @return float $price
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set price
     *
     * @return float $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * Get image1
     *
     * @return string $image1
     */
    public function getImage1()
    {
        return $this->image1;
    }

    /**
     * Set image1
     *
     * @param string $image1
     */
    public function setImage1($image1)
    {
        $this->image1 = $image1;
    }

    /**
     * Get image2
     *
     * @return string $image2
     */
    public function getImage2()
    {
        return $this->image2;
    }

    /**
     * Set image2
     *
     * @param string $image2
     */
    public function setImage2($image2)
    {
        $this->image2 = $image2;
    }

    /**
     * Get image3
     *
     * @return string $image3
     */
    public function getImage3()
    {
        return $this->image3;
    }

    /**
     * Set image3
     *
     * @param string $image3
     */
    public function setImage3($image3)
    {
        $this->image3 = $image3;
    }

    /**
     * Get catalog
     *
     * @return Cx\Modules\FavoriteList\Model\Entity\Catalog $catalog
     */
    public function getCatalog()
    {
        return $this->catalog;
    }

    /**
     * Set catalog
     *
     * @param Cx\Modules\FavoriteList\Model\Entity\Catalog $catalog
     */
    public function setCatalog(\Cx\Modules\FavoriteList\Model\Entity\Catalog $catalog)
    {
        $this->catalog = $catalog;
    }
}
