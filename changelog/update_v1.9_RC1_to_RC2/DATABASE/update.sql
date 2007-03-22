DROP TABLE IF EXISTS `astalavista_modules`;
CREATE TABLE `astalavista_modules` (
  `id` tinyint(2) default NULL,
  `name` varchar(250) NOT NULL default '',
  `description_variable` varchar(50) NOT NULL default '',
  `status` set('y','n') NOT NULL default 'n',
  `is_required` tinyint(1) NOT NULL default '0',
  `is_core` tinyint(4) NOT NULL default '0',
  UNIQUE KEY `id` (`id`)
) TYPE=MyISAM;


INSERT INTO `astalavista_modules` VALUES (0, '', '', 'n', 0, 1);
INSERT INTO `astalavista_modules` VALUES (1, 'core', 'TXT_CORE_MODULE_DESCRIPTION', 'n', 1, 1);
INSERT INTO `astalavista_modules` VALUES (2, 'stats', 'TXT_STATS_MODULE_DESCRIPTION', 'n', 0, 1);
INSERT INTO `astalavista_modules` VALUES (3, 'gallery', 'TXT_GALLERY_MODULE_DESCRIPTION', 'y', 0, 0);
INSERT INTO `astalavista_modules` VALUES (4, 'newsletter', 'TXT_NEWSLETTER_MODULE_DESCRIPTION', 'y', 0, 0);
INSERT INTO `astalavista_modules` VALUES (5, 'search', 'TXT_SEARCH_MODULE_DESCRIPTION', 'y', 0, 1);
INSERT INTO `astalavista_modules` VALUES (6, 'contact', 'TXT_CONTACT_MODULE_DESCRIPTION', 'y', 0, 1);
INSERT INTO `astalavista_modules` VALUES (8, 'news', 'TXT_NEWS_MODULE_DESCRIPTION', 'y', 1, 1);
INSERT INTO `astalavista_modules` VALUES (9, 'media', 'TXT_MEDIA_MODULE_DESCRIPTION', 'y', 0, 1);
INSERT INTO `astalavista_modules` VALUES (10, 'guestbook', 'TXT_GUESTBOOK_MODULE_DESCRIPTION', 'y', 0, 0);
INSERT INTO `astalavista_modules` VALUES (11, 'sitemap', 'TXT_SITEMAP_MODULE_DESCRIPTION', 'y', 0, 1);
INSERT INTO `astalavista_modules` VALUES (12, 'links', 'TXT_LINKS_MODULE_DESCRIPTION', 'n', 0, 0);
INSERT INTO `astalavista_modules` VALUES (13, 'ids', 'TXT_IDS_MODULE_DESCRIPTION', 'y', 1, 1);
INSERT INTO `astalavista_modules` VALUES (14, 'error', 'TXT_ERROR_MODULE_DESCRIPTION', 'y', 1, 1);
INSERT INTO `astalavista_modules` VALUES (15, 'home', 'TXT_HOME_MODULE_DESCRIPTION', 'y', 1, 1);
INSERT INTO `astalavista_modules` VALUES (16, 'shop', 'TXT_SHOP_MODULE_DESCRIPTION', 'y', 0, 0);
INSERT INTO `astalavista_modules` VALUES (17, 'voting', 'TXT_VOTING_MODULE_DESCRIPTION', 'y', 0, 0);
INSERT INTO `astalavista_modules` VALUES (18, 'login', 'TXT_LOGIN_MODULE_DESCRIPTION', 'y', 1, 1);
INSERT INTO `astalavista_modules` VALUES (19, 'docsys', 'TXT_DOC_SYS_MODULE_DESCRIPTION', 'y', 0, 0);
INSERT INTO `astalavista_modules` VALUES (20, 'forum', 'TXT_FORUM_MODULE_DESCRIPTION', 'n', 0, 0);
INSERT INTO `astalavista_modules` VALUES (21, 'calendar', 'TXT_CALENDAR_MODULE_DESCRIPTION', 'y', 0, 0);
INSERT INTO `astalavista_modules` VALUES (22, 'feed', 'TXT_FEED_MODULE_DESCRIPTION', 'y', 0, 0);


INSERT INTO `astalavista_backend_areas` VALUES (54, 4, 'navigation', 'TXT_NETWORK_TOOLS', 1, 'index.php?cmd=nettools', '_self', 0, 0);


DELETE FROM `astalavista_settings` WHERE `setid` = '8' LIMIT 1;
DELETE FROM `astalavista_settings` WHERE `setid` = '9' LIMIT 1;


DROP TABLE IF EXISTS `astalavista_yearstats`; 

