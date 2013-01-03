<?php

function _podcastUpdate() {
    global $objDatabase, $_ARRAYLANG, $objUpdate, $_CONFIG;

    //move podcast images directory
    $path = ASCMS_DOCUMENT_ROOT . '/images';
    $oldImagesPath = '/content/podcast';
    $newImagesPath = '/podcast';

    if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '1.2.1')) {
        if (   !file_exists($path . $newImagesPath)
            && file_exists($path . $oldImagesPath)
        ) {
            \Cx\Lib\FileSystem\FileSystem::makeWritable($path . $oldImagesPath);
            if (!\Cx\Lib\FileSystem\FileSystem::copy_folder($path . $oldImagesPath, $path . $newImagesPath)) {
                setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_TO_MOVE_DIRECTORY'], $path . $oldImagesPath, $path . $newImagesPath));
                return false;
            }
        }
        \Cx\Lib\FileSystem\FileSystem::makeWritable($path . $newImagesPath);
        \Cx\Lib\FileSystem\FileSystem::makeWritable($path . $newImagesPath . '/youtube_thumbnails');

        //change thumbnail paths
        $query = "UPDATE `" . DBPREFIX . "module_podcast_medium` SET `thumbnail` = REPLACE(`thumbnail`, '/images/content/podcast/', '/images/podcast/')";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    //set new default settings
    $query = "UPDATE `" . DBPREFIX . "module_podcast_settings` SET `setvalue` = '50' WHERE `setname` = 'thumb_max_size' AND `setvalue` = ''";
    if ($objDatabase->Execute($query) === false) {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }
    $query = "UPDATE `" . DBPREFIX . "module_podcast_settings` SET `setvalue` = '85' WHERE `setname` = 'thumb_max_size_homecontent' AND `setvalue` = ''";
    if ($objDatabase->Execute($query) === false) {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }

    return true;
}



