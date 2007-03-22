<?php
function _createLivecamModule()
	{
		global $objDatabase;

		if (!in_array(DBPREFIX."module_livecam_settings", $this->_arrDbTables)) {
			$query = "CREATE TABLE ".DBPREFIX."module_livecam_settings (`setid` INT UNSIGNED NOT NULL AUTO_INCREMENT ,`setname` VARCHAR( 255 ) NOT NULL ,`setvalue` TEXT NOT NULL ,PRIMARY KEY ( `setid` ) )";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		$arrLivecamOptions = array(
			'currentImageUrl'	=> "INSERT INTO ".DBPREFIX."module_livecam_settings ( `setid` , `setname` , `setvalue` ) VALUES ('', 'currentImageUrl', 'http://heimenschwand.ch/webcam/current.jpg'), ('', 'archivePath', '/webcam')",
			'thumbnailPath'		=> "INSERT INTO ".DBPREFIX."module_livecam_settings ( `setid` , `setname` , `setvalue` ) VALUES ('', 'thumbnailPath', '/webcam/thumbs')"
		);
		foreach ($arrLivecamOptions as $name => $insertQuery) {
			$query = "SELECT setid FROM ".DBPREFIX."module_livecam_settings WHERE setname='".$name."'";
			$objResult = $objDatabase->Execute($query);
			if ($objResult !== false) {
				if ($objResult->RecordCount() == 0) {
					if ($objDatabase->Execute($insertQuery) === false) {
						return $this->_databaseError($insertQuery, $objDatabase->ErrorMsg());
					}
				}
			} else {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}
		return true;
	}
?>