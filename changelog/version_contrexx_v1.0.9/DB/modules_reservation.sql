INSERT INTO `contrexx_modules` ( `id` , `name` , `description_variable` , `status` , `is_required` , `is_core` )
VALUES (
'34', 'reservation', 'TXT_RESERVATION_MODULE_DESCRIPTION', 'y', '0', '0'
);

INSERT INTO `contrexx_backend_areas` ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` )
VALUES (
NULL , '2', 'navigation', 'TXT_RESERVATION', '1', '?cmd=reservation', '_self', '34', '21', '84'
);

CREATE TABLE `contrexx_module_reservation_settings` (
`setid` INT( 14 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`setname` VARCHAR( 255 ) NOT NULL ,
`setvalue` TEXT NOT NULL ,
`lang_id` INT( 14 ) NOT NULL
) TYPE = MYISAM ;

INSERT INTO `contrexx_module_reservation_settings` VALUES (1, 'unit', '1800', 1);
INSERT INTO `contrexx_module_reservation_settings` VALUES (2, 'framestart', '1136098800', 1);
INSERT INTO `contrexx_module_reservation_settings` VALUES (3, 'frameend', '1136134800', 1);
INSERT INTO `contrexx_module_reservation_settings` VALUES (4, 'description', 'Hier können Sie dies und jenes Reservieren.', 1);
INSERT INTO `contrexx_module_reservation_settings` VALUES (5, 'confirmation', '1', 1);

CREATE TABLE `contrexx_module_reservation` (
  `id` int(14) unsigned NOT NULL auto_increment,
  `status` set('1','0') NOT NULL default '0',
  `confirmed` set('1','0') NOT NULL default '0',
  `day` varchar(10) NOT NULL default '',
  `unit` int(2) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `email` varchar(255) NOT NULL default '',
  `phone` varchar(255) NOT NULL default '',
  `comments` text NOT NULL,
  `lang_id` int(11) NOT NULL default '0',
  `time` int(11) NOT NULL default '0',
  `hash` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=4 ;

INSERT INTO `contrexx_module_reservation_settings` ( `setid` , `setname` , `setvalue` , `lang_id` )
VALUES (
NULL , 'mailtext', 'Vielen Dank für Ihre Reservation

Bitte klicken Sie hier
<URL>
um die Rervation zu vervollständigen.

Mit Freundlichen Grüssen
Ihr Contrexx Team', '1'
);

