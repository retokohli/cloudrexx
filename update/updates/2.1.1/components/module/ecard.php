<?php

function _ecardUpdate()
{
    try{
        UpdateUtil::table(
            DBPREFIX . 'module_ecard_ecards',
            array(
                'code'          => array('type' => 'VARCHAR(35)',  'notnull' => true, 'default'=>'', 'primary'=> true),
                'date'          => array('type' => 'INT(10)',      'notnull' => true, 'default'=> 0, 'unsigned' => true),
                'TTL'           => array('type' => 'INT(10)',      'notnull' => true, 'default'=> 0, 'unsigned' => true),
                'salutation'    => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default'=>''),
                'senderName'    => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default'=>''),
                'senderEmail'   => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default'=>''),
                'recipientName' => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default'=>''),
                'recipientEmail'=> array('type' => 'VARCHAR(100)', 'notnull' => true, 'default'=>''),
                'message'       => array('type' => 'TEXT',         'notnull' => true),
            )
        );
        UpdateUtil::table(
            DBPREFIX . 'module_ecard_settings',
            array(
                'setting_name'  => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default'=>'', 'primary'=> true),
                'setting_value' => array('type' => 'TEXT',         'notnull' => true, 'default'=> 0)
            )
        );
    }
    catch (UpdateException $e) {
        return UpdateUtil::DefaultActionHandler($e);
    }

    return true;
}

?>
