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
 * At the moment, this is ComponentController is just used to load
 * YAML files and JsonAdapters via component framework
 * @author Michael Ritter <michael.ritter@comvation.com>
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {

    public function getControllersAccessableByJson() {
        return array(
            'JsonNode', 'JsonPage', 'JsonContentManager',
        );
    }
}
