<?php
function _newsUpdate()
{
	global $objDatabase;

	$arrColumns = $objDatabase->MetaColumnNames(DBPREFIX."module_news");
	if (!is_array($arrColumns)) {
		print "Die Struktur der Datenbanktabelle ".DBPREFIX."module_news konnte nicht ermittelt werden!";
		return false;
	}

	if (!in_array('teaser_show_link', $arrColumns)) {
		$query = "ALTER TABLE `".DBPREFIX."module_news` ADD `teaser_show_link` TINYINT( 1 ) UNSIGNED DEFAULT '1' NOT NULL AFTER `teaser_text`";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$query = "ALTER TABLE `".DBPREFIX."module_news` CHANGE `teaser_frames` `teaser_frames` TEXT NOT NULL";
	if ($objDatabase->Execute($query) === false) {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	$query = "ALTER TABLE `".DBPREFIX."module_news` CHANGE `teaser_text` `teaser_text` TEXT NOT NULL";
	if ($objDatabase->Execute($query) === false) {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	$arrNewsSettings = array(
		'news_feed_image'		=> ''
	);

	foreach ($arrNewsSettings as $name => $value) {
		$query = "SELECT name FROM `".DBPREFIX."module_news_settings` WHERE `name`='".$name."'";
		if (($objSettings = $objDatabase->Execute($query)) !== false) {
			if ($objSettings->RecordCount() == 0) {
				$query = "INSERT INTO `".DBPREFIX."module_news_settings` ( `name`, `value` ) VALUES ('".$name."', '".$value."')";
				if ($objDatabase->Execute($query) === false) {
					return _databaseError($query, $objDatabase->ErrorMsg());
				}
			}
		} else {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	// create / update newsticker
	$arrTables = $objDatabase->MetaTables('TABLES');
	if ($arrTables !== false) {
		if (!in_array(DBPREFIX."module_news_ticker", $arrTables)) {
			$query = "CREATE TABLE `".DBPREFIX."module_news_ticker` (
			`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`name` VARCHAR( 255 ) NOT NULL DEFAULT '',
			`charset` ENUM( 'ISO-8859-1', 'UTF-8' ) NOT NULL DEFAULT 'ISO-8859-1',
			`urlencode` tinyint(1) unsigned NOT NULL default '0',
			`prefix` varchar(250) NOT NULL default '',
			UNIQUE ( `name` )
			) TYPE = MYISAM";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			} else {
				$tickerIsNew = true;
			}
		}
	} else {
		print 'Die Struktur der Datenbank konnte nicht ermittelt werden!';
		return false;
	}

	$query = "SELECT 1 FROM `".DBPREFIX."module_news_ticker`";
	$objResult = $objDatabase->SelectLimit($query, 1);
	if ($objResult !== false) {
		if ($objResult->RecordCount() == 0) {
			$query = "SELECT `value` FROM `".DBPREFIX."module_news_settings` WHERE `name` = 'news_ticker_filename'";
			$objResult = $objDatabase->SelectLimit($query, 1);
			if ($objResult !== false) {
				if ($objResult->RecordCount() == 1) {
					$tickerName = $objResult->fields['value'];
				} elseif ($tickerIsNew) {
					$tickerName = 'newsticker.txt';
				} else {
					$tickerName = null;
				}

				if (!empty($tickerName)) {
					$query = "INSERT INTO `".DBPREFIX."module_news_ticker` (`name`) VALUES ('".addslashes($tickerName)."')";
					if ($objDatabase->Execute($query) === false) {
						return _databaseError($query, $objDatabase->ErrorMsg());
					}
				}
			} else {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	$query = "DELETE FROM `".DBPREFIX."module_news_settings` WHERE `name` = 'news_ticker_filename'";
	if ($objDatabase->Execute($query) === false) {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	// change placeholder TXT_NEWS_CONTENT to TXT_NEWS_NEWS_CONTENT
	foreach (array('content', 'content_history', 'module_repository') as $table) {
		$query = "SELECT `id`, `content` FROM ".DBPREFIX.$table." WHERE `content` LIKE '%%'";
		$objContent = $objDatabase->Execute($query);
		if (!$objContent) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}

		while (!$objContent->EOF) {
			$query = "UPDATE ".DBPREFIX.$table." SET `content`='".addslashes(str_replace('', '', $objContent->fields['content']))."' WHERE `id`=".$objContent->fields['id'];
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
			$objContent->MoveNext();
		}
	}

	$query = "SELECT `name`, `value` FROM `".DBPREFIX."module_news_settings` WHERE `name` = 'news_ticker_filename' AND `value` = 'newsticker.txt'";
	$objResult = $objDatabase->SelectLimit($query, 1);
	if ($objResult !== false) {
		if ($objResult->RecordCount() == 0){
			$query = "INSERT INTO `".DBPREFIX."module_news_settings` (`name`, `value`) VALUES ('news_ticker_filename', 'newsticker.txt')";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	$arrColumns = $objDatabase->MetaColumnNames(DBPREFIX."module_news");
	if (!is_array($arrColumns)) {
		print "Die Struktur der Datenbanktabelle ".DBPREFIX."module_news konnte nicht ermittelt werden!";
		return false;
	}

	if (!in_array('redirect', $arrColumns)) {
		$query = "ALTER TABLE `".DBPREFIX."module_news` ADD `redirect` VARCHAR( 250 ) NOT NULL AFTER `text`";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	return true;
}
?>