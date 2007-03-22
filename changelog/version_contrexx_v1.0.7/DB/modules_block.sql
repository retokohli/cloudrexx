INSERT INTO `contrexx_settings` ( `setid` , `setname` , `setvalue` , `setmodule` ) VALUES ('', 'blockStatus', '0', '7');

INSERT INTO `contrexx_modules` ( `id` , `name` , `description_variable` , `status` , `is_required` , `is_core` ) VALUES (7 , 'block', 'TXT_BLOCK_MODULE_DESCRIPTION', 'y', '0', '0');

INSERT INTO `contrexx_backend_areas` ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` ) VALUES ('', '2', 'navigation', 'TXT_BLOCK_SYSTEM', '1', 'index.php?cmd=block', '_self', '7', '0', '76');

CREATE TABLE `contrexx_module_block_blocks` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,`content` TEXT NOT NULL ,PRIMARY KEY ( `id` ));

INSERT INTO `contrexx_module_block_blocks` ( `id` , `content` ) VALUES ('', '<table width="150" border="0" cellspacing="0" cellpadding="0"> <tr> <td> </td> </tr> <tr> <td class="rechts" height="90"><a href="index.php?page=493"><strong>Kontaktpersonen:</strong><br /> Hier finden Sie<br /> s&auml;mtliche Kontaktpersonen</a></td> </tr> <tr> <td class="rechts" height="90"><strong>Besuchszeiten:</strong><br /> Der Besuch<br /> von Heimbewohnern<br /> ist immer m&ouml;glich</td> </tr> <tr> <td class="rechts" height="90"><a href="index.php?page=507">Hier finden Sie<br /> <strong>das Wichtigste in K&uuml;rze</strong> und einen Lageplan</a></td> </tr> <tr> <td>&nbsp;</td> </tr> </table>');
