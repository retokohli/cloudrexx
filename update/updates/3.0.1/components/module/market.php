<?php
function _marketUpdate()
{
    global $objDatabase, $_ARRAYLANG;

    $query = "SELECT id FROM ".DBPREFIX."module_market_settings WHERE name='codeMode'";
    $objCheck = $objDatabase->SelectLimit($query, 1);
    if ($objCheck !== false) {
        if ($objCheck->RecordCount() == 0) {
            $query =   "INSERT INTO `".DBPREFIX."module_market_settings` ( `id` , `name` , `value` , `description` , `type` )
                        VALUES ( NULL , 'codeMode', '1', 'TXT_MARKET_SET_CODE_MODE', '2')";
            if ($objDatabase->Execute($query) === false) {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
    } else {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }

    $arrColumns = $objDatabase->MetaColumns(DBPREFIX.'module_market_mail');
    if ($arrColumns === false) {
        setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_market_mail'));
        return false;
    }

    if (!isset($arrColumns['MAILTO'])) {
        $query = "ALTER TABLE `".DBPREFIX."module_market_mail` ADD `mailto` VARCHAR( 10 ) NOT NULL AFTER `content`" ;
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }


    /*****************************************************************
    * EXTENSION:    New attributes 'color' and 'sort_id' for entries *
    * ADDED:        Contrexx v2.1.0                                  *
    *****************************************************************/
    $arrColumns = $objDatabase->MetaColumns(DBPREFIX.'module_market');
    if ($arrColumns === false) {
        setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_market'));
        return false;
    }

    if (!isset($arrColumns['SORT_ID'])) {
        $query = "ALTER TABLE `".DBPREFIX."module_market` ADD `sort_id` INT( 4 ) NOT NULL DEFAULT '0' AFTER `paypal`" ;
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    if (!isset($arrColumns['COLOR'])) {
        $query = "ALTER TABLE `".DBPREFIX."module_market` ADD `color` VARCHAR(50) NOT NULL DEFAULT '' AFTER `description`" ;
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }


    try {
        // delete obsolete table  contrexx_module_market_access
        \Cx\Lib\UpdateUtil::drop_table(DBPREFIX.'module_market_access');

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_market_spez_fields',
            array(
                'id'         => array('type' => 'INT(5)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'name'       => array('type' => 'VARCHAR(100)'),
                'value'      => array('type' => 'VARCHAR(100)'),
                'type'       => array('type' => 'INT(1)', 'notnull' => true, 'default' => '1'),
                'lang_id'    => array('type' => 'INT(2)', 'notnull' => true, 'default' => '0'),
                'active'     => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0')
            )
        );
    } catch (\Cx\Lib\UpdateException $e) {
        DBG::trace();
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }

    return true;
}



function _marketInstall()
{
    try {

        /**************************************************************************
         * EXTENSION:   Initial creation (for contrexx editions <> premium with   *
         *              version < 3.0.0 which have this module not yet installed) *
         *                                                                        *
         * ADDED:       Contrexx v3.0.1                                           *
         **************************************************************************/
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_market',
            array(
                'id'                 => array('type' => 'INT(9)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'name'               => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => '', 'after' => 'id'),
                'email'              => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => '', 'after' => 'name'),
                'type'               => array('type' => 'SET(\'search\',\'offer\')', 'notnull' => true, 'default' => '', 'after' => 'email'),
                'title'              => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'type'),
                'description'        => array('type' => 'mediumtext', 'after' => 'title'),
                'color'              => array('type' => 'VARCHAR(50)', 'notnull' => true, 'default' => '', 'after' => 'description'),
                'premium'            => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'color'),
                'picture'            => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'premium'),
                'catid'              => array('type' => 'INT(4)', 'notnull' => true, 'default' => '0', 'after' => 'picture'),
                'price'              => array('type' => 'VARCHAR(10)', 'notnull' => true, 'default' => '', 'after' => 'catid'),
                'regdate'            => array('type' => 'VARCHAR(20)', 'notnull' => true, 'default' => '', 'after' => 'price'),
                'enddate'            => array('type' => 'VARCHAR(20)', 'notnull' => true, 'default' => '', 'after' => 'regdate'),
                'userid'             => array('type' => 'INT(4)', 'notnull' => true, 'default' => '0', 'after' => 'enddate'),
                'userdetails'        => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'userid'),
                'status'             => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'userdetails'),
                'regkey'             => array('type' => 'VARCHAR(50)', 'notnull' => true, 'default' => '', 'after' => 'status'),
                'paypal'             => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'regkey'),
                'sort_id'            => array('type' => 'INT(4)', 'notnull' => true, 'default' => '0', 'after' => 'paypal'),
                'spez_field_1'       => array('type' => 'VARCHAR(255)', 'after' => 'sort_id'),
                'spez_field_2'       => array('type' => 'VARCHAR(255)', 'after' => 'spez_field_1'),
                'spez_field_3'       => array('type' => 'VARCHAR(255)', 'after' => 'spez_field_2'),
                'spez_field_4'       => array('type' => 'VARCHAR(255)', 'after' => 'spez_field_3'),
                'spez_field_5'       => array('type' => 'VARCHAR(255)', 'after' => 'spez_field_4')
            ),
            array(
                'description'        => array('fields' => array('description'), 'type' => 'FULLTEXT'),
                'title'              => array('fields' => array('description','title'), 'type' => 'FULLTEXT')
            ),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_market` (`id`, `name`, `email`, `type`, `title`, `description`, `color`, `premium`, `picture`, `catid`, `price`, `regdate`, `enddate`, `userid`, `userdetails`, `status`, `regkey`, `paypal`, `sort_id`, `spez_field_1`, `spez_field_2`, `spez_field_3`, `spez_field_4`, `spez_field_5`)
            VALUES (8, 'CMS System Benutzer', 'support@comvation.com', 'offer', 'Contrexx® CMS Software', 'Contrexx® ist ein modernes, einzigartiges und modulares Web Content Management System (WCMS) für die komplette Verwaltung einer Webseite. Zudem kann Contrexx® auch für andere Informationsangebote wie Intranet, Extranet, eShop, Portal und weiteres eingesetzt werden. Das Contrexx® basiert auf neuster PHP und MySQL Technologie und besticht in der einfachen Bedienung', '', 0, '50d68ff84e7c324b6e1b368582f2fcac.jpg', 11, '990', '1292236792', '1292799600', 1, 0, 1, '', 0, 0, '', '', '', '', '')
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_market_categories',
            array(
                'id'                 => array('type' => 'INT(6)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'name'               => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => '', 'after' => 'id'),
                'description'        => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'name'),
                'displayorder'       => array('type' => 'INT(4)', 'notnull' => true, 'default' => '0', 'after' => 'description'),
                'status'             => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'displayorder')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_market_categories` (`id`, `name`, `description`, `displayorder`, `status`)
            VALUES  (4, 'Antiquitäten & Kunst', 'Bilder, Antikes, Porzellan, Keramik, Skulpturen, Stiche, Kunstdrucke, Malerei, Grafiken, antike Einrichtung. Kaufe antike und moderne Waren und Kunst.', 0, 1),
                    (6, 'Audio, TV & Video', '', 0, 1),
                    (7, 'Auto & Motorrad', '', 0, 1),
                    (8, 'Briefmarken', '', 0, 1),
                    (9, 'Bücher & Comics', '', 0, 1),
                    (10, 'Büro & Gewerbe', '', 0, 1),
                    (11, 'Computer & Netzwerk', '', 0, 1),
                    (12, 'Filme & DVD', '', 0, 1),
                    (13, 'Foto & Optik', '', 0, 1),
                    (14, 'Games & Spielkonsolen', '', 0, 1),
                    (15, 'Handwerk & Garten', '', 0, 1),
                    (16, 'Handy, Festnetz, Funk', '', 0, 1),
                    (17, 'Haushalt & Wohnen', '', 0, 1),
                    (18, 'Kind & Baby', '', 0, 1),
                    (19, 'Kleidung & Accessoires', '', 0, 1),
                    (20, 'Kosmetik & Pflege', '', 0, 1),
                    (21, 'Modellbau & Hobby', '', 0, 1),
                    (22, 'Münzen', '', 0, 1),
                    (23, 'Musik & Musikinstrumente', '', 0, 1),
                    (24, 'Sammeln & Seltenes', '', 0, 1),
                    (25, 'Spielzeug & Basteln', '', 0, 1),
                    (26, 'Sport', '', 0, 1),
                    (27, 'Tickets & Gutscheine', '', 0, 1),
                    (28, 'Tierzubehör', '', 0, 1),
                    (29, 'Uhren & Schmuck', '', 0, 1),
                    (30, 'Wein & Genuss', '', 0, 1)
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_market_mail',
            array(
                'id'         => array('type' => 'INT(4)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'title'      => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'id'),
                'content'    => array('type' => 'longtext', 'after' => 'title'),
                'mailto'     => array('type' => 'VARCHAR(10)', 'after' => 'content'),
                'mailcc'     => array('type' => 'mediumtext', 'after' => 'mailto'),
                'active'     => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'mailcc')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_market_mail` (`id`, `title`, `content`, `mailto`, `mailcc`, `active`)
            VALUES  (1, 'Ihr Contrexx.com-Inserat mit dem Titel [[TITLE]] wurde freigeschaltet', 'Lieber Inserent\r\n\r\nBesten Dank für Ihre Geduld. Um eine hohe Qualität auf Contrexx.com garantieren zu können, haben wir Ihr Inserat geprüft. \r\n\r\nIhr Inserat mit dem Titel «[[TITLE]]» und der ID \"[[ID]]\" wurde von unseren Mitarbeiterinnen und Mitarbeitern geprüft und freigeschaltet.\r\n\r\nIhr Inserat ist ab sofort unter [[URL]] einsehbar.\r\n\r\nSie können Ihr Inserat jederzeit unter [[LINK]] gratis ändern oder löschen.\r\n\r\nHoffentlich bis bald wieder auf Contrexx.com und mit freundlichen Grüssen\r\n\r\nIhr Contrexx.com Team\r\n\r\nAutomatisch generierte Nachricht\r\n[[DATE]]\r\n\r\n\r\nhttp://www.contrexx.com/\r\nContrexx.com - Der Schweizer Marktplatz', '', '', 1),
                    (2, 'Neues Inserat auf [[URL]] - ID: [[ID]]', 'Hallo \r\n\r\nAuf [[URL]] wurde ein neues Inserat eingetragen.\r\n\r\nID:          [[ID]]\r\nTitel:       [[TITLE]]\r\nCode:        [[CODE]]\r\nUsername:    [[USERNAME]]\r\nName:        [[NAME]]\r\nE-Mail:      [[EMAIL]]\r\n\r\nAutomatisch generierte Nachricht\r\n[[DATE]]\r\n', '', '', 1)
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_market_paypal',
            array(
                'id'                 => array('type' => 'INT(4)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'active'             => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'id'),
                'profile'            => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'active'),
                'price'              => array('type' => 'VARCHAR(10)', 'notnull' => true, 'default' => '', 'after' => 'profile'),
                'price_premium'      => array('type' => 'VARCHAR(10)', 'notnull' => true, 'default' => '', 'after' => 'price')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_market_paypal` (`id`, `active`, `profile`, `price`, `price_premium`)
            VALUES (1, 0, 'noreply@example.com', '5.00', '2.00')
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_market_settings',
            array(
                'id'             => array('type' => 'INT(6)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'name'           => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => '', 'after' => 'id'),
                'value'          => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'name'),
                'description'    => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'value'),
                'type'           => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'description')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_market_settings` (`id`, `name`, `value`, `description`, `type`)
            VALUES  (1, 'maxday', '14', 'TXT_MARKET_SET_MAXDAYS', 1),
                    (2, 'description', '0', 'TXT_MARKET_SET_DESCRIPTION', 2),
                    (3, 'paging', '10', 'TXT_MARKET_SET_PAGING', 1),
                    (4, 'currency', 'CHF', 'TXT_MARKET_SET_CURRENCY', 1),
                    (5, 'addEntry_only_community', '1', 'TXT_MARKET_SET_ADD_ENTRY_ONLY_COMMUNITY', 2),
                    (6, 'addEntry', '1', 'TXT_MARKET_SET_ADD_ENTRY', 2),
                    (7, 'editEntry', '1', 'TXT_MARKET_SET_EDIT_ENTRY', 2),
                    (8, 'indexview', '0', 'TXT_MARKET_SET_INDEXVIEW', 2),
                    (9, 'maxdayStatus', '0', 'TXT_MARKET_SET_MAXDAYS_ON', 2),
                    (10, 'searchPrice', '100,200,500,1000,2000,5000', 'TXT_MARKET_SET_EXP_SEARCH_PRICE', 3),
                    (11, 'codeMode', '1', 'TXT_MARKET_SET_CODE_MODE', 2)
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_market_spez_fields',
            array(
                'id'         => array('type' => 'INT(5)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'name'       => array('type' => 'VARCHAR(100)', 'after' => 'id'),
                'value'      => array('type' => 'VARCHAR(100)', 'after' => 'name'),
                'type'       => array('type' => 'INT(1)', 'notnull' => true, 'default' => '1', 'after' => 'value'),
                'lang_id'    => array('type' => 'INT(2)', 'notnull' => true, 'default' => '0', 'after' => 'type'),
                'active'     => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'lang_id')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_market_spez_fields` (`id`, `name`, `value`, `type`, `lang_id`, `active`)
            VALUES  (1, 'spez_field_1', '', 1, 1, 0),
                    (2, 'spez_field_2', '', 1, 1, 0),
                    (3, 'spez_field_3', '', 1, 1, 0),
                    (4, 'spez_field_4', '', 1, 1, 0),
                    (5, 'spez_field_5', '', 1, 1, 0)
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }
}
