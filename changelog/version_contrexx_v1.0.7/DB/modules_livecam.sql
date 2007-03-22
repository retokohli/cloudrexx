INSERT INTO `contrexx_backend_areas` ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` )
VALUES ('82', '2', 'navigation', 'TXT_LIVECAM', '1', 'index.php?cmd=livecam', '_self', '30', '0', '82');

INSERT INTO `contrexx_modules` ( `id` , `name` , `description_variable` , `status` , `is_required` , `is_core` )VALUES ('30', 'livecam', 'TXT_LIVECAM_MODULE_DESCRIPTION', 'y', '0', '0');

CREATE TABLE `contrexx_module_livecam_settings` (
`setid` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
`setname` TEXT NOT NULL ,
`setvalue` TEXT NOT NULL ,
PRIMARY KEY ( `setid` ) 
);

ALTER TABLE `contrexx_module_livecam_settings` CHANGE `setname` `setname` VARCHAR( 255 ) NOT NULL;

INSERT INTO `contrexx_module_livecam_settings` ( `setid` , `setname` , `setvalue` ) 
VALUES (
'', 'currentImageUrl', 'http://heimenschwand.ch/webcam/current.jpg'
), (
'', 'archivePath', '/webcam'
);

INSERT INTO `contrexx_module_livecam_settings` ( `setid` , `setname` , `setvalue` ) 
VALUES (
'', 'thumbnailPath', '/webcam/thumbs'
);