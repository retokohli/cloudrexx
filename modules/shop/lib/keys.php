<?php

/**
 * Table and field indices for calculating the proper key_id value
 * for filtering purposes using the core_text table
 *
 * key_id values are calculated this way:
 *  (<table_index> * 2^16) + (<field_index> * 2^0)
 * Note that each module or module instance accesses its text records
 * using its individual module ID, thus allowing you to have 2^32 modules
 * with 2^16 tables with 2^16 fields each.
 * We will happily provide you with 64 or even 128 bit versions
 * if that ain't enough.  :)
 * @version     2.1.0
 * @since       2.1.0
 * @package     contrexx
 * @subpackage  module_shop
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 */

/**
 * Table indices
 *
 * Only add tables here having fields relating to table core_text IDs.
 * All constants must be uppercase.
 */
define('TABLE_MODULE_SHOP_ARTICLE_GROUP',              1);
define('TABLE_MODULE_SHOP_CATEGORIES',                 2);
define('TABLE_MODULE_SHOP_COUNTRY',                    3);
define('TABLE_MODULE_SHOP_CURRENCIES',                 4);
define('TABLE_MODULE_SHOP_CUSTOMER_GROUP',             5);
define('TABLE_MODULE_SHOP_DISCOUNTGROUP_COUNT_NAME',   6);
define('TABLE_MODULE_SHOP_MAIL',                       7);
define('TABLE_MODULE_SHOP_MANUFACTURER',               9);
define('TABLE_MODULE_SHOP_PAYMENT',                   10);
define('TABLE_MODULE_SHOP_PAYMENT_PROCESSORS',        11);
define('TABLE_MODULE_SHOP_PRODUCTS',                  12);
define('TABLE_MODULE_SHOP_PRODUCTS_ATTRIBUTES_NAME',  13);
define('TABLE_MODULE_SHOP_PRODUCTS_ATTRIBUTES_VALUE', 14);
define('TABLE_MODULE_SHOP_PRODUCTS_DOWNLOADS',        15);
define('TABLE_MODULE_SHOP_SHIPPER',                   16);
define('TABLE_MODULE_SHOP_VAT',                       17);
define('TABLE_MODULE_SHOP_ZONES',                     18);

/**
 * Field indices
 *
 * Only add fields here relating to table core_text IDs.
 * Prepend the table name plus underscore to the field name,
 * and remove redundant parts (repetitions) if possible, as well
 * as "TEXT_" and "_ID" from the field name.
 * The table indices are multiplied by 2^16 and added right away,
 * so that only one constant is needed to access the values.
 * All constants must be uppercase.
 */
define('TEXT_SHOP_ARTICLE_GROUP_NAME',              1 + (1<<16)*TABLE_MODULE_SHOP_ARTICLE_GROUP);
define('TEXT_SHOP_CATEGORIES_NAME',                 1 + (1<<16)*TABLE_MODULE_SHOP_CATEGORIES);
define('TEXT_SHOP_COUNTRY_NAME',                    1 + (1<<16)*TABLE_MODULE_SHOP_COUNTRY);
define('TEXT_SHOP_CURRENCIES_NAME',                 1 + (1<<16)*TABLE_MODULE_SHOP_CURRENCIES);
define('TEXT_SHOP_CUSTOMER_GROUP_NAME',             1 + (1<<16)*TABLE_MODULE_SHOP_CUSTOMER_GROUP);
define('TEXT_SHOP_DISCOUNTGROUP_COUNT_NAME_NAME',   1 + (1<<16)*TABLE_MODULE_SHOP_DISCOUNTGROUP_COUNT_NAME);
define('TEXT_SHOP_MAIL_NAME',                       1 + (1<<16)*TABLE_MODULE_SHOP_MAIL);
define('TEXT_SHOP_MAIL_FROM',                       2 + (1<<16)*TABLE_MODULE_SHOP_MAIL);
define('TEXT_SHOP_MAIL_SENDER',                     3 + (1<<16)*TABLE_MODULE_SHOP_MAIL);
define('TEXT_SHOP_MAIL_SUBJECT',                    4 + (1<<16)*TABLE_MODULE_SHOP_MAIL);
define('TEXT_SHOP_MAIL_MESSAGE',                    5 + (1<<16)*TABLE_MODULE_SHOP_MAIL);
define('TEXT_SHOP_MANUFACTURER_NAME',               1 + (1<<16)*TABLE_MODULE_SHOP_MANUFACTURER);
define('TEXT_SHOP_MANUFACTURER_URL',                2 + (1<<16)*TABLE_MODULE_SHOP_MANUFACTURER);
define('TEXT_SHOP_PAYMENT_NAME',                    1 + (1<<16)*TABLE_MODULE_SHOP_PAYMENT);
define('TEXT_SHOP_PAYMENT_PROCESSORS_NAME',         1 + (1<<16)*TABLE_MODULE_SHOP_PAYMENT_PROCESSORS);
define('TEXT_SHOP_PAYMENT_PROCESSORS_DESCRIPTION',  2 + (1<<16)*TABLE_MODULE_SHOP_PAYMENT_PROCESSORS);
define('TEXT_SHOP_PAYMENT_PROCESSORS_COMPANY_URL',  3 + (1<<16)*TABLE_MODULE_SHOP_PAYMENT_PROCESSORS);
define('TEXT_SHOP_PAYMENT_PROCESSORS_PICTURE',      4 + (1<<16)*TABLE_MODULE_SHOP_PAYMENT_PROCESSORS);
define('TEXT_SHOP_PAYMENT_PROCESSORS_TEXT',         5 + (1<<16)*TABLE_MODULE_SHOP_PAYMENT_PROCESSORS);
define('TEXT_SHOP_PRODUCTS_TITLE',                  1 + (1<<16)*TABLE_MODULE_SHOP_PRODUCTS);
define('TEXT_SHOP_PRODUCTS_SHORTDESC',              2 + (1<<16)*TABLE_MODULE_SHOP_PRODUCTS);
define('TEXT_SHOP_PRODUCTS_DESCRIPTION',            3 + (1<<16)*TABLE_MODULE_SHOP_PRODUCTS);
define('TEXT_SHOP_PRODUCTS_KEYWORDS',               4 + (1<<16)*TABLE_MODULE_SHOP_PRODUCTS);
define('TEXT_SHOP_PRODUCTS_ATTRIBUTES_NAME',        1 + (1<<16)*TABLE_MODULE_SHOP_PRODUCTS_ATTRIBUTES_NAME);
define('TEXT_SHOP_PRODUCTS_ATTRIBUTES_VALUE',       1 + (1<<16)*TABLE_MODULE_SHOP_PRODUCTS_ATTRIBUTES_VALUE);
define('TEXT_SHOP_PRODUCTS_DOWNLOADS_NAME',         1 + (1<<16)*TABLE_MODULE_SHOP_PRODUCTS_DOWNLOADS);
define('TEXT_SHOP_PRODUCTS_DOWNLOADS_FILENAME',     2 + (1<<16)*TABLE_MODULE_SHOP_PRODUCTS_DOWNLOADS);
define('TEXT_SHOP_SHIPPER_NAME',                    1 + (1<<16)*TABLE_MODULE_SHOP_SHIPPER);
define('TEXT_SHOP_VAT_CLASS',                       1 + (1<<16)*TABLE_MODULE_SHOP_VAT);
define('TEXT_SHOP_ZONES_NAME',                      1 + (1<<16)*TABLE_MODULE_SHOP_ZONES);

?>
