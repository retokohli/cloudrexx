<?php
function _galleryUpdate()
{
	global $objDatabase;

	$arrColumns = $objDatabase->MetaColumnNames(DBPREFIX."module_gallery_pictures");
	if (!is_array($arrColumns)) {
		print "Die Struktur der Datenbanktabelle ".DBPREFIX."module_gallery_pictures konnte nicht ermittelt werden!";
		return false;
	}

	if (!in_array('catimg', $arrColumns)) {
		$query = "ALTER TABLE `".DBPREFIX."module_gallery_pictures` ADD `catimg` SET( '0', '1' ) DEFAULT '0' NOT NULL AFTER `status`";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$arrIndexes = $objDatabase->MetaIndexes(DBPREFIX."module_gallery_language");
	if ($arrIndexes !== false) {
		if (!isset($arrIndexes['galleryindex'])) {
			$query = "ALTER TABLE `".DBPREFIX."module_gallery_language` ADD FULLTEXT `galleryindex` (`value`)";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}

	} else {
		print "Die Struktur der Datenbanktabelle ".DBPREFIX."module_gallery_language konnte nicht ermittelt werden!";
		return false;
	}

	$arrIndexes = $objDatabase->MetaIndexes(DBPREFIX."module_gallery_language_pics");
	if ($arrIndexes !== false) {
		if (!isset($arrIndexes['galleryindex'])) {
			$query = "ALTER TABLE `".DBPREFIX."module_gallery_language_pics` ADD FULLTEXT `galleryindex` (`name` ,`desc`)";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}

	} else {
		print "Die Struktur der Datenbanktabelle ".DBPREFIX."module_gallery_language_pics konnte nicht ermittelt werden!";
		return false;
	}

	$arrGallerySettings = array(
		'paging'		=> '30',
		'show_latest'	=> 'on',
		'show_random'	=> 'on',
        // Added by Reto
        'header_type'   => 'hierarchy',
        'show_ext'      => 'on',
	);
	foreach ($arrGallerySettings as $name => $value) {
		$query = "SELECT id FROM `".DBPREFIX."module_gallery_settings` WHERE `name`='".$name."'";
		if (($objSettings = $objDatabase->Execute($query)) !== false) {
			if ($objSettings->RecordCount() == 0) {
				$query = "INSERT INTO `".DBPREFIX."module_gallery_settings` ( `id` , `name` , `value` ) VALUES ('', '".$name."', '".$value."')";
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