<?php
/**
 * Created by PhpStorm.
 * User: robin
 * Date: 15.09.14
 * Time: 16:46
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

    function getOption($option)
    {
        if (isset($this->options[$option])) {
            return $this->options[$option];
        }
        return null;
    }

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

    function getXHtml($buttonName = "Upload")
    {
        $path = $this->cx->getCodeBaseCoreModulePath() . '/Uploader/View/Template/Backend/Uploader.html';
        $objFile = new File($path);
        return '<button ' . $this->getOptionsString() . ' >' . $buttonName . '</button>' . str_replace(
            '{UPLOADER_ID}', $this->id, $objFile->getData()
        );
    }

    public function setCallback($string)
    {
        $this->setOptions(array('data-on-file-uploaded' => $string));
    }

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