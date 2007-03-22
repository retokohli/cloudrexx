<?php
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
	function Update()
	{
		$this->__constructor();
	}

	function doUpdate()
	{
		global $objDatabase;

		$query = "SELECT themesid, print_themes_id FROM ".DBPREFIX."languages WHERE is_default='true'";
		$objResult = $objDatabase->SelectLimit($query, 1);

		if ($objResult !== false) {
			$themesId = $objResult->fields['themesid'];
			$printThemesId = $objResult->fields['print_themes_id'];

			$query = "INSERT INTO ".DBPREFIX."languages VALUES ('', 'ru', 'Russian', 'utf-8', ".$themesId.", ".$printThemesId.", 0, 0, 'false')";
			$objDatabase->Execute($query);
		}
	}
}
?>
