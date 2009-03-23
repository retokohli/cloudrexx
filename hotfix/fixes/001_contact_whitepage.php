<?php

/**
 * Returns true if the whitepage problem must be fixed.
 * returns false if the problem is already fixed.
 */
function check_contact_whitepage() {
	global $objDatabase;
	$tableinfo = $objDatabase->MetaTables();
	return in_array(DBPREFIX.'module_contact_recipient', $tableinfo)
		? NO_FIX_NEEDED
		: NEED_FIX;
}

function fix_contact_whitepage() {
	global $objDatabase;
	$sql = "
		CREATE TABLE `".DBPREFIX."module_contact_recipient` (
			`id`       INT(11)       NOT NULL AUTO_INCREMENT,
			`id_form`  INT(11)       NOT NULL DEFAULT '0',
			`name`     VARCHAR(250)  NOT NULL DEFAULT '',
			`email`    VARCHAR(250)  NOT NULL DEFAULT '',
			`sort`     INT(11)       NOT NULL DEFAULT '0',
			PRIMARY KEY  (`id`)
		)
	";
	$res = $objDatabase->Execute($sql);

	return fix_status($res !== false, "SQL failed: $sql");
}

