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
                'additional_city'     => array('type' => 'TINYINT(1)',          'notnull' => true, 'default'     => 0),
                'additional_email'    => array('type' => 'TINYINT(1)',          'notnull' => true, 'default'     => 0),
                'additional_comment'  => array('type' => 'TINYINT(1)',          'notnull' => true, 'default'     => 0),
            )
        );
        UpdateUtil::table(
            DBPREFIX . 'voting_additionaldata',
            array(
                'id'               => array('type' => 'INT',         'notnull' => true,  'primary'     => true,    'auto_increment' => true),
                'voting_system_id' => array('type' => 'INT(11)',     'notnull' => true,                            'renamefrom'   => 'voting_sytem_id'),
                'nickname'         => array('type' => 'VARCHAR(80)', 'notnull' => true,  'default'     => '',      'renamefrom'   => 'name'),
                'date_entered'     => array('type' => 'TIMESTAMP',   'notnull' => true, 'default_expr'=> 'CURRENT_TIMESTAMP'),
                'surname'          => array('type' => 'VARCHAR(80)', 'notnull' => true,  'default'     => ''),
                'forename'         => array('type' => 'VARCHAR(80)', 'notnull' => true,  'default'     => ''),
                'phone'            => array('type' => 'VARCHAR(80)', 'notnull' => true,  'default'     => ''),
                'street'           => array('type' => 'VARCHAR(80)', 'notnull' => true,  'default'     => ''),
                'zip'              => array('type' => 'VARCHAR(30)', 'notnull' => true,  'default'     => ''),
                'city'             => array('type' => 'VARCHAR(80)', 'notnull' => true,  'default'     => ''),
                'email'            => array('type' => 'VARCHAR(80)', 'notnull' => true,  'default'     => ''),
                'comment'          => array('type' => 'TEXT',        'notnull' => true),
            ),
            array( # indexes
                'voting_system_id' => array(
                    'fields'=>array('voting_system_id')
                )
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
