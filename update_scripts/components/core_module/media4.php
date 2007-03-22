<?php
function _media4Update()
{
	global $objDatabase;

	$query = "SELECT 1 FROM `".DBPREFIX."modules` WHERE `name` = 'media4'";
	$objModule = $objDatabase->SelectLimit($query, 1);
	if ($objModule !== false) {
		if ($objModule->RecordCount() == 0) {
			$query = "INSERT INTO `".DBPREFIX."modules` (
					`id` , `name` , `description_variable` , `status` , `is_required` , `is_core`
				) VALUES (
					'39', 'media4', 'TXT_MEDIA_MODULE_DESCRIPTION', 'y', '0', '1'
				)";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}

	} else {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	require_once ASCMS_FRAMEWORK_PATH.'/File.class.php';
	$objFile =& new File();
	if (!is_writeable(ASCMS_MEDIA4_PATH) && !$objFile->setChmod(ASCMS_MEDIA4_PATH, ASCMS_MEDIA4_WEB_PATH, '')) {
    	print "Setzen Sie die Zugriffsberechtigungen für das Verzeichnis ".ASCMS_MEDIA4_PATH."/ auf 777 (Unix) oder vergeben Sie dem Verzeichnis Schreibrechte (Windows) und laden Sie die Seite neu!";
    	return false;
    }

	return true;
}
?>