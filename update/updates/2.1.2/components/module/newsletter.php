<?php

function _newsletterUpdate()
{
    try{
        UpdateUtil::table(
            DBPREFIX.'module_newsletter_category',
            array(
                'id'                     => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'status'                 => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0'),
                'name'                   => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'notification_email'     => array('type' => 'VARCHAR(250)')
            ),
            array(
                'name'                   => array('fields' => array('name'))
            )
        );

        UpdateUtil::table(
            DBPREFIX.'module_newsletter_confirm_mail',
            array(
                'id'             => array('type' => 'INT(1)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'title'          => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'content'        => array('type' => 'LONGTEXT'),
                'recipients'     => array('type' => 'TEXT')
            )
        );

        UpdateUtil::table(
            DBPREFIX.'module_newsletter',
            array(
                'id'             => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'subject'        => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'template'       => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0'),
                'content'        => array('type' => 'TEXT'),
                'content_text'   => array('type' => 'TEXT'),
                'attachment'     => array('type' => 'ENUM(\'0\',\'1\')', 'notnull' => true, 'default' => '0'),
                'format'         => array('type' => 'ENUM(\'text\',\'html\',\'html/text\')', 'notnull' => true, 'default' => 'text', 'after' => 'attachment'),
                'priority'       => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0'),
                'sender_email'   => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'sender_name'    => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'return_path'    => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'smtp_server'    => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'status'         => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0'),
                'count'          => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0'),
                'date_create'    => array('type' => 'INT(14)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'date_sent'      => array('type' => 'INT(14)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'tmp_copy'       => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0')
            )
        );

        UpdateUtil::table(
            DBPREFIX.'module_newsletter_user',
            array(
                'id'         => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'code'       => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'email'      => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'uri'        => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'email'),
                'sex'        => array('type' => 'ENUM(\'m\',\'f\')', 'notnull' => false),
                'title'      => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'lastname'   => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'firstname'  => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'company'    => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'street'     => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'zip'        => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'city'       => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'country'    => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'phone'      => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'birthday'   => array('type' => 'VARCHAR(10)', 'notnull' => true, 'default' => '00-00-0000'),
                'status'     => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0'),
                'emaildate'  => array('type' => 'INT(14)', 'unsigned' => true, 'notnull' => true, 'default' => '0')
            ),
            array(
                'email'      => array('fields' => array('email'), 'type' => 'UNIQUE'),
                'status'     => array('fields' => array('status'))
            )
        );
    }
    catch (UpdateException $e) {
        return UpdateUtil::DefaultActionHandler($e);
    }

    return true;
}

?>
