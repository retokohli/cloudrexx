ALTER TABLE `contrexx_module_contact_form` ADD `subject` VARCHAR( 255 ) NOT NULL AFTER `mails` ;

ALTER TABLE `contrexx_module_contact_form` ADD `text` TEXT NOT NULL ;

ALTER TABLE `contrexx_module_contact_form` ADD `feedback` TEXT NOT NULL ;

ALTER TABLE `contrexx_module_contact_form_field` ADD `is_required` SET( '0', '1' ) DEFAULT '0' NOT NULL AFTER `attributes` ;

ALTER TABLE `contrexx_module_contact_form_field` ADD `check_type` INT( 3 ) NOT NULL AFTER `is_required` ;

ALTER TABLE `contrexx_module_contact_form_field` CHANGE `check_type` `check_type` INT( 3 ) DEFAULT '1' NOT NULL;

INSERT INTO `contrexx_module_contact_settings` ( `setid` , `setname` , `setvalue` , `status` )VALUES ('', 'spamProtectionWordList', 'poker,casino,viagra,sex,porn,pussy,fucking', '1');

ALTER TABLE `contrexx_module_contact_form_field` CHANGE `type` `type` ENUM( 'text', 'label', 'checkbox', 'checkboxGroup', 'file', 'hidden', 'password', 'radio', 'select', 'textarea' ) DEFAULT 'text' NOT NULL

ALTER TABLE `contrexx_module_contact_form` ADD `langId` TINYINT( 2 ) UNSIGNED DEFAULT '1' NOT NULL AFTER `feedback` ;

INSERT INTO `contrexx_module_contact_settings` ( `setid` , `setname` , `setvalue` , `status` )
VALUES (
NULL , 'fieldMetaDate', '0', '1'
), (
NULL , 'fieldMetaHost', '0', '1'
), (
NULL , 'fieldMetaLang', '0', '1'
), (
NULL , 'fieldMetaIP', '0', '1'
);