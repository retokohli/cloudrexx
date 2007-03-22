// SQL UPDATE v1.6 -> current
ALTER TABLE `astalavista_navigation` DROP `group_perms`; 
ALTER TABLE `astalavista_navigation` ADD `public_groups` VARCHAR( 250 ) NOT NULL AFTER `protected` , ADD `backend_groups` VARCHAR( 250 ) NOT NULL AFTER `public_groups` ;
ALTER TABLE `astalavista_modules` ADD `is_core` TINYINT DEFAULT '0' NOT NULL AFTER `add_on` ;
ALTER TABLE `astalavista_users` ADD `is_admin` TINYINT DEFAULT '0' NOT NULL AFTER `levelid` ;
INSERT INTO `astalavista_settings` ( `setid` , `setname` , `setvalue` , `setmodule` ) VALUES ('', 'sessionLifeTime', '3600', '1');
ALTER TABLE `astalavista_newsletter_archiv` CHANGE `archivbody` `archivbody` LONGTEXT NOT NULL;
DROP TABLE `astalavista_groups`;
INSERT INTO astalavista_modules VALUES (22, 'feed', 'txtFeedModuleDescription', 'y', 1, 0);


DROP TABLE IF EXISTS astalavista_user_groups;
CREATE TABLE astalavista_user_groups (
  group_id smallint(6) NOT NULL auto_increment,
  group_name varchar(100) NOT NULL default '',
  group_description varchar(255) NOT NULL default '',
  is_active tinyint(4) NOT NULL default '1',
  type enum('public','backend') NOT NULL default 'public',
  PRIMARY KEY  (group_id)
) TYPE=MyISAM;

INSERT INTO astalavista_user_groups VALUES (1, 'Standard Administrator', '-', 1, 'backend');
INSERT INTO astalavista_user_groups VALUES (2, 'Developer', 'Update and develop the system', 1, 'backend');
INSERT INTO astalavista_user_groups VALUES (3, 'News Editor', 'Manages Guestbook, News and Document System', 1, 'backend');
INSERT INTO astalavista_user_groups VALUES (4, 'Content Editor', 'Manages the content', 1, 'backend');

