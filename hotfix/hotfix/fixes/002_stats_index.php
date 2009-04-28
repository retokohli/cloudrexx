<?php

function check_stats_index() {
	global $objDatabase;

    $key_qry = "
        SHOW INDEX 
        FROM `".DBPREFIX."stats_visitors` 
        WHERE `Key_name`   = 'sid'
        AND   `Non_unique` = 0
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

	$sql = "CREATE INDEX `sid` ON `".DBPREFIX."stats_visitors`(`sid`);";
	$res = $objDatabase->Execute($sql);

    return fix_status($res !== false, "SQL failed: $sql");
}

