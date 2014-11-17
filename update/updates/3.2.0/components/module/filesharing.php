<?php

/**
 * Contrexx
 *
 * @link      http://www.contrexx.com
 * @copyright Comvation AG 2007-2014
 * @version   Contrexx 4.0
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
 * "Contrexx" is a registered trademark of Comvation AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

function _filesharingUpdate()
{
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
        \Cx\Lib\UpdateUtil::sql('
            INSERT INTO `'.DBPREFIX.'module_filesharing_mail_template` (`id`, `lang_id`, `subject`, `content`)
            VALUES  (1, 1, "Jemand teilt eine Datei mit Ihnen", "Guten Tag,\r\n\r\nJemand hat auf [[DOMAIN]] eine Datei mit Ihnen geteilt.\r\n\r\n<!-- BEGIN filesharing_file -->\r\nDownload-Link: [[FILE_DOWNLOAD]]\r\n<!-- END filesharing_file -->\r\n\r\nDie Person hat eine Nachricht hinterlassen:\r\n[[MESSAGE]]\r\n\r\nFreundliche Grüsse"),
                    (2, 2, "Somebody is sharing a file with you", "Hi,\r\n\r\nSomebody shared a file with you on [[DOMAIN]].\r\n\r\n<!-- BEGIN filesharing_file -->\r\nDownload link: [[FILE_DOWNLOAD]]\r\n<!-- END filesharing_file -->\r\n\r\nThe person has left a message for you:\r\n[[MESSAGE]]\r\n\r\nBest regards")
            ON DUPLICATE KEY UPDATE `id` = `id`
        ');

        \Cx\Lib\UpdateUtil::sql('INSERT IGNORE INTO `'.DBPREFIX.'core_setting` (`section`, `name`, `group`, `type`, `value`, `values`, `ord`)
                                    VALUES (\'filesharing\',\'permission\',\'config\',\'text\',\'off\',\'\',0)');

    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }
}
