<?php
function _guestbookUpdate()
{
	global $objDatabase;

	$arrGuestbookColumns = $objDatabase->MetaColumns(DBPREFIX.'module_guestbook');
    if ($arrGuestbookColumns === false) {
        setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_guestbook'));
        return false;
    }

    if (isset($arrGuestbookColumns['NICKNAME']) and !isset($arrGuestbookColumns['NAME'])) {
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

    // this addidional structure update/check is required due that the full version's structure isn't as it should be
    try {
        UpdateUtil::table(
            DBPREFIX . 'module_guestbook',
            array(
                'id'        => array('type' => 'INT(6)', 'unsigned' => true, 'auto_increment' => true, 'primary' => true),
                'status'    => array('type' => 'TINYINT(1)', 'unsigned' => true, 'default' => 0),
                'name'      => array('type' => 'VARCHAR(255)'),
                'forename'  => array('type' => 'VARCHAR(255)'),
                'gender'    => array('type' => 'CHAR(1)', 'notnull' => true, 'default' => ''),
                'url'       => array('type' => 'TINYTEXT'),
                'email'     => array('type' => 'TINYTEXT'),
                'comment'   => array('type' => 'TEXT'),
                'ip'        => array('type' => 'VARCHAR(15)'),
                'location'  => array('type' => 'TINYTEXT'),
                'lang_id'   => array('type' => 'TINYINT(2)', 'default' => '1'),
                'datetime'  => array('type' => 'DATETIME', 'default' => '0000-00-00 00:00:00')            ),
            array(
                'comment'   => array('fields' => array('comment'), 'type' => 'FULLTEXT')
            )
        );
    }
    catch (UpdateException $e) {
        return UpdateUtil::DefaultActionHandler($e);
    }

    return true;
}
?>
