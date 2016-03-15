<?php

/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2015
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Cloudrexx" is a registered trademark of Cloudrexx AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * Category entity
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      CLOUDREXX Development Team <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_mediadir
 */
namespace Cx\Modules\MediaDir\Model\Entity;

/**
 * Category entity
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      CLOUDREXX Development Team <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_mediadir
 */
class Category extends \Cx\Model\Base\EntityBase implements \RecursiveIterator, \Countable
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var integer $order
     */
    private $order;

    /**
     * @var boolean $show_subcategories
     */
    private $show_subcategories;

    /**
     * @var boolean $show_entries
     */
    private $show_entries;

    /**
     * @var text $picture
     */
    private $picture;

    /**
     * @var boolean $active
     */
    private $active;

    /**
     * @var integer $lft
     */
    private $lft;

    /**
     * @var integer $rgt
     */
    private $rgt;

    /**
     * @var integer $lvl
     */
    private $lvl;

    /**
     * @var Cx\Modules\MediaDir\Model\Entity\Category
     */
    private $children;

    /**
     * @var Cx\Modules\MediaDir\Model\Entity\CategoryLocale
     */
    private $locale;

    /**
     * @var Cx\Modules\MediaDir\Model\Entity\Category
     */
    private $parent;
    
    private $position = 0;

    public function __construct()
    {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
        $this->locale = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set order
     *
     * @param integer $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * Get order
     *
     * @return integer $order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set show_subcategories
     *
     * @param boolean $showSubcategories
     */
    public function setShowSubcategories($showSubcategories)
    {
        $this->show_subcategories = $showSubcategories;
    }

    /**
     * Get show_subcategories
     *
     * @return boolean $showSubcategories
     */
    public function getShowSubcategories()
    {
        return $this->show_subcategories;
    }

    /**
     * Set show_entries
     *
     * @param boolean $showEntries
     */
    public function setShowEntries($showEntries)
    {
        $this->show_entries = $showEntries;
    }

    /**
     * Get show_entries
     *
     * @return boolean $showEntries
     */
    public function getShowEntries()
    {
        return $this->show_entries;
    }

    /**
     * Set picture
     *
     * @param text $picture
     */
    public function setPicture($picture)
    {
        $this->picture = $picture;
    }

    /**
     * Get picture
     *
     * @return text $picture
     */
    public function getPicture()
    {
        return $this->picture;
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
     * Set lft
     *
     * @param integer $lft
     */
    public function setLft($lft)
    {
        $this->lft = $lft;
    }

    /**
     * Get lft
     *
     * @return integer $lft
     */
    public function getLft()
    {
        return $this->lft;
    }

    /**
     * Set rgt
     *
     * @param integer $rgt
     */
    public function setRgt($rgt)
    {
        $this->rgt = $rgt;
    }

    /**
     * Get rgt
     *
     * @return integer $rgt
     */
    public function getRgt()
    {
        return $this->rgt;
    }

    /**
     * Set lvl
     *
     * @param integer $lvl
     */
    public function setLvl($lvl)
    {
        $this->lvl = $lvl;
    }

    /**
     * Get lvl
     *
     * @return integer $lvl
     */
    public function getLvl()
    {
        return $this->lvl;
    }

    /**
     * Add children
     *
     * @param Cx\Modules\MediaDir\Model\Entity\Category $children
     */
    public function addChildren(\Cx\Modules\MediaDir\Model\Entity\Category $children)
    {
        $this->children[] = $children;
    }

    /**
     * Get children
     *
     * @return Doctrine\Common\Collections\Collection $children
     */
    public function getChildren()
    {
        return $this->valid() ? $this->children[$this->position] : null;
    }

    /**
     * Add locale
     *
     * @param Cx\Modules\MediaDir\Model\Entity\CategoryLocale $locale
     */
    public function addLocale(\Cx\Modules\MediaDir\Model\Entity\CategoryLocale $locale)
    {
        $this->locale[] = $locale;
    }

    /**
     * Get locale
     *
     * @return Doctrine\Common\Collections\Collection $locale
     */
    public function getLocale()
    {
        return $this->locale;
    }
    
    /**
     * Get locale by lang id
     *
     * @return CategoryLocale $locale
     */
    public function getLocaleByLang($lang = null)
    {
        $locale = null;
        
        foreach ($this->locale as $value) {
            if ($value->getLangId() == $lang) {
                $locale = $value;
                break;
            }
        }
        
        return $locale;
    }

    /**
     * Set parent
     *
     * @param Cx\Modules\MediaDir\Model\Entity\Category $parent
     */
    public function setParent(\Cx\Modules\MediaDir\Model\Entity\Category $parent)
    {
        $this->parent = $parent;
    }

    /**
     * Get parent
     *
     * @return Cx\Modules\MediaDir\Model\Entity\Category $parent
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Get all children
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getAllChildren()
    {
        return $this->children;
    }
    
    /**
     * Check node has children
     *
     * @return boolean
     */
    public function hasChildren()
    {
        if (!$this->valid()) {
            return false;
        }
        
        return count($this->children[$this->position]) > 0;
    }

    /**
     * Get current children in the iterator
     *
     * @return Category
     */
    public function current()
    {
        return $this->children[$this->position];
    }

    /**
     * Move to next position
     */
    public function next()
    {
        $this->position++;
    }

    /**
     * Get current position
     *
     * @return integer
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Verify the current children is valid
     *
     * @return boolean
     */
    public function valid()
    {
        return isset($this->children[$this->position]);
    }

    /**
     * Reset the iterator position
     */
    public function rewind()
    {
        $this->position = 0;
    }
    
    /**
     * Get the number of sub categories
     *
     * @return integer The number of elements in iterator.
     */
    public function count()
    {
        return iterator_count(new \RecursiveIteratorIterator($this, \RecursiveIteratorIterator::SELF_FIRST));
    }
}
