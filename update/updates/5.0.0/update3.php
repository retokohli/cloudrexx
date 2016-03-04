<?php

/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2015
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
 * "Cloudrexx" is a registered trademark of Cloudrexx AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * This file updates a RC1 or RC2 installation to a stable installation
 * To update your installation perform the following:
 * 1. Copy all your files to a backup folder
 * 2. Create a backup of your database
 * 3. Copy all stable files to your folder
 * 4. Copy /config/configuration.php, /config/settings.php and /.htaccess back
 * 5. Copy your customized files back
 * 6. Execute this script
 */

$documentRoot = ASCMS_PATH . ASCMS_PATH_OFFSET;

$version = detectCx3Version();

$updatesRc1ToRc2 = array(
    /*
    array(
        'table' => ,
        'structure' => ,
        'keys' => ,
    ),
     */
    '
        INSERT INTO `'.DBPREFIX.'settings` (`setid`, `setname`, `setvalue`, `setmodule`)
        VALUES (103, "availableComponents", "", 66)
    ',
    array(
        'table' => DBPREFIX.'modules',
        'structure' => array(
            'id'                         => array('type' => 'INT(2)', 'unsigned' => true, 'notnull' => false),
            'name'                       => array('type' => 'VARCHAR(250)', 'notnull' => true, 'default' => '', 'after' => 'id'),
            'distributor'                => array('type' => 'CHAR(50)', 'after' => 'name'),
            'description_variable'       => array('type' => 'VARCHAR(50)', 'notnull' => true, 'default' => '', 'after' => 'distributor'),
            'status'                     => array('type' => 'SET(\'y\',\'n\')', 'notnull' => true, 'default' => 'n', 'after' => 'description_variable'),
            'is_required'                => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'status'),
            'is_core'                    => array('type' => 'TINYINT(4)', 'notnull' => true, 'default' => '0', 'after' => 'is_required'),
            'is_active'                  => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'is_core'),
        ),
        'keys' => array(
            'id'                         => array('fields' => array('id'), 'type' => 'UNIQUE'),
        ),
    ),
    '
        UPDATE `'.DBPREFIX.'modules`
        SET `distributor` = "Cloudrexx AG"
    ',
    array(
        'table' => DBPREFIX.'module_repository',
        'structure' => array(
            'id'                 => array('type' => 'INT(6)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true),
            'moduleid'           => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'id'),
            'content'            => array('type' => 'mediumtext', 'after' => 'moduleid'),
            'title'              => array('type' => 'VARCHAR(250)', 'notnull' => true, 'default' => '', 'after' => 'content'),
            'cmd'                => array('type' => 'VARCHAR(20)', 'notnull' => true, 'default' => '', 'after' => 'title'),
            'expertmode'         => array('type' => 'SET(\'y\',\'n\')', 'notnull' => true, 'default' => 'n', 'after' => 'cmd'),
            'parid'              => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'expertmode'),
            'displaystatus'      => array('type' => 'SET(\'on\',\'off\')', 'notnull' => true, 'default' => 'on', 'after' => 'parid'),
            'username'           => array('type' => 'VARCHAR(250)', 'notnull' => true, 'default' => '', 'after' => 'displaystatus'),
            'displayorder'       => array('type' => 'SMALLINT(6)', 'notnull' => true, 'default' => '100', 'after' => 'username')
        ),
        'keys' => array(
            'contentid'          => array('fields' => array('id'), 'type' => 'UNIQUE'),
            'fulltextindex'      => array('fields' => array('title','content'), 'type' => 'FULLTEXT')
        ),
    ),
);
$updatesRc2ToStable = array(
    '
        INSERT INTO `'.DBPREFIX.'settings` (`setid`, `setname`, `setvalue`, `setmodule`)
        VALUES  (104, "upgradeUrl", "http://license.contrexx.com/", 66),
                (105, "isUpgradable", "on", 66),
                (106, "dashboardMessages", "", 66)
    ',
);
$updatesStableToHotfix = array();
$updatesHotfixToSp1 = array(
    array(
        'table' => DBPREFIX.'module_block_rel_lang_content',
        'structure' => array(
            'block_id'       => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
            'lang_id'        => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'block_id'),
            'content'        => array('type' => 'mediumtext', 'after' => 'lang_id'),
            'active'         => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'content'),
        ),
        'keys' => array(
            'id_lang'        => array('fields' => array('block_id','lang_id'), 'type' => 'UNIQUE'),
        ),
    ),
    array(
        'table' => DBPREFIX.'core_mail_template',
        'structure' => array(
            'key'            => array('type' => 'tinytext'),
            'section'        => array('type' => 'tinytext', 'notnull' => true, 'after' => 'key'),
            'text_id'        => array('type' => 'INT(10)', 'unsigned' => true, 'after' => 'section'),
            'html'           => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'text_id'),
            'protected'      => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'html'),
        ),
    ),
    '
        ALTER TABLE `'.DBPREFIX.'core_mail_template` ADD PRIMARY KEY (`key` (32), `section` (32))
    ',
    array(
        'table' => DBPREFIX.'languages',
        'structure' => array(
            'id'                     => array('type' => 'INT(2)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
            'lang'                   => array('type' => 'VARCHAR(5)', 'notnull' => true, 'default' => '', 'after' => 'id'),
            'name'                   => array('type' => 'VARCHAR(250)', 'notnull' => true, 'default' => '', 'after' => 'lang'),
            'charset'                => array('type' => 'VARCHAR(20)', 'notnull' => true, 'default' => 'iso-8859-1', 'after' => 'name'),
            'themesid'               => array('type' => 'INT(2)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'after' => 'charset'),
            'print_themes_id'        => array('type' => 'INT(2)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'after' => 'themesid'),
            'pdf_themes_id'          => array('type' => 'INT(2)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'print_themes_id'),
            'frontend'               => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'pdf_themes_id'),
            'backend'                => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'frontend'),
            'is_default'             => array('type' => 'SET(\'true\',\'false\')', 'notnull' => true, 'default' => 'false', 'after' => 'backend'),
            'mobile_themes_id'       => array('type' => 'INT(2)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'is_default'),
            'fallback'               => array('type' => 'INT(2)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'mobile_themes_id'),
            'app_themes_id'          => array('type' => 'INT(2)', 'after' => 'fallback'),
        ),
        'keys' => array(
            'lang'                   => array('fields' => array('lang'), 'type' => 'UNIQUE'),
            'defaultstatus'          => array('fields' => array('is_default')),
        ),
    ),
    '
        DROP TABLE IF EXISTS `'.DBPREFIX.'module_alias_source`
    ',
    '
        DROP TABLE IF EXISTS `'.DBPREFIX.'module_alias_target`
    ',
    array(
        'table' => DBPREFIX.'module_checkout_settings_mails',
        'structure' => array(
            'id'         => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
            'title'      => array('type' => 'text', 'after' => 'id'),
            'content'    => array('type' => 'text', 'after' => 'title')
        ),
        'engine' => 'MyISAM',
    ),
    array(
        'table' => DBPREFIX.'module_checkout_settings_yellowpay',
        'structure' => array(
            'id'         => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
            'name'       => array('type' => 'text', 'after' => 'id'),
            'value'      => array('type' => 'text', 'after' => 'name')
        ),
        'engine' => 'MyISAM',
    ),
);

$updatesSp1ToSp2 = array(
    array (
        'table' => DBPREFIX.'module_block_categories',
        'structure' => array(
            'id'             => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
            'parent'         => array('type' => 'INT(10)', 'notnull' => true, 'default' => '0', 'after' => 'id'),
            'name'           => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'parent'),
            'seperator'      => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'name'),
            'order'          => array('type' => 'INT(10)', 'notnull' => true, 'default' => '0', 'after' => 'seperator'),
            'status'         => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '1', 'after' => 'order')
        ),
    ),
    array (
        'table' => DBPREFIX.'module_block_blocks',
        'structure' => array(
            'id'                 => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
            'start'              => array('type' => 'INT(10)', 'notnull' => true, 'default' => '0', 'after' => 'id'),
            'end'                => array('type' => 'INT(10)', 'notnull' => true, 'default' => '0', 'after' => 'start'),
            'name'               => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'end'),
            'random'             => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'name'),
            'random_2'           => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'random'),
            'random_3'           => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'random_2'),
            'random_4'           => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'random_3'),
            'global'             => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'random_4'),
            'category'           => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'global'),
            'direct'             => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'category'),
            'active'             => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'direct'),
            'order'              => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'active'),
            'cat'                => array('type' => 'INT(10)', 'notnull' => true, 'default' => '0', 'after' => 'order'),
            'wysiwyg_editor'     => array('type' => 'INT(1)', 'notnull' => true, 'default' => '1', 'after' => 'cat')
        ),
    ),
    array (
        'table' => DBPREFIX.'module_block_rel_pages',
        'structure' => array(
            'block_id'       => array('type' => 'INT(7)', 'notnull' => true, 'default' => '0', 'primary' => true),
            'page_id'        => array('type' => 'INT(7)', 'notnull' => true, 'default' => '0', 'after' => 'block_id', 'primary' => true),
            'placeholder'    => array('type' => 'ENUM(\'global\',\'direct\',\'category\')', 'notnull' => true, 'default' => 'global', 'after' => 'page_id', 'primary' => true)
        ),
    ),
    array(
        'table' => DBPREFIX.'module_knowledge_tags_articles',
        'structure' => array(
            'article'    => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
            'tag'        => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'article'),
        ),
        'keys' => array(
            'article'    => array('fields' => array('article','tag'), 'type' => 'UNIQUE'),
        )
    ),
);

