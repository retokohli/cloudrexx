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
    public function __construct(\Cx\Core\Core\Model\Entity\SystemComponentController $systemComponentController, \Cx\Core\Core\Controller\Cx $cx) {
        parent::__construct($systemComponentController, $cx);
        
        $this->em                = $this->cx->getDb()->getEntityManager();
        $this->subscriptionRepo   = $this->em->getRepository('Cx\Modules\Order\Model\Entity\Subscription');
        
    }
    
    /**
     * Use this to parse your backend page
     * 
     * @param \Cx\Core\Html\Sigma $template 
     */
    public function parsePage(\Cx\Core\Html\Sigma $template) {
        $this->template = $template;
        
        $this->showSubscriptions();
    }
    
    public function showSubscriptions() 
    {
        global $_ARRAYLANG;
        
        $term          = isset($_GET['term']) ? contrexx_input2raw($_GET['term']) : '';
        $filterProduct = isset($_GET['filter_product']) 
                         ? contrexx_input2raw($_GET['filter_product']) : array();
        $filterState   = isset($_GET['filter_state']) 
                         ? contrexx_input2raw($_GET['filter_state']) : array();
        
        if (!empty($term) || !empty($filterProduct) || !empty($filterState)) {
            $filter    = array('term' => $term, 'filterProduct' => $filterProduct, 'filterState' => $filterState);
            $subscriptions = $this->subscriptionRepo->findSubscriptionsBySearchTerm($filter);
        } else {
            $subscriptions = $this->subscriptionRepo->getAllByDesc();
        }
        
        $products = \Env::get('em')->getRepository('Cx\Modules\Pim\Model\Entity\Product')->findAll();
        $this->getSearchFilterDropDown($products, $filterProduct, 'product');
        
        $subscriptionStates = array(\Cx\Modules\Order\Model\Entity\Subscription::STATE_ACTIVE, 
                                    \Cx\Modules\Order\Model\Entity\Subscription::STATE_INACTIVE, 
                                    \Cx\Modules\Order\Model\Entity\Subscription::STATE_TERMINATED, 
                                    \Cx\Modules\Order\Model\Entity\Subscription::STATE_CANCELLED
                              );
        $this->getSearchFilterDropDown($subscriptionStates, $filterState, 'state');
        
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
                'id' => array(
                  'header' => $_ARRAYLANG['TXT_MODULE_ORDER_SUBSCRIPTION_ID']  
                ),
                'subscriptionDate' => array(
                  'header' => $_ARRAYLANG['TXT_MODULE_ORDER_SUBSCRIPTION_DATE']  
                ),
                'expirationDate' => array(
                  'header' => $_ARRAYLANG['TXT_MODULE_ORDER_SUBSCRIPTION_EXPIRATION_DATE']  
                ),
                'productEntityId' => array(
                    'header' => $_ARRAYLANG['TXT_MODULE_ORDER_SUBSCRIPTION_PRODUCT_ENTITY'],
                    'table' => array(
                        'parse' => function($value, $rowData) {
                            $subscription  = $this->subscriptionRepo->findOneBy(array('id' => $rowData['id']));
                            $productEntity = $subscription->getProductEntity();
                            if(!$productEntity) {
                                return;
                            }
                            $productEditLink = $productEntity;
                            if (method_exists($productEntity, 'getEditLink')) {
                                $productEditLink = $productEntity->getEditLink();
                            }
                            
                            return $productEditLink;
                        }
                    )
                ),
                'paymentAmount' => array(
                    'header' => $_ARRAYLANG['TXT_MODULE_ORDER_SUBSCRIPTION_PAYMENT_AMOUNT'],
                    'table' => array(
                        'parse' => function($value, $rowData) {
                            $subscription    = $this->subscriptionRepo->findOneBy(array('id' => $rowData['id']));
                            $currency = '';
                            $order = $subscription->getOrder();
                            if ($order) {
                                $currency  = !\FWValidator::isEmpty($order->getCurrency()) ? $order->getCurrency() : '';
                            }
                            $paymentInterval = $subscription->getRenewalUnit();
                            return ($value) ? $value . ' ' . $currency . ' / ' . $paymentInterval : '';
                        }
                    )
                ),
                'renewalUnit' => array(
                    'header' => $_ARRAYLANG['TXT_MODULE_ORDER_SUBSCRIPTION_RENEWAL_UNIT']
                ),
                'renewalQuantifier' => array(
                    'showOverview' => false
                ),
                'renewalDate' => array(
                    'header' => $_ARRAYLANG['TXT_MODULE_ORDER_SUBSCRIPTION_RENEWAL_DATE']
                ),
                'description' => array(
                    'header' => $_ARRAYLANG['TXT_MODULE_ORDER_SUBSCRIPTION_DESCRIPTION']
                ),
                'state' => array(
                    'header' => $_ARRAYLANG['TXT_MODULE_ORDER_SUBSCRIPTION_STATE']
                ),
                'terminationDate' => array(
                    'header' => $_ARRAYLANG['TXT_MODULE_ORDER_SUBSCRIPTION_TERMI_DATE']
                ),
                'note' => array(
                    'header' => $_ARRAYLANG['TXT_MODULE_ORDER_SUBSCRIPTION_NOTE']
                ),
                'product'  => array(
                    'header' => $_ARRAYLANG['TXT_MODULE_ORDER_SUBSCRIPTION_PRODUCT'],
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
                'paymentState' => array(
                    'showOverview' => false
                ),
                'externalSubscriptionId' => array(
                    'showOverview' => false
                ),
                'order' => array(
                    'showOverview' => false
                ),
                
            ),
        ));

        $this->template->setVariable(array(
            'TXT_ORDER_SUBSCRIPTIONS_FILTER'       => $_ARRAYLANG['TXT_MODULE_ORDER_FILTER'],
            'TXT_ORDER_SUBSCRIPTIONS_SEARCH'       => $_ARRAYLANG['TXT_MODULE_ORDER_SEARCH'],
            'TXT_ORDER_SUBSCRIPTIONS_SEARCH_TERM'  => $_ARRAYLANG['TXT_MODULE_ORDER_SEARCH_TERM'],
            'ORDER_SUBSCRIPTIONS_SEARCH_VALUE'     => contrexx_raw2xhtml($term)
        ));
        if (isset($_GET['editid']) && !empty($_GET['editid']) || isset($_GET['add']) && !empty($_GET['add'])) {
            $this->template->hideBlock("subscription_filter");
        }
        $this->template->setVariable('SUBSCRIPTIONS_CONTENT', $view->render());
    }
    
    /**
     * Get search filter dropdown
     * 
     * @param mixed   $filterDropDownValues
     * @param mixed   $selected
     * @param string  $block       
     */
    public function getSearchFilterDropDown($filterDropDownValues, $selected, $block) {

        foreach ($filterDropDownValues as $filterDropDownValue) {
            $filterDropDownName = $filterDropDownValue;
            if (is_object($filterDropDownValue)) {
                $filterDropDownName  = $filterDropDownValue->getName();
                $filterDropDownValue = $filterDropDownValue->getId();
            }
            
            $selectedVal = in_array($filterDropDownValue, $selected) ? 'selected' : '';
            $this->template->setVariable(array(
                'ORDER_SUBSCRIPTION_'.strtoupper($block).'_NAME'         => contrexx_raw2xhtml($filterDropDownName),
                'ORDER_SUBSCRIPTION_'.strtoupper($block).'_VALUE'        => contrexx_raw2xhtml($filterDropDownValue),
                'ORDER_SUBSCRIPTION_'.strtoupper($block).'_SELECTED'     => $selectedVal,
            ));
            $this->template->parse('subscription_' . $block . '_filter');
        }
    }
}
