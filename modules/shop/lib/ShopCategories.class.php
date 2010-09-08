<?php

/**
 * Shop Product Categories
 *
 * Various helper methods for displaying stuff
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @access      public
 * @version     2.1.0
 * @package     contrexx
 * @subpackage  module_shop
 */


/**
 * Product objects
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/Product.class.php';
/**
 * The Products helper object
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/Products.class.php';

define('SHOP_CATEGORY_IMAGE_PATH',      ASCMS_SHOP_IMAGES_PATH.'/');
define('SHOP_CATEGORY_IMAGE_WEB_PATH',  ASCMS_SHOP_IMAGES_WEB_PATH.'/');


/**
 * Shop Categories
 *
 * Helper class
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @access      public
 * @version     2.1.0
 * @package     contrexx
 * @subpackage  module_shop
 */
class ShopCategories
{
    /**
     * ShopCategory Tree array
     * @var     array
     * @access  private
     */
    private static $arrShopCategory;
    /**
     * ShopCategory array index
     * @var     array
     * @access  private
     */
    private static $arrShopCategoryIndex;
    /**
     * Virtual ShopCategory Tree array
     * @var     array
     * @access  private
     */
    private static $arrShopCategoryVirtual;
    /**
     * Virtual ShopCategory array index
     * @var     array
     * @access  private
     */
    private static $arrShopCategoryVirtualIndex;
    /**
     * The trail from the root (0, zero) to the selected ShopCategory.
     *
     * See {@link getTrailArray()} for details.
     * @var     array
     */
    private static $arrTrail;


