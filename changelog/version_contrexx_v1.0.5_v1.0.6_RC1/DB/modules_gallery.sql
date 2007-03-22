# GALLERY
# -----------------------------------

INSERT INTO `contrexx_module_gallery_settings` ( `id` , `name` , `value` , `description` ) VALUES ('', 'quality', '80', 'TXT_QUALITY');

ALTER TABLE `contrexx_module_gallery_pictures` ADD `sorting` SMALLINT( 3 ) UNSIGNED DEFAULT '999' NOT NULL AFTER `catid` ;


INSERT INTO `contrexx_module_gallery_settings` ( `id` , `name` , `value` , `description` ) VALUES ('', 'show_comments', 'off', 'TXT_SETTINGS_COMMENTS');
INSERT INTO `contrexx_module_gallery_settings` ( `id` , `name` , `value` , `description` ) VALUES ('', 'show_voting', 'off', 'TXT_SETTINGS_VOTING');

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
);

CREATE TABLE `contrexx_module_gallery_votes` (
`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
`picid` INT(10) UNSIGNED NOT NULL ,
`date` INT( 14 ) UNSIGNED NOT NULL ,
`ip` VARCHAR( 15 ) NOT NULL ,
`md5` VARCHAR( 32 ) NOT NULL,
`mark` INT( 2 ) UNSIGNED NOT NULL ,
PRIMARY KEY ( `id` )
);

CREATE TABLE `contrexx_module_gallery_language` (
`gallery_id` INT UNSIGNED NOT NULL ,
`lang_id` INT UNSIGNED NOT NULL ,
`name` SET( 'name', 'desc' ) NOT NULL ,
`value` TEXT NOT NULL ,
PRIMARY KEY ( `gallery_id` , `lang_id` , `name` )
);


ALTER TABLE `contrexx_module_gallery_categories` ADD `comment` SET( '0', '1' ) DEFAULT '0' NOT NULL , ADD `voting` SET( '0', '1' ) DEFAULT '0' NOT NULL ;
ALTER TABLE `contrexx_module_gallery_categories` DROP `name`, DROP `description`;
ALTER TABLE `contrexx_module_gallery_settings` DROP `description`;