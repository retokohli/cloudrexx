<?php

function _u2uUpdate()
{
    try{
        #UpdateUtil::table(
        #    DBPREFIX . 'voting_additionaldata',
        #    array(
        #        'id'               => array('type' => 'INT',         'notnull' => true,  'primary'     => true,    'auto_increment' => true),
        #        'voting_system_id' => array('type' => 'INT(14)',     'notnull' => true,                            'renamefrom'   => 'voting_sytem_id'),
        #        'nickname'         => array('type' => 'VARCHAR(80)', 'notnull' => true,  'default'     => '',      'renamefrom'   => 'name'),
        #        'date_entered'     => array('type' => 'TIMESTAMP',   'notnull' => false, 'default_expr'=> 'CURRENT_TIMESTAMP'),
        #        'surname'          => array('type' => 'VARCHAR(80)', 'notnull' => true,  'default'     => ''),
        #        'forename'         => array('type' => 'VARCHAR(80)', 'notnull' => true,  'default'     => ''),
        #        'phone'            => array('type' => 'VARCHAR(80)', 'notnull' => true,  'default'     => ''),
        #        'street'           => array('type' => 'VARCHAR(80)', 'notnull' => true,  'default'     => ''),
        #        'zip'              => array('type' => 'VARCHAR(80)', 'notnull' => true,  'default'     => ''),
        #        'city'             => array('type' => 'VARCHAR(80)', 'notnull' => true,  'default'     => ''),
        #        'email'            => array('type' => 'VARCHAR(80)', 'notnull' => true,  'default'     => ''),
        #        'comment'          => array('type' => 'TEXT',        'notnull' => true,  'default'     => ''),
        #    ),
        #    array( # indexes
        #        'voting_system_id' => array(
        #            'fields'=>array('voting_system_id')
        #        )
        #    )
        #);

    }
    catch (UpdateException $e) {
        // we COULD do something else here..
        return UpdateUtil::DefaultActionHandler($e);
    }

    return true;
}

?>
