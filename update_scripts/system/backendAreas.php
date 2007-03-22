<?php
function _updateBackendAreas()
{
	global $objDatabase;

	$arrBackendAreas = array(
'[[BACKEND_AREAS]]'
	);

	$objDatabase->Execute("TRUNCATE TABLE ".DBPREFIX."backend_areas");

	// add backend areas
	foreach ($arrBackendAreas as $arrBackendArea) {
		$query = "INSERT INTO ".DBPREFIX."backend_areas (`area_id`, `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id`
			) VALUES (
			".$arrBackendArea['area_id'].", '".$arrBackendArea['parent_area_id']."', '".$arrBackendArea['type']."', '".$arrBackendArea['area_name']."', '".$arrBackendArea['is_active']."', '".$arrBackendArea['uri']."', '".$arrBackendArea['target']."', '".$arrBackendArea['module_id']."', '".$arrBackendArea['order_id']."', '".$arrBackendArea['access_id']."')";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	return true;
}
?>