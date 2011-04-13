<?php

function _mediaUpdate()
{
    global $_ARRAYLANG, $_CORELANG;

	require_once ASCMS_FRAMEWORK_PATH.'/File.class.php';
	$objFile = new File();

    $paths = glob(ASCMS_DOCUMENT_ROOT.'/media/archive*');
    foreach ($paths as $path) {
        $path = "$path/";
        $web_path = preg_replace("#".ASCMS_DOCUMENT_ROOT."/media/#", ASCMS_PATH_OFFSET . '/media/', $path);
        $status = $objFile->delFile($path, $web_path, '.htaccess');

        if ($status == 'error') {
            setUpdateMsg(sprintf($_ARRAYLANG['TXT_SET_WRITE_PERMISSON_TO_DIR_AND_CONTENT'], "<pre>$web_path</pre>", $_CORELANG['TXT_UPDATE_TRY_AGAIN']));
            return false;
        }
    }

    try {
        UpdateUtil::table(
            DBPREFIX.'module_mediadir_settings',
            array(
                'id'         => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true),
                'name'       => array('type' => 'VARCHAR(100)', 'after' => 'id'),
                'value'      => array('type' => 'VARCHAR(255)', 'after' => 'name')
            ),
            array(
                'name'       => array('fields' => array('name'), 'type' => 'UNIQUE')
            )
        );

        $rs = UpdateUtil::sql('SELECT COUNT(1) AS c FROM '.DBPREFIX.'module_mediadir_settings');
        if($rs->fields['c'] == 0) {//table empty => insert default settings
            UpdateUtil::sql('INSERT INTO '.DBPREFIX.'module_mediadir_settings VALUES
                ("media1_frontend_changable","off"), ("media1_frontend_managable","off"),
                ("media2_frontend_changable","off"), ("media2_frontend_managable","off"),
                ("media3_frontend_changable","off"), ("media3_frontend_managable","off"),
                ("media4_frontend_changable","off"), ("media4_frontend_managable","off");'
            );
        }
    }
    catch (UpdateException $e) {
        // we COULD do something else here..
        return UpdateUtil::DefaultActionHandler($e);
    }


    UpdateUtil::sql(
      
    return true;
}

