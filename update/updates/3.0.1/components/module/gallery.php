<?php

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

    return true;
}



function _galleryInstall()
{
    try {

        /**************************************************************************
         * EXTENSION:   Initial creation (for contrexx editions <> premium with   *
         *              version < 3.0.0 which have this module not yet installed) *
         *                                                                        *
         * ADDED:       Contrexx v3.0.1                                           *
         **************************************************************************/
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_gallery_categories',
            array(
                'id'                     => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'pid'                    => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'after' => 'id'),
                'sorting'                => array('type' => 'INT(6)', 'notnull' => true, 'default' => '0', 'after' => 'pid'),
                'status'                 => array('type' => 'SET(\'0\',\'1\')', 'notnull' => true, 'default' => '1', 'after' => 'sorting'),
                'comment'                => array('type' => 'SET(\'0\',\'1\')', 'notnull' => true, 'default' => '0', 'after' => 'status'),
                'voting'                 => array('type' => 'SET(\'0\',\'1\')', 'notnull' => true, 'default' => '0', 'after' => 'comment'),
                'backendProtected'       => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'after' => 'voting'),
                'backend_access_id'      => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'after' => 'backendProtected'),
                'frontendProtected'      => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'after' => 'backend_access_id'),
                'frontend_access_id'     => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'after' => 'frontendProtected')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_gallery_categories` (`id`, `pid`, `sorting`, `status`, `comment`, `voting`, `backendProtected`, `backend_access_id`, `frontendProtected`, `frontend_access_id`)
            VALUES (1, 0, 0, '1', '1', '1', 0, 0, 0, 0)
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_gallery_comments',
            array(
                'id'         => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'picid'      => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'id'),
                'date'       => array('type' => 'INT(14)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'picid'),
                'ip'         => array('type' => 'VARCHAR(15)', 'notnull' => true, 'default' => '', 'after' => 'date'),
                'name'       => array('type' => 'VARCHAR(50)', 'notnull' => true, 'default' => '', 'after' => 'ip'),
                'email'      => array('type' => 'VARCHAR(250)', 'notnull' => true, 'default' => '', 'after' => 'name'),
                'www'        => array('type' => 'VARCHAR(250)', 'notnull' => true, 'default' => '', 'after' => 'email'),
                'comment'    => array('type' => 'text', 'after' => 'www')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_gallery_comments` (`id`, `picid`, `date`, `ip`, `name`, `email`, `www`, `comment`)
            VALUES (1, 4, 1210154371, '84.72.45.57', 'Test Tschanz', '', 'http://Test Tschanz', 'Test Tschanz')
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_gallery_language',
            array(
                'gallery_id'     => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
                'lang_id'        => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true, 'after' => 'gallery_id'),
                'name'           => array('type' => 'SET(\'name\',\'desc\')', 'notnull' => true, 'default' => '', 'primary' => true, 'after' => 'lang_id'),
                'value'          => array('type' => 'text', 'after' => 'name')
            ),
            array(
                'galleryindex'   => array('fields' => array('value'), 'type' => 'FULLTEXT')
            ),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_gallery_language` (`gallery_id`, `lang_id`, `name`, `value`)
            VALUES  (1, 1, 'name', 'Lorem Ipsum '),
                    (1, 1, 'desc', 'Lorem Ipsum Lorem Ipsum Lorem Ipsum Lorem Ipsum '),
                    (1, 2, 'name', 'Lorem Ipsum '),
                    (1, 2, 'desc', 'Lorem Ipsum Lorem Ipsum Lorem Ipsum Lorem Ipsum '),
                    (1, 3, 'name', 'Lorem Ipsum '),
                    (1, 3, 'desc', 'Lorem Ipsum Lorem Ipsum Lorem Ipsum Lorem Ipsum '),
                    (1, 4, 'name', 'Lorem Ipsum '),
                    (1, 4, 'desc', 'Lorem Ipsum Lorem Ipsum Lorem Ipsum Lorem Ipsum '),
                    (1, 5, 'name', 'Lorem Ipsum '),
                    (1, 5, 'desc', 'Lorem Ipsum Lorem Ipsum Lorem Ipsum Lorem Ipsum '),
                    (1, 6, 'name', 'Lorem Ipsum '),
                    (1, 6, 'desc', 'Lorem Ipsum Lorem Ipsum Lorem Ipsum Lorem Ipsum ')
            ON DUPLICATE KEY UPDATE `gallery_id` = `gallery_id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_gallery_language_pics',
            array(
                'picture_id'     => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
                'lang_id'        => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true, 'after' => 'picture_id'),
                'name'           => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'lang_id'),
                'desc'           => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'name')
            ),
            array(
                'galleryindex'   => array('fields' => array('name','desc'), 'type' => 'FULLTEXT')
            ),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_gallery_language_pics` (`picture_id`, `lang_id`, `name`, `desc`)
            VALUES  (1, 1, 'Bild_001.jpg', ''),
                    (1, 2, 'Bild_001.jpg', ''),
                    (1, 3, 'Bild_001.jpg', ''),
                    (1, 4, 'Bild_001.jpg', ''),
                    (1, 5, 'Bild_001.jpg', ''),
                    (1, 6, 'Bild_001.jpg', ''),
                    (2, 1, 'Bild_002.jpg', ''),
                    (2, 2, 'Bild_002.jpg', ''),
                    (2, 3, 'Bild_002.jpg', ''),
                    (2, 4, 'Bild_002.jpg', ''),
                    (2, 5, 'Bild_002.jpg', ''),
                    (2, 6, 'Bild_002.jpg', ''),
                    (3, 1, 'Bild_003.jpg', ''),
                    (3, 2, 'Bild_003.jpg', ''),
                    (3, 3, 'Bild_003.jpg', ''),
                    (3, 4, 'Bild_003.jpg', ''),
                    (3, 5, 'Bild_003.jpg', ''),
                    (3, 6, 'Bild_003.jpg', ''),
                    (4, 1, 'banner_neu.jpg', ''),
                    (4, 2, 'banner_neu.jpg', ''),
                    (4, 3, 'banner_neu.jpg', ''),
                    (4, 4, 'banner_neu.jpg', ''),
                    (4, 5, 'banner_neu.jpg', ''),
                    (4, 6, 'banner_neu.jpg', ''),
                    (5, 1, 'bild_001.jpg', ''),
                    (5, 2, 'bild_001.jpg', ''),
                    (5, 3, 'bild_001.jpg', ''),
                    (5, 4, 'bild_001.jpg', ''),
                    (5, 5, 'bild_001.jpg', ''),
                    (5, 6, 'bild_001.jpg', ''),
                    (6, 1, 'bild_002.jpg', ''),
                    (6, 2, 'bild_002.jpg', ''),
                    (6, 3, 'bild_002.jpg', ''),
                    (6, 4, 'bild_002.jpg', ''),
                    (6, 5, 'bild_002.jpg', ''),
                    (6, 6, 'bild_002.jpg', ''),
                    (7, 1, 'bild_003.jpg', ''),
                    (7, 2, 'bild_003.jpg', ''),
                    (7, 3, 'bild_003.jpg', ''),
                    (7, 4, 'bild_003.jpg', ''),
                    (7, 5, 'bild_003.jpg', ''),
                    (7, 6, 'bild_003.jpg', ''),
                    (12, 1, 'comvation.gif', ''),
                    (12, 2, 'comvation.gif', ''),
                    (12, 3, 'comvation.gif', ''),
                    (12, 4, 'comvation.gif', ''),
                    (12, 5, 'comvation.gif', ''),
                    (12, 6, 'comvation.gif', ''),
                    (13, 1, 'bild_0011.jpg', ''),
                    (13, 2, 'bild_0011.jpg', ''),
                    (13, 3, 'bild_0011.jpg', ''),
                    (13, 4, 'bild_0011.jpg', ''),
                    (13, 5, 'bild_0011.jpg', ''),
                    (13, 6, 'bild_0011.jpg', ''),
                    (14, 1, 'bild_0021.jpg', ''),
                    (14, 2, 'bild_0021.jpg', ''),
                    (14, 3, 'bild_0021.jpg', ''),
                    (14, 4, 'bild_0021.jpg', ''),
                    (14, 5, 'bild_0021.jpg', ''),
                    (14, 6, 'bild_0021.jpg', ''),
                    (15, 1, 'bild_0031.jpg', ''),
                    (15, 2, 'bild_0031.jpg', ''),
                    (15, 3, 'bild_0031.jpg', ''),
                    (15, 4, 'bild_0031.jpg', ''),
                    (15, 5, 'bild_0031.jpg', ''),
                    (15, 6, 'bild_0031.jpg', ''),
                    (25, 6, 'Port-folio-3', ''),
                    (25, 5, 'Port-folio-3', ''),
                    (25, 4, 'Port-folio-3', ''),
                    (25, 3, 'Port-folio-3', ''),
                    (25, 2, 'Port-folio-3', ''),
                    (25, 1, 'Port-folio-3', ''),
                    (24, 6, 'Port-folio-2', ''),
                    (24, 5, 'Port-folio-2', ''),
                    (24, 4, 'Port-folio-2', ''),
                    (24, 3, 'Port-folio-2', ''),
                    (24, 2, 'Port-folio-2', ''),
                    (24, 1, 'Port-folio-2', ''),
                    (23, 6, 'Portfolio-1', ''),
                    (23, 5, 'Portfolio-1', ''),
                    (23, 4, 'Portfolio-1', ''),
                    (23, 3, 'Portfolio-1', ''),
                    (23, 2, 'Portfolio-1', ''),
                    (23, 1, 'Portfolio-1', '')
            ON DUPLICATE KEY UPDATE `picture_id` = `picture_id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_gallery_pictures',
            array(
                'id'             => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'catid'          => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'after' => 'id'),
                'validated'      => array('type' => 'SET(\'0\',\'1\')', 'notnull' => true, 'default' => '0', 'after' => 'catid'),
                'status'         => array('type' => 'SET(\'0\',\'1\')', 'notnull' => true, 'default' => '1', 'after' => 'validated'),
                'catimg'         => array('type' => 'SET(\'0\',\'1\')', 'notnull' => true, 'default' => '0', 'after' => 'status'),
                'sorting'        => array('type' => 'INT(6)', 'unsigned' => true, 'notnull' => true, 'default' => '999', 'after' => 'catimg'),
                'size_show'      => array('type' => 'SET(\'0\',\'1\')', 'notnull' => true, 'default' => '1', 'after' => 'sorting'),
                'path'           => array('type' => 'text', 'after' => 'size_show'),
                'link'           => array('type' => 'text', 'after' => 'path'),
                'lastedit'       => array('type' => 'INT(14)', 'notnull' => true, 'default' => '0', 'after' => 'link'),
                'size_type'      => array('type' => 'SET(\'abs\',\'proz\')', 'notnull' => true, 'default' => 'proz', 'after' => 'lastedit'),
                'size_proz'      => array('type' => 'INT(3)', 'notnull' => true, 'default' => '0', 'after' => 'size_type'),
                'size_abs_h'     => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'after' => 'size_proz'),
                'size_abs_w'     => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'after' => 'size_abs_h'),
                'quality'        => array('type' => 'TINYINT(3)', 'notnull' => true, 'default' => '0', 'after' => 'size_abs_w')
            ),
            array(
                'galleryPicturesIndex' => array('fields' => array('path'), 'type' => 'FULLTEXT')
            ),
            'MyISAM',
            'cx3upgrade'
        );

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_gallery_settings',
            array(
                'id'         => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'name'       => array('type' => 'VARCHAR(30)', 'notnull' => true, 'default' => '', 'after' => 'id'),
                'value'      => array('type' => 'text', 'after' => 'name')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_gallery_settings` (`id`, `name`, `value`)
            VALUES  (1, 'max_images_upload', '10'),
                    (2, 'standard_quality', '95'),
                    (3, 'standard_size_proz', '25'),
                    (4, 'standard_width_abs', '274'),
                    (6, 'standard_height_abs', '0'),
                    (7, 'standard_size_type', 'abs'),
                    (8, 'validation_show_limit', '10'),
                    (9, 'validation_standard_type', 'all'),
                    (11, 'show_names', 'on'),
                    (12, 'quality', '95'),
                    (13, 'show_comments', 'off'),
                    (14, 'show_voting', 'off'),
                    (15, 'enable_popups', 'on'),
                    (16, 'image_width', '1200'),
                    (17, 'paging', '30'),
                    (18, 'show_latest', 'on'),
                    (19, 'show_random', 'on'),
                    (20, 'header_type', 'hierarchy'),
                    (21, 'show_ext', 'off'),
                    (22, 'show_file_name', 'off'),
                    (23, 'slide_show', 'slideshow'),
                    (24, 'slide_show_seconds', '3')
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_gallery_votes',
            array(
                'id'         => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'picid'      => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'id'),
                'date'       => array('type' => 'INT(14)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'picid'),
                'ip'         => array('type' => 'VARCHAR(15)', 'notnull' => true, 'default' => '', 'after' => 'date'),
                'md5'        => array('type' => 'VARCHAR(32)', 'notnull' => true, 'default' => '', 'after' => 'ip'),
                'mark'       => array('type' => 'INT(2)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'md5')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_gallery_votes` (`id`, `picid`, `date`, `ip`, `md5`, `mark`)
            VALUES (1, 4, 1210154365, '84.72.45.57', '09e1e1188001f5d2f51f44621272ccd7', 1)
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }
}
