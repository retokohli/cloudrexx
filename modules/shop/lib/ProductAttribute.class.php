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
    var $id;

    /**
     * The associated Product ID, if any, or false
     */
    var $productId = false;

    /**
     * The ProductAttribute name
     * @var string
     */
    var $name;

    /**
     * The ProductAttribute type
     * @var integer
     */
    var $type;

    /**
     * The ProductAttribute array of values
     * @var array
     */
    var $arrValue;

    /**
     * The value index
     *
     * The array has the form
     * array ( ID => index, ... ),
     * where ID is the ID of the Attribute value, and index is its
     * offset in the Attribute value array ($arrValue object variable)
     * @var array
     */
    var $arrValueIndex;

    /**
     * Sorting order
     *
     * Only used by our friend, the Product class
     * @var integer
     */
    var $order;


    /**
     * Constructor (PHP4)
     *
     */
    function ProductAttribute($name, $type, $id=0, $productId=false)
    {
        $this->__construct($name, $type, $id, $productId);
    }

    /**
     * Constructor (PHP5)
     *
     * id, name, display_type (enum('0', '1', '2', '3'))
     */
    function __construct($name, $type, $id=0, $productId=false)
    {
        $this->name      = $name;
        $this->type      = $type;
        $this->id        = $id;
        $this->productId = $productId;
        $this->arrValue  = array();
/*
        if (!$this->id) {
            $this->store();
        }
*/
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
        $this->type = intval($type);
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
     * @access  public
     * @param   boolean     $flagReuse      If true, returns the previously
     *                                      initialized array, if any.
     *                                      Reinitializes the array otherwise.
     * @param   integer     $productId      The optional Product ID
     * @return  array                       Array of ProductAttribute values
     *                                      upon success, false otherwise.
     * @global  mixed       $objDatabase    The Database object
     */
    function getValueArray($flagReuse=true)
    {
        global $objDatabase;

        if ($flagReuse && $this->arrValue) {
            return $this->arrValue;
        }

        $query = "
            SELECT *
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_value
              WHERE name_id=$this->id
            ".($this->productId
              ? "INNER JOIN ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes
                         ON attributes_value_id=id
                      WHERE product_id=$this->productId
                   ORDER BY sort_id ASC
            " : '');
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        $this->arrValue      = array();
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
            $this->arrValue[++$index] = $arrValue;
            $this->arrValueIndex[$arrValue['id']] = $index;
            $objResult->MoveNext();
        }
        return $arrValue;
    }
    /**
     * Set the ProductAttribute value array -- NOT ALLOWED
     * Use addValue()/deleteValueById() instead.
     */


    /**
     * Add a ProductAttribute value
     *
     * The values' ID is updated when the record is stored by
     * {@link _insertValue()}.
     * @param   string  $value      The ProductAttribute value description
     * @param   float   $price      The ProductAttribute value price
     * @param   string  $prefix     The ProductAttribute value price prefix ('+' or '-')
     * @return  boolean             True on success, false otherwise
     */
    function addValue($value, $price, $prefix, $order=0, $id='')
    {
        $this->arrValue[] = array(
            'value'   => $value,
            'price'   => $price,
            'prefix'  => $prefix,
            'order'   => $order,
            'id'      => $id,          // changed by insertValue()
        );
    }


    /**
     * Update a ProductAttribute value
     *
     *
     */
    function updateValue($valueId, $value, $price, $prefix, $order)
    {
        // fields: id, name_id, value, price, price_prefix (enum('+', '-'))
        $index = $this->getValueIndexByValueId($valueId);
        $this->arrValue[$index]['value']  = $value;
        $this->arrValue[$index]['price']  = $price;
        $this->arrValue[$index]['prefix'] = $prefix;
        $this->arrValue[$index]['order']  = $order;
        // insert into database, and update ID
        $this->_updateValue($this->arrValue[$index]);
    }


    /**
     * Deletes the ProductAttribute value with the given value ID
     * from the database.
     *
     * Remember to reinitialize the value array after deleting any values!
     * @static
     * @param   integer     $id             The ProductAttribute value ID
     * @return  boolean                     True on success, false otherwise.
     * @global  mixed       $objDatabase    The Database object
     */
    //static
    function deleteValueById($id)
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
     * Deletes the ProductAttribute from the database.
     *
     * Includes both the name and all of the value entries related to it.
     * As a consequence, all Attribute entries referring to the deleted
     * entries are deleted, too.  See {@link Product::arrAttribute(sp?)}.
     * Keep in mind that any Products currently held in memory may cause
     * inconsistencies!  To be on the safe side, remove relations between
     * Products and these ProductAttribute first!
     * @return  boolean                     True on success, false otherwise.
     * @global  mixed       $objDatabase    The Database object
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
     * @return  boolean                     True on success, false otherwise
     * @global  mixed       $objDatabase    The Database object
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
        foreach ($this->arrValue as $value) {
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
     * @global  mixed       $objDatabase    The Database object
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
     * @return  boolean                     True on success, false otherwise
     * @global  mixed       $objDatabase    The Database object
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

        foreach ($this->arrValue as $value) {
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
     * @global  mixed       $objDatabase    The Database object
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
     * @global  mixed       $objDatabase    The Database object
     */
    //static
    function getByNameId($nameId, $arrProductAttributeValueId='')
    {
        global $objDatabase;

        // fields: id, name, display_type (enum('0', '1', '2', '3'))
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

            // id, name_id, value, price, price_prefix (enum('+', '-'))
            // get selected or all associated values
            $query = "
                SELECT id, value, price, price_prefix
                  FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_value
                 WHERE name_id=$nameId ".
                (is_array($arrProductAttributeValueId)
                    ? 'AND id IN ('.join(', ', $arrProductAttributeValueId).') '
                    : ''
                )."ORDER BY value ASC";
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
     * @global  mixed       $objDatabase    The Database object
     */
    function getValueIdArray($productId)
    {
        global $objDatabase;

        $query = "
            SELECT     attributes_value_id, sort_id
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


    /**
     * Returns the name ID associated with the given value ID in the
     * value table.
     *
     * @static
     * @param   integer     $valueId        The value ID
     * @return  integer                     The associated name ID
     * @global  mixed       $objDatabase    The Database object
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
     * @global  mixed       $objDatabase    The Database object
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
     * @global  mixed       $objDatabase    The Database object
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
          ORDER BY nameTxt ASC, valueTxt ASC
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
