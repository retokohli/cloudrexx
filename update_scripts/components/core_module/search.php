<?php
function _searchUpdate()
{
	global $objDatabase;

	$arrIndexes=$objDatabase->MetaIndexes(DBPREFIX.'module_directory_dir');

	if (empty($arrIndexes['title'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_directory_dir` ADD FULLTEXT (`title` ,`description`);";

		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	} elseif (!empty($arrIndexes['title'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_directory_dir` DROP INDEX `title`;";

		if ($objDatabase->Execute($query) !== false) {
			$query = "ALTER TABLE `".DBPREFIX."module_directory_dir` ADD FULLTEXT (`title` ,`description`);";

			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		} else {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	return true;
}
?>