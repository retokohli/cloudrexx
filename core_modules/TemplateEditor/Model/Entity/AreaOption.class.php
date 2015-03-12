<?php

namespace Cx\Core_Modules\TemplateEditor\Model\Entity;
use Cx\Core\Html\Sigma;

/**
 * 
 */
class AreaOption extends Option {

    protected $active;

    /**
     * @param String $name
     * @param array  $data
     */
    public function __construct($name,$humanname, $data)
    {
        parent::__construct($name,$humanname, $data);
        $this->active =  $data['active'] == 'true';
    }

    /**
     * @param Sigma $template
     */
    public function renderBackend($template)
    {
        $subTemplate = new Sigma();
        $subTemplate->loadTemplateFile('core_modules/TemplateEditor/View/Template/Backend/AreaOption.html');
        $subTemplate->setVariable('TEMPLATEEDITOR_OPTION_VALUE', ( $this->active) ? 'checked' : '');
        $subTemplate->setVariable('TEMPLATEEDITOR_OPTION_NAME', $this->name);
        $subTemplate->setVariable('TEMPLATEEDITOR_OPTION_HUMAN_NAME', $this->humanName);
        $template->setVariable('TEMPLATEEDITOR_OPTION', $subTemplate->get());
        $template->setVariable('TEMPLATEEDITOR_OPTION_TYPE', 'area');
        $template->parse('option');
        // TODO: Implement renderBackend() method.
    }

    /**
     * @param Sigma $template
     */
    public function renderFrontend($template)
    {
        $blockName = strtolower('TEMPLATE_EDITOR_'.$this->name);
        if ($template->blockExists($blockName) && $this->active){
            $template->touchBlock($blockName);
        }
    }

    /**
     * @param array $data
     *
     * @return array
     * @throws OptionValueNotValidException
     */
    public function handleChange($data)
    {
        if ($data != 'true' && $data != 'false'){
            throw new OptionValueNotValidException('Should be true or false.');
        }
        $this->active = $data;
        return array('active' => $data);
    }
}