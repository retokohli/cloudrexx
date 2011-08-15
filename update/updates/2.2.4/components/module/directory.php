<?php
function _directoryUpdate() {
	global $objDatabase, $_ARRAYLANG;

	/// 2.0

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


	/// 2.1

	$query = "SELECT setid FROM ".DBPREFIX."module_directory_settings WHERE setname='youtubeWidth'";
	$objCheck = $objDatabase->SelectLimit($query, 1);
	if ($objCheck !== false) {
		if ($objCheck->RecordCount() == 0) {
			$query = 	"INSERT INTO `".DBPREFIX."module_directory_settings` ( `setid` , `setname` , `setvalue` ,  `settyp` )
						 VALUES (NULL , 'youtubeWidth', '400', '1')";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	$query = "SELECT setid FROM ".DBPREFIX."module_directory_settings WHERE setname='youtubeHeight'";
	$objCheck = $objDatabase->SelectLimit($query, 1);
	if ($objCheck !== false) {
		if ($objCheck->RecordCount() == 0) {
			$query = 	"INSERT INTO `".DBPREFIX."module_directory_settings` ( `setid` , `setname` , `setvalue` ,  `settyp` )
						 VALUES (NULL , 'youtubeHeight', '300', '1')";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	$query = "SELECT id FROM ".DBPREFIX."module_directory_inputfields WHERE name='youtube'";
	$objCheck = $objDatabase->SelectLimit($query, 1);
	if ($objCheck !== false) {
		if ($objCheck->RecordCount() == 0) {
			$query = 	"INSERT INTO `".DBPREFIX."module_directory_inputfields` (`id` ,`typ` ,`name` ,`title` ,`active` ,`active_backend` ,`is_required` ,`read_only` ,`sort` ,`exp_search` ,`is_search`)
						 VALUES (NULL , '1', 'youtube', 'TXT_DIRECTORY_YOUTUBE', '0', '0', '0', '0', '0', '0', '0')";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	$arrColumns = $objDatabase->MetaColumns(DBPREFIX.'module_directory_dir');
	if ($arrColumns === false) {
		setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_directory_dir'));
		return false;
	}

	if (!array_key_exists("YOUTUBE", $arrColumns)) {
	    $query = "ALTER TABLE `".DBPREFIX."module_directory_dir` ADD `youtube` MEDIUMTEXT NOT NULL;";
	    if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$query   = "ALTER TABLE `".DBPREFIX."module_directory_dir`
                CHANGE `logo` `logo` VARCHAR(50) NULL,
                CHANGE `map` `map` VARCHAR(255) NULL,
                CHANGE `lokal` `lokal` VARCHAR(255) NULL,
                CHANGE `spez_field_11` `spez_field_11` VARCHAR(255) NULL,
                CHANGE `spez_field_12` `spez_field_12` VARCHAR(255) NULL,
                CHANGE `spez_field_13` `spez_field_13` VARCHAR(255) NULL,
                CHANGE `spez_field_14` `spez_field_14` VARCHAR(255) NULL,
                CHANGE `spez_field_15` `spez_field_15` VARCHAR(255) NULL,
                CHANGE `spez_field_16` `spez_field_16` VARCHAR(255) NULL,
                CHANGE `spez_field_17` `spez_field_17` VARCHAR(255) NULL,
                CHANGE `spez_field_18` `spez_field_18` VARCHAR(255) NULL,
                CHANGE `spez_field_19` `spez_field_19` VARCHAR(255) NULL,
                CHANGE `spez_field_20` `spez_field_20` VARCHAR(255) NULL;";
    if ($objDatabase->Execute($query) === false) {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}


    try{
        // delete obsolete table  contrexx_module_directory_access
        UpdateUtil::drop_table(DBPREFIX.'module_directory_access');
    }
    catch (UpdateException $e) {
        // we COULD do something else here..
        DBG::trace();
        return UpdateUtil::DefaultActionHandler($e);
    }


    return true;
}
?>
