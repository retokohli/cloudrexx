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
 * See {@link Attribute} for details.
 * @version     2.1.0
 * @package     contrexx
 * @subpackage  module_shop
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @todo        Test!
 */
class Attributes  // friend Product
{
    /**
     * The optional Product ID from which the Attributes are used,
     * or 0 (zero), meaning all Attributes whatsoever.
     * @var integer
     */
    private static $product_id = 0;

    /**
     * The array of Attribute names
     *
     * Includes the fields id, name, and display_type
     * @var array
     */
    private static $arrName;

    /**
     * The array of Attribute values
     *
     * Includes the fields id, name_id, value, and price
     * @var array
     */
    private static $arrValue;

    /**
     * The array of Attribute relations
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
        self::$arrName     = false;
        self::$arrValue    = false;
        self::$arrRelation = false;
    }


    /**
     * Returns an array of Attribute names.
     *
     * If the optional $product_id argument is greater than zero,
     * only names associated with this Product are returned,
     * all names found in the database otherwise.
     * @static
     * @access  public
     * @param   integer     $product_id      The optional Product ID
     * @return  array                       Array of Attribute names
     *                                      upon success, false otherwise.
     */
    static function getNameArrayByProductId($product_id=0)
    {
        global $objDatabase;

        $query = "
            SELECT DISTINCT `id`, `name`, `display_type`
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_name`
            ".($product_id
              ? "INNER JOIN `".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes`
                    ON `attributes_name_id`=`id`
                 WHERE `product_id`=$product_id
                 ORDER BY `sort_id` ASC
            " : '');
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        self::$arrName = array();
        while (!$objResult->EOF) {
            $id = $objResult->fields['id'];
            self::$arrName[$id] = array(
                'id'   => $id,
                'name' => $objResult->fields['name'],
                'type' => $objResult->fields['display_type'],
            );
            $objResult->MoveNext();
        }
        return self::$arrName;
    }


    /**
     * Returns an array of Attribute data for the given name ID
     *
     * This array contains no options, just the Attribute name and type.
     * It is a single entry of the result of {@see initNameArray()}.
     * @param   integer   $attribute_id    The name ID
     * @return  array                 The Attribute array
     */
    static function getNameArrayByNameId($attribute_id)
    {
        if (empty(self::$arrName) && !self::initNameArray()) return false;
        if (empty(self::$arrName[$attribute_id])) return false;
        return self::$arrName[$attribute_id];
    }


    /**
     * Returns an array of all available Attribute data arrays
     *
     * This array contains no options, just the Attribute name and type.
     * It is the complete array created by {@see initNameArray()}.
     * @return  array                 The Attribute array
     */
    static function getNameArray()
    {
        if (empty(self::$arrName) && !self::initNameArray()) return false;
        return self::$arrName;
    }


