<?php

/**
 * Class Uploader
 *
 * @copyright   CONTREXX CMS - Comvation AG Thun
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_uploader
 */

namespace Cx\Core_Modules\Uploader\Model\Entity;


use Cx\Core\Core\Controller\Cx;
use Cx\Core\Html\Sigma;
use Cx\Lib\FileSystem\File;
use Cx\Model\Base\EntityBase;

/**
 * Class Uploader
 *
 * @copyright   CONTREXX CMS - Comvation AG Thun
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_uploader
 */
class Uploader extends EntityBase
{

    /**
     * The uploader id
     *
     * @var int
     */
    protected $id;

    const UPLOADER_TYPE_MODAL = 'Modal';
    const UPLOADER_TYPE_INLINE = 'Inline';

    /**
     * @var Array
     */
    protected $options = array();

    /**
     * @var Cx
     */
    protected $cx;

    public static $allowedExtensions = array('jpg', 'jpeg', 'png', 'pdf', 'gif', 'mkv', 'zip', 'tar', 'gz', 'docx',
        'doc','mp3','wav','act','aiff','aac','amr','ape','au','awb','dct','dss','flac','gsm','m4a','m4p',
        'mp3','mpc','ogg','oga','opus','ra','rm','raw','sln','tta','vox','wav','wma','wv','webm');

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

        $i       = self::generateId();
//
//        $lastKey = count($_SESSION['uploader']['handlers']);
//        $i       = $lastKey++;

        $_SESSION['uploader']['handlers'][$i] = array('active' => true);

        $this->id = $i;

        $this->options = array(
            'data-pl-upload',
            'data-uploader-id' => $this->id,
            'class' => "uploader-button button",
            'uploader-type' => self::UPLOADER_TYPE_MODAL,
            'allowed-extensions' => self::$allowedExtensions
        );
    }

    /**
     * Saves the callback in the session.
     *
     * @param $callback
     *
     * @return $this
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
        return $this;
    }

    /**
     * @param $options
     */
    function setOptions($options)
    {
        $this->options = array_merge($this->options, $options);

        if (!isset($_SESSION['uploader']['handlers'])) {
            $_SESSION['uploader']['handlers'] = array();
        }
        if (!isset($_SESSION['uploader']['handlers'][$this->id])) {
            $_SESSION['uploader']['handlers'][$this->id] = array();
        }
        if (!isset($_SESSION['uploader']['handlers'][$this->id]['config'])) {
            $_SESSION['uploader']['handlers'][$this->id]['config'] = array();
        }

        //set upload file limit
        if (isset($this->options['upload-limit'])) {
            $_SESSION['uploader']['handlers'][$this->id]['config']['upload-limit'] = $this->options['upload-limit'];
        }
        //set custom allowed extensions
        if (isset($this->options['allowed-extensions'])) {
            $_SESSION['uploader']['handlers'][$this->id]['config']['allowed-extensions'] = $this->options['allowed-extensions'];
        }
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
                if (!in_array($key, array('class', 'id', 'style','title','value'))){
                    if ( strpos($key, 'data') !== 0){
                        $key = 'data-'.$key;
                    }
                }
                if (is_array($value)) {
                    $value = json_encode($value);
                }
                $optionsString .= $key . "='" . $value . "' ";
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
        $path = $this->cx->getCodeBaseCoreModulePath() . '/Uploader/View/Template/Backend/Uploader'.$this->options['uploader-type'].'.html';
        $template = new Sigma();
        $template->loadTemplateFile($path);
        $template->setVariable(
            array(
                'UPLOADER_ID' => $this->id,
                'UPLOADER_CODE' =>
                    file_get_contents($this->cx->getCodeBaseCoreModulePath()
                        . '/Uploader/View/Template/Backend/Uploader.html')
            )
        );
        if ($this->options['uploader-type'] == self::UPLOADER_TYPE_INLINE){
            $this->addClass('uploader-button-hidden');
        }
        return '<button ' . $this->getOptionsString() . ' disabled>' . $buttonName . '</button>' . $template->get();
    }

    /**
     * Set a javascript callback on a global function.
     *
     * @param String $string
     *
     * @return $this
     */
    public function setCallback($string)
    {
        $this->setOptions(array('data-on-file-uploaded' => $string));
        return $this;
    }

    /**
     * Set a file upload limit.
     * @param $limit
     *
     * @return self
     */
    public function setUploadLimit($limit){
        $this->setOptions(array('data-upload-limit' => $limit));
        return $this;
    }

    /**
     * Add a class to the button
     *
     * @param $class
     *
     * @return self
     */
    public function addClass($class){
        $classString = $this->getOption('class');
        $classes = explode(' ',$classString);
        if (!in_array($class, $classes)){
            $classes[] = $class;
        }
        $this->setOptions(array('class' => implode(' ', $classes)));
        return $this;
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
        return $this;
    }

    /**
     * @return string
     */
    public function getType() {
        return $this->options['uploader-type'];
    }

    /**
     * @param string $type
     */
    public function setType($type) {
        $this->options['uploader-type'] = $type;
    }

    public static function generateId(){
        $uploaders = $_SESSION['uploader']['handlers'];
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randstring = '';
        for ($i = 0; $i < 10; $i++) {
            $randstring .= $characters[rand(0, strlen($characters))];
        }
        if (array_key_exists($randstring, $uploaders)){
            return self::generateId();
        }
        return $randstring;
    }

} 