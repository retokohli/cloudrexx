<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Newsletter Update for 1.0.9.10.1</title>
</head>
<body>
<?php
/**

	Plazieren Sie diese Datei in das Hauptverzeichniss Ihrer Contrexx Installation
	und klicken Sie anschliessend auf "Update starten".

*/
if (!@include_once('../../config/configuration.php')) {
	die("Plazieren Sie diese Datei in das Hauptverzeichniss Ihrer Contrexx Installation.");
} elseif (!@include_once(ASCMS_CORE_PATH.'/API.php')) {
	die("Die Datei ".ASCMS_CORE_PATH."/API.php fehlt oder kann nicht geladen werden!");
}

if (isset($_SYSCONFIG)) {
	foreach ($_SYSCONFIG as $sysconfigKey => $sysconfValue) {
		$_CONFIG[$sysconfigKey] = $sysconfValue;
	}
}

if (_isNewerVersion($_CONFIG['coreCmsVersion'], '1.0.9.10.1')) {
	print "Dieses Update ist mit Ihrer Installation nicht kompatibel!<br /><br />";
	print "Sie benötigen ein Update Paket für die <b>".$_CONFIG['coreCmsEdition']."</b>-Edition in der Version <b>".$_CONFIG['coreCmsVersion']."</b>";
} else {
?>
<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
<?php
	if (!isset($_POST['doUpdate'])) {
		print "<input type=\"submit\" name=\"doUpdate\" value=\"Update ausführen\" />";

	} else {
		$errorMsg = '';
		$objDatabase = getDatabaseObject($errorMsg);
		$objDatabase->debug=true;

		$objUpdate = &new Update();
		$objUpdate->doUpdate();
	}
}

function _isNewerVersion($installedVersion, $newVersion)
{
	$arrInstalledVersion = explode('.', $installedVersion);
	$arrNewVersion = explode('.', $newVersion);

	$maxSubVersion = count($arrInstalledVersion) > count($arrNewVersion) ? count($arrInstalledVersion) : count($arrNewVersion);
	for ($nr = 0; $nr < $maxSubVersion; $nr++) {
		if (!isset($arrInstalledVersion[$nr])) {
			return true;
		} elseif ($arrNewVersion[$nr] > $arrInstalledVersion[$nr]) {
			return true;
		} elseif ($arrNewVersion[$nr] < $arrInstalledVersion[$nr]) {
			return false;
		}
	}

	return false;
}

class Update
{
	var $_arrSuccessMsg = array();

	function doUpdate()
	{
		if ($this->_newsletterUpdate()) {
			$this->_showSuccessMsg();
		}
	}

	function _showSuccessMsg()
	{
		print "<strong>Das Update wurde erfolgreich ausgeführt!</strong><br /><br />";
		print implode('<br />', $this->_arrSuccessMsg);
	}

	function _databaseError($query, $errorMsg)
	{
		print "Datenbank Fehler bei folgedem SQL Statement:<br />";
		print $query."<br /><br />";
		print "Detailierte Informationen:<br />";
		print $errorMsg."<br /><br />";
		print "Versuchen Sie das Update erneut auszuführen!<br />";

		return false;
	}

