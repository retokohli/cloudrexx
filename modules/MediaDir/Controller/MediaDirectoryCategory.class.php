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

use Cx\Modules\MediaDir\Model\Entity\Category as Category;

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
     * Category repository
     *
     * @var \Cx\Modules\MediaDir\Model\Repository\CategoryRepository
     */
    public $categoryRepository;

    /**
     * Constructor
     */
    function __construct($name = '')
    {
        parent::__construct('.', $name);
        parent::getSettings();
        parent::getFrontendLanguages();

        if ($this->arrSettings['settingsCountEntries'] == 1 || $this->cx->getMode() == \Cx\Core\Core\Controller\Cx::MODE_BACKEND) {
            $this->countEntries = true;
        }
        $this->categoryRepository = $this->em->getRepository('Cx\Modules\MediaDir\Model\Entity\Category');
    }

    /**
     * Parse the category details
     *
     * @param \Cx\Core\Html\Sigma   $objTpl             Instance of template object
     * @param Category              $category           Instance of category to parse
     * @param string                $strCategoryIcon    String category icon
     * @param string                $strCategoryClass   Class name for the category
     * @param string                $blockName          Parse block name
     */
    public function parseCategoryDetail(
        \Cx\Core\Html\Sigma $objTpl,
        Category $category,
        $strCategoryIcon = null,
        $strCategoryClass = 'inactive',
        $blockName = 'CategoriesList'
    ) {
        $intSpacerSize = ($category->getLvl() - 1) * 21;
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
            $this->moduleLangVar.'_CATEGORY_LEVEL_NUMBER'         => $category->getLvl() - 1,
            $this->moduleLangVar.'_CATEGORY_ACTIVE_STATUS'        => $strCategoryClass,
        ));

        $objTpl->parse($this->moduleNameLC . $blockName);
    }

    /**
     * Get name of category by output language
     *
     * @param Category $category
     *
     * @return string
     */
    public function getCategoryName(Category $category)
    {
        global $_LANGID;

        $locale       = $category->getLocaleByLang($_LANGID);
        return $locale ? $locale->getCategoryName() : '';
    }

    /**
     * Get category description by output language
     *
     * @param Category $category
     *
     * @return string
     */
    public function getCategoryDescription(Category $category)
    {
        global $_LANGID;

        $locale       = $category->getLocaleByLang($_LANGID);
        return $locale ? $locale->getCategoryDescription() : '';
    }

    /**
     * Parse category tree
     *
     * @param \Cx\Core\Html\Sigma   $objTpl                 Instance of template
     * @param Category              $category               Root category to parse
     * @param array                 $expandedCategoryIds    Expanded category array
     * @param boolean               $expandAll              True to expand all categories
     * @param boolean               $checkShowSubCategory   True to check the show subcategory option
     */
    public function parseCategoryTree(
        \Cx\Core\Html\Sigma $objTpl,
        Category $category,
        $expandedCategoryIds = array(),
        $expandAll = false,
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
                    $expCatId = $subcategory->getParent()->getId();
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
            $this->parseCategoryDetail($objTpl, $subcategory, $strCategoryIcon, $strCategoryClass);

            if ($isExpanded) {
                $this->parseCategoryTree($objTpl, $subcategory, $expandedCategoryIds, $expandAll);
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
        $categories = $objDatabase->Execute('SELECT
                                               `category_id`
                                            FROM
                                                `'. DBPREFIX .'module_'. $this->moduleTablePrefix .'_rel_entry_categories`
                                            WHERE
                                                `entry_id` = "'. $entryId .'"');
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
        $arrExistingBlocks = null
    ) {

        switch ($intView) {
            case 1:
                //Backend View
                $expandCategory      = isset($_GET['exp_cat']) ? $_GET['exp_cat'] : '';
                $expandedCategoryIds = $this->getExpandedCategoryIds($expandCategory);

                $this->parseCategoryTree(
                    $objTpl,
                    $this->categoryRepository->getRoot(),
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

                $category = $parentCategoryId ? $this->categoryRepository->findOneById($parentCategoryId) : false;
                if (!$category) {
                    $category = $this->categoryRepository->getRoot();
                }
                $intNumCategories = $this->categoryRepository->getChildCount($category, true);

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
                return $this->getCategoryDropDown($this->categoryRepository->getRoot(), $categoryId);
                break;
            case 4:
                //Category Selector (modify view)
                $arrSelectedCategories = $this->getSelectedCategoriesByEntryId($intEntryId);
                list($selectedOptions, $notSelectedOptions) = $this->getCategoryOptions4EditView($this->categoryRepository->getRoot(), $arrSelectedCategories);
                $arrSelectorOptions = array(
                  'selected'     => $selectedOptions,
                  'not_selected' => $notSelectedOptions
                );
                return $arrSelectorOptions;
                break;
            case 5:
                //Frontend View Detail
                $category  = $this->categoryRepository->findOneById($categoryId);
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

                $category = $this->categoryRepository->findOneById($categoryId);
                if (!$category) {
                    $category = $this->categoryRepository->getRoot();
                }

                $tpl = <<<TEMPLATE
    <!-- BEGIN {$this->moduleNameLC}CategoriesList -->
    <li class="level_{{$this->moduleLangVar}_CATEGORY_LEVEL_NUMBER}">
        <a href="index.php?section={$this->moduleName}{$strLevelId}&amp;cid={{$this->moduleLangVar}_CATEGORY_ID}" class="{{$this->moduleLangVar}_CATEGORY_ACTIVE_STATUS}">
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
                    $category->getShowSubcategories()
                );
                return $template->get();
                break;
        }
    }

    /**
     * Get category dropdown for Entry edit view
     *
     * @param Category  $category               Parent category
     * @param array     $arrSelectedCategories  Array selected categories
     *
     * @return array
     */
    public function getCategoryOptions4EditView(Category $category, $arrSelectedCategories)
    {
        $subCategories = $this->getSubCategoriesByCategory($category);
        $strSelectedOptions = $strNotSelectedOptions = '';
        foreach ($subCategories as $subCategory) {
            $spacer       = str_repeat('----', $subCategory->getLvl() - 1);
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
     * Get settings sorting field
     *
     * @return string
     */
    public function getSortingField()
    {
        switch($this->arrSettings['settingsCategoryOrder']) {
            case 0;
                $sortOrder = 'node.order';
                break;
            case 1;
            case 2;
                $sortOrder = 'lc.category_name';
                break;
        }
        return $sortOrder;
    }

    /**
     * Get sub categories of a category, by applying the sorting and status
     *
     * @param Category $category
     *
     * @return array Array collection of subcategories
     */
    public function getSubCategoriesByCategory(Category $category)
    {
        global $_LANGID;

        return $this->categoryRepository->getChildren(
            $category,
            $_LANGID,
            $this->cx->getMode() == \Cx\Core\Core\Controller\Cx::MODE_FRONTEND,
            $this->getSortingField()
        );
    }

    /**
     * Get the category dropdown
     * 
     * @param Category $category            Parent category instance
     * @param integer  $selectedCategoryId  Selected category id
     *
     * @return string
     */
    public function getCategoryDropDown(Category $category, $selectedCategoryId = null)
    {
        $strDropdownOptions = '';
        $subCategories      = $this->getSubCategoriesByCategory($category);
        foreach ($subCategories as $subCategory) {
            $strSelected  = $selectedCategoryId == $subCategory->getId() ? 'selected="selected"' : '';
            $spacer       = str_repeat('----', $subCategory->getLvl() - 1);
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
        $category = $this->categoryRepository->findOneById($categoryId);
        if (!$category) {
            return array();
        }
        $categories = array($category);
        while ($category = $category->getParent()) {
            $categories[] = $category;
        }

        return $categories;
    }

    /**
     * Save category
     *
     * @param Category $category Category instance
     * @param array    $arrData  Array data to save
     *
     * @return boolean TRUE on success, false otherwise
     */
    function saveCategory(Category $category, $arrData)
    {
        global $_ARRAYLANG;

        $intParentId  = intval($arrData['categoryPosition']);

        $parentCategory = null;
        if ($intParentId) {
            $parentCategory = $this->categoryRepository->findOneById($intParentId);
        } else {
            $parentCategory = $this->categoryRepository->getRoot();
        }
        if (!$parentCategory) {
            return false;
        }

        $intId    = $category->getId();

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
        
        if (empty($intId)) {
            $category->setOrder(0);
            $this->em->persist($category);
        }

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
            $this->em->persist($categoryLocale);
        }
        $this->em->flush();

        return true;
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
        if (!$intCategoryId) {
            return false;
        }
        $category = $this->categoryRepository->findOneById($intCategoryId);
        $this->em->remove($category);
        $this->em->flush();

        return true;
    }

    /**
     * Get subcategories id
     *
     * @param Category $category
     *
     * @return array subcategory id's
     */
    public function getSubCategories(Category $category)
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
            $category = $this->categoryRepository->getRoot();
        } else {
            $category = $this->categoryRepository->findOneById($intCategoryId);
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
     *
     * @return boolean
     */
    function saveOrder($arrData) {
        foreach($arrData['catOrder'] as $intCatId => $intCatOrder) {
            $category = $this->categoryRepository->findOneById($intCatId);
            if (!$category) {
                continue;
            }
            $category->setOrder(contrexx_input2int($intCatOrder));
        }
        $this->em->flush();
        $this->categoryRepository->reorder($this->categoryRepository->getRoot(), 'order');

        return true;
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
    public function createCategoryTree(Category $category, $strCategoryCmd, $strLevelId)
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
