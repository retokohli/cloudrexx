<?php

/**
 * This file updates an RC1 and RC2 installation to a stable installation
 * To update your installation perform the following
 * 1. Copy all your files to some backup folder
 * 2. Create a backup of your DB
 * 3. Copy all stable files to your folder
 * 4. Copy /config/configuration.php, /config/settings.php and /.htaccess back
 * 5. Copy your customized files back
 * 6. Execute this script
 */

$documentRoot = dirname(__FILE__);

require_once($documentRoot.'/lib/DBG.php');
DBG::activate(DBG_PHP);
require_once($documentRoot.'/config/settings.php');                      // needed for doctrine.php
require_once($documentRoot.'/config/configuration.php');                 // needed for doctrine.php
require_once($documentRoot.'/core/API.php');                             // needed for getDatabaseObject()
require_once($documentRoot.'/core/Env.class.php');                       // needed to get EM
require_once($documentRoot.'/core/ClassLoader/ClassLoader.class.php');
new \Cx\Core\ClassLoader\ClassLoader($documentRoot, false);
require_once($documentRoot.'/config/doctrine.php');
require_once($documentRoot.'/lib/FRAMEWORK/Language.class.php');         // needed by page repo
require_once($documentRoot.'/core/API.php');                             // needed for getDatabaseObject()
require_once($documentRoot.'/core/Init.class.php');
require_once($documentRoot.'/lib/FRAMEWORK/User/User_Setting_Mail.class.php');
require_once($documentRoot.'/lib/FRAMEWORK/User/User_Setting.class.php');
require_once($documentRoot.'/lib/FRAMEWORK/User/User_Profile_Attribute.class.php');
require_once($documentRoot.'/lib/FRAMEWORK/User/User_Profile.class.php');
require_once($documentRoot.'/lib/FRAMEWORK/User/UserGroup.class.php');
require_once($documentRoot.'/lib/FRAMEWORK/User/User.class.php');
require_once($documentRoot.'/lib/FRAMEWORK/FWUser.class.php');
require_once($documentRoot.'/core/session.class.php');

$objDatabase = getDatabaseObject($strErrMessage, true);

// note that license does not get any language vars, so it won't be able to display the fallback message
$license = \Cx\Core\License\License::getCached($_CONFIG, $objDatabase);

$objInit = new InitCMS('backend', null);

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
} else {
    die('You have already installed the stable...');
}

$updatesRc1ToStable = array(
    //rc1 to rc2
    '
        ALTER TABLE `'.DBPREFIX.'access_user_profile`
        CHANGE `interests` `interests` TEXT,
        CHANGE `signature` `signature` TEXT
    ',
    '
        INSERT INTO
            `'.DBPREFIX.'settings`
            (
                `setid`,
                `setname`,
                `setvalue`,
                `setmodule`
            )
        VALUES
            (
                103,
                \'availableComponents\',
                \'\',
                66
            )
    ',
    '
        ALTER TABLE
            `'.DBPREFIX.'modules`
        ADD
            `distributor` CHAR( 50 ) NOT NULL
        AFTER
            `name`
    ',
    '
        UPDATE
            `'.DBPREFIX.'modules`
        SET
            `distributor` = \'Comvation AG\'
    ',
    '
        ALTER TABLE `'.DBPREFIX.'module_mediadir_rel_entry_inputfields`
        ADD UNIQUE (
            `entry_id`,
            `lang_id`,
            `form_id`,
            `field_id`
        )
    ',
    '
        ALTER TABLE
            `'.DBPREFIX.'module_repository`
        DROP COLUMN
            `lang`
    ',
    '
        TRUNCATE TABLE
            `'.DBPREFIX.'module_repository`
    ',
    '
        UPDATE
            `'.DBPREFIX.'backend_areas`
        SET
            `target` = \'_blank\'
        WHERE
            `area_id` = 186
    ',
    //rc2 to stable
    '
        DELETE FROM `'.DBPREFIX.'backend_areas`
        WHERE `'.DBPREFIX.'backend_areas`.`area_id` = 179
    ',
    '
        INSERT INTO `'.DBPREFIX.'settings` (`setid`, `setname`, `setvalue`, `setmodule`)
        VALUES 
            (104, "upgradeUrl", "http://license.contrexx.com/", 66),
            (105, "isUpgradable", " ", 66),
            (106, "dashboardMessages", " ", 66)
    ',
    '
        UPDATE `'.DBPREFIX.'backend_areas` SET `order_id` = 4
        WHERE `'.DBPREFIX.'backend_areas`.`area_id` = 8;
    ',
    '
        UPDATE `'.DBPREFIX.'backend_areas` SET `order_id` = 5
        WHERE `'.DBPREFIX.'backend_areas`.`area_id` = 29;
    ',
    '
        UPDATE `'.DBPREFIX.'backend_areas` SET `order_id` = 6
        WHERE `'.DBPREFIX.'backend_areas`.`area_id` = 2;
    ',
);
$updatesRc2ToStable = array(
    '
        DELETE FROM `'.DBPREFIX.'backend_areas`
        WHERE `'.DBPREFIX.'backend_areas`.`area_id` = 179
    ',
    '
        INSERT INTO `'.DBPREFIX.'settings` (`setid`, `setname`, `setvalue`, `setmodule`)
        VALUES 
            (104, "upgradeUrl", "http://license.contrexx.com/", 66),
            (105, "isUpgradable", " ", 66),
            (106, "dashboardMessages", " ", 66)
    ',
    '
        UPDATE `'.DBPREFIX.'backend_areas` SET `order_id` = 4
        WHERE `'.DBPREFIX.'backend_areas`.`area_id` = 8;
    ',
    '
        UPDATE `'.DBPREFIX.'backend_areas` SET `order_id` = 5
        WHERE `'.DBPREFIX.'backend_areas`.`area_id` = 29;
    ',
    '
        UPDATE `'.DBPREFIX.'backend_areas` SET `order_id` = 6
        WHERE `'.DBPREFIX.'backend_areas`.`area_id` = 2;
    ',
);

$updates = ($version == 'rc1') ? $updatesRc1ToStable : $updatesRc2ToStable;

foreach ($updates as $update) {
    $result = $objDatabase->Execute($update);
    if (!$result) {
        echo $update;
        die('Update failed!');
    }
}

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
                if (substr($sqlQuery, 0, 40) != 'INSERT INTO `contrexx_module_repository`') {
                    $sqlQuery = '';
                    continue;
                }
                $sqlQuery = preg_replace('#`'.DBPREFIX.'(contrexx_module_repository)`#', '`'.DBPREFIX.'$1`', $sqlQuery);
                $result = $objDatabase->Execute($sqlQuery);
                if ($result === false) {
                    die('Update failed!');
                }
                $sqlQuery = '';
            }
        }
    }
} else {
    die('Could not read data dump file!');
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

$_GET['force'] = 'true';
$_GET['silent'] = 'true';
require_once($documentRoot.'/core_modules/License/versioncheck.php');

echo 'Update successful, you now have the stable installed!';
