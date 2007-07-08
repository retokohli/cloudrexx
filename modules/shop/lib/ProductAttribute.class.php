<?php

/**
 * Shop Product Attributes
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
 * These may be associated with one or more Products.
 * Each attribute consists of a name and one or more values.
 * The type determines the relation between a Product and the attribute values,
 * that is, whether it is optional or mandatory, and whether only single or
 * multiple attributes may be chosen.  See {@link } for details.
 * @version     $Id: 0.0.1 alpha$
 * @package     contrexx
 * @subpackage  module_shop
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @todo        Test!
 */

class ProductAttribute  // friend Product
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
    function ProductAttribute($name, $type, $id=0)
    {
        $this->__construct($name, $type, $id);
    }


    /**
     * Constructor (PHP5)
     *
     * id, name, display_type (enum('0', '1', '2', '3'))
     */
    function __construct($name, $type, $id=0)
    {
        $this->name     = $name;
        $this->type     = $type;
        $this->id       = $id;
        $this->arrValue = array();
/*
        if (!$this->id) {
            $this->store();
        }
*/
    }


    /**
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
     */
    //static
    function getValueIdArray($productId=0)
    {
        global $objDatabase;

        $query = ($productId ? "
            SELECT id
              FROM ".DBPREFIX."module_shop_products_attributes_value
        INNER JOIN ".DBPREFIX."module_shop_products_attributes
                ON attributes_value_id=id
             WHERE product_id=$productId
          ORDER BY id ASC
        " : "
            SELECT id
              FROM ".DBPREFIX."module_shop_products_attributes_value
          ORDER BY id ASC
        ");

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
//$objDatabase->debug = 1;
        $objResult = $objDatabase->Execute($query);
//$objDatabase->debug = 0;
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
     * Note that this is ONLY set by our friend, the Product object.
     * So if you don't have a ProductAttribute actually associated to
     * a Product, you'll get a return value of boolean false.
     * @return  integer                 The ProductAttribute sorting order,
     *                                  or false if unknown.
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
     * @param   integer                 The ProductAttribute sorting order
     */
    function setOrder($order)
    {
        if (is_integer($order)) {
            $this->order = intval($order);
        }
    }

    /**
     * Get the ProductAttribute value array
     * @return  array                   The ProductAttribute value array
     */
    function getValueArray()
    {
        return $this->arrValue;
    }
    /**
     * Set the ProductAttribute value array -- NOT ALLOWED
     * Use addValue()/deleteValueById() instead.
     */

    /**
     * Returns the array index for the given value ID, or false.
     *
     * @param   integer     $valueId    The value ID to look for
     * @return  integer                 The internal array index, or false.
     */
    function getValueIndexByValueId($valueId)
    {
        foreach ($this->arrValue as $index => $value) {
            if ($value['id'] == $valueId) {
                return $index;
            }
        }
        return false;
    }

    /**
     * Returns the array of ProductAttribute name IDs present in the database.
     *
     * @static
     * @return  array       Array of all ProductAttribute name IDs on success,
     *                      false otherwise
     */
    function getNameIdArray()
    {
        global $objDatabase;

        $query = "
            SELECT id
              FROM ".DBPREFIX."module_shop_products_attributes_name
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        $arrNameId = array();
        while (!$objResult->EOF) {
            $arrNameId[] = $objResult->Fields('id');
            $objResult->MoveNext();
        }
        return $arrNameId;
    }


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
    function addValue($value, $price, $prefix, $id='')
    {
        $this->arrValue[] = array(
            'value'   => $value,
            'price'   => $price,
            'prefix'  => $prefix,
            'id'      => $id,          // changed by insertValue()
        );
/*
        // insert into database, and update ID
        return ProductAttribute::_insertValue(
            $this->arrValue[end(array_keys($this->arrValue))], $this->id
        );
*/
    }


    /**
     * Update a ProductAttribute value
     *
     *
     */
    function updateValue($valueId, $value, $price, $prefix)
    {
        // fields: id, name_id, value, price, price_prefix (enum('+', '-'))
        $index = $this->getValueIndexByValueId($valueId);
        $this->arrValue[$index]['value']  = $value;
        $this->arrValue[$index]['price']  = $price;
        $this->arrValue[$index]['prefix'] = $prefix;
        // insert into database, and update ID
        $this->_updateValue($this->arrValue[$index]);
        if (!$this->arrValue[$index]['id']) {
// heavy debug
die("ERROR: value id not set after update/insert<br />");
        }
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
        foreach ($this->arrValue as $index => $value) {
            if ($value['id'] == $id) {
                // delete from the database
                if (!ProductAttribute::_deleteValueById($id)) {
                    return false;
                }
                // delete from the array
                unset($this->arrValue[$index]);
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
     * DO NOT, repeat, *DO* *NOT* call this method from the outside
     * unless you know what you are doing.  See {@link deleteValueById()}
     * for a hint on why.
     * @access  protected
     * @static
     * @param   integer $id             The ProductAttribute value ID
     * @global  mixed   $objDatabase    Database object;
     * @return  boolean                 True on success, false otherwise.
     */
    //static
    function _deleteValueById($id)
    {
        global $objDatabase;

        $query = "
            DELETE FROM ".DBPREFIX."module_shop_products_attributes_value
                  WHERE id = $id
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
     * @global  mixed   $objDatabase    Database object;
     * @return  boolean                 True on success, false otherwise.
     */
    function delete()
    {
        global $objDatabase;

        // delete values
        $query = "
            DELETE FROM ".DBPREFIX."module_shop_products_attributes_value
                  WHERE name_id = $this->id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        // delete name
        $query = "
            DELETE FROM ".DBPREFIX."module_shop_products_attributes_name
                  WHERE id = $this->id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        // delete referring attributes
        $query = "
            DELETE FROM ".DBPREFIX."module_shop_products_attributes
                  WHERE attributes_name_id = $this->id
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
            return ($this->update());
        }
        return ($this->insert());
    }


    /**
     * Updates the ProductAttribute object in the database.
     *
     * Also updates (or inserts) all value entries contained.
     * @return  boolean     True on success, false otherwise
     */
    function update()
    {
        global $objDatabase;

        $query = "
            UPDATE ".DBPREFIX."module_shop_products_attributes_name
               SET name='".contrexx_addslashes($this->name)."'
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


    function _updateValue($value)
    {
        global $objDatabase;

        // mind: value entries in the array may be *new* and have to
        // be inserted, even though the object itself has got a valid ID!
        $query = "
            UPDATE ".DBPREFIX."module_shop_products_attributes_value
               SET name_id=$this->id,
                   value='".contrexx_addslashes($value['value'])."',
                   price=".floatval($value['price']).",
                   price_prefix='".$value['prefix']."'
             WHERE id=".$value['id'];
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        // not updated? -- insert!
        if ($objDatabase->Affected_Rows() == 0) {
            return ProductAttribute::_insertValue($value, $this->id);
        }
        return true;
    }


    /**
     * Inserts the ProductAttribute object into the database.
     *
     * Also inserts all value entries contained.
     * @return  boolean     True on success, false otherwise
     */
    function insert()
    {
        global $objDatabase;

        $query = "
            INSERT INTO ".DBPREFIX."module_shop_products_attributes_name
                (name)
            VALUES
                ('".contrexx_addslashes($this->name)."')";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        $this->id = $objDatabase->Insert_ID();

        foreach ($this->arrValue as $value) {
            if (!insertValue($value, $this->id)) {
                return false;
            }
//echo("debug: ProductAttribute::insert(): value_id ".$value['id']." (must be != 0)<br />");
        }
        return true;
    }


    /**
     * Insert a new ProductAttribute value into the database.
     *
     * Updates the values' ID upon success.
     * @access  private
     * @param   array   $value      The value array, by reference
     * @param   integer $nameId     The associated name ID
     * @return  boolean             True on success, false otherwise
     */
    function _insertValue(&$value, $nameId)
    {
        global $objDatabase;

        $query = "
            INSERT INTO ".DBPREFIX."module_shop_products_attributes_value
                (name_id, value, price, price_prefix)
            VALUES
                ($nameId,
                '".contrexx_addslashes($value['value'])."',
                ".floatval($value['price']).",
                '".contrexx_addslashes($value['prefix'])."')";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        $value['id'] = $objDatabase->Insert_ID();
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
              FROM ".DBPREFIX."module_shop_products_attributes_name
             WHERE id=$nameId
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        // new ($name, $type, $id='')
        if (!$objResult->EOF) {
            $objProductAttribute = new ProductAttribute(
                contrexx_stripslashes($objResult->fields['name']),
                $objResult->fields['display_type'],
                $nameId
            );

            // id, name_id, value, price, price_prefix (enum('+', '-'))
            // get selected or all associated values
            $query = "
                SELECT id, value, price, price_prefix
                  FROM ".DBPREFIX."module_shop_products_attributes_value
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
                    contrexx_stripslashes($objResult->fields['value']),
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
     * Returns the name ID associated with the given value ID in the
     * value table.
     *
     * @param   integer     $valueId    The value ID
     * @return  integer                 The associated name ID
     */
    function getNameIdByValueId($valueId)
    {
        global $objDatabase;

        // fields: id, name_id, value, price, price_prefix (enum('+', '-'))
        $query = "
            SELECT name_id
              FROM ".DBPREFIX."module_shop_products_attributes_value
             WHERE id=$valueId
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult || $objResult->RecordCount() != 1) {
            return false;
        }
        return $objResult->fields['name_id'];
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
     */
    function getAttributeArray($productId=0)
    {
        global $objDatabase;

        // get attributes
        $query = "
            SELECT name.id AS nameId,
                   name.name AS nameTxt,
                   name.display_type as type,
                   value.id AS valueId,
                   value.value AS valueTxt,
                   value.price AS price,
                   value.price_prefix AS pricePrefix
              FROM ".DBPREFIX."module_shop_products_attributes_name AS name,
                   ".DBPREFIX."module_shop_products_attributes_value AS value
             WHERE value.name_id = name.id
          ORDER BY nameTxt ASC, valueTxt ASC
        ";
//unused  value.name_id AS valueNameId,

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
                    $objResult->fields['nameTxt'];
                $arrProductAttribute[$nameId]['type'] =
                    $objResult->fields['type'];
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
                    'value'    => $objResult->fields['valueTxt'],
                    'price'    => $objResult->fields['price'],
                    'prefix'   => $objResult->fields['pricePrefix'],
                    'selected' => ($order === false ? 0 : 1),
                );
            // use order value from last value record
            // boolean false, or integer!
            $arrProductAttribute[$nameId]['order'] = intval($order);
            $objResult->MoveNext();
        }
var_export($arrProductAttribute);//die();

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
        $menu = "";

        foreach ($arrValues as $id => $arrValue) {
            $menu .=
                "<select style=\"width:50px;display:".
                ($select == true ? "inline" : "none").
                ";\" name=\"".$name."[$id]\" id=\"".$name."[$id]\" size=\"1\"".
                "onchange='updateAttributeValueList($attributeId)'>\n".
                "<option value=\"+\" ".
                ($arrValue['price_prefix'] != "-" ? "selected=\"selected\"" : "").
                ">+</option>\n".
                "<option value=\"-\" ".
                ($arrValue['price_prefix'] == "-" ? "selected=\"selected\"" : "").
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
        $objProductAttribute = ProductAttribute::getByNameId($attributeId);
        if (!$objProductAttribute) {
            return '';
        }
        if (!$selectedId) {
            $selectedId = $objProductAttribute->arrValue[0]['id'];
        }

        $menu =
            "<select name='".$name."[]' id='".$name."[]' size='1' ".
            "onchange='$onchange' style='$style'>\n";

        //if (is_array($objProductAttribute->arrValue))
        foreach ($objProductAttribute->arrValue as $arrValue) {
            $id = $arrValue['id'];
            $menu .=
                "<option value='$id' ".
                ($selectedId == $id ? "selected='selected'" : '').'>'.
                $arrValue['value'].' ('.
                $arrValue['prefix'].$arrValue['price'].
                " $this->defaultCurrency)</option>\n";
        }
        $menu .= '</select>';
        $menu .= "<br /><a href='javascript:{}' ".
            "id='attributeValueMenuLink[$attributeId]' style='display:none;' ".
            "onclick='removeSelectedValues($attributeId)' ".
            "title='Ausgewählten Wert entfernen' ".
            "alt='Ausgewählten Wert entfernen'>".
            "Ausgewählten Wert entfernen</a>";
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
echo("jsVars:<br />$jsVars<br />");
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

        $query = "INSERT INTO ".DBPREFIX."module_shop_products_attributes_name (name)
            VALUES ('".addslashes($_POST['optionName'][0])."')";

        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return "ERROR: could not insert product attribute name into database<br />";
        }
        $nameId = $objResult->Insert_Id();

        foreach ($arrAttributeList[0] as $id) {
            // insert new attribute value
            $query = "
                INSERT INTO ".DBPREFIX."module_shop_products_attributes_value
                (name_id, value, price, price_prefix) VALUES
                ($nameId, '".
                addslashes($arrAttributeValue[$id])."', '".
                floatval($arrAttributePrice[$id])."', '".
                addslashes($arrAttributePricePrefix[$id])."')
            ";
            $objDatabase->Execute($query);
        }

        $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop_products_attributes_value");
        $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."module_shop_products_attributes_name");

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
//echo("getProductAttributes(): attributes_name_id (1): $attributeNameId<br />");
            if ($attributeNameId) {
                $arrProductAttributeId[$objResult->fields['attributes_name_id']] = array();
            } else {
//echo("skipped<br />");
            }
            $objResult->MoveNext();
        }
        // get associated ProductAttributes
        $arrProductAttribute = array();
        foreach (array_keys($arrProductAttributeId) as $attributeNameId) {
//echo("getProductAttributes(): attributes_name_id (2): $attributeNameId<br />");
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
//echo("getProductAttributes(): attributes_value_id: ".$objResult->fields['attributes_value_id']."<br />");
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

        // fields: attribute_id, product_id, attributes_name_id, attributes_value_id, sort_id
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
            DELETE FROM ".DBPREFIX."module_shop_products_attributes
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
     * simplyfy the update process, and mandatory to make {@link clone()}
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
            DELETE FROM ".DBPREFIX."module_shop_products_attributes
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
            DELETE FROM ".DBPREFIX."module_shop_products_attributes
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
