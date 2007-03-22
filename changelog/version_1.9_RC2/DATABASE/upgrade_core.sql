// sql modifications from v1.9 RC1

DROP TABLE IF EXISTS `astalavista_pagecounter`;
DROP TABLE IF EXISTS `astalavista_searchbots`;

DROP TABLE IF EXISTS `astalavista_module_news_settings`;
CREATE TABLE `astalavista_module_news_settings` (
  `name` varchar(50) NOT NULL default '',
  `value` varchar(250) NOT NULL default '',
  KEY `name` (`name`)
) TYPE=MyISAM;


INSERT INTO `astalavista_module_news_settings` VALUES ('news_feed_description', 'Informationen rund um das Contrexx® Open Source CMS');
INSERT INTO `astalavista_module_news_settings` VALUES ('news_feed_status', '0');
INSERT INTO `astalavista_module_news_settings` VALUES ('news_feed_title', 'Contrexx® Open Source CMS');
INSERT INTO `astalavista_module_news_settings` VALUES ('news_headlines_limit', '10');
INSERT INTO `astalavista_module_news_settings` VALUES ('news_settings_activated', '0');

DELETE FROM `astalavista_settings` WHERE `setid` = '6' LIMIT 1;
DELETE FROM `astalavista_settings` WHERE `setid` = '7' LIMIT 1;
DELETE FROM `astalavista_settings` WHERE `setid` = '30' LIMIT 1;
UPDATE `astalavista_settings` SET `setvalue` = '3600' WHERE `setid` = '34' LIMIT 1;

ALTER TABLE `astalavista_content` ADD `css_name` VARCHAR( 100 ) NOT NULL AFTER `metarobots` ;




