<?php

function _votingUpdate()
{
    global $objDatabase, $_ARRAYLANG;

    $arrColumns = $objDatabase->MetaColumns(DBPREFIX.'voting_system');
    if ($arrColumns === false) {
        setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_directory_dir'));
        return false;
    }
    $arrNewCols = array(
        'additional_nickname'  => array( 'type' => 'TINYINT(1)', 'default' => '0'),
        'additional_forename'  => array( 'type' => 'TINYINT(1)', 'default' => '0'),
        'additional_surname'   => array( 'type' => 'TINYINT(1)', 'default' => '0'),
        'additional_phone'     => array( 'type' => 'TINYINT(1)', 'default' => '0'),
        'additional_street'    => array( 'type' => 'TINYINT(1)', 'default' => '0'),
        'additional_zip'       => array( 'type' => 'TINYINT(1)', 'default' => '0'),
        'additional_city'      => array( 'type' => 'TINYINT(1)', 'default' => '0'),
        'additional_email'     => array( 'type' => 'TINYINT(1)', 'default' => '0'),
        'additional_comment'   => array( 'type' => 'TINYINT(1)', 'default' => '0'),
    );
    foreach ($arrNewCols as $col => $arrAttr) {
        if (!isset($arrColumns[strtoupper($col)])) {
            $query = "
                ALTER TABLE `".DBPREFIX."voting_system`
                    ADD       `".strtolower($col)."` ".$arrAttr['type']."
                    NOT NULL
                    DEFAULT   '".$arrAttr['default']."'";

            if ($objDatabase->Execute($query) === false) {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
    }

    $tableinfo = $objDatabase->MetaTables();
    if ($tableinfo === false) {
        setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_directory_dir'));
        return false;
    }

    if (!in_array(DBPREFIX.'voting_additionaldata', $tableinfo)) {
        $qry = "
            CREATE TABLE `".DBPREFIX."voting_additionaldata` (
                `id`               INT           NOT NULL AUTO_INCREMENT ,
                `nickname`         VARCHAR( 80 ) NOT NULL ,
                `surname`          VARCHAR( 80 ) NOT NULL ,
                `forename`         VARCHAR( 80 ) NOT NULL ,
                `phone`            VARCHAR( 80 ) NOT NULL ,
                `street`           VARCHAR( 80 ) NOT NULL ,
                `zip`              VARCHAR( 30 ) NOT NULL ,
                `city`             VARCHAR( 80 ) NOT NULL ,
                `email`            VARCHAR( 80 ) NOT NULL ,
                `voting_system_id` INT NOT NULL ,
                `date_entered`     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ,
                PRIMARY KEY(`id`) ,
                INDEX      (`voting_system_id`)
            ) ENGINE = MYISAM
        ";
        if ($objDatabase->Execute($qry) === false) {
            return _databaseError($qry, $objDatabase->ErrorMsg());
        }
    }
    else {
        // typo fix from older updates.
        $query = "
            ALTER TABLE `".DBPREFIX."voting_additionaldata`
            CHANGE   `voting_sytem_id`
            `voting_system_id` BIGINT NOT NULL DEFAULT 0
        ";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }

        // Missing column
        $query = "
            ALTER TABLE `".DBPREFIX."voting_additionaldata`
            ADD   `forename` VARCHAR (80) NOT NULL
        ";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    // comment field is new in 2.1
    $query = "
        ALTER TABLE `".DBPREFIX."voting_additionaldata`
            ADD       `comment` TEXT
            NOT NULL
            DEFAULT   ''
    ";
    if ($objDatabase->Execute($query) === false) {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }

    return true;
}

?>
