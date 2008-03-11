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
 * Product Attributes
 *
 * This class provides frontend and backend helper and display functionality
 * related to the Product Attribute class.
 * See {@link ProductAttribute} for details.
 * @version     $Id: 0.0.1 alpha$
 * @package     contrexx
 * @subpackage  module_shop
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @todo        Test!
 */

class ProductAttributes  // friend Product
{
    /**
     * The optional Product ID from which the Attributes are used,
     * or 0 (zero), meaning all Attributes whatsoever.
     * @var integer
     */
    var $productId = 0;

    /**
     * The array of ProductAttribute names
     *
     * Includes the fields id, name, and display_type
     * @var array
     */
    var $arrName;

    /**
     * The ProductAttribute name index
     *
     * The array has the form
     * array ( ID => index, ... ),
     * where ID is the ID of the Attribute name, and index is its
     * offset in the Attribute name array ($arrName object variable)
     * @var array
     */
    var $arrNameIndex;

    /**
     * The array of ProductAttribute values
     *
     * Includes the fields id, name_id, value, price, and price_prefix
     * @var array
     */
    var $arrValue;

    /**
     * The ProductAttribute value index
     *
     * The array has the form
     * array ( ID => index, ... ),
     * where ID is the ID of the Attribute value, and index is its
     * offset in the Attribute value array ($arrValue object variable)
     * @var array
     */
    var $arrValueIndex;

    /**
     * The array of ProductAttribute relations
     *
     * Includes the fields attribute_id, product_id,
     * attributes_name_id, attributes_value_id, and sort_id
     * @var array;
     */
    var $arrRelation;

    /**
     * The ProductAttribute relation index
     *
     * The array has the form
     * array ( ID => index, ... ),
     * where ID is the ID of the Attribute relation, and index is its
     * offset in the Attribute relation array ($arrRelation Value object
     * variable)
     * @var array
     */
    var $arrRelationIndex;


    /**
     * Constructor (PHP4)
     */
    function ProductAttributes($productId=0)
    {
        $this->__construct($productId);
    }


    /**
     * Constructor (PHP5)
     *
     * The optional $productId argument lets you specify a Product
     * whose Attributes you want to get.  Defaults to 0 (zero),
     * which will include all Attributes.
     */
    function __construct($productId=0)
    {
        $this->productId   = $productId;
        $this->arrName     = $this->getNameArray($productId);
        $this->arrValue    = $this->getValueArray($productId);
        $this->arrRelation = $this->getRelationArray($productId);
    }


    /**
     * Returns an array of ProductAttribute names.
     *
     * Set the $flagReuse parameter to false after any Attribute properties
     * have been changed.
     * If the optional $productId argument is greater than zero,
     * only names associated with this Product are returned,
     * all names found in the database otherwise.
     * @static
     * @access  public
     * @param   boolean     $flagReuse      If true, returns the previously
     *                                      initialized array, if any.
     *                                      Reinitializes the array otherwise.
     * @param   integer     $productId      The optional Product ID
     * @return  array                       Array of ProductAttribute names
     *                                      upon success, false otherwise.
     */
    function getNameArray($flagReuse=true, $productId=0)
    {
        global $objDatabase;

        if ($flagReuse && $this->arrName) {
            return $this->arrName;
        }

        $query = "
            SELECT DISTINCT id, name, display_type
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_name
            ".($productId
              ? "INNER JOIN ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes
                         ON attributes_name_id=id
                      WHERE product_id=$productId
                   ORDER BY sort_id ASC
            " : '');
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        $this->arrName      = array();
        $this->arrNameIndex = array();
        $index = 0;
        while (!$objResult->EOF) {
            $arrName = array();
            $arrName['id'] = $objResult->fields['id'];
            $arrName['name'] = $objResult->fields['name'];
            $arrName['type'] = $objResult->fields['display_type'];
            $this->arrName[++$index] = $arrName;
            $this->arrNameIndex[$arrName['id']] = $index;
            $objResult->MoveNext();
        }
        return $this->arrName;
    }


