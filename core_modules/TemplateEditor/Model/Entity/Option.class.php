<?php

namespace Cx\Core_Modules\TemplateEditor\Model\Entity;

use Cx\Core\Html\Sigma;
use Cx\Core_Modules\TemplateEditor\Model\YamlSerializable;

/**
 * Class OptionValueNotValidException
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package contrexx
 * @subpackage  core_module_templateeditor
 */
class OptionValueNotValidException extends \Exception
{
}

/**
 * Class Option
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 * @subpackage  core_module_templateeditor
 */
abstract class Option extends \Cx\Model\Base\EntityBase
    implements YamlSerializable
{

    /**
     * The identifying name of the option.
     * @var void
     */
    protected $name;

    /**
     * Array with translations for all available languages.
     * The key of the array is the language id.
     *
     * @var array
     */
    protected $translations;

    /**
     * The translated name of the option. If the active language isn't available
     * english is used as a fallback. If this also isn't available the name of
     * the option is used.
     *
     * @var string
     */
    protected $humanName;

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
     * Render the option field in the backend.
     *
     * @param Sigma $template
     */
    public abstract function renderOptionField($template);

    /**
     * Render the option in the frontend.
     *
     * @param Sigma $template
     */
    public abstract function renderTheme($template);

    /**
     * Handle a change of the option.
     *
     * @param array $data
     *
     * @return array
     * @throws OptionValueNotValidException
     */
    public abstract function handleChange($data);

    /**
     * Get the name of the option.
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set the name of the option.
     *
     * @param void $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * Get the translated name of the option
     *
     * @return string
     */
    public function getHumanName() {
        return $this->humanName;
    }

    /**
     * Set the human name.
     *
     * @param string $humanName
     */
    public function setHumanName($humanName) {
        $this->humanName = $humanName;
    }

    /**
     * Get the data in a serializable format.
     *
     * @return array
     */
    public function yamlSerialize() {
        return array(
            'name' => $this->name,
            'specific' => array(),
            'type' => get_called_class(),
            'translation' => $this->translations
        );
    }

    /**
     * Gets the current value of the option.
     *
     * @return array
     */
    public abstract function getValue();

}