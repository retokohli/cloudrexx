<?php

function _podcastUpdate() {
    global $objDatabase, $_ARRAYLANG, $objUpdate, $_CONFIG;

    //move podcast images directory
    $path = ASCMS_DOCUMENT_ROOT . '/images';
    $oldImagesPath = '/content/podcast';
    $newImagesPath = '/podcast';

    if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '1.2.1')) {
        if (   !file_exists($path . $newImagesPath)
            && file_exists($path . $oldImagesPath)
        ) {
            \Cx\Lib\FileSystem\FileSystem::makeWritable($path . $oldImagesPath);
            if (!\Cx\Lib\FileSystem\FileSystem::copy_folder($path . $oldImagesPath, $path . $newImagesPath)) {
                setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_TO_MOVE_DIRECTORY'], $path . $oldImagesPath, $path . $newImagesPath));
                return false;
            }
        }
        \Cx\Lib\FileSystem\FileSystem::makeWritable($path . $newImagesPath);
        \Cx\Lib\FileSystem\FileSystem::makeWritable($path . $newImagesPath . '/youtube_thumbnails');

        //change thumbnail paths
        $query = "UPDATE `" . DBPREFIX . "module_podcast_medium` SET `thumbnail` = REPLACE(`thumbnail`, '/images/content/podcast/', '/images/podcast/')";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
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



    // only update if installed version is at least a version 2.0.0
    // older versions < 2.0 have a complete other structure of the content page and must therefore completely be reinstalled
    if (!$objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '2.0.0')) {
        try {
            // migrate content page to version 3.0.1
            $search = array(
            '/(.*)/ms',
            );
            $callback = function($matches) {
                $content = $matches[1];
                if (empty($content)) {
                    return $content;
                }

                // add missing placeholder {PODCAST_JAVASCRIPT}
                if (strpos($content, '{PODCAST_JAVASCRIPT}') === false) {
                    $content .= "\n{PODCAST_JAVASCRIPT}";
                }

                // add missing placeholder {PODCAST_PAGING}
                if (strpos($content, '{PODCAST_PAGING}') === false) {
                    $content = preg_replace('/(\s+)(<!--\s+END\s+podcast_media\s+-->)/ms', '$1$2$1<div class="noMedium">$1    {PODCAST_PAGING}$1</div>', $content);
                }

                return $content;
            };

            \Cx\Lib\UpdateUtil::migrateContentPageUsingRegexCallback(array('module' => 'podcast'), $search, $callback, array('content'), '3.0.1');
        } catch (\Cx\Lib\UpdateException $e) {
            return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
        }
    }


    return true;
}
