<?php

require_once dirname(dirname(dirname(__FILE__))) . '/core/Core/init.php';
init('minimal');
echo marketUpdates();

function marketUpdates() {
    //Update the database changes
    try {
        //update module name 
        \Cx\Lib\UpdateUtil::sql("UPDATE `".DBPREFIX."modules` SET `name` = 'Market' WHERE `id` = 33");
        //update navigation url
        \Cx\Lib\UpdateUtil::sql("UPDATE `".DBPREFIX."backend_areas` SET `uri` = 'index.php?cmd=Market' WHERE `area_id` = 98");
        //Insert component entry
        \Cx\Lib\UpdateUtil::sql("INSERT INTO `".DBPREFIX."component` (`id`, `name`, `type`) VALUES ('33', 'Market', 'module')");
        //update module name for frontend pages
        \Cx\Lib\UpdateUtil::sql("UPDATE `".DBPREFIX."content_page` SET `module` = 'Market' WHERE `module` = 'market'");
    } catch (\Cx\Lib\UpdateException $e) {
        return "Error: $e->sql";
    }
    
    //Update script for moving the folder
    $marketMediaPath = ASCMS_DOCUMENT_ROOT . '/media';
    
    try {
        if (file_exists($marketMediaPath . '/market') && !file_exists($marketMediaPath . '/Market')) {
            \Cx\Lib\FileSystem\FileSystem::makeWritable($marketMediaPath . '/market');
            if (!\Cx\Lib\FileSystem\FileSystem::move($marketMediaPath . '/market', $marketMediaPath . '/Market')) {
                return 'Failed to move the folder from '.$marketMediaPath . '/market to '.$marketMediaPath . '/Market.';
            }
        }
    } catch (\Cx\Lib\FileSystem\FileSystemException $e) {
        return $e->getMessage();
    }
    
    return 'Market updated successfully.';
}