$updatesSp2ToSp3 = array(
    array (
        'table' => DBPREFIX.'module_block_categories',
        'structure' => array(
            'id'             => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
            'parent'         => array('type' => 'INT(10)', 'notnull' => true, 'default' => '0', 'after' => 'id'),
            'name'           => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'parent'),
            'seperator'      => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'name'),
            'order'          => array('type' => 'INT(10)', 'notnull' => true, 'default' => '0', 'after' => 'seperator'),
            'status'         => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '1', 'after' => 'order')
        ),
    ),
    array (
        'table' => DBPREFIX.'module_block_blocks',
        'structure' => array(
            'id'                 => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
            'start'              => array('type' => 'INT(10)', 'notnull' => true, 'default' => '0', 'after' => 'id'),
            'end'                => array('type' => 'INT(10)', 'notnull' => true, 'default' => '0', 'after' => 'start'),
            'name'               => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'end'),
            'random'             => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'name'),
            'random_2'           => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'random'),
            'random_3'           => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'random_2'),
            'random_4'           => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'random_3'),
            'global'             => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'random_4'),
            'category'           => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'global'),
            'direct'             => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'category'),
            'active'             => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'direct'),
            'order'              => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'active'),
            'cat'                => array('type' => 'INT(10)', 'notnull' => true, 'default' => '0', 'after' => 'order'),
            'wysiwyg_editor'     => array('type' => 'INT(1)', 'notnull' => true, 'default' => '1', 'after' => 'cat')
        ),
    ),
    array (
        'table' => DBPREFIX.'module_block_rel_pages',
        'structure' => array(
            'block_id'       => array('type' => 'INT(7)', 'notnull' => true, 'default' => '0', 'primary' => true),
            'page_id'        => array('type' => 'INT(7)', 'notnull' => true, 'default' => '0', 'after' => 'block_id', 'primary' => true),
            'placeholder'    => array('type' => 'ENUM(\'global\',\'direct\',\'category\')', 'notnull' => true, 'default' => 'global', 'after' => 'page_id', 'primary' => true)
        ),
    ),
    array (
        'table' => DBPREFIX.'settings_image',
        'structure' => array(
            'id'         => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
            'name'       => array('type' => 'VARCHAR(50)', 'after' => 'id'),
            'value'      => array('type' => 'text', 'after' => 'name')
        ),
    ),
    "INSERT IGNORE INTO `".DBPREFIX."settings_image` (`name`, `value`) VALUES ('image_cut_width', '500')",
    "INSERT IGNORE INTO `".DBPREFIX."settings_image` (`name`, `value`) VALUES ('image_cut_height', '500')",
    "INSERT IGNORE INTO `".DBPREFIX."settings_image` (`name`, `value`) VALUES ('image_scale_width', '800')",
    "INSERT IGNORE INTO `".DBPREFIX."settings_image` (`name`, `value`) VALUES ('image_scale_height', '800')",
    "INSERT IGNORE INTO `".DBPREFIX."settings_image` (`name`, `value`) VALUES ('image_compression', '100')",
    array (
        'table' => DBPREFIX.'module_egov_product_calendar',
        'structure' => array(
            'calendar_id'            => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
            'calendar_product'       => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'after' => 'calendar_id'),
            'calendar_order'         => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'after' => 'calendar_product'),
            'calendar_day'           => array('type' => 'INT(2)', 'notnull' => true, 'default' => '0', 'after' => 'calendar_order'),
            'calendar_month'         => array('type' => 'INT(2)', 'zerofill' => true, 'default' => '00', 'after' => 'calendar_day'),
            'calendar_year'          => array('type' => 'INT(4)', 'notnull' => true, 'default' => '0', 'after' => 'calendar_month'),
            'calendar_act'           => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'calendar_year')
        ),
        'keys' => array(
            'calendar_product'       => array('fields' => array('calendar_product'))
        ),
    ),
    array (
        'table' => DBPREFIX.'voting_results',
        'structure' => array(
            'id'                     => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
            'voting_system_id'       => array('type' => 'INT(11)', 'notnull' => false, 'after' => 'id'),
            'question'               => array('type' => 'CHAR(200)', 'notnull' => false, 'after' => 'voting_system_id'),
            'votes'                  => array('type' => 'INT(11)', 'notnull' => false, 'default' => '0', 'after' => 'question')
        ),
    ),
    array (
        'table' => DBPREFIX.'voting_system',
        'structure' => array(
            'id'                     => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
            'date'                   => array('type' => 'timestamp', 'notnull' => true, 'after' => 'id'),
            'title'                  => array('type' => 'VARCHAR(60)', 'notnull' => true, 'default' => '', 'after' => 'date'),
            'question'               => array('type' => 'text', 'after' => 'title', 'notnull' => false),
            'status'                 => array('type' => 'TINYINT(1)', 'default' => '1', 'notnull' => false, 'after' => 'question'),
            'submit_check'           => array('type' => 'ENUM(\'cookie\',\'email\')', 'notnull' => true, 'default' => 'cookie', 'after' => 'status'),
            'votes'                  => array('type' => 'INT(11)', 'notnull' => false, 'default' => '0', 'after' => 'submit_check'),
            'additional_nickname'    => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'votes'),
            'additional_forename'    => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'additional_nickname'),
            'additional_surname'     => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'additional_forename'),
            'additional_phone'       => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'additional_surname'),
            'additional_street'      => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'additional_phone'),
            'additional_zip'         => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'additional_street'),
            'additional_email'       => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'additional_zip'),
            'additional_city'        => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'additional_email'),
            'additional_comment'     => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'additional_city')
        ),
    ),
    array (
        'table' => DBPREFIX.'module_knowledge_tags_articles',
        'structure' => array(
            'article'    => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
            'tag'        => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'article')
        ),
        'keys' => array(
            'article'    => array('fields' => array('article', 'tag'), 'type' => 'UNIQUE', 'force' => true)
        ),
    ),
    array (
        'table' => DBPREFIX.'core_setting',
        'structure' => array(
            'section' => array('type' => 'VARCHAR(32)', 'default' => '', 'primary' => true),
            'name' => array('type' => 'VARCHAR(255)', 'default' => '', 'primary' => true),
            'group' => array('type' => 'VARCHAR(32)', 'default' => '', 'primary' => true),
            'type' => array('type' => 'VARCHAR(32)', 'notnull' => true, 'default' => 'text', 'after' => 'group'),
            'value' => array('type' => 'text', 'notnull' => true, 'after' => 'type'),
            'values' => array('type' => 'text', 'notnull' => true, 'after' => 'value'),
            'ord' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'values'),
        ),
    ),
    array (
        'table' => DBPREFIX.'core_text',
        'structure' => array(
            'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
            'lang_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'primary' => true, 'after' => 'id'),
            'section' => array('type' => 'VARCHAR(32)', 'notnull' => true, 'default' => '', 'primary' => true, 'after' => 'lang_id'),
            'key' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'primary' => true, 'after' => 'section'),
            'text' => array('type' => 'text', 'after' => 'key'),
        ),
        'keys' => array(
            'text' => array('fields' => array('text'), 'type' => 'FULLTEXT'),
        ),
    ),
    'UPDATE `' . DBPREFIX . 'modules` SET `status` = \'y\' WHERE `id` = 68',
);

