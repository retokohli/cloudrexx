# shop
UPDATE `contrexx_module_shop_payment_processors` SET `name` = 'Saferpay_All_Cards' WHERE `id` =1 LIMIT 1 ;

INSERT INTO `contrexx_module_shop_payment_processors` VALUES ('', 'external', 'Saferpay_Mastercard_Multipay_CAR', 'Saferpay is a comprehensive Internet payment platform, specially developed for commercial applications. It provides a guarantee of secure payment processes over the Internet for merchants as well as for cardholders. Merchants benefit from the easy integration of the payment method into their e-commerce platform, and from the modularity with which they can take account of current and future requirements. Cardholders benefit from the security of buying from any shop that uses Saferpay.', 'http://www.saferpay.com/', 1, 'logo_saferpay.gif', '');
INSERT INTO `contrexx_module_shop_payment_processors` VALUES ('', 'external', 'Saferpay_Visa_Multipay_CAR', 'Saferpay is a comprehensive Internet payment platform, specially developed for commercial applications. It provides a guarantee of secure payment processes over the Internet for merchants as well as for cardholders. Merchants benefit from the easy integration of the payment method into their e-commerce platform, and from the modularity with which they can take account of current and future requirements. Cardholders benefit from the security of buying from any shop that uses Saferpay.', 'http://www.saferpay.com/', 1, 'logo_saferpay.gif', '');


ALTER TABLE `contrexx_module_calendar` CHANGE `name` `name` VARCHAR( 100 ) NOT NULL;
ALTER TABLE `contrexx_module_gallery_categories` ADD FULLTEXT `galleryCategoryIndex` (`name` ,`description`);
ALTER TABLE `contrexx_module_gallery_pictures` ADD FULLTEXT `galleryPicturesIndex` (`name` ,`path`);

DROP TABLE IF EXISTS `contrexx_newsletter_settings`;
CREATE TABLE `contrexx_newsletter_settings` (
  `setid` smallint(6) NOT NULL auto_increment,
  `setname` varchar(250) NOT NULL default '',
  `setvalue` text NOT NULL,
  `setdescription` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`setid`),
  KEY `setname` (`setname`)
) TYPE=MyISAM;

INSERT INTO `contrexx_newsletter_settings` VALUES (1, 'newsletterLimitSafemode', '10', 'TXT_LIMIT_SAFEMODE');
INSERT INTO `contrexx_newsletter_settings` VALUES (2, 'newsletterSendLimit', '10', 'TXT_SEND_LIMIT');
INSERT INTO `contrexx_newsletter_settings` VALUES (3, 'newsletterWordWrap', '65', 'TXT_WORD_WRAP');
INSERT INTO `contrexx_newsletter_settings` VALUES (4, 'newsletterSenderName', 'Contrexx.com Developer Team', 'TXT_SENDER_NAME');
INSERT INTO `contrexx_newsletter_settings` VALUES (5, 'newsletterSenderEmail', 'support@contrexx.com', 'TXT_SENDER_EMAIL');
INSERT INTO `contrexx_newsletter_settings` VALUES (6, 'newsletterTitle', 'Newsletter Subject ', 'TXT_STANDARD_NEWSLETTER_TITLE');
INSERT INTO `contrexx_newsletter_settings` VALUES (7, 'newsletterMaxAttachment', '3', 'TXT_NEWSLETTER_MAX_ATTACHMENT');
INSERT INTO `contrexx_newsletter_settings` VALUES (8, 'newsletterContentTitle', 's e c u r i t y h e a d l i n e s', 'TXT_NEWSLETTER_CONTENT_TITLE');


DROP TABLE IF EXISTS `contrexx_module_gallery_settings`;
CREATE TABLE `contrexx_module_gallery_settings` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(30) NOT NULL default '',
  `value` text NOT NULL,
  `description` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;


INSERT INTO `contrexx_module_gallery_settings` VALUES (1, 'max_images_upload', '10', 'TXT_MAX_IMAGES_UPLOAD');
INSERT INTO `contrexx_module_gallery_settings` VALUES (2, 'standard_quality', '90', 'TXT_STANDARD_QUALITY_UPLOADED_PICS');
INSERT INTO `contrexx_module_gallery_settings` VALUES (3, 'standard_size_proz', '25', 'TXT_STANDARD_SIZE_UPLOADED_PICS_PROZ');
INSERT INTO `contrexx_module_gallery_settings` VALUES (4, 'standard_width_abs', '100', 'TXT_STANDARD_SIZE_UPLOADED_PICS_ABS');
INSERT INTO `contrexx_module_gallery_settings` VALUES (7, 'standard_size_type', 'abs', 'TXT_STANDARD_SIZE_TYPE_THUMBNAILS');
INSERT INTO `contrexx_module_gallery_settings` VALUES (6, 'standard_height_abs', '0', 'TXT_STANDARD_HEIGHT_UPLOADED_PICS_ABS');
INSERT INTO `contrexx_module_gallery_settings` VALUES (9, 'validation_standard_type', 'all', 'TXT_VAIDATION_STANDARD_TYPE');
INSERT INTO `contrexx_module_gallery_settings` VALUES (8, 'validation_show_limit', '10', 'TXT_VALIDATION_SHOW_LIMIT');
INSERT INTO `contrexx_module_gallery_settings` VALUES (11, 'show_names', 'off', 'TXT_SHOW_PICTURE_NAME');