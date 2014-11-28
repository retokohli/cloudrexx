<?php

/**
 * Class Log Repository
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_syslog
 */

namespace Cx\Core_Modules\SysLog\Model\Repository;

/**
 * Class Log Repository
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_syslog
 */
class LogRepository extends \Doctrine\ORM\EntityRepository {
    
    /**
     * Find Latest Sys Log Entry by its logger
     * 
     * @param string  $logger logger
     * @param integer $offset offset value
     * @param integer $limit  limit value
     * @return array
     */
    public function findLatestLogEntryByLogger($logger, $offset = 0, $limit = 1) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        
        $qb->select('Log')
                ->from('\Cx\Core_Modules\SysLog\Model\Entity\Log', 'Log')
                ->where('Log.logger = :logger')
                ->orderBy('Log.timestamp', 'DESC')
                ->getDql();
        $qb->setParameter('logger', $logger);
        $qb->setFirstResult($offset)->setMaxResults($limit);
        
        return $qb->getQuery()->getResult();
        
    }
}

