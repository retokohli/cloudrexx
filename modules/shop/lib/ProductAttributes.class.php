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

require_once ASCMS_MODULE_PATH.'/shop/lib/Currency.class.php';

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
     * Clear all static data
     *
     * You *SHOULD* call this after updating database records.
     * @static
     */
    static function reset()
    {
        // These will be reinitialised the next time they are accessed
        self::$productId   = false;
        self::$arrName     = false;
        self::$arrValue    = false;
        self::$arrRelation = false;
    }


    /** NEW **/
    static function getNameArray($name_id=0)
    {
        // No name ID, and the array has not been initialized yet,
        // or some name ID, and the array element has not been initialized yet
        if (   (   empty($name_id)
                && empty(self::$arrName))
            || (   $name_id
                && empty(self::$arrName[$name_id]))) {
            // Initialize as much of the array as needed
            if (!self::initNameArray($name_id)) return false;
        }
        // No name ID:  Return the entire array
        if (empty($name_id)) return self::$arrName;
        // Otherwise, there is some name ID:  Return the selected array element
        return self::$arrName[$name_id];
    }


    static function initNameArray($name_id=0)
    {
        global $objDatabase;

        if (!isset(self::$arrName)) self::$arrName = array();
        $arrSqlName = Text::getSqlSnippets(
            '`name`.`text_name_id`', FRONTEND_LANG_ID,
            MODULE_ID, TEXT_SHOP_PRODUCTS_ATTRIBUTES_NAME
        );
        $query = "
            SELECT `name`.`id`, `name`.`display_type`".
                   $arrSqlName['field']."
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_name` AS `name`".
                   $arrSqlName['join'].
            ($name_id ? " WHERE `name`.`id`=$name_id" : '');
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        while (!$objResult->EOF) {
            $id = $objResult->fields['id'];
            $text_name_id = $objResult->fields[$arrSqlName['name']];
            $strName = $objResult->fields[$arrSqlName['text']];
            // Replace Text in a missing language by another, if available
            if ($strName === null) {
                $objText = Text::getById($text_name_id, 0);
                if ($objText)
                    $objText->markDifferentLanguage(FRONTEND_LANG_ID);
                    $strName = $objText->getText();
            }
            self::$arrName[$id] = array(
                'id' => $id,
                'name' => $strName,
                'type' => $objResult->fields['display_type'],
            );
            $objResult->MoveNext();
        }
        return true;
    }


    static function getValueArray($name_id=0)
    {
        // No name ID, and the array has not been initialized yet,
        // or some name ID, and the array element has not been initialized yet
        if (   (   empty($name_id)
                && empty(self::$arrValue))
            || (   $name_id
                && empty(self::$arrValue[$name_id]))) {
            // Initialize the array with all the values of the
            // Product Attribute selected, if any, or all
            if (!self::initValueArray($name_id)) return false;
        }
        // No name ID:  Return the entire array
        if (empty($name_id)) return self::$arrValue;
        // Otherwise, there is some name ID:  Return the selected array element
        return self::$arrValue[$name_id];
    }


    static function initValueArray($name_id=0)
    {
        global $objDatabase;

        if (!isset(self::$arrValue)) self::$arrValue = array();
        $arrSqlValue = Text::getSqlSnippets(
            '`value`.`text_value_id`', FRONTEND_LANG_ID,
            MODULE_ID, TEXT_SHOP_PRODUCTS_ATTRIBUTES_VALUE
        );
        $query = "
            SELECT `value`.`id`, `value`.`name_id`,
                   `value`.`price`".$arrSqlValue['field']."
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_value` as `value`".
                   $arrSqlValue['join'].
            ($name_id ? " WHERE `value`.`name_id`=$name_id" : '');
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        while (!$objResult->EOF) {
            $value_id = $objResult->fields['id'];
            $name_id = $objResult->fields['name_id'];
            $text_value_id = $objResult->fields[$arrSqlValue['name']];
            $strValue = $objResult->fields[$arrSqlValue['text']];
            // Replace Text in a missing language by another, if available
            if ($strValue === null) {
                $objText = Text::getById($text_value_id, 0);
                if ($objText)
                    $objText->markDifferentLanguage(FRONTEND_LANG_ID);
                    $strValue = $objText->getText();
            }
            if (!isset(self::$arrValue[$name_id]))
                self::$arrValue[$name_id] = array();
            self::$arrValue[$name_id][$value_id] = array(
                'id' => $value_id,
                'name_id' => $name_id,
                'value' => $strValue,
                'text_value_id' => $text_value_id,
                'price' => $objResult->fields['price'],
            );
            $objResult->MoveNext();
        }
        return true;
    }


    /**
     * @todo
     */
    static function getRelationArray($product_id=0)
    {
        // No Product ID, and the array has not been initialized yet,
        // or some Product ID, and the array element has not been initialized yet
        if (   (   empty($product_id)
                && empty(self::$arrProduct))
            || (   $product_id
                && empty(self::$arrProduct[$product_id]))) {
            // Initialize the array with all the values of the
            // Product Attribute selected, if any, or all
            if (!self::initRelationArray($product_id)) return false;
        }
        // No Product ID:  Return the entire array
        if (empty($product_id)) return self::$arrProduct;
        // Otherwise, there is some Product ID:  Return the selected array element
        return self::$arrProduct[$product_id];

    }


    /**
     * @todo
     */
    static function initRelationArray($product_id=0)
    {
        global $objDatabase;

        $query = "
            SELECT `product_id`, `attributes_value_id`, `sort_id`
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes`".
            ($product_id ? " WHERE `product_id`=$productId" : '')."
             ORDER BY `sort_id` ASC
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        if (!isset(self::$arrRelation)) self::$arrRelation = array();
        while (!$objResult->EOF) {
            $product_id = $objResult->fields['product_id'];
            $value_id = $objResult->fields['attributes_value_id'];
            if (!isset(self::$arrRelation[$product_id]))
                self::$arrRelation[$product_id] = array();
            $arrRelation[$product_id][$value_id] = $objResult->fields['sort_id'];
            $objResult->MoveNext();
        }
        return true;

    }


    /**
     * Return an array of Attribute value IDs related to the given
     * Product ID.
     *
     * The array has the form
     * array(
     *  value ID => array(
     *      value ID => value ID,
     *      order   => sorting order,
     *  ),
     *  ...
     * @param   integer     $productId      The Product ID
     * @return  array                       Array of ProductAttribute value IDs
     *                                      upon success, false otherwise.
     * @static
    static function getValueIdArrayByProductId($productId)
    {
        global $objDatabase;

        $query = "
            SELECT attributes_value_id, sort_id
              FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes
             WHERE product_id=$productId
             ORDER BY sort_id ASC
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        $arrValue = array();
        while (!$objResult->EOF) {
            $value_id = $objResult->fields['attributes_value_id'];
            $arrValue[$value_id] = array(
                'value_id' => $id,
                'order'   => $objResult->fields['sort_id'],
            );
            $objResult->MoveNext();
        }
        return $arrValue;
    }
     */


    /**
     * Creates a relation between the Product Attribute value ID and the
     * Product ID.
     *
     * The optional $order argument determines the order position of the value.
     * @static
     * @param   integer     $value_id        The ProductAttribute value ID
     * @param   integer     $productId      The Product ID
     * @param   integer     $order          The optional sorting order,
     *                                      defaults to 0 (zero)
     * @return  boolean                     True on success, false otherwise
     * @global  ADONewConnection
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
     * @global  ADONewConnection
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
     * @global  ADONewConnection
     * @todo    Remove Text records as well
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
     * @global  array
     * @param   integer     $attributeId    ID of the ProductAttribute name
     * @param   string      $name           Name of the menu
     * @param   integer     $selectedId     ID of the selected value
     * @param   string      $onchange       Javascript onchange event of the menu
     * @param   string      $style          CSS style declaration for the menu
     * @return  string      $menu           Contains the value menus
     */
    function getAttributeValueMenu(
        $name_id, $name, $selectedId=0, $onchange='', $style='')
    {
        global $_ARRAYLANG;

        $arrValues = self::getValueArray($name_id);
echo("PAs::getAttributeValueMenu($name_id, $name, $selectedId, $onchange, $style):  Values: ".var_export($arrValues, true)."<br />");
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
            'title="'.$_ARRAYLANG['TXT_REMOVE_SELECTED_VALUE'].'">'.
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
