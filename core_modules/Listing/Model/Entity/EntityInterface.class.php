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
     * import
     * 
     * @param type $data
     */
    public function import($data) {
        $associationMappings = \Env::get('em')->getClassMetadata($this->entityClass)->getAssociationMappings();
        
        $covertedData = array();
        $classMethods = get_class_methods($this->entityClass);
        foreach ($data as $dataVal) {
            $result = array();
            foreach ($dataVal as $field => $value) {
                $methodNameToFetchAssociation = 'get' . ucfirst($field);
                if (array_key_exists($field, $associationMappings)) {
                    $result[$field] = array();
                } elseif (in_array($methodNameToFetchAssociation, $classMethods)) {
                    $result[$field] = $value;
                }                 
            }
            $covertedData[] = $result;
        }
        return $covertedData;
    }

}
