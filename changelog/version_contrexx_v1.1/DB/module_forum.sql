INSERT INTO `contrexx_modules` VALUES (20, 'forum', 'TXT_FORUM_MODULE_DESCRIPTION', 'y', 0, 0);


INSERT INTO `contrexx_backend_areas` VALUES (106, 2, 'navigation', 'TXT_FORUM', 1, 'index.php?cmd=forum', '_self', 20, 19, 106);
INSERT INTO `contrexx_backend_areas` VALUES (107, 106, 'function', 'TXT_FORUM_MENU_CATEGORIES', 1, 'index.php?cmd=forum', '_self', 20, 1, 107);
INSERT INTO `contrexx_backend_areas` VALUES (108, 106, 'function', 'TXT_FORUM_MENU_SETTINGS', 1, 'index.php?cmd=forum&amp;act=settings', '_self', 20, 2, 108);


INSERT INTO `contrexx_access_user_groups` VALUES (4, 'Forum: Administratoren', 'Gruppe der Forenadministratoren', '1', 'frontend');
INSERT INTO `contrexx_access_user_groups` VALUES (5, 'Forum: Benutzer', 'Gruppe der Forenbenutzer', '1', 'frontend');


CREATE TABLE `contrexx_module_forum_settings` (`id` SMALLINT UNSIGNED NOT NULL PRIMARY KEY,`name` VARCHAR(50) NOT NULL,`value` TEXT NOT NULL) TYPE = MYISAM;
INSERT INTO `contrexx_module_forum_settings` VALUES(1,'thread_paging',20);
INSERT INTO `contrexx_module_forum_settings` VALUES(2,'posting_paging',20);
INSERT INTO `contrexx_module_forum_settings` VALUES (3, 'latest_entries_count', '5');
INSERT INTO `contrexx_module_forum_settings` VALUES (4, 'block_template', '<div id="forum">    \r\n         <div class="div_board">\r\n	         <div class="div_title">[[TXT_FORUM_LATEST_ENTRIES]]</div>\r\n		<table cellspacing="0" cellpadding="0">\r\n			<tr class="row3">\r\n				<th width="65%" style="text-align: left;">[[TXT_FORUM_THREAD]]</th>\r\n				<th width="15%" style="text-align: left;">[[TXT_FORUM_OVERVIEW_FORUM]]</th>		\r\n				<th width="15%" style="text-align: left;">[[TXT_FORUM_THREAD_STRATER]]</th>		\r\n				<th width="1%" style="text-align: left;">[[TXT_FORUM_POST_COUNT]]</th>		\r\n				<th width="4%" style="text-align: left;">[[TXT_FORUM_THREAD_CREATE_DATE]]</th>\r\n			</tr>\r\n			<!-- BEGIN latestPosts -->\r\n			<tr class="row_[[FORUM_ROWCLASS]]">\r\n				<td>[[FORUM_THREAD]]</td>\r\n				<td>[[FORUM_FORUM_NAME]]</td>\r\n				<td>[[FORUM_THREAD_STARTER]]</td>\r\n				<td>[[FORUM_POST_COUNT]]</td>\r\n				<td>[[FORUM_THREAD_CREATE_DATE]]</td>\r\n			</tr>	\r\n			<!-- END latestPosts -->	\r\n		</table>\r\n	</div>\r\n</div>');
INSERT INTO `contrexx_module_forum_settings` VALUES (5, 'notification_template', '[[USERNAME]],\r\n\r\nEs wurde ein neuer Beitrag im Thema \\"[[THREAD_SUBJECT]]\\", gestartet \r\nvon \\"[[THREAD_STARTER]]\\", geschrieben.\r\n\r\nDer neue Beitrag umfasst folgenden Inhalt:\r\n\r\n-----------------NACHRICHT START-----------------\r\n-----Betreff-----\r\n[[LATEST_SUBJECT]]\r\n\r\n----Nachricht----\r\n[[LATEST_MESSAGE]]\r\n-----------------NACHRICHT ENDE------------------\r\n\r\nUm den ganzen Diskussionsverlauf zu sehen oder zur Abmeldung dieser \r\nBenachrichtigung, besuche folgenden Link:\r\n[[THREAD_URL]]\r\n\r\n\r\n\r\n\r\n\r\n\r\n');
INSERT INTO `contrexx_module_forum_settings` VALUES (6, 'notification_subject', 'Neuer Beitrag in \\"[[THREAD_SUBJECT]]\\"');
INSERT INTO `contrexx_module_forum_settings` VALUES (7, 'notification_from_email', 'noreply@example.com');
INSERT INTO `contrexx_module_forum_settings` VALUES (8, 'notification_from_name', 'nobody');


CREATE TABLE `contrexx_module_forum_categories` (`id` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,`parent_id` SMALLINT UNSIGNED NOT NULL DEFAULT '0',`order_id` SMALLINT UNSIGNED NOT NULL DEFAULT '0',`status` SET( '0', '1' ) NOT NULL DEFAULT '0',INDEX ( `parent_id` )) TYPE = MYISAM ;
INSERT INTO `contrexx_module_forum_categories` VALUES (1, 0, 1, '1');
INSERT INTO `contrexx_module_forum_categories` VALUES (2, 1, 1, '1');



