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

    /**
     * @param String $name
     * @param array  $data
     */
    public function __construct($name, $humanname, $data)
    {
        parent::__construct($name, $humanname, $data);
        $this->string = isset($data['textvalue']) ? $data['textvalue'] : '';
        $this->regex  = isset($data['regex']) ? $data['regex'] : null;
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
        // TODO: Implement renderBackend() method.
    }

    /**
     * @param Sigma $template
     */
    public function renderFrontend($template)
    {
        $template->setVariable('TEMPLATE_EDITOR_'.strtoupper($this->name), $this->string);
    }

    /**
     * @param array $data
     *
     * @return array
     * @throws OptionValueNotValidException
     */
    public function handleChange($data)
    {
        if (!preg_match($this->regex, $data)) {
            throw new OptionValueNotValidException(
                "String $data doesn't match given regex:" . $this->regex
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
