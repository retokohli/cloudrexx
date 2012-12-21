<?php

function _memberdirUpdate() {
    global $objDatabase, $_ARRAYLANG, $_CORELANG;

    try {
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX . 'module_memberdir_directories', array(
                'dirid' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'parentdir' => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0'),
                'active' => array('type' => 'SET(\'1\',\'0\')', 'notnull' => true, 'default' => '1'),
                'name' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'description' => array('type' => 'TEXT'),
                'displaymode' => array('type' => 'SET(\'0\',\'1\',\'2\')', 'notnull' => true, 'default' => '0'),
                'sort' => array('type' => 'INT(11)', 'notnull' => true, 'default' => '1'),
                'pic1' => array('type' => 'SET(\'1\',\'0\')', 'notnull' => true, 'default' => '0'),
                'pic2' => array('type' => 'SET(\'1\',\'0\')', 'notnull' => true, 'default' => '0'),
                'lang_id' => array('type' => 'INT(2)', 'unsigned' => true, 'notnull' => true, 'default' => '1')
            ), array(
                'memberdir_dir' => array('fields' => array('name', 'description'), 'type' => 'FULLTEXT')
            )
        );
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX . 'module_memberdir_name', array(
                'field' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'dirid' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'name' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'active' => array('type' => 'SET(\'0\',\'1\')', 'notnull' => true, 'default' => ''),
                'lang_id' => array('type' => 'INT(2)', 'unsigned' => true, 'notnull' => true, 'default' => '1')
            )
        );
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX . 'module_memberdir_settings', array(
                'setid' => array('type' => 'INT(4)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'setname' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'setvalue' => array('type' => 'TEXT'),
                'lang_id' => array('type' => 'INT(2)', 'unsigned' => true, 'notnull' => true, 'default' => '1')
            )
        );
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX . 'module_memberdir_values', array(
                'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'dirid' => array('type' => 'INT(14)', 'notnull' => true, 'default' => '0'),
                'pic1' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'pic2' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                '0' => array('type' => 'SMALLINT(5)', 'notnull' => true, 'unsigned' => true, 'default' => '0'),
                '1' => array('type' => 'TEXT'),
                '2' => array('type' => 'TEXT'),
                '3' => array('type' => 'TEXT'),
                '4' => array('type' => 'TEXT'),
                '5' => array('type' => 'TEXT'),
                '6' => array('type' => 'TEXT'),
                '7' => array('type' => 'TEXT'),
                '8' => array('type' => 'TEXT'),
                '9' => array('type' => 'TEXT'),
                '10' => array('type' => 'TEXT'),
                '11' => array('type' => 'TEXT'),
                '12' => array('type' => 'TEXT'),
                '13' => array('type' => 'TEXT'),
                '14' => array('type' => 'TEXT'),
                '15' => array('type' => 'TEXT'),
                '16' => array('type' => 'TEXT'),
                '17' => array('type' => 'TEXT'),
                '18' => array('type' => 'TEXT'),
                'lang_id' => array('type' => 'INT(2)', 'unsigned' => true, 'notnull' => true, 'default' => '1')
            )
        );

        $arrSettings = array(
            'default_listing' => array('1', '1'),
            'max_height' => array('400', '1'),
            'max_width' => array('500', '1')
        );

        foreach ($arrSettings as $key => $arrSetting) {
            if (!\Cx\Lib\UpdateUtil::sql("SELECT 1 FROM `" . DBPREFIX . "module_memberdir_settings` WHERE `setname` = '" . $key . "'")->RecordCount()) {
                \Cx\Lib\UpdateUtil::sql("INSERT INTO `" . DBPREFIX . "module_memberdir_settings`
                    SET `setname`    = '" . $key . "',
                        `setvalue`   = '" . $arrSetting[0] . "',
                        `lang_id`    = '" . $arrSetting[1] . "'
                ");
            }
        }
    } catch (\Cx\Lib\UpdateException $e) {
        // we COULD do something else here..
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }


    require_once ASCMS_FRAMEWORK_PATH . '/File.class.php';
    if (!\Cx\Lib\FileSystem\FileSystem::makeWritable(ASCMS_MEDIA_PATH . '/memberdir')) {
        setUpdateMsg(sprintf($_ARRAYLANG['TXT_SET_WRITE_PERMISSON_TO_DIR_AND_CONTENT'], ASCMS_MEDIA_PATH . '/memberdir/', $_CORELANG['TXT_UPDATE_TRY_AGAIN']), 'msg');
        return false;
    }

    return true;
}



