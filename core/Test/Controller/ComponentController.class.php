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
 * Main controller for Test
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_test
 */

namespace Cx\Core\Test\Controller;

/**
 * Main controller for Test
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_test
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {
    public function getControllerClasses() {
        // Return an empty array here to let the component handler know that there
        // does not exist a backend, nor a frontend controller of this component.
        return array();
    }

    /**
     * postInit hook
     *
     * @param \Cx\Core\Core\Controller\Cx $cx
     */
    public function postInit(\Cx\Core\Core\Controller\Cx $cx)
    {
        global $_DBCONFIG;

        $isTestRun = !empty($_GET['runTest']);
        if (!$isTestRun) {
            return;
        }

        $componentName =  !empty($_GET['component'])
                        ? contrexx_input2raw($_GET['component'])
                        : '';
        $dataSet       =  !empty($_GET['dataSet'])
                        ? contrexx_input2raw($_GET['dataSet'])
                        : '';
        if (empty($componentName) || empty($dataSet)) {
            return;
        }

        $dataSetFile = $this->getDataSetFilePath($componentName, $dataSet);
        if (!$dataSetFile) {
            return;
        }
        $operation = new \PHPUnit_Extensions_Database_Operation_Composite(array(
           \PHPUnit_Extensions_Database_Operation_Factory::TRUNCATE(),
           \PHPUnit_Extensions_Database_Operation_Factory::INSERT(),
        ));
        $pdo  = $this->cx->getDb()->getPdoConnection();
        $pdo->beginTransaction();
        register_shutdown_function(array($this, 'rollbackDataSet'));
        $conn = new \PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection(
            $pdo,
            $_DBCONFIG['database']
        );
        $ymlDataSet = new \PHPUnit_Extensions_Database_DataSet_YamlDataSet($dataSetFile);
        $operation->execute($conn, $ymlDataSet);
    }

    /**
     * Rollback the transaction created during dataset import
     * @see self::postInit()
     */
    public function rollbackDataSet()
    {
        $this->cx->getDb()->getPdoConnection()->rollBack();
    }

    /**
     * Get the data set file path by given component name and dataset file
     *
     * @param string $componentName Component Name
     * @param string $dataSet       Dataset file name
     *
     * @return string  Dataset file path (absolute)
     */
    protected function getDataSetFilePath($componentName, $dataSet)
    {
        $component = $this->getComponent($componentName);
        if (!$component) {
            return null;
        }

        $reflectionComponent = new \Cx\Core\Core\Model\Entity\ReflectionComponent(
            $component->getSystemComponent()
        );
        $dataSetFilePath     = $reflectionComponent->getDirectory() .
                               ASCMS_TESTING_FOLDER .
                               '/UnitTest/Data/'. $dataSet . '.yml';
        if (file_exists($dataSetFilePath)) {
            return $dataSetFilePath;
        }
        return null;
    }
}
