<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Update from Version 1.0.8/1.0.9 to DEV</title>
</head>
<body>
<?php
/**

	Plazieren Sie diese Datei in das Hauptverzeichniss Ihrer Contrexx Installation
	und klicken Sie anschliessend auf "Update starten".

*/
error_reporting(E_ALL);ini_set('display_errors', 1);
if (!@include_once('config/configuration.php')) {
	die("Plazieren Sie diese Datei in das Hauptverzeichniss Ihrer Contrexx Installation.");
} elseif (!@include_once(ASCMS_CORE_PATH.'/API.php')) {
	die("Die Datei ".ASCMS_CORE_PATH."/API.php fehlt oder kann nicht geladen werden!");
}

if (isset($_SYSCONFIG)) {
	foreach ($_SYSCONFIG as $sysconfigKey => $sysconfValue) {
		$_CONFIG[$sysconfigKey] = $sysconfValue;
	}
}

?>
<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
<?php
if (!isset($_POST['doUpdate'])) {
	print "<input type=\"submit\" name=\"doUpdate\" value=\"Update ausführen\" />";

} else {
	$errorMsg = '';
	$_arrSuccessMsg = array();
	$objDatabase = getDatabaseObject($errorMsg);
	$objDatabase->debug=false;

	doUpdate();
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

function doUpdate()
{
	$arrDirs = array('core', 'core_module', 'module');
	$updateStatus = true;

	foreach ($arrDirs as $dir) {
		$dh = opendir(ASCMS_DOCUMENT_ROOT.'/update_scripts/components/'.$dir);
		while (($file = readdir($dh)) !== false) {
			if (substr($file, -4) == '.php') {
				require_once(ASCMS_DOCUMENT_ROOT.'/update_scripts/components/'.$dir.'/'.$file);
				$function = '_'.substr($file, 0, -4).'Update';
				if (!$function()) {
					$updateStatus = false;
					break 2;
				}
			}
		}
		closedir($dh);
	}

	if ($updateStatus && _updateSettings() && _updateModules() && _updateBackendAreas() && _updateModuleRepository() && _createVersionFile()) {
		_showSuccessMsg();
	}
}

function _showSuccessMsg()
{
	global $_arrSuccessMsg;

	print "<strong>Das Update wurde erfolgreich ausgeführt!</strong><br /><br />";
	print implode('<br />', $_arrSuccessMsg);
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

?>
</form>
</body>
</html>