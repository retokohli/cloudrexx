<?php

/**
 *
 * @copyright   Comvation AG
 * @author      Tobias Schmoker <tobias.schmoker@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_mediabrowser
 */

namespace Cx\Core_Modules\MediaBrowser\Controller;


class MediaBrowserController
{

    private static $uniqueInstance = null;

    private final function __clone()
    {
    }

    protected $_attr = array();

    protected function __construct()
    {
        global $_ARRAYLANG;
        \Env::get('init')->loadLanguageData('MediaBrowser');
        foreach ($_ARRAYLANG as $key => $value) {
            if (preg_match("/TXT_FILEBROWSER_[A-Za-z0-9]+/", $key)) {
                \ContrexxJavascript::getInstance()->setVariable(
                    $key, $value, 'mediabrowser'
                );
            }
        }

        \Env::set('MediaBrowser', $this);
        //Cx\Core_Modules\MediaBrowser\Controller\ComponentController::    
    }

    public static function getInstance()
    {
        if (self::$uniqueInstance === null) {
            self::$uniqueInstance = new self;
        }
        return self::$uniqueInstance;
    }

    /**
     * Add a attribute to the button
     *
     * @param $name
     * @param $value
     */
    public function addAttribute($name, $value)
    {
        $this->_attr[$name] = $value;
    }

    /**
     *
     *
     * @param $name
     */
    public function removeAttribute($name)
    {
        unset($this->_attr[$name]);
    }

    /**
     * Set the javascript callback function.
     *
     * @param callable $callbackFunction
     */
    public function setCallback($callbackFunction)
    {
        $this->_attr['Mb-Cb-Js-Modalclosed'] = $callbackFunction;
    }

    /**
     * @return string
     */
    public function getAttributesAsString()
    {
        $attrs = 'data-cx-mb=""';
        foreach ($this->_attr as $name => $value) {
            $attrs .= ' data-cx-' . $name . '="' . $value . '"';
        }
        return $attrs;
    }

    public function getButton($text)
    {
        return '<button ' . $this->getAttributesAsString() . '>' . $text
        . '<button>';
    }


}
