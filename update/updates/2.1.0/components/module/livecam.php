<?php

function _livecamUpdate()
{
    global $objDatabase, $_ARRAYLANG;

	$arrColumns = $objDatabase->MetaColumns(DBPREFIX.'module_livecam');
	if ($arrColumns === false) {
		setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_livecam'));
		return false;
	}

	if (!isset($arrColumns['SHOWFROM'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_livecam` ADD `showFrom` INT(14) NOT NULL ;" ;
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (!isset($arrColumns['SHOWTILL'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_livecam` ADD `showTill` INT(14) NOT NULL ;" ;
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$defaultFrom = mktime(0, 0);
	$defaultTill = mktime(23, 59);
		//set new default settings
	$query = "UPDATE `".DBPREFIX."module_livecam` SET `showFrom`=$defaultFrom, `showTill`=$defaultTill WHERE 1";
	if ($objDatabase->Execute($query) === false) {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

    return true;
}
?>
