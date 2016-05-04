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
 * Media  Directory Category Class
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Cloudrexx Development Team <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_mediadir
 * @todo        Edit PHP DocBlocks!
 */
namespace Cx\Modules\MediaDir\Controller;

/**
 * Media Directory Category Class
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      CLOUDREXX Development Team <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_mediadir
 */
class MediaDirectoryCategory extends MediaDirectoryLibrary
{
    /**
     * Count number of entries for each categories
     *
     * @var boolean
     */
    protected $countEntries = false;

    /**
     * Instance of NestedSet
     *
     * @var \DB_NestedSet
     */
    public $categoryNestedSet;

    /**
     * Parent category id(Root of all categories)
     *
     * @var integer
     */
    public $nestedSetRootId;

    /**
     * Constructor
     */
    function __construct($name = '')
    {
        global $objDatabase;

        parent::__construct('.', $name);
        parent::getSettings();
        parent::getFrontendLanguages();

        if ($this->arrSettings['settingsCountEntries'] == 1 || $this->cx->getMode() == \Cx\Core\Core\Controller\Cx::MODE_BACKEND) {
            $this->countEntries = true;
        }

        //nestedSet setup
        $arrTableStructure = array(
            'id'                 => 'id',
            'parent_id'          => 'rootid',
            'lft'                => 'l',
            'rgt'                => 'r',
            'category_order'     => 'norder',
            'lvl'                => 'level',
            'show_subcategories' => 'show_subcategories',
            'show_entries'       => 'show_entries',
            'picture'            => 'picture',
            'active'             => 'active',
        );
        $objNs = new \DB_NestedSet($arrTableStructure);
        $this->categoryNestedSet = $objNs->factory('ADODB', $objDatabase, $arrTableStructure);
        $this->categoryNestedSet->setAttr(array(
            'node_table'    => DBPREFIX.'module_mediadir_categories',
            'lock_table'    => DBPREFIX.'module_mediadir_categories_locks',
        ));

        if (count($rootNodes = $this->categoryNestedSet->getRootNodes()) > 0) {
            foreach ($rootNodes as $rootNode) {
                $this->nestedSetRootId = $rootNode->id;
                break;
            }
        } else {
            // create first entry of sequence table for NestedSet
            $objResult = $objDatabase->SelectLimit("SELECT `id` FROM `".DBPREFIX."module_mediadir_categories_id`", 1);
            if ($objResult->RecordCount() == 0) {
                $objDatabase->Execute("INSERT INTO `".DBPREFIX."module_mediadir_categories_id` VALUES (0)");
            }
            $this->nestedSetRootId = $this->categoryNestedSet->createRootNode(array(), false, false);
        }
    }

    /**
     * Parse the category details
     *
     * @param \Cx\Core\Html\Sigma                           $objTpl             Instance of template object
     * @param \Cx\Modules\MediaDir\Model\Entity\Category    $category           Instance of category to parse
     * @param string                                        $strCategoryIcon    String category icon
     * @param string                                        $strCategoryClass   Class name for the category
     * @param string                                        $blockName          Parse block name
     */
    public function parseCategoryDetail(
        \Cx\Core\Html\Sigma $objTpl,
        \Cx\Modules\MediaDir\Model\Entity\Category $category,
        $strCategoryIcon = null,
        $strCategoryClass = 'inactive',
        $level = 1,
        $blockName = 'CategoriesList'
    ) {
        $intSpacerSize = ($level - 1) * 21;
        $spacer        = '<img src="../core/Core/View/Media/icons/pixel.gif" border="0" width="'.$intSpacerSize.'" height="11" alt="" />';

        $categoryDesc = $this->getCategoryDescription($category);
        //parse variables
        $objTpl->setVariable(array(
            $this->moduleLangVar.'_CATEGORY_ID'                   => $category->getId(),
            $this->moduleLangVar.'_CATEGORY_ORDER'                => $category->getOrder(),
            $this->moduleLangVar.'_CATEGORY_NAME'                 => contrexx_raw2xhtml($this->getCategoryName($category)),
            $this->moduleLangVar.'_CATEGORY_DESCRIPTION'          => $categoryDesc,
            $this->moduleLangVar.'_CATEGORY_DESCRIPTION_ESCAPED'  => strip_tags($categoryDesc),
            $this->moduleLangVar.'_CATEGORY_PICTURE'              => $category->getPicture(),
            $this->moduleLangVar.'_CATEGORY_NUM_ENTRIES'          => $this->countEntries($category->getId()),
            $this->moduleLangVar.'_CATEGORY_ICON'                 => $spacer.$strCategoryIcon,
            $this->moduleLangVar.'_CATEGORY_VISIBLE_STATE_ACTION' => $category->getActive() == 0 ? 1 : 0,
            $this->moduleLangVar.'_CATEGORY_VISIBLE_STATE_IMG'    => $category->getActive() == 0 ? 'off' : 'on',
            $this->moduleLangVar.'_CATEGORY_LEVEL_NUMBER'         => $level,
            $this->moduleLangVar.'_CATEGORY_ACTIVE_STATUS'        => $strCategoryClass,
        ));

        $objTpl->parse($this->moduleNameLC . $blockName);
    }

