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

class OrderRepositoryException extends \Exception {}

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
    
    /**
     * Create a new Order 
     * 
     * @param integer $productId            productId
     * @param integer $contactId            contactId
     * @param string  $transactionReference transactionReference
     * @param array   $subscriptionOptions  subscriptionOptions
     * 
     * @return boolean
     * @throws OrderRepositoryException
     */
    public function createOrder($productId, $contactId, $transactionReference, $subscriptionOptions = array()) {
        if (\FWValidator::isEmpty($productId) 
                || \FWValidator::isEmpty($contactId) 
                || \FWValidator::isEmpty($subscriptionOptions) 
                || \FWValidator::isEmpty($transactionReference)) {
            
            return;
        }
        
        try {
            $order = new \Cx\Modules\Order\Model\Entity\Order();
            $order->setContactId($contactId);
            $productRepository = \Env::get('em')->getRepository('Cx\Modules\Pim\Model\Entity\Product');
            $product = $productRepository->findOneBy(array('id' => $productId));
            
            //create subscription
            $subscription = $order->createSubscription($product, $subscriptionOptions);
            $order->billSubscriptions();
            $invoices = $order->getInvoices();
            
            if (!empty($invoices)) {
                $paymentRepo = \Env::get('em')->getRepository('\Cx\Modules\Order\Model\Entity\Payment');
                foreach ($invoices as $invoice) {
                    if (!$invoice->getPaid()) {
                        $payment     = $paymentRepo->findOneByCriteria(array('amount' => $invoice->getAmount(), 'transactionReference' => $transactionReference, 'invoice' => null));
                        if ($payment) {
                            //set subscription-id to Subscription::$externalSubscriptionId
                            if ($subscription) {
                                $referenceArry = explode('-', $payment->getTransactionReference());
                                if (isset($referenceArry[2]) && !empty($referenceArry[2])) {
                                    $subscription->setExternalSubscriptionId($referenceArry[2]);
                                }
                            }
                            $invoice->addPayment($payment);
                            $payment->setInvoice($invoice);
                            \Env::get('em')->persist($invoice);
                            \Env::get('em')->persist($payment);
                            break;
                        }
                    }
                }
            }

            \Env::get('em')->persist($order);
            \Env::get('em')->flush();
            
            return $order;
        } catch (\Exception $e) {
            throw new OrderRepositoryException($e->getMessage());
        }
    }
}
