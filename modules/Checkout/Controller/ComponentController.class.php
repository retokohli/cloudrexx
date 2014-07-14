<?php

/**
 * Main controller for Checkout
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_checkout
 */

namespace Cx\Modules\Checkout\Controller;

/**
 * Main controller for Checkout
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_checkout
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {

    public function getControllerClasses() {
        // Return an empty array here to let the component handler know that there
        // does not exist a backend, nor a frontend controller of this component.
        return array();
    }

    /**
     * Load your component.
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function load(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        global $_CORELANG, $subMenuTitle, $objTemplate;
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:
                $objCheckout = new \Cx\Modules\Checkout\Controller\Checkout(\Env::get('cx')->getPage()->getContent());
                \Env::get('cx')->getPage()->setContent($objCheckout->getPage());
                break;

            case \Cx\Core\Core\Controller\Cx::MODE_BACKEND:
                \Permission::checkAccess(161, 'static');
                $this->cx->getTemplate()->addBlockfile('CONTENT_OUTPUT', 'content_master', 'content_master.html');
                $objTemplate = $this->cx->getTemplate();

                $subMenuTitle = $_CORELANG['TXT_CHECKOUT_MODULE'];
                $objCheckoutManager = new \Cx\Modules\Checkout\Controller\CheckoutManager();
                $objCheckoutManager->getPage();
                break;

            default:
                break;
        }
    }

}