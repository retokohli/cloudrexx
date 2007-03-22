<?php
function _marketUpdate()
{
	global $objDatabase;

	//create acces table
	$arrTables = $objDatabase->MetaTables('TABLES');
	if ($arrTables === false) {
		print "Die Struktur der Datenbank konnte nicht ermittelt werden!";
		return false;
	}

	if (!in_array(DBPREFIX."module_market_access", $arrTables)) {
		$query = "CREATE TABLE `".DBPREFIX."module_market_access` (
				  `id` int(11) unsigned NOT NULL auto_increment,
				  `name` varchar(64) NOT NULL default '',
				  `description` varchar(255) NOT NULL default '',
				  `access_id` int(11) unsigned NOT NULL default '0',
				  `type` enum('global','frontend','backend') NOT NULL default 'global',
				  PRIMARY KEY  (`id`)
				) TYPE=MyISAM";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$query = "SELECT id FROM ".DBPREFIX."module_market_access WHERE id='1'";
	$objCheck = $objDatabase->SelectLimit($query, 1);
	if ($objCheck) {
		if ($objCheck->RecordCount() == 0) {
			$query = "INSERT INTO `".DBPREFIX."module_market_access` VALUES (1, 'add_entry', 'Inserat hinzufügen', 99, 'frontend')";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	$query = "SELECT id FROM ".DBPREFIX."module_market_access WHERE id='2'";
	$objCheck = $objDatabase->SelectLimit($query, 1);
	if ($objCheck) {
		if ($objCheck->RecordCount() == 0) {
			$query = "INSERT INTO `".DBPREFIX."module_market_access` VALUES (2, 'edit_entry', 'Inserate bearbeiten', 100, 'frontend')";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	$query = "SELECT id FROM ".DBPREFIX."module_market_access WHERE id='3'";
	$objCheck = $objDatabase->SelectLimit($query, 1);
	if ($objCheck) {
		if ($objCheck->RecordCount() == 0) {
			$query = "INSERT INTO `".DBPREFIX."module_market_access` VALUES (3, 'del_entry', 'Inserate löschen', 101, 'frontend')";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	//create catecories table
	if (!in_array(DBPREFIX.'module_market_categories', $arrTables)) {
		$query = "CREATE TABLE `".DBPREFIX."module_market_categories` (
				  `id` int(6) NOT NULL auto_increment,
				  `name` varchar(100) NOT NULL default '',
				  `description` varchar(255) NOT NULL default '',
				  `displayorder` int(4) NOT NULL default '0',
				  `status` int(1) NOT NULL default '0',
				  PRIMARY KEY  (`id`)
				) TYPE=MyISAM";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$query = "SELECT id FROM ".DBPREFIX."module_market_categories WHERE id='1'";
	$objCheck = $objDatabase->SelectLimit($query, 1);
	if ($objCheck) {
		if ($objCheck->RecordCount() == 0) {
			$query = "INSERT INTO `".DBPREFIX."module_market_categories` VALUES (1, 'Demo Kategorie', 'Demo Kategorie', 0, 1)";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	//create directory table
	if (!in_array(DBPREFIX.'module_market', $arrTables)) {
		$query = "CREATE TABLE `".DBPREFIX."module_market` (
				  `id` int(9) NOT NULL auto_increment,
				  `name` varchar(100) NOT NULL default '',
				  `email` varchar(100) NOT NULL default '',
				  `type` set('search','offer') NOT NULL default '',
				  `title` varchar(255) NOT NULL default '',
				  `description` mediumtext NOT NULL,
				  `premium` int(1) NOT NULL default '0',
				  `picture` varchar(255) NOT NULL default '',
				  `catid` int(4) NOT NULL default '0',
				  `price` varchar(10) NOT NULL default '',
				  `regdate` varchar(20) NOT NULL default '',
				  `enddate` varchar(20) NOT NULL default '',
				  `userid` int(4) NOT NULL default '0',
				  `userdetails` int(1) NOT NULL default '0',
				  `status` int(1) NOT NULL default '0',
				  `regkey` varchar(50) NOT NULL default '',
				  `paypal` int(1) NOT NULL default '0',
				  PRIMARY KEY  (`id`),
				  FULLTEXT KEY `description` (`description`),
				  FULLTEXT KEY `title` (`description`,`title`)
				) TYPE=MyISAM";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$query = "SELECT id FROM ".DBPREFIX."module_market WHERE id='1'";
	$objCheck = $objDatabase->SelectLimit($query, 1);
	if ($objCheck) {
		if ($objCheck->RecordCount() == 0) {
			$query = "INSERT INTO `".DBPREFIX."module_market` VALUES (1, 'Testeintrag', 'noreply@noreply.com', 'offer', '...einfach sorgenfrei mit Contrexx', 'Beschreibung', 0, '7d32904e2ce683f9de52bcddf798efd0.gif', 1, '10.00', '1151877600', '1153087200', 1, 1, 1, '', 0)";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	//create mail table
	if (!in_array(DBPREFIX.'module_market_mail', $arrTables)) {
		$query = "CREATE TABLE `".DBPREFIX."module_market_mail` (
				  `id` tinyint(4) NOT NULL auto_increment,
				  `title` varchar(255) NOT NULL default '',
				  `content` longtext NOT NULL,
				  `mailcc` mediumtext NOT NULL,
				  `active` int(1) NOT NULL default '0',
				  PRIMARY KEY  (`id`)
				) TYPE=MyISAM";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$query = "SELECT id FROM ".DBPREFIX."module_market_mail WHERE id='1'";
	$objCheck = $objDatabase->SelectLimit($query, 1);
	if ($objCheck) {
		if ($objCheck->RecordCount() == 0) {
			$query = "INSERT INTO `".DBPREFIX."module_market_mail` VALUES (1, 'Ihr Contrexx.com-Inserat mit dem Titel [[TITLE]] wurde freigeschaltet', 'Lieber Inserent\r\n\r\nBesten Dank für Ihre Geduld. Um eine hohe Qualität auf Contrexx.com garantieren zu können, haben wir Ihr Inserat geprüft. \r\n\r\nIhr Inserat mit dem Titel «[[TITLE]]» und der ID \"[[ID]]\" wurde von unseren Mitarbeiterinnen und Mitarbeitern geprüft und freigeschaltet.\r\n\r\nIhr Inserat ist ab sofort unter [[URL]] einsehbar.\r\n\r\nSie können Ihr Inserat jederzeit unter [[LINK]] gratis ändern oder löschen.\r\n\r\nHoffentlich bis bald wieder auf Contrexx.com und mit freundlichen Grüssen\r\n\r\nIhr Contrexx.com Team\r\n\r\nAutomatisch generierte Nachricht\r\n[[DATE]]\r\n\r\n\r\nhttp://www.contrexx.com/\r\nContrexx.com - Der Schweizer Marktplatz', '', 1)";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	$query = "SELECT id FROM ".DBPREFIX."module_market_mail WHERE id='2'";
	$objCheck = $objDatabase->SelectLimit($query, 1);
	if ($objCheck) {
		if ($objCheck->RecordCount() == 0) {
			$query = "INSERT INTO `".DBPREFIX."module_market_mail` VALUES (2, 'Neues Inserat auf [[URL]] - ID: [[ID]]', 'Hallo Admin\r\n\r\nAuf [[URL]] wurde ein neues Inserat eingetragen.\r\n\r\nID:          [[ID]]\r\nTitel:       [[TITLE]]\r\nCode:        [[CODE]]\r\nUsername:    [[USERNAME]]\r\nName:        [[NAME]]\r\nE-Mail:      [[EMAIL]]\r\n\r\nAutomatisch generierte Nachricht\r\n[[DATE]]\r\n', '', 1)";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	//create paypal table
	if (!in_array(DBPREFIX.'module_market_paypal', $arrTables)) {
		$query = "CREATE TABLE `".DBPREFIX."module_market_paypal` (
				  `id` int(4) NOT NULL auto_increment,
				  `active` int(1) NOT NULL default '0',
				  `profile` varchar(255) NOT NULL default '',
				  `price` varchar(10) NOT NULL default '',
				  `price_premium` varchar(10) NOT NULL default '',
				  PRIMARY KEY  (`id`)
				) TYPE=MyISAM";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$query = "SELECT id FROM ".DBPREFIX."module_market_paypal WHERE id='1'";
	$objCheck = $objDatabase->SelectLimit($query, 1);
	if ($objCheck) {
		if ($objCheck->RecordCount() == 0) {
			$query = "INSERT INTO `".DBPREFIX."module_market_paypal` VALUES (1, 0, '', '5.00', '2.00')";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	//create settings table
	if (!in_array(DBPREFIX.'module_market_settings', $arrTables)) {
		$query = "CREATE TABLE `".DBPREFIX."module_market_settings` (
				  `id` int(6) NOT NULL auto_increment,
				  `name` varchar(100) NOT NULL default '',
				  `value` varchar(255) NOT NULL default '',
				  `description` varchar(255) NOT NULL default '',
				  `type` int(1) NOT NULL default '0',
				  PRIMARY KEY  (`id`)
				) TYPE=MyISAM";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$arrSettings = array(
		1	=>	"INSERT INTO `".DBPREFIX."module_market_settings` VALUES (1, 'maxday', '14', 'max. Anzeigedauer (Tage)', 1)",
		2	=>	"INSERT INTO `".DBPREFIX."module_market_settings` VALUES (2, 'description', '0', 'Kategoriebeschreibung anzeigen', 2)",
		3	=>	"INSERT INTO `".DBPREFIX."module_market_settings` VALUES (3, 'paging', '10', 'Anzahl Inserate pro Seite', 1)",
		4	=>	"INSERT INTO `".DBPREFIX."module_market_settings` VALUES (4, 'currency', 'CHF', 'Währung', 1)",
		5	=>	"INSERT INTO `".DBPREFIX."module_market_settings` VALUES (5, 'addEntry_only_community', '1', 'Nur Mitglieder dürfen Inserate hinzufügen (Community-Modul)', 2)",
		6	=>	"INSERT INTO `".DBPREFIX."module_market_settings` VALUES (6, 'addEntry', '1', 'Besuchern erlauben Inserate hinzuzufügen', 2)",
		7	=>	"INSERT INTO `".DBPREFIX."module_market_settings` VALUES (7, 'editEntry', '1', 'Besuchern erlauben Inserate zu editieren', 2)",
		8	=>	"INSERT INTO `".DBPREFIX."module_market_settings` VALUES (8, 'indexview', '0', 'Index-Ansicht', 2)"
	);

	foreach ($arrSettings as $id => $querySettings) {
		$query = "SELECT id FROM ".DBPREFIX."module_market_settings WHERE id=".$id;
		$objCheck = $objDatabase->SelectLimit($query, 1);
		if ($objCheck) {
			if ($objCheck->RecordCount() == 0) {
				if ($objDatabase->Execute($querySettings) === false) {
					return _databaseError($querySettings, $objDatabase->ErrorMsg());
				}
			}
		} else {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	require_once ASCMS_FRAMEWORK_PATH.'/File.class.php';
	$objFile =& new File();
	if (is_writeable(ASCMS_MEDIA_PATH.'/market') || $objFile->setChmod(ASCMS_MEDIA_PATH, ASCMS_MEDIA_WEB_PATH, '/market')) {
    	if ($mediaDir = @opendir(ASCMS_MEDIA_PATH.'/market')) {
    		while($file = readdir($mediaDir)) {
    			if ($file != '.' && $file != '..') {
    				if (!is_writeable(ASCMS_MEDIA_PATH.'/market/'.$file) && !$objFile->setChmod(ASCMS_MEDIA_PATH.'/market/', ASCMS_MEDIA_WEB_PATH.'/market/', $file)) {
    					print "Setzen Sie die Zugriffsberechtigungen für die Datei ".ASCMS_MEDIA_PATH."/market/".$file." auf 777 (Unix) oder vergeben Sie auf diese Datei Schreibrechte (Windows) und laden Sie die Seite neu!";
    					return false;
    				}
    			}
			}
    	} else {
    		print "Setzen Sie die Zugriffsberechtigungen für das Verzeichnis ".ASCMS_MEDIA_PATH."/market/ und dessen Inhalt auf 777 (Unix) oder vergeben Sie dem Verzeichnis und dessen Inhalt Schreibrechte (Windows) und laden Sie die Seite neu!";
    		return false;
		}
    } else {
    	print "Setzen Sie die Zugriffsberechtigungen für das Verzeichnis ".ASCMS_MEDIA_PATH."/market/ und dessen Inhalt auf 777 (Unix) oder vergeben Sie dem Verzeichnis und dessen Inhalt Schreibrechte (Windows) und laden Sie die Seite neu!";
    	return false;
    }

    $arrColumns = $objDatabase->MetaColumns(DBPREFIX.'module_market');
	if ($arrColumns === false) {
		print "Die Struktur der Tabelle '".DBPREFIX."module_market' konnte nicht ermittelt werden!";
		return false;
	}

	if (empty($arrColumns['SPEZ_FIELD_1'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_market` ADD `spez_field_1` VARCHAR( 255 ) NOT NULL";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (empty($arrColumns['SPEZ_FIELD_2'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_market` ADD `spez_field_2` VARCHAR( 255 ) NOT NULL";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (empty($arrColumns['SPEZ_FIELD_3'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_market` ADD `spez_field_3` VARCHAR( 255 ) NOT NULL";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (empty($arrColumns['SPEZ_FIELD_4'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_market` ADD `spez_field_4` VARCHAR( 255 ) NOT NULL";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (empty($arrColumns['SPEZ_FIELD_5'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_market` ADD `spez_field_5` VARCHAR( 255 ) NOT NULL";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	//create acces table
	$arrTables = $objDatabase->MetaTables('TABLES');
	if ($arrTables === false) {
		print "Die Struktur der Datenbank konnte nicht ermittelt werden!";
		return false;
	}

	if (!in_array(DBPREFIX."module_market_spez_fields", $arrTables)) {
		$query = "CREATE TABLE `".DBPREFIX."module_market_spez_fields` (
			`id` INT( 5 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`name` VARCHAR( 100 ) NOT NULL ,
			`value` VARCHAR( 100 ) NOT NULL ,
			`type` INT( 1 ) NOT NULL DEFAULT '1',
			`lang_id` INT( 2 ) NOT NULL,
			`active` INT( 1 ) NOT NULL
			) ENGINE = MYISAM ;";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$query = "SELECT id FROM ".DBPREFIX."module_market_settings WHERE name='maxdayStatus'";
	$objCheck = $objDatabase->SelectLimit($query, 1);
	if ($objCheck !== false) {
		if ($objCheck->RecordCount() == 0) {
			$query = 	"INSERT INTO `".DBPREFIX."module_market_settings` VALUES (9, 'maxdayStatus', '0', 'TXT_MARKET_SET_MAXDAYS_ON', 2);";

			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	}

	$query = "SELECT id FROM ".DBPREFIX."module_market_settings WHERE name='searchPrice'";
	$objCheck = $objDatabase->SelectLimit($query, 1);
	if ($objCheck !== false) {
		if ($objCheck->RecordCount() == 0) {
			$query = 	"INSERT INTO `".DBPREFIX."module_market_settings` ( `id` , `name` , `value` , `description` , `type` )
						VALUES (
						NULL , 'searchPrice', '', 'TXT_MARKET_SET_EXP_SEARCH_PRICE', '3'
						);";

			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	}

	$query = "SELECT id FROM ".DBPREFIX."module_market_spez_fields WHERE name='spez_field_1'";
	$objCheck = $objDatabase->SelectLimit($query, 1);
	if ($objCheck !== false) {
		if ($objCheck->RecordCount() == 0) {
			$query = 	"INSERT INTO `".DBPREFIX."module_market_spez_fields` ( `id` , `name` , `value` , `type` , `lang_id` , `active` )
						VALUES (
						NULL , 'spez_field_1', '', '1', '1', '0'
						);";

			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	}

	$query = "SELECT id FROM ".DBPREFIX."module_market_spez_fields WHERE name='spez_field_2'";
	$objCheck = $objDatabase->SelectLimit($query, 1);
	if ($objCheck !== false) {
		if ($objCheck->RecordCount() == 0) {
			$query = 	"INSERT INTO `".DBPREFIX."module_market_spez_fields` ( `id` , `name` , `value` , `type` , `lang_id` , `active` )
						VALUES (
						NULL , 'spez_field_2', '', '1', '1', '0'
						);";

			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	}

	$query = "SELECT id FROM ".DBPREFIX."module_market_spez_fields WHERE name='spez_field_3'";
	$objCheck = $objDatabase->SelectLimit($query, 1);
	if ($objCheck !== false) {
		if ($objCheck->RecordCount() == 0) {
			$query = 	"INSERT INTO `".DBPREFIX."module_market_spez_fields` ( `id` , `name` , `value` , `type` , `lang_id` , `active` )
						VALUES (
						NULL , 'spez_field_3', '', '1', '1', '0'
						);";

			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	}

	$query = "SELECT id FROM ".DBPREFIX."module_market_spez_fields WHERE name='spez_field_4'";
	$objCheck = $objDatabase->SelectLimit($query, 1);
	if ($objCheck !== false) {
		if ($objCheck->RecordCount() == 0) {
			$query = 	"INSERT INTO `".DBPREFIX."module_market_spez_fields` ( `id` , `name` , `value` , `type` , `lang_id` , `active` )
						VALUES (
						NULL , 'spez_field_4', '', '1', '1', '0'
						);";

			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	}

	$query = "SELECT id FROM ".DBPREFIX."module_market_spez_fields WHERE name='spez_field_5'";
	$objCheck = $objDatabase->SelectLimit($query, 1);
	if ($objCheck !== false) {
		if ($objCheck->RecordCount() == 0) {
			$query = 	"INSERT INTO `".DBPREFIX."module_market_spez_fields` ( `id` , `name` , `value` , `type` , `lang_id` , `active` )
						VALUES (
						NULL , 'spez_field_5', '', '1', '1', '0'
						);";

			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	}

	$query = 	"UPDATE `".DBPREFIX."module_market_settings` SET `description` = 'TXT_MARKET_SET_MAXDAYS' WHERE `".DBPREFIX."module_market_settings`.`id` =1 LIMIT 1 ;";
	if ($objDatabase->Execute($query) === false) {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	$query = 	"UPDATE `".DBPREFIX."module_market_settings` SET `description` = 'TXT_MARKET_SET_DESCRIPTION' WHERE `".DBPREFIX."module_market_settings`.`id` =2 LIMIT 1 ;";
	if ($objDatabase->Execute($query) === false) {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	$query = 	"UPDATE `".DBPREFIX."module_market_settings` SET `description` = 'TXT_MARKET_SET_PAGING' WHERE `".DBPREFIX."module_market_settings`.`id` =3 LIMIT 1 ;";
	if ($objDatabase->Execute($query) === false) {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	$query = 	"UPDATE `".DBPREFIX."module_market_settings` SET `description` = 'TXT_MARKET_SET_CURRENCY' WHERE `".DBPREFIX."module_market_settings`.`id` =4 LIMIT 1 ;";
	if ($objDatabase->Execute($query) === false) {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	$query = 	"UPDATE `".DBPREFIX."module_market_settings` SET `description` = 'TXT_MARKET_SET_ADD_ENTRY_ONLY_COMMUNITY' WHERE `".DBPREFIX."module_market_settings`.`id` =5 LIMIT 1 ;";
	if ($objDatabase->Execute($query) === false) {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	$query = 	"UPDATE `contrexx_module_market_settings` SET `description` = 'TXT_MARKET_SET_ADD_ENTRY' WHERE `".DBPREFIX."module_market_settings`.`id` =6 LIMIT 1 ;";
	if ($objDatabase->Execute($query) === false) {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	$query = 	"UPDATE `".DBPREFIX."module_market_settings` SET `description` = 'TXT_MARKET_SET_EDIT_ENTRY' WHERE `".DBPREFIX."module_market_settings`.`id` =7 LIMIT 1 ;";
	if ($objDatabase->Execute($query) === false) {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	$query = 	" UPDATE `".DBPREFIX."module_market_settings` SET `description` = 'TXT_MARKET_SET_INDEXVIEW' WHERE `".DBPREFIX."module_market_settings`.`id` =8 LIMIT 1 ;";
	if ($objDatabase->Execute($query) === false) {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	$query = 	"UPDATE `".DBPREFIX."module_market_settings` SET `description` = 'TXT_MARKET_SET_MAXDAYS_ON' WHERE `".DBPREFIX."module_market_settings`.`id` =9 LIMIT 1 ;";
	if ($objDatabase->Execute($query) === false) {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	return true;
}
?>