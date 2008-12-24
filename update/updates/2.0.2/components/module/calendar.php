<?php
function _calendarUpdate()
{
	global $objDatabase, $_ARRAYLANG;

	$query = "UPDATE `".DBPREFIX."module_calendar_access` SET `type` = 'frontend' WHERE `name` = 'showNote'";
	if ($objDatabase->Execute($query) === false) {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

    return true;
}
?>