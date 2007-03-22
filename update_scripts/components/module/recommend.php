<?php
function _recommendUpdate()
{
	global $objDatabase;

	$arrTables = $objDatabase->MetaTables('TABLES');
	if ($arrTables !== false) {
		if (!in_array(DBPREFIX."module_recommend", $arrTables)) {
			$query = "CREATE TABLE `".DBPREFIX."module_recommend` (
				`id` int(10) unsigned NOT NULL auto_increment,
				`name` varchar(255) NOT NULL default '',
				`value` text NOT NULL,
				`lang_id` int(11) NOT NULL default '1',
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

	$arrRecommend = array(
		'body'				=> '<SALUTATION> <RECEIVER_NAME>\r\n\r\nFolgende Seite wurde ihnen von <SENDER_NAME> (<SENDER_MAIL>) empfohlen:\r\n\r\n<URL>\r\n\r\nAnmerkung von <SENDER_NAME>:\r\n\r\n<COMMENT>',
		'subject'			=> 'Seitenempfehlung von <SENDER_NAME>',
		'salutation_female'	=> 'Liebe',
		'salutation_male'	=> 'Lieber'
	);

	$objFWLanguage = &new FWLanguage();
	$arrLanguages = &$objFWLanguage->getLanguageArray();

	foreach ($arrLanguages as $arrLanguage) {
		foreach ($arrRecommend as $name => $value) {
			$query = "SELECT id FROM `".DBPREFIX."module_recommend` WHERE name='".$name."' AND lang_id=".$arrLanguage['id'];
			$objRecommend = $objDatabase->SelectLimit($query, 1);
			if ($objRecommend) {
				if ($objRecommend->RecordCount() == 0) {
					$query = "INSERT INTO `".DBPREFIX."module_recommend` VALUES (NULL, '".$name."', '".$value."', ".$arrLanguage['id'].")";
					if ($objDatabase->Execute($query) === false) {
						return _databaseError($query, $objDatabase->ErrorMsg());
					}
				}
			} else {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	}

	return true;
}
?>