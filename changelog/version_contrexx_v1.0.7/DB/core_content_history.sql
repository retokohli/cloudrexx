INSERT INTO `contrexx_settings` VALUES (43, 'contentHistoryStatus', 'on', 1);
INSERT INTO `contrexx_backend_areas` ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` ) VALUES ('75', '1', 'navigation', 'TXT_CONTENT_HISTORY', '1', 'index.php?cmd=workflow', '_self', '1', '3', '75');
INSERT INTO `contrexx_backend_areas` ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` ) VALUES ('77', '75', 'function', 'TXT_DELETED_RESTORE', '1', '', '_self', '0', '1', '77');
INSERT INTO `contrexx_backend_areas` ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` ) VALUES ('78', '75', 'function', 'TXT_WORKFLOW_VALIDATE', '1', '', '_self', '0', '1', '78');
INSERT INTO `contrexx_backend_areas` ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` ) VALUES ('79', '6', 'function', 'TXT_ACTIVATE_HISTORY', '1', '', '_self', '0', '6', '79');
INSERT INTO `contrexx_backend_areas` ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` ) VALUES ('80', '6', 'function', 'TXT_HISTORY_DELETE_ENTRY', '1', '', '_self', '0', '7', '80');
UPDATE `contrexx_backend_areas` SET `order_id` = '4' WHERE `area_id` =7 LIMIT 1 ;
UPDATE `contrexx_backend_areas` SET `order_id` = '4' WHERE `area_id` =32 LIMIT 1 ;

CREATE TABLE `contrexx_content_history` (
  `id` smallint(8) unsigned NOT NULL default '0',
  `page_id` smallint(7) unsigned NOT NULL default '0',
  `content` mediumtext NOT NULL,
  `title` varchar(250) NOT NULL default '',
  `metatitle` varchar(250) NOT NULL default '',
  `metadesc` varchar(250) NOT NULL default '',
  `metakeys` varchar(250) NOT NULL default '',
  `metarobots` varchar(7) NOT NULL default 'index',
  `css_name` varchar(50) NOT NULL default '',
  `redirect` varchar(255) NOT NULL default '',
  `expertmode` set('y','n') NOT NULL default 'n',
  PRIMARY KEY  (`id`),
  FULLTEXT KEY `fulltextindex` (`title`,`content`)
);
ALTER TABLE `contrexx_content_history` ADD INDEX ( `page_id` );

CREATE TABLE `contrexx_content_navigation_history` (
  `id` smallint(7) unsigned NOT NULL auto_increment,
  `is_active` set ('0','1') NOT NULL default '0',
  `catid` smallint(6) unsigned NOT NULL,
  `parcat` smallint(6) unsigned NOT NULL default '0',
  `catname` varchar(100) NOT NULL default '',
  `target` varchar(10) NOT NULL default '',
  `displayorder` smallint(6) unsigned NOT NULL default '1000',
  `displaystatus` set('on','off') NOT NULL default 'on',
  `activestatus` set('0','1') NOT NULL default '1',
  `cachingstatus` set('0','1') NOT NULL default '1',
  `username` varchar(40) NOT NULL default '',
  `changelog` int(14) default NULL,
  `cmd` varchar(50) NOT NULL default '',
  `lang` tinyint(2) unsigned NOT NULL default '1',
  `module` tinyint(2) unsigned NOT NULL default '0',
  `startdate` date NOT NULL default '0000-00-00',
  `enddate` date NOT NULL default '0000-00-00',
  `protected` tinyint(4) NOT NULL default '0',
  `frontend_access_id` int(11) unsigned NOT NULL default '0',
  `backend_access_id` int(11) unsigned NOT NULL default '0',
  `themes_id` int(4) NOT NULL default '0',
  PRIMARY KEY  (`id`)
);
ALTER TABLE `contrexx_content_navigation_history` ADD INDEX ( `catid` );

CREATE TABLE `contrexx_content_logfile` (
`id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
`action` SET( 'new', 'update', 'delete' ) DEFAULT 'new' NOT NULL ,
`history_id` INT( 10 ) UNSIGNED NOT NULL ,
`is_validated` SET('0','1') NOT NULL default '0',
PRIMARY KEY ( `id` )
);

# Changes on content manager
# --------------------------------------
ALTER TABLE `contrexx_content_navigation` ADD `activestatus` SET( '0', '1' ) DEFAULT '1' NOT NULL AFTER `displaystatus`;
ALTER TABLE `contrexx_content_navigation` ADD `cachingstatus` SET('0','1') DEFAULT '1' NOT NULL AFTER `activestatus`;
ALTER TABLE `contrexx_content_navigation` ADD `is_validated` SET( '0', '1' ) DEFAULT '1' NOT NULL AFTER `catid`;
