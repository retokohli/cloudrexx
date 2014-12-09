<?php

/**
 * DefaultController
 *
 * @copyright   Comvation AG
 * @author      Comvation Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_pim
 */

namespace Cx\Modules\Pim\Controller;

/**
 * 
 * DefaultController for displaying all the orders.
 *
 * @copyright   Comvation AG
 * @author      Comvation Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_pim
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
     * ProductRepository instance 
     * @var \Cx\Modules\Product\Model\Repository\ProductRepository $productRepository
     */
    protected $productRepository;
    
    /**
     * module name
     * @var string $moduleName
     */
    public $moduleName = 'Pim';
    
    /**
     * module name for language placeholder
     * @var string $moduleNameLang
     */
    public $moduleNameLang = 'PIM';

    /**
     * Controller for the Backend Orders views
     * 
     * @param \Cx\Core\Core\Model\Entity\SystemComponentController $systemComponentController the system component controller object
     * @param \Cx\Core\Core\Controller\Cx                          $cx                        the cx object
     * @param \Cx\Core\Html\Sigma                                  $template                  the template object
     */
    public function __construct(\Cx\Core\Core\Model\Entity\SystemComponentController $systemComponentController, \Cx\Core\Core\Controller\Cx $cx) {
        parent::__construct($systemComponentController, $cx);
        
        $this->em                  = $this->cx->getDb()->getEntityManager();
        $this->productRepository   = $this->em->getRepository('Cx\Modules\Pim\Model\Entity\Product');  
    }
    
    public function parsePage(\Cx\Core\Html\Sigma $template) {
        $this->template = $template;
        
        $this->showProducts();
    }
    
    public function showProducts() 
    {
        global $_ARRAYLANG;
        
        $products = $this->productRepository->findAll();
        if (empty($products)) {
            $products = new \Cx\Modules\Pim\Model\Entity\Product();
        }
        $view = new \Cx\Core\Html\Controller\ViewGenerator($products, array(
            'header'    => $_ARRAYLANG['TXT_MODULE_PIM_ACT_DEFAULT'],
            'functions' => array(
                'add'       => true,
                'edit'      => true,
                'delete'    => true,
                'sorting'   => true,
                'paging'    => true,
                'filtering' => false,
                )
            ));
        $this->template->setVariable('PRODUCTS_CONTENT', $view->render());
    }
}