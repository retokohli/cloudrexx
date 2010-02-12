<?php

function _u2uUpdate()
{
    global $objDatabase;

    try{
        UpdateUtil::table(
            DBPREFIX.'module_u2u_address_list',
            array(
                'id'             => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'user_id'        => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0'),
                'buddies_id'     => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0')
            ),
            array(),
            'InnoDB'
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
            DBPREFIX.'module_u2u_sent_messages',
            array(
                'id'                     => array('type' => 'INT(11)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'userid'                 => array('type' => 'INT(11)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'message_id'             => array('type' => 'INT(11)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'receiver_id'            => array('type' => 'INT(11)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'mesage_open_status'     => array('type' => 'ENUM(\'0\',\'1\')', 'notnull' => true, 'default' => '0'),
                'date_time'              => array('type' => 'DATETIME', 'notnull' => true, 'default' => '0000-00-00 00:00:00')
            ),
            array(),
            'InnoDB'

        );
        UpdateUtil::table(
            DBPREFIX.'module_u2u_settings',
            array(
                'id'     => array('type' => 'INT(11)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'name'   => array('type' => 'VARCHAR(50)'),
                'value'  => array('type' => 'TEXT')
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
            DBPREFIX.'module_u2u_user_log',
            array(
                'id'                 => array('type' => 'INT(11)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'userid'             => array('type' => 'INT(11)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'user_sent_items'    => array('type' => 'INT(11)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'user_unread_items'  => array('type' => 'INT(11)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'user_status'        => array('type' => 'ENUM(\'0\',\'1\')', 'notnull' => true, 'default' => '1')
            ),
            array(),
            'InnoDB'

        );
    }
    catch (UpdateException $e) {
        // we COULD do something else here..
        return UpdateUtil::DefaultActionHandler($e);
    }




	/************************************************
	* EXTENSION:	Initial adding of the           *
    *               settings values                 *
	* ADDED:		Contrexx v2.1.2					*
	************************************************/
    $arrSettings = array(
        'max_posting_size'           => '2000',
        'max_posting_chars'          => '2000',
        'wysiwyg_editor'                 => '1',
        'subject'              => 'Eine neue Nachricht von [senderName]',
        'from'              => 'Contrexx U2U Nachrichtensystem',
        'email_message'               => 'Hallo <strong>[receiverName]</strong>,<br />\r\n<br />\r\n<strong>[senderName]</strong> hat Ihnen eine private Nachricht gesendet. Um die Nachricht zu lesen, folgen Sie bitte folgendem Link:<br />\r\n<br />\r\nhttp://[domainName]/index.php?section=u2u&amp;cmd=notification<br />\r\n&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <br />\r\n<br />'
    );

    foreach ($arrSettings as $name => $value) {
        $query = "SELECT 1 FROM `".DBPREFIX."module_u2u_settings` WHERE `name` = '".$name."'";
        $objResult = $objDatabase->SelectLimit($query, 1);
        if ($objResult) {
            if ($objResult->RecordCount() == 0) {
                $query = "INSERT INTO `".DBPREFIX."module_u2u_settings` (`name`, `value`) VALUES ('".$name."', '".$value."')";
                if ($objDatabase->Execute($query) === false) {
                    return _databaseError($query, $objDatabase->ErrorMsg());
                }
            }
        } else {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    return true;
}

?>
