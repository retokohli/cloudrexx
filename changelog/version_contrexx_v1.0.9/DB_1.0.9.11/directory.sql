/*ALTER TABLE `contrexx_module_directory_levels` ADD `onlyentries` INT( 1 ) NOT NULL AFTER `showcategories` ;

INSERT INTO `contrexx_module_directory_settings` ( `setid` , `setname` , `setvalue` , `setdescription` , `settyp` )
VALUES (
NULL , 'thumbSize', '120', 'Thumbnail Grösse (in Pixel)', '1'
);

INSERT INTO `contrexx_module_directory_settings` ( `setid` , `setname` , `setvalue` , `setdescription` , `settyp` )
VALUES (
NULL , 'sortOrder', '', 'Sortierreihenfolge', '1'
);

ALTER TABLE `contrexx_module_directory_dir` CHANGE `spez_field_16` `spez_field_21` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
CHANGE `spez_field_17` `spez_field_22` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL 

UPDATE `contrexx_module_directory_settings` SET `setname` = 'spez_field_21',
`setvalue` = '',
`setdescription` = 'spez_field_21' WHERE `contrexx_module_directory_settings`.`setid` =32 LIMIT 1 ;

UPDATE `contrexx_module_directory_settings` SET `setvalue` = '',
`setdescription` = 'spez_field_22' WHERE `contrexx_module_directory_settings`.`setid` =33 LIMIT 1 ;

INSERT INTO `contrexx_module_directory_inputfields` ( `id` , `typ` , `name` , `title` , `active` , `active_backend` , `is_required` , `read_only` , `sort` , `exp_search` , `is_search` )
VALUES (
NULL , '7', 'spez_field_16', '', '0', '0', '0', '0', '0', '0', '0'
);

INSERT INTO `contrexx_module_directory_inputfields` ( `id` , `typ` , `name` , `title` , `active` , `active_backend` , `is_required` , `read_only` , `sort` , `exp_search` , `is_search` )
VALUES (
NULL , '7', 'spez_field_17', '', '0', '0', '0', '0', '0', '0', '0'
);

INSERT INTO `contrexx_module_directory_inputfields` ( `id` , `typ` , `name` , `title` , `active` , `active_backend` , `is_required` , `read_only` , `sort` , `exp_search` , `is_search` )
VALUES (
NULL , '7', 'spez_field_18', '', '0', '0', '0', '0', '0', '0', '0'
);
INSERT INTO `contrexx_module_directory_inputfields` ( `id` , `typ` , `name` , `title` , `active` , `active_backend` , `is_required` , `read_only` , `sort` , `exp_search` , `is_search` )
VALUES (
NULL , '7', 'spez_field_19', '', '0', '0', '0', '0', '0', '0', '0'
);
INSERT INTO `contrexx_module_directory_inputfields` ( `id` , `typ` , `name` , `title` , `active` , `active_backend` , `is_required` , `read_only` , `sort` , `exp_search` , `is_search` )
VALUES (
NULL , '7', 'spez_field_20', '', '0', '0', '0', '0', '0', '0', '0'
);



ALTER TABLE `contrexx_module_directory_dir` ADD `spez_field_16` VARCHAR( 255 ) NOT NULL ,
ADD `spez_field_17` VARCHAR( 255 ) NOT NULL ,
ADD `spez_field_18` VARCHAR( 255 ) NOT NULL ,
ADD `spez_field_19` VARCHAR( 255 ) NOT NULL ,
ADD `spez_field_20` VARCHAR( 255 ) NOT NULL ;



UPDATE `contrexx_module_directory_settings` SET `setname` = 'spez_field_21' WHERE `contrexx_module_directory_settings`.`setid` =32 LIMIT 1 ;

UPDATE `contrexx_module_directory_settings` SET `setname` = 'spez_field_22' WHERE `contrexx_module_directory_settings`.`setid` =33 LIMIT 1 ;

UPDATE `contrexx_module_directory_inputfields` SET `name` = 'spez_field_22' WHERE `contrexx_module_directory_inputfields`.`id` =53 LIMIT 1 ;

UPDATE `contrexx_module_directory_inputfields` SET `name` = 'spez_field_21' WHERE `contrexx_module_directory_inputfields`.`id` =52 LIMIT 1 ;

INSERT INTO `contrexx_module_directory_settings` ( `setid` , `setname` , `setvalue` , `setdescription` , `settyp` )
VALUES (
NULL , 'spez_field_23', '', 'spez_field_23', '0'
);

INSERT INTO `contrexx_module_directory_settings` ( `setid` , `setname` , `setvalue` , `setdescription` , `settyp` )
VALUES (
NULL , 'spez_field_24', '', 'spez_field_24', '0'
);

INSERT INTO `contrexx_module_directory_inputfields` ( `id` , `typ` , `name` , `title` , `active` , `active_backend` , `is_required` , `read_only` , `sort` , `exp_search` , `is_search` )
VALUES (
NULL , '9', 'spez_field_23', '', '0', '0', '0', '0', '0', '1', '0'
);

INSERT INTO `contrexx_module_directory_inputfields` ( `id` , `typ` , `name` , `title` , `active` , `active_backend` , `is_required` , `read_only` , `sort` , `exp_search` , `is_search` )
VALUES (
NULL , '9', 'spez_field_24', '', '0', '0', '0', '0', '0', '1', '0'
);


ALTER TABLE `contrexx_module_directory_dir` ADD `spez_field_23` VARCHAR( 255 ) NOT NULL ,
ADD `spez_field_24` VARCHAR( 255 ) NOT NULL ;

INSERT INTO `contrexx_module_directory_inputfields` ( `id` , `typ` , `name` , `title` , `active` , `active_backend` , `is_required` , `read_only` , `sort` , `exp_search` , `is_search` )
VALUES (
NULL , '10', 'spez_field_25', '', '0', '0', '0', '0', '0', '0', '0'
);

INSERT INTO `contrexx_module_directory_inputfields` ( `id` , `typ` , `name` , `title` , `active` , `active_backend` , `is_required` , `read_only` , `sort` , `exp_search` , `is_search` )
VALUES (
NULL , '10', 'spez_field_26', '', '0', '0', '0', '0', '0', '0', '0'
);

INSERT INTO `contrexx_module_directory_inputfields` ( `id` , `typ` , `name` , `title` , `active` , `active_backend` , `is_required` , `read_only` , `sort` , `exp_search` , `is_search` )
VALUES (
NULL , '10', 'spez_field_27', '', '0', '0', '0', '0', '0', '0', '0'
);

INSERT INTO `contrexx_module_directory_inputfields` ( `id` , `typ` , `name` , `title` , `active` , `active_backend` , `is_required` , `read_only` , `sort` , `exp_search` , `is_search` )
VALUES (
NULL , '10', 'spez_field_28', '', '0', '0', '0', '0', '0', '0', '0'
);

INSERT INTO `contrexx_module_directory_inputfields` ( `id` , `typ` , `name` , `title` , `active` , `active_backend` , `is_required` , `read_only` , `sort` , `exp_search` , `is_search` )
VALUES (
NULL , '10', 'spez_field_29', '', '0', '0', '0', '0', '0', '0', '0'
);



ALTER TABLE `contrexx_module_directory_dir` ADD `spez_field_25` VARCHAR( 255 ) NOT NULL ,
ADD `spez_field_26` VARCHAR( 255 ) NOT NULL ,
ADD `spez_field_27` VARCHAR( 255 ) NOT NULL ,
ADD `spez_field_28` VARCHAR( 255 ) NOT NULL ,
ADD `spez_field_29` VARCHAR( 255 ) NOT NULL ;

UPDATE `contrexx_module_directory_settings` SET `setdescription` = 'Index-Ansicht für Kategorien' WHERE `contrexx_module_directory_settings`.`setid` =31 LIMIT 1 ;

UPDATE `contrexx_module_directory_settings` SET `setdescription` = 'Einträge nach Alphabet sortieren',`settyp` = '2' WHERE `contrexx_module_directory_settings`.`setid` =35 LIMIT 1 ;
*/