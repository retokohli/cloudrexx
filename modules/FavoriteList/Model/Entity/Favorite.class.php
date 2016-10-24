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
     * @var integer $list_id
     */
    protected $list_id;

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
     * @var text $info
     */
    protected $info;

    /**
     * @var string $image_1
     */
    protected $image_1;

    /**
     * @var string $image_2
     */
    protected $image_2;

    /**
     * @var string $image_3
     */
    protected $image_3;

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
     * Get list_id
     *
     * @return integer $listId
     */
    public function getListId()
    {
        return $this->list_id;
    }

    /**
     * Set list_id
     *
     * @param integer $listId
     */
    public function setListId($listId)
    {
        $this->list_id = $listId;
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
     * Get info
     *
     * @return text $info
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * Set info
     *
     * @param text $info
     */
    public function setInfo($info)
    {
        $this->info = $info;
    }

    /**
     * Get image_1
     *
     * @return string $image1
     */
    public function getImage1()
    {
        return $this->image_1;
    }

    /**
     * Set image_1
     *
     * @param string $image1
     */
    public function setImage1($image1)
    {
        $this->image_1 = $image1;
    }

    /**
     * Get image_2
     *
     * @return string $image2
     */
    public function getImage2()
    {
        return $this->image_2;
    }

    /**
     * Set image_2
     *
     * @param string $image2
     */
    public function setImage2($image2)
    {
        $this->image_2 = $image2;
    }

    /**
     * Get image_3
     *
     * @return string $image3
     */
    public function getImage3()
    {
        return $this->image_3;
    }

    /**
     * Set image_3
     *
     * @param string $image3
     */
    public function setImage3($image3)
    {
        $this->image_3 = $image3;
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
