<?php
function _blockUpdate()
{
    global $objDatabase, $_ARRAYLANG;

    $arrColumns = $objDatabase->MetaColumnNames(DBPREFIX."module_block_blocks");
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

    return true;
}
?>
