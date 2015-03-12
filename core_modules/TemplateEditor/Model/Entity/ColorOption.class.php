<?php

namespace Cx\Core_Modules\TemplateEditor\Model\Entity;
use Cx\Core\Html\Sigma;

/**
 * 
 */
class ColorOption extends Option {

    protected $color;

    /**
     * @param String $name
     * @param array  $data
     */
    public function __construct($name,$humanname, $data)
    {
        parent::__construct($name,$humanname, $data);
        $this->color = $data['color'];
        // TODO: Implement _construct() method.
    }

    /**
     * @param Sigma $template
     */
    public function renderBackend($template)
    {
        $subTemplate = new Sigma();
        $subTemplate->loadTemplateFile('core_modules/TemplateEditor/View/Template/Backend/ColorOption.html');
        $subTemplate->setVariable('TEMPLATEEDITOR_OPTION_VALUE', $this->color);
        $subTemplate->setVariable('TEMPLATEEDITOR_OPTION_NAME', $this->name);
        $subTemplate->setVariable('TEMPLATEEDITOR_OPTION_HUMAN_NAME', $this->humanName);
        $template->setVariable('TEMPLATEEDITOR_OPTION', $subTemplate->get());
        $template->setVariable('TEMPLATEEDITOR_OPTION_TYPE', 'color');
        $template->parse('option');
    }

    /**
     * @param Sigma $template
     */
    public function renderFrontend($template)
    {
        $template->setVariable('TEMPLATE_EDITOR_'.strtoupper($this->name), $this->color);
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
}