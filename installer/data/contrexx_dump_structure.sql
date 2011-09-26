SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_access_group_dynamic_ids` (
  `access_id` int(11) unsigned NOT NULL default '0',
  `group_id` int(11) unsigned NOT NULL default '0'
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_access_group_static_ids` (
  `access_id` int(11) unsigned NOT NULL default '0',
  `group_id` int(11) unsigned NOT NULL default '0'
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_access_rel_user_group` (
  `user_id` int(10) unsigned NOT NULL default '0',
  `group_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`user_id`,`group_id`)
) TYPE=InnoDB;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_access_settings` (
  `key` varchar(32) NOT NULL default '',
  `value` varchar(255) NOT NULL default '',
  `status` tinyint(1) unsigned NOT NULL default '0',
  UNIQUE KEY `key` (`key`)
) TYPE=InnoDB;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_access_user_attribute` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `parent_id` int(10) unsigned NOT NULL default '0',
  `type` enum('text','textarea','mail','uri','date','image','checkbox','menu','menu_option','group','frame','history') NOT NULL default 'text',
  `mandatory` enum('0','1') NOT NULL default '0',
  `sort_type` enum('asc','desc','custom') NOT NULL default 'asc',
  `order_id` int(10) unsigned NOT NULL default '0',
  `access_special` enum('','menu_select_higher','menu_select_lower') NOT NULL default '',
  `access_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=InnoDB;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_access_user_attribute_name` (
  `attribute_id` int(10) unsigned NOT NULL default '0',
  `lang_id` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`attribute_id`,`lang_id`)
) TYPE=InnoDB;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_access_user_attribute_value` (
  `attribute_id` int(10) unsigned NOT NULL default '0',
  `user_id` int(10) unsigned NOT NULL default '0',
  `history_id` int(10) unsigned NOT NULL default '0',
  `value` text NOT NULL,
  PRIMARY KEY  (`attribute_id`,`user_id`,`history_id`),
  FULLTEXT KEY `value` (`value`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_access_user_core_attribute` (
  `id` varchar(25) NOT NULL,
  `mandatory` enum('0','1') NOT NULL default '0',
  `sort_type` enum('asc','desc','custom') NOT NULL default 'asc',
  `order_id` int(10) unsigned NOT NULL default '0',
  `access_special` enum('','menu_select_higher','menu_select_lower') NOT NULL default '',
  `access_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=InnoDB;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_access_user_groups` (
  `group_id` int(6) unsigned NOT NULL auto_increment,
  `group_name` varchar(100) NOT NULL default '',
  `group_description` varchar(255) NOT NULL default '',
  `is_active` tinyint(4) NOT NULL default '1',
  `type` enum('frontend','backend') NOT NULL default 'frontend',
  `homepage` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`group_id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_access_user_mail` (
  `type` enum('reg_confirm','reset_pw','user_activated','user_deactivated','new_user') NOT NULL default 'reg_confirm',
  `lang_id` tinyint(2) unsigned NOT NULL default '0',
  `sender_mail` varchar(255) NOT NULL default '',
  `sender_name` varchar(255) NOT NULL default '',
  `subject` varchar(255) NOT NULL default '',
  `format` enum('text','html','multipart') NOT NULL default 'text',
  `body_text` text NOT NULL,
  `body_html` text NOT NULL,
  UNIQUE KEY `mail` (`type`,`lang_id`)
) TYPE=InnoDB;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_access_user_profile` (
  `user_id` int(5) unsigned NOT NULL default '0',
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
  `birthday` varchar(11) default NULL,
  `website` varchar(255) NOT NULL default '',
  `profession` varchar(150) NOT NULL default '',
  `interests` varchar(255) NOT NULL default '',
  `signature` varchar(255) NOT NULL default '',
  `picture` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`user_id`),
  KEY `profile` (`firstname`(100),`lastname`(100),`company`(50))
) TYPE=InnoDB;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_access_user_title` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `order_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `title` (`title`)
) TYPE=InnoDB;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_access_user_validity` (
  `validity` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`validity`)
) TYPE=InnoDB;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
  `primary_group` int(6) unsigned NOT NULL default '0',
  `profile_access` enum('everyone','members_only','nobody') NOT NULL default 'members_only',
  `restore_key` varchar(32) NOT NULL default '',
  `restore_key_time` int(14) unsigned NOT NULL default '0',
  `u2u_active` enum('0','1') NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `username` (`username`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
  UNIQUE KEY `access_id` (`access_id`),
  KEY `area_name` (`area_name`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_content_logfile` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `action` set('new','update','delete') NOT NULL default 'new',
  `history_id` int(10) unsigned NOT NULL default '0',
  `is_validated` set('0','1') NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `history_id` (`history_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
  `lang` int(2) unsigned NOT NULL default '1',
  `module` int(2) unsigned NOT NULL default '0',
  `startdate` date NOT NULL default '0000-00-00',
  `enddate` date NOT NULL default '0000-00-00',
  `protected` tinyint(4) NOT NULL default '0',
  `frontend_access_id` int(11) unsigned NOT NULL default '0',
  `backend_access_id` int(11) unsigned NOT NULL default '0',
  `themes_id` int(4) NOT NULL default '0',
  `css_name` varchar(255) NOT NULL default '',
  `custom_content` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`catid`),
  KEY `parcat` (`parcat`),
  KEY `module` (`module`),
  KEY `catname` (`catname`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
  `lang` int(2) unsigned NOT NULL default '1',
  `module` int(2) unsigned NOT NULL default '0',
  `startdate` date NOT NULL default '0000-00-00',
  `enddate` date NOT NULL default '0000-00-00',
  `protected` tinyint(4) NOT NULL default '0',
  `frontend_access_id` int(11) unsigned NOT NULL default '0',
  `backend_access_id` int(11) unsigned NOT NULL default '0',
  `themes_id` int(4) NOT NULL default '0',
  `css_name` varchar(255) NOT NULL default '',
  `custom_content` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `catid` (`catid`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_ids` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `timestamp` int(14) default NULL,
  `type` varchar(100) NOT NULL default '',
  `remote_addr` varchar(15) default NULL,
  `http_x_forwarded_for` varchar(15) NOT NULL default '',
  `http_via` varchar(255) NOT NULL default '',
  `user` mediumtext,
  `gpcs` mediumtext NOT NULL,
  `file` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
  `mobile_themes_id` int(2) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `lang` (`lang`),
  KEY `defaultstatus` (`is_default`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_lib_country` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(64) NOT NULL,
  `iso_code_2` char(2) NOT NULL,
  `iso_code_3` char(3) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique` (`iso_code_2`),
  KEY `INDEX_COUNTRIES_NAME` (`name`)
) TYPE=InnoDB;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_log` (
  `id` int(6) unsigned NOT NULL auto_increment,
  `userid` int(6) unsigned default NULL,
  `datetime` datetime default '0000-00-00 00:00:00',
  `useragent` varchar(250) default NULL,
  `userlanguage` varchar(250) default NULL,
  `remote_addr` varchar(250) default NULL,
  `remote_host` varchar(250) default NULL,
  `http_via` varchar(250) NOT NULL default '',
  `http_client_ip` varchar(250) NOT NULL default '',
  `http_x_forwarded_for` varchar(250) NOT NULL default '',
  `referer` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_alias_source` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `target_id` int(10) unsigned NOT NULL default '0',
  `lang_id` int(10) unsigned NOT NULL default '1',
  `url` varchar(255) NOT NULL,
  `isdefault` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `isdefault` (`isdefault`),
  KEY `url_lang_id` (`lang_id`,`url`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_alias_target` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `type` enum('url','local') NOT NULL default 'url',
  `url` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `url` (`url`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_auction` (
  `id` int(9) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `email` varchar(100) NOT NULL default '',
  `type` set('search','offer') NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `description` mediumtext NOT NULL,
  `premium` int(1) NOT NULL default '0',
  `picture_1` varchar(255) NOT NULL,
  `picture_2` varchar(255) NOT NULL,
  `picture_3` varchar(255) NOT NULL,
  `picture_4` varchar(255) NOT NULL,
  `picture_5` varchar(255) NOT NULL,
  `catid` int(4) NOT NULL default '0',
  `startprice` decimal(11,2) NOT NULL,
  `incr_step` decimal(11,2) NOT NULL,
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
  `shipping` text NOT NULL,
  `payment` text NOT NULL,
  PRIMARY KEY  (`id`),
  FULLTEXT KEY `description` (`description`),
  FULLTEXT KEY `title` (`description`,`title`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_auction_bids` (
  `bid_id` int(11) NOT NULL auto_increment,
  `bid_auction` int(11) NOT NULL,
  `bid_user` int(11) NOT NULL,
  `bid_price` decimal(11,2) NOT NULL,
  `bid_time` varchar(255) NOT NULL,
  `bid_ip` varchar(255) NOT NULL,
  PRIMARY KEY  (`bid_id`),
  KEY `bid_auction` (`bid_auction`),
  KEY `bid_user` (`bid_user`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_auction_categories` (
  `id` int(6) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `displayorder` int(4) NOT NULL default '0',
  `status` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_auction_mail` (
  `id` int(4) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `content` longtext NOT NULL,
  `mailto` varchar(10) NOT NULL,
  `mailcc` mediumtext NOT NULL,
  `active` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_auction_paypal` (
  `id` int(4) NOT NULL auto_increment,
  `active` int(1) NOT NULL default '0',
  `profile` varchar(255) NOT NULL default '',
  `price` varchar(10) NOT NULL default '',
  `price_premium` varchar(10) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_auction_settings` (
  `id` int(6) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `value` varchar(255) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `type` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_auction_spez_fields` (
  `id` int(5) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `value` varchar(100) NOT NULL,
  `type` int(1) NOT NULL default '1',
  `lang_id` int(2) NOT NULL,
  `active` int(1) NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_blog_categories` (
  `category_id` int(4) unsigned NOT NULL default '0',
  `lang_id` int(2) unsigned NOT NULL default '0',
  `is_active` enum('0','1') NOT NULL default '1',
  `name` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`category_id`,`lang_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_blog_comments` (
  `comment_id` int(7) unsigned NOT NULL auto_increment,
  `message_id` int(6) unsigned NOT NULL default '0',
  `lang_id` int(2) unsigned NOT NULL default '0',
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
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_blog_message_to_category` (
  `message_id` int(6) unsigned NOT NULL default '0',
  `category_id` int(4) unsigned NOT NULL default '0',
  `lang_id` int(2) unsigned NOT NULL default '0',
  PRIMARY KEY  (`message_id`,`category_id`,`lang_id`),
  KEY `category_id` (`category_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_blog_messages` (
  `message_id` int(6) unsigned NOT NULL auto_increment,
  `user_id` int(5) unsigned NOT NULL default '0',
  `time_created` int(14) unsigned NOT NULL default '0',
  `time_edited` int(14) unsigned NOT NULL default '0',
  `hits` int(7) unsigned NOT NULL default '0',
  PRIMARY KEY  (`message_id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_blog_networks` (
  `network_id` int(8) unsigned NOT NULL auto_increment,
  `name` varchar(250) NOT NULL default '',
  `url` varchar(255) NOT NULL default '',
  `url_link` varchar(255) NOT NULL default '',
  `icon` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`network_id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_blog_networks_lang` (
  `network_id` int(8) unsigned NOT NULL default '0',
  `lang_id` int(2) unsigned NOT NULL default '0',
  PRIMARY KEY  (`network_id`,`lang_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_blog_settings` (
  `name` varchar(50) NOT NULL,
  `value` varchar(250) NOT NULL,
  PRIMARY KEY  (`name`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_blog_votes` (
  `vote_id` int(8) unsigned NOT NULL auto_increment,
  `message_id` int(6) unsigned NOT NULL default '0',
  `time_voted` int(14) unsigned NOT NULL default '0',
  `ip_address` varchar(15) NOT NULL default '0.0.0.0',
  `vote` enum('1','2','3','4','5','6','7','8','9','10') NOT NULL default '1',
  PRIMARY KEY  (`vote_id`),
  KEY `message_id` (`message_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
  `series_status` tinyint(4) NOT NULL,
  `series_type` int(11) NOT NULL,
  `series_pattern_count` int(11) NOT NULL,
  `series_pattern_weekday` varchar(7) NOT NULL,
  `series_pattern_day` int(11) NOT NULL,
  `series_pattern_week` int(11) NOT NULL,
  `series_pattern_month` int(11) NOT NULL,
  `series_pattern_type` int(11) NOT NULL,
  `series_pattern_dourance_type` int(11) NOT NULL,
  `series_pattern_end` int(11) NOT NULL,
  `series_pattern_begin` int(11) NOT NULL default '0',
  `series_pattern_exceptions` longtext NOT NULL,
  PRIMARY KEY  (`id`),
  FULLTEXT KEY `name` (`name`,`comment`,`placeName`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_calendar_categories` (
  `id` int(5) NOT NULL auto_increment,
  `name` varchar(150) NOT NULL default '',
  `status` int(1) NOT NULL default '0',
  `lang` int(1) NOT NULL default '0',
  `pos` int(5) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_calendar_form_data` (
  `reg_id` int(10) NOT NULL default '0',
  `field_id` int(10) NOT NULL default '0',
  `data` text NOT NULL
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_calendar_form_fields` (
  `id` int(7) NOT NULL auto_increment,
  `note_id` int(10) NOT NULL default '0',
  `name` text NOT NULL,
  `type` int(1) NOT NULL default '0',
  `required` int(1) NOT NULL default '0',
  `order` int(3) NOT NULL default '0',
  `key` int(7) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_calendar_registrations` (
  `id` int(7) NOT NULL auto_increment,
  `note_id` int(7) NOT NULL default '0',
  `note_date` int(11) NOT NULL,
  `time` int(14) NOT NULL default '0',
  `host` varchar(255) NOT NULL,
  `ip_address` varchar(15) NOT NULL,
  `type` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_calendar_settings` (
  `setid` int(7) NOT NULL auto_increment,
  `setname` varchar(255) NOT NULL,
  `setvalue` text NOT NULL,
  PRIMARY KEY  (`setid`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_data_categories` (
  `category_id` int(4) unsigned NOT NULL default '0',
  `lang_id` int(2) unsigned NOT NULL default '0',
  `is_active` enum('0','1') NOT NULL default '1',
  `parent_id` int(10) unsigned NOT NULL default '0',
  `name` varchar(100) NOT NULL default '',
  `active` enum('0','1') NOT NULL default '1',
  `cmd` int(10) unsigned NOT NULL default '1',
  `action` enum('content','overlaybox','subcategories') NOT NULL default 'content',
  `sort` int(10) unsigned NOT NULL default '1',
  `box_height` int(10) unsigned NOT NULL default '500',
  `box_width` int(11) NOT NULL default '350',
  `template` text NOT NULL,
  PRIMARY KEY  (`category_id`,`lang_id`)
) TYPE=InnoDB;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_data_message_to_category` (
  `message_id` int(6) unsigned NOT NULL default '0',
  `category_id` int(4) unsigned NOT NULL default '0',
  `lang_id` int(2) unsigned NOT NULL default '0',
  PRIMARY KEY  (`message_id`,`category_id`,`lang_id`),
  KEY `category_id` (`category_id`)
) TYPE=InnoDB;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_data_messages` (
  `message_id` int(6) unsigned NOT NULL auto_increment,
  `user_id` int(5) unsigned NOT NULL default '0',
  `time_created` int(14) unsigned NOT NULL default '0',
  `time_edited` int(14) unsigned NOT NULL default '0',
  `hits` int(7) unsigned NOT NULL default '0',
  `active` enum('0','1') NOT NULL default '1',
  `sort` int(10) unsigned NOT NULL default '1',
  `mode` set('normal','forward') NOT NULL default 'normal',
  `release_time` int(15) NOT NULL default '0',
  `release_time_end` int(15) NOT NULL default '0',
  PRIMARY KEY  (`message_id`)
) TYPE=InnoDB;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_data_messages_lang` (
  `message_id` int(6) unsigned NOT NULL default '0',
  `lang_id` int(2) unsigned NOT NULL default '0',
  `is_active` enum('0','1') NOT NULL default '1',
  `subject` varchar(250) NOT NULL default '',
  `content` text NOT NULL,
  `tags` varchar(250) NOT NULL default '',
  `image` varchar(250) NOT NULL default '',
  `thumbnail` varchar(250) NOT NULL,
  `thumbnail_type` enum('original','thumbnail') NOT NULL default 'original',
  `thumbnail_width` tinyint(3) unsigned NOT NULL default '0',
  `thumbnail_height` tinyint(3) unsigned NOT NULL default '0',
  `attachment` varchar(255) NOT NULL default '',
  `attachment_description` varchar(255) NOT NULL default '',
  `mode` set('normal','forward') NOT NULL default 'normal',
  `forward_url` varchar(255) NOT NULL default '',
  `forward_target` varchar(40) default NULL,
  PRIMARY KEY  (`message_id`,`lang_id`)
) TYPE=InnoDB;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_data_placeholders` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `type` set('cat','entry') NOT NULL default '',
  `ref_id` int(11) NOT NULL default '0',
  `placeholder` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `placeholder` (`placeholder`),
  UNIQUE KEY `type` (`type`,`ref_id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_data_settings` (
  `name` varchar(50) NOT NULL default '',
  `value` text NOT NULL,
  PRIMARY KEY  (`name`)
) TYPE=InnoDB;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
  `logo` varchar(50) default NULL,
  `team` varchar(255) NOT NULL default '',
  `portfolio` varchar(255) NOT NULL default '',
  `offers` varchar(255) NOT NULL default '',
  `concept` varchar(255) NOT NULL default '',
  `map` varchar(255) default NULL,
  `lokal` varchar(255) default NULL,
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
  `spez_field_11` varchar(255) default NULL,
  `spez_field_12` varchar(255) default NULL,
  `spez_field_13` varchar(255) default NULL,
  `spez_field_14` varchar(255) default NULL,
  `spez_field_15` varchar(255) default NULL,
  `spez_field_21` varchar(255) NOT NULL default '',
  `spez_field_22` varchar(255) NOT NULL default '',
  `spez_field_16` varchar(255) default NULL,
  `spez_field_17` varchar(255) default NULL,
  `spez_field_18` varchar(255) default NULL,
  `spez_field_19` varchar(255) default NULL,
  `spez_field_20` varchar(255) default NULL,
  `spez_field_23` varchar(255) NOT NULL default '',
  `spez_field_24` varchar(255) NOT NULL default '',
  `spez_field_25` varchar(255) NOT NULL default '',
  `spez_field_26` varchar(255) NOT NULL default '',
  `spez_field_27` varchar(255) NOT NULL default '',
  `spez_field_28` varchar(255) NOT NULL default '',
  `spez_field_29` varchar(255) NOT NULL default '',
  `youtube` mediumtext NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `date` (`date`),
  KEY `temphitsout` (`hits`),
  KEY `status` (`status`),
  FULLTEXT KEY `name` (`title`,`description`),
  FULLTEXT KEY `description` (`description`),
  FULLTEXT KEY `title` (`title`,`description`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_directory_mail` (
  `id` tinyint(4) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `content` longtext NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_directory_rel_dir_cat` (
  `dir_id` int(7) NOT NULL default '0',
  `cat_id` int(7) NOT NULL default '0',
  PRIMARY KEY  (`dir_id`,`cat_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_directory_rel_dir_level` (
  `dir_id` int(7) NOT NULL default '0',
  `level_id` int(7) NOT NULL default '0',
  PRIMARY KEY  (`dir_id`,`level_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_directory_settings` (
  `setid` int(6) unsigned NOT NULL auto_increment,
  `setname` varchar(250) NOT NULL default '',
  `setvalue` text NOT NULL,
  `settyp` int(1) NOT NULL default '0',
  PRIMARY KEY  (`setid`),
  KEY `setname` (`setname`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_directory_settings_google` (
  `setid` int(6) unsigned NOT NULL auto_increment,
  `setname` varchar(250) NOT NULL default '',
  `setvalue` text NOT NULL,
  `settyp` int(1) NOT NULL default '0',
  PRIMARY KEY  (`setid`),
  KEY `setname` (`setname`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_directory_vote` (
  `id` int(7) NOT NULL auto_increment,
  `feed_id` int(7) NOT NULL default '0',
  `vote` int(2) NOT NULL default '0',
  `count` int(7) NOT NULL default '0',
  `client` varchar(255) NOT NULL default '',
  `time` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_docsys` (
  `id` int(6) unsigned NOT NULL auto_increment,
  `date` int(14) default NULL,
  `title` varchar(250) NOT NULL default '',
  `author` varchar(150) NOT NULL default '',
  `text` mediumtext NOT NULL,
  `source` varchar(250) NOT NULL default '',
  `url1` varchar(250) NOT NULL default '',
  `url2` varchar(250) NOT NULL default '',
  `lang` int(2) unsigned NOT NULL default '0',
  `userid` int(6) unsigned NOT NULL default '0',
  `startdate` int(14) unsigned NOT NULL default '0',
  `enddate` int(14) unsigned NOT NULL default '0',
  `status` tinyint(4) NOT NULL default '1',
  `changelog` int(14) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  FULLTEXT KEY `newsindex` (`title`,`text`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_docsys_categories` (
  `catid` int(2) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `lang` int(2) unsigned NOT NULL default '1',
  `sort_style` enum('alpha','date','date_alpha') NOT NULL default 'alpha',
  PRIMARY KEY  (`catid`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_docsys_entry_category` (
  `entry` int(10) unsigned NOT NULL default '0',
  `category` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`entry`,`category`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_downloads_category` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `parent_id` int(11) unsigned NOT NULL default '0',
  `is_active` tinyint(1) unsigned NOT NULL default '1',
  `visibility` tinyint(1) unsigned NOT NULL default '1',
  `owner_id` int(5) unsigned NOT NULL default '0',
  `order` int(3) unsigned NOT NULL default '0',
  `deletable_by_owner` tinyint(1) unsigned NOT NULL default '1',
  `modify_access_by_owner` tinyint(1) unsigned NOT NULL default '1',
  `read_access_id` int(11) unsigned NOT NULL default '0',
  `add_subcategories_access_id` int(11) unsigned NOT NULL default '0',
  `manage_subcategories_access_id` int(11) unsigned NOT NULL default '0',
  `add_files_access_id` int(11) unsigned NOT NULL default '0',
  `manage_files_access_id` int(11) unsigned NOT NULL default '0',
  `image` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `is_active` (`is_active`),
  KEY `visibility` (`visibility`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_downloads_category_locale` (
  `lang_id` int(11) unsigned NOT NULL default '0',
  `category_id` int(11) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `description` text NOT NULL,
  PRIMARY KEY  (`lang_id`,`category_id`),
  FULLTEXT KEY `name` (`name`),
  FULLTEXT KEY `description` (`description`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_downloads_download` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `type` enum('file','url') NOT NULL default 'file',
  `mime_type` enum('image','document','pdf','media','archive','application','link') NOT NULL default 'image',
  `source` varchar(255) NOT NULL default '',
  `source_name` varchar(255) NOT NULL default '',
  `icon` enum('_blank','avi','bmp','css','doc','dot','exe','fla','gif','htm','html','inc','jpg','js','mp3','nfo','pdf','php','png','pps','ppt','rar','swf','txt','wma','xls','zip') NOT NULL default '_blank',
  `size` int(10) unsigned NOT NULL default '0',
  `image` varchar(255) NOT NULL default '',
  `owner_id` int(5) unsigned NOT NULL default '0',
  `access_id` int(10) unsigned NOT NULL default '0',
  `license` varchar(255) NOT NULL default '',
  `version` varchar(10) NOT NULL default '',
  `author` varchar(100) NOT NULL default '',
  `website` varchar(255) NOT NULL default '',
  `ctime` int(14) unsigned NOT NULL default '0',
  `mtime` int(14) unsigned NOT NULL default '0',
  `is_active` tinyint(3) unsigned NOT NULL default '0',
  `visibility` tinyint(1) unsigned NOT NULL default '1',
  `order` int(3) unsigned NOT NULL default '0',
  `views` int(10) unsigned NOT NULL default '0',
  `download_count` int(10) unsigned NOT NULL default '0',
  `expiration` int(14) unsigned NOT NULL default '0',
  `validity` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `is_active` (`is_active`),
  KEY `visibility` (`visibility`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_downloads_download_locale` (
  `lang_id` int(11) unsigned NOT NULL default '0',
  `download_id` int(11) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `description` text NOT NULL,
  PRIMARY KEY  (`lang_id`,`download_id`),
  FULLTEXT KEY `name` (`name`),
  FULLTEXT KEY `description` (`description`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_downloads_group` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `is_active` tinyint(1) NOT NULL default '1',
  `type` enum('file','url') NOT NULL default 'file',
  `info_page` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_downloads_group_locale` (
  `lang_id` int(11) unsigned NOT NULL default '0',
  `group_id` int(11) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`lang_id`,`group_id`),
  FULLTEXT KEY `name` (`name`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_downloads_rel_download_category` (
  `download_id` int(10) unsigned NOT NULL default '0',
  `category_id` int(10) unsigned NOT NULL default '0',
  `order` int(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`download_id`,`category_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_downloads_rel_download_download` (
  `id1` int(10) unsigned NOT NULL default '0',
  `id2` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id1`,`id2`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_downloads_rel_group_category` (
  `group_id` int(10) unsigned NOT NULL default '0',
  `category_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`group_id`,`category_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_downloads_settings` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(32) NOT NULL default '',
  `value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_ecard_ecards` (
  `code` varchar(35) NOT NULL default '',
  `date` int(10) unsigned NOT NULL default '0',
  `TTL` int(10) unsigned NOT NULL default '0',
  `salutation` varchar(100) NOT NULL default '',
  `senderName` varchar(100) NOT NULL default '',
  `senderEmail` varchar(100) NOT NULL default '',
  `recipientName` varchar(100) NOT NULL default '',
  `recipientEmail` varchar(100) NOT NULL default '',
  `message` text NOT NULL,
  PRIMARY KEY  (`code`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_ecard_settings` (
  `setting_name` varchar(100) NOT NULL default '',
  `setting_value` text NOT NULL,
  PRIMARY KEY  (`setting_name`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_egov_configuration` (
  `name` varchar(255) NOT NULL default '',
  `value` text NOT NULL,
  UNIQUE KEY `name` (`name`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_egov_orders` (
  `order_id` int(11) NOT NULL auto_increment,
  `order_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `order_ip` varchar(255) NOT NULL default '',
  `order_product` int(11) NOT NULL default '0',
  `order_values` text NOT NULL,
  `order_state` tinyint(4) NOT NULL default '0',
  `order_quant` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`order_id`),
  KEY `order_product` (`order_product`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_egov_product_calendar` (
  `calendar_id` int(11) NOT NULL auto_increment,
  `calendar_product` int(11) NOT NULL default '0',
  `calendar_order` int(11) NOT NULL default '0',
  `calendar_day` int(2) NOT NULL default '0',
  `calendar_month` int(2) unsigned zerofill NOT NULL default '00',
  `calendar_year` int(4) NOT NULL default '0',
  `calendar_act` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`calendar_id`),
  KEY `calendar_product` (`calendar_product`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_egov_products` (
  `product_id` int(11) NOT NULL auto_increment,
  `product_autostatus` tinyint(1) NOT NULL default '0',
  `product_name` varchar(255) NOT NULL default '',
  `product_desc` text NOT NULL,
  `product_price` decimal(11,2) NOT NULL default '0.00',
  `product_per_day` enum('yes','no') NOT NULL default 'no',
  `product_quantity` tinyint(2) NOT NULL default '0',
  `product_quantity_limit` tinyint(2) unsigned NOT NULL default '1',
  `product_target_email` varchar(255) NOT NULL default '',
  `product_target_url` varchar(255) NOT NULL default '',
  `product_message` text NOT NULL,
  `product_status` tinyint(1) NOT NULL default '1',
  `product_electro` tinyint(1) NOT NULL default '0',
  `product_file` varchar(255) NOT NULL default '',
  `product_sender_name` varchar(255) NOT NULL default '',
  `product_sender_email` varchar(255) NOT NULL default '',
  `product_target_subject` varchar(255) NOT NULL,
  `product_target_body` text NOT NULL,
  `product_paypal` tinyint(1) NOT NULL default '0',
  `product_paypal_sandbox` varchar(255) NOT NULL default '',
  `product_paypal_currency` varchar(255) NOT NULL default '',
  `product_orderby` int(11) NOT NULL default '0',
  `yellowpay` tinyint(1) unsigned NOT NULL default '0',
  `alternative_names` text NOT NULL,
  PRIMARY KEY  (`product_id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_egov_settings` (
  `set_id` int(11) NOT NULL default '0',
  `set_sender_name` varchar(255) NOT NULL default '',
  `set_sender_email` varchar(255) NOT NULL default '',
  `set_recipient_email` varchar(255) NOT NULL default '',
  `set_state_subject` varchar(255) NOT NULL default '',
  `set_state_email` text NOT NULL,
  `set_calendar_color_1` varchar(255) NOT NULL default '',
  `set_calendar_color_2` varchar(255) NOT NULL default '',
  `set_calendar_color_3` varchar(255) NOT NULL default '',
  `set_calendar_legende_1` varchar(255) NOT NULL default '',
  `set_calendar_legende_2` varchar(255) NOT NULL default '',
  `set_calendar_legende_3` varchar(255) NOT NULL default '',
  `set_calendar_background` varchar(255) NOT NULL default '',
  `set_calendar_border` varchar(255) NOT NULL default '',
  `set_calendar_date_label` varchar(255) NOT NULL default '',
  `set_calendar_date_desc` varchar(255) NOT NULL default '',
  `set_orderentry_subject` varchar(255) NOT NULL default '',
  `set_orderentry_email` text NOT NULL,
  `set_orderentry_name` varchar(255) NOT NULL default '',
  `set_orderentry_sender` varchar(255) NOT NULL default '',
  `set_orderentry_recipient` varchar(255) NOT NULL default '',
  `set_paypal_email` text NOT NULL,
  `set_paypal_currency` text NOT NULL,
  `set_paypal_ipn` tinyint(1) NOT NULL default '0',
  KEY `set_id` (`set_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_feed_category` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(150) NOT NULL default '',
  `status` int(1) NOT NULL default '1',
  `time` int(100) NOT NULL default '0',
  `lang` int(1) NOT NULL default '0',
  `pos` int(3) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_feed_newsml_association` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pId_master` text NOT NULL,
  `pId_slave` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_feed_newsml_providers` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `providerId` text NOT NULL,
  `name` varchar(40) NOT NULL default '',
  `path` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_forum_categories` (
  `id` int(5) unsigned NOT NULL auto_increment,
  `parent_id` int(5) unsigned NOT NULL default '0',
  `order_id` int(5) unsigned NOT NULL default '0',
  `status` set('0','1') NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `parent_id` (`parent_id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_forum_categories_lang` (
  `category_id` int(5) unsigned NOT NULL default '0',
  `lang_id` int(5) unsigned NOT NULL default '0',
  `name` varchar(100) NOT NULL default '',
  `description` text NOT NULL,
  PRIMARY KEY  (`category_id`,`lang_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_forum_notification` (
  `category_id` int(10) unsigned NOT NULL default '0',
  `thread_id` int(10) unsigned NOT NULL default '0',
  `user_id` int(5) unsigned NOT NULL default '0',
  `is_notified` set('0','1') NOT NULL default '0',
  PRIMARY KEY  (`category_id`,`thread_id`,`user_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_forum_rating` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `post_id` int(11) NOT NULL default '0',
  `time` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`,`post_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_forum_settings` (
  `id` int(5) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL default '',
  `value` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_forum_statistics` (
  `category_id` int(5) unsigned NOT NULL default '0',
  `thread_count` int(10) unsigned NOT NULL default '0',
  `post_count` int(10) unsigned NOT NULL default '0',
  `last_post_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`category_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_gallery_categories` (
  `id` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `sorting` int(6) NOT NULL default '0',
  `status` set('0','1') NOT NULL default '1',
  `comment` set('0','1') NOT NULL default '0',
  `voting` set('0','1') NOT NULL default '0',
  `backendProtected` int(11) NOT NULL default '0',
  `backend_access_id` int(11) NOT NULL default '0',
  `frontendProtected` int(11) NOT NULL default '0',
  `frontend_access_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_gallery_language` (
  `gallery_id` int(10) unsigned NOT NULL default '0',
  `lang_id` int(10) unsigned NOT NULL default '0',
  `name` set('name','desc') NOT NULL default '',
  `value` text NOT NULL,
  PRIMARY KEY  (`gallery_id`,`lang_id`,`name`),
  FULLTEXT KEY `galleryindex` (`value`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_gallery_language_pics` (
  `picture_id` int(10) unsigned NOT NULL default '0',
  `lang_id` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `desc` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`picture_id`,`lang_id`),
  FULLTEXT KEY `galleryindex` (`name`,`desc`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_gallery_pictures` (
  `id` int(11) NOT NULL auto_increment,
  `catid` int(11) NOT NULL default '0',
  `validated` set('0','1') NOT NULL default '0',
  `status` set('0','1') NOT NULL default '1',
  `catimg` set('0','1') NOT NULL default '0',
  `sorting` int(6) unsigned NOT NULL default '999',
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
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_gallery_settings` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(30) NOT NULL default '',
  `value` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_gallery_votes` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `picid` int(10) unsigned NOT NULL default '0',
  `date` int(14) unsigned NOT NULL default '0',
  `ip` varchar(15) NOT NULL default '',
  `md5` varchar(32) NOT NULL default '',
  `mark` int(2) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_guestbook` (
  `id` int(6) unsigned NOT NULL auto_increment,
  `status` tinyint(1) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `forename` varchar(255) NOT NULL default '',
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
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_guestbook_settings` (
  `name` varchar(50) NOT NULL default '',
  `value` varchar(250) NOT NULL default '',
  KEY `name` (`name`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_hotel` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `reference` varchar(20) NOT NULL default '-',
  `ref_nr_note` varchar(255) default NULL,
  `logo` enum('-') NOT NULL default '-',
  `special_offer` tinyint(1) NOT NULL default '0',
  `visibility` enum('disabled','reference','listing') NOT NULL default 'disabled',
  `object_type` enum('flat','house','multifamily','estate','industry','parking') NOT NULL default 'flat',
  `new_building` tinyint(1) NOT NULL default '0',
  `property_type` enum('purchase','rent') NOT NULL default 'purchase',
  `longitude` decimal(18,15) NOT NULL default '0.000000000000000',
  `latitude` decimal(18,15) NOT NULL default '0.000000000000000',
  `zoom` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `reference` (`reference`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_hotel_contact` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `email` varchar(255) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `firstname` varchar(255) NOT NULL default '',
  `street` varchar(255) NOT NULL default '',
  `zip` int(5) NOT NULL default '0',
  `location` varchar(255) NOT NULL default '',
  `company` varchar(255) NOT NULL default '',
  `telephone` varchar(30) NOT NULL default '',
  `telephone_office` varchar(30) NOT NULL default '',
  `telephone_mobile` varchar(30) NOT NULL default '',
  `purchase` tinyint(1) NOT NULL default '0',
  `funding` tinyint(1) NOT NULL default '0',
  `comment` text NOT NULL,
  `hotel_id` int(11) NOT NULL default '0',
  `field_id` int(11) NOT NULL default '0',
  `timestamp` int(14) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `immo_id` (`hotel_id`),
  KEY `field_id` (`field_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_hotel_content` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `hotel_id` int(11) NOT NULL default '0',
  `lang_id` tinyint(4) NOT NULL default '0',
  `field_id` int(10) unsigned NOT NULL default '0',
  `fieldvalue` text NOT NULL,
  `active` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `field_id` (`field_id`,`hotel_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_hotel_field` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `type` enum('text','textarea','img','link','protected_link','panorama','digits_only','price') NOT NULL default 'text',
  `order` int(11) NOT NULL default '1000',
  `mandatory` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_hotel_fieldname` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `field_id` int(10) unsigned NOT NULL default '0',
  `lang_id` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '-',
  PRIMARY KEY  (`id`),
  KEY `lang_id` (`lang_id`,`name`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_hotel_image` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `hotel_id` int(11) NOT NULL default '0',
  `field_id` int(10) unsigned NOT NULL default '0',
  `uri` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `immo_id` (`hotel_id`),
  KEY `field_id` (`field_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_hotel_interest` (
  `id` int(11) NOT NULL auto_increment,
  `hotel_id` int(11) NOT NULL default '0',
  `code` varchar(15) NOT NULL default '-',
  `name` varchar(60) NOT NULL default '',
  `firstname` varchar(60) NOT NULL default '',
  `street` varchar(100) NOT NULL default '',
  `zip` varchar(10) NOT NULL default '',
  `location` varchar(100) NOT NULL default '',
  `email` varchar(60) NOT NULL default '',
  `phone_home` varchar(40) NOT NULL default '',
  `phone_office` varchar(40) NOT NULL default '',
  `phone_mobile` varchar(40) NOT NULL default '',
  `weeks` tinyint(4) NOT NULL default '1',
  `adults` tinyint(2) NOT NULL default '0',
  `children` tinyint(2) NOT NULL default '0',
  `from` int(1) NOT NULL default '0',
  `to` int(1) NOT NULL default '0',
  `flight_only` tinyint(1) NOT NULL default '0',
  `traveldata_id` int(1) NOT NULL default '0',
  `comment` text NOT NULL,
  `time` int(14) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `immo_id` (`hotel_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_hotel_languages` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `language` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_hotel_settings` (
  `setid` int(10) unsigned NOT NULL auto_increment,
  `setname` varchar(80) NOT NULL default '',
  `setvalue` text NOT NULL,
  `status` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`setid`),
  UNIQUE KEY `setname` (`setname`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_hotel_statistics` (
  `id` int(11) NOT NULL auto_increment,
  `hotel_id` int(11) NOT NULL default '0',
  `field_id` int(11) NOT NULL default '0',
  `hits` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_immo` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `reference` varchar(20) NOT NULL default '-',
  `ref_nr_note` varchar(255) default NULL,
  `logo` enum('logo1','logo2') NOT NULL default 'logo1',
  `special_offer` tinyint(1) NOT NULL default '0',
  `visibility` enum('disabled','reference','listing') NOT NULL default 'disabled',
  `object_type` enum('flat','house','multifamily','estate','industry','parking') NOT NULL default 'flat',
  `new_building` tinyint(1) NOT NULL default '0',
  `property_type` enum('purchase','rent') NOT NULL default 'purchase',
  `longitude` decimal(18,15) NOT NULL default '0.000000000000000',
  `latitude` decimal(18,15) NOT NULL default '0.000000000000000',
  `zoom` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `reference` (`reference`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_immo_contact` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `email` varchar(255) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `firstname` varchar(255) NOT NULL default '',
  `street` varchar(255) NOT NULL default '',
  `zip` int(5) NOT NULL default '0',
  `location` varchar(255) NOT NULL default '',
  `company` varchar(255) NOT NULL default '',
  `telephone` varchar(30) NOT NULL default '',
  `telephone_office` varchar(30) NOT NULL default '',
  `telephone_mobile` varchar(30) NOT NULL default '',
  `purchase` tinyint(1) NOT NULL default '0',
  `funding` tinyint(1) NOT NULL default '0',
  `comment` text NOT NULL,
  `immo_id` int(11) NOT NULL default '0',
  `field_id` int(11) NOT NULL default '0',
  `timestamp` int(14) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `immo_id` (`immo_id`),
  KEY `field_id` (`field_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_immo_content` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `immo_id` int(11) NOT NULL default '0',
  `lang_id` tinyint(4) NOT NULL default '0',
  `field_id` int(10) unsigned NOT NULL default '0',
  `fieldvalue` text NOT NULL,
  `active` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `field_id` (`field_id`),
  KEY `immo_id` (`immo_id`),
  KEY `fieldvalue` (`fieldvalue`(64))
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_immo_field` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `type` enum('text','textarea','img','link','protected_link','panorama','digits_only','price') NOT NULL default 'text',
  `order` int(11) NOT NULL default '1000',
  `mandatory` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_immo_fieldname` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `field_id` int(10) unsigned NOT NULL default '0',
  `lang_id` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '-',
  PRIMARY KEY  (`id`),
  KEY `field_id` (`field_id`),
  KEY `lang_id` (`lang_id`),
  KEY `name` (`name`(5))
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_immo_image` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `immo_id` int(11) NOT NULL default '0',
  `field_id` int(10) unsigned NOT NULL default '0',
  `uri` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `immo_id` (`immo_id`),
  KEY `field_id` (`field_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_immo_interest` (
  `id` int(11) NOT NULL auto_increment,
  `immo_id` int(11) NOT NULL default '0',
  `name` varchar(60) NOT NULL default '',
  `firstname` varchar(60) NOT NULL default '',
  `street` varchar(100) NOT NULL default '',
  `zip` varchar(10) NOT NULL default '',
  `location` varchar(100) NOT NULL default '',
  `email` varchar(60) NOT NULL default '',
  `phone_office` varchar(40) NOT NULL default '',
  `phone_home` varchar(40) NOT NULL default '',
  `phone_mobile` varchar(40) NOT NULL default '',
  `doc_via_mail` tinyint(1) NOT NULL default '0',
  `funding_advice` tinyint(1) NOT NULL default '0',
  `inspection` tinyint(1) NOT NULL default '0',
  `contact_via_phone` tinyint(1) NOT NULL default '0',
  `comment` text NOT NULL,
  `time` int(14) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `immo_id` (`immo_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_immo_languages` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `language` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_immo_settings` (
  `setid` int(10) unsigned NOT NULL auto_increment,
  `setname` varchar(80) NOT NULL default '',
  `setvalue` text NOT NULL,
  `status` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`setid`),
  UNIQUE KEY `setname` (`setname`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_immo_statistics` (
  `id` int(11) NOT NULL auto_increment,
  `immo_id` int(11) NOT NULL default '0',
  `field_id` int(11) NOT NULL default '0',
  `hits` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_jobs` (
  `id` int(6) unsigned NOT NULL auto_increment,
  `date` int(14) default NULL,
  `title` varchar(250) NOT NULL default '',
  `author` varchar(150) NOT NULL default '',
  `text` mediumtext NOT NULL,
  `workloc` varchar(250) NOT NULL default '',
  `workload` varchar(250) NOT NULL default '',
  `work_start` int(14) NOT NULL default '0',
  `catid` int(2) unsigned NOT NULL default '0',
  `lang` int(2) unsigned NOT NULL default '0',
  `userid` int(6) unsigned NOT NULL default '0',
  `startdate` date NOT NULL default '0000-00-00',
  `enddate` date NOT NULL default '0000-00-00',
  `status` tinyint(4) NOT NULL default '1',
  `changelog` int(14) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  FULLTEXT KEY `newsindex` (`title`,`text`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_jobs_categories` (
  `catid` int(2) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `lang` int(2) unsigned NOT NULL default '1',
  `sort_style` enum('alpha','date','date_alpha') NOT NULL default 'alpha',
  PRIMARY KEY  (`catid`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_jobs_location` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_jobs_rel_loc_jobs` (
  `job` int(10) unsigned NOT NULL default '0',
  `location` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`job`,`location`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_jobs_settings` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(250) NOT NULL default '',
  `value` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_knowledge_article_content` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `article` int(10) unsigned NOT NULL default '0',
  `lang` int(10) unsigned NOT NULL default '0',
  `question` text NOT NULL,
  `answer` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `module_knowledge_article_content_lang` (`lang`),
  KEY `module_knowledge_article_content_article` (`article`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_knowledge_articles` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `category` int(10) unsigned NOT NULL default '0',
  `active` tinyint(1) NOT NULL default '1',
  `hits` int(11) NOT NULL default '0',
  `votes` int(11) NOT NULL default '0',
  `votevalue` int(11) NOT NULL default '0',
  `sort` int(11) NOT NULL default '0',
  `date_created` int(14) NOT NULL default '0',
  `date_updated` int(14) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_knowledge_categories` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `active` tinyint(1) unsigned NOT NULL default '1',
  `parent` int(10) unsigned NOT NULL default '0',
  `sort` int(10) unsigned NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `module_knowledge_categories_sort` (`sort`),
  KEY `module_knowledge_categories_parent` (`parent`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_knowledge_categories_content` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `category` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `lang` int(11) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_knowledge_settings` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `value` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `module_knowledge_settings_name` (`name`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_knowledge_tags` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `lang` int(10) unsigned NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `module_knowledge_tags_name` (`name`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_knowledge_tags_articles` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `article` int(10) unsigned NOT NULL default '0',
  `tag` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `module_knowledge_tags_articles_tag` (`tag`),
  KEY `module_knowledge_tags_articles_article` (`article`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_livecam` (
  `id` int(10) unsigned NOT NULL default '1',
  `currentImagePath` varchar(255) NOT NULL default '/webcam/cam1/current.jpg',
  `archivePath` varchar(255) NOT NULL default '/webcam/cam1/archive/',
  `thumbnailPath` varchar(255) NOT NULL default '/webcam/cam1/thumbs/',
  `maxImageWidth` int(10) unsigned NOT NULL default '400',
  `thumbMaxSize` int(10) unsigned NOT NULL default '200',
  `shadowboxActivate` set('1','0') NOT NULL default '1',
  `showFrom` int(14) NOT NULL default '0',
  `showTill` int(14) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_livecam_settings` (
  `setid` int(10) unsigned NOT NULL auto_increment,
  `setname` varchar(255) NOT NULL default '',
  `setvalue` text NOT NULL,
  PRIMARY KEY  (`setid`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_market` (
  `id` int(9) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `email` varchar(100) NOT NULL default '',
  `type` set('search','offer') NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `description` mediumtext NOT NULL,
  `color` varchar(50) NOT NULL default '',
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
  `sort_id` int(4) NOT NULL default '0',
  `spez_field_1` varchar(255) NOT NULL,
  `spez_field_2` varchar(255) NOT NULL,
  `spez_field_3` varchar(255) NOT NULL,
  `spez_field_4` varchar(255) NOT NULL,
  `spez_field_5` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  FULLTEXT KEY `description` (`description`),
  FULLTEXT KEY `title` (`description`,`title`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_market_categories` (
  `id` int(6) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `displayorder` int(4) NOT NULL default '0',
  `status` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_market_mail` (
  `id` int(4) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `content` longtext NOT NULL,
  `mailto` varchar(10) NOT NULL,
  `mailcc` mediumtext NOT NULL,
  `active` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_market_paypal` (
  `id` int(4) NOT NULL auto_increment,
  `active` int(1) NOT NULL default '0',
  `profile` varchar(255) NOT NULL default '',
  `price` varchar(10) NOT NULL default '',
  `price_premium` varchar(10) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_market_settings` (
  `id` int(6) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `value` varchar(255) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `type` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_market_spez_fields` (
  `id` int(5) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `value` varchar(100) NOT NULL,
  `type` int(1) NOT NULL default '1',
  `lang_id` int(2) NOT NULL default '0',
  `active` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_media_settings` (
  `name` varchar(50) NOT NULL,
  `value` varchar(250) NOT NULL,
  KEY `name` (`name`)
) TYPE=InnoDB;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_mediadir_categories` (
  `id` int(7) NOT NULL auto_increment,
  `parent_id` int(7) NOT NULL,
  `order` int(7) NOT NULL,
  `show_subcategories` int(11) NOT NULL,
  `show_entries` int(1) NOT NULL,
  `picture` mediumtext NOT NULL,
  `active` int(1) NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_mediadir_categories_names` (
  `lang_id` int(1) NOT NULL,
  `category_id` int(7) NOT NULL,
  `category_name` varchar(255) NOT NULL,
  `category_description` mediumtext NOT NULL,
  KEY `lang_id` (`lang_id`),
  KEY `category_id` (`category_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_mediadir_comments` (
  `id` int(7) NOT NULL auto_increment,
  `entry_id` int(7) NOT NULL,
  `added_by` varchar(255) NOT NULL,
  `date` varchar(100) NOT NULL,
  `ip` varchar(100) NOT NULL,
  `name` varchar(255) NOT NULL,
  `mail` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `notification` int(1) NOT NULL default '0',
  `comment` mediumtext NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_mediadir_entries` (
  `id` int(10) NOT NULL auto_increment,
  `order` int(7) NOT NULL default '0',
  `form_id` int(7) NOT NULL,
  `create_date` int(50) NOT NULL,
  `update_date` int(50) NOT NULL,
  `validate_date` int(50) NOT NULL,
  `added_by` int(10) NOT NULL,
  `updated_by` int(10) NOT NULL,
  `lang_id` int(1) NOT NULL,
  `hits` int(10) NOT NULL,
  `popular_hits` int(10) NOT NULL,
  `popular_date` varchar(20) NOT NULL,
  `last_ip` varchar(50) NOT NULL,
  `ready_to_confirm` int(1) NOT NULL default '0',
  `confirmed` int(1) NOT NULL,
  `active` int(1) NOT NULL,
  `duration_type` int(1) NOT NULL,
  `duration_start` int(50) NOT NULL,
  `duration_end` int(50) NOT NULL,
  `duration_notification` int(1) NOT NULL,
  `translation_status` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `lang_id` (`lang_id`),
  KEY `active` (`active`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_mediadir_form_names` (
  `lang_id` int(1) NOT NULL,
  `form_id` int(7) NOT NULL,
  `form_name` varchar(255) NOT NULL,
  `form_description` mediumtext NOT NULL
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_mediadir_forms` (
  `id` int(7) NOT NULL auto_increment,
  `order` int(7) NOT NULL,
  `picture` mediumtext NOT NULL,
  `active` int(1) NOT NULL,
  `use_level` int(1) NOT NULL,
  `use_category` int(1) NOT NULL,
  `use_ready_to_confirm` int(1) NOT NULL,
  `cmd` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_mediadir_inputfield_names` (
  `lang_id` int(10) NOT NULL,
  `form_id` int(7) NOT NULL,
  `field_id` int(10) NOT NULL,
  `field_name` varchar(255) NOT NULL,
  `field_default_value` mediumtext NOT NULL,
  `field_info` mediumtext NOT NULL,
  KEY `field_id` (`field_id`),
  KEY `lang_id` (`lang_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_mediadir_inputfield_types` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `active` int(1) NOT NULL,
  `multi_lang` int(1) NOT NULL,
  `exp_search` int(7) NOT NULL,
  `dynamic` int(1) NOT NULL,
  `comment` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_mediadir_inputfield_verifications` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `regex` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_mediadir_inputfields` (
  `id` int(10) NOT NULL auto_increment,
  `form` int(7) NOT NULL,
  `type` int(10) NOT NULL,
  `verification` int(10) NOT NULL,
  `search` int(10) NOT NULL,
  `required` int(10) NOT NULL,
  `order` int(10) NOT NULL,
  `show_in` int(10) NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_mediadir_level_names` (
  `lang_id` int(1) NOT NULL,
  `level_id` int(7) NOT NULL,
  `level_name` varchar(255) NOT NULL,
  `level_description` mediumtext NOT NULL,
  KEY `lang_id` (`lang_id`),
  KEY `category_id` (`level_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_mediadir_levels` (
  `id` int(7) NOT NULL auto_increment,
  `parent_id` int(7) NOT NULL,
  `order` int(7) NOT NULL,
  `show_sublevels` int(11) NOT NULL,
  `show_categories` int(1) NOT NULL,
  `show_entries` int(1) NOT NULL,
  `picture` mediumtext NOT NULL,
  `active` int(1) NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_mediadir_mail_actions` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `default_recipient` enum('admin','author') NOT NULL,
  `need_auth` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_mediadir_mails` (
  `id` int(7) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `recipients` mediumtext NOT NULL,
  `lang_id` int(1) NOT NULL,
  `action_id` int(1) NOT NULL,
  `is_default` int(1) NOT NULL,
  `active` int(1) NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_mediadir_masks` (
  `id` int(7) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `fields` mediumtext NOT NULL,
  `active` int(11) NOT NULL,
  `form_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_mediadir_order_rel_forms_selectors` (
  `selector_id` int(7) NOT NULL,
  `form_id` int(7) NOT NULL,
  `selector_order` int(7) NOT NULL,
  `exp_search` int(1) NOT NULL
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_mediadir_rel_entry_categories` (
  `entry_id` int(10) NOT NULL,
  `category_id` int(10) NOT NULL,
  KEY `entry_id` (`entry_id`),
  KEY `category_id` (`category_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_mediadir_rel_entry_inputfields` (
  `entry_id` int(7) NOT NULL,
  `lang_id` int(7) NOT NULL,
  `form_id` int(7) NOT NULL,
  `field_id` int(7) NOT NULL,
  `value` longtext NOT NULL,
  FULLTEXT KEY `value` (`value`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_mediadir_rel_entry_levels` (
  `entry_id` int(10) NOT NULL,
  `level_id` int(10) NOT NULL,
  KEY `entry_id` (`entry_id`),
  KEY `category_id` (`level_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_mediadir_settings` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_mediadir_settings_num_categories` (
  `group_id` int(1) NOT NULL,
  `num_categories` varchar(10) NOT NULL
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_mediadir_settings_num_entries` (
  `group_id` int(1) NOT NULL,
  `num_entries` varchar(10) NOT NULL default 'n'
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_mediadir_settings_num_levels` (
  `group_id` int(1) NOT NULL,
  `num_levels` varchar(10) NOT NULL
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_mediadir_settings_perm_group_forms` (
  `group_id` int(7) NOT NULL,
  `form_id` int(1) NOT NULL,
  `status_group` int(1) NOT NULL
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_mediadir_votes` (
  `id` int(7) NOT NULL auto_increment,
  `entry_id` int(7) NOT NULL,
  `added_by` varchar(255) NOT NULL,
  `date` varchar(100) NOT NULL,
  `ip` varchar(100) NOT NULL,
  `vote` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_memberdir_name` (
  `field` int(10) unsigned NOT NULL default '0',
  `dirid` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `active` set('0','1') NOT NULL default '',
  `lang_id` int(2) unsigned NOT NULL default '1'
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_memberdir_settings` (
  `setid` int(4) unsigned NOT NULL auto_increment,
  `setname` varchar(255) NOT NULL default '',
  `setvalue` text NOT NULL,
  `lang_id` int(2) unsigned NOT NULL default '1',
  PRIMARY KEY  (`setid`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_partners_categories` (
  `category_id` int(4) unsigned NOT NULL,
  `lang_id` int(2) unsigned NOT NULL,
  `sort_id` int(10) unsigned NOT NULL,
  `is_active` enum('0','1') NOT NULL default '1',
  `name` varchar(100) NOT NULL default '',
  `imgpath` varchar(250) NOT NULL,
  PRIMARY KEY  (`category_id`,`lang_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_partners_categories_name` (
  `id` int(11) NOT NULL,
  `lang_id` int(11) NOT NULL,
  `is_active` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  PRIMARY KEY  (`id`,`lang_id`)
) TYPE=InnoDB;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_partners_create` (
  `message_id` int(6) unsigned NOT NULL auto_increment,
  `user_id` int(5) unsigned NOT NULL,
  `time_created` int(14) unsigned NOT NULL default '0',
  `time_edited` int(14) unsigned NOT NULL default '0',
  `hits` int(7) unsigned NOT NULL default '0',
  PRIMARY KEY  (`message_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_partners_create_lang` (
  `message_id` int(6) unsigned NOT NULL,
  `lang_id` int(2) unsigned NOT NULL,
  `is_active` enum('0','1') NOT NULL,
  `status` enum('0','1') NOT NULL,
  `subject` varchar(250) binary NOT NULL,
  `level` varchar(250) NOT NULL default '',
  `profile` varchar(250) NOT NULL default '',
  `country` varchar(250) NOT NULL default '',
  `region` varchar(250) NOT NULL,
  `vertical` varchar(250) NOT NULL default '',
  `contactname` varchar(250) binary NOT NULL,
  `email` varchar(250) NOT NULL,
  `website` varchar(250) NOT NULL,
  `address1` varchar(250) NOT NULL default '',
  `address2` varchar(250) NOT NULL default '',
  `city` varchar(250) NOT NULL,
  `zipcode` varchar(250) NOT NULL,
  `phone` varchar(250) NOT NULL default '',
  `fax` varchar(250) NOT NULL,
  `reference` varchar(250) NOT NULL,
  `quote` text NOT NULL,
  `content` text NOT NULL,
  `image` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`message_id`,`lang_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_partners_create_lang_backup` (
  `message_id` int(6) unsigned NOT NULL,
  `lang_id` int(2) unsigned NOT NULL,
  `is_active` enum('0','1') NOT NULL,
  `status` enum('0','1') NOT NULL,
  `subject` varchar(250) NOT NULL default '',
  `level` varchar(250) NOT NULL default '',
  `profile` varchar(250) NOT NULL default '',
  `country` varchar(250) NOT NULL default '',
  `region` varchar(250) NOT NULL,
  `vertical` varchar(250) NOT NULL default '',
  `contactname` varchar(250) NOT NULL,
  `email` varchar(250) NOT NULL,
  `website` varchar(250) NOT NULL,
  `address1` varchar(250) NOT NULL default '',
  `address2` varchar(250) NOT NULL default '',
  `city` varchar(250) NOT NULL,
  `zipcode` varchar(250) NOT NULL,
  `phone` varchar(250) NOT NULL default '',
  `fax` varchar(250) NOT NULL,
  `reference` varchar(250) NOT NULL,
  `quote` text NOT NULL,
  `content` text NOT NULL,
  `image` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`message_id`,`lang_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_partners_display` (
  `display_id` int(11) NOT NULL auto_increment,
  `display_level_id` int(11) NOT NULL,
  `display_title` int(11) NOT NULL,
  `display_content` int(11) NOT NULL,
  `display_contactname` int(11) NOT NULL,
  `display_country` int(11) NOT NULL,
  `display_phone` int(11) NOT NULL,
  `display_address1` int(11) NOT NULL,
  `display_address2` int(11) NOT NULL,
  `display_city` int(11) NOT NULL,
  `display_zipcode` int(11) NOT NULL,
  `display_certificate_logo` int(11) NOT NULL,
  `display_logo` int(11) NOT NULL,
  `display_level_logo` int(11) NOT NULL,
  `display_level_text` int(11) NOT NULL,
  `display_quote` int(11) NOT NULL,
  PRIMARY KEY  (`display_id`)
) TYPE=InnoDB;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_partners_message_to_category` (
  `message_id` int(6) unsigned NOT NULL,
  `category_id` int(4) unsigned NOT NULL,
  `lang_id` int(2) unsigned NOT NULL,
  PRIMARY KEY  (`message_id`,`category_id`,`lang_id`),
  KEY `category_id` (`category_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_partners_message_to_country` (
  `message_id` int(6) unsigned NOT NULL,
  `category_id` int(4) unsigned NOT NULL,
  `lang_id` int(2) unsigned NOT NULL,
  `pos_id` int(10) NOT NULL,
  PRIMARY KEY  (`message_id`,`category_id`,`lang_id`),
  KEY `category_id` (`category_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_partners_message_to_level` (
  `message_id` int(6) unsigned NOT NULL,
  `category_id` int(4) unsigned NOT NULL,
  `lang_id` int(2) unsigned NOT NULL,
  PRIMARY KEY  (`message_id`,`category_id`,`lang_id`),
  KEY `category_id` (`category_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_partners_message_to_profile` (
  `message_id` int(6) unsigned NOT NULL,
  `category_id` int(4) unsigned NOT NULL,
  `lang_id` int(2) unsigned NOT NULL,
  PRIMARY KEY  (`message_id`,`category_id`,`lang_id`),
  KEY `category_id` (`category_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_partners_message_to_region` (
  `message_id` int(6) unsigned NOT NULL,
  `category_id` int(4) unsigned NOT NULL,
  `lang_id` int(2) unsigned NOT NULL,
  PRIMARY KEY  (`message_id`,`category_id`,`lang_id`),
  KEY `category_id` (`category_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_partners_message_to_vertical` (
  `message_id` int(6) unsigned NOT NULL,
  `category_id` int(4) unsigned NOT NULL,
  `lang_id` int(2) unsigned NOT NULL,
  PRIMARY KEY  (`message_id`,`category_id`,`lang_id`),
  KEY `category_id` (`category_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_partners_settings` (
  `id` int(10) NOT NULL auto_increment,
  `sortorder` varchar(100) NOT NULL,
  `width` varchar(100) NOT NULL,
  `height` varchar(100) NOT NULL,
  `lwidth` varchar(100) NOT NULL,
  `lheight` varchar(100) NOT NULL,
  `cwidth` varchar(100) NOT NULL,
  `cheight` varchar(100) NOT NULL,
  `lis_active` varchar(100) NOT NULL,
  `pis_active` varchar(100) NOT NULL,
  `cis_active` varchar(100) NOT NULL,
  `vis_active` varchar(100) NOT NULL,
  `ctis_active` varchar(100) NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=InnoDB;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_partners_user_country` (
  `id` int(10) unsigned NOT NULL,
  `lang_id` int(11) NOT NULL,
  `is_active` int(11) NOT NULL,
  `reg_id` int(10) NOT NULL,
  `sort_id` int(10) unsigned NOT NULL,
  `country` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`,`lang_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_partners_user_level` (
  `id` int(10) unsigned NOT NULL,
  `lang_id` int(2) NOT NULL,
  `is_active` int(2) NOT NULL,
  `sort_id` int(10) unsigned NOT NULL,
  `imgpath` varchar(250) NOT NULL,
  `level` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`,`lang_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_partners_user_profile` (
  `id` int(10) unsigned NOT NULL,
  `lang_id` int(2) NOT NULL,
  `is_active` int(2) NOT NULL,
  `sort_id` int(10) unsigned NOT NULL,
  `profile` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`,`lang_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_partners_user_region` (
  `id` int(10) unsigned NOT NULL,
  `lang_id` int(11) NOT NULL,
  `is_active` int(11) NOT NULL,
  `cat_id` int(10) NOT NULL,
  `sort_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`,`lang_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_partners_user_vertical` (
  `id` int(10) unsigned NOT NULL,
  `lang_id` int(11) NOT NULL,
  `is_active` int(11) NOT NULL,
  `sort_id` int(10) unsigned NOT NULL,
  `vertical` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`,`lang_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_podcast_category` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `status` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  FULLTEXT KEY `podcastindex` (`title`,`description`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_podcast_rel_category_lang` (
  `category_id` int(10) unsigned NOT NULL default '0',
  `lang_id` int(10) unsigned NOT NULL default '0'
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_podcast_rel_medium_category` (
  `medium_id` int(10) unsigned NOT NULL default '0',
  `category_id` int(10) unsigned NOT NULL default '0'
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_podcast_settings` (
  `setid` int(6) unsigned NOT NULL auto_increment,
  `setname` varchar(250) NOT NULL default '',
  `setvalue` text NOT NULL,
  `status` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`setid`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_podcast_template` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `description` varchar(255) NOT NULL default '',
  `template` text NOT NULL,
  `extensions` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `description` (`description`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_recommend` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `value` text NOT NULL,
  `lang_id` int(11) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_shop_article_group` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_shop_categories` (
  `catid` int(10) unsigned NOT NULL auto_increment,
  `parentid` int(10) unsigned NOT NULL default '0',
  `catname` varchar(255) NOT NULL default '',
  `catsorting` int(10) unsigned NOT NULL default '100',
  `catstatus` tinyint(1) unsigned NOT NULL default '1',
  `picture` varchar(255) NOT NULL default '',
  `flags` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`catid`),
  FULLTEXT KEY `flags` (`flags`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_shop_config` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(64) NOT NULL default '',
  `value` varchar(255) NOT NULL default '',
  `status` tinyint(1) unsigned NOT NULL default '1',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_shop_countries` (
  `countries_id` int(10) unsigned NOT NULL auto_increment,
  `countries_name` varchar(64) NOT NULL default '',
  `countries_iso_code_2` char(2) NOT NULL default '',
  `countries_iso_code_3` char(3) NOT NULL default '',
  `activation_status` tinyint(1) unsigned NOT NULL default '1',
  PRIMARY KEY  (`countries_id`),
  KEY `INDEX_COUNTRIES_NAME` (`countries_name`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_shop_currencies` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `code` char(3) NOT NULL default '',
  `symbol` varchar(20) NOT NULL default '',
  `name` varchar(50) NOT NULL default '',
  `rate` decimal(10,6) unsigned NOT NULL default '1.000000',
  `sort_order` int(5) unsigned NOT NULL default '0',
  `status` tinyint(1) unsigned NOT NULL default '1',
  `is_default` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_shop_customer_group` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
  `country_id` int(10) unsigned default NULL,
  `phone` varchar(20) NOT NULL default '',
  `fax` varchar(25) NOT NULL default '',
  `email` varchar(255) NOT NULL default '',
  `ccnumber` varchar(100) NOT NULL default '',
  `ccdate` varchar(10) NOT NULL default '',
  `ccname` varchar(100) NOT NULL default '',
  `cvc_code` varchar(5) NOT NULL default '',
  `company_note` text NOT NULL,
  `is_reseller` tinyint(1) unsigned NOT NULL default '0',
  `register_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `customer_status` tinyint(1) unsigned NOT NULL default '0',
  `group_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`customerid`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_shop_discountgroup_count_name` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `unit` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_shop_discountgroup_count_rate` (
  `group_id` int(10) unsigned NOT NULL default '0',
  `count` int(10) unsigned NOT NULL default '1',
  `rate` decimal(5,2) unsigned NOT NULL default '0.00',
  PRIMARY KEY  (`group_id`,`count`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_shop_importimg` (
  `img_id` int(10) unsigned NOT NULL auto_increment,
  `img_name` varchar(255) NOT NULL default '',
  `img_cats` text NOT NULL,
  `img_fields_file` text NOT NULL,
  `img_fields_db` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`img_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_shop_lsv` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `order_id` int(10) unsigned NOT NULL default '0',
  `holder` tinytext NOT NULL,
  `bank` tinytext NOT NULL,
  `blz` tinytext NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `order_id` (`order_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_shop_mail` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tplname` varchar(60) NOT NULL default '',
  `protected` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_shop_mail_content` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tpl_id` int(10) unsigned NOT NULL default '0',
  `lang_id` int(10) unsigned NOT NULL default '0',
  `from_mail` varchar(255) NOT NULL default '',
  `xsender` varchar(255) NOT NULL default '',
  `subject` varchar(255) NOT NULL default '',
  `message` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_shop_manufacturer` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `url` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_shop_order_items` (
  `order_items_id` int(10) unsigned NOT NULL auto_increment,
  `orderid` int(10) unsigned NOT NULL default '0',
  `productid` varchar(100) NOT NULL default '',
  `product_name` varchar(100) NOT NULL default '',
  `price` decimal(9,2) NOT NULL default '0.00',
  `quantity` int(10) unsigned NOT NULL default '1',
  `vat_percent` decimal(5,2) unsigned default NULL,
  `weight` int(10) unsigned default NULL,
  PRIMARY KEY  (`order_items_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_shop_order_items_attributes` (
  `orders_items_attributes_id` int(10) unsigned NOT NULL auto_increment,
  `order_items_id` int(10) unsigned NOT NULL default '0',
  `order_id` int(10) unsigned NOT NULL default '0',
  `product_id` int(10) unsigned NOT NULL default '0',
  `product_option_name` varchar(32) NOT NULL default '',
  `product_option_value` varchar(32) NOT NULL default '',
  `product_option_values_price` decimal(9,2) NOT NULL default '0.00',
  PRIMARY KEY  (`orders_items_attributes_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_shop_orders` (
  `orderid` int(10) unsigned NOT NULL auto_increment,
  `customerid` int(10) unsigned NOT NULL default '0',
  `selected_currency_id` int(10) unsigned NOT NULL default '0',
  `order_sum` decimal(9,2) NOT NULL default '0.00',
  `currency_order_sum` decimal(9,2) NOT NULL default '0.00',
  `order_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `order_status` tinyint(1) unsigned NOT NULL default '0',
  `ship_prefix` varchar(50) NOT NULL default '',
  `ship_company` varchar(100) NOT NULL default '',
  `ship_firstname` varchar(40) NOT NULL default '',
  `ship_lastname` varchar(100) NOT NULL default '',
  `ship_address` varchar(40) NOT NULL default '',
  `ship_city` varchar(20) NOT NULL default '',
  `ship_zip` varchar(10) default NULL,
  `ship_country_id` int(10) unsigned default NULL,
  `ship_phone` varchar(20) NOT NULL default '',
  `tax_price` decimal(9,2) NOT NULL default '0.00',
  `currency_ship_price` decimal(9,2) NOT NULL default '0.00',
  `shipping_id` int(10) unsigned default NULL,
  `payment_id` int(10) unsigned default NULL,
  `currency_payment_price` decimal(9,2) NOT NULL default '0.00',
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
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_shop_payment` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(50) default NULL,
  `processor_id` int(10) unsigned NOT NULL default '0',
  `costs` decimal(9,2) NOT NULL default '0.00',
  `costs_free_sum` decimal(9,2) NOT NULL default '0.00',
  `sort_order` int(5) unsigned default '0',
  `status` tinyint(1) unsigned default '1',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_shop_payment_processors` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `type` enum('internal','external') NOT NULL default 'internal',
  `name` varchar(100) NOT NULL default '',
  `description` text NOT NULL,
  `company_url` varchar(255) NOT NULL default '',
  `status` tinyint(1) unsigned default '1',
  `picture` varchar(100) NOT NULL default '',
  `text` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_shop_pricelists` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(25) NOT NULL default '',
  `lang_id` int(10) unsigned NOT NULL default '0',
  `border_on` tinyint(1) unsigned NOT NULL default '1',
  `header_on` tinyint(1) unsigned NOT NULL default '1',
  `header_left` text,
  `header_right` text,
  `footer_on` tinyint(1) unsigned NOT NULL default '0',
  `footer_left` text,
  `footer_right` text,
  `categories` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_shop_products` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `product_id` varchar(100) NOT NULL,
  `picture` text NOT NULL,
  `title` varchar(255) NOT NULL default '',
  `catid` int(10) unsigned NOT NULL default '1',
  `handler` enum('none','delivery','download') NOT NULL default 'delivery',
  `normalprice` decimal(9,2) NOT NULL default '0.00',
  `resellerprice` decimal(9,2) NOT NULL default '0.00',
  `shortdesc` text NOT NULL,
  `description` text NOT NULL,
  `stock` int(10) NOT NULL default '10',
  `stock_visibility` tinyint(1) unsigned NOT NULL default '1',
  `discountprice` decimal(9,2) NOT NULL default '0.00',
  `is_special_offer` tinyint(1) unsigned NOT NULL default '0',
  `property1` varchar(100) default '',
  `property2` varchar(100) default '',
  `status` tinyint(1) unsigned NOT NULL default '1',
  `b2b` tinyint(1) unsigned NOT NULL default '1',
  `b2c` tinyint(1) unsigned NOT NULL default '1',
  `startdate` datetime NOT NULL default '0000-00-00 00:00:00',
  `enddate` datetime NOT NULL default '0000-00-00 00:00:00',
  `thumbnail_percent` tinyint(2) unsigned NOT NULL default '0',
  `thumbnail_quality` tinyint(2) unsigned NOT NULL default '0',
  `manufacturer` int(10) unsigned NOT NULL default '0',
  `manufacturer_url` varchar(255) NOT NULL default '',
  `external_link` varchar(255) NOT NULL default '',
  `sort_order` int(5) unsigned NOT NULL default '0',
  `vat_id` int(10) unsigned default NULL,
  `weight` int(10) unsigned default NULL,
  `flags` varchar(255) NOT NULL default '',
  `usergroups` varchar(255) NOT NULL default '',
  `group_id` int(10) unsigned default NULL,
  `article_id` int(10) unsigned default NULL,
  `keywords` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `group_id` (`group_id`),
  KEY `article_id` (`article_id`),
  FULLTEXT KEY `shopindex` (`title`,`description`),
  FULLTEXT KEY `flags` (`flags`),
  FULLTEXT KEY `keywords` (`keywords`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_shop_products_attributes` (
  `attribute_id` int(10) unsigned NOT NULL auto_increment,
  `product_id` int(10) unsigned NOT NULL default '0',
  `attributes_name_id` int(10) unsigned NOT NULL default '0',
  `attributes_value_id` int(10) unsigned NOT NULL default '0',
  `sort_id` int(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`attribute_id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_shop_products_attributes_name` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `display_type` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_shop_products_attributes_value` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name_id` int(10) unsigned NOT NULL default '0',
  `value` varchar(255) NOT NULL default '',
  `price` decimal(9,2) default '0.00',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_shop_products_downloads` (
  `products_downloads_id` int(10) unsigned NOT NULL default '0',
  `products_downloads_name` varchar(255) NOT NULL default '',
  `products_downloads_filename` varchar(255) NOT NULL default '',
  `products_downloads_maxdays` int(10) unsigned default '0',
  `products_downloads_maxcount` int(10) unsigned default '0',
  PRIMARY KEY  (`products_downloads_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_shop_rel_countries` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `zones_id` int(10) unsigned NOT NULL default '0',
  `countries_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_shop_rel_discount_group` (
  `customer_group_id` int(10) unsigned NOT NULL default '0',
  `article_group_id` int(10) unsigned NOT NULL default '0',
  `rate` decimal(9,2) NOT NULL default '0.00',
  PRIMARY KEY  (`customer_group_id`,`article_group_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_shop_rel_payment` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `zones_id` int(10) unsigned NOT NULL default '0',
  `payment_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_shop_rel_shipment` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `zones_id` int(10) unsigned NOT NULL default '0',
  `shipment_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_shop_shipment_cost` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `shipper_id` int(10) unsigned NOT NULL default '0',
  `max_weight` int(10) unsigned default NULL,
  `cost` decimal(10,2) unsigned default NULL,
  `price_free` decimal(10,2) unsigned default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_shop_shipper` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` tinytext NOT NULL,
  `status` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_shop_vat` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `class` tinytext NOT NULL,
  `percent` decimal(5,2) unsigned NOT NULL default '0.00',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_shop_zones` (
  `zones_id` int(10) unsigned NOT NULL auto_increment,
  `zones_name` varchar(64) NOT NULL default '',
  `activation_status` tinyint(1) unsigned NOT NULL default '1',
  PRIMARY KEY  (`zones_id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_u2u_address_list` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `buddies_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=InnoDB;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_u2u_message_log` (
  `message_id` int(11) unsigned NOT NULL auto_increment,
  `message_text` text NOT NULL,
  `message_title` text NOT NULL,
  PRIMARY KEY  (`message_id`)
) TYPE=InnoDB;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_u2u_sent_messages` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `userid` int(11) unsigned NOT NULL default '0',
  `message_id` int(11) unsigned NOT NULL default '0',
  `receiver_id` int(11) unsigned NOT NULL default '0',
  `mesage_open_status` enum('0','1') NOT NULL default '0',
  `date_time` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
) TYPE=InnoDB;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_u2u_settings` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=InnoDB;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_module_u2u_user_log` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `userid` int(11) unsigned NOT NULL default '0',
  `user_sent_items` int(11) unsigned NOT NULL default '0',
  `user_unread_items` int(11) unsigned NOT NULL default '0',
  `user_status` enum('0','1') NOT NULL default '1',
  PRIMARY KEY  (`id`)
) TYPE=InnoDB;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_modules` (
  `id` int(2) unsigned default NULL,
  `name` varchar(250) NOT NULL default '',
  `description_variable` varchar(50) NOT NULL default '',
  `status` set('y','n') NOT NULL default 'n',
  `is_required` tinyint(1) NOT NULL default '0',
  `is_core` tinyint(4) NOT NULL default '0',
  UNIQUE KEY `id` (`id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_sessions` (
  `sessionid` varchar(255) NOT NULL default '',
  `startdate` varchar(14) NOT NULL default '',
  `lastupdated` varchar(14) NOT NULL default '',
  `status` varchar(20) NOT NULL default '',
  `user_id` int(10) unsigned NOT NULL default '0',
  `datavalue` text,
  PRIMARY KEY  (`sessionid`),
  KEY `LastUpdated` (`lastupdated`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_settings` (
  `setid` int(6) unsigned NOT NULL auto_increment,
  `setname` varchar(250) NOT NULL default '',
  `setvalue` text NOT NULL,
  `setmodule` tinyint(2) NOT NULL default '0',
  PRIMARY KEY  (`setid`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_skins` (
  `id` int(2) unsigned NOT NULL auto_increment,
  `themesname` varchar(50) NOT NULL default '',
  `foldername` varchar(50) NOT NULL default '',
  `expert` int(1) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `theme_unique` (`themesname`),
  UNIQUE KEY `folder_unique` (`foldername`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_stats_browser` (
  `id` int(6) unsigned NOT NULL auto_increment,
  `name` varchar(255) binary NOT NULL default '',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique` (`name`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_stats_colourdepth` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `depth` tinyint(3) unsigned NOT NULL default '0',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique` (`depth`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_stats_config` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(64) NOT NULL default '',
  `value` varchar(255) NOT NULL default '',
  `status` int(1) default '1',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_stats_country` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `country` varchar(100) binary NOT NULL default '',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique` (`country`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_stats_hostname` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `hostname` varchar(255) binary NOT NULL default '',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique` (`hostname`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_stats_javascript` (
  `id` int(3) unsigned NOT NULL auto_increment,
  `support` enum('0','1') default '0',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_stats_operatingsystem` (
  `id` int(6) unsigned NOT NULL auto_increment,
  `name` varchar(255) binary NOT NULL default '',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique` (`name`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_stats_referer` (
  `id` int(8) unsigned NOT NULL auto_increment,
  `uri` varchar(255) binary NOT NULL default '',
  `timestamp` int(11) unsigned NOT NULL default '0',
  `count` mediumint(8) unsigned NOT NULL default '0',
  `sid` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique` (`uri`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_stats_requests` (
  `id` int(9) unsigned NOT NULL auto_increment,
  `timestamp` int(11) default '0',
  `pageId` int(6) unsigned NOT NULL default '0',
  `page` varchar(255) binary NOT NULL default '',
  `visits` int(9) unsigned NOT NULL default '0',
  `sid` varchar(32) NOT NULL default '',
  `pageTitle` varchar(250) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique` (`page`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_stats_requests_summary` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `type` varchar(10) NOT NULL default '',
  `timestamp` int(11) NOT NULL default '0',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique` (`type`,`timestamp`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_stats_screenresolution` (
  `id` int(5) unsigned NOT NULL auto_increment,
  `resolution` varchar(11) NOT NULL default '',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique` (`resolution`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_stats_search` (
  `id` int(5) unsigned NOT NULL auto_increment,
  `name` varchar(100) binary NOT NULL default '',
  `count` int(10) unsigned NOT NULL default '0',
  `sid` varchar(32) NOT NULL default '',
  `external` enum('0','1') NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique` (`name`,`external`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_stats_spiders` (
  `id` int(9) unsigned NOT NULL auto_increment,
  `last_indexed` int(14) default NULL,
  `page` varchar(100) binary default NULL,
  `pageId` mediumint(6) unsigned NOT NULL default '0',
  `count` int(11) NOT NULL default '0',
  `spider_useragent` varchar(255) default NULL,
  `spider_ip` varchar(100) default NULL,
  `spider_host` varchar(255) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique` (`page`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_stats_spiders_summary` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) binary NOT NULL default '',
  `timestamp` int(11) NOT NULL default '0',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique` (`name`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique` (`sid`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_stats_visitors_summary` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `type` varchar(10) NOT NULL default '',
  `timestamp` int(11) NOT NULL default '0',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique` (`type`,`timestamp`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_voting_additionaldata` (
  `id` int(11) NOT NULL auto_increment,
  `nickname` varchar(80) NOT NULL default '',
  `surname` varchar(80) NOT NULL default '',
  `phone` varchar(80) NOT NULL default '',
  `street` varchar(80) NOT NULL default '',
  `zip` varchar(30) NOT NULL default '',
  `city` varchar(80) NOT NULL default '',
  `email` varchar(80) NOT NULL default '',
  `comment` text NOT NULL,
  `voting_system_id` int(11) NOT NULL default '0',
  `date_entered` timestamp NOT NULL,
  `forename` varchar(80) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `voting_system_id` (`voting_system_id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_voting_email` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `email` varchar(255) NOT NULL,
  `valid` enum('0','1') NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `email` (`email`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_voting_rel_email_system` (
  `email_id` int(10) unsigned NOT NULL default '0',
  `system_id` int(10) unsigned NOT NULL default '0',
  `voting_id` int(10) unsigned NOT NULL default '0',
  `valid` enum('0','1') NOT NULL default '0',
  UNIQUE KEY `email_id` (`email_id`,`system_id`)
) TYPE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_voting_results` (
  `id` int(11) NOT NULL auto_increment,
  `voting_system_id` int(11) default NULL,
  `question` char(200) default NULL,
  `votes` int(11) default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_voting_system` (
  `id` int(11) NOT NULL auto_increment,
  `date` timestamp NOT NULL,
  `title` varchar(60) NOT NULL default '',
  `question` text,
  `status` tinyint(1) default '1',
  `submit_check` enum('cookie','email') NOT NULL default 'cookie',
  `votes` int(11) default '0',
  `additional_nickname` tinyint(1) NOT NULL default '0',
  `additional_forename` tinyint(1) NOT NULL default '0',
  `additional_surname` tinyint(1) NOT NULL default '0',
  `additional_phone` tinyint(1) NOT NULL default '0',
  `additional_street` tinyint(1) NOT NULL default '0',
  `additional_zip` tinyint(1) NOT NULL default '0',
  `additional_email` tinyint(1) NOT NULL default '0',
  `additional_city` tinyint(1) NOT NULL default '0',
  `additional_comment` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
SET character_set_client = @saved_cs_client;



####################################################################################################
############# NEW CONTACT MANAGER ##################################################################
####################################################################################################
CREATE TABLE `contrexx_module_contact_form` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `mails` text NOT NULL,
  `showForm` tinyint(1) unsigned NOT NULL default '0',
  `use_captcha` tinyint(1) unsigned NOT NULL default '1',
  `use_custom_style` tinyint(1) unsigned NOT NULL default '0',
  `send_copy` tinyint(1) NOT NULL default '0',
  `html_mail` tinyint(1) unsigned NOT NULL default '1',
  `send_attachment` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
CREATE TABLE `contrexx_module_contact_form_data` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_form` int(10) unsigned NOT NULL default '0',
  `id_lang` int(10) unsigned NOT NULL default '1',
  `time` int(14) unsigned NOT NULL default '0',
  `host` varchar(255) NOT NULL default '',
  `lang` varchar(64) NOT NULL default '',
  `browser` varchar(255) NOT NULL default '',
  `ipaddress` varchar(15) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;
CREATE TABLE `contrexx_module_contact_form_field` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_form` int(10) unsigned NOT NULL default '0',
  `type` enum('text','label','checkbox','checkboxGroup','country','date','file','fieldset','hidden','horizontalLine','password','radio','select','textarea','recipient','special') NOT NULL default 'text',
  `special_type` varchar(20) NOT NULL,
  `is_required` set('0','1') NOT NULL default '0',
  `check_type` int(3) NOT NULL default '1',
  `order_id` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
CREATE TABLE `contrexx_module_contact_form_field_lang` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `fieldID` int(10) unsigned NOT NULL,
  `langID` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `attributes` text NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `fieldID` (`fieldID`,`langID`)
) TYPE=MyISAM ;
CREATE TABLE `contrexx_module_contact_form_lang` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `formID` int(10) unsigned NOT NULL,
  `langID` int(10) unsigned NOT NULL,
  `is_active` tinyint(1) unsigned NOT NULL default '1',
  `name` varchar(255) NOT NULL,
  `text` text NOT NULL,
  `feedback` text NOT NULL,
  `mailTemplate` text NOT NULL,
  `subject` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `formID` (`formID`,`langID`)
) TYPE=MyISAM ;
CREATE TABLE `contrexx_module_contact_form_submit_data` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_entry` int(10) unsigned NOT NULL,
  `id_field` int(10) unsigned NOT NULL,
  `formlabel` text NOT NULL,
  `formvalue` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;
CREATE TABLE `contrexx_module_contact_recipient` (
  `id` int(11) NOT NULL auto_increment,
  `id_form` int(11) NOT NULL default '0',
  `email` varchar(250) NOT NULL default '',
  `sort` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
CREATE TABLE `contrexx_module_contact_recipient_lang` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `recipient_id` int(10) unsigned NOT NULL,
  `langID` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `recipient_id` (`recipient_id`,`langID`)
) TYPE=MyISAM ;
CREATE TABLE `contrexx_module_contact_settings` (
  `setid` int(6) unsigned NOT NULL auto_increment,
  `setname` varchar(250) NOT NULL default '',
  `setvalue` text NOT NULL,
  `status` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`setid`)
) TYPE=MyISAM ;



####################################################################################################
############# NEW CONTENT MANAGER (Doctrine) #######################################################
####################################################################################################

CREATE TABLE `contrexx_nodes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `lft` int(11) NOT NULL,
  `rgt` int(11) NOT NULL,
  `lvl` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_E5A18FDD727ACA70` (`parent_id`),
  CONSTRAINT `contrexx_nodes_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `contrexx_nodes` (`id`) ON DELETE SET NULL
) TYPE=InnoDB AUTO_INCREMENT=432;

CREATE TABLE `contrexx_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `node_id` int(11) DEFAULT NULL,
  `lang` int(11) NOT NULL,
  `type` varchar(16) NOT NULL,
  `caching` tinyint(1) NOT NULL,
  `updatedAt` datetime NOT NULL,
  `title` varchar(255) NOT NULL,
  `contentTitle` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `customContent` varchar(64) DEFAULT NULL,
  `cssName` varchar(255) DEFAULT NULL,
  `skin` int(11) DEFAULT NULL,
  `metatitle` varchar(255) DEFAULT NULL,
  `metadesc` varchar(255) DEFAULT NULL,
  `metakeys` varchar(255) DEFAULT NULL,
  `metarobots` varchar(7) DEFAULT NULL,
  `start` datetime DEFAULT NULL,
  `end` datetime DEFAULT NULL,
  `editingStatus` varchar(16) NOT NULL,
  `username` varchar(64) NOT NULL,
  `protection` int(11) NOT NULL,
  `display` tinyint(1) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `target` varchar(255) DEFAULT NULL,
  `module` varchar(255) DEFAULT NULL,
  `cmd` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_D8E86F54460D9FD7` (`node_id`),
  CONSTRAINT `contrexx_pages_ibfk_1` FOREIGN KEY (`node_id`) REFERENCES `contrexx_nodes` (`id`)
) TYPE=InnoDB AUTO_INCREMENT=702;

CREATE TABLE `contrexx_ext_log_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` varchar(8) NOT NULL,
  `logged_at` datetime NOT NULL,
  `version` int(11) NOT NULL,
  `object_id` varchar(32) DEFAULT NULL,
  `object_class` varchar(255) NOT NULL,
  `data` longtext,
  `username` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `log_class_lookup_idx` (`object_class`),
  KEY `log_date_lookup_idx` (`logged_at`),
  KEY `log_user_lookup_idx` (`username`)
) TYPE=InnoDB AUTO_INCREMENT=962;

####################################################################################################
############# NEW BLOCK SYSTEM #####################################################################
####################################################################################################
CREATE TABLE `contrexx_module_block_blocks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `start` int(10) NOT NULL DEFAULT '0',
  `end` int(10) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `random` int(1) NOT NULL DEFAULT '0',
  `random_2` int(1) NOT NULL DEFAULT '0',
  `random_3` int(1) NOT NULL DEFAULT '0',
  `random_4` int(1) NOT NULL DEFAULT '0',
  `global` int(1) NOT NULL DEFAULT '0',
  `active` int(1) NOT NULL DEFAULT '0',
  `order` int(1) NOT NULL DEFAULT '0',
  `cat` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) TYPE=MyISAM;
CREATE TABLE `contrexx_module_block_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent` int(10) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `order` int(10) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) TYPE=MyISAM;
CREATE TABLE `contrexx_module_block_rel_lang_content` (
  `block_id` int(10) unsigned NOT NULL DEFAULT '0',
  `lang_id` int(10) unsigned NOT NULL DEFAULT '0',
  `content` mediumtext NOT NULL,
  `active` int(1) NOT NULL DEFAULT '0'
) TYPE=MyISAM;
CREATE TABLE `contrexx_module_block_rel_pages` (
  `block_id` int(7) NOT NULL DEFAULT '0',
  `page_id` int(7) NOT NULL DEFAULT '0'
) TYPE=MyISAM;
CREATE TABLE `contrexx_module_block_settings` (
  `id` int(7) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `value` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) TYPE=MyISAM;





####################################################################################################
############# NEW NEWS SYSTEM ######################################################################
####################################################################################################
CREATE TABLE `contrexx_module_news` (
  `id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `date` int(14) DEFAULT NULL,
  `redirect` varchar(250) NOT NULL DEFAULT '',
  `source` varchar(250) NOT NULL DEFAULT '',
  `url1` varchar(250) NOT NULL DEFAULT '',
  `url2` varchar(250) NOT NULL DEFAULT '',
  `catid` int(2) unsigned NOT NULL DEFAULT '0',
  `typeid` int(2) unsigned NOT NULL DEFAULT '0',
  `publisher` varchar(255) NOT NULL DEFAULT '',
  `publisher_id` int(5) unsigned NOT NULL DEFAULT '0',
  `author` varchar(255) NOT NULL DEFAULT '',
  `author_id` int(5) unsigned NOT NULL DEFAULT '0',
  `userid` int(6) unsigned NOT NULL DEFAULT '0',
  `startdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `enddate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `validated` enum('0','1') NOT NULL DEFAULT '0',
  `frontend_access_id` int(10) unsigned NOT NULL DEFAULT '0',
  `backend_access_id` int(10) unsigned NOT NULL DEFAULT '0',
  `teaser_only` enum('0','1') NOT NULL DEFAULT '0',
  `teaser_frames` text NOT NULL,
  `teaser_show_link` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `teaser_image_path` text NOT NULL,
  `teaser_image_thumbnail_path` text NOT NULL,
  `changelog` int(14) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) TYPE=MyISAM;
CREATE TABLE `contrexx_module_news_categories` (
  `catid` int(2) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`catid`)
) TYPE=MyISAM;
CREATE TABLE `contrexx_module_news_categories_locale` (
  `category_id` int(11) unsigned NOT NULL DEFAULT '0',
  `lang_id` int(11) unsigned NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`category_id`,`lang_id`),
  FULLTEXT KEY `name` (`name`)
) TYPE=MyISAM;
CREATE TABLE `contrexx_module_news_comments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(250) NOT NULL DEFAULT '',
  `text` mediumtext NOT NULL,
  `newsid` int(6) unsigned NOT NULL DEFAULT '0',
  `date` int(14) DEFAULT NULL,
  `poster_name` varchar(255) NOT NULL DEFAULT '',
  `userid` int(5) unsigned NOT NULL DEFAULT '0',
  `ip_address` varchar(15) NOT NULL DEFAULT '0.0.0.0',
  `is_active` enum('0','1') NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) TYPE=MyISAM;
CREATE TABLE `contrexx_module_news_locale` (
  `news_id` int(11) unsigned NOT NULL DEFAULT '0',
  `lang_id` int(11) unsigned NOT NULL DEFAULT '0',
  `is_active` int(1) unsigned NOT NULL DEFAULT '1',
  `title` varchar(250) NOT NULL DEFAULT '',
  `text` mediumtext NOT NULL,
  `teaser_text` text NOT NULL,
  PRIMARY KEY (`news_id`,`lang_id`),
  FULLTEXT KEY `title` (`title`),
  FULLTEXT KEY `text` (`text`),
  FULLTEXT KEY `teaser_text` (`teaser_text`)
) TYPE=MyISAM;
CREATE TABLE `contrexx_module_news_settings` (
  `name` varchar(50) NOT NULL DEFAULT '',
  `value` varchar(250) NOT NULL DEFAULT '',
  KEY `name` (`name`)
) TYPE=MyISAM;
CREATE TABLE `contrexx_module_news_settings_locale` (
  `name` varchar(50) NOT NULL DEFAULT '',
  `lang_id` int(11) unsigned NOT NULL DEFAULT '0',
  `value` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`name`,`lang_id`),
  FULLTEXT KEY `name` (`name`)
) TYPE=MyISAM;
CREATE TABLE `contrexx_module_news_stats_view` (
  `user_sid` char(32) NOT NULL,
  `news_id` int(6) unsigned NOT NULL,
  `time` timestamp NOT NULL,
  KEY `idx_user_sid` (`user_sid`),
  KEY `idx_news_id` (`news_id`)
) TYPE=MyISAM;
CREATE TABLE `contrexx_module_news_teaser_frame` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lang_id` int(3) unsigned NOT NULL DEFAULT '0',
  `frame_template_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) TYPE=MyISAM;
CREATE TABLE `contrexx_module_news_teaser_frame_templates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL DEFAULT '',
  `html` text NOT NULL,
  `source_code_mode` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) TYPE=MyISAM;
CREATE TABLE `contrexx_module_news_ticker` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `charset` enum('ISO-8859-1','UTF-8') NOT NULL DEFAULT 'ISO-8859-1',
  `urlencode` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `prefix` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) TYPE=MyISAM;
CREATE TABLE `contrexx_module_news_types` (
  `typeid` int(2) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`typeid`)
) TYPE=MyISAM;
CREATE TABLE `contrexx_module_news_types_locale` (
  `lang_id` int(11) unsigned NOT NULL DEFAULT '0',
  `type_id` int(11) unsigned NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`lang_id`,`type_id`),
  FULLTEXT KEY `name` (`name`)
) TYPE=MyISAM;





####################################################################################################
############# NEW NEWSLETTER SYSTEM ################################################################
####################################################################################################
CREATE TABLE `contrexx_module_newsletter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) NOT NULL DEFAULT '',
  `template` int(11) NOT NULL DEFAULT '0',
  `content` text NOT NULL,
  `content_text` text NOT NULL,
  `attachment` enum('0','1') NOT NULL DEFAULT '0',
  `format` enum('text','html','html/text') NOT NULL DEFAULT 'text',
  `priority` tinyint(1) NOT NULL DEFAULT '0',
  `sender_email` varchar(255) NOT NULL DEFAULT '',
  `sender_name` varchar(255) NOT NULL DEFAULT '',
  `return_path` varchar(255) NOT NULL DEFAULT '',
  `smtp_server` int(10) unsigned NOT NULL DEFAULT '0',
  `status` int(1) NOT NULL DEFAULT '0',
  `count` int(11) NOT NULL DEFAULT '0',
  `recipient_count` int(11) unsigned NOT NULL DEFAULT '0',
  `date_create` int(14) unsigned NOT NULL DEFAULT '0',
  `date_sent` int(14) unsigned NOT NULL DEFAULT '0',
  `tmp_copy` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) TYPE=MyISAM;
CREATE TABLE `contrexx_module_newsletter_access_user` (
  `accessUserID` int(5) unsigned NOT NULL,
  `newsletterCategoryID` int(11) NOT NULL,
  UNIQUE KEY `rel` (`accessUserID`,`newsletterCategoryID`),
  KEY `accessUserID` (`accessUserID`)
) TYPE=MyISAM;
CREATE TABLE `contrexx_module_newsletter_attachment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `newsletter` int(11) NOT NULL DEFAULT '0',
  `file_name` varchar(255) NOT NULL DEFAULT '',
  `file_nr` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `newsletter` (`newsletter`)
) TYPE=MyISAM;
CREATE TABLE `contrexx_module_newsletter_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `notification_email` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) TYPE=MyISAM;
CREATE TABLE `contrexx_module_newsletter_confirm_mail` (
  `id` int(1) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `content` longtext NOT NULL,
  `recipients` text NOT NULL,
  PRIMARY KEY (`id`)
) TYPE=MyISAM;
CREATE TABLE `contrexx_module_newsletter_email_link` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `email_id` int(11) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `email_id` (`email_id`)
) TYPE=MyISAM;
CREATE TABLE `contrexx_module_newsletter_email_link_feedback` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `link_id` int(11) unsigned NOT NULL,
  `email_id` int(11) unsigned NOT NULL,
  `recipient_id` int(11) unsigned NOT NULL,
  `recipient_type` enum('access','newsletter') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `link_id` (`link_id`,`email_id`,`recipient_id`,`recipient_type`),
  KEY `email_id` (`email_id`)
) TYPE=MyISAM;
CREATE TABLE `contrexx_module_newsletter_rel_cat_news` (
  `newsletter` int(11) NOT NULL DEFAULT '0',
  `category` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`newsletter`,`category`)
) TYPE=MyISAM;
CREATE TABLE `contrexx_module_newsletter_rel_user_cat` (
  `user` int(11) NOT NULL DEFAULT '0',
  `category` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user`,`category`)
) TYPE=MyISAM;
CREATE TABLE `contrexx_module_newsletter_rel_usergroup_newsletter` (
  `userGroup` int(10) unsigned NOT NULL,
  `newsletter` int(10) unsigned NOT NULL,
  UNIQUE KEY `uniq` (`userGroup`,`newsletter`)
) TYPE=MyISAM;
CREATE TABLE `contrexx_module_newsletter_settings` (
  `setid` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `setname` varchar(250) NOT NULL DEFAULT '',
  `setvalue` text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`setid`),
  UNIQUE KEY `setname` (`setname`)
) TYPE=MyISAM;
CREATE TABLE `contrexx_module_newsletter_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `html` text NOT NULL,
  `text` text NOT NULL,
  `required` int(1) NOT NULL DEFAULT '0',
  `type` enum('e-mail','news') NOT NULL DEFAULT 'e-mail',
  PRIMARY KEY (`id`)
) TYPE=MyISAM;
CREATE TABLE `contrexx_module_newsletter_tmp_sending` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `newsletter` int(11) NOT NULL DEFAULT '0',
  `email` varchar(255) NOT NULL DEFAULT '',
  `sendt` tinyint(1) NOT NULL DEFAULT '0',
  `type` enum('access','newsletter') NOT NULL DEFAULT 'newsletter',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_email` (`newsletter`,`email`),
  KEY `email` (`email`)
) TYPE=MyISAM;
CREATE TABLE `contrexx_module_newsletter_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `uri` varchar(255) NOT NULL DEFAULT '',
  `sex` enum('m','f') DEFAULT NULL,
  `salutation` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `lastname` varchar(255) NOT NULL DEFAULT '',
  `firstname` varchar(255) NOT NULL DEFAULT '',
  `position` varchar(255) NOT NULL DEFAULT '',
  `company` varchar(255) NOT NULL DEFAULT '',
  `industry_sector` varchar(255) NOT NULL DEFAULT '',
  `address` varchar(255) NOT NULL DEFAULT '',
  `zip` varchar(255) NOT NULL DEFAULT '',
  `city` varchar(255) NOT NULL DEFAULT '',
  `country_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `phone_office` varchar(255) NOT NULL DEFAULT '',
  `phone_private` varchar(255) NOT NULL DEFAULT '',
  `phone_mobile` varchar(255) NOT NULL DEFAULT '',
  `fax` varchar(255) NOT NULL DEFAULT '',
  `notes` text NOT NULL,
  `birthday` varchar(10) NOT NULL DEFAULT '00-00-0000',
  `status` int(1) NOT NULL DEFAULT '0',
  `emaildate` int(14) unsigned NOT NULL DEFAULT '0',
  `language` int(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `status` (`status`)
) TYPE=MyISAM;
CREATE TABLE `contrexx_module_newsletter_user_title` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `title` (`title`)
) TYPE=MyISAM;
