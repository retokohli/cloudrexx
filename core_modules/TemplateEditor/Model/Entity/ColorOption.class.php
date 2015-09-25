<?php

namespace Cx\Core_Modules\TemplateEditor\Model\Entity;

use Cx\Core\Core\Controller\Cx;
use Cx\Core\Html\Sigma;

/**
 * Class ColorOption
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 * @subpackage  core_module_templateeditor
 */
class ColorOption extends Option
{
    /**
     * Color in hex format
     *
     * @var String
     */
    protected $color;

    /**
     * Array with color choices in hex format
     *
     * @var array
     */
    protected $choice;

    /**
     * @param String $name
     * @param array  $translations
     * @param array  $data
     */
    public function __construct($name, $translations, $data)
    {
        parent::__construct($name, $translations, $data);
        $this->color = $data['color'];
        if (isset($data['choice'])) {
            $this->choice = $data['choice'];
        }
    }

    /**
     * Render the option field in the backend.
     *
     * @param Sigma $template
     */
    public function renderOptionField($template)
    {
        global $_ARRAYLANG;
        $subTemplate = new Sigma();
        $subTemplate->loadTemplateFile(
            $this->cx->getCodeBaseCoreModulePath()
            . '/TemplateEditor/View/Template/Backend/ColorOption.html'
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
        \ContrexxJavascript::getInstance()->setVariable(
            array(
                'select' => $_ARRAYLANG['TXT_CORE_MODULE_TEMPLATEEDITOR_SELECT'],
                'colorError' => $_ARRAYLANG['TXT_CORE_MODULE_TEMPLATEEDITOR_COLOR_WRONG_FORMAT'],
                'cancel' => $_ARRAYLANG['TXT_CORE_MODULE_TEMPLATEEDITOR_CANCEL']
            ),
            'TemplateEditor'
        );
        $template->setVariable('TEMPLATEEDITOR_OPTION', $subTemplate->get());
        $template->setVariable('TEMPLATEEDITOR_OPTION_TYPE', 'color');
        $template->parse('option');
    }

    /**
     * Render the option in the frontend.
     *
     * @param Sigma $template
     */
    public function renderTheme($template)
    {
        $template->setVariable(
            'TEMPLATE_EDITOR_' . strtoupper($this->name), $this->color
        );
        for ($i = -255; $i < 255; $i++) {
            $template->setVariable(
                'TEMPLATE_EDITOR_' . strtoupper($this->name) . '_' . (($i < 0)
                    ? 'SUBTRACT' : 'ADD') . '_' . abs($i),
                $this->adjustBrightness($this->color, $i)
            );
        }

    }

    /**
     * Handle a change of the option.
     *
     * @param array $data
     *
     * @return array
     * @throws OptionValueNotValidException
     */
    public function handleChange($data)
    {
        global $_ARRAYLANG;
        if (!preg_match('/^(#)?[0-9a-f]+$/', $data)) {
            throw new OptionValueNotValidException(
                $_ARRAYLANG['TXT_CORE_MODULE_TEMPLATEEDITOR_COLOR_WRONG_FORMAT']
            );
        }
        $this->color = $data;
        return array('color' => $this->color);
    }

    /**
     * Get the data in a serializable format.
     *
     * @return array
     */
    public function yamlSerialize()
    {
        $option = parent::yamlSerialize();
        $option['specific'] = array(
            'choice' => $this->choice
        );
        return $option;
    }

    /**
     * @return mixed
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param mixed $color
     */
    public function setColor($color)
    {
        $this->color = $color;
    }

    /**
     * Gets the current value of the option.
     *
     * @return array
     */
    public function getValue()
    {
        return array('color' => $this->color);
    }

    function adjustBrightness($hex, $steps)
    {
        // Steps should be between -255 and 255. Negative = darker, positive = lighter
        $steps = max(-255, min(255, $steps));
        // Normalize into a six character long hex string
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) == 3) {
            $hex = str_repeat(substr($hex, 0, 1), 2) . str_repeat(
                    substr($hex, 1, 1), 2
                ) . str_repeat(substr($hex, 2, 1), 2);
        }
        // Split into three parts: R, G and B
        $color_parts = str_split($hex, 2);
        $return      = '#';
        foreach ($color_parts as $color) {
            $color = hexdec($color); // Convert to decimal
            $color = max(0, min(255, $color + $steps)); // Adjust color
            $return .= str_pad(
                dechex($color), 2, '0', STR_PAD_LEFT
            ); // Make two char hex code
        }
        return $return;
    }
}