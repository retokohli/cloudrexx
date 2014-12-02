<?php

/**
 * Class OrderRepository
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_order
 */

namespace Cx\Modules\Order\Model\Repository;

/**
 * Class OrderRepository
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_order
 */
class OrderRepository extends \Doctrine\ORM\EntityRepository {
    /**
     * Get the orders based on the CRM contact, status(valid site or expired site), active site($excludeProduct) and trial site($includeProduct)
     * 
     * @param integer $contactId
     * @param string  $status
     * @param array   $excludeProduct
     * @param array   $includeProduct
     * 
     * @return object
     */
    public function getOrdersByCriteria($contactId, $status, $excludeProduct, $includeProduct) {
        //must check crm contact id present or not
        if (empty($contactId)) {
            return;
        }
        
        $now = new \DateTime('now');
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('o')
                ->from('\Cx\Modules\Order\Model\Entity\Order', 'o')
                ->leftJoin('o.subscriptions', 's')
                ->leftJoin('s.product', 'p')
                ->where('o.contactId = :contactId');
        if ($status == 'valid') {
            $qb->andWhere("s.expirationDate > '" . $now->format("Y-m-d H:i:s") . "'");
            if (!empty($excludeProduct)) {
                $qb->andWhere($qb->expr()->notIn('p.id', $excludeProduct));
            } elseif (!empty($includeProduct)) {
                $qb->andWhere($qb->expr()->in('p.id', $includeProduct));
            }
        } elseif ($status == 'expired') {
            $qb->andWhere("s.expirationDate <= '" . $now->format("Y-m-d H:i:s") . "'");
        }
        $qb->setParameter('contactId', $contactId);

        return $qb->getQuery()->getResult();
    }
    
    /**
     * Check the order count by the $crmId
     * 
     * @param integer $crmId Crm User Id
     * 
     * @return boolean
     */
    public function hasOrderByCrmId($crmId = 0) {
        if (empty($crmId)) {
            return;
        }
        
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('count(o.id)')
           ->from('\Cx\Modules\Order\Model\Entity\Order', 'o')
           ->where('o.contactId = :contactId');     
        $qb->setParameter('contactId', $crmId);
        
        return $qb->getQuery()->getSingleScalarResult();
    }
}
