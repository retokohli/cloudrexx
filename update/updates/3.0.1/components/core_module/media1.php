<?php

function _media1Update()
{
    global $_ARRAYLANG, $_CORELANG;

    /*	require_once ASCMS_FRAMEWORK_PATH.'/File.class.php';
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
        }*/

    try {
        UpdateUtil::table(
            DBPREFIX.'module_media_settings',
            array(
                  'name'       => array('type' => 'VARCHAR(50)'),
                  'value'      => array('type' => 'VARCHAR(250)', 'after' => 'name')
                  ),
            array(
                  'name'       => array('fields' => array('name'))
                  ),
            'InnoDB'
        );
        $arrValues = array(
                           array("media1_frontend_changable","off"),
                           array("media2_frontend_changable","off"),
                           array("media3_frontend_changable","off"),
                           array("media4_frontend_changable","off"),
                           array("media1_frontend_managable","off"),
                           array("media2_frontend_managable","off"),
                           array("media3_frontend_managable","off"),
                           array("media4_frontend_managable","off")
                           );

        for($i = 0; $i < count($arrValues); $i++) {
            $rs = UpdateUtil::sql('SELECT 1 FROM '.DBPREFIX.'module_media_settings WHERE name="'.$arrValues[$i][0].'";');
            if($rs->EOF) {
                UpdateUtil::sql('INSERT INTO '.DBPREFIX.'module_media_settings VALUES ("'.$arrValues[$i][0].'","'.$arrValues[$i][1].'")');
            }
        }
    }
    catch (UpdateException $e) {
        // we COULD do something else here..
        return UpdateUtil::DefaultActionHandler($e);
    }
      
    return true;
}

