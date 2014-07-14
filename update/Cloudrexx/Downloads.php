<?php

require_once dirname(dirname(dirname(__FILE__))) . '/core/Core/init.php';
init('minimal');
echo downloadsUpdate();

function downloadsUpdate() {
    try {
        //update module name 
        \Cx\Lib\UpdateUtil::sql("UPDATE `".DBPREFIX."modules` SET `name` = 'Downloads' WHERE `id` = 53");
        //update navigation url
        \Cx\Lib\UpdateUtil::sql("UPDATE `".DBPREFIX."backend_areas` SET `uri` = 'index.php?cmd=Downloads' WHERE `area_id` = 132");
        //Insert component entry
        \Cx\Lib\UpdateUtil::sql("INSERT INTO `".DBPREFIX."component` (`id`, `name`, `type`) VALUES ('53', 'Downloads', 'module')");
        //update module name for frontend pages
        \Cx\Lib\UpdateUtil::sql("UPDATE `".DBPREFIX."content_page` SET `module` = 'Downloads' WHERE `module` = 'downloads'");
        //following queries for changing the path from images/downloads into images/Downloads
        \Cx\Lib\UpdateUtil::sql("UPDATE `".DBPREFIX."module_downloads_download` 
                                        SET `image` = REPLACE(`image`, 'images/downloads', 'images/Downloads')
                                        WHERE `image` LIKE ('".ASCMS_PATH_OFFSET."/images/downloads%')");
        \Cx\Lib\UpdateUtil::sql("UPDATE `".DBPREFIX."module_downloads_category` 
                                        SET `image` = REPLACE(`image`, 'images/downloads', 'images/Downloads')
                                        WHERE `image` LIKE ('".ASCMS_PATH_OFFSET."/images/downloads%')");
        \Cx\Lib\UpdateUtil::sql("UPDATE `".DBPREFIX."module_downloads_download_locale` 
                                        SET `source` = REPLACE(`source`, 'images/downloads', 'images/Downloads')
                                        WHERE `source` LIKE ('".ASCMS_PATH_OFFSET."/images/downloads%')");
    } catch (\Cx\Lib\UpdateException $e) {
        return "Error: $e->sql";
    }
    
    $sourcePath = ASCMS_DOCUMENT_ROOT . '/images/downloads';
    $targetPath = ASCMS_DOCUMENT_ROOT . '/images/Downloads';
    try {
        if (file_exists($sourcePath) && !file_exists($targetPath)) {
            \Cx\Lib\FileSystem\FileSystem::makeWritable($sourcePath);
            if (!\Cx\Lib\FileSystem\FileSystem::move($sourcePath, $targetPath)) {
                return 'Failed to Moved the files from '.$sourcePath.' to '.$targetPath.'.<br>';
            }
        } 
    } catch (\Cx\Lib\FileSystem\FileSystemException $e) {
        return $e->getMessage();
    }
    return 'Downloads Updated successfully';
}