    /**
     * Initialises the array of Attribute name data
     *
     * This array contains no options, just the Attribute name and type.
     * The array has the form
     *  array(
     *    Attribute ID => array(
     *      'id'   => Attribute ID,
     *      'name' => Attribute name (according to FRONTEND_LANG_ID),
     *      'type' => Attribute type,
     *    ),
     *    ... more ...
     *  )
     * Note that internal calling methods like getNameArray() or
     * getNameArrayByNameId() make no use of the optional parameter, so that
     * the full array is initialised on the first call.
     * @param   integer   $attribute_id   The optional Attribute ID
     * @return  boolean                   True on success, false otherwise
     */
    static function initNameArray($attribute_id=0)
    {
        global $objDatabase;

        if (!isset(self::$arrName)) self::$arrName = array();
        $arrSqlName = Text::getSqlSnippets(
            '`name`.`text_name_id`', FRONTEND_LANG_ID,
            MODULE_ID, Attribute::TEXT_NAME
        );
        $query = "
            SELECT `name`.`id`, `name`.`display_type`".
                   $arrSqlName['field']."
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes_name` AS `name`".
                   $arrSqlName['join'].
            ($attribute_id ? " WHERE `name`.`id`=$attribute_id" : '');
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
                'id'   => $id,
                'name' => $strName,
                'type' => $objResult->fields['display_type'],
            );
            $objResult->MoveNext();
        }
        return true;
    }


    /**
     * Returns the full array of Options arrays available
     *
     * See {@see initValueArray()} for details on the array returned.
     * @return  array                     The Options array
     */
    static function getValueArray()
    {
        if (empty(self::$arrValue) && !self::initValueArray()) return false;
        return self::$arrValue;
    }


    /**
     * Returns the array of Options for the given Attribute ID
     *
     *
     * See {@see initValueArray()} for details on the array returned.
     * @return  array                     The Options array
     */
    static function getValueArrayByNameId($attribute_id)
    {
        if (empty(self::$arrValue) && !self::initValueArray()) return false;
        if (empty(self::$arrValue[$attribute_id])) return array();
        return self::$arrValue[$attribute_id];
    }


    /**
     * Initialises the Options array for the given Attribute ID, or any
     * Attributes if it is missing
     *
     * The array has the form
     *  array(
     *    Attribute ID => array(
     *      Option ID => array(
     *        'id' => The option ID,
     *        'name_id' => The Attribute ID,
     *        'value' => The option name (according to FRONTEND_LANG_ID),
     *        'text_value_id' => The option name Text ID,
     *        'price' => The option price (including the sign),
     *      ),
     *      ... more ...
     *    ),
     *    ... more ...
     *  )
     * @param   integer   $attribute_id   The optional Attribute ID
     * @return  boolean                   True on success, false otherwise
     */
    static function initValueArray($attribute_id=0)
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
            ($attribute_id ? " WHERE `value`.`name_id`=$attribute_id" : '');
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        while (!$objResult->EOF) {
            $option_id = $objResult->fields['id'];
            $attribute_id = $objResult->fields['name_id'];
            $text_value_id = $objResult->fields[$arrSqlValue['name']];
            $strValue = $objResult->fields[$arrSqlValue['text']];
            // Replace Text in a missing language by another, if available
            if ($strValue === null) {
                $objText = Text::getById($text_value_id, 0);
                if ($objText)
                    $objText->markDifferentLanguage(FRONTEND_LANG_ID);
                    $strValue = $objText->getText();
            }
            if (!isset(self::$arrValue[$attribute_id]))
                self::$arrValue[$attribute_id] = array();
            self::$arrValue[$attribute_id][$option_id] = array(
                'id'            => $option_id,
                'name_id'       => $attribute_id,
                'value'         => $strValue,
                'text_value_id' => $text_value_id,
                'price'         => $objResult->fields['price'],
            );
            $objResult->MoveNext();
        }
        return true;
    }


    /**
     * Returns an array of Product-Option relations for the given Product ID
     *
     * See {@see initRelationArray()} for details on the array.
     * @param   integer   $product_id     The Product ID
     * @return  array                     The relation array on success,
     *                                    false otherwise
     */
    static function getRelationArray($product_id)
    {
        if (empty($product_id)) return false;
        if (   !isset(self::$arrRelation)
            || !isset(self::$arrRelation[$product_id])) {
            if (!self::initRelationArray($product_id)) return false;
        }
        // No options for this Product ID:  Return the empty array
        if (empty(self::$arrRelation[$product_id])) return array();
        // Otherwise, there are some options.  Return that array element
        return self::$arrRelation[$product_id];
    }


    /**
     * Initialises the Product-Option relation array
     *
     * If the optional Product ID is missing, all Products are included.
     * The resulting array has the form
     *  array(
     *    Product ID => array(
     *      Option ID => The ordinal value (for sorting),
     *      ... more ...
     *    ),
     *    ... more ...
     *  )
     * @param   integer   $product_id     The optional Product ID
     * @return  boolean                   True on success, false otherwise
     */
    static function initRelationArray($product_id=0)
    {
        global $objDatabase;

        $query = "
            SELECT `product_id`, `attributes_value_id`, `sort_id`
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes`".
            ($product_id ? " WHERE `product_id`=$product_id" : '')."
             ORDER BY `sort_id` ASC";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        if (!isset(self::$arrRelation)) self::$arrRelation = array();
        while (!$objResult->EOF) {
            $product_id = $objResult->fields['product_id'];
            $option_id = $objResult->fields['attributes_value_id'];
            if (!isset(self::$arrRelation[$product_id]))
                self::$arrRelation[$product_id] = array();
            self::$arrRelation[$product_id][$option_id] = $objResult->fields['sort_id'];
            $objResult->MoveNext();
        }
        return true;
    }


