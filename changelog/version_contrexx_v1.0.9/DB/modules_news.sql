ALTER TABLE `contrexx_module_news` ADD `teaser_show_link` TINYINT( 1 ) UNSIGNED DEFAULT '1' NOT NULL AFTER `teaser_text` ;

ALTER TABLE `contrexx_module_news` CHANGE `teaser_frames` `teaser_frames` TEXT NOT NULL ,
CHANGE `teaser_text` `teaser_text` VARCHAR( 255 ) NOT NULL ;

INSERT INTO `contrexx_module_news_settings` ( `name` , `value` )
VALUES (
'news_feed_image', ''
);
