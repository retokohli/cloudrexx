<?php

function _newsletterUpdate()
{
    global $objDatabase;
    try{
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_newsletter_category',
            array(
                'id'                     => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'status'                 => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'id'),
                'name'                   => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'status'),
                'notification_email'     => array('type' => 'VARCHAR(250)', 'notnull' => true, 'default' => '', 'after' => 'name')
            ),
            array(
                'name'                   => array('fields' => array('name'))
            )
        );

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_newsletter_confirm_mail',
            array(
                'id'             => array('type' => 'INT(1)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'title'          => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'id'),
                'content'        => array('type' => 'longtext', 'after' => 'title'),
                'recipients'     => array('type' => 'text', 'after' => 'content')
            )
        );

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_newsletter',
            array(
                'id'                 => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'subject'            => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'id'),
                'template'           => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'after' => 'subject'),
                'content'            => array('type' => 'text', 'after' => 'template'),
                'attachment'         => array('type' => 'ENUM(\'0\',\'1\')', 'notnull' => true, 'default' => '0', 'after' => 'content'),
                'priority'           => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'attachment'),
                'sender_email'       => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'priority'),
                'sender_name'        => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'sender_email'),
                'return_path'        => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'sender_name'),
                'smtp_server'        => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'return_path'),
                'status'             => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'smtp_server'),
                'count'              => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'after' => 'status'),
                'recipient_count'    => array('type' => 'INT(11)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'count'),
                'date_create'        => array('type' => 'INT(14)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'recipient_count'),
                'date_sent'          => array('type' => 'INT(14)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'date_create'),
                'tmp_copy'           => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'date_sent')
            )
        );
    }
    catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }


    DBG::msg("Done checking tables.. going to check settings");
    

    //the two values notifyOnUnsubscribe and notificationUnsubscribe have been merged into the latter.
    $unsubscribeVal=1;
    try {
        DBG::msg("Retrieving old unsubscribe value if set.");
        $res = \Cx\Lib\UpdateUtil::sql("SELECT setvalue FROM ".DBPREFIX."module_newsletter_settings WHERE setname='notifyOnUnsubscribe'");
        
        if(!$res->EOF){
            $unsubscribeVal = $res->fields['setvalue'];
        }
        else //maybe update ran already => preserve new value
        {
            DBG::msg("Not found. Retrieving new unsubscribe value if set.");
            $res = \Cx\Lib\UpdateUtil::sql("SELECT setvalue FROM ".DBPREFIX."module_newsletter_settings WHERE setname='notificatonUnsubscribe'");
            if(!$res->EOF)
            $unsubscribeVal = $res->fields['setvalue'];
        }

    }
    catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }
        
    $settings = array(
        'sender_mail'             => array('setid' =>  1, 'setname' => 'sender_mail',             'setvalue' => 'info@example.com', 'status' => 1),
        'sender_name'             => array('setid' =>  2, 'setname' => 'sender_name',             'setvalue' => 'admin',            'status' => 1),
        'reply_mail'              => array('setid' =>  3, 'setname' => 'reply_mail',              'setvalue' => 'info@example.com', 'status' => 1),
        'mails_per_run'           => array('setid' =>  4, 'setname' => 'mails_per_run',           'setvalue' => '30',               'status' => 1),
        'text_break_after'        => array('setid' =>  5, 'setname' => 'text_break_after',        'setvalue' => '100',              'status' => 1),
        'test_mail'               => array('setid' =>  6, 'setname' => 'test_mail',               'setvalue' => 'info@example.com', 'status' => 1),
        'overview_entries_limit'  => array('setid' =>  7, 'setname' => 'overview_entries_limit',  'setvalue' => '10',               'status' => 1),
        'rejected_mail_operation' => array('setid' =>  8, 'setname' => 'rejected_mail_operation', 'setvalue' => 'delete',           'status' => 1),
        'defUnsubscribe'          => array('setid' =>  9, 'setname' => 'defUnsubscribe',          'setvalue' => '0',                'status' => 1),
        'notificationSubscribe'   => array('setid' => 11, 'setname' => 'notificationSubscribe',   'setvalue' => '1',                'status' => 1),
        'notificationUnsubscribe' => array('setid' => 10, 'setname' => 'notificationUnsubscribe', 'setvalue' => $unsubscribeVal,    'status' => 1),
    );

    try {
        DBG::msg("Reading current settings");
        $res = \Cx\Lib\UpdateUtil::sql("SELECT * FROM ".DBPREFIX."module_newsletter_settings");
        while (!$res->EOF) {
            $field = $res->fields['setname'];
            DBG::msg("...merging $field with default settings");
            if(isset($settings[$field])) //do we have another value for this?
                $settings[$field]['setvalue'] = $res->fields['setvalue'];
            $res->MoveNext();
        }
        DBG::msg("Updating settings");
        foreach ($settings as $entry) {
            $setid = intval    ($entry['setid']);
            $field = addslashes($entry['setname']);
            $value = addslashes($entry['setvalue']);
            $status= intval    ($entry['status']);
            DBG::msg("...deleting field $field");
            \Cx\Lib\UpdateUtil::sql("DELETE FROM ".DBPREFIX."module_newsletter_settings WHERE setid = '$setid' OR setname = '$field'");
            DBG::msg("...rewriting field $field");
            \Cx\Lib\UpdateUtil::sql("
                INSERT INTO ".DBPREFIX."module_newsletter_settings
                    (setid, setname, setvalue, status)
                VALUES (
                    '$setid', '$field', '$value', '$status'
                );
            ");
        }
        DBG::msg("Deleting old unsubscribe key if set");
        \Cx\Lib\UpdateUtil::sql("DELETE FROM ".DBPREFIX."module_newsletter_settings WHERE setname='notifyOnUnsubscribe'");
        DBG::msg("Done with newsletter update");
    }
    catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }

    try {
        DBG::msg("Setting recipient count");
        $objResult = \Cx\Lib\UpdateUtil::sql("SELECT `newsletter`, COUNT(1) AS recipient_count FROM `".DBPREFIX."module_newsletter_tmp_sending` GROUP BY `newsletter`");
        if ($objResult->RecordCount()) {
            while(!$objResult->EOF) {
                \Cx\Lib\UpdateUtil::sql("UPDATE `".DBPREFIX."module_newsletter` SET `recipient_count` = ".$objResult->fields['recipient_count']." WHERE `id`=".$objResult->fields['newsletter']);
                $objResult->MoveNext();
            }
        }
    }
    catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }




    // Add notification recipients to confirm_mail table
    try {
        $objResult = \Cx\Lib\UpdateUtil::sql("SELECT id FROM `".DBPREFIX."module_newsletter_confirm_mail` WHERE id='3'");
        if ($objResult->RecordCount() == 0) {
            DBG::msg("inserting standard confirm mails");
            \Cx\Lib\UpdateUtil::sql("INSERT INTO `".DBPREFIX."module_newsletter_confirm_mail` (`id` ,`title` ,`content` ,`recipients`) VALUES ('3', '[[url]] - Neue Newsletter Empfänger [[action]]', 'Hallo Admin Eine neue Empfänger [[action]] in ihrem Newsletter System. Automatisch generierte Nachricht [[date]]', '');");
        }
    }
    catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }




    try {
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_newsletter_access_user',
            array(
                'accessUserID'               => array('type' => 'INT(5)', 'unsigned' => true),
                'newsletterCategoryID'       => array('type' => 'INT(11)', 'after' => 'accessUserID'),
                'code'                       => array('type' => 'VARCHAR(255)', 'after' => 'newsletterCategoryID', 'notnull' => true, 'default' => '')
            ),
            array(
                'rel'                        => array('fields' => array('accessUserID','newsletterCategoryID'), 'type' => 'UNIQUE'),
                'accessUserID'               => array('fields' => array('accessUserID'))
            )
        );

        // set random newsletter code for access recipients
        \Cx\Lib\UpdateUtil::sql('UPDATE '.DBPREFIX.'module_newsletter_access_user SET `code` = SUBSTR(MD5(RAND()),1,12) WHERE `code` = \'\'');

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_newsletter_rel_usergroup_newsletter',
            array(
                'userGroup'      => array('type' => 'INT(10)', 'unsigned' => true),
                'newsletter'     => array('type' => 'INT(10)', 'unsigned' => true, 'after' => 'userGroup')
            ),
            array(
                'uniq'           => array('fields' => array('userGroup','newsletter'), 'type' => 'UNIQUE')
            )
        );

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_newsletter_settings',
            array(
                'setid'          => array('type' => 'INT(6)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'setname'        => array('type' => 'VARCHAR(250)', 'after' => 'setid', 'notnull' => true, 'default' => ''),
                'setvalue'       => array('type' => 'text', 'after' => 'setname'),
                'status'         => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'setvalue')
            ),
            array(
                'setname'        => array('fields' => array('setname'), 'type' => 'UNIQUE')
            )
        );

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_newsletter_tmp_sending',
            array(
                'id'             => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'newsletter'     => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'after' => 'id'),
                'email'          => array('type' => 'VARCHAR(255)', 'after' => 'newsletter', 'notnull' => true, 'default' => ''),
                'sendt'          => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'email'),
                'type'           => array('type' => 'ENUM(\'access\',\'newsletter\')', 'notnull' => true, 'default' => 'newsletter', 'after' => 'sendt'),
                'code'           => array('type' => 'VARCHAR(10)', 'after' => 'type')
            ),
            array(
                'unique_email'   => array('fields' => array('newsletter','email'), 'type' => 'UNIQUE'),
                'email'          => array('fields' => array('email'))
            )
        );

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_newsletter_email_link',
            array(
                'id'             => array('type' => 'INT(11)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'email_id'       => array('type' => 'INT(11)', 'unsigned' => true, 'after' => 'id'),
                'title'          => array('type' => 'VARCHAR(255)', 'after' => 'email_id'),
                'url'            => array('type' => 'VARCHAR(255)', 'after' => 'title')
            ),
            array(
                'email_id'       => array('fields' => array('email_id'))
            )
        );

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_newsletter_email_link_feedback',
            array(
                'id'                 => array('type' => 'INT(11)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'link_id'            => array('type' => 'INT(11)', 'unsigned' => true, 'after' => 'id'),
                'email_id'           => array('type' => 'INT(11)', 'unsigned' => true, 'after' => 'link_id'),
                'recipient_id'       => array('type' => 'INT(11)', 'unsigned' => true, 'after' => 'email_id'),
                'recipient_type'     => array('type' => 'ENUM(\'access\',\'newsletter\')', 'after' => 'recipient_id')
            ),
            array(
                'link_id'            => array('fields' => array('link_id','email_id','recipient_id','recipient_type'), 'type' => 'UNIQUE'),
                'email_id'           => array('fields' => array('email_id'))
            )
        );

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_newsletter_template',
            array(
                'id'             => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'name'           => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'id'),
                'description'    => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'name'),
                'html'           => array('type' => 'text', 'after' => 'description'),
                'required'       => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'html'),
                'type'           => array('type' => 'ENUM(\'e-mail\',\'news\')', 'notnull' => true, 'default' => 'e-mail', 'after' => 'required')
            )
        );

        // migrate country field
        newsletter_migrate_country_field();

        // fix missing columns & rename old columns if required
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_newsletter_user',
            array(
                'id'                 => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'code'               => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'id'),
                'email'              => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'code'),
                'uri'                => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'email'),
                'sex'                => array('type' => 'ENUM(\'m\',\'f\')', 'notnull' => false, 'after' => 'uri'),
                'salutation'         => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'sex'),
                'title'              => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'salutation'),
                'lastname'           => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'title'),
                'firstname'          => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'lastname'),
                'position'           => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'firstname'),
                'company'            => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'position'),
                'industry_sector'    => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'company'),
                'address'            => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'industry_sector', 'renamefrom' => 'street'),
                'zip'                => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'address'),
                'city'               => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'zip'),
                'country_id'         => array('type' => 'SMALLINT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'city'),
                'phone_office'       => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'country_id', 'renamefrom' => 'phone'),
                'phone_private'      => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'phone_office'),
                'phone_mobile'       => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'phone_private'),
                'fax'                => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'phone_mobile'),
                'notes'              => array('type' => 'text', 'after' => 'fax'),
                'birthday'           => array('type' => 'VARCHAR(10)', 'notnull' => true, 'default' => '00-00-0000', 'after' => 'notes'),
                'status'             => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'birthday'),
                'emaildate'          => array('type' => 'INT(14)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'status'),
                'language'           => array('type' => 'INT(3)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'emaildate')
            ),
            array(
                'email'              => array('fields' => array('email'), 'type' => 'UNIQUE'),
                'status'             => array('fields' => array('status'))
            )
        );


        // replace several placeholders that have changed
        $search = array(
            '/TXT_NEWSLETTER_URI/',
            '/NEWSLETTER_URI/',
            '/TXT_NEWSLETTER_STREET/',
            '/NEWSLETTER_STREET/',
        );
        $replace = array(
            'TXT_NEWSLETTER_WEBSITE',
            'NEWSLETTER_WEBSITE',
            'TXT_NEWSLETTER_ADDRESS',
            'NEWSLETTER_ADDRESS',
        );
        \Cx\Lib\UpdateUtil::migrateContentPageUsingRegex(array('module' => 'newsletter'), $search, $replace, array('content'), '3.0.1');


        // sorry, brainfuck coming up...
        // this adds the missing template block newsletter_list as well as the placeholder [[NEWSLETTER_LIST_SELECTED]]
        $search = array(
            '/(<!--\s+BEGIN\s+newsletter_lists\s+-->)(.*)(<!--\s+END\s+newsletter_lists\s+-->)/ms',
        );
        $callback = function($matches) {
            if (preg_match('/^(.*)(<[^>]+[\'"]list\[\{NEWSLETTER_LIST_ID\}\][\'"])([^>]*>)(.*)$/ms', $matches[2], $listMatches)) {
                if (strpos($listMatches[2].$listMatches[3], '{NEWSLETTER_LIST_SELECTED}') === false) {
                    $matches[2] = $listMatches[1].$listMatches[2].' {NEWSLETTER_LIST_SELECTED} '.$listMatches[3].$listMatches[4];
                } else {
                    $matches[2] = $listMatches[1].$listMatches[2].$listMatches[3].$listMatches[4];
                }
            }

            if (!preg_match('/<!--\s+BEGIN\s+newsletter_list\s+-->.*<!--\s+END\s+newsletter_list\s+-->/ms', $matches[2])) {
                return $matches[1].'<!-- BEGIN newsletter_list -->'.$matches[2].'<!-- END newsletter_list -->'.$matches[3];
            } else {
                return $matches[1].$matches[2].$matches[3];
            };
        };
        \Cx\Lib\UpdateUtil::migrateContentPageUsingRegexCallback(array('module' => 'newsletter'), $search, $callback, array('content'), '3.0.1');


        // this adds the missing placeholders [[SELECTED_DAY]], [[SELECTED_MONTH]], [[SELECTED_YEAR]]
        $search = array(
            '/(<option[^>]+\{USERS_BIRTHDAY_(DAY|MONTH|YEAR)\}[\'"])([^>]*>)/ms',
        );
        $callback = function($matches) {
            if (strpos($matches[1].$matches[3], '{SELECTED_'.$matches[2].'}') === false) {
                return $matches[1].' {SELECTED_'.$matches[2].'} '.$matches[3];
            } else {
                return $matches[1].$matches[3];
            }
        };
        \Cx\Lib\UpdateUtil::migrateContentPageUsingRegexCallback(array('module' => 'newsletter'), $search, $callback, array('content'), '3.0.1');


        // replace [[TXT_NEWSLETTER_TITLE]] to [[TXT_NEWSLETTER_SALUTATION]]
        // replace [[NEWSLETTER_TITLE]] to [[NEWSLETTER_SALUTATION]]
        $search = array(
            '/.*\{NEWSLETTER_TITLE\}.*/ms',
        );
        $callback = function($matches) {
            if (   !preg_match('/<!--\s+BEGIN\s+recipient_title\s+-->.*\{NEWSLETTER_TITLE\}.*<!--\s+END\s+recipient_title\s+-->/ms', $matches[0])
                && !preg_match('/<!--\s+BEGIN\s+recipient_salutation\s+-->/ms', $matches[0])
                && !preg_match('/\{NEWSLETTER_SALUTATION\}/ms', $matches[0])
            ) {
                return str_replace(array('TXT_NEWSLETTER_TITLE', '{NEWSLETTER_TITLE}'), array('TXT_NEWSLETTER_SALUTATION', '{NEWSLETTER_SALUTATION}'), $matches[0]);
            } else {
                return $matches[0];
            }
        };
        \Cx\Lib\UpdateUtil::migrateContentPageUsingRegexCallback(array('module' => 'newsletter'), $search, $callback, array('content'), '3.0.1');

    }
    catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }

    return true;
}

