<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Block System Update for Version 1.0.8</title>
<style type="text/css">
<!--
table {
	border:1px solid #000000;
}

table th,td  {
	border:1px solid #000000;
}

// -->
</style>
</head>
<body>
<?php
/**

	Plazieren Sie diese Datei in das Hauptverzeichniss Ihrer Contrexx Installation
	geben Sie die http Adresse zu Ihrem CMS ein
	und klicken Sie anschliessend auf "Update starten".

*/
if (!@include_once('config/configuration.php')) {
	print "Plazieren Sie diese Datei in das Hauptverzeichniss Ihrer Contrexx Installation.";
} else {
?>
<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
<?php
	if (!isset($_POST['doUpdate'])) {
		print "<input type=\"submit\" name=\"doUpdate\" value=\"Update ausführen\" />";
	} else {
		require_once ASCMS_CORE_PATH.'/API.php';
		$errorMsg = '';
		$objDatabase = getDatabaseObject($errorMsg);
		$objUpdate = &new Update();
		$objUpdate->doUpdate();
	}
}
class Update
{
	function doUpdate()
	{
		if ($this->_updateBlockSystem()) {
			print "Das Update wurde erfolgreich ausgeführt!";
		}
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

	function _updateBlockSystem()
	{
		global $objDatabase;
		
		$arrTables = $objDatabase->MetaTables('TABLES');
		if ($arrTables !== false) {
			if (!in_array(DBPREFIX."module_block_rel_lang", $arrTables)) {
				$query = "CREATE TABLE ".DBPREFIX."module_block_rel_lang (`block_id` int(10) unsigned NOT NULL default '0', `lang_id` int(10) unsigned NOT NULL default '0') TYPE=MyISAM";
				if ($objDatabase->Execute($query) === false) {
					return $this->_databaseError($query, $objDatabase->ErrorMsg());
				}
			}
		} else {
			return $this->_databaseError('Could not gather the database tables!', $objDatabase->ErrorMsg());
		}
		
		$objFWLanguage = &new FWLanguage();
		$arrLanguages = &$objFWLanguage->getLanguageArray();
		
		$query = "SELECT id FROM ".DBPREFIX."module_block_blocks";
		if (($objBlock = $objDatabase->Execute($query)) !== false) {
			while (!$objBlock->EOF) {
				foreach ($arrLanguages as $arrLanguage) {
					$query = "SELECT block_id FROM ".DBPREFIX."module_block_rel_lang WHERE lang_id=".$arrLanguage['id']." AND block_id=".$objBlock->fields['id'];
					if (($objLang = $objDatabase->SelectLimit($query, 1)) !== false) {
						if ($objLang->RecordCount() == 0) {
							$query = "INSERT INTO ".DBPREFIX."module_block_rel_lang (`block_id`, `lang_id`) VALUES (".$objBlock->fields['id'].", ".$arrLanguage['id'].")";
							if ($objDatabase->Execute($query) === false) {
								return $this->_databaseError($query, $objDatabase->ErrorMsg());
							}
						}
					} else {
						return $this->_databaseError($query, $objDatabase->ErrorMsg());
					}
				}
				$objBlock->MoveNext();
			}
		} else {
			return $this->_databaseError($query, $objDatabase->ErrorMsg());
		}
		
		return true;
	}
}
?>
</form>
</body>
</html>