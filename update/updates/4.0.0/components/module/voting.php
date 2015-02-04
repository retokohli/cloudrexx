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

function _votingUpdate()
{
    try{
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX . 'voting_system',
            array(
                'id'               => array('type' =>    'INT',                 'notnull' => true, 'primary'     => true,   'auto_increment' => true),
                'date'             => array('type' =>    'TIMESTAMP',           'notnull' => true, 'default_expr'=> 'CURRENT_TIMESTAMP'),
                'title'            => array('type' =>    'VARCHAR(60)',         'notnull' => true, 'default'     => '',     'renamefrom' => 'name'),
                'question'         => array('type' =>    'TEXT',                'notnull' => false),
                'status'           => array('type' =>    'TINYINT(1)',          'notnull' => false,'default'     => 1),
                'votes'            => array('type' =>    'INT(11)',             'notnull' => false,'default'     => 0),
                'submit_check'     => array('type' => "ENUM('cookie','email')", 'notnull' => true, 'default'    => 'cookie'),
                'additional_nickname' => array('type' => 'TINYINT(1)',          'notnull' => true, 'default'     => 0),
                'additional_forename' => array('type' => 'TINYINT(1)',          'notnull' => true, 'default'     => 0),
                'additional_surname'  => array('type' => 'TINYINT(1)',          'notnull' => true, 'default'     => 0),
                'additional_phone'    => array('type' => 'TINYINT(1)',          'notnull' => true, 'default'     => 0),
                'additional_street'   => array('type' => 'TINYINT(1)',          'notnull' => true, 'default'     => 0),
                'additional_zip'      => array('type' => 'TINYINT(1)',          'notnull' => true, 'default'     => 0),
                'additional_email'    => array('type' => 'TINYINT(1)',          'notnull' => true, 'default'     => 0),
                'additional_city'     => array('type' => 'TINYINT(1)',          'notnull' => true, 'default'     => 0),
                'additional_comment'  => array('type' => 'TINYINT(1)',          'notnull' => true, 'default'     => 0),
            )
        );
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'voting_additionaldata',
            array(
                'id'                 => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'nickname'           => array('type' => 'VARCHAR(80)', 'notnull' => true, 'default' => '', 'renamefrom' => 'name'),
                'surname'            => array('type' => 'VARCHAR(80)', 'notnull' => true, 'default' => ''),
                'phone'              => array('type' => 'VARCHAR(80)', 'notnull' => true, 'default' => ''),
                'street'             => array('type' => 'VARCHAR(80)', 'notnull' => true, 'default' => ''),
                'zip'                => array('type' => 'VARCHAR(30)', 'notnull' => true, 'default' => ''),
                'city'               => array('type' => 'VARCHAR(80)', 'notnull' => true, 'default' => ''),
                'email'              => array('type' => 'VARCHAR(80)', 'notnull' => true, 'default' => ''),
                'comment'            => array('type' => 'TEXT', 'after' => 'email'),
                'voting_system_id'   => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0', 'renamefrom' => 'voting_sytem_id'),
                'date_entered'       => array('type' => 'TIMESTAMP', 'notnull' => true, 'default_expr'=> 'CURRENT_TIMESTAMP', 'on_update' => 'CURRENT_TIMESTAMP'),
                'forename'           => array('type' => 'VARCHAR(80)', 'notnull' => true, 'default' => '')
            ),
            array(
                'voting_system_id'   => array('fields' => array('voting_system_id'))
            )
        );
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'voting_email',
            array(
                'id'     => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'email'  => array('type' => 'VARCHAR(255)'),
                'valid'  => array('type' => 'ENUM(\'0\',\'1\')', 'notnull' => true, 'default' => '0')
            ),
            array(
                'email'  => array('fields' => array('email'), 'type' => 'UNIQUE')
            )
        );
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'voting_rel_email_system',
            array(
                'email_id'   => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'system_id'  => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'voting_id'  => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'valid'      => array('type' => 'ENUM(\'0\',\'1\')', 'notnull' => true, 'default' => '0')
            ),
            array(
                'email_id'   => array('fields' => array('email_id','system_id'), 'type' => 'UNIQUE')
            )
        );
    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }

    return true;
}
