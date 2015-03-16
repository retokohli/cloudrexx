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
                    $options = array('ids' => $ids, 'entityClass' => $product->getEntityClass());
                }
            }
            
            if (!empty($filter['filterProduct'])) {
                $options['filterProduct']   = $filter['filterProduct'];
            }
            if (!empty($filter['filterState'])) {
                $options['filterState']   = $filter['filterState'];
            }
            $subscriptions = array_merge($subscriptions, $this->getSubscriptionsByProductEntity($options));
        }
        
        return $subscriptions;
    }
    
    /**
     * Get the subscriptions by product entity
     * 
     * @param array $criteria
     * 
     * @return array
     */
    function getSubscriptionsByProductEntity($criteria) {
        if (empty($criteria)) {
            return array();
        }

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select('s')
            ->from('\Cx\Modules\Order\Model\Entity\Subscription', 's')
            ->leftJoin('s.product', 'p');
                
        $criteria['ids']           ? $qb->where($qb->expr()->in('s.productEntityId', $criteria['ids'])) 
                                   : '';
        $criteria['entityClass']   ? $qb->andWhere('p.entityClass = :entityClass')
                                        ->setParameter('entityClass', $criteria['entityClass'])
                                   : '';
        $criteria['filterProduct'] ? $qb->andWhere($qb->expr()->in('p.id', $criteria['filterProduct'])) 
                                   : '';
        $criteria['filterState']   ? $qb->andWhere($qb->expr()->in('s.state', $criteria['filterState'])) 
                                   : '';
        $subscriptions = $qb->getQuery()->getResult();
        
        return !empty($subscriptions) ? $subscriptions : array();
    }
    /**
     * Get all subscriptions ordered by ID in descending order.
     * 
     * @return array
     */
    public function getAllByDesc() {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('s')
           ->from('\Cx\Modules\Order\Model\Entity\Subscription', 's')
           ->orderBy('s.id', 'DESC');
        
        return $qb->getQuery()->getResult();
    }
}
