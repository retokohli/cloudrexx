<?php

function _knowledgeUpdate()
{
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

    return true;
}

?>
