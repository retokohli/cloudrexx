DROP TABLE `contrexx_module_shop_categories`;
CREATE TABLE `contrexx_module_shop_categories` (
  `catid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parentid` int(10) unsigned NOT NULL DEFAULT '0',
  `catname` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `catsorting` int(10) unsigned NOT NULL DEFAULT '100',
  `catstatus` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `picture` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `flags` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `catdesc` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`catid`),
  FULLTEXT KEY `flags` (`flags`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=10 ;

INSERT INTO `contrexx_module_shop_categories` (`catid`, `parentid`, `catname`, `catsorting`, `catstatus`, `picture`, `flags`, `catdesc`) VALUES
(1, 0, 'Gadgets', 1, 1, 'iPhone.jpg', '', ''),
(3, 0, 'Mitgliedschaft', 0, 1, 'mitgliedschaft_400x300.jpg.thumb', '', 'Balh'),
(4, 0, 'Test mit Bild', 0, 1, 'premium_300.jpg', '', ''),
(5, 0, 'Schuhe', 0, 1, 'mitgliedschaft_400x300.jpg', '', 'Schuhe in allen Geschmacksrichtungen'),
(6, 0, 'Shirts', 0, 1, 'iPhone.jpg', '', 'Shirts in allen Temperaturen'),
(7, 6, 'T-Shirts', 0, 1, 'iPhone.jpg', '', 'Alle Shirts mit T');
