<?php
function _blockUpdate()
{
    global $objDatabase, $_ARRAYLANG;

    $arrTables = $objDatabase->MetaTables('TABLES');
    if(count($arrTables) < 1){
        setUpdateMsg($_ARRAYLANG['TXT_UNABLE_DETERMINE_DATABASE_STRUCTURE']);
    }
    if (!in_array(DBPREFIX.'module_block_categories', $arrTables)){
        $query = "CREATE TABLE `".DBPREFIX."module_block_categories` (
                    `id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                    `parent` INT( 10 ) NOT NULL DEFAULT '0',
                    `name` VARCHAR( 255 ) NOT NULL DEFAULT '',
                    `order` INT( 10 ) NOT NULL DEFAULT '0',
                    `status` TINYINT( 1 ) NOT NULL DEFAULT '1'
                  ) TYPE=MyISAM";
        if (!$objDatabase->Execute($query)){
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    $arrColumns = $objDatabase->MetaColumnNames(DBPREFIX."module_block_blocks");
    if(count($arrColumns) < 1){
        setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX."module_block_blocks"));
    }
    if(!in_array('random_4', $arrColumns)){
        $query = "ALTER TABLE `".DBPREFIX."module_block_blocks` ADD `random_4` INT(1) NOT NULL DEFAULT '0' AFTER `random_3`";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    if(!in_array('cat', $arrColumns)){
        $query = "ALTER TABLE `".DBPREFIX."module_block_blocks` ADD `cat` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `id`";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    if(!in_array('start', $arrColumns)){
        $query = "ALTER TABLE `".DBPREFIX."module_block_blocks` ADD `start` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `name`";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    if(!in_array('end', $arrColumns)){
        $query = "ALTER TABLE `".DBPREFIX."module_block_blocks` ADD `end` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `start`";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    if(in_array('end', $arrColumns) && in_array('start', $arrColumns)){
        $now = time();
        $later = 0x7FFFFFFF; //set to max timestamp (is signed 32-bit int)
        $query = "UPDATE `".DBPREFIX."module_block_blocks`
                  SET `start` = $now, `end` = $later";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    return true;
}
?>
