<?php

/**
 * This file updates an RC1 installation to an RC2 installation
 * To update your installation perform the following
 * 1. Copy all your files to some backup folder
 * 2. Create a backup of your DB
 * 3. Copy all RC2 files to your folder
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

$objDatabase = getDatabaseObject($strErrMessage, true);

$testQuery = '
    SELECT
        `target`
    FROM
        `'.DBPREFIX.'backend_areas`
    WHERE
        `area_id` = 186
';
$result = $objDatabase->Execute($testQuery);
if (!$result) {
    die('Could not execute query!');
}
if ($result->fields['target'] == '_blank') {
    die('You do already have RC2!');
}

$updates = array(
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
);
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

echo 'Update successful, you now have RC2!';
