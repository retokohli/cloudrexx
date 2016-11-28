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
 * Cx\Modules\Topics\Model\Entity\Category
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @copyright   Comvation AG
 * @link        http://www.comvation.com/
 * @package     comvation
 * @subpackage  module_topics
 */
class Category extends \Cx\Model\Base\EntityBase
implements \Gedmo\Translatable\Translatable
{
    /**
     * @var integer $id
     */
    private $id;
    /**
     * @var integer $parent_id
     */
    private $parent_id;
    /**
     * @var boolean $active
     */
    private $active;
    /**
     * @var string $name
     * @gedmo:Translatable
     */
    private $name;
    /**
     * @var string $slug
     */
    private $slug;
    /**
     * @var string $description
     * @gedmo:Translatable
     */
    private $description;
    /**
     * @var datetime $created
     */
    private $created;
    /**
     * @var datetime $updated
     */
    private $updated;
    /**
     * @var string $locale
     * @gedmo:Locale
     */
    private $locale;

    /**
     * Set locale
     * @param string $locale
     */
    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @var Cx\Modules\Topics\Model\Entity\Entry
     */
    private $entries;

    public function __construct()
    {
        $this->entries = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set id
     *
     * This may be required for the initial import.
     * @param integer $id
     */
    public function __setId($id)
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
     * Set parent_id
     *
     * @param integer $parentId
     */
    public function setParentId($parentId)
    {
        $this->parent_id = $parentId;
    }

    /**
     * Get parent_id
     *
     * @return integer $parentId
     */
    public function getParentId()
    {
        return $this->parent_id;
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
     * Add entry
     *
     * @param Cx\Modules\Topics\Model\Entity\Entry $entry
     */
    public function addEntry(\Cx\Modules\Topics\Model\Entity\Entry $entry)
    {
        $this->entries[] = $entry;
    }

    /**
     * Add entries
     *
     * @param Doctrine\Common\Collections\Collection $entries
     */
    public function setEntries(\Doctrine\Common\Collections\Collection $entries)
    {
        $this->entries = $entries;
    }

    /**
     * Get entries
     *
     * @return Doctrine\Common\Collections\Collection $entries
     */
    public function getEntries()
    {
        return $this->entries;
    }

}
