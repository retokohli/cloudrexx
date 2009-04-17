<?php

function _docsysUpdate()
{
    global $objDatabase;

    try{
        UpdateUtil::table(
            DBPREFIX . 'module_docsys_entry_category',
            array(
                'entry'    => array('type' => 'INT', 'unsigned' => true, 'notnull' => true, 'primary'=> true),
                'category' => array('type' => 'INT', 'unsigned' => true, 'notnull' => true, 'primary'=> true)
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


            UpdateUtil::table(
                DBPREFIX . 'module_docsys',
                array(
                    'id'        => array('type' => 'INT(6)', 'unsigned' => true, 'auto_increment' => true, 'primary' => true),
                    'date'      => array('type' => 'INT(14)', 'notnull' => false),
                    'title'     => array('type' => 'VARCHAR(250)'),
                    'author'    => array('type' => 'VARCHAR(150)'),
                    'text'      => array('type' => 'MEDIUMTEXT', 'notnull' => false),
                    'source'    => array('type' => 'VARCHAR(250)'),
                    'url1'      => array('type' => 'VARCHAR(250)'),
                    'url2'      => array('type' => 'VARCHAR(250)'),
                    'lang'      => array('type' => 'INT(2)', 'unsigned' => true, 'default_expr' => '0'),
                    'userid'    => array('type' => 'INT(6)', 'unsigned' => true, 'default_expr' => '0'),
                    'startdate' => array('type' => 'DATE', 'default_expr' => '0000-00-00'),
                    'enddate'   => array('type' => 'DATE', 'default_expr' => '0000-00-00'),
                    'status'    => array('type' => 'TINYINT(4)', 'default_expr' => '1'),
                    'changelog' => array('type' => 'INT(14)', 'default_expr' => '0')
                ),
                array(
                    'newsindex' => array('fields' => array('title', 'text'), 'type' => 'FULLTEXT')
                )
            );
        }
    }
    catch (UpdateException $e) {
        return UpdateUtil::DefaultActionHandler($e);
    }

    return true;
}

?>
