<?php
function _calendarUpdate()
{
	global $objDatabase;

    try{
        UpdateUtil::table(
            DBPREFIX.'module_calendar',
            array(
                'id'                                 => array('type' => 'INT(11)', 'notnull' => true, 'primary' => true, 'auto_increment' => true),
                'active'                             => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '1', 'after' => 'id'),
                'catid'                              => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'after' => 'active'),
                'startdate'                          => array('type' => 'INT(14)', 'notnull' => false, 'after' => 'catid'),
                'enddate'                            => array('type' => 'INT(14)', 'notnull' => false, 'after' => 'startdate'),
                'priority'                           => array('type' => 'INT(1)', 'notnull' => true, 'default' => '3', 'after' => 'enddate'),
                'access'                             => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'priority'),
                'name'                               => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => '', 'after' => 'access'),
                'comment'                            => array('type' => 'text', 'after' => 'name'),
                'placeName'                          => array('type' => 'VARCHAR(255)', 'after' => 'comment'),
                'link'                               => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => 'http://', 'after' => 'placeName'),
                'pic'                                => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'link'),
                'attachment'                         => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'pic'),
                'placeStreet'                        => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'attachment'),
                'placeZip'                           => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'placeStreet'),
                'placeCity'                          => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'placeZip'),
                'placeLink'                          => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'placeCity'),
                'placeMap'                           => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'placeLink'),
                'organizerName'                      => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'placeMap'),
                'organizerStreet'                    => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'organizerName'),
                'organizerZip'                       => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'organizerStreet'),
                'organizerPlace'                     => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'organizerZip'),
                'organizerMail'                      => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'organizerPlace'),
                'organizerLink'                      => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'organizerMail'),
                'key'                                => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'organizerLink'),
                'num'                                => array('type' => 'INT(5)', 'notnull' => true, 'default' => '0', 'after' => 'key'),
                'mailTitle'                          => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'num'),
                'mailContent'                        => array('type' => 'text', 'after' => 'mailTitle'),
                'registration'                       => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'mailContent'),
                'groups'                             => array('type' => 'text', 'after' => 'registration'),
                'all_groups'                         => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'groups'),
                'public'                             => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'all_groups'),
                'notification'                       => array('type' => 'INT(1)', 'after' => 'public'),
                'notification_address'               => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'notification'),
                'series_status'                      => array('type' => 'TINYINT(4)', 'after' => 'notification_address'),
                'series_type'                        => array('type' => 'INT(11)', 'after' => 'series_status'),
                'series_pattern_count'               => array('type' => 'INT(11)', 'after' => 'series_type'),
                'series_pattern_weekday'             => array('type' => 'VARCHAR(7)', 'after' => 'series_pattern_count'),
                'series_pattern_day'                 => array('type' => 'INT(11)', 'after' => 'series_pattern_weekday'),
                'series_pattern_week'                => array('type' => 'INT(11)', 'after' => 'series_pattern_day'),
                'series_pattern_month'               => array('type' => 'INT(11)', 'after' => 'series_pattern_week'),
                'series_pattern_type'                => array('type' => 'INT(11)', 'after' => 'series_pattern_month'),
                'series_pattern_dourance_type'       => array('type' => 'INT(11)', 'after' => 'series_pattern_type'),
                'series_pattern_end'                 => array('type' => 'INT(11)', 'after' => 'series_pattern_dourance_type'),
                'series_pattern_begin'               => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'after' => 'series_pattern_end'),
                'series_pattern_exceptions'          => array('type' => 'longtext', 'after' => 'series_pattern_begin')
            ),
            array(
                'name'                               => array('fields' => array('name','comment','placeName'), 'type' => 'FULLTEXT')
            )
        );
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
