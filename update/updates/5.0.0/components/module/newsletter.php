<?php

/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2015
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Cloudrexx" is a registered trademark of Cloudrexx AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */


function _newsletterUpdate()
{
    global $objDatabase, $objUpdate, $_CONFIG;

    if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '3.0.0')) {
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

            DBG::msg("Done checking tables.. going to check settings");
        } catch (\Cx\Lib\UpdateException $e) {
            return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
        }


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
            'recipient_attribute_status' => array('setid' => 12, 'setname' => 'recipient_attribute_status', 'setvalue' => '{"recipient_sex":{"active":true,"required":false},"recipient_salutation":{"active":true,"required":false},"recipient_title":{"active":false,"required":false},"recipient_firstname":{"active":true,"required":false},"recipient_lastname":{"active":true,"required":false},"recipient_position":{"active":false,"required":false},"recipient_company":{"active":true,"required":false},"recipient_industry":{"active":false,"required":false},"recipient_address":{"active":true,"required":false},"recipient_city":{"active":true,"required":false},"recipient_zip":{"active":true,"required":false},"recipient_country":{"active":true,"required":false},"recipient_phone":{"active":true,"required":false},"recipient_private":{"active":false,"required":false},"recipient_mobile":{"active":false,"required":false},"recipient_fax":{"active":false,"required":false},"recipient_birthday":{"active":true,"required":false},"recipient_website":{"active":false,"required":false}}',    'status' => 1),
            'reject_info_mail_text'   => array('setid' => 13, 'setname' => 'reject_info_mail_text', 'setvalue' => 'Der Newsletter konnte an folgende E-Mail-Adresse nicht versendet werden:\r\n[[EMAIL]]\r\n\r\nUm die E-Mail Adresse zu bearbeiten, klicken Sie bitte auf den folgenden Link:\r\n[[LINK]]',    'status' => 1),
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
        } catch (\Cx\Lib\UpdateException $e) {
            return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
        }
    }


    if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '3.0.1')) {
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
        } catch (\Cx\Lib\UpdateException $e) {
            return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
        }
    }


    if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '3.0.0')) {
        try {
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
        } catch (\Cx\Lib\UpdateException $e) {
            return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
        }
    }


    if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '3.0.3')) {
        try {
            \Cx\Lib\UpdateUtil::table(
                DBPREFIX.'module_newsletter_tmp_sending',
                array(
                    'id'             => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                    'newsletter'     => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'after' => 'id'),
                    'email'          => array('type' => 'VARCHAR(255)', 'after' => 'newsletter', 'notnull' => true, 'default' => ''),
                    'sendt'          => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'email'),
                    'type'           => array('type' => 'ENUM(\'access\',\'newsletter\',\'core\')', 'notnull' => true, 'default' => 'newsletter', 'after' => 'sendt'),
                    'code'           => array('type' => 'VARCHAR(10)', 'after' => 'type')
                ),
                array(
                    'unique_email'   => array('fields' => array('newsletter','email'), 'type' => 'UNIQUE'),
                    'email'          => array('fields' => array('email'))
                )
            );
        } catch (\Cx\Lib\UpdateException $e) {
            return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
        }
    }

    if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '3.0.0')) {
        try {
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
            if (newsletter_migrate_country_field() == 'timeout') {
                return 'timeout';
            }

            // IMPORTANT: the table definition statement of module_newsletter_user must be AFTER newsletter_migrate_country_field() has been called!
            // fix missing columns & rename old columns if required
            \Cx\Lib\UpdateUtil::table(
                DBPREFIX.'module_newsletter_user',
                array(
                    'id'                 => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                    'code'               => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'id'),
                    'email'              => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'code'),
                    'uri'                => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'email'),
                    'sex'                => array('type' => 'ENUM(\'m\',\'f\')', 'notnull' => false, 'after' => 'uri'),
                    'salutation'         => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'sex', 'renamefrom' => 'title'),
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
        } catch (\Cx\Lib\UpdateException $e) {
            return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
        }
    }


    try {
        // fix user's SALUTATION of previews updates
        if (   !$objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '3.0.0')
            && $objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '3.0.3'
        )) {
            // set user's SALUTATION based of previews updates
            \Cx\Lib\UpdateUtil::sql("UPDATE `".DBPREFIX."module_newsletter_user` SET `salutation` = `title`, `title` = '' WHERE `salutation` = '0' AND `title` REGEXP '^[0-9]+$'");
            
            // clear all user's TITLE attribute that consist only of a number (it is most likely not the case that a user's TITLE is a number,
            // so we assume that it is a left over of the preview update bug, which did not migrate the user's TITLE attribute to the user's SALUTATION attribute
            \Cx\Lib\UpdateUtil::sql("UPDATE `".DBPREFIX."module_newsletter_user` SET `title` = '' WHERE `title` REGEXP '^[0-9]+$'");
        }


        // switch to source mode for all newsletter content pages
        \Cx\Lib\UpdateUtil::setSourceModeOnContentPage(array('module' => 'newsletter'), '3.0.1');

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
    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }


    if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '3.0.3') && empty($_SESSION['contrexx_update']['newsletter_links_decoded'])) {
        try {
            $objResult = \Cx\Lib\UpdateUtil::sql('SELECT `id`, `url` FROM `'.DBPREFIX.'module_newsletter_email_link`');
            if ($objResult !== false && $objResult->RecordCount() > 0) {
                while (!$objResult->EOF) {
                    \Cx\Lib\UpdateUtil::sql(
                        'UPDATE `'.DBPREFIX.'module_newsletter_email_link` SET `url` = ? WHERE `id` = ?',
                        array(html_entity_decode($objResult->fields['url'], ENT_QUOTES, CONTREXX_CHARSET), $objResult->fields['id'])
                    );
                    $objResult->MoveNext();
                }
            }
            $_SESSION['contrexx_update']['newsletter_links_decoded'] = true;
        } catch (\Cx\Lib\UpdateException $e) {
            return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
        }
    }

    if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '3.0.0')) {
        // add access to access ids 152/171/172/174/175/176 for user groups which had access to access id 25
        try {
            $result = \Cx\Lib\UpdateUtil::sql("SELECT `group_id` FROM `" . DBPREFIX . "access_group_static_ids` WHERE access_id = 25 GROUP BY group_id");
            if ($result !== false) {
                while (!$result->EOF) {
                    \Cx\Lib\UpdateUtil::sql("INSERT IGNORE INTO `" . DBPREFIX . "access_group_static_ids` (`access_id`, `group_id`)
                                                VALUES
                                                (152, " . intval($result->fields['group_id']) . "),
                                                (171, " . intval($result->fields['group_id']) . "),
                                                (172, " . intval($result->fields['group_id']) . "),
                                                (174, " . intval($result->fields['group_id']) . "),
                                                (175, " . intval($result->fields['group_id']) . "),
                                                (176, " . intval($result->fields['group_id']) . ")
                                                ");
                    $result->MoveNext();
                }
            }
        } catch (\Cx\Lib\UpdateException $e) {
            return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
        }
    }

    // add access id 176 for user groups which had access to 172 if version is older than 3.1.0
    if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '3.1.0')) {
        try {
            $result = \Cx\Lib\UpdateUtil::sql("SELECT `group_id` FROM `" . DBPREFIX . "access_group_static_ids` WHERE access_id = 172 GROUP BY `group_id`");
            if ($result !== false) {
                while (!$result->EOF) {
                    \Cx\Lib\UpdateUtil::sql("INSERT IGNORE INTO `" . DBPREFIX . "access_group_static_ids` (`access_id`, `group_id`)
                                                VALUES (176, " . intval($result->fields['group_id']) . ")");
                    $result->MoveNext();
                }
            }
        } catch (\Cx\Lib\UpdateException $e) {
            return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
        }
    }

    // migrate path to images and media
    $pathsToMigrate = \Cx\Lib\UpdateUtil::getMigrationPaths();
    $attributes = array(
        'module_newsletter'                 => 'content',
        'module_newsletter_attachment'     => 'file_name',
        'module_newsletter_settings'        => 'setvalue',
        'module_newsletter_template'        => 'html',
        'module_newsletter_confirm_mail'    => 'content',
    );
    try {
        foreach ($attributes as $table => $attribute) {
            foreach ($pathsToMigrate as $oldPath => $newPath) {
                \Cx\Lib\UpdateUtil::migratePath(
                    '`' . DBPREFIX . $table . '`',
                    '`' . $attribute . '`',
                    $oldPath,
                    $newPath
                );
            }
        }
    } catch (\Cx\Lib\Update_DatabaseException $e) {
        \DBG::log($e->getMessage());
        return false;
    }

    if (   $objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '5.0.0')
        && \Cx\Lib\UpdateUtil::table_exist(DBPREFIX . 'module_newsletter_confirm_mail')
    ) {
        migrateNewsletterEmailTemplates();
    }

    return true;
}

