<?php

/**
 * Class SubscriptionRepository
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_order
 */

namespace Cx\Modules\Order\Model\Repository;

/**
 * Class SubscriptionRepository
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_order
 */
class SubscriptionRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * To get all the past subscriptions and based on the criteria get in the arguments.
     * 
     * @return array
     */
    function getExpiredSubscriptionsByCriteria($criteria) 
    {
        $now = new \DateTime('now');
        $qb  = \Env::get('em')->createQueryBuilder();
        $qb->select('s')
                ->from('\Cx\Modules\Order\Model\Entity\Subscription', 's')
                ->where('s.state = :state')
                ->andWhere('s.expirationDate <= :expirationDate')
                ->setParameters(array(
                    'state' => $criteria,
                    'expirationDate' => $now->format("Y-m-d H:i:s")                 
                ));
        
        return $qb->getQuery()->getResult();
    }
    
    /**
     * Find the subscriptions by the filter
     * 
     * @param string $filter
     * 
     * @return array
     */
    function findSubscriptionsBySearchTerm($filter) {
        if (empty($filter)) {
            return array();
        }
        
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select('p')
            ->from('\Cx\Modules\Pim\Model\Entity\Product', 'p')
            ->groupBy('p.entityClass');
        
        $products = $qb->getQuery()->getResult();
        
        $subscriptions = array();
        foreach ($products as $product) {
            $ids  = array();
            $repo = $this->getEntityManager()->getRepository($product->getEntityClass());
            if ($repo && method_exists($repo, 'findByTerm')) {
                if (!empty($filter['term'])) {
                    $entities = $repo->findByTerm($filter['term']);
                    if (empty($entities)) {
                        continue;
                    }
                    $entityClassMetaData = $this->getEntityManager()->getClassMetadata($product->getEntityClass());
                    $primaryKeyName      = $entityClassMetaData->getSingleIdentifierFieldName();
                    $methodName          = 'get'. ucfirst($primaryKeyName);
                    foreach ($entities as $entity) {
                        $ids[] = $entity->$methodName();
                    }
                    $options = array('in' => array(array('s.productEntityId', $ids)), 'p.entityClass' => $product->getEntityClass());
                }
            }
            
            if (!empty($filter['filterProduct'])) {
                $options['in'][]   = array('p.id', $filter['filterProduct']);
            }
            if (!empty($filter['filterState'])) {
                $options['in'][]   = array('s.state', $filter['filterState']);
            }
            $subscriptions = array_merge($subscriptions, $this->getSubscriptionsByCriteria($options));
        }
        
        return $subscriptions;
    }
    
    /**
     * Get the subscriptions by criteria
     * 
     * @param array $criteria
     * 
     * @return array
     */
    function getSubscriptionsByCriteria($criteria, $order) {
        if (empty($criteria) && empty($order)) {
            return array();
        }

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select('s')
            ->from('\Cx\Modules\Order\Model\Entity\Subscription', 's')
            ->leftJoin('s.product', 'p')
            ->leftJoin('s.order', 'o');
            
        if (!empty($order)) {
            foreach ($order as $field => $type) {
                $qb->orderBy($field, $type);
            }
        }
        
        $i = 1;
        foreach ($criteria as $fieldType => $value) {
            if (method_exists($qb->expr(), $fieldType) && is_array($value)) {
                foreach ($value as $condition) {
                    $qb->andWhere(call_user_func(array($qb->expr(), $fieldType), $condition[0], $condition[1]));
                }
            } else {
                $qb->andWhere($fieldType . ' = ?' . $i)->setParameter($i, $value);
            }
            $i++;
        }
        $subscriptions = $qb->getQuery()->getResult();
        
        return !empty($subscriptions) ? $subscriptions : array();
    }
}
