<?php
/**
 * Main controller for ContentManager
 * 
 * At the moment, this is just an empty ComponentController in order to load
 * YAML files via component framework
 * @author Michael Ritter <michael.ritter@comvation.com>
 * @package contrexx
 * @subpackage core_contentmanager
 */

namespace Cx\Core\ContentManager\Controller;

/**
 * Main controller for ContentManager
 * 
 * At the moment, this is ComponentController is just used to load
 * YAML files and JsonAdapters via component framework
 * @author Michael Ritter <michael.ritter@comvation.com>
 * @package contrexx
 * @subpackage core_contentmanager
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {
    
    public function __construct(\Cx\Core\Core\Model\Entity\SystemComponent $systemComponent, \Cx\Core\Core\Controller\Cx $cx) {
        parent::__construct($systemComponent, $cx);
        $evm = $cx->getEvents();
        $pageListener = new \Cx\Core\ContentManager\Model\Event\PageEventListener();
        $evm->addModelListener(\Doctrine\ORM\Events::prePersist, 'Cx\\Core\\ContentManager\\Model\\Entity\\Page', $pageListener);
        $evm->addModelListener(\Doctrine\ORM\Events::postPersist, 'Cx\\Core\\ContentManager\\Model\\Entity\\Page', $pageListener);
        $evm->addModelListener(\Doctrine\ORM\Events::preUpdate, 'Cx\\Core\\ContentManager\\Model\\Entity\\Page', $pageListener);
        $evm->addModelListener(\Doctrine\ORM\Events::postUpdate, 'Cx\\Core\\ContentManager\\Model\\Entity\\Page', $pageListener);
        $evm->addModelListener(\Doctrine\ORM\Events::preRemove, 'Cx\\Core\\ContentManager\\Model\\Entity\\Page', $pageListener);
        $evm->addModelListener(\Doctrine\ORM\Events::postRemove, 'Cx\\Core\\ContentManager\\Model\\Entity\\Page', $pageListener);
        $evm->addModelListener(\Doctrine\ORM\Events::onFlush, 'Cx\\Core\\ContentManager\\Model\\Entity\\Page', $pageListener);
        
        $nodeListener = new \Cx\Core\ContentManager\Model\Event\NodeEventListener();
        $evm->addModelListener(\Doctrine\ORM\Events::preRemove, 'Cx\\Core\\ContentManager\\Model\\Entity\\Node', $nodeListener);
        $evm->addModelListener(\Doctrine\ORM\Events::onFlush, 'Cx\\Core\\ContentManager\\Model\\Entity\\Node', $nodeListener);
        
        $evm->addModelListener(\Doctrine\ORM\Events::onFlush, 'Cx\\Core\\ContentManager\\Model\\Entity\\LogEntry', new \Cx\Core\ContentManager\Model\Event\LogEntryEventListener());
        
        $evm->addEvent('wysiwygCssReload');
    }
    
    /**
     * get controller classes
     * 
     * @return array
     */
    public function getControllerClasses() {
        // Return an empty array here to let the component handler know that there
        // does not exist a backend, nor a frontend controller of this component.
        return array();
    }
    public function getControllersAccessableByJson() {
        return array(
            'JsonNode', 'JsonPage', 'JsonContentManager',
        );
    }
    
    /**
     * Load your component.
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function load(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        global $objTemplate, $objDatabase, $objInit, $act;
        
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_BACKEND:
                $this->cx->getTemplate()->addBlockfile('CONTENT_OUTPUT', 'content_master', 'LegacyContentMaster.html');
                $cachedRoot = $this->cx->getTemplate()->getRoot();
                $this->cx->getTemplate()->setRoot($this->getDirectory() . '/View/Template/Backend');

                \Permission::checkAccess(6, 'static');
                $cm = new ContentManager($act, $objTemplate, $objDatabase, $objInit);
                $cm->getPage();

                $this->cx->getTemplate()->setRoot($cachedRoot);
                break;
        }
    }
    
    /**
     * Do something for search the content
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function preContentParse(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        $this->cx->getEvents()->addEventListener('SearchFindContent', new \Cx\Core\ContentManager\Model\Event\PageEventListener());
   }     
}
