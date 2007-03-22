DROP TABLE IF EXISTS `astalavista_module_news_settings`;
CREATE TABLE `astalavista_module_news_settings` (
  `name` varchar(50) NOT NULL default '',
  `value` varchar(250) NOT NULL default '',
  KEY `name` (`name`)
) TYPE=MyISAM;


INSERT INTO `astalavista_module_news_settings` VALUES ('news_feed_description', 'Informationen rund um das Contrexx® Open Source CMS');
INSERT INTO `astalavista_module_news_settings` VALUES ('news_feed_status', '0');
INSERT INTO `astalavista_module_news_settings` VALUES ('news_feed_title', 'Contrexx® Open Source CMS');
INSERT INTO `astalavista_module_news_settings` VALUES ('news_headlines_limit', '10');
INSERT INTO `astalavista_module_news_settings` VALUES ('news_settings_activated', '0');