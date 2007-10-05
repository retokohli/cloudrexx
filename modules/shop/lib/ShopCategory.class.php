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


/*

Modifications to the Database structure:

ALTER TABLE `contrexx_module_shop_categories` ADD `picture` VARCHAR( 255 ) NULL;
ALTER TABLE `contrexx_module_shop_categories` ADD `flags`   VARCHAR( 100 ) NULL;
ALTER TABLE `contrexx_module_shop_categories` ADD INDEX (`flags`);

Full structure:

DROP TABLE IF EXISTS `contrexx_module_shop_categories`;

CREATE TABLE `contrexx_module_shop_categories` (
  `catid`       int(10)     unsigned NOT NULL auto_increment,
  `parentid`    int(10)     unsigned NOT NULL default '0',
  `catname`     varchar(255)         NOT NULL default '',
  `catsorting`  smallint(6)          NOT NULL default '100',
  `catstatus`   tinyint(1)           NOT NULL default '1',
  `picture`     varchar(255)             NULL,
  `flags`       varchar(100)             NULL,
  PRIMARY KEY  (`catid`),
  KEY `flags` (`flags`)
) ENGINE=MyISAM;

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
     * @var     string      $picture    ShopCategory picture name
     * @access  private
     */
    var $picture;
    /**
     * @var     string      $flags      ShopCategory flags
     * @access  private
     */
    var $flags;


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
        $this->id = intval($catId);
        // Use access methods here, various checks included.
        $this->setName($catName);
        $this->setParentId($catParentId);
        $this->setStatus($catStatus);
        $this->setSorting($catSorting);
