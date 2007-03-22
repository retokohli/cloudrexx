
DROP TABLE `astalavista_countries`;
ALTER TABLE `astalavista_navigation` RENAME `astalavista_content_navigation`;
ALTER TABLE `astalavista_content_navigation` ADD `target` VARCHAR( 10 ) NOT NULL AFTER `catname`;
ALTER TABLE `astalavista_content` DROP `redirect_target`;

INSERT INTO `astalavista_stats_config` VALUES (10, 'count_search_terms', '', 1);
INSERT INTO `astalavista_stats_config` VALUES (11, 'count_screen_resolution', '', 1);
INSERT INTO `astalavista_stats_config` VALUES (12, 'count_colour_depth', '', 1);
INSERT INTO `astalavista_stats_config` VALUES (13, 'count_javascript', '', 1);

DROP TABLE IF EXISTS `astalavista_stats_search`;
CREATE TABLE `astalavista_stats_search` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `count` int(10) unsigned NOT NULL default '0',
  `sid` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `astalavista_stats_screenresolution`;
CREATE TABLE `astalavista_stats_screenresolution` (
`id` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT ,
`resolution` VARCHAR( 11 ) NOT NULL ,
`count` INT( 10 ) UNSIGNED NOT NULL ,
PRIMARY KEY ( `id` )
);

DROP TABLE IF EXISTS `astalavista_stats_colourdepth`;
CREATE TABLE `astalavista_stats_colourdepth` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
`depth` TINYINT UNSIGNED NOT NULL ,
`count` INT( 10 ) UNSIGNED NOT NULL ,
PRIMARY KEY ( `id` )
);

DROP TABLE IF EXISTS `astalavista_stats_javascript`;
CREATE TABLE `astalavista_stats_javascript` (
`id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT ,
`support` ENUM( '0', '1' ) DEFAULT '0' ,
`count` INT UNSIGNED NOT NULL ,
PRIMARY KEY ( `id` )
);

INSERT INTO `astalavista_stats_javascript` VALUES (1, '0', 0);
INSERT INTO `astalavista_stats_javascript` VALUES (2, '1', 0);



DROP TABLE IF EXISTS `astalavista_stats_referer`;
CREATE TABLE `astalavista_stats_referer` (
`id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT ,
`uri` VARCHAR( 255 ) NOT NULL ,
`timestamp` INT( 11 ) UNSIGNED NOT NULL ,
`count` MEDIUMINT UNSIGNED NOT NULL ,
`sid` VARCHAR( 32 ) NOT NULL ,
PRIMARY KEY ( `id` )
);

INSERT INTO `astalavista_stats_config` VALUES ('14', 'count_referer', '', '1');
ALTER TABLE `astalavista_stats_spiders_summary` CHANGE `name` `name` VARCHAR( 255 ) NOT NULL;
ALTER TABLE `astalavista_stats_requests` CHANGE `page` `page` VARCHAR( 255 ) DEFAULT NULL;
ALTER TABLE `astalavista_stats_requests` CHANGE `title` `pageId` SMALLINT( 6 ) UNSIGNED NOT NULL;
ALTER TABLE `astalavista_stats_spiders` CHANGE `title` `pageId` MEDIUMINT( 6 ) UNSIGNED NOT NULL; 