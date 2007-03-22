<?php

if (!@include_once('config/configuration.php')) {
 print "Plazieren Sie diese Datei in das Hauptverzeichniss Ihrer Contrexx Installation.";
} else {
  require_once ASCMS_CORE_PATH.'/API.php';

  $errorMsg = '';
  $objDatabase = getDatabaseObject($errorMsg);
}

// Neue Start - und Enddatumsfelder
$date_query = "ALTER TABLE `contrexx_module_calendar` ADD `startdate` INT( 14 ) NOT NULL AFTER `catid` ,ADD `enddate` INT( 14 ) NOT NULL AFTER `startdate` ;";
if ($objDatabase->Execute($date_query) == false) {
	print 'error altering: '.$objDatabase->ErrorMsg().'<br />';
}

// Active Feld
$active_query = "ALTER TABLE `contrexx_module_calendar` ADD `active` TINYINT( 1 ) DEFAULT '1' NOT NULL AFTER `id` ;";
if ($objDatabase->Execute($active_query) == false) {
	print 'error altering: '.$objDatabase->ErrorMsg().'<br />';
}

// Volltextsuche
$fulltext_query = "ALTER TABLE `contrexx_module_calendar` ADD FULLTEXT (name, `comment`, place);";
if ($objDatabase->Execute($fulltext_query) == false) {
	print 'error altering: '.$objDatabase->ErrorMsg().'<br />';
}

// Konfigurationsvariablen
$insert_query = "INSERT INTO `contrexx_settings` ( `setid` , `setname` , `setvalue` , `setmodule` ) VALUES ('', 'calendarheadlinescat', '0', '21');";
if ($objDatabase->Execute($insert_query) == false) {
	print 'error inserting: '.$objDatabase->ErrorMsg().'<br />';
}

$insert_query = "INSERT INTO `contrexx_settings` ( `setid` , `setname` , `setvalue` , `setmodule` ) VALUES ('', 'calendardefaultcount', '10', '21');";
if ($objDatabase->Execute($insert_query) == false) {
	print 'error inserting: '.$objDatabase->ErrorMsg().'<br />';
}

$select = "SELECT id, date, time, end_date, end_time  FROM ".DBPREFIX."module_calendar";
$objResult = $objDatabase->Execute($select);

if (!$objResult) {
	print $objDatabase->ErrorMsg();
} else {
	// Geht jeden Datensatz durch und ändert die Zeit
	while (!$objResult->EOF) {
	    $date = explode("/", $objResult->fields['date']);
	    $hours = substr($objResult->fields['time'], 0, 2);
	    $minutes = substr($objResult->fields['time'], 2, 2);

	    $end_date = explode("/", $objResult->fields['end_date']);
	    $end_hours = substr($objResult->fields['end_time'], 0, 2);
	    $end_minutes = substr($objResult->fields['end_time'], 2, 2);

	    $startdate = mktime($hours, $minutes, 0, $date[0], $date[1], $date[2]);
	    $enddate = mktime($end_hours, $end_minutes, 0, $end_date[0], $end_date[1], $end_date[2]);

	    $update = "UPDATE ".DBPREFIX."module_calendar SET
	               startdate = '$startdate', enddate = '$enddate'
	               WHERE id = '{$objResult->fields['id']}'";
	    if ($objDatabase->Execute($update) == false) {
	    	print $objDatabase->ErrorMsg();
	    }

	    $objResult->MoveNext();
	}

	// Dropt die alten Felder
	$drop_query = "ALTER TABLE `contrexx_module_calendar` DROP `date` ,DROP `time` ,DROP `end_date` ,DROP `end_time` ,DROP `sort` ;";
	if ($objDatabase->Execute($drop_query) == false) {
		print 'error dropping: '.$objDatabase->ErrorMsg().'<br />';
	}
}