/**
 * Note: the body of this function is by intention not enclosed in a try/catch block. We wan't the calling sections to catch and handle exceptions themself.
 */
function newsletter_migrate_country_field()
{
/*
TEST
                $countryId = 0;
$text = 'Switzerland';
                $objText= \Cx\Lib\UpdateUtil::sql("SELECT `id` FROM `".DBPREFIX."core_text` WHERE `section` = 'core' AND `key` = 'core_country_name' AND `text` = '".contrexx_raw2db($text)."'");
                if (!$objResult->EOF) {
                    $countryId = $objText->fields['id'];
                }
\DBG::dump($countryId);
return;
*/
    ///////////////////////////
    // MIGRATE COUNTRY FIELD //
    ///////////////////////////
    // 1. backup country column to country_old
    if (\Cx\Lib\UpdateUtil::column_exist(DBPREFIX.'module_newsletter_user', 'country')) {
        \Cx\Lib\UpdateUtil::sql('ALTER TABLE `'.DBPREFIX.'module_newsletter_user` CHANGE `country` `country_old` VARCHAR(255) NOT NULL DEFAULT \'\'');
    }
    // 2. add new column country_id (format int)
    if (!\Cx\Lib\UpdateUtil::column_exist(DBPREFIX.'module_newsletter_user', 'country_id')) {
        \Cx\Lib\UpdateUtil::sql('ALTER TABLE `'.DBPREFIX.'module_newsletter_user` ADD `country_id` SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT \'0\' AFTER `country_old`');
    }

    // 3. migrate to new country format (using IDs)
    if (\Cx\Lib\UpdateUtil::column_exist(DBPREFIX.'module_newsletter_user', 'country_old')) {
        $objResult = \Cx\Lib\UpdateUtil::sql('SELECT `id`, `country_old` FROM `'.DBPREFIX.'module_newsletter_user` WHERE `country_id` = 0');
        if ($objResult->RecordCount()) {
            while (!$objResult->EOF) {
                // try setting country_id based on a guess from country_old
                $countryId = 0;
                $objText= \Cx\Lib\UpdateUtil::sql("SELECT `id` FROM `".DBPREFIX."core_text` WHERE `section` = 'core' AND `key` = 'core_country_name' AND `text` = '".contrexx_raw2db($objResult->fields['country_old'])."'");
                if (!$objResult->EOF) {
                    $countryId = $objText->fields['id'];
                }
                \Cx\Lib\UpdateUtil::sql('UPDATE `'.DBPREFIX.'module_newsletter_user` SET `country_id` = \''.contrexx_raw2db($countryId).'\' WHERE `id` = '.$objResult->fields['id']);
                if (!checkTimeoutLimit()) {
                    throw new \Cx\Lib\UpdateException();
                }
                $objResult->MoveNext();
            }
        }

        // backup literal country name in field notes
        if (!\Cx\Lib\UpdateUtil::column_exist(DBPREFIX.'module_newsletter_user', 'notes')) {
            if (\Cx\Lib\UpdateUtil::column_exist(DBPREFIX.'module_newsletter_user', 'fax')) {
                $column = 'fax';
            } else {
                // versions pre 3.0.0 didn't have the column 'fax' yet
                $column = 'phone';
            }
            \Cx\Lib\UpdateUtil::sql('ALTER TABLE `'.DBPREFIX.'module_newsletter_user` ADD `notes` text NOT NULL AFTER `'.$column.'`');
        }
        \Cx\Lib\UpdateUtil::sql('UPDATE `'.DBPREFIX.'module_newsletter_user` SET `notes` = `country_old`');
        // drop obsolete column country_old'
        \Cx\Lib\UpdateUtil::sql('ALTER TABLE `'.DBPREFIX.'module_newsletter_user` DROP `country_old`');
    }
    ////////////////////////////////
    // END: MIGRATE COUNTRY FIELD //
    ////////////////////////////////
}