function _memberdirInstall()
{
    try {

        /**************************************************************************
         * EXTENSION:   Initial creation (for contrexx editions <> premium with   *
         *              version < 3.0.0 which have this module not yet installed) *
         *                                                                        *
         * ADDED:       Contrexx v3.0.1                                           *
         **************************************************************************/
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_memberdir_directories',
            array(
                'dirid'          => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'parentdir'      => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'after' => 'dirid'),
                'active'         => array('type' => 'SET(\'1\',\'0\')', 'notnull' => true, 'default' => '1', 'after' => 'parentdir'),
                'name'           => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'active'),
                'description'    => array('type' => 'text', 'after' => 'name'),
                'displaymode'    => array('type' => 'SET(\'0\',\'1\',\'2\')', 'notnull' => true, 'default' => '0', 'after' => 'description'),
                'sort'           => array('type' => 'INT(11)', 'notnull' => true, 'default' => '1', 'after' => 'displaymode'),
                'pic1'           => array('type' => 'SET(\'1\',\'0\')', 'notnull' => true, 'default' => '0', 'after' => 'sort'),
                'pic2'           => array('type' => 'SET(\'1\',\'0\')', 'notnull' => true, 'default' => '0', 'after' => 'pic1'),
                'lang_id'        => array('type' => 'INT(2)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'after' => 'pic2')
            ),
            array(
                'memberdir_dir'  => array('fields' => array('name','description'), 'type' => 'FULLTEXT')
            ),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_memberdir_directories` (`dirid`, `parentdir`, `active`, `name`, `description`, `displaymode`, `sort`, `pic1`, `pic2`, `lang_id`)
            VALUES  (1, 0, '1', 'Verein ABC', 'Mitglieder des Vereins ABC', '0', 0, '0', '0', 1),
                    (2, 0, '1', 'Verein XYZ', 'Mitglieder des Vereins XYZ', '0', 0, '0', '0', 0),
                    (3, 1, '1', 'Vorstand', '', '0', 0, '0', '0', 0),
                    (4, 1, '1', 'Leitung', 'Leitung', '0', 0, '0', '0', 0)
            ON DUPLICATE KEY UPDATE `dirid` = `dirid`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_memberdir_name',
            array(
                'field'      => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'dirid'      => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'field'),
                'name'       => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'dirid'),
                'active'     => array('type' => 'SET(\'0\',\'1\')', 'notnull' => true, 'default' => '', 'after' => 'name'),
                'lang_id'    => array('type' => 'INT(2)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'after' => 'active')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_memberdir_name` (`field`, `dirid`, `name`, `active`, `lang_id`)
            VALUES  (1, 1, 'Name', '1', 1),
                    (2, 1, 'Vorname', '1', 1),
                    (3, 1, 'Firma', '0', 1),
                    (4, 1, 'Telefonnummer', '0', 1),
                    (5, 1, 'Mobilnummer', '0', 1),
                    (6, 1, 'Adresse', '1', 1),
                    (7, 1, 'PLZ', '1', 1),
                    (8, 1, 'Ort', '1', 1),
                    (9, 1, 'E-Mail', '0', 1),
                    (10, 1, 'Fax', '0', 1),
                    (11, 1, 'Internet', '0', 1),
                    (12, 1, 'Geburtsdatum', '0', 1),
                    (13, 1, 'Beschreibung', '0', 1),
                    (14, 1, '', '0', 1),
                    (15, 1, '', '0', 1),
                    (16, 1, '', '0', 1),
                    (17, 1, '', '0', 1),
                    (18, 1, '', '0', 1),
                    (1, 2, 'Name', '1', 1),
                    (2, 2, 'Vorname', '1', 1),
                    (3, 2, 'Firma', '1', 1),
                    (4, 2, 'Telefonnummer', '1', 1),
                    (5, 2, 'Mobilnummer', '1', 1),
                    (6, 2, 'Adresse', '1', 1),
                    (7, 2, 'PLZ', '1', 1),
                    (8, 2, 'Ort', '1', 1),
                    (9, 2, 'E-Mail', '1', 1),
                    (10, 2, 'Fax', '1', 1),
                    (11, 2, 'Internet', '1', 1),
                    (12, 2, 'Geburtsdatum', '1', 1),
                    (13, 2, '', '0', 1),
                    (14, 2, '', '0', 1),
                    (15, 2, '', '0', 1),
                    (16, 2, '', '0', 1),
                    (17, 2, '', '0', 1),
                    (18, 2, '', '0', 1),
                    (1, 3, 'Name', '1', 1),
                    (2, 3, 'Vorname', '1', 1),
                    (3, 3, 'Firma', '1', 1),
                    (4, 3, 'Telefonnummer', '1', 1),
                    (5, 3, 'Mobilnummer', '1', 1),
                    (6, 3, 'Adresse', '1', 1),
                    (7, 3, 'PLZ', '1', 1),
                    (8, 3, 'Ort', '1', 1),
                    (9, 3, 'E-Mail', '1', 1),
                    (10, 3, 'Fax', '1', 1),
                    (11, 3, 'Internet', '1', 1),
                    (12, 3, 'Geburtsdatum', '1', 1),
                    (13, 3, '', '0', 1),
                    (14, 3, '', '0', 1),
                    (15, 3, '', '0', 1),
                    (16, 3, '', '0', 1),
                    (17, 3, '', '0', 1),
                    (18, 3, '', '0', 1),
                    (1, 4, 'Name', '1', 1),
                    (2, 4, 'Vorname', '1', 1),
                    (3, 4, 'Firma', '1', 1),
                    (4, 4, 'Telefonnummer', '1', 1),
                    (5, 4, 'Mobilnummer', '1', 1),
                    (6, 4, 'Adresse', '1', 1),
                    (7, 4, 'PLZ', '1', 1),
                    (8, 4, 'Ort', '1', 1),
                    (9, 4, 'E-Mail', '1', 1),
                    (10, 4, 'Fax', '1', 1),
                    (11, 4, 'Internet', '1', 1),
                    (12, 4, 'Geburtsdatum', '1', 1),
                    (13, 4, '', '0', 1),
                    (14, 4, '', '0', 1),
                    (15, 4, '', '0', 1),
                    (16, 4, '', '0', 1),
                    (17, 4, '', '0', 1),
                    (18, 4, '', '0', 1)
            ON DUPLICATE KEY UPDATE `field` = `field`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_memberdir_settings',
            array(
                'setid'          => array('type' => 'INT(4)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'setname'        => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'setid'),
                'setvalue'       => array('type' => 'text', 'after' => 'setname'),
                'lang_id'        => array('type' => 'INT(2)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'after' => 'setvalue')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_memberdir_settings` (`setid`, `setname`, `setvalue`, `lang_id`)
            VALUES  (1, 'default_listing', '1', 1),
                    (3, 'max_height', '400', 1),
                    (4, 'max_width', '500', 1)
            ON DUPLICATE KEY UPDATE `setid` = `setid`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_memberdir_values',
            array(
                'id'         => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'dirid'      => array('type' => 'INT(14)', 'notnull' => true, 'default' => '0', 'after' => 'id'),
                'pic1'       => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'dirid'),
                'pic2'       => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'pic1'),
                '0'          => array('type' => 'SMALLINT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'pic2'),
                '1'          => array('type' => 'text', 'after' => '0'),
                '2'          => array('type' => 'text', 'after' => '1'),
                '3'          => array('type' => 'text', 'after' => '2'),
                '4'          => array('type' => 'text', 'after' => '3'),
                '5'          => array('type' => 'text', 'after' => '4'),
                '6'          => array('type' => 'text', 'after' => '5'),
                '7'          => array('type' => 'text', 'after' => '6'),
                '8'          => array('type' => 'text', 'after' => '7'),
                '9'          => array('type' => 'text', 'after' => '8'),
                '10'         => array('type' => 'text', 'after' => '9'),
                '11'         => array('type' => 'text', 'after' => '10'),
                '12'         => array('type' => 'text', 'after' => '11'),
                '13'         => array('type' => 'text', 'after' => '12'),
                '14'         => array('type' => 'text', 'after' => '13'),
                '15'         => array('type' => 'text', 'after' => '14'),
                '16'         => array('type' => 'text', 'after' => '15'),
                '17'         => array('type' => 'text', 'after' => '16'),
                '18'         => array('type' => 'text', 'after' => '17'),
                'lang_id'    => array('type' => 'INT(2)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'after' => '18')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_memberdir_values` (`id`, `dirid`, `pic1`, `pic2`, `0`, `1`, `2`, `3`, `4`, `5`, `6`, `7`, `8`, `9`, `10`, `11`, `12`, `13`, `14`, `15`, `16`, `17`, `18`, `lang_id`)
            VALUES  (15, 1, 'none', 'none', 0, 'Muster', 'Hans', '', '', '', 'Musterstrasse 12', '00000', 'Musterhausen', '', '', '', '', '', '', '', '', '', '', 1),
                    (16, 1, 'none', 'none', 0, 'Musterfrau', 'Sabine', '', '', '', 'Teststrasse 123', '12345', 'Testerhausen', '', '', '', '', '', '', '', '', '', '', 1),
                    (17, 1, 'none', 'none', 0, 'Mustermann', 'Peter', '', '', '', 'Musterweg 321', '32112', 'Musterhausen', '', '', '', '', '', '', '', '', '', '', 1)
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }
}
