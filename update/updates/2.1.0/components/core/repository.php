<?php
function _updateModuleRepository_%MODULE_ID%()
{
	global $objDatabase;

	$arrModuleRepositoryPages = array(/*REPOSITORY_ARRAY*/);

	$query = "DELETE FROM ".DBPREFIX."module_repository WHERE `moduleid`=%MODULE_ID%";
	if ($objDatabase->Execute($query) === false) {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	$arrPageIds = array();
	foreach ($arrModuleRepositoryPages as $arrPage) {
		$arrPage['query'] = str_replace('[[PKG_MODULE_REPOSITORY_PAGE_PARID]]', array_search($arrPage['parid'], $arrPageIds), $arrPage['query']);

		if ($objDatabase->Execute($arrPage['query']) === false) {
			return _databaseError($arrPage['query'], $objDatabase->ErrorMsg());
		} else {
			$arrPageIds[$objDatabase->Insert_ID()] = $arrPage['id'];
		}
	}

    return true;
}
?>