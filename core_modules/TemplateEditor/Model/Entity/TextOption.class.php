<?php

namespace Cx\Core_Modules\TemplateEditor\Model\Entity;

use Cx\Core\Html\Sigma;

/**
 *
 */
class TextOption extends Option
{

    protected $string = '';
    protected $regex = null;
    protected $html = false;

    /**
     * @param String $name
     * @param array  $data
     */
    public function __construct($name, $humanname, $data)
    {
        parent::__construct($name, $humanname, $data);
        $this->string = isset($data['textvalue']) ? $data['textvalue'] : '';
        $this->regex  = isset($data['regex']) ? $data['regex'] : null;
        $this->html  = isset($data['html']) ? $data['html'] : false;
    }

    /**
     * @param Sigma $template
     */
    public function renderBackend($template)
    {
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
    public function renderFrontend($template)
    {
        $template->setVariable('TEMPLATE_EDITOR_'.strtoupper($this->name),$this->html  ? $this->string : htmlentities($this->string));
    }

    /**
     * @param array $data
     *
     * @return array
     * @throws OptionValueNotValidException
     */
    public function handleChange($data)
    {
        global $_ARRAYLANG;
        if ($this->regex && !preg_match($this->regex, $data)) {
            throw new OptionValueNotValidException(
              sprintf($_ARRAYLANG['TXT_CORE_MODULE_TEMPLATEEDITOR_TEXT_WRONG_FORMAT'], $data) . $this->regex
            );
        }
        $this->string = $data;
        return array('textvalue' => $this->string);
    }

    /**
     * @return string
     */
    public function yamlSerialize()
    {
        $option = parent::yamlSerialize();
        $option['specific'] = array(
            'textvalue' => $this->string
        );
        return $option;
    }
}
