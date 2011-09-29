<?php

/**
 * Shop Product Attributes
 *
 * @version     2.2.0
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
 * @version     2.2.0
 * @package     contrexx
 * @subpackage  module_shop
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @todo        Test!
 */
class Attributes
{
    /**
     * The array of Attribute names
     *
     * Includes the fields id, name, and type
     * @var array
     */
    private static $arrAttributes;

    /**
     * The array of options
     *
     * See {@see initOptionArray()} for details
     * @var array
     */
    private static $arrOptions;

    /**
     * The array of Attribute relations
     *
     * See {@see initRelationArray() for details.
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
        self::$arrAttributes = false;
        self::$arrOptions    = false;
        self::$arrRelation   = false;
    }


    /**
     * OBSOLETE AND DISFUNCT
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
    static function getArrayByProductId($product_id=0)
    {
        global $objDatabase;

        $query = "
            SELECT DISTINCT `id`, `name`, `type`
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_attribute`
            ".($product_id
              ? "INNER JOIN `".DBPREFIX."module_shop".MODULE_INDEX."_rel_product_attribute`
                    ON `attribute_id`=`id`
                 WHERE `product_id`=$product_id
                 ORDER BY `ord` ASC
            " : '');
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        self::$arrAttributes = array();
        while (!$objResult->EOF) {
            $id = $objResult->fields['id'];
            self::$arrAttributes[$id] = array(
                'id'   => $id,
                'name' => $objResult->fields['name'],
                'type' => $objResult->fields['type'],
            );
            $objResult->MoveNext();
        }
        return self::$arrAttributes;
    }
    */


    /**
     * Returns an array of Attribute data for the given name ID
     *
     * This array contains no options, just the Attribute name and type.
     * It is a single entry of the result of {@see initAttributeArray(()}.
     * @param   integer   $attribute_id    The name ID
     * @return  array                 The Attribute array
     */
    static function getArrayById($attribute_id)
    {
        if (   !is_array(self::$arrAttributes)
            && !self::initAttributeArray()) return false;
        if (empty(self::$arrAttributes[$attribute_id])) return false;
        return self::$arrAttributes[$attribute_id];
    }


    /**
     * Returns an array of Attribute objects
     * @param   integer   $count            The number of matching records,
     *                                      by reference
     * @param   integer   $offset           The optional offset,
     *                                      defaults to 0 (zero)
     * @param   integer   $limit            The optional limit for the number
     *                                      of IDs returned,
     *                                      defaults to null (all)
     * @param   string    $order            The optional order field and
     *                                      direction,
     *                                      defaults to ID, ascending
     * @param   string    $filter           The optional filter to be applied
     *                                      to the name, defaults to null (any)
     * @return  array                       The array of Attributes
     *                                      on success, false otherwise
     * @global  mixed     $objDatabase      The Database connection
     */
    static function getArray(
        &$count, $offset=0, $limit=null, $order='`attribute`.`id` ASC', $filter=null
    ) {
        $arrId = self::getIdArray(
            $count, $offset, $limit, $order, $filter);
        if ($arrId === false) return false;
        $arrAttribute = array();
        foreach ($arrId as $id) {
            $objAttribute = Attribute::getById($id);
            // This should never happen
            if (!$objAttribute) {
//DBG::log("Attributes::getArray(): Warning: failed to get Attribute for ID $id");
                --$count;
                continue;
            }
            $arrAttribute[$id] = $objAttribute;
        }
        return $arrAttribute;
    }


