<?php

/**
 * Entity interface
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_listing
 */

namespace Cx\Core_Modules\Listing\Model\Entity;

/**
 * Entity interface
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
     * To Export the array as objects.
     * 
     * @param array $data
     * 
     * @return array return as object array
     */
    public function export($data) {
        $associationMappings = \Env::get('em')->getClassMetadata($this->entityClass)->getAssociationMappings();

        $repo = \Env::get('em')->getRepository($this->entityClass);
        foreach ($data as $key => $value) {
            $entity = null;
            if (!empty($value['id'])) {
                $entity = $repo->findOneBy(array('id' => $value['id']));
            }
            if (!$entity) {
                $entity = new $this->entityClass();
            }
            foreach ($value as $field => $methodValue) {
                if (array_key_exists($field, $associationMappings)) {
                    continue;
                }

                $methodNameToSetAssociation = 'set' . ucfirst($field);
                $entity->$methodNameToSetAssociation($methodValue);
            }
            $entities[] = $entity;
        }
        return $entities;
    }

    /**
     * set the entity class
     * 
     * @param type $entityClass
     */
    public function setEntityClass($entityClass) {
        $this->entityClass = $entityClass;
    }

    /**
     * To import an array for the given entity object
     * 
     * @param  object $object
     * @return array return as array
     */
    public function import($object) {

        $em = \Env::get('em');
        $data = array();

        $entityClassMetaData = $em->getClassMetadata(get_class($object));
        $associationMappings = $entityClassMetaData->getAssociationMappings();

        foreach ($associationMappings as $field => $associationMapping) {
            if ($entityClassMetaData->isSingleValuedAssociation($field) && in_array('set' . ucfirst($field), get_class_methods($object))
            ) {
                $associationObj = $object->{'get' . ucfirst($field)}();
                $primaryKeyName = $em->getClassMetadata(get_class($associationObj))->getSingleIdentifierFieldName();
                $data[$field] = $associationObj->{'get' . ucfirst($primaryKeyName)}();
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
