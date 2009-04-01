<?php

function _docsysUpdate()
{
    try{
        UpdateUtil::table(
            DBPREFIX . 'module_docsys_entry_category',
            array(
                'entry'    => array('type' => 'INT', 'notnull' => true, 'primary'=> true, 'auto_increment' => true),
                'category' => array('type' => 'INT', 'notnull' => true, 'primary'=> true)
            )
        );
    }
    catch (UpdateException $e) {
        return UpdateUtil::DefaultActionHandler($e);
    }

    return true;
}

?>
