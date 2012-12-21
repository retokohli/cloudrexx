<?php

function _docsysUpdate()
{
    global $objDatabase, $_ARRAYLANG;

    try{
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_docsys_entry_category',
            array(
                'entry'      => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
                'category'   => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true)
            )
        );

        if (\Cx\Lib\UpdateUtil::column_exist(DBPREFIX . 'module_docsys', 'catid')) {
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


        \Cx\Lib\UpdateUtil::table(
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
    catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }

    return true;
}



function _docsysInstall()
{
    try {

        /**************************************************************************
         * EXTENSION:   Initial creation (for contrexx editions <> premium with   *
         *              version < 3.0.0 which have this module not yet installed) *
         *                                                                        *
         * ADDED:       Contrexx v3.0.1                                           *
         **************************************************************************/
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_docsys',
            array(
                'id'             => array('type' => 'INT(6)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'date'           => array('type' => 'INT(14)', 'notnull' => false, 'after' => 'id'),
                'title'          => array('type' => 'VARCHAR(250)', 'notnull' => true, 'default' => '', 'after' => 'date'),
                'author'         => array('type' => 'VARCHAR(150)', 'notnull' => true, 'default' => '', 'after' => 'title'),
                'text'           => array('type' => 'mediumtext', 'after' => 'author'),
                'source'         => array('type' => 'VARCHAR(250)', 'notnull' => true, 'default' => '', 'after' => 'text'),
                'url1'           => array('type' => 'VARCHAR(250)', 'notnull' => true, 'default' => '', 'after' => 'source'),
                'url2'           => array('type' => 'VARCHAR(250)', 'notnull' => true, 'default' => '', 'after' => 'url1'),
                'lang'           => array('type' => 'INT(2)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'url2'),
                'userid'         => array('type' => 'INT(6)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'lang'),
                'startdate'      => array('type' => 'INT(14)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'userid'),
                'enddate'        => array('type' => 'INT(14)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'startdate'),
                'status'         => array('type' => 'TINYINT(4)', 'notnull' => true, 'default' => '1', 'after' => 'enddate'),
                'changelog'      => array('type' => 'INT(14)', 'notnull' => true, 'default' => '0', 'after' => 'status')
            ),
            array(
                'newsindex'      => array('fields' => array('title','text'), 'type' => 'FULLTEXT')
            ),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_docsys` (`id`, `date`, `title`, `author`, `text`, `source`, `url1`, `url2`, `lang`, `userid`, `startdate`, `enddate`, `status`, `changelog`)
            VALUES (1, 1292236792, 'Google Sitemaps', 'system', '<div><strong><font size=\"2\">Nutzen und Funktion der Google Sitemaps</font></strong></div>\r\n<div>&nbsp;</div>\r\n<div><font size=\"2\">Google Sitemaps sind ein neuer Dienst, welcher von der Suchmaschine Google angeboten. Der Service ist momentan noch in der BETA-Phase, hat sich im Bereich der Suchmaschinen Optimierung bereits etabliert.</font></div>\r\n<div>&nbsp;</div>\r\n<div><font size=\"2\">Das Sitemapprotokoll dient dazu, Suchmaschinen die URLs auf Ihren Websites zu melden, die zum Durchsuchen verf&uuml;gbar sind. In ihrer einfachsten Form ist eine Sitemap, die das Sitemapprotokoll verwendet, eine XML-Datei, in der URLs f&uuml;r eine Website aufgelistet werden. Beachten Sie jedoch, dass das Sitemapprotokoll nur eine Erg&auml;nzung, keinen Ersatz f&uuml;r die crawlerbasierten Verfahren darstellt, die von Suchmaschinen bereits zur Erkundung von URLs genutzt werden. Indem Sie eine Sitemap (oder mehrere) bei einer Suchmaschine einreichen, tragen Sie dazu bei, dass die Crawler der Suchmaschine bessere Ergebnisse beim Durchsuchen Ihrer Website erzielen.</font></div>\r\n<div>&nbsp;</div>\r\n<div><font size=\"2\">Contrexx erstellt beim &Auml;ndern und Erstellen Ihres Seiteninhaltes automatisch ein Sitemap-File f&uuml;r Sie. Damit dieses File von Google auch gefunden wird, m&uuml;ssen Sie sich jedoch manuell auf der Google-Seite f&uuml;r den Dienst registrieren. Dies k&ouml;nnen Sie auf folgender Seite machen: </font><a href=\"https://www.google.com/webmasters/sitemaps/login?hl=de\"><font size=\"2\">https://www.google.com/webmasters/sitemaps/login?hl=de</font></a></div>\r\n<div>&nbsp;</div>\r\n<div>&nbsp;</div>\r\n<div><font size=\"2\">Sie k&ouml;nnen die automatische Erstellung der Google-Sitemap in folgendem Menu aktivieren:</font></div>\r\n<ul>\r\n    <li>Administration &gt; Grundeinstellung &gt; System &gt; Subsysteme</li>\r\n</ul>\r\n<div><font size=\"2\">Sobald Sie den Menupunkt auf aktiviert gesetzt haben, wird bei jeder &Auml;nderung Ihres Seiteninhalts automatisch eine Sitemap generiert. Diese befindet sich im Hauptverzeichnis Ihres Webservers und tr&auml;gt den Namen <strong>sitemap.xml</strong>.</font></div>\r\n<div>&nbsp;</div>\r\n<div>&nbsp;</div>\r\n<div><font size=\"2\">Beispiel: </font><a href=\"http://www.beispiel.ch/\"><font size=\"2\">http://www.beispiel.ch</font></a></div>\r\n<div><font size=\"2\">Pfad zur Sitemap-Datei: </font><a href=\"http://www.beispiel.ch/sitemap.xml\"><font size=\"2\">http://www.beispiel.ch/sitemap.xml</font></a></div>\r\n<div>&nbsp;</div>\r\n<div><font size=\"2\">Beachten Sie bitte, dass zur fehlerfreien Erstellung mindestens die Berechtigungen 666 auf die Datei sitemap.xml gesetzt sein m&uuml;ssen. Ansonsten kann die Datei nicht geschrieben werden. Ausserdem muss unbedingt eine Datei mit dem Namen sitemap.xml vorhanden sein, da wir aus Sicherheitsgr&uuml;nden im Hauptverzeichnis keine neuen Dateien erzeugen k&ouml;nnen.</font></div>\r\n<div>&nbsp;</div>', '', '', '', 1, 1, 0, 0, 1, 1235730488)
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_docsys_categories',
            array(
                'catid'          => array('type' => 'INT(2)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'name'           => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => '', 'after' => 'catid'),
                'lang'           => array('type' => 'INT(2)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'after' => 'name'),
                'sort_style'     => array('type' => 'ENUM(\'alpha\',\'date\',\'date_alpha\')', 'notnull' => true, 'default' => 'alpha', 'after' => 'lang')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_docsys_categories` (`catid`, `name`, `lang`, `sort_style`)
            VALUES  (1, 'Anleitungen', 1, 'alpha'),
                    (2, 'Produkt Infos', 1, 'alpha'),
                    (3, 'Beschreibungen', 1, 'alpha')
            ON DUPLICATE KEY UPDATE `catid` = `catid`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_docsys_entry_category',
            array(
                'entry'          => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
                'category'       => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true, 'after' => 'entry')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_docsys_entry_category` (`entry`, `category`)
            VALUES  (1, 2),
                    (5, 2)
            ON DUPLICATE KEY UPDATE `entry` = `entry`
        ");

    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }
}
