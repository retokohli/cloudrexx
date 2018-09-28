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

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Cx\Core\Html\Controller;

class ViewGeneratorException extends \Exception {}

/**
 * Description of ViewGenerator
 *
 * @author ritt0r
 * @todo    Refactor
 * @todo Currently, composite primary keys cannot be handled
 */
class ViewGenerator {

    /**
     * @var int $increment This ID is used to store the next free $viewId
     */
    protected static $increment = 0;

    /**
     * @var int $viewId This ID is used as html id for the view so we can load more than one view
     */
    protected $viewId;

    /**
     * @var object $object
     */
    protected $object;

    /**
     * @var array $options form options
     */
    protected $options;

    /**
     * @var array $componentOptions component options
     */
    protected $componentOptions;

    /**
     * @var FormGenerator $formGenerator
     */
    protected $formGenerator = null;

    /**
     * @var \Cx\Core\Core\Controller\Cx $cx
     */
    protected $cx;

    /**
     * @var \Cx\Core_Modules\Listing\Controller\ListingController $listingController
     */
    protected $listingController = null;

    /**
     *
     * @param mixed $object Array, instance of DataSet, instance of EntityBase, object
     * @param array $options component options
     * @throws ViewGeneratorException if there is any error in try catch statement
     */
    public function __construct($object, $options = array()) {
        $this->cx = \Cx\Core\Core\Controller\Cx::instanciate();
        $this->cx->getEvents()->triggerEvent(
            'Html.ViewGenerator:initialize',
            array(
                'options' => &$options,
            )
        );
        $this->componentOptions = $options;
        $this->viewId = static::$increment++;
        try {
            \JS::registerCSS($this->cx->getCoreFolderName() . '/Html/View/Style/Backend.css');
            $entityWithNS = preg_replace(
                '/^\\\/',
                '',
                $this->findEntityClass($object, $options)
            );

            // this is a temporary "workaround" for combined keys, see todo
            if ($entityWithNS != 'array') {
                $entityClassMetadata = \Env::get('em')->getClassMetadata($entityWithNS);
            }

            $this->initializeOptions($entityWithNS, $options);

            if (
                (!isset($_POST['vg_increment_number']) || $_POST['vg_increment_number'] != $this->viewId) &&
                (!isset($_GET['vg_increment_number']) || $_GET['vg_increment_number'] != $this->viewId)
            ) {
                $vgIncrementNo = 'empty';
                if (isset($_POST['vg_increment_number'])) {
                    $vgIncrementNo = '#' . $_POST['vg_increment_number'];
                } else if (isset($_GET['vg_increment_number'])) {
                    $vgIncrementNo = '#' . $_GET['vg_increment_number'];
                }
                // do not make any changes to entities of other view generator instances!
                \DBG::msg('Omitting changes, my ID is #' . $this->viewId . ', supplied viewId was ' . $vgIncrementNo);
                return;
            }

            // execute add if entry is a doctrine entity (or execute callback if specified in configuration)
            // post add
            if (
                !empty($_GET['add']) && (
                    !empty($this->options['functions']['add']) &&
                    $this->options['functions']['add'] != false
                ) || (
                    !empty($this->options['functions']['allowAdd']) &&
                    $this->options['functions']['allowAdd'] != false
                )
            ) {
                $this->saveEntry($entityWithNS);
            }

            // execute edit if entry is a doctrine entity (or execute callback if specified in configuration)
            // post edit
            $editId = $this->getEntryId();
            if (
                $editId != 0 && (
                    (
                        !empty($this->options['functions']['edit']) &&
                        $this->options['functions']['edit'] != false
                    ) || (
                        !empty($this->options['functions']['allowEdit']) &&
                        $this->options['functions']['allowEdit'] != false
                    )
                )
            ) {
                $this->saveEntry($entityWithNS);
            }

            // execute remove if entry is a doctrine entity (or execute callback if specified in configuration)
            // post remove
            $deleteId = !empty($_GET['deleteid']) ? contrexx_input2raw($_GET['deleteid']) : '';
            if (
                $deleteId!='' && (
                    (
                        !empty($this->options['functions']['delete']) &&
                        $this->options['functions']['delete'] != false
                    ) || (
                        !empty($this->options['functions']['allowDelete']) &&
                        $this->options['functions']['allowDelete'] != false
                    )
                )
            ) {
                $this->removeEntry($entityWithNS);
            }
        } catch (\Exception $e) {
            \Message::add($e->getMessage(), \Message::CLASS_ERROR);
            return;
        }
    }

    /**
     * This function is used to find the namespace of a passed object
     * This sets $this->object
     * @access protected
     * @param object $object object of which the namespace is needed
     * @param array $options All options supplied to this ViewGenerator
     * @return string namespace of the passed object
     */
    protected function findEntityClass($object, $options)
    {
        if (is_array($object)) {
            $object = new \Cx\Core_Modules\Listing\Model\Entity\DataSet($object);
        }
        if ($object instanceof \Cx\Core_Modules\Listing\Model\Entity\DataSet) {
            // render table if no parameter is set
            $this->object = $object;
            return $this->object->getDataType();
        }
        if (is_object($object)) {
            $this->object = $object;
            return get_class($this->object);
        }
        // Resolve proxies
        $entityClassName = \Env::get('em')->getClassMetadata($object)->name;
        $entityRepository = \Env::get('em')->getRepository($entityClassName);
        $this->initializeOptions($entityClassName, $options);
        $this->getListingController(
            $entityClassName,
            $entityClassName
        );
        $this->object = $this->listingController->getData();
        if (!$this->listingController->getDataSize()) {
            $this->object = new $entityClassName();
            return $entityClassName;
        }
        return $this->object->getDataType();
    }

    /**
     * Initializes local options based on all supplied options
     * This sets $this->options
     * @param string $entityWithNS Fully qualified name of the entity class
     */
    protected function initializeOptions($entityWithNS, $options) {
        $this->options = array();
        if (isset($options[$entityWithNS]) && is_array($options[$entityWithNS])) {
            $this->options = $options[$entityWithNS];
        } elseif (
            $entityWithNS == 'array'
            && isset($options['Cx\Core_Modules\Listing\Model\Entity\DataSet'])
            && isset($options['Cx\Core_Modules\Listing\Model\Entity\DataSet'][$this->object->getIdentifier()])
        ) {
            $this->options = $options['Cx\Core_Modules\Listing\Model\Entity\DataSet'][$this->object->getIdentifier()];
        }
        // If the options for this object are not set, we use the standard values from the component
        if (empty($this->options)) {
            $this->options = $options[''];
        }

        //initialize the row sorting functionality
        $this->getSortingOption($entityWithNS);
    }

