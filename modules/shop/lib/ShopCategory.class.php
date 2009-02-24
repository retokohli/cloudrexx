<?php

/**
 * Shop Product Category
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @access      public
 * @version     2.1.0
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
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @access      public
 * @version     2.1.0
 * @package     contrexx
 * @subpackage  module_shop
 * @todo        From time to time, do something like this:
 *              $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_categories");
 *              $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_products");
 */
class ShopCategory
{
    /**
     * @var     integer     $id         ShopCategory ID
     * @access  private
     */
    private $id;
    /**
     * @var     string      $name       ShopCategory name
     * @access  private
     */
    private $name;
    /**
     * @var     integer     $parentId   Parent ShopCategory ID
     * @access  private
     */
    private $parentId;
    /**
     * @var     boolean     $status     Status of the ShopCategory
     * @access  private
     */
    private $status;
    /**
     * @var     integer     $sorting    Sorting order of the ShopCategory
     * @access  private
     */
    private $sorting;
    /**
     * @var     string      $picture    ShopCategory picture name
     * @access  private
     */
    private $picture;
    /**
     * @var     string      $flags      ShopCategory flags
     * @access  private
     */
    private $flags;


    /**
     * Create a ShopCategory
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
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function __construct(
        $catName, $catParentId, $catStatus, $catSorting, $catId=0
    ) {
        $this->id = intval($catId);
        // Use access methods here, various checks included.
        $this->setName($catName);
        $this->setParentId($catParentId);
        $this->setStatus($catStatus);
        $this->setSorting($catSorting);
    }


    /**
     * Get the ShopCategory ID
     * @return  integer             The ShopCategory ID
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getId()
    {
        return $this->id;
    }
    /**
     * Set the ShopCategory ID -- NOT ALLOWED!
     */

