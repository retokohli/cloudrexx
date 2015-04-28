<?php

/**
 * Class AffiliateCreditRepository
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      ss4u <ss4ugroup@gmail.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */

namespace Cx\Core_Modules\MultiSite\Model\Repository;

/**
 * Class AffiliateCreditRepository
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      ss4u <ss4ugroup@gmail.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */

class AffiliateCreditRepository extends \Doctrine\ORM\EntityRepository {
    /**
     * get the sum of the Affiliate credits amount based on
     * the logged-in user, credit and payout
     * 
     * @return decimal
     */
    public function getTotalCreditsAmount() {
        $userId = \FWUser::getFWUserObject()->objUser->getId();
        
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('sum(ac.amount)')
           ->from('\Cx\Core_Modules\MultiSite\Model\Entity\AffiliateCredit', 'ac')
           ->leftJoin('ac.referee', 'r')
           ->groupBy('r.id')->having('r.id = :userId')->setParameter('userId' , $userId)
           ->andWhere('ac.credited = 1')
           ->andWhere('ac.payout is NULL');
        $result = $qb->getQuery()->getResult();
        return !empty($result) ? current(current($result)) : 0;
    }
}