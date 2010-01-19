<?php
function _calendarUpdate()
{
	global $objDatabase, $_ARRAYLANG;

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

    try{
        // delete obsolete table  contrexx_module_calendar_access
        UpdateUtil::drop_table(DBPREFIX.'module_calendar_access');
    }
    catch (UpdateException $e) {
        // we COULD do something else here..
        DBG::trace();
        return UpdateUtil::DefaultActionHandler($e);
    }

    //2.1.1


    $query = "SELECT status FROM ".DBPREFIX."modules WHERE id='21'";
	$objResultCheck = $objDatabase->SelectLimit($query, 1);

	if ($objResultCheck !== false) {
	    if($objResultCheck->fields['status'] == 'y') {
	        $calendarStatus = true;
	    } else {
            $calendarStatus = false;
	    }
	} else {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	if($calendarStatus) {
	    $arrContentSites = array();

	    $arrContentSites[0]['module_id'] = 21;
	    $arrContentSites[0]['cmd'] = '';
	    $arrContentSites[1]['module_id'] = 21;
	    $arrContentSites[1]['cmd'] = 'eventlist';
	    $arrContentSites[2]['module_id'] = 21;
	    $arrContentSites[2]['cmd'] = 'boxes';


	    //insert new link placeholder in content, if module is active
	    foreach ($arrContentSites as $key => $siteArray) {
    	    $arrContentCatIds = array();

	        $module_id = $siteArray['module_id'];
	        $cmd = $siteArray['cmd'];

    	    $query = "SELECT catid FROM ".DBPREFIX."content_navigation WHERE module='".$module_id."' AND cmd='".$cmd."'";
        	$objResultCatId = $objDatabase->Execute($query);

        	if ($objResultCatId !== false) {
        		while (!$objResultCatId->EOF) {
        		    $arrContentCatIds[] = $objResultCatId->fields['catid'];
        		    $objResultCatId->MoveNext();
        		}
        	} else {
        		return _databaseError($query, $objDatabase->ErrorMsg());
        	}

        	foreach ($arrContentCatIds as $key => $catId) {
        	    $query = "SELECT content FROM ".DBPREFIX."content WHERE id='".$catId."'";
            	$objResultContent = $objDatabase->SelectLimit($query, 1);

            	if ($objResultContent !== false) {
            		$oldColntent  = $objResultContent->fields['content'];
            		$newContent   = str_replace('<a href="index.php?section=calendar&amp;cmd=event&amp;id={CALENDAR_ID}">{CALENDAR_TITLE}</a>', '{CALENDAR_DETAIL_LINK}', $oldColntent);

            		$query = "UPDATE ".DBPREFIX."content SET content='".addslashes($newContent)."' WHERE id='".$catId."'";
            	    $objResultUpdate = $objDatabase->Execute($query);

            	    if ($objResultUpdate === false) {
        				return _databaseError($query, $objDatabase->ErrorMsg());
        			}
            	} else {
            		return _databaseError($query, $objDatabase->ErrorMsg());
            	}
        	}
	    }



    	//insert new BACKLIN placeholder in sign form
    	$arrContentCatIds = array();

    	$query = "SELECT catid FROM ".DBPREFIX."content_navigation WHERE module='21' AND cmd='sign'";
    	$objResultCatId = $objDatabase->Execute($query);

    	if ($objResultCatId !== false) {
    		while (!$objResultCatId->EOF) {
    		    $arrContentCatIds[] = $objResultCatId->fields['catid'];
    		    $objResultCatId->MoveNext();
    		}
    	} else {
    		return _databaseError($query, $objDatabase->ErrorMsg());
    	}

    	foreach ($arrContentCatIds as $key => $catId) {
    	    $query = "SELECT content FROM ".DBPREFIX."content WHERE id='".$catId."'";
        	$objResultContent = $objDatabase->SelectLimit($query, 1);

        	if ($objResultContent !== false) {
        		$oldColntent  = $objResultContent->fields['content'];
        		$newContent   = str_replace('<input type="hidden" name="id" value="{CALENDAR_NOTE_ID}" />', '<input type="hidden" name="id" value="{CALENDAR_NOTE_ID}" /><input type="hidden" name="date" value="{CALENDAR_NOTE_DATE}" />', $oldColntent);

        		$newContent   = str_replace('<a href="index.php?section=calendar&amp;id={CALENDAR_NOTE_ID}">{TXT_CALENDAR_BACK}</a>', '{CALENDAR_LINK_BACK}', $newContent);

        		$query = "UPDATE ".DBPREFIX."content SET content='".addslashes($newContent)."' WHERE id='".$catId."'";
        	    $objResultUpdate = $objDatabase->Execute($query);

        	    if ($objResultUpdate === false) {
    				return _databaseError($query, $objDatabase->ErrorMsg());
    			}
        	} else {
        		return _databaseError($query, $objDatabase->ErrorMsg());
        	}
    	}

	}

    try{
        // delete obsolete table  contrexx_module_calendar_access
        UpdateUtil::drop_table(DBPREFIX.'module_calendar_access');

        UpdateUtil::table(
            DBPREFIX.'module_calendar_form_data',
            array(
                'reg_id'     => array('type' => 'INT(10)', 'notnull' => true, 'default' => '0'),
                'field_id'   => array('type' => 'INT(10)', 'notnull' => true, 'default' => '0'),
                'data'       => array('type' => 'TEXT', 'notnull' => true)
            )
        );

        UpdateUtil::table(
            DBPREFIX.'module_calendar_form_fields',
            array(
                'id'         => array('type' => 'INT(7)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'note_id'    => array('type' => 'INT(10)', 'notnull' => true, 'default' => '0'),
                'name'       => array('type' => 'TEXT'),
                'type'       => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0'),
                'required'   => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0'),
                'order'      => array('type' => 'INT(3)', 'notnull' => true, 'default' => '0'),
                'key'        => array('type' => 'INT(7)', 'notnull' => true, 'default' => '0')
            )
        );

        UpdateUtil::table(
            DBPREFIX.'module_calendar_registrations',
            array(
                'id'             => array('type' => 'INT(7)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'note_id'        => array('type' => 'INT(7)', 'notnull' => true, 'default' => '0'),
                'note_date'      => array('type' => 'INT(11)', 'notnull' => true, 'after' => 'note_id'),
                'time'           => array('type' => 'INT(14)', 'notnull' => true, 'default' => '0'),
                'host'           => array('type' => 'VARCHAR(255)'),
                'ip_address'     => array('type' => 'VARCHAR(15)'),
                'type'           => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0')
            )
        );
    }
    catch (UpdateException $e) {
        // we COULD do something else here..
        DBG::trace();
        return UpdateUtil::DefaultActionHandler($e);
    }

    return true;
}
?>
