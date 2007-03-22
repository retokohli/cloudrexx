ALTER TABLE `contrexx_module_block_blocks` ADD `name` VARCHAR( 255 ) NOT NULL ,
ADD `active` INT( 1 ) NOT NULL ;

ALTER TABLE `contrexx_module_block_blocks` ADD `random` INT( 1 ) DEFAULT '0' NOT NULL AFTER `name` ;

INSERT INTO `contrexx_settings` ( `setid` , `setname` , `setvalue` , `setmodule` )
VALUES (
'', 'blockRandom', '1', '7'
);