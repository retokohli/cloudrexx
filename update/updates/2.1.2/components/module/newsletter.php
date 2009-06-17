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

    try{
        UpdateUtil::table(
            DBPREFIX.'module_newsletter_category',
            array(
                'id'                     => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'status'                 => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0'),
                'name'                   => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'notification_email'     => array('type' => 'VARCHAR(250)')
            ),
            array(
                'name'                   => array('fields' => array('name'))
            )
        );

        UpdateUtil::table(
            DBPREFIX.'module_newsletter_confirm_mail',
            array(
                'id'             => array('type' => 'INT(1)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'title'          => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'content'        => array('type' => 'LONGTEXT'),
                'recipients'     => array('type' => 'TEXT')
            )
        );
    }
    catch (UpdateException $e) {
        return UpdateUtil::DefaultActionHandler($e);
    }

    return true;
}

?>
