<?php

/**
 * This file updates a RC1 or RC2 installation to a stable installation
 * To update your installation perform the following:
 * 1. Copy all your files to a backup folder
 * 2. Create a backup of your database
 * 3. Copy all stable files to your folder
 * 4. Copy /config/configuration.php, /config/settings.php and /.htaccess back
 * 5. Copy your customized files back
 * 6. Execute this script
 */

$documentRoot = ASCMS_PATH . ASCMS_PATH_OFFSET;

$objResultRc1 = $objDatabase->Execute('SELECT `target` FROM `'.DBPREFIX.'backend_areas` WHERE `area_id` = 186');
$objResultRc2 = $objDatabase->Execute('SELECT `order_id` FROM `'.DBPREFIX.'backend_areas` WHERE `area_id` = 2');
if (!$objResultRc1 || !$objResultRc2) {
    die('ERROR: Could not execute query.');
}
if ($objResultRc1->fields['target'] != '_blank') {
    $version = 'rc1';
} elseif ($objResultRc2->fields['order_id'] != 6) {
    $version = 'rc2';
} elseif ($_CONFIG['coreCmsVersion'] == '3.0.0') {
    $version = 'stable';
} elseif ($_CONFIG['coreCmsVersion'] == '3.0.0.1') {
    $version = 'hotfix';
} else {
    // nothing to do
    return true;
}


$objResult = $objDatabase->Execute('SELECT 1 FROM `'.DBPREFIX.'module_filesharing_mail_template` WHERE (`id` = 1 OR `id` = 2)');
if (!$objResult) {
    die('ERROR: Could not execute query.');
}
if ($objResult->RecordCount() == 0) {
    $rc1UpdateFilesharing[] = '
        INSERT INTO `'.DBPREFIX.'module_filesharing_mail_template` (`id`, `lang_id`, `subject`, `content`) VALUES (1, 1, "Jemand teilt eine Datei mit Ihnen", "Guten Tag,\r\n\r\nJemand hat auf [[DOMAIN]] eine Datei mit Ihnen geteilt.\r\n\r\n<!-- BEGIN filesharing_file -->\r\nDownload-Link: [[FILE_DOWNLOAD]]\r\n<!-- END filesharing_file -->\r\n\r\nDie Person hat eine Nachricht hinterlassen:\r\n[[MESSAGE]]\r\n\r\nFreundliche Grüsse");
    ';
    $rc1UpdateFilesharing[] = '
        INSERT INTO `'.DBPREFIX.'module_filesharing_mail_template` (`id`, `lang_id`, `subject`, `content`) VALUES (2, 2, "Somebody is sharing a file with you", "Hi,\r\n\r\nSomebody shared a file with you on [[DOMAIN]].\r\n\r\n<!-- BEGIN filesharing_file -->\r\nDownload link: [[FILE_DOWNLOAD]]\r\n<!-- END filesharing_file -->\r\n\r\nThe person has left a message for you:\r\n[[MESSAGE]]\r\n\r\nBest regards");
    ';
} else {
    $rc1UpdateFilesharing = null;
}

