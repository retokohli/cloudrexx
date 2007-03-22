INSERT INTO `contrexx_modules` VALUES (40, 'survey', 'TXT_SURVEY_MODULE_DESCRIPTION', 'y', 0, 0);

INSERT INTO `contrexx_backend_areas` VALUES (111, 2, 'navigation', 'TXT_SURVEY', 1, 'index.php?cmd=survey', '_self', 40, 21, 111);
INSERT INTO `contrexx_backend_areas` VALUES (112, 111, 'function', 'TXT_SURVEY_MENU_ADD', 1, 'index.php?cmd=survey&amp;act=add', '_self', 40, 1, 112);
INSERT INTO `contrexx_backend_areas` VALUES (113, 111, 'function', 'TXT_SURVEY_MENU_SETTINGS', 1, 'index.php?cmd=survey&amp;act=settings', '_self', 40, 2, 113);
INSERT INTO `contrexx_backend_areas` VALUES (114, 111, 'function', 'TXT_SURVEY_DELETE', 1, 'index.php?cmd=survey&amp;act=delete', '_self', 40, 2, 114);

CREATE TABLE `contrexx_module_survey_groups` (
`id` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
`redirect` TINYTEXT NOT NULL,
`created` INTEGER(14) UNSIGNED NOT NULL,
`lastvote` INTEGER(14) UNSIGNED NOT NULL,
`participant` SMALLINT UNSIGNED NOT NULL DEFAULT '0',
`isActive` SET( '0', '1' ) NOT NULL DEFAULT '0',
`isExtended` SET( '0', '1' ) NOT NULL DEFAULT '0',
`isCommentable` SET( '0', '1' ) NOT NULL DEFAULT '0',
`isHomeBox` SET( '0', '1' ) NOT NULL DEFAULT '0',
PRIMARY KEY ( `id` ),
INDEX ( `isActive`, `isExtended`, `isHomeBox` )
) ENGINE = MYISAM ;


CREATE TABLE `contrexx_module_survey_groups_lang` (
`group_id` SMALLINT UNSIGNED NOT NULL,
`lang_id` SMALLINT UNSIGNED NOT NULL,
`subject` TINYTEXT NOT NULL,
PRIMARY KEY (`group_id`, `lang_id`)
) ENGINE = MYISAM;


CREATE TABLE `contrexx_module_survey_questions` (
`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
`group_id` SMALLINT UNSIGNED NOT NULL,
`sorting` SMALLINT UNSIGNED NOT NULL DEFAULT '0',
PRIMARY KEY ( `id` ),
INDEX ( `group_id`, `sorting` )
) ENGINE = MYISAM ;


CREATE TABLE `contrexx_module_survey_questions_lang` (
`question_id` SMALLINT UNSIGNED NOT NULL,
`lang_id` SMALLINT UNSIGNED NOT NULL,
`question` TEXT NOT NULL,
PRIMARY KEY (`question_id`, `lang_id`),
FULLTEXT KEY `fulltext` (`question`) 
) ENGINE = MYISAM ;


CREATE TABLE `contrexx_module_survey_answers` (
`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
`question_id` INTEGER UNSIGNED NOT NULL,
`sorting` SMALLINT UNSIGNED NOT NULL DEFAULT '0',
`votes` SMALLINT UNSIGNED NOT NULL DEFAULT '0',
PRIMARY KEY ( `id` ),
INDEX ( `question_id`, `votes` )
) ENGINE = MYISAM;


CREATE TABLE `contrexx_module_survey_answers_lang` (
`answer_id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
`lang_id` SMALLINT UNSIGNED NOT NULL,
`answer` TEXT NOT NULL,
PRIMARY KEY (`answer_id`, `lang_id`),
FULLTEXT KEY `fulltext` (`answer`) 
) ENGINE = MYISAM;


CREATE TABLE `contrexx_module_survey_votes` ( 
`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
`answer_id` INTEGER UNSIGNED NOT NULL,
`votetime` INTEGER(14) UNSIGNED NOT NULL,
`ip` VARCHAR(15) NOT NULL,
PRIMARY KEY ( `id` ),
INDEX ( `answer_id` )
) ENGINE = MYISAM;


CREATE TABLE `contrexx_module_survey_comments` (
`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
`group_id` SMALLINT UNSIGNED NOT NULL,
`time` INTEGER(14) UNSIGNED NOT NULL,
`ip` VARCHAR(15) NOT NULL,
`name` VARCHAR(100) NOT NULL DEFAULT '',
`email` TINYTEXT NOT NULL,
`comment` TEXT NOT NULL,
PRIMARY KEY ( `id` ),
INDEX ( `group_id` ),
FULLTEXT KEY `fulltext` (`comment`) 
) ENGINE = MYISAM;

CREATE TABLE `contrexx_module_survey_settings` (
`id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
`name` VARCHAR(50) NOT NULL,
`value` TEXT NOT NULL,
PRIMARY KEY ( `id` )
) ENGINE = MYISAM;
INSERT INTO `contrexx_module_survey_settings` VALUES(1,'logVotes',1);
INSERT INTO `contrexx_module_survey_settings` VALUES(2,'allowAnonymous',1);