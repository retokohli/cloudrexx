/* Neuen Bereich für DBM anlegen */
INSERT INTO `contrexx_backend_areas` ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` )
VALUES ( NULL , '3', 'navigation', 'TXT_DATABASE_MANAGER' , '1', 'index.php?cmd=dbm', '_self', '1', '2', '116');

/* Zugangsberechtigungen setzen */
INSERT INTO `contrexx_backend_areas` ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` )
VALUES (NULL , '111', 'function', 'TXT_DBM_STATUS_TITLE', '1', 'index.php?cmd=dbm&act=status', '_self', '1', '4', '117');

INSERT INTO `contrexx_backend_areas` ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` )
VALUES (NULL , '111', 'function', 'TXT_DBM_MAINTENANCE_TITLE', '1', 'index.php?cmd=dbm&act=maintenance', '_self', '1', '1', '118');

INSERT INTO `contrexx_backend_areas` ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` )
VALUES (NULL , '111', 'function', 'TXT_DBM_BACKUP_TITLE', '1', 'index.php?cmd=dbm&act=ie', '_self', '1', '5', '119');

INSERT INTO `contrexx_backend_areas` ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` )
VALUES (NULL , '111', 'function', 'TXT_DBM_SQL_TITLE', '1', 'index.php?cmd=dbm&act=sql', '_self', '1', '3', '120');

INSERT INTO `contrexx_backend_areas` ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` )
VALUES (NULL , '111', 'function', 'TXT_DBM_SHOW_TABLE_TITLE', '1', 'index.php?cmd=dbm&act=showTable', '_self', '1', '2', '121');

/* Bestehende Tabelle erweitern */
ALTER TABLE `contrexx_backups` ADD `version` VARCHAR( 20 ) NOT NULL AFTER `date`;
ALTER TABLE `contrexx_backups` ADD `edition` VARCHAR( 30 ) NOT NULL AFTER `version`;
ALTER TABLE `contrexx_backups` ADD `type` ENUM( 'sql', 'csv' ) NOT NULL DEFAULT 'sql' AFTER `edition` ;

/* Alte Datensätze aktualisieren */
UPDATE `contrexx_backups` SET  version='< 1.1.0', edition='???';

/* Alte Zugangsberechtigungen löschen */
DELETE FROM `contrexx_backend_areas` WHERE `area_id` = 20 LIMIT 1;
DELETE FROM `contrexx_backend_areas` WHERE `area_id` = 41 LIMIT 1;
DELETE FROM `contrexx_backend_areas` WHERE `area_id` = 42 LIMIT 1;
DELETE FROM `contrexx_backend_areas` WHERE `area_id` = 43 LIMIT 1;
DELETE FROM `contrexx_backend_areas` WHERE `area_id` = 44 LIMIT 1;
DELETE FROM `contrexx_backend_areas` WHERE `area_id` = 45 LIMIT 1;
DELETE FROM `contrexx_backend_areas` WHERE `area_id` = 58 LIMIT 1;

DELETE FROM `contrexx_access_group_static_ids` WHERE access_id = 20;
DELETE FROM `contrexx_access_group_static_ids` WHERE access_id = 41;
DELETE FROM `contrexx_access_group_static_ids` WHERE access_id = 42;
DELETE FROM `contrexx_access_group_static_ids` WHERE access_id = 43;
DELETE FROM `contrexx_access_group_static_ids` WHERE access_id = 44;
DELETE FROM `contrexx_access_group_static_ids` WHERE access_id = 45;