    function _newsletterUpdate()
    {
    	global $objDatabase;

    	$arrTables = $objDatabase->MetaTables('TABLES');
    	if (!$arrTables) {
    		print "Die Struktur der Datenbank konnte nicht ermittelt werden!";
    		return false;
    	}

    	if (!in_array(DBPREFIX."module_newsletter_user_title", $arrTables)) {
			$query ="CREATE TABLE `".DBPREFIX."module_newsletter_user_title` (
				`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`title` VARCHAR( 255 ) NOT NULL ,
				UNIQUE ( `title` )
				) TYPE = MYISAM";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
    	}

    	$arrTitleFields = array(
    		'Sehr geehrte Frau'		=> 'f',
    		'Sehr geehrter Herr'	=> 'm',

    	);

    	foreach (array_keys($arrTitleFields) as $field) {
    		$query = "SELECT 1 FROM `".DBPREFIX."module_newsletter_user_title` WHERE `title` = '".$field."'";
    		$objTitle = $objDatabase->SelectLimit($query, 1);
    		if ($objTitle !== false) {
    			if ($objTitle->RecordCount() == 0) {
    				$query = "INSERT INTO `".DBPREFIX."module_newsletter_user_title` (`title`) VALUES ('".$field."')";
    				if ($objDatabase->Execute($query) === false) {
    					return $this->_databaseError($query, $objDatabase->ErrorMsg());
    				}
    			}
    		} else {
    			return $this->_databaseError($query, $objDatabase->ErrorMsg());
    		}
    	}

    	$arrColumns = $objDatabase->MetaColumns(DBPREFIX.'module_newsletter_user');
    	if ($arrColumns === false) {
    		print "Die Struktur der Datenbanktabelle ".DBPREFIX."module_newsletter_user konnte nicht ermittelt werden!";
    		return false;
    	}

		if (!isset($arrColumns['SEX'])) {
			$query = "ALTER TABLE `".DBPREFIX."module_newsletter_user` CHANGE `title` `sex` ENUM( 'm', 'f' ) NULL DEFAULT NULL";
			if ($objDatabase->Execute($query) === false) {
				return $this->_databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		$arrColumns = $objDatabase->MetaColumns(DBPREFIX.'module_newsletter_user');
    	if ($arrColumns === false) {
    		print "Die Struktur der Datenbanktabelle ".DBPREFIX."module_newsletter_user konnte nicht ermittelt werden!";
    		return false;
    	}

		if (!isset($arrColumns['TITLE'])) {
			$query = "ALTER TABLE `".DBPREFIX."module_newsletter_user` ADD `title` INT UNSIGNED NOT NULL AFTER `sex`";
    		if ($objDatabase->Execute($query) === false) {
    			return $this->_databaseError($query, $objDatabase->ErrorMsg());
    		}
		}

		$arrTitles = array();
		$query = "SELECT `id`, `title` FROM `".DBPREFIX."module_newsletter_user_title` WHERE `title` = '".implode('\' OR `title` = \'', array_keys($arrTitleFields))."'";
		$objTitle = $objDatabase->Execute($query);
		if ($objTitle !== false) {
			while (!$objTitle->EOF) {
				$arrTitles[$arrTitleFields[$objTitle->fields['title']]] = $objTitle->fields['id'];
				$objTitle->MoveNext();
			}
		} else {
			return $this->_databaseError($query, $objDatabase->ErrorMsg());
		}

		$query = "SELECT `id`, `sex` FROM `".DBPREFIX."module_newsletter_user` WHERE `title` = 0 AND `sex` != ''";
    	while (1) {
	    	$objUser = $objDatabase->SelectLimit($query, 10);
	    	if ($objUser !== false) {
	    		if ($objUser->RecordCount() == 0) {
	    			// all datasets have been upgraded, so leave this loop
	    			break;
	    		} else {
	    			while (!$objUser->EOF) {
						$subQuery = "UPDATE `".DBPREFIX."module_newsletter_user` SET `title` = ".$arrTitles[$objUser->fields['sex']]." WHERE `id` = ".$objUser->fields['id'];
						if ($objDatabase->Execute($subQuery) === false) {
							return $this->_databaseError($subQuery, $objDatabase->ErrorMsg());
						}
	    				$objUser->MoveNext();
	    			}
	    		}
	    	} else {
	    		return $this->_databaseError($query, $objDatabase->ErrorMsg());
	    	}
    	}

    	// this must be done after the previous steps
    	$arrTitleFields = array(
    		'Dear Ms',
    		'Dear Mr',
    		'Madame',
    		'Monsieur'
    	);

    	foreach ($arrTitleFields as $field) {
    		$query = "SELECT 1 FROM `".DBPREFIX."module_newsletter_user_title` WHERE `title` = '".$field."'";
    		$objTitle = $objDatabase->SelectLimit($query, 1);
    		if ($objTitle !== false) {
    			if ($objTitle->RecordCount() == 0) {
    				$query = "INSERT INTO `".DBPREFIX."module_newsletter_user_title` (`title`) VALUES ('".$field."')";
    				if ($objDatabase->Execute($query) === false) {
    					return $this->_databaseError($query, $objDatabase->ErrorMsg());
    				}
    			}
    		} else {
    			return $this->_databaseError($query, $objDatabase->ErrorMsg());
    		}
    	}

    	return true;
    }

}
?>
</form>
</body>
</html>