/**
 * Migrate the existing newsletter mail templates to Emailtemplate component (\MailTemplate)
 */
function migrateNewsletterEmailTemplates()
{

    $mailTemplateArray = array(
        1 => array(
            'name' => array(
                1 => 'Anmeldung zum Newsletter',
                2 => 'Newsletter Activation',
            ),
            'subject' => array(
                1 => '[NEWSLETTER_DOMAIN_URL] - Anmeldung zum Newsletter',
                2 => '[NEWSLETTER_DOMAIN_URL] - Newsletter subscription',
            ),
            'content' => array(
                1 => '[NEWSLETTER_USER_TITLE] [NEWSLETTER_USER_LASTNAME]

Wir freuen uns,Sie bei unserem Newsletter begrüssen zu dürfen und wünschen Ihnen viel Freude damit.
Sie erhalten ab sofort wöchentlich die neuesten Informationen in elektronischer Form zu gestellt.

Um die Bestellung des Newsletters zu bestätigen,bitten wir Sie,auf den folgenden Link zu klicken bzw. ihn in die Adresszeile Ihres Browsers zu kopieren:

[NEWSLETTER_CONFIRM_CODE]

Um zu verhindern,dass unser Newsletter in Ihrem Spam-Ordner landet,fügen Sie bitte die Adresse dieser E-Mail Ihrem Adressbuch hinzu.

Sofern Sie diese E-Mail ungewünscht erhalten haben,bitten wir um Entschuldigung. Sie werden keine weitere E-Mail mehr von uns erhalten.

--
Dies ist eine automatisch generierte Nachricht.
[NEWSLETTER_CURRENT_DATE]',
                2 => '[NEWSLETTER_USER_TITLE] [NEWSLETTER_USER_LASTNAME]

We are glad to welcome you to our newsletter and wish you much enjoyment with it.
From now on you will receive the most actual news weekly.

In order to confirm the newsletter subscription please click on the following link or copy it to the address bar of your webbrowser:

[NEWSLETTER_CONFIRM_CODE]

To prevent our newsletter from ending up in your spam folder, please add this email\'s sender address to your contact list.

In case you did not request to sign up to this newsletter by yourself, please ignore this email. You may not receive any further emails from us.

--
This is an automatically generated message.
[NEWSLETTER_CURRENT_DATE]',
            ),
            'key' => 'activation_email',
            'text_id' => 24,
        ),
        2 => array(
            'name' => array(
                1 => 'Bestätigung der Anmeldung zum Newsletter',
                2 => 'Newsletter Activation',
            ),
            'subject' => array(
                1 => '[NEWSLETTER_DOMAIN_URL] - Bestätigung zur Newsletteranmeldung',
                2 => '[NEWSLETTER_DOMAIN_URL] - Newsletter subscription confirmation',
            ),
            'content' => array(
                1 => '[NEWSLETTER_USER_TITLE] [NEWSLETTER_USER_LASTNAME]

Ihr Newsletter Abonnement wurde erfolgreich registriert.
Sie werden nun in Zukunft unsere Newsletter erhalten.

--
Dies ist eine automatisch generierte Nachricht.
[NEWSLETTER_CURRENT_DATE]',
                2 => '[NEWSLETTER_USER_TITLE] [NEWSLETTER_USER_LASTNAME]

Your subscription to this newsletter was successfully registered.
You will receive our newsletter messages.

--
This is an automatically generated message.
[NEWSLETTER_CURRENT_DATE]',
            ),
            'key' => 'confirm_email',
            'text_id' => 25,
        ),
        3 => array(
            'name' => array(
                1 => 'Benachrichtigung über eine Änderung in Ihrer Newsletter-Anmeldung',
                2 => 'Newsletter Notification Email',
            ),
            'subject' => array(
                1 => '[NEWSLETTER_DOMAIN_URL] - Newsletter Empfänger [NEWSLETTER_NOTIFICATION_ACTION]',
                2 => '[NEWSLETTER_DOMAIN_URL] - Newsletter receiver [NEWSLETTER_NOTIFICATION_ACTION]',
            ),
            'content' => array(
                1 => 'Folgende Mutation wurde im Newsletter System getätigt:

Getätigte Aktion: [NEWSLETTER_NOTIFICATION_ACTION]
Geschlecht:       [NEWSLETTER_USER_SEX]
Anrede:           [NEWSLETTER_USER_TITLE]
Vorname:          [NEWSLETTER_USER_FIRSTNAME]
Nachname:         [NEWSLETTER_USER_LASTNAME]
E-Mail:           [NEWSLETTER_USER_EMAIL]

--
Dies ist eine automatisch generierte Nachricht.
[NEWSLETTER_CURRENT_DATE]',
                2 => 'The following changes were made to the newsletter system:

Action:     [NEWSLETTER_NOTIFICATION_ACTION]
Gender:     [NEWSLETTER_USER_SEX]
Salutation: [NEWSLETTER_USER_TITLE]
Firstname:  [NEWSLETTER_USER_FIRSTNAME]
Lastname:   [NEWSLETTER_USER_LASTNAME]
Email:      [NEWSLETTER_USER_EMAIL]

--
This is an automatically generated message.
[NEWSLETTER_CURRENT_DATE]',
            ),
            'key' => 'notification_email',
            'text_id' => 26,
        ),
        4 => array(
            'name' => array(
                1 => 'Benachrichtung über nicht zustellbaren Newsletter',
                2 => 'Undeliverable mail notification',
            ),
            'subject' => array(
                1 => '[NEWSLETTER_SUBJECT] - Benachrichtigung über unzustellbare E-Mail',
                2 => '[NEWSLETTER_SUBJECT] - Undeliverable email notification',
            ),
            'content' => array(
                1 => 'Der Newsletter konnte an folgende E-Mail-Adresse nicht versendet werden:
[NEWSLETTER_USER_EMAIL]

Um die E-Mail Adresse zu bearbeiten,klicken Sie bitte auf den folgenden Link:
[NEWSLETTER_USER_EDIT_LINK]

--
Dies ist eine automatisch generierte Nachricht.
[NEWSLETTER_CURRENT_DATE]',
                2 => 'Sending the newsletter to the following email address has failed:
[NEWSLETTER_USER_EMAIL]

In order to edit the email address please click the following link:
[NEWSLETTER_USER_EDIT_LINK]

--
This is an automatically generated message.
[NEWSLETTER_CURRENT_DATE]',
            ),
            'key' => 'notify_undelivered_email',
            'text_id' => 27,
        ),
    );

    $languages     = \FWLanguage::getLanguageArray();
    $defaultLangId = \FWLanguage::getDefaultLangId();

    $query = '
        SELECT
            `id`,
            `title`,
            `content` as "setvalue",
            `recipients`
        FROM
            `' . DBPREFIX . 'module_newsletter_confirm_mail`
        WHERE
            id IN (1,2,3)
        UNION
        SELECT
            4,
            "",
            `setvalue`,
            ""
        FROM
            `' . DBPREFIX . 'module_newsletter_settings` WHERE `setname` = "reject_info_mail_text"';
    $mailTemplates = \Cx\Lib\UpdateUtil::sql($query);
    if (!$mailTemplates) {
        return;
    }
    while (!$mailTemplates->EOF) {
        $mailTemplateReplacement =   isset($mailTemplateArray[$mailTemplates->fields['id']])
                                   ? $mailTemplateArray[$mailTemplates->fields['id']]
                                   : array();
        if (empty($mailTemplateReplacement)) {
            $mailTemplates->MoveNext();
            continue;
        }

        $key    = $mailTemplateReplacement['key'];
        $textId = $mailTemplateReplacement['text_id'];

        foreach ($languages as $lang) {
            $langId = $lang['id'];
            $name   =  isset($mailTemplateReplacement['name'][$langId])
                     ? $mailTemplateReplacement['name'][$langId]
                     : $mailTemplateReplacement['name'][1];
            $title = $content = '';
            if ($langId == $defaultLangId) {
                $title   = $mailTemplates->fields['title'];
                $content = $mailTemplates->fields['setvalue'];
            }
            if (empty($title)) {
                $title  =  isset($mailTemplateReplacement['subject'][$langId])
                         ? $mailTemplateReplacement['subject'][$langId]
                         : $mailTemplateReplacement['subject'][1];
            }
            if (empty($content)) {
                $content =  isset($mailTemplateReplacement['content'][$langId])
                          ? $mailTemplateReplacement['content'][$langId]
                          : $mailTemplateReplacement['content'][1];
            }
            insertMailTemplate($key, $textId, $langId, $name, $title, $content);
        }

        $mailTemplates->MoveNext();
    }

    // insert new template keys
    $newMailTemplates = array(
        array(
            'name' => array(
                1 => 'Informiert den Abonnenten über die erneute Anmeldung an den Newsletter',
                2 => 'Notify user about subscribing to the same newsletter list again',
            ),
            'subject' => array(
                1 => '[NEWSLETTER_DOMAIN_URL] - Anmeldung zum Newsletter',
                2 => '[NEWSLETTER_DOMAIN_URL] - Newsletter subscription',
            ),
            'content' => array(
                1 => '[NEWSLETTER_USER_TITLE] [NEWSLETTER_USER_LASTNAME]

Sie sind bereits Abonnent des gewählten Newsletters.

--
Dies ist eine automatisch generierte Nachricht.
[NEWSLETTER_CURRENT_DATE]',
                2 => '[NEWSLETTER_USER_TITLE] [NEWSLETTER_USER_LASTNAME]

You have already subscribed to this newsletter list.

--
This is an automatically generated message.
[NEWSLETTER_CURRENT_DATE]',
            ),
            'key' => 'notify_subscription_list_same',
            'text_id' => 28,
        ),
        array(
            'name' => array(
                1 => 'Informiert den Abonnenten über die Anmeldung an einen weiteren Newsletter',
                2 => 'Notify user about the additional newsletter list subscription',
            ),
            'subject' => array(
                1 => '[NEWSLETTER_DOMAIN_URL] - Anmeldung zum Newsletter',
                2 => '[NEWSLETTER_DOMAIN_URL] - Newsletter subscription',
            ),
            'content' => array(
                1 => '[NEWSLETTER_USER_TITLE] [NEWSLETTER_USER_LASTNAME]

Sie haben sich erfolgreich in den/die folgende(n) Newsletter eingetragen:
[[NEWSLETTER_LISTS]
   [NEWSLETTER_LIST]
[NEWSLETTER_LISTS]]

--
Dies ist eine automatisch generierte Nachricht.
[NEWSLETTER_CURRENT_DATE]',
                2 => '[NEWSLETTER_USER_TITLE] [NEWSLETTER_USER_LASTNAME]

You have successfully registered to the following newsletter list(s):
[[NEWSLETTER_LISTS]
    [NEWSLETTER_LIST]
[NEWSLETTER_LISTS]]

--
This is an automatically generated message.
[NEWSLETTER_CURRENT_DATE]',
            ),
            'key' => 'notify_subscription_list_additional',
            'text_id' => 29,
        ),
    );

    foreach ($newMailTemplates as $mailTemplateReplacement) {

        $key    = $mailTemplateReplacement['key'];
        $textId = $mailTemplateReplacement['text_id'];
        $mailTo = '[NEWSLETTER_USER_EMAIL]';

        foreach ($languages as $lang) {
            $langId = $lang['id'];
            $name   = $title = $content = '';
            if (isset($mailTemplateReplacement['name'][$langId])) {
                $name = $mailTemplateReplacement['name'][$langId];
            } else {
                $name = $mailTemplateReplacement['name'][1];
            }
            if (isset($mailTemplateReplacement['subject'][$langId])) {
                $title = $mailTemplateReplacement['subject'][$langId];
            } else {
                $title = $mailTemplateReplacement['subject'][1];
            }
            if (isset($mailTemplateReplacement['content'][$langId])) {
                $content = $mailTemplateReplacement['content'][$langId];
            } else {
                $content = $mailTemplateReplacement['content'][1];
            }
            insertMailTemplate($key, $textId, $langId, $name, $title, $content, $mailTo);
        }
    }

    try {
        \Cx\Lib\UpdateUtil::sql('DELETE FROM `'. DBPREFIX .'module_newsletter_settings` WHERE `setname` = "reject_info_mail_text"`');
        \Cx\Lib\UpdateUtil::drop_table(DBPREFIX . 'module_newsletter_confirm_mail');
    } catch (\Cx\Lib\UpdateException $e) {
        return "Error: $e->sql";
    }

}

