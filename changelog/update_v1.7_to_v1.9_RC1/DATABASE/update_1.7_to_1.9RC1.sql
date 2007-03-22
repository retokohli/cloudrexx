DROP TABLE IF EXISTS `astalavista_user_level`;

ALTER TABLE `astalavista_languages` CHANGE `active` `frontend` TINYINT( 1 ) UNSIGNED DEFAULT '0' NOT NULL;
ALTER TABLE `astalavista_languages` CHANGE `adminstatus` `backend` TINYINT( 1 ) UNSIGNED DEFAULT '0' NOT NULL;
ALTER TABLE `astalavista_languages` CHANGE `defaultstatus` `is_default` SET( 'true', 'false' ) DEFAULT 'false' NOT NULL;

ALTER TABLE `astalavista_content` ADD `redirect_target` VARCHAR( 10 ) NOT NULL AFTER `redirect`;

DROP TABLE IF EXISTS astalavista_language_variable_content;
DROP TABLE IF EXISTS astalavista_language_variable_names;

ALTER TABLE `astalavista_navigation` CHANGE `public_groups` `frontend_groups` VARCHAR( 250 ) NOT NULL;


ALTER TABLE `astalavista_themes` ADD `buildin_style` TEXT AFTER `style` ;
DELETE FROM `astalavista_modules` WHERE `id`='7';


ALTER TABLE `astalavista_users` CHANGE `lang` `langId` SMALLINT( 2 ) NOT NULL;


DROP TABLE IF EXISTS `astalavista_skins`;
CREATE TABLE `astalavista_skins` (
  `id` tinyint(2) unsigned NOT NULL auto_increment,
  `themesname` varchar(50) NOT NULL default '',
  `foldername` varchar(50) NOT NULL default '',
  `expert` int(1) NOT NULL default '1',
  UNIQUE KEY `id` (`id`),
  KEY `id_2` (`id`)
) TYPE=MyISAM;

DROP TABLE `astalavista_countries`;
ALTER TABLE `astalavista_navigation` RENAME `astalavista_content_navigation`;
ALTER TABLE `astalavista_content_navigation` ADD `target` VARCHAR( 10 ) NOT NULL AFTER `catname`;
ALTER TABLE `astalavista_content` DROP `redirect_target`;

ALTER TABLE `astalavista_modules` CHANGE `add_on` `is_required` TINYINT( 1 ) DEFAULT '0' NOT NULL;
DELETE FROM `astalavista_settings` WHERE `setid` = '8' LIMIT 1;
DELETE FROM `astalavista_settings` WHERE `setid` = '9' LIMIT 1;

DROP TABLE IF EXISTS `astalavista_yearstats`; 