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
     * To get all the past cancelled subscriptions.
     * 
     * @return array
     */
    function getExpiredCancelledSubscriptions() 
    {
        $now = new \DateTime('now');
        $qb  = \Env::get('em')->createQueryBuilder();
        $qb->select('s')
                ->from('\Cx\Modules\Order\Model\Entity\Subscription', 's')
                ->where('s.state = :state')
                ->andWhere('s.expirationDate <= :expirationDate')
                ->setParameters(array(
                    'state' => \Cx\Modules\Order\Model\Entity\Subscription::STATE_CANCELLED,
                    'expirationDate' => $now->format("Y-m-d H:i:s")                 
                ));
        
        return $qb->getQuery()->getResult();
    }
}
