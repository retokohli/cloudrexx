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
 * Level entity
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      CLOUDREXX Development Team <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_mediadir
 */
namespace Cx\Modules\MediaDir\Model\Entity;

/**
 * Level entity
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      CLOUDREXX Development Team <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_mediadir
 */
class Level extends \Cx\Model\Base\EntityBase
{
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var integer $order
     */
    protected $order;

    /**
     * @var boolean $show_sublevels
     */
    protected $show_sublevels;

    /**
     * @var boolean $show_categories
     */
    protected $show_categories;

    /**
     * @var boolean $show_entries
     */
    protected $show_entries;

    /**
     * @var text $picture
     */
    protected $picture;

    /**
     * @var boolean $active
     */
    protected $active;

    /**
     * @var integer $lft
     */
    protected $lft;

    /**
     * @var integer $rgt
     */
    protected $rgt;

    /**
     * @var integer $lvl
     */
    protected $lvl;

    /**
     * @var Cx\Modules\MediaDir\Model\Entity\LevelLocale
     */
    protected $locale;

    /**
     * @var integer Parent Level id
     */
    protected $parent;

    public function __construct()
    {
        $this->locale = array();
    }

    /**
     * Set id
     *
     * @return integer $id
     */
    public function setId($id)
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
     * Set show_sublevels
     *
     * @param boolean $showSublevels
     */
    public function setShowSublevels($showSublevels)
    {
        $this->show_sublevels = $showSublevels;
    }

    /**
     * Get show_sublevels
     *
     * @return boolean $showSublevels
     */
    public function getShowSublevels()
    {
        return $this->show_sublevels;
    }

    /**
     * Set show_categories
     *
     * @param boolean $showCategories
     */
    public function setShowCategories($showCategories)
    {
        $this->show_categories = $showCategories;
    }

    /**
     * Get show_categories
     *
     * @return boolean $showCategories
     */
    public function getShowCategories()
    {
        return $this->show_categories;
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
     * Add locale
     *
     * @param Cx\Modules\MediaDir\Model\Entity\LevelLocale $locale
     */
    public function addLocale(\Cx\Modules\MediaDir\Model\Entity\LevelLocale $locale)
    {
        $this->locale[] = $locale;
    }

    /**
     * Get locale
     *
     * @return array $locale
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
     * @param integer $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * Get parent
     *
     * @return integer $parent
     */
    public function getParent()
    {
        return $this->parent;
    }
}
