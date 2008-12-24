<?php

/**
 * Shop Product Attribute class
 * @version     2.1.0
 * @package     contrexx
 * @subpackage  module_shop
 * @todo        Test!
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 */

/**
 * Attribute type constants
 *
 * Note that you need to update methods like getAttributeDisplayTypeMenu()
 * manually when you add another option here.
 */
define('SHOP_PRODUCT_ATTRIBUTE_TYPE_MENU_OPTIONAL',    0);
define('SHOP_PRODUCT_ATTRIBUTE_TYPE_RADIOBUTTON',      1);
define('SHOP_PRODUCT_ATTRIBUTE_TYPE_CHECKBOX',         2);
define('SHOP_PRODUCT_ATTRIBUTE_TYPE_MENU_MANDATORY',   3);
define('SHOP_PRODUCT_ATTRIBUTE_TYPE_TEXT_OPTIONAL',    4);
define('SHOP_PRODUCT_ATTRIBUTE_TYPE_TEXT_MANDATORY',   5);
define('SHOP_PRODUCT_ATTRIBUTE_TYPE_UPLOAD_OPTIONAL',  6);
define('SHOP_PRODUCT_ATTRIBUTE_TYPE_UPLOAD_MANDATORY', 7);
// Keep this up to date!
define('SHOP_PRODUCT_ATTRIBUTE_TYPE_COUNT',            8);

/**
 * @ignore
 */
require_once ASCMS_CORE_PATH.'/Text.class.php';

/**
 * Product Attribute
 *
 * These may be associated with zero or more Products.
 * Each attribute consists of a name part
 * (module_shop_products_attributes_name) and zero or more value parts
 * (module_shop_products_attributes_value).
 * Each of the values can be associated with an arbitrary number of Products
 * by inserting the respective record into the relations table
 * module_shop_products_attributes.
 * The type determines the kind of relation between a Product and the attribute
 * values, that is, whether it is optional or mandatory, and whether single
 * or multiple attributes may be chosen at a time.  See {@link ?} for details.
 * @version     2.1.0
 * @package     contrexx
 * @subpackage  module_shop
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @todo        Test!
 */
class ProductAttribute
{
    // attributes: attribute_id, product_id, attributes_name_id, attributes_value_id, sort_id
    // names:      id, name, display_type
    // values:     id, name_id, value, price

    /**
     * The ProductAttribute ID
     * @var integer
     */
    private $id = 0;

    /**
     * The associated Product ID, if any, or false
     * @var   mixed
     */
    private $productId = false;

    /**
     * The ProductAttribute name
     * @var string
     */
    private $name = '';

    /**
     * The ProductAttribute name Text ID
     * @var integer
     */
    private $text_name_id = false;

    /**
     * The ProductAttribute type
     * @var integer
     */
    private $type = 0;

    /**
     * The array of Product Attribute values
     * @var array
     */
    private $arrValue = false;

    /**
     * The array of Product Attribute relations
     * @var array;
     */
    private $arrRelation = false;

/**
     * Sorting order
     *
     * Only used by our friend, the Product class
     * @var integer
     */
    private $order;


    /**
     * Constructor
     * @param   integer   $type       The type of the ProductAttribute
     * @param   integer   $id         The optional ProductAttribute ID
     * @param   integer   $productId  The optional Product ID
     */
    function __construct($type, $id=0, $productId=false)
    {
        $this->setType($type);
        $this->id        = $id;
        $this->productId = $productId;
        if ($id)
            $this->arrValue = ProductAttributes::getValueArray($id);
        if ($productId)
            $this->arrRelation = ProductAttributes::getRelationArray($productId);
    }


