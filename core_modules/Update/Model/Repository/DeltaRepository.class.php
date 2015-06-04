<?php

/**
 * DeltaRepository
 *
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_update
 */

namespace Cx\Core_Modules\Update\Model\Repository;

/**
 * DeltaRepository
 *
 * @copyright   Comvation AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_update
 */
class DeltaRepository extends \Cx\Core\Model\Controller\YamlRepository {

    public function __construct() {
        $folderPath = \Cx\Core\Core\Controller\Cx::instanciate()->getWebsiteTempPath() . '/Update';
        if (!file_exists($folderPath)) {
            \Cx\Lib\FileSystem\FileSystem::make_folder($folderPath);
            \Cx\Lib\FileSystem\FileSystem::copy_file(\Env::get('cx')->getCodeBaseCoreModulePath() . '/Update/Data/PendingDbUpdates.yml', $folderPath . '/PendingDbUpdates.yml');
        }
        parent::__construct($folderPath . '/PendingDbUpdates.yml');
    }

}
