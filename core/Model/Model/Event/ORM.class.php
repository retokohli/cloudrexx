<?php

/**
 * Wrapper class for the Gedmo\Loggable\Mapping\Event\Adapter\ORM
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      ss4u <ss4u.comvation@gmail.com>
 * @version     3.1.2
 * @package     contrexx
 * @subpackage  core 
 */

namespace Cx\Core\Model\Model\Event;

use Gedmo\Mapping\Event\Adapter\ORM as BaseAdapterORM;
use Gedmo\Loggable\Mapping\Event\LoggableAdapter;

/**
 * Wrapper class for the Gedmo\Loggable\Mapping\Event\Adapter\ORM
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      ss4u <ss4u.comvation@gmail.com>
 * @version     $Id:    Exp $
 * @package     contrexx
 * @subpackage  core
 */
final class ORM extends BaseAdapterORM implements LoggableAdapter
{
    /**
     * {@inheritDoc}
     */
    public function getDefaultLogEntryClass()
    {
        return 'Gedmo\\Loggable\\Entity\\LogEntry';
    }

    /**
     * {@inheritDoc}
     */    
    public function getNewVersion($meta, $object) {
        
        $em = $this->getObjectManager();
        $objectMeta = $em->getClassMetadata(get_class($object));
        $identifierField = $this->getSingleIdentifierFieldName($objectMeta);
        $objectId = $objectMeta->getReflectionProperty($identifierField)->getValue($object);

        $dql = "SELECT MAX(log.version) FROM {$meta->name} log";
        $dql .= " WHERE log.objectId = :objectId";
        $dql .= " AND log.objectClass = :objectClass";

        $q = $em->createQuery($dql);
        $q->setParameters(array(
            'objectId' => $objectId,
            'objectClass' => $objectMeta->name
        ));
        $q->useResultCache(false);
        
        return $q->getSingleScalarResult() + 1;
        
    }
}
