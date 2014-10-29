<?php

/**
 * Wrapper class for Doctrine Entity Manager
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      ss4u <ss4u.comvation@gmail.com>
 * @version     3.1.2
 * @package     contrexx
 * @subpackage  core
 */

namespace Cx\Core\Model\Controller;
        
/**
 * Wrapper class for Doctrine Entity Manager
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      ss4u <ss4u.comvation@gmail.com>
 * @version     $Id:    Exp $
 * @package     contrexx
 * @subpackage  core 
 */
class EntityManager extends \Doctrine\ORM\EntityManager {
    
    /**
     * {@inheritdoc}
     */
    public function createQuery($dql = "")
    {
        $query = new \Doctrine\ORM\Query($this);

        if (strpos($dql, 'SELECT') !== false) {
            $query->useResultCache(true);
        }

        if ( ! empty($dql)) {
            $query->setDql($dql);
        }

        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public static function create($conn, \Doctrine\ORM\Configuration $config, \Doctrine\Common\EventManager $eventManager = null)
    {
        if (!$config->getMetadataDriverImpl()) {
            throw \Doctrine\ORM\ORMException::missingMappingDriverImpl();
        }

        if (is_array($conn)) {
            $conn = \Doctrine\DBAL\DriverManager::getConnection($conn, $config, ($eventManager ?: new EventManager()));
        } else if ($conn instanceof Connection) {
            if ($eventManager !== null && $conn->getEventManager() !== $eventManager) {
                 throw \Doctrine\ORM\ORMException::mismatchedEventManager();
            }
        } else {
            throw new \InvalidArgumentException("Invalid argument: " . $conn);
        }

        return new EntityManager($conn, $config, $conn->getEventManager());
    }
}
