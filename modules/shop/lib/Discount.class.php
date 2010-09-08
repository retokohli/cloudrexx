<?php

/**
 * Discount
 *
 * Optional calculation of discounts in the Shop.
 * Note: This is to be customized for individual online shops.
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.ch>
 * @access      public
 * @version     2.2.0
 * @package     contrexx
 * @subpackage  module_shop
 * @internal    Added Coupon class for 2.2.0
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
             ORDER BY `count` DESC";
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
             WHERE id=$id";
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
             WHERE id=$id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        if ($objResult->RecordCount() == 1) {
            // Exists, update
            $query = "
                UPDATE `".DBPREFIX."module_shop_discountgroup_count_name`
                   SET name='$groupName',
                       unit='$groupUnit'
                 WHERE id=$id";
        } else {
            // Insert
            $query = "
                INSERT INTO `".DBPREFIX."module_shop_discountgroup_count_name` (
                    name, unit
                ) VALUES (
                    '".addslashes($groupName)."',
                    '".addslashes($groupUnit)."'
                )";
        }
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        if (empty($id)) {
            $id = $objDatabase->Insert_Id();
        }

        // Remove old counts and rates
        $query = "
            DELETE FROM `".DBPREFIX."module_shop_discountgroup_count_rate`
             WHERE group_id=$id";
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
                )";
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
             WHERE group_id=$id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;

        // Remove the group itself
        $query = "
            DELETE FROM `".DBPREFIX."module_shop_discountgroup_count_name`
             WHERE id=$id";
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
        $arrGroup = array(
            0 =>
                '<option value="0">'.
                $_ARRAYLANG['TXT_SHOP_DISCOUNT_GROUP_NONE'].
                '</option>',
        ) + $arrGroup;
        return Html::getOptions($arrGroup, $selectedId);
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
        $arrGroup = array(
            0 =>
                '<option value="0">'.
                $_ARRAYLANG['TXT_SHOP_DISCOUNT_GROUP_NONE'].
                '</option>',
        ) + $arrGroup;
        return Html::getOptions($arrGroup, $selectedId);
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
             ORDER BY id ASC";
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
             ORDER BY id ASC";
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
              FROM `".DBPREFIX."module_shop_rel_discount_group`";
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
               AND `article_group_id`=$groupArticleId";
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
             WHERE `id`=$id";
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
            TRUNCATE TABLE `".DBPREFIX."module_shop_rel_discount_group`";
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
                    )";
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
            )";
        // Maybe the record exists if the ID is not zero
        if ($id > 0) {
            $query_exists = "
                SELECT 1
                  FROM `".DBPREFIX."module_shop_customer_group`
                 WHERE id=$id";
            $objResult = $objDatabase->Execute($query_exists);
            if (!$objResult) return false;
            if ($objResult->RecordCount()) {
                // Exists, update
                $query = "
                    UPDATE `".DBPREFIX."module_shop_customer_group`
                       SET name='$groupName'
                     WHERE id=$id";
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
            )";
        // Maybe the record exists if the ID is not zero
        if ($id > 0) {
            $query_exists = "
                SELECT 1
                  FROM `".DBPREFIX."module_shop_article_group`
                 WHERE id=$id";
            $objResult = $objDatabase->Execute($query_exists);
            if (!$objResult) return false;
            if ($objResult->RecordCount() == 1) {
                // Exists, update
                $query = "
                    UPDATE `".DBPREFIX."module_shop_article_group`
                       SET name='$groupName'
                     WHERE id=$id";
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
             WHERE id=$id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        // Remove related rates
        $query = "
            DELETE FROM `".DBPREFIX."module_shop_rel_discount_group`
             WHERE customer_group_id=$id";
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
             WHERE id=$id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        // Remove related rates
        $query = "
            DELETE FROM `".DBPREFIX."module_shop_rel_discount_group`
             WHERE article_group_id=$id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        return true;
    }

}


/**
 * Coupon
 *
 * Manages and processes coupon codes for various kinds of discounts
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.ch>
 * @access      public
 * @version     3.0.0
 * @since       3.0.0
 * @package     contrexx
 * @subpackage  module_shop
 */
class Coupon
{
    /**
     * Name used for marking a Product Attribute as a Coupon
     */
    const COUPON_ATTRIBUTE_NAME = 'coupon_code';

    /**
     * Error messages
     * @var   array
     */
    private static $errors = array();

    /**
     * Success and info messages
     * @var   array
     */
    private static $messages = array();


    /**
     * The Coupon code
     * @var   string
     */
    private $code             = '';
    /**
     * Get or set the Coupon code
     * @param   string    $code       The optional new Coupon code
     * @return  string                The Coupon code
     */
    function code($code=null)
    {
        if (isset($code)) $this->code = $code;
        return $this->code;
    }

    /**
     * The minimum amount for which this Coupon is applicable
     *
     * Includes the Product prices only, possibly already discounted
     * @var   double
     */
    private $minimum_amount   = 0;
    /**
     * Get or set the minimum amount
     * @param   double    $minimum_amount The optional minimum amount
     * @return  double                    The minimum amount
     */
    function minimum_amount($minimum_amount=null)
    {
        if (isset($minimum_amount)) $this->minimum_amount = $minimum_amount;
        return $this->minimum_amount;
    }

    /**
     * The discount rate
     *
     * If this is non-empty, the discount amount *MUST* be zero.
     * @var   double
     */
    private $discount_rate    = 0;
    /**
     * Get or set the discount rate
     * @param   double    $discount_rate  The optional discount rate
     * @return  double                    The discount rate
     */
    function discount_rate($discount_rate=null)
    {
        if (isset($discount_rate)) $this->discount_rate = $discount_rate;
        return $this->discount_rate;
    }

