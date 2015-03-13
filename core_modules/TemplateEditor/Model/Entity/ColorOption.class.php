<?php

namespace Cx\Core_Modules\TemplateEditor\Model\Entity;

use Cx\Core\Html\Sigma;

/**
 *
 */
class ColorOption extends Option
{

    protected $color;
    protected $choice;

    /**
     * @param String $name
     * @param array  $data
     */
    public function __construct($name, $humanname, $data)
    {
        parent::__construct($name, $humanname, $data);
        $this->color = $data['color'];
        if (isset($data['choice'])) {
            $this->choice = $data['choice'];
        }
        // TODO: Implement _construct() method.
    }

    /**
     * @param Sigma $template
     */
    public function renderBackend($template)
    {
        $subTemplate = new Sigma();
        $subTemplate->loadTemplateFile(
            'core_modules/TemplateEditor/View/Template/Backend/ColorOption.html'
        );
        $subTemplate->setVariable('TEMPLATEEDITOR_OPTION_VALUE', $this->color);
        $subTemplate->setVariable('TEMPLATEEDITOR_OPTION_NAME', $this->name);
        $subTemplate->setVariable(
            'TEMPLATEEDITOR_OPTION_HUMAN_NAME', $this->humanName
        );
        if ($this->choice) {
            $subTemplate->setVariable(
                'TEMPLATEEDITOR_OPTION_CHOICE', json_encode($this->choice)
            );
        }
        $template->setVariable('TEMPLATEEDITOR_OPTION', $subTemplate->get());
        $template->setVariable('TEMPLATEEDITOR_OPTION_TYPE', 'color');
        $template->parse('option');
    }

    /**
     * @param Sigma $template
     */
    public function renderFrontend($template)
    {
        $template->setVariable(
            'TEMPLATE_EDITOR_' . strtoupper($this->name), $this->color
        );
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function handleChange($data)
    {
        $this->color = $data;
        return array('color' => $this->color);
    }

    /**
     * @return string
     */
    public function yamlSerialize()
    {
        $option = parent::yamlSerialize();
        $option['specific'] = array(
            'color' => $this->color,
            'choice' => $this->choice
        );
        return $option;
    }
}