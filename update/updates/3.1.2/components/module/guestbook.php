<?php
function _guestbookUpdate()
{
    global $objDatabase, $_ARRAYLANG;

    $arrGuestbookColumns = $objDatabase->MetaColumns(DBPREFIX.'module_guestbook');
    if ($arrGuestbookColumns === false) {
        setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_guestbook'));
        return false;
    }

    if (isset($arrGuestbookColumns['NICKNAME']) and !isset($arrGuestbookColumns['NAME'])) {
        $query = "ALTER TABLE ".DBPREFIX."module_guestbook
                  CHANGE `nickname` `name` varchar(255) NOT NULL default ''";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    if (!isset($arrGuestbookColumns['FORENAME'])) {
        $query = "ALTER TABLE ".DBPREFIX."module_guestbook
                  ADD `forename` varchar(255) NOT NULL default '' AFTER `name`";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    // this addidional structure update/check is required due that the full version's structure isn't as it should be
    try {
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX . 'module_guestbook',
            array(
                'id'        => array('type' => 'INT(6)', 'unsigned' => true, 'auto_increment' => true, 'primary' => true),
                'status'    => array('type' => 'TINYINT(1)', 'unsigned' => true, 'default' => 0),
                'name'      => array('type' => 'VARCHAR(255)'),
                'forename'  => array('type' => 'VARCHAR(255)'),
                'gender'    => array('type' => 'CHAR(1)', 'notnull' => true, 'default' => ''),
                'url'       => array('type' => 'TINYTEXT'),
                'email'     => array('type' => 'TINYTEXT'),
                'comment'   => array('type' => 'TEXT'),
                'ip'        => array('type' => 'VARCHAR(15)'),
                'location'  => array('type' => 'TINYTEXT'),
                'lang_id'   => array('type' => 'TINYINT(2)', 'default' => '1'),
                'datetime'  => array('type' => 'DATETIME', 'default' => '0000-00-00 00:00:00')            ),
            array(
                'comment'   => array('fields' => array('comment'), 'type' => 'FULLTEXT')
            )
        );
    }
    catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }


    /********************************
     * EXTENSION:   Timezone        *
     * ADDED:       Contrexx v3.0.0 *
     ********************************/
    try {
        \Cx\Lib\UpdateUtil::sql('ALTER TABLE `'.DBPREFIX.'module_guestbook` CHANGE `datetime` `datetime` TIMESTAMP NOT NULL DEFAULT "0000-00-00 00:00:00"');
    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }


    /********************************
     * EXTENSION:   Captcha         *
     * ADDED:       Contrexx v3.0.0 *
     ********************************/
    try {
        // switch to source mode for guestbook content page
        \Cx\Lib\UpdateUtil::setSourceModeOnContentPage(array('module' => 'guestbook', 'cmd' => 'post'), '3.0.1');

        // migrate content page to version 3.0.1
        $search = array(
        '/(.*)/ms',
        );
        $callback = function($matches) {
            $content = $matches[1];
            if (empty($content)) {
                return $content;
            }

            $content = str_replace(array('nickname', 'NICKNAME'), array('name', 'NAME'), $content);

            if (!preg_match('/<!--\s+BEGIN\s+guestbookForm\s+-->.*<!--\s+END\s+guestbookForm\s+-->/ms', $content)) {
                $content = '<!-- BEGIN guestbookForm -->'.$content.'<!-- END guestbookForm -->';
            }
            if (!preg_match('/<!--\s+BEGIN\s+guestbookStatus\s+-->.*<!--\s+END\s+guestbookStatus\s+-->/ms', $content)) {
                $content .= <<<STATUS_HTML
<!-- BEGIN guestbookStatus -->
{GUESTBOOK_STATUS}<br /><br />
<a href="index.php?section=guestbook">Zurück zum Gästebuch</a>
<!-- END guestbookStatus -->
STATUS_HTML;
            }

            if (!preg_match('/<!--\s+BEGIN\s+guestbook_captcha\s+-->.*<!--\s+END\s+guestbook_captcha\s+-->/ms', $content)) {
                // migrate captcha stuff
                $newCaptchaCode = <<<CAPTCHA_HTML
<!-- BEGIN guestbook_captcha -->
<p><label for="coreCaptchaCode">{TXT_GUESTBOOK_CAPTCHA}</label>{GUESTBOOK_CAPTCHA_CODE}</p>
<!-- END guestbook_captcha -->
CAPTCHA_HTML;
                $content = preg_replace('/<[^>]+\{IMAGE_URL\}.*\{CAPTCHA_OFFSET\}[^>]+>/ms', $newCaptchaCode, $content);
            }

            // this adds the missing placeholders [[FEMALE_CHECKED]], [[MALE_CHECKED]]
            $pattern = '/(<input[^>]+name=[\'"]malefemale[\'"])([^>]*>)/ms';
            if (preg_match_all($pattern, $content, $match)) {
                foreach ($match[0] as $idx => $input) {
                    // check if "checked"-placeholder is missing inputfield
                    if (!preg_match('/\{(FE)?MALE_CHECKED\}/ms', $input)) {
                        if (preg_match('/value\s*=\s*[\'"]F[\'"]/', $input)) {
                            $content = str_replace($input, $match[1][$idx].' {FEMALE_CHECKED} '.$match[2][$idx], $content);
                        } elseif (preg_match('/value\s*=\s*[\'"]M[\'"]/', $input)) {
                            $content = str_replace($input, $match[1][$idx].' {MALE_CHECKED} '.$match[2][$idx], $content);
                        }
                    }
                }
            }

            return $content;
        };
        \Cx\Lib\UpdateUtil::migrateContentPageUsingRegexCallback(array('module' => 'guestbook', 'cmd' => 'post'), $search, $callback, array('content'), '3.0.1');
    }
    catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }

    return true;
}
