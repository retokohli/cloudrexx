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

    return true;
}

