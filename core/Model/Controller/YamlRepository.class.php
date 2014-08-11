<?php
/**
 * YAML Repository
 *
 * A entity repository with its storage being in a YAML-file.
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Däppen <thomas.daeppen@comvation.com>
 * @package     contrexx
 * @subpackage  core_model
 */

namespace Cx\Core\Model\Controller;

/**
 * YAML Repository Exception
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Däppen <thomas.daeppen@comvation.com>
 * @package     contrexx
 * @subpackage  core_model
 */
class YamlRepositoryException extends \Exception {};

/**
 * YAML Repository
 *
 * A entity repository with its storage being in a YAML-file.
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Däppen <thomas.daeppen@comvation.com>
 * @package     contrexx
 * @subpackage  core_model
 */
class YamlRepository {
    /**
     * Absolute path to the YAML-repository
     * @var string
     */
    protected $repositoryPath;

    /**
     * All entities currently loaded/attached to the repository
     * @var array
     */
    protected $entities;

    /**
     * All entities currently added to the repository
     * @var array
     */
    protected $addedEntities = array();

    /**
     * All entities currently removed from the repository
     * @var array
     */
    protected $removedEntities = array();

    /**
     * Auto-increment value used for the primary identifier
     * when adding a new entity to the repository
     * @var integer
     */
    protected $entityAutoIncrement;

    /**
     * Primary identifier attribute of the entity model
     * @var string
     */
    protected $entityIdentifier;
    
    /**
     * Definition of the unique key attributes of the entity model
     * @var array
     */
    protected $entityUniqueKeys;

    /**
     * Initialize a YAML-Repository.
     * Load the entities stored in the file specified by $repositoryPath.
     * @param   string  $repositoryPath Absolute path in the file system to the YAML-repository
     */
    public function __construct($repositoryPath) {
        if (empty($repositoryPath)) {
            throw new YamlRepositoryException('No repository specified!');
        }

        $this->repositoryPath = $repositoryPath;
        $this->load();
    }

    /**
     * Reset the repository. Unload all data.
     * @access private
     */
    private function reset() {
        $this->entities = array();
        $this->entityAutoIncrement = 1;
        $this->entityIdentifier = null;
        $this->entityUniqueKeys = array();
    }

    /**
     * Load repository from file system specified by $this->repositoryPath
     * @access private
     */
    private function load() {
        $this->reset();

        $repository = \Cx\Core_Modules\Listing\Model\Entity\DataSet::load($this->repositoryPath);
        if (!$repository->entryExists('meta')) {
            throw new YamlRepositoryException("Unable to load repository $this->repositoryPath. Repository is missing meta description!");
        }

        $meta = $repository->getEntry('meta');
        $this->entityAutoIncrement = $meta['auto_increment'];
        $this->entityIdentifier = $meta['identifier'];
        $this->entityUniqueKeys = $meta['unique_keys'];

        if (!$repository->entryExists('data')) {
            \DBG::msg("YamlRepository: Empty repository $this->repositoryPath loaded.");
            return;
        }

        $data = $repository->getEntry('data');
        foreach ($data as $entity) {
            if (!($entity instanceof \Cx\Core\Model\Model\Entity\YamlEntity)) {
                throw new YamlRepositoryException("Unable to load repository $this->repositoryPath. Repository contains invalid entity: ".serialize($entity));
            }
            $id = $this->getIdentifierOfEntity($entity);
            $this->entities[$id] = $entity;
        }
    }

    /**
     * Return all entities from repository
     */
    public function findAll() {
        return $this->entities;
    }
    
     /**
     * Return single entity specified by primary identifier $id
     * @param   mixed   Value of primary identifier
     * @return  YamlEntity  Object from repository identified by primary identifier $id
     */
    public function find($id) {
        if (isset($this->entities[$id])) {
            return $this->entities[$id];
        }

        throw new YamlRepositoryException("No entity found by $this->entityIdentifier = $id!");
    }

    /**
     * Return an array of entities matching the criterias specified by $criteria.
     * @param   array   $criteria Arguments to filter the returned entities by
     * @return  array   Array containing the objects from the repository matching the filter criterias
     */
    public function findBy(array $criteria = array()) {
        $matchedEntities = array();

        foreach ($this->entities as $entity) {
            foreach ($criteria as $key => $value) {
                if (call_user_func(array($entity, "get".ucfirst($key))) != $value) {
                    continue 2;
                }
            }
            $matchedEntities[] = $entity;
        }

        return $matchedEntities;
    }

    /**
     * Add new entity to the repository
     * Method {@see flush()} must triggered to persist the entity in the repository.
     * @param   YamlEntity  Entity to add to the repository
     */
    public function add($entity) {
        if (!($entity instanceof \Cx\Core\Model\Model\Entity\YamlEntity)) {
            throw new YamlRepositoryException("Entity must be of type YamlEntity!");
        }

        // check if entity already has an ID and if so if there is already
        // an other entity present in the repository with the same ID.
        $id = $this->getIdentifierOfEntity($entity);
        if ($id && isset($this->entities[$id])) {
            throw new \YamlRepositoryException("Unable to add new entity to repository. A entity by the specified primary identifier {$this->getIdentifierOfEntity($entity)} is already present in the repository.");
        } else {
            // assign new ID to entity
            call_user_func(array($entity, "set".ucfirst($this->entityIdentifier)), $this->getNewEntityIdentifier());
        }
        $this->validate($entity);
        $this->entities[$this->getIdentifierOfEntity($entity)] = $entity;
        if (!$entity->isVirtual()) {
            $this->addedEntities[] = $entity;
            //\Env::get('cx')->getEvents()->triggerEvent('model/prePersist', array($entity));
            \Env::get('cx')->getEvents()->triggerEvent('model/prePersist', array(new \Doctrine\ORM\Event\LifecycleEventArgs($entity, \Env::get('em'))));
        }
    }

