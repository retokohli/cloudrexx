<?php

function _votingUpdate()
{
    try{
        UpdateUtil::table(
            DBPREFIX . 'voting_system',
            array(
                'id'               => array('type' =>    'INT',                 'notnull' => true, 'primary'     => true,   'auto_increment' => true),
                'date'             => array('type' =>    'TIMESTAMP',           'notnull' => true, 'default_expr'=> 'CURRENT_TIMESTAMP'),
                'title'            => array('type' =>    'VARCHAR(60)',         'notnull' => true, 'default'     => '',     'renamefrom' => 'name'),
                'question'         => array('type' =>    'TEXT',                'notnull' => false),
                'status'           => array('type' =>    'TINYINT(1)',          'notnull' => false,'default'     => 1),
                'votes'            => array('type' =>    'INT(11)',             'notnull' => false,'default'     => 0),
                'submit_check'     => array('type' => "ENUM('cookie','email')", 'notnull' => true, 'default'    => 'cookie'),
                'additional_nickname' => array('type' => 'TINYINT(1)',          'notnull' => true, 'default'     => 0),
                'additional_forename' => array('type' => 'TINYINT(1)',          'notnull' => true, 'default'     => 0),
                'additional_surname'  => array('type' => 'TINYINT(1)',          'notnull' => true, 'default'     => 0),
                'additional_phone'    => array('type' => 'TINYINT(1)',          'notnull' => true, 'default'     => 0),
                'additional_street'   => array('type' => 'TINYINT(1)',          'notnull' => true, 'default'     => 0),
                'additional_zip'      => array('type' => 'TINYINT(1)',          'notnull' => true, 'default'     => 0),
                'additional_email'    => array('type' => 'TINYINT(1)',          'notnull' => true, 'default'     => 0),
                'additional_city'     => array('type' => 'TINYINT(1)',          'notnull' => true, 'default'     => 0),
                'additional_comment'  => array('type' => 'TINYINT(1)',          'notnull' => true, 'default'     => 0),
            )
        );
        UpdateUtil::table(
            DBPREFIX.'voting_additionaldata',
            array(
                'id'                 => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'nickname'           => array('type' => 'VARCHAR(80)', 'notnull' => true, 'default' => '', 'renamefrom' => 'name'),
                'surname'            => array('type' => 'VARCHAR(80)', 'notnull' => true, 'default' => ''),
                'phone'              => array('type' => 'VARCHAR(80)', 'notnull' => true, 'default' => ''),
                'street'             => array('type' => 'VARCHAR(80)', 'notnull' => true, 'default' => ''),
                'zip'                => array('type' => 'VARCHAR(30)', 'notnull' => true, 'default' => ''),
                'city'               => array('type' => 'VARCHAR(80)', 'notnull' => true, 'default' => ''),
                'email'              => array('type' => 'VARCHAR(80)', 'notnull' => true, 'default' => ''),
                'comment'            => array('type' => 'TEXT', 'after' => 'email'),
                'voting_system_id'   => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'renamefrom' => 'voting_sytem_id'),
                'date_entered'       => array('type' => 'TIMESTAMP', 'notnull' => true, 'default_expr'=> 'CURRENT_TIMESTAMP', 'on_update' => 'CURRENT_TIMESTAMP'),
                'forename'           => array('type' => 'VARCHAR(80)', 'notnull' => true, 'default' => '')
            ),
            array(
                'voting_system_id'   => array('fields' => array('voting_system_id'))
            )
        );
        UpdateUtil::table(
            DBPREFIX.'voting_email',
            array(
                'id'     => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'email'  => array('type' => 'VARCHAR(255)'),
                'valid'  => array('type' => 'ENUM(\'0\',\'1\')', 'notnull' => true, 'default' => '0')
            ),
            array(
                'email'  => array('fields' => array('email'), 'type' => 'UNIQUE')
            )
        );
        UpdateUtil::table(
            DBPREFIX.'voting_rel_email_system',
            array(
                'email_id'   => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'system_id'  => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'voting_id'  => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'valid'      => array('type' => 'ENUM(\'0\',\'1\')', 'notnull' => true, 'default' => '0')
            ),
            array(
                'email_id'   => array('fields' => array('email_id','system_id'), 'type' => 'UNIQUE')
            )
        );
    }
    catch (UpdateException $e) {
        // we COULD do something else here..
        return UpdateUtil::DefaultActionHandler($e);
    }

    return true;
}

?>
