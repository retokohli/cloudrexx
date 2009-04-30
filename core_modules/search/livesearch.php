<?php
	/**
	 * Standalon search script - handles search in content pages and news article
	 *
	 * @author  <ben@comvation.com>
	 * @copyright Comvation AG <info@comvation.com>
	 */

	require_once("../../config/configuration.php");
	require_once("../../config/set_constants.php");
	require_once("../../core/validator.inc.php");
	require_once(ASCMS_LIBRARY_PATH.'/adodb/adodb.inc.php');
	
	$objDb       = ADONewConnection($_DBCONFIG['dbType']);
	@$objDb->Connect($_DBCONFIG['host'], $_DBCONFIG['user'], $_DBCONFIG['password'], $_DBCONFIG['database']);
	$objDatabase = $objDb;
		
	if (strlen($_GET['st']) > 2) {
		$query     = "SELECT * FROM contrexx_content WHERE content LIKE '%". $_GET['st'] ."%';";
		$objResult = $objDatabase->Execute($query);
		
		if ($objResult !== false) {
			while (!$objResult->EOF) {
				echo '<li><a href="index.php?page='.$objResult->fields['id'].'">» '.$objResult->fields['title'].'</a></li>';
				$objResult->MoveNext();
			}
		}
	}
?>