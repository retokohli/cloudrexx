<?php

require_once dirname(dirname(dirname(__FILE__))) . '/core/Core/init.php';
init('minimal');
echo memberDirUpdates();

function memberDirUpdates() {
    //Update the database changes
    try {
        //update module name 
        \Cx\Lib\UpdateUtil::sql("UPDATE `".DBPREFIX."modules` SET `name` = 'MemberDir' WHERE `contrexx_modules`.`id` = 31");
        //update navigation url
        \Cx\Lib\UpdateUtil::sql("UPDATE `".DBPREFIX."backend_areas` SET `uri` = 'index.php?cmd=MemberDir' WHERE `contrexx_backend_areas`.`area_id` = 89");
        //Insert component entry
        \Cx\Lib\UpdateUtil::sql("INSERT INTO `".DBPREFIX."component` (`id`, `name`, `type`) VALUES ('31', 'MemberDir', 'module')");
        //update module name for frontend pages
        \Cx\Lib\UpdateUtil::sql("UPDATE `".DBPREFIX."content_page` SET `module` = 'MemberDir' WHERE `module` = 'memberdir'");
    } catch (\Cx\Lib\UpdateException $e) {
        return "Error: $e->sql";
    }
    
    //Update script for moving the folder
    $sourcePath      = ASCMS_DOCUMENT_ROOT . '/media/memberdir';
    $destinationPath = ASCMS_DOCUMENT_ROOT . '/media/MemberDir';
    
    try {
        if (file_exists($sourcePath) && !file_exists($destinationPath)) {
            \Cx\Lib\FileSystem\FileSystem::makeWritable($sourcePath);
            if (!\Cx\Lib\FileSystem\FileSystem::move($sourcePath, $destinationPath)) {
                return 'Failed to move the folder from '.$sourcePath.' to '.$destinationPath;
            }
        }
    } catch (\Cx\Lib\FileSystem\FileSystemException $e) {
        return $e->getMessage();
    }
    
    return 'MemberDir is updated successfully.';
}