    /**
     * Returns the listing controller for this ViewGenerator
     * @param mixed $renderObject Entity name or DataSet
     * @param string $entityClass Fully qualified name of the entity class
     * @return \Cx\Core_Modules\Listing\Controller\ListingController ListingController for this ViewGenerator
     */
    protected function getListingController($renderObject, $entityClass) {
        if ($this->listingController) {
            return;
        }
        // replace foreign key search criteria
        $searchCriteria = contrexx_input2raw($this->getVgParam($_GET['search']));
        if ($entityClass !== 'array') {
            $em = $this->cx->getDb()->getEntityManager();
            $metaData = $em->getClassMetadata($entityClass);
            foreach ($metaData->associationMappings as $relationField => $associationMapping) {
                if (!isset($searchCriteria[$relationField])) {
                    continue;
                }
                $relationClass = $associationMapping['targetEntity'];
                $relationRepo = $em->getRepository($relationClass);
                $relationEntity = $relationRepo->find($searchCriteria[$relationField]);
                if ($relationEntity) {
                    $searchCriteria[$relationField] = $relationEntity;
                }
            }
        }
        if (
            !isset($this->options['functions']) ||
            !isset($this->options['functions']['paging']) ||
            $this->options['functions']['paging'] != false
        ) {
            if (!isset($this->options['functions'])) {
                $this->options['functions'] = array();
            }
            $this->options['functions']['paging'] = true;
        }
        $lcOptions = $this->options['functions'];
        if (!isset($lcOptions['searching'])) {
            $lcOptions['searching'] = false;
        }
        if ($lcOptions['searching']) {
            $lcOptions['searchFields'] = array();
            foreach ($this->options['fields'] as $field=>$fieldOptions) {
                if (
                    isset($fieldOptions['allowSearching']) &&
                    $fieldOptions['allowSearching']
                ) {
                    $lcOptions['searchFields'][] = $field;
                }
            }
        } else {
            $lcOptions['searchFields'] = array();
        }
        if (!isset($lcOptions['filterFields'])) {
            $lcOptions['filterFields'] = false;
        }
        if ($lcOptions['filterFields']) {
            $lcOptions['filterFields'] = array();
            foreach ($this->options['fields'] as $field=>$fieldOptions) {
                if (
                    isset($fieldOptions['allowFiltering']) &&
                    $fieldOptions['allowFiltering']
                ) {
                    $lcOptions['filterFields'][] = $field;
                }
            }
        } else {
            $lcOptions['filterFields'] = array();
        }
        $this->listingController = new \Cx\Core_Modules\Listing\Controller\ListingController(
            $renderObject,
            $searchCriteria,
            contrexx_input2raw($this->getVgParam($_GET['term'])),
            $lcOptions
        );
    }

    /**
     * This function saves the data of an entity to its class.
     * This only prepares the database store, but does not store it in database
     * To store them in database use persist and flush from doctrine
     *
     * @access protected
     * @param object $entity object of the class we want to save
     * @param Doctrine\ORM\Mapping\ClassMetadata $entityClassMetadata MetaData for the entity
     * @param array $entityData array with data to save to class
     * @param string $associatedTo the class which is oneToManyAssociated if it exists
     */
    protected function savePropertiesToClass($entity, $entityClassMetadata, $entityData = array(), $associatedTo='')
    {

        // if entityData is not set, we use $_POST as default, because the data are normally submitted over post
        if (empty($entityData)) {
            $entityData = $_POST;
        }

        $em = $this->cx->getDb()->getEntityManager();
        $primaryKeyNames = $entityClassMetadata->getIdentifierFieldNames();
        $entityColumnNames = $entityClassMetadata->getColumnNames(); //get the names of all fields

        //If the view is sortable, get the 'sortBy' field name and store it to the variable
        $sortByFieldName = (    isset($this->options['functions']['sortBy'])
                            &&  isset($this->options['functions']['sortBy']['field'])
                            &&  !empty($this->options['functions']['sortBy']['field'])
                           )
                           ? key($this->options['functions']['sortBy']['field'])
                           : '';
        //check the 'sortBy' field is self-healing or not
        $isSortSelfHealing = (    isset($this->options['fields'])
                              &&  isset($this->options['fields'][$sortByFieldName])
                              &&  isset($this->options['fields'][$sortByFieldName]['showDetail'])
                              &&  !$this->options['fields'][$sortByFieldName]['showDetail']
                             )
                             ? true
                             : false;
        // Foreach possible attribute in the database we try to find the matching entry in the $entityData array and add it
        // as property to the object
        foreach($entityColumnNames as $column) {
            $name = $entityClassMetadata->getFieldName($column);
            $methodBaseName = \Doctrine\Common\Inflector\Inflector::classify($name);
            $fieldSetMethodName = 'set' . $methodBaseName;
            $fieldGetMethodName = 'get' . $methodBaseName;
            if (
                isset($this->options['fields']) &&
                isset($this->options['fields'][$name]) &&
                isset($this->options['fields'][$name]['storecallback'])
            ) {
                $storecallback = $this->options['fields'][$name]['storecallback'];
                $postedValue = null;
                if (isset($entityData[$name])) {
                    $postedValue = contrexx_input2raw($entityData[$name]);
                }
                /* We use json to do the storecallback. The 'else if' is for backwards compatibility so you can declare
                 * the function directly without using json. This is not recommended and not working over session */
                if (
                    is_array($storecallback) &&
                    isset($storecallback['adapter']) &&
                    isset($storecallback['method'])
                ) {
                    $json = new \Cx\Core\Json\JsonData();
                    $jsonResult = $json->data(
                        $storecallback['adapter'],
                        $storecallback['method'],
                        array(
                            'postedValue' => $postedValue,
                        )
                    );
                    if ($jsonResult['status'] == 'success') {
                        $entityData[$name] = $jsonResult["data"];
                    }
                } else if (is_callable($storecallback)) {
                    $entityData[$name] = $storecallback($postedValue);
                }
            }
            if (isset($entityData[$name]) && !in_array($name, $primaryKeyNames)) {
                $fieldDefinition = $entityClassMetadata->getFieldMapping($name);
                if ($fieldDefinition['type'] == 'datetime') {
                    $newValue = new \DateTime($entityData[$name]);
                } elseif ($fieldDefinition['type'] == 'array') {
                    $newValue = unserialize($entityData[$name]);
                    // verify that the value is actually an array -> prevent to store other php data
                    if (!is_array($newValue)) {
                        $newValue = array();
                    }
                } else {
                    $newValue = contrexx_input2raw($entityData[$name]);
                }
                // set the value as property of the current object, so it is ready to be stored in the database
                $entity->$fieldSetMethodName($newValue);
            }

            //While adding a new entity, if the view is sortable and 'sortBy' field is disabled in the edit view
            //then the new entity sort order gets automatically adjusted.
            if (    $isSortSelfHealing
                &&  !empty($sortByFieldName)
                &&  ($sortByFieldName === $name)
                &&  !$entity->$fieldGetMethodName()
            ) {
                $qb = $em->createQueryBuilder();
                $qb ->select('e')
                    ->from(get_class($entity), 'e')
                    ->orderBy('e.' . $name, 'DESC')
                    ->setMaxResults(1);
                $result   = $qb->getQuery()->getResult();
                $newValue = isset($result[0]) ? ($result[0]->$fieldGetMethodName() + 1) : 1;
                // set the value as property of the current object,
                // so it is ready to be stored in the database
                $entity->$fieldSetMethodName($newValue);
            }
        }
        // save singleValuedAssociations
        foreach ($entityClassMetadata->getAssociationMappings() as $associationMapping) {
            $name = $associationMapping['fieldName'];
            if (
                isset($this->options['fields']) &&
                isset($this->options['fields'][$name]) &&
                isset($this->options['fields'][$name]['storecallback'])
            ) {
                $storecallback = $this->options['fields'][$name]['storecallback'];
                /* We use json to do the storecallback. The 'else if' is for backwards compatibility so you can declare
                 * the function directly without using json. This is not recommended and not working over session */
                if (
                    is_array($storecallback) &&
                    isset($storecallback['adapter']) &&
                    isset($storecallback['method'])
                ) {
                    $callback = function($entity, $value) use($storecallback, $name) {
                        $json = new \Cx\Core\Json\JsonData();
                        $jsonResult = $json->data(
                            $storecallback['adapter'],
                            $storecallback['method'],
                            array(
                                'entity' => $entity,
                                'postedValue' => $value,
                            )
                        );
                        if ($jsonResult['status'] == 'success') {
                            return $jsonResult["data"];
                        }
                        return $value;
                    };
                } else {
                    $callback = function($entity, $value) use ($storecallback) {
                        return $storecallback($entity, $value);
                    };
                }
                $callback($entityData, $entity);
            }

            // we're only interested in single valued associations here, so skip others
            if (!$entityClassMetadata->isSingleValuedAssociation($associationMapping['fieldName'])) {
                continue;
            }
            // we're only interested if there's a target entity other than $associatedTo, so skip others
            if (
                $associationMapping['targetEntity'] == '' ||
                $associatedTo == $associationMapping['targetEntity']
            ) {
                continue;
            }

            // save it:
            $targetEntityMetadata = $em->getClassMetadata($associationMapping['targetEntity']);
            // case a) was open in form directly
            $firstOffset = str_replace('\\', '_', strtolower($associationMapping['sourceEntity']));
            $secondOffset = $associationMapping['fieldName'];
            $methodBaseName = \Doctrine\Common\Inflector\Inflector::classify(
                $associationMapping['fieldName']
            );
            if (isset($entityData[$secondOffset])) {
                $this->storeSingleValuedAssociation(
                    $associationMapping['targetEntity'],
                    array(
                        $targetEntityMetadata->getFieldName($associationMapping['joinColumns'][0]['referencedColumnName']) => $entityData[$secondOffset],
                    ),
                    $entity,
                    'set' . $methodBaseName
                );
                continue;
            }

            // base b) was open in a modal form
            foreach ($_POST[$firstOffset] as $foreignEntityDataEncoded) {
                $foreignEntityData = array();
                parse_str($foreignEntityDataEncoded, $foreignEntityData);

                if (!isset($foreignEntityData[$secondOffset])) {
                    // todo: remove entity!
                    continue;
                }

                // todo: add/save entity
                $this->storeSingleValuedAssociation(
                    $associationMapping['targetEntity'],
                    array(
                        $targetEntityMetadata->getFieldName($associationMapping['joinColumns'][0]['referencedColumnName']) => $foreignEntityData[$secondOffset],
                    ),
                    $entity,
                    'set' . $methodBaseName
                );
            }
        }
    }