    /**
     * The discount amount
     *
     * If this is non-empty, the discount rate *MUST* be zero.
     * @var   double
     */
    private $discount_amount  = 0;
    /**
     * Get or set the discount amount
     * @param   double    $discount_amount  The optional discount amount
     * @return  double                      The discount amount
     */
    function discount_amount($discount_amount=null)
    {
        if (isset($discount_amount)) $this->discount_amount = $discount_amount;
        return $this->discount_amount;
    }

    /**
     * The validity period start time in time() format
     * @var   integer
     */
    private $start_time       = 0;
    /**
     * Get or set the start time
     * @param   integer   $start_time   The optional start time
     * @return  integer                 The start time
     */
    function start_time($start_time=null)
    {
        if (isset($start_time)) $this->start_time = $start_time;
        return $this->start_time;
    }

    /**
     * The validity period end time in time() format
     * @var   integer
     */
    private $end_time         = 0;
    /**
     * Get or set the end time
     * @param   integer   $end_time     The optional end time
     * @return  integer                 The end time
     */
    function end_time($end_time=null)
    {
        if (isset($end_time)) $this->end_time = $end_time;
        return $this->end_time;
    }

    /**
     * The number of uses available
     *
     * This is always initialized to the correct value for any customer,
     * if applicable.
     * Notes:
     *  - For general per-customer Coupons, this value never
     *    changes.  You have to subtract the number of times
     *    it has been used by each Customer.
     *  - For personal Customer Coupons and global Coupons,
     *    it is decremented on each use.
     * @var   integer
     */
    private $uses_available   = 1;
    /**
     * Get or set the uses available
     * @param   integer   $uses_available   The optional uses available
     * @return  integer                     The uses available
     */
    function uses_available($uses_available=null)
    {
        if (isset($uses_available)) $this->uses_available = $uses_available;
        return $this->uses_available;
    }

    /**
     * If true, the Coupon is globally valid for any registered or
     * non-registered Customer
     * @var   boolean
     */
    private $global           = true;
    /**
     * Get or set the global flag
     * @param   boolean   $global           The optional global flag
     * @return  boolean                     The global flag
     */
    function is_global($global=null)
    {
        if (isset($global)) $this->global = $global;
        return $this->global;
    }

    /**
     * The Customer ID for which the Coupon is valid
     *
     * If empty, it is valid for all Customers
     * @var   integer
     */
    private $customer_id      = 0;
    /**
     * Get or set the Customer ID
     * @param   integer   $customer_id      The optional Customer ID
     * @return  integer                     The Customer ID
     */
    function customer_id($customer_id=null)
    {
        if (isset($customer_id)) $this->customer_id = $customer_id;
        return $this->customer_id;
    }

    /**
     * The Product ID to which this Coupon applies
     *
     * If empty, it does not apply to any Product in particular, but
     * to any Product in the order.
     * @var   integer
     */
    private $product_id       = 0;
    /**
     * Get or set the Product ID
     * @param   integer   $product_id       The optional Product ID
     * @return  integer                     The Product ID
     */
    function product_id($product_id=null)
    {
        if (isset($product_id)) $this->product_id = $product_id;
        return $this->product_id;
    }

    /**
     * The Payment ID to which this Coupon applies
     *
     * If non-empty, it only applies when the corresponding Payment is selected
     * @var   integer
     */
    private $payment_id       = 0;
    /**
     * Get or set the Payment ID
     * @param   integer   $payment_id       The optional Payment ID
     * @return  integer                     The Payment ID
     */
    function payment_id($payment_id=null)
    {
        if (isset($payment_id)) $this->payment_id = $payment_id;
        return $this->payment_id;
    }



    /**
     * Returns the error messages
     * @return  array
     */
    static function getErrors()
    {
        return self::$errors;
    }


    /**
     * Returns the success and info messages
     * @return  array
     */
    static function getMessages()
    {
        return self::$messages;
    }


