<?php
function _calendarUpdate()
{
	global $objDatabase, $_ARRAYLANG;

	$query = "UPDATE `".DBPREFIX."module_calendar_access` SET `type` = 'frontend' WHERE `name` = 'showNote'";
	if ($objDatabase->Execute($query) === false) {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	$arrColumns = $objDatabase->MetaColumns(DBPREFIX.'module_calendar');
	if ($arrColumns === false) {
		setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_calendar'));
		return false;
	}


	$arrNewCols = array(
	   'series_status' => array(
			'type' 		=> 'TINYINT',
			'after' 	=> 'notification_address',
    	),
    	'series_type' => array(
			'type' 		=> 'INT (11)',
			'after' 	=> 'series_status',
    	),
    	'series_pattern_count' => array(
			'type' 		=> 'INT',
			'after' 	=> 'series_type',
    	),
    	'series_pattern_weekday' => array(
			'type' 		=> 'VARCHAR(7)',
			'after' 	=> 'series_pattern_count',
    	),
    	'series_pattern_day' => array(
			'type' 		=> 'INT',
			'after' 	=> 'series_pattern_weekday',
    	),
    	'series_pattern_week' => array(
			'type' 		=> 'INT',
			'after' 	=> 'series_pattern_day',
    	),
    	'series_pattern_month' => array(
			'type' 		=> 'INT',
			'after' 	=> 'series_pattern_week',
    	),
    	'series_pattern_type' => array(
			'type' 		=> 'INT',
			'after' 	=> 'series_pattern_month',
    	),
    	'series_pattern_dourance_type' => array(
			'type' 		=> 'INT',
			'after' 	=> 'series_pattern_type',
    	),
    	'series_pattern_end' => array(
			'type' 		=> 'INT',
			'after' 	=> 'series_pattern_dourance_type',
    	),
    	'series_pattern_begin' => array(
			'type' 		=> 'INT',
			'after' 	=> 'series_pattern_end',
    	),
    	'series_pattern_exceptions' => array(
			'type' 		=> 'longtext',
			'after' 	=> 'series_pattern_begin',
    	)
	);

	foreach ($arrNewCols as $col => $arrAttr) {
		if (!array_key_exists(strtoupper($col), $arrColumns)) {
		    $query = "ALTER TABLE `".DBPREFIX."module_calendar` ADD `".$col."` ".$arrAttr['type']." NOT NULL AFTER `".$arrAttr['after']."`";
		    if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	}


    return true;
}
?>
