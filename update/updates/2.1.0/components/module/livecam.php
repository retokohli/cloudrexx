<?php

function _livecamUpdate()
{
    global $objDatabase;

    try{
        UpdateUtil::table(
            DBPREFIX.'module_livecam',
            array(
                'id'                 => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'primary' => true),
                'currentImagePath'   => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '/webcam/cam1/current.jpg'),
                'archivePath'        => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '/webcam/cam1/archive/'),
                'thumbnailPath'      => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '/webcam/cam1/thumbs/'),
                'maxImageWidth'      => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '400'),
                'thumbMaxSize'       => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '200'),
                'shadowboxActivate'  => array('type' => 'SET(\'1\',\'0\')', 'notnull' => true, 'default' => '1', 'renamefrom' => 'lightboxActivate'),
                'showFrom'           => array('type' => 'INT(14)'),
                'showTill'           => array('type' => 'INT(14)')
            )
        );
    }
    catch (UpdateException $e) {
        return UpdateUtil::DefaultActionHandler($e);
    }

	$defaultFrom = mktime(0, 0);
	$defaultTill = mktime(23, 59);
    //set new default settings
	$query = "UPDATE `".DBPREFIX."module_livecam` SET `showFrom`=$defaultFrom, `showTill`=$defaultTill WHERE 1";
	if ($objDatabase->Execute($query) === false) {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

    return true;
}
?>
