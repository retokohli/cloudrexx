<?php

namespace Cx\Core_Modules\TemplateEditor\Model\Entity;

use Cx\Core\Core\Controller\Cx;
use Cx\Core\Html\Sigma;
use Cx\Core_Modules\TemplateEditor\Model\YamlSerializable;

/**
 * Class Option
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 * @subpackage  core_module_templateeditor
 */
abstract class Option implements YamlSerializable
{

    /**
     * @var void
     */
    public $name;

    /**
     * @var array
     */
    public $translations;

    /**
     * @var String
     */
    public $humanName;

    /**
     * @param String $name
     * @param        $translations
     * @param array  $data
     */
    public function __construct($name, $translations, $data) {
        global $_LANGID;
        $this->name         = $name;
        $this->humanName    = isset($translations[$_LANGID])
            ? $translations[$_LANGID]
            : (isset($translations[2]) ? $translations[2] : $name);
        $this->translations = $translations;
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
    public function getName() {
        return $this->name;
    }

    /**
     * @param void $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getHumanName() {
        return $this->humanName;
    }

    /**
     * @param mixed $humanName
     */
    public function setHumanName($humanName) {
        $this->humanName = $humanName;
    }

    /**
     * @return array
     *     -
     * name: slider_images
     * specific:
     * urls: [ "images/content/slideshow/slide_1.jpg", "images/content/slideshow/slide_2.jpg" ]
     * type: Cx\Core_Modules\TemplateEditor\Model\Entity\ImageSeriesOption
     * translation:
     * 1: "Haupt Slider"
     */
    public function yamlSerialize() {
        return array(
            'name' => $this->name,
            'specific' => array(),
            'type' => get_called_class(),
            'translation' => $this->translations
        );
    }


}

Class OptionValueNotValidException extends \Exception
{
}