    /**
     * Creates a relation between the given option and Product IDs.
     *
     * The optional $order argument determines the ordinal value.
     * @static
     * @param   integer     $option_id      The option ID
     * @param   integer     $product_id     The Product ID
     * @param   integer     $order          The optional ordinal value,
     *                                      defaults to 0 (zero)
     * @return  boolean                     True on success, false otherwise
     * @global  ADONewConnection  $objDatabase    Database connection object
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function addValueToProduct($option_id, $product_id, $order=0)
    {
        global $objDatabase;

        $attribute_id = Attribute::getNameIdByValueId($option_id);
        if ($attribute_id <= 0) return false;
        $query = "
            INSERT INTO `".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes` (
                `product_id`,
                `attributes_name_id`,
                `attributes_value_id`,
                `sort_id`
            ) VALUES (
                $product_id,
                $attribute_id,
                $option_id,
                $order
            )";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) return true;
        return false;
    }


    /**
     * Remove all Product-option relations for the given Product ID.
     * @static
     * @param   integer     $product_id     The Product ID
     * @return  boolean                     True on success, false otherwise.
     * @global  ADONewConnection  $objDatabase    Database connection object
     */
    function deleteByProductId($product_id)
    {
        global $objDatabase;

        $query = "
            DELETE FROM `".DBPREFIX."module_shop".MODULE_INDEX."_products_attributes`
             WHERE `product_id`=$product_id";
        $objResult = $objDatabase->Execute($query);
        return $objResult;
    }


    /**
     * Delete all Attributes from the database
     *
     * Clears all names, values, and relations.  Use with due care!
     * @static
     * @return  boolean                     True on success, false otherwise.
     * @global  ADONewConnection  $objDatabase    Database connection object
     */
    static function deleteAll()
    {
        global $objDatabase;

        $arrAttributes = Attributes::getNameArray();
        foreach (array_keys($arrAttributes) as $attribute_id) {
            if (!Attribute::deleteByAttributeId($attribute_id))
                return false;
        }
        return true;
    }


    static function getAttributeDisplayTypeMenu($attribute_id, $displayTypeId='0', $onchange='')
    {
        global $_ARRAYLANG;

        return
            "<select name='attributeDisplayType[$attribute_id]' ".
                "size='1' style='width:170px;'".
                (empty($onchange) ? '' : ' onchange="'.$onchange.'"').
                ">\n".
            "<option value='".Attribute::TYPE_MENU_OPTIONAL."'".
                ($displayTypeId == Attribute::TYPE_MENU_OPTIONAL
                    ? ' selected="selected"' : ''
                ).">".
                $_ARRAYLANG['TXT_MENU_OPTION']."</option>\n".
            "<option value='".Attribute::TYPE_MENU_MANDATORY."'".
                ($displayTypeId == Attribute::TYPE_MENU_MANDATORY
                    ? ' selected="selected"' : ''
                ).">".
                $_ARRAYLANG['TXT_SHOP_MENU_OPTION_DUTY']."</option>\n".
            "<option value='".Attribute::TYPE_RADIOBUTTON."'".
                ($displayTypeId == Attribute::TYPE_RADIOBUTTON
                    ? ' selected="selected"' : ''
                ).">".
                $_ARRAYLANG['TXT_RADIOBUTTON_OPTION']."</option>\n".
            "<option value='".Attribute::TYPE_CHECKBOX."'".
                ($displayTypeId == Attribute::TYPE_CHECKBOX
                    ? ' selected="selected"' : ''
                ).">".
                $_ARRAYLANG['TXT_CHECKBOXES_OPTION']."</option>\n".
            "<option value='".Attribute::TYPE_TEXT_OPTIONAL."'".
                ($displayTypeId == Attribute::TYPE_TEXT_OPTIONAL
                    ? ' selected="selected"' : ''
                ).">".
                $_ARRAYLANG['TXT_SHOP_PRODUCT_ATTRIBUTE_TYPE_TEXT_OPTIONAL']."</option>\n".
            "<option value='".Attribute::TYPE_TEXT_MANDATORY."'".
                ($displayTypeId == Attribute::TYPE_TEXT_MANDATORY
                    ? ' selected="selected"' : ''
                ).">".
                $_ARRAYLANG['TXT_SHOP_PRODUCT_ATTRIBUTE_TYPE_TEXT_MANDATORY']."</option>\n".
            "<option value='".Attribute::TYPE_UPLOAD_OPTIONAL."'".
                ($displayTypeId == Attribute::TYPE_UPLOAD_OPTIONAL
                    ? ' selected="selected"' : ''
                ).">".
                $_ARRAYLANG['TXT_SHOP_PRODUCT_ATTRIBUTE_TYPE_UPLOAD_OPTIONAL']."</option>\n".
            "<option value='".Attribute::TYPE_UPLOAD_MANDATORY."'".
                ($displayTypeId == Attribute::TYPE_UPLOAD_MANDATORY
                    ? ' selected="selected"' : ''
                ).">".
                $_ARRAYLANG['TXT_SHOP_PRODUCT_ATTRIBUTE_TYPE_UPLOAD_MANDATORY']."</option>\n".
            "</select>\n";
    }


