<?php

/**
 * SubscriptionController
 *
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_order
 */

namespace Cx\Modules\Order\Controller;

/**
 * 
 * SubscriptionController for displaying all the subscriptions.
 *
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_order
 */
class SubscriptionController extends \Cx\Core\Core\Model\Entity\Controller {
    
    /**
     * Em instance
     * @var \Doctrine\ORM\EntityManager em
     */
    protected $em;
    
    /**
     * Sigma template instance
     * @var Cx\Core\Html\Sigma  $template
     */
    protected $template;
    
    /**
     * OrderRepository instance 
     * @var \Cx\Modules\Order\Model\Repository\SubscriptionRepository $subscriptionRepo
     */
    protected $subscriptionRepo;
    
    /**
     * Controller for the Backend subscription views
     * 
     * @param \Cx\Core\Core\Model\Entity\SystemComponentController $systemComponentController the system component controller object
     * @param \Cx\Core\Core\Controller\Cx                          $cx                        the cx object
     * @param \Cx\Core\Html\Sigma                                  $template                  the template object
     * @param string                                               $submenu                   the submenu name
     */
    public function __construct(\Cx\Core\Core\Model\Entity\SystemComponentController $systemComponentController, \Cx\Core\Core\Controller\Cx $cx, \Cx\Core\Html\Sigma $template, $submenu = null) {
        parent::__construct($systemComponentController, $cx);
        
        $this->template          = $template;
        $this->em                = $this->cx->getDb()->getEntityManager();
        $this->subscriptionRepo   = $this->em->getRepository('Cx\Modules\Order\Model\Entity\Subscription');
        
        $this->showSubscriptions();
    }

    public function showSubscriptions() 
    {
        global $_ARRAYLANG;
        
        $subscriptions = $this->subscriptionRepo->findAll();
        if (empty($subscriptions)) {
            $subscriptions = new \Cx\Modules\Order\Model\Entity\Subscription();
        }
        $view = new \Cx\Core\Html\Controller\ViewGenerator($subscriptions, array(
            'header'    => $_ARRAYLANG['TXT_MODULE_ORDER_ACT_SUBSCRIPTION'],
            'functions' => array(
                'add'       => true,
                'edit'      => true,
                'delete'    => true,
                'sorting'   => true,
                'paging'    => true,
                'filtering' => false,
                ),
            'fields' => array(
                'productEntityId' => array(
                    'table' => array(
                        'parse' => function($value, $rowData) {
                            $subscription  = $this->subscriptionRepo->findOneBy(array('id' => $rowData['id']));
                            $productEntity = $subscription->getProductEntity();
                            if(!$productEntity) {
                                return;
                            }
                            return $productEntity;
                        }
                    )
                ),
                'product'  => array(
                    'table' => array(
                        'parse' => function($value, $rowData) {
                            $subscription  = $this->subscriptionRepo->findOneBy(array('id' => $rowData['id']));
                            $product       = $subscription->getProduct();
                            if (!$product) {
                                return;
                            }
                            return $product->getName();
                        }
                    )
                ),
                
            ),
            ));
        $this->template->setVariable('SUBSCRIPTIONS_CONTENT', $view->render());
    }
}
