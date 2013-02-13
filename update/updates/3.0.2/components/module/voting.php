<?php

function _votingUpdate()
{
    try{
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX . 'voting_system',
            array(
                'id'               => array('type' =>    'INT',                 'notnull' => true, 'primary'     => true,   'auto_increment' => true),
                'date'             => array('type' =>    'TIMESTAMP',           'notnull' => true, 'default_expr'=> 'CURRENT_TIMESTAMP'),
                'title'            => array('type' =>    'VARCHAR(60)',         'notnull' => true, 'default'     => '',     'renamefrom' => 'name'),
                'question'         => array('type' =>    'TEXT',                'notnull' => false),
                'status'           => array('type' =>    'TINYINT(1)',          'notnull' => false,'default'     => 1),
                'votes'            => array('type' =>    'INT(11)',             'notnull' => false,'default'     => 0),
                'submit_check'     => array('type' => "ENUM('cookie','email')", 'notnull' => true, 'default'    => 'cookie'),
                'additional_nickname' => array('type' => 'TINYINT(1)',          'notnull' => true, 'default'     => 0),
                'additional_forename' => array('type' => 'TINYINT(1)',          'notnull' => true, 'default'     => 0),
                'additional_surname'  => array('type' => 'TINYINT(1)',          'notnull' => true, 'default'     => 0),
                'additional_phone'    => array('type' => 'TINYINT(1)',          'notnull' => true, 'default'     => 0),
                'additional_street'   => array('type' => 'TINYINT(1)',          'notnull' => true, 'default'     => 0),
                'additional_zip'      => array('type' => 'TINYINT(1)',          'notnull' => true, 'default'     => 0),
                'additional_email'    => array('type' => 'TINYINT(1)',          'notnull' => true, 'default'     => 0),
                'additional_city'     => array('type' => 'TINYINT(1)',          'notnull' => true, 'default'     => 0),
                'additional_comment'  => array('type' => 'TINYINT(1)',          'notnull' => true, 'default'     => 0),
            )
        );
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'voting_additionaldata',
            array(
                'id'                 => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'nickname'           => array('type' => 'VARCHAR(80)', 'notnull' => true, 'default' => '', 'renamefrom' => 'name'),
                'surname'            => array('type' => 'VARCHAR(80)', 'notnull' => true, 'default' => ''),
                'phone'              => array('type' => 'VARCHAR(80)', 'notnull' => true, 'default' => ''),
                'street'             => array('type' => 'VARCHAR(80)', 'notnull' => true, 'default' => ''),
                'zip'                => array('type' => 'VARCHAR(30)', 'notnull' => true, 'default' => ''),
                'city'               => array('type' => 'VARCHAR(80)', 'notnull' => true, 'default' => ''),
                'email'              => array('type' => 'VARCHAR(80)', 'notnull' => true, 'default' => ''),
                'comment'            => array('type' => 'TEXT', 'after' => 'email'),
                'voting_system_id'   => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'renamefrom' => 'voting_sytem_id'),
                'date_entered'       => array('type' => 'TIMESTAMP', 'notnull' => true, 'default_expr'=> 'CURRENT_TIMESTAMP', 'on_update' => 'CURRENT_TIMESTAMP'),
                'forename'           => array('type' => 'VARCHAR(80)', 'notnull' => true, 'default' => '')
            ),
            array(
                'voting_system_id'   => array('fields' => array('voting_system_id'))
            )
        );
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'voting_email',
            array(
                'id'     => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'email'  => array('type' => 'VARCHAR(255)'),
                'valid'  => array('type' => 'ENUM(\'0\',\'1\')', 'notnull' => true, 'default' => '0')
            ),
            array(
                'email'  => array('fields' => array('email'), 'type' => 'UNIQUE')
            )
        );
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'voting_rel_email_system',
            array(
                'email_id'   => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'system_id'  => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'voting_id'  => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'valid'      => array('type' => 'ENUM(\'0\',\'1\')', 'notnull' => true, 'default' => '0')
            ),
            array(
                'email_id'   => array('fields' => array('email_id','system_id'), 'type' => 'UNIQUE')
            )
        );
    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }

    return true;
}



