<?php

function _knowledgeUpdate()
{
    global $objDatabase;

    try{
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX . 'module_knowledge_article_content',
            array(
                'id'       => array('type' => 'INT(10)',  'notnull' => true, 'primary' => true, 'auto_increment' => true, 'unsigned' => true),
                'article'  => array('type' => 'INT(10)',  'notnull' => true, 'default' => 0, 'unsigned' => true),
                'lang'     => array('type' => 'INT(10)',  'notnull' => true, 'default' => 0, 'unsigned' => true),
                'question' => array('type' => 'TEXT',     'notnull' => true, 'default' => 0),
                'answer'   => array('type' => 'TEXT',     'notnull' => true, 'default' => 0),
            ),
            array( # indexes
                'module_knowledge_article_content_lang'    => array( 'fields'=>array('lang')),
                'module_knowledge_article_content_article' => array( 'fields'=>array('article')),
            )
        );
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX . 'module_knowledge_articles',
            array(
                'id'           => array('type' => 'INT(10)',    'notnull' => true, 'primary' => true, 'auto_increment' => true, 'unsigned' => true),
                'category'     => array('type' => 'INT(10)',    'notnull' => true, 'default' => 0, 'unsigned' => true),
                'active'       => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => 1),
                'hits'         => array('type' => 'INT',        'notnull' => true, 'default' => 0),
                'votes'        => array('type' => 'INT',        'notnull' => true, 'default' => 0),
                'votevalue'    => array('type' => 'INT',        'notnull' => true, 'default' => 0),
                'sort'         => array('type' => 'INT',        'notnull' => true, 'default' => 0),
                'date_created' => array('type' => 'INT(14)',    'notnull' => true, 'default' => 0),
                'date_updated' => array('type' => 'INT(14)',    'notnull' => true, 'default' => 0),
            )
        );
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX . 'module_knowledge_categories',
            array(
                'id'           => array('type' => 'INT(10)',    'notnull' => true, 'primary' => true, 'auto_increment' => true, 'unsigned' => true),
                'active'       => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => 1, 'unsigned' => true),
                'parent'       => array('type' => 'INT(10)',    'notnull' => true, 'default' => 0, 'unsigned' => true),
                'sort'         => array('type' => 'INT(10)',    'notnull' => true, 'default' => 1, 'unsigned' => true),
            ),
            array( # indexes
                'module_knowledge_categories_sort'   => array( 'fields'=>array('sort')),
                'module_knowledge_categories_parent' => array( 'fields'=>array('parent'))
            )
        );
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX . 'module_knowledge_categories_content',
            array(
                'id'           => array('type' => 'INT(10)',    'notnull' => true, 'primary' => true, 'auto_increment' => true, 'unsigned' => true),
                'category'     => array('type' => 'INT(10)', 'notnull' => true, 'default' => 0, 'unsigned' => true),
                'name'         => array('type' => 'VARCHAR(255)',    'notnull' => true, 'default' => ''),
                'lang'         => array('type' => 'INT(11)',    'notnull' => true, 'default' => 1),
            )
        );
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX . 'module_knowledge_settings',
            array(
                'id'    => array('type' => 'INT(10)',          'notnull' => true, 'primary' => true, 'auto_increment' => true, 'unsigned' => true),
                'name'  => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'value' => array('type' => 'TEXT',         'notnull' => true, 'default' => 0),
            ),
            array( # indexes
                'module_knowledge_settings_name'   => array( 'fields'=>array('name'))
            )
        );
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX . 'module_knowledge_tags',
            array(
                'id'    => array('type' => 'INT(10)',          'notnull' => true, 'primary' => true, 'auto_increment' => true, 'unsigned' => true),
                'name'  => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'lang'  => array('type' => 'INT(10)',          'notnull' => true, 'default' => 1, 'unsigned' => true),
            ),
            array( # indexes
                'module_knowledge_tags_name'   => array( 'fields'=>array('name'))
            )
        );
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX . 'module_knowledge_tags_articles',
            array(
                'id'      => array('type' => 'INT(10)',  'notnull' => true, 'primary' => true, 'auto_increment' => true, 'unsigned' => true),
                'article' => array('type' => 'INT(10)',  'notnull' => true, 'default' => 0, 'unsigned' => true),
                'tag'     => array('type' => 'INT(10)',  'notnull' => true, 'default' => 0, 'unsigned' => true),
            ),
            array( # indexes
                'module_knowledge_tags_articles_tag'     => array( 'fields'=>array('tag')),
                'module_knowledge_tags_articles_article' => array( 'fields'=>array('article')),
            )
        );

    }
    catch (\Cx\Lib\UpdateException $e) {
        // we COULD do something else here..
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }


    $arrSettings = array(
        array(
            'name'  => 'max_subcategories',
            'value' => '5'
        ),
        array(
            'name'  => 'column_number',
            'value' => '2'
        ),
        array(
            'name'  => 'max_rating',
            'value' => '8'
        ),
        array(
            'name'  => 'best_rated_sidebar_template',
            'value' => '<h2>Bestbewertete Artikel</h2>\r\n<div class="clearfix">\r\n<ul class="knowledge_sidebar">\r\n<!-- BEGIN article -->\r\n<li><a href="[[URL]]">[[ARTICLE]]</a></li>\r\n<!-- END article -->\r\n</ul>\r\n</div>'
        ),
        array(
            'name'  => 'best_rated_sidebar_length',
            'value' => '82'
        ),
        array(
            'name'  => 'best_rated_sidebar_amount',
            'value' => '5'
        ),
        array(
            'name'  => 'tag_cloud_sidebar_template',
            'value' => '[[CLOUD]] <br style="clear: both;" />'
        ),
        array(
            'name'  => 'most_read_sidebar_template',
            'value' => '<h2>Bestbewertete Artikel 2</h2>\r\n<div class="clearfix">\r\n<ul class="knowledge_sidebar">\r\n<!-- BEGIN article -->\r\n<li><a href="[[URL]]">[[ARTICLE]]</a></li>\r\n<!-- END article -->\r\n</ul>\r\n</div>'
        ),
        array(
            'name'  => 'most_read_sidebar_length',
            'value' => '79'
        ),
        array(
            'name'  => 'most_read_sidebar_amount',
            'value' => '5'
        ),
        array(
            'name'  => 'best_rated_siderbar_template',
            'value' => ''
        ),
        array(
            'name'  => 'most_read_amount',
            'value' => '5'
        ),
        array(
            'name'  => 'best_rated_amount',
            'value' => '5'
        )
    );
    foreach ($arrSettings as $arrSetting) {
        $query = "SELECT 1 FROM `".DBPREFIX."module_knowledge_settings` WHERE `name` = '".$arrSetting['name']."'";
        $objResult = $objDatabase->SelectLimit($query, 1);
        if ($objResult !== false) {
            if ($objResult->RecordCount() == 0) {
                $query = "INSERT INTO `".DBPREFIX."module_knowledge_settings` (`name`, `value`) VALUES ('".$arrSetting['name']."', '".$arrSetting['value']."')";
                if ($objDatabase->Execute($query) === false) {
                    return _databaseError($query, $objDatabase->ErrorMsg());
                }
            }
        } else {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }


    /*******************************************
    * EXTENSION:    Duplicate entries clean up *
    * ADDED:        Contrexx v3.0.2            *
    *******************************************/
    try {
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_knowledge_tags_articles',
            array(
                'article'    => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'tag'        => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'article')
            ),
            array(
                'article'    => array('fields' => array('article','tag'), 'type' => 'UNIQUE')
            ),
            'MyISAM',
            'cx3upgrade'
        );
    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }

    return true;
}



