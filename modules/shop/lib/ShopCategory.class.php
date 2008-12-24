<?php

/**
 * Shop Category
 * @copyright   CONTREXX CMS - COMVATION AG
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
 * Container for Products in the Shop
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
    private $id = 0;
    /**
     * @var     string      $name       ShopCategory name
     * @access  private
     */
    private $name = '';
    /**
     * @var     integer     $text_name_id   ShopCategory name Text ID
     * @access  private
     */
    private $text_name_id = 0;
    /**
     * @var     integer     $parentId   Parent ShopCategory ID
     * @access  private
     */
    private $parentId = 0;
    /**
     * @var     boolean     $status     Status of the ShopCategory
     * @access  private
     */
    private $status = 1;
    /**
     * @var     integer     $sorting    Sorting order of the ShopCategory
     * @access  private
     */
    private $sorting = 1;
    /**
     * @var     string      $picture    ShopCategory picture name
     * @access  private
     */
    private $picture = '';
    /**
     * @var     string      $flags      ShopCategory flags
     * @access  private
     */
    private $flags = '';


    /**
     * Create a ShopCategory
     *
     * If the optional argument $catId is greater than zero, the corresponding
     * category is updated.  Otherwise, a new category is created.
     * @access  public
     * @param   integer $catParentId    The new parent ID of the category
     * @param   integer $catStatus      The new status of the category (0 or 1)
     * @param   integer $catSorting     The sorting order
     * @param   integer $catId          The optional category ID to be updated
     * @return  ShopCategory            The ShopCategory
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function __construct(
        $catParentId, $catStatus, $catSorting, $catId=0
    ) {
        $this->id = intval($catId);
        // Use access methods here, various checks included.
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
     * Ignores the call if the given name is empty.
     * @param   string    $name     The ShopCategory name
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setName($name)
    {
        if (empty($name)) return;
        $this->name = trim(strip_tags($name));
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
        if ($this->id && $catParentId == $this->id) return false;
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
     * @param   boolean   $status   The ShopCategory status
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setStatus($status)
    {
        $this->status = ($status ? true : false);
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
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setSorting($catSorting)
    {
        $this->sorting = ($catSorting > 0 ? $catSorting : 0);
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
        $this->picture = trim(strip_tags($picture));
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
     * Note that the match is case sensitive.
     * @param   string              The flag to be added
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function addFlag($flag)
    {
        if (!$this->testFlag($flag)) $this->flags .= ' '.$flag;
    }
    /**
     * Remove a flag
     *
     * Note that the match is case sensitive.
     * @param   string              The flag to be removed
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function removeFlag($flag)
    {
        $this->flags = trim(preg_replace("/\\s*$flag\\s*/", ' ', $this->flags));
    }
    /**
     * Set the ShopCategories flags
     * @param   string              The ShopCategories flags
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setFlags($flags)
    {
        $this->flags = $flags;
    }
    /**
     * Test for a match with the ShopCategory flags.
     *
     * Note that the match is case sensitive.
     * @param   string              The ShopCategory flag to test
     * @return  boolean             Boolean true if the flag is set,
     *                              false otherwise.
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function testFlag($flag)
    {
        return preg_match("/$flag/", $this->flags);
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
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setVirtual($flagVirtual)
    {
        if ($flagVirtual) {
            $this->addFlag('__VIRTUAL__');
        } else {
            $this->removeFlag('__VIRTUAL__');
        }
    }


    /**
     * Test whether a record with the ID of this object is already present
     * in the database.
     * @return  boolean                 True if it exists, false otherwise
     * @global  ADONewConnection
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function recordExists()
    {
        global $objDatabase;

        $query = "
            SELECT 1
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_categories
             WHERE id=$this->id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult || $objResult->EOF) return false;
        return true;
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
                if (!$objShopCategory->store()) return false;
            }
        }
        if ($flagWithProducts) {
            foreach (Products::getByShopCategory($oldId) as $objProduct) {
                $objProduct->makeClone();
                $objProduct->setShopCategoryId($newId);
                if (!$objProduct->store()) return false;
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
        // Store Text
        $objText = Text::replace(
            $this->text_name_id, FRONTEND_LANG_ID, $this->name,
            MODULE_ID, TEXT_SHOP_CATEGORIES_NAME
        );
        if (!$objText) return false;
        $this->text_name_id = $objText->getId();
        if ($this->recordExists()) {
            if (!$this->update()) return false;
        } else {
            if (!$this->insert()) return false;
        }
        return true;
    }


    /**
     * Update this ShopCategory in the database.
     *
     * Does not update the Text present in the object.
     * Only {@link store()} does that.
     * Call this method yourself if you don't want to update language
     * specific data.
     * Returns the result of the query.
     * @return  boolean                 True on success, false otherwise
     * @global  ADONewConnection
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function update()
    {
        global $objDatabase;

        $query = "
            UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_categories
            SET text_name_id=$this->text_name_id,
                parent_id=$this->parentId,
                status=".($this->status ? 1 : 0).",
                sort_order=$this->sorting,
                picture='".addslashes($this->picture)."',
                flags='".addslashes($this->flags)."'
            WHERE id=$this->id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        return true;
    }


    /**
     * Insert this ShopCategory into the database.
     *
     * Does not update the Text fields present in the object.
     * Only {@link store()} does that.
     * Call this method yourself if you don't want to update language
     * specific data.
     * On success, updates this objects' Category ID.
     * Uses the ID stored in this object, if that is greater than zero.
     * @return  boolean                 True on success, false otherwise
     * @global  ADONewConnection
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function insert()
    {
        global $objDatabase;

        $query = "
            INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_categories (
                text_name_id, parent_id, status, sort_order,
                picture, flags
                ".($this->id > 0 ? ', id' : '')."
            ) VALUES (
                $this->text_name_id,
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
     * @global  ADONewConnection
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function delete($flagDeleteImages=false)
    {
        global $objDatabase;

        // Delete Text in all languages
        if (!$this->name->deleteById($this->text_name_id)) return false;
        // Delete Products and images
        if (!Products::deleteByShopCategory($this->id, $flagDeleteImages))
            return false;
        // Delete subcategories
        foreach ($this->getChildCategories() as $subCategory) {
            if (!$subCategory->delete($flagDeleteImages)) return false;
        }
        // Delete Category
        $objResult = $objDatabase->Execute("
            DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_categories
            WHERE id=$this->id
        ");
        if (!$objResult) return false;
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
        if (is_array($arrChild) && count($arrChild) == 1)
            return $arrChild[0]->delete();
        return false;
    }


    /**
     * Select Categories matching the wildcard values in this object
     * from the database.
     *
     * Uses the values of $this ShopCategory as patterns for the match.
     * Empty values will be ignored.  Tests for identity of the fields,
     * except with the name (pattern match) and the flags (matching records
     * must contain at least all of the flags present in the pattern).
     * @return  array                   Array of the resulting
     *                                  Shop Category objects
     * @global  ADONewConnection  $objDatabase
     * @author      Reto Kohli <reto.kohli@comvation.com>
     * @todo    This implementation does not allow any other values than
     *          boolean true or false for the status.
     *          Thus, the status is ignored here for the time being.
     */
    function getByWildcard()
    {
        global $objDatabase;

        $strTextId = '';
        if (!empty($this->name))
            $strTextId = join(',', Text::getIdArrayBySearch(
                $this->name,
                MODULE_ID, TEXT_SHOP_CATEGORIES_NAME,
                $this->lang_id
            ));
        $query = '
            SELECT id
              FROM '.DBPREFIX.'module_shop'.MODULE_INDEX.'_categories
             WHERE 1 '.
        (!empty($this->id)       ? " AND id=$this->id"                    : '').
        (!empty($strTextId)      ? " AND text_name_id IN ($strTextId)"    : '').
        (!empty($this->parentId) ? " AND parent_id=$this->parentId"       : '').
// Ignored
//        (!empty($this->status)   ? " AND status=$this->status"         : '').
// Silly...
//        (!empty($this->sorting)  ? " AND sort_order=$this->sorting"       : '').
        (!empty($this->picture)  ? " AND picture LIKE '%$this->picture%'" : '');
        foreach (split(' ', $this->flags) as $flag) {
            $query .= " AND flags LIKE '%$flag%'";
        }
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        $arrShopCategories = array();
        while (!$objResult->EOF) {
            $objShopCategory =
                ShopCategory::getById($objResult->fields['id']);
            if (!$objShopCategory) continue;
            $arrShopCategories[] = $objShopCategory;
            $objResult->MoveNext();
        }
        return $arrShopCategories;
   }


    /**
     * Returns a ShopCategory selected by its ID from the database.
     * @static
     * @param   integer       $catId        The Shop Category ID
     * @return  ShopCategory                The Shop Category object on success,
     *                                      false otherwise.
     * @global  ADONewConnection
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    static function getById($catId)
    {
        global $objDatabase;

        $arrSqlName = Text::getSqlSnippets('`categories`.`text_name_id`', FRONTEND_LANG_ID);
        $objResult = $objDatabase->Execute("
            SELECT `categories`.`id`, `categories`.`parent_id`,
                   `categories`.`status`, `categories`.`sort_order`,
                   `categories`.`picture`, `categories`.`flags`".
                   $arrSqlName['field']."
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_categories` as `categories`
                   ".$arrSqlName['join']."
             WHERE `categories`.`id`=$catId
        ");
        if (!$objResult || $objResult->EOF) return false;
        $objShopCategory = new ShopCategory(
            $objResult->fields['parent_id'],
            $objResult->fields['status'],
            $objResult->fields['sort_order'],
            $objResult->fields['id']
        );
        $text_name_id = $objResult->fields[$arrSqlName['name']];
        $strName = $objResult->fields[$arrSqlName['text']];
        // Replace Text in a missing language by another, if available
        if ($text_name_id && $strName === null) {
            $objText = Text::getById($text_name_id, 0);
            if ($objText)
                $objText->markDifferentLanguage(FRONTEND_LANG_ID);
                $strName = $objText->getText();
        }
        $objShopCategory->name = $strName;
        $objShopCategory->text_name_id = $text_name_id;
        $objShopCategory->picture = $objResult->fields['picture'];
        $objShopCategory->flags = $objResult->fields['flags'];
        return $objShopCategory;
    }


    /**
     * Returns an array of this ShopCategory's children from the database.
     * @param   boolean $flagActiveOnly     Only return active ShopCategories
     *                                      if true.
     *                                      Defaults to false.
     * @return  array                       An array of ShopCategory objects
     *                                      on success, false otherwise
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getChildCategories($flagActiveOnly=false)
    {
        if ($this->id <= 0) return false;
        return ShopCategories::getChildCategoriesById(
            $this->id, $flagActiveOnly
        );
    }


    /**
     * Return an array of all IDs of children ShopCateries.
     * @return  mixed                   Array of the resulting Shop Category
     *                                  IDs on success, false otherwise
     * @global  ADONewConnection
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getChildrenIdArray()
    {
        global $objDatabase;

        $query = "
            SELECT id
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_categories
             WHERE parent_id=$this->id
          ORDER BY sort_order ASC
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        $arrShopCategoryID = array();
        while (!$objResult->EOF) {
            $arrShopCategoryID[] = $objResult->fields['id'];
            $objResult->MoveNext();
        }
        return $arrShopCategoryID;
   }

}

?>