    /**
     * Initialize the row sorting functionality
     *
     * @param string $entityNameSpace entity namespace
     *
     * @return boolean
     */
    protected function getSortingOption($entityNameSpace)
    {
        //If the entity namespace is empty or an array then disable the row sorting
        if (empty($entityNameSpace) && $entityNameSpace === 'array') {
            return;
        }

        $em = $this->cx->getDb()->getEntityManager();
        $sortBy = (     isset($this->options['functions']['sortBy'])
                    &&  is_array($this->options['functions']['sortBy'])
                  )
                  ? $this->options['functions']['sortBy']
                  : array();
        //If the option 'sortBy' is not set in the function array
        // then disable the row sorting.
        if (empty($sortBy)) {
            return;
        }

        //If the function array has 'order' option and the order by field
        //is not equal to 'sortBy' => 'field' then disable the row sorting
        $sortField   = key($this->options['functions']['sortBy']['field']);
        $orderOption = (    isset($this->options['functions']['order'])
                        &&  is_array($this->options['functions']['order'])
                       )
                       ? key($this->options['functions']['order']) : array();
        if (!empty($orderOption) && stripos($orderOption, $sortField) === false) {
            return;
        }

        //If the 'sortBy' option does not have 'jsonadapter',
        //we need to get the component name and entity name for updating the sorting order in db
        $componentName = '';
        $entityName    = '';
        if (    !isset($sortBy['jsonadapter'])
            ||  (    isset($sortBy['jsonadapter'])
                 &&  (    empty($sortBy['jsonadapter']['object'])
                      ||  empty($sortBy['jsonadapter']['act'])
                    )
                )
        ) {
            $split          = explode('\\', $entityNameSpace);
            $componentName  = isset($split[2]) ? $split[2] : '';
            $entityName     = isset($split) ? end($split) : '';
        }

        //If 'sorting' is applied and sorting field is not equal to
        //'sortBy' => 'field' then disable the row sorting.
        $orderParamName = $entityName . 'Order';
        if (    isset($_GET[$orderParamName])
            &&  stripos($_GET[$orderParamName], $sortField) === false
        ) {
            return;
        }

        //Get the current sorting order
        $order     = isset($_GET[$orderParamName]) ? explode('/', $_GET[$orderParamName]) : '';
        $sortOrder = ($sortBy['field'][$sortField] == SORT_ASC) ? 'ASC' : 'DESC';
        if ($order) {
            $sortOrder = !empty($order[1]) ? $order[1] : 'ASC';
        }

        //Get the paging position value
        $pagingPosName  = $entityName . 'Pos';
        $pagingPosition = isset($_GET[$pagingPosName])
                          ? contrexx_input2int($_GET[$pagingPosName])
                          : 0;

        //get the primary key names
        $entityObject   = $em->getClassMetadata($entityNameSpace);
        $primaryKeyNames = $entityObject->getIdentifierFieldNames();

        //set the sorting parameters in the functions 'sortBy' array and
        //it should be used in the Backend::constructor
        $this->options['functions']['sortBy']['sortingKey'] = current($primaryKeyNames);
        $this->options['functions']['sortBy']['component']  = $componentName;
        $this->options['functions']['sortBy']['entity']     = $entityName;
        $this->options['functions']['sortBy']['sortOrder']  = $sortOrder;
        $this->options['functions']['sortBy']['pagingPosition'] = $pagingPosition;

        //Register the script Backend.js and activate the jqueryui and cx for the row sorting
        \JS::registerJS(substr($this->cx->getCoreFolderName() . '/Html/View/Script/Backend.js', 1));
    }

    /**
     * This function returns the EntryId which was sent over get or post (if both are set it will take get)
     *
     * $_GET['editid'] has the following format:
     * {<vg_incr_no>,<id_to_edit>}[,{<vg_incr_no>,<id_to_edit>}[,...]
     * <id_to_edit> can be a number, string or set of both, separated by comma
     *
     * @access protected
     * @return int 0 if no entry was found
     */
    protected function getEntryId() {
        if (!isset($_GET['editid']) && !isset($_POST['editid'])) {
            return 0;
        }
        if (isset($_GET['editid'])) {
            return $this->getVgParam($_GET['editid']);
        }
        if (isset($_POST['editid'])) {
            return $this->getVgParam($_POST['editid']);
        }
    }

    /**
     * Extracts values for this VG instance from a combined VG-style variable
     * @see getEntryId() for a description of VG-style variable format
     * @param string $param VG-style param
     * @return array|string The relevant contents of the supplied paramater
     */
    protected function getVgParam($param) {
        $inner = preg_replace('/^(?:{|%7B)(.*)(?:}|%7D)$/', '\1', $param);
        $parts = preg_split('/},{|%7D%2C%7B/', $inner);
        $value = array();
        foreach ($parts as $part) {
            $part = preg_split('/,|%2C/', $part, 2);
            if ($part[0] != $this->viewId) {
                continue;
            }
            $keyVal = preg_split('/=|%3D/', $part[1], 2);
            if (count($keyVal) == 1) {
                if (empty(current($keyVal))) {
                    continue;
                }
                $value[] = current($keyVal);
            } else {
                $value[$keyVal[0]] = $keyVal[1];
            }
        }
        if (count($value) == 1) {
            if (key($value) === 0) {
                return current($value);
            }
        }
        return $value;
    }

