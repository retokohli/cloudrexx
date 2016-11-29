<?php
/**
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @copyright   Comvation AG
 * @link        http://www.comvation.com/
 * @package     comvation
 * @subpackage  module_topics
 */

namespace Cx\Modules\Topics\Model\Entity;

/**
 * Cx\Modules\Topics\Model\Entity\Entry
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @copyright   Comvation AG
 * @link        http://www.comvation.com/
 * @package     comvation
 * @subpackage  module_topics
 */
class Entry extends \Cx\Model\Base\EntityBase
implements \Gedmo\Translatable\Translatable
{
    /**
     * @var integer $id
     */
    protected $id;
    /**
     * @var boolean $active
     */
    protected $active;
    /**
     * @var string $name
     */
    protected $name;
    /**
     * @var string $slug
     */
    protected $slug;
    /**
     * @var string $href
     */
    protected $href;
    /**
     * @var string $description
     */
    protected $description;
    /**
     * @var datetime $created
     */
    protected $created;
    /**
     * @var datetime $updated
     */
    protected $updated;
    /**
     * @var string $locale
     */
    protected $locale;

    /**
     * Set locale
     * @param string $locale
     */
    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @var Cx\Modules\Topics\Model\Entity\Category
     */
    protected $categories;

    /**
     * Set up the Entry with an empty Collection of associated Categories
     */
    public function __construct()
    {
        $this->categories = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set id
     *
     * This is only required for the initial import.
     * @param integer $id
     */
    public function setIdImport($id)
    {
        $this->id = $id;
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
     * Set active
     *
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * Get active
     *
     * @return boolean $active
     */
    public function getActive()
    {
        return $this->active;
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
     * Set slug
     *
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * Get slug
     *
     * @return string $slug
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set href
     *
     * @param string $href
     */
    public function setHref($href)
    {
        $this->href = $href;
    }

    /**
     * Get href
     *
     * @return string $href
     */
    public function getHref()
    {
        return $this->href;
    }

    /**
     * Set description
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return string $description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set created
     *
     * @param datetime $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * Get created
     *
     * @return datetime $created
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param datetime $updated
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
    }

    /**
     * Get updated
     *
     * @return datetime $updated
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Add category
     *
     * @param Cx\Modules\Topics\Model\Entity\Category $category
     */
    public function addCategory(\Cx\Modules\Topics\Model\Entity\Category $category)
    {
        $category->addEntry($this);
        $this->categories[] = $category;
    }

    /**
     * Set categories
     *
     * @param Doctrine\Common\Collections\Collection $categories
     */
    public function setCategories(\Doctrine\Common\Collections\Collection $categories)
    {
        foreach ($categories as $category) {
            $category->addEntry($this);
        }
        $this->categories = $categories;
    }

    /**
     * Get categories
     *
     * @return Doctrine\Common\Collections\Collection $categories
     */
    public function getCategories()
    {
        return $this->categories;
    }

}
