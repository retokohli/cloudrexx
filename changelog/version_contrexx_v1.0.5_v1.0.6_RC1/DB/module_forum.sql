CREATE TABLE `contrexx_module_forum_category` (
`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
`name` TINYTEXT NOT NULL ,
`order_id` INT( 11 ) UNSIGNED NOT NULL ,
`security_id` INT( 11 ) NOT NULL ,
PRIMARY KEY ( `id` )
);

CREATE TABLE `contrexx_module_forum_entry` (
`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
`parent_id` INT( 11 ) UNSIGNED NOT NULL ,
`thread_id` INT( 11 ) UNSIGNED NOT NULL ,
`create_time` INT( 14 ) NOT NULL ,
`last_answer_time` INT( 14 ) NOT NULL ,
`edited_time` INT( 14 ) NOT NULL ,
`edited_by` TINYTEXT NOT NULL ,
`user_id` INT( 11 ) UNSIGNED NOT NULL ,
`user_name` TINYTEXT NOT NULL ,
`user_email` TINYTEXT NOT NULL ,
`user_webpage` TINYTEXT NOT NULL ,
`user_place` TINYTEXT NOT NULL ,
`user_show_signature` INT( 1 ) UNSIGNED,
`user_email_notify` INT( 1 ) UNSIGNED,
`category_id` INT( 11 ) UNSIGNED NOT NULL ,
`subject` TINYTEXT NOT NULL ,
`message` TEXT NOT NULL ,
`locked` INT( 1 ) UNSIGNED,
`fixed` INT( 1 ) UNSIGNED,
PRIMARY KEY ( `id` )
);

CREATE TABLE `contrexx_module_forum_user` (
`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
`webpage` TINYTEXT NOT NULL ,
`place` TINYTEXT NOT NULL ,
`signature` TINYTEXT NOT NULL ,
`profile` TEXT,
`view` INT( 2 ) UNSIGNED NOT NULL ,
`show_email` INT( 1 ) UNSIGNED,
`send_private_messages` INT( 1 ) UNSIGNED,
`notify_new_posting` INT( 1 ) UNSIGNED,
`notify_new_user` INT( 1 ) UNSIGNED,
`locked` INT( 1 ) UNSIGNED,
PRIMARY KEY ( `id` )
);

CREATE TABLE `contrexx_module_forum_config` (
`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
`name` VARCHAR( 64 ) NOT NULL ,
`value` VARCHAR( 255 ) NOT NULL ,
`status` INT( 1 ) UNSIGNED DEFAULT '1',
PRIMARY KEY ( `id` )
);

CREATE TABLE `contrexx_module_forum_access` (
`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
`name` VARCHAR( 64 ) NOT NULL ,
`description` VARCHAR( 255 ) NOT NULL ,
`group_ids` TINYTEXT NOT NULL ,
`type` ENUM( "global", "frontend", "backend" ) NOT NULL ,
PRIMARY KEY ( `id` )
);