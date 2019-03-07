<?php
/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2019
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
 * Test DataAccess
 *
 * @copyright   Cloudrexx AG
 * @author      Sam Hawkes <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_module_data_access
 */
namespace Cx\Core_Modules\DataAccess\Testing\UnitTest;

/**
 * Test DataAccess
 *
 * @copyright   Cloudrexx AG
 * @author      Sam Hawkes <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_module_data_access
 */
class DataAccessTest extends \Cx\Core\Test\Model\Entity\DoctrineTestCase
{
    /**
     * Get a DataAccess entity with test data.
     *
     * @return \Cx\Core_Modules\DataAccess\Model\Entity\DataAccess DataAccess entity
     */
    protected function getTestDataAccess()
    {
        $entity = new \Cx\Core_Modules\DataAccess\Model\Entity\DataAccess();
        $permissionEntity = new \Cx\Core_Modules\Access\Model\Entity\Permission();

        $entity->setName('test');
        $entity->setAccessCondition(array());
        $entity->setFieldList(array());
        $entity->setAllowedOutputMethods(array());
        $entity->setDataSource(1);
        $entity->setReadPermission($permissionEntity);
        $entity->setWritePermission($permissionEntity);

        return $entity;
    }

    /**
     * Get a DataAccessApiKey entity with test data.
     *
     * @return \Cx\Core_Modules\DataAccess\Model\Entity\DataAccessApiKey DataAccessApiKey entity
     */
    protected function getTestDataAccessApiKey()
    {
        $entity = new \Cx\Core_Modules\DataAccess\Model\Entity\DataAccessApiKey();
        $apiKeyEntity = new \Cx\Core_Modules\DataAccess\Model\Entity\ApiKey();
        $dataAccesEntity = $this->getTestDataAccess();

        $apiKeyEntity->setApiKey('test');

        $entity->setApiKey($apiKeyEntity);
        $entity->setDataAccess($dataAccesEntity);
        $entity->setReadOnly(true);

        return $entity;
    }

    /**
     * Store the given DataAccess entity in the database.
     *
     * @param $entity \Cx\Core_Modules\DataAccess\Model\Entity\DataAccess DataAccess to save
     */
    protected function saveDataAccess($entity)
    {
        $this::$em->persist($entity);
        $this::$em->flush();
    }

    /**
     * Find the DataAccess entity by given ID.
     *
     * @param $id int ID to identify entity
     * @return \Cx\Core_Modules\DataAccess\Model\Entity\DataAccess found entity
     */
    protected function findDataAccess($id)
    {
        $repo = $this::$em->getRepository(
            'Cx\Core_Modules\DataAccess\Model\Entity\DataAccess'
        );

        return $repo->find($id);
    }

    /**
     * Get DataAccess Json Controller.
     *
     * @return \Cx\Core_Modules\DataAccess\Controller\JsonDataAccessController
     */
    protected function getJsonController()
    {
        $componentRepo = $this::$cx->getDb()
            ->getEntityManager()
            ->getRepository('Cx\Core\Core\Model\Entity\SystemComponent');
        $componentContoller = $componentRepo->findOneBy(array('name' => 'DataAccess'));
        if (!$componentContoller) {
            return;
        }
        return $componentContoller->getController('JsonBlock');
    }

    /**
     * Test if 'DataAccess' will be returned.
     *
     * @covers \Cx\Core_Modules\DataAccess\Controller\JsonDataAccessController::getName
     */
    public function testGetName()
    {
        $jsonDataAccess = $this->getJsonController();
        $name = $jsonDataAccess->getName();

        $this->assertEquals('DataAccess', $name);
    }

    /**
     * Test if a string will be returned.
     *
     * @covers \Cx\Core_Modules\DataAccess\Controller\JsonDataAccessController::getMessageAsString
     */
    public function testGetMessageAsString()
    {
        $jsonDataAccess = $this->getJsonController();
        $message = $jsonDataAccess->getMessageAsString();

        $this->assertIsString('string', $message);
    }

    /**
     * Test if the returned permission object matches the defined permission
     * object.
     *
     * @covers \Cx\Core_Modules\DataAccess\Controller\JsonDataAccessController::getDefaultPermissions
     */
    public function testGetDefaultPermissions()
    {
        $default = new \Cx\Core_Modules\Access\Model\Entity\Permission();
        $default->setValidAccessIds(113);

        $jsonDataAccess = $this->getJsonController();
        $permission = $jsonDataAccess->getDefaultPermissions();

        $this->assertSame($default, $permission);
    }

    /**
     * Test if all necessary method names are in the array.
     *
     * @covers \Cx\Core_Modules\DataAccess\Controller\JsonDataAccessController::accessableMethods
     */
    public function testGetAccessableMethods()
    {
        $args = array(
            'name' => 'test_element',
            'value' => array(
                'test1', 'test2', 'test3'
            ),
        );

        $jsonDataAccess = $this->getJsonController();
        $htmlElement = $jsonDataAccess->getAccessableMethods(
            array('get' => $args)
        );

        $this->assertInstanceOf(
            'Cx\Core\Html\Model\Entity\HtmlElement',
            $htmlElement
        );
    }

