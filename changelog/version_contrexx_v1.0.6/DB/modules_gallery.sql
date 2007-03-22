# GALLERY
# -----------------------------------

CREATE TABLE `contrexx_module_gallery_language_pics` (
`picture_id` INT UNSIGNED NOT NULL ,
`lang_id` INT UNSIGNED NOT NULL ,
`name` VARCHAR( 255 ) NOT NULL ,
`desc` VARCHAR(255) NOT NULL,
PRIMARY KEY ( `picture_id` , `lang_id` )
);

ALTER TABLE `contrexx_module_gallery_pictures` DROP `name`;
ALTER TABLE `contrexx_module_gallery_pictures` DROP `linkname`;