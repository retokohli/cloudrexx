<?php
/**
 * Main controller for Sitemap
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_sitemap
 */

namespace Cx\Core_Modules\Sitemap\Controller;

/**
 * Main controller for Sitemap
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_sitemap
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
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:
                $objSitemap = new \Cx\Core_Modules\Sitemap\Controller\Sitemap(\Env::get('cx')->getPage()->getContent(), \Env::get('cx')->getLicense());
                $pageTitle = \Env::get('cx')->getPage()->getTitle();
                \Env::get('cx')->getPage()->setContent($objSitemap->getSitemapContent());
                break;

            default:
                break;
        }
    }
}
