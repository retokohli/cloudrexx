CREATE TABLE `contrexx_module_block_rel_pages` (
`block_id` INT( 7 ) NOT NULL ,
`page_id` INT( 7 ) NOT NULL ,
`lang_id` INT( 7 ) NOT NULL ,
) ENGINE = MYISAM ;

ALTER TABLE `contrexx_module_block_rel_lang` ADD `all_pages` INT( 1 ) NOT NULL ;

ALTER TABLE `contrexx_module_block_blocks` ADD `global` INT( 1 ) NOT NULL AFTER `random` ;

CREATE TABLE `contrexx_module_block_settings` (
`id` INT( 7 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`name` VARCHAR( 100 ) NOT NULL ,
`value` VARCHAR( 100 ) NOT NULL
) ENGINE = MYISAM ;