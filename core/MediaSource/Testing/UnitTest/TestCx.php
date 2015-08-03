<?php
/**
 * @copyright   Comvation AG 
 * @author Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 */

namespace Cx\Core_Modules\MediaBrowser\Testing\UnitTest;

/**
 * Class TestCx
 *
 * @copyright   Comvation AG
 * @author Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 */
class TestCx {
    private $testEventManager;

    public function getEvents(){
        if (!$this->testEventManager){
            $this->testEventManager = new TestEventManager();
        }
        return $this->testEventManager;
    }
}