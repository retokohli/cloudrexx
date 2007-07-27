<?php

/**
 * Shop Product Category
 *
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @access      public
 * @version     $Id: 1.0.1 $
 * @package     contrexx
 * @subpackage  module_shop
 */

/**
 * Access to Products
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/Product.class.php';


/**
 * Container for Products in the Shop.
 *
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @access      public
 * @version     $Id: 1.0.1 $
 * @package     contrexx
 * @subpackage  module_shop
 * @todo        From time to time, do something like this:
 *              $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop_categories");
 *              $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop_products");
 */
class ShopCategory
{
    /**
     * @var     integer     $id         ShopCategory ID
     * @access  private
     */
    var $id;
    /**
     * @var     string      $name       ShopCategory name
     * @access  private
     */
    var $name;
    /**
     * @var     integer     $parentId   Parent ShopCategory ID
     * @access  private
     */
    var $parentId;
    /**
     * @var     integer     $status     Status of the ShopCategory
     * @access  private
     */
    var $status;
    /**
     * @var     integer     $sorting    Sorting order of the ShopCategory
     * @access  private
     */
    var $sorting;


    /**
     * Add or replace a ShopCategory (PHP4)
     *
     * If the optional argument $catId is set, the corresponding
     * category is updated.  Otherwise, a new category is created.
     * @access  public
     * @param   string  $catName        The new category name
     * @param   integer $catParentId    The new parent ID of the category
     * @param   integer $catStatus      The new status of the category (0 or 1)
     * @param   integer $catSorting     The sorting order
     * @param   integer $catId          The optional category ID to be updated
     * @return  ShopCategory            The ShopCategory
     */
    function ShopCategory($catName, $catParentId, $catStatus, $catSorting, $catId=0)
    {
        $this->__construct($catName, $catParentId, $catStatus, $catSorting, $catId);
    }


    /**
     * Add or replace a ShopCategory (PHP5)
     *
     * If the optional argument $catId is greater than zero, the corresponding
     * category is updated.  Otherwise, a new category is created.
     * @access  public
     * @param   string  $catName        The new category name
     * @param   integer $catParentId    The new parent ID of the category
     * @param   integer $catStatus      The new status of the category (0 or 1)
     * @param   integer $catSorting     The sorting order
     * @param   integer $catId          The optional category ID to be updated
     * @return  ShopCategory            The ShopCategory
     */
    function __construct($catName, $catParentId, $catStatus, $catSorting, $catId=0)
    {
        // warn / debug
        if (empty($catName)) {
            echo("WARNING: ShopCategory::__construct(): called with empty Category name<br />");
        }

        // Use access methods here, various checks included.
        $this->setName($catName);
        $this->setParentId($catParentId);
        $this->setStatus($catStatus);
        $this->setSorting($catSorting);
        // Assign ID
        $this->id = intval($catId);
echo("__construct(): made<br />");var_export($this);echo("<br />");
    }


    /**
     * Get the ShopCategory ID
     * @return  integer             The ShopCategory ID
     */
    function getId() {
        return $this->id;
    }
    /**
     * Set the ShopCategory ID -- NOT ALLOWED!
     */

    /**
     * Get the ShopCategory name
     * @return  string              The ShopCategory name
     */
    function getName() {
        return $this->name;
    }
    /**
     * Set the ShopCategory name
     *
     * Returns false iff the given name is empty.
     * @param   string              The ShopCategory name
     * @return  boolean             True on success, false otherwise
     */
    function setName($catName) {
/*        if (empty($newCatName)) {
            return false;
        }*/
        $this->name = trim($catName);
        return true;
    }

    /**
     * Get the parent ShopCategory ID
     * @return  integer             The parent ShopCategory ID
     */
    function getParentId() {
        return $this->parentId;
    }
    /**
     * Set the parent ShopCategory ID.
     *
     * Returns false iff the given parent ID equals the objects' ID.
     * @param   integer             The parent ShopCategory ID
     * @return  boolean             True on success, false otherwise
     */
    function setParentId($catParentId) {
        if ($catParentId == $this->id) {
            return false;
        }
        $this->parentId = $catParentId;
        return true;
    }

