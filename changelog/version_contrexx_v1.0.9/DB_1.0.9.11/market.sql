Updatescript erstellt JT

ALTER TABLE `contrexx_module_market` ADD `spez_field_1` VARCHAR( 255 ) NOT NULL ,
ADD `spez_field_2` VARCHAR( 255 ) NOT NULL ,
ADD `spez_field_3` VARCHAR( 255 ) NOT NULL ,
ADD `spez_field_4` VARCHAR( 255 ) NOT NULL ,
ADD `spez_field_5` VARCHAR( 255 ) NOT NULL ;

CREATE TABLE `contrexx_module_market_spez_fields` (
`id` INT( 5 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`name` VARCHAR( 100 ) NOT NULL ,
`value` VARCHAR( 100 ) NOT NULL ,
`type` INT( 1 ) NOT NULL DEFAULT '1',
`lang_id` INT( 2 ) NOT NULL,
`active` INT( 1 ) NOT NULL
) ENGINE = MYISAM ;

INSERT INTO `contrexx_module_market_settings` VALUES (9, 'maxdayStatus', '0', 'TXT_MARKET_SET_MAXDAYS_ON', 2);


INSERT INTO `contrexx_module_market_settings` ( `id` , `name` , `value` , `description` , `type` )
VALUES (
NULL , 'searchPrice', '', 'TXT_MARKET_SET_EXP_SEARCH_PRICE', '3'
);

INSERT INTO `contrexx_module_market_spez_fields` ( `id` , `name` , `value` , `type` , `lang_id` , `active` )
VALUES (
NULL , 'spez_field_1', '', '1', '1', '0'
);

INSERT INTO `contrexx_module_market_spez_fields` ( `id` , `name` , `value` , `type` , `lang_id` , `active` )
VALUES (
NULL , 'spez_field_2', '', '1', '1', '0'
);

INSERT INTO `contrexx_module_market_spez_fields` ( `id` , `name` , `value` , `type` , `lang_id` , `active` )
VALUES (
NULL , 'spez_field_3', '', '1', '1', '0'
);

INSERT INTO `contrexx_module_market_spez_fields` ( `id` , `name` , `value` , `type` , `lang_id` , `active` )
VALUES (
NULL , 'spez_field_4', '', '1', '1', '0'
);

INSERT INTO `contrexx_module_market_spez_fields` ( `id` , `name` , `value` , `type` , `lang_id` , `active` )
VALUES (
NULL , 'spez_field_5', '', '1', '1', '0'
);