$updatesSp3ToSp4 = array();

$updatesSp4To310 = array(
    "INSERT IGNORE INTO `" . DBPREFIX . "settings` (`setid`, `setname`, `setvalue`, `setmodule`) VALUES
    (57, 'forceProtocolFrontend', 'none', 1),
    (58, 'forceProtocolBackend', 'none', 1),
    (59, 'forceDomainUrl', 'off', 1)",
    array (
        'table' => DBPREFIX.'core_text',
        'structure' => array(
            'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
            'lang_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'primary' => true, 'after' => 'id'),
            'section' => array('type' => 'VARCHAR(32)', 'notnull' => true, 'default' => '', 'primary' => true, 'after' => 'lang_id'),
            'key' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'primary' => 32, 'after' => 'section'),
            'text' => array('type' => 'text', 'after' => 'key'),
        ),
        'keys' => array(
            'text' => array('fields' => array('text'), 'type' => 'FULLTEXT'),
        ),
    ),
    array(
        'table' => DBPREFIX.'component',
        'structure' => array(
            'id'         => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
            'name'       => array('type' => 'VARCHAR(100)', 'after' => 'id'),
            'type'       => array('type' => 'ENUM(\'core\',\'core_module\',\'module\')', 'after' => 'name')
        ),
        'engine' => 'InnoDB',
    ),
    "INSERT IGNORE INTO `".DBPREFIX."component` (`id`, `name`, `type`) VALUES
    (70, 'Workbench', 'core_module'),
    (71, 'FrontendEditing', 'core_module'),
    (72, 'ContentManager', 'core')",
);