function _newsletterInstall()
{
    try {

        /**************************************************************************
         * EXTENSION:   Initial creation (for contrexx editions <> premium with   *
         *              version < 3.0.0 which have this module not yet installed) *
         *                                                                        *
         * ADDED:       Contrexx v3.0.1                                           *
         **************************************************************************/
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_newsletter',
            array(
                'id'                 => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'subject'            => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'id'),
                'template'           => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'after' => 'subject'),
                'content'            => array('type' => 'text', 'after' => 'template'),
                'attachment'         => array('type' => 'ENUM(\'0\',\'1\')', 'notnull' => true, 'default' => '0', 'after' => 'content'),
                'priority'           => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'attachment'),
                'sender_email'       => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'priority'),
                'sender_name'        => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'sender_email'),
                'return_path'        => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'sender_name'),
                'smtp_server'        => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'return_path'),
                'status'             => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'smtp_server'),
                'count'              => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'after' => 'status'),
                'recipient_count'    => array('type' => 'INT(11)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'count'),
                'date_create'        => array('type' => 'INT(14)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'recipient_count'),
                'date_sent'          => array('type' => 'INT(14)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'date_create'),
                'tmp_copy'           => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'date_sent')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_newsletter` (`id`, `subject`, `template`, `content`, `attachment`, `priority`, `sender_email`, `sender_name`, `return_path`, `smtp_server`, `status`, `count`, `recipient_count`, `date_create`, `date_sent`, `tmp_copy`)
            VALUES (2, 'Neue Homepage', 1, '[[salutation]] [[lastname]]<br />\r\n<br />\r\nGerne machen wir Sie auf unsere neue Website aufmerksam. Der Internetauftritt wurde komplett erneuert und aufgefrischt. Sie finden nun alle Informationen &uuml;bersichtlich dargestellt und ein modernisiertes Design.<br />\r\n<br />\r\nF&uuml;r unser Webprojekt haben wir <a  href=\"http://www.contrexx.com\" rel=\"newsletter_link_1\" target=\"_blank\">Contrexx</a> als Web Content Management System ausgew&auml;hlt. Contrexx beinhaltet alle wichtigen Anwendungen, die eine Website heute braucht. Die Inhalte und Funktionen k&ouml;nnen jederzeit mit kleinem Aufwand bearbeitet und aufgefrischt werden, um stets eine aktuelle und ansprechende Webseite zu pr&auml;sentieren.<br />\r\n<br />\r\n<br />\r\nIhr MaxMuster-Team', '0', 3, 'info@example.com', 'admin', 'info@maxmusterag.com', 0, 0, 0, 0, 1348220511, 0, 0)
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_newsletter_access_user',
            array(
                'accessUserID'               => array('type' => 'INT(5)', 'unsigned' => true),
                'newsletterCategoryID'       => array('type' => 'INT(11)', 'after' => 'accessUserID'),
                'code'                       => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'newsletterCategoryID')
            ),
            array(
                'rel'                        => array('fields' => array('accessUserID','newsletterCategoryID'), 'type' => 'UNIQUE'),
                'accessUserID'               => array('fields' => array('accessUserID'))
            ),
            'MyISAM',
            'cx3upgrade'
        );

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_newsletter_attachment',
            array(
                'id'             => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'newsletter'     => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'after' => 'id'),
                'file_name'      => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'newsletter'),
                'file_nr'        => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'file_name')
            ),
            array(
                'newsletter'     => array('fields' => array('newsletter'))
            ),
            'MyISAM',
            'cx3upgrade'
        );

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_newsletter_category',
            array(
                'id'                     => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'status'                 => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'id'),
                'name'                   => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'status'),
                'notification_email'     => array('type' => 'VARCHAR(250)', 'notnull' => true, 'default' => '', 'after' => 'name')
            ),
            array(
                'name'                   => array('fields' => array('name'))
            ),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_newsletter_category` (`id`, `status`, `name`, `notification_email`)
            VALUES (1, 1, 'Demo Liste', '')
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_newsletter_confirm_mail',
            array(
                'id'             => array('type' => 'INT(1)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'title'          => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'id'),
                'content'        => array('type' => 'longtext', 'after' => 'title'),
                'recipients'     => array('type' => 'text', 'after' => 'content')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_newsletter_confirm_mail` (`id`, `title`, `content`, `recipients`)
            VALUES  (1, '[[url]] - Anmeldung zum Newsletter', '[[title]] [[lastname]]\r\n\r\nWir freuen uns, Sie bei unserem Newsletter begrüssen zu dürfen und wünschen Ihnen viel Freude damit.\r\nSie erhalten ab sofort wöchentlich die neuesten Informationen in elektronischer Form zu gestellt.\r\n\r\nUm die Bestellung des Newsletters zu bestätigen, bitten wir Sie, auf den folgenden Link zu klicken bzw. ihn in die Adresszeile Ihres Browsers zu kopieren:\r\n\r\n[[code]]\r\n\r\nUm zu verhindern, dass unser Newsletter in Ihrem Spam-Ordner landet, fügen Sie bitte die Adresse dieser E-Mail Ihrem Adressbuch hinzu.\r\n\r\nSofern Sie diese E-Mail ungewünscht erhalten haben, bitten wir um Entschuldigung. Sie werden keine weitere E-Mail mehr von uns erhalten.\r\n\r\n--\r\nDies ist eine automatisch generierte Nachricht.\r\n[[date]]', ''),
                    (2, '[[url]] - Bestätigung zur Newsletteranmeldung', '[[title]] [[lastname]]\r\n\r\nIhr Newsletter Abonnement wurde erfolgreich registriert.\r\nSie werden nun in Zukunft unsere Newsletter erhalten. \r\n\r\n--\r\nDies ist eine automatisch generierte Nachricht.\r\n[[date]]', ''),
                    (3, '[[url]] - Newsletter Empfänger [[action]]', 'Folgende Mutation wurde im Newsletter System getätigt:\r\n\r\nGetätigte Aktion: [[action]]\r\nGeschlecht:       [[sex]]\r\nAnrede:           [[title]]\r\nVorname:          [[firstname]]\r\nNachname:         [[lastname]]\r\nE-Mail:           [[e-mail]]\r\n\r\n--\r\nDies ist eine automatisch generierte Nachricht.\r\n[[date]]', 'info@example.com')
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_newsletter_email_link',
            array(
                'id'             => array('type' => 'INT(11)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'email_id'       => array('type' => 'INT(11)', 'unsigned' => true, 'after' => 'id'),
                'title'          => array('type' => 'VARCHAR(255)', 'after' => 'email_id'),
                'url'            => array('type' => 'VARCHAR(255)', 'after' => 'title')
            ),
            array(
                'email_id'       => array('fields' => array('email_id'))
            ),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_newsletter_email_link` (`id`, `email_id`, `title`, `url`)
            VALUES (1, 2, 'Contrexx', 'http://www.contrexx.com')
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_newsletter_email_link_feedback',
            array(
                'id'                 => array('type' => 'INT(11)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'link_id'            => array('type' => 'INT(11)', 'unsigned' => true, 'after' => 'id'),
                'email_id'           => array('type' => 'INT(11)', 'unsigned' => true, 'after' => 'link_id'),
                'recipient_id'       => array('type' => 'INT(11)', 'unsigned' => true, 'after' => 'email_id'),
                'recipient_type'     => array('type' => 'ENUM(\'access\',\'newsletter\')', 'after' => 'recipient_id')
            ),
            array(
                'link_id'            => array('fields' => array('link_id','email_id','recipient_id','recipient_type'), 'type' => 'UNIQUE'),
                'email_id'           => array('fields' => array('email_id'))
            ),
            'MyISAM',
            'cx3upgrade'
        );

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_newsletter_rel_cat_news',
            array(
                'newsletter'     => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'primary' => true),
                'category'       => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'primary' => true, 'after' => 'newsletter')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_newsletter_rel_cat_news` (`newsletter`, `category`)
            VALUES (2, 1)
            ON DUPLICATE KEY UPDATE `newsletter` = `newsletter`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_newsletter_rel_user_cat',
            array(
                'user'           => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'primary' => true),
                'category'       => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'primary' => true, 'after' => 'user')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_newsletter_rel_user_cat` (`user`, `category`)
            VALUES (1, 1)
            ON DUPLICATE KEY UPDATE `user` = `user`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_newsletter_rel_usergroup_newsletter',
            array(
                'userGroup'      => array('type' => 'INT(10)', 'unsigned' => true),
                'newsletter'     => array('type' => 'INT(10)', 'unsigned' => true, 'after' => 'userGroup')
            ),
            array(
                'uniq'           => array('fields' => array('userGroup','newsletter'), 'type' => 'UNIQUE')
            ),
            'MyISAM',
            'cx3upgrade'
        );

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_newsletter_settings',
            array(
                'setid'          => array('type' => 'INT(6)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'setname'        => array('type' => 'VARCHAR(250)', 'notnull' => true, 'default' => '', 'after' => 'setid'),
                'setvalue'       => array('type' => 'text', 'after' => 'setname'),
                'status'         => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'setvalue')
            ),
            array(
                'setname'        => array('fields' => array('setname'), 'type' => 'UNIQUE')
            ),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_newsletter_settings` (`setid`, `setname`, `setvalue`, `status`)
            VALUES  (1, 'sender_mail', 'info@example.com', 1),
                    (2, 'sender_name', 'admin', 1),
                    (3, 'reply_mail', 'info@example.com', 1),
                    (4, 'mails_per_run', '30', 1),
                    (5, 'text_break_after', '100', 1),
                    (6, 'test_mail', 'info@example.com', 1),
                    (7, 'overview_entries_limit', '10', 1),
                    (8, 'rejected_mail_operation', 'delete', 1),
                    (9, 'defUnsubscribe', '0', 1),
                    (10, 'notificationUnsubscribe', '1', 1),
                    (11, 'notificationSubscribe', '1', 1),
                    (12, 'recipient_attribute_status', '{\"recipient_sex\":{\"active\":true,\"required\":false},\"recipient_salutation\":{\"active\":true,\"required\":false},\"recipient_title\":{\"active\":true,\"required\":false},\"recipient_firstname\":{\"active\":true,\"required\":false},\"recipient_lastname\":{\"active\":true,\"required\":false},\"recipient_position\":{\"active\":true,\"required\":false},\"recipient_company\":{\"active\":true,\"required\":false},\"recipient_industry\":{\"active\":true,\"required\":false},\"recipient_address\":{\"active\":true,\"required\":false},\"recipient_city\":{\"active\":true,\"required\":false},\"recipient_zip\":{\"active\":true,\"required\":false},\"recipient_country\":{\"active\":true,\"required\":false},\"recipient_phone\":{\"active\":true,\"required\":false},\"recipient_private\":{\"active\":true,\"required\":false},\"recipient_mobile\":{\"active\":true,\"required\":false},\"recipient_fax\":{\"active\":true,\"required\":false},\"recipient_birthday\":{\"active\":true,\"required\":false},\"recipient_website\":{\"active\":false,\"required\":false}}', 1),
                    (13, 'reject_info_mail_text', 'Der Newsletter konnte an folgende E-Mail-Adresse nicht versendet werden:\r\n[[EMAIL]]\r\n\r\nUm die E-Mail Adresse zu bearbeiten, klicken Sie bitte auf den folgenden Link:\r\n[[LINK]]', 1)
            ON DUPLICATE KEY UPDATE `setid` = `setid`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_newsletter_template',
            array(
                'id'             => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'name'           => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'id'),
                'description'    => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'name'),
                'html'           => array('type' => 'text', 'after' => 'description'),
                'required'       => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'html'),
                'type'           => array('type' => 'ENUM(\'e-mail\',\'news\')', 'notnull' => true, 'default' => 'e-mail', 'after' => 'required')
            ),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_newsletter_template` (`id`, `name`, `description`, `html`, `required`, `type`)
            VALUES  (1, 'Standard', 'Standard Template', '<html>\r\n    <head>\r\n        <style type=\"text/css\">\r\n        *, html, body, table {\r\n         padding: 0;\r\n         margin: 0;\r\n          font-size: 12px;\r\n            font-family: arial;\r\n         line-height: 1.5;\r\n           color: #000000;\r\n        }\r\n\r\n        h1 {\r\n            padding: 20px 0 5px 0;\r\n            font-size: 20px;\r\n          color: #487EAD;\r\n        }\r\n\r\n        h2 {\r\n            padding: 18px 0 4px 0;\r\n            font-size: 16px;\r\n          color: #487EAD;\r\n        }\r\n\r\n        h3, h4, h5, h6 {\r\n            padding: 16px 0 3px 0;\r\n            font-size: 13px;\r\n          font-weight: bold;\r\n              color: #487EAD;\r\n        }\r\n\r\n        a,\r\n        a:link,\r\n       a:hover,\r\n        a:focus,\r\n        a:active,\r\n       a:visited {\r\n            color: #487EAD !imprtant;\r\n        }\r\n        </style>\r\n    </head>\r\n    <body>\r\n        <table height=\"100%\" width=\"100%\" cellspacing=\"60\" style=\"background-color: rgb(204, 204, 204);\">\r\n            <tbody>\r\n                <tr>\r\n                    <td align=\"center\">\r\n                    <table width=\"660\" cellspacing=\"30\" bgcolor=\"#ffffff\" style=\"border: 7px solid rgb(72, 126, 173);\">\r\n                        <tbody>\r\n                            <tr>\r\n                                <td style=\"font-family: arial; font-size: 12px;\"><a target=\"_blank\" style=\"font-family: arial; font-size: 20px; text-decoration: none; color: rgb(72, 126, 173);\" href=\"http://www.example.com\">example.com</a><br />\r\n                                <span style=\"font-size: 20px; font-family: arial; text-decoration: none; color: rgb(72, 126, 173);\">Newsletter</span></td>\r\n                            </tr>\r\n                            <tr>\r\n                                <td style=\"font-family: arial; font-size: 12px; color: rgb(0, 0, 0);\">[[content]]</td>\r\n                            </tr>\r\n                            <tr>\r\n                                <td>\r\n                                <table width=\"600\" cellspacing=\"0\">\r\n                                    <tbody>\r\n                                        <tr>\r\n                                            <td height=\"30\" colspan=\"3\" style=\"border-top: 3px solid rgb(72, 126, 173);\">&nbsp;</td>\r\n                                        </tr>\r\n                                        <tr>\r\n                                            <td width=\"235\" valign=\"top\" style=\"font-family: arial; font-size: 11px; color: rgb(0, 0, 0);\">\r\n                                            <h3 style=\"padding: 0pt; margin: 0pt 0pt 5px;\">Impressum</h3>\r\n                                            Beispiel AG<br />\r\n                                            Firmenstrasse 1<br />\r\n                                            CH-1234 Irgendwo<br />\r\n                                            <br />\r\n                                            Telefon: + 41 (0)12 345 67 89<br />\r\n                                            Fax: + 41 (0)12 345 67 90<br />\r\n                                            <br />\r\n                                            E-Mail: <a href=\"mailto:info@example.com\">info@example.com</a><br />\r\n                                            Web: <a href=\"http://www.example.com\">www.example.com</a></td>\r\n                                            <td width=\"30\" valign=\"top\">&nbsp;</td>\r\n                                            <td width=\"335\" valign=\"top\" style=\"font-family: arial; font-size: 11px; color: rgb(135, 135, 135);\">Diese Art der Korrespondenz ist absichtlich von uns gew&auml;hlt worden, um wertvolle Naturressourcen zu schonen. Dieses E-Mail ist ausdr&uuml;cklich nicht verschickt worden, um Ihre betrieblichen Vorg&auml;nge zu st&ouml;ren und dient ausschliesslich dazu, Sie auf einfachste Weise unverbindlich zu informieren. Falls Sie sich dadurch trotzdem bel&auml;stigt f&uuml;hlen, bitten wir Sie, umgehend mit einem Klick auf &quot;Newsletter abmelden&quot; sich abzumelden, so dass wir Sie aus unserem Verteiler l&ouml;schen k&ouml;nnen.<br />\r\n                                            <br />\r\n                                            <span>[[unsubscribe]]&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[[profile_setup]]</span></td>\r\n                                        </tr>\r\n                                    </tbody>\r\n                                </table>\r\n                                </td>\r\n                            </tr>\r\n                        </tbody>\r\n                    </table>\r\n                    </td>\r\n                </tr>\r\n            </tbody>\r\n        </table>\r\n    </body>\r\n</html>', 1, 'e-mail'),
                    (2, 'Standard', 'Standard News-Import Template', '<table border=\"0\" cellpadding=\"3\" cellspacing=\"0\" width=\"100%\">\r\n    <!-- BEGIN news_list --><!-- BEGIN news_category -->\r\n    <tbody>\r\n        <tr>\r\n            <td colspan=\"2\" style=\"text-align:left;\">\r\n                <h2>\r\n                    [[NEWS_CATEGORY_NAME]]</h2>\r\n            </td>\r\n        </tr>\r\n        <!-- END news_category --><!-- BEGIN news_message -->\r\n        <tr>\r\n            <td style=\"text-align:left;\" width=\"25%\">\r\n                <!-- BEGIN news_image --><img alt=\"\" height=\"100\" src=\"[[NEWS_IMAGE_SRC]]\" width=\"150\" /><!-- END news_image --></td>\r\n            <td style=\"text-align:left;\" width=\"75%\">\r\n                <h3>\r\n                    [[NEWS_TITLE]]</h3>\r\n                <p>\r\n                    [[NEWS_TEASER_TEXT]]</p>\r\n                <p>\r\n                    <a href=\"[[NEWS_URL]]\">Meldung lesen...</a></p>\r\n            </td>\r\n        </tr>\r\n        <!-- END news_message --><!-- END news_list -->\r\n    </tbody>\r\n</table>\r\n', 1, 'news')
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_newsletter_tmp_sending',
            array(
                'id'             => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'newsletter'     => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'after' => 'id'),
                'email'          => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'newsletter'),
                'sendt'          => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'email'),
                'type'           => array('type' => 'ENUM(\'access\',\'newsletter\')', 'notnull' => true, 'default' => 'newsletter', 'after' => 'sendt'),
                'code'           => array('type' => 'VARCHAR(10)', 'after' => 'type')
            ),
            array(
                'unique_email'   => array('fields' => array('newsletter','email'), 'type' => 'UNIQUE'),
                'email'          => array('fields' => array('email'))
            ),
            'MyISAM',
            'cx3upgrade'
        );

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_newsletter_user',
            array(
                'id'                 => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'code'               => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'id'),
                'email'              => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'code'),
                'uri'                => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'email'),
                'sex'                => array('type' => 'ENUM(\'m\',\'f\')', 'notnull' => false, 'after' => 'uri'),
                'salutation'         => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'sex'),
                'title'              => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'salutation'),
                'lastname'           => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'title'),
                'firstname'          => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'lastname'),
                'position'           => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'firstname'),
                'company'            => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'position'),
                'industry_sector'    => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'company'),
                'address'            => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'industry_sector'),
                'zip'                => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'address'),
                'city'               => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'zip'),
                'country_id'         => array('type' => 'SMALLINT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'city'),
                'phone_office'       => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'country_id'),
                'phone_private'      => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'phone_office'),
                'phone_mobile'       => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'phone_private'),
                'fax'                => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'phone_mobile'),
                'notes'              => array('type' => 'text', 'after' => 'fax'),
                'birthday'           => array('type' => 'VARCHAR(10)', 'notnull' => true, 'default' => '00-00-0000', 'after' => 'notes'),
                'status'             => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'birthday'),
                'emaildate'          => array('type' => 'INT(14)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'status'),
                'language'           => array('type' => 'INT(3)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'emaildate')
            ),
            array(
                'email'              => array('fields' => array('email'), 'type' => 'UNIQUE'),
                'status'             => array('fields' => array('status'))
            ),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_newsletter_user` (`id`, `code`, `email`, `uri`, `sex`, `salutation`, `title`, `lastname`, `firstname`, `position`, `company`, `industry_sector`, `address`, `zip`, `city`, `country_id`, `phone_office`, `phone_private`, `phone_mobile`, `fax`, `notes`, `birthday`, `status`, `emaildate`, `language`)
            VALUES (1, 'btKCKTku5u', 'noreply@example.com', '', 'm', 2, '', 'Mustermann', 'Hans', '', '', '', '', '', '', 204, '', '', '', '', '', '01-01-2011', 1, 1153137001, 1)
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_newsletter_user_title',
            array(
                'id'         => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'title'      => array('type' => 'VARCHAR(255)', 'after' => 'id')
            ),
            array(
                'title'      => array('fields' => array('title'), 'type' => 'UNIQUE')
            ),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_newsletter_user_title` (`id`, `title`)
            VALUES  (1, 'Sehr geehrte Frau'),
                    (2, 'Sehr geehrter Herr'),
                    (3, 'Dear Ms'),
                    (4, 'Dear Mr'),
                    (5, 'Chère Madame'),
                    (6, 'Cher Monsieur')
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }
}
