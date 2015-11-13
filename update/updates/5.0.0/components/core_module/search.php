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


function _searchUpdate() {

    /*********************************************************
     * EXTENSION:   Change name of HTML_Template_Sigma block *
     * ADDED:       Contrexx v3.0.0                          *
     *********************************************************/
    try {
        \Cx\Lib\UpdateUtil::migrateContentPage('search', null, 'searchrow', 'search_result', '3.0.0');
    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }

}
