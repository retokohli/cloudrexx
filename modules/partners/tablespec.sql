-- DROP TABLE IF EXISTS contrexx_module_partners_to_labels;
-- DROP TABLE IF EXISTS contrexx_module_partners;
-- DROP TABLE IF EXISTS contrexx_module_partners_label_entry_text;
-- DROP TABLE IF EXISTS contrexx_module_partners_label_entry;
-- DROP TABLE IF EXISTS contrexx_module_partners_assignable_label_text;
-- DROP TABLE IF EXISTS contrexx_module_partners_assignable_label;

CREATE TABLE `contrexx_module_partners` (
  `id`                  INT NOT NULL AUTO_INCREMENT,
  `name`                VARCHAR(80),
  `first_contact_name`  VARCHAR(80),
  `first_contact_email` VARCHAR(80),
  `web_url`             VARCHAR(80),
  `address`             TEXT,
  `city`                VARCHAR(45),
  `zip_code`            VARCHAR(20),
  `phone_nr`            VARCHAR(45),
  `fax_nr`              VARCHAR(45),
  `creation_date`       DATE,
  `customer_quote`      TEXT,
  `logo_url`            VARCHAR(160),
  `num_installations`   INT NOT NULL DEFAULT 0,
  `description`         TEXT,
  `active`              TINYINT NOT NULL default 0,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB;


CREATE TABLE `contrexx_module_partners_assignable_label` (
  `id`                  INT NOT NULL auto_increment PRIMARY KEY,
  `label_placeholder`   VARCHAR(45) NOT NULL DEFAULT '',
  `multiple_assignable` boolean default 0,
  `datasource`          VARCHAR(45) NOT NULL DEFAULT '',
  `active`              TINYINT default 0
) ENGINE=InnoDB;

-- INSERT INTO contrexx_module_partners_assignable_label(label_placeholder, multiple_assignable) VALUES ('LEVEL',  0);
-- INSERT INTO contrexx_module_partners_assignable_label(label_placeholder, multiple_assignable) VALUES ('REGION', 1);

CREATE TABLE contrexx_module_partners_assignable_label_text (
	label_id    INT NOT NULL ,
	lang_id     INT NOT NULL,
	name        VARCHAR(45),
	name_m      VARCHAR(45),
	PRIMARY KEY (label_id, lang_id),
    FOREIGN KEY label_id(label_id) REFERENCES contrexx_module_partners_assignable_label(id) ON DELETE CASCADE
) ENGINE=innodb;

-- INSERT INTO contrexx_module_partners_assignable_label_text(label_id,lang_id,name,name_m) VALUES (1,1,'Level', 'Levels') ;
-- INSERT INTO contrexx_module_partners_assignable_label_text(label_id,lang_id,name,name_m) VALUES (1,2,'Level', 'Levels');
-- INSERT INTO contrexx_module_partners_assignable_label_text(label_id,lang_id,name,name_m) VALUES (2,1,'Region', 'Regionen');
-- INSERT INTO contrexx_module_partners_assignable_label_text(label_id,lang_id,name,name_m) VALUES (2,2,'Region', 'Regions');

CREATE TABLE contrexx_module_partners_label_entry (
	id                 INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
	label_id           INT NOT NULL,
	parent_entry_id    INT,
	default_partner    BOOLEAN NOT NULL DEFAULT 0,
	parse_custom_block VARCHAR(45),
    datasource_id      VARCHAR(45) NOT NULL DEFAULT '',
    FOREIGN KEY label_id(label_id) REFERENCES contrexx_module_partners_assignable_label(id) ON DELETE CASCADE,
    FOREIGN KEY parent_entry_id(parent_entry_id)  REFERENCES contrexx_module_partners_label_entry(id) ON DELETE CASCADE
) ENGINE=innodb;
CREATE INDEX labelentry_dsid ON contrexx_module_partners_label_entry(datasource_id);

CREATE TABLE contrexx_module_partners_label_entry_text (
    label_id INT NOT NULL,
    lang_id  INT NOT NULL,
    name     VARCHAR(45) NOT NULL,
    PRIMARY KEY (lang_id, label_id),
    FOREIGN KEY labelid(label_id) REFERENCES contrexx_module_partners_label_entry(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE contrexx_module_partners_to_labels (
    label_id   INT NOT NULL,
    partner_id INT NOT NULL,
    PRIMARY KEY (label_id, partner_id),
    FOREIGN KEY partner(partner_id) REFERENCES contrexx_module_partners            (id) ON DELETE CASCADE,
    FOREIGN KEY label  (label_id)   REFERENCES contrexx_module_partners_label_entry(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE contrexx_module_partners_settings (
    `key`    VARCHAR(60) NOT NULL PRIMARY KEY,
    `value`  TEXT NOT NULL
);

