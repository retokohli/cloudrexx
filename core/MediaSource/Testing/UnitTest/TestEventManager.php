<?php
/**
 * @copyright   Comvation AG 
 * @author Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 */

namespace Cx\Core_Modules\MediaBrowser\Testing\UnitTest;


/**
 * Class TestEventManager
 *
 * @package Cx\Core_Modules\MediaBrowser\Testing\UnitTest
 */
class TestEventManager {

    private $mediaSources = array();

    function triggerEvent($name,$callback){
        $mediaManager = current($callback);
        foreach ($this->mediaSources as $mediaSource){
           $mediaManager->addMediaType($mediaSource);
        }
    }

    /**
     * @param $mediaSource
     *
     * @return array
     */
    public function addMediaSource($mediaSource) {
        $this->mediaSources[] = $mediaSource;
    }
}