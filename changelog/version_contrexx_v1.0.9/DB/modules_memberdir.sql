--- Falls die Ticket-System-Tabellen noch vorhanden sind ---
DELETE FROM `contrexx_modules` WHERE `id` = 31 AND `name` = 'ticket' AND `description_variable` = 'TXT_TICKET_MODULE_DESCRIPTION' AND `status` = 'n' AND `is_required` = 0 AND `is_core` = 0 LIMIT 1;

--- Falls die Ticket-System-Tabellen noch vorhanden sind ---
DELETE FROM `contrexx_backend_areas` WHERE `area_id` = 89 LIMIT 1;

--- WIR GRISO ZEUGS ---
CREATE TABLE `contrexx_module_memberdir` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
	`name` VARCHAR( 255 ) NOT NULL ,
	`vorname` VARCHAR( 255 ) NOT NULL ,
	`firma` VARCHAR( 255 ) NOT NULL ,
	`strasse` VARCHAR( 255 ) NOT NULL ,
	`plz` INT( 4 ) NOT NULL ,
	`ort` VARCHAR( 255 ) NOT NULL ,
	`telefon` VARCHAR( 20 ) NOT NULL ,
	`fax` VARCHAR( 20 ) NOT NULL ,
	`e-mail` VARCHAR( 255 ) NOT NULL ,
	`bemerkung` TEXT NOT NULL ,
	`branche` VARCHAR( 255 ) NOT NULL ,
	`wir_konto_nr` VARCHAR( 255 ) NOT NULL ,
	`wir_satz` VARCHAR( 255 ) NOT NULL ,
	`kontoart` SET( 'Genossenschafter', 'Offizielles Konto', 'Stilles Konto' ) NOT NULL ,
	`datum` VARCHAR( 255 ) NOT NULL ,
PRIMARY KEY ( `id` )
);

ALTER TABLE `contrexx_module_memberdir` CHANGE `wir_konto_nr` `internet` VARCHAR( 255 ) NOT NULL;

--- Zum löschen der tabelle ---
DROP TABLE `contrexx_module_memberdir`


--- AB HIER SIND DIE RICHTIGEN QUERIES --
INSERT INTO `contrexx_modules` VALUES (31, 'memberdir', 'TXT_MEMBERDIR_MODULE_DESCRIPTION', 'y', 0, 0);
INSERT INTO `contrexx_backend_areas` VALUES (89, 2, 'navigation', 'TXT_MEMBERDIR', 1, 'index.php?cmd=memberdir', '_self', 31, 20, 83);

CREATE TABLE `contrexx_module_memberdir_settings` (
  `setid` int(4) unsigned NOT NULL auto_increment,
  `setname` varchar(255) NOT NULL default '',
  `setvalue` text NOT NULL,
  `lang_id` tinyint(2) NOT NULL default '1',
  PRIMARY KEY  (`setid`)
);

INSERT INTO `contrexx_module_memberdir_settings` (`setname`, `setvalue`, `lang_id`) VALUES ('default_listing', '1', 1);
INSERT INTO `contrexx_module_memberdir_settings` (`setname`, `setvalue`, `lang_id`) VALUES ('max_height', '400', 1);
INSERT INTO `contrexx_module_memberdir_settings` (`setname`, `setvalue`, `lang_id`) VALUES ('max_width', '500', 1);

CREATE TABLE `contrexx_module_memberdir_directories` (
  `dirid` int(10) unsigned NOT NULL auto_increment,
  `parentdir` int(11) NOT NULL default '0',
  `active` set('1','0') NOT NULL default '1',
  `name` varchar(255) NOT NULL default '',
  `description` text NOT NULL,
  `displaymode` set('0','1','2') NOT NULL default '0',
  `sort` int(11) NOT NULL default '1',
  `pic1` set('1','0') NOT NULL default '0',
  `pic2` set('1','0') NOT NULL default '0',
  `lang_id` tinyint(2) NOT NULL default '1',
  PRIMARY KEY  (`dirid`)
);



CREATE TABLE `contrexx_module_memberdir_name` (
  `field` int(10) unsigned NOT NULL default '0',
  `dirid` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `active` set('0','1') NOT NULL default '',
  `lang_id` tinyint(2) NOT NULL default '1'
);


CREATE TABLE `contrexx_module_memberdir_values` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `dirid` int(14) NOT NULL default '0',
  `pic1` varchar(255) NOT NULL default '',
  `pic2` varchar(255) NOT NULL default '',
  `1` text NOT NULL,
  `2` text NOT NULL,
  `3` text NOT NULL,
  `4` text NOT NULL,
  `5` text NOT NULL,
  `6` text NOT NULL,
  `7` text NOT NULL,
  `8` text NOT NULL,
  `9` text NOT NULL,
  `10` text NOT NULL,
  `11` text NOT NULL,
  `12` text NOT NULL,
  `13` text NOT NULL,
  `14` text NOT NULL,
  `15` text NOT NULL,
  `16` text NOT NULL,
  `17` text NOT NULL,
  `18` text NOT NULL,
  `lang_id` tinyint(2) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;


--- Das hier ist nur nötig, wenn ein Update gemacht wird (teilweise) ---
UPDATE contrexx_module_memberdir_values SET `pic1` = 'none'  WHERE CHAR_LENGTH(pic1) = 0
UPDATE contrexx_module_memberdir_values SET `pic2` = 'none'  WHERE CHAR_LENGTH(pic2) = 0