    /**
     * Get the name
     * @return  string                              The name
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getName()
    {
        return $this->name;
    }
    /**
     * Set the name
     *
     * Empty name arguments are ignored.
     * @param   string          $name               The name
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function setName($name)
    {
        if (!$name) return;
        $this->name = trim(strip_tags($name));
    }

    /**
     * Get the ProductAttribute type
     * @return  integer                 The ProductAttribute type
     */
    function getType()
    {
        return $this->type;
    }
    /**
     * Set the ProductAttribute type
     * @param   integer                 The ProductAttribute type
     */
    function setType($type)
    {
        if (   $type >= SHOP_PRODUCT_ATTRIBUTE_TYPE_MENU_OPTIONAL
            && $type <  SHOP_PRODUCT_ATTRIBUTE_TYPE_COUNT) {
            $this->type = intval($type);
        }
    }

    /**
     * Get the ProductAttribute name ID
     * @return  integer                 The ProductAttribute name ID
     */
    function getId()
    {
        return $this->id;
    }
    /**
     * Set the ProductAttribute ID -- NOT ALLOWED
     */

    /**
     * Get the ProductAttribute sorting order
     *
     * Note that this is *SHOULD* only be set by our friend,
     * the Product object.
     * So if you have a ProductAttribute not actually associated to
     * a Product, you *SHOULD* always get a return value of boolean false.
     * @return  integer                 The ProductAttribute sorting order,
     *                                  or false if not applicable.
     */
    function getOrder()
    {
        return (isset($this->order) ? $this->order : false);
    }
    /**
     * Set the ProductAttribute sorting order.
     *
     * Note that you can only set this to a valid integer value,
     * not reset to false or even unset state.
     * This *SHOULD* only be set if the Attribute is indeed associated
     * with a Product, as this value will only be stored in the
     * relations table module_shop_products_attributes.
     * @param   integer                 The ProductAttribute sorting order
     */
    function setOrder($order)
    {
        if (is_integer($order)) $this->order = intval($order);
    }

    /**
     * Returns an array of values for this ProductAttribute.
     *
     * If the array has not been initialized, the method tries to
     * do so from the database.
     * The array has the form
     *  array(
     *    value ID => array(
     *      'id' => value ID,
     *      'name_id' => name ID,
     *      'value' => value name,
     *      'text_value_id' => Text ID,
     *      'price' => price,
     *    ),
     *    ... more ...
     *  );
     * For relations to the associated Product, if any, see
     * {@link getRelationArray}.
     * @access  public
     * @return  array                       Array of Product Attribute values
     *                                      upon success, false otherwise.
     * @global  ADONewConnection
     */
    function getValueArray()
    {
        if (!is_array($this->arrValue))
            $this->arrValue = ProductAttributes::getValueArray($this->id);
        return $this->arrValue;
    }
    /**
     * Set the ProductAttribute value array -- NOT ALLOWED
     * Use addValue()/deleteValueById() instead.
     */


    /**
     * Add a ProductAttribute value
     *
     * The values' ID is set when the record is stored.
     * @param   string  $value      The value description
     * @param   float   $price      The value price
     * @param   integer $order      The value order, only applicable when
     *                              associated with a Product
     * @return  boolean             True on success, false otherwise
     */
    function addValue($value, $price, $order=0)
    {
        if (   $this->type == SHOP_PRODUCT_ATTRIBUTE_TYPE_UPLOAD_OPTIONAL
            || $this->type == SHOP_PRODUCT_ATTRIBUTE_TYPE_UPLOAD_MANDATORY
            || $this->type == SHOP_PRODUCT_ATTRIBUTE_TYPE_TEXT_OPTIONAL
            || $this->type == SHOP_PRODUCT_ATTRIBUTE_TYPE_TEXT_MANDATORY) {
            // These types can have exactly one value
            $this->arrValue = array(
                array(
                    'value'   => $value,
                    'price'   => $price,
                    'order'   => $order,
                )
            );
            return true;
        }
        // Any other types can have an arbitrary number of values
        $this->arrValue[] = array(
            'value'   => $value,
            'price'   => $price,
            'order'   => $order,
        );
        return true;
    }