$updates310To310Sp1 = array(
    "UPDATE `" . DBPREFIX . "modules` SET `is_core` = '1' WHERE `name` = 'upload'",
    // fixing issue with protocol selection in settings
    "INSERT INTO `" . DBPREFIX . "settings` (`setid`, `setname`, `setvalue`, `setmodule`) VALUES
        (57, 'forceProtocolFrontend', 'none', 1),
        (58, 'forceProtocolBackend', 'none', 1)
        ON DUPLICATE KEY UPDATE `setname` = VALUES(`setname`)",
    array(
        'table' => DBPREFIX.'languages',
        'structure' => array(
            'id'                     => array('type' => 'INT(2)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
            'lang'                   => array('type' => 'VARCHAR(5)', 'notnull' => true, 'default' => '', 'after' => 'id'),
            'name'                   => array('type' => 'VARCHAR(250)', 'notnull' => true, 'default' => '', 'after' => 'lang'),
            'charset'                => array('type' => 'VARCHAR(20)', 'notnull' => true, 'default' => 'iso-8859-1', 'after' => 'name'),
            'themesid'               => array('type' => 'INT(2)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'after' => 'charset'),
            'print_themes_id'        => array('type' => 'INT(2)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'after' => 'themesid'),
            'pdf_themes_id'          => array('type' => 'INT(2)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'print_themes_id'),
            'frontend'               => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'pdf_themes_id'),
            'backend'                => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'frontend'),
            'is_default'             => array('type' => 'SET(\'true\',\'false\')', 'notnull' => true, 'default' => 'false', 'after' => 'backend'),
            'mobile_themes_id'       => array('type' => 'INT(2)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'is_default'),
            'fallback'               => array('type' => 'INT(2)', 'unsigned' => true, 'notnull' => false, 'default' => '0', 'after' => 'mobile_themes_id'),
            'app_themes_id'          => array('type' => 'INT(2)', 'after' => 'fallback')
        ),
        'keys' => array(
            'lang'                   => array('fields' => array('lang'), 'type' => 'UNIQUE'),
            'defaultstatus'          => array('fields' => array('is_default')),
            'name'                   => array('fields' => array('name')),
            'name_2'                 => array('fields' => array('name'), 'type' => 'FULLTEXT')
        ),
    ),
);

$updates310Sp1To310Sp2 = array(
    '
        INSERT IGNORE INTO `'.DBPREFIX.'settings` (`setid`, `setname`, `setvalue`, `setmodule`)
        VALUES (119, "cacheUserCache", "off", 1)
    ',
    '
        INSERT IGNORE INTO `'.DBPREFIX.'settings` (`setid`, `setname`, `setvalue`, `setmodule`)
        VALUES (120, "cacheOPCache", "off", 1)
    ',
    '
        INSERT IGNORE INTO `'.DBPREFIX.'settings` (`setid`, `setname`, `setvalue`, `setmodule`)
        VALUES (121, "cacheUserCacheMemcacheConfig", "{\"ip\":\"127.0.0.1\",\"port\":11211}", 1)
    ',
    '
        INSERT IGNORE INTO `'.DBPREFIX.'settings` (`setid`, `setname`, `setvalue`, `setmodule`)
        VALUES (122, "cacheProxyCacheVarnishConfig", "{\"ip\":\"127.0.0.1\",\"port\":8080}", 1)
    ',
    'INSERT IGNORE INTO `'.DBPREFIX.'settings` (`setid`, `setname`, `setvalue`, `setmodule`) VALUES (123,"cacheOpStatus","off",1)',
    'INSERT IGNORE INTO `'.DBPREFIX.'settings` (`setid`, `setname`, `setvalue`, `setmodule`) VALUES (124,"cacheDbStatus","off",1)',
    'INSERT IGNORE INTO `'.DBPREFIX.'settings` (`setid`, `setname`, `setvalue`, `setmodule`) VALUES (125,"cacheVarnishStatus","off",1)',
    array(
        'table' => DBPREFIX.'modules',
        'structure' => array(
            'id'                         => array('type' => 'INT(2)', 'unsigned' => true, 'notnull' => false),
            'name'                       => array('type' => 'VARCHAR(250)', 'notnull' => true, 'default' => '', 'after' => 'id'),
            'distributor'                => array('type' => 'CHAR(50)', 'after' => 'name'),
            'description_variable'       => array('type' => 'VARCHAR(50)', 'notnull' => true, 'default' => '', 'after' => 'distributor'),
            'status'                     => array('type' => 'SET(\'y\',\'n\')', 'notnull' => true, 'default' => 'n', 'after' => 'description_variable'),
            'is_required'                => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'status'),
            'is_core'                    => array('type' => 'TINYINT(4)', 'notnull' => true, 'default' => '0', 'after' => 'is_required'),
            'is_active'                  => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'is_core'),
            'is_licensed'                => array('type' => 'TINYINT(1)', 'after' => 'is_active')
        ),
        'keys' => array(
            'id'                         => array('fields' => array('id'), 'type' => 'UNIQUE')
        ),
    ),
);

$updatesRc1To400    = array_merge($updatesRc1ToRc2, $updatesRc2ToStable, $updatesStableToHotfix, $updatesHotfixToSp1, $updatesSp1ToSp2, $updatesSp2ToSp3, $updatesSp3ToSp4, $updatesSp4To310, $updates310To310Sp1, $updates310Sp1To310Sp2);
$updatesRc2To400    = array_merge($updatesRc2ToStable, $updatesStableToHotfix, $updatesHotfixToSp1, $updatesSp1ToSp2, $updatesSp2ToSp3, $updatesSp3ToSp4, $updatesSp4To310, $updates310To310Sp1, $updates310Sp1To310Sp2);
$updatesStableTo400 = array_merge($updatesStableToHotfix, $updatesHotfixToSp1, $updatesSp1ToSp2, $updatesSp2ToSp3, $updatesSp3ToSp4, $updatesSp4To310, $updates310To310Sp1, $updates310Sp1To310Sp2);
$updatesHotfixTo400 = array_merge($updatesHotfixToSp1, $updatesSp1ToSp2, $updatesSp2ToSp3, $updatesSp3ToSp4, $updatesSp4To310, $updates310To310Sp1, $updates310Sp1To310Sp2);
$updatesSp1To400    = array_merge($updatesSp1ToSp2, $updatesSp2ToSp3, $updatesSp3ToSp4, $updatesSp4To310, $updates310To310Sp1, $updates310Sp1To310Sp2);
$updatesSp2To400    = array_merge($updatesSp2ToSp3, $updatesSp3ToSp4, $updatesSp4To310, $updates310To310Sp1, $updates310Sp1To310Sp2);
$updatesSp3To400    = array_merge($updatesSp3ToSp4, $updatesSp4To310, $updates310To310Sp1, $updates310Sp1To310Sp2);
$updatesSp4To400    = array_merge($updatesSp4To310, $updates310To310Sp1, $updates310Sp1To310Sp2);
$updates310To400    = array_merge($updates310To310Sp1, $updates310Sp1To310Sp2);
$updates310Sp1To400 = $updates310To400;


$updates = array();
if ($version == 'rc1') {
    $updates = $updatesRc1To400;
} elseif ($version == 'rc2') {
    $updates = $updatesRc2To400;
} elseif ($version == 'stable') {
    $updates = $updatesStableTo400;
} elseif ($version == 'hotfix') {
    $updates = $updatesHotfixTo400;
} elseif ($version == 'sp1') {
    $updates = $updatesSp1To400;
} elseif ($version == 'sp2') {
    $updates = $updatesSp2To400;
} elseif ($version == 'sp3') {
    $updates = $updatesSp3To400;
} elseif ($version == 'sp4') {
    $updates = $updatesSp4To400;
} elseif ($version == '310') {
    $updates = $updates310To400;
} elseif ($version == '311') {
    $updates = $updates310Sp1To400;
} elseif ($version == '320') {
    $updates = $updates310Sp1To400;
}



/***************************************
 *
 * EXECUTE DB-UPDATES
 *
 **************************************/
\DBG::msg('update3: execute DB-updates');
if (!isset($_SESSION['contrexx_update']['db3_migration'])) {
    $_SESSION['contrexx_update']['db3_migration'] = 0;
}
$executionCnt = 0;
foreach ($updates as $update) {
    // skip previously executed sql migrations
    if ($executionCnt < $_SESSION['contrexx_update']['db3_migration']) {
        $executionCnt++;
        continue;
    }

    if (is_array($update)) {
        try {
            \Cx\Lib\UpdateUtil::table(
                $update['table'],
                $update['structure'],
                $update['keys'],
                isset($update['engine']) ? $update['engine'] : 'MyISAM'
            );
        } catch (\Cx\Lib\UpdateException $e) {
            return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
        }
    } else {
        $result = \Cx\Lib\UpdateUtil::sql($update);
        if (!$result) {
            setUpdateMsg('Update failed: ' . contrexx_raw2xhtml($update));
            return false;
        }
    }

    $executionCnt++;
    $_SESSION['contrexx_update']['db3_migration'] = $executionCnt;
}

/***************************************
 *
 * STATS: ACCESS IDS
 *
 **************************************/
// add permission to stats settings if the user had permission to stats
if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '3.1.0')) {
    \DBG::msg('update3: fix stats access ids');
    try {
        $result = \Cx\Lib\UpdateUtil::sql("SELECT `group_id` FROM `" . DBPREFIX . "access_group_static_ids` WHERE access_id = 163 GROUP BY `group_id`");
        if ($result !== false) {
            while (!$result->EOF) {
                \Cx\Lib\UpdateUtil::sql("INSERT IGNORE INTO `" . DBPREFIX . "access_group_static_ids` (`access_id`, `group_id`)
                                            VALUES (170, " . intval($result->fields['group_id']) . ")");
                $result->MoveNext();
            }
        }
    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }
}

// replace source url to image
$arrContentSites = array(
    'media1', 'media2', 'media3', 'media4',
);
\DBG::msg('update3: migrate media pages (3.1.1)');
foreach ($arrContentSites as $module) {
    try {
        \Cx\Lib\UpdateUtil::migrateContentPage(
            $module,
            '',
            'images/modules/media/_base.gif',
            'core_modules/media/View/Media/_base.gif',
            '3.1.1'
        );
    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }
}
    
    
// replace source url to image
$arrContentSites = array(
    'media1', 'media2', 'media3', 'media4',
);
\DBG::msg('update3: migrate media pages (3.1.2)');
foreach ($arrContentSites as $module) {
    try {
        \Cx\Lib\UpdateUtil::migrateContentPage(
            $module,
            '',
            'images/modules/media/_base.gif',
            'core_modules/media/View/Media/_base.gif',
            '3.1.2'
        );
    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }
}

\DBG::msg('update3: end of script reached');
return true;
