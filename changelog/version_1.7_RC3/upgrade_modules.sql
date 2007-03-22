// sql modifications from v1.7 RC3
// Betrifft nur den SHOP!
// Am besten neu erstellen!

ALTER TABLE `astalavista_module_shop_currencies` ADD `code` CHAR( 3 ) NOT NULL AFTER `id`;
ALTER TABLE `astalavista_module_shop_currencies` CHANGE `symbol` `symbol` VARCHAR( 20 ) NOT NULL;
ALTER TABLE `astalavista_module_shop_orders` ADD `selected_currency_id` INT( 11 ) NOT NULL AFTER `transaction_status`;
ALTER TABLE `astalavista_module_shop_countries` ADD `zone_ids` TINYTEXT NOT NULL AFTER `countries_iso_code_3` ;
ALTER TABLE `astalavista_module_shop_payment` ADD `costs` DECIMAL( 8, 2 ) DEFAULT '0.00' NOT NULL AFTER `handler` , ADD `zone_id` INT( 3 ) DEFAULT '0' NOT NULL AFTER `costs` ;
ALTER TABLE `astalavista_module_shop_shipment` ADD `zone_id` INT( 3 ) DEFAULT '0' NOT NULL AFTER `costs` ;
ALTER TABLE `astalavista_module_shop_zones` ADD `payment_ids` TINYTEXT NOT NULL AFTER `zones_name` , ADD `shipment_ids` TINYTEXT NOT NULL AFTER `payment_ids` ;


ALTER TABLE `astalavista_module_shop_customers` CHANGE `email` `email` VARCHAR( 255 ) NOT NULL;

ALTER TABLE `astalavista_module_shop_payment` ADD `costs_free_sum` DECIMAL( 8, 2 ) NOT NULL AFTER `costs`;
ALTER TABLE `astalavista_module_shop_payment` CHANGE `handler` `processor_id` INT( 3 ) DEFAULT '0' NOT NULL;
ALTER TABLE `astalavista_module_shop_shipment` ADD `costs_free_sum` DECIMAL( 8, 2 ) NOT NULL AFTER `costs`;

DROP TABLE IF EXISTS astalavista_module_shop_payment_processors;
CREATE TABLE astalavista_module_shop_payment_processors (
  id int(3) NOT NULL auto_increment,
  type enum('internal','external') NOT NULL default 'internal',
  name varchar(100) NOT NULL default '',
  description varchar(250) NOT NULL default '',
  status int(1) default '1',
  picture varchar(100) NOT NULL default '',
  text text NOT NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

#
# Daten für Tabelle `astalavista_module_shop_payment_processors`
#

INSERT INTO astalavista_module_shop_payment_processors VALUES (1, 'external', 'Saferpay', '', 1, '', '');
INSERT INTO astalavista_module_shop_payment_processors VALUES (2, 'external', 'Paypal', '', 1, '', '');
INSERT INTO astalavista_module_shop_payment_processors VALUES (3, 'external', 'PostFinance_DebitDirect', '', 1, '', '');
INSERT INTO astalavista_module_shop_payment_processors VALUES (4, 'internal', 'Internal', 'Internal no forms', 1, '', '');
INSERT INTO astalavista_module_shop_payment_processors VALUES (5, 'internal', 'Internal_CreditCard', 'Internal with a Credit Card form', 1, '', '');


DROP TABLE IF EXISTS astalavista_module_shop_mail_content;
CREATE TABLE astalavista_module_shop_mail_content (
  id tinyint(4) NOT NULL auto_increment,
  tpl_id tinyint(4) NOT NULL default '0',
  lang_id tinyint(2) unsigned NOT NULL default '0',
  from varchar(255) NOT NULL default '',
  xsender tinytext NOT NULL,
  subject varchar(255) NOT NULL default '',
  message text NOT NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

INSERT INTO `astalavista_module_shop_config` ( `id` , `name` , `value` , `status` ) VALUES ('', 'saferpay_use_test_account', '0', '1');


 