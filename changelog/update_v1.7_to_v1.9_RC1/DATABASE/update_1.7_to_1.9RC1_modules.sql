
ALTER TABLE `astalavista_module_docsys` ADD `author` VARCHAR( 150 ) NOT NULL AFTER `title` ;





DROP TABLE IF EXISTS astalavista_module_calendar_categories;
CREATE TABLE astalavista_module_calendar_categories (
  id int(5) NOT NULL auto_increment,
  name varchar(150) NOT NULL default '',
  status int(1) NOT NULL default '0',
  lang int(1) NOT NULL default '0',
  pos int(5) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

ALTER TABLE `astalavista_module_calendar` ADD `catid` TINYINT( 3 ) DEFAULT '0' NOT NULL AFTER `id` ;

DROP TABLE IF EXISTS astalavista_module_calendar;
CREATE TABLE astalavista_module_calendar (
  id int(11) NOT NULL auto_increment,
  catid int(11) NOT NULL default '0',
  date varchar(10) NOT NULL default '',
  time varchar(4) NOT NULL default '',
  end_date varchar(10) NOT NULL default '',
  end_time varchar(4) NOT NULL default '',
  priority int(1) NOT NULL default '3',
  name varchar(25) NOT NULL default '',
  comment text NOT NULL,
  sort varchar(10) NOT NULL default '',
  place varchar(25) NOT NULL default '',
  info varchar(255) NOT NULL default 'http://',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

DROP TABLE IF EXISTS astalavista_module_calendar_style;
CREATE TABLE astalavista_module_calendar_style (
  id int(11) NOT NULL auto_increment,
  tableWidth varchar(4) NOT NULL default '141',
  tableHeight varchar(4) NOT NULL default '92',
  tableColor varchar(7) NOT NULL default '',
  tableBorder int(11) NOT NULL default '0',
  tableBorderColor varchar(7) NOT NULL default '',
  tableSpacing int(11) NOT NULL default '0',
  fontSize int(11) NOT NULL default '10',
  fontColor varchar(7) NOT NULL default '',
  numColor varchar(7) NOT NULL default '',
  normalDayColor varchar(7) NOT NULL default '',
  normalDayRollOverColor varchar(7) NOT NULL default '',
  curDayColor varchar(7) NOT NULL default '',
  curDayRollOverColor varchar(7) NOT NULL default '',
  eventDayColor varchar(7) NOT NULL default '',
  eventDayRollOverColor varchar(7) NOT NULL default '',
  shownEvents int(4) NOT NULL default '10',
  commentLength int(4) NOT NULL default '500',
  periodTime varchar(5) NOT NULL default '00 23',
  PRIMARY KEY  (id)
) TYPE=MyISAM;


ALTER TABLE `astalavista_guestbook` RENAME `astalavista_module_guestbook`;
ALTER TABLE `astalavista_module_guestbook` CHANGE `gbnick` `nickname` TINYTEXT NOT NULL;
ALTER TABLE `astalavista_module_guestbook` CHANGE `gbgender` `gender` CHAR( 1 ) NOT NULL;
ALTER TABLE `astalavista_module_guestbook` CHANGE `gburl` `url` TINYTEXT NOT NULL;
ALTER TABLE `astalavista_module_guestbook` CHANGE `gbtime` `time` VARCHAR( 8 ) NOT NULL;
ALTER TABLE `astalavista_module_guestbook` CHANGE `gbdate` `date` VARCHAR( 10 ) NOT NULL;
ALTER TABLE `astalavista_module_guestbook` CHANGE `gbmail` `email` TINYTEXT NOT NULL;
ALTER TABLE `astalavista_module_guestbook` CHANGE `gbcomment` `comment` TEXT NOT NULL;
ALTER TABLE `astalavista_module_guestbook` CHANGE `gbip` `ip` VARCHAR( 15 ) NOT NULL;
ALTER TABLE `astalavista_module_guestbook` CHANGE `gblocation` `location` TINYTEXT NOT NULL; 
ALTER TABLE `astalavista_module_guestbook` ADD `lang_id` TINYINT( 2 ) DEFAULT '1' NOT NULL;
ALTER TABLE `astalavista_module_guestbook` CHANGE `gbid` `id` SMALLINT( 6 ) NOT NULL AUTO_INCREMENT;
ALTER TABLE `astalavista_module_guestbook` ADD `datetime` DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL AFTER `lang_id` ;
ALTER TABLE `astalavista_module_guestbook` DROP `time` , DROP `date`;





// SHOP


ALTER TABLE `astalavista_shop_categories` RENAME `astalavista_module_shop_categories`;
ALTER TABLE `astalavista_shop_customers` RENAME `astalavista_module_shop_customers`;
ALTER TABLE `astalavista_shop_mail` RENAME `astalavista_module_shop_mail`;
ALTER TABLE `astalavista_shop_order_items` RENAME `astalavista_module_shop_order_items`;
ALTER TABLE `astalavista_shop_orders` RENAME `astalavista_module_shop_orders`;
ALTER TABLE `astalavista_shop_pricelists` RENAME `astalavista_module_shop_pricelists`;
ALTER TABLE `astalavista_shop_products` RENAME `astalavista_module_shop_products`;
ALTER TABLE `astalavista_shop_settings` RENAME `astalavista_module_shop_settings`; 

ALTER TABLE `astalavista_module_shop_products` ADD `product_id` TINYINT( 7 ) DEFAULT '0' NOT NULL AFTER `id`;
ALTER TABLE `astalavista_module_shop_orders` CHANGE `payment_typ` `payment_typ` TINYINT( 3 ) NOT NULL;
ALTER TABLE `astalavista_module_shop_orders` ADD `transaction_status` TINYINT( 2 ) DEFAULT '0' NOT NULL AFTER `customerid`;

#
# Tabellenstruktur für Tabelle `astalavista_module_shop_config`
#

DROP TABLE IF EXISTS astalavista_module_shop_config;
CREATE TABLE astalavista_module_shop_config (
  id int(11) NOT NULL auto_increment,
  name varchar(64) NOT NULL default '',
  value varchar(255) NOT NULL default '',
  status int(1) default '1',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

#
# Daten für Tabelle `astalavista_module_shop_config`
#

INSERT INTO astalavista_module_shop_config VALUES (21, 'email', 'thun@astalavista.ch', 1);
INSERT INTO astalavista_module_shop_config VALUES (22, 'tax_number', '535920', 1);
INSERT INTO astalavista_module_shop_config VALUES (23, 'tax_included', '1', 1);
INSERT INTO astalavista_module_shop_config VALUES (24, 'tax_percentaged_value', '7.6', 1);
INSERT INTO astalavista_module_shop_config VALUES (28, 'saferpay_id', '', 0);
INSERT INTO astalavista_module_shop_config VALUES (29, 'yellowpay_id', 'demoasta_yp', 1);
INSERT INTO astalavista_module_shop_config VALUES (30, 'yellowpay_hash_seed', '', 1);
INSERT INTO astalavista_module_shop_config VALUES (31, 'confirmation_emails', 'thun@astalavista.ch, chur@astalavista.ch', 1);
INSERT INTO astalavista_module_shop_config VALUES (32, 'telephone', '', 1);
INSERT INTO astalavista_module_shop_config VALUES (33, 'fax', '033 221 90 92', 1);
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `astalavista_module_shop_currencies`
#

DROP TABLE IF EXISTS astalavista_module_shop_currencies;
CREATE TABLE astalavista_module_shop_currencies (
  id int(11) NOT NULL auto_increment,
  symbol char(3) NOT NULL default '',
  name varchar(50) NOT NULL default '',
  rate decimal(10,6) NOT NULL default '1.000000',
  sort_order int(3) default NULL,
  status int(1) default '1',
  is_default tinyint(2) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

#
# Daten für Tabelle `astalavista_module_shop_currencies`
#

INSERT INTO astalavista_module_shop_currencies VALUES (1, 'CHF', 'Swiss Francs', '1.000000', 1, 0, 0);
INSERT INTO astalavista_module_shop_currencies VALUES (2, 'EUR', 'Euro', '0.780000', 2, 0, 1);
INSERT INTO astalavista_module_shop_currencies VALUES (3, '', '', '1.000000', NULL, 0, 0);
INSERT INTO astalavista_module_shop_currencies VALUES (4, '', '', '1.000000', NULL, 0, 0);
INSERT INTO astalavista_module_shop_currencies VALUES (5, '', '', '1.000000', NULL, 0, 0);
INSERT INTO astalavista_module_shop_currencies VALUES (6, '', '', '1.000000', NULL, 0, 0);
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `astalavista_module_shop_payment`
#

DROP TABLE IF EXISTS astalavista_module_shop_payment;
CREATE TABLE astalavista_module_shop_payment (
  id int(11) NOT NULL auto_increment,
  name varchar(50) default NULL,
  handler enum('Internal','Saferpay','Paypal','PostFinance Debit Direct') NOT NULL default 'Internal',
  lang_id int(3) NOT NULL default '0',
  sort_order int(3) default '0',
  status int(1) default '1',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

#
# Daten für Tabelle `astalavista_module_shop_payment`
#

INSERT INTO astalavista_module_shop_payment VALUES (1, 'Rechnung', 'Internal', 0, 0, 1);
INSERT INTO astalavista_module_shop_payment VALUES (2, 'Creditcard', 'Saferpay', 0, 0, 1);
INSERT INTO astalavista_module_shop_payment VALUES (3, 'PayPal', 'Paypal', 0, 0, 1);
INSERT INTO astalavista_module_shop_payment VALUES (4, NULL, 'Internal', 0, 0, 0);
INSERT INTO astalavista_module_shop_payment VALUES (5, NULL, 'Internal', 0, 0, 0);
INSERT INTO astalavista_module_shop_payment VALUES (6, NULL, 'Internal', 0, 0, 0);
INSERT INTO astalavista_module_shop_payment VALUES (7, NULL, 'Internal', 0, 0, 0);
INSERT INTO astalavista_module_shop_payment VALUES (8, NULL, 'Internal', 0, 0, 0);
INSERT INTO astalavista_module_shop_payment VALUES (9, NULL, 'Internal', 0, 0, 0);
INSERT INTO astalavista_module_shop_payment VALUES (10, NULL, 'Internal', 0, 0, 0);
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `astalavista_module_shop_pricelists`
#

DROP TABLE IF EXISTS astalavista_module_shop_pricelists;
CREATE TABLE astalavista_module_shop_pricelists (
  id int(11) NOT NULL auto_increment,
  name varchar(25) NOT NULL default '',
  border_on tinyint(1) NOT NULL default '1',
  header_on tinyint(1) NOT NULL default '1',
  header_left text,
  header_right text,
  footer_on tinyint(1) NOT NULL default '0',
  footer_left text,
  footer_right text,
  categories text NOT NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

#
# Daten für Tabelle `astalavista_module_shop_pricelists`
#

INSERT INTO astalavista_module_shop_pricelists VALUES (4, 'Test2', 1, 1, '', '', 1, '<--DATE-->', '<--PAGENUMBER-->', '*');
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `astalavista_module_shop_shipment`
#

DROP TABLE IF EXISTS astalavista_module_shop_shipment;
CREATE TABLE astalavista_module_shop_shipment (
  id int(8) NOT NULL auto_increment,
  name varchar(30) NOT NULL default '',
  costs decimal(8,2) NOT NULL default '0.00',
  lang_id int(2) NOT NULL default '0',
  status int(1) default '1',
  PRIMARY KEY  (id),
  KEY ID (id)
) TYPE=MyISAM;

#
# Daten für Tabelle `astalavista_module_shop_shipment`
#

INSERT INTO astalavista_module_shop_shipment VALUES (1, 'PostPac Priority', '20.00', 1, 1);
INSERT INTO astalavista_module_shop_shipment VALUES (2, 'PostPac Economy', '12.00', 1, 1);
INSERT INTO astalavista_module_shop_shipment VALUES (3, 'Sperrgut Economy', '14.00', 1, 1);
INSERT INTO astalavista_module_shop_shipment VALUES (4, '', '0.00', 0, 1);
INSERT INTO astalavista_module_shop_shipment VALUES (5, '', '0.00', 0, 0);
INSERT INTO astalavista_module_shop_shipment VALUES (6, '', '0.00', 0, 0);
INSERT INTO astalavista_module_shop_shipment VALUES (7, '', '0.00', 0, 0);
INSERT INTO astalavista_module_shop_shipment VALUES (8, '', '0.00', 0, 0);
INSERT INTO astalavista_module_shop_shipment VALUES (9, '', '0.00', 0, 0);
INSERT INTO astalavista_module_shop_shipment VALUES (10, '', '0.00', 0, 0);


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


ALTER TABLE `astalavista_newsletter_emails` CHANGE `emailid` `emailid` SMALLINT( 6 ) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `astalavista_module_shop_products` CHANGE `id` `id` SMALLINT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `astalavista_module_gallery_pictures` CHANGE `linkname` `linkname` TEXT NOT NULL;

// Shop
ALTER TABLE `astalavista_module_shop_products` CHANGE `manufacturer_id` `manufacturer_url` VARCHAR( 255 ) NOT NULL 

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


