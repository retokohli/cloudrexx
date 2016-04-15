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

function _marketUpdate()
{
    global $objDatabase, $objUpdate, $_CONFIG, $_ARRAYLANG;

    if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '3.0.0')) {
        $query = "SELECT id FROM ".DBPREFIX."module_market_settings WHERE name='codeMode'";
        $objCheck = $objDatabase->SelectLimit($query, 1);
        if ($objCheck !== false) {
            if ($objCheck->RecordCount() == 0) {
                $query =   "INSERT INTO `".DBPREFIX."module_market_settings` ( `id` , `name` , `value` , `description` , `type` )
                            VALUES ( NULL , 'codeMode', '1', 'TXT_MARKET_SET_CODE_MODE', '2')";
                if ($objDatabase->Execute($query) === false) {
                    return _databaseError($query, $objDatabase->ErrorMsg());
                }
            }
        } else {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }

        $arrColumns = $objDatabase->MetaColumns(DBPREFIX.'module_market_mail');
        if ($arrColumns === false) {
            setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_market_mail'));
            return false;
        }

        if (!isset($arrColumns['MAILTO'])) {
            $query = "ALTER TABLE `".DBPREFIX."module_market_mail` ADD `mailto` VARCHAR( 10 ) NOT NULL AFTER `content`" ;
            if ($objDatabase->Execute($query) === false) {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }


        /*****************************************************************
        * EXTENSION:    New attributes 'color' and 'sort_id' for entries *
        * ADDED:        Contrexx v2.1.0                                  *
        *****************************************************************/
        $arrColumns = $objDatabase->MetaColumns(DBPREFIX.'module_market');
        if ($arrColumns === false) {
            setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_market'));
            return false;
        }

        if (!isset($arrColumns['SORT_ID'])) {
            $query = "ALTER TABLE `".DBPREFIX."module_market` ADD `sort_id` INT( 4 ) NOT NULL DEFAULT '0' AFTER `paypal`" ;
            if ($objDatabase->Execute($query) === false) {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }

        if (!isset($arrColumns['COLOR'])) {
            $query = "ALTER TABLE `".DBPREFIX."module_market` ADD `color` VARCHAR(50) NOT NULL DEFAULT '' AFTER `description`" ;
            if ($objDatabase->Execute($query) === false) {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }


        try {
            // delete obsolete table  contrexx_module_market_access
            \Cx\Lib\UpdateUtil::drop_table(DBPREFIX.'module_market_access');

            \Cx\Lib\UpdateUtil::table(
                DBPREFIX.'module_market_spez_fields',
                array(
                    'id'         => array('type' => 'INT(5)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                    'name'       => array('type' => 'VARCHAR(100)'),
                    'value'      => array('type' => 'VARCHAR(100)'),
                    'type'       => array('type' => 'INT(1)', 'notnull' => true, 'default' => '1'),
                    'lang_id'    => array('type' => 'INT(2)', 'notnull' => true, 'default' => '0'),
                    'active'     => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0')
                )
            );
        } catch (\Cx\Lib\UpdateException $e) {
            DBG::trace();
            return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
        }
    }

    if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '5.0.0')) {
        \Cx\Lib\UpdateUtil::sql(
            'INSERT IGNORE INTO `contrexx_module_market_spez_fields` (`id`, `name`, `value`, `type`, `lang_id`, `active`)
             VALUES
                 (6, \'spez_field_6\', \'\', 1, 1, 0),
                 (7, \'spez_field_7\', \'\', 1, 1, 0),
                 (8, \'spez_field_8\', \'\', 1, 1, 0),
                 (9, \'spez_field_9\', \'\', 1, 1, 0),
                 (10, \'spez_field_10\', \'\', 1, 1, 0);
        ');
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX . 'module_market`',
            array(
                'id'        => array('type' => 'INT(9)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'name'      => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => ''),
                'email'     => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => ''),
                'type'      => array('type' => 'set(\'search\',\'offer\')', 'notnull' => true, 'default' => ''),
                'title'     => array('type' => 'varchar(255)', 'notnull' => true, 'default' => ''),
                'description'   => array('type' =>  'mediumtext', 'notnull' => true),
                'color'     => array('type' => 'varchar(50)', 'notnull' => true, 'default' => ''),
                'premium'   => array('type' => 'int(1)', 'notnull' => true, 'default' => '0'),
                'picture'   => array('type' => 'varchar(255)', 'notnull' => true, 'default' => ''),
                'catid'     => array('type' => 'int(4)', 'notnull' => true, 'default' => '0'),
                'price'     => array('type' => 'varchar(10)', 'notnull' => true, 'default' => ''),
                'regdate'   => array('type' => 'varchar(20)', 'notnull' => true, 'default' => ''),
                'enddate'   => array('type' => 'varchar(20)', 'notnull' => true, 'default' => ''),
                'userid'    => array('type' => 'int(4)', 'notnull' => true, 'default' => '0'),
                'userdetails'   => array('type' => 'int(1)', 'notnull' => true, 'default' => '0'),
                'status'    => array('type' => 'int(1)', 'notnull' => true, 'default' => '0'),
                'regkey'    => array('type' => 'varchar(50)', 'notnull' => true, 'default' => ''),
                'paypal'    => array('type' => 'int(1)', 'notnull' => true, 'default' => '0'),
                'sort_id'   => array('type' => 'int(4)', 'notnull' => true, 'default' => '0'),
                'spez_field_1'  => array('type' => 'varchar(255)', 'notnull' => true),
                'spez_field_2'  => array('type' => 'varchar(255)', 'notnull' => true),
                'spez_field_3'  => array('type' => 'varchar(255)', 'notnull' => true),
                'spez_field_4'  => array('type' => 'varchar(255)', 'notnull' => true),
                'spez_field_5'  => array('type' => 'varchar(255)', 'notnull' => true),
                'spez_field_6'  => array('type' => 'varchar(255)', 'notnull' => true),
                'spez_field_7'  => array('type' => 'varchar(255)', 'notnull' => true),
                'spez_field_8'  => array('type' => 'varchar(255)', 'notnull' => true),
                'spez_field_9'  => array('type' => 'varchar(255)', 'notnull' => true),
                'spez_field_10' => array('type' => 'varchar(255)', 'notnull' => true),
            ),
            array(
                'description' => array('fields' => array('description'), 'type' => 'FULLTEXT'),
                'title'       => array('fields' => array('description', 'title'), 'type' => 'FULLTEXT'),
            ),
            'MyISAM'
        );

        //Update script for moving the folder
        $mediaPath       = ASCMS_DOCUMENT_ROOT . '/media';
        $sourceMediaPath = $mediaPath . '/market';
        $targetMediaPath = $mediaPath . '/Market';
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

        // migrate path to images and media
        $pathsToMigrate = \Cx\Lib\UpdateUtil::getMigrationPaths();
        $attributes = array(
            'content'          => 'module_market_mail',
        );
        try {
            foreach ($attributes as $attribute => $table) {
                foreach ($pathsToMigrate as $oldPath => $newPath) {
                    \Cx\Lib\UpdateUtil::migratePath(
                        '`' . DBPREFIX . $table . '`',
                        '`' . $attribute . '`',
                        $oldPath,
                        $newPath
                    );
                }
            }
        } catch (\Cx\Lib\Update_DatabaseException $e) {
            \DBG::log($e->getMessage());
            return false;
        }
    }

    return true;
}
