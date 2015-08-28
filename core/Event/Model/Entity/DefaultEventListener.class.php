<?php
/**
 * @copyright   Comvation AG 
 * @author Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 */

namespace Cx\Core\Event\Model\Entity;


use Cx\Core\Core\Controller\Cx;

class DefaultEventListener implements EventListener {

    /**
     * @var Cx
     */
    protected $cx;

    /**
     * @param Cx $cx
     */
    public function __construct(Cx $cx)
    {
        $this->cx = $cx;
    }

    public function onEvent($eventName, array $eventArgs) {
        $methodName = $eventName;
        if (!method_exists($this, $eventName)) {
            $eventNameParts = explode('.', $eventName);
            $methodName = lcfirst(implode('', array_map('ucfirst',$eventNameParts)));
        }
        $this->$methodName(current($eventArgs));
    }
}