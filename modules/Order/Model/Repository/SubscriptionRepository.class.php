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
     * Find the subscriptions by the search term
     * 
     * @param string $term
     * 
     * @return array
     */
    function findSubscriptionsBySearchTerm($term) {
        if (empty($term)) {
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
                $entities = $repo->findByTerm($term);
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
                $subscriptions = array_merge($subscriptions, $this->getSubscriptionsByProductEntity($options));
            }
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
            ->leftJoin('s.product', 'p')
            ->where($qb->expr()->in('s.productEntityId', $criteria['ids']))
            ->andWhere('p.entityClass = :entityClass')
            ->setParameter('entityClass', $criteria['entityClass']);
        
        $subscriptions = $qb->getQuery()->getResult();
        
        return !empty($subscriptions) ? $subscriptions : array();
    }
}