$updatesRc1ToRc2 = array(
    '
        ALTER TABLE `'.DBPREFIX.'access_user_profile`
        CHANGE `interests` `interests` TEXT,
        CHANGE `signature` `signature` TEXT
    ',
    '
        UPDATE `'.DBPREFIX.'access_user_profile`
        SET  `interests` = NULL , `signature` = NULL
        WHERE (`interests` = "" AND `signature` = "");
    ',
    '
        INSERT INTO `'.DBPREFIX.'settings` (`setid`, `setname`, `setvalue`, `setmodule`)
        VALUES (103, "availableComponents", "", 66)
    ',
    '
        ALTER TABLE `'.DBPREFIX.'modules`
        ADD `distributor` CHAR( 50 ) NOT NULL
        AFTER `name`
    ',
    '
        UPDATE `'.DBPREFIX.'modules`
        SET `distributor` = "Comvation AG"
    ',
    '
        CREATE TABLE IF NOT EXISTS `'.DBPREFIX.'module_mediadir_rel_entry_inputfields_clean` (
          `id` int(11) NOT NULL auto_increment,
          `entry_id` int(7) NOT NULL,
          `lang_id` int(7) NOT NULL,
          `form_id` int(7) NOT NULL,
          `field_id` int(7) NOT NULL,
          `value` longtext collate utf8_unicode_ci NOT NULL,
          PRIMARY KEY  (`id`),
          FULLTEXT KEY `value` (`value`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
    ',
    '
        INSERT INTO `'.DBPREFIX.'module_mediadir_rel_entry_inputfields_clean`
        SELECT NULL, `entry_id`, `lang_id`, `form_id`, `field_id`, `value`
        FROM `'.DBPREFIX.'module_mediadir_rel_entry_inputfields`
        GROUP BY `entry_id`, `form_id`, `field_id`, `lang_id`, `value`
    ',
    '
        TRUNCATE `'.DBPREFIX.'module_mediadir_rel_entry_inputfields`
    ',
    '
        ALTER TABLE `'.DBPREFIX.'module_mediadir_rel_entry_inputfields`
        ADD UNIQUE (`entry_id`, `lang_id`, `form_id`, `field_id`)
    ',
    '
        INSERT IGNORE INTO `'.DBPREFIX.'module_mediadir_rel_entry_inputfields`
        SELECT `entry_id`, `lang_id`, `form_id`, `field_id`, `value`
        FROM `'.DBPREFIX.'module_mediadir_rel_entry_inputfields_clean`
        ORDER BY `id` DESC
    ',
    '
        DROP TABLE `'.DBPREFIX.'module_mediadir_rel_entry_inputfields_clean`
    ',
    '
        ALTER TABLE `'.DBPREFIX.'module_repository`
        DROP COLUMN `lang`
    ',
    '
        TRUNCATE TABLE `'.DBPREFIX.'module_repository`
    ',
    '
        UPDATE `'.DBPREFIX.'backend_areas`
        SET `target` = "_blank"
        WHERE `area_id` = 186
    ',
);
if (!empty($rc1UpdateFilesharing)) {
    $updatesRc1ToRc2 = array_merge($updatesRc1ToRc2, $rc1UpdateFilesharing);
}

$updatesRc2ToStable = array(
    '
        UPDATE `'.DBPREFIX.'core_setting`
        SET `value` = "off"
        WHERE (`section` = "filesharing" AND `name` = "permission" AND `group` = "config")
    ',
    '
        INSERT INTO `'.DBPREFIX.'core_text` (`id`, `lang_id`, `section`, `key`, `text`)
        VALUES  (4, 1, "shop", "currency_name", "Euro"),
                (5, 1, "shop", "currency_name", "United States Dollars")
    ',
    '
        INSERT INTO `'.DBPREFIX.'settings` (`setid`, `setname`, `setvalue`, `setmodule`)
        VALUES  (104, "upgradeUrl", "http://license.contrexx.com/", 66),
                (105, "isUpgradable", "on", 66),
                (106, "dashboardMessages", "", 66)
    ',
    '
        DELETE FROM `'.DBPREFIX.'backend_areas`
        WHERE `'.DBPREFIX.'backend_areas`.`area_id` = 179
    ',
    '
        UPDATE `'.DBPREFIX.'backend_areas`
        SET `order_id` = 4
        WHERE `'.DBPREFIX.'backend_areas`.`area_id` = 8
    ',
    '
        UPDATE `'.DBPREFIX.'backend_areas`
        SET `order_id` = 5
        WHERE `'.DBPREFIX.'backend_areas`.`area_id` = 29
    ',
    '
        UPDATE `'.DBPREFIX.'backend_areas`
        SET `order_id` = 6
        WHERE `'.DBPREFIX.'backend_areas`.`area_id` = 2
    ',
    '
        TRUNCATE TABLE `'.DBPREFIX.'module_repository`
    ',
);
$updatesStableToHotfix = array(
    'UPDATE `'.DBPREFIX.'settings` SET `setvalue` = \'3.0.0.1\' WHERE `setname` = \'coreCmsVersion\'',
    'UPDATE `'.DBPREFIX.'content_page` SET `customContent` = \'\' WHERE `customContent` = \'(Default)\'',
);
$updateHotfixToSp = array(
    'ALTER TABLE `contrexx_module_block_rel_lang_content` ADD UNIQUE `id_lang` ( `block_id` , `lang_id` )',
    'ALTER TABLE contrexx_module_contact_form ADD   `use_email_of_sender` tinyint(1) NOT NULL DEFAULT \'0\' AFTER `send_copy`',
    'ALTER TABLE contrexx_module_newsletter_access_user ADD `code` varchar(255) NOT NULL DEFAULT \'\' AFTER newsletterCategoryID',
    'UPDATE contrexx_module_newsletter_access_user SET `code` = SUBSTR(MD5(RAND()),1,12) WHERE `code` = \'\'',
);
$updatesRc1ToSp = array_merge($updatesRc1ToRc2, $updatesRc2ToStable, $updatesStableToHotfix, $updatesHotfixToSp);
$updatesRc2ToSp = array_merge($updatesRc2ToStable, $updatesStableToHotfix, $updatesHotfixToSp);
$updatesStableToSp = array_merge($updatesStableToHotfix, $updatesHotfixToSp);


if ($version == 'rc1') {
    $updates = $updatesRc1ToSp;
} else if ($version == 'rc2') {
    $updates = $updatesRc2ToSp;
} else if ($version == 'stable') {
    $updates = $updatesStableToSp;
} else {
    $updates = $updatesHotfixToSp;
}
foreach ($updates as $update) {
    $result = $objDatabase->Execute($update);
    if (!$result) {
        setUpdateMsg('Update failed: ' . $update);
        return false;
    }
}

if ($version != 'stable' && $version != 'hotfix') {
    // reimport module repository
    $sqlQuery = '';
    $fp = @fopen($documentRoot.'/installer/data/contrexx_dump_data.sql', 'r');
    if ($fp !== false) {
        while (!feof($fp)) {
            $buffer = fgets($fp);
            if ((substr($buffer,0,1) != '#') && (substr($buffer,0,2) != '--')) {
                $sqlQuery .= $buffer;
                if (preg_match('/;[ \t\r\n]*$/', $buffer)) {
                    $sqlQuery = preg_replace('/SET FOREIGN_KEY_CHECKS = 0;/', '', $sqlQuery);
                    if (substr(trim($sqlQuery), 0, 40) != 'INSERT INTO `contrexx_module_repository`') {
                        $sqlQuery = '';
                        continue;
                    }
                    $sqlQuery = preg_replace('#`contrexx_module_repository`#', '`'.DBPREFIX.'module_repository`', $sqlQuery);
                    $result = $objDatabase->Execute($sqlQuery);
                    if ($result === false) {
                        setUpdateMsg('Update failed: ' . $sqlQuery);
                        return false;
                    }
                    $sqlQuery = '';
                }
            }
        }
    } else {
        die('Could not read data dump file!');
    }
}

if ($version == 'rc1') {
    $em = \Env::em();
    $pageRepo = $em->getRepository('Cx\Model\ContentManager\Page');

    $fallbackPages = $pageRepo->findBy(array(
        'type' => \Cx\Model\ContentManager\Page::TYPE_FALLBACK,
    ));

    foreach ($fallbackPages as $page) {
        $page->setModule($page->getModule());
        $page->setCmd($page->getCmd());
        $em->persist($page);
    }
    $em->flush();
}

$objSettings = new \settingsManager();
$objSettings->writeSettingsFile();
require($documentRoot.'/config/settings.php');
