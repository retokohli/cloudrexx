SET FOREIGN_KEY_CHECKS = 0;
CREATE TABLE `contrexx_access_group_dynamic_ids` (
  `access_id` int(11) unsigned NOT NULL DEFAULT '0',
  `group_id` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`access_id`,`group_id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_access_group_static_ids` (
  `access_id` int(11) unsigned NOT NULL DEFAULT '0',
  `group_id` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`access_id`,`group_id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_access_rel_user_group` (
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `group_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`group_id`)
) ENGINE=InnoDB;
CREATE TABLE `contrexx_access_settings` (
  `key` varchar(32) NOT NULL DEFAULT '',
  `value` varchar(255) NOT NULL DEFAULT '',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB;
CREATE TABLE `contrexx_access_user_attribute` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
  `type` enum('text','textarea','mail','uri','date','image','checkbox','menu','menu_option','group','frame','history') NOT NULL DEFAULT 'text',
  `mandatory` enum('0','1') NOT NULL DEFAULT '0',
  `sort_type` enum('asc','desc','custom') NOT NULL DEFAULT 'asc',
  `order_id` int(10) unsigned NOT NULL DEFAULT '0',
  `access_special` enum('','menu_select_higher','menu_select_lower') NOT NULL DEFAULT '',
  `access_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB ;
CREATE TABLE `contrexx_access_user_attribute_name` (
  `attribute_id` int(10) unsigned NOT NULL DEFAULT '0',
  `lang_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`attribute_id`,`lang_id`)
) ENGINE=InnoDB;
CREATE TABLE `contrexx_access_user_attribute_value` (
  `attribute_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `history_id` int(10) unsigned NOT NULL DEFAULT '0',
  `value` text NOT NULL,
  PRIMARY KEY (`attribute_id`,`user_id`,`history_id`),
  FULLTEXT KEY `value` (`value`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_access_user_core_attribute` (
  `id` varchar(25) NOT NULL,
  `mandatory` enum('0','1') NOT NULL DEFAULT '0',
  `sort_type` enum('asc','desc','custom') NOT NULL DEFAULT 'asc',
  `order_id` int(10) unsigned NOT NULL DEFAULT '0',
  `access_special` enum('','menu_select_higher','menu_select_lower') NOT NULL DEFAULT '',
  `access_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
CREATE TABLE `contrexx_access_user_groups` (
  `group_id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `group_name` varchar(100) NOT NULL DEFAULT '',
  `group_description` varchar(255) NOT NULL DEFAULT '',
  `is_active` tinyint(4) NOT NULL DEFAULT '1',
  `type` enum('frontend','backend') NOT NULL DEFAULT 'frontend',
  `homepage` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`group_id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_access_user_mail` (
  `type` enum('reg_confirm','reset_pw','user_activated','user_deactivated','new_user') NOT NULL DEFAULT 'reg_confirm',
  `lang_id` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `sender_mail` varchar(255) NOT NULL DEFAULT '',
  `sender_name` varchar(255) NOT NULL DEFAULT '',
  `subject` varchar(255) NOT NULL DEFAULT '',
  `format` enum('text','html','multipart') NOT NULL DEFAULT 'text',
  `body_text` text NOT NULL,
  `body_html` text NOT NULL,
  UNIQUE KEY `mail` (`type`,`lang_id`)
) ENGINE=InnoDB;
CREATE TABLE `contrexx_access_user_network` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `oauth_provider` varchar(100) NOT NULL DEFAULT '',
  `oauth_id` varchar(100) NOT NULL DEFAULT '',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
CREATE TABLE `contrexx_access_user_profile` (
  `user_id` int(5) unsigned NOT NULL DEFAULT '0',
  `gender` enum('gender_undefined','gender_female','gender_male') NOT NULL DEFAULT 'gender_undefined',
  `title` int(10) unsigned NOT NULL DEFAULT '0',
  `firstname` varchar(255) NOT NULL DEFAULT '',
  `lastname` varchar(255) NOT NULL DEFAULT '',
  `company` varchar(255) NOT NULL DEFAULT '',
  `address` varchar(255) NOT NULL DEFAULT '',
  `city` varchar(50) NOT NULL DEFAULT '',
  `zip` varchar(10) NOT NULL DEFAULT '',
  `country` smallint(5) unsigned NOT NULL DEFAULT '0',
  `phone_office` varchar(20) NOT NULL DEFAULT '',
  `phone_private` varchar(20) NOT NULL DEFAULT '',
  `phone_mobile` varchar(20) NOT NULL DEFAULT '',
  `phone_fax` varchar(20) NOT NULL DEFAULT '',
  `birthday` varchar(11) DEFAULT NULL,
  `website` varchar(255) NOT NULL DEFAULT '',
  `profession` varchar(150) NOT NULL DEFAULT '',
  `interests` text,
  `signature` text,
  `picture` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`user_id`),
  KEY `profile` (`firstname`(100),`lastname`(100),`company`(50))
) ENGINE=InnoDB;
CREATE TABLE `contrexx_access_user_title` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `order_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `title` (`title`)
) ENGINE=InnoDB ;
CREATE TABLE `contrexx_access_user_validity` (
  `validity` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`validity`)
) ENGINE=InnoDB;
CREATE TABLE `contrexx_access_users` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `is_admin` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(32) DEFAULT NULL,
  `regdate` int(14) unsigned NOT NULL DEFAULT '0',
  `expiration` int(14) unsigned NOT NULL DEFAULT '0',
  `validity` int(10) unsigned NOT NULL DEFAULT '0',
  `last_auth` int(14) unsigned NOT NULL DEFAULT '0',
  `last_auth_status` int(1) NOT NULL DEFAULT '1',
  `last_activity` int(14) unsigned NOT NULL DEFAULT '0',
  `email` varchar(255) DEFAULT NULL,
  `email_access` enum('everyone','members_only','nobody') NOT NULL DEFAULT 'nobody',
  `frontend_lang_id` int(2) unsigned NOT NULL DEFAULT '0',
  `backend_lang_id` int(2) unsigned NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `primary_group` int(6) unsigned NOT NULL DEFAULT '0',
  `profile_access` enum('everyone','members_only','nobody') NOT NULL DEFAULT 'members_only',
  `restore_key` varchar(32) NOT NULL DEFAULT '',
  `restore_key_time` int(14) unsigned NOT NULL DEFAULT '0',
  `u2u_active` enum('0','1') NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `username` (`username`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_backend_areas` (
  `area_id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `parent_area_id` int(6) unsigned NOT NULL DEFAULT '0',
  `type` enum('group','function','navigation') DEFAULT 'navigation',
  `scope` enum('global','frontend','backend') NOT NULL DEFAULT 'global',
  `area_name` varchar(100) DEFAULT NULL,
  `is_active` tinyint(4) NOT NULL DEFAULT '1',
  `uri` varchar(255) NOT NULL DEFAULT '',
  `target` varchar(50) NOT NULL DEFAULT '_self',
  `module_id` int(6) unsigned NOT NULL DEFAULT '0',
  `order_id` int(6) unsigned NOT NULL DEFAULT '0',
  `access_id` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`area_id`),
  KEY `area_name` (`area_name`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_backups` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `date` varchar(14) NOT NULL DEFAULT '',
  `version` varchar(20) NOT NULL DEFAULT '',
  `edition` varchar(30) NOT NULL DEFAULT '',
  `type` enum('sql','csv') NOT NULL DEFAULT 'sql',
  `description` varchar(100) NOT NULL DEFAULT '',
  `usedtables` text NOT NULL,
  `size` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `date` (`date`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_component` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `type` enum('core','core_module','module') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB ;
CREATE TABLE `contrexx_content_node` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `lft` int(11) NOT NULL,
  `rgt` int(11) NOT NULL,
  `lvl` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_E5A18FDD727ACA70` (`parent_id`),
  CONSTRAINT `contrexx_content_node_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `contrexx_content_node` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB ;
CREATE TABLE `contrexx_content_page` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `node_id` int(11) DEFAULT NULL,
  `nodeIdShadowed` int(11) DEFAULT NULL,
  `lang` int(11) NOT NULL,
  `type` varchar(16) NOT NULL,
  `caching` tinyint(1) NOT NULL,
  `updatedAt` timestamp NULL DEFAULT NULL,
  `updatedBy` char(40) NOT NULL,
  `title` varchar(255) NOT NULL,
  `linkTarget` varchar(16) DEFAULT NULL,
  `contentTitle` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `sourceMode` tinyint(1) NOT NULL DEFAULT '0',
  `customContent` varchar(64) DEFAULT NULL,
  `useCustomContentForAllChannels` int(2) DEFAULT NULL,
  `cssName` varchar(255) DEFAULT NULL,
  `cssNavName` varchar(255) DEFAULT NULL,
  `skin` int(11) DEFAULT NULL,
  `useSkinForAllChannels` int(2) DEFAULT NULL,
  `metatitle` varchar(255) DEFAULT NULL,
  `metadesc` text NOT NULL,
  `metakeys` text NOT NULL,
  `metarobots` varchar(7) DEFAULT NULL,
  `start` timestamp NULL DEFAULT NULL,
  `end` timestamp NULL DEFAULT NULL,
  `editingStatus` varchar(16) NOT NULL,
  `protection` int(11) NOT NULL,
  `frontendAccessId` int(11) NOT NULL,
  `backendAccessId` int(11) NOT NULL,
  `display` tinyint(1) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `target` varchar(255) DEFAULT NULL,
  `module` varchar(255) DEFAULT NULL,
  `cmd` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `node_id` (`node_id`,`lang`),
  KEY `IDX_D8E86F54460D9FD7` (`node_id`),
  CONSTRAINT `contrexx_content_page_ibfk_3` FOREIGN KEY (`node_id`) REFERENCES `contrexx_content_node` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION
) ENGINE=InnoDB ;
CREATE TABLE `contrexx_core_country` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `alpha2` char(2) NOT NULL DEFAULT '',
  `alpha3` char(3) NOT NULL DEFAULT '',
  `ord` int(5) unsigned NOT NULL DEFAULT '0',
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_core_mail_template` (
  `key` tinytext NOT NULL,
  `section` tinytext NOT NULL,
  `text_id` int(10) unsigned NOT NULL,
  `html` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `protected` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`key`(32),`section`(32))
) ENGINE=MyISAM;
CREATE TABLE `contrexx_core_setting` (
  `section` varchar(32) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `group` varchar(32) NOT NULL DEFAULT '',
  `type` varchar(32) NOT NULL DEFAULT 'text',
  `value` text NOT NULL,
  `values` text NOT NULL,
  `ord` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`section`,`name`,`group`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_core_text` (
  `id` int(10) unsigned NOT NULL DEFAULT '0',
  `lang_id` int(10) unsigned NOT NULL DEFAULT '1',
  `section` varchar(32) NOT NULL DEFAULT '',
  `key` varchar(255) NOT NULL,
  `text` text NOT NULL,
  PRIMARY KEY (`id`,`lang_id`,`section`,`key`(32)),
  FULLTEXT KEY `text` (`text`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_ids` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` int(14) DEFAULT NULL,
  `type` varchar(100) NOT NULL DEFAULT '',
  `remote_addr` varchar(15) DEFAULT NULL,
  `http_x_forwarded_for` varchar(15) NOT NULL DEFAULT '',
  `http_via` varchar(255) NOT NULL DEFAULT '',
  `user` mediumtext,
  `gpcs` mediumtext NOT NULL,
  `file` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_languages` (
  `id` int(2) unsigned NOT NULL AUTO_INCREMENT,
  `lang` varchar(5) NOT NULL DEFAULT '',
  `name` varchar(250) NOT NULL DEFAULT '',
  `charset` varchar(20) NOT NULL DEFAULT 'iso-8859-1',
  `themesid` int(2) unsigned NOT NULL DEFAULT '1',
  `print_themes_id` int(2) unsigned NOT NULL DEFAULT '1',
  `pdf_themes_id` int(2) unsigned NOT NULL DEFAULT '0',
  `frontend` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `backend` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_default` set('true','false') NOT NULL DEFAULT 'false',
  `mobile_themes_id` int(2) unsigned NOT NULL DEFAULT '0',
  `fallback` int(2) unsigned DEFAULT '0',
  `app_themes_id` int(2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lang` (`lang`),
  KEY `defaultstatus` (`is_default`),
  KEY `name` (`name`),
  FULLTEXT KEY `name_2` (`name`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_lib_country` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `iso_code_2` char(2) NOT NULL,
  `iso_code_3` char(3) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique` (`iso_code_2`),
  KEY `INDEX_COUNTRIES_NAME` (`name`)
) ENGINE=InnoDB ;
CREATE TABLE `contrexx_log` (
  `id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(6) unsigned DEFAULT NULL,
  `datetime` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `useragent` varchar(250) DEFAULT NULL,
  `userlanguage` varchar(250) DEFAULT NULL,
  `remote_addr` varchar(250) DEFAULT NULL,
  `remote_host` varchar(250) DEFAULT NULL,
  `http_via` varchar(250) NOT NULL DEFAULT '',
  `http_client_ip` varchar(250) NOT NULL DEFAULT '',
  `http_x_forwarded_for` varchar(250) NOT NULL DEFAULT '',
  `referer` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_log_entry` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` varchar(8) NOT NULL,
  `logged_at` timestamp NULL DEFAULT NULL,
  `version` int(11) NOT NULL,
  `object_id` varchar(32) DEFAULT NULL,
  `object_class` varchar(255) NOT NULL,
  `data` longtext,
  `username` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `log_class_unique_version_idx` (`version`,`object_id`,`object_class`),
  KEY `log_class_lookup_idx` (`object_class`),
  KEY `log_date_lookup_idx` (`logged_at`),
  KEY `log_user_lookup_idx` (`username`)
) ENGINE=InnoDB ;
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
  `category` int(1) NOT NULL DEFAULT '0',
  `direct` int(1) NOT NULL DEFAULT '0',
  `active` int(1) NOT NULL DEFAULT '0',
  `order` int(1) NOT NULL DEFAULT '0',
  `cat` int(10) NOT NULL DEFAULT '0',
  `wysiwyg_editor` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_block_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent` int(10) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `seperator` varchar(255) NOT NULL DEFAULT '',
  `order` int(10) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_block_rel_lang_content` (
  `block_id` int(10) unsigned NOT NULL DEFAULT '0',
  `lang_id` int(10) unsigned NOT NULL DEFAULT '0',
  `content` mediumtext NOT NULL,
  `active` int(1) NOT NULL DEFAULT '0',
  UNIQUE KEY `id_lang` (`block_id`,`lang_id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_block_rel_pages` (
  `block_id` int(7) NOT NULL DEFAULT '0',
  `page_id` int(7) NOT NULL DEFAULT '0',
  `placeholder` enum('global','direct','category') NOT NULL DEFAULT 'global',
  PRIMARY KEY (`block_id`,`page_id`,`placeholder`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_block_settings` (
  `id` int(7) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `value` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_blog_categories` (
  `category_id` int(4) unsigned NOT NULL DEFAULT '0',
  `lang_id` int(2) unsigned NOT NULL DEFAULT '0',
  `is_active` enum('0','1') NOT NULL DEFAULT '1',
  `name` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`category_id`,`lang_id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_blog_comments` (
  `comment_id` int(7) unsigned NOT NULL AUTO_INCREMENT,
  `message_id` int(6) unsigned NOT NULL DEFAULT '0',
  `lang_id` int(2) unsigned NOT NULL DEFAULT '0',
  `is_active` enum('0','1') NOT NULL DEFAULT '1',
  `time_created` int(14) unsigned NOT NULL DEFAULT '0',
  `ip_address` varchar(15) NOT NULL DEFAULT '0.0.0.0',
  `user_id` int(5) unsigned NOT NULL DEFAULT '0',
  `user_name` varchar(50) DEFAULT NULL,
  `user_mail` varchar(250) DEFAULT NULL,
  `user_www` varchar(255) DEFAULT NULL,
  `subject` varchar(250) NOT NULL DEFAULT '',
  `comment` text NOT NULL,
  PRIMARY KEY (`comment_id`),
  KEY `message_id` (`message_id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_blog_message_to_category` (
  `message_id` int(6) unsigned NOT NULL DEFAULT '0',
  `category_id` int(4) unsigned NOT NULL DEFAULT '0',
  `lang_id` int(2) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`message_id`,`category_id`,`lang_id`),
  KEY `category_id` (`category_id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_blog_messages` (
  `message_id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(5) unsigned NOT NULL DEFAULT '0',
  `time_created` int(14) unsigned NOT NULL DEFAULT '0',
  `time_edited` int(14) unsigned NOT NULL DEFAULT '0',
  `hits` int(7) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`message_id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_blog_messages_lang` (
  `message_id` int(6) unsigned NOT NULL,
  `lang_id` int(2) unsigned NOT NULL,
  `is_active` enum('0','1') NOT NULL DEFAULT '1',
  `subject` varchar(250) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  `tags` varchar(250) NOT NULL DEFAULT '',
  `image` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`message_id`,`lang_id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_blog_networks` (
  `network_id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `url_link` varchar(255) NOT NULL DEFAULT '',
  `icon` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`network_id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_blog_networks_lang` (
  `network_id` int(8) unsigned NOT NULL DEFAULT '0',
  `lang_id` int(2) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`network_id`,`lang_id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_blog_settings` (
  `name` varchar(50) NOT NULL,
  `value` varchar(250) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_blog_votes` (
  `vote_id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `message_id` int(6) unsigned NOT NULL DEFAULT '0',
  `time_voted` int(14) unsigned NOT NULL DEFAULT '0',
  `ip_address` varchar(15) NOT NULL DEFAULT '0.0.0.0',
  `vote` enum('1','2','3','4','5','6','7','8','9','10') NOT NULL DEFAULT '1',
  PRIMARY KEY (`vote_id`),
  KEY `message_id` (`message_id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_calendar_category` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `pos` int(5) DEFAULT NULL,
  `status` int(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_calendar_category_name` (
  `cat_id` int(11) NOT NULL,
  `lang_id` int(11) DEFAULT NULL,
  `name` varchar(225) DEFAULT NULL,
  KEY `fk_contrexx_module_calendar_category_names_contrexx_module_ca1` (`cat_id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_calendar_event` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(11) NOT NULL DEFAULT '0',
  `startdate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `enddate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `use_custom_date_display` tinyint(1) NOT NULL,
  `showStartDateList` int(1) NOT NULL,
  `showEndDateList` int(1) NOT NULL,
  `showStartTimeList` int(1) NOT NULL,
  `showEndTimeList` int(1) NOT NULL,
  `showTimeTypeList` int(1) NOT NULL,
  `showStartDateDetail` int(1) NOT NULL,
  `showEndDateDetail` int(1) NOT NULL,
  `showStartTimeDetail` int(1) NOT NULL,
  `showEndTimeDetail` int(1) NOT NULL,
  `showTimeTypeDetail` int(1) NOT NULL,
  `google` int(11) NOT NULL,
  `access` int(1) NOT NULL DEFAULT '0',
  `priority` int(1) NOT NULL DEFAULT '3',
  `price` int(11) NOT NULL DEFAULT '0',
  `link` varchar(255) NOT NULL,
  `pic` varchar(255) NOT NULL DEFAULT '',
  `attach` varchar(255) NOT NULL,
  `place_mediadir_id` int(11) NOT NULL,
  `catid` int(11) NOT NULL DEFAULT '0',
  `show_in` varchar(255) NOT NULL,
  `invited_groups` varchar(45) DEFAULT NULL,
  `invited_mails` mediumtext,
  `invitation_sent` int(1) NOT NULL,
  `invitation_email_template` varchar(255) NOT NULL,
  `registration` int(1) NOT NULL DEFAULT '0',
  `registration_form` int(11) NOT NULL,
  `registration_num` varchar(45) DEFAULT NULL,
  `registration_notification` varchar(1024) DEFAULT NULL,
  `email_template` varchar(255) NOT NULL,
  `ticket_sales` tinyint(1) NOT NULL DEFAULT '0',
  `num_seating` text NOT NULL,
  `series_status` tinyint(4) NOT NULL DEFAULT '0',
  `series_type` int(11) NOT NULL DEFAULT '0',
  `series_pattern_count` int(11) NOT NULL DEFAULT '0',
  `series_pattern_weekday` varchar(7) NOT NULL,
  `series_pattern_day` int(11) NOT NULL DEFAULT '0',
  `series_pattern_week` int(11) NOT NULL DEFAULT '0',
  `series_pattern_month` int(11) NOT NULL DEFAULT '0',
  `series_pattern_type` int(11) NOT NULL DEFAULT '0',
  `series_pattern_dourance_type` int(11) NOT NULL DEFAULT '0',
  `series_pattern_end` int(11) NOT NULL DEFAULT '0',
  `series_pattern_end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `series_pattern_begin` int(11) NOT NULL DEFAULT '0',
  `series_pattern_exceptions` longtext NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `confirmed` tinyint(1) NOT NULL DEFAULT '1',
  `author` varchar(255) NOT NULL,
  `all_day` tinyint(1) NOT NULL DEFAULT '0',
  `location_type` tinyint(1) NOT NULL DEFAULT '1',
  `place` varchar(255) NOT NULL,
  `place_id` int(11) NOT NULL,
  `place_street` varchar(255) DEFAULT NULL,
  `place_zip` varchar(10) DEFAULT NULL,
  `place_city` varchar(255) DEFAULT NULL,
  `place_country` varchar(255) DEFAULT NULL,
  `place_link` varchar(255) NOT NULL,
  `place_map` varchar(255) NOT NULL,
  `host_type` tinyint(1) NOT NULL DEFAULT '1',
  `org_name` varchar(255) NOT NULL,
  `org_street` varchar(255) NOT NULL,
  `org_zip` varchar(10) NOT NULL,
  `org_city` varchar(255) NOT NULL,
  `org_country` varchar(255) NOT NULL,
  `org_link` varchar(255) NOT NULL,
  `org_email` varchar(255) NOT NULL,
  `host_mediadir_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_contrexx_module_calendar_notes_contrexx_module_calendar_ca1` (`catid`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_calendar_event_field` (
  `event_id` int(11) NOT NULL DEFAULT '0',
  `lang_id` varchar(225) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` mediumtext,
  `redirect` varchar(255) NOT NULL,
  KEY `lang_field` (`title`),
  KEY `fk_contrexx_module_calendar_note_field_contrexx_module_calend1` (`event_id`),
  FULLTEXT KEY `eventIndex` (`title`,`description`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_calendar_host` (
  `id` int(1) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `uri` mediumtext NOT NULL,
  `cat_id` int(11) NOT NULL,
  `key` varchar(40) NOT NULL,
  `confirmed` int(11) NOT NULL,
  `status` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_contrexx_module_calendar_shared_hosts_contrexx_module_cale1` (`cat_id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_calendar_mail` (
  `id` int(7) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content_text` longtext NOT NULL,
  `content_html` longtext NOT NULL,
  `recipients` mediumtext NOT NULL,
  `lang_id` int(1) NOT NULL,
  `action_id` int(1) NOT NULL,
  `is_default` int(1) NOT NULL,
  `status` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_calendar_mail_action` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `default_recipient` enum('empty','admin','author') NOT NULL,
  `need_auth` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_calendar_registration` (
  `id` int(7) NOT NULL AUTO_INCREMENT,
  `event_id` int(7) NOT NULL,
  `date` int(15) NOT NULL,
  `host_name` varchar(255) NOT NULL,
  `ip_address` varchar(15) NOT NULL,
  `type` int(1) NOT NULL,
  `key` varchar(45) NOT NULL,
  `user_id` int(7) NOT NULL,
  `lang_id` int(11) NOT NULL,
  `export` int(11) NOT NULL,
  `payment_method` int(11) NOT NULL,
  `paid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_calendar_registration_form` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` int(11) NOT NULL,
  `order` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_calendar_registration_form_field` (
  `id` int(7) NOT NULL AUTO_INCREMENT,
  `form` int(11) NOT NULL,
  `type` enum('inputtext','textarea','select','radio','checkbox','mail','seating','agb','salutation','firstname','lastname','selectBillingAddress','fieldset') NOT NULL,
  `required` int(1) NOT NULL,
  `order` int(3) NOT NULL,
  `affiliation` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_calendar_registration_form_field_name` (
  `field_id` int(7) NOT NULL,
  `form_id` int(11) NOT NULL,
  `lang_id` int(1) NOT NULL,
  `name` varchar(255) NOT NULL,
  `default` mediumtext NOT NULL
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_calendar_registration_form_field_value` (
  `reg_id` int(7) NOT NULL,
  `field_id` int(7) NOT NULL,
  `value` mediumtext NOT NULL
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_calendar_rel_event_host` (
  `host_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_calendar_settings` (
  `id` int(7) NOT NULL AUTO_INCREMENT,
  `section_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `info` mediumtext NOT NULL,
  `type` int(11) NOT NULL,
  `options` mediumtext NOT NULL,
  `special` varchar(255) NOT NULL,
  `order` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_calendar_settings_section` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent` int(11) NOT NULL,
  `order` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_calendar_style` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tableWidth` varchar(4) NOT NULL DEFAULT '141',
  `tableHeight` varchar(4) NOT NULL DEFAULT '92',
  `tableColor` varchar(7) NOT NULL DEFAULT '',
  `tableBorder` int(11) NOT NULL DEFAULT '0',
  `tableBorderColor` varchar(7) NOT NULL DEFAULT '',
  `tableSpacing` int(11) NOT NULL DEFAULT '0',
  `fontSize` int(11) NOT NULL DEFAULT '10',
  `fontColor` varchar(7) NOT NULL DEFAULT '',
  `numColor` varchar(7) NOT NULL DEFAULT '',
  `normalDayColor` varchar(7) NOT NULL DEFAULT '',
  `normalDayRollOverColor` varchar(7) NOT NULL DEFAULT '',
  `curDayColor` varchar(7) NOT NULL DEFAULT '',
  `curDayRollOverColor` varchar(7) NOT NULL DEFAULT '',
  `eventDayColor` varchar(7) NOT NULL DEFAULT '',
  `eventDayRollOverColor` varchar(7) NOT NULL DEFAULT '',
  `shownEvents` int(4) NOT NULL DEFAULT '10',
  `periodTime` varchar(5) NOT NULL DEFAULT '00 23',
  `stdCat` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_checkout_settings_general` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `value` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_checkout_settings_mails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_checkout_settings_yellowpay` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_checkout_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` int(10) NOT NULL DEFAULT '0',
  `status` enum('confirmed','waiting','cancelled') NOT NULL,
  `invoice_number` varchar(255) NOT NULL,
  `invoice_currency` int(11) NOT NULL DEFAULT '1',
  `invoice_amount` int(15) NOT NULL,
  `contact_title` enum('mister','miss') NOT NULL,
  `contact_forename` varchar(255) NOT NULL DEFAULT '',
  `contact_surname` varchar(255) NOT NULL DEFAULT '',
  `contact_company` varchar(255) NOT NULL DEFAULT '',
  `contact_street` varchar(255) NOT NULL DEFAULT '',
  `contact_postcode` varchar(255) NOT NULL DEFAULT '',
  `contact_place` varchar(255) NOT NULL DEFAULT '',
  `contact_country` int(11) NOT NULL DEFAULT '204',
  `contact_phone` varchar(255) NOT NULL DEFAULT '',
  `contact_email` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_contact_form` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mails` text NOT NULL,
  `showForm` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `use_captcha` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `use_custom_style` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `save_data_in_crm` tinyint(1) NOT NULL DEFAULT '0',
  `send_copy` tinyint(1) NOT NULL DEFAULT '0',
  `use_email_of_sender` tinyint(1) NOT NULL DEFAULT '0',
  `html_mail` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `send_attachment` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_contact_form_data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_form` int(10) unsigned NOT NULL DEFAULT '0',
  `id_lang` int(10) unsigned NOT NULL DEFAULT '1',
  `time` int(14) unsigned NOT NULL DEFAULT '0',
  `host` varchar(255) NOT NULL DEFAULT '',
  `lang` varchar(64) NOT NULL DEFAULT '',
  `browser` varchar(255) NOT NULL DEFAULT '',
  `ipaddress` varchar(15) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_contact_form_field` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_form` int(10) unsigned NOT NULL DEFAULT '0',
  `type` enum('text','label','checkbox','checkboxGroup','country','date','file','multi_file','fieldset','hidden','horizontalLine','password','radio','select','textarea','recipient','special','datetime') NOT NULL DEFAULT 'text',
  `special_type` varchar(20) NOT NULL,
  `is_required` set('0','1') NOT NULL DEFAULT '0',
  `check_type` int(3) NOT NULL DEFAULT '1',
  `order_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_contact_form_field_lang` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fieldID` int(10) unsigned NOT NULL,
  `langID` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `attributes` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fieldID` (`fieldID`,`langID`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_contact_form_lang` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `formID` int(10) unsigned NOT NULL,
  `langID` int(10) unsigned NOT NULL,
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `name` varchar(255) NOT NULL,
  `text` text NOT NULL,
  `feedback` text NOT NULL,
  `mailTemplate` text NOT NULL,
  `subject` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `formID` (`formID`,`langID`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_contact_form_submit_data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_entry` int(10) unsigned NOT NULL,
  `id_field` int(10) unsigned NOT NULL,
  `formlabel` text NOT NULL,
  `formvalue` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_contact_recipient` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_form` int(11) NOT NULL DEFAULT '0',
  `email` varchar(250) NOT NULL DEFAULT '',
  `sort` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_contact_recipient_lang` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `recipient_id` int(10) unsigned NOT NULL,
  `langID` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `recipient_id` (`recipient_id`,`langID`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_contact_settings` (
  `setid` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `setname` varchar(250) NOT NULL DEFAULT '',
  `setvalue` text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`setid`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_crm_contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` varchar(256) DEFAULT NULL,
  `customer_type` int(11) DEFAULT NULL,
  `customer_name` varchar(256) DEFAULT NULL,
 