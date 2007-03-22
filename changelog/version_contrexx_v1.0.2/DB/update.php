<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Database Update from Version 1.0.2 to 1.0.3</title>
</head>
<body>
Plazieren Sie diese Datei in das Hauptverzeichniss Ihrer Contrexx Installation<br />
und klicken Sie anschliessend auf "Update ausführen".<br /><br />
<form action="update.php" method="post">
<input type="submit" name="doUpdate" value="Update ausführen" />
</form>
</body>
</html>
<?php
/**

	Plazieren Sie diese Datei in das Hauptverzeichniss Ihrer Contrexx Installation
	und klicken Sie anschliessend auf "Update ausführen".

*/
if (!isset($_POST['doUpdate'])) {
	exit;
}
require_once 'config/configuration.php';
require_once ASCMS_CORE_PATH.'/API.php';

$errorMsg = '';
$objDatabase = getDatabaseObject($errorMsg);

$objDatabase->debug = true;

$dynamic_right_id = 1;

// check if the update has already been executed
$arrTables = $objDatabase->MetaTables('TABLES');
if ($arrTables === false) {
	die("Das Update konnte nicht durchgeführt werden, da nicht überprüft werden konnte, welche Schritte des Updates bereits durchgeführt wurden!");
}


// ***************************************************************************
// update backend_areas table
// ***************************************************************************

$arrColumns = $objDatabase->MetaColumnNames(DBPREFIX."backend_areas");
if (!in_array('access_id', $arrColumns)) {
	print "Modifiziere Tabelle '".DBPREFIX."backend_areas'<br />";
	
	if ($objDatabase->Execute("ALTER TABLE `".DBPREFIX."backend_areas` ADD `access_id` INT( 11 ) UNSIGNED NOT NULL") !== false) {
		
		$objResult = $objDatabase->Execute("SELECT area_id FROM ".DBPREFIX."backend_areas");
		
		if ($objResult !== false) {
			while (!$objResult->EOF) {
				$objDatabase->Execute("UPDATE ".DBPREFIX."backend_areas SET access_id=".$objResult->fields['area_id']." WHERE area_id=".$objResult->fields['area_id']);
				$objResult->MoveNext();
			}
		}
	} else {
		print "Fehler beim Modifizieren der Tabelle '".DBPREFIX."backend_areas'!<br />";
	}
}


// ***************************************************************************
// create table group_static_rights
// ***************************************************************************

if (!in_array(DBPREFIX."access_group_static_ids", $arrTables)) {
	print "Erstelle Tabelle '".DBPREFIX."access_group_static_ids'<br />";

	if ($objDatabase->Execute("CREATE TABLE `".DBPREFIX."access_group_static_ids` (
			`access_id` INT( 11 ) UNSIGNED NOT NULL ,
			`group_id` INT( 11 ) UNSIGNED NOT NULL
			)") === false) {
		die;
	}
	
	$objResult = $objDatabase->Execute("SELECT group_id, area_ids FROM ".DBPREFIX."group_backend_permissions");
	
	if ($objResult !== false) {
		while (!$objResult->EOF) {
			
			$arrAreaIds = explode(",", $objResult->fields['area_ids']);
			
			foreach ($arrAreaIds as $id) {
				if ($id != 0) {
					$objDatabase->Execute("INSERT INTO ".DBPREFIX."access_group_static_ids (`access_id`, `group_id`) VALUES (".$id.", ".$objResult->fields['group_id'].")");
				}
			}
			
			$objResult->MoveNext();
		}
		
		print "Lösche Tabelle '".DBPREFIX."group_backend_permissions'<br />";
		$objDatabase->Execute("DROP TABLE ".DBPREFIX."group_backend_permissions");
	}
}

// ***************************************************************************
// create table group_dynamic_right
// ***************************************************************************

