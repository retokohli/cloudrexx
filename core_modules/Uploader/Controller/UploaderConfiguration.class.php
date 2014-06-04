<?php

/**
 * Specific FrontendController for this Component. Use this to easily create a frontent view
 *
 * @copyright   Comvation AG
 * @author      Tobias Schmoker <tobias.schmoker@comvation.com>
 * @package     contrexx
 * @subpackage  modules_skeleton
 */

namespace Cx\Core_Modules\Uploader\Controller;

class UploaderConfiguration {

// implemented for expansion purposes
    public static function get() {
        return new self();
    }
    
    public $thumbnails = array(
        array( // the first one is used for hover effects. 
            'name' => 'small',
            'value' => '_s',
            'size' => 150, // width
            'quality' => 100
        ),
        array(
            'name' => 'medium',
            'value' => '_m',
            'size' => 600,  // width
            'quality' => 100
        ),
    );
}
