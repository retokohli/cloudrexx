ALTER TABLE `contrexx_module_calendar` CHANGE `info` `link` VARCHAR( 255 ) NOT NULL DEFAULT 'http://'

ALTER TABLE `contrexx_module_calendar` ADD `pic` VARCHAR( 255 ) NOT NULL AFTER `link` ,
ADD `attachment` VARCHAR( 255 ) NOT NULL AFTER `pic` ;

ALTER TABLE `contrexx_module_calendar` CHANGE `place` `placeName` VARCHAR( 25 ) NOT NULL

ALTER TABLE `contrexx_module_calendar` ADD `placeStreet` VARCHAR( 255 ) NOT NULL AFTER `attachment` ,
ADD `placeZip` VARCHAR( 255 ) NOT NULL AFTER `placeStreet` ,
ADD `placeLink` VARCHAR( 255 ) NOT NULL AFTER `placeZip` ,
ADD `placeMap` VARCHAR( 255 ) NOT NULL AFTER `placeLink` ;

ALTER TABLE `contrexx_module_calendar` ADD `access` INT( 1 ) NOT NULL DEFAULT '0' AFTER `priority` ;

ALTER TABLE `contrexx_module_calendar` ADD `organizerName` VARCHAR( 255 ) NOT NULL AFTER `placeMap` ,
ADD `organizerStreet` VARCHAR( 255 ) NOT NULL AFTER `organizerName` ,
ADD `organizerZip` VARCHAR( 255 ) NOT NULL AFTER `organizerStreet` ,
ADD `organizerMail` VARCHAR( 255 ) NOT NULL AFTER `organizerZip` ,
ADD `organizerLink` VARCHAR( 255 ) NOT NULL AFTER `organizerMail` ;

ALTER TABLE `contrexx_module_calendar` ADD `organizerPlace` VARCHAR( 255 ) NOT NULL AFTER `organizerZip` ;

ALTER TABLE `contrexx_module_calendar` ADD `placeCity` VARCHAR( 255 ) NOT NULL AFTER `placeZip` ;

CREATE TABLE `contrexx_module_calendar_access` (
`id` int( 11 ) unsigned NOT NULL AUTO_INCREMENT ,
`name` varchar( 64 ) NOT NULL default '',
`description` varchar( 255 ) NOT NULL default '',
`access_id` int( 11 ) unsigned NOT NULL default '0',
`type` enum( 'global', 'frontend', 'backend' ) NOT NULL default 'global',
PRIMARY KEY ( `id` )
) AUTO_INCREMENT =2;

INSERT INTO `contrexx_module_calendar_access` ( `id` , `name` , `description` , `access_id` , `type` )
VALUES (
NULL , 'showNote', 'Community Events einsehen', '116', 'frontend'
);


CREATE TABLE `contrexx_module_calendar_form_data` (
`id` INT( 7 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`note_id` INT( 7 ) NOT NULL ,
`time` INT( 14 ) NOT NULL ,
`host` VARCHAR( 255 ) NOT NULL ,
`ip_address` VARCHAR( 15 ) NOT NULL ,
`data` TEXT NOT NULL
) ENGINE = MYISAM ;

CREATE TABLE `contrexx_module_calendar_form_fields` (
`id` INT( 7 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`note_id` INT( 7 ) NOT NULL ,
`fields` TEXT NOT NULL ,
`type` TEXT NOT NULL ,
`required` TEXT NOT NULL
) ENGINE = MYISAM ;

ALTER TABLE `contrexx_module_calendar` ADD `registration` INT( 1 ) NOT NULL ,
ADD `groups` TEXT NOT NULL ,
ADD `all_groups` INT( 1 ) NOT NULL ;