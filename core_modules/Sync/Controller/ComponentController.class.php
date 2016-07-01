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
 * Main controller for Sync
 * 
 * @copyright   Cloudrexx AG
 * @author Michael Ritter <michael.ritter@cloudrexx.com>
 * @package cloudrexx
 * @subpackage core_modules_sync
 */

namespace Cx\Core_Modules\Sync\Controller;

/**
 * Main controller for Sync
 * 
 * @copyright   Cloudrexx AG
 * @author Michael Ritter <michael.ritter@cloudrexx.com>
 * @package cloudrexx
 * @subpackage core_modules_sync
 * @todo In order to respect permission layer, this should not make direct use of doctrine (except for handling \Cx\Core_Modules\Sync\Model\Entity\... entities)
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController implements \Cx\Core\Event\Model\Entity\EventListener {
    /**
     * @var array List of supported API versions
     */
    protected $supportedApiVersions = array('v1');
    
    /**
     * @var array Two dimensional array array(<entityClassName> => <listOfSyncEntities)
     */
    protected $syncs = array();
    
    /**
     * @var boolean Allows suppress pushing to remotes
     */
    protected $doPush = true;
    
    protected $alreadySyncedEntities = array();
    
    protected $removeIds = array();
    
    protected $addIds = array();
    
    protected $spooledPushes = array();
    
    protected $unspooling = false;
    
    /**
     * Returns all Controller class names for this component (except this)
     * 
     * Be sure to return all your controller classes if you add your own
     * @return array List of Controller class names (without namespace)
     */
    public function getControllerClasses() {
        return array();
    }
    
    /**
     * Registers all needed events for all syncronized entities
     * 
     * This includes listeners for local side (all entities that are to be synced)
     * and listeners for remote side (all changes of ID that are in mapping table)
     */
    public function registerEvents() {
        $doctrineEvents = array(
            \Doctrine\ORM\Events::preRemove,
            \Doctrine\ORM\Events::postRemove,
            \Doctrine\ORM\Events::postPersist,
            \Doctrine\ORM\Events::postUpdate,
            \Doctrine\ORM\Events::postFlush,
        );
        
        $this->syncs = array();
        $em = $this->cx->getDb()->getEntityManager();
        $syncRepo = $em->getRepository($this->getNamespace() . '\Model\Entity\Sync');
        $syncs = $syncRepo->findAll();
        foreach ($syncs as $sync) {
            if (!$sync->getActive()) {
                continue;
            }
            // get entity class name
            $entityClassName = $sync->getDataAccess()->getDataSource()->getIdentifier();
            if (!isset($this->syncs[$entityClassName])) {
                $this->syncs[$entityClassName] = array();
            }
            $this->syncs[$entityClassName][] = $sync;
            /*foreach ($doctrineEvents as $doctrineEvent) {
                $this->cx->getEvents()->addModelListener(
                    $entityClassName,
                    $doctrineEvent,
                    $this
                );
            }*/
        }
        
        // listen to events of all entities (since they could be in mapping table)
        foreach ($doctrineEvents as $doctrineEvent) {
            $this->cx->getEvents()->addEventListener(
                'model/' . $doctrineEvent,
                $this
            );
        }
    }
    
    /**
     * Returns a list of command mode commands provided by this component
     * @return array List of command names
     */
    public function getCommandsForCommandMode() {
        return array(
            'sync' => new \Cx\Core_Modules\Access\Model\Entity\Permission(
                array('http', 'https'), // allowed protocols
                array(
                    'get',
                    'post',
                    'put',
                    'delete',
                    'trace',
                    'options',
                    'head',
                    'cli',
                ),   // allowed methods
                false                   // requires login
            ),
        );
    }

    /**
     * Returns the description for a command provided by this component
     * @param string $command The name of the command to fetch the description from
     * @param boolean $short Wheter to return short or long description
     * @return string Command description
     */
    public function getCommandDescription($command, $short = false) {
        switch ($command) {
            case 'sync':
                if ($short) {
                    return 'Allows synchronizing data objects using the RESTful API';
                }
                return 'Allows synchronizing data objects using the RESTful API' . "\n" .
                    'Usage: sync <apiVersion> <outputModule> <dataSource> (<elementId>) (apikey=<apiKey>) (<options>)';
            default:
                return '';
        }
    }
    
    /**
     * Execute one of the commands listed in getCommandsForCommandMode()
     *
     * <domain>(/<offset>)/api/sync/<apiVersion>/<outputModule>/<dataSource>/<parameters>[(?apikey=<apikey>(&<options>))|?<options>]
     * @see getCommandsForCommandMode()
     * @param string $command Name of command to execute
     * @param array $arguments List of arguments for the command
     * @return void
     */
    public function executeCommand($command, $arguments, $dataArguments = array()) {
        try {
            switch ($command) {
                case 'sync':
                    // do not calculate new changes made during this request
                    $this->doPush = false;
                    
                    // local side code
                    if (count($arguments) == 1 && $arguments[0] == 'push') {
                        $this->pushChanges();
                        break;
                    }
                    
                    // remote side code
                    $this->sync($command, $arguments, $dataArguments);
                    break;
            }
        } catch (\Exception $e) {
            // This should only be used if API cannot handle the request at all.
            // Most exceptions should be catched inside the API!
            http_response_code(400); // BAD REQUEST
            echo 'Exception of type "' . get_class($e) . '" with message "' . $e->getMessage() . '"';
        }
    }
    
    public function postFinalize() {
        $em = $this->cx->getDb()->getEntityManager();
        $changeRepo = $em->getRepository($this->getNamespace() . '\Model\Entity\Change');
        if (!$changeRepo->findOneBy(array())) {
            // no changes
            return;
        }
        // execute `./cx sync push` asynchronously
        $this->getComponent('Core')->execAsync(
            'sync',
            array(
                'push',
            )
        );
    }
    
    /**
     * This is the handler for remote side: handles incoming changes
     * @param string $command Always "sync"
     * @param array $arguments Arguments for API, first entry is API version
     * @param array  $dataArguments (optional) List of data arguments for the command
     * @todo pretending delete was successful does not work for other output methods than json
     * @todo update ID fields does not work as expected
     */
    public function sync($command, $arguments, $dataArguments) {
        $method = strtolower($_SERVER['REQUEST_METHOD']);
        $apiVersion = array_shift($arguments); // shift api version
        
        if (!in_array($apiVersion, $this->supportedApiVersions)) {
            throw new \BadMethodCallException('Unsupported API version: "' . $apiVersion . '"');
        }
        
        $elementId = array();
        $argumentKeys = array_keys($arguments);
        for ($i = 2; $i < count($arguments); $i++) {
            if (!is_numeric($argumentKeys[$i])) {
                break;
            }
            $elementId[] = $arguments[$i];
        }
        $this->currentInsertId = $elementId;
    
        $em = $this->cx->getDb()->getEntityManager();
        $dataAccessRepo = $em->getRepository('Cx\Core_Modules\DataAccess\Model\Entity\DataAccess');
        $dataAccess = $dataAccessRepo->findOneBy(array('name' => $arguments[1]));
        if (!$dataAccess || !$dataAccess->getDataSource()) {
            throw new \Exception('No such DataSource: ' . $name);
        }
        $entityType = $dataAccess->getDataSource()->getIdentifier();
        $referrerParts = parse_url($_SERVER['HTTP_REFERRER']);
        $foreignHost = $referrerParts['host'];
        
        if (!isset($arguments[2])) {
            // API would produce a 404 here
            throw new \BadMethodCallException('No element given');
        }
        
        $mapping = $this->getIdMapping($entityType, $foreignHost, $elementId);
        
        if (!$mapping) {
            // remote is trying to push changes to an entity that does not yet exist here
            // let's self-heal:
            if ($method == 'delete') {
                // pretend everything is ok
                $response = new \Cx\Core_Modules\DataAccess\Model\Entity\ApiResponse();
                $response->setStatus(
                    \Cx\Core_Modules\DataAccess\Model\Entity\ApiResponse::STATUS_OK
                );
                $response->setData(array());
                
                $response->send($this->getComponent('DataAccess')->getController('JsonOutput'));
                return;
            } else {
                // pretent it's a new element
                // in order to do so we need to:
                // - tell api it's a POST request
                // - make sure our eventlistener thinks it's a POST request
                $this->cx->getRequest()->setHttpRequestMethod('post');
                $method = 'post';
            }
        } else if ($method == 'post') {
            // if a new entity should be created that we already have,
            // this POST request has been made twice and can be dropped.
            // We simply pretend everything went fine:
            $response = new \Cx\Core_Modules\DataAccess\Model\Entity\ApiResponse();
            $response->setStatus(
                \Cx\Core_Modules\DataAccess\Model\Entity\ApiResponse::STATUS_OK
            );
            $entityRepository = $em->getRepository($entityType);
            $entity = $entityRepository->findOneBy($mapping->getLocalId());
            if (!$entity) {
                // we have a mapping for a non-existing entity. This shouldn't
                // happen. Let's self-heal:
                $em->remove($mapping);
                $em->flush();
            } else {
                $response->setData($this->getEntityIndexData($entity));
                $response->send($this->getComponent('DataAccess')->getController('JsonOutput'));
                return;
            }
        }
        
        // foreach ID and foreign key
        $metaData = $em->getClassMetadata($entityType);
        $primaryKeyNames = $metaData->getIdentifierFieldNames();
        $foreignId = array();
        foreach ($primaryKeyNames as $fieldName) {
            $foreignId[] = $dataArguments[$fieldName];
        }
        $primaryKeyColumnNames = array();
        foreach ($primaryKeyNames as $fieldName) {
            $this->replaceKey($foreignHost, $entityType, $fieldName, $dataArguments, $foreignId);
            $fieldMapping = $metaData->getFieldMapping($fieldName);
            $primaryKeyColumnNames[$fieldMapping['columnName']] = $fieldName;
        }
        $associationMappings = $metaData->getAssociationMappings();
        foreach ($associationMappings as $fieldName => $associationMapping) {
            if (!isset($associationMapping['targetEntity'])) {
                continue;
            }
            $replacement = $this->replaceKey($foreignHost, $associationMapping['targetEntity'], $fieldName, $dataArguments);
            
            $joinColumn = current($associationMapping['joinColumns']);
            if ($replacement && isset($primaryKeyColumnNames[$joinColumn['name']])) {
                $dataArguments[$primaryKeyColumnNames[$joinColumn['name']]] = $replacement;
            }
        }
        $i = 2;
        foreach ($primaryKeyNames as $fieldName) {
            $arguments[$i] = $dataArguments[$fieldName];
            $i++;
        }
        if (isset($dataArguments['id'])) {
            unset($dataArguments['id']);
        }
        
        // Route to API
        $this->getComponent('DataAccess')->executeCommand(
            $apiVersion,
            $arguments,
            $dataArguments
        );
    }
    
    protected function replaceKey($foreignHost, $entityType, $fieldName, &$fieldData, $foreignId = array()) {
        if (!count($foreignId)) {
            $foreignId = array($fieldData[$fieldName]);
        }
        
        $em = $this->cx->getDb()->getEntityManager();
        
        $mapping = $this->getIdMapping($entityType, $foreignHost, $foreignId);

        // if yes:
        //if (count($mapping) && current($mapping)) {
        if ($mapping) {
            // replace ID by our ID
            //$localId = current($mapping)->getLocalId();
            $localId = $mapping->getLocalId();
            $newValue = isset($localId[$fieldName]) ? $localId[$fieldName] : current($localId);
            $fieldData[$fieldName] = $newValue;
            return $newValue;
            
        // if no:
        } else {
            // if auto-increment (assuming all 'id' fields are auto-incremented)
            if ($fieldName == 'id') {
                unset($fieldData[$fieldName]);
            }
            // else
                // use provided key -> do nothing
        }
    }
    
    /**
     * Pushes changes to remote (local side) and updates mapping table (remote side)
     * @param string $eventName Name of the triggered event
     * @param object $eventArgs Doctrine event args
     * @todo update ID fields does not work as expected
     */
    public function onEvent($eventName, array $eventArgs) {
        $em = $this->cx->getDb()->getEntityManager();
        if (substr($eventName, 6) != \Doctrine\ORM\Events::postFlush) {
            try {
                $dlea = current($eventArgs);
                $entity = $dlea->getEntity();
                $entityClassName = get_class($entity);
                $entityIndexData = $this->getEntityIndexData($entity);
            } catch (\Exception $e) {
                return; // temporary workaround for YAML entities
            }
            
            // We don't want to loop!
            if (
                substr($entityClassName, 0, strlen($this->getNamespace())) == $this->getNamespace() ||
                $entityClassName == 'Cx\Core_Modules\SysLog\Model\Entity\Log'
            ) {
                return;
            }
        }
        
        switch (substr($eventName, 6)) {
            // entity was dropped
            case \Doctrine\ORM\Events::preRemove:
                // remote side code
                if (!isset($this->removeIds[$entityClassName])) {
                    $this->removeIds[$entityClassName] = array();
                }
                $this->removeIds[$entityClassName][] = $entityIndexData;
                
                foreach ($this->syncs as $syncEntityClassName=>$syncs) {
                    foreach ($syncs as $sync) {
                        $sync->setOldHostEntitiesIncludingLegacy($sync->getHostEntitiesIncludingLegacy());
                    }
                }
                break;
                
            case \Doctrine\ORM\Events::postRemove:
                // local side code
                $this->spoolSync('delete', $entityClassName, $entityIndexData, $entity);
                break;
            // entity was added
            case \Doctrine\ORM\Events::postPersist:
                // remote side code
                if (!$this->doPush) {
                    if (!isset($this->addIds[$entityClassName])) {
                        $this->addIds[$entityClassName] = array();
                    }
                    $mapping = new \Cx\Core_Modules\Sync\Model\Entity\IdMapping();
                    $referrerParts = parse_url($_SERVER['HTTP_REFERRER']);
                    $mapping->setForeignHost($referrerParts['host']);
                    $mapping->setEntityType($entityClassName);
                    $mapping->setForeignId($this->currentInsertId);
                    $mapping->setLocalId($entityIndexData);
                    $this->addIds[$entityClassName][] = $mapping;
                }
                
                // local side code
                $this->spoolSync('post', $entityClassName, $entityIndexData, $entity);
                break;
            // entity was updated
            case \Doctrine\ORM\Events::postUpdate:
                // remote side code
                // @todo: set old/new ID
                /*$oldId = ??;
                $newId = ??;
                $mappings = $mappingRepo->findBy(array(
                    'entityType' => $entityClassName,
                    'localId' => $oldId,
                ));
                foreach ($mappings as $mapping) {
                    $em->setLocalId($newId);
                }
                $em->flush();*/
                
                // local side code
                $this->spoolSync('put', $entityClassName, $entityIndexData, $entity);
                break;
            case \Doctrine\ORM\Events::postFlush:
                // remote side code
                foreach ($this->addIds as $entityClassName=>$addIds) {
                    foreach ($addIds as $i=>$mapping) {
                        $em->persist($mapping);
                        unset($this->addIds[$entityClassName][$i]);
                    }
                }
                foreach ($this->removeIds as $entityClassName=>$removeIds) {
                    foreach ($removeIds as $i=>$indexData) {
                        $mappings = $this->getIdMapping($entityClassName, '', '', $indexData, true);
                        foreach ($mappings as $mapping) {
                            $em->remove($mapping);
                        }
                        unset($this->removeIds[$entityClassName][$i]);
                    }
                }
                $em->flush();
                
                // local side code
                $this->unspool();
                break;
            default:
                return;
        }
    }
    
    /**
     * Spools changes to be pushed to remote on local side
     * @param string $eventType One of "post", "put", "delete"
     * @param string $entityClassName Classname of the entity to update
     * @param array $entityIndexData Field=>value-type array with primary key data
     * @param \Cx\Model\Base\EntityBase $entity Changed entity
     * @todo: Push relations first
     * @todo: This does not spool yet, instead it writes changes instantly
     */
    public function spoolSync($eventType, $entityClassName, $entityIndexData, $entity) {
        if (!isset($this->spooler)) {
            $this->spooler = new \Cx\Core_Modules\Sync\Model\Entity\Spooler();
        }
        
        // suppress push on incoming changes (allows two-way sync)
        if (!$this->doPush) {
            return;
        }
        
        // do not sync entities that are not configured to be synced
        if (!isset($this->syncs[$entityClassName])) {
            return;
        }
        
        // push changes
        // @todo: This should write to a spooling table for asynchronous sync
        $this->pushedEntities = array();
        foreach ($this->syncs[$entityClassName] as $sync) {
            $this->pushSync($sync, $eventType, $entityIndexData, $entity);
        }
    }
    
    protected function pushSync($sync, $eventType, $entityIndexData, $entity, $prevRelationConfig = null, $recursive = false) {
        $entityClassName = get_class($entity);
        if ($entityClassName == 'Doctrine\ORM\PersistentCollection') {
            $entityClassName = $entity->getTypeClass()->name;
        }
        
        $hosts = array();
        foreach ($sync->getHostEntitiesIncludingLegacy() as $hostEntity) {
            if (in_array($hostEntity['host'], $hosts)) {
                continue;
            }
            $hosts[] = $hostEntity['host'];
        }
        foreach ($sync->getOldHostEntitiesIncludingLegacy() as $hostEntity) {
            if (in_array($hostEntity['host'], $hosts)) {
                continue;
            }
            $hosts[] = $hostEntity['host'];
        }
        if (count($hosts)) {
            //echo 'Spooling sync "' . $eventType . '" for ' . $entityClassName . ' #' . implode('/', $entityIndexData) . '<br />';
        }
        foreach ($hosts as $host) {
            $host->handleModelChange($entityIndexData, $entityClassName, $entity, $eventType, $this->spooler, $sync);
        }
    }
        
    protected function unspool() {
        if ($this->unspooling || !isset($this->spooler)) {
            return;
        }
        $this->unspooling = true;
        $em = $this->cx->getDb()->getEntityManager();
        foreach ($this->spooler->getSpool() as $i=>$change) {
            $change->getHost()->addContentsToChangeset($change);
            $em->persist($change);
        }
        $em->flush();
        $this->spooler = new \Cx\Core_Modules\Sync\Model\Entity\Spooler();
        $this->unspooling = false;
    }
    
    protected function pushChanges() {
        $em = $this->cx->getDb()->getEntityManager();
        $changeRepo = $em->getRepository($this->getNamespace() . '\Model\Entity\Change');
        ob_start();
        
        echo 'Pushing ' . count($changeRepo->findAll()) . ' changes<br />';
        $i = 0;
        $severity = 'INFO';
        foreach ($changeRepo->findAll() as $change) {
            if (!$change->getHost()->sendChange($change)) {
                $severity = 'WARNING';
                break;
            }
            $i++;
            $em->remove($change);
        }
        $logContents = ob_get_contents();
        ob_end_clean();
        echo $logContents;
        
        if ($severity == 'INFO' && $i == 0) {
            return;
        }
        
        $this->cx->getEvents()->triggerEvent('SysLog/Add', array(
           'severity' => $severity,
           'message' => 'Sent ' . $i . ' change(s)',
           'data' => $logContents,
        ));
        $em->flush();
    }
    
    /**
     * @todo: This method should be in DataSource model
     */
    public function getEntityIndexData($entity) {
        $em = $this->cx->getDb()->getEntityManager();
        $entityClassName = get_class($entity);
        if (substr($entityClassName, -5, 5) == 'Proxy') {
            return $em->getUnitOfWork()->getEntityIdentifier($entity);
        }
        $entityMetaData = $em->getClassMetadata($entityClassName);
        $entityIndexData = array();
        foreach ($entityMetaData->getIdentifierFieldNames() as $field) {
            $entityIndexData[$field] = ''.$entityMetaData->getFieldValue($entity, $field);
        }
        return $entityIndexData;
    }
    
    /**
     * Returns the fields this entity depends on (relations that must be satisfied)
     * @todo: This method should be in DataSource model
     */
    public function getDependendingFields($entity) {
        $em = $this->cx->getDb()->getEntityManager();
        $entityClassName = get_class($entity);
        $entityMetaData = $em->getClassMetadata($entityClassName);
        $associationMappings = $entityMetaData->getAssociationMappings();
        $dependingFields = array();
        foreach ($associationMappings as $field => $associationMapping) {
            if (
                // @todo: we may use "TO_ONE" bitmask here (check newer doctrine versions and combined key support first)
                !in_array(
                    $associationMapping['type'],
                    array(
                        \Doctrine\ORM\Mapping\ClassMetadataInfo::ONE_TO_ONE,
                        \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_ONE,
                    )
                )
            ) {
                continue;
            }
            $dependingFields[$field] = $associationMapping['targetEntity'];
        }
        return $dependingFields;
    }
    
    /**
     * Returns the fields another entity depends on (relations that should be satisfied)
     * @todo: This method should be in DataSource model
     */
    public function getCascadingFields($entity, $eventType) {
        $em = $this->cx->getDb()->getEntityManager();
        $entityClassName = get_class($entity);
        $entityMetaData = $em->getClassMetadata($entityClassName);
        $associationMappings = $entityMetaData->getAssociationMappings();
        $dependingFields = array();
        foreach ($associationMappings as $field => $associationMapping) {
            if (
                (
                    $eventType != 'delete' &&
                    !$associationMapping['isCascadePersist']
                ) ||
                (
                    $eventType == 'delete' &&
                    !$associationMapping['isCascadeRemove']
                )
            ) {
                continue;
            }
            $dependingFields[$field] = $associationMapping['targetEntity'];
        }
        return $dependingFields;
    }
    
    public function getIdMapping($entityType, $foreignHost = '', $foreignId = '', $localId = '', $allowMultiple = false) {
        $em = $this->cx->getDb()->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('m')
            ->from($this->getNamespace() . '\Model\Entity\IdMapping', 'm')
            ->andWhere($qb->expr()->eq('m.entityType', '?1'))
            ->setParameter(1, $entityType);
        if (!empty($foreignHost)) {
            $qb->andWhere($qb->expr()->eq('m.foreignHost', '?2'))
                ->setParameter(2, $foreignHost);
        }
        if (!empty($foreignId)) {
            $qb->andWhere($qb->expr()->like('m.foreignId', $qb->expr()->literal(serialize($foreignId))));
        }
        if (!empty($localId)) {
            $qb->andWhere($qb->expr()->like('m.localId', $qb->expr()->literal(serialize($localId))));
        }
        $mapping = null;
        try {
            if ($allowMultiple) {
                $mapping = $qb->getQuery()->getResult();
            } else {
                $mapping = $qb->getQuery()->getSingleResult();
            }
        } catch (\Doctrine\ORM\NoResultException $e) {}
        return $mapping;
    }
}
