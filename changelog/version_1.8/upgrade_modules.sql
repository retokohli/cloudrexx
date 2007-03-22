// sql modifications from v1.8

ALTER TABLE `astalavista_module_docsys` ADD `author` VARCHAR( 150 ) NOT NULL AFTER `title` ;


CREATE TABLE `astalavista_module_feed_category` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(150) NOT NULL default '',
  `status` int(1) NOT NULL default '1',
  `time` int(100) NOT NULL default '0',
  `lang` int(1) NOT NULL default '0',
  `pos` int(3) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) type=MyISAM;

CREATE TABLE `astalavista_module_feed_news` (
  `id` int(11) NOT NULL auto_increment,
  `subid` int(11) NOT NULL default '0',
  `name` varchar(150) NOT NULL default '',
  `link` varchar(150) NOT NULL default '',
  `filename` varchar(150) NOT NULL default '',
  `articles` int(2) NOT NULL default '0',
  `cache` int(4) NOT NULL default '3600',
  `time` int(100) NOT NULL default '0',
  `image` int(1) NOT NULL default '1',
  `status` int(1) NOT NULL default '1',
  `pos` int(3) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) type=MyISAM;
*/


ALTER TABLE `astalavista_module_shop_orders` CHANGE `order_status` `order_status` ENUM( '0', '1', '2', '3', '4' ) DEFAULT '0';
ALTER TABLE `astalavista_module_shop_orders` CHANGE `ship_country` `ship_country_id` SMALLINT;
ALTER TABLE `astalavista_module_shop_orders` CHANGE `payment_typ` `payment_id` TINYINT( 3 );
ALTER TABLE `astalavista_module_shop_orders` CHANGE `shipping_typ` `shipping_id` TINYINT;
ALTER TABLE `astalavista_module_shop_orders` ADD `payment_price` FLOAT( 6, 2 ) DEFAULT '0.00' NOT NULL AFTER `payment_id` ;
ALTER TABLE `astalavista_module_shop_orders` CHANGE `selected_currency_id` `selected_currency_id` SMALLINT UNSIGNEDDEFAULT '0' NOT NULL;
ALTER TABLE `astalavista_module_shop_orders` ADD `tax_price` FLOAT( 6, 2 ) DEFAULT '0.00' NOT NULL AFTER `ship_country_id`;
ALTER TABLE `astalavista_module_shop_orders` ADD `currency_order_sum` FLOAT( 8, 2 ) NOT NULL AFTER `order_sum`;
ALTER TABLE `astalavista_module_shop_orders` CHANGE `order_sum` `order_sum` FLOAT( 8, 2 ) NOT NULL;
ALTER TABLE `astalavista_module_shop_orders` ADD INDEX ( `order_status` );
ALTER TABLE `astalavista_module_shop_customers` CHANGE `country` `country_id` SMALLINT( 6 );
ALTER TABLE `astalavista_module_shop_orders` DROP `transaction_status`;
ALTER TABLE `astalavista_module_shop_orders` CHANGE `payment_price` `currency_payment_price` FLOAT( 6, 2 ) DEFAULT '0.00' NOT NULL;
ALTER TABLE `astalavista_module_shop_orders` CHANGE `shipping_price` `currency_ship_price` FLOAT( 6, 2 ) DEFAULT '0.00' NOT NULL;
INSERT INTO `astalavista_module_shop_config` ( `id` , `name` , `value` , `status` )VALUES ('', 'saferpay_finalize_payment', '1', '1');
ALTER TABLE `astalavista_module_shop_orders` CHANGE `order_status` `order_status` ENUM( '0', '1', '2', '3', '4' ) NOT NULL;
ALTER TABLE `astalavista_module_shop_pricelists` ADD `lang_id` TINYINT( 2 ) UNSIGNED NOT NULL AFTER `name` ;


DROP TABLE `astalavista_module_shop_settings`;

ALTER TABLE `astalavista_module_shop_orders` ADD `ship_phone` VARCHAR( 20 ) NOT NULL AFTER `ship_country_id` ;
ALTER TABLE `astalavista_module_shop_customers` ADD `cvc_code` VARCHAR( 5 ) NOT NULL AFTER `ccname` ;
ALTER TABLE `astalavista_module_shop_orders` ADD `customer_ip` VARCHAR( 50 ) NOT NULL AFTER `currency_payment_price` ,
ADD `customer_host` VARCHAR( 100 ) NOT NULL AFTER `customer_ip` ,
ADD `customer_lang` VARCHAR( 10 ) NOT NULL AFTER `customer_host` ,
ADD `customer_browser` VARCHAR( 100 ) NOT NULL AFTER `customer_lang` ;
ALTER TABLE `astalavista_module_shop_customers` DROP `customer_ip` , DROP `customer_host` , DROP `customer_lang` , DROP `customer_browser` ;

INSERT INTO `astalavista_module_shop_config` ( `id` , `name` , `value` , `status` ) VALUES ( '', 'saferpay_window_option', '0', '1');


ALTER TABLE `astalavista_module_shop_products` CHANGE `normalprice` `normalprice` DECIMAL( 6, 2 ) DEFAULT '0.00' NOT NULL;
ALTER TABLE `astalavista_module_shop_products` CHANGE `resellerprice` `resellerprice` DECIMAL( 6, 2 ) DEFAULT '0.00' NOT NULL,
CHANGE `discountprice` `discountprice` DECIMAL( 6, 2 ) DEFAULT '0.00' NOT NULL;






