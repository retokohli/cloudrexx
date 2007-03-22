ALTER TABLE `contrexx_module_newsletter_user` ADD `title` VARCHAR( 10 ) NOT NULL AFTER `email` ;
ALTER TABLE `contrexx_module_newsletter_user` CHANGE `title` `title` ENUM( 'm', 'f' ) NULL;
ALTER TABLE `contrexx_module_newsletter_category` DROP INDEX `id`;
ALTER TABLE `contrexx_module_newsletter_category` ADD INDEX ( `name` );
ALTER TABLE `contrexx_module_newsletter_user` ADD UNIQUE ( `email` );
CREATE TABLE `contrexx_module_newsletter_confirm_mail` (`id` INT( 1 ) NOT NULL AUTO_INCREMENT ,`title` VARCHAR( 255 ) NOT NULL ,`content` LONGTEXT NOT NULL ,PRIMARY KEY ( `id` ));
ALTER TABLE `contrexx_module_newsletter_user` ADD `company` VARCHAR( 120 ) NULL AFTER `firstname` ;

ALTER TABLE `contrexx_module_newsletter_user` CHANGE `company` `company` VARCHAR( 255 )NOT NULL DEFAULT '';