    /**
     * Returns an array representing a tree of ShopCategories,
     * not including the root chosen.
     *
     * See {@link ShopCategories::getTreeArray()} for a detailed explanation
     * of the array structure.
     * @version 2.1.0
     * @param   boolean $flagFull           If true, the full tree is built,
     *                                      only the parts visible for
     *                                      $selected_id otherwise.
     *                                      Defaults to false.
     * @param   boolean $active             Only return ShopCategories
     *                                      with active == true if true.
     *                                      Defaults to false.
     * @param   boolean $flagVirtual        If true, also returns the virtual
     *                                      content of ShopCategories marked
     *                                      as virtual.  Defaults to false.
     * @param   integer $selected_id        The optional selected ShopCategory
     *                                      ID.  If set and greater than zero,
     *                                      only the ShopCategories needed
     *                                      to display the Shop page are
     *                                      returned.
     * @param   integer $parent_id          The optional root ShopCategories ID.
     *                                      Defaults to 0 (zero).
     * @param   integer $maxlevel           The optional maximum nesting level.
     *                                      0 (zero) means all.
     *                                      Defaults to 0 (zero).
     * @return  mixed                       The array of ShopCategories on
     *                                      success, false on failure.
     * @static
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getTreeArray(
        $flagFull=false, $active=true, $flagVirtual=true,
        $selected_id=0, $parent_id=0, $maxlevel=0
    ) {
        // Return the same array if it's already been initialized
// TODO:  This won't work for the shopnavbar, as the categories menu is built first.
// This needs to be able to detect whether the arguments are the same.
//        if (is_array(self::$arrShopCategory))
//            return self::$arrShopCategory;
        // Otherwise, initialize it now
        if (self::buildTreeArray(
            $flagFull, $active, $flagVirtual,
            $selected_id, $parent_id, $maxlevel
        )) return self::$arrShopCategory;
        // It failed, probably due to a value of $selected_id that doesn't
        // exist.  Retry without it.
        if ($selected_id > 0)
            return self::buildTreeArray(
                $flagFull, $active, $flagVirtual,
                0, $parent_id, $maxlevel
            );
        // If that didn't help...
        return false;
    }


    /**
     * Returns an array representing the index for the tree of ShopCategories
     * {@link $arrShopCategory}.
     *
     * See {@link ShopCategories::buildTreeArray()} for a detailed explanation
     * of the array structure.
     * Note that you *MUST* call either {@link ShopCategories::getTreeArray()}
     * or {@link ShopCategories::buildTreeArray()} in order for the index
     * to be initialized.
     * @version 2.1.0
     * @return  mixed                       The ShopCategoriy index array on
     *                                      success, false on failure.
     * @static
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getTreeIndexArray()
    {
        // Return the same array if it's already been initialized
        if (is_array(self::$arrShopCategoryIndex))
            return self::$arrShopCategoryIndex;
        return false;
    }


    /**
     * Builds the $arrShopCategory array as returned by
     * {@link ShopCategories::getTreeArray()}, representing a tree of
     * ShopCategories, not including the root chosen.
     *
     * The resulting array looks like:
     * array(
     *    'id           => ShopCategory ID
     *    'name'        => Category name,
     *    'description' => Category description,
     *    'parent_id'   => parent ID
     *    'ord'         => order value,
     *    'active'      => active flag (boolean),
     *    'picture'     => 'picture name',
     *    'flags'       => 'Category flags' (string),
     *    'virtual'     => virtual flag status (boolean),
     *    'level'       => nesting level,
     * ),
     * ... more parents
     * Note that this includes the virtual ShopCategories and their children.
     * @version 2.1.0
     * @param   boolean $flagFull           If true, the full tree is built,
     *                                      only the parts visible for
     *                                      $selected_id otherwise.
     *                                      Defaults to false.
     * @param   boolean $active             Only return ShopCategories
     *                                      with active == true if true.
     *                                      Defaults to true.
     * @param   boolean $flagVirtual        If true, also returns the virtual
     *                                      content of ShopCategories marked
     *                                      as virtual.  Defaults to false.
     * @param   integer $selected_id        The optional selected ShopCategory
     *                                      ID.  If set and greater than zero,
     *                                      only the ShopCategories needed
     *                                      to display the Shop page are
     *                                      returned.
     * @param   integer $parent_id          The optional root ShopCategories ID.
     *                                      Defaults to 0 (zero).
     * @param   integer $maxlevel           The optional maximum nesting level.
     *                                      0 (zero) means all.
     *                                      Defaults to 0 (zero).
     * @return  boolean                     True on success, false otherwise.
     * @static
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function buildTreeArray(
        $flagFull=false, $active=true, $flagVirtual=true,
        $selected_id=0, $parent_id=0, $maxlevel=0
    ) {
        self::$arrShopCategory = array();
        self::$arrShopCategoryIndex = array();
        // Set up the trail from the root (0, zero) to the selected ShopCategory
        if (!self::buildTrailArray($selected_id)) return false;
        if (!self::buildTreeArrayRecursive(
            $flagFull, $active, $flagVirtual,
            $selected_id, $parent_id, $maxlevel
        )) return false;
        return true;
    }


    /**
     * Recursively builds the $arrShopCategory array as returned by
     * {@link ShopCategories::getTreeArray()}.
     *
     * See {@link buildTreeArray()} for details.
     * @version 2.1.0
     * @param   boolean $flagFull           If true, the full tree is built,
     *                                      only the parts visible for
     *                                      $selected_id otherwise.
     *                                      Defaults to false.
     * @param   boolean $active             Only return ShopCategories
     *                                      with active == true if true.
     *                                      Defaults to true.
     * @param   boolean $flagVirtual        If true, also returns the virtual
     *                                      content of ShopCategories marked
     *                                      as virtual.  Defaults to false.
     * @param   integer $selected_id        The optional selected ShopCategory
     *                                      ID.  If set and greater than zero,
     *                                      only the ShopCategories needed
     *                                      to display the Shop page are
     *                                      returned.
     * @param   integer $parent_id          The optional root ShopCategories ID.
     *                                      Defaults to 0 (zero).
     * @param   integer $maxlevel           The optional maximum nesting level.
     *                                      0 (zero) means all.
     *                                      Defaults to 0 (zero).
     * @param   integer $level              The optional nesting level,
     *                                      initially 0.
     *                                      Defaults to 0 (zero).
     * @return  boolean                     True on success, false otherwise.
     * @static
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function buildTreeArrayRecursive(
        $flagFull=false, $active=true, $flagVirtual=true,
        $selected_id=0, $parent_id=0, $maxlevel=0, $level=0
    ) {
        // Get the ShopCategories's children
        $arrShopCategory =
            ShopCategories::getChildCategoriesById(
                $parent_id, $active, $flagVirtual
            );
        // Has there been an error?
        if ($arrShopCategory === false) return false;
        foreach ($arrShopCategory as $objCategory) {
            $id = $objCategory->getId();
            $index = count(self::$arrShopCategory);
            self::$arrShopCategory[$index] = array(
                'id'          => $id,
                'name'        => $objCategory->getName(),
                'description' => $objCategory->getDescription(),
                'parent_id'   => $objCategory->getParentId(),
                'ord'         => $objCategory->getOrd(),
                'active'      => $objCategory->getActive(),
                'picture'     => $objCategory->getPicture(),
                'flags'       => $objCategory->getFlags(),
                'virtual'     => $objCategory->isVirtual(),
                'level'       => $level,
            );
            self::$arrShopCategoryIndex[$id] = $index;
            // Get the grandchildren if
            // - the maximum depth has not been exceeded and
            // - the full list has been requested, or the current ShopCategory
            //   is an ancestor of the selected one or the selected itself.
            if (   ($maxlevel == 0 || $level < $maxlevel)
                && ($flagFull || in_array($id, self::$arrTrail))
                && (!$objCategory->isVirtual() || $flagVirtual)) {
                self::buildTreeArrayRecursive(
                    $flagFull, $active, $flagVirtual,
                    $selected_id, $id, $maxlevel, $level+1
                );
            }
        }
        return true;
    }


    /**
     * Returns a string listing all ShopCategory IDs contained within the
     * subtree starting with the ShopCategory with ID $parent_id.
     *
     * This string is used to limit the range of Product searches.
     * The IDs are comma separated, ready to be used in an SQL query.
     * @version 1.1
     * @param   integer $parent_id          The optional root ShopCategories ID.
     *                                      Defaults to 0 (zero).
     * @param   boolean $active             Only return ShopCategories
     *                                      with active == true if true.
     *                                      Defaults to true.
     * @return  string                      The ShopCategory ID list
     *                                      on success, false otherwise.
     * @global  ADONewConnection  $objDatabase    Database connection object
     * @static
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getSearchCategoryIdString($parent_id=0, $active=true)
    {
        global $objDatabase;

        $strIdList = '';
        $tempList = $parent_id;
        while (1) {
            // Get the ShopCategories' children
            $query = "
               SELECT `id`
                 FROM `".DBPREFIX."module_shop".MODULE_INDEX."_categories`
                WHERE `parent_id` IN ($tempList)".
                ($active ? ' AND `active`=1' : '')."
                ORDER BY `ord` ASC";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) {
                return ShopCategory::errorHandler();
            }
            $strIdList .= ($strIdList ? ',' : '').$tempList;
            if ($objResult->EOF) {
                return $strIdList;
            }
            $tempList = '';
            while (!$objResult->EOF) {
                $tempList .=
                    ($tempList ? ',' : '').
                    $objResult->fields['id'];
                $objResult->MoveNext();
            }
        }
    }


    /**
     * Returns the ShopCategories ID trail array.
     *
     * See {@link ShopCategories::getTrailArray()} for details on
     * the array structure.
     * @version 2.1.0
     * @param   integer $selected_id        The selected ShopCategory ID.
     * @return  mixed                       The array of ShopCategory IDs
     *                                      on success, false on failure.
     * @static
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getTrailArray($selected_id=0)
    {
        // Return the same array if it's already been initialized
        if (is_array(self::$arrTrail)) return self::$arrTrail;
        // Otherwise, initialize it now
        if (!self::buildTrailArray($selected_id)) return false;
        return self::$arrShopCategory;
    }


    /**
     * Build the ShopCategories ID trail array.
     *
     * Sets up an array of ShopCategories IDs of the $shopCategoryId,
     * and all its ancestors.
     * @param   integer   $shopCategoryId   The ShopCategories ID
     * @return  mixed                       The array of all ancestor
     *                                      ShopCategories on success,
     *                                      false otherwise.
     * @static
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function buildTrailArray($shopCategoryId)
    {
        self::$arrTrail = array($shopCategoryId);
        while ($shopCategoryId != 0) {
            $objCategory = ShopCategory::getById($shopCategoryId, FRONTEND_LANG_ID);
            if (!$objCategory) {
                // Probably du to an illegal or unknown ID.
                // Use a dummy array so the work can go on anyway.
                self::$arrTrail = array(0, $shopCategoryId);
                return false;
            }
            $shopCategoryId = $objCategory->getParentId();
            self::$arrTrail[] = $shopCategoryId;
        }
        self::$arrTrail = array_reverse(self::$arrTrail);
        return true;
    }


    /**
     * Invalidate the current state of the arrShopCategory array and its
     * index.
     *
     * Do this after changing the database tables or in order to get
     * a different subset of the Shop Categories the next time
     * {@link ShopCategories::getTreeArray()} is called.
     * @static
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function invalidateTreeArray()
    {
        self::$arrShopCategory = false;
        self::$arrShopCategoryIndex = false;
    }


    /**
     * Returns the number of elements in the ShopCategory array of this object.
     *
     * If the array has not been initialized before, boolean false is returned.
     * @return  mixed                       The element count, or false.
     * @static
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getTreeNodeCount()
    {
        if (!is_array(self::$arrShopCategory)) return false;
        return count(self::$arrShopCategory);
    }


    /**
     * Returns the array of ShopCategory data for the given ID.
     *
     * If the ShopCategory array is not initialized, or if an invalid ID
     * is provided, returns boolean false.
     * @param   integer     $id         The ShopCategory ID
     * @return  mixed                   The ShopCategory data array, or false.
     * @static
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getArrayById($id)
    {
        if (!isset(self::$arrShopCategoryIndex[$id])) return false;
        $index = self::$arrShopCategoryIndex[$id];
        return self::$arrShopCategory[$index];
    }


    /**
     * Delete all ShopCategories from the database.
     *
     * Also removes associated subcategories and Products.
     * Images will only be erased from the disc if the optional
     * $flagDeleteImages parameter evaluates to true.
     * @return  boolean         True on success, false otherwise
     * @todo    Adopt this to using the $arrCategories object variable
     * @static
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function deleteAll($flagDeleteImages=false)
    {
        $arrChildCategoryId = self::getChildCategoryIdArray(0, false);
        foreach ($arrChildCategoryId as $id) {
            $objCategory = ShopCategory::getById($id, FRONTEND_LANG_ID);
            // delete siblings and Products as well; delete images if desired.
// TODO: Add deleteById() method
            if (!$objCategory->delete($flagDeleteImages)) return false;
        }
        return true;
    }


    /**
     * Returns the best match for the Category Image
     *
     * If there is an image specified for the Category itself, its name
     * is returned.  Otherwise, the Products in the Category are searched
     * for a valid image, and if one can be found, its name is returned.
     * If neither can be found, the same process is repeated with all
     * subcategories.
     * If no image could be found at all, returns the empty string.
     * @param   integer $catId          The ShopCategory to search
     * @param   boolean $active         Only consider active Categories if true
     * @return  string                  The product thumbnail path on success,
     *                                  the empty string otherwise.
     * @global  ADONewConnection
     * @static
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getPictureById($catId=0, $active=true)
    {
        global $objDatabase;

        // Look for an image in child Categories
        $query = "
            SELECT `picture`, `id`
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_categories`
             WHERE `parent_id`=$catId
               AND `picture`!=''
          ORDER BY `ord` ASC";
        $objResult = $objDatabase->Execute($query);
        if ($objResult && $objResult->RecordCount() > 0) {
            // Got a picture
            $imageName = $objResult->fields['picture'];
            return $imageName;
        }
        // Otherwise, look for images in Products within the children
        $arrChildCategoryId =
            self::getChildCategoryIdArray($catId, $active);
        foreach ($arrChildCategoryId as $catId) {
            $imageName = Products::getPictureByCategoryId($catId);
            if ($imageName) return $imageName;
        }

        // No picture there either, try the subcategories
        foreach ($arrChildCategoryId as $catId) {
            $imageName = self::getPictureById($catId);
            if ($imageName) return $imageName;
        }
        // No more subcategories, no picture -- give up
        return '';
    }


    /**
     * Returns an array of children of the ShopCategories
     * with ID $parent_id.
     *
     * Note that for virtual ShopCategories, this will include their children.
     * @param   integer $parent_id          The parent ShopCategories ID
     * @param   boolean $active             Only return ShopCategories with
     *                                      active==1 if true.
     *                                      Defaults to false.
     * @return  array                       An array of ShopCategories objects
     *                                      on success, false on failure.
     * @static
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getChildCategoriesById(
        $parent_id=0,
        $active=true, $flagVirtual=true
    ) {
        $arrChildShopCategoriesId =
            ShopCategories::getChildCategoryIdArray(
                $parent_id, $active, $flagVirtual
            );
        if (!is_array($arrChildShopCategoriesId)) return false;
        $arrShopCategories = array();
        foreach ($arrChildShopCategoriesId as $id) {
            $objCategory = ShopCategory::getById($id);
            if (!$objCategory) continue;
            $arrShopCategories[$id] = $objCategory;
        }
        return $arrShopCategories;
    }


    /**
     * Returns the ShopCategory array for the given ID
     * @param   integer   $id       The ShopCategory ID
     * @return  array               The ShopCategory array
     * @static
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getCategoryById($id)
    {
        $index = self::$arrShopCategoryIndex[$id];
        return self::$arrShopCategory[$index];
    }


    /**
     * Select an array of ShopCategories matching the wildcards
     * from the database.
     *
     * Uses the values of $objCategory  as pattern for the match.
     * Empty values will be ignored.  Tests for identity of the fields,
     * except with the name (pattern match) and the flags (matching records
     * must contain (at least) all of the flags present in the pattern).
     * @return  array                   Array of the resulting
     *                                  Shop Category objects
     * @global  ADONewConnection  $objDatabase    Database connection object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getByWildcard($objCategory)
    {
        global $objDatabase;

        $arrSqlName = Text::getSqlSnippets(
            '`category`.`text_name_id`', FRONTEND_LANG_ID,
            MODULE_ID, self::TEXT_NAME
        );
        $arrSqlDescription = Text::getSqlSnippets(
            '`category`.`text_description_id`', FRONTEND_LANG_ID,
            MODULE_ID, self::TEXT_DESCRIPTION
        );
        $query = "
            SELECT `category`.`id`
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_categories` AS `category`".
                   $arrSqlName['join'].
                   $arrSqlDescription['join']."
             WHERE 1 ".
        (!empty($objCategory->id)
            ? " AND id=$objCategory->id" : '').
        (!empty($objCategory->name)
            ? " AND ".$arrSqlName['text'].
              " LIKE '%".addslashes($objCategory->name)."%'"
            : '').
        (!empty($objCategory->description)
            ? " AND ".$arrSqlDescription['text'].
              " LIKE '%".addslashes($objCategory->description)."%'"
            : '').
        (!empty($objCategory->parent_id)
            ? " AND parentid=$objCategory->parent_id" : '').
// TODO: This implementation does not allow any value other than boolean values
// true or false.  As false is considered to be empty, this won't work in that
// case.  We better ignore the active status for the time being.
//        (!empty($objCategory->active)   ? " AND active=$objCategory->active" : '').
        (!empty($objCategory->ord)
            ? " AND ord=$objCategory->ord" : '').
        (!empty($objCategory->picture)
            ? " AND picture LIKE '%".addslashes($objCategory->picture)."%'" : '');
        foreach (split(' ', $objCategory->flags) as $flag) {
            $query .= " AND flags LIKE '%$flag%'";
        }
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        $arrCategories = array();
        while (!$objResult->EOF) {
            $objCategory =
                ShopCategory::getById($objResult->fields['id']);
            if (!$objCategory) continue;
            $arrCategories[] = $objCategory;
            $objResult->MoveNext();
        }
        return $arrCategories;
   }


    /**
     * Returns the HTML code for a dropdown menu listing all ShopCategories.
     *
     * The <select> tag pair
     * with the menu name will be included, plus an option for the root
     * ShopCategory.
     * @global  array
     * @param   integer     $selected_id    The selected ShopCategories ID
     * @param   string      $name           The optional menu name,
     *                                      defaults to 'catId'.
     * @return  string                      The HTML dropdown menu code
     * @static
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getMenu(
        $selected_id=0, $name='catId', $active=true
    ) {
        global $_ARRAYLANG;

        return
            "<select name='$name'>".
            "<option value='0'>{$_ARRAYLANG['TXT_ALL_PRODUCT_GROUPS']}</option>".
            self::getMenuoptions($selected_id, $active).
            "</select>";
    }


    /**
     * Returns the HTML code for a dropdown menu listing all ShopCategories.
     *
     * The <select> tag pair is not included, nor the option for the root
     * ShopCategory.
     * @version 1.0     initial version
     * @param   integer $selected_id    The optional selected ShopCategories ID.
     * @param   boolean $active         If true, only active ShopCategories
     *                                  are included, all otherwise.
     * @param   integer $maxlevel       The maximum nesting level,
     *                                  defaults to 0 (zero), meaning all.
     * @param   boolean $include_all    Include an option for "all" categories
     *                                  if true.  Defaults to false
     * @return  string                  The HTML code with all <option> tags,
     *                                  or the empty string on failure.
     * @static
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getMenuoptions(
        $selected_id=0, $active=true, $maxlevel=0, $include_all=false
    ) {
        global $_ARRAYLANG;

// TODO: Implement this in a way so that both the Shopnavbar and the Shopmenu
// can be set up using only one call to buildTreeArray().
// Unfortunately, the set of records used is not identical in both cases.
//        if (!self::$arrShopCategory) {
        self::buildTreeArray(
            true, $active, true, $selected_id, 0, $maxlevel
        );
//        }

        // Check whether the ShopCategory with the selected ID is missing
        // in the index (and thus in the tree as well)
        $trailIndex = count(self::$arrTrail);
        while (   $selected_id > 0
               && $trailIndex > 0
               && !isset(self::$arrShopCategoryIndex[$selected_id])
        ) {
            // So we choose its highest level ancestor present.
            $selected_id = self::$arrTrail[--$trailIndex];
        }
        $strMenu =
            ($include_all
              ? '<option value="0">'.
                $_ARRAYLANG['TXT_SHOP_CATEGORY_ALL'].
                '</option>'
              : '');
        foreach (self::$arrShopCategory as $arrCategory) {
            $level = $arrCategory['level'];
            $id    = $arrCategory['id'];
            $name  = $arrCategory['name'];
            $strMenu .=
                "<option value='$id'".
                // I dunno why, but the comparison "$selected_id == $id"
                // fails for some reason here.
                // A little arithmetic solves that, however.
                ($selected_id-$id ? '' : ' selected="selected"').'>'.
                str_repeat('...', $level).
// TODO: This used to fail sometimes when UTF8 was used.
// Should be thoroughly tested.
// Alternative: $name
                (empty($name)
                  ? '&nbsp;'
                  : htmlentities($name, ENT_QUOTES, CONTREXX_CHARSET)).
                "</option>\n";
        }
        return $strMenu;
    }


    /**
     * Returns an array of IDs of children of this ShopCategory.
     *
     * Note that this includes virtual children of ShopCategories,
     * if applicable.
     * @param   integer $parent_id          The parent Shop Category ID.
     * @param   boolean $active             Only return ShopCategories with
     *                                      active 1 if true.
     *                                      Defaults to false.
     * @return  array                       An array of ShopCategory IDs
     *                                      on success, false otherwise.
     * @global  ADONewConnection    $objDatabase  Database connection
     * @static
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getChildCategoryIdArray($parent_id=0, $active=true)
    {
        global $objDatabase;

        $query = "
           SELECT `id`
             FROM `".DBPREFIX."module_shop".MODULE_INDEX."_categories`
            WHERE `parent_id`=$parent_id".
            ($active ? ' AND `active`=1' : '')."
            ORDER BY `ord` ASC";
        // Query flags: OR flags LIKE '%parent:$parent_id%'
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return ShopCategory::errorHandler();
        $arrChildShopCategoryId = array();
        while (!$objResult->EOF) {
            $arrChildShopCategoryId[] = $objResult->fields['id'];
            $objResult->MoveNext();
        }
        return $arrChildShopCategoryId;
    }


    /**
     * Returns the ShopCategory with the given parent ID and the given name,
     * if found.
     *
     * Returns false if the query fails, or if more than one child Category of
     * that name is be found.
     * If no such Category is encountered, returns null.
     * @param   string      $strName        The root ShopCategory name
     * @param   integer     $parent_id      The parent ShopCategory Id,
     *                                      may be 0 (zero) to search the roots.
     *                                      Ignored if null.
     * @param   boolean     $active         If true, only active ShopCategories
     *                                      are considered.
     * @return  mixed                       The ShopCategory on success,
     *                                      null if none found,
     *                                      false otherwise.
     * @static
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getChildNamed($strName, $parent_id=null, $active=true)
    {
        global $objDatabase;

        $arrSqlName = Text::getSqlSnippets(
            '`category`.`text_name_id`', FRONTEND_LANG_ID,
            MODULE_ID, self::TEXT_NAME);
        $query = "
           SELECT `id`
             FROM `".DBPREFIX."module_shop".MODULE_INDEX."_categories` AS `category`".
                  $arrSqlName['join']."
            WHERE ".$arrSqlName['text']."='".addslashes($strName)."'".
            ($active ? ' AND `active`=1' : '').
            (is_null($parent_id) ? '' : ' AND `parent_id`=$parent_id')."
            ORDER BY `ord` ASC";
        // Qquery flags: OR flags LIKE '%parent:$parent_id%'
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return ShopCategory::errorHandler();
        if ($objResult->RecordCount() > 1) return false;
        if ($objResult->EOF) return null;
        return ShopCategory::getById($objResult->fields['id']);
    }


    /**
     * Returns the parent category ID of the ShopCategory specified by its ID,
     *
     * If the ID given corresponds to a top level category,
     * 0 (zero) is returned, as there is no parent.
     * If the ID cannot be found, boolean false is returned.
     * @param   integer $shopCategoryId The ShopCategory ID
     * @return  mixed                   The parent category ID,
     *                                  or boolean false on failure.
     * @static
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getParentCategoryId($shopCategoryId)
    {
        $arrShopCategory = self::getArrayById($shopCategoryId);
        if (!$arrShopCategory) return false;
        return $arrShopCategory['parent_id'];
    }


    /**
     * Returns an array with the names of all ShopCategories marked as virtual.
     *
     * Note that the names are ordered according to the sorting order field.
     * @return  array               The array of virtual ShopCategory names
     * @static
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getVirtualCategoryNameArray()
    {
        global $objDatabase;

        $arrSqlName = Text::getSqlSnippets(
            'text_name_id', FRONTEND_LANG_ID,
            MODULE_ID, ShopCategory::TEXT_NAME);
        $query = "
           SELECT `category`.`id`".
                  $arrSqlName['field']."
             FROM `".DBPREFIX."module_shop".MODULE_INDEX."_categories` AS `category`".
                  $arrSqlName['join']."
            WHERE flags LIKE '%__VIRTUAL__%'
            ORDER BY ord ASC";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        $arrVirtual = array();
        while (!$objResult->EOF) {
            $id = $objResult->fields['id'];
            $text_name_id = $objResult->fields[$arrSqlName['name']];
            $strName = $objResult->fields[$arrSqlName['text']];
            // Replace Text in a missing language by another, if available
            if ($strName === null) {
                $objText = Text::getById($text_name_id, 0);
                if ($objText)
                    $objText->markDifferentLanguage(FRONTEND_LANG_ID);
                    $strName = $objText->getText();
            }
            $arrVirtual[$id] = $strName;
            $objResult->MoveNext();
        }
        return $arrVirtual;
    }


    /**
     * Returns an array with the IDs and names of all ShopCategories marked
     * as virtual.
     *
     * The array structure is
     *  array(
     *      ID => Category name,
     *      ... more ...
     *  )
     * Note that the array elements are ordered according to the
     * ordinal value.
     * @return  array               The array of virtual ShopCategory IDs/names
     * @static
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getVirtualCategoryIdNameArray()
    {
        global $objDatabase;

        $arrSqlName = Text::getSqlSnippets(
            '`categories`.`text_name_id`', FRONTEND_LANG_ID,
            MODULE_ID, ShopCategory::TEXT_NAME);
        $query = "
            SELECT `category`.`id`".
                   $arrSqlName['field']."
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_categories AS `category`".
                   $arrSqlName['join']."
             WHERE `category`.`flags` LIKE '%__VIRTUAL__%'
             ORDER BY `category`.`ord` ASC";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        $arrVirtual = array();
        while (!$objResult->EOF) {
            $arrVirtual[$objResult->fields['id']] =
                $objResult->fields[$arrSqlName['text']];
            $objResult->MoveNext();
        }
        return $arrVirtual;
    }


    /**
     * Returns a string with HTML code to display the virtual ShopCategory
     * selection checkboxes.
     * @param   string      $strFlags       The Product Flags
     * @return  string                      The HTML checkboxes string
     * @static
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getVirtualCategoriesSelectionForFlags($strFlags)
    {
        $arrName = self::getVirtualCategoryIdNameArray();
        $arrChecked = array();
        foreach ($arrName as $id => $name) {
            if (ShopCategory::testFlag2($name, $strFlags)) $arrChecked[] = $id;
        }
        return Html::getCheckboxGroup('shopFlags',
            $arrName, $arrName, $arrChecked, false, '', '<br />');
    }


    /**
     * Create thumbnail and update the corresponding ShopCategory records
     *
     * Scans the ShopCategories with the given IDs.  If a non-empty picture
     * string with a reasonable extension is encountered, determines whether
     * the corresponding thumbnail is available and up to date or not.
     * If not, tries to load the file and to create a thumbnail.
     * Note that only single file names are supported!
     * Also note that this method returns a string with information about
     * problems that were encountered.
     * It skips records which contain no or invalid image
     * names, thumbnails that cannot be created, and records which refuse
     * to be updated!
     * The reasoning behind this is that this method is currently only called
     * from within some {@link _import()} methods.  The focus lies on importing;
     * whether or not thumbnails can be created is secondary, as the
     * process can be repeated if there is a problem.
     * @param   integer     $id         The ShopCategory ID
     * @param   integer     $maxWidth   The maximum thubnail width
     * @param   integer     $maxHeight  The maximum thubnail height
     * @param   integer     $quality    The thumbnail quality
     * @return  string                  Empty string on success, a string
     *                                  with error messages otherwise.
     * @global  array
     * @static
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function makeThumbnailById($id, $maxWidth=120, $maxHeight=80, $quality=90)
    {
/*
    Note: The size and quality parameters should be taken from the
          settings as follows:
    SettingDb::getValue('thumbnail_max_width'),
    SettingDb::getValue('thumbnail_max_height'),
    SettingDb::getValue('thumbnail_quality')
*/
        global $_ARRAYLANG;

