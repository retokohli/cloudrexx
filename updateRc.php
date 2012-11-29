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

$documentRoot = dirname(__FILE__);
require_once($documentRoot.'/lib/DBG.php');
DBG::deactivate();
require_once($documentRoot.'/config/settings.php');                      // needed for doctrine.php
require_once($documentRoot.'/config/configuration.php');                 // needed for doctrine.php
require_once($documentRoot.'/core/Env.class.php');                       // needed to get EM
require_once($documentRoot.'/core/ClassLoader/ClassLoader.class.php');
$cl = new \Cx\Core\ClassLoader\ClassLoader($documentRoot, true);
\Env::set('ClassLoader', $cl);
require_once($documentRoot.'/config/doctrine.php');
require_once($documentRoot.'/lib/FRAMEWORK/Language.class.php');         // needed by page repo
require_once($documentRoot.'/core/API.php');                             // needed for getDatabaseObject()
require_once($documentRoot.'/core/Init.class.php');
require_once($documentRoot.'/core/settings.class.php');
require_once($documentRoot.'/lib/FRAMEWORK/User/User_Setting_Mail.class.php');
require_once($documentRoot.'/lib/FRAMEWORK/User/User_Setting.class.php');
require_once($documentRoot.'/lib/FRAMEWORK/User/User_Profile_Attribute.class.php');
require_once($documentRoot.'/lib/FRAMEWORK/User/User_Profile.class.php');
require_once($documentRoot.'/lib/FRAMEWORK/User/UserGroup.class.php');
require_once($documentRoot.'/lib/FRAMEWORK/User/User.class.php');
require_once($documentRoot.'/lib/FRAMEWORK/FWUser.class.php');
require_once($documentRoot.'/core/session.class.php');

$objDatabase = getDatabaseObject($strErrMessage, true);
$objInit = new InitCMS('frontend', null);
$_CORELANG = $objInit->loadLanguageData('core');

$license = \Cx\Core_Modules\License\License::getCached($_CONFIG, $objDatabase, $_CORELANG);

if (!isset($sessionObj) || !is_object($sessionObj)) $sessionObj = new \cmsSession();

$objUser = \FWUser::getFWUserObject()->objUser;
if (!$objUser->login(true) || !$objUser->getAdminStatus()) {
    // do not use die() here, if we use this script as in include it won't work
    echo '<a href="' . ASCMS_ADMIN_WEB_PATH . '/">You must log in as admin before you can update the database. Click here to log in...</a>';
    return;
}

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
} else {
    die('You have already installed the current stable...');
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
$updatesRc1ToHotfix = array_merge($updatesRc1ToRc2, $updatesRc2ToStable, $updatesStableToHotfix);
$updatesRc2ToHotfix = array_merge($updatesRc2ToStable, $updatesStableToHotfix);

if ($version == 'rc1') {
    $updates = $updatesRc1ToHotfix;
} else if ($version == 'rc2') {
    $updates = $updatesRc2ToHotfix;
} else {
    $updates = $updatesStableToHotfix;
}
foreach ($updates as $update) {
    $result = $objDatabase->Execute($update);
    if (!$result) {
        echo $update;
        die('Update failed!');
    }
}

if ($version != 'stable') {
    // reimport module repository
    $sqlQuery = '';
    $fp = @fopen ($documentRoot.'/installer/data/contrexx_dump_data.sql', 'r');
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
                        echo $sqlQuery;
                        die('Update failed!');
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

$_GET['force'] = 'true';
$_GET['silent'] = 'true';
require_once($documentRoot.'/core_modules/License/versioncheck.php');

echo 'Update successful. Now you have installed the current stable.';
