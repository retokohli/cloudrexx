<?php
require_once dirname(dirname(dirname(__FILE__))) . '/core/Core/init.php';
init('minimal');
echo calendarUpdate();

function calendarUpdate() {
    try {
        //update module name 
        \Cx\Lib\UpdateUtil::sql("UPDATE `" . DBPREFIX . "modules` SET `name` = 'Calendar' WHERE `id` = 21");
        //update navigation url
        \Cx\Lib\UpdateUtil::sql("UPDATE `" . DBPREFIX . "backend_areas` SET `uri` = 'index.php?cmd=Calendar' WHERE `area_id` = 16");
        //Insert component entry
        \Cx\Lib\UpdateUtil::sql("INSERT INTO `" . DBPREFIX . "component` (`id`, `name`, `type`) VALUES ('21', 'Calendar', 'module')");
        //update module name for frontend pages
        \Cx\Lib\UpdateUtil::sql("UPDATE `" . DBPREFIX . "content_page` SET `module` = 'Calendar' WHERE `module` = 'calendar'");
        //following queries for changing the path from images/calendar into images/Calendar
        \Cx\Lib\UpdateUtil::sql("UPDATE `" . DBPREFIX . "module_calendar_event` SET `pic` = REPLACE(`pic`, 'images/calendar', 'images/Calendar'),
                                                                                    `attach` = REPLACE(`attach`, 'images/calendar', 'images/Calendar'),
                                                                                    `place_map` = REPLACE(`place_map`, 'images/calendar', 'images/Calendar')
                                                                                     WHERE `pic` LIKE ('" . ASCMS_PATH_OFFSET . "/images/calendar%') ");
        
    } catch (\Cx\Lib\UpdateException $e) {
        return "Error: $e->sql";
    }
    $sourcePath = ASCMS_DOCUMENT_ROOT . '/images/calendar';
    $targetPath = ASCMS_DOCUMENT_ROOT . '/images/Calendar';
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
    return 'Calendar Component Updated Successfully';
}
?>