<?php

/**
 * Specific FrontendController for this Component. Use this to easily create a frontent view
 *
 * @copyright   Comvation AG
 * @author      Tobias Schmoker <tobias.schmoker@comvation.com>
 * @package     contrexx
 * @subpackage  modules_skeleton
 */

namespace Cx\Core_Modules\MediaBrowser\Controller;

abstract class MediaBrowserStatus {

    const Upload = 0;
    const Medias = 1;
    const SimpleMedias = 2;

}

class MediaBrowserController {

    private $mode = MediaBrowserStatus::Medias;
    
    
    
    private function __construct() {
        \Env::set('MediaBrowser', $this);
        
        //Cx\Core_Modules\MediaBrowser\Controller\ComponentController::


        
        
    }

    public static function create() {
        return new self();
    }

    public function setMode($uploaderStatus) {
        $this->mode = $uploaderStatus;
    }

    public function setCallback(callable $callbackFunction) {
        // todo
    }

    public function getButton() {
        // is a finisher | todo
        return '';
    }

    public function showModal() {
        // is a finisher | todo
    }

}
