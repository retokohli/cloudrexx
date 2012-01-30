<?php
function _feedUpdate()
{
	global $objDatabase, $_ARRAYLANG;

	$query = "ALTER TABLE `".DBPREFIX."module_feed_newsml_documents` CHANGE `publicIdentifier` `publicIdentifier` VARCHAR( 255 ) NOT NULL DEFAULT ''";
	if (!$objDatabase->Execute($query)) {
    	return _databaseError($query, $objDatabase->ErrorMsg());
    }

	$arrIndexes = $objDatabase->MetaIndexes(DBPREFIX.'module_feed_newsml_documents');
	if ($arrIndexes !== false) {
		if (!isset($arrIndexes['unique'])) {
			$query = "ALTER TABLE `".DBPREFIX."module_feed_newsml_documents` ADD UNIQUE `unique` (`publicIdentifier`)";
			if (!$objDatabase->Execute($query)) {
		    	return _databaseError($query, $objDatabase->ErrorMsg());
		    }
		}
	} else {
		setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_feed_newsml_documents'));
		return false;
	}

    return true;
}
?>