<?php

/**
 * Shop Product Attributes
 *
 * @version     2.1.0
 * @package     contrexx
 * @subpackage  module_shop
 * @todo        Test!
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 */

require_once ASCMS_MODULE_PATH.'/shop/lib/Currency.class.php';

/**
 * Product Attributes
 *
 * This class provides frontend and backend helper and display functionality
 * related to the Product Attribute class.
 * See {@link ProductAttribute} for details.
 * @version     2.1.0
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
    private static $productId = 0;

    /**
     * The array of ProductAttribute names
     *
     * Includes the fields id, name, and display_type
     * @var array
     */
    private static $arrName;

    /**
     * The array of ProductAttribute values
     *
     * Includes the fields id, name_id, value, price, and price_prefix
     * @var array
     */
    private static $arrValue;

    /**
     * The array of ProductAttribute relations
     *
     * Includes the fields attribute_id, product_id,
     * attributes_name_id, attributes_value_id, and sort_id
     * @var array;
     */
    private static $arrRelation;


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
     * The array has the form
     * array(
     *  Index => array(
     *      valueId => value ID,
     *      order   => sorting order,
     *  ),
     *  ...
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
     * Creates a relation between the Product Attribute value ID and the
     * Product ID.     *
     * The optional $order argument determines the order position of the value.
     * @static
     * @param   integer     $value_id        The ProductAttribute value ID
     * @param   integer     $productId      The Product ID
     * @param   integer     $order          The optional sorting order,
     *                                      defaults to 0 (zero)
     * @return  boolean                     True on success, false otherwise
     * @global  ADONewConnection  $objDatabase    Database connection object
     * @copyright   CONTREXX CMS - COMVATION AG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    static function addValueToProduct($value_id, $productId, $order=0)
    {
        global $objDatabase;

        $nameId = ProductAttribute::getNameIdByValueId($value_id);
        if ($nameId <= 0) return false;
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
                $value_id,
                $order
            )
        ";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) return true;
        return false;
    }


    /**
     * Remove all Product Attribute relations for the given Product ID.
     * @static
     * @param   integer     $productId      The Product ID
     * @return  boolean                     True on success, false otherwise.
     * @global  ADONewConnection  $objDatabase    Database connection object
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
     * @global  ADONewConnection  $objDatabase    Database connection object
     */
    static function deleteAll()
    {
        global $objDatabase;

        $query = "DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        $query = "DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_value";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        $query = "DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_name";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        return true;
    }


    static function getAttributeDisplayTypeMenu($attributeId, $displayTypeId='0', $onchange='')
    {
        global $_ARRAYLANG;

        return
            "<select name='attributeDisplayType[$attributeId]' ".
                "size='1' style='width:170px;'".
                (empty($onchange) ? '' : ' onchange="'.$onchange.'"').
                ">\n".
            "<option value='".SHOP_PRODUCT_ATTRIBUTE_TYPE_MENU_OPTIONAL."'".
                ($displayTypeId == SHOP_PRODUCT_ATTRIBUTE_TYPE_MENU_OPTIONAL
                    ? ' selected="selected"' : ''
                ).">".
                $_ARRAYLANG['TXT_MENU_OPTION']."</option>\n".
            "<option value='".SHOP_PRODUCT_ATTRIBUTE_TYPE_MENU_MANDATORY."'".
                ($displayTypeId == SHOP_PRODUCT_ATTRIBUTE_TYPE_MENU_MANDATORY
                    ? ' selected="selected"' : ''
                ).">".
                $_ARRAYLANG['TXT_SHOP_MENU_OPTION_DUTY']."</option>\n".
            "<option value='".SHOP_PRODUCT_ATTRIBUTE_TYPE_RADIOBUTTON."'".
                ($displayTypeId == SHOP_PRODUCT_ATTRIBUTE_TYPE_RADIOBUTTON
                    ? ' selected="selected"' : ''
                ).">".
                $_ARRAYLANG['TXT_RADIOBUTTON_OPTION']."</option>\n".
            "<option value='".SHOP_PRODUCT_ATTRIBUTE_TYPE_CHECKBOX."'".
                ($displayTypeId == SHOP_PRODUCT_ATTRIBUTE_TYPE_CHECKBOX
                    ? ' selected="selected"' : ''
                ).">".
                $_ARRAYLANG['TXT_CHECKBOXES_OPTION']."</option>\n".
            "<option value='".SHOP_PRODUCT_ATTRIBUTE_TYPE_TEXT_OPTIONAL."'".
                ($displayTypeId == SHOP_PRODUCT_ATTRIBUTE_TYPE_TEXT_OPTIONAL
                    ? ' selected="selected"' : ''
                ).">".
                $_ARRAYLANG['TXT_SHOP_PRODUCT_ATTRIBUTE_TYPE_TEXT_OPTIONAL']."</option>\n".
            "<option value='".SHOP_PRODUCT_ATTRIBUTE_TYPE_TEXT_MANDATORY."'".
                ($displayTypeId == SHOP_PRODUCT_ATTRIBUTE_TYPE_TEXT_MANDATORY
                    ? ' selected="selected"' : ''
                ).">".
                $_ARRAYLANG['TXT_SHOP_PRODUCT_ATTRIBUTE_TYPE_TEXT_MANDATORY']."</option>\n".
            "<option value='".SHOP_PRODUCT_ATTRIBUTE_TYPE_UPLOAD_OPTIONAL."'".
                ($displayTypeId == SHOP_PRODUCT_ATTRIBUTE_TYPE_UPLOAD_OPTIONAL
                    ? ' selected="selected"' : ''
                ).">".
                $_ARRAYLANG['TXT_SHOP_PRODUCT_ATTRIBUTE_TYPE_UPLOAD_OPTIONAL']."</option>\n".
            "<option value='".SHOP_PRODUCT_ATTRIBUTE_TYPE_UPLOAD_MANDATORY."'".
                ($displayTypeId == SHOP_PRODUCT_ATTRIBUTE_TYPE_UPLOAD_MANDATORY
                    ? ' selected="selected"' : ''
                ).">".
                $_ARRAYLANG['TXT_SHOP_PRODUCT_ATTRIBUTE_TYPE_UPLOAD_MANDATORY']."</option>\n".
            "</select>\n";
    }


    /**
     * Generate a list of the input boxes containing the values of an
     * Attribute.
     * @access   private
     * @param    integer     $attributeId    ID of the ProductAttribute name
     * @param    string      $name           Name and ID of the input box
     * @param    string      $content        ProductAttribute value field
     * @param    integer     $maxlength      Maximum length of the input box
     * @param    string      $style          CSS style for the input box
     * @return   string      $inputBoxes     String with HTML code
     */
    static function getAttributeInputBoxes($name_id, $name, $content, $maxlength='', $style='')
    {
        $inputBoxes = '';
        $select = true;
        $arrAttributeName = ProductAttributes::getNameArray($name_id);
        $display_type = $arrAttributeName['type'];
        foreach (ProductAttributes::getValueArray($name_id) as $value_id => $arrAttributeValue) {
            $inputBoxes .=
                '<input type="text" name="'.$name.'['.$value_id.']" '.
                'id="'.$name.'-'.$value_id.'" '.
                'value="'.$arrAttributeValue[$content].'"'.
                ($maxlength ? ' maxlength="'.$maxlength.'"' : '').
                ' style="display: '.($select ? 'inline' : 'none').';'.
                    ($style ? " $style" : '').
                '" onchange="updateAttributeValueList('.
                    $name_id.','.$value_id.')"'.
                // For text and file upload options, disable the value field
                ($content == 'value' && $display_type > 3
                    ? ' disabled="disabled"' : ''
                ).' />';
            $select = false;
        }
        return $inputBoxes;
    }


    /**
     * Returns HTML code for the value menu for each ProductAttribute
     *
     * Used in the Backend for selecting and editing.
     * @global  array       $_ARRAYLANG     Language array
     * @param   integer     $attributeId    ID of the ProductAttribute name
     * @param   string      $name           Name of the menu
     * @param   integer     $selectedId     ID of the selected value
     * @param   string      $onchange       Javascript onchange event of the menu
     * @param   string      $style          CSS style declaration for the menu
     * @return  string      $menu           Contains the value menus
     */
    function getAttributeValueMenu(
        $name_id, $name, $selectedId=0, $onchange='', $style=''
    ) {
        global $_ARRAYLANG;

        $arrValues = self::getValueArray($name_id);
//echo("PAs::getAttributeValueMenu($name_id, $name, $selectedId, $onchange, $style):  Values: ".var_export($arrValues, true)."<br />");
        // No options, or an error occurred
        if (!$arrValues) return '';
        $menu =
            '<select name="'.$name.'['.$name_id.'][]" '.
            'id="'.$name.'-'.$name_id.'" size="1"'.
            ($onchange ? ' onchange="'.$onchange.'"' : '').
            ($style ? ' style="'.$style.'"' : '').'>'."\n";
        foreach ($arrValues as $value_id => $arrValue) {
            $menu .=
                '<option value="'.$value_id.'"'.
                ($selectedId == $value_id ? ' selected="selected"' : '').'>'.
                $arrValue['value'].' ('.$arrValue['price'].' '.
                Currency::getDefaultCurrencySymbol().')</option>'."\n";
        }
        $menu .=
            '</select><br /><a href="javascript:{}" '.
            'id="attributeValueMenuLink-'.$name_id.'" '.
            'style="display: none;" '.
            'onclick="removeSelectedValues('.$name_id.')" '.
            'title="'.$_ARRAYLANG['TXT_SHOP_REMOVE_SELECTED_VALUE'].'" '.
            'alt="'.$_ARRAYLANG['TXT_SHOP_REMOVE_SELECTED_VALUE'].'">'.
            $_ARRAYLANG['TXT_SHOP_REMOVE_SELECTED_VALUE'].'</a>'."\n";
        return $menu;
    }


    /**
     * Returns a string containing Javascript variable definitions for
     * all Product Attribute values
     * @static
     * @access    private
     * @return    string    $jsVars    Javascript variables list
     */
    static function getAttributeJSVars()
    {
        $jsVars = '';
        foreach (ProductAttributes::getValueArray() as $name_id => $arrAttributeValue) {
            list($value_id, $arrValue) = each($arrAttributeValue);
            $jsVars .= "attributeValueId[$name_id] = $value_id;\n";
        }
        return $jsVars;
    }

}

?>
