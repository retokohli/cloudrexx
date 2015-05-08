<?php

/**
 * @copyright   Comvation AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 * @subpackage  core_mediasource
 */

namespace Cx\Core\MediaSource\Controller;


use Cx\Core\ContentManager\Model\Entity\Page;
use Cx\Core\Core\Controller\Cx;
use Cx\Core\Core\Model\Entity\SystemComponent;
use Cx\Core\Core\Model\Entity\SystemComponentController;
use Cx\Core\MediaSource\Model\Entity\MediaSourceManager;

/**
 * Class ComponentController
 *
 * @copyright   Comvation AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 * @subpackage  core_mediasource
 */
class ComponentController
    extends SystemComponentController
{
    public function __construct(SystemComponent $systemComponent, Cx $cx) {
        parent::__construct($systemComponent, $cx);
        $eventHandlerInstance = $cx->getEvents();
        $eventHandlerInstance->addEvent('mediasource.load');
    }

    public function getControllerClasses() {
        // Return an empty array here to let the component handler know that there
        // does not exist a backend, nor a frontend controller of this component.
        return array();
    }


}