    /**
     * Update a ProductAttribute value.
     *
     * The value is only stored together with the object in {@link store()}
     * @param   integer   $value_id   The ProductAttribute value ID
     * @param   string    $value      The descriptive name
     * @param   float     $price      The price
     * @param   integer   $order      The order of the value, only applicable
     *                                when associated with a Product
     * @return  boolean               True on success, false otherwise
     */
    function updateValue($value_id, $value, $price, $order=0)
    {
        $this->arrValue[$value_id]['value'] = $value;
        $this->arrValue[$value_id]['price'] = $price;
        $this->arrValue[$value_id]['order'] = $order;
        // Insert into database, and update ID
        //return $this->_updateValue($this->arrValue[$value_id]);
    }


    /**
     * Remove the ProductAttribute value with the given ID from a Product.
     *
     * Note that this will not delete the value itself, but only clears the
     * association with a Product.
     * @param   integer     $value_id       The Product Attribute value ID
     * @return  boolean                     True on success, false otherwise
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function deleteValueById($value_id)
    {
        global $objDatabase;

        // Anything to be removed?
        if (empty($this->arrValue[$value_id])) return true;

        $arrValue = $this->arrValue[$value_id];
        $text_id = $arrValue['text_value_id'];
        if (!Text::deleteById($text_id)) return false;

        // Remove relations to Products
        $query = "
            DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes
            WHERE attributes_value_id=$value_id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;

        // Remove the value
        $query = "
            DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_values
            WHERE id=$value_id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        return true;
    }


    /**
     * Deletes the ProductAttribute from the database.
     *
     * Includes both the name and all of the value entries related to it.
     * As a consequence, all relations to Products referring to the deleted
     * entries are deleted, too.  See {@link Product::arrAttribute(sp?)}.
     * Keep in mind that any Products currently held in memory may cause
     * inconsistencies!
     * @return  boolean                     True on success, false otherwise.
     * @global  ADONewConnection  $objDatabase
     */
    function delete()
    {
        global $objDatabase;

        // Delete references to products first
        $query = "
            DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes
             WHERE attributes_name_id=$this->id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;

        // Delete values' Text entries
        foreach ($this->arrValue as $arrValue) {
            $objText = $arrValue['value'];
            if (!Text::deleteById($objText->getId())) return false;
        }
        // Delete values
        $query = "
            DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_value
             WHERE name_id=$this->id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        // Delete names' Text entry
        if (!Text::deleteById($this->name->getId())) return false;
        // Delete name
        $query = "
            DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_name
             WHERE id=$this->id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        unset($this);
        return true;
    }


    /**
     * Stores the ProductAttribute object in the database.
     *
     * Either updates or inserts the record.
     * @return  boolean     True on success, false otherwise
     */
    function store()
    {
        // Store Text
        $objText = Text::replace(
            $this->text_name_id, FRONTEND_LANG_ID, $this->name,
            MODULE_ID, TEXT_SHOP_PRODUCTS_ATTRIBUTES_NAME
        );
echo("replaced text: ".var_export($objText, true)."<br />");
        if (!$objText) return false;
        $this->text_name_id = $objText->getId();
        if ($this->id && $this->recordExists()) {
            if (!$this->update()) return false;
        } else {
            $this->id = 0;
            if (!$this->insert()) return false;
        }
        return $this->storeValues();
    }


