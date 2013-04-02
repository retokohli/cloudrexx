SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_access_group_dynamic_ids` (
  `access_id` int(11) unsigned NOT NULL default '0',
  `group_id` int(11) unsigned NOT NULL default '0'
) ENGINE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_access_group_static_ids` (
  `access_id` int(11) unsigned NOT NULL default '0',
  `group_id` int(11) unsigned NOT NULL default '0'
) ENGINE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_access_rel_user_group` (
  `user_id` int(10) unsigned NOT NULL default '0',
  `group_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`user_id`,`group_id`)
) ENGINE=InnoDB;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_access_settings` (
  `key` varchar(32) NOT NULL default '',
  `value` varchar(255) NOT NULL default '',
  `status` tinyint(1) unsigned NOT NULL default '0',
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB;
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
) ENGINE=InnoDB;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_access_user_attribute_name` (
  `attribute_id` int(10) unsigned NOT NULL default '0',
  `lang_id` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`attribute_id`,`lang_id`)
) ENGINE=InnoDB;
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
) ENGINE=MyISAM;
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
) ENGINE=InnoDB;
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
) ENGINE=MyISAM ;
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
) ENGINE=InnoDB;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_access_user_network` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `oauth_provider` varchar(100) NOT NULL default '',
  `oauth_id` varchar(100) NOT NULL default '',
  `user_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB;
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
  `interests` text NOT NULL,
  `signature` text NOT NULL,
  `picture` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`user_id`),
  KEY `profile` (`firstname`(100),`lastname`(100),`company`(50))
) ENGINE=InnoDB;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_access_user_title` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `order_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `title` (`title`)
) ENGINE=InnoDB;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_access_user_validity` (
  `validity` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`validity`)
) ENGINE=InnoDB;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_access_users` (
  `id` int(5) unsigned NOT NULL auto_increment,
  `is_admin` tinyint(1) unsigned NOT NULL default '0',
  `username` varchar(255) default NULL,
  `password` varchar(32) default NULL,
  `regdate` int(14) unsigned NOT NULL default '0',
  `expiration` int(14) unsigned NOT NULL default '0',
  `validity` int(10) unsigned NOT NULL default '0',
  `last_auth` int(14) unsigned NOT NULL default '0',
  `last_auth_status` int(1) NOT NULL default '1',
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
) ENGINE=MyISAM ;
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
  KEY `area_name` (`area_name`)
) ENGINE=MyISAM ;
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
) ENGINE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_content_node` (
  `id` int(11) NOT NULL auto_increment,
  `parent_id` int(11) default NULL,
  `lft` int(11) NOT NULL,
  `rgt` int(11) NOT NULL,
  `lvl` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `IDX_E5A18FDD727ACA70` (`parent_id`),
  CONSTRAINT `contrexx_content_node_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `contrexx_content_node` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_content_page` (
  `id` int(11) NOT NULL auto_increment,
  `node_id` int(11) default NULL,
  `nodeIdShadowed` int(11) default NULL,
  `lang` int(11) NOT NULL,
  `type` varchar(16) NOT NULL,
  `caching` tinyint(1) NOT NULL,
  `updatedAt` timestamp NULL default NULL,
  `updatedBy` char(40) NOT NULL,
  `title` varchar(255) NOT NULL,
  `linkTarget` varchar(16) default NULL,
  `contentTitle` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `sourceMode` tinyint(1) NOT NULL default '0',
  `customContent` varchar(64) default NULL,
  `cssName` varchar(255) default NULL,
  `cssNavName` varchar(255) default NULL,
  `skin` int(11) default NULL,
  `metatitle` varchar(255) default NULL,
  `metadesc` text NOT NULL,
  `metakeys` text NOT NULL,
  `metarobots` varchar(7) default NULL,
  `start` timestamp NULL default NULL,
  `end` timestamp NULL default NULL,
  `editingStatus` varchar(16) NOT NULL,
  `protection` int(11) NOT NULL,
  `frontendAccessId` int(11) NOT NULL,
  `backendAccessId` int(11) NOT NULL,
  `display` tinyint(1) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `target` varchar(255) default NULL,
  `module` varchar(255) default NULL,
  `cmd` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `node_id` (`node_id`,`lang`),
  KEY `IDX_D8E86F54460D9FD7` (`node_id`),
  CONSTRAINT `contrexx_content_page_ibfk_1` FOREIGN KEY (`node_id`) REFERENCES `contrexx_content_node` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_core_country` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `alpha2` char(2) NOT NULL default '',
  `alpha3` char(3) NOT NULL default '',
  `ord` int(5) unsigned NOT NULL default '0',
  `active` tinyint(1) unsigned NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM ;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_core_mail_template` (
  `key` tinytext NOT NULL,
  `section` tinytext NOT NULL,
  `text_id` int(10) unsigned NOT NULL,
  `html` tinyint(1) unsigned NOT NULL default '0',
  `protected` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`key`(32),`section`(32))
) ENGINE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_core_setting` (
  `section` varchar(32) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `group` varchar(32) NOT NULL default '',
  `type` varchar(32) NOT NULL default 'text',
  `value` text NOT NULL,
  `values` text NOT NULL,
  `ord` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`section`,`name`,`group`)
) ENGINE=MyISAM;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_core_text` (
  `id` int(10) unsigned NOT NULL default '0',
  `lang_id` int(10) unsigned NOT NULL default '1',
  `section` varchar(32) NOT NULL default '',
  `key` varchar(255) NOT NULL,
  `text` text NOT NULL,
  PRIMARY KEY  (`id`,`lang_id`,`section`,`key`(32)),
  FULLTEXT KEY `text` (`text`)
) ENGINE=MyISAM;
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
) ENGINE=MyISAM;
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
  `fallback` int(2) unsigned NOT NULL default '0',
  `app_themes_id` int(2) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `lang` (`lang`),
  KEY `defaultstatus` (`is_default`)
) ENGINE=MyISAM ;
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
) ENGINE=InnoDB;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contrexx_log` (
  `id` int(6) unsigned NOT NULL auto_increment,
  `userid` int(6) unsigned default NULL,
  `datetime` timestamp NULL default '0000-00-00 00:00:00',
  `useragent` varchar(250) default NULL,
  `userlanguage` varchar(250) default NULL,
  `remote_addr` varchar(250) default NULL,
  `remote_host` varchar(250) default NULL,
  `http_via` varchar(250) NOT NULL default '',
  `http_client_ip` varchar(250) NOT NULL default '',
  `http_x_forwarded_for` varchar(250) NOT NULL default '',
  `referer` varchar(250) NOT NULL default '',
 