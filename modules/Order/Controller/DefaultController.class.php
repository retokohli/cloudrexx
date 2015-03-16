<?php

/**
 * DefaultController
 *
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_order
 */

namespace Cx\Modules\Order\Controller;

/**
 * 
 * DefaultController for displaying all the orders.
 *
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_order
 */
class DefaultController extends \Cx\Core\Core\Model\Entity\Controller {
    
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
     * @var \Cx\Modules\Order\Model\Repository\OrderRepository $orderRepository
     */
    protected $orderRepository;
    
    /**
     * module name
     * @var string $moduleName
     */
    public $moduleName = 'Order';
    
    /**
     * module name for language placeholder
     * @var string $moduleNameLang
     */
    public $moduleNameLang = 'ORDER';

    /**
     * Controller for the Backend Orders views
     * 
     * @param \Cx\Core\Core\Model\Entity\SystemComponentController $systemComponentController the system component controller object
     * @param \Cx\Core\Core\Controller\Cx                          $cx                        the cx object
     * @param \Cx\Core\Html\Sigma                                  $template                  the template object
     */
    public function __construct(\Cx\Core\Core\Model\Entity\SystemComponentController $systemComponentController, \Cx\Core\Core\Controller\Cx $cx) {
        parent::__construct($systemComponentController, $cx);
        
        $this->em                = $this->cx->getDb()->getEntityManager();
        $this->orderRepository   = $this->em->getRepository('Cx\Modules\Order\Model\Entity\Order');
    }
    
    /**
     * Use this to parse your backend page
     * 
     * @param \Cx\Core\Html\Sigma $template 
     */
    public function parsePage(\Cx\Core\Html\Sigma $template) {
        $this->template = $template;
        
        $this->showOrders();
    }
    
    public function showOrders() 
    {
        global $_ARRAYLANG;
        
        $term          = isset($_GET['filter-term']) ? contrexx_input2raw($_GET['filter-term']) : '';
        $filterUserId  = isset($_GET['filter-user-id']) ? contrexx_input2raw($_GET['filter-user-id']) : 0;
        $objFilterUser = null;
        
        if (!empty($term) || !empty($filterUserId)) {
            if ($filterUserId) {
                $objFilterUser = \FWUser::getFWUserObject()->objUser->getUser($filterUserId);
            }
            $orders = $this->orderRepository->findOrdersBySearchTerm($term, $objFilterUser);
        } else {
            $orders = $this->orderRepository->getAllByDesc();
        }
        
        $view = new \Cx\Core\Html\Controller\ViewGenerator($orders, array(
            'header'    => $_ARRAYLANG['TXT_MODULE_ORDER_ACT_DEFAULT'],
            'functions' => array(
                'add'       => true,
                'edit'      => true,
                'delete'    => true,
                'sorting'   => true,
                'paging'    => true,
                'filtering' => false,
            ),
            'fields' => array(
                'contactId' => array(
                    'header' => 'contactId',
                    'table' => array(
                        'parse' => function($value) {
                            global $_ARRAYLANG;
                            $userId   = \Cx\Modules\Crm\Controller\CrmLibrary::getUserIdByCrmUserId($value);
                            $userName = \FWUser::getParsedUserTitle($userId);
                            $crmDetailLink = "<a href='index.php?cmd=Crm&amp;act=customers&amp;tpl=showcustdetail&amp;id={$value}' 
                                                    title='{$_ARRAYLANG['TXT_MODULE_ORDER_CRM_CONTACT']}'>
                                                    <img 
                                                        src='".\Env::get('cx')->getCodeBaseCoreWebPath()."/Core/View/Media/navigation_level_1_189.png' 
                                                        width='16' height='16' 
                                                        alt='{$_ARRAYLANG['TXT_MODULE_ORDER_CRM_CONTACT']}'
                                                    />
                                                </a>";
                                                        
                            $url = "<a href='index.php?cmd=Access&amp;act=user&amp;tpl=modify&amp;id={$userId}'
                                       title='{$_ARRAYLANG['TXT_MODULE_ORDER_MODIY_USER_ACCOUNT']}'>" .
                                       $userName .
                                    "</a>" . 
                                    $crmDetailLink;
                            return $url;
                        },
                    ),
                ),
                'subscriptions' => array(
                    'header' => 'subscriptions',
                    'table'  => array(
                        'parse' => function ($subscriptions) {
                            $result = array();
                            foreach ($subscriptions as $subscription) {
                                $productEntity     = $subscription->getProductEntity();
                                $productEntityName = $subscription->getProduct()->getName();
                                if(!$productEntity) {
                                    continue;
                                }
                                $productEditLink = $productEntity;
                                if (method_exists($productEntity, 'getEditLink')) {
                                    $productEditLink = $productEntity->getEditLink();
                                }
                                $subscriptionEditUrl = '<a href=​index.php?cmd=Order&act=subscription&editid='. $subscription->getId() .'>' . $productEntityName . '</a>';
                                
                                $result[] = $subscriptionEditUrl . ' (' . $productEditLink . ')';
                            }
                            
                            return implode(', ', $result);
                        }
                    )
                ),
            ),
        ));

        if ((isset($_GET['editid']) && !empty($_GET['editid'])) || (isset($_GET['add']) && !empty($_GET['add']))) {
            $this->template->hideBlock("order_filter");
        } else {
            \FWUser::getUserLiveSearch(array(
                'minLength' => 1,
                'canCancel' => true,
                'canClear'  => true
            ));
                        
            $this->template->setVariable(array(
                'TXT_MODULE_ORDER_SEARCH'       => $_ARRAYLANG['TXT_MODULE_ORDER_SEARCH'],
                'TXT_MODULE_ORDER_FILTER'       => $_ARRAYLANG['TXT_MODULE_ORDER_FILTER'],
                'TXT_MODULE_ORDER_SEARCH_TERM'  => $_ARRAYLANG['TXT_MODULE_ORDER_SEARCH_TERM'],                
                'ORDER_SEARCH_VALUE'            => isset($_GET['filter-term']) ? contrexx_input2xhtml($_GET['filter-term']) : '',
                'ORDER_USER_ID'                 => contrexx_raw2xhtml($filterUserId),
                'ORDER_USER_NAME'               => $objFilterUser ? contrexx_raw2xhtml(\FWUser::getParsedUserTitle($objFilterUser)) : '',
            ));
        }
        
        $this->template->setVariable('ORDERS_CONTENT', $view->render());
    }
}
