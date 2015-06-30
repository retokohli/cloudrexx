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