    /**
     * This function finds out what we want to render and then renders the form
     *
     * @param boolean $isSingle if we only render one entry
     * @access public
     * @return string rendered view
     */
    public function render(&$isSingle = false) {
        global $_ARRAYLANG;

        // this case is used to generate the add entry form, where we can create an new entry
        if (!empty($_GET['add'])
            && !empty($this->options['functions']['add'])) {
            $isSingle = true;
            return $this->renderFormForEntry(null);
        }
        $template = new \Cx\Core\Html\Sigma(\Env::get('cx')->getCodeBaseCorePath().'/Html/View/Template/Generic');
        $template->loadTemplateFile('TableView.html');
        $template->setGlobalVariable($_ARRAYLANG);
        $template->setGlobalVariable('VG_ID', $this->viewId);
        $renderObject = $this->object;
        $entityId = $this->getEntryId();

        // this case is used to get the right entry if we edit a existing one
        if (
            $this->object instanceof \Cx\Core_Modules\Listing\Model\Entity\DataSet &&
            $entityId != 0
        ) {
            if ($this->object->entryExists($entityId)) {
                $renderObject = $this->object->getEntry($entityId);
            }
        }

        // this case is used for the overview of all entities
        if ($renderObject instanceof \Cx\Core_Modules\Listing\Model\Entity\DataSet && !$isSingle) {
            if(!empty($this->options['order']['overview'])) {
                $renderObject->sortColumns($this->options['order']['overview']);
            }
            $addBtn = '';
            $actionUrl = clone \Env::get('cx')->getRequest()->getUrl();
            if (!empty($this->options['functions']['add'])) {
                $actionUrl->setParam('add', 1);
                //remove the parameter 'vg_increment_number' from actionUrl
                //if the baseUrl contains the parameter 'vg_increment_number'
                $params = $actionUrl->getParamArray();
                if (isset($params['vg_increment_number'])) {
                    \Html::stripUriParam($actionUrl, 'vg_increment_number');
                }
                $addBtn = '<br /><br /><input type="button" name="addEntity" value="'.$_ARRAYLANG['TXT_ADD'].'" onclick="location.href='."'".$actionUrl."&csrf=".\Cx\Core\Csrf\Controller\Csrf::code()."'".'" />';
            }
            $template->setVariable('ADD_BUTTON', $addBtn);
            if (!count($renderObject) || !count(current($renderObject))) {
                // make this configurable
                $template->parse('no-entries');
                return $template->get();
            }

            $this->getListingController(
                $renderObject,
                $renderObject->getDataType()
            );
            $renderObject = $this->listingController->getData();
            $this->options['functions']['vg_increment_number'] = $this->viewId;
            $backendTable = new \BackendTable($renderObject, $this->options);
            $template->setVariable(array(
                'TABLE' => $backendTable,
                'PAGING' => $this->listingController,
            ));
            $searching = (
                isset($this->options['functions']['searching']) &&
                $this->options['functions']['searching']
            );
            $filtering = (
                isset($this->options['functions']['filtering']) &&
                $this->options['functions']['filtering']
            );
            if ($searching || $filtering) {
                \JS::registerJS(substr($this->cx->getCoreFolderName() . '/Html/View/Script/Backend.js', 1));
            }
            if ($searching) {
                // If filter is used for extended search,
                // hide filter and add a toggle link
                if (
                    $filtering && (
                        !isset($this->options['functions']['autoHideFiltering']) ||
                        $this->options['functions']['autoHideFiltering']
                    )
                ) {
                    $template->touchBlock('showExtendedSearch');
                    $template->parse('showExtendedSearch');
                    $template->touchBlock('hideFilter');
                    $template->parse('hideFilter');
                }
                if (!$filtering) {
                    $template->touchBlock('submitSearch');
                    $template->hideBlock('buttonSearch');
                } else {
                    $template->touchBlock('buttonSearch');
                    $template->hideBlock('submitSearch');
                }
                $template->touchBlock('search');
                $template->parse('search');
            }
            if ($filtering) {
                // find all filter-able fields
                $filterableFields = array_keys($renderObject->rewind());
                foreach ($filterableFields as $field) {
                    if ($field == 'virtual') {
                        continue;
                    }
                    if (
                        isset($this->options['fields'][$field]['allowFiltering']) &&
                        !$this->options['fields'][$field]['allowFiltering']
                    ) {
                        continue;
                    }
                    // set field ID
                    $fieldId = 'vg-' . $this->viewId . '-filter-field-' . $field;
                    $template->setVariable('FIELD_ID', $fieldId);
                    // set field title
                    $header = $field;
                    if (isset($this->options['fields'][$field]['filterHeader'])) {
                        $header = $this->options['fields'][$field]['filterHeader'];
                    } else if (isset($this->options['fields'][$field]['header'])) {
                        $header = $this->options['fields'][$field]['header'];
                    }
                    if (isset($_ARRAYLANG[$header])) {
                        $header = $_ARRAYLANG[$header];
                    }
                    $template->setVariable('FIELD_TITLE', $header);
                    // find options: Default is a text field, for more we need doctrine
                    $optionsField = '';
                    if (isset($this->options['fields'][$field]['filterOptionsField'])) {
                        $optionsField = $this->options['fields'][$field]['filterOptionsField'](
                            $renderObject,
                            $field,
                            $fieldId,
                            'vg-' . $this->viewId . '-searchForm'
                        );
                    } else {
                        // parse options
                        // TODO: This is quite a simple way of solving this
                        $optionsField = new \Cx\Core\Html\Model\Entity\DataElement(
                            $fieldId
                        );
                        $optionsField->setAttribute('id', $fieldId);
                        $optionsField->setAttribute('form', 'vg-' . $this->viewId . '-searchForm');
                        $optionsField->setAttribute('data-vg-attrgroup', 'search');
                        $optionsField->setAttribute('data-vg-field', $field);
                        $optionsField->addClass('vg-encode');
                    }
                    // set options
                    $template->setVariable('FIELD_FILTER_OPTIONS', $optionsField);
                    // parse block
                    $template->parse('filter-field');
                }
                $template->touchBlock('filter');
                $template->parse('filter');
            }

            return $template->get();
        }

        // render form for single entry view like editEntry
        $isSingle = true;
        return $this->renderFormForEntry($entityId);
    }

