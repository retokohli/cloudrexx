# NEWSLETTER
# -----------------------------------
ALTER TABLE `contrexx_newsletter_emails` RENAME `contrexx_module_newsletter_user` ;
ALTER TABLE `contrexx_module_newsletter_user` CHANGE `emailid` `id` INT( 11 ) NOT NULL AUTO_INCREMENT 
ALTER TABLE `contrexx_module_newsletter_user` CHANGE `emailadress` `code` VARCHAR( 250 ) NOT NULL 
ALTER TABLE `contrexx_module_newsletter_user` CHANGE `code` `email` VARCHAR( 255 ) NOT NULL 
ALTER TABLE `contrexx_module_newsletter_user` DROP `emailvalidate` 
ALTER TABLE `contrexx_module_newsletter_user` DROP `emaillistid` 
ALTER TABLE `contrexx_module_newsletter_user` ADD `code` VARCHAR( 255 ) NOT NULL AFTER `id` ;
ALTER TABLE `contrexx_module_newsletter_user` ADD `lastname` VARCHAR( 255 ) NOT NULL AFTER `email` ,
ADD `firstname` VARCHAR( 255 ) NOT NULL AFTER `lastname` ,
ADD `street` VARCHAR( 255 ) NOT NULL AFTER `firstname` ,
ADD `zip` VARCHAR( 255 ) NOT NULL AFTER `street` ,
ADD `city` VARCHAR( 255 ) NOT NULL AFTER `zip` ,
ADD `country` VARCHAR( 255 ) NOT NULL AFTER `city` ,
ADD `phone` VARCHAR( 255 ) NOT NULL AFTER `country` ,
ADD `birthday` VARCHAR( 100 ) NOT NULL AFTER `phone` ,
ADD `status` INT( 1 ) NOT NULL AFTER `birthday` ;
