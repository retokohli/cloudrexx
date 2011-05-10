<?php
function _galleryUpdate()
{
	global $objDatabase, $_ARRAYLANG;

    try{
        UpdateUtil::table(
            DBPREFIX.'module_gallery_categories',
            array(
                'id'                     => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'pid'                    => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0'),
                'sorting'                => array('type' => 'INT(6)',  'notnull' => true, 'default' => '0'),
                'status'                 => array('type' => 'SET(\'0\',\'1\')', 'notnull' => true, 'default' => '1'),
                'comment'                => array('type' => 'SET(\'0\',\'1\')', 'notnull' => true, 'default' => '0'),
                'voting'                 => array('type' => 'SET(\'0\',\'1\')', 'notnull' => true, 'default' => '0'),
                'backendProtected'       => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0'),
                'backend_access_id'      => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0'),
                'frontendProtected'      => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0'),
                'frontend_access_id'     => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0')
            )
        );
        UpdateUtil::table(

            DBPREFIX.'module_gallery_pictures',
            array(
                'id'                     => array('type' => 'INT(11)',          'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'catid'                  => array('type' => 'INT(11)',          'notnull' => true, 'default' => '0'),
                'validated'              => array('type' => 'SET(\'0\',\'1\')', 'notnull' => true, 'default' => '0'),
                'status'                 => array('type' => 'SET(\'0\',\'1\')', 'notnull' => true, 'default' => '1'),
                'catimg'                 => array('type' => 'SET(\'0\',\'1\')', 'notnull' => true, 'default' => '0'),
                'sorting'                => array('type' => 'INT(6) UNSIGNED',  'notnull' => true, 'default' => '999'),
                'size_show'              => array('type' => 'SET(\'0\',\'1\')', 'notnull' => true, 'default' => '1'),
                'path'                   => array('type' => 'TEXT',             'notnull' => true),
                'link'                   => array('type' => 'TEXT',             'notnull' => true),
                'lastedit'               => array('type' => 'INT(14)',          'notnull' => true, 'default' => '0'),
                'size_type'              => array('type' =>"SET('abs', 'proz')",'notnull' => true, 'default' => 'proz'),
                'size_proz'              => array('type' => "INT(3)",           'notnull' => true, 'default' => '0'),
                'size_abs_h'             => array('type' => 'INT(11)',          'notnull' => true, 'default' => '0'),
                'size_abs_w'             => array('type' => 'INT(11)',          'notnull' => true, 'default' => '0'),
                'quality'                => array('type' => 'TINYINT(3)',       'notnull' => true, 'default' => '0')
            ),
            array(
                'galleryPicturesIndex' => array('type' => 'FULLTEXT', 'fields' => array('path'))
            )
        );
    }
    catch (UpdateException $e) {
        return UpdateUtil::DefaultActionHandler($e);
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
			$query = "INSERT IGNORE INTO `".DBPREFIX."module_gallery_settings`
							 (`id`, `name`, `value`)
					  VALUES (".$id.", '".$arrSetting['name']."', '".$arrSetting['value']."')" ;
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	}

    return true;
}