    /**
     * Get the ShopCategory name
     * @return  string              The ShopCategory name
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getName()
    {
        return $this->name;
    }
    /**
     * Set the ShopCategory name
     *
     * Returns false iff the given name is empty.
     * @param   string              The ShopCategory name
     * @return  boolean             True on success, false otherwise
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setName($catName)
    {
        if (empty($catName)) {
            return false;
        }
        $this->name = trim($catName);
        return true;
    }

    /**
     * Get the parent ShopCategory ID
     * @return  integer             The parent ShopCategory ID
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getParentId()
    {
        return $this->parentId;
    }
    /**
     * Set the parent ShopCategory ID.
     *
     * If the ID of this object is already set, returns false if the given
     * parent ID equals the ID.
     * @param   integer             The parent ShopCategory ID
     * @return  boolean             True on success, false otherwise
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setParentId($catParentId)
    {
        $catParentId = intval($catParentId);
        if ($this->id > 0 && $catParentId == $this->id) {
            return false;
        }
        $this->parentId = $catParentId;
        return true;
    }

    /**
     * Get the ShopCategory status
     * @return  integer             The ShopCategory status
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getStatus()
    {
        return $this->status;
    }
    /**
     * Set the ShopCategory status
     * @param   integer             The ShopCategory status
     * @return  boolean             Boolean true. Always.
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setStatus($catStatus)
    {
        $this->status = ($catStatus ? true : false);
        return true;
    }

    /**
     * Get the ShopCategory sorting order
     * @return  integer             The ShopCategory sorting order
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getSorting()
    {
        return $this->sorting;
    }
    /**
     * Set the ShopCategory sorting order
     * @param   integer             The ShopCategory sorting order
     * @return  boolean             Boolean true. Always.
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setSorting($catSorting)
    {
        $this->sorting = ($catSorting > 0 ? $catSorting : 0);
        return true;
    }

    /**
     * Get the ShopCategory picture name
     * @return  string              The ShopCategory picture name
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getPicture()
    {
        return $this->picture;
    }
    /**
     * Set the ShopCategory picture name
     * @param   string              The ShopCategory picture name
     * @return  boolean             Boolean true if the name was accepted,
     *                              false otherwise
     *                              (Always true for the time being).
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setPicture($picture)
    {
        $this->picture = $picture;
        return true;
    }

    /**
     * Get the ShopCategories flags
     * @return  string              The ShopCategories flags
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getFlags()
    {
        return $this->flags;
    }
    /**
     * Add a flag
     *
     * Note that the match is case insensitive.
     * @param   string              The flag to be added
     * @return  boolean             Boolean true if the flags were accepted
     *                              or already present, false otherwise
     *                              (always true for the time being).
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function addFlag($flag)
    {
        if (!$this->testFlag($flag)) {
            $this->flags .= ' '.$flag;
        }
        return true;
    }
    /**
     * Remove a flag
     *
     * Note that the match is case insensitive.
     * @param   string              The flag to be removed
     * @return  boolean             Boolean true if the flags could be removed
     *                              or wasn't present, false otherwise
     *                              (always true for the time being).
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function removeFlag($flag)
    {
        $this->flags = trim(preg_replace("/\\s*$flag\\s*/i", ' ', $this->flags));
        return true;
    }
    /**
     * Set the ShopCategories flags
     * @param   string              The ShopCategories flags
     * @return  boolean             Boolean true if the flags were accepted,
     *                              false otherwise
     *                              (always true for the time being).
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setFlags($flags)
    {
        $this->flags = $flags;
        return true;
    }
    /**
     * Test for a match with the ShopCategory flags.
     *
     * Note that the match is case insensitive.
     * @param   string              The ShopCategory flag to test
     * @return  boolean             Boolean true if the flag is set,
     *                              false otherwise.
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function testFlag($flag)
    {
        return preg_match("/$flag/i", $this->flags);
    }
    /**
     * Returns true if this ShopCategory is virtual
     *
     * Note: Virtual ShopCategories have the "__VIRTUAL__" flag set.
     * The test performed in isVirtual() is case sensitive!
     * @return  boolean             True if the ShopCategory is virtual,
     *                              false otherwise.
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function isVirtual()
    {
        return preg_match('/__VIRTUAL__/', $this->flags);
    }
    /**
     * Make this ShopCategory virtual if the argument evaluates to boolean
     * true.  If it evaluates to false, however, the virtual status is
     * cleared.
     * @return  boolean             True on success, false otherwise
     *                              (depends of the result of the call
     *                              to {@link addFlag()}).
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setVirtual($flagVirtual)
    {
        if ($flagVirtual) {
            return $this->addFlag('__VIRTUAL__');
        }
        return $this->removeFlag('__VIRTUAL__');
    }


    /**
     * Test whether a record with the ID of this object is already present
     * in the database.
     * @return  boolean                 True if it exists, false otherwise
     * @global  ADONewConnection  $objDatabase    Database connection object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function recordExists()
    {
        global $objDatabase;

        $query = "
            SELECT 1
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_categories
             WHERE catid=$this->id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        if ($objResult->RecordCount() == 1) {
            return true;
        }
        return false;
    }


    /**
     * Clone the ShopCategory
     *
     * Note that this does NOT create a copy in any way, but simply clears
     * the ShopCategory ID.  Upon storing this object, a new ID is created.
     * @return      void
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function makeClone($flagRecursive=false, $flagWithProducts=false)
    {
        $oldId = $this->id;
        $this->id = 0;
        $this->store();
        $newId = $this->id;
        if ($flagRecursive) {
            foreach (ShopCategories::getChildCategoriesById($oldId)
                    as $objShopCategory) {
                $objShopCategory->makeClone($flagRecursive, $flagWithProducts);
                $objShopCategory->setParentId($newId);
                if (!$objShopCategory->store()) {
                    return false;
                }
            }
        }
        if ($flagWithProducts) {
            foreach (Products::getByShopCategory($oldId) as $objProduct) {
                $objProduct->makeClone();
                $objProduct->setShopCategoryId($newId);
                if (!$objProduct->store()) {
                    return false;
                }
            }
        }
        return true;
    }


    /**
     * Stores the ShopCategory object in the database.
     *
     * Either updates (id > 0) or inserts (id == 0) the object.
     * @return  boolean     True on success, false otherwise
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function store()
    {
        if ($this->recordExists()) {
            return ($this->update());
        }
        return ($this->insert());
    }


    /**
     * Update this ShopCategory in the database.
     * Returns the result of the query.
     * @return  boolean                 True on success, false otherwise
     * @global  ADONewConnection  $objDatabase    Database connection object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function update()
    {
        global $objDatabase;

        $query = "
            UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_categories
            SET catname='".addslashes($this->name)."',
                parentid=$this->parentId,
                catstatus=".($this->status ? 1 : 0).",
                catsorting=$this->sorting,
                picture='".addslashes($this->picture)."',
                flags='".addslashes($this->flags)."'
            WHERE catid=$this->id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        return true;
    }


    /**
     * Insert this ShopCategory into the database.
     *
     * On success, updates this objects' Category ID.
     * Uses the ID stored in this object, if greater than zero.
     * @return  boolean                 True on success, false otherwise
     * @global  ADONewConnection  $objDatabase    Database connection object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function insert()
    {
        global $objDatabase;

        $query = "
            INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_categories (
                catname, parentid, catstatus, catsorting,
                picture, flags
                ".($this->id > 0 ? ', catid' : '')."
            ) VALUES (
                '".addslashes($this->name)."',
                $this->parentId,
                ".($this->status ? 1 : 0).",
                $this->sorting,
                '".addslashes($this->picture)."',
                '".addslashes($this->flags)."'
                ".($this->id > 0 ? ", $this->id" : '')."
            )";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        $this->id = $objDatabase->Insert_ID();
        return true;
    }


    /**
     * Delete this ShopCategory from the database.
     *
     * Also removes associated subcategories and Products.
     * Images will only be erased from the disc if the optional
     * $flagDeleteImages parameter evaluates to true.
     * @return  boolean                 True on success, false otherwise
     * @global  ADONewConnection  $objDatabase    Database connection object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function delete($flagDeleteImages=false)
    {
        global $objDatabase;

        // Delete Products and images
        if (!Products::deleteByShopCategory($this->id, $flagDeleteImages)) {
            return false;
        }

        // Delete subcategories
        foreach ($this->getChildCategories() as $subCategory) {
            if (!$subCategory->delete($flagDeleteImages)) {
                return false;
            }
        }

        // Delete Category
        $objResult = $objDatabase->Execute("
            DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_categories
            WHERE catid=$this->id
        ");
        if (!$objResult) {
            return false;
        }
        return true;
    }


    /**
     * Look for and delete the sub-ShopCategory named $catName
     * contained by the ShopCategory specified by $catParentId.
     * @param   integer     $catId      The parent ShopCategory ID
     * @param   string      $catName    The ShopCategory name to delete
     * @author      Reto Kohli <reto.kohli@comvation.com>
     * @static
     */
    static function deleteChildNamed($catParentId, $catName)
    {
        $objShopCategory = new ShopCategory($catName, $catParentId, '', '', '');
        $arrChild = $objShopCategory->getByWildcard();
        if (is_array($arrChild) && count($arrChild) == 1) {
            return $arrChild[0]->delete();
        }
        return false;
    }


    /**
     * Select a ShopCategory matching the wildcards from the database.
     *
     * Uses the values of $this ShopCategory as patterns for the match.
     * Empty values will be ignored.  Tests for identity of the fields,
     * except with the name (pattern match) and the flags (matching records
     * must contain at least all of the flags present in the pattern).
     * @return  array                   Array of the resulting
     *                                  Shop Category objects
     * @global  ADONewConnection  $objDatabase    Database connection object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getByWildcard()
    {
        global $objDatabase;
        $query = '
            SELECT catid
              FROM '.DBPREFIX.'module_shop_categories
             WHERE 1 '.
        (!empty($this->id)       ? " AND catid=$this->id"                 : '').
        (!empty($this->name)     ? " AND catname LIKE '%$this->name%'"    : '').
        (!empty($this->parentId) ? " AND parentid=$this->parentId"        : '').
// TODO: This implementation does not allow any value other than boolean values
// true or false.  As false is considered to be empty, this won't work in that
// case.  We better ignore the status for the time being.
//        (!empty($this->status)   ? " AND catstatus=$this->status"         : '').
        (!empty($this->sorting)  ? " AND catsorting=$this->sorting"       : '').
        (!empty($this->picture)  ? " AND picture LIKE '%$this->picture%'" : '');
        foreach (split(' ', $this->flags) as $flag) {
            $query .= " AND flags LIKE '%$flag%'";
        }
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        $arrShopCategories = array();
        while (!$objResult->EOF) {
            $objShopCategory =
                ShopCategory::getById($objResult->fields['catid']);
            $arrShopCategories[] = $objShopCategory;
            $objResult->MoveNext();
        }
        return $arrShopCategories;
   }


    /**
     * Returns a ShopCategory selected by its ID from the database.
     * @static
     * @param   integer                     The Shop Category ID
     * @return  ShopCategory                The Shop Category object on success,
     *                                      false otherwise.
     * @global  ADONewConnection  $objDatabase    Database connection object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    static function getById($catId)
    {
        global $objDatabase;
        $objResult = $objDatabase->Execute("
            SELECT *
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_categories
             WHERE catid=$catId
        ");
        if (!$objResult || $objResult->RecordCount() == 0) {
            return false;
        }
        $objShopCategory = new ShopCategory(
            $objResult->fields['catname'],
            $objResult->fields['parentid'],
            $objResult->fields['catstatus'],
            $objResult->fields['catsorting'],
            $objResult->fields['catid']
        );
        $objShopCategory->setPicture($objResult->fields['picture']);
        $objShopCategory->setFlags($objResult->fields['flags']);
        return $objShopCategory;
    }


    /**
     * Returns an array of this ShopCategory's children from the database.
     * @param   boolean $flagActiveOnly     Only return ShopCategories with
     *                                      status==1 if true.
     *                                      Defaults to false.
     * @return  mixed                       An array of ShopCategory objects
     *                                      on success, false otherwise
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getChildCategories($flagActiveOnly=false)
    {
        if ($this->id <= 0) {
            return false;
        }
        return ShopCategories::getChildCategoriesById(
            $this->id, $flagActiveOnly
        );
    }


    /**
     * Return an array of all IDs of children ShopCateries.
     * @return  mixed                   Array of the resulting Shop Category
     *                                  IDs on success, false otherwise
     * @global  ADONewConnection  $objDatabase    Database connection object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getChildrenIdArray()
    {
        global $objDatabase;

        $query = "
            SELECT catid
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_categories
             WHERE parentid=$this->id
          ORDER BY catsorting ASC
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        $arrShopCategoryID = array();
        while (!$objResult->EOF) {
            $arrShopCategoryID[] = $objResult->fields['catid'];
            $objResult->MoveNext();
        }
        return $arrShopCategoryID;
   }


    /**
     * Returns the child ShopCategory of this with the given name, if found.
     *
     * Returns false if the query fails, or if no child ShopCategory of
     * that name can be found.
     * //Note that if there are two or more children of the same name (and with
     * //active status, if $flagActiveOnly is true), a warning will be echo()ed.
     * //This is by design.
     * @static
     * @param   string      $strName        The child ShopCategory name
     * @param   boolean     $flagActiveOnly If true, only active ShopCategories
     *                                      are considered.
     * @return  mixed                       The ShopCategory on success,
     *                                      false otherwise.
     * @global  ADONewConnection  $objDatabase    Database connection object
     * @global  array
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    static function getChildNamed($strName, $flagActiveOnly=true)
    {
        global $objDatabase;

        $query = "
           SELECT catid
             FROM ".DBPREFIX."module_shop".MODULE_INDEX."_categories
            WHERE ".($flagActiveOnly ? 'catstatus=1 AND' : '')."
                  parentid=$this->parentId AND
                  catname='".addslashes($strName)."'
         ORDER BY catsorting ASC
        ";

        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
/*
        if ($objResult->RecordCount() > 1) {
            echo("ShopCategory::getChildNamed($strName, $flagActiveOnly): ".$_ARRAYLANG['TXT_SHOP_WARNING_MULTIPLE_CATEGORIES_WITH_SAME_NAME'].'<br />');
        }
*/
        if (!$objResult->EOF) {
            return ShopCategory::getById($objResult->fields['catid']);
        }
        return false;
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
    static function getCategoryTree($parentCategoryId=0, $flagActiveOnly=false, $level=0)
    {
        // Get the ShopCategory's children
        $arrChildShopCategories =
            ShopCategory::getChildCategoriesById(
                $parentCategoryId, $flagActiveOnly
            );
        // has there been an error?
        if ($arrChildShopCategories === false) {
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
                $arrCategoryTree[$subCategoryId] = $objSubCategory;
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
    static function getShopCategoryMenuHierarchic($selectedId=0, $name='catId')
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
    static function getShopCategoryMenuHierarchicRecurse($parentCatId, $level, $selectedId=0)
    {
        global $objDatabase;

        $arrChildShopCategories =
            ShopCategory::getChildCategoriesById($parentCatId);
        if (   !is_array($arrChildShopCategories
            || count($arrChildShopCategories) == 0)) {
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
     * @global  ADONewConnection  $objDatabase    Database connection object
     * @param   integer $intCategoryId  The category ID
     * @return  integer                 The parent category ID,
     *                                  or 0 (zero) on failure.
     * @static
     */
    static function getParentCategoryId($intCategoryId)
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
        return $objResult->fields['parentid'];
    }


    /**
     * Get the next ShopCategory ID after $shopCategoryId according to
     * the sorting order.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @param   integer $shopCategoryId     The ShopCategory ID
     * @return  integer                     The next ShopCategory ID
     * @static
     */
    static function getNextShopCategoryId($shopCategoryId=0)
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


// Test
//echo("TEST: getcategoryTree(): <br />");
//var_export(ShopCategory::getCategoryTree(0, 0, 0));
//echo("<br />");
//die();

?>
