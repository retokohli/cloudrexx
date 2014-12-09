<?php

/**
 * EntityInterface Class
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_listing
 */

namespace Cx\Core_Modules\Listing\Model\Entity;

/**
 * EntityInterface Class
 * 
 * This class used to convert the entity objects into array and array
 * into entity objects
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_listing
 */
class EntityInterface implements Exportable, Importable {

    protected $entityClass;

    /**
     * constructor
     */
    public function __construct() {
        
    }

    /**
     * This function is used to convert the array into entity objects.
     * 
     * @param  array $data
     * 
     * @return array return as entity object array
     */
    public function export($data) {
        if (empty($data)) {
            return;
        }
        $em = \Env::get('em');
        $entityClassMetaData = $em->getClassMetadata($this->entityClass);
        $repository = $em->getRepository($this->entityClass);

        $entityArray = current($data);
        
        $entityObj = null;
        $primaryKeyName = $entityClassMetaData->getSingleIdentifierFieldName();

        if (!empty($entityArray[$primaryKeyName])) {
            $entityObj = $repository->findOneBy(array($primaryKeyName => $entityArray[$primaryKeyName]));
        }
        if (!$entityObj) {
            $entityObj = new $this->entityClass();
        }
        //get the association mappings
        $associationMappings = $entityClassMetaData->getAssociationMappings();
        //class methods
        $classMethods = get_class_methods($entityObj);

        foreach ($entityArray as $field => $methodValue) {
            if (!array_key_exists($field, $associationMappings)) {
                $entityObj->{'set' . ucfirst($field)}($methodValue);
            } else {
                if ($entityClassMetaData->isSingleValuedAssociation($field) && in_array('set' . ucfirst($field), $classMethods)) {
                    $col = $associationMappings[$field]['joinColumns'][0]['referencedColumnName'];
                    $association = \Env::get('em')->getRepository($associationMappings[$field]['targetEntity'])->findOneBy(array($col => $methodValue));
                    if ($association) {
                        $entityObj->{'set' . ucfirst($field)}($association);
                    }
                }
            }
        }
        $entities[] = $entityObj;
        return $entities;
    }

    /**
     * set the entity class
     * 
     * @param string $entityClass
     */
    public function setEntityClass($entityClass) {
        $this->entityClass = $entityClass;
    }

    /**
     * This function is used to convert the entity object into array.
     * 
     * @param  object $object entity object
     * @return array  return as array
     */
    public function import($object) {

        if (!is_object($object)) {
            return;
        }

        $em = \Env::get('em');
        $data = array();

        $entityClassMetaData = $em->getClassMetadata(get_class($object));
        $associationMappings = $entityClassMetaData->getAssociationMappings();
        if (!empty($associationMappings)) {
            foreach ($associationMappings as $field => $associationMapping) {
                if ($entityClassMetaData->isSingleValuedAssociation($field) && in_array('set' . ucfirst($field), get_class_methods($object))) {
                    $associationObj = $object->{'get' . ucfirst($field)}();
                    $primaryKeyName = $em->getClassMetadata(get_class($associationObj))->getSingleIdentifierFieldName();
                    $data[$field] = $associationObj->{'get' . ucfirst($primaryKeyName)}();
                }
            }
        }
        foreach ($entityClassMetaData->getColumnNames() as $column) {
            $field = $entityClassMetaData->getFieldName($column);
            $value = $entityClassMetaData->getFieldValue($object, $field);
            if ($value instanceof \DateTime) {
                $value = $value->format('d.M.Y H:i:s');
            } elseif (is_array($value)) {
                $value = serialize($value);
            }
            $data[$field] = $value;
        }
        $data['virtual'] = $object->isVirtual();
        return array($data);
    }

}