    /**
     * This function will render the form for a given entry by id. If id is null, an empty form will be loaded
     *
     * @access protected
     * @param int $entityId id of the entity
     * @return string rendered view
     */
    protected function renderFormForEntry($entityId) {
        global $_CORELANG;

        $renderArray=array('vg_increment_number' => $this->viewId);
        if (!isset($this->options['fields'])) {
            $this->options['fields'] = array();
        }
        $this->options['fields']['vg_increment_number'] = array('type' => 'hidden');
        // the title is used for the heading. For example the heading in edit mode will be "edit [$entityTitle]"
        $entityTitle = isset($this->options['entityName']) ? $this->options['entityName'] : $_CORELANG['TXT_CORE_ENTITY'];

        // get the class name including the whole namspace of the class
        if ($this->object instanceof \Cx\Core_Modules\Listing\Model\Entity\DataSet) {
            $entityClassWithNS = $this->object->getDataType();
        } else {
            $entityClassWithNS = get_class($this->object);
        }
        $actionUrl = clone \Env::get('cx')->getRequest()->getUrl();
        if ($entityClassWithNS != 'array') {
            try {
                $entityObject = \Env::get('em')->getClassMetadata($entityClassWithNS);
            } catch (\Doctrine\Common\Persistence\Mapping\MappingException $e) {
                return;
            }
            $primaryKeyNames = $entityObject->getIdentifierFieldNames(); // get the name of primary key in database table
            if ($entityId == 0 && !empty($this->options['functions']['add'])) { // load add entry form
                $this->setProperCancelUrl('add');
                $actionUrl->setParam('add', 1);
                $title = sprintf($_CORELANG['TXT_CORE_ADD_ENTITY'], $entityTitle);
                $entityColumnNames = $entityObject->getColumnNames(); // get all database field names
                if (empty($entityColumnNames)) {
                    return false;
                }

                // instanciate a dummy entity of the model we are about
                // to render. we will need this for fetching any default values
                if ($this->object instanceof \Cx\Core_Modules\Listing\Model\Entity\DataSet) {
                    $object = new $entityClassWithNS();
                } else {
                    $object = $this->object;
                }

                foreach($entityColumnNames as $column) {
                    $field = $entityObject->getFieldName($column);
                    if (in_array($field, $primaryKeyNames)) {
                        continue;
                    }
                    $fieldDefinition = $entityObject->getFieldMapping($field);
                    $this->options[$field]['type'] = $fieldDefinition['type'];

                    // fetch default value of entity's field
                    if ($entityObject->getFieldValue($object, $field) !== null) {
                        $renderArray[$field] = $entityObject->getFieldValue($object, $field);
                        continue;
                    }
                    $renderArray[$field] = '';
                }
                // This is necessary to load default values set by constructor
                $this->object = new $entityClassWithNS(); 
                $associationMappings = $entityObject->getAssociationMappings();
                $classMethods = get_class_methods($entityObject->newInstance());
                foreach ($associationMappings as $field => $associationMapping) {
                    $methodBaseName = \Doctrine\Common\Inflector\Inflector::classify($field);
                    if (
                        $entityObject->isSingleValuedAssociation($field) &&
                        in_array('set' . $methodBaseName, $classMethods)
                    ) {
                        if ($entityObject->getFieldValue($this->object, $field)) {
                            $renderArray[$field] = $entityObject->getFieldValue($this->object, $field);
                            continue;
                        }
                        $renderArray[$field] = new $associationMapping['targetEntity']();
                    } else if (
                        $entityObject->isCollectionValuedAssociation($field)
                    ) {
                        $renderArray[$field] = new $associationMapping['targetEntity']();
                    }
                }
            } elseif ($entityId != 0 && $this->object->entryExists($entityId)) { // load edit entry form
                $this->setProperCancelUrl('editid');
                $actionUrl->setParam('editid', null);
                $title = sprintf($_CORELANG['TXT_CORE_EDIT_ENTITY'], $entityTitle);

                // get data of all fields of the entry, except associated fields
                $renderObject = $this->object->getEntry($entityId);
                if (empty($renderObject)) {
                    return false;
                }

                // get doctrine field name, database field name and type for each field
                foreach($renderObject as $name => $value) {
                    if ($name == 'virtual' || in_array($name, $primaryKeyNames)) {
                        continue;
                    }

                    $classMetadata = \Env::get('em')->getClassMetadata($entityClassWithNS);
                    // check if the field isn't mapped and is not an associated one
                    if (!$classMetadata->hasField($name) && !$classMetadata->hasAssociation($name)) {
                        continue;
                    }

                    $fieldDefinition['type'] = null;
                    if (!$classMetadata->hasAssociation($name)) {
                        $fieldDefinition = $entityObject->getFieldMapping($name);
                    }
                    $this->options[$name]['type'] = $fieldDefinition['type'];
                    $renderArray[$name] = $value;
                }

                // load single-valued-associations
                // this is used for those object fields that are associations, but no object has been assigned to yet
                $associationMappings = $entityObject->getAssociationMappings();
                $classMethods = get_class_methods($entityObject->newInstance());
                foreach ($associationMappings as $field => $associationMapping) {
                    $methodBaseName = \Doctrine\Common\Inflector\Inflector::classify($field);
                    if (
                        (
                            $entityObject->isSingleValuedAssociation($field) &&
                            in_array('set' . $methodBaseName, $classMethods) &&
                            !$renderArray[$field]
                        ) || (
                            $entityObject->isCollectionValuedAssociation($field) &&
                            !empty($renderArray[$field])
                        )
                    ) {
                        $renderArray[$field] = new $associationMapping['targetEntity']();
                    }
                }
            } else {
                //var_dump($entityId);
                //var_dump($this->options['functions']['add']);
                //var_dump($this->object->entryExists($entityId));
                throw new ViewGeneratorException('Tried to show form but neither add nor edit view can be shown');
            }
        } else {
            $renderArray = $this->object->toArray();
            $entityClassWithNS = '';
            $title = $entityTitle;
        }

        //sets the order of the fields
        if(!empty($this->options['order']['form'])) {
            $sortedData = array();
            foreach ($this->options['order']['form'] as $orderVal) {
                if(array_key_exists($orderVal, $renderArray)){
                    $sortedData[$orderVal] = $renderArray[$orderVal];
                }
            }
            $renderArray = array_merge($sortedData,$renderArray);
        }
        $this->formGenerator = new FormGenerator($renderArray, $actionUrl, $entityClassWithNS, $title, $this->options, $entityId, $this->componentOptions);
        // This should be moved to FormGenerator as soon as FormGenerator
        // gets the real entity instead of $renderArray
        $additionalContent = '';
        if (isset($this->options['preRenderDetail'])) {
            $preRender = $this->options['preRenderDetail'];
            /* We use json to do preRender the detail. The 'else if' is for backwards compatibility so you can declare
             * the function directly without using json. This is not recommended and not working over session */
            if (
                isset($preRender) &&
                is_array($preRender) &&
                isset($preRender['adapter']) &&
                isset($preRender['method'])
            ) {
                $json = new \Cx\Core\Json\JsonData();
                $jsonResult = $json->data(
                    $preRender['adapter'],
                    $preRender['method'],
                    array(
                        'viewGenerator' => $this,
                        'formGenerator' => $this->formGenerator,
                        'entityId'  => $entityId,
                    )
                );
                if ($jsonResult['status'] == 'success') {
                    $additionalContent .= $jsonResult["data"];
                }
            } else if (is_callable($preRender)) {
                $additionalContent = $preRender($this, $this->formGenerator, $entityId);

            }
        }
        return $this->formGenerator . $additionalContent;
    }

    /**
     * This function will return the object of the ViewGenerator
     * @access public
     * @return object the object of ViewGenerator
     */
    public function getObject() {
        return $this->object;
    }

