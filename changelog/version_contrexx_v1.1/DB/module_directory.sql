/*//create new table 'contrexx_module_directory_levels' for levels
CREATE TABLE `contrexx_module_directory_levels` (
`id` INT( 7 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`parentid` INT( 7 ) NOT NULL ,
`name` VARCHAR( 100 ) NOT NULL ,
`description` VARCHAR( 255 ) NOT NULL ,
`metadesc` VARCHAR( 100 ) NOT NULL ,
`metakeys` VARCHAR( 100 ) NOT NULL ,
`showlevels` INT( 1 ) NOT NULL ,
`showcategories` INT( 1 ) NOT NULL ,
`showentries` INT( 1 ) NOT NULL
) TYPE = MYISAM ;

ALTER TABLE `contrexx_module_directory_levels` ADD `displayorder` INT( 7 ) NOT NULL AFTER `metakey` ;
ALTER TABLE `contrexx_module_directory_levels` ADD `status` INT( 1 ) NOT NULL ;
ALTER TABLE `contrexx_module_directory_levels` DROP `showentries`; */


/*//del catid
ALTER TABLE `contrexx_module_directory_dir` DROP `catid` ;*/


/*//insert new cell 'showentries' for categories
ALTER TABLE `contrexx_module_directory_categories` ADD `showentries` INT( 1 ) NOT NULL AFTER `metakeys` ;*/


/*//inser new cell into settings
INSERT INTO `contrexx_module_directory_settings` ( `setid` , `setname` , `setvalue` , `setdescription` , `settyp` )
VALUES (
'1', 'levels', '1', 'Ebenen aktivieren', '2'
);*/


/*//creat new relations table
CREATE TABLE `contrexx_module_directory_rel_dir_level` (
`dir_id` INT( 7 ) NOT NULL ,
`level_id` INT( 7 ) NOT NULL ,
) TYPE = MYISAM ;

ALTER TABLE `contrexx_module_directory_rel_dir_level` ADD PRIMARY KEY ( `dir_id` , `level_id` ) ;

CREATE TABLE `contrexx_module_directory_rel_dir_cat` (
`dir_id` INT( 7 ) NOT NULL ,
`cat_id` INT( 7 ) NOT NULL
) TYPE = MYISAM ;

ALTER TABLE `contrexx_module_directory_rel_dir_cat` ADD PRIMARY KEY ( `dir_id` , `cat_id` ) ;*/


/*//set indexes
ALTER TABLE `contrexx_module_directory_dir` ADD INDEX ( `title` ) ;
ALTER TABLE `contrexx_module_directory_dir` ADD INDEX ( `status` );
ALTER TABLE `contrexx_module_directory_dir` ADD INDEX ( `date` ) ;
ALTER TABLE `contrexx_module_directory_dir` ADD INDEX ( `typ` ) ; */

/*ALTER TABLE `contrexx_module_directory_categories` ADD INDEX ( `parentid` ) ;
ALTER TABLE `contrexx_module_directory_categories` ADD INDEX ( `displayorder`) ;
ALTER TABLE `contrexx_module_directory_categories` ADD INDEX ( `name` ) ;
ALTER TABLE `contrexx_module_directory_categories` ADD INDEX ( `status` ) ;*/


/*ALTER TABLE `contrexx_module_directory_levels` ADD INDEX ( `parentid` ) ;
ALTER TABLE `contrexx_module_directory_levels` ADD INDEX ( `displayorder`) ;
ALTER TABLE `contrexx_module_directory_levels` ADD INDEX ( `name` ) ;
ALTER TABLE `contrexx_module_directory_levels` ADD INDEX ( `status` );*/


