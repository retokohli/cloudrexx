<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Datenbanktabellen umbenennen</title>
</head>
<body>
<?php
/**

	Plazieren Sie diese Datei in das Hauptverzeichniss Ihrer Contrexx Installation
	und klicken Sie anschliessend auf "Update ausführen".

*/
if (!@include_once('config/configuration.php')) {
	print "Plazieren Sie diese Datei in das Hauptverzeichniss Ihrer Contrexx Installation.";
} else {
	require_once ASCMS_CORE_PATH.'/API.php';
	
	$errorMsg = '';
	$old_prefix;
	$new_prefix = 'contrexx_';
	$arrRenameTables = array();
	$arrDontRenameTables = array();
	$doRename = false;
	
	
	if (isset($_POST['prefix']) && !empty($_POST['prefix'])) {
		$old_prefix = get_magic_quotes_gpc() ? stripslashes($_POST['prefix']) : $_POST['prefix'];
	}
	if (isset($_POST['rename']) && !empty($_POST['rename'])) {
		$doRename = true;
	}
	
	if (!empty($old_prefix)) {
		$objDatabase = getDatabaseObject($errorMsg);
		
		$objDatabase->debug = false;
		
		$arrTables = $objDatabase->MetaTables('TABLES');
		if ($arrTables !== false) {
			foreach ($arrTables as $table) {
				if (preg_match('/^'.$old_prefix.'(.*)/', $table, $arrMatches)) {
					if ($doRename) {
						if ($objDatabase->Execute("ALTER TABLE `".$table."` RENAME `".$new_prefix.$arrMatches[1]."`") === false) {
							$errorMsg .= "Ein Fehler ist während dem Umbenennen der Tabelle <b>".$table."</b> aufgetreten!<br />\n";
						}
					} else {
						array_push($arrRenameTables, $table);
					}
				} else {
					array_push($arrDontRenameTables, $table);
				}
			}
		}
		 if ($doRename) {
		 	if (empty($errorMsg)) {
		 		print "Die Tabellen wurden erfolgreich umbenannt!<br />";
		 	} else {
				print $errorMsg;
		 	}
		} else {
			if (count($arrRenameTables)>0) {
				print "<b>Die folgenden Tabellen werden umbenannt (Total: ".count($arrRenameTables)."):</b><br />\n";
				foreach ($arrRenameTables as $table) {
					print $table."<br />\n";
				}
			}
			
			if (count($arrDontRenameTables)>0) {
				print "<b>Die folgenden Tabellen werden ignoriert (Total: ".count($arrDontRenameTables)."):</b><br />\n";
				foreach ($arrDontRenameTables as $table) {
					print $table."<br />\n";
				}
			}
		}
	}
	?>
	<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
	<?php
	if (empty($old_prefix)) {
	?>
		aktuelles Tabellenprefix:<input type="text" name="prefix" value="astalavista_" />
		<input type="submit" name="setPrefix" value="weiter" />
	<?php
	} else {
	?>
	<input type="hidden" name="prefix" value="<?php echo $old_prefix;?>" />
	<input type="submit" name="rename" value="Datenbank Tabellen umbenennen" />
	<?php
	}
}
?>
</form>
</body>
</html>