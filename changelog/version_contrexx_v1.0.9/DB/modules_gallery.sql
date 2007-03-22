ALTER TABLE `contrexx_module_gallery_pictures` ADD `catimg` SET( '0', '1' ) DEFAULT '0' NOT NULL AFTER `status`;
INSERT INTO `contrexx_module_gallery_settings` ( `id` , `name` , `value` ) VALUES ('', 'paging', '30');

ALTER TABLE `contrexx_module_gallery_language` ADD FULLTEXT (`value`);