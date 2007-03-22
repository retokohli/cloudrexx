# CORE
# -----------------------------------

# Banner module - core_module
# -----------------------------------
INSERT INTO `contrexx_modules` ( `id` , `name` , `description_variable` , `status` , `is_required` , `is_core` )VALUES ('28' , 'banner', 'TXT_BANNER_MODULE_DESCRIPTION', 'y', '0', '1');
INSERT INTO `contrexx_backend_areas` ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` ) VALUES ('62', '3', 'navigation', 'TXT_BANNER_ADMINISTRATION', '1', 'index.php?cmd=banner', '_self', '28', '1', '61');
INSERT INTO `contrexx_backend_areas` ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` ) VALUES ('65', '12', 'function', 'TXT_GALLERY_MENU_OVERVIEW', '1', '', '_self', '0', '1', '65');
INSERT INTO `contrexx_backend_areas` ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` ) VALUES ('66', '12', 'function', 'TXT_GALLERY_MENU_NEW_CATEGORY', '1', '', '_self', '0', '2', '66');
INSERT INTO `contrexx_backend_areas` ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` ) VALUES ('67', '12', 'function', 'TXT_GALLERY_MENU_UPLOAD', '1', '', '_self', '0', '3', '67');
INSERT INTO `contrexx_backend_areas` ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` ) VALUES ('68', '12', 'function', 'TXT_GALLERY_MENU_IMPORT', '1', '', '_self', '0', '4', '68');
INSERT INTO `contrexx_backend_areas` ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` ) VALUES ('69', '12', 'function', 'TXT_GALLERY_MENU_VALIDATE', '1', '', '_self', '0', '5', '69');
INSERT INTO `contrexx_backend_areas` ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` ) VALUES ('70', '12', 'function', 'TXT_GALLERY_MENU_SETTINGS', '1', '', '_self', '0', '6', '70');
INSERT INTO `contrexx_backend_areas` ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` ) VALUES ('71', '62', 'function', 'TXT_BANNER_MENU_OVERVIEW', '1', '', '_self', '0', '1', '71');
INSERT INTO `contrexx_backend_areas` ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` ) VALUES ('72', '62', 'function', 'TXT_BANNER_MENU_GROUP_ADD', '1', '', '_self', '0', '1', '72');
INSERT INTO `contrexx_backend_areas` ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` ) VALUES ('73', '62', 'function', 'TXT_BANNER_MENU_BANNER_NEW', '1', '', '_self', '0', '1', '73');
INSERT INTO `contrexx_backend_areas` ( `area_id` , `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id` ) VALUES ('74', '62', 'function', 'TXT_BANNER_MENU_SETTINGS', '1', '', '_self', '0', '1', '74');

