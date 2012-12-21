<?php
function _directoryUpdate() {
    global $objDatabase, $_ARRAYLANG;

    /// 2.0

    $arrColumns = $objDatabase->MetaColumns(DBPREFIX.'module_directory_dir');
    if ($arrColumns === false) {
        setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_directory_dir'));
        return false;
    }

    $arrNewCols = array(
                'LONGITUDE' => array(
                        'type'      => 'DECIMAL( 18, 15 )',
                        'default'   => '0',
                        'after'     => 'premium',
                    ),
                'LATITUDE'  => array(
                        'type'      => 'DECIMAL( 18, 15 )',
                        'default'   => '0',
                        'after'     => 'longitude',
                    ),
                'ZOOM'      => array(
                        'type'      => 'DECIMAL( 18, 15 )',
                        'default'   => '1',
                        'after'     => 'latitude',
                    ),
                'COUNTRY'   => array(
                        'type'      => 'VARCHAR( 255 )',
                        'default'   => '',
                        'after'     => 'city',
                ));
    foreach ($arrNewCols as $col => $arrAttr) {
        if (!isset($arrColumns[$col])) {
            $query = "ALTER TABLE `".DBPREFIX."module_directory_dir` ADD `".strtolower($col)."` ".$arrAttr['type']." NOT NULL DEFAULT '".$arrAttr['default']."' AFTER `".$arrAttr['after']."`";

            if ($objDatabase->Execute($query) === false) {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
    }

    $inputColumns = '(`id`, `typ`, `name`, `title`, `active`, `active_backend`, `is_required`, `read_only`, `sort`, `exp_search`, `is_search`)';

    $arrInputs = array(
        69 => "INSERT INTO `".DBPREFIX."module_directory_inputfields` ".$inputColumns." VALUES (69, 13, 'googlemap', 'TXT_DIR_F_GOOGLEMAP', 1, 1, 0, 0, 6, 0, 0)",
        70 => "INSERT INTO `".DBPREFIX."module_directory_inputfields` ".$inputColumns." VALUES (70, 3, 'country', 'TXT_DIR_F_COUNTRY', 1, 1, 1, 0, 1, 0, 0)",
    );

    foreach ($arrInputs as $id => $queryInputs) {
        $query = "SELECT id FROM ".DBPREFIX."module_directory_inputfields WHERE id=".$id;
        $objCheck = $objDatabase->SelectLimit($query, 1);
        if ($objCheck !== false) {
            if ($objCheck->RecordCount() == 0) {
                if ($objDatabase->Execute($queryInputs) === false) {
                    return _databaseError($queryInputs, $objDatabase->ErrorMsg());
                }
            }
        } else {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    $query = "SELECT setid FROM ".DBPREFIX."module_directory_settings WHERE setname='country'";
    $objCheck = $objDatabase->SelectLimit($query, 1);
    if ($objCheck !== false) {
        if ($objCheck->RecordCount() == 0) {
            $query =    "INSERT INTO `".DBPREFIX."module_directory_settings` ( `setid` , `setname` , `setvalue` ,  `settyp` )
                        VALUES (NULL, 'country', ',Schweiz,Deutschland,Österreich', 0)";
            if ($objDatabase->Execute($query) === false) {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
    } else {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }

    $query = "ALTER TABLE `".DBPREFIX."module_directory_dir` CHANGE `spez_field_21` `spez_field_21` VARCHAR( 255 ) NOT NULL DEFAULT ''";
    if ($objDatabase->Execute($query) === false) {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }

    $query = "ALTER TABLE `".DBPREFIX."module_directory_dir` CHANGE `spez_field_22` `spez_field_22` VARCHAR( 255 ) NOT NULL DEFAULT ''";
    if ($objDatabase->Execute($query) === false) {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }

    $query = "SELECT setid FROM ".DBPREFIX."module_directory_settings WHERE setname='pagingLimit'";
    $objCheck = $objDatabase->SelectLimit($query, 1);
    if ($objCheck !== false) {
        if ($objCheck->RecordCount() == 0) {
            $query =    "INSERT INTO `".DBPREFIX."module_directory_settings` ( `setid` , `setname` , `setvalue` ,  `settyp` )
                        VALUES (NULL, 'pagingLimit', '20', '1')";
            if ($objDatabase->Execute($query) === false) {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
    } else {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }

    $query = "SELECT setid FROM ".DBPREFIX."module_directory_settings WHERE setname='googlemap_start_location'";
    $objCheck = $objDatabase->SelectLimit($query, 1);
    if ($objCheck !== false) {
        if ($objCheck->RecordCount() == 0) {
            $query =    "INSERT INTO `".DBPREFIX."module_directory_settings` ( `setid` , `setname` , `setvalue` ,  `settyp` )
                        VALUES (NULL, 'googlemap_start_location', '46:8:1', '1')";
            if ($objDatabase->Execute($query) === false) {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
    } else {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }


    /// 2.1

    $query = "SELECT setid FROM ".DBPREFIX."module_directory_settings WHERE setname='youtubeWidth'";
    $objCheck = $objDatabase->SelectLimit($query, 1);
    if ($objCheck !== false) {
        if ($objCheck->RecordCount() == 0) {
            $query =    "INSERT INTO `".DBPREFIX."module_directory_settings` ( `setid` , `setname` , `setvalue` ,  `settyp` )
                         VALUES (NULL , 'youtubeWidth', '400', '1')";
            if ($objDatabase->Execute($query) === false) {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
    } else {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }

    $query = "SELECT setid FROM ".DBPREFIX."module_directory_settings WHERE setname='youtubeHeight'";
    $objCheck = $objDatabase->SelectLimit($query, 1);
    if ($objCheck !== false) {
        if ($objCheck->RecordCount() == 0) {
            $query =    "INSERT INTO `".DBPREFIX."module_directory_settings` ( `setid` , `setname` , `setvalue` ,  `settyp` )
                         VALUES (NULL , 'youtubeHeight', '300', '1')";
            if ($objDatabase->Execute($query) === false) {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
    } else {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }

    $query = "SELECT id FROM ".DBPREFIX."module_directory_inputfields WHERE name='youtube'";
    $objCheck = $objDatabase->SelectLimit($query, 1);
    if ($objCheck !== false) {
        if ($objCheck->RecordCount() == 0) {
            $query =    "INSERT INTO `".DBPREFIX."module_directory_inputfields` (`id` ,`typ` ,`name` ,`title` ,`active` ,`active_backend` ,`is_required` ,`read_only` ,`sort` ,`exp_search` ,`is_search`)
                         VALUES (NULL , '1', 'youtube', 'TXT_DIRECTORY_YOUTUBE', '0', '0', '0', '0', '0', '0', '0')";
            if ($objDatabase->Execute($query) === false) {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
    } else {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }

    $arrColumns = $objDatabase->MetaColumns(DBPREFIX.'module_directory_dir');
    if ($arrColumns === false) {
        setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_directory_dir'));
        return false;
    }

    if (!array_key_exists("YOUTUBE", $arrColumns)) {
        $query = "ALTER TABLE `".DBPREFIX."module_directory_dir` ADD `youtube` MEDIUMTEXT NOT NULL;";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    $query   = "ALTER TABLE `".DBPREFIX."module_directory_dir`
                CHANGE `logo` `logo` VARCHAR(50) NULL,
                CHANGE `map` `map` VARCHAR(255) NULL,
                CHANGE `lokal` `lokal` VARCHAR(255) NULL,
                CHANGE `spez_field_11` `spez_field_11` VARCHAR(255) NULL,
                CHANGE `spez_field_12` `spez_field_12` VARCHAR(255) NULL,
                CHANGE `spez_field_13` `spez_field_13` VARCHAR(255) NULL,
                CHANGE `spez_field_14` `spez_field_14` VARCHAR(255) NULL,
                CHANGE `spez_field_15` `spez_field_15` VARCHAR(255) NULL,
                CHANGE `spez_field_16` `spez_field_16` VARCHAR(255) NULL,
                CHANGE `spez_field_17` `spez_field_17` VARCHAR(255) NULL,
                CHANGE `spez_field_18` `spez_field_18` VARCHAR(255) NULL,
                CHANGE `spez_field_19` `spez_field_19` VARCHAR(255) NULL,
                CHANGE `spez_field_20` `spez_field_20` VARCHAR(255) NULL;";
    if ($objDatabase->Execute($query) === false) {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }


    //delete obsolete table  contrexx_module_directory_access
    try {
        \Cx\Lib\UpdateUtil::drop_table(DBPREFIX.'module_directory_access');
    } catch (\Cx\Lib\UpdateException $e) {
        DBG::trace();
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }

    /********************************
     * EXTENSION:   Fulltext key    *
     * ADDED:       Contrexx v3.0.0 *
     ********************************/
    try {
        $objResult = \Cx\Lib\UpdateUtil::sql('SHOW KEYS FROM `'.DBPREFIX.'module_directory_categories` WHERE  `Key_name` = "directoryindex" and (`Column_name`= "name" OR `Column_name` = "description")');
        if ($objResult && ($objResult->RecordCount() == 0)) {
            \Cx\Lib\UpdateUtil::sql('ALTER TABLE `'.DBPREFIX.'module_directory_categories` ADD FULLTEXT KEY `directoryindex` (`name`, `description`)');
        }
    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }

    return true;
}



function _directoryInstall()
{
    try {

        /**************************************************************************
         * EXTENSION:   Initial creation (for contrexx editions <> premium with   *
         *              version < 3.0.0 which have this module not yet installed) *
         *                                                                        *
         * ADDED:       Contrexx v3.0.1                                           *
         **************************************************************************/
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_directory_categories',
            array(
                'id'                 => array('type' => 'INT(6)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'parentid'           => array('type' => 'INT(6)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'id'),
                'name'               => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => '', 'after' => 'parentid'),
                'description'        => array('type' => 'VARCHAR(250)', 'notnull' => true, 'default' => '', 'after' => 'name'),
                'displayorder'       => array('type' => 'SMALLINT(6)', 'unsigned' => true, 'notnull' => true, 'default' => '1000', 'after' => 'description'),
                'metadesc'           => array('type' => 'VARCHAR(250)', 'notnull' => true, 'default' => '', 'after' => 'displayorder'),
                'metakeys'           => array('type' => 'VARCHAR(250)', 'notnull' => true, 'default' => '', 'after' => 'metadesc'),
                'showentries'        => array('type' => 'INT(1)', 'notnull' => true, 'default' => '1', 'after' => 'metakeys'),
                'status'             => array('type' => 'INT(1)', 'notnull' => true, 'default' => '1', 'after' => 'showentries')
            ),
            array(
                'name'               => array('fields' => array('name')),
                'parentid'           => array('fields' => array('parentid')),
                'displayorder'       => array('fields' => array('displayorder')),
                'status'             => array('fields' => array('status')),
                'directoryindex'     => array('fields' => array('name','description'), 'type' => 'FULLTEXT')
            ),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_directory_categories` (`id`, `parentid`, `name`, `description`, `displayorder`, `metadesc`, `metakeys`, `showentries`, `status`)
            VALUES  (4, 0, 'Contrexx specific Links  ', 'A selection of links that are all related to the Contrexx Open Source WCMS', 0, '', '', 1, 1),
                    (5, 0, 'Website Tools', 'Useful Tools', 0, '', '', 1, 1),
                    (9, 0, 'News', 'News about IT', 0, '', '', 1, 1)
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_directory_dir',
            array(
                'id'                 => array('type' => 'INT(7)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'title'              => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => '', 'after' => 'id'),
                'attachment'         => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'title'),
                'rss_file'           => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'attachment'),
                'rss_link'           => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'rss_file'),
                'link'               => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'rss_link'),
                'date'               => array('type' => 'VARCHAR(14)', 'notnull' => false, 'after' => 'link'),
                'description'        => array('type' => 'mediumtext', 'after' => 'date'),
                'platform'           => array('type' => 'VARCHAR(40)', 'notnull' => true, 'default' => '', 'after' => 'description'),
                'language'           => array('type' => 'VARCHAR(40)', 'notnull' => true, 'default' => '', 'after' => 'platform'),
                'relatedlinks'       => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'language'),
                'hits'               => array('type' => 'INT(9)', 'notnull' => true, 'default' => '0', 'after' => 'relatedlinks'),
                'status'             => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'hits'),
                'addedby'            => array('type' => 'VARCHAR(50)', 'notnull' => true, 'default' => '', 'after' => 'status'),
                'provider'           => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'addedby'),
                'ip'                 => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'provider'),
                'validatedate'       => array('type' => 'VARCHAR(14)', 'notnull' => true, 'default' => '', 'after' => 'ip'),
                'lastip'             => array('type' => 'VARCHAR(50)', 'notnull' => true, 'default' => '', 'after' => 'validatedate'),
                'popular_date'       => array('type' => 'VARCHAR(30)', 'notnull' => true, 'default' => '', 'after' => 'lastip'),
                'popular_hits'       => array('type' => 'INT(7)', 'notnull' => true, 'default' => '0', 'after' => 'popular_date'),
                'xml_refresh'        => array('type' => 'VARCHAR(15)', 'notnull' => true, 'default' => '', 'after' => 'popular_hits'),
                'canton'             => array('type' => 'VARCHAR(50)', 'notnull' => true, 'default' => '', 'after' => 'xml_refresh'),
                'searchkeys'         => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'canton'),
                'company_name'       => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => '', 'after' => 'searchkeys'),
                'street'             => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'company_name'),
                'zip'                => array('type' => 'VARCHAR(5)', 'notnull' => true, 'default' => '', 'after' => 'street'),
                'city'               => array('type' => 'VARCHAR(50)', 'notnull' => true, 'default' => '', 'after' => 'zip'),
                'country'            => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'city'),
                'phone'              => array('type' => 'VARCHAR(20)', 'notnull' => true, 'default' => '', 'after' => 'country'),
                'contact'            => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => '', 'after' => 'phone'),
                'information'        => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => '', 'after' => 'contact'),
                'fax'                => array('type' => 'VARCHAR(20)', 'notnull' => true, 'default' => '', 'after' => 'information'),
                'mobile'             => array('type' => 'VARCHAR(20)', 'notnull' => true, 'default' => '', 'after' => 'fax'),
                'mail'               => array('type' => 'VARCHAR(50)', 'notnull' => true, 'default' => '', 'after' => 'mobile'),
                'homepage'           => array('type' => 'VARCHAR(50)', 'notnull' => true, 'default' => '', 'after' => 'mail'),
                'industry'           => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => '', 'after' => 'homepage'),
                'legalform'          => array('type' => 'VARCHAR(50)', 'notnull' => true, 'default' => '', 'after' => 'industry'),
                'conversion'         => array('type' => 'VARCHAR(50)', 'notnull' => true, 'default' => '', 'after' => 'legalform'),
                'employee'           => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'conversion'),
                'foundation'         => array('type' => 'VARCHAR(10)', 'notnull' => true, 'default' => '', 'after' => 'employee'),
                'mwst'               => array('type' => 'VARCHAR(50)', 'notnull' => true, 'default' => '', 'after' => 'foundation'),
                'opening'            => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'mwst'),
                'holidays'           => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'opening'),
                'places'             => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'holidays'),
                'logo'               => array('type' => 'VARCHAR(50)', 'notnull' => false, 'after' => 'places'),
                'team'               => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'logo'),
                'portfolio'          => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'team'),
                'offers'             => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'portfolio'),
                'concept'            => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'offers'),
                'map'                => array('type' => 'VARCHAR(255)', 'notnull' => false, 'after' => 'concept'),
                'lokal'              => array('type' => 'VARCHAR(255)', 'notnull' => false, 'after' => 'map'),
                'spezial'            => array('type' => 'INT(4)', 'notnull' => true, 'default' => '0', 'after' => 'lokal'),
                'premium'            => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'spezial'),
                'longitude'          => array('type' => 'DECIMAL(18,15)', 'notnull' => true, 'default' => '0.000000000000000', 'after' => 'premium'),
                'latitude'           => array('type' => 'DECIMAL(18,15)', 'notnull' => true, 'default' => '0.000000000000000', 'after' => 'longitude'),
                'zoom'               => array('type' => 'DECIMAL(18,15)', 'notnull' => true, 'default' => '1.000000000000000', 'after' => 'latitude'),
                'spez_field_1'       => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'zoom'),
                'spez_field_2'       => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'spez_field_1'),
                'spez_field_3'       => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'spez_field_2'),
                'spez_field_4'       => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'spez_field_3'),
                'spez_field_5'       => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'spez_field_4'),
                'spez_field_6'       => array('type' => 'mediumtext', 'after' => 'spez_field_5'),
                'spez_field_7'       => array('type' => 'mediumtext', 'after' => 'spez_field_6'),
                'spez_field_8'       => array('type' => 'mediumtext', 'after' => 'spez_field_7'),
                'spez_field_9'       => array('type' => 'mediumtext', 'after' => 'spez_field_8'),
                'spez_field_10'      => array('type' => 'mediumtext', 'after' => 'spez_field_9'),
                'spez_field_11'      => array('type' => 'VARCHAR(255)', 'notnull' => false, 'after' => 'spez_field_10'),
                'spez_field_12'      => array('type' => 'VARCHAR(255)', 'notnull' => false, 'after' => 'spez_field_11'),
                'spez_field_13'      => array('type' => 'VARCHAR(255)', 'notnull' => false, 'after' => 'spez_field_12'),
                'spez_field_14'      => array('type' => 'VARCHAR(255)', 'notnull' => false, 'after' => 'spez_field_13'),
                'spez_field_15'      => array('type' => 'VARCHAR(255)', 'notnull' => false, 'after' => 'spez_field_14'),
                'spez_field_21'      => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'spez_field_15'),
                'spez_field_22'      => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'spez_field_21'),
                'spez_field_16'      => array('type' => 'VARCHAR(255)', 'notnull' => false, 'after' => 'spez_field_22'),
                'spez_field_17'      => array('type' => 'VARCHAR(255)', 'notnull' => false, 'after' => 'spez_field_16'),
                'spez_field_18'      => array('type' => 'VARCHAR(255)', 'notnull' => false, 'after' => 'spez_field_17'),
                'spez_field_19'      => array('type' => 'VARCHAR(255)', 'notnull' => false, 'after' => 'spez_field_18'),
                'spez_field_20'      => array('type' => 'VARCHAR(255)', 'notnull' => false, 'after' => 'spez_field_19'),
                'spez_field_23'      => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'spez_field_20'),
                'spez_field_24'      => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'spez_field_23'),
                'spez_field_25'      => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'spez_field_24'),
                'spez_field_26'      => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'spez_field_25'),
                'spez_field_27'      => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'spez_field_26'),
                'spez_field_28'      => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'spez_field_27'),
                'spez_field_29'      => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'spez_field_28'),
                'youtube'            => array('type' => 'mediumtext', 'after' => 'spez_field_29')
            ),
            array(
                'date'               => array('fields' => array('date')),
                'temphitsout'        => array('fields' => array('hits')),
                'status'             => array('fields' => array('status')),
                'name'               => array('fields' => array('title','description'), 'type' => 'FULLTEXT'),
                'description'        => array('fields' => array('description'), 'type' => 'FULLTEXT'),
                'title'              => array('fields' => array('title','description'), 'type' => 'FULLTEXT')
            ),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_directory_dir` (`id`, `title`, `attachment`, `rss_file`, `rss_link`, `link`, `date`, `description`, `platform`, `language`, `relatedlinks`, `hits`, `status`, `addedby`, `provider`, `ip`, `validatedate`, `lastip`, `popular_date`, `popular_hits`, `xml_refresh`, `canton`, `searchkeys`, `company_name`, `street`, `zip`, `city`, `country`, `phone`, `contact`, `information`, `fax`, `mobile`, `mail`, `homepage`, `industry`, `legalform`, `conversion`, `employee`, `foundation`, `mwst`, `opening`, `holidays`, `places`, `logo`, `team`, `portfolio`, `offers`, `concept`, `map`, `lokal`, `spezial`, `premium`, `longitude`, `latitude`, `zoom`, `spez_field_1`, `spez_field_2`, `spez_field_3`, `spez_field_4`, `spez_field_5`, `spez_field_6`, `spez_field_7`, `spez_field_8`, `spez_field_9`, `spez_field_10`, `spez_field_11`, `spez_field_12`, `spez_field_13`, `spez_field_14`, `spez_field_15`, `spez_field_21`, `spez_field_22`, `spez_field_16`, `spez_field_17`, `spez_field_18`, `spez_field_19`, `spez_field_20`, `spez_field_23`, `spez_field_24`, `spez_field_25`, `spez_field_26`, `spez_field_27`, `spez_field_28`, `spez_field_29`, `youtube`)
            VALUES  (26, 'Smashingmagazine', '', '', '', 'http://www.smashingmagazine.com', '1292236792', 'Smashing Magazine delivers useful and innovative information for designers and Web developers. Our aim is to inform our readers about the latest trends and techniques in Web development: clearly, precisely and regularly. We try to convince you not with the quantity but with the quality of the information we present. That’s what makes us different. We smash you with the information that makes your life easier.', '', '', '', 3, 1, '1', '84-72-46-66.dclient.hispeed.ch', '84.72.46.66', '', '122.165.78.217', '1344549600', 0, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', NULL, '', '', '', '', NULL, NULL, 0, 0, '0.000000000000000', '0.000000000000000', '0.000000000000000', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', ''),
                    (27, 'Comvation AG, Thun', '', '', '', 'http://www.comvation.com', '1292236792', 'Die COMVATION AG ist ein international tätiger Softwareentwickler und Lösungsintegrator, spezialisiert auf massgeschneiderte Internet-Auftritte und webbasierte Software-Lösungen. Zentrales Software-Produkt von Comvation ist das Web Content Management System Contrexx®.', '', '', '', 3, 1, '1', '84-72-46-66.dclient.hispeed.ch', '84.72.46.66', '', '122.165.78.217', '1344549600', 0, '', '', '', '', 'Militärstrasse 6', '3600', 'Thun', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', NULL, '', '', '', '', NULL, NULL, 1, 0, '0.000000000000000', '0.000000000000000', '0.000000000000000', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', ''),
                    (28, 'KMU Markt Schweiz', '', '', '', 'http://www.kmumarkt.ch', '1292236792', 'Onlineverzeichnis und Suchmaschine für Schweizer KMU, Firmenverzeichnis.', '', '', '', 2, 1, '1', '84-72-46-66.dclient.hispeed.ch', '84.72.46.66', '', '80.219.232.8', '1344549600', 0, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', NULL, '', '', '', '', NULL, NULL, 0, 0, '0.000000000000000', '0.000000000000000', '0.000000000000000', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', ''),
                    (29, 'IT News Schweiz', '', '', '', 'http://www.itnews.ch', '1292236792', 'ITNews.ch, Ihre Schweizer Multiplattform im IT-Bereich.', '', '', '', 2, 1, '1', '84-72-46-66.dclient.hispeed.ch', '84.72.46.66', '', '80.219.232.8', '1344549600', 0, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', NULL, '', '', '', '', NULL, NULL, 0, 0, '0.000000000000000', '0.000000000000000', '0.000000000000000', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', ''),
                    (30, 'Contrexx Themes', '', '', '', 'http://www.contrexx.com/de/index.php?section=downloads&cmd=community&category=6', '1292236792', 'At CONTREXX THEMES, users can provide their templates for others and download other designs. The design templates are categorized by topic, architecture, and colours, and a simple search functionality is provided.', '', '', '', 2, 1, '1', '84-72-46-66.dclient.hispeed.ch', '84.72.46.66', '', '80.219.232.8', '1344549600', 0, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', NULL, '', '', '', '', NULL, NULL, 0, 0, '0.000000000000000', '0.000000000000000', '0.000000000000000', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', ''),
                    (32, 'Hotelcard.com', '', '', '', 'http://www.hotelcard.com', '1292236792', 'Mit der HOTELCARD übernachten Sie ein Jahr lang zum halben Preis!', '', '', '', 1, 1, '1', '80-219-232-8.dclient.hispeed.ch', '80.219.232.8', '', '80.219.232.8', '1344549600', 0, '1291977961', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', NULL, '', '', '', '', NULL, NULL, 0, 0, '0.000000000000000', '0.000000000000000', '0.000000000000000', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', ''),
                    (33, 'Lorem ipsum ', '', '', '', 'http://extensio-html.atixscripts.info/extensio-wide/blog-layout-1.html', '1343111919', 'Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum \r\n', '', '', '', 0, 1, '1', 'ABTS-TN-Static-217.78.165.122.airtelbroadband.in', '122.165.78.217', '1343111919', '', '1344549600', 0, '1343111919', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', NULL, '', '', '', '', NULL, NULL, 0, 0, '0.000000000000000', '0.000000000000000', '0.000000000000000', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', ''),
                    (34, 'Lorem ipsum ', '', '', '', 'http://extensio-html.atixscripts.info/extensio-wide/blog-layout-2.html', '1343111982', 'Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum \r\n', '', '', '', 1, 1, '1', 'ABTS-TN-Static-217.78.165.122.airtelbroadband.in', '122.165.78.217', '1343111982', '122.165.78.217', '1344549600', 0, '1343111982', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', NULL, '', '', '', '', NULL, NULL, 0, 0, '0.000000000000000', '0.000000000000000', '0.000000000000000', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', ''),
                    (35, 'Lorem ipsum ', '', '', '', 'http://extensio-html.atixscripts.info', '1343112038', 'Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum \r\n', '', '', '', 1, 1, '1', 'ABTS-TN-Static-217.78.165.122.airtelbroadband.in', '122.165.78.217', '1343112038', '46.127.25.132', '1344549600', 0, '1343112038', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', NULL, '', '', '', '', NULL, NULL, 0, 0, '0.000000000000000', '0.000000000000000', '0.000000000000000', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', '')
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_directory_inputfields',
            array(
                'id'                 => array('type' => 'INT(7)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'typ'                => array('type' => 'INT(2)', 'notnull' => true, 'default' => '0', 'after' => 'id'),
                'name'               => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'typ'),
                'title'              => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'name'),
                'active'             => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'title'),
                'active_backend'     => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'active'),
                'is_required'        => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'after' => 'active_backend'),
                'read_only'          => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'is_required'),
                'sort'               => array('type' => 'INT(5)', 'notnull' => true, 'default' => '0', 'after' => 'read_only'),
                'exp_search'         => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'sort'),
                'is_search'          => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'exp_search')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_directory_inputfields` (`id`, `typ`, `name`, `title`, `active`, `active_backend`, `is_required`, `read_only`, `sort`, `exp_search`, `is_search`)
            VALUES  (1, 1, 'title', 'TXT_DIR_F_TITLE', 1, 1, 1, 0, 1, 0, 0),
                    (2, 2, 'description', 'TXT_DIR_F_DESCRIPTION', 1, 1, 1, 0, 2, 0, 0),
                    (3, 3, 'platform', 'TXT_DIR_F_PLATFORM', 0, 0, 0, 0, 0, 1, 0),
                    (4, 3, 'language', 'TXT_DIR_F_LANG', 0, 0, 0, 0, 0, 1, 0),
                    (5, 1, 'addedby', 'TXT_DIR_F_ADDED_BY', 1, 1, 1, 0, 5, 0, 0),
                    (6, 1, 'relatedlinks', 'TXT_DIR_F_RELATED_LINKS', 0, 0, 0, 0, 0, 1, 0),
                    (7, 3, 'canton', 'TXT_DIR_F_CANTON', 0, 0, 0, 0, 0, 1, 0),
                    (8, 2, 'searchkeys', 'TXT_DIR_F_SEARCH_KEYS', 0, 0, 0, 0, 0, 0, 0),
                    (9, 1, 'company_name', 'TXT_DIR_F_CO_NAME', 0, 0, 0, 0, 0, 1, 0),
                    (10, 1, 'street', 'TXT_DIR_F_STREET', 1, 1, 0, 0, 2, 1, 0),
                    (11, 1, 'zip', 'TXT_DIR_F_PLZ', 1, 1, 0, 0, 3, 1, 0),
                    (12, 1, 'phone', 'TXT_DIR_F_PHONE', 0, 0, 0, 0, 0, 1, 0),
                    (13, 1, 'contact', 'TXT_DIR_F_PERSON', 0, 0, 0, 0, 0, 1, 0),
                    (14, 1, 'city', 'TXT_DIR_CITY', 1, 1, 0, 0, 4, 1, 0),
                    (15, 1, 'information', 'TXT_INFOZEILE', 0, 0, 0, 0, 0, 1, 0),
                    (16, 1, 'fax', 'TXT_TELEFAX', 0, 0, 0, 0, 0, 1, 0),
                    (17, 1, 'mobile', 'TXT_MOBILE', 0, 0, 0, 0, 0, 1, 0),
                    (18, 1, 'mail', 'TXT_DIR_F_EMAIL', 0, 0, 0, 0, 0, 1, 0),
                    (19, 1, 'homepage', 'TXT_HOMEPAGE', 0, 0, 0, 0, 0, 1, 0),
                    (20, 1, 'industry', 'TXT_BRANCHE', 0, 0, 0, 0, 0, 1, 0),
                    (21, 1, 'legalform', 'TXT_RECHTSFORM', 0, 0, 0, 0, 0, 1, 0),
                    (22, 2, 'conversion', 'TXT_UMSATZ', 0, 0, 0, 0, 0, 0, 0),
                    (23, 2, 'employee', 'TXT_MITARBEITER', 0, 0, 0, 0, 0, 1, 0),
                    (24, 1, 'foundation', 'TXT_GRUENDUNGSJAHR', 0, 0, 0, 0, 0, 1, 0),
                    (25, 1, 'mwst', 'TXT_MWST_NR', 0, 0, 0, 0, 0, 1, 0),
                    (26, 2, 'opening', 'TXT_OEFFNUNGSZEITEN', 0, 0, 0, 0, 0, 0, 0),
                    (27, 2, 'holidays', 'TXT_BETRIEBSFERIEN', 0, 0, 0, 0, 0, 0, 0),
                    (28, 2, 'places', 'TXT_SUCHORTE', 0, 0, 0, 0, 0, 0, 0),
                    (29, 4, 'logo', 'TXT_LOGO', 0, 0, 0, 0, 0, 0, 0),
                    (30, 2, 'team', 'TXT_TEAM', 0, 0, 0, 0, 0, 0, 0),
                    (32, 2, 'portfolio', 'TXT_REFERENZEN', 0, 0, 0, 0, 0, 0, 0),
                    (33, 2, 'offers', 'TXT_ANGEBOTE', 0, 0, 0, 0, 0, 0, 0),
                    (34, 2, 'concept', 'TXT_KONZEPT', 0, 0, 0, 0, 0, 0, 0),
                    (35, 4, 'map', 'TXT_MAP', 0, 0, 0, 0, 0, 0, 0),
                    (36, 4, 'lokal', 'TXT_LOKAL', 0, 0, 0, 0, 0, 0, 0),
                    (37, 5, 'spez_field_1', '', 0, 0, 0, 0, 0, 1, 0),
                    (38, 5, 'spez_field_2', '', 0, 0, 0, 0, 0, 1, 0),
                    (39, 5, 'spez_field_3', '', 0, 0, 0, 0, 0, 1, 0),
                    (40, 5, 'spez_field_4', '', 0, 0, 0, 0, 0, 1, 0),
                    (41, 5, 'spez_field_5', '', 0, 0, 0, 0, 0, 1, 0),
                    (42, 6, 'spez_field_6', '', 0, 0, 0, 0, 0, 1, 0),
                    (43, 6, 'spez_field_7', '', 0, 0, 0, 0, 0, 1, 0),
                    (44, 6, 'spez_field_8', '', 0, 0, 0, 0, 0, 1, 0),
                    (45, 6, 'spez_field_9', '', 0, 0, 0, 0, 0, 1, 0),
                    (46, 6, 'spez_field_10', '', 0, 0, 0, 0, 0, 1, 0),
                    (47, 7, 'spez_field_11', 'Grafik', 0, 0, 0, 0, 0, 0, 0),
                    (48, 7, 'spez_field_12', '', 0, 0, 0, 0, 0, 0, 0),
                    (49, 7, 'spez_field_13', '', 0, 0, 0, 0, 0, 0, 0),
                    (50, 7, 'spez_field_14', '', 0, 0, 0, 0, 0, 0, 0),
                    (51, 7, 'spez_field_15', '', 0, 0, 0, 0, 0, 0, 0),
                    (52, 8, 'spez_field_21', 'Land', 0, 0, 0, 0, 0, 1, 0),
                    (53, 8, 'spez_field_22', '', 0, 0, 0, 0, 0, 1, 0),
                    (54, 7, 'spez_field_18', '', 0, 0, 0, 0, 0, 0, 0),
                    (55, 7, 'spez_field_19', '', 0, 0, 0, 0, 0, 0, 0),
                    (56, 7, 'spez_field_20', '', 0, 0, 0, 0, 0, 0, 0),
                    (57, 9, 'spez_field_23', '', 0, 0, 0, 0, 0, 1, 0),
                    (58, 9, 'spez_field_24', '', 0, 0, 0, 0, 0, 1, 0),
                    (59, 10, 'spez_field_25', '', 0, 0, 0, 0, 0, 0, 0),
                    (60, 10, 'spez_field_26', '', 0, 0, 0, 0, 0, 0, 0),
                    (61, 10, 'spez_field_27', '', 0, 0, 0, 0, 0, 0, 0),
                    (62, 10, 'spez_field_28', '', 0, 0, 0, 0, 0, 0, 0),
                    (63, 10, 'spez_field_29', '', 0, 0, 0, 0, 0, 0, 0),
                    (64, 7, 'spez_field_16', '', 0, 0, 0, 0, 0, 0, 0),
                    (65, 7, 'spez_field_17', '', 0, 0, 0, 0, 0, 0, 0),
                    (66, 1, 'link', 'TXT_DIRECTORY_LINK', 1, 1, 1, 0, 3, 0, 0),
                    (67, 11, 'attachment', 'TXT_DIRECTORY_ATTACHMENT', 0, 0, 0, 0, 0, 0, 0),
                    (68, 12, 'rss_link', 'TXT_DIRECTORY_RSS_FEED', 0, 0, 0, 0, 0, 0, 0),
                    (69, 13, 'googlemap', 'TXT_DIR_F_GOOGLEMAP', 0, 0, 0, 0, 0, 0, 0),
                    (70, 3, 'country', 'TXT_DIR_F_COUNTRY', 0, 0, 0, 0, 0, 0, 0),
                    (71, 1, 'youtube', 'TXT_DIRECTORY_YOUTUBE', 0, 0, 0, 0, 0, 0, 0)
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_directory_levels',
            array(
                'id'                 => array('type' => 'INT(7)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'parentid'           => array('type' => 'INT(7)', 'notnull' => true, 'default' => '0', 'after' => 'id'),
                'name'               => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => '', 'after' => 'parentid'),
                'description'        => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'name'),
                'metadesc'           => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => '', 'after' => 'description'),
                'metakeys'           => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => '', 'after' => 'metadesc'),
                'displayorder'       => array('type' => 'INT(7)', 'notnull' => true, 'default' => '0', 'after' => 'metakeys'),
                'showlevels'         => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'displayorder'),
                'showcategories'     => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'showlevels'),
                'onlyentries'        => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'showcategories'),
                'status'             => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'onlyentries')
            ),
            array(
                'displayorder'       => array('fields' => array('displayorder')),
                'parentid'           => array('fields' => array('parentid')),
                'name'               => array('fields' => array('name')),
                'status'             => array('fields' => array('status'))
            ),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_directory_levels` (`id`, `parentid`, `name`, `description`, `metadesc`, `metakeys`, `displayorder`, `showlevels`, `showcategories`, `onlyentries`, `status`)
            VALUES (1, 0, 'Demo Ebene', 'Demo Ebene', '', '', 0, 1, 1, 0, 1)
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_directory_mail',
            array(
                'id'         => array('type' => 'TINYINT(4)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'title'      => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'id'),
                'content'    => array('type' => 'longtext', 'after' => 'title')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_directory_mail` (`id`, `title`, `content`)
            VALUES  (1, '[[URL]] - Eintrag aufgeschaltet', 'Hallo [[FIRSTNAME]] [[LASTNAME]] ([[USERNAME]])\r\n\r\nDein Eintrag mit dem Titel \"[[TITLE]]\" wurde auf [[URL]] erfolgreich aufgeschaltet. \r\n\r\nBenutze folgenden Link um direkt zu Deinem Eintrag zu gelangen:\r\n[[LINK]]\r\n\r\nMit freundlichen Grüssen\r\n[[URL]] - Team\r\n\r\n[[DATE]]'),
                    (2, '[[URL]] - Neuer Eintrag', 'Hallo Admin\r\n\r\nAuf [[URL]] wurde ein Eintrag aufgeschaltet oder editiert. Bitte überprüfen Sie diesen und Bestätigen Sie ihn falls nötig.\r\n\r\nEintrag Details:\r\n\r\nTitel: [[TITLE]]\r\nBenutzername: [[USERNAME]]\r\nVorname: [[FIRSTNAME]]\r\nNachname:[[LASTNAME]]\r\nLink: [[LINK]]\r\n\r\nAutomatisch generierte Nachricht\r\n[[DATE]]')
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_directory_rel_dir_cat',
            array(
                'dir_id'     => array('type' => 'INT(7)', 'notnull' => true, 'default' => '0', 'primary' => true),
                'cat_id'     => array('type' => 'INT(7)', 'notnull' => true, 'default' => '0', 'primary' => true, 'after' => 'dir_id')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_directory_rel_dir_cat` (`dir_id`, `cat_id`)
            VALUES  (26, 5),
                    (27, 4),
                    (28, 4),
                    (29, 9),
                    (30, 4),
                    (32, 9),
                    (33, 4),
                    (34, 4),
                    (35, 4)
            ON DUPLICATE KEY UPDATE `dir_id` = `dir_id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_directory_rel_dir_level',
            array(
                'dir_id'         => array('type' => 'INT(7)', 'notnull' => true, 'default' => '0', 'primary' => true),
                'level_id'       => array('type' => 'INT(7)', 'notnull' => true, 'default' => '0', 'primary' => true, 'after' => 'dir_id')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_directory_settings',
            array(
                'setid'          => array('type' => 'INT(6)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'setname'        => array('type' => 'VARCHAR(250)', 'notnull' => true, 'default' => '', 'after' => 'setid'),
                'setvalue'       => array('type' => 'text', 'after' => 'setname'),
                'settyp'         => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'setvalue')
            ),
            array(
                'setname'        => array('fields' => array('setname'))
            ),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
        INSERT INTO `".DBPREFIX."module_directory_settings` (`setid`, `setname`, `setvalue`, `settyp`)
        VALUES  (1, 'levels', '0', 2),
                (5, 'xmlLimit', '10', 1),
                (6, 'platform', '', 0),
                (7, 'language', ',Deutsch,English,Italian,French', 0),
                (10, 'latest_content', '3', 1),
                (11, 'latest_xml', '10', 1),
                (12, 'entryStatus', '1', 2),
                (13, 'description', '1', 2),
                (14, 'populardays', '7', 1),
                (16, 'canton', ',Aargau,Appenzell-Ausserrhoden,Appenzell-Innerrhoden,Basel-Land,\r\nBasel-Stadt,Bern,Freiburg,Genf,Glarus,Graubünden,Jura,Luzern,\r\nNeuenburg,Nidwalden,Obwalden,St. Gallen,Schaffhausen,Schwyz,\r\nSolothurn,Thurgau,Tessin,Uri,Waadt,Wallis,Zug,Zürich', 0),
                (17, 'refreshfeeds', '1', 1),
                (22, 'mark_new_entrees', '7', 1),
                (23, 'showConfirm', '1', 2),
                (26, 'addFeed', '1', 2),
                (27, 'addFeed_only_community', '1', 2),
                (28, 'editFeed', '1', 2),
                (29, 'editFeed_status', '1', 2),
                (30, 'adminMail', '', 1),
                (31, 'indexview', '0', 2),
                (32, 'spez_field_21', ',Germany,\r\nSwitzerland,\r\nAustria,\r\nLiechtenstein,\r\nUnited States,\r\nAlbania,\r\nAlgeria,\r\nAndorra,\r\nAngola,\r\nAnguilla,\r\nAntigua and Barbuda,\r\nArgentina,\r\nArmenia,\r\nAruba,\r\nAustralia,\r\nAzerbaijan Republic,\r\nBahamas,\r\nBahrain,\r\nBarbados,\r\nBelgium,\r\nBelize,\r\nBenin,\r\nBermuda,\r\nBhutan,\r\nBolivia,\r\nBosnia and Herzegovina,\r\nBotswana,\r\nBrazil,\r\nBritish Virgin Islands,\r\nBrunei,\r\nBulgaria,\r\nBurkina Faso,\r\nBurundi,\r\nCambodia,\r\nCanada,\r\nCape Verde,\r\nCayman Islands,\r\nChad,\r\nChile,\r\nChina Worldwide,\r\nColombia,\r\nComoros,\r\nCook Islands,\r\nCosta Rica,\r\nCroatia,\r\nCyprus,\r\nCzech Republic,\r\nDemocratic Republic of the Congo,\r\nDenmark,\r\nDjibouti,\r\nDominica,\r\nDominican Republic,\r\nEcuador,\r\nEl Salvador,\r\nEritrea,\r\nEstonia,\r\nEthiopia,\r\nFalkland Islands,\r\nFaroe Islands,\r\nFederated States of Micronesia,\r\nFiji,\r\nFinland,\r\nFrance,\r\nFrench Guiana,\r\nFrench Polynesia,\r\nGabon Republic,\r\nGambia,\r\nGibraltar,\r\nGreece,\r\nGreenland,\r\nGrenada,\r\nGuadeloupe,\r\nGuatemala,\r\nGuinea,\r\nGuinea Bissau,\r\nGuyana,\r\nHonduras,\r\nHong Kong,\r\nHungary,\r\nIceland,\r\nIndia,\r\nIndonesia,\r\nIreland,\r\nIsrael,\r\nItaly,\r\nJamaica,\r\nJapan,\r\nJordan,\r\nKazakhstan,\r\nKenya,\r\nKiribati,\r\nKuwait,\r\nKyrgyzstan,\r\nLaos,\r\nLatvia,\r\nLesotho,\r\nLithuania,\r\nLuxembourg,\r\nMadagascar,\r\nMalawi,\r\nMalaysia,\r\nMaldives,\r\nMali,\r\nMalta,\r\nMarshall Islands,\r\nMartinique,\r\nMauritania,\r\nMauritius,\r\nMayotte,\r\nMexico,\r\nMongolia,\r\nMontserrat,\r\nMorocco,\r\nMozambique,\r\nNamibia,\r\nNauru,\r\nNepal,\r\nNetherlands,\r\nNetherlands Antilles,\r\nNew Caledonia,\r\nNew Zealand,\r\nNicaragua,\r\nNiger,\r\nNiue,\r\nNorfolk Island,\r\nNorway,\r\nOman,\r\nPalau,\r\nPanama,\r\nPapua New Guinea,\r\nPeru,\r\nPhilippines,\r\nPitcairn Islands,\r\nPoland,\r\nPortugal,\r\nQatar,\r\nRepublic of the Congo,\r\nReunion,\r\nRomania,\r\nRussia,\r\nRwanda,\r\nSaint Vincent and the Grenadines,\r\nSamoa,\r\nSan Marino,\r\nSão Tomé and Príncipe,\r\nSaudi Arabia,\r\nSenegal,\r\nSeychelles,\r\nSierra Leone,\r\nSingapore,\r\nSlovakia,\r\nSlovenia,\r\nSolomon Islands,\r\nSomalia,\r\nSouth Africa,\r\nSouth Korea,\r\nSpain,\r\nSri Lanka,\r\nSt. Helena,\r\nSt. Kitts and Nevis,\r\nSt. Lucia,\r\nSt. Pierre and Miquelon,\r\nSuriname,\r\nSvalbard and Jan Mayen Islands,\r\nSwaziland,\r\nSweden,\r\nTaiwan,\r\nTajikistan,\r\nTanzania,\r\nThailand,\r\nTogo,\r\nTonga,\r\nTrinidad and Tobago,\r\nTunisia,\r\nTurkey,\r\nTurkmenistan,\r\nTurks and Caicos Islands,\r\nTuvalu,\r\nUganda,\r\nUkraine,\r\nUnited Arab Emirates,\r\nUnited Kingdom,\r\nUruguay,\r\nVanuatu,\r\nVatican City State,\r\nVenezuela,\r\nVietnam,\r\nWallis and Futuna Islands,\r\nYemen,\r\nZambia', 0),
                (33, 'spez_field_22', '', 0),
                (34, 'thumbSize', '120', 1),
                (35, 'sortOrder', '0', 2),
                (36, 'spez_field_23', '', 0),
                (37, 'spez_field_24', '', 0),
                (38, 'encodeFilename', '1', 2),
                (39, 'country', ',Schweiz,Deutschland,Österreich,Weltweit', 0),
                (40, 'pagingLimit', '4', 1),
                (41, 'youtubeWidth', '20', 1),
                (42, 'youtubeHeight', '300', 1),
                (43, 'youtubeWidth', '400', 1),
                (44, 'youtubeHeight', '300', 1)
            ON DUPLICATE KEY UPDATE `setid` = `setid`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_directory_settings_google',
            array(
                'setid'          => array('type' => 'INT(6)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'setname'        => array('type' => 'VARCHAR(250)', 'notnull' => true, 'default' => '', 'after' => 'setid'),
                'setvalue'       => array('type' => 'text', 'after' => 'setname'),
                'settyp'         => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'setvalue')
            ),
            array(
                'setname'        => array('fields' => array('setname'))
            ),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_directory_settings_google` (`setid`, `setname`, `setvalue`, `settyp`)
            VALUES  (1, 'googleSeach', '0', 2),
                    (2, 'googleResults', '', 1),
                    (26, 'googleId', '', 1),
                    (27, 'googleLang', '', 1)
            ON DUPLICATE KEY UPDATE `setid` = `setid`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_directory_vote',
            array(
                'id'         => array('type' => 'INT(7)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'feed_id'    => array('type' => 'INT(7)', 'notnull' => true, 'default' => '0', 'after' => 'id'),
                'vote'       => array('type' => 'INT(2)', 'notnull' => true, 'default' => '0', 'after' => 'feed_id'),
                'count'      => array('type' => 'INT(7)', 'notnull' => true, 'default' => '0', 'after' => 'vote'),
                'client'     => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'count'),
                'time'       => array('type' => 'VARCHAR(20)', 'notnull' => true, 'default' => '', 'after' => 'client')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_directory_vote` (`id`, `feed_id`, `vote`, `count`, `client`, `time`)
            VALUES  (8, 30, 12, 2, '2fcd8694488a86ce475bf72aae0891fa', '1291972976'),
                    (9, 26, 2, 1, 'fa54a587d9deb29e0ea679a53fdbf171', '1342424017'),
                    (10, 27, 7, 1, 'fa54a587d9deb29e0ea679a53fdbf171', '1342424866'),
                    (11, 29, 6, 1, 'fa54a587d9deb29e0ea679a53fdbf171', '1342424903'),
                    (12, 35, 7, 1, '4e7c33c1f34bd9c52da1c08f7bbaa846', '1344320520')
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }
}
