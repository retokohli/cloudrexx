<?php
function _podcastUpdate()
{
    try{
        UpdateUtil::table(
            DBPREFIX.'module_podcast_medium',
            array(
                'id'             => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'title'          => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'youtube_id'     => array('type' => 'VARCHAR(25)', 'notnull' => true, 'default' => ''),
                'author'         => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'description'    => array('type' => 'TEXT', 'notnull' => true),
                'source'         => array('type' => 'TEXT', 'notnull' => true),
                'thumbnail'      => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'template_id'    => array('type' => 'INT(11)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'width'          => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'height'         => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'playlength'     => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'size'           => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'views'          => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'status'         => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0'),
                'date_added'     => array('type' => 'INT(14)', 'unsigned' => true, 'notnull' => true, 'default' => '0')
            ),
            array(
                'podcastindex'   => array('fields' => array('title','description'), 'type' => 'FULLTEXT')
            )
        );

        UpdateUtil::table(
            DBPREFIX.'module_podcast_template',
            array(
                'id'                     => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'description'            => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'template'               => array('type' => 'TEXT', 'notnull' => true),
                'extensions'             => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'js_embed'               => array('type' => 'ENUM(\'0\',\'1\')', 'notnull' => true, 'default' => '1'),
                'player_offset_width'    => array('type' => 'INT(3)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'player_offset_height'   => array('type' => 'INT(3)', 'unsigned' => true, 'notnull' => true, 'default' => '0')
            ),
            array(
                'description'            => array('fields' => array('description'), 'type' => 'UNIQUE')
            )
        );

        $arrSettings = array(
            'default_medium'            => array('none',  '1'),
            'default_medium_id'         => array('0',     '1'),
            'feed_item_count'           => array('10',    '1'),
            'enable_recommend_by_email' => array('0',     '1')
        );

        foreach ($arrSettings as $key => $arrSetting) {
            if (!UpdateUtil::sql("SELECT 1 FROM `".DBPREFIX."module_podcast_settings` WHERE `setname` = '".$key."'")->RecordCount()) {
                UpdateUtil::sql("INSERT INTO `".DBPREFIX."module_podcast_settings` (
                    SET `setname`   = '".$key."',
                        `setvalue`  = '".$arrSetting['value']."',
                        `status`    = '".$arrSetting['status']."'
                )");
            }
        }
    }
    catch (UpdateException $e) {
        // we COULD do something else here..
        return UpdateUtil::DefaultActionHandler($e);
    }

    return true;
}
?>
