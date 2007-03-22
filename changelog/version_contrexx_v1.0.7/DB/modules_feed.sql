DROP TABLE IF EXISTS `contrexx_module_feed_newsml_categories`;
CREATE TABLE IF NOT EXISTS `contrexx_module_feed_newsml_categories` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `providerId` text NOT NULL,
  `name` varchar(40) NOT NULL default '',
  `subjectCodes` text NOT NULL,
  `showSubjectCodes` enum('all','only','exclude') NOT NULL default 'all',
  `template` text NOT NULL,
  `limit` smallint(6) NOT NULL default '0',
  `auto_update` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `contrexx_module_feed_newsml_documents`;
CREATE TABLE IF NOT EXISTS `contrexx_module_feed_newsml_documents` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `publicIdentifier` text NOT NULL,
  `providerId` text NOT NULL,
  `dateId` int(8) unsigned NOT NULL default '0',
  `newsItemId` text NOT NULL,
  `revisionId` smallint(5) unsigned NOT NULL default '0',
  `thisRevisionDate` int(14) NOT NULL default '0',
  `urgency` smallint(5) unsigned NOT NULL default '0',
  `subjectCode` int(10) unsigned NOT NULL default '0',
  `headLine` varchar(67) NOT NULL default '',
  `dataContent` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `contrexx_module_feed_newsml_providers`;
CREATE TABLE IF NOT EXISTS `contrexx_module_feed_newsml_providers` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `providerId` text NOT NULL,
  `name` varchar(40) NOT NULL default '',
  `path` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=2 ;

INSERT INTO `contrexx_module_feed_newsml_providers` VALUES (1, 'www.sda-ats.ch', 'sda-Online', '/sportnews');

INSERT INTO `contrexx_settings` ( `setid` , `setname` , `setvalue` , `setmodule` )
VALUES (
'', 'feedNewsMLStatus', '0', '22'
);