<?php

/**
 * InvoiceController
 *
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_order
 */

namespace Cx\Modules\Order\Controller;

/**
 * 
 * InvoiceController for displaying all the invoices.
 *
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_order
 */
class InvoiceController extends \Cx\Core\Core\Model\Entity\Controller {
    
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
     * @var \Cx\Modules\Order\Model\Repository\InvoiceRepository $invoiceRepo
     */
    protected $invoiceRepo;
    
    /**
     * Controller for the Backend Invoice views
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
        $this->invoiceRepo   = $this->em->getRepository('Cx\Modules\Order\Model\Entity\Invoice');
        
        $this->showInvoices();
    }

    public function showInvoices() 
    {
        global $_ARRAYLANG;
        
        $view = new \Cx\Core\Html\Controller\ViewGenerator($this->invoiceRepo->findAll(), array(
            'header'    => $_ARRAYLANG['TXT_MODULE_ORDER_ACT_INVOICE'],
            'functions' => array(
                'add'       => true,
                'edit'      => true,
                'delete'    => true,
                'sorting'   => true,
                'paging'    => true,
                'filtering' => false,
                )
            ));
        $this->template->setVariable('INVOICE_CONTENT', $view->render());
    }
}