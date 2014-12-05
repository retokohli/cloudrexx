<?php
/**
 * Class ComponentController
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_order
 */

namespace Cx\Modules\Order\Controller;

/**
 * Class OrderException
 */
class OrderException extends \Exception {}

/**
 * Class ComponentController
 *
 * The main Order component
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_order
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {
    
    /*
     * Constructor
     */
    public function __construct(\Cx\Core\Core\Model\Entity\SystemComponent $systemComponent, \Cx\Core\Core\Controller\Cx $cx) {
        parent::__construct($systemComponent, $cx);
    }
    
    public function getControllerClasses() {
        return array('Backend', 'Default', 'Invoice', 'Payment', 'Subscription');
    }
    
    public function postResolve(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        $evm = \Env::get('cx')->getEvents();
        
        $crmUserEventListener = new \Cx\Modules\Order\Model\Event\CrmUserEventListener();
        $evm->addModelListener(\Doctrine\ORM\Events::preRemove, 'Cx\\Modules\\Crm\\Model\\Entity\\CrmContact', $crmUserEventListener);
    }
}