<?php

namespace Cx\Core_Modules\TemplateEditor\Model\Entity;

use Cx\Core\Html\Sigma;

/**
 * Class AreaOption
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 * @subpackage  core_module_templateeditor
 */
class AreaOption extends Option
{

    /**
     * Shows whether the area should be shown
     *
     * @var bool
     */
    protected $active;

    /**
     * @param String $name
     * @param array  $translations
     * @param array  $data
     */
    public function __construct($name, $translations, $data) {
        parent::__construct($name, $translations, $data);
        $this->active = $data['active'] == 'true';
    }

    /**
     * @param Sigma $template
     */
    public function renderBackend($template) {
        $subTemplate = new Sigma();
        $subTemplate->loadTemplateFile(
            'core_modules/TemplateEditor/View/Template/Backend/AreaOption.html'
        );
        $subTemplate->setVariable(
            'TEMPLATEEDITOR_OPTION_VALUE', ($this->active) ? 'checked' : ''
        );
        $subTemplate->setVariable('TEMPLATEEDITOR_OPTION_NAME', $this->name);
        $subTemplate->setVariable(
            'TEMPLATEEDITOR_OPTION_HUMAN_NAME', $this->humanName
        );
        $template->setVariable('TEMPLATEEDITOR_OPTION', $subTemplate->get());
        $template->setVariable('TEMPLATEEDITOR_OPTION_TYPE', 'area');
        $template->parse('option');
    }

    /**
     * @param Sigma $template
     */
    public function renderFrontend($template) {
        $blockName = strtolower('TEMPLATE_EDITOR_' . $this->name);
        if ($template->blockExists($blockName) && $this->active) {
            $template->touchBlock($blockName);
        }
    }

    /**
     * @param array $data
     *
     * @return array
     * @throws OptionValueNotValidException
     */
    public function handleChange($data) {
        if ($data != 'true' && $data != 'false') {
            throw new OptionValueNotValidException('Should be true or false.');
        }
        $this->active = $data;
        return array('active' => $data);
    }

    /**
     * @return string
     */
    public function yamlSerialize() {
        $option             = parent::yamlSerialize();
        $option['specific'] = array('active' => $this->active);
        return $option;
    }

    /**
     * @return boolean
     */
    public function isActive() {
        return $this->active;
    }

    /**
     * @param boolean $active
     */
    public function setActive($active) {
        $this->active = $active;
    }
}