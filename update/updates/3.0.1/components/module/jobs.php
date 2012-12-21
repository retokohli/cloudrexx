<?php

function _jobsUpdate() {
    global $objDatabase;

    try {
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX . 'module_jobs',
            array(
                'id'         => array('type' => 'INT(6)',       'notnull' => true,  'primary' => true, 'auto_increment' => true, 'unsigned' => true),
                'date'       => array('type' => 'INT(14)',      'notnull' => false),
                'title'      => array('type' => 'VARCHAR(250)', 'notnull' => true,  'default' => ''),
                'author'     => array('type' => 'VARCHAR(150)', 'notnull' => true,  'default' => ''),
                'text'       => array('type' => 'MEDIUMTEXT'),
                'workloc'    => array('type' => 'VARCHAR(250)', 'notnull' => true,  'default' => ''),
                'workload'   => array('type' => 'VARCHAR(250)', 'notnull' => true,  'default' => ''),
                'work_start' => array('type' => 'INT(14)',      'notnull' => true,  'default' => 0),
                'catid'      => array('type' => 'INT(2)',       'notnull' => true,  'default' => 0, 'unsigned' => true),
                'lang'       => array('type' => 'INT(2)',       'notnull' => true,  'default' => 0, 'unsigned' => true),
                'userid'     => array('type' => 'INT(6)',       'notnull' => true,  'default' => 0, 'unsigned' => true),
                'startdate'  => array('type' => 'DATE',         'notnull' => true,  'default' => '0000-00-00'),
                'enddate'    => array('type' => 'DATE',         'notnull' => true,  'default' => '0000-00-00'),
                'status'     => array('type' => 'TINYINT(4)',       'notnull' => true,  'default' => 1),
                'changelog'  => array('type' => 'INT(14)',      'notnull' => true,  'default' => 0)
            ),
            array(
                'newsindex'  => array('fields' => array('title', 'text'), 'type' => 'fulltext')
            )
        );
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX . 'module_jobs_categories',
            array(
                'catid'      => array('type' => 'INT(2)',           'primary' => true, 'auto_increment' => true, 'unsigned' => true),
                'name'       => array('type' => 'VARCHAR(100)',                        'default'        => ''),
                'lang'       => array('type' => 'INT(2)',                              'default'        => 1, 'unsigned' => true),
                'sort_style' => array('type' => "ENUM('alpha', 'date', 'date_alpha')", 'default'        => 'alpha')
            )
        );
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX . 'module_jobs_location',
            array(
                'id'   => array('type' => 'INT(10)',      'primary' => true, 'auto_increment' => true, 'unsigned' => true),
                'name' => array('type' => 'VARCHAR(100)', 'default' => '')
            )
        );
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_jobs_rel_loc_jobs',
            array(
                'job'        => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
                'location'   => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true)
            )
        );
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX . 'module_jobs_settings',
            array(
                'id'    => array('type' => 'INT(10)',      'primary' => true, 'auto_increment' => true, 'unsigned' => true),
                'name'  => array('type' => 'VARCHAR(250)', 'default' => ''),
                'value' => array('type' => 'TEXT',         'default' => '')
            )
        );

    }
    catch (\Cx\Lib\UpdateException $e) {
        // we COULD do something else here..
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }


    $arrSettings = array(
        array(
            'name'  => 'footnote',
            'value' => 'Hat Ihnen diese Bewerbung zugesagt? \r\nDann können Sie sich sogleich telefonisch, per E-mail oder Web Formular bewerben.'
        ),
        array(
            'name'  => 'link',
            'value' => 'Online für diese Stelle bewerben.'
        ),
        array(
            'name'  => 'url',
            'value' => 'index.php?section=contact&cmd=5&44=%URL%&43=%TITLE%'
        ),
        array(
            'name'  => 'show_location_fe',
            'value' => '1'
        )
    );
    foreach ($arrSettings as $arrSetting) {
        $query = "SELECT 1 FROM `".DBPREFIX."module_jobs_settings` WHERE `name` = '".$arrSetting['name']."'";
        $objResult = $objDatabase->SelectLimit($query, 1);
        if ($objResult !== false) {
            if ($objResult->RecordCount() == 0) {
                $query = "INSERT INTO `".DBPREFIX."module_jobs_settings` (`name`, `value`) VALUES ('".$arrSetting['name']."', '".$arrSetting['value']."')";
                if ($objDatabase->Execute($query) === false) {
                    return _databaseError($query, $objDatabase->ErrorMsg());
                }
            }
        } else {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }


    /********************************
     * EXTENSION:   Timezone        *
     * ADDED:       Contrexx v3.0.0 *
     ********************************/
    try {
        \Cx\Lib\UpdateUtil::sql('
            ALTER TABLE `'.DBPREFIX.'module_jobs`
            CHANGE `startdate` `startdate` TIMESTAMP NOT NULL DEFAULT "0000-00-00 00:00:00",
            CHANGE `enddate` `enddate` TIMESTAMP NOT NULL DEFAULT "0000-00-00 00:00:00"
        ');
    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }


    return true;
}

^

function _jobsInstall()
{
    try {

        /**************************************************************************
         * EXTENSION:   Initial creation (for contrexx editions <> premium with   *
         *              version < 3.0.0 which have this module not yet installed) *
         *                                                                        *
         * ADDED:       Contrexx v3.0.1                                           *
         **************************************************************************/
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_jobs',
            array(
                'id'             => array('type' => 'INT(6)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'date'           => array('type' => 'INT(14)', 'notnull' => false, 'after' => 'id'),
                'title'          => array('type' => 'VARCHAR(250)', 'notnull' => true, 'default' => '', 'after' => 'date'),
                'author'         => array('type' => 'VARCHAR(150)', 'notnull' => true, 'default' => '', 'after' => 'title'),
                'text'           => array('type' => 'mediumtext', 'after' => 'author'),
                'workloc'        => array('type' => 'VARCHAR(250)', 'notnull' => true, 'default' => '', 'after' => 'text'),
                'workload'       => array('type' => 'VARCHAR(250)', 'notnull' => true, 'default' => '', 'after' => 'workloc'),
                'work_start'     => array('type' => 'INT(14)', 'notnull' => true, 'default' => '0', 'after' => 'workload'),
                'catid'          => array('type' => 'INT(2)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'work_start'),
                'lang'           => array('type' => 'INT(2)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'catid'),
                'userid'         => array('type' => 'INT(6)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'lang'),
                'startdate'      => array('type' => 'timestamp', 'notnull' => true, 'default' => '0000-00-00 00:00:00', 'after' => 'userid'),
                'enddate'        => array('type' => 'timestamp', 'notnull' => true, 'default' => '0000-00-00 00:00:00', 'after' => 'startdate'),
                'status'         => array('type' => 'TINYINT(4)', 'notnull' => true, 'default' => '1', 'after' => 'enddate'),
                'changelog'      => array('type' => 'INT(14)', 'notnull' => true, 'default' => '0', 'after' => 'status')
            ),
            array(
                'newsindex'      => array('fields' => array('title','text'), 'type' => 'FULLTEXT')
            ),
            'MyISAM',
            'cx3upgrade'
        );

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_jobs_categories',
            array(
                'catid'          => array('type' => 'INT(2)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'name'           => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => '', 'after' => 'catid'),
                'lang'           => array('type' => 'INT(2)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'after' => 'name'),
                'sort_style'     => array('type' => 'ENUM(\'alpha\',\'date\',\'date_alpha\')', 'notnull' => true, 'default' => 'alpha', 'after' => 'lang')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_jobs_categories` (`catid`, `name`, `lang`, `sort_style`)
            VALUES  (1, 'Informatik', 1, 'alpha'),
                    (2, 'Gastronomie', 1, 'alpha'),
                    (3, 'Baugewerbe', 1, 'alpha'),
                    (4, 'Grafik / Zeichner', 1, 'alpha'),
                    (5, 'Gastronomie', 1, 'alpha'),
                    (6, 'Landwirtschaft', 1, 'alpha'),
                    (7, 'Kaufmännische Berufe', 1, 'alpha'),
                    (8, 'Mechanik', 1, 'alpha'),
                    (9, 'Medizin', 1, 'alpha'),
                    (10, 'Bankgewerbe', 1, 'alpha'),
                    (11, 'Verkauf', 1, 'alpha')
            ON DUPLICATE KEY UPDATE `catid` = `catid`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_jobs_location',
            array(
                'id'         => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'name'       => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => '', 'after' => 'id')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_jobs_location` (`id`, `name`)
            VALUES  (1, 'New York'),
                    (2, 'Tokyo'),
                    (3, 'London'),
                    (4, 'Zürich'),
                    (5, 'Thun'),
                    (6, 'Los Angeles'),
                    (7, 'Shanghai'),
                    (8, 'Moskau'),
                    (9, 'Dublin')
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_jobs_rel_loc_jobs',
            array(
                'job'            => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
                'location'       => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true, 'after' => 'job')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_jobs_settings',
            array(
                'id'         => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'name'       => array('type' => 'VARCHAR(250)', 'notnull' => true, 'default' => '', 'after' => 'id'),
                'value'      => array('type' => 'text', 'after' => 'name')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_jobs_settings` (`id`, `name`, `value`)
            VALUES  (1, 'footnote', 'Hat Ihnen diese Bewerbung zugesagt? \r\nDann können Sie sich sogleich telefonisch, per E-mail oder Web Formular bewerben.'),
                    (2, 'link', 'Online für diese Stelle bewerben.'),
                    (3, 'url', 'index.php?section=contact&cmd=5&44=%URL%&43=%TITLE%'),
                    (4, 'show_location_fe', '1')
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }
}