    /**
     * Verifies the coupon code and returns the first matching one
     *
     * If the code is valid, returns the Coupon.
     * If the code is unknown, or limited and already exhausted, returns false.
     * Also note that no counter is changed upon verification; to update
     * a coupon after use see {@see useCoupon()}.
     * @param   string    $code           The coupon code
     * @param   double    $order_amount   The order amount
     * @param   integer   $customer_id    The Customer ID
     * @param   integer   $product_id     The Product ID
     * @param   integer   $payment_id     The Payment ID
     * @return  Coupon                    The matching Coupon on success,
     *                                    false otherwise
     * @static
     */
    static function get(
        $code, $order_amount,
        $customer_id=null, $product_id=null, $payment_id=null
    ) {
        global $objDatabase;

        // See if the code exists and is still valid
        $query = "
            SELECT `code`, `payment_id`, `start_time`, `end_time`,
                   `minimum_amount`, `discount_rate`, `discount_amount`,
                   `uses_available`, `global`, `customer_id`, `product_id`
              FROM `".DBPREFIX."module_shop_discount_coupon`
             WHERE `code`='".addslashes($code)."'
               AND `minimum_amount`<=$order_amount
               AND `start_time`<=".time()."
               AND (`end_time`>".time()." OR `end_time`=0)
               AND `uses_available`>0
               AND (`customer_id`=0".
             ($customer_id ? ' OR `customer_id`='.$customer_id : '').")
               AND `product_id`=".($product_id ? $product_id : '0')."
               AND (`payment_id`=0".
             ($payment_id ? ' OR `payment_id`='.$payment_id : '').")";
        $objResult = $objDatabase->Execute($query);
        // Failure or none found
        if (!$objResult || $objResult->EOF) {
//DBG::log("Coupon::get($code, $order_amount, $customer_id, $product_id, $payment_id): None found");
            return false;
        }

        $objCoupon = new Coupon();
        $objCoupon->code($objResult->fields['code']);
        $objCoupon->payment_id($objResult->fields['payment_id']);
        $objCoupon->start_time($objResult->fields['start_time']);
        $objCoupon->end_time($objResult->fields['end_time']);
        $objCoupon->minimum_amount($objResult->fields['minimum_amount']);
        $objCoupon->discount_rate($objResult->fields['discount_rate']);
        $objCoupon->discount_amount($objResult->fields['discount_amount']);
        $objCoupon->uses_available($objResult->fields['uses_available']);
        $objCoupon->is_global($objResult->fields['global']);
        $objCoupon->customer_id($objResult->fields['customer_id']);
        $objCoupon->product_id($objResult->fields['product_id']);
//DBG::log("Coupon::get($code, $order_amount, $customer_id, $product_id, $payment_id): Found ".(var_export($objCoupon, true)));

        if ($objResult->fields['customer_id'])
            // This is an individual customer coupon, and there are still
            // uses available.
            // Note:  Any other non-zero Customer ID won't pass the query above!
            return $objCoupon;
        if ($objResult->fields['global'])
            // This is a global one, and there are still uses available.
            return $objCoupon;
        // There is a general customer limit.
        // Subtract then number of times the Coupon has been used
        // by that Customer
        $objCoupon->uses_available(self::getUseCount($code, $customer_id));
        // See if the Coupon is still available for her.
        if ($objCoupon->uses_available() > 0)
            return $objCoupon;
        return false;
    }


    /**
     * "Uses" the given coupon code and updates the database, if applicable
     *
     * If the code has already expired of is unknown, returns false.
     * @param   string    $code           The coupon code
     * @param   double    $order_amount   The order amount
     * @param   integer   $customer_id    The Customer ID
     * @param   integer   $product_id     The Product ID
     * @param   integer   $payment_id     The Payment ID
     * @return  Coupon                    The Coupon on success, false otherwise
     * @static
     */
    static function useCoupon(
        $code, $order_amount, $customer_id, $product_id=0, $payment_id=0
    ) {
        global $objDatabase;

        $objCoupon = self::get($code, $order_amount,
            $customer_id, $product_id, $payment_id);
        if (!$objCoupon) return false;
        $discount_amount = $objCoupon->discount_amount();
        if ($discount_amount > $order_amount) {
            $discount_amount = $order_amount;
        }
        if ($objCoupon->customer_id()) {
            // The Customer ID is non-empty, so the Coupon is available
            // for that customer only.
            // If the amount used is zero, this won't do anything:
            if (!self::deductAmount($code, $customer_id, $discount_amount))
                return false;
            if (!self::decrementAvailability($code, $customer_id))
                return false;
        } elseif ($objCoupon->is_global()) {
            // The global flag is set:
            // the Coupon is available globally
            if (!self::decrementAvailability($code, 0))
                return false;
        } elseif ($objCoupon->uses_available()) {
            // Available to all Customers
            if (!self::incrementUse($code, $customer_id, $discount_amount))
                return false;
        } else {
            // Something is wrong
//DBG::log("Coupon::useCoupon(): Something went wrong! Coupon:");
//DBG::log(var_export($objCoupon, true));
            return false;
        }
        return $objCoupon;
    }


    /**
     * Increments the usage count for the given coupon code, Payment ID,
     * and Customer ID, if applicable
     *
     * Note that all arguments are mandatory and must be valid.
     * The discount amount is zero for Coupons where the rate is non-empty,
     * and limited to the available discount amount for others.
     * @param   string    $code           The coupon code
     * @param   integer   $customer_id    The Customer ID
     * @param   double    $amount         The discount amount used, or zero
     * @return  boolean                   True on success, false otherwise
     * @static
     * @access  private
     */
    private static function incrementUse($code, $customer_id, $amount)
    {
        global $objDatabase;

        $use_count = self::getUseCount($code, $customer_id);
        if ($use_count === false) return false;
        if ($use_count) {
            // Increment the counter for that Customer
            $query = "
                UPDATE `".DBPREFIX."module_shop_rel_customer_coupon`
                   SET `count`=`count`+1,
                       `amount`=`amount`-$amount
                 WHERE `code`='".addslashes($code)."'
                   AND `customer_id`=$customer_id";
            return (boolean)$objDatabase->Execute($query);
        }
        // First use: Insert a counter for that Customer
        $query = "
            INSERT `".DBPREFIX."module_shop_rel_customer_coupon` (
              `code`, `customer_id`, `count`, `amount`
            ) VALUES (
              '".addslashes($code)."', $customer_id, 1, $amount
            )";
        return (boolean)$objDatabase->Execute($query);
    }


    /**
     * Decrements the availability for the given coupon code, if applicable
     *
     * This is only applicable for global and personal Coupons, *NOT* for
     * general customer Coupons!
     * Returns false on failure, that is, if there's a problem with the update
     * query, or if no matching record was found for updating.
     * @param   string    $code           The coupon code
     * @param   integer   $customer_id    The Customer ID
     * @return  boolean                   True on success, false otherwise
     * @static
     * @access  private
     */
    private static function decrementAvailability($code, $customer_id)
    {
        global $objDatabase;

        $query = "
            UPDATE `".DBPREFIX."module_shop_discount_coupon`
               SET `uses_available`=`uses_available`-1
             WHERE `code`='".addslashes($code)."'
               AND `customer_id`=$customer_id";
            return (boolean)$objDatabase->Execute($query);
    }


    /**
     * Deducts the amount from the given coupon
     *
     * This is only applicable to personal customer coupons.
     * Returns false if the operation is not possible for the given code,
     * or if the amount is larger than the amount available.
     * @param   string    $code           The coupon code
     * @param   integer   $customer_id    The Customer ID
     * @param   double    $amount         The amount to be deducted
     * @return  boolean                   True on success, false otherwise
     * @static
     * @access  private
     */
    private static function deductAmount($code, $customer_id, $amount)
    {
        global $objDatabase;

        if (empty($customer_id)) return false;
        $query = "
            UPDATE `".DBPREFIX."module_shop_discount_coupon`
               SET `discount_amount`=`discount_amount`-$amount
             WHERE `code`='".addslashes($code)."'
               AND `customer_id`=$customer_id
               AND `discount_amount`>=$amount
               AND `global`=0";
            return (boolean)$objDatabase->Execute($query);
    }


    /**
     * Returns the count of the uses for the given code
     *
     * The optional $customer_id limits the result to the uses of that
     * Customer.
     * Returns 0 (zero) for codes not present in the relation (yet).
     * @param   string    $code           The coupon code
     * @param   integer   $customer_id    The Customer ID
     * @return  mixed                     The number of uses of the code
     *                                    on success, false otherwise
     * @static
     */
    static function getUseCount($code, $customer_id=0)
    {
        global $objDatabase;

        $query = "
            SELECT SUM(`count`) AS `numof_uses`
              FROM `".DBPREFIX."module_shop_rel_customer_coupon`
             WHERE `code`='".addslashes($code)."'
               AND `customer_id`=$customer_id";
        $objResult = $objDatabase->Execute($query);
        // Failure or none found
        if (!$objResult) return false;
        if ($objResult->EOF) return 0;
        // The coupon has been used so many times already
        return $objResult->fields['numof_uses'];
    }


    private static function delete($code)
    {
        global $objDatabase, $_ARRAYLANG;

        if (empty($code)) return false;

        $query = "
            DELETE FROM `".DBPREFIX."module_shop_rel_customer_coupon`
             WHERE `code`='".addslashes($code)."'";
        if (!$objDatabase->Execute($query)) {
            self::$errors[] = sprintf(
                $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUPON_ERROR_DELETING_RELATIONS'],
                $code
            );
            return false;
        }
        $query = "
            DELETE FROM `".DBPREFIX."module_shop_discount_coupon`
             WHERE `code`='".addslashes($code)."'";
        if (!$objDatabase->Execute($query)) {
            self::$errors[] = sprintf(
                $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUPON_ERROR_DELETING'],
                $code
            );
            return false;
        }
        self::$messages[] = sprintf(
            $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUPON_DELETED_SUCCESSFULLY'],
            $code
        );
        return true;
    }


    /**
     * Get coupon information from the database
     *
     * The array returned looks like
     *  array(
     *    'code-payment_id' => array(
     *      code            => The Coupon code,
     *      minimum_amount  => The minimum amount for which the coupon is valid,
     *      discount_rate   => The discount rate,
     *      discount_amount => The discount amount,
     *      start_time      => The validity period start time,
     *      end_time        => The validity period end time,
     *      uses_available  => The available number of uses,
     *      global          => Flag for globally available coupons,
     *      customer_id     => The Customer ID,
     *      product_id      => The Product ID,
     *      payment_id      => The Payment ID,
     *    ),
     *    ... more ...
     *  )
     * @param   integer   $offset           The offset.  Defaults to zero
     * @param   integer   $limit            The limit.  Defaults to 30.
     * @param   integer   $count            By reference.  Contains the actual
     *                                      number of total records on
     *                                      successful return
     * @param   string    $order            The sorting order.  Defaults to
     *                                      '`end_time` DESC'
     * @return  array                       The array of coupon data
     * @static
     */
    static function getArray($offset=0, $limit=0, &$count=0, $order='')
    {
        global $objDatabase;

        $offset = max(0, intval($offset));
        $limit = min(0, intval($limit));
        if (empty($limit)) $limit = 30;
        // The count is zero if an error occurs.
        // Ignore the code analyzer warning.
        $count = 0;
        if (empty($order)) $order='`end_time` DESC';


        $query = "
            SELECT `code`, `payment_id`, `start_time`, `end_time`,
                   `minimum_amount`, `discount_rate`, `discount_amount`,
                   `uses_available`, `global`, `customer_id`, `product_id`
              FROM `".DBPREFIX."module_shop_discount_coupon`
             ORDER BY $order";
        $objResult = $objDatabase->SelectLimit($query, $limit, $offset);
        if (!$objResult) return false;
        $arrCoupons = array();
        while (!$objResult->EOF) {
//echo("Fields: ".var_export($objResult->fields, true));
            $objCoupon = new Coupon();
            $code = $objResult->fields['code'];
            $payment_id = $objResult->fields['payment_id'];
            $objCoupon->code($code);
            $objCoupon->payment_id($payment_id);
            $objCoupon->start_time($objResult->fields['start_time']);
            $objCoupon->end_time($objResult->fields['end_time']);
            $objCoupon->minimum_amount($objResult->fields['minimum_amount']);
            $objCoupon->discount_rate($objResult->fields['discount_rate']);
            $objCoupon->discount_amount($objResult->fields['discount_amount']);
            $objCoupon->uses_available($objResult->fields['uses_available']);
            $objCoupon->is_global($objResult->fields['global']);
            $objCoupon->customer_id($objResult->fields['customer_id']);
            $objCoupon->product_id($objResult->fields['product_id']);
            $arrCoupons[$code] = $objCoupon;
            $objResult->MoveNext();
        }
        $query = "
            SELECT COUNT(*) AS `numof_records`
              FROM `".DBPREFIX."module_shop_discount_coupon`";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        $count = $objResult->fields['numof_records'];
        return $arrCoupons;
    }


    /**
     * Add a coupon code to the database
     *
     * Returns true on success.
     * The code must be unique, non-empty, and countain at least six characters.
     * Either $discount_rate or $discount_amount must be non-empty, but not both.
     * Any empty, non-integer, or non-positive values for $start_time,
     * $end_time, and $customer_id are ignored, and the  corresponding field
     * is set to zero.
     * Adding a code with $uses_available zero is pointless, as it can never be used.
     * @param   string    $code             The code, by reference
     * @param   double    $discount_rate             The discount rate in percent
     * @param   double    $discount_amount           The discount amount in default Currency
     * @param   integer   $start_time       The optional start time in time() format
     * @param   integer   $end_time         The optional end time in time() format
     * @param   integer   $uses_available   The available number of uses
     * @param   boolean   $global           If false, the code is valid on a
     *                                      per customer basis.
     *                                      Defaults to true
     * @param   integer   $customer_id      The optional customer ID
     * @return  boolean                     True on success, false otherwise
     * @static
     */
    static function addCode(
        &$code, $payment_id=0, $minimum_amount=0,
        $discount_rate=0, $discount_amount=0,
        $start_time=0, $end_time=0,
        $uses_available=0, $global=true,
        $customer_id=0, $product_id=0
    ) {
        global $objDatabase, $_ARRAYLANG;

// TODO: Three umlauts in UTF-8 encoding might count as six characters here!
// Allow arbitrary Coupon codes, even one with an empty name!
//        if (empty($code) || strlen($code) < 6) {
//            self::$errors[] =
//                $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUPON_ERROR_ADDING_INVALID_CODE'];
//            return false;
//        }
        // These all default to zero if invalid
        $discount_rate = max(0, $discount_rate);
        $discount_amount = max(0, $discount_amount);
        if (empty($discount_rate) && empty($discount_amount)) {
            self::$errors[] =
                $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUPON_ERROR_ADDING_MISSING_RATE_OR_AMOUNT'];
            return false;
        }
        if ($discount_rate && $discount_amount) {
            self::$errors[] =
                $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUPON_ERROR_ADDING_EITHER_RATE_OR_AMOUNT'];
            return false;
        }
        // These must be non-negative integers and default to zero
        $start_time = max(0, intval($start_time));
        $end_time = max(0, intval($end_time));
        if ($end_time && $end_time < time()) {
            self::$errors[] =
                $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUPON_ERROR_ADDING_INVALID_END_TIME'];
            return false;
        }
        $uses_available = max(0, intval($uses_available));
        if (empty($uses_available)) {
            self::$errors[] =
                $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUPON_ERROR_ADDING_INVALID_USES_AVAILABLE'];
            return false;
        }
        $customer_id = max(0, intval($customer_id));
        $query = "
            INSERT INTO `".DBPREFIX."module_shop_discount_coupon` (
              `code`, `payment_id`,
              `minimum_amount`, `discount_rate`, `discount_amount`,
              `start_time`, `end_time`, `uses_available`, `global`,
              `customer_id`, `product_id`
            ) VALUES (
              '".addslashes($code)."', $payment_id,
              $minimum_amount, $discount_rate, $discount_amount,
              $start_time, $end_time, $uses_available, ".intval($global).",
              $customer_id, $product_id
            )";
        if ($objDatabase->Execute($query)) {
            self::$messages[] = sprintf(
                $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUPON_ADDED_SUCCESSFULLY'],
                $code
            );
            return true;
        }
        self::$errors[] = sprintf(
            $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUPON_ERROR_ADDING_QUERY_FAILED'],
            $code
        );
        return false;
    }


    /**
     * Edit coupons
     * @param   HTML_Template_Sigma   $objTemplate    The Template
     */
    static function edit($objTemplate)
    {
        global $_ARRAYLANG, $_CORELANG;

        $result = true;
        if (isset($_GET['delete'])) {
            $result &= self::delete($_GET['delete']);
        }
        $edit = isset($_REQUEST['edit']) ? $_REQUEST['edit'] : null;
//DBG::log("Edit: ".($edit ? $edit : 'NULL'));
        $code = empty($_POST['coupon_code']) ? '' : $_POST['coupon_code'];
        $payment_id = empty($_POST['coupon_payment_id']) ? 0 : $_POST['coupon_payment_id'];
        $start_time = empty($_POST['coupon_start_date']) ? 0 : strtotime($_POST['coupon_start_date']);
        $end_time = empty($_POST['coupon_end_date_unlimited'])
            ? (empty($_POST['coupon_end_date'])
                ? 0 : strtotime($_POST['coupon_end_date']))
            : 0;
        $discount_rate = intval(
            empty($_POST['coupon_discount_rate'])
                ? 0 : $_POST['coupon_discount_rate']);
        $discount_amount = Currency::formatPrice(
            empty($_POST['coupon_discount_amount'])
                ? 0 : $_POST['coupon_discount_amount']);
        $minimum_amount = Currency::formatPrice(
            empty($_POST['coupon_minimum_amount'])
                ? 0 : $_POST['coupon_minimum_amount']);
        $uses_available = empty($_POST['coupon_unlimited'])
            ? (empty($_POST['coupon_uses_available']) ? 1 : $_POST['coupon_uses_available'])
            : 1e10;
        $customer_id = empty($_POST['coupon_customer_id']) ? 0 : $_POST['coupon_customer_id'];
        $product_id = empty($_POST['coupon_product_id']) ? 0 : $_POST['coupon_product_id'];
        $global =
               empty($customer_id)
            && (   empty($_POST['coupon_global_or_customer'])
                || $_POST['coupon_global_or_customer'] == 'global');
//DBG::log("code $code, start_time $start_time, end_time $end_time, discount_rate $discount_rate, discount_amount $discount_amount, uses_available $uses_available, customer_id $customer_id");
        if (isset($_POST['coupon_code'])) {
            $result &= self::addCode(
                $code, $payment_id, $minimum_amount,
                $discount_rate, $discount_amount, $start_time, $end_time,
                $uses_available, $global, $customer_id, $product_id);
            if ($result) {
                $code = '';
            }
        }
        // Reset the end time if it's in the past
        if ($end_time < time()) $end_time = 0;

        // Abbreviations for day of the week
        $arrDow2 = explode(',', $_CORELANG['TXT_CORE_DAY_ABBREV2_ARRAY']);
        // Months of the year
        $arrMoy = explode(',', $_CORELANG['TXT_CORE_MONTH_ARRAY']);
        unset($arrMoy[0]);
        $uri = Html::getRelativeUri_entities();
        Html::replaceUriParameter($uri, 'view');
        Html::replaceUriParameter($uri, 'edit');
        $arrSortingFields = array(
            'code' => $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUPON_CODE'],
            'start_time' => $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUPON_START_TIME'],
            'end_time' => $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUPON_END_TIME'],
            'minimum_amount' => sprintf(
                $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUPON_MINIMUM_AMOUNT_FORMAT'],
                Currency::getDefaultCurrencyCode()),
            'discount_rate' => $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUPON_RATE'],
            'discount_amount' => sprintf(
                $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUPON_AMOUNT_FORMAT'],
                Currency::getDefaultCurrencyCode()),
            'uses_available' => $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUPON_USES_AVAILABLE'],
            'global' => $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUPON_SCOPE'],
            'customer_id' => $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUPON_CUSTOMER'],
            'product_id' => $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUPON_PRODUCT'],
            'payment_id' => $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUPON_PAYMENT'],
        );
        require_once(ASCMS_CORE_PATH.'/Sorting.class.php');
        $objSorting = new Sorting(
            $uri, $arrSortingFields, true, 'order_coupon'
        );

        $objTemplate->setGlobalVariable($_ARRAYLANG + array(
            'TXT_SHOP_DISCOUNT_COUPON_MINIMUM_AMOUNT_CURRENCY' =>
                sprintf(
                    $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUPON_MINIMUM_AMOUNT_FORMAT'],
                    Currency::getDefaultCurrencyCode()),
            'TXT_SHOP_DISCOUNT_COUPON_AMOUNT_CURRENCY' =>
                sprintf(
                    $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUPON_AMOUNT_FORMAT'],
                    Currency::getDefaultCurrencyCode()),
            'TXT_SHOP_DISCOUNT_COUPON_ADD_OR_EDIT' =>
                $_ARRAYLANG[$edit
                    ? 'TXT_SHOP_DISCOUNT_COUPON_EDIT'
                    : 'TXT_SHOP_DISCOUNT_COUPON_ADD'],
            'SHOP_DISCOUNT_COUPON_VIEW_ACTIVE' => $edit ? '' : 'active',
            'SHOP_DISCOUNT_COUPON_EDIT_ACTIVE' => $edit ? 'active' : '',
            'SHOP_DISCOUNT_COUPON_VIEW_DISPLAY' => $edit ? 'none' : 'block',
            'SHOP_DISCOUNT_COUPON_EDIT_DISPLAY' => $edit ? 'block' : 'none',
            // Datepicker language and settings
            'DPC_DEFAULT_FORMAT' => 'DD.MM.YYYY',
            'DPC_TODAY_TEXT'     => $_CORELANG['TXT_CORE_TODAY'],
            'DPC_BUTTON_TITLE'   => $_CORELANG['TXT_CORE_CALENDAR_OPEN'],
            'DPC_MONTH_NAMES'    => "'".join("','", $arrMoy)."'",
            // Format the weekday string as "'Su','Mo','Tu','We','Th','Fr','Sa'"
            'DPC_DAY_NAMES'      => "'".join("','", $arrDow2)."'",
            'HEADER_SHOP_DISCOUNT_COUPON_CODE' =>
                $objSorting->getHeaderForField('code'),
            'HEADER_SHOP_DISCOUNT_COUPON_START_TIME' =>
                $objSorting->getHeaderForField('start_time'),
            'HEADER_SHOP_DISCOUNT_COUPON_END_TIME' =>
                $objSorting->getHeaderForField('end_time'),
            'HEADER_SHOP_DISCOUNT_COUPON_MINIMUM_AMOUNT_CURRENCY' =>
                $objSorting->getHeaderForField('minimum_amount'),
            'HEADER_SHOP_DISCOUNT_COUPON_RATE' =>
                $objSorting->getHeaderForField('discount_rate'),
            'HEADER_SHOP_DISCOUNT_COUPON_AMOUNT_CURRENCY' =>
                $objSorting->getHeaderForField('discount_amount'),
            'HEADER_SHOP_DISCOUNT_COUPON_USES_AVAILABLE' =>
                $objSorting->getHeaderForField('uses_available'),
            'HEADER_SHOP_DISCOUNT_COUPON_SCOPE' =>
                $objSorting->getHeaderForField('global'),
            'HEADER_SHOP_DISCOUNT_COUPON_CUSTOMER' =>
                $objSorting->getHeaderForField('customer_id'),
            'HEADER_SHOP_DISCOUNT_COUPON_PRODUCT' =>
                $objSorting->getHeaderForField('product_id'),
            'HEADER_SHOP_DISCOUNT_COUPON_PAYMENT' =>
                $objSorting->getHeaderForField('payment_id'),

        ));

        $count = 0;
        $arrCoupons = self::getArray(
            Paging::getPosition(), 30, $count,
            $objSorting->getOrder()
        );
        $arrProductName = Products::getNameArray(
            false, $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUPON_PRODUCT_FORMAT']);
        $arrPaymentName = Payment::getNameArray();
        $i = 0;
        $row = 0;
        $objCouponEdit = new Coupon();
        foreach ($arrCoupons as $index => $objCoupon) {
//DBG::log("Coupon: ".var_export($objCoupon, true));
            $objTemplate->setVariable(array(
                'SHOP_ROWCLASS' => 'row'.(++$row % 2 + 1),
                'SHOP_DISCOUNT_COUPON_CODE' => $objCoupon->code(),
                'SHOP_DISCOUNT_COUPON_START_TIME' =>
                    ($objCoupon->start_time()
                      ? date(ASCMS_DATE_SHORT_FORMAT, $objCoupon->start_time())
                      : $_ARRAYLANG['TXT_SHOP_DATE_NONE']),
                'SHOP_DISCOUNT_COUPON_END_TIME' =>
                    ($objCoupon->end_time()
                      ? date(ASCMS_DATE_SHORT_FORMAT, $objCoupon->end_time())
                      : $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUPON_END_TIME_UNLIMITED']),
                'SHOP_DISCOUNT_COUPON_MINIMUM_AMOUNT' =>
                    ($objCoupon->minimum_amount() > 0
                      ? $objCoupon->minimum_amount()
                      : $_ARRAYLANG['TXT_SHOP_AMOUNT_NONE']),
                'SHOP_DISCOUNT_COUPON_RATE' =>
                    ($objCoupon->discount_rate() > 0
                      ? $objCoupon->discount_rate()
                      : $_ARRAYLANG['TXT_SHOP_RATE_NONE']),
                'SHOP_DISCOUNT_COUPON_AMOUNT' =>
                    ($objCoupon->discount_amount() > 0
                      ? $objCoupon->discount_amount()
                      : $_ARRAYLANG['TXT_SHOP_AMOUNT_NONE']),
                'SHOP_DISCOUNT_COUPON_USES_AVAILABLE' =>
                    ($objCoupon->uses_available() < 1e9
                      ? $objCoupon->uses_available()
                      : $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUPON_USES_AVAILABLE_UNLIMITED']),
                'SHOP_DISCOUNT_COUPON_SCOPE' =>
                    ($objCoupon->is_global()
                      ? $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUPON_GLOBALLY']
                      : $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUPON_PER_CUSTOMER']),
                'SHOP_DISCOUNT_COUPON_PER_CUSTOMER' =>
                    (!$objCoupon->is_global()
                      ? Html::getRadio('foo_'.++$i, '', false,
                        true, '', HTML_ATTRIBUTE_DISABLED)
                      : '&nbsp;'),
                'SHOP_DISCOUNT_COUPON_CUSTOMER' =>
                    ($objCoupon->customer_id()
                      ? Customers::getNameById($objCoupon->customer_id())
                      : $_ARRAYLANG['TXT_SHOP_CUSTOMER_ANY']),
                'SHOP_DISCOUNT_COUPON_PRODUCT' =>
                    ($objCoupon->product_id()
                      ? $arrProductName[$objCoupon->product_id()]
                      : $_ARRAYLANG['TXT_SHOP_PRODUCT_ANY']),
                'SHOP_DISCOUNT_COUPON_PAYMENT' =>
                    ($objCoupon->payment_id()
                      ? sprintf(
                          $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUPON_PAYMENT_FORMAT'],
                          $objCoupon->payment_id(),
                          $arrPaymentName[$objCoupon->payment_id()])
                      : $_ARRAYLANG['TXT_SHOP_PAYMENT_ANY']),
                'SHOP_DISCOUNT_COUPON_FUNCTIONS' => Html::getBackendFunctions(
                    array(
// Partially implemented -- Coupon records cannot be updated yet
//                        'edit' =>
//                            ADMIN_SCRIPT_PATH.
//                            '?cmd=shop&amp;act=settings&amp;tpl=coupon&amp;edit='.
//                            urlencode($index),
                        'delete' =>
                            ADMIN_SCRIPT_PATH.
                            '?cmd=shop&amp;act=settings&amp;tpl=coupon&amp;delete='.
                            urlencode($index),
                    ),
                    array(
                        'delete' =>
                            $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUPON_DELETE_CONFIRM'].
                            '\\n\\n'.
                            $_ARRAYLANG['TXT_ACTION_IS_IRREVERSIBLE'],
                    )),
            ));
            $objTemplate->parse('shopDiscountCouponView');
            if ($index == $edit) $objCouponEdit = &$objCoupon;
        }
        $objTemplate->replaceBlock('shopDiscountCouponView', '', true);

        $attribute_code = 'style="width: 230px; text-align: left;" maxlength="30"';
        $attribute_time = 'style="width: 230px; text-align: left;" maxlength="10"';
        $attribute_discount_rate = 'style="width: 230px; text-align: right;" maxlength="3"';
        $attribute_discount_amount = 'style="width: 230px; text-align: right;" maxlength="9"';
        $attribute_minimum_amount = 'style="width: 230px; text-align: right;" maxlength="9"';
        $attribute_uses_available = 'style="width: 230px; text-align: right;" maxlength="6"';
        $attribute_customer = 'style="width: 230px;"';
        $attribute_product = 'style="width: 230px;"';
        $attribute_payment = 'style="width: 230px;"';

        $end_date_id = '';
        $objTemplate->setVariable(array(
            // Add new coupon code
            'SHOP_ROWCLASS' => 'row'.(++$row % 2 + 1),
            'SHOP_DISCOUNT_COUPON_CODE' =>
                Html::getInputText('coupon_code', $objCouponEdit->code(),
                    false, $attribute_code),
            'SHOP_DISCOUNT_COUPON_START_TIME' =>
                Html::getSelectDate('coupon_start_date',
                    ($objCouponEdit->start_time() ? date(ASCMS_DATE_SHORT_FORMAT, $objCouponEdit->start_time()) : ''),
                    $attribute_time),
            'SHOP_DISCOUNT_COUPON_END_TIME' =>
                Html::getSelectDate('coupon_end_date',
                    ($objCouponEdit->end_time() ? date(ASCMS_DATE_SHORT_FORMAT, $objCouponEdit->end_time()) : ''),
                    $attribute_time.($objCouponEdit->end_time() ? '' : HTML_ATTRIBUTE_DISABLED),
                    $end_date_id),
            'SHOP_DISCOUNT_COUPON_END_TIME_UNLIMITED' =>
                Html::getCheckbox('coupon_end_time_unlimited',
                    1, 'coupon_end_time_unlimited',
                    $objCouponEdit->end_time() == 0,
                    'document.getElementById(\''.$end_date_id.
                    '\').disabled = (this.checked); document.getElementById(\''.
                    $end_date_id.'\').value = \'\'').
                Html::getLabel('coupon_end_time_unlimited',
                    $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUPON_END_TIME_UNLIMITED']),
            'SHOP_DISCOUNT_COUPON_MINIMUM_AMOUNT' =>
                Html::getInputText('minimum_amount', $objCouponEdit->minimum_amount(), false,
                    $attribute_minimum_amount),
            'SHOP_DISCOUNT_COUPON_RATE' =>
                Html::getInputText('coupon_discount_rate', $objCouponEdit->discount_rate(), false,
                    $attribute_discount_rate),
            'SHOP_DISCOUNT_COUPON_AMOUNT' =>
                Html::getInputText('coupon_discount_amount',
                    number_format($objCouponEdit->discount_amount(), 2), false,
                    $attribute_discount_amount),
            'SHOP_DISCOUNT_COUPON_USES_AVAILABLE' =>
                Html::getInputText('coupon_uses_available',
                    ($objCouponEdit->uses_available() < 1e9 ? $objCouponEdit->uses_available() : ''), 'coupon_uses_available',
                    $attribute_uses_available.
                    ($objCouponEdit->uses_available() < 1e9 ? '' : HTML_ATTRIBUTE_DISABLED)),
            'SHOP_DISCOUNT_COUPON_USES_AVAILABLE_UNLIMITED' =>
                Html::getCheckbox('coupon_unlimited', 1, 'coupon_unlimited',
                    $objCouponEdit->uses_available() > 1e9,
                    'document.getElementById(\'coupon_uses_available\').disabled = this.checked;'.
                    'document.getElementById(\'coupon_uses_available\').value = \'\';').
                Html::getLabel('coupon_unlimited',
                    $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUPON_USES_AVAILABLE_UNLIMITED']),
            'SHOP_DISCOUNT_COUPON_GLOBALLY' =>
                Html::getRadio('coupon_global_or_customer', 'global',
                    'global', $objCouponEdit->is_global(),
                'document.getElementById(\'coupon_customer_id\').selectedIndex = 0;').
                Html::getLabel('global',
                    $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUPON_GLOBALLY']),
            'SHOP_DISCOUNT_COUPON_PER_CUSTOMER' =>
                Html::getRadio('coupon_global_or_customer', 'customer',
                    'coupon_customer', !$objCouponEdit->is_global()).
                Html::getLabel('coupon_customer',
                    $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUPON_PER_CUSTOMER']),
            'SHOP_DISCOUNT_COUPON_CUSTOMER' =>
                Html::getSelect(
                    'coupon_customer_id',
                      array(0 => $_ARRAYLANG['TXT_SHOP_CUSTOMER_ANY'])
                    + Customers::getNameArray(false), $objCouponEdit->customer_id(),
                    'coupon_customer_id',
                    'if (this.selectedIndex) document.getElementById(\'coupon_customer\').checked = true;',
                    $attribute_customer),
            'SHOP_DISCOUNT_COUPON_PRODUCT' =>
                Html::getSelect(
                    'coupon_product_id',
                      array(0 => $_ARRAYLANG['TXT_SHOP_PRODUCT_ANY'])
                    + $arrProductName, $objCouponEdit->product_id(), false, '',
                    $attribute_product),
            'SHOP_DISCOUNT_COUPON_PAYMENT' =>
                Html::getSelect(
                    'coupon_payment_id',
                      array(0 => $_ARRAYLANG['TXT_SHOP_PAYMENT_ANY'])
                    + $arrPaymentName, $objCouponEdit->payment_id(), false, '',
                    $attribute_payment),
        ));
        $objTemplate->parse('shopDiscountCouponEdit');
        return $result;
    }


    /**
     * Returns a textual representation for the discount provided by this
     * Coupon
     * @return  string
     */
    function getString()
    {
        global $_ARRAYLANG;

        if ($this->discount_rate)
            return sprintf(
                $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUPON_RATE_STRING_FORMAT'],
                $this->discount_rate);
        if ($this->discount_amount)
            return sprintf(
                $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUPON_AMOUNT_STRING_FORMAT'],
                Currency::formatPrice($this->discount_amount),
                Currency::getActiveCurrencyCode());
        // This should never happen
        return '';
    }


    function getDiscountAmount($amount)
    {
        if ($this->discount_rate)
            return Currency::formatPrice($amount * $this->discount_rate / 100);
        if ($this->discount_amount) {
            if ($amount < $this->discount_amount)
                return Currency::formatPrice($amount);
            return Currency::formatPrice($this->discount_amount);
        }
        // This should never happen
        return '';
    }


    static function getTotalAmountString($amount)
    {
        global $_ARRAYLANG;

        return sprintf(
            $_ARRAYLANG['TXT_SHOP_DISCOUNT_COUPON_AMOUNT_TOTAL_STRING_FORMAT'],
            Currency::formatPrice($amount), Currency::getActiveCurrencyCode()
        );
    }


    /**
     * Returns a unique Coupon code with eight characters
     * @return    string            The Coupon code
     * @see       User::makePassword()
     */
    static function getNewCode()
    {
        while (true) {
            $code = User::makePassword(8);
            if (!self::get($code, 1e10)) return $code;
        }
        die("
            This never happens, but the code analyzer complains
            if there's no return, exit() or die() here
        ");
    }

}

?>
