<?php

function _livecamUpdate()
{
    global $objDatabase, $objUpdate, $_CONFIG;

    try {
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_livecam',
            array(
                'id'                 => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'primary' => true),
                'currentImagePath'   => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '/webcam/cam1/current.jpg'),
                'archivePath'        => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '/webcam/cam1/archive/'),
                'thumbnailPath'      => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '/webcam/cam1/thumbs/'),
                'maxImageWidth'      => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '400'),
                'thumbMaxSize'       => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '200'),
                'shadowboxActivate'  => array('type' => 'SET(\'1\',\'0\')', 'notnull' => true, 'default' => '1', 'renamefrom' => 'lightboxActivate'),
                'showFrom'           => array('type' => 'INT(14)', 'notnull' => true, 'default' => '0'),
                'showTill'           => array('type' => 'INT(14)', 'notnull' => true, 'default' => '0')
            )
        );

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_livecam_settings',
            array(
                'setid'      => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'setname'    => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => ''),
                'setvalue'   => array('type' => 'TEXT')
            )
        );
    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }

    $query = "SELECT 1 FROM `".DBPREFIX."module_livecam_settings` WHERE `setname` = 'amount_of_cams'";
    $objResult = $objDatabase->SelectLimit($query, 1);
    if ($objResult !== false) {
        if ($objResult->RecordCount() == 0) {
            $query = "INSERT INTO `".DBPREFIX."module_livecam_settings` (`setname`, `setvalue`) VALUES ('amount_of_cams', '1')";
            if ($objDatabase->Execute($query) === false) {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
    } else {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }




    /************************************************
    * BUGFIX:   Migrate settings                    *
    * ADDED:    2.1.2                               *
    ************************************************/
    if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '2.0.0')) {
        $arrFormerSettings = array(
            'currentImageUrl'   => '',
            'archivePath'       => '',
            'thumbnailPath'     => ''
        );

        $query = "SELECT 1 FROM `".DBPREFIX."module_livecam` WHERE `id` = 1";
        $objResult = $objDatabase->SelectLimit($query, 1);
        if ($objResult !== false) {
            if ($objResult->RecordCount() == 0) {
                $query = "SELECT `setname`, `setvalue` FROM `".DBPREFIX."module_livecam_settings` WHERE `setname` IN ('".implode("','", array_keys($arrFormerSettings))."')";
                $objResult = $objDatabase->Execute($query);
                if ($objResult !== false) {
                    while (!$objResult->EOF) {
                        $arrFormerSettings[$objResult->fields['setname']] = $objResult->fields['setvalue'];
                        $objResult->MoveNext();
                    }

                    $query = "INSERT INTO `".DBPREFIX."module_livecam` (`id`, `currentImagePath`, `archivePath`, `thumbnailPath`, `maxImageWidth`, `thumbMaxSize`, `shadowboxActivate`) VALUES
                            ('1', '".addslashes($arrFormerSettings['currentImageUrl'])."', '".addslashes($arrFormerSettings['archivePath'])."', '".addslashes($arrFormerSettings['thumbnailPath'])."', '400', '120', '0')";
                    if ($objDatabase->Execute($query) === false) {
                        return _databaseError($query, $objDatabase->ErrorMsg());
                    }
                } else {
                    return _databaseError($query, $objDatabase->ErrorMsg());
                }
            }
        } else {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }

        foreach (array_keys($arrFormerSettings) as $setting) {
            $query = "DELETE FROM `".DBPREFIX."module_livecam_settings` WHERE `setname` = '".$setting."'";
            if ($objDatabase->Execute($query) === false) {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
    }

    $defaultFrom = mktime(0, 0);
    $defaultTill = mktime(23, 59);
    //set new default settings
    $query = "UPDATE `".DBPREFIX."module_livecam` SET `showFrom`=$defaultFrom, `showTill`=$defaultTill WHERE `showFrom` = '0'";
    if ($objDatabase->Execute($query) === false) {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }




    /************************************************
    * BUGFIX:   Update content page                 *
    * ADDED:    2.1.3                               *
    ************************************************/
    // both spaces in the search and replace pattern are required in that case
    \Cx\Lib\UpdateUtil::migrateContentPage('livecam', null, ' {LIVECAM_IMAGE_SHADOWBOX}', ' rel="{LIVECAM_IMAGE_SHADOWBOX}"', '2.1.3');

    return true;
}



function _livecamInstall()
{
    try {

        /**************************************************************************
         * EXTENSION:   Initial creation (for contrexx editions <> premium with   *
         *              version < 3.0.0 which have this module not yet installed) *
         *                                                                        *
         * ADDED:       Contrexx v3.0.1                                           *
         **************************************************************************/
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_livecam',
            array(
                'id'                     => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'primary' => true),
                'currentImagePath'       => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '/webcam/cam1/current.jpg', 'after' => 'id'),
                'archivePath'            => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '/webcam/cam1/archive/', 'after' => 'currentImagePath'),
                'thumbnailPath'          => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '/webcam/cam1/thumbs/', 'after' => 'archivePath'),
                'maxImageWidth'          => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '400', 'after' => 'thumbnailPath'),
                'thumbMaxSize'           => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '200', 'after' => 'maxImageWidth'),
                'shadowboxActivate'      => array('type' => 'SET(\'1\',\'0\')', 'notnull' => true, 'default' => '1', 'after' => 'thumbMaxSize'),
                'showFrom'               => array('type' => 'INT(14)', 'notnull' => true, 'default' => '0', 'after' => 'shadowboxActivate'),
                'showTill'               => array('type' => 'INT(14)', 'notnull' => true, 'default' => '0', 'after' => 'showFrom')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_livecam` (`id`, `currentImagePath`, `archivePath`, `thumbnailPath`, `maxImageWidth`, `thumbMaxSize`, `shadowboxActivate`, `showFrom`, `showTill`)
            VALUES (1, 'http://heimenschwand.ch/webcam/current.jpg', '/webcam/cam1/archive/', '/webcam/cam1/thumbs', 400, 120, '0', 1248818412, 1248904752)
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_livecam_settings',
            array(
                'setid'          => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'setname'        => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'setid'),
                'setvalue'       => array('type' => 'text', 'after' => 'setname')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_livecam_settings` (`setid`, `setname`, `setvalue`)
            VALUES (1, 'amount_of_cams', '1')
            ON DUPLICATE KEY UPDATE `setid` = `setid`
        ");

    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }
}