if (!in_array(DBPREFIX."access_group_dynamic_ids", $arrTables)) {
	print "Erstelle Tabelle '".DBPREFIX."access_group_dymaic_ids'<br />";
	
	if ($objDatabase->Execute("CREATE TABLE `".DBPREFIX."access_group_dynamic_ids` (
			`access_id` INT( 11 ) UNSIGNED NOT NULL ,
			`group_id` INT( 11 ) UNSIGNED NOT NULL
			)") === false) {
	die("Fehler beim Erstellen der Tabelle '".DBPREFIX."access_group_dymaic_ids'!<br />");
	}
	
	$objResult = $objDatabase->Execute("SELECT catid, frontend_groups, backend_groups FROM ".DBPREFIX."content_navigation WHERE frontend_groups != '' OR backend_groups!=''");
	
	if ($objResult !== false) {
		while (!$objResult->EOF) {
			if (!empty($objResult->fields['frontend_groups'])) {
				$arrContentNavigation[$objResult->fields['catid']]['frontend'] = explode(",",$objResult->fields['frontend_groups']);
			}
			if (!empty($objResult->fields['backend_groups'])) {
				$arrContentNavigation[$objResult->fields['catid']]['backend'] = explode(",",$objResult->fields['backend_groups']);
			}
			$objResult->MoveNext();
		}
		
		print "Modifiziere Tabelle '".DBPREFIX."content_navigation'<br />";
		
		$objDatabase->Execute("ALTER TABLE `".DBPREFIX."content_navigation` DROP `frontend_groups`");
		$objDatabase->Execute("ALTER TABLE `".DBPREFIX."content_navigation` DROP `backend_groups`");
		$objDatabase->Execute("ALTER TABLE `".DBPREFIX."content_navigation` ADD `frontend_access_id` INT( 11 ) UNSIGNED NOT NULL AFTER `protected`");
		$objDatabase->Execute("ALTER TABLE `".DBPREFIX."content_navigation` ADD `backend_access_id` INT( 11 ) UNSIGNED NOT NULL AFTER `frontend_access_id`");
		
		if (is_array($arrContentNavigation)) {
			foreach ($arrContentNavigation as $id => $arrGroups) {
				if (isset($arrGroups['frontend'])) {
					foreach ($arrGroups['frontend'] as $groupId) {
						$objDatabase->Execute("INSERT INTO ".DBPREFIX."access_group_dynamic_ids (`access_id`,`group_id`) VALUES (".$dynamic_right_id.", ".$groupId.")");
					}
					$objDatabase->Execute("UPDATE ".DBPREFIX."content_navigation SET frontend_access_id=".$dynamic_right_id." WHERE catid=".$id);
					$dynamic_right_id++;
				}
				
				if (isset($arrGroups['backend'])) {
					foreach ($arrGroups['backend'] as $groupId) {
						$objDatabase->Execute("INSERT INTO ".DBPREFIX."access_group_dynamic_ids (`access_id`,`group_id`) VALUES (".$dynamic_right_id.", ".$groupId.")");
					}
					$objDatabase->Execute("UPDATE ".DBPREFIX."content_navigation SET backend_access_id=".$dynamic_right_id." WHERE catid=".$id);
					$dynamic_right_id++;
				}
			}
		}
		
		$objDatabase->Execute("INSERT INTO ".DBPREFIX."settings (`setname`,`setvalue`,`setmodule`) VALUES ('lastAccessId', ".$dynamic_right_id.", 1)");
	}
}

if (!in_array(DBPREFIX."access_users", $arrTables)) {
	print "Benenne Tabelle '".DBPREFIX."users' in '".DBPREFIX."access_users' um.<br />";
	$objDatabase->Execute("ALTER TABLE `".DBPREFIX."users` RENAME `".DBPREFIX."access_users`");
}
if (!in_array(DBPREFIX."access_user_groups", $arrTables)) {
	print "Benenne Tabelle '".DBPREFIX."groups' in '".DBPREFIX."access_user_groups' um.<br />";
	$objDatabase->Execute("ALTER TABLE `".DBPREFIX."user_groups` RENAME `".DBPREFIX."access_user_groups`");
}

print "Update-Prozess beendet";
?>