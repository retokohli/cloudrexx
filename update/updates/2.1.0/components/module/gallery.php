
<?php
function _galleryUpdate()
{
    global $objDatabase, $_ARRAYLANG;

    $arrColumns = $objDatabase->MetaColumns(DBPREFIX.'module_gallery_categories');
    if ($arrColumns === false) {
        setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_gallery_categories'));
        return false;
    }

    if (!isset($arrColumns['BACKENDPROTECTED'])) {
        $query = "ALTER TABLE `".DBPREFIX."module_gallery_categories` ADD `backendProtected` INT NOT NULL ;" ;
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    if (!isset($arrColumns['BACKEND_ACCESS_ID'])) {
        $query = "ALTER TABLE `".DBPREFIX."module_gallery_categories` ADD `backend_access_id` INT NOT NULL ;" ;
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    if (!isset($arrColumns['FRONTENDPROTECTED'])) {
        $query = "ALTER TABLE `".DBPREFIX."module_gallery_categories` ADD `frontendProtected` INT NOT NULL ;" ;
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    if (!isset($arrColumns['FRONTEND_ACCESS_ID'])) {
        $query = "ALTER TABLE `".DBPREFIX."module_gallery_categories` ADD `frontend_access_id` INT NOT NULL ;" ;
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    $arrSettings = array(
                    '22' => array(
                        'name'     => 'show_file_name',
                        'value' => 'off'),
                    '23' => array(
                        'name'     => 'slide_show',
                        'value' => 'off'),
                    '24' => array(
                        'name'     => 'slide_show_seconds',
                        'value' => '3'),
                    );

    foreach ($arrSettings as $id => $arrSetting) {
        $query = "SELECT 1 FROM `".DBPREFIX."module_gallery_settings` WHERE `name`= '".$arrSetting['name']."'" ;
        if (($objRS = $objDatabase->Execute($query)) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
        if($objRS->RecordCount() == 0){
            $query = "INSERT INTO `".DBPREFIX."module_gallery_settings`
                             (`id`, `name`, `value`)
                      VALUES (".$id.", '".$arrSetting['name']."', '".$arrSetting['value']."')" ;
            if ($objDatabase->Execute($query) === false) {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
    }

    return true;
}
?>