    /**
     * Returns an array of Attribute value IDs for the specified Product.
     *
     * @access  public
     * @param   integer     $productId      The Product ID
     * @return  array                       Array of ProductAttribute value IDs
     *                                      upon success, false otherwise.
     */
    function getProductValueArray($productId)
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
        $arrValue = array();
        while (!$objResult->EOF) {
            $arrValue[] = array(
                'valueId' => $objResult->fields['attributes_value_id'],
                'order'   => $objResult->fields['sort_id'],
            );
            $objResult->MoveNext();
        }
        return $arrValue;
    }


    /**
     * Returns an array of ProductAttribute relations.
     *
     * Set the $flagReuse parameter to false after any Attribute properties
     * have been changed.
     * If the optional $productId argument is greater than zero,
     * only relations associated with this Product are returned,
     * all relations found in the database otherwise.
     * @static
     * @access  public
     * @param   boolean     $flagReuse      If true, returns the previously
     *                                      initialized array, if any.
     *                                      Reinitializes the array otherwise.
     * @param   integer     $productId      The optional Product ID
     * @return  array                       Array of ProductAttribute relations
     *                                      upon success, false otherwise.
     */
    function getRelationArray($flagReuse=true, $productId=0)
    {
        global $objDatabase;

        if ($flagReuse && $this->arrRelation) {
            return $this->arrRelation;
        }

        $query = "
            SELECT *
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes
        ".($productId ? "WHERE product_id=$productId" : '')."
          ORDER BY name_id ASC, sort_id ASC
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        $this->arrRelation      = array();
        $this->arrRelationIndex = array();
        $index = 0;
        while (!$objResult->EOF) {
            $arrRelation = array();
            $arrRelation['id']        = $objResult->fields['attribute_id'];
            $arrRelation['productId'] = $objResult->fields['product_id'];
            $arrRelation['nameId']    = $objResult->fields['attributes_name_id'];
            $arrRelation['valueId']   = $objResult->fields['attributes_value_id'];
            $arrRelation['order']     = $objResult->fields['sort_id'];
            $this->arrRelation[++$index] = $arrRelation;
            $this->arrRelationIndex[$arrRelation['id']] = $index;
            $objResult->MoveNext();
        }
        return $this->arrRelation;
    }


    /**
     * Add a ProductAttribute value to a Product.
     *
     * The optional $order argument determines the order position of the value.
     * @static
     * @param   integer     $valueId        The ProductAttribute value ID
     * @param   integer     $productId      The Product ID
     * @param   integer     $order          The optional sorting order,
     *                                      defaults to 0 (zero)
     * @return  boolean                     True on success, false otherwise
     * @global  mixed       $objDatabase    Database object
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function addValueToProduct($valueId, $productId, $order=0)
    {
        global $objDatabase;

        $nameId = ProductAttribute::getNameIdByValueId($valueId);
        if ($nameId <= 0) {
            return false;
        }

        // fields: attribute_id, product_id, attributes_name_id, attributes_value_id, sort_id
        $query = "
            INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes (
                product_id,
                attributes_name_id,
                attributes_value_id,
                sort_id
            ) VALUES (
                $productId,
                $nameId,
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
     * Remove all Product Attribute relations for the given Product ID.
     * @static
     * @param   integer     $productId      The Product ID
     * @return  boolean                     True on success, false otherwise.
     * @global  mixed       $objDatabase    Database object
     */
    function deleteByProductId($productId)
    {
        global $objDatabase;

        $query = "
            DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes
             WHERE product_id=$productId
        ";
        $objResult = $objDatabase->Execute($query);
        return $objResult;
    }


    /**
     * Delete all Attributes from the database
     *
     * Clears all names, values, and relations.
     * Use with due care!
     * @static
     * @return  boolean                     True on success, false otherwise.
     * @global  mixed       $objDatabase    Database object
     */
    //static
    function deleteAll()
    {
        global $objDatabase;

        $query = "DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        $query = "DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_value";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        $query = "DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_name";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        return true;
    }


    //static
    function getAttributeDisplayTypeMenu($attributeId, $displayTypeId='0')
    {
        global $_ARRAYLANG;

        return
            "<select name='attributeDisplayType[$attributeId]' ".
                "size='1' style='width:170px;'>\n".
            "<option value='0' ".
                ($displayTypeId == '0' ? "selected='selected'" : '').">".
                $_ARRAYLANG['TXT_MENU_OPTION']."</option>\n".
            "<option value='3' ".
                ($displayTypeId == '3' ? "selected='selected'" : '').">".
                $_ARRAYLANG['TXT_SHOP_MENU_OPTION_DUTY']."</option>\n".
            "<option value='1' ".
                ($displayTypeId == '1' ? "selected='selected'" : '').">".
                $_ARRAYLANG['TXT_RADIOBUTTON_OPTION']."</option>\n".
            "<option value='2' ".
                ($displayTypeId == '2' ? "selected='selected'" : '').">".
                $_ARRAYLANG['TXT_CHECKBOXES_OPTION']."</option>\n".
            "</select>\n";
    }


    /**
     * Generate a list of the input boxes containing the values of an
     * Attribute.
     *
     * @access   private
     * @param    integer     $attributeId    ID of the ProductAttribute name
     * @param    string      $name           Name and ID of the input box
     * @param    string      $content        ProductAttribute value field
     * @param    integer     $maxlength      Maximum length of the input box
     * @param    string      $style          CSS style for the input box
     * @return   string      $inputBoxes     String with HTML code
     */
    //static
    function getAttributeInputBoxes($attributeId, $name, $content, $maxlength, $style='')
    {
        $inputBoxes = '';
        $select     = true;

        $objProductAttribute = ProductAttribute::getByNameId($attributeId);
        foreach ($objProductAttribute->arrValue as $id => $arrValue) {
            $inputBoxes .=
                "<input type='text' name='$name[$id]' ".
                "id='{$name}_{$id}' value='$arrValue[$content]' ".
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


    /**
     * Returns HTML code for the value menu for each ProductAttribute
     *
     * @global  array       $_ARRAYLANG     Language array
     * @param   integer     $attributeId    ID of the ProductAttribute name
     * @param   string      $name           Name of the menu
     * @param   integer     $selectedId     ID of the selected value
     * @param   string      $onchange       Javascript onchange event of the menu
     * @param   string      $style          CSS style declaration for the menu
     * @return  string      $menu           Contains the value menus
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
        $menu .=
            '</select>'.
            '<br /><a href="javascript:{}" '.
            'id="attributeValueMenuLink[$attributeId]" style="display:none;" '.
            'onclick="removeSelectedValues($attributeId)" '.
            'title="'.$_ARRAYLANG['TXT_SHOP_REMOVE_SELECTED_VALUE'].'" '.
            'alt="'.$_ARRAYLANG['TXT_SHOP_REMOVE_SELECTED_VALUE'].'">'.
            $_ARRAYLANG['TXT_SHOP_REMOVE_SELECTED_VALUE'].'</a>';
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

}

?>