    /**
     * Get name of category by output language
     *
     * @param \Cx\Modules\MediaDir\Model\Entity\Category $category
     *
     * @return string
     */
    public function getCategoryName(\Cx\Modules\MediaDir\Model\Entity\Category $category)
    {
        global $_LANGID;

        $locale       = $category->getLocaleByLang($_LANGID);
        return $locale ? $locale->getCategoryName() : '';
    }

    /**
     * Get category description by output language
     *
     * @param \Cx\Modules\MediaDir\Model\Entity\Category $category
     *
     * @return string
     */
    public function getCategoryDescription(\Cx\Modules\MediaDir\Model\Entity\Category $category)
    {
        global $_LANGID;

        $locale       = $category->getLocaleByLang($_LANGID);
        return $locale ? $locale->getCategoryDescription() : '';
    }

    /**
     * Parse category tree
     *
     * @param \Cx\Core\Html\Sigma                           $objTpl                 Instance of template
     * @param \Cx\Modules\MediaDir\Model\Entity\Category    $category               Root category to parse
     * @param array                                         $expandedCategoryIds    Expanded category array
     * @param boolean                                       $expandAll              True to expand all categories
     * @param boolean                                       $checkShowSubCategory   True to check the show subcategory option
     */
    public function parseCategoryTree(
        \Cx\Core\Html\Sigma $objTpl,
        \Cx\Modules\MediaDir\Model\Entity\Category $category,
        $expandedCategoryIds = array(),
        $expandAll = false,
        $level = 1,
        $checkShowSubCategory = false
    ) {
        $subCategories = $this->getSubCategoriesByCategory($category);
        foreach ($subCategories as $subcategory) {
            $hasChildren = !\FWValidator::isEmpty($this->getSubCategoriesByCategory($subcategory));
            $isExpanded  = (!$checkShowSubCategory || $subcategory->getShowSubcategories())
                           && ($expandAll || in_array($subcategory->getId(), $expandedCategoryIds));

            $strCategoryClass = 'inactive';
            if ($hasChildren) {
                if ($isExpanded) {
                    $expCatId = $subcategory->getParent();
                    $iconFile = 'minuslink.gif';
                    $strCategoryClass = 'active';
                } else {
                    $expCatId = $subcategory->getId();
                    $iconFile = 'pluslink.gif';
                }
                $strCategoryIcon = '<a href="index.php?cmd='.$this->moduleName.'&amp;exp_cat='. $expCatId .'"><img src="../core/Core/View/Media/icons/'. $iconFile .'" border="0" alt="{'.$this->moduleLangVar.'_CATEGORY_NAME}" title="{'.$this->moduleLangVar.'_CATEGORY_NAME}" /></a>';
            } else {
                $strCategoryIcon = '<img src="../core/Core/View/Media/icons/pixel.gif" border="0" width="11" height="11" alt="{'.$this->moduleLangVar.'_CATEGORY_NAME}" title="{'.$this->moduleLangVar.'_CATEGORY_NAME}" />';
            }
            $this->parseCategoryDetail($objTpl, $subcategory, $strCategoryIcon, $strCategoryClass, $level);

            if ($isExpanded) {
                $this->parseCategoryTree($objTpl, $subcategory, $expandedCategoryIds, $expandAll, $level + 1);
            }
        }
    }