    /**
     * Test if an HTML-Element is returned so that it can be displayed
     * automatically by the ViewGenerator.
     *
     * @covers \Cx\Core_Modules\DataAccess\Controller\JsonDataAccessController::getAccessConditions
     */
    public function testGetAccessConditions()
    {
        $args = array(
            'name' => 'test_element',
            'value' => array(
                'test1' => array(
                    'eq' => 0,
                )
            ),
        );

        $jsonDataAccess = $this->getJsonController();
        $htmlElement = $jsonDataAccess->getAccessConditions(
            array('get' => $args)
        );

        $this->assertInstanceOf(
            'Cx\Core\Html\Model\Entity\HtmlElement',
            $htmlElement
        );
    }

    /**
     * Test if an HTML-Element is returned so that it can be displayed
     * automatically by the ViewGenerator.
     *
     * @covers \Cx\Core_Modules\DataAccess\Controller\JsonDataAccessController::getTitleRow
     */
    public function testGetTitleRow()
    {
        $args = array(
            'name' => 'test_element',
            'value' => 'Test',
        );

        $jsonDataAccess = $this->getJsonController();
        $htmlElement = $jsonDataAccess->getTitleRow(
            array('get' => $args)
        );

        $this->assertInstanceOf(
            'Cx\Core\Html\Model\Entity\HtmlElement',
            $htmlElement
        );
    }

    /**
     * Test if an HTML-Element is returned so that it can be displayed
     * automatically by the ViewGenerator.
     *
     * @covers \Cx\Core_Modules\DataAccess\Controller\JsonDataAccessController::getFieldListSearch
     */
    public function testGetFieldListSearch()
    {
        $args = array(
            'name' => 'test_element',
            'value' => array(
                'test1', 'test2', 'test3'
            ),
        );

        $jsonDataAccess = $this->getJsonController();
        $htmlElement = $jsonDataAccess->getFieldListSearch(
            array('get' => $args)
        );

        $this->assertInstanceOf(
            'Cx\Core\Html\Model\Entity\HtmlElement',
            $htmlElement
        );
    }

    /**
     * Test if a permission object is returned so that it can be saved
     * automatically by the ViewGenerator.
     *
     * @covers \Cx\Core_Modules\DataAccess\Controller\JsonDataAccessController::getDataAccessPermission
     */
    public function testGetDataAccessPermission()
    {
        $jsonDataAccess = $this->getJsonController();
        $htmlElement = $jsonDataAccess->getDataAccessPermission();

        $this->assertInstanceOf(
            'Cx\Core_Module\Access\Model\Entity\Permission',
            $htmlElement
        );
    }

    /**
     * Test if an entity can be edited.
     *
     * @coversNothing
     */
    public function testEditDataAccess()
    {
        $newName = 'testEdit';

        $entity = $this->getTestDataAccess();
        $this->saveDataAccess($entity);

        $entity->setName($newName);
        $this->saveDataAccess($entity);

        $editedEntity = $this->findDataAccess($entity->getId());

        $this->assertInstanceOf(
            'Cx\Core_Modules\DataAccess\Model\Entity\DataAccess',
            $editedEntity
        );
    }

    /**
     * Test if an ApiKey can be added.
     *
     * @coversNothing
     */
    public function testAddApiKey()
    {
        $entity = $this->getTestDataAccess();

        $dataAccessApiKey = $this->getTestDataAccessApiKey();
        $entity->addDataAccessApiKey($dataAccessApiKey);
        $this->saveDataAccess($entity);

        $storedEntity = $this->findDataAccess($entity->getId());
        $firstElement = $storedEntity->getDataAccessApiKeys()->current();

        $this->assertInstanceOf(
            'Cx\Core_Modules\DataAccess\Model\Entity\DataAccessApiKey',
            $firstElement
        );
    }

    /**
     * Test if an ApiKey can be removed.
     *
     * @coversNothing
     */
    public function testRemoveApiKey()
    {
        $entity = $this->getTestDataAccess();
        $id = $entity->getId();

        $dataAccessApiKey = $this->getTestDataAccessApiKey();
        $entity->addDataAccessApiKey($dataAccessApiKey);
        $this->saveDataAccess($entity);

        $storedEntity = $this->findDataAccess($id);
        $firstDataAccess = $storedEntity->getDataAccessApiKeys()->current();
        $storedEntity->removeDataAccessApiKey($firstDataAccess);
        $this->saveDataAccess($storedEntity);

        $this->assertNull(
            'Cx\Core_Modules\DataAccess\Model\Entity\DataAccessApiKey',
            $firstDataAccess
        );
    }

}