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
 * DoctrineRepository
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_datasource
 */

namespace Cx\Core\DataSource\Model\Entity;

/**
 * DoctrineRepository
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_datasource
 */

class DoctrineRepository extends DataSource {

    /**
     * Gets one or more entries from this DataSource
     *
     * If an argument is not provided, no restriction is made for this argument.
     * So if this is called without any arguments, all entries of this
     * DataSource are returned.
     * If no entry is found, an empty array is returned.
     * @param string $elementId (optional) ID of the element if only one is to be returned
     * @param array $filter (optional) field=>value-type condition array, only supports = for now
     * @param array $order (optional) field=>order-type array, order is either "ASC" or "DESC"
     * @param int $limit (optional) If set, no more than $limit results are returned
     * @param int $offset (optional) Entry to start with
     * @param array $fieldList (optional) Limits the result to the values for the fields in this list
     * @throws \Exception If doctrine repository for this DataSource could not be found
     * @return array Two dimensional array (/table) of results (array($row=>array($fieldName=>$value)))
     */
    public function get(
        $elementId = null,
        $filter = array(),
        $order = array(),
        $limit = 0,
        $offset = 0,
        $fieldList = array()
    ) {
        $repo = $this->getRepository();
        $em = $this->cx->getDb()->getEntityManager();

        $criteria = array();

        // $filter
        if (count($fieldList)) {
            foreach ($filter as $field=>$value) {
                if (!in_array($field, $fieldList)) {
                    continue;
                }
                $criteria[$field] = $value;
            }
        }

        // $elementId
        if (isset($elementId) && count($elementId)) {
            $meta = $em->getClassMetadata($this->getIdentifier());
            $identifierField = $meta->getSingleIdentifierFieldName();
            $criteria[$identifierField] = current($elementId);
        }

        // $order
        foreach ($order as $field=>$ascdesc) {
            if (
                !in_array($field, $fieldList) ||
                !in_array($ascdesc, array('ASC', 'DESC'))
            ) {
                unset($order[$field]);
            }
        }

        // order, limit and offset are not supported by our doctrine version
        // yet! This would be the nice way to solve this:
        /*$result = $repo->findBy(
            $criteria,
            $order,
            (int) $limit,
            (int) $offset
        );//*/

        // but for now we'll have to:
        $qb = $em->createQueryBuilder();
        $qb->select('x')
            ->from($this->getIdentifier(), 'x');
        // $filter
        $i = 1;
        foreach ($criteria as $field=>$value) {
            $qb->andWhere($qb->expr()->eq('x.' . $field, '?' . $i));
            $qb->setParameter($i, $value);
            $i++;
        }
        // $order, $limit, $offset
        foreach ($order as $field=>$ascdesc) {
            $qb->orderBy('x.' . $field, $ascdesc);
        }
        // $limit, $offset
        if ($limit) {
            $qb->setMaxResults($limit);
            if ($offset) {
                $qb->setFirstResult($offset);
            }
        }
        $result = $qb->getQuery()->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        // $fieldList
        $dataSet = new \Cx\Core_Modules\Listing\Model\Entity\DataSet($result);
        if (count($fieldList)) {
            $dataFlipped = $dataSet->flip()->toArray();
            foreach ($dataFlipped as $key=>$value) {
                if (!in_array($key, $fieldList)) {
                    unset($dataFlipped[$key]);
                }
            }
            $dataSetFlipped = new \Cx\Core_Modules\Listing\Model\Entity\DataSet(
                $dataFlipped
            );
            $dataSet = $dataSetFlipped->flip();
        }

        return $dataSet->toArray();
    }

    /**
     * Adds a new entry to this DataSource
     * @param array $data Field=>value-type array. Not all fields may be required.
     * @throws \Exception If something did not go as planned
     * @return string ID of the new entry
     */
    public function add($data) {
        $em = $this->cx->getDb()->getEntityManager();
        $entityClass = $this->getIdentifier();
        $entityClassMetadata = $em->getClassMetadata($entityClass);
        $entity = $entityClassMetadata->newInstance();

        $this->setEntityData($entity, $data);

        $em->persist($entity);
        $em->flush();
        return $this->getEntityIndexData($entity);
    }
    
    /**
     * @todo: This method should be elsewhere
     */
    protected function getEntityIndexData($entity) {
        $em = $this->cx->getDb()->getEntityManager();
        $entityClassName = get_class($entity);
        $entityMetaData = $em->getClassMetadata($entityClassName);
        $entityIndexData = array();
        foreach ($entityMetaData->getIdentifierFieldNames() as $field) {
            $entityIndexData[$field] = $entityMetaData->getFieldValue($entity, $field);
        }
        return $entityIndexData;
    }

    /**
     * Updates an existing entry of this DataSource
     * @param string $elementId ID of the element to update
     * @param array $data Field=>value-type array. Not all fields are required.
     * @throws \Exception If something did not go as planned
     */
    public function update($elementId, $data) {
        $em = $this->cx->getDb()->getEntityManager();
        $repo = $this->getRepository();

        $entity = $repo->find($elementId);

        if (!$entity) {
            throw new \Exception('Entry not found!');
        }

        $this->setEntityData($entity, $data);

        $em->persist($entity);
        $em->flush();
    }

