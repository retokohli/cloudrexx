<?php
function _aliasUpdate() {
    global $objDatabase;

    // Find out if the alias_source table is already present
    $tables = $objDatabase->MetaTables('TABLES');
    if (!$tables) {
        setUpdateMsg($_ARRAYLANG['TXT_UNABLE_DETERMINE_DATABASE_STRUCTURE']);
        return false;
    }

    // Add the isdefault column
    $qry_add_isdefault = "ALTER TABLE `".DBPREFIX."module_alias_source` ADD `isdefault` BOOL NOT NULL DEFAULT '0'";
    if (!$objDatabase->Execute($qry_add_isdefault)) {
        $msg = $objDatabase->ErrorMsg();
        if (!preg_match('/duplicate\s+column/i', $msg))
            return _databaseError($qry_add_isdefault, $msg);
    }

    // Index the isdefault column
    $qry_add_isdefault_idx = "ALTER TABLE `".DBPREFIX."module_alias_source` ADD INDEX ( `isdefault` )";
    if (!$objDatabase->Execute($qry_add_isdefault_idx)) {
        return _databaseError($qry_add_isdefault_idx, $objDatabase->ErrorMsg());
    }
    return true;
}

?>
