<?php
function _directoryUpdate() {
	global $objDatabase, $_ARRAYLANG;

	$arrColumns = $objDatabase->MetaColumns(DBPREFIX.'module_directory_dir');
	if ($arrColumns === false) {
		setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_directory_dir'));
		return false;
	}

	$arrNewCols = array(
				'LONGITUDE' => array(
						'type' 		=> 'DECIMAL( 18, 15 )',
						'default' 	=> '0',
						'after' 	=> 'premium',
					),
				'LATITUDE' 	=> array(
						'type' 		=> 'DECIMAL( 18, 15 )',
						'default' 	=> '0',
						'after' 	=> 'longitude',
					),
				'ZOOM' 		=> array(
						'type' 		=> 'DECIMAL( 18, 15 )',
						'default' 	=> '1',
						'after' 	=> 'latitude',
					),
				'COUNTRY' 	=> array(
						'type' 		=> 'VARCHAR( 255 )',
						'default' 	=> '',
						'after' 	=> 'city',
				));
	foreach ($arrNewCols as $col => $arrAttr) {
		if (!isset($arrColumns[$col])) {
			$query = "ALTER TABLE `".DBPREFIX."module_directory_dir` ADD `".strtolower($col)."` ".$arrAttr['type']." NOT NULL DEFAULT '".$arrAttr['default']."' AFTER `".$arrAttr['after']."`";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	}

	$inputColumns = '(`id`, `typ`, `name`, `title`, `active`, `active_backend`, `is_required`, `read_only`, `sort`, `exp_search`, `is_search`)';

	$arrInputs = array(
		69	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` ".$inputColumns." VALUES (69, 13, 'googlemap', 'TXT_DIR_F_GOOGLEMAP', 1, 1, 0, 0, 6, 0, 0)",
		70	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` ".$inputColumns." VALUES (70, 3, 'country', 'TXT_DIR_F_COUNTRY', 1, 1, 1, 0, 1, 0, 0)",
	);

	foreach ($arrInputs as $id => $queryInputs) {
		$query = "SELECT id FROM ".DBPREFIX."module_directory_inputfields WHERE id=".$id;
		$objCheck = $objDatabase->SelectLimit($query, 1);
		if ($objCheck !== false) {
			if ($objCheck->RecordCount() == 0) {
				if ($objDatabase->Execute($queryInputs) === false) {
					return _databaseError($queryInputs, $objDatabase->ErrorMsg());
				}
			}
		} else {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$query = "SELECT setid FROM ".DBPREFIX."module_directory_settings WHERE setname='country'";
	$objCheck = $objDatabase->SelectLimit($query, 1);
	if ($objCheck !== false) {
		if ($objCheck->RecordCount() == 0) {
			$query = 	"INSERT INTO `".DBPREFIX."module_directory_settings` ( `setid` , `setname` , `setvalue` ,  `settyp` )
						VALUES (NULL, 'country', ',Schweiz,Deutschland,Österreich', 0)";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	$query = "ALTER TABLE `".DBPREFIX."module_directory_dir` CHANGE `spez_field_21` `spez_field_21` VARCHAR( 255 ) NOT NULL DEFAULT ''";
	if ($objDatabase->Execute($query) === false) {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	$query = "ALTER TABLE `".DBPREFIX."module_directory_dir` CHANGE `spez_field_22` `spez_field_22` VARCHAR( 255 ) NOT NULL DEFAULT ''";
	if ($objDatabase->Execute($query) === false) {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}


	$query = "SELECT setid FROM ".DBPREFIX."module_directory_settings WHERE setname='pagingLimit'";
	$objCheck = $objDatabase->SelectLimit($query, 1);
	if ($objCheck !== false) {
		if ($objCheck->RecordCount() == 0) {
			$query = 	"INSERT INTO `".DBPREFIX."module_directory_settings` ( `setid` , `setname` , `setvalue` ,  `settyp` )
						VALUES (NULL, 'pagingLimit', '20', '1')";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	$query = "SELECT setid FROM ".DBPREFIX."module_directory_settings WHERE setname='googlemap_start_location'";
	$objCheck = $objDatabase->SelectLimit($query, 1);
	if ($objCheck !== false) {
		if ($objCheck->RecordCount() == 0) {
			$query = 	"INSERT INTO `".DBPREFIX."module_directory_settings` ( `setid` , `setname` , `setvalue` ,  `settyp` )
						VALUES (NULL, 'googlemap_start_location', '46:8:1', '1')";
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