function _votingInstall()
{
    try {

        /**************************************************************************
         * EXTENSION:   Initial creation (for contrexx editions <> premium with   *
         *              version < 3.0.0 which have this module not yet installed) *
         *                                                                        *
         * ADDED:       Contrexx v3.0.1                                           *
         **************************************************************************/
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'voting_additionaldata',
            array(
                'id'                     => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'nickname'               => array('type' => 'VARCHAR(80)', 'notnull' => true, 'default' => '', 'after' => 'id'),
                'surname'                => array('type' => 'VARCHAR(80)', 'notnull' => true, 'default' => '', 'after' => 'nickname'),
                'phone'                  => array('type' => 'VARCHAR(80)', 'notnull' => true, 'default' => '', 'after' => 'surname'),
                'street'                 => array('type' => 'VARCHAR(80)', 'notnull' => true, 'default' => '', 'after' => 'phone'),
                'zip'                    => array('type' => 'VARCHAR(30)', 'notnull' => true, 'default' => '', 'after' => 'street'),
                'city'                   => array('type' => 'VARCHAR(80)', 'notnull' => true, 'default' => '', 'after' => 'zip'),
                'email'                  => array('type' => 'VARCHAR(80)', 'notnull' => true, 'default' => '', 'after' => 'city'),
                'comment'                => array('type' => 'text', 'after' => 'email'),
                'voting_system_id'       => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'after' => 'comment'),
                'date_entered'           => array('type' => 'timestamp', 'notnull' => true, 'after' => 'voting_system_id'),
                'forename'               => array('type' => 'VARCHAR(80)', 'notnull' => true, 'default' => '', 'after' => 'date_entered')
            ),
            array(
                'voting_system_id'       => array('fields' => array('voting_system_id'))
            ),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."voting_additionaldata` (`id`, `nickname`, `surname`, `phone`, `street`, `zip`, `city`, `email`, `comment`, `voting_system_id`, `date_entered`, `forename`)
            VALUES  (1, '', '', '', '', '', '', '', '', 11, '2009-03-04 22:28:11', ''),
                    (2, '', '', '', '', '', '', '', '', 11, '2009-03-04 22:29:23', ''),
                    (3, 'additional_nickname', '', '', '', '', 'additional_city', '', '', 11, '2009-03-04 22:33:04', 'additional_forename'),
                    (4, '', '', '', '', '', '', '', '', 12, '2010-12-10 07:37:03', ''),
                    (5, '', '', '', '', '', '', '', '', 12, '2010-12-13 06:15:55', '')
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'voting_email',
            array(
                'id'         => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'email'      => array('type' => 'VARCHAR(255)', 'after' => 'id'),
                'valid'      => array('type' => 'ENUM(\'0\',\'1\')', 'notnull' => true, 'default' => '0', 'after' => 'email')
            ),
            array(
                'email'      => array('fields' => array('email'), 'type' => 'UNIQUE')
            ),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."voting_email` (`id`, `email`, `valid`)
            VALUES (13, 'markus.binggeli@comvation.com', '0')
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'voting_rel_email_system',
            array(
                'email_id'       => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'system_id'      => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'email_id'),
                'voting_id'      => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'system_id'),
                'valid'          => array('type' => 'ENUM(\'0\',\'1\')', 'notnull' => true, 'default' => '0', 'after' => 'voting_id')
            ),
            array(
                'email_id'       => array('fields' => array('email_id','system_id'), 'type' => 'UNIQUE')
            ),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."voting_rel_email_system` (`email_id`, `system_id`, `voting_id`, `valid`)
            VALUES (13, 8, 34, '0')
            ON DUPLICATE KEY UPDATE `email_id` = `email_id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'voting_results',
            array(
                'id'                     => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'voting_system_id'       => array('type' => 'INT(11)', 'notnull' => false, 'after' => 'id'),
                'question'               => array('type' => 'CHAR(200)', 'notnull' => false, 'after' => 'voting_system_id'),
                'votes'                  => array('type' => 'INT(11)', 'default' => '0', 'after' => 'question')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."voting_results` (`id`, `voting_system_id`, `question`, `votes`)
            VALUES  (34, 8, 'Einfache und intuitive Bedienung', 1),
                (35, 8, 'Grösst mögliche Sicherheit', 0),
                (43, 8, 'Vielseitige Auswahl an Modulen', 0),
                (44, 8, 'Möglichst kurze Umsetzungszeit des Projektes', 0),
                (45, 8, 'Kompetente und schnelle Unterstützung', 0),
                (46, 8, 'Preis', 0),
                (52, 11, 'Werbeagentur', 2),
                (53, 11, 'IT-Firma', 1),
                (54, 11, 'Consultant', 1),
                (55, 12, 'Hammermässig!', 0),
                (56, 12, 'Sehr schön', 1),
                (57, 12, 'Ist OK', 1),
                (58, 12, 'Naja...', 0),
                (59, 12, 'Gar nicht', 0)
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'voting_system',
            array(
                'id'                     => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'date'                   => array('type' => 'timestamp', 'notnull' => true, 'after' => 'id'),
                'title'                  => array('type' => 'VARCHAR(60)', 'notnull' => true, 'default' => '', 'after' => 'date'),
                'question'               => array('type' => 'text', 'after' => 'title'),
                'status'                 => array('type' => 'TINYINT(1)', 'default' => '1', 'after' => 'question'),
                'submit_check'           => array('type' => 'ENUM(\'cookie\',\'email\')', 'notnull' => true, 'default' => 'cookie', 'after' => 'status'),
                'votes'                  => array('type' => 'INT(11)', 'default' => '0', 'after' => 'submit_check'),
                'additional_nickname'    => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'votes'),
                'additional_forename'    => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'additional_nickname'),
                'additional_surname'     => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'additional_forename'),
                'additional_phone'       => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'additional_surname'),
                'additional_street'      => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'additional_phone'),
                'additional_zip'         => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'additional_street'),
                'additional_email'       => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'additional_zip'),
                'additional_city'        => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'additional_email'),
                'additional_comment'     => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'additional_city')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."voting_system` (`id`, `date`, `title`, `question`, `status`, `submit_check`, `votes`, `additional_nickname`, `additional_forename`, `additional_surname`, `additional_phone`, `additional_street`, `additional_zip`, `additional_email`, `additional_city`, `additional_comment`)
            VALUES  (8, '2010-12-13 06:44:32', 'Wichtige Eingenschaften', 'Welche wichtige Eigenschaften zeichnen ein anwenderfreundliches Content Management System aus?', 0, 'email', 1, 1, 1, 1, 0, 0, 0, 0, 1, 0),
                    (11, '2010-12-13 06:44:39', 'Webprojekte', 'Mit wem würden Sie Ihr Webprojekt besprechen?', 0, 'cookie', 4, 1, 1, 0, 0, 0, 0, 0, 1, 0),
                    (12, '2010-12-13 06:44:45', 'Wie gefällt Ihnen das neue Layout?', 'Wie gefällt Ihnen das neue Layout?', 1, 'cookie', 2, 0, 0, 0, 0, 0, 0, 0, 0, 0)
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }
}
