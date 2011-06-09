<?php

function _knowledgeUpdate()
{
    global $objDatabase;

    try{
        UpdateUtil::table(
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
        UpdateUtil::table(
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
        UpdateUtil::table(
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
        UpdateUtil::table(
            DBPREFIX . 'module_knowledge_categories_content',
            array(
                'id'           => array('type' => 'INT(10)',    'notnull' => true, 'primary' => true, 'auto_increment' => true, 'unsigned' => true),
                'category'     => array('type' => 'INT(10)', 'notnull' => true, 'default' => 0, 'unsigned' => true),
                'name'         => array('type' => 'VARCHAR(255)',    'notnull' => true, 'default' => ''),
                'lang'         => array('type' => 'INT(11)',    'notnull' => true, 'default' => 1),
            )
        );
        UpdateUtil::table(
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
        UpdateUtil::table(
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
        UpdateUtil::table(
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
    catch (UpdateException $e) {
        // we COULD do something else here..
        return UpdateUtil::DefaultActionHandler($e);
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


    return true;
}

?>
