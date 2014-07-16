<?php
/**
 * Main controller for Block
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_block
 */

namespace Cx\Modules\Block\Controller;

/**
 * Main controller for Block
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_block
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
            case \Cx\Core\Core\Controller\Cx::MODE_BACKEND:
                $this->cx->getTemplate()->addBlockfile('CONTENT_OUTPUT', 'content_master', 'content_master.html');
                $objTemplate = $this->cx->getTemplate();
                
                \Permission::checkAccess(76, 'static');
                $subMenuTitle = $_CORELANG['TXT_BLOCK_SYSTEM'];
                $objBlock = new \Cx\Modules\Block\Controller\BlockManager();
                $objBlock->getPage();
                break;
        }
    }

    /**
     * Do something before content is loaded from DB
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function preContentLoad(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        global $_CONFIG, $page, $themesPages, $page_template;
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:
                if ($_CONFIG['blockStatus'] == '1') {
                    $content = \Env::get('cx')->getPage()->getContent();
                    \Cx\Modules\Block\Controller\Block::setBlocks($content, $page);
                    \Env::get('cx')->getPage()->setContent($content);
                    \Cx\Modules\Block\Controller\Block::setBlocks($themesPages, $page);
                    // TODO: this call in unhappy, becase the content/home template already gets parsed just the line above
                    \Cx\Modules\Block\Controller\Block::setBlocks($page_template, $page);
                }
                break;
        }
    }
}