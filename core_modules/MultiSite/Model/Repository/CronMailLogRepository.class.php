<?php

/**
 * Class CronMailLogRepository
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      ss4u <ss4ugroup@gmail.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */

namespace Cx\Core_Modules\MultiSite\Model\Repository;

/**
 * Class CronMailLogRepository
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      ss4u <ss4ugroup@gmail.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class CronMailLogRepository extends \Doctrine\ORM\EntityRepository {
    
    /**
     * Get the CronMailLog by Criteria
     * 
     * @param array $criteria
     * @return object
     */
    public function getOneCronMailLogByCriteria(array $criteria) {
        if (empty($criteria)) {
            return;
        }
        
        try {
            $qb = $this->getEntityManager()->createQueryBuilder();
            $qb->select('log')
                    ->from('\Cx\Core_Modules\MultiSite\Model\Entity\CronMailLog', 'log')
                    ->leftJoin('log.cronMail', 'cron');

            if (!empty($criteria['id'])) {
                $qb->where('cron.id = ?1')->setParameter(1, $criteria['id']);
            }
            $i = 2;
            foreach ($criteria as $key => $value) {
                if (empty($value) || $key === 'id') {
                    continue;
                }

                if ($i == 2) {
                    $method = !empty($criteria['id']) ? 'andWhere' : 'where';
                    $qb->$method('log.' . $key . ' = ?' . $i)->setParameter($i, $value);
                } else {
                    $qb->andWhere('log.' . $key . ' = ?' . $i)->setParameter($i, $value);
                }
                $i++;
            }
            $qb->getDql();
            $logs = $qb->getQuery()->getResult();
        } catch (\Exception $e) {
            $logs = array();
        }
        
        return isset($logs[0]) ? $logs[0] : null;
    }
}
