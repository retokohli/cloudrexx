<?php

/**
 * Class MediaBrowser
 *
 * @copyright   Comvation AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_mediabrowser
 */
namespace Cx\Core_Modules\MediaBrowser\Model\Entity;

use Cx\Core\Html\Sigma;
use Cx\Model\Base\EntityBase;

/**
 * Class MediaBrowser
 *
 * @copyright   Comvation AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 */
class MediaBrowser extends EntityBase
{
    /**
     * The set options for the mediabrowser
     * @var Array
     */
    protected $options = array();

    /**
     * Create new instance of mediabrowser and register in componentcontroller.
     *
     * @throws \Cx\Core\Core\Model\Entity\SystemComponentException
     * @throws \Exception
     */
    function __construct()
    {
        $this->getComponentController()->addMediaBrowser($this);

        $this->options = array(
            'data-cx-mb',
            'class' => "mediabrowser-button button"
        );
    }


    /**
     * Set a mediabrowser option
     *
     * @param $options
     */
    function setOptions($options)
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Get a option
     *
     * @param $option
     *
     * @return string
     */
    function getOption($option)
    {
        if (isset($this->options[$option])) {
            return $this->options[$option];
        }
        return null;
    }

    /**
     * Set a Javascript callback when the modal gets closed
     *
     * @param $callback array Callback function name
     */
    function setCallback($callback)
    {
        $this->options['data-cx-Mb-Cb-Js-Modalclosed'] = $callback;
    }

    /**
     * Get all Options as a String
     *
     * @return string
     */
    function getOptionsString()
    {
        $optionsString = "";
        foreach ($this->options as $key => $value) {
            if (is_int($key)) {
                $optionsString .= $value . ' ';
            } else {
                $optionsString .= $key . '="' . $value . '" ';
            }
        }
        return $optionsString;
    }

    /**
     * Get the rendered mediabrowser button
     *
     * @param string $buttonName
     *
     * @return string
     */
    function getXHtml($buttonName = "MediaBrowser")
    {
        $button = new Sigma();
        $button->loadTemplateFile($this->cx->getCodeBaseCoreModulePath() . '/MediaBrowser/View/Template/MediaBrowserButton.html');
        $button->setVariable(array(
            'MEDIABROWSER_BUTTON_NAME' => $buttonName,
            'MEDIABROWSER_BUTTON_OPTIONS' =>  $this->getOptionsString()
        ));
        return $button->get();
    }
}