    /**
     * Returns an array of all Attribute names
     *
     * Backend use only.
     * The resulting array is limited to the first 1000 Attributes found,
     * if the $limit parameter value is missing.
     * @param   integer   $count            The number of matching records,
     *                                      by reference
     * @param   integer   $offset           The optional offset,
     *                                      defaults to 0 (zero)
     * @param   integer   $limit            The optional limit for the number
     *                                      of IDs returned,
     *                                      defaults to null (all)
     * @param   string    $order            The optional order field and
     *                                      direction,
     *                                      defaults to ID, ascending
     * @param   string    $filter           The optional filter to be applied
     *                                      to the name, defaults to null (any)
     * @return  array                       The array of Attribute
     *                                      names on success, false otherwise
     */
    static function getNameArray(
        &$count, $offset=0, $limit=1000, $order=null, $filter=null
    ) {
        $count = 0;
        $arrAttribute = self::getArray(
            $count, $offset, $limit, $order, $filter);
        if ($arrAttribute === false) return false;
        $arrName = array();
        foreach ($arrAttribute as $id => $objAttribute) {
            $arrName[$id] = $objAttribute->getName();
        }
        return $arrName;
    }


    /**
     * Returns an array of Attribute IDs
     * @param   integer   $count            The number of matching records,
     *                                      by reference
     * @param   integer   $offset           The optional offset,
     *                                      defaults to 0 (zero)
     * @param   integer   $limit            The optional limit for the number
     *                                      of IDs returned,
     *                                      defaults to null (all)
     * @param   string    $order            The optional order field and
     *                                      direction,
     *                                      defaults to ID, ascending
     * @param   string    $filter           The optional filter to be applied
     *                                      to the name, defaults to null (any)
     * @return  array                       The array of IDs on success,
     *                                      false otherwise
     * @global  mixed     $objDatabase      The Database connection
     */
    static function getIdArray(
        &$count, $offset=0, $limit=null, $order='`attribute`.`id` ASC', $filter=null
    ) {
        global $objDatabase;

//DBG::log("Attributes::getIdArray(count $count, offset $offset, limit $limit, order $order, filter $filter): Entered");

        $arrSqlName = Text::getSqlSnippets(
            '`attribute`.`id`', FRONTEND_LANG_ID, 'shop',
            array('name' => Attribute::TEXT_ATTRIBUTE_NAME));
        $query_count = "
            SELECT COUNT(*) AS `numof`";
        $query_select = "
            SELECT `attribute`.`id`, ".$arrSqlName['field'];
        $query_from = "
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_attribute` AS `attribute`".
                   $arrSqlName['join'];
        $query_where =
            (empty($filter['name'])
              ? ''
              : " WHERE `name` LIKE '%".addslashes($filter['name'])."%'" );
        if (!empty($filter['product_id'])) {
            //$query_select = "";
            $query_from .= "
                INNER JOIN `".DBPREFIX."module_shop".MODULE_INDEX."_option` AS `option`
                   ON `attribute`.`id`=`option`.`attribute_id`
                INNER JOIN `".DBPREFIX."module_shop".MODULE_INDEX."_rel_product_attribute` AS `rel`
                   ON `option`.`id`=`rel`.`option_id`";
            $query_where = "
                  AND `rel`.`product_id`=".$filter['product_id'];
        }
        $query_order = ($order ? " ORDER BY $order" : '');
        $count = 0;
        if (empty($limit)) $limit = -1;
        $objResult = $objDatabase->SelectLimit(
            $query_select.$query_from.$query_where.$query_order,
            $limit, $offset);
        if (!$objResult) {
            return false;
        }
        $arrId = array();
        while (!$objResult->EOF) {
            $arrId[] = $objResult->fields['id'];
            $objResult->MoveNext();
        }
        $objResult = $objDatabase->Execute(
            $query_count.$query_from.$query_where);
        if (!$objResult) {
            return false;
        }
        $count = $objResult->fields['numof'];
        return $arrId;
    }


    /**
     * OBSOLETE
     * Returns an array of all available Attribute data arrays
     *
     * This array contains no options, just the Attribute name and type.
     * It is the complete array created by {@see initAttributeArray(()}.
     * @return  array                 The Attribute array
    static function getArray()
    {
        if (   !is_array(self::$arrAttributes)
            && !self::initAttributeArray()) return false;
        return self::$arrAttributes;
    }
     */


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
     * If you specify a valid $attribute_id parameter value, only that Attribute
     * is initialized.  But note that the static array is not automatically
     * cleared, so you can use it as a cache.
     * @param   integer   $attribute_id   The optional Attribute ID
     * @return  boolean                   True on success, false otherwise
     */
    static function initAttributeArray($attribute_id=0)
    {
        global $objDatabase;

        if (!isset(self::$arrAttributes)) self::$arrAttributes = array();
        $arrSqlName = Text::getSqlSnippets(
            '`attribute`.`id`', FRONTEND_LANG_ID, 'shop',
            array('name' => Attribute::TEXT_ATTRIBUTE_NAME));
        $query = "
            SELECT `attribute`.`id`, `attribute`.`type`, ".
                   $arrSqlName['field']."
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_attribute` AS `attribute`".
                   $arrSqlName['join'].
            ($attribute_id ? " WHERE `attribute`.`id`=$attribute_id" : '');
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        while (!$objResult->EOF) {
            $id = $objResult->fields['id'];
            $strName = $objResult->fields['name'];
            // Replace Text in a missing language by another, if available
            if ($strName === null) {
                $strName = Text::getById($id, 'shop',
                    Attribute::TEXT_ATTRIBUTE_NAME)->content();
            }
            self::$arrAttributes[$id] = array(
                'id'   => $id,
                'name' => $strName,
                'type' => $objResult->fields['type'],
            );
            $objResult->MoveNext();
        }
        return true;
    }


