<?php
function _forumUpdate()
{
    global $objDatabase, $_ARRAYLANG;

    $arrSettings = array(
                        '9' => array(
                            'name' 	=> 'banned_words',
                            'value' => 'penis enlargement,free porn,(?i:buy\\\\s*?(?:cheap\\\\s*?)?viagra)'),
                        '10' => array(
                            'name' 	=> 'wysiwyg_editor',
                            'value' => '1'),
                        '11' => array(
                            'name' 	=> 'tag_count',
                            'value' => '10'),
                        '12' => array(
                            'name' 	=> 'latest_post_per_thread',
                            'value' => '1'),
                        '13' => array(
                            'name' 	=> 'allowed_extensions',
                            'value' => '7z,aiff,asf,avi,bmp,csv,doc,fla,flv,gif,gz,gzip, jpeg,jpg,mid,mov,mp3,mp4,mpc,mpeg,mpg,ods,odt,pdf, png,ppt,pxd,qt,ram,rar,rm,rmi,rmvb,rtf,sdc,sitd,swf, sxc,sxw,tar,tgz,tif,tiff,txt,vsd,wav,wma,wmv,xls,xml ,zip')
                        );

    $arrTables = $objDatabase->MetaTables();
    $arrColumns = $objDatabase->MetaColumnNames(DBPREFIX."module_forum_postings");
    if(!in_array('rating', $arrColumns)){
        $query = "ALTER TABLE `".DBPREFIX."module_forum_postings` ADD `rating` INT NOT NULL DEFAULT '0' AFTER `is_sticky`";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }


    if(!in_array('keywords', $arrColumns)){
        $query = "ALTER TABLE `".DBPREFIX."module_forum_postings` ADD `keywords` TEXT NOT NULL AFTER `icon`" ;
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }
    $arrIndexes = $objDatabase->MetaIndexes(DBPREFIX."module_forum_postings");

    if(is_array($arrIndexes['fulltext'])){
        $query = "ALTER TABLE `".DBPREFIX."module_forum_postings` DROP INDEX `fulltext`";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }
    $query = "ALTER TABLE `".DBPREFIX."module_forum_postings` ADD FULLTEXT `fulltext` (
                `keywords`,
                `subject`,
                `content`
                );" ;
    if ($objDatabase->Execute($query) === false) {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }


    if(!in_array('attachment', $arrColumns)){
        $query = "ALTER TABLE `".DBPREFIX."module_forum_postings` ADD `attachment` VARCHAR(250) NOT NULL DEFAULT '' AFTER `content`" ;
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    foreach ($arrSettings as $id => $arrSetting) {
        $query = "SELECT 1 FROM `".DBPREFIX."module_forum_settings` WHERE `name`= '".$arrSetting['name']."'" ;
        if (($objRS = $objDatabase->Execute($query)) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
        if($objRS->RecordCount() == 0){
            $query = "INSERT INTO `".DBPREFIX."module_forum_settings`
                             (`id`, `name`, `value`)
                      VALUES (".$id.", '".$arrSetting['name']."', '".addslashes($arrSetting['value'])."')" ;
            if ($objDatabase->Execute($query) === false) {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
    }


    try {
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_forum_rating',
            array(
                'id'         => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'user_id'    => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0'),
                'post_id'    => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0'),
                'time'       => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0')
            ),
            array(
                'user_id'    => array('fields' => array('user_id','post_id'))
            )
        );
    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }


    return true;
}



function _forumInstall()
{
    try {

        /**************************************************************************
         * EXTENSION:   Initial creation (for contrexx editions <> premium with   *
         *              version < 3.0.0 which have this module not yet installed) *
         *                                                                        *
         * ADDED:       Contrexx v3.0.1                                           *
         **************************************************************************/
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_forum_access',
            array(
                'category_id'    => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
                'group_id'       => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true, 'after' => 'category_id'),
                'read'           => array('type' => 'SET(\'0\',\'1\')', 'notnull' => true, 'default' => '0', 'after' => 'group_id'),
                'write'          => array('type' => 'SET(\'0\',\'1\')', 'notnull' => true, 'default' => '0', 'after' => 'read'),
                'edit'           => array('type' => 'SET(\'0\',\'1\')', 'notnull' => true, 'default' => '0', 'after' => 'write'),
                'delete'         => array('type' => 'SET(\'0\',\'1\')', 'notnull' => true, 'default' => '0', 'after' => 'edit'),
                'move'           => array('type' => 'SET(\'0\',\'1\')', 'notnull' => true, 'default' => '0', 'after' => 'delete'),
                'close'          => array('type' => 'SET(\'0\',\'1\')', 'notnull' => true, 'default' => '0', 'after' => 'move'),
                'sticky'         => array('type' => 'SET(\'0\',\'1\')', 'notnull' => true, 'default' => '0', 'after' => 'close')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_forum_access` (`category_id`, `group_id`, `read`, `write`, `edit`, `delete`, `move`, `close`, `sticky`)
            VALUES  (1, 0, '1', '0', '0', '0', '0', '0', '0'),
                    (1, 3, '1', '0', '0', '0', '0', '0', '0'),
                    (1, 4, '1', '1', '1', '1', '0', '0', '0'),
                    (1, 5, '1', '1', '0', '0', '0', '0', '0'),
                    (6, 0, '1', '0', '0', '0', '0', '0', '0'),
                    (6, 3, '1', '0', '0', '0', '0', '0', '0'),
                    (6, 4, '1', '1', '1', '1', '0', '0', '0'),
                    (6, 5, '1', '1', '0', '0', '0', '0', '0'),
                    (9, 0, '0', '0', '0', '0', '0', '0', '0'),
                    (9, 3, '0', '0', '0', '0', '0', '0', '0'),
                    (9, 4, '0', '0', '0', '0', '0', '0', '0'),
                    (9, 5, '0', '0', '0', '0', '0', '0', '0')
            ON DUPLICATE KEY UPDATE `category_id` = `category_id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_forum_categories',
            array(
                'id'             => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'parent_id'      => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'id'),
                'order_id'       => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'parent_id'),
                'status'         => array('type' => 'SET(\'0\',\'1\')', 'notnull' => true, 'default' => '0', 'after' => 'order_id')
            ),
            array(
                'parent_id'      => array('fields' => array('parent_id'))
            ),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_forum_categories` (`id`, `parent_id`, `order_id`, `status`)
            VALUES  (1, 0, 2, '1'),
                    (6, 1, 99, '1'),
                    (8, 0, 99, '1'),
                    (9, 8, 99, '1')
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_forum_categories_lang',
            array(
                'category_id'    => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
                'lang_id'        => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true, 'after' => 'category_id'),
                'name'           => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => '', 'after' => 'lang_id'),
                'description'    => array('type' => 'text', 'after' => 'name')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_forum_categories_lang` (`category_id`, `lang_id`, `name`, `description`)
            VALUES  (1, 1, 'Beispielskategorie', 'Diese Kategorie soll als Beispiel dienen.'),
                    (1, 2, 'Beispielskategorie', 'Sample Category'),
                    (1, 3, 'Beispielskategorie', 'Diese Kategorie soll als Beispiel dienen.'),
                    (6, 1, 'Forenregeln', 'Beispielforum'),
                    (8, 2, 'Rules', 'Rules'),
                    (8, 3, 'Rules', 'Rules'),
                    (9, 2, 'Readme - Sample Rules', 'here you should read the sample rules of this board before starting topics'),
                    (9, 3, 'Readme - Sample Rules', 'here you should read the sample rules of this board before starting topics')
            ON DUPLICATE KEY UPDATE `category_id` = `category_id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_forum_notification',
            array(
                'category_id'    => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
                'thread_id'      => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true, 'after' => 'category_id'),
                'user_id'        => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true, 'after' => 'thread_id'),
                'is_notified'    => array('type' => 'SET(\'0\',\'1\')', 'notnull' => true, 'default' => '0', 'after' => 'user_id')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_forum_postings',
            array(
                'id'                 => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'category_id'        => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'id'),
                'thread_id'          => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'category_id'),
                'prev_post_id'       => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'thread_id'),
                'user_id'            => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'prev_post_id'),
                'time_created'       => array('type' => 'INT(14)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'user_id'),
                'time_edited'        => array('type' => 'INT(14)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'time_created'),
                'is_locked'          => array('type' => 'SET(\'0\',\'1\')', 'notnull' => true, 'default' => '0', 'after' => 'time_edited'),
                'is_sticky'          => array('type' => 'SET(\'0\',\'1\')', 'notnull' => true, 'default' => '0', 'after' => 'is_locked'),
                'rating'             => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'after' => 'is_sticky'),
                'views'              => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'rating'),
                'icon'               => array('type' => 'SMALLINT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'views'),
                'keywords'           => array('type' => 'text', 'after' => 'icon'),
                'subject'            => array('type' => 'VARCHAR(250)', 'notnull' => true, 'default' => '', 'after' => 'keywords'),
                'content'            => array('type' => 'text', 'after' => 'subject'),
                'attachment'         => array('type' => 'VARCHAR(250)', 'notnull' => true, 'default' => '', 'after' => 'content')
            ),
            array(
                'category_id'        => array('fields' => array('category_id','thread_id','prev_post_id','user_id')),
                'fulltext'           => array('fields' => array('keywords','subject','content'), 'type' => 'FULLTEXT')
            ),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_forum_postings` (`id`, `category_id`, `thread_id`, `prev_post_id`, `user_id`, `time_created`, `time_edited`, `is_locked`, `is_sticky`, `rating`, `views`, `icon`, `keywords`, `subject`, `content`, `attachment`)
            VALUES  (4, 6, 1, 0, 1, 1292234985, 1292235345, '', '', 0, 15, 1, 'foren regeln, regeln,', 'Forenregeln', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.\r\n', ''),
                    (5, 6, 1, 4, 1, 1292947019, 0, '', '', 0, 1, 1, 'board rules', 'Board Rules', 'here you should read the sample rules of this board.\r\n', '')
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_forum_rating',
            array(
                'id'         => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'user_id'    => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'after' => 'id'),
                'post_id'    => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'after' => 'user_id'),
                'time'       => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'after' => 'post_id')
            ),
            array(
                'user_id'    => array('fields' => array('user_id','post_id'))
            ),
            'MyISAM',
            'cx3upgrade'
        );

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_forum_settings',
            array(
                'id'         => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'name'       => array('type' => 'VARCHAR(50)', 'notnull' => true, 'default' => '', 'after' => 'id'),
                'value'      => array('type' => 'text', 'after' => 'name')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_forum_settings` (`id`, `name`, `value`)
            VALUES  (1, 'thread_paging', '1'),
                    (2, 'posting_paging', '1'),
                    (3, 'latest_entries_count', '1'),
                    (4, 'block_template', '<div id=\"forum\">    \r\n         <div class=\"div_board\">\r\n	         <div class=\"div_title\">[[TXT_FORUM_LATEST_ENTRIES]]</div>\r\n		<table cellspacing=\"0\" cellpadding=\"0\">\r\n			<tr class=\"row3\">\r\n				<th width=\"65%\" style=\"text-align: left;\">[[TXT_FORUM_THREAD]]</th>\r\n				<th width=\"15%\" style=\"text-align: left;\">[[TXT_FORUM_OVERVIEW_FORUM]]</th>		\r\n				<th width=\"15%\" style=\"text-align: left;\">[[TXT_FORUM_THREAD_STRATER]]</th>		\r\n				<th width=\"1%\" style=\"text-align: left;\">[[TXT_FORUM_POST_COUNT]]</th>		\r\n				<th width=\"4%\" style=\"text-align: left;\">[[TXT_FORUM_THREAD_CREATE_DATE]]</th>\r\n			</tr>\r\n			<!-- BEGIN latestPosts -->\r\n			<tr class=\"row_[[FORUM_ROWCLASS]]\">\r\n				<td>[[FORUM_THREAD]]</td>\r\n				<td>[[FORUM_FORUM_NAME]]</td>\r\n				<td>[[FORUM_THREAD_STARTER]]</td>\r\n				<td>[[FORUM_POST_COUNT]]</td>\r\n				<td>[[FORUM_THREAD_CREATE_DATE]]</td>\r\n			</tr>	\r\n			<!-- END latestPosts -->	\r\n		</table>\r\n	</div>\r\n</div>'),
                    (5, 'notification_template', '[[FORUM_USERNAME]],\r\n\r\nEs wurde ein neuer Beitrag im Thema \"[[FORUM_THREAD_SUBJECT]]\", gestartet \r\nvon \"[[FORUM_THREAD_STARTER]]\", geschrieben.\r\n\r\nDer neue Beitrag umfasst folgenden Inhalt:\r\n\r\n-----------------NACHRICHT START-----------------\r\n-----Betreff-----\r\n[[FORUM_LATEST_SUBJECT]]\r\n\r\n----Nachricht----\r\n[[FORUM_LATEST_MESSAGE]]\r\n-----------------NACHRICHT ENDE------------------\r\n\r\nUm den ganzen Diskussionsverlauf zu sehen oder zur Abmeldung dieser \r\nBenachrichtigung, besuchen Sie folgenden Link:\r\n[[FORUM_THREAD_URL]]\r\n'),
                    (6, 'notification_subject', 'Neuer Beitrag in \"[[FORUM_THREAD_SUBJECT]]\"'),
                    (7, 'notification_from_email', 'noreply@example.com'),
                    (8, 'notification_from_name', 'nobody'),
                    (9, 'banned_words', 'penis enlargement,free porn,(?i:buy\\\\s*?(?:cheap\\\\s*?)?viagra)'),
                    (10, 'wysiwyg_editor', '1'),
                    (11, 'tag_count', '1'),
                    (12, 'latest_post_per_thread', '0'),
                    (13, 'allowed_extensions', '7z,aiff,asf,avi,bmp,csv,doc,fla,flv,gif,gz,gzip,jpeg,jpg,mid,mov,mp3,mp4,mpc,mpeg,mpg,ods,odt,pdf,png,ppt,pxd,qt,ram,rar,rm,rmi,rmvb,rtf,sdc,sitd,swf,sxc,sxw,tar,tgz,tif,tiff,txt,vsd,wav,wma,wmv,xls,xml,zip')
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_forum_statistics',
            array(
                'category_id'        => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
                'thread_count'       => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'category_id'),
                'post_count'         => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'thread_count'),
                'last_post_id'       => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'post_count')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_forum_statistics` (`category_id`, `thread_count`, `post_count`, `last_post_id`)
            VALUES  (6, 1, 2, 5),
                    (9, 0, 0, 0)
            ON DUPLICATE KEY UPDATE `category_id` = `category_id`
        ");

    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }
}
