<?php

function _newsletterUpdate()
{
    global $objDatabase, $_ARRAYLANG;

    // Newsletter user table fields
    $arrUserColumns = $objDatabase->MetaColumns(DBPREFIX.'module_newsletter_user');
    if ($arrUserColumns === false) {
        setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_newsletter_user'));
        return false;
    }

    // Add URI field to user table
    if (!isset($arrUserColumns['URI'])) {
        $query = "
            ALTER TABLE `".DBPREFIX."module_newsletter_user`
            ADD `uri` VARCHAR(255) NOT NULL DEFAULT '' AFTER `email`;
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    return true;
}

?>