    /**
     * This function saves an entity to the database
     *
     * @param string $entityWithNS class name including namespace
     * @access protected
     * @global array $_ARRAYLANG array containing the language variables
     */
    protected function saveEntry($entityWithNS) {
        global $_ARRAYLANG;

        $em = $this->cx->getDb()->getEntityManager();
        // if entityId is a number the user edited an existing entry. If it is null we create a new one
        $entityId = contrexx_input2raw($this->getEntryId());
        $this->renderFormForEntry($entityId);

        // if the form is not valid in any case, we stay in this view and do not save anything, because we can not be
        // sure that everything is alright
        if (!$this->validateForm()) {
            return;
        }

        // if there are no data submitted, we stay on this view, because we have nothing to save
        if (!$this->checkBlankPostRequest()){
            return;
        }

        $entityClassMetadata = $em->getClassMetadata($entityWithNS);
        $associationMappings = $entityClassMetadata->getAssociationMappings();

        // if we have a entityId, we came from edit mode and so we try to load the existing entry
        if($entityId != 0) {
            $identifierFields = $entityClassMetadata->getIdentifierFieldNames();
            $identifierData = explode('/', $entityId);
            $lookupData = array();
            foreach ($identifierFields as $index => $field) {
                $lookupData[$field] = $identifierData[$index];
            }
            $entity = $em->getRepository($entityWithNS)->find($lookupData);
            $entityArray = array(); // This array is used for the existing values
            if ($this->object->entryExists($entityId)) {
                $entityArray = $this->object->getEntry($entityId);
            }
            if (empty($entityArray)) {
                \Message::add($_ARRAYLANG['TXT_CORE_RECORD_NO_SUCH_ENTRY'], \Message::CLASS_ERROR);
                return;
            }
        } else {
            // create new entity without calling the constructor TODO: this might break certain entities!
            $entity = $entityClassMetadata->newInstance();
        }
        $classMethods = get_class_methods($entity);

        // this array is used to store all oneToMany associated entities, because we need to persist them for doctrine,
        // but we can not persist them before the main entity, so we need to buffer them
        $associatedEntityToPersist = array ();
        $deletedEntities = array();
        foreach ($associationMappings as $name => $value) {
            if (
                isset($this->options['fields'][$name]) &&
                isset($this->options['fields'][$name]['mode']) &&
                $this->options['fields'][$name]['mode'] == 'associate'
            ) {
                $associatedIds = array();
                if (isset($_POST[$name])) {
                    $associatedIds = $_POST[$name];
                }
                // get currently associated
                $assocMapping = $entityClassMetadata->getAssociationMapping($name);
                $methodBaseName = \Doctrine\Common\Inflector\Inflector::classify(
                    $assocMapping['fieldName']
                );
                $foreignEntityGetter = 'get' . $methodBaseName;
                $foreignEntityAdder = 'add' . \Doctrine\Common\Inflector\Inflector::singularize(
                    $methodBaseName
                );
                $foreignEntityRemover = 'remove' . \Doctrine\Common\Inflector\Inflector::singularize(
                    $methodBaseName
                );
                $currentlyAssociated = $entity->$foreignEntityGetter();
                if (
                    count($associatedIds) == 0 && 
                    count($currentlyAssociated) == 0
                ) {
                    continue;
                }
                // get difflists (add, remove)
                // add / remove
                // mark remote entities for persist
                foreach ($currentlyAssociated as $associatedEntity) {
                    $indexdata = implode(
                        '/',
                        \Cx\Core\Html\Controller\FormGenerator::getEntityIndexData(
                            $associatedEntity
                        )
                    );
                    if (in_array($indexdata, $associatedIds)) {
                        // case 1/3: entity is already mapped, noop
                        // unset matching index of $associatedIds
                        $key = array_search($indexdata, $associatedIds);
                        unset($associatedIds[$key]);
                    } else {
                        // case 2/3: entity should be unmapped
                        $entity->$foreignEntityRemover($associatedEntity);
                        $foreignMethodBaseName = \Doctrine\Common\Inflector\Inflector::classify(
                            $value['mappedBy']
                        );
                        $method = 'remove' . \Doctrine\Common\Inflector\Inflector::singularize(
                            $foreignMethodBaseName
                        );
                        if (method_exists($associatedEntity, $method)) {
                            $associatedEntity->$method($entity);
                        }
                    }
                }
                foreach ($associatedIds as $associatedId) {
                    // case 3/3: entity should be mapped
                    // find entity by indexdata
                    $foreignEntity = \Cx\Core\Html\Controller\FormGenerator::findEntityByIndexData(
                        $value['targetEntity'],
                        explode('/', $associatedId)
                    );
                    // map both ways
                    $entity->$foreignEntityAdder($foreignEntity);
                    $foreignMethodBaseName = \Doctrine\Common\Inflector\Inflector::classify(
                        $value['mappedBy']
                    );
                    $method = 'set' . $foreignMethodBaseName;
                    if (method_exists($associatedEntity, $method)) {
                        $associatedEntity->$method($entity);
                    }
                    // schedule foreign entity for persist
                    $associatedEntityToPersist[] = $foreignEntity;
                }
                // save
                continue;
            }

            /* if we can not find the class name or the function to save the association we skip the entry, because there
               is now way to store it without these information */
            if (empty($value['targetEntity'])) {
                \Message::add(sprintf($_ARRAYLANG['TXT_CORE_RECORD_CLASS_NOT_FOUND'], $name), \Message::CLASS_ERROR);
                continue;
            }

            /* this variable is the name of the field where we saved the values of the one to many associations
               because css does not support \ in class name */
            $relatedClassInputFieldName = str_replace('\\', '_', strtolower($value["targetEntity"]));

            if (!empty($relatedClassInputFieldName)
                && !empty($_POST[$relatedClassInputFieldName])
                && $em->getClassMetadata($entityWithNS)->isCollectionValuedAssociation($name)
            ) {
                // store one to many associated entries
                $associatedEntityClassMetadata = $em->getClassMetadata($value["targetEntity"]);

                foreach ($_POST[$relatedClassInputFieldName] as $relatedPostData) {
                    $entityData = array();
                    parse_str($relatedPostData, $entityData);

                    // if we have already an entry (on update) we take the existing one and update it.
                    // Otherwise we create a new one
                    if (isset($entityData['id']) && $entityData['id'] != 0) { // update/edit case
                        $associatedClassRepo = $em->getRepository($value["targetEntity"]);
                        $associatedEntity = $associatedClassRepo->find($entityData['id']);
                    } else { // add case
                        $associatedEntity = $associatedEntityClassMetadata->newInstance();
                    }

                    // if there are any entries which the user wants to delete, we delete them here
                    if (isset($entityData['delete']) && $entityData['delete'] == 1) {
                        $em->remove($associatedEntity);
                        $deletedEntities[] = $associatedEntity;
                    }

                    // save the "n" associated class data to its class
                    $this->savePropertiesToClass($associatedEntity, $associatedEntityClassMetadata, $entityData, $entityWithNS);

                    // Linking 1: link the associated entity to the main entity for doctrine
                    $methodBaseName = \Doctrine\Common\Inflector\Inflector::classify($name);
                    $methodBaseNameSingular = \Doctrine\Common\Inflector\Inflector::singularize(
                        $methodBaseName
                    );
                    $methodName = 'add' . $methodBaseNameSingular;
                    if (!in_array($methodName, $classMethods)) {
                        \Message::add(sprintf($_ARRAYLANG['TXT_CORE_RECORD_FUNCTION_NOT_FOUND'], $name, $methodName), \Message::CLASS_ERROR);
                        continue;
                    }
                    if (!empty($value['mappedBy'])) {
                        $entity->$methodName($associatedEntity);
                    }

                    // Linking 2: link the main entity to its associated entity. This should normally be done by
                    // 'Linking 1' but because not all components have implemented this, we do it here by ourselves
                    $methodBaseName = \Doctrine\Common\Inflector\Inflector::classify(
                        $value['mappedBy']
                    );
                    $method = 'set' . $methodBaseName;
                    if (method_exists($associatedEntity, $method)) {
                        $associatedEntity->$method($entity);
                    }

                    // buffer entity, so we can persist it later
                    $associatedEntityToPersist[] = $associatedEntity;
                }
            }
        }

        if ($entityId != 0) { // edit case
            // update the main entry in doctrine so we can store it over doctrine to database later
            $this->savePropertiesToClass($entity, $entityClassMetadata);
            $param = 'editid';
            $successMessage = $_ARRAYLANG['TXT_CORE_RECORD_UPDATED_SUCCESSFUL'];
        } else { // add case
            // save main formular class data to its class over $_POST
            $this->savePropertiesToClass($entity, $entityClassMetadata);
            $param = 'add';
            $successMessage = $_ARRAYLANG['TXT_CORE_RECORD_ADDED_SUCCESSFUL'];
        }

        $showSuccessMessage = false;
        if ($entity instanceof \Cx\Core\Model\Model\Entity\YamlEntity) {
            // Save the yaml entities
            $entityRepository = $em->getRepository($entityWithNS);
            if (!$entityRepository->isManaged($entity)) {
                $entityRepository->add($entity);
            }
            $entityRepository->flush();
            $showSuccessMessage = true;
        } else if ($entity instanceof \Cx\Model\Base\EntityBase) {
            /* We try to store the prepared em. This may fail if (for example) we have a one to many association which
               can not be null but was not set in the post request. This cases should be caught here. */
            try {
                // persist main entity. This must be done first, otherwise saving oneToManyAssociated entities won't work
                $em->persist($entity);
                // now we can persist the associated entities. We need to do this, because otherwise it will fail,
                // if yaml does not contain a cascade option
                foreach ($associatedEntityToPersist as $associatedEntity) {
                    if (in_array($associatedEntity, $deletedEntities)) {
                        continue;
                    }
                    $em->persist($associatedEntity);
                }
                $em->flush();
                $showSuccessMessage = true;
            } catch(\Cx\Core\Error\Model\Entity\ShinyException $e){
                /* Display the message from the exception. If this message is empty, we output a general message,
                   so the user knows what to do in every case */
                if ($e->getMessage() != "") {
                    \Message::add($e->getMessage(), \Message::CLASS_ERROR);
                } else {
                    \Message::add($_ARRAYLANG['TXT_CORE_RECORD_UNKNOWN_ERROR'], \Message::CLASS_ERROR);
                }
                return;
            } catch (\Exception $e) {
                echo $e->getMessage();die();
            }

        } else {
            \Message::add($_ARRAYLANG['TXT_CORE_RECORD_VALIDATION_FAILED'], \Message::CLASS_ERROR);
            \DBG::msg('Unkown entity model '.get_class($entity).'! Trying to persist using entity manager...');
        }

        if($showSuccessMessage) {
            \Message::add($successMessage);
        }
        // get the proper action url and redirect the user
        $actionUrl = clone $this->cx->getRequest()->getUrl();
        $actionUrl->setParam($param, null);
        \Cx\Core\Csrf\Controller\Csrf::redirect($actionUrl);
    }

