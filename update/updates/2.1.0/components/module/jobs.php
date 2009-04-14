<?php
function _jobsUpdate() {

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
            DBPREFIX . 'module_jobs_rel_loc_jobs',
            array(
                'job'      => array('type' => 'INT(10)', 'primary' => true, 'unsigned' => true),
                'location' => array('type' => 'INT(10)', 'primary' => true, 'unsigned' => true)
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

	// Everything went fine. Return without any errors.
    return true;
}

