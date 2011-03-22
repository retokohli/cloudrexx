<?php
function _jobsUpdate() {
    global $objDatabase;

    try {
        UpdateUtil::table(
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
        UpdateUtil::table(
            DBPREFIX . 'module_jobs_categories',
            array(
                'catid'      => array('type' => 'INT(2)',           'primary' => true, 'auto_increment' => true, 'unsigned' => true),
                'name'       => array('type' => 'VARCHAR(100)',                        'default'        => ''),
                'lang'       => array('type' => 'INT(2)',                              'default'        => 1, 'unsigned' => true),
                'sort_style' => array('type' => "ENUM('alpha', 'date', 'date_alpha')", 'default'        => 'alpha')
            )
        );
        UpdateUtil::table(
            DBPREFIX . 'module_jobs_location',
            array(
                'id'   => array('type' => 'INT(10)',      'primary' => true, 'auto_increment' => true, 'unsigned' => true),
                'name' => array('type' => 'VARCHAR(100)', 'default' => '')
            )
        );
        UpdateUtil::table(
            DBPREFIX.'module_jobs_rel_loc_jobs',
            array(
                'job'        => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
                'location'   => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true)
            )
        );
        UpdateUtil::table(
            DBPREFIX . 'module_jobs_settings',
            array(
                'id'    => array('type' => 'INT(10)',      'primary' => true, 'auto_increment' => true, 'unsigned' => true),
                'name'  => array('type' => 'VARCHAR(250)', 'default' => ''),
                'value' => array('type' => 'TEXT',         'default' => '')
            )
        );

    }
    catch (UpdateException $e) {
        // we COULD do something else here..
        return UpdateUtil::DefaultActionHandler($e);
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


	// Everything went fine. Return without any errors.
    return true;
}

