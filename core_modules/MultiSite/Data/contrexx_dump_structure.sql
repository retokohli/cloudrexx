SET FOREIGN_KEY_CHECKS = 0;
CREATE TABLE `contrexx_access_group_dynamic_ids` (
  `access_id` int(11) unsigned NOT NULL default '0',
  `group_id` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`access_id`,`group_id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_access_group_static_ids` (
  `access_id` int(11) unsigned NOT NULL default '0',
  `group_id` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`access_id`,`group_id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_access_rel_user_group` (
  `user_id` int(10) unsigned NOT NULL default '0',
  `group_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`user_id`,`group_id`)
) ENGINE=InnoDB;
CREATE TABLE `contrexx_access_settings` (
  `key` varchar(32) NOT NULL default '',
  `value` varchar(255) NOT NULL default '',
  `status` tinyint(1) unsigned NOT NULL default '0',
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB;
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
CREATE TABLE `contrexx_access_user_attribute_name` (
  `attribute_id` int(10) unsigned NOT NULL default '0',
  `lang_id` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`attribute_id`,`lang_id`)
) ENGINE=InnoDB;
CREATE TABLE `contrexx_access_user_attribute_value` (
  `attribute_id` int(10) unsigned NOT NULL default '0',
  `user_id` int(10) unsigned NOT NULL default '0',
  `history_id` int(10) unsigned NOT NULL default '0',
  `value` text NOT NULL,
  PRIMARY KEY  (`attribute_id`,`user_id`,`history_id`),
  FULLTEXT KEY `value` (`value`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_access_user_core_attribute` (
  `id` varchar(25) NOT NULL,
  `mandatory` enum('0','1') NOT NULL default '0',
  `sort_type` enum('asc','desc','custom') NOT NULL default 'asc',
  `order_id` int(10) unsigned NOT NULL default '0',
  `access_special` enum('','menu_select_higher','menu_select_lower') NOT NULL default '',
  `access_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB;
CREATE TABLE `contrexx_access_user_groups` (
  `group_id` int(6) unsigned NOT NULL auto_increment,
  `group_name` varchar(100) NOT NULL default '',
  `group_description` varchar(255) NOT NULL default '',
  `is_active` tinyint(4) NOT NULL default '1',
  `type` enum('frontend','backend') NOT NULL default 'frontend',
  `homepage` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`group_id`)
) ENGINE=MyISAM ;
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
CREATE TABLE `contrexx_access_user_network` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `oauth_provider` varchar(100) NOT NULL default '',
  `oauth_id` varchar(100) NOT NULL default '',
  `user_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB;
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
  `interests` text,
  `signature` text,
  `picture` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`user_id`),
  KEY `profile` (`firstname`(100),`lastname`(100),`company`(50))
) ENGINE=InnoDB;
CREATE TABLE `contrexx_access_user_title` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `order_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `title` (`title`)
) ENGINE=InnoDB;
CREATE TABLE `contrexx_access_user_validity` (
  `validity` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`validity`)
) ENGINE=InnoDB;
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
CREATE TABLE `contrexx_component` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `type` enum('core','core_module','module') NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB;
CREATE TABLE `contrexx_content_node` (
  `id` int(11) NOT NULL auto_increment,
  `parent_id` int(11) default NULL,
  `lft` int(11) NOT NULL,
  `rgt` int(11) NOT NULL,
  `lvl` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `IDX_E5A18FDD727ACA70` (`parent_id`),
  CONSTRAINT `contrexx_content_node_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `contrexx_content_node` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB;
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
  `useCustomContentForAllChannels` int(2) default NULL,
  `applicationTemplate` varchar(100) default NULL,
  `useCustomApplicationTemplateForAllChannels` tinyint(2) default NULL,
  `cssName` varchar(255) default NULL,
  `cssNavName` varchar(255) default NULL,
  `skin` int(11) default NULL,
  `useSkinForAllChannels` int(2) default NULL,
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
  CONSTRAINT `contrexx_content_page_ibfk_3` FOREIGN KEY (`node_id`) REFERENCES `contrexx_content_node` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION
) ENGINE=InnoDB;
CREATE TABLE `contrexx_core_country` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `alpha2` char(2) NOT NULL default '',
  `alpha3` char(3) NOT NULL default '',
  `ord` int(5) unsigned NOT NULL default '0',
  `active` tinyint(1) unsigned NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_core_mail_template` (
  `key` tinytext NOT NULL,
  `section` tinytext NOT NULL,
  `text_id` int(10) unsigned NOT NULL,
  `html` tinyint(1) unsigned NOT NULL default '0',
  `protected` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`key`(32),`section`(32))
) ENGINE=MyISAM;
CREATE TABLE `contrexx_core_module_multisite_domain` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(200) NOT NULL,
  `websiteId` int(11) NOT NULL,
  `type` varchar(12) NOT NULL,
  `pleskId` int(11) default NULL,
  PRIMARY KEY  (`id`),
  KEY `name` (`name`),
  KEY `websiteId` (`websiteId`),
  CONSTRAINT `contrexx_core_module_multisite_domain_ibfk_1` FOREIGN KEY (`websiteId`) REFERENCES `contrexx_core_module_multisite_website` (`id`)
) ENGINE=InnoDB;
CREATE TABLE `contrexx_core_module_multisite_user_website` (
  `websiteId` int(11) unsigned NOT NULL,
  `multiSiteUserId` int(11) unsigned NOT NULL,
  `userId` int(5) unsigned NOT NULL,
  `role` enum('admin','user') NOT NULL default 'user'
) ENGINE=InnoDB;
CREATE TABLE `contrexx_core_module_multisite_website` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(200) NOT NULL,
  `language` varchar(50) NOT NULL,
  `status` int(11) NOT NULL,
  `websiteServiceServerId` int(11) NOT NULL,
  `secretKey` varchar(255) NOT NULL,
  `ipAddress` varchar(45) NOT NULL,
  `ownerId` int(11) NOT NULL,
  `installationId` varchar(40) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `name_index` (`name`)
) ENGINE=InnoDB;
CREATE TABLE `contrexx_core_module_multisite_website_service_server` (
  `id` int(11) NOT NULL auto_increment,
  `hostname` varchar(255) NOT NULL,
  `label` varchar(225) NOT NULL,
  `secretKey` varchar(32) NOT NULL,
  `installationId` varchar(40) NOT NULL,
  `isDefault` int(1) NOT NULL,
  `httpAuthMethod` varchar(6) default NULL,
  `httpAuthUsername` varchar(255) NOT NULL,
  `httpAuthPassword` varchar(255) NOT NULL,
  `defaultWebsiteIp` varchar(45) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB;
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
CREATE TABLE `contrexx_core_text` (
  `id` int(10) unsigned NOT NULL default '0',
  `lang_id` int(10) unsigned NOT NULL default '1',
  `section` varchar(32) NOT NULL default '',
  `key` varchar(255) NOT NULL,
  `text` text NOT NULL,
  PRIMARY KEY  (`id`,`lang_id`,`section`,`key`(32)),
  FULLTEXT KEY `text` (`text`)
) ENGINE=MyISAM;
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
  `fallback` int(2) unsigned default '0',
  `app_themes_id` int(2) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `lang` (`lang`),
  KEY `defaultstatus` (`is_default`),
  KEY `name` (`name`),
  FULLTEXT KEY `name_2` (`name`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_lib_country` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(64) NOT NULL,
  `iso_code_2` char(2) NOT NULL,
  `iso_code_3` char(3) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique` (`iso_code_2`),
  KEY `INDEX_COUNTRIES_NAME` (`name`)
) ENGINE=InnoDB;
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
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_log_entry` (
  `id` int(11) NOT NULL auto_increment,
  `action` varchar(8) NOT NULL,
  `logged_at` timestamp NULL default NULL,
  `version` int(11) NOT NULL,
  `object_id` varchar(32) default NULL,
  `object_class` varchar(255) NOT NULL,
  `data` longtext,
  `username` varchar(255) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `log_class_unique_version_idx` (`version`,`object_id`,`object_class`),
  KEY `log_class_lookup_idx` (`object_class`),
  KEY `log_date_lookup_idx` (`logged_at`),
  KEY `log_user_lookup_idx` (`username`)
) ENGINE=InnoDB;
CREATE TABLE `contrexx_module_block_blocks` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `start` int(10) NOT NULL default '0',
  `end` int(10) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `random` int(1) NOT NULL default '0',
  `random_2` int(1) NOT NULL default '0',
  `random_3` int(1) NOT NULL default '0',
  `random_4` int(1) NOT NULL default '0',
  `global` int(1) NOT NULL default '0',
  `category` int(1) NOT NULL default '0',
  `direct` int(1) NOT NULL default '0',
  `active` int(1) NOT NULL default '0',
  `order` int(1) NOT NULL default '0',
  `cat` int(10) NOT NULL default '0',
  `wysiwyg_editor` int(1) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_block_categories` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `parent` int(10) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `seperator` varchar(255) NOT NULL default '',
  `order` int(10) NOT NULL default '0',
  `status` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_block_rel_lang_content` (
  `block_id` int(10) unsigned NOT NULL default '0',
  `lang_id` int(10) unsigned NOT NULL default '0',
  `content` mediumtext NOT NULL,
  `active` int(1) NOT NULL default '0',
  UNIQUE KEY `id_lang` (`block_id`,`lang_id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_block_rel_pages` (
  `block_id` int(7) NOT NULL default '0',
  `page_id` int(7) NOT NULL default '0',
  `placeholder` enum('global','direct','category') NOT NULL default 'global',
  PRIMARY KEY  (`block_id`,`page_id`,`placeholder`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_block_settings` (
  `id` int(7) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `value` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_contact_form` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `mails` text NOT NULL,
  `showForm` tinyint(1) unsigned NOT NULL default '0',
  `use_captcha` tinyint(1) unsigned NOT NULL default '1',
  `use_custom_style` tinyint(1) unsigned NOT NULL default '0',
  `save_data_in_crm` tinyint(1) NOT NULL default '0',
  `send_copy` tinyint(1) NOT NULL default '0',
  `use_email_of_sender` tinyint(1) NOT NULL default '0',
  `html_mail` tinyint(1) unsigned NOT NULL default '1',
  `send_attachment` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM ;
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
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_contact_form_field` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_form` int(10) unsigned NOT NULL default '0',
  `type` enum('text','label','checkbox','checkboxGroup','country','date','file','multi_file','fieldset','hidden','horizontalLine','password','radio','select','textarea','recipient','special') NOT NULL default 'text',
  `special_type` varchar(20) NOT NULL,
  `is_required` set('0','1') NOT NULL default '0',
  `check_type` int(3) NOT NULL default '1',
  `order_id` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_contact_form_field_lang` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `fieldID` int(10) unsigned NOT NULL,
  `langID` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `attributes` text NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `fieldID` (`fieldID`,`langID`)
) ENGINE=MyISAM ;
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
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_contact_form_submit_data` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_entry` int(10) unsigned NOT NULL,
  `id_field` int(10) unsigned NOT NULL,
  `formlabel` text NOT NULL,
  `formvalue` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_contact_recipient` (
  `id` int(11) NOT NULL auto_increment,
  `id_form` int(11) NOT NULL default '0',
  `email` varchar(250) NOT NULL default '',
  `sort` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_contact_recipient_lang` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `recipient_id` int(10) unsigned NOT NULL,
  `langID` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `recipient_id` (`recipient_id`,`langID`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_contact_settings` (
  `setid` int(6) unsigned NOT NULL auto_increment,
  `setname` varchar(250) NOT NULL default '',
  `setvalue` text NOT NULL,
  `status` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`setid`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_filesharing` (
  `id` int(10) NOT NULL auto_increment,
  `file` varchar(250) NOT NULL,
  `source` varchar(250) NOT NULL,
  `cmd` varchar(50) NOT NULL,
  `hash` varchar(50) NOT NULL,
  `check` varchar(50) NOT NULL,
  `expiration_date` timestamp NULL default NULL,
  `upload_id` int(10) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_filesharing_mail_template` (
  `id` int(10) NOT NULL auto_increment,
  `lang_id` int(1) NOT NULL,
  `subject` varchar(250) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_media_settings` (
  `name` varchar(50) NOT NULL,
  `value` varchar(250) NOT NULL,
  KEY `name` (`name`)
) ENGINE=InnoDB;
CREATE TABLE `contrexx_module_news` (
  `id` int(6) unsigned NOT NULL auto_increment,
  `date` int(14) default NULL,
  `redirect` varchar(250) NOT NULL default '',
  `source` varchar(250) NOT NULL default '',
  `url1` varchar(250) NOT NULL default '',
  `url2` varchar(250) NOT NULL default '',
  `catid` int(2) unsigned NOT NULL default '0',
  `typeid` int(2) unsigned NOT NULL default '0',
  `publisher` varchar(255) NOT NULL default '',
  `publisher_id` int(5) unsigned NOT NULL default '0',
  `author` varchar(255) NOT NULL default '',
  `author_id` int(5) unsigned NOT NULL default '0',
  `userid` int(6) unsigned NOT NULL default '0',
  `startdate` timestamp NOT NULL default '0000-00-00 00:00:00',
  `enddate` timestamp NOT NULL default '0000-00-00 00:00:00',
  `status` tinyint(4) NOT NULL default '1',
  `validated` enum('0','1') NOT NULL default '0',
  `frontend_access_id` int(10) unsigned NOT NULL default '0',
  `backend_access_id` int(10) unsigned NOT NULL default '0',
  `teaser_only` enum('0','1') NOT NULL default '0',
  `teaser_frames` text NOT NULL,
  `teaser_show_link` tinyint(1) unsigned NOT NULL default '1',
  `teaser_image_path` text NOT NULL,
  `teaser_image_thumbnail_path` text NOT NULL,
  `changelog` int(14) NOT NULL default '0',
  `allow_comments` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_news_categories` (
  `catid` int(2) unsigned NOT NULL auto_increment,
  `parent_id` int(11) NOT NULL,
  `left_id` int(11) NOT NULL,
  `right_id` int(11) NOT NULL,
  `sorting` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  PRIMARY KEY  (`catid`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_news_categories_catid` (
  `id` int(11) NOT NULL
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_news_categories_locale` (
  `category_id` int(11) unsigned NOT NULL default '0',
  `lang_id` int(11) unsigned NOT NULL default '0',
  `name` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`category_id`,`lang_id`),
  FULLTEXT KEY `name` (`name`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_news_categories_locks` (
  `lockId` varchar(32) NOT NULL,
  `lockTable` varchar(32) NOT NULL,
  `lockStamp` bigint(11) NOT NULL
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_news_comments` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(250) NOT NULL default '',
  `text` mediumtext NOT NULL,
  `newsid` int(6) unsigned NOT NULL default '0',
  `date` int(14) default NULL,
  `poster_name` varchar(255) NOT NULL default '',
  `userid` int(5) unsigned NOT NULL default '0',
  `ip_address` varchar(15) NOT NULL default '0.0.0.0',
  `is_active` enum('0','1') NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_news_locale` (
  `news_id` int(11) unsigned NOT NULL default '0',
  `lang_id` int(11) unsigned NOT NULL default '0',
  `is_active` int(1) unsigned NOT NULL default '1',
  `title` varchar(250) NOT NULL default '',
  `text` mediumtext NOT NULL,
  `teaser_text` text NOT NULL,
  PRIMARY KEY  (`news_id`,`lang_id`),
  FULLTEXT KEY `newsindex` (`text`,`title`,`teaser_text`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_news_settings` (
  `name` varchar(50) NOT NULL default '',
  `value` varchar(250) NOT NULL default '',
  KEY `name` (`name`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_news_settings_locale` (
  `name` varchar(50) NOT NULL default '',
  `lang_id` int(11) unsigned NOT NULL default '0',
  `value` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`name`,`lang_id`),
  FULLTEXT KEY `name` (`name`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_news_stats_view` (
  `user_sid` char(32) NOT NULL,
  `news_id` int(6) unsigned NOT NULL,
  `time` timestamp NOT NULL,
  KEY `idx_user_sid` (`user_sid`),
  KEY `idx_news_id` (`news_id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_news_teaser_frame` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `lang_id` int(3) unsigned NOT NULL default '0',
  `frame_template_id` int(10) unsigned NOT NULL default '0',
  `name` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_news_teaser_frame_templates` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `description` varchar(100) NOT NULL default '',
  `html` text NOT NULL,
  `source_code_mode` enum('0','1') NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_news_ticker` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `charset` enum('ISO-8859-1','UTF-8') NOT NULL default 'ISO-8859-1',
  `urlencode` tinyint(1) unsigned NOT NULL default '0',
  `prefix` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_news_types` (
  `typeid` int(2) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`typeid`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_news_types_locale` (
  `lang_id` int(11) unsigned NOT NULL default '0',
  `type_id` int(11) unsigned NOT NULL default '0',
  `name` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`lang_id`,`type_id`),
  FULLTEXT KEY `name` (`name`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_newsletter` (
  `id` int(11) NOT NULL auto_increment,
  `subject` varchar(255) NOT NULL default '',
  `template` int(11) NOT NULL default '0',
  `content` text NOT NULL,
  `attachment` enum('0','1') NOT NULL default '0',
  `priority` tinyint(1) NOT NULL default '0',
  `sender_email` varchar(255) NOT NULL default '',
  `sender_name` varchar(255) NOT NULL default '',
  `return_path` varchar(255) NOT NULL default '',
  `smtp_server` int(10) unsigned NOT NULL default '0',
  `status` int(1) NOT NULL default '0',
  `count` int(11) NOT NULL default '0',
  `recipient_count` int(11) unsigned NOT NULL default '0',
  `date_create` int(14) unsigned NOT NULL default '0',
  `date_sent` int(14) unsigned NOT NULL default '0',
  `tmp_copy` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_newsletter_access_user` (
  `accessUserID` int(5) unsigned NOT NULL,
  `newsletterCategoryID` int(11) NOT NULL,
  `code` varchar(255) NOT NULL default '',
  UNIQUE KEY `rel` (`accessUserID`,`newsletterCategoryID`),
  KEY `accessUserID` (`accessUserID`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_newsletter_attachment` (
  `id` int(11) NOT NULL auto_increment,
  `newsletter` int(11) NOT NULL default '0',
  `file_name` varchar(255) NOT NULL default '',
  `file_nr` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `newsletter` (`newsletter`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_newsletter_category` (
  `id` int(11) NOT NULL auto_increment,
  `status` tinyint(1) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `notification_email` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_newsletter_confirm_mail` (
  `id` int(1) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `content` longtext NOT NULL,
  `recipients` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_newsletter_email_link` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `email_id` int(11) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `email_id` (`email_id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_newsletter_email_link_feedback` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `link_id` int(11) unsigned NOT NULL,
  `email_id` int(11) unsigned NOT NULL,
  `recipient_id` int(11) unsigned NOT NULL,
  `recipient_type` enum('access','newsletter') NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `link_id` (`link_id`,`email_id`,`recipient_id`,`recipient_type`),
  KEY `email_id` (`email_id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_newsletter_rel_cat_news` (
  `newsletter` int(11) NOT NULL default '0',
  `category` int(11) NOT NULL default '0',
  PRIMARY KEY  (`newsletter`,`category`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_newsletter_rel_user_cat` (
  `user` int(11) NOT NULL default '0',
  `category` int(11) NOT NULL default '0',
  PRIMARY KEY  (`user`,`category`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_newsletter_rel_usergroup_newsletter` (
  `userGroup` int(10) unsigned NOT NULL,
  `newsletter` int(10) unsigned NOT NULL,
  UNIQUE KEY `uniq` (`userGroup`,`newsletter`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_newsletter_settings` (
  `setid` int(6) unsigned NOT NULL auto_increment,
  `setname` varchar(250) NOT NULL default '',
  `setvalue` text NOT NULL,
  `status` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`setid`),
  UNIQUE KEY `setname` (`setname`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_newsletter_template` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `html` text NOT NULL,
  `required` int(1) NOT NULL default '0',
  `type` enum('e-mail','news') NOT NULL default 'e-mail',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_newsletter_tmp_sending` (
  `id` int(11) NOT NULL auto_increment,
  `newsletter` int(11) NOT NULL default '0',
  `email` varchar(255) NOT NULL default '',
  `sendt` tinyint(1) NOT NULL default '0',
  `type` enum('access','newsletter','core') NOT NULL default 'newsletter',
  `code` varchar(10) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique_email` (`newsletter`,`email`),
  KEY `email` (`email`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_module_newsletter_user` (
  `id` int(11) NOT NULL auto_increment,
  `code` varchar(255) NOT NULL default '',
  `email` varchar(255) NOT NULL default '',
  `uri` varchar(255) NOT NULL default '',
  `sex` enum('m','f') default NULL,
  `salutation` int(10) unsigned NOT NULL default '0',
  `title` varchar(255) NOT NULL default '',
  `lastname` varchar(255) NOT NULL default '',
  `firstname` varchar(255) NOT NULL default '',
  `position` varchar(255) NOT NULL default '',
  `company` varchar(255) NOT NULL default '',
  `industry_sector` varchar(255) NOT NULL default '',
  `address` varchar(255) NOT NULL default '',
  `zip` varchar(255) NOT NULL default '',
  `city` varchar(255) NOT NULL default '',
  `country_id` smallint(5) unsigned NOT NULL default '0',
  `phone_office` varchar(255) NOT NULL default '',
  `phone_private` varchar(255) NOT NULL default '',
  `phone_mobile` varchar(255) NOT NULL default '',
  `fax` varchar(255) NOT NULL default '',
  `notes` text NOT NULL,
  `birthday` varchar(10) NOT NULL default '00-00-0000',
  `status` int(1) NOT NULL default '0',
  `emaildate` int(14) unsigned NOT NULL default '0',
  `language` int(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `status` (`status`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_newsletter_user_title` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `title` (`title`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_recommend` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `value` text NOT NULL,
  `lang_id` int(11) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM ;
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
  UNIQUE KEY `contentid` (`id`),
  FULLTEXT KEY `fulltextindex` (`title`,`content`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_module_u2u_address_list` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `buddies_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB;
CREATE TABLE `contrexx_module_u2u_message_log` (
  `message_id` int(11) unsigned NOT NULL auto_increment,
  `message_text` text NOT NULL,
  `message_title` text NOT NULL,
  PRIMARY KEY  (`message_id`)
) ENGINE=InnoDB;
CREATE TABLE `contrexx_module_u2u_sent_messages` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `userid` int(11) unsigned NOT NULL default '0',
  `message_id` int(11) unsigned NOT NULL default '0',
  `receiver_id` int(11) unsigned NOT NULL default '0',
  `mesage_open_status` enum('0','1') NOT NULL default '0',
  `date_time` timestamp NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB;
CREATE TABLE `contrexx_module_u2u_settings` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB;
CREATE TABLE `contrexx_module_u2u_user_log` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `userid` int(11) unsigned NOT NULL default '0',
  `user_sent_items` int(11) unsigned NOT NULL default '0',
  `user_unread_items` int(11) unsigned NOT NULL default '0',
  `user_status` enum('0','1') NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB;
CREATE TABLE `contrexx_modules` (
  `id` int(2) unsigned default NULL,
  `name` varchar(250) NOT NULL default '',
  `distributor` char(50) NOT NULL,
  `description_variable` varchar(50) NOT NULL default '',
  `status` set('y','n') NOT NULL default 'n',
  `is_required` tinyint(1) NOT NULL default '0',
  `is_core` tinyint(4) NOT NULL default '0',
  `is_active` tinyint(1) NOT NULL default '0',
  `is_licensed` tinyint(1) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_session_variable` (
  `id` int(11) NOT NULL auto_increment,
  `parent_id` int(11) NOT NULL,
  `sessionid` varchar(32) NOT NULL default '',
  `lastused` timestamp NOT NULL,
  `key` varchar(100) NOT NULL default '',
  `value` text,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `key_index` (`parent_id`,`key`,`sessionid`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_sessions` (
  `sessionid` varchar(255) NOT NULL default '',
  `remember_me` int(1) NOT NULL default '0',
  `startdate` varchar(14) NOT NULL default '',
  `lastupdated` varchar(14) NOT NULL default '',
  `status` varchar(20) NOT NULL default '',
  `user_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`sessionid`),
  KEY `LastUpdated` (`lastupdated`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_settings` (
  `setid` int(6) unsigned NOT NULL auto_increment,
  `setname` varchar(250) NOT NULL default '',
  `setvalue` text NOT NULL,
  `setmodule` tinyint(2) NOT NULL default '0',
  PRIMARY KEY  (`setid`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_settings_image` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_settings_smtp` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `hostname` varchar(255) NOT NULL,
  `port` smallint(5) unsigned NOT NULL default '25',
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_skins` (
  `id` int(2) unsigned NOT NULL auto_increment,
  `themesname` varchar(50) NOT NULL default '',
  `foldername` varchar(50) NOT NULL default '',
  `expert` int(1) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `theme_unique` (`themesname`),
  UNIQUE KEY `folder_unique` (`foldername`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_stats_browser` (
  `id` int(6) unsigned NOT NULL auto_increment,
  `name` varchar(255) binary NOT NULL default '',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique` (`name`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_stats_colourdepth` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `depth` tinyint(3) unsigned NOT NULL default '0',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique` (`depth`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_stats_config` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(64) NOT NULL default '',
  `value` varchar(255) NOT NULL default '',
  `status` int(1) default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM ;
CREATE TABLE `contrexx_stats_country` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `country` varchar(100) binary NOT NULL default '',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique` (`country`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_stats_hostname` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `hostname` varchar(255) binary NOT NULL default '',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique` (`hostname`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_stats_javascript` (
  `id` int(3) unsigned NOT NULL auto_increment,
  `support` enum('0','1') default '0',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_stats_operatingsystem` (
  `id` int(6) unsigned NOT NULL auto_increment,
  `name` varchar(255) binary NOT NULL default '',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique` (`name`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_stats_referer` (
  `id` int(8) unsigned NOT NULL auto_increment,
  `uri` varchar(255) binary NOT NULL default '',
  `timestamp` int(11) unsigned NOT NULL default '0',
  `count` mediumint(8) unsigned NOT NULL default '0',
  `sid` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique` (`uri`)
) ENGINE=MyISAM;
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
) ENGINE=MyISAM;
CREATE TABLE `contrexx_stats_requests_summary` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `type` varchar(10) NOT NULL default '',
  `timestamp` int(11) NOT NULL default '0',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique` (`type`,`timestamp`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_stats_screenresolution` (
  `id` int(5) unsigned NOT NULL auto_increment,
  `resolution` varchar(11) NOT NULL default '',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique` (`resolution`)
) ENGINE=MyISAM;
CREATE TABLE `contrexx_stats_search` (
  `id` int(5) unsigned NOT NULL auto_increment,
  `name` varchar(100) binary NOT NULL default '',
  `count` int(10) unsigned NOT NULL default '0',
  `sid` varchar(32) NOT NULL default '',
  `external` enum('0','1') NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique` (`name`,`external`)
) ENGINE=MyISAM;
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
) ENGINE=MyISAM;
CREATE TABLE `contrexx_stats_spiders_summary` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) binary NOT NULL default '',
  `timestamp` int(11) NOT NULL default '0',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique` (`name`)
) ENGINE=MyISAM;
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
) ENGINE=MyISAM;
CREATE TABLE `contrexx_stats_visitors_summary` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `type` varchar(10) NOT NULL default '',
  `timestamp` int(11) NOT NULL default '0',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique` (`type`,`timestamp`)
) ENGINE=MyISAM;
SET FOREIGN_KEY_CHECKS = 1;
