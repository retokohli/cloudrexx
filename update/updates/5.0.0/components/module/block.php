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


function _blockUpdate()
{
    global $_ARRAYLANG, $objUpdate, $_CONFIG;

    if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '5.0.0')) {
        try{
            \Cx\Lib\UpdateUtil::table(
                DBPREFIX.'module_block_targeting_option',
                array(
                    'block_id'       => array('type' => 'INT(11)', 'notnull' => true, 'primary' => true),
                    'filter'         => array('type' => 'ENUM(\'include\',\'exclude\')', 'notnull' => true, 'default' => 'include'),
                    'type'           => array('type' => 'ENUM(\'country\')', 'notnull' => true, 'default' => 'country', 'primary' => true),
                    'value'          => array('type' => 'text', 'notnull' => true)
                ),
                array(),
                'InnoDb'
            );
        } catch (\Cx\Lib\UpdateException $e) {
            return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
        }

        // migrate path to images and media
        $pathsToMigrate = \Cx\Lib\UpdateUtil::getMigrationPaths();
        try {
            foreach ($pathsToMigrate as $oldPath => $newPath) {
                \Cx\Lib\UpdateUtil::migratePath(
                    '`' . DBPREFIX . 'module_block_rel_lang_content`',
                    '`content`',
                    $oldPath,
                    $newPath
                );
            }
        } catch (\Cx\Lib\Update_DatabaseException $e) {
            \DBG::log($e->getMessage());
            setUpdateMsg(sprintf(
                $_ARRAYLANG['TXT_UNABLE_TO_MIGRATE_MEDIA_PATH'],
                'Inhaltscontainer (Block)'
            ));
            return false;
        }
    }

    return true;
}