    /**
     * Remove entity from repository.
     * Entity will not be removed until the method {@see flush()} has been triggered.
     * @param   YamlEntity $entity The entity to remove from the repository.
     */
    public function remove($entity) {
        unset($this->entities[$this->getIdentifierOfEntity($entity)]);
        $this->removedEntities[] = $entity;
        //\Env::get('cx')->getEvents()->triggerEvent('model/preRemove', array($entity));
        \Env::get('cx')->getEvents()->triggerEvent('model/preRemove', array(new \Doctrine\ORM\Event\LifecycleEventArgs($entity, \Env::get('em'))));
    }

    /**
     * For triggering the preUpdate, we have to get the last updated entry
     * 
     * @return boolean
     */
    private function getLastUpdatedEntry() {
        $updateEntries  = $this->entities;
        $this->entities = array();
        $this->load();
        foreach ($this->entities As $id => $objDomain) {
            if (isset($updateEntries[$id])) {
                if ($objDomain->getName() != $updateEntries[$id]->getName()) {
                    $this->entities = $updateEntries;
                    return array($objDomain, $updateEntries[$id]);
                }
            }
        }
        $this->entities = $updateEntries;
        
        return false;
    }
    
    /**
     * Flush the current state of the repository into the file system.
     * @todo    Make thread safe
     */
    public function flush() {
        //for triggering the preUpdate
        list($oldEntry, $newEntry) = $this->getLastUpdatedEntry();
        if (!empty($oldEntry) && ($oldEntry instanceof \Cx\Core\Net\Model\Entity\Domain)) {
            \Env::get('cx')->getEvents()->triggerEvent('model/preUpdate', array(new \Doctrine\ORM\Event\LifecycleEventArgs($oldEntry, \Env::get('em'))));
        }
        
        $entitiesToPersist = array();
        foreach ($this->entities as $entity) {
            // Validation must be done before checking for virtual entities.
            // As even virtual entities must comply with the unique-key restrictions.
            $this->validate($entity);
            if ($entity->isVirtual()) {
                continue;
            }
            $entitiesToPersist[] = $entity;
        }
        $dataSet = new \Cx\Core_Modules\Listing\Model\Entity\DataSet();
        $dataSet->add('data', $entitiesToPersist);
        $dataSet->add('meta', $this->getMetaDefinition());
        $dataSet->save($this->repositoryPath);
        
        if (!empty($newEntry) && ($newEntry instanceof \Cx\Core\Net\Model\Entity\Domain)) {
            \Env::get('cx')->getEvents()->triggerEvent('model/postUpdate', array(new \Doctrine\ORM\Event\LifecycleEventArgs($newEntry, \Env::get('em'))));
        }
        
        foreach ($this->removedEntities as $entity) {
            //\Env::get('cx')->getEvents()->triggerEvent('model/postRemove', array($entity));
            \Env::get('cx')->getEvents()->triggerEvent('model/postRemove', array(new \Doctrine\ORM\Event\LifecycleEventArgs($entity, \Env::get('em'))));
        }

        foreach ($this->addedEntities as $entity) {
            //\Env::get('cx')->getEvents()->triggerEvent('model/postPersist', array($entity));
            \Env::get('cx')->getEvents()->triggerEvent('model/postPersist', array(new \Doctrine\ORM\Event\LifecycleEventArgs($entity, \Env::get('em'))));
        }
        //truncate the variables
        if (!empty($this->addedEntities))
            $this->addedEntities = array();
        if (!empty($this->removedEntities))
            $this->removedEntities = array();
    }

    /**
     * Generate meta definition to be stored in the repository
     * @access private
     * @return array    Meta definition
     */
    private function getMetaDefinition() {
        return array(
            'auto_increment'    => $this->entityAutoIncrement,
            'identifier'        => $this->entityIdentifier,
            'unique_keys'       => $this->entityUniqueKeys,
        );
    }

    /**
     * Validate the entity to comply with any unique-key constraints
     * @param   YamlEntity The entity to validate
     * @access private
     */
    private function validate($entity) {
        foreach ($this->entityUniqueKeys as $uniqueKey) {
            $value = call_user_func(array($entity, "get".ucfirst($uniqueKey)));
            $matchedEntities = $this->findBy(array($uniqueKey => $value));

            // if no other entities use the same uniqueKey then we're good
            if (!count($matchedEntities)) {
                continue;
            }
            if (count($matchedEntities) == 1) {
                // it's fine in case the entity with the same uniqueKey is actually the entity we are validating
                if ($this->getIdentifierOfEntity(current($matchedEntities)) == $this->getIdentifierOfEntity($entity)) {
                    continue;
                }
            }

            throw new YamlRepositoryException("Attribute $uniqueKey ($value) of entity #{$this->getIdentifierOfEntity($entity)} must be unique!");
        }
    }

    /**
     * Generate and get a new primary identifier
     * @return integer New primary identifier
     * @access private
     */
    private function getNewEntityIdentifier() {
        return ++$this->entityAutoIncrement;
    }

    /**
     * Get the primary identifier of an entity
     * @param   YamlEntity $entity The entity of which to get the primary identifier from
     * @access private
     * @return  integer The primary identifier of the supplied entity
     */
    private function getIdentifierOfEntity($entity) {
        return call_user_func(array($entity, "get".ucfirst($this->entityIdentifier)));
    }
}

