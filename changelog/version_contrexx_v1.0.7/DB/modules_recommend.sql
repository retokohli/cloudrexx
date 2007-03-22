# Recommend module
# -----------------------------------
INSERT INTO `contrexx_modules` ( `id` , `name` , `description_variable` , `status` , `is_required` , `is_core` ) VALUES ('27', 'recommend', 'TXT_RECOMMEND_MODULE_DESCRIPTION', 'y', '0', '0');
INSERT INTO `contrexx_backend_areas` ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` ) VALUES ('', '2', 'navigation', 'TXT_RECOMMEND', '1', 'index.php?cmd=recommend', '_self', '27', '0', '31');
CREATE TABLE `contrexx_module_recommend` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,`name` VARCHAR( 255 ) NOT NULL ,`value` TEXT NOT NULL, PRIMARY KEY ( `id` ) );
ALTER TABLE `contrexx_module_recommend` ADD `lang_id` INT DEFAULT '1' NOT NULL ;
INSERT INTO `contrexx_module_recommend` ( `id` , `name` , `value`, `lang_id` ) VALUES ('', 'body', 'Sehr geehrte(r) Herr/Frau <RECEIVER_NAME>

Folgende Seite wurde ihnen von <SENDER_NAME> (<SENDER_MAIL>) empfohlen:

<URL>

Anmerkung von <SENDER_NAME>:

<COMMENT>', '1'
);

INSERT INTO `contrexx_module_recommend` ( `id` , `name` , `value`, `lang_id` ) VALUES ('', 'subject', 'Seitenempfehlung von <SENDER_NAME>', '1');	
INSERT INTO `contrexx_module_recommend` ( `id` , `name` , `value` , `lang_id` ) VALUES ('', 'salutation_female', 'Liebe', '1');
INSERT INTO `contrexx_module_recommend` ( `id` , `name` , `value` , `lang_id` ) VALUES ('', 'salutation_male', 'Lieber', '1');