    /**
     * Drops an entry from this DataSource
     * @param string $elementId ID of the element to update
     * @throws \Exception If something did not go as planned
     */
    public function remove($elementId) {
        $em = $this->cx->getDb()->getEntityManager();
        $repo = $this->getRepository();

        $entity = $repo->find($elementId);

        if (!$entity) {
            throw new \Exception('Entry not found!');
        }

        $em->remove($entity);
        $em->flush();
    }

    /**
     * Returns the repository for this DataSource
     * @return \Doctrine\ORM\EntityRepository Repository for this DataSource
     */
    protected function getRepository() {
        $em = $this->cx->getDb()->getEntityManager();
        $repo = $em->getRepository($this->getIdentifier());

        if (!$repo) {
            throw new \Exception('Repository not found!');
        }

        return $repo;
    }

    /**
     * Sets data for an entity
     * @todo Check relations
     * @param \Cx\Model\Base\EntityBase $entity Entity to set data
     * @param array $data Field=>$value-type array
     */
    protected function setEntityData($entity, $data) {
        $em = $this->cx->getDb()->getEntityManager();
        $entityClassMetadata = $em->getClassMetadata(get_class($entity));
        $primaryKeyNames = $entityClassMetadata->getIdentifierFieldNames(); //get primary key name
        $entityColumnNames = $entityClassMetadata->getColumnNames(); //get the names of all fields

        foreach($entityColumnNames as $column) {
            $name = $entityClassMetadata->getFieldName($column);
            if (/*in_array($name, $primaryKeyNames) ||*/ !isset($data[$name])) {
                continue;
            }

            $fieldDefinition = $entityClassMetadata->getFieldMapping($name);
            if ($fieldDefinition['type'] == 'datetime') {
                $data[$name] = new \DateTime($data[$name]);
            } elseif ($fieldDefinition['type'] == 'array') {
                // verify that the value is actually an array -> prevent to store other php data
                if (!is_array($data[$name])) {
                    $data[$name] = array();
                }
            }

            $fieldSetMethodName = 'set'.preg_replace('/_([a-z])/', '\1', ucfirst($name));
            // set the value as property of the current object, so it is ready to be stored in the database
            $entity->$fieldSetMethodName($data[$name]);
        }
        
        $associationMappings = $entityClassMetadata->getAssociationMappings();
        $classMethods = get_class_methods($entity);
        foreach ($associationMappings as $field => $associationMapping) {
            if (!isset($data[$field])) {
                continue;
            }
            // handle many to many relations
            if ($associationMapping['type'] == \Doctrine\ORM\Mapping\ClassMetadata::MANY_TO_MANY) {
                // prepare data
                $foreignEntityIndexes = explode(',', $data[$field]);
                $targetRepo = $em->getRepository($associationMapping['targetEntity']);
                $primaryKeys = $entityClassMetadata->getIdentifierFieldNames();
                $addMethod = 'add'.preg_replace('/_([a-z])/', '\1', ucfirst($field));
                // foreach distant entity
                foreach ($foreignEntityIndexes as $foreignEntityIndex) {
                    // prepare data
                    $foreignEntityIds = explode('/', $foreignEntityIndex);
                    $foreignEntityIndexData = array();
                    foreach ($primaryKeys as $i=>$primaryFieldName) {
                        $foreignEntityIndexData[$primaryFieldName] = $foreignEntityIds[$i];
                    }
                    $this->cx->getEvents()->triggerEvent(
                        'preDistantEntityLoad',
                        array(
                            'targetEntityClassName' => $associationMapping['targetEntity'],
                            'targetId' => &$foreignEntityIndexData,
                        )
                    );
                    // find and add entity
                    $targetEntity = $targetRepo->findOneBy($foreignEntityIndexData);
                    if (!$targetEntity) {
                        throw new \Exception(
                            'Entity not found (' . $associationMapping['targetEntity'] . ' with ID ' . var_export($foreignEntityIndexData, true) . ')'
                        );
                    }
                    $entity->$addMethod($targetEntity);
                }
            } else if (
                $entityClassMetadata->isSingleValuedAssociation($field) &&
                in_array('set'.ucfirst($field), $classMethods)
            ) {
                $foreignId = $data[$field];
                if (is_array($foreignId)) {
                    $foreignId = current($foreignId);
                }
                $targetRepo = $em->getRepository($associationMapping['targetEntity']);
                $targetEntity = $targetRepo->findOneBy(array(
                    $associationMapping['joinColumns'][0]['referencedColumnName'] => $foreignId,
                ));
                if (!$targetEntity) {
                    throw new \Exception('Entity not found (' . $associationMapping['targetEntity'] . ' with ID ' . $foreignId . ')');
                }
                $setMethod = 'set'.ucfirst($field);
                $entity->$setMethod($targetEntity);
            }
        }
    }
}
