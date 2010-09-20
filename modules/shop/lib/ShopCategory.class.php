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
     * Text keys
     */
    const TEXT_NAME = 'shop_category_name';
    const TEXT_DESCRIPTION = 'shop_category_description';

    /**
     * @var     integer     $id         ShopCategory ID
     * @access  private
     */
    private $id = null;
    /**
     * @var     integer     $parent_id   Parent ShopCategory ID
     * @access  private
     */
    private $parent_id = 0;
    /**
     * @var     string      $name       ShopCategory name
     * @access  private
     */
    private $name = '';
    /**
     * @var     integer     $text_name_id  Text ID of the name
     * @access  private
     */
    private $text_name_id = null;
    /**
     * @var     string      $description    ShopCategory description
     * @access  private
     */
    private $description = '';
    /**
     * @var     integer     $text_description_id  Text ID of the description
     * @access  private
     */
    private $text_description_id = null;
    /**
     * @var     boolean     $active     Active status of the ShopCategory
     * @access  private
     */
    private $active = 1;
    /**
     * @var     integer     $ord    Ordinal value of the ShopCategory
     * @access  private
     */
    private $ord = 0;
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
     * If the optional argument $category_id is greater than zero, the corresponding
     * category is updated.  Otherwise, a new category is created.
     * @access  public
     * @param   string  $name           The new category name
     * @param   string  $description    The new category description
     * @param   integer $parent_id      The new parent ID of the category
     * @param   integer $active         The new active status of the category (0 or 1)
     * @param   integer $ord            The sorting order
     * @param   integer $id             The optional category ID to be updated
     * @return  ShopCategory            The ShopCategory
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function __construct(
        $name, $description='', $parent_id, $active=false, $ord=0, $id=0
    ) {
        $this->id = intval($id);
        // Use access methods here, various checks included.
        $this->setName($name);
        $this->setDescription($description);
        $this->setParentId($parent_id);
        $this->setActive($active);
        $this->setOrd($ord);
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
    function setName($name)
    {
        if (empty($name)) {
            return false;
        }
        $this->name = trim($name);
        return true;
    }

    /**
     * Get the description
     * @return  string              The description
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getDescription() {
        return $this->description;
    }
    /**
     * Set the description
     *
     * Returns false iff the given description is empty.
     * @param   string              The description
     * @return  boolean             True on success, false otherwise
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setDescription($description)
    {
        $this->description = trim($description);
        return true;
    }

    /**
     * Get the parent ShopCategory ID
     * @return  integer             The parent ShopCategory ID
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getParentId()
    {
        return $this->parent_id;
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
    function setParentId($parent_id)
    {
        $parent_id = intval($parent_id);
        if ($this->id > 0 && $parent_id == $this->id) {
            return false;
        }
        $this->parent_id = $parent_id;
        return true;
    }

    /**
     * Get the ShopCategory active status
     * @return  integer             The ShopCategory active status
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getActive()
    {
        return $this->active;
    }
    /**
     * Set the ShopCategory active status
     * @param   boolean   $active   The ShopCategory active status
     * @return  boolean             Boolean true. Always.
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setActive($active)
    {
        $this->active = ($active ? true : false);
        return true;
    }

    /**
     * Get the ShopCategory sorting order
     * @return  integer             The ShopCategory sorting order
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getOrd()
    {
        return $this->ord;
    }
    /**
     * Set the ShopCategory sorting order
     * @param   integer             The ShopCategory sorting order
     * @return  boolean             Boolean true. Always.
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function setOrd($ord)
    {
        $this->ord = ($ord > 0 ? $ord : 0);
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
        $this->flags = trim(preg_replace(
            '/(?:^|\s)$flag(?:\s|\=\S*]|$)/i', ' ', $this->flags));
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
        return self::testFlag2($flag, $this->flags);
    }
    /**
     * Test for a match with the flags in the string.
     *
     * Note that the match is case insensitive.
     * @param   string              The ShopCategory flag to test
     * @param   string              The ShopCategory flags
     * @return  boolean             Boolean true if the flag is set,
     *                              false otherwise.
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    static function testFlag2($flag, $flags)
    {
        return preg_match('/(?:^|\s)$flag(?:\s|\=|$)/i', $flags);
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
             WHERE id=$this->id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        if ($objResult->EOF) {
            return false;
        }
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
                    as $objCategory) {
                $objCategory->makeClone($flagRecursive, $flagWithProducts);
                $objCategory->setParentId($newId);
                if (!$objCategory->store()) {
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
        // Empty names are invalid
        if ($this->name == '') return false;
        $this->text_name_id = Text::replace(
            $this->text_name_id, FRONTEND_LANG_ID,
            $this->name, MODULE_ID, self::TEXT_NAME
        );
        if (!$this->text_name_id) return false;
        if ($this->description == '') {
            // Delete empty description record (current language only)
            if ($this->text_description_id) {
                if (!Text::deleteById(
                    $this->text_description_id, FRONTEND_LANG_ID))
                    return false;
            }
            $this->text_description_id = null;
        } else {
            $this->text_description_id = Text::replace(
                $this->text_description_id, FRONTEND_LANG_ID,
                $this->description, MODULE_ID, self::TEXT_DESCRIPTION);
            if (!$this->text_description_id) return false;
        }

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
            UPDATE `".DBPREFIX."module_shop".MODULE_INDEX."_categories`
              SET `text_name_id`=$this->text_name_id,
                  `text_description_id`=$this->text_description_id,
                  `parent_id`=$this->parent_id,
                  `active`=".($this->active ? 1 : 0).",
                  `ord`=$this->ord,
                  `picture`='".addslashes($this->picture)."',
                  `flags`='".addslashes($this->flags)."'
            WHERE `id`=$this->id";
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
                `text_name_id`, `text_description_id`,
                `parent_id`, `active`, `ord`,
                `picture`, `flags`
                ".($this->id ? ', id' : '')."
            ) VALUES (
                $this->text_name_id, $this->text_description_id,
                $this->parent_id, ".($this->active ? 1 : 0).", $this->ord,
                '".addslashes($this->picture)."', '".addslashes($this->flags)."'
                ".($this->id ? ", $this->id" : '')."
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
            WHERE id=$this->id");
        if (!$objResult) {
            return false;
        }
        return true;
    }


    /**
     * Look for and delete the sub-ShopCategory named $name
     * contained by the ShopCategory specified by $parent_id.
     *
     * The child's name must be unambiguous, or the method will fail.
     * @param   integer     $category_id    The parent ShopCategory ID
     * @param   string      $name     The ShopCategory name to delete
     * @return  boolean               True on success, false otherwise
     * @author      Reto Kohli <reto.kohli@comvation.com>
     * @static
     */
    static function deleteChildNamed($parent_id, $name)
    {
        $objCategory = new ShopCategory($name, $parent_id, '', '', '');
        $arrChild = $objCategory->getByWildcard();
        if (is_array($arrChild) && count($arrChild) == 1) {
            return $arrChild[0]->delete();
        }
        return false;
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
    static function getById($category_id)
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
            SELECT `category`.`id`,
                   `category`.`parent_id`,
                   `category`.`active`,
                   `category`.`ord`,
                   `category`.`picture`,
                   `category`.`flags`".
                   $arrSqlName['field'].
                   $arrSqlDescription['field']."
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_categories` AS `category`".
                   $arrSqlName['join'].
                   $arrSqlDescription['join']."
             WHERE `category`.`id`=$category_id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) { return self::errorHandler(); }
        if ($objResult->EOF) return false;

        $text_name_id = $objResult->fields[$arrSqlName['name']];
        $strName = $objResult->fields[$arrSqlName['text']];
        if ($strName === null) {
            $objText = Text::getById($text_name_id, 0);
            $objText->markDifferentLanguage(FRONTEND_LANG_ID);
            $strName = $objText->getText();
        }
        $text_description_id = $objResult->fields[$arrSqlName['name']];
        $strDescription = $objResult->fields[$arrSqlName['text']];
        if ($strDescription === null) {
            $objText = Text::getById($text_description_id, 0);
            $objText->markDifferentLanguage(FRONTEND_LANG_ID);
            $strDescription = $objText->getText();
        }
        $objCategory = new ShopCategory(
            $strName,
            $strDescription,
            $objResult->fields['parent_id'],
            $objResult->fields['active'],
            $objResult->fields['ord'],
            $category_id
        );
        $objCategory->setPicture($objResult->fields['picture']);
        $objCategory->setFlags($objResult->fields['flags']);
        $objCategory->text_name_id = $text_name_id;
        $objCategory->text_description_id = $text_description_id;
        return $objCategory;
    }


    /**
     * Returns an array of this ShopCategory's children from the database.
     * @param   boolean $active     Only return ShopCategories with
     *                                      active==1 if true.
     *                                      Defaults to false.
     * @return  mixed                       An array of ShopCategory objects
     *                                      on success, false otherwise
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getChildCategories($active=false)
    {
        if ($this->id <= 0) return false;
        return ShopCategories::getChildCategoriesById(
            $this->id, $active
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
            SELECT id
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_categories
             WHERE parent_id=$this->id
          ORDER BY ord ASC";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        $arrShopCategoryID = array();
        while (!$objResult->EOF) {
            $arrShopCategoryID[] = $objResult->fields['id'];
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
     * //active status, if $active is true), a warning will be echo()ed.
     * //This is by design.
     * @static
     * @param   string      $strName        The child ShopCategory name
     * @param   boolean     $active         If true, only active ShopCategories
     *                                      are considered.
     * @return  mixed                       The ShopCategory on success,
     *                                      false otherwise.
     * @global  ADONewConnection  $objDatabase    Database connection object
     * @global  array
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    static function getChildNamed($strName, $active=true)
    {
        global $objDatabase;

        $query = "
           SELECT id
             FROM ".DBPREFIX."module_shop".MODULE_INDEX."_categories
            WHERE ".($active ? 'active=1 AND' : '')."
                  parent_id=$this->parent_id AND
                  catname='".addslashes($strName)."'
            ORDER BY ord ASC";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
//        if ($objResult->RecordCount() > 1) echo("ShopCategory::getChildNamed($strName, $active): ".$_ARRAYLANG['TXT_SHOP_WARNING_MULTIPLE_CATEGORIES_WITH_SAME_NAME'].'<br />');
        if (!$objResult->EOF)
            return ShopCategory::getById($objResult->fields['id']);
        return false;
    }


    /**
     * Returns an array representing a tree of ShopCategories,
     * not including the root chosen.
     *
     * The resulting array looks like:
     * array(
     *   parent ID => array(
     *     child ID => array(
     *       'ord'    => val,
     *       'active' => val,
     *       'level'  => val,
     *     ),
     *     ... more children
     *   ),
     *   ... more parents
     * )
     * @static
     * @version 1.1
     * @param   integer $parent_id          The optional root ShopCategory ID.
     *                                      Defaults to 0 (zero).
     * @param   boolean $active             Only return ShopCategories
     *                                      with active==1 if true.
     *                                      Defaults to false.
     * @param   integer $level              Optional nesting level, initially 0.
     *                                      Defaults to 0 (zero).
     * @return  array                       The array of ShopCategories,
     *                                      or false on failure.
     */
    static function getCategoryTree($parent_id=0, $active=false, $level=0)
    {
        // Get the ShopCategory's children
        $arrChildShopCategories =
            ShopCategory::getChildCategoriesById(
                $parent_id, $active
            );
        // has there been an error?
        if ($arrChildShopCategories === false) {
            return false;
        }
        // initialize root tree
        $arrCategoryTree = array();
        // local parent subtree
        $arrCategoryTree[$parent_id] = array();
        // the local parent's children
        foreach ($arrChildShopCategories as $objChildShopCategory) {
            $childCategoryId = $objChildShopCategory->getId();
            $arrCategoryTree[$parent_id][$childCategoryId] = array(
                'ord'    => $objChildShopCategory->getOrd(),
                'active' => $objChildShopCategory->getactive(),
                'level'  => $level,
            );
            // get the grandchildren
            foreach (ShopCategory::getCategoryTree(
                        $childCategoryId, $active, $level+1
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
    static function getShopCategoryMenuHierarchic($selected=0, $name='catId')
    {
        global $_ARRAYLANG;

        $result =
            ShopCategory::getShopCategoryMenuHierarchicRecurse(0, 0, $selected);
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
     * @param    integer  $parent_id    The parent ShopCategory ID.
     * @param    integer  $level        The nesting level.
     *                                  Should start at 0 (zero).
     * @param    integer  $selected     The optional selected ShopCategory ID.
     * @return   string                 The HTML code with all <option> tags,
     *                                  or the empty string on failure.
     */
    static function getShopCategoryMenuHierarchicRecurse(
        $parent_id, $level, $selected=0
    ) {
        global $objDatabase;

        $arrChildShopCategories =
            ShopCategory::getChildCategoriesById($parent_id);
        if (   !is_array($arrChildShopCategories
            || empty($arrChildShopCategories))) {
            return '';
        }
        $result = '';
        foreach ($arrChildShopCategories as $objCategory) {
            $id   = $objCategory->getId();
            $name = $objCategory->getName();
            $result .=
                "<option value='$id'".
                ($selected == $id ? HTML_ATTRIBUTE_SELECTED : '').
                '>'.str_repeat('.', $level*3).
                htmlentities($name).
                "</option>\n";
            if ($id != $parent_id) {
                $result .=
                    ShopCategory::getShopCategoryMenuHierarchicRecurse(
                        $id, $level+1, $selected);
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

        $query = "
            SELECT parent_id
              FROM ".DBPREFIX."module_shop_categories
             WHERE id=$intCategoryId
          ORDER BY ord ASC";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult || $objResult->EOF) return 0;
        return $objResult->fields['parent_id'];
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
        $index = array_search($parentShopCategoryId, $arrChildShopCategoryId);
        if (   $index === false
            || empty($arrChildShopCategoryId[$index+1])) {
            $index = -1;
        }
        return $arrChildShopCategoryId[$index+1];
    }


    static function errorHandler()
    {
        require_once(ASCMS_CORE_PATH.'/DbTool.class.php');

DBG::activate(DBG_DB_FIREPHP);

        // Fix the Text table first
        Text::errorHandler();

        $table_name = DBPREFIX.'module_shop'.MODULE_INDEX.'_categories';
        $table_structure = array(
            'id' => array('type' => 'INT(10)', 'unsigned' => true, 'auto_increment' => true, 'primary' => true, 'renamefrom' => 'catid'),
            'parent_id' => array('type' => 'INT(10)', 'unsigned' => true, 'default' => '0', 'renamefrom' => 'parentid'),
            'text_name_id' => array('type' => 'INT(10)', 'unsigned' => true, 'default' => '0', 'renamefrom' => 'catname'),
            'text_description_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'default' => null),
            'ord' => array('type' => 'INT(5)', 'unsigned' => true, 'default' => '0', 'renamefrom' => 'catsorting'),
            'active' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'default' => '1', 'renamefrom' => 'catstatus'),
            'picture' => array('type' => 'VARCHAR(255)', 'default' => ''),
            'flags' => array('type' => 'VARCHAR(255)', 'default' => ''),
        );
        $table_index =  array(
            'flags' => array(
                'fields' => 'flags',
                'type' => 'FULLTEXT',
            ),
        );

        if (DbTool::table_exists($table_name)) {
            if (DbTool::column_exists($table_name, 'catname')) {
                // Migrate all ShopCategory names to the Text table first
                Text::deleteByKey(self::TEXT_NAME);
                Text::deleteByKey(self::TEXT_DESCRIPTION);
                $objResult = DbTool::sql("
                    SELECT `catid`, `catname`
                      FROM `$table_name`");
                if (!$objResult) {
die("ShopCategory::errorHandler(): Error: failed to query catnames, code rvnla7hw");
                }
                while (!$objResult->EOF) {
                    $id = $objResult->fields['catid'];
                    $name = $objResult->fields['catname'];
                    $text_name_id = Text::replace(
                        null, FRONTEND_LANG_ID,
                        $name, MODULE_ID, self::TEXT_NAME);
                    if (!$text_name_id) {
die("ShopCategory::errorHandler(): Error: failed to migrate catname '$name', code hrdsaeru3");
                    }
                    $objResult2 = DbTool::sql("
                        UPDATE `$table_name`
                           SET `catname`='$text_name_id'
                         WHERE `catid`=$id");
                    if (!$objResult2) {
die("ShopCategory::errorHandler(): Error: failed to update ShopCategory ID $id, code t5kjfas");
                    }
                    $objResult->MoveNext();
                }
            }
        }

        if (!DbTool::table($table_name, $table_structure, $table_index)) {
die("ShopCategory::errorHandler(): Error: failed to migrate ShopCategory table, code agkjgb7ls");
        }

        // Always
        return false;
    }

}

?>
