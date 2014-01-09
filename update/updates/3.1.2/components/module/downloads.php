<?php

function _downloadsUpdate()
{
    global $objDatabase, $_ARRAYLANG, $_CORELANG, $objUpdate, $_CONFIG;

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




    /**********************************************************
    * EXTENSION:    Increase length of download source fields *
    * ADDED:        Contrexx v3.1.0                           *
    **********************************************************/
    try {
        if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '3.1.0')) {

            \Cx\Lib\UpdateUtil::sql('
                ALTER TABLE `'.DBPREFIX.'module_downloads_download_locale`
                CHANGE `source` `source` VARCHAR(1024) NULL DEFAULT NULL,
                CHANGE `source_name` `source_name` VARCHAR(1024) NULL DEFAULT NULL
            ');

        }
    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }


    /**********************************************************
    * EXTENSION:    Add access ids of "editing all downloads" *
    *               to groups which had access to "administer"*
    * ADDED:        Contrexx v3.1.1                           *
    **********************************************************/
    try {
        if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '3.1.0.2')) {
            $result = \Cx\Lib\UpdateUtil::sql("SELECT `group_id` FROM `" . DBPREFIX . "access_group_static_ids` WHERE access_id = 142 GROUP BY `group_id`");
            if ($result !== false) {
                while (!$result->EOF) {
                    \Cx\Lib\UpdateUtil::sql("INSERT IGNORE INTO `" . DBPREFIX . "access_group_static_ids` (`access_id`, `group_id`)
                                                VALUES (143, " . intval($result->fields['group_id']) . ")");
                    $result->MoveNext();
                }
            }
        }
    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }

    return true;
}
