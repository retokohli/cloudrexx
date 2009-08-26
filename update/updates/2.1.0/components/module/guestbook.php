<?php
function _guestbookUpdate()
{
    global $objDatabase;

    $arrGuestbookColumns = $objDatabase->MetaColumns(DBPREFIX.'module_guestbook');
    if ($arrGuestbookColumns === false) {
        setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_guestbook'));
        return false;
    }

    if (isset($arrGuestbookColumns['NICKNAME'])) {
        $query = "ALTER TABLE ".DBPREFIX."module_guestbook
                  CHANGE `nickname` `name` varchar(255) NOT NULL default ''";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    if (!isset($arrGuestbookColumns['FORENAME'])) {
        $query = "ALTER TABLE ".DBPREFIX."module_guestbook
                  ADD `forename` varchar(255) NOT NULL default '' AFTER `name`";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    return true;
}
?>