DROP TABLE IF EXISTS `astalavista_stats`;
CREATE TABLE `astalavista_stats` (
  `id` mediumint(9) unsigned NOT NULL auto_increment,
  `timestamp` int(14) default NULL,
  `page` varchar(100) default NULL,
  `refer` text,
  `useragent` varchar(100) default NULL,
  `userlanguage` varchar(16) default NULL,
  `clientip` varchar(200) default NULL,
  `clienthost` varchar(255) default NULL,
  `proxyhost` varchar(255) NOT NULL default '',
  `proxyip` varchar(50) NOT NULL default '',
  `sid` text NOT NULL,
  `proxyid` int(11) default NULL,
  `proxyuseragent` varchar(50) NOT NULL default '',
  `javascript_enabled` tinyint(4) NOT NULL default '0',
  `screen_resolution` varchar(10) NOT NULL default '',
  `color_depth` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;


DROP TABLE IF EXISTS `astalavista_stats_browser`;
CREATE TABLE `astalavista_stats_browser` (
  `id` smallint(6) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;



DROP TABLE IF EXISTS `astalavista_stats_colourdepth`;
CREATE TABLE `astalavista_stats_colourdepth` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `depth` tinyint(3) unsigned NOT NULL default '0',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;


DROP TABLE IF EXISTS `astalavista_stats_config`;
CREATE TABLE `astalavista_stats_config` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(64) NOT NULL default '',
  `value` varchar(255) NOT NULL default '',
  `status` int(1) default '1',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;


INSERT INTO `astalavista_stats_config` VALUES (1, 'reload_block_time', '86400', 1);
INSERT INTO `astalavista_stats_config` VALUES (2, 'online_timeout', '60', 1);
INSERT INTO `astalavista_stats_config` VALUES (3, 'paging_limit', '100', 1);
INSERT INTO `astalavista_stats_config` VALUES (4, 'count_browser', '', 1);
INSERT INTO `astalavista_stats_config` VALUES (5, 'count_operating_system', '', 1);
INSERT INTO `astalavista_stats_config` VALUES (6, 'make_statistics', '', 1);
INSERT INTO `astalavista_stats_config` VALUES (7, 'count_spiders', '', 1);
INSERT INTO `astalavista_stats_config` VALUES (10, 'remove_requests', '86400', 0);
INSERT INTO `astalavista_stats_config` VALUES (9, 'count_requests', '', 0);
INSERT INTO `astalavista_stats_config` VALUES (11, 'count_search_terms', '', 1);
INSERT INTO `astalavista_stats_config` VALUES (12, 'count_screen_resolution', '', 1);
INSERT INTO `astalavista_stats_config` VALUES (13, 'count_colour_depth', '', 1);
INSERT INTO `astalavista_stats_config` VALUES (14, 'count_javascript', '', 1);
INSERT INTO `astalavista_stats_config` VALUES (15, 'count_referer', '', 1);
INSERT INTO `astalavista_stats_config` VALUES (16, 'count_hostname', '', 1);
INSERT INTO `astalavista_stats_config` VALUES (17, 'count_country', '', 1);



DROP TABLE IF EXISTS `astalavista_stats_country`;
CREATE TABLE `astalavista_stats_country` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `country` varchar(100) NOT NULL default '',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;


DROP TABLE IF EXISTS `astalavista_stats_hostname`;
CREATE TABLE `astalavista_stats_hostname` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `hostname` varchar(255) NOT NULL default '',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;


DROP TABLE IF EXISTS `astalavista_stats_javascript`;
CREATE TABLE `astalavista_stats_javascript` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `support` enum('0','1') default '0',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;


INSERT INTO `astalavista_stats_javascript` VALUES (1, '0', 0);
INSERT INTO `astalavista_stats_javascript` VALUES (2, '1', 2);


DROP TABLE IF EXISTS `astalavista_stats_operatingsystem`;
CREATE TABLE `astalavista_stats_operatingsystem` (
  `id` smallint(6) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;


DROP TABLE IF EXISTS `astalavista_stats_referer`;
CREATE TABLE `astalavista_stats_referer` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `uri` varchar(255) NOT NULL default '',
  `timestamp` int(11) unsigned NOT NULL default '0',
  `count` mediumint(8) unsigned NOT NULL default '0',
  `sid` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;


DROP TABLE IF EXISTS `astalavista_stats_requests`;
CREATE TABLE `astalavista_stats_requests` (
  `id` mediumint(9) unsigned NOT NULL auto_increment,
  `timestamp` int(11) default '0',
  `pageId` smallint(6) unsigned NOT NULL default '0',
  `page` varchar(255) default NULL,
  `visits` mediumint(9) unsigned NOT NULL default '0',
  `sid` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;


DROP TABLE IF EXISTS `astalavista_stats_requests_summary`;
CREATE TABLE `astalavista_stats_requests_summary` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `type` varchar(10) NOT NULL default '',
  `timestamp` int(11) NOT NULL default '0',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;



DROP TABLE IF EXISTS `astalavista_stats_screenresolution`;
CREATE TABLE `astalavista_stats_screenresolution` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `resolution` varchar(11) NOT NULL default '',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;


DROP TABLE IF EXISTS `astalavista_stats_search`;
CREATE TABLE `astalavista_stats_search` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `count` int(10) unsigned NOT NULL default '0',
  `sid` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;


DROP TABLE IF EXISTS `astalavista_stats_spiders`;
CREATE TABLE `astalavista_stats_spiders` (
  `id` mediumint(9) unsigned NOT NULL auto_increment,
  `last_indexed` int(14) default NULL,
  `page` varchar(100) default NULL,
  `pageId` mediumint(6) unsigned NOT NULL default '0',
  `count` int(11) NOT NULL default '0',
  `spider_useragent` varchar(255) default NULL,
  `spider_ip` varchar(100) default NULL,
  `spider_host` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;


DROP TABLE IF EXISTS `astalavista_stats_spiders_summary`;
CREATE TABLE `astalavista_stats_spiders_summary` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `timestamp` int(11) NOT NULL default '0',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;


DROP TABLE IF EXISTS `astalavista_stats_visitors`;
CREATE TABLE `astalavista_stats_visitors` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `sid` varchar(32) NOT NULL default '',
  `timestamp` int(11) NOT NULL default '0',
  `client_ip` varchar(100) default NULL,
  `client_host` varchar(255) default NULL,
  `client_useragent` varchar(255) default NULL,
  `proxy_ip` varchar(100) default NULL,
  `proxy_host` varchar(255) default NULL,
  `proxy_useragent` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;


DROP TABLE IF EXISTS `astalavista_stats_visitors_summary`;
CREATE TABLE `astalavista_stats_visitors_summary` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `type` varchar(10) NOT NULL default '',
  `timestamp` int(11) NOT NULL default '0',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;