    /**
     * Get the ShopCategory status
     * @return  integer             The ShopCategory status
     */
    function getStatus() {
        return $this->status;
    }
    /**
     * Set the ShopCategory status
     * @param   integer             The ShopCategory status
     * @return  boolean             Boolean true. Always.
     */
    function setStatus($catStatus) {
        $this->status = ($catStatus == 0 ? 0 : 1);
        return true;
    }

    /**
     * Get the ShopCategory sorting order
     * @return  integer             The ShopCategory sorting order
     */
    function getSorting() {
        return $this->sorting;
    }
    /**
     * Set the ShopCategory sorting order
     * @param   integer             The ShopCategory sorting order
     * @return  boolean             Boolean true. Always.
     */
    function setSorting($catSorting) {
        $this->sorting = ($catSorting >= 0 ? $catSorting : 0);
    }


    /**
     * Stores the ShopCategory object in the database.
     *
     * Either updates (id > 0) or inserts (id == 0) the object.
     *
     * @return  boolean     True on success, false otherwise
     */
    function store() {
        if ($this->id > 0) {
            return ($this->update());
        }
        return ($this->insert());
    }


    /**
     * Update this ShopCategory in the database.
     * Returns the result of the query.
     *
     * @return  boolean         True on success, false otherwise
     */
    function update()
    {
        global $objDatabase;
        return $objDatabase->Execute("
            UPDATE ".DBPREFIX."module_shop_categories
            SET catname='".addslashes($this->name)."',
                parentid=$this->parentId,
                catstatus=$this->status,
                catsorting=$this->sorting
            WHERE catid=$this->id
        ");
    }


    /**
     * Insert this ShopCategory into the database.
     *
     * On success, updates the Category ID.
     * @return  boolean         True on success, false otherwise
     */
    function insert()
    {
        global $objDatabase;
        $query = "
            INSERT INTO ".DBPREFIX."module_shop_categories
            (catname, parentid, catstatus, catsorting)
            VALUES (
                '".addslashes($this->name)."',
                $this->parentId,
                $this->status,
                $this->sorting
            )";
//echo("ShopCategory::insert(): query: $query<br />");
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        $this->id = $objDatabase->Insert_ID();
//echo("ShopCategory::insert(): new ID: $this->id<br />");
        return true;
    }


    /**
     * Delete this ShopCategory from the database.
     *
     * Also removes associated subcategories, Products and images.
     * @return  boolean         True on success, false otherwise
     */
    function delete()
    {
        global $objDatabase;
//echo("Debug: ShopCategory::delete(): entered<br />");

//echo("Error: ShopCategory::delete(): my ID: $this->id<br />"); die();
        // delete Products and images
        if (!Product::deleteByShopCategory($this->id)) {
echo("Error: ShopCategory::delete(): failed to delete Products from ShopCategory $this->id<br />");
            return false;
        }
//echo("Debug: ShopCategory::delete(): deleted Products from ShopCategory $this->id<br />");

        // remove sobcategories
        foreach ($this->getChildCategories() as $subCategory) {
            $subCategory->delete();
        }

        // remove category
        $objResult = $objDatabase->Execute("
            DELETE FROM ".DBPREFIX."module_shop_categories
            WHERE catid=$this->id
        ");
        if (!$objResult) {
echo("Error: ShopCategory::delete(): failed to delete ShopCategory $this->id<br />");
            return false;
        }
//echo("Debug: ShopCategory::delete(): deleted ShopCategory $this->id<br />");
        return true;
    }


    /**
     * Look for and delete the sub-ShopCategory named $catName
     * contained by the ShopCategory specified by $catParentId.
     * @param   integer     $catId      The parent ShopCategory ID
     * @param   string      $catName    The ShopCategory name to delete
     */
    function deleteChildNamed($catParentId, $catName)
    {
//echo("ShopCategory::deleteChildNamed(): parentId: $catParentId, name: $catName<br />");
        $objShopCategory = new ShopCategory($catName, $catParentId, '', '', '');
        $arrChild = $objShopCategory->selectWildcard();
        if (is_array($arrChild)) {
            if (count($arrChild) == 1) {
                return $arrChild[0]->delete();
            } else {
echo("ShopCategory::deleteChildNamed(): result miscount: ".count($arrChild)."<br />");
            }
        } else {
echo("ShopCategory::deleteChildNamed(): child not found: ".count($arrChild)."<br />");
        }
        return false;
    }


    /**
     * Select a ShopCategory matching the wildcards from the database.
     *
     * Uses the values of $this ShopCategory as patterns for the match.
     * Empty values will be ignored.
     * @return  array   Array of the resulting ShopCategory objects
     */
    function getByWildcard()
    {
        global $objDatabase;
        $query = '
            SELECT catid
              FROM '.DBPREFIX.'module_shop_categories
             WHERE 1 '.
        (!empty($this->id)       ? " AND catid=$this->id"            : '').
        (!empty($this->name)     ? " AND catname LIKE '%$this->name%'" : '').
        (!empty($this->parentId) ? " AND parentid=$this->parentId"   : '').
        (!empty($this->status)   ? " AND catstatus=$this->status"    : '').
        (!empty($this->sorting)  ? " AND catsorting=$this->sorting"  : '');
//echo("ShopCategory::selectWildcard(): query: $query<br />");
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
echo("ShopCategory::selectWildcard(): query error<br />");
            return false;
        }
        $arrShopCategories = array();
        while (!$objResult->EOF) {
            $arrShopCategories[] =
                ShopCategory::getById($objResult->Fields('catid'));
            $objResult->MoveNext();
        }
//echo("ShopCategory::selectWildcard(): got array:<br />");var_export($arrShopCategories);echo("<br />");
        return $arrShopCategories;
   }


    /**
     * Returns a ShopCategory selected by its ID from the database.
     * @static
     * @param   integer         The ShopCategory ID
     * @return  ShopCategory    The ShopCategory object on success,
     *                          false otherwise.
     */
    //static
    function getById($catId)
    {
        global $objDatabase;
        $objResult = $objDatabase->Execute("
            SELECT *
              FROM ".DBPREFIX."module_shop_categories
             WHERE catid=$catId
        ");
        if (!$objResult || $objResult->RecordCount() == 0) {
            return false;
        }
        return new ShopCategory(
            $objResult->Fields('catname'),
            $objResult->Fields('parentid'),
            $objResult->Fields('catstatus'),
            $objResult->Fields('catsorting'),
            $objResult->Fields('catid')
        );
    }


    /**
     * Returns an array of IDs of children of this ShopCategory.
     * @param   integer $parentShopCategoryId   The parent Shop Category ID.
     * @param   boolean $flagActiveOnly     Only return ShopCategories with
     *                                      status==1 if true.
     *                                      Defaults to false.
     * @return  array                       An array of ShopCategory IDs
     *                                      on success, false otherwise.
     * @static
     */
    //static
    function getChildCategoryIdArray(
        $parentShopCategoryId=0, $flagActiveOnly=false
    ) {
        global $objDatabase;

        $query = '
           SELECT catid
             FROM '.DBPREFIX."module_shop_categories
            WHERE parentid=$parentShopCategoryId".
                  ($flagActiveOnly ? ' AND catstatus=1' : '').'
         ORDER BY catsorting ASC
        ';
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
//echo("getChildCategoryIdArray($parentShopCategoryId, $flagActiveOnly): no child category ids!<br />");
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
     * Returns an array of this ShopCategory's children from the database.
     * @param   boolean $flagActiveOnly     Only return ShopCategories with
     *                                      status==1 if true.
     *                                      Defaults to false.
     * @return  array                       An array of ShopCategory objects
     *                                      on success, false otherwise
     */
    function getChildCategories($flagActiveOnly=false)
    {
        return ShopCategory::getChildCategoriesById($this->id, $flagActiveOnly);
    }


    /**
     * Returns an array of children of the ShopCategory
     * with ID $parentCategoryId.
     * @static
     * @param   integer $parentCategoryId   The parent ShopCategory ID
     * @param   boolean $flagActiveOnly     Only return ShopCategories with
     *                                      status==1 if true.
     *                                      Defaults to false.
     * @return  array                       An array of ShopCategory objects
     *                                      on success, false on failure.
     */
    //static
    function getChildCategoriesById($parentCategoryId=0, $flagActiveOnly=false)
    {
        global $objDatabase;

        $arrChildShopCategoryId =
            ShopCategory::getChildCategoryIdArray(
                $parentCategoryId, $flagActiveOnly
            );
        if ($arrChildShopCategoryId === false) {
//echo("no child category ids!<br />");
            return false;
        }
        $arrShopCategories = array();
        foreach ($arrChildShopCategoryId as $id) {
            $arrShopCategories[] =
                ShopCategory::getById($id);
        }
//echo("getChildCategoriesById(): child categories:<br />");var_export($arrShopCategories);echo("<br />");
        return $arrShopCategories;
    }


    /**
     * Returns an array representing a tree of ShopCategories,
     * not including the root chosen.
     *
     * The resulting array looks like:
     * array(
     *   parentId => array(
     *     childId => array(
     *       'sorting' => val,
     *       'status'  => val,
     *       'level'   => val,
     *     ),
     *     ... more children
     *   ),
     *   ... more parents
     * )
     * @static
     * @version 1.1
     * @param   integer $parentCategoryId   The optional root ShopCategory ID.
     *                                      Defaults to 0 (zero).
     * @param   boolean $flagActiveOnly     Only return ShopCategories
     *                                      with status==1 if true.
     *                                      Defaults to false.
     * @param   integer $level              Optional nesting level, initially 0.
     *                                      Defaults to 0 (zero).
     * @return  array   $arrShopCategories  The array of ShopCategories,
     *                                      or false on failure.
     */
    //static
    function getCategoryTree($parentCategoryId=0, $flagActiveOnly=false, $level=0)
    {
        // Get the ShopCategory's children
        $arrChildShopCategories =
            ShopCategory::getChildCategoriesById(
                $parentCategoryId, $flagActiveOnly
            );
        // has there been an error?
        if ($arrCategoryTree === false) {
            return false;
        }
        // initialize root tree
        $arrCategoryTree = array();
        // local parent subtree
        $arrCategoryTree[$parentCategoryId] = array();
        // the local parent's children
        foreach ($arrChildShopCategories as $objChildShopCategory) {
            $childCategoryId = $objChildShopCategory->getId();                 //echo("setting arrCategoryTree[$parentCategoryId][$childCategoryId]<br />");
            $arrCategoryTree[$parentCategoryId][$childCategoryId] = array(
                'sorting' => $objChildShopCategory->getSorting(),
                'status'  => $objChildShopCategory->getStatus(),
                'level'   => $level,
            );
            // get the grandchildren
            foreach (ShopCategory::getCategoryTree(
                        $childCategoryId, $flagActiveOnly, $level+1
                    ) as $subCategoryId => $objSubCategory) {
            	$arrCategoryTree[$objSubCategoryId] = $objSubCategory;
            };
        }
        return $arrCategoryTree;
    }


    /**
     * Returns the HTML code for a dropdown menu listing all ShopCategories.
     *
     * If the optional menu name string is non-empty, the <select> tag pair
     * with the menu name will be included, plus an option for the root
     * ShopCategory.  Otherwise, only the <option> tag list is returned.
     * @static
     * @global  array       $_ARRAYLANG     Language array
     * @param   integer     $selectedid     The selected ShopCategory ID
     * @param   string      $name           The optional menu name
     * @return  string                      The HTML dropdown menu code
     */
    //static
    function getShopCategoryMenuHierarchic($selectedId=0, $name='catId')
    {
        global $_ARRAYLANG;

        $result =
            ShopCategory::getShopCategoryMenuHierarchicRecurse(
                0, 0, $selectedId
            );
        if ($name) {
            $result =
                "<select name='$name'>".
    			"<option value='0'>{$_ARRAYLANG['TXT_ALL_PRODUCT_GROUPS']}</option>".
                "$result</select>";
        }
        return $result;
    }


    /**
     * Builds the ShopCategory menu recursively.
     *
     * Do not call this directly, use {@link getShopCategoryMenuHierarchic()}
     * instead.
     * @version  1.0      initial version
     * @static
     * @param    integer  $parcat       The parent ShopCategory ID.
     * @param    integer  $level        The nesting level.
     *                                  Should start at 0 (zero).
     * @param    integer  $selectedid   The optional selected ShopCategory ID.
     * @return   string                 The HTML code with all <option> tags,
     *                                  or the empty string on failure.
     */
    //static
    function getShopCategoryMenuHierarchicRecurse($parentCatId, $level, $selectedId=0)
    {
//echo("Debug: ShopCategory::getShopCategoryMenuHierarchicRecurse(parentCatId=$parentCatId, level=$level, selectedId=$selectedId)<br />");
        global $objDatabase;

        $arrChildShopCategories =
            ShopCategory::getChildCategoriesById($parentCatId);
        if (   !is_array($arrChildShopCategories
            || count($arrChildShopCategories) == 0)) {
echo("no child categories!<br />");
            return '';
        }
        $result = '';
        foreach ($arrChildShopCategories as $objShopCategory) {
            $id   = $objShopCategory->getId();
            $name = $objShopCategory->getName();
            $result .=
                "<option value='$id'".
                ($selectedId == $id ? ' selected="selected"' : '').'>'.
                str_repeat('.', $level*3).
                htmlentities($name).
                "</option>\n";
            if ($id != $parentCatId) {
                $result .=
                    ShopCategory::getShopCategoryMenuHierarchicRecurse(
                        $id, $level+1, $selectedId
                    );
            }
        }
        return $result;
    }


    /**
     * Returns the parent category ID, or 0 (zero)
     *
     * If the ID given corresponds to a top level category,
     * 0 (zero) is returned, as there is no parent.
     * If the ID cannot be found, 0 (zero) is returned as well.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @global  mixed   $objDatabase    Database object
     * @param   integer $intCategoryId  The category ID
     * @return  integer                 The parent category ID,
     *                                  or 0 (zero) on failure.
     * @static
     */
    //static
    function getParentCategoryId($intCategoryId)
    {
        global $objDatabase;

        $query = '
            SELECT parentid
              FROM '.DBPREFIX."module_shop_categories
             WHERE catid=$intCategoryId
          ORDER BY catsorting ASC
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult || $objResult->RecordCount == 0) {
            return 0;
        }
        return $objResult->Fields('parentid');
    }


    /**
     * Get the next ShopCategory ID after $shopCategoryId according to
     * the sorting order.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @param   integer $shopCategoryId     The ShopCategory ID
     * @return  integer                     The next ShopCategory ID
     * @static
     */
    //static
    function getNextShopCategoryId($shopCategoryId=0)
    {
        global $objDatabase;

        // Get the parent ShopCategory ID
        $parentShopCategoryId =
            ShopCategory::getParentCategoryId($shopCategoryId);
        // Get the IDs of all active children
        $arrChildShopCategoryId =
            ShopCategory::getChildCategoryIdArray($parentShopCategoryId, true);
        return
            (isset($arrChildShopCategoryId[
                        array_search($parentShopCategoryId, $arrChildShopCategoryId)+1
                   ])
                ? $arrChildShopCategoryId[
                        array_search($parentShopCategoryId, $arrChildShopCategoryId)+1
                  ]
                : $arrChildShopCategoryId[0]
            );
    }


    /**
     * Get the ShopCategory ID trail array.
     *
     * Returns an array of ShopCategory IDs of the current $shopCategoryId,
     * and all its ancestors.
     * @param   integer  $shopCategoryId    The current ShopCategory ID
     * @return  array                       The array of all ancestor
     *                                      ShopCategories
     */
    function getAncestorIdArray($shopCategoryId=1)
    //_makeArrCurrentCategories
    {
        $arrCategory = array();
        while ($shopCategoryId != 0) {
            $arrParentCategory = $this->arrParentCategoriesTable[$shopCategoryId];
            if (!is_array($arrParentCategory)) {
                $arrCategory[]       = 0;
                $shopCategoryId = 0;
            } else {
                $result = each($arrParentCategory);
                $arrCategory[]       = $result[0];
                $shopCategoryId = $result[0];
            }
        }
        return $arrCategory;
    }



}


/* test
echo("TEST: getcategoryTree(): <br />");
var_export(ShopCategory::getCategoryTree(0, 0, 0));
echo("<br />");
//die();
*/

?>
