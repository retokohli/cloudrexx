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
        $folder_path = \Cx\Core\Core\Controller\Cx::instanciate()->getWebsiteTempPath() . '/Update';
        if (!file_exists($folder_path)) {
            \Cx\Lib\FileSystem\FileSystem::make_folder($folder_path);
            \Cx\Lib\FileSystem\FileSystem::copy_file(\Env::get('cx')->getCodeBaseCoreModulePath() . '/Update/Data/PendingDbUpdates.yml', $folder_path . '/PendingDbUpdates.yml');
        }
        parent::__construct($folder_path . '/PendingDbUpdates.yml');
    }

}