function _podcastInstall()
{
    try {

        /**************************************************************************
         * EXTENSION:   Initial creation (for contrexx editions <> premium with   *
         *              version < 3.0.0 which have this module not yet installed) *
         *                                                                        *
         * ADDED:       Contrexx v3.0.1                                           *
         **************************************************************************/
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_podcast_category',
            array(
                'id'             => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'title'          => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'id'),
                'description'    => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'title'),
                'status'         => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'description')
            ),
            array(
                'podcastindex'   => array('fields' => array('title','description'), 'type' => 'FULLTEXT')
            ),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_podcast_category` (`id`, `title`, `description`, `status`)
            VALUES  (1, 'Demo Kategorie', 'Das ist eine Demo Kategorie', 1),
                    (2, 'Web 2.0 English', '', 1),
                    (3, 'Web 2.0 Deutsch', '', 1),
                    (4, 'Adobe Photoshop', '', 1),
                    (5, 'Internettrends', '', 1)
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_podcast_medium',
            array(
                'id'             => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'title'          => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'id'),
                'youtube_id'     => array('type' => 'VARCHAR(25)', 'notnull' => true, 'default' => '', 'after' => 'title'),
                'author'         => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'youtube_id'),
                'description'    => array('type' => 'text', 'after' => 'author'),
                'source'         => array('type' => 'text', 'after' => 'description'),
                'thumbnail'      => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'source'),
                'template_id'    => array('type' => 'INT(11)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'thumbnail'),
                'width'          => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'template_id'),
                'height'         => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'width'),
                'playlenght'     => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'height'),
                'size'           => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'playlenght'),
                'views'          => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'size'),
                'status'         => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'views'),
                'date_added'     => array('type' => 'INT(14)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'status')
            ),
            array(
                'podcastindex'   => array('fields' => array('title','description'), 'type' => 'FULLTEXT')
            ),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_podcast_medium` (`id`, `title`, `youtube_id`, `author`, `description`, `source`, `thumbnail`, `template_id`, `width`, `height`, `playlenght`, `size`, `views`, `status`, `date_added`)
            VALUES  (5, 'Schlagwort Web 2.0 Part 2', 'deqIIoSyuik', '', 'Vortrag anlässlich der Cisco Expo 2008 Vienna', 'http://youtube.com/v/deqIIoSyuik', 'images/podcast/youtube_thumbnails/youtube_deqIIoSyuik.jpg', 101, 425, 350, 549, 0, 1, 1, 1292236792),
                    (7, 'Im Gespräch: Heiko Hebig zu den neuesten Trends im Internet', 'kvzX0UTa5zI', 'mediaTREFF', 'Kongress Web 2.0 in Wiesbaden', 'http://youtube.com/v/kvzX0UTa5zI', 'images/podcast/youtube_thumbnails/youtube_kvzX0UTa5zI.jpg', 101, 425, 350, 585, 0, 1, 1, 1292236792),
                    (9, 'Social Networking - Der große Trend im Internet', 'GSuJwQxU6a8', '', 'Die Idee ist einfach: Menschen mit gleichen Interessen treffen sich auf Internetportalen, um z. B. Persönliches auszutauschen oder für den Beruf eine Gemeinschaft aufzubauen. Die Nutzerzahlen steigen rasant. Doch bei allen \"Social Networking\"-Portalen gibt es noch keine zündende Idee, wie man damit auch richtig Geld verdienen kann.', 'http://youtube.com/v/GSuJwQxU6a8', 'images/podcast/youtube_thumbnails/youtube_GSuJwQxU6a8.jpg', 101, 425, 350, 291, 0, 1, 1, 1292236792),
                    (10, 'Are You Taking Advantage of Web 2.0 Marketing', 'HR0Ip0JwJKk', 'Jack Humphrey', '\"Are You Taking Advantage of Web 2.0 Marketing?\" by Jack Humphrey', 'http://youtube.com/v/HR0Ip0JwJKk', 'images/podcast/youtube_thumbnails/youtube_HR0Ip0JwJKk.jpg', 101, 425, 350, 266, 0, 1, 1, 1292236792),
                    (11, 'Marketingberater 2.0@Web 2.0-Expo (3): iStockphoto', 'Lp8oqHseoOw', '', 'Berater, Coach, Blogger und Podcaster Sebastian Voss alias Marketingberater 2.0, hat sich zur Web2.0Expo in Berlin aufgemacht, um für Sie die neuesten Trends in Sachen Marketing und Mitmach-Web zu erhaschen. Ausgestattet mit seiner nagelneuen Handycam und einer Handvoll Neugier spricht er mit CEOs und Mitarbeitern von Adobe Systems, Wikio, GfK GeoMarketing uvm.', 'http://youtube.com/v/Lp8oqHseoOw', 'images/podcast/youtube_thumbnails/youtube_Lp8oqHseoOw.jpg', 101, 425, 350, 545, 0, 1, 1, 1292236792),
                    (12, 'What is Web 2.0?', '0LzQIUANnHc', 'Andy Gutmans, Co-founder and VP, Zend', 'It''s one of the biggest buzzwords out there, but what exactly does it mean? Andy Gutmans of Zend defines Web 2.0 and explains how it''s changing the face of the Internet.', 'http://youtube.com/v/0LzQIUANnHc', 'images/podcast/youtube_thumbnails/youtube_0LzQIUANnHc.jpg', 101, 425, 350, 182, 0, 1, 1, 1292236792),
                    (13, 'Contrexx CMS - Saferpay® Zahlungsmittel', 'khed50iZnVc', 'Comvation', 'Dieses Tutorial zeigt Ihnen, wie die Saferpay® Zahlungsmittel der Telekurs Card Solutions in den Contrexx CMS Shop eingebunden werden können.', 'http://youtube.com/v/khed50iZnVc', 'images/podcast/youtube_thumbnails/youtube_khed50iZnVc.jpg', 101, 425, 350, 170, 0, 1, 1, 1292237521),
                    (14, 'Contrexx HowTo: Upload a Podcast', '0FSU3i_QBZQ', 'Comvation', 'This Tutorial shows you how to upload a new podcast to the Contrexx podcast module', 'http://youtube.com/v/0FSU3i_QBZQ', 'images/podcast/youtube_thumbnails/youtube_0FSU3i_QBZQ.jpg', 101, 425, 350, 163, 0, 1, 1, 1292237769),
                    (15, 'Contrexx SuisseID', 'WdiTcQoSS-o', 'COMVATION AG', 'Dieses Video erklärt die Möglichkeiten von Contrexx SuisseID.', 'http://youtube.com/v/WdiTcQoSS-o', 'images/podcast/youtube_thumbnails/youtube_WdiTcQoSS-o.jpg', 101, 425, 350, 0, 0, 1, 1, 1292948589)
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_podcast_rel_category_lang',
            array(
                'category_id'    => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'lang_id'        => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'category_id')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_podcast_rel_category_lang` (`category_id`, `lang_id`)
            VALUES  (1, 1),
                    (1, 4),
                    (1, 2),
                    (1, 5),
                    (1, 3),
                    (1, 6),
                    (2, 1),
                    (2, 2),
                    (3, 1),
                    (4, 1),
                    (4, 2),
                    (5, 1),
                    (5, 4),
                    (5, 2),
                    (5, 5),
                    (5, 3),
                    (5, 6)
            ON DUPLICATE KEY UPDATE `category_id` = `category_id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_podcast_rel_medium_category',
            array(
                'medium_id'      => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'category_id'    => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'medium_id')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_podcast_rel_medium_category` (`medium_id`, `category_id`)
            VALUES  (12, 2),
                    (15, 5),
                    (14, 1),
                    (5, 3),
                    (9, 3),
                    (9, 5),
                    (7, 5),
                    (7, 3),
                    (10, 2),
                    (10, 5),
                    (11, 3),
                    (13, 1)
            ON DUPLICATE KEY UPDATE `medium_id` = `medium_id`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_podcast_settings',
            array(
                'setid'          => array('type' => 'INT(6)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'setname'        => array('type' => 'VARCHAR(250)', 'notnull' => true, 'default' => '', 'after' => 'setid'),
                'setvalue'       => array('type' => 'text', 'after' => 'setname'),
                'status'         => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'setvalue')
            ),
            null,
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `".DBPREFIX."module_podcast_settings` (`setid`, `setname`, `setvalue`, `status`)
            VALUES  (3, 'default_width', '320', 1),
                    (4, 'default_height', '240', 1),
                    (5, 'feed_title', 'Contrexx Demo-Seite Neuste Videos', 1),
                    (6, 'feed_description', 'Neuste Videos', 1),
                    (7, 'feed_image', '', 1),
                    (8, 'latest_media_count', '4', 1),
                    (9, 'latest_media_categories', '1,4,2,5,3', 1),
                    (10, 'thumb_max_size', '140', 1),
                    (11, 'thumb_max_size_homecontent', '90', 1),
                    (12, 'auto_validate', '0', 1)
            ON DUPLICATE KEY UPDATE `setid` = `setid`
        ");

        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'module_podcast_template',
            array(
                'id'             => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'description'    => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'id'),
                'template'       => array('type' => 'text', 'after' => 'description'),
                'extensions'     => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'template')
            ),
            array(
                'description'    => array('fields' => array('description'), 'type' => 'UNIQUE')
            ),
            'MyISAM',
            'cx3upgrade'
        );
        \Cx\Lib\UpdateUtil::sql("
            INSERT INTO `\".DBPREFIX.\"module_podcast_template` (`id`, `description`, `template`, `extensions`)
            VALUES  (50, 'Video für Windows (Windows Media Player Plug-in)', '<object id=\"podcastPlayer\" classid=\"clsid:6BF52A52-394A-11d3-B153-00C04F79FAA6\" standby=\"Loading Windows Media Player components...\" type=\"application/x-oleobject\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<embed type=\"application/x-mplayer2\" name=\"podcastPlayer\" showstatusbar=\"1\" src=\"[[MEDIUM_URL]]\" autostart=\"1\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]+70\" />\r\n<param name=\"URL\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"BufferingTime\" value=\"60\" />\r\n<param name=\"AllowChangeDisplaySize\" value=\"true\" />\r\n<param name=\"AutoStart\" value=\"true\" />\r\n<param name=\"EnableContextMenu\" value=\"true\" />\r\n<param name=\"stretchToFit\" value=\"true\" />\r\n<param name=\"ShowControls\" value=\"true\" />\r\n<param name=\"ShowTracker\" value=\"true\" />\r\n<param name=\"uiMode\" value=\"full\" />\r\n</object>', 'avi, wmv'),
                    (51, 'RealMedia (RealMedia Player Plug-in)', '<object id=\"podcastPlayer1\" classid=\"clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\">\r\n<param name=\"controls\" value=\"all\">\r\n<param name=\"autostart\" value=\"true\">\r\n<embed src=\"[[MEDIUM_URL]]\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" autostart=\"true\" type=\"video/x-pn-realvideo\" console=\"video1\" controls=\"All\" nojava=\"true\"></embed>\r\n</object>', 'ram, rpm'),
                    (52, 'QuickTime Film (QuickTime Plug-in)', '<object classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"autoplay\" value=\"true\" />\r\n<param name=\"controller\" value=\"true\" />\r\n<param name=\"target\" value=\"myself\" />\r\n<param name=\"type\" value=\"video/quicktime\" />\r\n<embed src=\"[[MEDIUM_URL]]\" width=[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" type=\"video/quicktime\" pluginspage=\"http://www.apple.com/quicktime/download/\" autoplay=\"true\" controller=\"true\" target=\"myself\" />\r\n</object>', 'mov, qt, mqv'),
                    (53, 'CAF-Audio (QuickTime Plug-in)', '<object classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"autoplay\" value=\"true\" />\r\n<param name=\"controller\" value=\"true\" />\r\n<param name=\"target\" value=\"myself\" />\r\n<param name=\"type\" value=\"audio/x-caf\" />\r\n<embed src=\"[[MEDIUM_URL]]\" width=[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" type=\"audio/x-caf\" pluginspage=\"http://www.apple.com/quicktime/download/\" autoplay=\"true\" controller=\"true\" target=\"myself\" />\r\n</object>', 'caf'),
                    (54, 'AAC-Audio (QuickTime Plug-in)', '<object classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"autoplay\" value=\"true\" />\r\n<param name=\"controller\" value=\"true\" />\r\n<param name=\"target\" value=\"myself\" />\r\n<param name=\"type\" value=\"audio/x-aac\" />\r\n<embed src=\"[[MEDIUM_URL]]\" width=[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" type=\"audio/x-aac\" pluginspage=\"http://www.apple.com/quicktime/download/\" autoplay=\"true\" controller=\"true\" target=\"myself\" />\r\n</object>', 'aac, adts'),
                    (55, 'AMR-Audio (QuickTime Plug-in)', '<object classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"autoplay\" value=\"true\" />\r\n<param name=\"controller\" value=\"true\" />\r\n<param name=\"target\" value=\"myself\" />\r\n<param name=\"type\" value=\"audio/AMR\" />\r\n<embed src=\"[[MEDIUM_URL]]\" width=[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" type=\"audio/AMR\" pluginspage=\"http://www.apple.com/quicktime/download/\" autoplay=\"true\" controller=\"true\" target=\"myself\" />\r\n</object>', 'amr'),
                    (56, 'GSM-Audio (QuickTime Plug-in)', '<object classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"autoplay\" value=\"true\" />\r\n<param name=\"controller\" value=\"true\" />\r\n<param name=\"target\" value=\"myself\" />\r\n<param name=\"type\" value=\"audio/x-gsm\" />\r\n<embed src=\"[[MEDIUM_URL]]\" width=[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" type=\"audio/x-gsm\" pluginspage=\"http://www.apple.com/quicktime/download/\" autoplay=\"true\" controller=\"true\" target=\"myself\" />\r\n</object>', 'gsm'),
                    (57, 'QUALCOMM PureVoice Audio (QuickTime Plug-in)', '<object classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"autoplay\" value=\"true\" />\r\n<param name=\"controller\" value=\"true\" />\r\n<param name=\"target\" value=\"myself\" />\r\n<param name=\"type\" value=\"audio/vnd.qcelp\" />\r\n<embed src=\"[[MEDIUM_URL]]\" width=[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" type=\"audio/vnd.qcelp\" pluginspage=\"http://www.apple.com/quicktime/download/\" autoplay=\"true\" controller=\"true\" target=\"myself\" />\r\n</object>', 'qcp'),
                    (58, 'MIDI (QuickTime Plug-in)', '<object classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"autoplay\" value=\"true\" />\r\n<param name=\"controller\" value=\"true\" />\r\n<param name=\"target\" value=\"myself\" />\r\n<param name=\"type\" value=\"audio/x-midi\" />\r\n<embed src=\"[[MEDIUM_URL]]\" width=[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" type=\"audio/x-midi\" pluginspage=\"http://www.apple.com/quicktime/download/\" autoplay=\"true\" controller=\"true\" target=\"myself\" />\r\n</object>', 'mid, midi, smf, kar'),
                    (59, 'uLaw/AU-Audio (QuickTime Plug-in)', '<object classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"autoplay\" value=\"true\" />\r\n<param name=\"controller\" value=\"true\" />\r\n<param name=\"target\" value=\"myself\" />\r\n<param name=\"type\" value=\"audio/basic\" />\r\n<embed src=\"[[MEDIUM_URL]]\" width=[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" type=\"audio/basic\" pluginspage=\"http://www.apple.com/quicktime/download/\" autoplay=\"true\" controller=\"true\" target=\"myself\" />\r\n</object>', 'au, snd, ulw'),
                    (60, 'AIFF-Audio (QuickTime Plug-in)', '<object classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"autoplay\" value=\"true\" />\r\n<param name=\"controller\" value=\"true\" />\r\n<param name=\"target\" value=\"myself\" />\r\n<param name=\"type\" value=\"audio/x-aiff\" />\r\n<embed src=\"[[MEDIUM_URL]]\" width=[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" type=\"audio/x-aiff\" pluginspage=\"http://www.apple.com/quicktime/download/\" autoplay=\"true\" controller=\"true\" target=\"myself\" />\r\n</object>', 'aiff, aif, aifc, cdda'),
                    (61, 'WAVE-Audio (QuickTime Plug-in)', '<object classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"autoplay\" value=\"true\" />\r\n<param name=\"controller\" value=\"true\" />\r\n<param name=\"target\" value=\"myself\" />\r\n<param name=\"type\" value=\"audio/x-wav\" />\r\n<embed src=\"[[MEDIUM_URL]]\" width=[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" type=\"audio/x-wav\" pluginspage=\"http://www.apple.com/quicktime/download/\" autoplay=\"true\" controller=\"true\" target=\"myself\" />\r\n</object>', 'wav, bwf'),
                    (62, 'Video für Windows (AVI) (QuickTime Plug-in)', '<object classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"autoplay\" value=\"true\" />\r\n<param name=\"controller\" value=\"true\" />\r\n<param name=\"target\" value=\"myself\" />\r\n<param name=\"type\" value=\"video/x-msvideo\" />\r\n<embed src=\"[[MEDIUM_URL]]\" width=[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" type=\"video/x-msvideo\" pluginspage=\"http://www.apple.com/quicktime/download/\" autoplay=\"true\" controller=\"true\" target=\"myself\" />\r\n</object>', 'avi, vfw'),
                    (63, 'AutoDesk Animator (FLC) (QuickTime Plug-in)', '<object classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"autoplay\" value=\"true\" />\r\n<param name=\"controller\" value=\"true\" />\r\n<param name=\"target\" value=\"myself\" />\r\n<param name=\"type\" value=\"video/flc\" />\r\n<embed src=\"[[MEDIUM_URL]]\" width=[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" type=\"video/flc\" pluginspage=\"http://www.apple.com/quicktime/download/\" autoplay=\"true\" controller=\"true\" target=\"myself\" />\r\n</object>', 'flc, fli, cel'),
                    (64, 'Digitales Video (DV) (QuickTime Plug-in)', '<object classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"autoplay\" value=\"true\" />\r\n<param name=\"controller\" value=\"true\" />\r\n<param name=\"target\" value=\"myself\" />\r\n<param name=\"type\" value=\"video/x-dv\" />\r\n<embed src=\"[[MEDIUM_URL]]\" width=[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" type=\"video/x-dv\" pluginspage=\"http://www.apple.com/quicktime/download/\" autoplay=\"true\" controller=\"true\" target=\"myself\" />\r\n</object>', 'dv, dif'),
                    (65, 'SDP-Stream-Beschreibung (QuickTime Plug-in)', '<object classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"autoplay\" value=\"true\" />\r\n<param name=\"controller\" value=\"true\" />\r\n<param name=\"target\" value=\"myself\" />\r\n<param name=\"type\" value=\"application/x-sdp\" />\r\n<embed src=\"[[MEDIUM_URL]]\" width=[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" type=\"application/x-sdp\" pluginspage=\"http://www.apple.com/quicktime/download/\" autoplay=\"true\" controller=\"true\" target=\"myself\" />\r\n</object>', 'sdp'),
                    (66, 'RTSP-Stream-Beschreibung (QuickTime Plug-in)', '<object classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"autoplay\" value=\"true\" />\r\n<param name=\"controller\" value=\"true\" />\r\n<param name=\"target\" value=\"myself\" />\r\n<param name=\"type\" value=\"application/x-rtsp\" />\r\n<embed src=\"[[MEDIUM_URL]]\" width=[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" type=\"application/x-rtsp\" pluginspage=\"http://www.apple.com/quicktime/download/\" autoplay=\"true\" controller=\"true\" target=\"myself\" />\r\n</object>', 'rtsp, rts'),
                    (67, 'MP3-Wiedergabeliste (QuickTime Plug-in)', '<object classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"autoplay\" value=\"true\" />\r\n<param name=\"controller\" value=\"true\" />\r\n<param name=\"target\" value=\"myself\" />\r\n<param name=\"type\" value=\"audio/x-mpegurl\" />\r\n<embed src=\"[[MEDIUM_URL]]\" width=[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" type=\"audio/x-mpegurl\" pluginspage=\"http://www.apple.com/quicktime/download/\" autoplay=\"true\" controller=\"true\" target=\"myself\" />\r\n</object>', 'm3u, m3url'),
                    (68, 'MPEG-Medien (QuickTime Plug-in)', '<object classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"autoplay\" value=\"true\" />\r\n<param name=\"controller\" value=\"true\" />\r\n<param name=\"target\" value=\"myself\" />\r\n<param name=\"type\" value=\"video/x-mpeg\" />\r\n<embed src=\"[[MEDIUM_URL]]\" width=[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" type=\"video/x-mpeg\" pluginspage=\"http://www.apple.com/quicktime/download/\" autoplay=\"true\" controller=\"true\" target=\"myself\" />\r\n</object>', 'mpeg, mpg, m1s, m1v, m1a, m75, m15, mp2'),
                    (69, '3GPP-Medien (QuickTime Plug-in)', '<object classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"autoplay\" value=\"true\" />\r\n<param name=\"controller\" value=\"true\" />\r\n<param name=\"target\" value=\"myself\" />\r\n<param name=\"type\" value=\"video/3gpp\" />\r\n<embed src=\"[[MEDIUM_URL]]\" width=[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" type=\"video/3gpp\" pluginspage=\"http://www.apple.com/quicktime/download/\" autoplay=\"true\" controller=\"true\" target=\"myself\" />\r\n</object>', '3gp, 3gpp'),
                    (70, '3GPP2-Medien (QuickTime Plug-in)', '<object classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"autoplay\" value=\"true\" />\r\n<param name=\"controller\" value=\"true\" />\r\n<param name=\"target\" value=\"myself\" />\r\n<param name=\"type\" value=\"video/3gpp2\" />\r\n<embed src=\"[[MEDIUM_URL]]\" width=[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" type=\"video/3gpp2\" pluginspage=\"http://www.apple.com/quicktime/download/\" autoplay=\"true\" controller=\"true\" target=\"myself\" />\r\n</object>', '3g2, 3gp2'),
                    (71, 'SD-Video (QuickTime Plug-in)', '<object classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"autoplay\" value=\"true\" />\r\n<param name=\"controller\" value=\"true\" />\r\n<param name=\"target\" value=\"myself\" />\r\n<param name=\"type\" value=\"video/sd-video\" />\r\n<embed src=\"[[MEDIUM_URL]]\" width=[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" type=\"video/sd-video\" pluginspage=\"http://www.apple.com/quicktime/download/\" autoplay=\"true\" controller=\"true\" target=\"myself\" />\r\n</object>', 'sdv'),
                    (72, 'AMC-Medien (QuickTime Plug-in)', '<object classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"autoplay\" value=\"true\" />\r\n<param name=\"controller\" value=\"true\" />\r\n<param name=\"target\" value=\"myself\" />\r\n<param name=\"type\" value=\"application/x-mpeg\" />\r\n<embed src=\"[[MEDIUM_URL]]\" width=[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" type=\"application/x-mpeg\" pluginspage=\"http://www.apple.com/quicktime/download/\" autoplay=\"true\" controller=\"true\" target=\"myself\" />\r\n</object>', 'amc'),
                    (73, 'MPEG-4-Medien (QuickTime Plug-in)', '<object classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"autoplay\" value=\"true\" />\r\n<param name=\"controller\" value=\"true\" />\r\n<param name=\"target\" value=\"myself\" />\r\n<param name=\"type\" value=\"video/mp4\" />\r\n<embed src=\"[[MEDIUM_URL]]\" width=[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" type=\"video/mp4\" pluginspage=\"http://www.apple.com/quicktime/download/\" autoplay=\"true\" controller=\"true\" target=\"myself\" />\r\n</object>', 'mp4'),
                    (74, 'AAC-Audiodatei (QuickTime Plug-in)', '<object classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"autoplay\" value=\"true\" />\r\n<param name=\"controller\" value=\"true\" />\r\n<param name=\"target\" value=\"myself\" />\r\n<param name=\"type\" value=\"audio/x-m4a\" />\r\n<embed src=\"[[MEDIUM_URL]]\" width=[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" type=\"audio/x-m4a\" pluginspage=\"http://www.apple.com/quicktime/download/\" autoplay=\"true\" controller=\"true\" target=\"myself\" />\r\n</object>', 'm4a'),
                    (75, 'AAC-Audio (geschützt) (QuickTime Plug-in)', '<object classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"autoplay\" value=\"true\" />\r\n<param name=\"controller\" value=\"true\" />\r\n<param name=\"target\" value=\"myself\" />\r\n<param name=\"type\" value=\"audio/x-m4p\" />\r\n<embed src=\"[[MEDIUM_URL]]\" width=[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" type=\"audio/x-m4p\" pluginspage=\"http://www.apple.com/quicktime/download/\" autoplay=\"true\" controller=\"true\" target=\"myself\" />\r\n</object>', 'm4p'),
                    (76, 'ACC-Audiobuch (QuickTime Plug-in)', '<object classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"autoplay\" value=\"true\" />\r\n<param name=\"controller\" value=\"true\" />\r\n<param name=\"target\" value=\"myself\" />\r\n<param name=\"type\" value=\"audio/x-m4b\" />\r\n<embed src=\"[[MEDIUM_URL]]\" width=[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" type=\"audio/x-m4b\" pluginspage=\"http://www.apple.com/quicktime/download/\" autoplay=\"true\" controller=\"true\" target=\"myself\" />\r\n</object>', 'm4b'),
                    (77, 'Video (geschützt) (QuickTime Plug-in)', '<object classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"autoplay\" value=\"true\" />\r\n<param name=\"controller\" value=\"true\" />\r\n<param name=\"target\" value=\"myself\" />\r\n<param name=\"type\" value=\"video/x-m4v\" />\r\n<embed src=\"[[MEDIUM_URL]]\" width=[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" type=\"video/x-m4v\" pluginspage=\"http://www.apple.com/quicktime/download/\" autoplay=\"true\" controller=\"true\" target=\"myself\" />\r\n</object>', 'm4v'),
                    (78, 'MP3-Audio (QuickTime Plug-in)', '<object classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"autoplay\" value=\"true\" />\r\n<param name=\"controller\" value=\"true\" />\r\n<param name=\"target\" value=\"myself\" />\r\n<param name=\"type\" value=\"audio/x-mpeg\" />\r\n<embed src=\"[[MEDIUM_URL]]\" width=[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" type=\"audio/x-mpeg\" pluginspage=\"http://www.apple.com/quicktime/download/\" autoplay=\"true\" controller=\"true\" target=\"myself\" />\r\n</object>', 'mp3, swa'),
                    (79, 'Sound Designer II (QuickTime Plug-in)', '<object classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"autoplay\" value=\"true\" />\r\n<param name=\"controller\" value=\"true\" />\r\n<param name=\"target\" value=\"myself\" />\r\n<param name=\"type\" value=\"audio/x-sd2\" />\r\n<embed src=\"[[MEDIUM_URL]]\" width=[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" type=\"audio/x-sd2\" pluginspage=\"http://www.apple.com/quicktime/download/\" autoplay=\"true\" controller=\"true\" target=\"myself\" />\r\n</object>', 'sd2'),
                    (80, 'BMP-Bild (QuickTime Plug-in)', '<object classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"autoplay\" value=\"true\" />\r\n<param name=\"controller\" value=\"true\" />\r\n<param name=\"target\" value=\"myself\" />\r\n<param name=\"type\" value=\"image/x-bmp\" />\r\n<embed src=\"[[MEDIUM_URL]]\" width=[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" type=\"image/x-bmp\" pluginspage=\"http://www.apple.com/quicktime/download/\" autoplay=\"true\" controller=\"true\" target=\"myself\" />\r\n</object>', 'bmp, dib'),
                    (81, 'MacPaint Bild (QuickTime Plug-in)', '<object classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"autoplay\" value=\"true\" />\r\n<param name=\"controller\" value=\"true\" />\r\n<param name=\"target\" value=\"myself\" />\r\n<param name=\"type\" value=\"image/x-macpaint\" />\r\n<embed src=\"[[MEDIUM_URL]]\" width=[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" type=\"image/x-macpaint\" pluginspage=\"http://www.apple.com/quicktime/download/\" autoplay=\"true\" controller=\"true\" target=\"myself\" />\r\n</object>', 'pntg, pnt, mac'),
                    (82, 'PICT-Bild (QuickTime Plug-in)', '<object classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"autoplay\" value=\"true\" />\r\n<param name=\"controller\" value=\"true\" />\r\n<param name=\"target\" value=\"myself\" />\r\n<param name=\"type\" value=\"image/x-pict\" />\r\n<embed src=\"[[MEDIUM_URL]]\" width=[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" type=\"image/x-pict\" pluginspage=\"http://www.apple.com/quicktime/download/\" autoplay=\"true\" controller=\"true\" target=\"myself\" />\r\n</object>', 'pict, pic, pct'),
                    (83, 'PNG-Bild (QuickTime Plug-in)', '<object classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"autoplay\" value=\"true\" />\r\n<param name=\"controller\" value=\"true\" />\r\n<param name=\"target\" value=\"myself\" />\r\n<param name=\"type\" value=\"image/x-png\" />\r\n<embed src=\"[[MEDIUM_URL]]\" width=[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" type=\"image/x-png\" pluginspage=\"http://www.apple.com/quicktime/download/\" autoplay=\"true\" controller=\"true\" target=\"myself\" />\r\n</object>', 'png'),
                    (84, 'QuickTime Bild (QuickTime Plug-in)', '<object classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"autoplay\" value=\"true\" />\r\n<param name=\"controller\" value=\"true\" />\r\n<param name=\"target\" value=\"myself\" />\r\n<param name=\"type\" value=\"image/x-quicktime\" />\r\n<embed src=\"[[MEDIUM_URL]]\" width=[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" type=\"image/x-quicktime\" pluginspage=\"http://www.apple.com/quicktime/download/\" autoplay=\"true\" controller=\"true\" target=\"myself\" />\r\n</object>', 'qtif, qti'),
                    (85, 'SGI-Bild (QuickTime Plug-in)', '<object classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"autoplay\" value=\"true\" />\r\n<param name=\"controller\" value=\"true\" />\r\n<param name=\"target\" value=\"myself\" />\r\n<param name=\"type\" value=\"image/x-sgi\" />\r\n<embed src=\"[[MEDIUM_URL]]\" width=[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" type=\"image/x-sgi\" pluginspage=\"http://www.apple.com/quicktime/download/\" autoplay=\"true\" controller=\"true\" target=\"myself\" />\r\n</object>', 'sgi, rgb'),
                    (86, 'TGA-Bild (QuickTime Plug-in)', '<object classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"autoplay\" value=\"true\" />\r\n<param name=\"controller\" value=\"true\" />\r\n<param name=\"target\" value=\"myself\" />\r\n<param name=\"type\" value=\"image/x-targa\" />\r\n<embed src=\"[[MEDIUM_URL]]\" width=[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" type=\"image/x-targa\" pluginspage=\"http://www.apple.com/quicktime/download/\" autoplay=\"true\" controller=\"true\" target=\"myself\" />\r\n</object>', 'targa, tga'),
                    (87, 'TIFF-Bild (QuickTime Plug-in)', '<object classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"autoplay\" value=\"true\" />\r\n<param name=\"controller\" value=\"true\" />\r\n<param name=\"target\" value=\"myself\" />\r\n<param name=\"type\" value=\"image/x-tiff\" />\r\n<embed src=\"[[MEDIUM_URL]]\" width=[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" type=\"image/x-tiff\" pluginspage=\"http://www.apple.com/quicktime/download/\" autoplay=\"true\" controller=\"true\" target=\"myself\" />\r\n</object>', 'tif, tiff'),
                    (88, 'Photoshop-Bild (QuickTime Plug-in)', '<object classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"autoplay\" value=\"true\" />\r\n<param name=\"controller\" value=\"true\" />\r\n<param name=\"target\" value=\"myself\" />\r\n<param name=\"type\" value=\"image/x-photoshop\" />\r\n<embed src=\"[[MEDIUM_URL]]\" width=[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" type=\"image/x-photoshop\" pluginspage=\"http://www.apple.com/quicktime/download/\" autoplay=\"true\" controller=\"true\" target=\"myself\" />\r\n</object>', 'psd'),
                    (89, 'JPEG2000 image (QuickTime Plug-in)', '<object classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"autoplay\" value=\"true\" />\r\n<param name=\"controller\" value=\"true\" />\r\n<param name=\"target\" value=\"myself\" />\r\n<param name=\"type\" value=\"image/jp2\" />\r\n<embed src=\"[[MEDIUM_URL]]\" width=[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" type=\"image/jp2\" pluginspage=\"http://www.apple.com/quicktime/download/\" autoplay=\"true\" controller=\"true\" target=\"myself\" />\r\n</object>', 'jp2'),
                    (90, 'SMIL 1.0 (QuickTime Plug-in)', '<object classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"autoplay\" value=\"true\" />\r\n<param name=\"controller\" value=\"true\" />\r\n<param name=\"target\" value=\"myself\" />\r\n<param name=\"type\" value=\"application/smil\" />\r\n<embed src=\"[[MEDIUM_URL]]\" width=[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" type=\"application/smil\" pluginspage=\"http://www.apple.com/quicktime/download/\" autoplay=\"true\" controller=\"true\" target=\"myself\" />\r\n</object>', 'smi, sml, smil'),
                    (91, 'Flash-Medien (QuckTime Plug-in)', '<object classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"autoplay\" value=\"true\" />\r\n<param name=\"controller\" value=\"true\" />\r\n<param name=\"target\" value=\"myself\" />\r\n<param name=\"type\" value=\"application/x-shockwave-flash\" />\r\n<embed src=\"[[MEDIUM_URL]]\" width=[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" type=\"application/x-shockwave-flash\" pluginspage=\"http://www.apple.com/quicktime/download/\" autoplay=\"true\" controller=\"true\" target=\"myself\" />\r\n</object>', 'swf'),
                    (92, 'QuickTime HTML (QHTML) (QuickTime Plug-in)', '<object classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"autoplay\" value=\"true\" />\r\n<param name=\"controller\" value=\"true\" />\r\n<param name=\"target\" value=\"myself\" />\r\n<param name=\"type\" value=\"text/x-html-insertion\" />\r\n<embed src=\"[[MEDIUM_URL]]\" width=[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" type=\"text/x-html-insertion\" pluginspage=\"http://www.apple.com/quicktime/download/\" autoplay=\"true\" controller=\"true\" target=\"myself\" />\r\n</object>', 'qht, qhtm'),
                    (93, 'MP3-Audio (RealPlayer Player)', '<object id=\"videoplayer1\" classid=\"clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"controls\" value=\"all\" />\r\n<param name=\"autostart\" value=\"true\" />\r\n<param name=\"type\" value=\"audio/x-mpeg\" />\r\n<embed src=\"[[MEDIUM_URL]]\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" autostart=\"true\" type=\"audio/x-mpeg\" console=\"video1\" controls=\"All\" nojava=\"true\"></embed>\r\n</object>', 'mp3'),
                    (94, 'MP3-Wiedergabeliste (RealPlayer Plug-in)', '<object id=\"videoplayer1\" classid=\"clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"controls\" value=\"all\" />\r\n<param name=\"autostart\" value=\"true\" />\r\n<param name=\"type\" value=\"audio/x-mpegurl\" />\r\n<embed src=\"[[MEDIUM_URL]]\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" autostart=\"true\" type=\"audio/x-mpegurl\" console=\"video1\" controls=\"All\" nojava=\"true\"></embed>\r\n</object>', 'm3u, m3url'),
                    (95, 'WAVE-Audio (RealPlayer Plug-in)', '<object id=\"videoplayer1\" classid=\"clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\">\r\n<param name=\"src\" value=\"[[MEDIUM_URL]]\" />\r\n<param name=\"controls\" value=\"all\" />\r\n<param name=\"autostart\" value=\"true\" />\r\n<param name=\"type\" value=\"audio/x-wav\" />\r\n<embed src=\"[[MEDIUM_URL]]\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\" autostart=\"true\" type=\"audio/x-wav\" console=\"video1\" controls=\"All\" nojava=\"true\"></embed>\r\n</object>', 'wav'),
                    (100, 'Flash-Video (Flash Video File)', '<object\r\n  type=\"application/x-shockwave-flash\"\r\n  data=\"[[ASCMS_PATH_OFFSET]]/modules/podcast/lib/FlowPlayer.swf\" \r\n	width=\"[[MEDIUM_WIDTH]]\"\r\n  height=\"[[MEDIUM_HEIGHT]]\"\r\n  id=\"FlowPlayer\">\r\n    <param name=\"movie\" value=\"[[ASCMS_PATH_OFFSET]]/modules/podcast/lib/FlowPlayer.swf\" />\r\n    <param name=\"quality\" value=\"high\" />\r\n    <param name=\"scale\" value=\"noScale\" />\r\n    <param name=\"allowfullscreen\" value=\"true\" />\r\n    <param name=\"allowScriptAccess\" value=\"always\" />\r\n    <param name=\"allownetworking\" value=\"all\" />\r\n    <param name=\"flashvars\" value=\"config={\r\n      autoPlay:true,\r\n      loop: false,\r\n      autoRewind: true,\r\n      videoFile: ''[[MEDIUM_URL]]'',\r\n      fullScreenScriptURL:''[[ASCMS_PATH_OFFSET]]/modules/podcast/lib/fullscreen.js'',\r\n      initialScale:''orig''}\" />\r\n</object>', 'flv'),
                    (101, 'YouTube Video', '<object width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\"><param name=\"movie\" value=\"[[MEDIUM_URL]]\"></param><param name=\"wmode\" value=\"transparent\"></param><embed src=\"[[MEDIUM_URL]]\" type=\"application/x-shockwave-flash\" wmode=\"transparent\" width=\"[[MEDIUM_WIDTH]]\" height=\"[[MEDIUM_HEIGHT]]\"></embed></object>', 'swf')
            ON DUPLICATE KEY UPDATE `id` = `id`
        ");

    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }
}