CREATE TABLE `contrexx_module_forum_categories_lang` ( `category_id` SMALLINT UNSIGNED NOT NULL ,`lang_id` SMALLINT UNSIGNED NOT NULL ,`name` VARCHAR( 100 ) NOT NULL ,`description` TEXT NOT NULL ,PRIMARY KEY ( `category_id` , `lang_id` )) TYPE = MYISAM ;
INSERT INTO `contrexx_module_forum_categories_lang` VALUES (1, 1, 'Beispielskategorie', 'Diese Kategorie soll als Beispiel dienen.');
INSERT INTO `contrexx_module_forum_categories_lang` VALUES (1, 2, 'Example: category', 'This category is just an example.');
INSERT INTO `contrexx_module_forum_categories_lang` VALUES (2, 1, 'Beispielsforum', 'Dieses Forum soll Ihnen die Fähigkeiten des neuen Forenmoduls demonstrieren.');
INSERT INTO `contrexx_module_forum_categories_lang` VALUES (2, 2, 'Example: forum', 'This forum should demonstrate you the possibilities of the new forum-module.');



CREATE TABLE `contrexx_module_forum_statistics` (`category_id` SMALLINT UNSIGNED NOT NULL PRIMARY KEY ,`thread_count` INTEGER UNSIGNED NOT NULL,`post_count` INTEGER UNSIGNED NOT NULL ,`last_post_id` INTEGER UNSIGNED NOT NULL) TYPE = MYISAM ;INSERT INTO `contrexx_module_forum_statistics` VALUES (2,1,1,1);



CREATE TABLE `contrexx_module_forum_access` (`category_id` SMALLINT UNSIGNED NOT NULL ,`group_id` SMALLINT UNSIGNED NOT NULL ,`read` SET( '0', '1' ) NOT NULL DEFAULT '0',`write` SET( '0', '1' ) NOT NULL DEFAULT '0',`edit` SET( '0', '1' ) NOT NULL DEFAULT '0',`delete` SET( '0', '1' ) NOT NULL DEFAULT '0',`move` SET( '0', '1' ) NOT NULL DEFAULT '0',`close` SET( '0', '1' ) NOT NULL DEFAULT '0',`sticky` SET( '0', '1' ) NOT NULL DEFAULT '0',PRIMARY KEY ( `category_id` , `group_id` )) TYPE = MYISAM ;
INSERT INTO `contrexx_module_forum_access` VALUES(1, 4, '1', '1', '1', '1', '1', '1', '1');
INSERT INTO `contrexx_module_forum_access` VALUES(2, 4, '1', '1', '1', '1', '1', '1', '1');
INSERT INTO `contrexx_module_forum_access` VALUES(1, 5, '1', '1', '0', '0', '0', '0', '0');
INSERT INTO `contrexx_module_forum_access` VALUES(2, 5, '1', '1', '0', '0', '0', '0', '0');



CREATE TABLE `contrexx_module_forum_postings` (`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,`category_id` SMALLINT UNSIGNED NOT NULL,`thread_id` INTEGER UNSIGNED NOT NULL DEFAULT '0',`prev_post_id` INTEGER UNSIGNED NOT NULL DEFAULT '0',`user_id` SMALLINT UNSIGNED NOT NULL,`time_created` INTEGER(14) UNSIGNED NOT NULL,`time_edited` INTEGER(14) UNSIGNED NOT NULL,`is_locked` SET('0','1') NOT NULL DEFAULT '0',`is_sticky` SET('0','1') NOT NULL DEFAULT '0',`views` INTEGER UNSIGNED NOT NULL DEFAULT '0',`icon` SMALLINT UNSIGNED NOT NULL DEFAULT '0',`subject` VARCHAR(250) NOT NULL,`content` TEXT NOT NULL,INDEX(`category_id`,`thread_id`,`prev_post_id`,`user_id`),FULLTEXT KEY `fulltext` (`subject`, `content`)) TYPE = MYISAM;
INSERT INTO `contrexx_module_forum_postings` VALUES ( 1, 2, 0, 0, 1, 1155045563, 0, '0', '0', 0, 2, 'Das neue Forenmodul', 'Sehr geehrter Contrexx-User, Wir freuen uns, Ihnen das neue Foren-Modul präsentieren zu dürfen. Mit freundlichen Gr&uuml;ssen Ihr Contrexx-Team');


CREATE TABLE `contrexx_module_forum_notification` (`thread_id` INTEGER UNSIGNED NOT NULL,`user_id` SMALLINT UNSIGNED NOT NULL,`is_notified` SET('0','1') NOT NULL DEFAULT '0',PRIMARY KEY (`thread_id`, `user_id`)) TYPE = MYISAM;


--- post 1.0.9.10.1
ALTER TABLE `contrexx_module_forum_notification` ADD `category_id` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' FIRST ;
ALTER TABLE `contrexx_module_forum_notification` DROP PRIMARY KEY 
ALTER TABLE `contrexx_module_forum_notification` ADD PRIMARY KEY ( `category_id` , `thread_id` , `user_id` );