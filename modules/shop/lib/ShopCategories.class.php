<?php

/**
 * Shop Product Categories
 *
 * Various helper methods for displaying stuff
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @access      public
 * @version     $Id: 1.0.1 $
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
 * @version     $Id: 1.0.1 $
 * @package     contrexx
 * @subpackage  module_shop
 */
class ShopCategories
{
    /**
     * The Products helper object.
     * @var     Products
     */
    var $objProducts;
    /**
     * ShopCategory Tree array
     * @var     array
     * @access  private
     */
    var $arrShopCategory;
    /**
     * ShopCategory array index
     * @var     array
     * @access  private
     */
    var $arrShopCategoryIndex;
    /**
     * Virtual ShopCategory Tree array
     * @var     array
     * @access  private
     */
    var $arrShopCategoryVirtual;
    /**
     * Virtual ShopCategory array index
     * @var     array
     * @access  private
     */
    var $arrShopCategoryVirtualIndex;
    /**
     * The trail from the root (0, zero) to the selected ShopCategory.
     *
     * See {@link getTrailArray()} for details.
     * @var     array
     */
    var $arrTrail;


    /**
     * Create a new ShopCategories object(PHP4)
     * @return  ShopCategories              The ShopCategories object
     * @todo    Make this multilingual!
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function ShopCategories()
    {
        $this->__construct();
    }


    /**
     * Create a new ShopCategories object(PHP4)
     * @return  ShopCategories            The ShopCategories object
     * @todo    Make this multilingual!
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function __construct()
    {
        $this->objProducts = &new Products();
    }


    /**
     * Returns an array representing a tree of ShopCategories,
     * not including the root chosen.
     *
     * See {@link ShopCategories::getTreeArray()} for a detailed explanation
     * of the array structure.
     * @version 1.1
     * @param   boolean $flagFull           If true, the full tree is built,
     *                                      only the parts visible for
     *                                      $selectedId otherwise.
     *                                      Defaults to false.
     * @param   boolean $flagActiveOnly     Only return ShopCategories
     *                                      with status == true if true.
     *                                      Defaults to false.
     * @param   boolean $flagVirtual        If true, also returns the virtual
     *                                      content of ShopCategories marked
     *                                      as virtual.  Defaults to false.
     * @param   integer $selectedId         The optional selected ShopCategory
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
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getTreeArray(
        $flagFull=false, $flagActiveOnly=true, $flagVirtual=true,
        $selectedId=0, $parentCategoryId=0, $maxlevel=0
    ) {
        // Return the same array if it's already been initialized
        if (is_array($this->arrShopCategory)) {
            return $this->arrShopCategory;
        }
        // Otherwise, initialize it now
        if ($this->buildTreeArray(
            $flagFull, $flagActiveOnly, $flagVirtual,
            $selectedId, $parentCategoryId, $maxlevel
        )) {
            return $this->arrShopCategory;
        }
        // It failed, probably due to a value of $selectedId that doesn't
        // exist.  Retry without it.
echo("$selectedId<br />");
        if ($selectedId > 0) {
            return $this->buildTreeArray(
                $flagFull, $flagActiveOnly, $flagVirtual,
                0, $parentCategoryId, $maxlevel
            );
        }
        // If that doesn't help...
        return false;
    }


    /**
     * Returns an array representing the index for the tree of ShopCategories
     * {@link $arrShopCategory).
     *
     * See {@link ShopCategories::buildTreeArray()} for a detailed explanation
     * of the array structure.
     * Note that you *MUST* call either {@link ShopCategories::getTreeArray()}
     * or {@link ShopCategories::buildTreeArray()} in order for the index
     * to be initialized.
     * @version 1.1
     * @return  mixed                       The ShopCategoriy index array on
     *                                      success, false on failure.
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getTreeIndexArray()
    {
        // Return the same array if it's already been initialized
        if (is_array($this->arrShopCategoryIndex)) {
            return $this->arrShopCategoryIndex;
        }
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
     * @version 1.1
     * @param   boolean $flagFull           If true, the full tree is built,
     *                                      only the parts visible for
     *                                      $selectedId otherwise.
     *                                      Defaults to false.
     * @param   boolean $flagActiveOnly     Only return ShopCategories
     *                                      with status == true if true.
     *                                      Defaults to false.
     * @param   boolean $flagVirtual        If true, also returns the virtual
     *                                      content of ShopCategories marked
     *                                      as virtual.  Defaults to false.
     * @param   integer $selectedId         The optional selected ShopCategory
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
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function buildTreeArray(
        $flagFull=false, $flagActiveOnly=true, $flagVirtual=true,
        $selectedId=0, $parentCategoryId=0, $maxlevel=0
    ) {
        $this->arrShopCategory = array();
        $this->arrShopCategoryIndex = array();

        // Set up the trail from the root (0, zero) to the selected ShopCategory
        if (!$this->buildTrailArray($selectedId)) {
            return false;
        }

        if (!$this->buildTreeArrayRecursive(
            $flagFull, $flagActiveOnly, $flagVirtual,
            $selectedId, $parentCategoryId, $maxlevel
        )) {
            return false;
        }
        return true;
    }


    /**
     * Recursively builds the $arrShopCategory array as returned by
     * {@link ShopCategories::getTreeArray()}.
     *
     * See {@link buildTreeArray()} for details.
     * @version 1.1
     * @param   boolean $flagFull           If true, the full tree is built,
     *                                      only the parts visible for
     *                                      $selectedId otherwise.
     *                                      Defaults to false.
     * @param   boolean $flagActiveOnly     Only return ShopCategories
     *                                      with status == true if true.
     *                                      Defaults to false.
     * @param   boolean $flagVirtual        If true, also returns the virtual
     *                                      content of ShopCategories marked
     *                                      as virtual.  Defaults to false.
     * @param   integer $selectedId         The optional selected ShopCategory
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
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function buildTreeArrayRecursive(
        $flagFull=false, $flagActiveOnly=true, $flagVirtual=true,
        $selectedId=0, $parentCategoryId=0, $maxlevel=0, $level=0
    ) {
        // Get the ShopCategories's children
        $arrShopCategory =
            ShopCategories::getChildCategoriesById(
                $parentCategoryId, $flagActiveOnly, $flagVirtual
            );
        // Has there been an error?
        if ($arrShopCategory === false) {
            return false;
        }
        foreach ($arrShopCategory as $objShopCategory) {
            $id = $objShopCategory->id;
            $index = count($this->arrShopCategory);
            $this->arrShopCategory[$index] = array(
                'id'       => $id,
                'name'     => $objShopCategory->name,
                'parentId' => $objShopCategory->parentId,
                'sorting'  => $objShopCategory->sorting,
                'status'   => $objShopCategory->status,
                'picture'  => $objShopCategory->picture,
                'flags'    => $objShopCategory->flags,
                'virtual'  => $objShopCategory->isVirtual(),
                'level'    => $level,
            );
            $this->arrShopCategoryIndex[$id] = $index;
            // Get the grandchildren if
            // - the maximum depth has not been exceeded and
            // - the full list has been requested, or the current ShopCategory
            //   is an ancestor of the selected one or the selected itself.
            if (($maxlevel == 0 || $level < $maxlevel)
             && ($flagFull || in_array($id, $this->arrTrail))
             && (!$objShopCategory->isVirtual() || $flagVirtual)) {
                $this->buildTreeArrayRecursive(
                    $flagFull, $flagActiveOnly, $flagVirtual,
                    $selectedId, $id, $maxlevel, $level+1
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
     * @global  mixed   $objDatabase        Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getSearchCategoryIdString(
        $parentCategoryId=0, $flagActiveOnly=true
    ) {
        global $objDatabase;

        $strIdList = '';
        $tempList = $parentCategoryId;
        while (1) {
            // Get the ShopCategories' children
            $query = "
               SELECT catid
                 FROM ".DBPREFIX."module_shop_categories
                WHERE ".($flagActiveOnly ? 'catstatus=1 AND' : '')."
                      parentid IN ($tempList)
             ORDER BY catsorting ASC
            ";
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
                $objResult->MoveNext();
            }
        }
    }


    /**
     * Returns the ShopCategories ID trail array.
     *
     * See {@link ShopCategories::getTrailArray()} for details on
     * the array structure.
     * @version 1.1
     * @param   integer $selectedId         The selected ShopCategory ID.
     * @return  mixed                       The array of ShopCategory IDs
     *                                      on success, false on failure.
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getTrailArray($selectedId=0)
    {
        // Return the same array if it's already been initialized
        if (is_array($this->arrTrail)) {
            return $this->arrTrail;
        }
        // Otherwise, initialize it now
        if (!$this->buildTrailArray($selectedId)) {
            return false;
        }
        return $this->arrShopCategory;
    }


    /**
     * Build the ShopCategories ID trail array.
     *
     * Sets up an array of ShopCategories IDs of the $shopCategoryId,
     * and all its ancestors.
     * @param   integer  $shopCategoryId    The ShopCategories ID
     * @return  mixed                       The array of all ancestor
     *                                      ShopCategories on success,
     *                                      false otherwise.
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    // was: _makeArrCurrentCategories
    function buildTrailArray($shopCategoryId)
    {
        $this->arrTrail = array($shopCategoryId);
        while ($shopCategoryId != 0) {
            $objShopCategory = ShopCategory::getById($shopCategoryId);
            if (!$objShopCategory) {
                // Probably du to an illegal or unknown ID.
                // Use a dummy array so the work can go on anyway.
                $this->arrTrail = array(0, $shopCategoryId);
                return false;
            } else {
                $shopCategoryId = $objShopCategory->getParentId();
                $this->arrTrail[] = $shopCategoryId;
            }
        }
        $this->arrTrail = array_reverse($this->arrTrail);
        return true;
    }


    /**
     * Invalidate the current state of the arrShopCategory array and its
     * index.
     *
     * Do this after changing the database tables or in order to get
     * a different subset of the Shop Categories the next time
     * {@link ShopCategories::getTreeArray()} is called.
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function invalidateTreeArray()
    {
        $this->arrShopCategory = false;
        $this->arrShopCategoryIndex = false;
    }


    /**
     * Returns the number of elements in the ShopCategory array of this object.
     *
     * If the array has not been initialized before, boolean false is returned.
     * @return  mixed                       The element count, or false.
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getTreeNodeCount()
    {
        if (!is_array($this->arrShopCategory)) {
            return false;
        }
        return count($this->arrShopCategory);
    }


    /**
     * Returns the array of ShopCategory data for the given ID.
     *
     * If the ShopCategory array is not initialized, or if an invalid ID
     * is provided, returns boolean false.
     * @param   integer     $id         The ShopCategory ID
     * @return  mixed                   The ShopCategory data array, or false.
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getArrayById($id)
    {
        if (!isset($this->arrShopCategoryIndex[$id])) {
            return false;
        }
        $index = $this->arrShopCategoryIndex[$id];
        return $this->arrShopCategory[$index];
    }


    /**
     * Delete all ShopCategories from the database.
     *
     * Also removes associated subcategories and Products.
     * Images will only be erased from the disc if the optional
     * $flagDeleteImages parameter evaluates to true.
     * @static
     * @return  boolean         True on success, false otherwise
     * @todo    Adopt this to using the $arrCategories object variable
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function deleteAll($flagDeleteImages=false)
    {

        $arrChildCategories = ShopCategories::getChildCategoryIdArray(0, false);
        foreach ($arrChildCategories as $id) {
            $objShopCategories = ShopCategory::getById($id);
            // delete Product records, delete images if desired.
            if (!Products::deleteByShopCategory($objShopCategories->id, $flagDeleteImages)) {
                return false;
            }
            if (!$objShopCategories->delete($flagDeleteImages)) {
                return false;
            }
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
     * @static
     * @param   integer $catId          The ShopCategory to search
     * @param   boolean $flagActiveOnly Only consider active Categories if true
     * @return  string                  The product thumbnail path on success,
     *                                  the empty string otherwise.
     * @global  mixed   $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function getPictureById($catId=0, $flagActiveOnly=true)
    {
        global $objDatabase;

        // Look for an image in child Categories
        $query = "
            SELECT picture, catid
              FROM ".DBPREFIX."module_shop_categories
             WHERE parentid=$catId
               AND picture!=''
          ORDER BY catsorting ASC
        ";
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
            if ($imageName) {
                return $imageName;
            }
        }

        // No picture there either, try the subcategories
        foreach ($arrChildCategoryId as $catId) {
            $imageName = ShopCategories::getPictureById($catId);
            if ($imageName) {
                return $imageName;
            }
        }
        // No more subcategories, no picture -- give up
        return '';
    }


    /**
     * Returns an array of children of the ShopCategories
     * with ID $parentCategoryId.
     *
     * Note that for virtual ShopCategories, this will include their children.
     * @static
     * @param   integer $parentCategoryId   The parent ShopCategories ID
     * @param   boolean $flagActiveOnly     Only return ShopCategories with
     *                                      status==1 if true.
     *                                      Defaults to false.
     * @return  array                       An array of ShopCategories objects
     *                                      on success, false on failure.
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function getChildCategoriesById(
        $parentCategoryId=0, $flagActiveOnly=true, $flagVirtual=true
    ) {
        global $objDatabase;

        $arrChildShopCategoriesId =
            ShopCategories::getChildCategoryIdArray(
                $parentCategoryId, $flagActiveOnly, $flagVirtual
            );
        if (!is_array($arrChildShopCategoriesId)) {
            return false;
        }
        $arrShopCategories = array();
        foreach ($arrChildShopCategoriesId as $id) {
            $arrShopCategories[] =
                ShopCategory::getById($id);
        }
        return $arrShopCategories;
    }


    /**
     * Returns the ShopCategory array with ID $
     *
     * @param unknown_type $id
     * @return unknown
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getCategoryById($id)
    {
        $index = $this->arrShopCategoryIndex[$id];
        return $this->arrShopCategory[$index];
    }


    /**
     * Returns the HTML code for a dropdown menu listing all ShopCategories.
     *
     * The <select> tag pair
     * with the menu name will be included, plus an option for the root
     * ShopCategory.
     * @global  array       $_ARRAYLANG     Language array
     * @param   integer     $selectedid     The selected ShopCategories ID
     * @param   string      $name           The optional menu name,
     *                                      defaults to 'catId'.
     * @return  string                      The HTML dropdown menu code
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getShopCategoriesMenuNamed($selectedId=0, $name='catId')
    {
        global $_ARRAYLANG;

        $result =
            $this->getShopCategoriesMenu($selectedId);
        if ($name) {
            $result =
                "<select name='$name'>".
                "<option value='0'>{$_ARRAYLANG['TXT_ALL_PRODUCT_GROUPS']}</option>".
                "$result</select>";
        }
        return $result;
    }


    /**
     * Returns the HTML code for a dropdown menu listing all ShopCategories.
     *
     * The <select> tag pair is not included, nor the option for the root
     * ShopCategory.
     * @version 1.0     initial version
     * @param   integer $selectedid     The optional selected ShopCategories ID.
     * @param   boolean $flagActiveOnly If true, only active ShopCategories
     *                                  are included, all otherwise.
     * @param   integer $maxlevel       The maximum nesting level,
     *                                  defaults to 0 (zero), meaning all.
     * @return  string                  The HTML code with all <option> tags,
     *                                  or the empty string on failure.
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getShopCategoriesMenu(
        $selectedId=0, $flagActiveOnly=true, $maxlevel=0
    ) {
//echo("ShopCategories::getShopCategoriesMenu(): ".($flagActiveOnly ? 'true' : 'false')."<br />");
// TODO: Implement this in a way so that both the Shopnavbar and the Shopmenu
// can be set up using only one call to buildTreeArray().
// Unfortunately, the set of records used is not identical in both cases.
//        if (!$this->arrShopCategory) {
        $this->buildTreeArray(
            true, $selectedId, 0, $flagActiveOnly, 0, $maxlevel
        );
//        }

        // Check whether the ShopCategory with the selected ID is missing
        // in the index (and thus in the tree as well)
        $trailIndex = count($this->arrTrail);
        while ($selectedId > 0
            && $trailIndex > 0
            && !isset($this->arrShopCategoryIndex[$selectedId])
        ) {
            // So we choose its highest level ancestor present.
            $selectedId = $this->arrTrail[--$trailIndex];
        }
        $strMenu = '';
        foreach ($this->arrShopCategory as $arrCategory) {
            $level = $arrCategory['level'];
            $id    = $arrCategory['id'];
            $name  = $arrCategory['name'];
            $strMenu .=
                "<option value='$id'".
                ($selectedId == $id ? ' selected="selected"' : '').'>'.
                str_repeat('...', $level).
                htmlentities($name).
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
     * @static
     * @global  mixed   $objDatabase        Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function getChildCategoryIdArray(
        $parentShopCategoryId=0, $flagActiveOnly=true //, $flagVirtual=true
    ) {
        global $objDatabase;

        $query = "
           SELECT catid
             FROM ".DBPREFIX."module_shop_categories
            WHERE ".($flagActiveOnly ? 'catstatus=1 AND' : '')."
                  parentid=$parentShopCategoryId
         ORDER BY catsorting ASC
        "; // $queryFlags: OR flags LIKE '%parent:$parentShopCategoryId%'

        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        $arrChildShopCategoryId = array();
        while (!$objResult->EOF) {
            $arrChildShopCategoryId[] = $objResult->fields['catid'];
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
     * active status, if $flagActiveOnly is true), a warning will be echo()ed.
     * This is by design.
     * @static
     * @param   integer     $parentId       The parent ShopCategory Id,
     *                                      may be 0 (zero) to search the roots.
     * @param   string      $strName        The root ShopCategory name
     * @param   boolean     $flagActiveOnly If true, only active ShopCategories
     *                                      are considered.
     * @return  mixed                       The ShopCategory on success,
     *                                      false otherwise.
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function getChildNamed($parentId, $strName, $flagActiveOnly=true)
    {
        global $objDatabase;

        $query = "
           SELECT catid
             FROM ".DBPREFIX."module_shop_categories
            WHERE ".($flagActiveOnly ? 'catstatus=1 AND' : '')."
                  parentid=$parentId AND
                  catname='".addslashes($strName)."'
         ORDER BY catsorting ASC
        "; // $queryFlags: OR flags LIKE '%parent:$parentShopCategoryId%'

        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        if (!$objResult->RecordCount() > 1) {
            return false;
        }
        if (!$objResult->EOF) {
            return ShopCategory::getById($objResult->fields['catid']);
        }
        return false;
    }


    /**
     * Returns the parent category ID of the ShopCategory specified by its ID,
     *
     * If the ID given corresponds to a top level category,
     * 0 (zero) is returned, as there is no parent.
     * If the ID cannot be found, boolean false is returned.
     * @global  mixed   $objDatabase    Database object
     * @param   integer $shopCategoryId The ShopCategory ID
     * @return  mixed                   The parent category ID,
     *                                  or boolean false on failure.
     * @global  mixed   $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getParentCategoryId($shopCategoryId)
    {
        $arrShopCategory = $this->getArrayById($shopCategoryId);
        if (!$arrShopCategory) {
            return false;
        }
        return $arrShopCategory['parentId'];
    }


    /**
     * Get the next ShopCategories ID after $shopCategoryId according to
     * the sorting order.
     * @param   integer $shopCategoryId     The ShopCategories ID
     * @return  integer                     The next ShopCategories ID
     * @static
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function getNextShopCategoriesId($shopCategoryId=0)
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
     */
    function getVirtualCategoryNameArray()
    {
        global $objDatabase;

        $query = "
           SELECT catname
             FROM ".DBPREFIX."module_shop_categories
            WHERE flags LIKE '%__VIRTUAL__%'
         ORDER BY catsorting ASC
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        $arrVirtual = array();
        while (!$objResult->EOF) {
            $arrVirtual[] = $objResult->fields['catname'];
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
     *      index => array(
     *          'id'    => The ShopCategory ID
     *          'name'  => The ShopCategory name
     *      ),
     *      ... [more]
     *  )
     * Note that the array elements are ordered according to the
     * sorting order field.
     * @return  array               The array of virtual ShopCategory IDs/names
     */
    function getVirtualCategoryIdNameArray()
    {
        global $objDatabase;

        $query = "
           SELECT catid, catname
             FROM ".DBPREFIX."module_shop_categories
            WHERE flags LIKE '%__VIRTUAL__%'
         ORDER BY catsorting ASC
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        $arrVirtual = array();
        while (!$objResult->EOF) {
            $arrVirtual[] = array(
                'id'   => $objResult->fields['catid'],
                'name' => $objResult->fields['catname'],
            );
            $objResult->MoveNext();
        }
        return $arrVirtual;
    }


    /**
     * Returns a string with HTML code to display the virtual ShopCategory
     * selection checkboxes.
     * @param   string      $strFlags       The Product Flags
     * @return  string                      The HTML checkboxes string
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getVirtualCategoriesSelectionForFlags($strFlags)
    {
        $arrVirtualShopCategoryName =
            ShopCategories::getVirtualCategoryIdNameArray();

        $strSelection = '';
        foreach ($arrVirtualShopCategoryName as $arrShopCategory) {
            $id   = $arrShopCategory['id'];
            $name = $arrShopCategory['name'];
            $strSelection .=
                '<input type="checkbox" name="shopFlags['.$id.']" '.
                'value="'.$name.'" '.
                (preg_match("/$name/", $strFlags)
                    ? 'checked="checked"' : ''
                ).' />'.
                '<label for="shopFlags['.$id.']">'.$name.'</label>&nbsp;';
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
     * @return  string                  Empty string on success, a string
     *                                  with error messages otherwise.
     * @global  array       $_ARRAYLANG     Language array
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function makeThumbnailById($id)
    {
        global $_ARRAYLANG;

        if ($id <= 0) {
            return sprintf($_ARRAYLANG['TXT_SHOP_INVALID_CATEGORY_ID'], $id);
        }
        $objShopCategory = ShopCategory::getById($id);
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
        if (file_exists($imagePath.'.thumb')
         && filemtime($imagePath.'.thumb') > filemtime($imagePath)) {
            return '';
        }
        // Already included by the Shop.
        require_once ASCMS_FRAMEWORK_PATH.'/Image.class.php';
        $objImageManager = &new ImageManager();
        // Create thumbnail.
        // Deleting the old thumb beforehand is integrated into
        // _createThumbWhq().
echo("ShopCategories::makeThumbnailById(): config<br />");var_export($this->arrConfig);echo("<br />");
        if (!$objImageManager->_createThumbWhq(
            SHOP_CATEGORY_IMAGE_PATH,
            SHOP_CATEGORY_IMAGE_WEB_PATH,
            $imageName,
            $this->arrConfig['shop_thumbnail_max_width']['value'],
            $this->arrConfig['shop_thumbnail_max_height']['value'],
            $this->arrConfig['shop_thumbnail_quality']['value']
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
