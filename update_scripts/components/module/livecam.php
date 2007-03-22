<?php
function _livecamUpdate()
{
	global $objDatabase;

	$arrTables = $objDatabase->MetaTables('TABLES');
	if ($arrTables === false) {
		print "Die Struktur der Datenbank konnte nicht ermittelt werden!";
		return false;
	}

	if (!in_array(DBPREFIX."module_livecam_settings", $arrTables)) {
		$query = "CREATE TABLE `".DBPREFIX."module_livecam_settings` (
			`setid` int(10) unsigned NOT NULL auto_increment,
			`setname` varchar(255) NOT NULL default '',
			`setvalue` text NOT NULL,
			PRIMARY KEY  (`setid`)
			) TYPE=MyISAM";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$arrSettings = array(
		'currentImageUrl'	=> 'http://heimenschwand.ch/webcam/current.jpg',
		'archivePath'		=> '/webcam',
		'thumbnailPath'		=> '/webcam/thumbs'
	);

	foreach ($arrSettings as $setname => $setvalue) {
		$query = "SELECT setid FROM ".DBPREFIX."module_livecam_settings WHERE setname='".$setname."'";
		$objResult = $objDatabase->SelectLimit($query, 1);
		if ($objResult) {
			if ($objResult->RecordCount() == 0) {
				$query = "INSERT INTO `".DBPREFIX."module_livecam_settings` ( `setname` , `setvalue` ) VALUES ('".$setname."', '".$setvalue."')";
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