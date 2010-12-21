<?php

/**
 * Discount
 *
 * Optional calculation of discounts in the Shop.
 * Note: This is to be customized for individual online shops.
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.ch>
 * @access      public
 * @version     2.1.0
 * @package     contrexx
 * @subpackage  module_shop
 */

/**
 * Product class with database layer.
 */
require_once ASCMS_MODULE_PATH.'/shop/lib/Product.class.php';

/**
 * Discount
 *
 * Processes many kinds of discounts - as long as you can express the
 * rules in the terms used here.
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.ch>
 * @access      public
 * @version     2.1.0
 * @package     contrexx
 * @subpackage  module_shop
 */
class Discount
{
    /**
     * Constructor
     * @return  Discount
     */
    function __construct()
    {
    }


    /**
     * Returns the HTML dropdown menu options with all of the
     * count type discount names plus a neutral option ("none")
     *
     * Backend use only.
     * @param   integer   $selectedId   The optional preselected ID
     * @return  string                  The HTML dropdown menu options
     *                                  on success, false otherwise
     * @static
     * @author  Reto Kohli <reto.kohli@astalavista.ch>
     */
    static function getMenuOptionsGroupCount($selectedId=0)
    {
        global $_ARRAYLANG;

        $arrGroup = Discount::getDiscountCountArray();
        if ($arrGroup === false) return false;
        $strMenuOptions =
            '<option value="0">'.
            $_ARRAYLANG['TXT_SHOP_DISCOUNT_GROUP_NONE'].
            '</option>';
        foreach ($arrGroup as $id => $arrDiscount) {
            $name = $arrDiscount['name'];
            $unit = $arrDiscount['unit'];
            $strMenuOptions .=
                '<option value="'.$id.'"'.
                ($selectedId == $id ? ' selected="selected"' : '').
                '>'.$name.' ('.$unit.')</option>';
        }
        return $strMenuOptions;
    }


    /**
     * Returns an array with all the count type discount names
     * indexed by their ID.
     *
     * Backend use only.
     * @return  array         The discount name array on success,
     *                        false otherwise
     * @static
     * @author  Reto Kohli <reto.kohli@astalavista.ch>
     */
    static function getDiscountCountArray()
    {
        global $objDatabase;

        $query = "
            SELECT *
              FROM `".DBPREFIX."module_shop_discountgroup_count_name`
             ORDER BY id ASC
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        $arrDiscount = array();
        while (!$objResult->EOF) {
            $id = $objResult->fields['id'];
            $arrDiscount[$id] = array(
                'name' => $objResult->fields['name'],
                'unit' => $objResult->fields['unit']
            );
            $objResult->MoveNext();
        }
        return $arrDiscount;
    }


    /**
     * Returns an array with all counts and rates for the count type
     * discount selected by its ID.
     *
     * Backend use only.
     * @param   integer   $id     The count type discount ID
     * @return  array             The array with counts and rates on success,
     *                            false otherwise
     * @static
     * @author  Reto Kohli <reto.kohli@astalavista.ch>
     */
    static function getDiscountCountRateArray($id)
    {
        global $objDatabase;

        if (empty($id)) return '';
        $query = "
            SELECT count, rate
              FROM `".DBPREFIX."module_shop_discountgroup_count_rate`
             WHERE group_id=".intval($id);
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        // Default to no discount
        $arrDiscount = array(0 => 0);
        while (!$objResult->EOF) {
            $count = $objResult->fields['count'];
            $rate = $objResult->fields['rate'];
            $arrDiscount[$count] = $rate;
            $objResult->MoveNext();
        }
        return $arrDiscount;
    }


    /**
     * Determine the product discount rate for the discount group with
     * the given ID and the given count.
     *
     * Frontend use only.
     * @param   integer   $id           The discount group ID
     * @param   integer   $count        The number of Products
     * @return  double                  The discount rate in percent
     *                                  to be applied, if any,
     *                                  0 (zero) otherwise
     * @static
     * @author  Reto Kohli <reto.kohli@astalavista.ch>
     */
    static function getDiscountRateCount($id, $count=1)
    {
        global $objDatabase;

        if (empty($id)) return '';
        $query = "
            SELECT `count`, `rate`
              FROM `".DBPREFIX."module_shop_discountgroup_count_rate`
             WHERE `group_id`=$id
               AND `count`<=$count
             ORDER BY `count` DESC
        ";
        $objResult = $objDatabase->SelectLimit($query, 1);
        if (!$objResult || $objResult->EOF) return 0;
        return $objResult->fields['rate'];
    }


