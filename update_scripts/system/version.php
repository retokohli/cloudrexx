<?php
function _createVersionFile()
{
	$versionTemplate = '[[VERSION_TEMPLATE]]';

	$versionFile = str_replace(
		array('[[CMS_NAME]]', '[[CMS_VERSION]]', '[[CMS_STATUS]]', '[[CMS_EDITION]]', '[[CMS_CODE_NAME]]', '[[CMS_RELEASE_DATE]]'),
		array('%CMS_NAME%', '%CMS_VERSION%', '%CMS_STATUS%', '%CMS_EDITION%', '%CMS_CODE_NAME%', '%CMS_RELEASE_DATE%'),
		$versionTemplate
	);
	
	require_once ASCMS_FRAMEWORK_PATH.'/File.class.php';
	$objFile =& new File();
	
	if (!is_writable(ASCMS_DOCUMENT_ROOT.'/config/version.php')) {
		$objFile->setChmod(ASCMS_DOCUMENT_ROOT.'/config', ASCMS_PATH_OFFSET.'/config', '/version.php');
	}

	if (($fpVersionFile = @fopen(ASCMS_DOCUMENT_ROOT.'/config/version.php', 'wb')) !== false) {
		$status = @fwrite($fpVersionFile, $versionFile);
		@fclose($fpVersionFile);

		if ($status) {
			return true;
		} else {
			print "Die Versionsdatei ".ASCMS_DOCUMENT_ROOT."/config/version.php kann nicht geschrieben werden!<br />";
			print "Setzen Sie die Zugriffsberechtigungen für die Datei ".ASCMS_DOCUMENT_ROOT."/config/version.php auf 777 (Unix) oder vergeben Sie auf diese Datei Schreibrechte (Windows) und laden Sie die Seite neu!";
			return false;
		}
	} else {
		print "Die Versionsdatei ".ASCMS_DOCUMENT_ROOT."/config/version.php kann nicht erstellt werden!<br />";
		print "Setzen Sie die Zugriffsberechtigungen für das Verzeichnis ".ASCMS_DOCUMENT_ROOT."/config/ auf 777 (Unix) oder vergeben Sie auf diese Datei Schreibrechte (Windows) und laden Sie die Seite neu!";
		return false;
	}
}
?>