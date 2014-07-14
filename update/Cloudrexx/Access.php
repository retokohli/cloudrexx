<?php

require_once dirname(dirname(dirname(__FILE__))) . '/core/Core/init.php';
init('minimal');
echo accessUpdates();

function accessUpdates() {
    //Update the database changes
    try {
        //update module name 
        \Cx\Lib\UpdateUtil::sql("UPDATE `".DBPREFIX."modules` SET `name` = 'Access' WHERE `id` = 23");
        //update navigation url
        \Cx\Lib\UpdateUtil::sql("UPDATE `".DBPREFIX."backend_areas` SET `uri` = 'index.php?cmd=Access', `module_id` = '23' WHERE `area_id` = 18");
        \Cx\Lib\UpdateUtil::sql("UPDATE `".DBPREFIX."backend_areas` SET `uri` = 'index.php?cmd=Access' WHERE `area_id` = 208");
        //Insert component entry
        \Cx\Lib\UpdateUtil::sql("INSERT INTO `".DBPREFIX."component` (`id`, `name`, `type`) VALUES ('23', 'Access', 'core_module')");
        //update module name for frontend pages
        \Cx\Lib\UpdateUtil::sql("UPDATE `".DBPREFIX."content_page` SET `module` = 'Access' WHERE `module` = 'access'");
        //update module name for crm core settings
        \Cx\Lib\UpdateUtil::sql("UPDATE `".DBPREFIX."core_setting` SET `section` = 'Access' WHERE `section` = 'access' AND `name` = 'providers' AND `group` = 'sociallogin'");
    } catch (\Cx\Lib\UpdateException $e) {
        return "Error: $e->sql";
    }
    
    //Update script for moving the folder
    $accessImgPath   = ASCMS_DOCUMENT_ROOT . '/images';
    
    try {
        if (file_exists($accessImgPath . '/access') && !file_exists($accessImgPath . '/Access')) {
            \Cx\Lib\FileSystem\FileSystem::makeWritable($accessImgPath . '/access');
            if (!\Cx\Lib\FileSystem\FileSystem::move($accessImgPath . '/access', $accessImgPath . '/Access')) {
                return 'Failed to move the folder from '.$accessImgPath . '/access to '.$accessImgPath . '/Access.';
            }
        }
    } catch (\Cx\Lib\FileSystem\FileSystemException $e) {
        return $e->getMessage();
    }
    
    return 'Access updated successfully.';
}