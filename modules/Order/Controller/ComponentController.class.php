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
        
        $crmCrmContactEventListener = new \Cx\Modules\Order\Model\Event\CrmCrmContactEventListener();
        $evm->addModelListener(\Doctrine\ORM\Events::preRemove, 'Cx\\Modules\\Crm\\Model\\Entity\\CrmContact', $crmCrmContactEventListener);
    }
    
    /**
     * get command mode
     * 
     * @return type array
     */
    public function getCommandsForCommandMode() 
    {
        return array('Order');
    }
    
    /**
     * execute the command
     * 
     * @param type $command   command name
     * @param type $arguments argument name
     * 
     * @return type null
     */
    public function executeCommand($command, $arguments) 
    {
        $evm = \Env::get('cx')->getEvents();
        $evm->addEvent('model/terminated');
        
        $subcommand = null;
        if (!empty($arguments[0])) {
            $subcommand = $arguments[0];
        }
        switch ($command) {
            case 'Order':
                switch ($subcommand) {
                    case 'Cron':
                        $this->executeCommandCron();
                        break;
                    default:
                        break;
                }
                break;
            default :
                break;
        }
    }
    
    /**
     * Api Cron command
     * 
     * @return type null
     */
    public function executeCommandCron()
    {
        //  Terminate the active and expired subscription.
        $this->terminateExpiredSubscriptions();
    }
    
    /**
     * To terminate the expired and active subscription
     * 
     * @return type null
     */
    public function terminateExpiredSubscriptions()
    {
        $subscriptionRepo = \Env::get('em')->getRepository('Cx\Modules\Order\Model\Entity\Subscription');
        $subscriptions    = $subscriptionRepo->getExpiredSubscriptionsByCriteria(\Cx\Modules\Order\Model\Entity\Subscription::STATE_ACTIVE);
        
        if (\FWValidator::isEmpty($subscriptions)) {
            return;
        }
        
        foreach ($subscriptions as $subscription) {
            $subscription->terminate();
        }
        \Env::get('em')->flush();
    }
}