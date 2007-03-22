// sql modifications from v1.8 RC2

DROP TABLE IF EXISTS `astalavista_skins`;
CREATE TABLE `astalavista_skins` (
  `id` tinyint(2) unsigned NOT NULL auto_increment,
  `themesname` varchar(50) NOT NULL default '',
  `foldername` varchar(50) NOT NULL default '',
  `expert` int(1) NOT NULL default '1',
  UNIQUE KEY `id` (`id`),
  KEY `id_2` (`id`)
) TYPE=MyISAM;

UPDATE `astalavista_backend_areas` SET `uri` = 'index.php?cmd=skins' WHERE `area_id` = '21' LIMIT 1;

DROP TABLE IF EXISTS `astalavista_stats_browser`;
CREATE TABLE `astalavista_stats_browser` (
  `id` smallint(6) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
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

INSERT INTO `astalavista_stats_config` VALUES (1, 'make_statistics', '', 1);
INSERT INTO `astalavista_stats_config` VALUES (2, 'count_requests', '', 1);
INSERT INTO `astalavista_stats_config` VALUES (3, 'count_spiders', '', 1);
INSERT INTO `astalavista_stats_config` VALUES (4, 'count_browser', '', 1);
INSERT INTO `astalavista_stats_config` VALUES (5, 'count_operating_system', '', 1);
INSERT INTO `astalavista_stats_config` VALUES (6, 'reload_block_time', '86400', 1);
INSERT INTO `astalavista_stats_config` VALUES (7, 'online_timeout', '3600', 1);
INSERT INTO `astalavista_stats_config` VALUES (8, 'paging_limit', '25', 1);
INSERT INTO `astalavista_stats_config` VALUES (9, 'remove_requests', '0', 0);

DROP TABLE IF EXISTS `astalavista_stats_operatingsystem`;
CREATE TABLE `astalavista_stats_operatingsystem` (
  `id` smallint(6) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `astalavista_stats_requests`;
CREATE TABLE `astalavista_stats_requests` (
  `id` mediumint(9) unsigned NOT NULL auto_increment,
  `timestamp` int(11) default '0',
  `title` varchar(100) NOT NULL default '',
  `page` varchar(100) default NULL,
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

DROP TABLE IF EXISTS `astalavista_stats_spiders`;
CREATE TABLE `astalavista_stats_spiders` (
  `id` mediumint(9) unsigned NOT NULL auto_increment,
  `last_indexed` int(14) default NULL,
  `page` varchar(100) default NULL,
  `title` varchar(100) NOT NULL default '',
  `count` int(11) NOT NULL default '0',
  `spider_useragent` varchar(255) default NULL,
  `spider_ip` varchar(100) default NULL,
  `spider_host` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `astalavista_stats_spiders_summary`;
CREATE TABLE `astalavista_stats_spiders_summary` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(10) NOT NULL default '',
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


DROP TABLE IF EXISTS `astalavista_backend_areas`;
CREATE TABLE `astalavista_backend_areas` (
  `area_id` smallint(6) NOT NULL auto_increment,
  `parent_area_id` smallint(6) NOT NULL default '0',
  `type` enum('group','function','navigation')  default 'navigation',
  `area_name` varchar(100) default NULL,
  `is_active` tinyint(4) NOT NULL default '1',
  `uri` varchar(255)  NOT NULL default '',
  `target` varchar(50) NOT NULL default '_self',
  `module_id` smallint(6) NOT NULL default '0',
  `order_id` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`area_id`),
  KEY `area_name` (`area_name`)
) TYPE=MyISAM;

INSERT INTO `astalavista_backend_areas` VALUES (1, 0, 'group', 'TXT_CONTENT_MANAGEMENT', 1, '', '_self', 1, 0);
INSERT INTO `astalavista_backend_areas` VALUES (2, 0, 'group', 'TXT_MODULE', 1, '', '_self', 1, 2);
INSERT INTO `astalavista_backend_areas` VALUES (3, 0, 'group', 'TXT_ADMINISTRATION', 1, '', '_self', 1, 3);
INSERT INTO `astalavista_backend_areas` VALUES (4, 0, 'group', 'TXT_SERVER_INFO', 1, '', '_new', 1, 4);
INSERT INTO `astalavista_backend_areas` VALUES (5, 1, 'navigation', 'TXT_NEW_PAGE', 1, 'index.php?cmd=content&act=new', '_self', 1, 1);
INSERT INTO `astalavista_backend_areas` VALUES (6, 1, 'navigation', 'TXT_CONTENT_MANAGER', 1, 'index.php?cmd=content', '_self', 1, 2);
INSERT INTO `astalavista_backend_areas` VALUES (7, 1, 'navigation', 'TXT_MEDIA_MANAGER', 1, 'index.php?cmd=media', '_self', 1, 3);
INSERT INTO `astalavista_backend_areas` VALUES (8, 1, 'navigation', 'TXT_SITE_PREVIEW', 1, '../index.php', '_blank', 1, 5);
INSERT INTO `astalavista_backend_areas` VALUES (10, 1, 'navigation', 'TXT_NEWS_MANAGER', 1, 'index.php?cmd=news', '_self', 8, 4);
INSERT INTO `astalavista_backend_areas` VALUES (9, 2, 'navigation', 'TXT_GUESTBOOK', 1, 'index.php?cmd=guestbook', '_self', 10, 0);
INSERT INTO `astalavista_backend_areas` VALUES (11, 2, 'navigation', 'TXT_DOC_SYS_MANAGER', 1, 'index.php?cmd=docsys', '_self', 19, 0);
INSERT INTO `astalavista_backend_areas` VALUES (12, 2, 'navigation', 'TXT_THUMBNAIL_GALLERY', 1, 'index.php?cmd=gallery', '_self', 3, 0);
INSERT INTO `astalavista_backend_areas` VALUES (13, 2, 'navigation', 'TXT_SHOP', 1, 'index.php?cmd=shop', '_self', 16, 0);
INSERT INTO `astalavista_backend_areas` VALUES (14, 2, 'navigation', 'TXT_VOTING', 1, 'index.php?cmd=voting', '_self', 17, 0);
INSERT INTO `astalavista_backend_areas` VALUES (15, 2, 'navigation', 'TXT_FORUM', 0, 'index.php?cmd=forum', '_self', 20, 0);
INSERT INTO `astalavista_backend_areas` VALUES (16, 2, 'navigation', 'TXT_CALENDAR', 1, 'index.php?cmd=calendar', '_self', 21, 0);
INSERT INTO `astalavista_backend_areas` VALUES (25, 2, 'navigation', 'TXT_NEWSLETTER', 1, 'index.php?cmd=newsletter', '_self', 4, 0);
INSERT INTO `astalavista_backend_areas` VALUES (27, 2, 'navigation', 'TXT_NEWS_SYNDICATION', 1, 'index.php?cmd=feed', '_self', 22, 0);
INSERT INTO `astalavista_backend_areas` VALUES (17, 3, 'navigation', 'TXT_SYSTEM_SETTINGS', 1, 'index.php?cmd=settings', '_self', 1, 0);
INSERT INTO `astalavista_backend_areas` VALUES (18, 3, 'navigation', 'TXT_USER_ADMINISTRATION', 1, 'index.php?cmd=user', '_self', 1, 0);
INSERT INTO `astalavista_backend_areas` VALUES (19, 3, 'navigation', 'TXT_STATS', 1, 'index.php?cmd=stats', '_self', 1, 0);
INSERT INTO `astalavista_backend_areas` VALUES (20, 3, 'navigation', 'TXT_BACKUP', 1, 'index.php?cmd=backup', '_self', 1, 0);
INSERT INTO `astalavista_backend_areas` VALUES (21, 3, 'navigation', 'TXT_DESIGN_MANAGEMENT', 1, 'index.php?cmd=skins', '_self', 1, 0);
INSERT INTO `astalavista_backend_areas` VALUES (22, 3, 'navigation', 'TXT_LANGUAGE_SETTINGS', 1, 'index.php?cmd=language', '_self', 1, 0);
INSERT INTO `astalavista_backend_areas` VALUES (23, 3, 'navigation', 'TXT_MODULE_MANAGER', 1, 'index.php?cmd=modulemanager', '_self', 1, 0);
INSERT INTO `astalavista_backend_areas` VALUES (24, 4, 'navigation', 'TXT_SERVER_INFO', 1, 'index.php?cmd=server', '_self', 1, 0);
INSERT INTO `astalavista_backend_areas` VALUES (26, 6, 'function', 'TXT_DELETE_PAGES', 1, '', '_self', 0, 0);
INSERT INTO `astalavista_backend_areas` VALUES (35, 6, 'function', 'TXT_EDIT_PAGES', 1, '', '_self', 0, 0);
INSERT INTO `astalavista_backend_areas` VALUES (36, 6, 'function', 'TXT_ACCESS_CONTROL', 1, '', '_self', 0, 0);
INSERT INTO `astalavista_backend_areas` VALUES (37, 6, 'function', 'TXT_ADD_REPOSITORY', 1, '', '_self', 0, 0);
INSERT INTO `astalavista_backend_areas` VALUES (38, 7, 'function', 'TXT_MODIFY_MEDIA_FILES', 1, '', '_self', 0, 0);
INSERT INTO `astalavista_backend_areas` VALUES (39, 7, 'function', 'TXT_UPLOAD_MEDIA_FILES', 1, '', '_self', 0, 0);
INSERT INTO `astalavista_backend_areas` VALUES (28, 18, 'function', 'TXT_DELETE_USERS', 1, '', '_self', 0, 0);
INSERT INTO `astalavista_backend_areas` VALUES (29, 18, 'function', 'TXT_ADD_USERS', 1, '', '_self', 0, 0);
INSERT INTO `astalavista_backend_areas` VALUES (31, 18, 'function', 'TXT_EDIT_USERINFOS', 1, '', '_self', 0, 0);
INSERT INTO `astalavista_backend_areas` VALUES (30, 18, 'function', 'TXT_DELETE_GROUPS', 1, '', '_self', 0, 0);
INSERT INTO `astalavista_backend_areas` VALUES (33, 18, 'function', 'TXT_ADD_GROUPS', 1, '', '_self', 0, 0);
INSERT INTO `astalavista_backend_areas` VALUES (34, 18, 'function', 'TXT_EDIT_GROUPS', 1, '', '_self', 0, 0);
INSERT INTO `astalavista_backend_areas` VALUES (40, 19, 'function', 'TXT_RESET_STATS', 1, '', '_self', 0, 0);
INSERT INTO `astalavista_backend_areas` VALUES (41, 20, 'function', 'TXT_CREATE_BACKUPS', 1, '', '_self', 0, 0);
INSERT INTO `astalavista_backend_areas` VALUES (42, 20, 'function', 'TXT_RESTORE_BACKUP', 1, '', '_self', 0, 0);
INSERT INTO `astalavista_backend_areas` VALUES (43, 20, 'function', 'TXT_DELETE_BACKUPS', 1, '', '_self', 0, 0);
INSERT INTO `astalavista_backend_areas` VALUES (44, 20, 'function', 'TXT_DOWNLOAD_BACKUPS', 1, '', '_self', 0, 0);
INSERT INTO `astalavista_backend_areas` VALUES (45, 20, 'function', 'TXT_VIEW_BACKUPS', 1, '', '_self', 0, 0);
INSERT INTO `astalavista_backend_areas` VALUES (46, 21, 'function', 'TXT_ACTIVATE_SKINS', 1, '', '_self', 0, 0);
INSERT INTO `astalavista_backend_areas` VALUES (47, 21, 'function', 'TXT_EDIT_SKINS', 1, '', '_self', 0, 0);
INSERT INTO `astalavista_backend_areas` VALUES (48, 22, 'function', 'TXT_EDIT_LANGUAGE_SETTINGS', 1, '', '_self', 0, 0);
INSERT INTO `astalavista_backend_areas` VALUES (49, 22, 'function', 'TXT_DELETE_LANGUAGES', 1, '', '_self', 0, 0);
INSERT INTO `astalavista_backend_areas` VALUES (50, 22, 'function', 'TXT_LANGUAGE_SETTINGS', 1, '', '_self', 0, 0);
INSERT INTO `astalavista_backend_areas` VALUES (51, 23, 'function', 'TXT_REGISTER_MODULES', 1, '', '_self', 0, 0);
INSERT INTO `astalavista_backend_areas` VALUES (52, 23, 'function', 'TXT_INST_REMO_MODULES', 1, '', '_self', 0, 0);
INSERT INTO `astalavista_backend_areas` VALUES (53, 6, 'function', 'TXT_COPY_DELETE_SITES', 1, '', '_self', 0, 0);
        

DROP TABLE IF EXISTS `astalavista_modules`;
CREATE TABLE `astalavista_modules` (
  `id` tinyint(2) default NULL,
  `name` varchar(250) NOT NULL default '',
  `description_variable` varchar(50) NOT NULL default '',
  `status` set('y','n') NOT NULL default 'n',
  `add_on` tinyint(1) NOT NULL default '0',
  `is_core` tinyint(4) NOT NULL default '0',
  UNIQUE KEY `id` (`id`)
) TYPE=MyISAM;

-- 
-- Daten für Tabelle `astalavista_modules`
-- 

INSERT INTO `astalavista_modules` VALUES (0, '', '', 'n', 0, 1);
INSERT INTO `astalavista_modules` VALUES (1, 'core', 'TXT_CORE_MODULE_DESCRIPTION', 'y', 0, 1);
INSERT INTO `astalavista_modules` VALUES (2, 'stats', 'TXT_STATS_MODULE_DESCRIPTION', 'y', 1, 1);
INSERT INTO `astalavista_modules` VALUES (3, 'gallery', 'TXT_GALLERY_MODULE_DESCRIPTION', 'y', 1, 0);
INSERT INTO `astalavista_modules` VALUES (4, 'newsletter', 'TXT_NEWSLETTER_MODULE_DESCRIPTION', 'y', 1, 0);
INSERT INTO `astalavista_modules` VALUES (5, 'search', 'TXT_SEARCH_MODULE_DESCRIPTION', 'y', 1, 1);
INSERT INTO `astalavista_modules` VALUES (6, 'contact', 'TXT_CONTACT_MODULE_DESCRIPTION', 'y', 1, 1);
INSERT INTO `astalavista_modules` VALUES (8, 'news', 'TXT_NEWS_MODULE_DESCRIPTION', 'y', 1, 1);
INSERT INTO `astalavista_modules` VALUES (9, 'media', 'TXT_MEDIA_MODULE_DESCRIPTION', 'y', 1, 1);
INSERT INTO `astalavista_modules` VALUES (10, 'guestbook', 'TXT_GUESTBOOK_MODULE_DESCRIPTION', 'y', 1, 0);
INSERT INTO `astalavista_modules` VALUES (11, 'sitemap', 'TXT_SITEMAP_MODULE_DESCRIPTION', 'y', 1, 1);
INSERT INTO `astalavista_modules` VALUES (12, 'links', 'TXT_LINKS_MODULE_DESCRIPTION', 'n', 0, 0);
INSERT INTO `astalavista_modules` VALUES (13, 'ids', 'TXT_IDS_MODULE_DESCRIPTION', 'y', 1, 1);
INSERT INTO `astalavista_modules` VALUES (14, 'error', 'TXT_ERROR_MODULE_DESCRIPTION', 'y', 1, 1);
INSERT INTO `astalavista_modules` VALUES (15, 'home', 'TXT_HOME_MODULE_DESCRIPTION', 'y', 1, 1);
INSERT INTO `astalavista_modules` VALUES (16, 'shop', 'TXT_SHOP_MODULE_DESCRIPTION', 'n', 1, 0);
INSERT INTO `astalavista_modules` VALUES (17, 'voting', 'TXT_VOTING_MODULE_DESCRIPTION', 'y', 1, 0);
INSERT INTO `astalavista_modules` VALUES (18, 'login', 'TXT_LOGIN_MODULE_DESCRIPTION', 'y', 1, 1);
INSERT INTO `astalavista_modules` VALUES (19, 'docsys', 'TXT_DOC_SYS_MODULE_DESCRIPTION', 'n', 1, 0);
INSERT INTO `astalavista_modules` VALUES (20, 'forum', 'TXT_FORUM_MODULE_DESCRIPTION', 'n', 0, 0);
INSERT INTO `astalavista_modules` VALUES (21, 'calendar', 'TXT_CALENDAR_MODULE_DESCRIPTION', 'y', 1, 0);
INSERT INTO `astalavista_modules` VALUES (22, 'feed', 'TXT_FEED_MODULE_DESCRIPTION', 'y', 1, 0);