    /**
     * Returns true if the record for this objects' ID exists,
     * false otherwise
     * @return  boolean                     True if the record exists,
     *                                      false otherwise
     * @global  ADONewConnection  $objDatabase
     */
    function recordExists()
    {
        global $objDatabase;

        $query = "
            SELECT 1
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_name
             WHERE id=$this->id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult || $objResult->EOF) return false;
        return true;
    }


    /**
     * Updates the ProductAttribute object in the database.
     *
     * Note that this neither updates the associated Text nor
     * the values records.  Call {@link store()} for that.
     * @return  boolean                     True on success, false otherwise
     * @global  ADONewConnection  $objDatabase
     */
    function update()
    {
        global $objDatabase;

        $query = "
            UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_name
               SET text_name_id=$this->text_name_id,
                   display_type=$this->type
             WHERE id=$this->id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        return true;
    }


    /**
     * Inserts the ProductAttribute object into the database.
     *
     * Note that this neither updates the associated Text nor
     * the values records.  Call {@link store()} for that.
     * @return  boolean                     True on success, false otherwise
     * @global  ADONewConnection
     */
    function insert()
    {
        global $objDatabase;

        $query = "
            INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_name (
                text_name_id, display_type
            ) VALUES (
                $this->text_name_id,
                $this->type
            )
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        $this->id = $objDatabase->Insert_ID();
        return true;
    }


    /**
     * Store the Attibute value records in the database
     * @return  boolean                     True on success, false otherwise
     * @global  ADONewConnection
     */
    function storeValues()
    {
        // Mind: value entries in the array may be new and have to
        // be inserted, even though the object itself has got a valid ID!
        foreach ($this->arrValue as $arrValue) { // $id is the key in the loop
            // The Text ID is not set for values that have been added
            $text_id =
                (empty($arrValue['text_value_id'])
                    ? 0 : $arrValue['text_value_id']
                );
echo("storing value: ".var_export($arrValue, true)."<br />");

            // Store Text
            $objText = Text::replace(
                $text_id, FRONTEND_LANG_ID, $arrValue['value'],
                MODULE_ID, TEXT_SHOP_PRODUCTS_ATTRIBUTES_VALUE
            );
            if (!$objText) return false;
            $arrValue['text_value_id'] = $objText->getId();
            // Note that the $id is only identical to the value ID stored
            // in $arrValue['id'] for value records already present.
            // If the value was just added to the array, the $id is just
            // an array index, and its $arrValue['id'] is empty.
            $value_id = (isset($arrValue['id']) ? $arrValue['id'] : 0);
            if ($value_id && $this->recordExistsValue($value_id)) {
                if (!$this->_updateValue($value_id)) return false;
            } else {
                // This is a temporary dummy value used to find the
                // value array in $this->arrValue.
                // Updated in _insertValue().
                //$arrValue['id'] = $id; // $id is the key in the loop
                if (!$this->_insertValue($arrValue)) return false;
            }
        }
        return true;
    }


    function _updateValue($value_id)
    {
        global $objDatabase;

        $query = "
            UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_value
               SET name_id=$this->id,
                   text_value_id=".$this->arrValue[$value_id]['text_value_id']."',
                   price=".floatval($this->arrValue[$value_id]['price']).",
             WHERE id=$value_id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        return true;
    }


    /**
     * Insert a new ProductAttribute value into the database.
     *
     * Updates the values' ID upon success.
     * @access  private
     * @param   array       $arrValue       The value array, by reference
     * @return  boolean                     True on success, false otherwise
     * @global  ADONewConnection
     */
    function _insertValue(&$arrValue)
    {
        global $objDatabase;

        $query = "
            INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_value (
                name_id, text_value_id, price
            ) VALUES (
                $this->id,
                ".$arrValue['text_value_id'].",
                ".floatval($arrValue['price'])."
            )";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        $arrValue['id'] = $objDatabase->Insert_ID();
        return true;
    }


    /**
     * Returns boolean true if the Product Attribute value record with the
     * given ID exists in the database table, false otherwise
     * @param   integer     $value_id       The Product Attribute value ID
     * @return  boolean                     True if the record exists,
     *                                      false otherwise
     */
    function recordExistsValue($value_id)
    {
        global $objDatabase;

        $query = "
            SELECT 1
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_value
             WHERE id=$value_id
        ";
        $objResult = $objDatabase->Execute($query);
        if ($objResult && $objResult->RecordCount()) return true;
        return false;
    }


    /**
     * Returns a new ProductAttribute queried by its name ID from
     * the database.
     * @param   integer     $name_id        The ProductAttribute name ID
     * @return  ProductAttribute            The ProductAttribute object
     * @global  ADONewConnection
     */
    static function getByNameId($name_id)
    {
        $arrName = ProductAttributes::getNameArray($name_id);
        if ($arrName === false) return false;
        $objProductAttribute = new ProductAttribute(
            $arrName['type'], $name_id
        );
        $objProductAttribute->setName($arrName['name']);
        return $objProductAttribute;
    }


    /**
     * Returns a new ProductAttribute queried by one of its value IDs from
     * the database.
     *
     * @param   integer     $value_id     the value ID
     */
    static function getByValueId($value_id)
    {
        // Get the associated name ID
        $name_id = ProductAttribute::getNameIdByValueId($value_id);
        return ProductAttribute::getByNameId($name_id);
    }


    /**
     * Return the name of the ProductAttribute value selected by its ID
     * from the database.
     *
     * Returns false on error, or the empty string if the value cannot be
     * found.
     * @param   integer   $id     The ProductAttribute value ID
     * @return  mixed             The ProductAttribute value name on success,
     *                            the empty string if it cannot be found,
     *                            or false otherwise.
     * @static
     * @global  mixed     $objDatabase  Database object
     */
    static function getValueNameById($id)
    {
        global $objDatabase;

        $arrSqlValue = Text::getSqlSnippets(
            'text_value_id', FRONTEND_LANG_ID,
            MODULE_ID, TEXT_SHOP_PRODUCTS_ATTRIBUTES_VALUE
        );
        $query = "
            SELECT ".$arrSqlValue['field']."
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_value".
                   $arrSqlValue['join']."
             WHERE id=$id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        if ($objResult->RecordCount() == 1)
            return $objResult->fields[$arrSqlValue['text']];
        return '';
    }


    /**
     * Return the price of the ProductAttribute value selected by its ID
     * from the database.
     *
     * Returns false on error, or the empty string if the value cannot be
     * found.
     * @param   integer   $id     The ProductAttribute value ID
     * @return  mixed             The ProductAttribute value price on success,
     *                            the empty string if it cannot be found,
     *                            or false.
     * @static
     * @global  mixed     $objDatabase  Database object
     */
    static function getValuePriceById($id)
    {
        global $objDatabase;

        // id, name_id, value, price, price_prefix (enum('+', '-'))
        $query = "
            SELECT price, price_prefix
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_value
             WHERE id=$id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        if ($objResult->RecordCount() == 1) {
            return $objResult->fields['price_prefix'].$objResult->fields['price'];
        }
        return '';
    }


    /**
     * Return the name of the ProductAttribute selected by its ID
     * from the database.
     *
     * Returns false on error, or the empty string if the name cannot be
     * found.
     * @param   integer   $id     The ProductAttribute ID
     * @return  mixed             The ProductAttribute name on success,
     *                            the empty string if it cannot be found,
     *                            or false.
     * @static
     * @global  mixed     $objDatabase  Database object
     */
    static function getNameById($id)
    {
        global $objDatabase;

        $query = "
            SELECT name
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_name
             WHERE id=$id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        if ($objResult->RecordCount() == 1) {
            return $objResult->fields['name'];
        }
        return '';
    }


    /**
     * Returns the name ID associated with the given value ID in the
     * value table.
     *
     * @static
     * @param   integer     $value_id        The value ID
     * @return  integer                     The associated name ID
     * @global  ADONewConnection
     */
    static function getNameIdByValueId($value_id)
    {
        global $objDatabase;

        $query = "
            SELECT name_id
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_value
             WHERE id=$value_id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult || $objResult->RecordCount() != 1) {
            return false;
        }
        return $objResult->fields['name_id'];
    }


    /**
     * Return the value ID corresponding to the given value name,
     * if found, false otherwise.
     *
     * If there is more than one value of the same name, only the
     * first ID found is returned, with no guarantee that it will
     * always return the same.
     * This method is awkwardly named because of the equally awkward
     * names given to the database fields.
     * @param   string      $value          The Attribute value name
     * @return  integer                     The first matching value ID found,
     *                                      or false.
     * @global  ADONewConnection
     */
    function getValueIdByName($value)
    {
        global $objDatabase;

        $query = "
            SELECT id
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_value
             WHERE value='".addslashes($value)."'
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult || $objResult->RecordCount() == 0) {
            return false;
        }
        return $objResult->fields['id'];
    }


    ///////////////////////////////////////////////////////////////////////
    // old (static) functions, taken from index.php/admin.php
    // these may be removed at will!
    ///////////////////////////////////////////////////////////////////////

    /**
     * Store new attribute option
     *
     * OBSOLETE
     *
     * @access    private
     * @return    string    $statusMessage    Status message
     */
    function _storeNewAttributeOption()
    {
        global $objDatabase, $_ARRAYLANG;

        $arrAttributeList = array();
        $arrAttributeValue = array();
        $arrAttributePrice = array();

        if (empty($_POST['optionName'][0]))
            return $_ARRAYLANG['TXT_DEFINE_NAME_FOR_OPTION'];
        if (!is_array($_POST['attributeValueList'][0]))
            return $_ARRAYLANG['TXT_DEFINE_VALUE_FOR_OPTION'];

        //$arrAttributesDb = $this->arrAttributes;
        $arrAttributeList = $_POST['attributeValueList'];
        $arrAttributeValue = $_POST['attributeValue'];
        $arrAttributePrice = $_POST['attributePrice'];

        $query = "
            INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_name (
                name
            ) VALUES (
                '".addslashes($_POST['optionName'][0])."'
            )
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult)
            return "ERROR: could not insert product attribute name into database<br />";
        $name_id = $objResult->Insert_Id();
        foreach ($arrAttributeList[0] as $id) {
            // insert new attribute value
            $query = "
                INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_value (
                    name_id, value, price
                ) VALUES (
                    $name_id, '".
                    addslashes($arrAttributeValue[$id])."', '".
                    floatval($arrAttributePrice[$id])."'
                )
            ";
            $objDatabase->Execute($query);
        }
        $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_value");
        $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_name");
        return '';
    }


