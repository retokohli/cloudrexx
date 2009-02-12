
<?php
function _galleryUpdate()
{
	global $objDatabase, $_ARRAYLANG;

	$arrColumns = $objDatabase->MetaColumns(DBPREFIX.'module_gallery_categories');
	if ($arrColumns === false) {
		setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_gallery_categories'));
		return false;
	}

	if (!isset($arrColumns['BACKENDPROTECTED'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_gallery_categories` ADD `backendProtected` INT NOT NULL ;" ;
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (!isset($arrColumns['BACKEND_ACCESS_ID'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_gallery_categories` ADD `backend_access_id` INT NOT NULL ;" ;
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (!isset($arrColumns['FRONTENDPROTECTED'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_gallery_categories` ADD `frontendProtected` INT NOT NULL ;" ;
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (!isset($arrColumns['FRONTEND_ACCESS_ID'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_gallery_categories` ADD `frontend_access_id` INT NOT NULL ;" ;
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

    return true;
}
?>