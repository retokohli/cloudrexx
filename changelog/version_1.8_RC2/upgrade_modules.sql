// sql modifications from v1.8 RC2

ALTER TABLE `astalavista_newsletter_emails` CHANGE `emailid` `emailid` SMALLINT( 6 ) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `astalavista_module_gallery_pictures` CHANGE `linkname` `linkname` TEXT NOT NULL;





// Shop
ALTER TABLE `astalavista_module_shop_products` CHANGE `id` `id` SMALLINT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `astalavista_module_shop_products` CHANGE `manufacturer_id` `manufacturer_url` VARCHAR( 255 ) NOT NULL;

CREATE TABLE `astalavista_module_shop_products_attributes` (
`attribute_id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
`product_id` SMALLINT( 10 ) UNSIGNED NOT NULL ,
`attributes_name_id` INT( 11 ) UNSIGNED NOT NULL ,
`attributes_value_id` INT( 11 ) UNSIGNED NOT NULL ,
`sort_id` smallint(5) unsigned NOT NULL default '0',
PRIMARY KEY ( `attribute_id` )
);


CREATE TABLE `astalavista_module_shop_products_attributes_name` (
`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
`name` VARCHAR( 32 ) NOT NULL ,
`display_type` enum('0','1','2') NOT NULL default '0',
PRIMARY KEY ( `id` )
);

CREATE TABLE `astalavista_module_shop_products_attributes_value` (
`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
`name_id` INT( 11 ) UNSIGNED NOT NULL ,
`value` VARCHAR( 32 ) NOT NULL ,
`price` DECIMAL( 6, 2 ) NOT NULL ,
`price_prefix` ENUM( '+', '-' ) DEFAULT '+' NOT NULL ,
PRIMARY KEY ( `id` )
);

ALTER TABLE `astalavista_module_shop_order_items` DROP `property1`,DROP `property2`,DROP `property3`;

CREATE TABLE `astalavista_module_shop_order_items_attributes` (
`orders_items_attributes_id` int( 11 ) NOT NULL AUTO_INCREMENT ,
`order_id` int( 11 ) NOT NULL default '0',
`product_id` int( 11 ) NOT NULL default '0',
`product_option_name` varchar( 32 ) NOT NULL default '',
`product_option_value` varchar( 32 ) NOT NULL default '',
`product_option_values_price` decimal( 6, 2 ) NOT NULL default '0.00',
`price_prefix` ENUM( '+', '-' ) DEFAULT '+' NOT NULL ,
PRIMARY KEY ( `orders_items_attributes_id` )
) TYPE = MYISAM;

ALTER TABLE `astalavista_module_shop_order_items` ADD `product_name` VARCHAR( 100 ) NOT NULL AFTER `productid`;













