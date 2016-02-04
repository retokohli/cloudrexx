<?php

/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2015
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Cloudrexx" is a registered trademark of Cloudrexx AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */


function _filesharingUpdate()
{
    global $objUpdate, $_CONFIG, $_ARRAYLANG;

    if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '3.0.0')) {
        try {
            /*********************************
             * EXTENSION:   Initial creation *
             * ADDED:       Contrexx v3.0.0  *
             *********************************/
            \Cx\Lib\UpdateUtil::table(
                DBPREFIX.'module_filesharing',
                array(
                    'id'                 => array('type' => 'INT(10)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                    'file'               => array('type' => 'VARCHAR(250)', 'notnull' => true, 'after' => 'id'),
                    'source'             => array('type' => 'VARCHAR(250)', 'notnull' => true, 'after' => 'file'),
                    'cmd'                => array('type' => 'VARCHAR(50)', 'notnull' => true, 'after' => 'source'),
                    'hash'               => array('type' => 'VARCHAR(50)', 'notnull' => true, 'after' => 'cmd'),
                    'check'              => array('type' => 'VARCHAR(50)', 'notnull' => true, 'after' => 'hash'),
                    'expiration_date'    => array('type' => 'TIMESTAMP', 'notnull' => false, 'default' => NULL, 'after' => 'check'),
                    'upload_id'          => array('type' => 'INT(10)', 'notnull' => false, 'default' => NULL, 'after' => 'expiration_date')
                )
            );

            \Cx\Lib\UpdateUtil::table(
                DBPREFIX.'module_filesharing_mail_template',
                array(
                    'id'         => array('type' => 'INT(10)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                    'lang_id'    => array('type' => 'INT(1)', 'notnull' => true, 'after' => 'id'),
                    'subject'    => array('type' => 'VARCHAR(250)', 'notnull' => true, 'after' => 'lang_id'),
                    'content'    => array('type' => 'TEXT', 'notnull' => true, 'after' => 'subject')
                )
            );
        } catch (\Cx\Lib\UpdateException $e) {
            return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
        }
    }


    if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '3.0.1')) {
        try {
            \Cx\Lib\UpdateUtil::sql('
                INSERT INTO `'.DBPREFIX.'module_filesharing_mail_template` (`id`, `lang_id`, `subject`, `content`)
                VALUES  (1, 1, "Jemand teilt eine Datei mit Ihnen", "Guten Tag,\r\n\r\nJemand hat auf [[DOMAIN]] eine Datei mit Ihnen geteilt.\r\n\r\n<!-- BEGIN filesharing_file -->\r\nDownload-Link: [[FILE_DOWNLOAD]]\r\n<!-- END filesharing_file -->\r\n\r\nDie Person hat eine Nachricht hinterlassen:\r\n[[MESSAGE]]\r\n\r\nFreundliche Grüsse"),
                        (2, 2, "Somebody is sharing a file with you", "Hi,\r\n\r\nSomebody shared a file with you on [[DOMAIN]].\r\n\r\n<!-- BEGIN filesharing_file -->\r\nDownload link: [[FILE_DOWNLOAD]]\r\n<!-- END filesharing_file -->\r\n\r\nThe person has left a message for you:\r\n[[MESSAGE]]\r\n\r\nBest regards")
                ON DUPLICATE KEY UPDATE `id` = `id`
            ');
        } catch (\Cx\Lib\UpdateException $e) {
            return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
        }
    }


    if (in_array(detectCx3Version(), array('rc1', 'rc2'))) {
        try {
            \Cx\Lib\UpdateUtil::sql('UPDATE `'.DBPREFIX.'core_setting` SET `value` = "off" WHERE (`section` = "filesharing" AND `name` = "permission" AND `group` = "config")');
        } catch (\Cx\Lib\UpdateException $e) {
            return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
        }
    }


    if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '3.1.2')) {
        try {
            \Cx\Lib\UpdateUtil::sql('INSERT IGNORE INTO `'.DBPREFIX.'core_setting` (`section`, `name`, `group`, `type`, `value`, `values`, `ord`)
                                        VALUES (\'filesharing\',\'permission\',\'config\',\'text\',\'off\',\'\',0)');
        } catch (\Cx\Lib\UpdateException $e) {
            return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
        }
    }


    // update filesharing page, add confirm deletion view
    if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '3.1.0')) {
        try {
            \DBG::msg('update3: migrate filesharing page');
            $search = array(
                '/.*/ms',
            );
            $callback = function($matches) {
                $newHtmlCode = <<<HTMLCODE
    <!-- BEGIN confirm_delete -->
    <form action="[[FORM_ACTION]]" class="fileshareForm" id="contactForm" method="[[FORM_METHOD]]" style="float: left;">
        <p>
            <label>[[TXT_FILESHARING_FILE_NAME]]</label>[[FILESHARING_FILE_NAME]]
        </p>
        <p>
            <input name="delete" type="submit" value="[[TXT_FILESHARING_CONFIRM_DELETE]]" />
        </p>
    </form>
    <!-- END confirm_delete -->
HTMLCODE;
                if (!preg_match('/<!--\s+BEGIN\s+confirm_delete\s+-->.*<!--\s+END\s+confirm_delete\s+-->/ms', $matches[0])) {
                    return str_replace('<!-- END upload_form -->', $newHtmlCode, $matches[0]);
                } else {
                    return $matches[0];
                }
            };
            \Cx\Lib\UpdateUtil::migrateContentPageUsingRegexCallback(array('module' => 'filesharing', 'cmd' => ''), $search, $callback, array('content'), '3.1.0');
        } catch (\Cx\Lib\UpdateException $e) {
            return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
        }
    }


    if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '5.0.0')) {
        try {
            //update section
            \Cx\Lib\UpdateUtil::sql("UPDATE `" . DBPREFIX . "core_setting` SET `section` = 'FileSharing' WHERE `section` = 'filesharing' AND `name` = 'permission' AND `group` = 'config'");

            // update path
            $mediaPath       = ASCMS_DOCUMENT_ROOT . '/media';
            $sourceMediaPath = $mediaPath . '/filesharing';
            $targetMediaPath = $mediaPath . '/FileSharing';
            try {
                \Cx\Lib\UpdateUtil::migrateOldDirectory($sourceMediaPath, $targetMediaPath);
            } catch (\Exception $e) {
                \DBG::log($e->getMessage());
                setUpdateMsg(sprintf(
                    $_ARRAYLANG['TXT_UNABLE_TO_MOVE_DIRECTORY'],
                    $sourceMediaPath, $targetMediaPath
                ));
                return false;
            }
        } catch (\Cx\Lib\UpdateException $e) {
            return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
        }
    }

    return true;
}
