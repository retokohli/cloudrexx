ALTER TABLE `astalavista_modules` CHANGE `add_on` `is_required` TINYINT( 1 ) DEFAULT '0' NOT NULL;
INSERT INTO `astalavista_backend_areas` VALUES (54, 4, 'navigation', 'TXT_NETWORK_TOOLS', 1, 'index.php?cmd=nettools', '_self', 0, 0);
DELETE FROM `astalavista_settings` WHERE `setid` = '8' LIMIT 1;
DELETE FROM `astalavista_settings` WHERE `setid` = '9' LIMIT 1;

INSERT INTO `astalavista_stats_config` (`name` , `value` , `status` ) VALUES ('count_hostname', '', '1');
INSERT INTO `astalavista_stats_config` (`name` , `value` , `status` ) VALUES ('count_country', '', '1');

DROP TABLE IF EXISTS `astalavista_stats_hostname`;
CREATE TABLE `astalavista_stats_hostname` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
`hostname` VARCHAR( 255 ) NOT NULL ,
`count` INT UNSIGNED NOT NULL ,
PRIMARY KEY ( `id` )
);

DROP TABLE IF EXISTS `astalavista_stats_country`;
CREATE TABLE `astalavista_stats_country` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
`country` VARCHAR( 100 ) NOT NULL ,
`count` INT UNSIGNED NOT NULL ,
PRIMARY KEY ( `id` )
);

DROP TABLE IF EXISTS `astalavista_yearstats`; 