        if ($id <= 0) {
            return sprintf($_ARRAYLANG['TXT_SHOP_INVALID_CATEGORY_ID'], $id);
        }
        $objCategory = ShopCategory::getById($id, LANGID);
        if (!$objCategory) {
            return sprintf($_ARRAYLANG['TXT_SHOP_INVALID_CATEGORY_ID'], $id);
        }
        $imageName = $objCategory->getPicture();
        $imagePath = SHOP_CATEGORY_IMAGE_PATH.'/'.$imageName;
        // Only try to create thumbs from entries that contain a
        // plain text file name (i.e. from an import)
        if (   $imageName == ''
            || !preg_match('/\.(?:jpe?g|gif|png)$/', $imageName)) {
            return sprintf(
                $_ARRAYLANG['TXT_SHOP_UNSUPPORTED_IMAGE_FORMAT'],
                $id, $imageName
            );
        }
        // If the picture is missing, skip it.
        if (!file_exists($imagePath)) {
            return sprintf(
                $_ARRAYLANG['TXT_SHOP_MISSING_CATEGORY_IMAGES'],
                $id, $imageName
            );
        }
        // If the thumbnail exists and is newer than the picture,
        // don't create it again.
        $thumb_name = ImageManager::getThumbnailFilename($imagePath);
        if (file_exists($thumb_name)
         && filemtime($thumb_name) > filemtime($imagePath)) {
            return '';
        }
        // Already included by the Shop.
        require_once ASCMS_FRAMEWORK_PATH.'/Image.class.php';
        $objImageManager = new ImageManager();
        // Create thumbnail.
        // Deleting the old thumb beforehand is integrated into
        // _createThumbWhq().
        if (!$objImageManager->_createThumbWhq(
            SHOP_CATEGORY_IMAGE_PATH,
            SHOP_CATEGORY_IMAGE_WEB_PATH,
            $imageName,
            $maxWidth, $maxHeight, $quality
        )) {
            return sprintf(
                $_ARRAYLANG['TXT_SHOP_ERROR_CREATING_CATEGORY_THUMBNAIL'],
                $id
            );
        }
        return '';
    }


    /**
     * Returns an array of Category names indexed by their respective IDs
     * @param   boolean   $activeonly   If true, only active categories are
     *                                  included in the array
     * @return  array                   The array of Category names
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getNameArray($activeonly=false)
    {
//echo("getNameArray():  Categories:<br />".var_export(self::$arrCategory, true)."<br />");
        if (empty(self::$arrCategory))
            self::buildTreeArray(true, $activeonly);
//echo("getNameArray():  Categories:<br />".var_export(self::$arrCategory, true)."<br />");
        $arrName = array();
        foreach (self::$arrCategory as $arrCategory) {
            $arrName[$arrCategory['id']] = $arrCategory['name'];
        }
        return $arrName;
    }

}

?>
