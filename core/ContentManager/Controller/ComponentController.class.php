<?php
/**
 * Main controller for ContentManager
 * 
 * At the moment, this is just an empty ComponentController in order to load
 * YAML files via component framework
 * @author Michael Ritter <michael.ritter@comvation.com>
 */

namespace Cx\Core\ContentManager\Controller;

/**
 * Main controller for ContentManager
 * 
 * At the moment, this is just an empty ComponentController in order to load
 * YAML files via component framework
 * @author Michael Ritter <michael.ritter@comvation.com>
 */
class ComponentController extends \Cx\Core\Component\Model\Entity\SystemComponentController {
    
    public function load(\Cx\Core\Cx $cx, \Cx\Core\ContentManager\Model\Entity\Page $page) {}
    
    public function postContentLoad(\Cx\Core\Cx $cx, \Cx\Core\ContentManager\Model\Entity\Page $page) {}
    
    public function postContentParse(\Cx\Core\Cx $cx, \Cx\Core\ContentManager\Model\Entity\Page $page) {}
    
    public function postFinalize(\Cx\Core\Cx $cx) {}
    
    public function postResolve(\Cx\Core\Cx $cx, \Cx\Core\ContentManager\Model\Entity\Page $page) {}
    
    public function preContentLoad(\Cx\Core\Cx $cx, \Cx\Core\ContentManager\Model\Entity\Page $page) {}
    
    public function preContentParse(\Cx\Core\Cx $cx, \Cx\Core\ContentManager\Model\Entity\Page $page) {}
    
    public function preFinalize(\Cx\Core\Cx $cx, \Cx\Core\Html\Sigma $template) {}
    
    public function preResolve(\Cx\Core\Cx $cx, \Cx\Core\Routing\Url $request) {}

    public function getControllersAccessableByJson() {
        return array(
            'JsonNode', 'JsonPage', 'JsonContentManager',
        );
    }
}
