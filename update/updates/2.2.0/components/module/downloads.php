<?php
function _downloadsUpdate()
{
    global $objDatabase;

    try{
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

        UpdateUtil::table(
            DBPREFIX.'module_downloads_notification_rel_category_user_group',
            array(
                'category_id'    => array('type' => 'INT(11)', 'unsigned' => true, 'notnull' => true, 'primary' => true),
                'user_group_id'  => array('type' => 'INT(11)', 'unsigned' => true, 'notnull' => true, 'primary' => true)
            ),
            array(),
            'InnoDB'
        );

        $query = "INSERT INTO `".DBPREFIX."module_downloads_mail` VALUES ('new_entry',0,'noreply@example.com','Contrexx Demo','Neuer Eintrag im Download Verzeichnis','text','Hallo [[RECIPIENT_FIRSTNAME]],\r\n\r\n[[PUBLISHER_FIRSTNAME]] [[PUBLISHER_LASTNAME]] hat bei der Rubrik [[CATEGORY]] den Download [[NAME]] hinzugefügt.\r\n\r\n[[LINK]]\r\n\r\n--\r\n[[SENDER]]\r\n','')";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }
    catch (UpdateException $e) {
        // we COULD do something else here..
        return UpdateUtil::DefaultActionHandler($e);
    }

    return true;
}
?>
