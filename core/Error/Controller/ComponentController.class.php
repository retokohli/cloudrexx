<?php
/**
 * Main controller for Error
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_error
 */

namespace Cx\Core\Error\Controller;

/**
 * Main controller for Error
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_error
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {

    /**
     * @var array $missingPage Information about the missing page if there are some
     */
    protected $missingPage = array();

    public function getControllerClasses() {
        return array('Frontend');
    }

     /**
     * Load your component.
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page

    public function load(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:
               $errorObj = new Error(\Env::get('cx')->getPage()->getContent());
               \Env::get('cx')->getPage()->setContent($errorObj->getErrorPage());
                break;
        }
    }*/

    /**
     * Register event-listener for Routing/PageNotFound-Event
     * @throws \Cx\Core\Event\Controller\EventManagerException
     */
    public function registerEventListeners() {
        $this->cx->getEvents()->addEventListener('Routing/PageNotFound', $this->getController('Frontend'));
    }

    public function setMissingPage(array $missingPageDetails) {
        $this->missingPage = $missingPageDetails;
    }
}