////////////////////////////////////////////////////////////////////////////
// Expelled from the Product class.
// To be rewritten as ProductAttribute methods
////////////////////////////////////////////////////////////////////////////

    /**
     * Remove the ProductAttribute with the given ID from a Product.
     *
     * @param   integer     $productAttributeNameId
     *                                      The ProductAttribute name ID
     * @return  boolean                     True on success, false otherwise
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function removeProductAttributeByNameId($productAttributeNameId)
    {
        global $objDatabase;

        // fields: attribute_id, product_id, attributes_name_id, attributes_value_id, sort_id
        $query = "
            DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes
            WHERE attributes_name_id=$productAttributeNameId
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        return true;
    }


    /**
     * Delete all of a Products' ProductAttributes from the database.
     *
     * Note that the ProductAttributes are not removed from the Product
     * itself, but from the database ONLY.  This is used in order to
     * simplyfy the update process, and mandatory to make {@link makeClone()}
     * work properly.
     * @return  boolean                     True on success, false otherwise
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function deleteProductAttributes()
    {
        global $objDatabase;

        // fields: attribute_id, product_id, attributes_name_id, attributes_value_id, sort_id
        $query = "
            DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes
            WHERE product_id=$this->id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        return true;
    }


    /**
     * Remove the ProductAttribute value with the given ID from a Product.
     *
     * Note that this will not delete the value itself, but only clears the
     * association with a Product.
     * @param   integer     $productAttributeValueId
     *                                      The ProductAttribute value ID
     * @return  boolean                     True on success, false otherwise
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function removeProductAttributeValueByValueId($productAttributeValueId)
    {
        global $objDatabase;

        // fields: attribute_id, product_id, attributes_name_id, attributes_value_id, sort_id
        $query = "
            DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes
            WHERE attributes_value_id=$productAttributeValueId
              AND product_id=$this->id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        return true;
    }


    function toString()
    {
        $string = "ID: $this->id, name: $this->name, type: $this->type<br />  values:<br />";
        foreach ($this->arrValue as $value) {
            $string .=
                "    id: ".  $value['id'].
                ", value: ". $value['value'].
                ", price: ". $value['price'].
                ", prefix: ".$value['prefix'].
                "<br />";
        }
        return $string;
    }

}

?>
