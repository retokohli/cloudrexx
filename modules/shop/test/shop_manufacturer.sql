DROP TABLE IF EXISTS `contrexx_module_shop_manufacturer`;
CREATE TABLE IF NOT EXISTS `contrexx_module_shop_manufacturer` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 ;

--
-- Dumping data for table `contrexx_module_shop_manufacturer`
--

INSERT INTO `contrexx_module_shop_manufacturer` (`id`, `name`, `url`) VALUES
(1, 'Comvation Internet Solutions', 'http://www.comvation.com'),
(2, 'Apple, Inc.', 'http://www.apple.com/');