//echo("__construct(): made<br />");var_export($this);echo("<br />");
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
        if (empty($catName)) {
//echo("ShopCategory::setParentId(): ERROR: Empty name!<br />");
            return false;
        }
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
//echo("ShopCategory::setParentId(): ERROR: ID == parent ID ($this->id)!<br />");
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
     * Get the ShopCategory picture name
     * @return  string              The ShopCategory picture name
     */
    function getPicture() {
        return $this->picture;
    }
    /**
     * Set the ShopCategory picture name
     * @param   string              The ShopCategory picture name
     * @return  boolean             Boolean true if the name was accepted,
     *                              false otherwise
     *                              (Always true for the time being).
     */
    function setPicture($picture) {
        $this->picture = $picture;
    }

    /**
     * Get the ShopCategories flags
     * @return  string              The ShopCategories flags
     */
    function getFlags() {
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
     */
    function addFlag($flag) {
        if (!preg_match("/$flag/i", $this->flags)) {
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
     */
    function removeFlag($flag) {
        $this->flags = trim(preg_replace("/\\s*$flag\\s*/i", ' ', $this->flags));
        return true;
    }
    /**
     * Set the ShopCategories flags
     * @param   string              The ShopCategories flags
     * @return  boolean             Boolean true if the flags were accepted,
     *                              false otherwise
     *                              (always true for the time being).
     */
    function setFlags($flags) {
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
     */
    function testFlag($flag) {
        return preg_match("/$flag/i", $this->flags);
    }

    /**
     * Returns true if this ShopCategory is virtual
     *
     * Note: Virtual ShopCategories have the "__VIRTUAL__" flag set.
     * The test performed in isVirtual() is case sensitive!
     * @return  boolean             True if the ShopCategory is virtual,
     *                              false otherwise.
     */
    function isVirtual()
    {
        return preg_match('/__VIRTUAL__/', $this->flags);
    }


    /**
     * Test whether a record with the ID of this object is already present
     * in the database.
     * @return  boolean                 True if it exists, false otherwise
     * @global  mixed   $objDatabase    Database object
     */
    function recordExists()
    {
        global $objDatabase;

        $query = "
            SELECT 1
              FROM ".DBPREFIX."module_shop_categories
             WHERE catid=$this->id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
//echo("ShopCategory::recordExists(): query error<br />");
            return false;
        }
        if ($objResult->RecordCount() == 1) {
//echo("ShopCategory::recordExists(): ID $this->id: Yes<br />");
            return true;
        }
//echo("ShopCategory::recordExists(): ID $this->id: No<br />");
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
     *
     * @return  boolean     True on success, false otherwise
     */
    function store() {
        if ($this->recordExists()) {
            return ($this->update());
        }
        return ($this->insert());
    }


    /**
     * Update this ShopCategory in the database.
     * Returns the result of the query.
     * @return  boolean                 True on success, false otherwise
     * @global  mixed   $objDatabase    Database object
     */
    function update()
    {
        global $objDatabase;

        return $objDatabase->Execute("
            UPDATE ".DBPREFIX."module_shop_categories
            SET catname='".addslashes($this->name)."',
                parentid=$this->parentId,
                catstatus=$this->status,
                catsorting=$this->sorting,
                picture='".addslashes($this->picture)."',
                flags='".addslashes($this->flags)."'
            WHERE catid=$this->id
        ");
    }


    /**
     * Insert this ShopCategory into the database.
     *
     * On success, updates this objects' Category ID.
     * Uses the ID stored in this object, if greater than zero.
     * @return  boolean                 True on success, false otherwise
     * @global  mixed   $objDatabase    Database object
     */
    function insert()
    {
        global $objDatabase;
//echo("ShopCategory::insert(): Parent ID: $this->parentId<br />");

        $query = "
            INSERT INTO ".DBPREFIX."module_shop_categories (
                catname, parentid, catstatus, catsorting,
                picture, flags
                ".($this->id > 0 ? ', catid' : '')."
            ) VALUES (
                '".addslashes($this->name)."',
                $this->parentId,
                $this->status,
                $this->sorting,
                '".addslashes($this->picture)."',
                '".addslashes($this->flags)."'
                ".($this->id > 0 ? ", $this->id" : '')."
            )";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
//echo("ShopCategory::insert(): Query failed: $query<br />");
            return false;
        }
        $this->id = $objDatabase->Insert_ID();
//echo("ShopCategory::insert(): new ID: $this->id<br />");
        return true;
    }


    /**
     * Delete this ShopCategory from the database.
     *
     * Also removes associated subcategories and Products.
     * Images will only be erased from the disc if the optional
     * $flagDeleteImages parameter evaluates to true.
     * @return  boolean                 True on success, false otherwise
     * @global  mixed   $objDatabase    Database object
     */
    function delete($flagDeleteImages=false)
    {
        global $objDatabase;

        // Delete Products and images
        if (!Products::deleteByShopCategory($this->id, $flagDeleteImages)) {
//echo("Error: ShopCategory::delete(): failed to delete Products from ShopCategory $this->id<br />");
            return false;
        }
//echo("Debug: ShopCategory::delete(): deleted Products from ShopCategory $this->id<br />");

        // Delete subcategories
        foreach ($this->getChildCategories() as $subCategory) {
            if (!$subCategory->delete($flagDeleteImages)) {
//echo("Error: ShopCategory::delete(): failed to delete ShopCategory $this->id<br />");
                return false;
            }
        }

        // Delete Category
        $objResult = $objDatabase->Execute("
            DELETE FROM ".DBPREFIX."module_shop_categories
            WHERE catid=$this->id
        ");
        if (!$objResult) {
//echo("Error: ShopCategory::delete(): failed to delete ShopCategory $this->id<br />");
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
        $arrChild = $objShopCategory->getByWildcard();
        if (is_array($arrChild)) {
            if (count($arrChild) == 1) {
                return $arrChild[0]->delete();
            } else {
//echo("ShopCategory::deleteChildNamed(): result miscount: ".count($arrChild)."<br />");
            }
        } else {
//echo("ShopCategory::deleteChildNamed(): child not found: ".count($arrChild)."<br />");
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
     * @global  mixed   $objDatabase    Database object
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
        (!empty($this->picture)  ? " AND picture=$this->picture"     : '');
        foreach (split(' ', $this->flags) as $flag) {
        	$query .= " AND flags LIKE '%$flag%'";
        }
//echo("ShopCategory::getByWildcard(): query: $query<br />");
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
//echo("ShopCategory::getByWildcard(): query error<br />");
            return false;
        }
        $arrShopCategories = array();
        while (!$objResult->EOF) {
            $objShopCategory =
                ShopCategory::getById($objResult->Fields('catid'));
//echo("ShopCategory::getByWildcard(): got ShopCategory: ");var_export($arrShopCategories);//echo("<br />");
            $arrShopCategories[] = $objShopCategory;
            $objResult->MoveNext();
        }
//echo("ShopCategory::getByWildcard(): got array:<br />");var_export($arrShopCategories);echo("<br />");
        return $arrShopCategories;
   }


    /**
     * Returns a ShopCategory selected by its ID from the database.
     * @static
     * @param   integer                     The Shop Category ID
     * @return  ShopCategory                The Shop Category object on success,
     *                                      false otherwise.
     * @global  mixed       $objDatabase    Database object
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
        $objShopCategory = new ShopCategory(
            $objResult->Fields('catname'),
            $objResult->Fields('parentid'),
            $objResult->Fields('catstatus'),
            $objResult->Fields('catsorting'),
            $objResult->Fields('catid')
        );
        $objShopCategory->setPicture($objResult->Fields('picture'));
        $objShopCategory->setFlags($objResult->Fields('flags'));
        return $objShopCategory;
    }


    /**
     * Returns an array of this ShopCategory's children from the database.
     * @param   boolean $flagActiveOnly     Only return ShopCategories with
     *                                      status==1 if true.
     *                                      Defaults to false.
     * @return  mixed                       An array of ShopCategory objects
     *                                      on success, false otherwise
     */
    function getChildCategories($flagActiveOnly=false)
    {
        if ($this->id <= 0) {
//echo("ShopCategory::getChildCategories($flagActiveOnly): ERROR: Missing or invalid ID: $id!<br />");
            return false;
        }
        return ShopCategories::getChildCategoriesById($this->id, $flagActiveOnly);
    }


    /**
     * Return an array of all IDs of children ShopCateries.
     * @return  mixed                   Array of the resulting Shop Category
     *                                  IDs on success, false otherwise
     * @global  mixed   $objDatabase    Database object
     */
    function getChildrenIdArray()
    {
        global $objDatabase;

        $query = "
            SELECT catid
              FROM ".DBPREFIX."module_shop_categories
             WHERE parentid=$this->id
          ORDER BY catsorting ASC
        ";
//echo("ShopCategory::getChildrenIdArray(): query: $query<br />");
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
//echo("ShopCategory::getChildrenIdArray(): ERROR: Query failed: $query<br />");
            return false;
        }
        $arrShopCategoryID = array();
        while (!$objResult->EOF) {
            $arrShopCategoryID[] = $objResult->Fields('catid');
            $objResult->MoveNext();
        }
//echo("ShopCategory::getChildrenIdArray(): got array:<br />");var_export($arrShopCategoryID);echo("<br />");
        return $arrShopCategoryID;
   }


    /**
     * Returns the child ShopCategory of this with the given name, if found.
     *
     * Returns false if the query fails, or if no child ShopCategory of
     * that name can be found.
     * Note that if there are two or more children of the same name (and with
     * active status, if $flagActiveOnly is true), a warning will be echo()ed.
     * This is by design.
     * @static
     * @param   string      $strName        The child ShopCategory name
     * @param   boolean     $flagActiveOnly If true, only active ShopCategories
     *                                      are considered.
     * @return  mixed                       The ShopCategory on success,
     *                                      false otherwise.
     */
    //static
    function getChildNamed($strName, $flagActiveOnly=true)
    {
        global $objDatabase;

        $query = "
           SELECT catid
             FROM ".DBPREFIX."module_shop_categories
            WHERE ".($flagActiveOnly ? 'catstatus=1 AND' : '')."
                  parentid=$this->parentId AND
                  catname='".addslashes($strName)."'
         ORDER BY catsorting ASC
        ";

        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
//echo("ShopCategory::getChildNamed($strName, $flagActiveOnly): ERROR: Query failed: $query<br />");
            return false;
        }
        if (!$objResult->RecordCount() > 1) {
echo("ShopCategory::getChildNamed($strName, $flagActiveOnly): WARNING: More than one ShopCategory of the same name found!<br />");
        }
        if (!$objResult->EOF) {
            return ShopCategory::getById($objResult->Fields('catid'));
        }
        return false;
    }



}


/* test
//echo("TEST: getcategoryTree(): <br />");
//var_export(ShopCategory::getCategoryTree(0, 0, 0));
//echo("<br />");
//die();
*/

?>
