<?php

function _knowledgeUpdate()
{
    try{
        UpdateUtil::table(
            DBPREFIX . 'module_knowledge_article_content',
            array(
                'id'       => array('type' => 'INT',  'notnull' => true, 'primary' => true, 'auto_increment' => true),
                'article'  => array('type' => 'INT',  'notnull' => true, 'default' => 0),
                'lang'     => array('type' => 'INT',  'notnull' => true, 'default' => 0),
                'question' => array('type' => 'TEXT', 'notnull' => true, 'default' => 0),
                'answer'   => array('type' => 'TEXT', 'notnull' => true, 'default' => 0),
            ),
            array( # indexes
                'module_knowledge_article_content_lang'    => array( 'fields'=>array('lang')),
                'module_knowledge_article_content_article' => array( 'fields'=>array('article')),
            )
        );
        UpdateUtil::table(
            DBPREFIX . 'module_knowledge_articles',
            array(
                'id'           => array('type' => 'INT',    'notnull' => true, 'primary' => true, 'auto_increment' => true),
                'category'     => array('type' => 'INT',    'notnull' => true, 'default' => 0),
                'active'       => array('type' => 'INT(1)', 'notnull' => true, 'default' => 1),
                'hits'         => array('type' => 'INT',    'notnull' => true, 'default' => 0),
                'votes'        => array('type' => 'INT',    'notnull' => true, 'default' => 0),
                'votevalue'    => array('type' => 'INT',    'notnull' => true, 'default' => 0),
                'sort'         => array('type' => 'INT',    'notnull' => true, 'default' => 0),
                'date_created' => array('type' => 'INT',    'notnull' => true, 'default' => 0),
                'date_updated' => array('type' => 'INT',    'notnull' => true, 'default' => 0),
            )
        );
        UpdateUtil::table(
            DBPREFIX . 'module_knowledge_categories',
            array(
                'id'           => array('type' => 'INT',    'notnull' => true, 'primary' => true, 'auto_increment' => true),
                'active'       => array('type' => 'INT(1)', 'notnull' => true, 'default' => 1),
                'parent'       => array('type' => 'INT',    'notnull' => true, 'default' => 0),
                'sort'         => array('type' => 'INT',    'notnull' => true, 'default' => 0),
            ),
            array( # indexes
                'module_knowledge_categories_sort'   => array( 'fields'=>array('sort')),
                'module_knowledge_categories_parent' => array( 'fields'=>array('parent'))
            )
        );
        UpdateUtil::table(
            DBPREFIX . 'module_knowledge_settings',
            array(
                'id'    => array('type' => 'INT',          'notnull' => true, 'primary' => true, 'auto_increment' => true),
                'name'  => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => 1),
                'value' => array('type' => 'TEXT',         'notnull' => true, 'default' => 0),
            ),
            array( # indexes
                'module_knowledge_settings_name'   => array( 'fields'=>array('name'))
            )
        );
        UpdateUtil::table(
            DBPREFIX . 'module_knowledge_tags',
            array(
                'id'    => array('type' => 'INT',          'notnull' => true, 'primary' => true, 'auto_increment' => true),
                'name'  => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => 1),
                'lang'  => array('type' => 'INT',          'notnull' => true, 'default' => 0),
            ),
            array( # indexes
                'module_knowledge_tags_name'   => array( 'fields'=>array('name'))
            )
        );
        UpdateUtil::table(
            DBPREFIX . 'module_knowledge_tags_articles',
            array(
                'id'      => array('type' => 'INT',  'notnull' => true, 'primary' => true, 'auto_increment' => true),
                'article' => array('type' => 'INT',  'notnull' => true, 'default' => 0),
                'tag'     => array('type' => 'INT',  'notnull' => true, 'default' => 0),
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
