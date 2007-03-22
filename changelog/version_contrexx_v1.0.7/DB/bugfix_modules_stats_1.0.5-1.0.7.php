<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Bugfix für das Statistik Modul</title>
</head>
<body>
<?php
/**

	Plazieren Sie diese Datei in das Hauptverzeichniss Ihrer Contrexx Installation
	und klicken Sie anschliessend auf "Bugfix ausführen".
	
*/
if (!@include_once('config/configuration.php')) {
	print "Plazieren Sie diese Datei in das Hauptverzeichniss Ihrer Contrexx Installation.";
} else {
?>
<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
<?php

	$objBugfix = &new Bugfix();
	if (!isset($_POST['doUpdate'])) {
		print "Dieses Bugfix behebt den Fehler, dass keine Statistiken zur JavaScript Verwendung der Clients erstellt wird.<br />Dies betrifft alle Versionen, die neuer als die Version 1.0.4a sind.<br /><br />";
		print "<input type=\"submit\" name=\"doUpdate\" value=\"Bugfix ausführen\" />";
	} else {
		require_once ASCMS_CORE_PATH.'/API.php';
		
		$errorMsg = '';
		$objDatabase = getDatabaseObject($errorMsg);
		$objBugfix->execute();
	}
}

class Bugfix
{
	var $errorMsg = "";
	
	function execute()
	{
		$this->_fixStatistics();
		if (empty($this->errorMsg)) {
			print "Das Bugfix wurde erfolgreich ausgeführt!";
		} else {
			print $this->errorMsg;
		}
	}
	
	function _fixStatistics()
	{
		global $objDatabase;
		
		$objResult = $objDatabase->Execute("SELECT id, support, count FROM ".DBPREFIX."stats_javascript");
		
		if ($objResult !== false) {
			if ($objResult->RecordCount() == 0) {
				if ($objDatabase->Execute("INSERT INTO `".DBPREFIX."stats_javascript` (`id`, `support`, `count`) VALUES (1, '0', 0)") !== false) {
					if ($objDatabase->Execute("INSERT INTO `".DBPREFIX."stats_javascript` (`id`, `support`, `count`) VALUES (2, '1', 0)") === false) {
						$this->errorMsg .= "Bugfix konnte nicht erfolgreich ausgeführt werden!";
					}
				} else {
					$this->errorMsg .= "Bugfix konnte nicht erfolgreich ausgeführt werden!<br />";
				}
			}
		} else {
			$this->errorMsg .= "Bugfix konnte nicht erfolgreich ausgeführt werden!<br />";
		}
	}
}
?>
</form>
</body>
</html>