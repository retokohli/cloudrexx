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

function _marketUpdate()
{
    global $objDatabase, $_ARRAYLANG;

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

    return true;
}
