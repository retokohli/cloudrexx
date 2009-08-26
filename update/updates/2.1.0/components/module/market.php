
<?php
function _marketUpdate()
{
    global $objDatabase, $_ARRAYLANG;

    $query = "SELECT id FROM ".DBPREFIX."module_market_settings WHERE name='codeMode'";
    $objCheck = $objDatabase->SelectLimit($query, 1);
    if ($objCheck !== false) {
        if ($objCheck->RecordCount() == 0) {
            $query =     "INSERT INTO `".DBPREFIX."module_market_settings` ( `id` , `name` , `value` , `description` , `type` )
                        VALUES ( NULL , 'codeMode', '1', 'TXT_MARKET_SET_CODE_MODE', '2')";
            if ($objDatabase->Execute($query) === false) {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
    } else {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }

    $arrColumns = $objDatabase->MetaColumns(DBPREFIX.'module_market_mail');
    if ($arrColumns === false) {
        setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_market_mail'));
        return false;
    }

    if (!isset($arrColumns['MAILTO'])) {
        $query = "ALTER TABLE `".DBPREFIX."module_market_mail` ADD `mailto` VARCHAR( 10 ) NOT NULL AFTER `content`" ;
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }


    /*****************************************************************
    * EXTENSION:    New attributes 'color' and 'sort_id' for entries *
    * ADDED:        Contrexx v2.1.0                                     *
    *****************************************************************/
    $arrColumns = $objDatabase->MetaColumns(DBPREFIX.'module_market');
    if ($arrColumns === false) {
        setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_market'));
        return false;
    }

    if (!isset($arrColumns['SORT_ID'])) {
        $query = "ALTER TABLE `".DBPREFIX."module_market` ADD `sort_id` INT( 4 ) NOT NULL DEFAULT '0' AFTER `paypal`" ;
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    if (!isset($arrColumns['COLOR'])) {
        $query = "ALTER TABLE `".DBPREFIX."module_market` ADD `color` VARCHAR(50) NOT NULL DEFAULT '' AFTER `description`" ;
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    return true;
}
?>
