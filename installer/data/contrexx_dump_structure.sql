--
-- Contrexx WCMS SQL Dump
--
-- version: 2.0.0
-- edition: Premium
-- created: 09.05.2008 00:00
--
-- http://www.contrexx.com
--
CREATE TABLE `contrexx_access_group_dynamic_ids` (
  `access_id` int(11) unsigned NOT NULL default '0',
  `group_id` int(11) unsigned NOT NULL default '0'
) TYPE=MyISAM;

CREATE TABLE `contrexx_access_group_static_ids` (
  `access_id` int(11) unsigned NOT NULL default '0',
  `group_id` int(11) unsigned NOT NULL default '0'
) TYPE=MyISAM;

CREATE TABLE `contrexx_access_rel_user_group` (
  `user_id` int(10) unsigned NOT NULL default '0',
  `group_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`user_id`,`group_id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_access_settings` (
  `key` varchar(32) NOT NULL default '',
  `value` varchar(255) NOT NULL default '',
  `status` tinyint(1) unsigned NOT NULL default '0',
  UNIQUE KEY `key` (`key`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_access_users` (
  `id` int(5) unsigned NOT NULL auto_increment,
  `is_admin` tinyint(1) unsigned NOT NULL default '0',
  `username` varchar(40) default NULL,
  `password` varchar(32) default NULL,
  `regdate` int(14) unsigned NOT NULL default '0',
  `expiration` int(14) unsigned NOT NULL default '0',
  `validity` int(10) unsigned NOT NULL default '0',
  `last_auth` int(14) unsigned NOT NULL default '0',
  `last_activity` int(14) unsigned NOT NULL default '0',
  `email` varchar(255) default NULL,
  `email_access` enum('everyone','members_only','nobody') NOT NULL default 'nobody',
  `frontend_lang_id` int(2) unsigned NOT NULL default '0',
  `backend_lang_id` int(2) unsigned NOT NULL default '0',
  `active` tinyint(1) NOT NULL default '0',
  `profile_access` enum('everyone','members_only','nobody') NOT NULL default 'members_only',
  `restore_key` varchar(32) NOT NULL default '',
  `restore_key_time` int(14) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `username` (`username`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_access_user_attribute` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `parent_id` int(10) unsigned NOT NULL default '0',
  `type` enum('text','textarea','mail','uri','date','image','menu','menu_option','group','frame','history') NOT NULL default 'text',
  `mandatory` enum('0','1') NOT NULL default '0',
  `sort_type` enum('asc','desc','custom') NOT NULL default 'asc',
  `order_id` int(10) unsigned NOT NULL default '0',
  `access_special` enum('','menu_select_higher','menu_select_lower') NOT NULL default '',
  `access_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_access_user_attribute_name` (
  `attribute_id` int(10) unsigned NOT NULL default '0',
  `lang_id` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`attribute_id`,`lang_id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_access_user_attribute_value` (
  `attribute_id` int(10) unsigned NOT NULL default '0',
  `user_id` int(10) unsigned NOT NULL default '0',
  `history_id` int(10) unsigned NOT NULL default '0',
  `value` text NOT NULL,
  PRIMARY KEY  (`attribute_id`,`user_id`,`history_id`),
  FULLTEXT KEY `value` (`value`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_access_user_core_attribute` (
  `id` varchar(25) NOT NULL,
  `mandatory` enum('0','1') NOT NULL default '0',
  `sort_type` enum('asc','desc','custom') NOT NULL default 'asc',
  `order_id` int(10) unsigned NOT NULL default '0',
  `access_special` enum('','menu_select_higher','menu_select_lower') NOT NULL default '',
  `access_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_access_user_groups` (
  `group_id` int(6) unsigned NOT NULL auto_increment,
  `group_name` varchar(100) NOT NULL default '',
  `group_description` varchar(255) NOT NULL default '',
  `is_active` tinyint(4) NOT NULL default '1',
  `type` enum('frontend','backend') NOT NULL default 'frontend',
  PRIMARY KEY  (`group_id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_access_user_mail` (
  `type` enum('reg_confirm','reset_pw','user_activated','user_deactivated','new_user') NOT NULL,
  `lang_id` tinyint(2) unsigned NOT NULL default '0',
  `sender_mail` varchar(255) NOT NULL default '',
  `sender_name` varchar(255) NOT NULL default '',
  `subject` varchar(255) NOT NULL default '',
  `format` enum('text','html','multipart') NOT NULL default 'text',
  `body_text` text NOT NULL,
  `body_html` text NOT NULL,
  UNIQUE KEY `mail` (`type`,`lang_id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_access_user_profile` (
  `user_id` int(10) unsigned NOT NULL,
  `gender` enum('gender_undefined','gender_female','gender_male') NOT NULL default 'gender_undefined',
  `title` int(10) unsigned NOT NULL default '0',
  `firstname` varchar(255) NOT NULL default '',
  `lastname` varchar(255) NOT NULL default '',
  `company` varchar(255) NOT NULL default '',
  `address` varchar(255) NOT NULL default '',
  `city` varchar(50) NOT NULL default '',
  `zip` varchar(10) NOT NULL default '',
  `country` smallint(5) unsigned NOT NULL default '0',
  `phone_office` varchar(20) NOT NULL default '',
  `phone_private` varchar(20) NOT NULL default '',
  `phone_mobile` varchar(20) NOT NULL default '',
  `phone_fax` varchar(20) NOT NULL default '',
  `birthday` varchar(10) default '',
  `website` varchar(255) NOT NULL default '',
  `profession` varchar(150) NOT NULL default '',
  `interests` varchar(255) NOT NULL default '',
  `signature` varchar(255) NOT NULL default '',
  `picture` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`user_id`),
  KEY `profile` (`firstname`(100),`lastname`(100),`company`(50))
) TYPE=MyISAM;

CREATE TABLE `contrexx_access_user_title` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `order_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `title` (`title`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_access_user_validity` (
  `validity` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`validity`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_backend_areas` (
  `area_id` int(6) unsigned NOT NULL auto_increment,
  `parent_area_id` int(6) unsigned NOT NULL default '0',
  `type` enum('group','function','navigation') default 'navigation',
  `scope` enum('global','frontend','backend') NOT NULL default 'global',
  `area_name` varchar(100) default NULL,
  `is_active` tinyint(4) NOT NULL default '1',
  `uri` varchar(255) NOT NULL default '',
  `target` varchar(50) NOT NULL default '_self',
  `module_id` int(6) unsigned NOT NULL default '0',
  `order_id` int(6) unsigned NOT NULL default '0',
  `access_id` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`area_id`),
  KEY `area_name` (`area_name`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_backups` (
  `id` int(5) unsigned NOT NULL auto_increment,
  `date` varchar(14) NOT NULL default '',
  `version` varchar(20) NOT NULL default '',
  `edition` varchar(30) NOT NULL default '',
  `type` enum('sql','csv') NOT NULL default 'sql',
  `description` varchar(100) NOT NULL default '',
  `usedtables` text NOT NULL,
  `size` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `date` (`date`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_community_config` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(64) NOT NULL default '',
  `value` varchar(255) NOT NULL default '',
  `status` int(1) default '1',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_content` (
  `id` int(6) unsigned NOT NULL default '0',
  `content` mediumtext NOT NULL,
  `title` varchar(250) NOT NULL default '',
  `metatitle` varchar(250) NOT NULL default '',
  `metadesc` varchar(250) NOT NULL default '',
  `metakeys` text NOT NULL,
  `metarobots` varchar(7) NOT NULL default 'index',
  `css_name` varchar(50) NOT NULL default '',
  `redirect` varchar(255) NOT NULL default '',
  `expertmode` set('y','n') NOT NULL default 'n',
  UNIQUE KEY `contentid` (`id`),
  FULLTEXT KEY `fulltextindex` (`title`,`content`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_content_history` (
  `id` int(8) unsigned NOT NULL default '0',
  `page_id` int(7) unsigned NOT NULL default '0',
  `content` mediumtext NOT NULL,
  `title` varchar(250) NOT NULL default '',
  `metatitle` varchar(250) NOT NULL default '',
  `metadesc` varchar(250) NOT NULL default '',
  `metakeys` text NOT NULL,
  `metarobots` varchar(7) NOT NULL default 'index',
  `css_name` varchar(50) NOT NULL default '',
  `redirect` varchar(255) NOT NULL default '',
  `expertmode` set('y','n') NOT NULL default 'n',
  PRIMARY KEY  (`id`),
  KEY `page_id` (`page_id`),
  FULLTEXT KEY `fulltextindex` (`title`,`content`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_content_logfile` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `action` set('new','update','delete') NOT NULL default 'new',
  `history_id` int(10) unsigned NOT NULL default '0',
  `is_validated` set('0','1') NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `history_id` (`history_id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_content_navigation` (
  `catid` int(6) unsigned NOT NULL auto_increment,
  `is_validated` set('0','1') NOT NULL default '1',
  `parcat` int(6) unsigned NOT NULL default '0',
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
  `css_name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`catid`),
  KEY `parcat` (`parcat`),
  KEY `module` (`module`),
  KEY `catname` (`catname`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_content_navigation_history` (
  `id` int(7) unsigned NOT NULL auto_increment,
  `is_active` set('0','1') NOT NULL default '0',
  `catid` int(6) unsigned NOT NULL default '0',
  `parcat` int(6) unsigned NOT NULL default '0',
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
  `css_name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `catid` (`catid`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_ids` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `timestamp` int(14) default NULL,
  `type` varchar(100) NOT NULL default '',
  `remote_addr` varchar(15) default NULL,
  `http_x_forwarded_for` varchar(15) NOT NULL default '',
  `http_via` varchar(255) NOT NULL default '',
  `user` mediumtext ,
  `gpcs` mediumtext NOT NULL,
  `file` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_languages` (
  `id` int(2) unsigned NOT NULL auto_increment,
  `lang` varchar(5) NOT NULL default '',
  `name` varchar(250) NOT NULL default '',
  `charset` varchar(20) NOT NULL default 'iso-8859-1',
  `themesid` int(2) unsigned NOT NULL default '1',
  `print_themes_id` int(2) unsigned NOT NULL default '1',
  `pdf_themes_id` int(2) unsigned NOT NULL default '0',
  `frontend` tinyint(1) unsigned NOT NULL default '0',
  `backend` tinyint(1) unsigned NOT NULL default '0',
  `is_default` set('true','false') NOT NULL default 'false',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `lang` (`lang`),
  KEY `defaultstatus` (`is_default`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_lib_country` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(64) NOT NULL,
  `iso_code_2` char(2) NOT NULL,
  `iso_code_3` char(3) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique` (`iso_code_2`),
  KEY `INDEX_COUNTRIES_NAME` (`name`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_log` (
  `id` int(6) unsigned NOT NULL auto_increment,
  `userid` int(6) unsigned default NULL,
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
) TYPE=MyISAM;

CREATE TABLE `contrexx_modules` (
  `id` int(2) unsigned default NULL,
  `name` varchar(250) NOT NULL default '',
  `description_variable` varchar(50) NOT NULL default '',
  `status` set('y','n') NOT NULL default 'n',
  `is_required` tinyint(1) NOT NULL default '0',
  `is_core` tinyint(4) NOT NULL default '0',
  UNIQUE KEY `id` (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_alias_source` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `target_id` int(10) unsigned NOT NULL,
  `url` varchar(255) NOT NULL,
  `isdefault` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `url` (`url`),
  KEY `isdefault` (`isdefault`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_alias_target` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `type` enum('url','local') NOT NULL,
  `url` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `url` (`url`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_block_blocks` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `content` text NOT NULL,
  `name` varchar(255) NOT NULL default '',
  `random` int(1) NOT NULL default '0',
  `random_2` int(1) NOT NULL default '0',
  `random_3` int(1) NOT NULL default '0',
  `global` int(1) NOT NULL default '0',
  `active` int(1) NOT NULL default '0',
  `order` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_block_rel_lang` (
  `block_id` int(10) unsigned NOT NULL default '0',
  `lang_id` int(10) unsigned NOT NULL default '0',
  `all_pages` int(1) NOT NULL default '0'
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_block_rel_pages` (
  `block_id` int(7) NOT NULL default '0',
  `page_id` int(7) NOT NULL default '0',
  `lang_id` int(7) NOT NULL default '0'
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_block_settings` (
  `id` int(7) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `value` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_blog_categories` (
  `category_id` int(4) unsigned NOT NULL,
  `lang_id` int(2) unsigned NOT NULL,
  `is_active` enum('0','1') NOT NULL default '1',
  `name` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`category_id`,`lang_id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_blog_comments` (
  `comment_id` int(7) unsigned NOT NULL auto_increment,
  `message_id` int(6) unsigned NOT NULL,
  `lang_id` int(2) unsigned NOT NULL,
  `is_active` enum('0','1') NOT NULL default '1',
  `time_created` int(14) unsigned NOT NULL default '0',
  `ip_address` varchar(15) NOT NULL default '0.0.0.0',
  `user_id` int(5) unsigned NOT NULL default '0',
  `user_name` varchar(50) default NULL,
  `user_mail` varchar(250) default NULL,
  `user_www` varchar(255) default NULL,
  `subject` varchar(250) NOT NULL default '',
  `comment` text NOT NULL,
  PRIMARY KEY  (`comment_id`),
  KEY `message_id` (`message_id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_blog_messages` (
  `message_id` int(6) unsigned NOT NULL auto_increment,
  `user_id` int(5) unsigned NOT NULL,
  `time_created` int(14) unsigned NOT NULL default '0',
  `time_edited` int(14) unsigned NOT NULL default '0',
  `hits` int(7) unsigned NOT NULL default '0',
  PRIMARY KEY  (`message_id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_blog_messages_lang` (
  `message_id` int(6) unsigned NOT NULL,
  `lang_id` int(2) unsigned NOT NULL,
  `is_active` enum('0','1') NOT NULL default '1',
  `subject` varchar(250) NOT NULL default '',
  `content` text NOT NULL,
  `tags` varchar(250) NOT NULL default '',
  `image` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`message_id`,`lang_id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_blog_message_to_category` (
  `message_id` int(6) unsigned NOT NULL,
  `category_id` int(4) unsigned NOT NULL,
  `lang_id` int(2) unsigned NOT NULL,
  PRIMARY KEY  (`message_id`,`category_id`,`lang_id`),
  KEY `category_id` (`category_id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_blog_networks` (
  `network_id` int(8) unsigned NOT NULL auto_increment,
  `name` varchar(250) NOT NULL default '',
  `url` varchar(255) NOT NULL default '',
  `url_link` varchar(255) NOT NULL default '',
  `icon` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`network_id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_blog_networks_lang` (
  `network_id` int(8) unsigned NOT NULL,
  `lang_id` int(2) unsigned NOT NULL,
  PRIMARY KEY  (`network_id`,`lang_id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_blog_settings` (
  `name` varchar(50) NOT NULL,
  `value` varchar(250) NOT NULL,
  PRIMARY KEY  (`name`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_blog_votes` (
  `vote_id` int(8) unsigned NOT NULL auto_increment,
  `message_id` int(6) unsigned NOT NULL,
  `time_voted` int(14) unsigned NOT NULL default '0',
  `ip_address` varchar(15) NOT NULL default '0.0.0.0',
  `vote` enum('1','2','3','4','5','6','7','8','9','10') NOT NULL default '1',
  PRIMARY KEY  (`vote_id`),
  KEY `message_id` (`message_id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_calendar` (
  `id` int(11) NOT NULL auto_increment,
  `active` tinyint(1) NOT NULL default '1',
  `catid` int(11) NOT NULL default '0',
  `startdate` int(14) default NULL,
  `enddate` int(14) default NULL,
  `priority` int(1) NOT NULL default '3',
  `access` int(1) NOT NULL default '0',
  `name` varchar(100) NOT NULL default '',
  `comment` text NOT NULL,
  `placeName` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL default 'http://',
  `pic` varchar(255) NOT NULL default '',
  `attachment` varchar(255) NOT NULL default '',
  `placeStreet` varchar(255) NOT NULL default '',
  `placeZip` varchar(255) NOT NULL default '',
  `placeCity` varchar(255) NOT NULL default '',
  `placeLink` varchar(255) NOT NULL default '',
  `placeMap` varchar(255) NOT NULL default '',
  `organizerName` varchar(255) NOT NULL default '',
  `organizerStreet` varchar(255) NOT NULL default '',
  `organizerZip` varchar(255) NOT NULL default '',
  `organizerPlace` varchar(255) NOT NULL default '',
  `organizerMail` varchar(255) NOT NULL default '',
  `organizerLink` varchar(255) NOT NULL default '',
  `key` varchar(255) NOT NULL default '',
  `num` int(5) NOT NULL default '0',
  `mailTitle` varchar(255) NOT NULL default '',
  `mailContent` text NOT NULL,
  `registration` int(1) NOT NULL default '0',
  `groups` text NOT NULL,
  `all_groups` int(1) NOT NULL default '0',
  `public` int(1) NOT NULL default '0',
  `notification` int(1) NOT NULL,
  `notification_address` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  FULLTEXT KEY `name` (`name`,`comment`,`placeName`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_calendar_access` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(64) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `access_id` int(11) unsigned NOT NULL default '0',
  `type` enum('global','frontend','backend') NOT NULL default 'global',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_calendar_categories` (
  `id` int(5) NOT NULL auto_increment,
  `name` varchar(150) NOT NULL default '',
  `status` int(1) NOT NULL default '0',
  `lang` int(1) NOT NULL default '0',
  `pos` int(5) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_calendar_form_data` (
  `reg_id` int(10) NOT NULL,
  `field_id` int(10) NOT NULL,
  `data` text NOT NULL
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_calendar_form_fields` (
  `id` int(7) NOT NULL auto_increment,
  `note_id` int(10) NOT NULL,
  `name` text NOT NULL,
  `type` int(1) NOT NULL,
  `required` int(1) NOT NULL,
  `order` int(3) NOT NULL,
  `key` int(7) NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_calendar_registrations` (
  `id` int(7) NOT NULL auto_increment,
  `note_id` int(7) NOT NULL,
  `time` int(14) NOT NULL,
  `host` varchar(255) NOT NULL,
  `ip_address` varchar(15) NOT NULL,
  `type` int(1) NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_calendar_settings` (
  `setid` int(7) NOT NULL auto_increment,
  `setname` varchar(255) NOT NULL,
  `setvalue` text NOT NULL,
  PRIMARY KEY  (`setid`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_calendar_style` (
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
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_contact_form` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `mails` text NOT NULL,
  `subject` varchar(255) NOT NULL default '',
  `text` text NOT NULL,
  `feedback` text NOT NULL,
  `showForm` tinyint(1) unsigned NOT NULL default '0',
  `use_captcha` tinyint(1) unsigned NOT NULL default '1',
  `use_custom_style` tinyint(1) unsigned NOT NULL default '0',
  `langId` tinyint(2) unsigned NOT NULL default '1',
  `send_copy` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_contact_form_data` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_form` int(10) unsigned NOT NULL default '0',
  `time` int(14) unsigned NOT NULL default '0',
  `host` varchar(255) NOT NULL default '',
  `lang` varchar(64) NOT NULL default '',
  `browser` varchar(255) NOT NULL default '',
  `ipaddress` varchar(15) NOT NULL default '',
  `data` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_contact_form_field` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_form` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `type` enum('text','label','checkbox','checkboxGroup','date','file','hidden','password','radio','select','textarea') NOT NULL default 'text',
  `attributes` text NOT NULL,
  `is_required` set('0','1') NOT NULL default '0',
  `check_type` int(3) NOT NULL default '1',
  `order_id` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_contact_settings` (
  `setid` int(6) unsigned NOT NULL auto_increment,
  `setname` varchar(250) NOT NULL default '',
  `setvalue` text NOT NULL,
  `status` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`setid`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_directory_access` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(64) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `access_id` int(11) unsigned NOT NULL default '0',
  `type` enum('global','frontend','backend') NOT NULL default 'global',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_directory_categories` (
  `id` int(6) unsigned NOT NULL auto_increment,
  `parentid` int(6) unsigned NOT NULL default '0',
  `name` varchar(100) NOT NULL default '',
  `description` varchar(250) NOT NULL default '',
  `displayorder` smallint(6) unsigned NOT NULL default '1000',
  `metadesc` varchar(250) NOT NULL default '',
  `metakeys` varchar(250) NOT NULL default '',
  `showentries` int(1) NOT NULL default '1',
  `status` int(1) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `name` (`name`),
  KEY `parentid` (`parentid`),
  KEY `displayorder` (`displayorder`),
  KEY `status` (`status`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_directory_dir` (
  `id` int(7) unsigned NOT NULL auto_increment,
  `title` varchar(100) NOT NULL default '',
  `attachment` varchar(255) NOT NULL default '',
  `rss_file` varchar(255) NOT NULL default '',
  `rss_link` varchar(255) NOT NULL default '',
  `link` varchar(255) NOT NULL default '',
  `date` varchar(14) default NULL,
  `description` mediumtext NOT NULL,
  `platform` varchar(40) NOT NULL default '',
  `language` varchar(40) NOT NULL default '',
  `relatedlinks` varchar(255) NOT NULL default '',
  `hits` int(9) NOT NULL default '0',
  `status` tinyint(1) NOT NULL default '0',
  `addedby` varchar(50) NOT NULL default '',
  `provider` varchar(255) NOT NULL default '',
  `ip` varchar(255) NOT NULL default '',
  `validatedate` varchar(14) NOT NULL default '',
  `lastip` varchar(50) NOT NULL default '',
  `popular_date` varchar(30) NOT NULL default '',
  `popular_hits` int(7) NOT NULL default '0',
  `xml_refresh` varchar(15) NOT NULL default '',
  `canton` varchar(50) NOT NULL default '',
  `searchkeys` varchar(255) NOT NULL default '',
  `company_name` varchar(100) NOT NULL default '',
  `street` varchar(255) NOT NULL default '',
  `zip` varchar(5) NOT NULL default '',
  `city` varchar(50) NOT NULL default '',
  `country` varchar(255) NOT NULL default '',
  `phone` varchar(20) NOT NULL default '',
  `contact` varchar(100) NOT NULL default '',
  `information` varchar(100) NOT NULL default '',
  `fax` varchar(20) NOT NULL default '',
  `mobile` varchar(20) NOT NULL default '',
  `mail` varchar(50) NOT NULL default '',
  `homepage` varchar(50) NOT NULL default '',
  `industry` varchar(100) NOT NULL default '',
  `legalform` varchar(50) NOT NULL default '',
  `conversion` varchar(50) NOT NULL default '',
  `employee` varchar(255) NOT NULL default '',
  `foundation` varchar(10) NOT NULL default '',
  `mwst` varchar(50) NOT NULL default '',
  `opening` varchar(255) NOT NULL default '',
  `holidays` varchar(255) NOT NULL default '',
  `places` varchar(255) NOT NULL default '',
  `logo` varchar(50) NOT NULL default '',
  `team` varchar(255) NOT NULL default '',
  `portfolio` varchar(255) NOT NULL default '',
  `offers` varchar(255) NOT NULL default '',
  `concept` varchar(255) NOT NULL default '',
  `map` varchar(255) NOT NULL default '',
  `lokal` varchar(255) NOT NULL default '',
  `spezial` int(4) NOT NULL default '0',
  `premium` int(1) NOT NULL default '0',
  `longitude` decimal(18,15) NOT NULL default '0.000000000000000',
  `latitude` decimal(18,15) NOT NULL default '0.000000000000000',
  `zoom` decimal(18,15) NOT NULL default '1.000000000000000',
  `spez_field_1` varchar(255) NOT NULL default '',
  `spez_field_2` varchar(255) NOT NULL default '',
  `spez_field_3` varchar(255) NOT NULL default '',
  `spez_field_4` varchar(255) NOT NULL default '',
  `spez_field_5` varchar(255) NOT NULL default '',
  `spez_field_6` mediumtext NOT NULL,
  `spez_field_7` mediumtext NOT NULL,
  `spez_field_8` mediumtext NOT NULL,
  `spez_field_9` mediumtext NOT NULL,
  `spez_field_10` mediumtext NOT NULL,
  `spez_field_11` varchar(255) NOT NULL default '',
  `spez_field_12` varchar(255) NOT NULL default '',
  `spez_field_13` varchar(255) NOT NULL default '',
  `spez_field_14` varchar(255) NOT NULL default '',
  `spez_field_15` varchar(255) NOT NULL default '',
  `spez_field_21` varchar(255) NOT NULL default '',
  `spez_field_22` varchar(255) NOT NULL default '',
  `spez_field_16` varchar(255) NOT NULL default '',
  `spez_field_17` varchar(255) NOT NULL default '',
  `spez_field_18` varchar(255) NOT NULL default '',
  `spez_field_19` varchar(255) NOT NULL default '',
  `spez_field_20` varchar(255) NOT NULL default '',
  `spez_field_23` varchar(255) NOT NULL default '',
  `spez_field_24` varchar(255) NOT NULL default '',
  `spez_field_25` varchar(255) NOT NULL default '',
  `spez_field_26` varchar(255) NOT NULL default '',
  `spez_field_27` varchar(255) NOT NULL default '',
  `spez_field_28` varchar(255) NOT NULL default '',
  `spez_field_29` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `date` (`date`),
  KEY `temphitsout` (`hits`),
  KEY `status` (`status`),
  FULLTEXT KEY `name` (`title`,`description`),
  FULLTEXT KEY `description` (`description`),
  FULLTEXT KEY `title` (`title`,`description`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_directory_inputfields` (
  `id` int(7) NOT NULL auto_increment,
  `typ` int(2) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `active` int(1) NOT NULL default '0',
  `active_backend` int(1) NOT NULL default '0',
  `is_required` int(11) NOT NULL default '0',
  `read_only` int(1) NOT NULL default '0',
  `sort` int(5) NOT NULL default '0',
  `exp_search` int(1) NOT NULL default '0',
  `is_search` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_directory_levels` (
  `id` int(7) NOT NULL auto_increment,
  `parentid` int(7) NOT NULL default '0',
  `name` varchar(100) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `metadesc` varchar(100) NOT NULL default '',
  `metakeys` varchar(100) NOT NULL default '',
  `displayorder` int(7) NOT NULL default '0',
  `showlevels` int(1) NOT NULL default '0',
  `showcategories` int(1) NOT NULL default '0',
  `onlyentries` int(1) NOT NULL default '0',
  `status` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `displayorder` (`displayorder`),
  KEY `parentid` (`parentid`),
  KEY `name` (`name`),
  KEY `status` (`status`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_directory_mail` (
  `id` tinyint(4) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `content` longtext NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_directory_rel_dir_cat` (
  `dir_id` int(7) NOT NULL default '0',
  `cat_id` int(7) NOT NULL default '0',
  PRIMARY KEY  (`dir_id`,`cat_id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_directory_rel_dir_level` (
  `dir_id` int(7) NOT NULL default '0',
  `level_id` int(7) NOT NULL default '0',
  PRIMARY KEY  (`dir_id`,`level_id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_directory_settings` (
  `setid` int(6) unsigned NOT NULL auto_increment,
  `setname` varchar(250) NOT NULL default '',
  `setvalue` text NOT NULL,
  `settyp` int(1) NOT NULL default '0',
  PRIMARY KEY  (`setid`),
  KEY `setname` (`setname`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_directory_settings_google` (
  `setid` int(6) unsigned NOT NULL auto_increment,
  `setname` varchar(250) NOT NULL default '',
  `setvalue` text NOT NULL,
  `settyp` int(1) NOT NULL default '0',
  PRIMARY KEY  (`setid`),
  KEY `setname` (`setname`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_directory_vote` (
  `id` int(7) NOT NULL auto_increment,
  `feed_id` int(7) NOT NULL default '0',
  `vote` int(2) NOT NULL default '0',
  `count` int(7) NOT NULL default '0',
  `client` varchar(255) NOT NULL default '',
  `time` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_docsys` (
  `id` int(6) unsigned NOT NULL auto_increment,
  `date` int(14) default NULL,
  `title` varchar(250) NOT NULL default '',
  `author` varchar(150) NOT NULL default '',
  `text` mediumtext NOT NULL,
  `source` varchar(250) NOT NULL default '',
  `url1` varchar(250) NOT NULL default '',
  `url2` varchar(250) NOT NULL default '',
  `catid` int(2) unsigned NOT NULL default '0',
  `lang` int(2) unsigned NOT NULL default '0',
  `userid` int(6) unsigned NOT NULL default '0',
  `startdate` date NOT NULL default '0000-00-00',
  `enddate` date NOT NULL default '0000-00-00',
  `status` tinyint(4) NOT NULL default '1',
  `changelog` int(14) NOT NULL default '0',
  KEY `ID` (`id`),
  FULLTEXT KEY `newsindex` (`title`,`text`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_docsys_categories` (
  `catid` int(2) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `lang` int(2) unsigned NOT NULL default '1',
  `sort_style` enum('alpha','date','date_alpha') NOT NULL default 'alpha',
  PRIMARY KEY  (`catid`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_downloads_cat_lang` (
  `category` int(11) NOT NULL default '0',
  `language` int(11) NOT NULL default '0',
  KEY `category` (`category`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_downloads_cat_locales` (
  `loc_id` int(11) NOT NULL auto_increment,
  `loc_lang` int(11) NOT NULL default '0',
  `loc_cat` int(11) NOT NULL default '0',
  `loc_name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `loc_desc` text collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`loc_id`)
) TYPE=MyISAM;


CREATE TABLE `contrexx_module_downloads_categories` (
  `category_id` int(11) NOT NULL auto_increment,
  `category_img` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `category_author` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `category_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `category_state` tinyint(1) NOT NULL default '0',
  `category_order` int(3) NOT NULL default '0',
  PRIMARY KEY  (`category_id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_downloads_files` (
  `file_id` int(11) NOT NULL auto_increment,
  `file_name` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `file_type` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `file_size` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `file_source` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `file_url` varchar(255) collate latin1_general_ci NOT NULL default '',
  `file_img` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `file_autor` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `file_access_id` int(11) NOT NULL default '0',
  `file_protected` tinyint(1) NOT NULL default '0',
  `file_license` varchar(255) collate latin1_general_ci NOT NULL default '',
  `file_version` varchar(255) collate latin1_general_ci NOT NULL default '',
  `file_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `file_state` tinyint(1) NOT NULL,
  `file_order` int(11) NOT NULL default '0',
  `file_views` int(11) NOT NULL default '0',
  `file_downloads` int(11) NOT NULL default '0',
  PRIMARY KEY  (`file_id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_downloads_files_lang` (
  `file` int(11) NOT NULL default '0',
  `language` int(11) NOT NULL default '0',
  KEY `file` (`file`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_downloads_files_locales` (
  `loc_id` int(11) NOT NULL auto_increment,
  `loc_lang` int(11) NOT NULL default '0',
  `loc_file` int(11) NOT NULL default '0',
  `loc_name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `loc_desc` text collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`loc_id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_downloads_files_stat` (
  `stat_id` int(11) NOT NULL auto_increment,
  `stat_file` int(11) NOT NULL default '0',
  `stat_user_id` int(11) NOT NULL default '0',
  `stat_views` int(11) NOT NULL default '0',
  `stat_downloads` int(11) NOT NULL default '0',
  PRIMARY KEY  (`stat_id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_downloads_rel_files_cat` (
  `rel_file` int(11) NOT NULL default '0',
  `rel_category` int(11) NOT NULL default '0',
  KEY `rel_file` (`rel_file`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_downloads_rel_files_files` (
  `rel_file` int(11) NOT NULL default '0',
  `rel_related` int(11) NOT NULL default '0',
  KEY `rel_file` (`rel_file`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_downloads_settings` (
  `setting_id` int(11) NOT NULL auto_increment,
  `setting_name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `setting_value` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  PRIMARY KEY  (`setting_id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_egov_configuration` (
  `name` varchar(255) NOT NULL default '',
  `value` text NOT NULL default '',
  UNIQUE KEY `name` (`name`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_egov_orders` (
  `order_id` int(11) unsigned NOT NULL auto_increment,
  `order_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `order_ip` varchar(255) NOT NULL default '',
  `order_product` int(11) unsigned NOT NULL default '0',
  `order_values` text NOT NULL,
  `order_state` tinyint(4) unsigned NOT NULL default '0',
  `order_quant` tinyint(4) unsigned NOT NULL default '1',
  PRIMARY KEY  (`order_id`),
  KEY `order_product` (`order_product`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_egov_product_calendar` (
  `calendar_id` int(11) unsigned NOT NULL auto_increment,
  `calendar_product` int(11) unsigned NOT NULL default '0',
  `calendar_order` int(11) unsigned NOT NULL default '0',
  `calendar_day` int(2) unsigned NOT NULL default '0',
  `calendar_month` int(2) unsigned zerofill NOT NULL default '00',
  `calendar_year` int(4)unsigned  NOT NULL default '0',
  `calendar_act` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`calendar_id`),
  KEY `calendar_product` (`calendar_product`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_egov_product_fields` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `product` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `type` enum('text','label','checkbox','checkboxGroup','file','hidden','password','radio','select','textarea') NOT NULL default 'text',
  `attributes` text NOT NULL,
  `is_required` set('0','1') NOT NULL default '0',
  `check_type` int(3) NOT NULL default '1',
  `order_id` int(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `product` (`product`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_egov_products` (
  `product_id` int(11) unsigned NOT NULL auto_increment,
  `product_autostatus` tinyint(1) unsigned NOT NULL default '0',
  `product_name` varchar(255) NOT NULL default '',
  `product_desc` text NOT NULL,
  `product_price` decimal(11,2) NOT NULL default '0.00',
  `product_per_day` enum('yes','no') NOT NULL default 'no',
  `product_quantity` tinyint(2) unsigned NOT NULL default '0',
  `product_quantity_limit` tinyint(2) unsigned NOT NULL default '1',
  `product_target_email` varchar(255) NOT NULL default '',
  `product_target_url` varchar(255) NOT NULL default '',
  `product_message` text NOT NULL,
  `product_status` tinyint(1) unsigned NOT NULL default '1',
  `product_electro` tinyint(1) unsigned NOT NULL default '0',
  `product_file` varchar(255) NOT NULL default '',
  `product_sender_name` varchar(255) NOT NULL default '',
  `product_sender_email` varchar(255) NOT NULL default '',
  `product_target_subject` varchar(255) NOT NULL,
  `product_target_body` text NOT NULL,
  `product_paypal` tinyint(1) NOT NULL default '0',
  `product_paypal_sandbox` varchar(255) NOT NULL default '',
  `product_paypal_currency` varchar(255) NOT NULL default '',
  `product_orderby` int(11) unsigned NOT NULL default '0',
  `yellowpay` tinyint(1) unsigned NOT NULL default '0',
  `alternative_names` TEXT NOT NULL default '',
  PRIMARY KEY  (`product_id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_feed_category` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(150) NOT NULL default '',
  `status` int(1) NOT NULL default '1',
  `time` int(100) NOT NULL default '0',
  `lang` int(1) NOT NULL default '0',
  `pos` int(3) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_feed_news` (
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
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_feed_newsml_association` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pId_master` text NOT NULL,
  `pId_slave` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_feed_newsml_categories` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `providerId` text NOT NULL,
  `name` varchar(40) NOT NULL default '',
  `subjectCodes` text NOT NULL,
  `showSubjectCodes` enum('all','only','exclude') NOT NULL default 'all',
  `template` text NOT NULL,
  `limit` smallint(6) NOT NULL default '0',
  `showPics` enum('0','1') NOT NULL default '1',
  `auto_update` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_feed_newsml_documents` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `publicIdentifier` varchar(255) NOT NULL default '',
  `providerId` text NOT NULL,
  `dateId` int(8) unsigned NOT NULL default '0',
  `newsItemId` text NOT NULL,
  `revisionId` int(5) unsigned NOT NULL default '0',
  `thisRevisionDate` int(14) NOT NULL default '0',
  `urgency` smallint(5) unsigned NOT NULL default '0',
  `subjectCode` int(10) unsigned NOT NULL default '0',
  `headLine` varchar(67) NOT NULL default '',
  `dataContent` text NOT NULL,
  `is_associated` tinyint(1) unsigned NOT NULL default '0',
  `media_type` enum('Text','Graphic','Photo','Audio','Video','ComplexData') NOT NULL default 'Text',
  `source` text NOT NULL,
  `properties` text NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique` (`publicIdentifier`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_feed_newsml_providers` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `providerId` text NOT NULL,
  `name` varchar(40) NOT NULL default '',
  `path` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_forum_access` (
  `category_id` int(5) unsigned NOT NULL default '0',
  `group_id` int(5) unsigned NOT NULL default '0',
  `read` set('0','1') NOT NULL default '0',
  `write` set('0','1') NOT NULL default '0',
  `edit` set('0','1') NOT NULL default '0',
  `delete` set('0','1') NOT NULL default '0',
  `move` set('0','1') NOT NULL default '0',
  `close` set('0','1') NOT NULL default '0',
  `sticky` set('0','1') NOT NULL default '0',
  PRIMARY KEY  (`category_id`,`group_id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_forum_categories` (
  `id` int(5) unsigned NOT NULL auto_increment,
  `parent_id` int(5) unsigned NOT NULL default '0',
  `order_id` int(5) unsigned NOT NULL default '0',
  `status` set('0','1') NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `parent_id` (`parent_id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_forum_categories_lang` (
  `category_id` int(5) unsigned NOT NULL default '0',
  `lang_id` int(5) unsigned NOT NULL default '0',
  `name` varchar(100) NOT NULL default '',
  `description` text NOT NULL,
  PRIMARY KEY  (`category_id`,`lang_id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_forum_notification` (
  `category_id` int(10) unsigned NOT NULL default '0',
  `thread_id` int(10) unsigned NOT NULL default '0',
  `user_id` int(5) unsigned NOT NULL default '0',
  `is_notified` set('0','1') NOT NULL default '0',
  PRIMARY KEY  (`category_id`,`thread_id`,`user_id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_forum_postings` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `category_id` int(5) unsigned NOT NULL default '0',
  `thread_id` int(10) unsigned NOT NULL default '0',
  `prev_post_id` int(10) unsigned NOT NULL default '0',
  `user_id` int(5) unsigned NOT NULL default '0',
  `time_created` int(14) unsigned NOT NULL default '0',
  `time_edited` int(14) unsigned NOT NULL default '0',
  `is_locked` set('0','1') NOT NULL default '0',
  `is_sticky` set('0','1') NOT NULL default '0',
  `rating` int(11) NOT NULL default '0',
  `views` int(10) unsigned NOT NULL default '0',
  `icon` smallint(5) unsigned NOT NULL default '0',
  `keywords` text NOT NULL,
  `subject` varchar(250) NOT NULL default '',
  `content` text NOT NULL,
  `attachment` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `category_id` (`category_id`,`thread_id`,`prev_post_id`,`user_id`),
  FULLTEXT KEY `fulltext` (`keywords`,`subject`,`content`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_forum_rating` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`,`post_id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_forum_settings` (
  `id` int(5) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL default '',
  `value` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_forum_statistics` (
  `category_id` int(5) unsigned NOT NULL default '0',
  `thread_count` int(10) unsigned NOT NULL default '0',
  `post_count` int(10) unsigned NOT NULL default '0',
  `last_post_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`category_id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_gallery_categories` (
  `id` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `sorting` tinyint(3) NOT NULL default '0',
  `status` set('0','1') NOT NULL default '1',
  `comment` set('0','1') NOT NULL default '0',
  `voting` set('0','1') NOT NULL default '0',
  `backendProtected` int(11) NOT NULL,
  `backend_access_id` int(11) NOT NULL,
  `frontendProtected` int(11) NOT NULL,
  `frontend_access_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_gallery_comments` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `picid` int(10) unsigned NOT NULL default '0',
  `date` int(14) unsigned NOT NULL default '0',
  `ip` varchar(15) NOT NULL default '',
  `name` varchar(50) NOT NULL default '',
  `email` varchar(250) NOT NULL default '',
  `www` varchar(250) NOT NULL default '',
  `comment` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_gallery_language` (
  `gallery_id` int(10) unsigned NOT NULL default '0',
  `lang_id` int(10) unsigned NOT NULL default '0',
  `name` set('name','desc') NOT NULL default '',
  `value` text NOT NULL,
  PRIMARY KEY  (`gallery_id`,`lang_id`,`name`),
  FULLTEXT KEY `galleryindex` (`value`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_gallery_language_pics` (
  `picture_id` int(10) unsigned NOT NULL default '0',
  `lang_id` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `desc` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`picture_id`,`lang_id`),
  FULLTEXT KEY `galleryindex` (`name`,`desc`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_gallery_pictures` (
  `id` int(11) NOT NULL auto_increment,
  `catid` int(11) NOT NULL default '0',
  `validated` set('0','1') NOT NULL default '0',
  `status` set('0','1') NOT NULL default '1',
  `catimg` set('0','1') NOT NULL default '0',
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
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_gallery_settings` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(30) NOT NULL default '',
  `value` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_gallery_votes` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `picid` int(10) unsigned NOT NULL default '0',
  `date` int(14) unsigned NOT NULL default '0',
  `ip` varchar(15) NOT NULL default '',
  `md5` varchar(32) NOT NULL default '',
  `mark` int(2) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_guestbook` (
  `id` int(6) unsigned NOT NULL auto_increment,
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
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_guestbook_settings` (
  `name` varchar(50) NOT NULL default '',
  `value` varchar(250) NOT NULL default '',
  KEY `name` (`name`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_livecam` (
  `id` int(10) unsigned NOT NULL default '1',
  `currentImagePath` varchar(255) NOT NULL default '/webcam/cam1/current.jpg',
  `archivePath` varchar(255) NOT NULL default '/webcam/cam1/archive/',
  `thumbnailPath` varchar(255) NOT NULL default '/webcam/cam1/thumbs/',
  `maxImageWidth` int(10) unsigned NOT NULL default '400',
  `thumbMaxSize` int(10) unsigned NOT NULL default '200',
  `lightboxActivate` set('1','0') NOT NULL default '1',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_livecam_settings` (
  `setid` int(10) unsigned NOT NULL auto_increment,
  `setname` varchar(255) NOT NULL default '',
  `setvalue` text NOT NULL,
  PRIMARY KEY  (`setid`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_market` (
  `id` int(9) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `email` varchar(100) NOT NULL default '',
  `type` set('search','offer') NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `description` mediumtext NOT NULL,
  `premium` int(1) NOT NULL default '0',
  `picture` varchar(255) NOT NULL default '',
  `catid` int(4) NOT NULL default '0',
  `price` varchar(10) NOT NULL default '',
  `regdate` varchar(20) NOT NULL default '',
  `enddate` varchar(20) NOT NULL default '',
  `userid` int(4) NOT NULL default '0',
  `userdetails` int(1) NOT NULL default '0',
  `status` int(1) NOT NULL default '0',
  `regkey` varchar(50) NOT NULL default '',
  `paypal` int(1) NOT NULL default '0',
  `spez_field_1` varchar(255) NOT NULL,
  `spez_field_2` varchar(255) NOT NULL,
  `spez_field_3` varchar(255) NOT NULL,
  `spez_field_4` varchar(255) NOT NULL,
  `spez_field_5` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  FULLTEXT KEY `description` (`description`),
  FULLTEXT KEY `title` (`description`,`title`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_market_access` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(64) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `access_id` int(11) unsigned NOT NULL default '0',
  `type` enum('global','frontend','backend') NOT NULL default 'global',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_market_categories` (
  `id` int(6) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `displayorder` int(4) NOT NULL default '0',
  `status` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_market_mail` (
  `id` int(4) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `content` longtext NOT NULL,
  `mailto` varchar(10) NOT NULL,
  `mailcc` mediumtext NOT NULL,
  `active` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_market_paypal` (
  `id` int(4) NOT NULL auto_increment,
  `active` int(1) NOT NULL default '0',
  `profile` varchar(255) NOT NULL default '',
  `price` varchar(10) NOT NULL default '',
  `price_premium` varchar(10) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_market_settings` (
  `id` int(6) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `value` varchar(255) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `type` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_market_spez_fields` (
  `id` int(5) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `value` varchar(100) NOT NULL,
  `type` int(1) NOT NULL default '1',
  `lang_id` int(2) NOT NULL,
  `active` int(1) NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_memberdir_directories` (
  `dirid` int(10) unsigned NOT NULL auto_increment,
  `parentdir` int(11) NOT NULL default '0',
  `active` set('1','0') NOT NULL default '1',
  `name` varchar(255) NOT NULL default '',
  `description` text NOT NULL,
  `displaymode` set('0','1','2') NOT NULL default '0',
  `sort` int(11) NOT NULL default '1',
  `pic1` set('1','0') NOT NULL default '0',
  `pic2` set('1','0') NOT NULL default '0',
  `lang_id` int(2) unsigned NOT NULL default '1',
  PRIMARY KEY  (`dirid`),
  FULLTEXT KEY `memberdir_dir` (`name`,`description`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_memberdir_name` (
  `field` int(10) unsigned NOT NULL default '0',
  `dirid` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `active` set('0','1') NOT NULL default '',
  `lang_id` int(2) unsigned NOT NULL default '1'
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_memberdir_settings` (
  `setid` int(4) unsigned NOT NULL auto_increment,
  `setname` varchar(255) NOT NULL default '',
  `setvalue` text NOT NULL,
  `lang_id` int(2) unsigned NOT NULL default '1',
  PRIMARY KEY  (`setid`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_memberdir_values` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `dirid` int(14) NOT NULL default '0',
  `pic1` varchar(255) NOT NULL default '',
  `pic2` varchar(255) NOT NULL default '',
  `0` smallint(5) unsigned NOT NULL default '0',
  `1` text NOT NULL,
  `2` text NOT NULL,
  `3` text NOT NULL,
  `4` text NOT NULL,
  `5` text NOT NULL,
  `6` text NOT NULL,
  `7` text NOT NULL,
  `8` text NOT NULL,
  `9` text NOT NULL,
  `10` text NOT NULL,
  `11` text NOT NULL,
  `12` text NOT NULL,
  `13` text NOT NULL,
  `14` text NOT NULL,
  `15` text NOT NULL,
  `16` text NOT NULL,
  `17` text NOT NULL,
  `18` text NOT NULL,
  `lang_id` int(2) unsigned NOT NULL default '1',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_news` (
  `id` int(6) unsigned NOT NULL auto_increment,
  `date` int(14) default NULL,
  `title` varchar(250) NOT NULL default '',
  `text` mediumtext NOT NULL,
  `redirect` varchar(250) NOT NULL default '',
  `source` varchar(250) NOT NULL default '',
  `url1` varchar(250) NOT NULL default '',
  `url2` varchar(250) NOT NULL default '',
  `catid` int(2) unsigned NOT NULL default '0',
  `lang` int(2) unsigned NOT NULL default '0',
  `userid` int(6) unsigned NOT NULL default '0',
  `startdate` date NOT NULL default '0000-00-00',
  `enddate` date NOT NULL default '0000-00-00',
  `status` tinyint(4) NOT NULL default '1',
  `validated` enum('0','1') NOT NULL default '0',
  `teaser_only` enum('0','1') NOT NULL default '0',
  `teaser_frames` text NOT NULL,
  `teaser_text` text NOT NULL,
  `teaser_show_link` tinyint(1) unsigned NOT NULL default '1',
  `teaser_image_path` text NOT NULL,
  `changelog` int(14) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `ID` (`id`),
  FULLTEXT KEY `newsindex` (`text`,`title`,`teaser_text`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_newsletter` (
  `id` int(11) NOT NULL auto_increment,
  `subject` varchar(255) NOT NULL default '',
  `template` int(11) NOT NULL default '0',
  `content` text NOT NULL,
  `content_text` text NOT NULL,
  `attachment` enum('0','1') NOT NULL default '0',
  `format` enum('text','html','html/text') NOT NULL default 'text',
  `priority` tinyint(1) NOT NULL default '0',
  `sender_email` varchar(255) NOT NULL default '',
  `sender_name` varchar(255) NOT NULL default '',
  `return_path` varchar(255) NOT NULL default '',
  `smtp_server` int(10) unsigned NOT NULL,
  `status` int(1) NOT NULL default '0',
  `count` int(11) NOT NULL default '0',
  `date_create` int(14) unsigned NOT NULL default '0',
  `date_sent` int(14) unsigned NOT NULL default '0',
  `tmp_copy` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_newsletter_attachment` (
  `id` int(11) NOT NULL auto_increment,
  `newsletter` int(11) NOT NULL default '0',
  `file_name` varchar(255) NOT NULL default '',
  `file_nr` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `newsletter` (`newsletter`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_newsletter_category` (
  `id` int(11) NOT NULL auto_increment,
  `status` tinyint(1) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `name` (`name`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_newsletter_confirm_mail` (
  `id` int(1) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `content` longtext NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_newsletter_rel_cat_news` (
  `newsletter` int(11) NOT NULL default '0',
  `category` int(11) NOT NULL default '0',
  PRIMARY KEY  (`newsletter`,`category`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_newsletter_rel_user_cat` (
  `user` int(11) NOT NULL default '0',
  `category` int(11) NOT NULL default '0',
  PRIMARY KEY  (`user`,`category`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_newsletter_settings` (
  `setid` int(6) unsigned NOT NULL auto_increment,
  `setname` varchar(250) NOT NULL default '',
  `setvalue` text NOT NULL,
  `status` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`setid`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_newsletter_template` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `html` text NOT NULL,
  `text` text NOT NULL,
  `required` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_newsletter_tmp_sending` (
  `id` int(11) NOT NULL auto_increment,
  `newsletter` int(11) NOT NULL default '0',
  `email` varchar(255) NOT NULL default '',
  `sendt` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `email` (`email`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_newsletter_user` (
  `id` int(11) NOT NULL auto_increment,
  `code` varchar(255) NOT NULL default '',
  `email` varchar(255) NOT NULL default '',
  `sex` enum('m','f') default NULL,
  `title` int(10) unsigned NOT NULL,
  `lastname` varchar(255) NOT NULL default '',
  `firstname` varchar(255) NOT NULL default '',
  `company` varchar(255) NOT NULL default '',
  `street` varchar(255) NOT NULL default '',
  `zip` varchar(255) NOT NULL default '',
  `city` varchar(255) NOT NULL default '',
  `country` varchar(255) NOT NULL default '',
  `phone` varchar(255) NOT NULL default '',
  `birthday` varchar(10) NOT NULL default '00-00-0000',
  `status` int(1) NOT NULL default '0',
  `emaildate` int(14) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `status` (`status`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_newsletter_user_title` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `title` (`title`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_news_access` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(64) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `access_id` int(11) unsigned NOT NULL default '0',
  `type` enum('global','frontend','backend') NOT NULL default 'global',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_news_categories` (
  `catid` int(2) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `lang` int(2) unsigned NOT NULL default '1',
  PRIMARY KEY  (`catid`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_news_settings` (
  `name` varchar(50) NOT NULL default '',
  `value` varchar(250) NOT NULL default '',
  KEY `name` (`name`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_news_teaser_frame` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `lang_id` int(3) unsigned NOT NULL default '0',
  `frame_template_id` int(10) unsigned NOT NULL default '0',
  `name` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_news_teaser_frame_templates` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `description` varchar(100) NOT NULL default '',
  `html` text NOT NULL,
  `source_code_mode` enum('0','1') NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_news_ticker` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `charset` enum('ISO-8859-1','UTF-8') NOT NULL default 'ISO-8859-1',
  `urlencode` tinyint(1) unsigned NOT NULL default '0',
  `prefix` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_podcast_category` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `status` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  FULLTEXT KEY `podcastindex` (`title`,`description`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_podcast_medium` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `youtube_id` varchar(25) NOT NULL default '',
  `author` varchar(255) NOT NULL default '',
  `description` text NOT NULL,
  `source` text NOT NULL,
  `thumbnail` varchar(255) NOT NULL default '',
  `template_id` int(11) unsigned NOT NULL default '0',
  `width` int(10) unsigned NOT NULL default '0',
  `height` int(10) unsigned NOT NULL default '0',
  `playlenght` int(10) unsigned NOT NULL default '0',
  `size` int(10) unsigned NOT NULL default '0',
  `views` int(10) unsigned NOT NULL default '0',
  `status` tinyint(1) NOT NULL default '0',
  `date_added` int(14) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  FULLTEXT KEY `podcastindex` (`title`,`description`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_podcast_rel_category_lang` (
  `category_id` int(10) unsigned NOT NULL default '0',
  `lang_id` int(10) unsigned NOT NULL default '0'
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_podcast_rel_medium_category` (
  `medium_id` int(10) unsigned NOT NULL default '0',
  `category_id` int(10) unsigned NOT NULL default '0'
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_podcast_settings` (
  `setid` int(6) unsigned NOT NULL auto_increment,
  `setname` varchar(250) NOT NULL default '',
  `setvalue` text NOT NULL,
  `status` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`setid`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_podcast_template` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `description` varchar(255) NOT NULL default '',
  `template` text NOT NULL,
  `extensions` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `description` (`description`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_recommend` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `value` text NOT NULL,
  `lang_id` int(11) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_repository` (
  `id` int(6) unsigned NOT NULL auto_increment,
  `moduleid` int(5) unsigned NOT NULL default '0',
  `content` mediumtext NOT NULL,
  `title` varchar(250) NOT NULL default '',
  `cmd` varchar(20) NOT NULL default '',
  `expertmode` set('y','n') NOT NULL default 'n',
  `parid` int(5) unsigned NOT NULL default '0',
  `displaystatus` set('on','off') NOT NULL default 'on',
  `username` varchar(250) NOT NULL default '',
  `displayorder` smallint(6) NOT NULL default '100',
  `lang` varchar(5) NOT NULL default '',
  UNIQUE KEY `contentid` (`id`),
  FULLTEXT KEY `fulltextindex` (`title`,`content`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_shop_categories` (
  `catid` int(10) unsigned NOT NULL auto_increment,
  `parentid` int(10) unsigned NOT NULL default '0',
  `catname` varchar(255) NOT NULL default '',
  `catsorting` smallint(6) NOT NULL default '100',
  `catstatus` tinyint(1) NOT NULL default '1',
  `picture` varchar(255) NOT NULL default '',
  `flags` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`catid`),
  FULLTEXT KEY `flags` (`flags`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_shop_config` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(64) NOT NULL default '',
  `value` varchar(255) NOT NULL default '',
  `status` int(1) default '1',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_shop_countries` (
  `countries_id` int(11) NOT NULL auto_increment,
  `countries_name` varchar(64) NOT NULL default '',
  `countries_iso_code_2` char(2) NOT NULL default '',
  `countries_iso_code_3` char(3) NOT NULL default '',
  `activation_status` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`countries_id`),
  KEY `INDEX_COUNTRIES_NAME` (`countries_name`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_shop_currencies` (
  `id` int(11) NOT NULL auto_increment,
  `code` char(3) NOT NULL default '',
  `symbol` varchar(20) NOT NULL default '',
  `name` varchar(50) NOT NULL default '',
  `rate` decimal(10,6) NOT NULL default '1.000000',
  `sort_order` int(3) default NULL,
  `status` int(1) default '1',
  `is_default` tinyint(2) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_shop_customers` (
  `customerid` int(10) unsigned NOT NULL auto_increment,
  `username` varchar(255) NOT NULL default '',
  `password` varchar(32) NOT NULL default '',
  `prefix` varchar(50) NOT NULL default '',
  `company` varchar(100) NOT NULL default '',
  `firstname` varchar(50) NOT NULL default '',
  `lastname` varchar(100) NOT NULL default '',
  `address` varchar(40) NOT NULL default '',
  `city` varchar(20) NOT NULL default '',
  `zip` varchar(10) default NULL,
  `country_id` int(6) unsigned default NULL,
  `phone` varchar(20) NOT NULL default '',
  `fax` varchar(25) NOT NULL default '',
  `email` varchar(255) NOT NULL default '',
  `ccnumber` varchar(100) NOT NULL default '',
  `ccdate` varchar(10) NOT NULL default '',
  `ccname` varchar(100) NOT NULL default '',
  `cvc_code` varchar(5) NOT NULL default '',
  `company_note` text NOT NULL,
  `is_reseller` tinyint(3) NOT NULL default '0',
  `register_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `customer_status` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`customerid`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_shop_importimg` (
  `img_id` int(11) NOT NULL auto_increment,
  `img_name` varchar(255) NOT NULL default '',
  `img_cats` text NOT NULL,
  `img_fields_file` text NOT NULL,
  `img_fields_db` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`img_id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_shop_lsv` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `order_id` int(10) unsigned NOT NULL,
  `holder` tinytext NOT NULL,
  `bank` tinytext NOT NULL,
  `blz` tinytext NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `order_id` (`order_id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_shop_mail` (
  `id` int(4) unsigned NOT NULL auto_increment,
  `tplname` varchar(60) NOT NULL default '',
  `protected` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_shop_mail_content` (
  `id` int(4) unsigned NOT NULL auto_increment,
  `tpl_id` int(4) unsigned NOT NULL default '0',
  `lang_id` int(2) unsigned NOT NULL default '0',
  `from_mail` varchar(255) NOT NULL default '',
  `xsender` varchar(255) NOT NULL default '',
  `subject` varchar(255) NOT NULL default '',
  `message` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_shop_manufacturer` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `url` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_shop_orders` (
  `orderid` int(10) unsigned NOT NULL auto_increment,
  `customerid` int(10) unsigned NOT NULL default '0',
  `selected_currency_id` int(5) unsigned NOT NULL default '0',
  `order_sum` float(8,2) NOT NULL default '0.00',
  `currency_order_sum` float(8,2) NOT NULL default '0.00',
  `order_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `order_status` tinyint(1) unsigned NOT NULL default '0',
  `ship_prefix` varchar(50) NOT NULL default '',
  `ship_company` varchar(100) NOT NULL default '',
  `ship_firstname` varchar(40) NOT NULL default '',
  `ship_lastname` varchar(100) NOT NULL default '',
  `ship_address` varchar(40) NOT NULL default '',
  `ship_city` varchar(20) NOT NULL default '',
  `ship_zip` varchar(10) default NULL,
  `ship_country_id` int(6) unsigned default NULL,
  `ship_phone` varchar(20) NOT NULL default '',
  `tax_price` float(6,2) NOT NULL default '0.00',
  `currency_ship_price` float(6,2) NOT NULL default '0.00',
  `shipping_id` int(4) unsigned default NULL,
  `payment_id` int(3) unsigned default NULL,
  `currency_payment_price` float(6,2) NOT NULL default '0.00',
  `customer_ip` varchar(50) NOT NULL default '',
  `customer_host` varchar(100) NOT NULL default '',
  `customer_lang` varchar(255) NOT NULL default '',
  `customer_browser` varchar(100) NOT NULL default '',
  `customer_note` text NOT NULL,
  `last_modified` datetime NOT NULL default '0000-00-00 00:00:00',
  `modified_by` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`orderid`),
  KEY `order_status` (`order_status`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_shop_order_items` (
  `order_items_id` int(11) unsigned NOT NULL auto_increment,
  `orderid` int(10) unsigned NOT NULL default '0',
  `productid` varchar(13) NOT NULL default '',
  `product_name` varchar(100) NOT NULL default '',
  `price` float(8,2) NOT NULL default '0.00',
  `quantity` int(11) unsigned NOT NULL default '1',
  `vat_percent` decimal(5,2) unsigned default NULL,
  `weight` int(10) unsigned default NULL,
  PRIMARY KEY  (`order_items_id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_shop_order_items_attributes` (
  `orders_items_attributes_id` int(11) NOT NULL auto_increment,
  `order_items_id` int(10) unsigned NOT NULL default '0',
  `order_id` int(11) NOT NULL default '0',
  `product_id` int(11) NOT NULL default '0',
  `product_option_name` varchar(32) NOT NULL default '',
  `product_option_value` varchar(32) NOT NULL default '',
  `product_option_values_price` decimal(6,2) NOT NULL default '0.00',
  `price_prefix` enum('+','-') NOT NULL default '+',
  PRIMARY KEY  (`orders_items_attributes_id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_shop_payment` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(50) default NULL,
  `processor_id` int(3) NOT NULL default '0',
  `costs` decimal(8,2) NOT NULL default '0.00',
  `costs_free_sum` decimal(8,2) NOT NULL default '0.00',
  `sort_order` int(3) default '0',
  `status` int(1) default '1',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_shop_payment_processors` (
  `id` int(3) NOT NULL auto_increment,
  `type` enum('internal','external') NOT NULL default 'internal',
  `name` varchar(100) NOT NULL default '',
  `description` text NOT NULL,
  `company_url` varchar(255) NOT NULL default '',
  `status` int(1) default '1',
  `picture` varchar(100) NOT NULL default '',
  `text` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_shop_pricelists` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(25) NOT NULL default '',
  `lang_id` int(2) unsigned NOT NULL default '0',
  `border_on` tinyint(1) NOT NULL default '1',
  `header_on` tinyint(1) NOT NULL default '1',
  `header_left` text ,
  `header_right` text ,
  `footer_on` tinyint(1) NOT NULL default '0',
  `footer_left` text ,
  `footer_right` text ,
  `categories` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_shop_products` (
  `id` smallint(10) unsigned NOT NULL auto_increment,
  `product_id` tinytext NOT NULL,
  `picture` text ,
  `title` varchar(255) NOT NULL default '',
  `catid` int(10) unsigned NOT NULL default '1',
  `handler` enum('none','delivery','download') NOT NULL default 'delivery',
  `normalprice` decimal(6,2) NOT NULL default '0.00',
  `resellerprice` decimal(6,2) NOT NULL default '0.00',
  `shortdesc` text NOT NULL,
  `description` text ,
  `stock` smallint(6) unsigned NOT NULL default '10',
  `stock_visibility` tinyint(1) unsigned NOT NULL default '1',
  `discountprice` decimal(6,2) NOT NULL default '0.00',
  `is_special_offer` tinyint(1) unsigned default '0',
  `property1` varchar(100) default '0',
  `property2` varchar(100) default '0',
  `status` tinyint(1) unsigned default '1',
  `b2b` tinyint(1) unsigned NOT NULL default '1',
  `b2c` tinyint(1) unsigned NOT NULL default '1',
  `startdate` datetime NOT NULL default '0000-00-00 00:00:00',
  `enddate` datetime NOT NULL default '0000-00-00 00:00:00',
  `thumbnail_percent` tinyint(2) unsigned NOT NULL default '0',
  `thumbnail_quality` tinyint(2) unsigned NOT NULL default '0',
  `manufacturer` int(11) unsigned NOT NULL,
  `manufacturer_url` varchar(255) NOT NULL default '',
  `external_link` varchar(255) NOT NULL default '',
  `sort_order` smallint(4) unsigned NOT NULL default '0',
  `vat_id` int(10) unsigned default NULL,
  `weight` int(10) unsigned default NULL,
  `flags` varchar(255) NOT NULL default '',
  `usergroups` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  FULLTEXT KEY `shopindex` (`title`,`description`),
  FULLTEXT KEY `flags` (`flags`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_shop_products_attributes` (
  `attribute_id` int(11) unsigned NOT NULL auto_increment,
  `product_id` smallint(10) unsigned NOT NULL default '0',
  `attributes_name_id` int(11) unsigned NOT NULL default '0',
  `attributes_value_id` int(11) unsigned NOT NULL default '0',
  `sort_id` int(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`attribute_id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_shop_products_attributes_name` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(32) NOT NULL default '',
  `display_type` TINYINT UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_shop_products_attributes_value` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name_id` int(11) unsigned NOT NULL default '0',
  `value` varchar(32) NOT NULL default '',
  `price` decimal(6,2) default '0.00',
  `price_prefix` enum('+','-') NOT NULL default '+',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_shop_products_downloads` (
  `products_downloads_id` int(11) NOT NULL default '0',
  `products_downloads_name` varchar(255) NOT NULL default '',
  `products_downloads_filename` varchar(255) NOT NULL default '',
  `products_downloads_maxdays` int(2) default '0',
  `products_downloads_maxcount` int(2) default '0',
  PRIMARY KEY  (`products_downloads_id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_shop_rel_countries` (
  `id` int(3) NOT NULL auto_increment,
  `zones_id` int(3) NOT NULL default '0',
  `countries_id` int(3) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_shop_rel_payment` (
  `id` int(3) NOT NULL auto_increment,
  `zones_id` int(3) NOT NULL default '0',
  `payment_id` int(3) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_shop_rel_shipment` (
  `id` tinyint(3) NOT NULL auto_increment,
  `zones_id` tinyint(3) NOT NULL default '0',
  `shipment_id` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_shop_shipment_cost` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `shipper_id` int(10) unsigned NOT NULL,
  `max_weight` int(10) unsigned default NULL,
  `cost` decimal(8,2) unsigned default NULL,
  `price_free` decimal(8,2) unsigned default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_shop_shipper` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` tinytext NOT NULL,
  `status` tinyint(1) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_shop_vat` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `class` tinytext NOT NULL,
  `percent` decimal(5,2) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_module_shop_zones` (
  `zones_id` int(3) NOT NULL auto_increment,
  `zones_name` varchar(64) NOT NULL default '',
  `activation_status` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`zones_id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_sessions` (
  `sessionid` varchar(255) NOT NULL default '',
  `startdate` varchar(14) NOT NULL default '',
  `lastupdated` varchar(14) NOT NULL default '',
  `status` varchar(20) NOT NULL default '',
  `user_id` int(10) unsigned NOT NULL default '0',
  `datavalue` text ,
  PRIMARY KEY  (`sessionid`),
  KEY `LastUpdated` (`lastupdated`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_settings` (
  `setid` int(6) unsigned NOT NULL auto_increment,
  `setname` varchar(250) NOT NULL default '',
  `setvalue` text NOT NULL,
  `setmodule` tinyint(2) NOT NULL default '0',
  PRIMARY KEY  (`setid`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_settings_smtp` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `hostname` varchar(255) NOT NULL,
  `port` smallint(5) unsigned NOT NULL default '25',
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_skins` (
  `id` int(2) unsigned NOT NULL auto_increment,
  `themesname` varchar(50) NOT NULL default '',
  `foldername` varchar(50) NOT NULL default '',
  `expert` int(1) NOT NULL default '1',
  UNIQUE KEY `id` (`id`),
  KEY `id_2` (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_stats_browser` (
  `id` int(6) unsigned NOT NULL auto_increment,
  `name` varchar(255) BINARY NOT NULL default '',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique` (`name`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_stats_colourdepth` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `depth` tinyint(3) unsigned NOT NULL default '0',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique` (`depth`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_stats_config` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(64) NOT NULL default '',
  `value` varchar(255) NOT NULL default '',
  `status` int(1) default '1',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_stats_country` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `country` varchar(100) BINARY NOT NULL default '',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique` (`country`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_stats_hostname` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `hostname` varchar(255) BINARY NOT NULL default '',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique` (`hostname`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_stats_javascript` (
  `id` int(3) unsigned NOT NULL auto_increment,
  `support` enum('0','1') default '0',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_stats_operatingsystem` (
  `id` int(6) unsigned NOT NULL auto_increment,
  `name` varchar(255) BINARY NOT NULL default '',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique` (`name`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_stats_referer` (
  `id` int(8) unsigned NOT NULL auto_increment,
  `uri` varchar(255) BINARY NOT NULL default '',
  `timestamp` int(11) unsigned NOT NULL default '0',
  `count` mediumint(8) unsigned NOT NULL default '0',
  `sid` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique` (`uri`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_stats_requests` (
  `id` int(9) unsigned NOT NULL auto_increment,
  `timestamp` int(11) default '0',
  `pageId` int(6) unsigned NOT NULL default '0',
  `page` varchar(255) BINARY NOT NULL default '',
  `visits` int(9) unsigned NOT NULL default '0',
  `sid` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique` (`page`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_stats_requests_summary` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `type` varchar(10) NOT NULL default '',
  `timestamp` int(11) NOT NULL default '0',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique` (`type`,`timestamp`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_stats_screenresolution` (
  `id` int(5) unsigned NOT NULL auto_increment,
  `resolution` varchar(11) NOT NULL default '',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique` (`resolution`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_stats_search` (
  `id` int(5) unsigned NOT NULL auto_increment,
  `name` varchar(100) BINARY NOT NULL default '',
  `count` int(10) unsigned NOT NULL default '0',
  `sid` varchar(32) NOT NULL default '',
  `external` enum('0','1') NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique` (`name`,`external`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_stats_spiders` (
  `id` int(9) unsigned NOT NULL auto_increment,
  `last_indexed` int(14) default NULL,
  `page` varchar(100) BINARY default NULL,
  `pageId` mediumint(6) unsigned NOT NULL default '0',
  `count` int(11) NOT NULL default '0',
  `spider_useragent` varchar(255) default NULL,
  `spider_ip` varchar(100) default NULL,
  `spider_host` varchar(255) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique` (`page`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_stats_spiders_summary` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) BINARY NOT NULL default '',
  `timestamp` int(11) NOT NULL default '0',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique` (`name`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_stats_visitors` (
  `id` int(8) unsigned NOT NULL auto_increment,
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

CREATE TABLE `contrexx_stats_visitors_summary` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `type` varchar(10) NOT NULL default '',
  `timestamp` int(11) NOT NULL default '0',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique` (`type`,`timestamp`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_voting_email` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `email` varchar(255) NOT NULL,
  `valid` enum('0','1') NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `email` (`email`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_voting_rel_email_system` (
  `email_id` int(10) unsigned NOT NULL,
  `system_id` int(10) unsigned NOT NULL,
  `voting_id` int(10) unsigned NOT NULL,
  `valid` enum('0','1') NOT NULL,
  UNIQUE KEY `email_id` (`email_id`,`system_id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_voting_results` (
  `id` int(11) NOT NULL auto_increment,
  `voting_system_id` int(11) default NULL,
  `question` char(200) default NULL,
  `votes` int(11) default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `contrexx_voting_system` (
  `id` int(11) NOT NULL auto_increment,
  `date` timestamp NOT NULL ,
  `title` varchar(60) NOT NULL default '',
  `question` text ,
  `status` tinyint(1) default '1',
  `submit_check` enum('cookie','email') NOT NULL default 'cookie',
  `votes` int(11) default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;
