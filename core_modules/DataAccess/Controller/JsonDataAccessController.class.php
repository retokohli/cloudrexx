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
            'getReadonlyField' => $this->getDefaultPermissions(),
            'storeSelectedDataAccess' => $this->getDefaultPermissions(),
            'getDataAccessReadOnlySearch' => $this->getDefaultPermissions(),
            'getDataAccessSearch' => $this->getDefaultPermissions(),
            'getFieldListSearch' => $this->getDefaultPermissions(),
            'getAccessCondition' => $this->getDefaultPermissions(),
            'storeAccessCondition' => $this->getDefaultPermissions(),
            'getAllowedOutputMethods' => $this->getDefaultPermissions(),
            'storeAllowedOutputMethods' => $this->getDefaultPermissions(),
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

    /**
     * Get a simple div to display the value that cannot be edited.
     *
     * @param $args array arguments from formfield callback
     * @return \Cx\Core\Html\Model\Entity\HtmlElement div element
     */
    public function getReadonlyField($args)
    {
        $text = new \Cx\Core\Html\Model\Entity\TextElement($args['value']);
        $wrapper = new \Cx\Core\Html\Model\Entity\HtmlElement('div');
        $wrapper->addChild($text);

        return $wrapper;
    }

    /**
     * Get an array with all output methods.
     *
     * @return array contains names of output methods
     */
    protected function getOutputMethods()
    {
        $delimiter = 'Output';
        $outputMethods = array();

        $controllerClasses = $this->getSystemComponentController()
            ->getControllerClasses();

        foreach ($controllerClasses as $controller) {
            if (preg_match('/\w+'. $delimiter .'[[:>:]]/', $controller)) {
                $outputMethods[] = strtolower(explode($delimiter, $controller)[0]);
            }
        }

        return $outputMethods;
    }

    /**
     * Delete elements with the key 0 from the array.
     *
     * @param $value array include array to serialize
     * @return string serialized array
     */
    public function storeAccessCondition($value)
    {
        // Unset all empty conditions
        unset($value['postedValue'][0]);

        return $this->serializeArray($value);
    }

    /**
     * Get all conditions and an extra row to add a new condition
     *
     * @param $args array arguments from formfield callback
     * @return \Cx\Core\Html\Model\Entity\HtmlElement element with conditions
     * @throws \Cx\Core\Error\Model\Entity\ShinyException handle if dataSource
     *                                                   or dataAccess not exist
     */
    public function getAccessCondition($args)
    {
        global $_ARRAYLANG;

        $id = $args['id'];

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
        $wrapper = new \Cx\Core\Html\Model\Entity\HtmlElement('div');

        $fields = array('' => '-');
        $fields = array_merge(
            $fields,
            $dataSource->listFields()
        );
        $fields = array_combine($fields, $fields);
        $operations = array('' => '-');
        $operations = array_merge(
            $operations,
            $dataSource->getSupportedOperations()
        );
        $operations = array_combine($operations, $operations);

        foreach ($dataAccess->getAccessCondition() as $field=>$condition) {
            $selectedOperation = key($condition);
            $value = $condition[$selectedOperation];
            $row = $this->getConditionRow(
                $name, $value, $field, $fields, $selectedOperation, $operations,
                true
            );
            $wrapper->addChild($row);
        }

        $row = $this->getConditionRow(
            $name, '', '', $fields, '', $operations
        );
        $wrapper->addChild($row);

        return $wrapper;

    }

    /**
     * Get a row to show a certain condition. It includes a select to choose
     * the field, a select to choose the operations and an input field to
     * define the value
     *
     * @param $name              string name of condition row
     * @param $value             string existing value for condition
     * @param $selectedField     string field that is selected
     * @param $fields            array  all available fields
     * @param $selectedOperation string options that is selected
     * @param $operations        array  all available options
     * @param $addDelete         bool   if an delete icon should be added
     * @return \Cx\Core\Html\Model\Entity\HtmlElement generate condition row
     */
    protected function getConditionRow(
        $name, $value, $selectedField, $fields, $selectedOperation, $operations,
        $addDelete = false
    ) {
        $row = new \Cx\Core\Html\Model\Entity\HtmlElement('div');
        $row->addClass('condition-row');

        $selectFields = new \Cx\Core\Html\Model\Entity\DataElement(
            'condition-fields',
            $selectedField,
            'select',
            null,
            $fields
        );
        $selectFields->addClass('condition-fields');

        $selectOperations = new \Cx\Core\Html\Model\Entity\DataElement(
            'conditions-operations',
            $selectedOperation,
            'select',
            null,
            $operations
        );
        $selectOperations->addClass('condition-operations');

        $input = new \Cx\Core\Html\Model\Entity\DataElement(
            $name . '['.$selectedField.']['.$selectedOperation.']',
            $value
        );
        $input->addClass('condition-input');

        $row->addChild($selectFields);
        $row->addChild($selectOperations);
        $row->addChild($input);

        // Add element to delete a row
        if ($addDelete) {
            $wrapper = new \Cx\Core\Html\Model\Entity\HtmlElement('span');
            $delete = new \Cx\Core\Html\Model\Entity\HtmlElement('a');
            $wrapper->addClass('delete-wrapper');
            $delete->addClass('delete');
            $delete->allowDirectClose(null);
            $wrapper->addChild($delete);
            $row->addChild($wrapper);
        }

        return $row;
    }

    /**
     * Get all output methods as checkboxes and select the allowed methods. If
     * no method is defined as allowed, all methods are allowed
     *
     * @param $args array arguments from formfield callback
     * @return \Cx\Core\Html\Model\Entity\HtmlElement wrapper with all
     *                                                checkboxes
     * @throws \Cx\Core\Error\Model\Entity\ShinyException handle illegal inputs
     */
    public function getAllowedOutputMethods($args)
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

        $outputMethods = $this->getOutputMethods();
        $allowedOutputMethods = $dataAccess->getAllowedOutputMethods();
        $wrapper = new \Cx\Core\Html\Model\Entity\HtmlElement('div');

        $wrapper->addChild(
            $this->getCheckboxes(
                $name, $outputMethods, $allowedOutputMethods
            )
        );

        return $wrapper;
    }

    /**
     * When all output methods are selected, serialize an empty array. When a
     * new output method is added, the allowed data access automatically
     * supports that output method.
     *
     * @param $value array include array to serialize
     * @return string serialized array
     */
    public function storeAllowedOutputMethods($value)
    {
        $newValue = array();
        $outputMethods = $this->getOutputMethods();

        if (array_diff($outputMethods, $value['postedValue'])) {
            $newValue['postedValue'] = $value['postedValue'];
        }

        return $this->serializeArray($newValue);
    }

    public function getDataAccessPermission() {}

    public function getDataAccessPermissionId() {}

    /**
     * The ViewGenerator expects a string for the doctrine type array.
     * Therefore, the obtained array must be converted before it can be saved.
     * At a later time, the ViewGenerator will be modified.
     *
     * @param $value array include array to serialize
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
     * @throws \Cx\Core\Error\Model\Entity\ShinyException handle illegal inputs
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
     * @param $args array arguments from formfield callback
     * @return \Cx\Core\Html\Model\Entity\DataElement search element
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
     * @param $args array arguments from formfield callback
     * @return \Cx\Core\Html\Model\Entity\DataElement search element
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

    /**
     * Get a label element with a title, similar
     * to the generated attributes from the ViewGenerator
     *
     * @param $key string used to identify the lang placeholder
     * @return \Cx\Core\Html\Model\Entity\HtmlElement label element
     */
    protected function getLabelWrapper($key)
    {
        global $_ARRAYLANG;

        $label = new \Cx\Core\Html\Model\Entity\HtmlElement('label');
        $text = new \Cx\Core\Html\Model\Entity\TextElement(
            $_ARRAYLANG[$key]
        );
        $label->addChild($text);

        return $label;
    }

    /**
     * Wrap a div around the content and add the controls class, similar
     * to the generated attributes from the ViewGenerator.
     *
     * @param $name  string                                 to identify element
     * @param $child \Cx\Core\Html\Model\Entity\HtmlElement element to add as
     *                                                      child
     * @return \Cx\Core\Html\Model\Entity\HtmlElement
     */
    protected function getContentWrapper($name, $child)
    {
        $controls = new \Cx\Core\Html\Model\Entity\HtmlElement('div');
        $controls->addClass('controls');
        // Replace "[" and "]" to get a valid html id.
        $id = str_replace(
            ']', '', str_replace(
                '[', '-', $name
            )
        );
        $controls->setAttribute('id', $id);
        $controls->addChild($child);

        return $controls;
    }

    /**
     * Combine label and content to get a div with the class 'group', similar
     * to the generated attributes from the ViewGenerator
     *
     * @param $name    string name of html element
     * @param $content \Cx\Core\Html\Model\Entity\HtmlElement content to add
     * @param $key     string to identify lang placeholder
     * @return \Cx\Core\Html\Model\Entity\HtmlElement group element
     */
    protected function getGroupWrapper($name, $content, $key)
    {
        $group = new \Cx\Core\Html\Model\Entity\HtmlElement('div');
        $group->addClass('group');
        $group->addChildren(
            array(
                $this->getLabelWrapper($key),
                $this->getContentWrapper($name, $content)
            )
        );

        return $group;
    }

    /**
     * The ViewGenerator expects a string for the doctrine type array.
     * Therefore, the obtained array must be converted before it can be saved.
     * At a later time, the ViewGenerator will be modified.
     *
     * @param $value array include array to serialize
     * @return string serialized array
     */
    public function serializeArray($value)
    {
        return serialize($value['postedValue']);
    }

    /**
     * Get checkboxes with all possible values and check the selected values
     *
     * @param $name           string name of the checkboxes
     * @param $values         array  contains all possible values
     * @param $selectedValues array  contains all selected values
     * @return \Cx\Core\Html\Model\Entity\HtmlElement element with checkboxes
     */
    protected function getCheckboxes($name, $values, $selectedValues)
    {
        $wrapper = new \Cx\Core\Html\Model\Entity\HtmlElement('div');

        foreach ($values as $value) {
            $label = new \Cx\Core\Html\Model\Entity\HtmlElement('label');
            $text = new \Cx\Core\Html\Model\Entity\TextElement(
                ucfirst($value)
            );
            $checkbox = new \Cx\Core\Html\Model\Entity\DataElement(
                $name . '[]',
                $value
            );
            $checkbox->setAttribute('type', 'checkbox');

            if (
                empty($selectedValues) ||
                in_array($value, $selectedValues)
            ) {
                $checkbox->setAttribute('checked', 'checked');
            }

            $label->addChild($checkbox);
            $label->addChild($text);
            $wrapper->addChild($label);
        }

        return $wrapper;
    }

    /**
     * Get radio buttons with all possible values and check the selected value
     *
     * @param $name          string name of the checkboxes
     * @param $values        array  contains all possible values
     * @param $selectedValue string contains the selected value
     * @return \Cx\Core\Html\Model\Entity\HtmlElement element with checkboxes
     */
    protected function getRadioButtons($name, $values, $selectedValue)
    {
        $wrapper = new \Cx\Core\Html\Model\Entity\HtmlElement('div');

        foreach ($values as $key=>$value) {
            $label = new \Cx\Core\Html\Model\Entity\HtmlElement('label');
            $text = new \Cx\Core\Html\Model\Entity\TextElement(
                ucfirst($value)
            );
            $checkbox = new \Cx\Core\Html\Model\Entity\DataElement(
                $name,
                $key
            );
            $checkbox->setAttribute('type', 'radio');

            if ($key == $selectedValue) {
                $checkbox->setAttribute('checked', 'checked');
            }

            $label->addChild($checkbox);
            $label->addChild($text);
            $wrapper->addChild($label);
        }

        return $wrapper;
    }

    /**
     * Get the DataElement to select the data entries
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

}