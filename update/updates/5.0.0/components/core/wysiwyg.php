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

function _wysiwygUpdate()
{
    global $objUpdate, $_CONFIG;

    if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '5.0.0')) {
        try {
            \Cx\Lib\UpdateUtil::table(
                DBPREFIX.'core_wysiwyg_template',
                array(
                    'id'          => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                    'title'       => array('type' => 'VARCHAR(255)', 'notnull' => true, 'after' => 'id'),
                    'description' => array('type' => 'TEXT', 'notnull' => true, 'after' => 'title'),
                    'imagePath'   => array('type' => 'VARCHAR(255)', 'notnull' => true, 'after' => 'description'),
                    'htmlContent' => array('type' => 'TEXT', 'notnull' => false, 'after' => 'imagePath'),
                    'active'      => array('type' => 'TINYINT(4)', 'notnull' => true, 'default' => '1', 'after' => 'htmlContent'),
                )
            );
            \Cx\Lib\UpdateUtil::sql('INSERT IGNORE INTO `'. DBPREFIX .'core_wysiwyg_template` (`id`, `title`, `description`, `imagePath`, `htmlContent`, `active`) VALUES (1,\'Bild und Titel\',\'Bild mit einem Titel und Text, der das Bild umfliesst.\',\'/images/Wysiwyg/template1.gif\',\'<h3><img src=\" \" alt=\"\" style=\"margin-right: 10px\" height=\"100\" width=\"100\" align=\"left\" />Hier den Titel einfügen</h3><p>Hier den Text einfügen</p>\',1)');
            \Cx\Lib\UpdateUtil::sql('INSERT IGNORE INTO `'. DBPREFIX .'core_wysiwyg_template` (`id`, `title`, `description`, `imagePath`, `htmlContent`, `active`) VALUES (2,\'Zwei Spalten mit Titel\',\'Zwei Spalten, die beide einen Titel und Text beinhalten.\',\'/images/Wysiwyg/template2.gif\',\'<div class=\"row\"><div class=\"col-md-6\"><h2>Hier den Titel einfügen</h2>Hier den Text einfügen <br/> </div><div class=\"col-md-6\"><h2 >Title</h2>Hier den Text einfügen <br/></div></div>Text goes here\',1)');
            \Cx\Lib\UpdateUtil::sql('INSERT IGNORE INTO `'. DBPREFIX .'core_wysiwyg_template` (`id`, `title`, `description`, `imagePath`, `htmlContent`, `active`) VALUES (3,\'Text und Tabelle\',\'Ein Titel mit Text und einer Tabelle.\',\'/images/Wysiwyg/template3.gif\',\'<div style=\"width: 80%\"><h3>Hier den Titel einfügen</h3><table style=\"width:150px;float: right\" cellspacing=\"0\" cellpadding=\"0\" border=\"1\"><caption style=\"border:solid 1px black\"><strong> Hier den Tabellentitel einfügen</strong></caption><tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr></table><p>Type the text here</p></div>\',1)');
        } catch (\Cx\Lib\UpdateException $e) {
            return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
        }
    }

    return true;
}
