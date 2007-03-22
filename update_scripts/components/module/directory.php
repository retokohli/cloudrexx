<?php
function _directoryUpdate() {
	global $objDatabase;

	//create acces table
	$arrTables = $objDatabase->MetaTables('TABLES');
	if ($arrTables === false) {
		print "Die Struktur der Datenbank konnte nicht ermittelt werden!";
		return false;
	}

	if (!in_array(DBPREFIX."module_directory_settings_google", $arrTables)) {
		$query = "CREATE TABLE `".DBPREFIX."module_directory_settings_google` (
			`setid` smallint(6) NOT NULL auto_increment,
			`setname` varchar(250) NOT NULL default '',
			`setvalue` text NOT NULL,
			`setdescription` varchar(60) NOT NULL default '',
			`settyp` int(1) NOT NULL default '0',
			PRIMARY KEY  (`setid`),
			KEY `setname` (`setname`)
			) TYPE=MyISAM";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$query = "SELECT setid FROM ".DBPREFIX."module_directory_settings_google WHERE setname='googleSeach'";
	$objCheck = $objDatabase->SelectLimit($query, 1);
	if ($objCheck !== false) {
		if ($objCheck->RecordCount() == 0) {
			$query = "INSERT INTO `".DBPREFIX."module_directory_settings_google` VALUES (1, 'googleSeach', '0', 'Google Suche', 2)";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	$query = "SELECT setid FROM ".DBPREFIX."module_directory_settings_google WHERE setname='googleResults'";
	$objCheck = $objDatabase->SelectLimit($query, 1);
	if ($objCheck) {
		if ($objCheck->RecordCount() == 0) {
			$query = "INSERT INTO `".DBPREFIX."module_directory_settings_google` VALUES (2, 'googleResults', '', 'Anzahl Google Resultate', 1)";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	$query = "SELECT setid FROM ".DBPREFIX."module_directory_settings_google WHERE setname='googleId'";
	$objCheck = $objDatabase->SelectLimit($query, 1);
	if ($objCheck !== false) {
		if ($objCheck->RecordCount() == 0) {
			$query = "INSERT INTO `".DBPREFIX."module_directory_settings_google` VALUES (26, 'googleId', '', 'Google Key', 1)";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	$query = "SELECT setid FROM ".DBPREFIX."module_directory_settings_google WHERE setname='googleLang'";
	$objCheck = $objDatabase->SelectLimit($query, 1);
	if ($objCheck !== false) {
		if ($objCheck->RecordCount() == 0) {
			$query = "INSERT INTO `".DBPREFIX."module_directory_settings_google` VALUES (27, 'googleLang', '', 'Sprachparameter', 1)";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	if (!in_array(DBPREFIX.'module_directory_access', $arrTables)) {
		$query = "CREATE TABLE `".DBPREFIX."module_directory_access` (
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

	$query = "SELECT id FROM ".DBPREFIX."module_directory_access WHERE id='2'";
	$objCheck = $objDatabase->SelectLimit($query, 1);
	if ($objCheck !== false) {
		if ($objCheck->RecordCount() == 0) {
			$query = "INSERT INTO `".DBPREFIX."module_directory_access` VALUES (2, 'addFeed', 'Dateien hinzufügen', 96, 'global')";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	$query = "SELECT id FROM ".DBPREFIX."module_directory_access WHERE id='3'";
	$objCheck = $objDatabase->SelectLimit($query, 1);
	if ($objCheck !== false) {
		if ($objCheck->RecordCount() == 0) {
			$query = "INSERT INTO `".DBPREFIX."module_directory_access` VALUES (3, 'manageFeeds', 'Dateien verwalten', 94, 'global')";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	//create catecories table
	if (!in_array(DBPREFIX.'module_directory_categories', $arrTables)) {
		$query = "CREATE TABLE `".DBPREFIX."module_directory_categories` (
				  `id` smallint(6) NOT NULL auto_increment,
				  `parentid` smallint(6) unsigned NOT NULL default '0',
				  `name` varchar(100) NOT NULL default '',
				  `description` varchar(250) NOT NULL default '',
				  `displayorder` smallint(6) unsigned NOT NULL default '1000',
				  `metadesc` varchar(250) NOT NULL default '',
				  `metakeys` varchar(250) NOT NULL default '',
				  `status` int(1) NOT NULL default '1',
				  PRIMARY KEY  (`id`),
				  KEY `name` (`name`)
				) TYPE=MyISAM";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$query = "SELECT id FROM ".DBPREFIX."module_directory_categories WHERE id='1'";
	$objCheck = $objDatabase->SelectLimit($query, 1);
	if ($objCheck !== false) {
		if ($objCheck->RecordCount() == 0) {
			$query = "INSERT INTO `".DBPREFIX."module_directory_categories` (`id`, `parentid`, `name`, `description`, `displayorder`, `metadesc`, `metakeys`, `status`) VALUES ('1', '0', 'Demo Kategorie', 'Demo Kategorie', '0', 'Demo Kategorie', 'Demo Kategorie', '1')";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		return _databaseError($query, $objDatabase);
	}

	//create directory table
	if (!in_array(DBPREFIX.'module_directory_dir', $arrTables)) {
		$query = "CREATE TABLE `".DBPREFIX."module_directory_dir` (
				  `id` mediumint(7) NOT NULL auto_increment,
				  `title` varchar(100) NOT NULL default '',
				  `filename` varchar(255) NOT NULL default '',
				  `link` varchar(255) NOT NULL default '',
				  `date` varchar(14) default NULL,
				  `description` mediumtext NOT NULL,
				  `catid` varchar(255) NOT NULL default '0',
				  `platform` varchar(40) NOT NULL default '',
				  `language` varchar(40) NOT NULL default '',
				  `size` int(9) NOT NULL default '0',
				  `checksum` varchar(255) NOT NULL default '',
				  `relatedlinks` varchar(255) NOT NULL default '',
				  `typ` varchar(10) NOT NULL default '',
				  `hits` int(9) NOT NULL default '0',
				  `status` tinyint(1) NOT NULL default '0',
				  `addedby` varchar(50) NOT NULL default '',
				  `provider` varchar(255) NOT NULL default '',
				  `ip` varchar(255) NOT NULL default '',
				  `validatedate` varchar(14) NOT NULL default '',
				  `lastip` varchar(50) NOT NULL default '',
				  `popular_date` varchar(30) NOT NULL default '',
				  `popular_hits` int(7) NOT NULL default '0',
				  `xml_refresh` varchar(15) NOT NULL default '',
				  `canton` varchar(50) NOT NULL default '',
				  `searchkeys` varchar(255) NOT NULL default '',
				  `company_name` varchar(100) NOT NULL default '',
				  `street` varchar(255) NOT NULL default '',
				  `zip` varchar(5) NOT NULL default '',
				  `city` varchar(50) NOT NULL default '',
				  `phone` varchar(20) NOT NULL default '',
				  `contact` varchar(100) NOT NULL default '',
				  `information` varchar(100) NOT NULL default '',
				  `fax` varchar(20) NOT NULL default '',
				  `mobile` varchar(20) NOT NULL default '',
				  `mail` varchar(50) NOT NULL default '',
				  `homepage` varchar(50) NOT NULL default '',
				  `industry` varchar(100) NOT NULL default '',
				  `legalform` varchar(50) NOT NULL default '',
				  `conversion` varchar(50) NOT NULL default '',
				  `employee` varchar(255) NOT NULL default '',
				  `foundation` varchar(10) NOT NULL default '',
				  `mwst` varchar(50) NOT NULL default '',
				  `opening` varchar(255) NOT NULL default '',
				  `holidays` varchar(255) NOT NULL default '',
				  `places` varchar(255) NOT NULL default '',
				  `logo` varchar(50) NOT NULL default '',
				  `team` varchar(255) NOT NULL default '',
				  `portfolio` varchar(255) NOT NULL default '',
				  `offers` varchar(255) NOT NULL default '',
				  `concept` varchar(255) NOT NULL default '',
				  `map` varchar(255) NOT NULL default '',
				  `lokal` varchar(255) NOT NULL default '',
				  `spezial` int(4) NOT NULL default '0',
				  `premium` int(1) NOT NULL default '0',
				  PRIMARY KEY  (`id`),
				  KEY `catid` (`catid`),
				  KEY `date` (`date`),
				  KEY `temphitsout` (`hits`),
				  FULLTEXT KEY `name` (`title`,`description`),
				  FULLTEXT KEY `description` (`description`)
				) TYPE=MyISAM";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$query = "SELECT id FROM ".DBPREFIX."module_directory_dir";
	$objCheck = $objDatabase->Execute($query);
	if ($objCheck !== false) {
		if ($objCheck->RecordCount() == 0) {
			$query = "INSERT INTO `".DBPREFIX."module_directory_dir` (id, title, link, date, description, catid, size, relatedlinks, typ, status, addedby) VALUES ('1', 'Demoeintrag', 'http://www.contrexx.com', '1153217288', 'Beschreibung', '[1]', '0', 'http://www.astalavista.ch', 'link', '1', '1')";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	//create vote table
	if (!in_array(DBPREFIX.'module_directory_vote', $arrTables)) {
		$query = "CREATE TABLE `".DBPREFIX."module_directory_vote` (
				  `id` int(7) NOT NULL auto_increment,
				  `feed_id` int(7) NOT NULL default '0',
				  `vote` int(2) NOT NULL default '0',
				  `client` varchar(255) NOT NULL default '',
				  `time` varchar(20) NOT NULL default '',
				  PRIMARY KEY  (`id`)
				) TYPE=MyISAM";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	//create mail table
	if (!in_array(DBPREFIX.'module_directory_mail', $arrTables)) {
		$query = "CREATE TABLE `".DBPREFIX."module_directory_mail` (
				  `id` tinyint(4) NOT NULL auto_increment,
				  `title` varchar(255) NOT NULL default '',
				  `content` longtext NOT NULL,
				  PRIMARY KEY  (`id`)
				) TYPE=MyISAM";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$query = "SELECT id FROM ".DBPREFIX."module_directory_mail WHERE id=1";
	$objCheck = $objDatabase->SelectLimit($query, 1);
	if ($objCheck !== false) {
		if ($objCheck->RecordCount() == 0) {
			$query = "INSERT INTO `".DBPREFIX."module_directory_mail` VALUES (1, '[[URL]] - Eintrag aufgeschaltet', 'Hallo [[FIRSTNAME]] [[LASTNAME]] ([[USERNAME]])\r\n\r\nDein Eintrag mit dem Titel \"[[TITLE]]\" wurde auf [[URL]] erfolgreich aufgeschaltet. \r\n\r\nBenutze folgenden Link um direkt zu Deinem Eintrag zu gelangen:\r\n[[LINK]]\r\n\r\nMit freundlichen Grüssen\r\n[[URL]] - Team\r\n\r\n[[DATE]]')";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	//create settings table
	if (!in_array(DBPREFIX.'module_directory_settings', $arrTables)) {
		$query = "CREATE TABLE `".DBPREFIX."module_directory_settings` (
				  `setid` smallint(6) NOT NULL auto_increment,
				  `setname` varchar(250) NOT NULL default '',
				  `setvalue` text NOT NULL,
				  `setdescription` varchar(75) NOT NULL default '',
				  `settyp` int(1) NOT NULL default '0',
				  PRIMARY KEY  (`setid`),
				  KEY `setname` (`setname`)
				) TYPE=MyISAM";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$arrSettings = array(
		5		=>	"INSERT INTO `".DBPREFIX."module_directory_settings` VALUES (5, 'xmlLimit', '5', 'XML Limite', 1)",
		6		=>	"INSERT INTO `".DBPREFIX."module_directory_settings` VALUES (6, 'platforms', ',Windows (all),Win2003 Server,WinXP,Win2k,Win9x,WinNT,WinME,\r\nWinCE,Linux,Solaris,HPUX,FreeBSD,PalmOS,Java,MacOS,IRIX,OS/2,DOS,öö  ja,\r\nUnix', 'Plattformen', 0)",
		7		=>	"INSERT INTO `".DBPREFIX."module_directory_settings` VALUES (7, 'language', ',Deutsch,English,Italian,French', 'Sprachen', 0)",
		10	=>	"INSERT INTO `".DBPREFIX."module_directory_settings` VALUES (10, 'latest_content', '4', 'Anzahl neuste Einträge (Content)', 1)",
		11	=>	"INSERT INTO `".DBPREFIX."module_directory_settings` VALUES (11, 'latest_xml', '4', 'Anzahl neuste Einträge (XML)', 1)",
		13	=>	"INSERT INTO `".DBPREFIX."module_directory_settings` VALUES (13, 'description', '0', 'Kategorie Beschreibung', 2)",
		12	=>	"INSERT INTO `".DBPREFIX."module_directory_settings` VALUES (12, 'status', '0', 'Automatisch aktiv', 2)",
		14	=>	"INSERT INTO `".DBPREFIX."module_directory_settings` VALUES (14, 'populardays', '7', 'Anzahl Tage für Popular', 1)",
		16	=>	"INSERT INTO `".DBPREFIX."module_directory_settings` VALUES (16, 'canton', ',Aargau,Appenzell-Ausserrhoden,Appenzell-Innerrhoden,Basel-Land,\r\nBasel-Stadt,Bern,Freiburg,Genf,Glarus,Graubünden,Jura,Luzern,\r\nNeuenburg,Nidwalden,Obwalden,St. Gallen,Schaffhausen,Schwyz,\r\nSolothurn,Thurgau,Tessin,Uri,Waadt,Wallis,Zug,Zürich', 'Kantone', 0)",
		17	=>	"INSERT INTO `".DBPREFIX."module_directory_settings` VALUES (17, 'refreshfeeds', '3600', 'XML aktualisieren (sec)', 1)",
		19	=>	"INSERT INTO `".DBPREFIX."module_directory_settings` VALUES (19, 'show_rss', '1', 'RSS erlauben', 2)",
		20	=>	"INSERT INTO `".DBPREFIX."module_directory_settings` VALUES (20, 'show_links', '1', 'Links erlauben', 2)",
		21	=>	"INSERT INTO `".DBPREFIX."module_directory_settings` VALUES (21, 'show_files', '1', 'Dateien erlauben', 2)",
		22	=>	"INSERT INTO `".DBPREFIX."module_directory_settings` VALUES (22, 'mark_new_entrees', '7', 'Neue Einträge markieren (Day)', 1)",
		23	=>	"INSERT INTO `".DBPREFIX."module_directory_settings` VALUES (23, 'showConfirm', '1', 'Nicht bestätigte Feeds beim Start(Backend) anzeigen', 2)",
		27	=>	"INSERT INTO `".DBPREFIX."module_directory_settings` VALUES (27, 'addFeed_only_community', '1', 'Nur Mitglieder dürfen Einträge anmelden (Community-Modul)', 2)",
		26	=>	"INSERT INTO `".DBPREFIX."module_directory_settings` VALUES (26, 'addFeed', '1', 'Besuchern erlauben Einträge anzumelden', 2)",
		28	=>	"INSERT INTO `".DBPREFIX."module_directory_settings` VALUES (28, 'editFeed', '1', 'Besuchern erlauben, eigene Einträge zu editieren (Community Modul)', 2)",
		29	=>	"INSERT INTO `".DBPREFIX."module_directory_settings` VALUES (29, 'editFeed_status', '0', 'Einträge nach Bearbeiten wieder aktiv', 2)",
		30	=>	"INSERT INTO `".DBPREFIX."module_directory_settings` VALUES (30, 'adminMail', '', 'Benachrichtigungsmail Empfänger', 1)"
	);

	foreach ($arrSettings as $id => $querySettings) {
		$query = "SELECT setid FROM ".DBPREFIX."module_directory_settings WHERE setid=".$id;
		$objCheck = $objDatabase->SelectLimit($query, 1);
		if ($objCheck !== false) {
			if ($objCheck->RecordCount() == 0) {
				if ($objDatabase->Execute($querySettings) === false) {
					return _databaseError($querySettings, $objDatabase->ErrorMsg());
				}
			}
		} else {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	//create inputfields table
	if (!in_array(DBPREFIX.'module_directory_inputfields', $arrTables)) {
		$query = "CREATE TABLE `".DBPREFIX."module_directory_inputfields` (
				  `id` int(7) NOT NULL auto_increment,
				  `typ` int(2) NOT NULL default '0',
				  `name` varchar(255) NOT NULL default '',
				  `title` varchar(255) NOT NULL default '',
				  `active` int(1) NOT NULL default '0',
				  `active_backend` int(1) NOT NULL default '0',
				  `is_required` int(11) NOT NULL default '0',
				  `read_only` int(1) NOT NULL default '0',
				  `sort` int(5) NOT NULL default '0',
				  `exp_search` int(1) NOT NULL default '0',
				  `is_search` int(1) NOT NULL default '0',
				  PRIMARY KEY  (`id`)
				) TYPE=MyISAM";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$arrInputs = array(
		1	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` VALUES (1, 1, 'title', 'TXT_DIR_F_TITLE', 1, 1, 1, 0, 0, 0, 0)",
		2	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` VALUES (2, 2, 'description', 'TXT_DIR_F_DESCRIPTION', 1, 1, 1, 0, 1, 0, 0)",
		3	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` VALUES (3, 3, 'platform', 'TXT_DIR_F_PLATFORM', 0, 0, 0, 0, 0, 1, 0)",
		4	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` VALUES (4, 3, 'language', 'TXT_DIR_F_LANG', 0, 0, 0, 0, 0, 1, 0)",
		5	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` VALUES (5, 1, 'addedby', 'TXT_DIR_F_ADDED_BY', 1, 1, 1, 0, 16, 0, 0)",
		6	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` VALUES (6, 1, 'relatedlinks', 'TXT_DIR_F_RELATED_LINKS', 1, 1, 0, 0, 14, 1, 0)",
		7	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` VALUES (7, 3, 'canton', 'TXT_DIR_F_CANTON', 0, 0, 0, 0, 7, 1, 0)",
		8	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` VALUES (8, 2, 'searchkeys', 'TXT_DIR_F_SEARCH_KEYS', 1, 1, 0, 0, 15, 0, 0)",
		9	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` VALUES (9, 1, 'company_name', 'TXT_DIR_F_CO_NAME', 1, 1, 0, 0, 2, 1, 0)",
		10	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` VALUES (10, 1, 'street', 'TXT_DIR_F_STREET', 1, 1, 0, 0, 4, 1, 0)",
		11	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` VALUES (11, 1, 'zip', 'TXT_DIR_F_PLZ', 1, 1, 0, 0, 5, 1, 1)",
		12	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` VALUES (12, 1, 'phone', 'TXT_DIR_F_PHONE', 1, 1, 0, 0, 8, 1, 0)",
		13	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` VALUES (13, 1, 'contact', 'TXT_DIR_F_PERSON', 0, 0, 0, 0, 0, 1, 0)",
		15	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` VALUES (15, 1, 'information', 'TXT_INFOZEILE', 0, 0, 0, 0, 0, 1, 0)",
		14	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` VALUES (14, 1, 'city', 'TXT_DIR_CITY', 1, 1, 0, 0, 6, 1, 1)",
		16	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` VALUES (16, 1, 'fax', 'TXT_TELEFAX', 1, 1, 0, 0, 9, 1, 0)",
		17	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` VALUES (17, 1, 'mobile', 'TXT_MOBILE', 1, 1, 0, 0, 10, 1, 0)",
		18	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` VALUES (18, 1, 'mail', 'TXT_DIR_F_EMAIL', 1, 1, 0, 0, 11, 1, 0)",
		19	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` VALUES (19, 1, 'homepage', 'TXT_HOMEPAGE', 1, 1, 1, 0, 12, 1, 0)",
		20	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` VALUES (20, 1, 'industry', 'TXT_BRANCHE', 0, 0, 0, 0, 0, 1, 0)",
		21	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` VALUES (21, 1, 'legalform', 'TXT_RECHTSFORM', 0, 0, 0, 0, 0, 1, 0)",
		22	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` VALUES (22, 2, 'conversion', 'TXT_UMSATZ', 0, 0, 0, 0, 0, 0, 0)",
		23	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` VALUES (23, 2, 'employee', 'TXT_MITARBEITER', 0, 0, 0, 0, 0, 1, 0)",
		24	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` VALUES (24, 1, 'foundation', 'TXT_GRUENDUNGSJAHR', 0, 0, 0, 0, 0, 1, 0)",
		25	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` VALUES (25, 1, 'mwst', 'TXT_MWST_NR', 0, 0, 0, 0, 0, 1, 0)",
		26	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` VALUES (26, 2, 'opening', 'TXT_OEFFNUNGSZEITEN', 0, 0, 0, 0, 0, 0, 0)",
		27	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` VALUES (27, 2, 'holidays', 'TXT_BETRIEBSFERIEN', 0, 0, 0, 0, 0, 0, 0)",
		28	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` VALUES (28, 2, 'places', 'TXT_SUCHORTE', 0, 0, 0, 0, 0, 0, 0)",
		29	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` VALUES (29, 4, 'logo', 'TXT_LOGO', 1, 1, 0, 0, 13, 0, 0)",
		30	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` VALUES (30, 2, 'team', 'TXT_TEAM', 0, 0, 0, 0, 0, 0, 0)",
		32	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` VALUES (32, 2, 'portfolio', 'TXT_REFERENZEN', 0, 0, 0, 0, 0, 0, 0)",
		33	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` VALUES (33, 2, 'offers', 'TXT_ANGEBOTE', 0, 0, 0, 0, 0, 0, 0)",
		34	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` VALUES (34, 2, 'concept', 'TXT_KONZEPT', 0, 0, 0, 0, 0, 0, 0)",
		35	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` VALUES (35, 4, 'map', 'TXT_MAP', 0, 0, 0, 0, 0, 0, 0)",
		36	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` VALUES (36, 4, 'lokal', 'TXT_LOKAL', 0, 0, 0, 0, 0, 0, 0)"
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

	require_once ASCMS_FRAMEWORK_PATH.'/File.class.php';
	$objFile =& new File();
	if (is_writable(ASCMS_MEDIA_PATH.'/directory') || $objFile->setChmod(ASCMS_MEDIA_PATH, ASCMS_MEDIA_WEB_PATH, '/directory')) {
    	if ($mediaDir = @opendir(ASCMS_MEDIA_PATH.'/directory')) {
    		while($file = readdir($mediaDir)) {
    			if ($file != '.' && $file != '..') {
    				if (!is_writeable(ASCMS_MEDIA_PATH.'/directory/'.$file) && !$objFile->setChmod(ASCMS_MEDIA_PATH.'/directory/', ASCMS_MEDIA_WEB_PATH.'/directory/', $file)) {
    					print "Setzen Sie die Zugriffsberechtigungen für die Datei ".ASCMS_MEDIA_PATH."/directory/".$file." auf 777 (Unix) oder vergeben Sie auf diese Datei Schreibrechte (Windows) und laden Sie die Seite neu!";
    					return false;
    				}
    			}
			}
    	} else {
    		print "Setzen Sie die Zugriffsberechtigungen für das Verzeichnis ".ASCMS_MEDIA_PATH."/directory/ und dessen Inhalt auf 777 (Unix) oder vergeben Sie dem Verzeichnis und dessen Inhalt Schreibrechte (Windows) und laden Sie die Seite neu!";
    		return false;
		}
    } else {
    	print "Setzen Sie die Zugriffsberechtigungen für das Verzeichnis ".ASCMS_MEDIA_PATH."/directory/ und dessen Inhalt auf 777 (Unix) oder vergeben Sie dem Verzeichnis und dessen Inhalt Schreibrechte (Windows) und laden Sie die Seite neu!";
    	return false;
    }



    if (!in_array(DBPREFIX.'module_directory_levels', $arrTables)) {
		$query = "CREATE TABLE 	`".DBPREFIX."module_directory_levels` (
								`id` int(7) NOT NULL auto_increment,
								`parentid` int(7) NOT NULL default '0',
								`name` varchar(100) NOT NULL default '',
								`description` varchar(255) NOT NULL default '',
								`metadesc` varchar(100) NOT NULL default '',
								`metakeys` varchar(100) NOT NULL default '',
								`displayorder` int(7) NOT NULL default '0',
								`showlevels` int(1) NOT NULL default '0',
								`showcategories` int(1) NOT NULL default '0',
								`status` int(1) NOT NULL default '0',
				   PRIMARY KEY  (`id`),
				            KEY `displayorder` (`displayorder`),
						    KEY `parentid` (`parentid`),
						    KEY `name` (`name`),
							KEY `status` (`status`)
		           ) TYPE=MyISAM";

		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}



	$arrColumns = $objDatabase->MetaColumns(DBPREFIX.'module_directory_categories');
	if ($arrColumns === false) {
		print "Die Struktur der Tabelle '".DBPREFIX."module_directory_categories' konnte nicht ermittelt werden!";
		return false;
	}


	if (empty($arrColumns['SHOWENTRIES'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_directory_categories` ADD `showentries` INT( 1 ) NOT NULL DEFAULT '1' AFTER `metakeys` ;";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}


	}

	$arrIndexes = $objDatabase->MetaIndexes(DBPREFIX.'module_directory_categories');
	if ($arrIndexes === false) {
		print "Die Struktur der Tabelle '".DBPREFIX."module_directory_categories' konnte nicht ermittelt werden!";
		return false;
	}

	if (empty($arrIndexes['parentid'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_directory_categories` ADD INDEX ( `parentid` ) ;";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (empty($arrIndexes['displayorder'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_directory_categories` ADD INDEX ( `displayorder`) ;";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (empty($arrIndexes['name'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_directory_categories` ADD INDEX ( `name` ) ;";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (empty($arrIndexes['status'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_directory_categories` ADD INDEX ( `status` ) ;";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}


	if (!in_array(DBPREFIX.'module_directory_rel_dir_cat', $arrTables)) {
		$query = "CREATE TABLE 	`".DBPREFIX."module_directory_rel_dir_cat` (
								`dir_id` int(7) NOT NULL default '0',
								`cat_id` int(7) NOT NULL default '0',
		           PRIMARY KEY  (`dir_id`,`cat_id`)
				   ) TYPE=MyISAM";

		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}


	if (!in_array(DBPREFIX.'module_directory_rel_dir_level', $arrTables)) {
		$query = "CREATE TABLE `".DBPREFIX."module_directory_rel_dir_level` (
							   `dir_id` int(7) NOT NULL default '0',
							   `level_id` int(7) NOT NULL default '0',
				  PRIMARY KEY  (`dir_id`,`level_id`)
				  ) TYPE=MyISAM";

		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}


	$query = "SELECT setid FROM ".DBPREFIX."module_directory_settings WHERE setid='1'";
	$objCheck = $objDatabase->SelectLimit($query, 1);
	if ($objCheck !== false) {
		if ($objCheck->RecordCount() == 0) {
			$query = "INSERT INTO `".DBPREFIX."module_directory_settings` ( `setid` , `setname` , `setvalue` , `setdescription` , `settyp` )
	                       VALUES ('1', 'levels', '0', 'Ebenen aktivieren', '2');";

			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	}


	$query = "SELECT setid FROM ".DBPREFIX."module_directory_settings WHERE setname='indexview'";
	$objCheck = $objDatabase->SelectLimit($query, 1);
	if ($objCheck !== false) {
		if ($objCheck->RecordCount() == 0) {
			$query = "INSERT INTO `".DBPREFIX."module_directory_settings` VALUES ('31', 'indexview', '0', 'Index-Ansicht', 2)";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	}


	$query = "SELECT setid FROM ".DBPREFIX."module_directory_settings WHERE setname='platforms'";
	$objCheck = $objDatabase->SelectLimit($query, 1);
	if ($objCheck !== false) {
		if ($objCheck->RecordCount() == 0) {
			$query = "UPDATE `".DBPREFIX."module_directory_settings` SET `setname` = 'platform' WHERE `setid` = 6 LIMIT 1 ;";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	}


	$query = "SELECT id FROM ".DBPREFIX."module_directory_mail WHERE id='2'";
	$objCheck = $objDatabase->SelectLimit($query, 1);
	if ($objCheck !== false) {
		if ($objCheck->RecordCount() == 0) {
			$query = "INSERT INTO `".DBPREFIX."module_directory_mail`
			               VALUES (2, '[[URL]] - Neuer Eintrag', 'Hallo Admin\r\n\r\nAuf [[URL]] wurde ein Eintrag aufgeschaltet oder editiert. Bitte überprüfen Sie diesen und Bestätigen Sie ihn falls nötig.\r\n\r\nEintrag Details:\r\n\r\nTitel: [[TITLE]]\r\nBenutzername: [[USERNAME]]\r\nVorname: [[FIRSTNAME]]\r\nNachname:[[LASTNAME]]\r\nLink: [[LINK]]\r\n\r\nAutomatisch generierte Nachricht\r\n[[DATE]]');";

			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	}


	$arrColumns = $objDatabase->MetaColumns(DBPREFIX.'module_directory_vote');
	if ($arrColumns === false) {
		print "Die Struktur der Tabelle '".DBPREFIX."module_directory_vote' konnte nicht ermittelt werden!";
		return false;
	}

	if (empty($arrColumns['COUNT'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_directory_vote` ADD `count` INT( 7 ) NOT NULL AFTER `vote` ;";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		} else {
			$arrVotes 		= array();
			$arrVotesNew 	= array();

			$query = "SELECT id, feed_id FROM ".DBPREFIX."module_directory_vote";
			$objResult = $objDatabase->Execute($query);
			if ($objResult !== false) {
				while (!$objResult->EOF) {
					if (!in_array($objResult->fields['feed_id'], $arrVotes)) {
						$arrVotes[] = $objResult->fields['feed_id'];
					}
					$objResult->MoveNext();
				}
			}

			if (!empty($arrVotes)) {
				foreach ($arrVotes as $key => $id) {
					$vote 	= 0;
					$count	= 0;

					$query = "SELECT feed_id, vote, client, time FROM ".DBPREFIX."module_directory_vote WHERE feed_id='".$id."'";
					$objResult = $objDatabase->Execute($query);
					if ($objResult !== false) {
						while (!$objResult->EOF) {
							$vote 	= $vote+$objResult->fields['vote'];
							$count++;

							$arrVotesNew[$id]['vote'] 	= $vote;
							$arrVotesNew[$id]['count'] 	= $count;
							$arrVotesNew[$id]['client'] = $objResult->fields['client'];
							$arrVotesNew[$id]['time'] 	= $objResult->fields['time'];

							$objResult->MoveNext();
						}
					}
				}
			}

			if (!empty($arrVotesNew)) {
				$query = "TRUNCATE TABLE ".DBPREFIX."module_directory_vote;";

				if ($objDatabase->Execute($query) === false) {
					return _databaseError($query, $objDatabase->ErrorMsg());
				}

				foreach ($arrVotesNew as $id => $value) {
					$query = "INSERT INTO `".DBPREFIX."module_directory_vote`
					               VALUES ('', '".$id."', '".$arrVotesNew[$id]['vote']."', '".$arrVotesNew[$id]['count']."', '".$arrVotesNew[$id]['client']."', '".$arrVotesNew[$id]['time']."');";

					if ($objDatabase->Execute($query) === false) {
						return _databaseError($query, $objDatabase->ErrorMsg());
					}
				}
			}
		}
	}

	//wird 2 mal erstellt.
	/*if (!in_array(DBPREFIX.'module_directory_settings_google', $arrTables)) {
		$query = "CREATE TABLE `".DBPREFIX."module_directory_settings_google` (
							   `setid` smallint(6) NOT NULL auto_increment,
							   `setname` varchar(250) NOT NULL default '',
							   `setvalue` text NOT NULL,
							   `setdescription` varchar(60) NOT NULL default '',
							   `settyp` int(1) NOT NULL default '0',
				   PRIMARY KEY (`setid`),
						   KEY `setname` (`setname`)
				   ) TYPE=MyISAM";

		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		} else {
			$query = "SELECT setid FROM ".DBPREFIX."module_directory_settings_google WHERE setid='1'";
			$objCheck = $objDatabase->SelectLimit($query, 1);
			if ($objCheck->RecordCount() == 0) {
				$query = "INSERT INTO `".DBPREFIX."module_directory_settings_google`
				               VALUES (1, 'googleSeach', '0', 'Google Suche', 2);";

				if ($objDatabase->Execute($query) === false) {
					return _databaseError($query, $objDatabase->ErrorMsg());
				}
			}

			$query = "SELECT setid FROM ".DBPREFIX."module_directory_settings_google WHERE setid='2'";
			$objCheck = $objDatabase->SelectLimit($query, 1);
			if ($objCheck->RecordCount() == 0) {
				$query = "INSERT INTO `".DBPREFIX."module_directory_settings_google`
				               VALUES (2, 'googleResults', '', 'Anzahl Google Resultate', 1);";

				if ($objDatabase->Execute($query) === false) {
					return _databaseError($query, $objDatabase->ErrorMsg());
				}
			}

			$query = "SELECT setid FROM ".DBPREFIX."module_directory_settings_google WHERE setid='3'";
			$objCheck = $objDatabase->SelectLimit($query, 1);
			if ($objCheck->RecordCount() == 0) {
				$query = "INSERT INTO `".DBPREFIX."module_directory_settings_google`
				               VALUES (3, 'googleId', '', 'Google Key', 1);";

				if ($objDatabase->Execute($query) === false) {
					return _databaseError($query, $objDatabase->ErrorMsg());
				}
			}

			$query = "SELECT setid FROM ".DBPREFIX."module_directory_settings_google WHERE setid='4'";
			$objCheck = $objDatabase->SelectLimit($query, 1);
			if ($objCheck->RecordCount() == 0) {
				$query = "INSERT INTO `".DBPREFIX."module_directory_settings_google`
				               VALUES (4, 'googleLang', '', 'Sprachparameter', 1);";

				if ($objDatabase->Execute($query) === false) {
					return _databaseError($query, $objDatabase->ErrorMsg());
				}
			}
		}
	}*/

	//update acces table
	$arrTables = $objDatabase->MetaTables('TABLES');
	if ($arrTables === false) {
		print "Die Struktur der Datenbank konnte nicht ermittelt werden!";
		return false;
	};

	$arrColumns = $objDatabase->MetaColumns(DBPREFIX.'module_directory_dir');
	if ($arrColumns !== false) {
		if (!empty($arrColumns['CATID'])) {
			$arrCategories = array();

			$query = "SELECT id, catid FROM ".DBPREFIX."module_directory_dir";
			$objResult = $objDatabase->Execute($query);
			if ($objResult !== false) {
				while (!$objResult->EOF) {
					$oldCategories = explode('[', $objResult->fields['catid']);

					foreach($oldCategories as $catId){
						if (!empty($catId)) {
							$arrCategories[$objResult->fields['id']][] = str_replace(']', '', $catId);
						}
					}

					$objResult->MoveNext();
				}
			}

			foreach($arrCategories as $id => $value){
				foreach($arrCategories[$id] as $key => $catid){
					$query = "SELECT dir_id FROM ".DBPREFIX."module_directory_rel_dir_cat WHERE dir_id='".$id."' AND cat_id='".$catid."'";
					$objCheck = $objDatabase->SelectLimit($query, 1);
					if ($objCheck->RecordCount() == 0) {
						$query = "INSERT INTO `".DBPREFIX."module_directory_rel_dir_cat`
						               VALUES ('".$id."', '".$catid."');";

						//echo $query;

						if ($objDatabase->Execute($query) === false) {
							return _databaseError($query, $objDatabase->ErrorMsg());
						}
					}
				}
			}
		}
	} else {
		print "Die Struktur der Tabelle '".DBPREFIX."module_directory_dir' konnte nicht ermittelt werden!";
		return false;
	}

	$arrIndexes = $objDatabase->MetaIndexes(DBPREFIX.'module_directory_dir');
	if ($arrIndexes === false) {
		print "Die Struktur der Tabelle '".DBPREFIX."module_directory_dir' konnte nicht ermittelt werden!";
		return false;
	}

	if (empty($arrIndexes['title'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_directory_dir` ADD INDEX ( `title` )";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (empty($arrIndexes['status'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_directory_dir` ADD INDEX ( `status` )";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (empty($arrIndexes['date'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_directory_dir` ADD INDEX ( `date` )";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (empty($arrIndexes['typ'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_directory_dir` ADD INDEX ( `typ` )";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$query = "SELECT dir_id FROM ".DBPREFIX."module_directory_rel_dir_cat";
	$objCheck = $objDatabase->Execute($query);
	if ($objCheck->RecordCount() != 0) {
		$arrColumns = $objDatabase->MetaColumns(DBPREFIX.'module_directory_dir');
		if ($arrColumns !== false) {
			if (!empty($arrColumns['CATID'])) {
				$query = "ALTER TABLE `".DBPREFIX."module_directory_dir` DROP `catid` ;";
				if ($objDatabase->Execute($query) === false) {
					return _databaseError($query, $objDatabase->ErrorMsg());
				}
			}
		} else {
			print "Die Struktur der Tabelle '".DBPREFIX."module_directory_dir' konnte nicht ermittelt werden!";
			return false;
		}
	} else {
		echo "Das migrieren der Verzeichniseinträge wurde nicht komplett abgeschlossen, bitte Update noch einmal ausführen.";
		return false;
	}


	$arrColumns = $objDatabase->MetaColumns(DBPREFIX.'module_directory_dir');
	if ($arrIndexes === false) {
		print "Die Struktur der Tabelle '".DBPREFIX."module_directory_dir' konnte nicht ermittelt werden!";
		return false;
	}

	if (empty($arrColumns['SPEZ_FIELD_1'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_directory_dir` ADD `spez_field_1` VARCHAR( 255 ) NOT NULL";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (empty($arrColumns['SPEZ_FIELD_2'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_directory_dir` ADD `spez_field_2` VARCHAR( 255 ) NOT NULL";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (empty($arrColumns['SPEZ_FIELD_3'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_directory_dir` ADD `spez_field_3` VARCHAR( 255 ) NOT NULL";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (empty($arrColumns['SPEZ_FIELD_4'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_directory_dir` ADD `spez_field_4` VARCHAR( 255 ) NOT NULL";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (empty($arrColumns['SPEZ_FIELD_5'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_directory_dir` ADD `spez_field_5` VARCHAR( 255 ) NOT NULL";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}
	if (empty($arrColumns['SPEZ_FIELD_6'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_directory_dir` ADD `spez_field_6` MEDIUMTEXT NOT NULL";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (empty($arrColumns['SPEZ_FIELD_7'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_directory_dir` ADD `spez_field_7` MEDIUMTEXT NOT NULL";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (empty($arrColumns['SPEZ_FIELD_8'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_directory_dir` ADD `spez_field_8` MEDIUMTEXT NOT NULL";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (empty($arrColumns['SPEZ_FIELD_9'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_directory_dir` ADD `spez_field_9` MEDIUMTEXT NOT NULL";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (empty($arrColumns['SPEZ_FIELD_10'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_directory_dir` ADD `spez_field_10` MEDIUMTEXT NOT NULL";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (empty($arrColumns['SPEZ_FIELD_11'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_directory_dir` ADD `spez_field_11` VARCHAR( 255 ) NOT NULL";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (empty($arrColumns['SPEZ_FIELD_12'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_directory_dir` ADD `spez_field_12` VARCHAR( 255 ) NOT NULL";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (empty($arrColumns['SPEZ_FIELD_13'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_directory_dir` ADD `spez_field_13` VARCHAR( 255 ) NOT NULL";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (empty($arrColumns['SPEZ_FIELD_14'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_directory_dir` ADD `spez_field_14` VARCHAR( 255 ) NOT NULL";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (empty($arrColumns['SPEZ_FIELD_15'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_directory_dir` ADD `spez_field_15` VARCHAR( 255 ) NOT NULL";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (empty($arrColumns['SPEZ_FIELD_16'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_directory_dir` ADD `spez_field_16` VARCHAR( 255 ) NOT NULL";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (empty($arrColumns['SPEZ_FIELD_17'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_directory_dir` ADD `spez_field_17` VARCHAR( 255 ) NOT NULL";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$arrInputs = array(
		37	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` (`id`, `typ`, `name`, `title`, `active`, `active_backend`, `is_required`, `read_only`, `sort`, `exp_search`, `is_search`) VALUES (37, 5, 'spez_field_1', '', 0, 0, 0, 0, 0, 1, 0)",
		38	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` (`id`, `typ`, `name`, `title`, `active`, `active_backend`, `is_required`, `read_only`, `sort`, `exp_search`, `is_search`) VALUES (38, 5, 'spez_field_2', '', 0, 0, 0, 0, 0, 1, 0)",
		39	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` (`id`, `typ`, `name`, `title`, `active`, `active_backend`, `is_required`, `read_only`, `sort`, `exp_search`, `is_search`) VALUES (39, 5, 'spez_field_3', '', 0, 0, 0, 0, 0, 1, 0)",
		40	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` (`id`, `typ`, `name`, `title`, `active`, `active_backend`, `is_required`, `read_only`, `sort`, `exp_search`, `is_search`) VALUES (40, 5, 'spez_field_4', '', 0, 0, 0, 0, 0, 1, 0)",
		41	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` (`id`, `typ`, `name`, `title`, `active`, `active_backend`, `is_required`, `read_only`, `sort`, `exp_search`, `is_search`) VALUES (41, 5, 'spez_field_5', '', 0, 0, 0, 0, 0, 1, 0)",
		42	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` (`id`, `typ`, `name`, `title`, `active`, `active_backend`, `is_required`, `read_only`, `sort`, `exp_search`, `is_search`) VALUES (42, 6, 'spez_field_6', '', 0, 0, 0, 0, 0, 1, 0)",
		43	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` (`id`, `typ`, `name`, `title`, `active`, `active_backend`, `is_required`, `read_only`, `sort`, `exp_search`, `is_search`) VALUES (43, 6, 'spez_field_7', '', 0, 0, 0, 0, 0, 1, 0)",
		44	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` (`id`, `typ`, `name`, `title`, `active`, `active_backend`, `is_required`, `read_only`, `sort`, `exp_search`, `is_search`) VALUES (44, 6, 'spez_field_8', '', 0, 0, 0, 0, 0, 1, 0)",
		45	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` (`id`, `typ`, `name`, `title`, `active`, `active_backend`, `is_required`, `read_only`, `sort`, `exp_search`, `is_search`) VALUES (45, 6, 'spez_field_9', '', 0, 0, 0, 0, 0, 1, 0)",
		46	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` (`id`, `typ`, `name`, `title`, `active`, `active_backend`, `is_required`, `read_only`, `sort`, `exp_search`, `is_search`) VALUES (46, 6, 'spez_field_10', '', 0, 0, 0, 0, 0, 1, 0)",
		47	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` (`id`, `typ`, `name`, `title`, `active`, `active_backend`, `is_required`, `read_only`, `sort`, `exp_search`, `is_search`) VALUES (47, 7, 'spez_field_11', '', 0, 0, 0, 0, 0, 0, 0)",
		48	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` (`id`, `typ`, `name`, `title`, `active`, `active_backend`, `is_required`, `read_only`, `sort`, `exp_search`, `is_search`) VALUES (48, 7, 'spez_field_12', '', 0, 0, 0, 0, 0, 0, 0)",
		49	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` (`id`, `typ`, `name`, `title`, `active`, `active_backend`, `is_required`, `read_only`, `sort`, `exp_search`, `is_search`) VALUES (49, 7, 'spez_field_13', '', 0, 0, 0, 0, 0, 0, 0)",
		50	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` (`id`, `typ`, `name`, `title`, `active`, `active_backend`, `is_required`, `read_only`, `sort`, `exp_search`, `is_search`) VALUES (50, 7, 'spez_field_14', '', 0, 0, 0, 0, 0, 0, 0)",
		51	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` (`id`, `typ`, `name`, `title`, `active`, `active_backend`, `is_required`, `read_only`, `sort`, `exp_search`, `is_search`) VALUES (51, 7, 'spez_field_15', '', 0, 0, 0, 0, 0, 0, 0)",
		52	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` (`id`, `typ`, `name`, `title`, `active`, `active_backend`, `is_required`, `read_only`, `sort`, `exp_search`, `is_search`) VALUES (52, 8, 'spez_field_16', '', 0, 0, 0, 0, 0, 1, 0)",
		53	=>	"INSERT INTO `".DBPREFIX."module_directory_inputfields` (`id`, `typ`, `name`, `title`, `active`, `active_backend`, `is_required`, `read_only`, `sort`, `exp_search`, `is_search`) VALUES (53, 8, 'spez_field_17', '', 0, 0, 0, 0, 0, 1, 0)"
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


	$arrInputs = array(
		32	=>	"INSERT INTO `".DBPREFIX."module_directory_settings` ( `setid` , `setname` , `setvalue` , `setdescription` , `settyp` )VALUES (32 , 'spez_field_16', '', 'spez_field_16', '0')",
		33	=>	"INSERT INTO `".DBPREFIX."module_directory_settings` ( `setid` , `setname` , `setvalue` , `setdescription` , `settyp` )VALUES (33 , 'spez_field_17', '', 'spez_field_17', '0')"
	);

	foreach ($arrInputs as $id => $queryInputs) {
		$query = "SELECT setid FROM ".DBPREFIX."module_directory_settings WHERE setid=".$id;
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



	$query = "UPDATE `".DBPREFIX."module_directory_settings` SET `setname` = 'platforms' WHERE `setid`=6 LIMIT 1";
	if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
	}

	$query = "UPDATE `".DBPREFIX."module_directory_inputfields` SET `name` = 'platforms' WHERE `id`=3 LIMIT 1";
	if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
	}

	return true;
}
?>