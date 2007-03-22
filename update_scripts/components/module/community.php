<?php
function _communityUpdate()
{
	global $objDatabase;

	$arrTables = $objDatabase->MetaTables('TABLES');
	if ($arrTables !== false) {
		if (!in_array(DBPREFIX."community_config", $arrTables)) {
			$query = "CREATE TABLE `".DBPREFIX."community_config` (
				`id` int(11) NOT NULL auto_increment,
				`name` varchar(64) NOT NULL default '',
				`value` varchar(255) NOT NULL default '',
				`status` int(1) default '1',
				PRIMARY KEY  (`id`)
				) TYPE=MyISAM";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		print 'Die Struktur der Datenbank konnte nicht ermittelt werden!';
		return false;
	}

	$arrSettings = array(
		array(
			'name'		=> 'community_groups',
			'value'		=> '3',
			'status'	=> 1
		),
		array(
			'name'		=> 'user_activation',
			'value'		=> '',
			'status'	=> 0
		),
		array(
			'name'		=> 'user_activation_timeout',
			'value'		=> '1',
			'status'	=> 0
		)
	);

	foreach ($arrSettings as $arrSetting) {
		$query = "SELECT id FROM `".DBPREFIX."community_config` WHERE name='".$arrSetting['name']."'";
		$objSetting = $objDatabase->SelectLimit($query, 1);
		if ($objSetting) {
			if ($objSetting->RecordCount() == 0) {
				$query = "INSERT INTO `".DBPREFIX."community_config` VALUES (NULL, '".$arrSetting['name']."', '".$arrSetting['value']."', ".$arrSetting['status'].")";
				if ($objDatabase->Execute($query) === false) {
					return _databaseError($query, $objDatabase->ErrorMsg());
				}
			}
		} else {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	return true;
}
?>