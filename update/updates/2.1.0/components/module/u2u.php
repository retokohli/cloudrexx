<?php

function _u2uUpdate()
{
    try{
        UpdateUtil::table(
            DBPREFIX . 'module_u2u_address_list',
            array(
                'id'               => array('type' => 'INT(11)',     'notnull' => true, 'primary' => true, 'auto_increment' => true),
                'user_id'          => array('type' => 'INT(11)',     'notnull' => true),
                'buddies_id'       => array('type' => 'INT(11)',     'notnull' => true),
            )
        );
        UpdateUtil::table(
            DBPREFIX . 'module_u2u_message_log',
            array(
                'message_id'       => array('type' => 'INT(11) UNSIGNED','notnull' => true, 'primary' => true, 'auto_increment' => true),
                'message_text'     => array('type' => 'TEXT',            'notnull' => true),
                'message_title'    => array('type' => 'TEXT',            'notnull' => true),
            ),
            array(),
            'InnoDB'
        );
        UpdateUtil::table(
            DBPREFIX . 'module_u2u_sent_messages',
            array(
                'id'                  => array('type' => 'INT(11) UNSIGNED',      'notnull' => true, 'primary' => true, 'auto_increment' => true),
                'userid'              => array('type' => 'INT(11) UNSIGNED',      'notnull' => true),
                'message_id'          => array('type' => 'INT(11) UNSIGNED',      'notnull' => true),
                'receiver_id'         => array('type' => 'INT(11) UNSIGNED',      'notnull' => true),
                'mesage_open_status'  => array('type' => "ENUM('0','1')",         'notnull' => true, 'default' => '0'),
                'date_time'           => array('type' => "DATETIME",              'notnull' => true),
            ),
            array(),
            'InnoDB'
        );
        UpdateUtil::table(
            DBPREFIX . 'module_u2u_settings',
            array(
                'id'                  => array('type' => 'INT(11) UNSIGNED','notnull' => true, 'primary' => true, 'auto_increment' => true),
                'name'                => array('type' => 'VARCHAR(50)',     'notnull' => true),
                'value'               => array('type' => 'TEXT',            'notnull' => true),
            ),
            array(),
            'InnoDB'
        );
        UpdateUtil::table(
            DBPREFIX . 'module_u2u_user_log',
            array(
                'id'                  => array('type' => 'INT(11) UNSIGNED', 'notnull' => true, 'primary' => true, 'auto_increment' => true),
                'userid'              => array('type' => 'INT(11) UNSIGNED', 'notnull' => true),
                'user_sent_items'     => array('type' => 'INT(11) UNSIGNED', 'notnull' => true),
                'user_unread_items'   => array('type' => 'INT(11) UNSIGNED', 'notnull' => true),
                'user_status'         => array('type' => "ENUM('0','1')",    'notnull' => true, 'default' => '1'),
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
