<?php

require_once dirname(dirname(dirname(__FILE__))) . '/core/Core/init.php';
init('minimal');
echo ecardUpdates();

function ecardUpdates() {
    
    //Update database changes
    try {
        //update module name 
        \Cx\Lib\UpdateUtil::sql("UPDATE `".DBPREFIX."modules` SET `name` = 'Ecard' WHERE `id` = 49");
        //update navigation url
        \Cx\Lib\UpdateUtil::sql("UPDATE `".DBPREFIX."backend_areas` SET `uri` = 'index.php?cmd=Ecard' WHERE `area_id` = 130");
        //Insert component entry
        \Cx\Lib\UpdateUtil::sql("INSERT INTO `".DBPREFIX."component` (`id`, `name`, `type`) VALUES ('49', 'Ecard', 'module')");
        //update module name for frontend pages
        \Cx\Lib\UpdateUtil::sql("UPDATE `".DBPREFIX."content_page` SET `module` = 'Ecard' WHERE `module` = 'ecard'");
    } catch (\Cx\Lib\UpdateException $e) {
        return "Error: $e->sql";
    }
    
    //Update script for moving the folders
    $imgModulesfolderPath = ASCMS_DOCUMENT_ROOT . '/images/modules/ecard';
    $mediafolderPath      = ASCMS_DOCUMENT_ROOT . '/media/Ecard';
    
    try {
        
        if (!file_exists($mediafolderPath)) {
            \Cx\Lib\FileSystem\FileSystem::make_folder($mediafolderPath);
            \Cx\Lib\FileSystem\FileSystem::makeWritable($mediafolderPath);
        }
        
        //move the folder from '/images/modules/ecard/ecards_optimized' to '/media/Ecard/ecards_optimized'
        if (file_exists($imgModulesfolderPath . '/ecards_optimized') && !file_exists($mediafolderPath . '/ecards_optimized')) {
            \Cx\Lib\FileSystem\FileSystem::makeWritable($imgModulesfolderPath . '/ecards_optimized');
            if (!\Cx\Lib\FileSystem\FileSystem::move($imgModulesfolderPath . '/ecards_optimized', $mediafolderPath . '/ecards_optimized')) {
                return 'Failed to Move the folders from '.$imgModulesfolderPath . '/ecards_optimized to '.$mediafolderPath . '/ecards_optimized.';
            }
        }
        
        //move the folder from '/images/modules/ecard/send_ecards' to '/media/Ecard/send_ecards'
        if (file_exists($imgModulesfolderPath . '/send_ecards') && !file_exists($mediafolderPath . '/send_ecards')) {
            \Cx\Lib\FileSystem\FileSystem::makeWritable($imgModulesfolderPath . '/send_ecards');
            if (!\Cx\Lib\FileSystem\FileSystem::move($imgModulesfolderPath . '/send_ecards', $mediafolderPath . '/send_ecards')) {
                return 'Failed to Move the folders from '.$imgModulesfolderPath . '/send_ecards to '.$mediafolderPath . '/send_ecards.';
            }
        }
        
        //move the folder from '/images/modules/ecard/thumbnails' to '/media/Ecard/thumbnails'
        if (file_exists($imgModulesfolderPath . '/thumbnails') && !file_exists($mediafolderPath . '/thumbnails')) {
            \Cx\Lib\FileSystem\FileSystem::makeWritable($imgModulesfolderPath . '/thumbnails');
            if (!\Cx\Lib\FileSystem\FileSystem::move($imgModulesfolderPath . '/thumbnails', $mediafolderPath . '/thumbnails')) {
                return 'Failed to Move the folders from '.$imgModulesfolderPath . '/thumbnails to '.$mediafolderPath . '/thumbnails.';
            }
        }
        
        return 'Successfully updated.';
    } catch (\Cx\Lib\FileSystem\FileSystemException $e) {
        return $e->getMessage();
    }
}