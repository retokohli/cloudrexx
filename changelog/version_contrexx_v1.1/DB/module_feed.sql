DROP TABLE `contrexx_module_feed_newsml_content_item`;


ALTER TABLE `contrexx_module_feed_newsml_categories` ADD `showPics` ENUM( '0', '1' ) NOT NULL DEFAULT '1' AFTER `limit` ;