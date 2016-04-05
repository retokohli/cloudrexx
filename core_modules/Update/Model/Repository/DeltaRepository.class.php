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
 * DeltaRepository
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  coremodule_update
 */

namespace Cx\Core_Modules\Update\Model\Repository;

/**
 * DeltaRepository
 *
 * @copyright   Cloudrexx AG
 * @author      CLOUDREXX Development Team <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  coremodule_update
 */
class DeltaRepository extends \Cx\Core\Model\Controller\YamlRepository {

    /**
     * pending database update yml
     */
    const PENDING_DB_UPDATES_YML = 'PendingDbUpdates.yml';

    /**
     * Constructor
     */
    public function __construct() {
        $cx = \Cx\Core\Core\Controller\Cx::instanciate();
        $folderPath = $cx->getWebsiteTempPath() . '/Update';
        if (!file_exists($folderPath)) {
            \Cx\Lib\FileSystem\FileSystem::make_folder($folderPath);
        }
        if (!file_exists($folderPath . '/' . self::PENDING_DB_UPDATES_YML)) {
            \Cx\Lib\FileSystem\FileSystem::copy_file(
                $cx->getCodeBaseCoreModulePath() . '/Update/Data/' . self::PENDING_DB_UPDATES_YML,
                $folderPath . '/' . self::PENDING_DB_UPDATES_YML
            );
        }
        parent::__construct($folderPath . '/'. self::PENDING_DB_UPDATES_YML);
    }

}
