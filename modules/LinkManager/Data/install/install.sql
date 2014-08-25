# Step 1: Insert Modul into components table
INSERT INTO `contrexx_component` (`name`, `type`) VALUES ('LinkManager', 'module');
# Step 2: Insert Module into Modules table
INSERT INTO `contrexx_modules` (`id`, `name`, `distributor`, `description_variable`, `status`, `is_required`, `is_core`, `is_active`) 
VALUES (900, 'LinkManager', 'Comvation', 'TXT_MODULE_LINKMANAGER_DESCRIPTION', 'y', 0, 0, 1);
# Step 3: Insert Menu table
INSERT INTO `contrexx_backend_areas` (`parent_area_id`,`type`,`scope`,`area_name`,`is_active`,`uri`,`target`,`module_id`, `order_id`, `access_id`)
VALUES (2, 'navigation', 'backend', 'TXT_MODULE_LINKMANAGER', 1, 'index.php?cmd=LinkManager', '_self', 900, 1, 1030);
INSERT INTO `contrexx_backend_areas` (`area_id`, `parent_area_id`, `type`, `scope`, `area_name`, `is_active`, `uri`, `target`, `module_id`, `order_id`, `access_id`) VALUES (NULL, '210', 'function', 'global', 'TXT_MODULE_LINKMANAGER_CRAWLER_RESULT', '1', 'index.php?cmd=LinkManager&act=crawlerResult', '_self', '900', '2', '1031');
INSERT INTO `contrexx_backend_areas` (`area_id`, `parent_area_id`, `type`, `scope`, `area_name`, `is_active`, `uri`, `target`, `module_id`, `order_id`, `access_id`) VALUES (NULL, '210', 'function', 'global', 'TXT_MODULE_LINKMANAGER_SETTINGS', '1', 'index.php?cmd=LinkManager&act=settings', '_self', '900', '3', '1032');
# Step 4: Add a module page in the ContentManager -> Module: LinkManager;

#step 4: for settings
INSERT INTO `contrexx_core_setting` (`section`, `name`, `group`, `type`, `value`, `values`, `ord`) VALUES ('LinkManager', 'entriesPerPage', 'config', 'text', '25', '', '0');


# Create Tables
CREATE TABLE IF NOT EXISTS `contrexx_module_linkmanager_crawler` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lang` tinyint(2) NOT NULL,
  `startTime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `endTime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `totalLinks` int(11) NOT NULL,
  `totalBrokenLinks` int(11) NOT NULL,
  `runStatus` enum('running','incomplete','completed') COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `contrexx_module_linkmanager_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lang` tinyint(2) NOT NULL,
  `requestedPath` text COLLATE utf8_unicode_ci NOT NULL,
  `linkStatusCode` int(1) DEFAULT NULL,
  `entryTitle` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `moduleName` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `moduleAction` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `moduleParams` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `detectedTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `flagStatus` tinyint(2) NOT NULL,
  `updatedBy` int(2) NOT NULL,
  `requestedLinkType` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `refererPath` text COLLATE utf8_unicode_ci,
  `leadPath` text COLLATE utf8_unicode_ci NOT NULL,
  `linkStatus` tinyint(2) NOT NULL,
  `linkRecheck` tinyint(2) NOT NULL,
  `brokenLinkText` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `contrexx_module_linkmanager_link` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lang` tinyint(2) NOT NULL,
  `requestedPath` text COLLATE utf8_unicode_ci NOT NULL,
  `linkStatusCode` int(1) DEFAULT NULL,
  `entryTitle` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `moduleName` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `moduleAction` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `moduleParams` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `detectedTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `flagStatus` tinyint(2) NOT NULL,
  `updatedBy` int(2) NOT NULL,
  `requestedLinkType` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `refererPath` text COLLATE utf8_unicode_ci,
  `leadPath` text COLLATE utf8_unicode_ci,
  `linkStatus` tinyint(2) NOT NULL,
  `linkRecheck` tinyint(2) NOT NULL,
  `brokenLinkText` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;