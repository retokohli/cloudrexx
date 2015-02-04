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

function _galleryUpdate()
{
    global $objDatabase, $_ARRAYLANG;

    try {
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_gallery_categories',
            array(
                'id'                     => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'pid'                    => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0'),
                'sorting'                => array('type' => 'INT(6)',  'notnull' => true, 'default' => '0'),
                'status'                 => array('type' => 'SET(\'0\',\'1\')', 'notnull' => true, 'default' => '1'),
                'comment'                => array('type' => 'SET(\'0\',\'1\')', 'notnull' => true, 'default' => '0'),
                'voting'                 => array('type' => 'SET(\'0\',\'1\')', 'notnull' => true, 'default' => '0'),
                'backendProtected'       => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0'),
                'backend_access_id'      => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0'),
                'frontendProtected'      => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0'),
                'frontend_access_id'     => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0')
            )
        );
        \Cx\Lib\UpdateUtil::table(

            DBPREFIX.'module_gallery_pictures',
            array(
                'id'                     => array('type' => 'INT(11)',          'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'catid'                  => array('type' => 'INT(11)',          'notnull' => true, 'default' => '0'),
                'validated'              => array('type' => 'SET(\'0\',\'1\')', 'notnull' => true, 'default' => '0'),
                'status'                 => array('type' => 'SET(\'0\',\'1\')', 'notnull' => true, 'default' => '1'),
                'catimg'                 => array('type' => 'SET(\'0\',\'1\')', 'notnull' => true, 'default' => '0'),
                'sorting'                => array('type' => 'INT(6) UNSIGNED',  'notnull' => true, 'default' => '999'),
                'size_show'              => array('type' => 'SET(\'0\',\'1\')', 'notnull' => true, 'default' => '1'),
                'path'                   => array('type' => 'TEXT',             'notnull' => true),
                'link'                   => array('type' => 'TEXT',             'notnull' => true),
                'lastedit'               => array('type' => 'INT(14)',          'notnull' => true, 'default' => '0'),
                'size_type'              => array('type' =>"SET('abs', 'proz')",'notnull' => true, 'default' => 'proz'),
                'size_proz'              => array('type' => "INT(3)",           'notnull' => true, 'default' => '0'),
                'size_abs_h'             => array('type' => 'INT(11)',          'notnull' => true, 'default' => '0'),
                'size_abs_w'             => array('type' => 'INT(11)',          'notnull' => true, 'default' => '0'),
                'quality'                => array('type' => 'TINYINT(3)',       'notnull' => true, 'default' => '0')
            ),
            array(
                'galleryPicturesIndex' => array('type' => 'FULLTEXT', 'fields' => array('path'))
            )
        );
    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }

    $arrSettings = array(
        '1'  => array( 'name' => 'max_images_upload',        'value' => '10'),
        '2'  => array( 'name' => 'standard_quality',         'value' => '95'),
        '3'  => array( 'name' => 'standard_size_proz',       'value' => '25'),
        '4'  => array( 'name' => 'standard_width_abs',       'value' => '140'),
        '6'  => array( 'name' => 'standard_height_abs',      'value' => '0'),
        '7'  => array( 'name' => 'standard_size_type',       'value' => 'abs'),
        '8'  => array( 'name' => 'validation_show_limit',    'value' => '10'),
        '9'  => array( 'name' => 'validation_standard_type', 'value' => 'all'),
        '11' => array( 'name' => 'show_names',               'value' => 'off'),
        '12' => array( 'name' => 'quality',                  'value' => '95'),
        '13' => array( 'name' => 'show_comments',            'value' => 'off'),
        '14' => array( 'name' => 'show_voting',              'value' => 'off'),
        '15' => array( 'name' => 'enable_popups',            'value' => 'on'),
        '16' => array( 'name' => 'image_width',              'value' => '1200'),
        '17' => array( 'name' => 'paging',                   'value' => '30'),
        '18' => array( 'name' => 'show_latest',              'value' => 'on'),
        '19' => array( 'name' => 'show_random',              'value' => 'on'),
        '20' => array( 'name' => 'header_type',              'value' => 'hierarchy'),
        '21' => array( 'name' => 'show_ext',                 'value' => 'off'),
        '22' => array( 'name' => 'show_file_name',           'value' => 'on'),
        '23' => array( 'name' => 'slide_show',               'value' => 'slideshow'),
        '24' => array( 'name' => 'slide_show_seconds',       'value' => '3')
    );

    foreach ($arrSettings as $id => $arrSetting) {
        $query = "SELECT 1 FROM `".DBPREFIX."module_gallery_settings` WHERE `name`= '".$arrSetting['name']."'" ;
        if (($objRS = $objDatabase->Execute($query)) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
        if($objRS->RecordCount() == 0){
            $query = "
                INSERT INTO `".DBPREFIX."module_gallery_settings` (`id`, `name`, `value`)
                VALUES (".$id.", '".$arrSetting['name']."', '".$arrSetting['value']."')
                ON DUPLICATE KEY UPDATE `id` = `id`
            ";
            if ($objDatabase->Execute($query) === false) {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
    }

    /**************************************************************************
     * EXTENSION:   cleanup: delete translations, comments and                *
     *              votes of inexisting pictures                              *
     * ADDED:       Contrexx v3.0.1                                           *
     **************************************************************************/
    try {
        \Cx\Lib\UpdateUtil::sql('
            DELETE `language_pics`
            FROM `'.DBPREFIX.'module_gallery_language_pics` as `language_pics` LEFT JOIN `'.DBPREFIX.'module_gallery_pictures` as `pictures` ON `pictures`.`id` = `language_pics`.`picture_id`
            WHERE `pictures`.`id` IS NULL
        ');

        \Cx\Lib\UpdateUtil::sql('
            DELETE `comments`
            FROM `'.DBPREFIX.'module_gallery_comments` as `comments` LEFT JOIN `'.DBPREFIX.'module_gallery_pictures` as `pictures` ON `pictures`.`id` = `comments`.`picid`
            WHERE `pictures`.`id` IS NULL
        ');

        \Cx\Lib\UpdateUtil::sql('
            DELETE `votes`
            FROM `'.DBPREFIX.'module_gallery_votes` as `votes` LEFT JOIN `'.DBPREFIX.'module_gallery_pictures` as `pictures` ON `pictures`.`id` = `votes`.`picid`
            WHERE `pictures`.`id` IS NULL
        ');
    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }

    // remove the script tag at the beginning of the gallery page
    \Cx\Lib\UpdateUtil::migrateContentPageUsingRegex(array('module' => 'gallery'), '/^\s*(<script[^>]+>.+?Shadowbox.+?<\/script>)+/sm', '', array('content'), '3.0.3');
    return true;
}
