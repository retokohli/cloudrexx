<?php

namespace Cx\Core_Modules\TemplateEditor\Model\Entity;

use Cx\Core\Html\Sigma;

/**
 * Class TextOption
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 * @subpackage  core_module_templateeditor
 */
class TextOption extends Option
{
    /**
     * The text value of the option.
     *
     * @var string
     */
    protected $string = '';

    /**
     * Regex which the string has to match
     *
     * @var String
     */
    protected $regex = null;

    /**
     * @var bool
     */
    protected $html = false;

    /**
     * @param String $name
     * @param array  $translations
     * @param array  $data
     */
    public function __construct($name, $translations, $data) {
        parent::__construct($name, $translations, $data);
        $this->string = isset($data['textvalue']) ? $data['textvalue'] : '';
        $this->regex  = isset($data['regex']) ? $data['regex'] : null;
        $this->html   = isset($data['html']) ? $data['html'] : false;
    }

    /**
     * @param Sigma $template
     */
    public function renderBackend($template) {
        $subTemplate = new Sigma();
        $subTemplate->loadTemplateFile(
            'core_modules/TemplateEditor/View/Template/Backend/TextOption.html'
        );
        $subTemplate->setVariable('TEMPLATEEDITOR_OPTION_VALUE', $this->string);
        $subTemplate->setVariable('TEMPLATEEDITOR_OPTION_NAME', $this->name);
        $subTemplate->setVariable(
            'TEMPLATEEDITOR_OPTION_HUMAN_NAME', $this->humanName
        );
        $template->setVariable('TEMPLATEEDITOR_OPTION', $subTemplate->get());
        $template->setVariable('TEMPLATEEDITOR_OPTION_TYPE', 'text');
        $template->parse('option');
    }

    /**
     * @param Sigma $template
     */
    public function renderFrontend($template) {
        $template->setVariable(
            'TEMPLATE_EDITOR_' . strtoupper($this->name),
            $this->html ? $this->string : htmlentities($this->string)
        );
    }

    /**
     * @param array $data
     *
     * @return array
     * @throws OptionValueNotValidException
     */
    public function handleChange($data) {
        global $_ARRAYLANG;
        if ($this->regex && !preg_match($this->regex, $data)) {
            throw new OptionValueNotValidException(
                sprintf(
                    $_ARRAYLANG['TXT_CORE_MODULE_TEMPLATEEDITOR_TEXT_WRONG_FORMAT'],
                    $data
                ) . $this->regex
            );
        }
        $this->string = $data;
        return array('textvalue' => $this->string);
    }

    /**
     * @return string
     */
    public function yamlSerialize() {
        $option             = parent::yamlSerialize();
        $option['specific'] = array(
            'textvalue' => $this->string
        );
        return $option;
    }

    /**
     * @return string
     */
    public function getString() {
        return $this->string;
    }

    /**
     * @param string $string
     */
    public function setString($string) {
        $this->string = $string;
    }

    /**
     * @return String
     */
    public function getRegex() {
        return $this->regex;
    }

    /**
     * @param String $regex
     */
    public function setRegex($regex) {
        $this->regex = $regex;
    }

    /**
     * @return boolean
     */
    public function isHtml() {
        return $this->html;
    }

    /**
     * @param boolean $html
     */
    public function setHtml($html) {
        $this->html = $html;
    }
}
