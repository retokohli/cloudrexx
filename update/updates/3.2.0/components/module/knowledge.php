<?php

/**
 * Contrexx
 *
 * @link      http://www.contrexx.com
 * @copyright Comvation AG 2007-2014
 * @version   Contrexx 4.0
 * 
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Contrexx" is a registered trademark of Comvation AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

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

        if (strpos(\Cx\Lib\UpdateUtil::sql('SHOW CREATE TABLE `'.DBPREFIX.'module_knowledge_tags_articles`')->fields['Create Table'], 'UNIQUE KEY') === false) {
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
                'article'    => array('fields' => array('article', 'tag'), 'type' => 'UNIQUE', 'force' => true)
            ),
            'MyISAM'
        );
    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }

    return true;
}
