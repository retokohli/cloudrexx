-- phpMyAdmin SQL Dump
-- version 2.6.1-pl3
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Erstellungszeit: 23. Dezember 2005 um 18:35
-- Server Version: 3.23.58
-- PHP-Version: 4.3.10
-- 
-- Datenbank: `contrexx`
-- 

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_access_group_dynamic_ids`
-- 

DROP TABLE IF EXISTS `contrexx_access_group_dynamic_ids`;
CREATE TABLE IF NOT EXISTS `contrexx_access_group_dynamic_ids` (
  `access_id` int(11) unsigned NOT NULL default '0',
  `group_id` int(11) unsigned NOT NULL default '0'
) TYPE=MyISAM;

-- 
-- Daten für Tabelle `contrexx_access_group_dynamic_ids`
-- 

INSERT INTO `contrexx_access_group_dynamic_ids` VALUES (26, 46);
INSERT INTO `contrexx_access_group_dynamic_ids` VALUES (27, 108);
INSERT INTO `contrexx_access_group_dynamic_ids` VALUES (26, 4);
INSERT INTO `contrexx_access_group_dynamic_ids` VALUES (26, 3);
INSERT INTO `contrexx_access_group_dynamic_ids` VALUES (26, 2);
INSERT INTO `contrexx_access_group_dynamic_ids` VALUES (26, 1);
INSERT INTO `contrexx_access_group_dynamic_ids` VALUES (28, 108);
INSERT INTO `contrexx_access_group_dynamic_ids` VALUES (29, 4);
INSERT INTO `contrexx_access_group_dynamic_ids` VALUES (29, 2);
INSERT INTO `contrexx_access_group_dynamic_ids` VALUES (29, 1);
INSERT INTO `contrexx_access_group_dynamic_ids` VALUES (30, 108);
INSERT INTO `contrexx_access_group_dynamic_ids` VALUES (31, 108);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_access_group_static_ids`
-- 

DROP TABLE IF EXISTS `contrexx_access_group_static_ids`;
CREATE TABLE IF NOT EXISTS `contrexx_access_group_static_ids` (
  `access_id` int(11) unsigned NOT NULL default '0',
  `group_id` int(11) unsigned NOT NULL default '0'
) TYPE=MyISAM;

-- 
-- Daten für Tabelle `contrexx_access_group_static_ids`
-- 

INSERT INTO `contrexx_access_group_static_ids` VALUES (1, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (10, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (8, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (7, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (38, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (39, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (6, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (53, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (37, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (36, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (35, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (26, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (5, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (2, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (27, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (25, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (16, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (14, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (13, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (12, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (11, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (9, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (3, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (23, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (51, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (52, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (22, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (48, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (49, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (50, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (21, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (46, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (47, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (20, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (41, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (45, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (44, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (43, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (42, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (19, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (40, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (18, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (34, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (33, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (30, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (31, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (29, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (17, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (4, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (24, 1);
INSERT INTO `contrexx_access_group_static_ids` VALUES (1, 46);
INSERT INTO `contrexx_access_group_static_ids` VALUES (10, 46);
INSERT INTO `contrexx_access_group_static_ids` VALUES (8, 46);
INSERT INTO `contrexx_access_group_static_ids` VALUES (7, 46);
INSERT INTO `contrexx_access_group_static_ids` VALUES (6, 46);
INSERT INTO `contrexx_access_group_static_ids` VALUES (35, 46);
INSERT INTO `contrexx_access_group_static_ids` VALUES (77, 46);
INSERT INTO `contrexx_access_group_static_ids` VALUES (5, 46);
INSERT INTO `contrexx_access_group_static_ids` VALUES (2, 46);
INSERT INTO `contrexx_access_group_static_ids` VALUES (27, 46);
INSERT INTO `contrexx_access_group_static_ids` VALUES (25, 46);
INSERT INTO `contrexx_access_group_static_ids` VALUES (16, 46);
INSERT INTO `contrexx_access_group_static_ids` VALUES (14, 46);
INSERT INTO `contrexx_access_group_static_ids` VALUES (13, 46);
INSERT INTO `contrexx_access_group_static_ids` VALUES (12, 46);
INSERT INTO `contrexx_access_group_static_ids` VALUES (11, 46);
INSERT INTO `contrexx_access_group_static_ids` VALUES (9, 46);
INSERT INTO `contrexx_access_group_static_ids` VALUES (3, 46);
INSERT INTO `contrexx_access_group_static_ids` VALUES (23, 46);
INSERT INTO `contrexx_access_group_static_ids` VALUES (22, 46);
INSERT INTO `contrexx_access_group_static_ids` VALUES (21, 46);
INSERT INTO `contrexx_access_group_static_ids` VALUES (18, 46);
INSERT INTO `contrexx_access_group_static_ids` VALUES (4, 46);
INSERT INTO `contrexx_access_group_static_ids` VALUES (75, 46);
INSERT INTO `contrexx_access_group_static_ids` VALUES (1, 4);
INSERT INTO `contrexx_access_group_static_ids` VALUES (5, 4);
INSERT INTO `contrexx_access_group_static_ids` VALUES (6, 4);
INSERT INTO `contrexx_access_group_static_ids` VALUES (26, 4);
INSERT INTO `contrexx_access_group_static_ids` VALUES (35, 4);
INSERT INTO `contrexx_access_group_static_ids` VALUES (36, 4);
INSERT INTO `contrexx_access_group_static_ids` VALUES (37, 4);
INSERT INTO `contrexx_access_group_static_ids` VALUES (53, 4);
INSERT INTO `contrexx_access_group_static_ids` VALUES (7, 4);
INSERT INTO `contrexx_access_group_static_ids` VALUES (38, 4);
INSERT INTO `contrexx_access_group_static_ids` VALUES (39, 4);
INSERT INTO `contrexx_access_group_static_ids` VALUES (8, 4);
INSERT INTO `contrexx_access_group_static_ids` VALUES (55, 4);
INSERT INTO `contrexx_access_group_static_ids` VALUES (56, 4);
INSERT INTO `contrexx_access_group_static_ids` VALUES (66, 4);
INSERT INTO `contrexx_access_group_static_ids` VALUES (67, 4);
INSERT INTO `contrexx_access_group_static_ids` VALUES (12, 4);
INSERT INTO `contrexx_access_group_static_ids` VALUES (2, 4);
INSERT INTO `contrexx_access_group_static_ids` VALUES (65, 4);
INSERT INTO `contrexx_access_group_static_ids` VALUES (32, 46);
INSERT INTO `contrexx_access_group_static_ids` VALUES (84, 46);
INSERT INTO `contrexx_access_group_static_ids` VALUES (85, 46);
INSERT INTO `contrexx_access_group_static_ids` VALUES (65, 46);
INSERT INTO `contrexx_access_group_static_ids` VALUES (66, 46);
INSERT INTO `contrexx_access_group_static_ids` VALUES (67, 46);
INSERT INTO `contrexx_access_group_static_ids` VALUES (68, 46);
INSERT INTO `contrexx_access_group_static_ids` VALUES (69, 46);
INSERT INTO `contrexx_access_group_static_ids` VALUES (19, 46);
INSERT INTO `contrexx_access_group_static_ids` VALUES (20, 46);
INSERT INTO `contrexx_access_group_static_ids` VALUES (54, 46);
INSERT INTO `contrexx_access_group_static_ids` VALUES (55, 46);
INSERT INTO `contrexx_access_group_static_ids` VALUES (56, 46);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_access_user_groups`
-- 

DROP TABLE IF EXISTS `contrexx_access_user_groups`;
CREATE TABLE IF NOT EXISTS `contrexx_access_user_groups` (
  `group_id` smallint(6) NOT NULL auto_increment,
  `group_name` varchar(100) NOT NULL default '',
  `group_description` varchar(255) NOT NULL default '',
  `is_active` tinyint(4) NOT NULL default '1',
  `type` enum('frontend','backend') NOT NULL default 'frontend',
  PRIMARY KEY  (`group_id`)
) TYPE=MyISAM AUTO_INCREMENT=112 ;

-- 
-- Daten für Tabelle `contrexx_access_user_groups`
-- 

INSERT INTO `contrexx_access_user_groups` VALUES (1, 'Standard Administrator', '-', 1, 'backend');
INSERT INTO `contrexx_access_user_groups` VALUES (46, 'Demo', 'Demo Benutzergruppe ohne Schreibrechte', 1, 'backend');
INSERT INTO `contrexx_access_user_groups` VALUES (108, 'Community', 'Community users', 1, 'frontend');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_access_users`
-- 

DROP TABLE IF EXISTS `contrexx_access_users`;
CREATE TABLE IF NOT EXISTS `contrexx_access_users` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `levelid` tinyint(2) unsigned NOT NULL default '1',
  `is_admin` tinyint(4) NOT NULL default '0',
  `username` varchar(40) default NULL,
  `password` varchar(32) default NULL,
  `regdate` date default '2003-00-00',
  `email` varchar(255) default NULL,
  `firstname` varchar(150) default NULL,
  `lastname` varchar(150) default NULL,
  `residence` varchar(255) NOT NULL default '',
  `profession` varchar(255) NOT NULL default '',
  `interests` varchar(255) NOT NULL default '',
  `webpage` varchar(255) NOT NULL default '',
  `langId` smallint(2) NOT NULL default '0',
  `active` tinyint(1) NOT NULL default '0',
  `groups` varchar(50) NOT NULL default '0',
  `restore_key` varchar(32) NOT NULL default '',
  `restore_key_time` int(14) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `username` (`username`)
) TYPE=MyISAM PACK_KEYS=0 AUTO_INCREMENT=2 ;

-- 
-- Daten für Tabelle `contrexx_access_users`
-- 

INSERT INTO `contrexx_access_users` VALUES (1, 6, 1, 'system', '', '2003-00-00', '', 'CMS', 'System Benutzer', '', '', '', '', 1, 0, '1,2,3,4', '', 0);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_backend_areas`
-- 

DROP TABLE IF EXISTS `contrexx_backend_areas`;
CREATE TABLE IF NOT EXISTS `contrexx_backend_areas` (
  `area_id` smallint(6) NOT NULL auto_increment,
  `parent_area_id` smallint(6) NOT NULL default '0',
  `type` enum('group','function','navigation') default 'navigation',
  `area_name` varchar(100) default NULL,
  `is_active` tinyint(4) NOT NULL default '1',
  `uri` varchar(255) NOT NULL default '',
  `target` varchar(50) NOT NULL default '_self',
  `module_id` smallint(6) NOT NULL default '0',
  `order_id` smallint(6) NOT NULL default '0',
  `access_id` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`area_id`),
  KEY `area_name` (`area_name`)
) TYPE=MyISAM AUTO_INCREMENT=92 ;

-- 
-- Daten für Tabelle `contrexx_backend_areas`
-- 

INSERT INTO `contrexx_backend_areas` VALUES (1, 0, 'group', 'TXT_CONTENT_MANAGEMENT', 1, '', '_self', 1, 0, 1);
INSERT INTO `contrexx_backend_areas` VALUES (2, 0, 'group', 'TXT_MODULE', 1, '', '_self', 1, 2, 2);
INSERT INTO `contrexx_backend_areas` VALUES (3, 0, 'group', 'TXT_ADMINISTRATION', 1, '', '_self', 1, 3, 3);
INSERT INTO `contrexx_backend_areas` VALUES (4, 0, 'group', 'TXT_SYSTEM_INFO', 1, '', '_new', 1, 4, 4);
INSERT INTO `contrexx_backend_areas` VALUES (5, 1, 'navigation', 'TXT_NEW_PAGE', 1, 'index.php?cmd=content&amp;act=new', '_self', 1, 1, 5);
INSERT INTO `contrexx_backend_areas` VALUES (6, 1, 'navigation', 'TXT_CONTENT_MANAGER', 1, 'index.php?cmd=content', '_self', 1, 2, 6);
INSERT INTO `contrexx_backend_areas` VALUES (7, 1, 'navigation', 'TXT_MEDIA_MANAGER', 1, 'index.php?cmd=media&amp;archive=archive1', '_self', 1, 4, 7);
INSERT INTO `contrexx_backend_areas` VALUES (8, 1, 'navigation', 'TXT_SITE_PREVIEW', 1, '../index.php', '_blank', 1, 8, 8);
INSERT INTO `contrexx_backend_areas` VALUES (10, 1, 'navigation', 'TXT_NEWS_MANAGER', 1, 'index.php?cmd=news', '_self', 8, 6, 10);
INSERT INTO `contrexx_backend_areas` VALUES (9, 2, 'navigation', 'TXT_GUESTBOOK', 1, 'index.php?cmd=guestbook', '_self', 10, 0, 9);
INSERT INTO `contrexx_backend_areas` VALUES (11, 2, 'navigation', 'TXT_DOC_SYS_MANAGER', 1, 'index.php?cmd=docsys', '_self', 19, 0, 11);
INSERT INTO `contrexx_backend_areas` VALUES (12, 2, 'navigation', 'TXT_THUMBNAIL_GALLERY', 1, 'index.php?cmd=gallery', '_self', 3, 0, 12);
INSERT INTO `contrexx_backend_areas` VALUES (13, 2, 'navigation', 'TXT_SHOP', 1, 'index.php?cmd=shop', '_self', 16, 0, 13);
INSERT INTO `contrexx_backend_areas` VALUES (14, 2, 'navigation', 'TXT_VOTING', 1, 'index.php?cmd=voting', '_self', 17, 0, 14);
INSERT INTO `contrexx_backend_areas` VALUES (16, 2, 'navigation', 'TXT_CALENDAR', 1, 'index.php?cmd=calendar', '_self', 21, 0, 16);
INSERT INTO `contrexx_backend_areas` VALUES (25, 2, 'navigation', 'TXT_NEWSLETTER', 1, 'index.php?cmd=newsletter', '_self', 4, 0, 25);
INSERT INTO `contrexx_backend_areas` VALUES (27, 2, 'navigation', 'TXT_NEWS_SYNDICATION', 1, 'index.php?cmd=feed', '_self', 22, 0, 27);
INSERT INTO `contrexx_backend_areas` VALUES (17, 3, 'navigation', 'TXT_SYSTEM_SETTINGS', 1, 'index.php?cmd=settings', '_self', 1, 0, 17);
INSERT INTO `contrexx_backend_areas` VALUES (18, 3, 'navigation', 'TXT_USER_ADMINISTRATION', 1, 'index.php?cmd=user', '_self', 1, 0, 18);
INSERT INTO `contrexx_backend_areas` VALUES (19, 3, 'navigation', 'TXT_STATS', 1, 'index.php?cmd=stats', '_self', 1, 0, 19);
INSERT INTO `contrexx_backend_areas` VALUES (20, 3, 'navigation', 'TXT_BACKUP', 1, 'index.php?cmd=backup', '_self', 1, 0, 20);
INSERT INTO `contrexx_backend_areas` VALUES (21, 3, 'navigation', 'TXT_DESIGN_MANAGEMENT', 1, 'index.php?cmd=skins', '_self', 1, 0, 21);
INSERT INTO `contrexx_backend_areas` VALUES (22, 3, 'navigation', 'TXT_LANGUAGE_SETTINGS', 1, 'index.php?cmd=language', '_self', 1, 0, 22);
INSERT INTO `contrexx_backend_areas` VALUES (23, 3, 'navigation', 'TXT_MODULE_MANAGER', 1, 'index.php?cmd=modulemanager', '_self', 1, 0, 23);
INSERT INTO `contrexx_backend_areas` VALUES (24, 4, 'navigation', 'TXT_SERVER_INFO', 1, 'index.php?cmd=server', '_self', 1, 0, 24);
INSERT INTO `contrexx_backend_areas` VALUES (26, 6, 'function', 'TXT_DELETE_PAGES', 1, '', '_self', 0, 0, 26);
INSERT INTO `contrexx_backend_areas` VALUES (35, 6, 'function', 'TXT_EDIT_PAGES', 1, '', '_self', 0, 0, 35);
INSERT INTO `contrexx_backend_areas` VALUES (36, 6, 'function', 'TXT_ACCESS_CONTROL', 1, '', '_self', 0, 0, 36);
INSERT INTO `contrexx_backend_areas` VALUES (37, 6, 'function', 'TXT_ADD_REPOSITORY', 1, '', '_self', 0, 0, 37);
INSERT INTO `contrexx_backend_areas` VALUES (38, 7, 'function', 'TXT_MODIFY_MEDIA_FILES', 1, '', '_self', 0, 0, 38);
INSERT INTO `contrexx_backend_areas` VALUES (39, 7, 'function', 'TXT_UPLOAD_MEDIA_FILES', 1, '', '_self', 0, 0, 39);
INSERT INTO `contrexx_backend_areas` VALUES (28, 18, 'function', 'TXT_ACTIVATE_DEACTIVATE_USERS', 1, '', '_self', 0, 0, 28);
INSERT INTO `contrexx_backend_areas` VALUES (29, 18, 'function', 'TXT_ADD_USERS', 1, '', '_self', 0, 0, 29);
INSERT INTO `contrexx_backend_areas` VALUES (31, 18, 'function', 'TXT_EDIT_USERINFOS', 1, '', '_self', 0, 0, 31);
INSERT INTO `contrexx_backend_areas` VALUES (30, 18, 'function', 'TXT_DELETE_GROUPS', 1, '', '_self', 0, 0, 30);
INSERT INTO `contrexx_backend_areas` VALUES (33, 18, 'function', 'TXT_ADD_GROUPS', 1, '', '_self', 0, 0, 33);
INSERT INTO `contrexx_backend_areas` VALUES (34, 18, 'function', 'TXT_EDIT_GROUPS', 1, '', '_self', 0, 0, 34);
INSERT INTO `contrexx_backend_areas` VALUES (40, 19, 'function', 'TXT_SETTINGS', 1, '', '_self', 0, 0, 40);
INSERT INTO `contrexx_backend_areas` VALUES (41, 20, 'function', 'TXT_CREATE_BACKUPS', 1, '', '_self', 0, 0, 41);
INSERT INTO `contrexx_backend_areas` VALUES (42, 20, 'function', 'TXT_RESTORE_BACKUP', 1, '', '_self', 0, 0, 42);
INSERT INTO `contrexx_backend_areas` VALUES (43, 20, 'function', 'TXT_DELETE_BACKUPS', 1, '', '_self', 0, 0, 43);
INSERT INTO `contrexx_backend_areas` VALUES (44, 20, 'function', 'TXT_DOWNLOAD_BACKUPS', 1, '', '_self', 0, 0, 44);
INSERT INTO `contrexx_backend_areas` VALUES (45, 20, 'function', 'TXT_VIEW_BACKUPS', 1, '', '_self', 0, 0, 45);
INSERT INTO `contrexx_backend_areas` VALUES (46, 21, 'function', 'TXT_ACTIVATE_SKINS', 1, '', '_self', 0, 0, 46);
INSERT INTO `contrexx_backend_areas` VALUES (47, 21, 'function', 'TXT_EDIT_SKINS', 1, '', '_self', 0, 0, 47);
INSERT INTO `contrexx_backend_areas` VALUES (48, 22, 'function', 'TXT_EDIT_LANGUAGE_SETTINGS', 1, '', '_self', 0, 0, 48);
INSERT INTO `contrexx_backend_areas` VALUES (49, 22, 'function', 'TXT_DELETE_LANGUAGES', 1, '', '_self', 0, 0, 49);
INSERT INTO `contrexx_backend_areas` VALUES (50, 22, 'function', 'TXT_LANGUAGE_SETTINGS', 1, '', '_self', 0, 0, 50);
INSERT INTO `contrexx_backend_areas` VALUES (51, 23, 'function', 'TXT_REGISTER_MODULES', 1, '', '_self', 0, 0, 51);
INSERT INTO `contrexx_backend_areas` VALUES (52, 23, 'function', 'TXT_INST_REMO_MODULES', 1, '', '_self', 0, 0, 52);
INSERT INTO `contrexx_backend_areas` VALUES (53, 6, 'function', 'TXT_COPY_DELETE_SITES', 1, '', '_self', 0, 0, 53);
INSERT INTO `contrexx_backend_areas` VALUES (54, 4, 'navigation', 'TXT_NETWORK_TOOLS', 1, 'index.php?cmd=nettools', '_self', 0, 0, 54);
INSERT INTO `contrexx_backend_areas` VALUES (55, 0, 'group', 'TXT_HELP_SUPPORT', 1, '', '_self', 1, 5, 55);
INSERT INTO `contrexx_backend_areas` VALUES (56, 55, 'navigation', 'TXT_SUPPORT_FORUM', 1, 'http://www.contrexx.com/forum/', '_blank', 1, 1, 56);
INSERT INTO `contrexx_backend_areas` VALUES (58, 4, 'navigation', 'TXT_SYSTEM_UPDATE', 1, 'index.php?cmd=systemUpdate', '_self', 0, 0, 58);
INSERT INTO `contrexx_backend_areas` VALUES (59, 2, 'navigation', 'TXT_LINKS_MODULE_DESCRIPTION', 1, 'index.php?cmd=directory', '_self', 12, 0, 59);
INSERT INTO `contrexx_backend_areas` VALUES (15, 2, 'navigation', 'TXT_FORUM', 1, 'index.php?cmd=forum', '_self', 20, 0, 15);
INSERT INTO `contrexx_backend_areas` VALUES (64, 2, 'navigation', 'TXT_RECOMMEND', 1, 'index.php?cmd=recommend', '_self', 27, 0, 31);
INSERT INTO `contrexx_backend_areas` VALUES (32, 1, 'navigation', 'TXT_IMAGE_ADMINISTRATION', 1, 'index.php?cmd=media&amp;archive=content', '_self', 1, 4, 32);
INSERT INTO `contrexx_backend_areas` VALUES (61, 2, 'navigation', 'TXT_COMMUNITY', 1, 'index.php?cmd=community', '_self', 23, 0, 60);
INSERT INTO `contrexx_backend_areas` VALUES (62, 3, 'navigation', 'TXT_BANNER_ADMINISTRATION', 1, 'index.php?cmd=banner', '_self', 28, 1, 62);
INSERT INTO `contrexx_backend_areas` VALUES (68, 12, 'function', 'TXT_GALLERY_MENU_IMPORT', 1, '', '_self', 0, 4, 68);
INSERT INTO `contrexx_backend_areas` VALUES (69, 12, 'function', 'TXT_GALLERY_MENU_VALIDATE', 1, '', '_self', 0, 5, 69);
INSERT INTO `contrexx_backend_areas` VALUES (70, 12, 'function', 'TXT_GALLERY_MENU_SETTINGS', 1, '', '_self', 0, 6, 70);
INSERT INTO `contrexx_backend_areas` VALUES (65, 12, 'function', 'TXT_GALLERY_MENU_OVERVIEW', 1, '', '_self', 0, 1, 65);
INSERT INTO `contrexx_backend_areas` VALUES (66, 12, 'function', 'TXT_GALLERY_MENU_NEW_CATEGORY', 1, '', '_self', 0, 2, 66);
INSERT INTO `contrexx_backend_areas` VALUES (67, 12, 'function', 'TXT_GALLERY_MENU_UPLOAD', 1, '', '_self', 0, 3, 67);
INSERT INTO `contrexx_backend_areas` VALUES (71, 62, 'function', 'TXT_BANNER_MENU_OVERVIEW', 1, '', '_self', 0, 1, 71);
INSERT INTO `contrexx_backend_areas` VALUES (72, 62, 'function', 'TXT_BANNER_MENU_GROUP_ADD', 1, '', '_self', 0, 1, 72);
INSERT INTO `contrexx_backend_areas` VALUES (73, 62, 'function', 'TXT_BANNER_MENU_BANNER_NEW', 1, '', '_self', 0, 1, 73);
INSERT INTO `contrexx_backend_areas` VALUES (74, 62, 'function', 'TXT_BANNER_MENU_SETTINGS', 1, '', '_self', 0, 1, 74);
INSERT INTO `contrexx_backend_areas` VALUES (75, 1, 'navigation', 'TXT_CONTENT_HISTORY', 1, 'index.php?cmd=workflow', '_self', 1, 3, 75);
INSERT INTO `contrexx_backend_areas` VALUES (76, 2, 'navigation', 'TXT_BLOCK_SYSTEM', 1, 'index.php?cmd=block', '_self', 7, 0, 76);
INSERT INTO `contrexx_backend_areas` VALUES (77, 75, 'function', 'TXT_DELETED_RESTORE', 1, '', '_self', 0, 1, 77);
INSERT INTO `contrexx_backend_areas` VALUES (78, 75, 'function', 'TXT_WORKFLOW_VALIDATE', 1, '', '_self', 0, 1, 78);
INSERT INTO `contrexx_backend_areas` VALUES (79, 6, 'function', 'TXT_ACTIVATE_HISTORY', 1, '', '_self', 0, 6, 79);
INSERT INTO `contrexx_backend_areas` VALUES (80, 6, 'function', 'TXT_HISTORY_DELETE_ENTRY', 1, '', '_self', 0, 7, 80);
INSERT INTO `contrexx_backend_areas` VALUES (81, 3, 'navigation', 'TXT_SYSTEM_DEVELOPMENT', 1, 'index.php?cmd=development', '_self', 29, 0, 81);
INSERT INTO `contrexx_backend_areas` VALUES (82, 2, 'navigation', 'TXT_LIVECAM', 1, 'index.php?cmd=livecam', '_self', 30, 0, 82);
INSERT INTO `contrexx_backend_areas` VALUES (89, 2, 'navigation', 'TXT_TICKET', 1, 'index.php?cmd=ticket', '_self', 31, 20, 83);
INSERT INTO `contrexx_backend_areas` VALUES (90, 1, 'navigation', 'TXT_CONTACTS', 1, 'index.php?cmd=contact', '_self', 0, 7, 84);
INSERT INTO `contrexx_backend_areas` VALUES (91, 90, 'function', 'TXT_CONTACT_SETTINGS', 1, 'index.php?cmd=contact&amp;act=settings', '_self', 6, 0, 85);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_backups`
-- 

DROP TABLE IF EXISTS `contrexx_backups`;
CREATE TABLE IF NOT EXISTS `contrexx_backups` (
  `id` smallint(5) NOT NULL auto_increment,
  `date` varchar(14) NOT NULL default '',
  `description` varchar(100) NOT NULL default '',
  `usedtables` text NOT NULL,
  `size` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `date` (`date`)
) TYPE=MyISAM PACK_KEYS=0 AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `contrexx_backups`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_community_config`
-- 

DROP TABLE IF EXISTS `contrexx_community_config`;
CREATE TABLE IF NOT EXISTS `contrexx_community_config` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(64) NOT NULL default '',
  `value` varchar(255) NOT NULL default '',
  `status` int(1) default '1',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=4 ;

-- 
-- Daten für Tabelle `contrexx_community_config`
-- 

INSERT INTO `contrexx_community_config` VALUES (1, 'community_groups', '108', 1);
INSERT INTO `contrexx_community_config` VALUES (2, 'user_activation', '', 1);
INSERT INTO `contrexx_community_config` VALUES (3, 'user_activation_timeout', '1', 1);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_content`
-- 

DROP TABLE IF EXISTS `contrexx_content`;
CREATE TABLE IF NOT EXISTS `contrexx_content` (
  `id` smallint(6) unsigned NOT NULL default '0',
  `content` mediumtext NOT NULL,
  `title` varchar(250) NOT NULL default '',
  `metatitle` varchar(250) NOT NULL default '',
  `metadesc` varchar(250) NOT NULL default '',
  `metakeys` varchar(250) NOT NULL default '',
  `metarobots` varchar(7) NOT NULL default 'index',
  `css_name` varchar(50) NOT NULL default '',
  `redirect` varchar(255) NOT NULL default '',
  `expertmode` set('y','n') NOT NULL default 'n',
  UNIQUE KEY `contentid` (`id`),
  FULLTEXT KEY `fulltextindex` (`title`,`content`)
) TYPE=MyISAM;

-- 
-- Daten für Tabelle `contrexx_content`
-- 

INSERT INTO `contrexx_content` VALUES (1, '<h2>Thun</h2>\r\n<br /> Die Stadt Thun (frz. Thoune) ist Hauptort des gleichnamigen Amtsbezirks im Schweizer Kanton Bern. Sie liegt am Ausfluss der Aare aus dem Thunersee. Neben dem Tourismus sind Maschinen- und Apparatebau, Nahrungsmittelindustrie und Verlagswesen von wirtschaftlicher Bedeutung. Thun ist auch die gr&ouml;sste Garnisonsstadt der Schweizer Armee.<br /><br />\r\n<h2>Geografie</h2>\r\nDer historische Stadtkern liegt nicht direkt am Thunersee, sondern etwa einen Kilometer davon entfernt an der Aare. Die Altstadt besteht aus dem Schlossberg, wo das Schloss und die Stadtkirche stehen und dem B&auml;lliz, einer Insel in der Aare beim Abfluss aus dem Thunersee. Das B&auml;lliz geh&ouml;rt seit dem 14. Jahrhundert zur Stadt, es wurde im Mittelalter befestigt und besiedelt. Seit 1988 ist das B&auml;lliz eine Fussg&auml;ngerzone. Heute ist es nicht nur die wichtigste Einkaufs- und Marktgasse der Stadt, sondern auch die kulturelle Insel und Zentrum sowie ein beliebte Flaniermeile, besonders im Sommer.<br /><br />Die neuen Stadtquartiere liegen auf der Schwemmebene am Nordwestende des Thunersees, welche von der Kander aufgesch&uuml;ttet wurde, bevor diese 1714 in den See umgeleitet wurde.<br /><br />W&auml;hrend im Norden die Nachbargemeinde Steffisburg mit dem Ortsteil Schw&auml;bis direkt an die Innenstadt grenzt, reicht das Thuner Gemeindegebiet im S&uuml;den wesentlich weiter und umfasst entlang des linken Seeufers die ehemaligen D&ouml;rfer D&uuml;rrenast und Gwatt, welche heute zum geschlossenen Siedlungsgebiet der Stadt geh&ouml;ren. Im Westen der Gemeinde liegen das Quartier Lerchenfeld, die Allmend, welche heute vor allem als Truppen&uuml;bungsplatz genutzt wird, sowie der Stadtteil Allmendingen. Im Osten reicht das Gemeindegebiet ins H&uuml;gelland hinein und umfasst das Dorf Goldiwil. Der mit der Stadt zusammengewachsene Ortsteil H&uuml;nibach am rechten Seeufer geh&ouml;rt nicht mehr zu Thun, sondern zur Gemeinde Hilterfingen.<br /><br />\r\n<h2>Geschichte</h2>\r\nBereits in der Jungsteinzeit (ca. 2500 v.Chr.) gab es im Stadtgebiet eine Siedlung. Der Name Thun wird vom keltischen Wort dunum abgeleitet, was soviel wie Palisadenwerk oder befestigter Ort heisst. Im 7. Jahrhundert wird Thun in der Chronik des fr&auml;nkischen M&ouml;nchs Fredgar erw&auml;hnt. Vor 1200 bauten die Herz&ouml;ge von Z&auml;hringen das heutige Schloss. 1264 erhielt Thun das Stadtrecht. 1384 wurde die Stadt vom Kanton Bern gekauft.<br /><br />W&auml;hrend der Helvetik, von 1798 bis 1802, war Thun die Hauptstadt des kurzlebigen Kantons Oberland. 1913 und 1920 wurden die Vorortsgemeinden Goldiwil und Str&auml;ttligen eingemeindet.<br />', 'Willkommen', 'Willkommen', 'Willkommen', 'Willkommen', 'index', '', '', 'n');
INSERT INTO `contrexx_content` VALUES (534, '<table width=\\"100%\\" cellspacing=\\"0\\" cellpadding=\\"2\\" border=\\"0\\">\r\n    <tbody>\r\n        <tr>\r\n            <td colspan=\\"2\\">Sie sind hier: {GALLERY_CATEGORY_TREE}</td>\r\n        </tr>\r\n        <!-- BEGIN galleryCategories -->\r\n        <tr>\r\n            <td colspan=\\"2\\"><hr size=\\"1\\" /></td>\r\n        </tr>\r\n        <tr class=\\"row{GALLERY_STYLE}\\">\r\n            <td width=\\"1%\\" valign=\\"top\\" align=\\"left\\">{GALLERY_CATEGORY_IMAGE}</td>\r\n            <td valign=\\"top\\"><b>{GALLERY_CATEGORY_NAME}</b><br />{GALLERY_CATEGORY_INFO}<br />{GALLERY_CATEGORY_DESCRIPTION}</td>\r\n        </tr>\r\n        <!-- END galleryCategories -->\r\n        <tr>\r\n            <td colspan=\\"2\\"><hr size=\\"1\\" /></td>\r\n        </tr>\r\n    </tbody>\r\n</table>\r\n<!-- CATEGORY END AND IMAGES START -->   <!-- BEGIN galleryImageBlock --> {GALLERY_JAVASCRIPT}\r\n<table width=\\"100%\\" cellspacing=\\"1\\" cellpadding=\\"0\\" border=\\"0\\">\r\n    <tbody>\r\n        <tr>\r\n            <td colspan=\\"3\\">{GALLERY_CATEGORY_COMMENT}<br /></td>\r\n        </tr>\r\n        <tr>\r\n            <td colspan=\\"3\\"><hr size=\\"1\\" /></td>\r\n        </tr>\r\n        <!-- BEGIN galleryShowImages -->\r\n        <tr>\r\n            <td width=\\"33%\\" valign=\\"top\\" align=\\"center\\" id=\\"gallery\\"> {GALLERY_IMAGE1}<br /> {GALLERY_IMAGE_LINK1} </td>\r\n            <td width=\\"33%\\" valign=\\"top\\" align=\\"center\\" id=\\"gallery\\"> {GALLERY_IMAGE2}<br /> {GALLERY_IMAGE_LINK2} </td>\r\n            <td width=\\"33%\\" valign=\\"top\\" align=\\"center\\" id=\\"gallery\\"> {GALLERY_IMAGE3}<br /> {GALLERY_IMAGE_LINK3} </td>\r\n        </tr>\r\n        <!-- END galleryShowImages -->\r\n    </tbody>\r\n</table>\r\n<!-- END galleryImageBlock -->', 'Bildergalerie', 'Bildergalerie', 'Bildergalerie', 'Bildergalerie', 'index', '', '', 'n');
INSERT INTO `contrexx_content` VALUES (5, '<form action=\\"index.php\\" method=\\"get\\">\r\n	<input name=\\"term\\" value=\\"{SEARCH_TERM}\\" size=\\"30\\" maxlength=\\"100\\" />\r\n	<input value=\\"search\\" name=\\"section\\" type=\\"hidden\\" />\r\n	<input value=\\"{TXT_SEARCH}\\" name=\\"Submit\\" type=\\"submit\\" />\r\n</form>\r\n<br />\r\n{SEARCH_TITLE}<br />\r\n<!-- BEGIN searchrow -->\r\n	{LINK} {COUNT_MATCH}<br />\r\n	{SHORT_CONTENT}<br />\r\n<!-- END searchrow -->\r\n<br />\r\n{SEARCH_PAGING}\r\n<br />\r\n<br />', 'Suchen', 'Suchen', 'Suchen', 'Suchen', 'index', '', '', 'y');
INSERT INTO `contrexx_content` VALUES (6, '<form action=\\"index.php?section=contact&amp;id=1&amp;cmd=thanks\\" method=\\"post\\" enctype=\\"multipart/form-data\\">\r\n<table border=\\"0\\">\r\n<tr>\r\n<td style=\\"width:100px;\\">Name</td>\r\n<td><input style=\\"width:300px;\\" type=\\"text\\" name=\\"contactFormField_1\\" value=\\"\\" />\r\n</td>\r\n</tr>\r\n<tr>\r\n<td style=\\"width:100px;\\">Firmenname</td>\r\n<td><input style=\\"width:300px;\\" type=\\"text\\" name=\\"contactFormField_2\\" value=\\"\\" />\r\n</td>\r\n</tr>\r\n<tr>\r\n<td style=\\"width:100px;\\">Strasse</td>\r\n<td><input style=\\"width:300px;\\" type=\\"text\\" name=\\"contactFormField_3\\" value=\\"\\" />\r\n</td>\r\n</tr>\r\n<tr>\r\n<td style=\\"width:100px;\\">PLZ</td>\r\n<td><input style=\\"width:300px;\\" type=\\"text\\" name=\\"contactFormField_4\\" value=\\"\\" />\r\n</td>\r\n</tr>\r\n<tr>\r\n<td style=\\"width:100px;\\">Ort</td>\r\n<td><input style=\\"width:300px;\\" type=\\"text\\" name=\\"contactFormField_5\\" value=\\"\\" />\r\n</td>\r\n</tr>\r\n<tr>\r\n<td style=\\"width:100px;\\">Land</td>\r\n<td><input style=\\"width:300px;\\" type=\\"text\\" name=\\"contactFormField_6\\" value=\\"\\" />\r\n</td>\r\n</tr>\r\n<tr>\r\n<td style=\\"width:100px;\\">Telefon</td>\r\n<td><input style=\\"width:300px;\\" type=\\"text\\" name=\\"contactFormField_7\\" value=\\"\\" />\r\n</td>\r\n</tr>\r\n<tr>\r\n<td style=\\"width:100px;\\">Fax</td>\r\n<td><input style=\\"width:300px;\\" type=\\"text\\" name=\\"contactFormField_8\\" value=\\"\\" />\r\n</td>\r\n</tr>\r\n<tr>\r\n<td style=\\"width:100px;\\">E-Mail</td>\r\n<td><input style=\\"width:300px;\\" type=\\"text\\" name=\\"contactFormField_9\\" value=\\"\\" />\r\n</td>\r\n</tr>\r\n<tr>\r\n<td style=\\"width:100px;\\">Bemerkungen</td>\r\n<td><textarea style=\\"width:300px; height:100px;\\" name=\\"contactFormField_10\\"></textarea>\r\n</td>\r\n</tr>\r\n<tr>\r\n<td style=\\"width:100px;\\">Datei</td>\r\n<td><input style=\\"width:300px;\\" type=\\"file\\" name=\\"contactFormField_11\\" />\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>&nbsp;</td>\r\n<td>\r\n<input type=\\"reset\\" value=\\"Löschen\\" /> <input type=\\"submit\\" value=\\"Absenden\\" />\r\n</td>\r\n</tr>\r\n</table>\r\n</form>', 'Kontakt', 'Kontakt', 'Kontakt', 'Kontakt', 'index', '', '', 'y');
INSERT INTO `contrexx_content` VALUES (7, 'Vielen Dank, Ihre Formularangaben wurden erfolgreich abgesendet ...', 'Kontakt', 'Kontakt', 'Kontakt', 'Kontakt', 'index', '', '', 'n');
INSERT INTO `contrexx_content` VALUES (8, '<form name=\\"formNews\\" action=\\"index.php?section=news\\" method=\\"post\\">\r\n    <select onchange=\\"this.form.submit()\\" name=\\"category\\">\r\n    <option value=\\"\\" selected=\\"selected\\">{NEWS_NO_CATEGORY}</option>\r\n{NEWS_CAT_DROPDOWNMENU}</select>\r\n</form>\r\n<br/>\r\n<table id=\\"news\\" cellspacing=\\"0\\" cellpadding=\\"5\\" width=\\"100%\\" border=\\"0\\">\r\n<tr>\r\n<td nowrap=\\"nowrap\\" width=\\"15%\\">{TXT_DATE}</th>\r\n<td nowrap=\\"nowrap\\" width=\\"70%\\">{TXT_TITLE}</th>\r\n<td nowrap=\\"nowrap\\" width=\\"15%\\">{TXT_CATEGORY}</th>\r\n</tr>\r\n<!-- BEGIN newsrow -->\r\n<tr>\r\n<td nowrap=\\"nowrap\\" width=\\"15%\\">{NEWS_DATE}&nbsp;&nbsp;</td>\r\n<td width=\\"70%\\"><b>{NEWS_LINK}</b>&nbsp;&nbsp;</td>\r\n<td nowrap=\\"nowrap\\" width=\\"15%\\">{NEWS_CATEGORY}</td>\r\n</tr>\r\n<!-- END newsrow -->\r\n</table>\r\n<br/>\r\n{NEWS_PAGING}<br/>\r\n<br/>', 'News', 'News', 'News', 'News', 'index', '', '', 'n');
INSERT INTO `contrexx_content` VALUES (9, 'Veröffentlicht am: {NEWS_DATE}\r\n<br /><br />\r\n{NEWS_TEXT} <br />\r\n{NEWS_SOURCE}<br />\r\n{NEWS_URL} \r\n<br />\r\n{NEWS_LASTUPDATE}<br />', 'Newsmeldung', 'Newsmeldung', 'Newsmeldung', 'Newsmeldung', 'index', '', '', 'y');
INSERT INTO `contrexx_content` VALUES (10, '{MEDIA_JAVASCRIPT}\r\n<table id=\\"media\\" cellspacing=\\"0\\" cellpadding=\\"5\\" width=\\"100%\\" border=\\"0\\">\r\n    <tbody>\r\n        <tr class=\\"head\\">\r\n            <td align=\\"center\\" width=\\"16\\"><strong>#</strong></td>\r\n            <td colspan=\\"2\\"><a title=\\"{TXT_MEDIA_SORT}\\" href=\\"{MEDIA_NAME_HREF}\\"><strong>{TXT_MEDIA_FILE_NAME}</strong></a> {MEDIA_NAME_ICON}</td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\"><a title=\\"{TXT_MEDIA_SORT}\\" href=\\"{MEDIA_SIZE_HREF}\\" name=\\"sort_size\\"><strong>{TXT_MEDIA_FILE_SIZE}</strong></a> {MEDIA_SIZE_ICON} </td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\"><a title=\\"{TXT_MEDIA_SORT}\\" href=\\"{MEDIA_TYPE_HREF}\\" name=\\"sort_type\\"><strong>{TXT_MEDIA_FILE_TYPE}</strong></a> {MEDIA_TYPE_ICON} </td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\"><a title=\\"{TXT_MEDIA_SORT}\\" href=\\"{MEDIA_DATE_HREF}\\" name=\\"sort_date\\"><strong>{TXT_MEDIA_FILE_DATE}</strong></a> {MEDIA_DATE_ICON} </td>\r\n        </tr>\r\n        <tr class=\\"row2\\" valign=\\"middle\\">\r\n            <td align=\\"center\\" width=\\"16\\"><img title=\\"base\\" height=\\"16\\" alt=\\"base\\" src=\\"images/modules/media/_base.gif\\" width=\\"16\\"/> </td>\r\n            <td colspan=\\"5\\"><strong><a title=\\"{MEDIA_TREE_NAV_MAIN}\\" href=\\"{MEDIA_TREE_NAV_MAIN_HREF}\\">{MEDIA_TREE_NAV_MAIN}</a></strong> <!-- BEGIN mediaTreeNavigation --><a href=\\"{MEDIA_TREE_NAV_DIR_HREF}\\">&nbsp;{MEDIA_TREE_NAV_DIR} /</a> <!-- END mediaTreeNavigation --></td>\r\n        </tr>\r\n        <!-- BEGIN mediaDirectoryTree -->\r\n        <tr class=\\"{MEDIA_DIR_TREE_ROW}\\" valign=\\"middle\\">\r\n            <td width=\\"16\\">&nbsp;</td>\r\n            <td align=\\"center\\" width=\\"16\\"><img title=\\"icon\\" height=\\"16\\" alt=\\"icon\\" src=\\"{MEDIA_FILE_ICON}\\" width=\\"16\\"/></td>\r\n            <td width=\\"100%\\"><a title=\\"{MEDIA_FILE_NAME}\\" href=\\"{MEDIA_FILE_NAME_HREF}\\">{MEDIA_FILE_NAME}</a></td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\">{MEDIA_FILE_SIZE}&nbsp;</td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\">{MEDIA_FILE_TYPE}&nbsp;</td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\">{MEDIA_FILE_DATE}&nbsp;</td>\r\n        </tr>\r\n        <!-- END mediaDirectoryTree --><!-- BEGIN mediaEmptyDirectory -->\r\n        <tr class=\\"row1\\">\r\n            <td>&nbsp;</td>\r\n            <td colspan=\\"5\\">{TXT_MEDIA_DIR_EMPTY}</td>\r\n        </tr>\r\n        <!-- END mediaEmptyDirectory -->\r\n    </tbody>\r\n</table>', 'Media Archiv #1', 'Media Archiv #1', 'Media Archiv #1', 'Media Archiv #1', 'index', '', '', 'y');
INSERT INTO `contrexx_content` VALUES (13, '<b><a href=\\"index.php?section=guestbook&amp;cmd=post\\" title=\\"Eintragen\\">Eintragen</a></b><br /> {GUESTBOOK_TOTAL_ENTRIES} Eintr&auml;ge im G&auml;stebuch.<br /> {GUESTBOOK_PAGING}<br /> {GUESTBOOK_STATUS} <br /><br />\r\n<table width=\\"100%\\" cellspacing=\\"1\\" cellpadding=\\"1\\" border=\\"0\\">\r\n    <!-- BEGIN guestbook_row -->\r\n    <tbody>\r\n        <tr class=\\"{GUESTBOOK_ROWCLASS}\\">\r\n            <td valign=\\"top\\"><img hspace=\\"0\\" border=\\"0\\" alt=\\"\\" src=\\"images/modules/guestbook/post.gif\\" /> <strong>{GUESTBOOK_NICK}</strong> {GUESTBOOK_GENDER} {GUESTBOOK_LOCATION}</td>\r\n            <td valign=\\"top\\" nowrap=\\"nowrap\\">\r\n            <div align=\\"right\\">{GUESTBOOK_DATE}</div>\r\n            </td>\r\n        </tr>\r\n        <tr class=\\"{GUESTBOOK_ROWCLASS}\\">\r\n            <td valign=\\"top\\" colspan=\\"2\\"><br />{GUESTBOOK_COMMENT}<br /><br /></td>\r\n        </tr>\r\n        <tr class=\\"{GUESTBOOK_ROWCLASS}\\">\r\n            <td valign=\\"top\\">{GUESTBOOK_EMAIL} {GUESTBOOK_URL}</td>\r\n        </tr>\r\n        <tr>\r\n            <td colspan=\\"2\\"><hr size=\\"1\\" noshade=\\"noshade\\" /></td>\r\n        </tr>\r\n        <!-- END guestbook_row -->\r\n    </tbody>\r\n</table>', 'Gästebuch', 'Gästebuch', 'Gästebuch', 'Gästebuch', 'index', '', '', 'n');
INSERT INTO `contrexx_content` VALUES (14, '{GUESTBOOK_JAVASCRIPT}\r\nSie können sich hier ins Gästebuch eintragen. <br /> Damit der Eintrag klappt, sollten mindestens alle mit einem <font color="red">*</font> \r\nmarkierten Felder ausgefüllt werden. \r\n<br />\r\n<form name="GuestbookForm" action="index.php?section=guestbook" method="post" onsubmit="return validate(this)">\r\n<br />\r\n<b>Name:</b><font color="red"> *</font> <br />\r\n<input style="width: 350px;" maxlength="255" size="60" name="nickname" id="nickname" /> <br /><br /><b>Kommentar:</b><font color="red"> *</font> \r\n<br />\r\n<textarea style="width: 350px;" name="comment" id="comment" rows="6" cols="60"></textarea><br /><br /><b>Geschlecht: </b><font color="red">*</font>\r\n<br />\r\n<input type="radio" checked="checked" value="F" name="malefemale" /> Weiblich<br />\r\n<input type="radio" value="M" name="malefemale" /> Männlich<br /><br /><b>Wohnort:</b> <font color="red">*</font>\r\n<br />\r\n<input style="width: 350px;" maxlength="255" size="60" name="location" id="location" /> <br /><b>E-mail:</b>&nbsp;<font color="#ff0000">*</font>\r\n<br />\r\n<input style="width: 350px;" maxlength="255" size="60" name="email" id="email" /> <br /><b>Homepage:</b>\r\n<br />\r\n<input style="width: 350px;" name="url" value="http://" size="60" maxlength="255" /> \r\n<br />\r\n<br />\r\n<input type="reset" value="&nbsp;Reset&nbsp;" name="Submit" />&nbsp;&nbsp;\r\n<input type="submit" value="&nbsp;Speichern&nbsp;" name="Submit" /> \r\n</form>', 'Eintragen', 'Eintragen', 'Eintragen', 'Eintragen', 'index', '', '', 'y');
INSERT INTO `contrexx_content` VALUES (16, '<p>Ihre Eingabe wurde vom <b>ASTALAVISTA&reg; Angriffserkennungs System</b> als unzul&auml;ssig erkannt. <br/><br/>Einige besondere Zeichenfolgen werden vom Intrusion Detection System gefiltert und vom Intrusion Response System blockiert. Wenn Sie finden, dass diese Meldung unrechterweise erscheint, nehmen Sie doch bitte mit uns <a href="mailto:ivan.schmid%20AT%20astalavista%20DOT%20ch">Kontakt</a> auf.<br/><br/><i><b>Aktive Arbitrary Input Module:</b></i> \r\n</p><ul>\r\n<li>SQL Injection \r\n</li><li>Cross-Site Scripting \r\n</li><li>Session Hijacking<br/><br/></li></ul>', 'Alert System', 'Alert System', 'Alert System', 'Alert System', 'index', '', '', 'n');
INSERT INTO `contrexx_content` VALUES (17, '<table cellspacing="0" cellpadding="0" width="100%" border="0">\r\n<tbody>\r\n<tr>\r\n<td scope="col">\r\n<div align="left">{ERROR_NUMBER} {ERROR_MESSAGE} <br /><br /><strong>Das gewünschte Dokument existiert nicht an dieser Stelle.</strong><br /><br />Das von Ihnen gesuchte Dokument wurde möglicherweise umbenannt, verschoben oder gelöscht. Es existieren mehrere Möglichkeiten, um ein Dokument zu finden. Sie können auf die Homepage zurückkehren, das Dokument mit Stichworten suchen oder unsere Help Site konsultieren. Um von der letztbesuchten Seite aus weiterzufahren, klicken Sie bitte auf die Schaltfläche ''Zurück'' Ihres Browsers. <br /><br />The document you requested does not exist at this location.<br />The document you are looking for may have been renamed, moved or deleted. There are several ways to locate a document. You can return to the Homepage, search for the document using keywords or consult our Help Site. To continue on from the last page you visited, please press the ''Back'' button of your browser. <br /></div></td></tr></tbody></table>', 'Fehlermeldung', 'Fehlermeldung', 'Fehlermeldung', 'Fehlermeldung', 'index', '', '', 'n');
INSERT INTO `contrexx_content` VALUES (513, '<FORM name=shop action={SHOP_CHECKOUT_ACTION} method=post>\r\n  <TABLE class=text cellSpacing=2 cellPadding=1 width="100%" border=0>\r\n    <TBODY> \r\n    <TR> \r\n      <TD colSpan=2><B>Passwort Hilfe</B></TD>\r\n    </TR>\r\n    <TR> \r\n      <TD colSpan=2><B><FONT color=red>{SHOP_PASSWORD_STATUS}</FONT></B></TD>\r\n    </TR>\r\n    <tr> \r\n      <td noWrap colspan="2"><br>\r\n        Geben Sie die E-Mail-Adresse für Ihr Konto bei Sat-com Multimedia \r\n        ein. </td>\r\n    </tr>\r\n    <TR> \r\n      <TD noWrap width="8%"> \r\n        <input size=50 value={SHOP_PASSWORD_EMAIL} name=email>\r\n      </TD>\r\n      <TD width="92%"> \r\n        <input type=submit value=Weiter name=pay>\r\n      </TD>\r\n    </TR>\r\n    <TR> \r\n      <TD noWrap colspan="2"><br>\r\n        Nachdem Sie den "Weiter"-Knopf angeklickt haben, schicken wir \r\n        Ihnen eine Benachrichtigung per E-Mail mit einem neuen Passwort. <br>\r\n        <br>\r\n        <br>\r\n        <br>\r\n        <br>\r\n        Wenn Sie Ihr Passwort vergessen haben und sich Ihre alte E-Mail-Adresse \r\n        nicht weiter verwenden lässt, Sie aber kein neues Konto eröffnen \r\n        wollen, dann können Sie sich telefonisch bei uns melden. </TD>\r\n    </TR>\r\n    </TBODY> \r\n  </TABLE>\r\n  <BR>\r\n<HR width="100%" color=black noShade SIZE=1>\r\n</FORM>', 'Passwort Hilfe', 'Passwort Hilfe', 'Passwort Hilfe', 'Passwort Hilfe', 'index', '', '', 'y');
INSERT INTO `contrexx_content` VALUES (512, '<table cellspacing=2 cellpadding=1 width="100%" border=0>\r\n  <tbody> \r\n  <tr> \r\n    <td colspan=2><b>Mein Konto</b></td>\r\n  </tr>\r\n  <tr> \r\n    <td colspan=2> Nutzen Sie das Konto um Ihre Bestellungen und Ihre Daten komfortabel \r\n      zu kontrollieren und zu verwalten.<br>\r\n    </td>\r\n  </tr>\r\n  <tr valign="top"> \r\n    <td noWrap rowspan="4" width="20%"><a href="?section=shop&cmd=logout">Log-Out</a><br>\r\n      <a href="?section=shop&cmd=delete">Konto löschen</a></td>\r\n    <td width="92%"> <a href="?section=shop&cmd=orders">Meine Bestellungen \r\n      ansehen</a></td>\r\n  </tr>\r\n  <tr valign="top"> \r\n    <td width="92%"> <a href="?section=shop&cmd=mod">Meine Konto-Daten ändern</a></td>\r\n  </tr>\r\n  <tr valign="top"> \r\n    <td width="92%"> <br>\r\n      <table width="100%" border="0" cellspacing="0" cellpadding="0">\r\n        <tr valign="top"> \r\n          <td colspan="2"><b>eMail-Adresse</b><font color="#C1C0D0"><br>\r\n            </font>{SHOP_EMAIL}</td>\r\n        </tr>\r\n        <tr valign="top"> \r\n          <td><b>Kundennummer</b><font color="#C1C0D0"><br>\r\n            </font>{SHOP_CUSTOMERID}<br>\r\n            <br>\r\n            Zahlungsart<br>\r\n            {SHOP_PAYMENT}</td>\r\n          <td width="61%"><b>Rechnungsadresse</b><font color="#C1C0D0"><br>\r\n            </font>{SHOP_SIGN}<br>\r\n            {SHOP_FIRSTNAME} {SHOP_LASTNAME}<br>\r\n            {SHOP_ADDRESS} <br>\r\n            {SHOP_ZIP}  {SHOP_CITY}<br>\r\n            {SHOP_COUNTRY} </td>\r\n        </tr>\r\n      </table>\r\n      <br>\r\n    </td>\r\n  </tr>\r\n  <tr valign="top"> \r\n    <td> <a href="?section=shop&cmd=modpass">Mein Passwort und </a><a href="?section=shop&cmd=modemail">eMail-Adresse ändern</a> </td>\r\n  </tr>\r\n  </tbody> \r\n</table>', 'Konto Übersicht', 'Konto Übersicht', 'Konto Übersicht', 'Konto Übersicht', 'index', '', '', 'y');
INSERT INTO `contrexx_content` VALUES (509, '<!-- BEGIN shopProductRow1 -->\r\n<table border=\\"0\\" width=\\"100%\\">\r\n<tr valign=\\"top\\"> \r\n<td border=\\"0\\">\r\n<b>{SHOP_PRODUCT_TITLE}</b>\r\n<br>\r\n<a href=\\"{SHOP_PRODUCT_DETAILLINK}\\"><img src=\\"{SHOP_PRODUCT_THUMBNAIL}\\" border=\\"0\\" alt=\\"{SHOP_PRODUCT_TITLE}\\" /></a>\r\n<br />\r\n     {TXT_PRICE_NOW} {SHOP_PRODUCT_DISCOUNTPRICE} {SHOP_PRODUCT_DISCOUNTPRICE_UNIT} {TXT_INSTEAD_OF}\r\n      {SHOP_PRODUCT_PRICE} {SHOP_PRODUCT_PRICE_UNIT} <br>\r\n</td>\r\n<!-- BEGIN shopProductRow2 -->\r\n<td border=\\"0\\" width=\\"50%\\"><b>{SHOP_PRODUCT_TITLE}</b>\r\n<br>\r\n<a href=\\"{SHOP_PRODUCT_DETAILLINK}\\"><img src=\\"{SHOP_PRODUCT_THUMBNAIL}\\" border=\\"0\\" alt=\\"{SHOP_PRODUCT_TITLE}\\"></a>\r\n<br>\r\n      {TXT_PRICE_NOW} {SHOP_PRODUCT_DISCOUNTPRICE} {SHOP_PRODUCT_DISCOUNTPRICE_UNIT} {TXT_INSTEAD_OF} {SHOP_PRODUCT_PRICE} \r\n      {SHOP_PRODUCT_PRICE_UNIT} \r\n</td>\r\n<!-- END shopProductRow2 -->\r\n</tr>\r\n</table>\r\n<!-- END shopProductRow1 -->', 'Sonderangebote', 'Sonderangebote', 'Sonderangebote', 'Sonderangebote', 'index', '', '', 'y');
INSERT INTO `contrexx_content` VALUES (510, 'Hier k&ouml;nnen Sie Ihre eigenen Allgemeinen Gesch&auml;ftsbedingungen hineinschreiben.', 'Allgemeinen Geschäftsbedingungen', 'Allgemeinen Geschäftsbedingungen', 'Allgemeinen Geschäftsbedingungen', 'Allgemeinen Geschäftsbedingungen', 'index', '', '', 'n');
INSERT INTO `contrexx_content` VALUES (511, '<TABLE cellSpacing=\\"2\\" cellPadding=\\"1\\" width=\\"100%\\" border=\\"0\\">\r\n  <TR> \r\n    <TD colSpan=\\"2\\"><B>Eine Online-Bestellung ist einfach.</B></TD>\r\n  </TR>\r\n  <TR> \r\n    <TD align=right colSpan=2> \r\n      <DIV align=left><B><FONT color=\\"red\\">{SHOP_LOGIN_STATUS}</FONT></B></DIV>\r\n    </TD>\r\n  </TR>\r\n  <TR>\r\n    <TD align=\\"right\\" colSpan=\\"2\\">\r\n      <hr width=\\"100%\\" color=\\"black\\" noShade size=\\"1\\">\r\n    </TD>\r\n  </TR>\r\n</TABLE>\r\n  <TABLE cellSpacing=\\"2\\" cellPadding=\\"1\\" width=\\"100%\\" border=\\"0\\">\r\n<FORM name=\\"shop\\" action=\\"?section=shop&cmd=account\\" method=\\"post\\">  \r\n    <TR> \r\n      <TD width=\\"7%\\"> </TD>\r\n      <TD width=\\"93%\\"> </TD>\r\n    </TR>\r\n    <TR> \r\n      <TD colSpan=2><b>Ich bin ein neuer Kunde. </b></TD>\r\n    </TR>\r\n    <TR> \r\n      <TD colSpan=2>Durch Ihre Anmeldung bei uns sind Sie in der Lage schneller \r\n        zu bestellen, kennen jederzeit den Status Ihrer Bestellung und haben immer \r\n        eine aktuelle &Uuml;bersicht &uuml;ber Ihre bisherigen Bestellungen.<br>\r\n      </TD>\r\n    </TR>\r\n    <TR> \r\n      <TD colSpan=2> <br>\r\n        <input type=submit value=\\"Weiter &gt;&gt;\\" name=\\"login\\">\r\n        <br><br>\r\n      </TD>\r\n    </TR>\r\n    <TR> \r\n      <TD colSpan=2> \r\n        <hr width=\\"100%\\" color=black noShade size=1>\r\n      </TD>\r\n    </TR>\r\n	</FORM>\r\n  </TABLE>\r\n  <TABLE class=text cellSpacing=2 cellPadding=1 width=\\"100%\\" border=0>\r\n<FORM name=shop action=\\"{SHOP_LOGIN_ACTION}\\" method=post>\r\n    <TR> \r\n      <TD colSpan=2><b>Ich bin bereits Kunde.</b></TD>\r\n    </TR>\r\n    <TR> \r\n      <TD width=\\"7%\\" nowrap>E-Mail Adresse: </TD>\r\n      <TD width=\\"93%\\"> \r\n        <INPUT maxLength=250 size=30 value=\\"{SHOP_LOGIN_EMAIL}\\" name=\\"username\\">\r\n      </TD>\r\n    </TR>\r\n    <TR> \r\n      <TD width=\\"7%\\">Passwort: </TD>\r\n      <TD width=\\"93%\\"> \r\n        <INPUT type=password maxLength=50 size=30 name=\\"password\\">\r\n      </TD>\r\n    </TR>\r\n    <TR> \r\n      <TD width=\\"7%\\"> </TD>\r\n      <TD width=\\"93%\\"> \r\n        <INPUT type=submit value=\\"Anmelden &gt;&gt;\\" name=login>\r\n      </TD>\r\n    </TR>\r\n	</FORM>\r\n  </TABLE>', 'Mein Konto', 'Mein Konto', 'Mein Konto', 'Mein Konto', 'index', '', '', 'y');
INSERT INTO `contrexx_content` VALUES (508, '<!-- BEGIN shopCart -->\r\n<form action =\\"index.php?section=shop&amp;cmd=cart\\" name=\\"shopForm\\" method =\\"post\\">\r\n  <table width=\\"100%\\" cellpadding=\\"0\\" cellspacing=\\"0\\" border=\\"0\\">\r\n    <tr valign=\\"middle\\"> \r\n      <td align=\\"center\\">\r\n        <table width=\\"100%\\" border=\\"0\\" cellpadding=\\"2\\" cellspacing=\\"1\\">\r\n          <tr> \r\n            <td colspan=\\"5\\"> \r\n              <hr width=\\"100%\\" noshade=\\"noshade\\" color=\\"black\\" size=\\"1\\" />\r\n            </td>\r\n          </tr>\r\n          <tr valign=\\"top\\"> \r\n            <td width=\\"10%\\"><div align=\\"left\\"><b>{TXT_PRODUCT_ID}</b></div></td>\r\n            <td width=\\"45%\\"><div align=\\"left\\"><b>{TXT_PRODUCT}</b></div></td>\r\n            <td width=\\"15%\\"><div align=\\"left\\"><b>{TXT_UNIT_PRICE}</b></div></td>\r\n            <td width=\\"12%\\"><div align=\\"left\\"><b>{TXT_QUANTITY}</b></div></td>\r\n            <td width=\\"25%\\"><div align=\\"right\\"><b>{TXT_TOTAL}</b></div></td>\r\n          </tr>\r\n          <tr> \r\n            <td colspan=\\"5\\" valign=\\"top\\"> \r\n              <hr width=\\"100%\\" color=\\"#cccccc\\" noshade=\\"noshade\\" size=\\"1\\" />\r\n            </td>\r\n          </tr>\r\n          <!-- BEGIN shopCartRow -->\r\n          <tr valign=\\"top\\"> \r\n            <td><div align=\\"left\\">{SHOP_PRODUCT_ID}</div></td>\r\n            <td><div align=\\"left\\"><a href =\\"?section=shop&amp;cmd=details&amp;referer=cart&amp;productId={SHOP_PRODUCT_CART_ID}\\">{SHOP_PRODUCT_TITLE}</a>{SHOP_PRODUCT_OPTIONS}</div></td>\r\n            <td><div align=\\"left\\">{SHOP_PRODUCT_ITEMPRICE} {SHOP_PRODUCT_ITEMPRICE_UNIT}</div></td>\r\n            <td><div align=\\"left\\"><input class=\\"form\\" type=\\"text\\" name=\\"quantity[{SHOP_PRODUCT_CART_ID}]\\" value=\\"{SHOP_PRODUCT_QUANTITY}\\" size=\\"3\\" />\r\n            </div></td>\r\n            <td width=\\"25%\\"> \r\n              <div align=\\"right\\">{SHOP_PRODUCT_PRICE} {SHOP_PRODUCT_PRICE_UNIT} </div>\r\n            </td>\r\n          </tr>\r\n          <!-- END shopCartRow -->\r\n          <tr> \r\n            <td colspan=\\"5\\" valign=\\"top\\"> \r\n              <hr width=\\"100%\\" color=\\"#cccccc\\" noshade=\\"noshade\\" size=\\"1\\" />\r\n            </td>\r\n          </tr>\r\n          <tr> \r\n            <td colspan=\\"3\\" valign=\\"top\\"><div align=\\"left\\"><b>{TXT_INTER_TOTAL}</b></div></td>\r\n            <td width=\\"17%\\" valign=\\"top\\"><div align=\\"left\\"><b>{SHOP_PRODUCT_TOTALITEM}</b></div></td>\r\n            <td width=\\"25%\\" valign=\\"top\\"> \r\n              <div align=\\"right\\"><b>{SHOP_PRODUCT_TOTALPRICE} {SHOP_PRODUCT_TOTALPRICE_UNIT}<br />\r\n                </b> </div>\r\n            </td>\r\n          </tr>\r\n          <tr> \r\n            <td colspan=\\"5\\" valign=\\"top\\"> \r\n              <hr width=\\"100%\\" color=\\"black\\" noshade=\\"noshade\\" size=\\"1\\" />\r\n            </td>\r\n          </tr>\r\n          <tr> \r\n            <td valign=\\"top\\"> \r\n              <strong>{TXT_SHIP_COUNTRY}</strong></td>\r\n            <td colspan=\\"3\\" valign=\\"top\\">{SHOP_COUNTRIES_MENU} </td>\r\n            <td valign=\\"top\\"><div align=\\"right\\">\r\n                <input type=\\"submit\\" name=\\"update\\" value=\\"{TXT_UPDATE}\\" />\r\n            </div></td>\r\n          </tr>\r\n          <tr>\r\n            <td colspan=\\"5\\" valign=\\"top\\">&nbsp;</td>\r\n          </tr>\r\n          <tr>\r\n            <td colspan=\\"5\\" valign=\\"top\\"><div align=\\"right\\">\r\n                <input type=\\"submit\\" name=\\"continue\\" value=\\"{TXT_NEXT}  >>\\" />\r\n            </div></td>\r\n          </tr>\r\n        </table>\r\n      </td>\r\n  </tr>\r\n</table>\r\n</form>\r\n<!-- END shopCart -->\r\n<br />\r\n<b><a href=\\"index.php?section=shop\\" title=\\"{TXT_CONTINUE_SHOPPING}\\">{TXT_CONTINUE_SHOPPING}</a><br />\r\n<a href=\\"index.php?section=shop&amp;act=destroy\\" title=\\"{TXT_EMPTY_CART}\\">{TXT_EMPTY_CART}</a></b>\r\n<br />', 'Ihr Warenkorb', 'Ihr Warenkorb', 'Ihr Warenkorb', 'Ihr Warenkorb', 'index', '', '', 'y');
INSERT INTO `contrexx_content` VALUES (506, '<b><font color="#ff0000">{SHOP_STATUS}</font></b>\r\n<form action="index.php?section=shop&amp;cmd=payment" name="shopForm" method="post">\r\n<table cellspacing="0" cellpadding="0" width="100%" border="0">\r\n<tr valign="middle">\r\n<td align="center">\r\n<table cellspacing="1" cellpadding="2" width="100%" border="0">\r\n<tr>\r\n<td nowrap="nowrap" colspan="2"><b>{TXT_PRODUCTS}</b></td></tr>\r\n<tr>\r\n<td nowrap="nowrap" colspan="2">\r\n<hr width="100%" color="#000000" noshade="noshade" size="1" /></td></tr>\r\n<tr>\r\n<td valign="top"><b>{TXT_TOTALLY_GOODS} </b>{SHOP_TAX_PRODUCTS_TXT}<b>&nbsp;&nbsp;&nbsp;&nbsp;</b>{SHOP_TOTALITEM} {TXT_PRODUCT_S}</td>\r\n<td width="30%" colspan="-1" valign="top" nowrap="nowrap">\r\n<div align="right"><b>{SHOP_TOTALPRICE} {SHOP_UNIT}</b></div></td></tr>\r\n<tr>\r\n<td colspan="2">\r\n<hr width="100%" color="#cccccc" noshade="noshade" size="1" /></td></tr>\r\n<tr valign="top">\r\n<td><strong>{TXT_SHIPPING_METHODS}</strong><br />\r\n{SHOP_SHIPMENT_MENU}\r\n</td>\r\n<td><div align="right"><br>\r\n  {SHOP_SHIPMENT_PRICE} {SHOP_UNIT}</div></td>\r\n</tr>\r\n<tr valign="top">\r\n<td><strong>{TXT_PAYMENT_TYPES}</strong><br />\r\n{SHOP_PAYMENT_MENU}  \r\n</td>\r\n<td><div align="right"> <br>\r\n  {SHOP_PAYMENT_PRICE} {SHOP_UNIT}</div></td>\r\n</tr>\r\n<tr>\r\n<td valign="top" nowrap="nowrap">{TXT_PROCENTUAL_TAX_PART} {SHOP_TAX_PROCENTUAL}</td>\r\n<td width="30%" colspan="-1" valign="top" nowrap="nowrap">\r\n<div align="right">{TXT_TAX_PART} {SHOP_TAX_PRICE} {SHOP_UNIT}</div></td></tr>\r\n<tr>\r\n<td valign="top" nowrap="nowrap"><b>{TXT_TOTAL_PRICE}</b>{SHOP_TAX_GRAND_TXT}</td>\r\n<td width="30%" colspan="-1" valign="top" nowrap="nowrap">\r\n<div align="right"><b>{SHOP_GRAND_TOTAL} {SHOP_UNIT}</b></div></td></tr>\r\n<tr>\r\n<td colspan="2">\r\n<hr width="100%" color="#000000" noshade="noshade" size="1" /></td></tr>\r\n<tr>\r\n<td colspan="2"><b>{TXT_COMMENTS}</b></td>\r\n</tr>\r\n<tr>\r\n<td colspan="2"><textarea name="customer_note" rows="4" cols="52">{SHOP_CUSTOMERNOTE}</textarea> \r\n</td></tr>\r\n<tr>\r\n<td colspan="2">\r\n<hr width="100%" color="#000000" noshade="noshade" size="1" /></td></tr>\r\n<tr>\r\n<td colspan="2"><b>{TXT_TAC}</b></td></tr>\r\n<tr>\r\n<td colspan="2">\r\n<input type="checkbox" value="checked" name="agb" {SHOP_AGB} /> <font color="#ff0000">&nbsp;</font>{TXT_ACCEPT_TAC}</td></tr>\r\n<tr>\r\n<td colspan="2">\r\n<input type="submit" value="{TXT_UPDATE}" name="refresh" /> \r\n<input type="submit" value="{TXT_NEXT}" name="check" /> \r\n</td>\r\n</tr>\r\n</table>\r\n</td>\r\n</tr>\r\n</table>\r\n</form>', 'Bezahlung und Versand', 'Bezahlung und Versand', 'Bezahlung und Versand', 'Bezahlung und Versand', 'index', '', '', 'y');
INSERT INTO `contrexx_content` VALUES (507, '<script language=\\"JavaScript\\" type=\\"text/javascript\\">\r\n<!--  \r\nfunction shopCopyText()  \r\n{\r\n	with (document.shop){\r\n		if(equalAddress.checked) {\r\n			prefix2.value= prefix.value;\r\n			company2.value= company.value;\r\n			lastname2.value= lastname.value;\r\n			firstname2.value= firstname.value;\r\n			address2.value=address.value;\r\n			zip2.value= zip.value;\r\n			city2.value= city.value;\r\n			phone2.value= phone.value;				\r\n			return true;\r\n		} else {	\r\n			prefix2.value= \\"\\";\r\n			company2.value= \\"\\";\r\n			lastname2.value= \\"\\";\r\n			firstname2.value= \\"\\";\r\n			address2.value=\\"\\";\r\n			zip2.value= \\"\\";\r\n			city2.value= \\"\\";\r\n			phone2.value= \\"\\";\r\n			return true;\r\n		}\r\n	}\r\n}\r\n-->\r\n</script>\r\n<form name=\\"shop\\" action=\\"{SHOP_ACCOUNT_ACTION}\\" method=\\"post\\">\r\n<table cellspacing=\\"2\\" cellpadding=\\"1\\" width=\\"100%\\" border=\\"0\\">\r\n<tbody>\r\n<tr>\r\n<td colspan=\\"2\\"><b>{TXT_CUSTOMER_ADDRESS}</b></td>\r\n</tr>\r\n<tr>\r\n<td colspan=\\"2\\"><font color=\\"#ff0000\\">* </font>{TXT_REQUIRED_FIELDS}<br />\r\n  <table cellspacing=\\"0\\" cellpadding=\\"0\\" width=\\"100%\\" border=\\"0\\">\r\n    <tbody>\r\n      <tr>\r\n        <td><b><font color=\\"#ff0000\\">{SHOP_ACCOUNT_STATUS}</font></b></td>\r\n      </tr>\r\n    </tbody>\r\n  </table>\r\n  </td>\r\n</tr>\r\n<tr valign=\\"top\\">\r\n  <td colspan=\\"2\\" nowrap=\\"nowrap\\">&nbsp;  </td>\r\n  </tr>\r\n<tr valign=\\"top\\">\r\n<td nowrap=\\"nowrap\\">{TXT_COMPANY}</td>\r\n<td><input size=\\"30\\" value=\\"{SHOP_ACCOUNT_COMPANY}\\" name=\\"company\\" tabindex=\\"1\\" /> </td>\r\n</tr>\r\n<tr valign=\\"top\\">\r\n<td nowrap=\\"nowrap\\">{TXT_GREETING}</td>\r\n<td><input maxlength=\\"50\\" size=\\"30\\" value=\\"{SHOP_ACCOUNT_PREFIX}\\" name=\\"prefix\\" tabindex=\\"2\\" /> <b><font color=\\"#ff0000\\">*</font></b></td>\r\n</tr>\r\n<tr valign=\\"top\\">\r\n<td nowrap=\\"nowrap\\">{TXT_SURNAME}</td>\r\n<td><input size=\\"30\\" value=\\"{SHOP_ACCOUNT_LASTNAME}\\" name=\\"lastname\\" tabindex=\\"3\\" /> <b><font color=\\"#ff0000\\">*</font></b></td>\r\n</tr>\r\n<tr valign=\\"top\\">\r\n<td nowrap=\\"nowrap\\">{TXT_FIRSTNAME}&nbsp;&nbsp;</td>\r\n<td><input size=\\"30\\" value=\\"{SHOP_ACCOUNT_FIRSTNAME}\\" name=\\"firstname\\" tabindex=\\"4\\" /> <b><font color=\\"#ff0000\\">*</font></b></td>\r\n</tr>\r\n<tr valign=\\"top\\">\r\n<td nowrap=\\"nowrap\\">{TXT_ADDRESS}</td>\r\n<td><input size=\\"30\\" value=\\"{SHOP_ACCOUNT_ADDRESS}\\" name=\\"address\\" tabindex=\\"5\\" /> <b><font color=\\"#ff0000\\">*</font></b></td>\r\n</tr>\r\n<tr valign=\\"top\\">\r\n<td nowrap=\\"nowrap\\">{TXT_POSTALE_CODE}</td>\r\n<td><input size=\\"6\\" value=\\"{SHOP_ACCOUNT_ZIP}\\" name=\\"zip\\" tabindex=\\"6\\" /> <b><font color=\\"#ff0000\\">*</font></b> {TXT_CITY} <input value=\\"{SHOP_ACCOUNT_CITY}\\" name=\\"city\\" tabindex=\\"7\\" /> <b><font color=\\"#ff0000\\">*</font></b> </td>\r\n</tr>\r\n<tr valign=\\"top\\">\r\n<td nowrap=\\"nowrap\\">{TXT_COUNTRY}</td>\r\n<td>{SHOP_ACCOUNT_COUNTRY}</td>\r\n</tr>\r\n<tr valign=\\"top\\">\r\n<td nowrap=\\"nowrap\\">{TXT_PHONE_NUMBER}</td>\r\n<td><input value=\\"{SHOP_ACCOUNT_PHONE}\\" name=\\"phone\\" tabindex=\\"8\\" /> <b><font color=\\"#ff0000\\">*</font></b></td>\r\n</tr>\r\n<tr valign=\\"top\\">\r\n<td nowrap=\\"nowrap\\">{TXT_FAX_NUMBER}</td>\r\n<td><input value=\\"{SHOP_ACCOUNT_FAX}\\" name=\\"fax\\" tabindex=\\"9\\" /> </td>\r\n</tr>\r\n<tr valign=\\"top\\">\r\n  <td colspan=\\"2\\" nowrap=\\"nowrap\\"><hr width=\\"100%\\" color=\\"#000000\\" noshade=\\"noshade\\" size=\\"1\\" /></td>\r\n  </tr>\r\n<tr valign=\\"top\\">\r\n  <td nowrap=\\"nowrap\\"><b>{TXT_SHIPPING_ADDRESS}</b></td>\r\n  <td>&nbsp;</td>\r\n  </tr>\r\n<tr valign=\\"top\\">\r\n  <td nowrap=\\"nowrap\\">&nbsp;</td>\r\n  <td><input type=\\"checkbox\\" value=\\"checked\\" name=\\"equalAddress\\" onClick=\\"shopCopyText();\\" {SHOP_ACCOUNT_EQUAL_ADDRESS} tabindex=\\"10\\" />\r\n{TXT_SAME_BILLING_ADDRESS}</td>\r\n  </tr>\r\n<tr valign=\\"top\\">\r\n  <td nowrap=\\"NOWRAP\\">{TXT_COMPANY}</td>\r\n  <td><input size=\\"30\\" value=\\"{SHOP_ACCOUNT_COMPANY2}\\" name=\\"company2\\" tabindex=\\"11\\" /></td>\r\n  </tr>\r\n<tr valign=\\"top\\">\r\n  <td nowrap=\\"NOWRAP\\">{TXT_GREETING}</td>\r\n  <td><input maxlength=\\"50\\" size=\\"30\\" value=\\"{SHOP_ACCOUNT_PREFIX2}\\" name=\\"prefix2\\" tabindex=\\"12\\" />\r\n      <b><font color=\\"#ff0000\\">*</font></b></td>\r\n  </tr>\r\n<tr valign=\\"top\\">\r\n  <td nowrap=\\"NOWRAP\\">{TXT_SURNAME}</td>\r\n  <td><input size=\\"30\\" value=\\"{SHOP_ACCOUNT_LASTNAME2}\\" name=\\"lastname2\\" tabindex=\\"13\\" />\r\n      <b><font color=\\"#ff0000\\">*</font></b></td>\r\n  </tr>\r\n<tr valign=\\"top\\">\r\n  <td nowrap=\\"NOWRAP\\">{TXT_FIRSTNAME}&nbsp;&nbsp; </td>\r\n  <td><input size=\\"30\\" value=\\"{SHOP_ACCOUNT_FIRSTNAME2}\\" name=\\"firstname2\\" tabindex=\\"14\\" />\r\n      <b><font color=\\"#ff0000\\">*</font></b></td>\r\n  </tr>\r\n<tr valign=\\"top\\">\r\n  <td nowrap=\\"NOWRAP\\">{TXT_ADDRESS}</td>\r\n  <td><input size=\\"30\\" value=\\"{SHOP_ACCOUNT_ADDRESS2}\\" name=\\"address2\\" tabindex=\\"15\\" />\r\n      <b><font color=\\"#ff0000\\">*</font></b></td>\r\n  </tr>\r\n<tr valign=\\"top\\">\r\n  <td nowrap=\\"NOWRAP\\">{TXT_POSTALE_CODE}</td>\r\n  <td><input size=\\"6\\" value=\\"{SHOP_ACCOUNT_ZIP2}\\" name=\\"zip2\\" tabindex=\\"16\\" />\r\n      <b><font color=\\"#ff0000\\">*</font></b> {TXT_CITY}\r\n      <input value=\\"{SHOP_ACCOUNT_CITY2}\\" name=\\"city2\\" tabindex=\\"17\\" />\r\n      <b><font color=\\"#ff0000\\">*</font></b> </td>\r\n  </tr>\r\n<tr valign=\\"top\\">\r\n  <td nowrap=\\"NOWRAP\\">{TXT_COUNTRY}</td>\r\n  <td>{SHOP_ACCOUNT_COUNTRY2}</td>\r\n  </tr>\r\n<tr valign=\\"top\\">\r\n  <td nowrap=\\"NOWRAP\\">{TXT_PHONE_NUMBER}</td>\r\n  <td><input value=\\"{SHOP_ACCOUNT_PHONE2}\\" name=\\"phone2\\" tabindex=\\"18\\" />\r\n      <b><font color=\\"#ff0000\\">*</font></b></td>\r\n  </tr>\r\n<!-- BEGIN account_details -->\r\n<tr valign=\\"top\\">\r\n  <td colspan=\\"2\\" nowrap=\\"NOWRAP\\"><hr width=\\"100%\\" color=\\"#000000\\" noshade=\\"noshade\\" size=\\"1\\" /></td>\r\n  </tr>\r\n<tr valign=\\"top\\">\r\n  <td nowrap=\\"NOWRAP\\"><b>{TXT_YOUR_ACCOUNT_DETAILS}</b></td>\r\n  <td>&nbsp;</td>\r\n</tr>\r\n<tr valign=\\"top\\">\r\n  <td nowrap=\\"nowrap\\">{TXT_EMAIL}</td>\r\n  <td><input size=\\"30\\" value=\\"{SHOP_ACCOUNT_EMAIL}\\" name=\\"email\\" tabindex=\\"19\\" />\r\n      <b><font color=\\"#ff0000\\">*</font></b> </td>\r\n</tr>\r\n<tr valign=\\"top\\">\r\n  <td nowrap=\\"nowrap\\">{TXT_PASSWORD}</td>\r\n  <td><input type=\\"password\\" size=\\"30\\" value=\\"\\" name=\\"password\\" tabindex=\\"20\\" />\r\n      <b><font color=\\"#ff0000\\">*</font></b><br />\r\n    {TXT_PASSWORD_MIN_CHARS}</td>\r\n</tr>\r\n<!-- END account_details -->\r\n<tr valign=\\"top\\">\r\n  <td nowrap=\\"NOWRAP\\">&nbsp;</td>\r\n  <td>&nbsp;</td>\r\n</tr>\r\n<tr valign=\\"top\\">\r\n  <td colspan=\\"2\\" nowrap=\\"NOWRAP\\"><input type=\\"reset\\" value=\\"{TXT_RESET}\\" name=\\"reset\\" tabindex=\\"21\\" />\r\n    <input type=\\"submit\\" value=\\"{TXT_NEXT}  >>\\" name=\\"Submit\\" tabindex=\\"22\\" /></td>\r\n  </tr>\r\n</tbody>\r\n</table>\r\n<br />\r\n</form>', 'Kontoangaben', 'Kontoangaben', 'Kontoangaben', 'Kontoangaben', 'index', '', '', 'y');
INSERT INTO `contrexx_content` VALUES (505, '<b><font color="#ff0000">{SHOP_STATUS}</font></b>\r\n<!-- BEGIN shopConfirm -->\r\n<form action="index.php?section=shop&amp;cmd=confirm" name="shopForm" method="post">\r\n  <table cellspacing="1" cellpadding="2" width="100%" border="0">\r\n  <tr>\r\n    <td nowrap="nowrap" colspan="5"><b>{TXT_ORDER_INFOS}</b></td>\r\n  </tr>\r\n  <tr>\r\n    <td nowrap><b>{TXT_ID}</b></td>\r\n    <td><b>{TXT_PRODUCT}</b></td>\r\n    <td nowrap><b>{TXT_UNIT_PRICE}</b></td>\r\n    <td nowrap><b>{TXT_QUANTITY}</b></td>\r\n    <td nowrap><div align="right"><b>{TXT_TOTAL}</b></div></td>\r\n  </tr>\r\n  <tr>\r\n    <td colspan="5" nowrap><hr width="100%" color="#cccccc" noShade size="1" />\r\n    </td>\r\n  </tr>\r\n  <!-- BEGIN shopCartRow -->\r\n  <tr style="vertical-align:top;">\r\n    <td nowrap>{SHOP_PRODUCT_ID}</td>\r\n    <td>{SHOP_PRODUCT_TITLE}</td>\r\n    <td nowrap>{SHOP_PRODUCT_ITEMPRICE} {SHOP_UNIT}</td>\r\n    <td nowrap>{SHOP_PRODUCT_QUANTITY}</td>\r\n    <td nowrap><div align="right">{SHOP_PRODUCT_PRICE} {SHOP_UNIT}<br>\r\n    </div></td>\r\n  </tr>\r\n  <!-- END shopCartRow -->\r\n  <tr>\r\n    <td colspan="5"><hr width="100%" color="#cccccc" noShade size=1>\r\n    </td>\r\n  </tr>\r\n<tr>\r\n<td colspan="3" valign="top"><b>{TXT_INTER_TOTAL}</b>{SHOP_TAX_PRODUCTS_TXT}</td>\r\n<td valign="top" nowrap="nowrap">{SHOP_TOTALITEM} {TXT_PRODUCT_S}</td>\r\n<td width="30%" colspan="-1" valign="top" nowrap="nowrap">\r\n<div align="right"><b>{SHOP_TOTALPRICE} {SHOP_UNIT}</b></div></td></tr>\r\n<tr>\r\n<td colspan="5">\r\n<hr width="100%" color="#cccccc" noshade="noshade" size="1" /></td></tr>\r\n<tr valign="top">\r\n<td colspan="4"><strong>{TXT_SHIPPING_METHOD}:</strong> {SHOP_SHIPMENT}\r\n</td>\r\n<td><div align="right">{SHOP_SHIPMENT_PRICE} {SHOP_UNIT}</div></td>\r\n</tr>\r\n<tr valign="top">\r\n<td colspan="4"><strong>{TXT_PAYMENT_TYPE}:</strong> {SHOP_PAYMENT}  \r\n</td>\r\n<td><div align="right"> {SHOP_PAYMENT_PRICE} {SHOP_UNIT}</div></td>\r\n</tr>\r\n<tr>\r\n<td colspan="4" valign="top" nowrap="nowrap">{TXT_PROCENTUAL_TAX_PART}: {SHOP_TAX_PROCENTUAL}</td>\r\n<td width="30%" colspan="-1" valign="top" nowrap="nowrap">\r\n<div align="right">{TXT_TAX_PART} {SHOP_TAX_PRICE} {SHOP_UNIT}</div></td></tr>\r\n<tr>\r\n<td colspan="4" valign="top" nowrap="nowrap"><b>{TXT_TOTAL_PRICE}</b>{SHOP_TAX_GRAND_TXT}</td>\r\n<td width="30%" colspan="-1" valign="top" nowrap="nowrap">\r\n<div align="right"><b>{SHOP_GRAND_TOTAL} {SHOP_UNIT}</b></div></td></tr>\r\n<tr>\r\n<td colspan="5">\r\n<hr width="100%" color="#000000" noshade="noshade" size="1" /></td></tr>\r\n<tr>\r\n  <td colspan="5">\r\n  <TABLE  cellSpacing=2 cellPadding=1 width="100%" border=0>\r\n      <TR>\r\n        <TD noWrap rowspan="2" width="49%"><b>{TXT_ADDRESS_CUSTOMER}</b></TD>\r\n        <TD rowspan="2" width="1%"></TD>\r\n      </TR>\r\n      <TR>\r\n        <TD width="50%"><b>{TXT_SHIPPING_ADDRESS}</b></TD>\r\n      </TR>\r\n      <TR valign="top">\r\n        <TD noWrap width="49%">{SHOP_COMPANY}<br>\r\n        {SHOP_PREFIX}<br>\r\n        {SHOP_LASTNAME}<br>\r\n        {SHOP_FIRSTNAME}<br>\r\n        {SHOP_ADDRESS}<br>\r\n        {SHOP_ZIP} {SHOP_CITY}<br>\r\n        {SHOP_COUNTRY}<br>\r\n        <br>\r\n        {SHOP_PHONE}<br>\r\n        {SHOP_FAX}<br>\r\n        {SHOP_EMAIL}</TD>\r\n        <TD width="1%"></TD>\r\n        <TD width="50%">{SHOP_COMPANY2}<br>\r\n        {SHOP_PREFIX2}<br>\r\n        {SHOP_LASTNAME2}<br>\r\n        {SHOP_FIRSTNAME2}<br>\r\n        {SHOP_ADDRESS2}<br>\r\n        {SHOP_ZIP2} {SHOP_CITY2}<br>\r\n        {SHOP_COUNTRY2}<br>\r\n        <br>\r\n        {SHOP_PHONE2}</TD>\r\n      </TR>\r\n      <TR>\r\n        <TD noWrap colspan="3"><hr width="100%" color="black" noShade size="1" />\r\n        </TD>\r\n      </TR>\r\n  </TABLE>\r\n  </td>\r\n</tr>\r\n<tr>\r\n  <td colspan="5"><b>{TXT_COMMENTS}</b></td>\r\n</tr>\r\n<tr>\r\n  <td colspan="4">{SHOP_CUSTOMERNOTE}</td>\r\n  <td>&nbsp;</td>\r\n</tr>\r\n<tr>\r\n  <td colspan="5"><hr width="100%" color="#000000" noshade="noshade" size="1" /></td>\r\n</tr>\r\n<tr>\r\n  <td colspan="5"><div align="right"><input type="submit" value="{TXT_ORDER_NOW}" name="process" /></div></td>\r\n</tr>\r\n</table>\r\n</form>\r\n<!-- END shopConfirm -->\r\n<!-- BEGIN shopProcess -->\r\n{TXT_ORDER_PREPARED} <br/>\r\n{SHOP_PAYMENT_PROCESSING}\r\n<!-- END shopProcess -->', 'Bestellen', 'Bestellen', 'Bestellen', 'Bestellen', 'index', '', '', 'y');
INSERT INTO `contrexx_content` VALUES (503, '<font color=\\"red\\"><b>{SHOP_STATUS}</b></font><br />', 'Transaktionsstatus', 'Transaktionsstatus', 'Transaktionsstatus', 'Transaktionsstatus', 'index', '', '', 'n');
INSERT INTO `contrexx_content` VALUES (504, '{SHOP_JAVASCRIPT_CODE} \r\n{SHOP_MENU}<br />\r\n{SHOP_CART_INFO}<br />\r\n{SHOP_PRODUCT_PAGING}\r\n<table width="100%" cellspacing="4" cellpadding="0" border="0">\r\n<tr> \r\n<td width="100%" height="20" background="images/modules/shop/dotted_line.gif"><img width="1" height="20" border="0" alt="" src="images/modules/shop/pixel.gif" /></td>		\r\n</tr>\r\n</table>\r\n<!-- BEGIN shopProductRow -->\r\n<form method="post" action="index.php?section=shop&amp;cmd=cart" name="{SHOP_PRODUCT_FORM_NAME}" id="{SHOP_PRODUCT_FORM_NAME}">\r\n<input type="hidden" value="{SHOP_PRODUCT_ID}" name="productId" />\r\n<table width="100%" cellspacing="3" cellpadding="1" border="0">\r\n<tr> \r\n<td colspan="4"><strong>{SHOP_PRODUCT_TITLE}</strong></td>\r\n</tr>\r\n<tr>\r\n<td width="25%" style="vertical-align:top;"><a href="{SHOP_PRODUCT_THUMBNAIL_LINK}"><img border="0" alt="{TXT_SEE_LARGE_PICTURE}" src="{SHOP_PRODUCT_THUMBNAIL}" /></a></td>\r\n<td width="75%" colspan="3" valign="top"><small><i><strong>{TXT_PRODUCT_ID}:</strong> {SHOP_PRODUCT_ID}</i></small> <br />\r\n{SHOP_PRODUCT_DESCRIPTION}<br />{SHOP_PRODUCT_DETAILDESCRIPTION}\r\n<br />\r\n<!-- BEGIN shopProductOptionsRow -->\r\n<table width="100%" cellspacing="0" cellpadding="0" border="0">\r\n<tr>\r\n<td>\r\n<strong>{SHOP_PRODUCT_OPTIONS_TITLE}</strong><br><br >\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\n<div id="product_options_layer{SHOP_PRODUCT_ID}" style="display:none;">\r\n<table width="100%" cellspacing="0" cellpadding="0" border="0">\r\n<!-- BEGIN shopProductOptionsValuesRow -->\r\n<tr>\r\n<td width="150" style="vertical-align:top;">\r\n{SHOP_PRODUCT_OPTIONS_NAME}:\r\n</td>\r\n<td>{SHOP_PRODCUT_OPTION}</td>\r\n</tr>\r\n<!-- END shopProductOptionsValuesRow -->\r\n</table>\r\n</div>\r\n</td>\r\n</tr>\r\n</table>\r\n<!-- END shopProductOptionsRow -->\r\n<br />{SHOP_PRODUCT_STOCK}<br />{SHOP_MANUFACTURER_LINK}</td>\r\n</tr>\r\n<tr>     \r\n<td colspan="4">{SHOP_PRODUCT_DETAILLINK}</td>\r\n</tr>\r\n<tr>   \r\n<td colspan="3"><b><font color="red">{SHOP_PRODUCT_DISCOUNTPRICE} {SHOP_PRODUCT_DISCOUNTPRICE_UNIT} </font> {SHOP_PRODUCT_PRICE} {SHOP_PRODUCT_PRICE_UNIT} </b>\r\n</td>   \r\n<td>\r\n<div align="right"><input type="submit" value="{TXT_ADD_TO_CARD}" name="{SHOP_PRODUCT_SUBMIT_NAME}" onclick="{SHOP_PRODUCT_SUBMIT_FUNCTION}" /></div></td>\r\n</td>\r\n</tr>\r\n<tr>   \r\n<td height="20" background="images/modules/shop/dotted_line.gif" colspan="4"><img width="1" height="20" border="0" alt="" src="images/modules/shop/pixel.gif" /></td>\r\n</tr>	\r\n</table>\r\n</form>\r\n<!-- END shopProductRow -->\r\n<p>{SHOP_PRODUCT_PAGING}', 'Detaillierte Produktedaten', 'Detaillierte Produktedaten', 'Detaillierte Produktedaten', 'Detaillierte Produktedaten', 'index', '', '', 'y');
INSERT INTO `contrexx_content` VALUES (502, '{SHOP_JAVASCRIPT_CODE} \r\n{SHOP_MENU}<br />\r\n{SHOP_CART_INFO}<br />\r\n{SHOP_PRODUCT_PAGING}\r\n<table width=\\"100%\\" cellspacing=\\"4\\" cellpadding=\\"0\\" border=\\"0\\">\r\n<tr> \r\n<td width=\\"100%\\" height=\\"20\\" style=\\"background-image: url(images/modules/shop/dotted_line.gif);\\"><img width=\\"1\\" height=\\"20\\" border=\\"0\\" alt=\\"\\" src=\\"images/modules/shop/pixel.gif\\" /></td>		\r\n</tr>\r\n</table>\r\n<!-- BEGIN shopProductRow -->\r\n<form method=\\"post\\" action=\\"index.php?section=shop&amp;cmd=cart\\" name=\\"{SHOP_PRODUCT_FORM_NAME}\\" id=\\"{SHOP_PRODUCT_FORM_NAME}\\">\r\n<input type=\\"hidden\\" value=\\"{SHOP_PRODUCT_ID}\\" name=\\"productId\\" />\r\n<table width=\\"100%\\" cellspacing=\\"3\\" cellpadding=\\"1\\" border=\\"0\\">\r\n<tr> \r\n<td colspan=\\"4\\"><strong>{SHOP_PRODUCT_TITLE}</strong></td>\r\n</tr>\r\n<tr>\r\n<td width=\\"25%\\" style=\\"vertical-align:top;\\"><a href=\\"{SHOP_PRODUCT_THUMBNAIL_LINK}\\"><img border=\\"0\\" alt=\\"{TXT_SEE_LARGE_PICTURE}\\" src=\\"{SHOP_PRODUCT_THUMBNAIL}\\" /></a></td>\r\n<td width=\\"75%\\" colspan=\\"3\\" valign=\\"top\\"><small><i><strong>{TXT_PRODUCT_ID}:</strong> {SHOP_PRODUCT_ID}</i></small> <br />\r\n{SHOP_PRODUCT_DESCRIPTION}<br />\r\n<br />\r\n<!-- BEGIN shopProductOptionsRow -->\r\n<table width=\\"100%\\" cellspacing=\\"0\\" cellpadding=\\"0\\" border=\\"0\\">\r\n<tr>\r\n<td>\r\n<strong>{SHOP_PRODUCT_OPTIONS_TITLE}</strong><br /><br />\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\n<div id=\\"product_options_layer{SHOP_PRODUCT_ID}\\" style=\\"display:none;\\">\r\n<table width=\\"100%\\" cellspacing=\\"0\\" cellpadding=\\"0\\" border=\\"0\\">\r\n<!-- BEGIN shopProductOptionsValuesRow -->\r\n<tr>\r\n<td width=\\"150\\" style=\\"vertical-align:top;\\">\r\n{SHOP_PRODUCT_OPTIONS_NAME}:\r\n</td>\r\n<td>{SHOP_PRODCUT_OPTION}</td>\r\n</tr>\r\n<!-- END shopProductOptionsValuesRow -->\r\n</table>\r\n</div>\r\n</td>\r\n</tr>\r\n</table>\r\n<!-- END shopProductOptionsRow -->\r\n<br />{SHOP_PRODUCT_STOCK}<br />{SHOP_MANUFACTURER_LINK}</td>\r\n</tr>\r\n<tr>     \r\n<td colspan=\\"4\\">{SHOP_PRODUCT_DETAILLINK}</td>\r\n</tr>\r\n<tr>   \r\n<td colspan=\\"3\\"><b><font color=\\"red\\">{SHOP_PRODUCT_DISCOUNTPRICE} {SHOP_PRODUCT_DISCOUNTPRICE_UNIT} </font> {SHOP_PRODUCT_PRICE} {SHOP_PRODUCT_PRICE_UNIT} </b>\r\n</td>   \r\n<td>\r\n<div align=\\"right\\"><input type=\\"submit\\" value=\\"{TXT_ADD_TO_CARD}\\" name=\\"{SHOP_PRODUCT_SUBMIT_NAME}\\" onclick=\\"{SHOP_PRODUCT_SUBMIT_FUNCTION}\\" /></div></td>\r\n</tr>\r\n<tr>   \r\n<td height=\\"20\\" style=\\"background-image: url(images/modules/shop/dotted_line.gif);\\" colspan=\\"4\\"><img width=\\"1\\" height=\\"20\\" border=\\"0\\" alt=\\"\\" src=\\"images/modules/shop/pixel.gif\\" /></td>\r\n</tr>	\r\n</table>\r\n</form>\r\n<!-- END shopProductRow -->\r\n{SHOP_PRODUCT_PAGING}\r\n', 'Online Shop', 'Online Shop', 'Online Shop', 'Online Shop', 'index', '', '', 'y');
INSERT INTO `contrexx_content` VALUES (151, '<br />\r\n<form name=\\"VotingForm\\" action=\\"?section=voting\\" method=\\"post\\">\r\n<table width=\\"100%\\" border=\\"0\\">\r\n<tr> \r\n<td><b>{VOTING_TITLE}</b>{VOTING_DATE}</td>\r\n</tr>\r\n<tr> \r\n<td class=\\"desc\\"> {VOTING_RESULTS_TEXT}<br />\r\n{VOTING_RESULTS_TOTAL_VOTES}{TXT_SUBMIT} </td>\r\n</tr>\r\n</table>\r\n</form>\r\n<table width=\\"100%\\" border=\\"0\\">\r\n<tr> \r\n<td valign=\\"top\\" colspan=\\"2\\" class=\\"title\\"><b>{VOTING_OLDER_TITLE}</b></td>\r\n</tr>\r\n<tr> \r\n<td valign=\\"top\\" nowrap=\\"nowrap\\"><b>{TXT_DATE}</b></td>\r\n<td valign=\\"top\\" nowrap=\\"nowrap\\"><b>{TXT_TITLE}</b></td>\r\n</tr>\r\n<!-- BEGIN votingRow -->\r\n<tr class=\\"{VOTING_LIST_CLASS}\\"> \r\n<td nowrap=\\"nowrap\\">{VOTING_OLDER_DATE}</td>\r\n<td nowrap=\\"nowrap\\">{VOTING_OLDER_TEXT}</td>\r\n</tr>\r\n<!-- END votingRow -->\r\n</table>\r\n<br />{VOTING_PAGING}', 'Voting', 'Voting', 'Voting', 'Voting', 'index', '', '', 'y');
INSERT INTO `contrexx_content` VALUES (31, '<form method=\\"post\\" action=\\"index.php?section=login\\" name=\\"loginForm\\">\r\n    <input type=\\"hidden\\" value=\\"{LOGIN_REDIRECT}\\" name=\\"redirect\\" />\r\n    <table width=\\"100%\\" cellspacing=\\"0\\" cellpadding=\\"2\\" border=\\"0\\">\r\n        <tbody>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">{TXT_USER_NAME}:</td>\r\n                <td width=\\"40%\\"><input type=\\"\\" name=\\"USERNAME\\" value=\\"\\" size=\\"30\\" /></td>\r\n                <td width=\\"30%\\" rowspan=\\"3\\">&nbsp;&nbsp;&nbsp;&nbsp;<img width=\\"20\\" height=\\"28\\" align=\\"middle\\" src=\\"/images/modules/login/login_key.gif\\" alt=\\"\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\" rowspan=\\"3\\" style=\\"vertical-align: top;\\">{TXT_PASSWORD}:</td>\r\n                <td width=\\"40%\\"><input type=\\"password\\" name=\\"PASSWORD\\" value=\\"\\" size=\\"30\\" /> </td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"40%\\"><input type=\\"submit\\" name=\\"login\\" value=\\"{TXT_LOGIN}\\" size=\\"15\\" class=\\"input\\" /> </td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"40%\\" colspan=\\"2\\"><a title=\\"{TXT_LOST_PASSWORD}\\" href=\\"index.php?section=login&amp;cmd=lostpw\\">{TXT_PASSWORD_LOST}</a></td>\r\n            </tr>\r\n            <tr>\r\n                <td style=\\"color: rgb(255, 0, 0); font-weight: bold;\\" colspan=\\"3\\"><br />{LOGIN_STATUS_MESSAGE}</td>\r\n            </tr>\r\n        </tbody>\r\n    </table>\r\n</form>', 'Login', 'Login', 'Login', 'Login', 'index', '', '', 'n');
INSERT INTO `contrexx_content` VALUES (32, '<form name=\\"docSys\\" action=\\"index.php?section=docsys\\" method=\\"post\\">\r\n    <select onchange=\\"javascript:this.form.submit()\\" name=\\"category\\">\r\n    <option value=\\"\\" selected=\\"selected\\">{DOCSYS_NO_CATEGORY}</option>\r\n    {DOCSYS_CAT_MENU}</select>\r\n</form>\r\n<br/>\r\n<table id=\\"docsys\\" cellspacing=\\"0\\" cellpadding=\\"2\\" width=\\"100%\\" border=\\"0\\">\r\n        <tr>\r\n            <td nowrap=\\"nowrap\\" width=\\"5%\\"><b>Datum</b></td>\r\n            <td width=\\"100%\\"><b>Titel</b></td>\r\n            <td nowrap=\\"nowrap\\"><b>Kategorie</b></td>\r\n        </tr>\r\n        <!-- BEGIN row -->\r\n        <tr>\r\n            <td nowrap=\\"nowrap\\">{DOCSYS_DATE}&nbsp;&nbsp;</td>\r\n            <td width=\\"100%\\"><b>{DOCSYS_LINK}</b>&nbsp;&nbsp;{DOCSYS_AUTHOR}</td>\r\n            <td nowrap=\\"nowrap\\">{DOCSYS_CATEGORY}</td>\r\n        </tr>\r\n        <!-- END row -->\r\n</table>\r\n<br/>\r\n{DOCSYS_PAGING}<br/>\r\n<br/>', 'Dokumenten System', 'Dokumenten System', 'Dokumenten System', 'Dokumenten System', 'index', '', '', 'y');
INSERT INTO `contrexx_content` VALUES (33, '{DOCSYS_TEXT} <br />\r\nVeröffentlicht am {DOCSYS_DATE} unter dem Titel {DOCSYS_TITLE}\r\n{DOCSYS_AUTHOR} <br />\r\n{DOCSYS_SOURCE}<br />\r\n{DOCSYS_URL} \r\n<br />\r\n{DOCSYS_LASTUPDATE}<br />', 'Documents', 'Documents', 'Documents', 'Documents', 'index', '', '', 'y');
INSERT INTO `contrexx_content` VALUES (154, '<!-- START calendar_show.html -->\r\n{CALENDAR_JAVASCRIPT}\r\n\r\n<!-- BEGIN boxes -->\r\n<div style=\\"margin: auto; width: 200px;\\">\r\n{CALENDAR_CATEGORIES}\r\n<br />\r\n{CALENDAR}\r\n</div>\r\n<!-- END boxes -->\r\n\r\n<!-- BEGIN list -->\r\n<div>\r\n{CALENDAR_CATEGORIES}\r\n<br /><br />\r\n<h3>{CALENDAR_DATE}</h3>\r\n<table class=\\"calendar_eventlist\\" style=\\"width: 100%;\\">\r\n	<tr>\r\n		<th style=\\"text-align: left;\\">{TXT_CALENDAR_STARTDATE}</th>\r\n		<th style=\\"text-align: left;\\">{TXT_CALENDAR_TITLE}</th>\r\n		<th style=\\"text-align: left;\\">{TXT_CALENDAR_PLACE}</th>\r\n	</tr>\r\n	<!-- BEGIN event -->\r\n	<tr>\r\n		<td style=\\"width: 80px;\\">{CALENDAR_STARTDATE} {CALENDAR_STARTTIME}</td>\r\n		<td><a href=\\"?section=calendar&amp;cmd=event&amp;id={CALENDAR_ID}\\">{CALENDAR_TITLE}</a></td>\r\n		<td style=\\"width: 80px;\\">{CALENDAR_PLACE}</td>\r\n	</tr>\r\n	<!-- END event -->\r\n</table>\r\n</div>\r\n<!-- END list -->\r\n<!-- END calendar_show.html -->', 'Drei Boxen Ansicht', 'Drei Boxen Ansicht', 'Drei Boxen Ansicht', 'Drei Boxen Ansicht', 'index', '', '', 'n');
INSERT INTO `contrexx_content` VALUES (155, '<!-- START calendar_show_note.html -->\r\n<table cellspacing=\\"0\\" cellpadding=\\"3\\" width=\\"100%\\" border=\\"0\\">\r\n	<tr>\r\n		<td class=\\"title\\" nowrap=\\"nowrap\\">\r\n			{TXT_CALENDAR_DATE}:\r\n		</td>\r\n		<td class=\\"title\\" nowrap=\\"nowrap\\">\r\n			<b>{CALENDAR_TITLE}</b>\r\n		</td>\r\n	</tr>\r\n	<tr>\r\n		<td width=\\"100\\">\r\n			{TXT_CALENDAR_CAT}:\r\n		</td>\r\n		<td>\r\n			{CALENDAR_CAT}\r\n		</td>\r\n	</tr>\r\n	<tr>\r\n		<td width=\\"100\\">\r\n			{TXT_CALENDAR_NAME}:\r\n		</td>\r\n		<td>\r\n			{CALENDAR_NAME}\r\n		</td>	\r\n	</tr>\r\n	<tr>\r\n		<td width=\\"100\\">\r\n			{TXT_CALENDAR_PLACE}:\r\n		</td>\r\n		<td>\r\n			{CALENDAR_PLACE}\r\n		</td>\r\n	</tr>\r\n	<!--\r\n	<tr>\r\n		<td width=\\"100\\">\r\n			{TXT_CALENDAR_PRIORITY}:\r\n		</td>\r\n		<td>\r\n			<img src=\\"images/modules/calendar/{CALENDAR_PRIORITY_GIF}.gif\\" align=\\"absmiddle\\"> ({CALENDAR_PRIORITY})\r\n		</td>\r\n	</tr>\r\n	-->\r\n	<tr>\r\n		<td width=\\"100\\">\r\n			{TXT_CALENDAR_START}:\r\n		</td>\r\n		<td>\r\n			{CALENDAR_START}\r\n		</td>\r\n	</tr>\r\n	<tr>\r\n		<td width=\\"100\\">\r\n			{TXT_CALENDAR_END}:\r\n		</td>\r\n		<td>\r\n			{CALENDAR_END}\r\n		</td>\r\n	</tr>\r\n	<tr>\r\n		<td width=\\"100\\" valign=\\"top\\">\r\n			{TXT_CALENDAR_COMMENT}:\r\n		</td>\r\n		<td>\r\n			{CALENDAR_COMMENT}\r\n		</td>\r\n	</tr>\r\n\r\n	<!-- BEGIN infolink -->\r\n    <tr class=\\"row1\\">\r\n        	<td width=\\"100\\" valign=\\"top\\"> {TXT_CALENDAR_INFO}: </td>\r\n        	<td><a href=\\"{CALENDAR_INFO_HREF}\\">{CALENDAR_INFO}</a></td>\r\n        </tr>\r\n	<!-- END infolink -->\r\n</table><br />\r\n<a href=\\"javascript:history.back()\\">{TXT_CALENDAR_BACK}</a>\r\n<!-- END calendar_show_note.html -->', 'Veranstaltungs Information', 'Veranstaltungs Information', 'Veranstaltungs Information', 'Veranstaltungs Information', 'index', '', '', 'y');
INSERT INTO `contrexx_content` VALUES (636, '<!-- START calendar_standard_view.html --> {CALENDAR_JAVASCRIPT}\r\n<div style=\\"margin: auto; width: 200px;\\"> {CALENDAR} {CALENDAR_CATEGORIES} <br /> <br /> </div>\r\n<span style=\\"font-size: 11px; font-weight: bold;\\">{TXT_CALENDAR_SEARCH}:</span>\r\n<form action=\\"?section=calendar&amp;act=search\\" method=\\"post\\" id=\\"searchform\\">\r\n    <table style=\\"font-size: 11px;\\">\r\n        <tbody>\r\n            <tr>\r\n                <td>{TXT_CALENDAR_FROM}:</td>\r\n                <td style=\\"padding-left: 5px;\\"><input type=\\"text\\" name=\\"startDate\\" id=\\"DPC_edit1_YYYY-MM-DD\\" value=\\"{CALENDAR_DATEPICKER_START}\\" style=\\"padding: 2px; width: 8em;\\" /></td>\r\n                <td style=\\"padding-left: 15px;\\">{TXT_CALENDAR_KEYWORD}:</td>\r\n                <td style=\\"padding-left: 5px;\\"><input type=\\"text\\" name=\\"keyword\\" style=\\"padding: 2px;\\" value=\\"{CALENDAR_SEARCHED_KEYWORD}\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td>{TXT_CALENDAR_TILL}:</td>\r\n                <td style=\\"padding-left: 5px;\\"><input type=\\"text\\" name=\\"endDate\\" id=\\"DPC_edit2_YYYY-MM-DD\\" value=\\"{CALENDAR_DATEPICKER_END}\\" style=\\"padding: 2px; width: 8em;\\" /></td>\r\n                <td style=\\"padding-left: 15px;\\">&nbsp;</td>\r\n                <td style=\\"padding-left: 5px;\\"><input type=\\"submit\\" value=\\"{TXT_CALENDAR_SEARCH}\\" /></td>\r\n            </tr>\r\n        </tbody>\r\n    </table>\r\n</form>\r\n<div style=\\"width: 100%; margin-top: 15px;\\">\r\n<table cellspacing=\\"0\\" cellpadding=\\"0\\" class=\\"calendar_eventlist\\" style=\\"width: 100%;\\">\r\n    <tbody>\r\n        <tr>\r\n            <th style=\\"text-align: left;\\">{TXT_CALENDAR_STARTDATE}</th> 		<th style=\\"text-align: left;\\">{TXT_CALENDAR_TITLE}</th> 		<th style=\\"text-align: left;\\">{TXT_CALENDAR_PLACE}</th>\r\n        </tr>\r\n        <!-- BEGIN event -->\r\n        <tr>\r\n            <td style=\\"width: 80px;\\">{CALENDAR_STARTDATE} {CALENDAR_STARTTIME}</td>\r\n            <td><a href=\\"?section=calendar&amp;cmd=event&amp;id={CALENDAR_ID}\\">{CALENDAR_TITLE}</a></td>\r\n            <td style=\\"width: 80px;\\">{CALENDAR_PLACE}</td>\r\n        </tr>\r\n        <!-- END event -->\r\n    </tbody>\r\n</table>\r\n</div>\r\n<!-- END calendar_standard_view.html -->', 'Kalender', 'Kalender', 'Kalender', 'Kalender', 'index', '', '', 'n');
INSERT INTO `contrexx_content` VALUES (637, '<!-- START calendar_event_list.html -->\r\n{CALENDAR_JAVASCRIPT}\r\n\r\n{CALENDAR_CATEGORIES}<br />\r\n\r\n<table class=\\"calendar_eventlist\\" style=\\"width: 100%;\\">\r\n	<tr>\r\n		<th style=\\"text-align: left;\\">{TXT_CALENDAR_STARTDATE}</th>\r\n		<th style=\\"text-align: left;\\">{TXT_CALENDAR_TITLE}</th>\r\n		<th style=\\"text-align: left;\\">{TXT_CALENDAR_PLACE}</th>\r\n	</tr>\r\n	<!-- BEGIN event -->\r\n	<tr>\r\n		<td style=\\"width: 80px;\\">{CALENDAR_STARTDATE} {CALENDAR_STARTTIME}</td>\r\n		<td><a href=\\"?section=calendar&amp;cmd=event&amp;id={CALENDAR_ID}\\">{CALENDAR_TITLE}</a></td>\r\n		<td style=\\"width: 80px;\\">{CALENDAR_PLACE}</td>\r\n	</tr>\r\n	<!-- END event -->\r\n</table>\r\n\r\n<!-- END calendar_event_list.html -->	', 'Auflistung aller Events', 'Auflistung aller Events', 'Auflistung aller Events', 'Auflistung aller Events', 'index', '', '', 'n');
INSERT INTO `contrexx_content` VALUES (37, 'Hier k&ouml;nnen Sie sich die Module anschauen.refrfer', 'Demo Module', 'Demo Module', 'Demo Module', 'Demo Module', 'index', '', '', 'n');
INSERT INTO `contrexx_content` VALUES (203, '<!-- START feed.html -->\r\n{FEED_NO_NEWSFEED}\r\n<!-- BEGIN feed_table -->\r\n<table cellspacing=\\"0\\" cellpadding=\\"0\\" border=\\"0\\">\r\n  <tr> \r\n    <td valign=\\"top\\" nowrap=\\"nowrap\\"> \r\n      <!-- BEGIN feed_cat -->\r\n      <b>{FEED_CAT_NAME}</b><br />\r\n      <!-- BEGIN feed_news -->\r\n      &nbsp;&nbsp;&nbsp;&nbsp;<a href=\\"{FEED_NEWS_LINK}\\">{FEED_NEWS_NAME}</a><br />\r\n      <!-- END feed_news -->\r\n      <!-- END feed_cat -->\r\n    </td>\r\n  </tr>\r\n  <tr> \r\n    <td valign=\\"top\\" nowrap=\\"nowrap\\">\r\n      <div  style=\\"overflow:auto;width: 500px;\\">  <br />\r\n      <!-- BEGIN feed_show_news -->\r\n      <br /><b>{FEED_CAT}</b> &gt; <b>{FEED_PAGE}</b> ({FEED_TITLE})<br />\r\n      {FEED_IMAGE} {TXT_FEED_LAST_UPTDATE}: {FEED_TIME}<br />\r\n      <br />\r\n      <ul>\r\n	  \r\n      <!-- BEGIN feed_output_news -->      \r\n       <li><a href=\\"{FEED_LINK}\\" target=\\"_blank\\">{FEED_NAME}</a></li>     \r\n      <!-- END feed_output_news --> \r\n      </ul></div>\r\n      <!-- END feed_show_news -->\r\n    </td>\r\n  </tr>\r\n</table>\r\n<!-- END feed_table -->\r\n<!-- END feed.html -->', 'News-Syndication', 'News-Syndication', 'News-Syndication', 'News-Syndication', 'index', '', '', 'y');
INSERT INTO `contrexx_content` VALUES (371, '{MEDIA_JAVASCRIPT}\r\n<table id=\\"media\\" cellspacing=\\"0\\" cellpadding=\\"5\\" width=\\"100%\\" border=\\"0\\">\r\n    <tbody>\r\n        <tr class=\\"head\\">\r\n            <td align=\\"center\\" width=\\"16\\"><strong>#</strong></td>\r\n            <td colspan=\\"2\\"><a title=\\"{TXT_MEDIA_SORT}\\" href=\\"{MEDIA_NAME_HREF}\\"><strong>{TXT_MEDIA_FILE_NAME}</strong></a> {MEDIA_NAME_ICON}</td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\"><a title=\\"{TXT_MEDIA_SORT}\\" href=\\"{MEDIA_SIZE_HREF}\\" name=\\"sort_size\\"><strong>{TXT_MEDIA_FILE_SIZE}</strong></a> {MEDIA_SIZE_ICON} </td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\"><a title=\\"{TXT_MEDIA_SORT}\\" href=\\"{MEDIA_TYPE_HREF}\\" name=\\"sort_type\\"><strong>{TXT_MEDIA_FILE_TYPE}</strong></a> {MEDIA_TYPE_ICON} </td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\"><a title=\\"{TXT_MEDIA_SORT}\\" href=\\"{MEDIA_DATE_HREF}\\" name=\\"sort_date\\"><strong>{TXT_MEDIA_FILE_DATE}</strong></a> {MEDIA_DATE_ICON} </td>\r\n        </tr>\r\n        <tr class=\\"row2\\" valign=\\"middle\\">\r\n            <td align=\\"center\\" width=\\"16\\"><img title=\\"base\\" height=\\"16\\" alt=\\"base\\" src=\\"images/modules/media/_base.gif\\" width=\\"16\\"/> </td>\r\n            <td colspan=\\"5\\"><strong><a title=\\"{MEDIA_TREE_NAV_MAIN}\\" href=\\"{MEDIA_TREE_NAV_MAIN_HREF}\\">{MEDIA_TREE_NAV_MAIN}</a></strong> <!-- BEGIN mediaTreeNavigation --><a href=\\"{MEDIA_TREE_NAV_DIR_HREF}\\">&nbsp;{MEDIA_TREE_NAV_DIR} /</a> <!-- END mediaTreeNavigation --></td>\r\n        </tr>\r\n        <!-- BEGIN mediaDirectoryTree -->\r\n        <tr class=\\"{MEDIA_DIR_TREE_ROW}\\" valign=\\"middle\\">\r\n            <td width=\\"16\\">&nbsp;</td>\r\n            <td align=\\"center\\" width=\\"16\\"><img title=\\"icon\\" height=\\"16\\" alt=\\"icon\\" src=\\"{MEDIA_FILE_ICON}\\" width=\\"16\\"/></td>\r\n            <td width=\\"100%\\"><a title=\\"{MEDIA_FILE_NAME}\\" href=\\"{MEDIA_FILE_NAME_HREF}\\">{MEDIA_FILE_NAME}</a></td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\">{MEDIA_FILE_SIZE}&nbsp;</td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\">{MEDIA_FILE_TYPE}&nbsp;</td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\">{MEDIA_FILE_DATE}&nbsp;</td>\r\n        </tr>\r\n        <!-- END mediaDirectoryTree --><!-- BEGIN mediaEmptyDirectory -->\r\n        <tr class=\\"row1\\">\r\n            <td>&nbsp;</td>\r\n            <td colspan=\\"5\\">{TXT_MEDIA_DIR_EMPTY}</td>\r\n        </tr>\r\n        <!-- END mediaEmptyDirectory -->\r\n    </tbody>\r\n</table>', 'Media Archiv #2', 'Media Archiv #2', 'Media Archiv #2', 'Media Archiv #2', 'index', '', '', 'y');
INSERT INTO `contrexx_content` VALUES (372, '&nbsp;', 'Downloads', 'Downloads', 'Media Archiv, Downloads', 'Media Archiv, Downloads', 'index', '', '', 'n');
INSERT INTO `contrexx_content` VALUES (374, '{MEDIA_JAVASCRIPT}\r\n<table id=\\"media\\" cellspacing=\\"0\\" cellpadding=\\"5\\" width=\\"100%\\" border=\\"0\\">\r\n    <tbody>\r\n        <tr class=\\"head\\">\r\n            <td align=\\"center\\" width=\\"16\\"><strong>#</strong></td>\r\n            <td colspan=\\"2\\"><a title=\\"{TXT_MEDIA_SORT}\\" href=\\"{MEDIA_NAME_HREF}\\"><strong>{TXT_MEDIA_FILE_NAME}</strong></a> {MEDIA_NAME_ICON}</td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\"><a title=\\"{TXT_MEDIA_SORT}\\" href=\\"{MEDIA_SIZE_HREF}\\" name=\\"sort_size\\"><strong>{TXT_MEDIA_FILE_SIZE}</strong></a> {MEDIA_SIZE_ICON} </td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\"><a title=\\"{TXT_MEDIA_SORT}\\" href=\\"{MEDIA_TYPE_HREF}\\" name=\\"sort_type\\"><strong>{TXT_MEDIA_FILE_TYPE}</strong></a> {MEDIA_TYPE_ICON} </td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\"><a title=\\"{TXT_MEDIA_SORT}\\" href=\\"{MEDIA_DATE_HREF}\\" name=\\"sort_date\\"><strong>{TXT_MEDIA_FILE_DATE}</strong></a> {MEDIA_DATE_ICON} </td>\r\n        </tr>\r\n        <tr class=\\"row2\\" valign=\\"middle\\">\r\n            <td align=\\"center\\" width=\\"16\\"><img title=\\"base\\" height=\\"16\\" alt=\\"base\\" src=\\"images/modules/media/_base.gif\\" width=\\"16\\"/> </td>\r\n            <td colspan=\\"5\\"><strong><a title=\\"{MEDIA_TREE_NAV_MAIN}\\" href=\\"{MEDIA_TREE_NAV_MAIN_HREF}\\">{MEDIA_TREE_NAV_MAIN}</a></strong> <!-- BEGIN mediaTreeNavigation --><a href=\\"{MEDIA_TREE_NAV_DIR_HREF}\\">&nbsp;{MEDIA_TREE_NAV_DIR} /</a> <!-- END mediaTreeNavigation --></td>\r\n        </tr>\r\n        <!-- BEGIN mediaDirectoryTree -->\r\n        <tr class=\\"{MEDIA_DIR_TREE_ROW}\\" valign=\\"middle\\">\r\n            <td width=\\"16\\">&nbsp;</td>\r\n            <td align=\\"center\\" width=\\"16\\"><img title=\\"icon\\" height=\\"16\\" alt=\\"icon\\" src=\\"{MEDIA_FILE_ICON}\\" width=\\"16\\"/></td>\r\n            <td width=\\"100%\\"><a title=\\"{MEDIA_FILE_NAME}\\" href=\\"{MEDIA_FILE_NAME_HREF}\\">{MEDIA_FILE_NAME}</a></td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\">{MEDIA_FILE_SIZE}&nbsp;</td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\">{MEDIA_FILE_TYPE}&nbsp;</td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\">{MEDIA_FILE_DATE}&nbsp;</td>\r\n        </tr>\r\n        <!-- END mediaDirectoryTree --><!-- BEGIN mediaEmptyDirectory -->\r\n        <tr class=\\"row1\\">\r\n            <td>&nbsp;</td>\r\n            <td colspan=\\"5\\">{TXT_MEDIA_DIR_EMPTY}</td>\r\n        </tr>\r\n        <!-- END mediaEmptyDirectory -->\r\n    </tbody>\r\n</table>', 'Media Archiv #3', 'Media Archiv #3', 'Media Archiv #3', 'Media Archiv #3', 'index', '', '', 'y');
INSERT INTO `contrexx_content` VALUES (389, 'Hier wird vorgef&uuml;hrt, wie Sie f&uuml;r jede eigene Seite ein anderes Design bestimmen k&ouml;nnen.<br /><br /><a href=\\"http://www.contrexx.com/index.php?section=media1&amp;path=/media/archive1/Opensource/Themes/\\" target=\\"_blank\\">Hier</a> k&ouml;nnen Sie <a href=\\"http://www.contrexx.com/index.php?section=media1&amp;path=/media/archive1/Opensource/Themes/\\" target=\\"_blank\\">Designs</a> herunterladen<br /> <br /> <a href=\\"http://www.contrexx.com/index.php?section=media1&amp;path=/media/archive1/Opensource/menuevorlagen/\\" target=\\"_blank\\">Hier</a> finden Sie <a href=\\"http://www.contrexx.com/index.php?section=media1&amp;path=/media/archive1/Opensource/menuevorlagen/\\" target=\\"_blank\\">Beispiel Dateien</a> f&uuml;r das Navigations Menue', 'Themes', 'Themes', 'Themes', 'Themes', 'index', '', '', 'n');
INSERT INTO `contrexx_content` VALUES (404, 'Newgen Theme angew&auml;hlt.', 'Newgen', 'Newgen', 'Newgen', 'Newgen', 'index', '', '', 'n');
INSERT INTO `contrexx_content` VALUES (472, '<b>{NEWS_STATUS_MESSAGE}</b>\r\n<form action=\\"index.php?section=news&amp;cmd=submit\\" method=\\"post\\">\r\n<table width=\\"100%\\" cellspacing=\\"0\\" cellpadding=\\"5\\" border=\\"0\\">\r\n    <tbody>\r\n        <tr>\r\n            <th colspan=\\"2\\">{TXT_NEWS_MESSAGE}</th>\r\n        </tr>\r\n        <tr>\r\n            <td width=\\"20%\\">{TXT_TITLE}</td>\r\n            <td width=\\"80%\\"><input type=\\"text\\" style=\\"width: 250px;\\" name=\\"newsTitle\\" value=\\"{NEWS_TITLE}\\" maxlength=\\"250\\" /></td>\r\n        </tr>\r\n        <tr>\r\n            <td width=\\"20%\\">{TXT_CATEGORY}</td>\r\n            <td width=\\"80%\\"><select style=\\"width: 250px;\\" name=\\"newsCat\\">{NEWS_CAT_MENU}</select></td>\r\n        </tr>\r\n        <tr>\r\n            <td width=\\"20%\\">{TXT_EXTERNAL_SOURCE}</td>\r\n            <td width=\\"80%\\"><input type=\\"text\\" style=\\"width: 250px;\\" name=\\"newsSource\\" value=\\"{NEWS_SOURCE}\\" maxlength=\\"250\\" /></td>\r\n        </tr>\r\n        <tr>\r\n            <td width=\\"20%\\">{TXT_LINK} #1</td>\r\n            <td width=\\"80%\\"><input type=\\"text\\" style=\\"width: 250px;\\" name=\\"newsUrl1\\" value=\\"{NEWS_URL1}\\" maxlength=\\"250\\" /></td>\r\n        </tr>\r\n        <tr>\r\n            <td width=\\"20%\\">{TXT_LINK} #2</td>\r\n            <td width=\\"80%\\"><input type=\\"text\\" style=\\"width: 250px;\\" name=\\"newsUrl2\\" value=\\"{NEWS_URL2}\\" maxlength=\\"250\\" /></td>\r\n        </tr>\r\n        <tr>\r\n            <th colspan=\\"2\\"><br />{TXT_NEWS_CONTENT}</th>\r\n        </tr>\r\n        <tr>\r\n            <td colspan=\\"2\\">{NEWS_TEXT}</td>\r\n        </tr>\r\n        <tr>\r\n            <td colspan=\\"2\\"><input type=\\"submit\\" name=\\"submitNews\\" value=\\"{TXT_SUBMIT_NEWS}\\" /></td>\r\n        </tr>\r\n    </tbody>\r\n</table>\r\n</form>', 'Newsanmelden', 'Newsanmelden', 'News anmelden', 'News anmelden', 'index', '', '', 'y');
INSERT INTO `contrexx_content` VALUES (482, '<form method=\\"post\\" action=\\"index.php?section=login&amp;cmd=lostpw\\">\r\n    <table width=\\"100%\\" cellspacing=\\"0\\" cellpadding=\\"2\\" border=\\"0\\" summary=\\"lost password form\\">\r\n        <tbody>\r\n            <!-- BEGIN login_lost_password -->\r\n            <tr>\r\n                <td width=\\"70%\\" colspan=\\"2\\">{TXT_LOST_PASSWORD_TEXT}</td>\r\n                <td width=\\"30%\\" rowspan=\\"3\\">&nbsp;&nbsp;&nbsp;&nbsp;<img width=\\"32\\" height=\\"32\\" align=\\"middle\\" src=\\"images/modules/login/lost_pw.gif\\" alt=\\"login key\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\" rowspan=\\"2\\" style=\\"vertical-align: top;\\">{TXT_EMAIL}:</td>\r\n                <td width=\\"40%\\"><input type=\\"text\\" tabindex=\\"1\\" maxlength=\\"255\\" style=\\"width: 100%;\\" size=\\"30\\" name=\\"email\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td><input type=\\"submit\\" tabindex=\\"2\\" name=\\"restore_pw\\" value=\\"{TXT_RESET_PASSWORD}\\" /></td>\r\n            </tr>\r\n            <!-- END login_lost_password -->\r\n            <tr>\r\n                <td colspan=\\"3\\" style=\\"color: rgb(255, 0, 0); font-weight: bold;\\"><br />{LOGIN_STATUS_MESSAGE}</td>\r\n            </tr>\r\n        </tbody>\r\n    </table>\r\n</form>', 'Passwort vergessen?', 'Passwort vergessen?', 'Passwort vergessen?', 'Passwort vergessen?', 'index', '', '', 'n');
INSERT INTO `contrexx_content` VALUES (483, '<form method=\\"post\\" action=\\"index.php?section=login&amp;cmd=resetpw\\">\r\n    <input type=\\"hidden\\" name=\\"restore_key\\" value=\\"{LOGIN_RESTORE_KEY}\\" /> <input type=\\"hidden\\" name=\\"username\\" value=\\"{LOGIN_USERNAME}\\" />\r\n    <table width=\\"100%\\" cellspacing=\\"0\\" cellpadding=\\"2\\" border=\\"0\\" summary=\\"set new password form\\">\r\n        <tbody>\r\n            <!-- BEGIN login_reset_password -->\r\n            <tr>\r\n                <td width=\\"70%\\" colspan=\\"2\\">{TXT_SET_PASSWORD_TEXT}</td>\r\n                <td width=\\"30%\\" rowspan=\\"5\\">&nbsp;&nbsp;&nbsp;&nbsp;<img width=\\"32\\" height=\\"32\\" align=\\"middle\\" src=\\"images/modules/login/lost_pw.gif\\" alt=\\"login key\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">{TXT_USERNAME}</td>\r\n                <td width=\\"40%\\">{LOGIN_USERNAME}</td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">{TXT_PASSWORD}&nbsp;{TXT_PASSWORD_MINIMAL_CHARACTERS}</td>\r\n                <td width=\\"40%\\"><input type=\\"password\\" maxlength=\\"50\\" size=\\"30\\" name=\\"password\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\" rowspan=\\"2\\" style=\\"vertical-align: top;\\">{TXT_VERIFY_PASSWORD}</td>\r\n                <td width=\\"40%\\"><input type=\\"password\\" maxlength=\\"50\\" size=\\"30\\" name=\\"password2\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td><input type=\\"submit\\" value=\\"{TXT_SET_NEW_PASSWORD}\\" name=\\"reset_password\\" /></td>\r\n            </tr>\r\n            <!-- END login_reset_password -->\r\n            <tr>\r\n                <td colspan=\\"2\\" style=\\"color: rgb(255, 0, 0); font-weight: bold;\\"><br />{LOGIN_STATUS_MESSAGE}</td>\r\n            </tr>\r\n        </tbody>\r\n    </table>\r\n</form>', 'Neues Passwort setzen', 'Neues Passwort setzen', 'Neues Passwort setzen', 'Neues Passwort setzen', 'index', '', '', 'n');
INSERT INTO `contrexx_content` VALUES (496, '<form name=\\"newsletter\\" action=\\"?section=newsletter&act=subscribe\\" method=\\"_post\\"><input type=\\"checkbox\\" name=\\"category_3\\" /> News über neue Produkte<br/><input type=\\"checkbox\\" name=\\"category_1\\" /> Newsletter in Deutsch<br/><input type=\\"checkbox\\" name=\\"category_2\\" /> Newsletter in Franz<br/><input type=\\"text\\" name=\\"email\\" value=\\"Ihre E-Mail Adresse\\" /><br/><input type=\\"submit\\" value=\\"Eintrag\\" /></form>', 'Newsletter', 'Newsletter', 'Newsletter', 'Newsletter', 'index', '', '', 'y');
INSERT INTO `contrexx_content` VALUES (501, '<img width=\\"100\\" height=\\"100\\" src=\\"images/modules/login/stop_hand.gif\\" alt=\\"\\" /><br />{TXT_NOT_ALLOWED_TO_ACCESS}', 'Zugriff verweigert', 'Zugriff verweigert', 'Zugriff verweigert', 'Zugriff verweigert', 'index', '', '', 'n');
INSERT INTO `contrexx_content` VALUES (531, '{COMMUNITY_STATUS_MESSAGE}', 'Benutzerkonto aktivieren', 'Benutzerkonto aktivieren', 'Benutzerkonto aktivieren', 'Benutzerkonto aktivieren', 'index', '', '', 'y');
INSERT INTO `contrexx_content` VALUES (529, '{COMMUNITY_STATUS_MESSAGE}<br /> <!-- BEGIN community_registration_form -->\r\n<form method=\\"post\\" action=\\"index.php?section=community&amp;cmd=register\\">\r\n    <table width=\\"100%\\" cellspacing=\\"5\\" cellpadding=\\"0\\" border=\\"0\\" summary=\\"registration\\">\r\n        <tbody>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">{TXT_LOGIN_NAME}:&nbsp;<font color=\\"red\\">*</font></td>\r\n                <td width=\\"70%\\"><input type=\\"text\\" value=\\"{COMMUNITY_USERNAME}\\" maxlength=\\"40\\" size=\\"30\\" name=\\"username\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">{TXT_LOGIN_PASSWORD}:&nbsp;<font color=\\"red\\">*</font></td>\r\n                <td width=\\"70%\\"><input type=\\"password\\" maxlength=\\"50\\" size=\\"30\\" name=\\"password\\" />&nbsp;{TXT_PASSWORD_MINIMAL_CHARACTERS}</td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">{TXT_VERIFY_PASSWORD}:&nbsp;<font color=\\"red\\">*</font></td>\r\n                <td width=\\"70%\\"><input type=\\"password\\" maxlength=\\"50\\" size=\\"30\\" name=\\"password2\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">{TXT_EMAIL}: <font color=\\"red\\">*</font></td>\r\n                <td width=\\"70%\\"><input type=\\"text\\" value=\\"{COMMUNITY_EMAIL}\\" maxlength=\\"255\\" size=\\"30\\" name=\\"email\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td colspan=\\"2\\">&nbsp;</td>\r\n            </tr>\r\n            <tr>\r\n                <td colspan=\\"2\\"><input type=\\"submit\\" name=\\"register\\" value=\\"{TXT_REGISTER}\\" /><br /><br />[<font color=\\"red\\">*</font>] {TXT_ALL_FIELDS_REQUIRED} {TXT_PASSWORD_NOT_USERNAME_TEXT}</td>\r\n            </tr>\r\n        </tbody>\r\n    </table>\r\n</form>\r\n<!-- END community_registration_form -->', 'Registration', 'Registration', 'Registration', 'Registration', 'index', '', '', 'n');
INSERT INTO `contrexx_content` VALUES (530, '<form action=\\"index.php?section=community&amp;cmd=profile\\" method=\\"post\\">\r\n    <table width=\\"100%\\" cellspacing=\\"5\\" cellpadding=\\"0\\" border=\\"0\\">\r\n        <tbody>\r\n            <tr>\r\n                <th colspan=\\"2\\">Pers&ouml;nliche Angaben</th>\r\n            </tr>\r\n            <tr>\r\n                <td colspan=\\"2\\">{COMMUNITY_STATUS_MESSAGE_PROFILE}</td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">Vorname</td>\r\n                <td width=\\"70%\\"><input type=\\"text\\" name=\\"firstname\\" value=\\"{COMMUNITY_FIRSTNAME}\\" tabindex=\\"1\\" style=\\"width: 250px;\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">Nachname</td>\r\n                <td width=\\"70%\\"><input type=\\"text\\" name=\\"lastname\\" value=\\"{COMMUNITY_LASTNAME}\\" tabindex=\\"2\\" style=\\"width: 250px;\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">Wohnort</td>\r\n                <td width=\\"70%\\"><input type=\\"text\\" name=\\"residence\\" value=\\"{COMMUNITY_RESIDENCE}\\" tabindex=\\"3\\" style=\\"width: 250px;\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">Beruf</td>\r\n                <td width=\\"70%\\"><input type=\\"text\\" name=\\"profession\\" value=\\"{COMMUNITY_PROFESSION}\\" tabindex=\\"4\\" style=\\"width: 250px;\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">Interessen</td>\r\n                <td width=\\"70%\\"><input type=\\"text\\" name=\\"interests\\" value=\\"{COMMUNITY_INTERESTS}\\" tabindex=\\"5\\" style=\\"width: 250px;\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">Webseite</td>\r\n                <td width=\\"70%\\"><input type=\\"text\\" name=\\"webpage\\" value=\\"{COMMUNITY_WEBPAGE}\\" tabindex=\\"6\\" style=\\"width: 250px;\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td colspan=\\"2\\"><input type=\\"submit\\" name=\\"change_profile\\" value=\\"Angaben Ändern\\" tabindex=\\"7\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td colspan=\\"2\\"><hr /></td>\r\n            </tr>\r\n        </tbody>\r\n    </table>\r\n</form>\r\n<form action=\\"index.php?section=community&amp;cmd=profile\\" method=\\"post\\">\r\n    <table width=\\"100%\\" cellspacing=\\"5\\" cellpadding=\\"0\\" border=\\"0\\">\r\n        <tbody>\r\n            <tr>\r\n                <th colspan=\\"2\\">E-Mail Adresse &auml;ndern</th>\r\n            </tr>\r\n        </tbody>\r\n        <tbody>\r\n            <tr>\r\n                <td colspan=\\"2\\">{COMMUNITY_STATUS_MESSAGE_EMAIL}</td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">Aktuelle E-Mail Adresse</td>\r\n                <td width=\\"70%\\">{COMMUNITY_EMAIL}</td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">Neue E-Mail Adresse</td>\r\n                <td width=\\"70%\\"><input type=\\"text\\" name=\\"email\\" value=\\"{COMMUNITY_NEW_EMAIL}\\" tabindex=\\"8\\" style=\\"width: 250px;\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">E-Mail best&auml;tigen</td>\r\n                <td width=\\"70%\\"><input type=\\"text\\" name=\\"email2\\" value=\\"\\" tabindex=\\"9\\" style=\\"width: 250px;\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td colspan=\\"2\\"><input type=\\"submit\\" name=\\"change_email\\" value=\\"E-Mail Ändern\\" tabindex=\\"10\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td colspan=\\"2\\"><hr /></td>\r\n            </tr>\r\n        </tbody>\r\n    </table>\r\n</form>\r\n<form action=\\"index.php?section=community&amp;cmd=profile\\" method=\\"post\\">\r\n    <table width=\\"100%\\" cellspacing=\\"5\\" cellpadding=\\"0\\" border=\\"0\\">\r\n        <tbody>\r\n            <tr>\r\n                <th colspan=\\"2\\">Kennwort &auml;ndern</th>\r\n            </tr>\r\n            <tr>\r\n                <td colspan=\\"2\\">{COMMUNITY_STATUS_MESSAGE_PASSWORD}</td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">Neues Kennwort</td>\r\n                <td width=\\"70%\\"><input type=\\"password\\" name=\\"password\\" value=\\"\\" tabindex=\\"11\\" style=\\"width: 250px;\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">Kennwort best&auml;tigen</td>\r\n                <td width=\\"70%\\"><input type=\\"password\\" name=\\"password2\\" value=\\"\\" tabindex=\\"12\\" style=\\"width: 250px;\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td colspan=\\"2\\"><input type=\\"submit\\" name=\\"change_password\\" value=\\"Kennwort Ändern\\" tabindex=\\"13\\" /></td>\r\n            </tr>\r\n        </tbody>\r\n    </table>\r\n</form>', 'Mein Profil', 'Mein Profil', 'Mein Profil', 'Mein Profil', 'index', '', '', 'n');
INSERT INTO `contrexx_content` VALUES (528, '<table width=\\"100%\\" cellspacing=\\"0\\" cellpadding=\\"0 border=\\"0\\">\r\n<tbody>\r\n<tr>\r\n<td><a href=\\"index.php?section=community&amp;cmd=register\\">Mitglied werden</a></td>\r\n</tr>\r\n<tr>\r\n<td><a href=\\"index.php?section=community&amp;cmd=profile\\">Mein Profil</a></td>\r\n</tr>\r\n</tbody>\r\n</table>', 'Community', 'Community', 'Community', 'Community', 'index', '', '', 'y');
INSERT INTO `contrexx_content` VALUES (533, '<table width=\\"100%\\">\r\n    <!-- BEGIN sitemap -->\r\n    <tbody>\r\n        <tr>\r\n            <td id=\\"{STYLE}\\">{SPACER}<a href=\\"{URL}\\" title=\\"{NAME}\\">{NAME}</a></td>\r\n        </tr>\r\n        <!-- END sitemap -->\r\n    </tbody>\r\n</table>', 'Sitemap', 'Sitemap', 'Sitemap', 'Sitemap', 'index', '', '', 'n');
INSERT INTO `contrexx_content` VALUES (582, '<h3>So k&ouml;nnen Sie ihredomain.ch &uuml;ber einen RSS-Newsreader lesen </h3>\r\nEin RSS-Newsfeed bietet aktuelle Nachrichten und die schnellsten Hintergr&uuml;nde und Analysen. Mit einem RSS-Newsfeed sind Sie st&auml;ndig auf dem Laufenden und erhalten die Schlagzeilen der neusten Nachrichten in Ihrem Feed-Reader. <br />\r\n<h3>Was ist RSS?</h3>\r\nRSS steht f&uuml;r Really Simple Syndication und ist ein plattform-unabh&auml;ngiges auf XML basierendes Format. Es wird genutzt, um Nachrichten und andere Webinhalte auszutauschen.<br /><br />RSS Newsfeeds sind auf Websites erkennbar an einem orangefarbenen Button mit der Aufschrift RSS oder XML. Diese Feeds beinhalten in der Regel den Titel sowie eine Kurzbeschreibung des Informationsangebotes. Der eigentliche Feed enth&auml;lt meist die &Uuml;berschriften und eine URL zum Volltext der Artikel.<br />\r\n<h3><a href=\\"http://demo.contrexx.com/feed/news_headlines_de.xml\\" target=\\"_blank\\"><img width=\\"80\\" height=\\"15\\" border=\\"0\\" alt=\\"RSS-Feed\\" src=\\"images/content/feed_rss.png\\" /></a>&nbsp;&nbsp;&nbsp; demo.contrexx.com RSS-Newsfeed </h3>\r\n<h3>Wie kann ich den Feed per Newsreader lesen?</h3>\r\n<p>Um einen RSS-Newsfeed lesen zu k&ouml;nnen, ben&ouml;tigen Sie einen RSS-Newsreader. Mit diesem Programm k&ouml;nnen Sie den Feed abbonieren und abrufen. Diese Feedreader sind meist Freeware und lassen sich sehr einfach bedienen. Hier einige der bekanntesten Feed-Reader:<br /> <br />&bull;&nbsp;&nbsp;&nbsp; AmphetaDesk - Windows, Mac OS, Linux * <br />&bull;&nbsp;&nbsp;&nbsp; Awasu - Windows * <br />&bull;&nbsp;&nbsp;&nbsp; BlogExpress - Windows * <br />&bull;&nbsp;&nbsp;&nbsp; FeedDemon - Windows <br />&bull;&nbsp;&nbsp;&nbsp; FeedOwl - Windows <br />&bull;&nbsp;&nbsp;&nbsp; FeedReader - Windows * <br />&bull;&nbsp;&nbsp;&nbsp; Liferea - Linux * <br />&bull;&nbsp;&nbsp;&nbsp; NetNewsWireLite - Mac OS X * <br />&bull;&nbsp;&nbsp;&nbsp; NewsDesk - Windows * <br />&bull;&nbsp;&nbsp;&nbsp; NewsGator - Windows (Outlook-Plugin) <br />&bull;&nbsp;&nbsp;&nbsp; NewsMonster - Windows, Mac OS X, Linux (Mozilla-Plugin) * <br />&bull;&nbsp;&nbsp;&nbsp; RSSOwl - Windows, Mac OS, Linux (Java)*<br />&bull;&nbsp;&nbsp;&nbsp; SlashDock - Mac OS X * <br /><br />* freeware Feedreader</p>\r\n<p>Den  demo.contrexx.com RSS-Newsfeed k&ouml;nnen sie bei den meisten Feedreadern mit einem Klick auf&nbsp; &bdquo;NEU / NEW&rdquo; hinzuf&uuml;gen. Dort tragen sie&nbsp; dann einfach den Link zum demo.contrexx.com Newsfeed ein (http://demo.contrexx.com/feed/news_headlines_de.xml).<br />    <br /> </p>\r\n<h3>HTML-Code</h3>\r\n<br /> F&uuml;gen Sie den folgenden Code in Ihre eigene Webseite ein, um das RSS Feed von {NEWS_HOSTNAME} auf Ihrer Webseite einzubinden:<br /> <br />\r\n<form>\r\n    <textarea style=\\"width: 98%; font-size: 95%;\\" wrap=\\"PHYSICAL\\" rows=\\"18\\" name=\\"code\\">{NEWS_RSS2JS_CODE}</textarea>     <br />     <br />  <input type=\\"button\\" value=\\"Alles markieren\\" onclick=\\"javascript:this.form.code.focus();this.form.code.select();\\" name=\\"button\\" />\r\n</form>\r\n<br /> Gem&auml;ss obigem Beispiel sieht die Ausgabe dann folgendermassen aus:<br /><br />\r\n<script language=\\"JavaScript\\" type=\\"text/javascript\\">\r\n<!--\r\n// Diese Variablen sind optional\r\nvar rssFeedFontColor = \\"#000000\\"; // Schriftfarbe\r\nvar rssFeedFontSize = 8; // Schriftgrösse\r\nvar rssFeedFont = \\"Verdana, Arial\\"; // Schriftart\r\nvar rssFeedLimit = 10; // Anzahl anzuzeigende Newsmeldungen\r\nvar rssFeedShowDate = true; // Datum der Newsmeldung anzeigen\r\nvar rssFeedTarget = \\"_blank\\"; // _blank | _parent | _self | _top\r\n// -->\r\n</script>\r\n<script type=\\"text/javascript\\" language=\\"JavaScript\\" src=\\"{NEWS_RSS2JS_URL}\\"></script>\r\n<noscript> &amp;lt;a href=&amp;quot;{NEWS_RSS_FEED_URL}&amp;quot;&amp;gt;{NEWS_HOSTNAME} - News anzeigen&amp;lt;/a&amp;gt; </noscript>', 'News Feed', 'News Feed', 'News Feed', 'News Feed', 'index', '', '', 'n');
INSERT INTO `contrexx_content` VALUES (99, 'Wiederhergestellte Seiten werden unter dieser Kategorie eingefügt.', 'Lost &amp; Found', 'Lost &amp; Found', 'Lost & Found', 'Lost & Found', 'index', '', '', 'y');
INSERT INTO `contrexx_content` VALUES (593, '{NEWSML_TITLE}<br /><br />{NEWSML_TEXT}<br /> <a href="javascript:window.history.back();">&lt; zur&uuml;ck</a>', 'Newsmeldung', 'Newsmeldung', 'Newsmeldung', 'Newsmeldung', 'index', '', '', 'y');
INSERT INTO `contrexx_content` VALUES (649, '{RECOM_STATUS} <!-- BEGIN recommend_form --> {RECOM_TEXT} {RECOM_SCRIPT}\r\n<form name=\\"recommend\\" method=\\"post\\" action=\\"index.php?section=recommend&amp;act=sendRecomm\\">\r\n    <input type=\\"hidden\\" value=\\"{RECOM_REFERER}\\" name=\\"uri\\" /> <input type=\\"hidden\\" value=\\"{RECOM_FEMALE_SALUTATION_TEXT}\\" name=\\"female_salutation_text\\" /> <input type=\\"hidden\\" value=\\"{RECOM_MALE_SALUTATION_TEXT}\\" name=\\"male_salutation_text\\" /> <input type=\\"hidden\\" value=\\"{RECOM_PREVIEW}\\" name=\\"preview_text\\" />\r\n    <table style=\\"width: 90%;\\">\r\n        <tbody>\r\n            <tr>\r\n                <td style=\\"width: 40%; padding-bottom: 15px;\\">{RECOM_TXT_RECEIVER_NAME}:</td>\r\n                <td style=\\"padding-bottom: 15px; width: 60%;\\"><input type=\\"text\\" onchange=\\"update();\\" style=\\"width: 100%;\\" value=\\"{RECOM_RECEIVER_NAME}\\" maxlength=\\"100\\" name=\\"receivername\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td style=\\"padding-bottom: 15px;\\">{RECOM_TXT_RECEIVER_MAIL}:</td>\r\n                <td style=\\"padding-bottom: 15px;\\"><input type=\\"text\\" onchange=\\"update();\\" style=\\"width: 100%;\\" value=\\"{RECOM_RECEIVER_MAIL}\\" maxlength=\\"100\\" name=\\"receivermail\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td valign=\\"top\\" style=\\"padding-bottom: 15px;\\">{RECOM_TXT_GENDER}:</td>\r\n                <td style=\\"padding-bottom: 15px;\\"><input type=\\"radio\\" onclick=\\"update();\\" value=\\"female\\" style=\\"border: medium none ; margin-left: 0px;\\" name=\\"gender\\" />{RECOM_TXT_FEMALE}<br /> 		<input type=\\"radio\\" onclick=\\"update();\\" value=\\"male\\" style=\\"border: medium none ; margin-left: 0px;\\" name=\\"gender\\" />{RECOM_TXT_MALE}</td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"100\\" style=\\"padding-bottom: 15px;\\">{RECOM_TXT_SENDER_NAME}:</td>\r\n                <td style=\\"padding-bottom: 15px;\\"><input type=\\"text\\" onchange=\\"update();\\" style=\\"width: 100%;\\" value=\\"{RECOM_SENDER_NAME}\\" maxlength=\\"100\\" name=\\"sendername\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td style=\\"padding-bottom: 15px;\\">{RECOM_TXT_SENDER_MAIL}:</td>\r\n                <td style=\\"padding-bottom: 15px;\\"><input type=\\"text\\" onchange=\\"update();\\" style=\\"width: 100%;\\" value=\\"{RECOM_SENDER_MAIL}\\" maxlength=\\"100\\" name=\\"sendermail\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td valign=\\"top\\">{RECOM_TXT_COMMENT}:</td>\r\n                <td style=\\"padding-bottom: 15px;\\"><textarea onchange=\\"update();\\" style=\\"width: 100%;\\" name=\\"comment\\" cols=\\"30\\" rows=\\"7\\">{RECOM_COMMENT}</textarea></td>\r\n            </tr>\r\n            <tr>\r\n                <td valign=\\"top\\">{RECOM_TXT_PREVIEW}:</td>\r\n                <td style=\\"padding-bottom: 15px;\\"> 	<textarea readonly=\\"\\" style=\\"width: 100%; height: 200px;\\" name=\\"preview\\"></textarea></td>\r\n            </tr>\r\n            <tr>\r\n                <td>&nbsp;</td>\r\n                <td><input type=\\"submit\\" value=\\"Senden\\" /> <input type=\\"reset\\" value=\\"Löschen\\" /></td>\r\n            </tr>\r\n        </tbody>\r\n    </table>\r\n</form>\r\n<!-- END recommend_form -->', 'Seite weiterempfehlen', 'Seite weiterempfehlen', 'Seite weiterempfehlen', 'Seite weiterempfehlen', 'index', '', '', 'n');
INSERT INTO `contrexx_content` VALUES (651, 'Kaelin Grey Theme angew&auml;hlt.', 'Kaelin Grey', 'Kaelin Grey', 'Kaelin Grey', 'Kaelin Grey', 'index', '', '', 'n');
INSERT INTO `contrexx_content` VALUES (668, '', 'Impressum', 'Impressum', 'Impressum', 'Impressum', 'index', '', '', 'n');
INSERT INTO `contrexx_content` VALUES (669, '{NEWSLETTER}', 'Newsletter', 'Newsletter', 'Newsletter', 'Newsletter', 'index', '', '', 'y');
INSERT INTO `contrexx_content` VALUES (670, '{NEWSLETTER}', 'Newsletter Profil bearbeiten', 'Newsletter Profil bearbeiten', 'Newsletter Profil bearbeiten', 'Newsletter Profil bearbeiten', 'index', '', '', 'y');
INSERT INTO `contrexx_content` VALUES (671, '{NEWSLETTER}', 'Newsletter abonnieren', 'Newsletter abonnieren', 'Newsletter abonnieren', 'Newsletter abonnieren', 'index', '', '', 'y');
INSERT INTO `contrexx_content` VALUES (672, '{NEWSLETTER}', 'Newsletter abmelden', 'Newsletter abmelden', 'Newsletter abmelden', 'Newsletter abmelden', 'index', '', '', 'y');
INSERT INTO `contrexx_content` VALUES (675, '{NEWSLETTER}', 'Newsletter bestätigen', 'Newsletter bestätigen', 'Newsletter bestätigen', 'Newsletter bestätigen', 'index', '', '', 'y');
INSERT INTO `contrexx_content` VALUES (676, 'Es gibt viele Gr&uuml;nde, warum man Internet Explorer nicht benutzen sollte:\r\n<ul>\r\n    <li>Internet Explorer ist <b>unsicher</b>. Es gibt Unmengen von Sicherheitsl&uuml;cken, durch die sich meistens lokale Daten einsehen und bearbeiten lassen. Wer mir das nicht glaubt, sollte sich unbedingt die <a href=\\"http://www.eggdrop.ch/noie/holes.html\\">Liste der gefundenen L&uuml;cken</a> anschauen.</li>\r\n    <li>Internet Explorer <b>zeigt moderne Seiten nicht immer korrekt an</b>. Andere Browser halten sich besser an die Standards und bieten somit dem Webdesigner mehr Freiheit, seine Seiten zu gestalten. Beispiel stufenlose PNG-Transparenz: S&auml;mtliche bekannte Browser k&ouml;nnen das - ausser Internet Explorer. Auch der CSS-Support beim Internet Explorer ist schlecht implementiert, auf <a href=\\"http://www.positioniseverything.net/explorer.html\\">http://www.positioniseverything.net/explorer.html</a> findet man einige der vielen CSS-Bugs.</li>\r\n    <li>Internet Explorer ist <b>nicht Open Source</b>, das heisst, dass sein Quellcode nicht frei verf&uuml;gbar ist. Bei Open Source-Browsern kann jeder den Quellcode anschauen, der Vorteil ist klar: Wenn mehr Leute den Quellcode anschauen, werden auch mehr Fehler gefunden, der Browser ist sicherer. Ausserdem kann jeder seine Erweiterungen dazugeben, wodurch der Browser immer moderner wird.</li>\r\n    <li>Internet Explorer ist <b>nicht plattformunabh&auml;ngig</b>. Microsoft liefert den Internet Explorer nur f&uuml;r Windows und Mac aus, die Mac-Version wird aber laut Microsoft (zum Gl&uuml;ck) nicht mehr weiterenwickelt. Neuere Internet Explorer sollen sogar eine aktuelle Windows-Version verlangen und zwar nicht, weil sie auf den &auml;lteren Betriebssystemversionen nicht mehr laufen w&uuml;rden, sondern weil Microsoft den Benutzer dazu zwingen will, sich eine neue Windows-Version zu kaufen. Alternative Browser laufen auf verschiedenen Plattformen (z.B. Linux, Mac, BeOS etc.)</li>\r\n    <li>Internet Explorer hat <b>wenig Komfort</b>. Das bekannteste Beispiel ist das sogenannte Tabbed-Browsing. Damit kann man mehrere Webseiten in einem Browserfenster ge&ouml;ffnet werden und erspart sich somit das Fensterchaos. Einige Browser haben beispielsweise auch einen Download- und Thememanager.</li>\r\n</ul>\r\nImmer noch nicht &uuml;berzeugt? Probier es doch einfach <b>jetzt</b> aus! Du kannst jederzeit kostenlos zu anderen Browsern wechseln.<br /><br /> <a target=\\"_blank\\" title=\\"Get Firefox - The Browser, Reloaded.\\" href=\\"http://getfirefox.com/\\"><img width=\\"80\\" height=\\"15\\" border=\\"0\\" alt=\\"Get Firefox\\" src=\\"http://www.mozilla.org/products/firefox/buttons/firefox_80x15.png\\" /></a>', 'Warum man den Internet Explorer nicht benutzen sollte', 'Warum man den Internet Explorer nicht benutzen sollte', 'Warum man den Internet Explorer nicht benutzen sollte', 'Warum man den Internet Explorer nicht benutzen sollte', 'index', '', '', 'n');
INSERT INTO `contrexx_content` VALUES (677, '<p>Hier sind nur einige wenige Alternativen aufgelistet. Alle diese Browser kann man kostenlos herunterladen:<br />   <br /> </p>\r\n<a href=\\"http://www.firefox-browser.de/\\" target=\\"_blank\\"><img border=\\"0\\" src=\\"images/content/browser/icon_firefox.png\\" alt=\\"\\" class=\\"img\\" style=\\"width: 32px; height: 32px;\\" /></a><b><br /> <br />Firefox</b> - <a href=\\"http://www.mozilla.org/products/firefox/\\">http://www.mozilla.org/products/firefox/</a> (auf Deutsch: <a href=\\"http://www.firefox-browser.de/\\">http://www.firefox-browser.de/</a>) <br /> Open Source, l&auml;uft auf Windows, Linux, Mac&nbsp;OS&nbsp;X und anderen; <b>sehr empfehlenswerter Browser!<br /></b><a href=\\"http://www.eggdrop.ch/texts/firefox/\\"><br /> <br /> <br /></a><a href=\\"http://www.mozilla.org/products/mozilla1.x/%20\\" target=\\"_blank\\"><img width=\\"32\\" height=\\"32\\" border=\\"0\\" src=\\"images/content/browser/icon_mozilla.png\\" alt=\\"\\" class=\\"img\\" /></a><b><br />   <br />Mozilla Suite</b> - <a href=\\"http://www.mozilla.org/products/mozilla1.x/\\">http://www.mozilla.org/products/mozilla1.x/</a> <br /> Open Source, l&auml;uft auf Windows, Linux, Mac&nbsp;OS&nbsp;X und anderen (wird momentan als <a href=\\"http://www.mozilla.org/projects/seamonkey/\\">Seamonkey</a> weiterentwickelt)<br /><br /> <br /> <br /> <a href=\\"http://www.apple.com/safari/%20\\" target=\\"_blank\\"><img width=\\"32\\" height=\\"32\\" border=\\"0\\" src=\\"images/content/browser/icon_safari.png\\" alt=\\"\\" class=\\"img\\" /></a><b><br /> <br />Safari</b> - <a href=\\"http://www.apple.com/safari/\\">http://www.apple.com/safari/</a> <br />Closed Source (die Rendering-Engine ist aber <a href=\\"http://webkit.opendarwin.org/\\">Open Source</a>), l&auml;uft nur auf Mac&nbsp;OS&nbsp;X; empfehlenswert f&uuml;r Mac-Benutzer.<br /><br /> <br /> <br /> <a href=\\"http://www.konqueror.org/%20\\" target=\\"_blank\\"><img width=\\"32\\" height=\\"32\\" border=\\"0\\" src=\\"images/content/browser/icon_konqueror.png\\" alt=\\"\\" class=\\"img\\" /></a><b><br />   <br />Konqueror</b> - <a href=\\"http://www.konqueror.org/\\">http://www.konqueror.org/</a> <br />Open Source, l&auml;uft auf Linux/UNIX<br /><br />   <br />   <br />   <a href=\\"http://www.opera.com/%20\\" target=\\"_blank\\"><img width=\\"32\\" height=\\"32\\" border=\\"0\\" src=\\"images/content/browser/icon_opera.png\\" alt=\\"\\" class=\\"img\\" /></a><b><br />   <br />Opera</b> - <a href=\\"http://www.opera.com/\\">http://www.opera.com/</a> <br />Closed Source, l&auml;uft auf Windows, Linux, Mac&nbsp;OS&nbsp;X und anderen; blendet seit Version 8.50 keine Werbung mehr ein', 'Internet Explorer Alternativen', 'Internet Explorer Alternativen', 'IE Alternativen', 'IE Alternativen', 'index', '', '', 'n');
INSERT INTO `contrexx_content` VALUES (682, 'Webstoff1 Theme angew&auml;hlt.', 'Webstoff1', 'Webstoff1', 'Webstoff1', 'Webstoff1', 'index', '', '', 'n');
INSERT INTO `contrexx_content` VALUES (683, 'Webstoff2 Theme angew&auml;hlt.', 'Webstoff2', 'Webstoff2', 'Webstoff2', 'Webstoff2', 'index', '', '', 'n');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_content_history`
-- 

DROP TABLE IF EXISTS `contrexx_content_history`;
CREATE TABLE IF NOT EXISTS `contrexx_content_history` (
  `id` smallint(8) unsigned NOT NULL default '0',
  `page_id` smallint(7) unsigned NOT NULL default '0',
  `content` mediumtext NOT NULL,
  `title` varchar(250) NOT NULL default '',
  `metatitle` varchar(250) NOT NULL default '',
  `metadesc` varchar(250) NOT NULL default '',
  `metakeys` varchar(250) NOT NULL default '',
  `metarobots` varchar(7) NOT NULL default 'index',
  `css_name` varchar(50) NOT NULL default '',
  `redirect` varchar(255) NOT NULL default '',
  `expertmode` set('y','n') NOT NULL default 'n',
  PRIMARY KEY  (`id`),
  FULLTEXT KEY `fulltextindex` (`title`,`content`),
  KEY `page_id` (`page_id`)
) TYPE=MyISAM;

-- 
-- Daten für Tabelle `contrexx_content_history`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_content_logfile`
-- 

DROP TABLE IF EXISTS `contrexx_content_logfile`;
CREATE TABLE IF NOT EXISTS `contrexx_content_logfile` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `action` set('new','update','delete') NOT NULL default 'new',
  `history_id` int(10) unsigned NOT NULL default '0',
  `is_validated` set('0','1') NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=3 ;

-- 
-- Daten für Tabelle `contrexx_content_logfile`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_content_navigation`
-- 

DROP TABLE IF EXISTS `contrexx_content_navigation`;
CREATE TABLE IF NOT EXISTS `contrexx_content_navigation` (
  `catid` smallint(6) unsigned NOT NULL auto_increment,
  `is_validated` set('0','1') NOT NULL default '1',
  `parcat` smallint(6) unsigned NOT NULL default '0',
  `catname` varchar(100) NOT NULL default '',
  `target` varchar(10) NOT NULL default '',
  `displayorder` smallint(6) unsigned NOT NULL default '1000',
  `displaystatus` set('on','off') NOT NULL default 'on',
  `activestatus` set('0','1') NOT NULL default '1',
  `cachingstatus` set('0','1') NOT NULL default '1',
  `username` varchar(40) NOT NULL default '',
  `changelog` int(14) default NULL,
  `cmd` varchar(50) NOT NULL default '',
  `lang` tinyint(2) unsigned NOT NULL default '1',
  `module` tinyint(2) unsigned NOT NULL default '0',
  `startdate` date NOT NULL default '0000-00-00',
  `enddate` date NOT NULL default '0000-00-00',
  `protected` tinyint(4) NOT NULL default '0',
  `frontend_access_id` int(11) unsigned NOT NULL default '0',
  `backend_access_id` int(11) unsigned NOT NULL default '0',
  `themes_id` int(4) NOT NULL default '0',
  PRIMARY KEY  (`catid`),
  UNIQUE KEY `catid` (`catid`),
  KEY `parcat` (`parcat`),
  KEY `module` (`module`),
  KEY `catname` (`catname`)
) TYPE=MyISAM AUTO_INCREMENT=687 ;

-- 
-- Daten für Tabelle `contrexx_content_navigation`
-- 

INSERT INTO `contrexx_content_navigation` VALUES (1, '1', 0, 'Willkommen', '', 0, 'on', '1', '1', 'system', 1134477339, '', 1, 15, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (534, '1', 37, 'Bildergalerie', '', 2, 'on', '1', '1', 'system', 1134683465, '', 1, 3, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (5, '1', 0, 'Suchen', '', 999, 'off', '1', '1', 'system', 1121763545, '', 1, 5, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (6, '1', 37, 'Kontakt', '', 5, 'on', '1', '1', 'system', 1135350411, '', 1, 6, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (7, '1', 6, 'Kontakt', '', 0, 'off', '1', '1', 'system', 1124180994, 'thanks', 1, 6, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (8, '1', 37, 'News', '', 1, 'on', '1', '1', 'system', 1127914039, '', 1, 8, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (9, '1', 8, 'Newsmeldung', '', 1, 'off', '1', '1', 'system', 1108115497, 'details', 1, 8, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (10, '1', 372, 'Media Archiv #1', '', 1, 'on', '1', '1', 'system', 1115200130, '', 1, 9, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (13, '1', 37, 'Gästebuch', '', 8, 'on', '1', '1', 'system', 1133274991, '', 1, 10, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (14, '1', 13, 'Eintragen', '', 1, 'on', '1', '1', 'system', 1108115497, 'post', 1, 10, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (16, '1', 0, 'Alert System', '', 999, 'off', '1', '1', 'system', 1108386244, '', 1, 13, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (17, '1', 0, 'Fehlermeldung', '', 999, 'off', '1', '1', 'system', 1109357223, '', 1, 14, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (509, '1', 502, 'Sonderangebote', '', 1, 'on', '1', '1', 'system', 1125066603, 'discounts', 1, 16, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (508, '1', 502, 'Ihr Warenkorb', '', 2, 'on', '1', '1', 'system', 1125066603, 'cart', 1, 16, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (507, '1', 502, 'Kontoangaben', '', 4, 'off', '1', '1', 'system', 1125066603, 'account', 1, 16, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (506, '1', 502, 'Bezahlung und Versand', '', 5, 'off', '1', '1', 'system', 1125066603, 'payment', 1, 16, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (505, '1', 502, 'Bestellen', '', 6, 'off', '1', '1', 'system', 1125066603, 'confirm', 1, 16, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (504, '1', 502, 'Detaillierte Produktedaten', '', 97, 'off', '1', '1', 'system', 1125066603, 'details', 1, 16, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (503, '1', 502, 'Transaktionsstatus', '', 7, 'off', '1', '1', 'system', 1129020069, 'success', 1, 16, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (502, '1', 0, 'Online Shop', '', 6, 'off', '1', '1', 'system', 1131215045, '', 1, 16, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (151, '1', 37, 'Voting', '', 111, 'on', '1', '1', 'system', 1112628836, '', 1, 17, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (31, '1', 0, 'Login', '', 999, 'off', '1', '1', 'system', 1124205070, '', 1, 18, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (32, '1', 37, 'Dokumenten System', '', 5, 'on', '1', '1', 'system', 1112780461, '', 1, 19, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (33, '1', 32, 'Documents', '', 0, 'off', '1', '1', 'system', 1108115497, 'details', 1, 19, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (203, '1', 37, 'News-Syndication', '', 4, 'on', '1', '1', 'system', 1134656951, '', 1, 22, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (371, '1', 372, 'Media Archiv #2', '', 2, 'on', '1', '1', 'system', 1115200097, '', 1, 24, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (374, '1', 372, 'Media Archiv #3', '', 3, 'on', '1', '1', 'system', 1115204166, '', 1, 25, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (472, '1', 8, 'News anmelden', '', 1, 'on', '1', '1', 'system', 1127997894, 'submit', 1, 8, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (482, '1', 31, 'Passwort vergessen?', '', 0, 'off', '1', '1', 'system', 1132050846, 'lostpw', 1, 18, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (483, '1', 31, 'Neues Passwort setzen', '', 0, 'off', '1', '1', 'system', 1124725810, 'resetpw', 1, 18, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (496, '1', 37, 'Newsletter', '', 7, 'off', '1', '1', 'system', 1134950300, '', 1, 0, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (37, '1', 0, 'Demo Module', '_self', 2, 'on', '1', '1', 'system', 1132063435, '', 1, 0, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (372, '1', 37, 'Downloads', '', 7, 'on', '1', '1', 'system', 1124721477, '', 1, 0, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (389, '1', 0, 'Themes', '', 3, 'on', '1', '1', 'system', 1135270239, '', 1, 0, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (404, '1', 389, 'Newgen', '', 1, 'on', '1', '1', 'system', 1124721394, '', 1, 0, '0000-00-00', '0000-00-00', 0, 0, 0, 17);
INSERT INTO `contrexx_content_navigation` VALUES (501, '1', 31, 'Zugriff verweigert', '', 0, 'off', '1', '1', 'system', 1124791578, 'noaccess', 1, 18, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (510, '1', 502, 'AGB', '', 98, 'on', '1', '1', 'system', 1134727659, 'terms', 1, 16, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (511, '1', 502, 'Mein Konto', '', 99, 'off', '1', '1', 'system', 1130519456, 'login', 1, 16, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (512, '1', 511, 'Konto Übersicht', '', 0, 'off', '1', '1', '', 1125066603, 'development', 1, 16, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (513, '1', 511, 'Passwort Hilfe', '', 0, 'off', '1', '1', '', 1125066603, 'sendpass', 1, 16, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (531, '1', 528, 'Benutzerkonto aktivieren', '', 0, 'off', '1', '1', 'system', 1127995943, 'activate', 1, 23, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (530, '1', 528, 'Mein Profil', '', 0, 'off', '1', '1', 'system', 1127995943, 'profile', 1, 23, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (529, '1', 528, 'Registration', '', 0, 'off', '1', '1', 'system', 1127995943, 'register', 1, 23, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (528, '1', 0, 'Community', '', 8, 'on', '1', '1', 'system', 1132050061, '', 1, 23, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (533, '1', 37, 'Sitemap', '', 111, 'on', '1', '1', 'system', 1135344626, '', 1, 11, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (582, '1', 8, 'News Feed', '', 1, 'on', '1', '1', 'system', 1135097523, 'feed', 1, 8, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (99, '1', 0, 'Lost & Found', '', 9999, 'off', '0', '1', 'system', 1132500836, 'lost_and_found', 1, 1, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (593, '1', 203, 'Newsmeldung', '', 1, 'off', '1', '1', 'system', 1135261361, 'newsML', 1, 22, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (649, '1', 37, 'Seite weiterempfehlen', '', 111, 'on', '1', '1', 'system', 1134026476, '', 1, 27, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (651, '1', 389, 'Kaelin Grey', '', 1, 'on', '1', '1', 'system', 1135327059, '', 1, 0, '0000-00-00', '0000-00-00', 0, 0, 0, 51);
INSERT INTO `contrexx_content_navigation` VALUES (668, '1', 0, 'Impressum', '', 8, 'on', '1', '1', 'system', 1135327218, '', 1, 0, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (669, '1', 37, 'Newsletter', '', 1, 'on', '1', '0', 'system', 1134951622, '', 1, 4, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (670, '1', 669, 'Newsletter Profil bearbeiten', '', 2, 'off', '1', '0', 'system', 1135244586, 'profile', 1, 4, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (671, '1', 669, 'Newsletter abonnieren', '', 1, 'off', '1', '0', 'system', 1135244049, 'subscribe', 1, 4, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (672, '1', 669, 'Newsletter abmelden', '', 1, 'off', '1', '0', 'system', 1135244506, 'unsubscribe', 1, 4, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (675, '1', 669, 'Newsletter bestätigen', '', 1, 'off', '1', '0', 'system', 1135244562, 'confirm', 1, 4, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (676, '1', 0, 'Browser Empfehlung', '', 4, 'on', '1', '1', 'system', 1135242028, '', 1, 0, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (677, '1', 676, 'IE Alternativen', '', 1, 'on', '1', '1', 'system', 1135239605, '', 1, 0, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (682, '1', 389, 'Webstoff1', '', 1, 'on', '1', '1', 'system', 1135259981, '', 1, 0, '0000-00-00', '0000-00-00', 0, 0, 0, 55);
INSERT INTO `contrexx_content_navigation` VALUES (683, '1', 389, 'Webstoff2', '', 1, 'on', '1', '1', 'system', 1135259959, '', 1, 0, '0000-00-00', '0000-00-00', 0, 0, 0, 56);
INSERT INTO `contrexx_content_navigation` VALUES (154, '1', 636, 'Drei Boxen Ansicht', '', 111, 'on', '1', '1', 'system', 1134579828, 'boxes', 1, 21, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (155, '1', 636, 'Veranstaltungs Information', '', 1, 'off', '1', '1', 'system', 1133789487, 'event', 1, 21, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (636, '1', 37, 'Kalender', '', 3, 'on', '1', '1', 'system', 1135432655, '', 1, 21, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content_navigation` VALUES (637, '1', 636, 'Auflistung aller Events', '', 1, 'on', '1', '1', 'system', 1134579795, 'eventlist', 1, 21, '0000-00-00', '0000-00-00', 0, 0, 0, 0);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_content_navigation_history`
-- 

DROP TABLE IF EXISTS `contrexx_content_navigation_history`;
CREATE TABLE IF NOT EXISTS `contrexx_content_navigation_history` (
  `id` smallint(7) unsigned NOT NULL auto_increment,
  `is_active` set('0','1') NOT NULL default '0',
  `catid` smallint(6) unsigned NOT NULL default '0',
  `parcat` smallint(6) unsigned NOT NULL default '0',
  `catname` varchar(100) NOT NULL default '',
  `target` varchar(10) NOT NULL default '',
  `displayorder` smallint(6) unsigned NOT NULL default '1000',
  `displaystatus` set('on','off') NOT NULL default 'on',
  `activestatus` set('0','1') NOT NULL default '1',
  `cachingstatus` set('0','1') NOT NULL default '1',
  `username` varchar(40) NOT NULL default '',
  `changelog` int(14) default NULL,
  `cmd` varchar(50) NOT NULL default '',
  `lang` tinyint(2) unsigned NOT NULL default '1',
  `module` tinyint(2) unsigned NOT NULL default '0',
  `startdate` date NOT NULL default '0000-00-00',
  `enddate` date NOT NULL default '0000-00-00',
  `protected` tinyint(4) NOT NULL default '0',
  `frontend_access_id` int(11) unsigned NOT NULL default '0',
  `backend_access_id` int(11) unsigned NOT NULL default '0',
  `themes_id` int(4) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `catid` (`catid`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `contrexx_content_navigation_history`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_ids`
-- 

DROP TABLE IF EXISTS `contrexx_ids`;
CREATE TABLE IF NOT EXISTS `contrexx_ids` (
  `id` smallint(11) NOT NULL auto_increment,
  `timestamp` int(14) default NULL,
  `type` varchar(100) NOT NULL default '',
  `remote_addr` varchar(15) default NULL,
  `http_x_forwarded_for` varchar(15) NOT NULL default '',
  `http_via` varchar(255) NOT NULL default '',
  `user` mediumtext,
  `gpcs` mediumtext NOT NULL,
  `file` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `contrexx_ids`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_languages`
-- 

DROP TABLE IF EXISTS `contrexx_languages`;
CREATE TABLE IF NOT EXISTS `contrexx_languages` (
  `id` tinyint(2) unsigned NOT NULL auto_increment,
  `lang` varchar(5) NOT NULL default '',
  `name` varchar(250) NOT NULL default '',
  `charset` varchar(20) NOT NULL default 'iso-8859-1',
  `themesid` tinyint(2) unsigned NOT NULL default '1',
  `print_themes_id` tinyint(2) unsigned NOT NULL default '1',
  `frontend` tinyint(1) unsigned NOT NULL default '0',
  `backend` tinyint(1) unsigned NOT NULL default '0',
  `is_default` set('true','false') NOT NULL default 'false',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `lang` (`lang`),
  KEY `defaultstatus` (`is_default`)
) TYPE=MyISAM AUTO_INCREMENT=11 ;

-- 
-- Daten für Tabelle `contrexx_languages`
-- 

INSERT INTO `contrexx_languages` VALUES (1, 'de', 'Deutsch', 'iso-8859-1', 55, 41, 1, 1, 'true');
INSERT INTO `contrexx_languages` VALUES (2, 'en', 'English', 'en-iso-8859-1', 55, 41, 0, 0, 'false');
INSERT INTO `contrexx_languages` VALUES (3, 'fr', 'French', 'fr-iso-8859-1', 55, 41, 0, 0, 'false');
INSERT INTO `contrexx_languages` VALUES (4, 'it', 'Italian', 'it-iso-8859-1', 55, 41, 0, 0, 'false');
INSERT INTO `contrexx_languages` VALUES (5, 'dk', 'Danish', 'da-iso-8859-1', 55, 41, 0, 0, 'false');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_log`
-- 

DROP TABLE IF EXISTS `contrexx_log`;
CREATE TABLE IF NOT EXISTS `contrexx_log` (
  `id` smallint(6) NOT NULL auto_increment,
  `userid` smallint(6) default NULL,
  `datetime` datetime default '0000-00-00 00:00:00',
  `useragent` varchar(100) default NULL,
  `userlanguage` varchar(25) default NULL,
  `remote_addr` varchar(250) default NULL,
  `remote_host` varchar(250) default NULL,
  `http_via` varchar(250) NOT NULL default '',
  `http_client_ip` varchar(250) NOT NULL default '',
  `http_x_forwarded_for` varchar(250) NOT NULL default '',
  `referer` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `contrexx_log`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_banner_groups`
-- 

DROP TABLE IF EXISTS `contrexx_module_banner_groups`;
CREATE TABLE IF NOT EXISTS `contrexx_module_banner_groups` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `placeholder_name` varchar(100) NOT NULL default '',
  `status` int(1) NOT NULL default '1',
  `is_deleted` set('0','1') NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=11 ;

-- 
-- Daten für Tabelle `contrexx_module_banner_groups`
-- 

INSERT INTO `contrexx_module_banner_groups` VALUES (1, 'Full Banner - Header', '468 x 60 Pixel', '[[BANNER_GROUP_1]]', 1, '0');
INSERT INTO `contrexx_module_banner_groups` VALUES (2, 'Full Banner - Footer', '468 x 60 Pixel', '[[BANNER_GROUP_2]]', 1, '0');
INSERT INTO `contrexx_module_banner_groups` VALUES (3, 'Half Banner', '234 x 60 Pixel', '[[BANNER_GROUP_3]]', 1, '0');
INSERT INTO `contrexx_module_banner_groups` VALUES (4, 'Button 1', '120 x 90 Pixel', '[[BANNER_GROUP_4]]', 1, '0');
INSERT INTO `contrexx_module_banner_groups` VALUES (6, 'Square Pop-Up', '250 x 250 Pixel', '[[BANNER_GROUP_6]]', 1, '0');
INSERT INTO `contrexx_module_banner_groups` VALUES (5, 'Button 2', '120 x 60 Pixel', '[[BANNER_GROUP_5]]', 1, '0');
INSERT INTO `contrexx_module_banner_groups` VALUES (7, 'Skyscraper', '120 x 600 Pixel', '[[BANNER_GROUP_7]]', 1, '0');
INSERT INTO `contrexx_module_banner_groups` VALUES (8, 'Wide Skyscraper', '160 x 600 Pixel', '[[BANNER_GROUP_8]]', 1, '0');
INSERT INTO `contrexx_module_banner_groups` VALUES (9, '', '', '[[BANNER_GROUP_9]]', 0, '1');
INSERT INTO `contrexx_module_banner_groups` VALUES (10, '', '', '[[BANNER_GROUP_10]]', 0, '1');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_banner_relations`
-- 

DROP TABLE IF EXISTS `contrexx_module_banner_relations`;
CREATE TABLE IF NOT EXISTS `contrexx_module_banner_relations` (
  `banner_id` int(11) NOT NULL default '0',
  `group_id` tinyint(4) NOT NULL default '0',
  `page_id` int(11) NOT NULL default '0',
  `type` set('content','news','teaser') NOT NULL default 'content',
  KEY `banner_id` (`banner_id`,`group_id`,`page_id`)
) TYPE=MyISAM;

-- 
-- Daten für Tabelle `contrexx_module_banner_relations`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_banner_settings`
-- 

DROP TABLE IF EXISTS `contrexx_module_banner_settings`;
CREATE TABLE IF NOT EXISTS `contrexx_module_banner_settings` (
  `name` varchar(50) NOT NULL default '',
  `value` varchar(250) NOT NULL default '',
  KEY `name` (`name`)
) TYPE=MyISAM;

-- 
-- Daten für Tabelle `contrexx_module_banner_settings`
-- 

INSERT INTO `contrexx_module_banner_settings` VALUES ('news_banner', '1');
INSERT INTO `contrexx_module_banner_settings` VALUES ('content_banner', '1');
INSERT INTO `contrexx_module_banner_settings` VALUES ('teaser_banner', '1');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_banner_system`
-- 

DROP TABLE IF EXISTS `contrexx_module_banner_system`;
CREATE TABLE IF NOT EXISTS `contrexx_module_banner_system` (
  `id` int(11) NOT NULL auto_increment,
  `parent_id` int(11) NOT NULL default '0',
  `name` varchar(150) NOT NULL default '',
  `banner_code` mediumtext NOT NULL,
  `status` int(1) NOT NULL default '1',
  `is_default` tinyint(2) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=30 ;

-- 
-- Daten für Tabelle `contrexx_module_banner_system`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_block_blocks`
-- 

DROP TABLE IF EXISTS `contrexx_module_block_blocks`;
CREATE TABLE IF NOT EXISTS `contrexx_module_block_blocks` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `content` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=2 ;

-- 
-- Daten für Tabelle `contrexx_module_block_blocks`
-- 

INSERT INTO `contrexx_module_block_blocks` VALUES (1, '<table width="150" cellspacing="0" cellpadding="0" border="0">\r\n    <tbody>\r\n        <tr>\r\n            <td> </td>\r\n        </tr>\r\n        <tr>\r\n            <td height="90" class="rechts"><a href="index.php?page=493"><strong>Kontaktpersonen:</strong><br />             Hier finden Sie<br />             s&auml;mtliche Kontaktpersonen</a></td>\r\n        </tr>\r\n        <tr>\r\n            <td height="90" class="rechts"><strong>Besuchszeiten:</strong><br />              Der Besuch<br />             von Heimbewohnern<br />             ist immer m&ouml;glich</td>\r\n        </tr>\r\n        <tr>\r\n            <td height="90" class="rechts"><a href="index.php?page=507">Hier finden Sie<br />    <strong>das Wichtigste in K&uuml;rze</strong> und einen Lageplan</a></td>\r\n        </tr>\r\n        <tr>\r\n            <td>&nbsp;</td>\r\n        </tr>\r\n    </tbody>\r\n</table>');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_calendar`
-- 

DROP TABLE IF EXISTS `contrexx_module_calendar`;
CREATE TABLE IF NOT EXISTS `contrexx_module_calendar` (
  `id` int(11) NOT NULL auto_increment,
  `active` tinyint(1) NOT NULL default '1',
  `catid` int(11) NOT NULL default '0',
  `startdate` int(14) default NULL,
  `enddate` int(14) default NULL,
  `priority` int(1) NOT NULL default '3',
  `name` varchar(100) NOT NULL default '',
  `comment` text NOT NULL,
  `place` varchar(25) NOT NULL default '',
  `info` varchar(255) NOT NULL default 'http://',
  PRIMARY KEY  (`id`),
  FULLTEXT KEY `name` (`name`,`comment`,`place`)
) TYPE=MyISAM AUTO_INCREMENT=47 ;

-- 
-- Daten für Tabelle `contrexx_module_calendar`
-- 

INSERT INTO `contrexx_module_calendar` VALUES (46, 1, 9, 1144130400, 1144168200, 0, 'Contrexx feiert einjähriges Jubiläum', 'Contrexx Open-Source CMS wurde von der Unternehmung Astalavista IT Engineering aus der Schweiz in der serverseitigen Skriptsprache PHP geschrieben, ist plattformunabh&auml;ngig und ben&ouml;tigt eine MySQL-Datenbank. Das erste Release wurde am 4. April 2005 ver&ouml;ffentlicht.', 'Thun', 'http://www.contrexx.com/');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_calendar_categories`
-- 

DROP TABLE IF EXISTS `contrexx_module_calendar_categories`;
CREATE TABLE IF NOT EXISTS `contrexx_module_calendar_categories` (
  `id` int(5) NOT NULL auto_increment,
  `name` varchar(150) NOT NULL default '',
  `status` int(1) NOT NULL default '0',
  `lang` int(1) NOT NULL default '0',
  `pos` int(5) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=10 ;

-- 
-- Daten für Tabelle `contrexx_module_calendar_categories`
-- 

INSERT INTO `contrexx_module_calendar_categories` VALUES (9, 'Jubiläum', 1, 1, 0);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_calendar_style`
-- 

DROP TABLE IF EXISTS `contrexx_module_calendar_style`;
CREATE TABLE IF NOT EXISTS `contrexx_module_calendar_style` (
  `id` int(11) NOT NULL auto_increment,
  `tableWidth` varchar(4) NOT NULL default '141',
  `tableHeight` varchar(4) NOT NULL default '92',
  `tableColor` varchar(7) NOT NULL default '',
  `tableBorder` int(11) NOT NULL default '0',
  `tableBorderColor` varchar(7) NOT NULL default '',
  `tableSpacing` int(11) NOT NULL default '0',
  `fontSize` int(11) NOT NULL default '10',
  `fontColor` varchar(7) NOT NULL default '',
  `numColor` varchar(7) NOT NULL default '',
  `normalDayColor` varchar(7) NOT NULL default '',
  `normalDayRollOverColor` varchar(7) NOT NULL default '',
  `curDayColor` varchar(7) NOT NULL default '',
  `curDayRollOverColor` varchar(7) NOT NULL default '',
  `eventDayColor` varchar(7) NOT NULL default '',
  `eventDayRollOverColor` varchar(7) NOT NULL default '',
  `shownEvents` int(4) NOT NULL default '10',
  `periodTime` varchar(5) NOT NULL default '00 23',
  `stdCat` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=3 ;

-- 
-- Daten für Tabelle `contrexx_module_calendar_style`
-- 

INSERT INTO `contrexx_module_calendar_style` VALUES (1, '141', '92', '#ffffff', 1, '#cccccc', 0, 10, '#000000', '#0000ff', '#ffffff', '#eeeeee', '#00ccff', '#0066ff', '#00cc00', '#009900', 10, '00 23', '');
INSERT INTO `contrexx_module_calendar_style` VALUES (2, '141', '92', '#ffffff', 1, '#cccccc', 0, 10, '#000000', '#0000ff', '#ffffff', '#eeeeee', '#00ccff', '#0066ff', '#00cc00', '#009900', 10, '05 19', '1>0 2>0');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_contact_form`
-- 

DROP TABLE IF EXISTS `contrexx_module_contact_form`;
CREATE TABLE IF NOT EXISTS `contrexx_module_contact_form` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `mails` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=2 ;

-- 
-- Daten für Tabelle `contrexx_module_contact_form`
-- 

INSERT INTO `contrexx_module_contact_form` VALUES (1, 'Standard Kontaktformular', '');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_contact_form_data`
-- 

DROP TABLE IF EXISTS `contrexx_module_contact_form_data`;
CREATE TABLE IF NOT EXISTS `contrexx_module_contact_form_data` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_form` int(10) unsigned NOT NULL default '0',
  `time` int(14) unsigned NOT NULL default '0',
  `host` varchar(255) NOT NULL default '',
  `lang` varchar(64) NOT NULL default '',
  `browser` varchar(255) NOT NULL default '',
  `ipaddress` varchar(15) NOT NULL default '',
  `data` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=6 ;

-- 
-- Daten für Tabelle `contrexx_module_contact_form_data`
-- 

INSERT INTO `contrexx_module_contact_form_data` VALUES (4, 0, 1135350379, 'adsl-84-227-230-178.adslplus.ch', 'de-ch,de-de;q=0.8,en;q=0.7,de;q=0.5,en-us;q=0.3,ru;q=0.2', 'Mozilla/5.0 (Windows; U; Windows NT 5.1; de; rv:1.8) Gecko/20051111 Firefox/1.5', '84.227.230.178', 'Y29udGFjdEZvcm1GaWVsZF8x,TmFtZQ==;Y29udGFjdEZvcm1GaWVsZF8y,RmlybWVubmFtZQ==;Y29udGFjdEZvcm1GaWVsZF8z,U3RyYXNzZQ==;Y29udGFjdEZvcm1GaWVsZF80,UExa;Y29udGFjdEZvcm1GaWVsZF81,T3J0;Y29udGFjdEZvcm1GaWVsZF82,TGFuZA==;Y29udGFjdEZvcm1GaWVsZF83,VGVsZWZvbg==;Y29udGFjdEZvcm1GaWVsZF84,RmF4;Y29udGFjdEZvcm1GaWVsZF85,RS1NYWls;Y29udGFjdEZvcm1GaWVsZF8xMA==,QmVtZXJrdW5nZW4=;Y29udGFjdEZvcm1GaWVsZF8xMQ==,L2ltYWdlcy9hdHRhY2gvcmllc2VucmFkLWF1Zi1tdWVobGVwbGF0ei5qcGc=');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_contact_form_field`
-- 

DROP TABLE IF EXISTS `contrexx_module_contact_form_field`;
CREATE TABLE IF NOT EXISTS `contrexx_module_contact_form_field` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_form` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `type` enum('text','checkbox','checkboxGroup','file','hidden','password','radio','select','textarea') NOT NULL default 'text',
  `attributes` text NOT NULL,
  `order_id` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=12 ;

-- 
-- Daten für Tabelle `contrexx_module_contact_form_field`
-- 

INSERT INTO `contrexx_module_contact_form_field` VALUES (1, 1, 'Name', 'text', '', 0);
INSERT INTO `contrexx_module_contact_form_field` VALUES (2, 1, 'Firmenname', 'text', '', 1);
INSERT INTO `contrexx_module_contact_form_field` VALUES (3, 1, 'Strasse', 'text', '', 2);
INSERT INTO `contrexx_module_contact_form_field` VALUES (4, 1, 'PLZ', 'text', '', 3);
INSERT INTO `contrexx_module_contact_form_field` VALUES (5, 1, 'Ort', 'text', '', 4);
INSERT INTO `contrexx_module_contact_form_field` VALUES (6, 1, 'Land', 'text', '', 5);
INSERT INTO `contrexx_module_contact_form_field` VALUES (7, 1, 'Telefon', 'text', '', 6);
INSERT INTO `contrexx_module_contact_form_field` VALUES (8, 1, 'Fax', 'text', '', 7);
INSERT INTO `contrexx_module_contact_form_field` VALUES (9, 1, 'E-Mail', 'text', '', 8);
INSERT INTO `contrexx_module_contact_form_field` VALUES (10, 1, 'Bemerkungen', 'textarea', '', 9);
INSERT INTO `contrexx_module_contact_form_field` VALUES (11, 1, 'Datei', 'file', '', 10);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_contact_settings`
-- 

DROP TABLE IF EXISTS `contrexx_module_contact_settings`;
CREATE TABLE IF NOT EXISTS `contrexx_module_contact_settings` (
  `setid` smallint(6) NOT NULL auto_increment,
  `setname` varchar(250) NOT NULL default '',
  `setvalue` text NOT NULL,
  `status` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`setid`)
) TYPE=MyISAM AUTO_INCREMENT=2 ;

-- 
-- Daten für Tabelle `contrexx_module_contact_settings`
-- 

INSERT INTO `contrexx_module_contact_settings` VALUES (1, 'fileUploadDepositionPath', '/images/attach', 1);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_docsys`
-- 

DROP TABLE IF EXISTS `contrexx_module_docsys`;
CREATE TABLE IF NOT EXISTS `contrexx_module_docsys` (
  `id` smallint(6) unsigned NOT NULL auto_increment,
  `date` int(14) default NULL,
  `title` varchar(250) NOT NULL default '',
  `author` varchar(150) NOT NULL default '',
  `text` mediumtext NOT NULL,
  `source` varchar(250) NOT NULL default '',
  `url1` varchar(250) NOT NULL default '',
  `url2` varchar(250) NOT NULL default '',
  `catid` tinyint(2) NOT NULL default '0',
  `lang` tinyint(2) NOT NULL default '0',
  `userid` smallint(6) NOT NULL default '0',
  `startdate` date NOT NULL default '0000-00-00',
  `enddate` date NOT NULL default '0000-00-00',
  `status` tinyint(4) NOT NULL default '1',
  `changelog` int(14) NOT NULL default '0',
  KEY `ID` (`id`),
  FULLTEXT KEY `newsindex` (`title`,`text`)
) TYPE=MyISAM AUTO_INCREMENT=26 ;

-- 
-- Daten für Tabelle `contrexx_module_docsys`
-- 

INSERT INTO `contrexx_module_docsys` VALUES (24, 1121680696, 'Test Dokument', 'system', 'Test Test Test\r\n\r\nZeilenumbruch', '', '', '', 21, 1, 1, '0000-00-00', '0000-00-00', 1, 1127890428);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_docsys_categories`
-- 

DROP TABLE IF EXISTS `contrexx_module_docsys_categories`;
CREATE TABLE IF NOT EXISTS `contrexx_module_docsys_categories` (
  `catid` tinyint(2) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `lang` tinyint(2) unsigned NOT NULL default '1',
  PRIMARY KEY  (`catid`)
) TYPE=MyISAM AUTO_INCREMENT=23 ;

-- 
-- Daten für Tabelle `contrexx_module_docsys_categories`
-- 

INSERT INTO `contrexx_module_docsys_categories` VALUES (21, 'Anleitungen', 1);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_feed_category`
-- 

DROP TABLE IF EXISTS `contrexx_module_feed_category`;
CREATE TABLE IF NOT EXISTS `contrexx_module_feed_category` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(150) NOT NULL default '',
  `status` int(1) NOT NULL default '1',
  `time` int(100) NOT NULL default '0',
  `lang` int(1) NOT NULL default '0',
  `pos` int(3) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=8 ;

-- 
-- Daten für Tabelle `contrexx_module_feed_category`
-- 

INSERT INTO `contrexx_module_feed_category` VALUES (5, 'Internet News', 1, 1134028532, 1, 0);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_feed_news`
-- 

DROP TABLE IF EXISTS `contrexx_module_feed_news`;
CREATE TABLE IF NOT EXISTS `contrexx_module_feed_news` (
  `id` int(11) NOT NULL auto_increment,
  `subid` int(11) NOT NULL default '0',
  `name` varchar(150) NOT NULL default '',
  `link` varchar(150) NOT NULL default '',
  `filename` varchar(150) NOT NULL default '',
  `articles` int(2) NOT NULL default '0',
  `cache` int(4) NOT NULL default '3600',
  `time` int(100) NOT NULL default '0',
  `image` int(1) NOT NULL default '1',
  `status` int(1) NOT NULL default '1',
  `pos` int(3) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=35 ;

-- 
-- Daten für Tabelle `contrexx_module_feed_news`
-- 

INSERT INTO `contrexx_module_feed_news` VALUES (1, 5, 'Golem', 'http://www.golem.de/backends/golemrdf_10.rdf', 'feed_1135353465_golemrdf_10.rdf', 50, 3600, 1135353465, 0, 1, 4);
INSERT INTO `contrexx_module_feed_news` VALUES (33, 5, 'Astalavista.ch News', 'http://www.astalavista.ch/feed/news_headlines_de.xml', 'feed_1135353455_news_headlines_de.xml', 50, 3600, 1135353455, 1, 1, 1);
INSERT INTO `contrexx_module_feed_news` VALUES (34, 5, 'Astalavista.com News', 'http://www.astalavista.com/feed/news_headlines_en.xml', 'feed_1135353448_news_headlines_en.xml', 50, 3600, 1135353448, 1, 1, 2);
INSERT INTO `contrexx_module_feed_news` VALUES (32, 5, 'Contrexx News', 'http://www.contrexx.com/feed/news_headlines_de.xml', 'feed_1135352940_news_headlines_de.xml', 50, 3600, 1135352940, 1, 1, 0);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_feed_newsml_categories`
-- 

DROP TABLE IF EXISTS `contrexx_module_feed_newsml_categories`;
CREATE TABLE IF NOT EXISTS `contrexx_module_feed_newsml_categories` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `providerId` text NOT NULL,
  `name` varchar(40) NOT NULL default '',
  `subjectCodes` text NOT NULL,
  `showSubjectCodes` enum('all','only','exclude') NOT NULL default 'all',
  `template` text NOT NULL,
  `limit` smallint(6) NOT NULL default '0',
  `auto_update` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM PACK_KEYS=0 AUTO_INCREMENT=7 ;

-- 
-- Daten für Tabelle `contrexx_module_feed_newsml_categories`
-- 

INSERT INTO `contrexx_module_feed_newsml_categories` VALUES (1, '1', 'sdaOnline_Ski_News', '15002000,15043000', 'only', '<div><b><a href="index.php?section=feed&amp;cmd=newsML&amp;id={ID}">{TITLE}</a></b></div>', 5, 0);
INSERT INTO `contrexx_module_feed_newsml_categories` VALUES (3, '1', 'sdaOnline_SI_News', '15002000', 'exclude', '<div style="margin-top:10px; border-bottom:1px dotted #000000;"><b><a href="index.php?section=feed&amp;cmd=newsML&amp;id={ID}">{TITLE}</a></b><br /><div style="color:#AAAAAA;">{DATE}</div>{TEXT}</div>', 10, 0);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_feed_newsml_documents`
-- 

DROP TABLE IF EXISTS `contrexx_module_feed_newsml_documents`;
CREATE TABLE IF NOT EXISTS `contrexx_module_feed_newsml_documents` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `publicIdentifier` text NOT NULL,
  `providerId` text NOT NULL,
  `dateId` int(8) unsigned NOT NULL default '0',
  `newsItemId` text NOT NULL,
  `revisionId` smallint(5) unsigned NOT NULL default '0',
  `thisRevisionDate` int(14) NOT NULL default '0',
  `urgency` smallint(5) unsigned NOT NULL default '0',
  `subjectCode` int(10) unsigned NOT NULL default '0',
  `headLine` varchar(67) NOT NULL default '',
  `dataContent` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=26 ;

-- 
-- Daten für Tabelle `contrexx_module_feed_newsml_documents`
-- 

INSERT INTO `contrexx_module_feed_newsml_documents` VALUES (5, 'urn:newsml:www.sda-ats.ch:20051027:brz001:2N', 'www.sda-ats.ch', 20051027, 'brz001', 2, 1130402266, 5, 15031000, 'Noch ein Sieg für Martin Gerber', '<p>EISHOCKEY - Zwei Tage nach seiner Glanzleistung beim 3:2 gegen die Ottawa Senators kam Martin Gerber mit den Carolina Hurricanes zu einem weiteren Sieg. Das Team mit dem Schweizer Nationalgoalie bezwang die Boston Bruins 4:3 nach Verlängerung. Wie schon gegen Ottawa lag Carolina zwischenzeitlich mit zwei Toren im Rückstand. Gerber zeigte 27 erfolgreiche Paraden.</p><p>Die weiteren Begegnungen in der Nacht auf Donnerstag: Buffalo Sabres - Washington Capitals 2:3. Columbus Blue Jackets - Nashville Predators 3:2 n.V. New Jersey Devils - Tampa Bay Lightning 3:6. Dallas Stars - San Jose Sharks 4:5 n.V. Anaheim Mighty Ducks - Calgary Flames 4:1.</p>');
INSERT INTO `contrexx_module_feed_newsml_documents` VALUES (6, 'urn:newsml:www.sda-ats.ch:20051027:brz002:1N', 'www.sda-ats.ch', 20051027, 'brz002', 1, 1130402036, 5, 15054000, 'Erneute Niederlage für Real Madrid', '<p>FUSSBALL - Real Madrid verpasste in der 9. Runde der Primera Division die Möglichkeit, sich an die Spitze zu setzen. Die Königlichen bezogen bei Deportivo La Coruña die zweite Niederlage in Serie und fielen nach dem 1:3 auf den 5. Platz zurück.</p><p>Ein herrlicher Schuss des kanadischen Mittelfeldspieler Julian de Guzman und zwei Kopfballtore von Zentralverteidiger Juanma liessen die Galicier gegen Real mit 3:0 in Führung gehen, ehe Raul fünf Minuten vor Schluss der Ehrentreffer mit einem Weitschuss über den zu weit vor dem Tor postierten Molina hinweg gelang. La Coruña wahrte damit seine seit 14 Jahren andauernde Ungeschlagenheit gegen Real im eigenen Stadion.</p><p>Nach der zweiten Niederlage in Serie liegt Real in der Tabelle drei Punkte hinterÜberraschungsleader Osasuna Pamplona und zwei Zähler hinter Getafe mit dem Schweizer Fabio Celestini zurück. Vor den Königlichen sind auch noch Erzrivale Barcelona und Aufsteiger Celta Vigo platziert.</p>');
INSERT INTO `contrexx_module_feed_newsml_documents` VALUES (7, 'urn:newsml:www.sda-ats.ch:20051027:brz004:1N', 'www.sda-ats.ch', 20051027, 'brz004', 1, 1130412131, 5, 15007000, 'Erster Titel für die Chicago White Sox seit 88 Jahren', '<p>BASEBALL - Die Chicago White Sox haben die Major League Baseball (MLB) gewonnen. Nach 88 Jahren und einem 4:0-Triumph im Final der World Series gegen Houston steht das Team von Coach Ozzie Guillen wieder zuoberst im Ranking der populären US-Sportart.</p><p>Vor dem 1:0 im vierten Finalspiel hatten die White Sox 5:3, 7:6 und 7:5 gewonnen. Im 101. Endspiel wars der insgesamt 19. "Sweep" (4:0-Sieg). Im klassischen Duell der Pitcher Brandon Backe (Houston) und Freddy Garcia (Chicago) entschied letztlich ein Single von "Rightfielder" Jermaine Dye, der später zum wertvollsten Spieler gewählt wurde, im achten Inning die Meisterschaft.</p><p>Mit dem Titelgewinn Chicagos hatte niemand gerechnet. Als 20:1-Aussenseiter war die Mannschaft aus "Windy City" vor der Saison gehandelt worden. Selbst in der Heimatstadt stehen 80 Prozent der Baseball-Fans hinter dem Lokalrivalen "Cubs".</p><p>Die White Soxüberraschten alle und sind erst das zweite Team nach den New York Yankees (1999), das die Playoffs mit einem Rekord von elf Siegen und nur einer Niederlage abschloss. Die entscheidenden Spiele aller Runden entschieden die Sox überdies auswärts.</p><p></p>');
INSERT INTO `contrexx_module_feed_newsml_documents` VALUES (8, 'urn:newsml:www.sda-ats.ch:20051027:brz005:1N', 'www.sda-ats.ch', 20051027, 'brz005', 1, 1130412633, 5, 15028000, 'Melanie Marti Zweite beim Gander-Memorial', '<p>TURNEN - Melanie Marti belegte beim Memorial Arthur Gander in Morges hinter der Rumänin Sandra Izbasa Platz 2. Bei den Männern gewann der chinesische Pferd-Olympiasieger Teng Haibin überlegen.</p><p>Beim Gander-Memorial, das an den ehemaligen Schweizer FIG-Präsidenten erinnert, turnen die Männer nur vier der sechs Disziplinen nach freier Wahl, die Frauen drei der vier Geräte. So konnte die Schweizer Meisterin Melanie Marti auf den Balken verzichten. Am Boden erhielt sie 9,20, am Stufenbarren die Höchstnote 9,175 und am Boden 9,05, was zum hohen Total von 27,425 führte. Die rumänische Juniorin Sandra Izbasa war mit 28,10 ausser Reichweite. Ariella Kaeslin, die zweite Schweizer Vertreterin, wurde im Feld der zehn Turnerinnen Letzte. Ein Sturz vom Balken warf sie zurück. Mit 9,40 erzielte die EM-Vierte beim Sprung die höchste Note.</p><p>Bei den Männern war Teng Haibin trotz missglückter Reckübung  eine Klasse für sich. Patrick Dominguez stürzte am Boden (8,95), weshalb er nur Siebenter wurde. Claudio Capelli holte sich mit seinem "Roche" beim Sprung die Höchstnote (9,525). Die Barrenübung misslang ihm aber, sodass er nicht über Rang9 hinauskam.</p><p>Morges VD. Memorial Arthur Gander. Männer (4 Disziplinen nach freier Wahl): 1. Haibin Teng (China) 36,85 (Pferd 9,75, Sprung 9,425, Barren  9,325, Reck 8,35). 2. Jordan Jovtschev (Bul) 36,05 (Boden 8,45, Ringe  9,625, Sprung 9,325, Barren 9,65). 3. Naoya Tsukahara (Jap) 35,85. 4. Thomas Andergassen (De) 35,75. 5. Rafael Martinez (Sp)35,60. 6. Yann Cucherat (Fr) 35,20. 7. Patrick Dominguez (Sz) 35,05 (Boden 8,95, Ringe 8,45, Sprung 9,50, Barren 8,15). 8. Andri Isajew (Ukr) 35,00. 9. Claudio Capelli (Sz) 34,375 (Pferd 8,15, Sprung 9,525, Barren 7,70, Boden 9,00). 10. Dimitri Karbanenko (Fr) 34,35. 11. Razvan Selariu (Rum) 31,20.</p><p>Frauen (3 Disziplinen nach freier Wahl): 1. Sandra Izbasa (Rum) 28,10 (Sprung 9,35, Balken 9,35, Boden 9,40). 2. Melanie Marti (Sz) 27,425 (Sprung 9,20, Stufenbarren 9,175, Boden 9,05). 3. Isabelle Severino (Fr) 26,50 (Stufenbarren 8,425, Balken 8,625, Boden 9,45). 4. Han Bing (China) 26,20. 5. Sakiko Okabe (Jap) 26,10. 6. Ariella Kaeslin (Sz) 26,00 (Sprung 9,40, Stufenbarren 8,60, Balken 8,00). 7. Marina Proskurina (Ukr) 25,975. 8. Anja Brinker (De) und Aisha Gerber (Ka) 25,925. 10. Emilie Lepennec (Fr) 25,625.</p>');
INSERT INTO `contrexx_module_feed_newsml_documents` VALUES (9, 'urn:newsml:www.sda-ats.ch:20051027:brz006:1N', 'www.sda-ats.ch', 20051027, 'brz006', 1, 1130414797, 4, 15031000, 'Jonas Hiller debütiert im Deutschland-Cup', '<p>EISHOCKEY - Nationalcoach Ralph Krueger hat für den Deutschland-Cup vom 8. bis 13. November in Zürich, Mannheim und Hannover 27 Spieler aufgeboten. Torhüter Jonas Hiller (Davos) wird erstmals zum Einsatz kommen.</p><p>Krueger setzt beim zweitletzten Zusammenzug vor Beginn der unmittelbaren Olympia-Vorbereitung auf bewährte Kräfte. 20 der 27 aufgebotenen Akteure nahmen letzten Frühling an der WM in Österreich teil. Ausserdem kehren die WM-erfahrenen Marcel Jenni, Martin Steinegger, Beat Gerber, Steve Hirschi und Daniel Steiner ins Nationalteam zurück.</p><p>Félicien Du Bois erhielt erstmals mitten in der Saison ein Aufgebot. Der junge Ambri-Verteidiger bestritt seine ersten neun Länderspiele allesamt im April, als ein Teil der Internationalen noch in den Playoffs engagiert war. Jonas Hiller kam bislang erst auf vier Länderspiele als Ersatzgoalie.</p><p>Das Schweizer Aufgebot. Tor (2): Marco Bührer (Bern), Jonas Hiller (Davos). -- Verteidigung (10): Goran Bezina (Servette), Severin Blindenbacher (ZSC Lions), Félicien Du Bois (Ambri), Beat Forster (ZSC Lions), Beat Gerber (Bern), Cyrill Geyer (Rapperswil), Steve Hirschi (Lugano), Mathias Seger (ZSC Lions), Martin Steinegger (SC Bern), Julien Vauclair (Lugano). -- Sturm (15): Andres Ambühl (Davos), Flavien Conne (Lugano), Patric Della Rossa (Zug), Paul Di Pietro (Zug), Patrick Fischer (Zug), Sandy Jeannin (Lugano), Marcel Jenni (Kloten), Romano Lemm (Kloten), Thierry Paterlini (ZSC Lions), Martin Plüss (Frölunda/Sd), Kevin Romy(Lugano), Ivo Rüthemann (Bern), Daniel Steiner (ZSC Lions), Adrian Wichser (ZSC Lions), Thomas Ziegler (Bern). -- Coach: Ralph Krueger.</p>');
INSERT INTO `contrexx_module_feed_newsml_documents` VALUES (10, 'urn:newsml:www.sda-ats.ch:20051027:brz007:1N', 'www.sda-ats.ch', 20051027, 'brz007', 1, 1130416763, 4, 15065000, 'Topgesetzter Guillermo Coria in Basel out', '<p>TENNIS - Die Weltnummer 1 und 2, Roger Federer und Rafael Nadal, erklärten wegen Verletzungen für die Swiss Indoors in Basel Forfait. Nun ist auch der topgesetzte Guillermo Coria (Arg) nicht mehr dabei. Er unterlag Kristof Vliegen im Achtelfinal 6:7, 5:7.</p><p>Zur Mittagszeit und vor spärlich besetzten Plätzen verpasste Coria am Ende des ersten Satzes, das Spiel in für ihn günstigere Bahnen zu lenken. Der Südamerikaner, die Nummer 7 im ATP-Ranking, vergab im zehnten Game des Startsatzes einen Satzball und verlor dann das Tiebreak sang- und klanglos 0:7. Im zweiten Durchgang geriet Coria vorab in der Schlussphase in Bedrängnis. Bei 5:4 für Vliegen konnte er zwei Break- und Matchbälle noch abwehren. Den dritten Matchball zum 7:5 verwertete dann der Belgier.</p><p>Qualifikant Vliegen (ATP 131), der in der 1. Runde den Schweizer Hoffnungsträger Stanislas Wawrinka eliminiert hatte, schaffte damit erstmals in dieser Saison den Sprung in die Viertelfinals eines ATP-Turniers. Der grösste Erfolg seiner Karriere ist die Woche in Basel bislang jedoch nicht. In Barcelona hat Vliegen vor einem Jahr den Halbfinal erreicht. Und in Adelaide stand er 2003 sogar im Final.</p>');
INSERT INTO `contrexx_module_feed_newsml_documents` VALUES (11, 'urn:newsml:www.sda-ats.ch:20051027:brz008:1N', 'www.sda-ats.ch', 20051027, 'brz008', 1, 1130422003, 5, 15019000, 'Tour de France 2006 ohne Mannschaftszeitfahren', '<p>RAD - Die 93. Tour de France (1. bis 23. Juli) führt von Strassburg im Gegenuhrzeigersinn über 3639 km nach Paris und ist damit etwas länger als die Austragung in diesem Jahr. Erstmals seit 1999 verzichten die Organisatoren auf ein Mannschaftszeitfahren.</p><p>Wie zuletzt immer in den geraden Jahren führt der Parcours zuerst in die Pyrenäen, wo nach der Überfahrt der klassischen Pässe Tourmalet, Col d''Aspin, Col de la Peyresourde und Col du Portillon erstmals die spanische Station Pla-de-Beret Zielort sein wird (11. Etappe).</p><p>Nach zweiÜberführungsetappen und einem Ruhetag in Gap kehrt die Tour nach einem Jahr Pause auf die Alpe d''Huez zurück. Die französische Skistation, deren 21 Kehren erstmals 1952 zu bewältigen waren, ist bereits zum 25. Mal Etappenort. Die Königsetappe folgt am nächsten Tag mit der Passage des gefürchteten Galibier, des Glandon und dem Schlussaufstieg nach La Toussuire, das ebenfalls seine Tour-Premiere erleben wird. Die dritte Alpenetappe führt nach Morzine mit dem Aufstieg auf den Col de Joux-Plane nur 12 km vor dem Ziel.</p><p>Nach dem 19 km langen Prolog sowie dem Mannschaftszeitfahren und nur einem Einzelzeitfahren in diesem Jahr hat die Tour 2006 wieder drei individuelle Prüfungen gegen die Uhr im Programm. Dem 7 km langen Aufgalopp am 1. Juli in Strassburg folgen die Einzelzeitfahren von Saint-Grégoire nach Rennes (52 km) in der 7. Etappe und von Le Creusot nach Montceau-les-Mines (56 km) traditionell im vorletzten Teilstück.</p><p>Neben dem Abstecher nach Spanien in den Pyrenäen gastiert die "Grande Boucle" im nächsten Jahr auch in Luxemburg (2. Etappe nach Esch-sur-Alzette), in Holland (3. Etappe nach Valkenburg) und in Belgien (4. Etappe mit Start in Huy).</p>');
INSERT INTO `contrexx_module_feed_newsml_documents` VALUES (12, 'urn:newsml:www.sda-ats.ch:20051027:brz009:2N', 'www.sda-ats.ch', 20051027, 'brz009', 2, 1130429554, 5, 15054000, 'News und Transfers', '<p>FUSSBALL - Davide Callà wurde für seine grobe Notbremse im verlorenen Cupspiel gegen den interregionalen Zweitligisten Küssnacht am Rigi für drei Spiele suspendiert. Der St. Galler Captain muss die Sperren in der Super League absitzen.</p><p>Die ManU-Ikone George Best liegt seit Anfang Oktober wegen einer Leberinfektion und inneren Blutungen auf der Intensivstation eines Londoner Spitals. Sein Zustand hat sich offenbar ernsthaft verschlechtert. Der 59-Jährige hatte sich vor drei Jahren wegen Alkoholexzessen einer Lebertransplantation unterziehen müssen. 1968 war der Nordire zum Fussballer Europas gewählt worden.</p><p>Irlands Absage</p><p>Irlands Verbandsführung hat beschlossen, den Mitte November in Dublin angesetzten Test gegen Dänemark ersatzlos zu streichen. Da die Iren seit der Entlassung von Coach Brian Kerr keinen Nachfolger gefunden haben, erachteten sie das Spiel als sinnlos. Die Dänen sprachen offen von Vertragsbruch.</p><p>Gascoigne trainiert Sechstligisten</p><p>Kettering Town hat Paul Gascoigne (38) als neuen Trainer engagiert. Zusammen mit dem ehemaligen Arsenal-Professional Paul Davis will der Ex-Internationale den sechstklassigen Dorfverein ins englische Profi-Geschäft führen. "Gazza" ist in den letzten Jahren schwer ins Abseits geraten und unternimmt in der Provinz einen neuen Anlauf. Zur persönlichen Blütezeit führte er England 1990 als genialer Mittelfeldregisseur in den WM-Halbfinal.</p><p>Squillaci vier Wochen out</p><p>Rund einen Monat lang muss Monaco ohne den Internationalen Sébastien Squillaci auskommen. Der Verteidiger erlitt beim 1:0-Sieg im Cup gegen Dijon eine Oberschenkelverletzung und fällt auch für die Länderspiele gegen Costa Rica und Deutschland aus.</p><p>Israels Angebot an Toppmöller</p><p>Klaus Toppmöller liegt eigenen Angaben zufolge ein konkretes Angebot des israelischen Verbands vor. Erst am Vortag war Avi Grant zurückgetreten. Der langjährige Bundesliga-Coach wird sich Anfang November entscheiden, erklärte aber, "es wäre für mich eine grosse Ehre, Israel zu trainieren".</p>');
INSERT INTO `contrexx_module_feed_newsml_documents` VALUES (13, 'urn:newsml:www.sda-ats.ch:20051027:brz010:2N', 'www.sda-ats.ch', 20051027, 'brz010', 2, 1130425389, 5, 15065000, 'Prominente Namen in Basel werden rarer - Auch Ferrero out', '<p>TENNIS - Die prominenten Namen werden im Tableau der Davidoff Swiss Indoors in Basel rarer. Wenige Stunden nach dem Out des topgesetzten Guillermo Coria hat es im Achtelfinal auch die Nummer 3 erwischt: Juan Carlos Ferrero (Sp) unterlag José Acasuso (Arg) 3:6, 7:6. 3:6.</p><p>Für Acasuso, die Nummer 49 der Welt, ist es in der zweiten Saisonhälfte erst das zweite Vorrücken unter die letzten acht. Im August stand er beim Masters-Series-Turnier von Cincinnati letztmals in den Viertelfinals -- und verlor gegen Roger Federer in zwei Sätzen. Aufsteigende Form hatte der Südamerikaner immerhin in der Vorwoche in Madrid bewiesen, als er in der 2. Runde den früheren French-Open-Sieger Gaston Gaudio schlug.</p><p>Ferrero (ATP 19) wird durch das frühe Ausscheiden im Ranking nicht weiter vorrücken. Er kämpfte zunächst zwar erfolgreich gegen ein noch schnelleres Ende, indem er im Tiebreak des zweiten Satzes auf dem Weg zum 9:7 einen Matchball abwehrte, doch im finalen Durchgang wurde er rasch durch ein Break auf die Verliererstrasse gedrängt. Und als Acasuso zum Matchgewinn aufschlagen konnte, besass Ferrero keine Chance mehr, die Wende herbeizuführen. Mit einem Servicewinner und einem Ass beendete Acasuso die Partie.</p>');
INSERT INTO `contrexx_module_feed_newsml_documents` VALUES (14, 'urn:newsml:www.sda-ats.ch:20051027:brz012:1N', 'www.sda-ats.ch', 20051027, 'brz012', 1, 1130425893, 5, 15000000, 'Peking 2008 ohne Frauen-Boxen', '<p>OLYMPISCHE SPIELE - Die IOC-Exekutive hat in Lausanne die Aufnahme des Frauen-Boxens ins Programm der Olympischen Spiele 2008 in Peking abgelehnt. Im Gegenzug beschloss die Versammlung, die Zahl der Wettbewerbe um einen auf 302 zu erhöhen.</p><p>Boxen bleibt damit die einzige olympische Männer-Domäne, nachdem in den letzten Jahren die Frauen im Ringen und im Gewichtheben zugelassen worden sind. Der Box-Weltverband hatte die Eingliederung von vier Frauen-Klassen beantragt.</p><p>In anderen Sparten nahm das IOC drei neue Wettkämpfe ins olympische Programm auf; in der Leichtathletik die 3000 m Steeple der Frauen, im Schwimmen die Langstrecken-Prüfung über 10 km für Männer und Frauen. Dafür müssen die Schützen auf zwei Disziplinen verzichten. Im Tischtennis werden die Doppel-Konkurrenzen beider Geschlechter durch Teamwettbewerbe ersetzt.</p><p>Bei den Frauen-Teamsportarten Fussball, Handball und Landhockey steigt die Zahl der teilnehmenden Teams von zehn auf zwölf. Die Obergrenze von 10 500 Athleten bleibt bestehen.</p><p>Insgesamt wurden zehn Anträge von Weltverbänden abgelehnt, unter anderem jene des Tennis-Verbandes für einen Mixed-Wettbewerb und im Schwimmen die Aufnahme weiterer Rennen über 50 m (Brust, Rücken, Delfin).</p>');
INSERT INTO `contrexx_module_feed_newsml_documents` VALUES (15, 'urn:newsml:www.sda-ats.ch:20051027:brz014:1N', 'www.sda-ats.ch', 20051027, 'brz014', 1, 1130431275, 4, 15039000, 'Organisator des GP Belgien konkurs', '<p>AUTOMOBIL - Die Veranstalterin des Formel-1-Grand-Prix von Belgien in Spa, die Firma Didier Defourny GPF1, hat vor dem Handelsgericht in Lüttich Konkurs anmelden müssen.</p><p>Der Kollaps kommt nicht unerwartet; die finanziellen Schwierigkeiten des Unternehmens waren seit geraumer Zeit bekannt.</p><p>Die Regierung der Region Wallonien, die für die Finanzierung des Grand Prix gerade steht, musste in der letzten Woche nachträglich die Garantiesumme von 15 Millionen Euro für das diesjährige Formel-1-Rennen in Francorchamps freigeben. Diesen Betrag hätte im Normalfall die "Didier Defourny GPF1" an Formula One Management (FOM) von Promoter Bernie Ecclestone für die GP-Austragungsrechte überwiesen müssen.</p>');
INSERT INTO `contrexx_module_feed_newsml_documents` VALUES (16, 'urn:newsml:www.sda-ats.ch:20051027:brz015:1N', 'www.sda-ats.ch', 20051027, 'brz015', 1, 1130431296, 5, 15014000, 'WM-Kampf im Gefängnis', '<p>BOXEN - Eine verurteilte Drogen-Dealerin will sich in Thailand den vakanten Titel des World Boxing Council (WBC) im "Strohgewicht" erkämpfen. Da Nongmai Sor Siriporn (26) inhaftiert ist, wurde der Kampf gegen Carina Moreno (USA) in die Haftanstalt verlegt.</p><p>Die beiden Boxerinnen steigen am 7. November im Prathum Thani-Frauengefängnis in Bangkok in den Ring. Moreno ist die aktuelle Nummer zwei, Nongmai Dritte der Rangliste des World Boxing Council (WBC).</p>');
INSERT INTO `contrexx_module_feed_newsml_documents` VALUES (17, 'urn:newsml:www.sda-ats.ch:20051027:brz016:1N', 'www.sda-ats.ch', 20051027, 'brz016', 1, 1130435002, 5, 15067000, 'Könizer Erfolg zum Auftakt', '<p>VOLLEYBALL - Nach Voléro Zürich am Mittwoch haben auch die Frauen aus Köniz ihre erste Partie in der Hauptrunde des Top Teams Cup gewonnen. In Minsk (WRuss) siegten die Bernerinnen 3:1.</p><p>Den Erfolg gegen die junge und talentierte Mannschaft musste sich das Team von Trainer Atay Dogu hart erkämpfen. Öfters brauchte Köniz gegen die defensiv starken Gegnerinnen mehrere Anläufe, um die Bälle erfolgreich zu verwerten. Nach dem (zu) leicht gewonnenen ersten Satz (25:17) brach der frühere Serienmeister im zweiten Durchgang ein. Bis zu acht Punkte (12:20) lag Köniz im Rückstand und verlor den Satz schliesslich 21:25, nachdem sie noch bis auf 21:23 heran kamen und so Moral tankten für den weiteren Verlauf der Partie.</p>');
INSERT INTO `contrexx_module_feed_newsml_documents` VALUES (18, 'urn:newsml:www.sda-ats.ch:20051027:brz017:1N', 'www.sda-ats.ch', 20051027, 'brz017', 1, 1130436782, 5, 15029000, 'Wacker bis 2008 mit Bachmann und Lee', '<p>HANDBALL - Wacker Thun hat zwei wichtige Führungspersonen langfristig gebunden. Der SHL-Dritte verlängerte die Verträge von Trainer Peter Bachmann (48) und dem südkoreanischen Spitzengoalie Suik-Houng Lee (34) vorzeitig bis Ende der Saison 2007/08.</p><p>Unter Bachmanns Führung resultierte für die Berner Oberländer mit dem Gewinn des Challenge Cups der erste Europacup-Sieg einer Schweizer Handball-Mannschaft. Zudem gewann der 48-jährige Sekundarlehrer mit Wacker, das er zuerst unter Halid Demirovic (von 1997 bis 1999) mitcoachte und nun in seiner sechsten Saisonals Cheftrainer betreut, zwei Mal die SHL-Qualifikation sowie 2002 den Schweizer Cup. Lee stiess im Sommer 2001 vom TV Suhr zu den Thunern und avancierte sofort zu einem Teamleader, der am Europacup-Triumph im Final gegen den portugiesischen Vertreter Braga im Frühling massgeblichen Anteil hatte.</p><p>Auch betreffend der Sporthalle Lachen vermeldet Wacker positive Neuigkeiten. Die Arena, die seit dem Hochwasser im August nicht bespielbar ist, sollte rechtzeitig für das erste Meisterschaftsspiel im Februar 2006 wieder bezugsbereit sein. Bis am 20. November sollen der Trockungsprozess im Lachen beendet und bis Weihnachten der neue Bodenbelag gelegt sein.</p>');
INSERT INTO `contrexx_module_feed_newsml_documents` VALUES (19, 'urn:newsml:www.sda-ats.ch:20051027:brz018:1N', 'www.sda-ats.ch', 20051027, 'brz018', 1, 1130437488, 3, 15065000, 'David Nalbandian nun der grosse Favorit auf Basel-Titel', '<p>TENNIS - Nach dem Favoritensterben vom Donnerstag ist David Nalbandian nun der erste Anwärter auf den Titel bei den Davidoff Swiss Indoors in Basel. Der als Nummer 2 gesetzte Argentinier schlug in den Achtelfinals den Deutschen Florian Mayer mühelos 6:3, 6:2.</p><p>Nachdem in Nalbandians Tableau-Hälfte Juan Carlos Ferrero und Tommy Haas in der Runde der letzten 16 scheiterten, scheint der Weg für den "Stier von Cordoba" zur vierten Final-Qualifikation in Basel in Folge frei. In den Viertelfinals trifft die Weltnummer 10 auf den Thailänder Paradorn Srichaphan (ATP 47), der TitelverteidigerJiri Novak (Tsch/ATP 35) ebenso deutlich eliminierte - 6:3, 6:2.</p><p>Von Mayer (ATP 81) wurde Nalbandian nur zu Beginn gefordert. Rasch führte der Deutsche mit Break-Vorsprung 3:0, konnte die Führung aber nicht konservieren und gab acht Games in Folge ab. Das entscheidende Break im Startsatz kassierte der 22-jährige Bayer zum 3:5, als er bei 40:40 einen Ball unbedrängt ins Netz schlug und dem Südamerikaner so den Weg zum Satzgewinn ebnete.</p><p></p>');
INSERT INTO `contrexx_module_feed_newsml_documents` VALUES (20, 'urn:newsml:www.sda-ats.ch:20051027:brz019:1N', 'www.sda-ats.ch', 20051027, 'brz019', 1, 1130441061, 4, 15065000, 'Patty Schnyder locker im Viertelfinal', '<p>TENNIS - Patty Schnyder hat bei ihrem Einstand im WTA-Turnier in Linz locker die Viertelfinals erreicht. Die Baslerin bezwang im Duell der Linkshänderinnen die Tschechin Iveta Benesova, die Nummer 57 des Weltrankings, in 57 Minuten mit 6:2 und 6:2.</p><p>Im Viertelfinal trifft die Finalistin von Zürich auf die als Nummer 6 gesetzte Slowakin Daniela Hantuchova, die nach der Aufgabe der Spanierin Conchita Martinez kampflos im Tableau des mit 585 000 Dollar dotierten Turniers vorrückte. Die ehemalige Wimbledon-Finalistin musste im dritten Satz wegen einer Ellbogenverletzung passen. Schnyderund Hantuchova standen sich bislang 12 Mal gegenüber. Patty führt im direkten Vergleich mit 7:5, verlor aber die beiden letzten Duelle in diesem Jahr sowohl in New Haven als auch in Filderstadt.</p><p>Schnyders 370. Sieg auf der Tour war gegen Benesova nie gefährdet und brachte ihr 57 weitere Punkte in der Jahreswertung ein, womit die Teilnahme am Masters Race von Mitte November in Los Angeles so gut wie gesichert erscheint. Die äusserst solid auftretende Baslerin erhöhte den Vorsprung auf Venus Williams auf 143 Punkte, und die derzeit neuntplatzierteElena Dementiewa (Russ) weist bereits einen Rückstand von 242 Punkten auf.</p><p>Schnyder schlug gegen Benesova schon im ersten Satz fünf Asse und verzeichnete generell zahlreiche Servicewinner. Das erste Break gelang ihr zum 3:2, das zweite zum 5:2. Im zweiten Satz wehrte Schnyder beim Stande von 0:1 zwei Breakbälle der 22-jährigen Tschechin ab, die auch in den beiden bisherigen Duellen nie einen Satz gegen die Top-Ten-Spielerin der Schweiz gewonnen hatte. Nach dem 2:2 war der Widerstand der Tschechin gebrochen. Gegen das variable Spiel der Baslerin fand sie keine Mittel.</p>');
INSERT INTO `contrexx_module_feed_newsml_documents` VALUES (21, 'urn:newsml:www.sda-ats.ch:20051027:brz020:1N', 'www.sda-ats.ch', 20051027, 'brz020', 1, 1130442493, 5, 15065000, 'Wawrinka und Bastl in Paris', '<p>TENNIS - Stanislas Wawrinka (ATP 60) und wohl auch George Bastl (ATP 124) bestreiten am Wochenende die Qualifikation für das Masters-Series-Turnier in Paris-Bercy, den letzten ATP-Event vor dem abschliessenden Masters in Schanghai.</p><p>Wawrinka bekam von denÄrzten nach einer MRI-Kontrolle, die den Verdacht einer Halswirbel-Verletzung nicht bestätigte, grünes Licht für den Start in der französischen Metropole. Sollte der Waadtländer im Qualifikationstableau gesetzt sein, kann sich Bastl seines Platzes im Feld nicht sicher sein. Nach der guten Leistung in Basel gegen den Tschechen Tomas Berdych nimmt Bastl nun das Risiko auf sich, unter Umständen vergeblich nach Paris gereist zu sein.</p>');
INSERT INTO `contrexx_module_feed_newsml_documents` VALUES (22, 'urn:newsml:www.sda-ats.ch:20051027:brz021:1N', 'www.sda-ats.ch', 20051027, 'brz021', 1, 1130442742, 5, 15065000, 'Federer wird für den Gewinn des Sport-Oscars geehrt =', '<p>TENNIS - Wegen eines Bänderrisses im rechten Fuss konnte Roger Federer bei seinem Heimturnier in der Basler St.-Jakob-Halle nicht antreten. Am Sonntag soll die Weltnummer 1 vor seinem Publikum nun doch noch zu einem kurzen Auftritt kommen.</p><p>Vor dem Final wird er für den im Mai gewonnenen "Sport-Oscar" geehrt. Federer war im Frühling im portugiesischen Touristenort Estoril mit dem prestigeträchtigen Laureus World Sports Award ausgezeichnet worden. In der Wahl zum Weltsportler des Jahres 2004 hatte er Lance Armstrong, Michael Schumacher, Michael Phelps, Valentino Rossi und Hicham El Gerrouj hinter sich gelassen.</p><p>Anschliessend an seinen Kurzauftritt vor den Fans in Basel wird "King Roger" nach Zürich chauffiert, wo er Studiogast im Sportpanorama von SF 1 (ab 18.15 Uhr) sein wird.</p>');
INSERT INTO `contrexx_module_feed_newsml_documents` VALUES (23, 'urn:newsml:www.sda-ats.ch:20051027:brz022:1N', 'www.sda-ats.ch', 20051027, 'brz022', 1, 1130444526, 5, 15031000, 'Viertes Saisontor für Martin Plüss', '<p>EISHOCKEY - Martin Plüss verteidigte mit Västra Frölunda in der 14. Runde der schwedischen Meisterschaft die Leaderposition.</p><p>Der Titelverteidiger aus Göteborg gewann nicht zuletzt dank dem vierten Saisontor des Schweizer Internationalen (zum 2:0) gegen Brynäs 3:1.</p><p>Weil Verfolger HV 71 gegen Djurgarden Stockholm eine 2:3-Heimniederlage nach Verlängerung bezog, beträgt Västras Vorsprung neu zwei Punkte.</p><p>Schweden. Elitserien. 14. Runde: Västra Frölunda (mit Plüss/Tor zum 2:0) - Brynäs 3:1. - Ranglistenspitze (je 14 Spiele): 1. Västra Frölunda 35. 2. HV 71 Jönköping 33 (44:20). 3. Linköping 33 (38:23). 4. Färjestad 24. Ferner: 8. Brynäs 17.</p>');
INSERT INTO `contrexx_module_feed_newsml_documents` VALUES (25, 'urn:newsml:www.sda-ats.ch:20051028:brz001:1N', 'www.sda-ats.ch', 20051028, 'brz001', 1, 1130485398, 5, 15031000, 'David Aebischer zaubert für Colorado', '<p>EISHOCKEY - David Aebischer glänzte beim 6:2-Sieg der Colorado Avalanche gegen Vancouver mit 40 abgewehrten Schüssen und war damit massgeblich am Abbruch der Siegesserie der Canucks beteiligt.</p><p>Vor dem ersten der beiden Duelle innert drei Tagen hatte Vancouver sechs Siege in Serie verzeichnet. Aebischer glänzte vor allem im Schlussdrittel, als Colorado dank Treffern von Joe Sakic, Steve Konowalchuk und Ian Laperriere, Pierre Turgeon (2) und dem 200. Karrieretor von Milan Hejduk bereits mit 6:1 in Führung lag. Die Canucks deckten das Tor von Aebischer mit insgesamt 23 Schüssen ein, von denen dank der Klasse des Fribourgers aber nur einer von Ryan Kesler den Weg ins Netz fand.</p><p>Mark Streit kam bei der 3:4-Niederlage der Montreal Canadiens gegen Ottawa nur zu einem Kurzeinsatz von 77 Sekunden.</p><p>National Hockey League (NHL). Die Ergebnisse aus der Nacht auf Freitag: Philadelphia Flyers - Florida Panthers 5:4 n.V. New York Rangers - New York Islanders 3:1. Boston Bruins - Toronto Maple Leafs 2:1. Detroit Red Wings - Chicago Blackhawks 5:2. Ottawa Senators - Montreal Canadiens (1:17 Minuten mit Streit) 4:3 n.V. Pittsburgh Penguins - Atlanta Trashers 7:5. Colorado Avalanche (mit Aebischer/40 Paraden) - Vancouver Canucks 6:2. Phoenix Coyotes - Calgary Flames 3:2.</p>');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_feed_newsml_providers`
-- 

DROP TABLE IF EXISTS `contrexx_module_feed_newsml_providers`;
CREATE TABLE IF NOT EXISTS `contrexx_module_feed_newsml_providers` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `providerId` text NOT NULL,
  `name` varchar(40) NOT NULL default '',
  `path` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=2 ;

-- 
-- Daten für Tabelle `contrexx_module_feed_newsml_providers`
-- 

INSERT INTO `contrexx_module_feed_newsml_providers` VALUES (1, 'www.sda-ats.ch', 'sda-Online', '/sportnews');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_gallery_categories`
-- 

DROP TABLE IF EXISTS `contrexx_module_gallery_categories`;
CREATE TABLE IF NOT EXISTS `contrexx_module_gallery_categories` (
  `id` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `sorting` tinyint(3) NOT NULL default '0',
  `status` set('0','1') NOT NULL default '1',
  `comment` set('0','1') NOT NULL default '0',
  `voting` set('0','1') NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=62 ;

-- 
-- Daten für Tabelle `contrexx_module_gallery_categories`
-- 

INSERT INTO `contrexx_module_gallery_categories` VALUES (61, 0, 0, '1', '1', '1');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_gallery_comments`
-- 

DROP TABLE IF EXISTS `contrexx_module_gallery_comments`;
CREATE TABLE IF NOT EXISTS `contrexx_module_gallery_comments` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `picid` int(10) unsigned NOT NULL default '0',
  `date` int(14) unsigned NOT NULL default '0',
  `ip` varchar(15) NOT NULL default '',
  `name` varchar(50) NOT NULL default '',
  `email` varchar(250) NOT NULL default '',
  `www` varchar(250) NOT NULL default '',
  `comment` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=48 ;

-- 
-- Daten für Tabelle `contrexx_module_gallery_comments`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_gallery_language`
-- 

DROP TABLE IF EXISTS `contrexx_module_gallery_language`;
CREATE TABLE IF NOT EXISTS `contrexx_module_gallery_language` (
  `gallery_id` int(10) unsigned NOT NULL default '0',
  `lang_id` int(10) unsigned NOT NULL default '0',
  `name` set('name','desc') NOT NULL default '',
  `value` text NOT NULL,
  PRIMARY KEY  (`gallery_id`,`lang_id`,`name`)
) TYPE=MyISAM;

-- 
-- Daten für Tabelle `contrexx_module_gallery_language`
-- 

INSERT INTO `contrexx_module_gallery_language` VALUES (61, 5, 'desc', '');
INSERT INTO `contrexx_module_gallery_language` VALUES (61, 5, 'name', 'test');
INSERT INTO `contrexx_module_gallery_language` VALUES (61, 4, 'desc', '');
INSERT INTO `contrexx_module_gallery_language` VALUES (61, 4, 'name', 'test');
INSERT INTO `contrexx_module_gallery_language` VALUES (61, 3, 'desc', '');
INSERT INTO `contrexx_module_gallery_language` VALUES (61, 3, 'name', 'test');
INSERT INTO `contrexx_module_gallery_language` VALUES (61, 2, 'desc', '');
INSERT INTO `contrexx_module_gallery_language` VALUES (61, 2, 'name', 'test');
INSERT INTO `contrexx_module_gallery_language` VALUES (61, 1, 'desc', '');
INSERT INTO `contrexx_module_gallery_language` VALUES (61, 1, 'name', 'test');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_gallery_language_pics`
-- 

DROP TABLE IF EXISTS `contrexx_module_gallery_language_pics`;
CREATE TABLE IF NOT EXISTS `contrexx_module_gallery_language_pics` (
  `picture_id` int(10) unsigned NOT NULL default '0',
  `lang_id` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `desc` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`picture_id`,`lang_id`)
) TYPE=MyISAM;

-- 
-- Daten für Tabelle `contrexx_module_gallery_language_pics`
-- 

INSERT INTO `contrexx_module_gallery_language_pics` VALUES (414, 3, 'schlossberg.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (412, 5, 'Nur für Ivan_D', 'Oh yeah! :-)');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (412, 4, 'Nur für Ivan_I', 'Oh yeah! :-)');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (412, 3, 'Nur für Ivan_F', 'Oh yeah! :-)');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (412, 2, 'Nur für Ivan_E', 'Oh yeah! :-)');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (412, 1, 'Nur für Ivan_D', 'Oh yeah! :-)');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (414, 2, 'schlossberg.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (414, 1, 'schlossberg.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (414, 4, 'schlossberg.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (414, 5, 'schlossberg.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (415, 1, 'blickvomschlossaufaltstadt.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (415, 2, 'blickvomschlossaufaltstadt.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (415, 3, 'blickvomschlossaufaltstadt.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (415, 4, 'blickvomschlossaufaltstadt.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (415, 5, 'blickvomschlossaufaltstadt.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (416, 1, 'aarequaibeinacht.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (416, 2, 'aarequaibeinacht.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (416, 3, 'aarequaibeinacht.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (416, 4, 'aarequaibeinacht.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (416, 5, 'aarequaibeinacht.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (453, 3, '400w.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (453, 2, '400w.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (453, 1, '400w.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (418, 1, 'schlossimabendlicht.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (418, 2, 'schlossimabendlicht.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (418, 3, 'schlossimabendlicht.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (418, 4, 'schlossimabendlicht.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (418, 5, 'schlossimabendlicht.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (419, 1, 'blickvomschlossaufaltstadt1.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (419, 2, 'blickvomschlossaufaltstadt1.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (419, 3, 'blickvomschlossaufaltstadt1.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (419, 4, 'blickvomschlossaufaltstadt1.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (419, 5, 'blickvomschlossaufaltstadt1.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (422, 1, 'Wasserlilien.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (422, 2, 'Wasserlilien.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (422, 3, 'Wasserlilien.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (422, 4, 'Wasserlilien.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (422, 5, 'Wasserlilien.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (423, 1, 'Winter.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (423, 2, 'Winter.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (423, 3, 'Winter.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (423, 4, 'Winter.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (423, 5, 'Winter.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (424, 1, '1129642370_Blaue Berge.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (424, 2, '1129642370_Blaue Berge.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (424, 3, '1129642370_Blaue Berge.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (424, 4, '1129642370_Blaue Berge.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (424, 5, '1129642370_Blaue Berge.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (425, 1, '1129642371_Sonnenuntergang.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (425, 2, '1129642371_Sonnenuntergang.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (425, 3, '1129642371_Sonnenuntergang.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (425, 4, '1129642371_Sonnenuntergang.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (425, 5, '1129642371_Sonnenuntergang.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (426, 1, '1129642371_Wasserlilien.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (426, 2, '1129642371_Wasserlilien.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (426, 3, '1129642371_Wasserlilien.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (426, 4, '1129642371_Wasserlilien.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (426, 5, '1129642371_Wasserlilien.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (427, 1, '1129642371_Winter.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (427, 2, '1129642371_Winter.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (427, 3, '1129642371_Winter.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (427, 4, '1129642371_Winter.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (427, 5, '1129642371_Winter.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (428, 1, '1129642430_Blaue Berge.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (428, 2, '1129642430_Blaue Berge.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (428, 3, '1129642430_Blaue Berge.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (428, 4, '1129642430_Blaue Berge.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (428, 5, '1129642430_Blaue Berge.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (429, 1, '1129642430_Sonnenuntergang.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (429, 2, '1129642430_Sonnenuntergang.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (429, 3, '1129642430_Sonnenuntergang.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (429, 4, '1129642430_Sonnenuntergang.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (429, 5, '1129642430_Sonnenuntergang.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (430, 1, '1129642431_Wasserlilien.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (430, 2, '1129642431_Wasserlilien.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (430, 3, '1129642431_Wasserlilien.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (430, 4, '1129642431_Wasserlilien.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (430, 5, '1129642431_Wasserlilien.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (431, 1, '1129642431_Winter.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (431, 2, '1129642431_Winter.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (431, 3, '1129642431_Winter.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (431, 4, '1129642431_Winter.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (431, 5, '1129642431_Winter.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (433, 1, 'schloss1.png', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (433, 2, 'schloss1.png', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (433, 3, 'schloss1.png', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (433, 4, 'schloss1.png', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (433, 5, 'schloss1.png', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (434, 1, 'schloss1_nikon.png', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (434, 2, 'schloss1_nikon.png', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (434, 3, 'schloss1_nikon.png', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (434, 4, 'schloss1_nikon.png', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (434, 5, 'schloss1_nikon.png', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (435, 1, 'schloss1_zoom_nikon.png', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (435, 2, 'schloss1_zoom_nikon.png', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (435, 3, 'schloss1_zoom_nikon.png', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (435, 4, 'schloss1_zoom_nikon.png', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (435, 5, 'schloss1_zoom_nikon.png', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (436, 1, 'schloss2_zoom.png', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (436, 2, 'schloss2_zoom.png', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (436, 3, 'schloss2_zoom.png', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (436, 4, 'schloss2_zoom.png', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (436, 5, 'schloss2_zoom.png', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (438, 5, 'bunny.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (438, 4, 'bunny.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (438, 3, 'bunny.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (438, 2, 'bunny.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (438, 1, 'bunny.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (440, 1, 'hitsch.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (440, 2, 'hitsch.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (440, 3, 'hitsch.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (440, 4, 'hitsch.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (440, 5, 'hitsch.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (453, 4, '400w.jpg', '');
INSERT INTO `contrexx_module_gallery_language_pics` VALUES (453, 5, '400w.jpg', '');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_gallery_pictures`
-- 

DROP TABLE IF EXISTS `contrexx_module_gallery_pictures`;
CREATE TABLE IF NOT EXISTS `contrexx_module_gallery_pictures` (
  `id` int(11) NOT NULL auto_increment,
  `catid` int(11) NOT NULL default '0',
  `validated` set('0','1') NOT NULL default '0',
  `status` set('0','1') NOT NULL default '1',
  `sorting` smallint(3) unsigned NOT NULL default '999',
  `size_show` set('0','1') NOT NULL default '1',
  `path` text NOT NULL,
  `link` text NOT NULL,
  `lastedit` int(14) NOT NULL default '0',
  `size_type` set('abs','proz') NOT NULL default 'proz',
  `size_proz` int(3) NOT NULL default '0',
  `size_abs_h` int(11) NOT NULL default '0',
  `size_abs_w` int(11) NOT NULL default '0',
  `quality` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  FULLTEXT KEY `galleryPicturesIndex` (`path`)
) TYPE=MyISAM AUTO_INCREMENT=454 ;

-- 
-- Daten für Tabelle `contrexx_module_gallery_pictures`
-- 

INSERT INTO `contrexx_module_gallery_pictures` VALUES (453, 61, '1', '1', 999, '1', '400w.jpg', '', 1135179661, 'abs', 25, 105, 140, 95);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_gallery_settings`
-- 

DROP TABLE IF EXISTS `contrexx_module_gallery_settings`;
CREATE TABLE IF NOT EXISTS `contrexx_module_gallery_settings` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(30) NOT NULL default '',
  `value` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=15 ;

-- 
-- Daten für Tabelle `contrexx_module_gallery_settings`
-- 

INSERT INTO `contrexx_module_gallery_settings` VALUES (1, 'max_images_upload', '10');
INSERT INTO `contrexx_module_gallery_settings` VALUES (2, 'standard_quality', '95');
INSERT INTO `contrexx_module_gallery_settings` VALUES (3, 'standard_size_proz', '25');
INSERT INTO `contrexx_module_gallery_settings` VALUES (4, 'standard_width_abs', '140');
INSERT INTO `contrexx_module_gallery_settings` VALUES (7, 'standard_size_type', 'abs');
INSERT INTO `contrexx_module_gallery_settings` VALUES (6, 'standard_height_abs', '0');
INSERT INTO `contrexx_module_gallery_settings` VALUES (9, 'validation_standard_type', 'all');
INSERT INTO `contrexx_module_gallery_settings` VALUES (8, 'validation_show_limit', '10');
INSERT INTO `contrexx_module_gallery_settings` VALUES (11, 'show_names', 'on');
INSERT INTO `contrexx_module_gallery_settings` VALUES (12, 'quality', '95');
INSERT INTO `contrexx_module_gallery_settings` VALUES (13, 'show_comments', 'on');
INSERT INTO `contrexx_module_gallery_settings` VALUES (14, 'show_voting', 'on');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_gallery_votes`
-- 

DROP TABLE IF EXISTS `contrexx_module_gallery_votes`;
CREATE TABLE IF NOT EXISTS `contrexx_module_gallery_votes` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `picid` int(10) unsigned NOT NULL default '0',
  `date` int(14) unsigned NOT NULL default '0',
  `ip` varchar(15) NOT NULL default '',
  `md5` varchar(32) NOT NULL default '',
  `mark` int(2) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=57 ;

-- 
-- Daten für Tabelle `contrexx_module_gallery_votes`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_guestbook`
-- 

DROP TABLE IF EXISTS `contrexx_module_guestbook`;
CREATE TABLE IF NOT EXISTS `contrexx_module_guestbook` (
  `id` smallint(6) NOT NULL auto_increment,
  `status` tinyint(1) unsigned NOT NULL default '0',
  `nickname` tinytext NOT NULL,
  `gender` char(1) NOT NULL default '',
  `url` tinytext NOT NULL,
  `email` tinytext NOT NULL,
  `comment` text NOT NULL,
  `ip` varchar(15) NOT NULL default '',
  `location` tinytext NOT NULL,
  `lang_id` tinyint(2) NOT NULL default '1',
  `datetime` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  FULLTEXT KEY `comment` (`comment`)
) TYPE=MyISAM AUTO_INCREMENT=56 ;

-- 
-- Daten für Tabelle `contrexx_module_guestbook`
-- 

INSERT INTO `contrexx_module_guestbook` VALUES (35, 1, 'Contrexx Development Team', 'M', 'http://www.contrexx.com/', 'support@contrexx.com', 'Das Gästebuch ist eröffnet!', '84.226.45.86', 'Sonnendeck', 1, '2005-12-08 08:48:03');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_guestbook_settings`
-- 

DROP TABLE IF EXISTS `contrexx_module_guestbook_settings`;
CREATE TABLE IF NOT EXISTS `contrexx_module_guestbook_settings` (
  `name` varchar(50) NOT NULL default '',
  `value` varchar(250) NOT NULL default '',
  KEY `name` (`name`)
) TYPE=MyISAM;

-- 
-- Daten für Tabelle `contrexx_module_guestbook_settings`
-- 

INSERT INTO `contrexx_module_guestbook_settings` VALUES ('guestbook_send_notification_email', '0');
INSERT INTO `contrexx_module_guestbook_settings` VALUES ('guestbook_activate_submitted_entries', '0');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_news`
-- 

DROP TABLE IF EXISTS `contrexx_module_news`;
CREATE TABLE IF NOT EXISTS `contrexx_module_news` (
  `id` smallint(6) unsigned NOT NULL auto_increment,
  `date` int(14) default NULL,
  `title` varchar(250) NOT NULL default '',
  `text` mediumtext NOT NULL,
  `source` varchar(250) NOT NULL default '',
  `url1` varchar(250) NOT NULL default '',
  `url2` varchar(250) NOT NULL default '',
  `catid` tinyint(2) NOT NULL default '0',
  `lang` tinyint(2) NOT NULL default '0',
  `userid` smallint(6) NOT NULL default '0',
  `startdate` date NOT NULL default '0000-00-00',
  `enddate` date NOT NULL default '0000-00-00',
  `status` tinyint(4) NOT NULL default '1',
  `validated` enum('0','1') NOT NULL default '0',
  `teaser_only` enum('0','1') NOT NULL default '0',
  `teaser_frames` varchar(255) NOT NULL default '',
  `teaser_text` tinytext NOT NULL,
  `teaser_image_path` text NOT NULL,
  `changelog` int(14) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `ID` (`id`),
  FULLTEXT KEY `newsindex` (`text`,`title`)
) TYPE=MyISAM AUTO_INCREMENT=189 ;

-- 
-- Daten für Tabelle `contrexx_module_news`
-- 

INSERT INTO `contrexx_module_news` VALUES (188, 1135085906, 'Warum man den Internet Explorer nicht benutzen sollte', 'Es gibt viele Gr&uuml;nde, warum man Internet Explorer nicht benutzen sollte:\r\n<ul>\r\n    <li>Internet Explorer ist <b>unsicher</b>. Es gibt Unmengen von Sicherheitsl&uuml;cken, durch die sich meistens lokale Daten einsehen und bearbeiten lassen. Wer mir das nicht glaubt, sollte sich unbedingt die <a href="http://www.eggdrop.ch/noie/holes.html">Liste der gefundenen L&uuml;cken</a> anschauen.</li>\r\n    <li>Internet Explorer <b>zeigt moderne Seiten nicht immer korrekt an</b>. Andere Browser halten sich besser an die Standards und bieten somit dem Webdesigner mehr Freiheit, seine Seiten zu gestalten. Beispiel stufenlose PNG-Transparenz: S&auml;mtliche bekannte Browser k&ouml;nnen das - ausser Internet Explorer. Auch der CSS-Support beim Internet Explorer ist schlecht implementiert, auf <a href="http://www.positioniseverything.net/explorer.html">http://www.positioniseverything.net/explorer.html</a> findet man einige der vielen CSS-Bugs.</li>\r\n    <li>Internet Explorer ist <b>nicht Open Source</b>, das heisst, dass sein Quellcode nicht frei verf&uuml;gbar ist. Bei Open Source-Browsern kann jeder den Quellcode anschauen, der Vorteil ist klar: Wenn mehr Leute den Quellcode anschauen, werden auch mehr Fehler gefunden, der Browser ist sicherer. Ausserdem kann jeder seine Erweiterungen dazugeben, wodurch der Browser immer moderner wird.</li>\r\n    <li>Internet Explorer ist <b>nicht plattformunabh&auml;ngig</b>. Microsoft liefert den Internet Explorer nur f&uuml;r Windows und Mac aus, die Mac-Version wird aber laut Microsoft (zum Gl&uuml;ck) nicht mehr weiterenwickelt. Neuere Internet Explorer sollen sogar eine aktuelle Windows-Version verlangen und zwar nicht, weil sie auf den &auml;lteren Betriebssystemversionen nicht mehr laufen w&uuml;rden, sondern weil Microsoft den Benutzer dazu zwingen will, sich eine neue Windows-Version zu kaufen. Alternative Browser laufen auf verschiedenen Plattformen (z.B. Linux, Mac, BeOS etc.)</li>\r\n    <li>Internet Explorer hat <b>wenig Komfort</b>. Das bekannteste Beispiel ist das sogenannte Tabbed-Browsing. Damit kann man mehrere Webseiten in einem Browserfenster ge&ouml;ffnet werden und erspart sich somit das Fensterchaos. Einige Browser haben beispielsweise auch einen Download- und Thememanager.</li>\r\n</ul>\r\n<p>Immer noch nicht &uuml;berzeugt? Probier es doch einfach <b>jetzt</b> aus! Du kannst jederzeit kostenlos zu anderen Browsern wechseln.</p>', 'http://www.eggdrop.ch/noie/', '', '', 14, 1, 1, '0000-00-00', '0000-00-00', 1, '1', '0', '', '', '', 1135086215);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_news_access`
-- 

DROP TABLE IF EXISTS `contrexx_module_news_access`;
CREATE TABLE IF NOT EXISTS `contrexx_module_news_access` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(64) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `access_id` int(11) unsigned NOT NULL default '0',
  `type` enum('global','frontend','backend') NOT NULL default 'global',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=2 ;

-- 
-- Daten für Tabelle `contrexx_module_news_access`
-- 

INSERT INTO `contrexx_module_news_access` VALUES (1, 'submit_news', 'News anmelden', 61, 'frontend');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_news_categories`
-- 

DROP TABLE IF EXISTS `contrexx_module_news_categories`;
CREATE TABLE IF NOT EXISTS `contrexx_module_news_categories` (
  `catid` tinyint(2) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `lang` tinyint(2) unsigned NOT NULL default '1',
  PRIMARY KEY  (`catid`)
) TYPE=MyISAM AUTO_INCREMENT=15 ;

-- 
-- Daten für Tabelle `contrexx_module_news_categories`
-- 

INSERT INTO `contrexx_module_news_categories` VALUES (1, 'Wirtschaft', 1);
INSERT INTO `contrexx_module_news_categories` VALUES (10, 'Test', 2);
INSERT INTO `contrexx_module_news_categories` VALUES (14, 'Informatik', 1);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_news_settings`
-- 

DROP TABLE IF EXISTS `contrexx_module_news_settings`;
CREATE TABLE IF NOT EXISTS `contrexx_module_news_settings` (
  `name` varchar(50) NOT NULL default '',
  `value` varchar(250) NOT NULL default '',
  KEY `name` (`name`)
) TYPE=MyISAM;

-- 
-- Daten für Tabelle `contrexx_module_news_settings`
-- 

INSERT INTO `contrexx_module_news_settings` VALUES ('news_feed_description', 'Informationen rund um das Contrexx® Open Source CMS');
INSERT INTO `contrexx_module_news_settings` VALUES ('news_feed_status', '1');
INSERT INTO `contrexx_module_news_settings` VALUES ('news_feed_title', 'Contrexx® Open Source CMS');
INSERT INTO `contrexx_module_news_settings` VALUES ('news_headlines_limit', '10');
INSERT INTO `contrexx_module_news_settings` VALUES ('news_settings_activated', '1');
INSERT INTO `contrexx_module_news_settings` VALUES ('news_submit_news', '1');
INSERT INTO `contrexx_module_news_settings` VALUES ('news_submit_only_community', '0');
INSERT INTO `contrexx_module_news_settings` VALUES ('news_activate_submitted_news', '0');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_news_teaser_frame`
-- 

DROP TABLE IF EXISTS `contrexx_module_news_teaser_frame`;
CREATE TABLE IF NOT EXISTS `contrexx_module_news_teaser_frame` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `lang_id` tinyint(3) unsigned NOT NULL default '0',
  `frame_template_id` int(10) unsigned NOT NULL default '0',
  `name` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=20 ;

-- 
-- Daten für Tabelle `contrexx_module_news_teaser_frame`
-- 

INSERT INTO `contrexx_module_news_teaser_frame` VALUES (1, 1, 2, 'Beispiel3');
INSERT INTO `contrexx_module_news_teaser_frame` VALUES (2, 1, 1, 'Beispiel1');
INSERT INTO `contrexx_module_news_teaser_frame` VALUES (3, 1, 3, 'Beispiel2');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_news_teaser_frame_templates`
-- 

DROP TABLE IF EXISTS `contrexx_module_news_teaser_frame_templates`;
CREATE TABLE IF NOT EXISTS `contrexx_module_news_teaser_frame_templates` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `description` varchar(100) NOT NULL default '',
  `html` text NOT NULL,
  `source_code_mode` enum('0','1') NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=7 ;

-- 
-- Daten für Tabelle `contrexx_module_news_teaser_frame_templates`
-- 

INSERT INTO `contrexx_module_news_teaser_frame_templates` VALUES (1, '3 Teaserboxen (1. Zeile: 1 Teaser - 2. Zeile: 2 Teaser)', '<table cellspacing="5" cellpadding="0" border="0">\r\n    <tbody>\r\n        <tr>\r\n            <td colspan="2"><!-- BEGIN teaser_1 -->\r\n            <table width="480" cellspacing="0" cellpadding="0" border="0">\r\n                <tbody>\r\n                    <tr>\r\n                        <th colspan="2">{TEASER_CATEGORY}</th>\r\n                    </tr>\r\n                    <tr>\r\n                        <td colspan="2">{TEASER_DATE}<br />{TEASER_TITLE} <a href="{TEASER_URL}">» Zur Meldung</a></td>\r\n                    </tr>\r\n                    <tr>\r\n                        <td width="25%"><img src="{TEASER_IMAGE_PATH}" width="80" height="120" alt="" /></td>\r\n                        <td width="75%" style="vertical-align: top;">{TEASER_TEXT}</td>\r\n                    </tr>\r\n                </tbody>\r\n            </table>\r\n            <!-- END teaser_1 --></td>\r\n        </tr>\r\n        <tr>\r\n            <td width="50%"><!-- BEGIN teaser_2 -->\r\n            <table width="235" cellspacing="0" cellpadding="0" border="0">\r\n                <tbody>\r\n                    <tr>\r\n                        <th colspan="2">{TEASER_CATEGORY}</th>\r\n                    </tr>\r\n                    <tr>\r\n                        <td colspan="2">{TEASER_DATE}<br />{TEASER_TITLE} <a href="{TEASER_URL}">» Zur Meldung</a></td>\r\n                    </tr>\r\n                    <tr>\r\n                        <td width="50%"><img src="{TEASER_IMAGE_PATH}" width="80" height="120" alt="" /></td>\r\n                        <td width="50%" style="vertical-align: top;">{TEASER_TEXT}</td>\r\n                    </tr>\r\n                </tbody>\r\n            </table>\r\n            <!-- END teaser_2 --></td>\r\n            <td width="50%"><!-- BEGIN teaser_3 -->\r\n            <table width="235" cellspacing="0" cellpadding="0" border="0">\r\n                <tbody>\r\n                    <tr>\r\n                        <th>{TEASER_CATEGORY}</th>\r\n                    </tr>\r\n                    <tr>\r\n                        <td>{TEASER_DATE}<br />{TEASER_TITLE} <a href="{TEASER_URL}">» Zur Meldung</a></td>\r\n                    </tr>\r\n                    <tr>\r\n                        <td><img src="{TEASER_IMAGE_PATH}" width="80" height="120" alt="" /></td>\r\n                    </tr>\r\n                </tbody>\r\n            </table>\r\n            <!-- END teaser_3 --></td>\r\n        </tr>\r\n    </tbody>\r\n</table>', '1');
INSERT INTO `contrexx_module_news_teaser_frame_templates` VALUES (2, '4 Teaserboxen (2 Teaser pro Zeile)', '<table cellspacing="5" cellpadding="0" border="0">\r\n    <tbody>\r\n        <tr>\r\n            <td width="50%" style="vertical-align: top;"><!-- BEGIN teaser_1 -->\r\n            <table width="235" cellspacing="0" cellpadding="0" border="0">\r\n                <tbody>\r\n                    <tr>\r\n                        <th colspan="2">{TEASER_CATEGORY}</th>\r\n                    </tr>\r\n                    <tr>\r\n                        <td colspan="2">{TEASER_DATE}<br />{TEASER_TITLE} <a href="{TEASER_URL}">» Zur Meldung</a></td>\r\n                    </tr>\r\n                    <tr>\r\n                        <td width="50%"><img alt="" src="{TEASER_IMAGE_PATH}" /></td>\r\n                        <td width="50%" style="vertical-align: top;">{TEASER_TEXT}</td>\r\n                    </tr>\r\n                </tbody>\r\n            </table>\r\n            <!-- END teaser_1 --></td>\r\n            <td width="50%" style="vertical-align: top;"><!-- BEGIN teaser_2 -->\r\n            <table width="235" cellspacing="0" cellpadding="0" border="0">\r\n                <tbody>\r\n                    <tr>\r\n                        <th>{TEASER_CATEGORY}</th>\r\n                    </tr>\r\n                    <tr>\r\n                        <td>{TEASER_DATE}<br />{TEASER_TITLE} <a href="{TEASER_URL}">» Zur Meldung</a></td>\r\n                    </tr>\r\n                    <tr>\r\n                        <td><img alt="" src="{TEASER_IMAGE_PATH}" /></td>\r\n                    </tr>\r\n                </tbody>\r\n            </table>\r\n            <!-- END teaser_2 --></td>\r\n        </tr>\r\n        <tr>\r\n            <td width="50%" style="vertical-align: top;"><!-- BEGIN teaser_3 -->\r\n            <table width="235" cellspacing="0" cellpadding="0" border="0">\r\n                <tbody>\r\n                    <tr>\r\n                        <th>{TEASER_CATEGORY}</th>\r\n                    </tr>\r\n                    <tr>\r\n                        <td>{TEASER_DATE}<br />{TEASER_TITLE} <a href="{TEASER_URL}">» Zur Meldung</a></td>\r\n                    </tr>\r\n                    <tr>\r\n                        <td><img alt="" src="{TEASER_IMAGE_PATH}" /></td>\r\n                    </tr>\r\n                </tbody>\r\n            </table>\r\n            <!-- END teaser_3 --></td>\r\n            <td width="50%" style="vertical-align: top;"><!-- BEGIN teaser_4 -->\r\n            <table width="235" cellspacing="0" cellpadding="0" border="0">\r\n                <tbody>\r\n                    <tr>\r\n                        <th colspan="2">{TEASER_CATEGORY}</th>\r\n                    </tr>\r\n                    <tr>\r\n                        <td colspan="2">{TEASER_DATE}<br />{TEASER_TITLE} <a href="{TEASER_URL}">» Zur Meldung</a></td>\r\n                    </tr>\r\n                    <tr>\r\n                        <td width="50%"><img alt="" src="{TEASER_IMAGE_PATH}" /></td>\r\n                        <td width="50%" style="vertical-align: top;">{TEXT}</td>\r\n                    </tr>\r\n                </tbody>\r\n            </table>\r\n            <!-- END teaser_4 --></td>\r\n        </tr>\r\n    </tbody>\r\n</table>', '1');
INSERT INTO `contrexx_module_news_teaser_frame_templates` VALUES (3, '6 Teaserboxen (2 Teaser pro Zeile)', '<table cellspacing="5" cellpadding="0" border="0">\r\n    <tbody>\r\n        <tr>\r\n            <td width="50%" style="vertical-align: top;"><!-- BEGIN teaser_1 -->\r\n            <table width="235" cellspacing="0" cellpadding="0" border="0">\r\n                <tbody>\r\n                    <tr>\r\n                        <th colspan="2">{TEASER_CATEGORY}</th>\r\n                    </tr>\r\n                    <tr>\r\n                        <td colspan="2">{TEASER_DATE}<br />{TEASER_TITLE} <a href="{TEASER_URL}">» Zur Meldung</a></td>\r\n                    </tr>\r\n                    <tr>\r\n                        <td width="50%"><img alt="" src="{TEASER_IMAGE_PATH}" /></td>\r\n                        <td width="50%" style="vertical-align: top;">{TEASER_TEXT}</td>\r\n                    </tr>\r\n                </tbody>\r\n            </table>\r\n            <!-- END teaser_1 --></td>\r\n            <td width="50%" style="vertical-align: top;"><!-- BEGIN teaser_2 -->\r\n            <table width="235" cellspacing="0" cellpadding="0" border="0">\r\n                <tbody>\r\n                    <tr>\r\n                        <th colspan="2">{TEASER_CATEGORY}</th>\r\n                    </tr>\r\n                    <tr>\r\n                        <td colspan="2">{TEASER_DATE}<br />{TEASER_TITLE} <a href="{TEASER_URL}">» Zur Meldung</a></td>\r\n                    </tr>\r\n                    <tr>\r\n                        <td width="50%"><img alt="" src="{TEASER_IMAGE_PATH}" /></td>\r\n                        <td width="50%" style="vertical-align: top;">{TEASER_TEXT}</td>\r\n                    </tr>\r\n                </tbody>\r\n            </table>\r\n            <!-- END teaser_2 --></td>\r\n        </tr>\r\n        <tr>\r\n            <td width="50%" style="vertical-align: top;"><!-- BEGIN teaser_3 -->\r\n            <table width="235" cellspacing="0" cellpadding="0" border="0">\r\n                <tbody>\r\n                    <tr>\r\n                        <th colspan="2">{TEASER_CATEGORY}</th>\r\n                    </tr>\r\n                    <tr>\r\n                        <td colspan="2">{TEASER_DATE}<br />{TEASER_TITLE} <a href="{TEASER_URL}">» Zur Meldung</a></td>\r\n                    </tr>\r\n                    <tr>\r\n                        <td width="50%"><img alt="" src="{TEASER_IMAGE_PATH}" /></td>\r\n                        <td width="50%" style="vertical-align: top;">{TEASER_TEXT}</td>\r\n                    </tr>\r\n                </tbody>\r\n            </table>\r\n            <!-- END teaser_3 --></td>\r\n            <td width="50%" style="vertical-align: top;"><!-- BEGIN teaser_4 -->\r\n            <table width="235" cellspacing="0" cellpadding="0" border="0">\r\n                <tbody>\r\n                    <tr>\r\n                        <th colspan="2">{TEASER_CATEGORY}</th>\r\n                    </tr>\r\n                    <tr>\r\n                        <td colspan="2">{TEASER_DATE}<br />{TEASER_TITLE} <a href="{TEASER_URL}">» Zur Meldung</a></td>\r\n                    </tr>\r\n                    <tr>\r\n                        <td width="50%"><img alt="" src="{TEASER_IMAGE_PATH}" /></td>\r\n                        <td width="50%" style="vertical-align: top;">{TEASER_TEXT}</td>\r\n                    </tr>\r\n                </tbody>\r\n            </table>\r\n            <!-- END teaser_4 --></td>\r\n        </tr>\r\n        <tr>\r\n            <td width="50%" style="vertical-align: top;"><!-- BEGIN teaser_5 -->\r\n            <table width="235" cellspacing="0" cellpadding="0" border="0">\r\n                <tbody>\r\n                    <tr>\r\n                        <th colspan="2">{TEASER_CATEGORY}</th>\r\n                    </tr>\r\n                    <tr>\r\n                        <td colspan="2">{TEASER_DATE}<br />{TEASER_TITLE} <a href="{TEASER_URL}">» Zur Meldung</a></td>\r\n                    </tr>\r\n                    <tr>\r\n                        <td width="50%"><img alt="" src="{TEASER_IMAGE_PATH}" /></td>\r\n                        <td width="50%" style="vertical-align: top;">{TEASER_TEXT}</td>\r\n                    </tr>\r\n                </tbody>\r\n            </table>\r\n            <!-- END teaser_5 --></td>\r\n            <td width="50%" style="vertical-align: top;"><!-- BEGIN teaser_6 -->\r\n            <table width="235" cellspacing="0" cellpadding="0" border="0">\r\n                <tbody>\r\n                    <tr>\r\n                        <th colspan="2">{TEASER_CATEGORY}</th>\r\n                    </tr>\r\n                    <tr>\r\n                        <td colspan="2">{TEASER_DATE}<br />{TEASER_TITLE} <a href="{TEASER_URL}">» Zur Meldung</a></td>\r\n                    </tr>\r\n                    <tr>\r\n                        <td width="50%"><img alt="" src="{TEASER_IMAGE_PATH}" /></td>\r\n                        <td width="50%" style="vertical-align: top;">{TEASER_TEXT}</td>\r\n                    </tr>\r\n                </tbody>\r\n            </table>\r\n            <!-- END teaser_6 --></td>\r\n        </tr>\r\n    </tbody>\r\n</table>', '1');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_newsletter`
-- 

DROP TABLE IF EXISTS `contrexx_module_newsletter`;
CREATE TABLE IF NOT EXISTS `contrexx_module_newsletter` (
  `id` int(11) NOT NULL auto_increment,
  `subject` varchar(255) NOT NULL default '',
  `template` int(11) NOT NULL default '0',
  `content` text NOT NULL,
  `content_text` text NOT NULL,
  `attachment` set('0','1') NOT NULL default '',
  `format` set('html/text','html','text') NOT NULL default '',
  `priority` tinyint(1) NOT NULL default '0',
  `sender_email` varchar(255) NOT NULL default '',
  `sender_name` varchar(255) NOT NULL default '',
  `return_path` varchar(255) NOT NULL default '',
  `status` int(1) NOT NULL default '0',
  `count` int(11) NOT NULL default '0',
  `date_create` date NOT NULL default '0000-00-00',
  `date_sent` date NOT NULL default '0000-00-00',
  `tmp_copy` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id` (`id`)
) TYPE=MyISAM AUTO_INCREMENT=52 ;

-- 
-- Daten für Tabelle `contrexx_module_newsletter`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_newsletter_attachment`
-- 

DROP TABLE IF EXISTS `contrexx_module_newsletter_attachment`;
CREATE TABLE IF NOT EXISTS `contrexx_module_newsletter_attachment` (
  `id` int(11) NOT NULL auto_increment,
  `newsletter` int(11) NOT NULL default '0',
  `file_name` varchar(255) NOT NULL default '',
  `file_nr` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `newsletter` (`newsletter`)
) TYPE=MyISAM AUTO_INCREMENT=53 ;

-- 
-- Daten für Tabelle `contrexx_module_newsletter_attachment`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_newsletter_category`
-- 

DROP TABLE IF EXISTS `contrexx_module_newsletter_category`;
CREATE TABLE IF NOT EXISTS `contrexx_module_newsletter_category` (
  `id` int(11) NOT NULL auto_increment,
  `status` tinyint(1) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `id` (`id`)
) TYPE=MyISAM AUTO_INCREMENT=2 ;

-- 
-- Daten für Tabelle `contrexx_module_newsletter_category`
-- 

INSERT INTO `contrexx_module_newsletter_category` VALUES (1, 1, 'Demo Kategorie');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_newsletter_config`
-- 

DROP TABLE IF EXISTS `contrexx_module_newsletter_config`;
CREATE TABLE IF NOT EXISTS `contrexx_module_newsletter_config` (
  `id` int(11) NOT NULL default '0',
  `sender_email` varchar(255) NOT NULL default '',
  `sender_name` varchar(255) NOT NULL default '',
  `return_path` varchar(255) NOT NULL default '',
  `profile_setup_html` text NOT NULL,
  `profile_setup_text` text NOT NULL,
  `unsubscribe_html` text NOT NULL,
  `unsubscribe_text` text NOT NULL,
  `mails_per_run` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

-- 
-- Daten für Tabelle `contrexx_module_newsletter_config`
-- 

INSERT INTO `contrexx_module_newsletter_config` VALUES (1, 'noreply@example.com', 'Hans Mustermann', 'noreply@example.com', 'Profil bearbeiten', 'Profil bearbeiten', 'Newsletter Abmelden', 'Newsletter Abmelden', 30);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_newsletter_rel_cat_news`
-- 

DROP TABLE IF EXISTS `contrexx_module_newsletter_rel_cat_news`;
CREATE TABLE IF NOT EXISTS `contrexx_module_newsletter_rel_cat_news` (
  `id` int(11) NOT NULL auto_increment,
  `newsletter` int(11) NOT NULL default '0',
  `category` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=33 ;

-- 
-- Daten für Tabelle `contrexx_module_newsletter_rel_cat_news`
-- 

INSERT INTO `contrexx_module_newsletter_rel_cat_news` VALUES (1, 45, 7);
INSERT INTO `contrexx_module_newsletter_rel_cat_news` VALUES (2, 0, 7);
INSERT INTO `contrexx_module_newsletter_rel_cat_news` VALUES (20, 46, 7);
INSERT INTO `contrexx_module_newsletter_rel_cat_news` VALUES (4, 47, 7);
INSERT INTO `contrexx_module_newsletter_rel_cat_news` VALUES (5, 48, 7);
INSERT INTO `contrexx_module_newsletter_rel_cat_news` VALUES (6, 0, 7);
INSERT INTO `contrexx_module_newsletter_rel_cat_news` VALUES (7, 0, 7);
INSERT INTO `contrexx_module_newsletter_rel_cat_news` VALUES (8, 0, 7);
INSERT INTO `contrexx_module_newsletter_rel_cat_news` VALUES (9, 0, 7);
INSERT INTO `contrexx_module_newsletter_rel_cat_news` VALUES (10, 0, 7);
INSERT INTO `contrexx_module_newsletter_rel_cat_news` VALUES (11, 0, 7);
INSERT INTO `contrexx_module_newsletter_rel_cat_news` VALUES (13, 0, 7);
INSERT INTO `contrexx_module_newsletter_rel_cat_news` VALUES (14, 0, 7);
INSERT INTO `contrexx_module_newsletter_rel_cat_news` VALUES (16, 0, 7);
INSERT INTO `contrexx_module_newsletter_rel_cat_news` VALUES (18, 0, 7);
INSERT INTO `contrexx_module_newsletter_rel_cat_news` VALUES (21, 0, 7);
INSERT INTO `contrexx_module_newsletter_rel_cat_news` VALUES (22, 0, 7);
INSERT INTO `contrexx_module_newsletter_rel_cat_news` VALUES (23, 0, 7);
INSERT INTO `contrexx_module_newsletter_rel_cat_news` VALUES (24, 0, 7);
INSERT INTO `contrexx_module_newsletter_rel_cat_news` VALUES (25, 0, 7);
INSERT INTO `contrexx_module_newsletter_rel_cat_news` VALUES (26, 49, 7);
INSERT INTO `contrexx_module_newsletter_rel_cat_news` VALUES (27, 0, 7);
INSERT INTO `contrexx_module_newsletter_rel_cat_news` VALUES (29, 41, 7);
INSERT INTO `contrexx_module_newsletter_rel_cat_news` VALUES (31, 50, 7);
INSERT INTO `contrexx_module_newsletter_rel_cat_news` VALUES (32, 51, 7);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_newsletter_rel_user_cat`
-- 

DROP TABLE IF EXISTS `contrexx_module_newsletter_rel_user_cat`;
CREATE TABLE IF NOT EXISTS `contrexx_module_newsletter_rel_user_cat` (
  `id` int(11) NOT NULL auto_increment,
  `user` int(11) NOT NULL default '0',
  `category` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `user` (`user`)
) TYPE=MyISAM AUTO_INCREMENT=83 ;

-- 
-- Daten für Tabelle `contrexx_module_newsletter_rel_user_cat`
-- 

INSERT INTO `contrexx_module_newsletter_rel_user_cat` VALUES (82, 51, 1);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_newsletter_template`
-- 

DROP TABLE IF EXISTS `contrexx_module_newsletter_template`;
CREATE TABLE IF NOT EXISTS `contrexx_module_newsletter_template` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `html` text NOT NULL,
  `text` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=2 ;

-- 
-- Daten für Tabelle `contrexx_module_newsletter_template`
-- 

INSERT INTO `contrexx_module_newsletter_template` VALUES (1, 'Standard', 'Standard Template, Contrexx 2005', '<html>\r\n<head>\r\n<title><-- subject --></title>\r\n</head>\r\n<body>\r\n<-- content -->\r\n<br/><br/>\r\n<-- profile_setup --><br/>\r\n<-- unsubscribe -->\r\n</body>\r\n</html>', '<-- content -->\r\n\r\n<-- profile_setup -->\r\n<-- unsubscribe -->');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_newsletter_tmp_sending`
-- 

DROP TABLE IF EXISTS `contrexx_module_newsletter_tmp_sending`;
CREATE TABLE IF NOT EXISTS `contrexx_module_newsletter_tmp_sending` (
  `id` int(11) NOT NULL auto_increment,
  `newsletter` int(11) NOT NULL default '0',
  `email` varchar(255) NOT NULL default '',
  `sendt` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `email` (`email`)
) TYPE=MyISAM AUTO_INCREMENT=770 ;

-- 
-- Daten für Tabelle `contrexx_module_newsletter_tmp_sending`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_newsletter_user`
-- 

DROP TABLE IF EXISTS `contrexx_module_newsletter_user`;
CREATE TABLE IF NOT EXISTS `contrexx_module_newsletter_user` (
  `id` int(11) NOT NULL auto_increment,
  `code` varchar(255) NOT NULL default '',
  `email` varchar(255) NOT NULL default '',
  `lastname` varchar(255) NOT NULL default '',
  `firstname` varchar(255) NOT NULL default '',
  `street` varchar(255) NOT NULL default '',
  `zip` varchar(255) NOT NULL default '',
  `city` varchar(255) NOT NULL default '',
  `country` varchar(255) NOT NULL default '',
  `phone` varchar(255) NOT NULL default '',
  `birthday` varchar(100) NOT NULL default '',
  `status` int(1) NOT NULL default '0',
  `emaildate` date NOT NULL default '0000-00-00',
  PRIMARY KEY  (`id`),
  KEY `emailadress` (`email`)
) TYPE=MyISAM AUTO_INCREMENT=52 ;

-- 
-- Daten für Tabelle `contrexx_module_newsletter_user`
-- 

INSERT INTO `contrexx_module_newsletter_user` VALUES (51, 'g8B2fcrSnT', 'noreply@contrexx.com', 'Mustermann', 'Alex', '', '', '', '', '', '', 1, '2005-12-23');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_recommend`
-- 

DROP TABLE IF EXISTS `contrexx_module_recommend`;
CREATE TABLE IF NOT EXISTS `contrexx_module_recommend` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `value` text NOT NULL,
  `lang_id` int(11) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=15 ;

-- 
-- Daten für Tabelle `contrexx_module_recommend`
-- 

INSERT INTO `contrexx_module_recommend` VALUES (1, 'body', '<SALUTATION> <RECEIVER_NAME>\r\n\r\nFolgende Seite wurde ihnen von <SENDER_NAME> (<SENDER_MAIL>) empfohlen:\r\n\r\n<URL>\r\n\r\nAnmerkung von <SENDER_NAME>:\r\n\r\n<COMMENT>', 1);
INSERT INTO `contrexx_module_recommend` VALUES (2, 'subject', 'Seitenempfehlung von <SENDER_NAME>', 1);
INSERT INTO `contrexx_module_recommend` VALUES (5, 'salutation_female', 'Liebe', 1);
INSERT INTO `contrexx_module_recommend` VALUES (6, 'salutation_male', 'Lieber', 1);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_module_repository`
-- 

DROP TABLE IF EXISTS `contrexx_module_repository`;
CREATE TABLE IF NOT EXISTS `contrexx_module_repository` (
  `id` smallint(6) unsigned NOT NULL auto_increment,
  `moduleid` smallint(5) unsigned NOT NULL default '0',
  `content` mediumtext NOT NULL,
  `title` varchar(250) NOT NULL default '',
  `cmd` varchar(20) NOT NULL default '',
  `expertmode` set('y','n') NOT NULL default 'n',
  `parid` smallint(5) NOT NULL default '0',
  `displaystatus` set('on','off') NOT NULL default 'on',
  `username` varchar(250) NOT NULL default '',
  `displayorder` smallint(6) NOT NULL default '100',
  `lang` varchar(5) NOT NULL default '',
  UNIQUE KEY `contentid` (`id`),
  FULLTEXT KEY `fulltextindex` (`title`,`content`)
) TYPE=MyISAM AUTO_INCREMENT=526 ;

-- 
-- Daten für Tabelle `contrexx_module_repository`
-- 

INSERT INTO `contrexx_module_repository` VALUES (344, 14, '<table cellspacing="0" cellpadding="0" width="100%" border="0">\r\n<tbody>\r\n<tr>\r\n<td scope="col">\r\n<div align="left">{ERROR_NUMBER} {ERROR_MESSAGE} <br /><br /><strong>Das gewünschte Dokument existiert nicht an dieser Stelle.</strong><br /><br />Das von Ihnen gesuchte Dokument wurde möglicherweise umbenannt, verschoben oder gelöscht. Es existieren mehrere Möglichkeiten, um ein Dokument zu finden. Sie können auf die Homepage zurückkehren, das Dokument mit Stichworten suchen oder unsere Help Site konsultieren. Um von der letztbesuchten Seite aus weiterzufahren, klicken Sie bitte auf die Schaltfläche ''Zurück'' Ihres Browsers. <br /><br />The document you requested does not exist at this location.<br />The document you are looking for may have been renamed, moved or deleted. There are several ways to locate a document. You can return to the Homepage, search for the document using keywords or consult our Help Site. To continue on from the last page you visited, please press the ''Back'' button of your browser. <br /></div></td></tr></tbody></table>', 'Fehlermeldung', '', 'n', 0, 'off', 'system', 111, '1');
INSERT INTO `contrexx_module_repository` VALUES (345, 13, '<p>Ihre Eingabe wurde vom <b>ASTALAVISTA&reg; Angriffserkennungs System</b> als unzul&auml;ssig erkannt. <br/><br/>Einige besondere Zeichenfolgen werden vom Intrusion Detection System gefiltert und vom Intrusion Response System blockiert. Wenn Sie finden, dass diese Meldung unrechterweise erscheint, nehmen Sie doch bitte mit uns <a href="mailto:ivan.schmid%20AT%20astalavista%20DOT%20ch">Kontakt</a> auf.<br/><br/><i><b>Aktive Arbitrary Input Module:</b></i> \r\n</p><ul>\r\n<li>SQL Injection \r\n</li><li>Cross-Site Scripting \r\n</li><li>Session Hijacking<br/><br/></li></ul>', 'Alert System', '', 'n', 0, 'off', 'system', 111, '1');
INSERT INTO `contrexx_module_repository` VALUES (467, 18, '<img width=\\"100\\" height=\\"100\\" src=\\"images/modules/login/stop_hand.gif\\" alt=\\"\\" /><br />{TXT_NOT_ALLOWED_TO_ACCESS}', 'Zugriff verweigert', 'noaccess', 'n', 464, 'off', 'system', 0, '1');
INSERT INTO `contrexx_module_repository` VALUES (342, 5, '<form action="index.php" method="get">\r\n	<input name="term" value="{SEARCH_TERM}" size="30" maxlength="100" />\r\n	<input value="search" name="section" type="hidden" />\r\n	<input value="{TXT_SEARCH}" name="Submit" type="submit" />\r\n</form>\r\n<br />\r\n{SEARCH_TITLE}<br />\r\n<!-- BEGIN searchrow -->\r\n	{LINK} {COUNT_MATCH}<br />\r\n	{SHORT_CONTENT}<br />\r\n<!-- END searchrow -->\r\n<br />\r\n{SEARCH_PAGING}\r\n<br />\r\n<br />', 'Suchen', '', 'y', 0, 'off', 'system', 110, '1');
INSERT INTO `contrexx_module_repository` VALUES (405, 16, '<font color="red"><b>{SHOP_STATUS}</b></font><br />', 'Transaktionsstatus', 'success', 'n', 398, 'off', 'system', 7, '1');
INSERT INTO `contrexx_module_repository` VALUES (406, 16, '{SHOP_JAVASCRIPT_CODE} \r\n{SHOP_MENU}<br />\r\n{SHOP_CART_INFO}<br />\r\n{SHOP_PRODUCT_PAGING}\r\n<table width="100%" cellspacing="4" cellpadding="0" border="0">\r\n<tr> \r\n<td width="100%" height="20" background="images/modules/shop/dotted_line.gif"><img width="1" height="20" border="0" alt="" src="images/modules/shop/pixel.gif" /></td>		\r\n</tr>\r\n</table>\r\n<!-- BEGIN shopProductRow -->\r\n<form method="post" action="index.php?section=shop&amp;cmd=cart" name="{SHOP_PRODUCT_FORM_NAME}" id="{SHOP_PRODUCT_FORM_NAME}">\r\n<input type="hidden" value="{SHOP_PRODUCT_ID}" name="productId" />\r\n<table width="100%" cellspacing="3" cellpadding="1" border="0">\r\n<tr> \r\n<td colspan="4"><strong>{SHOP_PRODUCT_TITLE}</strong></td>\r\n</tr>\r\n<tr>\r\n<td width="25%" style="vertical-align:top;"><a href="{SHOP_PRODUCT_THUMBNAIL_LINK}"><img border="0" alt="{TXT_SEE_LARGE_PICTURE}" src="{SHOP_PRODUCT_THUMBNAIL}" /></a></td>\r\n<td width="75%" colspan="3" valign="top"><small><i><strong>{TXT_PRODUCT_ID}:</strong> {SHOP_PRODUCT_ID}</i></small> <br />\r\n{SHOP_PRODUCT_DESCRIPTION}<br />{SHOP_PRODUCT_DETAILDESCRIPTION}\r\n<br />\r\n<!-- BEGIN shopProductOptionsRow -->\r\n<table width="100%" cellspacing="0" cellpadding="0" border="0">\r\n<tr>\r\n<td>\r\n<strong>{SHOP_PRODUCT_OPTIONS_TITLE}</strong><br><br >\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\n<div id="product_options_layer{SHOP_PRODUCT_ID}" style="display:none;">\r\n<table width="100%" cellspacing="0" cellpadding="0" border="0">\r\n<!-- BEGIN shopProductOptionsValuesRow -->\r\n<tr>\r\n<td width="150" style="vertical-align:top;">\r\n{SHOP_PRODUCT_OPTIONS_NAME}:\r\n</td>\r\n<td>{SHOP_PRODCUT_OPTION}</td>\r\n</tr>\r\n<!-- END shopProductOptionsValuesRow -->\r\n</table>\r\n</div>\r\n</td>\r\n</tr>\r\n</table>\r\n<!-- END shopProductOptionsRow -->\r\n<br />{SHOP_PRODUCT_STOCK}<br />{SHOP_MANUFACTURER_LINK}</td>\r\n</tr>\r\n<tr>     \r\n<td colspan="4">{SHOP_PRODUCT_DETAILLINK}</td>\r\n</tr>\r\n<tr>   \r\n<td colspan="3"><b><font color="red">{SHOP_PRODUCT_DISCOUNTPRICE} {SHOP_PRODUCT_DISCOUNTPRICE_UNIT} </font> {SHOP_PRODUCT_PRICE} {SHOP_PRODUCT_PRICE_UNIT} </b>\r\n</td>   \r\n<td>\r\n<div align="right"><input type="submit" value="{TXT_ADD_TO_CARD}" name="{SHOP_PRODUCT_SUBMIT_NAME}" onclick="{SHOP_PRODUCT_SUBMIT_FUNCTION}" /></div></td>\r\n</td>\r\n</tr>\r\n<tr>   \r\n<td height="20" background="images/modules/shop/dotted_line.gif" colspan="4"><img width="1" height="20" border="0" alt="" src="images/modules/shop/pixel.gif" /></td>\r\n</tr>	\r\n</table>\r\n</form>\r\n<!-- END shopProductRow -->\r\n<p>{SHOP_PRODUCT_PAGING}', 'Detaillierte Produktedaten', 'details', 'y', 398, 'off', 'system', 97, '1');
INSERT INTO `contrexx_module_repository` VALUES (404, 16, '<b><font color="#ff0000">{SHOP_STATUS}</font></b>\r\n<!-- BEGIN shopConfirm -->\r\n<form action="index.php?section=shop&amp;cmd=confirm" name="shopForm" method="post">\r\n  <table cellspacing="1" cellpadding="2" width="100%" border="0">\r\n  <tr>\r\n    <td nowrap="nowrap" colspan="5"><b>{TXT_ORDER_INFOS}</b></td>\r\n  </tr>\r\n  <tr>\r\n    <td nowrap><b>{TXT_ID}</b></td>\r\n    <td><b>{TXT_PRODUCT}</b></td>\r\n    <td nowrap><b>{TXT_UNIT_PRICE}</b></td>\r\n    <td nowrap><b>{TXT_QUANTITY}</b></td>\r\n    <td nowrap><div align="right"><b>{TXT_TOTAL}</b></div></td>\r\n  </tr>\r\n  <tr>\r\n    <td colspan="5" nowrap><hr width="100%" color="#cccccc" noShade size="1" />\r\n    </td>\r\n  </tr>\r\n  <!-- BEGIN shopCartRow -->\r\n  <tr style="vertical-align:top;">\r\n    <td nowrap>{SHOP_PRODUCT_ID}</td>\r\n    <td>{SHOP_PRODUCT_TITLE}</td>\r\n    <td nowrap>{SHOP_PRODUCT_ITEMPRICE} {SHOP_UNIT}</td>\r\n    <td nowrap>{SHOP_PRODUCT_QUANTITY}</td>\r\n    <td nowrap><div align="right">{SHOP_PRODUCT_PRICE} {SHOP_UNIT}<br>\r\n    </div></td>\r\n  </tr>\r\n  <!-- END shopCartRow -->\r\n  <tr>\r\n    <td colspan="5"><hr width="100%" color="#cccccc" noShade size=1>\r\n    </td>\r\n  </tr>\r\n<tr>\r\n<td colspan="3" valign="top"><b>{TXT_INTER_TOTAL}</b>{SHOP_TAX_PRODUCTS_TXT}</td>\r\n<td valign="top" nowrap="nowrap">{SHOP_TOTALITEM} {TXT_PRODUCT_S}</td>\r\n<td width="30%" colspan="-1" valign="top" nowrap="nowrap">\r\n<div align="right"><b>{SHOP_TOTALPRICE} {SHOP_UNIT}</b></div></td></tr>\r\n<tr>\r\n<td colspan="5">\r\n<hr width="100%" color="#cccccc" noshade="noshade" size="1" /></td></tr>\r\n<tr valign="top">\r\n<td colspan="4"><strong>{TXT_SHIPPING_METHOD}:</strong> {SHOP_SHIPMENT}\r\n</td>\r\n<td><div align="right">{SHOP_SHIPMENT_PRICE} {SHOP_UNIT}</div></td>\r\n</tr>\r\n<tr valign="top">\r\n<td colspan="4"><strong>{TXT_PAYMENT_TYPE}:</strong> {SHOP_PAYMENT}  \r\n</td>\r\n<td><div align="right"> {SHOP_PAYMENT_PRICE} {SHOP_UNIT}</div></td>\r\n</tr>\r\n<tr>\r\n<td colspan="4" valign="top" nowrap="nowrap">{TXT_PROCENTUAL_TAX_PART}: {SHOP_TAX_PROCENTUAL}</td>\r\n<td width="30%" colspan="-1" valign="top" nowrap="nowrap">\r\n<div align="right">{TXT_TAX_PART} {SHOP_TAX_PRICE} {SHOP_UNIT}</div></td></tr>\r\n<tr>\r\n<td colspan="4" valign="top" nowrap="nowrap"><b>{TXT_TOTAL_PRICE}</b>{SHOP_TAX_GRAND_TXT}</td>\r\n<td width="30%" colspan="-1" valign="top" nowrap="nowrap">\r\n<div align="right"><b>{SHOP_GRAND_TOTAL} {SHOP_UNIT}</b></div></td></tr>\r\n<tr>\r\n<td colspan="5">\r\n<hr width="100%" color="#000000" noshade="noshade" size="1" /></td></tr>\r\n<tr>\r\n  <td colspan="5">\r\n  <TABLE  cellSpacing=2 cellPadding=1 width="100%" border=0>\r\n      <TR>\r\n        <TD noWrap rowspan="2" width="49%"><b>{TXT_ADDRESS_CUSTOMER}</b></TD>\r\n        <TD rowspan="2" width="1%"></TD>\r\n      </TR>\r\n      <TR>\r\n        <TD width="50%"><b>{TXT_SHIPPING_ADDRESS}</b></TD>\r\n      </TR>\r\n      <TR valign="top">\r\n        <TD noWrap width="49%">{SHOP_COMPANY}<br>\r\n        {SHOP_PREFIX}<br>\r\n        {SHOP_LASTNAME}<br>\r\n        {SHOP_FIRSTNAME}<br>\r\n        {SHOP_ADDRESS}<br>\r\n        {SHOP_ZIP} {SHOP_CITY}<br>\r\n        {SHOP_COUNTRY}<br>\r\n        <br>\r\n        {SHOP_PHONE}<br>\r\n        {SHOP_FAX}<br>\r\n        {SHOP_EMAIL}</TD>\r\n        <TD width="1%"></TD>\r\n        <TD width="50%">{SHOP_COMPANY2}<br>\r\n        {SHOP_PREFIX2}<br>\r\n        {SHOP_LASTNAME2}<br>\r\n        {SHOP_FIRSTNAME2}<br>\r\n        {SHOP_ADDRESS2}<br>\r\n        {SHOP_ZIP2} {SHOP_CITY2}<br>\r\n        {SHOP_COUNTRY2}<br>\r\n        <br>\r\n        {SHOP_PHONE2}</TD>\r\n      </TR>\r\n      <TR>\r\n        <TD noWrap colspan="3"><hr width="100%" color="black" noShade size="1" />\r\n        </TD>\r\n      </TR>\r\n  </TABLE>\r\n  </td>\r\n</tr>\r\n<tr>\r\n  <td colspan="5"><b>{TXT_COMMENTS}</b></td>\r\n</tr>\r\n<tr>\r\n  <td colspan="4">{SHOP_CUSTOMERNOTE}</td>\r\n  <td>&nbsp;</td>\r\n</tr>\r\n<tr>\r\n  <td colspan="5"><hr width="100%" color="#000000" noshade="noshade" size="1" /></td>\r\n</tr>\r\n<tr>\r\n  <td colspan="5"><div align="right"><input type="submit" value="{TXT_ORDER_NOW}" name="process" /></div></td>\r\n</tr>\r\n</table>\r\n</form>\r\n<!-- END shopConfirm -->\r\n<!-- BEGIN shopProcess -->\r\n{TXT_ORDER_PREPARED} <br/>\r\n{SHOP_PAYMENT_PROCESSING}\r\n<!-- END shopProcess -->', 'Bestellen', 'confirm', 'y', 398, 'off', 'system', 6, '1');
INSERT INTO `contrexx_module_repository` VALUES (403, 16, '<b><font color="#ff0000">{SHOP_STATUS}</font></b>\r\n<form action="index.php?section=shop&amp;cmd=payment" name="shopForm" method="post">\r\n<table cellspacing="0" cellpadding="0" width="100%" border="0">\r\n<tr valign="middle">\r\n<td align="center">\r\n<table cellspacing="1" cellpadding="2" width="100%" border="0">\r\n<tr>\r\n<td nowrap="nowrap" colspan="2"><b>{TXT_PRODUCTS}</b></td></tr>\r\n<tr>\r\n<td nowrap="nowrap" colspan="2">\r\n<hr width="100%" color="#000000" noshade="noshade" size="1" /></td></tr>\r\n<tr>\r\n<td valign="top"><b>{TXT_TOTALLY_GOODS} </b>{SHOP_TAX_PRODUCTS_TXT}<b>&nbsp;&nbsp;&nbsp;&nbsp;</b>{SHOP_TOTALITEM} {TXT_PRODUCT_S}</td>\r\n<td width="30%" colspan="-1" valign="top" nowrap="nowrap">\r\n<div align="right"><b>{SHOP_TOTALPRICE} {SHOP_UNIT}</b></div></td></tr>\r\n<tr>\r\n<td colspan="2">\r\n<hr width="100%" color="#cccccc" noshade="noshade" size="1" /></td></tr>\r\n<tr valign="top">\r\n<td><strong>{TXT_SHIPPING_METHODS}</strong><br />\r\n{SHOP_SHIPMENT_MENU}\r\n</td>\r\n<td><div align="right"><br>\r\n  {SHOP_SHIPMENT_PRICE} {SHOP_UNIT}</div></td>\r\n</tr>\r\n<tr valign="top">\r\n<td><strong>{TXT_PAYMENT_TYPES}</strong><br />\r\n{SHOP_PAYMENT_MENU}  \r\n</td>\r\n<td><div align="right"> <br>\r\n  {SHOP_PAYMENT_PRICE} {SHOP_UNIT}</div></td>\r\n</tr>\r\n<tr>\r\n<td valign="top" nowrap="nowrap">{TXT_PROCENTUAL_TAX_PART} {SHOP_TAX_PROCENTUAL}</td>\r\n<td width="30%" colspan="-1" valign="top" nowrap="nowrap">\r\n<div align="right">{TXT_TAX_PART} {SHOP_TAX_PRICE} {SHOP_UNIT}</div></td></tr>\r\n<tr>\r\n<td valign="top" nowrap="nowrap"><b>{TXT_TOTAL_PRICE}</b>{SHOP_TAX_GRAND_TXT}</td>\r\n<td width="30%" colspan="-1" valign="top" nowrap="nowrap">\r\n<div align="right"><b>{SHOP_GRAND_TOTAL} {SHOP_UNIT}</b></div></td></tr>\r\n<tr>\r\n<td colspan="2">\r\n<hr width="100%" color="#000000" noshade="noshade" size="1" /></td></tr>\r\n<tr>\r\n<td colspan="2"><b>{TXT_COMMENTS}</b></td>\r\n</tr>\r\n<tr>\r\n<td colspan="2"><textarea name="customer_note" rows="4" cols="52">{SHOP_CUSTOMERNOTE}</textarea> \r\n</td></tr>\r\n<tr>\r\n<td colspan="2">\r\n<hr width="100%" color="#000000" noshade="noshade" size="1" /></td></tr>\r\n<tr>\r\n<td colspan="2"><b>{TXT_TAC}</b></td></tr>\r\n<tr>\r\n<td colspan="2">\r\n<input type="checkbox" value="checked" name="agb" {SHOP_AGB} /> <font color="#ff0000">&nbsp;</font>{TXT_ACCEPT_TAC}</td></tr>\r\n<tr>\r\n<td colspan="2">\r\n<input type="submit" value="{TXT_UPDATE}" name="refresh" /> \r\n<input type="submit" value="{TXT_NEXT}" name="check" /> \r\n</td>\r\n</tr>\r\n</table>\r\n</td>\r\n</tr>\r\n</table>\r\n</form>', 'Bezahlung und Versand', 'payment', 'y', 398, 'off', 'system', 5, '1');
INSERT INTO `contrexx_module_repository` VALUES (402, 0, '<script language="JavaScript" type="text/javascript">\r\n<!--  \r\nfunction shopCopyText()  \r\n{\r\n	with (document.shop){\r\n			prefix2.value= prefix.value;\r\n			company2.value= company.value;\r\n			lastname2.value= lastname.value;\r\n			firstname2.value= firstname.value;\r\n			address2.value=address.value;\r\n			zip2.value= zip.value;\r\n			city2.value= city.value;\r\n			phone2.value= phone.value;				\r\n			return true;\r\n	}\r\n}\r\n-->\r\n</script>\r\n<form name="shop" action="{SHOP_ACCOUNT_ACTION}" method="post">\r\n<table cellspacing="2" cellpadding="1" width="100%" border="0">\r\n<tbody>\r\n<tr>\r\n<td colspan="2"><b>{TXT_SHIPPING_ADDRESS}</b></td>\r\n</tr>\r\n<tr>\r\n<td colspan="2"><font color="#ff0000">* </font>{TXT_REQUIRED_FIELDS}<br />\r\n  <table cellspacing="0" cellpadding="0" width="100%" border="0">\r\n    <tbody>\r\n      <tr>\r\n        <td><b><font color="#ff0000">{SHOP_ACCOUNT_STATUS}</font></b></td>\r\n      </tr>\r\n    </tbody>\r\n  </table>\r\n  </td>\r\n</tr>\r\n<tr valign="top">\r\n  <td colspan="2" nowrap="nowrap">&nbsp;  </td>\r\n  </tr>\r\n<tr valign="top">\r\n<td width="10%" nowrap="NOWRAP">{TXT_COMPANY}</td>\r\n<td><input style="width: 70%;" size="30" value="{SHOP_ACCOUNT_COMPANY}" name="company" tabindex="1" /> </td>\r\n</tr>\r\n<tr valign="top">\r\n<td width="10%" nowrap="NOWRAP">{TXT_GREETING}</td>\r\n<td><input style="width: 70%;" maxlength="50" size="30" value="{SHOP_ACCOUNT_PREFIX}" name="prefix" tabindex="2" /> <b><font color="#ff0000">*</font></b></td>\r\n</tr>\r\n<tr valign="top">\r\n<td width="10%" nowrap="NOWRAP">{TXT_SURNAME}</td>\r\n<td><input style="width: 70%;" size="30" value="{SHOP_ACCOUNT_LASTNAME}" name="lastname" tabindex="3" /> <b><font color="#ff0000">*</font></b></td>\r\n</tr>\r\n<tr valign="top">\r\n<td width="10%" nowrap="NOWRAP">{TXT_FIRSTNAME}&nbsp;&nbsp;</td>\r\n<td><input style="width: 70%;" size="30" value="{SHOP_ACCOUNT_FIRSTNAME}" name="firstname" tabindex="4" /> <b><font color="#ff0000">*</font></b></td>\r\n</tr>\r\n<tr valign="top">\r\n<td width="10%" nowrap="NOWRAP">{TXT_ADDRESS}</td>\r\n<td><input style="width: 70%;" size="30" value="{SHOP_ACCOUNT_ADDRESS}" name="address" tabindex="5" /> <b><font color="#ff0000">*</font></b></td>\r\n</tr>\r\n<tr valign="top">\r\n  <td nowrap="NOWRAP">{TXT_POSTALE_CODE}</td>\r\n  <td><input style="width: 70%;" size="6" value="{SHOP_ACCOUNT_ZIP}" name="zip" tabindex="6" />\r\n      <b><font color="#ff0000">*</font></b></td>\r\n</tr>\r\n<tr valign="top">\r\n<td width="10%" nowrap="NOWRAP">{TXT_CITY}</td>\r\n<td> <input style="width: 70%;" value="{SHOP_ACCOUNT_CITY}" name="city" tabindex="7" /> <b><font color="#ff0000">*</font></b> </td>\r\n</tr>\r\n<tr valign="top">\r\n<td width="10%" nowrap="NOWRAP">{TXT_COUNTRY}</td>\r\n<td>{SHOP_ACCOUNT_COUNTRY}</td>\r\n</tr>\r\n<tr valign="top">\r\n<td width="10%" nowrap="NOWRAP">{TXT_PHONE_NUMBER}&nbsp;&nbsp;</td>\r\n<td><input style="width: 70%;" value="{SHOP_ACCOUNT_PHONE}" name="phone" tabindex="8" /> <b><font color="#ff0000">*</font></b></td>\r\n</tr>\r\n<tr valign="top">\r\n<td width="10%" nowrap="NOWRAP">{TXT_FAX_NUMBER}</td>\r\n<td><input style="width: 70%;" value="{SHOP_ACCOUNT_FAX}" name="fax" tabindex="9" /> </td>\r\n</tr>\r\n<tr valign="top">\r\n  <td width="10%" nowrap="NOWRAP">{TXT_EMAIL}</td>\r\n  <td><input style="width: 70%;" size="30" value="{SHOP_ACCOUNT_EMAIL}" name="email" tabindex="19" />\r\n      <b><font color="#ff0000">*</font></b> </td>\r\n</tr>\r\n<tr valign="top">\r\n  <td width="10%" nowrap="NOWRAP">\r\n  <input type="hidden" size="30" value="" name="company2" tabindex="11" />\r\n    <input type="hidden" maxlength="50" size="30" value="" name="prefix2" tabindex="12" />\r\n    <input type="hidden" size="30" value="" name="lastname2" tabindex="13" />\r\n    <input type="hidden" size="30" value="" name="firstname2" tabindex="14" />\r\n    <input type="hidden" size="30" value="" name="address2" tabindex="15" />\r\n    <input type="hidden" size="6" value="" name="zip2" tabindex="16" />\r\n    <input type="hidden" value="" name="city2" tabindex="17" />\r\n    <input type="hidden" value="" name="phone2" tabindex="18" />\r\n    <input type="hidden" size="30" value="Kein Passwort erforderlich" name="password" tabindex="20" /></td>\r\n  <td>&nbsp;</td>\r\n</tr>\r\n<tr valign="top">\r\n  <td colspan="2" nowrap="NOWRAP"><input type="reset" value="{TXT_RESET}" name="reset" tabindex="21" />\r\n    <input type="submit" value="{TXT_NEXT}  >>" name="Submit" onClick="shopCopyText();" tabindex="22" /></td>\r\n  </tr>\r\n</tbody>\r\n</table>\r\n<br />\r\n</form>', 'Kontoangaben Kurzform', '', 'y', 398, 'off', 'system', 4, '1');
INSERT INTO `contrexx_module_repository` VALUES (401, 16, '<script language=\\"JavaScript\\" type=\\"text/javascript\\">\r\n<!--  \r\nfunction shopCopyText()  \r\n{\r\n	with (document.shop){\r\n		if(equalAddress.checked) {\r\n			prefix2.value= prefix.value;\r\n			company2.value= company.value;\r\n			lastname2.value= lastname.value;\r\n			firstname2.value= firstname.value;\r\n			address2.value=address.value;\r\n			zip2.value= zip.value;\r\n			city2.value= city.value;\r\n			phone2.value= phone.value;				\r\n			return true;\r\n		} else {	\r\n			prefix2.value= \\"\\";\r\n			company2.value= \\"\\";\r\n			lastname2.value= \\"\\";\r\n			firstname2.value= \\"\\";\r\n			address2.value=\\"\\";\r\n			zip2.value= \\"\\";\r\n			city2.value= \\"\\";\r\n			phone2.value= \\"\\";\r\n			return true;\r\n		}\r\n	}\r\n}\r\n-->\r\n</script>\r\n<form name=\\"shop\\" action=\\"{SHOP_ACCOUNT_ACTION}\\" method=\\"post\\">\r\n<table cellspacing=\\"2\\" cellpadding=\\"1\\" width=\\"100%\\" border=\\"0\\">\r\n<tbody>\r\n<tr>\r\n<td colspan=\\"2\\"><b>{TXT_CUSTOMER_ADDRESS}</b></td>\r\n</tr>\r\n<tr>\r\n<td colspan=\\"2\\"><font color=\\"#ff0000\\">* </font>{TXT_REQUIRED_FIELDS}<br />\r\n  <table cellspacing=\\"0\\" cellpadding=\\"0\\" width=\\"100%\\" border=\\"0\\">\r\n    <tbody>\r\n      <tr>\r\n        <td><b><font color=\\"#ff0000\\">{SHOP_ACCOUNT_STATUS}</font></b></td>\r\n      </tr>\r\n    </tbody>\r\n  </table>\r\n  </td>\r\n</tr>\r\n<tr valign=\\"top\\">\r\n  <td colspan=\\"2\\" nowrap=\\"nowrap\\">&nbsp;  </td>\r\n  </tr>\r\n<tr valign=\\"top\\">\r\n<td nowrap=\\"nowrap\\">{TXT_COMPANY}</td>\r\n<td><input size=\\"30\\" value=\\"{SHOP_ACCOUNT_COMPANY}\\" name=\\"company\\" tabindex=\\"1\\" /> </td>\r\n</tr>\r\n<tr valign=\\"top\\">\r\n<td nowrap=\\"nowrap\\">{TXT_GREETING}</td>\r\n<td><input maxlength=\\"50\\" size=\\"30\\" value=\\"{SHOP_ACCOUNT_PREFIX}\\" name=\\"prefix\\" tabindex=\\"2\\" /> <b><font color=\\"#ff0000\\">*</font></b></td>\r\n</tr>\r\n<tr valign=\\"top\\">\r\n<td nowrap=\\"nowrap\\">{TXT_SURNAME}</td>\r\n<td><input size=\\"30\\" value=\\"{SHOP_ACCOUNT_LASTNAME}\\" name=\\"lastname\\" tabindex=\\"3\\" /> <b><font color=\\"#ff0000\\">*</font></b></td>\r\n</tr>\r\n<tr valign=\\"top\\">\r\n<td nowrap=\\"nowrap\\">{TXT_FIRSTNAME}&nbsp;&nbsp;</td>\r\n<td><input size=\\"30\\" value=\\"{SHOP_ACCOUNT_FIRSTNAME}\\" name=\\"firstname\\" tabindex=\\"4\\" /> <b><font color=\\"#ff0000\\">*</font></b></td>\r\n</tr>\r\n<tr valign=\\"top\\">\r\n<td nowrap=\\"nowrap\\">{TXT_ADDRESS}</td>\r\n<td><input size=\\"30\\" value=\\"{SHOP_ACCOUNT_ADDRESS}\\" name=\\"address\\" tabindex=\\"5\\" /> <b><font color=\\"#ff0000\\">*</font></b></td>\r\n</tr>\r\n<tr valign=\\"top\\">\r\n<td nowrap=\\"nowrap\\">{TXT_POSTALE_CODE}</td>\r\n<td><input size=\\"6\\" value=\\"{SHOP_ACCOUNT_ZIP}\\" name=\\"zip\\" tabindex=\\"6\\" /> <b><font color=\\"#ff0000\\">*</font></b> {TXT_CITY} <input value=\\"{SHOP_ACCOUNT_CITY}\\" name=\\"city\\" tabindex=\\"7\\" /> <b><font color=\\"#ff0000\\">*</font></b> </td>\r\n</tr>\r\n<tr valign=\\"top\\">\r\n<td nowrap=\\"nowrap\\">{TXT_COUNTRY}</td>\r\n<td>{SHOP_ACCOUNT_COUNTRY}</td>\r\n</tr>\r\n<tr valign=\\"top\\">\r\n<td nowrap=\\"nowrap\\">{TXT_PHONE_NUMBER}</td>\r\n<td><input value=\\"{SHOP_ACCOUNT_PHONE}\\" name=\\"phone\\" tabindex=\\"8\\" /> <b><font color=\\"#ff0000\\">*</font></b></td>\r\n</tr>\r\n<tr valign=\\"top\\">\r\n<td nowrap=\\"nowrap\\">{TXT_FAX_NUMBER}</td>\r\n<td><input value=\\"{SHOP_ACCOUNT_FAX}\\" name=\\"fax\\" tabindex=\\"9\\" /> </td>\r\n</tr>\r\n<tr valign=\\"top\\">\r\n  <td colspan=\\"2\\" nowrap=\\"nowrap\\"><hr width=\\"100%\\" color=\\"#000000\\" noshade=\\"noshade\\" size=\\"1\\" /></td>\r\n  </tr>\r\n<tr valign=\\"top\\">\r\n  <td nowrap=\\"nowrap\\"><b>{TXT_SHIPPING_ADDRESS}</b></td>\r\n  <td>&nbsp;</td>\r\n  </tr>\r\n<tr valign=\\"top\\">\r\n  <td nowrap=\\"nowrap\\">&nbsp;</td>\r\n  <td><input type=\\"checkbox\\" value=\\"checked\\" name=\\"equalAddress\\" onClick=\\"shopCopyText();\\" {SHOP_ACCOUNT_EQUAL_ADDRESS} tabindex=\\"10\\" />\r\n{TXT_SAME_BILLING_ADDRESS}</td>\r\n  </tr>\r\n<tr valign=\\"top\\">\r\n  <td nowrap=\\"NOWRAP\\">{TXT_COMPANY}</td>\r\n  <td><input size=\\"30\\" value=\\"{SHOP_ACCOUNT_COMPANY2}\\" name=\\"company2\\" tabindex=\\"11\\" /></td>\r\n  </tr>\r\n<tr valign=\\"top\\">\r\n  <td nowrap=\\"NOWRAP\\">{TXT_GREETING}</td>\r\n  <td><input maxlength=\\"50\\" size=\\"30\\" value=\\"{SHOP_ACCOUNT_PREFIX2}\\" name=\\"prefix2\\" tabindex=\\"12\\" />\r\n      <b><font color=\\"#ff0000\\">*</font></b></td>\r\n  </tr>\r\n<tr valign=\\"top\\">\r\n  <td nowrap=\\"NOWRAP\\">{TXT_SURNAME}</td>\r\n  <td><input size=\\"30\\" value=\\"{SHOP_ACCOUNT_LASTNAME2}\\" name=\\"lastname2\\" tabindex=\\"13\\" />\r\n      <b><font color=\\"#ff0000\\">*</font></b></td>\r\n  </tr>\r\n<tr valign=\\"top\\">\r\n  <td nowrap=\\"NOWRAP\\">{TXT_FIRSTNAME}&nbsp;&nbsp; </td>\r\n  <td><input size=\\"30\\" value=\\"{SHOP_ACCOUNT_FIRSTNAME2}\\" name=\\"firstname2\\" tabindex=\\"14\\" />\r\n      <b><font color=\\"#ff0000\\">*</font></b></td>\r\n  </tr>\r\n<tr valign=\\"top\\">\r\n  <td nowrap=\\"NOWRAP\\">{TXT_ADDRESS}</td>\r\n  <td><input size=\\"30\\" value=\\"{SHOP_ACCOUNT_ADDRESS2}\\" name=\\"address2\\" tabindex=\\"15\\" />\r\n      <b><font color=\\"#ff0000\\">*</font></b></td>\r\n  </tr>\r\n<tr valign=\\"top\\">\r\n  <td nowrap=\\"NOWRAP\\">{TXT_POSTALE_CODE}</td>\r\n  <td><input size=\\"6\\" value=\\"{SHOP_ACCOUNT_ZIP2}\\" name=\\"zip2\\" tabindex=\\"16\\" />\r\n      <b><font color=\\"#ff0000\\">*</font></b> {TXT_CITY}\r\n      <input value=\\"{SHOP_ACCOUNT_CITY2}\\" name=\\"city2\\" tabindex=\\"17\\" />\r\n      <b><font color=\\"#ff0000\\">*</font></b> </td>\r\n  </tr>\r\n<tr valign=\\"top\\">\r\n  <td nowrap=\\"NOWRAP\\">{TXT_COUNTRY}</td>\r\n  <td>{SHOP_ACCOUNT_COUNTRY2}</td>\r\n  </tr>\r\n<tr valign=\\"top\\">\r\n  <td nowrap=\\"NOWRAP\\">{TXT_PHONE_NUMBER}</td>\r\n  <td><input value=\\"{SHOP_ACCOUNT_PHONE2}\\" name=\\"phone2\\" tabindex=\\"18\\" />\r\n      <b><font color=\\"#ff0000\\">*</font></b></td>\r\n  </tr>\r\n<!-- BEGIN account_details -->\r\n<tr valign=\\"top\\">\r\n  <td colspan=\\"2\\" nowrap=\\"NOWRAP\\"><hr width=\\"100%\\" color=\\"#000000\\" noshade=\\"noshade\\" size=\\"1\\" /></td>\r\n  </tr>\r\n<tr valign=\\"top\\">\r\n  <td nowrap=\\"NOWRAP\\"><b>{TXT_YOUR_ACCOUNT_DETAILS}</b></td>\r\n  <td>&nbsp;</td>\r\n</tr>\r\n<tr valign=\\"top\\">\r\n  <td nowrap=\\"nowrap\\">{TXT_EMAIL}</td>\r\n  <td><input size=\\"30\\" value=\\"{SHOP_ACCOUNT_EMAIL}\\" name=\\"email\\" tabindex=\\"19\\" />\r\n      <b><font color=\\"#ff0000\\">*</font></b> </td>\r\n</tr>\r\n<tr valign=\\"top\\">\r\n  <td nowrap=\\"nowrap\\">{TXT_PASSWORD}</td>\r\n  <td><input type=\\"password\\" size=\\"30\\" value=\\"\\" name=\\"password\\" tabindex=\\"20\\" />\r\n      <b><font color=\\"#ff0000\\">*</font></b><br />\r\n    {TXT_PASSWORD_MIN_CHARS}</td>\r\n</tr>\r\n<!-- END account_details -->\r\n<tr valign=\\"top\\">\r\n  <td nowrap=\\"NOWRAP\\">&nbsp;</td>\r\n  <td>&nbsp;</td>\r\n</tr>\r\n<tr valign=\\"top\\">\r\n  <td colspan=\\"2\\" nowrap=\\"NOWRAP\\"><input type=\\"reset\\" value=\\"{TXT_RESET}\\" name=\\"reset\\" tabindex=\\"21\\" />\r\n    <input type=\\"submit\\" value=\\"{TXT_NEXT}  >>\\" name=\\"Submit\\" tabindex=\\"22\\" /></td>\r\n  </tr>\r\n</tbody>\r\n</table>\r\n<br />\r\n</form>', 'Kontoangaben', 'account', 'y', 398, 'off', 'system', 4, '1');
INSERT INTO `contrexx_module_repository` VALUES (329, 0, '<script language="JavaScript" type="text/javascript">\r\n<!--  \r\nfunction shopCopyText()  \r\n{\r\n	with (document.shop){\r\n			prefix2.value= prefix.value;\r\n			company2.value= company.value;\r\n			lastname2.value= lastname.value;\r\n			firstname2.value= firstname.value;\r\n			address2.value=address.value;\r\n			zip2.value= zip.value;\r\n			city2.value= city.value;\r\n			phone2.value= phone.value;				\r\n			return true;\r\n	}\r\n}\r\n-->\r\n</script>\r\n<form name="shop" action="{SHOP_ACCOUNT_ACTION}" method="post">\r\n<table cellspacing="2" cellpadding="1" width="100%" border="0">\r\n<tbody>\r\n<tr>\r\n<td colspan="2"><b>{TXT_SHIPPING_ADDRESS}</b></td>\r\n</tr>\r\n<tr>\r\n<td colspan="2"><font color="#ff0000">* </font>{TXT_REQUIRED_FIELDS}<br />\r\n  <table cellspacing="0" cellpadding="0" width="100%" border="0">\r\n    <tbody>\r\n      <tr>\r\n        <td><b><font color="#ff0000">{SHOP_ACCOUNT_STATUS}</font></b></td>\r\n      </tr>\r\n    </tbody>\r\n  </table>\r\n  </td>\r\n</tr>\r\n<tr valign="top">\r\n  <td colspan="2" nowrap="nowrap">&nbsp;  </td>\r\n  </tr>\r\n<tr valign="top">\r\n<td width="10%" nowrap="NOWRAP">{TXT_COMPANY}</td>\r\n<td><input style="width: 70%;" size="30" value="{SHOP_ACCOUNT_COMPANY}" name="company" tabindex="1" /> </td>\r\n</tr>\r\n<tr valign="top">\r\n<td width="10%" nowrap="NOWRAP">{TXT_GREETING}</td>\r\n<td><input style="width: 70%;" maxlength="50" size="30" value="{SHOP_ACCOUNT_PREFIX}" name="prefix" tabindex="2" /> <b><font color="#ff0000">*</font></b></td>\r\n</tr>\r\n<tr valign="top">\r\n<td width="10%" nowrap="NOWRAP">{TXT_SURNAME}</td>\r\n<td><input style="width: 70%;" size="30" value="{SHOP_ACCOUNT_LASTNAME}" name="lastname" tabindex="3" /> <b><font color="#ff0000">*</font></b></td>\r\n</tr>\r\n<tr valign="top">\r\n<td width="10%" nowrap="NOWRAP">{TXT_FIRSTNAME}&nbsp;&nbsp;</td>\r\n<td><input style="width: 70%;" size="30" value="{SHOP_ACCOUNT_FIRSTNAME}" name="firstname" tabindex="4" /> <b><font color="#ff0000">*</font></b></td>\r\n</tr>\r\n<tr valign="top">\r\n<td width="10%" nowrap="NOWRAP">{TXT_ADDRESS}</td>\r\n<td><input style="width: 70%;" size="30" value="{SHOP_ACCOUNT_ADDRESS}" name="address" tabindex="5" /> <b><font color="#ff0000">*</font></b></td>\r\n</tr>\r\n<tr valign="top">\r\n  <td nowrap="NOWRAP">{TXT_POSTALE_CODE}</td>\r\n  <td><input style="width: 70%;" size="6" value="{SHOP_ACCOUNT_ZIP}" name="zip" tabindex="6" />\r\n      <b><font color="#ff0000">*</font></b></td>\r\n</tr>\r\n<tr valign="top">\r\n<td width="10%" nowrap="NOWRAP">{TXT_CITY}</td>\r\n<td> <input style="width: 70%;" value="{SHOP_ACCOUNT_CITY}" name="city" tabindex="7" /> <b><font color="#ff0000">*</font></b> </td>\r\n</tr>\r\n<tr valign="top">\r\n<td width="10%" nowrap="NOWRAP">{TXT_COUNTRY}</td>\r\n<td>{SHOP_ACCOUNT_COUNTRY}</td>\r\n</tr>\r\n<tr valign="top">\r\n<td width="10%" nowrap="NOWRAP">{TXT_PHONE_NUMBER}&nbsp;&nbsp;</td>\r\n<td><input style="width: 70%;" value="{SHOP_ACCOUNT_PHONE}" name="phone" tabindex="8" /> <b><font color="#ff0000">*</font></b></td>\r\n</tr>\r\n<tr valign="top">\r\n<td width="10%" nowrap="NOWRAP">{TXT_FAX_NUMBER}</td>\r\n<td><input style="width: 70%;" value="{SHOP_ACCOUNT_FAX}" name="fax" tabindex="9" /> </td>\r\n</tr>\r\n<tr valign="top">\r\n  <td width="10%" nowrap="NOWRAP">{TXT_EMAIL}</td>\r\n  <td><input style="width: 70%;" size="30" value="{SHOP_ACCOUNT_EMAIL}" name="email" tabindex="19" />\r\n      <b><font color="#ff0000">*</font></b> </td>\r\n</tr>\r\n<tr valign="top">\r\n  <td width="10%" nowrap="NOWRAP">\r\n  <input type="hidden" size="30" value="" name="company2" tabindex="11" />\r\n    <input type="hidden" maxlength="50" size="30" value="" name="prefix2" tabindex="12" />\r\n    <input type="hidden" size="30" value="" name="lastname2" tabindex="13" />\r\n    <input type="hidden" size="30" value="" name="firstname2" tabindex="14" />\r\n    <input type="hidden" size="30" value="" name="address2" tabindex="15" />\r\n    <input type="hidden" size="6" value="" name="zip2" tabindex="16" />\r\n    <input type="hidden" value="" name="city2" tabindex="17" />\r\n    <input type="hidden" value="" name="phone2" tabindex="18" />\r\n    <input type="hidden" size="30" value="Kein Passwort erforderlich" name="password" tabindex="20" /></td>\r\n  <td>&nbsp;</td>\r\n</tr>\r\n<tr valign="top">\r\n  <td colspan="2" nowrap="NOWRAP"><input type="reset" value="{TXT_RESET}" name="reset" tabindex="21" />\r\n    <input type="submit" value="{TXT_NEXT}  >>" name="Submit" onClick="shopCopyText();" tabindex="22" /></td>\r\n  </tr>\r\n</tbody>\r\n</table>\r\n<br />\r\n</form>', 'Kontoangaben Kurzform', '', 'y', 326, 'off', 'system', 4, '1');
INSERT INTO `contrexx_module_repository` VALUES (441, 11, '<table width=\\"100%\\">\r\n<!-- BEGIN sitemap -->\r\n<tr>\r\n<td id=\\"{STYLE}\\">{SPACER}<a href=\\"{URL}\\" title=\\"{NAME}\\">{NAME}</a></td>\r\n</tr>\r\n<!-- END sitemap -->\r\n</table>', 'Sitemap', '', 'y', 0, 'on', 'system', 111, '1');
INSERT INTO `contrexx_module_repository` VALUES (351, 24, '{MEDIA_JAVASCRIPT}\r\n<table id=\\"media\\" cellspacing=\\"0\\" cellpadding=\\"5\\" width=\\"100%\\" border=\\"0\\">\r\n    <tbody>\r\n        <tr class=\\"head\\">\r\n            <td align=\\"center\\" width=\\"16\\"><strong>#</strong></td>\r\n            <td colspan=\\"2\\"><a title=\\"{TXT_MEDIA_SORT}\\" href=\\"{MEDIA_NAME_HREF}\\"><strong>{TXT_MEDIA_FILE_NAME}</strong></a> {MEDIA_NAME_ICON}</td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\"><a title=\\"{TXT_MEDIA_SORT}\\" href=\\"{MEDIA_SIZE_HREF}\\" name=\\"sort_size\\"><strong>{TXT_MEDIA_FILE_SIZE}</strong></a> {MEDIA_SIZE_ICON} </td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\"><a title=\\"{TXT_MEDIA_SORT}\\" href=\\"{MEDIA_TYPE_HREF}\\" name=\\"sort_type\\"><strong>{TXT_MEDIA_FILE_TYPE}</strong></a> {MEDIA_TYPE_ICON} </td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\"><a title=\\"{TXT_MEDIA_SORT}\\" href=\\"{MEDIA_DATE_HREF}\\" name=\\"sort_date\\"><strong>{TXT_MEDIA_FILE_DATE}</strong></a> {MEDIA_DATE_ICON} </td>\r\n        </tr>\r\n        <tr class=\\"row2\\" valign=\\"middle\\">\r\n            <td align=\\"center\\" width=\\"16\\"><img title=\\"base\\" height=\\"16\\" alt=\\"base\\" src=\\"images/modules/media/_base.gif\\" width=\\"16\\"/> </td>\r\n            <td colspan=\\"5\\"><strong><a title=\\"{MEDIA_TREE_NAV_MAIN}\\" href=\\"{MEDIA_TREE_NAV_MAIN_HREF}\\">{MEDIA_TREE_NAV_MAIN}</a></strong> <!-- BEGIN mediaTreeNavigation --><a href=\\"{MEDIA_TREE_NAV_DIR_HREF}\\">&nbsp;{MEDIA_TREE_NAV_DIR} /</a> <!-- END mediaTreeNavigation --></td>\r\n        </tr>\r\n        <!-- BEGIN mediaDirectoryTree -->\r\n        <tr class=\\"{MEDIA_DIR_TREE_ROW}\\" valign=\\"middle\\">\r\n            <td width=\\"16\\">&nbsp;</td>\r\n            <td align=\\"center\\" width=\\"16\\"><img title=\\"icon\\" height=\\"16\\" alt=\\"icon\\" src=\\"{MEDIA_FILE_ICON}\\" width=\\"16\\"/></td>\r\n            <td width=\\"100%\\"><a title=\\"{MEDIA_FILE_NAME}\\" href=\\"{MEDIA_FILE_NAME_HREF}\\">{MEDIA_FILE_NAME}</a></td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\">{MEDIA_FILE_SIZE}&nbsp;</td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\">{MEDIA_FILE_TYPE}&nbsp;</td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\">{MEDIA_FILE_DATE}&nbsp;</td>\r\n        </tr>\r\n        <!-- END mediaDirectoryTree --><!-- BEGIN mediaEmptyDirectory -->\r\n        <tr class=\\"row1\\">\r\n            <td>&nbsp;</td>\r\n            <td colspan=\\"5\\">{TXT_MEDIA_DIR_EMPTY}</td>\r\n        </tr>\r\n        <!-- END mediaEmptyDirectory -->\r\n    </tbody>\r\n</table>', 'Media Archiv #2', '', 'y', 0, 'on', 'system', 2, '2');
INSERT INTO `contrexx_module_repository` VALUES (315, 10, '<b><a href="index.php?section=guestbook&amp;cmd=post" title="Eintragen">Eintragen</a></b><br />\r\n{GUESTBOOK_TOTAL_ENTRIES} Einträge im Gästebuch.<br />\r\n{GUESTBOOK_PAGING}<br />\r\n{GUESTBOOK_STATUS}\r\n<table cellspacing="1" cellpadding="1" width="100%" border="0">\r\n	<!-- BEGIN guestbook_row -->\r\n		<tr class="{GUESTBOOK_ROWCLASS}"> \r\n			<td valign="top"><img alt="" hspace="0" src="images/modules/guestbook/post.gif"  border="0" />{GUESTBOOK_DATE} - <strong>{GUESTBOOK_NICK}</strong> {GUESTBOOK_GENDER} {GUESTBOOK_LOCATION}</td>\r\n			<td  valign="top" nowrap="nowrap"><div align="right">{GUESTBOOK_EMAIL} {GUESTBOOK_URL} </div></td>\r\n		</tr>\r\n		<tr class="{GUESTBOOK_ROWCLASS}"> \r\n			<td valign="top" colspan="2"><hr noshade="noshade" size="1" />{GUESTBOOK_COMMENT}<br /><br /></td>\r\n		</tr>\r\n	<!-- END guestbook_row -->\r\n</table>\r\n<br />', 'Gästebuch', '', 'n', 0, 'on', 'system', 8, '1');
INSERT INTO `contrexx_module_repository` VALUES (316, 10, '{GUESTBOOK_JAVASCRIPT}\r\nSie können sich hier ins Gästebuch eintragen. <br /> Damit der Eintrag klappt, sollten mindestens alle mit einem <font color="red">*</font> \r\nmarkierten Felder ausgefüllt werden. \r\n<br />\r\n<form name="GuestbookForm" action="index.php?section=guestbook" method="post" onsubmit="return validate(this)">\r\n<br />\r\n<b>Name:</b><font color="red"> *</font> <br />\r\n<input style="width: 350px;" maxlength="255" size="60" name="nickname" id="nickname" /> <br /><br /><b>Kommentar:</b><font color="red"> *</font> \r\n<br />\r\n<textarea style="width: 350px;" name="comment" id="comment" rows="6" cols="60"></textarea><br /><br /><b>Geschlecht: </b><font color="red">*</font>\r\n<br />\r\n<input type="radio" checked="checked" value="F" name="malefemale" /> Weiblich<br />\r\n<input type="radio" value="M" name="malefemale" /> Männlich<br /><br /><b>Wohnort:</b> <font color="red">*</font>\r\n<br />\r\n<input style="width: 350px;" maxlength="255" size="60" name="location" id="location" /> <br /><b>E-mail:</b>&nbsp;<font color="#ff0000">*</font>\r\n<br />\r\n<input style="width: 350px;" maxlength="255" size="60" name="email" id="email" /> <br /><b>Homepage:</b>\r\n<br />\r\n<input style="width: 350px;" name="url" value="http://" size="60" maxlength="255" /> \r\n<br />\r\n<br />\r\n<input type="reset" value="&nbsp;Reset&nbsp;" name="Submit" />&nbsp;&nbsp;\r\n<input type="submit" value="&nbsp;Speichern&nbsp;" name="Submit" /> \r\n</form>', 'Eintragen', 'post', 'y', 315, 'on', 'system', 1, '1');
INSERT INTO `contrexx_module_repository` VALUES (400, 16, '<!-- BEGIN shopCart -->\r\n<form action =\\"index.php?section=shop&amp;cmd=cart\\" name=\\"shopForm\\" method =\\"post\\">\r\n  <table width=\\"100%\\" cellpadding=\\"0\\" cellspacing=\\"0\\" border=\\"0\\">\r\n    <tr valign=\\"middle\\"> \r\n      <td align=\\"center\\">\r\n        <table width=\\"100%\\" border=\\"0\\" cellpadding=\\"2\\" cellspacing=\\"1\\">\r\n          <tr> \r\n            <td colspan=\\"5\\"> \r\n              <hr width=\\"100%\\" noshade=\\"noshade\\" color=\\"black\\" size=\\"1\\" />\r\n            </td>\r\n          </tr>\r\n          <tr valign=\\"top\\"> \r\n            <td width=\\"10%\\"><div align=\\"left\\"><b>{TXT_PRODUCT_ID}</b></div></td>\r\n            <td width=\\"45%\\"><div align=\\"left\\"><b>{TXT_PRODUCT}</b></div></td>\r\n            <td width=\\"15%\\"><div align=\\"left\\"><b>{TXT_UNIT_PRICE}</b></div></td>\r\n            <td width=\\"12%\\"><div align=\\"left\\"><b>{TXT_QUANTITY}</b></div></td>\r\n            <td width=\\"25%\\"><div align=\\"right\\"><b>{TXT_TOTAL}</b></div></td>\r\n          </tr>\r\n          <tr> \r\n            <td colspan=\\"5\\" valign=\\"top\\"> \r\n              <hr width=\\"100%\\" color=\\"#cccccc\\" noshade=\\"noshade\\" size=\\"1\\" />\r\n            </td>\r\n          </tr>\r\n          <!-- BEGIN shopCartRow -->\r\n          <tr valign=\\"top\\"> \r\n            <td><div align=\\"left\\">{SHOP_PRODUCT_ID}</div></td>\r\n            <td><div align=\\"left\\"><a href =\\"?section=shop&amp;cmd=details&amp;referer=cart&amp;productId={SHOP_PRODUCT_CART_ID}\\">{SHOP_PRODUCT_TITLE}</a>{SHOP_PRODUCT_OPTIONS}</div></td>\r\n            <td><div align=\\"left\\">{SHOP_PRODUCT_ITEMPRICE} {SHOP_PRODUCT_ITEMPRICE_UNIT}</div></td>\r\n            <td><div align=\\"left\\"><input class=\\"form\\" type=\\"text\\" name=\\"quantity[{SHOP_PRODUCT_CART_ID}]\\" value=\\"{SHOP_PRODUCT_QUANTITY}\\" size=\\"3\\" />\r\n            </div></td>\r\n            <td width=\\"25%\\"> \r\n              <div align=\\"right\\">{SHOP_PRODUCT_PRICE} {SHOP_PRODUCT_PRICE_UNIT} </div>\r\n            </td>\r\n          </tr>\r\n          <!-- END shopCartRow -->\r\n          <tr> \r\n            <td colspan=\\"5\\" valign=\\"top\\"> \r\n              <hr width=\\"100%\\" color=\\"#cccccc\\" noshade=\\"noshade\\" size=\\"1\\" />\r\n            </td>\r\n          </tr>\r\n          <tr> \r\n            <td colspan=\\"3\\" valign=\\"top\\"><div align=\\"left\\"><b>{TXT_INTER_TOTAL}</b></div></td>\r\n            <td width=\\"17%\\" valign=\\"top\\"><div align=\\"left\\"><b>{SHOP_PRODUCT_TOTALITEM}</b></div></td>\r\n            <td width=\\"25%\\" valign=\\"top\\"> \r\n              <div align=\\"right\\"><b>{SHOP_PRODUCT_TOTALPRICE} {SHOP_PRODUCT_TOTALPRICE_UNIT}<br />\r\n                </b> </div>\r\n            </td>\r\n          </tr>\r\n          <tr> \r\n            <td colspan=\\"5\\" valign=\\"top\\"> \r\n              <hr width=\\"100%\\" color=\\"black\\" noshade=\\"noshade\\" size=\\"1\\" />\r\n            </td>\r\n          </tr>\r\n          <tr> \r\n            <td valign=\\"top\\"> \r\n              <strong>{TXT_SHIP_COUNTRY}</strong></td>\r\n            <td colspan=\\"3\\" valign=\\"top\\">{SHOP_COUNTRIES_MENU} </td>\r\n            <td valign=\\"top\\"><div align=\\"right\\">\r\n                <input type=\\"submit\\" name=\\"update\\" value=\\"{TXT_UPDATE}\\" />\r\n            </div></td>\r\n          </tr>\r\n          <tr>\r\n            <td colspan=\\"5\\" valign=\\"top\\">&nbsp;</td>\r\n          </tr>\r\n          <tr>\r\n            <td colspan=\\"5\\" valign=\\"top\\"><div align=\\"right\\">\r\n                <input type=\\"submit\\" name=\\"continue\\" value=\\"{TXT_NEXT}  >>\\" />\r\n            </div></td>\r\n          </tr>\r\n        </table>\r\n      </td>\r\n  </tr>\r\n</table>\r\n</form>\r\n<!-- END shopCart -->\r\n<br />\r\n<b><a href=\\"index.php?section=shop\\" title=\\"{TXT_CONTINUE_SHOPPING}\\">{TXT_CONTINUE_SHOPPING}</a><br />\r\n<a href=\\"index.php?section=shop&amp;act=destroy\\" title=\\"{TXT_EMPTY_CART}\\">{TXT_EMPTY_CART}</a></b>\r\n<br />', 'Ihr Warenkorb', 'cart', 'y', 398, 'on', 'system', 2, '1');
INSERT INTO `contrexx_module_repository` VALUES (506, 22, '<!-- START feed.html -->\r\n{FEED_NO_NEWSFEED}\r\n<!-- BEGIN feed_table -->\r\n<table cellspacing=\\"0\\" cellpadding=\\"0\\" border=\\"0\\">\r\n  <tr> \r\n    <td valign=\\"top\\" nowrap=\\"nowrap\\"> \r\n      <!-- BEGIN feed_cat -->\r\n      <b>{FEED_CAT_NAME}</b><br />\r\n      <!-- BEGIN feed_news -->\r\n      &nbsp;&nbsp;&nbsp;&nbsp;<a href=\\"{FEED_NEWS_LINK}\\">{FEED_NEWS_NAME}</a><br />\r\n      <!-- END feed_news -->\r\n      <!-- END feed_cat -->\r\n    </td>\r\n  </tr>\r\n  <tr> \r\n    <td valign=\\"top\\" nowrap=\\"nowrap\\">\r\n      <div  style=\\"overflow:auto;width: 500px;\\">  <br />\r\n      <!-- BEGIN feed_show_news -->\r\n      <br /><b>{FEED_CAT}</b> &gt; <b>{FEED_PAGE}</b> ({FEED_TITLE})<br />\r\n      {FEED_IMAGE} {TXT_FEED_LAST_UPTDATE}: {FEED_TIME}<br />\r\n      <br />\r\n      <ul>\r\n	  \r\n      <!-- BEGIN feed_output_news -->      \r\n       <li><a href=\\"{FEED_LINK}\\" target=\\"_blank\\">{FEED_NAME}</a></li>     \r\n      <!-- END feed_output_news --> \r\n      </ul></div>\r\n      <!-- END feed_show_news -->\r\n    </td>\r\n  </tr>\r\n</table>\r\n<!-- END feed_table -->\r\n<!-- END feed.html -->', 'News-Syndication', '', 'y', 0, 'on', 'system', 4, '1');
INSERT INTO `contrexx_module_repository` VALUES (507, 22, '{NEWSML_TITLE}<br /><br />{NEWSML_TEXT}<br /> <a href="javascript:window.history.back();">&lt; zur&uuml;ck</a>', 'Newsmeldung', 'newsML', 'y', 506, 'on', 'system', 1, '1');
INSERT INTO `contrexx_module_repository` VALUES (510, 21, '<!-- START calendar_standard_view.html -->\r\n{CALENDAR_JAVASCRIPT}\r\n\r\n<div style=\\"margin: auto; width: 200px;\\">\r\n{CALENDAR}\r\n{CALENDAR_CATEGORIES}\r\n<br />\r\n<br />\r\n</div>\r\n\r\n<span style=\\"font-size: 11px; font-weight: bold;\\">{TXT_CALENDAR_SEARCH}:</span>\r\n<form action=\\"?section=calendar&amp;act=search\\" method=\\"post\\" id=\\"searchform\\">\r\n<table style=\\"font-size: 11px;\\">\r\n  <tr>\r\n    <td>{TXT_CALENDAR_FROM}:</td>\r\n    <td style=\\"padding-left: 5px;\\"><input type=\\"text\\" name=\\"startDate\\" id=\\"DPC_edit1_YYYY-MM-DD\\" value=\\"{CALENDAR_DATEPICKER_START}\\" style=\\"width:8em; padding: 2px;\\" /></td>\r\n    <td style=\\"padding-left: 15px;\\">{TXT_CALENDAR_KEYWORD}:</td>\r\n    <td style=\\"padding-left: 5px;\\"><input type=\\"text\\" name=\\"keyword\\" style=\\"padding: 2px;\\" value=\\"{CALENDAR_SEARCHED_KEYWORD}\\" /></td>\r\n  </tr>\r\n  <tr>\r\n    <td>{TXT_CALENDAR_TILL}:</td>\r\n    <td style=\\"padding-left: 5px;\\"><input type=\\"text\\" name=\\"endDate\\" id=\\"DPC_edit2_YYYY-MM-DD\\" value=\\"{CALENDAR_DATEPICKER_END}\\" style=\\"width:8em; padding: 2px;\\" /></td>\r\n    <td style=\\"padding-left: 15px;\\">&nbsp;</td>\r\n    <td style=\\"padding-left: 5px;\\"><input type=\\"submit\\" value=\\"{TXT_CALENDAR_SEARCH}\\" /></td>\r\n  </tr>\r\n</table>\r\n  </form>\r\n\r\n<div style=\\"width: 100%; margin-top: 15px;\\">\r\n<table class=\\"calendar_eventlist\\" style=\\"width: 100%;\\" cellspacing=\\"0\\" cellpadding=\\"0\\">\r\n	<tr>\r\n		<th style=\\"text-align: left;\\">{TXT_CALENDAR_STARTDATE}</th>\r\n		<th style=\\"text-align: left;\\">{TXT_CALENDAR_TITLE}</th>\r\n		<th style=\\"text-align: left;\\">{TXT_CALENDAR_PLACE}</th>\r\n	</tr>\r\n	<!-- BEGIN event -->\r\n	<tr>\r\n		<td style=\\"width: 80px;\\">{CALENDAR_STARTDATE} {CALENDAR_STARTTIME}</td>\r\n		<td><a href=\\"?section=calendar&amp;cmd=event&amp;id={CALENDAR_ID}\\">{CALENDAR_TITLE}</a></td>\r\n		<td style=\\"width: 80px;\\">{CALENDAR_PLACE}</td>\r\n	</tr>\r\n	<!-- END event -->\r\n</table>\r\n</div>\r\n\r\n<!-- END calendar_standard_view.html -->	', 'Standard Ansicht', '', 'n', 0, 'on', 'system', 1, '1');
INSERT INTO `contrexx_module_repository` VALUES (525, 4, '{NEWSLETTER}', 'Newsletter Profil bearbeiten', 'profile', 'y', 521, 'off', 'system', 2, '1');
INSERT INTO `contrexx_module_repository` VALUES (524, 4, '{NEWSLETTER}', 'Newsletter bestätigen', 'confirm', 'y', 521, 'off', 'system', 1, '1');
INSERT INTO `contrexx_module_repository` VALUES (523, 4, '{NEWSLETTER}', 'Newsletter abmelden', 'unsubscribe', 'y', 521, 'off', 'system', 1, '1');
INSERT INTO `contrexx_module_repository` VALUES (522, 4, '{NEWSLETTER}', 'Newsletter abonnieren', 'subscribe', 'y', 521, 'off', 'system', 1, '1');
INSERT INTO `contrexx_module_repository` VALUES (521, 4, '{NEWSLETTER}', 'Newsletter', '', 'y', 0, 'on', 'system', 1, '1');
INSERT INTO `contrexx_module_repository` VALUES (325, 17, '<br />\r\n<form name=\\"VotingForm\\" action=\\"?section=voting\\" method=\\"post\\">\r\n<table width=\\"100%\\" border=\\"0\\">\r\n<tr> \r\n<td><b>{VOTING_TITLE}</b>{VOTING_DATE}</td>\r\n</tr>\r\n<tr> \r\n<td class=\\"desc\\"> {VOTING_RESULTS_TEXT}<br />\r\n{VOTING_RESULTS_TOTAL_VOTES}{TXT_SUBMIT} </td>\r\n</tr>\r\n</table>\r\n</form>\r\n<table width=\\"100%\\" border=\\"0\\">\r\n<tr> \r\n<td valign=\\"top\\" colspan=\\"2\\" class=\\"title\\"><b>{VOTING_OLDER_TITLE}</b></td>\r\n</tr>\r\n<tr> \r\n<td valign=\\"top\\" nowrap=\\"nowrap\\"><b>{TXT_DATE}</b></td>\r\n<td valign=\\"top\\" nowrap=\\"nowrap\\"><b>{TXT_TITLE}</b></td>\r\n</tr>\r\n<!-- BEGIN votingRow -->\r\n<tr class=\\"{VOTING_LIST_CLASS}\\"> \r\n<td nowrap=\\"nowrap\\">{VOTING_OLDER_DATE}</td>\r\n<td nowrap=\\"nowrap\\">{VOTING_OLDER_TEXT}</td>\r\n</tr>\r\n<!-- END votingRow -->\r\n</table>\r\n<br />{VOTING_PAGING}', 'Voting', '', 'y', 0, 'on', 'system', 111, '1');
INSERT INTO `contrexx_module_repository` VALUES (339, 6, 'Ihre Adresse<br/>\r\nIhre Email Adresse <br/>\r\n<br/>\r\n<form enctype=\\"multipart/form-data\\" method=\\"post\\" action=\\"index.php?section=contact&amp;id=3&amp;cmd=thanks\\" name=\\"ContactForm\\">\r\n    <table width=\\"80%\\" border=\\"0\\">\r\n        <tbody>\r\n            <tr>\r\n                <td><b>Name</b></td>\r\n                <td><input type=\\"text\\" name=\\"Vorname\\" size=\\"35\\" style=\\"width: 80%;\\"/> </td>\r\n            </tr>\r\n            <tr>\r\n                <td>Firmenname</td>\r\n                <td><input type=\\"text\\" name=\\"Name\\" size=\\"35\\" style=\\"width: 80%;\\"/> </td>\r\n            </tr>\r\n            <tr>\r\n                <td>Strasse</td>\r\n                <td><input type=\\"text\\" name=\\"Strasse\\" size=\\"35\\" style=\\"width: 80%;\\"/> </td>\r\n            </tr>\r\n            <tr>\r\n                <td>PLZ</td>\r\n                <td><input type=\\"text\\" name=\\"PLZ\\" size=\\"35\\" style=\\"width: 80%;\\"/> </td>\r\n            </tr>\r\n            <tr>\r\n                <td>Ort</td>\r\n                <td><input type=\\"text\\" name=\\"Ort\\" size=\\"35\\" style=\\"width: 80%;\\"/> </td>\r\n            </tr>\r\n            <tr>\r\n                <td>Land</td>\r\n                <td><input type=\\"text\\" name=\\"Land\\" size=\\"35\\" style=\\"width: 80%;\\"/> </td>\r\n            </tr>\r\n            <tr>\r\n                <td><b>Telefon</b></td>\r\n                <td><input type=\\"text\\" name=\\"Telefon\\" size=\\"35\\" style=\\"width: 80%;\\"/> </td>\r\n            </tr>\r\n            <tr>\r\n                <td>Fax</td>\r\n                <td><input type=\\"text\\" name=\\"Fax\\" size=\\"35\\" style=\\"width: 80%;\\"/> </td>\r\n            </tr>\r\n            <tr>\r\n                <td>E-Mail</td>\r\n                <td><input type=\\"text\\" name=\\"EMail\\" size=\\"35\\" style=\\"width: 80%;\\"/> </td>\r\n            </tr>\r\n            <tr>\r\n                <td>Bemerkungen&nbsp;</td>\r\n                <td><textarea cols=\\"30\\" rows=\\"7\\" name=\\"Bemerkungen\\" style=\\"width: 80%;\\"></textarea></td>\r\n            </tr>\r\n            <tr>\r\n                <td>Datei<br/>\r\n                </td>\r\n                <td>\r\n                <div align=\\"left\\"><input type=\\"file\\" name=\\"file\\" style=\\"width: 80%;\\"/> </div>\r\n                </td>\r\n            </tr>\r\n            <tr>\r\n                <td>&nbsp;</td>\r\n                <td><input type=\\"reset\\" name=\\"Reset\\" value=\\"Löschen\\"/> &nbsp;&nbsp;&nbsp;  <input type=\\"submit\\" name=\\"Submit\\" value=\\"Senden\\"/>  </td>\r\n            </tr>\r\n        </tbody>\r\n    </table>\r\n</form>', 'Kontakt', '', 'n', 0, 'on', 'system', 3, '1');
INSERT INTO `contrexx_module_repository` VALUES (350, 9, '{MEDIA_JAVASCRIPT}\r\n<table id=\\"media\\" cellspacing=\\"0\\" cellpadding=\\"5\\" width=\\"100%\\" border=\\"0\\">\r\n    <tbody>\r\n        <tr class=\\"head\\">\r\n            <td align=\\"center\\" width=\\"16\\"><strong>#</strong></td>\r\n            <td colspan=\\"2\\"><a title=\\"{TXT_MEDIA_SORT}\\" href=\\"{MEDIA_NAME_HREF}\\"><strong>{TXT_MEDIA_FILE_NAME}</strong></a> {MEDIA_NAME_ICON}</td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\"><a title=\\"{TXT_MEDIA_SORT}\\" href=\\"{MEDIA_SIZE_HREF}\\" name=\\"sort_size\\"><strong>{TXT_MEDIA_FILE_SIZE}</strong></a> {MEDIA_SIZE_ICON} </td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\"><a title=\\"{TXT_MEDIA_SORT}\\" href=\\"{MEDIA_TYPE_HREF}\\" name=\\"sort_type\\"><strong>{TXT_MEDIA_FILE_TYPE}</strong></a> {MEDIA_TYPE_ICON} </td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\"><a title=\\"{TXT_MEDIA_SORT}\\" href=\\"{MEDIA_DATE_HREF}\\" name=\\"sort_date\\"><strong>{TXT_MEDIA_FILE_DATE}</strong></a> {MEDIA_DATE_ICON} </td>\r\n        </tr>\r\n        <tr class=\\"row2\\" valign=\\"middle\\">\r\n            <td align=\\"center\\" width=\\"16\\"><img title=\\"base\\" height=\\"16\\" alt=\\"base\\" src=\\"images/modules/media/_base.gif\\" width=\\"16\\"/> </td>\r\n            <td colspan=\\"5\\"><strong><a title=\\"{MEDIA_TREE_NAV_MAIN}\\" href=\\"{MEDIA_TREE_NAV_MAIN_HREF}\\">{MEDIA_TREE_NAV_MAIN}</a></strong> <!-- BEGIN mediaTreeNavigation --><a href=\\"{MEDIA_TREE_NAV_DIR_HREF}\\">&nbsp;{MEDIA_TREE_NAV_DIR} /</a> <!-- END mediaTreeNavigation --></td>\r\n        </tr>\r\n        <!-- BEGIN mediaDirectoryTree -->\r\n        <tr class=\\"{MEDIA_DIR_TREE_ROW}\\" valign=\\"middle\\">\r\n            <td width=\\"16\\">&nbsp;</td>\r\n            <td align=\\"center\\" width=\\"16\\"><img title=\\"icon\\" height=\\"16\\" alt=\\"icon\\" src=\\"{MEDIA_FILE_ICON}\\" width=\\"16\\"/></td>\r\n            <td width=\\"100%\\"><a title=\\"{MEDIA_FILE_NAME}\\" href=\\"{MEDIA_FILE_NAME_HREF}\\">{MEDIA_FILE_NAME}</a></td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\">{MEDIA_FILE_SIZE}&nbsp;</td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\">{MEDIA_FILE_TYPE}&nbsp;</td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\">{MEDIA_FILE_DATE}&nbsp;</td>\r\n        </tr>\r\n        <!-- END mediaDirectoryTree --><!-- BEGIN mediaEmptyDirectory -->\r\n        <tr class=\\"row1\\">\r\n            <td>&nbsp;</td>\r\n            <td colspan=\\"5\\">{TXT_MEDIA_DIR_EMPTY}</td>\r\n        </tr>\r\n        <!-- END mediaEmptyDirectory -->\r\n    </tbody>\r\n</table>', 'Media Archiv #1', '', 'y', 0, 'on', 'system', 1, '2');
INSERT INTO `contrexx_module_repository` VALUES (314, 19, '{DOCSYS_TEXT} <br />\r\nVeröffentlicht am {DOCSYS_DATE} unter dem Titel {DOCSYS_TITLE}\r\n{DOCSYS_AUTHOR} <br />\r\n{DOCSYS_SOURCE}<br />\r\n{DOCSYS_URL} \r\n<br />\r\n{DOCSYS_LASTUPDATE}<br />', 'Documents', 'details', 'y', 313, 'off', 'system', 0, '1');
INSERT INTO `contrexx_module_repository` VALUES (313, 19, '<form name=\\"docSys\\" action=\\"index.php?section=docsys\\" method=\\"post\\">\r\n    <select onchange=\\"javascript:this.form.submit()\\" name=\\"category\\">\r\n    <option value=\\"\\" selected=\\"selected\\">{DOCSYS_NO_CATEGORY}</option>\r\n    {DOCSYS_CAT_MENU}</select>\r\n</form>\r\n<br/>\r\n<table id=\\"docsys\\" cellspacing=\\"0\\" cellpadding=\\"2\\" width=\\"100%\\" border=\\"0\\">\r\n        <tr>\r\n            <td nowrap=\\"nowrap\\" width=\\"5%\\"><b>Datum</b></td>\r\n            <td width=\\"100%\\"><b>Titel</b></td>\r\n            <td nowrap=\\"nowrap\\"><b>Kategorie</b></td>\r\n        </tr>\r\n        <!-- BEGIN row -->\r\n        <tr>\r\n            <td nowrap=\\"nowrap\\">{DOCSYS_DATE}&nbsp;&nbsp;</td>\r\n            <td width=\\"100%\\"><b>{DOCSYS_LINK}</b>&nbsp;&nbsp;{DOCSYS_AUTHOR}</td>\r\n            <td nowrap=\\"nowrap\\">{DOCSYS_CATEGORY}</td>\r\n        </tr>\r\n        <!-- END row -->\r\n</table>\r\n<br/>\r\n{DOCSYS_PAGING}<br/>\r\n<br/>', 'Dokumenten System', '', 'y', 0, 'on', 'system', 5, '1');
INSERT INTO `contrexx_module_repository` VALUES (475, 12, '<table cellspacing=\\"5\\" cellpadding=\\"0\\" width=\\"100%\\" border=\\"0\\" id=\\"cat\\">\r\n<tr>\r\n<td width=\\"33%\\" class=\\"title\\">&raquo;&nbsp;<a href=\\''?section=directory&latest=true\\''>{LATEST}</a></td>\r\n<td width=\\"33%\\" class=\\"title\\">&raquo;&nbsp;<a href=\\''?section=directory&popular=true\\''>{POPULAR}</a></td>\r\n<td width=\\"33%\\" colspan=\\"2\\" class=\\"title\\">&raquo;&nbsp;<a href=\\''?section=directory&cmd=add\\''>{NEW}</a></td>\r\n</tr>\r\n<form action=\\"?section=directory&cmd=search\\" method=\\"get\\">\r\n<tr><td colspan=\\"4\\"><input name=\\"term\\" value=\\"{SEARCH_TERM}\\" size=\\"19\\" maxlength=\\"100\\" /><input type=\\"hidden\\" name=\\"section\\" value=\\"directory\\" size=\\"19\\" maxlength=\\"100\\" /><input type=\\"hidden\\" name=\\"cmd\\" value=\\"search\\" size=\\"19\\" maxlength=\\"100\\" />&nbsp;<input value=\\"{TXT_SEARCH}\\" name=\\"search\\" type=\\"submit\\" /></td>\r\n</tr></form>\r\n</table>\r\n<br />\r\n<table cellspacing=\\"5\\" cellpadding=\\"0\\" width=\\"100%\\" border=\\"0\\" id=\\"cat\\">\r\n<tr>\r\n<td width=\\"94%\\" colspan=\\"3\\" class=\\"title\\">&raquo;&nbsp;<a href=\\"?section=directory\\">{TXT_DIR}</a>{TXT_CATEGORY}<br />{TXT_DESCRIPTION_CAT}</td>\r\n<td width=\\"6%\\" class=\\"title\\" valign=\\"top\\"><div align=\\"right\\">{RSS_CAT}&nbsp;</div></td>\r\n</tr>\r\n</table>\r\n<br />\r\n<table cellspacing=\\"0\\" cellpadding=\\"0\\" width=\\"100%\\" border=\\"0\\" class=\\"feed\\">\r\n<tr>\r\n<th width=\\"50%\\" colspan=\\"2\\">Name&nbsp;</th>\r\n<th width=\\"25%\\" colspan=\\"2\\">Details&nbsp;</th>\r\n<th width=\\"15%\\">Hinzugefügt&nbsp;</th>\r\n<th width=\\"10%\\">Hits&nbsp;</th>\r\n</tr>\r\n<tr>\r\n<td class=\\"spacer\\"><img src=\\"images/content/gif/pixel.gif\\" border=\\"0\\" width=\\"1\\" height=\\"1\\" alt=\\"\\" /></td>\r\n</tr>\r\n<!-- BEGIN showResults -->\r\n<tr>\r\n<th colspan=\\"6\\" class=\\"title\\" valign=\\"top\\"><a href=\\"?section=directory&cmd=detail&id={DETAIL}\\">{NAME}</a>&nbsp;{NEW_FEED}</th>\r\n</tr>\r\n<tr>\r\n<td class=\\"content\\" valign=\\"top\\">{DES}&nbsp;</td>\r\n<td class=\\"content\\" valign=\\"top\\" nowrap>&nbsp;</td>\r\n<td class=\\"content\\" valign=\\"top\\" width=\\"8%\\" nowrap>Typ:&nbsp;<br />Autor:&nbsp;<br />Source:&nbsp;</td>\r\n<td class=\\"content\\" valign=\\"top\\" width=\\"17%\\">{TYP}<br />{AUTHOR}<br />{LINK}</td>\r\n<td class=\\"content\\" valign=\\"top\\" nowrap>{DATE}</td>\r\n<td class=\\"content\\" valign=\\"top\\" nowrap>{HITS}&nbsp;{TXT_HITS}&nbsp;</td>\r\n</tr>\r\n<tr>\r\n<td colspan=\\"6\\"><img src=\\"images/content/gif/pixel.gif\\" border=\\"0\\" width=\\"1\\" height=\\"2\\" alt=\\"\\" /></td>\r\n</tr>\r\n</td>\r\n<!-- END showResults -->\r\n<!-- BEGIN noResult -->\r\n<tr>\r\n<td class=\\"spacer\\"><br />{NO_FEED}</td>\r\n</tr>\r\n<!-- END noResult -->\r\n</table>\r\n<br />\r\n{SEARCH_PAGING}', 'Suche', 'search', 'y', 472, 'off', 'system', 2, '1');
INSERT INTO `contrexx_module_repository` VALUES (476, 12, '{STATUS}\r\n<!-- BEGIN login -->\r\n<form name=\\"addEntry\\" enctype=\\"multipart/form-data\\" method=\\"post\\" action=\\"?section=directory&cmd=add\\" onSubmit=\\"return CheckForm()\\">\r\n  <table border=\\"0\\" cellpadding=\\"3\\" cellspacing=\\"0\\" align=\\"center\\" width=\\"100%\\">\r\n    <tr> \r\n      <td width=\\"150\\">{TXT_USERNAME}:</td>\r\n      <td><input name=\\"username\\"  style=\\"width:220px;\\"></td>\r\n    </tr>\r\n    <tr> \r\n      <td width=\\"150\\">{TXT_PW}:</td>\r\n      <td><input type=\\"password\\" name=\\"password\\"  style=\\"width:220px;\\"></td>\r\n    </tr>\r\n    <tr> \r\n      <td width=\\"150\\">&nbsp;</td>\r\n      <td><input type=\\"submit\\" name=\\"login\\" value=\\"{TXT_LOGIN}\\"></td>\r\n    </tr>\r\n    <tr> \r\n      <td width=\\"150\\">&nbsp;</td>\r\n      <td><a href=\\"?section=directory&cmd=lostpw\\">{TXT_LOST_PW}</a></td>\r\n    </tr>\r\n    <tr> \r\n      <td width=\\"150\\">&nbsp;</td>\r\n      <td><br /></td>\r\n    </tr>\r\n    <tr> \r\n      <td width=\\"150\\">&nbsp;</td>\r\n      <td>{TXT_NOLOGIN}</td>\r\n    </tr>\r\n</table>\r\n</form>\r\n<br />\r\n<!-- END login -->\r\n<!-- BEGIN newFeed -->\r\n<script language=\\"JavaScript\\">\r\nfunction CheckForm() {\r\n  with( document.addEntry ) {\r\n    if (new_description.value == \\"\\" || new_name.value == \\"\\") \r\n    {\r\n        alert (\\"{TXT_FIELDS_REQUIRED}\\");       \r\n        return false;\r\n    }   	\r\n    return true;\r\n  }\r\n}\r\n\r\nfunction hideRow(type)\r\n{ \r\n	if (type==\\''file\\'')\r\n	{\r\n		document.getElementById(\\''hiddenfile\\'').style.display = \\''block\\'';\r\n		document.getElementById(\\''hiddenlink\\'').style.display = \\''none\\'';\r\n		document.getElementById(\\''hiddenrss\\'').style.display = \\''none\\'';\r\n		document.addEntry.linkname.value = \\''http://\\'';\r\n		document.addEntry.rssname.value = \\''http://\\'';\r\n		return true;\r\n	}\r\n	else if (type==\\''rss\\'')\r\n	{\r\n		document.getElementById(\\''hiddenlink\\'').style.display = \\''none\\'';\r\n		document.getElementById(\\''hiddenfile\\'').style.display = \\''none\\'';\r\n		document.getElementById(\\''hiddenrss\\'').style.display = \\''block\\'';\r\n		document.addEntry.linkname.value = \\''http://\\'';\r\n		return true;\r\n	}\r\n	else\r\n	{\r\n		document.getElementById(\\''hiddenlink\\'').style.display = \\''block\\'';\r\n		document.getElementById(\\''hiddenfile\\'').style.display = \\''none\\'';\r\n		document.getElementById(\\''hiddenrss\\'').style.display = \\''none\\'';\r\n		document.addEntry.rssname.value = \\''http://\\'';\r\n		return true;\r\n	}\r\n}\r\n</script>\r\n<form name=\\"addEntry\\" enctype=\\"multipart/form-data\\" method=\\"post\\" action=\\"?section=directory&cmd=add\\" onSubmit=\\"return CheckForm()\\">\r\n  <table border=\\"0\\" cellpadding=\\"3\\" cellspacing=\\"0\\" align=\\"center\\" width=\\"100%\\">\r\n    <tr class=\\"row1\\"> \r\n      <td width=\\"150\\">{TXT_FILETYPE}:</td>\r\n      <td> \r\n         <input type=\\"radio\\" name=\\"type\\" value=\\"rss\\" onClick=\\"javascript:hideRow(\\''rss\\'')\\" checked>\r\n        {TXT_RSSLINK}\r\n        <input type=\\"radio\\" name=\\"type\\" value=\\"file\\" onClick=\\"javascript:hideRow(\\''file\\'')\\">\r\n        {TXT_FILE} \r\n        <input type=\\"radio\\" name=\\"type\\" value=\\"link\\" onClick=\\"javascript:hideRow(\\''link\\'')\\">\r\n        {TXT_LINK}</td>\r\n    </tr>\r\n    <tr class=\\"row1\\"> \r\n      <td width=\\"150\\">{TXT_CATEGORY}:</td>\r\n      <td>{CATEGORY}</td>\r\n    </tr>\r\n  </table>  \r\n  <div id=\\"hiddenlink\\" style=\\"display:none;\\">\r\n    <table border=\\"0\\" cellpadding=\\"3\\" cellspacing=\\"0\\" align=\\"center\\" width=\\"100%\\">\r\n	   <tr class=\\"row1\\" > \r\n	      <td width=\\"150\\" height=\\"26\\">{TXT_LINK}:<font color=\\"red\\">*</font></td>\r\n	      <td><input name=\\"linkname\\" style=\\"width:300px;\\" value=\\"http://\\"></td>\r\n	    </tr>\r\n	</table>\r\n  </div>\r\n  <div id=\\"hiddenrss\\">\r\n    <table border=\\"0\\" cellpadding=\\"3\\" cellspacing=\\"0\\" align=\\"center\\" width=\\"100%\\">\r\n	   <tr class=\\"row1\\" > \r\n	      <td width=\\"150\\" height=\\"26\\">{TXT_RSSLINK}:</td>\r\n	      <td><input name=\\"rssname\\"  style=\\"width:300px;\\" value=\\"http://\\"></td>\r\n	    </tr>\r\n	</table>\r\n  </div>\r\n  <div id=\\"hiddenfile\\" style=\\"display:none;\\">\r\n   <table border=\\"0\\" cellpadding=\\"3\\" cellspacing=\\"0\\" align=\\"center\\" width=\\"100%\\">  \r\n    <tr class=\\"row1\\" > \r\n      <td width=\\"150\\" height=\\"26\\">{TXT_FILE}:<font color=\\"red\\">*</font></td>\r\n      <td> \r\n        <input type=\\"file\\" name=\\"fileName\\" value=\\"\\" size=\\"37\\" style=\\"width:300px;\\">\r\n        </td>\r\n    </tr>\r\n   </table>\r\n  </div>\r\n<table border=\\"0\\" cellpadding=\\"3\\" cellspacing=\\"0\\" align=\\"center\\" width=\\"100%\\"  class=\\"adminlist\\">\r\n   	<!-- BEGIN inputfieldsOutput -->\r\n    <tr class=\\"{FIELD_ROW}\\"> \r\n      <td width=\\"150\\" valign=\\"top\\">{FIELD_NAME}:</td>\r\n      <td >{FIELD_VALUE}</td>\r\n    </tr>\r\n    <!-- END inputfieldsOutput -->\r\n   <tr class=\\"row1\\"> \r\n      <td width=\\"150\\">&nbsp;</td>\r\n      <td><input type=\\"submit\\" name=\\"new_submit\\" value=\\"{TXT_ADD}\\">&nbsp;<font color=\\"red\\">*</font> = {TXT_REQUIRED_FIELDS}</td>\r\n    </tr>\r\n</table>\r\n<input type=\\"hidden\\" name=\\"inputValue[addedby]\\" value=\\"{ADDED_BY_ID}\\" size=\\"37\\" style=\\"width:300px;\\">\r\n</form>\r\n<br />\r\n<!-- END newFeed -->', 'neuer Eintrag', 'add', 'y', 472, 'on', 'system', 3, '1');
INSERT INTO `contrexx_module_repository` VALUES (477, 12, '<!-- START module_directory_user_add.html -->\r\n<script language=\\"JavaScript\\" type=\\"text/javascript\\">\r\nfunction checkForm()\r\n{\r\n	if (document.addForm.firstname.value == \\"\\" || document.addForm.lastname.value == \\"\\" || document.addForm.email.value == \\"\\" || document.addForm.username.value == \\"\\" || document.addForm.password.value == \\"\\") \r\n		{\r\n		        alert (\\"{TXT_FIELDS_REQUIRED}\\");       \r\n		        return false;\r\n		}else{ 	\r\n		    return true;\r\n		}\r\n\r\n}\r\n\r\nfunction checkPW()\r\n{\r\n	if (document.addForm.password.value == \\"\\") \r\n		{\r\n		        alert (\\"{TXT_FIELDS_REQUIRED}\\");       \r\n		        return false;\r\n		}else{ 	\r\n		    return true;\r\n		}\r\n\r\n}\r\n\r\n</script>\r\n{STATUS}\r\n<!-- BEGIN activate -->\r\n<table width=\\"100%\\" cellspacing=\\"0\\" cellpadding=\\"3\\" border=\\"0\\" align=\\"top\\" class=\\"adminlist\\"> \r\n    <tr class=\\"row1\\"> \r\n      <td nowrap=\\"nowrap\\">{TXT_SUCCESSFULL_ACTIVATE}</td>\r\n    </tr>\r\n</table>\r\n<!-- END activate -->\r\n<!-- BEGIN pw_updated -->\r\n<table width=\\"100%\\" cellspacing=\\"0\\" cellpadding=\\"3\\" border=\\"0\\" align=\\"top\\" class=\\"adminlist\\"> \r\n    <tr class=\\"row1\\"> \r\n      <td nowrap=\\"nowrap\\">{TXT_DIR_PW_SUCCESSFULL_UPDATED}</td>\r\n    </tr>\r\n    <tr class=\\"row1\\"> \r\n      <td colspan=\\"2\\" nowrap=\\"nowrap\\"><br /></td>\r\n    </tr>\r\n    <tr class=\\"row1\\"> \r\n      <td nowrap=\\"nowrap\\"><a href=\\"?section=directory\\">{TXT_BACK}</a></td>\r\n    </tr>\r\n</table>\r\n<!-- END pw_updated -->\r\n<!-- BEGIN restore_pw -->\r\n<form name=\\"addForm\\" method=\\"post\\" action=\\"?section=directory&cmd=reg\\" onsubmit=\\"return checkPW()\\">\r\n  <table width=\\"100%\\" cellspacing=\\"0\\" cellpadding=\\"3\\" border=\\"0\\" align=\\"top\\" class=\\"adminlist\\"> \r\n     <tr class=\\"row1\\"> \r\n      <td colspan=\\"2\\" nowrap=\\"nowrap\\">{TXT_SUCCESSFULL_RESETE}</td>\r\n    </tr>\r\n    <tr class=\\"row1\\"> \r\n      <td colspan=\\"2\\" nowrap=\\"nowrap\\"><br /></td>\r\n    </tr>\r\n     <tr class=\\"row1\\"> \r\n      <td nowrap=\\"nowrap\\" width=\\"150\\">{TXT_USERNAME}: <font color=\\"red\\"></font><input type=\\"hidden\\" name=\\"userid\\" style=\\"width: 20px;\\" maxlength=\\"255\\" value=\\"{USER_ID}\\" /></td>\r\n      <td nowrap=\\"nowrap\\"><input type=\\"text\\" name=\\"username_fake\\" style=\\"width: 220px;\\" maxlength=\\"150\\" value=\\"{USER_NAME}\\" disabled /></td>\r\n    </tr> \r\n     <tr class=\\"row1\\"> \r\n      <td nowrap=\\"nowrap\\" width=\\"150\\">{TXT_PASSWORD}: <font color=\\"red\\">*</font></td>\r\n      <td nowrap=\\"nowrap\\"><input type=\\"password\\" name=\\"password\\" style=\\"width: 220px;\\" maxlength=\\"150\\" value=\\"\\" /></td>\r\n    </tr>  \r\n    <tr class=\\"row1\\"> \r\n      <td nowrap=\\"nowrap\\" width=\\"150\\">&nbsp;</td>\r\n      <td><input type=\\"submit\\" value=\\"{TXT_REG}\\" name=\\"restore_submit\\" />&nbsp;<font color=\\"red\\">*</font><b></b> = {TXT_REQUIRED_FIELDS}</td>\r\n    </tr>\r\n    </table>\r\n</form>\r\n<!-- END restore_pw -->\r\n<!-- BEGIN registration -->\r\n<form name=\\"addForm\\" method=\\"post\\" action=\\"?section=directory&cmd=reg\\" onsubmit=\\"return checkForm()\\">\r\n  <table width=\\"100%\\" cellspacing=\\"0\\" cellpadding=\\"3\\" border=\\"0\\" align=\\"top\\" class=\\"adminlist\\"> \r\n    <tr class=\\"row1\\"> \r\n      <td nowrap=\\"nowrap\\" width=\\"150\\">{TXT_USERNAME}: <font color=\\"red\\">*</font><b></b></td>\r\n      <td nowrap=\\"nowrap\\"><input type=\\"text\\" name=\\"username\\" style=\\"width: 220px;\\" maxlength=\\"150\\" value=\\"{USERNAME}\\" /></td>\r\n    </tr>\r\n    <tr class=\\"row1\\"> \r\n      <td nowrap=\\"nowrap\\" width=\\"150\\">{TXT_PASSWORD}: <font color=\\"red\\">*</font><b></b></td>\r\n      <td nowrap=\\"nowrap\\"><input type=\\"password\\" name=\\"password\\" style=\\"width: 220px;\\" maxlength=\\"150\\" value=\\"\\" /></td>\r\n    </tr>  \r\n    <tr class=\\"row1\\"> \r\n      <td nowrap=\\"nowrap\\" width=\\"150\\">{TXT_FIRST_NAME}: <font color=\\"red\\">*</font><b></b></td>\r\n      <td nowrap=\\"nowrap\\"><input type=\\"text\\" name=\\"firstname\\" style=\\"width: 220px;\\" maxlength=\\"150\\" value=\\"{FIRSTNAME}\\" /></td>\r\n    </tr>\r\n    <tr class=\\"row1\\"> \r\n      <td nowrap=\\"nowrap\\" width=\\"150\\">{TXT_LAST_NAME}: <font color=\\"red\\">*</font></td>\r\n      <td nowrap=\\"nowrap\\"><input type=\\"text\\" name=\\"lastname\\" style=\\"width: 220px;\\" maxlength=\\"150\\" value=\\"{LASTNAME}\\" /></td>\r\n    </tr>\r\n    <tr class=\\"row1\\"> \r\n      <td nowrap=\\"nowrap\\" width=\\"150\\">{TXT_EMAIL}: <font color=\\"red\\">*</font></td>\r\n      <td nowrap=\\"nowrap\\"><input type=\\"text\\" name=\\"email\\" style=\\"width: 220px;\\" maxlength=\\"255\\" value=\\"{EMAIL}\\" />\r\n      </td>\r\n    </tr>\r\n    <tr class=\\"row1\\"> \r\n      <td nowrap=\\"nowrap\\" width=\\"150\\">&nbsp;</td>\r\n      <td><input type=\\"submit\\" value=\\"{TXT_REG}\\" name=\\"add_submit\\" />&nbsp;<font color=\\"red\\">*</font><b></b> = {TXT_REQUIRED_FIELDS}</td>\r\n    </tr>\r\n    </table>\r\n</form>\r\n<!-- END registration -->\r\n<br />\r\n<!-- END module_directory_user_add.html -->', 'Registrieren', 'reg', 'y', 472, 'on', 'system', 4, '1');
INSERT INTO `contrexx_module_repository` VALUES (473, 12, '<!-- BEGIN restore_password -->\r\n<form name=\\"lostPassword\\" method=\\"post\\" action=\\"?section=directory&cmd=lostpw\\">\r\n  <table border=\\"0\\" cellpadding=\\"3\\" cellspacing=\\"0\\" align=\\"center\\" width=\\"100%\\">\r\n    <tr> \r\n      <td colspan=\\"2\\">{TXT_LOST_PW}</td>\r\n    </tr>\r\n    <tr> \r\n      <td width=\\"150\\">&nbsp;</td>\r\n      <td width=\\"*\\"><br /></td>\r\n    </tr>\r\n    <tr> \r\n      <td width=\\"150\\">{TXT_EMAIL}:</td>\r\n\r\n      <td><input name=\\"email\\"  style=\\"width:220px;\\"></td>\r\n    </tr>\r\n    <tr> \r\n      <td width=\\"150\\">&nbsp;</td>\r\n      <td><input type=\\"submit\\" name=\\"login\\" value=\\"{TXT_RESET}\\"></td>\r\n    </tr>\r\n    <tr> \r\n      <td width=\\"150\\">&nbsp;</td>\r\n      <td width=\\"*\\"><br /></td>\r\n    </tr>\r\n    <tr> \r\n      <td width=\\"150\\">&nbsp;</td>\r\n      <td width=\\"*\\">{TXT_STATUS}</td>\r\n    </tr>\r\n</table>\r\n</form>\r\n<!-- END restore_password -->\r\n<!-- BEGIN restore_password_in_progress -->\r\n<table border=\\"0\\" cellpadding=\\"3\\" cellspacing=\\"0\\" align=\\"center\\" width=\\"100%\\">\r\n    <tr> \r\n      <td>{TXT_STATUS}</td>\r\n    </tr>\r\n    <tr> \r\n      <td><br /></td>\r\n    </tr>\r\n    <tr> \r\n      <td><a href=\\"?section=directory\\">{TXT_BACK}</a></td>\r\n    </tr>\r\n</table>\r\n<!-- END restore_password_in_progress -->\r\n<br/>', 'Passwort vergessen', 'lostpw', 'y', 472, 'off', 'system', 0, '1');
INSERT INTO `contrexx_module_repository` VALUES (474, 12, '<table cellspacing=\\"5\\" cellpadding=\\"0\\" width=\\"100%\\" border=\\"0\\" id=\\"cat\\">\r\n<tr>\r\n<td width=\\"33%\\" class=\\"title\\">&raquo;&nbsp;<a href=\\''?section=directory&latest=true\\''>{LATEST}</a></td>\r\n<td width=\\"33%\\" class=\\"title\\">&raquo;&nbsp;<a href=\\''?section=directory&popular=true\\''>{POPULAR}</a></td>\r\n<td width=\\"33%\\" colspan=\\"2\\" class=\\"title\\">&raquo;&nbsp;<a href=\\''?section=directory&cmd=add\\''>{NEW}</a></td>\r\n</tr>\r\n<form action=\\"?section=directory&cmd=search\\" method=\\"get\\">\r\n<tr><td colspan=\\"4\\"><input name=\\"term\\" value=\\"\\" size=\\"19\\" maxlength=\\"100\\" /><input type=\\"hidden\\" name=\\"section\\" value=\\"directory\\" size=\\"19\\" maxlength=\\"100\\" /><input type=\\"hidden\\" name=\\"cmd\\" value=\\"search\\" size=\\"19\\" maxlength=\\"100\\" />&nbsp;<input value=\\"{TXT_SEARCH}\\" name=\\"search\\" type=\\"submit\\" /></td>\r\n</tr></form>\r\n</table>\r\n<br />\r\n<table cellspacing=\\"5\\" cellpadding=\\"0\\" width=\\"100%\\" border=\\"0\\" id=\\"cat\\">\r\n<tr>\r\n<td width=\\"94%\\" colspan=\\"3\\" class=\\"title\\">&raquo;&nbsp;<a href=\\"?section=directory\\">{TXT_DIR}</a>{TXT_CATEGORY}<br />{TXT_DESCRIPTION_CAT}</td>\r\n<td width=\\"6%\\" class=\\"title\\" valign=\\"top\\"><div align=\\"right\\">{RSS_CAT}</div></td>\r\n</tr>\r\n</table>\r\n<table cellspacing=\\"0\\" cellpadding=\\"0\\" width=\\"100%\\" border=\\"0\\" id=\\"rss\\">\r\n<tr>\r\n<td><br /><br /></td>\r\n</tr>\r\n<!-- BEGIN showFeed -->\r\n<tr>\r\n<td>\r\n<table cellspacing=\\''0\\'' cellpadding=\\''1\\'' width=\\''100%\\'' border=\\''0\\'' class=\\"feed\\">\r\n<tr>\r\n<th colspan=\\''2\\''><div align=\\''left\\''>{TXT_TITLE}&nbsp;{NEW_FEED}</div></th>\r\n</tr>\r\n<tr>\r\n<td colspan=\\''2\\''>{TXT_DESCRIPTION}</td>\r\n</tr>\r\n<tr>\r\n<td valign=\\''top\\'' width=\\''17%\\''><div align=\\''left\\''><b>{TXT_EXTENSION_NAME}</b></div></td>\r\n<td valign=\\''top\\'' width=\\''83%\\''>{TXT_EXTENSION_VALUE}</td>\r\n</tr>\r\n{TXT_EXTENSION_SIZE}\r\n{TXT_EXTENSION_MD5}\r\n<!-- BEGIN fieldsFeed --><tr>\r\n<td valign=\\''top\\'' width=\\''17%\\''><div align=\\''left\\''><b>{TXT_NAME}<b></div></td>\r\n<td valign=\\''top\\'' width=\\''83%\\''>{TXT_VALUE}</td>\r\n<tr><!-- END fieldsFeed -->\r\n<tr>\r\n<td class=\\''footer\\''>{TXT_DATE}</td>\r\n<td class=\\''footer\\'' valign=\\''top\\''><div align=\\''right\\''>{TXT_HITS}&nbsp;Hits&nbsp;</div></td>\r\n</tr>\r\n</table>\r\n<br />\r\n</td>\r\n</tr>\r\n<!-- END showFeed -->\r\n<!-- BEGIN noFeeds -->\r\n<tr>\r\n<td><br />{NO_FEED}</td>\r\n</tr>\r\n<!-- END noFeeds -->\r\n</table>', 'Feed anzeigen', 'detail', 'y', 472, 'off', 'system', 1, '1');
INSERT INTO `contrexx_module_repository` VALUES (305, 15, '<p>Herzlich Willkommen auf der deutschsprachigen <strong>Contrexx&reg; CMS Demo Website</strong>! </p>\r\n<p>Contrexx&reg; erm&ouml;glicht Ihnen individuelles, eigenh&auml;ndiges und einfaches Aktualisieren Ihrer Website, Ihres Intranets oder Extranets. Weder HTML- noch Programmierungskenntnisse sind notwendig, um Ihr Projekt auf dem neusten Stand zu halten. Bearbeiten Sie Ihren Internet-Auftritt als w&uuml;rden Sie mit Word arbeiten. Viele integrierte Module wie etwa (Newsletter, News, EShop usw.) helfen Ihnen, das Maximum aus Ihrem Internet-Auftritt heraus zu holen.<br/>\r\n<br/>\r\n<strong>Unterhalten Sie Ihren Internetauftritt selbst.&nbsp; Schnell. Einfach. Sicher. <br/>\r\nOhne Software. Ohne Programmierkenntnisse.</strong></p>\r\n<table cellspacing=\\"1\\" cellpadding=\\"1\\" width=\\"100%\\" border=\\"0\\">\r\n    <tbody>\r\n        <tr>\r\n            <td onmouseover=\\"this.style.backgroundColor=\\''#FFD966\\'';\\" style=\\"TEXT-ALIGN: justify\\" onmouseout=\\"this.style.backgroundColor=\\''\\'';\\">&nbsp;Die wichtigsten Vorteile, welche Contrexx&reg; CMS&nbsp;auszeichnen:<br/>\r\n            &gt; Einfache und intuitive Bedienung<br/>\r\n            &gt; Modularer und flexibler Aufbau<br/>\r\n            &gt; Grosse Anzahl an optionalen Modulen<br/>\r\n            &gt; Komfortable Trennung von Design und Inhalt<br/>\r\n            &gt; Browserbasierende Administrationsanwendung<br/>\r\n            &gt; Dezentrale Pflege durch mehrere Benutzer<br/>\r\n            &gt; Bew&auml;hrte und zukunftssichere L&ouml;sung<br/>\r\n            &gt; Geringe Anschaffungs- und Betriebskosten</td>\r\n        </tr>\r\n    </tbody>\r\n</table>\r\n<p>Weitere Informationen zu diesem <a href=\\"http://www.contrexx.com/\\" target=\\"_blank\\">innovativen Web Content Management System</a>.</p>', 'Willkommen', '', 'n', 0, 'on', 'system', 0, '1');
INSERT INTO `contrexx_module_repository` VALUES (399, 16, '<!-- BEGIN shopProductRow1 -->\r\n<table border=\\"0\\" width=\\"100%\\">\r\n<tr valign=\\"top\\"> \r\n<td border=\\"0\\">\r\n<b>{SHOP_PRODUCT_TITLE}</b>\r\n<br>\r\n<a href=\\"{SHOP_PRODUCT_DETAILLINK}\\"><img src=\\"{SHOP_PRODUCT_THUMBNAIL}\\" border=\\"0\\" alt=\\"{SHOP_PRODUCT_TITLE}\\" /></a>\r\n<br />\r\n     {TXT_PRICE_NOW} {SHOP_PRODUCT_DISCOUNTPRICE} {SHOP_PRODUCT_DISCOUNTPRICE_UNIT} {TXT_INSTEAD_OF}\r\n      {SHOP_PRODUCT_PRICE} {SHOP_PRODUCT_PRICE_UNIT} <br>\r\n</td>\r\n<!-- BEGIN shopProductRow2 -->\r\n<td border=\\"0\\" width=\\"50%\\"><b>{SHOP_PRODUCT_TITLE}</b>\r\n<br>\r\n<a href=\\"{SHOP_PRODUCT_DETAILLINK}\\"><img src=\\"{SHOP_PRODUCT_THUMBNAIL}\\" border=\\"0\\" alt=\\"{SHOP_PRODUCT_TITLE}\\"></a>\r\n<br>\r\n      {TXT_PRICE_NOW} {SHOP_PRODUCT_DISCOUNTPRICE} {SHOP_PRODUCT_DISCOUNTPRICE_UNIT} {TXT_INSTEAD_OF} {SHOP_PRODUCT_PRICE} \r\n      {SHOP_PRODUCT_PRICE_UNIT} \r\n</td>\r\n<!-- END shopProductRow2 -->\r\n</tr>\r\n</table>\r\n<!-- END shopProductRow1 -->', 'Sonderangebote', 'discounts', 'y', 398, 'on', 'system', 1, '1');
INSERT INTO `contrexx_module_repository` VALUES (398, 16, '{SHOP_JAVASCRIPT_CODE} \r\n{SHOP_MENU}<br />\r\n{SHOP_CART_INFO}<br />\r\n{SHOP_PRODUCT_PAGING}\r\n<table width=\\"100%\\" cellspacing=\\"4\\" cellpadding=\\"0\\" border=\\"0\\">\r\n<tr> \r\n<td width=\\"100%\\" height=\\"20\\" style=\\"background-image: url(images/modules/shop/dotted_line.gif);\\"><img width=\\"1\\" height=\\"20\\" border=\\"0\\" alt=\\"\\" src=\\"images/modules/shop/pixel.gif\\" /></td>		\r\n</tr>\r\n</table>\r\n<!-- BEGIN shopProductRow -->\r\n<form method=\\"post\\" action=\\"index.php?section=shop&amp;cmd=cart\\" name=\\"{SHOP_PRODUCT_FORM_NAME}\\" id=\\"{SHOP_PRODUCT_FORM_NAME}\\">\r\n<input type=\\"hidden\\" value=\\"{SHOP_PRODUCT_ID}\\" name=\\"productId\\" />\r\n<table width=\\"100%\\" cellspacing=\\"3\\" cellpadding=\\"1\\" border=\\"0\\">\r\n<tr> \r\n<td colspan=\\"4\\"><strong>{SHOP_PRODUCT_TITLE}</strong></td>\r\n</tr>\r\n<tr>\r\n<td width=\\"25%\\" style=\\"vertical-align:top;\\"><a href=\\"{SHOP_PRODUCT_THUMBNAIL_LINK}\\"><img border=\\"0\\" alt=\\"{TXT_SEE_LARGE_PICTURE}\\" src=\\"{SHOP_PRODUCT_THUMBNAIL}\\" /></a></td>\r\n<td width=\\"75%\\" colspan=\\"3\\" valign=\\"top\\"><small><i><strong>{TXT_PRODUCT_ID}:</strong> {SHOP_PRODUCT_ID}</i></small> <br />\r\n{SHOP_PRODUCT_DESCRIPTION}<br />\r\n<br />\r\n<!-- BEGIN shopProductOptionsRow -->\r\n<table width=\\"100%\\" cellspacing=\\"0\\" cellpadding=\\"0\\" border=\\"0\\">\r\n<tr>\r\n<td>\r\n<strong>{SHOP_PRODUCT_OPTIONS_TITLE}</strong><br /><br />\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\n<div id=\\"product_options_layer{SHOP_PRODUCT_ID}\\" style=\\"display:none;\\">\r\n<table width=\\"100%\\" cellspacing=\\"0\\" cellpadding=\\"0\\" border=\\"0\\">\r\n<!-- BEGIN shopProductOptionsValuesRow -->\r\n<tr>\r\n<td width=\\"150\\" style=\\"vertical-align:top;\\">\r\n{SHOP_PRODUCT_OPTIONS_NAME}:\r\n</td>\r\n<td>{SHOP_PRODCUT_OPTION}</td>\r\n</tr>\r\n<!-- END shopProductOptionsValuesRow -->\r\n</table>\r\n</div>\r\n</td>\r\n</tr>\r\n</table>\r\n<!-- END shopProductOptionsRow -->\r\n<br />{SHOP_PRODUCT_STOCK}<br />{SHOP_MANUFACTURER_LINK}</td>\r\n</tr>\r\n<tr>     \r\n<td colspan=\\"4\\">{SHOP_PRODUCT_DETAILLINK}</td>\r\n</tr>\r\n<tr>   \r\n<td colspan=\\"3\\"><b><font color=\\"red\\">{SHOP_PRODUCT_DISCOUNTPRICE} {SHOP_PRODUCT_DISCOUNTPRICE_UNIT} </font> {SHOP_PRODUCT_PRICE} {SHOP_PRODUCT_PRICE_UNIT} </b>\r\n</td>   \r\n<td>\r\n<div align=\\"right\\"><input type=\\"submit\\" value=\\"{TXT_ADD_TO_CARD}\\" name=\\"{SHOP_PRODUCT_SUBMIT_NAME}\\" onclick=\\"{SHOP_PRODUCT_SUBMIT_FUNCTION}\\" /></div></td>\r\n</tr>\r\n<tr>   \r\n<td height=\\"20\\" style=\\"background-image: url(images/modules/shop/dotted_line.gif);\\" colspan=\\"4\\"><img width=\\"1\\" height=\\"20\\" border=\\"0\\" alt=\\"\\" src=\\"images/modules/shop/pixel.gif\\" /></td>\r\n</tr>	\r\n</table>\r\n</form>\r\n<!-- END shopProductRow -->\r\n{SHOP_PRODUCT_PAGING}\r\n', 'Online Shop', '', 'y', 0, 'on', 'system', 5, '1');
INSERT INTO `contrexx_module_repository` VALUES (340, 6, 'Formulardaten erhalten', 'Formulardaten erhalten', 'thanks', 'y', 339, 'off', 'daeppen', 0, '1');
INSERT INTO `contrexx_module_repository` VALUES (352, 25, '{MEDIA_JAVASCRIPT}\r\n<table id=\\"media\\" cellspacing=\\"0\\" cellpadding=\\"5\\" width=\\"100%\\" border=\\"0\\">\r\n    <tbody>\r\n        <tr class=\\"head\\">\r\n            <td align=\\"center\\" width=\\"16\\"><strong>#</strong></td>\r\n            <td colspan=\\"2\\"><a title=\\"{TXT_MEDIA_SORT}\\" href=\\"{MEDIA_NAME_HREF}\\"><strong>{TXT_MEDIA_FILE_NAME}</strong></a> {MEDIA_NAME_ICON}</td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\"><a title=\\"{TXT_MEDIA_SORT}\\" href=\\"{MEDIA_SIZE_HREF}\\" name=\\"sort_size\\"><strong>{TXT_MEDIA_FILE_SIZE}</strong></a> {MEDIA_SIZE_ICON} </td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\"><a title=\\"{TXT_MEDIA_SORT}\\" href=\\"{MEDIA_TYPE_HREF}\\" name=\\"sort_type\\"><strong>{TXT_MEDIA_FILE_TYPE}</strong></a> {MEDIA_TYPE_ICON} </td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\"><a title=\\"{TXT_MEDIA_SORT}\\" href=\\"{MEDIA_DATE_HREF}\\" name=\\"sort_date\\"><strong>{TXT_MEDIA_FILE_DATE}</strong></a> {MEDIA_DATE_ICON} </td>\r\n        </tr>\r\n        <tr class=\\"row2\\" valign=\\"middle\\">\r\n            <td align=\\"center\\" width=\\"16\\"><img title=\\"base\\" height=\\"16\\" alt=\\"base\\" src=\\"images/modules/media/_base.gif\\" width=\\"16\\"/> </td>\r\n            <td colspan=\\"5\\"><strong><a title=\\"{MEDIA_TREE_NAV_MAIN}\\" href=\\"{MEDIA_TREE_NAV_MAIN_HREF}\\">{MEDIA_TREE_NAV_MAIN}</a></strong> <!-- BEGIN mediaTreeNavigation --><a href=\\"{MEDIA_TREE_NAV_DIR_HREF}\\">&nbsp;{MEDIA_TREE_NAV_DIR} /</a> <!-- END mediaTreeNavigation --></td>\r\n        </tr>\r\n        <!-- BEGIN mediaDirectoryTree -->\r\n        <tr class=\\"{MEDIA_DIR_TREE_ROW}\\" valign=\\"middle\\">\r\n            <td width=\\"16\\">&nbsp;</td>\r\n            <td align=\\"center\\" width=\\"16\\"><img title=\\"icon\\" height=\\"16\\" alt=\\"icon\\" src=\\"{MEDIA_FILE_ICON}\\" width=\\"16\\"/></td>\r\n            <td width=\\"100%\\"><a title=\\"{MEDIA_FILE_NAME}\\" href=\\"{MEDIA_FILE_NAME_HREF}\\">{MEDIA_FILE_NAME}</a></td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\">{MEDIA_FILE_SIZE}&nbsp;</td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\">{MEDIA_FILE_TYPE}&nbsp;</td>\r\n            <td nowrap=\\"nowrap\\" align=\\"right\\">{MEDIA_FILE_DATE}&nbsp;</td>\r\n        </tr>\r\n        <!-- END mediaDirectoryTree --><!-- BEGIN mediaEmptyDirectory -->\r\n        <tr class=\\"row1\\">\r\n            <td>&nbsp;</td>\r\n            <td colspan=\\"5\\">{TXT_MEDIA_DIR_EMPTY}</td>\r\n        </tr>\r\n        <!-- END mediaEmptyDirectory -->\r\n    </tbody>\r\n</table>', 'Media Archiv #3', '', 'y', 0, 'on', 'system', 3, '2');
INSERT INTO `contrexx_module_repository` VALUES (472, 12, '<table cellspacing=\\"5\\" cellpadding=\\"0\\" width=\\"100%\\" border=\\"0\\" id=\\"cat\\">\r\n<tr>\r\n<td width=\\"33%\\" class=\\"title\\">&raquo;&nbsp;<a href=\\''?section=directory&amp;latest=true\\''>{LATEST}</a></td>\r\n<td width=\\"33%\\" class=\\"title\\">&raquo;&nbsp;<a href=\\''?section=directory&amp;popular=true\\''>{POPULAR}</a></td>\r\n<td width=\\"34%\\" colspan=\\"2\\" class=\\"title\\">&raquo;&nbsp;<a href=\\''?section=directory&amp;cmd=add\\''>{NEW}</a></td>\r\n</tr>\r\n<tr><td colspan=\\"4\\"><form action=\\"index.php?\\" method=\\"get\\"><input name=\\"term\\" value=\\"\\" size=\\"19\\" maxlength=\\"100\\" /><input type=\\"hidden\\" name=\\"section\\" value=\\"directory\\" size=\\"19\\" maxlength=\\"100\\" /><input type=\\"hidden\\" name=\\"cmd\\" value=\\"search\\" size=\\"19\\" maxlength=\\"100\\" />&nbsp;<input value=\\"{TXT_SEARCH}\\" name=\\"search\\" type=\\"submit\\" /></form></td>\r\n</table>\r\n<br />\r\n<table cellspacing=\\"5\\" cellpadding=\\"0\\" width=\\"100%\\" border=\\"0\\" id=\\"cat\\">\r\n<tr>\r\n<td width=\\"94%\\" colspan=\\"3\\" class=\\"title\\">&raquo;&nbsp;<a href=\\"?section=directory\\">{TXT_DIR}</a>{TXT_CATEGORY}<br />{TXT_DESCRIPTION_CAT}</td>\r\n<td width=\\"6%\\" class=\\"title\\" valign=\\"top\\"><div align=\\"right\\">{RSS_CAT}</div></td>\r\n</tr>\r\n</table>\r\n<!-- BEGIN showCategories -->\r\n<br />\r\n<table cellspacing=\\"5\\" cellpadding=\\"0\\" width=\\"100%\\" border=\\"0\\" id=\\"cat\\">\r\n<tr>\r\n{CATEGORY}\r\n</tr>\r\n</table>\r\n<!-- END showCategories -->\r\n<br />\r\n<table cellspacing=\\"0\\" cellpadding=\\"0\\" width=\\"100%\\" border=\\"0\\" class=\\"feed\\">\r\n<tr>\r\n<th width=\\"50%\\" colspan=\\"2\\">Name&nbsp;</th>\r\n<th width=\\"25%\\" colspan=\\"2\\">Details&nbsp;</th>\r\n<th width=\\"15%\\">Hinzugefügt&nbsp;</th>\r\n<th width=\\"10%\\">Hits&nbsp;</th>\r\n</tr>\r\n<tr>\r\n<td class=\\"spacer\\" colspan=\\"6\\"><img src=\\"images/content/gif/pixel.gif\\" border=\\"0\\" width=\\"1\\" height=\\"1\\" alt=\\"\\" /></td>\r\n</tr>\r\n<!-- BEGIN showLatest -->\r\n<tr>\r\n<th colspan=\\"6\\" class=\\"title\\" valign=\\"top\\"><a href=\\"?section=directory&amp;cmd=detail&amp;id={DETAIL}\\">{NAME}</a>&nbsp;{NEW_FEED}</th>\r\n</tr>\r\n<tr>\r\n<td class=\\"content\\" valign=\\"top\\">{DES}&nbsp;</td>\r\n<td class=\\"content\\" valign=\\"top\\" nowrap>&nbsp;</td>\r\n<td class=\\"content\\" valign=\\"top\\" width=\\"8%\\" nowrap>Typ:&nbsp;<br />Autor:&nbsp;<br />Source:&nbsp;</td>\r\n<td class=\\"content\\" valign=\\"top\\" width=\\"17%\\">{TYP}<br />{AUTHOR}<br />{LINK}</td>\r\n<td class=\\"content\\" valign=\\"top\\" nowrap>{DATE}</td>\r\n<td class=\\"content\\" valign=\\"top\\" nowrap>{HITS}&nbsp;{TXT_HITS}&nbsp;</td>\r\n</tr>\r\n<tr>\r\n<td colspan=\\"6\\"><img src=\\"images/content/gif/pixel.gif\\" border=\\"0\\" width=\\"1\\" height=\\"2\\" alt=\\"\\" /></td>\r\n</tr>\r\n<!-- END showLatest -->\r\n<!-- BEGIN noFeeds -->\r\n<tr>\r\n<td colspan=\\"6\\" class=\\"spacer\\"><br />{NO_FEED}</td>\r\n</tr>\r\n<!-- END noFeeds -->\r\n</table>\r\n<br />\r\n{SEARCH_PAGING}', 'Verzeichnis ( Beta )', '', 'y', 0, 'on', 'system', 0, '1');
INSERT INTO `contrexx_module_repository` VALUES (407, 16, 'Hier k&ouml;nnen Sie Ihre eigenen Allgemeinen Gesch&auml;ftsbedingungen hineinschreiben.<br/>', 'Allgemeinen Geschäftsbedingungen', 'terms', 'n', 398, 'on', 'system', 98, '1');
INSERT INTO `contrexx_module_repository` VALUES (408, 16, '<TABLE cellSpacing="2" cellPadding="1" width="100%" border="0">\r\n  <TR> \r\n    <TD colSpan="2"><B>Eine Online-Bestellung ist einfach.</B></TD>\r\n  </TR>\r\n  <TR> \r\n    <TD align=right colSpan=2> \r\n      <DIV align=left><B><FONT color="red">{SHOP_LOGIN_STATUS}</FONT></B></DIV>\r\n    </TD>\r\n  </TR>\r\n  <TR>\r\n    <TD align="right" colSpan="2">\r\n      <hr width="100%" color="black" noShade size="1">\r\n    </TD>\r\n  </TR>\r\n</TABLE>\r\n  <TABLE cellSpacing="2" cellPadding="1" width="100%" border="0">\r\n<FORM name="shop" action="?section=shop&cmd=account" method="post">  \r\n    <TR> \r\n      <TD width="7%"> </TD>\r\n      <TD width="93%"> </TD>\r\n    </TR>\r\n    <TR> \r\n      <TD colSpan=2><b>Ich bin ein neuer Kunde. </b></TD>\r\n    </TR>\r\n    <TR> \r\n      <TD colSpan=2>Durch Ihre Anmeldung bei uns sind Sie in der Lage schneller \r\n        zu bestellen, kennen jederzeit den Status Ihrer Bestellung und haben immer \r\n        eine aktuelle &Uuml;bersicht &uuml;ber Ihre bisherigen Bestellungen.<br>\r\n      </TD>\r\n    </TR>\r\n    <TR> \r\n      <TD colSpan=2> <br>\r\n        <input type=submit value="Weiter &gt;&gt;" name="login">\r\n        <br><br>\r\n      </TD>\r\n    </TR>\r\n    <TR> \r\n      <TD colSpan=2> \r\n        <hr width="100%" color=black noShade size=1>\r\n      </TD>\r\n    </TR>\r\n	</FORM>\r\n  </TABLE>\r\n  <TABLE class=text cellSpacing=2 cellPadding=1 width="100%" border=0>\r\n<FORM name=shop action="{SHOP_LOGIN_ACTION}" method=post>\r\n    <TR> \r\n      <TD colSpan=2><b>Ich bin bereits Kunde.</b></TD>\r\n    </TR>\r\n    <TR> \r\n      <TD width="7%" nowrap>E-Mail Adresse: </TD>\r\n      <TD width="93%"> \r\n        <INPUT maxLength=250 size=30 value="{SHOP_LOGIN_EMAIL}" name="username">\r\n      </TD>\r\n    </TR>\r\n    <TR> \r\n      <TD width="7%">Passwort: </TD>\r\n      <TD width="93%"> \r\n        <INPUT type=password maxLength=50 size=30 name="password">\r\n      </TD>\r\n    </TR>\r\n    <TR> \r\n      <TD width="7%"> </TD>\r\n      <TD width="93%"> \r\n        <INPUT type=submit value="Anmleden &gt;&gt;" name=login>\r\n      </TD>\r\n    </TR>\r\n	</FORM>\r\n  </TABLE>', 'Mein Konto', 'login', 'y', 398, 'off', '', 99, '1');
INSERT INTO `contrexx_module_repository` VALUES (409, 16, '<table cellspacing=2 cellpadding=1 width="100%" border=0>\r\n  <tbody> \r\n  <tr> \r\n    <td colspan=2><b>Mein Konto</b></td>\r\n  </tr>\r\n  <tr> \r\n    <td colspan=2> Nutzen Sie das Konto um Ihre Bestellungen und Ihre Daten komfortabel \r\n      zu kontrollieren und zu verwalten.<br>\r\n    </td>\r\n  </tr>\r\n  <tr valign="top"> \r\n    <td noWrap rowspan="4" width="20%"><a href="?section=shop&cmd=logout">Log-Out</a><br>\r\n      <a href="?section=shop&cmd=delete">Konto löschen</a></td>\r\n    <td width="92%"> <a href="?section=shop&cmd=orders">Meine Bestellungen \r\n      ansehen</a></td>\r\n  </tr>\r\n  <tr valign="top"> \r\n    <td width="92%"> <a href="?section=shop&cmd=mod">Meine Konto-Daten ändern</a></td>\r\n  </tr>\r\n  <tr valign="top"> \r\n    <td width="92%"> <br>\r\n      <table width="100%" border="0" cellspacing="0" cellpadding="0">\r\n        <tr valign="top"> \r\n          <td colspan="2"><b>eMail-Adresse</b><font color="#C1C0D0"><br>\r\n            </font>{SHOP_EMAIL}</td>\r\n        </tr>\r\n        <tr valign="top"> \r\n          <td><b>Kundennummer</b><font color="#C1C0D0"><br>\r\n            </font>{SHOP_CUSTOMERID}<br>\r\n            <br>\r\n            Zahlungsart<br>\r\n            {SHOP_PAYMENT}</td>\r\n          <td width="61%"><b>Rechnungsadresse</b><font color="#C1C0D0"><br>\r\n            </font>{SHOP_SIGN}<br>\r\n            {SHOP_FIRSTNAME} {SHOP_LASTNAME}<br>\r\n            {SHOP_ADDRESS} <br>\r\n            {SHOP_ZIP}  {SHOP_CITY}<br>\r\n            {SHOP_COUNTRY} </td>\r\n        </tr>\r\n      </table>\r\n      <br>\r\n    </td>\r\n  </tr>\r\n  <tr valign="top"> \r\n    <td> <a href="?section=shop&cmd=modpass">Mein Passwort und </a><a href="?section=shop&cmd=modemail">eMail-Adresse ändern</a> </td>\r\n  </tr>\r\n  </tbody> \r\n</table>', 'Konto Übersicht', 'development', 'y', 408, 'off', '', 0, '1');
INSERT INTO `contrexx_module_repository` VALUES (410, 16, '<FORM name=shop action={SHOP_CHECKOUT_ACTION} method=post>\r\n  <TABLE class=text cellSpacing=2 cellPadding=1 width="100%" border=0>\r\n    <TBODY> \r\n    <TR> \r\n      <TD colSpan=2><B>Passwort Hilfe</B></TD>\r\n    </TR>\r\n    <TR> \r\n      <TD colSpan=2><B><FONT color=red>{SHOP_PASSWORD_STATUS}</FONT></B></TD>\r\n    </TR>\r\n    <tr> \r\n      <td noWrap colspan="2"><br>\r\n        Geben Sie die E-Mail-Adresse für Ihr Konto bei Sat-com Multimedia \r\n        ein. </td>\r\n    </tr>\r\n    <TR> \r\n      <TD noWrap width="8%"> \r\n        <input size=50 value={SHOP_PASSWORD_EMAIL} name=email>\r\n      </TD>\r\n      <TD width="92%"> \r\n        <input type=submit value=Weiter name=pay>\r\n      </TD>\r\n    </TR>\r\n    <TR> \r\n      <TD noWrap colspan="2"><br>\r\n        Nachdem Sie den "Weiter"-Knopf angeklickt haben, schicken wir \r\n        Ihnen eine Benachrichtigung per E-Mail mit einem neuen Passwort. <br>\r\n        <br>\r\n        <br>\r\n        <br>\r\n        <br>\r\n        Wenn Sie Ihr Passwort vergessen haben und sich Ihre alte E-Mail-Adresse \r\n        nicht weiter verwenden lässt, Sie aber kein neues Konto eröffnen \r\n        wollen, dann können Sie sich telefonisch bei uns melden. </TD>\r\n    </TR>\r\n    </TBODY> \r\n  </TABLE>\r\n  <BR>\r\n<HR width="100%" color=black noShade SIZE=1>\r\n</FORM>', 'Passwort Hilfe', 'sendpass', 'y', 408, 'off', '', 0, '1');
INSERT INTO `contrexx_module_repository` VALUES (468, 23, '<table width=\\"100%\\" cellspacing=\\"0\\" cellpadding=\\"0 border=\\"0\\">\r\n<tbody>\r\n<tr>\r\n<td><a href=\\"index.php?section=community&amp;cmd=register\\">Mitglied werden</a></td>\r\n</tr>\r\n<tr>\r\n<td><a href=\\"index.php?section=community&amp;cmd=profile\\">Mein Profil</a></td>\r\n</tr>\r\n</tbody>\r\n</table>', 'Community', '', 'y', 0, 'on', 'system', 111, '1');
INSERT INTO `contrexx_module_repository` VALUES (469, 23, '{COMMUNITY_STATUS_MESSAGE}<br /> <!-- BEGIN community_registration_form -->\r\n<form method=\\"post\\" action=\\"index.php?section=community&amp;cmd=register\\">\r\n    <table width=\\"100%\\" cellspacing=\\"5\\" cellpadding=\\"0\\" border=\\"0\\" summary=\\"registration\\">\r\n        <tbody>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">{TXT_LOGIN_NAME}:&nbsp;<font color=\\"red\\">*</font></td>\r\n                <td width=\\"70%\\"><input type=\\"text\\" value=\\"{COMMUNITY_USERNAME}\\" maxlength=\\"40\\" size=\\"30\\" name=\\"username\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">{TXT_LOGIN_PASSWORD}:&nbsp;<font color=\\"red\\">*</font></td>\r\n                <td width=\\"70%\\"><input type=\\"password\\" maxlength=\\"50\\" size=\\"30\\" name=\\"password\\" />&nbsp;{TXT_PASSWORD_MINIMAL_CHARACTERS}</td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">{TXT_VERIFY_PASSWORD}:&nbsp;<font color=\\"red\\">*</font></td>\r\n                <td width=\\"70%\\"><input type=\\"password\\" maxlength=\\"50\\" size=\\"30\\" name=\\"password2\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">{TXT_EMAIL}: <font color=\\"red\\">*</font></td>\r\n                <td width=\\"70%\\"><input type=\\"text\\" value=\\"{COMMUNITY_EMAIL}\\" maxlength=\\"255\\" size=\\"30\\" name=\\"email\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td colspan=\\"2\\">&nbsp;</td>\r\n            </tr>\r\n            <tr>\r\n                <td colspan=\\"2\\"><input type=\\"submit\\" name=\\"register\\" value=\\"{TXT_REGISTER}\\" /><br /><br />[<font color=\\"red\\">*</font>] {TXT_ALL_FIELDS_REQUIRED} {TXT_PASSWORD_NOT_USERNAME_TEXT}</td>\r\n            </tr>\r\n        </tbody>\r\n    </table>\r\n</form>\r\n<!-- END community_registration_form -->', 'Registration', 'register', 'n', 468, 'off', 'system', 0, '1');
INSERT INTO `contrexx_module_repository` VALUES (470, 23, '<form action=\\"index.php?section=community&amp;cmd=profile\\" method=\\"post\\">\r\n    <table width=\\"100%\\" cellspacing=\\"5\\" cellpadding=\\"0\\" border=\\"0\\">\r\n        <tbody>\r\n            <tr>\r\n                <th colspan=\\"2\\">Pers&ouml;nliche Angaben</th>\r\n            </tr>\r\n            <tr>\r\n                <td colspan=\\"2\\">{COMMUNITY_STATUS_MESSAGE_PROFILE}</td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">Vorname</td>\r\n                <td width=\\"70%\\"><input type=\\"text\\" name=\\"firstname\\" value=\\"{COMMUNITY_FIRSTNAME}\\" tabindex=\\"1\\" style=\\"width: 250px;\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">Nachname</td>\r\n                <td width=\\"70%\\"><input type=\\"text\\" name=\\"lastname\\" value=\\"{COMMUNITY_LASTNAME}\\" tabindex=\\"2\\" style=\\"width: 250px;\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">Wohnort</td>\r\n                <td width=\\"70%\\"><input type=\\"text\\" name=\\"residence\\" value=\\"{COMMUNITY_RESIDENCE}\\" tabindex=\\"3\\" style=\\"width: 250px;\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">Beruf</td>\r\n                <td width=\\"70%\\"><input type=\\"text\\" name=\\"profession\\" value=\\"{COMMUNITY_PROFESSION}\\" tabindex=\\"4\\" style=\\"width: 250px;\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">Interessen</td>\r\n                <td width=\\"70%\\"><input type=\\"text\\" name=\\"interests\\" value=\\"{COMMUNITY_INTERESTS}\\" tabindex=\\"5\\" style=\\"width: 250px;\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">Webseite</td>\r\n                <td width=\\"70%\\"><input type=\\"text\\" name=\\"webpage\\" value=\\"{COMMUNITY_WEBPAGE}\\" tabindex=\\"6\\" style=\\"width: 250px;\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td colspan=\\"2\\"><input type=\\"submit\\" name=\\"change_profile\\" value=\\"Angaben Ändern\\" tabindex=\\"7\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td colspan=\\"2\\"><hr /></td>\r\n            </tr>\r\n        </tbody>\r\n    </table>\r\n</form>\r\n<form action=\\"index.php?section=community&amp;cmd=profile\\" method=\\"post\\">\r\n    <table width=\\"100%\\" cellspacing=\\"5\\" cellpadding=\\"0\\" border=\\"0\\">\r\n        <tbody>\r\n            <tr>\r\n                <th colspan=\\"2\\">E-Mail Adresse &auml;ndern</th>\r\n            </tr>\r\n        </tbody>\r\n        <tbody>\r\n            <tr>\r\n                <td colspan=\\"2\\">{COMMUNITY_STATUS_MESSAGE_EMAIL}</td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">Aktuelle E-Mail Adresse</td>\r\n                <td width=\\"70%\\">{COMMUNITY_EMAIL}</td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">Neue E-Mail Adresse</td>\r\n                <td width=\\"70%\\"><input type=\\"text\\" name=\\"email\\" value=\\"{COMMUNITY_NEW_EMAIL}\\" tabindex=\\"8\\" style=\\"width: 250px;\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">E-Mail best&auml;tigen</td>\r\n                <td width=\\"70%\\"><input type=\\"text\\" name=\\"email2\\" value=\\"\\" tabindex=\\"9\\" style=\\"width: 250px;\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td colspan=\\"2\\"><input type=\\"submit\\" name=\\"change_email\\" value=\\"E-Mail Ändern\\" tabindex=\\"10\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td colspan=\\"2\\"><hr /></td>\r\n            </tr>\r\n        </tbody>\r\n    </table>\r\n</form>\r\n<form action=\\"index.php?section=community&amp;cmd=profile\\" method=\\"post\\">\r\n    <table width=\\"100%\\" cellspacing=\\"5\\" cellpadding=\\"0\\" border=\\"0\\">\r\n        <tbody>\r\n            <tr>\r\n                <th colspan=\\"2\\">Kennwort &auml;ndern</th>\r\n            </tr>\r\n            <tr>\r\n                <td colspan=\\"2\\">{COMMUNITY_STATUS_MESSAGE_PASSWORD}</td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">Neues Kennwort</td>\r\n                <td width=\\"70%\\"><input type=\\"password\\" name=\\"password\\" value=\\"\\" tabindex=\\"11\\" style=\\"width: 250px;\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">Kennwort best&auml;tigen</td>\r\n                <td width=\\"70%\\"><input type=\\"password\\" name=\\"password2\\" value=\\"\\" tabindex=\\"12\\" style=\\"width: 250px;\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td colspan=\\"2\\"><input type=\\"submit\\" name=\\"change_password\\" value=\\"Kennwort Ändern\\" tabindex=\\"13\\" /></td>\r\n            </tr>\r\n        </tbody>\r\n    </table>\r\n</form>', 'Mein Profil', 'profile', 'n', 468, 'off', 'system', 0, '1');
INSERT INTO `contrexx_module_repository` VALUES (466, 18, '<form method=\\"post\\" action=\\"index.php?section=login&amp;cmd=resetpw\\">\r\n    <input type=\\"hidden\\" name=\\"restore_key\\" value=\\"{LOGIN_RESTORE_KEY}\\" /> <input type=\\"hidden\\" name=\\"username\\" value=\\"{LOGIN_USERNAME}\\" />\r\n    <table width=\\"100%\\" cellspacing=\\"0\\" cellpadding=\\"2\\" border=\\"0\\" summary=\\"set new password form\\">\r\n        <tbody>\r\n            <!-- BEGIN login_reset_password -->\r\n            <tr>\r\n                <td width=\\"70%\\" colspan=\\"2\\">{TXT_SET_PASSWORD_TEXT}</td>\r\n                <td width=\\"30%\\" rowspan=\\"5\\">&nbsp;&nbsp;&nbsp;&nbsp;<img width=\\"32\\" height=\\"32\\" align=\\"middle\\" src=\\"images/modules/login/lost_pw.gif\\" alt=\\"login key\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">{TXT_USERNAME}</td>\r\n                <td width=\\"40%\\">{LOGIN_USERNAME}</td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">{TXT_PASSWORD}&nbsp;{TXT_PASSWORD_MINIMAL_CHARACTERS}</td>\r\n                <td width=\\"40%\\"><input type=\\"password\\" maxlength=\\"50\\" size=\\"30\\" name=\\"password\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\" rowspan=\\"2\\" style=\\"vertical-align: top;\\">{TXT_VERIFY_PASSWORD}</td>\r\n                <td width=\\"40%\\"><input type=\\"password\\" maxlength=\\"50\\" size=\\"30\\" name=\\"password2\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td><input type=\\"submit\\" value=\\"{TXT_SET_NEW_PASSWORD}\\" name=\\"reset_password\\" /></td>\r\n            </tr>\r\n            <!-- END login_reset_password -->\r\n            <tr>\r\n                <td colspan=\\"2\\" style=\\"color: rgb(255, 0, 0); font-weight: bold;\\"><br />{LOGIN_STATUS_MESSAGE}</td>\r\n            </tr>\r\n        </tbody>\r\n    </table>\r\n</form>', 'Neues Passwort setzen', 'resetpw', 'n', 464, 'off', 'system', 0, '1');
INSERT INTO `contrexx_module_repository` VALUES (465, 18, '<form method=\\"post\\" action=\\"index.php?section=login&amp;cmd=lostpw\\">\r\n    <table width=\\"100%\\" cellspacing=\\"0\\" cellpadding=\\"2\\" border=\\"0\\" summary=\\"lost password form\\">\r\n        <tbody>\r\n            <!-- BEGIN login_lost_password -->\r\n            <tr>\r\n                <td width=\\"70%\\" colspan=\\"2\\">{TXT_LOST_PASSWORD_TEXT}</td>\r\n                <td width=\\"30%\\" rowspan=\\"3\\">&nbsp;&nbsp;&nbsp;&nbsp;<img width=\\"32\\" height=\\"32\\" align=\\"middle\\" src=\\"images/modules/login/lost_pw.gif\\" alt=\\"login key\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\" rowspan=\\"2\\" style=\\"vertical-align: top;\\">{TXT_EMAIL}:</td>\r\n                <td width=\\"40%\\"><input type=\\"text\\" maxlength=\\"255\\" style=\\"width: 100%;\\" size=\\"30\\" name=\\"email\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td><input type=\\"submit\\" name=\\"restore_pw\\" value=\\"{TXT_RESET_PASSWORD}\\" /></td>\r\n            </tr>\r\n            <!-- END login_lost_password -->\r\n            <tr>\r\n                <td colspan=\\"3\\" style=\\"color: rgb(255, 0, 0); font-weight: bold;\\"><br />{LOGIN_STATUS_MESSAGE}</td>\r\n            </tr>\r\n        </tbody>\r\n    </table>\r\n</form>', 'Passwort vergessen?', 'lostpw', 'n', 464, 'off', 'system', 0, '1');
INSERT INTO `contrexx_module_repository` VALUES (464, 18, '<form method=\\"post\\" action=\\"index.php?section=login\\" name=\\"loginForm\\">\r\n    <input type=\\"hidden\\" value=\\"{LOGIN_REDIRECT}\\" name=\\"redirect\\" />\r\n    <table width=\\"100%\\" cellspacing=\\"0\\" cellpadding=\\"2\\" border=\\"0\\">\r\n        <tbody>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\">{TXT_USER_NAME}:</td>\r\n                <td width=\\"40%\\"><input type=\\"\\" name=\\"USERNAME\\" value=\\"\\" size=\\"30\\" /></td>\r\n                <td width=\\"30%\\" rowspan=\\"3\\">&nbsp;&nbsp;&nbsp;&nbsp;<img width=\\"20\\" height=\\"28\\" align=\\"middle\\" src=\\"/images/modules/login/login_key.gif\\" alt=\\"\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"30%\\" nowrap=\\"nowrap\\" rowspan=\\"3\\" style=\\"vertical-align: top;\\">{TXT_PASSWORD}:</td>\r\n                <td width=\\"40%\\"><input type=\\"password\\" name=\\"PASSWORD\\" value=\\"\\" size=\\"30\\" /> </td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"40%\\"><input type=\\"submit\\" name=\\"login\\" value=\\"{TXT_LOGIN}\\" size=\\"15\\" class=\\"input\\" /> </td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"40%\\" colspan=\\"2\\"><a title=\\"{TXT_LOST_PASSWORD}\\" href=\\"index.php?section=login&amp;cmd=lostpw\\">{TXT_PASSWORD_LOST}</a></td>\r\n            </tr>\r\n            <tr>\r\n                <td style=\\"color: rgb(255, 0, 0); font-weight: bold;\\" colspan=\\"3\\"><br />{LOGIN_STATUS_MESSAGE}</td>\r\n            </tr>\r\n        </tbody>\r\n    </table>\r\n</form>', 'Login', '', 'n', 0, 'off', 'system', 130, '1');
INSERT INTO `contrexx_module_repository` VALUES (471, 23, '{COMMUNITY_STATUS_MESSAGE}', 'Benutzerkonto aktivieren', 'activate', 'y', 468, 'off', 'system', 0, '1');
INSERT INTO `contrexx_module_repository` VALUES (498, 8, 'F&uuml;gen Sie den folgenden Code in Ihre eigene Webseite ein, um das RSS Feed von {NEWS_HOSTNAME} auf Ihrer Webseite einzubinden:<br /> <br />\r\n<form>\r\n<textarea style=\\"width: 98%; font-size: 95%;\\" wrap=\\"PHYSICAL\\" rows=\\"18\\" name=\\"code\\">{NEWS_RSS2JS_CODE}</textarea>\r\n<input type=button value=\\"Alles markieren\\" onclick=\\"javascript:this.form.code.focus();this.form.code.select();\\" name=\\"button\\">\r\n</form>\r\n\r\n<br />\r\nGemäss obigem Beispiel sieht die Ausgabe dann folgendermassen aus:<br /><br />\r\n<script language=\\"JavaScript\\" type=\\"text/javascript\\">\r\n<!--\r\n// Diese Variablen sind optional\r\nvar rssFeedFontColor = \\"#000000\\"; // Schriftfarbe\r\nvar rssFeedFontSize = 8; // Schriftgrösse\r\nvar rssFeedFont = \\"Verdana, Arial\\"; // Schriftart\r\nvar rssFeedLimit = 10; // Anzahl anzuzeigende Newsmeldungen\r\nvar rssFeedShowDate = true; // Datum der Newsmeldung anzeigen\r\nvar rssFeedTarget = \\"_blank\\"; // _blank | _parent | _self | _top\r\n// -->\r\n</script>\r\n<script type=\\"text/javascript\\" language=\\"JavaScript\\" src=\\"{NEWS_RSS2JS_URL}\\"></script>\r\n<noscript>\r\n<a href=\\"{NEWS_RSS_FEED_URL}\\">{NEWS_HOSTNAME} - News anzeigen</a>\r\n</noscript>', 'News Feed', 'feed', 'n', 495, 'on', 'system', 1, '1');
INSERT INTO `contrexx_module_repository` VALUES (508, 3, '<table width=\\"100%\\" cellspacing=\\"0\\" cellpadding=\\"2\\" border=\\"0\\">\r\n    <tbody>\r\n        <tr>\r\n            <td colspan=\\"2\\">Sie sind hier: {GALLERY_CATEGORY_TREE}</td>\r\n        </tr>\r\n        <!-- BEGIN galleryCategories -->\r\n        <tr>\r\n            <td colspan=\\"2\\"><hr size=\\"1\\" /></td>\r\n        </tr>\r\n        <tr class=\\"row{GALLERY_STYLE}\\">\r\n            <td width=\\"1%\\" valign=\\"top\\" align=\\"left\\">{GALLERY_CATEGORY_IMAGE}</td>\r\n            <td valign=\\"top\\"><b>{GALLERY_CATEGORY_NAME}</b><br />{GALLERY_CATEGORY_INFO}<br />{GALLERY_CATEGORY_DESCRIPTION}</td>\r\n        </tr>\r\n        <!-- END galleryCategories -->\r\n        <tr>\r\n            <td colspan=\\"2\\"><hr size=\\"1\\" /></td>\r\n        </tr>\r\n    </tbody>\r\n</table>\r\n<!-- CATEGORY END AND IMAGES START -->   <!-- BEGIN galleryImageBlock --> {GALLERY_JAVASCRIPT}\r\n<table width=\\"100%\\" cellspacing=\\"1\\" cellpadding=\\"0\\" border=\\"0\\">\r\n    <tbody>\r\n        <tr>\r\n            <td colspan=\\"3\\">{GALLERY_CATEGORY_COMMENT}<br /></td>\r\n        </tr>\r\n        <tr>\r\n            <td colspan=\\"3\\"><hr size=\\"1\\" /></td>\r\n        </tr>\r\n        <!-- BEGIN galleryShowImages -->\r\n        <tr>\r\n            <td width=\\"33%\\" valign=\\"top\\" align=\\"center\\" id=\\"gallery\\"> {GALLERY_IMAGE1}<br /> {GALLERY_IMAGE_LINK1} </td>\r\n            <td width=\\"33%\\" valign=\\"top\\" align=\\"center\\" id=\\"gallery\\"> {GALLERY_IMAGE2}<br /> {GALLERY_IMAGE_LINK2} </td>\r\n            <td width=\\"33%\\" valign=\\"top\\" align=\\"center\\" id=\\"gallery\\"> {GALLERY_IMAGE3}<br /> {GALLERY_IMAGE_LINK3} </td>\r\n        </tr>\r\n        <!-- END galleryShowImages -->\r\n    </tbody>\r\n</table>\r\n<!-- END galleryImageBlock -->', 'Bildergalerie', '', 'n', 0, 'on', 'system', 2, '1');
INSERT INTO `contrexx_module_repository` VALUES (500, 27, '{RECOM_STATUS} <!-- BEGIN recommend_form --> {RECOM_TEXT} {RECOM_SCRIPT}\r\n<form action=\\"index.php?section=recommend&amp;act=sendRecomm\\" method=\\"post\\" name=\\"recommend\\">\r\n    <input type=\\"hidden\\" name=\\"uri\\" value=\\"{RECOM_REFERER}\\" /> <input type=\\"hidden\\" name=\\"female_salutation_text\\" value=\\"{RECOM_FEMALE_SALUTATION_TEXT}\\" /> <input type=\\"hidden\\" name=\\"male_salutation_text\\" value=\\"{RECOM_MALE_SALUTATION_TEXT}\\" /> <input type=\\"hidden\\" name=\\"preview_text\\" value=\\"{RECOM_PREVIEW}\\" />\r\n    <table style=\\"width: 90%;\\">\r\n        <tbody>\r\n            <tr>\r\n                <td style=\\"width: 40%; padding-bottom: 15px;\\">{RECOM_TXT_RECEIVER_NAME}:</td>\r\n                <td style=\\"padding-bottom: 15px; width: 60%;\\"><input type=\\"text\\" name=\\"receivername\\" maxlength=\\"100\\" value=\\"{RECOM_RECEIVER_NAME}\\" style=\\"width: 100%;\\" onchange=\\"update();\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td style=\\"padding-bottom: 15px;\\">{RECOM_TXT_RECEIVER_MAIL}:</td>\r\n                <td style=\\"padding-bottom: 15px;\\"><input type=\\"text\\" name=\\"receivermail\\" maxlength=\\"100\\" value=\\"{RECOM_RECEIVER_MAIL}\\" style=\\"width: 100%;\\" onchange=\\"update();\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td valign=\\"top\\" style=\\"padding-bottom: 15px;\\">{RECOM_TXT_GENDER}:</td>\r\n                <td style=\\"padding-bottom: 15px;\\"><input type=\\"radio\\" name=\\"gender\\" style=\\"border: medium none ; margin-left: 0px;\\" value=\\"female\\" onclick=\\"update();\\" />{RECOM_TXT_FEMALE}<br /> 		<input type=\\"radio\\" name=\\"gender\\" style=\\"border: medium none ; margin-left: 0px;\\" value=\\"male\\" onclick=\\"update();\\" />{RECOM_TXT_MALE}</td>\r\n            </tr>\r\n            <tr>\r\n                <td width=\\"100\\" style=\\"padding-bottom: 15px;\\">{RECOM_TXT_SENDER_NAME}:</td>\r\n                <td style=\\"padding-bottom: 15px;\\"><input type=\\"text\\" name=\\"sendername\\" maxlength=\\"100\\" value=\\"{RECOM_SENDER_NAME}\\" style=\\"width: 100%;\\" onchange=\\"update();\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td style=\\"padding-bottom: 15px;\\">{RECOM_TXT_SENDER_MAIL}:</td>\r\n                <td style=\\"padding-bottom: 15px;\\"><input type=\\"text\\" name=\\"sendermail\\" maxlength=\\"100\\" value=\\"{RECOM_SENDER_MAIL}\\" style=\\"width: 100%;\\" onchange=\\"update();\\" /></td>\r\n            </tr>\r\n            <tr>\r\n                <td valign=\\"top\\">{RECOM_TXT_COMMENT}:</td>\r\n                <td style=\\"padding-bottom: 15px;\\"><textarea rows=\\"7\\" cols=\\"30\\" name=\\"comment\\" style=\\"width: 100%;\\" onchange=\\"update();\\">{RECOM_COMMENT}</textarea></td>\r\n            </tr>\r\n            <tr>\r\n                <td valign=\\"top\\">{RECOM_TXT_PREVIEW}:</td>\r\n                <td style=\\"padding-bottom: 15px;\\"> 	<textarea name=\\"preview\\" style=\\"width: 100%; height: 200px;\\" readonly=\\"\\"></textarea></td>\r\n            </tr>\r\n            <tr>\r\n                <td>&nbsp;</td>\r\n                <td><input type=\\"submit\\" value=\\"Senden\\" /> <input type=\\"reset\\" value=\\"Löschen\\" /></td>\r\n            </tr>\r\n        </tbody>\r\n    </table>\r\n</form>\r\n<!-- END recommend_form -->', 'Seite weiterempfehlen', '', 'n', 0, 'off', 'system', 1000, '1');
INSERT INTO `contrexx_module_repository` VALUES (495, 8, '<form name=\\"formNews\\" action=\\"index.php?section=news\\" method=\\"post\\">\r\n    <select onchange=\\"this.form.submit()\\" name=\\"category\\">\r\n    <option value=\\"\\" selected=\\"selected\\">{NEWS_NO_CATEGORY}</option>\r\n{NEWS_CAT_DROPDOWNMENU}</select>\r\n</form>\r\n<br/>\r\n<table id=\\"news\\" cellspacing=\\"0\\" cellpadding=\\"5\\" width=\\"100%\\" border=\\"0\\">\r\n<tr>\r\n<td nowrap=\\"nowrap\\" width=\\"15%\\">{TXT_DATE}</th>\r\n<td nowrap=\\"nowrap\\" width=\\"70%\\">{TXT_TITLE}</th>\r\n<td nowrap=\\"nowrap\\" width=\\"15%\\">{TXT_CATEGORY}</th>\r\n</tr>\r\n<!-- BEGIN newsrow -->\r\n<tr>\r\n<td nowrap=\\"nowrap\\" width=\\"15%\\">{NEWS_DATE}&nbsp;&nbsp;</td>\r\n<td width=\\"70%\\"><b>{NEWS_LINK}</b>&nbsp;&nbsp;</td>\r\n<td nowrap=\\"nowrap\\" width=\\"15%\\">{NEWS_CATEGORY}</td>\r\n</tr>\r\n<!-- END newsrow -->\r\n</table>\r\n<br/>\r\n{NEWS_PAGING}<br/>\r\n<br/>', 'News', '', 'n', 0, 'on', 'system', 11, '1');
INSERT INTO `contrexx_module_repository` VALUES (496, 8, 'Veröffentlicht am: {NEWS_DATE}\r\n<br /><br />\r\n{NEWS_TEXT} <br />\r\n{NEWS_SOURCE}<br />\r\n{NEWS_URL} \r\n<br />\r\n{NEWS_LASTUPDATE}<br />', 'Newsmeldung', 'details', 'y', 495, 'off', 'system', 1, '1');
INSERT INTO `contrexx_module_repository` VALUES (497, 8, '<b>{NEWS_STATUS_MESSAGE}</b>\r\n<form action=\\"index.php?section=news&amp;cmd=submit\\" method=\\"post\\">\r\n<table width=\\"100%\\" cellspacing=\\"0\\" cellpadding=\\"5\\" border=\\"0\\">\r\n    <tbody>\r\n        <tr>\r\n            <th colspan=\\"2\\">{TXT_NEWS_MESSAGE}</th>\r\n        </tr>\r\n        <tr>\r\n            <td width=\\"20%\\">{TXT_TITLE}</td>\r\n            <td width=\\"80%\\"><input type=\\"text\\" style=\\"width: 250px;\\" name=\\"newsTitle\\" value=\\"{NEWS_TITLE}\\" maxlength=\\"250\\" /></td>\r\n        </tr>\r\n        <tr>\r\n            <td width=\\"20%\\">{TXT_CATEGORY}</td>\r\n            <td width=\\"80%\\"><select style=\\"width: 250px;\\" name=\\"newsCat\\">{NEWS_CAT_MENU}</select></td>\r\n        </tr>\r\n        <tr>\r\n            <td width=\\"20%\\">{TXT_EXTERNAL_SOURCE}</td>\r\n            <td width=\\"80%\\"><input type=\\"text\\" style=\\"width: 250px;\\" name=\\"newsSource\\" value=\\"{NEWS_SOURCE}\\" maxlength=\\"250\\" /></td>\r\n        </tr>\r\n        <tr>\r\n            <td width=\\"20%\\">{TXT_LINK} #1</td>\r\n            <td width=\\"80%\\"><input type=\\"text\\" style=\\"width: 250px;\\" name=\\"newsUrl1\\" value=\\"{NEWS_URL1}\\" maxlength=\\"250\\" /></td>\r\n        </tr>\r\n        <tr>\r\n            <td width=\\"20%\\">{TXT_LINK} #2</td>\r\n            <td width=\\"80%\\"><input type=\\"text\\" style=\\"width: 250px;\\" name=\\"newsUrl2\\" value=\\"{NEWS_URL2}\\" maxlength=\\"250\\" /></td>\r\n        </tr>\r\n        <tr>\r\n            <th colspan=\\"2\\"><br />{TXT_NEWS_CONTENT}</th>\r\n        </tr>\r\n        <tr>\r\n            <td colspan=\\"2\\">{NEWS_TEXT}</td>\r\n        </tr>\r\n        <tr>\r\n            <td colspan=\\"2\\"><input type=\\"submit\\" name=\\"submitNews\\" value=\\"{TXT_SUBMIT_NEWS}\\" /></td>\r\n        </tr>\r\n    </tbody>\r\n</table>\r\n</form>', 'Newsanmelden', 'submit', 'y', 495, 'on', 'system', 1, '1');
INSERT INTO `contrexx_module_repository` VALUES (502, 30, '{LIVECAM_JAVASCRIPT}\r\n<form action=\\"index.php?section=livecam\\" method=\\"post\\" name=\\"form\\">\r\n	<input type=\\"submit\\" value=\\"Aktuelles Bild\\" tabindex=\\"1\\" accesskey=\\"A\\" name=\\"act[current]\\" />&nbsp;<input type=\\"submit\\" value=\\"Heute\\" name=\\"act[today]\\" style=\\"border-width: 1px;\\" size=\\"12\\" />&nbsp;<input type=\\"text\\" style=\\"border-width: 1px;\\" size=\\"12\\" value=\\"{LIVECAM_DATE}\\" id=\\"DPC_datum\\" name=\\"date\\" />&nbsp;<input type=\\"submit\\" value=\\"Archiv Anzeigen\\" name=\\"act[archive]\\" style=\\"border-width: 1px;\\" size=\\"12\\" />\r\n</form>\r\n<br />\r\n{LIVECAM_STATUS_MESSAGE}<br />\r\n<!-- BEGIN livecamPicture -->\r\n<a href=\\"?section=livecam&amp;act=today\\" title=\\"{LIVECAM_IMAGE_TEXT}\\"><img width=\\"640\\" height=\\"480\\" border=\\"0\\" alt=\\"{LIVECAM_IMAGE_TEXT}\\" src=\\"{LIVECAM_CURRENT_IMAGE}\\" /></a><br />\r\nDie Seite wird jede Minute automatisch aktualisiert.  <a onclick=\\"javascript:document.location.reload();\\" href=\\"index.php?section=livecam\\">Aktualisieren.</a>\r\n<!-- END livecamPicture -->\r\n<!-- BEGIN livecamArchive -->\r\n<table width=\\"100%\\" cellspacing=\\"0\\" cellpadding=\\"0\\" border=\\"0\\">\r\n    <tbody>\r\n        <tr>\r\n            <td colspan=\\"3\\">\r\n            <h2>Archiv {LIVECAM_DATE}</h2>\r\n            </td>\r\n        </tr>\r\n        <!-- BEGIN livecamArchiveRow -->\r\n        <tr>\r\n            <td>\r\n            <p><!-- BEGIN livecamArchivePicture1 -->\r\n<a href=\\"{LIVECAM_PICTURE_URL}\\" title=\\"{LIVECAM_PICTURE_TIME}\\"><img src=\\"{LIVECAM_THUMBNAIL_URL}\\" border=\\"0\\" alt=\\"{LIVECAM_PICTURE_TIME}\\" /></a><br />{LIVECAM_PICTURE_TIME}<!-- END livecamArchivePicture1 --><br /></p>\r\n            </td>\r\n            <td>\r\n            <p><!-- BEGIN livecamArchivePicture2 -->\r\n<a href=\\"{LIVECAM_PICTURE_URL}\\" title=\\"{LIVECAM_PICTURE_TIME}\\"><img src=\\"{LIVECAM_THUMBNAIL_URL}\\" border=\\"0\\" alt=\\"{LIVECAM_PICTURE_TIME}\\" /></a><br />{LIVECAM_PICTURE_TIME}<!-- END livecamArchivePicture2 --><br /></p>\r\n            </td>\r\n            <td>\r\n            <p><!-- BEGIN livecamArchivePicture3 -->\r\n<a href=\\"{LIVECAM_PICTURE_URL}\\" title=\\"{LIVECAM_PICTURE_TIME}\\"><img src=\\"{LIVECAM_THUMBNAIL_URL}\\" border=\\"0\\" alt=\\"{LIVECAM_PICTURE_TIME}\\" /></a><br />{LIVECAM_PICTURE_TIME}<!-- END livecamArchivePicture3 --><br /></p>\r\n            </td>\r\n        </tr>\r\n        <!-- END livecamArchiveRow -->\r\n    </tbody>\r\n</table>\r\n<!-- END livecamArchive -->', 'Livebild ansehen', '', 'y', 0, 'on', 'system', 1, '1');



DROP TABLE IF EXISTS `contrexx_modules`;
CREATE TABLE IF NOT EXISTS `contrexx_modules` (
  `id` tinyint(2) default NULL,
  `name` varchar(250) NOT NULL default '',
  `description_variable` varchar(50) NOT NULL default '',
  `status` set('y','n') NOT NULL default 'n',
  `is_required` tinyint(1) NOT NULL default '0',
  `is_core` tinyint(4) NOT NULL default '0',
  UNIQUE KEY `id` (`id`)
) TYPE=MyISAM;

-- 
-- Daten für Tabelle `contrexx_modules`
-- 

INSERT INTO `contrexx_modules` VALUES (0, '', '', 'n', 0, 1);
INSERT INTO `contrexx_modules` VALUES (1, 'core', 'TXT_CORE_MODULE_DESCRIPTION', 'n', 1, 1);
INSERT INTO `contrexx_modules` VALUES (2, 'stats', 'TXT_STATS_MODULE_DESCRIPTION', 'n', 0, 1);
INSERT INTO `contrexx_modules` VALUES (3, 'gallery', 'TXT_GALLERY_MODULE_DESCRIPTION', 'y', 0, 0);
INSERT INTO `contrexx_modules` VALUES (4, 'newsletter', 'TXT_NEWSLETTER_MODULE_DESCRIPTION', 'y', 0, 0);
INSERT INTO `contrexx_modules` VALUES (5, 'search', 'TXT_SEARCH_MODULE_DESCRIPTION', 'y', 0, 1);
INSERT INTO `contrexx_modules` VALUES (6, 'contact', 'TXT_CONTACT_MODULE_DESCRIPTION', 'y', 1, 1);
INSERT INTO `contrexx_modules` VALUES (8, 'news', 'TXT_NEWS_MODULE_DESCRIPTION', 'y', 1, 1);
INSERT INTO `contrexx_modules` VALUES (9, 'media1', 'TXT_MEDIA_MODULE_DESCRIPTION', 'y', 0, 1);
INSERT INTO `contrexx_modules` VALUES (10, 'guestbook', 'TXT_GUESTBOOK_MODULE_DESCRIPTION', 'y', 0, 0);
INSERT INTO `contrexx_modules` VALUES (11, 'sitemap', 'TXT_SITEMAP_MODULE_DESCRIPTION', 'y', 0, 1);
INSERT INTO `contrexx_modules` VALUES (13, 'ids', 'TXT_IDS_MODULE_DESCRIPTION', 'y', 1, 1);
INSERT INTO `contrexx_modules` VALUES (14, 'error', 'TXT_ERROR_MODULE_DESCRIPTION', 'y', 1, 1);
INSERT INTO `contrexx_modules` VALUES (15, 'home', 'TXT_HOME_MODULE_DESCRIPTION', 'y', 1, 1);
INSERT INTO `contrexx_modules` VALUES (16, 'shop', 'TXT_SHOP_MODULE_DESCRIPTION', 'n', 0, 0);
INSERT INTO `contrexx_modules` VALUES (17, 'voting', 'TXT_VOTING_MODULE_DESCRIPTION', 'y', 0, 0);
INSERT INTO `contrexx_modules` VALUES (18, 'login', 'TXT_LOGIN_MODULE_DESCRIPTION', 'y', 1, 1);
INSERT INTO `contrexx_modules` VALUES (19, 'docsys', 'TXT_DOC_SYS_MODULE_DESCRIPTION', 'y', 0, 0);
INSERT INTO `contrexx_modules` VALUES (21, 'calendar', 'TXT_CALENDAR_MODULE_DESCRIPTION', 'y', 0, 0);
INSERT INTO `contrexx_modules` VALUES (22, 'feed', 'TXT_FEED_MODULE_DESCRIPTION', 'y', 0, 0);
INSERT INTO `contrexx_modules` VALUES (24, 'media2', 'TXT_MEDIA_MODULE_DESCRIPTION', 'y', 0, 1);
INSERT INTO `contrexx_modules` VALUES (25, 'media3', 'TXT_MEDIA_MODULE_DESCRIPTION', 'y', 0, 1);
INSERT INTO `contrexx_modules` VALUES (27, 'recommend', 'TXT_RECOMMEND_MODULE_DESCRIPTION', 'y', 0, 0);
INSERT INTO `contrexx_modules` VALUES (23, 'community', 'TXT_COMMUNITY_MODULE_DESCRIPTION', 'y', 0, 0);
INSERT INTO `contrexx_modules` VALUES (26, 'fileBrowser', 'TXT_FILEBROWSER_DESCRIPTION', 'n', 1, 1);
INSERT INTO `contrexx_modules` VALUES (28, 'banner', 'TXT_BANNER_MODULE_DESCRIPTION', 'n', 0, 1);
INSERT INTO `contrexx_modules` VALUES (7, 'block', 'TXT_BLOCK_MODULE_DESCRIPTION', 'y', 0, 0);
INSERT INTO `contrexx_modules` VALUES (30, 'livecam', 'TXT_LIVECAM_MODULE_DESCRIPTION', 'n', 0, 0);
INSERT INTO `contrexx_modules` VALUES (31, 'ticket', 'TXT_TICKET_MODULE_DESCRIPTION', 'n', 0, 0);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_sessions`
-- 

DROP TABLE IF EXISTS `contrexx_sessions`;
CREATE TABLE IF NOT EXISTS `contrexx_sessions` (
  `sessionid` varchar(255) NOT NULL default '',
  `startdate` varchar(14) NOT NULL default '',
  `lastupdated` varchar(14) NOT NULL default '',
  `status` varchar(20) NOT NULL default '',
  `username` varchar(100) NOT NULL default '',
  `datavalue` text,
  PRIMARY KEY  (`sessionid`),
  KEY `LastUpdated` (`lastupdated`)
) TYPE=MyISAM;

-- 
-- Daten für Tabelle `contrexx_sessions`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_settings`
-- 

DROP TABLE IF EXISTS `contrexx_settings`;
CREATE TABLE IF NOT EXISTS `contrexx_settings` (
  `setid` smallint(6) NOT NULL auto_increment,
  `setname` varchar(250) NOT NULL default '',
  `setvalue` text NOT NULL,
  `setmodule` tinyint(2) NOT NULL default '0',
  PRIMARY KEY  (`setid`)
) TYPE=MyISAM AUTO_INCREMENT=48 ;

-- 
-- Daten für Tabelle `contrexx_settings`
-- 

INSERT INTO `contrexx_settings` VALUES (3, 'dnsServer', 'dns1.exigo.ch', 0);
INSERT INTO `contrexx_settings` VALUES (4, 'bannerStatus', '0', 28);
INSERT INTO `contrexx_settings` VALUES (11, 'coreAdminName', 'System Administrator', 1);
INSERT INTO `contrexx_settings` VALUES (18, 'corePagingLimit', '30', 1);
INSERT INTO `contrexx_settings` VALUES (19, 'searchDescriptionLength', '150', 5);
INSERT INTO `contrexx_settings` VALUES (23, 'coreIdsStatus', 'on', 1);
INSERT INTO `contrexx_settings` VALUES (24, 'coreAdminEmail', 'support@contrexx.com', 1);
INSERT INTO `contrexx_settings` VALUES (29, 'contactFormEmail', 'support@contrexx.com', 6);
INSERT INTO `contrexx_settings` VALUES (34, 'sessionLifeTime', '3600', 1);
INSERT INTO `contrexx_settings` VALUES (35, 'lastAccessId', '31', 1);
INSERT INTO `contrexx_settings` VALUES (37, 'newsTeasersStatus', '1', 8);
INSERT INTO `contrexx_settings` VALUES (39, 'feedNewsMLStatus', '0', 22);
INSERT INTO `contrexx_settings` VALUES (40, 'calendarheadlines', '1', 21);
INSERT INTO `contrexx_settings` VALUES (41, 'calendarheadlinescount', '5', 21);
INSERT INTO `contrexx_settings` VALUES (42, 'blockStatus', '1', 7);
INSERT INTO `contrexx_settings` VALUES (43, 'contentHistoryStatus', 'on', 1);
INSERT INTO `contrexx_settings` VALUES (44, 'calendarheadlinescat', '9', 21);
INSERT INTO `contrexx_settings` VALUES (45, 'calendardefaultcount', '30', 21);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_skins`
-- 

DROP TABLE IF EXISTS `contrexx_skins`;
CREATE TABLE IF NOT EXISTS `contrexx_skins` (
  `id` tinyint(2) unsigned NOT NULL auto_increment,
  `themesname` varchar(50) NOT NULL default '',
  `foldername` varchar(50) NOT NULL default '',
  `expert` int(1) NOT NULL default '1',
  UNIQUE KEY `id` (`id`),
  KEY `id_2` (`id`)
) TYPE=MyISAM AUTO_INCREMENT=59 ;

-- 
-- Daten für Tabelle `contrexx_skins`
-- 

INSERT INTO `contrexx_skins` VALUES (17, 'newgen', 'newgen', 1);
INSERT INTO `contrexx_skins` VALUES (41, 'print', 'print', 1);
INSERT INTO `contrexx_skins` VALUES (51, 'kaelin_grey', 'kaelin_grey', 1);
INSERT INTO `contrexx_skins` VALUES (55, 'webstoff1', 'webstoff1', 1);
INSERT INTO `contrexx_skins` VALUES (56, 'webstoff2', 'webstoff2', 1);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_stats_browser`
-- 

DROP TABLE IF EXISTS `contrexx_stats_browser`;
CREATE TABLE IF NOT EXISTS `contrexx_stats_browser` (
  `id` smallint(6) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `contrexx_stats_browser`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_stats_colourdepth`
-- 

DROP TABLE IF EXISTS `contrexx_stats_colourdepth`;
CREATE TABLE IF NOT EXISTS `contrexx_stats_colourdepth` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `depth` tinyint(3) unsigned NOT NULL default '0',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `contrexx_stats_colourdepth`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_stats_config`
-- 

DROP TABLE IF EXISTS `contrexx_stats_config`;
CREATE TABLE IF NOT EXISTS `contrexx_stats_config` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(64) NOT NULL default '',
  `value` varchar(255) NOT NULL default '',
  `status` int(1) default '1',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=20 ;

-- 
-- Daten für Tabelle `contrexx_stats_config`
-- 

INSERT INTO `contrexx_stats_config` VALUES (1, 'reload_block_time', '86400', 1);
INSERT INTO `contrexx_stats_config` VALUES (2, 'online_timeout', '3000', 1);
INSERT INTO `contrexx_stats_config` VALUES (3, 'paging_limit', '100', 1);
INSERT INTO `contrexx_stats_config` VALUES (4, 'count_browser', '', 1);
INSERT INTO `contrexx_stats_config` VALUES (5, 'count_operating_system', '', 1);
INSERT INTO `contrexx_stats_config` VALUES (6, 'make_statistics', '', 1);
INSERT INTO `contrexx_stats_config` VALUES (7, 'count_spiders', '', 1);
INSERT INTO `contrexx_stats_config` VALUES (10, 'remove_requests', '86400', 0);
INSERT INTO `contrexx_stats_config` VALUES (9, 'count_requests', '', 0);
INSERT INTO `contrexx_stats_config` VALUES (11, 'count_search_terms', '', 1);
INSERT INTO `contrexx_stats_config` VALUES (12, 'count_screen_resolution', '', 1);
INSERT INTO `contrexx_stats_config` VALUES (13, 'count_colour_depth', '', 1);
INSERT INTO `contrexx_stats_config` VALUES (14, 'count_javascript', '', 1);
INSERT INTO `contrexx_stats_config` VALUES (15, 'count_referer', '', 1);
INSERT INTO `contrexx_stats_config` VALUES (16, 'count_hostname', '', 1);
INSERT INTO `contrexx_stats_config` VALUES (17, 'count_country', '', 1);
INSERT INTO `contrexx_stats_config` VALUES (18, 'paging_limit_visitor_details', '100', 1);
INSERT INTO `contrexx_stats_config` VALUES (19, 'count_visitor_number', '', 1);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_stats_country`
-- 

DROP TABLE IF EXISTS `contrexx_stats_country`;
CREATE TABLE IF NOT EXISTS `contrexx_stats_country` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `country` varchar(100) NOT NULL default '',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `contrexx_stats_country`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_stats_hostname`
-- 

DROP TABLE IF EXISTS `contrexx_stats_hostname`;
CREATE TABLE IF NOT EXISTS `contrexx_stats_hostname` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `hostname` varchar(255) NOT NULL default '',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `contrexx_stats_hostname`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_stats_javascript`
-- 

DROP TABLE IF EXISTS `contrexx_stats_javascript`;
CREATE TABLE IF NOT EXISTS `contrexx_stats_javascript` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `support` enum('0','1') default '0',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `contrexx_stats_javascript`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_stats_operatingsystem`
-- 

DROP TABLE IF EXISTS `contrexx_stats_operatingsystem`;
CREATE TABLE IF NOT EXISTS `contrexx_stats_operatingsystem` (
  `id` smallint(6) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `contrexx_stats_operatingsystem`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_stats_referer`
-- 

DROP TABLE IF EXISTS `contrexx_stats_referer`;
CREATE TABLE IF NOT EXISTS `contrexx_stats_referer` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `uri` varchar(255) NOT NULL default '',
  `timestamp` int(11) unsigned NOT NULL default '0',
  `count` mediumint(8) unsigned NOT NULL default '0',
  `sid` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `contrexx_stats_referer`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_stats_requests`
-- 

DROP TABLE IF EXISTS `contrexx_stats_requests`;
CREATE TABLE IF NOT EXISTS `contrexx_stats_requests` (
  `id` mediumint(9) unsigned NOT NULL auto_increment,
  `timestamp` int(11) default '0',
  `pageId` smallint(6) unsigned NOT NULL default '0',
  `page` varchar(255) default NULL,
  `visits` mediumint(9) unsigned NOT NULL default '0',
  `sid` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `contrexx_stats_requests`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_stats_requests_summary`
-- 

DROP TABLE IF EXISTS `contrexx_stats_requests_summary`;
CREATE TABLE IF NOT EXISTS `contrexx_stats_requests_summary` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `type` varchar(10) NOT NULL default '',
  `timestamp` int(11) NOT NULL default '0',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `contrexx_stats_requests_summary`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_stats_screenresolution`
-- 

DROP TABLE IF EXISTS `contrexx_stats_screenresolution`;
CREATE TABLE IF NOT EXISTS `contrexx_stats_screenresolution` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `resolution` varchar(11) NOT NULL default '',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `contrexx_stats_screenresolution`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_stats_search`
-- 

DROP TABLE IF EXISTS `contrexx_stats_search`;
CREATE TABLE IF NOT EXISTS `contrexx_stats_search` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `count` int(10) unsigned NOT NULL default '0',
  `sid` varchar(32) NOT NULL default '',
  `external` enum('0','1') NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `contrexx_stats_search`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_stats_spiders`
-- 

DROP TABLE IF EXISTS `contrexx_stats_spiders`;
CREATE TABLE IF NOT EXISTS `contrexx_stats_spiders` (
  `id` mediumint(9) unsigned NOT NULL auto_increment,
  `last_indexed` int(14) default NULL,
  `page` varchar(100) default NULL,
  `pageId` mediumint(6) unsigned NOT NULL default '0',
  `count` int(11) NOT NULL default '0',
  `spider_useragent` varchar(255) default NULL,
  `spider_ip` varchar(100) default NULL,
  `spider_host` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `contrexx_stats_spiders`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_stats_spiders_summary`
-- 

DROP TABLE IF EXISTS `contrexx_stats_spiders_summary`;
CREATE TABLE IF NOT EXISTS `contrexx_stats_spiders_summary` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `timestamp` int(11) NOT NULL default '0',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `contrexx_stats_spiders_summary`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_stats_visitors`
-- 

DROP TABLE IF EXISTS `contrexx_stats_visitors`;
CREATE TABLE IF NOT EXISTS `contrexx_stats_visitors` (
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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `contrexx_stats_visitors`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_stats_visitors_summary`
-- 

DROP TABLE IF EXISTS `contrexx_stats_visitors_summary`;
CREATE TABLE IF NOT EXISTS `contrexx_stats_visitors_summary` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `type` varchar(10) NOT NULL default '',
  `timestamp` int(11) NOT NULL default '0',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `contrexx_stats_visitors_summary`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_voting_results`
-- 

DROP TABLE IF EXISTS `contrexx_voting_results`;
CREATE TABLE IF NOT EXISTS `contrexx_voting_results` (
  `id` int(11) NOT NULL auto_increment,
  `voting_system_id` int(11) default NULL,
  `question` char(200) default NULL,
  `votes` int(11) default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=27 ;

-- 
-- Daten für Tabelle `contrexx_voting_results`
-- 

INSERT INTO `contrexx_voting_results` VALUES (20, 5, 'search.ch?', 0);
INSERT INTO `contrexx_voting_results` VALUES (19, 5, 'Yahoo?', 0);
INSERT INTO `contrexx_voting_results` VALUES (18, 5, 'InfoSeek?', 1);
INSERT INTO `contrexx_voting_results` VALUES (17, 5, 'Altavista?', 4);
INSERT INTO `contrexx_voting_results` VALUES (15, 5, 'Google?', 14);
INSERT INTO `contrexx_voting_results` VALUES (16, 5, 'AlltheWeb?', 1);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `contrexx_voting_system`
-- 

DROP TABLE IF EXISTS `contrexx_voting_system`;
CREATE TABLE IF NOT EXISTS `contrexx_voting_system` (
  `id` int(11) NOT NULL auto_increment,
  `date` timestamp(14) NOT NULL,
  `title` varchar(60) NOT NULL default '',
  `question` text,
  `status` tinyint(1) default '1',
  `votes` int(11) default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=10 ;

-- 
-- Daten für Tabelle `contrexx_voting_system`
-- 

INSERT INTO `contrexx_voting_system` VALUES (5, '20040408110748', 'Umfrage Google', 'Welche Suchmaschine hat die grösste Zukunft?', 1, 20);
        
