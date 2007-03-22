Das Update.php Script muss nach folgendem SQL-Befehl ausgeführt werden:

CREATE TABLE `contrexx_module_gallery_language` (
`gallery_id` INT UNSIGNED NOT NULL ,
`lang_id` INT UNSIGNED NOT NULL ,
`name` SET( 'name', 'desc' ) NOT NULL ,
`value` TEXT NOT NULL ,
PRIMARY KEY ( `gallery_id` , `lang_id` , `name` )
);

Achtung: Der folgende SQL-Befehl darf erst nach dem Update-Skript
durchgeführt werden:

ALTER TABLE `contrexx_module_gallery_categories` DROP `name`, DROP `description`;

Ansonsten gehen alle Gallery-Beschreibungen / Namen verloren! 

--------------------------------------------------------

Das update_2.php Script muss nach folgendem SQL-Befehl ausgeführt werden:

CREATE TABLE `contrexx_module_gallery_language_pics` (
`picture_id` INT UNSIGNED NOT NULL ,
`lang_id` INT UNSIGNED NOT NULL ,
`name` VARCHAR( 255 ) NOT NULL ,
`desc` VARCHAR(255) NOT NULL,
PRIMARY KEY ( `picture_id` , `lang_id` )
);

Achtung: Die folgenden SQL-Befehle dürfen erst nach dem Update-Skript
durchgeführt werden:

ALTER TABLE `contrexx_module_gallery_pictures` DROP `name`;
ALTER TABLE `contrexx_module_gallery_pictures` DROP `linkname`;

Ansonsten gehen alle Bildnamen und Beschreibungen verloren! 


