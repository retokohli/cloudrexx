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
 * JsonController for DataAccess
 *
 * @copyright   Cloudrexx AG
 * @author      Sam Hawkes <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_modules_dataaccess
 * @version     5.0.0
 */
namespace Cx\Core_Modules\DataAccess\Controller;

/**
 * JsonController for DataAccess
 *
 * @copyright   Cloudrexx AG
 * @author      Sam Hawkes <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_modules_dataaccess
 * @version     5.0.0
 */
class JsonDataAccessController
    extends \Cx\Core\Core\Model\Entity\Controller
    implements \Cx\Core\Json\JsonAdapter
{
    /**
     * List of messages
     * @var array
     */
    protected $messages = array();

    /**
     * Returns the internal name used as identifier for this adapter
     * @return String Name of this adapter
     */
    public function getName()
    {
        return 'DataAccess';
    }

    /**
     * Returns an array of method names accessable from a JSON request
     *
     * @return array List of method names
     */
    public function getAccessableMethods()
    {
        return array(
            'serializeArray' => $this->getDefaultPermissions(),
            'storeSelectedDataAccess' => $this->getDefaultPermissions(),
            'getDataAccessReadOnlySearch' => $this->getDefaultPermissions(),
            'getDataAccessSearch' => $this->getDefaultPermissions(),
            'getFieldListSearch' => $this->getDefaultPermissions(),
            'getAccessCondition' => $this->getDefaultPermissions(),
            'getAllowedOutputMethods' => $this->getDefaultPermissions(),
            'getDataAccessPermission' => $this->getDefaultPermissions(),
            'getDataAccessPermissionId' => $this->getDefaultPermissions(),
        );
    }

    /**
     * Returns all messages as string
     *
     * @return String HTML encoded error messages
     */
    public function getMessagesAsString()
    {
        return implode('<br />', $this->messages);
    }

    /**
     * Returns default permission as object
     *
     * @return \Cx\Core_Modules\Access\Model\Entity\Permission
     */
    public function getDefaultPermissions()
    {
        $permission = new \Cx\Core_Modules\Access\Model\Entity\Permission(
            array('http', 'https'),
            array('get', 'post'),
            true,
            array(),
            array(113)
        );

        return $permission;
    }

    public function getAccessCondition() {}

    public function getAllowedOutputMethods() {}

    public function getDataAccessPermission() {}

    public function getDataAccessPermissionId() {}

    /**
     * The ViewGenerator expects a string for the doctrine type array.
     * Therefore, the obtained array must be converted before it can be saved.
     * At a later time, the ViewGenerator will be modified.
     *
     * @param $value array to serialize
     * @return string serialized array
     */
    public function serializeArray($value)
    {
        return serialize($value['postedValue']);
    }

    /**
     * Get search element to select allowed fields.
     *
     * @param $args array arguments from formfield callback
     * @return \Cx\Core\Html\Model\Entity\DataElement search element
     * @throws \Cx\Core\Error\Model\Entity\ShinyException handle
     */
    public function getFieldListSearch($args)
    {
        global $_ARRAYLANG;

        $id = $args['id'];
        if (empty($id)) {
            throw new \Cx\Core\Error\Model\Entity\ShinyException(
                $_ARRAYLANG['TXT_CORE_MODULE_DATA_ACCESS_ERROR_NO_DATA_ACCESS']
            );
        }

        $name = '';
        if (!empty($args['name'])) {
            $name = $args['name'];
        }

        $dataAccessRepo = $this->cx->getDb()->getEntityManager()->getRepository(
            'Cx\Core_Modules\DataAccess\Model\Entity\DataAccess'
        );

        $dataAccess = $dataAccessRepo->find($id);
        if (!empty($dataSource)) {
            throw new \Cx\Core\Error\Model\Entity\ShinyException(
                $_ARRAYLANG['TXT_CORE_MODULE_DATA_ACCESS_ERROR_NO_DATA_ACCESS']
            );
        }

        $dataSource = $dataAccess->getDataSource();

        if (empty($dataSource)) {
            throw new \Cx\Core\Error\Model\Entity\ShinyException(
                $_ARRAYLANG['TXT_CORE_MODULE_DATA_ACCESS_ERROR_NO_DATA_SOURCE']
            );
        }

        // Sets field names as array keys for easy storage later on.
        $selectedFields = array_combine(
            $dataAccess->getFieldList(),
            $dataAccess->getFieldList()
        );

        // Sets field names as array keys for easy storage later on.
        $allFields = array_combine(
            $dataSource->listFields(),
            $dataSource->listFields()
        );

        $data = array(
            'selected' => $selectedFields,
            'all' => $allFields
        );

        return $this->getSearch($name, $data);
    }

    /**
     * Get search element to select DataAccess entities.
     *
     * @param $args
     * @return \Cx\Core\Html\Model\Entity\DataElement
     */
    public function getDataAccessSearch($args)
    {
        $id = 0;
        if (!empty($args['id'])) {
            $id = (int)$args['id'];
        }

        $name = '';
        if (!empty($args['name'])) {
            $name = $args['name'];
        }

        $values = $this->getDataAccessValues($id);
        $data = array(
            'selected' => $values['selected']['normal'],
            'all' => $values['all'],
        );

        return $this->getSearch($name, $data);
    }

    /**
     * Get search item to select DataAccess entities that have read-only
     * permissions.
     *
     * @param $args
     * @return \Cx\Core\Html\Model\Entity\DataElement
     */
    public function getDataAccessReadOnlySearch($args)
    {
        $id = 0;
        if (!empty($args['id'])) {
            $id = (int)$args['id'];
        }

        $name = '';
        if (!empty($args['name'])) {
            $name = $args['name'];
        }

        $values = $this->getDataAccessValues($id);
        $data = array(
            'selected' => $values['selected']['readOnly'],
            'all' => $values['all'],
        );

        return $this->getSearch($name, $data);
    }

    /**
     * Get an array with all DataAccess entities and the selected ones
     * separately from each other.
     *
     * @param $apiKeyId int Id of edited API-Key
     * @return array contains all DataAccess entities
     */
    protected function getDataAccessValues($apiKeyId)
    {
        $dataAccessValues = array('selected' => array(), 'all' => array());

        $dataAccessValues['selected'] = $this->getSelectedDataAccessValues(
            $apiKeyId
        );

        $dataAccessValues['all'] = $this->getAllDataAccessValues();

        return $dataAccessValues;
    }

    /**
     * Get all selected DataAccess entities and separates them from read-only
     * and normal units.
     *
     * @param $apiKeyId int Id of edited API-Key
     * @return array contains all selected DataAccess entities
     */
    protected function getSelectedDataAccessValues($apiKeyId)
    {
        $em = $this->cx->getDb()->getEntityManager();
        $repoApiKey = $em->getRepository(
            'Cx\Core_Modules\DataAccess\Model\Entity\ApiKey'
        );
        $apiKey = $repoApiKey->find($apiKeyId);

        $dataAccessValuesSelected = array(
            'readOnly' => array(),
            'normal' => array()
        );

        $dataAccessApiKeys = array();
        if (!empty($apiKey)) {
            $dataAccessApiKeys = $apiKey->getDataAccessApiKeys();
        }

        foreach ($dataAccessApiKeys as $dataAccessApiKey) {
            $id = $dataAccessApiKey->getDataAccess()->getId();
            $name = $dataAccessApiKey->getDataAccess()->getName();

            if ($dataAccessApiKey->getReadOnly()) {
                $dataAccessValuesSelected['readOnly'][$id] = $name;
            } else {
                $dataAccessValuesSelected['normal'][$id] = $name;
            }
        }

        return $dataAccessValuesSelected;
    }

    /**
     * Get all available DataAccess entities.
     *
     * @return array contains all available DataAccess entities
     */
    protected function getAllDataAccessValues()
    {
        $em = $this->cx->getDb()->getEntityManager();

        $repoDataAccess = $em->getRepository(
            'Cx\Core_Modules\DataAccess\Model\Entity\DataAccess'
        );

        $allDataAccess = $repoDataAccess->findAll();
        $allDataAccessValues = array();

        foreach ($allDataAccess as $dataAccess) {
            $id = $dataAccess->getId();
            $name = $dataAccess->getName();

            $allDataAccessValues[$id] = $name;
        }

        return $allDataAccessValues;
    }

    /**
     * Get the DataElement to select the data entries.
     *
     * @param $name string name of the element
     * @param $data array  data to select
     * @return \Cx\Core\Html\Model\Entity\DataElement the search element
     */
    protected function getSearch($name, $data)
    {
        global $_ARRAYLANG;

        $values = $data['all'];

        $select = new \Cx\Core\Html\Model\Entity\DataElement(
            $name . '[]',
            '',
            \Cx\Core\Html\Model\Entity\DataElement::TYPE_SELECT,
            null,
            $values
        );
        foreach ($select->getChildren() as $option) {
            if (isset($data['selected'][$option->getAttribute('value')])) {
                $option->setAttribute('selected');
            }
        }
        $select->addClass('chzn');
        $select->setAttribute(
            'data-placeholder',
            $_ARRAYLANG['TXT_CORE_MODULE_DATA_ACCESS_PLEASE_CHOOSE']
        );
        $select->setAttribute('multiple');

        return $select;
    }


    /**
     * Adds the selected DataAccess entities and removes the old ones.
     *
     * @param $args array contains the ApiKey entity and the selected DataAccess
     *                    entities, normal and read-only are separated.
     * @throws \Doctrine\ORM\ORMException handle ORM exceptions
     */
    public function storeSelectedDataAccess($args)
    {
        // Check if params are valid.
        if (
            empty($args['postedValue']) ||
            empty($args['postedValue']->getId())
        ) {
            return;
        }
        $id = $args['postedValue']->getId();

        $allDataAccess = array();
        if (!empty($args['entity']['dataAccessApiKeys'])) {
            $allDataAccess = $args['entity']['dataAccessApiKeys'];
        }

        $allDataAccessReadOnly = array();
        if (!empty($args['entity']['dataAccessReadOnly'])) {
            $allDataAccessReadOnly = $args['entity']['dataAccessReadOnly'];
        }


        $em = $this->cx->getDb()->getEntityManager();
        $repoApiKey = $em->getRepository(
            'Cx\Core_Modules\DataAccess\Model\Entity\ApiKey'
        );

        foreach ($allDataAccess as $dataAccessId) {
            $repoApiKey->addNewDataAccessApiKey($id, $dataAccessId);
        }
        foreach ($allDataAccessReadOnly as $dataAccessReadOnlyId) {
            $repoApiKey->addNewDataAccessApiKey(
                $id,
                $dataAccessReadOnlyId,
                true
            );
        }

        $dataAccessIds = array_merge($allDataAccess, $allDataAccessReadOnly);

        $repoApiKey->removeOldDataAccessApiKeys($id, $dataAccessIds);
    }
}