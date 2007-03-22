<?php
function _updateModules()
{
	global $objDatabase;

	$arrModules = array(
	'[[MODULES]]'
	);

	$query = "TRUNCATE TABLE ".DBPREFIX."modules";
	if ($objDatabase->Execute($query) === false) {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	// add modules
	foreach ($arrModules as $arrModule) {
		$query = "INSERT INTO ".DBPREFIX."modules ( `id` , `name` , `description_variable` , `status` , `is_required` , `is_core` ) VALUES ( ".$arrModule['id']." , '".$arrModule['name']."', '".$arrModule['description_variable']."', '".$arrModule['status']."', '".$arrModule['is_required']."', '".$arrModule['is_core']."')";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	return true;
}
?>