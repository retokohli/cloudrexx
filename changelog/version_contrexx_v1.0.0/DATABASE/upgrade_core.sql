// sql modifications from v1.9 RC2


UPDATE `astalavista_backend_areas` SET `area_name` = 'TXT_SYSTEM_INFO' WHERE `area_id` = '4' LIMIT 1;
INSERT INTO `astalavista_backend_areas` ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` ) VALUES ('', '0', 'group', 'TXT_HELP_SUPPORT', '1', '', '_self', '1', '5');
INSERT INTO `astalavista_backend_areas` ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` ) VALUES ('', '55', 'navigation', 'TXT_SUPPORT_FORUM', '1', 'http://www.contrexx.com/forum/', '_blank', '1', '1');

DELETE FROM `astalavista_settings` WHERE `setid` = '6' LIMIT 1;
DELETE FROM `astalavista_settings` WHERE `setid` = '7' LIMIT 1;
DELETE FROM `astalavista_settings` WHERE `setid` = '30' LIMIT 1;
UPDATE `astalavista_settings` SET `setvalue` = '3600' WHERE `setid` = '34' LIMIT 1;


INSERT INTO `astalavista_settings` ( `setid` , `setname` , `setvalue` , `setmodule` ) VALUES ('1', 'contactFormEmail2', '', '1'), ('2', 'contactFormEmail3', '', '1');

ALTER TABLE `astalavista_stats_search` ADD `external` ENUM( '0', '1' ) NOT NULL ;
INSERT INTO `astalavista_stats_config` ( `id` , `name` , `value` , `status` ) VALUES ('', 'paging_limit_visitor_details', '100', '1');
INSERT INTO `astalavista_backend_areas` ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` ) VALUES ('', '4', 'navigation', 'TXT_SYSTEM_UPDATE', '1', 'index.php?cmd=systemUpdate', '_self', '0', '0');