function _knowledgeInstall()
{
    try {

        /**************************************************************************
         * EXTENSION:   Initial creation (for contrexx editions <> premium with   *
         *              version < 3.0.0 which have this module not yet installed) *
         *                                                                        *
         * ADDED:       Contrexx v3.0.1                                           *
         **************************************************************************/
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_knowledge_article_content',
            array(
                'id'             => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'article'        => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'id'),
                'lang'           => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'article'),
                'question'       => array('type' => 'text', 'after' => 'lang'),
                'answer'         => array('type' => 'text', 'after' => 'question')
            ),
            array(
                'module_knowledge_article_content_lang' => array('fields' => array('lang')),
                'module_knowledge_article_content_article' => array('fields' => array('article'))
            ),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_knowledge_article_content` (`id`, `article`, `lang`, `question`, `answer`)
            VALUES  (184, 1, 1, 'Was ist Contrexx?', 'Contrexx&reg; ist ein modernes, einzigartiges und modulares Web Content Management System (WCMS) f&uuml;r die komplette Verwaltung einer Webseite. Zudem kann Contrexx&reg; auch f&uuml;r andere&nbsp;Informationsangebote wie Intranet, Extranet, eShop, Portal und weiteres eingesetzt werden. Das Contrexx&reg; basiert auf neuster PHP und MySQL Technologie und besticht in der einfachen Bedienung.'),
                    (185, 1, 2, 'What is Contrexx?', 'Contrexx&reg; is a powerful Open Source Web Content Management System (WCMS) which will assist you in the creation, administration and maintenance of contents for the internet or intranet. It''s a completely web-based system with an easy-to-use WYSIWYG editor and intuitive user interface. Contrexx&reg; is very flexible, by adding modules you can customize the CMS according to your individual demands and it requires no technical knowledge or previous training. Visit us online at <a rel=\"nofollow\" title=\"http://www.contrexx.com/\" class=\"external\" href=\"http://www.contrexx.com/\">http://www.contrexx.com/</a> to learn more.'),
                    (186, 1, 3, 'What is Contrexx?', 'Contrexx&reg; is a powerful Open Source Web Content Management System (WCMS)  which will assist you in the creation, administration and maintenance  of contents for the internet or intranet. It''s a completely web-based  system with an easy-to-use WYSIWYG editor and intuitive user interface.  Contrexx&reg; is very flexible, by adding modules you can customize the CMS  according to your individual demands and it requires no technical  knowledge or previous training. Visit us online at <a href=\"http://www.contrexx.com/\" class=\"external\" title=\"http://www.contrexx.com/\" rel=\"nofollow\">http://www.contrexx.com/</a> to learn more.'),
                    (187, 7, 1, 'Wie erstelle ich einen neuen Eintrag?', '<h3>1. Im Backend einloggen</h3> <p>Melden Sie sich zuerst im Backend Ihrer Website an. Die URL&nbsp;zum Backend lautet jeweils <a href=\"http://www.DOMAINNAME.TLD/cadmin\">http://www.DOMAINNAME.TLD/cadmin</a></p> <h3>2. Neuen Artikel erstellen</h3> <p>Navigieren Sie nach &quot;Module/Wissensdatenbank/Artikel&quot; und klicken Sie dort auf die Schaltfl&auml;che &quot;Hinzuf&uuml;gen&quot;.</p> <h3>3. Kategorie festlegen</h3> <p>W&auml;hlen Sie die Kategorie, unter welcher der Artikel erscheinen soll.</p> <img height=\"23\" width=\"412\" src=\"images/content/knowledgebase/wie_erstelle_ich_einen_neuen_eintrag_schritt_3_de.jpg\" alt=\"\" /><br /> <br /> <h3>4. Frage festlegen</h3> <p>Definieren Sie die Frage, welche beantwortet werden soll.</p> <img height=\"47\" border=\"0\" width=\"475\" alt=\"\" src=\"images/content/knowledgebase/wie_erstelle_ich_einen_neuen_eintrag_schritt_4_de.jpg\" /><br /> <br /> <h3>5. Stichworte festlegen</h3> <p>W&auml;hlen Sie vorhandene Stichworte aus oder f&uuml;gen Sie neue hinzu.</p> <img height=\"21\" width=\"378\" alt=\"\" src=\"images/content/knowledgebase/wie_erstelle_ich_einen_neuen_eintrag_schritt_5_de.jpg\" /><br /> <br /> <h3>6. Antwort schreiben</h3> <p>Schreiben Sie die Antwort im Editor.</p> <img height=\"102\" width=\"425\" alt=\"\" src=\"images/content/knowledgebase/wie_erstelle_ich_einen_neuen_eintrag_schritt_6_de.jpg\" /><br /> <br /> <h3>7. Eintrag speichern</h3> <p>Klicken Sie nun auf die Schaltfl&auml;che &quot;Speichern&quot;.</p>'),
                    (188, 7, 2, 'How do I create a new entry?', '<h3>1. Backend login</h3> <p>First login to your backend. The url is always named on <a href=\"http://www.DOMAINNAME.TLD/cadmin\">http://www.DOMAINNAME.TLD/cadmin</a></p> <h3>2. Create a new entry</h3> <p>Navigate to &quot;Module/Knowledge base/Articles&quot; and click on the button &quot;Add&quot;.</p> <h3>3. Set the category</h3> <p>Choose the category in which the entry should appear.</p> <img height=\"23\" width=\"408\" alt=\"\" src=\"images/content/knowledgebase/wie_erstelle_ich_einen_neuen_eintrag_schritt_3_en.jpg\" /><br /> <br /> <h3>4. Set the question</h3> <p>Define the question that has to be answered.</p> <img height=\"49\" border=\"0\" width=\"412\" src=\"images/content/knowledgebase/wie_erstelle_ich_einen_neuen_eintrag_schritt_4_en.jpg\" alt=\"\" /><br /> <br /> <h3>5. Set the keywords</h3> <p>Choose some existing keywords or add new ones.</p> <img height=\"22\" width=\"348\" src=\"images/content/knowledgebase/wie_erstelle_ich_einen_neuen_eintrag_schritt_5_en.jpg\" alt=\"\" /><br /> <br /> <h3>6. Write the answer</h3> <p>Type in the answer into the editor.</p> <img height=\"104\" width=\"424\" src=\"images/content/knowledgebase/wie_erstelle_ich_einen_neuen_eintrag_schritt_6_en.jpg\" alt=\"\" /><br /> <br /> <h3>7. Save the entry</h3> <p>Now click on the button &quot;Save&quot;.</p>'),
                    (189, 7, 3, 'How do I create a new entry?', '<h3>1. Backend login</h3> <p>First login to your backend. The url is always named on <a href=\"http://www.DOMAINNAME.TLD/cadmin\">http://www.DOMAINNAME.TLD/cadmin</a></p> <h3>2. Create a new entry</h3> <p>Navigate to &quot;Module/Knowledge base/Articles&quot; and click on the button &quot;Add&quot;.</p> <h3>3. Set the category</h3> <p>Choose the category in which the entry should appear.</p> <img height=\"23\" width=\"408\" alt=\"\" src=\"images/content/knowledgebase/wie_erstelle_ich_einen_neuen_eintrag_schritt_3_en.jpg\" /><br /> <br /> <h3>4. Set the question</h3> <p>Define the question that has to be answered.</p> <img height=\"49\" border=\"0\" width=\"412\" src=\"images/content/knowledgebase/wie_erstelle_ich_einen_neuen_eintrag_schritt_4_en.jpg\" alt=\"\" /><br /> <br /> <h3>5. Set the keywords</h3> <p>Choose some existing keywords or add new ones.</p> <img height=\"22\" width=\"348\" src=\"images/content/knowledgebase/wie_erstelle_ich_einen_neuen_eintrag_schritt_5_en.jpg\" alt=\"\" /><br /> <br /> <h3>6. Write the answer</h3> <p>Type in the answer into the editor.</p> <img height=\"104\" width=\"424\" src=\"images/content/knowledgebase/wie_erstelle_ich_einen_neuen_eintrag_schritt_6_en.jpg\" alt=\"\" /><br /> <br /> <h3>7. Save the entry</h3> <p>Now click on the button &quot;Save&quot;.</p>')
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

         \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_knowledge_articles',
            array(
                'id'                 => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'category'           => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'id'),
                'active'             => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '1', 'after' => 'category'),
                'hits'               => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'after' => 'active'),
                'votes'              => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'after' => 'hits'),
                'votevalue'          => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'after' => 'votes'),
                'sort'               => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'after' => 'votevalue'),
                'date_created'       => array('type' => 'INT(14)', 'notnull' => true, 'default' => '0', 'after' => 'sort'),
                'date_updated'       => array('type' => 'INT(14)', 'notnull' => true, 'default' => '0', 'after' => 'date_created')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_knowledge_articles` (`id`, `category`, `active`, `hits`, `votes`, `votevalue`, `sort`, `date_created`, `date_updated`)
            VALUES  (1, 1, 1, 20, 2, 14, 0, 1292236792, 1292847652),
                    (4, 0, 0, 0, 0, 0, 0, 1292236792, 1292236792),
                    (5, 0, 0, 0, 0, 0, 0, 1292236792, 1292236792),
                    (6, 0, 0, 0, 0, 0, 0, 1292236792, 1292236792),
                    (7, 2, 1, 50, 2, 15, 0, 1292405974, 1292849754)
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_knowledge_categories',
            array(
                'id'         => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'active'     => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'after' => 'id'),
                'parent'     => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'active'),
                'sort'       => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'after' => 'parent')
            ),
            array(
                'module_knowledge_categories_sort' => array('fields' => array('sort')),
                'module_knowledge_categories_parent' => array('fields' => array('parent'))
            ),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_knowledge_categories` (`id`, `active`, `parent`, `sort`)
            VALUES  (1, 1, 0, 1),
                    (2, 1, 0, 0)
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_knowledge_categories_content',
            array(
                'id'             => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'category'       => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'id'),
                'name'           => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'category'),
                'lang'           => array('type' => 'INT(11)', 'notnull' => true, 'default' => '1', 'after' => 'name')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_knowledge_categories_content` (`id`, `category`, `name`, `lang`)
            VALUES  (25, 1, 'Über Contrexx WCMS', 1),
                    (26, 1, 'About Contrexx WCMS', 2),
                    (27, 1, 'About Contrexx WCMS', 3),
                    (31, 2, 'Anleitungen zur Wissensdatenbank', 1),
                    (32, 2, 'Instructions for the knowledgebase', 2),
                    (33, 2, 'Instructions for the knowledgebase', 3)
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_knowledge_settings',
            array(
                'id'         => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'name'       => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'id'),
                'value'      => array('type' => 'text', 'after' => 'name')
            ),
            array(
                'module_knowledge_settings_name' => array('fields' => array('name'))
            ),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_knowledge_settings` (`id`, `name`, `value`)
            VALUES  (1, 'max_subcategories', '5'),
                    (2, 'column_number', '2'),
                    (3, 'max_rating', '8'),
                    (6, 'best_rated_sidebar_template', '<div class=\"clearfix\">\r\n<ul class=\"knowledge_sidebar\">\r\n<!-- BEGIN article -->\r\n<li><a href=\"[[URL]]\">[[ARTICLE]]</a></li>\r\n<!-- END article -->\r\n</ul>\r\n</div>'),
                    (7, 'best_rated_sidebar_length', '82'),
                    (8, 'best_rated_sidebar_amount', '5'),
                    (9, 'tag_cloud_sidebar_template', '[[CLOUD]] <br style=\"clear: both;\" />'),
                    (10, 'most_read_sidebar_template', '<div class=\"clearfix\">\r\n<ul class=\"knowledge_sidebar\">\r\n<!-- BEGIN article -->\r\n<li><a href=\"[[URL]]\">[[ARTICLE]]</a></li>\r\n<!-- END article -->\r\n</ul>\r\n</div>'),
                    (12, 'most_read_sidebar_length', '79'),
                    (13, 'most_read_sidebar_amount', '5'),
                    (14, 'best_rated_siderbar_template', ''),
                    (15, 'most_read_amount', '5'),
                    (16, 'best_rated_amount', '5')
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_knowledge_tags',
            array(
                'id'         => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'name'       => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'id'),
                'lang'       => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'after' => 'name')
            ),
            array(
                'module_knowledge_tags_name' => array('fields' => array('name'))
            ),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_knowledge_tags` (`id`, `name`, `lang`)
            VALUES  (1, 'comvation', 1),
                    (2, 'thun', 1),
                    (3, 'cms', 1),
                    (4, 'contrexx', 1),
                    (5, 'contrexx', 2),
                    (6, 'cms', 2),
                    (7, 'thun', 2),
                    (8, 'comvation', 2),
                    (9, 'thun cms', 1),
                    (10, 'neuer artikel', 1),
                    (11, 'aritkel anlegen', 1),
                    (12, 'neu', 1),
                    (13, 'nouvelle page', 3),
                    (14, 'nouveau article', 3),
                    (15, 'wcms', 1),
                    (16, 'contrexx', 3),
                    (17, 'cms', 3),
                    (18, 'thun', 3),
                    (19, 'comvation', 3),
                    (20, 'eintrag erstellen', 1),
                    (21, 'neuer eintrag', 1),
                    (22, 'new', 2),
                    (23, 'new entry', 2),
                    (24, 'create entry', 2),
                    (25, 'new entry', 3),
                    (26, 'new', 3),
                    (27, 'create entry', 3),
                    (28, 'wcms', 2),
                    (29, 'wcms', 3)
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_knowledge_tags_articles',
            array(
                'article'    => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'tag'        => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'article')
            ),
            array(
                'article'    => array('fields' => array('article','tag'), 'type' => 'UNIQUE')
            ),
            'MyISAM',
            'cx3upgrade'
        );

        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_knowledge_tags_articles` (`article`, `tag`)
            VALUES  (1, 1),
                    (1, 2),
                    (1, 15),
                    (1, 4),
                    (1, 8),
                    (1, 7),
                    (1, 28),
                    (1, 5),
                    (1, 19),
                    (1, 18),
                    (1, 29),
                    (1, 16),
                    (7, 12),
                    (7, 21),
                    (7, 20),
                    (7, 24),
                    (7, 22),
                    (7, 23),
                    (7, 26),
                    (7, 25),
                    (7, 27)
            ON DUPLICATE KEY UPDATE `article` = `article`
        ");

    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }
}
