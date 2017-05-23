SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `contrexx_module_topics_rel_entry_category`;
CREATE TABLE `contrexx_module_topics_rel_entry_category` (
`entry_id` INT UNSIGNED NOT NULL,
`category_id` INT UNSIGNED NOT NULL,
INDEX `category_id` (`category_id`),
PRIMARY KEY(`entry_id`, `category_id`)
) ENGINE = InnoDB;
-- Redundant: INDEX `entry_id` (`entry_id`),

DROP TABLE IF EXISTS `contrexx_module_topics_categories`;
CREATE TABLE `contrexx_module_topics_categories` (
`id` INT UNSIGNED AUTO_INCREMENT,
`parent_id` INT UNSIGNED DEFAULT NULL,
`active` TINYINT(1) UNSIGNED NOT NULL,
`name` VARCHAR(255) NOT NULL,
`slug` VARCHAR(255) NOT NULL,
`description` TEXT DEFAULT NULL,
`created` DATETIME NOT NULL,
`updated` DATETIME DEFAULT NULL,
PRIMARY KEY(`id`)
) ENGINE = InnoDB;
ALTER TABLE `contrexx_module_topics_categories`
  ADD KEY `active_idx` (`active`),
  ADD KEY `parent_idx` (`parent_id`),
  ADD KEY `name_idx` (`name`),
  ADD KEY `slug_idx` (`slug`),
  ADD KEY `description_idx` (`description`(255)),
  ADD KEY `created_idx` (`created`),
  ADD KEY `updated_idx` (`updated`);
--  ADD UNIQUE KEY `slug_idx` (`slug`);

DROP TABLE IF EXISTS `contrexx_module_topics_entries`;
CREATE TABLE `contrexx_module_topics_entries` (
`id` INT UNSIGNED AUTO_INCREMENT,
`active` TINYINT(1) UNSIGNED NOT NULL,
`name` VARCHAR(255) NOT NULL,
`slug` VARCHAR(255) NOT NULL,
`href` VARCHAR(1024) DEFAULT NULL,
`description` text NOT NULL,
`created` DATETIME NOT NULL,
`updated` DATETIME DEFAULT NULL,
PRIMARY KEY(`id`)
) ENGINE = InnoDB;
ALTER TABLE `contrexx_module_topics_entries`
  ADD KEY `active_idx` (`active`),
  ADD KEY `name_idx` (`name`),
  ADD KEY `slug_idx` (`slug`),
  ADD KEY `description_idx` (`description`(255)),
  ADD KEY `created_idx` (`created`),
  ADD KEY `updated_idx` (`updated`);
--  ADD UNIQUE KEY `slug_idx` (`slug`);

DROP TABLE IF EXISTS `contrexx_translations`;
CREATE TABLE `contrexx_translations` (
  `id` int(10) UNSIGNED AUTO_INCREMENT,
  `locale` varchar(8) NOT NULL,
  `object_class` varchar(255) NOT NULL,
  `field` varchar(32) NOT NULL,
  `foreign_key` varchar(64) DEFAULT NULL,
  `content` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lookup_unique_idx` (`locale`,`object_class`,`foreign_key`,`field`)
) ENGINE=InnoDB;
-- Redundant (covered by the unique index):
--  INDEX `translations_lookup_idx` (`locale`, `object_class`, `foreign_key`),
-- Custom index for faster content lookup
ALTER TABLE `contrexx_translations`
  ADD KEY `content_lookup_idx` (`content`(255), `object_class`, `field`);

ALTER TABLE `contrexx_module_topics_rel_entry_category`
  ADD CONSTRAINT `contrexx_module_topics_rel_entry_category_ibfk_1`
  FOREIGN KEY (`category_id`) REFERENCES `contrexx_module_topics_categories` (`id`)
  ON DELETE CASCADE;
ALTER TABLE `contrexx_module_topics_rel_entry_category`
  ADD CONSTRAINT `contrexx_module_topics_rel_entry_category_ibfk_2`
  FOREIGN KEY (`entry_id`) REFERENCES `contrexx_module_topics_entries` (`id`)
  ON DELETE CASCADE;

-- These are taken from the development system and may be arbitrary.
-- Define and use proper values for production.
REPLACE INTO `contrexx_access_group_static_ids`
(`access_id`, `group_id`)
VALUES
(900, 1),
(900, 9);

REPLACE INTO `contrexx_backend_areas`
(`area_id`, `parent_area_id`, `type`, `scope`, `area_name`, `is_active`,
 `uri`, `target`, `module_id`, `order_id`, `access_id`)
VALUES
(229, 2, 'navigation', 'backend', 'TXT_MODULE_TOPICS', 1, 'index.php?cmd=Topics&act=Entry', '_self', 900, 2, 900);

REPLACE INTO `contrexx_component`
(`id`, `name`, `type`)
VALUES
(NULL, 'Topics', 'module');

REPLACE INTO `contrexx_modules`
(`id`, `name`, `distributor`, `description_variable`, `status`,
`is_required`, `is_core`, `is_active`, `is_licensed`, `additional_data`)
VALUES
(900, 'Topics', 'Comvation', 'TXT_MODULE_TOPICS_DESCRIPTION', 'y', 0, 0, 1, 0, NULL);

SET FOREIGN_KEY_CHECKS=1;
