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
