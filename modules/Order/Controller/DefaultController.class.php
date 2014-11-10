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
     * @param string                                               $submenu                   the submenu name
     */
    public function __construct(\Cx\Core\Core\Model\Entity\SystemComponentController $systemComponentController, \Cx\Core\Core\Controller\Cx $cx, \Cx\Core\Html\Sigma $template, $submenu = null) {
        parent::__construct($systemComponentController, $cx);
        
        $this->template          = $template;
        $this->em                = $this->cx->getDb()->getEntityManager();
        $this->orderRepository   = $this->em->getRepository('Cx\Modules\Order\Model\Entity\Order');
        
        $this->showOrders();
    }

    public function showOrders() 
    {
        global $_ARRAYLANG;
        
        $orders = $this->orderRepository->findAll();
        if (empty($orders)) {
            $orders = new \Cx\Modules\Order\Model\Entity\Order();
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
                            $userId   = \Cx\Modules\Crm\Controller\CrmLibrary::getUserIdByCrmUserId($value);
                            $userName = \FWUser::getParsedUserTitle($userId);
                            $url = '<a href=​index.php?cmd=Access&act=user&tpl=modify&id='. $userId .'>' . $userName . '</a>';
                            return $url;
                        },
                    ),
                ),
                'subscriptions' => array(
                    'header' => 'subscriptions',
                    'table'  => array(
                        'parse' => function ($value, $arrayData) {
                            $subscription  = \Env::get('em')->getRepository('\Cx\Modules\Order\Model\Entity\Subscription')->findOneBy(array('id' => $arrayData['id']));
                            $productEntity = $subscription->getProductEntity();
                            if(!$productEntity) {
                                return;
                            }
                            return $productEntity;
                        }
                    )
                ),
            ),
        ));
        $this->template->setVariable('ORDERS_CONTENT', $view->render());
    }
}
