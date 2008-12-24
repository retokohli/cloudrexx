<?php

function _livecamUpdate()
{
    global $objDatabase;

    $arrTables = $objDatabase->MetaTables();
    if (!in_array(DBPREFIX.'module_livecam', $arrTables)) {
        $query = "  CREATE TABLE `".DBPREFIX."module_livecam` (
                        `id`               int(10) unsigned NOT NULL DEFAULT '1',
                        `currentImagePath` varchar(255) NOT NULL DEFAULT '/webcam/cam1/current.jpg',
                        `archivePath`      varchar(255) NOT NULL DEFAULT '/webcam/cam1/archive/',
                        `thumbnailPath`    varchar(255) NOT NULL DEFAULT '/webcam/cam1/thumbs/',
                        `maxImageWidth`    int(10) unsigned NOT NULL default '400',
                        `thumbMaxSize`     int(10) unsigned NOT NULL default '200',
                        `lightboxActivate` set('1','0') NOT NULL default '1',
                    PRIMARY KEY  (`id`)
                    ) ENGINE=MyISAM;";
        $check = $objDatabase->Execute($query);
        if ($check == false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    $query = "  SELECT setname, setvalue FROM ".DBPREFIX."module_livecam_settings";
    $result = $objDatabase->Execute($query);
    // just in case
    $settings = array(  "currentImageUrl"   => "/webcam/cam1/current.jpg",
                        "archivePath"       => "/webcam/cam1/archive/",
                        "thumbnailPath"     => "/webcam/cam1/thumbs/");
    if ($result !== false) {
        while (!$result->EOF) {
            $settings[$result->fields['setname']] = $result->fields["setvalue"];
            $result->MoveNext();
        }
    } else {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }

    /*
        old settings:
        currentImageUrl
        archivePath
        thumbnailPath

        new settings:
        currentImagePath
        archivePath
        thumbnailPath
        maxImageWidth
        thumbMaxSize
        lightboxActivate
    */

    $query = "  SELECT currentImagePath FROM ".DBPREFIX."module_livecam";
    $check = $objDatabase->SelectLimit($query, 1);
    if ($check !== false) {
        if ($check->RecordCount() == 0) {
            $id = 1;
        } else {
            $query = "  SELECT max(id) as id FROM ".DBPREFIX."module_livecam";
            $idRes = $objDatabase->Execute($query);
            if ($idRes !== false) {
                $id = ++$idRes->fields['id'];
            }
        }

        $query = "  INSERT INTO ".DBPREFIX."module_livecam
                    (
                        id,
                        currentImagePath,
                        archivePath,
                        thumbnailPath,
                        maxImageWidth,
                        thumbMaxSize,
                        lightboxActivate
                    )
                    VALUES
                    (
                        ".$id.",
                        '".$settings['currentImageUrl']."',
                        '".$settings['archivePath']."',
                        '".$settings['thumbnailPath']."',
                        400,
                        200,
                        1
                    )";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    $query = "  DELETE FROM ".DBPREFIX."module_livecam_settings";
    if ($objDatabase->Execute($query) === false) {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }

    $query = "  INSERT INTO ".DBPREFIX."module_livecam_settings
                (setname, setvalue)
                VALUES
                ('amount_of_cams', ".$id.")";
    if ($objDatabase->Execute($query) === false) {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }

    return true;
}
?>