    /**
     * Returns the unit used for the count type discount group
     * with the given ID
     * @param   integer   $id       The count type discount group ID
     * @return  string              The unit used for this group on success,
     *                              the empty string otherwise
     */
    static function getUnit($id)
    {
        global $objDatabase;

        if (empty($id)) return '';
        $query = "
            SELECT unit
              FROM `".DBPREFIX."module_shop_discountgroup_count_name`
             WHERE id=$id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult || $objResult->EOF) return '';
        return $objResult->fields['unit'];
    }


    /**
     * Store the count type discount settings
     *
     * Backend use only.
     * @param   integer   $id         The ID of the discount group,
     *                                if known, or 0 (zero)
     * @param   string    $groupName  The group name
     * @param   string    $groupUnit  The group unit
     * @param   array     $arrCount   The array of minimum counts
     * @param   array     $arrRate    The array of discount rates,
     *                                in percent, corresponding to
     *                                the elements of the count array
     * @return  boolean               True on success, false otherwise
     * @static
     * @author  Reto Kohli <reto.kohli@astalavista.ch>
     */
    static function storeDiscountCount(
        $id, $groupName, $groupUnit, $arrCount, $arrRate
    ) {
        global $objDatabase;

        if (empty($id)) $id = 0;
        $query = "
            SELECT 1
              FROM `".DBPREFIX."module_shop_discountgroup_count_name`
             WHERE id=$id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        if ($objResult->RecordCount() == 1) {
            // Exists, update
            $query = "
                UPDATE `".DBPREFIX."module_shop_discountgroup_count_name`
                   SET name='$groupName',
                       unit='$groupUnit'
                 WHERE id=$id
            ";
        } else {
            // Insert
            $query = "
                INSERT INTO `".DBPREFIX."module_shop_discountgroup_count_name` (
                    name, unit
                ) VALUES (
                    '".addslashes($groupName)."',
                    '".addslashes($groupUnit)."'
                )
            ";
        }
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        if (empty($id)) {
            $id = $objDatabase->Insert_Id();
        }

        // Remove old counts and rates
        $query = "
            DELETE FROM `".DBPREFIX."module_shop_discountgroup_count_rate`
             WHERE group_id=$id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;

        // Insert new counts and rates
        foreach ($arrCount as $index => $count) {
            $rate = $arrRate[$index];
            if ($count <= 0 || $rate <= 0) continue;
            $query = "
                INSERT INTO `".DBPREFIX."module_shop_discountgroup_count_rate` (
                    group_id, count, rate
                ) VALUES (
                    $id, $count, $rate
                )
            ";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return false;
        }
        return true;
    }


    /**
     * Delete the count type discount group seleted by its ID from the database
     *
     * Backend use only.
     * @param   integer   $id     The discount group ID
     * @return  boolean           True on success, false otherwise
     * @static
     * @author  Reto Kohli <reto.kohli@astalavista.ch>
     */
    static function deleteDiscountCount($id)
    {
        global $objDatabase;

        if (empty($id)) return false;
        // Remove counts and rates
        $query = "
            DELETE FROM `".DBPREFIX."module_shop_discountgroup_count_rate`
             WHERE group_id=$id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;

        // Remove the group itself
        $query = "
            DELETE FROM `".DBPREFIX."module_shop_discountgroup_count_name`
             WHERE id=$id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        return true;
    }


    /**
     * Returns the HTML dropdown menu options with all of the
     * customer group names
     *
     * Backend use only.
     * @param   integer   $selectedId   The optional preselected ID
     * @return  string                  The HTML dropdown menu options
     *                                  on success, false otherwise
     * @static
     * @author  Reto Kohli <reto.kohli@astalavista.ch>
     */
    static function getMenuOptionsGroupCustomer($selectedId=0)
    {
        global $_ARRAYLANG;

        $arrGroup = Discount::getCustomerGroupArray();
        if ($arrGroup === false) return false;
        $strMenuOptions =
            '<option value="0">'.
            $_ARRAYLANG['TXT_SHOP_DISCOUNT_GROUP_NONE'].
            '</option>';
        foreach ($arrGroup as $id => $name) {
            $strMenuOptions .=
                '<option value="'.$id.'"'.
                ($selectedId == $id ? ' selected="selected"' : '').
                '>'.$name.'</option>';
        }
        return $strMenuOptions;
    }


    /**
     * Returns the HTML dropdown menu options with all of the
     * article group names
     *
     * Backend use only.
     * @param   integer   $selectedId   The optional preselected ID
     * @return  string                  The HTML dropdown menu options
     *                                  on success, false otherwise
     * @static
     * @author  Reto Kohli <reto.kohli@astalavista.ch>
     */
    static function getMenuOptionsGroupArticle($selectedId=0)
    {
        global $_ARRAYLANG;

        $arrGroup = Discount::getArticleGroupArray();
        if ($arrGroup === false) return false;
        $strMenuOptions =
            '<option value="0">'.
            $_ARRAYLANG['TXT_SHOP_DISCOUNT_GROUP_NONE'].
            '</option>';
        foreach ($arrGroup as $id => $name) {
            $strMenuOptions .=
                '<option value="'.$id.'"'.
                ($selectedId == $id ? ' selected="selected"' : '').
                '>'.$name.'</option>';
        }
        return $strMenuOptions;
    }


    /**
     * Returns an array with all the customer group names
     * indexed by their ID
     *
     * Backend use only.
     * @return  array         The group name array on success,
     *                        false otherwise
     * @static
     * @author  Reto Kohli <reto.kohli@astalavista.ch>
     */
    static function getCustomerGroupArray()
    {
        global $objDatabase;

        $query = "
            SELECT *
              FROM `".DBPREFIX."module_shop_customer_group`
             ORDER BY id ASC
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        $arrGroup = array();
        while (!$objResult->EOF) {
            $arrGroup[$objResult->fields['id']] = $objResult->fields['name'];
            $objResult->MoveNext();
        }
        return $arrGroup;
    }


    /**
     * Returns an array with all the article group names
     * indexed by their ID
     *
     * Backend use only.
     * @return  array         The group name array on success,
     *                        false otherwise
     * @static
     * @author  Reto Kohli <reto.kohli@astalavista.ch>
     */
    static function getArticleGroupArray()
    {
        global $objDatabase;

        $query = "
            SELECT *
              FROM `".DBPREFIX."module_shop_article_group`
             ORDER BY id ASC
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        $arrGroup = array();
        while (!$objResult->EOF) {
            $arrGroup[$objResult->fields['id']] = $objResult->fields['name'];
            $objResult->MoveNext();
        }
        return $arrGroup;
    }


    /**
     * Returns an array with all the customer/article type discount rates.
     *
     * The array has the structure
     *  array(
     *    customerGroupId => array(
     *      articleGroupId => discountRate,
     *      ...
     *    ),
     *    ...
     *  );
     * @return  array             The discount rate array
     * @static
     */
    static function getDiscountRateCustomerArray()
    {
        global $objDatabase;

        $query = "
            SELECT *
              FROM `".DBPREFIX."module_shop_rel_discount_group`
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        $arrRate = array();
        while (!$objResult->EOF) {
            $groupCustomerId = $objResult->fields['customer_group_id'];
            $groupArticleId = $objResult->fields['article_group_id'];
            $rate = $objResult->fields['rate'];
            $arrRate[$groupCustomerId][$groupArticleId] = $rate;
            $objResult->MoveNext();
        }
        return $arrRate;
    }


    /**
     * Returns the customer/article type discount rate to be applied
     * for the given group IDs
     *
     * Frontend use only.
     * @param   integer   $groupCustomerId    The customer group ID
     * @param   integer   $groupArticleId     The article group ID
     * @return  double                        The discount rate, if applicable,
     *                                        0 (zero) otherwise
     * @static
     * @author  Reto Kohli <reto.kohli@astalavista.ch>
     */
    static function getDiscountRateCustomer($groupCustomerId, $groupArticleId)
    {
        global $objDatabase;

        if (empty($groupCustomerId) || empty($groupArticleId)) return 0;
        $query = "
            SELECT `rate`
              FROM `".DBPREFIX."module_shop_rel_discount_group`
             WHERE `customer_group_id`=$groupCustomerId
               AND `article_group_id`=$groupArticleId
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult || $objResult->EOF) return 0;
        return $objResult->fields['rate'];
    }


    /**
     * Returns a string with the customer group name
     * for the given ID
     *
     * Backend use only.
     * @return  string        The group name on success,
     *                        false otherwise
     * @static
     * @author  Reto Kohli <reto.kohli@astalavista.ch>
     */
    static function getCustomerGroupName($id)
    {
        global $objDatabase, $_ARRAYLANG;

        if (empty($id) || !is_numeric($id))
            return $_ARRAYLANG['TXT_SHOP_DISCOUNT_GROUP_NONE'];
        $query = "
            SELECT `name`
              FROM `".DBPREFIX."module_shop_customer_group`
             WHERE `id`=$id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        if (!$objResult->EOF) return $objResult->fields['name'];
        return '';
    }


    /**
     * Store the customer/article group discounts in the database.
     *
     * Backend use only.
     * The array argument has the structure
     *  array(
     *    customerGroupId => array(
     *      articleGroupId => discountRate,
     *      ...
     *    ),
     *    ...
     *  );
     * @param   array     $arrDiscountRate  The array of discount rates
     * @return  boolean                     True on success, false otherwise
     * @static
     * @author  Reto Kohli <reto.kohli@astalavista.ch>
     */
    static function storeDiscountCustomer($arrDiscountRate)
    {
        global $objDatabase;

        $query = "
            TRUNCATE TABLE `".DBPREFIX."module_shop_rel_discount_group`
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        foreach ($arrDiscountRate as $groupCustomerId => $arrArticleRow) {
            foreach ($arrArticleRow as $groupArticleId => $rate) {
                // No need to insert "no discount" records.
                if ($rate == 0) continue;
                // Insert
                $query = "
                    INSERT INTO `".DBPREFIX."module_shop_rel_discount_group` (
                        `customer_group_id`, `article_group_id`, `rate`
                    ) VALUES (
                        $groupCustomerId, $groupArticleId, $rate
                    )
                ";
                $objResult = $objDatabase->Execute($query);
                if (!$objResult) return false;
            }
        }
        return true;
    }


    /**
     * Store a customer group in the database
     * @param   string    $groupName    The group name
     * @param   integer   $id           The optional group ID
     * @return  integer                 The (new) group ID on success,
     *                                  0 (zero) otherwise
     * @static
     * @author  Reto Kohli <reto.kohli@astalavista.ch>
     */
    static function storeCustomerGroup($groupName, $id=0)
    {
        global $objDatabase;

        // Default to inserting the group
        $query = "
            INSERT INTO `".DBPREFIX."module_shop_customer_group` (
                name
            ) VALUES (
                '".addslashes($groupName)."'
            )
        ";
        // Maybe the record exists if the ID is not zero
        if ($id > 0) {
            $query_exists = "
                SELECT 1
                  FROM `".DBPREFIX."module_shop_customer_group`
                 WHERE id=$id
            ";
            $objResult = $objDatabase->Execute($query_exists);
            if (!$objResult) return false;
            if ($objResult->RecordCount() == 1) {
                // Exists, update
                $query = "
                    UPDATE `".DBPREFIX."module_shop_customer_group`
                       SET name='$groupName'
                     WHERE id=$id
                ";
            }
        }
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        return true;
    }


    /**
     * Store an article group in the database
     * @param   string    $groupName    The group name
     * @param   integer   $id           The optional group ID
     * @return  integer                 The (new) group ID on success,
     *                                  0 (zero) otherwise
     * @static
     * @author  Reto Kohli <reto.kohli@astalavista.ch>
     */
    static function storeArticleGroup($groupName, $id=0)
    {
        global $objDatabase;

        // Default to inserting the group
        $query = "
            INSERT INTO `".DBPREFIX."module_shop_article_group` (
                name
            ) VALUES (
                '".addslashes($groupName)."'
            )
        ";
        // Maybe the record exists if the ID is not zero
        if ($id > 0) {
            $query_exists = "
                SELECT 1
                  FROM `".DBPREFIX."module_shop_article_group`
                 WHERE id=$id
            ";
            $objResult = $objDatabase->Execute($query_exists);
            if (!$objResult) return false;
            if ($objResult->RecordCount() == 1) {
                // Exists, update
                $query = "
                    UPDATE `".DBPREFIX."module_shop_article_group`
                       SET name='$groupName'
                     WHERE id=$id
                ";
            }
        }
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        return true;
    }


    /**
     * Delete the customer group from the database
     *
     * Backend use only.
     * @param   integer   $id           The group ID
     * @return  boolean                 True on success, false otherwise
     * @static
     * @author  Reto Kohli <reto.kohli@astalavista.ch>
     */
    static function deleteCustomerGroup($id)
    {
        global $objDatabase;

        if (empty($id)) return false;
        // Remove the group itself
        $query = "
            DELETE FROM `".DBPREFIX."module_shop_customer_group`
             WHERE id=$id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        // Remove related rates
        $query = "
            DELETE FROM `".DBPREFIX."module_shop_rel_discount_group`
             WHERE customer_group_id=$id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        return true;
    }


    /**
     * Delete the article group from the database
     *
     * Backend use only.
     * @param   integer   $id           The group ID
     * @return  boolean                 True on success, false otherwise
     * @static
     * @author  Reto Kohli <reto.kohli@astalavista.ch>
     */
    static function deleteArticleGroup($id)
    {
        global $objDatabase;

        if (empty($id)) return false;
        // Remove the group itself
        $query = "
            DELETE FROM `".DBPREFIX."module_shop_article_group`
             WHERE id=$id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        // Remove related rates
        $query = "
            DELETE FROM `".DBPREFIX."module_shop_rel_discount_group`
             WHERE article_group_id=$id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        return true;
    }


}

?>
