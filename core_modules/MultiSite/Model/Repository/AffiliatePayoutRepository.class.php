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
     * Get the total amount by user
     * 
     * @return decimal
     */
    public function getTotalAmountByUser() {
        $userId = \FWUser::getFWUserObject()->objUser->getId();
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('sum(ap.amount)')
           ->from('\Cx\Core_Modules\MultiSite\Model\Entity\AffiliatePayout', 'ap')
           ->leftJoin('ap.referee', 'r')
           ->groupBy('r.id')->having('r.id = :userId')->setParameter('userId' , $userId);
        $result = $qb->getQuery()->getResult();
        return !empty($result) ? current(current($result)) : 0;
    }
}