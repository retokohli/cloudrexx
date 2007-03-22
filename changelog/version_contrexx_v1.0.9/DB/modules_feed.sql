

# CREATE TABLE `contrexx_module_feed_newsml_content_item` (
# `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
# `publicIdentifier` TEXT NOT NULL ,
#`media_type` ENUM( 'Text', 'Graphic', 'Photo', 'Audio', 'Video', 'ComplexData' ) NOT NULL DEFAULT 'Text',
#`source` TEXT NOT NULL
#) TYPE = MYISAM ;

#ALTER TABLE `contrexx_module_feed_newsml_content_item` ADD `data` TEXT NOT NULL AFTER `media_type` ;
#ALTER TABLE `contrexx_module_feed_newsml_content_item` ADD `properties` TEXT NOT NULL AFTER `data` ;


ALTER TABLE `contrexx_module_feed_newsml_documents` ADD `is_associated` TINYINT( 1 ) UNSIGNED NOT NULL AFTER `dataContent` ;
ALTER TABLE `contrexx_module_feed_newsml_documents` ADD `media_type` ENUM( 'Text', 'Graphic', 'Photo', 'Audio', 'Video', 'ComplexData' ) NOT NULL DEFAULT 'Text' AFTER `is_associated` ;
ALTER TABLE `contrexx_module_feed_newsml_documents` ADD `properties` TEXT NOT NULL AFTER `media_type` ;
ALTER TABLE `contrexx_module_feed_newsml_documents` ADD `source` TEXT NOT NULL AFTER `media_type` ;


CREATE TABLE `contrexx_module_feed_newsml_association` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`pId_master` TEXT NOT NULL ,
`pId_slave` TEXT NOT NULL
) TYPE = MYISAM ;




CREATE TABLE `contrexx_module_feed_newsml_content_item` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
`news_item_id` INT UNSIGNED NOT NULL ,
`media_type` ENUM( 'Text', 'Graphic', 'Photo', 'Audio', 'Video', 'ComplexData' ) DEFAULT 'Text' NOT NULL ,
`is_reference` TINYINT( 1 ) DEFAULT '0' NOT NULL ,
PRIMARY KEY ( `id` )
) TYPE = MYISAM ;

CREATE TABLE `contrexx_module_feed_newsml_content_item_data` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
`content_item_id` INT UNSIGNED NOT NULL ,
`data` TEXT NOT NULL ,
PRIMARY KEY ( `id` )
) TYPE = MYISAM ;

CREATE TABLE `contrexx_module_feed_newsml_content_item_source` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
`content_item_id` INT UNSIGNED NOT NULL ,
`source` TEXT NOT NULL ,
PRIMARY KEY ( `id` )
) TYPE = MYISAM ;

CREATE TABLE `contrexx_module_feed_newsml_content_item_property` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
`content_item_id` INT UNSIGNED NOT NULL ,
`property` VARCHAR( 255 ) NOT NULL ,
`value` VARCHAR( 255 ) NOT NULL ,
PRIMARY KEY ( `id` )
) TYPE = MYISAM ;

ALTER TABLE `contrexx_module_feed_newsml_documents` ADD `newsProduct` VARCHAR( 255 ) NOT NULL AFTER `thisRevisionDate` ;