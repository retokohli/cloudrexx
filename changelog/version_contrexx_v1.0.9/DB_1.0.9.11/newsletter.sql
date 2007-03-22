/* update erstellt */

CREATE TABLE `contrexx_module_newsletter_system` (
  `sysid` int(7) NOT NULL auto_increment,
  `sysname` varchar(255) NOT NULL default '',
  `sysvalue` varchar(255) NOT NULL default '',
  `type` int(1) NOT NULL default '0',
  PRIMARY KEY  (`sysid`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


INSERT INTO `contrexx_module_newsletter_system` ( `sysid` , `sysname` , `sysvalue` , `type` )
VALUES (
NULL , 'defUnsubscribe', '1', '1'
);