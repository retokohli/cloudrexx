
DROP TABLE IF EXISTS contrexx_module_support_attachment;
CREATE TABLE contrexx_module_support_attachment (
  `id`         int(10)      unsigned NOT NULL auto_increment,
  `message_id` int(10)      unsigned NOT NULL,
  `name`       varchar(255)              NULL default NULL,
  `type`       varchar(255)              NULL default NULL,
  `content`    mediumtext            NOT NULL,
  PRIMARY KEY (`id`),
  KEY `message_id` (`message_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS contrexx_module_support_info_field;
CREATE TABLE contrexx_module_support_info_field (
  id        int(10)          unsigned NOT NULL auto_increment,
  `status`  tinyint(1)       unsigned NOT NULL default '1',
  `order`   int(10)          unsigned NOT NULL default '0',
  `type`    tinyint(2)       unsigned NOT NULL default '1',
  mandatory tinyint(1)       unsigned NOT NULL default '0',
  multiple  tinyint(1)       unsigned NOT NULL default '0',
  PRIMARY KEY (id),
  KEY `status` (`status`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS contrexx_module_support_info_field_language;
CREATE TABLE contrexx_module_support_info_field_language (
  info_field_id int(10)      unsigned NOT NULL,
  language_id   int(10)      unsigned NOT NULL,
  `name`        varchar(255)          NOT NULL,
  PRIMARY KEY (info_field_id, language_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS contrexx_module_support_info_field_rel_support_category;
CREATE TABLE contrexx_module_support_info_field_rel_support_category (
  info_field_id       int(10) unsigned NOT NULL,
  support_category_id int(10) unsigned NOT NULL,
  PRIMARY KEY (info_field_id, support_category_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS contrexx_module_support_knowledge_base;
CREATE TABLE contrexx_module_support_knowledge_base (
  id                    int(10)    unsigned NOT NULL auto_increment,
  `support_category_id` int(10)    unsigned NOT NULL,
  `status`              tinyint(1) unsigned NOT NULL default '1',
  PRIMARY KEY               (id),
  KEY `support_category_id` (`support_category_id`),
  KEY `status`              (`status`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS contrexx_module_support_knowledge_base_language;
CREATE TABLE contrexx_module_support_knowledge_base_language (
  knowledge_base_id int(10)      unsigned NOT NULL,
  language_id       int(10)      unsigned NOT NULL,
  subject           varchar(255)          NOT NULL,
  body              mediumtext            NOT NULL,
  PRIMARY KEY (knowledge_base_id, language_id),
  INDEX subject (subject),
  INDEX body (body(32))
) ENGINE=MyISAM;

DROP TABLE IF EXISTS contrexx_module_support_message;
CREATE TABLE contrexx_module_support_message (
  id            int(10)      unsigned NOT NULL auto_increment,
  ticket_id     int(10)      unsigned NOT NULL,
--  `status`      tinyint(2)   unsigned NOT NULL default 1,
  `from`        varchar(255)          NOT NULL,
  subject       varchar(255)          NOT NULL,
  body          mediumtext            NOT NULL,
  `date`        datetime              NOT NULL,
  `timestamp`   timestamp             NOT NULL default current_timestamp,
  PRIMARY KEY     (id),
  KEY ticket_id   (ticket_id),
--  KEY status      (status),
  KEY `date`      (`date`),
  KEY `timestamp` (`timestamp`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS contrexx_module_support_category;
CREATE TABLE `contrexx_module_support_category` (
  `id`        int(10)    unsigned NOT NULL auto_increment,
  `parent_id` int(10)    unsigned NOT NULL default '0',
  `status`    tinyint(1) unsigned NOT NULL default '1',
  `order`     int(10)    unsigned NOT NULL default '0',
  PRIMARY KEY     (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `status`    (`status`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS contrexx_module_support_category_language;
CREATE TABLE `contrexx_module_support_category_language` (
  `support_category_id` int(10)      unsigned NOT NULL,
  `language_id`         int(10)      unsigned NOT NULL,
  `name`                varchar(255)          NOT NULL,
  PRIMARY KEY (`support_category_id`,`language_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS contrexx_module_support_category;
CREATE TABLE contrexx_module_support_category (
  id        int(10)    unsigned NOT NULL auto_increment,
  parent_id int(10)    unsigned NOT NULL default '0',
  `status`  tinyint(1) unsigned NOT NULL default '1',
  `order`   int(10)    unsigned NOT NULL default '0',
  PRIMARY KEY (id),
  KEY parent_id (parent_id),
  KEY `status` (`status`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS contrexx_module_support_category_language;
CREATE TABLE contrexx_module_support_category_language (
  support_category_id int(10)      unsigned NOT NULL,
  language_id         int(10)      unsigned NOT NULL,
  `name`              varchar(255)          NOT NULL,
  PRIMARY KEY (support_category_id, language_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS contrexx_module_support_ticket;
CREATE TABLE contrexx_module_support_ticket (
  id                  int(10)      unsigned NOT NULL auto_increment,
  support_category_id int(10)      unsigned NOT NULL,
  language_id         int(10)      unsigned NOT NULL,
  source              tinyint(2)   unsigned NOT NULL default 0,
  email               varchar(255)          NOT NULL,
  `timestamp`         timestamp             NOT NULL default current_timestamp,
  PRIMARY KEY (id),
  KEY support_category_id (support_category_id),
  KEY language_id         (language_id),
  KEY source              (source),
  KEY email               (email),
  KEY `timestamp`         (`timestamp`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS contrexx_module_support_ticket_event;
CREATE TABLE contrexx_module_support_ticket_event (
  id              int(10)     unsigned NOT NULL auto_increment,
  ticket_id       int(10)     unsigned     NULL default NULL,
  `event`         tinyint(2)  unsigned     NULL default NULL,
  `value`         int(10)     unsigned     NULL default NULL,
  `user_id`       int(10)     unsigned     NULL default NULL,
  `status`        tinyint(2)  unsigned     NULL default NULL,
  `timestamp`     timestamp            NOT NULL default current_timestamp,
  PRIMARY KEY     (id),
  KEY ticket_id   (ticket_id),
  KEY `event`     (`event`)
) ENGINE=MyISAM;

-- Mind the module ID (should be unique)
REPLACE INTO `contrexx_modules` (
  `id`, `name`, `description_variable`, `status`, `is_required`, `is_core`
) VALUES (
  10111, 'support', 'TXT_SUPPORT_MODULE_DESCRIPTION', 'y', 0, 0
);

-- Mind the access ID (should be unique)
INSERT INTO `contrexx_backend_areas` (
  `area_id`, `parent_area_id`, `type`, `scope`, `area_name`, `is_active`, `uri`, `target`, `module_id`, `order_id`, `access_id`
) VALUES (
  NULL, 2, 'navigation', 'backend', 'TXT_SUPPORT', 1, 'index.php?cmd=support', '_self', 10111, 0, 10111
);
