<?php
function _feedUpdate()
{
    global $objDatabase, $_ARRAYLANG;

    $query = "ALTER TABLE `".DBPREFIX."module_feed_newsml_documents` CHANGE `publicIdentifier` `publicIdentifier` VARCHAR( 255 ) NOT NULL DEFAULT ''";
    if (!$objDatabase->Execute($query)) {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }

    $arrIndexes = $objDatabase->MetaIndexes(DBPREFIX.'module_feed_newsml_documents');
    if ($arrIndexes !== false) {
        if (!isset($arrIndexes['unique'])) {
            $query = "ALTER TABLE `".DBPREFIX."module_feed_newsml_documents` ADD UNIQUE `unique` (`publicIdentifier`)";
            if (!$objDatabase->Execute($query)) {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
    } else {
        setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_feed_newsml_documents'));
        return false;
    }

    return true;
}



function _feedInstall()
{
    try {

        /**************************************************************************
         * EXTENSION:   Initial creation (for contrexx editions <> premium with   *
         *              version < 3.0.0 which have this module not yet installed) *
         *                                                                        *
         * ADDED:       Contrexx v3.0.1                                           *
         **************************************************************************/
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_feed_category',
            array(
                'id'         => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'name'       => array('type' => 'VARCHAR(150)', 'notnull' => true, 'default' => '', 'after' => 'id'),
                'status'     => array('type' => 'INT(1)', 'notnull' => true, 'default' => '1', 'after' => 'name'),
                'time'       => array('type' => 'INT(100)', 'notnull' => true, 'default' => '0', 'after' => 'status'),
                'lang'       => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'time'),
                'pos'        => array('type' => 'INT(3)', 'notnull' => true, 'default' => '0', 'after' => 'lang')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_feed_category` (`id`, `name`, `status`, `time`, `lang`, `pos`)
            VALUES (1, 'Internet News', 1, 1134028532, 1, 0)
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_feed_news',
            array(
                'id'             => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'subid'          => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'after' => 'id'),
                'name'           => array('type' => 'VARCHAR(150)', 'notnull' => true, 'default' => '', 'after' => 'subid'),
                'link'           => array('type' => 'VARCHAR(150)', 'notnull' => true, 'default' => '', 'after' => 'name'),
                'filename'       => array('type' => 'VARCHAR(150)', 'notnull' => true, 'default' => '', 'after' => 'link'),
                'articles'       => array('type' => 'INT(2)', 'notnull' => true, 'default' => '0', 'after' => 'filename'),
                'cache'          => array('type' => 'INT(4)', 'notnull' => true, 'default' => '3600', 'after' => 'articles'),
                'time'           => array('type' => 'INT(100)', 'notnull' => true, 'default' => '0', 'after' => 'cache'),
                'image'          => array('type' => 'INT(1)', 'notnull' => true, 'default' => '1', 'after' => 'time'),
                'status'         => array('type' => 'INT(1)', 'notnull' => true, 'default' => '1', 'after' => 'image'),
                'pos'            => array('type' => 'INT(3)', 'notnull' => true, 'default' => '0', 'after' => 'status')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_feed_news` (`id`, `subid`, `name`, `link`, `filename`, `articles`, `cache`, `time`, `image`, `status`, `pos`)
            VALUES  (3, 1, 'pressetext.schweiz News', 'http://www.pressetext.ch/produkte/rss/schlagzeilen.mc?land=ch&channel=ht&show=rss_2', '', 10, 3600, 1292836792, 1, 1, 3),
                    (4, 1, 'pressetext.deutschland News', 'http://pressetext.com/produkte/rss/schlagzeilen.mc?land=de&channel=ht&show=rss_2', '', 10, 3600, 1236268968, 1, 1, 4),
                    (5, 1, 'pressetext.österreich News', 'http://pressetext.com/produkte/rss/schlagzeilen.mc?land=at&channel=ht&show=rss_2', '', 50, 3600, 1235730600, 1, 1, 5)
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_feed_newsml_association',
            array(
                'id'             => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'pId_master'     => array('type' => 'text', 'after' => 'id'),
                'pId_slave'      => array('type' => 'text', 'after' => 'pId_master')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_feed_newsml_categories',
            array(
                'id'                     => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'providerId'             => array('type' => 'text', 'after' => 'id'),
                'name'                   => array('type' => 'VARCHAR(40)', 'notnull' => true, 'default' => '', 'after' => 'providerId'),
                'subjectCodes'           => array('type' => 'text', 'after' => 'name'),
                'showSubjectCodes'       => array('type' => 'ENUM(\'all\',\'only\',\'exclude\')', 'notnull' => true, 'default' => 'all', 'after' => 'subjectCodes'),
                'template'               => array('type' => 'text', 'after' => 'showSubjectCodes'),
                'limit'                  => array('type' => 'SMALLINT(6)', 'notnull' => true, 'default' => '0', 'after' => 'template'),
                'showPics'               => array('type' => 'ENUM(\'0\',\'1\')', 'notnull' => true, 'default' => '1', 'after' => 'limit'),
                'auto_update'            => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'showPics')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_feed_newsml_categories` (`id`, `providerId`, `name`, `subjectCodes`, `showSubjectCodes`, `template`, `limit`, `showPics`, `auto_update`)
            VALUES  (1, '0', 'TestML', '0', 'all', '{ID}    Id der Newsmeldung\r\n{TITLE}   Titel\r\n{DATE}     Datum (Donnerstag, 5. März 2009 / 13:13 h)\r\n{LONG_DATE}   Datum (13:13:55 05.03.2009)\r\n{SHORT_DATE}     Datum (05.03.2009)\r\n{TEXT}    Inhalt der Newsmledung', 10, '1', 0),
                    (2, '0', 'TestML', '0', 'all', '{ID}    Id der Newsmeldung\r\n{TITLE}   Titel\r\n{DATE}     Datum (Donnerstag, 5. März 2009 / 13:13 h)\r\n{LONG_DATE}   Datum (13:13:55 05.03.2009)\r\n{SHORT_DATE}     Datum (05.03.2009)\r\n{TEXT}    Inhalt der Newsmledung', 10, '1', 0)
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_feed_newsml_documents',
            array(
                'id'                     => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'publicIdentifier'       => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'id'),
                'providerId'             => array('type' => 'text', 'after' => 'publicIdentifier'),
                'dateId'                 => array('type' => 'INT(8)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'providerId'),
                'newsItemId'             => array('type' => 'text', 'after' => 'dateId'),
                'revisionId'             => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'newsItemId'),
                'thisRevisionDate'       => array('type' => 'INT(14)', 'notnull' => true, 'default' => '0', 'after' => 'revisionId'),
                'urgency'                => array('type' => 'SMALLINT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'thisRevisionDate'),
                'subjectCode'            => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'urgency'),
                'headLine'               => array('type' => 'VARCHAR(67)', 'notnull' => true, 'default' => '', 'after' => 'subjectCode'),
                'dataContent'            => array('type' => 'text', 'after' => 'headLine'),
                'is_associated'          => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'dataContent'),
                'media_type'             => array('type' => 'ENUM(\'Text\',\'Graphic\',\'Photo\',\'Audio\',\'Video\',\'ComplexData\')', 'notnull' => true, 'default' => 'Text', 'after' => 'is_associated'),
                'source'                 => array('type' => 'text', 'after' => 'media_type'),
                'properties'             => array('type' => 'text', 'after' => 'source')
            ),
            array(
                'unique'                 => array('fields' => array('publicIdentifier'), 'type' => 'UNIQUE')
            ),
           'MyISAM',
           'cx3upgrade'
        );

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_feed_newsml_providers',
            array(
                'id'             => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'providerId'     => array('type' => 'text', 'after' => 'id'),
                'name'           => array('type' => 'VARCHAR(40)', 'notnull' => true, 'default' => '', 'after' => 'providerId'),
                'path'           => array('type' => 'text', 'after' => 'name')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );

    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }
}
