<?php

function _docsysUpdate()
{
    global $objDatabase, $_ARRAYLANG;

    try{
        UpdateUtil::table(
            DBPREFIX.'module_docsys_entry_category',
            array(
                'entry'      => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
                'category'   => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true)
            )
        );

        if (UpdateUtil::column_exist(DBPREFIX . 'module_docsys', 'catid')) {
            $query = "SELECT `id`, `catid` FROM `".DBPREFIX."module_docsys`";
            $objResult = $objDatabase->Execute($query);
            if ($objResult !== false) {
                while (!$objResult->EOF) {
                    $query = "SELECT 1 FROM `".DBPREFIX."module_docsys_entry_category` WHERE `entry` = ".$objResult->fields['id']." AND `category` = ".$objResult->fields['catid'];
                    $objCheck = $objDatabase->SelectLimit($query, 1);
                    if ($objCheck !== false) {
                        if ($objCheck->RecordCount() == 0) {
                            $query = "INSERT INTO `".DBPREFIX."module_docsys_entry_category` (`entry`, `category`) VALUES ('".$objResult->fields['id']."', '".$objResult->fields['catid']."')";
                            if ($objDatabase->Execute($query) === false) {
                                return _databaseError($query, $objDatabase->ErrorMsg());
                            }
                        }
                    } else {
                        return _databaseError($query, $objDatabase->ErrorMsg());
                    }

                    $objResult->MoveNext();
                }
            } else {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }

        // Fix some fuckup that UpdatUtil can't do.. make sure that "id" is unique before attempting
        // to make it a primary key
        $duplicateIDs_sql = "SELECT COUNT(*) as c, id FROM ".DBPREFIX."module_docsys GROUP BY id HAVING c > 1";
        $duplicateIDs = $objDatabase->Execute($duplicateIDs_sql);
        if ($duplicateIDs === false) {
            return _databaseError($duplicateIDs_sql, $objDatabase->ErrorMsg());
        }
        $fix_queries = array();
        while (!$duplicateIDs->EOF) {
            $id    = $duplicateIDs->fields['id'];
            $entries_sql = "SELECT * FROM ".DBPREFIX."module_docsys WHERE id = $id";
            $entries     = $objDatabase->Execute($entries_sql);
            if ($entries === false) {
                return _databaseError($entries_sql, $objDatabase->ErrorMsg());
            }
            // NOW: put them all in an array, DELETE them and then re-INSERT them
            // without id. the auto_increment will take care of the rest. The first one we
            // re-insert can keep it's id.
            $entries_sql = "SELECT * FROM ".DBPREFIX."module_docsys WHERE id = $id";
            $entries     = $objDatabase->Execute($entries_sql);
            if ($entries === false) {
                return _databaseError($entries_sql, $objDatabase->ErrorMsg());
            }
            $is_first = true;
            $fix_queries[] = "DELETE FROM ".DBPREFIX."module_docsys WHERE id = $id";
            while (!$entries->EOF) {
                $pairs = array();
                foreach ($entries->fields as $k => $v) {
                    // only first may keep it's id
                    if ($k == 'id' and !$is_first) {
                            continue;
                    }
                    $pairs[] = "$k = '" . addslashes($v) . "'";
                }
                $fix_queries[] = "INSERT INTO ".DBPREFIX."module_docsys SET ".join(', ', $pairs);

                $is_first = false;
                $entries->MoveNext();
            }
            $duplicateIDs->MoveNext();
        }

        // Now run all of these queries. basically DELETE, INSERT,INSERT, DELETE,INSERT...
        foreach ($fix_queries as $insert_query) {
            if ($objDatabase->Execute($insert_query) === false) {
                return _databaseError($insert_query, $objDatabase->ErrorMsg());
            }
        }




        // alter column startdate from date to int
        $arrColumns = $objDatabase->MetaColumns(DBPREFIX.'module_docsys');
        if ($arrColumns === false) {
            setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_docsys'));
            return false;
        }
        if (isset($arrColumns['STARTDATE'])) {
            if ($arrColumns['STARTDATE']->type == 'date') {
                if (!isset($arrColumns['STARTDATE_NEW'])) {
                    $query = 'ALTER TABLE `'.DBPREFIX.'module_docsys` ADD `startdate_new` INT(14) UNSIGNED NOT NULL DEFAULT \'0\' AFTER `startdate`';
                    if ($objDatabase->Execute($query) === false) {
                        return _databaseError($query, $objDatabase->ErrorMsg());
                    }
                }

                $query = 'UPDATE `'.DBPREFIX.'module_docsys` SET `startdate_new` = UNIX_TIMESTAMP(`startdate`) WHERE `startdate` != \'0000-00-00\'';
                if ($objDatabase->Execute($query) === false) {
                    return _databaseError($query, $objDatabase->ErrorMsg());
                }

                $query = 'ALTER TABLE `'.DBPREFIX.'module_docsys` DROP `startdate`';
                if ($objDatabase->Execute($query) === false) {
                    return _databaseError($query, $objDatabase->ErrorMsg());
                }
            }
        }
        $arrColumns = $objDatabase->MetaColumns(DBPREFIX.'module_docsys');
        if ($arrColumns === false) {
            setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_docsys'));
            return false;
        }
        if (!isset($arrColumns['STARTDATE'])) {
            $query = 'ALTER TABLE `'.DBPREFIX.'module_docsys` CHANGE `startdate_new` `startdate` INT(14) UNSIGNED NOT NULL DEFAULT \'0\'';
            if ($objDatabase->Execute($query) === false) {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }


        // alter column enddate from date to int
        $arrColumns = $objDatabase->MetaColumns(DBPREFIX.'module_docsys');
        if ($arrColumns === false) {
            setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_docsys'));
            return false;
        }
        if (isset($arrColumns['ENDDATE'])) {
            if ($arrColumns['ENDDATE']->type == 'date') {
                if (!isset($arrColumns['ENDDATE_NEW'])) {
                    $query = 'ALTER TABLE `'.DBPREFIX.'module_docsys` ADD `enddate_new` INT(14) UNSIGNED NOT NULL DEFAULT \'0\' AFTER `enddate`';
                    if ($objDatabase->Execute($query) === false) {
                        return _databaseError($query, $objDatabase->ErrorMsg());
                    }
                }

                $query = 'UPDATE `'.DBPREFIX.'module_docsys` SET `enddate_new` = UNIX_TIMESTAMP(`enddate`) WHERE `enddate` != \'0000-00-00\'';
                if ($objDatabase->Execute($query) === false) {
                    return _databaseError($query, $objDatabase->ErrorMsg());
                }

                $query = 'ALTER TABLE `'.DBPREFIX.'module_docsys` DROP `enddate`';
                if ($objDatabase->Execute($query) === false) {
                    return _databaseError($query, $objDatabase->ErrorMsg());
                }
            }
        }
        $arrColumns = $objDatabase->MetaColumns(DBPREFIX.'module_docsys');
        if ($arrColumns === false) {
            setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_docsys'));
            return false;
        }
        if (!isset($arrColumns['ENDDATE'])) {
            $query = 'ALTER TABLE `'.DBPREFIX.'module_docsys` CHANGE `enddate_new` `enddate` INT(14) UNSIGNED NOT NULL DEFAULT \'0\'';
            if ($objDatabase->Execute($query) === false) {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }


        UpdateUtil::table(
            DBPREFIX . 'module_docsys',
            array(
                'id'        => array('type' => 'INT(6)', 'unsigned' => true, 'auto_increment' => true, 'primary' => true),
                'date'      => array('type' => 'INT(14)', 'notnull' => false),
                'title'     => array('type' => 'VARCHAR(250)'),
                'author'    => array('type' => 'VARCHAR(150)'),
                'text'      => array('type' => 'MEDIUMTEXT', 'notnull' => true),
                'source'    => array('type' => 'VARCHAR(250)'),
                'url1'      => array('type' => 'VARCHAR(250)'),
                'url2'      => array('type' => 'VARCHAR(250)'),
                'lang'      => array('type' => 'INT(2)', 'unsigned' => true, 'default' => '0'),
                'userid'    => array('type' => 'INT(6)', 'unsigned' => true, 'default' => '0'),
                'startdate' => array('type' => 'INT(14)', 'unsigned' => true, 'default' => '0'),
                'enddate'   => array('type' => 'INT(14)', 'unsigned' => true, 'default' => '0'),
                'status'    => array('type' => 'TINYINT(4)', 'default' => '1'),
                'changelog' => array('type' => 'INT(14)', 'default' => '0')
            ),
            array(
                'newsindex' => array('fields' => array('title', 'text'), 'type' => 'FULLTEXT')
            )
        );
    }
    catch (UpdateException $e) {
        return UpdateUtil::DefaultActionHandler($e);
    }

    return true;
}

?>
