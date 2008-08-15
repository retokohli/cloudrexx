<?php

/**
 * Shop Product Attribute
 *
 * @version     $Id: 0.0.1 alpha$
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
define('SHOP_PRODUCT_ATTRIBUTE_TYPE_MENU_OPTIONAL', 0);
define('SHOP_PRODUCT_ATTRIBUTE_TYPE_RADIOBUTTON', 1);
define('SHOP_PRODUCT_ATTRIBUTE_TYPE_CHECKBOX', 2);
define('SHOP_PRODUCT_ATTRIBUTE_TYPE_MENU_MANDATORY', 3);
define('SHOP_PRODUCT_ATTRIBUTE_TYPE_TEXT_OPTIONAL', 4);
define('SHOP_PRODUCT_ATTRIBUTE_TYPE_TEXT_MANDATORY', 5);
define('SHOP_PRODUCT_ATTRIBUTE_TYPE_UPLOAD_OPTIONAL', 6);
define('SHOP_PRODUCT_ATTRIBUTE_TYPE_UPLOAD_MANDATORY', 7);
// Keep this up to date!
define('SHOP_PRODUCT_ATTRIBUTE_TYPE_COUNT', 8);

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
 * @version     $Id: 0.0.1 alpha$
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
    // values:     id, name_id, value, price, price_prefix

    /**
     * The ProductAttribute ID
     * @var integer
     */
    private $id;

    /**
     * The associated Product ID, if any, or false
     * @var   mixed
     */
    private $productId = false;

    /**
     * The ProductAttribute name
     * @var string
     */
    private $name;

    /**
     * The ProductAttribute type
     * @var integer
     */
    private $type;

    /**
     * The ProductAttribute array of values
     * @var array
     */
    private $arrValues;

    /**
     * The value index
     *
     * The array has the form
     * array ( ID => index, ... ),
     * where ID is the ID of the Attribute value, and index is its
     * offset in the ProductAttribute array of values ($arrValues object variable)
     * @var   array
     */
    private $arrValueIndex;

    /**
     * Sorting order
     *
     * Only used by our friend, the Product class
     * @var integer
     */
    private $order;


    /**
     * Constructor
     */
    function __construct($name, $type, $id=0, $productId=false)
    {
        $this->name      = $name;
        $this->setType($type);
        $this->id        = $id;
        $this->productId = $productId;
        $this->arrValues  = array();
/*
        if (!$this->id) {
            $this->store();
        }
*/
    }


    /**
     * Add a ProductAttribute value to a Product.
     *
     * The optional argument determines the sorting order of the value.
     * @static
     * @param   integer     $productId      The Product ID
     * @param   integer     $valueId        The ProductAttribute value ID
     * @param   integer     $order          The optional sorting order
     * @return  boolean                     True on success, false otherwise
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function addValueToProduct($valueId, $productId, $order)
    {
        global $objDatabase;

        // fields: attribute_id, product_id, attributes_name_id, attributes_value_id, sort_id
        $query = "
            INSERT INTO ".DBPREFIX."module_shop_products_attributes (
                product_id,
                attributes_name_id,
                attributes_value_id,
                sort_id
            ) VALUES (
                $productId,
                ".ProductAttribute::getNameIdByValueId($valueId).",
                $valueId,
                $order
            )
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        return true;
    }


    /**
     * Get the ProductAttribute name
     * @return  string                  The ProductAttribute name
     */
    function getName()
    {
        return $this->name;
    }
    /**
     * Set the ProductAttribute name
     * @param   string                  The ProductAttribute name
     */
    function setName($name)
    {
        $this->name = $name;
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
     * a Product, you'll get a return value of boolean false.
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
        if (is_integer($order)) {
            $this->order = intval($order);
        }
    }

    /**
     * Returns an array of values for this ProductAttribute.
     *
     * Set the $flagReuse parameter to false after any Attribute properties
     * have been changed.
     * If the $productId object variable is greater than zero,
     * only values associated with that Product are returned,
     * all values found in the database otherwise.
     * Note that the indices of the resulting array start at 1 (one)!
     * @access  public
     * @param   boolean     $flagReuse      If true, returns the previously
     *                                      initialized array, if any.
     *                                      Reinitializes the array otherwise.
     * @param   integer     $productId      The optional Product ID
     * @return  array                       Array of ProductAttribute values
     *                                      upon success, false otherwise.
     * @global  ADONewConnection
     */
    function getValueArray($flagReuse=false)
    {
        global $objDatabase;

        if ($flagReuse && $this->arrValues) {
            return $this->arrValues;
        }

        $query = "
            SELECT *
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_value
              WHERE name_id=$this->id
            ".($this->productId
              ? "INNER JOIN ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes
                    ON attributes_value_id=id
                 WHERE product_id=$this->productId
                 ORDER BY sort_id ASC "
              : ''
            );
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        $this->arrValues     = array();
        $this->arrValueIndex = array();
        $index = 0;
        while (!$objResult->EOF) {
            $arrValue = array();
            $arrValue['id']     = $objResult->fields['id'];
            $arrValue['nameId'] = $objResult->fields['name_id'];
            $arrValue['value']  = $objResult->fields['value'];
            $arrValue['price']  = $objResult->fields['price'];
            $arrValue['prefix'] = $objResult->fields['price_prefix'];
            $arrValue['order']  =
                ($this->productId ? $objResult->fields['sort_id'] : 0);
            $this->arrValues[++$index] = $arrValue;
            $this->arrValueIndex[$arrValue['id']] = $index;
            $objResult->MoveNext();
        }
        return $this->arrValues;
    }
    /**
     * Set the ProductAttribute value array -- NOT ALLOWED
     * Use addValue()/deleteValueById() instead.
     */


    /**
     * Returns an array with the values of ProductAttribute value records
     * for the given name, value and/or product ID.
     *
     * If the $productId parameter is greater than zero,
     * only values associated with that Product are returned.
     * Any zero arguments are ignored.
     * @static
     * @access  public
     * @param   integer     $nameId         The optional ProductAttribute name ID
     * @param   integer     $valueId        The optional ProductAttribute value ID
     * @param   integer     $productId      The optional Product ID
     * @return  array                       Array of ProductAttribute values
     *                                      upon success, false otherwise.
     */
    static function getValueArrayById($nameId=0, $valueId=0, $productId=0)
    {
        global $objDatabase;

        $query = "
            SELECT *
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_value
            ".
            ($productId
              ? "INNER JOIN ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes
                    ON attributes_value_id=id"
              : ''
            )." WHERE 1".
            ($valueId ? " AND id=$valueId" : '').
            ($nameId ? " AND name_id=$nameId" : '').
            ($productId ? " AND product_id=$productId ORDER BY sort_id ASC" : '');
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        $arrValues = array();
        $index = 0;
        while (!$objResult->EOF) {
            $arrValue = array();
            $arrValue['id']     = $objResult->fields['id'];
            $arrValue['nameId'] = $objResult->fields['name_id'];
            $arrValue['value']  = $objResult->fields['value'];
            $arrValue['price']  = $objResult->fields['price'];
            $arrValue['prefix'] = $objResult->fields['price_prefix'];
            $arrValue['order']  =
                ($productId ? $objResult->fields['sort_id'] : 0);
            $arrValues[++$index] = $arrValue;
            $objResult->MoveNext();
        }
        return $arrValues;
    }


    /**
     * Returns the name of a ProductAttribute value from the database
     * @static
     * @access  public
     * @param   integer     $valueId        The ProductAttribute value ID
     * @return  mixed                       The ProductAttribute value name
     *                                      on success, false otherwise.
     */
    static function getValueNameById($valueId)
    {
        global $objDatabase;

        $query = "
            SELECT value
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_value
             WHERE id=$valueId
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult || $objResult->EOF) {
            return false;
        }
        return $objResult->fields['value'];
    }


    /**
     * Add a ProductAttribute value
     *
     * The values' ID is updated when the record is stored by
     * {@link _insertValue()}.
     * @param   string  $value      The ProductAttribute value description
     * @param   float   $price      The ProductAttribute value price
     * @param   string  $prefix     The ProductAttribute value price prefix ('+' or '-')
     * @param   mixed   $order      The sorting order
     *                              (used in relation with a product only)
     * @return  boolean             True on success, false otherwise
     */
    function addValue($value, $price, $prefix, $order=0, $id='')
    {
        if (   $this->type == SHOP_PRODUCT_ATTRIBUTE_TYPE_UPLOAD_OPTIONAL
            || $this->type == SHOP_PRODUCT_ATTRIBUTE_TYPE_UPLOAD_MANDATORY) {
            // These types cannot have any values
            return false;
        }
        if (   $this->type == SHOP_PRODUCT_ATTRIBUTE_TYPE_TEXT_OPTIONAL
            || $this->type == SHOP_PRODUCT_ATTRIBUTE_TYPE_TEXT_MANDATORY) {
            // These types can have exactly one value
            $this->arrValues = array(
                array(
                    'value'   => $value,
                    'price'   => $price,
                    'prefix'  => $prefix,
                    'order'   => $order,
                    'id'      => $id,          // changed by insertValue()
                )
            );
            return ProductAttribute::rebuildValueIndex();
        }
        // Any other types can have an arbitrary number of values
        $this->arrValues[] = array(
            'value'   => $value,
            'price'   => $price,
            'prefix'  => $prefix,
            'order'   => $order,
            'id'      => $id,          // changed by insertValue()
        );
        return ProductAttribute::rebuildValueIndex();
    }


    /**
     * Add a ProductAttribute value to the database
     * @param   string  $value      The ProductAttribute value description
     * @param   float   $price      The ProductAttribute value price
     * @param   string  $prefix     The ProductAttribute value price prefix ('+' or '-')
     * @return  mixed               The ID of the value added on success,
     *                              false otherwise
     */
    function addValueToNameId($nameId, $value, $price, $prefix, $id='')
    {
        $objProductAttribute = ProductAttribute::getByNameId($nameId);
        if (!$objProductAttribute) return false;
        $objProductAttribute->addValue($value, $price, $prefix);
        $arrValue = array(
            'value'   => $value,
            'price'   => $price,
            'prefix'  => $prefix,
            'id'      => $id,          // changed by insertValue()
        );
        $objProductAttribute->insertValue($arrValue, $nameId);
        return $arrValue['id'];
    }


    /**
     * Update a ProductAttribute value.
     *
     * Updates the value in the database as well.
     * If the value with the given value ID does not exists, returns false.
     * @param   integer   $valueId    The ProductAttribute value ID
     * @param   string    $value      The descriptive name
     * @param   float     $price      The price
     * @param   string    $prefix     The price prefix
     * @param   integer   $order      The order of the value
     * @return  boolean               True on success, false otherwise
     */
    function updateValue($valueId, $value, $price, $prefix, $order)
    {
        // fields: id, name_id, value, price, price_prefix (enum('+', '-'))
        $index = $this->arrValueIndex[$valueId];
        if ($index === false) return false;
        $this->arrValues[$index]['value']  = $value;
        $this->arrValues[$index]['price']  = $price;
        $this->arrValues[$index]['prefix'] = $prefix;
        $this->arrValues[$index]['order']  = $order;
        // insert into database, and update ID
        return $this->_updateValue($this->arrValues[$index]);
    }


    /**
     * Removes the ProductAttribute value with the given value ID from
     * the database and the values array, if found.
     *
     * The values' ID will be set upon storing a ProductAttribute.
     * Therefore, this function will only work after a new ProductAttribute
     * has been stored, or if an existing object it was read from the database.
     * False will be returned both if the value cannot be deleted from the
     * database, and if it cannot be found in the values array.
     * @param   integer     $id     The ProductAttribute' value ID
     * @return  boolean             True on success, false otherwise.
     */
    function deleteValueById($id)
    {
        foreach ($this->arrValues as $index => $value) {
            if ($value['id'] == $id) {
                // delete from the database
                if (!ProductAttribute::_deleteValueById($id)) {
                    return false;
                }
                // delete from the array
                unset($this->arrValues[$index]);
                return true;
            }
        }
        // not found
        return false;
    }


    /**
     * Deletes the ProductAttribute value with the given value ID
     * from the database.
     *
     * Remember to update or reinitialize the value array
     * after deleting any values!
     * @static
     * @param   integer     $id             The ProductAttribute value ID
     * @global  ADONewConnection  $objDatabase    Database connection object
     * @return  boolean                     True on success, false otherwise.
     */
    static function _deleteValueById($id)
    {
        global $objDatabase;

        $query = "
            DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_value
                  WHERE id=$id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        return true;
    }


    /**
     * Rebuilds the arrValueIndex array
     *
     * Called whenever the arrValue array is modified.
     * @return  boolean             True if arrValue is initialized as an array,
     *                              false otherwise
     */
    function rebuildValueIndex()
    {
        if (!is_array($this->arrValues)) return false;
        $this->arrValueIndex = array();
        foreach ($this->arrValues as $index => $arrValue) {
            $this->arrValueIndex[$arrValue['id']] = $index;
        }
        return true;
    }


    /**
     * Deletes the ProductAttribute from the database.
     *
     * Includes both the name and all of the value entries related to it.
     * As a consequence, all Attribute entries referring to the deleted
     * entries are deleted, too.  See {@link Product::arrAttribute(sp?)}.
     * Keep in mind that any Products currently held in memory may cause
     * inconsistencies!  To be on the safe side, remove relations between
     * Products and these ProductAttribute first!
     * @global  ADONewConnection  $objDatabase    Database connection object
     * @return  boolean                 True on success, false otherwise.
     */
    function delete()
    {
        global $objDatabase;

        // delete referring attributes
        $query = "
            DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes
                  WHERE attributes_name_id=$this->id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        // delete values
        $query = "
            DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_value
                  WHERE name_id=$this->id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        // delete name
        $query = "
            DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_name
                  WHERE id=$this->id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        return true;
    }


    /**
     * Stores the ProductAttribute object in the database.
     *
     * Either updates (id > 0) or inserts (id <= 0) the object.
     * @return  boolean     True on success, false otherwise
     */
    function store()
    {
        if ($this->id > 0) {
            return $this->update();
        }
        return $this->insert();
    }


    /**
     * Updates the ProductAttribute object in the database.
     *
     * Also updates (or inserts) all value entries contained.
     * @return  boolean     True on success, false otherwise
     * @global  ADONewConnection
     */
    function update()
    {
        global $objDatabase;

        $query = "
            UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_name
               SET name='".addslashes($this->name)."',
                   display_type=$this->type
             WHERE id=$this->id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        foreach ($this->arrValues as $value) {
            if (!$this->_updateValue($value)) {
                return false;
            }
        }
        return true;
    }


    /**
     * Update the Attibute value record in the database
     *
     * The value array is passed by reference, as the ID may be updated
     * in case it had not been set and {@link _insertValue()} was called.
     * @param   array       $arrValue       The value array
     * @return  boolean                     True on success, false otherwise
     * @global  ADONewConnection  $objDatabase    Database connection object
     */
    function _updateValue(&$arrValue)
    {
        global $objDatabase;

        // mind: value entries in the array may be *new* and have to
        // be inserted, even though the object itself has got a valid ID!
        $query = "
            UPDATE ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_value
               SET name_id=$this->id,
                   value='".addslashes($arrValue['value'])."',
                   price=".floatval($arrValue['price']).",
                   price_prefix='".$arrValue['prefix']."'
             WHERE id=".$arrValue['id'];
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        // not updated? -- insert!
        if ($objDatabase->Affected_Rows() == 0) {
            return $this->_insertValue($arrValue);
        }
        return true;
    }


    /**
     * Inserts the ProductAttribute object into the database.
     *
     * Also inserts all value entries contained.
     * @return  boolean     True on success, false otherwise
     * @global  ADONewConnection
     */
    function insert()
    {
        global $objDatabase;

        $query = "
            INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_name (
                name, display_type
            ) VALUES (
                '".addslashes($this->name)."',
                $this->type
            )
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        $this->id = $objDatabase->Insert_ID();

        foreach ($this->arrValues as $value) {
            if (!$this->_insertValue($value)) {
                return false;
            }
        }
        return true;
    }


    /**
     * Insert a new ProductAttribute value into the database.
     *
     * Updates the values' ID upon success.
     * @access  private
     * @param   array       $value          The value array, by reference
     * @return  boolean                     True on success, false otherwise
     * @global  ADONewConnection  $objDatabase    Database connection object
     */
    function _insertValue(&$arrValue)
    {
        global $objDatabase;

        $query = "
            INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_value (
                name_id, value, price, price_prefix
            ) VALUES (
                $this->id,
                '".addslashes($arrValue['value'])."',
                ".floatval($arrValue['price']).",
                '".addslashes($arrValue['prefix'])."')";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        $arrValue['id'] = $objDatabase->Insert_ID();
        return true;
    }


    /**
     * Returns a new ProductAttribute queried by its name ID from
     * the database.
     *
     * The optional argument must only contain valid ProductAttribute
     * value IDs, or be empty.
     * @param   integer     $nameId         The ProductAttribute name ID
     * @param   array       $arrProductAttributeValueId
     *                                      An optional array of
     *                                      ProductAttribute value IDs
     * @return  ProductAttribute            The ProductAttribute object
     * @global  ADONewConnection  $objDatabase    Database connection object
     */
    //static
    function getByNameId($nameId, $arrProductAttributeValueId='')
    {
        global $objDatabase;

        // fields: id, name, display_type
        $query = "
            SELECT name, display_type
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_name
             WHERE id=$nameId
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        // new ($name, $type, $id='')
        if (!$objResult->EOF) {
            $objProductAttribute = new ProductAttribute(
                $objResult->fields['name'],
                $objResult->fields['display_type'],
                $nameId
            );

            // fields: id, name_id, value, price, price_prefix (enum('+', '-'))
            // get selected or all associated values
            $query = "
                SELECT id, value, price, price_prefix
                  FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_value
                 WHERE name_id=$nameId".
                (is_array($arrProductAttributeValueId)
                    ? ' AND id IN ('.join(', ', $arrProductAttributeValueId).')'
                    : ''
                )." ORDER BY value ASC";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) {
                return false;
            }
            while (!$objResult->EOF) {
                $objProductAttribute->addValue(
                    $objResult->fields['value'],
                    $objResult->fields['price'],
                    $objResult->fields['price_prefix'],
                    $objResult->fields['id']
                );
                $objResult->MoveNext();
            }
            return $objProductAttribute;
        }
        return false;
    }


    /**
     * Returns the name of a ProductAttribute from the database.
     * @param   integer     $nameId         The ProductAttribute name ID
     * @return  mixed                       The ProductAttribute name on
     *                                      success, false otherwise
     * @global  ADONewConnection  $objDatabase    Database connection object
     */
    static function getNameById($nameId)
    {
        global $objDatabase;

        // fields: id, name, display_type
        $query = "
            SELECT name
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_name
             WHERE id=$nameId
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult || $objResult->EOF) {
            return false;
        }
        return $objResult->fields['name'];
    }


    /**
     * Returns a new ProductAttribute queried by one of its value IDs from
     * the database.
     *
     * @param   integer     $valueId     the value ID
     */
    //static
    function getByValueId($valueId)
    {
        // get associated name ID
        $nameId = ProductAttribute::getNameIdByValueId($valueId);
        return ProductAttribute::getByNameId($nameId);
    }


    /**
     * OBSOLETE
     * Return an array of Attribute value IDs related to the given
     * Product ID.
     *
     * The array has the form
     * array(
     *  Index => array(
     *      valueId => value ID,
     *      order   => sorting order,
     *  ),
     *  ...
     * @param   integer     $productId      The Product ID
     * @return  mixed                       The array of Attribute value IDs
     *                                      upon success, false otherwise
     * @global  ADONewConnection  $objDatabase    Database connection object
    static function getValueIdArray($productId)
    {
        global $objDatabase;

        $query = "
            SELECT attributes_value_id, sort_id
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes
             WHERE product_id=$productId
          ORDER BY sort_id ASC
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        $arrValueId = array();
        while (!$objResult->EOF) {
            $arrValueId[] = array(
                'valueId' => $objResult->fields['attributes_value_id'],
                'order'   => $objResult->fields['sort_id'],
            );
            $objResult->MoveNext();
        }
        return $arrValueId;
    }
     */


    /*
     * OBSOLETE
     * Returns an array of ProductAttribute value IDs.
     *
     * If the optional $productId argument is greater than zero,
     * only IDs associated with this Product are returned,
     * all value IDs found in the database otherwise.
     * Note that this exact information has to be stored in the Product object
     * to enable Product cloning along with ProductAttributes.
     * @static
     * @access  public
     * @param   integer     $productId      The optional Product ID
     * @return  array                       Array of ProductAttribute value IDs
    static function getValueIdArray($productId=0)
    {
        global $objDatabase;

        $query = "
            SELECT id
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_value
            ($productId ? "
        INNER JOIN ".DBPREFIX."module_shop_products_attributes
                ON attributes_value_id=id
             WHERE product_id=$productId" : '')."
             ORDER BY id ASC
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        $arrValueId = array();
        while (!$objResult->EOF) {
            $arrValueId[] = $objResult->fields['id'];
            $objResult->MoveNext();
        }
        return $arrValueId;
    }
     */


    /**
     * Returns the name ID associated with the given value ID in the
     * value table.
     *
     * @static
     * @param   integer     $valueId        The value ID
     * @return  integer                     The associated name ID
     * @global  ADONewConnection
     */
    // static
    function getNameIdByValueId($valueId)
    {
        global $objDatabase;

        // fields: id, name_id, value, price, price_prefix (enum('+', '-'))
        $query = "
            SELECT name_id
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_value
             WHERE id=$valueId
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult || $objResult->RecordCount() != 1) {
            return false;
        }
        return $objResult->fields['name_id'];
    }


    /**
     * Returns a values index in the value array by its ID.
     *
     * Somewhat obsolete; you should use the arrValueIndex array instead.
     * @param   integer   $valueId    The ProductAttribute value ID
     * @return  mixed                 The index in the value array, if found,
     *                                false otherwise
     */
    function getValueIndexByValueId($valueId)
    {
        foreach ($this->arrValues as $index => $arrValue) {
            if ($arrValue['id'] == $valueId) return $index;
        }
        return false;
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
     * @global  ADONewConnection  $objDatabase    Database connection object
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


    /**
     * OBSOLETE -- Returns the array of all available Attributes.
     *
     * If the optional $productId argument is greater than zero,
     * an additional 'status' field in the values array of the
     * ProductAttribute object is set according to whether it is
     * associated with the Product or not.
     *
     * @access  public
     * @param   integer     $productId      The optional Product ID
     * @global  ADONewConnection
     */
    function getAttributeArray($productId=0)
    {
        global $objDatabase;

        // get attributes
        $query = "
            SELECT name.id AS nameId,
                   name.name,
                   name.display_type,
                   value.id AS valueId,
                   value.value,
                   value.price,
                   value.price_prefix
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_name name
        INNER JOIN ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_value value
                ON value.name_id = name.id
          ORDER BY name ASC, value ASC
        ";

        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        $order = false;
        $arrProductAttribute = array();
        while (!$objResult->EOF) {
            $nameId = $objResult->fields['nameId'];
            if (!isset($arrProductAttribute[$nameId]['name'])) {
                $arrProductAttribute[$nameId]['name'] =
                    $objResult->fields['name'];
                $arrProductAttribute[$nameId]['type'] =
                    $objResult->fields['display_type'];
                $arrProductAttribute[$nameId]['selected'] = false;
            }
            $valueId = $objResult->fields['valueId'];
            if ($productId > 0) {
                // returns false if the two are not related,
                // the sorting order otherwise
                $order = Product::GetValueOrderByProductId(
                    $valueId, $productId
                );
                if ($order !== false) {
                    // set selected flag for entire ProductAttribute
                    $arrProductAttribute[$nameId]['selected'] = true;
                }
            }
            $arrProductAttribute[$nameId]['values'][$valueId] =
                array(
                    'id'       => $valueId,
                    'value'    => $objResult->fields['value'],
                    'price'    => $objResult->fields['price'],
                    'prefix'   => $objResult->fields['price_prefix'],
                    'selected' => ($order === false ? 0 : 1),
                );
            // use order value from last value record
            // boolean false, or integer!
            $arrProductAttribute[$nameId]['order'] = intval($order);
            $objResult->MoveNext();
        }
        return $arrProductAttribute;
    }


    //static
    function getAttributeDisplayTypeMenu($attributeId, $displayTypeId='0')
    {
        global $_ARRAYLANG;

        return "<select name='attributeDisplayType[".$attributeId."]' size='1' style='width:170px;'>\n".
            "<option value='0' ".($displayTypeId == '0' ? "selected='selected'" : '').">".$_ARRAYLANG['TXT_MENU_OPTION']."</option>\n".
            "<option value='3' ".($displayTypeId == '3' ? "selected='selected'" : '').">".$_ARRAYLANG['TXT_SHOP_MENU_OPTION_DUTY']."</option>\n".
            "<option value='1' ".($displayTypeId == '1' ? "selected='selected'" : '').">".$_ARRAYLANG['TXT_RADIOBUTTON_OPTION']."</option>\n".
            "<option value='2' ".($displayTypeId == '2' ? "selected='selected'" : '').">".$_ARRAYLANG['TXT_CHECKBOXES_OPTION']."</option>\n".
            "<option value='4' ".($displayTypeId == '4' ? "selected='selected'" : '').">".$_ARRAYLANG['TXT_SHOP_PRODUCTATTRIBUTE_TYPE_TEXT']."</option>\n".
            "<option value='5' ".($displayTypeId == '5' ? "selected='selected'" : '').">".$_ARRAYLANG['TXT_SHOP_PRODUCTATTRIBUTE_TYPE_UPLOAD']."</option>\n".
            "</select>\n";
    }


    /**
     * Generate a list of the inputboxes with the values of an attribute option
     *
     * @static
     * @access   private
     * @param    integer     $attributeId    ID of the ProductAttribute name
     * @param    string      $name           Name of the inputbox
     * @param    string      $content        ProductAttribute value field
     * @param    integer     $maxlength      Maximum length of the inputbox
     * @param    string      $style          CSS style declaration for the inputbox
     * @return   string      $inputBoxes     String with HTML code for the inputboxes
     */
    //static
    function getAttributeInputBoxes($attributeId, $name, $content, $maxlength, $style='')
    {
        $inputBoxes = '';
        $select     = true;

        $objProductAttribute = ProductAttribute::getByNameId($attributeId);
        foreach ($objProductAttribute->arrValue as $id => $arrValue) {
            $inputBoxes .= "<input type='text' name='".$name."[$id]' ".
                "id='".$name."[$id]' value='$arrValue[$content]' ".
                "maxlength='$maxlength' style='display:".
                ($select == true ? "inline" : "none").
                ";$style' onchange='updateAttributeValueList($attributeId, $id)' />";
            $select = false;
        }
        return $inputBoxes;
    }


    /**
     * Returns HTML code for the ProductAttribute price prefix menus
     *
     * @static
     * @param    integer     $attributeId    ID of the ProductAttribute name
     * @param    string      $name           Name of the menu
     * @param    string      $pricePrefix    Price prefix of the ProductAttribute value
     * @return   string      $menu           The HTML code for the price prefix
     */
    function _getAttributePricePrefixMenu($attributeId, $name, $arrValues)
    {
        $select = true;
        $menu = '';
        foreach ($arrValues as $id => $arrValue) {
            $menu .=
                '<select style="width:50px;display:'.
                ($select == true ? 'inline' : 'none').
                ';" name="'.$name.'['.$id.']" id="'.$name.'['.$id.']" size="1"'.
                "onchange=\"updateAttributeValueList($attributeId)\">\n".
                '<option value="+" '.
                ($arrValue['price_prefix'] != '-' ? 'selected="selected"' : '').
                ">+</option>\n".
                '<option value="-" '.
                ($arrValue['price_prefix'] == '-' ? 'selected="selected"' : '').
                ">-</option>\n".
                "</select>\n";
            if ($select) {
                $select = false;
            }
        }
        return $menu;
    }
/*
    function getAttributePricePrefixMenu($attributeId, $name, $pricePrefix='+')
    {
        $select = true;
        $menu = '';
        foreach (ProductAttribute::getByNameId($attributeId) as $objProductAttribute) {

            $menu .= "<select style='width:50px;display:".
                ($select ? 'inline' : 'none').
                ";' name='{$name}[$attributeId]' id='{$name}[$attributeId]' size='1'>\n".
                "<option value='+' ".
                ($select && $pricePrefix != '-' ? "selected='selected'" : '').
                ">+</option>\n".
                "<option value='-' ".
                ($select && $pricePrefix == '-' ? "selected='selected'" : '').
                ">-</option>\n".
                "</select>\n";
            $select = false;
        }
        return $menu;
    }
*/


    /**
     * Returns HTML code for the value menu for each ProductAttribute
     *
     * @param    integer     $attributeId    ID of the ProductAttribute name
     * @param    string      $name           Name of the menu
     * @param    integer     $selectedId     ID of the selected value
     * @param    string      $onchange       Javascript onchange event of the menu
     * @param    string      $style          CSS style declaration for the menu
     * @return   string      $menu           Contains the value menus
     */
    function getAttributeValueMenu($attributeId, $name, $selectedId, $onchange, $style)
    {
        global $_ARRAYLANG;

        $objProductAttribute = ProductAttribute::getByNameId($attributeId);
        if (!$objProductAttribute) {
            return '';
        }
        if (!$selectedId) {
            $selectedId = $objProductAttribute->arrValue[0]['id'];
        }
        $menu =
            '<select name="'.$name.'[]" id="'.$name.'[]" size="1" '.
            'onchange="'.$onchange.'" style="'.$style."\">\n";
        if (is_array($objProductAttribute->arrValue)) {
            foreach ($objProductAttribute->arrValue as $arrValue) {
                $id = $arrValue['id'];
                $menu .=
                    '<option value="'.$id.'" '.
                    ($selectedId == $id ? 'selected="selected"' : '').'>'.
                $arrValue['value'].' ('.
                $arrValue['prefix'].$arrValue['price'].
                " $this->defaultCurrency)</option>\n";
            }
        }
        $menu .=
            "</select><br />\n".
            '<a href="javascript:void(0);" '.
            'id="attributeValueMenuLink['.$attributeId.']" '.
            'style="display:none;" '.
            'onclick="removeSelectedValues('.$attributeId.')" '.
            'title="'.$_ARRAYLANG['TXT_SHOP_REMOVE_SELECTED_VALUE'].'" '.
            'alt="'.$_ARRAYLANG['TXT_SHOP_REMOVE_SELECTED_VALUE'].'">'.
            $_ARRAYLANG['TXT_SHOP_REMOVE_SELECTED_VALUE'].
            "</a>\n";
        return $menu;
    }


    /**
     * Returns a string containing Javascript variables of the attributes
     *
     * @static
     * @access    private
     * @return    string    $jsVars    Javascript variables list
     */
    //static
    function getAttributeJSVars()
    {
        $jsVars = '';
        foreach (ProductAttribute::getAttributeArray() as $attributeId => $arrValues) {
            reset($arrValues['values']);
            $arrValue = current($arrValues['values']);
            $jsVars .= "attributeValueId[$attributeId] = ".$arrValue['id'].";\n";
        }
        return $jsVars;
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
echo("WARNING: obsolete static method ProductAttribute::_storeNewAttributeOption() called<br />");
        global $objDatabase;

        //$statusMessage = "";
        $arrAttributeList = array();
        $arrAttributeValue = array();
        $arrAttributePrice = array();
        $arrAttributePricePrefix = array();


        if (empty($_POST['optionName'][0])) {
            return "Sie müssen einen Namen für die Option setzen\n";
        } elseif (!is_array($_POST['attributeValueList'][0])) {
            return "Sie müssen mindestens einen Wert für die Option definieren\n";
        }

        //$arrAttributesDb = $this->arrAttributes;
        $arrAttributeList = $_POST['attributeValueList'];
        $arrAttributeValue = $_POST['attributeValue'];
        $arrAttributePrice = $_POST['attributePrice'];
        $arrAttributePricePrefix = $_POST['attributePricePrefix'];

        $query = "INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_name (name)
            VALUES ('".addslashes($_POST['optionName'][0])."')";

        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return "ERROR: could not insert product attribute name into database<br />";
        }
        $nameId = $objResult->Insert_Id();

        foreach ($arrAttributeList[0] as $id) {
            // insert new attribute value
            $query = "
                INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_value
                (name_id, value, price, price_prefix) VALUES
                ($nameId, '".
                addslashes($arrAttributeValue[$id])."', '".
                floatval($arrAttributePrice[$id])."', '".
                addslashes($arrAttributePricePrefix[$id])."')
            ";
            $objDatabase->Execute($query);
        }

        $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_value");
        $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_name");

        return '';
    }


    /**
     * Update the attribute option/value list
     *
     * OBSOLETE
     *
     * @access    private
     * @return    string    $statusMessage    Status message
     */
    function _updateAttributeOptions()
    {
echo("WARNING: obsolete static method ProductAttribute::_updateAttributeOptions() called<br />");
        global $objDatabase;

        $statusMessage = "";

        $arrAttributesDb = array();
        $arrAttributeList = array();
        $arrAttributeValue = array();
        $arrAttributePrice = array();
        $arrAttributePricePrefix = array();

        $arrAttributesDb = $this->arrAttributes;
        $arrAttributeName = $_POST['optionName'];
        $arrAttributeList = $_POST['attributeValueList'];
        $arrAttributeValue = $_POST['attributeValue'];
        $arrAttributePrice = $_POST['attributePrice'];
        $arrAttributePricePrefix = $_POST['attributePricePrefix'];

        // update attribute names
        foreach ($arrAttributeName as $id => $name) {
            if (isset($arrAttributesDb[$id])) {
                if ($name != $arrAttributesDb[$id]['name']) {
                    $query = "UPDATE ".DBPREFIX."module_shop_products_attributes_name Set name='".addslashes($name)."' WHERE id=".intval($id);
                    $objDatabase->Execute($query);
                }
            }
        }

        foreach ($arrAttributeList as $attributeId => $arrAttributeValueIds) {
            foreach ($arrAttributeValueIds as $id) {
                if (isset($arrAttributesDb[$attributeId]['values'][$id])) {
                    // update attribute value
                    $updateString = "";
                    if ($arrAttributeValue[$id] != $arrAttributesDb[$attributeId]['values'][$id]['value']) {
                        $updateString .= "value = '".addslashes($arrAttributeValue[$id])."', ";
                    }
                    if ($arrAttributePrice[$id] != $arrAttributesDb[$attributeId]['values'][$id]['price']){
                        $updateString .= " price = '".floatval($arrAttributePrice[$id])."', ";
                    }
                    if ($arrAttributePricePrefix[$id] != $arrAttributesDb[$attributeId]['values'][$id]['price_prefix']) {
                        $updateString .= " price_prefix = '".addslashes($arrAttributePricePrefix[$id])."', ";
                    }
                    if (strlen($updateString)>0) {
                        $query = "UPDATE ".DBPREFIX."module_shop_products_attributes_value Set ".substr($updateString,0,strlen($updateString)-2)." WHERE id=".$id;
                        $objDatabase->Execute($query);
                    }
                } else {
                    // insert new attribute value
                    $query = "INSERT INTO ".DBPREFIX."module_shop_products_attributes_value (name_id, value, price, price_prefix) VALUES (".intval($attributeId).", '".addslashes($arrAttributeValue[$id])."', '".floatval($arrAttributePrice[$id])."', '".addslashes($arrAttributePricePrefix[$id])."')";
                    $objDatabase->Execute($query);
                }
                unset($arrAttributesDb[$attributeId]['values'][$id]);
            }
        }

        foreach ($arrAttributesDb as $arrAttributes) {
            foreach ($arrAttributes['values'] as $arrValue) {
                $query = "DELETE FROM ".DBPREFIX."module_shop_products_attributes WHERE attributes_value_id=".intval($arrValue['id']);
                if ($objDatabase->Execute($query)) {
                    $query = "DELETE FROM ".DBPREFIX."module_shop_products_attributes_value WHERE id=".intval($arrValue['id']);
                    $objDatabase->Execute($query);
                }
            }
        }

        // delete the option if it has no options
        $arrAttributeKeys = array_keys($this->arrAttributes);
        foreach ($arrAttributeKeys as $attributeId) {
            if (!array_key_exists($attributeId,$arrAttributeList)) {
                $query = "DELETE FROM ".DBPREFIX."module_shop_products_attributes_name WHERE id=".intval($attributeId);
                $objDatabase->Execute($query);
            }
        }

        $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop_products_attributes_value");
        $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop_products_attributes_name");
        $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop_products_attributes");

        return $statusMessage;
    }


    /**
    * Delete the selected attribute option(s)
    *
    * OBSOLETE
    *
    * @access    private
    * @param    integer    $optionId    Id of the attribute option
    * @return    string    Status message
    */
    function _deleteAttributeOption($optionId)
    {
echo("WARNING: obsolete static method ProductAttribute::_deleteAttributeOption() called<br />");
        global $objDatabase;

        if (!is_array($optionId)) {
            $arrOptionIds = array($optionId);
        } else {
            $arrOptionIds = $optionId;
        }

        foreach ($arrOptionIds as $optionId) {
            $query = "DELETE FROM ".DBPREFIX."module_shop_products_attributes WHERE attributes_name_id=".intval($optionId);
            if ($objDatabase->Execute($query)) {
                $query = "DELETE FROM ".DBPREFIX."module_shop_products_attributes_value WHERE name_id=".intval($optionId);
                if ($objDatabase->Execue($query)) {
                    $query = "DELETE FROM ".DBPREFIX."module_shop_products_attributes_name WHERE id=".intval($optionId);
                    $objDatabase->query($query);
                }
            }
        }
        return "Option(s) deleted succesfull";
    }


    /**
     * Initialize the array $this->arrAttributes
     *
     * OBSOLETE
     *
     * @access    private
     */
    function _initAttributes()
    {
        global $objDatabase;

        // get attributes
        $query =
            "SELECT name.id AS nameId, ".
                "name.name AS nameTxt, ".
                "name.display_type AS displayType, ".
                "value.id AS valueId, ".
                "value.name_id AS valueNameId, ".
                "value.value AS valueTxt, ".
                "value.price AS price, ".
                "value.price_prefix AS price_prefix ".
            "FROM ".DBPREFIX."module_shop_products_attributes_name AS name, ".
                    DBPREFIX."module_shop_products_attributes_value AS value ".
            "WHERE value.name_id = name.id ".
            "ORDER BY nameTxt, valueId ASC";

        if (($objResult = $objDatabase->Execute($query)) !== false) {
            while (!$objResult->EOF) {
                if (!isset($this->arrAttributes[$objResult->fields['nameId']]['name'])) {
                    $this->arrAttributes[$objResult->fields['nameId']]['name'] = $objResult->fields['nameTxt'];
                    $this->arrAttributes[$objResult->fields['nameId']]['displayType'] = $objResult->fields['displayType'];
                }
                $this->arrAttributes[$objResult->fields['nameId']]['values'][$objResult->fields['valueId']] = array(
                    'id'           => $objResult->fields['valueId'],
                    'value'        => $objResult->fields['valueTxt'],
                    'price'        => $objResult->fields['price'],
                    'price_prefix' => $objResult->fields['price_prefix'],
                    'selected'     => false
                );
                $this->highestIndex = $objResult->fields['valueId'] > $this->highestIndex
                    ? $objResult->fields['valueId']
                    : $this->highestIndex;
                $objResult->MoveNext();
            }
        }
    }


    /**
     * Generate the standard attribute option/value list or the one of a product
     *
     * BOGUS
     *
     * @todo    Create both a Product and a ProductAttribute method
     *          to properly access this data.
     * @access  private
     * @param   string    $productId    Product Id of which its list will be displayed
     */
    function getAttributeList($productId=0)
    {
echo("WARNING: bogus method ProductAttribute::getAttributeList() called<br />");
        global $objDatabase;
        $this->_initAttributes();

        if ($productId > 0) {
            $query =
                "SELECT attribute_id, product_id, attributes_name_id, attributes_value_id, sort_id " .
                "FROM ".DBPREFIX."module_shop_products_attributes ".
                "WHERE product_id=".intval($productId);
            $objResult = $objDatabase->Execute($query);

            while (!$objResult->EOF) {
                $this->arrAttributes[$objResult->fields['attributes_name_id']]['sortid'] = $objResult->fields['sort_id'];
                $this->arrAttributes[$objResult->fields['attributes_name_id']]['values'][$objResult->fields['attributes_value_id']]['selected'] = true;
                $objResult->MoveNext();
            }
        }
    }


    /**
     * Returns an array of ProductAttributes associated with a Product.
     *
     * Only ProductAttribute values actually linked to a Product will be returned.
     * See {@link ProductAttribute}.
     * @return  array           An array of ProductAttribute objects on success, or false.
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getProductAttributes()
    {
        global $objDatabase;

        // cannot get attributes before Product has been stored.
        // in fact, as long as the ID is invalid, there cannot be any.
        if (!($this->id > 0)) {
            return array();
        }
        // get associated attribute name ids
        $query = "
            SELECT DISTINCT attributes_name_id
              FROM ".DBPREFIX."module_shop_products_attributes
             WHERE product_id=$this->id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        $arrProductAttributeId = array();
        while (!$objResult->EOF) {
            $attributeNameId = $objResult->fields['attributes_name_id'];
            if ($attributeNameId) {
                $arrProductAttributeId[$objResult->fields['attributes_name_id']] = array();
            }
            $objResult->MoveNext();
        }
        // get associated ProductAttributes
        $arrProductAttribute = array();
        foreach (array_keys($arrProductAttributeId) as $attributeNameId) {
            // get associated attribute value ids
            // fields: attribute_id, product_id, attributes_name_id, attributes_value_id, sort_id
            $query = "
                SELECT DISTINCT attributes_value_id, sort_id
                  FROM ".DBPREFIX."module_shop_products_attributes
                 WHERE attributes_name_id=$attributeNameId
                   AND product_id=$this->id
                 ORDER BY sort_id ASC
            ";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) {
                return false;
            }
            // pick ProductAttribute values associated with a Product
            while (!$objResult->EOF) {
                $arrProductAttributeId[$attributeNameId][] =
                    $objResult->fields['attributes_value_id'];
                $objResult->MoveNext();
            }
            // only returns ProductAttributes associated with a Product
            $arrProductAttribute[] = ProductAttribute::getByNameId(
                $attributeNameId, $arrProductAttributeId[$attributeNameId]
            );
        }
        return $arrProductAttribute;
    }


    /**
     * Returns the sort order of the value identified by its ID.
     *
     * If the ProductAttribute value is associated with the given Product ID,
     * the respective sorting order value is returned.
     * If the Product does not carry that value, false is returned instead.
     * @param   integer     $valueId        The value ID
     * @param   integer     $productId      The Product ID
     * @return  integer                     The sorting order, or false.
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function GetValueOrderByProductId($valueId, $productId)
    {
        global $objDatabase;

        $query = "
            SELECT sort_id FROM ".DBPREFIX."module_shop_products_attributes
             WHERE attributes_value_id=$valueId
               AND product_id=$productId
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        // any result?
        if (!$objResult->EOF) {
            return $objResult->fields['sort_id'];
        }
        return false;
    }


////////////////////////////////////////////////////////////////////////////
// Expelled from the Product class.
// To be rewritten as ProductAttribute methods
////////////////////////////////////////////////////////////////////////////

    /**
     * Associates the given ProductAttribute with a Product.
     *
     * Note that all ProductAttribute values therein are added,
     * so if you only want a subset of them, remove them beforehand
     * or afterwards.
     * @param   ProductAttribute    $objProductAttribute
     *                                      The ProductAttribute object
     * @return  boolean                     True on success, false otherwise
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function addProductAttribute($objProductAttribute)
    {
        global $objDatabase;

        $arrValue = $objProductAttribute->getValueArray();
        foreach ($arrValue as $value) {
            $query = "
                INSERT INTO ".DBPREFIX."module_shop_products_attributes
                    (product_id,
                    attributes_name_id,
                    attributes_value_id,
                    sort_id)
                VALUES
                    ($this->id, ".
                    $objProductAttribute->getNameId().', '.
                    $value['id'].', '.
                    $value['order'].')';
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) {
                return false;
            }
        }
        return true;
    }


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
        foreach ($this->arrValues as $value) {
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

/* test
    $objProductAttribute = new ProductAttribute('Grösse', 0);
    $objProductAttribute->addValue('S', 9.95, '-');
    $objProductAttribute->addValue('M', 0.00, '+');
    $objProductAttribute->addValue('L', 9.95, '+');
    echo("new PA:<br />".$objProductAttribute->toString());

    echo("id: ".$objProductAttribute->getId()."<br />");
    $objProductAttribute->setName('Neuer Name');
    echo("changed name: ".$objProductAttribute->getName()."<br />");
    $objProductAttribute->setType(1);
    echo("changed type: ".$objProductAttribute->getType()."<br />");
    //echo("setName() result: ".($objProductAttribute->setName('Neuer Name') ? 'true' : 'false').'<br />');

    $arrValues = $objProductAttribute->getValueArray();
    echo("value array: ");var_export($arrValues);echo("<br />");
    $arrValueIds = $objProductAttribute->getValueIdArray();
    echo("value ID array: ");var_export($arrValueIds);echo("<br />");
    $arrNameIds = $objProductAttribute->getNameIdArray();
    echo("name ID array: ");var_export($arrNameIds);echo("<br />");
    echo("indices:<br />");
    foreach ($arrValueIds as $id) {
        echo("id $id => ".$objProductAttribute->getValueIndexByValueId($id).'<br />');
    }
    $id = current($arrValueIds);
    $objProductAttribute->updateValue($id, 'XL', 19.95, '+');

    die();

    // todo from here
    updateValue($valueId, $value, $price, $prefix)
    deleteValueById($id)
    _deleteValueById($id)
    delete()
    store()
    update()
    _updateValue($value)
    insert()
    _insertValue(&$value, $nameId)
    getByNameId($nameId, $arrProductAttributeValueId='')
    getByValueId($valueId)
    getNameIdByValueId($valueId)
    getAttributeArray()
    getAttributeDisplayTypeMenu($attributeId, $displayTypeId='0')
    getAttributeInputBoxes($attributeId, $name, $content, $maxlength, $style='')
    getAttributePricePrefixMenu($attributeId, $name, $pricePrefix)
    getAttributeValueMenu($attributeId, $name, $selectedId, $onchange, $style)
    // todo: getAttributeJSVars()
*/


?>
