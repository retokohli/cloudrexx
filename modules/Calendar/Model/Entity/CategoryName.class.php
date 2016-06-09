<?php

namespace Cx\Modules\Calendar\Model\Entity;

/**
 * Cx\Modules\Calendar\Model\Entity\CategoryName
 */
class CategoryName
{
    /**
     * @var integer $catId
     */
    private $catId;

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var integer $langId
     */
    private $langId;

    /**
     * @var Cx\Modules\Calendar\Model\Entity\Category
     */
    private $category;


    /**
     * Set catId
     *
     * @param integer $catId
     */
    public function setCatId($catId)
    {
        $this->catId = $catId;
    }

    /**
     * Get catId
     *
     * @return integer $catId
     */
    public function getCatId()
    {
        return $this->catId;
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
     * Get name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set langId
     *
     * @param integer $langId
     */
    public function setLangId($langId)
    {
        $this->langId = $langId;
    }

    /**
     * Get langId
     *
     * @return integer $langId
     */
    public function getLangId()
    {
        return $this->langId;
    }

    /**
     * Set category
     *
     * @param Cx\Modules\Calendar\Model\Entity\Category $category
     */
    public function setCategory(\Cx\Modules\Calendar\Model\Entity\Category $category)
    {
        $this->category = $category;
    }

    /**
     * Get category
     *
     * @return Cx\Modules\Calendar\Model\Entity\Category $category
     */
    public function getCategory()
    {
        return $this->category;
    }
}