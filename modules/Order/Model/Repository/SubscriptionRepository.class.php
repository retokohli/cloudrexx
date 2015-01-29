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
}