    /**
     * This function is used to delete an entry
     *
     * @param string $entityWithNS class name including namespace
     * @access protected
     * @global array $_ARRAYLANG array containing the language variables
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Exception
     */
    protected function removeEntry($entityWithNS) {
        global $_ARRAYLANG;

        $em = $this->cx->getDb()->getEntityManager();
        $deleteId = !empty($_GET['deleteid']) ? contrexx_input2raw($_GET['deleteid']) : '';
        $entityObject = $this->object->getEntry($deleteId);
        if (empty($entityObject)) {
            \Message::add($_ARRAYLANG['TXT_CORE_RECORD_NO_SUCH_ENTRY'], \Message::CLASS_ERROR);
            return;
        }
        $entityObj = $em->getClassMetadata($entityWithNS);

        $entityClassMetadata = $em->getClassMetadata($entityWithNS);
        $identifierFields = $entityClassMetadata->getIdentifierFieldNames();
        $id = array();
        foreach ($identifierFields as $field) {
            $id[$field] = $entityObject[$field];
        }

        // delete all n associated entries, because the are not longer used and we can delete the main entry only if we
        // have no more n associated entries
        $pageRepo = $em->getRepository($entityWithNS);
        $associationMappings = $entityObj->getAssociationMappings();
        foreach ($associationMappings as $mapping => $value) {
            // we only need to delete the n associated values, the single associated will be handled by doctrine itself
            if (!$entityObj->isCollectionValuedAssociation($mapping)) {
                continue;
            }
            $mainEntity = $pageRepo->find($id);
            $getMethod = 'get' . \Doctrine\Common\Inflector\Inflector::classify($mapping);
            $associatedEntities = $mainEntity->$getMethod();
            foreach ($associatedEntities as $associatedEntity) {
                $em->remove($associatedEntity);
            }
        }

        if (!empty($id)) {
            $entityObj = $em->getRepository($entityWithNS)->find($id);
            if (!empty($entityObj)) {
                if ($entityObj instanceof \Cx\Core\Model\Model\Entity\YamlEntity) {
                    $ymlRepo = $em->getRepository($entityWithNS);
                    $ymlRepo->remove($entityObj);;
                    $ymlRepo->flush();
                } else {
                    $em->remove($entityObj);
                    $em->flush();
                }
                \Message::add($_ARRAYLANG['TXT_CORE_RECORD_DELETED_SUCCESSFUL']);
            }
        }
        $actionUrl = clone $this->cx->getRequest()->getUrl();
        $actionUrl->setParam('deleteid', null);
        \Cx\Core\Csrf\Controller\Csrf::redirect($actionUrl);
    }

    /**
     * Creates a string out of the ViewGenerator object
     *
     * @access public
     * @return string the object ViewGenerator as string
     */
    public function __toString() {
        try {
            return (string) $this->render();
        } catch (\Exception $e) {
            echo $e->getMessage();die();
        }
    }

    /**
     * This function checks if a post request contains any data besides csrf
     *
     * @access protected
     * @global array $_ARRAYLANG array containing the language variables
     * @return bool true if $_POST is empty
     */
    protected function checkBlankPostRequest() {
        global $_ARRAYLANG;

        $post = $_POST;
        unset($post['csrf']);
        $blankPost = true;
        if (!empty($post)) {
            foreach ($post as $value) {
                if ($value) {
                    $blankPost = false;
                }
            }
        }
        if ($blankPost) {
            \Message::add($_ARRAYLANG['TXT_CORE_RECORD_FILL_OUT_AT_LEAST_ONE_FILED'], \Message::CLASS_ERROR);
            return false;
        }
        return true;
    }

    /**
     * This function checks if a form is valid
     *
     * @access protected
     * @global array $_ARRAYLANG array containing the language variables
     * @return boolean true if form is valid
     */
    protected function validateForm() {
        global $_ARRAYLANG;

        if ($this->formGenerator === false) {
            // cannot save, no such entry
            \Message::add($_ARRAYLANG['TXT_CORE_RECORD_NO_SUCH_ENTRY'], \Message::CLASS_ERROR);
            return false;
        } else if (
            !$this->formGenerator->isValid() ||
            (
                isset($this->options['validate']) &&
                !$this->options['validate']($this->formGenerator)
            )
        ) {
            // data validation failed
            \Message::add($_ARRAYLANG['TXT_CORE_RECORD_VALIDATION_FAILED'], \Message::CLASS_ERROR);
            return false;
        }
        return true;

    }

    /**
     * Sets the cancel url for the given param
     *
     * @access protected
     * @param string $parameterName name of the param
     */
    protected function setProperCancelUrl($parameterName){
        if (!isset($this->options['cancelUrl']) || !is_a($this->options['cancelUrl'], 'Cx\Core\Routing\Url')) {
            $this->options['cancelUrl'] = clone \Env::get('cx')->getRequest()->getUrl();
        }
        $this->options['cancelUrl']->setParam($parameterName, null);
    }

    /**
     * Adds/sets a foreign entity (1:1 or n:1)
     *
     * @param string $targetEntity FQCN of foreign entity
     * @param array $criteria Criteria to fetch the entity to set
     * @param object $entity Entity to set foreign entity of
     * @param string $methodName Name of method to set entity
     */
    protected function storeSingleValuedAssociation($targetEntity, $criteria, $entity, $methodName) {
        $association = \Env::get('em')->getRepository($targetEntity)->findOneBy($criteria);
        $entity->$methodName($association);
    }

