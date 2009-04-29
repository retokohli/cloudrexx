<?php

function check_stats_index() {
	global $objDatabase;

    $key_qry = "
        SHOW INDEX
        FROM `".DBPREFIX."stats_visitors`
        WHERE `Key_name`   = 'sid'
    ";
    $keyinfo = $objDatabase->Execute($key_qry);
    if ($keyinfo->RecordCount() == 1) {
		return NEED_FIX;
    }
    return NO_FIX_NEEDED;
}

function fix_stats_index() {
	global $objDatabase;
	$sql = "ALTER TABLE `".DBPREFIX."stats_visitors` DROP KEY `sid`;";
	$res = $objDatabase->Execute($sql);
    if ($res === false) {
        return fix_status(false, "SQL failed: $sql");
    }

	$sql = "ALTER IGNORE TABLE `".DBPREFIX."stats_visitors` ADD UNIQUE `unique` (`sid`);";
	$res = $objDatabase->Execute($sql);

    return fix_status($res !== false, "SQL failed: $sql");
}