    /**
     * Returns a string containing HTML code for a list of input boxes
     * with the option values or prices of an Attributes' options
     *
     * Only the first of the input elements has its display style set to
     * 'inline', the others are invisible ('none').
     * See {@see _showAttributeOptions()} for an example on how it's used.
     * @access   private
     * @param    integer     $attribute_id  The Attribute ID
     * @param    string      $name          The name and ID attribute for the
     *                                      input element
     * @param    string      $content       The field content
     *                                      ('value' or 'price')
     * @param    integer     $maxlength     The maximum length of the input box
     * @param    string      $style         The optional CSS style for the
     *                                      input element
     * @return   string                     The string with HTML code
     */
    static function getAttributeInputBoxes(
        $attribute_id, $name, $content, $maxlength='', $style=''
    ) {
        $inputBoxes = '';
        $select = true;
        $arrAttributeName = Attributes::getNameArrayByNameId($attribute_id);
        $display_type = $arrAttributeName['type'];
        foreach (Attributes::getValueArrayByNameId($attribute_id) as $option_id => $arrAttributeValue) {
            $inputBoxes .=
                '<input type="text" name="'.$name.'['.$option_id.']" '.
                'id="'.$name.'-'.$option_id.'" '.
                'value="'.$arrAttributeValue[$content].'"'.
                ($maxlength ? ' maxlength="'.$maxlength.'"' : '').
                ' style="display: '.($select ? 'inline' : 'none').';'.
                    ($style ? " $style" : '').
                '" onchange="updateAttributeValueList('.
                    $attribute_id.','.$option_id.')"'.
                // For text and file upload options, disable the value field.
                // This does not apply to the price field, however.
                (   $content == 'value'
                 && $display_type >= Attribute::TYPE_TEXT_OPTIONAL
                    ? ' disabled="disabled"' : ''
                ).' />';
            $select = false;
        }
        return $inputBoxes;
    }


    /**
     * Returns HTML code for the value menu for each Attribute
     *
     * Used in the Backend for selecting and editing.
     * @global  array       $_ARRAYLANG     Language array
     * @param   integer     $attribute_id   ID of the Attribute name
     * @param   string      $name           Name of the menu
     * @param   integer     $selectedId     ID of the selected value
     * @param   string      $onchange       Javascript onchange event of the menu
     * @param   string      $style          CSS style declaration for the menu
     * @return  string      $menu           Contains the value menus
     */
    function getAttributeValueMenu(
        $attribute_id, $name, $selectedId=0, $onchange='', $style=''
    ) {
        global $_ARRAYLANG;

        $arrValues = self::getValueArrayByNameId($attribute_id);
        // No options, or an error occurred
        if (!$arrValues) return '';
        $menu =
            '<select name="'.$name.'['.$attribute_id.'][]" '.
            'id="'.$name.'-'.$attribute_id.'" size="1"'.
            ($onchange ? ' onchange="'.$onchange.'"' : '').
            ($style ? ' style="'.$style.'"' : '').'>'."\n";
        foreach ($arrValues as $option_id => $arrValue) {
            $menu .=
                '<option value="'.$option_id.'"'.
                ($selectedId == $option_id ? ' selected="selected"' : '').'>'.
                $arrValue['value'].' ('.$arrValue['price'].' '.
                Currency::getDefaultCurrencySymbol().')</option>'."\n";
        }
        $menu .=
            '</select><br /><a href="javascript:{}" '.
            'id="attributeValueMenuLink-'.$attribute_id.'" '.
            'style="display: none;" '.
            'onclick="removeSelectedValues('.$attribute_id.')" '.
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
        $highestIndex = 0;
        foreach (Attributes::getValueArray() as $attribute_id => $arrAttributeValue) {
            $first = true;
            foreach (array_keys($arrAttributeValue) as $option_id) {
                if ($first)
                    $jsVars .= "attributeValueId[$attribute_id] = $option_id;\n";
                $first = false;
                if ($option_id > $highestIndex) $highestIndex = $option_id;
            }
        }
        $jsVars .= "\nindex = ".$highestIndex.";\n";
        return $jsVars;
    }

}

?>
