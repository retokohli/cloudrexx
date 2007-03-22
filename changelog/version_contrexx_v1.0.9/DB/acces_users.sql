ALTER TABLE `contrexx_access_users` ADD `company` VARCHAR( 255 ) NOT NULL AFTER `webpage` ;

--birthday reminder
ALTER TABLE `contrexx_access_users` ADD `birthday` VARCHAR( 10 ) NOT NULL DEFAULT '0000-00-00' AFTER `mobile` , ADD `show_birthday` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `birthday` ;
ALTER TABLE `contrexx_access_users` ADD INDEX ( `birthday` ) ;
ALTER TABLE `contrexx_access_users` DROP `birthday` , DROP `show_birthday` ;


