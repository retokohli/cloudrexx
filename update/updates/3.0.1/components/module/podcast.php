<?php

function _podcastUpdate() {
    global $objDatabase, $_ARRAYLANG;

    require_once ASCMS_FRAMEWORK_PATH . '/File.class.php';

    //move podcast images directory
    $path = ASCMS_DOCUMENT_ROOT . '/images';
    $oldImagesPath = '/content/podcast';
    $newImagesPath = '/podcast';

    \Cx\Lib\FileSystem\FileSystem::makeWritable($path . '/content/podcast');
    if (file_exists($path . $oldImagesPath)) {
        if (!\Cx\Lib\FileSystem\FileSystem::copy_folder($path . $oldImagesPath, $path . $newImagesPath)) {
            setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_TO_MOVE_DIRECTORY'], $path . $oldImagesPath, $path . $newImagesPath));
        }
    }
    \Cx\Lib\FileSystem\FileSystem::makeWritable($path . '/podcast');
    \Cx\Lib\FileSystem\FileSystem::makeWritable($path . '/podcast/youtube_thumbnails');

    //change thumbnail paths
    $query = "UPDATE `" . DBPREFIX . "module_podcast_medium` SET `thumbnail` = REPLACE(`thumbnail`, '/images/content/podcast/', '/images/podcast/')";
    if ($objDatabase->Execute($query) === false) {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }

    //set new default settings
    $query = "UPDATE `" . DBPREFIX . "module_podcast_settings` SET `setvalue` = '50' WHERE `setname` = 'thumb_max_size' AND `setvalue` = ''";
    if ($objDatabase->Execute($query) === false) {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }
    $query = "UPDATE `" . DBPREFIX . "module_podcast_settings` SET `setvalue` = '85' WHERE `setname` = 'thumb_max_size_homecontent' AND `setvalue` = ''";
    if ($objDatabase->Execute($query) === false) {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }

    return true;
}

?>