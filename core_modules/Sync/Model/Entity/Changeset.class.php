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
 * Host
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  coremodule_sync
 */

namespace Cx\Core_Modules\Sync\Model\Entity;

/**
 * Host
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  coremodule_sync
 */
class Changeset extends \Cx\Model\Base\EntityBase
{
    protected $calculatedEntities = array();
    
    protected $spool = array();
    
    public function __construct($entityIndexData, $entityClassName, $entity, $eventType, $sync) {
        $this->calculate($entityIndexData, $entityClassName, $entity, $eventType, $sync);
    }
    
    public function calculate($entityIndexData, $entityClassName, $entity, $eventType, $sync) {
        // do not add the same entity twice in the same changeset
        $entityIdentifier = $entityClassName . implode('/', $entityIndexData);
        if (in_array($entityIdentifier, $this->calculatedEntities)) {
            return;
        }
        $this->calculatedEntities[] = $entityIdentifier;
        
        // calculate changeset!
        // foreach n:1 relation:
        $em = $this->cx->getDb()->getEntityManager();
        $subEventType = 'put';
        foreach ($this->getComponentController()->getDependendingFields($entity) as $field=>$fieldType) {
            // recurse
            //echo 'Calculating relations for ' . $entityClassName . '.' . $field . '<br />';
            $this->calculateRelation($field, $fieldType, $entity, $em, $eventType, $subEventType, $sync);
        }
        
        // If doctrine supplied a proxy, there was no change to this entity
        if (substr(get_class($entity), -5) == 'Proxy') {
            //return;
        }
        
        // add to pre-spool
        $this->spool[] = array(
            'sync' => $sync,
            'eventType' => $eventType,
            'entityIndexData' => $entityIndexData,
            'entity' => $entity,
            'entityIdentifier' => $entityIdentifier,
        );
        
        // foreach cascade (delete/persist)
        if ($eventType == 'delete') {
            $subEventType = 'delete';
        }
        foreach ($this->getComponentController()->getCascadingFields($entity, $eventType) as $field=>$fieldType) {
            // recurse
            //echo 'Calculating cascades for ' . $entityClassName . '.' . $field . '<br />';
            $this->calculateRelation($field, $fieldType, $entity, $em, $eventType, $subEventType, $sync);
        }
        
        //$this->simplify();
    }
    
    protected function calculateRelation($field, $fieldType, $entity, $em, $eventType, $subEventType, $sync) {
        // @todo: this does not take relation config into account!
        $fieldGetMethodName = 'get'.preg_replace('/_([a-z])/', '\1', ucfirst($field));
        $foreignEntities = $entity->$fieldGetMethodName();
        if (get_class($foreignEntities) != 'Doctrine\ORM\PersistentCollection') {
            $foreignEntities = array($foreignEntities);
        }
        foreach ($foreignEntities as $foreignEntity) {
            $foreignEntityIndexData = $this->getComponentController()->getEntityIndexData($foreignEntity);
            $foreignEntityClassName = get_class($foreignEntity);
            if ($foreignEntityClassName == 'Doctrine\ORM\PersistentCollection') {
                $foreignEntityClassName = $foreignEntity->getTypeClass()->name;
            }
            $foreignEntityIdentifier = $foreignEntityClassName . implode('/', $foreignEntityIndexData);
            //echo 'Calculating foreign entity ' . $foreignEntityIdentifier . '<br />';
            if (in_array($foreignEntityIdentifier, $this->calculatedEntities)) {
                //echo 'Entity already spooled<br />';
                continue;
            }
            
            // @todo: this might fail in real-life since there could be more than one DA per DS and Sy per DA
            $dataSourceRepo = $em->getRepository('Cx\Core\DataSource\Model\Entity\DataSource');
            $dataAccessRepo = $em->getRepository('Cx\Core_Modules\DataAccess\Model\Entity\DataAccess');
            $syncRepo = $em->getRepository($this->getComponentController()->getNamespace() . '\Model\Entity\Sync');
            $foreignDataSource = $dataSourceRepo->findOneBy(array(
                'identifier' => $fieldType,
            ));
            
            $foreignSync = $foreignDataSource->getDataAccesses()->first()->getSyncs()->first();
            $foreignSync->setVirtual(true); // does not seem to work anymore!
            $foreignSync->setTempActive(true); // that's why we introduced "tempActive"
            
            if ($eventType == 'delete' && $subEventType != 'delete') {
                //echo 'Entity not configured to cascade<br />';
                continue; // if cascading isn't configured
            }
            $this->calculate($foreignEntityIndexData, $foreignEntityClassName, $foreignEntity, $subEventType, $foreignSync);
        }
    }
    
    public function getChanges() {
        return $this->spool;
    }
}

