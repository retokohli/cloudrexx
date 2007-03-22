<?php
function _memberdirUpdate()
{
	global $objDatabase;

	$arrTables = $objDatabase->MetaTables('TABLES');
	if ($arrTables === false) {
		print "Die Struktur der Datenbank konnte nicht ermittelt werden!";
		return false;
	}

	// create settings table
	if (!in_array(DBPREFIX."module_memberdir_settings", $arrTables)) {
		$query = "CREATE TABLE `".DBPREFIX."module_memberdir_settings` ( `setid` int(4) unsigned NOT NULL auto_increment, `setname` varchar(255) NOT NULL default '', `setvalue` text NOT NULL, `lang_id` tinyint(2) NOT NULL default '1', PRIMARY KEY  (`setid`) )";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$arrColumns = $objDatabase->MetaColumnNames(DBPREFIX."module_memberdir_settings");
	if (!is_array($arrColumns)) {
		print "Die Struktur der Datenbanktabelle ".DBPREFIX."module_memberdir_settings konnte nicht ermittelt werden!";
		return false;
	}

	if (!in_array("lang_id", $arrColumns)) {
		$query = "ALTER TABLE `".DBPREFIX."module_memberdir_settings` ADD `lang_id` TINYINT( 2 ) DEFAULT '1' NOT NULL AFTER `setvalue`";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	// insert settings
	/*$objFWLanguage = &new FWLanguage();
	$arrLanguages = &$objFWLanguage->getLanguageArray();
	if (!is_array($arrLanguages) || count($arrLanguages) == 0) {
		print "Konnte die vorhandenen Sprachen im System nicht ausfindig machen!";
		return false;
	}*/

	$arrMemberdirSettings = array(
		'default_listing'	=> '1',
		'max_height'		=> '400',
		'max_width'			=> '500'
	);

	//foreach ($arrLanguages as $arrLanguage) {
		foreach ($arrMemberdirSettings as $setname => $setvalue) {
			$query = "SELECT setid FROM `".DBPREFIX."module_memberdir_settings` WHERE `setname`='".$setname."'";
			if (($objSettings = $objDatabase->SelectLimit($query, 1)) !== false) {
				if ($objSettings->RecordCount() == 0) {
					$query = "INSERT INTO `".DBPREFIX."module_memberdir_settings` (`setname`, `setvalue`, `lang_id`) VALUES ('".$setname."', '".$setvalue."', 1)";
					if ($objDatabase->Execute($query) === false) {
						return _databaseError($query, $objDatabase->ErrorMsg());
					}
				}
			} else {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	//}

	if (!in_array(DBPREFIX."module_memberdir_directories", $arrTables)) {
		$query = "CREATE TABLE `".DBPREFIX."module_memberdir_directories` (  `dirid` int(10) unsigned NOT NULL auto_increment,  `parentdir` int(11) NOT NULL default '0',  `active` set('1','0') NOT NULL default '1',  `name` varchar(255) NOT NULL default '',  `description` text NOT NULL,  `displaymode` set('0','1','2') NOT NULL default '0',  `sort` int(11) NOT NULL default '1',  `pic1` set('1','0') NOT NULL default '0',  `pic2` set('1','0') NOT NULL default '0',  `lang_id` tinyint(2) NOT NULL default '1',  PRIMARY KEY  (`dirid`))";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$arrColumns = $objDatabase->MetaColumnNames(DBPREFIX."module_memberdir_directories");
	if (!is_array($arrColumns)) {
		print "Die Struktur der Datenbanktabelle ".DBPREFIX."module_memberdir_directories konnte nicht ermittelt werden!";
		return false;
	}

	if (!in_array("parentdir", $arrColumns)) {
		$query = "ALTER TABLE `".DBPREFIX."module_memberdir_directories` ADD `parentdir` INT( 11 ) DEFAULT '0' NOT NULL AFTER `dirid`";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (!in_array("displaymode", $arrColumns)) {
		$query = "ALTER TABLE `".DBPREFIX."module_memberdir_directories` ADD `displaymode` SET( '0', '1', '2' ) DEFAULT '0' NOT NULL AFTER `description`";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (!in_array("sort", $arrColumns)) {
		$query = "ALTER TABLE `".DBPREFIX."module_memberdir_directories` ADD `sort` INT( 11 ) DEFAULT '1' NOT NULL AFTER `displaymode`";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (!in_array("pic1", $arrColumns)) {
		$query = "ALTER TABLE `".DBPREFIX."module_memberdir_directories` ADD `pic1` SET( '1', '0' ) DEFAULT '0' NOT NULL AFTER `sort`";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (!in_array("pic2", $arrColumns)) {
		$query = "ALTER TABLE `".DBPREFIX."module_memberdir_directories` ADD `pic2` SET( '1', '0' ) DEFAULT '0' NOT NULL AFTER `pic1`";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (!in_array("lang_id", $arrColumns)) {
		$query = "ALTER TABLE `".DBPREFIX."module_memberdir_directories` ADD `lang_id` TINYINT( 2 ) DEFAULT '1' NOT NULL AFTER `pic2`";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (!in_array(DBPREFIX."module_memberdir_name", $arrTables)) {
		$query = "CREATE TABLE `".DBPREFIX."module_memberdir_name` (  `field` int(10) unsigned NOT NULL default '0',  `dirid` int(10) unsigned NOT NULL default '0',  `name` varchar(255) NOT NULL default '',  `active` set('0','1') NOT NULL default '',  `lang_id` tinyint(2) NOT NULL default '1')";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$arrColumns = $objDatabase->MetaColumnNames(DBPREFIX."module_memberdir_name");
	if (!is_array($arrColumns)) {
		print "Die Struktur der Datenbanktabelle ".DBPREFIX."module_memberdir_name konnte nicht ermittelt werden!";
		return false;
	}

	if (!in_array("lang_id", $arrColumns)) {
		$query = "ALTER TABLE `".DBPREFIX."module_memberdir_name` ADD `lang_id` TINYINT( 2 ) DEFAULT '1' NOT NULL AFTER `active`";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (!in_array(DBPREFIX."module_memberdir_values", $arrTables)) {
		$query = "CREATE TABLE `".DBPREFIX."module_memberdir_values` (  `id` int(10) unsigned NOT NULL auto_increment,  `dirid` int(14) NOT NULL default '0',  `pic1` varchar(255) NOT NULL default '',  `pic2` varchar(255) NOT NULL default '',  `1` text NOT NULL,  `2` text NOT NULL,  `3` text NOT NULL,  `4` text NOT NULL,  `5` text NOT NULL,  `6` text NOT NULL,  `7` text NOT NULL,  `8` text NOT NULL,  `9` text NOT NULL,  `10` text NOT NULL,  `11` text NOT NULL,  `12` text NOT NULL,  `13` text NOT NULL,  `14` text NOT NULL,  `15` text NOT NULL,  `16` text NOT NULL,  `17` text NOT NULL,  `18` text NOT NULL,  `lang_id` tinyint(2) NOT NULL default '1',  PRIMARY KEY  (`id`)) TYPE=MyISAM";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$arrColumns = $objDatabase->MetaColumnNames(DBPREFIX."module_memberdir_values");
	if (!is_array($arrColumns)) {
		print "Die Struktur der Datenbanktabelle ".DBPREFIX."module_memberdir_values konnte nicht ermittelt werden!";
		return false;
	}

	if (!in_array("pic1", $arrColumns)) {
		$query = "ALTER TABLE `".DBPREFIX."module_memberdir_values` ADD `pic1` VARCHAR(255) NOT NULL AFTER `dirid`";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (!in_array("pic2", $arrColumns)) {
		$query = "ALTER TABLE `".DBPREFIX."module_memberdir_values` ADD `pic2` VARCHAR(255) NOT NULL AFTER `pic1`";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (!in_array("17", $arrColumns)) {
		$query = "ALTER TABLE `".DBPREFIX."module_memberdir_values` ADD `17` TEXT NOT NULL AFTER `16`";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (!in_array("18", $arrColumns)) {
		$query = "ALTER TABLE `".DBPREFIX."module_memberdir_values` ADD `18` TEXT NOT NULL AFTER `17`";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (!in_array("lang_id", $arrColumns)) {
		$query = "ALTER TABLE `".DBPREFIX."module_memberdir_values` ADD `lang_id` TINYINT( 2 ) DEFAULT '1' NOT NULL AFTER `18`";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (!in_array("0", $arrColumns)) {
		$query = "ALTER TABLE `".DBPREFIX."module_memberdir_values` ADD `0` SMALLINT UNSIGNED NOT NULL DEFAULT '0' AFTER `pic2`";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$query = "UPDATE `".DBPREFIX."module_memberdir_values` SET `pic1` = 'none' WHERE CHAR_LENGTH(`pic1`) = 0";
	if ($objDatabase->Execute($query) === false) {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	$query = "UPDATE `".DBPREFIX."module_memberdir_values` SET `pic2` = 'none'  WHERE CHAR_LENGTH(`pic2`) = 0";
	if ($objDatabase->Execute($query) === false) {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	$arrIndexes = $objDatabase->MetaIndexes(DBPREFIX."module_memberdir_directories");
	if ($arrIndexes === false) {
		print "Die Struktur der Datenbanktabelle ".DBPREFIX."module_memberdir_directories konnte nicht ermittelt werden!";
		return false;
	}

	if (!isset($arrIndexes['memberdir_dir'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_memberdir_directories` ADD FULLTEXT `memberdir_dir` ( `name` , `description` )";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	require_once ASCMS_FRAMEWORK_PATH.'/File.class.php';
	$objFile =& new File();
	if (is_writeable(ASCMS_MEDIA_PATH.'/memberdir') || $objFile->setChmod(ASCMS_MEDIA_PATH, ASCMS_MEDIA_WEB_PATH, '/memberdir')) {
    	if ($mediaDir = @opendir(ASCMS_MEDIA_PATH.'/memberdir')) {
    		while($file = readdir($mediaDir)) {
    			if ($file != '.' && $file != '..') {
    				if (!is_writeable(ASCMS_MEDIA_PATH.'/memberdir/'.$file) && !$objFile->setChmod(ASCMS_MEDIA_PATH.'/memberdir/', ASCMS_MEDIA_WEB_PATH.'/memberdir/', $file)) {
    					print "Setzen Sie die Zugriffsberechtigungen für die Datei ".ASCMS_MEDIA_PATH."/memberdir/".$file." auf 777 (Unix) oder vergeben Sie auf diese Datei Schreibrechte (Windows) und laden Sie die Seite neu!";
    					return false;
    				}
    			}
			}
    	} else {
    		print "Setzen Sie die Zugriffsberechtigungen für das Verzeichnis ".ASCMS_MEDIA_PATH."/memberdir/ und dessen Inhalt auf 777 (Unix) oder vergeben Sie dem Verzeichnis und dessen Inhalt Schreibrechte (Windows) und laden Sie die Seite neu!";
    		return false;
		}
    } else {
    	print "Setzen Sie die Zugriffsberechtigungen für das Verzeichnis ".ASCMS_MEDIA_PATH."/memberdir/ und dessen Inhalt auf 777 (Unix) oder vergeben Sie dem Verzeichnis und dessen Inhalt Schreibrechte (Windows) und laden Sie die Seite neu!";
    	return false;
    }

	return true;
}
?>