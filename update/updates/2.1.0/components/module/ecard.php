<?php

function _ecardUpdate()
{
    try{
        UpdateUtil::table(
            DBPREFIX . 'module_ecard_ecards',
            array(
                'code'          => array('type' => 'VARCHAR(35)',  'notnull' => true, 'default'=>'', 'primary'=> true),
                'date'          => array('type' => 'INT(10)',      'notnull' => true, 'default'=> 0),
                'TTL'           => array('type' => 'INT(10)',      'notnull' => true, 'default'=> 0),
                'salutation'    => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default'=> 0),
                'senderName'    => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default'=> 0),
                'senderEmail'   => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default'=> 0),
                'recipientName' => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default'=> 0),
                'recipientEmail'=> array('type' => 'VARCHAR(100)', 'notnull' => true, 'default'=> 0),
                'message'       => array('type' => 'TEXT',         'notnull' => true, 'default'=> 0),
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
