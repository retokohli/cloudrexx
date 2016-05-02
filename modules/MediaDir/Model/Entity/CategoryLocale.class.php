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
 * CategoryLocale entity
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      CLOUDREXX Development Team <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_mediadir
 */
namespace Cx\Modules\MediaDir\Model\Entity;

/**
 * CategoryLocale entity
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      CLOUDREXX Development Team <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_mediadir
 */
class CategoryLocale extends \Cx\Model\Base\EntityBase
{
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var integer $lang_id
     */
    protected $lang_id;

    /**
     * @var string $category_name
     */
    protected $category_name;

    /**
     * @var text $category_description
     */
    protected $category_description;

    /**
     * @var Cx\Modules\MediaDir\Model\Entity\Category
     */
    protected $category;

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
     * Set lang_id
     *
     * @param integer $langId
     */
    public function setLangId($langId)
    {
        $this->lang_id = $langId;
    }

    /**
     * Get lang_id
     *
     * @return integer $langId
     */
    public function getLangId()
    {
        return $this->lang_id;
    }

    /**
     * Set category_name
     *
     * @param string $categoryName
     */
    public function setCategoryName($categoryName)
    {
        $this->category_name = $categoryName;
    }

    /**
     * Get category_name
     *
     * @return string $categoryName
     */
    public function getCategoryName()
    {
        return $this->category_name;
    }

    /**
     * Set category_description
     *
     * @param text $categoryDescription
     */
    public function setCategoryDescription($categoryDescription)
    {
        $this->category_description = $categoryDescription;
    }

    /**
     * Get category_description
     *
     * @return text $categoryDescription
     */
    public function getCategoryDescription()
    {
        return $this->category_description;
    }

    /**
     * Set category
     *
     * @param Cx\Modules\MediaDir\Model\Entity\Category $category
     */
    public function setCategory(\Cx\Modules\MediaDir\Model\Entity\Category $category)
    {
        $this->category = $category;
    }

    /**
     * Get category
     *
     * @return Cx\Modules\MediaDir\Model\Entity\Category $category
     */
    public function getCategory()
    {
        return $this->category;
    }

}