<?php
function _bannerUpdate() {
	global $objDatabase;

	$query = "ALTER TABLE `".DBPREFIX."module_banner_relations` CHANGE `type` `type` SET( 'content', 'news', 'teaser', 'level' ) NOT NULL DEFAULT 'content'";
	if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
	}

	$query = "SELECT `name`, `value` FROM `".DBPREFIX."module_banner_settings` WHERE `name` = 'level_banner' AND `value` = '1'";
	$objResult = $objDatabase->SelectLimit($query, 1);
	if ($objResult !== false) {
		if ($objResult->RecordCount() == 0){
			$query = "INSERT INTO `".DBPREFIX."module_banner_settings` ( `name` , `value` ) VALUES ('level_banner', '1')";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	$arrColumns = $objDatabase->MetaColumns(DBPREFIX.'module_banner_system');
	if ($arrColumns === false) {
		print "Die Struktur der Tabelle '".DBPREFIX."module_banner_system' konnte nicht ermittelt werden!";
		return false;
	}

	if (empty($arrColumns['VIEWS'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_banner_system` ADD `views` INT( 100 ) NOT NULL";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (empty($arrColumns['CLICKS'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_banner_system` ADD `clicks` INT( 100 ) NOT NULL";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	return true;
}
?>