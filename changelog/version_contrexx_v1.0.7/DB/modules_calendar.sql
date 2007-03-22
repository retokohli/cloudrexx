ALTER TABLE `contrexx_module_calendar` DROP `date` ,DROP `time` ,DROP `end_date` ,DROP `end_time` ,DROP `sort` ;
	
ALTER TABLE `contrexx_module_calendar` ADD `active` TINYINT( 1 ) DEFAULT '1' NOT NULL AFTER `id` ;
ALTER TABLE `contrexx_module_calendar` ADD `startdate` INT( 14 ) NOT NULL AFTER `catid` ,ADD `enddate` INT( 14 ) NOT NULL AFTER `startdate` ;
ALTER TABLE `contrexx_module_calendar` ADD FULLTEXT (name, `comment`, place);

INSERT INTO `contrexx_settings` ( `setid` , `setname` , `setvalue` , `setmodule` ) VALUES ('', 'calendarheadlinescat', '0', '21');
INSERT INTO `contrexx_settings` ( `setid` , `setname` , `setvalue` , `setmodule` ) VALUES ('', 'calendardefaultcount', '10', '21');