/*Updatescript erstellt */
ALTER TABLE `contrexx_module_banner_relations` CHANGE `type` `type` SET( 'content', 'news', 'teaser', 'level' ) NOT NULL DEFAULT 'content'

INSERT INTO `contrexx_module_banner_settings` ( `name` , `value` )
VALUES (
'level_banner', '1'
);

ALTER TABLE `contrexx_module_banner_system` ADD `views` INT( 100 ) NOT NULL ,
ADD `clicks` INT( 100 ) NOT NULL ;