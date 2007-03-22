// sql modifications from Contrexx v1.0.1


UPDATE `contrexx_modules` SET `name` = 'media1' WHERE `id` =9;

INSERT INTO `contrexx_modules` (`id`, `name`, `description_variable`, `status`, `is_required`, `is_core`) VALUES (24, 'media2', 'TXT_MEDIA_MODULE_DESCRIPTION', 'y', 0, 1);
INSERT INTO `contrexx_modules` (`id`, `name`, `description_variable`, `status`, `is_required`, `is_core`) VALUES (25, 'media3', 'TXT_MEDIA_MODULE_DESCRIPTION', 'y', 0, 1);
INSERT INTO `contrexx_stats_config` VALUES (18, 'paging_limit_visitor_details', '100', 1);
ALTER TABLE `contrexx_stats_search` ADD `external` ENUM( '0', '1' ) DEFAULT '0' NOT NULL ;

INSERT INTO `contrexx_backend_areas` VALUES (58, 4, 'navigation', 'TXT_SYSTEM_UPDATE', 1, 'index.php?cmd=systemUpdate', '_self', 0, 0, 58);

INSERT INTO `contrexx_modules` VALUES (3, 'gallery', 'TXT_GALLERY_MODULE_DESCRIPTION', 'y', 0, 0);
INSERT INTO `contrexx_modules` VALUES (4, 'newsletter', 'TXT_NEWSLETTER_MODULE_DESCRIPTION', 'y', 0, 0);
INSERT INTO `contrexx_modules` VALUES (17, 'voting', 'TXT_VOTING_MODULE_DESCRIPTION', 'y', 0, 0);
INSERT INTO `contrexx_modules` VALUES (19, 'docsys', 'TXT_DOC_SYS_MODULE_DESCRIPTION', 'y', 0, 0);
INSERT INTO `contrexx_modules` VALUES (21, 'calendar', 'TXT_CALENDAR_MODULE_DESCRIPTION', 'y', 0, 0);
INSERT INTO `contrexx_modules` VALUES (22, 'feed', 'TXT_FEED_MODULE_DESCRIPTION', 'y', 0, 0);