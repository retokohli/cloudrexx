<?php
/**
 * YamlSetting Repository
 *
 * Repository to manage the YamlSetting entities.
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_setting
 */

namespace Cx\Core\Setting\Model\Repository;

/**
 * YamlSetting Repository
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_setting
 */
class YamlSettingRepositoryException extends \Exception {};

/**
 * YamlSetting Repository
 *
 * Repository to manage the YamlSetting entities.
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_setting
 */
class YamlSettingRepository extends \Cx\Core\Model\Controller\YamlRepository {
    
    public function __construct($repositoryPath) {
        parent::__construct($repositoryPath);
    }
    
    protected function load() {
        if (!parent::load()) {
            $this->entityIdentifier = 'id';
            $this->entityUniqueKeys = array('name');
        }
    }
}

