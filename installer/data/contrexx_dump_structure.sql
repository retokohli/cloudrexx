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
  `customer_website` varchar(256) DEFAULT NULL,
  `customer_addedby` int(11) DEFAULT NULL,
  `customer_currency` int(11) DEFAULT NULL,
  `contact_familyname` varchar(256) DEFAULT NULL,
  `contact_role` varchar(256) DEFAULT NULL,
  `contact_customer` int(11) DEFAULT NULL,
  `contact_language` int(11) DEFAULT NULL,
  `gender` tinyint(2) NOT NULL,
  `notes` text,
  `industry_type` int(11) DEFAULT NULL,
  `contact_type` tinyint(2) DEFAULT NULL,
  `user_account` int(11) DEFAULT NULL,
  `datasource` int(11) DEFAULT NULL,
  `profile_picture` varchar(256) NOT NULL,
  `status` tinyint(2) NOT NULL DEFAULT '1',
  `added_date` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `contact_customer` (`contact_customer`),
  KEY `customer_id` (`customer_id`),
  KEY `customer_name` (`customer_name`),
  KEY `contact_familyname` (`contact_familyname`),
  KEY `contact_role` (`contact_role`),
  FULLTEXT KEY `customer_id_2` (`customer_id`,`customer_name`,`contact_familyname`,`contact_role`,`notes`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_crm_currency` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(400) NOT NULL,
  `active` int(1) NOT NULL DEFAULT '1',
  `pos` int(5) NOT NULL DEFAULT '0',
  `hourly_rate` text NOT NULL,
  `default_currency` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`(333)),
  FULLTEXT KEY `name_2` (`name`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_crm_customer_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) DEFAULT NULL,
  `notes_type_id` int(1) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `comment` text,
  `added_date` datetime DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_on` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  FULLTEXT KEY `comment` (`comment`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_crm_customer_contact_address` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `address` varchar(256) NOT NULL,
  `city` varchar(256) NOT NULL,
  `state` varchar(256) NOT NULL,
  `zip` varchar(256) NOT NULL,
  `country` varchar(256) NOT NULL,
  `Address_Type` tinyint(4) NOT NULL,
  `is_primary` enum('0','1') NOT NULL,
  `contact_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `contact_id` (`contact_id`),
  KEY `address` (`address`),
  KEY `city` (`city`),
  KEY `state` (`state`),
  KEY `zip` (`zip`),
  KEY `country` (`country`),
  FULLTEXT KEY `address_2` (`address`,`city`,`state`,`zip`,`country`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_crm_customer_contact_emails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(256) NOT NULL,
  `email_type` tinyint(4) NOT NULL,
  `is_primary` enum('0','1') DEFAULT '0',
  `contact_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `contact_id` (`contact_id`),
  KEY `email` (`email`),
  FULLTEXT KEY `email_2` (`email`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_crm_customer_contact_phone` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone` varchar(256) NOT NULL,
  `phone_type` tinyint(4) NOT NULL,
  `is_primary` enum('0','1') DEFAULT '0',
  `contact_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `contact_id` (`contact_id`),
  KEY `phone` (`phone`),
  FULLTEXT KEY `phone_2` (`phone`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_crm_customer_contact_social_network` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(256) NOT NULL,
  `url_profile` tinyint(4) NOT NULL,
  `is_primary` enum('0','1') DEFAULT '0',
  `contact_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `contact_id` (`contact_id`),
  KEY `url` (`url`),
  FULLTEXT KEY `url_2` (`url`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_crm_customer_contact_websites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(256) NOT NULL,
  `url_type` tinyint(4) NOT NULL,
  `url_profile` tinyint(4) NOT NULL,
  `is_primary` enum('0','1') DEFAULT '0',
  `contact_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `contact_id` (`contact_id`),
  KEY `url` (`url`),
  FULLTEXT KEY `url_2` (`url`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_crm_customer_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_name` varchar(256) NOT NULL,
  `added_by` int(11) NOT NULL,
  `uploaded_date` datetime NOT NULL,
  `contact_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `contact_id` (`contact_id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_crm_customer_membership` (
  `contact_id` int(11) NOT NULL,
  `membership_id` int(11) NOT NULL
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_crm_customer_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(250) NOT NULL,
  `hourly_rate` varchar(256) NOT NULL,
  `active` int(1) NOT NULL,
  `pos` int(10) NOT NULL DEFAULT '0',
  `default` tinyint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `label` (`label`),
  FULLTEXT KEY `label_2` (`label`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_crm_datasources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datasource` varchar(256) NOT NULL,
  `status` tinyint(2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_crm_deals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(256) NOT NULL,
  `website` int(11) NOT NULL,
  `customer` int(11) NOT NULL,
  `customer_contact` int(11) NOT NULL,
  `quoted_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `quote_number` varchar(256) NOT NULL,
  `assigned_to` int(11) NOT NULL,
  `due_date` date DEFAULT NULL,
  `stage` int(11) NOT NULL,
  `description` text NOT NULL,
  `project_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `customer` (`customer`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_crm_industry_type_local` (
  `entry_id` int(11) NOT NULL,
  `lang_id` int(11) NOT NULL,
  `value` varchar(256) NOT NULL,
  KEY `entry_id` (`entry_id`),
  KEY `value` (`value`),
  FULLTEXT KEY `value_2` (`value`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_crm_industry_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `sorting` int(11) NOT NULL,
  `status` smallint(2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_crm_membership_local` (
  `entry_id` int(11) NOT NULL,
  `lang_id` int(11) NOT NULL,
  `value` varchar(256) NOT NULL,
  KEY `entry_id` (`entry_id`),
  KEY `value` (`value`),
  FULLTEXT KEY `value_2` (`value`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_crm_memberships` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sorting` int(11) NOT NULL,
  `status` smallint(2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_crm_notes` (
  `id` int(1) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `icon` varchar(255) NOT NULL,
  `pos` int(1) NOT NULL,
  `system_defined` tinyint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  FULLTEXT KEY `name_2` (`name`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_crm_settings` (
  `setid` int(7) NOT NULL AUTO_INCREMENT,
  `setname` varchar(255) NOT NULL,
  `setvalue` text NOT NULL,
  PRIMARY KEY (`setid`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_crm_stages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(256) NOT NULL,
  `stage` varchar(256) NOT NULL,
  `status` tinyint(2) NOT NULL,
  `sorting` int(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_crm_success_rate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(256) NOT NULL,
  `rate` varchar(256) NOT NULL,
  `status` tinyint(2) NOT NULL,
  `sorting` int(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_crm_task` (
  `id` int(2) NOT NULL AUTO_INCREMENT,
  `task_id` varchar(10) NOT NULL,
  `task_title` varchar(255) NOT NULL,
  `task_type_id` int(2) NOT NULL,
  `customer_id` int(2) NOT NULL,
  `due_date` datetime NOT NULL,
  `assigned_to` int(11) NOT NULL,
  `description` text NOT NULL,
  `task_status` tinyint(1) NOT NULL DEFAULT '1',
  `added_by` int(11) NOT NULL,
  `added_date_time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_crm_task_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `sorting` int(11) NOT NULL,
  `description` text NOT NULL,
  `icon` varchar(255) NOT NULL,
  `system_defined` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  FULLTEXT KEY `name_2` (`name`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_data_categories` (
  `category_id` int(4) unsigned NOT NULL DEFAULT '0',
  `lang_id` int(2) unsigned NOT NULL DEFAULT '0',
  `is_active` enum('0','1') NOT NULL DEFAULT '1',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL DEFAULT '',
  `active` enum('0','1') NOT NULL DEFAULT '1',
  `cmd` int(10) unsigned NOT NULL DEFAULT '1',
  `action` enum('content','overlaybox','subcategories') NOT NULL DEFAULT 'content',
  `sort` int(10) unsigned NOT NULL DEFAULT '1',
  `box_height` int(10) unsigned NOT NULL DEFAULT '500',
  `box_width` int(11) NOT NULL DEFAULT '350',
  `template` text NOT NULL,
  PRIMARY KEY (`category_id`,`lang_id`)
) ENGINE=InnoDB;
CREATE TABLE `contrexx_module_data_message_to_category` (
  `message_id` int(6) unsigned NOT NULL DEFAULT '0',
  `category_id` int(4) unsigned NOT NULL DEFAULT '0',
  `lang_id` int(2) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`message_id`,`category_id`,`lang_id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB;
CREATE TABLE `contrexx_module_data_messages` (
  `message_id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(5) unsigned NOT NULL DEFAULT '0',
  `time_created` int(14) unsigned NOT NULL DEFAULT '0',
  `time_edited` int(14) unsigned NOT NULL DEFAULT '0',
  `hits` int(7) unsigned NOT NULL DEFAULT '0',
  `active` enum('0','1') NOT NULL DEFAULT '1',
  `sort` int(10) unsigned NOT NULL DEFAULT '1',
  `mode` set('normal','forward') NOT NULL DEFAULT 'normal',
  `release_time` int(15) NOT NULL DEFAULT '0',
  `release_time_end` int(15) NOT NULL DEFAULT '0',
  PRIMARY KEY (`message_id`)
) ENGINE=InnoDB ;
CREATE TABLE `contrexx_module_data_messages_lang` (
  `message_id` int(6) unsigned NOT NULL DEFAULT '0',
  `lang_id` int(2) unsigned NOT NULL DEFAULT '0',
  `is_active` enum('0','1') NOT NULL DEFAULT '1',
  `subject` varchar(250) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  `tags` varchar(250) NOT NULL DEFAULT '',
  `image` varchar(250) NOT NULL DEFAULT '',
  `thumbnail` varchar(250) NOT NULL,
  `thumbnail_type` enum('original','thumbnail') NOT NULL DEFAULT 'original',
  `thumbnail_width` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `thumbnail_height` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `attachment` varchar(255) NOT NULL DEFAULT '',
  `attachment_description` varchar(255) NOT NULL DEFAULT '',
  `mode` set('normal','forward') NOT NULL DEFAULT 'normal',
  `forward_url` varchar(255) NOT NULL DEFAULT '',
  `forward_target` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`message_id`,`lang_id`)
) ENGINE=InnoDB;
CREATE TABLE `contrexx_module_data_placeholders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` set('cat','entry') NOT NULL DEFAULT '',
  `ref_id` int(11) NOT NULL DEFAULT '0',
  `placeholder` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `placeholder` (`placeholder`),
  UNIQUE KEY `type` (`type`,`ref_id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_data_settings` (
  `name` varchar(50) NOT NULL DEFAULT '',
  `value` text NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB;
CREATE TABLE `contrexx_module_directory_categories` (
  `id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `parentid` int(6) unsigned NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL DEFAULT '',
  `description` varchar(250) NOT NULL DEFAULT '',
  `displayorder` smallint(6) unsigned NOT NULL DEFAULT '1000',
  `metadesc` varchar(250) NOT NULL DEFAULT '',
  `metakeys` varchar(250) NOT NULL DEFAULT '',
  `showentries` int(1) NOT NULL DEFAULT '1',
  `status` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `parentid` (`parentid`),
  KEY `displayorder` (`displayorder`),
  KEY `status` (`status`),
  FULLTEXT KEY `directoryindex` (`name`,`description`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_directory_dir` (
  `id` int(7) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL DEFAULT '',
  `attachment` varchar(255) NOT NULL DEFAULT '',
  `rss_file` varchar(255) NOT NULL DEFAULT '',
  `rss_link` varchar(255) NOT NULL DEFAULT '',
  `link` varchar(255) NOT NULL DEFAULT '',
  `date` varchar(14) DEFAULT NULL,
  `description` mediumtext NOT NULL,
  `platform` varchar(40) NOT NULL DEFAULT '',
  `language` varchar(40) NOT NULL DEFAULT '',
  `relatedlinks` varchar(255) NOT NULL DEFAULT '',
  `hits` int(9) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `addedby` varchar(50) NOT NULL DEFAULT '',
  `provider` varchar(255) NOT NULL DEFAULT '',
  `ip` varchar(255) NOT NULL DEFAULT '',
  `validatedate` varchar(14) NOT NULL DEFAULT '',
  `lastip` varchar(50) NOT NULL DEFAULT '',
  `popular_date` varchar(30) NOT NULL DEFAULT '',
  `popular_hits` int(7) NOT NULL DEFAULT '0',
  `xml_refresh` varchar(15) NOT NULL DEFAULT '',
  `canton` varchar(50) NOT NULL DEFAULT '',
  `searchkeys` varchar(255) NOT NULL DEFAULT '',
  `company_name` varchar(100) NOT NULL DEFAULT '',
  `street` varchar(255) NOT NULL DEFAULT '',
  `zip` varchar(5) NOT NULL DEFAULT '',
  `city` varchar(50) NOT NULL DEFAULT '',
  `country` varchar(255) NOT NULL DEFAULT '',
  `phone` varchar(20) NOT NULL DEFAULT '',
  `contact` varchar(100) NOT NULL DEFAULT '',
  `information` varchar(100) NOT NULL DEFAULT '',
  `fax` varchar(20) NOT NULL DEFAULT '',
  `mobile` varchar(20) NOT NULL DEFAULT '',
  `mail` varchar(50) NOT NULL DEFAULT '',
  `homepage` varchar(50) NOT NULL DEFAULT '',
  `industry` varchar(100) NOT NULL DEFAULT '',
  `legalform` varchar(50) NOT NULL DEFAULT '',
  `conversion` varchar(50) NOT NULL DEFAULT '',
  `employee` varchar(255) NOT NULL DEFAULT '',
  `foundation` varchar(10) NOT NULL DEFAULT '',
  `mwst` varchar(50) NOT NULL DEFAULT '',
  `opening` varchar(255) NOT NULL DEFAULT '',
  `holidays` varchar(255) NOT NULL DEFAULT '',
  `places` varchar(255) NOT NULL DEFAULT '',
  `logo` varchar(50) DEFAULT NULL,
  `team` varchar(255) NOT NULL DEFAULT '',
  `portfolio` varchar(255) NOT NULL DEFAULT '',
  `offers` varchar(255) NOT NULL DEFAULT '',
  `concept` varchar(255) NOT NULL DEFAULT '',
  `map` varchar(255) DEFAULT NULL,
  `lokal` varchar(255) DEFAULT NULL,
  `spezial` int(4) NOT NULL DEFAULT '0',
  `premium` int(1) NOT NULL DEFAULT '0',
  `longitude` decimal(18,15) NOT NULL DEFAULT '0.000000000000000',
  `latitude` decimal(18,15) NOT NULL DEFAULT '0.000000000000000',
  `zoom` decimal(18,15) NOT NULL DEFAULT '1.000000000000000',
  `spez_field_1` varchar(255) NOT NULL DEFAULT '',
  `spez_field_2` varchar(255) NOT NULL DEFAULT '',
  `spez_field_3` varchar(255) NOT NULL DEFAULT '',
  `spez_field_4` varchar(255) NOT NULL DEFAULT '',
  `spez_field_5` varchar(255) NOT NULL DEFAULT '',
  `spez_field_6` mediumtext NOT NULL,
  `spez_field_7` mediumtext NOT NULL,
  `spez_field_8` mediumtext NOT NULL,
  `spez_field_9` mediumtext NOT NULL,
  `spez_field_10` mediumtext NOT NULL,
  `spez_field_11` varchar(255) DEFAULT NULL,
  `spez_field_12` varchar(255) DEFAULT NULL,
  `spez_field_13` varchar(255) DEFAULT NULL,
  `spez_field_14` varchar(255) DEFAULT NULL,
  `spez_field_15` varchar(255) DEFAULT NULL,
  `spez_field_21` varchar(255) NOT NULL DEFAULT '',
  `spez_field_22` varchar(255) NOT NULL DEFAULT '',
  `spez_field_16` varchar(255) DEFAULT NULL,
  `spez_field_17` varchar(255) DEFAULT NULL,
  `spez_field_18` varchar(255) DEFAULT NULL,
  `spez_field_19` varchar(255) DEFAULT NULL,
  `spez_field_20` varchar(255) DEFAULT NULL,
  `spez_field_23` varchar(255) NOT NULL DEFAULT '',
  `spez_field_24` varchar(255) NOT NULL DEFAULT '',
  `spez_field_25` varchar(255) NOT NULL DEFAULT '',
  `spez_field_26` varchar(255) NOT NULL DEFAULT '',
  `spez_field_27` varchar(255) NOT NULL DEFAULT '',
  `spez_field_28` varchar(255) NOT NULL DEFAULT '',
  `spez_field_29` varchar(255) NOT NULL DEFAULT '',
  `youtube` mediumtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `date` (`date`),
  KEY `temphitsout` (`hits`),
  KEY `status` (`status`),
  FULLTEXT KEY `name` (`title`,`description`),
  FULLTEXT KEY `description` (`description`),
  FULLTEXT KEY `title` (`title`,`description`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_directory_inputfields` (
  `id` int(7) NOT NULL AUTO_INCREMENT,
  `typ` int(2) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `active` int(1) NOT NULL DEFAULT '0',
  `active_backend` int(1) NOT NULL DEFAULT '0',
  `is_required` int(11) NOT NULL DEFAULT '0',
  `read_only` int(1) NOT NULL DEFAULT '0',
  `sort` int(5) NOT NULL DEFAULT '0',
  `exp_search` int(1) NOT NULL DEFAULT '0',
  `is_search` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_directory_levels` (
  `id` int(7) NOT NULL AUTO_INCREMENT,
  `parentid` int(7) NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `metadesc` varchar(100) NOT NULL DEFAULT '',
  `metakeys` varchar(100) NOT NULL DEFAULT '',
  `displayorder` int(7) NOT NULL DEFAULT '0',
  `showlevels` int(1) NOT NULL DEFAULT '0',
  `showcategories` int(1) NOT NULL DEFAULT '0',
  `onlyentries` int(1) NOT NULL DEFAULT '0',
  `status` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `displayorder` (`displayorder`),
  KEY `parentid` (`parentid`),
  KEY `name` (`name`),
  KEY `status` (`status`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_directory_mail` (
  `id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `content` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_directory_rel_dir_cat` (
  `dir_id` int(7) NOT NULL DEFAULT '0',
  `cat_id` int(7) NOT NULL DEFAULT '0',
  PRIMARY KEY (`dir_id`,`cat_id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_directory_rel_dir_level` (
  `dir_id` int(7) NOT NULL DEFAULT '0',
  `level_id` int(7) NOT NULL DEFAULT '0',
  PRIMARY KEY (`dir_id`,`level_id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_directory_settings` (
  `setid` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `setname` varchar(250) NOT NULL DEFAULT '',
  `setvalue` text NOT NULL,
  `settyp` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`setid`),
  KEY `setname` (`setname`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_directory_settings_google` (
  `setid` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `setname` varchar(250) NOT NULL DEFAULT '',
  `setvalue` text NOT NULL,
  `settyp` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`setid`),
  KEY `setname` (`setname`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_directory_vote` (
  `id` int(7) NOT NULL AUTO_INCREMENT,
  `feed_id` int(7) NOT NULL DEFAULT '0',
  `vote` int(2) NOT NULL DEFAULT '0',
  `count` int(7) NOT NULL DEFAULT '0',
  `client` varchar(255) NOT NULL DEFAULT '',
  `time` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_docsys` (
  `id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `date` int(14) DEFAULT NULL,
  `title` varchar(250) NOT NULL DEFAULT '',
  `author` varchar(150) NOT NULL DEFAULT '',
  `text` mediumtext NOT NULL,
  `source` varchar(250) NOT NULL DEFAULT '',
  `url1` varchar(250) NOT NULL DEFAULT '',
  `url2` varchar(250) NOT NULL DEFAULT '',
  `lang` int(2) unsigned NOT NULL DEFAULT '0',
  `userid` int(6) unsigned NOT NULL DEFAULT '0',
  `startdate` int(14) unsigned NOT NULL DEFAULT '0',
  `enddate` int(14) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `changelog` int(14) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  FULLTEXT KEY `newsindex` (`title`,`text`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_docsys_categories` (
  `catid` int(2) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `lang` int(2) unsigned NOT NULL DEFAULT '1',
  `sort_style` enum('alpha','date','date_alpha') NOT NULL DEFAULT 'alpha',
  PRIMARY KEY (`catid`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_docsys_entry_category` (
  `entry` int(10) unsigned NOT NULL DEFAULT '0',
  `category` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`entry`,`category`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_downloads_category` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `visibility` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `owner_id` int(5) unsigned NOT NULL DEFAULT '0',
  `order` int(3) unsigned NOT NULL DEFAULT '0',
  `deletable_by_owner` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `modify_access_by_owner` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `read_access_id` int(11) unsigned NOT NULL DEFAULT '0',
  `add_subcategories_access_id` int(11) unsigned NOT NULL DEFAULT '0',
  `manage_subcategories_access_id` int(11) unsigned NOT NULL DEFAULT '0',
  `add_files_access_id` int(11) unsigned NOT NULL DEFAULT '0',
  `manage_files_access_id` int(11) unsigned NOT NULL DEFAULT '0',
  `image` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `is_active` (`is_active`),
  KEY `visibility` (`visibility`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_downloads_category_locale` (
  `lang_id` int(11) unsigned NOT NULL DEFAULT '0',
  `category_id` int(11) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  PRIMARY KEY (`lang_id`,`category_id`),
  FULLTEXT KEY `name` (`name`),
  FULLTEXT KEY `description` (`description`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_downloads_download` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('file','url') NOT NULL DEFAULT 'file',
  `mime_type` enum('image','document','pdf','media','archive','application','link') NOT NULL DEFAULT 'image',
  `icon` enum('_blank','avi','bmp','css','doc','dot','exe','fla','gif','htm','html','inc','jpg','js','mp3','nfo','pdf','php','png','pps','ppt','rar','swf','txt','wma','xls','zip') NOT NULL DEFAULT '_blank',
  `size` int(10) unsigned NOT NULL DEFAULT '0',
  `image` varchar(255) NOT NULL DEFAULT '',
  `owner_id` int(5) unsigned NOT NULL DEFAULT '0',
  `access_id` int(10) unsigned NOT NULL DEFAULT '0',
  `license` varchar(255) NOT NULL DEFAULT '',
  `version` varchar(10) NOT NULL DEFAULT '',
  `author` varchar(100) NOT NULL DEFAULT '',
  `website` varchar(255) NOT NULL DEFAULT '',
  `ctime` int(14) unsigned NOT NULL DEFAULT '0',
  `mtime` int(14) unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `visibility` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `order` int(3) unsigned NOT NULL DEFAULT '0',
  `views` int(10) unsigned NOT NULL DEFAULT '0',
  `download_count` int(10) unsigned NOT NULL DEFAULT '0',
  `expiration` int(14) unsigned NOT NULL DEFAULT '0',
  `validity` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `is_active` (`is_active`),
  KEY `visibility` (`visibility`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_downloads_download_locale` (
  `lang_id` int(11) unsigned NOT NULL DEFAULT '0',
  `download_id` int(11) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `source` varchar(1024) DEFAULT NULL,
  `source_name` varchar(1024) DEFAULT NULL,
  `description` text NOT NULL,
  `metakeys` text NOT NULL,
  PRIMARY KEY (`lang_id`,`download_id`),
  FULLTEXT KEY `name` (`name`),
  FULLTEXT KEY `description` (`description`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_downloads_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `type` enum('file','url') NOT NULL DEFAULT 'file',
  `info_page` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_downloads_group_locale` (
  `lang_id` int(11) unsigned NOT NULL DEFAULT '0',
  `group_id` int(11) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`lang_id`,`group_id`),
  FULLTEXT KEY `name` (`name`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_downloads_rel_download_category` (
  `download_id` int(10) unsigned NOT NULL DEFAULT '0',
  `category_id` int(10) unsigned NOT NULL DEFAULT '0',
  `order` int(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`download_id`,`category_id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_downloads_rel_download_download` (
  `id1` int(10) unsigned NOT NULL DEFAULT '0',
  `id2` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id1`,`id2`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_downloads_rel_group_category` (
  `group_id` int(10) unsigned NOT NULL DEFAULT '0',
  `category_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`group_id`,`category_id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_downloads_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL DEFAULT '',
  `value` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_ecard_ecards` (
  `code` varchar(35) NOT NULL DEFAULT '',
  `date` int(10) unsigned NOT NULL DEFAULT '0',
  `TTL` int(10) unsigned NOT NULL DEFAULT '0',
  `salutation` varchar(100) NOT NULL DEFAULT '',
  `senderName` varchar(100) NOT NULL DEFAULT '',
  `senderEmail` varchar(100) NOT NULL DEFAULT '',
  `recipientName` varchar(100) NOT NULL DEFAULT '',
  `recipientEmail` varchar(100) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  PRIMARY KEY (`code`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_ecard_settings` (
  `setting_name` varchar(100) NOT NULL DEFAULT '',
  `setting_value` text NOT NULL,
  PRIMARY KEY (`setting_name`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_egov_configuration` (
  `name` varchar(255) NOT NULL DEFAULT '',
  `value` text NOT NULL,
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_egov_orders` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `order_ip` varchar(255) NOT NULL DEFAULT '',
  `order_product` int(11) NOT NULL DEFAULT '0',
  `order_values` text NOT NULL,
  `order_state` tinyint(4) NOT NULL DEFAULT '0',
  `order_quant` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`order_id`),
  KEY `order_product` (`order_product`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_egov_product_calendar` (
  `calendar_id` int(11) NOT NULL AUTO_INCREMENT,
  `calendar_product` int(11) NOT NULL DEFAULT '0',
  `calendar_order` int(11) NOT NULL DEFAULT '0',
  `calendar_day` int(2) NOT NULL DEFAULT '0',
  `calendar_month` int(2) unsigned zerofill NOT NULL DEFAULT '00',
  `calendar_year` int(4) NOT NULL DEFAULT '0',
  `calendar_act` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`calendar_id`),
  KEY `calendar_product` (`calendar_product`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_egov_product_fields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `type` enum('text','label','checkbox','checkboxGroup','file','hidden','password','radio','select','textarea') NOT NULL DEFAULT 'text',
  `attributes` text NOT NULL,
  `is_required` set('0','1') NOT NULL DEFAULT '0',
  `check_type` int(3) NOT NULL DEFAULT '1',
  `order_id` int(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product` (`product`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_egov_products` (
  `product_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_autostatus` tinyint(1) NOT NULL DEFAULT '0',
  `product_name` varchar(255) NOT NULL DEFAULT '',
  `product_desc` text NOT NULL,
  `product_price` decimal(11,2) NOT NULL DEFAULT '0.00',
  `product_per_day` enum('yes','no') NOT NULL DEFAULT 'no',
  `product_quantity` tinyint(2) NOT NULL DEFAULT '0',
  `product_quantity_limit` tinyint(2) unsigned NOT NULL DEFAULT '1',
  `product_target_email` varchar(255) NOT NULL DEFAULT '',
  `product_target_url` varchar(255) NOT NULL DEFAULT '',
  `product_message` text NOT NULL,
  `product_status` tinyint(1) NOT NULL DEFAULT '1',
  `product_electro` tinyint(1) NOT NULL DEFAULT '0',
  `product_file` varchar(255) NOT NULL DEFAULT '',
  `product_sender_name` varchar(255) NOT NULL DEFAULT '',
  `product_sender_email` varchar(255) NOT NULL DEFAULT '',
  `product_target_subject` varchar(255) NOT NULL,
  `product_target_body` text NOT NULL,
  `product_paypal` tinyint(1) NOT NULL DEFAULT '0',
  `product_paypal_sandbox` varchar(255) NOT NULL DEFAULT '',
  `product_paypal_currency` varchar(255) NOT NULL DEFAULT '',
  `product_orderby` int(11) NOT NULL DEFAULT '0',
  `yellowpay` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `alternative_names` text NOT NULL,
  PRIMARY KEY (`product_id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_egov_settings` (
  `set_id` int(11) NOT NULL DEFAULT '0',
  `set_sender_name` varchar(255) NOT NULL DEFAULT '',
  `set_sender_email` varchar(255) NOT NULL DEFAULT '',
  `set_recipient_email` varchar(255) NOT NULL DEFAULT '',
  `set_state_subject` varchar(255) NOT NULL DEFAULT '',
  `set_state_email` text NOT NULL,
  `set_calendar_color_1` varchar(255) NOT NULL DEFAULT '',
  `set_calendar_color_2` varchar(255) NOT NULL DEFAULT '',
  `set_calendar_color_3` varchar(255) NOT NULL DEFAULT '',
  `set_calendar_legende_1` varchar(255) NOT NULL DEFAULT '',
  `set_calendar_legende_2` varchar(255) NOT NULL DEFAULT '',
  `set_calendar_legende_3` varchar(255) NOT NULL DEFAULT '',
  `set_calendar_background` varchar(255) NOT NULL DEFAULT '',
  `set_calendar_border` varchar(255) NOT NULL DEFAULT '',
  `set_calendar_date_label` varchar(255) NOT NULL DEFAULT '',
  `set_calendar_date_desc` varchar(255) NOT NULL DEFAULT '',
  `set_orderentry_subject` varchar(255) NOT NULL DEFAULT '',
  `set_orderentry_email` text NOT NULL,
  `set_orderentry_name` varchar(255) NOT NULL DEFAULT '',
  `set_orderentry_sender` varchar(255) NOT NULL DEFAULT '',
  `set_orderentry_recipient` varchar(255) NOT NULL DEFAULT '',
  `set_paypal_email` text NOT NULL,
  `set_paypal_currency` text NOT NULL,
  `set_paypal_ipn` tinyint(1) NOT NULL DEFAULT '0',
  KEY `set_id` (`set_id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_feed_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL DEFAULT '',
  `status` int(1) NOT NULL DEFAULT '1',
  `time` int(100) NOT NULL DEFAULT '0',
  `lang` int(1) NOT NULL DEFAULT '0',
  `pos` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_feed_news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subid` int(11) NOT NULL DEFAULT '0',
  `name` varchar(150) NOT NULL DEFAULT '',
  `link` varchar(150) NOT NULL DEFAULT '',
  `filename` varchar(150) NOT NULL DEFAULT '',
  `articles` int(2) NOT NULL DEFAULT '0',
  `cache` int(4) NOT NULL DEFAULT '3600',
  `time` int(100) NOT NULL DEFAULT '0',
  `image` int(1) NOT NULL DEFAULT '1',
  `status` int(1) NOT NULL DEFAULT '1',
  `pos` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_feed_newsml_association` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pId_master` text NOT NULL,
  `pId_slave` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_feed_newsml_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `providerId` text NOT NULL,
  `name` varchar(40) NOT NULL DEFAULT '',
  `subjectCodes` text NOT NULL,
  `showSubjectCodes` enum('all','only','exclude') NOT NULL DEFAULT 'all',
  `template` text NOT NULL,
  `limit` smallint(6) NOT NULL DEFAULT '0',
  `showPics` enum('0','1') NOT NULL DEFAULT '1',
  `auto_update` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_feed_newsml_documents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `publicIdentifier` varchar(255) NOT NULL DEFAULT '',
  `providerId` text NOT NULL,
  `dateId` int(8) unsigned NOT NULL DEFAULT '0',
  `newsItemId` text NOT NULL,
  `revisionId` int(5) unsigned NOT NULL DEFAULT '0',
  `thisRevisionDate` int(14) NOT NULL DEFAULT '0',
  `urgency` smallint(5) unsigned NOT NULL DEFAULT '0',
  `subjectCode` int(10) unsigned NOT NULL DEFAULT '0',
  `headLine` varchar(67) NOT NULL DEFAULT '',
  `dataContent` text NOT NULL,
  `is_associated` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `media_type` enum('Text','Graphic','Photo','Audio','Video','ComplexData') NOT NULL DEFAULT 'Text',
  `source` text NOT NULL,
  `properties` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique` (`publicIdentifier`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_feed_newsml_providers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `providerId` text NOT NULL,
  `name` varchar(40) NOT NULL DEFAULT '',
  `path` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_filesharing` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `file` varchar(250) NOT NULL,
  `source` varchar(250) NOT NULL,
  `cmd` varchar(50) NOT NULL,
  `hash` varchar(50) NOT NULL,
  `check` varchar(50) NOT NULL,
  `expiration_date` timestamp NULL DEFAULT NULL,
  `upload_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_filesharing_mail_template` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `lang_id` int(1) NOT NULL,
  `subject` varchar(250) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_forum_access` (
  `category_id` int(5) unsigned NOT NULL DEFAULT '0',
  `group_id` int(5) unsigned NOT NULL DEFAULT '0',
  `read` set('0','1') NOT NULL DEFAULT '0',
  `write` set('0','1') NOT NULL DEFAULT '0',
  `edit` set('0','1') NOT NULL DEFAULT '0',
  `delete` set('0','1') NOT NULL DEFAULT '0',
  `move` set('0','1') NOT NULL DEFAULT '0',
  `close` set('0','1') NOT NULL DEFAULT '0',
  `sticky` set('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`category_id`,`group_id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_forum_categories` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(5) unsigned NOT NULL DEFAULT '0',
  `order_id` int(5) unsigned NOT NULL DEFAULT '0',
  `status` set('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_forum_categories_lang` (
  `category_id` int(5) unsigned NOT NULL DEFAULT '0',
  `lang_id` int(5) unsigned NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  PRIMARY KEY (`category_id`,`lang_id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_forum_notification` (
  `category_id` int(10) unsigned NOT NULL DEFAULT '0',
  `thread_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` int(5) unsigned NOT NULL DEFAULT '0',
  `is_notified` set('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`category_id`,`thread_id`,`user_id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_forum_postings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(5) unsigned NOT NULL DEFAULT '0',
  `thread_id` int(10) unsigned NOT NULL DEFAULT '0',
  `prev_post_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` int(5) unsigned NOT NULL DEFAULT '0',
  `time_created` int(14) unsigned NOT NULL DEFAULT '0',
  `time_edited` int(14) unsigned NOT NULL DEFAULT '0',
  `is_locked` set('0','1') NOT NULL DEFAULT '0',
  `is_sticky` set('0','1') NOT NULL DEFAULT '0',
  `rating` int(11) NOT NULL DEFAULT '0',
  `views` int(10) unsigned NOT NULL DEFAULT '0',
  `icon` smallint(5) unsigned NOT NULL DEFAULT '0',
  `keywords` text NOT NULL,
  `subject` varchar(250) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  `attachment` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`,`thread_id`,`prev_post_id`,`user_id`),
  FULLTEXT KEY `fulltext` (`keywords`,`subject`,`content`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_forum_rating` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `post_id` int(11) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`post_id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_forum_settings` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '',
  `value` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_forum_statistics` (
  `category_id` int(5) unsigned NOT NULL DEFAULT '0',
  `thread_count` int(10) unsigned NOT NULL DEFAULT '0',
  `post_count` int(10) unsigned NOT NULL DEFAULT '0',
  `last_post_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`category_id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_gallery_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `sorting` int(6) NOT NULL DEFAULT '0',
  `status` set('0','1') NOT NULL DEFAULT '1',
  `comment` set('0','1') NOT NULL DEFAULT '0',
  `voting` set('0','1') NOT NULL DEFAULT '0',
  `backendProtected` int(11) NOT NULL DEFAULT '0',
  `backend_access_id` int(11) NOT NULL DEFAULT '0',
  `frontendProtected` int(11) NOT NULL DEFAULT '0',
  `frontend_access_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_gallery_comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `picid` int(10) unsigned NOT NULL DEFAULT '0',
  `date` int(14) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(15) NOT NULL DEFAULT '',
  `name` varchar(50) NOT NULL DEFAULT '',
  `email` varchar(250) NOT NULL DEFAULT '',
  `www` varchar(250) NOT NULL DEFAULT '',
  `comment` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_gallery_language` (
  `gallery_id` int(10) unsigned NOT NULL DEFAULT '0',
  `lang_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` set('name','desc') NOT NULL DEFAULT '',
  `value` text NOT NULL,
  PRIMARY KEY (`gallery_id`,`lang_id`,`name`),
  FULLTEXT KEY `galleryindex` (`value`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_gallery_language_pics` (
  `picture_id` int(10) unsigned NOT NULL DEFAULT '0',
  `lang_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `desc` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`picture_id`,`lang_id`),
  FULLTEXT KEY `galleryindex` (`name`,`desc`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_gallery_pictures` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `catid` int(11) NOT NULL DEFAULT '0',
  `validated` set('0','1') NOT NULL DEFAULT '0',
  `status` set('0','1') NOT NULL DEFAULT '1',
  `catimg` set('0','1') NOT NULL DEFAULT '0',
  `sorting` int(6) unsigned NOT NULL DEFAULT '999',
  `size_show` set('0','1') NOT NULL DEFAULT '1',
  `path` text NOT NULL,
  `link` text NOT NULL,
  `lastedit` int(14) NOT NULL DEFAULT '0',
  `size_type` set('abs','proz') NOT NULL DEFAULT 'proz',
  `size_proz` int(3) NOT NULL DEFAULT '0',
  `size_abs_h` int(11) NOT NULL DEFAULT '0',
  `size_abs_w` int(11) NOT NULL DEFAULT '0',
  `quality` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  FULLTEXT KEY `galleryPicturesIndex` (`path`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_gallery_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL DEFAULT '',
  `value` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_gallery_votes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `picid` int(10) unsigned NOT NULL DEFAULT '0',
  `date` int(14) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(15) NOT NULL DEFAULT '',
  `md5` varchar(32) NOT NULL DEFAULT '',
  `mark` int(2) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_guestbook` (
  `id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `forename` varchar(255) NOT NULL DEFAULT '',
  `gender` char(1) NOT NULL DEFAULT '',
  `url` tinytext NOT NULL,
  `email` tinytext NOT NULL,
  `comment` text NOT NULL,
  `ip` varchar(15) NOT NULL DEFAULT '',
  `location` tinytext NOT NULL,
  `lang_id` tinyint(2) NOT NULL DEFAULT '1',
  `datetime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  FULLTEXT KEY `comment` (`comment`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_guestbook_settings` (
  `name` varchar(50) NOT NULL DEFAULT '',
  `value` varchar(250) NOT NULL DEFAULT '',
  KEY `name` (`name`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_immo` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reference` varchar(20) NOT NULL DEFAULT '-',
  `ref_nr_note` varchar(255) DEFAULT NULL,
  `logo` enum('logo1','logo2') NOT NULL DEFAULT 'logo1',
  `special_offer` tinyint(1) NOT NULL DEFAULT '0',
  `visibility` enum('disabled','reference','listing') NOT NULL DEFAULT 'disabled',
  `object_type` enum('flat','house','multifamily','estate','industry','parking') NOT NULL DEFAULT 'flat',
  `new_building` tinyint(1) NOT NULL DEFAULT '0',
  `property_type` enum('purchase','rent') NOT NULL DEFAULT 'purchase',
  `longitude` decimal(18,15) NOT NULL DEFAULT '0.000000000000000',
  `latitude` decimal(18,15) NOT NULL DEFAULT '0.000000000000000',
  `zoom` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `reference` (`reference`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_immo_contact` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `firstname` varchar(255) NOT NULL DEFAULT '',
  `street` varchar(255) NOT NULL DEFAULT '',
  `zip` int(5) NOT NULL DEFAULT '0',
  `location` varchar(255) NOT NULL DEFAULT '',
  `company` varchar(255) NOT NULL DEFAULT '',
  `telephone` varchar(30) NOT NULL DEFAULT '',
  `telephone_office` varchar(30) NOT NULL DEFAULT '',
  `telephone_mobile` varchar(30) NOT NULL DEFAULT '',
  `purchase` tinyint(1) NOT NULL DEFAULT '0',
  `funding` tinyint(1) NOT NULL DEFAULT '0',
  `comment` text NOT NULL,
  `immo_id` int(11) NOT NULL DEFAULT '0',
  `field_id` int(11) NOT NULL DEFAULT '0',
  `timestamp` int(14) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `immo_id` (`immo_id`),
  KEY `field_id` (`field_id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_immo_content` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `immo_id` int(11) NOT NULL DEFAULT '0',
  `lang_id` tinyint(4) NOT NULL DEFAULT '0',
  `field_id` int(10) unsigned NOT NULL DEFAULT '0',
  `fieldvalue` text NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `field_id` (`field_id`),
  KEY `immo_id` (`immo_id`),
  KEY `fieldvalue` (`fieldvalue`(64))
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_immo_field` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('text','textarea','img','link','protected_link','panorama','digits_only','price') NOT NULL DEFAULT 'text',
  `order` int(11) NOT NULL DEFAULT '1000',
  `mandatory` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_immo_fieldname` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `field_id` int(10) unsigned NOT NULL DEFAULT '0',
  `lang_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '-',
  PRIMARY KEY (`id`),
  KEY `field_id` (`field_id`),
  KEY `lang_id` (`lang_id`),
  KEY `name` (`name`(5))
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_immo_image` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `immo_id` int(11) NOT NULL DEFAULT '0',
  `field_id` int(10) unsigned NOT NULL DEFAULT '0',
  `uri` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `immo_id` (`immo_id`),
  KEY `field_id` (`field_id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_immo_interest` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `immo_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(60) NOT NULL DEFAULT '',
  `firstname` varchar(60) NOT NULL DEFAULT '',
  `street` varchar(100) NOT NULL DEFAULT '',
  `zip` varchar(10) NOT NULL DEFAULT '',
  `location` varchar(100) NOT NULL DEFAULT '',
  `email` varchar(60) NOT NULL DEFAULT '',
  `phone_office` varchar(40) NOT NULL DEFAULT '',
  `phone_home` varchar(40) NOT NULL DEFAULT '',
  `phone_mobile` varchar(40) NOT NULL DEFAULT '',
  `doc_via_mail` tinyint(1) NOT NULL DEFAULT '0',
  `funding_advice` tinyint(1) NOT NULL DEFAULT '0',
  `inspection` tinyint(1) NOT NULL DEFAULT '0',
  `contact_via_phone` tinyint(1) NOT NULL DEFAULT '0',
  `comment` text NOT NULL,
  `time` int(14) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `immo_id` (`immo_id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_immo_languages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `language` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_immo_settings` (
  `setid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `setname` varchar(80) NOT NULL DEFAULT '',
  `setvalue` text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`setid`),
  UNIQUE KEY `setname` (`setname`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_immo_statistics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `immo_id` int(11) NOT NULL DEFAULT '0',
  `field_id` int(11) NOT NULL DEFAULT '0',
  `hits` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_jobs` (
  `id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `date` int(14) DEFAULT NULL,
  `title` varchar(250) NOT NULL DEFAULT '',
  `author` varchar(150) NOT NULL DEFAULT '',
  `text` mediumtext NOT NULL,
  `workloc` varchar(250) NOT NULL DEFAULT '',
  `workload` varchar(250) NOT NULL DEFAULT '',
  `work_start` int(14) NOT NULL DEFAULT '0',
  `catid` int(2) unsigned NOT NULL DEFAULT '0',
  `lang` int(2) unsigned NOT NULL DEFAULT '0',
  `userid` int(6) unsigned NOT NULL DEFAULT '0',
  `startdate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `enddate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `changelog` int(14) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  FULLTEXT KEY `newsindex` (`title`,`text`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_jobs_categories` (
  `catid` int(2) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `lang` int(2) unsigned NOT NULL DEFAULT '1',
  `sort_style` enum('alpha','date','date_alpha') NOT NULL DEFAULT 'alpha',
  PRIMARY KEY (`catid`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_jobs_location` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_jobs_rel_loc_jobs` (
  `job` int(10) unsigned NOT NULL DEFAULT '0',
  `location` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`job`,`location`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_jobs_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL DEFAULT '',
  `value` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_knowledge_article_content` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `article` int(10) unsigned NOT NULL DEFAULT '0',
  `lang` int(10) unsigned NOT NULL DEFAULT '0',
  `question` text NOT NULL,
  `answer` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `module_knowledge_article_content_lang` (`lang`),
  KEY `module_knowledge_article_content_article` (`article`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_knowledge_articles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category` int(10) unsigned NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `hits` int(11) NOT NULL DEFAULT '0',
  `votes` int(11) NOT NULL DEFAULT '0',
  `votevalue` int(11) NOT NULL DEFAULT '0',
  `sort` int(11) NOT NULL DEFAULT '0',
  `date_created` int(14) NOT NULL DEFAULT '0',
  `date_updated` int(14) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_knowledge_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `parent` int(10) unsigned NOT NULL DEFAULT '0',
  `sort` int(10) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `module_knowledge_categories_sort` (`sort`),
  KEY `module_knowledge_categories_parent` (`parent`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_knowledge_categories_content` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `lang` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_knowledge_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `value` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `module_knowledge_settings_name` (`name`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_knowledge_tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `lang` int(10) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `module_knowledge_tags_name` (`name`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_knowledge_tags_articles` (
  `article` int(10) unsigned NOT NULL DEFAULT '0',
  `tag` int(10) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `article` (`article`,`tag`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_livecam` (
  `id` int(10) unsigned NOT NULL DEFAULT '1',
  `currentImagePath` varchar(255) NOT NULL DEFAULT '/webcam/cam1/current.jpg',
  `archivePath` varchar(255) NOT NULL DEFAULT '/webcam/cam1/archive/',
  `thumbnailPath` varchar(255) NOT NULL DEFAULT '/webcam/cam1/thumbs/',
  `maxImageWidth` int(10) unsigned NOT NULL DEFAULT '400',
  `thumbMaxSize` int(10) unsigned NOT NULL DEFAULT '200',
  `shadowboxActivate` set('1','0') NOT NULL DEFAULT '1',
  `showFrom` int(14) NOT NULL DEFAULT '0',
  `showTill` int(14) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_livecam_settings` (
  `setid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `setname` varchar(255) NOT NULL DEFAULT '',
  `setvalue` text NOT NULL,
  PRIMARY KEY (`setid`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_market` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `email` varchar(100) NOT NULL DEFAULT '',
  `type` set('search','offer') NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` mediumtext NOT NULL,
  `color` varchar(50) NOT NULL DEFAULT '',
  `premium` int(1) NOT NULL DEFAULT '0',
  `picture` varchar(255) NOT NULL DEFAULT '',
  `catid` int(4) NOT NULL DEFAULT '0',
  `price` varchar(10) NOT NULL DEFAULT '',
  `regdate` varchar(20) NOT NULL DEFAULT '',
  `enddate` varchar(20) NOT NULL DEFAULT '',
  `userid` int(4) NOT NULL DEFAULT '0',
  `userdetails` int(1) NOT NULL DEFAULT '0',
  `status` int(1) NOT NULL DEFAULT '0',
  `regkey` varchar(50) NOT NULL DEFAULT '',
  `paypal` int(1) NOT NULL DEFAULT '0',
  `sort_id` int(4) NOT NULL DEFAULT '0',
  `spez_field_1` varchar(255) NOT NULL,
  `spez_field_2` varchar(255) NOT NULL,
  `spez_field_3` varchar(255) NOT NULL,
  `spez_field_4` varchar(255) NOT NULL,
  `spez_field_5` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  FULLTEXT KEY `description` (`description`),
  FULLTEXT KEY `title` (`description`,`title`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_market_categories` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `displayorder` int(4) NOT NULL DEFAULT '0',
  `status` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_market_mail` (
  `id` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `content` longtext NOT NULL,
  `mailto` varchar(10) NOT NULL,
  `mailcc` mediumtext NOT NULL,
  `active` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_market_paypal` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `active` int(1) NOT NULL DEFAULT '0',
  `profile` varchar(255) NOT NULL DEFAULT '',
  `price` varchar(10) NOT NULL DEFAULT '',
  `price_premium` varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_market_settings` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `value` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `type` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_market_spez_fields` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `value` varchar(100) NOT NULL,
  `type` int(1) NOT NULL DEFAULT '1',
  `lang_id` int(2) NOT NULL DEFAULT '0',
  `active` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_media_settings` (
  `name` varchar(50) NOT NULL,
  `value` varchar(250) NOT NULL,
  KEY `name` (`name`)
) ENGINE=InnoDB;
CREATE TABLE `contrexx_module_mediadir_categories` (
  `id` int(7) NOT NULL AUTO_INCREMENT,
  `parent_id` int(7) NOT NULL,
  `order` int(7) NOT NULL,
  `show_subcategories` int(11) NOT NULL,
  `show_entries` int(1) NOT NULL,
  `picture` mediumtext NOT NULL,
  `active` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_mediadir_categories_names` (
  `lang_id` int(1) NOT NULL,
  `category_id` int(7) NOT NULL,
  `category_name` varchar(255) NOT NULL,
  `category_description` mediumtext NOT NULL,
  KEY `lang_id` (`lang_id`),
  KEY `category_id` (`category_id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_mediadir_comments` (
  `id` int(7) NOT NULL AUTO_INCREMENT,
  `entry_id` int(7) NOT NULL,
  `added_by` varchar(255) NOT NULL,
  `date` varchar(100) NOT NULL,
  `ip` varchar(100) NOT NULL,
  `name` varchar(255) NOT NULL,
  `mail` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `notification` int(1) NOT NULL DEFAULT '0',
  `comment` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_mediadir_entries` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `order` int(7) NOT NULL DEFAULT '0',
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
  `ready_to_confirm` int(1) NOT NULL DEFAULT '0',
  `confirmed` int(1) NOT NULL,
  `active` int(1) NOT NULL,
  `duration_type` int(1) NOT NULL,
  `duration_start` int(50) NOT NULL,
  `duration_end` int(50) NOT NULL,
  `duration_notification` int(1) NOT NULL,
  `translation_status` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `lang_id` (`lang_id`),
  KEY `active` (`active`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_mediadir_form_names` (
  `lang_id` int(1) NOT NULL,
  `form_id` int(7) NOT NULL,
  `form_name` varchar(255) NOT NULL,
  `form_description` mediumtext NOT NULL
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_mediadir_forms` (
  `id` int(7) NOT NULL AUTO_INCREMENT,
  `order` int(7) NOT NULL,
  `picture` mediumtext NOT NULL,
  `active` int(1) NOT NULL,
  `use_level` int(1) NOT NULL,
  `use_category` int(1) NOT NULL,
  `use_ready_to_confirm` int(1) NOT NULL,
  `cmd` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_mediadir_inputfield_names` (
  `lang_id` int(10) NOT NULL,
  `form_id` int(7) NOT NULL,
  `field_id` int(10) NOT NULL,
  `field_name` varchar(255) NOT NULL,
  `field_default_value` mediumtext NOT NULL,
  `field_info` mediumtext NOT NULL,
  KEY `field_id` (`field_id`),
  KEY `lang_id` (`lang_id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_mediadir_inputfield_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `active` int(1) NOT NULL,
  `multi_lang` int(1) NOT NULL,
  `exp_search` int(7) NOT NULL,
  `dynamic` int(1) NOT NULL,
  `comment` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_mediadir_inputfield_verifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `regex` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_mediadir_inputfields` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `form` int(7) NOT NULL,
  `type` int(10) NOT NULL,
  `verification` int(10) NOT NULL,
  `search` int(10) NOT NULL,
  `required` int(10) NOT NULL,
  `order` int(10) NOT NULL,
  `show_in` int(10) NOT NULL,
  `context_type` enum('none','title','address','zip','city','country') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_mediadir_level_names` (
  `lang_id` int(1) NOT NULL,
  `level_id` int(7) NOT NULL,
  `level_name` varchar(255) NOT NULL,
  `level_description` mediumtext NOT NULL,
  KEY `lang_id` (`lang_id`),
  KEY `category_id` (`level_id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_mediadir_levels` (
  `id` int(7) NOT NULL AUTO_INCREMENT,
  `parent_id` int(7) NOT NULL,
  `order` int(7) NOT NULL,
  `show_sublevels` int(11) NOT NULL,
  `show_categories` int(1) NOT NULL,
  `show_entries` int(1) NOT NULL,
  `picture` mediumtext NOT NULL,
  `active` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_mediadir_mail_actions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `default_recipient` enum('admin','author') NOT NULL,
  `need_auth` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_mediadir_mails` (
  `id` int(7) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `recipients` mediumtext NOT NULL,
  `lang_id` int(1) NOT NULL,
  `action_id` int(1) NOT NULL,
  `is_default` int(1) NOT NULL,
  `active` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_mediadir_masks` (
  `id` int(7) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `fields` mediumtext NOT NULL,
  `active` int(11) NOT NULL,
  `form_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_mediadir_order_rel_forms_selectors` (
  `selector_id` int(7) NOT NULL,
  `form_id` int(7) NOT NULL,
  `selector_order` int(7) NOT NULL,
  `exp_search` int(1) NOT NULL
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_mediadir_rel_entry_categories` (
  `entry_id` int(10) NOT NULL,
  `category_id` int(10) NOT NULL,
  KEY `entry_id` (`entry_id`),
  KEY `category_id` (`category_id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_mediadir_rel_entry_inputfields` (
  `entry_id` int(7) NOT NULL,
  `lang_id` int(7) NOT NULL,
  `form_id` int(7) NOT NULL,
  `field_id` int(7) NOT NULL,
  `value` longtext NOT NULL,
  UNIQUE KEY `entry_id` (`entry_id`,`lang_id`,`form_id`,`field_id`),
  FULLTEXT KEY `value` (`value`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_mediadir_rel_entry_inputfields_clean1` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entry_id` int(7) NOT NULL,
  `lang_id` int(7) NOT NULL,
  `form_id` int(7) NOT NULL,
  `field_id` int(7) NOT NULL,
  `value` longtext NOT NULL,
  PRIMARY KEY (`id`),
  FULLTEXT KEY `value` (`value`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_mediadir_rel_entry_levels` (
  `entry_id` int(10) NOT NULL,
  `level_id` int(10) NOT NULL,
  KEY `entry_id` (`entry_id`),
  KEY `category_id` (`level_id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_mediadir_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_mediadir_settings_num_categories` (
  `group_id` int(1) NOT NULL,
  `num_categories` varchar(10) NOT NULL
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_mediadir_settings_num_entries` (
  `group_id` int(1) NOT NULL,
  `num_entries` varchar(10) NOT NULL DEFAULT 'n'
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_mediadir_settings_num_levels` (
  `group_id` int(1) NOT NULL,
  `num_levels` varchar(10) NOT NULL
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_mediadir_settings_perm_group_forms` (
  `group_id` int(7) NOT NULL,
  `form_id` int(1) NOT NULL,
  `status_group` int(1) NOT NULL
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_mediadir_votes` (
  `id` int(7) NOT NULL AUTO_INCREMENT,
  `entry_id` int(7) NOT NULL,
  `added_by` varchar(255) NOT NULL,
  `date` varchar(100) NOT NULL,
  `ip` varchar(100) NOT NULL,
  `vote` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_memberdir_directories` (
  `dirid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parentdir` int(11) NOT NULL DEFAULT '0',
  `active` set('1','0') NOT NULL DEFAULT '1',
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `displaymode` set('0','1','2') NOT NULL DEFAULT '0',
  `sort` int(11) NOT NULL DEFAULT '1',
  `pic1` set('1','0') NOT NULL DEFAULT '0',
  `pic2` set('1','0') NOT NULL DEFAULT '0',
  `lang_id` int(2) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`dirid`),
  FULLTEXT KEY `memberdir_dir` (`name`,`description`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_memberdir_name` (
  `field` int(10) unsigned NOT NULL DEFAULT '0',
  `dirid` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `active` set('0','1') NOT NULL DEFAULT '',
  `lang_id` int(2) unsigned NOT NULL DEFAULT '1'
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_memberdir_settings` (
  `setid` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `setname` varchar(255) NOT NULL DEFAULT '',
  `setvalue` text NOT NULL,
  `lang_id` int(2) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`setid`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_memberdir_values` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dirid` int(14) NOT NULL DEFAULT '0',
  `pic1` varchar(255) NOT NULL DEFAULT '',
  `pic2` varchar(255) NOT NULL DEFAULT '',
  `0` smallint(5) unsigned NOT NULL DEFAULT '0',
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
  `lang_id` int(2) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
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
  `startdate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `enddate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
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
  `allow_comments` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_news_categories` (
  `catid` int(2) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `left_id` int(11) NOT NULL,
  `right_id` int(11) NOT NULL,
  `sorting` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  PRIMARY KEY (`catid`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_news_categories_catid` (
  `id` int(11) NOT NULL
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_news_categories_locale` (
  `category_id` int(11) unsigned NOT NULL DEFAULT '0',
  `lang_id` int(11) unsigned NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`category_id`,`lang_id`),
  FULLTEXT KEY `name` (`name`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_news_categories_locks` (
  `lockId` varchar(32) NOT NULL,
  `lockTable` varchar(32) NOT NULL,
  `lockStamp` bigint(11) NOT NULL
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_news_comments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(250) NOT NULL DEFAULT '',
  `text` mediumtext NOT NULL,
  `newsid` int(6) unsigned NOT NULL DEFAULT '0',
  `date` int(14) DEFAULT NULL,
  `poster_name` varchar(255) NOT NULL DEFAULT '',
  `user