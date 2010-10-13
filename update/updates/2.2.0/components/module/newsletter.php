<?php
//@TODO: should we use the script from 2.1.4? old setting notifyOnUnsubscribe is not merged into notificationUnsubscribe here.

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

    // Add notigication recipians to confirm_mail table
    if (!isset($arrUserColumns['RECIPIENTS'])) {
        $query = "
            ALTER TABLE `".DBPREFIX."module_newsletter_confirm_mail`
            ADD `recipients`  MEDIUMTEXT NOT NULL;
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    //insert notification values
    $query = "SELECT id FROM `".DBPREFIX."module_newsletter_confirm_mail` WHERE id='3'";
    $objCheck = $objDatabase->SelectLimit($query, 1);
    if ($objCheck !== false) {
        if ($objCheck->RecordCount() == 0) {
            $query =     "INSERT INTO `".DBPREFIX."module_newsletter_confirm_mail` (`id` ,`title` ,`content` ,`recipients`) VALUES ('3', '[[url]] - Neue Newsletter Empfänger [[action]]', 'Hallo Admin Eine neue Empfänger [[action]] in ihrem Newsletter System. Automatisch generierte Nachricht [[date]]', '');";
            if ($objDatabase->Execute($query) === false) {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
    } else {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }

    //insert settings values
    $query = "SELECT setid FROM `".DBPREFIX."module_newsletter_settings` WHERE setname='notificationSubscribe'";
    $objCheck = $objDatabase->SelectLimit($query, 1);
    if ($objCheck !== false) {
        if ($objCheck->RecordCount() == 0) {
            $query =     "INSERT INTO `".DBPREFIX."module_newsletter_settings` (`setid` ,`setname` ,`setvalue` ,`status`) VALUES ('11', 'notificationSubscribe', '1', '1');
            ";
            if ($objDatabase->Execute($query) === false) {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
    } else {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }

    $query = "SELECT setid FROM `".DBPREFIX."module_newsletter_settings` WHERE setname='notificationUnsubscribe'";
    $objCheck = $objDatabase->SelectLimit($query, 1);
    if ($objCheck !== false) {
        if ($objCheck->RecordCount() == 0) {
            $query =     "INSERT INTO `".DBPREFIX."module_newsletter_settings` (`setid` ,`setname` ,`setvalue` ,`status`) VALUES ('12', 'notificationUnsubscribe', '1', '1');
            ";
            if ($objDatabase->Execute($query) === false) {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
    } else {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }


    return true;
}

?>
