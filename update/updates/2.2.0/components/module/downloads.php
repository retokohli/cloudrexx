<?php
function _downloadsUpdate()
{
    try{
        /* this check is kind of ugly, because it assumes that
         * as long as the table contrexx_module_downloads_mail
         * doesn't exist, the update hasn't been executed yet. */
        if (!UpdateUtil::table_exist(DBPREFIX.'module_downloads_mail')) {
            UpdateUtil::sql("UPDATE `".DBPREFIX."module_downloads_download_locale` SET `description` = REPLACE(`description`, '\r\n','<br />')");
        }

        UpdateUtil::table(
            DBPREFIX.'module_downloads_mail',
            array(
                'type'           => array('type' => 'ENUM(\'new_entry\')', 'notnull' => true),
                'lang_id'        => array('type' => 'TINYINT(2)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'sender_mail'    => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'sender_name'    => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'subject'        => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'format'         => array('type' => 'ENUM(\'text\',\'html\',\'multipart\')', 'notnull' => true, 'default' => 'text'),
                'body_text'      => array('type' => 'TEXT', 'notnull' => true),
                'body_html'      => array('type' => 'TEXT', 'notnull' => true)
            ),
            array(
                'mail'           => array('fields' => array('type','lang_id'), 'type' => 'UNIQUE')
            ),
            'InnoDB'
        );

        // insert default language mail
        if (!UpdateUtil::sql("SELECT 1 FROM `".DBPREFIX."module_downloads_mail` WHERE `type` = 'new_entry'")->RecordCount()) {
            UpdateUtil::sql("INSERT INTO `".DBPREFIX."module_downloads_mail`
                SET `type`          = 'new_entry',
                    `sender_mail`   = 'noreply@example.com',
                    `sender_name`   = 'Contrexx Demo',
                    `subject`       = 'Neuer Eintrag im Download Verzeichnis',
                    `body_text`     = 'Hallo [[RECIPIENT_FIRSTNAME]],\r\n\r\n[[PUBLISHER_FIRSTNAME]] [[PUBLISHER_LASTNAME]] hat bei der Rubrik [[CATEGORY]] den Download [[NAME]] hinzugefügt.\r\n\r\n[[LINK]]\r\n\r\n--\r\n[[SENDER]]\r\n',
                    `body_html`     = ''
            )");
        }

        UpdateUtil::table(
            DBPREFIX.'module_downloads_notification_rel_category_user_group',
            array(
                'category_id'    => array('type' => 'INT(11)', 'unsigned' => true, 'notnull' => true, 'primary' => true),
                'user_group_id'  => array('type' => 'INT(11)', 'unsigned' => true, 'notnull' => true, 'primary' => true)
            ),
            array(),
            'InnoDB'
        );
    }
    catch (UpdateException $e) {
        // we COULD do something else here..
        return UpdateUtil::DefaultActionHandler($e);
    }

    return true;
}
?>
