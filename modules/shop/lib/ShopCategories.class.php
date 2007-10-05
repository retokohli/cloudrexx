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

define('SHOP_CATEGORY_IMAGE_PATH',      ASCMS_SHOP_IMAGES_PATH.'/category/');
define('SHOP_CATEGORY_IMAGE_WEB_PATH',  ASCMS_SHOP_IMAGES_WEB_PATH.'/category/');


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
     * Returns an array representing a tree of ShopCategories,
     * not including the root chosen.
     *
     * See {@link ShopCategories::getTreeArray()} for a detailed explanation
     * of the array structure.
     * @version 1.1
     * @param   boolean $flagFull           If true, the full tree is built,
     *                                      only the parts visible for
     *                                      $selectedId otherwise.
     * @param   integer $selectedId         The optional selected ShopCategory
     *                                      ID.  If set and greater than zero,
     *                                      only the ShopCategories needed
     *                                      to display the Shop page are
     *                                      returned.
     * @param   integer $parentCategoryId   The optional root ShopCategories ID.
     *                                      Defaults to 0 (zero).
     * @param   boolean $flagActiveOnly     Only return ShopCategories
     *                                      with status == true if true.
     *                                      Defaults to false.
     * @param   integer $maxlevel           The optional maximum nesting level.
     *                                      0 (zero) means all.
     *                                      Defaults to 0 (zero).
     * @return  mixed                       The array of ShopCategories on
     *                                      success, false on failure.
     */
    function getTreeArray(
        $flagFull=false, $selectedId=0, $parentCategoryId=0,
        $flagActiveOnly=true, $maxlevel=0)
    {
        // Return the same array if it's already been initialized
        if (is_array($this->arrShopCategory)) {
//echo("ShopCategories::getTreeArray(): INFO: Returning present array.<br />");
            return $this->arrShopCategory;
        }
        // Otherwise, initialize it now
        if ($this->buildTreeArray(
            $flagFull, $selectedId, $parentCategoryId, $flagActiveOnly, 0, $maxlevel
        )) {
//echo("ShopCategories::getTreeArray(): INFO: Made array:<br />");var_export($this->arrShopCategory);echo("<br />");die();
            return $this->arrShopCategory;
        }
        // It failed, probably due to a value of $selectedId that doesn't
        // exist.  Retry without it.
        if ($selectedId > 0) {
            return $this->getTreeArray(
                $flagFull, 0, $parentCategoryId, $flagActiveOnly, $maxlevel
            );
        }
        // If that doesn't help...
//echo("ShopCategories::getTreeArray(selectedId=$selectedId, parentCategoryId=$parentCategoryId, flagActiveOnly=$flagActiveOnly, maxlevel=$maxlevel): ERROR: Failed to initialize array for parent ShopCategory ID $parentCategoryId!<br />");
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
     *    'status'   => status flag,
     *    'picture'  => 'picture name',
     *    'flags'    => 'Category flags',
     *    'level'    => nesting level,
     * ),
     * ... more parents
     * Note that this includes the virtual ShopCategories and their children.
     * @version 1.1
     * @param   boolean $flagFull           If true, the full tree is built,
     *                                      only the parts visible for
     *                                      $selectedId otherwise.
     * @param   integer $selectedId         The optional selected ShopCategory
     *                                      ID.  If set and greater than zero,
     *                                      only the ShopCategories needed
     *                                      to display the Shop page are
     *                                      returned.
     * @param   integer $parentCategoryId   The optional root ShopCategories ID.
     *                                      Defaults to 0 (zero).
     * @param   boolean $flagActiveOnly     Only return ShopCategories
     *                                      with status == true if true.
     *                                      Defaults to false.
     * @param   integer $level              The optional nesting level,
     *                                      initially 0.
     *                                      Defaults to 0 (zero).
     * @param   integer $maxlevel           The optional maximum nesting level.
     *                                      0 (zero) means all.
     *                                      Defaults to 0 (zero).
     * @return  boolean                     True on success, false otherwise.
     */
    function buildTreeArray(
        $flagFull=false, $selectedId=0, $parentCategoryId=0,
        $flagActiveOnly=true, $level=0, $maxlevel=0
    ) {
        $this->arrShopCategory = array();
        $this->arrShopCategoryIndex = array();

        // Set up the trail from the root (0, zero) to the selected ShopCategory
        if (!$this->buildTrailArray($selectedId)) {
//echo("ShopCategories::buildTreeArray($parentCategoryId, $flagActiveOnly, $level, $maxlevel): ERROR! Failed to build trail array!<br />");
            return false;
        }
//echo("ShopCategories::buildTreeArray($parentCategoryId, $flagActiveOnly, $level, $maxlevel): INFO: Made Trail: ");var_export($this->arrTrail);echo("<br />");

        if (!$this->buildTreeArrayRecursive(
            $flagFull, $selectedId, $parentCategoryId,
            $flagActiveOnly, $level, $maxlevel
        )) {
//echo("ShopCategories::buildTreeArray($parentCategoryId, $flagActiveOnly, $level, $maxlevel): ERROR! Failed to build tree array!<br />");
            return false;
        }
//echo("ShopCategories::buildTreeArray($parentCategoryId, $flagActiveOnly, $level, $maxlevel):<br />");var_export($this->arrShopCategory);echo("<br />");
/*
        $this->arrShopCategoryVirtual = array();
        $this->arrShopCategoryVirtualIndex = array();
        $arrVirtualCategories = $this->getVirtualCategoryArray(true);
        foreach ($arrVirtualCategories as $arrVirtualCategory) {
            if (!$this->buildTreeArrayVirtual(
                $arrVirtualCategory['id'], $flagActiveOnly, $level, $maxlevel
            )) {
                return false;
            }
        }
//echo("ShopCategories::buildTreeArray($parentCategoryId, $flagActiveOnly, $level, $maxlevel):<br />");
//var_export($this->arrShopCategoryVirtual);
//echo("<br />");die();
        // Unite the two arrays
*/
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
     * @param   integer $selectedId         The optional selected ShopCategory
     *                                      ID.  If set and greater than zero,
     *                                      only the ShopCategories needed
     *                                      to display the Shop page are
     *                                      returned.
     * @param   integer $parentCategoryId   The optional root ShopCategories ID.
     *                                      Defaults to 0 (zero).
     * @param   boolean $flagActiveOnly     Only return ShopCategories
     *                                      with status == true if true.
     *                                      Defaults to false.
     * @param   integer $level              The optional nesting level,
     *                                      initially 0.
     *                                      Defaults to 0 (zero).
     * @param   integer $maxlevel           The optional maximum nesting level.
     *                                      0 (zero) means all.
     *                                      Defaults to 0 (zero).
     * @return  boolean                     True on success, false otherwise.
     */
    function buildTreeArrayRecursive(
        $flagFull=false, $selectedId=0, $parentCategoryId=0,
        $flagActiveOnly=true, $level=0, $maxlevel=0
    ) {
        // Get the ShopCategories's children
        $arrShopCategory =
            ShopCategories::getChildCategoriesById(
                $parentCategoryId, $flagActiveOnly
            );
        // Has there been an error?
        if ($arrShopCategory === false) {
//echo("ShopCategories::buildTreeArray(parentCategoryId=$parentCategoryId, flagActiveOnly=$flagActiveOnly, level=$level, maxlevel=$maxlevel): ERROR: Failed to get child Category IDs!<br />");
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
                'level'    => $level,
            );
//echo("ShopCategories::buildTreeArray(): INFO: Made array: ");var_export($this->arrShopCategory[$index]);echo("<br />");
//if ($objShopCategory->parentId) echo("ShopCategories::buildTreeArray(): INFO: ID $id, my index $index, parent index {$this->arrShopCategoryIndex[$objShopCategory->parentId]}<br />");
            $this->arrShopCategoryIndex[$id] = $index;
            // Get the grandchildren if desired
            if (($maxlevel == 0 || $level < $maxlevel)
             && ($flagFull || in_array($id, $this->arrTrail))) {
//echo("ShopCategories::buildTreeArrayRecursive(parentCategoryId=$parentCategoryId, flagActiveOnly=$flagActiveOnly, level=$level, maxlevel=$maxlevel): INFO: Level $level, recursing down for ID $id<br />");
/*
                // Skip ShopCategories marked as virtual, these are filled separately.
                if (!$objShopCategory->isVirtual()) {
*/
//echo("ShopCategories::buildTreeArray(parentCategoryId=$parentCategoryId, flagActiveOnly=$flagActiveOnly, level=$level, maxlevel=$maxlevel): INFO: Recursing down to ID $id<br />");
                    $this->buildTreeArrayRecursive(
                        $flagFull, $selectedId, $id,
                        $flagActiveOnly, $level+1, $maxlevel
                    );
//                }
            } else {
//echo("ShopCategories::buildTreeArray(): INFO: Skipping recursion for ID $id<br />");
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
//echo("getChildCategoryIdArray($parentShopCategoryId, $flagActiveOnly): Query error: $query<br />");
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
                    $objResult->Fields('catid');
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
     */
    function getTrailArray($selectedId=0)
    {
        // Return the same array if it's already been initialized
        if (is_array($this->arrTrail)) {
//echo("ShopCategories::getTrailArray(): INFO: Returning present array.<br />");
            return $this->arrTrail;
        }
        // Otherwise, initialize it now
        if (!$this->buildTrailArray($selectedId)) {
//echo("ShopCategories::getTrailArray(): ERROR: Failed to initialize array!<br />");
            return false;
        }
//echo("ShopCategories::getTrailArray(): INFO: Made array:<br />");var_export($this->arrShopCategory);echo("<br />");die();
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
     */
    function invalidateTreeArray()
    {
        $this->arrShopCategory = false;
        $this->arrShopCategoryIndex = false;
    }


    /**
     * Create a new ShopCategories object(PHP4)
     * @return  ShopCategories            The ShopCategories object
     * @todo    Make this multilingual!
     */
    function ShopCategories()
    {
        $this->__construct();
    }


    /**
     * Create a new ShopCategories object(PHP4)
     * @return  ShopCategories            The ShopCategories object
     * @todo    Make this multilingual!
     */
    function __construct()
    {
        $this->objProducts = &new Products();
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
     */
    //static
    function deleteAll($flagDeleteImages=false)
    {
//echo("Debug: ShopCategories::delete(): entered<br />");

        $arrChildCategories = ShopCategories::getChildCategoryIdArray(0, false);
        foreach ($arrChildCategories as $id) {
            $objShopCategories = ShopCategory::getById($id);
            // delete Product records, delete images if desired.
            if (!Products::deleteByShopCategory($objShopCategories->id, $flagDeleteImages)) {
//echo("ShopCategories::deleteAll(): ERROR: Failed to delete Products from ShopCategories $objShopCategories->id!<br />");
                return false;
            }
//echo("Debug: ShopCategories::delete(): deleted Products from ShopCategories $this->id<br />");
            if (!$objShopCategories->delete($flagDeleteImages)) {
//echo("ShopCategories::deleteAll(): ERROR: Failed to delete ShopCategories ID $objShopCategories->id!<br />");
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
            $imageName = stripslashes($objResult->fields['picture']);
            return 'category/'.$imageName;
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
     */
    //static
    function getChildCategoriesById($parentCategoryId=0, $flagActiveOnly=true)
    {
        global $objDatabase;

        $arrChildShopCategoriesId =
            ShopCategories::getChildCategoryIdArray(
                $parentCategoryId, $flagActiveOnly
            );
        if (!is_array($arrChildShopCategoriesId)) {
//echo("ShopCategories::getChildCategoriesById(parentCategoryId=$parentCategoryId, flagActiveOnly=$flagActiveOnly): ERROR: Failed to get child Category IDs!<br />");
            return false;
        }
        $arrShopCategories = array();
        foreach ($arrChildShopCategoriesId as $id) {
            $arrShopCategories[] =
                ShopCategory::getById($id);
        }
//echo("getChildCategoriesById(): child categories:<br />");var_export($arrShopCategories);echo("<br />");
        return $arrShopCategories;
    }


    /**
     * Returns the ShopCategory array with ID $
     *
     * @param unknown_type $id
     * @return unknown
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
     * @static
     * @param   integer $selectedid     The optional selected ShopCategories ID.
     * @param   boolean $flagActiveOnly If true, only active ShopCategories
     *                                  are included, all otherwise.
     * @param   integer $maxlevel       The maximum nesting level,
     *                                  defaults to 0 (zero), meaning all.
     * @return  string                  The HTML code with all <option> tags,
     *                                  or the empty string on failure.
     */
    //static
    function getShopCategoriesMenu(
        $selectedId=0, $flagActiveOnly=true, $maxlevel=0
    ) {
//        if (!$this->arrShopCategory) {
//echo("ShopCategories::getShopCategoriesMenu(selectedId=$selectedId, flagActiveOnly=$flagActiveOnly, maxlevel=$maxlevel): INFO: Building.<br />");
            $this->buildTreeArray(
                true, $selectedId, 0, $flagActiveOnly, 0, $maxlevel
            );
//        }
//echo("SC:<br />");var_export($this->arrShopCategory);echo("<br />");
//echo("Trail:<br />");var_export($this->arrTrail);echo("<br />");

        // Check whether the ShopCategory with the selected ID is missing
        $trailIndex = count($this->arrTrail);
        while ($selectedId
            && !isset($this->arrShopCategoryIndex[$selectedId])
            && $trailIndex > 0) {
            // So we choose its highest level ancestor present.
            $selectedId = $this->arrTrail[--$trailIndex];
//echo("ShopCategories::getShopCategoriesMenu(selectedId=$selectedId, flagActiveOnly=$flagActiveOnly, maxlevel=$maxlevel): INFO: Fixed selectedId to $selectedId (trailIndex $trailIndex) .<br />");
        }
        $strMenu = '';
        foreach ($this->arrShopCategory as $arrCategory) {
            $level = $arrCategory['level'];
            //if ($level > $maxlevel) { continue; }
            $id    = $arrCategory['id'];
            $name  = $arrCategory['name'];
            $strMenu .=
                "<option value='$id'".
                ($selectedId == $id ? ' selected="selected"' : '').'>'.
                str_repeat('...', $level).
                htmlentities($name).
                "</option>\n";
        }
//echo("ShopCategories::getShopCategoriesMenu(selectedId=$selectedId, flagActiveOnly=$flagActiveOnly, maxlevel=$maxlevel): INFO: Made '".htmlentities($strMenu)."'<br />");
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
     */
    //static
    function getChildCategoryIdArray(
        $parentShopCategoryId=0, $flagActiveOnly=true
    ) {
        global $objDatabase;

/*
        $queryFlags = '';
        if ($parentShopCategoryId > 0) {
            // Get the parent.  We need to check his flags.
            $objShopCategory = ShopCategory::getById($parentShopCategoryId);
            if (!$objShopCategory) {
//echo("getChildCategoryIdArray($parentShopCategoryId, $flagActiveOnly): ERROR: Failed to get ShopCategory for ID $parentShopCategoryId!<br />");
                return false;
            }
            $strFlags = $objShopCategory->getFlags();
            if (preg_match('/__VIRTUAL__/', $strFlags)) {
                // Get ShopCategories with the ID of that ShopCategory
                // set in their flags as parent
                $queryFlags = " OR flags LIKE '%parent:$parentShopCategoryId%'";
            }
        }
*/
        $query = "
           SELECT catid
             FROM ".DBPREFIX."module_shop_categories
            WHERE ".($flagActiveOnly ? 'catstatus=1 AND' : '')."
                  parentid=$parentShopCategoryId
         ORDER BY catsorting ASC
        "; // $queryFlags: OR flags LIKE '%parent:$parentShopCategoryId%'

        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
//echo("ShopCategories::getChildCategoryIdArray($parentShopCategoryId, $flagActiveOnly): Query error: $query<br />");
            return false;
        }
        $arrChildShopCategoryId = array();
        while (!$objResult->EOF) {
            $arrChildShopCategoryId[] = $objResult->Fields('catid');
            $objResult->MoveNext();
        }
//echo("getChildCategoryIdArray($parentShopCategoryId, $flagActiveOnly): child category ids:<br />");var_export($arrChildShopCategoryId);echo("<br />");
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
//echo("ShopCategories::getChildNamed($parentId, $strName, $flagActiveOnly): ERROR: Query failed: $query<br />");
            return false;
        }
        if (!$objResult->RecordCount() > 1) {
//echo("ShopCategories::getChildNamed($parentId, $strName, $flagActiveOnly): WARNING: More than one root ShopCategory of the same name exists!<br />");
            return false;
        }
        if (!$objResult->EOF) {
            return ShopCategory::getById($objResult->Fields('catid'));
        }
        return false;
    }


    /**
     * Returns the parent category ID, or 0 (zero)
     *
     * If the ID given corresponds to a top level category,
     * 0 (zero) is returned, as there is no parent.
     * If the ID cannot be found, 0 (zero) is returned as well.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @global  mixed   $objDatabase    Database object
     * @param   integer $shopCategoryId The ShopCategory ID
     * @return  integer                 The parent category ID,
     *                                  or 0 (zero) on failure.
     * @static
     * @global  mixed   $objDatabase    Database object
     * @todo    Adopt this to using the $arrCategories object variable
     */
    //static
    function getParentCategoryId($shopCategoryId)
    {
        global $objDatabase;

        $query = '
            SELECT parentid
              FROM '.DBPREFIX."module_shop_categories
             WHERE catid=$shopCategoryId
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult || $objResult->RecordCount == 0) {
            return 0;
        }
        return $objResult->Fields('parentid');
    }


    /**
     * Get the next ShopCategories ID after $shopCategoryId according to
     * the sorting order.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @param   integer $shopCategoryId     The ShopCategories ID
     * @return  integer                     The next ShopCategories ID
     * @static
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


    function getVirtualCategoryNameArray()
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
//echo("getVirtualCategoryArray($flagActiveOnly): Query error: $query<br />");
            return false;
        }
        $arrVirtual = array();
        while (!$objResult->EOF) {
            $arrVirtual[] = array(
                'id'   => $objResult->Fields('catid'),
                'name' => $objResult->Fields('catname'),
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
     */
    function getVirtualCategoriesSelectionForFlags($strFlags)
    {
        $arrVirtualShopCategoryName =
            ShopCategories::getVirtualCategoryNameArray();

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
     * Create thumbnails and update the corresponding ShopCategory records
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
     * @param   integer     $arrId      The array of ShopCategory IDs
     * @return  string                  Empty string on success, a string
     *                                  with error messages otherwise.
     */
    function makeThumbnailsById($arrId)
    {
        require_once ASCMS_FRAMEWORK_PATH.'/Image.class.php';

        if (!is_array($arrId)) {
            //$this->addMessage("Keine Kategorie IDs zum erstellen der Thumbnails vorhanden ($id).");
            return false;
        }

        // Collect and group errors
        $arrMissingCategoryPicture = array();
        $arrFailedCreatingThumb    = array();
        $strError = '';

        $objImageManager = new ImageManager();
        foreach ($arrId as $id) {
            if ($id <= 0) {
                $strError .= ($strError ? '<br />' : '').
                    "Ungültige Kategorie ID '$id'!";
                continue;
            }
            $objShopCategory = ShopCategory::getById($id);
            if (!$objShopCategory) {
                $strError .= ($strError ? '<br />' : '').
                    "Ungültige Kategorie ID '$id' - Konnte Kategorie nicht finden!";
                continue;
            }
            $imageName = $objShopCategory->getPicture();
            $imagePath = SHOP_CATEGORY_IMAGE_PATH.'/'.$imageName;
            // only try to create thumbs from entries that contain a
            // plain text file name (i.e. from an import)
            if (   $imageName == ''
                || !preg_match('/\.(?:jpg|jpeg|gif|png)$/', $imageName)) {
                $strError .= ($strError ? '<br />' : '').
                    "Nicht unterstütztes Bildformat: '$imageName' (Kategorie ID $id)!";
                continue;
            }
            // If the picture is missing, skip it.
            if (!file_exists($imagePath)) {
                $arrMissingCategoryPicture["$id - $imageName"] = 1;
                continue;
            }
            $thumbResult = true;
            // If the thumbnail exists and is newer than the picture,
            // don't create it again.
            if (file_exists($imagePath.'.thumb')
             && filemtime($imagePath.'.thumb') > filemtime($imagePath)) {
                //$this->addMessage("Hinweis: Thumbnail für Kategorie ID '$id' existiert bereits");
            } else {
                // Create thumbnail, get the original size.
                // Deleting the old thumb beforehand is integrated into
                // _createThumbWhq().
                $thumbResult = $objImageManager->_createThumbWhq(
                    SHOP_CATEGORY_IMAGE_PATH,
                    SHOP_CATEGORY_IMAGE_WEB_PATH,
                    $imageName,
                    $this->arrConfig['shop_thumbnail_max_width']['value'],
                    $this->arrConfig['shop_thumbnail_max_height']['value'],
                    95 //$this->arrConfig['shop_thumbnail_quality']['value']
                );
            }
            // The database needs to be updated, however, as all Categories
            // have been imported.
            if (!$thumbResult) {
                $arrFailedCreatingThumb[] = $id;
            }
        }
        if (count($arrMissingCategoryPicture)) {
            ksort($arrMissingCategoryPicture);
            $strError .= ($strError ? '<br />' : '').
                "Fehlende Bilder (Kategorie ID - Bildname): ".
                join(', ', array_keys($arrMissingCategoryPicture));
        }
        if (count($arrFailedCreatingThumb)) {
            sort($arrFailedCreatingThumb);
            $strError .= ($strError ? '<br />' : '').
                "Fehler beim erzeugen des Thumbnails bei Kategorie ID: ".
                join(', ', $arrFailedCreatingThumb);
        }
//echo("$strError<br />");
        return $strError;
    }



}


/* test
//echo("TEST: getcategoryTree(): <br />");
//var_export(ShopCategories::getCategoryTree(0, 0, 0));
//echo("<br />");
//die();
*/

?>
