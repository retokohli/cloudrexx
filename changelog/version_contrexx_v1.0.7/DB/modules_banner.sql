# BANNER
# -----------------------------------


DROP TABLE IF EXISTS `contrexx_module_banner_groups`;
CREATE TABLE `contrexx_module_banner_groups` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `placeholder_name` varchar(100) NOT NULL default '',
  `status` int(1) NOT NULL default '1',
  `is_deleted` set('0','1') NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

INSERT INTO `contrexx_module_banner_groups` VALUES (1, 'Full Banner - Header', '468 x 60 Pixel', '[[BANNER_GROUP_1]]', 1, '0');
INSERT INTO `contrexx_module_banner_groups` VALUES (2, 'Full Banner - Footer', '468 x 60 Pixel', '[[BANNER_GROUP_2]]', 1, '0');
INSERT INTO `contrexx_module_banner_groups` VALUES (3, 'Half Banner', '234 x 60 Pixel', '[[BANNER_GROUP_3]]', 1, '0');
INSERT INTO `contrexx_module_banner_groups` VALUES (4, 'Button 1', '120 x 90 Pixel', '[[BANNER_GROUP_3]]', 1, '0');
INSERT INTO `contrexx_module_banner_groups` VALUES (6, 'Square Pop-Up', '250 x 250 Pixel', '[[BANNER_GROUP_6]]', 1, '0');
INSERT INTO `contrexx_module_banner_groups` VALUES (5, 'Button 2', '120 x 60 Pixel', '[[BANNER_GROUP_5]]', 1, '0');
INSERT INTO `contrexx_module_banner_groups` VALUES (7, 'Skyscraper', '120 x 600 Pixel', '[[BANNER_GROUP_7]]', 1, '0');
INSERT INTO `contrexx_module_banner_groups` VALUES (8, 'Wide Skyscraper', '160 x 600 Pixel', '[[BANNER_GROUP_8]]', 1, '0');
INSERT INTO `contrexx_module_banner_groups` VALUES (9, 'Half Page Ad', '300 x 600 Pixel', '[[BANNER_GROUP_9]]', 1, '0');
INSERT INTO `contrexx_module_banner_groups` VALUES (10, 'Popup-Window', 'Werbung Aufklappfenster', '[[BANNER_GROUP_10]]', 1, '0');


DROP TABLE IF EXISTS `contrexx_module_banner_relations`;
CREATE TABLE `contrexx_module_banner_relations` (
  `banner_id` int(11) NOT NULL default '0',
  `group_id` tinyint(4) NOT NULL default '0',
  `page_id` int(11) NOT NULL default '0',
  `type` set('content','news','teaser') NOT NULL default 'content',
  KEY `banner_id` (`banner_id`,`group_id`,`page_id`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `contrexx_module_banner_settings`;
CREATE TABLE `contrexx_module_banner_settings` (
  `name` varchar(50) NOT NULL default '',
  `value` varchar(250) NOT NULL default '',
  KEY `name` (`name`)
) TYPE=MyISAM;


INSERT INTO `contrexx_module_banner_settings` VALUES ('news_banner', '0');
INSERT INTO `contrexx_module_banner_settings` VALUES ('content_banner', '1');
INSERT INTO `contrexx_module_banner_settings` VALUES ('teaser_banner', '1');



DROP TABLE IF EXISTS `contrexx_module_banner_system`;
CREATE TABLE `contrexx_module_banner_system` (
  `id` int(11) NOT NULL auto_increment,
  `parent_id` int(11) NOT NULL default '0',
  `name` varchar(150) NOT NULL default '',
  `banner_code` mediumtext NOT NULL,
  `status` int(1) NOT NULL default '1',
  `is_default` tinyint(2) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;
