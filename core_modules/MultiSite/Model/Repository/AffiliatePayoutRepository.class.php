<?php

/**
 * Class AffiliatePayoutRepository
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      ss4u <ss4ugroup@gmail.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */

namespace Cx\Core_Modules\MultiSite\Model\Repository;

/**
 * Class AffiliatePayoutRepository
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      ss4u <ss4ugroup@gmail.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */

class AffiliatePayoutRepository extends \Doctrine\ORM\EntityRepository {
    
    /**
     * Get the total payout amount by user
     * 
     * @param object $user User object
     * 
     * @return decimal
     */
    public function getTotalAmountByUser($user) {
        
        if (!$user || (!($user instanceof \User) && !($user instanceof \Cx\Core\User\Model\Entity\User))) {
            return 0;
        }
        $userId = $user->getId();
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('sum(ap.amount)')
           ->from('\Cx\Core_Modules\MultiSite\Model\Entity\AffiliatePayout', 'ap')
           ->leftJoin('ap.referee', 'r')
           ->groupBy('r.id')->having('r.id = :userId')->setParameter('userId' , $userId);
        $result = $qb->getQuery()->getResult();
        return !empty($result) ? current(current($result)) : 0;
    }
}