    /**
     * Get the Url to edit an entry of this VG instance
     * @param int|string|array|object $entryOrId Entity or entity key
     * @param \Cx\Core\Routing\Url $url (optional) If supplied necessary params are applied
     * @return \Cx\Core\Routing\Url URL with edit arguments
     */
    public function getEditUrl($entryOrId, $url = null) {
        return static::getVgEditUrl($this->viewId, $entryOrId, $url);
    }

    /**
     * Get the Url to delete an entry of this VG instance
     * @param int|string|array|object $entryOrId Entity or entity key
     * @return \Cx\Core\Routing\Url URL with delete arguments
     */
    public function getDeleteUrl($entryOrId) {
        return static::getVgDeleteUrl($this->viewId, $entryOrId);
    }

    /**
     * Get the Url to create an entry in this VG instance
     * @param \Cx\Core\Routing\Url $url (optional) If supplied necessary params are applied
     * @return \Cx\Core\Routing\Url URL with create arguments
     */
    public function getCreateUrl($url = null) {
        return static::getVgCreateUrl($this->viewId, $url);
    }

    /**
     * Get the Url to perform search in this VG instance
     * @param string $term Search term
     * @param \Cx\Core\Routing\Url $url (optional) If supplied necessary params are applied
     * @return \Cx\Core\Routing\Url URL with search arguments
     */
    public function getSearchUrl($term, $url = null) {
        return static::getVgSearchUrl($this->viewId, $term, $url);
    }

    /**
     * Get the Url to perform extended search in this VG instance
     * @param array $criteria field=>value type array
     * @param \Cx\Core\Routing\Url $url (optional) If supplied necessary params are applied
     * @return \Cx\Core\Routing\Url URL with extended search arguments
     */
    public function getExtendedSearchUrl($criteria, $url = null) {
        return static::getVgExtendedSearchUrl($this->viewId, $criteria, $url);
    }

    /**
     * Get the Url to sort entries in this VG instance
     * @param array $sort field=>SORT_ASC|SORT_DESC type array
     * @param \Cx\Core\Routing\Url $url (optional) If supplied necessary params are applied
     * @return \Cx\Core\Routing\Url URL with sort arguments
     */
    public function getSortUrl($sort, $url = null) {
        return static::getVgSortUrl($this->viewId, $sort, $url);
    }

    /**
     * Gets the Url object used to build Urls for this VG
     * @return \Cx\Core\Routing\Url Url object used to build Urls for this VG
     */
    protected static function getBaseUrl() {
        return clone \Cx\Core\Core\Controller\Cx::instanciate()->getRequest()->getUrl();
    }

    /**
     * Get the Url to edit an entry of a VG instance
     * @param int $vgId ViewGenerator id
     * @param int|string|array|object $entryOrId Entity or entity key
     * @param \Cx\Core\Routing\Url $url (optional) If supplied necessary params are applied
     * @return \Cx\Core\Routing\Url URL with edit arguments
     */
    public static function getVgEditUrl($vgId, $entryOrId, $url = null) {
        if (!$url) {
            $url = static::getBaseUrl();
        }
        static::appendVgParam(
            $url,
            $vgId,
            'editid',
            static::getEditId($entryOrId)
        );
        return $url;
    }

    /**
     * Parses the mixed type $entryOrId param for all the get...Url methods
     * @param int|string|array|object $entryOrId Entity or entity key
     * @return string Entity identifier
     */
    protected static function getEditId($entryOrId) {
        if (is_array($entryOrId)) {
            return implode('/', $entryOrId);
        }
        if (is_object($entryOrId)) {
            // find id using doctrine or dataset
        }
        return $entryOrId;
    }

    /**
     * Appends a VG-style parameter to an Url object
     *
     * VG-style means:
     * {<vgIncrementNumber>,(<key>=)<value>}(,...) 
     * @param \Cx\Core\Routing\Url $url Url object to apply params to
     * @param int $vgId ID of the VG for the parameter
     * @param string $name Parameter name
     * @param string $value Parameter value
     */
    protected static function appendVgParam($url, $vgId, $name, $value) {
        $params = $url->getParamArray();
        $pre = '';
        if (isset($params[$name])) {
            $pre = $params[$name];
        }
        if (!empty($pre)) {
            $pre .= ',';
        }
        $url->setParam(
            $name,
            $pre . '{' . $vgId . ',' . $value . '}'
        );
    }

    /**
     * Get the Url to delete an entry of a VG instance
     * @param int $vgId ID of the VG for the parameter
     * @param int|string|array|object $entryOrId Entity or entity key
     * @return \Cx\Core\Routing\Url URL with delete arguments
     */
    public static function getVgDeleteUrl($vgId, $entryOrId) {
        $url = static::getBaseUrl();
        // this is temporary:
        $url->setParam('deleteid', static::getEditId($entryOrId));
        $url->setParam('vg_increment_number', $vgId);
        return $url;
        // this would be the way to go:
        static::appendVgParam($url, $vgId, 'deleteid', static::getEditId($entryOrId));
        return $url;
    }

    /**
     * Get the Url to create an entry in a VG instance
     * @param int $vgId ID of the VG for the parameter
     * @param \Cx\Core\Routing\Url $url (optional) If supplied necessary params are applied
     * @return \Cx\Core\Routing\Url URL with create arguments
     */
    public static function getVgCreateUrl($vgId, $url = null) {
        if (!$url) {
            $url = static::getBaseUrl();
        }
        // this is temporary:
        $url->setParam('add', $vgId);
        return $url;
        // this would be the way to go:
        static::appendVgParam($url, $vgId, 'add', '');
        return $url;
    }

    /**
     * Get the Url to perform search in a VG instance
     * @param int $vgId ID of the VG for the parameter
     * @param string $term Search term
     * @param \Cx\Core\Routing\Url $url (optional) If supplied necessary params are applied
     * @return \Cx\Core\Routing\Url URL with search arguments
     */
    public static function getVgSearchUrl($vgId, $term, $url = null) {
        if (!$url) {
            $url = static::getBaseUrl();
        }
        static::appendVgParam($url, $vgId, 'term', $term);
        return $url;
    }

    /**
     * Get the Url to perform extended search in a VG instance
     * @param int $vgId ID of the VG for the parameter
     * @param array $criteria field=>value type array
     * @param \Cx\Core\Routing\Url $url (optional) If supplied necessary params are applied
     * @return \Cx\Core\Routing\Url URL with extended search arguments
     */
    public static function getVgExtendedSearchUrl($vgId, $criteria, $url = null) {
        if (!$url) {
            $url = static::getBaseUrl();
        }
        foreach ($criteria as $field=>$value) {
            static::appendVgParam($url, $vgId, 'search', $field . '=' . $value);
        }
        return $url;
    }

    /**
     * Get the Url to sort entries in a VG instance
     * @param int $vgId ID of the VG for the parameter
     * @param array $sort field=>SORT_ASC|SORT_DESC type array
     * @param \Cx\Core\Routing\Url $url (optional) If supplied necessary params are applied
     * @return \Cx\Core\Routing\Url URL with sort arguments
     */
    public static function getVgSortUrl($vgId, $sort, $url = null) {
        if (!$url) {
            $url = static::getBaseUrl();
        }
        foreach ($sort as $field=>$order) {
            static::appendVgParam($url, $vgId, 'order', $field . '=' . $order);
        }
        return $url;
    }
}
