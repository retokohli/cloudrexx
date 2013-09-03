<?php

function _downloadsUpdate()
{
    global $objDatabase, $_ARRAYLANG, $_CORELANG;

    /************************************************
    * EXTENSION:    Initial creation of the         *
    *               database tables                 *
    * ADDED:        Contrexx v2.1.0                 *
    ************************************************/
    $arrTables  = $objDatabase->MetaTables('TABLES');
    if (!sizeof($arrTables)) {
        setUpdateMsg($_ARRAYLANG['TXT_UNABLE_DETERMINE_DATABASE_STRUCTURE']);
        return false;
    }

    $tables = array(
        DBPREFIX.'module_downloads_category' => "CREATE TABLE `".DBPREFIX."module_downloads_category` (
             `id` int(11) unsigned NOT NULL auto_increment,
             `parent_id` int(11) unsigned NOT NULL default '0',
             `is_active` tinyint(1) unsigned NOT NULL default '1',
             `visibility` tinyint(1) unsigned NOT NULL default '1',
             `owner_id` int(5) unsigned NOT NULL default '0',
             `order` int(3) unsigned NOT NULL default '0',
             `deletable_by_owner` tinyint(1) unsigned NOT NULL default '1',
             `modify_access_by_owner` tinyint(1) unsigned NOT NULL default '1',
             `read_access_id` int(11) unsigned NOT NULL default '0',
             `add_subcategories_access_id` int(11) unsigned NOT NULL default '0',
             `manage_subcategories_access_id` int(11) unsigned NOT NULL default '0',
             `add_files_access_id` int(11) unsigned NOT NULL default '0',
             `manage_files_access_id` int(11) unsigned NOT NULL default '0',
             `image` varchar(255) NOT NULL default '',
              PRIMARY KEY (`id`),
              KEY `is_active` (`is_active`),
              KEY `visibility` (`visibility`)
            ) ENGINE=MyISAM",
        #################################################################################
        DBPREFIX.'module_downloads_category_locale' => "CREATE TABLE `".DBPREFIX."module_downloads_category_locale` (
             `lang_id` int(11) unsigned NOT NULL default '0',
             `category_id` int(11) unsigned NOT NULL default '0',
             `name` varchar(255) NOT NULL default '',
             `description` text NOT NULL,
              PRIMARY KEY (`lang_id`,`category_id`),
              FULLTEXT KEY `name` (`name`),
              FULLTEXT KEY `description` (`description`)
            ) ENGINE=MyISAM",
        #################################################################################
        DBPREFIX.'module_downloads_download_locale' => "CREATE TABLE `".DBPREFIX."module_downloads_download_locale` (
             `lang_id` int(11) unsigned NOT NULL default '0',
             `download_id` int(11) unsigned NOT NULL default '0',
             `name` varchar(255) NOT NULL default '',
             `description` text NOT NULL,
              PRIMARY KEY (`lang_id`,`download_id`),
              FULLTEXT KEY `name` (`name`),
              FULLTEXT KEY `description` (`description`)
            ) ENGINE=MyISAM",
        #################################################################################
        DBPREFIX.'module_downloads_rel_download_category' => "CREATE TABLE `".DBPREFIX."module_downloads_rel_download_category` (
             `download_id` int(10) unsigned NOT NULL default '0',
             `category_id` int(10) unsigned NOT NULL default '0',
             `order` int(3) unsigned NOT NULL default '0',
              PRIMARY KEY (`download_id`,`category_id`)
            ) ENGINE=MyISAM",
        #################################################################################
        DBPREFIX.'module_downloads_rel_download_download' => "CREATE TABLE `".DBPREFIX."module_downloads_rel_download_download` (
             `id1` int(10) unsigned NOT NULL default '0',
             `id2` int(10) unsigned NOT NULL default '0',
              PRIMARY KEY (`id1`,`id2`)
            ) ENGINE=MyISAM",
        #################################################################################
        DBPREFIX.'module_downloads_settings' => "CREATE TABLE `".DBPREFIX."module_downloads_settings` (
             `id` int(11) NOT NULL auto_increment,
             `name` varchar(32) NOT NULL default '',
             `value` varchar(255) NOT NULL default '',
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM"
   );

    foreach ($tables as $name => $query) {
        #print_r($arrTables);
        if (in_array($name, $arrTables)) {
            continue;
        }
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }


    try{
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_downloads_download',
            array(
                'id'                 => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'type'               => array('type' => 'ENUM(\'file\',\'url\')', 'notnull' => true, 'default' => 'file'),
                'mime_type'          => array('type' => 'ENUM(\'image\',\'document\',\'pdf\',\'media\',\'archive\',\'application\',\'link\')', 'notnull' => true, 'default' => 'image'),
                'source'             => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'source_name'        => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'icon'               => array('type' => 'ENUM(\'_blank\',\'avi\',\'bmp\',\'css\',\'doc\',\'dot\',\'exe\',\'fla\',\'gif\',\'htm\',\'html\',\'inc\',\'jpg\',\'js\',\'mp3\',\'nfo\',\'pdf\',\'php\',\'png\',\'pps\',\'ppt\',\'rar\',\'swf\',\'txt\',\'wma\',\'xls\',\'zip\')', 'notnull' => true, 'default' => '_blank'),
                'size'               => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'image'              => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'owner_id'           => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'access_id'          => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'license'            => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'version'            => array('type' => 'VARCHAR(10)', 'notnull' => true, 'default' => ''),
                'author'             => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => ''),
                'website'            => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'ctime'              => array('type' => 'INT(14)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'mtime'              => array('type' => 'INT(14)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'is_active'          => array('type' => 'TINYINT(3)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'visibility'         => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1'),
                'order'              => array('type' => 'INT(3)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'views'              => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'download_count'     => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'expiration'         => array('type' => 'INT(14)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'validity'           => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0')
            ),
            array(
                'is_active'          => array('fields' => array('is_active')),
                'visibility'         => array('fields' => array('visibility'))
            )
        );
    }
    catch (\Cx\Lib\UpdateException $e) {
        // we COULD do something else here..
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }




    /************************************************
    * EXTENSION:    Initial adding of the           *
    *               settings values                 *
    * ADDED:        Contrexx v2.1.0                 *
    ************************************************/
    $arrSettings = array(
        'overview_cols_count'           => '2',
        'overview_max_subcats'          => '5',
        'use_attr_size'                 => '1',
        'use_attr_license'              => '1',
        'use_attr_version'              => '1',
        'use_attr_author'               => '1',
        'use_attr_website'              => '1',
        'most_viewed_file_count'        => '5',
        'most_downloaded_file_count'    => '5',
        'most_popular_file_count'       => '5',
        'newest_file_count'             => '5',
        'updated_file_count'            => '5',
        'new_file_time_limit'           => '604800',
        'updated_file_time_limit'       => '604800',
        'associate_user_to_groups'      => ''
    );

    foreach ($arrSettings as $name => $value) {
        $query = "SELECT 1 FROM `".DBPREFIX."module_downloads_settings` WHERE `name` = '".$name."'";
        $objResult = $objDatabase->SelectLimit($query, 1);
        if ($objResult) {
            if ($objResult->RecordCount() == 0) {
                $query = "INSERT INTO `".DBPREFIX."module_downloads_settings` (`name`, `value`) VALUES ('".$name."', '".$value."')";
                if ($objDatabase->Execute($query) === false) {
                    return _databaseError($query, $objDatabase->ErrorMsg());
                }
            }
        } else {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }




    /************************************************
    * BUGFIX:   Set write access to the upload dir  *
    ************************************************/
    if (\Cx\Lib\FileSystem\FileSystem::makeWritable(ASCMS_DOWNLOADS_IMAGES_PATH)) {
        if ($mediaDir = @opendir(ASCMS_DOWNLOADS_IMAGES_PATH)) {
            while($file = readdir($mediaDir)) {
                if ($file != '.' && $file != '..') {
                    if (!\Cx\Lib\FileSystem\FileSystem::makeWritable(ASCMS_DOWNLOADS_IMAGES_PATH.'/'.$file)) {
                        setUpdateMsg(sprintf($_ARRAYLANG['TXT_SET_WRITE_PERMISSON_TO_FILE'], ASCMS_DOWNLOADS_IMAGES_PATH.'/'.$file, $_CORELANG['TXT_UPDATE_TRY_AGAIN']), 'msg');
                        return false;
                    }
                }
            }
        } else {
            setUpdateMsg(sprintf($_ARRAYLANG['TXT_SET_WRITE_PERMISSON_TO_DIR_AND_CONTENT'], ASCMS_DOWNLOADS_IMAGES_PATH.'/', $_CORELANG['TXT_UPDATE_TRY_AGAIN']), 'msg');
            return false;
        }
    } else {
        setUpdateMsg(sprintf($_ARRAYLANG['TXT_SET_WRITE_PERMISSON_TO_DIR_AND_CONTENT'], ASCMS_DOWNLOADS_IMAGES_PATH.'/', $_CORELANG['TXT_UPDATE_TRY_AGAIN']), 'msg');
        return false;
    }




    /************************************************
    * EXTENSION:    Groups                          *
    * ADDED:        Contrexx v2.1.2                 *
    ************************************************/
    try{
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_downloads_group',
            array(
                'id'         => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'is_active'  => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '1'),
                'type'       => array('type' => 'ENUM(\'file\',\'url\')', 'notnull' => true, 'default' => 'file'),
                'info_page'  => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '')
            )
        );

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_downloads_group_locale',
            array(
                'lang_id'    => array('type' => 'INT(11)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
                'group_id'   => array('type' => 'INT(11)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
                'name'       => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '')
            ),
            array(
                'name'       => array('fields' => array('name'), 'type' => 'FULLTEXT')
            )
        );

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_downloads_rel_group_category',
            array(
                'group_id'       => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
                'category_id'    => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true)
            )
        );
    }
    catch (\Cx\Lib\UpdateException $e) {
        // we COULD do something else here..
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }




    /*******************************************************
    * EXTENSION:    Localisation of download source fields *
    * ADDED:        Contrexx v3.0.0                        *
    ********************************************************/
    try {
        $arrColumns = $objDatabase->MetaColumns(DBPREFIX.'module_downloads_download_locale');
        if ($arrColumns === false) {
            setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_downloads_download_locale'));
            return false;
        }

        if (!isset($arrColumns['SOURCE']) && !isset($arrColumns['SOURCE_NAME'])) {
            \Cx\Lib\UpdateUtil::sql('
                ALTER TABLE `'.DBPREFIX.'module_downloads_download_locale`
                ADD `source` VARCHAR(255) NULL DEFAULT NULL AFTER `name`,
                ADD `source_name` VARCHAR(255) NULL DEFAULT NULL AFTER `source`,
                ADD `metakeys` TEXT NOT NULL AFTER `description`
            ');
            \Cx\Lib\UpdateUtil::sql('
                UPDATE `'.DBPREFIX.'module_downloads_download` AS download INNER JOIN `'.DBPREFIX.'module_downloads_download_locale` AS download_locale ON download.id = download_locale.download_id
                SET download_locale.source = download.source, download_locale.source_name = download.source_name
            ');
            \Cx\Lib\UpdateUtil::sql('
                ALTER TABLE `'.DBPREFIX.'module_downloads_download`
                DROP COLUMN `source`,
                DROP COLUMN `source_name`
            ');
        }
    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }

    return true;
}



function _downloadsInstall()
{
    try {

        /**************************************************************************
         * EXTENSION:   Initial creation (for contrexx editions <> premium with   *
         *              version < 3.0.0 which have this module not yet installed) *
         *                                                                        *
         * ADDED:       Contrexx v3.0.1                                           *
         **************************************************************************/
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_downloads_category',
            array(
                'id'                                 => array('type' => 'INT(11)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'parent_id'                          => array('type' => 'INT(11)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'id'),
                'is_active'                          => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'after' => 'parent_id'),
                'visibility'                         => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'after' => 'is_active'),
                'owner_id'                           => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'visibility'),
                'order'                              => array('type' => 'INT(3)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'owner_id'),
                'deletable_by_owner'                 => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'after' => 'order'),
                'modify_access_by_owner'             => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'after' => 'deletable_by_owner'),
                'read_access_id'                     => array('type' => 'INT(11)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'modify_access_by_owner'),
                'add_subcategories_access_id'        => array('type' => 'INT(11)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'read_access_id'),
                'manage_subcategories_access_id'     => array('type' => 'INT(11)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'add_subcategories_access_id'),
                'add_files_access_id'                => array('type' => 'INT(11)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'manage_subcategories_access_id'),
                'manage_files_access_id'             => array('type' => 'INT(11)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'add_files_access_id'),
                'image'                              => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'manage_files_access_id')
            ),
            array(
                'is_active'                          => array('fields' => array('is_active')),
                'visibility'                         => array('fields' => array('visibility'))
            ),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_downloads_category` (`id`, `parent_id`, `is_active`, `visibility`, `owner_id`, `order`, `deletable_by_owner`, `modify_access_by_owner`, `read_access_id`, `add_subcategories_access_id`, `manage_subcategories_access_id`, `add_files_access_id`, `manage_files_access_id`, `image`)
            VALUES  (10, 0, 1, 1, 1, 3, 1, 1, 61, 62, 63, 64, 65, ''),
                    (12, 10, 1, 1, 1, 2, 1, 1, 53, 70, 71, 72, 73, ''),
                    (13, 10, 1, 1, 1, 1, 1, 1, 54, 74, 75, 76, 77, ''),
                    (14, 0, 1, 1, 1, 1, 1, 1, 0, 53, 54, 55, 56, '')
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_downloads_category_locale',
            array(
                'lang_id'        => array('type' => 'INT(11)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
                'category_id'    => array('type' => 'INT(11)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true, 'after' => 'lang_id'),
                'name'           => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'category_id'),
                'description'    => array('type' => 'text', 'after' => 'name')
            ),
            array(
                'name'           => array('fields' => array('name'), 'type' => 'FULLTEXT'),
                'description'    => array('fields' => array('description'), 'type' => 'FULLTEXT')
            ),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_downloads_category_locale` (`lang_id`, `category_id`, `name`, `description`)
            VALUES  (1, 10, 'Community', 'Dateiaustausch der Mitglieder'),
                    (1, 12, 'Tools', ''),
                    (1, 13, 'Bilder', ''),
                    (1, 14, 'Contrexx Produktbilder', 'Bilder zu den einzelnen Contrexx Editions'),
                    (2, 10, 'Community', 'Data Exchange for members'),
                    (2, 12, 'Tools', ''),
                    (2, 13, 'Bilder', ''),
                    (2, 14, 'Contrexx Product Pictures', 'Pictures of each Contrexx Edition'),
                    (3, 10, 'Community', 'Data Exchange for members'),
                    (3, 12, 'Tools', ''),
                    (3, 13, 'Bilder', ''),
                    (3, 14, 'Contrexx Product Pictures', 'Pictures of each Contrexx Edition'),
                    (4, 10, 'Community', 'Data Exchange for members'),
                    (4, 12, 'Tools', ''),
                    (4, 13, 'Bilder', ''),
                    (4, 14, 'Contrexx Product Pictures', 'Pictures of each Contrexx Edition'),
                    (5, 10, 'Community', 'Data Exchange for members'),
                    (5, 12, 'Tools', ''),
                    (5, 13, 'Bilder', ''),
                    (5, 14, 'Contrexx Product Pictures', 'Pictures of each Contrexx Edition'),
                    (6, 10, 'Community', 'Data Exchange for members'),
                    (6, 12, 'Tools', ''),
                    (6, 13, 'Bilder', ''),
                    (6, 14, 'Contrexx Product Pictures', 'Pictures of each Contrexx Edition')
            ON DUPLICATE KEY UPDATE `lang_id` = `lang_id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_downloads_download',
            array(
                'id'                 => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'type'               => array('type' => 'ENUM(\'file\',\'url\')', 'notnull' => true, 'default' => 'file', 'after' => 'id'),
                'mime_type'          => array('type' => 'ENUM(\'image\',\'document\',\'pdf\',\'media\',\'archive\',\'application\',\'link\')', 'notnull' => true, 'default' => 'image', 'after' => 'type'),
                'icon'               => array('type' => 'ENUM(\'_blank\',\'avi\',\'bmp\',\'css\',\'doc\',\'dot\',\'exe\',\'fla\',\'gif\',\'htm\',\'html\',\'inc\',\'jpg\',\'js\',\'mp3\',\'nfo\',\'pdf\',\'php\',\'png\',\'pps\',\'ppt\',\'rar\',\'swf\',\'txt\',\'wma\',\'xls\',\'zip\')', 'notnull' => true, 'default' => '_blank', 'after' => 'mime_type'),
                'size'               => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'icon'),
                'image'              => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'size'),
                'owner_id'           => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'image'),
                'access_id'          => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'owner_id'),
                'license'            => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'access_id'),
                'version'            => array('type' => 'VARCHAR(10)', 'notnull' => true, 'default' => '', 'after' => 'license'),
                'author'             => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => '', 'after' => 'version'),
                'website'            => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'author'),
                'ctime'              => array('type' => 'INT(14)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'website'),
                'mtime'              => array('type' => 'INT(14)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'ctime'),
                'is_active'          => array('type' => 'TINYINT(3)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'mtime'),
                'visibility'         => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'after' => 'is_active'),
                'order'              => array('type' => 'INT(3)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'visibility'),
                'views'              => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'order'),
                'download_count'     => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'views'),
                'expiration'         => array('type' => 'INT(14)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'download_count'),
                'validity'           => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'expiration')
            ),
            array(
                'is_active'          => array('fields' => array('is_active')),
                'visibility'         => array('fields' => array('visibility'))
            ),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_downloads_download` (`id`, `type`, `mime_type`, `icon`, `size`, `image`, `owner_id`, `access_id`, `license`, `version`, `author`, `website`, `ctime`, `mtime`, `is_active`, `visibility`, `order`, `views`, `download_count`, `expiration`, `validity`)
            VALUES  (4, 'file', 'image', 'jpg', 114688, '/images/downloads/produktbild_contrexx_personal.jpg', 1, 0, 'Free to Use', '3.0', 'Comvation AG', 'http://www.contrexx.com', 1292505824, 1348228278, 1, 1, 0, 12, 27, 0, 0),
                    (5, 'file', 'image', 'jpg', 98304, '/images/downloads/produktbild_contrexx_non_profit.jpg', 1, 0, 'Free to Use', '3.0', 'Comvation AG', 'http://www.contrexx.com', 1292506378, 1348228289, 1, 1, 0, 8, 3, 0, 0),
                    (6, 'file', 'image', 'jpg', 100352, '/images/downloads/produktbild_contrexx_small_business.jpg', 1, 0, 'Free to Use', '3.0', 'Comvation AG', 'http://www.contrexx.com', 1292506490, 1348228299, 1, 1, 0, 8, 4, 0, 0),
                    (7, 'file', 'image', 'jpg', 88064, '/images/downloads/produktbild_contrexx_premium.jpg', 1, 0, 'Free to Use', '3.0', 'Comvation AG', 'http://www.contrexx.com', 1292506574, 1348228311, 1, 1, 0, 16, 8, 0, 0),
                    (12, 'file', 'document', '_blank', 0, '/images/downloads/produktbild_contrexx_enterprise.jpg', 1, 0, 'Free to Use', '3.0', 'Comvation AG', 'http://www.contrexx.com', 1347628955, 1348228264, 1, 1, 0, 1, 0, 0, 0),
                    (9, 'file', 'pdf', 'pdf', 136192, '', 1, 0, 'Free to Use', '1.0', 'Comvation AG', 'http://www.contrexx.com', 1292601284, 1348226752, 1, 1, 0, 4, 3, 0, 0),
                    (10, 'file', 'pdf', 'pdf', 135168, 'images/downloads/factsheet_contrexx_basic.pdf', 1, 0, 'Free to Use', '1.0', 'Comvation AG', 'http://www.contrexx.com', 1292601393, 1292601393, 1, 1, 0, 1, 0, 0, 0),
                    (11, 'file', 'pdf', 'pdf', 111616, 'images/downloads/factsheet_contrexx_premium.pdf', 1, 0, 'Free to Use', '1.0', 'Comvation AG', 'http://www.contrexx.com', 1292601456, 1292601456, 1, 1, 0, 1, 0, 0, 0)
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_downloads_download_locale',
            array(
                'lang_id'        => array('type' => 'INT(11)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
                'download_id'    => array('type' => 'INT(11)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true, 'after' => 'lang_id'),
                'name'           => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'download_id'),
                'source'         => array('type' => 'VARCHAR(255)', 'notnull' => false, 'after' => 'name'),
                'source_name'    => array('type' => 'VARCHAR(255)', 'notnull' => false, 'after' => 'source'),
                'description'    => array('type' => 'text', 'after' => 'source_name'),
                'metakeys'       => array('type' => 'text', 'after' => 'description')
            ),
            array(
                'name'           => array('fields' => array('name'), 'type' => 'FULLTEXT'),
                'description'    => array('fields' => array('description'), 'type' => 'FULLTEXT')
            ),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_downloads_download_locale` (`lang_id`, `download_id`, `name`, `source`, `source_name`, `description`, `metakeys`)
            VALUES  (1, 4, 'Contrexx® Personal', '/images/downloads/produktbild_contrexx_open_source.jpg', 'produktbild_contrexx_open_source.jpg', 'Produktbild von Contrexx® Personal', ''),
                    (2, 4, 'Contrexx® Personal', '/images/downloads/produktbild_contrexx_open_source.jpg', 'produktbild_contrexx_open_source.jpg', 'Produktbild von Contrexx® Personal', ''),
                    (3, 4, 'Contrexx® Personal', '', 'produktbild_contrexx_open_source.jpg', '', ''),
                    (1, 5, 'Contrexx® NonProfit', 'images/downloads/produktbild_contrexx_education.jpg', 'produktbild_contrexx_education.jpg', 'Produktbild von Contrexx® NonProfit', ''),
                    (2, 5, 'Contrexx® NonProfit', 'images/downloads/produktbild_contrexx_education.jpg', 'produktbild_contrexx_education.jpg', 'Produktbild von Contrexx® NonProfit', ''),
                    (3, 5, 'Contrexx® NonProfit', '', 'produktbild_contrexx_education.jpg', '', ''),
                    (1, 6, 'Contrexx® Small Business', 'images/downloads/produktbild_contrexx_basic.jpg', 'produktbild_contrexx_basic.jpg', 'Produktbild von Contrexx® Small Business', ''),
                    (2, 6, 'Contrexx® Small Business', 'images/downloads/produktbild_contrexx_basic.jpg', 'produktbild_contrexx_basic.jpg', 'Produktbild von Contrexx® Small Business', ''),
                    (3, 6, 'Contrexx® Small Business', '', 'produktbild_contrexx_basic.jpg', '', ''),
                    (1, 7, 'Contrexx® Premium', 'images/downloads/produktbild_contrexx_premium.jpg', 'produktbild_contrexx_premium.jpg', 'Produktbild von Contrexx® Premium', ''),
                    (2, 7, 'Contrexx® Premium', 'images/downloads/produktbild_contrexx_premium.jpg', 'produktbild_contrexx_premium.jpg', 'Produktbild von Contrexx® Premium', ''),
                    (3, 7, 'Contrexx® Premium', '', 'produktbild_contrexx_premium.jpg', '', ''),
                    (1, 12, 'Contrexx® Enterprise', '', '', 'Produktbild von Contrexx® Enterprise', ''),
                    (2, 12, 'Contrexx® Enterprise', '', '', 'Produktbild von Contrexx® Enterprise', ''),
                    (3, 12, 'Contrexx® Enterprise', '', '', '', ''),
                    (1, 9, 'Contrexx® NonProfit', 'images/downloads/factsheet_contrexx_education.pdf', 'factsheet_contrexx_education.pdf', 'Contrexx NonProfit Factsheet', ''),
                    (2, 9, 'Contrexx® NonProfit', 'images/downloads/factsheet_contrexx_education.pdf', 'factsheet_contrexx_education.pdf', 'Contrexx NonProfit Factsheet', ''),
                    (3, 9, 'Contrexx® NonProfit', '', 'factsheet_contrexx_education.pdf', '', ''),
                    (1, 10, 'Contrexx Basic', 'images/downloads/factsheet_contrexx_basic.pdf', 'factsheet_contrexx_basic.pdf', 'Contrexx Basic Factsheet', ''),
                    (2, 10, 'Contrexx Basic', 'images/downloads/factsheet_contrexx_basic.pdf', 'factsheet_contrexx_basic.pdf', 'Contrexx Basic Factsheet', ''),
                    (3, 10, 'Contrexx Basic', 'images/downloads/factsheet_contrexx_basic.pdf', 'factsheet_contrexx_basic.pdf', 'Contrexx Basic Factsheet', ''),
                    (4, 10, 'Contrexx Basic', 'images/downloads/factsheet_contrexx_basic.pdf', 'factsheet_contrexx_basic.pdf', 'Contrexx Basic Factsheet', ''),
                    (5, 10, 'Contrexx Basic', 'images/downloads/factsheet_contrexx_basic.pdf', 'factsheet_contrexx_basic.pdf', 'Contrexx Basic Factsheet', ''),
                    (6, 10, 'Contrexx Basic', 'images/downloads/factsheet_contrexx_basic.pdf', 'factsheet_contrexx_basic.pdf', 'Contrexx Basic Factsheet', ''),
                    (1, 11, 'Contrexx Premium', 'images/downloads/factsheet_contrexx_premium.pdf', 'factsheet_contrexx_premium.pdf', 'Contrexx Premium Factsheet', ''),
                    (2, 11, 'Contrexx Premium', 'images/downloads/factsheet_contrexx_premium.pdf', 'factsheet_contrexx_premium.pdf', 'Contrexx Premium Factsheet', ''),
                    (3, 11, 'Contrexx Premium', 'images/downloads/factsheet_contrexx_premium.pdf', 'factsheet_contrexx_premium.pdf', 'Contrexx Premium Factsheet', ''),
                    (4, 11, 'Contrexx Premium', 'images/downloads/factsheet_contrexx_premium.pdf', 'factsheet_contrexx_premium.pdf', 'Contrexx Premium Factsheet', ''),
                    (5, 11, 'Contrexx Premium', 'images/downloads/factsheet_contrexx_premium.pdf', 'factsheet_contrexx_premium.pdf', 'Contrexx Premium Factsheet', ''),
                    (6, 11, 'Contrexx Premium', 'images/downloads/factsheet_contrexx_premium.pdf', 'factsheet_contrexx_premium.pdf', 'Contrexx Premium Factsheet', '')
            ON DUPLICATE KEY UPDATE `lang_id` = `lang_id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_downloads_group',
            array(
                'id'             => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'is_active'      => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '1', 'after' => 'id'),
                'type'           => array('type' => 'ENUM(\'file\',\'url\')', 'notnull' => true, 'default' => 'file', 'after' => 'is_active'),
                'info_page'      => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'type')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_downloads_group_locale',
            array(
                'lang_id'        => array('type' => 'INT(11)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
                'group_id'       => array('type' => 'INT(11)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true, 'after' => 'lang_id'),
                'name'           => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'group_id')
            ),
            array(
                'name'           => array('fields' => array('name'), 'type' => 'FULLTEXT')
            ),
            'MyISAM',
            'cx3upgrade'
        );

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_downloads_rel_download_category',
            array(
                'download_id'    => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
                'category_id'    => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true, 'after' => 'download_id'),
                'order'          => array('type' => 'INT(3)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'category_id')
            ),
            array(),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_downloads_rel_download_category` (`download_id`, `category_id`, `order`)
            VALUES  (4, 14, 1),
                    (5, 14, 2),
                    (6, 14, 3),
                    (7, 14, 4),
                    (12, 14, 0)
            ON DUPLICATE KEY UPDATE `download_id` = `download_id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_downloads_rel_download_download',
            array(
                'id1'    => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
                'id2'    => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true, 'after' => 'id1')
            ),
            array(),
            'MyISAM',
            'cx3upgrade'
        );

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_downloads_rel_group_category',
            array(
                'group_id'       => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
                'category_id'    => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true, 'after' => 'group_id')
            ),
            array(),
            'MyISAM',
            'cx3upgrade'
        );

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_downloads_settings',
            array(
                'id'         => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'name'       => array('type' => 'VARCHAR(32)', 'notnull' => true, 'default' => '', 'after' => 'id'),
                'value'      => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'name')
            ),
            array(),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_downloads_settings` (`id`, `name`, `value`)
            VALUES  (1, 'overview_cols_count', '2'),
                    (2, 'overview_max_subcats', '5'),
                    (3, 'use_attr_size', '1'),
                    (4, 'use_attr_license', '1'),
                    (5, 'use_attr_version', '1'),
                    (6, 'use_attr_author', '1'),
                    (7, 'use_attr_website', '1'),
                    (8, 'most_viewed_file_count', '5'),
                    (9, 'most_downloaded_file_count', '5'),
                    (10, 'most_popular_file_count', '5'),
                    (11, 'newest_file_count', '5'),
                    (12, 'updated_file_count', '5'),
                    (13, 'new_file_time_limit', '604800'),
                    (14, 'updated_file_time_limit', '604800'),
                    (15, 'associate_user_to_groups', ''),
                    (16, 'use_attr_metakeys', '1')
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }
}
