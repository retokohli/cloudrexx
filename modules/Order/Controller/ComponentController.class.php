<?php
/**
 * Class ComponentController
 *
 * @copyright   CONTREXX CMS - Comvation AG Thun
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_order
 */

namespace Cx\Modules\Order\Controller;

/**
 * Class ComponentController
 *
 * The main controller for Order
 *
 * @copyright   CONTREXX CMS - Comvation AG Thun
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_order
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController
{
    public function postResolve(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        // Event Listener must be registered before preContentLoad event
        $evm = \Env::get('cx')->getEvents();
        $subscriptionEventListener = new \Cx\Modules\Order\Model\Event\SubscriptionEventListener();
        $evm->addModelListener(\Doctrine\ORM\Events::postPersist, 'Cx\\Modules\\Order\\Model\\Entity\\Subscription', $subscriptionEventListener);
    }
}
