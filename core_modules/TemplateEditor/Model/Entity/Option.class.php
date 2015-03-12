<?php

namespace Cx\Core_Modules\TemplateEditor\Model\Entity;
use Cx\Core\Core\Controller\Cx;
use Cx\Core\Html\Sigma;

/**
 * 
 */
abstract class Option {

    /**
     * @var void
     */
    public $name;

    public $humanName;

    /**
     * @param String $name
     * @param        $humanname
     * @param array  $data
     */
    public function __construct($name, $humanname, $data){
        $this->name = $name;
        $this->humanName = $humanname;
    }

    /**
     * @param Sigma $template
     */
    public abstract function renderBackend($template);

    /**
     * @param Sigma $template
     */
    public abstract function renderFrontend($template);

    /**
     * @param array $data
     */
    public abstract function handleChange($data);

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param void $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getHumanName()
    {
        return $this->humanName;
    }

    /**
     * @param mixed $humanName
     */
    public function setHumanName($humanName)
    {
        $this->humanName = $humanName;
    }

}

Class OptionValueNotValidException extends \Exception {}