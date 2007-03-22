<?php
function _feedUpdate()
{
	global $objDatabase;

	$arrColumns = $objDatabase->MetaColumnNames(DBPREFIX."module_feed_newsml_documents");
	if (!is_array($arrColumns)) {
		print "Die Struktur der Datenbanktabelle ".DBPREFIX."module_feed_newsml_documents konnte nicht ermittelt werden!";
		return false;
	}

	if (!in_array('is_associated', $arrColumns)) {
		$query = "ALTER TABLE `".DBPREFIX."module_feed_newsml_documents` ADD `is_associated` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `dataContent`";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	} else {
		$query = "ALTER TABLE `".DBPREFIX."module_feed_newsml_documents` CHANGE `is_associated` `is_associated` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0'";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (!in_array('media_type', $arrColumns)) {
		$query = "ALTER TABLE `".DBPREFIX."module_feed_newsml_documents` ADD `media_type` ENUM( 'Text', 'Graphic', 'Photo', 'Audio', 'Video', 'ComplexData' ) NOT NULL DEFAULT 'Text' AFTER `is_associated`";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (!in_array('properties', $arrColumns)) {
		$query = "ALTER TABLE `".DBPREFIX."module_feed_newsml_documents` ADD `properties` TEXT NOT NULL AFTER `media_type`";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (!in_array('source', $arrColumns)) {
		$query = "ALTER TABLE `".DBPREFIX."module_feed_newsml_documents` ADD `source` TEXT NOT NULL AFTER `media_type`";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$arrTables = $objDatabase->MetaTables('TABLES');
	if (!$arrTables) {
		print "Die Struktur der Datenbank konnte nicht ermittelt werden!";
		return false;
	}

	if (!in_array(DBPREFIX."module_feed_newsml_association", $arrTables)) {
		$query = "CREATE TABLE `".DBPREFIX."module_feed_newsml_association` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,`pId_master` TEXT NOT NULL ,`pId_slave` TEXT NOT NULL) TYPE = MYISAM";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (in_array(DBPREFIX."module_feed_newsml_content_item", $arrTables)) {
		$query = "DROP TABLE `".DBPREFIX."module_feed_newsml_content_item`";
		if ($objDatabase->Execute($query) === false) {
			_databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$arrColumns = $objDatabase->MetaColumnNames(DBPREFIX."module_feed_newsml_categories");
	if (!is_array($arrColumns)) {
		print "Die Struktur der Datenbanktabelle ".DBPREFIX."module_feed_newsml_categories konnte nicht ermittelt werden!";
		return false;
	}

	if (!in_array('showPics', $arrColumns)) {
		$query = "ALTER TABLE `".DBPREFIX."module_feed_newsml_categories` ADD `showPics` ENUM( '0', '1' ) NOT NULL DEFAULT '1' AFTER `limit`";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	return true;
}
?>