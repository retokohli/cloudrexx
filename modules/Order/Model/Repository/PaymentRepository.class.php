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
        foreach ($criteria as $key => $value) {
            switch ($key) {
                case 'transactionReference':
                    $operator = ' LIKE ?';
                    $term = $value . '%';
                    break;

                default:
                    $operator = ' = ?';
                    $term = $value;
                    break;
            }

            if ($i == 1) {
                if (is_null($value)) {
                    $qb->where("p.$key IS NULL");
                } else {
                    $qb->where('p.' . $key . $operator . $i)->setParameter($i, $term);
                }
            } else {
                if (is_null($value)) {
                    $qb->andWhere("p.$key IS NULL");
                } else {
                    $qb->andWhere('p.' . $key . $operator . $i)->setParameter($i, $term);
                }
            }
            $i++;
        }
        
        $result = $qb->getQuery()->getResult();
        if (!$result) {
            return;
        }

        return current($result);
    }
}
