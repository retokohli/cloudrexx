<?php
function _guestbookUpdate()
{
    global $objDatabase;

    $arrGuestbookColumns = $objDatabase->MetaColumns(DBPREFIX.'module_guestbook');
    if ($arrGuestbookColumns === false) {
        setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_guestbook'));
        return false;
    }

    if (isset($arrGuestbookColumns['NICKNAME']) and !isset($arrGuestbookColumns['NAME'])) {
        $query = "ALTER TABLE ".DBPREFIX."module_guestbook
                  CHANGE `nickname` `name` varchar(255) NOT NULL default ''";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    if (!isset($arrGuestbookColumns['FORENAME'])) {
        $query = "ALTER TABLE ".DBPREFIX."module_guestbook
                  ADD `forename` varchar(255) NOT NULL default '' AFTER `name`";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    // this addidional structure update/check is required due that the full version's structure isn't as it should be
    try {
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX . 'module_guestbook',
            array(
                'id'        => array('type' => 'INT(6)', 'unsigned' => true, 'auto_increment' => true, 'primary' => true),
                'status'    => array('type' => 'TINYINT(1)', 'unsigned' => true, 'default' => 0),
                'name'      => array('type' => 'VARCHAR(255)'),
                'forename'  => array('type' => 'VARCHAR(255)'),
                'gender'    => array('type' => 'CHAR(1)', 'notnull' => true, 'default' => ''),
                'url'       => array('type' => 'TINYTEXT'),
                'email'     => array('type' => 'TINYTEXT'),
                'comment'   => array('type' => 'TEXT'),
                'ip'        => array('type' => 'VARCHAR(15)'),
                'location'  => array('type' => 'TINYTEXT'),
                'lang_id'   => array('type' => 'TINYINT(2)', 'default' => '1'),
                'datetime'  => array('type' => 'DATETIME', 'default' => '0000-00-00 00:00:00')            ),
            array(
                'comment'   => array('fields' => array('comment'), 'type' => 'FULLTEXT')
            )
        );
    }
    catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }


    /********************************
     * EXTENSION:   Timezone        *
     * ADDED:       Contrexx v3.0.0 *
     ********************************/
    try {
        \Cx\Lib\UpdateUtil::sql('ALTER TABLE `'.DBPREFIX.'module_guestbook` CHANGE `datetime` `datetime` TIMESTAMP NOT NULL DEFAULT "0000-00-00 00:00:00"');
    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }


    return true;
}



function _guestbookInstall()
{
    try {

        /**************************************************************************
         * EXTENSION:   Initial creation (for contrexx editions <> premium with   *
         *              version < 3.0.0 which have this module not yet installed) *
         *                                                                        *
         * ADDED:       Contrexx v3.0.1                                           *
         **************************************************************************/
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_guestbook',
            array(
                'id'             => array('type' => 'INT(6)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'status'         => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'id'),
                'name'           => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'status'),
                'forename'       => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'name'),
                'gender'         => array('type' => 'CHAR(1)', 'notnull' => true, 'default' => '', 'after' => 'forename'),
                'url'            => array('type' => 'tinytext', 'after' => 'gender'),
                'email'          => array('type' => 'tinytext', 'after' => 'url'),
                'comment'        => array('type' => 'text', 'after' => 'email'),
                'ip'             => array('type' => 'VARCHAR(15)', 'notnull' => true, 'default' => '', 'after' => 'comment'),
                'location'       => array('type' => 'tinytext', 'after' => 'ip'),
                'lang_id'        => array('type' => 'TINYINT(2)', 'notnull' => true, 'default' => '1', 'after' => 'location'),
                'datetime'       => array('type' => 'timestamp', 'notnull' => true, 'default' => '0000-00-00 00:00:00', 'after' => 'lang_id')
            ),
            array(
                'comment'        => array('fields' => array('comment'), 'type' => 'FULLTEXT')
            ),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_guestbook` (`id`, `status`, `name`, `forename`, `gender`, `url`, `email`, `comment`, `ip`, `location`, `lang_id`, `datetime`)
            VALUES (1, 1, 'COMVATION', 'Internet Solutions AG', 'M', 'http://www.contrexx.com/', 'nospam@example.com', 'This is a sample entry.\r\n\r\nsincerely yours\r\nCOMVATION AG', '127.0.0.1', 'Schweiz', 2, '2010-12-13 09:00:10')
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_guestbook_settings',
            array(
                'name'       => array('type' => 'VARCHAR(50)', 'notnull' => true, 'default' => ''),
                'value'      => array('type' => 'VARCHAR(250)', 'notnull' => true, 'default' => '', 'after' => 'name')
            ),
            array(
                'name'       => array('fields' => array('name'))
            ),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_guestbook_settings` (`name`, `value`)
            VALUES  ('guestbook_send_notification_email', '0'),
                    ('guestbook_activate_submitted_entries', '0'),
                    ('guestbook_replace_at', '1'),
                    ('guestbook_only_lang_entries', '0')
            ON DUPLICATE KEY UPDATE `name` = `name`
        ");

    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }
}
