// sql modifications from v1.7 RC2

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

/* wenn das gästebuch leer war, kann es gerade neu erstellt werden!
DROP TABLE IF EXISTS astalavista_module_guestbook;
CREATE TABLE astalavista_module_guestbook (
  id smallint(6) NOT NULL auto_increment,
  nickname tinytext NOT NULL,
  gender char(1) NOT NULL default '',
  url tinytext NOT NULL,
  email tinytext NOT NULL,
  comment text NOT NULL,
  ip varchar(15) NOT NULL default '',
  location tinytext NOT NULL,
  lang_id tinyint(2) NOT NULL default '1',
  datetime datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (id),
  FULLTEXT KEY comment (comment)
) TYPE=MyISAM;
*/



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



