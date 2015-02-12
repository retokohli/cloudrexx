<?php
/**
 * Main controller for Media
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_media
 */

namespace Cx\Core_Modules\Media\Controller;

/**
 * Main controller for Media
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_media
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
        global $_CORELANG, $subMenuTitle, $objTemplate, $plainSection;
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:
                $objMedia = new Media(\Env::get('cx')->getPage()->getContent(), $plainSection.MODULE_INDEX);
                \Env::get('cx')->getPage()->setContent($objMedia->getMediaPage());
                break;

            case \Cx\Core\Core\Controller\Cx::MODE_BACKEND:
                $this->cx->getTemplate()->addBlockfile('CONTENT_OUTPUT', 'content_master', 'LegacyContentMaster.html');
                $objTemplate = $this->cx->getTemplate();
                
                $subMenuTitle = $_CORELANG['TXT_MEDIA_MANAGER'];
                $objMedia = new MediaManager();
                $objMedia->getMediaPage();
                break;

            default:
                break;
        }
    }


    /**
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page
     *
     * @throws \Cx\Core\Event\Controller\EventManagerException
     */
    public function preContentParse(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        $this->cx->getEvents()->addEventListener('LoadMediaTypes', new \Cx\Core_Modules\Media\Model\Event\MediaEventListener($this->cx));
    }
}