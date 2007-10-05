<?php

/**
 * Discount
 *
 * Optional calculation of discounts in the Shop.
 * Note: This is to be customized for individual online shops.
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author      Reto Kohli <reto.kohli@astalavista.ch>
 * @access      public
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_shop
 */

/*

-- Possible alterations to customer table

ALTER TABLE `contrexx_module_shop_customers` ADD `discount` VARCHAR( 10 ) NOT NULL ,
ADD `percent` FLOAT( 3, 2 ) NOT NULL DEFAULT '0',
ADD `preorder` VARCHAR( 10 ) NOT NULL ,
ADD `payment` VARCHAR( 10 ) NOT NULL ,
ADD `picturename` VARCHAR( 255 ) NOT NULL ,
ADD `homepage` VARCHAR( 255 ) NOT NULL ;


-- Possible alterations to products table

ALTER TABLE `contrexx_module_shop_products` ADD `discount` VARCHAR( 10 ) NOT NULL ;


-- Possible alterations to order table

ALTER TABLE `contrexx_module_shop_orders`
CHANGE `order_status` `order_status` TINYINT(2) UNSIGNED NOT NULL DEFAULT '0';


-- Possible new discount table

CREATE TABLE `contrexx_module_shop_discounts` (
`code` VARCHAR( 10 ) NOT NULL ,
`minimum` FLOAT( 5, 2 ) NOT NULL ,
`percent` FLOAT( 5, 2 ) NOT NULL ,
`fee` FLOAT( 5, 2 ) NOT NULL ,
`customer_id` INT NOT NULL ,
INDEX ( `customer_id` )
) ENGINE = MYISAM ;


*/


/**
 * Discount
 *
 * Processes many kinds of discounts - as long as you can express the
 * rules in the terms used here.
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author      Reto Kohli <reto.kohli@astalavista.ch>
 * @access      public
 * @version     1.0.1
 * @package     contrexx
 * @subpackage  module_shop
 */
class Discount
{
    /**
     * @var integer     The current Customer ID
     */
    var $customerId;

    /**
     * The amount of previous orders made by that customer
     * @var double
     */
    var $totalOrderAmount;

    /**
     * The amount of orders made by that customer, including the current one
     * @var double
     */
    var $newTotalOrderAmount;

    /**
     * The discount amount according to the previous orders
     * @var double
     */
    var $discountAmount;

    /**
     * The discount amount according to the orders, including the current one
     * @var double
     */
    var $newDiscountAmount;


    /**
     * Return the current total order amount of the Customer
     * @return double       The total order amount
     */
    function getTotalOrderAmount()
    {
        return $this->totalOrderAmount;
    }

    /**
     * Return the new total order amount of the Customer
     * @return double       The new total order amount
     */
    function getNewTotalOrderAmount()
    {
        return $this->newTotalOrderAmount;
    }

    /**
     * Return the current total discount amount of the Customer
     * @return double       The total discount amount
     */
    function getDiscountAmount()
    {
        return $this->discountAmount;
    }

    /**
     * Return the new total discount amount of the Customer
     * @return double       The new total discount amount
     */
    function getNewDiscountAmount()
    {
        return $this->newDiscountAmount;
    }


    /**
     * Constructor (PHP4)
     * @param   integer     $customerId     The current Customer ID
     * @return  Discount
     */
    function Discount($customerId) {
        $this->__construct($customerId);
    }

    /**
     * Constructor (PHP5)
     * @param   integer     $customerId     The current Customer ID
     * @return  Discount
     */
    function __construct($customerId, $orderAmount)
    {
        $this->customerId = $customerId;
        $this->totalOrderAmount = $this->_totalOrderAmount($customerId);
        $this->newTotalOrderAmount = $this->totalOrderAmount+$orderAmount;
        $this->discountAmount = number_format(
            $this->totalOrderAmount * 3 / 100,
            2, '.', ''
        );
        $this->newDiscountAmount = number_format(
            $this->newTotalOrderAmount * 3 / 100,
            2, '.', ''
        );
    }


    /**
     * Returns the total amount of the previous orders the Customer made.
     *
     * Note that only orders marked as COMPLETED are considered here.
     * That means that an order *MUST* have been shipped and paid for
     * before the vouchers are issued to the Customer.
     * Once the voucher has been handed out, the orders considered
     * have to be either marked DELETED or removed from the database.
     * Also note that this method does *NOT* consider Product Attributes'
     * prices.  All the Products in the current Shop use only free options.
     * @return  float                       The order amount on success,
     *                                      0 (zero) upon failure
     *                                      or if no order could be found.
     * @todo    Once the Order class exists, this method *SHOULD* be moved
     *          there.
     */
    function _totalOrderAmount()
    {
        global $objDatabase;

        if (empty($this->customerId)) {
            return false;
        }
        $query = "
             SELECT SUM(price * quantity) AS orderprice
               FROM ".DBPREFIX."module_shop_orders AS o
         INNER JOIN ".DBPREFIX."module_shop_order_items AS oi USING (orderid)
              WHERE customerid=$this->customerId
                AND order_status=".SHOP_ORDER_STATUS_COMPLETED."
                 OR order_status=".SHOP_ORDER_STATUS_PAID
        ;
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return false;
        }
        $orderTotal = 0;
        // Do not, repeat, *DO NOT* use while() here.  It won't terminate.
        if (!$objResult->EOF) {
            $orderTotal = floatval($objResult->fields['orderprice']);
        }
        return $orderTotal;
    }


}

?>
