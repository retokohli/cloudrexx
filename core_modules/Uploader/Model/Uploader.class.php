<?php

/**
 * @copyright   CONTREXX CMS - Comvation AG Thun
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_uploader
 */

namespace Cx\Core_Modules\Uploader\Model;


use Cx\Core\Core\Controller\Cx;
use Cx\Core\Html\Sigma;
use Cx\Lib\FileSystem\File;
use Cx\Model\Base\EntityBase;

class Uploader extends EntityBase
{

    /**
     * The uploader id
     *
     * @var int
     */
    protected $id;

    /**
     * @var Array
     */
    protected $options = array();

    /**
     * @var Cx
     */
    protected $cx;

    function __construct()
    {
        $this->cx = Cx::instanciate();
        $this->getComponentController()->addUploader($this);
        if (!isset($_SESSION['uploader'])) {
            $_SESSION['uploader'] = array();
        }
        if (!isset($_SESSION['uploader']['handlers'])) {
            $_SESSION['uploader']['handlers'] = array();
        }


        $lastKey = count($_SESSION['uploader']['handlers']);
        $i       = $lastKey++;

        $_SESSION['uploader']['handlers'][$i] = array('active' => true);

        $this->id = $i;

        $this->options = array(
            'data-pl-upload',
            'data-uploader-id' => $this->id,
            'class' => "button"
        );
    }

    /**
     * Saves the callback in the session.
     *
     * @param $callback
     */
    function setFinishedCallback($callback)
    {
        if (!isset($_SESSION['uploader']['handlers'])) {
            $_SESSION['uploader']['handlers'] = array();
        }
        if (!isset($_SESSION['uploader']['handlers'][$this->id])) {
            $_SESSION['uploader']['handlers'][$this->id] = array();
        }
        $_SESSION['uploader']['handlers'][$this->id]['callback'] = $callback;
    }

    /**
     * @param $options
     */
    function setOptions($options)
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
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
     * Get all options as a string.
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
     * @param string $buttonName
     *
     * @return string
     */
    function getXHtml($buttonName = "Upload")
    {
        $path = $this->cx->getCodeBaseCoreModulePath() . '/Uploader/View/Template/Backend/Uploader.html';
        $objFile = new File($path);
        return '<button ' . $this->getOptionsString() . ' >' . $buttonName . '</button>' . str_replace(
            '{UPLOADER_ID}', $this->id, $objFile->getData()
        );
    }

    /**
     * Set a javascript callback on a global function.
     * @param String $string
     */
    public function setCallback($string)
    {
        $this->setOptions(array('data-on-file-uploaded' => $string));
    }

    /**
     * Add additional data for the uploader
     * @param $data
     */
    public function setData($data)
    {
        if (!isset($_SESSION['uploader']['handlers'])) {
            $_SESSION['uploader']['handlers'] = array();
        }
        if (!isset($_SESSION['uploader']['handlers'][$this->id])) {
            $_SESSION['uploader']['handlers'][$this->id] = array();
        }
        $_SESSION['uploader']['handlers'][$this->id]['data'] = $data;
    }

} 