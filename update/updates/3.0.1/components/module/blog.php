<?php

function _blogUpdate() {
    global $objDatabase, $_ARRAYLANG, $_CORELANG, $objUpdate, $_CONFIG;

    /*
     * Check for missing setting "blog_comments_editor" in database. In the update-package for 1.2 this value somehow
     * got lost.
     */
    $query = '	SELECT 	name
				FROM	`' . DBPREFIX . 'module_blog_settings`
				WHERE	name="blog_comments_editor"
				LIMIT	1';

    $objResult = $objDatabase->Execute($query);

    if ($objResult !== false) {
        if ($objResult->RecordCount() == 0) {
            $query = "INSERT INTO `" . DBPREFIX . "module_blog_settings` ( `name` , `value` ) VALUES ('blog_comments_editor', 'wysiwyg')";

            if ($objDatabase->Execute($query) === false) {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
    } else {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }

    try {
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX . 'module_blog_categories', array(
                'category_id' => array('type' => 'INT(4)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
                'lang_id' => array('type' => 'INT(2)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
                'is_active' => array('type' => 'ENUM(\'0\',\'1\')', 'notnull' => true, 'default' => '1'),
                'name' => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => '')
            )
        );

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX . 'module_blog_comments', array(
                'comment_id' => array('type' => 'INT(7)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'message_id' => array('type' => 'INT(6)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'lang_id' => array('type' => 'INT(2)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'is_active' => array('type' => 'ENUM(\'0\',\'1\')', 'notnull' => true, 'default' => '1'),
                'time_created' => array('type' => 'INT(14)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'ip_address' => array('type' => 'VARCHAR(15)', 'notnull' => true, 'default' => '0.0.0.0'),
                'user_id' => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'user_name' => array('type' => 'VARCHAR(50)', 'notnull' => false),
                'user_mail' => array('type' => 'VARCHAR(250)', 'notnull' => false),
                'user_www' => array('type' => 'VARCHAR(255)', 'notnull' => false),
                'subject' => array('type' => 'VARCHAR(250)', 'notnull' => true, 'default' => ''),
                'comment' => array('type' => 'TEXT')
            ), array(
                'message_id' => array('fields' => array('message_id'))
            )
        );

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX . 'module_blog_message_to_category', array(
                'message_id' => array('type' => 'INT(6)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
                'category_id' => array('type' => 'INT(4)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
                'lang_id' => array('type' => 'INT(2)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true)
            ), array(
                'category_id' => array('fields' => array('category_id'))
            )
        );

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX . 'module_blog_messages', array(
                'message_id' => array('type' => 'INT(6)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'user_id' => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'time_created' => array('type' => 'INT(14)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'time_edited' => array('type' => 'INT(14)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'hits' => array('type' => 'INT(7)', 'unsigned' => true, 'notnull' => true, 'default' => '0')
            )
        );

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX . 'module_blog_networks_lang', array(
                'network_id' => array('type' => 'INT(8)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
                'lang_id' => array('type' => 'INT(2)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true)
            )
        );

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX . 'module_blog_votes', array(
                'vote_id' => array('type' => 'INT(8)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'message_id' => array('type' => 'INT(6)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'time_voted' => array('type' => 'INT(14)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'ip_address' => array('type' => 'VARCHAR(15)', 'notnull' => true, 'default' => '0.0.0.0'),
                'vote' => array('type' => 'ENUM(\'1\',\'2\',\'3\',\'4\',\'5\',\'6\',\'7\',\'8\',\'9\',\'10\')', 'notnull' => true, 'default' => '1')
            ), array(
                'message_id' => array('fields' => array('message_id'))
            )
        );
    } catch (\Cx\Lib\UpdateException $e) {
        // we COULD do something else here..
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }

    try { //update to 2.2.3 in this block
        if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '2.2.3')) {
            //we've hidden the wysiwyg - let's default to textarea
            \Cx\Lib\UpdateUtil::sql('UPDATE ' . DBPREFIX . 'module_blog_settings SET value="textarea" WHERE name="blog_comments_editor"');

            //comments: convert escaped db entries to their unescaped equivalents
            $rs = \Cx\Lib\UpdateUtil::sql('SELECT comment_id, comment FROM  ' . DBPREFIX . 'module_blog_comments');
            while (!$rs->EOF) {
                $content = $rs->fields['comment'];
                $id = $rs->fields['comment_id'];
                $content = contrexx_raw2db(html_entity_decode($content, ENT_QUOTES, CONTREXX_CHARSET));
                \Cx\Lib\UpdateUtil::sql('UPDATE ' . DBPREFIX . 'module_blog_comments SET comment="' . $content . '" WHERE comment_id=' . $id);
                $rs->MoveNext();
            }
        }
    } catch (\Cx\Lib\UpdateException $e) {
        // we COULD do something else here..
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }


    try {
        // migrate content page to version 3.0.1
        $search = array(
        '/(.*)/ms',
        );
        $callback = function($matches) {
            $content = $matches[1];
            if (empty($content)) {
                return $content;
            }

            // replace placeholder {TXT_COMMENT_ADD_SPAM} with {TXT_COMMENT_CAPTCHA}
            $content = str_replace('{TXT_COMMENT_ADD_SPAM}', '{TXT_COMMENT_CAPTCHA}', $content);

            // replace <img src="[[BLOG_DETAILS_COMMENT_ADD_SPAM_URL]]" alt="[[BLOG_DETAILS_COMMENT_ADD_SPAM_ALT]]" title="[[BLOG_DETAILS_COMMENT_ADD_SPAM_ALT]]" /> with {COMMENT_CAPTCHA_CODE}
            $content = preg_replace('/<img[^>]+\{BLOG_DETAILS_COMMENT_ADD_SPAM_URL\}[^>]+>/ms', '{COMMENT_CAPTCHA_CODE}', $content);

            // remove <input type="text" name="frmAddComment_Captcha" />
            $content = preg_replace('/<input[^>]+name\s*=\s*[\'"]frmAddComment_Captcha[^>]+>/ms', '', $content);

            // remove <input type="hidden" name="frmAddComment_Offset" value="[[BLOG_DETAILS_COMMENT_ADD_SPAM_OFFSET]]" />
            $content = preg_replace('/<(div|p)[^>]*>\s*<input[^>]+name\s*=\s*[\'"]frmAddComment_Offset[^>]+>\s*<\/(div|p)>/ms', '', $content);

            // add missing comment_captcha template block
            if (!preg_match('/<!--\s+BEGIN\s+comment_captcha\s+-->.*<!--\s+END\s+comment_captcha\s+-->/ms', $content)) {
                $content = preg_replace('/(.*)(<(div|p)[^{]*?>.*?\{TXT_COMMENT_CAPTCHA\}.*?\{COMMENT_CAPTCHA_CODE\}.*?<\/\3>)/ms', '$1<!-- BEGIN comment_captcha -->$2<!-- END comment_captcha -->', $content, -1, $count);
                if (!$count) {
                    $content = preg_replace('/(.*)(<(div|p)[^{]*?>.*?\{COMMENT_CAPTCHA_CODE\}.*?<\/\3>)/ms', '$1<!-- BEGIN comment_captcha -->$2<!-- END comment_captcha -->', $content, -1, $count);
                }
            }

            return $content;
        };

        \Cx\Lib\UpdateUtil::migrateContentPageUsingRegexCallback(array('module' => 'blog', 'cmd' => 'details'), $search, $callback, array('content'), '3.0.1');
    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }


    /**
     * Everything went fine. Return without any errors.
     */
    return true;
}



function _blogInstall()
{
    try {

        /**************************************************************************
         * EXTENSION:   Initial creation (for contrexx editions <> premium with   *
         *              version < 3.0.0 which have this module not yet installed) *
         *                                                                        *
         * ADDED:       Contrexx v3.0.1                                           *
         **************************************************************************/
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_blog_categories',
            array(
                'category_id'    => array('type' => 'INT(4)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
                'lang_id'        => array('type' => 'INT(2)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true, 'after' => 'category_id'),
                'is_active'      => array('type' => 'ENUM(\'0\',\'1\')', 'notnull' => true, 'default' => '1', 'after' => 'lang_id'),
                'name'           => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => '', 'after' => 'is_active')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_blog_categories` (`category_id`, `lang_id`, `is_active`, `name`)
            VALUES  (1, 1, '1', 'Allgemein'),
                    (1, 2, '1', 'General'),
                    (1, 3, '1', 'General'),
                    (1, 4, '1', 'General'),
                    (1, 5, '1', 'General'),
                    (1, 6, '1', 'General')
            ON DUPLICATE KEY UPDATE `category_id` = `category_id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_blog_comments',
            array(
                'comment_id'         => array('type' => 'INT(7)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'message_id'         => array('type' => 'INT(6)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'comment_id'),
                'lang_id'            => array('type' => 'INT(2)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'message_id'),
                'is_active'          => array('type' => 'ENUM(\'0\',\'1\')', 'notnull' => true, 'default' => '1', 'after' => 'lang_id'),
                'time_created'       => array('type' => 'INT(14)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'is_active'),
                'ip_address'         => array('type' => 'VARCHAR(15)', 'notnull' => true, 'default' => '0.0.0.0', 'after' => 'time_created'),
                'user_id'            => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'ip_address'),
                'user_name'          => array('type' => 'VARCHAR(50)', 'notnull' => false, 'after' => 'user_id'),
                'user_mail'          => array('type' => 'VARCHAR(250)', 'notnull' => false, 'after' => 'user_name'),
                'user_www'           => array('type' => 'VARCHAR(255)', 'notnull' => false, 'after' => 'user_mail'),
                'subject'            => array('type' => 'VARCHAR(250)', 'notnull' => true, 'default' => '', 'after' => 'user_www'),
                'comment'            => array('type' => 'text', 'after' => 'subject')
            ),
            array(
                'message_id'         => array('fields' => array('message_id'))
            ),
            'MyISAM',
            'cx3upgrade'
        );

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_blog_message_to_category',
            array(
                'message_id'     => array('type' => 'INT(6)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
                'category_id'    => array('type' => 'INT(4)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true, 'after' => 'message_id'),
                'lang_id'        => array('type' => 'INT(2)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true, 'after' => 'category_id')
            ),
            array(
                'category_id'    => array('fields' => array('category_id'))
            ),
            'MyISAM',
            'cx3upgrade'
        );

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_blog_messages',
            array(
                'message_id'         => array('type' => 'INT(6)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'user_id'            => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'message_id'),
                'time_created'       => array('type' => 'INT(14)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'user_id'),
                'time_edited'        => array('type' => 'INT(14)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'time_created'),
                'hits'               => array('type' => 'INT(7)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'time_edited')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_blog_messages` (`message_id`, `user_id`, `time_created`, `time_edited`, `hits`)
            VALUES  (4, 1, 1291976892, 1339076983, 5),
                    (5, 1, 1338990215, 1345209958, 13),
                    (6, 1, 1339076909, 1345210026, 110)
            ON DUPLICATE KEY UPDATE `message_id` = `message_id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_blog_messages_lang',
            array(
                'message_id'     => array('type' => 'INT(6)', 'unsigned' => true, 'primary' => true),
                'lang_id'        => array('type' => 'INT(2)', 'unsigned' => true, 'primary' => true, 'after' => 'message_id'),
                'is_active'      => array('type' => 'ENUM(\'0\',\'1\')', 'notnull' => true, 'default' => '1', 'after' => 'lang_id'),
                'subject'        => array('type' => 'VARCHAR(250)', 'notnull' => true, 'default' => '', 'after' => 'is_active'),
                'content'        => array('type' => 'text', 'after' => 'subject'),
                'tags'           => array('type' => 'VARCHAR(250)', 'notnull' => true, 'default' => '', 'after' => 'content'),
                'image'          => array('type' => 'VARCHAR(250)', 'notnull' => true, 'default' => '', 'after' => 'tags')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_blog_messages_lang` (`message_id`, `lang_id`, `is_active`, `subject`, `content`, `tags`, `image`)
            VALUES  (4, 2, '0', 'I am the latest blog entry', 'Hi there, I am the latest blog entry,<br />\r\nyou can change or delete me if you log in to the backend, the coose Module-&gt;Blog-&gt;Manage News.<br />\r\n<br />\r\nHave fun<br />\r\nsincerely yours,<br />\r\nyour latest blog entry<br />\r\n<br />\r\n', 'blog entry latest', ''),
                    (4, 1, '0', '\"Hello World\"-Blog', 'Hier finden Sie jeweils den j&uuml;ngsten meiner Art. Falls ich ersetzt oder bearbeitet werden soll: unter &quot;Module&quot; - &quot;Blog&quot; - &quot;Eintr&auml;ge verwalten&quot; finden Sie mich und meine Vorg&auml;nger.', 'blog', ''),
                    (5, 3, '1', '', '', '', ''),
                    (5, 2, '1', 'Are you utilizing your company software layer?', 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat.', 'blog', 'images/blog/postimg2.jpg'),
                    (5, 1, '1', '', '', '', ''),
                    (6, 2, '1', 'How to break the Financial Inertia?', 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat.', 'blog', 'images/blog/postimg1.jpg'),
                    (4, 3, '1', '', '<br />\r\n', '', ''),
                    (6, 3, '1', '', '', '', ''),
                    (6, 1, '1', '', '', '', '')
            ON DUPLICATE KEY UPDATE `message_id` = `message_id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_blog_networks',
            array(
                'network_id'     => array('type' => 'INT(8)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'name'           => array('type' => 'VARCHAR(250)', 'notnull' => true, 'default' => '', 'after' => 'network_id'),
                'url'            => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'name'),
                'url_link'       => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'url'),
                'icon'           => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'url_link')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_blog_networks` (`network_id`, `name`, `url`, `url_link`, `icon`)
            VALUES  (1, 'Digg', 'http://www.digg.com', 'http://digg.com/submit?phase=2&url=[URL]&title=[SUBJECT]', 'images/blog/networks/digg.gif'),
                    (2, 'del.icio.ous', 'http://del.icio.us', 'http://del.icio.us/post?url=[URL]&title=[SUBJECT]', 'images/blog/networks/delicious.gif'),
                    (3, 'Mister Wong', 'http://www.mister-wong.de', 'http://www.mister-wong.de/index.php?action=addurl&bm_url=[URL]&bm_description=[SUBJECT]', 'images/blog/networks/wong.gif'),
                    (4, 'Google Bookmarks', 'http://www.google.com/bookmarks/', 'http://www.google.com/bookmarks/mark?op=edit&output=popup&bkmk=[URL]&title=[SUBJECT]', 'images/blog/networks/google.gif'),
                    (5, 'Furl', 'http://www.furl.net', 'http://furl.net/storeIt.jsp?t=[SUBJECT]&u=[URL]', 'images/blog/networks/furl.gif'),
                    (6, 'reddit', 'http://www.reddit.com/', 'http://reddit.com/submit?url=[URL]&title=[SUBJECT]', 'images/blog/networks/reddit.gif'),
                    (7, 'Yigg', 'http://www.yigg.de', 'http://yigg.de/neu?exturl=[URL]&exttitle=[SUBJECT]', 'images/blog/networks/yigg.gif'),
                    (8, 'BlinkList', 'http://www.blinklist.com', 'http://www.blinklist.com/index.php?Action=Blink/addblink.php&Description=&Url=[URL]&Title=[SUBJECT]', 'images/blog/networks/blinklist.gif'),
                    (9, 'Blogmarks', 'http://www.blogmarks.net', 'http://blogmarks.net/my/new.php?mini=1&simple=1&url=[URL]&title=[SUBJECT]', 'images/blog/networks/blogmarks.gif'),
                    (10, 'Co.mments', 'http://co.mments.com', 'http://co.mments.com/track?url=[URL]&title=[SUBJECT]', 'images/blog/networks/comments.gif'),
                    (11, 'Feed Me Links', 'http://www.feedmelinks.com', 'http://feedmelinks.com/categorize?from=toolbar&op=submit&name=[SUBJECT]&url=[URL]&version=0.7', 'images/blog/networks/feedmelinks.gif'),
                    (12, 'Folkd', 'http://www.folkd.com', 'http://www.folkd.com/submit/page/[URL]', 'images/blog/networks/folkd.gif'),
                    (13, 'Linkarena', 'http://www.linkarena.com', 'http://linkarena.com/bookmarks/addlink/?url=[URL]&title=[SUBJECT]&desc=&tags=', 'images/blog/networks/linkarena.gif'),
                    (14, 'Ma.gnolia', 'http://Ma.gnolia', 'http://ma.gnolia.com/bookmarklet/add?url=[URL]&title=[SUBJECT]&description=[SUBJECT]', 'images/blog/networks/magnolia.gif'),
                    (15, 'Newsvine', 'http://www.newsvine.com', 'http://www.newsvine.com/_wine/save?u=[URL]&h=[SUBJECT]', 'images/blog/networks/newsvine.gif'),
                    (16, 'OneView', 'http://beta.oneview.de', 'http://beta.oneview.de/quickadd/neu/addBookmark.jsf?URL=[URL]&title=[SUBJECT]', 'images/blog/networks/oneview.gif'),
                    (17, 'RawSugar', 'http://www.rawsugar.com', 'http://www.rawsugar.com/tagger/?turl=[URL]&tttl=[SUBJECT]', 'images/blog/networks/rawsugar.gif'),
                    (18, 'Squidoo', 'http://www.squidoo.com', 'http://www.squidoo.com/lensmaster/bookmark?[URL]', 'images/blog/networks/squidoo.gif'),
                    (19, 'Stumble Upon', 'http://www.stumbleupon.com', 'http://www.stumbleupon.com/refer.php?url=[URL]&title=[SUBJECT]', 'images/blog/networks/stumbleupon.gif'),
                    (20, 'Technorati', 'http://www.technorati.com', 'http://www.technorati.com/faves?add=[URL]', 'images/blog/networks/technorati.gif'),
                    (21, 'Webnews', 'http://www.webnews.de', 'http://www.webnews.de/einstellen?url=[URL]&title=[SUBJECT]', 'images/blog/networks/webnews.gif'),
                    (22, 'Yahoo My Web', 'http://myweb2.search.yahoo.com', 'http://myweb2.search.yahoo.com/myresults/bookmarklet?u=[URL]&t=[SUBJECT]', 'images/blog/networks/yahoo.gif')
            ON DUPLICATE KEY UPDATE `network_id` = `network_id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_blog_networks_lang',
            array(
                'network_id'     => array('type' => 'INT(8)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
                'lang_id'        => array('type' => 'INT(2)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true, 'after' => 'network_id')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_blog_networks_lang` (`network_id`, `lang_id`)
            VALUES  (1, 1),
                    (1, 2),
                    (1, 3),
                    (2, 1),
                    (2, 2),
                    (2, 3),
                    (3, 1),
                    (3, 2),
                    (3, 3),
                    (4, 1),
                    (4, 2),
                    (4, 3),
                    (5, 1),
                    (5, 2),
                    (5, 3),
                    (6, 1),
                    (6, 2),
                    (6, 3),
                    (7, 1),
                    (7, 2),
                    (7, 3),
                    (8, 1),
                    (8, 2),
                    (8, 3),
                    (9, 1),
                    (9, 2),
                    (9, 3),
                    (10, 1),
                    (10, 2),
                    (10, 3),
                    (11, 1),
                    (11, 2),
                    (11, 3),
                    (12, 1),
                    (12, 2),
                    (12, 3),
                    (13, 1),
                    (13, 2),
                    (13, 3),
                    (14, 1),
                    (14, 2),
                    (14, 3),
                    (15, 1),
                    (15, 2),
                    (15, 3),
                    (16, 1),
                    (16, 2),
                    (16, 3),
                    (17, 1),
                    (17, 2),
                    (17, 3),
                    (18, 1),
                    (18, 2),
                    (18, 3),
                    (19, 1),
                    (19, 2),
                    (19, 3),
                    (20, 1),
                    (20, 2),
                    (20, 3),
                    (21, 1),
                    (21, 2),
                    (21, 3),
                    (22, 1),
                    (22, 2),
                    (22, 3)
            ON DUPLICATE KEY UPDATE `network_id` = `network_id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_blog_settings',
            array(
                'name'       => array('type' => 'VARCHAR(50)', 'primary' => true),
                'value'      => array('type' => 'VARCHAR(250)', 'after' => 'name')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_blog_settings` (`name`, `value`)
            VALUES  ('blog_block_activated', '1'),
                    ('blog_block_messages', '5'),
                    ('blog_comments_activated', '1'),
                    ('blog_comments_anonymous', '1'),
                    ('blog_comments_autoactivate', '1'),
                    ('blog_comments_editor', 'textarea'),
                    ('blog_comments_notification', '1'),
                    ('blog_comments_timeout', '30'),
                    ('blog_general_introduction', '400'),
                    ('blog_rss_activated', '1'),
                    ('blog_rss_comments', '20'),
                    ('blog_rss_messages', '5'),
                    ('blog_tags_hitlist', '5'),
                    ('blog_voting_activated', '1')
            ON DUPLICATE KEY UPDATE `name` = `name`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_blog_votes',
            array(
                'vote_id'        => array('type' => 'INT(8)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'message_id'     => array('type' => 'INT(6)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'vote_id'),
                'time_voted'     => array('type' => 'INT(14)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'message_id'),
                'ip_address'     => array('type' => 'VARCHAR(15)', 'notnull' => true, 'default' => '0.0.0.0', 'after' => 'time_voted'),
                'vote'           => array('type' => 'ENUM(\'1\',\'2\',\'3\',\'4\',\'5\',\'6\',\'7\',\'8\',\'9\',\'10\')', 'notnull' => true, 'default' => '1', 'after' => 'ip_address')
            ),
            array(
                'message_id'     => array('fields' => array('message_id'))
            ),
            'MyISAM',
            'cx3upgrade'
        );

    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }
}
