<?php
function _galleryUpdate()
{
	global $objDatabase, $_ARRAYLANG;

	$arrColumns = $objDatabase->MetaColumns(DBPREFIX.'module_gallery_categories');
	if ($arrColumns === false) {
		setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_gallery_categories'));
		return false;
	}

	if (!isset($arrColumns['BACKENDPROTECTED'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_gallery_categories` ADD `backendProtected` INT NOT NULL ;" ;
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (!isset($arrColumns['BACKEND_ACCESS_ID'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_gallery_categories` ADD `backend_access_id` INT NOT NULL ;" ;
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (!isset($arrColumns['FRONTENDPROTECTED'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_gallery_categories` ADD `frontendProtected` INT NOT NULL ;" ;
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if (!isset($arrColumns['FRONTEND_ACCESS_ID'])) {
		$query = "ALTER TABLE `".DBPREFIX."module_gallery_categories` ADD `frontend_access_id` INT NOT NULL ;" ;
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$arrSettings = array(
        '1'  => array( 'name' => 'max_images_upload',        'value' => '10'),
        '2'  => array( 'name' => 'standard_quality',         'value' => '95'),
        '3'  => array( 'name' => 'standard_size_proz',       'value' => '25'),
        '4'  => array( 'name' => 'standard_width_abs',       'value' => '140'),
        '6'  => array( 'name' => 'standard_height_abs',      'value' => '0'),
        '7'  => array( 'name' => 'standard_size_type',       'value' => 'abs'),
        '8'  => array( 'name' => 'validation_show_limit',    'value' => '10'),
        '9'  => array( 'name' => 'validation_standard_type', 'value' => 'all'),
        '11' => array( 'name' => 'show_names',               'value' => 'off'),
        '12' => array( 'name' => 'quality',                  'value' => '95'),
        '13' => array( 'name' => 'show_comments',            'value' => 'off'),
        '14' => array( 'name' => 'show_voting',              'value' => 'off'),
        '15' => array( 'name' => 'enable_popups',            'value' => 'on'),
        '16' => array( 'name' => 'image_width',              'value' => '1200'),
        '17' => array( 'name' => 'paging',                   'value' => '30'),
        '18' => array( 'name' => 'show_latest',              'value' => 'on'),
        '19' => array( 'name' => 'show_random',              'value' => 'on'),
        '20' => array( 'name' => 'header_type',              'value' => 'hierarchy'),
        '21' => array( 'name' => 'show_ext',                 'value' => 'off'),
        '22' => array( 'name' => 'show_file_name',           'value' => 'on'),
        '23' => array( 'name' => 'slide_show',               'value' => 'slideshow'),
        '24' => array( 'name' => 'slide_show_seconds',       'value' => '3')
    );

	foreach ($arrSettings as $id => $arrSetting) {
		$query = "SELECT 1 FROM `".DBPREFIX."module_gallery_settings` WHERE `name`= '".$arrSetting['name']."'" ;
		if (($objRS = $objDatabase->Execute($query)) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
		if($objRS->RecordCount() == 0){
			$query = "INSERT INTO `".DBPREFIX."module_gallery_settings`
							 (`id`, `name`, `value`)
					  VALUES (".$id.", '".$arrSetting['name']."', '".$arrSetting['value']."')" ;
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	}

    return true;
}

