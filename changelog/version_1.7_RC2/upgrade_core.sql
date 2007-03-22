// sql modifications from v1.7 RC2

ALTER TABLE `astalavista_module_repository` ADD `username` VARCHAR( 250 ) NOT NULL AFTER `displaystatus` ;
ALTER TABLE `astalavista_languages` CHANGE `active` `frontend` TINYINT( 1 ) UNSIGNED DEFAULT '0' NOT NULL;
ALTER TABLE `astalavista_languages` CHANGE `adminstatus` `backend` TINYINT( 1 ) UNSIGNED DEFAULT '0' NOT NULL;
ALTER TABLE `astalavista_languages` CHANGE `defaultstatus` `is_default` SET( 'true', 'false' ) DEFAULT 'false' NOT NULL;

ALTER TABLE `astalavista_content` ADD `redirect_target` VARCHAR( 10 ) NOT NULL AFTER `redirect`;

