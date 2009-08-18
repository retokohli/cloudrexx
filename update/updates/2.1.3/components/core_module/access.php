<?php

function _accessUpdate()
{
    global $objDatabase, $_CONFIG, $_ARRAYLANG, $_CORELANG;

    $arrTables = $objDatabase->MetaTables('TABLES');
    if (!$arrTables) {
        setUpdateMsg($_ARRAYLANG['TXT_UNABLE_DETERMINE_DATABASE_STRUCTURE']);
        return false;
    }

    /****************************
     *
     * ADD NOTIFICATION E-MAILS
     *
     ***************************/
    try{
        UpdateUtil::table(
            DBPREFIX.'access_user_mail',
            array(
                'type'           => array('type' => 'ENUM(\'reg_confirm\',\'reset_pw\',\'user_activated\',\'user_deactivated\',\'new_user\')', 'notnull' => true, 'default' => 'reg_confirm'),
                'lang_id'        => array('type' => 'TINYINT(2)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'sender_mail'    => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'sender_name'    => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'subject'        => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'format'         => array('type' => 'ENUM(\'text\',\'html\',\'multipart\')', 'notnull' => true, 'default' => 'text'),
                'body_text'      => array('type' => 'TEXT'),
                'body_html'      => array('type' => 'TEXT')
            ),
            array(
                'mail'           => array('fields' => array('type','lang_id'), 'type' => 'UNIQUE')
            )
        );
    }
    catch (UpdateException $e) {
        // we COULD do something else here..
        DBG::trace();
        return UpdateUtil::DefaultActionHandler($e);
    }

    $arrMails = array(
        array(
            'type'            => 'reg_confirm',
            'subject'        => 'Benutzerregistrierung bestätigen',
            'body_text'    => 'Hallo [[USERNAME]],\r\n\r\nVielen Dank für Ihre Anmeldung bei [[HOST]].\r\nBitte klicken Sie auf den folgenden Link, um Ihre E-Mail-Adresse zu bestätigen:\r\n[[ACTIVATION_LINK]]\r\n\r\nUm sich später einzuloggen, geben Sie bitte Ihren Benutzernamen \"[[USERNAME]]\" und das Passwort ein, das Sie bei der Registrierung festgelegt haben.\r\n\r\n\r\n--\r\nIhr [[SENDER]]'
        ),
        array(
            'type'            => 'reset_pw',
            'subject'        => 'Kennwort zurücksetzen',
            'body_text'        => 'Hallo [[USERNAME]],\r\n\r\nUm ein neues Passwort zu wählen, müssen Sie auf die unten aufgeführte URL gehen und dort Ihr neues Passwort eingeben.\r\n\r\nWICHTIG: Die Gültigkeit der URL wird nach 60 Minuten verfallen, nachdem diese E-Mail abgeschickt wurde.\r\nFalls Sie mehr Zeit benötigen, geben Sie Ihre E-Mail Adresse einfach ein weiteres Mal ein.\r\n\r\nIhre URL:\r\n[[URL]]\r\n\r\n\r\n--\r\n[[SENDER]]'
        ),
        array(
            'type'            => 'user_activated',
            'subject'        => 'Ihr Benutzerkonto wurde aktiviert',
            'body_text'        => 'Hallo [[USERNAME]],\r\n\r\nIhr Benutzerkonto auf [[HOST]] wurde soeben aktiviert und kann von nun an verwendet werden.\r\n\r\n\r\n--\r\n[[SENDER]]'
        ),
        array(
            'type'            => 'user_deactivated',
            'subject'        => 'Ihr Benutzerkonto wurde deaktiviert',
            'body_text'        => 'Hallo [[USERNAME]],\r\n\r\nIhr Benutzerkonto auf [[HOST]] wurde soeben deaktiviert.\r\n\r\n\r\n--\r\n[[SENDER]]'
        ),
        array(
            'type'            => 'new_user',
            'subject'        => 'Ein neuer Benutzer hat sich registriert',
            'body_text'        => 'Der Benutzer [[USERNAME]] hat sich soeben registriert und muss nun frei geschaltet werden.\r\n\r\nÜber die folgende Adresse kann das Benutzerkonto von [[USERNAME]] verwaltet werden:\r\n[[LINK]]\r\n\r\n\r\n--\r\n[[SENDER]]'
        )
    );

    foreach ($arrMails as $arrMail) {
        $query = "SELECT 1 FROM `".DBPREFIX."access_user_mail` WHERE `type` = '".$arrMail['type']."'";
        $objMail = $objDatabase->SelectLimit($query, 1);
        if ($objMail !== false) {
            if ($objMail->RecordCount() == 0) {
                $query = "INSERT INTO `".DBPREFIX."access_user_mail` (
                    `type`,
                    `lang_id`,
                    `sender_mail`,
                    `sender_name`,
                    `subject`,
                    `body_text`,
                    `body_html`
                ) VALUES (
                    '".$arrMail['type']."',
                    0,
                    '".addslashes($_CONFIG['coreAdminEmail'])."',
                    '".addslashes($_CONFIG['coreAdminName'])."',
                    '".$arrMail['subject']."',
                    '".$arrMail['body_text']."',
                    ''
                )";
                if ($objDatabase->Execute($query) === false) {
                    return _databaseError($query, $objDatabase->ErrorMsg());
                }
            }
        } else {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }



    /****************
     *
     * ADD SETTINGS
     *
     ***************/
    if (!in_array(DBPREFIX."access_settings", $arrTables)) {
        $query = "
            CREATE TABLE `".DBPREFIX."access_settings` (
                `key` VARCHAR( 32 ) NOT NULL DEFAULT '',
                `value` VARCHAR( 255 ) NOT NULL DEFAULT '',
                `status` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0',
                UNIQUE (
                    `key`
                )
            ) TYPE = InnoDB
        ";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    if (in_array(DBPREFIX."communit_config", $arrTables)) {
        $query = 'SELECT `name`, `value`, `status` FROM `'.DBPREFIX.'community_config`';
        $objResult = $objDatabase->Execute($query);
        if ($objResult) {
            while (!$objResult->EOF) {
                $arrCommunityConfig[$objResult->fields['name']] = array(
                    'value'        => $objResult->fields['value'],
                    'status'    => $objResult->fields['status']
                );
                $objResult->MoveNext();
            }
        } else {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    $arrSettings = array(
        'user_activation'                 =>array('value'=> '',               'status'    => isset($arrCommunityConfig['user_activation']['status']) ? $arrCommunityConfig['user_activation']['status'] : 0),
        'user_activation_timeout'         =>array('value'=> isset($arrCommunityConfig['user_activation_timeout']['value']) ? $arrCommunityConfig['user_activation_timeout']['value'] : 0, 'status'    => isset($arrCommunityConfig['user_activation_timeout']['status']) ? $arrCommunityConfig['user_activation_timeout']['status'] : 0),
        'assigne_to_groups'               =>array('value'=> isset($arrCommunityConfig['community_groups']['value']) ? $arrCommunityConfig['community_groups']['value'] : '', 'status'    => 1),
        'max_profile_pic_width'           =>array('value'=>'160',            'status'  => 1),
        'max_profile_pic_height'          =>array('value'=>'160',            'status'  => 1),
        'profile_thumbnail_pic_width'     =>array('value'=>'50',             'status'  => 1),
        'profile_thumbnail_pic_height'    =>array('value'=>'50',             'status'  => 1),
        'max_profile_pic_size'            =>array('value'=>'30000',          'status'  => 1),
        'max_pic_width'                   =>array('value'=>'600',            'status'  => 1),
        'max_pic_height'                  =>array('value'=>'600',            'status'  => 1),
        'max_thumbnail_pic_width'         =>array('value'=>'130',            'status'  => 1),
        'max_thumbnail_pic_height'        =>array('value'=>'130',            'status'  => 1),
        'max_pic_size'                    =>array('value'=>'200000',         'status'  => 1),
        'notification_address'            =>array('value'=>addslashes($_CONFIG['coreAdminEmail']), 'status'=>1),
        'user_config_email_access'        =>array('value'=> '',              'status'    => 1),
        'user_config_profile_access'      =>array('value'=> '',              'status'    => 1),
        'default_email_access'            =>array('value'=> 'members_only',  'status'    => 1),
        'default_profile_access'          =>array('value'=> 'members_only',  'status'    => 1),
        'user_delete_account'             =>array('value'=> '',              'status'    => 1),
        'block_currently_online_users'    =>array('value'=> '10',            'status'    => 0),
        'block_currently_online_users_pic'=>array('value'=> '',              'status'    => 0),
        'block_last_active_users'         =>array('value'=> '10',            'status'    => 0),
        'block_last_active_users_pic'     =>array('value'=> '',              'status'    => 0),
        'block_latest_reg_users'          =>array('value'=> '10',            'status'    => 0),
        'block_latest_reg_users_pic'      =>array('value'=> '',              'status'    => 0),
        'block_birthday_users'            =>array('value'=> '10',            'status'    => 0),
        'block_birthday_users_pic'        =>array('value'=> '',              'status'    => 0),
        'session_user_interval'           =>array('value'=> '0',             'status'    => 1)
    );

    foreach ($arrSettings as $key => $arrSetting) {
        $query = "SELECT 1 FROM `".DBPREFIX."access_settings` WHERE `key` = '".$key."'";
        $objResult = $objDatabase->SelectLimit($query, 1);
        if ($objResult !== false) {
            if ($objResult->RecordCount() == 0) {
                $query = "INSERT INTO `".DBPREFIX."access_settings` (
                    `key`,
                    `value`,
                    `status`
                ) VALUES (
                    '".$key."',
                    '".$arrSetting['value']."',
                    '".$arrSetting['status']."'
                )";
                if ($objDatabase->Execute($query) === false) {
                    return _databaseError($query, $objDatabase->ErrorMsg());
                }
            }
        } else {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }


    try{
        // delete obsolete table community_config
        UpdateUtil::drop_table(DBPREFIX.'community_config');

        // delete obsolete table community_config
        UpdateUtil::drop_table(DBPREFIX.'user_validity');
    }
    catch (UpdateException $e) {
        // we COULD do something else here..
        DBG::trace();
        return UpdateUtil::DefaultActionHandler($e);
    }


    /********************
     *
     * ADD USER PROFILE
     *
     *******************/
    try {
        UpdateUtil::table(
            DBPREFIX.'access_user_profile',
            array(
                'user_id'        => array('type' => 'INT(10)', 'unsigned' => true, 'primary' => true, 'default' => '0'),
                'gender'         => array('type' => 'ENUM(\'gender_undefined\', \'gender_female\', \'gender_male\')', 'notnull' => true, 'default' => 'gender_undefined'),
                'title'          => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'firstname'      => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'lastname'       => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'company'        => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'address'        => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'city'           => array('type' => 'VARCHAR(50)', 'notnull' => true, 'default' => ''),
                'zip'            => array('type' => 'VARCHAR(10)', 'notnull' => true, 'default' => ''),
                'country'        => array('type' => 'SMALLINT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'phone_office'   => array('type' => 'VARCHAR(20)', 'notnull' => true, 'default' => ''),
                'phone_private'  => array('type' => 'VARCHAR(20)', 'notnull' => true, 'default' => ''),
                'phone_mobile'   => array('type' => 'VARCHAR(20)', 'notnull' => true, 'default' => ''),
                'phone_fax'      => array('type' => 'VARCHAR(20)', 'notnull' => true, 'default' => ''),
                'birthday'       => array('type' => 'VARCHAR(11)', 'notnull' => false),
                'website'        => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'profession'     => array('type' => 'VARCHAR(150)', 'notnull' => true, 'default' => ''),
                'interests'      => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'signature'      => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'picture'        => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '')
            ),
            array(
                'profile'        => array('fields' => array('firstname' => 100, 'lastname' => 100, 'company' => 50))
            )
        );
    }
    catch (UpdateException $e) {
        return UpdateUtil::DefaultActionHandler($e);
    }



    /***************************
     *
     * MIGRATE GROUP RELATIONS
     *
     **************************/
    if (!in_array(DBPREFIX."access_rel_user_group", $arrTables)) {
        $query = "
            CREATE TABLE `".DBPREFIX."access_rel_user_group` (
                `user_id` int(10) unsigned NOT NULL DEFAULT '0',
                `group_id` int(10) unsigned NOT NULL DEFAULT '0',
                PRIMARY KEY  (`user_id`,`group_id`)
            ) TYPE=InnoDB
        ";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    $arrColumns = $objDatabase->MetaColumnNames(DBPREFIX.'access_users');
    if ($arrColumns === false) {
        setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'access_users'));
        return false;
    }

    if (in_array('groups', $arrColumns)) {
        $query = "SELECT `id`, `groups` FROM ".DBPREFIX."access_users WHERE `groups` != ''";
        $objUser = $objDatabase->Execute($query);
        if ($objUser) {
            while (!$objUser->EOF) {
                $arrGroups = explode(',', $objUser->fields['groups']);
                foreach ($arrGroups as $groupId) {
                    $query = "SELECT 1 FROM ".DBPREFIX."access_rel_user_group WHERE `user_id` = ".$objUser->fields['id']." AND `group_id` = ".intval($groupId);
                    $objRel = $objDatabase->SelectLimit($query, 1);
                    if ($objRel) {
                        if ($objRel->RecordCount() == 0) {
                            $query = "INSERT INTO ".DBPREFIX."access_rel_user_group (`user_id`, `group_id`) VALUES (".$objUser->fields['id'].", ".intval($groupId).")";
                            if ($objDatabase->Execute($query) === false) {
                                return _databaseError($query, $objDatabase->ErrorMsg());
                            }
                        }
                    } else {
                        return _databaseError($query, $objDatabase->ErrorMsg());
                    }
                }

                $objUser->MoveNext();
            }
        } else {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }

        $query = "ALTER TABLE `".DBPREFIX."access_users` DROP `groups`";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }



    /*********************
     *
     * ADD USER VALIDITY
     *
     ********************/
    try {
        UpdateUtil::table(
            DBPREFIX.'access_user_validity',
            array(
                'validity'   => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true)
            )
        );
    }
    catch (UpdateException $e) {
        return UpdateUtil::DefaultActionHandler($e);
    }

    $query = "SELECT 1 FROM `".DBPREFIX."access_user_validity`";
    $objResult = $objDatabase->SelectLimit($query, 1);
    if ($objResult) {
        if ($objResult->RecordCount() == 0) {
            $query = "
                INSERT INTO `".DBPREFIX."access_user_validity` (`validity`) VALUES
                    ('0'), ('1'), ('15'), ('31'), ('62'),
                    ('92'), ('123'), ('184'), ('366'), ('731')
                ";
            if ($objDatabase->Execute($query) === false) {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
    } else {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }

    /********************
     *
     * MIGRATE PROFILES
     *
     *******************/
    if (in_array('firstname', $arrColumns)) {
        $query = "SELECT `id`, `firstname`, `lastname`, `residence`, `profession`, `interests`, `webpage`, `company`, `zip`, `phone`, `mobile`, `street` FROM `".DBPREFIX."access_users`";
        $objUser = $objDatabase->Execute($query);
        if ($objUser) {
            while (!$objUser->EOF) {
                $query = "SELECT 1 FROM `".DBPREFIX."access_user_profile` WHERE `user_id` = ".$objUser->fields['id'];
                $objProfile = $objDatabase->SelectLimit($query, 1);
                if ($objProfile) {
                    if ($objProfile->RecordCount() == 0) {
                        $query = "INSERT INTO `".DBPREFIX."access_user_profile` (
                            `user_id`,
                            `gender`,
                            `firstname`,
                            `lastname`,
                            `company`,
                            `address`,
                            `city`,
                            `zip`,
                            `country`,
                            `phone_office`,
                            `phone_private`,
                            `phone_mobile`,
                            `phone_fax`,
                            `website`,
                            `profession`,
                            `interests`,
                            `picture`
                        ) VALUES (
                            ".$objUser->fields['id'].",
                            'gender_undefined',
                            '".addslashes($objUser->fields['firstname'])."',
                            '".addslashes($objUser->fields['lastname'])."',
                            '".addslashes($objUser->fields['company'])."',
                            '".addslashes($objUser->fields['street'])."',
                            '".addslashes($objUser->fields['residence'])."',
                            '".addslashes($objUser->fields['zip'])."',
                            0,
                            '',
                            '".addslashes($objUser->fields['phone'])."',
                            '".addslashes($objUser->fields['mobile'])."',
                            '',
                            '".addslashes($objUser->fields['webpage'])."',
                            '".addslashes($objUser->fields['profession'])."',
                            '".addslashes($objUser->fields['interests'])."',
                            ''
                        )";
                        if ($objDatabase->Execute($query) === false) {
                            return _databaseError($query, $objDatabase->ErrorMsg());
                        }
                    }
                } else {
                    return _databaseError($query, $objDatabase->ErrorMsg());
                }

                $objUser->MoveNext();
            }
        } else {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    $arrRemoveColumns = array(
        'firstname',
        'lastname',
        'residence',
        'profession',
        'interests',
        'webpage',
        'company',
        'zip',
        'phone',
        'mobile',
        'street',
        'levelid'
    );

    foreach ($arrRemoveColumns as $column) {
        if (in_array($column, $arrColumns)) {
            $query = "ALTER TABLE ".DBPREFIX."access_users DROP `".$column."`";
            if ($objDatabase->Execute($query) === false) {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
    }

    $arrColumnDetails = $objDatabase->MetaColumns(DBPREFIX.'access_users');
    if ($arrColumnDetails === false) {
        setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'access_users'));
        return false;
    }

    if (in_array('regdate', $arrColumns)) {
        if ($arrColumnDetails['REGDATE']->type == 'date') {
            if (!in_array('regdate_new', $arrColumns)) {
                $query = "ALTER TABLE `".DBPREFIX."access_users` ADD `regdate_new` INT( 14 ) UNSIGNED NULL DEFAULT '0' AFTER `regdate`";
                if ($objDatabase->Execute($query) === false) {
                    return _databaseError($query, $objDatabase->ErrorMsg());
                }
            }

            $query = "UPDATE `".DBPREFIX."access_users` SET `regdate_new` = UNIX_TIMESTAMP(`regdate`), `regdate` = '0000-00-00' WHERE `regdate` != '0000-00-00'";
            if ($objDatabase->Execute($query) === false) {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }

            $query = "ALTER TABLE `".DBPREFIX."access_users` DROP `regdate`";
            if ($objDatabase->Execute($query) === false) {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
    }

    $arrColumns = $objDatabase->MetaColumnNames(DBPREFIX.'access_users');
    if ($arrColumns === false) {
        setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'access_users'));
        return false;
    }

    if (in_array('regdate_new', $arrColumns)) {
        $query = "ALTER TABLE `".DBPREFIX."access_users` CHANGE `regdate_new` `regdate` INT( 14 ) UNSIGNED NOT NULL DEFAULT '0'";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    $query = "ALTER TABLE `".DBPREFIX."access_users` CHANGE `is_admin` `is_admin` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0'";
    if ($objDatabase->Execute($query) === false) {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }

    if (!in_array('email_access', $arrColumns)) {
        $query = "ALTER TABLE `".DBPREFIX."access_users` ADD `email_access` ENUM( 'everyone', 'members_only', 'nobody' ) NOT NULL DEFAULT 'nobody' AFTER `email`";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    if (!in_array('profile_access', $arrColumns)) {
        $query = "ALTER TABLE `".DBPREFIX."access_users` ADD `profile_access` ENUM( 'everyone', 'members_only', 'nobody' ) NOT NULL DEFAULT 'members_only' AFTER `active`";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    if (!in_array('frontend_lang_id', $arrColumns)) {
        $query = "ALTER TABLE `".DBPREFIX."access_users` CHANGE `langId` `frontend_lang_id` INT( 2 ) UNSIGNED NOT NULL DEFAULT '0'";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    if (!in_array('backend_lang_id', $arrColumns)) {
        $query = "ALTER TABLE `".DBPREFIX."access_users` ADD `backend_lang_id` INT( 2 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `frontend_lang_id`";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        } else {
             $query = "UPDATE `".DBPREFIX."access_users` SET `backend_lang_id` = `frontend_lang_id`";
             if ($objDatabase->Execute($query) === false) {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
    }

    if (!in_array('last_auth', $arrColumns)) {
        $query = "ALTER TABLE `".DBPREFIX."access_users` ADD `last_auth` INT( 14 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `regdate`";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    if (!in_array('last_activity', $arrColumns)) {
        $query = "ALTER TABLE `".DBPREFIX."access_users` ADD `last_activity` INT( 14 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `last_auth`";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    if (!in_array('expiration', $arrColumns)) {
        $query = "ALTER TABLE `".DBPREFIX."access_users` ADD `expiration` INT( 14 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `regdate`";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    if (!in_array('validity', $arrColumns)) {
        $query = "ALTER TABLE `".DBPREFIX."access_users` ADD `validity` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `expiration`";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    } else {
        $query = "UPDATE `".DBPREFIX."access_users` SET `expiration` = `validity`*60*60*24+`regdate` WHERE `expiration` = 0 AND `validity` > 0";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }



    /***********************************
     *
     * MIGRATE COMMUNITY CONTENT PAGES
     *
     **********************************/
    foreach (
        array(
            DBPREFIX.'content',
            DBPREFIX.'content_history'
        ) as $dbTable
    ) {
        $query = "
            UPDATE    `".$dbTable."`
                SET    `content` = REPLACE(
                                    REPLACE(
                                        REPLACE(
                                            REPLACE(
                                                    REPLACE(
                                                    `content`,
                                                    'section=community&cmd=profile',
                                                    'section=access&cmd=settings'
                                                ),
                                                'section=community&amp;cmd=profile',
                                                'section=access&amp;cmd=settings'
                                            ),
                                            'section=community&cmd=register',
                                            'section=access&cmd=signup'
                                        ),
                                        'section=community&amp;cmd=register',
                                        'section=access&amp;cmd=signup'
                                    ),
                                    'section=community',
                                    'section=access'
                                ),
                    `redirect` = REPLACE(
                                    REPLACE(
                                        REPLACE(
                                            REPLACE(
                                                    REPLACE(
                                                    `redirect`,
                                                    'section=community&cmd=profile',
                                                    'section=access&cmd=settings'
                                                ),
                                                'section=community&amp;cmd=profile',
                                                'section=access&amp;cmd=settings'
                                            ),
                                            'section=community&cmd=register',
                                            'section=access&cmd=signup'
                                        ),
                                        'section=community&amp;cmd=register',
                                        'section=access&amp;cmd=signup'
                                    ),
                                    'section=community',
                                    'section=access'
                                )
            WHERE    `content` LIKE '%section=community%' OR `redirect` LIKE '%section=community%'
            ";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }



    /***********************************
     *
     * CREATE PROFILE ATTRIBUTE TABLES
     *
     **********************************/
    if (!in_array(DBPREFIX."access_user_attribute", $arrTables)) {
        $query = "
            CREATE TABLE `".DBPREFIX."access_user_attribute` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                `parent_id` INT UNSIGNED NOT NULL DEFAULT '0',
                `type` ENUM( 'text', 'textarea', 'mail', 'uri', 'date', 'image', 'menu', 'menu_option','group', 'frame', 'history' ) NOT NULL DEFAULT 'text',
                `mandatory` ENUM( '0', '1' ) NOT NULL DEFAULT '0',
                `sort_type` ENUM( 'asc', 'desc', 'custom' ) NOT NULL DEFAULT 'asc',
                `order_id` INT UNSIGNED NOT NULL DEFAULT '0',
                `access_special` ENUM( '', 'menu_select_higher', 'menu_select_lower' ) NOT NULL DEFAULT '',
                `access_id` INT UNSIGNED NOT NULL DEFAULT '0'
            ) TYPE = InnoDB
        ";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    if (!in_array(DBPREFIX."access_user_attribute_name", $arrTables)) {
        $query = "
            CREATE TABLE `".DBPREFIX."access_user_attribute_name` (
                `attribute_id` int(10) unsigned NOT NULL DEFAULT '0',
                `lang_id` int(10) unsigned NOT NULL DEFAULT '0',
                `name` varchar(255) NOT NULL DEFAULT '',
                PRIMARY KEY  (`attribute_id`,`lang_id`)
            ) TYPE=InnoDB
        ";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    if (!in_array(DBPREFIX."access_user_attribute_value", $arrTables)) {
        $query = "
            CREATE TABLE `".DBPREFIX."access_user_attribute_value` (
                `attribute_id` INT UNSIGNED NOT NULL DEFAULT '0',
                `user_id` INT UNSIGNED NOT NULL DEFAULT '0',
                `history_id` INT UNSIGNED NOT NULL DEFAULT '0',
                `value` TEXT NOT NULL,
                PRIMARY KEY ( `attribute_id` , `user_id` , `history_id` ),
                FULLTEXT KEY `value` (`value`)
            ) TYPE = MYISAM
        ";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    if (!in_array(DBPREFIX."access_user_core_attribute", $arrTables)) {
        $query = "
            CREATE TABLE `".DBPREFIX."access_user_core_attribute` (
                `id` VARCHAR( 25 ) NOT NULL ,
                `mandatory` ENUM( '0', '1' ) NOT NULL DEFAULT '0',
                `sort_type` ENUM( 'asc', 'desc', 'custom' ) NOT NULL DEFAULT 'asc',
                `order_id` INT UNSIGNED NOT NULL DEFAULT '0',
                `access_special` ENUM( '', 'menu_select_higher', 'menu_select_lower' ) NOT NULL DEFAULT '',
                `access_id` INT UNSIGNED NOT NULL DEFAULT '0',
                PRIMARY KEY ( `id` )
            ) ENGINE = InnoDB
        ";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }



    /************************
     *
     * ADD USER TITLE TABLE
     *
     ***********************/
    if (!in_array(DBPREFIX."access_user_title", $arrTables)) {
        $query = "
            CREATE TABLE `".DBPREFIX."access_user_title` (
                `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `title` varchar(255) NOT NULL DEFAULT '',
                `order_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
                PRIMARY KEY  (`id`),
                UNIQUE KEY `title` (`title`)
            ) ENGINE=InnoDB
        ";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    $query = 'SELECT 1 FROM `'.DBPREFIX.'access_user_title`';
    $objTitle = $objDatabase->SelectLimit($query, 1);
    if ($objTitle === false) {
        return _databaseError($query, $objDatabase->ErrorMsg());
    } elseif ($objTitle->RecordCount() == 0) {
        $arrDefaultTitle = array(
            'Sehr geehrte Frau',
            'Sehr geehrter Herr',
            'Dear Ms',
            'Dear Mr',
            'Madame',
            'Monsieur'
        );

        foreach ($arrDefaultTitle as $title) {
            $query = "INSERT INTO `".DBPREFIX."access_user_title` SET `title` = '".$title."'";
            if ($objDatabase->Execute($query) === false) {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
    }



    /******************************
     *
     * REMOVE OBSOLETE ACCESS IDS
     *
     *****************************/
    $query = 'DELETE FROM `'.DBPREFIX.'access_group_static_ids` WHERE `access_id` IN (28, 29, 30, 33, 34, 36)';
    if ($objDatabase->Execute($query) === false) {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }



    /*******************
     *
     * MIGRATE SESSION
     *
     ******************/
    $arrColumns = $objDatabase->MetaColumnNames(DBPREFIX.'sessions');
    if ($arrColumns === false) {
        setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'sessions'));
        return false;
    }

    if (!in_array('user_id', $arrColumns)) {
         $query = "
            ALTER TABLE `".DBPREFIX."sessions`
             DROP `username`,
              ADD `user_id` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `status`";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    /***************************************
     *
     * ADD CHECKBOX PROFILE ATTRIBUTE TYPE
     *
     **************************************/
    $query = "ALTER TABLE `".DBPREFIX."access_user_attribute` CHANGE `type` `type` enum('text','textarea','mail','uri','date','image','checkbox','menu','menu_option','group','frame','history') NOT NULL DEFAULT 'text'";
    if ($objDatabase->Execute($query) === false) {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }



    // Currently, this is only here to create the u2u_active field.. but instead of adding
    // 10 lines for each new field in the future, why not just extend this block
    try{
        DBG::trace();
        UpdateUtil::table(
            DBPREFIX . 'access_users',
            array(
                'id'               => array('type' => 'INT(5)',            'unsigned' => true, 'primary'     => true,    'auto_increment' => true),
                'is_admin'         => array('type' => 'TINYINT(1)', 'unsigned' => true, 'default'        => 0),
                'username'         => array('type' => 'VARCHAR(40)', 'notnull' => false, 'default_expr' => 'NULL'     ),
                'password'         => array('type' => 'VARCHAR(32)', 'notnull' => false, 'default_expr' => 'NULL'     ),
                'regdate'          => array('type' => 'INT(14)', 'unsigned' => true, 'default' => 0         ),
                'expiration'       => array('type' => 'INT(14)', 'unsigned' => true,                         'default'        => 0),
                'validity'         => array('type' => 'INT(10)', 'unsigned' => true,                         'default'        => 0),
                'last_auth'        => array('type' => 'INT(14)', 'unsigned' => true,                         'default'        => 0),
                'last_activity'    => array('type' => 'INT(14)', 'unsigned' => true,                         'default'        => 0),
                'email'            => array('type' => 'VARCHAR(255)', 'notnull' => false, 'default_expr' => 'NULL'    ),
                'email_access'     => array('type' => "ENUM('everyone','members_only','nobody')", 'default'        => 'nobody'),
                'frontend_lang_id' => array('type' => 'INT(2)', 'unsigned' => true,                          'default'        => 0),
                'backend_lang_id'  => array('type' => 'INT(2)', 'unsigned' => true,                          'default'        => 0),
                'active'           => array('type' => 'TINYINT(1)', 'default'        => 0),
                'profile_access'   => array('type' => "ENUM('everyone','members_only','nobody')", 'default'        => 'members_only'),
                'restore_key'      => array('type' => 'VARCHAR(32)'     ),
                'restore_key_time' => array('type' => 'INT(14)', 'unsigned' => true, 'default' => 0         ),
                'u2u_active'       => array('type' => "ENUM('0','1')",                            'default'        => '1'),
            ),
            array( # indexes
                'username' => array( 'fields'=>array('username'))
            )
        );
    }
    catch (UpdateException $e) {
        // we COULD do something else here..
        DBG::trace();
        return UpdateUtil::DefaultActionHandler($e);
    }

	/************************************************
	* BUGFIX:	Set write access to the upload dir  *
	************************************************/
	require_once ASCMS_FRAMEWORK_PATH.'/File.class.php';
	$objFile = new File();
	if (is_writeable(ASCMS_ACCESS_PROFILE_IMG_PATH) || $objFile->setChmod(ASCMS_ACCESS_PROFILE_IMG_PATH, ASCMS_ACCESS_PROFILE_IMG_WEB_PATH, '')) {
    	if ($mediaDir = @opendir(ASCMS_ACCESS_PROFILE_IMG_PATH)) {
    		while($file = readdir($mediaDir)) {
    			if ($file != '.' && $file != '..') {
    				if (!is_writeable(ASCMS_ACCESS_PROFILE_IMG_PATH.'/'.$file) && !$objFile->setChmod(ASCMS_ACCESS_PROFILE_IMG_PATH.'/', ASCMS_ACCESS_PROFILE_IMG_WEB_PATH.'/', $file)) {
    					setUpdateMsg(sprintf($_ARRAYLANG['TXT_SET_WRITE_PERMISSON_TO_FILE'], ASCMS_ACCESS_PROFILE_IMG_PATH.'/'.$file, $_CORELANG['TXT_UPDATE_TRY_AGAIN']), 'msg');
    					return false;
    				}
    			}
			}
    	} else {
    		setUpdateMsg(sprintf($_ARRAYLANG['TXT_SET_WRITE_PERMISSON_TO_DIR_AND_CONTENT'], ASCMS_ACCESS_PROFILE_IMG_PATH.'/', $_CORELANG['TXT_UPDATE_TRY_AGAIN']), 'msg');
    		return false;
		}
    } else {
    	setUpdateMsg(sprintf($_ARRAYLANG['TXT_SET_WRITE_PERMISSON_TO_DIR_AND_CONTENT'], ASCMS_ACCESS_PROFILE_IMG_PATH.'/', $_CORELANG['TXT_UPDATE_TRY_AGAIN']), 'msg');
    	return false;
    }

	require_once ASCMS_FRAMEWORK_PATH.'/File.class.php';
	$objFile = new File();
	if (is_writeable(ASCMS_ACCESS_PHOTO_IMG_PATH) || $objFile->setChmod(ASCMS_ACCESS_PHOTO_IMG_PATH, ASCMS_ACCESS_PHOTO_IMG_WEB_PATH, '')) {
    	if ($mediaDir = @opendir(ASCMS_ACCESS_PHOTO_IMG_PATH)) {
    		while($file = readdir($mediaDir)) {
    			if ($file != '.' && $file != '..') {
    				if (!is_writeable(ASCMS_ACCESS_PHOTO_IMG_PATH.'/'.$file) && !$objFile->setChmod(ASCMS_ACCESS_PHOTO_IMG_PATH.'/', ASCMS_ACCESS_PHOTO_IMG_WEB_PATH.'/', $file)) {
    					setUpdateMsg(sprintf($_ARRAYLANG['TXT_SET_WRITE_PERMISSON_TO_FILE'], ASCMS_ACCESS_PHOTO_IMG_PATH.'/'.$file, $_CORELANG['TXT_UPDATE_TRY_AGAIN']), 'msg');
    					return false;
    				}
    			}
			}
    	} else {
    		setUpdateMsg(sprintf($_ARRAYLANG['TXT_SET_WRITE_PERMISSON_TO_DIR_AND_CONTENT'], ASCMS_ACCESS_PHOTO_IMG_PATH.'/', $_CORELANG['TXT_UPDATE_TRY_AGAIN']), 'msg');
    		return false;
		}
    } else {
    	setUpdateMsg(sprintf($_ARRAYLANG['TXT_SET_WRITE_PERMISSON_TO_DIR_AND_CONTENT'], ASCMS_ACCESS_PHOTO_IMG_PATH.'/', $_CORELANG['TXT_UPDATE_TRY_AGAIN']), 'msg');
    	return false;
    }


    return true;
}
?>
