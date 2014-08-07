<?php

/**
 *
 * @copyright   Comvation AG
 * @author      Tobias Schmoker <tobias.schmoker@comvation.com>
 * @package     contrexx
 * @subpackage coremodule_mediabrowser
 */

namespace Cx\Core_Modules\MediaBrowser\Controller;


class MediaBrowserController {

    protected $_attr = array();

    protected function __construct() {
        \Env::set('MediaBrowser', $this);
        //Cx\Core_Modules\MediaBrowser\Controller\ComponentController::    
    }

    public static function initialize() {
        return new self();
    }
    
    public function setAttr($name, $value) {
        array_push($this->_attr, array('name' => $name, 'value' => $value));
    }

    public function setCallback(callable $callbackFunction) {
        // todo
    }

    public function getAttributesAsString() {
        $attrs = 'data-cx-mb=""';
        foreach ($this->_attr as $attr) {
            $attrs .= ' data-cx-'.$attr["name"].'="'.$attr["value"].'"';
        }
        return $attrs;
    }
    
    public function getButton($text) {
        return '<button '.$this->getAttributesAsString().'>'.$text.'<button>';
    }

    // todo | wirklich nötig?
    public function showModal() {
    }

}
