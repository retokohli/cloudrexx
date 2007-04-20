/* Update Skript erstellt */

INSERT INTO `contrexx_module_shop_config` ( `id` , `name` , `value` , `status` )
VALUES (
NULL , 'paypal_default_currency', 'EUR', '1'
);

CREATE TABLE `contrexx_module_shop_importimg` (
	`img_id` int(11) NOT NULL auto_increment,
	`img_name` varchar(255) NOT NULL default '',
	`img_cats` text NOT NULL,
	`img_fields_file` text NOT NULL,
	`img_fields_db` varchar(255) NOT NULL default '',
	PRIMARY KEY  (`img_id`)
	) TYPE=MyISAM


UPDATE `contrexx_module_shop_payment_processors` SET `status` = '0' WHERE `name` = 'Internal_CreditCard' OR `name` = 'Internal_Debit';