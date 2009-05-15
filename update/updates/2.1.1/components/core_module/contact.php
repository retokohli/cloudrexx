<?php

function _contactUpdate()
{
    try{
        UpdateUtil::table(
            DBPREFIX . 'module_contact_recipient',
            array(
                'id'     => array('type' => 'INT',          'notnull' => true, 'primary'     => true,      'auto_increment' => true),
                'id_form'=> array('type' => 'INT(11)',      'notnull' => true, 'default'     => 0),
                'name'   => array('type' => 'VARCHAR(250)', 'notnull' => true, 'default'     => ''),
                'email'  => array('type' => 'VARCHAR(250)', 'notnull' => true, 'default'     => ''),
                'sort'   => array('type' => 'INT(11)',      'notnull' => true, 'default'     => 0),
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
