# TICKET
# -----------------------------------

INSERT INTO `contrexx_modules` ( `id` , `name` , `description_variable` , `status` , `is_required` , `is_core` )VALUES ('31', 'ticket', 'TXT_TICKET_MODULE_DESCRIPTION', 'n', '0', '0');
INSERT INTO `contrexx_backend_areas` ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` )VALUES ('89', '2', 'navigation', 'TXT_TICKET', '1', 'index.php?cmd=ticket&dirid=1', '_self', '31', '0', '83');



CREATE TABLE `contrexx_module_ticket_categories` (`id` INT( 5 ) NOT NULL AUTO_INCREMENT ,`name` VARCHAR( 100 ) NOT NULL ,`pophost` VARCHAR( 200 ) NOT NULL ,`popuser` VARCHAR( 200 ) NOT NULL ,`poppass` VARCHAR( 200 ) NOT NULL ,`email` VARCHAR( 200 ) NOT NULL ,`signature` TEXT NOT NULL ,`hidden` TINYINT( 1 ) DEFAULT '0' NOT NULL ,PRIMARY KEY ( `id` ));
CREATE TABLE `contrexx_module_ticket_tickets` (`id` int(8) NOT NULL default '0',  `email` varchar(255) NOT NULL default '',  `name` varchar(255) NOT NULL default '',  `status` enum('open','closed') NOT NULL default 'open',  `subject` varchar(255) NOT NULL default '',  `date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00',  `priority` tinyint(4) NOT NULL default '0',  `category` int(5) NOT NULL default '0',  `phone` varchar(30) NOT NULL default '',  PRIMARY KEY  (`id`));
CREATE TABLE `contrexx_module_ticket_messages` (`id` MEDIUMINT( 7 ) NOT NULL AUTO_INCREMENT ,`uid` VARCHAR( 32 ) NOT NULL ,`ticket` INT( 8 ) DEFAULT '0' NOT NULL ,`name` VARCHAR( 255 ) NOT NULL ,`email` VARCHAR( 255 ) NOT NULL ,`subject` VARCHAR( 255 ) DEFAULT '[no subject]' NOT NULL ,`body` TEXT NOT NULL ,`date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00', PRIMARY KEY ( `id` ) ,INDEX ( `ticket` ) ,UNIQUE (`uid`))
