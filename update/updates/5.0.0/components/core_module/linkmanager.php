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

function _linkmanagerUpdate()
{
    global $objUpdate, $_CONFIG;

    if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '5.0.0')) {
        try {
            \Cx\Lib\UpdateUtil::table(
                DBPREFIX.'core_module_linkmanager_crawler',
                array(
                    'id'               => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                    'lang'             => array('type' => 'TINYINT(2)', 'notnull' => true, 'after' => 'id'),
                    'startTime'        => array('type' => 'TIMESTAMP', 'notnull' => true, 'default' => '0000-00-00 00:00:00', 'after' => 'lang'),
                    'endTime'          => array('type' => 'TIMESTAMP', 'notnull' => true, 'default' => '0000-00-00 00:00:00', 'after' => 'startTime'),
                    'totalLinks'       => array('type' => 'INT(11)', 'notnull' => true, 'after' => 'endTime'),
                    'totalBrokenLinks' => array('type' => 'INT(11)', 'notnull' => true, 'after' => 'totalLinks'),
                    'runStatus'        => array('type' => 'ENUM(\'running\',\'incomplete\', \'completed\')', 'notnull' => true, 'after' => 'totalBrokenLinks')
                ),
                array(),
                'InnoDB'
            );
            \Cx\Lib\UpdateUtil::table(
                DBPREFIX.'core_module_linkmanager_history',
                array(
                    'id'                => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                    'lang'              => array('type' => 'TINYINT(2)', 'notnull' => true, 'after' => 'id'),
                    'requestedPath'     => array('type' => 'TEXT', 'notnull' => true, 'after' => 'lang'),
                    'linkStatusCode'    => array('type' => 'INT(1)', 'notnull' => false, 'default' => null, 'after' => 'requestedPath'),
                    'entryTitle'        => array('type' => 'VARCHAR(255)', 'notnull' => true, 'after' => 'linkStatusCode'),
                    'moduleName'        => array('type' => 'VARCHAR(100)', 'notnull' => false, 'default' => null, 'after' => 'entryTitle'),
                    'moduleAction'      => array('type' => 'VARCHAR(100)', 'notnull' => false, 'default' => null, 'after' => 'moduleName'),
                    'moduleParams'      => array('type' => 'VARCHAR(255)', 'notnull' => false, 'default' => null, 'after' => 'moduleAction'),
                    'detectedTime'      => array('type' => 'TIMESTAMP', 'notnull' => true, 'after' => 'moduleParams'),
                    'flagStatus'        => array('type' => 'TINYINT(2)', 'notnull' => true, 'after' => 'detectedTime'),
                    'updatedBy'         => array('type' => 'INT(2)', 'notnull' => true, 'after' => 'flagStatus'),
                    'requestedLinkType' => array('type' => 'VARCHAR(25)', 'notnull' => false, 'default' => null, 'after' => 'updatedBy'),
                    'refererPath'       => array('type' => 'TEXT', 'notnull' => false, 'after' => 'requestedLinkType'),
                    'leadPath'          => array('type' => 'TEXT', 'notnull' => true, 'after' => 'refererPath'),
                    'linkStatus'        => array('type' => 'TINYINT(2)', 'notnull' => true, 'after' => 'leadPath'),
                    'linkRecheck'       => array('type' => 'TINYINT(2)', 'notnull' => true, 'after' => 'linkStatus'),
                    'brokenLinkText'    => array('type' => 'VARCHAR(255)', 'notnull' => true, 'after' => 'linkRecheck'),
                ),
                array(),
                'InnoDB'
            );
            \Cx\Lib\UpdateUtil::table(
                DBPREFIX.'core_module_linkmanager_link',
                array(
                    'id'                => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                    'lang'              => array('type' => 'TINYINT(2)', 'notnull' => true, 'after' => 'id'),
                    'requestedPath'     => array('type' => 'TEXT', 'notnull' => true, 'after' => 'lang'),
                    'linkStatusCode'    => array('type' => 'INT(1)', 'notnull' => false, 'default' => null, 'after' => 'requestedPath'),
                    'entryTitle'        => array('type' => 'VARCHAR(255)', 'notnull' => true, 'after' => 'linkStatusCode'),
                    'moduleName'        => array('type' => 'VARCHAR(100)', 'notnull' => false, 'default' => null, 'after' => 'entryTitle'),
                    'moduleAction'      => array('type' => 'VARCHAR(100)', 'notnull' => false, 'default' => null, 'after' => 'moduleName'),
                    'moduleParams'      => array('type' => 'VARCHAR(255)', 'notnull' => false, 'default' => null, 'after' => 'moduleAction'),
                    'detectedTime'      => array('type' => 'TIMESTAMP', 'notnull' => true, 'after' => 'moduleParams'),
                    'flagStatus'        => array('type' => 'TINYINT(2)', 'notnull' => true, 'after' => 'detectedTime'),
                    'updatedBy'         => array('type' => 'INT(2)', 'notnull' => true, 'after' => 'flagStatus'),
                    'requestedLinkType' => array('type' => 'VARCHAR(25)', 'notnull' => false, 'default' => null, 'after' => 'updatedBy'),
                    'refererPath'       => array('type' => 'TEXT', 'notnull' => false, 'after' => 'requestedLinkType'),
                    'leadPath'          => array('type' => 'TEXT', 'notnull' => false, 'after' => 'refererPath'),
                    'linkStatus'        => array('type' => 'TINYINT(2)', 'notnull' => true, 'after' => 'leadPath'),
                    'linkRecheck'       => array('type' => 'TINYINT(2)', 'notnull' => true, 'after' => 'linkStatus'),
                    'brokenLinkText'    => array('type' => 'VARCHAR(255)', 'notnull' => true, 'after' => 'linkRecheck'),
                ),
                array(),
                'InnoDB'
            );
            
        } catch (\Cx\Lib\UpdateException $e) {
            return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
        }
    }

    return true;
}
