<?php
/**
 * Main controller for Market
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  modules_market
 */

namespace Cx\Modules\Market\Controller;

/**
 * Main controller for Market
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  modules_market
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
        global $subMenuTitle, $_CORELANG, $objTemplate;
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:
                $market = new Market(\Env::get('cx')->getPage()->getContent());
                \Env::get('cx')->getPage()->setContent($market->getPage());
                break;

            case \Cx\Core\Core\Controller\Cx::MODE_BACKEND:
                $this->cx->getTemplate()->addBlockfile('CONTENT_OUTPUT', 'content_master', 'content_master.html');
                $objTemplate = $this->cx->getTemplate();
                
                \Permission::checkAccess(98, 'static');
                $subMenuTitle = $_CORELANG['TXT_CORE_MARKET_TITLE'];
                $objMarket = new MarketManager();
                $objMarket->getPage();
                break;
        }
    }

    /**
     * Do something after content is loaded from DB
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function postContentLoad(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        global $marketCheck, $objTemplate, $objMarket, $_CORELANG;
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:
                // Market Show Latest
                $marketCheck = $objTemplate->blockExists('marketLatest');
                if ($marketCheck) {
                    $objMarket = new Market('');
                    $objTemplate->setVariable('TXT_MARKET_LATEST', $_CORELANG['TXT_MARKET_LATEST']);
                    $objMarket->getBlockLatest();
                }
                break;
        }
    }
}