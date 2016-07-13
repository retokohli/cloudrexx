SET FOREIGN_KEY_CHECKS = 0;
CREATE TABLE `contrexx_access_group_dynamic_ids` (
  `access_id` int(11) unsigned NOT NULL DEFAULT '0',
  `group_id` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`access_id`,`group_id`)
) ENGINE=MyISAM;
