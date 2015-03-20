<?php
/**
 * Main controller for Shop
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_shop
 */

namespace Cx\Modules\Shop\Controller;

/**
 * Main controller for Shop
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_shop
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
        global $_CORELANG, $subMenuTitle, $intAccessIdOffset, $objTemplate;
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:
                \Env::get('cx')->getPage()->setContent(Shop::getPage(\Env::get('cx')->getPage()->getContent()));

                // show product title if the user is on the product details page
                if ($page_metatitle = Shop::getPageTitle()) {
                    \Env::get('cx')->getPage()->setTitle($page_metatitle);
                    \Env::get('cx')->getPage()->setContentTitle($page_metatitle);
                    \Env::get('cx')->getPage()->setMetaTitle($page_metatitle);
                }
                break;

            case \Cx\Core\Core\Controller\Cx::MODE_BACKEND:
                $this->cx->getTemplate()->addBlockfile('CONTENT_OUTPUT', 'content_master', 'LegacyContentMaster.html');
                $objTemplate = $this->cx->getTemplate();

                \Permission::checkAccess($intAccessIdOffset+13, 'static');
                $subMenuTitle = $_CORELANG['TXT_SHOP_ADMINISTRATION'];
                $objShopManager = new ShopManager();
                $objShopManager->getPage();
                break;
        }
    }

    /**
     * Do something after content is loaded from DB
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function postContentLoad(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:
                // Show the Shop navbar in the Shop, or on every page if configured to do so
                if (!Shop::isInitialized()
                // Optionally limit to the first instance
                // && MODULE_INDEX == ''
                ) {
                    \Cx\Core\Setting\Controller\Setting::init('Shop', 'config');
                    if (\Cx\Core\Setting\Controller\Setting::getValue('shopnavbar_on_all_pages','Shop')) {
                        Shop::init();
                        Shop::setNavbar();
                    }
                }
                break;
        }
    }
    
    /**
     * Do something for search the content
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function preContentParse(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        $eventListener = new \Cx\Modules\Shop\Model\Event\ShopEventListener($this->cx);
        $this->cx->getEvents()->addEventListener('SearchFindContent',$eventListener);
        $this->cx->getEvents()->addEventListener('LoadMediaTypes', $eventListener);
    }
}