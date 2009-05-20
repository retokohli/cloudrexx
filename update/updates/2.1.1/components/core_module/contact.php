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


        $type_t = "ENUM('text','label','checkbox','checkboxGroup','date','file','hidden','password','radio','select','textarea','recipient')";
        UpdateUtil::table(
            DBPREFIX . 'module_contact_form_field',
            array(
                'id'          => array('type' => 'INT',          'notnull' => true, 'primary' => true,      'auto_increment' => true),
                'id_form'     => array('type' => 'INT(11)',      'notnull' => true, 'default' => 0),
                'name'        => array('type' => 'VARCHAR(250)', 'notnull' => true, 'default' => ''),
                'type'        => array('type' => $type_t,        'notnull' => true, 'default' => 'text'),
                'is_required' => array('type' => 'TEXT',         'notnull' => true),
                'check_type'  => array('type' => 'INT(3)',       'notnull' => true, 'default' => 0),
                'order_id'    => array('type' => 'INT(5)',       'notnull' => true, 'default' => 0)
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