    /**
     * Get categories related to the entry
     *
     * @param integer $entryId Entry id
     *
     * @return array Array of category id's
     */
    public function getSelectedCategoriesByEntryId($entryId)
    {
        global $objDatabase;

        $entryId = contrexx_input2int($entryId);
        if (!$entryId) {
            return array();
        }
        $categories = $objDatabase->Execute('
            SELECT
                `category_id`
            FROM
                `'. DBPREFIX .'module_'. $this->moduleTablePrefix .'_rel_entry_categories`
            WHERE
                `entry_id` = "'. $entryId .'"
        ');
        if (!$categories) {
            return array();
        }
        $selectedCategories = array();
        while (!$categories->EOF) {
            $selectedCategories[] = $categories->fields['category_id'];
            $categories->MoveNext();
        }
        return $selectedCategories;
    }

    /**
     * List the categories by the view type
     *
     * @param mixed     $objTpl             Instance of template or null
     * @param integer   $intView            View type
     * @param integer   $categoryId         Category id
     * @param integer   $parentCategoryId   Parent category id
     * @param integer   $intEntryId         Entry id
     * @param array     $arrExistingBlocks  Existing blocks to parse
     *
     * @return string
     */
    function listCategories(
        $objTpl,
        $intView,
        $categoryId = null,
        $parentCategoryId = null,
        $intEntryId = null,
        $arrExistingBlocks = null,
        $startLevel = 1
    ) {

        switch ($intView) {
            case 1:
                //Backend View
                $expandCategory      = isset($_GET['exp_cat']) ? $_GET['exp_cat'] : '';
                $expandedCategoryIds = $this->getExpandedCategoryIds($expandCategory);

                $rootCategory = $this->getCategoryById($this->nestedSetRootId);
                $this->parseCategoryTree(
                    $objTpl,
                    $rootCategory,
                    $expandedCategoryIds,
                    $expandCategory == 'all'
                );
                break;
            case 2:
                //Frontend View
                $intNumBlocks = count($arrExistingBlocks);
                $strIndexHeader = '';

                $i = $this->arrSettings['settingsCategoryOrder'] == 2
                        ? $intNumBlocks - 1
                        : 0;

                //set first index header
                if ($this->arrSettings['settingsCategoryOrder'] == 2) {
                    $strFirstIndexHeader = null;
                }

                $category = $parentCategoryId ? $this->getCategoryById($parentCategoryId) : false;
                if (!$category) {
                    $category = $this->getCategoryById($this->nestedSetRootId);
                }

                $intNumCategories = $this->categoryNestedSet->getSubBranch($category->getId(), true, true, array('where' => array('active' => 1)));

                if ($intNumCategories % $intNumBlocks != 0) {
                    $intNumCategories = $intNumCategories + ($intNumCategories % $intNumBlocks);
                }

                $intNumPerRow = intval($intNumCategories / $intNumBlocks);
                $x = 0;

                $strLevelId    = isset($_GET['lid']) ? "&amp;lid=".intval($_GET['lid']) : '';

                $subCategories = $this->getSubCategoriesByCategory($category);
                foreach ($subCategories as $subCategory) {

                    $strIndexHeaderTag = null;
                    if ($this->arrSettings['settingsCategoryOrder'] == 2) {
                        $strIndexHeader = strtoupper(substr($this->getCategoryName($subCategory), 0, 1));
                        if ($strFirstIndexHeader != $strIndexHeader) {
                            $i = $i < $intNumBlocks - 1 ? $i + 1 : 0;
                            $strIndexHeaderTag = '<span class="' . $this->moduleNameLC . 'LevelCategoryIndexHeader">' . $strIndexHeader . '</span><br />';
                        }
                    } else {
                        if ($x == $intNumPerRow) {
                            ++$i;

                            if ($i == $intNumBlocks) {
                                $i = 0;
                            }

                            $x = 1;
                        } else {
                            $x++;
                        }
                    }

                    //get ids
                    $strCategoryCmd = isset($_GET['cmd'])
                                        ? '&amp;cmd='.$_GET['cmd']
                                        : '';

                    $childrenString = $this->createCategoryTree(
                        $subCategory,
                        $strCategoryCmd,
                        $strLevelId
                    );

                    $categoryName = $this->getCategoryName($subCategory);
                    $categoryDesc = $this->getCategoryDescription($subCategory);
                    //parse variables
                    $objTpl->setVariable(array(
                        $this->moduleLangVar.'_CATEGORY_LEVEL_ID'             => $subCategory->getId(),
                        $this->moduleLangVar.'_CATEGORY_LEVEL_NAME'           => contrexx_raw2xhtml($categoryName),
                        $this->moduleLangVar.'_CATEGORY_LEVEL_LINK'           => $strIndexHeaderTag.'<a href="index.php?section='.$this->moduleName.$strCategoryCmd.$strLevelId.'&amp;cid='.$subCategory->getId().'">'.contrexx_raw2xhtml($categoryName).'</a>',
                        $this->moduleLangVar.'_CATEGORY_LEVEL_DESCRIPTION'    => contrexx_raw2xhtml($categoryDesc),
                        $this->moduleLangVar.'_CATEGORY_LEVEL_PICTURE'        => '<img src="'.$subCategory->getPicture().'" border="0" alt="'.contrexx_raw2xhtml($categoryName).'" />',
                        $this->moduleLangVar.'_CATEGORY_LEVEL_PICTURE_SOURCE' => $subCategory->getPicture(),
                        $this->moduleLangVar.'_CATEGORY_LEVEL_NUM_ENTRIES'    => $this->countEntries($category->getId()),
                        $this->moduleLangVar.'_CATEGORY_LEVEL_CHILDREN'       => $childrenString,
                    ));

                    $intBlockId = $arrExistingBlocks[$i];

                    $objTpl->parse($this->moduleNameLC.'CategoriesLevels_row_'.$intBlockId);
                    $objTpl->clearVariables();

                    $strFirstIndexHeader = $strIndexHeader;
                }
                break;
            case 3:
                //Category Dropdown Menu
                $rootCategory = $this->getCategoryById($this->nestedSetRootId);
                return $this->getCategoryDropDown($rootCategory, $categoryId);
                break;
            case 4:
                //Category Selector (modify view)
                $rootCategory          = $this->getCategoryById($this->nestedSetRootId);
                $arrSelectedCategories = $this->getSelectedCategoriesByEntryId($intEntryId);
                list($selectedOptions, $notSelectedOptions) = $this->getCategoryOptions4EditView($rootCategory, $arrSelectedCategories);
                $arrSelectorOptions = array(
                  'selected'     => $selectedOptions,
                  'not_selected' => $notSelectedOptions
                );
                return $arrSelectorOptions;
                break;
            case 5:
                //Frontend View Detail
                $category  = $this->getCategoryById($categoryId);
                if (!$category) {
                    $objTpl->hideBlock($this->moduleNameLC.'CategoryLevelDetail');
                    return;
                }

                $strLevelId          = isset($_GET['lid']) ? "&amp;lid=".intval($_GET['lid']) : '';
                $categoryName        = $this->getCategoryName($category);
                $categoryDescription = $this->getCategoryDescription($category);
                $thumbImage          = $this->getThumbImage($category->getPicture());
                $objTpl->setVariable(array(
                    $this->moduleLangVar.'_CATEGORY_LEVEL_ID'             => $category->getId(),
                    $this->moduleLangVar.'_CATEGORY_LEVEL_NAME'           => contrexx_raw2xhtml($categoryName),
                    $this->moduleLangVar.'_CATEGORY_LEVEL_LINK'           => '<a href="index.php?section='.$this->moduleName.$strLevelId.'&amp;cid='.$category->getId().'">'.contrexx_raw2xhtml($categoryName).'</a>',
                    $this->moduleLangVar.'_CATEGORY_LEVEL_DESCRIPTION'    => contrexx_raw2xhtml($categoryDescription),
                    $this->moduleLangVar.'_CATEGORY_LEVEL_PICTURE'        => '<img src="'. $thumbImage .'" border="0" alt="'. contrexx_raw2xhtml($categoryName) .'" />',
                    $this->moduleLangVar.'_CATEGORY_LEVEL_PICTURE_SOURCE' => $category->getPicture(),
                    $this->moduleLangVar.'_CATEGORY_LEVEL_NUM_ENTRIES'    => $this->countEntries($category->getId()),
                ));

                if(!\FWValidator::isEmpty($category->getPicture()) && $this->arrSettings['settingsShowCategoryImage'] == 1) {
                    $objTpl->parse($this->moduleNameLC.'CategoryLevelPicture');
                } else {
                    $objTpl->hideBlock($this->moduleNameLC.'CategoryLevelPicture');
                }

                if(!empty($categoryDescription) && $this->arrSettings['settingsShowCategoryDescription'] == 1) {
                    $objTpl->parse($this->moduleNameLC.'CategoryLevelDescription');
                } else {
                    $objTpl->hideBlock($this->moduleNameLC.'CategoryLevelDescription');
                }

                $objTpl->parse($this->moduleNameLC.'CategoryLevelDetail');
                break;
            case 6:
                //Frontend Tree Placeholder
                $expandedCategoryIds = $this->getExpandedCategoryIds($categoryId);
                $category            = $this->getCategoryById($this->nestedSetRootId);

                $strLevelId = isset($_GET['lid']) ? "&amp;lid=".intval($_GET['lid']) : '';
                $tpl = <<<TEMPLATE
    <!-- BEGIN {$this->moduleNameLC}CategoriesList -->
    <li class="level_{{$this->moduleLangVar}_CATEGORY_LEVEL_NUMBER}">
        <a href="index.php?section={$this->moduleName}{$strLevelId}&amp;cid={{$this->moduleLangVar}_CATEGORY_ID}$strLevelId" class="{{$this->moduleLangVar}_CATEGORY_ACTIVE_STATUS}">
            {{$this->moduleLangVar}_CATEGORY_NAME}
        </a>
    </li>
    <!-- END {$this->moduleNameLC}CategoriesList -->
TEMPLATE;
                $template = new \Cx\Core\Html\Sigma('.');
                $template->setTemplate($tpl);
                $this->parseCategoryTree(
                    $template,
                    $category,
                    $expandedCategoryIds,
                    $expandCategory == 'all',
                    $startLevel,
                    $category->getShowSubcategories()
                );
                return $template->get();
                break;
        }
    }

    /**
     * Get category dropdown for Entry edit view
     *
     * @param \Cx\Modules\MediaDir\Model\Entity\Category  $category               Parent category
     * @param array                                       $arrSelectedCategories  Array selected categories
     *
     * @return array
     */
    public function getCategoryOptions4EditView(\Cx\Modules\MediaDir\Model\Entity\Category $category, $arrSelectedCategories)
    {
        $subCategories = $this->getSubCategoriesByCategory($category);
        $strSelectedOptions = $strNotSelectedOptions = '';
        foreach ($subCategories as $subCategory) {
            $spacer       = str_repeat('----', $subCategory->getLvl() - 2);
            $spacer      .= $subCategory->getLvl() > 0 ? '&nbsp;' : '';
            $categoryName = $this->getCategoryName($subCategory);

            $option = '<option value="'. $subCategory->getId() .'">'. $spacer . contrexx_raw2xhtml($categoryName).'</option>';
            if (in_array($subCategory->getId(), $arrSelectedCategories)) {
                $strSelectedOptions .= $option;
            } else {
                $strNotSelectedOptions .= $option;
            }
            list($selectedOptions, $notSelectedOptions) = $this->getCategoryOptions4EditView($subCategory, $arrSelectedCategories);
            $strSelectedOptions    .= $selectedOptions;
            $strNotSelectedOptions .= $notSelectedOptions;
        }
        return array($strSelectedOptions, $strNotSelectedOptions);
    }

    /**
     * Get sub categories of a category, by applying the sorting and status
     *
     * @param \Cx\Modules\MediaDir\Model\Entity\Category $category
     *
     * @return array Array collection of subcategories
     */
    public function getSubCategoriesByCategory(\Cx\Modules\MediaDir\Model\Entity\Category $category)
    {

        $addSql   = array();
        if ($this->cx->getMode() == \Cx\Core\Core\Controller\Cx::MODE_FRONTEND) {
            $addSql['where'] = 'active = 1';
        }
        $children = array();
        $childrenResult = $this->categoryNestedSet->getChildren($category->getId(), true, true, false, $addSql);
        if (!$childrenResult) {
            return $children;
        }
        foreach ($childrenResult as $value) {
            $subCategory = $this->createCategoryFromArray($value);
            $this->loadCategoryLocale($subCategory);
            $children[] = $subCategory;
        }
        if (in_array($this->arrSettings['settingsCategoryOrder'], array(1, 2))) {
            // sort by category name
            uasort($children, array($this, 'sortCategoriesByName'));
        }
        return $children;
    }

    /**
     * Custom sorting callback function to sort the categories by name
     *
     * @param \Cx\Modules\MediaDir\Model\Entity\Category $a
     * @param \Cx\Modules\MediaDir\Model\Entity\Category $b
     *
     * @return boolean
     */
    public function sortCategoriesByName(
        \Cx\Modules\MediaDir\Model\Entity\Category $a,
        \Cx\Modules\MediaDir\Model\Entity\Category $b
    ) {
        return strcmp($this->getCategoryName($a), $this->getCategoryName($b));
    }

    /**
     * Get the category dropdown
     *
     * @param \Cx\Modules\MediaDir\Model\Entity\Category $category            Parent category instance
     * @param integer                                    $selectedCategoryId  Selected category id
     *
     * @return string
     */
    public function getCategoryDropDown(\Cx\Modules\MediaDir\Model\Entity\Category $category, $selectedCategoryId = null)
    {
        $strDropdownOptions = '';
        $subCategories      = $this->getSubCategoriesByCategory($category);
        foreach ($subCategories as $subCategory) {
            $strSelected  = $selectedCategoryId == $subCategory->getId() ? 'selected="selected"' : '';
            $spacer       = str_repeat('----', $subCategory->getLvl() - 2);
            $spacer      .= $subCategory->getLvl() > 1 ? '&nbsp;' : '';
            $categoryName = $this->getCategoryName($subCategory);

            $strDropdownOptions .= '<option value="'. $subCategory->getId() .'" '. $strSelected .' >'. $spacer . contrexx_raw2xhtml($categoryName) .'</option>';
            $strDropdownOptions .= $this->getCategoryDropDown($subCategory, $selectedCategoryId);
        }
        return $strDropdownOptions;
    }

    /**
     * Get expanaged category id's
     *
     * @param integer $categoryId Category id
     *
     * @return Array
     */
    public function getExpandedCategoryIds($categoryId)
    {
        $categories = $this->getExpandedCategories($categoryId);
        $categoryIds = array();
        foreach ($categories as $category) {
            $categoryIds[] = $category->getId();
        }

        return $categoryIds;
    }

    /**
     * Get all expanded categories
     *
     * @param type $categoryId
     *
     * @return Array
     */
    function getExpandedCategories($categoryId)
    {
        $categoryId = contrexx_input2int($categoryId);
        if (!$categoryId) {
            return array();
        }
        $category = $this->getCategoryById($categoryId);
        if (!$category) {
            return array();
        }

        $categories = array($category);
        while (   $category->getId() != $category->getParent()
               && $category = $this->getCategoryById($category->getParent())
        ) {
            $categories[] = $category;
        }

        return $categories;
    }

    /**
     * Save category
     *
     * @param \Cx\Modules\MediaDir\Model\Entity\Category $category Category instance
     * @param array                                      $arrData  Array data to save
     *
     * @return boolean TRUE on success, false otherwise
     */
    function saveCategory(\Cx\Modules\MediaDir\Model\Entity\Category $category, $arrData)
    {
        global $_ARRAYLANG;

        $oldParentId  = $category->getParent();
        $intParentId  = intval($arrData['categoryPosition']);

        $parentCategory = null;
        if ($intParentId) {
            $parentCategory = $this->getCategoryById($intParentId) ? $intParentId : null;
        } else {
            $parentCategory = $this->nestedSetRootId;
        }
        if (!$parentCategory) {
            return false;
        }
        if ($category->getId() && $category->getId() == $parentCategory) {
            return false;
        }

        //get data
        $intShowEntries    = intval($arrData['categoryShowEntries']);
        $intShowCategories = isset($arrData['categoryShowSubcategories']) ? contrexx_input2int($arrData['categoryShowSubcategories']) : 0;
        $intActive         = intval($arrData['categoryActive']);
        $strPicture        = contrexx_addslashes(contrexx_strip_tags($arrData['categoryImage']));
        $arrName           = $arrData['categoryName'];
        $arrDescription    = $arrData['categoryDescription'];

        $category->setParent($parentCategory);
        $category->setShowEntries($intShowEntries);
        $category->setShowSubcategories($intShowCategories);
        $category->setActive($intActive);
        $category->setPicture($strPicture);

        if(empty($arrName[0])) {
            $arrName[0] = "[[".$_ARRAYLANG['TXT_MEDIADIR_NEW_CATEGORY']."]]";
        }
        foreach ($this->arrFrontendLanguages as $arrLang) {
            $langId         = $arrLang['id'];
            $strName        = $arrName[$langId] ? $arrName[$langId] : $arrName[0];
            $strDescription = $arrDescription[$langId];

            $categoryLocale = $category->getLocaleByLang($langId);
            if (!$categoryLocale) {
                $categoryLocale = new \Cx\Modules\MediaDir\Model\Entity\CategoryLocale();
            }

            $categoryLocale->setCategory($category);
            $categoryLocale->setLangId($arrLang['id']);
            $categoryLocale->setCategoryName($strName);
            $categoryLocale->setCategoryDescription($strDescription);

            if (!$category->getId()) {
                $category->addLocale($categoryLocale);
            }
        }
        $values = array(
            'category_order'     => $category->getOrder(),
            'show_subcategories' => $category->getShowSubcategories(),
            'show_entries'       => $category->getShowEntries(),
            'picture'            => $category->getPicture(),
            'active'             => $category->getActive(),
        );
        if (!$category->getId()) {
            $intId = $this->categoryNestedSet->createSubNode($category->getParent(), $values);
            if (!$intId) {
                return false;
            }
            $category->setId($intId);
        } else {
            if ($oldParentId != $category->getParent()) {
                $this->categoryNestedSet->moveTree($category->getId(), $category->getParent(), NESE_MOVE_BELOW);
            }
            $this->categoryNestedSet->updateNode($category->getId(), $values);
        }

        foreach ($category->getLocale() as $locale) {
            $this->saveCategoryLocale($locale);
        }

        return true;
    }

    /**
     * Save the category locale information
     *
     * @param \Cx\Modules\MediaDir\Model\Entity\CategoryLocale $locale
     */
    public function saveCategoryLocale(\Cx\Modules\MediaDir\Model\Entity\CategoryLocale $locale)
    {
        global $objDatabase;

        $objDatabase->Execute('
            INSERT INTO
                `' . DBPREFIX . 'module_' . $this->moduleTablePrefix . '_categories_names`
            SET
                `lang_id`="' . intval($locale->getLangId()) . '",
                `category_id`="' . ($locale->getCategory() ? $locale->getCategory()->getId() : 0) . '",
                `category_name`="' . contrexx_raw2db($locale->getCategoryName()) . '",
                `category_description`="' . contrexx_raw2db($locale->getCategoryDescription()) . '"
            ON DUPLICATE KEY UPDATE
                `category_name`="' . contrexx_raw2db($locale->getCategoryName()) . '",
                `category_description`="' . contrexx_raw2db($locale->getCategoryDescription()) . '"
        ');
    }

    /**
     * Get the category entity by given id
     *
     * @param integer $id Category id
     *
     * @return mixed \Cx\Modules\MediaDir\Model\Entity\Category instance if loaded, false otherwise
     */
    public function getCategoryById($id)
    {
        $categoryArray = $this->categoryNestedSet->pickNode($id, true);
        if (empty($categoryArray)) {
            return false;
        }

        $category = $this->createCategoryFromArray($categoryArray);
        $this->loadCategoryLocale($category);

        return $category;
    }

    /**
     * Loaded the locale information of category from the database
     *
     * @param \Cx\Modules\MediaDir\Model\Entity\Category $category
     */
    public function loadCategoryLocale(\Cx\Modules\MediaDir\Model\Entity\Category $category)
    {
        global $objDatabase;

        $categoryAttributes = $objDatabase->Execute('
            SELECT
                `lang_id` AS `lang_id`,
                `category_name` AS `name`,
                `category_description` AS `description`
            FROM
                `' . DBPREFIX . 'module_' . $this->moduleTablePrefix . '_categories_names`
            WHERE
                category_id=' . $category->getId()
        );

        if ($categoryAttributes !== false) {
            while (!$categoryAttributes->EOF) {
                $locale = new \Cx\Modules\MediaDir\Model\Entity\CategoryLocale();
                $locale->setLangId($categoryAttributes->fields['lang_id']);
                $locale->setCategoryName($categoryAttributes->fields['name']);
                $locale->setCategoryDescription($categoryAttributes->fields['description']);
                $category->addLocale($locale);
                $categoryAttributes->MoveNext();
            }
        }
    }

    /**
     * Create the category instance and set the info of category from the given array
     * 
     * @param array $input
     *
     * @return \Cx\Modules\MediaDir\Model\Entity\Category
     */
    public function createCategoryFromArray($input)
    {
        $category = new \Cx\Modules\MediaDir\Model\Entity\Category();
        $category->setId($input['id']);
        $category->setParent($input['rootid']);
        $category->setOrder($input['norder']);
        $category->setLvl($input['level']);
        $category->setShowSubcategories($input['show_subcategories']);
        $category->setShowEntries($input['show_entries']);
        $category->setPicture($input['picture']);
        $category->setActive($input['active']);

        return $category;
    }

    /**
     * Delete cateogry from the tree
     *
     * @param integer $intCategoryId Category id to delete
     *
     * @return boolean
     */
    function deleteCategory($intCategoryId = null)
    {
        global $objDatabase;

        if (!$intCategoryId) {
            return false;
        }
        $subcats = $this->categoryNestedSet->getSubBranch($intCategoryId, true);
        if (count($subcats) > 0) {
            foreach ($subcats as $subcat) {
                if (!$this->deleteCategory(intval($subcat['id']))) {
                    return false;
                }
            }
        }
        if (   $objDatabase->Execute('DELETE FROM '.DBPREFIX.'module_'.$this->moduleTablePrefix.'_categories_names WHERE category_id='. contrexx_input2int($intCategoryId))
            && $objDatabase->Execute('DELETE FROM '.DBPREFIX.'module_'.$this->moduleTablePrefix.'_rel_entry_categories WHERE category_id='. contrexx_input2int($intCategoryId))
            && $this->categoryNestedSet->deleteNode($intCategoryId) !== false
        ) {
            return true;
        }
        return true;
    }

    /**
     * Get subcategories id
     *
     * @param \Cx\Modules\MediaDir\Model\Entity\Category $category
     *
     * @return array subcategory id's
     */
    public function getSubCategories(\Cx\Modules\MediaDir\Model\Entity\Category $category)
    {
        $categories    = array();
        $subCategories = $this->getSubCategoriesByCategory($category);
        foreach ($subCategories as $subCategory) {
            $categories = array_merge(
                array($subCategory->getId()),
                $this->getSubCategories($subCategory)
            );
        }
        return $categories;
    }

    /**
     * Count number of entries for the category
     *
     * @param integer $intCategoryId Category id
     *
     * @return int
     */
    function countEntries($intCategoryId = null)
    {
        global $objDatabase, $_LANGID;

        if (!$this->countEntries) {
            return 0;
        }

        $intCategoryId = intval($intCategoryId);
        if (!$intCategoryId) {
            $category = $this->getCategoryById($this->nestedSetRootId);
        } else {
            $category = $this->getCategoryById($intCategoryId);
        }

        if (!$category) {
            return 0;
        }

        $categories = array_merge(
            array($category->getId()),
            $this->getSubCategories($category)
        );
        $whereCategory = '';
        if ($categories) {
            $whereCategory = " AND `rel_categories`.`category_id` IN (" . implode(', ', contrexx_input2int($categories)) .")";
        }
        $objCountEntriesRS = $objDatabase->Execute("
                                                SELECT COUNT(`entry`.`id`) as c
                                                FROM
                                                        `" . DBPREFIX . "module_".$this->moduleTablePrefix."_entries` AS `entry`
                                                INNER JOIN
                                                    `".DBPREFIX."module_".$this->moduleTablePrefix."_rel_entry_categories` AS `rel_categories`
                                                ON
                                                    `rel_categories`.`entry_id` = `entry`.`id`
                                                LEFT JOIN
                                                    `".DBPREFIX."module_".$this->moduleTablePrefix."_rel_entry_inputfields` AS rel_inputfield
                                                ON
                                                    rel_inputfield.`entry_id` = `entry`.`id`
                                                WHERE
                                                    `entry`.`active` = 1
                                                AND
                                                    (rel_inputfield.`form_id` = entry.`form_id`)
                                                AND
                                                    (rel_inputfield.`field_id` = (".$this->getQueryToFindFirstInputFieldId()."))
                                                AND
                                                    (rel_inputfield.`lang_id` = '".$_LANGID."')
                                                AND ((`entry`.`duration_type`=2 AND `entry`.`duration_start` <= ".time()." AND `entry`.`duration_end` >= ".time().") OR (`entry`.`duration_type`=1))
                                                    " . $whereCategory . "
                                                GROUP BY
                                                    `rel_categories`.`category_id`");

        return contrexx_input2int($objCountEntriesRS->fields['c']);
    }

    /**
     * Save the category order
     *
     * @param array $arrData
     */
    function saveOrder($arrData)
    {
        $sorting = !empty($arrData['catOrder']) ? $arrData['catOrder'] : array();
        asort($sorting);
        $categories = array_keys($sorting);
        foreach($categories as $intCatId) {
            $category = $this->categoryNestedSet->pickNode($intCatId);
            if (!$category) {
                continue;
            }
            $this->categoryNestedSet->moveTree($intCatId, $category->rootid, NESE_MOVE_BELOW);
        }
    }

    /**
     * Create UL/LI of category tree
     *
     * @param Category  $category       Instance of category id
     * @param string    $strCategoryCmd Url category id
     * @param string    $strLevelId     Url level id
     *
     * @return string
     */
    public function createCategoryTree(\Cx\Modules\MediaDir\Model\Entity\Category $category, $strCategoryCmd, $strLevelId)
    {
        $subCategories = $this->getSubCategoriesByCategory($category);
        if (empty($subCategories)) {
            return '';
        }
        $childrenString = '<ul>';
        foreach ($subCategories as $subCategory) {
            $categoryName = $this->getCategoryName($subCategory);

            $childrenString .= '<li><a href="index.php?section=' . $this->moduleName . $strCategoryCmd . $strLevelId . '&amp;cid=' . $subCategory->getId() . '">' . contrexx_raw2xhtml($categoryName) . '</a>';
            $childrenString .= $this->createCategoryTree($subCategory, $strCategoryCmd, $strLevelId);
            $childrenString .= '</li>';
        }
        $childrenString .= '</ul>';

        return $childrenString;
    }
}