# Versionierungssystem
# --------------------------------
INSERT INTO `contrexx_content_navigation` VALUES ('', 0, 'Lost & Found', '', 9999, 'off', 'system', 1121083147, 'lost_and_found', 1, 1, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content` VALUES ('', 'Wiederhergestellte Seiten werden unter dieser Kategorie eingefügt.', 'Lost &amp; Found', 'Lost & Found', 'Lost & Found', 'index', '', '', 'y');


# Repository
# --------------------------------
INSERT INTO `contrexx_module_repository` VALUES (499, 27, '{RECOM_STATUS}\r\n\r\n<!-- BEGIN recommend_form -->\r\n<p>\r\n{RECOM_TEXT}\r\n</p>\r\n{RECOM_SCRIPT}\r\n<form action=\\"index.php?section=recommend&amp;act=sendRecomm\\" method=\\"post\\" name=\\"recommend\\">\r\n<input type=\\"hidden\\" name=\\"uri\\" value=\\"{RECOM_REFERER}\\" />\r\n<input type=\\"hidden\\" name=\\"female_salutation_text\\" value=\\"{RECOM_FEMALE_SALUTATION_TEXT}\\" />\r\n<input type=\\"hidden\\" name=\\"male_salutation_text\\" value=\\"{RECOM_MALE_SALUTATION_TEXT}\\" />\r\n<input type=\\"hidden\\" name=\\"preview_text\\" value=\\"{RECOM_PREVIEW}\\" />\r\n<table style=\\"width: 90%\\">\r\n<tr>\r\n	<td style=\\"width: 40%; padding-bottom: 15px;\\">{RECOM_TXT_RECEIVER_NAME}:</td>\r\n	<td style=\\"padding-bottom: 15px; width: 60%\\"><input name=\\"receivername\\" type=\\"text\\" maxlength=\\"100\\" value=\\"{RECOM_RECEIVER_NAME}\\" style=\\"width: 100%\\" onchange=\\"update();\\"/></td>\r\n</tr>\r\n<tr>\r\n	<td style=\\"padding-bottom: 15px;\\">{RECOM_TXT_RECEIVER_MAIL}:</td>\r\n	<td style=\\"padding-bottom: 15px;\\"><input name=\\"receivermail\\" type=\\"text\\" maxlength=\\"100\\" value=\\"{RECOM_RECEIVER_MAIL}\\" style=\\"width: 100%\\" onchange=\\"update();\\"/></td>\r\n</tr>\r\n<tr>\r\n	<td style=\\"padding-bottom: 15px;\\" valign=\\"top\\">{RECOM_TXT_GENDER}:</td>\r\n	<td style=\\"padding-bottom: 15px;\\"><input name=\\"gender\\" style=\\"border: none; margin-left: 0px;\\" type=\\"radio\\" value=\\"female\\" {RECOM_FEMALE_CHECKED} onclick=\\"update();\\">{RECOM_TXT_FEMALE}</input><br />\r\n		<input name=\\"gender\\" style=\\"border: none; margin-left: 0px;\\" type=\\"radio\\" value=\\"male\\" {RECOM_MALE_CHECKED}  onclick=\\"update();\\">{RECOM_TXT_MALE}</input></td>\r\n</tr>\r\n<tr>\r\n	<td width=\\"100\\" style=\\"padding-bottom: 15px;\\">{RECOM_TXT_SENDER_NAME}:</td>\r\n	<td style=\\"padding-bottom: 15px;\\"><input name=\\"sendername\\" type=\\"text\\" maxlength=\\"100\\" value=\\"{RECOM_SENDER_NAME}\\" style=\\"width: 100%\\" onchange=\\"update();\\"/></td>\r\n</tr>\r\n<tr>\r\n	<td style=\\"padding-bottom: 15px;\\">{RECOM_TXT_SENDER_MAIL}:</td>\r\n	<td style=\\"padding-bottom: 15px;\\"><input name=\\"sendermail\\" type=\\"text\\" maxlength=\\"100\\" value=\\"{RECOM_SENDER_MAIL}\\" style=\\"width: 100%\\" onchange=\\"update();\\"/></td>\r\n</tr>\r\n<tr>\r\n	<td valign=\\"top\\">{RECOM_TXT_COMMENT}:</td>\r\n	<td style=\\"padding-bottom: 15px;\\"><textarea rows=\\"7\\" cols=\\"30\\" name=\\"comment\\" style=\\"width: 100%\\" onchange=\\"update();\\">{RECOM_COMMENT}</textarea></td>\r\n</tr>\r\n<tr>\r\n	<td valign=\\"top\\">{RECOM_TXT_PREVIEW}:</td>\r\n	<td style=\\"padding-bottom: 15px;\\">\r\n	<textarea name=\\"preview\\" style=\\"width: 100%; height: 200px;\\" readonly></textarea></td>\r\n</tr>\r\n<tr>\r\n	<td>&nbsp;</td>\r\n	<td><input type=\\"submit\\" value=\\"Senden\\" /> <input type=\\"reset\\" value=\\"Löschen\\" /></td>\r\n</tr>\r\n</table>\r\n</form>\r\n<!-- END recommend_form -->', 'Seite weiterempfehlen', '', 'n', 0, 'off', 'system', 1000, '1');


INSERT INTO `contrexx_settings` ( `setid` , `setname` , `setvalue` , `setmodule` )
VALUES (
'', 'calendarheadlines', '1', '21'
);

INSERT INTO `contrexx_settings` ( `setid` , `setname` , `setvalue` , `setmodule` )
VALUES (
'', 'calendarheadlinescount', '5', '21'
);

# FileBrowser
UPDATE `contrexx_modules` SET `status` = 'n' WHERE `id` =26 AND `name` = 'fileBrowser' AND `description_variable` = 'TXT_FILEBROWSER_DESCRIPTION' AND `status` = 'y' AND `is_required` =1 AND `is_core` =1 LIMIT 1 ;

# Contact Core Modul
# ------------------------------------
INSERT INTO `contrexx_settings` ( `setid` , `setname` , `setvalue` , `setmodule` )VALUES ('5', 'contactFormEmail4', '', '6'), ('6', 'contactFormEmail5', '', '6');
INSERT INTO `contrexx_settings` ( `setid` , `setname` , `setvalue` , `setmodule` )VALUES ('7', 'contactFormEmail6', '', '6');

# Content-Navigation
# -------------------------------------

ALTER TABLE `contrexx_content` ADD `metatitle` VARCHAR(250) NOT NULL AFTER `title`;
UPDATE `contrexx_content` SET `metatitle` = `title`;
UPDATE `contrexx_backend_areas` SET `order_id` = '8' WHERE `area_id` =8 LIMIT 1 ;

INSERT INTO `contrexx_settings` VALUES ('', 'bannerStatus', '0', 28);