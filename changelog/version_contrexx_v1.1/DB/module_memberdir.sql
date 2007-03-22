ALTER TABLE `contrexx_module_memberdir_values` ADD `0` SMALLINT UNSIGNED NOT NULL DEFAULT '0' AFTER `pic2`;

ALTER TABLE `contrexx_module_memberdir_directories` ADD FULLTEXT `memberdir_dir` ( `name` , `description` );