/**
 * Insert MailTemplate into database
 *
 * @param string  $key      Mail template key
 * @param string  $textId   Core text id
 * @param integer $langId   Language id
 * @param string  $name     Mail template name
 * @param string  $title    Mail template title
 * @param string  $content  Mail template content
 */
function insertMailTemplate($key, $textId, $langId, $name, $title, $content)
{

    //replase placeholder
    $array_1 = array(
        '[[title]]',
        '[[sex]]',
        '[[firstname]]',
        '[[lastname]]',
        '[[e-mail]]',
        '[[url]]',
        '[[date]]',
        '[[code]]',
        '[[action]]',
        '[[EMAIL]]',
        '[[LINK]]',
    );
    $array_2 = array(
        '[NEWSLETTER_USER_TITLE]',
        '[NEWSLETTER_USER_SEX]',
        '[NEWSLETTER_USER_FIRSTNAME]',
        '[NEWSLETTER_USER_LASTNAME]',
        '[NEWSLETTER_USER_EMAIL]',
        '[NEWSLETTER_DOMAIN_URL]',
        '[NEWSLETTER_CURRENT_DATE]',
        '[NEWSLETTER_CONFIRM_CODE]',
        '[NEWSLETTER_NOTIFICATION_ACTION]',
        '[NEWSLETTER_USER_EMAIL]',
        '[NEWSLETTER_USER_EDIT_LINK]',
    );

    $mailTitle   = str_replace($array_1, $array_2, $title);
    $mailContent = str_replace($array_1, $array_2, $content);

    \Cx\Lib\UpdateUtil::sql('INSERT IGNORE INTO `' . DBPREFIX . 'core_mail_template` (`key`, `section`, `text_id`, `html`, `protected`) VALUES("'. $key .'", "Newsletter", '. $textId .', 0, 1)');
    \Cx\Lib\UpdateUtil::sql('INSERT INTO `' . DBPREFIX . 'core_text` (`id`, `lang_id`, `section`, `key`, `text`) VALUES('. $textId .', '. $langId .', \'Newsletter\', \'core_mail_template_bcc\', \'\')');
    \Cx\Lib\UpdateUtil::sql('INSERT INTO `' . DBPREFIX . 'core_text` (`id`, `lang_id`, `section`, `key`, `text`) VALUES('. $textId .', '. $langId .', \'Newsletter\', \'core_mail_template_cc\', \'\')');
    \Cx\Lib\UpdateUtil::sql('INSERT INTO `' . DBPREFIX . 'core_text` (`id`, `lang_id`, `section`, `key`, `text`) VALUES('. $textId .', '. $langId .', \'Newsletter\', \'core_mail_template_from\', \'\')');
    \Cx\Lib\UpdateUtil::sql('INSERT INTO `' . DBPREFIX . 'core_text` (`id`, `lang_id`, `section`, `key`, `text`) VALUES('. $textId .', '. $langId .', \'Newsletter\', \'core_mail_template_message\', "'. $mailContent .'")');
    \Cx\Lib\UpdateUtil::sql('INSERT INTO `' . DBPREFIX . 'core_text` (`id`, `lang_id`, `section`, `key`, `text`) VALUES('. $textId .', '. $langId .', \'Newsletter\', \'core_mail_template_name\', "'. $name . '")');
    \Cx\Lib\UpdateUtil::sql('INSERT INTO `' . DBPREFIX . 'core_text` (`id`, `lang_id`, `section`, `key`, `text`) VALUES('. $textId .', '. $langId .', \'Newsletter\', \'core_mail_template_reply\', \'\')');
    \Cx\Lib\UpdateUtil::sql('INSERT INTO `' . DBPREFIX . 'core_text` (`id`, `lang_id`, `section`, `key`, `text`) VALUES('. $textId .', '. $langId .', \'Newsletter\', \'core_mail_template_sender\', \'\')');
    \Cx\Lib\UpdateUtil::sql('INSERT INTO `' . DBPREFIX . 'core_text` (`id`, `lang_id`, `section`, `key`, `text`) VALUES('. $textId .', '. $langId .', \'Newsletter\', \'core_mail_template_subject\', "'. $mailTitle .'")');
    \Cx\Lib\UpdateUtil::sql('INSERT INTO `' . DBPREFIX . 'core_text` (`id`, `lang_id`, `section`, `key`, `text`) VALUES('. $textId .', '. $langId .', \'Newsletter\', \'core_mail_template_to\', \'\')');

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
        $objResult = \Cx\Lib\UpdateUtil::sql('SELECT `id`, `country_old` FROM `'.DBPREFIX.'module_newsletter_user` WHERE `country_id` = 0 AND `country_old` <> \'\'');
        if ($objResult->RecordCount()) {
            while (!$objResult->EOF) {
                // try setting country_id based on a guess from country_old
                $countryId = 0;
                $objText= \Cx\Lib\UpdateUtil::sql("SELECT `id` FROM `".DBPREFIX."core_text` WHERE `section` = 'core' AND `key` = 'core_country_name' AND `text` = '".contrexx_raw2db($objResult->fields['country_old'])."'");
                if (!$objResult->EOF) {
                    $countryId = $objText->fields['id'];
                }
                \Cx\Lib\UpdateUtil::sql('UPDATE `'.DBPREFIX.'module_newsletter_user` SET `country_id` = \''.contrexx_raw2db($countryId).'\', `country_old` = \'\' WHERE `id` = '.$objResult->fields['id']);
                if (!checkTimeoutLimit()) {
                    return 'timeout';
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
