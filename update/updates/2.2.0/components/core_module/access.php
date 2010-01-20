<?php

function _accessUpdate()
{
    try{
        UpdateUtil::table(
            DBPREFIX.'access_users',
            array(
                'id'                 => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'is_admin'           => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'username'           => array('type' => 'VARCHAR(40)', 'notnull' => false),
                'password'           => array('type' => 'VARCHAR(32)', 'notnull' => false),
                'regdate'            => array('type' => 'INT(14)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'expiration'         => array('type' => 'INT(14)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'validity'           => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'last_auth'          => array('type' => 'INT(14)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'last_activity'      => array('type' => 'INT(14)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'email'              => array('type' => 'VARCHAR(255)', 'notnull' => false),
                'email_access'       => array('type' => 'ENUM(\'everyone\',\'members_only\',\'nobody\')', 'notnull' => true, 'default' => 'nobody'),
                'frontend_lang_id'   => array('type' => 'INT(2)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'backend_lang_id'    => array('type' => 'INT(2)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'active'             => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0'),
                'primary_group'      => array('type' => 'INT(6)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'profile_access'     => array('type' => 'ENUM(\'everyone\',\'members_only\',\'nobody\')', 'notnull' => true, 'default' => 'members_only'),
                'restore_key'        => array('type' => 'VARCHAR(32)', 'notnull' => true, 'default' => ''),
                'restore_key_time'   => array('type' => 'INT(14)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'u2u_active'         => array('type' => 'ENUM(\'0\',\'1\')', 'notnull' => true, 'default' => '1')
            ),
            array(
                'username'           => array('fields' => array('username'))
            )
        );
        UpdateUtil::table(
            DBPREFIX.'access_user_groups',
            array(
                'group_id'           => array('type' => 'INT(6)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'group_name'         => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => ''),
                'group_description'  => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'is_active'          => array('type' => 'TINYINT(4)', 'notnull' => true, 'default' => '1'),
                'type'               => array('type' => 'ENUM(\'frontend\',\'backend\')', 'notnull' => true, 'default' => 'frontend'),
                'homepage'           => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '')
            )
        );


        $arrSettings = array(
            'user_accept_tos_on_signup'     => array('',            '0'),
            'user_captcha'                  => array('',            '0'),
            'profile_thumbnail_method'      => array('crop',        '1'),
            'profile_thumbnail_scale_color' => array('#FFFFFF',     '1')
        );

        foreach ($arrSettings as $key => $arrSetting) {
            if (!UpdateUtil::sql("SELECT 1 FROM `".DBPREFIX."access_settings` WHERE `key` = '".$key."'")->RecordCount()) {
                UpdateUtil::sql("INSERT INTO `".DBPREFIX."access_settings` (
                    SET `key`       = '".$key."',
                        `value`     = '".$arrSetting['value']."',
                        `status`    = '".$arrSetting['status']."'
                )");
            }
        }
    }
    catch (UpdateException $e) {
        // we COULD do something else here..
        DBG::trace();
        return UpdateUtil::DefaultActionHandler($e);
    }

    return true;
}
?>
