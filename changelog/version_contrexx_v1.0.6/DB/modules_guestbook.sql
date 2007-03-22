# GUESTBOOK
# -----------------------------------

ALTER TABLE `contrexx_module_guestbook` ADD `status` TINYINT( 1 ) UNSIGNED DEFAULT '0' NOT NULL AFTER `id`;

DROP TABLE IF EXISTS `contrexx_module_guestbook_settings`;
CREATE TABLE `contrexx_module_guestbook_settings` (
  `name` varchar(50) NOT NULL default '',
  `value` varchar(250) NOT NULL default '',
  KEY `name` (`name`)
) TYPE=MyISAM;


INSERT INTO `contrexx_module_guestbook_settings` VALUES ('guestbook_send_notification_email', '1');
INSERT INTO `contrexx_module_guestbook_settings` VALUES ('guestbook_activate_submitted_entries', '0');