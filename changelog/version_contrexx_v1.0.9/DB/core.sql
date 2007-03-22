INSERT INTO `contrexx_settings` VALUES (5, 'spamKeywords', 'sex, viagra', 0);
INSERT INTO `contrexx_settings` VALUES (49, 'directoryHomeContent', '1', 12);
INSERT INTO `contrexx_settings` VALUES (50, 'cacheEnabled', 'off', 1);
INSERT INTO `contrexx_settings` VALUES (51, 'coreGlobalPageTitle', 'Contrexx CMS', 1);
INSERT INTO `contrexx_settings` VALUES (52, 'cacheExpiration', 86400, 1);
INSERT INTO `contrexx_settings` VALUES (53, 'domainUrl', 'dev.contrexx.org', 1);
INSERT INTO `contrexx_settings` VALUES (54, 'googleSitemapStatus', 'off', 1);
INSERT INTO `contrexx_settings` VALUES (55, 'systemStatus', 'on', 1);

UPDATE `contrexx_settings` SET `setmodule`=1 WHERE `setmodule`=0;

INSERT INTO `contrexx_modules` ( `id` , `name` , `description_variable` , `status` , `is_required` , `is_core` )VALUES ('32', 'nettools', 'TXT_NETTOOLS_MODULE_DESCRIPTION', 'y', '0', '1');

UPDATE `contrexx_backend_areas` SET `parent_area_id` = '1', `order_id` = '7' WHERE `area_id` =62 LIMIT 1;

UPDATE `contrexx_backend_areas` SET `parent_area_id` = '1', `order_id` = '3' WHERE `area_id` =76 LIMIT 1;

INSERT INTO `contrexx_settings` ( `setid` , `setname` , `setvalue` , `setmodule` ) VALUES ('', 'searchVisibleContentOnly', 'off', '1');

ALTER TABLE `contrexx_content_logfile` ADD INDEX ( `history_id` );

UPDATE `contrexx_backend_areas` SET `order_id` = '10' WHERE `uri` = '../index.php' AND `area_name` = 'TXT_SITE_PREVIEW' LIMIT 1 ;

UPDATE `contrexx_backend_areas` SET `order_id` = '5' WHERE `uri` = 'index.php?cmd=media&amp;archive=content' AND `area_name` = 'TXT_IMAGE_ADMINISTRATION' LIMIT 1 ;

UPDATE `contrexx_backend_areas` SET `order_id` = '8' WHERE `uri` = 'index.php?cmd=banner' AND `area_name` = 'TXT_BANNER_ADMINISTRATION' LIMIT 1 ;
UPDATE `contrexx_backend_areas` SET `order_id` = '9' WHERE `uri` = 'index.php?cmd=block' AND `area_name` = 'TXT_BLOCK_SYSTEM' LIMIT 1 ;

UPDATE `contrexx_backend_areas` SET `order_id` = '9' WHERE `uri` = 'index.php?cmd=directory' AND `area_name` = 'TXT_LINKS_MODULE_DESCRIPTION' LIMIT 1 ;
UPDATE `contrexx_backend_areas` SET `order_id` = '10' WHERE `uri` = 'index.php?cmd=recommend' AND `area_name` = 'TXT_RECOMMEND' LIMIT 1 ;
UPDATE `contrexx_backend_areas` SET `order_id` = '11' WHERE `uri` = 'index.php?cmd=community' AND `area_name` = 'TXT_COMMUNITY' LIMIT 1 ;
UPDATE `contrexx_backend_areas` SET `order_id` = '12' WHERE `uri` = 'index.php?cmd=reservation' AND `area_name` = 'TXT_RESERVATION' LIMIT 1 ;
UPDATE `contrexx_backend_areas` SET `order_id` = '13' WHERE `uri` = 'index.php?cmd=memberdir' AND `area_name` = 'TXT_MEMBERDIR' LIMIT 1 ;
UPDATE `contrexx_backend_areas` SET `order_id` = '14' WHERE `uri` = 'index.php?cmd=market' AND `area_name` = 'TXT_MARKET_MODULE_DESCRIPTION' LIMIT 1 ;
UPDATE `contrexx_backend_areas` SET `order_id` = '15' WHERE `uri` = 'index.php?cmd=livecam' AND `area_name` = 'TXT_LIVECAM' LIMIT 1 ;
UPDATE `contrexx_backend_areas` SET `order_id` = '16' WHERE `uri` = 'index.php?cmd=forum' AND `area_name` = 'TXT_FORUM' LIMIT 1 ;

UPDATE `contrexx_backend_areas` SET `order_id` = '1' WHERE `uri` = 'index.php?cmd=user' AND `area_name` = 'TXT_USER_ADMINISTRATION' LIMIT 1 ;
UPDATE `contrexx_backend_areas` SET `order_id` = '2' WHERE `uri` = 'index.php?cmd=backup' AND `area_name` = 'TXT_BACKUP' LIMIT 1 ;
UPDATE `contrexx_backend_areas` SET `order_id` = '3' WHERE `uri` = 'index.php?cmd=skins' AND `area_name` = 'TXT_DESIGN_MANAGEMENT' LIMIT 1 ;
UPDATE `contrexx_backend_areas` SET `order_id` = '4' WHERE `uri` = 'index.php?cmd=language' AND `area_name` = 'TXT_LANGUAGE_SETTINGS' LIMIT 1 ;
UPDATE `contrexx_backend_areas` SET `order_id` = '5' WHERE `uri` = 'index.php?cmd=modulemanager' AND `area_name` = 'TXT_MODULE_MANAGER' LIMIT 1 ;
UPDATE `contrexx_backend_areas` SET `order_id` = '6' WHERE `uri` = 'index.php?cmd=stats' AND `area_name` = 'TXT_STATS' LIMIT 1 ;
UPDATE `contrexx_backend_areas` SET `order_id` = '7' WHERE `uri` = 'index.php?cmd=settings' AND `area_name` = 'TXT_SYSTEM_SETTINGS' LIMIT 1 ;
UPDATE `contrexx_backend_areas` SET `order_id` = '8' WHERE `uri` = 'index.php?cmd=development' AND `area_name` = 'TXT_SYSTEM_DEVELOPMENT' LIMIT 1 ;

UPDATE `contrexx_backend_areas` SET `order_id` = '1' WHERE `uri` = 'index.php?cmd=server' AND `area_name` = 'TXT_SERVER_INFO' LIMIT 1 ;
UPDATE `contrexx_backend_areas` SET `order_id` = '2' WHERE `uri` = 'index.php?cmd=nettools' AND `area_name` = 'TXT_NETWORK_TOOLS' LIMIT 1 ;
UPDATE `contrexx_backend_areas` SET `order_id` = '3' WHERE `uri` = 'index.php?cmd=systemUpdate' AND `area_name` = 'TXT_SYSTEM_UPDATE' LIMIT 1 ;

UPDATE `contrexx_backend_areas` SET `access_id` = '86' WHERE `uri` = 'index.php?cmd=reservation' AND `area_name` = 'TXT_RESERVATION' LIMIT 1 ;