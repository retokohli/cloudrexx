<?php
/**
 * ModelSetting Repository
 *
 * Repository to manage the modelSetting entities.
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_setting
 */

namespace Cx\Core\Setting\Model\Repository;

/**
 * ModelSetting Repository
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_setting
 */
class ModelSettingRepositoryException extends \Exception {};

/**
 * ModelSetting Repository
 *
 * Repository to manage the modelSetting entities.
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_setting
 */
class ModelSettingRepository extends \Cx\Core\Model\Controller\YamlRepository {
    /**
     * Constructor to initialize the YamlRepository with source
     * file config/DomainRepository.yml.
     */
    public function __construct() {}
    
    public function initialize($repositoryPath) {
        parent::__construct($repositoryPath);
    }
}

