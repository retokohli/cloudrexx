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