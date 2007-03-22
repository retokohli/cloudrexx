<?php
function _updateModuleRepository()
{
	global $objDatabase;

	$arrModuleRepositoryPages = array(
		'[[REPOSITORY]]'
	);

	$query = "TRUNCATE TABLE ".DBPREFIX."module_repository";
	if ($objDatabase->Execute($query) === false) {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	foreach ($arrModuleRepositoryPages as $page) {
		if ($objDatabase->Execute($page) === false) {
			return _databaseError($page, $objDatabase->ErrorMsg());
		}
	}

	return true;
}
?>