/*//new admin mail
INSERT INTO `contrexx_module_directory_mail` VALUES (2, '[[URL]] - Neuer Eintrag', 'Hallo Admin\r\n\r\nAuf [[URL]] wurde ein Eintrag aufgeschaltet oder editiert. Bitte überprüfen Sie diesen und Bestätigen Sie ihn falls nötig.\r\n\r\nEintrag Details:\r\n\r\nTitel: [[TITLE]]\r\nBenutzername: [[USERNAME]]\r\nVorname: [[FIRSTNAME]]\r\nNachname:[[LASTNAME]]\r\nLink: [[LINK]]\r\n\r\nAutomatisch generierte Nachricht\r\n[[DATE]]');
*/

/*//new cell in vote
ALTER TABLE `contrexx_module_directory_vote` ADD `count` INT( 7 ) NOT NULL AFTER `vote` ;*/


/*neu felder
INSERT INTO `contrexx_module_directory_inputfields` (`id`, `typ`, `name`, `title`, `active`, `active_backend`, `is_required`, `read_only`, `sort`, `exp_search`, `is_search`) VALUES (37, 5, 'spez_field_1', '', 0, 0, 0, 0, 0, 1, 0);
INSERT INTO `contrexx_module_directory_inputfields` (`id`, `typ`, `name`, `title`, `active`, `active_backend`, `is_required`, `read_only`, `sort`, `exp_search`, `is_search`) VALUES (38, 5, 'spez_field_2', '', 0, 0, 0, 0, 0, 1, 0);
INSERT INTO `contrexx_module_directory_inputfields` (`id`, `typ`, `name`, `title`, `active`, `active_backend`, `is_required`, `read_only`, `sort`, `exp_search`, `is_search`) VALUES (39, 5, 'spez_field_3', '', 0, 0, 0, 0, 0, 1, 0);
INSERT INTO `contrexx_module_directory_inputfields` (`id`, `typ`, `name`, `title`, `active`, `active_backend`, `is_required`, `read_only`, `sort`, `exp_search`, `is_search`) VALUES (40, 5, 'spez_field_4', '', 0, 0, 0, 0, 0, 1, 0);
INSERT INTO `contrexx_module_directory_inputfields` (`id`, `typ`, `name`, `title`, `active`, `active_backend`, `is_required`, `read_only`, `sort`, `exp_search`, `is_search`) VALUES (41, 5, 'spez_field_5', '', 0, 0, 0, 0, 0, 1, 0);
INSERT INTO `contrexx_module_directory_inputfields` (`id`, `typ`, `name`, `title`, `active`, `active_backend`, `is_required`, `read_only`, `sort`, `exp_search`, `is_search`) VALUES ('', 6, 'spez_field_6', '', 0, 0, 0, 0, 0, 1, 0);
INSERT INTO `contrexx_module_directory_inputfields` (`id`, `typ`, `name`, `title`, `active`, `active_backend`, `is_required`, `read_only`, `sort`, `exp_search`, `is_search`) VALUES ('', 6, 'spez_field_7', '', 0, 0, 0, 0, 0, 1, 0);
INSERT INTO `contrexx_module_directory_inputfields` (`id`, `typ`, `name`, `title`, `active`, `active_backend`, `is_required`, `read_only`, `sort`, `exp_search`, `is_search`) VALUES ('', 6, 'spez_field_8', '', 0, 0, 0, 0, 0, 1, 0);
INSERT INTO `contrexx_module_directory_inputfields` (`id`, `typ`, `name`, `title`, `active`, `active_backend`, `is_required`, `read_only`, `sort`, `exp_search`, `is_search`) VALUES ('', 6, 'spez_field_9', '', 0, 0, 0, 0, 0, 1, 0);
INSERT INTO `contrexx_module_directory_inputfields` (`id`, `typ`, `name`, `title`, `active`, `active_backend`, `is_required`, `read_only`, `sort`, `exp_search`, `is_search`) VALUES ('', 6, 'spez_field_10', '', 0, 0, 0, 0, 0, 1, 0);
INSERT INTO `contrexx_module_directory_inputfields` (`id`, `typ`, `name`, `title`, `active`, `active_backend`, `is_required`, `read_only`, `sort`, `exp_search`, `is_search`) VALUES ('', 7, 'spez_field_11', '', 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `contrexx_module_directory_inputfields` (`id`, `typ`, `name`, `title`, `active`, `active_backend`, `is_required`, `read_only`, `sort`, `exp_search`, `is_search`) VALUES ('', 7, 'spez_field_12', '', 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `contrexx_module_directory_inputfields` (`id`, `typ`, `name`, `title`, `active`, `active_backend`, `is_required`, `read_only`, `sort`, `exp_search`, `is_search`) VALUES ('', 7, 'spez_field_13', '', 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `contrexx_module_directory_inputfields` (`id`, `typ`, `name`, `title`, `active`, `active_backend`, `is_required`, `read_only`, `sort`, `exp_search`, `is_search`) VALUES ('', 7, 'spez_field_14', '', 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `contrexx_module_directory_inputfields` (`id`, `typ`, `name`, `title`, `active`, `active_backend`, `is_required`, `read_only`, `sort`, `exp_search`, `is_search`) VALUES ('', 7, 'spez_field_15', '', 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `contrexx_module_directory_inputfields` (`id`, `typ`, `name`, `title`, `active`, `active_backend`, `is_required`, `read_only`, `sort`, `exp_search`, `is_search`) VALUES ('', 8, 'spez_field_16', '', 0, 0, 0, 0, 0, 1, 0);
INSERT INTO `contrexx_module_directory_inputfields` (`id`, `typ`, `name`, `title`, `active`, `active_backend`, `is_required`, `read_only`, `sort`, `exp_search`, `is_search`) VALUES ('', 8, 'spez_field_17', '', 0, 0, 0, 0, 0, 1, 0);


ALTER TABLE `contrexx_module_directory_dir` ADD `spez_field_1` VARCHAR( 255 ) NOT NULL ,
ADD `spez_field_2` VARCHAR( 255 ) NOT NULL ,
ADD `spez_field_3` VARCHAR( 255 ) NOT NULL ,
ADD `spez_field_4` VARCHAR( 255 ) NOT NULL ,
ADD `spez_field_5` VARCHAR( 255 ) NOT NULL ,
ADD `spez_field_6` MEDIUMTEXT  NOT NULL ,
ADD `spez_field_7` MEDIUMTEXT  NOT NULL ,
ADD `spez_field_8` MEDIUMTEXT  NOT NULL ,
ADD `spez_field_9` MEDIUMTEXT  NOT NULL ,
ADD `spez_field_10` MEDIUMTEXT  NOT NULL ,
ADD `spez_field_11` VARCHAR( 255 ) NOT NULL ,
ADD `spez_field_12` VARCHAR( 255 ) NOT NULL ,
ADD `spez_field_13` VARCHAR( 255 ) NOT NULL ,
ADD `spez_field_14` VARCHAR( 255 ) NOT NULL ,
ADD `spez_field_15` VARCHAR( 255 ) NOT NULL ,
ADD `spez_field_16` VARCHAR( 255 ) NOT NULL ,
ADD `spez_field_17` VARCHAR( 255 ) NOT NULL ;

INSERT INTO `contrexx_module_directory_settings` ( `setid` , `setname` , `setvalue` , `setdescription` , `settyp` )VALUES (NULL , 'spez_field_16', '', 'spez_field_16', '0');
INSERT INTO `contrexx_module_directory_settings` ( `setid` , `setname` , `setvalue` , `setdescription` , `settyp` )VALUES (NULL , 'spez_field_17', '', 'spez_field_17', '0');



UPDATE `contrexx_module_directory_settings` SET `setname` = 'catDescription' WHERE `contrexx_module_directory_settings`.`setid` =13 LIMIT 1 ;

UPDATE `contrexx_module_directory_settings` SET `setname` = 'entryStatus' WHERE `contrexx_module_directory_settings`.`setid` =12 LIMIT 1 ;

UPDATE `contrexx_module_directory_settings` SET `setname` = 'showLevels' WHERE `contrexx_module_directory_settings`.`setid` =1 LIMIT 1 ;*/