INSERT INTO `contrexx_backend_areas` ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` ) VALUES ('', '1', 'navigation', 'TXT_CONTACTS', '1', 'index.php?cmd=contact', '_self', '0', '7', '84');
INSERT INTO `contrexx_backend_areas` ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` ) VALUES ('', '90', 'function', 'TXT_CONTACT_SETTINGS', '1', 'index.php?cmd=contact&amp;act=settings', '_self', '6', '0', '85');

UPDATE `contrexx_modules` SET `is_required` = '1' WHERE `id` =6 AND `name` = 'contact' AND `description_variable` = 'TXT_CONTACT_MODULE_DESCRIPTION' AND `status` = 'y' AND `is_required` =0 AND `is_core` =1;

DROP TABLE IF EXISTS `contrexx_module_contact_form`;
CREATE TABLE IF NOT EXISTS `contrexx_module_contact_form` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `mails` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=2 ;

INSERT INTO `contrexx_module_contact_form` VALUES (1, 'Standard Kontaktformular', '');

DROP TABLE IF EXISTS `contrexx_module_contact_form_data`;
CREATE TABLE IF NOT EXISTS `contrexx_module_contact_form_data` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_form` int(10) unsigned NOT NULL default '0',
  `time` int(14) unsigned NOT NULL default '0',
  `host` varchar(255) NOT NULL default '',
  `lang` varchar(64) NOT NULL default '',
  `browser` varchar(255) NOT NULL default '',
  `ipaddress` varchar(15) NOT NULL default '',
  `data` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `contrexx_module_contact_form_field`;
CREATE TABLE IF NOT EXISTS `contrexx_module_contact_form_field` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_form` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `type` enum('text','checkbox','checkboxGroup','file','hidden','password','radio','select','textarea') NOT NULL default 'text',
  `attributes` text NOT NULL,
  `order_id` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=12 ;

INSERT INTO `contrexx_module_contact_form_field` VALUES (1, 1, 'Name', 'text', '', 0);
INSERT INTO `contrexx_module_contact_form_field` VALUES (2, 1, 'Firmenname', 'text', '', 1);
INSERT INTO `contrexx_module_contact_form_field` VALUES (3, 1, 'Strasse', 'text', '', 2);
INSERT INTO `contrexx_module_contact_form_field` VALUES (4, 1, 'PLZ', 'text', '', 3);
INSERT INTO `contrexx_module_contact_form_field` VALUES (5, 1, 'Ort', 'text', '', 4);
INSERT INTO `contrexx_module_contact_form_field` VALUES (6, 1, 'Land', 'text', '', 5);
INSERT INTO `contrexx_module_contact_form_field` VALUES (7, 1, 'Telefon', 'text', '', 6);
INSERT INTO `contrexx_module_contact_form_field` VALUES (8, 1, 'Fax', 'text', '', 7);
INSERT INTO `contrexx_module_contact_form_field` VALUES (9, 1, 'E-Mail', 'text', '', 8);
INSERT INTO `contrexx_module_contact_form_field` VALUES (10, 1, 'Bemerkungen', 'textarea', '', 9);
INSERT INTO `contrexx_module_contact_form_field` VALUES (11, 1, 'Datei', 'file', '', 10);


DROP TABLE IF EXISTS `contrexx_module_contact_settings`;
CREATE TABLE IF NOT EXISTS `contrexx_module_contact_settings` (
  `setid` smallint(6) NOT NULL auto_increment,
  `setname` varchar(250) NOT NULL default '',
  `setvalue` text NOT NULL,
  `status` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`setid`)
) TYPE=MyISAM AUTO_INCREMENT=2 ;

INSERT INTO `contrexx_module_contact_settings` VALUES (1, 'fileUploadDepositionPath', '/images/attach', 1);
        