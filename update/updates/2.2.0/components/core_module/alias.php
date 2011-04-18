<?php
function _aliasUpdate() {
    global $objUpdate;

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

        // Since version 2.2, aliases are unique within a language. Therefore every alias must be associated to its content page's language.
        if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '2.2.0')) {
            UpdateUtil::sql('UPDATE `'.DBPREFIX.'module_alias_source` AS tblAS
                         INNER JOIN `'.DBPREFIX.'module_alias_target` AS tblAT ON tblAT.`id` = tblAS.`target_id`
                         INNER JOIN `'.DBPREFIX.'content_navigation`  AS tblN  ON tblN.`catid` = tblAT.`url`
                                SET tblAS.`lang_id` = tblN.`lang`
                              WHERE tblAT.`type` = \'local\'');
        }
    }
    catch (UpdateException $e) {
        return UpdateUtil::DefaultActionHandler($e);
    }
	return true;
}

?>