    /**
     * Returns the full array of Options arrays available
     *
     * See {@see initOptionArray()} for details on the array returned.
     * @return  array                     The Options array
     */
    static function getOptionArray()
    {
        if (   !is_array(self::$arrOptions)
            && !self::initOptionArray()) return false;
        return self::$arrOptions;
    }


    /**
     * Returns the array of Options for the given Attribute ID
     *
     * See {@see initOptionArray()} for details on the array returned.
     * @return  array                     The Options array
     */
    static function getOptionArrayByAttributeId($attribute_id)
    {
        if (   !is_array(self::$arrOptions)
            && !self::initOptionArray()) return false;
        if (empty(self::$arrOptions[$attribute_id])) return array();
        return self::$arrOptions[$attribute_id];
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
     *        'attribute_id' => The Attribute ID,
     *        'value' => The option name (according to FRONTEND_LANG_ID),
     *        'price' => The option price (including the sign),
     *      ),
     *      ... more ...
     *    ),
     *    ... more ...
     *  )
     * @param   integer   $attribute_id   The optional Attribute ID
     * @return  boolean                   True on success, false otherwise
     */
    static function initOptionArray($attribute_id=0)
    {
        global $objDatabase;

        if (!isset(self::$arrOptions)) self::$arrOptions = array();
        $arrSqlValue = Text::getSqlSnippets(
            '`value`.`id`', FRONTEND_LANG_ID, 'shop',
            array('name' => Attribute::TEXT_OPTION_NAME));
        $query = "
            SELECT `value`.`id`, `value`.`attribute_id`,
                   `value`.`price`, ".$arrSqlValue['field']."
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_option` as `value`".
                   $arrSqlValue['join'].
            ($attribute_id ? " WHERE `value`.`attribute_id`=$attribute_id" : '');
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        while (!$objResult->EOF) {
            $option_id = $objResult->fields['id'];
            $attribute_id = $objResult->fields['attribute_id'];
            $strValue = $objResult->fields['name'];
            // Replace Text in a missing language by another, if available
            if ($strValue === null) {
                $strValue = Text::getById($option_id, 'shop',
                    Attribute::TEXT_OPTION_NAME)->content();
            }
            if (!isset(self::$arrOptions[$attribute_id]))
                self::$arrOptions[$attribute_id] = array();
            self::$arrOptions[$attribute_id][$option_id] = array(
                'id'            => $option_id,
                'attribute_id'  => $attribute_id,
                'value'         => $strValue,
                'price'         => $objResult->fields['price'],
            );
            $objResult->MoveNext();
        }
        return true;
    }


    /**
     * Return the name of the option selected by its ID
     * from the database.
     *
     * Returns false on error, or the empty string if the value cannot be
     * found.
     * @param   integer   $option_id    The option ID
     * @return  mixed                   The option name on success,
     *                                  or false otherwise.
     * @static
     * @global  mixed     $objDatabase  Database object
     */
    static function getOptionNameById($option_id)
    {
        global $objDatabase;

        $arrSqlValue = Text::getSqlSnippets(
            '`option`.`id`', FRONTEND_LANG_ID, 'shop',
            array('name' => Attribute::TEXT_OPTION_NAME));
        $query = "
            SELECT 1, ".$arrSqlValue['field']."
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_option` AS `option`".
                   $arrSqlValue['join']."
             WHERE `option`.`id`=$option_id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult || $objResult->EOF) return false;
        $strName = $objResult->fields['name'];
        if (is_null($strName)) {
            $strName = Text::getById(
                $option_id, 'shop', Attribute::TEXT_OPTION_NAME)->content();
        }
        return $strName;
    }


    /**
     * Return the price of the option selected by its ID
     * from the database.
     *
     * Returns false on error or if the value cannot be found.
     * @param   integer   $option_id    The option ID
     * @return  double                  The option price on success,
     *                                  or false on failure.
     * @static
     * @global  mixed     $objDatabase  Database object
     */
    static function getOptionPriceById($option_id)
    {
        global $objDatabase;

        $query = "
            SELECT `price`
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_option`
             WHERE `id`=$option_id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult || $objResult->EOF) return false;
        return $objResult->fields['price'];
    }


    static function getOptionPriceSum($attribute_id, $arrOptionId)
    {
        if (   !is_array(self::$arrAttributes)
            && !self::initAttributeArray()) return null;
        if (empty(self::$arrAttributes[$attribute_id])) {
DBG::log("Attributes::getOptionPriceSum(): ERROR: unknown Attribute ID $attribute_id in Attributes!");
            return null;
        }
        if (   !is_array(self::$arrOptions)
            && !self::initOptionArray()) return null;
        if (empty(self::$arrOptions[$attribute_id])) {
DBG::log("Attributes::getOptionPriceSum(): ERROR: unknown Attribute ID $attribute_id in options!");
            return null;
        }
        $optionPriceSum = 0;
        $arrOption = self::$arrOptions[$attribute_id];
        if (   self::$arrAttributes[$attribute_id]['type'] == Attribute::TYPE_TEXT_OPTIONAL
            || self::$arrAttributes[$attribute_id]['type'] == Attribute::TYPE_TEXT_MANDATORY
            || self::$arrAttributes[$attribute_id]['type'] == Attribute::TYPE_UPLOAD_OPTIONAL
            || self::$arrAttributes[$attribute_id]['type'] == Attribute::TYPE_UPLOAD_MANDATORY) {
            $arrOption = current($arrOption);
            $productOptionPrice = $arrOption['price'];
            $optionPriceSum += $productOptionPrice;
//DBG::log("Attributes::getOptionPriceSum(): Attribute ID $attribute_id: price for text/file option: $productOptionPrice");
        } else {
            foreach ($arrOptionId as $option_id) {
                if (!is_numeric($option_id)) {
DBG::log("Attributes::getOptionPriceSum(): ERROR: option ID $option_id is not numeric!");
//DBG::log("Attributes::getOptionPriceSum(): Options Array #$index: ".var_export($arrOptionId, true));
                    continue;
                }
                $productOptionPrice = $arrOption[$option_id]['price'];
                $optionPriceSum += $productOptionPrice;
//DBG::log("Attributes::getOptionPriceSum(): Attribute ID $attribute_id: price for regular option: $productOptionPrice");
            }
        }
        return $optionPriceSum;
    }


    /**
     * Returns an array of Product-Option relations for the given Product ID
     *
     * See {@see initRelationArray()} for details on the array.
     * @param   integer   $product_id     The Product ID
     * @return  array                     The relation array on success,
     *                                    null otherwise
     */
    static function getRelationArray($product_id)
    {
        // New Products don't have any associated options
        if (empty($product_id)) return array();
        if (   !isset(self::$arrRelation)
            || !isset(self::$arrRelation[$product_id])) {
            if (!self::initRelationArray($product_id)) return null;
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
     * The option IDs for any Product are sorted by their ascending ordinal
     * value.
     * @param   integer   $product_id     The optional Product ID
     * @return  boolean                   True on success, false otherwise
     */
    static function initRelationArray($product_id=0)
    {
        global $objDatabase;

        $query = "
            SELECT `product_id`, `option_id`, `ord`
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_rel_product_attribute`".
            ($product_id ? " WHERE `product_id`=$product_id" : '')."
             ORDER BY `ord` ASC";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        if (!isset(self::$arrRelation)) self::$arrRelation = array();
        while (!$objResult->EOF) {
            $product_id = $objResult->fields['product_id'];
            if (!isset(self::$arrRelation[$product_id]))
                self::$arrRelation[$product_id] = array();
            self::$arrRelation[$product_id][$objResult->fields['option_id']] =
                $objResult->fields['ord'];
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
    static function addOptionToProduct($option_id, $product_id, $order=0)
    {
        global $objDatabase;

        $query = "
            INSERT INTO `".DBPREFIX."module_shop".MODULE_INDEX."_rel_product_attribute` (
                `product_id`,
                `option_id`,
                `ord`
            ) VALUES (
                $product_id,
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
    static function removeFromProduct($product_id)
    {
        global $objDatabase;

        $query = "
            DELETE FROM `".DBPREFIX."module_shop".MODULE_INDEX."_rel_product_attribute`
             WHERE `product_id`=$product_id";
        $objResult = $objDatabase->Execute($query);
        return (boolean)$objResult;
    }


    /**
     * Delete all Attributes from the database
     *
     * Clears all Attributes, options, and relations.  Use with due care!
     * @static
     * @return  boolean                     True on success, false otherwise.
     * @global  ADONewConnection  $objDatabase    Database connection object
     */
    static function deleteAll()
    {
        global $objDatabase;

        $arrAttributes = self::getArray();
        foreach (array_keys($arrAttributes) as $attribute_id) {
            $objAttribute = Attribute::getById($attribute_id);
            if (!$objAttribute->delete()) return false;
        }
        return true;
    }


    static function getDisplayTypeMenu($attribute_id, $displayTypeId='0', $onchange='')
    {
        global $_ARRAYLANG;

        return
            "<select name='attribute_type[$attribute_id]' ".
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
            "<option value='".SHOP_PRODUCT_ATTRIBUTE_TYPE_TEXTAREA_OPTIONAL."'".
                ($displayTypeId == Attribute::TYPE_TEXTAREA_OPTIONAL
                    ? ' selected="selected"' : ''
                ).">".
                $_ARRAYLANG['TXT_SHOP_PRODUCT_ATTRIBUTE_TYPE_TEXTAREA_OPTIONAL']."</option>\n".
            "<option value='".SHOP_PRODUCT_ATTRIBUTE_TYPE_TEXTAREA_MANDATORY."'".
                ($displayTypeId == Attribute::TYPE_TEXTAREA_MANDATORY
                    ? ' selected="selected"' : ''
                ).">".
                $_ARRAYLANG['TXT_SHOP_PRODUCT_ATTRIBUTE_TYPE_TEXTAREA_MANDATORY']."</option>\n".
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
     * with the option values (names) or prices of an Attribute
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
    static function getInputs(
        $attribute_id, $name, $content, $maxlength='', $style=''
    ) {
        $inputBoxes = '';
        $select = true;
        $arrAttributeName = self::getArrayById($attribute_id);
        $type = $arrAttributeName['type'];
        foreach (self::getOptionArrayByAttributeId($attribute_id)
                 as $option_id => $arrOption) {
            $inputBoxes .=
                '<input type="text" name="'.$name.'['.$option_id.']" '.
                'id="'.$name.'-'.$option_id.'" '.
                'value="'.$arrOption[$content].'"'.
                ($maxlength ? ' maxlength="'.$maxlength.'"' : '').
                ' style="display: '.($select ? 'inline' : 'none').';'.
                    ($style ? " $style" : '').
                '" onchange="updateOptionList('.
                    $attribute_id.','.$option_id.')"'.
                // For text and file upload options, disable the value field.
                // This does not apply to the price field, however.
                (   $content == 'value'
                 && $type >= Attribute::TYPE_TEXT_OPTIONAL
                    ? ' disabled="disabled"' : ''
                ).' />';
            $select = false;
        }
        return $inputBoxes;
    }


    /**
     * Returns HTML code for the option menu for an Attribute
     *
     * Used in the Backend for selecting and editing.
     * @global  array       $_ARRAYLANG     Language array
     * @param   integer     $attribute_id   The Attribute ID
     * @param   string      $name           The name and ID attribute for the
     *                                      menu
     * @param   integer     $selected_id    The ID of the selected option
     * @param   string      $onchange       The optional Javascript onchange
     *                                      event
     * @param   string      $style          The optional CSS style for the menu
     * @return  string      $menu           The Option menu HTML code
     * @static
     */
    static function getOptionMenu(
        $attribute_id, $name, $selected_id=0, $onchange='', $style=''
    ) {
        global $_ARRAYLANG;

        $arrOptions = self::getOptionArrayByAttributeId($attribute_id);
        // No options, or an error occurred
        if (!$arrOptions) return '';
        $menu =
            '<select name="'.$name.'['.$attribute_id.'][]" '.
            'id="'.$name.'-'.$attribute_id.'" size="1"'.
            ($onchange ? ' onchange="'.$onchange.'"' : '').
            ($style ? ' style="'.$style.'"' : '').'>'."\n";
        foreach ($arrOptions as $option_id => $arrValue) {
            $menu .=
                '<option value="'.$option_id.'"'.
                ($selected_id == $option_id ? ' selected="selected"' : '').'>'.
                $arrValue['value'].' ('.$arrValue['price'].' '.
                Currency::getDefaultCurrencySymbol().')</option>'."\n";
        }
        $menu .=
            '</select><br /><a href="javascript:{}" '.
            'id="optionMenuLink-'.$attribute_id.'" '.
            'style="display: none;" '.
            'onclick="removeSelectedValues('.$attribute_id.')" '.
            'title="'.$_ARRAYLANG['TXT_SHOP_REMOVE_SELECTED_VALUE'].'" '.
// Invalid
//            'alt="'.$_ARRAYLANG['TXT_SHOP_REMOVE_SELECTED_VALUE'].'"'.
            '>'.
            $_ARRAYLANG['TXT_SHOP_REMOVE_SELECTED_VALUE'].'</a>'."\n";
        return $menu;
    }


    /**
     * Returns a string containing an Javascript array variable definition
     * with the first option ID for each Attribute
     *
     * The array has the form
     *  optionId[Attribute ID] = first option ID;
     * Additionally, the variable "index" is set to the highest option ID
     * encountered.  This is incremented for each new option added
     * on the page.
     * @static
     * @access    private
     * @return    string    $jsVars    Javascript variables list
     */
    static function getAttributeJSVars()
    {
        $jsVars = '';
        $highestIndex = 0;
        foreach (Attributes::getOptionArray() as $attribute_id => $arrOption) {
            $first = true;
            foreach (array_keys($arrOption) as $option_id) {
                if ($first)
                    $jsVars .= "optionId[$attribute_id] = $option_id;\n";
                $first = false;
                if ($option_id > $highestIndex) $highestIndex = $option_id;
            }
        }
        $jsVars .= "\nindex = ".$highestIndex.";\n";
        return $jsVars;
    }


    static function errorHandler()
    {
        return Attribute::errorHandler();
    }

}
