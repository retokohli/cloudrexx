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
     * @param   boolean $flagActiveOnly     Only return ShopCategories
     *                                      with status == true if true.
     *                                      Defaults to false.
     * @param   boolean $flagVirtual        If true, also returns the virtual
     *                                      content of ShopCategories marked
     *                                      as virtual.  Defaults to false.
     * @param   integer $selected_id        The optional selected ShopCategory
     *                                      ID.  If set and greater than zero,
     *                                      only the ShopCategories needed
     *                                      to display the Shop page are
     *                                      returned.
     * @param   integer $parentCategoryId   The optional root ShopCategories ID.
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
        $flagFull=false, $flagActiveOnly=true, $flagVirtual=true,
        $selected_id=0, $parentCategoryId=0, $maxlevel=0
    ) {
        // Return the same array if it's already been initialized
// TODO:  This won't work for the shopnavbar, as the categories menu is built first.
// This needs to be able to detect whether the arguments are the same.
//        if (is_array(self::$arrShopCategory))
//            return self::$arrShopCategory;
        // Otherwise, initialize it now
        if (self::buildTreeArray(
            $flagFull, $flagActiveOnly, $flagVirtual,
            $selected_id, $parentCategoryId, $maxlevel
        )) return self::$arrShopCategory;
        // It failed, probably due to a value of $selected_id that doesn't
        // exist.  Retry without it.
        if ($selected_id > 0)
            return self::buildTreeArray(
                $flagFull, $flagActiveOnly, $flagVirtual,
                0, $parentCategoryId, $maxlevel
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
     *    'id        => ShopCategory ID
     *    'name'     => 'Category name',
     *    'parentId' => parent ID
     *    'sorting'  => order value,
     *    'status'   => status flag (boolean),
     *    'picture'  => 'picture name',
     *    'flags'    => 'Category flags' (string),
     *    'virtual'  => virtual flag status (boolean),
     *    'level'    => nesting level,
     * ),
     * ... more parents
     * Note that this includes the virtual ShopCategories and their children.
     * @version 2.1.0
     * @param   boolean $flagFull           If true, the full tree is built,
     *                                      only the parts visible for
     *                                      $selected_id otherwise.
     *                                      Defaults to false.
     * @param   boolean $flagActiveOnly     Only return ShopCategories
     *                                      with status == true if true.
     *                                      Defaults to true.
     * @param   boolean $flagVirtual        If true, also returns the virtual
     *                                      content of ShopCategories marked
     *                                      as virtual.  Defaults to false.
     * @param   integer $selected_id        The optional selected ShopCategory
     *                                      ID.  If set and greater than zero,
     *                                      only the ShopCategories needed
     *                                      to display the Shop page are
     *                                      returned.
     * @param   integer $parentCategoryId   The optional root ShopCategories ID.
     *                                      Defaults to 0 (zero).
     * @param   integer $maxlevel           The optional maximum nesting level.
     *                                      0 (zero) means all.
     *                                      Defaults to 0 (zero).
     * @return  boolean                     True on success, false otherwise.
     * @static
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function buildTreeArray(
        $flagFull=false, $flagActiveOnly=true, $flagVirtual=true,
        $selected_id=0, $parentCategoryId=0, $maxlevel=0
    ) {
        self::$arrShopCategory = array();
        self::$arrShopCategoryIndex = array();
        // Set up the trail from the root (0, zero) to the selected ShopCategory
        if (!self::buildTrailArray($selected_id)) return false;
        if (!self::buildTreeArrayRecursive(
            $flagFull, $flagActiveOnly, $flagVirtual,
            $selected_id, $parentCategoryId, $maxlevel
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
     * @param   boolean $flagActiveOnly     Only return ShopCategories
     *                                      with status == true if true.
     *                                      Defaults to true.
     * @param   boolean $flagVirtual        If true, also returns the virtual
     *                                      content of ShopCategories marked
     *                                      as virtual.  Defaults to false.
     * @param   integer $selected_id        The optional selected ShopCategory
     *                                      ID.  If set and greater than zero,
     *                                      only the ShopCategories needed
     *                                      to display the Shop page are
     *                                      returned.
     * @param   integer $parentCategoryId   The optional root ShopCategories ID.
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
        $flagFull=false, $flagActiveOnly=true, $flagVirtual=true,
        $selected_id=0, $parentCategoryId=0, $maxlevel=0, $level=0
    ) {
        // Get the ShopCategories's children
        $arrShopCategory =
            ShopCategories::getChildCategoriesById(
                $parentCategoryId, $flagActiveOnly, $flagVirtual
            );
        // Has there been an error?
        if ($arrShopCategory === false) return false;
        foreach ($arrShopCategory as $objShopCategory) {
            $id = $objShopCategory->getId();
            $index = count(self::$arrShopCategory);
            self::$arrShopCategory[$index] = array(
                'id'       => $id,
                'name'     => $objShopCategory->getName(),
                'parentId' => $objShopCategory->getParentId(),
                'sorting'  => $objShopCategory->getSorting(),
                'status'   => $objShopCategory->getStatus(),
                'picture'  => $objShopCategory->getPicture(),
                'flags'    => $objShopCategory->getFlags(),
                'virtual'  => $objShopCategory->isVirtual(),
                'level'    => $level,
            );
            self::$arrShopCategoryIndex[$id] = $index;
            // Get the grandchildren if
            // - the maximum depth has not been exceeded and
            // - the full list has been requested, or the current ShopCategory
            //   is an ancestor of the selected one or the selected itself.
            if (   ($maxlevel == 0 || $level < $maxlevel)
                && ($flagFull || in_array($id, self::$arrTrail))
                && (!$objShopCategory->isVirtual() || $flagVirtual)) {
                self::buildTreeArrayRecursive(
                    $flagFull, $flagActiveOnly, $flagVirtual,
                    $selected_id, $id, $maxlevel, $level+1
                );
            }
        }
        return true;
    }


    /**
     * Returns a string listing all ShopCategory IDs contained within the
     * subtree starting with the ShopCategory with ID $parentCategoryId.
     *
     * This string is used to limit the range of Product searches.
     * The IDs are comma separated, ready to be used in an SQL query.
     * @version 1.1
     * @param   integer $parentCategoryId   The optional root ShopCategories ID.
     *                                      Defaults to 0 (zero).
     * @param   boolean $flagActiveOnly     Only return ShopCategories
     *                                      with status == true if true.
     *                                      Defaults to true.
     * @return  string                      The ShopCategory ID list
     *                                      on success, false otherwise.
     * @global  ADONewConnection  $objDatabase    Database connection object
     * @static
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getSearchCategoryIdString(
        $parentCategoryId=0, $flagActiveOnly=true
    ) {
        global $objDatabase;

        $strIdList = '';
        $tempList = $parentCategoryId;
        while (1) {
            // Get the ShopCategories' children
            $query = "
               SELECT catid
                 FROM ".DBPREFIX."module_shop".MODULE_INDEX."_categories
                WHERE ".($flagActiveOnly ? 'catstatus=1 AND' : '')."
                      parentid IN ($tempList)
             ORDER BY catsorting ASC
            ";
/*
TODO:  For 2.2.0
               SELECT id
                 FROM ".DBPREFIX."module_shop".MODULE_INDEX."_categories
                WHERE ".($flagActiveOnly ? 'status=1 AND' : '')."
                      parent_id IN ($tempList)
             ORDER BY sort_order ASC
*/
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) {
                return false;
            }
            $strIdList .= ($strIdList ? ',' : '').$tempList;
            if ($objResult->EOF) {
                return $strIdList;
            }
            $tempList = '';
            while (!$objResult->EOF) {
                $tempList .=
                    ($tempList ? ',' : '').
                    $objResult->fields['catid'];
// 2.2.0
//                    $objResult->fields['id'];
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
            $objShopCategory = ShopCategory::getById($shopCategoryId, FRONTEND_LANG_ID);
            if (!$objShopCategory) {
                // Probably du to an illegal or unknown ID.
                // Use a dummy array so the work can go on anyway.
                self::$arrTrail = array(0, $shopCategoryId);
                return false;
            }
            $shopCategoryId = $objShopCategory->getParentId();
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
        $arrChildCategoryId = ShopCategories::getChildCategoryIdArray(0, false);
        foreach ($arrChildCategoryId as $id) {
            $objShopCategory = ShopCategory::getById($id, FRONTEND_LANG_ID);
            // delete siblings and Products as well; delete images if desired.
// TODO: Add deleteById() method
            if (!$objShopCategory->delete($flagDeleteImages)) return false;
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
     * @param   boolean $flagActiveOnly Only consider active Categories if true
     * @return  string                  The product thumbnail path on success,
     *                                  the empty string otherwise.
     * @global  ADONewConnection
     * @static
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getPictureById($catId=0, $flagActiveOnly=true)
    {
        global $objDatabase;

        // Look for an image in child Categories
        $query = "
            SELECT picture, catid
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_categories
             WHERE parentid=$catId
               AND picture!=''
          ORDER BY catsorting ASC
        ";
/*
2.2.0
            SELECT picture, id
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_categories
             WHERE parent_id=$catId
               AND picture!=''
          ORDER BY sort_order ASC
*/
        $objResult = $objDatabase->Execute($query);
        if ($objResult && $objResult->RecordCount() > 0) {
            // Got a picture
            $imageName = $objResult->fields['picture'];
            return $imageName;
        }
        // Otherwise, look for images in Products within the children
        $arrChildCategoryId =
            ShopCategories::getChildCategoryIdArray($catId, $flagActiveOnly);
        foreach ($arrChildCategoryId as $catId) {
            $imageName = Products::getPictureByCategoryId($catId);
            if ($imageName) return $imageName;
        }

        // No picture there either, try the subcategories
        foreach ($arrChildCategoryId as $catId) {
            $imageName = ShopCategories::getPictureById($catId);
            if ($imageName) return $imageName;
        }
        // No more subcategories, no picture -- give up
        return '';
    }


    /**
     * Returns an array of children of the ShopCategories
     * with ID $parentCategoryId.
     *
     * Note that for virtual ShopCategories, this will include their children.
     * @param   integer $parentCategoryId   The parent ShopCategories ID
     * @param   boolean $flagActiveOnly     Only return ShopCategories with
     *                                      status==1 if true.
     *                                      Defaults to false.
     * @return  array                       An array of ShopCategories objects
     *                                      on success, false on failure.
     * @static
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getChildCategoriesById(
        $parentCategoryId=0,
        $flagActiveOnly=true, $flagVirtual=true
    ) {
        $arrChildShopCategoriesId =
            ShopCategories::getChildCategoryIdArray(
                $parentCategoryId, $flagActiveOnly, $flagVirtual
            );
        if (!is_array($arrChildShopCategoriesId)) return false;
        $arrShopCategories = array();
        foreach ($arrChildShopCategoriesId as $id) {
            $objShopCategory = ShopCategory::getById($id);
            if (!$objShopCategory) continue;
            $arrShopCategories[] = $objShopCategory;
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
    static function getShopCategoriesMenu(
        $selected_id=0, $name='catId', $flagActiveOnly=true
    ) {
        global $_ARRAYLANG;

        return
            "<select name='$name'>".
            "<option value='0'>{$_ARRAYLANG['TXT_ALL_PRODUCT_GROUPS']}</option>".
            self::getShopCategoriesMenuoptions($selected_id, $flagActiveOnly).
            "</select>";
    }


    /**
     * Returns the HTML code for a dropdown menu listing all ShopCategories.
     *
     * The <select> tag pair is not included, nor the option for the root
     * ShopCategory.
     * @version 1.0     initial version
     * @param   integer $selected_id    The optional selected ShopCategories ID.
     * @param   boolean $flagActiveOnly If true, only active ShopCategories
     *                                  are included, all otherwise.
     * @param   integer $maxlevel       The maximum nesting level,
     *                                  defaults to 0 (zero), meaning all.
     * @return  string                  The HTML code with all <option> tags,
     *                                  or the empty string on failure.
     * @static
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getShopCategoriesMenuoptions(
        $selected_id=0, $flagActiveOnly=true, $maxlevel=0
    ) {
// TODO: Implement this in a way so that both the Shopnavbar and the Shopmenu
// can be set up using only one call to buildTreeArray().
// Unfortunately, the set of records used is not identical in both cases.
//        if (!self::$arrShopCategory) {
        self::buildTreeArray(
            true, $flagActiveOnly, true, $selected_id, 0, $maxlevel
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
        $strMenu = '';
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
                htmlentities($name, ENT_QUOTES, CONTREXX_CHARSET).
                "</option>\n";
        }
        return $strMenu;
    }


    /**
     * Returns an array of IDs of children of this ShopCategory.
     *
     * Note that this includes virtual children of ShopCategories,
     * if applicable.
     * @param   integer $parentShopCategoryId   The parent Shop Category ID.
     * @param   boolean $flagActiveOnly     Only return ShopCategories with
     *                                      status 1 if true.
     *                                      Defaults to false.
     * @return  array                       An array of ShopCategory IDs
     *                                      on success, false otherwise.
     * @global  ADONewConnection    $objDatabase  Database connection
     * @static
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getChildCategoryIdArray(
        $parentShopCategoryId=0, $flagActiveOnly=true //, $flagVirtual=true
    ) {
        global $objDatabase;

        $query = "
           SELECT catid
             FROM `".DBPREFIX."module_shop".MODULE_INDEX."_categories`
            WHERE ".($flagActiveOnly ? 'catstatus=1 AND' : '')."
                  parentid=$parentShopCategoryId
         ORDER BY catsorting ASC
        "; // $queryFlags: OR flags LIKE '%parent:$parentShopCategoryId%'
/*
2.2.0
           SELECT `id`
             FROM `".DBPREFIX."module_shop".MODULE_INDEX."_categories`
            WHERE parent_id=$parentShopCategoryId".
                  ($flagActiveOnly ? ' AND `status`=1' : '')."
            ORDER BY sort_order ASC
        "; // $queryFlags: OR flags LIKE '%parent:$parentShopCategoryId%'
*/
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        $arrChildShopCategoryId = array();
        while (!$objResult->EOF) {
            $arrChildShopCategoryId[] = $objResult->fields['catid'];
// 2.2.0
//            $arrChildShopCategoryId[] = $objResult->fields['id'];
            $objResult->MoveNext();
        }
        return $arrChildShopCategoryId;
    }


    /**
     * Returns the ShopCategory with the given parent ID and the given name,
     * if found.
     *
     * Returns false if the query fails, or if no child ShopCategory of
     * that name can be found.
     * Note that if there are two or more children of the same name (and with
     * active status, if $flagActiveOnly is true), this will fail.
     * This is by design.
     * @param   integer     $parentId       The parent ShopCategory Id,
     *                                      may be 0 (zero) to search the roots.
     * @param   string      $strName        The root ShopCategory name
     * @param   boolean     $flagActiveOnly If true, only active ShopCategories
     *                                      are considered.
     * @return  mixed                       The ShopCategory on success,
     *                                      false otherwise.
     * @static
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getChildNamed(
        $parentId, $strName, $flagActiveOnly=true
    ) {
        global $objDatabase;

        $query = "
           SELECT catid
             FROM ".DBPREFIX."module_shop".MODULE_INDEX."_categories
            WHERE ".($flagActiveOnly ? 'catstatus=1 AND' : '')."
                  parentid=$parentId AND
                  catname='".addslashes($strName)."'
         ORDER BY catsorting ASC
        "; // $queryFlags: OR flags LIKE '%parent:$parentShopCategoryId%'
/*
2.2.0
// TODO: *MUST NOT* ignore the language IDs nested in the array!
        $strTextId = join(',', array_keys(Text::getIdArrayBySearch(
            $strName, MODULE_ID, TEXT_SHOP_CATEGORIES_NAME, FRONTEND_LANG_ID
        )));
        if (empty($strTextId)) return false;
        $query = "
           SELECT id
             FROM ".DBPREFIX."module_shop".MODULE_INDEX."_categories
            WHERE parent_id=$parentId
              AND text_name_id IN ($strTextId)".
            ($flagActiveOnly ? ' AND status=1' : '')."
            ORDER BY sort_order ASC
        "; // $queryFlags: OR flags LIKE '%parent:$parentShopCategoryId%'
*/
        $objResult = $objDatabase->Execute($query);
        if (!$objResult || $objResult->EOF || $objResult->RecordCount() > 1)
            return false;
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
        return $arrShopCategory['parentId'];
    }


    /**
     * Get the next ShopCategories ID after $shopCategoryId according to
     * the sorting order.
     * @param   integer $shopCategoryId     The ShopCategories ID
     * @return  integer                     The next ShopCategories ID
     * @static
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getNextShopCategoriesId($shopCategoryId=0)
    {
        // Get the parent ShopCategories ID
        $parentShopCategoryId =
            ShopCategories::getParentCategoryId($shopCategoryId);
        if (!$parentShopCategoryId) {
            $parentShopCategoryId = 0;
        }
        // Get the IDs of all active children
        $arrChildShopCategoriesId =
            ShopCategories::getChildCategoryIdArray($parentShopCategoryId, true);
        return
            (isset($arrChildShopCategoriesId[
                array_search($parentShopCategoryId, $arrChildShopCategoriesId)+1
             ])
                ? $arrChildShopCategoriesId[
                    array_search($parentShopCategoryId, $arrChildShopCategoriesId)+1
                  ]
                : $arrChildShopCategoriesId[0]
            );
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

        $query = "
           SELECT catname
             FROM ".DBPREFIX."module_shop".MODULE_INDEX."_categories
            WHERE flags LIKE '%__VIRTUAL__%'
         ORDER BY catsorting ASC
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        $arrVirtual = array();
        while (!$objResult->EOF) {
            $arrVirtual[] = $objResult->fields['catname'];
            $objResult->MoveNext();
        }
        return $arrVirtual;
/*
2.2.0
        $arrSqlName = Text::getSqlSnippets('text_name_id', FRONTEND_LANG_ID);
        $query = "
           SELECT ".$arrSqlName['field']."
             FROM ".DBPREFIX."module_shop".MODULE_INDEX."_categories
            ".$arrSqlName['join']."
            WHERE flags LIKE '%__VIRTUAL__%'
            ORDER BY sort_order ASC
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        $arrVirtual = array();
        while (!$objResult->EOF) {
            $text_name_id = $objResult->fields[$arrSqlName['name']];
            $strName = $objResult->fields[$arrSqlName['text']];
            // Replace Text in a missing language by another, if available
            if ($text_name_id && $strName === null) {
                $objText = Text::getById($text_name_id, 0);
                if ($objText)
                    $objText->markDifferentLanguage(FRONTEND_LANG_ID);
                    $strName = $objText->getText();
            }
            $arrVirtual[] = $strName;
            $objResult->MoveNext();
        }
        return $arrVirtual;
*/
    }


    /**
     * Returns an array with the IDs and names of all ShopCategories marked
     * as virtual.
     *
     * The array structure is
     *  array(
     *      index => array(
     *          'id'    => The ShopCategory ID
     *          'name'  => The ShopCategory name
     *      ),
     *      ... [more]
     *  )
     * Note that the array elements are ordered according to the
     * sorting order field.
     * @return  array               The array of virtual ShopCategory IDs/names
     * @static
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getVirtualCategoryIdNameArray()
    {
        global $objDatabase;

        $query = "
           SELECT catid, catname
             FROM ".DBPREFIX."module_shop".MODULE_INDEX."_categories
            WHERE flags LIKE '%__VIRTUAL__%'
         ORDER BY catsorting ASC
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        $arrVirtual = array();
        while (!$objResult->EOF) {
            $arrVirtual[] = array(
                'id'   => $objResult->fields['catid'],
                'name' => $objResult->fields['catname'],
            );
            $objResult->MoveNext();
        }
        return $arrVirtual;
/*
2.2.0
        $arrSqlName = Text::getSqlSnippets('`categories`.`text_name_id`', FRONTEND_LANG_ID);
        $query = "
            SELECT `categories`.`id`".$arrSqlName['field']."
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_categories AS `categories`".
                   $arrSqlName['join']."
             WHERE `categories`.`flags` LIKE '%__VIRTUAL__%'
             ORDER BY `categories`.`sort_order` ASC
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        $arrVirtual = array();
        while (!$objResult->EOF) {
            $arrVirtual[] = array(
                'id'   => $objResult->fields['id'],
                'name' => $objResult->fields[$arrSqlName['text']],
                'text_name_id' => $objResult->fields[$arrSqlName['name']],
            );
            $objResult->MoveNext();
        }
        return $arrVirtual;
*/
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
        $arrVirtualShopCategoryName =
            ShopCategories::getVirtualCategoryIdNameArray();
        $strSelection = '';
        foreach ($arrVirtualShopCategoryName as $arrShopCategory) {
            $id   = $arrShopCategory['id'];
            $name = $arrShopCategory['name'];
            $strSelection .=
                '<input type="checkbox" value="'.$name.'" '.
                'name="shopFlags['.$id.']" id="shopFlags_'.$id.'"'.
                (preg_match("/$name/", $strFlags)
                    ? ' checked="checked"' : ''
                ).' />'.
                '<label for="shopFlags_'.$id.'">'.$name.'</label>&nbsp;';
        }
        return $strSelection;
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
    $this->arrConfig['shop_thumbnail_max_width']['value'],
    $this->arrConfig['shop_thumbnail_max_height']['value'],
    $this->arrConfig['shop_thumbnail_quality']['value']
*/
        global $_ARRAYLANG;

        if ($id <= 0) {
            return sprintf($_ARRAYLANG['TXT_SHOP_INVALID_CATEGORY_ID'], $id);
        }
        $objShopCategory = ShopCategory::getById($id, LANGID);
        if (!$objShopCategory) {
            return sprintf($_ARRAYLANG['TXT_SHOP_INVALID_CATEGORY_ID'], $id);
        }
        $imageName = $objShopCategory->getPicture();
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

}

?>
