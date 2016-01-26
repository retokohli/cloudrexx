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

/**
 * Update the component table and fill it with the new values
 * @return bool true on success false otherwise
 */
function _updateComponent() {
    $components = getModules();
    // Ids of modules which shall not be in the component-table
    $componentsToSkip = array(
        0, // system
        1, // core
        37, // immo
        64, // language
        65, // fulllanguage
        67, // logout
        96, // CacheManager
        97, // LicenseManager
    );
    try {
        // Drop existing values
        Cx\Lib\UpdateUtil::sql('TRUNCATE TABLE `' . DBPREFIX . 'component`');

        foreach ($components as $component) {
            if (in_array($component['id'], $componentsToSkip)) {
                continue;
            }

            $componentType = 'module';
            if ($component['is_required'] === 1
                && $component['is_core'] === 1
            ) {
                $componentType = 'core';
            } else if ($component['is_required'] === 0
                && $component['is_core'] === 1
            ) {
                $componentType = 'core_module';
            }

            // add new ones
            Cx\Lib\UpdateUtil::sql("INSERT INTO ".DBPREFIX."component ( `id` , `name` , `type`) VALUES ( ".$component['id']." , '".$component['name']."', '". $componentType ."') ON DUPLICATE KEY UPDATE `id` = `id`");
        }
    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }

    return true;
}
