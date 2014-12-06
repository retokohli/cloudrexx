<?php

/**
 * Class PaymentRepository
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_order
 */

namespace Cx\Modules\Order\Model\Repository;

/**
 * Class PaymentRepository
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_order
 */
class PaymentRepository extends \Doctrine\ORM\EntityRepository {
    
    /**
     * Get the payment by criteria
     * 
     * @param string $criteria
     * 
     * @return object
     */
    public function findOneByCriteria($criteria) {
        if (empty($criteria)) {
            return;
        }
        
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('p')
           ->from('\Cx\Modules\Order\Model\Entity\Payment', 'p');
        
        $i = 1;
        $term = '';
        $operator = '';
        foreach ($criteria as $key => $value) {
            if (empty($value)) {
                continue;
            }
            $operator = ($key == 'transactionReference') ? ' LIKE ?' : ($key == 'invoice' ? ' IS ?' : ' = ?');
            $term     = ($key == 'transactionReference') ? $value . '%' : $value;
            if ($i == 1) {
                $qb->where('p.' . $key . $operator . $i)->setParameter($i, $term);
            } else {
                $qb->andWhere('p.' . $key . $operator . $i)->setParameter($i, $term);
            }
            $i++;
        }
        
        return current($qb->getQuery()->getResult());
    }
}
