<?php

function _livecamUpdate()
{
    global $objDatabase, $_ARRAYLANG;

    $arrColumns = $objDatabase->MetaColumns(DBPREFIX.'module_livecam');
    if ($arrColumns === false) {
        // try to create the table before bitching around
        $query = "
              CREATE TABLE `".DBPREFIX."module_livecam` (
                  `id` int(10) unsigned NOT NULL default '1',
                  `currentImagePath` varchar(255)     NOT NULL default '/webcam/cam1/current.jpg',
                  `archivePath`      varchar(255)     NOT NULL default '/webcam/cam1/archive/',
                  `thumbnailPath`    varchar(255)     NOT NULL default '/webcam/cam1/thumbs/',
                  `maxImageWidth`    int(10) unsigned NOT NULL default '400',
                  `thumbMaxSize`     int(10) unsigned NOT NULL default '200',
                  `lightboxActivate` set('1','0')     NOT NULL default '1',
                  `showFrom`         int(14)          NOT NULL,
                  `showTill`         int(14)          NOT NULL,
                  PRIMARY KEY  (`id`)
            ) ENGINE=MyISAM
        " ;
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
        // try again
        $arrColumns = $objDatabase->MetaColumns(DBPREFIX.'module_livecam');
        if ($arrColumns === false) {
            setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_livecam'));
            return false;
        }
    }

    if (!isset($arrColumns['SHOWFROM'])) {
        $query = "ALTER TABLE `".DBPREFIX."module_livecam` ADD `showFrom` INT(14) NOT NULL ;" ;
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    if (!isset($arrColumns['SHOWTILL'])) {
        $query = "ALTER TABLE `".DBPREFIX."module_livecam` ADD `showTill` INT(14) NOT NULL ;" ;
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    $defaultFrom = mktime(0, 0);
    $defaultTill = mktime(23, 59);
        //set new default settings
    $query = "UPDATE `".DBPREFIX."module_livecam` SET `showFrom`=$defaultFrom, `showTill`=$defaultTill WHERE 1";
    if ($objDatabase->Execute($query) === false) {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }

    return true;
}
?>
