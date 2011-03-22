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


        UpdateUtil::table(
            DBPREFIX.'module_contact_form_field',
            array(
                'id'             => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'id_form'        => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'name'           => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'type'           => array('type' => 'ENUM(\'text\',\'label\',\'checkbox\',\'checkboxGroup\',\'date\',\'file\',\'hidden\',\'password\',\'radio\',\'select\',\'textarea\',\'recipient\')', 'notnull' => true, 'default' => 'text'),
                'attributes'     => array('type' => 'TEXT'),
                'is_required'    => array('type' => 'SET(\'0\',\'1\')', 'notnull' => true, 'default' => '0'),
                'check_type'     => array('type' => 'INT(3)', 'notnull' => true, 'default' => '1'),
                'order_id'       => array('type' => 'SMALLINT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0')
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
