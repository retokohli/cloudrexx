// update.php ausführen

Content Seite: Sitemap
<!-- BEGIN sitempap --> anstelle von <!-- BEGIN row -->


INSERT INTO `contrexx_settings` ( `setid` , `setname` , `setvalue` , `setmodule` ) VALUES ('3', 'dnsServer', '', '0');


DELETE FROM `contrexx_modules` WHERE `id` = 12 AND `name` = 'directory' AND `description_variable` = 'TXT_LINKS_MODULE_DESCRIPTION' AND `status` = 'y' AND `is_required` = 0 AND `is_core` = 0 LIMIT 1;
DELETE FROM `contrexx_modules` WHERE `id` = 20 AND `name` = 'forum' AND `description_variable` = 'TXT_FORUM_MODULE_DESCRIPTION' AND `status` = 'y' AND `is_required` = 0 AND `is_core` = 0 LIMIT 1;
DELETE FROM `contrexx_modules` WHERE `id` = 7 AND `name` = 'notepad' AND `description_variable` = 'TXT_NOTEPAD_MODULE_DESCRIPTION' AND `status` = 'y' AND `is_required` = 0 AND `is_core` = 0 LIMIT 1;
DELETE FROM `contrexx_modules` WHERE `id` IS NULL AND `name` = '' AND `description_variable` = '' AND `status` = 'y' AND `is_required` = 0 AND `is_core` = 0 LIMIT 1;