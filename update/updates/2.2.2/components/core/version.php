<?php
function _createVersionFile()
{
	global $_ARRAYLANG, $_CORELANG;

	$versionFile = <<<VERSION
<?php
/**
 * Version code
 *
 * Version informations
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team
 * @version     2.1.1
 * @package     contrexx
 * @subpackage  config
 * @todo        Edit PHP DocBlocks!
 */

// status levels:
//	Planning
//	Pre-Alpha
//	Alpha
//	Beta
//	Production/Stable
//	Mature
//	Inactive

\$_CONFIG['coreCmsName']        = "Contrexx® Web Content Management System";
\$_CONFIG['coreCmsVersion']	    = "1.3-dev";
\$_CONFIG['coreCmsStatus']	    = "Unstable";
\$_CONFIG['coreCmsEdition']	    = "Premium";
\$_CONFIG['coreCmsCodeName']    = "None";
\$_CONFIG['coreCmsReleaseDate']	= "-";

if (strstr(str_replace('\\\\\\', '/',__FILE__), \$_SERVER['PHP_SELF'])) {
    header('Content-type: text/html; charset="utf-8"',true);
    echo \$_CONFIG['coreCmsName']
        . ' ' . \$_CONFIG['coreCmsVersion']
        . ' ' . \$_CONFIG['coreCmsEdition']
        . ' ' . \$_CONFIG['coreCmsStatus']
        ;
}

?>
VERSION
;

	require_once ASCMS_FRAMEWORK_PATH.'/File.class.php';
	$objFile =new File();

	if (!is_writable(ASCMS_DOCUMENT_ROOT.'/config/version.php')) {
		$objFile->setChmod(ASCMS_DOCUMENT_ROOT.'/config', ASCMS_PATH_OFFSET.'/config', '/version.php');
	}

	if (($fpVersionFile = @fopen(ASCMS_DOCUMENT_ROOT.'/config/version.php', 'wb')) !== false) {
		$status = @fwrite($fpVersionFile, $versionFile);
		@fclose($fpVersionFile);

		if ($status) {
			return true;
		} else {
			setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_WRITE_VERSION_FILE'], ASCMS_DOCUMENT_ROOT.'/config/version.php'));
			setUpdateMsg(sprintf($_ARRAYLANG['TXT_SET_WRITE_PERMISSON_TO_FILE'], ASCMS_DOCUMENT_ROOT.'/config/version.php', $_CORELANG['TXT_UPDATE_TRY_AGAIN']), 'msg');
			return false;
		}
	} else {
		setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_CREATE_VERSION_FILE'], ASCMS_DOCUMENT_ROOT.'/config/version.php'));
		setUpdateMsg(sprintf($_ARRAYLANG['TXT_SET_WRITE_PERMISSON_TO_DIR'], ASCMS_DOCUMENT_ROOT.'/config/', $_CORELANG['TXT_UPDATE_TRY_AGAIN']), 'msg');
		return false;
	}
}
?>
