<?php
function _aliasUpdate() {
    try {
        UpdateUtil::table(
            DBPREFIX.'module_alias_source',
            array(
                'id'         => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'target_id'  => array('type' => 'INT(10)', 'unsigned' => true, 'default' => '0'),
                'lang_id'    => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'after' => 'target_id'),
                'url'        => array('type' => 'VARCHAR(255)'),
                'isdefault'  => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0')
            ),
            array(
                'url_lang_id'=> array('fields' => array('lang_id', 'url')),
                'isdefault'  => array('fields' => array('isdefault'))
            )
        );

        UpdateUtil::table(
            DBPREFIX.'module_alias_target',
            array(
                'id'     => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'type'   => array('type' => 'ENUM(\'url\',\'local\')', 'notnull' => true, 'default' => 'url'),
                'url'    => array('type' => 'VARCHAR(255)')
            ),
            array(
                'url'    => array('fields' => array('url'), 'type' => 'UNIQUE')
            )
        );
    }
    catch (UpdateException $e) {
        return UpdateUtil::DefaultActionHandler($e);
    }
	return true;
}

?>
