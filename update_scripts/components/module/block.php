<?php
function _blockUpdate()
{
	global $objDatabase;

	$arrTables = $objDatabase->MetaTables('TABLES');
	if ($arrTables !== false) {
		if (!in_array(DBPREFIX."module_block_blocks", $arrTables)) {
			$query = "CREATE TABLE `".DBPREFIX."module_block_blocks` (
				`id` int(10) unsigned NOT NULL auto_increment,
				`content` text NOT NULL,
				`name` varchar(255) NOT NULL default '',
				`random` int(1) NOT NULL default '0',
				`active` int(1) NOT NULL default '0',
				PRIMARY KEY  (`id`)
				) TYPE=MyISAM";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		if (!in_array(DBPREFIX."module_block_rel_lang", $arrTables)) {
			$query = "CREATE TABLE ".DBPREFIX."module_block_rel_lang (`block_id` int(10) unsigned NOT NULL default '0', `lang_id` int(10) unsigned NOT NULL default '0') TYPE=MyISAM";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		print 'Die Struktur der Datenbank konnte nicht ermittelt werden!';
		return false;
	}

	$objFWLanguage = &new FWLanguage();
	$arrLanguages = &$objFWLanguage->getLanguageArray();

	$query = "SELECT id FROM ".DBPREFIX."module_block_blocks";
	if (($objBlock = $objDatabase->Execute($query)) !== false) {
		while (!$objBlock->EOF) {
			foreach ($arrLanguages as $arrLanguage) {
				$query = "SELECT block_id FROM ".DBPREFIX."module_block_rel_lang WHERE lang_id=".$arrLanguage['id']." AND block_id=".$objBlock->fields['id'];
				if (($objLang = $objDatabase->SelectLimit($query, 1)) !== false) {
					if ($objLang->RecordCount() == 0) {
						$query = "INSERT INTO ".DBPREFIX."module_block_rel_lang (`block_id`, `lang_id`) VALUES (".$objBlock->fields['id'].", ".$arrLanguage['id'].")";
						if ($objDatabase->Execute($query) === false) {
							return _databaseError($query, $objDatabase->ErrorMsg());
						}
					}
				} else {
					return _databaseError($query, $objDatabase->ErrorMsg());
				}
			}
			$objBlock->MoveNext();
		}
	} else {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	if (!in_array(DBPREFIX.'module_block_rel_pages', $arrTables)) {
		$query = "CREATE TABLE `".DBPREFIX."module_block_rel_pages` (
				`block_id` INT( 7 ) NOT NULL,
				`page_id` INT( 7 ) NOT NULL,
				`lang_id` INT( 7 ) NOT NULL
				) TYPE = MYISAM";

		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$arrColumns = $objDatabase->MetaColumnNames(DBPREFIX."module_block_rel_lang");
	if (!is_array($arrColumns)) {
		print "Die Struktur der Datenbanktabelle ".DBPREFIX."module_block_rel_lang konnte nicht ermittelt werden!";
		return false;
	}

	if (!in_array('all_pages', $arrColumns)) {
		$query = "ALTER TABLE `".DBPREFIX."module_block_rel_lang` ADD `all_pages` INT( 1 ) NOT NULL";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$arrColumns = $objDatabase->MetaColumnNames(DBPREFIX."module_block_blocks");
	if (!is_array($arrColumns)) {
		print "Die Struktur der Datenbanktabelle ".DBPREFIX."module_block_blocks konnte nicht ermittelt werden!";
		return false;
	}

	if (!in_array('global', $arrColumns)) {
		$query = "ALTER TABLE `".DBPREFIX."module_block_blocks` ADD `global` INT( 1 ) NOT NULL AFTER `random`";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (!in_array('order', $arrColumns)) {
		$query = "ALTER TABLE `".DBPREFIX."module_block_blocks` ADD `order` INT( 1 ) NOT NULL";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (!in_array(DBPREFIX.'module_block_settings', $arrTables)) {
		$query = "CREATE TABLE `".DBPREFIX."module_block_settings` (
					`id` INT( 7 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
					`name` VARCHAR( 100 ) NOT NULL ,
					`value` VARCHAR( 100 ) NOT NULL
					) TYPE = MYISAM";

		if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$query = "SELECT id FROM ".DBPREFIX."module_block_settings WHERE name = 'blockGlobalSeperator'";
	$objResult = $objDatabase->SelectLimit($query, 1);
	if ($objResult !== false) {
		if ($objResult->RecordCount() == 0) {
			$query = "INSERT INTO `".DBPREFIX."module_block_settings` VALUES (1, 'blockGlobalSeperator', '<br /><br />');";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	return true;
}
?>