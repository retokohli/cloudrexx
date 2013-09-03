<?php

function _calendarUpdate() {
    global $objDatabase;

    try {
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX . 'module_calendar', array(
                'id' => array('type' => 'INT(11)', 'notnull' => true, 'primary' => true, 'auto_increment' => true),
                'active' => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '1', 'after' => 'id'),
                'catid' => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'after' => 'active'),
                'startdate' => array('type' => 'INT(14)', 'notnull' => false, 'after' => 'catid'),
                'enddate' => array('type' => 'INT(14)', 'notnull' => false, 'after' => 'startdate'),
                'priority' => array('type' => 'INT(1)', 'notnull' => true, 'default' => '3', 'after' => 'enddate'),
                'access' => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'priority'),
                'name' => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => '', 'after' => 'access'),
                'comment' => array('type' => 'text', 'after' => 'name'),
                'placeName' => array('type' => 'VARCHAR(255)', 'after' => 'comment'),
                'link' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => 'http://', 'after' => 'placeName'),
                'pic' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'link'),
                'attachment' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'pic'),
                'placeStreet' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'attachment'),
                'placeZip' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'placeStreet'),
                'placeCity' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'placeZip'),
                'placeLink' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'placeCity'),
                'placeMap' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'placeLink'),
                'organizerName' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'placeMap'),
                'organizerStreet' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'organizerName'),
                'organizerZip' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'organizerStreet'),
                'organizerPlace' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'organizerZip'),
                'organizerMail' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'organizerPlace'),
                'organizerLink' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'organizerMail'),
                'key' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'organizerLink'),
                'num' => array('type' => 'INT(5)', 'notnull' => true, 'default' => '0', 'after' => 'key'),
                'mailTitle' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'num'),
                'mailContent' => array('type' => 'text', 'after' => 'mailTitle'),
                'registration' => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'mailContent'),
                'groups' => array('type' => 'text', 'after' => 'registration'),
                'all_groups' => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'groups'),
                'public' => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'all_groups'),
                'notification' => array('type' => 'INT(1)', 'after' => 'public'),
                'notification_address' => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'notification'),
                'series_status' => array('type' => 'TINYINT(4)', 'after' => 'notification_address'),
                'series_type' => array('type' => 'INT(11)', 'after' => 'series_status'),
                'series_pattern_count' => array('type' => 'INT(11)', 'after' => 'series_type'),
                'series_pattern_weekday' => array('type' => 'VARCHAR(7)', 'after' => 'series_pattern_count'),
                'series_pattern_day' => array('type' => 'INT(11)', 'after' => 'series_pattern_weekday'),
                'series_pattern_week' => array('type' => 'INT(11)', 'after' => 'series_pattern_day'),
                'series_pattern_month' => array('type' => 'INT(11)', 'after' => 'series_pattern_week'),
                'series_pattern_type' => array('type' => 'INT(11)', 'after' => 'series_pattern_month'),
                'series_pattern_dourance_type' => array('type' => 'INT(11)', 'after' => 'series_pattern_type'),
                'series_pattern_end' => array('type' => 'INT(11)', 'after' => 'series_pattern_dourance_type'),
                'series_pattern_begin' => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'after' => 'series_pattern_end'),
                'series_pattern_exceptions' => array('type' => 'longtext', 'after' => 'series_pattern_begin')
            ),
            array(
                'name' => array('fields' => array('name', 'comment', 'placeName'), 'type' => 'FULLTEXT')
            )
        );
    } catch (\Cx\Lib\UpdateException $e) {
        // we COULD do something else here..
        DBG::trace();
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }


    //2.1.1


    $query = "SELECT status FROM " . DBPREFIX . "modules WHERE id='21'";
    $objResultCheck = $objDatabase->SelectLimit($query, 1);

    if ($objResultCheck !== false) {
        if ($objResultCheck->fields['status'] == 'y') {
            $calendarStatus = true;
        } else {
            $calendarStatus = false;
        }
    } else {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }

    if ($calendarStatus) {
        $arrContentSites = array();

        $arrContentSites[0]['module'] = 'calendar';
        $arrContentSites[0]['cmd'] = '';
        $arrContentSites[1]['module'] = 'calendar';
        $arrContentSites[1]['cmd'] = 'eventlist';
        $arrContentSites[2]['module'] = 'calendar';
        $arrContentSites[2]['cmd'] = 'boxes';


        //insert new link placeholder in content, if module is active
        foreach ($arrContentSites as $key => $siteArray) {
            
            $module = $siteArray['module'];
            $cmd = $siteArray['cmd'];
            
            try {
                \Cx\Lib\UpdateUtil::migrateContentPage(
                    $module,
                    $cmd,
                    '<a href="index.php?section=calendar&amp;cmd=event&amp;id={CALENDAR_ID}">{CALENDAR_TITLE}</a>',
                    '{CALENDAR_DETAIL_LINK}',
                    '3.0.0'
                );
            } catch (\Cx\Lib\UpdateException $e) {
                return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
            }
        }
        
        
        try {
            \Cx\Lib\UpdateUtil::migrateContentPage(
                'calendar',
                'sign',
                '<input type="hidden" name="id" value="{CALENDAR_NOTE_ID}" />',
                '<input type="hidden" name="id" value="{CALENDAR_NOTE_ID}" /><input type="hidden" name="date" value="{CALENDAR_NOTE_DATE}" />',
                '3.0.0'
            );
            \Cx\Lib\UpdateUtil::migrateContentPage(
                'calendar',
                'sign',
                '<a href="index.php?section=calendar&amp;id={CALENDAR_NOTE_ID}">{TXT_CALENDAR_BACK}</a>',
                '{CALENDAR_LINK_BACK}',
                '3.0.0'
            );
        } catch (\Cx\Lib\UpdateException $e) {
            return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
        }
    }

    try {
        // delete obsolete table  contrexx_module_calendar_access
        \Cx\Lib\UpdateUtil::drop_table(DBPREFIX . 'module_calendar_access');

        \Cx\Lib\UpdateUtil::table(
                DBPREFIX . 'module_calendar_form_data', array(
            'reg_id' => array('type' => 'INT(10)', 'notnull' => true, 'default' => '0'),
            'field_id' => array('type' => 'INT(10)', 'notnull' => true, 'default' => '0'),
            'data' => array('type' => 'TEXT', 'notnull' => true)
                )
        );

        \Cx\Lib\UpdateUtil::table(
                DBPREFIX . 'module_calendar_form_fields', array(
            'id' => array('type' => 'INT(7)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
            'note_id' => array('type' => 'INT(10)', 'notnull' => true, 'default' => '0'),
            'name' => array('type' => 'TEXT'),
            'type' => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0'),
            'required' => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0'),
            'order' => array('type' => 'INT(3)', 'notnull' => true, 'default' => '0'),
            'key' => array('type' => 'INT(7)', 'notnull' => true, 'default' => '0')
                )
        );

        \Cx\Lib\UpdateUtil::table(
                DBPREFIX . 'module_calendar_registrations', array(
            'id' => array('type' => 'INT(7)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
            'note_id' => array('type' => 'INT(7)', 'notnull' => true, 'default' => '0'),
            'note_date' => array('type' => 'INT(11)', 'notnull' => true, 'after' => 'note_id'),
            'time' => array('type' => 'INT(14)', 'notnull' => true, 'default' => '0'),
            'host' => array('type' => 'VARCHAR(255)'),
            'ip_address' => array('type' => 'VARCHAR(15)'),
            'type' => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0')
                )
        );
    } catch (\Cx\Lib\UpdateException $e) {
        // we COULD do something else here..
        DBG::trace();
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }

    return true;
}



function _calendarInstall()
{
    try {

        /**************************************************************************
         * EXTENSION:   Initial creation (for contrexx editions <> premium with   *
         *              version < 3.0.0 which have this module not yet installed) *
         *                                                                        *
         * ADDED:       Contrexx v3.0.1                                           *
         **************************************************************************/
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_calendar',
            array(
                'id'                                 => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'active'                             => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '1', 'after' => 'id'),
                'catid'                              => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'after' => 'active'),
                'startdate'                          => array('type' => 'INT(14)', 'notnull' => false, 'after' => 'catid'),
                'enddate'                            => array('type' => 'INT(14)', 'notnull' => false, 'after' => 'startdate'),
                'priority'                           => array('type' => 'INT(1)', 'notnull' => true, 'default' => '3', 'after' => 'enddate'),
                'access'                             => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'priority'),
                'name'                               => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => '', 'after' => 'access'),
                'comment'                            => array('type' => 'text', 'after' => 'name'),
                'placeName'                          => array('type' => 'VARCHAR(255)', 'after' => 'comment'),
                'link'                               => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => 'http://', 'after' => 'placeName'),
                'pic'                                => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'link'),
                'attachment'                         => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'pic'),
                'placeStreet'                        => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'attachment'),
                'placeZip'                           => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'placeStreet'),
                'placeCity'                          => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'placeZip'),
                'placeLink'                          => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'placeCity'),
                'placeMap'                           => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'placeLink'),
                'organizerName'                      => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'placeMap'),
                'organizerStreet'                    => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'organizerName'),
                'organizerZip'                       => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'organizerStreet'),
                'organizerPlace'                     => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'organizerZip'),
                'organizerMail'                      => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'organizerPlace'),
                'organizerLink'                      => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'organizerMail'),
                'key'                                => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'organizerLink'),
                'num'                                => array('type' => 'INT(5)', 'notnull' => true, 'default' => '0', 'after' => 'key'),
                'mailTitle'                          => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'num'),
                'mailContent'                        => array('type' => 'text', 'after' => 'mailTitle'),
                'registration'                       => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'mailContent'),
                'groups'                             => array('type' => 'text', 'after' => 'registration'),
                'all_groups'                         => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'groups'),
                'public'                             => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'all_groups'),
                'notification'                       => array('type' => 'INT(1)', 'after' => 'public'),
                'notification_address'               => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'notification'),
                'series_status'                      => array('type' => 'TINYINT(4)', 'after' => 'notification_address'),
                'series_type'                        => array('type' => 'INT(11)', 'after' => 'series_status'),
                'series_pattern_count'               => array('type' => 'INT(11)', 'after' => 'series_type'),
                'series_pattern_weekday'             => array('type' => 'VARCHAR(7)', 'after' => 'series_pattern_count'),
                'series_pattern_day'                 => array('type' => 'INT(11)', 'after' => 'series_pattern_weekday'),
                'series_pattern_week'                => array('type' => 'INT(11)', 'after' => 'series_pattern_day'),
                'series_pattern_month'               => array('type' => 'INT(11)', 'after' => 'series_pattern_week'),
                'series_pattern_type'                => array('type' => 'INT(11)', 'after' => 'series_pattern_month'),
                'series_pattern_dourance_type'       => array('type' => 'INT(11)', 'after' => 'series_pattern_type'),
                'series_pattern_end'                 => array('type' => 'INT(11)', 'after' => 'series_pattern_dourance_type'),
                'series_pattern_begin'               => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'after' => 'series_pattern_end'),
                'series_pattern_exceptions'          => array('type' => 'longtext', 'after' => 'series_pattern_begin')
            ),
            array(
                'name'                               => array('fields' => array('name','comment','placeName'), 'type' => 'FULLTEXT')
            ),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_calendar` (`id`, `active`, `catid`, `startdate`, `enddate`, `priority`, `access`, `name`, `comment`, `placeName`, `link`, `pic`, `attachment`, `placeStreet`, `placeZip`, `placeCity`, `placeLink`, `placeMap`, `organizerName`, `organizerStreet`, `organizerZip`, `organizerPlace`, `organizerMail`, `organizerLink`, `key`, `num`, `mailTitle`, `mailContent`, `registration`, `groups`, `all_groups`, `public`, `notification`, `notification_address`, `series_status`, `series_type`, `series_pattern_count`, `series_pattern_weekday`, `series_pattern_day`, `series_pattern_week`, `series_pattern_month`, `series_pattern_type`, `series_pattern_dourance_type`, `series_pattern_end`, `series_pattern_begin`, `series_pattern_exceptions`)
            VALUES  (5, 1, 1, 1348848000, 1380396600, 3, 0, 'Einweihungs-Apéro der neuen Webseite', 'Stolz m&ouml;chten wir alle Kunden zum Einweihungs-Ap&eacute;ro unserer neuen Webseite einladen. Der Internetauftritt ist nun dank dem Contrexx Web Content Management System bestens ausger&uuml;stet und stellt die Grundlage unseres Onlineerfolgs dar.<br />\r\n<br />\r\nLasst uns gem&uuml;tlich beisammen sein, mit Champagner anstossen und leckere Snacks geniessen.<br />\r\n<br />\r\nWir freuen uns auf Sie!<br />\r\n<br />\r\nIhr MaxMuster Team', 'MaxMuster AG', 'http://', 'images/calendar/event-example-image.jpg', '', 'Musterstrasse 12', '3600', 'Musterhausen', '', '', 'MaxMuster AG', 'Musterstrasse 12', '3600', 'Musterhausen', '', '', 'db6a777e12', 0, 'Neuer Termin', 'Hallo [[FIRSTNAME]] [[LASTNAME]] \r\n\r\nAuf [[URL]] wurde ein neuer Termin für ihre Gruppe aufgeschaltet. Mit folgendem Link können Sie sich für diesen Anlass an- oder abmelden. \r\n\r\n[[REG_LINK]]\r\n\r\nFreundliche Grüsse \r\n[[URL]] - Team \r\n\r\n\r\n*Diese Nachricht wurde automatisch generiert*\r\n[[DATE]]\r\n', 1, '', 0, 1, 1, 'info@example.com', 0, 1, 0, '0', 0, 0, 0, 0, 0, 0, 0, ''),
                    (6, 1, 2, 1348848000, 1380396600, 3, 0, 'Inauguration aperitif of the new website', 'Stolz m&ouml;chten wir alle Kunden zum Einweihungs-Ap&eacute;ro unserer neuen Webseite einladen. Der Internetauftritt ist nun dank dem Contrexx Web Content Management System bestens ausger&uuml;stet und stellt die Grundlage unseres Onlineerfolgs dar.<br />\r\n<br />\r\nLasst uns gem&uuml;tlich beisammen sein, mit Champagner anstossen und leckere Snacks geniessen.<br />\r\n<br />\r\nWir freuen uns auf Sie!<br />\r\n<br />\r\nIhr MaxMuster Team', 'MaxMuster AG', 'http://', 'images/calendar/event-example-image.jpg', '', 'Musterstrasse 12', '3600', 'Musterhausen', '', '', 'MaxMuster AG', 'Musterstrasse 12', '3600', 'Musterhausen', '', '', 'e76a1ad122', 0, 'Neuer Termin', 'Hallo [[FIRSTNAME]] [[LASTNAME]] \r\n\r\nAuf [[URL]] wurde ein neuer Termin für ihre Gruppe aufgeschaltet. Mit folgendem Link können Sie sich für diesen Anlass an- oder abmelden. \r\n\r\n[[REG_LINK]]\r\n\r\nFreundliche Grüsse \r\n[[URL]] - Team \r\n\r\n\r\n*Diese Nachricht wurde automatisch generiert*\r\n[[DATE]]\r\n', 0, '', 0, 0, 1, 'info@example.com', 0, 1, 0, '0', 0, 0, 0, 0, 0, 0, 0, '')
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_calendar_categories',
            array(
                'id'         => array('type' => 'INT(5)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'name'       => array('type' => 'VARCHAR(150)', 'notnull' => true, 'default' => '', 'after' => 'id'),
                'status'     => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'name'),
                'lang'       => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'status'),
                'pos'        => array('type' => 'INT(5)', 'notnull' => true, 'default' => '0', 'after' => 'lang')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_calendar_categories` (`id`, `name`, `status`, `lang`, `pos`)
            VALUES  (1, 'Firmenanlässe', 1, 1, 1),
                    (2, 'Company occasion', 1, 2, 2)
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_calendar_form_data',
            array(
                'reg_id'         => array('type' => 'INT(10)', 'notnull' => true, 'default' => '0'),
                'field_id'       => array('type' => 'INT(10)', 'notnull' => true, 'default' => '0', 'after' => 'reg_id'),
                'data'           => array('type' => 'text', 'notnull' => true, 'after' => 'field_id')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_calendar_form_fields',
            array(
                'id'             => array('type' => 'INT(7)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'note_id'        => array('type' => 'INT(10)', 'notnull' => true, 'default' => '0', 'after' => 'id'),
                'name'           => array('type' => 'text', 'after' => 'note_id'),
                'type'           => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'name'),
                'required'       => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'type'),
                'order'          => array('type' => 'INT(3)', 'notnull' => true, 'default' => '0', 'after' => 'required'),
                'key'            => array('type' => 'INT(7)', 'notnull' => true, 'default' => '0', 'after' => 'order')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_calendar_form_fields` (`id`, `note_id`, `name`, `type`, `required`, `order`, `key`)
            VALUES  (4, 5, 'Telefon', 1, 0, 0, 8),
                    (3, 5, 'E-Mail', 1, 1, 0, 6),
                    (2, 5, 'Nachname', 1, 1, 0, 2),
                    (1, 5, 'Vorname', 1, 1, 0, 1),
                    (5, 5, 'Firma', 1, 0, 0, 12)
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_calendar_registrations',
            array(
                'id'             => array('type' => 'INT(7)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'note_id'        => array('type' => 'INT(7)', 'notnull' => true, 'default' => '0', 'after' => 'id'),
                'note_date'      => array('type' => 'INT(11)', 'after' => 'note_id'),
                'time'           => array('type' => 'INT(14)', 'notnull' => true, 'default' => '0', 'after' => 'note_date'),
                'host'           => array('type' => 'VARCHAR(255)', 'after' => 'time'),
                'ip_address'     => array('type' => 'VARCHAR(15)', 'after' => 'host'),
                'type'           => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'ip_address')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_calendar_settings',
            array(
                'setid'          => array('type' => 'INT(7)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'setname'        => array('type' => 'VARCHAR(255)', 'after' => 'setid'),
                'setvalue'       => array('type' => 'text', 'after' => 'setname')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_calendar_settings` (`setid`, `setname`, `setvalue`)
            VALUES  (1, 'mailTitle', 'Neuer Termin'),
                    (2, 'mailContent', 'Hallo [[FIRSTNAME]] [[LASTNAME]] \r\n\r\nAuf [[URL]] wurde ein neuer Termin für ihre Gruppe aufgeschaltet. Mit folgendem Link können Sie sich für diesen Anlass an- oder abmelden. \r\n\r\n[[REG_LINK]]\r\n\r\nFreundliche Grüsse \r\n[[URL]] - Team \r\n\r\n\r\n*Diese Nachricht wurde automatisch generiert*\r\n[[DATE]]\r\n'),
                    (3, 'mailConTitle', '[[REG_TYPE]] erfolgreich eingetragen'),
                    (4, 'mailConContent', 'Hallo [[FIRSTNAME]] [[LASTNAME]]\r\n\r\nIhre [[REG_TYPE]] zum Termin \"[[TITLE]] vom [[START_DATE]]\" wurde in unserem System erfolgreich eingetragen.\r\n\r\nFreundliche Grüsse\r\n[[URL]] - Team\r\n\r\n\r\n*Diese Nachricht wurde automatisch generiert*\r\n[[DATE]]'),
                    (5, 'mailNotTitle', 'Neue [[REG_TYPE]] auf [[URL]]'),
                    (6, 'mailNotContent', 'Hallo\r\n\r\nAuf [[URL]] wurde eine neue [[REG_TYPE]] eingetragen.\r\n\r\nTermin:   [[TITLE]]\r\nMeldung:  [[REG_TYPE]]\r\nVorname:  [[FIRSTNAME]]\r\nNachname: [[LASTNAME]]\r\nE-Mail:   [[E-MAIL]]\r\n\r\n\r\nWeitere Informationen können Sie in der Administrationskonsole einsehen.\r\n[[URL]]/cadmin/\r\n\r\n\r\n*Diese Nachricht wurde automatisch generiert*\r\n[[DATE]]'),
                    (7, 'fe_entries_ability', '0')
            ON DUPLICATE KEY UPDATE `setid` = `setid`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_calendar_style',
            array(
                'id'                         => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'tableWidth'                 => array('type' => 'VARCHAR(4)', 'notnull' => true, 'default' => '141', 'after' => 'id'),
                'tableHeight'                => array('type' => 'VARCHAR(4)', 'notnull' => true, 'default' => '92', 'after' => 'tableWidth'),
                'tableColor'                 => array('type' => 'VARCHAR(7)', 'notnull' => true, 'default' => '', 'after' => 'tableHeight'),
                'tableBorder'                => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'after' => 'tableColor'),
                'tableBorderColor'           => array('type' => 'VARCHAR(7)', 'notnull' => true, 'default' => '', 'after' => 'tableBorder'),
                'tableSpacing'               => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'after' => 'tableBorderColor'),
                'fontSize'                   => array('type' => 'INT(11)', 'notnull' => true, 'default' => '10', 'after' => 'tableSpacing'),
                'fontColor'                  => array('type' => 'VARCHAR(7)', 'notnull' => true, 'default' => '', 'after' => 'fontSize'),
                'numColor'                   => array('type' => 'VARCHAR(7)', 'notnull' => true, 'default' => '', 'after' => 'fontColor'),
                'normalDayColor'             => array('type' => 'VARCHAR(7)', 'notnull' => true, 'default' => '', 'after' => 'numColor'),
                'normalDayRollOverColor'     => array('type' => 'VARCHAR(7)', 'notnull' => true, 'default' => '', 'after' => 'normalDayColor'),
                'curDayColor'                => array('type' => 'VARCHAR(7)', 'notnull' => true, 'default' => '', 'after' => 'normalDayRollOverColor'),
                'curDayRollOverColor'        => array('type' => 'VARCHAR(7)', 'notnull' => true, 'default' => '', 'after' => 'curDayColor'),
                'eventDayColor'              => array('type' => 'VARCHAR(7)', 'notnull' => true, 'default' => '', 'after' => 'curDayRollOverColor'),
                'eventDayRollOverColor'      => array('type' => 'VARCHAR(7)', 'notnull' => true, 'default' => '', 'after' => 'eventDayColor'),
                'shownEvents'                => array('type' => 'INT(4)', 'notnull' => true, 'default' => '10', 'after' => 'eventDayRollOverColor'),
                'periodTime'                 => array('type' => 'VARCHAR(5)', 'notnull' => true, 'default' => '00 23', 'after' => 'shownEvents'),
                'stdCat'                     => array('type' => 'text', 'after' => 'periodTime')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_calendar_style` (`id`, `tableWidth`, `tableHeight`, `tableColor`, `tableBorder`, `tableBorderColor`, `tableSpacing`, `fontSize`, `fontColor`, `numColor`, `normalDayColor`, `normalDayRollOverColor`, `curDayColor`, `curDayRollOverColor`, `eventDayColor`, `eventDayRollOverColor`, `shownEvents`, `periodTime`, `stdCat`)
            VALUES  (1, '141', '92', '#ffffff', 1, '#cccccc', 0, 10, '#000000', '#0000ff', '#ffffff', '#eeeeee', '#00ccff', '#0066ff', '#00cc00', '#009900', 10, '00 23', ''),
                    (2, '141', '92', '#ffffff', 1, '#cccccc', 0, 10, '#000000', '#0000ff', '#ffffff', '#eeeeee', '#00ccff', '#0066ff', '#00cc00', '#009900', 10, '05 19', '1>1 2>0')
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }
}
